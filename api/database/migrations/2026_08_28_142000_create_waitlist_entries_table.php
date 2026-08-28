<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lista de espera de un producto agotado: el cliente deja su nombre y su
     * WhatsApp para que le avisen cuando vuelva.
     *
     * Son datos personales de un tercero, así que el dueño tiene que poder
     * borrarlos: por eso la fila se elimina de verdad, sin borrado suave.
     */
    public function up(): void
    {
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('name', 120);
            $table->string('phone', 30);

            /* Cuándo el dueño marcó que ya le avisó. Null es "pendiente". */
            $table->timestamp('notified_at')->nullable();

            $table->timestamps();

            /* La misma persona no se anota dos veces al mismo producto: si
               vuelve a mandar el formulario se actualiza la fila que ya está. */
            $table->unique(['product_id', 'phone']);
            $table->index(['store_id', 'notified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_entries');
    }
};
