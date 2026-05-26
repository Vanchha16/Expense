<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Borrow;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $expenses = [
            ['date' => $now->copy()->subDays(0)->format('Y-m-d'), 'amount' => 12.50, 'category' => 'Food',          'description' => 'Lunch at Noodle House'],
            ['date' => $now->copy()->subDays(1)->format('Y-m-d'), 'amount' => 3.80,  'category' => 'Transport',     'description' => 'Bus fare to market'],
            ['date' => $now->copy()->subDays(2)->format('Y-m-d'), 'amount' => 45.00, 'category' => 'Bills',         'description' => 'Mobile top-up'],
            ['date' => $now->copy()->subDays(3)->format('Y-m-d'), 'amount' => 18.00, 'category' => 'Entertainment', 'description' => 'Netflix subscription'],
            ['date' => $now->copy()->subDays(4)->format('Y-m-d'), 'amount' => 7.20,  'category' => 'Food',          'description' => 'Coffee and snacks'],
            ['date' => $now->copy()->subDays(5)->format('Y-m-d'), 'amount' => 22.00, 'category' => 'Transport',     'description' => 'Grab ride to office'],
            ['date' => $now->copy()->subDays(6)->format('Y-m-d'), 'amount' => 60.00, 'category' => 'Bills',         'description' => 'Electricity bill'],
            ['date' => $now->copy()->subDays(7)->format('Y-m-d'), 'amount' => 15.00, 'category' => 'Food',          'description' => 'Dinner with family'],
            ['date' => $now->copy()->subDays(8)->format('Y-m-d'), 'amount' => 5.00,  'category' => 'Other',         'description' => 'Parking fee'],
            ['date' => $now->copy()->subDays(9)->format('Y-m-d'), 'amount' => 35.00, 'category' => 'Food',          'description' => 'Weekly grocery run'],
        ];

        foreach ($expenses as $e) {
            Expense::create($e);
        }

        $borrows = [
            [
                'borrower_name' => 'Sophea Meng',
                'amount'        => 150.00,
                'date_borrowed' => $now->copy()->subDays(10)->format('Y-m-d'),
                'due_date'      => $now->copy()->addDays(5)->format('Y-m-d'),
                'status'        => 'unpaid',
                'notes'         => 'Emergency loan for hospital',
            ],
            [
                'borrower_name' => 'Dara Pich',
                'amount'        => 80.00,
                'date_borrowed' => $now->copy()->subDays(20)->format('Y-m-d'),
                'due_date'      => $now->copy()->subDays(5)->format('Y-m-d'),
                'status'        => 'partially_paid',
                'notes'         => 'Paid $30 back so far',
            ],
            [
                'borrower_name' => 'Channary Ros',
                'amount'        => 200.00,
                'date_borrowed' => $now->copy()->subDays(30)->format('Y-m-d'),
                'due_date'      => null,
                'status'        => 'paid',
                'notes'         => 'Repaid in full',
            ],
            [
                'borrower_name' => 'Virak Noun',
                'amount'        => 50.00,
                'date_borrowed' => $now->copy()->subDays(3)->format('Y-m-d'),
                'due_date'      => $now->copy()->addDays(14)->format('Y-m-d'),
                'status'        => 'unpaid',
                'notes'         => null,
            ],
        ];

        foreach ($borrows as $b) {
            Borrow::create($b);
        }
    }
}
