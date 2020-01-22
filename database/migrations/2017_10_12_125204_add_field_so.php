<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldSo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      /*Schema::table('files_cmo', function (Blueprint $table) {
        $table->boolean('approve')->nullable();
      });

      Schema::table('so_headers', function (Blueprint $table) {
        $table->decimal('disc_reg_percentage',3,2)->nullable();
      });*/
      Schema::table('so_lines', function (Blueprint $table) {
        $table->char('uom_primary',3)->nullable();
        $table->decimal('qty_request_primary',15,2)->nullable();
        $table->decimal('disc_product_percentage',5,2)->nullable();
        $table->decimal('disc_reg_amount',15,2)->nullable();
        $table->decimal('disc_reg_percentage',5,2)->nullable();
        $table->decimal('conversion_qty',15,2)->change();
        $table->decimal('qty_comfirm_primary',15,2)->nullable();        
        $table->renameColumn('discount', 'disc_product_amount');
      });

      Schema::create('so_shipping', function (Blueprint $table) {
            $table->increments('id');
            $table->string('deliveryno')->nullable();
            $table->integer('source_header_id');
            $table->integer('source_line_id');
            $table->integer('delivery_detail_id')->nullable();
            $table->char('product_id',36);
            $table->char('uom',3);
            $table->decimal('qty_request',15,2);
            $table->char('uom_primary',3)->nullable();
            $table->decimal('qty_request_primary',15,2)->nullable();
            $table->decimal('conversion_qty',15,2)->nullable();
            $table->decimal('qty_shipping',15,2)->nullable();
            $table->decimal('qty_accept',15,2)->nullable();
            $table->string('batchno')->nullable();
            $table->integer('split_source_id')->nullable();
            $table->DateTime('tgl_kirim')->nullable();
            $table->DateTime('tgl_terima')->nullable();
            $table->DateTime('tgl_terima_kurir')->nullable();
            $table->char('userid_kurir',36)->nullable();
            $table->timestamps();

            $table->foreign('source_header_id')->references('id')->on('so_headers')
                  ->onUpdate('cascade');
            $table->foreign('source_line_id')->references('id')->on('so_lines')
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
        Schema::drop('so_shipping');
    }
}
