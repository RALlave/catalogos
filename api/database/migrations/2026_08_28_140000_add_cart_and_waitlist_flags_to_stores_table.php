<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las dos funciones nacen apagadas: una tienda que ya está publicada no
     * tiene que cambiar de comportamiento sola.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('cart_enabled')->default(false)->after('banner');
            $table->boolean('waitlist_enabled')->default(false)->after('cart_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['cart_enabled', 'waitlist_enabled']);
        });
    }
};
