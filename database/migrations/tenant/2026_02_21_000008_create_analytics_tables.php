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
        // Analytics dashboards table
        Schema::connection('tenant')->create('analytics_dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('layout_json')->nullable(); // Widget positions and config
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'is_default']);
        });

        // Dashboard widgets table
        Schema::connection('tenant')->create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('analytics_dashboards')->onDelete('cascade');
            $table->string('widget_type'); // sales_chart, kpi_card, top_products, etc
            $table->string('title');
            $table->integer('position')->default(0);
            $table->integer('width')->default(6); // 1-12 grid
            $table->integer('height')->default(1); // rows
            $table->json('config_json')->nullable(); // Widget-specific config
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['dashboard_id', 'position']);
        });

        // Sales forecasts table
        Schema::connection('tenant')->create('sales_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->date('forecast_date');
            $table->decimal('predicted_sales', 15, 2)->default(0);
            $table->decimal('actual_sales', 15, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable(); // 0-100
            $table->string('model_version')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'forecast_date']);
            $table->index(['tenant_id', 'product_id', 'forecast_date']);
        });

        // Customer segments table
        Schema::connection('tenant')->create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('segment_type'); // RFM, CLV, behavior, etc
            $table->string('segment_value'); // e.g., "Champions", "At Risk"
            $table->decimal('score', 5, 2)->nullable(); // 0-100
            $table->json('metadata')->nullable(); // Additional segment data
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            $table->unique(['tenant_id', 'customer_id', 'segment_type']);
            $table->index(['tenant_id', 'segment_type']);
            $table->index(['segment_type', 'segment_value']);
        });

        // Automated reports table
        Schema::connection('tenant')->create('automated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('report_type'); // sales_summary, inventory, customer, etc
            $table->string('schedule'); // Cron expression or simple: daily, weekly, monthly
            $table->json('recipients'); // Array of email addresses
            $table->json('filters')->nullable(); // Report filters
            $table->string('format')->default('pdf'); // pdf, excel, csv
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'report_type']);
        });

        // Report execution log
        Schema::connection('tenant')->create('report_execution_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('automated_reports')->onDelete('cascade');
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->integer('records_processed')->default(0);
            $table->text('error_message')->nullable();
            $table->string('file_path')->nullable(); // Path to generated report
            $table->timestamp('executed_at');
            $table->timestamps();
            
            $table->index(['report_id', 'executed_at']);
        });

        // Analytics cache table (for performance)
        Schema::connection('tenant')->create('analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('cache_key');
            $table->json('cache_data');
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->unique(['tenant_id', 'cache_key']);
            $table->index(['tenant_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('analytics_cache');
        Schema::connection('tenant')->dropIfExists('report_execution_log');
        Schema::connection('tenant')->dropIfExists('automated_reports');
        Schema::connection('tenant')->dropIfExists('customer_segments');
        Schema::connection('tenant')->dropIfExists('sales_forecasts');
        Schema::connection('tenant')->dropIfExists('dashboard_widgets');
        Schema::connection('tenant')->dropIfExists('analytics_dashboards');
    }
};
