<?php

namespace Tests\Traits;

use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Models\Organization;
use App\Models\StockItem;

trait CreatesLoans
{
    /**
     * Criar empréstimo individual para testes.
     */
    protected function createIndividualLoan(User $borrower, array $items = [], array $attributes = []): Loan
    {
        $loanedBy = $attributes['loaned_by'] ?? $this->createAlmoxarife();
        if (is_object($loanedBy)) {
            $loanedBy = $loanedBy->id;
        }
        unset($attributes['loaned_by']);

        $loan = Loan::create(array_merge([
            'loan_number' => Loan::generateLoanNumber(),
            'borrower_type' => 'individual',
            'borrower_user_id' => $borrower->id,
            'borrower_section' => null,
            'borrower_organization_id' => null,
            'loaned_by' => $loanedBy,
            'loan_date' => now(),
            'expected_return_date' => now()->addDays(7),
            'actual_return_date' => null,
            'status' => 'active',
            'notes' => null,
        ], $attributes));

        // Adicionar itens ao empréstimo
        foreach ($items as $item) {
            $this->addItemToLoan($loan, $item['stock_item'], $item['quantity'] ?? 1, $item['condition'] ?? 'bom');
        }

        return $loan->fresh();
    }

    /**
     * Criar empréstimo por seção para testes.
     */
    protected function createSectionLoan(string $section, Organization $organization, array $items = [], array $attributes = []): Loan
    {
        $loanedBy = $attributes['loaned_by'] ?? $this->createAlmoxarife();
        if (is_object($loanedBy)) {
            $loanedBy = $loanedBy->id;
        }
        unset($attributes['loaned_by']);

        $loan = Loan::create(array_merge([
            'loan_number' => Loan::generateLoanNumber(),
            'borrower_type' => 'section',
            'borrower_user_id' => null,
            'borrower_section' => $section,
            'borrower_organization_id' => $organization->id,
            'loaned_by' => $loanedBy,
            'loan_date' => now(),
            'expected_return_date' => now()->addDays(7),
            'actual_return_date' => null,
            'status' => 'active',
            'notes' => null,
        ], $attributes));

        // Adicionar itens ao empréstimo
        foreach ($items as $item) {
            $this->addItemToLoan($loan, $item['stock_item'], $item['quantity'] ?? 1, $item['condition'] ?? 'bom');
        }

        return $loan->fresh();
    }

    /**
     * Adicionar item a um empréstimo.
     */
    protected function addItemToLoan(Loan $loan, StockItem $stockItem, int $quantity = 1, string $condition = 'bom'): LoanItem
    {
        // Atualizar estoque
        $stockItem->quantity -= $quantity;
        if ($stockItem->quantity === 0) {
            $stockItem->status = 'loaned';
        }
        $stockItem->save();

        // Criar item do empréstimo
        return LoanItem::create([
            'loan_id' => $loan->id,
            'stock_item_id' => $stockItem->id,
            'quantity' => $quantity,
            'returned_quantity' => 0,
            'condition_out' => $condition,
            'condition_in' => null,
        ]);
    }
}
