<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Traspaso de sesión entre subdominios.
 *
 * Cada tienda administra desde su propio subdominio, y dos subdominios son dos
 * orígenes distintos que no comparten `localStorage`: el token del panel no
 * puede cruzar de uno a otro. En vez de mandarlo por la URL —quedaría en el
 * historial y en los logs de nginx— se emite un código de vida corta que sólo
 * sirve una vez y se canjea del otro lado por un token nuevo.
 *
 * Lo usan los dos caminos que cambian de origen: el dueño que se logueó en el
 * dominio principal y el superadmin que entra a administrar una tienda.
 */
class SessionHandoff
{
    /** Lo justo para redirigir el navegador; no es una sesión. */
    private const TTL = 60;

    /**
     * Emite un código para llevar la sesión de este usuario a otro subdominio.
     *
     * `$impersonated` marca que el código lo pidió un superadmin para entrar
     * como el dueño: el panel lo necesita para dibujar la barra de aviso.
     */
    public function issue(User $user, bool $impersonated = false): string
    {
        $code = Str::random(64);

        Cache::put($this->key($code), [
            'user_id' => $user->id,
            'impersonated' => $impersonated,
        ], self::TTL);

        return $code;
    }

    /**
     * Canjea el código y lo quema. Devuelve null si no existe, ya se usó o
     * venció: los tres casos son el mismo error para quien pregunta.
     *
     * @return array{user: User, impersonated: bool}|null
     */
    public function redeem(string $code): ?array
    {
        $payload = Cache::pull($this->key($code));

        if (! $payload) {
            return null;
        }

        $user = User::find($payload['user_id']);

        if (! $user || $user->isSuspended()) {
            return null;
        }

        return [
            'user' => $user,
            'impersonated' => (bool) $payload['impersonated'],
        ];
    }

    private function key(string $code): string
    {
        return 'handoff:'.hash('sha256', $code);
    }
}
