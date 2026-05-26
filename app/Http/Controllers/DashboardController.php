<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Borrow;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $month = now()->month;
        $year  = now()->year;

        $totalSpentThisMonth = Expense::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $totalBorrowedOut = Borrow::whereIn('status', ['unpaid', 'partially_paid'])
            ->sum('amount');

        $recentExpenses = Expense::orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentBorrows = Borrow::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $categoryBreakdown = Expense::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $overdueCount = Borrow::whereIn('status', ['unpaid', 'partially_paid'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->count();

        return view('dashboard', compact(
            'totalSpentThisMonth',
            'totalBorrowedOut',
            'recentExpenses',
            'recentBorrows',
            'categoryBreakdown',
            'overdueCount',
        ));
    }
}
