<?php

namespace App\Console\Commands;

use App\Services\StoreTrashService;
use Illuminate\Console\Command;

/**
 * Empties the store trash: every store that has been there longer than the
 * days set in the superadmin settings is deleted for good, owner and files
 * included. Runs once a day from the scheduler.
 */
class PurgeTrashedStores extends Command
{
    protected $signature = 'stores:purge-trash';

    protected $description = 'Permanently delete the stores whose time in the trash is over';

    public function handle(StoreTrashService $trash): int
    {
        $purged = $trash->purgeExpired();

        $this->info(sprintf('%d store(s) deleted.', $purged));

        return self::SUCCESS;
    }
}
