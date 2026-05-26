<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\BorrowController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Render health-check + keep-alive ping endpoint
Route::get('/health', function () {
    return response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('health');

Route::resource('expenses', ExpenseController::class)->except(['show', 'create']);

Route::resource('borrows', BorrowController::class)->except(['show', 'create']);
Route::patch('borrows/{borrow}/mark-paid', [BorrowController::class, 'markPaid'])->name('borrows.mark-paid');
