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
        Schema::connection('tenant')->create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('transfer_number')->unique();
            $table->foreignId('from_branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('to_branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('shipped_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'in_transit',
                'received',
                'cancelled'
            ])->default('draft');
            
            $table->datetime('request_date')->nullable();
            $table->datetime('approval_date')->nullable();
            $table->datetime('shipped_date')->nullable();
            $table->datetime('received_date')->nullable();
            
            $table->text('notes')->nullable();
            $table->integer('total_items')->default(0);
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['from_branch_id', 'to_branch_id']);
            $table->index('created_at');
        });

        Schema::connection('tenant')->create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            
            $table->decimal('qty_requested', 15, 4)->default(0);
            $table->decimal('qty_approved', 15, 4)->nullable();
            $table->decimal('qty_shipped', 15, 4)->default(0);
            $table->decimal('qty_received', 15, 4)->default(0);
            $table->decimal('qty_discrepancy', 15, 4)->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('transfer_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('stock_transfer_items');
        Schema::connection('tenant')->dropIfExists('stock_transfers');
    }
};
