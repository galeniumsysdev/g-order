<?php

use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('customers')->delete();
       //insert some dummy records
       DB::table('customers')->insert(array(
       //User::create(array(
           array('customer_name'=>'YASA MITRA PERDANA, PT','oracle_customer_id'=>2040,'status'=>'A','customer_class_code'=>'Reguler','tax_reference'=>'02.098.869.7-013.000','pharma_flag'=>true,'psc_flag'=>true),
           array('customer_name'=>'ANTARMITRA SEMBADA , PT','oracle_customer_id'=>3046,'status'=>'A','customer_class_code'=>'Distributor','tax_reference'=>'01.345.766.8-062.000','pharma_flag'=>true,'psc_flag'=>true),
           array('customer_name'=>'ANTARMITRA SEMBADA (JAKARTA 1), PT','oracle_customer_id'=>3047,'status'=>'A','customer_class_code'=>'Distributor','tax_reference'=>'01.345.766.8-062.000','pharma_flag'=>true,'psc_flag'=>true),
           array('customer_name'=>'UNITED DICO CITAS , PT','oracle_customer_id'=>3048,'status'=>'A','customer_class_code'=>'Distributor','tax_reference'=>'01.301.246.3-073.000','pharma_flag'=>true,'psc_flag'=>false),
           //array('customer_name'=>'PENTA VALENT (JAKARTA - I) , PT','oracle_customer_id'=>,'status'=>'A','customer_clas_code'=>'','tax_reference'=>'','pharma_flag'=>,'psc_flag'=>'','outlet_type_id'=>,'subgroup_dc_id'=>),
           array('customer_name'=>'WICAKSANA OVERSEAS INTERNATIONAL , PT','oracle_customer_id'=>3050,'status'=>'A','customer_class_code'=>'Distributor','tax_reference'=>'01.305.436.6-056.000','pharma_flag'=>false,'psc_flag'=>true),
           //array('customer_name'=>'WICAKSANA OVERSEAS INTERNATIONAL (MEDAN) , PT','oracle_customer_id'=>,'status'=>'A','customer_clas_code'=>'','tax_reference'=>'','pharma_flag'=>,'psc_flag'=>'','outlet_type_id'=>,'subgroup_dc_id'=>),
           //array('customer_name'=>'MANUNGGAL JAYA PERKASA, CV','oracle_customer_id'=>,'status'=>'A','customer_clas_code'=>'','tax_reference'=>'','pharma_flag'=>,'psc_flag'=>'','outlet_type_id'=>,'subgroup_dc_id'=>),
           //array('customer_name'=>'PANAY FARMALAB, PT','oracle_customer_id'=>,'status'=>'A','customer_clas_code'=>'','tax_reference'=>'','pharma_flag'=>,'psc_flag'=>'','outlet_type_id'=>,'subgroup_dc_id'=>),
           //array('customer_name'=>'DUTA INTIDAYA TBK, PT','oracle_customer_id'=>,'status'=>'A','customer_clas_code'=>'','tax_reference'=>'','pharma_flag'=>,'psc_flag'=>'','outlet_type_id'=>,'subgroup_dc_id'=>),
           array('customer_name'=>'WATSONS PONDOK INDAH MALL','oracle_customer_id'=>3056,'status'=>'A','customer_class_code'=>'MTI','tax_reference'=>'02.460.408.4-063.000','pharma_flag'=>false,'psc_flag'=>true,'outlet_type_id'=>2),
           array('customer_name'=>'SUPRA BOGA LESTARI TBK, PT','oracle_customer_id'=>3057,'status'=>'A','customer_class_code'=>'MTI','tax_reference'=>'01.821.420.5-054.000','pharma_flag'=>false,'psc_flag'=>true,'outlet_type_id'=>2),
           array('customer_name'=>'THE FOODHALL','oracle_customer_id'=>3058,'status'=>'A','customer_class_code'=>'MTI','tax_reference'=>'03.303.719.3-022.000','pharma_flag'=>false,'psc_flag'=>true,'outlet_type_id'=>2),
           array('customer_name'=>'TOTAL BUAH','oracle_customer_id'=>3059,'status'=>'A','customer_class_code'=>'MTI','tax_reference'=>'24.023.436.9-003.000','pharma_flag'=>false,'psc_flag'=>true,'outlet_type_id'=>2),
           array('customer_name'=>'FARMERS MARKET KARAWACI','oracle_customer_id'=>3065,'status'=>'A','customer_class_code'=>'MTI','tax_reference'=>'01.821.420.5-054.000','pharma_flag'=>false,'psc_flag'=>true,'outlet_type_id'=>2),
        ));
    }
}
