<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            if (!Schema::hasColumn('warranties', 'warranty_period_months')) {
                $table->integer('warranty_period_months')->default(12)->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            if (Schema::hasColumn('warranties', 'warranty_period_months')) {
                $table->dropColumn('warranty_period_months');
            }
        });
    }
};







