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
        Schema::table('product_units', function (Blueprint $table) {
            if (!Schema::hasColumn('product_units', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index(); // Using nullable for now to avoid strict issues on existing data
            }
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_movements', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};
