<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El logo con el que arranca una tienda recién creada.
     *
     * Es una marca sobre la fila y no una clave en `settings` porque el dato es
     * de un logo, no de la plataforma: borrar el logo se lleva la marca sola,
     * sin dejar apuntando a un id que ya no existe.
     *
     * Hay uno o ninguno: marcar uno desmarca al anterior.
     */
    public function up(): void
    {
        Schema::table('platform_logos', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('platform_logos', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
