<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesUsers;
use Tests\Traits\CreatesStock;
use Tests\Traits\CreatesLoans;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\StockMovement;

class LoanTest extends TestCase
{
    use CreatesUsers, CreatesStock, CreatesLoans;

    protected bool $seed = true;

    /** @test */
    public function user_can_view_loans_list(): void
    {
        $user = $this->createAdmin();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ]);

        $response = $this->actingAs($user)->get('/loans');

        $response->assertStatus(200);
        $response->assertSee($loan->loan_number);
    }

    /** @test */
    public function user_can_view_loan_details(): void
    {
        $user = $this->createAdmin();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ]);

        $response = $this->actingAs($user)->get("/loans/{$loan->id}");

        $response->assertStatus(200);
        $response->assertSee($loan->loan_number);
        $response->assertSee($borrower->war_name);
    }

    /** @test */
    public function user_can_create_individual_loan(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loanData = [
            'borrower_type' => 'individual',
            'borrower_user_id' => $borrower->id,
            'expected_return_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Empréstimo de teste',
            'items' => [
                [
                    'stock_item_id' => $stockItem->id,
                    'quantity' => 5,
                    'condition_out' => 'bom',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post('/loans', $loanData);

        $response->assertRedirect();
        $this->assertDatabaseHas('loans', [
            'borrower_type' => 'individual',
            'borrower_user_id' => $borrower->id,
            'status' => 'active',
        ]);

        // Verificar que o estoque foi decrementado
        $this->assertDatabaseHas('stock_items', [
            'id' => $stockItem->id,
            'quantity' => 5,
        ]);

        // Verificar movimento de estoque
        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'movement_type' => 'loan',
            'quantity' => -5,
        ]);
    }

    /** @test */
    public function user_can_create_section_loan(): void
    {
        $user = $this->createAlmoxarife();
        $organization = $this->createOrganization();
        $stockItem = $this->createStockItem(['quantity' => 20]);

        $loanData = [
            'borrower_type' => 'section',
            'borrower_section' => '1ª Seção',
            'borrower_organization_id' => $organization->id,
            'expected_return_date' => now()->addDays(14)->format('Y-m-d'),
            'items' => [
                [
                    'stock_item_id' => $stockItem->id,
                    'quantity' => 10,
                    'condition_out' => 'bom',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post('/loans', $loanData);

        $response->assertRedirect();
        $this->assertDatabaseHas('loans', [
            'borrower_type' => 'section',
            'borrower_section' => '1ª Seção',
            'borrower_organization_id' => $organization->id,
        ]);
    }

    /** @test */
    public function loan_number_is_auto_generated(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], ['loaned_by' => $user]);

        $this->assertNotNull($loan->loan_number);
        $this->assertStringStartsWith('CAUTELA-', $loan->loan_number);
        $this->assertMatchesRegularExpression('/^CAUTELA-\d{4}-\d{6}$/', $loan->loan_number);
    }

    /** @test */
    public function loan_validation_requires_borrower(): void
    {
        $user = $this->createAlmoxarife();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loanData = [
            'borrower_type' => 'individual',
            // Sem borrower_user_id
            'items' => [
                [
                    'stock_item_id' => $stockItem->id,
                    'quantity' => 5,
                    'condition_out' => 'bom',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post('/loans', $loanData);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function loan_validation_requires_items(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();

        $loanData = [
            'borrower_type' => 'individual',
            'borrower_user_id' => $borrower->id,
            'items' => [], // Sem itens
        ];

        $response = $this->actingAs($user)->post('/loans', $loanData);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function cannot_loan_more_than_available_stock(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loanData = [
            'borrower_type' => 'individual',
            'borrower_user_id' => $borrower->id,
            'items' => [
                [
                    'stock_item_id' => $stockItem->id,
                    'quantity' => 15, // Mais que o disponível
                    'condition_out' => 'bom',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post('/loans', $loanData);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function cannot_loan_expired_items(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $expiredItem = $this->createStockItem([
            'quantity' => 10,
            'expiration_date' => now()->subDays(1),
        ]);

        $loanData = [
            'borrower_type' => 'individual',
            'borrower_user_id' => $borrower->id,
            'items' => [
                [
                    'stock_item_id' => $expiredItem->id,
                    'quantity' => 5,
                    'condition_out' => 'bom',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post('/loans', $loanData);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_can_process_full_return(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ]);

        $stockItem->refresh();
        $this->assertEquals(5, $stockItem->quantity);

        $returnData = [
            'returns' => [
                [
                    'loan_item_id' => $loan->items->first()->id,
                    'quantity' => 5,
                    'condition_in' => 'bom',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post("/loans/{$loan->id}/return", $returnData);

        $response->assertRedirect("/loans/{$loan->id}");
        
        $loan->refresh();
        $this->assertEquals('returned', $loan->status);
        $this->assertNotNull($loan->actual_return_date);

        // Verificar que o estoque foi restaurado
        $stockItem->refresh();
        $this->assertEquals(10, $stockItem->quantity);
        $this->assertEquals('available', $stockItem->status);

        // Verificar movimento de devolução
        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'movement_type' => 'return',
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function user_can_process_partial_return(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 10],
        ]);

        $returnData = [
            'returns' => [
                [
                    'loan_item_id' => $loan->items->first()->id,
                    'quantity' => 5, // Devolvendo apenas metade
                    'condition_in' => 'bom',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post("/loans/{$loan->id}/return", $returnData);

        $loan->refresh();
        $this->assertEquals('partial', $loan->status);
        $this->assertNull($loan->actual_return_date); // Ainda não totalmente devolvido

        // Verificar quantidade devolvida no item
        $loanItem = $loan->items->first();
        $this->assertEquals(5, $loanItem->returned_quantity);
        $this->assertEquals(5, $loanItem->getPendingQuantity());
    }

    /** @test */
    public function cannot_return_more_than_loaned(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ]);

        $returnData = [
            'returns' => [
                [
                    'loan_item_id' => $loan->items->first()->id,
                    'quantity' => 10, // Mais que o emprestado
                    'condition_in' => 'bom',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post("/loans/{$loan->id}/return", $returnData);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function cannot_return_already_returned_loan(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], ['status' => 'returned', 'actual_return_date' => now()]);

        $response = $this->actingAs($user)->get("/loans/{$loan->id}/return");

        $response->assertRedirect();
        $response->assertSessionHas('info');
    }

    /** @test */
    public function overdue_loans_are_detected(): void
    {
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $overdueLoan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], [
            'expected_return_date' => now()->subDays(5),
            'status' => 'active',
        ]);

        $overdueLoans = Loan::overdue()->get();

        $this->assertTrue($overdueLoans->contains($overdueLoan));
    }

    protected function createOrganization(array $attributes = [])
    {
        return \App\Models\Organization::create(array_merge([
            'name' => fake()->company(),
            'abbreviation' => fake()->lexify('???'),
            'is_host' => false,
        ], $attributes));
    }
}
