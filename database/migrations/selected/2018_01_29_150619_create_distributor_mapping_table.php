<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('distributor_mappings', function (Blueprint $table) {
          $table->char('distributor_id',36);
          $table->string('data',100);
          $table->integer('data_id')->unsigned();
          $table->timestamps();
          $table->foreign('distributor_id')->references('id')->on('customers');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::dropIfExists('distributor_mappings');
    }
}
