<?php

namespace App\Models;

use App\Http\Requests\NoticiaRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Noticia extends Model
{
    use HasFactory;

    public $fillable = [
        'imagem_principal',
        'titulo',
        'texto',
        'link',
        'publicada',
    ];

    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    /**
     * Seta os atributos do objeto
     *
     * @param App\Http\Requests\NoticiaRequest
     * @return void
     */
    public function setAtributes(NoticiaRequest $request)
    {
        $this->titulo = $request->input('título');
        $this->texto = $request->texto;
        $this->publicada = $request->publicar == 'on';
        $this->destaque = $request->destaque == 'on';
        $this->link = $this->gerarLinkDivulgacao($request->input('título'));
        $this->autor_id = auth()->user()->id;
    }

    /**
     * Salva a imagem principal da noticia em seu respectivo diretório
     *
     * @param $file
     * @return void
     */
    public function salvarImagem($file)
    {
        $this->deletar_imagem();

        $caminho_noticias = 'noticias/' . $this->id . '/';
        $documento_nome = $file->getClientOriginalName();
        Storage::putFileAs('public/' . $caminho_noticias, $file, $documento_nome);
        $this->imagem_principal = $caminho_noticias . $file->getClientOriginalName();
    }

    /**
     * Chega se a noticia sofreu alguma atualização.
     *
     * @return bool
     */
    public function exibirDatas()
    {
        if ((new Carbon($this->created_at)) == (new Carbon($this->updated_at))) {
            return true;
        }

        return false;
    }

    /**
     * Retorna a data de criação.
     *
     * @return string $ultimaAtualizacao
     */
    public function dataPublicado()
    {
        $ultima = now()->diff(new Carbon($this->created_at));
        if ($ultima->d >= 1) {
            return 'Publicada em ' . (new Carbon($this->created_at))->format('d/m/Y') . ' às ' . (new Carbon($this->created_at))->format('H:m');
        } elseif ($ultima->h >= 1 && $ultima->h < 2) {
            return 'Publicada há ' . $ultima->h . ' hora atrás.';
        } elseif ($ultima->h >= 2) {
            return 'Publicada há ' . $ultima->h . ' horas atrás.';
        } elseif ($ultima->m <= 1) {
            return 'Publicada agora.';
        } elseif ($ultima->m > 1) {
            return 'Publicada há ' . $ultima->m . ' minutos atrás.';
        }
    }

    /**
     * Retorna a ultima atualização da notícia
     *
     * @return string $ultimaAtualizacao
     */
    public function ultimaAtualizacao()
    {
        $ultima = now()->diff(new Carbon($this->updated_at));
        if ($ultima->d >= 1) {
            return 'Atualizado em ' . (new Carbon($this->updated_at))->format('d/m/Y às H:m');
        } elseif ($ultima->h >= 1 && $ultima->h < 2) {
            return 'Última atualização à ' . $ultima->h . ' hora atrás.';
        } elseif ($ultima->h >= 2) {
            return 'Última atualização à ' . $ultima->h . ' horas atrás.';
        } elseif ($ultima->m <= 1) {
            return 'Atualizado agora.';
        } elseif ($ultima->m > 1) {
            return 'Última atualização à ' . $ultima->m . ' minutos atrás.';
        }
    }

    /**
     * Gera o link de divulgação da notícia
     *
     * @param string $string
     * @return string $string
     */
    private function gerarLinkDivulgacao($string)
    {
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $complemento = preg_replace(['/[ ]/', '/[^A-Za-z0-9\-]/'], ['', ''], $string);

        return route('welcome') . '/noticias/' . $complemento;
    }

    /**
     * Deleta a imagem principal, se ela existor
     *
     * @return void
     */
    public function deletar_imagem()
    {
        if ($this->imagem_principal != null) {
            if (Storage::disk()->exists('public/' . $this->imagem_principal)) {
                Storage::delete('public/' . $this->imagem_principal);
            }
        }
    }
}
