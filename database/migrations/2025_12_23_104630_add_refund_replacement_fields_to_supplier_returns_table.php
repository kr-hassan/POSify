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
        Schema::table('supplier_returns', function (Blueprint $table) {
            $table->enum('settlement_type', ['pending', 'refund', 'replacement', 'partial'])->default('pending')->after('status');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('settlement_type');
            $table->date('refund_date')->nullable()->after('refund_amount');
            $table->string('refund_method')->nullable()->after('refund_date'); // cash, bank_transfer, credit_note, etc.
            $table->text('refund_notes')->nullable()->after('refund_method');
            $table->boolean('is_settled')->default(false)->after('refund_notes');
            $table->date('settled_date')->nullable()->after('is_settled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_returns', function (Blueprint $table) {
            $table->dropColumn([
                'settlement_type',
                'refund_amount',
                'refund_date',
                'refund_method',
                'refund_notes',
                'is_settled',
                'settled_date',
            ]);
        });
    }
};
