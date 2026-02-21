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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('transport_allowance', 15, 2)->default(0)->after('allowance');
            $table->decimal('meal_allowance', 15, 2)->default(0)->after('transport_allowance');
            $table->decimal('position_allowance', 15, 2)->default(0)->after('meal_allowance');
            $table->decimal('performance_bonus', 15, 2)->default(0)->after('position_allowance');
            $table->string('bank_name')->nullable()->after('performance_bonus');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_holder')->nullable()->after('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'transport_allowance',
                'meal_allowance',
                'position_allowance',
                'performance_bonus',
                'bank_name',
                'bank_account_number',
                'bank_account_holder'
            ]);
        });
    }
};
