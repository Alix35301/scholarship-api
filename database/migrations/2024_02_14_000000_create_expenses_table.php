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
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('type', ['individual', 'shared'])->default('individual');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();
        });

        // For shared expenses, we need a pivot table to track expense allocations
        Schema::create('expense_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('share_percentage', 5, 2);
            $table->decimal('share_amount', 10, 2);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('expense_allocations');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
