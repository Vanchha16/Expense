{{-- Expense Modal --}}
{{-- Requires x-data with expenseModal boolean in parent scope --}}
<div x-show="expenseModal"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 modal-backdrop"
     @click.self="expenseModal = false"
     @keydown.escape.window="expenseModal = false">

    <div x-show="expenseModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800">{{ isset($expense) && $expense ? 'Edit Expense' : 'Add Expense' }}</h3>
            </div>
            <button @click="expenseModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ $action }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @if($method !== 'POST') @method($method) @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="date"
                           value="{{ old('date', isset($expense) && $expense ? $expense->date->format('Y-m-d') : date('Y-m-d')) }}"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount ($) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0.01"
                           value="{{ old('amount', isset($expense) && $expense ? $expense->amount : '') }}"
                           placeholder="0.00"
                           class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-slate-50" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                <select name="category"
                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-slate-50" required>
                    <option value="">Select category...</option>
                    @foreach(\App\Models\Expense::$categories as $cat)
                    <option value="{{ $cat }}" {{ old('category', isset($expense) && $expense ? $expense->category : '') === $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Description <span class="text-red-500">*</span></label>
                <input type="text" name="description" maxlength="255"
                       value="{{ old('description', isset($expense) && $expense ? $expense->description : '') }}"
                       placeholder="What did you spend on?"
                       class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-slate-50" required>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" @click="expenseModal = false"
                        class="flex-1 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 px-4 py-2.5 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 px-4 py-2.5 rounded-xl transition-colors">
                    {{ isset($expense) && $expense ? 'Update' : 'Save Expense' }}
                </button>
            </div>
        </form>
    </div>
</div>
