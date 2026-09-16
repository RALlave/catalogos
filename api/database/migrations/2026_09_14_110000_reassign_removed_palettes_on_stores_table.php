<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The "alegre" and "arcoiris" palettes were removed from the config, so
     * stores that still had them move to the default palette.
     */
    public function up(): void
    {
        DB::table('stores')
            ->whereNotIn('palette', array_keys(config('themes.palettes')))
            ->update(['palette' => config('themes.default.palette')]);
    }

    public function down(): void
    {
        //
    }
};
