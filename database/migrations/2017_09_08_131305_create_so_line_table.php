<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSoLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('so_lines', function (Blueprint $table) {
          $table->increments('line_id');
          $table->timestamps();
          $table->integer('header_id')->unsigned();
          $table->char('product_id',36);
          $table->char('uom',3);
          $table->decimal('qty_request',15,2);
          $table->decimal('qty_confirm',15,2)->nullable();
          $table->decimal('qty_shipping',15,2)->nullable();
          $table->decimal('qty_accept',15,2)->nullable();
          $table->decimal('list_price',15,2);
          $table->decimal('unit_price',15,2);
          $table->decimal('amount',15,2);
          $table->decimal('tax_amount',15,2)->nullable();
          $table->string('tax_type',100)->nullable();
          $table->integer('discount')->nullable();
          $table->integer('bonus')->nullable();
          $table->integer('oracle_line_id')->nullable();
          $table->integer('conversion_qty')->nullable();
          $table->integer('inventory_item_id')->nullable();

          //$table->string('enabled_flag',1)->default('Y');

          $table->foreign('header_id')->references('id')->on('so_headers')
              ->onUpdate('cascade');
          $table->foreign('product_id')->references('id')->on('products')
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
        Schema::drop('so_lines');
    }
}
