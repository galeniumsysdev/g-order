<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('product_categories', function (Blueprint $table) {
          $table->char('product_id',36);
          $table->integer('flex_value_id');
          $table->string('enabled_flag',1)->default('Y');
          $table->timestamps();

          $table->foreign('product_id')->references('id')->on('products')
              ->onUpdate('cascade')->onDelete('cascade');

        /*  $table->foreign('flex_value_id')->references('flex_value_id')->on('product_flexfield')
                  ->onUpdate('cascade')->onDelete('cascade');*/

          $table->primary(['product_id', 'flex_value_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::drop('product_categories');
    }
}
