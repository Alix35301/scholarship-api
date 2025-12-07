<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('scholarship_applications')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('receipt_file')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_receipts');
    }
};

