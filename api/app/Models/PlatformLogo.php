<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Uno de los logos que el superadmin deja listos para que una tienda elija.
 *
 * Los archivos viven en la misma carpeta que la marca de la plataforma
 * (`platform/`), porque tampoco cuelgan de ninguna tienda.
 */
#[Fillable([
    'name',
    'is_default',
    'path',
    'variants',
    'mime',
    'size',
    'width',
    'height',
])]
class PlatformLogo extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'is_default' => 'boolean',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /**
     * URL de una variante. Pedir una que no se generó cae en el archivo más
     * grande, igual que en Media.
     */
    public function url(?string $size = null): string
    {
        $path = $this->variants[$size]['path'] ?? $this->path;

        return Storage::disk('public')->url($path);
    }

    /**
     * Candidatos para el navegador, del más angosto al más ancho. El ancho que
     * se declara es el real del archivo, nunca el de la medida objetivo.
     */
    public function srcset(): string
    {
        $candidates = [];

        foreach ($this->variants ?? [] as $variant) {
            $candidates[$variant['width']] = Storage::disk('public')->url($variant['path']).' '.$variant['width'].'w';
        }

        ksort($candidates);

        return implode(', ', $candidates);
    }
}
