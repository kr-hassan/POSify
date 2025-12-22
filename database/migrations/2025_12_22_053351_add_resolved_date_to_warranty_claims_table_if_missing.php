<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds resolved_date column to warranty_claims table if it doesn't exist.
     * This column stores the date when a warranty claim was resolved/completed.
     */
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('warranty_claims', 'resolved_date')) {
                $table->date('resolved_date')->nullable()->after('resolution_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (Schema::hasColumn('warranty_claims', 'resolved_date')) {
                $table->dropColumn('resolved_date');
            }
        });
    }
};
