<?php

namespace App\Services;

class ThemeService
{
    /**
     * @return array<string, mixed>
     */
    public function palettes(): array
    {
        return config('themes.palettes');
    }

    /**
     * Grupos de opciones de forma: radius, nav y banner.
     *
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return config('themes.options');
    }

    /**
     * @return array<int, string>
     */
    public function paletteKeys(): array
    {
        return array_keys($this->palettes());
    }

    /**
     * Valores aceptados de una opción de forma.
     *
     * @return array<int, string>
     */
    public function optionKeys(string $option): array
    {
        return array_keys(config("themes.options.{$option}.values", []));
    }

    /**
     * Colores de una paleta. Si la tienda quedó apuntando a una paleta que ya
     * no está en la configuración, se responde con la de por defecto.
     *
     * @return array<string, string>
     */
    public function colors(?string $palette): array
    {
        return config("themes.palettes.{$palette}.colors")
            ?? config('themes.palettes.'.config('themes.default.palette').'.colors');
    }

    public function paletteName(?string $palette): string
    {
        return config("themes.palettes.{$palette}.name")
            ?? config('themes.palettes.'.config('themes.default.palette').'.name');
    }

    /**
     * Valor de una opción de forma, con la misma red de seguridad que la
     * paleta: si la tienda guardó algo que ya no existe, vuelve el defecto.
     */
    public function option(string $option, ?string $value): string
    {
        return in_array($value, $this->optionKeys($option), true)
            ? $value
            : config("themes.default.{$option}");
    }
}
