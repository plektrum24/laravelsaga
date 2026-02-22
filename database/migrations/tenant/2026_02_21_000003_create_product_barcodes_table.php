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
        Schema::connection('tenant')->create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('barcode', 100);
            $table->enum('barcode_type', ['ean13', 'upc', 'code128', 'qr'])->default('ean13');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->index('barcode');
            $table->index(['product_id', 'is_primary']);
        });

        // Add barcode field to products table for backward compatibility
        Schema::connection('tenant')->table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('product_barcodes');
        
        Schema::connection('tenant')->table('products', function (Blueprint $table) {
            $table->string('barcode')->change();
        });
    }
};
