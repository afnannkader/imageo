<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'avatar' => 'default.png',
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'google2fa_status' => 0,
            'google2fa_secret' => encrypt('dummy_secret'),
        ]); 
    }
}
