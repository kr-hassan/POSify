<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates returns table for product returns (refund/exchange).
     * This is separate from repair workflow and handles:
     * - Financial adjustments (refund amount)
     * - Inventory adjustments (stock increase/decrease)
     * - Warranty voiding for refunds
     * - New warranty creation for exchanges
     */
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique(); // Unique return number
            $table->foreignId('sale_id')->constrained()->onDelete('cascade'); // Link to original sale
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null'); // Customer who returned
            $table->enum('return_type', ['refund', 'exchange'])->default('refund'); // Type of return
            $table->decimal('total_refund', 10, 2)->default(0); // Total refund amount
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending'); // Return status
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // User who created the return
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // User who approved (Admin/Manager)
            $table->text('reason')->nullable(); // Reason for return
            $table->date('return_date'); // Date of return
            $table->date('processed_date')->nullable(); // Date when refund/exchange was processed
            $table->timestamps();
            
            // Indexes for performance
            $table->index('sale_id');
            $table->index('return_no');
            $table->index('status');
            $table->index('return_date');
            $table->index(['status', 'return_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
