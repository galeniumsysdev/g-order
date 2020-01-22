<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('customer_contacts', function (Blueprint $table) {
          $table->increments('id');
          $table->timestamps();
          $table->integer('oracle_customer_id')->nullable();
          $table->integer('account_number')->nullable();
          $table->String('contact_name')->nullable();
          $table->String('contact_type');
          $table->String('contact');
          $table->uuid('customer_id');
          $table->foreign('customer_id')->references('id')->on('customers')
              ->onUpdate('cascade')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_contacts');
    }
}
