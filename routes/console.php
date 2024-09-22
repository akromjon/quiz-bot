<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:check-user-tariff-command')->everyMinute();
Schedule::command('backup:sqlite')->environments(['production'])->hourly();

