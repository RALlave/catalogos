<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WaitlistEntry;

class WaitlistEntryPolicy
{
    public function view(User $user, WaitlistEntry $entry): bool
    {
        return $this->owns($user, $entry);
    }

    public function update(User $user, WaitlistEntry $entry): bool
    {
        return $this->owns($user, $entry);
    }

    public function delete(User $user, WaitlistEntry $entry): bool
    {
        return $this->owns($user, $entry);
    }

    private function owns(User $user, WaitlistEntry $entry): bool
    {
        return $user->store !== null && $entry->store_id === $user->store->id;
    }
}
