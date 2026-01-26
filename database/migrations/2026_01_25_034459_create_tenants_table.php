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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Nama Toko/Usaha
            $table->string('owner_name', 100);
            $table->enum('business_type', ['retail', 'barber', 'laundry', 'car_wash', 'cafe'])->default('retail');
            $table->enum('subscription_plan', ['basic', 'pro', 'enterprise'])->default('basic');
            $table->string('domain', 100)->unique()->nullable(); // Subdomain/Domain
            $table->string('database_name', 100)->nullable(); // Jika pakai multi-db
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
