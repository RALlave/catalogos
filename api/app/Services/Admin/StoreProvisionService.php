<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use App\Services\StoreService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StoreProvisionService
{
    public function __construct(private readonly StoreService $stores) {}

    /**
     * Create the owner account and its store in a single transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function provision(array $data): Store
    {
        return DB::transaction(function () use ($data): Store {
            $user = User::create([
                'name' => $data['owner_name'],
                'username' => $data['owner_username'],
                'email' => $data['owner_email'],
                'password' => $data['owner_password'],
            ]);

            $user->assignRole(UserRole::StoreOwner->value);

            return $this->stores->create(
                $user,
                Arr::except($data, ['owner_name', 'owner_username', 'owner_email', 'owner_password']),
            );
        });
    }
}
