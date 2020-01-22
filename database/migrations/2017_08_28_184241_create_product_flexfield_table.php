<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductFlexfieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('product_flexfield', function (Blueprint $table) {
          $table->integer('flex_value_set_id');
          $table->integer('flex_value_id');
          $table->string('flex_value')->unique();
          $table->string('description');
          $table->string('enabled_flag',1)->default('Y');
          $table->string('summary_flag',1)->default('N');
          $table->timestamps();

          $table->primary(['flex_value_set_id', 'flex_value_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::drop('product_flexfield');
    }
}
