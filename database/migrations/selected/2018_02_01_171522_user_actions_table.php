<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('user_actions', function (Blueprint $table) {
        $table->increments('id');
        $table->char('user_id',36)->nullable();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->string('action');  // created / updated / deleted
        $table->string('action_model')->nullable();
        $table->integer('action_id')->nullable();  // CRUD entry ID
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
        Schema::dropIfExists('user_actions');
    }
}
