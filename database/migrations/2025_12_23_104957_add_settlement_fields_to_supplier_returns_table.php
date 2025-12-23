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
            if (!Schema::hasColumn('supplier_returns', 'settlement_type')) {
                $table->enum('settlement_type', ['refund', 'replacement', 'partial_refund', 'partial_replacement'])->nullable()->after('processed_date');
            }
            if (!Schema::hasColumn('supplier_returns', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->nullable()->after('settlement_type');
            }
            if (!Schema::hasColumn('supplier_returns', 'refund_date')) {
                $table->date('refund_date')->nullable()->after('refund_amount');
            }
            if (!Schema::hasColumn('supplier_returns', 'refund_method')) {
                $table->string('refund_method')->nullable()->after('refund_date'); // cash, bank_transfer, credit_note, etc.
            }
            if (!Schema::hasColumn('supplier_returns', 'refund_notes')) {
                $table->text('refund_notes')->nullable()->after('refund_method');
            }
            if (!Schema::hasColumn('supplier_returns', 'is_settled')) {
                $table->boolean('is_settled')->default(false)->after('refund_notes');
            }
            if (!Schema::hasColumn('supplier_returns', 'settled_date')) {
                $table->date('settled_date')->nullable()->after('is_settled');
            }
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
