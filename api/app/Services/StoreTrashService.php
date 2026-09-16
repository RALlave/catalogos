<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Media;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * La papelera de tiendas y el borrado sin rastro.
 *
 * En la papelera la tienda sigue entera: el catálogo responde 404 y el dueño
 * sigue entrando a su panel. Restaurarla la deja como estaba, publicada u
 * oculta, porque `active` no se toca.
 *
 * Eliminarla definitivamente borra al dueño —un dueño es una tienda— y la base
 * se lleva en cascada todo lo que cuelga de la tienda. Lo que no tiene cascada
 * se borra a mano, y los archivos se van con la carpeta entera de la tienda.
 */
class StoreTrashService
{
    public function __construct(private readonly CatalogCache $cache) {}

    public function trash(Store $store): Store
    {
        $store->forceFill(['trashed_at' => now()])->save();

        /* Sin esto el catálogo seguiría respondiendo desde la caché. */
        $this->cache->forgetStore($store);

        return $store;
    }

    public function restore(Store $store): Store
    {
        $store->forceFill(['trashed_at' => null])->save();

        $this->cache->forgetStore($store);

        return $store;
    }

    /**
     * Borra la tienda, su dueño y todo lo que dejaron: filas, tokens y
     * archivos.
     *
     * Las filas van en una transacción y los archivos después: si la base
     * falla no queda una tienda viva sin imágenes.
     */
    public function destroy(Store $store): void
    {
        $user = $store->user;
        $slug = $store->slug;
        $directory = Media::directoryFor($store->id);

        DB::transaction(function () use ($store, $user): void {
            /* Un superadmin no es "el dueño" de nadie: si alguna vez tuviera
               tienda, se va la tienda y la cuenta queda. */
            if (! $user || $user->hasRole(UserRole::Superadmin->value)) {
                $store->delete();

                return;
            }

            /* Nada de esto tiene clave foránea hacia el usuario. */
            $user->tokens()->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();

            /* Los roles los suelta el propio HasRoles al borrar. La tienda cae
               por la cascada de `stores.user_id`, y con ella categorías,
               productos, galerías, media, heros, pedidos, lista de espera y
               estadísticas. */
            $user->delete();
        });

        Storage::disk('public')->deleteDirectory($directory);

        $this->cache->forget($slug);
    }

    /**
     * Borra las tiendas que cumplieron su plazo en la papelera.
     *
     * @return int cuántas se borraron
     */
    public function purgeExpired(): int
    {
        $days = (int) Setting::where('key', Setting::STORE_TRASH_DAYS)->value('value');

        /* Sin el ajuste cargado no se borra nada: borrar de más no tiene vuelta. */
        if ($days < 1) {
            return 0;
        }

        $purged = 0;

        Store::query()
            ->whereNotNull('trashed_at')
            ->where('trashed_at', '<=', now()->subDays($days))
            ->with('user')
            /* get() y no each(): each() pagina por offset y, borrando
               mientras avanza, se saltearía la mitad. */
            ->get()
            ->each(function (Store $store) use (&$purged): void {
                $this->destroy($store);
                $purged++;
            });

        return $purged;
    }
}
