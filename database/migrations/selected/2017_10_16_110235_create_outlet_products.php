<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutletProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outlet_products', function (Blueprint $table) {
          $table->uuid('id');
          //$table->increments('itemcode');
          $table->String('title');
          $table->String('unit');
          $table->integer('price')->nullable();
          $table->String('enabled_flag',1)->default('Y');
          $table->timestamps();
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
        Schema::dropIfExists('outlet_product');
    }
}
