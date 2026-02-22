<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'code' => 'free',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'features' => [
                    'pos_access',
                    'basic_inventory',
                    'basic_reports',
                ],
                'limits' => [
                    'users' => 3,
                    'products' => 100,
                    'branches' => 1,
                    'transactions_per_month' => 500,
                    'storage_mb' => 500,
                ],
                'trial_days' => 0,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'name' => 'Starter',
                'code' => 'starter',
                'price_monthly' => 99000,
                'price_yearly' => 990000,
                'features' => [
                    'pos_access',
                    'inventory_management',
                    'basic_reports',
                    'loyalty_program',
                    'barcode_generation',
                    'email_support',
                ],
                'limits' => [
                    'users' => 10,
                    'products' => 1000,
                    'branches' => 3,
                    'transactions_per_month' => 5000,
                    'storage_mb' => 2048,
                ],
                'trial_days' => 14,
                'is_active' => true,
                'priority' => 2,
            ],
            [
                'name' => 'Professional',
                'code' => 'pro',
                'price_monthly' => 299000,
                'price_yearly' => 2990000,
                'features' => [
                    'pos_access',
                    'advanced_inventory',
                    'advanced_reports',
                    'loyalty_program',
                    'barcode_generation',
                    'label_printing',
                    'stock_transfer',
                    'e_commerce',
                    'mobile_app',
                    'priority_support',
                    'api_access',
                ],
                'limits' => [
                    'users' => 50,
                    'products' => 10000,
                    'branches' => 10,
                    'transactions_per_month' => 50000,
                    'storage_mb' => 10240,
                ],
                'trial_days' => 14,
                'is_active' => true,
                'priority' => 3,
            ],
            [
                'name' => 'Enterprise',
                'code' => 'enterprise',
                'price_monthly' => 999000,
                'price_yearly' => 9990000,
                'features' => [
                    'pos_access',
                    'unlimited_inventory',
                    'advanced_analytics',
                    'loyalty_program',
                    'barcode_generation',
                    'label_printing',
                    'stock_transfer',
                    'e_commerce',
                    'mobile_app',
                    'white_label',
                    'custom_fields',
                    'api_access',
                    'dedicated_support',
                    'sla',
                    'custom_integrations',
                ],
                'limits' => [
                    'users' => 0, // Unlimited
                    'products' => 0, // Unlimited
                    'branches' => 0, // Unlimited
                    'transactions_per_month' => 0, // Unlimited
                    'storage_mb' => 102400, // 100 GB
                ],
                'trial_days' => 30,
                'is_active' => true,
                'priority' => 4,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['code' => $planData['code']],
                $planData
            );
        }

        $this->command->info('Subscription plans seeded successfully!');
    }
}
