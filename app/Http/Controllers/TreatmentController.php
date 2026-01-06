<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TreatmentController extends Controller
{
    public function createTreatment(Request $request){
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:users,id',
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required|after_or_equal:start_date',
            'comment' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = Treatment::where('patient_id', $request->patient_id)->where('name', $request->name)->exists();

        if ($exists) {
            return response()->json(['message' => __('treatment.duplicate_treatment')], 409);
        }

        $validated = $validator->validated();
        Treatment::create($validated);

        return response()->json(['message' => __('treatment.create_treatment_success')], 201);
    }

    public function byPatient(){
        $user = Auth::user();
        $treatments = $user->treatments;
        return $treatments->isEmpty()
            ? response()->json(['message' => __('treatment.treatment_not_found')], 404)
            : response()->json($treatments);
    }

    public function byDate(Request $request){
        $user = Auth::user();
        $month = $request->query('month');
        $year = $request->query('year');
        $patient_id = $request->query('patient_id');

        if (!$month || !$year) {
            return response()->json(['message' => __('treatment.validation_fail')], 400);
        }

        $treatments = Treatment::where('patient_id', $patient_id ?? $user->id)
            ->where(function ($query) use ($month, $year) {
                $query->whereMonth('start_date', $month)->whereYear('start_date', $year)
                      ->orWhereMonth('end_date', $month)->whereYear('end_date', $year);
            })->get();

        return $treatments->isEmpty()
            ? response()->json(['message' => __('treatment.treatment_not_found')], 404)
            : response()->json($treatments);
    }

    public function treatmentsOfMyPatients(){
        $user = Auth::user();
        $patients = $user->patients()->with('treatments')->get();

        $data = $patients->map(function ($patient) {
            return [
                'patient' => $patient->name,
                'treatments' => $patient->treatments,
            ];
        });

        return response()->json($data, 200);
    }


    public function update(Request $request, $id){
        $treatment = Treatment::find($id);
        if (!$treatment) {
            return response()->json(['message' => __('treatment.treatment_not_found')], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes',
            'start_date' => 'sometimes',
            'end_date' => 'sometimes|after_or_equal:start_date',
            'comment' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $treatment->update($validated);

        return response()->json(['message' => __('treatment.update_treatment_success')], 200);
    }

    public function destroy($id){
        $treatment = Treatment::find($id);

        if (!$treatment) {
            return response()->json(['message' => __('treatment.treatment_not_found')], 404);
        }

        $treatment->delete();
        return response()->json(['message' => __('treatment.delete_treatment_success')], 200);
    }
}
