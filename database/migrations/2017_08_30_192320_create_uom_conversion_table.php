<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUomConversionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('uom_conversions', function (Blueprint $table) {
          $table->char('product_id',36);
          $table->string('uom_code',3);
          $table->string('uom_class');
          $table->integer('rate',3);
          $table->string('base_uom',3);
          $table->integer('width')->nullable();
          $table->integer('height')->nullable();
          $table->string('dimension_uom',3)->nullable();

          //$table->string('enabled_flag',1)->default('Y');
          $table->timestamps();

          $table->foreign('product_id')->references('id')->on('products')
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
        Schema::drop('uom_conversions');
    }
}
