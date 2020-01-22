<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoryProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     protected $menu ="CategoryProduct";
    public function index()
    {
        $categories=Category::all();//paginate(10);
        return view('admin.categoryproduct.index',['categories'=>$categories,'menu'=>$this->menu]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.categoryproduct.create',['menu'=>$this->menu]);
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
         'code' => 'required|unique:categories,flex_value|max:5',
         'name' => 'required|unique:categories,description|max:191',
         'parent'=> 'required',
      ]);
      if ($request->status<>'Y')
      {
        $request->status = 'N';
      }

      //dd($request->all());
      $cat=Category::create([
        'flex_value'=>$request->code,
        'description'=>$request->name,
        'parent'=>$request->parent,
        'enabled_flag'=>$request->status,
      ]);
      return redirect()->route('CategoryProduct.index')->withMessage('Category product berhasil disimpan');
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
      $category=Category::find($id);

       return view('admin.categoryproduct.edit',['category'=>$category,'menu'=>$this->menu]);
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

      if ($request->status<>'Y')
      {
        $request->status = 'N';
      }

      try{
        $category=Category::find($id);
        $category->description = $request->name;
        $category->enabled_flag = $request->status;
        $category->save();
          return redirect()->route('CategoryProduct.edit',$id)->withMessage('Category Product Updated');
      }
      catch(Exception $e){
        return Redirect::back()->withErrors(['message', $e]);
        //dd($e);
      }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
