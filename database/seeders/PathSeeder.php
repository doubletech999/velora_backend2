<?php

namespace Database\Seeders;

use App\Models\Path;
use App\Models\Activity;
use Illuminate\Database\Seeder;

class PathSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paths = [
            [
                'name' => 'Wadi Qelt Trail',
                'name_ar' => 'مسار وادي القلط',
                'description' => 'A beautiful trail through Wadi Qelt with stunning views of the canyon and St. George Monastery',
                'description_ar' => 'مسار جميل عبر وادي القلط مع مناظر خلابة للوادي ودير القديس جورج',
                'location' => 'Jerusalem',
                'location_ar' => 'القدس',
                'length' => 8.5,
                'estimated_duration' => 180,
                'difficulty' => 'moderate',
                'coordinates' => [
                    ['lat' => 31.8423, 'lng' => 35.3207],
                    ['lat' => 31.8445, 'lng' => 35.3234],
                    ['lat' => 31.8467, 'lng' => 35.3256],
                ],
                'warnings' => ['Bring plenty of water', 'Steep sections', 'Not suitable for children under 10'],
                'warnings_ar' => ['احضر الكثير من الماء', 'أقسام شديدة الانحدار', 'غير مناسب للأطفال دون سن 10'],
                'is_featured' => true,
            ],
            [
                'name' => 'Battir Terraces Walk',
                'name_ar' => 'مشي مدرجات بتير',
                'description' => 'Walk through the UNESCO World Heritage site of Battir with ancient agricultural terraces',
                'description_ar' => 'امشي عبر موقع التراث العالمي لليونسكو في بتير مع المدرجات الزراعية القديمة',
                'location' => 'Bethlehem',
                'location_ar' => 'بيت لحم',
                'length' => 5.2,
                'estimated_duration' => 120,
                'difficulty' => 'easy',
                'coordinates' => [
                    ['lat' => 31.7272, 'lng' => 35.1319],
                    ['lat' => 31.7290, 'lng' => 35.1335],
                    ['lat' => 31.7305, 'lng' => 35.1350],
                ],
                'warnings' => ['Respect private property', 'Stay on marked paths'],
                'warnings_ar' => ['احترم الملكية الخاصة', 'ابق على المسارات المحددة'],
                'is_featured' => true,
            ],
            [
                'name' => 'Mount Gerizim Trail',
                'name_ar' => 'مسار جبل جرزيم',
                'description' => 'Hike to the top of Mount Gerizim for panoramic views of Nablus',
                'description_ar' => 'تسلق إلى قمة جبل جرزيم للحصول على مناظر بانورامية لنابلس',
                'location' => 'Nablus',
                'location_ar' => 'نابلس',
                'length' => 6.8,
                'estimated_duration' => 150,
                'difficulty' => 'moderate',
                'coordinates' => [
                    ['lat' => 32.2036, 'lng' => 35.2728],
                    ['lat' => 32.2050, 'lng' => 35.2745],
                    ['lat' => 32.2065, 'lng' => 35.2760],
                ],
                'warnings' => ['Check security situation', 'Steep climb'],
                'warnings_ar' => ['تحقق من الوضع الأمني', 'صعود حاد'],
                'is_featured' => false,
            ],
            [
                'name' => 'Makhrour Valley Trail',
                'name_ar' => 'مسار وادي المخرور',
                'description' => 'A scenic valley trail with olive groves and natural springs',
                'description_ar' => 'مسار وادي خلاب مع بساتين الزيتون والينابيع الطبيعية',
                'location' => 'Bethlehem',
                'location_ar' => 'بيت لحم',
                'length' => 7.3,
                'estimated_duration' => 165,
                'difficulty' => 'moderate',
                'coordinates' => [
                    ['lat' => 31.7156, 'lng' => 35.1567],
                    ['lat' => 31.7170, 'lng' => 35.1580],
                    ['lat' => 31.7185, 'lng' => 35.1595],
                ],
                'warnings' => ['Slippery when wet', 'Watch for wildlife'],
                'warnings_ar' => ['زلق عندما يكون مبللاً', 'انتبه للحياة البرية'],
                'is_featured' => false,
            ],
            [
                'name' => 'Ein Prat Nature Reserve',
                'name_ar' => 'محمية عين فرات الطبيعية',
                'description' => 'Beautiful nature reserve with springs and pools perfect for summer',
                'description_ar' => 'محمية طبيعية جميلة مع ينابيع وبرك مثالية للصيف',
                'location' => 'Ramallah',
                'location_ar' => 'رام الله',
                'length' => 4.5,
                'estimated_duration' => 100,
                'difficulty' => 'easy',
                'coordinates' => [
                    ['lat' => 31.9156, 'lng' => 35.2890],
                    ['lat' => 31.9170, 'lng' => 35.2905],
                    ['lat' => 31.9185, 'lng' => 35.2920],
                ],
                'warnings' => ['Can get crowded on weekends', 'Bring swimwear'],
                'warnings_ar' => ['يمكن أن يزدحم في عطلات نهاية الأسبوع', 'احضر ملابس السباحة'],
                'is_featured' => true,
            ],
        ];

        $activities = Activity::all()->keyBy('slug');

        foreach ($paths as $pathData) {
            $path = Path::create(array_merge($pathData, [
                'images' => [
                    'https://example.com/path-image-1.jpg',
                    'https://example.com/path-image-2.jpg',
                ],
                'rating' => rand(35, 50) / 10,
                'review_count' => rand(5, 50),
                'is_active' => true,
            ]));

            // Attach random activities
            $activityTypes = match($path->difficulty) {
                'easy' => ['hiking', 'photography', 'family', 'nature'],
                'moderate' => ['hiking', 'cycling', 'photography', 'nature', 'bird-watching'],
                'hard' => ['hiking', 'running', 'camping', 'nature'],
                default => ['hiking', 'nature'],
            };

            $selectedActivities = array_rand(array_flip($activityTypes), rand(2, 4));
            if (!is_array($selectedActivities)) {
                $selectedActivities = [$selectedActivities];
            }

            foreach ($selectedActivities as $activitySlug) {
                if (isset($activities[$activitySlug])) {
                    $path->activities()->attach($activities[$activitySlug]->id);
                }
            }
        }
    }
}
