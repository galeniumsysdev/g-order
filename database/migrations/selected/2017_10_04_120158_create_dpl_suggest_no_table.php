<?php
/**
* created by WK Productions
*/
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDplSuggestNoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dpl_suggest_no', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('mr_id');
            $table->uuid('outlet_id');
            $table->uuid('distributor_id');
            $table->char('suggest_no',8);
            $table->string('notrx',50)->nullable();
            $table->boolean('fill_in')->default(1);
            $table->uuid('approved_by')->nullable();
            $table->uuid('next_approver')->nullable();
            $table->boolean('active')->default(1);

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
        Schema::dropIfExists('dpl_suggest_no');
    }
}
