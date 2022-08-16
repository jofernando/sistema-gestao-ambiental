<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ValorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->role == User::ROLE_ENUM['secretario'];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'porte' => 'required',
            'potencial_poluidor' => 'required',
            'tipo_de_licença' => 'required',
            'valor' => 'required',
        ];
    }
}
