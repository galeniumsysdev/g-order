<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableCustomerSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       /*Schema::create('provinces', function (Blueprint $table) {
           $table->char('id',2);
           $table->string('name',255);
           $table->primary('id');
       });
       Schema::create('regencies', function (Blueprint $table) {
           $table->char('id',4);
           $table->integer('province_id');
           $table->string('name',255);
           $table->primary('id');
           $table->foreign('province_id')->references('id')->on('provinces');
       });
       Schema::create('districts', function (Blueprint $table) {
           $table->char('id',7);
           $table->integer('regency_id');
           $table->string('name',255);
           $table->primary('id');
           $table->foreign('regency_id')->references('id')->on('regencies');
       });
       Schema::create('villages', function (Blueprint $table) {
           $table->char('id'10);
           $table->integer('id');
           $table->integer('district_id');
           $table->string('name',255);
           $table->foreign('district_id')->references('id')->on('districts');
       });*/
       Schema::table('customer_sites', function (Blueprint $table) {
         $table->char('province_id',2)->nullable();
         $table->char('city_id',4)->nullable();
         $table->char('district_id',7)->nullable();
         $table->char('state_id',10)->nullable();

        /* $table->foreign('province_id')->references('id')->on('provinces');
         $table->foreign('city_id')->references('id')->on('regencies');
         $table->foreign('district_id')->references('id')->on('districts');
         $table->foreign('state_id')->references('id')->on('villages');*/
       });
       /*Schema::table('customers', function (Blueprint $table) {
         $table->Char('parent_dist',36)->nullable();
         $table->foreign('parent_dist')->references('id')->on('customers');
       }*/
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::dropIfExists('provinces');
         Schema::dropIfExists('regencies');
         Schema::dropIfExists('districts');
         Schema::dropIfExists('villages');
     }
}
