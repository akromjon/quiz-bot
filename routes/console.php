<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:check-user-tariff-command')->hourly();
Schedule::command('backup:sqlite')->environments(['production'])->hourly();
Schedule::command('app:check-telegram-user-status-command')->hourly();



