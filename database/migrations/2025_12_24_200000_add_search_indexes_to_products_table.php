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
            // Add indexes for faster search (using try-catch to handle existing indexes)
            try {
                $table->index('name');
            } catch (\Exception $e) {
                // Index might already exist
            }
            
            try {
                $table->index(['is_active', 'stock']);
            } catch (\Exception $e) {
                // Index might already exist
            }
            
            try {
                $table->index('category_id');
            } catch (\Exception $e) {
                // Index might already exist
            }
            
            try {
                $table->index('sell_price');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropIndex(['name']);
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex(['is_active', 'stock']);
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex(['category_id']);
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex(['sell_price']);
            } catch (\Exception $e) {
                // Index might not exist
            }
        });
    }
};

