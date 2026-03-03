<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'moderator',
            'member',
            'advertiser',
            'verified_business',
            'community_rep',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $admin = User::where('email', 'admin@local.test')->first();

        if ($admin) {
            $admin->assignRole('admin');
        }
    }
}
