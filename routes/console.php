<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sensors:fetch')->everyMinute()->withoutOverlapping();

// check flood alerts for every 10 minutes
Schedule::command('flood:check-alerts')->everyTenMinutes();
