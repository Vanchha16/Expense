@extends('layouts.app')
@section('title', 'Borrows')

@section('content')

<div x-data="{
    borrowModal: false,
    editModal: false,
    editData: {},
    openEdit(borrow) {
        this.editData = borrow;
        this.editModal = true;
    }
}">

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Borrow Management</h2>
        <p class="text-sm text-slate-500 mt-0.5">Track who owes you money</p>
    </div>
    <button @click="borrowModal = true"
            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Record Borrow
    </button>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Outstanding</p>
        <p class="mt-2 text-2xl font-bold text-slate-800">${{ number_format($totalUnpaid, 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Records</p>
        <p class="mt-2 text-2xl font-bold text-slate-800">{{ $borrows->total() }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm {{ $overdueCount > 0 ? 'border-red-200 bg-red-50' : '' }}">
        <p class="text-xs font-semibold {{ $overdueCount > 0 ? 'text-red-500' : 'text-slate-500' }} uppercase tracking-wider">Overdue</p>
        <p class="mt-2 text-2xl font-bold {{ $overdueCount > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $overdueCount }}</p>
    </div>
</div>

{{-- Status Filter --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('borrows.index') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Filter by Status</label>
            <select name="status"
                    class="text-sm border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 bg-slate-50">
                <option value="">All statuses</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
        </div>
        <button type="submit"
                class="text-sm font-medium bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl transition-colors">
            Filter
        </button>
        @if(request('status'))
        <a href="{{ route('borrows.index') }}"
           class="text-sm font-medium text-slate-500 hover:text-slate-700 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
            Reset
        </a>
        @endif
    </form>
</div>

{{-- Desktop Table --}}
<div class="hidden sm:block bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    @if($borrows->isEmpty())
    <div class="py-16 text-center">
        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <p class="text-slate-500 text-sm font-medium">No borrow records found</p>
        <p class="text-slate-400 text-xs mt-1">Start by adding a record above.</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Borrower</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Borrowed</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Due Date</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($borrows as $borrow)
            @php
                $sc = match($borrow->status) { 'paid'=>'emerald','partially_paid'=>'amber',default=>'red' };
                $sl = match($borrow->status) { 'paid'=>'Paid','partially_paid'=>'Partially Paid',default=>'Unpaid' };
                $overdue = $borrow->isOverdue();
            @endphp
            <tr class="hover:bg-slate-50 transition-colors group {{ $overdue ? 'bg-red-50/40' : '' }}">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600 uppercase flex-shrink-0">
                            {{ substr($borrow->borrower_name, 0, 2) }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">{{ $borrow->borrower_name }}</p>
                            @if($borrow->notes)
                            <p class="text-xs text-slate-400 truncate max-w-[180px]">{{ $borrow->notes }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3.5 font-bold text-slate-800">${{ number_format($borrow->amount, 2) }}</td>
                <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $borrow->date_borrowed->format('d M Y') }}</td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    @if($borrow->due_date)
                        <span class="{{ $overdue ? 'text-red-600 font-semibold' : 'text-slate-600' }}">
                            {{ $borrow->due_date->format('d M Y') }}
                            @if($overdue) <span class="text-xs text-red-500 font-normal">(overdue)</span> @endif
                        </span>
                    @else
                        <span class="text-slate-400 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-{{ $sc }}-100 text-{{ $sc }}-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $sc }}-500 mr-1.5"></span>
                        {{ $sl }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-1">
                        @if($borrow->status !== 'paid')
                        <form action="{{ route('borrows.mark-paid', $borrow) }}" method="POST"
                              onsubmit="return confirm('Mark {{ $borrow->borrower_name }} as paid?')">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 px-2.5 py-1.5 rounded-lg transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Paid
                            </button>
                        </form>
                        @endif
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openEdit({{ $borrow->toJson() }})"
                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>
                            <form action="{{ route('borrows.destroy', $borrow) }}" method="POST"
                                  onsubmit="return confirm('Delete this record?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
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
    @if($borrows->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 py-12 text-center">
        <p class="text-slate-500 text-sm">No borrow records found.</p>
    </div>
    @else
    @foreach($borrows as $borrow)
    @php
        $sc = match($borrow->status) { 'paid'=>'emerald','partially_paid'=>'amber',default=>'red' };
        $sl = match($borrow->status) { 'paid'=>'Paid','partially_paid'=>'Partially Paid',default=>'Unpaid' };
        $overdue = $borrow->isOverdue();
    @endphp
    <div class="bg-white rounded-2xl border {{ $overdue ? 'border-red-200' : 'border-slate-200' }} p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-sm font-bold text-slate-600 uppercase flex-shrink-0">
                    {{ substr($borrow->borrower_name, 0, 2) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800">{{ $borrow->borrower_name }}</p>
                    @if($borrow->notes)
                    <p class="text-xs text-slate-400 truncate mt-0.5">{{ $borrow->notes }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-base font-bold text-slate-800">${{ number_format($borrow->amount, 2) }}</p>
                <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-{{ $sc }}-100 text-{{ $sc }}-700 mt-1">
                    {{ $sl }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-100">
            <div>
                <p class="text-xs text-slate-400">Borrowed</p>
                <p class="text-xs font-medium text-slate-700 mt-0.5">{{ $borrow->date_borrowed->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Due</p>
                <p class="text-xs font-medium {{ $overdue ? 'text-red-600' : 'text-slate-700' }} mt-0.5">
                    {{ $borrow->due_date ? $borrow->due_date->format('d M Y') : '—' }}
                    @if($overdue) <span class="text-red-500">(overdue)</span> @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 mt-3">
            @if($borrow->status !== 'paid')
            <form action="{{ route('borrows.mark-paid', $borrow) }}" method="POST"
                  onsubmit="return confirm('Mark as paid?')" class="flex-1">
                @csrf @method('PATCH')
                <button type="submit"
                        class="w-full flex items-center justify-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 px-3 py-2 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mark Paid
                </button>
            </form>
            @endif
            <button @click="openEdit({{ $borrow->toJson() }})"
                    class="flex-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-lg transition-colors text-center">
                Edit
            </button>
            <form action="{{ route('borrows.destroy', $borrow) }}" method="POST"
                  onsubmit="return confirm('Delete?')" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full text-xs font-medium text-red-500 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>
    @endforeach
    @endif
</div>

{{-- Pagination --}}
@if($borrows->hasPages())
<div class="flex justify-center">
    {{ $borrows->links() }}
</div>
@endif

{{-- Add Borrow Modal --}}
@include('partials.borrow-modal', ['action' => route('borrows.store'), 'method' => 'POST', 'borrow' => null])

{{-- Edit Borrow Modal --}}
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
                <h3 class="text-base font-semibold text-slate-800">Edit Borrow Record</h3>
            </div>
            <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form :action="`/borrows/${editData.id}`" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Borrower's Name</label>
                <input type="text" name="borrower_name" :value="editData.borrower_name"
                       class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount ($)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" :value="editData.amount"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
                    <select name="status"
                            class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50" required>
                        <option value="unpaid" :selected="editData.status === 'unpaid'">Unpaid</option>
                        <option value="partially_paid" :selected="editData.status === 'partially_paid'">Partially Paid</option>
                        <option value="paid" :selected="editData.status === 'paid'">Paid</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Date Borrowed</label>
                    <input type="date" name="date_borrowed" :value="editData.date_borrowed"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Due Date</label>
                    <input type="date" name="due_date" :value="editData.due_date"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Notes</label>
                <textarea name="notes" rows="2"
                          class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-slate-50 resize-none"
                          x-text="editData.notes"></textarea>
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
