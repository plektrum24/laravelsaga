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
        // 1. Employees (Linked to Users if needed)
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // Login Access
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('name', 100);
            $table->string('nik', 20)->unique()->nullable(); // Employee ID
            $table->string('role', 50)->default('staff'); // Cashier, Manager, etc
            $table->string('phone', 20)->nullable();
            $table->date('join_date')->nullable();

            // Salary Info
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowance', 15, 2)->default(0); // Tunjangan tetap

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Attendances (Absensi)
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->string('status', 20)->default('present'); // present, late, absent, leave
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Payrolls (Gaji)
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->string('period', 7); // YYYY-MM
            $table->date('payment_date');

            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0); // Potongan
            $table->decimal('bonuses', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
