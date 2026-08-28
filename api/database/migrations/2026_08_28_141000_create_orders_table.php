<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un pedido es la foto de lo que el cliente mandó por WhatsApp. No hay
     * pagos, ni estados, ni datos del cliente: el catálogo no le pide nada.
     * Sirve para que el dueño sepa qué le piden.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('items_count');

            /* Total y moneda quedan congelados: los precios de los productos
               cambian y el pedido tiene que seguir diciendo lo que decía. Es
               null cuando ningún producto del pedido tenía precio cargado. */
            $table->decimal('total', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();

            $table->timestamps();

            $table->index(['store_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            /* Si el producto se borra el pedido no desaparece: queda con el
               nombre y el precio que tenía en ese momento. */
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('quantity');

            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
