<x-app-layout>

    <div class="container" style="padding-top: 2rem; padding-bottom: 8rem;">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-row">
                    <div class="col-md-8" style="padding-top: 30px;">
                        <h4 class="card-title">Visualizar solicitação de muda {{$solicitacao->protocolo}}</h4>
                        @can('usuarioInterno', \App\Models\User::class)
                            <h6 class="card-subtitle mb-2 text-muted">Mudas > Visualizar solicitação de muda {{$solicitacao->protocolo}}</h6>
                        @endcan
                    </div>
                    <div class="col-md-4" style="text-align: right; padding-top: 15px;">
                        <a class="btn my-2" href="{{route('mudas.index')}}" style="cursor: pointer;"><img class="icon-licenciamento btn-voltar" src="{{asset('img/back-svgrepo-com.svg')}}"  alt="Voltar" title="Voltar"></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row justify-content-center">
            <div class="col-md-8">
                <div class="card" style="width: 100%;">
                    <div class="card-body">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-md-12 form-group">
                                    <label for="nome">Nome</label>
                                    <input id="nome" class="form-control" type="text" name="nome"
                                        value="{{ $solicitacao->cidadao->user->name }}" autocomplete="nome" disabled>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="qtd_mudas">Quantidade de mudas</label>
                                    <input id="qtd_mudas" class="form-control" type="number" step="0.01" name="qtd_mudas"
                                        value="{{ $solicitacao->qtd_mudas }}" autocomplete="qtd_mudas" disabled>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="email">E-mail</label>
                                    <input id="email" class="form-control" type="text" name="email"
                                        value="{{ $solicitacao->cidadao->user->email }}" autocomplete="email" disabled>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="cpf">{{ __('CPF') }}</label>
                                    <input id="cpf" class="form-control simple-field-data-mask" type="text" name="cpf"
                                        value="{{ $solicitacao->cidadao->cpf }}" autofocus autocomplete="cpf"
                                        data-mask="000.000.000-00" disabled>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="cep">{{ __('CEP') }}</label>
                                    <input id="cep" class="form-control cep" type="text" name="cep" value="{{$solicitacao->cidadao->endereco->cep}}" disabled>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="bairro">{{ __('Bairro') }}</label>
                                    <input id="bairro" class="form-control" type="text" name="bairro" value="{{$solicitacao->cidadao->endereco->bairro}}" disabled>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="rua">{{ __('Rua') }}</label>
                                    <input id="rua" class="form-control" type="text" name="rua" value="{{$solicitacao->cidadao->endereco->rua}}" disabled>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="numero">{{ __('Número') }}</label>
                                    <input id="numero" class="form-control " type="text" name="numero" value="{{$solicitacao->cidadao->endereco->numero}}" disabled>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="cidade">{{ __('Cidade') }}</label>
                                    <input id="cidade" class="form-control" type="text" name="cidade" value="Garanhuns" disabled>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="estado">{{ __('Estado') }}</label>
                                    <select id="estado" class="form-control" type="text" disabled name="estado">
                                        <option selected value="PE">Pernambuco</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-12 form-group">
                                    <label for="complemento">{{ __('Complemento') }}</label>
                                    <input class="form-control" value="{{$solicitacao->cidadao->endereco->complemento}}" type="text" name="complemento" id="complemento" disabled/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-row">
                    <div class="col-md-12" style="margin-bottom:20px">
                        <div class="card shadow bg-white" style="border-radius:12px; border-width:0px;">
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="col-md-12" style="margin-bottom: 0.5rem">
                                        <h5 class="card-title mb-0"
                                            style="font-family:Arial, Helvetica, sans-serif; color:#08a02e; font-weight:bold">
                                            Status da solicitação</h5>
                                    </div>
                                    <div class="col-md-12">
                                        @switch($solicitacao->status)
                                            @case(\App\Models\SolicitacaoMuda::STATUS_ENUM['registrada'])
                                                <h5>Solicitação em análise.</h5>
                                            @break
                                            @case(\App\Models\SolicitacaoMuda::STATUS_ENUM['deferido'])
                                                <h5>Solicitação deferida</h5>
                                            @break
                                            @case(\App\Models\SolicitacaoMuda::STATUS_ENUM['indeferido'])
                                                <h5>Solicitação indeferida</h5>
                                                <p>Motivo: {{$solicitacao->motivo_indeferimento}}</p>
                                            @break
                                        @endswitch
                                    </div>
                                    <div class="col-md-12">
                                        @if(isset($solicitacao->arquivo))
                                            <div style="margin-top: 20px; margin-bottom: 10px;">
                                                <a href="{{route('mudas.documento', ['id' => $solicitacao->id])}}" target="_new">Baixar solicitação</a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
