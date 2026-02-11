<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Tarefas agendadas ───────────────────────────────────────────

// Verifica empréstimos vencidos diariamente às 06:00
Schedule::command('loans:check-overdue')->dailyAt('06:00');
