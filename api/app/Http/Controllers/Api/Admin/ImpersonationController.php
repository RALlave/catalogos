<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImpersonationController extends Controller
{
    /**
     * Entrar al panel de una tienda como su dueño, para dar soporte. Devuelve un
     * token del dueño con acceso completo: el panel guarda el del superadmin
     * aparte y lo restaura al volver.
     */
    public function __invoke(Store $store): JsonResponse
    {
        $owner = $store->user;

        if (! $owner) {
            throw new NotFoundHttpException('The store has no owner.');
        }

        if ($owner->hasRole(UserRole::Superadmin->value)) {
            throw new AccessDeniedHttpException('A superadmin cannot be impersonated.');
        }

        return response()->json([
            'user' => new UserResource($owner),
            'token' => $owner->createToken('impersonation')->plainTextToken,
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
            ],
        ]);
    }
}
