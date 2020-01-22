<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QpCreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::create('qp_modifier_summary_v', function (Blueprint $table) {
            $table->integer('list_line_id');
            $table->timestamps();
            $table->integer('list_header_id');
            $table->string('list_line_type_code',30)->nullable();
            $table->string('automatic_flag',1)->nullable();
            $table->string('modifier_level_code',30)->nullable();
            $table->integer('list_price')->nullable();
            $table->string('list_price_uom_code')->nullable();
            $table->string('primary_uom_flag',1)->nullable();
            $table->integer('inventory_item_id')->nullable();
            $table->integer('organization_id')->nullable();
            $table->integer('operand')->nullable();
            $table->string('arithmetic_operator',30)->nullable();
            $table->string('override_flag',1)->nullable();
            $table->string('print_on_invoice_flag',1)->nullable();
            $table->DATE('start_date_active')->nullable();
            $table->DATE('end_date_active')->nullable();
            $table->string('incompatibility_grp_code',30)->nullable();
            $table->string('list_line_no',30)->nullable();
            $table->integer('product_precedence')->nullable();
            $table->integer('pricing_phase_id')->nullable();
            $table->integer('pricing_attribute_id')->nullable();
            $table->string('product_attribute_context',30)->nullable();
            $table->string('product_attr',30)->nullable();
            $table->string('product_attr_val',240)->nullable();
            $table->string('product_uom_code',3)->nullable();
            $table->string('comparison_operator_code',30)->nullable();
            $table->string('pricing_attribute_context',30)->nullable();
            $table->string('pricing_attr',30)->nullable();
            $table->string('pricing_attr_value_from',240)->nullable();
            $table->string('pricing_attr_value_to',240)->nullable();
            $table->string('pricing_attribute_datatype',30)->nullable();
            $table->string('product_attribute_datatype',30)->nullable();

          });

          Schema::create('qp_qualifiers_v', function (Blueprint $table) {
            $table->integer('qualifier_id');
            $table->timestamps();
            $table->string('excluder_flag',1)->nullable();
            $table->string('comparision_operator_code',30)->nullable();
            $table->string('qualifier_context',30)->nullable();
            $table->string('qualifier_attribute',30)->nullable();
            $table->integer('qualifier_grouping_no')->nullable();
            $table->string('qualifier_attr_value',240)->nullable();
            $table->integer('list_header_id');
            $table->integer('list_line_id');
            $table->date('start_date_active')->nullable();
            $table->date('end_date_Active')->nullable();
            $table->string('qualifier_datatype',10)->nullable();
            $table->integer('qualifier_precedence')->nullable();
          });

          Schema::create('QP_PRICING_ATTR_GET_V', function (Blueprint $table) {
            $table->integer('pricing_attribute_id');
            $table->timestamps();
            $table->integer('list_line_id');
            $table->string('excluder_flag',1)->nullable();
            $table->string('product_attribute_context',30)->nullable();
            $table->string('product_attribute',30)->nullable();
            $table->string('product_attr_value',240)->nullable();
            $table->string('product_uom_code',3)->nullable();
            $table->string('pricing_attribute_datatype',30)->nullable();
            $table->string('product_attribute_datatype',30)->nullable();
            $table->integer('list_header_id')->nullable();
            $table->string('list_line_no',30)->nullable();
            $table->string('list_line_type_code',30)->nullable();
            $table->string('arithmetic_operator',30)->nullable();
            $table->integer('operand')->nullable();
            $table->integer('benefit_limit')->nullable();
            $table->string('benefit_uom_code',3)->nullable();
            $table->string('automatic_flag',1)->nullable();
            $table->string('modifier_level_code',30)->nullable();
            $table->integer('pricing_phase_id')->nullable();
            $table->integer('benefit_price_list_line_id')->nullable();
            $table->integer('benefit_qty')->nullable();
            $table->string('override_flag',1)->nullable();
            $table->string('rltd_modifier_grp_type',30)->nullable();
            $table->integer('rltd_modifier_id')->nullable();
            $table->integer('rltd_modifier_grp_no')->nullable();
            $table->integer('parent_list_line_id')->nullable();
            $table->integer('to_rltd_modifier_id')->nullable();
          });

          Schema::create('QP_PRICING_ATTR_V', function (Blueprint $table) {
            $table->integer('pricing_attribute_id');
            $table->timestamps();
            $table->integer('list_line_id');
            $table->string('excluder_flag',1)->nullable();
            $table->string('product_attribute_context',30)->nullable();
            $table->string('product_attribute',30)->nullable();
            $table->string('product_attr_value_from',240)->nullable();
            $table->string('product_uom_code',3)->nullable();
            $table->string('pricing_attribute_context',30)->nullable();
            $table->string('pricing_attr',30)->nullable();
            $table->string('pricing_attr_value_from',240)->nullable();
            $table->string('pricing_attr_value_to',240)->nullable();
            $table->integer('attribute_grouping_no')->nullable();
            $table->string('list_line_type_code',240)->nullable();
            $table->string('arithmetic_operator',30)->nullable();
            $table->integer('operand')->nullable();
            $table->integer('estim_accrual_rate')->nullable();
            $table->string('comparison_operator_code',30)->nullable();
            $table->integer('list_header_id')->nullable();
            $table->integer('parent_list_line_id')->nullable();
            $table->integer('rltd_modifier_grp_no')->nullable();
            $table->string('rltd_modifier_grp_type',30)->nullable();
            $table->integer('rltd_modifier_id')->nullable();
            $table->integer('to_rltd_modifier_id')->nullable();
            $table->string('pricing_attribute_datatype',30)->nullable();
            $table->string('product_attribute_datatype',30)->nullable();
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('QP_PRICING_ATTR_V');
        Schema::dropIfExists('QP_PRICING_ATTR_GET_V');
        Schema::dropIfExists('qp_qualifiers_v');
        Schema::dropIfExists('qp_modifier_summary_v');
    }
}
