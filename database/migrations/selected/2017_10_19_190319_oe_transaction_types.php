<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OeTransactionTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('oe_transaction_types', function (Blueprint $table) {
          $table->integer('transaction_type_id');
          $table->String('name',240);
          $table->text('description')->nullable();
          $table->date('start_date_active')->nullable();
          $table->date('end_date_active')->nullable();
          $table->String('currency_code',30)->nullable();
          $table->integer('price_list_id')->nullable();
          $table->integer('warehouse_id')->nullable();
          $table->integer('org_id')->nullable();
          $table->timestamps();

          $table->primary('transaction_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oe_transaction_types');
    }
}
