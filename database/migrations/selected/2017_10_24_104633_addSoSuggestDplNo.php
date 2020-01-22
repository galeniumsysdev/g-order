<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoSuggestDplNo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('so_headers', function (Blueprint $table) {
          $table->dropColumn('disc_reg_percentage');
          $table->char('suggest_no',8)->nullable();
          $table->char('dpl_no',8)->nullable();
        });
        Schema::table('so_lines', function (Blueprint $table) {
          $table->decimal('percentage_gpl',5,2)->unsigned()->nullable();
          $table->decimal('percentage_distributor',5,2)->unsigned()->nullable();
          $table->integer('qty_bonus_dpl')->unsigned()->nullable();

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
