<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Milon\Barcode\DNS1D;

class DanfeController extends Controller
{
    public function uploadFile(Request $request)
    {
        if($request->hasFile('xmlNfe')){
            
            $xml=simplexml_load_file($request->xmlNfe);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);

            $nfeChaveAcesso = $array['protNFe']['infProt']['chNFe'] ?? null;
            $nfeNumero = $array['NFe']['infNFe']['ide']['nNF'] ?? null;
            $nfeSerie = $array['NFe']['infNFe']['ide']['serie'] ?? null;
            $nfeProtocolo = $array['protNFe']['infProt']['nProt'] ?? null;
            $nfeDataHoraAutorizacaoUso = $array['protNFe']['infProt']['dhRecbto'] ?? null;
            $nfeDataHoraEmissao = $array['NFe']['infNFe']['ide']['dhEmi'] ?? null;
            $nfeDadosEmitente = $array['NFe']['infNFe']['emit'] ?? null;
            $nfeDadosDestinatario = $array['NFe']['infNFe']['dest'] ?? null;
            $nfeDadosProdutos = $array['NFe']['infNFe']['det'] ?? null;
            $nfeValorTotal = $array['NFe']['infNFe']['total']['ICMSTot']['vNF'] ?? null;
            $nfeInformacaoAdicional = $array['NFe']['infNFe']['infAdic']['infCpl'] ?? null;

            if(!self::validarChaveAcessoNFe($nfeChaveAcesso)){
                return redirect()->back()->with('alert', 'Chave acesso XML Invalida');
            }

            if($nfeChaveAcesso == null or $nfeNumero == null or $nfeSerie == null or $nfeProtocolo == null or $nfeDataHoraAutorizacaoUso == null or $nfeDataHoraEmissao == null or $nfeDadosEmitente == null or $nfeDadosDestinatario == null or $nfeDadosProdutos == null or $nfeValorTotal == null or $nfeInformacaoAdicional == null){
                return redirect()->back()->with('alert', 'Arquivo XML Invalido');
            }

            $barcodeBase64 = DNS1D::getBarcodePNG($nfeNumero, 'C128');

            return view('etiqueta', compact('nfeChaveAcesso', 'nfeNumero', 'nfeSerie', 'nfeProtocolo', 'nfeDataHoraAutorizacaoUso', 'nfeDataHoraEmissao', 'nfeDadosEmitente', 'nfeDadosDestinatario', 'nfeDadosProdutos', 'nfeValorTotal', 'nfeInformacaoAdicional', 'barcodeBase64'));
         }
        
        return redirect()->back()->with('alert', 'Arquivo XML não enviado!');
    }

    public function validarChaveAcessoNFe($chaveAcesso) {
        // Verifica se a chave de acesso tem 44 caracteres
        if ($chaveAcesso == null) {
            return false;
        }

        // Verifica se a chave de acesso tem 44 caracteres
        if (strlen($chaveAcesso) != 44) {
            return false;
        }
        
        // Verifica se a chave de acesso contém apenas números
        if (!preg_match('/^[0-9]+$/', $chaveAcesso)) {
            return false;
        }
        
        // Verifica o dígito verificador da chave de acesso
        $soma = 0;
        $peso = 2;
        for ($i = 42; $i >= 0; $i--) {
            $soma += $chaveAcesso[$i] * $peso;
            $peso++;
            if ($peso == 10) {
                $peso = 2;
            }
        }
        $resto = $soma % 11;
        $dv = ($resto == 0 || $resto == 1) ? 0 : (11 - $resto);
        if ($dv != $chaveAcesso[43]) {
            return false;
        }
        
        // Chave de acesso válida
        return true;
    }

 
}
