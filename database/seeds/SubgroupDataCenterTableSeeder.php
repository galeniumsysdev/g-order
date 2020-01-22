<?php

use Illuminate\Database\Seeder;
use App\SubgroupDatacenter;

class SubgroupDataCenterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
     public function run()
     {
       $subgroup = [
         [
           'name'=>'Hypermarket NKA',
           'display_name' =>'Hypermarket NKA'
         ],
         [
           'name'=>'Supermarket NKA',
           'display_name' =>'Supermarket NKA'
         ],
         [
           'name'=>'Minimarket NKA',
           'display_name' =>'Minimarket NKA'
         ],
         [
           'name'=>'Others NKA',
           'display_name' =>'Others NKA'
         ],
         [
           'name'=>'Supermarket MTI',
           'display_name' =>'Supermarket MTI'
         ],
         [
           'name'=>'Minimarket MTI',
           'display_name' =>'Minimarket MTI'
         ],
         [
           'name'=>'Wholesaler',
           'display_name' =>'Wholesaler'
         ],
         [
           'name'=>'Retailer',
           'display_name' =>'Retailer'
         ],
         [
           'name'=>'Toko Kosmetik',
           'display_name' =>'Toko Kosmetik'
         ],
         [
           'name'=>'Direct Selling',
           'display_name' =>'Direct Selling'
         ],
         [
           'name'=>'Baby Shop',
           'display_name' =>'Baby Shop'
         ],
         [
           'name'=>'Others GT',
           'display_name' =>'Others GT'
         ],
         [
           'name'=>'Apotik Chain',
           'display_name' =>'Apotik Chain'
         ],
         [
           'name'=>'PBF',
           'display_name' =>'PBF'
         ],
         [
           'name'=>'Toko Obat',
           'display_name' =>'Toko Obat'
         ],
         [
           'name'=>'Apt. Non Chain',
           'display_name' =>'Apt. Non Chain'
         ],
         [
           'name'=>'Others Pharma',
           'display_name' =>'Others Pharma'
         ],
         [
           'name'=>'Digital Marketing',
           'display_name' =>'Digital Marketing'
         ],
       ];
       foreach ($subgroup as $key=>$value){
         SubgroupDatacenter::create($value);
       }
     }
}
