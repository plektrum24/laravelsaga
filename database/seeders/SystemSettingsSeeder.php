<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // SaaS Settings
            [
                'key' => 'saas.enabled',
                'value' => true,
                'description' => 'Enable/disable SaaS features',
                'is_public' => false,
            ],
            [
                'key' => 'saas.allow_self_signup',
                'value' => true,
                'description' => 'Allow tenants to sign up themselves',
                'is_public' => false,
            ],
            [
                'key' => 'saas.default_trial_days',
                'value' => 14,
                'description' => 'Default trial period in days',
                'is_public' => false,
            ],

            // Billing Settings
            [
                'key' => 'billing.tax_rate',
                'value' => 0,
                'description' => 'Default tax rate percentage',
                'is_public' => false,
            ],
            [
                'key' => 'billing.invoice_due_days',
                'value' => 14,
                'description' => 'Default invoice due days',
                'is_public' => false,
            ],
            [
                'key' => 'billing.auto_retry_failed_payments',
                'value' => true,
                'description' => 'Automatically retry failed payments',
                'is_public' => false,
            ],
            [
                'key' => 'billing.payment_retry_days',
                'value' => [1, 3, 7, 14],
                'description' => 'Days to retry failed payments',
                'is_public' => false,
            ],

            // Notification Settings
            [
                'key' => 'notification.subscription_expiry_reminder',
                'value' => [7, 3, 1],
                'description' => 'Days before expiry to send reminder',
                'is_public' => false,
            ],
            [
                'key' => 'notification.invoice_due_reminder',
                'value' => [7, 3, 1],
                'description' => 'Days before due date to send reminder',
                'is_public' => false,
            ],
            [
                'key' => 'notification.send_overdue_notices',
                'value' => true,
                'description' => 'Send notices for overdue invoices',
                'is_public' => false,
            ],

            // Usage Tracking
            [
                'key' => 'usage.track_users',
                'value' => true,
                'description' => 'Track user count against limits',
                'is_public' => false,
            ],
            [
                'key' => 'usage.track_products',
                'value' => true,
                'description' => 'Track product count against limits',
                'is_public' => false,
            ],
            [
                'key' => 'usage.track_storage',
                'value' => true,
                'description' => 'Track storage usage against limits',
                'is_public' => false,
            ],
            [
                'key' => 'usage.soft_limit_enforcement',
                'value' => true,
                'description' => 'Warn but allow exceeding soft limits',
                'is_public' => false,
            ],

            // Support Settings
            [
                'key' => 'support.enabled',
                'value' => true,
                'description' => 'Enable support ticket system',
                'is_public' => true,
            ],
            [
                'key' => 'support.response_sla_hours',
                'value' => [
                    'low' => 72,
                    'medium' => 48,
                    'high' => 24,
                    'urgent' => 4,
                ],
                'description' => 'SLA response time in hours by priority',
                'is_public' => true,
            ],
            [
                'key' => 'support.auto_assign_tickets',
                'value' => true,
                'description' => 'Automatically assign tickets to support staff',
                'is_public' => false,
            ],

            // Branding (White Label)
            [
                'key' => 'branding.platform_name',
                'value' => 'SAGA POS',
                'description' => 'Platform name for white-label',
                'is_public' => true,
            ],
            [
                'key' => 'branding.support_email',
                'value' => 'support@sagaposo.com',
                'description' => 'Support email address',
                'is_public' => true,
            ],
            [
                'key' => 'branding.support_phone',
                'value' => '+62-xxx-xxxx-xxxx',
                'description' => 'Support phone number',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $settingData) {
            SystemSetting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }

        $this->command->info('System settings seeded successfully!');
    }
}
