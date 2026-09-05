<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every upload is now stored as a set of WebP variants. `path` keeps
     * pointing at the biggest one, so anything already reading it still works;
     * `variants` maps each size name to its own file. A null means the row
     * predates the conversion and has a single unoptimized file.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->json('variants')->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('variants');
        });
    }
};
