<?php

namespace App\Imports;

use App\ExpensesDetail;
use App\Product;
use App\Http\Controllers\GlobalController;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductImport implements ToModel,WithHeadingRow, SkipsOnError
{
    use Importable;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $user       =   auth()->user();
        $date = Carbon::now();
        /*Validator::make($rows->toArray(), [
            '*.name' => 'required',
            '*.email' => 'required',
            '*.password' => 'required',
        ])->validate();*/
        
        
            $buscar_product = Product::on(Auth::user()->database_name)->find($row['id']);


            if (empty($buscar_product) & $row['id'] != '') {

                $product = DB::connection(Auth::user()->database_name)->table('products')->insert([
                    'id'                    => $row['id'],
                    'segment_id'            => $row['id_segmento'], 
                    'subsegment_id'         => $row['id_subsegmento'], 
                    'twosubsegment_id'      => $row['id_twosubsegment'] ?? null, 
                    'threesubsegment_id'    => $row['id_threesubsegment'] ?? null,
                    'id_account'            => null,
                    'unit_of_measure_id'    => $row['id_unidadmedida'], 
                    'code_comercial'        => $row['codigo_comercial'], 
                    'type'                  => $row['tipo_mercancia_o_servicio'], 
                    'description'           => $row['descripcion'], 
                    'price'                 => $row['precio'], 
                    'price_buy'             => $row['precio_compra'], 
                    'cost_average'          => 0, 
                    'photo_product'         => null, 
                    'money'                 => $row['moneda_d_o_bs'], 
                    'exento'                => $row['exento_1_o_0'], 
                    'islr'                  => $row['islr_1_o_0'], 
                    'id_user'               => $user->id,
                    'special_impuesto'      => 0,
                    'status'                => 1,
                    'created_at'            => $date,
                    'updated_at'            => $date
                ]);       


                //$product->setConnection(Auth::user()->database_name);
                
                
                $global = new GlobalController; 
                $global->transaction_inv('entrada',$row['id'],'Entrada Masiva de Inventario',$row['cantidad_actual'],$row['precio'],$date,1,1,0,0,0,0,0);

                /*if($product->status == '1'){
                    return $product;
                }*/
                
            }



            return;
    }

    



    public function onError(\Throwable $e)
    {
        // Handle the exception how you'd like.
    }
}
