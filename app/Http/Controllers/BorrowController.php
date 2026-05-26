<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrow::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrows      = $query->orderBy('date_borrowed', 'desc')->paginate(15)->withQueryString();
        $totalUnpaid  = Borrow::whereIn('status', ['unpaid', 'partially_paid'])->sum('amount');
        $overdueCount = Borrow::whereIn('status', ['unpaid', 'partially_paid'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->count();

        return view('borrows.index', compact('borrows', 'totalUnpaid', 'overdueCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_name' => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0.01|max:9999999',
            'date_borrowed' => 'required|date',
            'due_date'      => 'nullable|date|after_or_equal:date_borrowed',
            'status'        => 'required|in:unpaid,partially_paid,paid',
            'notes'         => 'nullable|string|max:500',
        ]);

        Borrow::create($validated);

        return back()->with('success', 'Borrow record added successfully.');
    }

    public function edit(Borrow $borrow)
    {
        return response()->json($borrow);
    }

    public function update(Request $request, Borrow $borrow)
    {
        $validated = $request->validate([
            'borrower_name' => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0.01|max:9999999',
            'date_borrowed' => 'required|date',
            'due_date'      => 'nullable|date|after_or_equal:date_borrowed',
            'status'        => 'required|in:unpaid,partially_paid,paid',
            'notes'         => 'nullable|string|max:500',
        ]);

        $borrow->update($validated);

        return back()->with('success', 'Borrow record updated successfully.');
    }

    public function destroy(Borrow $borrow)
    {
        $borrow->delete();

        return back()->with('success', 'Borrow record deleted.');
    }

    public function markPaid(Borrow $borrow)
    {
        $borrow->update(['status' => 'paid']);

        return back()->with('success', "{$borrow->borrower_name} marked as paid.");
    }
}
