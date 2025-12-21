<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('warranty_no')->unique();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_item_id')->constrained('sale_items')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('warranty_period_months')->default(12);
            $table->enum('status', ['active', 'expired', 'void', 'claimed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('warranty_no');
            $table->index('status');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
