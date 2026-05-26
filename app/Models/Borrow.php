<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_name',
        'amount',
        'date_borrowed',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_borrowed' => 'date',
        'due_date'      => 'date',
        'amount'        => 'decimal:2',
    ];

    public static array $statuses = ['unpaid', 'partially_paid', 'paid'];

    public function isOverdue(): bool
    {
        return $this->status !== 'paid'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'unpaid'        => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            'paid'          => 'Paid',
            default         => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'unpaid'        => 'red',
            'partially_paid' => 'amber',
            'paid'          => 'emerald',
            default         => 'slate',
        };
    }
}
