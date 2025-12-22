<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if column exists and add default value
        if (Schema::hasColumn('warranties', 'warranty_period_days')) {
            Schema::table('warranties', function (Blueprint $table) {
                $table->integer('warranty_period_days')->default(365)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->integer('warranty_period_days')->nullable()->change();
        });
    }
};


