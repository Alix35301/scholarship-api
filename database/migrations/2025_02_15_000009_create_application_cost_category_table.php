<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_cost_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('scholarship_applications')->onDelete('cascade');
            $table->foreignId('cost_category_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            
            $table->unique(['application_id', 'cost_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_cost_category');
    }
};
