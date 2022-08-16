<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicencaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'visita' => 'required',
            'requerimento' => 'required',
            'licença' => 'required|file|mimes:pdf|max:2048',
            'data_de_validade' => 'required|date',
            'tipo_de_licença' => 'required',
        ];
    }
}
