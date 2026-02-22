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
        // Product reviews table
        Schema::connection('tenant')->create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('web_orders')->onDelete('set null');
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            
            $table->index(['product_id', 'status']);
            $table->index(['customer_id', 'status']);
        });

        // Product shares table (for analytics)
        Schema::connection('tenant')->create('product_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('platform'); // whatsapp, facebook, twitter, email, sms
            $table->timestamp('shared_at');
            $table->timestamps();
            
            $table->index(['product_id', 'platform']);
        });

        // Scan & Go sessions table
        Schema::connection('tenant')->create('scan_and_go_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('web_orders')->onDelete('set null');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
        });

        // Scan & Go items table
        Schema::connection('tenant')->create('scan_and_go_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('scan_and_go_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('qty')->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['session_id', 'product_id']);
        });

        // Add wishlist column to customers table
        Schema::connection('tenant')->table('customers', function (Blueprint $table) {
            $table->json('wishlist')->nullable()->after('notification_preferences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('scan_and_go_items');
        Schema::connection('tenant')->dropIfExists('scan_and_go_sessions');
        Schema::connection('tenant')->dropIfExists('product_shares');
        Schema::connection('tenant')->dropIfExists('product_reviews');
        
        Schema::connection('tenant')->table('customers', function (Blueprint $table) {
            $table->dropColumn('wishlist');
        });
    }
};
