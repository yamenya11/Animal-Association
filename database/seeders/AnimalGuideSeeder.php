<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AnimalGuide;
use App\Models\Category;
class AnimalGuideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnimalGuide::truncate();
    $common = Category::where('name', 'common')->first()->id;
    $weird = Category::where('name', 'weird')->first()->id;
    $extinct = Category::where('name', 'extinct')->first()->id;

    AnimalGuide::insert([
        [
            'name' => 'الأسد',
            'type' => 'ثديي',
            'description' => 'حيوان مفترس يعيش في السافانا والغابات',
            'food' => 'اللحوم',
            'image' => 'animal_guides\lion.jpg',
            'category_id' => $common,
        ],
        [
            'name' => 'الأكسولوتل',
            'type' => 'برمائي',
            'description' => 'كائن غريب يعيش تحت الماء ويمكنه تجديد أعضائه',
            'food' => 'الديدان والحشرات المائية',
            'image' => 'animal_guides\سمندل.jpg',
            'category_id' => $weird,
        ],
        [
            'name' => 'الدودو',
            'type' => 'طائر منقرض',
            'description' => 'طائر لا يطير انقرض منذ قرون',
            'food' => 'الفواكه والبذور',
            'image' => 'animal_guides\الدودو.jpg',
            'category_id' => $extinct,
        ],
    ]);
    }
}
