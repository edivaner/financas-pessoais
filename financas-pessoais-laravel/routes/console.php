<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ResetMonthlyLimitsCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ResetMonthlyLimitsCommand::class)
    ->monthlyOn(1, '00:05')
    ->timezone('America/Sao_Paulo');
