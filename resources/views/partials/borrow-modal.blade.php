{{-- Borrow Modal --}}
{{-- Requires x-data with borrowModal boolean in parent scope --}}
<div x-show="borrowModal"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 modal-backdrop"
     @click.self="borrowModal = false"
     @keydown.escape.window="borrowModal = false">

    <div x-show="borrowModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800">{{ isset($borrow) && $borrow ? 'Edit Borrow' : 'Record Borrow' }}</h3>
            </div>
            <button @click="borrowModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ $action }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @if($method !== 'POST') @method($method) @endif

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Borrower's Name <span class="text-red-500">*</span></label>
                <input type="text" name="borrower_name" maxlength="255"
                       value="{{ old('borrower_name', isset($borrow) && $borrow ? $borrow->borrower_name : '') }}"
                       placeholder="Full name..."
                       class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 bg-slate-50" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount ($) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0.01"
                           value="{{ old('amount', isset($borrow) && $borrow ? $borrow->amount : '') }}"
                           placeholder="0.00"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status"
                            class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 bg-slate-50" required>
                        <option value="unpaid" {{ old('status', isset($borrow) && $borrow ? $borrow->status : 'unpaid') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partially_paid" {{ old('status', isset($borrow) && $borrow ? $borrow->status : '') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ old('status', isset($borrow) && $borrow ? $borrow->status : '') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Date Borrowed <span class="text-red-500">*</span></label>
                    <input type="date" name="date_borrowed"
                           value="{{ old('date_borrowed', isset($borrow) && $borrow ? $borrow->date_borrowed->format('Y-m-d') : date('Y-m-d')) }}"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Due Date</label>
                    <input type="date" name="due_date"
                           value="{{ old('due_date', isset($borrow) && $borrow && $borrow->due_date ? $borrow->due_date->format('Y-m-d') : '') }}"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Notes</label>
                <textarea name="notes" rows="2" maxlength="500"
                          placeholder="Optional notes..."
                          class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 bg-slate-50 resize-none">{{ old('notes', isset($borrow) && $borrow ? $borrow->notes : '') }}</textarea>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" @click="borrowModal = false"
                        class="flex-1 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 px-4 py-2.5 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 px-4 py-2.5 rounded-xl transition-colors">
                    {{ isset($borrow) && $borrow ? 'Update' : 'Save Record' }}
                </button>
            </div>
        </form>
    </div>
</div>
