<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El catálogo dejó de tener cuatro layouts: ahora es un solo diseño y lo
     * que cambia es la paleta más tres opciones de forma. Las paletas viejas
     * (tierra, madera, promocional, oferta) ya no existen en la configuración,
     * así que las tiendas que las tenían pasan a la de por defecto.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('layout');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->string('palette', 40)->default('cafe')->change();

            $table->string('radius', 20)->default('square')->after('palette');
            $table->string('nav', 20)->default('dark')->after('radius');
            $table->string('banner', 20)->default('dark')->after('nav');
        });

        DB::table('stores')
            ->whereNotIn('palette', array_keys(config('themes.palettes')))
            ->update(['palette' => config('themes.default.palette')]);
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['radius', 'nav', 'banner']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->string('palette', 40)->default('tierra')->change();

            $table->string('layout', 20)->default('01')->after('cover');
        });
    }
};
