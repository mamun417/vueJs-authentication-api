<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\ApiController;
use App\User;
use Auth;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return $this->errorMessage('email or password does not match', 404);
    }

    public function me(): \Illuminate\Http\JsonResponse
    {
        $userInfo = $this->guard()->user();

        $userSkills = User::find($this->guard()->id())->first()->skills;

        $userSkills = $userSkills->map(function ($item, $key) {
            return ['label' => $item->name, 'code' => $item->id];
        });

        $userInfo['skills'] = $userSkills;

        return $this->successResponse(['user' => $userInfo], 200);
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        $this->guard()->logout();
        return $this->successMessage('successfully logged out', 200);
    }

    public function refresh(): \Illuminate\Http\JsonResponse
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    public function respondWithToken($token): \Illuminate\Http\JsonResponse
    {
        $data = [
            'user' => $this->guard()->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ];

        return $this->successResponse($data, 200);
    }

    public function guard()
    {
        return Auth::guard();
    }
}
