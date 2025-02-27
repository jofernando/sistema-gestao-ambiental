<?php

namespace App\Http\Controllers;

use App\Http\Requests\DenunciaRequest;
use App\Models\Denuncia;
use App\Models\Empresa;
use App\Models\FotoDenuncia;
use App\Models\Relatorio;
use App\Models\User;
use App\Models\VideoDenuncia;
use App\Notifications\DenunciaRecebida;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class DenunciaController extends Controller
{
    public function index($filtro)
    {
        $this->authorize('isSecretarioOrAnalista', User::class);

        $denuncias_registradas = collect();
        $denuncias_aprovadas = collect();
        $denuncias_arquivadas = collect();
        $user = auth()->user();
        switch ($user->role) {
            case User::ROLE_ENUM['secretario']:
                switch ($filtro) {
                    case 'pendentes':
                        $denuncias = Denuncia::where('aprovacao', '1')->orderBy('created_at', 'DESC')->paginate(20);
                        break;
                    case 'deferidas':
                        $denuncias_concluidas = Denuncia::whereRelation('visita.relatorio', 'aprovacao', Relatorio::APROVACAO_ENUM['aprovado'])->orderBy('created_at', 'DESC')->paginate(20);
                        $denuncias = Denuncia::where('aprovacao', '2')->whereNotIn('id', $denuncias_concluidas->pluck('id'))->orderBy('created_at', 'DESC')->paginate(20);
                        break;
                    case 'indeferidas':
                        $denuncias = Denuncia::where('aprovacao', '3')->orderBy('created_at', 'DESC')->paginate(20);
                        break;
                    case 'concluidas':
                        $denuncias = Denuncia::whereRelation('visita.relatorio', 'aprovacao', Relatorio::APROVACAO_ENUM['aprovado'])->orderBy('created_at', 'DESC')->paginate(20);
                        break;
                }
                break;
            case User::ROLE_ENUM['analista']:
                switch ($filtro) {
                    case 'pendentes':
                        $denuncias = Denuncia::where([['aprovacao', '1'], ['analista_id', $user->id]])->orderBy('created_at', 'DESC')->paginate(20);
                        break;
                    case 'deferidas':
                        $denuncias = Denuncia::where([['aprovacao', '2'], ['analista_id', $user->id]])->orderBy('created_at', 'DESC')->paginate(20);
                        break;
                    case 'indeferidas':
                        $denuncias = Denuncia::where([['aprovacao', '3'], ['analista_id', $user->id]])->orderBy('created_at', 'DESC')->paginate(20);
                        break;
                }
                break;
        }
        $analistas = User::analistas();

        return view('denuncia.index', compact('denuncias', 'analistas', 'filtro'));
    }

    public function infoDenuncia(Request $request)
    {
        $this->authorize('isSecretario', User::class);

        $denuncia = Denuncia::find($request->denuncia_id);

        $denunciaInfo = [
            'id' => $denuncia->id,
            'analista_atribuido' => $denuncia->analista ? $denuncia->analista : null,
            'analista_visita' => $denuncia->visita ? $denuncia->visita->analista : null,
            'marcada' => $denuncia->visita ? $denuncia->visita->data_marcada : null,
            'realizada' => $denuncia->visita ? $denuncia->visita->data_realizada : null,
        ];

        return response()->json($denunciaInfo);
    }

    public function create()
    {
        $empresas = Empresa::all();

        return view('denuncia.create', compact('empresas'));
    }

    public function store(DenunciaRequest $request)
    {
        $data = $request->validated();

        $denuncia = new Denuncia();
        $denuncia->texto = $data['texto'];
        $denuncia->empresa_id = strcmp($data['empresa_id'], 'none') == 0 || empty($data['empresa_id']) ? null : $data['empresa_id'];
        $denuncia->empresa_nao_cadastrada = $data['empresa_nao_cadastrada'] ?? '';
        $denuncia->endereco = $data['endereco'] ?? '';
        $denuncia->denunciante = $data['denunciante'] ?? '';
        $denuncia->aprovacao = Denuncia::APROVACAO_ENUM['registrada'];
        $protocolo = $this->gerarProtocolo($data['texto']);
        $denuncia->protocolo = $protocolo;
        $denuncia->save();

        if (array_key_exists('imagem', $data)) {
            $count = count($data['imagem']);
            for ($i = 0; $i < $count; $i++) {
                $foto_denuncia = new FotoDenuncia();
                $foto_denuncia->denuncia_id = $denuncia->id;
                $foto_denuncia->comentario = $data['comentario'][$i] ?? '';
                $foto_denuncia->caminho = $data['imagem'][$i]->store("denuncias/{$denuncia->id}/imagens");
                $foto_denuncia->save();
            }
        }

        if ($request->has('arquivoFile')) {
            $denuncia->arquivo = $request->arquivoFile->store("denuncias/{$denuncia->id}/arquivo");
            $denuncia->save();
        }

        if (array_key_exists('video', $data)) {
            $count = count($data['video']);
            for ($i = 0; $i < $count; $i++) {
                $video_denuncia = new VideoDenuncia();
                $video_denuncia->denuncia_id = $denuncia->id;
                $video_denuncia->comentario = $data['comentario'][$i] ?? '';
                $video_denuncia->caminho = $data['video'][$i]->store("denuncias/{$denuncia->id}/videos");
                $video_denuncia->save();
            }
        }

        if (auth()->user()) {
            Notification::send(auth()->user(), new DenunciaRecebida($protocolo));
        }

        return redirect()->back()->with(['success' => 'Denúncia cadastrada com sucesso!', 'protocolo' => $protocolo]);
    }

    public function edit()
    {
        return redirect()->route('denuncias.create');
    }

    public function update()
    {
        return redirect()->route('denuncias.create');
    }

    public function imagem(FotoDenuncia $foto)
    {
        return Storage::download($foto->caminho);
    }

    public function imagensDenuncia(Request $request)
    {
        $imagens = FotoDenuncia::where('denuncia_id', $request->id)->get();
        $caminhos = [];

        foreach ($imagens as $key) {
            array_push($caminhos, $key->caminho);
        }

        $data = [
            'success' => true,
            'table_data' => $caminhos,
        ];
        echo json_encode($data);
    }

    public function avaliarDenuncia(Request $request)
    {
        $msg = '';
        $denuncia = Denuncia::find($request->denunciaId);
        $this->authorize('isSecretarioOrAnalista', User::class);

        if ($request->aprovar == 'true') {
            $denuncia->aprovacao = Denuncia::APROVACAO_ENUM['aprovada'];
            $msg = 'Denuncia deferida com sucesso!';
        } elseif ($request->aprovar == 'false') {
            $denuncia->aprovacao = Denuncia::APROVACAO_ENUM['arquivada'];
            $msg = 'Denuncia indeferida com sucesso!';
        }

        $denuncia->update();

        return redirect()->back()->with(['success' => $msg]);
    }

    public function baixarArquivo(Denuncia $denuncia)
    {
        $this->authorize('isSecretarioOrAnalista', User::class);
        if ($denuncia->arquivo && Storage::exists($denuncia->arquivo)) {
            return Storage::download($denuncia->arquivo);
        }
        abort(404);
    }

    /**
     * Atribuir uma denúncia a um analaista.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function atribuirAnalistaDenuncia(Request $request)
    {
        $this->authorize('isSecretario', User::class);

        $request->validate([
            'denuncia_id_analista' => 'required',
            'analista' => 'required',
        ]);

        $denuncia = Denuncia::find($request->denuncia_id_analista);
        $denuncia->analista_id = $request->analista;
        $denuncia->update();

        return redirect()->back()->with(['success' => 'Denúncia atribuida com sucesso ao analista.']);
    }

    public function statusDenuncia(Request $request)
    {
        $denuncia = Denuncia::where('protocolo', $request->protocolo)->first();
        if ($denuncia == null) {
            return redirect()->back()->with(['error' => 'A denúncia informada não se encontra no banco de registro de denúncias.']);
        }

        return view('denuncia.status', compact('denuncia'));
    }

    private function gerarProtocolo($texto)
    {
        $protocolo = null;
        do {
            $protocolo = substr(str_shuffle(Hash::make($texto)), 0, 20);
            $check = Denuncia::where('protocolo', $protocolo)->first();
        } while ($check != null);

        return $protocolo;
    }

    public function get(Request $request)
    {
        $denuncia = Denuncia::find($request->denuncia_id);
        $denunciaInfo = [
            'id' => $denuncia->id,
            'texto' => $denuncia->texto,
        ];

        return response()->json($denunciaInfo);
    }
}
