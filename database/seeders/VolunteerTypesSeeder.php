<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class VolunteerTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $types = [
            [
                'name_en' => 'Cleaning Shelters',
                'slug' => 'cleaning_shelters',
                'description' => 'Volunteers for cleaning and organizing animal shelters'
            ],
            [
                'name_en' => 'Animal Care',
                'slug' => 'animal_care',
                'description' => 'Volunteers for animal care and feeding'
            ],
            [
                'name_en' => 'Photography and Documentation',
                'slug' => 'photography_and_documentation',
                'description' => 'Volunteers for photography and event documentation'
            ],
            [
                'name_en' => 'Design and Marketing',
                'slug' => 'design_and_markiting',
                'description' => 'Volunteers for design and marketing materials'
            ],
            [
                'name_en' => 'Social Media Administration',
                'slug' => 'social_midea_administrator',
                'description' => 'Volunteers for managing social media accounts'
            ],
            [
                'name_en' => 'School Awareness',
                'slug' => 'school_awareness',
                'description' => 'Volunteers for school awareness programs'
            ]
        ];

        foreach ($types as $type) {
            DB::table('volunteer_types')->insert([
                'name_en' => $type['name_en'],
                'slug' => $type['slug'],
                'description' => $type['description'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    
    }
}
