<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where the hero "Ver catálogo" button goes: a category of the store, or
     * the products grid when empty. Deleting the category sends it back to the
     * grid instead of leaving a broken link.
     */
    public function up(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('media_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
