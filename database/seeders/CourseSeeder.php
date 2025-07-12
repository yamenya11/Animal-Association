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
            ['name' => 'Medical Courses'],
            ['name' => 'Health & Wellness'],
            ['name' => 'Specialized Treatments']
        ];

        foreach ($categories as $category) {
            CatgoryCourse::create($category);
        }
    }
}
