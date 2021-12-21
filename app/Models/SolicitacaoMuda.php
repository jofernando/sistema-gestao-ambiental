<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoMuda extends Model
{
    use HasFactory;
    protected $table = 'solicitacoes_mudas';
    protected $fillable = [
        'protocolo',
        'comentario',
        'motivo_indeferimento',
        'qtd_mudas',
        'status',
    ];

    public const STATUS_ENUM = [
        'registrada' => 1,
        'deferido'   => 2,
        'indeferido' => 3,
    ];

    public function cidadao()
    {
        return $this->belongsTo(Cidadao::class);
    }
}
