<?php
/**
 * PRINCIPALE.PHP
 * 
 * Pagina principale dell'applicazione gestionale Bertoldi
 * Gestisce l'interfaccia utente principale con menu dinamico e dashboard
 * 
 * @author Sistema Gestionale Bertoldi
 * @version 1.0
 * @since PHP 5.6.4
 */

// Configurazione base del sistema
$basepath = $_SERVER['DOCUMENT_ROOT'];
include($basepath . "/main_include.php");

// Inizializzazione componenti principali
$config = new Config();
$run = $config->load();
$page = new Html_Page();
$classespath_ = Config::$classespath;
include_once($classespath_ . "class.Sede.php");

/**
 * Carica il contenuto principale della pagina
 * 
 * Genera l'interfaccia utente completa includendo:
 * - Header con informazioni operatore e sede
 * - Menu dinamico basato sui permessi utente
 * - Area contenuto principale (dashboard)
 * 
 * @global object $user Oggetto utente corrente
 * @global object $db Connessione database
 * @global array $dizionario Array traduzioni/etichette
 * @return void
 */
function load_content()
{
    global $user, $db, $dizionario;
    
    // Inizializzazione dati sede corrente
    $sede = new Sede();
    $sede->conn = $db;
    $sede->inizializza($user->SedeId);
    ?>
    <!-- CSS e stili per l'interfaccia -->
    <link rel="stylesheet" href="/css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="/css/printreport.css" media="print" />
    
    <!-- Header principale dell'applicazione -->
    <div class="brain_row brain_top">
        <div class="top-menu">
            <!-- Logo dell'applicazione -->
            <div class="brain_titolo_top">
                <img class="logo" src="/images/logo.png" title="<?= Config::$application_name ?>">
            </div>
            
            <!-- Informazioni utente e sede -->
            <div class="brain_utente">
                <p class="brain_infoSede">
                    <?= $dizionario['operatore']['operatore'] ?>: 
                    <strong><?= $user->Cognome . " " . $user->Nome ?> (<?= $user->Username ?>)</strong>
                </p>
                <p class="brain_info">
                    <strong><?= $dizionario['generale']['sede'] ?>:</strong> <?= $sede->Comune ?> - <?= $sede->Indirizzo ?> - 
                    <strong>IP:</strong> <?= getenv('REMOTE_ADDR') ?>
                </p>
                
                <!-- Menu utente rapido -->
                <a href="javascript:void(0);" onclick="javascript:ExternalLoad('operatore','operatore.php?do=mod_password');">
                    <?= $dizionario['operatore']['cambia_password'] ?>
                </a> 
                <a title="esci" href="/logout.php?do=logout">Logout</a><br>
                <br />
            </div>
        </div>
        <?php
        // Logo personalizzato per organizzazione (se presente)
        $filename = Config::$odcfile . "/" . $user->OdcId . "/images/logo.jpg";
        
        if (file_exists($filename)) {
            ?>
            <div class="brain_logo">
                <img src="/odcfile/<?= $user->OdcId ?>/images/logo.jpg" alt="Logo Organizzazione">
            </div>
            <?php
        }
        ?>
        
        
        <!-- Menu di navigazione principale -->
        <div id="menubar">
            <ul id="topnav">
                <!-- Voce menu Home/Dashboard (sempre presente) -->
                <li class="brain_menu_el" id="brain_menu_home">
                    <span id="span100" class="brain_bgMenu brain_sel">
                        <span class="brain_vociMenu">
                            <a href="javascript:void(0);" onclick="loadMainContentFromMenu('home','home.php',100);" title="Home">
                                <span class="link_menu">
                                    <i class="fa fa-home" aria-hidden="true"></i> Dashboard
                                </span>
                            </a>
                        </span>
                    </span>
                </li>
                
                <?php
                /*
                 * GENERAZIONE MENU DINAMICO
                 * 
                 * Carica le voci di menu basate sui permessi dell'operatore corrente.
                 * La query unisce le tabelle dei ruoli, permessi e moduli per determinare
                 * quali voci di menu visualizzare per l'utente autenticato.
                 */
                
                // Query per ottenere menu e moduli accessibili dall'operatore
                $sql = "SELECT
                    OperatoreRuolo.OperatoreId AS OperatoreId,
                    AppModulo.AppModulo AS AppModulo,
                    AppModulo.AppModuloId AS AppModuloId,
                    AppMenu.AppMenuId AS AppMenuId,
                    AppModulo.AppNomeCartella AS AppNomeCartella,
                    AppModulo.AppNomePagina AS AppNomePagina,
                    AppMenu.Classe AS Classe,
                    AppMenu.AppMenu AS AppMenu,
                    AppMenu.Aggiungi AS Aggiungi
                FROM ((`RuoloPermesso` JOIN `OperatoreRuolo` ON((`RuoloPermesso`.`RuoloId` = `OperatoreRuolo`.`RuoloId`))) 
                      JOIN (`AppMenu` JOIN `AppModulo` ON((`AppMenu`.`AppMenuId` = `AppModulo`.`AppMenuId`))) 
                      ON((`RuoloPermesso`.`AppModuloId` = `AppModulo`.`AppModuloId`)))
                WHERE OperatoreId = $user->OperatoreId 
                  AND ((`RuoloPermesso`.`AppPermessoId` <> 4) AND (`AppModulo`.`Stato` = 1))
                GROUP BY `OperatoreRuolo`.`OperatoreId`, `AppMenu`.`AppMenu`, `AppModulo`.`AppModulo`, 
                         `AppModulo`.`AppModuloId`, `AppMenu`.`AppMenuId`, `AppModulo`.`AppNomeCartella`, 
                         `AppModulo`.`AppNomePagina`, `AppMenu`.`Ordinamento`, `AppModulo`.`ModuloOrdinamento`, `AppMenu`.`Classe`
                ORDER BY `AppMenu`.`Ordinamento`, `AppModulo`.`ModuloOrdinamento`";
        
                $result = $db->query($sql);
                
                // Elaborazione risultati query menu
                $OldMenuCorrenteId = "";
                while ($myrow = $db->fetch($result)) {
                    $MenuCorrenteId = $myrow['AppMenuId'];
                    $idmenu = 0;
                    
                    // Controllo per evitare duplicati nel menu principale
                    if ($MenuCorrenteId != $OldMenuCorrenteId) {
                        // Estrazione dati menu corrente
                        $titolo = $myrow['AppMenu'];
                        $classe = $myrow['Classe'];
                        $cartella = $myrow['AppNomeCartella'];
                        $pagina = $myrow['AppNomePagina'];
                        $idmenu = $myrow['AppMenuId'];
                        $idmodulo = $myrow['AppModuloId'];
                        $Aggiungi = $myrow['Aggiungi'];
                        $OldMenuCorrenteId = $idmenu;
                        
                        ?>
                        <!-- Generazione voce menu principale -->
                        <li class="brain_menu_el" id="brain_menu_<?= $classe ?>">
                            <span id="span<?= $idmenu ?>" class="brain_bgMenu">
                                <?php 
                                // Assegnazione icone FontAwesome per categoria menu
                                switch ($classe) {
                                    case 'mediazioni': 
                                        $icona = '<i class="fa fa-handshake-o" aria-hidden="true"></i>';
                                        break;
                                    case 'agenda':
                                        $icona = '<i class="fa fa-calendar" aria-hidden="true"></i>';
                                        break;
                                    case 'contabilita':
                                        $icona = '<i class="fa fa-eur" aria-hidden="true"></i>';
                                        break;
                                    case 'statistiche':
                                        $icona = '<i class="fa fa-bar-chart" aria-hidden="true"></i>';
                                        break;
                                    case 'admin':
                                        $icona = '<i class="fa fa-wrench" aria-hidden="true"></i>';
                                        break;
                                    case 'privilegi':
                                        $icona = '<i class="fa fa-users" aria-hidden="true"></i>';
                                        break;
                                }
                                ?>
                                
                                <span class="brain_vociMenu">
                                    <a href="javascript:void(0);" onclick="loadMainContentFromMenu('<?= $cartella ?>','<?= $pagina ?>','<?= $idmenu ?>');">
                                        <span class="link_menu">
                                            <?php echo $icona ?> <?= $dizionario['menu'][$idmenu] ?>
                                        </span>
                                    </a>
                                </span>
                            </span>
                        <?php
                    }
                    
                    /*
                     * GENERAZIONE SOTTOMENU
                     * 
                     * Per ogni menu principale, carica i sottomenu (moduli) disponibili
                     * Query simile alla precedente ma filtrata per il menu corrente
                     */
                    
                    // Query per ottenere i moduli del menu corrente
                    $sql = "SELECT
                        OperatoreRuolo.OperatoreId AS OperatoreId,
                        AppModulo.AppModulo AS AppModulo,
                        AppModulo.AppModuloId AS AppModuloId,
                        AppMenu.AppMenuId AS AppMenuId,
                        AppModulo.AppNomeCartella AS AppNomeCartella,
                        AppModulo.AppNomePagina AS AppNomePagina,
                        AppMenu.Classe AS Classe,
                        AppMenu.AppMenu AS AppMenu,
                        AppMenu.Aggiungi AS Aggiungi
                    FROM ((`RuoloPermesso` JOIN `OperatoreRuolo` ON((`RuoloPermesso`.`RuoloId` = `OperatoreRuolo`.`RuoloId`))) 
                          JOIN (`AppMenu` JOIN `AppModulo` ON((`AppMenu`.`AppMenuId` = `AppModulo`.`AppMenuId`))) 
                          ON((`RuoloPermesso`.`AppModuloId` = `AppModulo`.`AppModuloId`)))
                    WHERE OperatoreRuolo.OperatoreId = $user->OperatoreId 
                      AND AppMenu.AppMenuId = $idmenu 
                      AND ((`RuoloPermesso`.`AppPermessoId` <> 4) AND (`AppModulo`.`Stato` = 1))
                    GROUP BY `OperatoreRuolo`.`OperatoreId`, `AppMenu`.`AppMenu`, `AppModulo`.`AppModulo`, 
                             `AppModulo`.`AppModuloId`, `AppMenu`.`AppMenuId`, `AppModulo`.`AppNomeCartella`, 
                             `AppModulo`.`AppNomePagina`, `AppMenu`.`Ordinamento`, `AppModulo`.`ModuloOrdinamento`, `AppMenu`.`Classe`
                    ORDER BY `AppMenu`.`Ordinamento`, `AppModulo`.`ModuloOrdinamento`";
                    
                    $result1 = $db->query($sql);
                    
                    ?>
                    <!-- Container sottomenu (dropdown) -->
                    <div style="opacity: 0; display: none;" class="sub">
                        <div class="row">
                            <div class="sottomenu sottomenu">
                                <ul>
                                    <?php
                                    // Inizializzazione sede per controlli aggiuntivi
                                    $vis = true;
                                    $sedd = new Sede();
                                    $sedd->conn = $db;
                                    $sedd->inizializza($user->SedeId);
                                    
                                    // Generazione voci sottomenu
                                    while ($myrow1 = $db->fetch($result1)) {
                                        // Estrazione dati modulo
                                        $AppModulo = $myrow1['AppModulo'];
                                        $classe = $myrow1['Classe'];
                                        $cartella = $myrow1['AppNomeCartella'];
                                        $pagina = $myrow1['AppNomePagina'];
                                        $idmenu = $myrow1['AppMenuId'];
                                        $idmodulo = $myrow1['AppModuloId'];
                                        ?>
                                        
                                        <!-- Voce sottomenu -->
                                        <li>
                                            <a href="javascript:void(0);" onclick="loadMainContentFromMenu('<?= $cartella ?>','<?= $pagina ?>','<?= $idmenu ?>');">
                                                <?= $dizionario['menu_voci'][$idmodulo] ?>
                                            </a>
                                        </li>
                                        
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>                
                <?php
                } // Fine ciclo generazione menu
                ?>
            </ul>
        </div>

        <br style="clear:both;" />
    </div>
    
    <!-- Area contenuto principale -->
    <div id="brain_main-content">
        <?php include("protected/modules/home/home.php"); ?>
    </div>
    
    <?php
}
/**
 * Funzione di rilevamento browser
 * 
 * Analizza lo user agent del client per determinare tipo e versione del browser.
 * Supporta i principali browser: Opera, IE, Konqueror, Safari, Firefox, Netscape, Chrome.
 * 
 * @param string $which_test Tipo di informazione richiesta ('browser', 'number', 'full')
 * @return string|array Informazioni browser richieste
 */
function browser_detection($which_test) 
{
    // Inizializzazione variabili
    $browser_name = '';
    $browser_number = '';
    
    // Ottieni user agent string (minuscolo per confronti)
    $browser_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    
    // Array definizione browser supportati
    // Formato: [identificatore, DOM supportato, abbreviazione]
    $a_browser_types[] = array('opera', true, 'op');
    $a_browser_types[] = array('msie', true, 'ie');
    $a_browser_types[] = array('konqueror', true, 'konq');
    $a_browser_types[] = array('safari', true, 'saf');
    $a_browser_types[] = array('gecko', true, 'moz');
    $a_browser_types[] = array('mozilla/4', false, 'ns4');
    $a_browser_types[] = array('Chrome', false, 'ch');

    // Ciclo attraverso i tipi di browser per identificazione
    for ($i = 0; $i < count($a_browser_types); $i++) {
        $s_browser = $a_browser_types[$i][0];
        $b_dom = $a_browser_types[$i][1];
        $browser_name = $a_browser_types[$i][2];
        
        // Se l'identificatore è trovato nello user agent
        if (stristr($browser_user_agent, $s_browser)) {
            // Per Firefox/Mozilla, cerchiamo 'rv' invece di 'gecko'
            // Questo potrebbe fallire su Galeon (nessun numero rv)
            if ($browser_name == 'moz') {
                $s_browser = 'rv';
            }
            
            $browser_number = browser_version($browser_user_agent, $s_browser);
            break;
        }
    }
    // Determinazione valore di ritorno in base al parametro richiesto
    if ($which_test == 'browser') {
        return $browser_name;
    }
    elseif ($which_test == 'number') {
        return $browser_number;
    }
    elseif ($which_test == 'full') {
        // Ritorna entrambe le informazioni in array
        $a_browser_info = array($browser_name, $browser_number);
        return $a_browser_info;
    }
}

/**
 * Estrae il numero di versione del browser dallo user agent
 * 
 * Funzione di supporto per browser_detection(), analizza lo user agent
 * per estrarre il numero di versione del browser identificato.
 * 
 * @param string $browser_user_agent User agent completo del browser
 * @param string $search_string Stringa identificatore del browser
 * @return string Numero di versione del browser (vuoto se non trovato)
 */
function browser_version($browser_user_agent, $search_string)
{
    $string_length = 8; // Lunghezza massima per ricerca versione
    $browser_number = ''; // Inizializzazione (vuoto se non trovato)

    // Trova posizione stringa identificatore
    $start_pos = strpos($browser_user_agent, $search_string);
    
    // Inizia ricerca 1 carattere dopo la stringa identificatore
    $start_pos += strlen($search_string) + 1;
    
    // Estrai la porzione numerica più lunga possibile
    for ($i = $string_length; $i > 0; $i--) {
        // Verifica che la sottostringa sia completamente numerica
        if (is_numeric(substr($browser_user_agent, $start_pos, $i))) {
            $browser_number = substr($browser_user_agent, $start_pos, $i);
            break;
        }
    }
    
    return $browser_number;
}

/*
 * ESECUZIONE PRINCIPALE
 * 
 * Controllo autenticazione utente ed esecuzione logica principale:
 * - Verifica che l'utente sia autenticato (oggetto $user valido)
 * - Inizializza connessione database con MySQLi
 * - Genera l'interfaccia HTML completa
 * - Redirect al login se utente non autenticato
 */

if (is_object($user)) {
    // Utente autenticato: inizializza sistema
    $db = new Database();
    $db->connect();
    $user->conn = $db;
    
    // Genera interfaccia HTML
    $page->html_header();
    load_content();
    $page->html_footer();
    
} else {
    // Utente non autenticato: redirect al login
    header("location: index.php");
    exit();
}
?>