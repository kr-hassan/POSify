<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            // Add user_id if it doesn't exist
            if (!Schema::hasColumn('warranty_claims', 'user_id')) {
                $table->foreignId('user_id')->after('warranty_id')->constrained()->onDelete('cascade');
            }
            
            // Add claim_type if it doesn't exist
            if (!Schema::hasColumn('warranty_claims', 'claim_type')) {
                $table->enum('claim_type', ['repair', 'replacement', 'refund'])->default('repair')->after('user_id');
            }
            
            // Update status enum to include missing values
            // Note: This might require dropping and recreating the column
            if (Schema::hasColumn('warranty_claims', 'status')) {
                // Check current enum values
                $result = DB::select("SHOW COLUMNS FROM warranty_claims WHERE Field = 'status'");
                if (!empty($result)) {
                    $enumValues = $result[0]->Type;
                    if (strpos($enumValues, 'in_progress') === false || strpos($enumValues, 'approved') === false) {
                        // Need to modify enum - this is complex, so we'll use DB::statement
                        DB::statement("ALTER TABLE warranty_claims MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'in_progress', 'completed') DEFAULT 'pending'");
                    }
                }
            }
            
            // Add resolved_date if it doesn't exist (rename from processed_date if needed)
            if (!Schema::hasColumn('warranty_claims', 'resolved_date') && Schema::hasColumn('warranty_claims', 'processed_date')) {
                // Keep both for now, or rename
            } elseif (!Schema::hasColumn('warranty_claims', 'resolved_date')) {
                $table->date('resolved_date')->nullable()->after('resolution_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (Schema::hasColumn('warranty_claims', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('warranty_claims', 'claim_type')) {
                $table->dropColumn('claim_type');
            }
            if (Schema::hasColumn('warranty_claims', 'resolved_date')) {
                $table->dropColumn('resolved_date');
            }
        });
    }
};

