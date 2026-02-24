<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ─── Guest ────────────────────────────────────────────────────────

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Cadastro de usuário (público)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

// ─── Autenticado ──────────────────────────────────────────────────

Route::middleware(['auth', 'active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Produtos ──────────────────────────────────────────────
    Route::resource('products', ProductController::class);

    // ── Categorias ────────────────────────────────────────────
    Route::get('/categories', [ProductController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [ProductController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [ProductController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [ProductController::class, 'destroyCategory'])->name('categories.destroy');

    // ── Estoque ───────────────────────────────────────────────
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/entry', [StockController::class, 'entry'])->name('stock.entry');
    Route::post('/stock/entry', [StockController::class, 'storeEntry'])->name('stock.storeEntry');
    Route::get('/stock/movements', [StockController::class, 'movements'])->name('stock.movements');
    Route::get('/stock/{stockItem}', [StockController::class, 'show'])->name('stock.show');
    Route::get('/stock/{stockItem}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::post('/stock/{stockItem}/adjust', [StockController::class, 'storeAdjust'])->name('stock.storeAdjust');
    Route::put('/stock/{stockItem}', [StockController::class, 'updateItem'])->name('stock.updateItem');
    Route::get('/stock/{stockItem}/label', [StockController::class, 'label'])->name('stock.label');
    Route::post('/stock/labels-batch', [StockController::class, 'labelsBatch'])->name('stock.labelsBatch');

    // ── Uso Duradouro ─────────────────────────────────────────
    Route::get('/durables', [StockController::class, 'durables'])->name('durables.index');
    Route::get('/durables/pdf', [StockController::class, 'durablesPdf'])->name('durables.pdf');
    Route::get('/durables/excel', [StockController::class, 'durablesExcel'])->name('durables.excel');

    // ── Debug (temporário) ────────────────────────────────────
    Route::get('/debug/forms', function () {
        return view('debug.form-test');
    })->name('debug.forms');

    // ── Empréstimos (Cautelas) ────────────────────────────────
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::get('/loans/search-borrower', [LoanController::class, 'searchBorrower'])->name('loans.searchBorrower');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::get('/loans/{loan}/return', [LoanController::class, 'returnForm'])->name('loans.return');
    Route::post('/loans/{loan}/return', [LoanController::class, 'processReturn'])->name('loans.processReturn');
    Route::get('/loans/{loan}/pdf', [LoanController::class, 'cautelaPdf'])->name('loans.pdf');
    Route::get('/loans/{loan}/return-pdf', [LoanController::class, 'returnReceiptPdf'])->name('loans.returnPdf');

    // ── Inventário (PDF SISCOFIS) ────────────────────────────
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/upload', [InventoryController::class, 'create'])->name('inventory.create');
    Route::get('/inventory/materials', [InventoryController::class, 'searchMaterials'])->name('inventory.materials');
    Route::post('/inventory/materials/export', [InventoryController::class, 'exportMaterials'])->name('inventory.materials.export');
    Route::post('/inventory/materials/hide', [InventoryController::class, 'hideItem'])->name('inventory.materials.hide');
    Route::post('/inventory/materials/reset-hidden', [InventoryController::class, 'resetHidden'])->name('inventory.materials.reset-hidden');
    Route::get('/inventory/reports', [InventoryController::class, 'reportsIndex'])->name('inventory.reports');
    Route::post('/inventory/reports/generate', [InventoryController::class, 'generateReport'])->name('inventory.reports.generate');
    Route::get('/inventory/compare', [InventoryController::class, 'compareForm'])->name('inventory.compare');
    Route::post('/inventory/compare', [InventoryController::class, 'compareResults'])->name('inventory.compare.results');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{inventoryUpload}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{inventoryUpload}/edit-location', [InventoryController::class, 'editLocation'])->name('inventory.edit-location');
    Route::patch('/inventory/{inventoryUpload}/update-location', [InventoryController::class, 'updateLocation'])->name('inventory.update-location');
    Route::get('/inventory/{inventoryUpload}/compare-durables', [InventoryController::class, 'compareDurables'])->name('inventory.compare.durables');
    Route::post('/inventory/{inventoryUpload}/sync-durables', [InventoryController::class, 'syncDurables'])->name('inventory.sync-durables');
    Route::post('/inventory/{inventoryUpload}/reprocess', [InventoryController::class, 'reprocess'])->name('inventory.reprocess');
    Route::delete('/inventory/{inventoryUpload}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::get('/inventory/{inventoryUpload}/download', [InventoryController::class, 'download'])->name('inventory.download');

    // ── Administração (somente admin) ─────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {

        // Usuários
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');

        // Relatórios
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

        // Reset de Dados de Estoque
        Route::get('/reset-stock', [AdminController::class, 'resetStockConfirm'])->name('reset-stock');
        Route::post('/reset-stock', [AdminController::class, 'resetStockExecute'])->name('reset-stock.execute');
    });
});

// ─── Fallback ─────────────────────────────────────────────────────
Route::fallback(fn () => redirect('/'));
