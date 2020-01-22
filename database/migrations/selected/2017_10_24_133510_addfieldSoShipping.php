<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddfieldSoShipping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('so_shipping', function (Blueprint $table) {
        $table->integer('header_id')->unsigned();
        $table->integer('line_id')->unsigned();
        $table->foreign('line_id')->references('line_id')->on('so_lines');
        $table->foreign('header_id')->references('id')->on('so_headers');        
      });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
