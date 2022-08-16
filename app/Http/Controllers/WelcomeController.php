<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Noticia;

class WelcomeController extends Controller
{
    public function index()
    {
        $noticias = Noticia::where([['destaque', true], ['publicada', true]])->get();
        $empresas = Empresa::all();

        return view('welcome', compact('noticias', 'empresas'));
    }
}
