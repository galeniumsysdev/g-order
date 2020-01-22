<?php
/**
 * created by WK Productions
 */

namespace App\Http\Controllers;

use App\DPLLog;
use App\DPLNo;
use App\DPLSuggestNo;
use App\Customer;
use App\OrgStructure;
use App\OutletDistributor;
use App\SoHeader;
use App\SoLine;
use App\User;
use App\Role;
use Carbon\Carbon;
use Auth;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;
use Illuminate\Support\Facades\Validator;

class DPLController extends Controller {
	public function __construct() {
		$this->middleware('auth');
	}

	public function generateSuggestNoForm() {
		return view('admin.dpl.genSuggestNo');
	}

	public function getOutletDPL(){
		//DB::enablequerylog();
		$outlets = Customer::select('customers.id','customers.customer_name','cs.address1','cs.province','cs.city')
			//->join('customers', 'customers.id', 'outlet_distributor.outlet_id')
			->join('customer_sites as cs',function($join){
					$join->on('cs.customer_id','=','customers.id');
					$join->on('cs.site_use_code','=',DB::raw("'SHIP_TO'"));
					$join->on('cs.primary_flag','=',DB::raw("'Y'"));
			})
			->join('users as u','customers.id','u.customer_id')
			->join('role_user as ru','ru.user_id','u.id')
			->join('roles as r','ru.role_id','r.id')
			->where('customers.pharma_flag','=','1')
			->whereIn('r.name',['Outlet','Apotik/Klinik'])
			->where('customers.status','=','A')
			->where('u.register_flag','=',1)
			->WhereExists(function($query){
				$query->select(DB::raw(1))
						->from('org_structure as os')
						->join('mapping_region_dpl as mrd','os.user_code','mrd.user_code')
						->whereraw("os.user_id='".Auth::user()->id."' and mrd.regency_id=cs.city_id");
			})
			->groupBy('customers.id','customers.customer_name','cs.address1','cs.province','cs.city')
			->orderBy('customers.customer_name')
			->get();
			//dd(DB::getquerylog());
		return response()->json($outlets);
	}

	public function generateExec(Request $request) {
		$token = "";
		do {
			$codeAlphanum = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$codeAlphanum .= "0123456789";

			mt_srand();

			for ($i = 0; $i < 8; $i++) {
				$token .= $codeAlphanum[mt_rand(0, 35)];
			}

			$checkSuggestNo = DPLSuggestNo::where('suggest_no', $token)->count();
		} while ($checkSuggestNo);

		$dplSuggestNo = new DPLSuggestNo;
		$dplSuggestNo->mr_id = Auth::User()->id;
		$dplSuggestNo->outlet_id = $request->outlet_id;
		$dplSuggestNo->suggest_no = $token;
		$dplSuggestNo->kowil_mr = $request->kowil_mr;
		$dplSuggestNo->save();

		$this->dplLog($token, 'Create DPL Suggest No.');

		\Session::flash('suggest_no', $token);

		return redirect('/dpl/suggestno/success');
	}

	public function generateSuccess() {
		if (\Session::has('suggest_no')) {
			return view('admin.dpl.genSuggestNoSuccess', array('suggest_no' => session('suggest_no')));
		} else {
			return redirect('/dpl/suggestno/form');
		}
	}

	public function suggestNoValidation($outlet_id, $suggest_no) {
		$check_dpl = DPLSuggestNo::where('outlet_id', $outlet_id)
			->where('suggest_no', $suggest_no)
			->whereNull('notrx')
			->count();

		if ($check_dpl) {
			return response()->json(array('valid' => true));
		} else {
			return response()->json(array('valid' => false));
		}

	}

	public function getDistributorList($outlet_id) {
		$distributors = OutletDistributor::join('customers', 'customers.id', 'outlet_distributor.distributor_id')
			->where('outlet_id', $outlet_id)
			->get();

		return response()->json($distributors);
	}

	public function discountView($suggest_no) {
		$dpl = DPLSuggestNo::select('mr.id as dpl_mr_id',
			'mr.name as dpl_mr_name',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'suggest_no',
			'notrx',
			'fill_in',
			'approver.name as approver_name','file_sp')
			->join('users as mr', 'mr.id', 'dpl_suggest_no.mr_id')
			->leftjoin('users as approver', 'approver.id', 'dpl_suggest_no.approved_by')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->where('suggest_no', $suggest_no)
			//->where('active', 1)
			->first();

		$header = DB::table('so_header_v as sh')
			->where('notrx', '=', $dpl['notrx'])->first();
		$lines = DB::table('so_lines_v')->where('header_id', '=', $header->id)->get();

		/*Reason reject, if any*/
		$log = DPLLog::join('users','users.id','dpl_log.done_by')
					->where('suggest_no',$suggest_no)
					->orderby('dpl_log.id','desc')
					->first();
		$dpl->reason = nl2br($log['reason']);
		$dpl->reject_by = $log['name'];
		$dpl->log_type = $log['type'];

		$user_dist = User::where('customer_id', '=', $header->distributor_id)->first();

		$distributor_list = OutletDistributor::join('customers', 'customers.id', 'outlet_distributor.distributor_id')
			->where('outlet_id', $dpl['outlet_id'])
			->get();

		return view('admin.dpl.discountView', compact('dpl', 'header', 'lines', 'distributor_list'));
	}

	public function inputDiscount($suggest_no) {
		$allowed = DB::select('SELECT privilegeSuggestNo(?,?) AS allowed', [$suggest_no, Auth::user()->id]);
		if(empty($allowed)){
			return view('errors.403');
		}
		$dpl = DPLSuggestNo::select('mr.id as dpl_mr_id',
			'mr.name as dpl_mr_name',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'dpl_suggest_no.suggest_no',
			'outlet_id',
			'notrx',
			'note',
			'fill_in',
			'approver.name as approver_name',
			'dpl_no.dpl_no',
			'dpl_suggest_no.active',
			'dpl_suggest_no.file_sp')
			->join('users as mr', 'mr.id', 'dpl_suggest_no.mr_id')
			->leftjoin('users as approver', 'approver.id', 'dpl_suggest_no.approved_by')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->leftjoin('dpl_no', 'dpl_no.suggest_no', 'dpl_suggest_no.suggest_no')
			->where('dpl_suggest_no.suggest_no', $suggest_no)
			//->where('active', 1)
			->first();

		if(!$dpl['active'])
			return redirect('/dpl/discount/view/' . $suggest_no);
		if (!$dpl['fill_in']) {
			return redirect('/dpl/discount/approval/' . $suggest_no);
		}

		$header = DB::table('so_header_v as sh')
			->where('notrx', '=', $dpl['notrx'])->first();
		if (!$header) {
			return view('errors.403');
		}
		$lines = DB::table('so_lines_v')->where('header_id', '=', $header->id)->get();

		/*Reason reject, if any*/
		$log = DPLLog::join('users','users.id','dpl_log.done_by')
					->where('suggest_no',$suggest_no)
					->orderby('dpl_log.id','desc')
					->first();
		$dpl->reason = nl2br($log['reason']);
		$dpl->reject_by = $log['name'];
		$dpl->log_type = $log['type'];

		$user_dist = User::where('customer_id', '=', $header->distributor_id)->first();

		$distributors = OutletDistributor::join('customers', 'customers.id', 'outlet_distributor.distributor_id')
			->where('outlet_id', $dpl['outlet_id'])
			->get();

		$distributor_list = array();

		foreach ($distributors as $key => $distributor) {
			$distributor_list[$distributor->distributor_id] = $distributor->customer_name;
		}
		if($header->dpl_no)
		{
			return view('admin.dpl.discountReform', compact('dpl', 'header', 'lines', 'distributor_list'));
		}else return view('admin.dpl.discountForm', compact('dpl', 'header', 'lines', 'distributor_list'));
	}

	public function discountSet(Request $request) {
		DB::beginTransaction();
		try{
			$discount = $request->discount;
			$discount_gpl = $request->discount_gpl;
			$bonus_gpl = $request->bonus_gpl;
			$suggest_no = $request->suggest_no;
			$distributor = $request->distributor;
			$notrx = $request->notrx;
			$note = $request->note;
			$path=null;
			if($request->hasFile('filesp'))
			{
				$validator = Validator::make($request->all(), [
						'filesp' => 'required|mimes:jpeg,jpg,png,pdf|max:10240',
				])->validate();
				$path = $request->file('filesp')->storeAs(
						'PO', "SP_".$notrx.".".$request->file('filesp')->getClientOriginalExtension()
				);
				$update_file = DPLSuggestNo::where('suggest_no',$suggest_no)
											->update(array('file_sp'=>$path,'last_update_by'=>Auth::user()->id));
			}
			$so_header = SoHeader::where('notrx', $notrx)->update(array('distributor_id' => $distributor,'last_update_by'=>Auth::user()->id));

			$input_note = DPLSuggestNo::where('suggest_no',$suggest_no)
										->update(array('note'=>$note,'last_update_by'=>Auth::user()->id));

			foreach ($discount as $key => $disc) {
				$so_line = SoLine::where('line_id', $key)
					->update(array('discount' => ($disc ? $disc : 0),
						'discount_gpl' => ($discount_gpl[$key] ? $discount_gpl[$key] : 0),
						'bonus_gpl' => ($bonus_gpl[$key] ? $bonus_gpl[$key] : 0),
						'last_update_by'=>Auth::user()->id,
					));
			}
			$user_role = Auth::user()->roles->whereIn('name',['SPV','ASM','Admin DPL','FSM','HSM'])->first();

			$dplno = DPLNo::where('suggest_no','=',$suggest_no)->first();
			if(($user_role->name == 'FSM' or $user_role->name=='HSM') and ($dplno) )
			{
					$this->sendDPLNo($suggest_no,$dplno->dpl_no);
					$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
						->update(array('fill_in' => 0,
										'approved_by' => Auth::user()->id,
										'next_approver' => null
										,'last_update_by'=>Auth::user()->id
										));
					DB::commit();
					return redirect('/dpl/list');
			}

			$next_approver = OrgStructure::select('org_structure.*','email')
										->join('users','users.id','org_structure.directsup_user_id')
										->where('user_id', Auth::user()->id)
										->first();

			$this->dplLog($suggest_no, 'Input Discount');

			//notif
			$dpl_outlet = DPLSuggestNo::select('dpl_suggest_no.*','customer_name')
								->join('customers','customers.id','dpl_suggest_no.outlet_id')
								->where('suggest_no', $suggest_no)
								->first();


			$notified_users = $this->getArrayNotifiedEmail($suggest_no, $user_role->name);
			if(!empty($notified_users)){
				if($user_role->name != 'FSM' && $user_role->name != 'HSM'){
				$data = [
					'title' => 'Permohonan Approval',
					'message' => 'Permohonan Approval #'.$suggest_no,
					'id' => $suggest_no,
					'href' => route('dpl.readNotifDiscount'),
					'mail' => [
						'greeting'=>'Yang terhormat FSM/HSM Galenium',
						'content'=> 'Bersama ini kami informasikan No. Pengajuan DPL #'.$suggest_no.' membutuhkan approval Anda.<br>Untuk melihat detail pengajuan DPL, silakan login ke dalam sistem aplikasi gOrder (http://g-order.id) menggunakan email dan password Anda.<br>Terima kasih.'
					]
				];
			}else{
					$data = [
						'title' => 'Pengisian No. DPL',
						'message' => 'Pengisian No. DPL untuk #'.$suggest_no,
						'id' => $suggest_no,
						'href' => route('dpl.readNotifDPLInput'),
						'mail' => [
							'greeting'=>'',
							'content'=> ''
						]
					];
				}
				foreach ($notified_users as $key => $email) {
					if($user_role->name != 'FSM' && $user_role->name != 'HSM'){
						$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
							->update(array('fill_in' => 0,
											'approved_by' => Auth::user()->id,
											'next_approver' => $key
											,'last_update_by'=>Auth::user()->id
											));
					}else{
						$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
							->update(array('fill_in' => 0,
											'approved_by' => Auth::user()->id,
											'next_approver' => null
											,'last_update_by'=>Auth::user()->id
											));
					}
					foreach ($email as $key2 => $mail) {
						$data['email'] = $mail;
						if($key == 'FSM_HSM'){
							if($user_role->name == 'Admin DPL')
								$data['sendmail'] = 1;
							else
								$data['sendmail'] = 0;
						}
						else
							$data['sendmail'] = 0;
						$apps_user = User::where('email',$mail)->first();
						if(!empty($apps_user))
							$apps_user->notify(new PushNotif($data));
					}
					break;
				}
			}
			DB::commit();
			return redirect('/dpl/list');
		}catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}
	}

	public function discountApprovalForm($suggest_no) {
		$allowed = DB::select('SELECT privilegeSuggestNo(?,?) AS allowed', [$suggest_no, Auth::user()->id]);
		if(empty($allowed)){
			return view('errors.403');
		}
		$dpl = DPLSuggestNo::select('mr.id as dpl_mr_id',
			'mr.name as dpl_mr_name',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'suggest_no',
			'notrx',
			'note',
			'fill_in',
			'approved_by',
			'next_approver',
			'approver.name as approver_name',
			'dpl_suggest_no.active',
			'dpl_suggest_no.file_sp')
			->join('users as mr', 'mr.id', 'dpl_suggest_no.mr_id')
			->leftjoin('users as approver', 'approver.id', 'dpl_suggest_no.approved_by')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->where('suggest_no', $suggest_no)
			//->where('active', 1)
			->first();

		if(!$dpl['active'])
				return redirect('/dpl/discount/view/' . $suggest_no);
		if ($dpl['fill_in']) {
			return redirect('/dpl/discount/form/' . $suggest_no);
		}

		$header = DB::table('so_header_v as sh')
			->where('notrx', '=', $dpl['notrx'])->first();
		if (!$header) {
			return view('errors.403');
		}

		$user_role = Auth::user()->roles->whereIn('name',['SPV','ASM','Admin DPL','FSM','HSM'])->first();

		$prev_approver = Role::join('role_user','role_user.role_id','roles.id')
								->where('role_user.user_id',$dpl['approved_by'])
								->whereIn('name',['SPV','ASM','Admin DPL','FSM','HSM'])
								->first();

		$role_prev_approve = $prev_approver['name'];

		$notified_users = $this->getArrayNotifiedEmail($suggest_no, $role_prev_approve,false);
		if(!empty($notified_users)){
			$check_count = 0;
			foreach ($notified_users as $ind => $email) {
				if(strpos($ind, $user_role->name) !== false && ($role_prev_approve != 'FSM' && $role_prev_approve != 'HSM')){
					$check_count++;
					break;
				}
			}

			if($check_count == 0)
				return redirect()->route('dpl.discountView',$suggest_no);
		}

		$lines = DB::table('so_lines_v')->where('header_id', '=', $header->id)->get();

		$user_dist = User::where('customer_id', '=', $header->distributor_id)->first();

		$distributor_list = OutletDistributor::join('customers', 'customers.id', 'outlet_distributor.distributor_id')
			->where('outlet_id', $dpl['outlet_id'])
			->get();

		return view('admin.dpl.discountApprovalForm', compact('dpl', 'header', 'lines', 'distributor_list'));
	}

	public function discountApprovalSet(Request $request) {
		$suggest_no = $request->suggest_no;
		$action = $request->action;
		$user_role = Auth::user()->roles->whereIn('name',['SPV','ASM','Admin DPL','FSM','HSM'])->first();
		/*check apakah dapat approve atau tidak*/
		$prev_approver =Role::join('role_user','role_user.role_id','roles.id')
									->join('dpl_suggest_no as dsn','dsn.approved_by','role_user.user_id')
									->join('users','users.id','dsn.approved_by')
									->where('dsn.suggest_no',$suggest_no)
									->wherein('roles.name',['SPV','ASM','HSM','FSM','Admin DPL'])
									->select('roles.name','users.name as user_name')
									->first();
		$role_prev_approve = $prev_approver['name'];
		if($role_prev_approve =="HSM" or $role_prev_approve=="FSM")
			return response()->json(['status'=>'error', 'error' => trans('dpl.alreadyapproved',['suggestno'=>$suggest_no,'lastapprover'=>$prev_approver['user_name']])]);

		$allow_approved=false;
		$notified_users = $this->getArrayNotifiedEmail($suggest_no, $role_prev_approve,false);
		if(!empty($notified_users)){
			foreach ($notified_users as $ind => $email) {
				if(strpos($ind, $user_role->name) !== false){
					$allow_approved=true;
					break;
				}
			}
			if(!$allow_approved)
			  return response()->json(['status'=>'error', 'error' => trans('dpl.alreadyapproved',['suggestno'=>$suggest_no,'lastapprover'=>$prev_approver['user_name']])]);
		}
		if ($action == 'Approve') {
			$notified_users = $this->getArrayNotifiedEmail($suggest_no, $user_role->name);
			if(!empty($notified_users)){
				if($user_role->name != 'FSM' && $user_role->name != 'HSM'){
					$data = [
						'title' => 'Permohonan Approval',
						'message' => 'Permohonan Approval #'.$suggest_no,
						'id' => $suggest_no,
						'href' => route('dpl.readNotifApproval'),
						'mail' => [
							'greeting'=>'Yang terhormat FSM/HSM Galenium',
							'content'=> "Bersama ini kami informasikan No. Pengajuan DPL #".$suggest_no." membutuhkan approval Anda.\n\nUntuk melihat detail pengajuan DPL, silakan login ke dalam sistem aplikasi gOrder (http://g-order.id) menggunakan email dan password Anda.\nTerima kasih."
						]
					];
				}
				else{
					$data = [
						'title' => 'Pengisian No. DPL',
						'message' => 'Pengisian No. DPL untuk #'.$suggest_no,
						'id' => $suggest_no,
						'href' => route('dpl.readNotifDPLInput'),
						'mail' => [
							'greeting'=>'',
							'content'=> ''
						]
					];
				}
				foreach ($notified_users as $key => $email) {
					if($user_role->name != 'FSM' && $user_role->name != 'HSM'){
						$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
							->update(array('approved_by' => Auth::user()->id, 'next_approver' => $key,'last_update_by'=>Auth::user()->id));
					}
					else{
						$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
							->update(array('approved_by' => Auth::user()->id, 'next_approver' => '','last_update_by'=>Auth::user()->id));
						$dplno = DPLNo::where('suggest_no','=',$suggest_no)->first();
						if(($user_role->name == 'FSM' or $user_role->name=='HSM') and ($dplno) )
						{
								$this->sendDPLNo($suggest_no,$dplno->dpl_no);
								return response()->json(['status'=>'success', 'error' => '']);;
								break;
						}
					}

					foreach ($email as $key2 => $mail) {
						$data['email'] = $mail;
						if($key == 'FSM_HSM'){
							if($user_role->name == 'Admin DPL')
								$data['sendmail'] = 1;
							else
								$data['sendmail'] = 0;
						}
						else
							$data['sendmail'] = 0;
						$apps_user = User::where('email',$mail)->first();
						if(!empty($apps_user))
							$apps_user->notify(new PushNotif($data));
					}
				}
			}
			$this->dplLog($suggest_no, $action);

		} else {
			$notified_users = $this->getArrayNotifiedEmail($suggest_no);
			if(!empty($notified_users)){
				$data = [
					'title' => 'Usulan Discount ditolak',
					'message' => 'Usulan Discount #'.$suggest_no.' ditolak oleh '.Auth::user()->name,
					'id' => $suggest_no,
					'href' => route('dpl.readNotifApproval'),
					'mail' => [
						'greeting'=>'',
						'content'=> ''
					]
				];
				foreach ($notified_users as $key => $email) {
					$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
						->update(array('approved_by' => '', 'next_approver' => '', 'fill_in' => 1,'last_update_by'=>Auth::user()->id));
					foreach ($email as $key2 => $mail) {
						$data['email'] = $mail;
						if($key == 'FSM_HSM')
							$data['sendmail'] = 0;
						else
							$data['sendmail'] = 0;
						$apps_user = User::where('email',$mail)->first();
						if(!empty($apps_user))
							$apps_user->notify(new PushNotif($data));
					}
				}
			}
			$reason = $request->reason_reject;
			$this->dplLog($suggest_no, $action, $reason);
		}
		return response()->json(['status'=>'success', 'error' => '']);
	}

	public function getArrayNotifiedEmail($suggest_no, $curr_pos = '',$vbreak=true){
		$positions = [];
		$dpl = DPLSuggestNo::select('dpl_suggest_no.*','email')
							->join('users','users.id','dpl_suggest_no.mr_id')
							->where('suggest_no', $suggest_no)
							->first();
		if($curr_pos == '' && $dpl)
			$positions['SPV'][] = $dpl['email'];
		if($vbreak && !empty($positions)) return $positions;
		$next_approver = OrgStructure::select('org_structure.*','email')
									->join('users','users.id','org_structure.directsup_user_id')
									->where('user_id', $dpl['mr_id'])
									->first();
		if(($curr_pos == '' || ($curr_pos != 'ASM' && $curr_pos != 'Admin DPL' && $curr_pos != 'FSM' && $curr_pos != 'HSM'))&&!empty($next_approver))
			$positions['ASM'][] = $next_approver['email'];
		if($vbreak && !empty($positions)) return $positions;

		$next_approver = User::whereHas('roles', function($q){
								    $q->where('name', 'Admin DPL');
								})->first();
		if($curr_pos == '' || ($curr_pos != 'Admin DPL' && $curr_pos != 'FSM' && $curr_pos != 'HSM'))
			$positions['Admin DPL'][] = $next_approver['email'];
		if($vbreak && !empty($positions)) return $positions;

		$next_approver = User::whereHas('roles', function($q){
								    $q->where('name', 'FSM');
								})->first();
		if($curr_pos == '' || ($curr_pos != 'FSM' && $curr_pos != 'HSM'))
			$positions['FSM_HSM'][] = $next_approver['email'];

		$next_approver = User::whereHas('roles', function($q){
								    $q->where('name', 'HSM');
								})->first();
		if($curr_pos == '' || ($curr_pos != 'FSM' && $curr_pos != 'HSM'))
			$positions['FSM_HSM'][] = $next_approver['email'];

		$fill_no_dpl = User::whereHas('roles', function($q){
								    $q->where('name', 'Admin DPL');
								})->first();
		if($curr_pos == 'FSM' || $curr_pos == 'HSM')
			$positions['Admin DPL'][] = $fill_no_dpl['email'];

		return $positions;
	}

	public function readNotifDiscount($suggest_no, $notifid){
		Auth::User()->notifications()
		           	->where('id','=',$notifid)
		            ->update(['read_at' => Carbon::now()]);
		Auth::User()->notifications()
		           	->whereraw("data like '%\"id\":\"".$suggest_no."\"%'")
								->wherenull('read_at')
		            ->update(['read_at' => Carbon::now()]);
		return redirect()->route('dpl.discountApproval',$suggest_no);
	}

	public function readNotifApproval($suggest_no, $notifid){
		Auth::User()->notifications()
		           	->where('id','=',$notifid)
		            ->update(['read_at' => Carbon::now()]);
		Auth::User()->notifications()
		           	->whereraw("data like '%\"id\":\"".$suggest_no."\"%'")
								->wherenull('read_at')
		            ->update(['read_at' => Carbon::now()]);

		return redirect()->route('dpl.discountForm',$suggest_no);
	}

	public function readNotifDPLInput($suggest_no, $notifid){
		Auth::User()->notifications()
		           	->where('id','=',$notifid)
		            ->update(['read_at' => Carbon::now()]);
		Auth::User()->notifications()
		           	->whereraw("data like '%\"id\":\"".$suggest_no."\"%'")
								->wherenull('read_at')
		            ->update(['read_at' => Carbon::now()]);
		return redirect()->route('dpl.dplNoForm',$suggest_no);
	}

	public function readNotifDPLCancel($suggest_no, $notifid){
		Auth::User()->notifications()
		           	->where('id','=',$notifid)
		            ->update(['read_at' => Carbon::now()]);

		return redirect()->route('dpl.discountForm',$suggest_no);
	}

	public function readNotifDPLCancelOutlet($suggest_no, $notifid){
		Auth::User()->notifications()
		           	->where('id','=',$notifid)
		            ->update(['read_at' => Carbon::now()]);

		return redirect()->route('order.listPO');
	}

	public function dplLog($suggest_no, $type, $reason = "") {
		$log = new DPLLog;
		$log->suggest_no = $suggest_no;
		$log->type = $type;
		if($reason)
			$log->reason = $reason;
		$log->done_by = Auth::user()->id;
		$log->save();
	}

	public function dplLogHistory($suggest_no) {
		$dpl = DPLLog::select('users.name','r.display_name as role','dpl_log.*')
			->join('users', 'users.id', 'dpl_log.done_by')
			->join('role_user as ru','ru.user_id','users.id')
			->join('roles as r','ru.role_id','r.id')
			->where('suggest_no', $suggest_no)
			->where('r.name','not like','Marketing%')
			->orderBy('created_at','desc')
			->get();

		return view('admin.dpl.dplHistory', array('dpl' => $dpl));
	}

	public function dplList(Request $request) {
		$dpl = DPLSuggestNo::select('mr.id as dpl_mr_id',
			'mr.name as dpl_mr_name',
			'org_structure.user_code as dpl_mr_code',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'distributor.id as dpl_distributor_id',
			'distributor.customer_name as dpl_distributor_name',
			'approver.id as dpl_appr_id',
			'approver.name as dpl_appr_name',
			'roles.name as dpl_appr_role',
			'dpl_suggest_no.suggest_no',
			'dpl_no.dpl_no',
			'dpl_suggest_no.notrx',
			'fill_in',
			'approved_by',
			'next_approver','active'
			,'so_headers.status'
			,'fv.name as status_po')
			->join('users as mr', 'mr.id', 'dpl_suggest_no.mr_id')
			->leftjoin('org_structure','org_structure.user_id','mr.id')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->leftJoin('users as approver', 'approver.id', 'dpl_suggest_no.approved_by')
			->leftjoin('role_user as ru','ru.user_id','approver.id')
			->leftjoin('roles','ru.role_id','roles.id')
			->leftJoin('dpl_no', 'dpl_no.suggest_no', 'dpl_suggest_no.suggest_no')
			->leftJoin('so_headers', 'so_headers.notrx', 'dpl_suggest_no.notrx')
			->leftjoin('flexvalue as fv',function($join){
				$join->on('so_headers.status','=','fv.id');
				$join->on('fv.master','=',DB::raw("'status_po'"));
			})
			->leftjoin('customers as distributor', 'distributor.id', 'so_headers.distributor_id')
			->where(function($query1){
				$query1->whereIn('roles.name',['SPV','ASM','HSM','FSM','Admin DPL'])
					->orwhereNull('roles.name');
			})
			//->where('active', 1)
			;


		$user_role = Auth::user()->roles->whereIn('name',['SPV','ASM','Admin DPL','FSM','HSM'])->first();
		$role = $user_role->name;

		if($role=="SPV" or $role=="ASM")
		{
				$dpl =$dpl->where(function($query){
						$query->where('dpl_suggest_no.mr_id','=',Auth::user()->id)
									->orWhereExists(function($query2){
											$query2->select(DB::raw(1))
			                      ->from('org_structure as os')
			                      ->whereRaw("os.user_id = dpl_suggest_no.mr_id and directsup_user_id = '".Auth::user()->id."'");
									});
				});
		}
		if(!empty($request->period))	{
			$dpl=$dpl->whereraw("date_format(dpl_suggest_no.created_at,'%b %Y')='".$request->period."'");
			$period = $request->period;
		}else{
			$dpl=$dpl->whereraw("date_format(dpl_suggest_no.created_at,'%b %Y')='".date('M Y')."'");
			$period = date('M Y');
		}
		//dd($dpl->toSQL());
		$dpl =$dpl->orderby('dpl_suggest_no.created_at','desc')->get();
		$dpl_show = array();
		foreach ($dpl as $key => $list) {
			$allowed = DB::select('SELECT privilegeSuggestNo(?,?) AS allowed', [$list->suggest_no, Auth::user()->id]);

			if(!$allowed)
				continue;

			$dpl[$key]->btn_discount = ($list->active && $list->fill_in && !empty($list->notrx)) ? '<a href="'.route('dpl.discountForm', $list->suggest_no) . '" class="btn btn-danger">'.\Lang::get('dpl.discount').'</a>' : '';

			$prev_approver = Role::join('role_user','role_user.role_id','roles.id')
									->where('role_user.user_id',$list->approved_by)
									->wherein('roles.name',['SPV','ASM','HSM','FSM','Admin DPL'])
									->first();

			$role_prev_approve = $prev_approver['name'];

			$notified_users = $this->getArrayNotifiedEmail($list->suggest_no, $role_prev_approve,false);
			if(!empty($notified_users)){
				foreach ($notified_users as $ind => $email) {
					if(strpos($ind, $role) !== false){
						$dpl[$key]->btn_confirm = ($list->active && !$list->fill_in && !empty($list->notrx) && !empty($list->next_approver) ) ? '<a href="'.route('dpl.discountApproval', $list->suggest_no) . '" class="btn btn-primary">'.\Lang::get('dpl.confirmation').'</a>' : '';
						break;
					}
				}
			}

			$dpl[$key]->btn_dpl_no = ($list->active && !$list->fill_in && !empty($list->notrx) && empty($list->next_approver) && $role == 'Admin DPL' && empty($list->dpl_no)) ? '<a href="'.route('dpl.dplNoForm', $list->suggest_no) . '" class="btn btn-warning">'.\Lang::get('dpl.dplNo').'</a>' : '';

			array_push($dpl_show, $dpl[$key]);
		}

		return view('admin.dpl.dplList', array('dpl' => $dpl_show,'period'=>$period));
	}

	public function dplreport(Request $request)
    {
      if ($request->method()=='GET')
      {
        return view('admin.dpl.dplReport');
      }
      else {
      	DB::enablequerylog();
        $datalist = DB::table('dpl_no')
                    ->join('so_headers as sh','dpl_no.dpl_no','sh.dpl_no')
                    ->join('customers as c','sh.customer_id','c.id')
                    ->join('so_lines_v as sl','sh.id','sl.header_id')
                    ->join('customers as dist','sh.distributor_id','dist.id')
                    ->join('dpl_suggest_no as dsn','dpl_no.suggest_no','dsn.suggest_no')
                    ->leftjoin('users as mr','dsn.mr_id','mr.id')
										->leftjoin('flexvalue as fl',function($join){
											$join->on('fl.id','=','sh.status');
											$join->on('fl.master','=',DB::raw("'status_po'"));
											})
                    ->leftjoin('so_shipping as ship',function($union){
                    	$union->on('ship.line_id','sl.line_id');
                    	$union->on('ship.header_id','sl.header_id');
                    })

                    ->select('dpl_no.dpl_no','dpl_no.created_at','c.customer_name'
                    		,'sl.title as nm_product','sl.qty_request_primary'
                    		,DB::raw("(sl.list_price/sl.conversion_qty) as hna")
                    		,'sl.amount as total'
                    		,'sl.bonus_gpl as gpl_bonus'
                    		,'sl.discount_gpl as gpl_discount'
                    		,'sl.discount as disc_distributor'
                    		,'dist.customer_name as distributor'
                    		,'dsn.mr_id'
                    		,'dsn.kowil_MR'
                    		,'mr.name as spv_name'
                    		,'ship.deliveryno'
                    		,'sl.header_id','sl.line_id'
												, 'fl.name as status'
						    ,DB::raw("ifnull(ship.qty_shipping,ship.qty_accept) as jml_kirim")
                            )->where('dsn.active','=',1);
					if(isset($request->trx_in_date))
          {
            $datalist=$datalist->whereraw("date_format(dpl_no.created_at,'%M %Y')='".$request->trx_in_date."'");
          }
          if(isset($request->dist_id))
          {
            $datalist=$datalist->where('sh.distributor_id','=',$request->dist_id);
          }
					if(isset($request->spv_id))
          {
						//$spv = User::where('id','=',$request->spv_id)->select('id','name')->first();
            $datalist=$datalist->whereRaw("privilegeSuggestNo(dpl_no.suggest_no,'".$request->spv_id."')=1");
          }
					if(isset($request->asm_id))
          {
						//$asm = User::where('id','=',$request->asm_id)->select('id','name')->first();
            $datalist=$datalist->whereRaw("privilegeSuggestNo(dpl_no.suggest_no,'".$request->asm_id."')=1");
          }
					if(Auth::user()->hasRole('SPV') or Auth::user()->hasRole('ASM'))
					{
							$datalist =$datalist->where(function($query){
									$query->where('dsn.mr_id','=',Auth::user()->id)
												->orWhereExists(function($query2){
														$query2->select(DB::raw(1))
						                      ->from('org_structure as os')
						                      ->whereRaw("os.user_id = dsn.mr_id and directsup_user_id = '".Auth::user()->id."'");
												});
							});
					}
			  $datalist =$datalist->orderBy('dpl_no','asc')->orderBy('title','asc')->orderBy('deliveryno','asc')->get();
				$datalist =$datalist->groupBy('dpl_no');
				//dd($datalist);
			  //DD(DB::getquerylog());

			  //return view ('admin.dpl.reportdownload',compact('datalist','request'));
				//if($datalist->count()>0){
	        Excel::create('Order-'.Carbon::now(), function($excel) use($datalist,$request) {
	            $excel->sheet('order', function($sheet) use($datalist,$request) {
	                $sheet->loadView('admin.dpl.reportdownload',array('datalist'=>$datalist,'request'=>$request));
	                 $sheet->setWidth(array(
																		'D'     =>  50,
																		'F'     =>  50,
																		'H'     =>  15,
																		'I'     =>  15,
																		'M'     =>  50,
																		'N'     =>  10,
																		'O'			=>  30,
	                                ));
	                $sheet->getStyle('D','F','M')->getAlignment()->setWrapText(true);
									/*$sheet->row(6, function($row) { $row->setBackground('#CCCCCC'); });
									$sheet->row(7, function($row) { $row->setBackground('#CCCCCC'); });*/
	            });
	        })->export('xlsx');
				//}

      }
    }

	public function dplNoInputForm($suggest_no) {
		$user_role = Auth::user()->roles->whereIn('name','Admin DPL')->first();

		if(!$user_role){
			return view('errors.403');
		}
		$dpl = DPLSuggestNo::select('users.id as dpl_mr_id',
			'users.name as dpl_mr_name',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'sh.distributor_id as dpl_distributor_id',
			'distributor.customer_name as dpl_distributor_name',
			'dpl_suggest_no.suggest_no',
			'dpl_suggest_no.note',
			'dpl_no.dpl_no')
			->join('users', 'users.id', 'dpl_suggest_no.mr_id')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->join('so_headers as sh', 'sh.notrx', 'dpl_suggest_no.notrx')
			->join('customers as distributor', 'distributor.id', 'sh.distributor_id')
			->leftjoin('dpl_no', 'dpl_no.suggest_no', 'dpl_suggest_no.suggest_no')
			->where('dpl_suggest_no.suggest_no', $suggest_no)
			->where('active', 1)
			->first();

		if (empty($dpl->dpl_no)) {
			$max_dpl_no = DPLNo::max('dpl_no');
			$readonly = '';

			if ($max_dpl_no) {
				$max_no = intval(substr($max_dpl_no, 6));
				$dpl_no = date('ym') . str_pad($max_no + 1, 4, 0, STR_PAD_LEFT);
			} else {
				$dpl_no = date('ym') . str_pad(1, 4, 0, STR_PAD_LEFT);
			}
		} else {
			$dpl_no = $dpl->dpl_no;
			$readonly = 'readonly';
		}

		return view('admin.dpl.dplNoForm', array('dpl' => $dpl, 'readonly' => $readonly, 'dpl_no' => $dpl_no));
	}

	public function dplNoInputSet(Request $request) {
		$suggest_no = $request->suggest_no;
		$dpl_no = $request->dpl_no;

		$this->dplLog($suggest_no, 'Input DPL No #' . $dpl_no);

		$check_dpl = DPLNo::where('dpl_no', $dpl_no)->first();
		if ($check_dpl) {
			if(!is_null($check_dpl->suggest_no))
			{
				return redirect()->back()->withInput()->with('msg', \Lang::get('dpl.dplNo').' #' . $dpl_no . ' '.\Lang::get('dpl.alreadyExist').'.');
			}else{
				$check_dpl->suggest_no = $suggest_no;
				$check_dpl->save();
				$dpl = $check_dpl;
			}
		}
		if (!$check_dpl) {
			$dpl = new DPLNo;
			$dpl->dpl_no = $dpl_no;
			$dpl->suggest_no = $suggest_no;
			$dpl->save();
		}
			$this->sendDPLNo($suggest_no,$dpl_no);

			return redirect()->route('dpl.list');

	}

	public function sendDPLNo($suggest_no,$dpl_no)
	{
		$so_header = SoHeader::where('suggest_no',$suggest_no)->first();
		$so_header->dpl_no = $dpl_no;
		$so_header->status = 0;
		$so_header->save();

		$solines = SoLine::where('header_id','=',$so_header->id)
							->where(function($query1){
									$query1->whereNotNull('discount')
													->orWhereNotNull('discount_gpl')
													->orWhereNotNull('bonus_gpl');
							})
							->get();
		foreach($solines as $soline){
			if(floatval($soline->discount)+floatval($soline->discount_gpl)!=0){
					$discount = floatval($soline->discount)+floatval($soline->discount_gpl);
					$unitprice = $soline->list_price * (100-$discount)/100;
					$soline->unit_price = $unitprice;
					$soline->amount=$soline->qty_request*$unitprice;
					if($soline->tax_type=="10%")
					{
						$soline->tax_amount =0.1*$soline->amount;
					}
					$soline->save();
			}
		}

		$distributor = Customer::where('id','=',$so_header['distributor_id'])->first();

		$data = [
			'title' => 'New PO DPL',
			'message' => 'New PO '.$so_header->notrx.' dg DPL#'.$dpl_no,
			'id' => $so_header['id'],
			'href' => route('order.notifnewpo'),
			'mail' => [
				'greeting'=>'',
				'content'=> ''
			],
			'sendmail' => 0,
			'email'=> $distributor->user->email
		];

		$apps_user = User::where('email',$distributor->user->email)->first();
		if(!empty($apps_user))
			$apps_user->notify(new PushNotif($data));
			return true;
	}

	public function suggestNoCancel(Request $request){
		DB::beginTransaction();
		try{
			$suggest_no = $request->suggest_no;
			$customer_po = $request->customer_po;
			$note = $request->note;

			$soheader = SoHeader::where('suggest_no',$suggest_no)->first();
			$soheader->status=-98;
			if(isset($soheader->dpl_no))
				$deldplno = DPLNo::where('dpl_no','=',$soheader->dpl_no)
										->where('suggest_no','=',$suggest_no)
										->delete();
			$soheader->save();
			$this->dplLog($suggest_no, 'Batalkan Pengajuan DPL #' . $suggest_no,$note);
			$update = DPLSuggestNo::where('suggest_no',$suggest_no)
								->update(array('note'=>$note,'active'=>0,'last_update_by'=>Auth::user()->id));
			// Notif to outlet
			$dpl = DPLSuggestNo::select('users.email','note')
								->join('users','users.customer_id','dpl_suggest_no.outlet_id')
								->where('suggest_no',$suggest_no)
								->first();

			$data = [
				'title' => 'Pembatalan Pengajuan DPL',
				'message' => 'Pembatalan Pengajuan DPL #'.$suggest_no,
				'id' => $soheader->id,
				'href' => route('order.notifnewpo'),
				'mail' => [
					'greeting'=>'Pembatalan Pengajuan DPL #'.$suggest_no,
					'content'=> "Pengajuan no DPL #".$suggest_no." untuk PO #".$customer_po." anda telah dibatalkan oleh marketing PT.Galenium Pharmasaia Laboratories dengan alasan\n\n".$note
				],
				'sendmail' => 1,
				'email' => $dpl['email']
			];
			$apps_user = User::where('email',$dpl['email'])->first();
			if(!empty($apps_user))
				$apps_user->notify(new PushNotif($data));

			// Notif to Galenium
			$notified_users = $this->getArrayNotifiedEmail($suggest_no);
			if(!empty($notified_users)){
				$data = [
					'title' => 'Pembatalan Pengajuan DPL',
					'message' => 'Pembatalan Pengajuan DPL #'.$suggest_no,
					'id' => $suggest_no,
					'href' => route('dpl.readNotifDPLCancel'),
					'mail' => [
						'greeting'=>'',
						'content'=> ''
					],
					'sendmail' => 0
				];
				foreach ($notified_users as $key => $email) {
					foreach ($email as $key2 => $mail) {
						$data['email'] = $mail;
						$apps_user = User::where('email',$mail)->first();
						if(!empty($apps_user))
							$apps_user->notify(new PushNotif($data));
					}
				}
			}
			DB::commit();
			return redirect()->route('dpl.list');
		}catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}
	}

	public function dplDiscountSplit(Request $request){
		$distributor = $request->distributor;
		$notrx = $request->notrx;
		$so_header = SoHeader::where('notrx', $notrx)->first();
		if($request->Save)
		{
			if($so_header->distributor_id!=$request->distributor)
			{
				DB::beginTransaction();
	      try{
					$suggest_no=$request->suggest_no;
					SoLine::where('header_id','=',$so_header->id)
					->update(['qty_confirm'=>null,'unit_price'=>DB::raw('list_price'),'last_update_by'=>Auth::user()->id]);
					$this->dpllog($suggest_no,'Change Distributor');
					/*$updateDPL = DPLNo::where('dpl_no',$so_header->dpl_no)
																			->update(array('suggest_no'=>null));
					$so_header->dpl_no=null;
					$so_header->save();*/
					DB::commit();
				}catch (\Exception $e) {
		      DB::rollback();
		      throw $e;
		    }
			}
			$this->discountSet($request);
			return redirect('/dpl/list');
		}elseif($request->Split)
		{
			if(is_null($request->lineid))
			{
					return redirect()->back()->withError(trans('pesan.nosplitline'))->withInput();
			}

			if($so_header){
					$so_lines = SoLine::where('header_id','=',$so_header->id)
											->whereNotIn('line_id',$request->lineid)
											->get();

				 if($so_lines->isEmpty())	{
					 return redirect()->back()->withError(trans('pesan.errsplitall'))->withInput();
				 }else{
					 DB::beginTransaction();
	 	      try{
					 $this->generateExec($request);
					 $suggest_no = \Session::get('suggest_no');
					 if($suggest_no){
						 $thn =substr($notrx,3,4);
			       $bln=substr($notrx,7,2);
			       $tmpnotrx = DB::table('tmp_notrx')->where([
			           ['tahun','=',$thn],
			           ['bulan','=',$bln],
			       ])->select('lastnum')->first();
			       if(!$tmpnotrx)
			       {
			           $tmpnotrx = 0;
			       }else{
			           $tmpnotrx = (int)$tmpnotrx->lastnum;
			       }
			       $lastnumber=$tmpnotrx+1;
						 $newnotrx = 'PO-'.date('Ymd').'-'.app('App\Http\Controllers\ProductController')->getRomanNumerals(date('m')).'-'.str_pad($lastnumber,5,'0',STR_PAD_LEFT) ;
						 echo "insert soheader:".$suggest_no;
						 $newheader = SoHeader::Create([
							 	'customer_id'=>$so_header->customer_id,
								'distributor_id' =>$distributor,
								'cust_ship_to' =>$so_header->cust_ship_to,
								'cust_bill_to' =>$so_header->cust_bill_to,
								'customer_po' =>$so_header->customer_po."_copy1",
								'file_po' =>$so_header->file_po,
								'approve'=>$so_header->approve,
								'tgl_order'=>$so_header->tgl_order,
								'currency'=>$so_header->currency,
								'oracle_ship_to'=>$so_header->oracle_ship_to,
								'oracle_bill_to'=>$so_header->oracle_bill_to,
								'oracle_customer_id'=>$so_header->oracle_customer_id,
								'notrx'=>$newnotrx,
								'status'=>-99,
								'org_id'=>$so_header->org_id,
								'warehouse'=>$so_header->warehouse,
								'suggest_no'=>$suggest_no,
								'split_from_id'=>$so_header->id
						 ]);
						 if($tmpnotrx==0){
		           DB::table('tmp_notrx')->insert(
		               ['tahun' => $thn,'bulan'=>$bln,'lastnum'=>$lastnumber]
		           );
		         }else{
		           DB::table('tmp_notrx')->where([
		               ['tahun','=',  $thn],
		               ['bulan','=',$bln]
		             ])
		             ->update(
		               ['lastnum'=>$lastnumber]
		           );
		         }
						 /*Update soline with new header_id*/
						 if($so_header->distributor_id!=$request->distributor){
							 $newlines = SoLine::where('header_id','=',$so_header->id)
 	 	 						->whereIn('line_id',$request->lineid)
 								->update(['header_id'=>$newheader->id,'qty_confirm'=>null,'last_update_by'=>Auth::user()->id]);
						 }else{
							 $newlines = SoLine::where('header_id','=',$so_header->id)
 	 	 						->whereIn('line_id',$request->lineid)
 								->update(['header_id'=>$newheader->id,'last_update_by'=>Auth::user()->id]);
						 }
						 //dd("suggestno:" $suggest_no);
						 $oldsuggestno = dplSuggestNo::where('suggest_no','=',$request->suggest_no)
						 										->first();
							if($oldsuggestno){
							$updateDPL = DPLSuggestNo::where('suggest_no',$suggest_no)
			                                    ->update(array('notrx'=>$newnotrx,'mr_id'=>$oldsuggestno->mr_id,'kowil_mr'=>$oldsuggestno->kowil_mr,'file_sp'=>$oldsuggestno->file_sp,'last_update_by'=>Auth::user()->id));
							}else{
								$updateDPL = DPLSuggestNo::where('suggest_no',$suggest_no)
				                                    ->update(array('notrx'=>$newnotrx,'last_update_by'=>Auth::user()->id));
							}
							$this->dplLog($suggest_no, 'Split From Trx #' . $notrx);
			          $notified_users = $this->getArrayNotifiedEmail($suggest_no,'');
							//	dd($notified_users);
			    			if(!empty($notified_users)){
			    				$data = [
			    					'title' => 'Pengajuan DPL',
			    					'message' => 'Pengajuan DPL #'.$suggest_no,
			    					'id' => $suggest_no,
			    					'href' => route('dpl.readNotifApproval'),
			    					'mail' => [
			    						'greeting'=>'Split From Trx #' . $notrx,
			    						'content'=> 'Bersama ini kami informasikan pengajuan DPL baru dari nomor trx #'.$notrx
			    					],
			    					'sendmail' => 0
			    				];
			    				foreach ($notified_users as $key => $email) {
			    					foreach ($email as $key2 => $mail) {
			                $data['email'] = $mail;
			    						$apps_user = User::where('email',$mail)->first();
			                if(!empty($apps_user))
			    						  $apps_user->notify(new PushNotif($data));
			    					}
			    				}
			    			}
								DB::commit();
								return redirect('/dpl/list');
					 }
					}catch (\Exception $e) {
						 DB::rollback();
						 throw $e;
					}
				 }
			}

		}

	}


	public function getListSpvAsm(Request $request,$posisi='SPV'){
		$id = $request->input('id');
			$data = DB::table('users as u')
					->join('role_user as ru','u.id','=','ru.user_id')
					->join('roles as r','ru.role_id','=','r.id')
					//->where('r.name','=',$posisi)
					->where('u.register_flag','=',1);

			if	(Auth::check())
			{
				if($posisi=="SPV" and !is_null($id)){
					$data=$data->WhereExists(function($query2) use($id){
							$query2->select(DB::raw(1))
										->from('org_structure as os')
										->whereRaw("os.user_id = u.id and directsup_user_id = '".$id."'");
									});
				}elseif(Auth::user()->hasRole($posisi) and ($posisi=="ASM" or $posisi="SPV"))
				{
					$data=$data->where('r.name','=',$posisi)->where('u.id','=',Auth::user()->id);
				}elseif($posisi=="SPV" and Auth::user()->hasRole('ASM'))
				{
					$data=$data->where('r.name','=',$posisi)->WhereExists(function($query2){
							$query2->select(DB::raw(1))
										->from('org_structure as os')
										->whereRaw("os.user_id = u.id and directsup_user_id = '".Auth::user()->id."'");
									});
				}elseif($posisi=="ASM" and Auth::user()->hasRole('SPV'))
				{
					$data=$data->where('r.name','=',$posisi)->WhereExists(function($query2){
							$query2->select(DB::raw(1))
										->from('org_structure as os')
										->whereRaw("os.directsup_user_id = u.id and os.user_id = '".Auth::user()->id."'");
									});

				}
			}
			$data=$data->select('u.id','u.name','u.email')->orderBy('u.name','asc')->get();
			return response()->json($data);;
	}

}
