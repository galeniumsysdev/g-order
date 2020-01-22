<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Banner;
use Image;
use File;
use DB;

class BannerController extends Controller
{
    protected $menu = "banner";
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Banner');
    }

    public function create()
    {
        $menu = $this->menu;
        return view('admin.banner.create',compact('menu'));
    }

    public function edit($id)
    {
        $banner=Banner::find($id);
        $menu = $this->menu;
        return view('admin.banner.edit',compact(['banner','menu']));
    }

    public function store(Request $request)
    {
      $this->validate($request, [
         'input_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);
      $gmbr = $request->file('input_img');
      $filename = 'slide_'.time() . '.' . $gmbr->getClientOriginalExtension();
      Image::make($gmbr)->resize(1348, 478)->save( public_path('uploads/product/' . $filename));
      $banner = new Banner;
      $banner->teks = $request->caption;
      $banner->image_path = 'uploads/product/'.$filename;
      if($request->publish=="Y")
      {
          $banner->publish_flag = $request->publish;
      }else{
        $banner->publish_flag = "N";
      }
      $banner->save();
        return redirect()->route('admin.banner')->withMessage('Banner berhasil dibuat');
    }

    public function update(Request $request,$id)
    {
        $banner=Banner::find($id);
        $banner->teks = $request->caption;
        $gmbr = $request->file('input_img');
        if($gmbr){
          $temp=$banner->image_path;
          $this->validate($request, [
             'input_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
          ]);
          $filename = 'slide_'.time() . '.' . $gmbr->getClientOriginalExtension();
          Image::make($gmbr)->resize(1348, 478)->save( public_path('uploads/product/' . $filename));
          $banner->image_path = 'uploads/product/'.$filename;
          if($temp)
          {
            if (File::exists(public_path($temp))){
              unlink(public_path($temp));
            }
          }
        }
        //$banner->image_path = 'uploads/product/'.$filename;
        if($request->publish=="Y")
        {
            $banner->publish_flag = $request->publish;
        }else{
          $banner->publish_flag = "N";
        }
        $banner->save();
          return redirect()->route('admin.banner')->withMessage('Banner berhasil diubah');
    }

    public function listBanner()
    {
      $banners = Banner::all();
      return view('admin.banner.index',['banners' => $banners,'menu'=>$this->menu]);
    }
    public function publish($publish,$id)
    {
        $banners = Banner::where('id','=',$id)->update(['publish_flag'=>$publish,'last_update_by'=>Auth::user()->id]);
        return redirect()->route('admin.banner')->withMessage('Banner telah diupdate');
    }
    public function destroy($id)
    {
      $banner = Banner::where('id','=',$id)->first();
      if (File::exists(public_path('uploads\\product\\'.$banner->image_path))){
        unlink(public_path('uploads\\product\\'.$banner->image_path));
      }
      $banner->delete();
      //DB::table("banners")->where('id',$id)->delete();
      //return back()->withMessage('Banner berhasil dihapus');
      return response()->json([
        'success' => 'Record has been deleted successfully!'
      ]);
    }
}
