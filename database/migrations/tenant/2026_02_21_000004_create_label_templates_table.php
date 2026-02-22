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
        Schema::connection('tenant')->create('label_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->enum('template_type', ['price_tag', 'shelf_label', 'barcode_label', 'custom'])->default('custom');
            $table->decimal('width_mm', 8, 2)->default(50); // mm
            $table->decimal('height_mm', 8, 2)->default(30); // mm
            $table->json('layout_json'); // Field positions, fonts, colors, etc
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['tenant_id', 'template_type']);
            $table->index(['tenant_id', 'is_default']);
        });

        // Print jobs for tracking print history
        Schema::connection('tenant')->create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('label_templates')->onDelete('set null');
            $table->json('product_ids'); // Array of product IDs
            $table->integer('quantity')->default(1);
            $table->enum('status', ['pending', 'printing', 'completed', 'failed'])->default('pending');
            $table->string('printer_name')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('print_jobs');
        Schema::connection('tenant')->dropIfExists('label_templates');
    }
};
