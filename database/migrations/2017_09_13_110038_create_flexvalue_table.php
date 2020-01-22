<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlexvalueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('flexvalue', function (Blueprint $table) {

          $table->string('master',50);
          $table->integer('id');
          $table->string('name');
          $table->char('enabled_flag',1)->default('Y');
          $table->timestamps();
          $table->unique(['master', 'id']);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::drop('flexvalue');
    }
}
