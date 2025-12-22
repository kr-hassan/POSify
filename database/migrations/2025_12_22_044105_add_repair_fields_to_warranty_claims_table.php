<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds repair-specific fields to warranty_claims table:
     * - received_date: When the product was received for repair
     * - returned_date: When the repaired product was returned to customer
     * - technician_notes: Notes from technician during repair process
     * - Updates status enum to include repair workflow statuses
     */
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            // Add repair-specific fields
            if (!Schema::hasColumn('warranty_claims', 'received_date')) {
                $table->date('received_date')->nullable()->after('claim_date');
            }
            
            if (!Schema::hasColumn('warranty_claims', 'returned_date')) {
                $table->date('returned_date')->nullable()->after('received_date');
            }
            
            if (!Schema::hasColumn('warranty_claims', 'technician_notes')) {
                $table->text('technician_notes')->nullable()->after('resolution_notes');
            }
        });
        
        // Update status enum to include repair workflow statuses
        // Note: MySQL doesn't support ALTER ENUM easily, so we'll handle this in the model/application layer
        // The status field already has: pending, approved, rejected, in_progress, completed
        // For repair workflow: pending -> in_progress (renamed to 'in_repair') -> completed -> returned
        // We'll use: pending, in_progress (as 'in_repair'), completed, and add 'returned' if needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (Schema::hasColumn('warranty_claims', 'technician_notes')) {
                $table->dropColumn('technician_notes');
            }
            
            if (Schema::hasColumn('warranty_claims', 'returned_date')) {
                $table->dropColumn('returned_date');
            }
            
            if (Schema::hasColumn('warranty_claims', 'received_date')) {
                $table->dropColumn('received_date');
            }
        });
    }
};
