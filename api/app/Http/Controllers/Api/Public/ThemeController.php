<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\ThemeService;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    public function __construct(private readonly ThemeService $themes) {}

    /**
     * Paletas y opciones de forma disponibles. Lo consume el panel para armar
     * el selector de apariencia de la tienda.
     */
    public function __invoke(): JsonResponse
    {
        $palettes = collect($this->themes->palettes())
            ->map(fn (array $palette, string $key) => [
                'key' => $key,
                'name' => $palette['name'],
                'swatches' => $palette['swatches'],
                'colors' => $palette['colors'],
            ])
            ->values();

        $options = collect($this->themes->options())
            ->map(fn (array $option, string $key) => [
                'key' => $key,
                'name' => $option['name'],
                'values' => collect($option['values'])
                    ->map(fn (array $value, string $valueKey) => [
                        'key' => $valueKey,
                        'name' => $value['name'],
                    ])
                    ->values(),
            ])
            ->values();

        return response()->json([
            'palettes' => $palettes,
            'options' => $options,
            'default' => config('themes.default'),
        ]);
    }
}
