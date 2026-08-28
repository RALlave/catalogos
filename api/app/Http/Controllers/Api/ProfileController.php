<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json(['user' => new UserResource($user->refresh())]);
    }

    /**
     * Cambiar la contraseña cierra el resto de las sesiones: quedan vivos
     * únicamente el token con el que se hizo el cambio.
     */
    public function password(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update(['password' => $request->string('password')]);

        $current = $request->user()->currentAccessToken();

        $user->tokens()->where('id', '!=', $current->id)->delete();

        return response()->json(['message' => __('Password updated.')]);
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(['user' => new UserResource($request->user())]);
    }
}
