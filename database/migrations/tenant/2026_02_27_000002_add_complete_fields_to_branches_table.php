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
        Schema::table('branches', function (Blueprint $table) {
            // Add tenant_id for multi-tenant support (no foreign key in tenant DB)
            $table->unsignedBigInteger('tenant_id')->after('id')->nullable();
            $table->index('tenant_id');
            
            // Add contact information
            $table->string('phone', 20)->after('address')->nullable();
            $table->string('email', 100)->after('phone')->nullable();
            
            // Add location details
            $table->string('city', 100)->after('email')->nullable();
            $table->string('province', 100)->after('city')->nullable();
            $table->string('postal_code', 20)->after('province')->nullable();
            
            // Add manager information
            $table->string('manager_name', 100)->after('postal_code')->nullable();
            $table->string('manager_phone', 20)->after('manager_name')->nullable();
            
            // Add status column
            $table->string('status', 20)->after('is_main')->default('active');
            
            // Add indexes for better performance
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'city']);
            
            $table->dropColumn([
                'tenant_id',
                'phone',
                'email',
                'city',
                'province',
                'postal_code',
                'manager_name',
                'manager_phone',
                'status'
            ]);
        });
    }
};
