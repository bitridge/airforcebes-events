<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EventCategory;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Conferences',
                'description' => 'Large-scale professional conferences and symposiums',
                'color' => '#3B82F6',
                'icon' => 'academic-cap',
                'sort_order' => 1,
            ],
            [
                'name' => 'Workshops',
                'description' => 'Hands-on training and skill development sessions',
                'color' => '#10B981',
                'icon' => 'wrench-screwdriver',
                'sort_order' => 2,
            ],
            [
                'name' => 'Seminars',
                'description' => 'Educational presentations and discussions',
                'color' => '#F59E0B',
                'icon' => 'presentation-chart-line',
                'sort_order' => 3,
            ],
            [
                'name' => 'Networking Events',
                'description' => 'Social and professional networking opportunities',
                'color' => '#8B5CF6',
                'icon' => 'user-group',
                'sort_order' => 4,
            ],
            [
                'name' => 'Training Programs',
                'description' => 'Comprehensive training and certification programs',
                'color' => '#EF4444',
                'icon' => 'academic-cap',
                'sort_order' => 5,
            ],
            [
                'name' => 'Webinars',
                'description' => 'Online educational and informational sessions',
                'color' => '#06B6D4',
                'icon' => 'computer-desktop',
                'sort_order' => 6,
            ],
            [
                'name' => 'Hackathons',
                'description' => 'Competitive coding and innovation events',
                'color' => '#84CC16',
                'icon' => 'code-bracket',
                'sort_order' => 7,
            ],
            [
                'name' => 'Meetups',
                'description' => 'Informal community gatherings and discussions',
                'color' => '#F97316',
                'icon' => 'users',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            EventCategory::create($category);
        }
    }
}
