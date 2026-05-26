@extends('layouts.app')
@section('title', 'Expenses')

@section('content')

<div x-data="{
    expenseModal: {{ $errors->any() && old('_from') === 'expense' ? 'true' : 'false' }},
    editModal: false,
    editData: {},
    openEdit(expense) {
        this.editData = expense;
        this.editModal = true;
    }
}">

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Expense Tracker</h2>
        <p class="text-sm text-slate-500 mt-0.5">Track your daily spending</p>
    </div>
    <button @click="expenseModal = true"
            class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Add Expense
    </button>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Month</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="text-sm border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-slate-50">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Category</label>
            <select name="category"
                    class="text-sm border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-slate-50">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="text-sm font-medium bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl transition-colors">
            Filter
        </button>
        @if(request('category') || request('month') !== now()->format('Y-m'))
        <a href="{{ route('expenses.index') }}"
           class="text-sm font-medium text-slate-500 hover:text-slate-700 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
            Reset
        </a>
        @endif
    </form>
</div>

{{-- Summary stat --}}
<div class="flex items-center justify-between mb-4 px-1">
    <p class="text-sm text-slate-500">
        <span class="font-semibold text-slate-700">{{ $expenses->total() }}</span> records
    </p>
    <p class="text-sm font-semibold text-slate-700">
        Total: <span class="text-emerald-600">${{ number_format($totalAmount, 2) }}</span>
    </p>
</div>

{{-- Desktop Table --}}
<div class="hidden sm:block bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    @if($expenses->isEmpty())
    <div class="py-16 text-center">
        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-slate-500 text-sm font-medium">No expenses found</p>
        <p class="text-slate-400 text-xs mt-1">Try a different filter or add a new expense.</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Description</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @php
                $catColors = ['Food'=>'emerald','Transport'=>'blue','Bills'=>'red','Entertainment'=>'purple','Other'=>'slate'];
            @endphp
            @foreach($expenses as $expense)
            <tr class="hover:bg-slate-50 transition-colors group">
                <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $expense->date->format('d M Y') }}</td>
                <td class="px-5 py-3.5 text-slate-800 font-medium max-w-xs truncate">{{ $expense->description }}</td>
                <td class="px-5 py-3.5">
                    @php $c = $catColors[$expense->category] ?? 'slate'; @endphp
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-{{ $c }}-100 text-{{ $c }}-700">
                        {{ $expense->category }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-right font-semibold text-slate-800 whitespace-nowrap">${{ number_format($expense->amount, 2) }}</td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEdit({{ $expense->toJson() }})"
                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                              onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Mobile Cards --}}
<div class="sm:hidden space-y-3 mb-5">
    @if($expenses->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 py-12 text-center">
        <p class="text-slate-500 text-sm">No expenses found for this period.</p>
    </div>
    @else
    @php $catColors = ['Food'=>'emerald','Transport'=>'blue','Bills'=>'red','Entertainment'=>'purple','Other'=>'slate']; @endphp
    @foreach($expenses as $expense)
    @php $c = $catColors[$expense->category] ?? 'slate'; @endphp
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-9 h-9 bg-{{ $c }}-100 rounded-xl flex items-center justify-center flex-shrink-0 text-{{ $c }}-600 text-xs font-bold">
                    {{ substr($expense->category, 0, 2) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $expense->description }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $expense->date->format('d M Y') }}</p>
                </div>
            </div>
            <p class="text-base font-bold text-slate-800 flex-shrink-0">${{ number_format($expense->amount, 2) }}</p>
        </div>
        <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-{{ $c }}-100 text-{{ $c }}-700">{{ $expense->category }}</span>
            <div class="flex items-center gap-2">
                <button @click="openEdit({{ $expense->toJson() }})"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium">Edit</button>
                <span class="text-slate-300">|</span>
                <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                      onsubmit="return confirm('Delete this expense?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-600 font-medium">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>

{{-- Pagination --}}
@if($expenses->hasPages())
<div class="flex justify-center">
    {{ $expenses->links() }}
</div>
@endif

{{-- Add Expense Modal --}}
@include('partials.expense-modal', ['action' => route('expenses.store'), 'method' => 'POST', 'expense' => null])

{{-- Edit Expense Modal --}}
<div x-show="editModal"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 modal-backdrop"
     @click.self="editModal = false"
     @keydown.escape.window="editModal = false">

    <div x-show="editModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800">Edit Expense</h3>
            </div>
            <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form :action="`/expenses/${editData.id}`" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Date</label>
                    <input type="date" name="date" :value="editData.date"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount ($)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" :value="editData.amount"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Category</label>
                <select name="category"
                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" :selected="editData.category === '{{ $cat }}'">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Description</label>
                <input type="text" name="description" :value="editData.description"
                       class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50" required>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" @click="editModal = false"
                        class="flex-1 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 px-4 py-2.5 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 text-sm font-semibold text-white bg-blue-500 hover:bg-blue-600 px-4 py-2.5 rounded-xl transition-colors">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection
