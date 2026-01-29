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
        // 1. Transactions (Sales Header)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->unsignedBigInteger('user_id')->index(); // Cashier
            $table->string('invoice_number', 50)->unique();
            $table->timestamp('date');

            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);

            $table->enum('payment_method', ['cash', 'transfer', 'credit', 'qris'])->default('cash');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Transaction Items (Sales Details)
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('unit_id')->nullable()->constrained(); // Unit sold (e.g. Box)
            $table->decimal('qty', 15, 4);
            $table->decimal('price', 15, 2); // Price at moment of sale
            $table->decimal('cogs', 15, 2)->default(0); // Cost of Goods Sold
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 3. Purchase (Stock In)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->foreignId('supplier_id')->constrained();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('reference_number', 50)->nullable(); // Supplier Invoice No
            $table->date('date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['ordered', 'received', 'cancelled'])->default('received');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Purchase Items
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('unit_id')->nullable()->constrained();
            $table->decimal('qty', 15, 4);
            $table->decimal('buy_price', 15, 2);
            $table->date('expiry_date')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
