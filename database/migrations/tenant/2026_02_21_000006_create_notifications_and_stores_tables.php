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
        // Push notification devices table
        Schema::connection('tenant')->create('push_notification_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('device_token');
            $table->enum('device_type', ['ios', 'android']);
            $table->string('device_id')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'device_token']);
            $table->unique(['customer_id', 'device_token']);
        });

        // Push notifications log table
        Schema::connection('tenant')->create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->string('type'); // order_update, promotional, points_expiry, etc
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'created_at']);
            $table->index(['customer_id', 'read_at']);
        });

        // Add notification_preferences to customers table
        Schema::connection('tenant')->table('customers', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->after('phone');
        });

        // Stores table for store locator
        Schema::connection('tenant')->create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('ID');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('opening_hours')->nullable(); // JSON format
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active']);
            $table->spatialIndex('latitude'); // For location queries
            $table->spatialIndex('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('stores');
        Schema::connection('tenant')->table('customers', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
        Schema::connection('tenant')->dropIfExists('push_notifications');
        Schema::connection('tenant')->dropIfExists('push_notification_devices');
    }
};
