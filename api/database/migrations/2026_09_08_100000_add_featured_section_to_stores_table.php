<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The showcase of featured products on the home page: a switch and its two
     * headings.
     *
     * The headings carry a column default instead of being seeded afterwards:
     * in MySQL a default also fills the existing rows, so every store that is
     * already live gets a readable section from the first deploy and the owner
     * only has to rewrite it if the wording does not fit.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('featured_enabled')->default(true)->after('waitlist_enabled');
            /* Nullable on top of the default: emptying a heading is a valid
               choice, and then that line simply is not drawn. */
            $table->string('featured_title', 60)->nullable()->default('No te lo podés perder')->after('featured_enabled');
            $table->string('featured_subtitle', 80)->nullable()->default('nuestros productos más recomendados')->after('featured_title');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['featured_enabled', 'featured_title', 'featured_subtitle']);
        });
    }
};
