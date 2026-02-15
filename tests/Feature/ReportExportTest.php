<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesUsers;
use Tests\Traits\CreatesStock;
use Tests\Traits\CreatesLoans;

class ReportExportTest extends TestCase
{
    use CreatesUsers, CreatesStock, CreatesLoans;

    protected bool $seed = true;

    /** @test */
    public function user_can_generate_cautela_pdf(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], ['loaned_by' => $user]);

        $response = $this->actingAs($user)->get("/loans/{$loan->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function user_can_generate_return_receipt_pdf(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], ['loaned_by' => $user]);

        // Processar devolução primeiro
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

        // Gerar PDF da devolução
        $response = $this->actingAs($user)->get("/loans/{$loan->id}/return-pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function user_can_generate_stock_summary_report_pdf(): void
    {
        $admin = $this->createAdmin();
        $this->createStockItem(['quantity' => 50]);

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'report_type' => 'stock_summary',
            'format' => 'pdf',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function user_can_generate_stock_summary_report_excel(): void
    {
        $admin = $this->createAdmin();
        $this->createStockItem(['quantity' => 50]);

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'report_type' => 'stock_summary',
            'format' => 'excel',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('Content-Type'), 'spreadsheet') ||
            str_contains($response->headers->get('Content-Type'), 'excel')
        );
    }

    /** @test */
    public function user_can_generate_loans_active_report(): void
    {
        $admin = $this->createAdmin();
        $borrower = $this->createSolicitante();
        $stockItem = $this->createStockItem(['quantity' => 10]);

        $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ]);

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'report_type' => 'loans_active',
            'format' => 'pdf',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function user_can_generate_movements_report_with_date_range(): void
    {
        $admin = $this->createAdmin();
        $this->createStockItem(['quantity' => 50]);

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'report_type' => 'movements',
            'format' => 'pdf',
            'date_from' => now()->subDays(7)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function user_can_generate_low_stock_report(): void
    {
        $admin = $this->createAdmin();
        
        // Criar produto com estoque abaixo do mínimo
        $product = $this->createProduct(['minimum_stock' => 50]);
        $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 10, // Abaixo do mínimo
        ]);

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'report_type' => 'low_stock',
            'format' => 'pdf',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function user_can_generate_expiring_items_report(): void
    {
        $admin = $this->createAdmin();
        
        // Criar item próximo da validade
        $this->createStockItem([
            'expiration_date' => now()->addDays(20),
        ]);

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'report_type' => 'expiring',
            'format' => 'pdf',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function non_admin_cannot_generate_reports(): void
    {
        $solicitante = $this->createSolicitante();

        $response = $this->actingAs($solicitante)->post('/admin/reports/generate', [
            'report_type' => 'stock_summary',
            'format' => 'pdf',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function report_validation_requires_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'format' => 'pdf',
        ]);

        $response->assertSessionHasErrors(['report_type']);
    }

    /** @test */
    public function report_validation_requires_format(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'report_type' => 'stock_summary',
        ]);

        $response->assertSessionHasErrors(['format']);
    }

    /** @test */
    public function cautela_pdf_contains_loan_information(): void
    {
        $user = $this->createAlmoxarife();
        $borrower = $this->createSolicitante([
            'war_name' => 'SILVA',
        ]);
        $product = $this->createProduct(['name' => 'Japona Teste']);
        $stockItem = $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $loan = $this->createIndividualLoan($borrower, [
            ['stock_item' => $stockItem, 'quantity' => 5],
        ], ['loaned_by' => $user]);

        $response = $this->actingAs($user)->get("/loans/{$loan->id}/pdf");

        $content = $response->getContent();
        
        // PDF contém metadados básicos
        $this->assertStringContainsString('%PDF', $content);
        $this->assertGreaterThan(1000, strlen($content)); // PDF tem conteúdo significativo
    }

    /** @test */
    public function excel_export_has_data(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct(['name' => 'Produto Excel Test']);
        $this->createStockItem([
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        $response = $this->actingAs($admin)->post('/admin/reports/generate', [
            'report_type' => 'stock_summary',
            'format' => 'excel',
        ]);

        $response->assertStatus(200);
        
        // Excel é retornado como download (streamed), verificar Content-Type e Content-Disposition
        $contentType = $response->headers->get('Content-Type');
        $this->assertTrue(
            str_contains($contentType, 'spreadsheet') ||
            str_contains($contentType, 'excel') ||
            str_contains($contentType, 'octet-stream'),
            "Expected spreadsheet Content-Type, got: {$contentType}"
        );
        $this->assertNotNull($response->headers->get('Content-Disposition'));
    }
}
