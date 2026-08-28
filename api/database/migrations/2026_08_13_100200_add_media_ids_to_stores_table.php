<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * El logo y la portada dejan de ser un path suelto: pasan a la biblioteca, así
 * se eligen desde ahí y se pueden reutilizar.
 */
return new class extends Migration
{
    private const DISK = 'public';

    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->unsignedBigInteger('logo_media_id')->nullable()->after('slug');
            $table->unsignedBigInteger('cover_media_id')->nullable()->after('logo_media_id');
        });

        $this->moveToLibrary();

        Schema::table('stores', function (Blueprint $table) {
            $table->foreign('logo_media_id')->references('id')->on('media')->nullOnDelete();
            $table->foreign('cover_media_id')->references('id')->on('media')->nullOnDelete();
            $table->dropColumn(['logo', 'cover']);
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('slug');
            $table->string('cover')->nullable()->after('logo');
        });

        foreach (['logo', 'cover'] as $field) {
            DB::table('stores')
                ->join('media', 'media.id', '=', 'stores.'.$field.'_media_id')
                ->update(['stores.'.$field => DB::raw('media.path')]);
        }

        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['logo_media_id']);
            $table->dropForeign(['cover_media_id']);
            $table->dropColumn(['logo_media_id', 'cover_media_id']);
        });
    }

    private function moveToLibrary(): void
    {
        $stores = DB::table('stores')->select('id', 'logo', 'cover')->orderBy('id')->get();

        foreach ($stores as $store) {
            $values = [];

            foreach (['logo', 'cover'] as $field) {
                $id = $store->{$field} ? $this->createMedia($store->id, $store->{$field}) : null;

                if ($id) {
                    $values[$field.'_media_id'] = $id;
                }
            }

            if ($values) {
                DB::table('stores')->where('id', $store->id)->update($values);
            }
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
