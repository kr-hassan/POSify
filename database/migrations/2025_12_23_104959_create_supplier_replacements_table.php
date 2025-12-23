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
        if (!Schema::hasTable('supplier_replacements')) {
            Schema::create('supplier_replacements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_return_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('cost_price', 10, 2);
                $table->date('received_date');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('supplier_return_id');
                $table->index('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_replacements');
    }
};
