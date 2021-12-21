<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnderecoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('enderecos')->insert([
            'rua' => 'Rua dos Estados',
            'numero' => '807',
            'bairro' => 'Miranda',
            'cidade' => 'Garanhuns',
            'estado' => 'PE',
            'complemento' => 'Casa',
            'cep' => '55292-000',
        ]);

        DB::table('enderecos')->insert([
            'rua' => 'Rua do Cateté',
            'numero' => '588',
            'bairro' => 'Jardim Monte Líbano',
            'cidade' => 'Garanhuns',
            'estado' => 'PE',
            'complemento' => 'Casa',
            'cep' => '55292-000',
        ]);

        DB::table('enderecos')->insert([
            'rua' => '1ª Travessa Monte Sinai',
            'numero' => '154',
            'bairro' => 'Severiano de Moraes Filho',
            'cidade' => 'Garanhuns',
            'estado' => 'PE',
            'complemento' => 'Casa',
            'cep' => '55299-406',
        ]);
    }
}
