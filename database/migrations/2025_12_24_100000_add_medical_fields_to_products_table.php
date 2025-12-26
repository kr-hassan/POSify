<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Medical/Pharmacy specific fields
            $table->boolean('requires_prescription')->default(false)->after('is_active');
            $table->string('hsn_code')->nullable()->after('requires_prescription');
            $table->string('manufacturer')->nullable()->after('hsn_code');
            $table->string('composition')->nullable()->after('manufacturer');
            $table->string('schedule')->nullable()->after('composition'); // H1, H2, X, etc.
            $table->integer('shelf_life_days')->nullable()->after('schedule');
            
            // Indexes
            $table->index('requires_prescription');
            $table->index('hsn_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['requires_prescription']);
            $table->dropIndex(['hsn_code']);
            $table->dropColumn([
                'requires_prescription',
                'hsn_code',
                'manufacturer',
                'composition',
                'schedule',
                'shelf_life_days',
            ]);
        });
    }
};


