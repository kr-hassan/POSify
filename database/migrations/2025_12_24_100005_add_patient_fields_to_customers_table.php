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
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('blood_group')->nullable()->after('date_of_birth');
            $table->text('allergies')->nullable()->after('blood_group');
            $table->text('medical_history')->nullable()->after('allergies');
            $table->boolean('is_patient')->default(false)->after('medical_history');
            
            $table->index('is_patient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['is_patient']);
            $table->dropColumn([
                'gender',
                'date_of_birth',
                'blood_group',
                'allergies',
                'medical_history',
                'is_patient',
            ]);
        });
    }
};


