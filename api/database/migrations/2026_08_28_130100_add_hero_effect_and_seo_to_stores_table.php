<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two settings that belong to the store, not to each hero: how the carousel
     * moves between slides, and the SEO block. The share image is the cover,
     * which stops being the banner photo and now only feeds the meta tags.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('hero_effect', 20)->default('slide')->after('banner');
            $table->string('meta_title', 60)->nullable()->after('description');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['hero_effect', 'meta_title', 'meta_description']);
        });
    }
};
