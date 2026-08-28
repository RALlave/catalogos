<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 15)->nullable()->unique()->after('name');
        });

        $this->backfill();

        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 15)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }

    /**
     * Give every existing account a username derived from its email.
     */
    private function backfill(): void
    {
        $taken = [];

        DB::table('users')->orderBy('id')->each(function (object $user) use (&$taken): void {
            $username = $this->uniqueUsername(Str::before($user->email, '@'), $taken);

            $taken[] = Str::lower($username);

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });
    }

    /**
     * @param  array<int, string>  $taken
     */
    private function uniqueUsername(string $base, array $taken): string
    {
        $base = Str::of($base)->replaceMatches('/[^a-zA-Z0-9]/', '')->lower()->limit(15, '')->toString();

        if (Str::length($base) < 4) {
            $base = Str::padRight($base, 4, '0');
        }

        $username = $base;
        $suffix = 1;

        while (in_array($username, $taken, true) || DB::table('users')->where('username', $username)->exists()) {
            $suffix++;
            $username = Str::limit($base, 15 - Str::length((string) $suffix), '').$suffix;
        }

        return $username;
    }
};
