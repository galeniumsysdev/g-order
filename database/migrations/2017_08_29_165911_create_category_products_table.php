<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('category_products', function (Blueprint $table) {
          $table->char('product_id',36);
          $table->string('flex_value',5);
          //$table->string('enabled_flag',1)->default('Y');
          $table->timestamps();

          $table->foreign('product_id')->references('id')->on('products')
              ->onUpdate('cascade')->onDelete('cascade');

          $table->foreign('flex_value')->references('flex_value')->on('categories')
                  ->onUpdate('cascade')->onDelete('cascade');
      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('category_products');
    }
}
