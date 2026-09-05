<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // A manual mark, like "featured" and "sold_out": nothing derives it
            // from created_at. Named "is_new" because "new" is a reserved word.
            $table->boolean('is_new')->default(false)->after('sold_out');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_new');
        });
    }
};
