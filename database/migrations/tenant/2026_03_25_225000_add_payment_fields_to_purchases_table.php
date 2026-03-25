<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('purchases', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'transfer', 'credit', 'qris'])->default('cash')->after('paid_amount');
            }
            if (!Schema::hasColumn('purchases', 'due_date')) {
                $table->date('due_date')->nullable()->after('date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'payment_method', 'due_date']);
        });
    }
};
