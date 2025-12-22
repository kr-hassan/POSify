<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates return_items table to track individual items being returned.
     * Links to sale_item_id to track which specific sale item is being returned.
     * This allows partial returns and proper tracking.
     */
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->onDelete('cascade'); // Link to return
            $table->foreignId('sale_item_id')->constrained('sale_items')->onDelete('cascade'); // Link to original sale item
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Product being returned
            $table->integer('quantity')->default(1); // Quantity being returned
            $table->decimal('refund_amount', 10, 2); // Refund amount for this item
            $table->text('reason')->nullable(); // Reason for returning this specific item
            $table->timestamps();
            
            // Indexes for performance
            $table->index('return_id');
            $table->index('sale_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
