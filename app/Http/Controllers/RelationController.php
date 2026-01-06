<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RelationController extends Controller
{
    public function createRelation(Request $request){
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:users,id',
            'caregiver_id' => 'required|exists:users,id',
        ]);
        
        if($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $patient = User::find($request->patient_id);

        if ($patient->caregivers()->where('caregiver_id', $request->caregiver_id)->exists()) {
            return response()->json(['message' => __('relation.duplicate_relation')], 409);
        }

        $patient->caregivers()->attach($request->caregiver_id);

        return response()->json(['message' => __('relation.create_relation_success')], 201);
    }

    public function getCaregiverList(){
        $user = Auth::user();
        $caregivers = $user->caregivers()->get(['id', 'name', 'email']);

        if ($caregivers->isEmpty()) return response()->json(['message' => __('relation.caregiver_list_not_found')], 404);
        
        return response()->json($caregivers);
    }

    public function getPatientList(){
        $user = Auth::user(); 
        $patients = $user->patients()->get(['id', 'name', 'birth_date', 'gender', 'email', 'doctor']);

        if ($patients->isEmpty()) return response()->json(['message' => __('relation.patient_list_not_found')], 404);

        return response()->json($patients);
    }

    public function deleteRelation(Request $request){
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:users,id',
            'caregiver_id' => 'required|exists:users,id',
        ]);

        if($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $patient = User::find($request->patient_id);
        $patient->caregivers()->detach($request->caregiver_id);

        return response()->json(['message' => __('relation.delete_relation_success')]);
    }

        public function deleteCaregivers(){
            $user = Auth::user(); 

            if ($user->role !== 'patient') return response()->json(['message' => __('relation.unauthorized_action_1')], 403);

            $patient = User::findOrFail($user->id);
            $patient->caregivers()->detach();

            return response()->json(['message' => __('relation.delete_caregivers_success')]);
        }

        public function deletePatients(){
            $user = Auth::user(); 

            if ($user->role !== 'caregiver') return response()->json(['message' => __('relation.unauthorized_action_2')], 403);

            $caregiver = User::findOrFail($user->id);
            $caregiver->patients()->detach();

            return response()->json(['message' => __('relation.delete_patients_success')], 200);
        }
}
