<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::firstOrCreate(
            ['email' => 'partha1717@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('test1234')
            ]
        );
    }
}
