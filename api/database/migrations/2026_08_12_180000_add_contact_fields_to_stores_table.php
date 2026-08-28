<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('description');
            $table->string('phone')->nullable()->after('whatsapp');
            $table->string('email')->nullable()->after('phone');
            $table->string('website')->nullable()->after('tiktok');
            $table->string('map_url')->nullable()->after('address');
            $table->string('currency', 3)->nullable()->after('country');

            // [{"days": "Lunes a viernes", "hours": "08:00 a 18:00"}]
            $table->json('schedules')->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'industry',
                'phone',
                'email',
                'website',
                'map_url',
                'currency',
                'schedules',
            ]);
        });
    }
};
