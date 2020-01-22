<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
      $this->menu="permission";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $permissions=Permission::all();//paginate(10);
      return view('admin.permission.index',['permissions'=>$permissions,'menu'=>$this->menu]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return view('admin.permission.create',['menu'=>$this->menu]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $this->validate($request, [
         'name' => 'required|unique:permissions|max:50',
      ]);
      $permission=Permission::create($request->all());
      return redirect()->route('permission.index')->withMessage('Permission created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $permission=Permission::find($id);

       return view('admin.permission.edit',['permission'=>$permission,'menu'=>$this->menu]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $this->validate($request, [
         'name' => 'required|unique:permissions,name,'.$id.'|max:50',
      ]);
      $permission=Permission::find($id);
      $permission->name=$request->name;
      $permission->display_name=$request->display_name;
      $permission->description=$request->description;
      $permission->save();

      return redirect()->route('permission.edit',$permission->id)->withMessage('Permission Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      DB::table("permissions")->where('id',$id)->delete();
      return back()->withMessage('Permission Deleted');
    }
}
