<?php

namespace Database\Seeders;

use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        SuperAdmin::updateOrCreate(
            ['email' => 'admin@hormonelens.com'],
            [
                'name' => 'HormoneLens Admin',
                'password' => Hash::make('admin123'),
            ],
        );
    }
}
