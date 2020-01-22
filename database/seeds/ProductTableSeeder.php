<?php

use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $product = new \App\Product([
        'title' => 'JOVIAL MUL VIT EMULSI PLATINUM',
        'price' => 38000,
        'satuan_primary' => 'BTL',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2890,
        'itemcode'=>'4110010020'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'JOVIAL PROBIOTIC GRAPE',
        'price' => 100000,
        'satuan_primary' => 'BTL',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2902,
        'itemcode'=>'4110020010'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'LAXACOD TABLET',
        'price' => 100000,
        'satuan_primary' => 'DUS',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2903,
        'itemcode'=>'4112010010'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'LAXAREC',
        'price' => 10000,
        'satuan_primary' => 'TUB',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2904,
        'itemcode'=>'4112040010'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'SIMVASCHOL TABLET',
        'price' => 100000,
        'satuan_primary' => 'DUS',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2905,
        'itemcode'=>'4119030010'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'TOPISEL LOTION',
        'price' => 89000,
        'satuan_primary' => 'BTL',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2906,
        'itemcode'=>'4120010010'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'TRICHOL KAPSUL',
        'price' => 397950,
        'satuan_primary' => 'DUS',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2891,
        'itemcode'=>'4120030010'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'CAL POW ACTIVE FRESH 100 GR',
        'price' => 14400,
        'satuan_primary' => 'BTL',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2892,
        'itemcode'=>'4203080010'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'CAL POW ACTIVE FRESH 220 GR',
        'price' => 27400,
        'satuan_primary' => 'BTL',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2893,
        'itemcode'=>'4203080020'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'CAL POW ACTIVE FRESH 60 GR',
        'price' =>10200 ,
        'satuan_primary' => 'BTL',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2894,
        'itemcode'=>'4203080040'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'JF ACNE PROTECT GS OILY 90 GR',
        'price' => 11900,
        'satuan_primary' => 'DUS',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2895,
        'itemcode'=>'4210010040'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'JF GEL BLEMISH CARE 10 GR',
        'price' => 22200,
        'satuan_primary' => 'TUB',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2896,
        'itemcode'=>'4210100020'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'JF TSS CLEANSING WET WIPES',
        'price' => 8200,
        'satuan_primary' => 'SCH',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2897,
        'itemcode'=>'4210120010'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'OILUM BW MOIST INDULGENC 175ML',
        'price' => 39000,
        'satuan_primary' => 'POU',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2898,
        'itemcode'=>'4215020030'
      ]);
      $product->save();

      $product = new \App\Product([
        'title' => 'OILUM BW MOIST INDULGENC 210ML',
        'price' => 51000,
        'satuan_primary' => 'BTL',
        'satuan_secondary'=> 'Box',
        'inventory_item_id' =>2899,
        'itemcode'=>'4215020040'
      ]);
      $product->save();


    }
}
