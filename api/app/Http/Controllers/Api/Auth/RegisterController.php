<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $user->assignRole(UserRole::StoreOwner->value);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken($request->string('device_name', 'api'))->plainTextToken,
        ], 201);
    }
}
