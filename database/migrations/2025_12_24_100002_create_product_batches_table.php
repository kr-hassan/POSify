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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->string('batch_number')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('batch_number');
            $table->index('expiry_date');
            $table->index(['product_id', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};


