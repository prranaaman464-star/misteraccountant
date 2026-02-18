<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Phones',
            'Laptops',
            'Tablets',
            'Desktops',
            'Monitors',
            'Printers',
            'Scanners',
            'Projectors',
            'Speakers',
            'Headphones',
        ];

        foreach ($names as $name) {
            Category::query()->firstOrCreate(['name' => $name]);
        }
    }
}
