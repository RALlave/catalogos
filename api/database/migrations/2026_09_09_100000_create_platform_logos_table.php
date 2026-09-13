<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La galería de logos que el superadmin ofrece a las tiendas.
     *
     * No es un ajuste más de `settings`: son muchos, se listan, se renombran y
     * se borran de a uno, y eso pide columnas y no un clave-valor. Tampoco
     * entra en `media`, que exige `store_id`: estos no son de ninguna tienda.
     *
     * Elegir uno **copia** el archivo a la biblioteca de la tienda, así que
     * nada de acá queda referenciado desde afuera: borrar un logo no deja
     * huérfano a nadie.
     */
    public function up(): void
    {
        Schema::create('platform_logos', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);

            /* Misma forma que `media`: la variante más grande en `path` y el
               resto en `variants`, tal como los escribe ImageOptimizer. */
            $table->string('path');
            $table->json('variants')->nullable();
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_logos');
    }
};
