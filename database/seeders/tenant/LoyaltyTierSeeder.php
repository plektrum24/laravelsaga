<?php

namespace Database\Seeders\tenant;

use App\Models\MembershipTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoyaltyTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get tenant ID from auth or use first tenant
        $tenantId = DB::table('tenants')->value('id');
        
        if (!$tenantId) {
            $this->command->error('No tenant found. Please create a tenant first.');
            return;
        }

        $tiers = [
            [
                'name' => 'Bronze',
                'min_spend' => 0,
                'min_visits' => 0,
                'benefits' => [
                    'discount_percent' => 0,
                    'points_multiplier' => 1.0,
                    'birthday_bonus' => 0,
                    'free_shipping_threshold' => null,
                ],
                'badge_color' => '#CD7F32', // Bronze color
                'priority' => 1,
                'active' => true,
            ],
            [
                'name' => 'Silver',
                'min_spend' => 1000000, // Rp 1,000,000
                'min_visits' => 10,
                'benefits' => [
                    'discount_percent' => 2,
                    'points_multiplier' => 1.2,
                    'birthday_bonus' => 50,
                    'free_shipping_threshold' => 100000,
                ],
                'badge_color' => '#C0C0C0', // Silver color
                'priority' => 2,
                'active' => true,
            ],
            [
                'name' => 'Gold',
                'min_spend' => 5000000, // Rp 5,000,000
                'min_visits' => 50,
                'benefits' => [
                    'discount_percent' => 5,
                    'points_multiplier' => 1.5,
                    'birthday_bonus' => 200,
                    'free_shipping_threshold' => 50000,
                ],
                'badge_color' => '#FFD700', // Gold color
                'priority' => 3,
                'active' => true,
            ],
            [
                'name' => 'Platinum',
                'min_spend' => 10000000, // Rp 10,000,000
                'min_visits' => 100,
                'benefits' => [
                    'discount_percent' => 10,
                    'points_multiplier' => 2.0,
                    'birthday_bonus' => 500,
                    'free_shipping_threshold' => 0, // Always free shipping
                ],
                'badge_color' => '#E5E4E2', // Platinum color
                'priority' => 4,
                'active' => true,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($tiers as $tierData) {
            $tierData['tenant_id'] = $tenantId;
            
            $existing = MembershipTier::where('tenant_id', $tenantId)
                ->where('name', $tierData['name'])
                ->first();
            
            if ($existing) {
                $existing->update($tierData);
                $updated++;
                $this->command->info("Updated {$tierData['name']} tier");
            } else {
                MembershipTier::create($tierData);
                $created++;
                $this->command->info("Created {$tierData['name']} tier");
            }
        }

        $this->command->info("✓ Loyalty tiers seeded: {$created} created, {$updated} updated");
    }
}
