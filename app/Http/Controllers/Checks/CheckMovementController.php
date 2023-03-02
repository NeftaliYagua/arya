<?php

namespace App\Http\Controllers\Checks;

use App\Client;
use App\Branch;
use App\HeaderVoucher;
use App\DetailVoucher;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserAccess\UserAccessController;
use App\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckMovementController extends Controller
{
 
    public $userAccess;
    public $modulo = 'Reportes';

    public function __construct(){

        $this->middleware('auth');
        $this->userAccess = new UserAccessController();
    }

    /** Actual month first day **/
    public function data_first_month_day() {
        $month = date('m');
        $year = date('Y');
        $dia = date('1');
        return date('Y-m-').'01';
    }
    

   public function index()
   {
        if($this->userAccess->validate_user_access($this->modulo)){
            $user= auth()->user();

            $details = DetailVoucher::on(Auth::user()->database_name)->where('status','C')
                                                                    ->select('id_header_voucher',DB::raw('SUM(debe) As debe'),DB::raw('SUM(haber) As haber'))
                                                                    ->groupBy('id_header_voucher')->get();
          
            $details = $details->filter(function($detail)
            {
                if($detail->debe <> $detail->haber){
                    return $detail;
                    
                }
                
            });

            return view('admin.check_movements.index',compact('details'));
            
        }else{
            return redirect('/home')->withDanger('No tiene Acceso al modulo de '.$this->modulo);
        }
   }



   public function comprobanteschk()
   {
        if($this->userAccess->validate_user_access($this->modulo)){
            $user= auth()->user();
            
            $date = Carbon::now();
            $date_begin = $this->data_first_month_day();
            $date_end = Carbon::parse($date)->format('d-m-Y');

            $debe = 0;
            $haber = 0;
            $cuenta = '';
            $a_headers[] = array('0','0',0,0);

            $com_headers = DB::connection(Auth::user()->database_name)->table('header_vouchers')
            ->where('date','>=',$date_begin)
            ->where('date','<=',$date_end)
            ->whereIn('status',['C',1])
            ->get();

            foreach ($com_headers as $headers) {
                
                $suma_mov = '';
                $debe = 0;
                $haber = 0;
                

                $suma_mov = DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                ->where('id_header_voucher',$headers->id)
                ->where('status','C')
                ->select('id_header_voucher',DB::raw('SUM(debe) As debe'),DB::raw('SUM(haber) As haber'))
                ->groupBy('id_header_voucher')
                ->first();


                if (empty($suma_mov)) {
                    $debe = 0;
                    $haber = 0;

                } else {

                    $debe = $suma_mov->debe;
                    $haber = $suma_mov->haber;
                }


              //  if ($debe <> $haber){
                    $a_headers[] = array($headers->id,$headers->date,$debe,$haber);
                //}

            } 

    
            return view('admin.check_movements.comprobanteschk',compact('a_headers','date_begin','date_end'));
            
        }else{
            return redirect('/home')->withDanger('No tiene Acceso al modulo de '.$this->modulo);
        }
   }


 public function comprobanteschks(request $request)
   {
        if($this->userAccess->validate_user_access($this->modulo)){
            $user= auth()->user();
            
            $date_begin = request('date_begin');
            $date_end = request('date_end');

            $debe = 0;
            $haber = 0;
            $cuenta = '';
            $a_headers[] = array('0','0',0,0);

            $com_headers = DB::connection(Auth::user()->database_name)->table('header_vouchers')
            ->where('date','>=',$date_begin)
            ->where('date','<=',$date_end)
            ->whereIn('status',['C',1])
            ->get();

            foreach ($com_headers as $headers) {
                
                $suma_mov = '';
                $debe = 0;
                $haber = 0;
                

                $suma_mov = DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                ->where('id_header_voucher',$headers->id)
                ->where('status','C')
                ->select('id_header_voucher',DB::raw('SUM(debe) As debe'),DB::raw('SUM(haber) As haber'))
                ->groupBy('id_header_voucher')
                ->first();


                if (empty($suma_mov)) {
                    $debe = 0;
                    $haber = 0;

                } else {

                    $debe = $suma_mov->debe;
                    $haber = $suma_mov->haber;
                }


              //  if ($debe <> $haber){
                    $a_headers[] = array($headers->id,$headers->date,$debe,$haber);
                //}

            } 


            return view('admin.check_movements.comprobanteschk',compact('a_headers','date_begin','date_end'));
        
   }

  
  }
}
