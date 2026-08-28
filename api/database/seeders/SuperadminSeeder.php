<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('superadmin.email');
        $password = config('superadmin.password');

        if (! $email || ! $password) {
            $this->command?->warn('Superadmin skipped: set SUPERADMIN_EMAIL and SUPERADMIN_PASSWORD in your .env file.');

            return;
        }

        // updateOrCreate so changing the .env credentials is enough to reset the account.
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('superadmin.name'),
                'username' => config('superadmin.username'),
                'password' => $password,
            ],
        );

        $user->syncRoles([UserRole::Superadmin->value]);

        $this->command?->info("Superadmin ready: {$email}");
    }
}
