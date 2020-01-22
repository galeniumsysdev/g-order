<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use App\Permission;
use Illuminate\Support\Facades\DB;
use PDF;

class RoleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles=Role::all();//paginate(10);
        $menu = "role";
        return view('admin.role.index',compact('roles','menu'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permissions= Permission::all();
        $menu = "role";
        return view('admin.role.create',compact('permissions','menu'));
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
         'name' => 'required|unique:roles|max:50',
      ]);
        //dd($request->all());
        $role=Role::create($request->except(['permission','_token']));

        foreach ((array) $request->permission as $key=>$value){
          $role->attachPermission($value);
        }
        return redirect()->route('role.index')->withMessage('role created');
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
      $role=Role::find($id);
      $permissions=Permission::all();
      $menu="role";
      $role_permissions = $role->perms()->pluck('id','id')->toArray();
      

       return view('admin.role.edit',compact(['role','role_permissions','permissions','menu']));
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
         'name' => 'required|unique:roles,name,'.$id.'|max:50',
      ]);
      $role=Role::find($id);
      $role->name=$request->name;
      $role->display_name=$request->display_name;
      $role->description=$request->description;
      $role->save();

      DB::table('permission_role')->where('role_id',$id)->delete();
      if (isset($request->permission)){
        foreach ($request->permission as $key=>$value){
            $role->attachPermission($value);
        }
      }


      return redirect()->route('role.edit',$role->id)->withMessage('Role Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      DB::table("roles")->where('id',$id)->delete();
      return back()->withMessage('Role Deleted');
    }

    public function makePDF(){
      $roles=Role::all();


      $no=0;
      $pdf = PDF::loadView('admin.role.listpdf',compact('roles','no'));
      $pdf->setPaper('a4','portrait');
      //return $pdf->stream();
      return $pdf->download('role.pdf');
    }
}
