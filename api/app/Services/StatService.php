<?php

namespace App\Services;

use App\Enums\StatType;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatService
{
    /** Días que mira el panel, contando hoy. */
    public const PERIOD = 30;

    /** Cuántas filas devuelven los rankings. */
    private const TOP = 5;

    /**
     * Cliente automático: no es una visita real y ensucia el número.
     * Un user agent vacío es casi siempre un script.
     */
    private const BOTS = '/bot|crawl|spider|slurp|fetch|monitor|preview|headless|facebookexternalhit/i';

    /**
     * Cuenta el hecho una sola vez por visitante y por día.
     *
     * El visitante se identifica con su IP y su navegador, sin guardar nada de
     * eso: sólo vive un hash en la caché hasta que termina el día. Recargar el
     * catálogo veinte veces sigue siendo una visita.
     *
     * Se trabaja con slugs y no con modelos a propósito: las respuestas del
     * catálogo salen de `CatalogCache` sin tocar la base, así que la tienda se
     * resuelve recién cuando hay algo para contar. El visitante repetido no
     * paga ninguna consulta.
     */
    public function track(Request $request, string $slug, StatType $type, ?string $productSlug = null): void
    {
        if (! $request->userAgent() || preg_match(self::BOTS, $request->userAgent())) {
            return;
        }

        $key = sprintf(
            'stat:%s:%s:%s:%s',
            $type->value,
            $slug,
            $productSlug ?? '',
            hash('sha256', $this->visitorIp($request).'|'.$request->userAgent()),
        );

        /* add() escribe sólo si la clave no estaba: devuelve false cuando el
           mismo visitante ya contó hoy. */
        if (! Cache::add($key, true, now()->endOfDay())) {
            return;
        }

        $store = Store::query()->where('slug', $slug)->where('active', true)->first(['id']);

        if (! $store) {
            return;
        }

        $product = $productSlug === null
            ? null
            : $store->products()->where('slug', $productSlug)->first(['id']);

        if ($productSlug !== null && ! $product) {
            return;
        }

        $this->record($store, $type, $product);
    }

    /**
     * Suma uno al acumulado del día.
     *
     * Primero se incrementa y recién si no había fila se crea, para que dos
     * visitas simultáneas no se pisen: el UPDATE es atómico.
     */
    public function record(Store $store, StatType $type, ?Product $product = null): void
    {
        $attributes = [
            'store_id' => $store->id,
            'product_id' => $product?->id,
            'type' => $type->value,
            'date' => today()->toDateString(),
        ];

        $updated = StoreStat::query()
            ->where('store_id', $attributes['store_id'])
            ->where('product_id', $attributes['product_id'])
            ->where('type', $attributes['type'])
            ->where('date', $attributes['date'])
            ->increment('count');

        if ($updated === 0) {
            StoreStat::query()->create($attributes + ['count' => 1]);
        }
    }

    /**
     * Todo lo que dibuja el dashboard del dueño.
     *
     * @return array<string, mixed>
     */
    public function report(Store $store): array
    {
        [$from, $to] = $this->period();
        [$previousFrom, $previousTo] = $this->previousPeriod();

        $total = $this->visits($store, $from, $to);
        $previous = $this->visits($store, $previousFrom, $previousTo);

        return [
            'days' => self::PERIOD,
            'visits' => [
                'total' => $total,
                /* Sin período anterior no hay contra qué comparar: null, no 0. */
                'change' => $previous > 0 ? (int) round(($total - $previous) / $previous * 100) : null,
            ],
            'daily' => $this->daily($store, $from, $to),
            'top_viewed' => $this->topProducts($store, StatType::ProductView, $from, $to),
            'top_shared' => $this->topProducts($store, StatType::Share, $from, $to),
        ];
    }

    /**
     * Tiendas con más visitas del período, para el panel del superadmin.
     *
     * @return array<int, array{name: string, count: int}>
     */
    public function topStores(): array
    {
        [$from, $to] = $this->period();

        return DB::table('store_stats')
            ->join('stores', 'stores.id', '=', 'store_stats.store_id')
            ->where('store_stats.type', StatType::Visit->value)
            ->whereBetween('store_stats.date', [$from, $to])
            ->groupBy('stores.id', 'stores.name')
            ->orderByDesc('total')
            ->limit(self::TOP)
            ->get(['stores.name', DB::raw('SUM(store_stats.count) as total')])
            ->map(fn (object $row) => ['name' => $row->name, 'count' => (int) $row->total])
            ->all();
    }

    private function visits(Store $store, string $from, string $to): int
    {
        return (int) DB::table('store_stats')
            ->where('store_id', $store->id)
            ->where('type', StatType::Visit->value)
            ->whereBetween('date', [$from, $to])
            ->sum('count');
    }

    /**
     * Serie de visitas día por día, con los días sin datos en cero: si no se
     * rellenan, el gráfico junta fechas salteadas y miente.
     *
     * @return array<string, array<int, mixed>>
     */
    private function daily(Store $store, string $from, string $to): array
    {
        $counts = DB::table('store_stats')
            ->where('store_id', $store->id)
            ->where('type', StatType::Visit->value)
            ->whereBetween('date', [$from, $to])
            ->groupBy('date')
            ->get(['date', DB::raw('SUM(count) as total')])
            ->pluck('total', 'date');

        $labels = [];
        $values = [];

        for ($day = today()->subDays(self::PERIOD - 1); $day->lte(today()); $day->addDay()) {
            $labels[] = $day->format('d/m');
            $values[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array<int, array{name: string, count: int}>
     */
    private function topProducts(Store $store, StatType $type, string $from, string $to): array
    {
        return DB::table('store_stats')
            ->join('products', 'products.id', '=', 'store_stats.product_id')
            ->where('store_stats.store_id', $store->id)
            ->where('store_stats.type', $type->value)
            ->whereBetween('store_stats.date', [$from, $to])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->limit(self::TOP)
            ->get(['products.name', DB::raw('SUM(store_stats.count) as total')])
            ->map(fn (object $row) => ['name' => $row->name, 'count' => (int) $row->total])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function period(): array
    {
        return [today()->subDays(self::PERIOD - 1)->toDateString(), today()->toDateString()];
    }

    /**
     * @return array<int, string>
     */
    private function previousPeriod(): array
    {
        return [
            today()->subDays(self::PERIOD * 2 - 1)->toDateString(),
            today()->subDays(self::PERIOD)->toDateString(),
        ];
    }

    /**
     * IP real del visitante.
     *
     * El catálogo se renderiza en el servidor de Nuxt, así que la petición
     * llega desde ahí: la IP de quien mira viaja en X-Forwarded-For y es el
     * primer valor de la lista.
     */
    private function visitorIp(Request $request): string
    {
        $forwarded = $request->header('X-Forwarded-For');

        if ($forwarded) {
            return trim(explode(',', $forwarded)[0]);
        }

        return (string) $request->ip();
    }
}
