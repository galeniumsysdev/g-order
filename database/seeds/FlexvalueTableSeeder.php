<?php

use Illuminate\Database\Seeder;
use App\Flexvalue;

class FlexvalueTableSeeder extends Seeder
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
          'master'=>'status_po',
          'id' =>'-1',
          'name'=>'Cancel'

        ],
        [
          'master'=>'status_po',
          'id' =>'0',
          'name'=>'Belum diproses'

        ],
        [
          'master'=>'status_po',
          'id' =>'1',
          'name'=>'Telah dikirim'
        ],
        [
          'master'=>'status_po',
          'id' =>'2',
          'name'=>'Sudah diterima'
        ],
      ];
      foreach ($group as $key=>$value){
        Flexvalue::create($value);
      }
    }
}
