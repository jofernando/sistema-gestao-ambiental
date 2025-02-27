<?php

namespace App\Http\Controllers\WebServiceCaixa;

use App\Http\Controllers\Controller;
use App\Models\BoletoCobranca;
use App\Models\Requerimento;
use App\Models\WebServiceCaixa\AlterarBoletoRemessa;
use App\Models\WebServiceCaixa\BaixarBoletoRemessa;
use App\Models\WebServiceCaixa\ErrorRemessaException;
use App\Models\WebServiceCaixa\GerirBoletoRemessa;
use App\Models\WebServiceCaixa\IncluirBoletoRemessa;
use App\Models\WebServiceCaixa\Pessoa;
use Illuminate\Support\Facades\Storage;

class XMLCoderController extends Controller
{
    /**
     * Gera o boleto objeto do requerimento e inclui o arquivo de remessa.
     *
     * @param Requerimento $requerimento
     * @return BoletoCobranca $boleto
     */
    public function gerarIncluirBoleto(Requerimento $requerimento)
    {
        $pagador = new Pessoa();
        $beneficiario = new Pessoa();

        $pagador->gerarPagador($requerimento->empresa);
        $beneficiario->gerarBeneficiario();

        $data_vencimento = now()->addDays(30)->format('Y-m-d');

        $boleto = new IncluirBoletoRemessa([
            'data_vencimento' => $data_vencimento,
            'requerimento_id' => $requerimento->id,
        ]);
        $boleto->save();

        $boleto->setAttributes([
            'codigo_beneficiario' => $beneficiario->cod_beneficiario,
            'data_vencimento' => $data_vencimento,
            'valor' => $requerimento->valor,
            'pagador' => $pagador,
            'beneficiario' => $beneficiario,
            'tipo_juros_mora' => 'VALOR_POR_DIA',
            'valor_juros_mora' => 0.01,
            'data_multa' => $data_vencimento,
            'valor_multa' => 0.79,
            'mensagens_compensacao' => $requerimento->gerarMensagemCompesacao(),
        ]);

        $boleto->salvarArquivo($boleto->gerarRemessa());
        $boleto->update();

        return $boleto;
    }

    /**
     * Envia o arquivo de remessa incluir boleto para o WebService da Caixa e gera a resposta e salva no boleto objeto,
     * ou trata a exceção lançada.
     *
     * @param BoletoCobranca $boleto
     * @return void
     * @throws ErrorRemessaException
     */
    public function incluirBoletoRemessa(BoletoCobranca $boleto)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => IncluirBoletoRemessa::URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
            CURLOPT_POSTFIELDS => file_get_contents(storage_path('') . '/app/' . $boleto->caminho_arquivo_remessa),
            CURLOPT_HTTPHEADER => [
                'SoapAction: INCLUI_BOLETO',
                'Content-Type: text/plain',
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $resultado = (new IncluirBoletoRemessa())->xmlToArray($response);

        if (! array_key_exists('COD_RETORNO', $resultado) || ! is_array($resultado['COD_RETORNO']) || ! array_key_exists('DADOS', $resultado['COD_RETORNO'])) {
            throw new ErrorRemessaException($response);
        }
        switch ($resultado['COD_RETORNO']['DADOS']) {
            case 0:
                $boleto->salvarArquivoResposta($response);
                $boleto->save();
                $this->salvarRespostaIncluirBoletoRemessa($boleto, $resultado);
                break;
            default:
                throw new ErrorRemessaException($resultado['RETORNO']);
        }
    }

    /**
     * Salva a resposta de incluir boleto ao boleto objeto.
     *
     * @param BoletoCobranca $boleto
     * @param array $resultado
     * @return void
     */
    private function salvarRespostaIncluirBoletoRemessa(BoletoCobranca $boleto, $resultado)
    {
        $boleto = BoletoCobranca::find($boleto->id);
        $boleto->codigo_de_barras = $resultado['CODIGO_BARRAS'];
        $boleto->linha_digitavel = $resultado['LINHA_DIGITAVEL'];
        $boleto->nosso_numero = $resultado['NOSSO_NUMERO'];
        $boleto->URL = $resultado['URL'];
        $boleto->update();
    }

    /**
     * Salva a resposta de alterar boleto ao boleto objeto.
     *
     * @param BoletoCobranca $boleto
     * @param array $resultado
     * @return void
     */
    private function salvarRespostaAlterarBoletoRemessa(BoletoCobranca $boleto, array $resultado)
    {
        $boleto = BoletoCobranca::find($boleto->id);
        $boleto->codigo_de_barras = $resultado['CODIGO_BARRAS'];
        $boleto->linha_digitavel = $resultado['LINHA_DIGITAVEL'];
        $boleto->URL = $resultado['URL'];
        $boleto->update();
    }

    /**
     * Gerar e envia o alterar boleto.
     *
     * @param BoletoCobranca $boleto
     * @return void
     * @throws ErrorRemessaException
     */
    public function gerarAlterarBoleto(BoletoCobranca $boleto)
    {
        $pagador = new Pessoa();
        $beneficiario = new Pessoa();
        $remessa_alterar_boleto = new AlterarBoletoRemessa();

        $pagador->gerarPagador($boleto->requerimento->empresa);
        $beneficiario->gerarBeneficiario();
        $data_vencimento = now()->addDays(30)->format('Y-m-d');
        $remessa_alterar_boleto->setAttributes([
            'codigo_beneficiario' => $beneficiario->cod_beneficiario,
            'data_vencimento' => $data_vencimento,
            'valor' => $boleto->requerimento->valor,
            'pagador' => $pagador,
            'beneficiario' => $beneficiario,
            'tipo_juros_mora' => 'VALOR_POR_DIA',
            'valor_juros_mora' => 0.01,
            'data_multa' => $data_vencimento,
            'valor_multa' => 0.79,
            'mensagens_compensacao' => $boleto->requerimento->gerarMensagemCompesacao(),
            'nosso_numero' => $boleto->nosso_numero,
            'numero_do_documento' => strval($boleto->id),
        ]);

        $caminho = 'remessas/alterar_boleto_remessa_' . $boleto->id . '.xml';
        Storage::put($caminho, $remessa_alterar_boleto->gerarRemessa());
        $boleto->update();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => GerirBoletoRemessa::URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
            CURLOPT_POSTFIELDS => file_get_contents(storage_path('') . '/app/' . $caminho),
            CURLOPT_HTTPHEADER => [
                'SoapAction: ALTERA_BOLETO',
                'Content-Type: text/plain',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $resultado = (new AlterarBoletoRemessa())->xmlToArray($response);

        if (! array_key_exists('COD_RETORNO', $resultado) || ! is_array($resultado['COD_RETORNO']) || ! array_key_exists('DADOS', $resultado['COD_RETORNO'])) {
            throw new ErrorRemessaException($response);
        }
        switch ($resultado['COD_RETORNO']['DADOS']) {
            case 0:
                $boleto->salvarArquivoRespostaAlterarBoleto($response);
                $boleto->save();
                $this->salvarRespostaAlterarBoletoRemessa($boleto, $resultado);
                break;
            default:
                throw new ErrorRemessaException($resultado['RETORNO']);
        }
    }

    public function gerarBaixarBoleto(BoletoCobranca $boleto)
    {
        $pagador = new Pessoa();
        $beneficiario = new Pessoa();
        $baixar_boleto = new BaixarBoletoRemessa();

        $pagador->gerarPagador($boleto->requerimento->empresa);
        $beneficiario->gerarBeneficiario();
        $baixar_boleto->setAttributes([
            'codigo_beneficiario' => $beneficiario->cod_beneficiario,
            'beneficiario' => $beneficiario,
            'nosso_numero' => $boleto->nosso_numero,
        ]);

        $caminho = 'remessas/baixar_boleto_remessa_' . $boleto->id . '.xml';
        Storage::put($caminho, $baixar_boleto->gerarRemessa());
        $boleto->update();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => GerirBoletoRemessa::URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
            CURLOPT_POSTFIELDS => file_get_contents(storage_path('') . '/app/' . $caminho),
            CURLOPT_HTTPHEADER => [
                'SoapAction: ALTERA_BOLETO',
                'Content-Type: text/plain',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $resultado = (new BaixarBoletoRemessa())->xmlToArray($response);

        if (! array_key_exists('COD_RETORNO', $resultado) || ! is_array($resultado['COD_RETORNO']) || ! array_key_exists('DADOS', $resultado['COD_RETORNO'])) {
            throw new ErrorRemessaException($response);
        }
        switch ($resultado['COD_RETORNO']['DADOS']) {
            case 0:
                $boleto->salvarArquivoRespostaBaixarBoleto($response);
                $boleto->status_pagamento = BoletoCobranca::STATUS_PAGAMENTO_ENUM['cancelado'];
                $boleto->save();
                break;
            default:
                throw new ErrorRemessaException($resultado['RETORNO']);
        }
    }
}
