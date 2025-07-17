<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
//use database\seeders\RolesAndPermissionsSeeder ;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CategorySeeder::class,
            AnimalGuideSeeder::class,
            CourseSeeder::class,
             VolunteerTypesSeeder::class,
        ]);  
      }
}
