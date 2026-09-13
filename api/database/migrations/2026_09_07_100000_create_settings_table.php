<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajustes de la plataforma, los que no pertenecen a ninguna tienda.
     *
     * Es la única tabla del negocio sin `store_id`: lo que guarda es del SaaS
     * entero, y hoy es el logo que ven todos los accesos. Va como clave-valor
     * para que sumar el favicon o el nombre no sea una migración más.
     *
     * `value` es texto: lo que necesita estructura —el logo y sus variantes—
     * se guarda como JSON y lo interpreta el servicio.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('key', 100)->unique();
            $table->text('value')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
