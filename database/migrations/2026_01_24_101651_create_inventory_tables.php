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
        // 1. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null'); // Owner Branch
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sku', 50)->unique();
            $table->string('barcode', 50)->nullable()->index();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();

            // Stock & Pricing (Base Unit)
            $table->decimal('stock', 15, 4)->default(0); // Global Stock
            $table->decimal('min_stock', 15, 4)->default(5);
            $table->decimal('buy_price', 15, 2)->default(0); // HPP Terakhir
            $table->decimal('sell_price', 15, 2)->default(0); // Harga Jual Umum

            $table->boolean('track_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Product Units (Conversion)
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained();
            $table->decimal('conversion_qty', 15, 4)->default(1); // 1 Box = 12 Pcs
            $table->decimal('buy_price', 15, 2)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0);
            $table->boolean('is_base_unit')->default(false);
            $table->timestamps();
        });

        // 3. Inventory Movements (Stock Log)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained(); // Who did it
            $table->string('reference_number', 100)->nullable(); // Transaction No / PO No
            $table->enum('type', ['in', 'out', 'adjustment', 'transfer'])->default('out');
            $table->decimal('qty', 15, 4);
            $table->decimal('current_stock', 15, 4); // Snapshot after movement
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('products');
    }
};
