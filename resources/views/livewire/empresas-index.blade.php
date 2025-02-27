<div class="col-md-9">
    <div class="form-row justify-content-between">
        <div class="col-md-4">
            <h4 class="card-title">Empresas/Serviços</h4>
        </div>
        <div class="col-md-7 d-flex justify-content-end">
            <input wire:model="search" class="form-control w-100" type="search" placeholder="Busque pelo nome da empresa ou pelo CNPJ/CPF">
        </div>
    </div>
    <div class="card card-borda-esquerda" style="width: 100%;">
        <div class="card-body">
            <div div class="form-row">
                @if(session('success'))
                    <div class="col-md-12" style="margin-top: 5px;">
                        <div class="alert alert-success" role="alert">
                            <p>{{session('success')}}</p>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="col-md-12" style="margin-top: 5px;">
                        <div class="alert alert-danger" role="alert">
                            <p>{{session('error')}}</p>
                        </div>
                    </div>
                @endif
            </div>
            <div class="table-responsive">
            <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nome</th>
                            <th scope="col">CNPJ/CPF</th>
                            <th scope="col">Empresário</th>
                            <th scope="col">Grupo</th>
                            <th scope="col">Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $i => $empresa)
                            <tr>
                                <th scope="row">{{ ($empresas->currentpage()-1) * $empresas->perpage() + $loop->index + 1 }}</th>
                                <td>{{$empresa->nome}}</td>
                                <td>{{$empresa->cpf_cnpj}}</td>
                                <td>{{$empresa->user->name}}</td>
                                <td>{{$empresa->cnaes()->first() ? $empresa->cnaes()->first()->setor->nome : "Sem cnae cadastrado"}}</td>
                                <td>
                                    <a  href="{{route('empresas.show', $empresa)}}" style="cursor: pointer; margin-left: 2px;"><img class="icon-licenciamento" width="20px;" src="{{asset('img/Visualizar.svg')}}"  alt="Visualizar a empresa" title="Visualizar a empresa"></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
            </table>
            </div>
            @if($empresas->first() == null)
                <div class="col-md-12 text-center" style="font-size: 18px;">
                    {{__('Nenhuma empresa cadastrada')}}
                </div>
            @endif
        </div>
    </div>
    <div class="form-row justify-content-center">
        <div class="col-md-10">
            {{$empresas->links()}}
        </div>
    </div>
</div>
