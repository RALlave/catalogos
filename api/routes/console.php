<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* Necesita el cron de `schedule:run` en el servidor: sin él la papelera sólo
   se vacía a mano. */
Schedule::command('stores:purge-trash')->dailyAt('03:30')->withoutOverlapping();
