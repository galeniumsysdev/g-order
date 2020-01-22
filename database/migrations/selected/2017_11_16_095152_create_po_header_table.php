<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('po_draft_headers', function (Blueprint $table) {
          $table->increments('id');
          $table->timestamps();
          $table->char('customer_id',36);
          $table->char('distributor_id',36)->nullable();
          $table->string('currency',50)->nullable();
          $table->char('psc_flag',1)->nullable();
          $table->char('pharma_flag',1)->nullable();
          $table->char('export_flag',1)->nullable();
          $table->char('tollin_flag',1)->nullable();
          $table->decimal('subtotal',15,2)->nullable();
          $table->decimal('discount',15,2)->nullable();
          $table->decimal('Tax',15,2)->nullable();
          $table->decimal('amount',15,2)->nullable();

          $table->foreign('distributor_id')->references('id')->on('customers')
              ->onUpdate('cascade');
          $table->foreign('customer_id')->references('id')->on('customers')
                  ->onUpdate('cascade');
      });

      Schema::create('po_draft_lines', function (Blueprint $table) {
          $table->increments('id');
          $table->timestamps();
          $table->integer('po_header_id')->unsigned();
          $table->char('product_id',36);
          $table->String('uom',3);
          $table->decimal('qty_request',15,2);
          $table->decimal('qty_request_primary',15,2);
          $table->decimal('conversion_qty',15,2);
          $table->String('primary_uom',3);
          $table->integer('inventory_item_id')->nullable();
          $table->decimal('list_price',15,2);
          $table->decimal('unit_price',15,2);
          $table->decimal('amount',15,2);
          $table->decimal('discount',15,2)->nullable();

          $table->foreign('po_header_id')->references('id')->on('po_draft_headers')
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
        Schema::dropIfExists('po_draft_headers');
        Schema::dropIfExists('po_draft_lines');
    }
}
