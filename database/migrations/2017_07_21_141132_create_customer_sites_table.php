<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('customer_sites', function (Blueprint $table) {
          $table->increments('id');
          $table->timestamps();
          $table->integer('oracle_customer_id')->nullable();
          $table->integer('cust_acct_site_id')->nullable();
          $table->integer('site_use_id')->nullable();
          $table->String('site_use_code');
          $table->String('status');
          $table->String('bill_to_site_use_id')->nullable();
          $table->integer('payment_term_id')->nullable();
          $table->integer('price_list_id')->nullable();
          $table->integer('order_type_id')->nullable();
          $table->String('tax_code')->nullable();
          $table->text('address1');
          $table->string('state')->nullable();
          $table->string('district')->nullable();
          $table->string('city');
          $table->string('province')->nullable();
          $table->string('postalcode')->nullable();
          $table->string('Country')->default('ID');
          $table->integer('org_id')->nullable();
          $table->integer('langitude')->nullable();
          $table->integer('longitude')->nullable();
          $table->integer('warehouse')->nullable();
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
        Schema::dropIfExists('customer_sites');
    }
}
