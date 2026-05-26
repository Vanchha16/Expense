@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">

    {{-- Total Spent This Month --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Spent This Month</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">${{ number_format($totalSpentThisMonth, 2) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ now()->format('F Y') }}</p>
            </div>
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        @if($categoryBreakdown->isNotEmpty())
        <div class="mt-4 space-y-1.5">
            @foreach($categoryBreakdown->take(3) as $cat)
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ $cat->category }}</span>
                <span class="font-medium text-slate-700">${{ number_format($cat->total, 2) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Total Borrowed Out --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Borrowed Out</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">${{ number_format($totalBorrowedOut, 2) }}</p>
                <p class="mt-1 text-xs text-slate-400">Outstanding balance</p>
            </div>
            <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        @if($overdueCount > 0)
        <div class="mt-4 flex items-center gap-1.5">
            <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 border border-red-100 px-2 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block animate-pulse"></span>
                {{ $overdueCount }} overdue {{ Str::plural('record', $overdueCount) }}
            </span>
        </div>
        @else
        <div class="mt-4">
            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                No overdue records
            </span>
        </div>
        @endif
    </div>

    {{-- Quick Actions --}}
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-5 shadow-sm sm:col-span-2 xl:col-span-1"
         x-data="{ expenseModal: false, borrowModal: false }">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Quick Actions</p>
        <div class="space-y-3">
            <button @click="expenseModal = true"
                    class="w-full flex items-center gap-3 bg-emerald-500 hover:bg-emerald-400 text-white font-semibold text-sm px-4 py-3 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Add Expense
            </button>
            <button @click="borrowModal = true"
                    class="w-full flex items-center gap-3 bg-amber-500 hover:bg-amber-400 text-white font-semibold text-sm px-4 py-3 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Record Borrow
            </button>
        </div>

        {{-- Add Expense Modal --}}
        @include('partials.expense-modal', ['action' => route('expenses.store'), 'method' => 'POST', 'expense' => null])

        {{-- Add Borrow Modal --}}
        @include('partials.borrow-modal', ['action' => route('borrows.store'), 'method' => 'POST', 'borrow' => null])
    </div>

</div>

{{-- Category Breakdown Bar --}}
@if($categoryBreakdown->isNotEmpty() && $totalSpentThisMonth > 0)
<div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm mb-6">
    <h2 class="text-sm font-semibold text-slate-700 mb-4">Spending by Category — {{ now()->format('F Y') }}</h2>
    <div class="flex h-3 rounded-full overflow-hidden gap-0.5 mb-4">
        @php
            $barColors = ['Food'=>'bg-emerald-500','Transport'=>'bg-blue-500','Bills'=>'bg-red-500','Entertainment'=>'bg-purple-500','Other'=>'bg-slate-400'];
        @endphp
        @foreach($categoryBreakdown as $cat)
        <div class="{{ $barColors[$cat->category] ?? 'bg-slate-400' }} rounded-full"
             style="width: {{ round(($cat->total / $totalSpentThisMonth) * 100, 1) }}%"
             title="{{ $cat->category }}: ${{ number_format($cat->total, 2) }}">
        </div>
        @endforeach
    </div>
    <div class="flex flex-wrap gap-x-4 gap-y-2">
        @foreach($categoryBreakdown as $cat)
        @php $pct = round(($cat->total / $totalSpentThisMonth) * 100, 1); @endphp
        <div class="flex items-center gap-1.5 text-xs text-slate-600">
            <span class="w-2.5 h-2.5 rounded-sm {{ $barColors[$cat->category] ?? 'bg-slate-400' }} flex-shrink-0"></span>
            <span class="font-medium">{{ $cat->category }}</span>
            <span class="text-slate-400">{{ $pct }}%</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Recent Activity --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

    {{-- Recent Expenses --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700">Recent Expenses</h2>
            <a href="{{ route('expenses.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">View all →</a>
        </div>
        @if($recentExpenses->isEmpty())
        <div class="px-5 py-10 text-center text-slate-400 text-sm">No expenses recorded yet.</div>
        @else
        <ul class="divide-y divide-slate-100">
            @foreach($recentExpenses as $expense)
            @php
                $catColors = ['Food'=>'emerald','Transport'=>'blue','Bills'=>'red','Entertainment'=>'purple','Other'=>'slate'];
                $c = $catColors[$expense->category] ?? 'slate';
            @endphp
            <li class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-{{ $c }}-100 flex items-center justify-center flex-shrink-0 text-{{ $c }}-600 text-xs font-bold">
                    {{ substr($expense->category, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">{{ $expense->description }}</p>
                    <p class="text-xs text-slate-400">{{ $expense->date->format('d M Y') }} &middot; {{ $expense->category }}</p>
                </div>
                <span class="text-sm font-semibold text-slate-800 flex-shrink-0">${{ number_format($expense->amount, 2) }}</span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    {{-- Recent Borrows --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700">Recent Borrows</h2>
            <a href="{{ route('borrows.index') }}" class="text-xs text-amber-600 hover:text-amber-700 font-medium">View all →</a>
        </div>
        @if($recentBorrows->isEmpty())
        <div class="px-5 py-10 text-center text-slate-400 text-sm">No borrow records yet.</div>
        @else
        <ul class="divide-y divide-slate-100">
            @foreach($recentBorrows as $borrow)
            @php
                $sc = match($borrow->status) { 'paid'=>'emerald', 'partially_paid'=>'amber', default=>'red' };
                $sl = match($borrow->status) { 'paid'=>'Paid', 'partially_paid'=>'Partial', default=>'Unpaid' };
            @endphp
            <li class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0 text-slate-600 text-xs font-bold uppercase">
                    {{ substr($borrow->borrower_name, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">{{ $borrow->borrower_name }}</p>
                    <p class="text-xs text-slate-400">
                        Borrowed {{ $borrow->date_borrowed->format('d M Y') }}
                        @if($borrow->due_date) &middot; Due {{ $borrow->due_date->format('d M') }}@endif
                    </p>
                </div>
                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                    <span class="text-sm font-semibold text-slate-800">${{ number_format($borrow->amount, 2) }}</span>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ $sl }}</span>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

</div>

@endsection
