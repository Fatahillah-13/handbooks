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
        // Pastikan role terbuat / ter-update
        $roles = [
            ['name' => 'admin',  'label' => 'Administrator'],
            ['name' => 'editor', 'label' => 'Editor'],
            ['name' => 'viewer', 'label' => 'Viewer'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['name' => $r['name']], $r);
        }

        // Buat / perbarui user admin (username: admin)
        $adminRole = Role::where('name', 'admin')->first();

        $admin = User::firstOrNew(['username' => 'admin']); // kunci unik
        $admin->fill([
            'name'    => 'Super Admin',
            'email'   => 'admin@example.com',
            'role_id' => $adminRole?->id,
            // opsional: 'email_verified_at' => now(),
        ]);

        $plain = 'ChangeMe123!'; // bisa ambil dari env('ADMIN_PASSWORD', 'ChangeMe123!')
        // Set password hanya jika user baru atau password sekarang tidak sama dengan yang diinginkan
        if (!$admin->exists || !Hash::check($plain, (string) $admin->password)) {
            $admin->password = Hash::make($plain);
        }

        $admin->save();
    }
}
