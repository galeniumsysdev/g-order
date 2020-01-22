<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSoHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('so_headers', function (Blueprint $table) {
          $table->increments('id');
          $table->timestamps();
          $table->char('distributor_id',36);
          $table->char('customer_id',36);
          $table->integer('cust_ship_to')->nullable();
          $table->integer('cust_bill_to')->nullable();
          $table->string('customer_po',50);
          $table->string('file_po',150)->nullable();
          $table->boolean('approve')->nullable();
          $table->DateTime('tgl_order',3);
          $table->string('currency',50);
          $table->integer('payment_term_id')->nullable();
          $table->integer('price_list_id')->nullable();
          $table->integer('order_type_id')->nullable();
          $table->integer('oracle_ship_to')->nullable();
          $table->integer('oracle_bill_to')->nullable();
          $table->integer('oracle_header_id')->nullable();
          $table->integer('oracle_customer_id')->nullable();
          $table->string('notrx');
          $table->integer('status')->default(0);
          $table->DateTime('tgl_approve')->nullable();
          $table->DateTime('tgl_kirim')->nullable();
          $table->DateTime('tgl_terima')->nullable();
          $table->integer('org_id')->nullable();
          $table->integer('warehouse')->nullable();
          $table->string('interface_flag',1)->default('N');
          $table->string('status_oracle',50)->nullable();
          $table->text('alasan_tolak')->nullable();


          //$table->string('enabled_flag',1)->default('Y');

          $table->foreign('distributor_id')->references('id')->on('customers')
              ->onUpdate('cascade');
          $table->foreign('customer_id')->references('id')->on('customers')
                  ->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('so_headers');
    }
}
