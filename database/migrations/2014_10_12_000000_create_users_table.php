<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            //$table->increments('id');
            $table->uuid('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->boolean('validate_flag')->default(0);//untuk flag sudah verifikasi
            $table->boolean('register_flag')->default(0);
            $table->uuid('customer_id')->nullable();
            $table->string('api_token')->nullable();
            $table->string('code_verifikasi',6)->nullable();
            $table->rememberToken();
            $table->dateTime('first_login')->nullable();
            $table->string('avatar')->default('default.jpg');
            $table->timestamps();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
