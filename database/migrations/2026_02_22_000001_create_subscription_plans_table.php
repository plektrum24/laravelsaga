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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Starter, Pro, Enterprise
            $table->string('code')->unique(); // free, starter, pro, enterprise
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->default(0);
            $table->json('features')->nullable(); // Feature flags
            $table->json('limits')->nullable(); // user_limit, product_limit, etc
            $table->integer('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // For sorting
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
