<?php
// Imposta il percorso base e include i file necessari
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include_app.php");

// Inizializzazione configurazione e percorsi
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

// Includi le classi necessarie
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "/class.TipologiaBus.php");
include_once($classespath_ . "class.DT.php");
include_once($classespath_ . "Graph/class.LineaGraph.php");
include_once($classespath_ . "Graph/class.GraphUtil.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.PrenotazioneDettaglio.php");
include_once($classespath_ . "class.Fermata.php");
include_once($classespath_ . "class.GestioneOttimizzataFlotta.php");
include_once($classespath_ . "class.Autisti.php");
include_once($classespath_ . "class.Flotta.php");
include_once($classespath_ . "class.PreparazioneBusAutista.php");
include_once($classespath_ . "class.GestioneOttimizzataModifiche.php");
include_once($classespath_ . "class.Prenotazione.php");

// Abilita la visualizzazione degli errori
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$ModuloId = 1;
global $db, $autista, $user;
$db = new Database();
$db->connect();

// Controllo login autista
if (!isset($_SESSION['autista'])) {
    header('Location: ./login.php');
}
$autista = $_SESSION['autista'];
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

// Funzione principale per la creazione/modifica ticket
function nuovaTicket()
{
    if (!isset($_SESSION['tipo_tour'])) {
        // Redirect se manca il tipo tour
        header("Location: ./index.php");
        exit;
    }
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html lang="en" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <!-- Meta e risorse -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Bertoldi Boats - Nuovo Ticket</title>
        <link rel="manifest" href="./manifest.json">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="./css/new-ticket.css" />
        <link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="/js/jquery.min.js"></script>
        <script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
        <script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
        <script type="text/javascript" src="/js/menu_hover.js"></script>
        <script type="text/javascript" language="javascript" src="/js/dialogbox.js?v=3"></script>
        <script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
        <script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
        <script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
        <script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
        <!-- Bootstrap CSS e JS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
        <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
        <script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    </head>
    <?php
    // Inizializzazione variabili e oggetti utente
    $codiceControllo = 'gC_6XHrZvC9$avW!';
    global $user, $HtmlCommon, $dizionario, $autista;
    $db = new Database();
    $db->connect();

    $user = new Operatore();
    $user->OperatoreId = 1;
    $user->OdcId = 1;
    $user->IsAdmin = 1;
    $user->GestoreId = 1;

    // Recupera dati da POST o SESSION
    if (isset($_REQUEST['DataPartenza'])) {
        $DataPartenza = $_REQUEST['DataPartenza'];
    }
    if (isset($_REQUEST['CorsaId'])) {
        $CorsaId = $_REQUEST['CorsaId'];
    }

    $DataPartenza = date('Y-m-d');
    $datacorrente = Date('d/m/Y');
    $CorsaId = 0;

    // Se esiste una prenotazione in sessione, recupera i dati relativi
    if (isset($_SESSION['PrenotazioneId'])) {
        $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId']." AND Direzione = 'A'";
        $resultRow = $db->query_first($sql);
        $_SESSION['porto_partenza'] = $resultRow['ComuneSalitaId'];
        $_SESSION['porto_destinazione'] = $resultRow['ComuneDiscesaId'];
        $sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId'];
        $resultRow = $db->query_first($sql);
        $_SESSION['tipo_viaggio'] = $resultRow['TipoViaggioId'];
    }

    // Aggiorna variabili da POST se presenti
    if (isset($_POST['CorsaId']))
        $CorsaId = $_POST['CorsaId'];

    if (isset($_POST['DataPartenza'])) {
        $dt = new DT();
        $post_dal = $_POST['DataPartenza'];
        $DataPartenza = $_POST['DataPartenza'];
        $dateTime = new DateTime($DataPartenza);
        $datacorrente = $dateTime->format('d/m/Y');
        $_SESSION['ticket_date'] = $_POST['DataPartenza'];
    }

    if (isset($_POST['porto_partenza'])) {
        $porto_partenza = $_POST['porto_partenza'];
        $_SESSION['porto_partenza'] = $porto_partenza;
    }
    if (isset($_POST['porto_destinazione'])) {
        $porto_destinazione = $_POST['porto_destinazione'];
        $_SESSION['porto_destinazione'] = $porto_destinazione;
    }
    if (isset($_POST['tipo_viaggio'])) {
        $tipo_viaggio = $_POST['tipo_viaggio'];
        $_SESSION['tipo_viaggio'] = $tipo_viaggio;
    }

    // Recupera tipo tour e esperienza selezionata
    $tipoTour = $_SESSION['tipo_tour'];
    $selezionaLesperienza = $_SESSION['seleziona_lesperienza'];

    if($selezionaLesperienza <> 14) {
        // Query per porti di partenza
        $sqlPortoPartenza = "SELECT f.FermataId, f.FermataNome, c.Comune, c.ComuneId, o.CorsaId, MIN(o.Orario) AS Orario, o.GiorniAggiuntivi, f.TrattaId 
            FROM RT_Fermata f 
            LEFT JOIN RT_Orario o ON f.FermataId = o.FermataId 
            LEFT JOIN Comune c ON c.ComuneId = f.ComuneId 
            LEFT JOIN Provincia p ON c.provincia = p.ProvinciaId 
            LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId 
            WHERE t.LineaId = $selezionaLesperienza 
                AND o.Orario IS NOT NULL AND o.Orario <> '' 
                AND o.Stato = 1 AND o.Cancella = 0 
                AND f.Stato = 1 AND f.Cancella = 0 
                AND f.IsPickup = 1 
                AND c.ComuneId IS NOT NULL 
                AND f.WebSelling = 1 
            GROUP BY c.ComuneId 
            ORDER BY c.Comune ASC";
        $arrPortoPartenza = $db->fetch_array($sqlPortoPartenza);

        // Query per porti di destinazione
        $sqlPortoDestinazione = "SELECT f.FermataId, f.FermataNome, c.Comune, c.ComuneId, o.CorsaId, MIN(o.Orario) AS Orario, o.GiorniAggiuntivi, f.TrattaId 
            FROM RT_Fermata f 
            LEFT JOIN RT_Orario o ON f.FermataId = o.FermataId 
            LEFT JOIN Comune c ON c.ComuneId = f.ComuneId 
            LEFT JOIN Provincia p ON c.provincia = p.ProvinciaId 
            LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId 
            WHERE t.LineaId = $selezionaLesperienza 
                AND o.Orario IS NOT NULL AND o.Orario <> '' 
                AND o.Stato = 1 AND o.Cancella = 0 
                AND f.Stato = 1 AND f.Cancella = 0 
                AND f.IsDropOff = 1 
                AND c.ComuneId IS NOT NULL 
                AND f.WebSelling = 1 
            GROUP BY c.ComuneId 
            ORDER BY c.Comune ASC";
        $arrPortoDestinazione = $db->fetch_array($sqlPortoDestinazione);
    } else {
        // Se l'esperienza è 14, non ci sono porti di partenza
        $Corsa = new Corsa();
        $Corsa->conn = $db;
        $arrPortoPartenza = $Corsa->getAllFermatePartenza(true);
        $arrPortoDestinazione = $Corsa->getAllFermateArrivo(true);
    }
    //creo le option per i porti di partenza
    foreach ($arrPortoPartenza as &$item) {
        if (isset($item['Comune'])) {
            $item['Comune'] = rimuoviParentesiComune($item['Comune']);
        }
    }
    //creo le option per i porti di destinazione
    foreach ($arrPortoDestinazione as &$item) {
        if (isset($item['Comune'])) {
            $item['Comune'] = rimuoviParentesiComune($item['Comune']);
        }
    }

    // Verifica se non ci sono porti disponibili
    $noPorti = false;
    if (count($arrPortoPartenza) == 0 || count($arrPortoDestinazione) == 0) {
        $noPorti = true;
    }

    // Imposta tipo viaggio in base all'esperienza selezionata
    if ($selezionaLesperienza == 1) {
        $_SESSION['tipo_viaggio'] = 2;
    } else {
        $_SESSION['tipo_viaggio'] = 1;
    }

    // Se c'è un solo porto per andata e ritorno e tour di gruppo, seleziona automaticamente e vai avanti
    if (count($arrPortoPartenza) == 1 && count($arrPortoDestinazione) == 1 && $tipoTour == 0) {
        $_SESSION['porto_partenza'] = $arrPortoPartenza[0]['ComuneId'];
        $_SESSION['porto_destinazione'] = $arrPortoDestinazione[0]['ComuneId'];
        $_SESSION['Skip3Step'] = 2;
        $urlStepNext = "./ticket-prices.php";
        header("Location: $urlStepNext");
    } else {
        $_SESSION['Skip3Step'] = 0;
    }

    // Se tour privato, crea select unica per partenza/destinazione
    $selectPortoAR = array();
    foreach ($arrPortoPartenza as $portoPar) {
        foreach ($arrPortoDestinazione as $portoDes) {
            if (trim(strtoupper($portoPar['Comune'])) == trim(strtoupper($portoDes['Comune']))) {
                $selectPortoAR[$portoPar['ComuneId'].'_'.$portoDes['ComuneId']] = $portoPar['Comune'];
            }
        }
    }
    ?>

    <body class="main-bg" id="nuova-prenotazione">
        <div id="top-menu">
            <a href="/protected/modules/app/">
                <img src="./img/logo.png" class="logo" />
            </a>
            <button class="btn btn-outline" id="logout">
                <span class="nowhitespace">LOG OUT</span>
                <svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>

        <div class="main-container">
            <div class="content">
                <div class="info-bar">
                    <button class="btn btn-rounded btn-primary" id="back">
                        <svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <div class="info">
                        <?php if (isset($_POST['PrenotazioneId']) || isset($_SESSION['PrenotazioneId'])) { ?>
                            <span>Modifica Ticket</span>
                        <?php } else { ?>
                            <span>Nuovo Ticket</span>
                        <?php } ?>
                                                <form id="change-date-form" method="post" style="display:inline;">
                                                    <input type="hidden" name="DataPartenza" id="DataPartenza_hidden" value="<?= isset($_SESSION['ticket_date']) ? date('d/m/Y', strtotime($_SESSION['ticket_date'])) : date('d/m/Y') ?>">
                                                    <span class="date" id="date-display" style="cursor:pointer; text-decoration:underline;">
                                                        <?= date('M d, Y', strtotime($_SESSION['ticket_date'])) ?>
                                                    </span>
                                                </form>
                    </div>
                </div>
                <?php if ($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
                    <!-- Messaggio se la data del biglietto è già trascorsa -->
                    <div class="form-container" style="padding:20px 0px;">
                        <div class="available-item" style="text-align:center;">
                            Non &egrave; possibile modificare il seguente biglietto. La data del tour del biglietto &egrave; gi&agrave; trascorsa.<br>
                            Torna alla <a style="display:contents" href="./index.php">home</a> per altre operazioni.
                        </div>
                    </div>
                <?php } else if ($noPorti) { ?>
                    <!-- Messaggio se non ci sono porti disponibili -->
                    <div class="form-container" style="padding:20px 0px;">
                        <div class="available-item" style="text-align:center;">
                            Non ci sono porti da selezionare per questo tour. Scegli un'altra esperienza tra le altre disponbili.<br>
                            Torna allo <a style="display:contents" href="/prenota/1.php">step precedente</a> o alla <a style="display:contents" href="/index.php">home</a> per selezionare una nuova data.
                        </div>
                    </div>
                <?php } else { ?>
                    <!-- Form di selezione porti -->
                    <div class="form-container">
                        <div class="card-cta">
                            <p>Compila i campi e conferma la prenotazione!</p>
                        </div>
                        <form method="post" id="ticket_form">
                            <div class="form-group" style="display: flow-root;">
                                <input type="checkbox" id="coincide_checkbox" name="coincide_checkbox" checked style="width:22px; height:22px; margin:0;">
                                <label for="coincide_checkbox" style="margin-bottom:0; margin-left:6px; white-space:nowrap;">Porto di partenza e destinazione coincidono</label>
                            </div>
                            <div class="form-group" id="porto_partenza_destinazione_group">
                                <label for="porto_partenza_destinazione">Porto di partenza e destinazione</label>
                                <select name="porto_partenza_destinazione" id="porto_partenza_destinazione">
                                    <?php
                                    if (count($selectPortoAR) > 0) {
                                        foreach ($selectPortoAR as $id => $tempPorto) {
                                    ?>
                                            <option value="<?= $id ?>" <?= (isset($_SESSION['porto_partenza']) && $_SESSION['porto_partenza']."_".$_SESSION['porto_destinazione'] == $id) ? 'selected' : '' ?>><?= $tempPorto ?> </option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group" id="porto_partenza_group" style="display:none;">
                                <label for="porto_partenza">Porto di partenza </label>
                                <select name="porto_partenza" id="porto_partenza">
                                    <?php
                                    if (count($arrPortoPartenza) > 0) {
                                        foreach ($arrPortoPartenza as $list) {
                                    ?>
                                            <option value="<?= $list['ComuneId'] ?>" <?= (isset($_SESSION['porto_partenza']) && $_SESSION['porto_partenza'] == $list['ComuneId']) ? 'selected' : '' ?>><?= $list['Comune'] ?> </option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group" id="porto_destinazione_group" style="display:none;">
                                <label for="porto_destinazione">Porto di destinazione </label>
                                <select name="porto_destinazione" id="porto_destinazione">
                                    <?php
                                    if (count($arrPortoDestinazione) > 0) {
                                        foreach ($arrPortoDestinazione as $list) {
                                    ?>
                                            <option value="<?= $list['ComuneId'] ?>" <?= (isset($_SESSION['porto_destinazione']) && $_SESSION['porto_destinazione'] == $list['ComuneId']) ? 'selected' : '' ?>><?= $list['Comune'] ?> </option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if ($tipoTour == 0) : ?>
                                <!-- Tipo viaggio (nascosto, sempre andata/ritorno per tour di gruppo) -->
                                <input type="hidden" name="tipo_viaggio" id="tipo_viaggio" value="2">
                            <?php else: ?>
                                <input type="hidden" name="tipo_viaggio" id="tipo_viaggio" value="1">
                            <?php endif; ?>
                            <div class="px-5">
                                <button type="submit" class="btn btn-primary w-full btn-big mt-5 rounded-pill" id="continua">
                                    CONTINUA
                                </button>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php include_once("app.php"); ?>
        <script type="text/javascript">
            $(document).ready(function() {
                // Datepicker per cambio data
                $('#date-display').click(function () {
                    if ($('#datepicker-temp').length === 0) {
                        var offset = $(this).offset();
                        var $input = $('<input type="text" id="datepicker-temp" style="position:absolute;z-index:9999;width:120px;">');
                        $input.val($('#DataPartenza_hidden').val());
                        $('body').append($input);
                        $input.css({ top: offset.top + $(this).height() + 5, left: offset.left });
                        $input.datepicker({
                            dateFormat: 'dd/mm/yy',
                            defaultDate: $input.val(),
                            onClose: function (dateText) {
                                var oldVal = $('#DataPartenza_hidden').val();
                                if (dateText && dateText !== oldVal) {
                                    // Conversione da dd/mm/yyyy a yyyy-mm-dd
                                    var parts = dateText.split('/');
                                    if(parts.length === 3) {
                                        var isoDate = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
                                        $('#DataPartenza_hidden').val(isoDate);
                                    } else {
                                        $('#DataPartenza_hidden').val(dateText);
                                    }
                                    $('#change-date-form').submit();
                                }
                                $input.remove();
                            }
                        }).datepicker('show');
                    }
                });
                // Logout handler
                $('#logout').click(function() {
                    var formData = {
                        action: "logoutBrowser",
                    };
                    $.ajax({
                        url: '<?php echo Config::$UrlMobile; ?>',
                        type: "POST",
                        data: formData,
                        dataType: 'json',
                        success: function(responce) {
                            window.location = "./login.php";
                        }
                    });
                });

                // Pulsante indietro
                $('#back').click(function() {
                    <?php if ($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
                        window.location.href = "./index.php";
                    <?php } else { ?>
                        window.location = "./choose-seats.php";
                    <?php } ?>
                });

                // Submit form via AJAX
                $("#ticket_form").submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo $_SERVER['REQUEST_URI']; ?>",
                        data: $('#ticket_form').serialize(),
                        success: function(response) {
                            window.location = "./ticket-prices.php";
                        }
                    });
                });

                // Gestione checkbox coincide
                function togglePortSelects() {
                    if ($('#coincide_checkbox').is(':checked')) {
                        $('#porto_partenza_destinazione_group').show();
                        $('#porto_partenza_group').hide();
                        $('#porto_destinazione_group').hide();
                    } else {
                        $('#porto_partenza_destinazione_group').hide();
                        $('#porto_partenza_group').show();
                        $('#porto_destinazione_group').show();
                    }
                }
                $('#coincide_checkbox').change(function() {
                    togglePortSelects();
                });
                togglePortSelects();
                // Gestione select unica sempre
                $('#porto_partenza_destinazione').change(function() {
                    var porti = $('#porto_partenza_destinazione').val().split("_");
                    $('#porto_partenza').val(porti[0]);
                    $('#porto_destinazione').val(porti[1]);
                });
            });
        </script>
    </body>
    </html>
    <?php
}

// Gestione accesso: se l'autista è loggato, mostra la pagina, altrimenti redirect login
if (is_array($autista)) {
    $db = new Database();
    $db->connect();
    $user = new Operatore();
    $user->OperatoreId = 1;
    $user->OdcId = 1;
    $user->IsAdmin = 1;
    $user->GestoreId = 1;
    $user->conn = $db;
    $permessi = $user->get_permessi_modulo($ModuloId);

    $do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';
    switch ($do) {
        default:
            nuovaTicket();
            break;
    }
} else {
    // Redirect se non loggato
    header('Location: ./login.php');
}

// Funzione per rimuovere testo tra parentesi tonde dal nome del comune
function rimuoviParentesiComune($string) {
    // Rimuove qualsiasi testo tra parentesi (con spazi opzionali)
    return preg_replace('/\s*\(.*?\)\s*/i', '', $string);
}
?>
