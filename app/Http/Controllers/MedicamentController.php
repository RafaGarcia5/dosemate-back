<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MedicamentController extends Controller
{
    public function createMedicament(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'dosage' => 'required',
            'interval_hours' => 'required',
            'start_date' => 'required',
            'end_date' => 'required|after_or_equal:start_date',
            'comment' => 'nullable',
            'treatment_id' => 'required|exists:treatment,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = Medicament::where('name', $request->name)->where('treatment_id', $request->treatment_id)->exists();

        if ($exists) {
            return response()->json(['message' => __('medicament.duplicate_medicament')], 422);
        }

        $validated = $validator->validated();
        $medicament = Medicament::create($validated);
        $medicament->generateDoseTracks();

        return response()->json(['message' => __('medicament.create_medicament_success')], 201);
    }

    public function getMedicamentList(int $id){
        $medicamentList = Medicament::where('treatment_id', $id)->get();

        return $medicamentList->isEmpty()
            ? response()->json(['message' => __('medicament.list_not_found')], 404)
            : response()->json($medicamentList);
    }

    public function getMedicamentById(int $id){
        $medicament = Medicament::find($id);

        return $medicament 
        ?   response()->json($medicament)
        :   response()->json(['message' => __('medicament.medicament_not_found')], 404); 
    }

    public function update(Request $request, int $id){
        $medicament = Medicament::find($id);
        if (!$medicament) {
            return response()->json(['message' => __('medicament.medicament_not_found')], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required',
            'dosage' => 'sometimes|required',
            'interval_hours' => 'sometimes|required',
            'start_date' => 'sometimes|required',
            'end_date' => 'nullable|after_or_equal:start_date',
            'comment' => 'nullable',
            'treatment_id' => 'sometimes|required|exists:treatment,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $oldStart = $medicament->start_date;
        $oldEnd = $medicament->end_date;
        $oldInterval = $medicament->interval_hours;

        $medicament->update($validated);

        if (
            isset($validated['start_date']) ||
            isset($validated['end_date']) ||
            isset($validated['interval_hours'])
        ) {
            $medicament->doseTracks()->delete();
            $medicament->generateDoseTracks();
        }

        return response()->json(['message' => __('medicament.update_medicament_success')], 200);
    }

    // Delete medicament
    public function delete(int $id){
        $medicament = Medicament::find($id);

        if(!$medicament) {
            return response()->json(['message' => __('medicament.medicament_not_found')], 404);
        }

        $medicament->delete();
        return response()->json(['message' => __('medicament.delete_medicament_success')], 200);
    }
}
