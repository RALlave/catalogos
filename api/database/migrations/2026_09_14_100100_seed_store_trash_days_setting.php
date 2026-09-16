<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los días que una tienda espera en la papelera antes de borrarse sola.
     *
     * El valor inicial vive acá y no en el código: el código lee lo que hay en
     * `settings` y nada más.
     */
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key' => 'store_trash_days',
            'value' => '90',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'store_trash_days')->delete();
    }
};
