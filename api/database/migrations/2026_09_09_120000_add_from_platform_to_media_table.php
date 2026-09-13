<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca la imagen que entró a la biblioteca como copia de un logo de la
     * plataforma.
     *
     * Sin esto, cambiar de logo iría dejando una copia por elección y no habría
     * cómo distinguirlas de las que subió el dueño, que no se tocan nunca.
     *
     * Es un booleano y no el id del logo de origen: lo que hay que saber es de
     * dónde vino la copia, y ese logo puede desaparecer de la galería sin que
     * la copia deje de ser una copia.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->boolean('from_platform')->default(false)->after('store_id');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('from_platform');
        });
    }
};
