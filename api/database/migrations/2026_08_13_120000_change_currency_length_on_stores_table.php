<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The currency accepts a trailing dot, so "Gs." and "USD." need one more character.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('currency', 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('currency', 3)->nullable()->change();
        });
    }
};
