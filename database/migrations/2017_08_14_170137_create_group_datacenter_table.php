<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupDatacenterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('group_datacenters', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name')->unique();
          $table->string('display_name')->nullable();
          $table->boolean('enabled_flag')->default(true);
          $table->integer('pricelist_id')->nullable();
          $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('group_datacenters');
    }
}
