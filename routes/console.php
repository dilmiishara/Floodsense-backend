<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sensors:fetch')->everyMinute()->withoutOverlapping();