<?php
/**
* created by WK Productions
*/
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMrOutletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mr_outlet', function (Blueprint $table) {
        $table->uuid('mr_id');
        $table->uuid('outlet_id');
        $table->boolean('approval')->nullable();
        $table->text('keterangan')->nullable();
        $table->dateTime('tgl_approve')->nullable();

        $table->foreign('mr_id')->references('id')->on('customers')
            ->onUpdate('cascade')->onDelete('cascade');
        $table->foreign('outlet_id')->references('id')->on('customers')
            ->onUpdate('cascade')->onDelete('cascade');

        $table->primary(['mr_id', 'outlet_id']);
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
        Schema::dropIfExists('mr_outlet');
    }
}
