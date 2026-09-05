<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contador de visitas del catálogo, ya agregado por día.
     *
     * No se guarda un evento por visita: cada fila es "esta tienda, este tipo,
     * este día" con el acumulado en `count`. La tabla queda chica para siempre
     * y los gráficos del panel salen de una sola consulta.
     *
     * `product_id` es null cuando el dato es de la tienda entera (una visita al
     * catálogo) y apunta al producto cuando es una vista o un compartido suyo.
     */
    public function up(): void
    {
        Schema::create('store_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('type', 20);
            $table->date('date');
            $table->unsignedInteger('count')->default(0);

            $table->timestamps();

            /* Una sola fila por tienda, tipo, producto y día. En MySQL el
               índice no alcanza cuando `product_id` es null, así que el
               servicio igual incrementa antes de crear. */
            $table->unique(['store_id', 'type', 'product_id', 'date']);

            /* Los tres gráficos filtran por tipo dentro de un rango de fechas. */
            $table->index(['store_id', 'type', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_stats');
    }
};
