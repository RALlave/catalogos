<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The hero main button: its text and where it goes. `link` is a key of
     * `catalog.hero_links`; existing heroes keep going to the products grid,
     * or to their category when they already had one.
     */
    public function up(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->string('button_text', 30)->nullable()->default('Ver catálogo')->after('text');
            $table->string('link', 20)->default('products')->after('button_text');
        });

        DB::table('heroes')->whereNotNull('category_id')->update(['link' => 'category']);
    }

    public function down(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->dropColumn(['button_text', 'link']);
        });
    }
};
