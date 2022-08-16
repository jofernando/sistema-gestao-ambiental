<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
    ];

    public function cnaes()
    {
        return $this->hasMany(Cnae::class, 'setor_id');
    }

    public function setAtributes($input)
    {
        $this->nome = $input['nome'];
        $this->descricao = $input['descricao'];
    }

    public function existemEmpresas()
    {
        foreach ($this->cnaes as $cnae) {
            if ($cnae->existemEmpresas()) {
                return true;
            }
        }

        return false;
    }

    public function deletarCnaes()
    {
        foreach ($this->cnaes as $cnae) {
            $cnae->delete();
        }
    }
}
