<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_no')->unique();
            $table->foreignId('warranty_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('claim_type', ['repair', 'replacement', 'refund'])->default('repair');
            $table->text('issue_description');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed'])->default('pending');
            $table->text('resolution_notes')->nullable();
            $table->date('claim_date');
            $table->date('resolved_date')->nullable();
            $table->timestamps();

            $table->index('claim_no');
            $table->index('status');
            $table->index('claim_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_claims');
    }
};
