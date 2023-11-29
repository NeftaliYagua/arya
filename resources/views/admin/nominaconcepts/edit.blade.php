@extends('admin.layouts.dashboard')

@section('content')

    <!-- container-fluid -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="row py-lg-2">
            <div class="col-md-6">
                <h2>Editar Concepto</h2>
            </div>

        </div>
    </div>
    <!-- /container-fluid -->

    {{-- VALIDACIONES-RESPUESTA--}}
@include('admin.layouts.success')   {{-- SAVE --}}
@include('admin.layouts.danger')    {{-- EDITAR --}}
@include('admin.layouts.delete')    {{-- DELELTE --}}
{{-- VALIDACIONES-RESPUESTA --}}

@if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form  method="POST"   action="{{ route('nominaconcepts.update',$var->id) }}" enctype="multipart/form-data" >
                @method('PATCH')
                @csrf()
                <div class="container py-2">
                    <div class="row">
                        <div class="col-12 ">
                            <div class="form-group row">
                                <label for="abbreviation" class="col-md-2 col-form-label text-md-right">Concepto Abreviado</label>

                                <div class="col-md-3">
                                    <input id="abbreviation" type="text" class="form-control @error('abbreviation') is-invalid @enderror" name="abbreviation" value="{{ $var->abbreviation ?? old('abbreviation') }}" maxlength="60" required autocomplete="abbreviation">

                                    @error('abbreviation')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="description" class="col-md-2 col-form-label text-md-right">Descripción</label>

                                <div class="col-md-3">
                                    <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ $var->description ?? old('description') }}" maxlength="60" required autocomplete="description">

                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <label for="order" class="col-md-1 col-form-label text-md-right">Orden</label>

                                <div class="col-md-2">
                                    <input id="order" type="number" class="form-control @error('order') is-invalid @enderror" name="order" value="{{ $var->order ?? old('order') }}" maxlength="60" required autocomplete="order">

                                    @error('order')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <label for="sign" class="col-md-2 col-form-label text-md-right">Calcular <br>con Nómina:</label>
                                <div class="col-md-2">
                                    <select class="form-control" id="calculate" name="calculate" title="calculate">
                                        @if($var->calculate == "N")
                                            <option value="N">No</option>
                                        @else
                                            <option value="S">Si</option>
                                        @endif
                                        <option value="nulo">----------------</option>

                                        <div class="dropdown">
                                            <option value="N">No</option>
                                            <option value="S">Si</option>
                                        </div>


                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="sign" class="col-md-2 col-form-label text-md-right">Signo:</label>
                                <div class="col-md-3">
                                    <select class="form-control" id="sign" name="sign" title="sign">
                                        @if($var->sign == "A")
                                            <option value="A">Asignación</option>
                                        @else
                                            <option value="D">Deducción</option>
                                        @endif
                                        <option value="nulo">----------------</option>

                                        <div class="dropdown">
                                            <option value="A">Asignación</option>
                                            <option value="D">Deducción</option>
                                        </div>


                                    </select>
                                </div>
                                <label for="type" class="col-md-2 col-form-label text-md-right">Tipo:</label>
                                <div class="col-md-3">
                                    <select class="form-control" id="type" name="type" title="type">

                                            <option value="{{ $var->type }}">{{ $var->type }}</option>

                                        <option value="nulo">----------------</option>

                                        <div class="dropdown">
                                            <option value="Primera Quincena">Primera Quincena</option>
                                            <option value="Segunda Quincena">Segunda Quincena</option>
                                            <option value="Semanal">Semanal</option>
                                            <option value="Quincenal">Quincenal</option>
                                            <option value="Mensual">Mensual</option>
                                            <option value="Especial">Especial</option>

                                        </div>


                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="formula_m" class="col-md-2 col-form-label text-md-right">Fórmula Mensual:</label>

                                <div class="col-md-6">
                                    <select class="form-control" id="formula_m" name="formula_m" title="formula_mensual">
                                        @foreach ($formulam as $formula_m)
                                            @if ($var->id_formula_m == $formula_m->id)
                                            <option value="{{ $formula_m->id }}">{{ $formula_m->description }}</option>
                                            @endif
                                        @endforeach
                                    <option value="">----------------</option>

                                    <div class="dropdown">
                                        @foreach ($formulam as $m)
                                            <option value="{{ $m->id }}">{{ $m->description }}</option>
                                        @endforeach
                                    </div>

                                </select>
                                </div>
                                <label for="formula_q" class="col-md-2 col-form-label text-md-right">(30 dias)</label>
                            </div>
                            <div class="form-group row">
                                <label for="formula_s" class="col-md-2 col-form-label text-md-right">Fórmula Semanal:</label>

                                <div class="col-md-6">
                                   <select class="form-control" id="formula_s" name="formula_s" title="formula_semanal">
                                        @foreach ($formulas as $formula_s)
                                            @if ($var->id_formula_s == $formula_s->id)
                                            <option value="{{ $formula_s->id }}">{{ $formula_s->description }}</option>
                                            @endif
                                        @endforeach
                                    <option value="">----------------</option>

                                    <div class="dropdown">
                                        @foreach ($formulas as $s)
                                            <option value="{{ $s->id }}">{{ $s->description }}</option>
                                        @endforeach
                                    </div>

                                </select>
                                </div>
                                <label for="formula_q" class="col-md-2 col-form-label text-md-right">(4 Semanas)</label>
                            </div>
                            <div class="form-group row">
                                <label for="formula_q" class="col-md-2 col-form-label text-md-right">Fórmula Quincenal:</label>

                                <div class="col-md-6">
                                    <select class="form-control" id="formula_q" name="formula_q" title="formula_quincenal">
                                         @foreach ($formulaq as $formula_q)
                                            @if ($var->id_formula_q == $formula_q->id)
                                            <option value="{{ $formula_q->id }}">{{ $formula_q->description }}</option>
                                            @endif
                                         @endforeach
                                     <option value="">----------------</option>

                                     <div class="dropdown">
                                         @foreach ($formulaq as $s)
                                             <option value="{{ $s->id }}">{{ $s->description }}</option>
                                         @endforeach
                                     </div>

                                 </select>
                                 </div>
                                 <label for="formula_q" class="col-md-2 col-form-label text-md-right">(15 dias)</label>
                            </div>
                            <div class="form-group row">
                                <label for="formula_e" class="col-md-2 col-form-label text-md-right">Fórmula Especial:</label>

                                <div class="col-md-6">
                                    <select class="form-control" id="formula_e" name="formula_e" title="Formula Especial">
                                         @foreach ($formulae as $formula_q)
                                            @if ($var->id_formula_e == $formula_q->id)
                                            <option value="{{ $formula_q->id }}">{{ $formula_q->description }}</option>
                                            @endif
                                         @endforeach
                                     <option value="">----------------</option>

                                     <div class="dropdown">
                                         @foreach ($formulae as $s)
                                             <option value="{{ $s->id }}">{{ $s->description }}</option>
                                         @endforeach
                                     </div>

                                 </select>
                                 </div>
                                 <label for="formula_q" class="col-md-2 col-form-label text-md-right">(Especial)</label>
                            </div>
                            <div class="form-group row">
                                <label for="formula_a" class="col-md-2 col-form-label text-md-right">Fórmula Asignación:</label>

                                <div class="col-md-6">
                                    <select class="form-control" id="formula_a" name="formula_a" title="Fórmula Asignación General">
                                         @foreach ($formulaa as $formula_q)
                                            @if ($var->id_formula_a == $formula_q->id)
                                            <option value="{{ $formula_q->id }}">{{ $formula_q->description }}</option>
                                            @endif
                                         @endforeach
                                     <option value="">----------------</option>

                                     <div class="dropdown">
                                         @foreach ($formulaa as $s)
                                             <option value="{{ $s->id }}">{{ $s->description }}</option>
                                         @endforeach
                                     </div>

                                 </select>
                                 </div>
                                 <label for="formula_q" class="col-md-2 col-form-label text-md-right">(Asignación General)</label>
                            </div>
                            <div class="form-group row">
                                <label for="cuenta_contable" class="col-md-2 col-form-label text-md-right">Cuenta Contable:</label>

                                <div class="col-md-6">
                                    <select class="form-control" name="cuenta_contable" id="cuenta_contable">
                                        <option value="">Seleccionar Cuenta Contable</option>
                                        @if (isset($accounts_one))
                                        @foreach ($accounts_one as $account_one)

                                                @if ($var->account_name == $account_one->description)
                                                <option selected value="{{ $account_one->description }}">{{$account_one->code_one.'.'.$account_one->code_two.'.'.$account_one->code_three.'.'.$account_one->code_four.'.'.str_pad($account_one->code_five, 3, "0", STR_PAD_LEFT)}} {{ $account_one->description }}</option>
                                                @else
                                                <option value="{{ $account_one->description }}">{{$account_one->code_one.'.'.$account_one->code_two.'.'.$account_one->code_three.'.'.$account_one->code_four.'.'.str_pad($account_one->code_five, 3, "0", STR_PAD_LEFT)}} {{ $account_one->description }}</option>
                                                @endif
                                        @endforeach
                                        @endif

                                        @if (isset($accounts))
                                            @foreach ($accounts as $account)

                                                    @if ($var->account_name == $account->description)
                                                    <option selected value="{{ $account->description }}">{{$account->code_one.'.'.$account->code_two.'.'.$account->code_three.'.'.$account->code_four.'.'.str_pad($account->code_five, 3, "0", STR_PAD_LEFT)}} {{ $account->description }}</option>
                                                    @else
                                                    <option value="{{ $account->description }}">{{$account->code_one.'.'.$account->code_two.'.'.$account->code_three.'.'.$account->code_four.'.'.str_pad($account->code_five, 3, "0", STR_PAD_LEFT)}} {{ $account->description }}</option>
                                                    @endif
                                            @endforeach
                                        @endif

                                        @if (isset($accounts_tree))
                                            @foreach ($accounts_tree as $account_tree)

                                                    @if ($var->account_name == $account_tree->description)
                                                    <option selected value="{{ $account_tree->description }}">{{$account_tree->code_one.'.'.$account_tree->code_tree.'.'.$account_tree->code_three.'.'.$account_tree->code_four.'.'.str_pad($account_tree->code_five, 3, "0", STR_PAD_LEFT)}} {{ $account_tree->description }}</option>
                                                    @else
                                                    <option value="{{ $account_tree->description }}">{{$account_tree->code_one.'.'.$account_tree->code_tree.'.'.$account_tree->code_three.'.'.$account_tree->code_four.'.'.str_pad($account_tree->code_five, 3, "0", STR_PAD_LEFT)}} {{ $account_tree->description }}</option>
                                                    @endif
                                            @endforeach
                                        @endif
                                        @if (isset($accounts_two))
                                        @foreach ($accounts_two as $account_two)

                                                @if ($var->account_name == $account_two->description)
                                                <option selected value="{{ $account_two->description }}">{{$account_two->code_one.'.'.$account_two->code_two.'.'.$account_two->code_three.'.'.$account_two->code_four.'.'.str_pad($account_two->code_five, 3, "0", STR_PAD_LEFT)}} {{ $account_two->description }}</option>
                                                @else
                                                <option value="{{ $account_two->description }}">{{$account_two->code_one.'.'.$account_two->code_two.'.'.$account_two->code_three.'.'.$account_two->code_four.'.'.str_pad($account_two->code_five, 3, "0", STR_PAD_LEFT)}} {{ $account_two->description }}</option>
                                                @endif
                                        @endforeach
                                    @endif
                                    </select>
                                </div>

                            </div>


                            <div class="form-group row">

                                    <label for="sign" class="col-md-4 col-form-label text-md-right">Calcular Asignación con Nómina:</label>
                                    <div class="col-md-2">
                                        <select class="form-control" name="asignation" id="asignation">
                                            @if($var->asignation == "N")
                                            <option value="N">No</option>
                                            @else
                                                <option value="S">Si</option>
                                            @endif
                                            <option value="N">----------------</option>

                                            <div class="dropdown">
                                                <option value="N">No</option>
                                                <option value="S">Si</option>
                                            </div>
                                        </select>
                                    </div>


                                    <label for="sign" class="col-md-4 col-form-label text-md-right">Afectar a Prestaciones:</label>
                                    <div class="col-md-2">
                                        <select class="form-control" name="prestations" id="prestations">
                                            @if($var->prestations == "N")
                                            <option value="N">No</option>
                                            @else
                                                <option value="S">Si</option>
                                            @endif
                                            <option value="N">----------------</option>

                                            <div class="dropdown">
                                                <option value="N">No</option>
                                                <option value="S">Si</option>
                                            </div>
                                        </select>
                                    </div>
                            </div>

                            <div style="display: none;">
                                    <div class="form-group row">
                                        <label for="minimum" class="col-md-2 col-form-label text-md-right">Mínimo:</label>

                                        <div class="col-md-4">
                                            <input id="minimum" type="text" class="form-control @error('minimum') is-invalid @enderror" name="minimum"  value="{{ $var->minimum }}" maxlength="60" required autocomplete="off" placeholder='0,00' >

                                            @error('minimum')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="maximum" class="col-md-2 col-form-label text-md-right">Máximo:</label>

                                        <div class="col-md-4">
                                            <input id="maximum" type="text" class="form-control @error('maximum') is-invalid @enderror" name="maximum"  value="{{ $var->maximum }}" maxlength="60" required autocomplete="off" placeholder='0,00'>

                                            @error('maximum')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                            </div>

                            <br>
                                <div class="form-group row justify-content-center">
                                    <div class="form-group col-sm-2">
                                        <button type="submit" class="btn btn-info btn-block"><i class="fa fa-send-o"></i>Registrar</button>
                                    </div>
                                    <div class="form-group col-sm-2">
                                        <a href="{{ route('nominaconcepts') }}" name="danger" type="button" class="btn btn-danger btn-block">Cancelar</a>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                    @endsection

                    @section('validacion')
                    <!-- Se encarga de los input number, el formato -->
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous"></script>

                    <script>
                    $(document).ready(function () {
                        $("#minimum").mask('000.000.000.000.000,00', { reverse: true });
                    });
                    $(document).ready(function () {
                        $("#maximum").mask('000.000.000.000.000,00', { reverse: true });
                    });

                    </script>
                @endsection
