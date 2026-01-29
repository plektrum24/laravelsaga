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
        // 1. Cash Registers (Shifts)
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index(); // Cashier

            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();

            $table->decimal('start_cash', 15, 2)->default(0); // Modal Awal
            $table->decimal('end_cash', 15, 2)->nullable(); // Cash in Drawer saat closing (input manual)

            // Computed fields (for performance/snapshot)
            $table->decimal('total_cash_sales', 15, 2)->default(0);
            $table->decimal('total_non_cash_sales', 15, 2)->default(0);
            $table->decimal('total_expenses', 15, 2)->default(0); // Uang Keluar
            $table->decimal('diff_amount', 15, 2)->default(0); // Selisih (System vs Physical)

            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Cash Expenses (Uang Keluar)
        Schema::create('cash_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->index(); // Who took the money

            $table->decimal('amount', 15, 2);
            $table->text('note'); // Description
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_expenses');
        Schema::dropIfExists('cash_registers');
    }
};
