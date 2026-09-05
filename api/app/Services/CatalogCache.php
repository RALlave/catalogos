<?php

namespace App\Services;

use App\Models\Store;
use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Caché de los endpoints públicos del catálogo.
 *
 * El caso de uso es un link circulando por WhatsApp: mucha gente entrando a la
 * misma tienda en pocos minutos. Sin esto, cada visita rehace las mismas
 * consultas con sus joins.
 *
 * La invalidación es por **versión**, no por tags: el driver `file` (y también
 * `database`) no soporta `Cache::tags()`. Cada tienda tiene un número de
 * versión que forma parte de la clave; al guardar cualquier cosa se cambia ese
 * número y todas sus claves anteriores dejan de encontrarse, y expiran solas.
 *
 * Se indexa por slug y no por id porque el slug es lo único que trae la URL
 * pública: resolverlo a un id primero significaría la consulta que estamos
 * tratando de evitar.
 */
class CatalogCache
{
    /**
     * Red de seguridad: lo máximo que puede sobrevivir un dato viejo si alguna
     * invalidación falla. No es el tiempo esperado de vida — lo normal es que
     * la entrada muera antes, al guardarse un cambio.
     */
    private const TTL = 600;

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public function remember(string $slug, string $key, Closure $callback): mixed
    {
        return Cache::remember($this->key($slug, $key), self::TTL, $callback);
    }

    /**
     * Tira toda la caché de una tienda. Si el slug acaba de cambiar también se
     * limpia el anterior: las páginas viejas siguen circulando por WhatsApp.
     */
    public function forgetStore(Store $store): void
    {
        $slugs = [$store->slug, $store->getOriginal('slug')];

        foreach (array_unique(array_filter($slugs)) as $slug) {
            $this->forget($slug);
        }
    }

    public function forget(string $slug): void
    {
        Cache::forever($this->versionKey($slug), $this->newVersion($slug));
    }

    private function key(string $slug, string $key): string
    {
        return 'catalog:'.$slug.':'.$this->version($slug).':'.$key;
    }

    private function version(string $slug): int
    {
        return Cache::rememberForever($this->versionKey($slug), fn (): int => now()->timestamp);
    }

    private function versionKey(string $slug): string
    {
        return 'catalog:'.$slug.':version';
    }

    /**
     * La marca de tiempo, pero nunca repetida: dos guardados dentro del mismo
     * segundo tienen que dar versiones distintas o el segundo no invalidaría
     * lo que se cacheó entre medio.
     */
    private function newVersion(string $slug): int
    {
        return max(now()->timestamp, $this->version($slug) + 1);
    }
}
