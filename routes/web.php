<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/stock/{stockItem}/label', [StockController::class, 'label'])->name('stock.label');
    Route::post('/stock/labels-batch', [StockController::class, 'labelsBatch'])->name('stock.labelsBatch');

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
    });
});

// ─── Fallback ─────────────────────────────────────────────────────
Route::fallback(fn () => redirect('/'));
