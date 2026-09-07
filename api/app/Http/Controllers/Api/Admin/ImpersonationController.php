<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\SessionHandoff;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImpersonationController extends Controller
{
    /**
     * Entrar al panel de una tienda como su dueño, para dar soporte.
     *
     * El panel de la tienda vive en su propio subdominio, que es otro origen:
     * no se devuelve un token —no podría guardarse desde acá— sino un código
     * de un solo uso que el navegador canjea del otro lado. El token del
     * superadmin ni se toca: sigue esperando en su origen.
     */
    public function __invoke(Store $store, SessionHandoff $handoff): JsonResponse
    {
        $owner = $store->user;

        if (! $owner) {
            throw new NotFoundHttpException('The store has no owner.');
        }

        if ($owner->hasRole(UserRole::Superadmin->value)) {
            throw new AccessDeniedHttpException('A superadmin cannot be impersonated.');
        }

        return response()->json([
            'code' => $handoff->issue($owner, impersonated: true),
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
            ],
        ]);
    }
}
