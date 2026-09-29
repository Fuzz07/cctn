<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class ExpireClientSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark client accounts inactive when their active subscription has expired';

    public function handle(): int
    {
        $count = Client::expireSubscriptions();
        $this->info("Expired {$count} client subscription(s).");

        return self::SUCCESS;
    }
}
