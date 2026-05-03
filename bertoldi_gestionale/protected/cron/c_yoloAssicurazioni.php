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

//dipendenze libreria phpseclib
require_once $basepath.'/protected/classes/phpseclib/Math/BigInteger.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/Hash.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/Base.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/RC4.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/Rijndael.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/Twofish.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/Blowfish.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/Random.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/DES.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/TripleDES.php';
require_once $basepath.'/protected/classes/phpseclib/Net/SSH2.php';
require_once $basepath.'/protected/classes/phpseclib/Net/SFTP.php';
require_once $basepath.'/protected/classes/phpseclib/Crypt/RSA.php';

// Usa il namespace corretto per phpseclib v2.x
use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

global $user, $db;
$config = new Config();
$run=$config->loadCron($type);

$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";




$db = new Database();
$db->connect();

// recupera le prenotazioni da inviare a Yolo Assicurazioni
// sono selezionati solo i tour con assicurazione attiva, con partenza da oggi in poi, e con durata totale (salita-discesa + sosta) superiore a 2 ore
$sql = "
    SELECT p.*, pp.*, l.YoloCodiceFascia, p.DataIns as DataPrenotazione
    FROM RT_Prenotazione p
    INNER JOIN RT_PrenotazionePercorso pp ON p.PrenotazioneId = pp.PrenotazioneId
    INNER JOIN RT_Linea l ON pp.LineaId = l.LineaId
    WHERE l.YoloCodiceFascia IS NOT NULL
    AND l.YoloCodiceFascia <> ''
    AND pp.Direzione = 'A'
    AND DATE(pp.CorsaDataPartenza) >= CURDATE()
    AND p.PrenotazioneStato IN (3, 4, 16)
    AND p.TipoTour = 1
    AND (TIMESTAMPDIFF(MINUTE, pp.DataoraSalita, pp.DataOraDiscesa) + 
         COALESCE((SELECT SUM(NumeroPax) FROM RT_PrenotazioneBiglietto 
                   WHERE TipologiaBigliettoId = 23 
                   AND PrenotazioneId = p.PrenotazioneId), 0) * 60 +
         COALESCE((SELECT SUM(NumeroPax) FROM RT_PrenotazioneBiglietto 
                   WHERE TipologiaBigliettoId = 41 
                   AND PrenotazioneId = p.PrenotazioneId), 0) * 30) > 120
    AND p.TotaleDaPagare > 0 AND p.Assicurazione = 1
";

$result = $db->fetch_array($sql);

//verifica se ci sono prenotazioni da elaborare e termino il processo
if(count($result) == 0) {
    echo "Nessuna prenotazione da elaborare.\n";
    exit(0);
}

// Impostazioni CSV
$oggi = date('dmY');
$oggiNome = date('Ymd');
$csv_filename = $oggiNome . '.csv';
$csv_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $csv_filename;
$csv_sep = ';';

// Header CSV
$header = [
    'DATA ACQUISTO COPERTURA',
    'CODICE FASCIA',
    'NUMERO PRATICA',
    'PROGRESSIVO',
    'NOMINATIVO',
    'CODICE FISCALE',
    'DATA PRENOTAZIONE SOGGIORNO',
    'DATA INIZIO SOGGIORNO',
    'DATA FINE SOGGIORNO',
    'DATA ANNULLAMENTO SOGGIORNO',
    'VALORE DEL SOGGIORNO',
    'DESTINAZIONE',
    'PREMIO',
    'TIPO OPERAZIONE'
];

// Abilita la visualizzazione degli errori PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Apri file CSV per scrittura
$fp = fopen($csv_path, 'w');
if (!$fp) {
    echo "Errore apertura file CSV: $csv_path\n";
    //exit(1);
}

// Scrivi header senza virgolette
$headerLine = implode($csv_sep, $header) . "\n";
fwrite($fp, $headerLine);

$num_righe_inserite = 0;

// Prima di inserire, controlla se esiste già la testata per questo file
$sqlCheckYolo = "SELECT YoloId FROM RT_Yolo WHERE Nome = '" . $csv_filename . "'";
$yoloTestata = $db->query_first($sqlCheckYolo);
if ($yoloTestata && isset($yoloTestata['YoloId'])) {
    $yoloId = $yoloTestata['YoloId'];
    // Cancella tutte le righe associate
    $db->query("DELETE FROM RT_YoloRiga WHERE YoloId = " . intval($yoloId));
    // Cancella la testata
    $db->query("DELETE FROM RT_Yolo WHERE YoloId = " . intval($yoloId));
}

// CREA SUBITO L'INTESTAZIONE (SPOSTATO QUI!)
$yoloData = [
    'Tipo' => 'I',
    'Nome' => $csv_filename,
    'Data' => date('Y-m-d H:i:s')
];
$yoloId = $db->insert('RT_Yolo', $yoloData);
echo "Creato YoloId: $yoloId per file: $csv_filename\n";

$progressivo = 1;
foreach ($result as $row) {
    echo "\n=== ELABORAZIONE PRENOTAZIONE {$row['PrenotazioneId']} ===\n";
    echo "CodicePrenotazione: {$row['CodicePrenotazione']}\n";
    echo "PrenotazioneStato: {$row['PrenotazioneStato']}\n";
    echo "TotaleDaPagare: {$row['TotaleDaPagare']}\n";
    echo "DataPrenotazione: {$row['DataPrenotazione']}\n";
    echo "CorsaDataPartenza: {$row['CorsaDataPartenza']}\n";
    
    // DATA ACQUISTO COPERTURA
    $data_acquisto = $oggi;
    // CODICE FASCIA
    $codice_fascia = $row['YoloCodiceFascia'];
    // NUMERO PRATICA
    $numero_pratica = substr($row['CodicePrenotazione'], 2);
    // PROGRESSIVO
    $prog = $progressivo;
    // NOMINATIVO
    $nominativo = $row['ClienteNome'];
    // CODICE FISCALE
    $codice_fiscale = '';
    // DATA PRENOTAZIONE SOGGIORNO
    $data_prenotazione = $oggi;
    if (!empty($row['DataPrenotazione'])) {
        $dt = strtotime($row['DataPrenotazione']);
        $data_prenotazione = date('dmY', $dt);
    }
    $data_acquisto = $data_prenotazione;
    // DATA INIZIO SOGGIORNO
    $data_inizio = date('dmY', strtotime($row['CorsaDataPartenza']));
    // DATA FINE SOGGIORNO (+1 giorno)
    $data_fine = date('dmY', strtotime($row['CorsaDataPartenza'] . ' +1 day'));
    // DATA ANNULLAMENTO SOGGIORNO
    if ($row['PrenotazioneStato'] == 3) {
        $data_annullamento = '01010001';
    } elseif ($row['PrenotazioneStato'] == 4) {
        $data_annullamento = $oggi;
    } else {
        $data_annullamento = '';
    }
    // VALORE DEL SOGGIORNO (senza virgola)
    $valore = intval(round(floatval(str_replace(',', '.', $row['TotaleDaPagare'])) * 100));
    // DESTINAZIONE
    $destinazione = $row['ComuneSalita'];
    if (($pos = strpos($destinazione, '(')) !== false) {
        $destinazione = trim(substr($destinazione, 0, $pos));
    }
    // PREMIO (2,75%)
    $premio = intval(round($valore * 0.0275));
    
    echo "Numero pratica: $numero_pratica\n";
    echo "Valore soggiorno: $valore\n";
    echo "Data inizio: $data_inizio\n";
   
    // Controllo esistenza riga in RT_YoloRiga
    $sqlCheck = "SELECT * FROM RT_YoloRiga 
                        WHERE NumeroPratica = '$numero_pratica'
                        ORDER BY DataInizioSoggiorno DESC";
    $rigaEsistente = $db->query_first($sqlCheck);

    if ($rigaEsistente) {
        echo "RIGA ESISTENTE TROVATA:\n";
        echo "  - Valore esistente: {$rigaEsistente['ValoreDelSoggiorno']}\n";
        echo "  - Data inizio esistente: {$rigaEsistente['DataInizioSoggiorno']}\n";
        echo "  - Tipo operazione esistente: {$rigaEsistente['TipoOperazione']}\n";
    } else {
        echo "NESSUNA RIGA ESISTENTE TROVATA\n";
    }

    // TIPO OPERAZIONE
    $tipo_operazione = 'N';
    if ($rigaEsistente && $row['PrenotazioneStato'] == 4) {
        $tipo_operazione = 'D';
        echo "TIPO OPERAZIONE: D (annullamento - stato 4)\n";
    } else if ($rigaEsistente && $row['PrenotazioneStato'] == 16) {
        $tipo_operazione = 'C';
        echo "TIPO OPERAZIONE: C (stato 16)\n";
    } else {
        if (!$rigaEsistente) {
            // Non esiste ancora: Nuova riga
            $tipo_operazione = 'N';
            echo "TIPO OPERAZIONE: N (nuova riga)\n";
        } else {
            // Esiste già: controlla valore e data inizio soggiorno
            $valoreEsistente = $rigaEsistente['ValoreDelSoggiorno'];
            $dataInizioEsistente = $rigaEsistente['DataInizioSoggiorno'];
            echo "CONFRONTO: valore $valoreEsistente vs $valore, data $dataInizioEsistente vs $data_inizio\n";
            if ($valoreEsistente != $valore || $dataInizioEsistente != $data_inizio) {
                $tipo_operazione = 'V';
                echo "TIPO OPERAZIONE: V (variazione)\n";
            } else {
                // Esiste già e valore e data inizio sono uguali: non inserire la riga
                echo "❌ SALTATA: Riga già esistente con stesso valore e data\n";
                continue;
            }
        }
    }

    // Se tipo_operazione è 'N', controlla che la partenza sia almeno una settimana dopo la prenotazione
    if ($tipo_operazione == 'N') {
        $dtPren = !empty($row['DataPrenotazione']) ? strtotime($row['DataPrenotazione']) : false;
        $dtPart = strtotime($row['CorsaDataPartenza']);
        echo "CONTROLLO 7 GIORNI:\n";
        echo "  - Data prenotazione: " . ($dtPren ? date('Y-m-d H:i:s', $dtPren) : 'VUOTA') . "\n";
        echo "  - Data partenza: " . date('Y-m-d H:i:s', $dtPart) . "\n";
        
        if ($dtPren && $dtPart < strtotime('+7 days', $dtPren)) {
            $giorni = round(($dtPart - $dtPren) / (60 * 60 * 24));
            echo "❌ SALTATA: Partenza tra $giorni giorni (< 7 giorni)\n";
            continue;
        } else {
            echo "✅ CONTROLLO 7 GIORNI: OK\n";
        }
    }

    echo "✅ RIGA VALIDA - INSERIMENTO IN CORSO\n";

    // Se la riga è valida, inserisci
    $line = [
        $data_acquisto,
        $codice_fascia,
        $numero_pratica,
        $prog,
        $nominativo,
        $codice_fiscale,
        $data_prenotazione,
        $data_inizio,
        $data_fine,
        $data_annullamento,
        $valore,
        $destinazione,
        $premio,
        $tipo_operazione
    ];
    
    // Scrivi riga manualmente senza virgolette
    $csvLine = implode($csv_sep, $line) . "\n";
    fwrite($fp, $csvLine);
    
    echo "Scritta riga CSV: " . implode($csv_sep, $line) . "\n";
    $progressivo++;
    $num_righe_inserite++;

    // Inserimento riga in RT_YoloRiga
    $yoloRigaData = [
        'DataAcquisto' => $data_acquisto,
        'Codice Fascia' => $codice_fascia,
        'NumeroPratica' => $numero_pratica,
        'Progressivo' => $prog,
        'Nominativo' => $nominativo,
        'Codice Fiscale' => $codice_fiscale,
        'DataPrenotazioneSoggiorno' => $data_prenotazione,
        'DataInizioSoggiorno' => $data_inizio,
        'DataFineSoggiorno' => $data_fine,
        'DataAnnulamento' => $data_annullamento,
        'ValoreDelSoggiorno' => $valore,
        'Destinazione' => $destinazione,
        'Premio' => $premio,
        'TipoOperazione' => $tipo_operazione,
        'YoloId' => $yoloId
    ];
    $db->insert('RT_YoloRiga', $yoloRigaData);
    echo "✅ Inserita riga DB per PrenotazioneId: {$row['PrenotazioneId']} - Progressivo: $prog\n";
    echo "=== FINE ELABORAZIONE ===\n\n";
}
fclose($fp);
echo "File CSV generato: $csv_path\n";

// Se non sono state inserite righe, elimina file e non inviare su SFTP né inserire su DB
if ($num_righe_inserite == 0) {
    unlink($csv_path);
    echo "Nessuna riga valida da esportare. File CSV e inserimenti DB annullati.\n";
    if ($yoloId) {
        $db->query("DELETE FROM RT_Yolo WHERE YoloId = " . intval($yoloId));
    }
    //exit(0);
} else {
    // Invio file via SFTP con phpseclib
    $remote_host = Config::$yolo_host;
    $remote_user = Config::$yolo_username;
    $remote_path = Config::$yolo_path_in . $csv_filename;
    $ssh_key = $basepath . Config::$yolo_sshkey;

    // Carica la chiave privata
    $key = new RSA();
    $key->loadKey(file_get_contents($ssh_key));

    // Connessione SFTP
    $sftp = new SFTP($remote_host);
    if (!$sftp->login($remote_user, $key)) {
        echo "Login SFTP fallito\n";
        exit(1);
    }

    // Carica il file CSV
    if ($sftp->put($remote_path, $csv_path, SFTP::SOURCE_LOCAL_FILE)) {
        echo "File trasferito su $remote_host:$remote_path\n";
    } else {
        echo "Errore nel trasferimento del file via SFTP\n";
        //exit(1);
    }
}

// --- FASE DI LETTURA OUTPUT YOLO ---

$remote_dirOut = Config::$yolo_path_out;
$remote_host = Config::$yolo_host;
$remote_user = Config::$yolo_username;
$ssh_key = $basepath . Config::$yolo_sshkey;

// Carica la chiave privata
$key = new RSA();
$key->loadKey(file_get_contents($ssh_key));

// Connessione SFTP per lettura out
$sftp_out = new SFTP($remote_host);
if (!$sftp_out->login($remote_user, $key)) {
    echo "Login SFTP OUT fallito\n";
    exit(1);
} else {
    echo "Login SFTP OUT OK\n";
}

echo "Connessione SFTP OUT riuscita. Directory remota: $remote_dirOut\n";

// Lista file remoti e costruzione lista da processare
$remoteFiles = $sftp_out->nlist($remote_dirOut);
$filesCatalog = [];
if ($remoteFiles !== false) {
    foreach ($remoteFiles as $file) {
        if ($file == '.' || $file == '..') continue;
        if (preg_match('/^BB_OUT_(\d{8})\.csv$/', $file, $m)) {
            $key = $m[1] . '0000'; // YYYYMMDD + HHMM fittizio per ordinare
            $filesCatalog[] = ['name' => $file, 'key' => $key];
        } elseif (preg_match('/^ERRORI\.(\d{12})\.csv$/', $file, $m)) {
            $key = $m[1]; // YYYYMMDDHHMM
            $filesCatalog[] = ['name' => $file, 'key' => $key];
        }
    }
    usort($filesCatalog, function($a, $b) { return strcmp($a['key'], $b['key']); });
    echo "Totale file remoti candidati: " . count($filesCatalog) . "\n";
} else {
    echo "Directory remota di output non trovata o errore di lettura: $remote_dirOut\n";
    exit(1);
}

if(count($filesCatalog) == 0) {
    echo "Nessun file di output da elaborare.\n";
    exit(0);
}

foreach ($filesCatalog as $entry) {
    $fileName = $entry['name'];
    // Salta file già processati
    $exists = $db->query_first("SELECT YoloId FROM RT_Yolo WHERE Tipo = 'O' AND Nome = '" . $db->escape($fileName) . "' LIMIT 1");
    if ($exists && isset($exists['YoloId'])) {
        echo "[YOLO OUT] Saltato già processato: $fileName\n";
        continue;
    }
    $remoteFilePath = rtrim($remote_dirOut, '/\\') . '/' . $fileName;
    $localTmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

    // Scarica il file remoto in locale per la lettura
    if (!$sftp_out->get($remoteFilePath, $localTmpFile)) {
        echo "[YOLO OUT] Errore download file remoto: $remoteFilePath\n";
        continue;
    }
    echo "[YOLO OUT] Scaricato file remoto: $remoteFilePath -> $localTmpFile\n";

    // 2. Salva il record nella tabella RT_Yolo (Tipo = 'O')
    $yoloOutData = [
        'Tipo' => 'O',
        'Nome' => $fileName,
        'Data' => date('Y-m-d H:i:s')
    ];
    $yoloOutId = $db->insert('RT_Yolo', $yoloOutData);
    echo "[YOLO OUT] Inserito record RT_Yolo per file: $fileName (YoloId: $yoloOutId)\n";

    // 3. Leggi il file CSV e inserisci in RT_YoloRigaOut
    if (($fpOut = fopen($localTmpFile, 'r')) !== false) {
        $firstLine = fgetcsv($fpOut, 0, ';');
        $okFound = false;

        // Caso 1: la prima riga è "000TUTTO OK" (file senza header)
        if ($firstLine !== false && isset($firstLine[0]) && preg_match('/^0+TUTTO OK$/i', trim($firstLine[0]))) {
            $return = $db->insert('RT_YoloRigaOut', [
                'YoloId' => $yoloOutId,
                'Record' => $firstLine[0],
                'CodiceErrore' => isset($firstLine[1]) ? $firstLine[1] : '',
                'DescrizioneErrore' => isset($firstLine[2]) ? $firstLine[2] : ''
            ]);
            echo "[YOLO OUT] File $fileName: 000TUTTO OK trovato nella prima riga, inserita una sola riga.\n";
            $okFound = true;
        } else {
            // Caso 2: la prima riga potrebbe essere un header, leggi la seconda riga
            $secondLine = fgetcsv($fpOut, 0, ';');
            
            if ($secondLine !== false && isset($secondLine[0]) && preg_match('/^0+TUTTO OK$/i', trim($secondLine[0]))) {
                // La seconda riga è "000TUTTO OK" (file con header)
                $db->insert('RT_YoloRigaOut', [
                    'YoloId' => $yoloOutId,
                    'Record' => $secondLine[0],
                    'CodiceErrore' => isset($secondLine[1]) ? $secondLine[1] : '',
                    'DescrizioneErrore' => isset($secondLine[2]) ? $secondLine[2] : ''
                ]);
                echo "[YOLO OUT] File $fileName: 000TUTTO OK trovato nella seconda riga (dopo header), inserita una sola riga.\n";
                $okFound = true;
            }
        }

        // Se non è stato trovato "000TUTTO OK", salva tutte le righe
        if (!$okFound) {
            // Riavvolgi il file per leggere dall'inizio
            rewind($fpOut);
            
            while (($row = fgetcsv($fpOut, 0, ';')) !== false) {
                $db->insert('RT_YoloRigaOut', [
                    'YoloId' => $yoloOutId,
                    'Record' => $row[0],
                    'CodiceErrore' => isset($row[1]) ? $row[1] : '',
                    'DescrizioneErrore' => isset($row[2]) ? $row[2] : ''
                ]);
                echo "[YOLO OUT] File $fileName: inserita riga (Record: {$row[0]})\n";
            }
        }
        
        fclose($fpOut);
        echo "[YOLO OUT] Completata lettura file: $fileName\n";
    } else {
        echo "[YOLO OUT] Errore apertura file locale: $localTmpFile\n";
    }
    // Rimuovi il file temporaneo locale dopo la lettura
    if (file_exists($localTmpFile)) {
        unlink($localTmpFile);
    }
}

