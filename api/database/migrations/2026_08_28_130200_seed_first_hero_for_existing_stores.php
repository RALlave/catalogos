<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Until now the banner texts lived in the catalog markup and the photo was
     * the store cover. Every existing store gets that same banner as its first
     * hero, so no catalog is left without one.
     */
    public function up(): void
    {
        $now = now();

        $heroes = DB::table('stores')
            ->select('id', 'cover_media_id')
            ->get()
            ->map(fn ($store) => [
                'store_id' => $store->id,
                'media_id' => $store->cover_media_id,
                'eyebrow' => 'Catálogo online · Consultá por WhatsApp',
                'title' => 'Elegí lo que te gusta y pedilo por WhatsApp',
                'text' => 'Todo el catálogo con precio a la vista. Sin registro y sin carrito: nos escribís y te lo reservamos.',
                'order' => 1,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($heroes !== []) {
            DB::table('heroes')->insert($heroes);
        }
    }

    public function down(): void
    {
        DB::table('heroes')->delete();
    }
};
