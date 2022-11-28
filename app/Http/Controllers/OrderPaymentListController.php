<?php

namespace App\Http\Controllers;

use App;
use App\BankMovement;
use Illuminate\Http\Request;


use App\Account;
use App\Client;
use App\Company;
use App\DetailVoucher;
use App\HeaderVoucher;
use App\Provider;
use App\Segment;
use App\Subsegment;
use App\UnitOfMeasure;
use App\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class OrderPaymentListController extends Controller
{
 
    public function __construct(){

        $this->middleware('auth');
        $this->middleware('valiuser')->only('indexmovement');
        $this->middleware('valimodulo:Ordenes de Pago');
       }

   public function index()
   {

 
       $user       =   auth()->user();
       $users_role =   $user->role_id;
       if($users_role == '1'){
        
        $accounts = $this->calculation('bolivares');
        $accounts_USD = $this->calculation('dolares');

        
        }

       return view('admin.bankmovements.index',compact('accounts','accounts_USD'));
   }

   public function indexmovement(request $request)
   {

    $agregarmiddleware = $request->get('agregarmiddleware');
    $actualizarmiddleware = $request->get('actualizarmiddleware');
    $eliminarmiddleware = $request->get('eliminarmiddleware');

            $detailvouchers = DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                                ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                                ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                                ->where('header_vouchers.status','LIKE','1')
                                ->where(function ($query) {
                                    $query->where('header_vouchers.description','LIKE','Orden de Pago%');
                                })
                                
                                ->select('detail_vouchers.*','header_vouchers.description as header_description', 
                                'header_vouchers.reference as header_reference','header_vouchers.date as header_date',
                                'accounts.description as account_description','accounts.code_one as account_code_one',
                                'accounts.code_two as account_code_two','accounts.code_three as account_code_three',
                                'accounts.code_four as account_code_four','accounts.code_five as account_code_five')
                                ->orderBy('header_vouchers.id','desc')
                                ->orderBy('detail_vouchers.debe','desc')
                                ->get();

            //dd($detailvouchers);

            $accounts     = Account::on(Auth::user()->database_name)->orderBy('description','asc')->get();

            $date = Carbon::now();
            $datenow = $date->format('Y-m-d'); 

            return view('admin.bankmovements.indexorderpayment',compact('eliminarmiddleware','actualizarmiddleware','agregarmiddleware','detailvouchers','accounts','datenow'));

      
   } 

  

   public function pdfAccountOrdenDePago(Request $request)
   {
       $date_begin = request('date_begin');
       $date_end = request('date_end');

       $date = Carbon::now();
       $datenow = $date->format('d-m-Y');

       $pdf = App::make('dompdf.wrapper');

       $id_account = request('id_account');

       $coin = request('coin');
       
       $company = Company::on(Auth::user()->database_name)->find(1);

       
       if(isset($id_account)){
            if(isset($coin) && $coin != 'Bolivares'){
                $detailvouchers =  DB::connection(Auth::user()->database_name)->table('header_vouchers')
                ->join('detail_vouchers', 'detail_vouchers.id_header_voucher', '=', 'header_vouchers.id')
                ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                ->whereBetween('header_vouchers.date', [$date_begin, $date_end])
                ->whereIn('header_vouchers.id', function($query) use ($id_account){
                    $query->select('id_header_voucher')
                    ->from('detail_vouchers')
                    ->where('id_account',$id_account)
                    ->where('header_vouchers.description','LIKE','Orden de Pago%');
                })
                ->whereIn('detail_vouchers.status', ['F','C'])
                ->select('detail_vouchers.*','header_vouchers.*'
                ,'accounts.description as account_description'
                ,'header_vouchers.id as id_header'
                ,'header_vouchers.description as header_description'
                ,DB::raw('(detail_vouchers.debe / detail_vouchers.tasa) as debe')
                ,DB::raw('(detail_vouchers.haber / detail_vouchers.tasa) as haber'))->get();
            }else{
                $detailvouchers =  DB::connection(Auth::user()->database_name)->table('header_vouchers')
                ->join('detail_vouchers', 'detail_vouchers.id_header_voucher', '=', 'header_vouchers.id')
                ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                ->whereBetween('header_vouchers.date', [$date_begin, $date_end])
                ->whereIn('header_vouchers.id', function($query) use ($id_account){
                    $query->select('id_header_voucher')
                    ->from('detail_vouchers')
                    ->where('id_account',$id_account)
                    ->where('header_vouchers.description','LIKE','Orden de Pago%');
                })
                ->whereIn('detail_vouchers.status', ['F','C'])
                ->select('detail_vouchers.*','header_vouchers.*'
                ,'accounts.description as account_description'
                ,'header_vouchers.id as id_header'
                ,'header_vouchers.description as header_description')->get();
            }
       }else{
            if(isset($coin) && $coin != 'Bolivares'){
                $detailvouchers =  DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                ->whereBetween('header_vouchers.date', [$date_begin, $date_end])
                ->where(function ($query) {
                    $query->where('header_vouchers.description','LIKE','Orden de Pago%');
                    })
                ->whereIn('detail_vouchers.status', ['F','C'])
                ->select('detail_vouchers.*','header_vouchers.*'
                ,'accounts.description as account_description'
                ,'header_vouchers.id as id_header'
                ,'header_vouchers.description as header_description' 
                ,DB::raw('(detail_vouchers.debe / detail_vouchers.tasa) as debe')
                ,DB::raw('(detail_vouchers.haber / detail_vouchers.tasa) as haber'))->get();
            }else{
                $detailvouchers =  DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                ->whereBetween('header_vouchers.date', [$date_begin, $date_end])
                ->where(function ($query) {
                    $query->where('header_vouchers.description','LIKE','Orden de Pago%');
                    })
                ->whereIn('detail_vouchers.status', ['F','C'])
                ->select('detail_vouchers.*','header_vouchers.*'
                ,'accounts.description as account_description'
                ,'header_vouchers.id as id_header'
                ,'header_vouchers.description as header_description')->get();
            }
       }
       
       $date_begin = Carbon::parse($date_begin)->format('d-m-Y');

       $date_end = Carbon::parse($date_end)->format('d-m-Y');

       $titlePDF = 'Ordenes de Pago';

       $pdf = $pdf->loadView('admin.reports.journal_book',compact('company','detailvouchers'
                               ,'datenow','date_begin','date_end','titlePDF'));
       return $pdf->stream();
   }

   
   public function orderPaymentPdf(Request $request)
   {
       
       $pdf = App::make('dompdf.wrapper');

       $date = Carbon::now();
       $datenow = $date->format('Y-m-d');    
       $id_account = $request->id_account;
      
       if(isset($request->id_account)){
            $movements = DetailVoucher::on(Auth::user()->database_name)
                ->join('header_vouchers','header_vouchers.id','detail_vouchers.id_header_voucher')
                ->join('accounts','accounts.id','detail_vouchers.id_account')
                ->leftJoin('payment_orders','payment_orders.id','header_vouchers.id_payment_order')
                ->leftJoin('clients','clients.id','payment_orders.id_client')
                ->leftJoin('providers','providers.id','payment_orders.id_provider')
                ->whereBetween('header_vouchers.date', [$request->date_begin, $request->date_end])
                ->whereIn('header_vouchers.id', function($query) use ($id_account){
                    $query->select('id_header_voucher')
                    ->from('detail_vouchers')
                    ->where('id_account',$id_account)
                    ->where('header_vouchers.description','LIKE','Orden de Pago%');
                })
                ->where('detail_vouchers.status','C')
                ->select('header_vouchers.description', 'header_vouchers.id as header_id',
                'detail_vouchers.debe', 'detail_vouchers.haber', 'detail_vouchers.haber', 'detail_vouchers.tasa',
                'accounts.code_one','accounts.code_two','accounts.code_three','accounts.code_four','accounts.code_five','accounts.description as account_description'
                ,'clients.name as client_name','providers.razon_social as provider_name'
                ,'providers.code_provider as code_provider'
                ,'payment_orders.reference reference_order','payment_orders.date as date_order'
                ,'payment_orders.id as id_order')
                ->get();
       }else{
          
            $movements = DetailVoucher::on(Auth::user()->database_name)
                ->join('header_vouchers','header_vouchers.id','detail_vouchers.id_header_voucher')
                ->join('accounts','accounts.id','detail_vouchers.id_account')
                ->leftJoin('payment_orders','payment_orders.id','header_vouchers.id_payment_order')
                ->leftJoin('clients','clients.id','payment_orders.id_client')
                ->leftJoin('providers','providers.id','payment_orders.id_provider')
                ->whereBetween('header_vouchers.date', [$request->date_begin, $request->date_end])
                ->where('header_vouchers.description','LIKE','Orden de Pago%')
                ->where('detail_vouchers.status','C')
                ->select('header_vouchers.description', 'header_vouchers.id as header_id',
                'detail_vouchers.debe', 'detail_vouchers.haber', 'detail_vouchers.haber', 'detail_vouchers.tasa',
                'accounts.code_one','accounts.code_two','accounts.code_three','accounts.code_four','accounts.code_five','accounts.description as account_description'
                ,'clients.name as client_name','providers.razon_social as provider_name'
                ,'providers.code_provider as code_provider'
                ,'payment_orders.reference as reference_order','payment_orders.date as date_order'
                ,'payment_orders.id as id_order')
                ->get();
        }

       
       $pdf = $pdf->loadView('admin.bankmovements.reports.order_payment_pdf',compact('movements','datenow'));
       return $pdf->stream();
                
   }


   public function orderPaymentPdfDetail($id_header_voucher)
   {
       
       $pdf = App::make('dompdf.wrapper');

       $date = Carbon::now();
       $datenow = $date->format('Y-m-d');    
      
        $movements = DetailVoucher::on(Auth::user()->database_name)
            ->join('header_vouchers','header_vouchers.id','detail_vouchers.id_header_voucher')
            ->join('accounts','accounts.id','detail_vouchers.id_account')
            ->leftJoin('payment_orders','payment_orders.id','header_vouchers.id_payment_order')
            ->leftJoin('clients','clients.id','payment_orders.id_client')
            ->leftJoin('providers','providers.id','payment_orders.id_provider')
            ->where('header_vouchers.id',$id_header_voucher)
            ->where('detail_vouchers.status','C')
            ->select('header_vouchers.description', 'header_vouchers.id as header_id',
            'detail_vouchers.debe', 'detail_vouchers.haber', 'detail_vouchers.haber', 'detail_vouchers.tasa',
            'accounts.code_one','accounts.code_two','accounts.code_three','accounts.code_four','accounts.code_five','accounts.description as account_description'
            ,'clients.name as client_name','providers.razon_social as provider_name'
            ,'providers.code_provider as code_provider'
            ,'payment_orders.reference as reference_order','payment_orders.date as date_order'
            ,'payment_orders.id as id_order')
            ->orderBY('detail_vouchers.debe','desc')
            ->get();
        
       
       $pdf = $pdf->loadView('admin.bankmovements.reports.order_payment_pdf',compact('movements','datenow'));
       return $pdf->stream();
                
   }

   /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function createdeposit($id)
   {
    
   
        $account = Account::on(Auth::user()->database_name)->find($id);

      
        if(isset($account)){   
            
            $contrapartidas     = Account::on(Auth::user()->database_name)->where('code_one', '<>',0)
                                            ->where('code_two', '<>',0)
                                            ->where('code_three', '<>',0)
                                            ->where('code_four', '<>',0)
                                            ->where('code_five', '=',0)
                                        ->orderBY('description','asc')->pluck('description','id')->toArray();
            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');  
            
            $company = Company::on(Auth::user()->database_name)->find(1);
            $global = new GlobalController();

            //Si la taza es automatica
            if($company->tiporate_id == 1){
                $bcv = $global->search_bcv();
            }else{
                //si la tasa es fija
                $bcv = $company->rate;
            }

            return view('admin.bankmovements.createdeposit',compact('bcv','account','datenow','contrapartidas'));

        }else{
            return redirect('/bankmovements')->withDanger('No existe la Cuenta!');
       }
   }

   public function createretirement($id)
   {
        $account = Account::on(Auth::user()->database_name)->find($id);

        if(isset($account)){   

            $contrapartidas     = Account::on(Auth::user()->database_name)->where('code_one', '<>',0)
                                            ->where('code_two', '<>',0)
                                            ->where('code_three', '<>',0)
                                            ->where('code_four', '<>',0)
                                            ->where('code_five', '=',0)
                                        ->orderBY('description','asc')->pluck('description','id')->toArray();
            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');  
            
            $company = Company::on(Auth::user()->database_name)->find(1);
            $global = new GlobalController();

            //Si la taza es automatica
            if($company->tiporate_id == 1){
                $bcv = $global->search_bcv();
            }else{
                //si la tasa es fija
                $bcv = $company->rate;
            }

            return view('admin.bankmovements.createretirement',compact('bcv','account','datenow','contrapartidas'));

        }else{
            return redirect('/bankmovements')->withDanger('No existe la Cuenta!');
       }
   }

   public function createtransfer($id)
   {
        $account = Account::on(Auth::user()->database_name)->find($id);

        if(isset($account)){   

            $counterparts     =     DB::connection(Auth::user()->database_name)->table('accounts')->where('code_one', 1)
                                        ->where('code_two', 1)
                                        ->where('code_three', 1)
                                        ->whereIn('code_four', [1,2])
                                        ->where('code_five','<>',0)
                                        ->orderBY('description','asc')
                                        ->get();
            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');  
            
            $company = Company::on(Auth::user()->database_name)->find(1);
            $global = new GlobalController();
            
            //Si la taza es automatica
            if($company->tiporate_id == 1){
                $bcv = $global->search_bcv();
            }else{
                //si la tasa es fija
                $bcv = $company->rate;
            }

            return view('admin.bankmovements.createtransfer',compact('bcv','account','datenow','counterparts'));

        }else{
            return redirect('/bankmovements')->withDanger('No existe la Cuenta!');
       }
   }

   
    public function store(Request $request)
    {
        //DEPOSITOS

        $data = request()->validate([
            
        
            'id_account'        =>'required',
            'Subcontrapartida'  =>'required',
            'user_id'           =>'required',
            'amount'            =>'required',
            'rate'            =>'required',
            'coin'            =>'required',
            'date'              =>'required',
        
        
        ]);
        
        $account = request('id_account');
        $contrapartida = request('Subcontrapartida');
        $coin = request('coin');

        if($account != $contrapartida){

            $amount = str_replace(',', '.', str_replace('.', '', request('amount')));
            $rate = str_replace(',', '.', str_replace('.', '', request('rate')));

            if($coin != 'bolivares'){
                $amount = $amount * $rate;
            }

            $check_amount = $this->check_amount('bolivares',$contrapartida);

           // if($check_amount->saldo_actual >= $amount){

                $header = new HeaderVoucher();
                $header->setConnection(Auth::user()->database_name);

                $header->reference = request('reference');
                $header->description = "Deposito " . request('description');
                $header->date = request('date');
                
            
                $header->status =  "1";
            
                $header->save();

                $movement = new DetailVoucher();
                $movement->setConnection(Auth::user()->database_name);

                $movement->id_header_voucher = $header->id;
                $movement->id_account = $contrapartida;
                $movement->user_id = request('user_id');
                $movement->debe = 0;
                $movement->haber = $amount;
                $movement->tasa = $rate;
                $movement->status = "C";
            
                $movement->save();

                $movement_counterpart = new DetailVoucher();
                $movement_counterpart->setConnection(Auth::user()->database_name);

                $movement_counterpart->id_header_voucher = $header->id;
                $movement_counterpart->id_account = $account;
                $movement_counterpart->user_id = request('user_id');
                $movement_counterpart->debe = $amount;
                $movement_counterpart->haber = 0;
                $movement_counterpart->tasa = $rate;
                $movement_counterpart->status = "C";

                $movement_counterpart->save();



                $verification = Account::on(Auth::user()->database_name)->findOrFail($account);

                if($verification->status != "M"){
                    $verification->status = "M";
                    $verification->save();
                }

                $verification2 = Account::on(Auth::user()->database_name)->findOrFail($contrapartida);

                if($verification2->status != "M"){
                    $verification2->status = "M";
                    $verification2->save();
                }

                
                return redirect('/bankmovements')->withSuccess('Registro Exitoso!');

           /* }else{
                return redirect('/bankmovements/registerdeposit/'.request('id_account').'')->withDanger('El saldo de la Cuenta '.$check_amount->description.' es menor al monto del deposito!');
            }*/
            
        }else{
            return redirect('/bankmovements/registerdeposit/'.request('id_account').'')->withDanger('No se puede hacer un movimiento a la misma cuenta!');
        }
    }



    public function storeretirement(Request $request)
    {

       
        $data = request()->validate([
            
        
            'id_account'        =>'required',
            'Subcontrapartida'  =>'required',

            'user_id'           =>'required',
            'amount'            =>'required',
            'rate'            =>'required',
            'coin'            =>'required',
            'date'              =>'required',
        
        
        ]);
        //dd($request);
        $account = request('id_account');
        $contrapartida = request('Subcontrapartida');
        $coin = request('coin');

        if($account != $contrapartida){

            $amount = str_replace(',', '.', str_replace('.', '', request('amount')));
            $rate = str_replace(',', '.', str_replace('.', '', request('rate')));

            if($coin != 'bolivares'){
                $amount = $amount * $rate;
            }

            $check_amount = $this->check_amount('bolivares',$account);

           // if($check_amount->saldo_actual >= $amount){
               
                $header = new HeaderVoucher();
                $header->setConnection(Auth::user()->database_name);
                
                $header->reference = request('reference');
                $header->description = "Retiro " . request('description');
                $header->date = request('date');
                $header->status =  "1";
            
                $header->save();


                $movement = new DetailVoucher();
                $movement->setConnection(Auth::user()->database_name);

                $movement->id_header_voucher = $header->id;
                $movement->id_account = $account;
                $movement->user_id = request('user_id');
                $movement->debe = 0;
                $movement->haber = $amount;
                $movement->tasa = $rate;
                $movement->status = "C";
            
                $movement->save();

                $movement_counterpart = new DetailVoucher();
                $movement_counterpart->setConnection(Auth::user()->database_name);

                $movement_counterpart->id_header_voucher = $header->id;
                $movement_counterpart->id_account = $contrapartida;
                $movement_counterpart->user_id = request('user_id');
                $movement_counterpart->debe = $amount;
                $movement_counterpart->haber = 0;
                $movement_counterpart->tasa = $rate;
                $movement_counterpart->status = "C";

                $movement_counterpart->save();


                $verification = Account::on(Auth::user()->database_name)->findOrFail($account);

                if($verification->status != "M"){
                    $verification->status = "M";
                    $verification->save();
                }

                $verification2 = Account::on(Auth::user()->database_name)->findOrFail($contrapartida);

                if($verification2->status != "M"){
                    $verification2->status = "M";
                    $verification2->save();
                }

                return redirect('/bankmovements')->withSuccess('Registro Exitoso!');

           /* }else{
                return redirect('/bankmovements/registerretirement/'.request('id_account').'')->withDanger('El saldo de la Cuenta '.$check_amount->description.' es menor al monto del deposito!');
            }*/

        }else{
            return redirect('/bankmovements/registerretirement/'.request('id_account').'')->withDanger('No se puede hacer un movimiento a la misma cuenta!');
        }
    }


    public function storetransfer(Request $request)
    {
       
        $data = request()->validate([
            
        
            'id_account'        =>'required',
            'id_counterpart'  =>'required',

            'user_id'           =>'required',
            'amount'            =>'required',
            'rate'            =>'required',
            'coin'            =>'required',
            'date'              =>'required',
        
        
        ]);
        
        $account = request('id_account');
        $contrapartida = request('id_counterpart');
        $coin = request('coin'); 

        if($account != $contrapartida){

            $amount = str_replace(',', '.', str_replace('.', '', request('amount')));
            $rate = str_replace(',', '.', str_replace('.', '', request('rate')));

            if($coin != 'bolivares'){
                $amount = $amount * $rate;
            }

            $check_amount = $this->check_amount('bolivares',$account);

           // if($check_amount->saldo_actual >= $amount){
               
                $header = new HeaderVoucher();
                $header->setConnection(Auth::user()->database_name);
                
                $header->reference = request('reference');
                $header->description = "Transferencia " . request('description');
                $header->date = request('date');
                $header->status =  "1";
            
                $header->save();


                $movement = new DetailVoucher();
                $movement->setConnection(Auth::user()->database_name);

                $movement->id_header_voucher = $header->id;
                $movement->id_account = $account;
                $movement->user_id = request('user_id');
                $movement->debe = 0;
                $movement->haber = $amount;
                $movement->tasa = $rate;
                $movement->status = "C";
            
                $movement->save();

                $movement_counterpart = new DetailVoucher();
                $movement_counterpart->setConnection(Auth::user()->database_name);

                $movement_counterpart->id_header_voucher = $header->id;
                $movement_counterpart->id_account = $contrapartida;
                $movement_counterpart->user_id = request('user_id');
                $movement_counterpart->debe = $amount;
                $movement_counterpart->haber = 0;
                $movement_counterpart->tasa = $rate;
                $movement_counterpart->status = "C";

                $movement_counterpart->save();


                $verification = Account::on(Auth::user()->database_name)->findOrFail($account);

                if($verification->status != "M"){
                    $verification->status = "M";
                    $verification->save();
                }

                $verification2 = Account::on(Auth::user()->database_name)->findOrFail($contrapartida);

                if($verification2->status != "M"){
                    $verification2->status = "M";
                    $verification2->save();
                }


                return redirect('/bankmovements')->withSuccess('Registro Exitoso!');

           /* }else{
                return redirect('/bankmovements/registertransfer/'.request('id_account').'')->withDanger('El saldo de la Cuenta '.$check_amount->description.' es menor al monto del deposito!');
            }*/

        }else{
            return redirect('/bankmovements/registertransfer/'.request('id_account').'')->withDanger('No se puede hacer un movimiento a la misma cuenta!');
        }
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
  
   public function calculation($coin)
   {
       
        $accounts = DB::connection(Auth::user()->database_name)->table('accounts')->where('code_one', 1)
                                        ->where('code_two', 1)
                                        ->where('code_three', 1)
                                        ->whereIn('code_four', [1,2])
                                        ->where('code_five','<>',0)
                                        ->get();
   
       
                      
       if(isset($accounts)) {
           
           foreach ($accounts as $var) 
           {
               if($var->code_one != 0)
               {
                   if($var->code_two != 0)
                   {
                       if($var->code_three != 0)
                       {
                           if($var->code_four != 0)
                           {
                               if($var->code_five != 0)
                               {
                                    /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */                                                   
                               
                                    if($coin == 'bolivares'){
                                       $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                       FROM accounts a
                                                       INNER JOIN detail_vouchers d 
                                                           ON d.id_account = a.id
                                                       WHERE a.code_one = ? AND
                                                       a.code_two = ? AND
                                                       a.code_three = ? AND
                                                       a.code_four = ? AND
                                                       a.code_five = ? AND
                                                       d.status = ?
                                                       '
                                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
                                       $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                       FROM accounts a
                                                       INNER JOIN detail_vouchers d 
                                                           ON d.id_account = a.id
                                                       WHERE a.code_one = ? AND
                                                       a.code_two = ? AND
                                                       a.code_three = ? AND
                                                       a.code_four = ? AND
                                                       a.code_five = ? AND
                                                       d.status = ?
                                                       '
                                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
   
                                       $total_dolar_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS dolar
                                                       FROM accounts a
                                                       INNER JOIN detail_vouchers d 
                                                           ON d.id_account = a.id
                                                       WHERE a.code_one = ? AND
                                                       a.code_two = ? AND
                                                       a.code_three = ? AND
                                                       a.code_four = ? AND
                                                       a.code_five = ? AND
                                                       d.status = ?
                                                       '
                                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
   
                                       $total_dolar_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS dolar
                                                       FROM accounts a
                                                       INNER JOIN detail_vouchers d 
                                                           ON d.id_account = a.id
                                                       WHERE a.code_one = ? AND
                                                       a.code_two = ? AND
                                                       a.code_three = ? AND
                                                       a.code_four = ? AND
                                                       a.code_five = ? AND
                                                       d.status = ?
                                                       '
                                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
   
                                                       $var->balance =  $var->balance_previus;
   
                                      
                                       }else{
                                           $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                           FROM accounts a
                                           INNER JOIN detail_vouchers d 
                                               ON d.id_account = a.id
                                           WHERE a.code_one = ? AND
                                           a.code_two = ? AND
                                           a.code_three = ? AND
                                           a.code_four = ? AND
                                           a.code_five = ? AND
                                           d.status = ?
                                           '
                                           , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
                                           
                                           $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                           FROM accounts a
                                           INNER JOIN detail_vouchers d 
                                               ON d.id_account = a.id
                                           WHERE a.code_one = ? AND
                                           a.code_two = ? AND
                                           a.code_three = ? AND
                                           a.code_four = ? AND
                                           a.code_five = ? AND
                                           d.status = ?
                                           '
                                           , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
   
                                          
                                           
                                           
                                       }
                                       $total_debe = $total_debe[0]->debe;
                                       $total_haber = $total_haber[0]->haber;
                                       if(isset($total_dolar_debe[0]->dolar)){
                                           $total_dolar_debe = $total_dolar_debe[0]->dolar;
                                           $var->dolar_debe = $total_dolar_debe;
                                       }
                                       if(isset($total_dolar_haber[0]->dolar)){
                                           $total_dolar_haber = $total_dolar_haber[0]->dolar;
                                           $var->dolar_haber = $total_dolar_haber;
                                       }
                                   
                                       $var->debe = $total_debe;
                                       $var->haber = $total_haber;

                                       if(($var->balance_previus != 0) && ($var->rate !=0)){
                                           $var->balance =  $var->balance_previus;
                                       }
                               
                               }else{
                           
                                   /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */                                                   
                               
                                   if($coin == 'bolivares'){
                                   $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                   FROM accounts a
                                                   INNER JOIN detail_vouchers d 
                                                       ON d.id_account = a.id
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ? AND
                                                   a.code_three = ? AND
                                                   a.code_four = ? AND
                                                   d.status = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);
                                   $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                   FROM accounts a
                                                   INNER JOIN detail_vouchers d 
                                                       ON d.id_account = a.id
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ? AND
                                                   a.code_three = ? AND
                                                   a.code_four = ? AND
                                                   d.status = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                   $total_dolar_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS dolar
                                                   FROM accounts a
                                                   INNER JOIN detail_vouchers d 
                                                       ON d.id_account = a.id
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ? AND
                                                   a.code_three = ? AND
                                                   a.code_four = ? AND
                                                   d.status = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                   $total_dolar_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS dolar
                                                   FROM accounts a
                                                   INNER JOIN detail_vouchers d 
                                                       ON d.id_account = a.id
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ? AND
                                                   a.code_three = ? AND
                                                   a.code_four = ? AND
                                                   d.status = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                                   $var->balance =  $var->balance_previus;

                                   $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                                   FROM accounts a
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ?  AND
                                                   a.code_three = ? AND
                                                   a.code_four = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four]);
                               
                                   }else{
                                       $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                       FROM accounts a
                                       INNER JOIN detail_vouchers d 
                                           ON d.id_account = a.id
                                       WHERE a.code_one = ? AND
                                       a.code_two = ? AND
                                       a.code_three = ? AND
                                       a.code_four = ? AND
                                       d.status = ?
                                       '
                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);
                                       
                                       $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                       FROM accounts a
                                       INNER JOIN detail_vouchers d 
                                           ON d.id_account = a.id
                                       WHERE a.code_one = ? AND
                                       a.code_two = ? AND
                                       a.code_three = ? AND
                                       a.code_four = ? AND
                                       d.status = ?
                                       '
                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                       $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                                   FROM accounts a
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ?  AND
                                                   a.code_three = ? AND
                                                   a.code_four = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four]);

                                       /*if(($var->balance_previus != 0) && ($var->rate !=0))
                                       $var->balance =  $var->balance_previus / $var->rate;*/
                                   }
                                   $total_debe = $total_debe[0]->debe;
                                   $total_haber = $total_haber[0]->haber;
                                   if(isset($total_dolar_debe[0]->dolar)){
                                       $total_dolar_debe = $total_dolar_debe[0]->dolar;
                                       $var->dolar_debe = $total_dolar_debe;
                                   }
                                   if(isset($total_dolar_haber[0]->dolar)){
                                       $total_dolar_haber = $total_dolar_haber[0]->dolar;
                                       $var->dolar_haber = $total_dolar_haber;
                                   }
                               
                                   $var->debe = $total_debe;
                                   $var->haber = $total_haber;

                                   $total_balance = $total_balance[0]->balance;
                                   $var->balance = $total_balance;
                               }  
                           }else{          
                           
                               if($coin == 'bolivares'){
                               $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                               FROM accounts a
                                               INNER JOIN detail_vouchers d 
                                                   ON d.id_account = a.id
                                               WHERE a.code_one = ? AND
                                               a.code_two = ? AND
                                               a.code_three = ? AND
                                               
                                               d.status = ?
                                               '
                                               , [$var->code_one,$var->code_two,$var->code_three,'C']);
                               $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                               FROM accounts a
                                               INNER JOIN detail_vouchers d 
                                                   ON d.id_account = a.id
                                               WHERE a.code_one = ? AND
                                               a.code_two = ? AND
                                               a.code_three = ? AND
                                               
                                               d.status = ?
                                               '
                                               , [$var->code_one,$var->code_two,$var->code_three,'C']);

                               $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ? AND
                                           a.code_two = ?  AND
                                           a.code_three = ?
                                           '
                                           , [$var->code_one,$var->code_two,$var->code_three]);
                               
                               }else{
                                       $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                       FROM accounts a
                                       INNER JOIN detail_vouchers d 
                                           ON d.id_account = a.id
                                       WHERE a.code_one = ? AND
                                       a.code_two = ? AND
                                       a.code_three = ? AND
                                       
                                       d.status = ?
                                       '
                                       , [$var->code_one,$var->code_two,$var->code_three,'C']);
                                       
                                       $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                       FROM accounts a
                                       INNER JOIN detail_vouchers d 
                                           ON d.id_account = a.id
                                       WHERE a.code_one = ? AND
                                       a.code_two = ? AND
                                       a.code_three = ? AND
                                       
                                       d.status = ?
                                       '
                                       , [$var->code_one,$var->code_two,$var->code_three,'C']);
                       
                                       $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ? AND
                                           a.code_two = ? AND
                                           a.code_three = ?
                                           '
                                           , [$var->code_one,$var->code_two,$var->code_three]);

                                   }
                                   $total_debe = $total_debe[0]->debe;
                                   $total_haber = $total_haber[0]->haber;
                               
                                   $var->debe = $total_debe;
                                   $var->haber = $total_haber;

                                   

                                   $total_balance = $total_balance[0]->balance;
                                   $var->balance = $total_balance;
                                     
                                           
                           }           
                       }else{
                                           
                           if($coin == 'bolivares'){
                               $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                               FROM accounts a
                                               INNER JOIN detail_vouchers d 
                                                   ON d.id_account = a.id
                                               WHERE a.code_one = ? AND
                                               a.code_two = ? AND
                                               d.status = ?
                                               '
                                               , [$var->code_one,$var->code_two,'C']);
                               $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                               FROM accounts a
                                               INNER JOIN detail_vouchers d 
                                                   ON d.id_account = a.id
                                               WHERE a.code_one = ? AND
                                               a.code_two = ? AND
                                               d.status = ?
                                               '
                                               , [$var->code_one,$var->code_two,'C']);
                               
                               $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ? AND
                                           a.code_two = ?
                                           '
                                           , [$var->code_one,$var->code_two]);
                               
                               }else{
                                   $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                   FROM accounts a
                                   INNER JOIN detail_vouchers d 
                                       ON d.id_account = a.id
                                   WHERE a.code_one = ? AND
                                   a.code_two = ? AND
                                   d.status = ?
                                   '
                                   , [$var->code_one,$var->code_two,'C']);
                                   
                                   $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                   FROM accounts a
                                   INNER JOIN detail_vouchers d 
                                       ON d.id_account = a.id
                                   WHERE a.code_one = ? AND
                                   a.code_two = ? AND
                                   d.status = ?
                                   '
                                   , [$var->code_one,$var->code_two,'C']);

                                   $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ? AND
                                           a.code_two = ?
                                           '
                                           , [$var->code_one,$var->code_two]);
                   
                               }
                               
                               $total_debe = $total_debe[0]->debe;
                               $total_haber = $total_haber[0]->haber;
                               $var->debe = $total_debe;
                               $var->haber = $total_haber;

                               

                               $total_balance = $total_balance[0]->balance;
                               $var->balance = $total_balance;
                       }
                   }else{
                       if($coin == 'bolivares'){
                           $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                           FROM accounts a
                                           INNER JOIN detail_vouchers d 
                                               ON d.id_account = a.id
                                           WHERE a.code_one = ? AND
                                           d.status = ?
                                           '
                                           , [$var->code_one,'C']);
                           $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                           FROM accounts a
                                           INNER JOIN detail_vouchers d 
                                               ON d.id_account = a.id
                                           WHERE a.code_one = ? AND
                                           d.status = ?
                                           '
                                           , [$var->code_one,'C']);

                           $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ?
                                           '
                                           , [$var->code_one]);
                           
                           }else{
                               $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                               FROM accounts a
                               INNER JOIN detail_vouchers d 
                                   ON d.id_account = a.id
                               WHERE a.code_one = ? AND
                               d.status = ?
                               '
                               , [$var->code_one,'C']);
                               
                               $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                               FROM accounts a
                               INNER JOIN detail_vouchers d 
                                   ON d.id_account = a.id
                               WHERE a.code_one = ? AND
                               d.status = ?
                               '
                               , [$var->code_one,'C']);

                               $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ?
                                           '
                                           , [$var->code_one]);
               
                           }
                           $total_debe = $total_debe[0]->debe;
                           $total_haber = $total_haber[0]->haber;
                           $var->debe = $total_debe;
                           $var->haber = $total_haber;

                           $total_balance = $total_balance[0]->balance;

                           $var->balance = $total_balance;

                   }
               }else{
                   return redirect('/accounts/menu')->withDanger('El codigo uno es igual a cero!');
               }
           } 
       
       }else{
           return redirect('/accounts/menu')->withDanger('No hay Cuentas');
       }              
                
      
       
        return $accounts;
   }



   public function check_amount($coin,$id_account)
   {
       
        $var = Account::on(Auth::user()->database_name)->where('id',$id_account)->first();
   
       
                      
       if(isset($var)) {
           
               if($var->code_one != 0)
               {
                   if($var->code_two != 0)
                   {
                       if($var->code_three != 0)
                       {
                           if($var->code_four != 0)
                           {
                               if($var->code_five != 0)
                               {
                                    /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */                                                   
                               
                                    if($coin == 'bolivares'){
                                       $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                       FROM accounts a
                                                       INNER JOIN detail_vouchers d 
                                                           ON d.id_account = a.id
                                                       WHERE a.code_one = ? AND
                                                       a.code_two = ? AND
                                                       a.code_three = ? AND
                                                       a.code_four = ? AND
                                                       a.code_five = ? AND
                                                       d.status = ?
                                                       '
                                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
                                       $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                       FROM accounts a
                                                       INNER JOIN detail_vouchers d 
                                                           ON d.id_account = a.id
                                                       WHERE a.code_one = ? AND
                                                       a.code_two = ? AND
                                                       a.code_three = ? AND
                                                       a.code_four = ? AND
                                                       a.code_five = ? AND
                                                       d.status = ?
                                                       '
                                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
   
                                       $total_dolar_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS dolar
                                                       FROM accounts a
                                                       INNER JOIN detail_vouchers d 
                                                           ON d.id_account = a.id
                                                       WHERE a.code_one = ? AND
                                                       a.code_two = ? AND
                                                       a.code_three = ? AND
                                                       a.code_four = ? AND
                                                       a.code_five = ? AND
                                                       d.status = ?
                                                       '
                                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
   
                                       $total_dolar_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS dolar
                                                       FROM accounts a
                                                       INNER JOIN detail_vouchers d 
                                                           ON d.id_account = a.id
                                                       WHERE a.code_one = ? AND
                                                       a.code_two = ? AND
                                                       a.code_three = ? AND
                                                       a.code_four = ? AND
                                                       a.code_five = ? AND
                                                       d.status = ?
                                                       '
                                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
   
                                                       $var->balance =  $var->balance_previus;
   
                                      
                                       }else{
                                           $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                           FROM accounts a
                                           INNER JOIN detail_vouchers d 
                                               ON d.id_account = a.id
                                           WHERE a.code_one = ? AND
                                           a.code_two = ? AND
                                           a.code_three = ? AND
                                           a.code_four = ? AND
                                           a.code_five = ? AND
                                           d.status = ?
                                           '
                                           , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
                                           
                                           $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                           FROM accounts a
                                           INNER JOIN detail_vouchers d 
                                               ON d.id_account = a.id
                                           WHERE a.code_one = ? AND
                                           a.code_two = ? AND
                                           a.code_three = ? AND
                                           a.code_four = ? AND
                                           a.code_five = ? AND
                                           d.status = ?
                                           '
                                           , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
   
                                          
                                           
                                           
                                       }
                                       $total_debe = $total_debe[0]->debe;
                                       $total_haber = $total_haber[0]->haber;
                                       if(isset($total_dolar_debe[0]->dolar)){
                                           $total_dolar_debe = $total_dolar_debe[0]->dolar;
                                           $var->dolar_debe = $total_dolar_debe;
                                       }
                                       if(isset($total_dolar_haber[0]->dolar)){
                                           $total_dolar_haber = $total_dolar_haber[0]->dolar;
                                           $var->dolar_haber = $total_dolar_haber;
                                       }
                                   
                                       $var->debe = $total_debe;
                                       $var->haber = $total_haber;

                                       if(($var->balance_previus != 0) && ($var->rate !=0)){
                                           $var->balance =  $var->balance_previus;
                                       }
                               
                                       $var->saldo_actual = $var->balance_previus + $var->debe - $var->haber;
                               }else{
                           
                                   /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */                                                   
                               
                                   if($coin == 'bolivares'){
                                   $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                   FROM accounts a
                                                   INNER JOIN detail_vouchers d 
                                                       ON d.id_account = a.id
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ? AND
                                                   a.code_three = ? AND
                                                   a.code_four = ? AND
                                                   d.status = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);
                                   $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                   FROM accounts a
                                                   INNER JOIN detail_vouchers d 
                                                       ON d.id_account = a.id
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ? AND
                                                   a.code_three = ? AND
                                                   a.code_four = ? AND
                                                   d.status = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                   $total_dolar_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS dolar
                                                   FROM accounts a
                                                   INNER JOIN detail_vouchers d 
                                                       ON d.id_account = a.id
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ? AND
                                                   a.code_three = ? AND
                                                   a.code_four = ? AND
                                                   d.status = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                   $total_dolar_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS dolar
                                                   FROM accounts a
                                                   INNER JOIN detail_vouchers d 
                                                       ON d.id_account = a.id
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ? AND
                                                   a.code_three = ? AND
                                                   a.code_four = ? AND
                                                   d.status = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                                   $var->balance =  $var->balance_previus;

                                   $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                                   FROM accounts a
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ?  AND
                                                   a.code_three = ? AND
                                                   a.code_four = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four]);
                               
                                   }else{
                                       $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                       FROM accounts a
                                       INNER JOIN detail_vouchers d 
                                           ON d.id_account = a.id
                                       WHERE a.code_one = ? AND
                                       a.code_two = ? AND
                                       a.code_three = ? AND
                                       a.code_four = ? AND
                                       d.status = ?
                                       '
                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);
                                       
                                       $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                       FROM accounts a
                                       INNER JOIN detail_vouchers d 
                                           ON d.id_account = a.id
                                       WHERE a.code_one = ? AND
                                       a.code_two = ? AND
                                       a.code_three = ? AND
                                       a.code_four = ? AND
                                       d.status = ?
                                       '
                                       , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                       $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                                   FROM accounts a
                                                   WHERE a.code_one = ? AND
                                                   a.code_two = ?  AND
                                                   a.code_three = ? AND
                                                   a.code_four = ?
                                                   '
                                                   , [$var->code_one,$var->code_two,$var->code_three,$var->code_four]);

                                       /*if(($var->balance_previus != 0) && ($var->rate !=0))
                                       $var->balance =  $var->balance_previus / $var->rate;*/
                                   }
                                   $total_debe = $total_debe[0]->debe;
                                   $total_haber = $total_haber[0]->haber;
                                   if(isset($total_dolar_debe[0]->dolar)){
                                       $total_dolar_debe = $total_dolar_debe[0]->dolar;
                                       $var->dolar_debe = $total_dolar_debe;
                                   }
                                   if(isset($total_dolar_haber[0]->dolar)){
                                       $total_dolar_haber = $total_dolar_haber[0]->dolar;
                                       $var->dolar_haber = $total_dolar_haber;
                                   }
                               
                                   $var->debe = $total_debe;
                                   $var->haber = $total_haber;

                                   $total_balance = $total_balance[0]->balance;
                                   $var->balance = $total_balance;

                                   $var->saldo_actual = $var->balance + $var->debe - $var->haber;
                               }  
                           }else{          
                           
                               if($coin == 'bolivares'){
                               $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                               FROM accounts a
                                               INNER JOIN detail_vouchers d 
                                                   ON d.id_account = a.id
                                               WHERE a.code_one = ? AND
                                               a.code_two = ? AND
                                               a.code_three = ? AND
                                               
                                               d.status = ?
                                               '
                                               , [$var->code_one,$var->code_two,$var->code_three,'C']);
                               $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                               FROM accounts a
                                               INNER JOIN detail_vouchers d 
                                                   ON d.id_account = a.id
                                               WHERE a.code_one = ? AND
                                               a.code_two = ? AND
                                               a.code_three = ? AND
                                               
                                               d.status = ?
                                               '
                                               , [$var->code_one,$var->code_two,$var->code_three,'C']);

                               $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ? AND
                                           a.code_two = ?  AND
                                           a.code_three = ?
                                           '
                                           , [$var->code_one,$var->code_two,$var->code_three]);
                               
                               }else{
                                       $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                       FROM accounts a
                                       INNER JOIN detail_vouchers d 
                                           ON d.id_account = a.id
                                       WHERE a.code_one = ? AND
                                       a.code_two = ? AND
                                       a.code_three = ? AND
                                       
                                       d.status = ?
                                       '
                                       , [$var->code_one,$var->code_two,$var->code_three,'C']);
                                       
                                       $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                       FROM accounts a
                                       INNER JOIN detail_vouchers d 
                                           ON d.id_account = a.id
                                       WHERE a.code_one = ? AND
                                       a.code_two = ? AND
                                       a.code_three = ? AND
                                       
                                       d.status = ?
                                       '
                                       , [$var->code_one,$var->code_two,$var->code_three,'C']);
                       
                                       $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ? AND
                                           a.code_two = ? AND
                                           a.code_three = ?
                                           '
                                           , [$var->code_one,$var->code_two,$var->code_three]);

                                   }
                                   $total_debe = $total_debe[0]->debe;
                                   $total_haber = $total_haber[0]->haber;
                               
                                   $var->debe = $total_debe;
                                   $var->haber = $total_haber;

                                   

                                   $total_balance = $total_balance[0]->balance;
                                   $var->balance = $total_balance;
                                     
                                   $var->saldo_actual = $var->balance + $var->debe - $var->haber;
                           }           
                       }else{
                                           
                           if($coin == 'bolivares'){
                               $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                               FROM accounts a
                                               INNER JOIN detail_vouchers d 
                                                   ON d.id_account = a.id
                                               WHERE a.code_one = ? AND
                                               a.code_two = ? AND
                                               d.status = ?
                                               '
                                               , [$var->code_one,$var->code_two,'C']);
                               $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                               FROM accounts a
                                               INNER JOIN detail_vouchers d 
                                                   ON d.id_account = a.id
                                               WHERE a.code_one = ? AND
                                               a.code_two = ? AND
                                               d.status = ?
                                               '
                                               , [$var->code_one,$var->code_two,'C']);
                               
                               $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ? AND
                                           a.code_two = ?
                                           '
                                           , [$var->code_one,$var->code_two]);
                               
                               }else{
                                   $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                   FROM accounts a
                                   INNER JOIN detail_vouchers d 
                                       ON d.id_account = a.id
                                   WHERE a.code_one = ? AND
                                   a.code_two = ? AND
                                   d.status = ?
                                   '
                                   , [$var->code_one,$var->code_two,'C']);
                                   
                                   $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                   FROM accounts a
                                   INNER JOIN detail_vouchers d 
                                       ON d.id_account = a.id
                                   WHERE a.code_one = ? AND
                                   a.code_two = ? AND
                                   d.status = ?
                                   '
                                   , [$var->code_one,$var->code_two,'C']);

                                   $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ? AND
                                           a.code_two = ?
                                           '
                                           , [$var->code_one,$var->code_two]);
                   
                               }
                               
                               $total_debe = $total_debe[0]->debe;
                               $total_haber = $total_haber[0]->haber;
                               $var->debe = $total_debe;
                               $var->haber = $total_haber;

                               

                               $total_balance = $total_balance[0]->balance;
                               $var->balance = $total_balance;

                               $var->saldo_actual = $var->balance + $var->debe - $var->haber;
                       }
                   }else{
                       if($coin == 'bolivares'){
                           $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                           FROM accounts a
                                           INNER JOIN detail_vouchers d 
                                               ON d.id_account = a.id
                                           WHERE a.code_one = ? AND
                                           d.status = ?
                                           '
                                           , [$var->code_one,'C']);
                           $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                           FROM accounts a
                                           INNER JOIN detail_vouchers d 
                                               ON d.id_account = a.id
                                           WHERE a.code_one = ? AND
                                           d.status = ?
                                           '
                                           , [$var->code_one,'C']);

                           $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ?
                                           '
                                           , [$var->code_one]);
                           
                           }else{
                               $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                               FROM accounts a
                               INNER JOIN detail_vouchers d 
                                   ON d.id_account = a.id
                               WHERE a.code_one = ? AND
                               d.status = ?
                               '
                               , [$var->code_one,'C']);
                               
                               $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                               FROM accounts a
                               INNER JOIN detail_vouchers d 
                                   ON d.id_account = a.id
                               WHERE a.code_one = ? AND
                               d.status = ?
                               '
                               , [$var->code_one,'C']);

                               $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                           FROM accounts a
                                           WHERE a.code_one = ?
                                           '
                                           , [$var->code_one]);
               
                           }
                           $total_debe = $total_debe[0]->debe;
                           $total_haber = $total_haber[0]->haber;
                           $var->debe = $total_debe;
                           $var->haber = $total_haber;

                           $total_balance = $total_balance[0]->balance;

                           $var->balance = $total_balance;
                           $var->saldo_actual = $var->balance + $var->debe - $var->haber;
                   }
               }else{
                   return redirect('/accounts/menu')->withDanger('El codigo uno es igual a cero!');
               }
           
       
       }else{
           return redirect('/accounts/menu')->withDanger('No hay Cuentas');
       }              
                
      
       
        return $var;
   }
  
  
   
   /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function edit($id)
   {
        $bankmovement = BankMovement::on(Auth::user()->database_name)->find($id);
       
     
        return view('admin.bankmovements.edit',compact('bankmovement','modelos','colors'));
  
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

    $vars =  BankMovement::on(Auth::user()->database_name)->find($id);

    $vars_status = $vars->status;
   
    $data = request()->validate([
        
       
        'account_code_one'         =>'required',
        'account_code_two'         =>'required',
        'account_code_three'         =>'required',
        'account_code_four'         =>'required',
        'account_period'         =>'required',

        'counterpart_code_one'         =>'required',
        'counterpart_code_two'         =>'required',
        'counterpart_code_three'         =>'required',
        'counterpart_code_four'         =>'required',
        'counterpart_period'         =>'required',

        'id_header'         =>'required',
        'id_client'         =>'required',
        'id_vendor'         =>'required',
        'user_id'         =>'required',

        'description'         =>'required',
        'type_movement'         =>'required',
        'date'         =>'required',

        'reference'         =>'required',
       
       
    ]);

    $var = BankMovement::on(Auth::user()->database_name)->findOrFail($id);

    $var->account_code_one = request('account_code_one');
    $var->account_code_two = request('account_code_two');
    $var->account_code_three = request('account_code_three');
    $var->account_code_four = request('account_code_four');
    $var->account_period = request('account_period');

    $var->counterpart_code_one = request('counterpart_code_one');
    $var->counterpart_code_two = request('counterpart_code_two');
    $var->counterpart_code_three = request('counterpart_code_three');
    $var->counterpart_code_four = request('counterpart_code_four');
    $var->counterpart_period = request('counterpart_period');

    $var->id_header = request('id_header');
    $var->id_client = request('id_client');
    $var->id_vendor = request('id_vendor');
    $var->user_id = request('user_id');

    $var->description = request('description');
    $var->type_movement = request('type_movement');
   
    $var->date = request('date');
    $var->reference = request('reference');


   
    if(request('status') == null){
        $var->status = $vars_status;
    }else{
        $var->status = request('status');
    }
   
    $var->save();

    return redirect('/bankmovements')->withSuccess('Actualizacion Exitosa!');
    }


   /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
  public function destroy($id){
    if(isset($id)){
        $header = HeaderVoucher::on(Auth::user()->database_name)->findOrFail($id);

        $detail = DetailVoucher::on(Auth::user()->database_name)->where('id_header_voucher',$header->id)
            ->update(['status' => 'X']);

        $header->status = "X";
        $header->save();

        return redirect('/bankmovements/orderpaymentlist')->withSuccess('Se deshabilitó con éxito el movimiento!');
       
       }else{
        return redirect('/bankmovements/orderpaymentlist')->withDanger('Debe buscar un movimiento primero !!');
       
       }
  }

   public function listbeneficiario(Request $request, $id_var = null){
    //validar si la peticion es asincrona
    if($request->ajax()){
        try{
            
            if(strcmp($id_var, "Cliente") == 0){
                $respuesta = Client::on(Auth::user()->database_name)->select('id','name')->orderBy('name','asc')->get();
            }else{
               $respuesta = Provider::on(Auth::user()->database_name)->select('id','razon_social as name')->orderBy('razon_social','asc')->get();
             }
           
            return response()->json($respuesta,200);
        }catch(Throwable $th){
            return response()->json(false,500);
        }
    }
    
}


 public function list(Request $request, $id_var = null){
    //validar si la peticion es asincrona
    if($request->ajax()){
        try{
            
            $account = Account::on(Auth::user()->database_name)->find($id_var);
            $subcontrapartidas = Account::on(Auth::user()->database_name)->select('id','description')->where('code_one',$account->code_one)
                                                                    ->where('code_two',$account->code_two)
                                                                    ->where('code_three',$account->code_three)
                                                                    ->where('code_four',$account->code_four)
                                                                    ->where('code_five', '<>',0)
                                                                    ->orderBy('description','asc')->get();
            return response()->json($subcontrapartidas,200);
        }catch(Throwable $th){
            return response()->json(false,500);
        }
    }
    
}





}
