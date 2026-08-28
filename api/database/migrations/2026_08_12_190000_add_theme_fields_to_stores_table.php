<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Claves de config/themes.php, no los valores: cambiar una paleta
            // ahí se refleja en todas las tiendas que la usan.
            $table->string('layout', 20)->default('01')->after('cover');
            $table->string('palette', 40)->default('tierra')->after('layout');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['layout', 'palette']);
        });
    }
};
