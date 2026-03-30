<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WaterLevelFetchService;
use App\Services\WeatherFetchService;

class FetchAllSensorData extends Command
{
    protected $signature   = 'sensors:fetch';
    protected $description = 'Fetch and store water level + weather data';

    public function handle(
        WaterLevelFetchService $waterService,
        WeatherFetchService    $weatherService
    ) {
        $this->info('--- Fetching Water Level Data ---');
        $count = $waterService->fetchAndStore();
        $count > 0
            ? $this->info("✅ Saved {$count} station records.")
            : $this->error('❌ Water level fetch failed.');

        $this->info('--- Fetching Weather Data ---');
        $ok = $weatherService->fetchAndStore();
        $ok
            ? $this->info('✅ Weather data saved.')
            : $this->error('❌ Weather fetch failed.');
    }
}