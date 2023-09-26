@extends('admin.layouts.dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <div class="card">
                <form id="formPost" method="POST" action="{{ route('report_payments.store') }}">
                    @csrf

                <input type="hidden" name="id_client" value="{{$client->id ?? null}}" readonly>
                <input type="hidden" name="id_vendor" value="{{$vendor->id ?? null}}" readonly>

                <div class="card-header text-center h4">
                    Reporte Cobros
                </div>
                
                <div class="card-body">
                        <div class="form-group row">
                            
                            <label for="date_end" class="col-sm-1 col-form-label text-md-right">Desde:</label>

                            <div class="col-sm-3">
                                <input id="date_ini" type="date" class="form-control @error('date_ini') is-invalid @enderror" name="date_ini" value="{{ date('Y-m-d', strtotime($date_ini ?? $datenow))}}" required autocomplete="date_ini">

                                @error('date_ini')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <label for="date_end" class="col-sm-1 col-form-label text-md-right">Hasta:</label>

                            <div class="col-sm-3">
                                <input id="date_end" type="date" class="form-control @error('date_end') is-invalid @enderror" name="date_end" value="{{ date('Y-m-d', strtotime($date_end ?? $datenow))}}" required autocomplete="date_end">

                                @error('date_end')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            @if (isset($client))
                                <label id="client_label1" for="clients" class="col-sm-1 text-md-right">Cliente:</label>
                                <label id="client_label2" name="id_client" value="{{ $client->id }}" for="clients" class="col-sm-3">{{ $client->name }}</label>
                            @endif
                            @if (isset($vendor))
                                <label id="vendor_label1" for="vendors" class="col-sm-1 text-md-right">Vendedor:</label>
                                <label id="vendor_label2" name="id_vendor" value="{{ $vendor->id }}" for="vendors" class="col-sm-3">{{ $vendor->name }}</label>
                            @endif
                            
                            <div id="client_label3" class="form-group col-sm-1">
                                <a id="route_select" href="{{ route('report_payments.selectClient') }}" title="Seleccionar Cliente"><i class="fa fa-eye"></i></a>  
                            </div>
                            <div class="col-sm-2">
                                <select class="form-control" name="coin" id="coin">
                                    @if(isset($coin))
                                        <option disabled selected value="{{ $coin }}">{{ $coin }}</option>
                                        <option disabled  value="{{ $coin }}">-----------</option>
                                    @else
                                        <option disabled selected value="bolivares">Moneda</option>
                                    @endif
                                    
                                    <option  value="bolivares">Bolívares</option>
                                    <option value="dolares">Dólares</option>
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <button type="submit" class="btn btn-primary ">
                                    Buscar
                                </button>
                            </div>
                           
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-2 offset-sm-1">
                                <select class="form-control" name="type" id="type">
                                    @if (isset($client))
                                        <option value="todo">Todo</option>
                                        <option selected value="Cliente">Por Cliente</option>
                                        <option value="Vendedor">Por Vendedor</option>
                                    @elseif (isset($Vendedor))
                                        <option value="todo">Todo</option>
                                        <option value="Cliente">Por Cliente</option>
                                        <option selected value="Vendedor">Por Vendedor</option>
                                    @else
                                        <option selected value="todo">Todo</option>
                                        <option value="Cliente">Por Cliente</option>
                                        <option value="Vendedor">Por Vendedor</option>
                                    @endif
                                </select>
                            </div>
                           
                            <div class="col-sm-3  dropdown mb-4">
                                <button class="btn btn-success" type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false"
                                    aria-expanded="false">
                                    <i class="fas fa-bars"></i>
                                    Exportaciones
                                </button>
                                <div class="dropdown-menu animated--fade-in"
                                    aria-labelledby="dropdownMenuButton">
                                    <a href="#" onclick="exportToExcel();" class="dropdown-item bg-light">Exportar a Excel</a> 
                                </div>
                            </div> 
                        </div>
                    </form>
                        <div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item" src="{{ route('report_payments.pdf',[$coin ?? 'bolivares',$date_end ?? $datenow,$date_ini ?? $datenow,$typeperson ?? 'ninguno',$client->id ?? $vendor->id ?? null]) }}" allowfullscreen></iframe>
                          </div>
                        
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
@section('javascript')

    <script>
    $('#dataTable').DataTable({
        "ordering": false,
        "order": [],
        'aLengthMenu': [[-1, 50, 100, 150, 200], ["Todo",50, 100, 150, 200]]
    });

    
    function exportToExcel(){
        var old_action = document.getElementById("formPost").action;
        document.getElementById("formPost").action = "{{ route('export_reports.payment_cobro') }}";
        document.getElementById("formPost").submit();
        document.getElementById("formPost").action = old_action;
    }

    let client  = "<?php echo $client->name ?? 0 ?>";  
    let vendor  = "<?php echo $vendor->name ?? 0 ?>"; 

    if(client != 0){
        $("#client_label1").show();
        $("#client_label2").show();
        $("#client_label3").show();
    }else if(vendor != 0){
        $("#client_label1").show();
        $("#client_label2").show();
        $("#client_label3").show();
    }else{
        $("#client_label1").hide();
        $("#client_label2").hide();
        $("#client_label3").hide();
    }
    

    $("#type").on('change',function(){
            type = $(this).val();
            
            if(type == 'todo'){
                $("#client_label1").hide();
                $("#client_label2").hide();
                $("#client_label3").hide();
            }else if(type == 'vendor'){
                document.getElementById("route_select").href = "{{ route('report_payments.selectVendor') }}";
                $("#client_label1").show();
                $("#client_label2").show();
                $("#client_label3").show();
            }else{
                document.getElementById("route_select").href = "{{ route('report_payments.selectClient') }}";
                $("#client_label1").show();
                $("#client_label2").show();
                $("#client_label3").show();
            }
        });

    </script> 

    @isset($vendor)
        <script>
            document.getElementById("route_select").href = "{{ route('report_payments.selectVendor') }}";
        </script>
    @endisset
@endsection