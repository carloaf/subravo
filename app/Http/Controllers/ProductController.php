<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Listagem de produtos com filtros e busca.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('siscofis_code', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type')) {
            $query->where('is_durable', $request->type === 'durable');
        }

        $products   = $query->orderBy('name')->paginate(20)->appends($request->query());
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Formulário de criação de produto.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $units      = ['UN', 'CX', 'PCT', 'KG', 'L', 'M', 'PAR', 'JG', 'RL', 'FL'];

        return view('products.create', compact('categories', 'units'));
    }

    /**
     * Salva novo produto.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'category_id'   => 'required|exists:categories,id',
            'unit'          => 'required|string|max:20',
            'minimum_stock' => 'required|integer|min:0',
            'is_serialized' => 'boolean',
            'siscofis_code' => 'nullable|string|max:50',
            'shelf_life_months' => 'nullable|integer|min:1',
            'is_durable'    => 'boolean',
        ]);

        $validated['is_serialized'] = $request->boolean('is_serialized');
        $validated['is_durable'] = $request->boolean('is_durable');

        try {
            $product = Product::create($validated);

            return redirect()
                ->route('products.show', $product)
                ->with('success', "Produto \"{$product->name}\" cadastrado com sucesso.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar produto. Tente novamente.');
        }
    }

    /**
     * Exibe detalhes do produto com itens de estoque.
     */
    public function show(Product $product)
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    /**
     * Formulário de edição do produto.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $units      = ['UN', 'CX', 'PCT', 'KG', 'L', 'M', 'PAR', 'JG', 'RL', 'FL'];

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    /**
     * Atualiza produto existente.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'category_id'   => 'required|exists:categories,id',
            'unit'          => 'required|string|max:20',
            'minimum_stock' => 'required|integer|min:0',
            'is_serialized' => 'boolean',
            'siscofis_code' => 'nullable|string|max:50',
            'shelf_life_months' => 'nullable|integer|min:1',
            'is_durable'    => 'boolean',
        ]);

        $validated['is_serialized'] = $request->boolean('is_serialized');
        $validated['is_durable'] = $request->boolean('is_durable');

        try {
            $product->update($validated);

            return redirect()
                ->route('products.show', $product)
                ->with('success', "Produto \"{$product->name}\" atualizado com sucesso.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar produto. Tente novamente.');
        }
    }

    /**
     * Remove produto (somente se não houver itens de estoque vinculados).
     */
    public function destroy(Product $product)
    {
        if ($product->stockItems()->exists()) {
            return back()->with('error', 'Não é possível excluir produto com itens de estoque vinculados.');
        }

        try {
            $name = $product->name;
            $product->delete();

            return redirect()
                ->route('products.index')
                ->with('success', "Produto \"{$name}\" removido com sucesso.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover produto.');
        }
    }

    // ─── Categorias ──────────────────────────────────────────────

    /**
     * Listagem de categorias.
     */
    public function categories()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('products.categories', compact('categories'));
    }

    /**
     * Salva nova categoria.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $category = Category::create($validated);

            return redirect()
                ->route('categories.index')
                ->with('success', "Categoria \"{$category->name}\" criada com sucesso.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar categoria.');
        }
    }

    /**
     * Atualiza categoria existente.
     */
    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $category->update($validated);

            return redirect()
                ->route('categories.index')
                ->with('success', "Categoria \"{$category->name}\" atualizada com sucesso.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar categoria.');
        }
    }

    /**
     * Remove categoria (somente se não houver produtos vinculados).
     */
    public function destroyCategory(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Não é possível excluir categoria com produtos vinculados.');
        }

        try {
            $name = $category->name;
            $category->delete();

            return redirect()
                ->route('categories.index')
                ->with('success', "Categoria \"{$name}\" removida com sucesso.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover categoria.');
        }
    }
}
