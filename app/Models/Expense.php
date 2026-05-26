<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'amount', 'category', 'description'];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public static array $categories = ['Food', 'Transport', 'Bills', 'Entertainment', 'Other'];

    public static array $categoryColors = [
        'Food'          => 'emerald',
        'Transport'     => 'blue',
        'Bills'         => 'red',
        'Entertainment' => 'purple',
        'Other'         => 'slate',
    ];

    public function categoryColor(): string
    {
        return self::$categoryColors[$this->category] ?? 'slate';
    }
}
