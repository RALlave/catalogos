<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * La galería del producto deja de guardar el archivo: pasa a apuntar a la
 * biblioteca, así una misma imagen sirve para varios productos.
 *
 * Los archivos ya subidos se quedan donde están (products/{id}/...): lo que se
 * migra es la referencia, no el archivo.
 */
return new class extends Migration
{
    private const DISK = 'public';

    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id')->nullable()->after('product_id');
        });

        $this->moveToLibrary();

        // Sin archivo la fila ya no representa nada, y la referencia pasa a ser obligatoria.
        DB::table('product_images')->whereNull('media_id')->delete();

        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id')->nullable(false)->change();
            $table->foreign('media_id')->references('id')->on('media')->cascadeOnDelete();
            $table->dropColumn('path');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('path')->after('product_id')->default('');
        });

        DB::table('product_images')
            ->join('media', 'media.id', '=', 'product_images.media_id')
            ->update(['product_images.path' => DB::raw('media.path')]);

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['media_id']);
            $table->dropColumn('media_id');
        });
    }

    /**
     * Cada imagen de producto se convierte en un registro de la biblioteca.
     * Un mismo archivo repetido en varios productos entra una sola vez.
     */
    private function moveToLibrary(): void
    {
        $rows = DB::table('product_images')
            ->join('products', 'products.id', '=', 'product_images.product_id')
            ->select('product_images.id', 'product_images.path', 'products.store_id')
            ->orderBy('product_images.id')
            ->get();

        $created = [];

        foreach ($rows as $row) {
            $key = $row->store_id.'|'.$row->path;

            $created[$key] ??= $this->createMedia($row->store_id, $row->path);

            DB::table('product_images')
                ->where('id', $row->id)
                ->update(['media_id' => $created[$key]]);
        }
    }

    private function createMedia(int $storeId, string $path): ?int
    {
        if (! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        $absolute = Storage::disk(self::DISK)->path($path);
        $size = @getimagesize($absolute) ?: [];
        $now = now();

        return DB::table('media')->insertGetId([
            'store_id' => $storeId,
            'path' => $path,
            'name' => basename($path),
            'mime' => Storage::disk(self::DISK)->mimeType($path) ?: 'image/jpeg',
            'size' => Storage::disk(self::DISK)->size($path),
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
