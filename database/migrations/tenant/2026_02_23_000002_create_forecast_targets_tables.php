<?php

namespace Database\Migrations;

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
        Schema::create('forecast_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->decimal('target_revenue', 15, 2);
            $table->integer('target_duration_days')->default(30);
            $table->decimal('current_trajectory', 15, 2)->default(0);
            $table->decimal('gap', 15, 2)->default(0);
            $table->enum('status', ['draft', 'active', 'achieved', 'expired'])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('achieved_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'generated_at']);
        });

        Schema::create('forecast_target_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_target_id')->constrained('forecast_targets')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('recommended_qty')->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('expected_revenue', 15, 2)->default(0);
            $table->decimal('expected_profit', 15, 2)->default(0);
            $table->integer('priority')->default(3); // 1-5, 1 is highest
            $table->timestamps();
            
            $table->index(['forecast_target_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_target_items');
        Schema::dropIfExists('forecast_targets');
    }
};
