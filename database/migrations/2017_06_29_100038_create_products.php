<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('products', function (Blueprint $table) {
          //$table->increments('id');
          $table->uuid('id');
          $table->timestamps();
          $table->String('imagePath')->nullable();
          $table->String('title');
          $table->Text('description')->nullable();
          $table->Text('description_en')->nullable();
          $table->String('satuan_primary');
          $table->String('satuan_secondary');
          $table->integer('price')->nullable();
          $table->integer('inventory_item_id');
          $table->String('itemcode');
          $table->String('Enabled_Flag')->default('Y');
          $table->primary('id');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
