<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('infrawatch:start-due-runs')->everyMinute()->withoutOverlapping();
