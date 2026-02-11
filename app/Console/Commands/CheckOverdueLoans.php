<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class CheckOverdueLoans extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'loans:check-overdue';

    /**
     * The console command description.
     */
    protected $description = 'Marca empréstimos ativos vencidos como "overdue"';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = Loan::where('status', 'active')
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', now())
            ->update(['status' => 'overdue']);

        if ($count > 0) {
            $this->info("✓ {$count} cautela(s) marcada(s) como vencida(s).");
        } else {
            $this->info('Nenhuma cautela vencida encontrada.');
        }

        return self::SUCCESS;
    }
}
