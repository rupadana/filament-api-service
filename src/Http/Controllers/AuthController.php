<?php

namespace Rupadana\ApiService\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Rupadana\ApiService\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    /**
     * Login
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        if (Auth::validate($request->validated())) {
            $user = Auth::getLastAttempted();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Login success.',
                    'token' => $user->createToken($request->header('User-Agent'), ['*'])->plainTextToken,
                ],
                201
            );
        }

        return response()->json(
            [
                'sucess' => false,
                'message' => 'The provided credentials are incorrect.',
            ],
            401
        );
    }

    /**
     * Logout
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json(
            [
                'success' => true,
                'message' => 'Logout success.',
            ],
            200
        );
    }
}
