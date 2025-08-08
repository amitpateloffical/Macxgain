<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the priorities table to start fresh
        DB::table('priorities')->truncate();
        
        \Log::info('Seeding Priorities Table');

        $priorities = [
            [
                'id' => 1,
                'title' => 'High',
                'description' => 'High Priority',
            ],
            [
                'id' => 2,
                'title' => 'Medium',
                'description' => 'Medium Priority',
            ],
            [
                'id' => 3,
                'title' => 'Low',
                'description' => 'Low Priority',
            ],
        ];

        // Insert each priority into the priorities table
        foreach ($priorities as $priority) {
            DB::table('priorities')->insert($priority);
        }

        \Log::info('Priorities Table Seeded');
    }
}
