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
        Schema::connection('tenant')->create('loyalty_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->decimal('earn_rate', 15, 2)->default(10000); // 1 point per 10k
            $table->string('earn_currency', 3)->default('IDR');
            $table->decimal('point_value', 15, 4)->default(100); // 1 point = 100 IDR
            $table->integer('min_redemption_points')->default(100);
            $table->decimal('max_redemption_percent', 5, 2)->default(50.00);
            $table->integer('points_expiry_months')->default(12);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            $table->unique('tenant_id');
        });

        Schema::connection('tenant')->create('customer_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->decimal('points', 15, 2);
            $table->enum('type', ['earn', 'redeem', 'adjust', 'expire', 'refund']);
            $table->string('reference_type'); // transaction, adjustment, campaign, reward
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->datetime('expiry_date')->nullable();
            $table->decimal('balance_after', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'type']);
            $table->index(['customer_id', 'expiry_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::connection('tenant')->create('membership_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name'); // Bronze, Silver, Gold, Platinum
            $table->decimal('min_spend', 15, 2)->default(0);
            $table->integer('min_visits')->default(0);
            $table->json('benefits')->nullable(); // {discount_percent, points_multiplier, etc}
            $table->string('badge_color')->default('#6B7280');
            $table->integer('priority')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index('tenant_id');
        });

        Schema::connection('tenant')->create('customer_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('tier_id')->constrained('membership_tiers')->onDelete('cascade');
            $table->datetime('qualified_at');
            $table->datetime('valid_until')->nullable();
            $table->unsignedBigInteger('previous_tier_id')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'qualified_at']);
        });

        Schema::connection('tenant')->create('reward_catalog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points_cost')->default(0);
            $table->integer('stock')->nullable(); // null = infinite
            $table->string('image_url')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->datetime('active_from')->nullable();
            $table->datetime('active_to')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
        });

        Schema::connection('tenant')->create('customer_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('reward_id')->constrained('reward_catalog')->onDelete('cascade');
            $table->integer('points_redeemed')->default(0);
            $table->enum('status', ['pending', 'fulfilled', 'expired', 'cancelled'])->default('pending');
            $table->datetime('fulfilled_at')->nullable();
            $table->datetime('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('customer_rewards');
        Schema::connection('tenant')->dropIfExists('reward_catalog');
        Schema::connection('tenant')->dropIfExists('customer_tiers');
        Schema::connection('tenant')->dropIfExists('membership_tiers');
        Schema::connection('tenant')->dropIfExists('customer_points');
        Schema::connection('tenant')->dropIfExists('loyalty_settings');
    }
};
