<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('slug');
            $table->boolean('sold_out')->default(false)->after('visible');

            // "attributes" is reserved by Eloquent, so the column is named "specs".
            // [{"label": "Talles", "value": "S, M, L"}]
            // [{"label": "Colores", "type": "colors", "values": ["#000000", "#ffffff"]}]
            $table->json('specs')->nullable()->after('description');

            // ["Envío gratis", "Garantía 6 meses"]
            $table->json('benefits')->nullable()->after('specs');

            // [{"type": "discount", "text": "-20%", "detail": "OFF"}]
            $table->json('badges')->nullable()->after('benefits');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'sold_out', 'specs', 'benefits', 'badges']);
        });
    }
};
