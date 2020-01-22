<?php

use Illuminate\Database\Seeder;
use App\User;
use Webpatser\Uuid\Uuid;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('users')->delete();
       //insert some dummy records
       DB::table('users')->insert(array(
       //User::create(array(
           array('id'=>Uuid::generate(),'name'=>'IT Galenium','email'=>'stevanus.andre@solinda.co.id','validate_flag'=>true, 'register_flag'=>true,'password'=>bcrypt('123456')),
           array('id'=>Uuid::generate(),'name'=>'Yasa Mitra Perdana, PT','email'=>'mexar.shanty@gmail.com','validate_flag'=>true, 'register_flag'=>true,'password'=>bcrypt('123456')),
           array('id'=>Uuid::generate(),'name'=>'Galenium Pharmashia Laboratories, PT','email'=>'shanty1.dewi@solinda.co.id','validate_flag'=>true, 'register_flag'=>true,'password'=>bcrypt('123456')),
           array('id'=>Uuid::generate(),'name'=>'Marketing PSC','email'=>'shanty2.dewi@solinda.co.id','validate_flag'=>true, 'register_flag'=>true,'password'=>bcrypt('123456')),
           array('id'=>Uuid::generate(),'name'=>'Marketing Pharma','email'=>'shanty3.dewi@solinda.co.id','validate_flag'=>true, 'register_flag'=>true,'password'=>bcrypt('123456')),
           array('id'=>Uuid::generate(),'name'=>'ANTARMITRA SEMBADA JAKARTA1, PT','email'=>'shanty.dewi@solinda.co.id','validate_flag'=>true, 'register_flag'=>true,'password'=>bcrypt('123456')),
        ));
    }
}
