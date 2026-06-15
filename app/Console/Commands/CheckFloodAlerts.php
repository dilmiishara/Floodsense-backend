<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FloodNotificationService;

class CheckFloodAlerts extends Command
{
    protected $signature   = 'flood:check-alerts';
    protected $description = 'Check for new flood alerts and send notifications';

    public function handle()
    {
        $this->info('Checking for new flood alerts...');
        $service = new FloodNotificationService();
        $service->checkAndNotify();
        $this->info('Done!');
    }
}
