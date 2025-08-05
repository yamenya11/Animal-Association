<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CatgoryCourse;
class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $categories = [
            ['name' => 'nursing'],
            ['name' => 'First aid'],
            ['name' => 'feeding']
        ];

        foreach ($categories as $category) {
            CatgoryCourse::create($category);
        }
    }
}
