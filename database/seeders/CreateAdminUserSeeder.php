<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserAccount;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
        	'name' => 'Admin', 
            'status' => 1,
            'account_id'=>1,
            'user_type'=>1,
        	'email' => 'admin@gmail.com',
        	'password' => bcrypt('admin')
        ]);
 

        $roleuser = UserAccount::create([
            'name' => $user->name,
            'email' => $user->email 
        ]);
    }
}
