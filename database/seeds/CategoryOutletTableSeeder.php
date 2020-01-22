<?php

use Illuminate\Database\Seeder;
use App\CategoryOutlet;

class CategoryOutletTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $kategori = [
        [
          'name'=>'Apotik',
          'enable_flag' =>'Y'
        ],
        [
          'name'=>'Mini Market',
          'enable_flag' =>'Y'
        ],
        [
          'name'=>'Retail',
          'enable_flag' =>'Y'
        ],
        [
          'name'=>'Toko Obat',
          'enable_flag' =>'Y'
        ],
        [
          'name'=>'PBF',
          'enable_flag' =>'Y'
        ],
        [
          'name'=>'Rumah Sakit/Klinik',
          'enable_flag' =>'Y'
        ],
        [
          'name'=>'e-Commerce',
          'enable_flag' =>'Y'
        ],
        [
          'name'=>'others',
          'enable_flag' =>'Y'
        ],
      ];
      foreach ($kategori as $key=>$value){
        CategoryOutlet::create($value);
      }
    }
}
