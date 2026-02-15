<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesUsers;
use Tests\Traits\CreatesStock;
use App\Models\Product;
use App\Models\Category;

class ProductTest extends TestCase
{
    use CreatesUsers, CreatesStock;

    protected bool $seed = true;

    /** @test */
    public function user_can_view_products_list(): void
    {
        $user = $this->createAdmin();
        $product = $this->createProduct();

        $response = $this->actingAs($user)->get('/products');

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /** @test */
    public function user_can_view_product_details(): void
    {
        $user = $this->createAdmin();
        $product = $this->createProduct();

        $response = $this->actingAs($user)->get("/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->description);
    }

    /** @test */
    public function user_can_create_product(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();

        $productData = [
            'name' => 'Japona Militar',
            'description' => 'Japona para uso em campanha',
            'category_id' => $category->id,
            'unit' => 'un',
            'minimum_stock' => 10,
            'is_serialized' => false,
            'is_durable' => false,
            'shelf_life_months' => 24,
            'siscofis_code' => '123456',
        ];

        $response = $this->actingAs($user)->post('/products', $productData);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Japona Militar',
            'siscofis_code' => '123456',
        ]);
    }

    /** @test */
    public function user_can_update_product(): void
    {
        $user = $this->createAdmin();
        $product = $this->createProduct();

        $updatedData = [
            'name' => 'Produto Atualizado',
            'description' => 'Descrição atualizada',
            'category_id' => $product->category_id,
            'unit' => 'pç',
            'minimum_stock' => 20,
            'is_serialized' => true,
            'is_durable' => true,
            'shelf_life_months' => 36,
            'siscofis_code' => '654321',
        ];

        $response = $this->actingAs($user)->put("/products/{$product->id}", $updatedData);

        $response->assertRedirect("/products/{$product->id}");
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Produto Atualizado',
            'siscofis_code' => '654321',
        ]);
    }

    /** @test */
    public function user_can_delete_product_without_stock(): void
    {
        $user = $this->createAdmin();
        $product = $this->createProduct();

        $response = $this->actingAs($user)->delete("/products/{$product->id}");

        $response->assertRedirect('/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function user_cannot_delete_product_with_stock(): void
    {
        $user = $this->createAdmin();
        $product = $this->createProduct();
        $this->createStockItem(['product_id' => $product->id]);

        $response = $this->actingAs($user)->delete("/products/{$product->id}");

        // Deve falhar  ou redirecionar com erro
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /** @test */
    public function product_validation_requires_name(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'category_id' => $category->id,
            'unit' => 'un',
            'minimum_stock' => 10,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function product_validation_requires_category(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Produto Teste',
            'unit' => 'un',
            'minimum_stock' => 10,
        ]);

        $response->assertSessionHasErrors(['category_id']);
    }

    /** @test */
    public function product_validation_requires_unit(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Produto Teste',
            'category_id' => $category->id,
            'minimum_stock' => 10,
        ]);

        $response->assertSessionHasErrors(['unit']);
    }

    /** @test */
    public function product_can_be_serialized(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();

        $productData = [
            'name' => 'Equipamento Serializado',
            'category_id' => $category->id,
            'unit' => 'un',
            'minimum_stock' => 5,
            'is_serialized' => true,
        ];

        $response = $this->actingAs($user)->post('/products', $productData);

        $this->assertDatabaseHas('products', [
            'name' => 'Equipamento Serializado',
            'is_serialized' => true,
        ]);
    }

    /** @test */
    public function product_can_be_durable(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();

        $productData = [
            'name' => 'Material Durável',
            'category_id' => $category->id,
            'unit' => 'un',
            'minimum_stock' => 5,
            'is_durable' => true,
        ];

        $response = $this->actingAs($user)->post('/products', $productData);

        $this->assertDatabaseHas('products', [
            'name' => 'Material Durável',
            'is_durable' => true,
        ]);
    }

    /** @test */
    public function product_shelf_life_affects_stock_expiration(): void
    {
        $product = $this->createProduct([
            'shelf_life_months' => 12,
        ]);

        $stockItem = $this->createStockItem([
            'product_id' => $product->id,
            'siscofis_entry_date' => now(),
            'expiration_date' => null, // Auto-calculado
        ]);

        $stockItem->refresh();

        $this->assertNotNull($stockItem->expiration_date);
        $this->assertEquals(
            now()->addMonths(12)->format('Y-m-d'),
            $stockItem->expiration_date->format('Y-m-d')
        );
    }
}
