<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QpListHeaders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('qp_list_headers', function (Blueprint $table) {
          $table->integer('list_header_id');
          $table->String('name',240);
          $table->text('description')->nullable();
          $table->String('version_no',30)->nullable();
          $table->String('currency_code',30)->nullable();
          $table->date('start_date_active')->nullable();
          $table->date('end_date_active')->nullable();
          $table->String('automatic_flag',1)->nullable();
          $table->String('list_type_code',30)->nullable();
          $table->integer('terms_id')->nullable();
          $table->integer('rounding_factor')->nullable();
          $table->String('discount_lines_flag',1)->nullable();
          $table->String('active_flag',1)->nullable();
          $table->integer('orig_org_id')->nullable();          
          $table->String('global_flag',1)->nullable();
          $table->timestamps();

          $table->primary('list_header_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qp_list_headers');
    }
}
