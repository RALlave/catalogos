<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The home banner stopped being a single fixed block: a store keeps a list
     * of heroes and the catalog rotates them. The image points at the media
     * library, so deleting the file only leaves the hero without a photo.
     */
    public function up(): void
    {
        Schema::create('heroes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('eyebrow', 120)->nullable();
            $table->string('title', 120);
            $table->string('text', 255)->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['store_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroes');
    }
};
