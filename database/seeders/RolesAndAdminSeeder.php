<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'label' => 'Administrator'],
            ['name' => 'editor', 'label' => 'Editor'],
            ['name' => 'viewer', 'label' => 'Viewer'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r['name']], $r);
        }

        // Buat user admin default (username: admin)
        if (!User::where('username', 'admin')->exists()) {
            $adminRole = Role::where('name', 'admin')->first();
            User::create([
                'name' => 'Super Admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('ChangeMe123!'), // ubah setelah setup
                'role_id' => $adminRole->id
            ]);
        }
    }
}
