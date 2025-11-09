<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Storage, Bintex, Document, User};
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $storage = Storage::create([
            'name' => 'Demo Storage',
            'slug' => 'demo-storage',
            'description' => 'Storage for demo content',
            'created_by' => $admin->id,
        ]);

        $bintex = $storage->bintexes()->create([
            'name' => 'Peraturan Karyawan',
            'slug' => 'peraturan-karyawan',
            'description' => 'Kumpulan kebijakan & SOP',
            'created_by' => $admin->id,
        ]);

        $bintex->documents()->create([
            'title' => 'Panduan Kerja',
            'filename_original' => 'panduan_kerja.pdf',
            'file_path_private' => 'private/demo/panduan_kerja.pdf',
            'page_count' => 0,
            'is_published' => false,
            'allow_download' => false,
            'created_by' => $admin->id,
        ]);
    }
}
