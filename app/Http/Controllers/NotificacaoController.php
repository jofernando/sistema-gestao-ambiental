<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificacaoRequest;
use App\Models\FotoNotificacao;
use App\Models\Notificacao;
use App\Models\Visita;
use App\Notifications\NotificacaoCriadaNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class NotificacaoController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @param \App\Models\Empresa  $empresa
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Visita $visita)
    {
        return view('notificacao.create')->with('visita', $visita);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\NotificacaoRequest  $request
     * @param \App\Models\Empresa  $empresa
     *
     * @return \Illuminate\Http\Response
     */
    public function store(NotificacaoRequest $request, Visita $visita)
    {
        $notificacao = new Notificacao();
        $data = $request->validated();
        $notificacao->fill($data);
        $notificacao->empresa_id = $visita->requerimento->empresa->id;
        $notificacao->visita_id = $visita->id;
        $notificacao->save();
        if (array_key_exists("imagem", $data))
        {
            for ($i = 0; $i < count($data['imagem']); $i++) {
                $foto_notificacao = new FotoNotificacao();
                $foto_notificacao->notificacao_id = $notificacao->id;
                $foto_notificacao->comentario = $data['comentario'][$i] ?? "";

                $nomeImg = $data['imagem'][$i]->getClientOriginalName();
                $path = 'notificacoes/' . $notificacao->id . '/';
                Storage::putFileAs('public/' . $path, $data['imagem'][$i], $nomeImg);
                $foto_notificacao->caminho = $path . $nomeImg;
                $foto_notificacao->save();
            }
        }
        Notification::send($visita->requerimento->empresa->user, new NotificacaoCriadaNotification($notificacao, $visita->requerimento));
        return redirect()->action([VisitaController::class, 'index'])->with('success', 'Notificação criada com sucesso');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notificacao  $notificacao
     * @return \Illuminate\Http\Response
     */
    public function show(Notificacao $notificacao)
    {
        return view('notificacao.show', ['notificacao' => $notificacao]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notificacao  $notificacao
     * @return \Illuminate\Http\Response
     */
    public function edit(Notificacao $notificacao)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\NotificacaoRequest  $request
     * @param  \App\Models\Notificacao  $notificacao
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notificacao $notificacao)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notificacao  $notificacao
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notificacao $notificacao)
    {
        //
    }
}
