<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath."/main_include.php");

$config=new Config();

$run=$config->load();

ini_set('display_errors', 0);
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
					'version' => '1.0.0',
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
	        $responce =  fattura($_POST['nome'], $_POST['indirizzo_via'], $_POST['indirizzo_cap'], $_POST['indirizzo_citta'], $_POST['indirizzo_provincia'], $_POST['paese'], $_POST['paese_iso'],
	            $_POST['lingua'], $_POST['piva'], $_POST['cf'], $_POST['metodo_pagamento'], $_POST['metodo_titoloN'], $_POST['metodo_descN'],
	            $_POST['articolo_nome'], $_POST['articolo_quantita'], $_POST['articolo_nota'], $_POST['articolo_prezzo_netto'], $_POST['articolo_prezzo_lordo'], $_POST['articolo_cod_iva'],
	            $_POST['pagamento_data_scadenza'], $_POST['pagamento_importo'], $_POST['pagamento_data_saldo'], $_POST['codice_destinatario'], $_POST['pec'],
	            $_POST['fa_istituto_credito'], $_POST['fa_iban'], $_POST['fa_beneficiario'], $_POST['email'], $_POST['tel'], $_POST['fax'], $_POST['fa_data'], $_POST['pagamento_metodo'], $_POST['fattura_numero']
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
    $fa_istituto_credito, $fa_iban, $fa_beneficiario, $email, $tel, $fax, $fa_data, $pagamento_metodo, $fatturaNumero = ""
    ) {
    $url = Config::$fattureInCloudUrl."fatture/nuovo";
    $request = array("api_uid" => Config::$fattureInCloudAPIUID, 
                     "api_key" => Config::$fattureInCloudAPIKey,
                    "id_cliente" => "0",
                    "id_fornitore" => "0",
                    "nome" => $nome,
                    "indirizzo_via" => $indirizzo_via,
                    "indirizzo_cap" => $indirizzo_cap,
                    "indirizzo_citta" => $indirizzo_citta,
                    "indirizzo_provincia" => $indirizzo_provincia,
                    "indirizzo_extra" => "",
                    "paese" => $paese,
                    "paese_iso" => $paese_iso,
                    "lingua" => $lingua,
                    "piva" => $piva,
                    "cf" => $cf,
                    "autocompila_anagrafica" => false,
                    "salva_anagrafica" => false,
                    "numero" => $fatturaNumero, //compila in automatico fatturaInCloud
                    "data" => "", //compila in automatico fatturaInCloud
                    "valuta" => "EUR",
                    "valuta_cambio" => 1,
                    "prezzi_ivati" => false,
                    "rivalsa" => 0,
                    "cassa" => 0,
                    "rit_acconto" => 0,
                    "imponibile_ritenuta" => 0,
                    "rit_altra" => 0,
                    "marca_bollo" => 0,
                    "oggetto_visibile" => $articolo_nome,
                    "oggetto_interno" => "",
                    "centro_ricavo" => "",
                    "centro_costo" => "",
                    "note" => "",
                    "nascondi_scadenza" => false,
                    "ddt" => false,
                    "ftacc" => false,
                    "id_template" => "0",
                    "ddt_id_template" => "0",
                    "ftacc_id_template" => "0",
                    "mostra_info_pagamento" => true,
                    "metodo_pagamento" => $metodo_pagamento,//"Bonifico",
                    "metodo_titoloN" => $metodo_titoloN, //"IBAN",
                    "metodo_descN" => $metodo_descN, //"IT01A2345678900000000001234",
                    "mostra_totali" => "tutti",
                    "mostra_bottone_paypal" => false,
                    "mostra_bottone_bonifico" => false,
                    "mostra_bottone_notifica" => false,
                    "lista_articoli" => [
                        [
                            "id" => "0",
                            "codice" => "",
                            "nome" => $articolo_nome,
                            "um" => "",
                            "quantita" => $articolo_quantita,
                            "descrizione" => "",
                            "categoria" => "",
                            "prezzo_netto" => $articolo_prezzo_netto, //IVA esclusa
                            "prezzo_lordo" => $articolo_prezzo_lordo, //IVA inclusa
                            "cod_iva" => $articolo_cod_iva, //0 = 22% - 14 = Non Imp. Art.9 1C
                            "tassabile" => true,
                            "sconto" => 0,
                            "applica_ra_contributi" => true,
                            "ordine" => 0,
                            "sconto_rosso" => 0,
                            "in_ddt" => false,
                            "magazzino" => true
                        ]
                    ],
                    "lista_pagamenti" => [
                        [
                            "data_scadenza" => $pagamento_data_scadenza,
                            "importo" => $pagamento_importo,
                            "metodo" => $pagamento_metodo, //$fa_istituto_credito." - IBAN ".$fa_iban, //not = non pagato - rev = stornato - oppure nome del tipo pagamento
                            "data_saldo" => $pagamento_data_saldo
                        ]
                    ],
            "ddt_numero" => "",
            "ddt_data" => "",
            "ddt_colli" => "",
            "ddt_peso" => "",
            "ddt_causale" => "",
            "ddt_luogo" => "",
            "ddt_trasportatore" => "",
            "ddt_annotazioni" => "",
            "PA" => true,
            "PA_tipo_cliente" => "B2B",
            "PA_tipo" => "nessuno",
            "PA_numero" => "", //numero fattura elettronica in automatico
            "PA_data" => $fa_data, 
            "PA_cup" => "", //per PA true
            "PA_cig" => "", //per PA true
            "PA_codice" => $codice_destinatario, //se vuoto utilizza pec
            "PA_pec" => $pec,
            "PA_esigibilita" => "N",
            "PA_modalita_pagamento" => "MP01",
            "PA_istituto_credito" => $fa_istituto_credito, //BPER BANCA
            "PA_iban" => $fa_iban, //IT22P0538716500000003371325
            "PA_beneficiario" => $fa_beneficiario, //onebus srl
            "extra_anagrafica" => [
                "mail" => $email,
                "tel" => $tel,
                "fax" => $fax
            ],
            "split_payment" => true
        );
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

?>