<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@ptbpi.co.id',
            'password' => 'password123',
            'department' => 'IT',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'John Doe',
            'email' => 'john@ptbpi.co.id',
            'password' => 'password123',
            'department' => 'Engineering',
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@ptbpi.co.id',
            'password' => 'password123',
            'department' => 'Marketing',
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Bob Wilson',
            'email' => 'bob@ptbpi.co.id',
            'password' => 'password123',
            'department' => 'Finance',
            'role' => 'user',
        ]);

        $assets = [
            ['asset_number' => 'LAP-001', 'asset_name' => 'Laptop Dell XPS 15', 'total_stock' => 10, 'available_stock' => 10],
            ['asset_number' => 'PRJ-001', 'asset_name' => 'Projector Epson EB-X51', 'total_stock' => 5, 'available_stock' => 5],
            ['asset_number' => 'CAM-001', 'asset_name' => 'Camera Canon EOS R6', 'total_stock' => 3, 'available_stock' => 3],
            ['asset_number' => 'MIC-001', 'asset_name' => 'Microphone Blue Yeti', 'total_stock' => 8, 'available_stock' => 8],
            ['asset_number' => 'MON-001', 'asset_name' => 'Monitor LG 27" 4K', 'total_stock' => 15, 'available_stock' => 15],
            ['asset_number' => 'TAB-001', 'asset_name' => 'iPad Pro 12.9"', 'total_stock' => 6, 'available_stock' => 6],
            ['asset_number' => 'KEY-001', 'asset_name' => 'Mechanical Keyboard', 'total_stock' => 20, 'available_stock' => 20],
            ['asset_number' => 'HSP-001', 'asset_name' => 'Headset Sony WH-1000XM5', 'total_stock' => 12, 'available_stock' => 12],
        ];

        foreach ($assets as $asset) {
            Asset::create(array_merge($asset, [
                'asset_id' => Asset::generateAssetId(),
            ]));
        }
    }
}
