<?php

namespace App\Policies;

use App\Models\Hero;
use App\Models\User;

class HeroPolicy
{
    public function view(User $user, Hero $hero): bool
    {
        return $this->owns($user, $hero);
    }

    public function update(User $user, Hero $hero): bool
    {
        return $this->owns($user, $hero);
    }

    public function delete(User $user, Hero $hero): bool
    {
        return $this->owns($user, $hero);
    }

    private function owns(User $user, Hero $hero): bool
    {
        return $user->store !== null && $hero->store_id === $user->store->id;
    }
}
