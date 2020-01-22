<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('customers', function (Blueprint $table) {
          //$table->increments('id');
          $table->uuid('id');
          $table->timestamps();
          $table->String('customer_name');
          $table->String('customer_number')->nullable();
          $table->integer('oracle_customer_id')->nullable();
          $table->String('Status');
          $table->String('customer_category_code')->nullable();
          $table->String('customer_class_code')->nullable();
          $table->integer('primary_salesrep_id')->nullable();
          $table->String('tax_reference')->nullable();
          $table->String('tax_code')->nullable();
          $table->integer('price_list_id')->nullable();
          $table->integer('order_type_id')->nullable();
          $table->String('customer_name_phonetic')->nullable();
          $table->String('pharma_flag')->nullable();
          $table->String('psc_flag')->nullable();
          $table->integer('outlet_type_id')->nullable();
          $table->integer('subgroup_dc_id')->nullable();
          $table->decimal('longitude',11,6)->nullable();
          $table->decimal('langitude',11,6)->nullable();
          $table->uuid('id_approval_psc')->nullable();
          $table->uuid('id_approval_pharma')->nullable();
          $table->dateTime('date_approval_pharma')->nullable();
          $table->dateTime('date_approval_psc')->nullable();
          $table->text('keterangan')->nullable();

          $table->primary('id');
          $table->foreign('id_approval_psc')->references('id')->on('users')
              ->onUpdate('cascade')->onDelete('cascade');
          $table->foreign('id_approval_pharma')->references('id')->on('users')
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
        Schema::dropIfExists('customers');
    }
}
