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
        // Check if columns already exist before adding
        if (!Schema::hasColumn('branches', 'phone')) {
            Schema::table('branches', function (Blueprint $table) {
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
                
                // Add status column (instead of is_active boolean)
                $table->string('status', 20)->after('is_main')->default('active');
                
                // Add indexes for better performance
                $table->index('status');
                $table->index('city');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex('status');
            $table->dropIndex('city');
            
            $table->dropColumn([
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
