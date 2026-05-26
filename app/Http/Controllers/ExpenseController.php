<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query();

        $month = $request->input('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);
        $query->whereYear('date', $year)->whereMonth('date', $mon);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses   = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $totalAmount = (clone $query)->sum('amount');
        $categories  = Expense::$categories;

        return view('expenses.index', compact('expenses', 'categories', 'totalAmount', 'month'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'        => 'required|date',
            'amount'      => 'required|numeric|min:0.01|max:9999999',
            'category'    => 'required|in:Food,Transport,Bills,Entertainment,Other',
            'description' => 'required|string|max:255',
        ]);

        Expense::create($validated);

        return back()->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        return response()->json($expense);
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'date'        => 'required|date',
            'amount'      => 'required|numeric|min:0.01|max:9999999',
            'category'    => 'required|in:Food,Transport,Bills,Entertainment,Other',
            'description' => 'required|string|max:255',
        ]);

        $expense->update($validated);

        return back()->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }
}
