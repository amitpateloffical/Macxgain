<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        // Second Admin User
        $user2 = new User();
        $user2->name = 'MacXgain Admin';
        $user2->email = 'admin@macxgain.com';
        $user2->mobile_code = '91';
        $user2->phone = '9876543211';
        $user2->is_admin = true;
        $user2->password = '1234567890';
        $user2->bank_name = 'HDFC Bank';
        $user2->account_no = '9876543210987654';
        $user2->ifsc_code = 'HDFC0001234';
        $user2->aadhar_number = '987654321098';
        $user2->pan_number = 'FGHIJ5678K';
        $user2->address = '456 Business District, Delhi, India - 110001';
        $user2->created_at = now();
        $user2->updated_at = now();
        $user2->save();
        

    }
}
