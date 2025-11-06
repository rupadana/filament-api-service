<?php

namespace Rupadana\ApiService\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Rupadana\ApiService\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    /**
     * Login
     *
     * @return JsonResponse
     */
    public function login(LoginRequest $request)
    {
        if (! Auth::validate($request->validated())) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.',
                ],
                401
            );
        }

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

    /**
     * Logout
     *
     * @return JsonResponse
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
