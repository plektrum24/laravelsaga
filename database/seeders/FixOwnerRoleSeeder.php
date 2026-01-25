<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FixOwnerRoleSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email', 'owner@sagatoko.com')->first();
        if ($user) {
            $user->role = 'tenant_owner';
            $user->branch_id = 1;
            // $user->password = Hash::make('password'); // Optional: reset password if needed
            $user->save();
            $this->command->info("User {$user->email} role updated to: {$user->role}");
        } else {
            // Create if not exists
            User::create([
                'name' => 'Owner Saga',
                'email' => 'owner@sagatoko.com',
                'password' => Hash::make('password'),
                'role' => 'tenant_owner',
                'branch_id' => 1
            ]);
            $this->command->info("User owner@sagatoko.com created with role: tenant_owner");
        }
    }
}
