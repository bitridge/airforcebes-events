<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EventTag;

class EventTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Technology',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Business',
                'color' => '#10B981',
            ],
            [
                'name' => 'Education',
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Healthcare',
                'color' => '#EF4444',
            ],
            [
                'name' => 'Finance',
                'color' => '#84CC16',
            ],
            [
                'name' => 'Marketing',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'Design',
                'color' => '#F97316',
            ],
            [
                'name' => 'Leadership',
                'color' => '#06B6D4',
            ],
            [
                'name' => 'Innovation',
                'color' => '#EC4899',
            ],
            [
                'name' => 'Sustainability',
                'color' => '#059669',
            ],
            [
                'name' => 'Remote',
                'color' => '#7C3AED',
            ],
            [
                'name' => 'In-Person',
                'color' => '#DC2626',
            ],
            [
                'name' => 'Hybrid',
                'color' => '#EA580C',
            ],
            [
                'name' => 'Beginner',
                'color' => '#16A34A',
            ],
            [
                'name' => 'Advanced',
                'color' => '#B91C1C',
            ],
            [
                'name' => 'Certification',
                'color' => '#C2410C',
            ],
        ];

        foreach ($tags as $tag) {
            EventTag::create($tag);
        }
    }
}
