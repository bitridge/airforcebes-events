<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EventSeries;

class EventSeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $series = [
            [
                'name' => 'Tech Leadership Summit',
                'description' => 'Annual technology leadership conference series',
                'sort_order' => 1,
            ],
            [
                'name' => 'Digital Marketing Workshop Series',
                'description' => 'Quarterly digital marketing skill development workshops',
                'sort_order' => 2,
            ],
            [
                'name' => 'Healthcare Innovation Forum',
                'description' => 'Bi-annual healthcare technology and innovation discussions',
                'sort_order' => 3,
            ],
            [
                'name' => 'Startup Accelerator Program',
                'description' => 'Monthly startup mentoring and networking events',
                'sort_order' => 4,
            ],
            [
                'name' => 'Data Science Bootcamp',
                'description' => 'Intensive data science training program series',
                'sort_order' => 5,
            ],
            [
                'name' => 'Sustainability Conference',
                'description' => 'Annual environmental and sustainability conference',
                'sort_order' => 6,
            ],
            [
                'name' => 'Women in Tech Meetup',
                'description' => 'Monthly networking and mentorship for women in technology',
                'sort_order' => 7,
            ],
            [
                'name' => 'AI & Machine Learning Symposium',
                'description' => 'Quarterly AI and ML research and application symposium',
                'sort_order' => 8,
            ],
        ];

        foreach ($series as $seriesItem) {
            EventSeries::create($seriesItem);
        }
    }
}
