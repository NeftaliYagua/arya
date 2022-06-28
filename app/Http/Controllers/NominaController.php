<?php

namespace App\Http\Controllers;

use App\Account;
use App\DetailVoucher;
use App\Employee;
use App\HeaderVoucher;
use App\Nomina;
use App\NominaCalculation;
use App\NominaConcept;
use App\Profession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NominaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        
        $user       =   auth()->user();
        $users_role =   $user->role_id;
        if($users_role == '1'){
           $nominas      =   Nomina::on(Auth::user()->database_name)->where('status','NOT LIKE','X')->orderBy('id', 'desc')->get();
          
        }elseif($users_role == '2'){
            return view('admin.index');
        }

    
        return view('admin.nominas.index',compact('nominas'));
      
    }

    public function searchMovementNomina($id_nomina){
        $header = HeaderVoucher::on(Auth::user()->database_name)->where('id_nomina',$id_nomina)->first();
        
        if(isset($header)){
            $detail = new DetailVoucherController();
            return $detail->create("bolivares",$header->id);
        }


        return redirect('/nominas')->withDanger('No posee movimientos la Nomina !!');
    }

    public function create()
    {
        $professions = Profession::on(Auth::user()->database_name)->orderBY('name','asc')->get();
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');

        return view('admin.nominas.create',compact('professions','datenow'));
    }

   
    public function selectemployee($id)
    {

        $var  = Nomina::on(Auth::user()->database_name)->find($id);

        $employees = Employee::on(Auth::user()->database_name)->where('status','NOT LIKE','X')->where('profession_id',$var->id_profession)->get();

        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');

       // dd($var);
        return view('admin.nominas.selectemployee',compact('var','employees','datenow'));
        
    }

    public function calculate($id_nomina)
    {

        $nomina = Nomina::on(Auth::user()->database_name)->find($id_nomina);
        
        $employees = Employee::on(Auth::user()->database_name)->where('status','NOT LIKE','X')->where('profession_id',$nomina->id_profession)->get();

        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');

        $global = new GlobalController();
        $bcv = $global->search_bcv();
       
        foreach($employees as $employee){
            $this->addNominaCalculation($nomina,$employee);
        }


        $amount_total_nomina = $this->calculateAmountTotalNomina($nomina);

        $header_voucher  = new HeaderVoucher();
        $header_voucher->setConnection(Auth::user()->database_name);

        $header_voucher->id_nomina = $id_nomina;
        $header_voucher->description = "Nomina";
        $header_voucher->date = $datenow;
        
    
        $header_voucher->status =  "1";
    
        $header_voucher->save();

        $accounts_sueldos = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                        ->where('description','LIKE', 'Sueldos y Salarios')
                                                                        ->first();

        $this->add_movement($bcv,$header_voucher->id,$accounts_sueldos->id,$nomina->id,$amount_total_nomina,0);

        $accounts_sueldos_por_pagar = DB::connection(Auth::user()->database_name)->table('accounts')
        ->where('description','LIKE', 'Sueldos por Pagar')
        ->first();

        $this->add_movement($bcv,$header_voucher->id,$accounts_sueldos_por_pagar->id,$nomina->id,0,$amount_total_nomina);

        
        return redirect('/nominas')->withSuccess('El calculo de la Nomina '.$nomina->description.' fue Exitoso!');
        
    }


    
    public function add_movement($bcv,$id_header,$id_account,$id_nomina,$debe,$haber){

        $detail = new DetailVoucher();
        $detail->setConnection(Auth::user()->database_name);
        $user       =   auth()->user();

        $detail->id_account = $id_account;
        $detail->id_header_voucher = $id_header;
        $detail->user_id = $user->id;
        $detail->tasa = $bcv;
       

      /*  $valor_sin_formato_debe = str_replace(',', '.', str_replace('.', '', $debe));
        $valor_sin_formato_haber = str_replace(',', '.', str_replace('.', '', $haber));*/


        $detail->debe = $debe;
        $detail->haber = $haber;
       
      
        $detail->status =  "C";

         /*Le cambiamos el status a la cuenta a M, para saber que tiene Movimientos en detailVoucher */
         
            $account = Account::on(Auth::user()->database_name)->findOrFail($detail->id_account);

            if($account->status != "M"){
                $account->status = "M";
                $account->save();
            }
         
    
        $detail->save();

    }


    public function calculateAmountTotalNomina($nomina){

       
        $amount_total_asignacion = NominaCalculation::join('nomina_concepts','nomina_concepts.id','nomina_calculations.id_nomina_concept')
                                                    ->where('id_nomina',$nomina->id)
                                                    ->where('nomina_concepts.sign',"A")
                                                    ->sum('nomina_calculations.amount');

        $amount_total_deduccion = NominaCalculation::join('nomina_concepts','nomina_concepts.id','nomina_calculations.id_nomina_concept')
                                                    ->where('id_nomina',$nomina->id)
                                                    ->where('nomina_concepts.sign',"D")
                                                    ->sum('nomina_calculations.amount');

                                            
        return $amount_total_asignacion - $amount_total_deduccion;

    }
   
  
    public function addNominaCalculation($nomina,$employee)
    {
        
        if(($nomina->type == "Primera Quincena") || ($nomina->type == "Segunda Quincena")){
            
            $nominaconcepts_comun = NominaConcept::on(Auth::user()->database_name)->where('type','LIKE','Quincenal')
                                                ->where('calculate','LIKE','S')->get();
        }

        if(($nomina->type == "Primera Quincena")){
            $nominaconcepts = NominaConcept::on(Auth::user()->database_name)->where('type','LIKE','%Primera Quincena%')
                                                ->where('calculate','LIKE','S')->get();

        }else if(($nomina->type == "Segunda Quincena")){
            $nominaconcepts = NominaConcept::on(Auth::user()->database_name)->where('type','LIKE','%Segunda Quincena%')
                                                ->where('calculate','LIKE','S')->get();

        }else if(($nomina->type == "Quincenal")){
            $nominaconcepts = NominaConcept::on(Auth::user()->database_name)->where('type','LIKE','Quincenal')
                                                ->where('calculate','LIKE','S')->get();

        }else if(($nomina->type == "Mensual")){
            $nominaconcepts = NominaConcept::on(Auth::user()->database_name)->where('type','LIKE','%Mensual%')
                                                ->where('calculate','LIKE','S')->get();

        }else if(($nomina->type == "Semanal")){
            $nominaconcepts = NominaConcept::on(Auth::user()->database_name)->where('type','LIKE','%Semanal%')
                                                ->where('calculate','LIKE','S')->get();

        }else if(($nomina->type == "Especial")){
            $nominaconcepts = NominaConcept::on(Auth::user()->database_name)->where('type','LIKE','Especial')
                                                ->where('calculate','LIKE','S')->get();
        }else{
            $nominaconcepts = NominaConcept::on(Auth::user()->database_name)->where('type','LIKE',$nomina->type)
                                                ->where('calculate','LIKE','S')->get();
        }
       
        if(isset($nominaconcepts))
        {
            foreach($nominaconcepts as $nominaconcept){

                $vars = new NominaCalculation();
                $vars->setConnection(Auth::user()->database_name);

                $vars->id_nomina = $nomina->id;
                $vars->id_nomina_concept = $nominaconcept->id;
                $vars->id_employee = $employee->id;
            
                $vars->number_receipt = 0;
                
                $vars->type = 'No';

                $vars->days = 0;
                $vars->hours = 0;
                $vars->cantidad = 0;
        
                $amount = 0;
                $tiene_calculo = false;

                if(($nomina->type == "Primera Quincena") || ($nomina->type == "Segunda Quincena")){
                    if(isset($nominaconcept->id_formula_q)){
                        $tiene_calculo = true;
                        $amount = $this->formula($nominaconcept->id_formula_q,$employee,$nomina,$vars);
                    }
                    
                }else if(($nomina->type == "Mensual")){
                    if(isset($nominaconcept->id_formula_m)){
                        $tiene_calculo = true;
                        $amount = $this->formula($nominaconcept->id_formula_m,$employee,$nomina,$vars);
                    }

                }else if(($nomina->type == "Semanal")){
                    if(isset($nominaconcept->id_formula_s)){
                        $tiene_calculo = true;
                        $amount = $this->formula($nominaconcept->id_formula_s,$employee,$nomina,$vars);
                    }

                }else if(($nomina->type == "Especial")){
                    if(isset($nominaconcept->id_formula_m)){
                        $tiene_calculo = true;
                        $amount = $this->formula($nominaconcept->id_formula_m,$employee,$nomina,$vars);
                    }
                }

                $vars->amount = $amount;
                $vars->status =  "1";
            
                if($tiene_calculo == true){
                    $vars->save();
                  
                }
            }

           
        }


        if(isset($nominaconcepts_comun))
        {
            foreach($nominaconcepts_comun as $nominaconcept){

                $vars = new NominaCalculation();
                $vars->setConnection(Auth::user()->database_name);
    
                $vars->id_nomina = $nomina->id;
                $vars->id_nomina_concept = $nominaconcept->id;
                $vars->id_employee = $employee->id;
               
                $vars->number_receipt = 0;
                
                $vars->type = 'No';

                $vars->days = 0;
                $vars->hours = 0;
                $vars->cantidad = 0;
        
                $amount = 0;
                $tiene_calculo = false;
    
                if(($nomina->type == "Primera Quincena") || ($nomina->type == "Segunda Quincena")){
                    if(isset($nominaconcept->id_formula_q)){
                        $tiene_calculo = true;
                        $amount = $this->formula($nominaconcept->id_formula_q,$employee,$nomina,$vars);
                    }
    
                }else if(($nomina->type == "Mensual")){
                    if(isset($nominaconcept->id_formula_m)){
                        $tiene_calculo = true;
                        $amount = $this->formula($nominaconcept->id_formula_m,$employee,$nomina,$vars);
                    }
    
                }else if(($nomina->type == "Semanal")){
                    if(isset($nominaconcept->id_formula_s)){
                        $tiene_calculo = true;
                        $amount = $this->formula($nominaconcept->id_formula_s,$employee,$nomina,$vars);
                    }
                }
    
                $vars->amount = $amount;
                $vars->status =  "1";
               
               
                if($tiene_calculo == true){
                    $vars->save();
                   
             
                }
                
            }
            
           
        }    
        

        
        
    }

    public function formula($id_formula,$employee,$nomina,$nomina_calculation)
    {

        
        $lunes = 0;
        $hours = 0;
        $days = 0;
        $cestaticket = 0;
        

        if(isset($nomina_calculation->days)){
            if($nomina_calculation->days != 0){
                $days = $nomina_calculation->days;
            }
        }

        if(isset($nomina_calculation->hours)){
            if($nomina_calculation->hours != 0){
                $hours = $nomina_calculation->hours;
            }
        }

        if(isset($nomina_calculation->cantidad)){
            if($nomina_calculation->cantidad != 0){
                $cestaticket = $nomina_calculation->cantidad;
            }
        }

        

        if($id_formula == 1){
            //{{sueldo}} * 12 / 52 * {{lunes}} * 0.04
            $lunes = $this->calcular_cantidad_de_lunes($nomina);
            $total = ($employee->monto_pago * 12)/52 * ($lunes * 0.04);
            
        }else if($id_formula == 2){
            //{{sueldo}} * 12 / 52 * {{lunes}} * 0.04 * 5 / 5
            $lunes = $this->calcular_cantidad_de_lunes($nomina);
            $total = (($employee->monto_pago * 12)/52) * (($lunes * 0.04) * 5)/5 ;
            
        }else if($id_formula == 3){
            //{{sueldo}} / 30 * 7.5
            $total = ($employee->monto_pago * 30) * 7.5 ;
            
        }else if($id_formula == 4){
            //{{sueldo}} * 0.01 / 2
            $total = ($employee->monto_pago * 0.01)/2 ;
            
        }else if($id_formula == 5){
            //{{sueldo}} * 0.01 / 4
            $total = ($employee->monto_pago * 0.01) / 4 ;
            
        }else if($id_formula == 6){
            //{{sueldo}} / 2
            $total = ($employee->monto_pago)/2 ;
            
        }else if($id_formula == 7){
            //{{sueldo}} 
            $total = ($employee->monto_pago) ;
            
        }else if($id_formula == 8){
            //{{sueldo}} / 30 / 8 * 1.6 / {{horas}} 
            $total = (($employee->monto_pago * 30)/8 * 1.6) * $hours ;
            
        }else if($id_formula == 9){
            //{{sueldo}} / 30 / 8 * 1.8 / {{horas}}
            $total = (($employee->monto_pago * 30)/8 * 1.8) * $hours ;
            
        }else if($id_formula == 10){
            //{{sueldo}} / 30*1.5 *{{dias}}
            $total = ($employee->monto_pago / 30) * 1.5 * $days;
            
        }else if($id_formula == 11){
            //{{sueldo}} / 30 * 1.5 * {{diasferiados}}
            $total = ($employee->monto_pago / 30) * 1.5 * $days;
            
        }else if($id_formula == 12){
            //{{cestaticket}} / 2
            $total = $cestaticket / 2;
            
        }else if($id_formula == 13){
            //{{sueldo}} * 0.03
            $total = $employee->monto_pago * 0.03;
            
        }else if($id_formula == 14){
            //{{sueldo}} * 12 / 52 * {{lunes}} * 0.005
            $lunes = $this->calcular_cantidad_de_lunes($nomina);
            $total = ($employee->monto_pago * 12)/52 * $lunes * 0.005;
            
        }else if($id_formula == 15){
            //{{sueldo}} * 12 / 52 * {{lunes}} * 0.004
            $lunes = $this->calcular_cantidad_de_lunes($nomina);
            $total = ($employee->monto_pago * 12)/52 * $lunes * 0.004;
            
        }else if($id_formula == 16){
            //{{sueldo}} / 30 * {{dias_faltados}}
            
            $total = ($employee->monto_pago / 30) * $days;
            
        }else if($id_formula == 17){
            //{{sueldo}} /4
            $total = ($employee->monto_pago) /4;
            
        }else{
            return -1;
        }
        return $total;
    }

    public function calcular_cantidad_de_lunes($nomina)
    {
        $fechaInicio= strtotime($nomina->date_begin);
        $fechaFin= strtotime($nomina->date_end);
       

        $cantidad_de_dias_lunes = 0;
        //Recorro las fechas y con la función strotime obtengo los lunes
        for($i=$fechaInicio; $i<=$fechaFin; $i+=86400){
            //Sacar el dia de la semana con el modificador N de la funcion date
            $dia = date('N', $i);
            if($dia==1){
                $cantidad_de_dias_lunes += 1;
            }
        }
        return $cantidad_de_dias_lunes;
    }


    public function store(Request $request)
    {
       
        $data = request()->validate([
           
            'id_profession'     =>'required',
            'description'       =>'required|max:60',
            'type'              =>'required',
            'date_begin'        =>'required',
            
            
           
        ]);

        $users = new Nomina();
        $users->setConnection(Auth::user()->database_name);

        $users->id_profession = request('id_profession');
        $users->description = request('description');
        $users->type = request('type');
       
        $users->date_begin = request('date_begin');
        
        $users->date_end = request('date_end');
        $users->status =  "1";
       
       

        $users->save();

        return redirect('/nominas')->withSuccess('Registro Exitoso!');
    }



    public function edit($id)
    {

        $var  = Nomina::on(Auth::user()->database_name)->find($id);

        $professions = Profession::on(Auth::user()->database_name)->orderBY('name','asc')->get();
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');

        
        return view('admin.nominas.edit',compact('var','professions','datenow'));
        
    }

   


    public function update(Request $request,$id)
    {
       
        $vars =  Nomina::on(Auth::user()->database_name)->find($id);
        $var_status = $vars->status;
      

        $data = request()->validate([
           
            'id_profession'         =>'required',
            'description'         =>'required|max:255',
            'type'         =>'required',
            'date_begin'         =>'required|max:255',
            
            
           
        ]);

        $var          = Nomina::on(Auth::user()->database_name)->findOrFail($id);

        $var->id_profession = request('id_profession');
        $var->description = request('description');
        $var->type = request('type');
        $var->date_begin = request('date_begin');
        $var->date_end = request('date_end');
       
        if(request('status') == null){
            $var->status = $var_status;
        }else{
            $var->status = request('status');
        }
       

        $var->save();


        return redirect('/nominas')->withSuccess('Registro Guardado Exitoso!');

    }

    public function destroy(Request $request)
   {
        $nomina = Nomina::on(Auth::user()->database_name)->findOrFail($request->id_nomina_modal);

        if(isset($nomina)){
            $nomina->status = 'X';

            $nomina->save();

            return redirect('/nominas')->withSuccess('Eliminacion Exitosa!');

        }else{

            return redirect('/nominas')->withDanger('No se encontro el empleado!');
        }
   }

}
