<?php

namespace App\Http\Controllers;

use App\Employee;
use App\NominaConcept;
use App\NominaFormula;
use App\Account;
use App\Profession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NominaConceptController extends Controller
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


        $nominaconcepts = NominaConcept::on(Auth::user()->database_name)
         /*->orderBy('id', 'asc')
       ->orderBy('type', 'asc')
        ->orderBy('calculate', 'desc')*/
        ->get();
        
        if (isset($nominaconcepts)){
            foreach ($nominaconcepts as $nominaconcept) {
            
                $accounts = Account::on(Auth::user()->database_name)->orderBy('code_one', 'asc')
                ->where('description','LIKE','%'.$nominaconcept->account_name.'%')
                ->get()->first();

                if(!empty($accounts)){
                    $nominaconcept->account_code = $accounts->code_one.'.'.$accounts->code_two.'.'.$accounts->code_three.'.'.$accounts->code_four.'.'.str_pad($accounts->code_five, 3, "0", STR_PAD_LEFT); 
                } else {
                    $nominaconcept->account_code = ''; 
                }

                
            }
        }
    
        return view('admin.nominaconcepts.index',compact('nominaconcepts'));
      
    }

    public function create()
    {
       
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');
        
        $formulam = NominaFormula::on(Auth::user()->database_name)
        ->where('type','M')
        ->orderBy('id','asc')->get();

        $formulaq = NominaFormula::on(Auth::user()->database_name)
        ->where('type','Q')
        ->orderBy('id','asc')->get();

        $formulas = NominaFormula::on(Auth::user()->database_name)
        ->where('type','S')
        ->orderBy('id','asc')->get();

        $formulae = NominaFormula::on(Auth::user()->database_name)
        ->where('type','E')
        ->orderBy('id','asc')->get();

        $formulaa = NominaFormula::on(Auth::user()->database_name)
        ->where('type','A')
        ->orderBy('id','asc')->get();
    
        $accounts = '';

        $accounts = Account::on(Auth::user()->database_name)
        ->where('code_one','2')
        ->where('code_two','1')
        ->where('code_three','2')
        ->where('code_four','2')
        ->orderBy('id', 'asc')
        ->get();

        $accounts_two = Account::on(Auth::user()->database_name)
        ->where('code_one','6')
        ->where('code_two','1')
        ->where('code_three','1')
        ->where('code_four','1')
        ->orderBy('id', 'asc')
        ->get();

        return view('admin.nominaconcepts.create',compact('datenow','formulam','formulaq','formulas','formulae','formulaa','accounts','accounts_two'));
    }

    public function store(Request $request)
    {
       
        $data = request()->validate([
           
            'order'         =>'required',
            'abbreviation'  =>'required',
            'description'   =>'required|max:60',
            'type'          =>'required',
            'sign'          =>'required',

            'calculate'     =>'required',
           

            'minimum'     =>'required',
            'maximum'     =>'required',
            
            
           
        ]);

        $users = new NominaConcept();
        $users->setConnection(Auth::user()->database_name);

        $users->order = request('order');
        $users->abbreviation = request('abbreviation');
        $users->description = request('description');
        $users->type = request('type');
       
        $users->sign = request('sign');
        
        $users->calculate = request('calculate');
        $users->id_formula_m = request('formula_m');
        $users->id_formula_s = request('formula_s');
        $users->id_formula_q = request('formula_q');
        $users->id_formula_e = request('formula_e');
        $users->id_formula_a = request('formula_a');
        $users->account_name = request('cuenta_contable');
        $users->asignation = request('asignation');
        $users->prestations = request('prestations');

        $valor_sin_formato_minimum = str_replace(',', '.', str_replace('.', '', request('minimum')));
        $valor_sin_formato_maximum = str_replace(',', '.', str_replace('.', '', request('maximum')));


        $users->minimum = $valor_sin_formato_minimum;
        $users->maximum = $valor_sin_formato_maximum;


        $users->status =  "1";
       
       

        $users->save();

        return redirect('/nominaconcepts')->withSuccess('Registro Exitoso!');
    }



    public function edit($id)
    {

        $var  = NominaConcept::on(Auth::user()->database_name)->find($id);


        $formulam = NominaFormula::on(Auth::user()->database_name)
        ->where('type','M')
        ->orderBy('id','asc')->get();

        $formulaq = NominaFormula::on(Auth::user()->database_name)
        ->where('type','Q')
        ->orderBy('id','asc')->get();

        $formulas = NominaFormula::on(Auth::user()->database_name)
        ->where('type','S')
        ->orderBy('id','asc')->get();

        $formulae = NominaFormula::on(Auth::user()->database_name)
        ->where('type','E')
        ->orderBy('id','asc')->get();

        $formulaa = NominaFormula::on(Auth::user()->database_name)
        ->where('type','A')
        ->orderBy('id','asc')->get();

        $accounts = '';

        $accounts = Account::on(Auth::user()->database_name)
        ->where('code_one','2')
        ->where('code_two','1')
        ->where('code_three','2')
        ->where('code_four','2')
        ->orderBy('id', 'asc')
        ->get();

        $accounts_two = Account::on(Auth::user()->database_name)
        ->where('code_one','6')
        ->where('code_two','1')
        ->where('code_three','1')
        ->where('code_four','1')
        ->orderBy('id', 'asc')
        ->get();

        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');

       // dd($var);
        return view('admin.nominaconcepts.edit',compact('var','datenow','formulam','formulaq','formulas','formulae','formulaa','accounts','accounts_two'));
        
    }

   



    public function update(Request $request,$id)
    {
       
        $vars =  NominaConcept::on(Auth::user()->database_name)->find($id);
        $var_status = $vars->status;
      
      
        $data = request()->validate([
           
            'order'         =>'required',
            'abbreviation'         =>'required',
            'description'   =>'required|max:60',
            'type'          =>'required',
            'sign'          =>'required',

            'calculate'     =>'required',
           

            'minimum'     =>'required',
            'maximum'     =>'required',
            
            
           
        ]);

        $var = NominaConcept::on(Auth::user()->database_name)->findOrFail($id);

        $var->order = request('order');
        $var->abbreviation = request('abbreviation');
        $var->description = request('description');
        $var->type = request('type');
       
        $var->sign = request('sign');
        
        $var->calculate = request('calculate');
        $var->id_formula_m = request('formula_m');
        $var->id_formula_s = request('formula_s');
        $var->id_formula_q = request('formula_q');
        $var->id_formula_e = request('formula_e');
        $var->id_formula_a = request('formula_a');
        $var->account_name = request('cuenta_contable');
        $var->asignation = request('asignation');
        $var->prestations = request('prestations');

        $valor_sin_formato_minimum = str_replace(',', '.', str_replace('.', '', request('minimum')));
        $valor_sin_formato_maximum = str_replace(',', '.', str_replace('.', '', request('maximum')));


        $var->minimum = $valor_sin_formato_minimum;
        $var->maximum = $valor_sin_formato_maximum;
       
        if(request('status') == null){
            $var->status = $var_status;
        }else{
            $var->status = request('status');
        }
       

        $var->save();


        return redirect('/nominaconcepts')->withSuccess('Registro Guardado Exitoso!');

    }


}
