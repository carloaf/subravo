<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesUsers;
use Tests\Traits\CreatesStock;
use Tests\Traits\CreatesLoans;
use App\Models\StockItem;
use App\Models\Product;
use App\Models\Loan;

class BusinessRulesTest extends TestCase
{
    use CreatesUsers, CreatesStock, CreatesLoans;

    protected bool $seed = true;

    /** @test */
    public function stock_quantity_cannot_go_negative(): void
    {
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $this->expectException(\DomainException::class);

        $stockItem->quantity = -5;
        $stockItem->save();
    }

    /** @test */
    public function product_is_below_minimum_when_stock_is_low(): void
    {
        $product = $this->createProduct(['minimum_stock' => 20]);
        $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->assertTrue($product->isBelowMinimum());
    }

    /** @test */
    public function product_is_not_below_minimum_when_stock_is_adequate(): void
    {
        $product = $this->createProduct(['minimum_stock' => 20]);
        $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 25,
        ]);

        $this->assertFalse($product->isBelowMinimum());
    }

    /** @test */
    public function stock_item_status_changes_to_loaned_when_fully_loaned(): void
    {
        $user = $this->createUser();
        $borrower = $this->createUser();
        $stockItem = $this->createStockItem(['quantity' => 5, 'status' => 'available']);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], ['loaned_by' => $user]);

        $stockItem->refresh();
        $this->assertEquals('loaned', $stockItem->status);
        $this->assertEquals(0, $stockItem->quantity);
    }

    /** @test */
    public function stock_item_status_remains_available_when_partially_loaned(): void
    {
        $user = $this->createUser();
        $borrower = $this->createUser();
        $stockItem = $this->createStockItem(['quantity' => 10, 'status' => 'available']);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], ['loaned_by' => $user]);

        $stockItem->refresh();
        $this->assertEquals('available', $stockItem->status);
        $this->assertEquals(5, $stockItem->quantity);
    }

    /** @test */
    public function loan_status_changes_to_returned_after_full_return(): void
    {
        $user = $this->createUser();
        $borrower = $this->createUser();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], ['loaned_by' => $user]);

        // Processar devolução total
        $returnData = [
            'returns' => [
                [
                    'loan_item_id' => $loan->items->first()->id,
                    'quantity' => 5,
                    'condition_in' => 'bom',
                ],
            ],
        ];

        $this->actingAs($user)->post("/loans/{$loan->id}/return", $returnData);

        $loan->refresh();
        $this->assertEquals('returned', $loan->status);
        $this->assertNotNull($loan->actual_return_date);
    }

    /** @test */
    public function loan_status_changes_to_partial_after_partial_return(): void
    {
        $user = $this->createUser();
        $borrower = $this->createUser();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 10],
        ], ['loaned_by' => $user]);

        // Processar devolução parcial
        $returnData = [
            'returns' => [
                [
                    'loan_item_id' => $loan->items->first()->id,
                    'quantity' => 5,
                    'condition_in' => 'bom',
                ],
            ],
        ];

        $this->actingAs($user)->post("/loans/{$loan->id}/return", $returnData);

        $loan->refresh();
        $this->assertEquals('partial', $loan->status);
        $this->assertNull($loan->actual_return_date);
    }

    /** @test */
    public function stock_is_restored_after_loan_return(): void
    {
        $user = $this->createUser();
        $borrower = $this->createUser();
        $stockItem = $this->createStockItem(['quantity' => 20]);

        $initialQuantity = $stockItem->quantity;

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 10],
        ], ['loaned_by' => $user]);

        $stockItem->refresh();
        $this->assertEquals(10, $stockItem->quantity);

        // Processar devolução
        $returnData = [
            'returns' => [
                [
                    'loan_item_id' => $loan->items->first()->id,
                    'quantity' => 10,
                    'condition_in' => 'bom',
                ],
            ],
        ];

        $this->actingAs($user)->post("/loans/{$loan->id}/return", $returnData);

        $stockItem->refresh();
        $this->assertEquals($initialQuantity, $stockItem->quantity);
    }

    /** @test */
    public function expiration_date_is_auto_calculated_from_shelf_life(): void
    {
        $product = $this->createProduct([
            'shelf_life_months' => 18,
        ]);

        $entryDate = now()->startOfDay();
        
        $stockItem = $this->createStockItem([
            'product_id' => $product->id,
            'siscofis_entry_date' => $entryDate,
            'expiration_date' => null,
        ]);

        $stockItem->refresh();

        $expectedDate = $entryDate->copy()->addMonths(18);
        
        $this->assertNotNull($stockItem->expiration_date);
        $this->assertEquals(
            $expectedDate->format('Y-m-d'),
            $stockItem->expiration_date->format('Y-m-d')
        );
    }

    /** @test */
    public function serialized_products_require_serial_numbers(): void
    {
        $product = $this->createProduct([
            'is_serialized' => true,
        ]);

        $this->assertTrue($product->is_serialized);
    }

    /** @test */
    public function durable_products_are_tracked_separately(): void
    {
        $durableProduct = $this->createProduct([
            'is_durable' => true,
            'name' => 'Ferramenta Durável',
        ]);

        $consumableProduct = $this->createProduct([
            'is_durable' => false,
            'name' => 'Material Consumível',
        ]);

        $this->assertTrue($durableProduct->is_durable);
        $this->assertFalse($consumableProduct->is_durable);
    }

    /** @test */
    public function loan_number_follows_correct_pattern(): void
    {
        $loanNumber = Loan::generateLoanNumber();

        $this->assertMatchesRegularExpression(
            '/^CAUTELA-\d{4}-\d{6}$/',
            $loanNumber
        );
    }

    /** @test */
    public function loan_numbers_are_sequential(): void
    {
        $borrower = $this->createUser();
        $stockItem = $this->createStockItem(['quantity' => 100]);

        $loan1 = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ]);

        $loan2 = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ]);

        // Extrair números sequenciais
        preg_match('/CAUTELA-\d{4}-(\d{6})/', $loan1->loan_number, $matches1);
        preg_match('/CAUTELA-\d{4}-(\d{6})/', $loan2->loan_number, $matches2);

        $seq1 = (int) $matches1[1];
        $seq2 = (int) $matches2[1];

        $this->assertEquals($seq1 + 1, $seq2);
    }

    /** @test */
    public function pending_quantity_is_calculated_correctly(): void
    {
        $user = $this->createUser();
        $borrower = $this->createUser();
        $stockItem = $this->createStockItem(['quantity' => 20]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 10],
        ], ['loaned_by' => $user]);

        $loanItem = $loan->items->first();

        $this->assertEquals(10, $loanItem->getPendingQuantity());
        $this->assertFalse($loanItem->isFullyReturned());

        // Atualizar quantidade devolvida
        $loanItem->returned_quantity = 6;
        $loanItem->save();

        $this->assertEquals(4, $loanItem->getPendingQuantity());
        $this->assertFalse($loanItem->isFullyReturned());
        $this->assertTrue($loanItem->isPartiallyReturned());

        // Devolução total
        $loanItem->returned_quantity = 10;
        $loanItem->save();

        $this->assertEquals(0, $loanItem->getPendingQuantity());
        $this->assertTrue($loanItem->isFullyReturned());
    }

    /** @test */
    public function available_stock_excludes_loaned_items(): void
    {
        $product = $this->createProduct();
        
        $availableItem = $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 50,
            'status' => 'available',
        ]);

        $loanedItem = $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 30,
            'status' => 'loaned',
        ]);

        $availableStock = $product->getAvailableStock();
        $this->assertEquals(50, $availableStock);
    }

    /** @test */
    public function loaned_stock_only_counts_loaned_items(): void
    {
        $product = $this->createProduct();
        
        $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 50,
            'status' => 'available',
        ]);

        $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 30,
            'status' => 'loaned',
        ]);

        $loanedStock = $product->getLoanedStock();
        $this->assertEquals(30, $loanedStock);
    }
}
