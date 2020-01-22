<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutletDistributorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('outlet_distributor', function (Blueprint $table) {
          $table->uuid('outlet_id');
          $table->uuid('distributor_id');
          $table->boolean('approval')->nullable();
          $table->text('keterangan')->nullable();
          $table->dateTime('tgl_approve')->nullable();

          $table->foreign('outlet_id')->references('id')->on('customers')
              ->onUpdate('cascade')->onDelete('cascade');
          $table->foreign('distributor_id')->references('id')->on('customers')
              ->onUpdate('cascade')->onDelete('cascade');

          $table->primary(['outlet_id', 'distributor_id']);
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
        Schema::drop('outlet_distributor');
    }
}
