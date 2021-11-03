<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Criar programação de visita') }}
        </h2>
    </x-slot>
    <div class="container" style="padding-top: 5rem; padding-bottom: 8rem;">
        <div class="form-row justify-content-center">
            <div class="col-md-10">
                <div class="card" style="width: 100%;">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="col-md-8">
                                <h5 class="card-title">Cadastrar uma visita</h5>
                                <h6 class="card-subtitle mb-2 text-muted">Programação > Criar visita</h6>
                            </div>
                        </div>
                        <form method="POST" id="criar-visita" action="{{route('visitas.store')}}">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:19px;margin-top:10px; margin-bottom:-5px; font-family: 'Roboto', sans-serif;">DADOS DA VISITA</label>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="data_marcada">{{ __('Data') }} <span style="color: red; font-weight: bold;">*</span></label>
                                    <input class="form-control @error('data_marcada') is-invalid @enderror" type="date"  id="data_marcada" name="data_marcada" required autofocus autocomplete="data_marcada">

                                    @error('data_marcada')
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="analista">{{__('Selecione o analista da visita')}}<span style="color: red; font-weight: bold;">*</span></label>
                                    <select name="analista" id="analista" class="form-control @error('analista') is-invalid @enderror" required>
                                        <option value="">-- {{__('Selecione um analista')}} --</option>
                                        @foreach ($analistas as $analista)
                                            <option @if(old('analista') == $analista->id) selected @endif value="{{$analista->id}}">{{$analista->name}}</option>
                                        @endforeach
                                    </select>

                                    @error('analista')
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="requerimento">{{__('Selecione um requerimento')}}<span style="color: red; font-weight: bold;">*</span></label>
                                    <select name="requerimento" id="requerimento" class="form-control @error('requerimento') is-invalid @enderror" required>
                                        <option value="">-- {{__('Selecione um requerimento')}} --</option>
                                        @foreach ($requerimentos as $requerimento)
                                            <option value="{{$requerimento->id}}">{{$requerimento->empresa->nome}} @if($requerimento->tipo == \App\Models\Requerimento::TIPO_ENUM['primeira_licenca'])
                                                {{__('(primeira licença)')}}
                                            @elseif($requerimento->tipo == \App\Models\Requerimento::TIPO_ENUM['renovacao'])
                                                {{__('(renovação)')}}
                                            @elseif($requerimento->tipo == \App\Models\Requerimento::TIPO_ENUM['autorizacao'])
                                                {{__('(autorização)')}}
                                            @endif</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <div class="form-row">
                            <div class="col-md-6"></div>
                            <div class="col-md-6" style="text-align: right">
                                <button type="submit" id="submeterFormBotao" class="btn btn-success" form="criar-visita" style="width: 100%">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
