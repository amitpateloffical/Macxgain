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
        $user = new User();
        $user->name = 'kamlesh patel';
        $user->email = 'admin@test.com';
        $user->mobile_code = '91';
        $user->phone = '9876543210';
        $user->is_admin = true;
        $user->password='password';
        $user->created_at= now();
        $user->updated_at= now();
        $user->save();
    }
}
