<?php

namespace App\Http\Controllers;

use App\OrgStructure;
use App\User;
use DB;
use Webpatser\Uuid\Uuid;
use Datatables;
use Carbon\Carbon;
use Auth;

use Illuminate\Http\Request;

class OrgStructureController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
  	$menu = 'OrgStructure';
  	$users = OrgStructure::select('users.*','org_structure.*','sup.name as sup_name','users.id as user_id')
  								->leftjoin('users','org_structure.user_id','users.id')
  								->leftjoin('users as sup','sup.id','org_structure.directsup_user_id')
  								->whereNull('users.customer_id')
  								->get();

  	return view('admin.org.orgList', compact('users','menu'));
  }

  public function setting($user_id)
  {
  	$menu = 'OrgStructure';
		$roles =DB::table('roles')
					->wherein('name',['SPV','ASM'])
					->select('id','name')
					->get();
		$role_list = array(''=>'---Choose Roles---');
		foreach ($roles as $key => $role) {
  		$role_list[$role->id] = $role->name;
  	}
  	$org = OrgStructure::leftjoin('users as u','u.id','org_structure.user_id')
					 ->leftjoin('role_user as ru','ru.user_id','u.id')
					 ->leftjoin('roles as r','r.id','ru.role_id')
					 ->where('org_structure.id',$user_id)
					 ->select('org_structure.*','u.id as userid','u.email','r.id as role_id','r.name as role_name')->first();

  	$users_sup = User::select('users.*','org_structure.*','users.id as user_id')
  								->leftjoin('org_structure','org_structure.user_id','users.id')
  								->where('org_structure.id','!=',$user_id)
  								->whereNull('customer_id')
									->orderBy('org_structure.description')
  								->get();

  	$users_sup_list = array(''=>'---Choose User---');
  	foreach ($users_sup as $key => $user) {
  		$users_sup_list[$user->user_id] = $user->name;
  	}

		$provinces =DB::table('provinces')->get();

  	return view('admin.org.orgSetting', compact('org','users_sup_list','user_id','menu','role_list','provinces'));
  }

  public function saveSetting(Request $request, $user_id)
  {
  	$user_code = $request->user_code;
  	$user_sup = $request->directsup;
		DB::beginTransaction();
		try{
			if (!empty($request->user_id) and $request->email!=""){
				$this->validate($request, [
					 'email' => 'unique:users,email,'.$request->user_id.',id',
					 'user_code' => 'required|unique:org_structure,user_code,'.$user_id.',id|max:16',
				]);
			}else{
				$this->validate($request, [
					 'user_code' => 'required|unique:org_structure,user_code,'.$user_id.',id|max:16',
				]);
			}

	  	$org = OrgStructure::where('id',$user_id)->first();
			if(!$org){
	  		$org = new OrgStructure;
	  	}
			/*jika ganti email*/
			if (!empty($request->user_id) and $request->email!="")
			{
				$user =User::where('id',$request->user_id)->first();
				if($user) {
					$user->email = $request->email;
					$user->save();
					$org->user_id = $user->id;
				}else{
					return redirect()->back()->withInput()
			  	                ->withErrors('email','user id not found');
				}
			}elseif(empty($org->user_id) and $request->email!=""){
				$user = User::where('email',$request->email)
								->wherenotexists(function($query) use($user_id){
									$query->select(DB::raw(1))
										->from('org_structure as os')
										->whereraw("os.user_id = users.id and os.id!='".$user_id."'");
								})->first();
				if(!$user){
					$user = User::Create(
						['id'=>Uuid::generate()->string
						,'email'=>$request->email
						,'name'=>$request->user_name
						,'passswrod'=>bcrypt('123456')
						,'validate_flag'=>1
						,'register_flag'=>1]
					);
				}
				$user->roles()->detach();
				$user->roles()->attach($request->role);
				if($user) $org->user_id = $user->id; else $org->user_id=null;
			}else{
				$org->user_id=null;
			}

			$org->description = $request->user_name;
	  	$org->user_code = $user_code;
	  	$org->directsup_user_id = $user_sup;
	  	$org->save();
			DB::commit();
	  	return redirect()->route('org.list')
	  	                ->with('success','Setting saved successfully.');
		}catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}
  }

	public function create()
	{
		$menu = 'OrgStructure';
		$roles =DB::table('roles')
					->wherein('name',['SPV','ASM'])
					->select('id','name')
					->get();
		$role_list = array(''=>'---Choose Roles---');
		foreach ($roles as $key => $role) {
  		$role_list[$role->id] = $role->name;
  	}
		$users_sup = User::select('users.*','org_structure.*','users.id as user_id')
  								->leftjoin('org_structure','org_structure.user_id','users.id')
									->whereExists(function ($query) {
												$query->select(DB::raw(1))
															->from('role_user as ru')
															->join('roles as r','ru.role_id','r.id')
															->whereraw('ru.user_id = users.id')
															->whereIn('r.name',['ASM','HSM','FSM']);
									})->whereNull('customer_id')
  								->get();

  	$users_sup_list = array(''=>'---Choose User---');
  	foreach ($users_sup as $key => $user) {
  		$users_sup_list[$user->user_id] = $user->name;
  	}
		return view('admin.org.orgSettingAdd',compact('users_sup_list','role_list','menu'));
	}

	public function addSetting(Request $request)
	{
		$this->validate($request, [
			 'email' => 'unique:users,email,'.$request->user_id.',id',
			 'user_code' => 'required|unique:org_structure,user_code|max:16',
		]);

		if(!is_null($request->email))
		{
			$newuser = User::where('id','=',$request->user_id)->orwhere('email','=',$request->email)->first();
			if($newuser)
			{
				$newuser->name = $request->user_name;
				$newuser->save();
			}else{
				$newuser= User::Create(
					['id'=>Uuid::generate()->string
					,'email'=>$request->email
					,'name'=>$request->user_name
					,'passswrod'=>bcrypt('123456')
					,'validate_flag'=>1
					,'register_flag'=>1]
				);
			}

			$newuser->roles()->sync($request->role);
			$request->user_id = $newuser->id;
		}else{
			$request->user_id =null;
		}

		$exists = OrgStructure::where('user_code','=',$request->user_code)->first();
		if($exists)
		{
			$exists->user_id = $request->user_id;
			$exists->description = $request->user_name;
			$exists->directsup_user_id =$request->directsup;
			$exists->save();
			return redirect()->route('org.create') ->with('success','Organization Structure updated successfully.');
		}else{
			$neworg = new OrgStructure();
			$neworg->user_code = $request->user_code;
			$neworg->user_id = $request->user_id;
			$neworg->directsup_user_id = $request->directsup;
			$neworg->description = $request->user_name;
			$neworg->save();
			return redirect()->route('org.create') ->with('success','Organization Structure added successfully.');
		}

	}

	public function ajaxOrgArea($id=null){
		$area = DB::table('mapping_region_dpl')
								->join('regencies as r','mapping_region_dpl.regency_id','r.id')
								->join('provinces as p','p.id','r.province_id')
								->join('org_structure as os','mapping_region_dpl.user_code','os.user_code')
								->where('os.id','=',$id);
		$area =$area->select('r.id','p.name as province','r.name as city');

		return Datatables::of($area)
					->editColumn('id',function($area){
						return '<input type="checkbox" class="chk-mapping" name="mapping[]" value='.$area->id.'>';
					})->rawColumns(['id'])
					->make(true);
	}

	public function ajaxaddAreaDPL(Request $request)
	{
		$error_array = array();
		$success_output = '';
		$x=0;
	 if($request->get('button_action')=="add")
	 {
		 $code=$request->get('code');
		 foreach ($request->value as $nil){
			 $ada = DB::table('mapping_region_dpl')->where('user_code',$code)
			 				->where('regency_id',$nil)
							->first();
			 if(!$ada){
			 		$insert = DB::table("mapping_region_dpl")
								 ->insert(['user_code'=>$code
													 ,'regency_id'=>$nil
													 ,'created_at'=>Carbon::now()
													 ,'updated_at'=>Carbon::now()
													 ,'created_by'=>Auth::user()->id
													 ,'last_update_by' => Auth::user()->id
												 ]);
					if($insert) $x++;

			 }
		 }
		 if ($x >0) $success_output = '<div class="alert alert-success">'.$x.' data inserted</div>';
		 else $error_array = 'No Data Inserted';
	 }
	 $output = array(
				 'error'     =>  $error_array,
				 'success'   =>  $success_output
		 );
	 echo json_encode($output);
	}

	public function deleteArea(Request $request,$user_id){
		$org = OrgStructure::where('id',$user_id)->first();
		if($org){
			$user_code =$org->user_code;
			DB::beginTransaction();
			try{
				if(is_array($request->mapping))
						$deletedata = DB::table('mapping_region_dpl')->where("user_code",$user_code)
							->whereIn('regency_id',$request->mapping)
							->delete();
				DB::commit();
				return redirect()->route('org.setting',$user_id)
												->with('success','Successfully delete data mapping');
			}catch (\Exception $e) {
        DB::rollback();
        throw $e;
			}
		}
	}

}
