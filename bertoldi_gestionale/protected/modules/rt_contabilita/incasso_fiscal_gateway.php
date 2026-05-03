<!-- Inclusione dei fogli di stile -->
<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/print_stat_mediazione.css" media="print" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />

<?php 
// Imposta il percorso base del documento
$basepath = $_SERVER['DOCUMENT_ROOT'];

// Inclusione dei file principali
include_once($basepath . "/main_include.php");

// Inizializzazione della configurazione
$config = new Config();
$run = $config->load(); 

// Percorsi delle classi e dei moduli
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

// Gestione degli errori
$errors = new Errors();

// Inclusione delle classi necessarie
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Nazione.php");
include_once($classespath_ . "class.Regione.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.Percorso.php");

// ID del modulo corrente
$ModuloId = 58;

/**
 * Funzione che mostra la lista e il form di filtro
 */
function show_list() {
    global $user, $dizionario;
    $db = new Database();
    $db->connect();
    $page = new Form();

    // Inclusione del validatore specifico
    include_once("incasso_fiscal_gateway_validator.php");  

    // Data corrente
    $datacorrente = Date('d/m/Y');
    ?>

    <div>
        <!-- Inizio riga per i filtri -->
        <div class="brainFiltri">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="rowForm">
                    <label for="Dal"><?= $dizionario['fiscaly']['incasso_giorno'] ?></label>
                    <input class="required" type="text" value="<?= $datacorrente ?>" id="Dal" name="Dal" maxlength="255" size="10">
                </div>
                <div class="rowForm">
                    <input name="applica" type="submit" value="<?= $dizionario['generale']['genera'] ?>" />
                </div>
                <br style="clear:both;" />
            </form>
        </div>
        <!-- Fine riga per i filtri -->

        <div id="risultato_report">
            <!-- Qui verrà mostrato il risultato del report -->
        </div>
    </div>

    <?php
}

// Verifica se l'utente è loggato
if (is_object($user)) {
    $db = new Database();
    $db->connect();
    $user->conn = $db;

    // Recupera i permessi dell'utente per il modulo
    $permessi = $user->get_permessi_modulo($ModuloId);

    // Gestione dell'azione richiesta
    if (!isset($_REQUEST['do'])) {
        $do = '';
    } else {
        $do = $_REQUEST['do'];
    }

    switch ($do) {
        default:
            $FunzioneId = 1;
            // Controlla i permessi per la funzione specifica
            $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

            // Se ci sono permessi, mostra la lista
            if (sizeof($permesso))
                show_list();

            break;
    }
} 
// Se l'utente non è loggato, reindirizza al logout
else {
    header("Location: /logout.php");
}
?>