<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Loan;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Exibe o painel principal com resumos e alertas.
     */
    public function index()
    {
        $user = Auth::user();

        // ── Contadores ───────────────────────────────────────
        $totalProducts     = Product::count();
        $totalCategories   = Category::count();
        $availableStock    = StockItem::available()->sum('quantity');
        $availableLots     = StockItem::available()->count();
        $activeLoans       = Loan::active()->count();
        $overdueLoans      = Loan::overdue()->count();

        // ── Alertas ──────────────────────────────────────────
        $lowStockProducts = Product::with('category')
            ->get()
            ->filter->isBelowMinimum()
            ->take(5);

        $expiringItems = StockItem::with('product')
            ->expiringSoon(30)
            ->orderBy('expiration_date')
            ->take(5)
            ->get();

        $overdueLoansCollection = Loan::with(['borrower', 'borrowerOrganization'])
            ->overdue()
            ->orderBy('expected_return_date')
            ->take(5)
            ->get();

        $alertCount = StockItem::expiringSoon(30)->count()
            + Product::all()->filter->isBelowMinimum()->count()
            + $overdueLoans;

        // ── Últimos registros ────────────────────────────────
        $recentLoans = Loan::with(['borrower', 'borrowerOrganization', 'loanedBy'])
            ->latest('loan_date')
            ->take(5)
            ->get();

        $recentMovements = StockMovement::with(['stockItem.product', 'performedBy'])
            ->whereHas('stockItem') // herda SubunitScope do StockItem automaticamente
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'user',
            'totalProducts',
            'totalCategories',
            'availableStock',
            'availableLots',
            'activeLoans',
            'overdueLoans',
            'lowStockProducts',
            'expiringItems',
            'overdueLoansCollection',
            'alertCount',
            'recentLoans',
            'recentMovements',
        ));
    }
}
