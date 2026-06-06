<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('notifications:send-deadline-reminders')->everyTenMinutes();
Schedule::command('notifications:process-overdue')->everyFiveMinutes();
