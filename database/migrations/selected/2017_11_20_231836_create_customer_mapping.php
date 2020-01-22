<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('distributor_groupdc', function (Blueprint $table) {
          $table->char('distributor_id',36);
          $table->integer('group_id')->unsigned();
          $table->timestamps();


          $table->foreign('distributor_id')->references('id')->on('customers');
          $table->foreign('group_id')->references('id')->on('group_datacenters');
      });

      Schema::create('distributor_regency', function (Blueprint $table) {
          $table->char('distributor_id',36);
          $table->char('regency_id',4);
          $table->timestamps();


          $table->foreign('distributor_id')->references('id')->on('customers');
        /*  $table->foreign('regency_id')->references('id')->on('regencies');*/
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('distributor_regency');
      Schema::dropIfExists('distributor_groupdc');
    }
}
