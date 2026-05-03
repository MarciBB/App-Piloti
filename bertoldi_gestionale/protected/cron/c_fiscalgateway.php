<?php
/* Configurazione log */
include_once('c_path_include.php');

$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$storico_include=$basepath.'/protected/classes/class.StoricoOperazioni.php';
$operatore_include=$basepath.'/protected/classes/class.Operatore.php';
$dt_include=$basepath.'/protected/classes/class.DT.php';

include_once($config_include);
include_once($database_include);
include_once($dt_include);
include_once($operatore_include);
include_once($storico_include);

global $user, $db;
$config = new Config();
$run=$config->loadCron($type);

$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";

$db = new Database();
$db->connect();

echo "Connessione al db OK<br>";

// Configurazione FiscalGateway
$baseUrl = 'https://printer-server.cassaincloud-prod.it/fiscalgateway/bertoldiboats/bills?v=1&limit=50&skip=';
$token = '8521c1a878c5c63b9294c65285e827e3448c9ab337bda40b0c8dd5fd9d1088ae';

$skip = 0;
$totalInserted = 0;

echo "Inizio sincronizzazione FiscalGateway<br>";

do {
    $url = $baseUrl . $skip;
    echo "Richiesta URL: $url<br>";

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'authentication: ' . $token
        ),
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode !== 200) {
        echo "Errore HTTP: $httpCode<br>";
        break;
    }

    $data = json_decode($response, true);

    // Controlla la struttura JSON corretta
    if (!isset($data['success']) || !$data['success']) {
        echo "Errore nella risposta API<br>";
        break;
    }

    // Estrai l'array bills dalla struttura
    $bills = isset($data['result']['bills']) ? $data['result']['bills'] : array();

    if (!is_array($bills) || count($bills) === 0) {
        echo "Fine dei dati ricevuti - array bills vuoto<br>";
        break; // Fine dei dati
    }

    $billsCount = count($bills);
    echo "Ricevuti " . $billsCount . " record<br>";

    foreach ($bills as $bill) {
        // Prepara i dati per l'inserimento usando il metodo della classe Database
        $billData = array();
        $billData['id'] = isset($bill['_id']) ? $bill['_id'] : '';
        $billData['order_id'] = isset($bill['data']['order_id']) ? $bill['data']['order_id'] : '';
        $billData['store_id'] = isset($bill['data']['store_id']) ? $bill['data']['store_id'] : '';
        $billData['attempts'] = isset($bill['attempts']) ? $bill['attempts'] : 0;
        $billData['creation_date'] = isset($bill['creationDate']) ? date('Y-m-d H:i:s', strtotime($bill['creationDate'])) : null;
        $billData['last_update_date'] = isset($bill['lastUpdateDate']) ? date('Y-m-d H:i:s', strtotime($bill['lastUpdateDate'])) : null;
        $billData['salespoint_id'] = isset($bill['salespointId']) ? $bill['salespointId'] : '';
        $billData['account_code'] = isset($bill['accountCode']) ? $bill['accountCode'] : '';
        $billData['state'] = isset($bill['state']) ? $bill['state'] : '';
        $billData['live'] = isset($bill['live']) ? ($bill['live'] ? 1 : 0) : 0;
        $billData['fiscal'] = isset($bill['data']['fiscal']) ? ($bill['data']['fiscal'] ? 1 : 0) : 0;
        
        // Dati documento fiscale se presente
        $billData['document_date'] = isset($bill['documentReference']['date']) ? date('Y-m-d H:i:s', strtotime($bill['documentReference']['date'])) : null;
        $billData['document_number'] = isset($bill['documentReference']['number']) ? $bill['documentReference']['number'] : null;
        $billData['printer_id'] = isset($bill['documentReference']['printerId']) ? $bill['documentReference']['printerId'] : '';
        $billData['z_number'] = isset($bill['documentReference']['znumber']) ? $bill['documentReference']['znumber'] : null;
        
        // Calcola totale dai payment_methods
        $totale = 0;
        if (isset($bill['data']['payment_methods']) && is_array($bill['data']['payment_methods'])) {
            foreach ($bill['data']['payment_methods'] as $payment) {
                $totale += isset($payment['amount']) ? $payment['amount'] : 0;
            }
        }
        $billData['totale'] = $totale / 100; // Converti da centesimi a euro
        
        // Salva i dati completi come JSON per riferimento
        $billData['data_json'] = json_encode($bill, JSON_UNESCAPED_UNICODE);
        $billData['data_sync'] = date('Y-m-d H:i:s');

        // Verifica se il record esiste già
        $sql = "SELECT id, last_update_date, state FROM bills WHERE id = '" . $db->escape($billData['id']) . "'";
        $existing = $db->query_first($sql);
        
        // Debug per vedere cosa restituisce la query
        echo "Controllo esistenza ID: " . $billData['id'] . "<br>";
        var_dump($existing);
        echo "<br>";

        // Controlla se il record esiste usando isset invece di empty
        if (!$existing || !isset($existing['id'])) {
            // Inserisce nuovo record
            echo "Record non trovato, inserisco nuovo<br>";
            if ($db->insert("bills", $billData)) {
                $totalInserted++;
                echo "Inserito bill ID: " . $billData['id'] . " - Order: " . $billData['order_id'] . " - Totale: €" . $billData['totale'] . "<br>";
            } else {
                echo "Errore inserimento bill ID: " . $billData['id'] . "<br>";
            }
        } else {
            echo "Record trovato, controllo aggiornamenti<br>";
            // Verifica se necessita aggiornamento confrontando le date
            $existingDate = $existing['last_update_date'];
            $newDate = $billData['last_update_date'];
            $existingState = $existing['state'];
            $newState = $billData['state'];
            
            // Aggiorna solo se la data di aggiornamento è diversa o lo stato è cambiato
            if ($existingDate != $newDate || $existingState != $newState) {
                if ($db->update("bills", $billData, "id = '" . $db->escape($billData['id']) . "'")) {
                    $totalInserted++;
                    echo "Aggiornato bill ID: " . $billData['id'] . " - State: " . $billData['state'] . " (era: " . $existingState . ")<br>";
                } else {
                    echo "Errore aggiornamento bill ID: " . $billData['id'] . "<br>";
                }
            } else {
                echo "Saltato bill ID: " . $billData['id'] . " - Nessuna modifica<br>";
            }
        }
    }

    $skip += 50;
    echo "Processati $skip record totali<br>";
    echo "Valore corrente di skip: $skip<br>";

    // IMPORTANTE: Se hai ricevuto meno di 50 record, significa che è l'ultimo batch
    if ($billsCount < 50) {
        echo "Ultimo batch ricevuto con $billsCount record<br>";
        echo "VALORE FINALE DI SKIP: $skip<br>";
        break;
    }

} while (true);

echo "<br>Sincronizzazione completata!<br>";
echo "Totale record inseriti o aggiornati: $totalInserted<br>";
echo "SKIP FINALE: $skip<br>";