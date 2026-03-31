<?php

namespace App\Console\Commands;

use App\Models\StockItem;
use Illuminate\Console\Command;

class BackfillStockUnitCost extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stock:backfill-unit-cost {--dry-run : Apenas mostra quantos registros seriam atualizados}';

    /**
     * The console command description.
     */
    protected $description = 'Preenche unit_cost nulo em lotes antigos a partir do inventário SISCOFIS';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;
        $skipped = 0;
        $dryRun = (bool) $this->option('dry-run');

        StockItem::query()
            ->withoutGlobalScopes()
            ->with('product')
            ->whereNull('unit_cost')
            ->orderBy('id')
            ->chunkById(200, function ($items) use (&$updated, &$skipped, $dryRun) {
                foreach ($items as $item) {
                    $resolvedUnitCost = $item->getResolvedUnitCost();

                    if ($resolvedUnitCost === null) {
                        $skipped++;
                        continue;
                    }

                    if (!$dryRun) {
                        $item->forceFill([
                            'unit_cost' => $resolvedUnitCost,
                        ])->saveQuietly();
                    }

                    $updated++;
                }
            });

        if ($dryRun) {
            $this->info("Dry run: {$updated} item(ns) receberiam unit_cost; {$skipped} permaneceriam sem correspondência.");
            return self::SUCCESS;
        }

        $this->info("Backfill concluído: {$updated} item(ns) atualizados; {$skipped} sem correspondência.");

        return self::SUCCESS;
    }
}