<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        try {
            $result = $this->authService->login($request->only('email', 'password'));

            return ApiResponse::success([
                'user' => $result['user'],
                'token' => $result['token'],
            ], 'Login successful');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $user = $this->authService->register($request->only('name', 'email', 'password'));

        return ApiResponse::success($user, 'Registration successful', 201);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->user($request->user());

        return ApiResponse::success($user);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        try {
            $status = $this->authService->sendPasswordResetLink($request->only('email'));

            return ApiResponse::success(null, $status);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        try {
            $status = $this->authService->resetPassword($request->only('email', 'token', 'password'));

            return ApiResponse::success(null, $status);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function users(): JsonResponse
    {
        $users = User::with('roles')->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->getRoleNames()->first(),
        ]);

        return ApiResponse::success($users);
    }

    public function verificationNotification(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return ApiResponse::error('Email already verified', 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return ApiResponse::success(null, 'Verification link sent');
    }
}
