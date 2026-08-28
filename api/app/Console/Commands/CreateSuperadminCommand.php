<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class CreateSuperadminCommand extends Command
{
    protected $signature = 'superadmin:create
                            {--name= : Name of the superadmin}
                            {--username= : Username used to log in}
                            {--email= : Email used to log in}
                            {--password= : Plain password}';

    protected $description = 'Create a superadmin account for the platform panel';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name');
        $username = $this->option('username') ?: $this->ask('Username');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password');

        $validator = Validator::make(
            ['name' => $name, 'username' => $username, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'username' => [
                    'required',
                    'string',
                    'alpha_num:ascii',
                    'min:4',
                    'max:15',
                    Rule::unique('users', 'username')->ignore(User::where('email', $email)->value('id')),
                ],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', Password::defaults()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        Role::findOrCreate(UserRole::Superadmin->value, 'web');

        $user = User::where('email', $email)->first();

        if ($user) {
            if (! $this->confirm("The user {$email} already exists. Promote it to superadmin and reset its password?")) {
                return self::FAILURE;
            }

            $user->update(['name' => $name, 'username' => $username, 'password' => $password]);
        } else {
            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => $password,
            ]);
        }

        $user->syncRoles([UserRole::Superadmin->value]);

        $this->info("Superadmin ready: {$email}");

        return self::SUCCESS;
    }
}
