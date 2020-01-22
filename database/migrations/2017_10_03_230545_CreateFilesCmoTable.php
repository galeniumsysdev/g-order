<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesCmoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('files_cmo', function (Blueprint $table) {
          $table->increments('id');
          $table->String('distributor_id',36);
          $table->String('period',6);
          $table->integer('bulan');
          $table->integer('tahun');
          $table->integer('version');
          $table->text('file_pdf');
          $table->text('file_excel');
          $table->DateTime('first_download')->nullable();
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
         Schema::dropIfExists('files_cmo');
    }
}
