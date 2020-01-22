<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SubgroupDatacenter;
use App\GroupDatacenter;
use DB;


class SubgroupDCController extends Controller
{
  protected $menu = "SubgroupDatacenter";
  public function __construct()
  {
      $this->middleware('auth');
      $this->middleware('role:IT Galenium');
  }

  public function index()
  {
    $subgroups=SubgroupDatacenter::orderBy('group_id','asc')->orderBy('id','asc')->get();//paginate(10);
    return view('admin.SubgroupDC.index',array('subgroups'=>$subgroups,'menu'=>$this->menu));
  }


  public function create()
  {
    $groups = GroupDatacenter::where('enabled_flag','=',1)->get();
    return view('admin.SubgroupDC.create',array('groups'=>$groups,'menu'=>$this->menu));
  }


  public function store(Request $request)
  {
    $this->validate($request, [
       'name' => 'required|unique:subgroup_datacenters,name|max:50',
       'groupdc' =>'required'
    ]);
    $subgroup = new SubgroupDatacenter();
    $subgroup->name = $request->name;
    $subgroup->display_name = $request->display_name;
    $subgroup->group_id = $request->groupdc;
    if($request->status=='1'){
      $subgroup->enabled_flag = $request->status;
    }else {
      $subgroup->enabled_flag = 0;
    }
    $subgroup->save();
      return redirect()->route('GroupDataCenter.edit',$group->id)->withMessage('Success Insert Data');
  }


  public function show(GroupDatacenter $group)
  {

  }


  public function edit($id)
  {
    $subgroup=SubgroupDatacenter::find($id);//paginate(10);
    $groups = GroupDatacenter::where('enabled_flag','=',1)->get();
    return view('admin.SubgroupDC.edit',array('subgroup'=>$subgroup,'groups'=>$groups,'menu'=>$this->menu));
  }


  public function update(Request $request, $id)
  {
    $this->validate($request, [
       'name' => 'required|unique:subgroup_datacenters,name,'.$id.'|max:50',
       'groupdc'=>'required'
    ]);

    $subgroup=SubgroupDatacenter::find($id);
    $subgroup->name = $request->name;
    $subgroup->display_name = $request->display_name;
    $subgroup->group_id = $request->groupdc;
    if($request->status=='1'){
      $subgroup->enabled_flag = $request->status;
    }else {
      $subgroup->enabled_flag = 0;
    }
    $subgroup->save();
      return redirect()->route('SubgroupDatacenter.edit',$id)->withMessage('Success Update Data');
  }


  public function destroy($id)
  {
    DB::table("subgroup_datacenters")->where('id',$id)->delete();
    return back()->withMessage('Success Delete Data');
  }
}
