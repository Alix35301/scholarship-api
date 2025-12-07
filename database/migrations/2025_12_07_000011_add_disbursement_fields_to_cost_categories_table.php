<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_categories', function (Blueprint $table) {
            $table->enum('disbursement_type', ['semester', 'monthly', 'reimbursement'])->nullable()->after('category');
            $table->json('disbursement_config')->nullable()->after('disbursement_type');
        });
    }

    public function down(): void
    {
        Schema::table('cost_categories', function (Blueprint $table) {
            $table->dropColumn(['disbursement_type', 'disbursement_config']);
        });
    }
};

