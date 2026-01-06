<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUserById(Request $request){
        return response()->json($request->user());
    }

    public function updateUser(Request $request){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'name' => 'sometimes',
            'birth_date' => 'sometimes',
            'gender' => 'sometimes',
            'role' => 'sometimes',
            'doctor' => 'sometimes|nullable',
            'old_password' => 'sometimes|required_with:new_password',
            'new_password' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $validated = $validator->validated();

        if ($request->filled('old_password') && $request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['message' => __('user.validation_pwd_failed')], 401);
            }
            $validated['password'] = Hash::make($request->new_password);
        }

        $user->update($validated);
        return response()->json(['message' => __('user.update_user_success')],200);
    }

    public function deleteUser(){
        $user = Auth::user();
        $user->delete();
        return response()->json(['message' => __('user.delete_user_success')], 200);
    }
}
