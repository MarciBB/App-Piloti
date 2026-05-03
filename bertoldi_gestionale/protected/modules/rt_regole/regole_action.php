<?php
// Determina il percorso base del server
$basepath = $_SERVER['DOCUMENT_ROOT'];

// Include il file principale
include_once($basepath . "/main_include.php");

// Inizializza la configurazione
$config = new Config();
$run = $config->load(); 

// Percorsi dei moduli e delle classi configurati
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

// Inizializza gestione errori
$errors = new Errors();

// Inclusione delle classi necessarie
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.Percorso.php");
include_once($classespath_ . "class.Linea.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.TrattaTipo.php");
include_once($classespath_ . "class.Mezzo.php");
include_once($classespath_ . "class.TrattaDirezione.php");
include_once($classespath_ . "class.Provvigione.php");

// Identificativo del modulo corrente
$ModuloId = 43;

// Variabile globale per il wizard della tratta
global $tratta_wizard;
$tratta_wizard = null;

// Recupero sessione wizard se esiste
if (isset($_SESSION['TRATTA_WIZARD'])) {
    $tratta_wizard = unserialize($_SESSION['TRATTA_WIZARD']);
}

// Funzione per creare una nuova voce
function create() {
    global $user, $db;

    // Inizializza il registro delle operazioni
    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // Recupera dati dal form
    $data = $_POST['RimborsoPercentuale'];
    $data2 = $_POST['RimborsoFisso'];

    // Elimina i rimborsi esistenti
    $lastidA = $db->delete("RT_RimborsoPenale", "RimborsoPenaleId > 0");

    // Inserisce nuovi dati
    foreach ($data as $chiave => $valore) { 
        // Pulisce la chiave da caratteri indesiderati
        $chiave = str_replace("'", "", $chiave);
        $chiave = str_replace("\\", "", $chiave);

        // Divide la chiave in parti
        $arr_chiave = explode('_', $chiave);
        $regolaRimborsoId = $arr_chiave[1];
        $LineaId = $arr_chiave[0];

        // Imposta valori di default se non presenti
        if (!isset($valore) || $valore == '') {
            $valore = 0;
        }
		if (!isset($data2[$chiave]) || $data2[$chiave] == '') {
            $fisso = 0;
        } else {
			$fisso = $data2[$chiave];
		}

        // Prepara i dati per l'inserimento
        $d1 = [
            'LineaId' => $LineaId,
            'RimborsoRegolaId' => $regolaRimborsoId,
            'Percentuale' => $valore,
            'Fisso' => $fisso
        ];

        // Inserisce il record nel database
        $lastidA = $db->insert("RT_RimborsoPenale", $d1);
    }

    // Output di conferma
    echo("ok" . "," . $lastidA);

    // Chiude la connessione e termina lo script
    $db->close();
    exit();
}

// Verifica se l'utente è loggato
if (is_object($user)) {
    // Inizializza connessione al database
    $db = new Database();
    $db->connect();
    $user->conn = $db;

    // Recupera i permessi per il modulo corrente
    $permessi = $user->get_permessi_modulo($ModuloId);

    if (sizeof($permessi) > 0) {
        // Gestisce richieste POST
        if (!empty($_POST)) {
            switch ($_POST['action']) {
                case "create":
                    $FunzioneId = 1;

                    // Controlla i permessi per la funzione
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso)) {
                        create();
                    } else {
                        Errors::$ErrorePermessiModuloFunzione;
                    }
                    break;
            }
        }
    } else {
        // Nessun permesso per il modulo
        echo("no");
    }
} else {
    // L'utente non è loggato, reindirizza al logout
    header("Location: /logout.php");
}
?>
