<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\AuthResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get authenticated user profile
     */
    public function profile(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Profile obtained successfully');
    }

    /**
     * Update user profile
     */
    public function updateProfile(UserRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->successResponse($user, 'Profile updated successfully');
    }

    /**
     * Update user password
     */
    public function updatePassword(UserRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return $this->successResponse(null, 'Password updated successfully');
    }
}
