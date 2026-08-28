<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->string('login')->toString();

        /* Se puede entrar con el email o con el usuario: el arroba decide por cuál buscar. */
        $user = User::where(
            Str::contains($login, '@') ? 'email' : 'username',
            $login,
        )->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        if ($user->isSuspended()) {
            throw ValidationException::withMessages([
                'login' => [__('This account is suspended.')],
            ]);
        }

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken($request->string('device_name', 'api'))->plainTextToken,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('Logged out.')]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }
}
