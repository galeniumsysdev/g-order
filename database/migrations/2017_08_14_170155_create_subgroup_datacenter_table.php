<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubgroupDatacenterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('subgroup_datacenters', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name')->unique();
          $table->string('display_name')->nullable();
          $table->boolean('enabled_flag')->default(true);
          $table->integer('group_id')->unsigned();
          $table->timestamps();

          $table->foreign('group_id')->references('id')->on('group_datacenters')
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
        Schema::drop('subgroup_datacenters');
    }
}
