<?php

namespace App\Http\Controllers;

use App\CategoryOutlet;
use Illuminate\Http\Request;

class Cat_OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $categories=CategoryOutlet::all();//paginate(10);
      $menu = "CategoryOutlet";
      return view('admin.category.index',compact('categories','menu'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $menu = "CategoryOutlet";
      return view('admin.category.create',compact('menu'));
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
         'name' => 'required|unique:category_outlets|max:255',
      ]);
      if ($request->status<>'Y')
      {
        $request->status = 'N';
      }

      //dd($request->all());
      $cat=CategoryOutlet::create([
        'name'=>$request->name,
        'enable_flag'=>$request->status,
      ]);
      return redirect()->route('CategoryOutlet.index')->withMessage('Category Outlet created');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CategoryOutlet  $categoryOutlet
     * @return \Illuminate\Http\Response
     */
    public function show(CategoryOutlet $categoryOutlet)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CategoryOutlet  $categoryOutlet
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $category=CategoryOutlet::find($id);
      $menu="CategoryOutlet";

       return view('admin.category.edit',compact(['category','menu']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CategoryOutlet  $categoryOutlet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      if ($request->status<>'Y')
      {
        $request->status = 'N';
      }
      //dd($request->all());
      try{
        $category=CategoryOutlet::find($id);
        $category->name = $request->name;
        $category->enable_flag = $request->status;
        $category->save();
          return redirect()->route('CategoryOutlet.edit',$id)->withMessage('Category Outlet Updated');
      }
      catch(Exception $e){
        return Redirect::back()->withErrors(['message', $e]);
        //dd($e);
      }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CategoryOutlet  $categoryOutlet
     * @return \Illuminate\Http\Response
     */
    public function destroy(CategoryOutlet $categoryOutlet)
    {
        //
    }
}
