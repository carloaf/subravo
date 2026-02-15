<?php

namespace Tests\Traits;

use App\Models\Product;
use App\Models\Category;
use App\Models\StockItem;

trait CreatesStock
{
    /**
     * Criar categoria para testes.
     */
    protected function createCategory(array $attributes = []): Category
    {
        return Category::create(array_merge([
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
        ], $attributes));
    }

    /**
     * Criar produto para testes.
     */
    protected function createProduct(array $attributes = []): Product
    {
        $category = Category::first() ?? $this->createCategory();

        return Product::create(array_merge([
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category_id' => $category->id,
            'unit' => 'un',
            'minimum_stock' => 10,
            'is_serialized' => false,
            'is_durable' => false,
            'shelf_life_months' => null,
            'siscofis_code' => null,
        ], $attributes));
    }

    /**
     * Criar item de estoque para testes.
     */
    protected function createStockItem(array $attributes = []): StockItem
    {
        $product = $attributes['product_id'] ?? null;
        if (!$product) {
            $product = $this->createProduct();
            $attributes['product_id'] = $product->id;
        } elseif (is_numeric($product)) {
            $product = Product::find($product);
        }

        return StockItem::create(array_merge([
            'serial_number' => null,
            'batch' => 'LOTE-' . fake()->numerify('####'),
            'expiration_date' => now()->addMonths(12),
            'siscofis_entry_date' => now()->subDays(30),
            'location' => 'Prateleira ' . fake()->numberBetween(1, 20),
            'subunit' => null,
            'quantity' => 50,
            'status' => 'available',
            'notes' => null,
        ], $attributes));
    }

    /**
     * Criar produto serializado com item de estoque.
     */
    protected function createSerializedProduct(int $quantity = 1): array
    {
        $product = $this->createProduct([
            'is_serialized' => true,
        ]);

        $items = [];
        for ($i = 0; $i < $quantity; $i++) {
            $items[] = $this->createStockItem([
                'product_id' => $product->id,
                'serial_number' => 'SN-' . fake()->unique()->numerify('######'),
                'quantity' => 1,
            ]);
        }

        return [
            'product' => $product,
            'items' => $items,
        ];
    }
}
