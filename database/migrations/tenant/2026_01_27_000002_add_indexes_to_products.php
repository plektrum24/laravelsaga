<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Check if index exists before adding to avoid duplicate errors would be complex in migration
            // typically we just add them. If they exist, it might error, but standard Laravel doesn't "check" easily without raw SQL.
            // Safe approach: Add if likely missing. 
            // Better: use a name for the index and try-catch or just add them.

            // Assuming they don't exist given the performance complaint.
            // $table->index('category_id');
            // $table->index('name');
            // $table->index('sku');
            // $table->index('barcode');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['name']);
            $table->dropIndex(['sku']);
            $table->dropIndex(['barcode']);
        });
    }
};
