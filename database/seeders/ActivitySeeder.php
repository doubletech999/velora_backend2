<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            [
                'slug' => 'hiking',
                'name' => 'Hiking',
                'name_ar' => 'المشي لمسافات طويلة',
                'icon' => 'hiking',
                'color' => '#4CAF50',
                'description' => 'Trails suitable for hiking and walking',
                'is_active' => true,
            ],
            [
                'slug' => 'cycling',
                'name' => 'Cycling',
                'name_ar' => 'ركوب الدراجات',
                'icon' => 'bike',
                'color' => '#2196F3',
                'description' => 'Paths suitable for cycling',
                'is_active' => true,
            ],
            [
                'slug' => 'running',
                'name' => 'Running',
                'name_ar' => 'الجري',
                'icon' => 'run',
                'color' => '#FF5722',
                'description' => 'Routes suitable for running',
                'is_active' => true,
            ],
            [
                'slug' => 'camping',
                'name' => 'Camping',
                'name_ar' => 'التخييم',
                'icon' => 'tent',
                'color' => '#795548',
                'description' => 'Areas suitable for camping',
                'is_active' => true,
            ],
            [
                'slug' => 'photography',
                'name' => 'Photography',
                'name_ar' => 'التصوير',
                'icon' => 'camera',
                'color' => '#9C27B0',
                'description' => 'Scenic spots for photography',
                'is_active' => true,
            ],
            [
                'slug' => 'bird-watching',
                'name' => 'Bird Watching',
                'name_ar' => 'مراقبة الطيور',
                'icon' => 'bird',
                'color' => '#00BCD4',
                'description' => 'Areas suitable for bird watching',
                'is_active' => true,
            ],
            [
                'slug' => 'family',
                'name' => 'Family Friendly',
                'name_ar' => 'مناسب للعائلات',
                'icon' => 'family',
                'color' => '#FFC107',
                'description' => 'Paths suitable for families with children',
                'is_active' => true,
            ],
            [
                'slug' => 'nature',
                'name' => 'Nature Exploration',
                'name_ar' => 'استكشاف الطبيعة',
                'icon' => 'tree',
                'color' => '#8BC34A',
                'description' => 'Explore natural landscapes',
                'is_active' => true,
            ],
        ];

        foreach ($activities as $activity) {
            Activity::updateOrCreate(
                ['slug' => $activity['slug']],
                $activity
            );
        }
    }
}
