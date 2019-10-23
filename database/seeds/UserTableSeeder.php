<?php

use Illuminate\Database\Seeder;

use App\User;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $user = User::where('email','ugwukelvintochukwu@gmail.com')->first();

         if(!$user){

             User::create([

                 'name' => 'Tochukwu Ugwu',
                 'email' => 'ugwukelvintochukwu@gmail.com',
                 'role' => 'admin',
                 'password' => Hash::make('11111111'),
             ]);
         }
    }
}
