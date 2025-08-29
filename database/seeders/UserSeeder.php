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
        // First Admin User
        $user = new User();
        $user->name = 'kamlesh patel';
        $user->email = 'admin@test.com';
        $user->mobile_code = '91';
        $user->phone = '9876543210';
        $user->is_admin = true;
        $user->password='password';
        $user->bank_name = 'State Bank of India';
        $user->account_no = '1234567890123456';
        $user->ifsc_code = 'SBIN0001234';
        $user->aadhar_number = '123456789012';
        $user->pan_number = 'ABCDE1234F';
        $user->address = '123 Main Street, Mumbai, Maharashtra, India - 400001';
        $user->created_at= now();
        $user->updated_at= now();
        $user->save();

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
        
        // Sample Employee User
        $user3 = new User();
        $user3->name = 'John Employee';
        $user3->email = 'employee@macxgain.com';
        $user3->mobile_code = '91';
        $user3->phone = '9876543212';
        $user3->is_admin = false;
        $user3->password = 'password123';
        $user3->bank_name = 'ICICI Bank';
        $user3->account_no = '5432109876543210';
        $user3->ifsc_code = 'ICIC0001234';
        $user3->aadhar_number = '456789012345';
        $user3->pan_number = 'KLMNO9012P';
        $user3->address = '789 Employee Colony, Bangalore, Karnataka, India - 560001';
        $user3->created_at = now();
        $user3->updated_at = now();
        $user3->save();
    }
}
