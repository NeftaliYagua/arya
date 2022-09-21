@extends('admin.layouts.dashboard')

@section('content')


  <!-- /.container-fluid -->
  {{-- VALIDACIONES-RESPUESTA--}}
  @include('admin.layouts.success')   {{-- SAVE --}}
  @include('admin.layouts.danger')    {{-- EDITAR --}}
  @include('admin.layouts.delete')    {{-- DELELTE --}}
  {{-- VALIDACIONES-RESPUESTA --}}
<!-- DataTales Example -->
<div class="container-fluid">
    <div class="row py-lg-2">
        <div class="col-sm-3 offset-sm-2  dropdown mb-2">
            <button class="btn btn-success" type="button"
                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false"
                aria-expanded="false">
                <i class="fas fa-bars"></i>
                Exportaciones
            </button>
            <div class="dropdown-menu animated--fade-in"
                aria-labelledby="dropdownMenuButton">
                <a href="#" data-toggle="modal" data-target="#PDFModalAccount" class="dropdown-item bg-light">Exportar a PDF</a>
                <a href="#" data-toggle="modal" data-target="#ExcelModalAccount" class="dropdown-item bg-light">Exportar a Excel</a> 
            </div>
        </div> 
        <div class="col-md-2">
            <a href="{{ route('bankmovements') }}" class="btn btn-info" title="Transferencia">Bancos</a>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary" data-toggle="modal" data-target="#deleteModal" >Subir Movimientos</button>
        </div>
    </div>
</div>
<br>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center font-weight-bold h3">Listado de Movimientos de Caja y Bancos</div>

                <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-light2 table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Referencia</th>
                                    <th class="text-center">Nº</th>
                                    <th class="text-center">Cuenta</th>
                                    <th class="text-center">Descripción</th>
                                    <th class="text-center">Debe</th>
                                    <th class="text-center">Haber</th>
                                    <th class="text-center">Debe USD</th>
                                    <th class="text-center">Haber USD</th>
                                    <th class="text-center"></th>
                                </tr>
                                </thead>
                                
                                <tbody>
                                    @if (empty($detailvouchers))
                                    @else
                                        @foreach ($detailvouchers as $var)
                                        <tr>
                                        <td>{{ date('d-m-Y', strtotime( $var->header_date ?? '')) }}</td>
                                        <td class="text-center">{{$var->id_header_voucher ?? ''}}</td>
                                        <td>{{$var->account_code_one ?? ''}}.{{$var->account_code_two ?? ''}}.{{$var->account_code_three ?? ''}}.{{$var->account_code_four ?? ''}}</td>
                                        <td>{{$var->account_description ?? ''}}</td>
                                        <td>{{$var->header_description ?? ''}}</td>
                                       
                                        <td>{{ number_format($var->debe, 2, ',', '.')}}</td>
                                        <td>{{ number_format($var->haber, 2, ',', '.')}}</td>

                                        <td>{{ number_format($var->debe / $var->tasa, 2, ',', '.')}}</td>
                                        <td>{{ number_format($var->haber / $var->tasa, 2, ',', '.')}}</td>
                                      
                                        <td>
                                            <a href="{{ route('bankmovements.bankmovementPdfDetail',$var->id_header_voucher ?? null) }}" class="show" title="Ver Comprobante"><i class="fa fa-print"></i></a>  
                                            @if (Auth::user()->role_id  == '1' || $eliminarmiddleware  == '1')
                                            <a href="{{ route('bankmovements.delete',$var->id_header_voucher ?? null) }}" class="delete" title="Eliminar"><i class="fa fa-trash text-danger"></i></a>  
                                            @endif
                                        </td>  
                                        
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        </div>
    </div>
</div>
</div>
</div>
</div>
</div>

   
<div class="modal fade" id="PDFModalAccount" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Seleccione el periodo</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="POST" id="formPostPdfAccountOrdenDePago" action="{{ route('bankmovements.pdfAccountBankMovement') }}"   target="print_popup" onsubmit="window.open('about:blank','print_popup','width=1000,height=800');">
                @csrf
            <div class="modal-body">
                <div class="form-group row">
                    <label for="account" class="col-md-2 col-form-label text-md-right">Cuenta:</label>
                        <div class="col-md-8">
                            <select class="form-control" id="id_account" name="id_account" >
                                <option value="">Selecciona una Cuenta</option>
                                @foreach($accounts as $var)
                                    <option value="{{ $var->id }}">{{ $var->description }}</option>
                                @endforeach
                              
                            </select>
                        </div>
                </div>
                <div class="form-group row">
                    <label id="coinlabel" for="coin" class="col-md-2 col-form-label text-md-right">Moneda:</label>
                    <div class="col-md-6">
                        <select class="form-control" name="coin" id="coin">
                            <option selected value="bolivares">Bolívares</option>
                            <option value="dolares">Dolares</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="date_end" class="col-sm-2 col-form-label text-md-right">Desde</label>
    
                    <div class="col-sm-6">
                        <input id="date_begin" type="date" class="form-control @error('date_begin') is-invalid @enderror" name="date_begin" value="{{  $date_begin ?? $datenow ?? '' }}" required autocomplete="date_begin">
    
                        @error('date_begin')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label for="date_end" class="col-sm-2 col-form-label text-md-right">hasta </label>
    
                    <div class="col-sm-6">
                        <input id="date_begin" type="date" class="form-control @error('date_end') is-invalid @enderror" name="date_end" value="{{ $date_end ?? $datenow ?? '' }}" required autocomplete="date_end">
    
                        @error('date_end')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
              
            </div>
                <div class="modal-footer">
                    <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-info" title="Buscar">Enviar</button>  
                    </div>
            </form>
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    
<div class="modal fade" id="ExcelModalAccount" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Seleccione el periodo / Exportar a Excel</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="POST" id="formPostPdfAccountOrdenDePago" action="{{ route('export_reports.bankmovements') }}"  >
                @csrf
            <div class="modal-body">
                <div class="form-group row">
                    <label for="account" class="col-md-2 col-form-label text-md-right">Cuenta:</label>
                        <div class="col-md-8">
                            <select class="form-control" id="id_account" name="id_account" >
                                <option value="">Selecciona una Cuenta</option>
                                @foreach($accounts as $var)
                                    <option value="{{ $var->id }}">{{ $var->description }}</option>
                                @endforeach
                              
                            </select>
                        </div>
                </div>
                <div class="form-group row">
                    <label id="coinlabel" for="coin" class="col-md-2 col-form-label text-md-right">Moneda:</label>
                    <div class="col-md-6">
                        <select class="form-control" name="coin" id="coin">
                            <option selected value="bolivares">Bolívares</option>
                            <option value="dolares">Dolares</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="date_end" class="col-sm-2 col-form-label text-md-right">Desde</label>
    
                    <div class="col-sm-6">
                        <input id="date_begin" type="date" class="form-control @error('date_begin') is-invalid @enderror" name="date_begin" value="{{  $date_begin ?? $datenow ?? '' }}" required autocomplete="date_begin">
    
                        @error('date_begin')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label for="date_end" class="col-sm-2 col-form-label text-md-right">hasta </label>
    
                    <div class="col-sm-6">
                        <input id="date_begin" type="date" class="form-control @error('date_end') is-invalid @enderror" name="date_end" value="{{ $date_end ?? $datenow ?? '' }}" required autocomplete="date_end">
    
                        @error('date_end')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
              
            </div>
                <div class="modal-footer">
                    <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-info" title="Buscar">Enviar</button>  
                    </div>
            </form>
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Delete Warning Modal -->
<div class="modal modal-danger fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Subir Movimientos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="fileForm" method="POST" action="{{ route('importmovimientos') }}" enctype="multipart/form-data" >
                    @csrf
                    <div class="form-group col-md-8">
                        <label for="inputState">Banco</label>
                        <select id="inputState" class="form-control" name="banco" id="banco">
                          <option value='0'>Seleccione..</option>
                          <option value='1'>Bancamiga</option>
                          <option value='2'>Banesco</option>
                          <option value='3'>Mercantil</option>
                          <option value='4'>Chase</option>
                          <option value='5'>BOFA</option>
                        </select>
                      </div>

                      <div class="form-group col-md-12">
                        <input id="file" type="file" value="import" name="file" class="form-control-file" accept=".xls">

                      </div>
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Importar</button>
            </div>
            </form>
        </div>
    </div>
  </div>
@endsection
@section('javascript')
    <script>
    $('#dataTable').DataTable({
        "ordering": false,
        "order": [],
        'aLengthMenu': [[50, 100, 150, -1], [50, 100, 150, "All"]]
    });
    </script> 
@endsection