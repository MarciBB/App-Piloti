<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/stile_prenotazioni.css" />
<?php
// Imposta il percorso base per l'applicazione
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

// Inizializza la configurazione e carica le impostazioni
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

// Includi i file delle classi necessarie
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Ruolo.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Nazione.php");
include_once($classespath_ . "class.Regione.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Percorso.php");
include_once($classespath_ . "class.Linea.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.TrattaTipo.php");
include_once($classespath_ . "class.Mezzo.php");
include_once($classespath_ . "class.TrattaDirezione.php");
include_once($classespath_ . "class.Prenotazione.php");
include_once($classespath_ . "class.Fermata.php");

// Definisci variabili globali
global $ModuloId;
$ModuloId = 5; // ID del modulo per la mediazione base
global $user, $prenotazione_wizard, $funzione_edit, $abilita_modifica;

$funzione_edit = false; // Disabilita la funzionalità di modifica di default
$prenotazione_wizard = null;

// Controlla se esiste una sessione del wizard di prenotazione
if (isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
    $prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
}

// Funzione per visualizzare la lista dei biglietti da confermare
function show_list()
{
    global $user, $HtmlCommon, $ModuloId, $dizionario;

    // Mostra il titolo della pagina e del box
    $HtmlCommon->html_titolo_pagina($dizionario['biglietto']['titolo_da_confermare'], 0, "", "");
    $HtmlCommon->html_titolo_box($dizionario['biglietto']['titolo_da_confermare']);

    // Connetti al database
    $db = new Database();
    $db->connect();

    // Includi file aggiuntivi per la validazione e il rendering della tabella dati
    include_once("biglietto_validator.php");
    include_once("biglietto_datatable_daconfermare.php");

    // Controlla i permessi dell'utente per aggiungere nuovi biglietti
    $aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
    if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_biglietto','biglietto.php?do=add',$dizionario['prenotazioni']['btn_aggiungi']);
    ?>
    <!-- Renderizza la tabella dati per i biglietti -->
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
        <thead>
            <tr class="brain_tabellaTr">
                <!-- Definisci le intestazioni della tabella -->
                <th width="8%"><?= $dizionario['generale']['stato'] ?></th>
                <th width="15%"><?= $dizionario['generale']['agenzia'] ?></th>
                <th width="8%"><?= $dizionario['biglietto']['data_operazione'] ?></th>
                <th width="8%"><?= $dizionario['generale']['num_itinerario'] ?></th>
                <th width="15%"><?= $dizionario['generale']['cliente'] ?></th>
                <th width="15%"><?= $dizionario['generale']['telefono'] ?></th>
                <th width="15%"><?= $dizionario['generale']['corsa'] ?></th>
                <th width="8%"><?= $dizionario['generale']['data_partenza'] ?></th>
                <th width="5%"><?= $dizionario['generale']['ora_partenza'] ?></th>
                <th width="10%"><?= $dizionario['generale']['da'] ?></th>
                <th width="10%"><?= $dizionario['generale']['a'] ?></th>
                <th width="5%"><?= $dizionario['generale']['pax'] ?></th>
                <th width="5%"><?= $dizionario['generale']['totale'] ?></th>
                <th width="3%"><?= $dizionario['generale']['conferma'] ?></th>
                <th width="3%"><?= $dizionario['generale']['edita'] ?></th>
            </tr>
            <tr class="brain_tabellaFilter">
                <!-- Definisci i filtri di input per ogni colonna -->
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="text" /></th>
                <th><input type="hidden" /></th>
                <th><input type="hidden" /></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Mostra un messaggio di caricamento mentre i dati vengono recuperati -->
                <td colspan="15" class="dataTables_empty">
                    <i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br>
                    <?= $dizionario['generale']['caricamento_in_corso'] ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
    // Chiudi la connessione al database
    $db->close();
}

// Controlla se l'oggetto utente esiste
if (is_object($user)) {
    $db = new Database();
    $db->connect();
    $user->conn = $db;

    // Assegna la connessione al wizard delle tratte se esiste
    if (is_object($tratta_wizard)) {
        $tratta_wizard->conn = $db;
    }

    // Ottieni i permessi dell'utente per il modulo
    $permessi = $user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi) > 0) {
        $do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';

        // Gestisci le diverse azioni in base al parametro 'do'
        switch ($do) {
            default:
                $FunzioneId = 2; // ID funzione per visualizzare la lista
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso)) {
                    show_list(); // Mostra la lista se l'utente ha il permesso
                } else {
                    $errore->stampa_errore(2); // Stampa un errore se il permesso è negato
                }
                break;
        }
    } else {
        $errore->stampa_errore(1); // Stampa un errore se non ci sono permessi
    }
} else {
    // Reindirizza alla pagina di logout se l'oggetto utente non è valido
    header("Location: /logout.php");
}
