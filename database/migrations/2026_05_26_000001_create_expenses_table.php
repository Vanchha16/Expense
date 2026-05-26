<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->enum('category', ['Food', 'Transport', 'Bills', 'Entertainment', 'Other']);
            $table->string('description', 255);
            $table->timestamps();

            $table->index(['date']);
            $table->index(['category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
