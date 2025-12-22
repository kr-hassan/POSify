<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            // Add indexes for better search performance
            if (!$this->indexExists('warranties', 'warranties_customer_id_index')) {
                $table->index('customer_id');
            }
            if (!$this->indexExists('warranties', 'warranties_product_id_index')) {
                $table->index('product_id');
            }
            if (!$this->indexExists('warranties', 'warranties_start_date_index')) {
                $table->index('start_date');
            }
            if (!$this->indexExists('warranties', 'warranties_sale_id_index')) {
                $table->index('sale_id');
            }
            // Composite index for common queries
            if (!$this->indexExists('warranties', 'warranties_status_end_date_index')) {
                $table->index(['status', 'end_date']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['status', 'end_date']);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        $result = $connection->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$databaseName, $table, $index]
        );
        return $result[0]->count > 0;
    }
};


