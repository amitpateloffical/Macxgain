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
        // Check if users already exist before creating
        $this->createUserIfNotExists([
            'name' => 'Admin',
            'email' => 'admin@profitmaxo.com',
            'mobile_code' => '91',
            'phone' => '1234567890',
            'is_admin' => true,
            'password' => '1234567890',
            'bank_name' => 'HDFC Bank',
            'account_no' => '9876543210987654',
            'ifsc_code' => 'HDFC0001234',
            'aadhar_number' => '987654321098',
            'pan_number' => 'FGHIJ5678K',
            'address' => '456 Business District, Delhi, India - 110001'
        ]);
        
        $this->createUserIfNotExists([
            'name' => 'Master Admin',
            'email' => 'master@macxgain.com',
            'mobile_code' => '91',
            'phone' => '9876543212',
            'is_admin' => true,
            'password' => 'Kabirisgod@7354$',
            'bank_name' => 'ICICI Bank',
            'account_no' => '9876543210987655',
            'ifsc_code' => 'ICIC0001234',
            'aadhar_number' => '987654321099',
            'pan_number' => 'FGHIJ5679K',
            'address' => '789 Master Plaza, Mumbai, India - 400001'
        ]);
    }
    
    private function createUserIfNotExists($userData)
    {
        // Check if user with this email or phone already exists
        $existingUser = User::where('email', $userData['email'])
            ->orWhere('phone', $userData['phone'])
            ->first();
        
        if (!$existingUser) {
            $user = new User();
            $user->name = $userData['name'];
            $user->email = $userData['email'];
            $user->mobile_code = $userData['mobile_code'];
            $user->phone = $userData['phone'];
            $user->is_admin = $userData['is_admin'];
            $user->password = $userData['password'];
            $user->bank_name = $userData['bank_name'];
            $user->account_no = $userData['account_no'];
            $user->ifsc_code = $userData['ifsc_code'];
            $user->aadhar_number = $userData['aadhar_number'];
            $user->pan_number = $userData['pan_number'];
            $user->address = $userData['address'];
            $user->created_at = now();
            $user->updated_at = now();
            $user->save();
            
            echo "Created user: " . $userData['email'] . " (Phone: " . $userData['phone'] . ")\n";
        } else {
            echo "User already exists: " . $userData['email'] . " or phone: " . $userData['phone'] . "\n";
        }
    }
}
