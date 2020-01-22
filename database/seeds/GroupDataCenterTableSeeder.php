<?php

use Illuminate\Database\Seeder;
use App\GroupDatacenter;

class GroupDataCenterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $group = [
        [
          'name'=>'NKA',
          'display_name' =>'NKA'
        ],
        [
          'name'=>'MTI',
          'display_name' =>'MTI'
        ],
        [
          'name'=>'GT',
          'display_name' =>'GT'
        ],
        [
          'name'=>'PM',
          'display_name' =>'PM'
        ],
        [
          'name'=>'DM',
          'display_name' =>'DM'
        ],
      ];
      foreach ($group as $key=>$value){
        GroupDatacenter::create($value);
      }
    }
}
