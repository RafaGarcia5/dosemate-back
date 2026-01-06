<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //register
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'birth_date' => 'required',
            'gender' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'role' => 'required',
            'doctor' => 'nullable|string'
        ]);

        if($validator->fails()){
            $data = [
                'message' => __('auth.validation_fail'),
                'errors' => $validator->errors()
            ];
            return response()->json($data, 400);
        }

        $validated = $validator->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if(!$user) return response()->json(['message' => __('auth.register_fail')], 500);
        
        return response()->json(['message' => __('auth.register_success')], 200);
    }

    //login
    public function login(Request $request){
        $credentials = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($credentials->fails()){
            $data = [
                'message' => __('auth.validation_fail'),
                'errors' => $credentials->errors()
            ];
            return response()->json($data, 422);
        }

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password))
            return response()->json(['message' => __('auth.login_fail')], 401);

        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role
            ]
        ]);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('auth.logout_success')], 200);
    }
}
