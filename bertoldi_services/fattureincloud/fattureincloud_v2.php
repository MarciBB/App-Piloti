<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath."/main_include.php");

$config=new Config();

$run=$config->load();

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$handler = explode('/', $_GET['handler']);

$token = $_GET['token'];

if (!function_exists('http_response_code')) {
    function http_response_code($newcode = NULL) {
        static $code = 200;
        if ($newcode !== NULL) {
            header('X-PHP-Response-Code: ' . $newcode, true, $newcode);
            if (!headers_sent()) {
                $code = $newcode;
            }
        }
        return $code;
    }
}

//verifica token
if($token != Config::$accessTokenAPI){
    http_response_code(404);
    
    echo json_encode(
        array("message" => "Token not valid.")
        );
    exit();
}

switch ($handler[0]) {
    
    case 'status': {
        // required headers
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');
        //         header("Content-Type: application/json; charset=UTF-8");
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // The request is using the POST method
            
            // set response code - 200 OK
            http_response_code(200);
            
            echo json_encode(array(
                'version' => '2.0.0',
                'status' => 'OK'
            ));
            
        } else {
            // set response code - 404 Not found
            http_response_code(404);
            
            echo json_encode(
                array("message" => "Method not valid.")
                );
        }
        
        break;
    }
    
    case 'info': {
        // required headers
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // The request is using the POST method
            
            // set response code - 200 OK
            http_response_code(200);
            $responce =  info();
            
            echo json_encode($responce);
            
        } else {
            // set response code - 404 Not found
            http_response_code(404);
            
            echo json_encode(
                array("message" => "Method not valid.")
                );
        }
        
        break;
    }
    
    case 'vat_types': {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            http_response_code(200);
            $responce = vatTypes();
            echo json_encode($responce);
        } else {
            http_response_code(404);
            echo json_encode(
                array("message" => "Method not valid.")
            );
        }
        
        break;
    }
    
    case 'fatturaAgenzia': {
        // required headers
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // The request is using the POST method
            
            // set response code - 200 OK
            http_response_code(200);
            $responce =  fattura($_POST['nome'], $_POST['indirizzo_via'], $_POST['indirizzo_cap'], $_POST['indirizzo_citta'], $_POST['indirizzo_provincia'], $_POST['paese'], $_POST['paese_iso'],
                $_POST['lingua'], $_POST['piva'], $_POST['cf'], 'Bonifico', 'IBAN', 'IT22P0538716500000003371325',
                $_POST['articolo_nome'], $_POST['articolo_quantita'], $_POST['articolo_nota'], $_POST['articolo_prezzo_netto'], $_POST['articolo_prezzo_lordo'], 14,
                $_POST['pagamento_data_scadenza'], $_POST['pagamento_importo'], $_POST['pagamento_data_saldo'], $_POST['codice_destinatario'], $_POST['pec'],
                'BPER BANCA', 'IT22P0538716500000003371325', 'Onebus S.r.l.', $_POST['email'], $_POST['tel'], $_POST['fax'], $_POST['fa_data']
                );
            
            echo json_encode($responce);
            
        } else {
            // set response code - 404 Not found
            http_response_code(404);
            
            echo json_encode(
                array("message" => "Method not valid.")
                );
        }
        
        break;
    }
    
    
    case 'fattura': {
        // required headers
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // The request is using the POST method
            
            // set response code - 200 OK
            http_response_code(200);
            $metodo_titoloN = null;
            if(isset($_POST['metodo_titoloN'])) {
                $metodo_titoloN = $_POST['metodo_titoloN'];
            }
            $metodo_descN = null;
            if(isset($_POST['metodo_descN'])) {
                $metodo_descN = $_POST['metodo_descN'];
            }
            $fattura_numero = null;
            if(isset($_POST['fattura_numero'])) {
                $fattura_numero = $_POST['fattura_numero'];
            }
            $fattura_progressivo = null;
            if(isset($_POST['fattura_progressivo'])) {
                $fattura_progressivo = $_POST['fattura_progressivo'];
            }
            
            $responce =  fattura($_POST['nome'], $_POST['indirizzo_via'], $_POST['indirizzo_cap'], $_POST['indirizzo_citta'], $_POST['indirizzo_provincia'], $_POST['paese'], $_POST['paese_iso'],
                $_POST['lingua'], $_POST['piva'], $_POST['cf'], $_POST['metodo_pagamento'], $metodo_titoloN, $metodo_descN,
                $_POST['articolo_nome'], $_POST['articolo_quantita'], $_POST['articolo_nota'], $_POST['articolo_prezzo_netto'], $_POST['articolo_prezzo_lordo'], $_POST['articolo_cod_iva'],
                $_POST['pagamento_data_scadenza'], $_POST['pagamento_importo'], $_POST['pagamento_data_saldo'], $_POST['codice_destinatario'], $_POST['pec'],
                $_POST['fa_istituto_credito'], $_POST['fa_iban'], $_POST['fa_beneficiario'], $_POST['email'], $_POST['tel'], $_POST['fax'], $_POST['fa_data'], $_POST['pagamento_metodo'], $fattura_numero, $fattura_progressivo
                );
            
            echo json_encode($responce);
        } else {
            // set response code - 404 Not found
            http_response_code(404);
            
            echo json_encode(
                array("message" => "Method not valid.")
                );
        }
        
        break;
    }
    
    case 'fatturaCliente': {
        // required headers
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // The request is using the POST method
            
            // set response code - 200 OK
            http_response_code(200);
            $metodo_titoloN = null;
            if(isset($_POST['metodo_titoloN'])) {
                $metodo_titoloN = $_POST['metodo_titoloN'];
            }
            $metodo_descN = null;
            if(isset($_POST['metodo_descN'])) {
                $metodo_descN = $_POST['metodo_descN'];
            }
            $fattura_numero = null;
            if(isset($_POST['fattura_numero'])) {
                $fattura_numero = $_POST['fattura_numero'];
            }
            $fattura_progressivo = null;
            if(isset($_POST['fattura_progressivo'])) {
                $fattura_progressivo = $_POST['fattura_progressivo'];
            }
            
            $responce =  fatturaCliente($_POST['nome'], $_POST['indirizzo_via'], $_POST['indirizzo_cap'], $_POST['indirizzo_citta'], $_POST['indirizzo_provincia'], $_POST['paese'], $_POST['paese_iso'],
                $_POST['lingua'], $_POST['piva'], $_POST['cf'], $_POST['metodo_pagamento'], $metodo_titoloN, $metodo_descN,
                $_POST['articolo_nome'], $_POST['articolo_quantita'], $_POST['articolo_nota'], $_POST['articolo_prezzo_netto'], $_POST['articolo_prezzo_lordo'], $_POST['articolo_cod_iva'],
                $_POST['pagamento_data_scadenza'], $_POST['pagamento_importo'], $_POST['pagamento_data_saldo'], $_POST['codice_destinatario'], $_POST['pec'],
                $_POST['fa_istituto_credito'], $_POST['fa_iban'], $_POST['fa_beneficiario'], $_POST['email'], $_POST['tel'], $_POST['fax'], $_POST['fa_data'], $_POST['pagamento_metodo'], $fattura_numero, $fattura_progressivo,
                $_POST['tipo']
                );
            
            echo json_encode($responce);
            
        } else {
            // set response code - 404 Not found
            http_response_code(404);
            
            echo json_encode(
                array("message" => "Method not valid.")
                );
        }
        
        break;
    }
    
    default: {
        // required headers
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');
        //         header("Content-Type: application/json; charset=UTF-8");
        
        // set response code - 404 Not found
        http_response_code(404);
        
        echo json_encode(
            array("message" => "API not valid.")
            );
    }
}


function info() {
    $url = Config::$fattureInCloudUrl."richiesta/info";
    $request = array("api_uid" => Config::$fattureInCloudAPIUID, "api_key" => Config::$fattureInCloudAPIKey);
    $options = array(
        "http" => array(
            "header"  => "Content-type: text/json",
            "method"  => "POST",
            "content" => json_encode($request)
        ),
    );
    
    $context  = stream_context_create($options);
    $result = json_decode(file_get_contents($url, false, $context), true);
    
    return $result;
}

function fattura($nome, $indirizzo_via, $indirizzo_cap, $indirizzo_citta, $indirizzo_provincia, $paese, $paese_iso,
    $lingua, $piva, $cf, $metodo_pagamento = '', $metodo_titoloN = '', $metodo_descN = '',
    $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto, $articolo_prezzo_lordo, $articolo_cod_iva = 14,
    $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
    $fa_istituto_credito, $fa_iban, $fa_beneficiario, $email, $tel, $fax, $fa_data, $pagamento_metodo, $fatturaNumero = "", $fattura_progressivo = ""
    ) {
        
        
        $tipo_fattura = 'self_supplier_invoice';
        /************RICERCA FORNITORE************/
        $nomeRicerca = str_replace(" ", "%20", $nome);
        $urlSearch = Config::$fattureInCloudUrl_v2."c/".Config::$fattureInCloudUrl_v2_CompanyID."/entities/suppliers?page=1&filter%5B0%5D%5Bvalue%5D=".$nomeRicerca."&filter%5B0%5D%5Bop%5D=%3D&filter%5B0%5D%5Bfield%5D=name";
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlSearch,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".Config::$fattureInCloudUrl_v2_APIToken
            ),
        ));
        
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            var_dump($err);
            $agenzia = null;
            $idAgenzia = "null";
        } else {
            $response = json_decode($response, true);
            if(isset($response['data'][0])) {
                $agenzia = $response['data'][0];
                $idAgenzia = $agenzia['id'];
            } else {
                $agenzia = null;
                $idAgenzia = "null";
            }
        }
        /***************FINE RICERCA FORNITORE************/
        
        
        /*************AUTOFATTURA***********/
        $fa_pagamento_account_id = '775247'; //BPER BANCA Messina
        $fa_data = str_replace('/', '-', $fa_data);
        $fa_data = date("Y-m-d", strtotime($fa_data) );
        
        $pagamento_data_scadenza = str_replace('/', '-', $pagamento_data_scadenza);
        $pagamento_data_scadenza = date("Y-m-d", strtotime($pagamento_data_scadenza) );
        if(isset($pagamento_data_saldo)) {
            $pagamento_data_saldo = str_replace('/', '-', $pagamento_data_saldo);
            $pagamento_data_saldo = date("Y-m-d", strtotime($pagamento_data_saldo) );
        }
        if($paese_iso == 'it' || $paese_iso == 'IT') {
            $paese_iso = 'it';
            $paese_language = 'italian';
        } else {
            $paese_language = 'english';
            $paese_iso == 'en';
        }
        if(isset($pagamento_data_saldo) && $pagamento_data_saldo!='' && $pagamento_data_saldo == $pagamento_data_scadenza) {
            $pagamento_stato = 'paid';
        } else {
            $pagamento_stato = 'not_paid';
        }
        
        $url = Config::$fattureInCloudUrl_v2."c/".Config::$fattureInCloudUrl_v2_CompanyID."/issued_documents";
        
        $curl = curl_init();
        //invoice, receipt, credit_note
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"data\":{\"entity\":{\"name\":\"".$nome."\",\"id\":".$idAgenzia.",\"code\":\"\",\"type\":null,\"first_name\":\"\",\"last_name\":\"\",\"contact_person\":\"\",\"vat_number\":\"".$piva."\",\"tax_code\":\"".$cf."\",\"address_street\":\"".$indirizzo_via."\",\"address_postal_code\":\"".$indirizzo_cap."\",\"address_city\":\"".$indirizzo_citta."\",\"address_province\":\"".$indirizzo_provincia."\",\"address_extra\":\"\",\"country\":\"".$paese."\",\"email\":\"".$email."\",\"certified_email\":\"".$pec."\",\"phone\":\"".$tel."\",\"fax\":\"".$fax."\",\"notes\":\"\",\"default_vat\":{\"value\":0,\"id\":".$articolo_cod_iva."},\"default_payment_method\":{\"name\":\"".$metodo_pagamento."\",\"id\":0,\"type\":\"standard\",\"details\":[{\"title\":\"".$articolo_nome."\",\"description\":\"".$articolo_nome."\"}]}},\"items_list\":[{\"product_id\":0,\"code\":\"\",\"name\":\"".$articolo_nome."\",\"description\":\"".$articolo_nome."\",\"qty\":".$articolo_quantita.",\"measure\":\"\",\"net_price\":".$articolo_prezzo_netto.",\"gross_price\":".$articolo_prezzo_lordo.",\"vat\":{\"value\":0,\"id\":".$articolo_cod_iva."},\"not_taxable\":false,\"apply_withholding_taxes\":true,\"discount\":0,\"discount_highlight\":false,\"in_ddt\":false,\"stock\":true,\"ei_raw\":{}}],\"id\":0,\"type\":\"".$tipo_fattura."\",\"number\":".$fattura_progressivo.",\"numeration\":\"".$fatturaNumero."\",\"date\":\"".$fa_data."\",\"currency\":{\"id\":\"EUR\",\"symbol\":\"\u20AC\",\"exchange_rate\":1},\"language\":{\"code\":\"".$paese_iso."\",\"name\":\"".$paese_language."\"},\"subject\":\"".$articolo_nome."\",\"visible_subject\":\"".$articolo_nome."\",\"rc_center\":\"\",\"notes\":\"\",\"rivalsa\":0,\"cassa\":0,\"withholding_tax\":0,\"withholding_tax_taxable\":0,\"other_withholding_tax\":0,\"stamp_duty\":0,\"payment_method\":{\"name\":\"".$metodo_pagamento."\",\"id\":0,\"type\":\"standard\",\"details\":[{\"title\":\"$articolo_nome\",\"description\":\"$articolo_nome\"}]},\"use_split_payment\":false,\"use_gross_prices\":false,\"e_invoice\":true,\"ei_data\":{\"payment_method\":\"MP05\",\"vat_kind\":null,\"invoice_date\":\"".$fa_data."\",\"invoice_number\":\"\"},\"payments_list\":[{\"due_date\":\"".$pagamento_data_scadenza."\",\"amount\":".$pagamento_importo.",\"status\":\"".$pagamento_stato."\",\"payment_account\":{\"name\":\"".$fa_istituto_credito."\",\"id\":".$fa_pagamento_account_id.",\"type\":\"standard\",\"iban\":\"".$fa_iban."\"},\"paid_date\":\"".$pagamento_data_saldo."\",\"ei_raw\":{}}],\"show_payments\":true,\"show_payment_method\":true,\"show_totals\":\"all\",\"show_paypal_button\":false,\"show_notification_button\":false,\"delivery_note\":false,\"accompanying_invoice\":false,\"is_marked\":false,\"extra_data\":{},\"seen_date\":\"".$fa_data."\",\"url\":\"\",\"attachment_url\":\"\",\"attachment_token\":\"\",\"ei_raw\":{\"FatturaElettronicaBody\":{\"DatiGenerali\":{\"DatiGeneraliDocumento\":{\"TipoDocumento\":\"TD01\"}}}}},\"options\":{\"entity_search_fields\":\"entity.id,tax_code,vat_number,code\",\"entity_autocomplete\":false,\"entity_create\":false,\"entity_update\":\"\"}}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".Config::$fattureInCloudUrl_v2_APIToken,
                "content-type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        $result = json_decode($response, true);
        
        return $result;
}


function fatturaCliente($nome, $indirizzo_via, $indirizzo_cap, $indirizzo_citta, $indirizzo_provincia, $paese, $paese_iso,
    $lingua, $piva, $cf, $metodo_pagamento = '', $metodo_titoloN = '', $metodo_descN = '',
    $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto, $articolo_prezzo_lordo, $articolo_cod_iva = 14,
    $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
    $fa_istituto_credito, $fa_iban, $fa_beneficiario, $email, $tel, $fax, $fa_data, $pagamento_metodo, $fatturaNumero = "", $fattura_progressivo = "", $tipo_fattura
    ) {
        
        
        /************RICERCA FORNITORE************/
        $nomeRicerca = str_replace(" ", "%20", $nome);
        $urlSearch = Config::$fattureInCloudUrl_v2."c/".Config::$fattureInCloudUrl_v2_CompanyID."/entities/clients?page=1&filter%5B0%5D%5Bvalue%5D=".$nomeRicerca."&filter%5B0%5D%5Bop%5D=%3D&filter%5B0%5D%5Bfield%5D=name";
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlSearch,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".Config::$fattureInCloudUrl_v2_APIToken
            ),
        ));
        
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            var_dump($err);
            $agenzia = null;
            $idAgenzia = "null";
        } else {
            $response = json_decode($response, true);
            if(isset($response['data'][0])) {
                $agenzia = $response['data'][0];
                $idAgenzia = $agenzia['id'];
            } else {
                $agenzia = null;
                $idAgenzia = "null";
            }
        }
        /***************FINE RICERCA FORNITORE************/
        
        
        /*************AUTOFATTURA***********/
        $fa_pagamento_account_id = '810355';
        $fa_data = str_replace('/', '-', $fa_data);
        $fa_data = date("Y-m-d", strtotime($fa_data) );
        $pagamento_data_scadenza = str_replace('/', '-', $pagamento_data_scadenza);
        $pagamento_data_scadenza = date("Y-m-d", strtotime($pagamento_data_scadenza) );
        if(isset($pagamento_data_saldo)) {
            $pagamento_data_saldo = str_replace('/', '-', $pagamento_data_saldo);
            $pagamento_data_saldo = date("Y-m-d", strtotime($pagamento_data_saldo) );
        }
        if($paese_iso == 'it' || $paese_iso == 'IT') {
            $paese_iso = 'it';
            $paese_language = 'italian';
        } else {
            $paese_language = 'english';
            $paese_iso == 'en';
        }
        if(isset($pagamento_data_saldo) && $pagamento_data_saldo!='' && $pagamento_data_saldo == $pagamento_data_scadenza) {
            $pagamento_stato = 'paid';
        } else {
            $pagamento_stato = 'not_paid';
        }
        
        //$result = "{\"data\":{\"entity\":{\"name\":\"".$nome."\",\"id\":".$idAgenzia.",\"code\":\"\",\"type\":null,\"first_name\":\"\",\"last_name\":\"\",\"contact_person\":\"\",\"vat_number\":\"".$piva."\",\"tax_code\":\"".$cf."\",\"address_street\":\"".$indirizzo_via."\",\"address_postal_code\":\"".$indirizzo_cap."\",\"address_city\":\"".$indirizzo_citta."\",\"address_province\":\"".$indirizzo_provincia."\",\"address_extra\":\"\",\"country\":\"".$paese."\",\"email\":\"".$email."\",\"certified_email\":\"".$pec."\",\"phone\":\"".$tel."\",\"fax\":\"".$fax."\",\"notes\":\"\",\"default_vat\":{\"value\":0,\"id\":".$articolo_cod_iva."},\"default_payment_method\":{\"name\":\"".$metodo_pagamento."\",\"id\":0,\"type\":\"standard\",\"details\":[{\"title\":\"".$articolo_nome."\",\"description\":\"".$articolo_nome."\"}]}},\"items_list\":[{\"product_id\":0,\"code\":\"\",\"name\":\"".$articolo_nome."\",\"description\":\"".$articolo_nome."\",\"qty\":".$articolo_quantita.",\"measure\":\"\",\"net_price\":".$articolo_prezzo_netto.",\"gross_price\":".$articolo_prezzo_lordo.",\"vat\":{\"value\":0,\"id\":".$articolo_cod_iva."},\"not_taxable\":false,\"apply_withholding_taxes\":true,\"discount\":0,\"discount_highlight\":false,\"in_ddt\":false,\"stock\":true,\"ei_raw\":{}}],\"id\":0,\"type\":\"self_supplier_invoice\",\"number\":".$fattura_progressivo.",\"numeration\":\"".$fatturaNumero."\",\"date\":\"".$fa_data."\",\"currency\":{\"id\":\"EUR\",\"symbol\":\"\u20AC\",\"exchange_rate\":1},\"language\":{\"code\":\"".$paese_iso."\",\"name\":\"".$paese_language."\"},\"subject\":\"".$articolo_nome."\",\"visible_subject\":\"".$articolo_nome."\",\"rc_center\":\"\",\"notes\":\"\",\"rivalsa\":0,\"cassa\":0,\"withholding_tax\":0,\"withholding_tax_taxable\":0,\"other_withholding_tax\":0,\"stamp_duty\":0,\"payment_method\":{\"name\":\"".$metodo_pagamento."\",\"id\":0,\"type\":\"standard\",\"details\":[{\"title\":\"$articolo_nome\",\"description\":\"$articolo_nome\"}]},\"use_split_payment\":false,\"use_gross_prices\":false,\"e_invoice\":true,\"ei_data\":{\"payment_method\":\"MP05\",\"vat_kind\":null,\"invoice_date\":\"".$fa_data."\",\"invoice_number\":\"\"},\"payments_list\":[{\"due_date\":\"".$pagamento_data_scadenza."\",\"amount\":".$pagamento_importo.",\"status\":\"".$pagamento_stato."\",\"payment_account\":{\"name\":\"".$fa_istituto_credito."\",\"id\":".$fa_pagamento_account_id.",\"type\":\"standard\",\"iban\":\"".$fa_iban."\"},\"paid_date\":\"".$pagamento_data_saldo."\",\"ei_raw\":{}}],\"show_payments\":true,\"show_payment_method\":true,\"show_totals\":\"all\",\"show_paypal_button\":false,\"show_notification_button\":false,\"delivery_note\":false,\"accompanying_invoice\":false,\"is_marked\":false,\"extra_data\":{},\"seen_date\":\"".$fa_data."1\",\"url\":\"\",\"attachment_url\":\"\",\"attachment_token\":\"\",\"ei_raw\":{\"FatturaElettronicaBody\":{\"DatiGenerali\":{\"DatiGeneraliDocumento\":{\"TipoDocumento\":\"TD01\"}}}}},\"options\":{\"entity_search_fields\":\"entity.id,tax_code,vat_number,code\",\"entity_autocomplete\":false,\"entity_create\":false,\"entity_update\":\"\"}}";
        $url = Config::$fattureInCloudUrl_v2."c/".Config::$fattureInCloudUrl_v2_CompanyID."/issued_documents";
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"data\":{\"entity\":{\"name\":\"".$nome."\",\"id\":".$idAgenzia.",\"code\":\"\",\"type\":null,\"first_name\":\"\",\"last_name\":\"\",\"contact_person\":\"\",\"vat_number\":\"".$piva."\",\"tax_code\":\"".$cf."\",\"address_street\":\"".$indirizzo_via."\",\"address_postal_code\":\"".$indirizzo_cap."\",\"address_city\":\"".$indirizzo_citta."\",\"address_province\":\"".$indirizzo_provincia."\",\"address_extra\":\"\",\"country\":\"".$paese."\",\"email\":\"".$email."\",\"certified_email\":\"".$pec."\",\"phone\":\"".$tel."\",\"fax\":\"".$fax."\",\"notes\":\"\",\"default_vat\":{\"value\":0,\"id\":".$articolo_cod_iva."},\"default_payment_method\":{\"name\":\"".$metodo_pagamento."\",\"id\":0,\"type\":\"standard\",\"details\":[{\"title\":\"".$articolo_nome."\",\"description\":\"".$articolo_nota."\"}]}},\"items_list\":[{\"product_id\":0,\"code\":\"\",\"name\":\"".$articolo_nome."\",\"description\":\"".$articolo_nota."\",\"qty\":".$articolo_quantita.",\"measure\":\"\",\"net_price\":".$articolo_prezzo_netto.",\"gross_price\":".$articolo_prezzo_lordo.",\"vat\":{\"value\":0,\"id\":".$articolo_cod_iva."},\"not_taxable\":false,\"apply_withholding_taxes\":true,\"discount\":0,\"discount_highlight\":false,\"in_ddt\":false,\"stock\":true,\"ei_raw\":{}}],\"id\":0,\"type\":\"$tipo_fattura\",\"number\":".$fattura_progressivo.",\"numeration\":\"".$fatturaNumero."\",\"date\":\"".$fa_data."\",\"currency\":{\"id\":\"EUR\",\"symbol\":\"\u20AC\",\"exchange_rate\":1},\"language\":{\"code\":\"".$paese_iso."\",\"name\":\"".$paese_language."\"},\"subject\":\"".$articolo_nome."\",\"visible_subject\":\"".$articolo_nome."\",\"rc_center\":\"\",\"notes\":\"\",\"rivalsa\":0,\"cassa\":0,\"withholding_tax\":0,\"withholding_tax_taxable\":0,\"other_withholding_tax\":0,\"stamp_duty\":0,\"payment_method\":{\"name\":\"".$metodo_pagamento."\",\"id\":0,\"type\":\"standard\",\"details\":[{\"title\":\"$articolo_nome\",\"description\":\"$articolo_nota\"}]},\"use_split_payment\":false,\"use_gross_prices\":false,\"e_invoice\":true,\"ei_code\":\"$codice_destinatario\",\"ei_data\":{\"payment_method\":\"MP05\",\"vat_kind\":null,\"invoice_date\":\"".$fa_data."\",\"invoice_number\":\"\"},\"payments_list\":[{\"due_date\":\"".$pagamento_data_scadenza."\",\"amount\":".$pagamento_importo.",\"status\":\"".$pagamento_stato."\",\"payment_account\":{\"name\":\"".$fa_istituto_credito."\",\"id\":".$fa_pagamento_account_id.",\"type\":\"standard\",\"iban\":\"".$fa_iban."\"},\"paid_date\":\"".$pagamento_data_saldo."\",\"ei_raw\":{}}],\"show_payments\":true,\"show_payment_method\":true,\"show_totals\":\"all\",\"show_paypal_button\":false,\"show_notification_button\":false,\"delivery_note\":false,\"accompanying_invoice\":false,\"is_marked\":false,\"extra_data\":{},\"seen_date\":\"".$fa_data."1\",\"url\":\"\",\"attachment_url\":\"\",\"attachment_token\":\"\",\"ei_raw\":{}},\"options\":{\"entity_search_fields\":\"entity.id,tax_code,vat_number,code\",\"entity_autocomplete\":false,\"entity_create\":false,\"entity_update\":\"\"}}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".Config::$fattureInCloudUrl_v2_APIToken,
                "content-type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        $result = json_decode($response, true);
        
        return $result;
}

function vatTypes() {
    $url = Config::$fattureInCloudUrl_v2."c/".Config::$fattureInCloudUrl_v2_CompanyID."/info/vat_types";
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".Config::$fattureInCloudUrl_v2_APIToken,
            "content-type: application/json"
        ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    $result = json_decode($response, true);
    
    return $result;
}

?>
