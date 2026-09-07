<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\HandoffRedeemRequest;
use App\Http\Resources\StoreResource;
use App\Http\Resources\UserResource;
use App\Services\SessionHandoff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Llevar la sesión de un subdominio a otro. Ver App\Services\SessionHandoff.
 */
class HandoffController extends Controller
{
    public function __construct(private readonly SessionHandoff $handoff) {}

    /** Código para llevar la propia sesión al subdominio de la tienda. */
    public function issue(Request $request): JsonResponse
    {
        return response()->json([
            'code' => $this->handoff->issue($request->user()),
        ]);
    }

    /**
     * Canjea el código por un token de este origen. Va sin autenticar: el
     * código ES la credencial, y por eso dura un minuto y sirve una sola vez.
     */
    public function redeem(HandoffRedeemRequest $request): JsonResponse
    {
        $session = $this->handoff->redeem($request->string('code')->toString());

        if (! $session) {
            throw new NotFoundHttpException('The handoff code is invalid or expired.');
        }

        $user = $session['user'];

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken($request->string('device_name', 'panel'))->plainTextToken,
            'impersonated' => $session['impersonated'],
            'store' => $user->store ? new StoreResource($user->store) : null,
        ]);
    }
}
