<?php
/**
 * Description of class
 *
 * @author L.Casaburi
 */
class ServiceFatturaInCloud  {
    
    public $conn;
    
    function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function existFattura($gestoreId, $articolo_nome){
        $sql = "SELECT * FROM FatturaInCloudAgenzia
                WHERE Articolo = '$articolo_nome' AND GestoreId = $gestoreId";
        $row =  $this->conn->query_first($sql);
        if(isset($row['FatturaId']) && $row['FatturaId'] != ''){
            return true;
        } else {
            return false;
        }
    }
    
    
    
    public function inviaFatturaAgenzia($verificato, $gestoreId, $nome, $indirizzo_via, $indirizzo_cap, $indirizzo_provincia, $indirizzo_citta,
        $paese, $paese_iso, $lingua, $piva, $cf, $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto,
        $articolo_prezzo_lordo, $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
        $email, $tel, $fax, $fa_data, $fattura_numero, $progressivo, $fattura_sigla){
            
            if($verificato == 1) {
                $pagamento_data_saldo = "";
                $pagamento_data_saldo_f = "";
                $pagamento_metodo = "not";
                $statoFattura = 'NON SALDATO';
                $pagamento_data_scadenza_f = date('Y-m-d', strtotime(date('Y-m-d'). ' + 3 day'));
            } else {
                $pagamento_data_saldo = $pagamento_data_saldo;
                $pagamento_data_saldo_f = date("Y-m-d");
                $pagamento_metodo = "BPER BANCA - IBAN IT22P0538716500000003371325";
                $statoFattura = 'SALDATO';
                $pagamento_data_scadenza_f = $pagamento_data_saldo_f;
            }
            $fa_data_f = date("Y-m-d");
            
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);
            
            $url = Config::$servicesUrl.'fattureincloud/fattura?token='.Config::$servicesToken;
            
            $data = array(
                'nome' => $nome,
                'indirizzo_via' => $indirizzo_via,
                'indirizzo_cap' => $indirizzo_cap,
                'indirizzo_citta' => $indirizzo_citta,
                'indirizzo_provincia' => $indirizzo_provincia,
                'paese' => $paese,
                'paese_iso' => $paese_iso,
                'lingua' => $lingua,
                'piva' => $piva,
                'cf' => $cf,
                'metodo_pagamento' => 'bonifico',
                'metodo_titoloN' => 'IBAN',
                'metodo_descN' => 'IT22P0538716500000003371325',
                'articolo_nome' => $articolo_nome,
                'articolo_quantita' => $articolo_quantita,
                'articolo_nota' => $articolo_nota,
                'articolo_prezzo_netto' => $articolo_prezzo_netto,
                'articolo_prezzo_lordo' => $articolo_prezzo_lordo,
                'articolo_cod_iva' => 14,
                'pagamento_data_scadenza' => $pagamento_data_scadenza,
                'pagamento_importo' => $pagamento_importo,
                'pagamento_metodo' => $pagamento_metodo,
                'pagamento_data_saldo' => $pagamento_data_saldo,
                'codice_destinatario' => $codice_destinatario,
                'pec' => $pec,
                'fa_istituto_credito' => 'BPER BANCA',
                'fa_iban' => 'IT22P0538716500000003371325',
                'fa_beneficiario' => 'Onebus S.r.l.',
                'email' => $email,
                'tel' => $tel,
                'fax' => $fax,
                'fa_data' => $fa_data,
                'fattura_numero' => $fattura_sigla,
                'fattura_progressivo' => $progressivo
            );
            
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $result = json_decode($result, true);
            
            $nome = str_replace("'", "\'", $nome);
            $indirizzo_via = str_replace("'", "\'", $indirizzo_via);
            $indirizzo_citta = str_replace("'", "\'", $indirizzo_citta);
            
            if(isset($result['data']['id']) && $result['data']['id'] > 0) {
                $fatturaInCloudId = $result['data']['id'];
                $sql = "INSERT INTO FatturaInCloudAgenzia
                        (Verificata, GestoreId, FatturaInCloudId, Nome, IndirizzoVia, IndirizzoCap, IndirizzoCitta, IndirizzoProvincia, Paese, PaeseISO, Lingua, PIVA, CF, Articolo,
                        ArticoloCodIva, ArticoloQuantita, ArticoloPrezzoNetto, ArticoloPrezzoLordo, PagamentoData, PagamentoScadenza, PagamentoImporto, CodiceDestinatario, PEC,
                        Email, Tel, Fax, FatturaData, FatturaStato, FatturaNumero, Anno, Progressivo)
                    VALUES
    					($verificato, $gestoreId, $fatturaInCloudId, '$nome', '$indirizzo_via', '$indirizzo_cap', '$indirizzo_citta', '$indirizzo_provincia', '$paese',
                        '$paese_iso', '$lingua', '$piva', '$cf', '$articolo_nome',
                        14, $articolo_quantita, $articolo_prezzo_netto, $articolo_prezzo_lordo,
                        '$pagamento_data_saldo_f', '$pagamento_data_scadenza_f', $pagamento_importo, '$codice_destinatario', '$pec',
                        '$email', '$tel', '$fax', '$fa_data_f', '$statoFattura', '$fattura_numero', ".date('Y').", $progressivo)";
                $this->conn->query($sql);
            }
            return $result;
    }
    
    public function inviaFatturaCliente($nome, $indirizzo_via, $indirizzo_cap, $indirizzo_provincia, $indirizzo_citta,
        $paese, $paese_iso, $lingua, $piva, $cf, $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto,
        $articolo_prezzo_lordo, $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
        $email, $tel, $fax, $fa_data, $fattura_numero, $progressivo, $fattura_sigla, $tipo, $statoFattura, 
        $prenotazioneCodice, $prenotazioneId, $prenotazioneMovimentoId, $fatturaInCloudIdRimborsata = null, $metodo_pagamento){
        
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);
            
            if(isset($prenotazioneCodice)) {
                $prenotazioneCodice = "'$prenotazioneCodice'";
            } else {
                $prenotazioneCodice = "null";
            }
            if(isset($fatturaInCloudIdRimborsata)) {
                $fatturaInCloudIdRimborsata = "'$fatturaInCloudIdRimborsata'";
            } else {
                $fatturaInCloudIdRimborsata = "null";
            }
            if(isset($prenotazioneMovimentoId)) {
                $prenotazioneMovimentoId = "$prenotazioneMovimentoId";
            } else {
                $prenotazioneMovimentoId = "null";
            }
            
//             switch ($tipo) {
//                 case 'invoice':
//                     echo "i equals 0";
//                     break;
//                 case 'receipt':
//                     echo "i equals 1";
//                     break;
//                 case 'credit_note':
//                     echo "i equals 2";
//                     break;
//             }
            
            switch ($statoFattura) {
                case 'NON SALDATO':
                    $pagamento_data_saldo = "";
                    $pagamento_data_saldo_f = "";
                    $pagamento_metodo = "not";
                    $statoFattura = 'NON SALDATO';
                    $pagamento_data_scadenza_f = date('Y-m-d', strtotime(date('Y-m-d'). ' + 30 day'));
                    
                    if($tipo == 'credit_note' ) {
//                         $metodo_pagamento = 'bonifico';
                        $metodo_titoloN = '';
                        $metodo_descN = '';
                        $fa_istituto_credito = '';
                        $fa_iban = '';
                        $fa_beneficiario = $nome;
                    } else {
//                         $metodo_pagamento = 'bonifico';
                        $metodo_titoloN = '';
                        $metodo_descN = '';
                        $fa_istituto_credito = '';
                        $fa_iban = '';
                        $fa_beneficiario = 'Onebus S.r.l.';
                    }
                    break;
                case 'SALDATO':
                    $pagamento_data_saldo = $pagamento_data_saldo;
                    $pagamento_data_saldo_f = date("Y-m-d");
                    if($tipo == 'credit_note' ) {
                        $pagamento_metodo = $metodo_pagamento;
                        $metodo_titoloN = '';
                        $metodo_descN = '';
                        $fa_istituto_credito = '';
                        $fa_iban = '';
                        $fa_beneficiario = $nome;
                    } else {
                        $pagamento_metodo = $metodo_pagamento;
                        $metodo_titoloN = '';
                        $metodo_descN = '';
                        $fa_istituto_credito = '';
                        $fa_iban = '';
                        $fa_beneficiario = 'Onebus S.r.l.';
                    }
                    $statoFattura = 'SALDATO';
                    $pagamento_data_scadenza_f = $pagamento_data_saldo_f;
                    break;
            }
            $fa_data_f = date("Y-m-d");

            $url = Config::$servicesUrl.'fattureincloud/fatturaCliente?token='.Config::$servicesToken;
            
            $data = array(
                'nome' => $nome,
                'indirizzo_via' => $indirizzo_via,
                'indirizzo_cap' => $indirizzo_cap,
                'indirizzo_citta' => $indirizzo_citta,
                'indirizzo_provincia' => $indirizzo_provincia,
                'paese' => $paese,
                'paese_iso' => $paese_iso,
                'lingua' => $lingua,
                'piva' => $piva,
                'cf' => $cf,
                'metodo_pagamento' => $metodo_pagamento,
                'metodo_titoloN' => $metodo_titoloN,
                'metodo_descN' => $metodo_descN,
                'articolo_nome' => $articolo_nome,
                'articolo_quantita' => $articolo_quantita,
                'articolo_nota' => $articolo_nota,
                'articolo_prezzo_netto' => $articolo_prezzo_netto,
                'articolo_prezzo_lordo' => $articolo_prezzo_lordo,
                'articolo_cod_iva' => 3,
                'pagamento_data_scadenza' => $pagamento_data_scadenza,
                'pagamento_importo' => $pagamento_importo,
                'pagamento_metodo' => $pagamento_metodo,
                'pagamento_data_saldo' => $pagamento_data_saldo,
                'codice_destinatario' => $codice_destinatario,
                'pec' => $pec,
                'fa_istituto_credito' => $fa_istituto_credito,
                'fa_iban' => $fa_iban,
                'fa_beneficiario' => $fa_beneficiario,
                'email' => $email,
                'tel' => $tel,
                'fax' => $fax,
                'fa_data' => $fa_data,
                'fattura_numero' => $fattura_sigla,
                'fattura_progressivo' => $progressivo,
                'tipo' => $tipo //invoice //receipt //credit_note
            );
//             var_dump($data);
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $result = json_decode($result, true);
//             var_dump($result);
            $nome = str_replace("'", "\'", $nome);
            $indirizzo_via = str_replace("'", "\'", $indirizzo_via);
            $indirizzo_citta = str_replace("'", "\'", $indirizzo_citta);
            
            if(isset($result['data']['id']) && $result['data']['id'] > 0) {
                $fatturaInCloudId = $result['data']['id'];
                $sql = "INSERT INTO FatturaInCloudViaggiatore
                        (Tipo, PrenotazioneId, PrenotazioneMovimentoId, FatturaInCloudId, Nome, IndirizzoVia, IndirizzoCap, IndirizzoCitta, IndirizzoProvincia, Paese, PaeseISO, Lingua, PIVA, CF, Articolo,
                        ArticoloCodIva, ArticoloQuantita, ArticoloPrezzoNetto, ArticoloPrezzoLordo, PagamentoData, PagamentoScadenza, PagamentoImporto, CodiceDestinatario, PEC,
                        Email, Tel, Fax, FatturaData, FatturaStato, FatturaNumero, Anno, Progressivo, PrenotazioneCodice, FatturaInCloudIdRimborsata)
                    VALUES
    					('$tipo', $prenotazioneId, $prenotazioneMovimentoId, $fatturaInCloudId, '$nome', '$indirizzo_via', '$indirizzo_cap', '$indirizzo_citta', '$indirizzo_provincia', '$paese',
                        '$paese_iso', '$lingua', '$piva', '$cf', '$articolo_nome',
                        14, $articolo_quantita, $articolo_prezzo_netto, $articolo_prezzo_lordo,
                        '$pagamento_data_saldo_f', '$pagamento_data_scadenza_f', $pagamento_importo, '$codice_destinatario', '$pec',
                        '$email', '$tel', '$fax', '$fa_data_f', '$statoFattura', '$fattura_numero', ".date('Y').", $progressivo, $prenotazioneCodice, $fatturaInCloudIdRimborsata)";
//                 echo $sql;
                $this->conn->query($sql);
            }
            return $result;
    }
}
?>
