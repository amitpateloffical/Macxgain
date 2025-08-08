<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConversationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Truncate the priorities table to start fresh
         DB::table('conversation_types')->truncate();
        
         \Log::info('Seeding conversation_types Table');
 
         $conversation_types = [
             [
                 'id' => 1,
                 'title' => 'Confirmation',
                 'slug' => 'Confirmation',
             ],
             [
                 'id' => 2,
                 'title' => 'Update',
                 'slug' => 'Update',
             ],
             [
                 'id' => 3,
                 'title' => 'Acknowledgment',
                 'slug' => 'Acknowledgment',
             ],
         ];
 
         // Insert each priority into the conversation_types table
         foreach ($conversation_types as $conv) {
             DB::table('conversation_types')->insert($conv);
         }
 
         \Log::info('conversation_types Table Seeded');
    }
}
