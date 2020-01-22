<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => 'prevent-back-history'],function(){
  Route::get('/',  [
    'uses' => 'ProductController@getIndex',
    'as' => 'product.index'
  ]);
});
Route::get('/getPrice',[
    'uses' => 'ProductController@getPrice'
    ,'as' => 'product.getPrice'
  ]);
Route::post('/ajax/changeOrderUom', 'OrderController@changeOrderUom');

Route::get('/ajax/getCity', 'UserController@getListCity');
Route::get('/ajax/getDistrict', 'UserController@getListDistrict');
Route::get('/ajax/getSubdistrict', 'UserController@getListSubDistrict');
Route::get('/ajax/getCatOutlet', 'UserController@getkategoriOutlet');
Route::get('/ajax/typeaheadProvince', 'ProfileController@getListProvince');
Route::get('/ajax/typeaheadCity/{propid?}', 'ProfileController@getListCity');
Route::get('/ajax/typeaheadOutlet', 'OutletProductController@getListOutlet');
Route::get('/ajax-subcat',function () {
    $cat_id = Input::get('cat_id');
    $subcategories = App\SubgroupDatacenter::where('group_id','=',$cat_id)->get();
    return Response::json($subcategories);
});


Route::get('/privacy_policy','FilesController@privacy');
Route::get('/privacy','FilesController@privacy_policy');

Route::get('/ajax/shiptoaddr', 'CustomerController@ajaxSearchAlamat');
Route::post('/ajax/addMappingType', 'UserController@ajaxAddMappingType')->name('ajax.addmapping.type');
Route::get('/ajax/getMappingType/{id?}', 'UserController@ajaxGetMappingType')->name('ajax.mapping.getdata');
Route::get('/ajax/getPriceList', 'PriceController@ajaxPriceList')->name('ajax.price.getdata');
Route::get('/ajax/orgArea/{id?}', 'OrgStructureController@ajaxOrgArea')->name('ajax.area.orgpharma');
Route::post('/ajax/addAreaDPL','OrgStructureController@ajaxaddAreaDPL');
Route::get('/ajax/getMappngInclude/{id?}', 'UserController@ajaxGetMappingInclude')->name('ajax.mapping.include');

Route::get('detail/{id}', [
  'uses' => 'ProductController@show'
  ,'as' => 'product.detail'
]);
Route::match(['get', 'post'],'search_product', [
  'uses' => 'ProductController@search'
  ,'as' => 'product.search'
]);




Route::group(['middleware'=>['auth','prevent-back-history']],function(){
  Route::get('product/category/{id}', [
    'uses' => 'ProductController@category'
    ,'as' => 'product.category'
  ]);
  Route::get('profile', 'ProfileController@profile')->name('profile.index');
  Route::post('profile/update', 'ProfileController@updateprofile')->name('profile.updateprofile');
  Route::post('profile', 'ProfileController@update_avatar')->name('profile.update');
  Route::post('add-address', 'ProfileController@addaddress')->name('profile.address');
  Route::post('add-contact', 'ProfileController@addcontact')->name('profile.contact');
  Route::get('add_address', 'ProfileController@addaddressview');
  Route::match(['patch','get'],'/edit_address/{id}', 'ProfileController@editaddress')->name('profile.edit_address');;
  //Route::get('/edit_address/{id}', 'ProfileController@editaddress')->name('profile.edit_address');;
  Route::get('/add_contact', function () {
      return view('auth.profile.add_contact');
  });
  Route::delete('remove-address/{id}', 'ProfileController@removeaddress')->name('profile.removeaddress');
  Route::delete('remove-contact/{id}', 'ProfileController@removecontact')->name('profile.removecontact');
  Route::get('/home', 'HomeController@index')->name('home');
  Route::post('/home', 'HomeController@search')->name('notif.search');
});

Route::group(['middleware'=>['permission:Create PO','prevent-back-history']],function(){
Route::get('/product/buy', 'ProductController@displayProduct')->name('product.buy');
Route::get('/shopping-cart',[
    'uses' => 'ProductController@getCart'
    ,'as' => 'product.shoppingCart'
  ]);
  Route::match(['get','post'],'/checkout/{distributorid?}',[
      'uses' => 'ProductController@checkOut'
      ,'as' => 'product.checkOut'
    ]);

 Route::post('/add-to-cart/{id}',[
     'uses' => 'ProductController@getAddToCart'
     ,'as' => 'product.addToCart'
   ]);

   Route::post('/edit-item-cart/{id}',[
       'uses' => 'ProductController@getEditToCart'
       ,'as' => 'product.editToCart'
     ]);

    Route::get('/removeItem/{id}',[
      'uses' => 'ProductController@getRemoveItem'
      ,'as' => 'product.removeItem'
    ]);

    Route::post('/postcheckout',[
      'uses' => 'ProductController@postOrder'
      ,'as' => 'product.postOrder'
    ]);
  });



Route::group(['middleware' => ['web']],function(){
  Auth::routes();
  Route::get('/contact','Auth\RegisterController@contactview')->name('contact');
  //Route::get('/home', 'HomeController@index');
});


Route::group(['middleware' => ['web']], function () {
  Route::post('language-chooser','LanguageController@changeLanguage');
  Route::post('/language/',array(
    'before'=>'csrf',
    'as'=>'language-chooser',
    'uses'=>'LanguageController@changeLanguage',
    )
  );

});

Route::get('/users/confirmation/{token}','Auth\RegisterController@confirmation')->name('confirmation');
Route::get('/users/verification/{token}','Auth\RegisterController@verification_email')->name('verification');

Route::post('/register2','Auth\RegisterController@register2')->name('register2');

Route::group(['middleware' => ['role:IT Galenium','prevent-back-history']], function () {
  Route::resource('role',  'RoleController');
  Route::get('/rolepdf', [
    'uses' => 'RoleController@makePDF'
    ,'as' => 'role.rptpdf'
  ]);
  Route::resource('permission',  'PermissionController');
  Route::get('/admin',  [
    'uses' => 'HomeController@indexAdmin',
    'middleware' => ['role:IT Galenium'],
    'as' => 'admin.index'
  ]);
  Route::get('/admin/banner','BannerController@listBanner')->name('admin.banner');
  Route::get('/banner/publish/{publish}/{id}','BannerController@publish')->name('banner.publish');
  Route::delete('/banner/destroy/{id}','BannerController@destroy')->name('banner.destroy');
  Route::get('/banner/create','BannerController@create')->name('banner.create');
  Route::post('/banner/create','BannerController@store')->name('banner.store');
  Route::get('/banner/{id}/edit','BannerController@edit')->name('banner.edit');
  Route::patch('/banner/{id}','BannerController@update')->name('banner.update');

  Route::get('/showProduct', [
  'uses' => 'ProductController@index'
  ,'as' => 'product.show'
  ]);


  Route::get('/product/listparetoProduct','ProductController@listParetoProduct')->name('product.pareto');
  Route::get('/product/getParetoProduct','ProductController@getAjaxProduct')->name('product.getAjaxProduct');
  Route::post('/product/updatePareto','ProductController@updatePareto')->name('product.updatePareto');
  Route::delete('/product/updatePareto/{id}','ProductController@destroyPareto')->name('product.destroyPareto');
  Route::get('/product/pricelist','PriceController@index')->name('product.priceindex');
  Route::get('/product/searchdiskon','PriceController@searchDiskon')->name('product.searchDiskon');
  Route::post('/product/diskonIndex','PriceController@diskonIndex')->name('product.diskonIndex');

  Route::get('masterProduct/{id}', [
  'uses' => 'ProductController@master'
  ,'as' => 'product.master'
  ]);

  Route::post('/updateProduct/{id}', [
    'uses' => 'ProductController@update'
    ,'as' => 'product.update'
  ]);
  Route::get('/users/oracle','UserController@oracleIndex')->name('useroracle.index');
  Route::get('/users/oracle/{id}','UserController@oracleShow')->name('useroracle.show');
  Route::patch('/users/oracle/{id}','UserController@oracleUpdate')->name('useroracle.update');
  Route::get('/users/cabang/{parent_id}','UserController@cabangCreate')->name('usercabang.create');
  Route::post('/users/cabang/{parent_id}','UserController@cabangStore')->name('usercabang.store');
  Route::get('/users/cabang/edit/{id}','UserController@cabangEdit')->name('usercabang.edit');
  Route::patch('/users/cabang/edit/{id}','UserController@cabangUpdate')->name('usercabang.update');
  Route::get('/customer/yasaNonOracle','UserController@CustYasaNonOracle')->name('customer.yasaNonOracle');
  Route::patch('/customer/updateyasaNonOracle/{id}','UserController@mergeCustomer')->name('customer.mergeCustomer');
  Route::get('/distributor/mappingOutlet/{id?}','UserController@MappingOutletDistributor')->name('customer.mappingOutlet');
  Route::patch('/distributor/remappingOutlet/{id?}','UserController@remappingOutlet')->name('customer.remappingOutlet');

  Route::resource('CategoryOutlet',  'Cat_OutletController');
  Route::resource('CategoryProduct',  'CategoryProductController');
  Route::resource('users','UserController');
  Route::resource('GroupDataCenter','DataCenterController');
  Route::resource('SubgroupDatacenter','SubgroupDCController');

  Route::get('/admin/flexvalue','FlexvalueController@index')->name('flexvalue.index');
  Route::get('/admin/flexvalue/show/{master?}/{id?}','FlexvalueController@show')->name('flexvalue.show');
  Route::get('/admin/flexvalue/create','FlexvalueController@create')->name('flexvalue.create');
  Route::post('/admin/flexvalue/create','FlexvalueController@store')->name('flexvalue.store');
  Route::patch('/admin/flexvalue/edit/{master?}/{id?}','FlexvalueController@update')->name('flexvalue.edit');
  Route::delete('/admin/flexvalue/delete/{master?}/{id?}','FlexvalueController@destroy')->name('flexvalue.destroy');

  Route::get('/oracle/getcustomer/{lasttime?}','BackgroundController@getCustomer')->name('oracle.synchronize.customer');
  Route::get('/pricelist/index','PriceController@index')->name('oracle.pricelist.index');
  Route::get('/oracle/getPrice/{lasttime?}', 'BackgroundController@getPricelist')->name('oracle.synchronize.pricelist');
});

Route::get('/manageOutlet/{id?}/{notif_id?}', 'CustomerController@show')->name('customer.show');
//Route::get('/searchNoo', 'CustomerController@searchNoo')->name('customer.searchNoo');
Route::get('customer/searchOutlet', 'CustomerController@searchOutlet')->name('customer.searchoutlet');
Route::get('customer/searchDistributor/{flag?}', 'CustomerController@searchDistributor')->name('customer.searchDistributor');
Route::get('customer/searchOutletDistributor', 'CustomerController@searchOutletDistributor')->name('customer.searchOutletDistributor');
Route::get('customer/searchOracleOutlet', 'CustomerController@searchOracleOutlet')->name('customer.oracle.searchoutlet');
Route::get('customer/searchCustomerOracle', 'CustomerController@searchCustomerOracle')->name('customer.oracle.searchCustomer');
Route::get('ajax/searchProduct', 'PriceController@ajaxSearchProduct')->name('oracle.searchProduct');



Route::group(['middleware' => ['permission:Outlet_Distributor']], function () {
  Route::match(['get','post'],'/searchNoo', 'CustomerController@listNoo')->name('customer.listNoo');
  Route::match(['get','post'],'/reportNoo', 'CustomerController@reportNoo')->name('customer.reportNoo');
  Route::get('/searchCustomer/{search}/{id}','CustomerController@search');
  Route::get('/tambahDistributor/{id}/{outletid}','CustomerController@addlist');
  Route::patch('/saveOutlet/{customer}', 'CustomerController@update')->name('customer.update');
  Route::post('/rejectbyGPL','CustomerController@rejectGPL')->name('customer.rejectGPL');
  Route::patch('/customer/inactiveDistributor','CustomerController@inactiveDistributor')->name('customer.inactiveDistributor');
});

/*distributor or principal*/
Route::group(['middleware' => ['permission:ApproveOutlet']], function () {
  Route::post('/approveOutlet','CustomerController@approve')->name('customer.approve');
  Route::post('/rejectOutlet','CustomerController@reject')->name('customer.reject');
});
/*
Route::get('/csv/user', function()
{
    if (($handle = fopen(public_path() . '/uploads/user3.csv','r')) !== FALSE)
    {
        while (($data = fgetcsv($handle, 1000, ',')) !==FALSE)
        {
                $user = new \App\User();
                $user->id = Webpatser\Uuid\Uuid::generate();
                $user->name = $data[0];
                $user->email = $data[1];
                $user->customer_id = $data[2];
                $user->password = bcrypt('123456');
                $user->validate_flag = 1;
                $user->register_flag = 1;
                $user->avatar = 'default.jpg';
                $user->save();
        }
        fclose($handle);
    }

    return \App\User::all();
});


Route::get('/csv/cabang', function()
{
    if (($handle = fopen(public_path() . '/uploads/outlet.csv','r')) !== FALSE)
    {
        while (($data = fgetcsv($handle, 1000, ',')) !==FALSE)
        {
                $custumer = new \App\Customer();
                $custumer->id = Webpatser\Uuid\Uuid::generate();
                $custumer->customer_name = $data[0];
                $custumer->customer_number = $data[1];
                $custumer->psc_flag = $data[2];
                $custumer->pharma_flag=$data[3];
                //$custumer->parent_dist=$data[4];
                $custumer->outlet_type_id=$data[4];
                //$custumer->subgroup_dc_id=$data[5];
                $custumer->status='A';
                $custumer->save();
        }
        fclose($handle);
    }

  //  return \App\Customer::whereNotNull('parent_dist');
});*/

/*check PO from Outlet/Distributor*/
Route::group(['middleware' => ['auth','prevent-back-history']], function () {
  Route::get('/checkPO/{id}','OrderController@checkOrder')->name('order.checkPO');
  Route::match(['get', 'post'],'/listpo','OrderController@listOrder')->name('order.listPO');
  Route::match(['get', 'post'],'/listso','OrderController@listSO')->name('order.listSO');
  Route::post('/SO/approval','OrderController@approvalSO')->name('order.approvalSO');
  Route::get('/notif/newpo/{id?}/{notifid?}','OrderController@readnotifnewpo')->name('order.notifnewpo');
  Route::post('/PO/batal','OrderController@batalPO')->name('order.cancelPO');
  Route::post('/PO/update','OrderController@updatePO')->name('order.updatePO');
  //Route::post('/PO/Receive','OrderController@receivePO')->name('order.receivePO');
});

Route::group(['middleware' => ['auth']], function () {
  Route::get('/download/PO/{file}', function ($file='') {
      return response()->download(storage_path('app/PO/'.$file));
  });
});

Route::group(['middleware' => ['role:KurirGPL','prevent-back-history']], function () {
  Route::get('/SO/shipping', 'OrderController@shippingKurir')->name('order.shippingSO');
  Route::post('/SO/shipping', 'OrderController@searchShipping')->name('order.searchShippingOracle');
  Route::post('/SO/shipconfirm', 'OrderController@shipconfirmcourier')->name('order.shipconfirmcourier');
});

Route::get('/oracle/getOrder', 'BackgroundController@getStatusOrderOracle')->name('order.getStatusOracle');
Route::get('/oracle/exportexcel/{id}', 'OrderController@createExcel')->name('order.createExcel');
Route::get('/oracle/synchronize', 'BackgroundController@synchronize_oracle')->name('order.synchronizeOracle');
Route::get('/oracle/synchronizemodifier', 'BackgroundController@getModifierSummary');



Route::get('/oracle/getdiskon/{tglskrg?}', 'BackgroundController@getMasterDiscount')->name('oracle.synchronize.diskon');
/*
Route::get('/test',function () {
  dd (DB::connection('oracle')->select('select name from hr_all_organization_units haou '));
});*/


Route::group(['middleware' => ['permission:UploadCMO']], function () {
    Route::get('uploadCMO', 'FilesController@upload')->name('files.uploadcmo');
    Route::post('/handleUpload', 'FilesController@handleUpload');
    Route::get('/downloadCMO/{file}', function ($file='') {
        return response()->download(storage_path('app/uploads/'.$file));
    });
});

Route::group(['middleware' => ['permission:Create PO']], function () {
    Route::get('importCSV', 'FilesController@importcsv')->name('files.importcsv');
    Route::get('uploadCSV', 'FilesController@uploadcsv')->name('files.uploadcsv');
    Route::post('/parseImport', 'FilesController@parseImport')->name('files.import_parse');
    Route::post('/import_process', 'FilesController@processImport')->name('files.import_process');
    Route::get('/import_process', 'FilesController@downloadTemplateCSV')->name('files.downloadtemplatecsv');
    Route::get('/downloadCSV/{file}', function ($file='') {
        return response()->download(storage_path('app/uploads/'.$file));
    });
});

Route::group(['middleware' => ['permission:DownloadCMO']], function () {
  Route::get('viewAlldownloadfile/{id?}', 'FilesController@downfunc')->name('files.viewfile');
  Route::get('/downloadCMO/{file}', function ($file='') {
      return response()->download(storage_path('app/uploads/'.$file));
  });
  Route::post('/viewAlldownloadfile', 'FilesController@search')->name('files.postviewfile');
  route::get('notifrejectcmo/{id?}/{notifid?}','FilesController@readNotif')->name('files.readnotif');
});
Route::group(['middleware' => ['role:Principal','prevent-back-history']], function () {
  Route::put('/approvecmo/{id}', 'FilesController@approvecmo')->name('files.approvecmo');
  /*report order*/
  Route::get('/report/order','OrderController@rptOrderForm')->name('report.orderform');
  Route::post('/report/order','OrderController@rptOrderForm')->name('report.orderexcel');
  /*end report order*/
});
  Route::get('/dpl/report/','DPLController@dplreport')->name('dpl.report');
  Route::post('/dpl/report/','DPLController@dplreport')->name('dpl.reportdownload');
  Route::get('/dpl/ajax/asmspv/{posisi?}','DPLController@getListSpvAsm')->name('dpl.ajax.asmspv');

/**
* created by WK Productions
*/
Route::get('/dpl/list/','DPLController@dplList')->name('dpl.list');
Route::post('/dpl/list','DPLController@dplList')->name('dpl.list');
Route::get('/dpl/list/outlet','DPLController@getOutletDPL')->name('dpl.listOutlet');
Route::get('/dpl/suggestno/form','DPLController@generateSuggestNoForm')->name('dpl.generateForm');
Route::post('/dpl/suggestno/generate','DPLController@generateExec')->name('dpl.generateExec');
Route::get('/dpl/suggestno/success','DPLController@generateSuccess')->name('dpl.generateSuccess');
Route::get('/dpl/suggestno/validation/{outlet_id}/{suggest_no}','DPLController@suggestNoValidation')->name('dpl.suggestNoValidation');
Route::post('/dpl/suggestno/cancel','DPLController@suggestNoCancel')->name('dpl.suggestNoCancel');
Route::get('/dpl/distlist/{outlet_id}','DPLController@getDistributorList')->name('dpl.distributorList');

Route::get('/dpl/discount/form/{suggest_no?}','DPLController@inputDiscount')->name('dpl.discountForm');
Route::post('/dpl/discount/set','DPLController@discountSet')->name('dpl.discountSet');
Route::get('/dpl/discount/approval/{suggest_no}','DPLController@discountApprovalForm')->name('dpl.discountApproval');
Route::post('/dpl/discount/approval','DPLController@discountApprovalSet')->name('dpl.discountApprovalSet');
Route::get('/dpl/discount/view/{suggest_no}','DPLController@discountView')->name('dpl.discountView');
Route::post('/dpl/discount/split','DPLController@dplDiscountSplit')->name('dpl.discountSplit');


Route::get('/dpl/history/{suggest_no}','DPLController@dplLogHistory')->name('dpl.dplHistory');

Route::get('/dpl/input/form/{suggest_no}','DPLController@dplNoInputForm')->name('dpl.dplNoForm');
Route::post('/dpl/input/set','DPLController@dplNoInputSet')->name('dpl.dplNoSet');

Route::get('/dpl/discount/{suggest_no?}/{notifid?}','DPLController@readNotifDiscount')->name('dpl.readNotifDiscount');
Route::get('/dpl/approval/{suggest_no?}/{notifid?}','DPLController@readNotifApproval')->name('dpl.readNotifApproval');
Route::get('/dpl/input/{suggest_no?}/{notifid?}','DPLController@readNotifDPLInput')->name('dpl.readNotifDPLInput');
Route::get('/dpl/cancel/{suggest_no?}/{notifid?}','DPLController@readNotifDPLCancel')->name('dpl.readNotifDPLCancel');
Route::get('/dpl/cancel/outlet/{suggest_no?}/{notifid?}','DPLController@readNotifDPLCancelOutlet')->name('dpl.readNotifDPLCancelOutlet');

Route::group(['middleware' => ['permission:OrgStructureDPL']], function () {
//Organization Structure
Route::get('/Organization','OrgStructureController@index')->name('org.list');
Route::get('/Organization/{user_id}/setting','OrgStructureController@setting')->name('org.setting');
Route::post('/Organization/{user_id}/setting/save','OrgStructureController@saveSetting')->name('org.saveSetting');
Route::get('/Organization/create','OrgStructureController@create')->name('org.create');
Route::post('/Organization/setting/add','OrgStructureController@addSetting')->name('org.postcreate');
Route::post('/Organization/{user_id}/area/delete','OrgStructureController@deleteArea')->name('org.deleteArea');
});

//Outlet Product and Stock
//---Product---
Route::group(['middleware' => ['role:Apotik/Klinik']], function () {
Route::get('/outlet/product/download/template/product','OutletProductController@downloadTemplateProduct')->name('outlet.downloadTemplateProduct');
Route::get('/outlet/product/import','OutletProductController@importProduct')->name('outlet.importProduct');
Route::post('/outlet/product/import/view','OutletProductController@importProductView')->name('outlet.importProductView');
Route::post('/outlet/product/import/process','OutletProductController@importProductProcess')->name('outlet.importProductProcess');
//---Stock---
Route::get('/outlet/product/download/template/stock','OutletProductController@downloadTemplateStock')->name('outlet.downloadTemplateStock');
Route::get('/outlet/product/import/stock','OutletProductController@importProductStock')->name('outlet.importProductStock');
Route::post('/outlet/product/import/stock/view','OutletProductController@importProductStockView')->name('outlet.importProductStockView');
Route::post('/outlet/product/import/stock/process','OutletProductController@importProductStockProcess')->name('outlet.importProductStockProcess');

Route::get('/outlet/product/list','OutletProductController@listProductStock')->name('outlet.listProductStock');
Route::get('/outlet/product/form/{id?}','OutletProductController@formProduct')->name('outlet.formProduct');
Route::post('/outlet/product/submit','OutletProductController@submitProduct')->name('outlet.submitProduct');
Route::get('/outlet/product/delete/{id}','OutletProductController@deleteProduct')->name('outlet.deleteProduct');
Route::get('/outlet/product/detail/{product_id}/{flag?}','OutletProductController@detailProductStock')->name('outlet.detailProductStock');
Route::get('/outlet/product/getList','OutletProductController@getListProductStock')->name('outlet.getListProductStock');
Route::get('/outlet/product/getBatchOut/{product_id?}','OutletProductController@getListBatchStock')->name('outlet.getListBatchStock');

Route::get('/outlet/transaction','OutletProductController@outletTrx')->name('outlet.trx');
Route::get('/outlet/transaction/list','OutletProductController@outletTrxList')->name('outlet.trxList');
Route::post('/outlet/transaction/in/process','OutletProductController@outletTrxInProcess')->name('outlet.trxInProcess');
Route::post('/outlet/transaction/out/process','OutletProductController@outletTrxOutProcess')->name('outlet.trxOutProcess');
});
/*
*
*/
Route::group(['middleware' => ['role:Principal|Apotik/Klinik']], function () {
Route::get('/outlet/product/download/stock','OutletProductController@downloadProductStock')->name('outlet.downloadStock');
Route::post('/outlet/product/download/stock/view','OutletProductController@downloadProductStockView')->name('outlet.downloadStockView');
Route::post('/outlet/product/download/stock/process','OutletProductController@downloadProductStockProcess')->name('outlet.downloadStockProcess');
});

Route::post('ExportClients', 'ExcelController@ExportClients')->name('ExportClients');

//Route::get('checkImageProduct', 'ExcelController@checkImageProduct')->name('getProdukImage');
Route::get('/sendEmailInvitation', 'UserController@sendEmailInvitation');
Route::group(['middleware' => ['permission:SuperUser']], function () {
Route::get('/users/LogOnAs/{id}','UserController@logOn')->name('users.logOnAs');
});
