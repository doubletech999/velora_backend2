<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            // Explorer Achievements
            [
                'category' => 'explorer',
                'slug' => 'first-path',
                'title' => 'First Steps',
                'title_ar' => 'الخطوات الأولى',
                'description' => 'Complete your first path',
                'description_ar' => 'أكمل أول مسار لك',
                'icon' => 'footsteps',
                'points' => 10,
                'requirements' => ['completed_paths' => 1],
            ],
            [
                'category' => 'explorer',
                'slug' => 'explorer-5',
                'title' => 'Explorer',
                'title_ar' => 'المستكشف',
                'description' => 'Complete 5 different paths',
                'description_ar' => 'أكمل 5 مسارات مختلفة',
                'icon' => 'compass',
                'points' => 25,
                'requirements' => ['completed_paths' => 5],
            ],
            [
                'category' => 'explorer',
                'slug' => 'pathfinder',
                'title' => 'Pathfinder',
                'title_ar' => 'مكتشف المسارات',
                'description' => 'Complete 10 different paths',
                'description_ar' => 'أكمل 10 مسارات مختلفة',
                'icon' => 'map',
                'points' => 50,
                'requirements' => ['completed_paths' => 10],
            ],

            // Hiker Achievements
            [
                'category' => 'hiker',
                'slug' => 'distance-10km',
                'title' => '10K Club',
                'title_ar' => 'نادي 10 كم',
                'description' => 'Travel a total of 10 kilometers',
                'description_ar' => 'اقطع مسافة إجمالية 10 كيلومترات',
                'icon' => 'milestone',
                'points' => 15,
                'requirements' => ['total_distance' => 10],
            ],
            [
                'category' => 'hiker',
                'slug' => 'distance-50km',
                'title' => 'Half Century',
                'title_ar' => 'نصف قرن',
                'description' => 'Travel a total of 50 kilometers',
                'description_ar' => 'اقطع مسافة إجمالية 50 كيلومتر',
                'icon' => 'trophy',
                'points' => 40,
                'requirements' => ['total_distance' => 50],
            ],
            [
                'category' => 'hiker',
                'slug' => 'distance-100km',
                'title' => 'Century Rider',
                'title_ar' => 'راكب القرن',
                'description' => 'Travel a total of 100 kilometers',
                'description_ar' => 'اقطع مسافة إجمالية 100 كيلومتر',
                'icon' => 'medal',
                'points' => 75,
                'requirements' => ['total_distance' => 100],
            ],

            // Challenge Achievements
            [
                'category' => 'challenge',
                'slug' => 'hard-path',
                'title' => 'Challenge Accepted',
                'title_ar' => 'قبول التحدي',
                'description' => 'Complete a hard difficulty path',
                'description_ar' => 'أكمل مسار بصعوبة عالية',
                'icon' => 'mountain',
                'points' => 30,
                'requirements' => ['difficulty' => 'hard', 'count' => 1],
            ],
            [
                'category' => 'challenge',
                'slug' => 'streak-3',
                'title' => 'Consistent Explorer',
                'title_ar' => 'المستكشف المستمر',
                'description' => 'Complete paths on 3 consecutive days',
                'description_ar' => 'أكمل مسارات في 3 أيام متتالية',
                'icon' => 'calendar',
                'points' => 20,
                'requirements' => ['consecutive_days' => 3],
            ],
            [
                'category' => 'challenge',
                'slug' => 'streak-7',
                'title' => 'Week Warrior',
                'title_ar' => 'محارب الأسبوع',
                'description' => 'Complete paths on 7 consecutive days',
                'description_ar' => 'أكمل مسارات في 7 أيام متتالية',
                'icon' => 'fire',
                'points' => 50,
                'requirements' => ['consecutive_days' => 7],
            ],

            // Region Specific (Palestine)
            [
                'category' => 'region_specific',
                'slug' => 'ramallah-explorer',
                'title' => 'Ramallah Explorer',
                'title_ar' => 'مستكشف رام الله',
                'description' => 'Complete 3 paths in Ramallah area',
                'description_ar' => 'أكمل 3 مسارات في منطقة رام الله',
                'icon' => 'location',
                'points' => 25,
                'requirements' => ['region' => 'Ramallah', 'paths_count' => 3],
            ],
            [
                'category' => 'region_specific',
                'slug' => 'jerusalem-trails',
                'title' => 'Jerusalem Trails',
                'title_ar' => 'مسارات القدس',
                'description' => 'Complete 3 paths in Jerusalem area',
                'description_ar' => 'أكمل 3 مسارات في منطقة القدس',
                'icon' => 'location',
                'points' => 25,
                'requirements' => ['region' => 'Jerusalem', 'paths_count' => 3],
            ],
            [
                'category' => 'region_specific',
                'slug' => 'bethlehem-walker',
                'title' => 'Bethlehem Walker',
                'title_ar' => 'مشاء بيت لحم',
                'description' => 'Complete 3 paths in Bethlehem area',
                'description_ar' => 'أكمل 3 مسارات في منطقة بيت لحم',
                'icon' => 'location',
                'points' => 25,
                'requirements' => ['region' => 'Bethlehem', 'paths_count' => 3],
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['slug' => $achievement['slug']],
                array_merge($achievement, ['is_active' => true])
            );
        }
    }
}
