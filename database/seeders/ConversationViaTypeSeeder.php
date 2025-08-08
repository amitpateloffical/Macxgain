<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class ConversationViaTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                 // Truncate the priorities table to start fresh
                 DB::table('conversation_via_types')->truncate();
        
                 \Log::info('Seeding conversation_via_types Table');
         
                 $conversation_types = [
                     [
                         'id' => 1,
                         'title' => 'What\'s App',
                         'slug' => 'What\'s App',
                     ],
                     [
                         'id' => 2,
                         'title' => 'Email',
                         'slug' => 'Email',
                     ],
                     [
                         'id' => 3,
                         'title' => 'Phone',
                         'slug' => 'Phone',
                     ],
                 ];
         
                 // Insert each priority into the conversation_types table
                 foreach ($conversation_types as $conv) {
                     DB::table('conversation_via_types')->insert($conv);
                 }
         
                 \Log::info('conversation_via_types Table Seeded');
    }
}
