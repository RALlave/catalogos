<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('visible')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'slug']);
            $table->index(['store_id', 'visible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
