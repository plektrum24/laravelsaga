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
        // Subscription plans table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // free, pro, enterprise
            $table->decimal('price_monthly', 15, 2)->default(0);
            $table->decimal('price_yearly', 15, 2)->default(0);
            $table->json('features')->nullable(); // Feature flags
            $table->json('limits')->nullable(); // {users: 10, products: 100, etc}
            $table->integer('trial_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });

        // Tenant subscriptions table
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->after('id')
                ->constrained('subscription_plans')->nullOnDelete();
            $table->enum('subscription_status', ['trial', 'active', 'suspended', 'cancelled', 'expired'])
                ->default('trial')->after('subscription_id');
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            $table->timestamp('subscription_expires_at')->nullable()->after('trial_ends_at');
            $table->boolean('auto_renew')->default(true)->after('subscription_expires_at');
        });

        // Tenant usage tracking table
        Schema::connection('tenant')->create('tenant_usage', function (Blueprint $table) {
            $table->id();
            $table->string('metric'); // users, products, orders, storage_mb
            $table->integer('current_value')->default(0);
            $table->integer('limit_value')->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();
            
            $table->unique(['metric', 'period_start']);
            $table->index('period_end');
        });

        // Invoices table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('subscription_plans')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('invoice_type')->default('subscription'); // subscription, usage, one_time
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_gateway_id')->nullable();
            $table->string('payment_gateway_response')->nullable(); // JSON response
            $table->string('pdf_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('invoice_number');
            $table->index(['tenant_id', 'status']);
            $table->index('due_date');
        });

        // Payment methods table
        Schema::create('tenant_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('gateway'); // midtrans, xendit, stripe
            $table->string('gateway_customer_id')->nullable();
            $table->string('gateway_payment_method_id')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_default']);
        });

        // System settings table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            $table->index('key');
        });

        // Support tickets table
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('created_at');
        });

        // Support ticket messages table
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->boolean('is_internal')->default(false); // Internal notes
            $table->json('attachments')->nullable();
            $table->timestamps();
            
            $table->index('ticket_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('tenant_payment_methods');
        Schema::dropIfExists('invoices');
        
        Schema::connection('tenant')->dropIfExists('tenant_usage');
        
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn([
                'subscription_id',
                'subscription_status',
                'trial_ends_at',
                'subscription_expires_at',
                'auto_renew',
            ]);
        });
        
        Schema::dropIfExists('subscription_plans');
    }
};
