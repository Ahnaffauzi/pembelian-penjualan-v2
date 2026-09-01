<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'SuperAdmin',
            'Sales',
            'Purchase',
            'Manager'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'SuperAdmin',
        ]);
        $admin->assignRole('SuperAdmin');

        $sales =User::create([
            'name' => 'Sales User',
            'email' => 'sales@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Sales',
        ]);
        $sales->assignRole('Sales');

        $purchase = User::create([
            'name' => 'Purchase User',
            'email' => 'purchase@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Purchase',
        ]);
        $purchase->assignRole('Purchase');

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Manager',
        ]);
        $manager->assignRole('Manager');
    }
}
