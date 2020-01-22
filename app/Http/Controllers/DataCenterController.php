<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\GroupDatacenter;
use DB;

class DataCenterController extends Controller
{
  protected $menu = "GroupDatacenter";
  public function __construct()
  {
      $this->middleware('auth');
      $this->middleware('role:IT Galenium');
  }

  public function index()
  {
    $group=GroupDatacenter::all();//paginate(10);
    return view('admin.groupDC.index',array('groups'=>$group,'menu'=>$this->menu));
  }


  public function create()
  {
    return view('admin.groupDC.create',array('menu'=>$this->menu));
  }


  public function store(Request $request)
  {
    $this->validate($request, [
       'name' => 'required|unique:group_datacenters,name|max:50',
    ]);
    $group = new GroupDatacenter();
    $group->name = $request->name;
    $group->display_name = $request->display_name;
    if($request->status=='1'){
      $group->enabled_flag = $request->status;
    }else {
      $group->enabled_flag = 0;
    }
    $group->save();
      return redirect()->route('GroupDataCenter.edit',$group->id)->withMessage('Success Insert Data');
  }


  public function show(GroupDatacenter $group)
  {

  }


  public function edit($id)
  {
    $group=GroupDatacenter::find($id);//paginate(10);
    return view('admin.groupDC.edit',array('group'=>$group,'menu'=>$this->menu));
  }


  public function update(Request $request, $id)
  {
    $this->validate($request, [
       'name' => 'required|unique:group_datacenters,name,'.$id.'|max:50',
    ]);

    $group=GroupDatacenter::find($id);
    $group->name = $request->name;
    $group->display_name = $request->display_name;
    if($request->status=='1'){
      $group->enabled_flag = $request->status;
    }else {
      $group->enabled_flag = 0;
    }
    $group->save();
      return redirect()->route('GroupDataCenter.edit',$id)->withMessage('Success Update Data');
  }


  public function destroy($id)
  {
    DB::table("group_datacenters")->where('id',$id)->delete();
    return back()->withMessage('Success Delete Data');
  }


}
