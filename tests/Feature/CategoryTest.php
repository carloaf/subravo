<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesUsers;
use Tests\Traits\CreatesStock;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use CreatesUsers, CreatesStock;

    protected bool $seed = true;

    /** @test */
    public function user_can_view_categories_list(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->get('/categories');

        $response->assertStatus(200);
        $response->assertSee($category->name);
    }

    /** @test */
    public function user_can_create_category(): void
    {
        $user = $this->createAdmin();

        $categoryData = [
            'name' => 'Categoria Teste',
            'description' => 'Descrição da categoria',
        ];

        $response = $this->actingAs($user)->post('/categories', $categoryData);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'name' => 'Categoria Teste',
        ]);
    }

    /** @test */
    public function user_can_update_category(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();

        $updatedData = [
            'name' => 'Categoria Atualizada',
            'description' => 'Descrição atualizada',
        ];

        $response = $this->actingAs($user)->put("/categories/{$category->id}", $updatedData);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Categoria Atualizada',
        ]);
    }

    /** @test */
    public function user_can_delete_category_without_products(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->delete("/categories/{$category->id}");

        $response->assertRedirect('/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function user_cannot_delete_category_with_products(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory();
        $this->createProduct(['category_id' => $category->id]);

        $response = $this->actingAs($user)->delete("/categories/{$category->id}");

        // Deve falhar ou redirecionar com erro
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function category_validation_requires_name(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->post('/categories', [
            'description' => 'Descrição',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function category_name_must_be_unique(): void
    {
        $user = $this->createAdmin();
        $category = $this->createCategory(['name' => 'Categoria Única']);

        $response = $this->actingAs($user)->post('/categories', [
            'name' => 'Categoria Única',
            'description' => 'Tentando duplicar',
        ]);

        $response->assertSessionHasErrors(['name']);
    }
}
