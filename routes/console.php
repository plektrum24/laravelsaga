<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Phase 22 - SaaS Management Scheduled Commands
Schedule::command('saas:process-recurring')
    ->dailyAt('02:00')
    ->description('Process recurring subscription payments and generate invoices');

Schedule::command('saas:check-overdue')
    ->dailyAt('03:00')
    ->description('Check and mark overdue invoices');

// Monthly subscription renewal processing
Schedule::command('saas:process-recurring')
    ->monthlyOn(1, '01:00')
    ->description('Monthly subscription renewal processing');
