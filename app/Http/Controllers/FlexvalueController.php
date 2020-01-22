<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class FlexvalueController extends Controller
{
    protected $menu ="flexvalue";
    public function index()
    {
      $flexvalue = DB::table('flexvalue')
      ->whereIn('master',['PHARMA_PRODUCT','PSC_PRODUCT'])->orderBy('master','asc')->orderBy('id','asc')->get();
      return view('admin.flexvalue.index',['flexvalue'=>$flexvalue,'menu'=>$this->menu]);
    }

    public function create()
    {
      $group = array(''=>'--Pilih Salah Satu--','PHARMA_PRODUCT'=>'Pharma Product','PSC_PRODUCT'=>'PSC Product');
      return view('admin.flexvalue.create',['menu'=>$this->menu,'group'=>$group]);
    }

    public function store(Request $request)
    {
      DB::beginTransaction();
      try{
        $getID = DB::table('flexvalue')->where('master','=',$request->master)
                ->max('id');
        if(!$getID) $getID = 1; else $getID+=1;
        if($request->status!="Y") $request->status="N";
        $insert = DB::table('flexvalue')->insert(['master'=>$request->master, 'id'=>$getID, 'name'=>$request->name,'enabled_flag'=>$request->status]);
        DB::commit();
        if($insert) return redirect()->route('flexvalue.index')->withMessage('Flexvalue berhasil ditambahkan');
        else return redirect()->route('flexvalue.index')->withMessage('Flexvalue gagal ditambahkan');
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }

    public function show($master=null, $id=null)
    {
      $group = DB::table('flexvalue')
          ->whereIn('master',['PHARMA_PRODUCT','PSC_PRODUCT'])->select('master')->groupBy('master')->orderBy('master','asc')->get();
      $data = DB::table('flexvalue')->where('master','=',$master)->where('id','=',$id)->first();
      return view('admin.flexvalue.edit',['menu'=>$this->menu,'group'=>$group,'data'=>$data]);
    }

    public function update(Request $request,$master=null,$id=null)
    {
      DB::beginTransaction();
      try{
        $update = DB::table('flexvalue')->where('master','=',$master)
                  ->where('id','=',$id)
                  ->update(['name'=>$request->name,'enabled_flag'=>$request->status,'master'=>$request->master]);
        DB::commit();
        if ($update)
        {
          return redirect()->route('flexvalue.index')->withMessage('Flexvalue berhasil disimpan');
        }else return redirect()->route('flexvalue.index')->withMessage('Flexvalue gagal disimpan');
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }

    public function destroy($master=null, $id=null)
    {
      DB::beginTransaction();
      try{
        $delete = DB::table('flexvalue')->where('master','=',$master)
                  ->where('id','=',$id)
                  ->delete();
        DB::commit();
        if($delete)
        {
          return redirect()->route('flexvalue.index')->withMessage('Flexvalue berhasil dihapus');
        }else{
          return redirect()->route('flexvalue.index')->withMessage('Flexvalue gagal dihapus');
        }
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }
}
