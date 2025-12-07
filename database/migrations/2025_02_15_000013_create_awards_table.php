<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('application_id')->constrained('scholarship_applications')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['awarded', 'disbursing', 'completed', 'cancelled'])->default('awarded');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};

