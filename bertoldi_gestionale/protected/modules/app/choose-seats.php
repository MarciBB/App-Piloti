<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
// Includi il file principale di configurazione dell'applicazione
include_once($basepath . "/main_include_app.php");

$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

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

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
$ModuloId = 1;
global $db, $autista, $user;
$db = new Database();
$db->connect();

//controllo login
if (!isset($_SESSION['autista'])) {
    header('Location: ./login.php');
}
$autista = $_SESSION['autista'];
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

function nuovaTicket()
{
    if (!isset($_SESSION['tipo_tour'])) {
        // Effettua il redirect alla pagina index.php
        header("Location: ./index.php");
        exit; // Termina lo script per evitare l'esecuzione di ulteriori istruzioni
    }
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html lang="en" xmlns="http://www.w3.org/1999/xhtml">

    <head>
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
        <link rel="stylesheet" href="./css/style.css" />

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

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
        <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
        <script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>

        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

        <title>Bertoldi Boats - Nuovo Ticket</title>
    </head>

    <?php

    $codiceControllo = 'gC_6XHrZvC9$avW!';
    global $user, $HtmlCommon, $dizionario, $autista;
    // include_once("previaggio_validator.php");
    $db = new Database();
    $db->connect();

    $user = new Operatore();
    $user->OperatoreId = 1;
    $user->OdcId = 1;
    $user->IsAdmin = 1;
    $user->GestoreId = 1;

    $DataPartenza = date('Y-m-d');
    $datacorrente = Date('d/m/Y');
    $LineaId = 0; //$arr_corse[0]['CorsaId'];
    if (isset($_SESSION['seleziona_lesperienza'])) {
        $LineaId = $_SESSION['seleziona_lesperienza'];
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['DataPartenza'])) {
        $_SESSION['ticket_date'] = $_POST['DataPartenza'];
    }
    if (isset($_SESSION['ticket_date'])) {
        $dt = new DT();
        $DataPartenza = $_SESSION['ticket_date'];
        $dateTime = new DateTime($DataPartenza);
        $datacorrente = $dateTime->format('d/m/Y');
    }
    $tipoTour = $_SESSION['tipo_tour'];
    $selezionaLesperienza = $_SESSION['seleziona_lesperienza'];

    if (isset($_SESSION['PrenotazioneId']) && !isset($_SESSION['load_passeggeri'])) {
        $sql = "SELECT * 
                FROM RT_PrenotazioneBiglietto b
                LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
                WHERE PrenotazioneId = " . $_SESSION['PrenotazioneId'] . " AND t.OccupaPosto = 1";
        $resultRows = $db->fetch_array($sql);
        foreach ($resultRows as $value) {
            $_SESSION['BigliettoTipologiaPax'][$value['TipologiaBigliettoId']] = $value['NumeroPax'];
            $_SESSION['TipoBigliettoId'][$value['TipologiaBigliettoId']] = $value['TipologiaBiglietto'];
            $_SESSION['passangerId'][$value['TipologiaBigliettoId']] = $value['TipologiaBigliettoId'];
        }
        $_SESSION['load_passeggeri'] = 1;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['DataPartenza'])) {
        unset($_SESSION['BigliettoTipologiaPax']);
        unset($_SESSION['TipoBigliettoId']);
        unset($_SESSION['passangerId']);
        foreach ($_POST['BigliettoTipologiaPax'] as $key => $value) {
            if ($value > 0) {
                list($tipologiaBigliettoId, $tipologiaBiglietto) = explode('_', $key);
                $_SESSION['BigliettoTipologiaPax'][$tipologiaBigliettoId] = $value;
                $_SESSION['TipoBigliettoId'][$tipologiaBigliettoId] = $tipologiaBiglietto;
                $_SESSION['passangerId'][$tipologiaBigliettoId] = $tipologiaBigliettoId;
            } else {
                list($tipologiaBigliettoId, $tipologiaBiglietto) = explode('_', $key);
                unset($_SESSION['BigliettoTipologiaPax'][$tipologiaBigliettoId]);
                unset($_SESSION['TipoBigliettoId'][$tipologiaBigliettoId]);
                unset($_SESSION['passangerId'][$tipologiaBigliettoId]);
            }
        }
    }

    if ($LineaId != 14) {
        // Query standard per tutte le linee tranne la 14
        $sqlCorse = "SELECT * 
            FROM RT_TipologiaBiglietto t 
            LEFT JOIN RT_ValiditaBigliettoDettaglio vb ON t.TipologiaBigliettoId = vb.BigliettoId
            LEFT JOIN RT_ValiditaBiglietto v ON v.ValiditaBigliettoId = vb.ValiditaBigliettoId
            LEFT JOIN RT_Corsa c ON c.CorsaId = v.CorsaId
            WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = $tipoTour AND t.OccupaPosto = 1 
            AND c.LineaId = $LineaId AND v.Dal <= '$DataPartenza' AND v.Al >= '$DataPartenza'
            AND TipologiaBigliettoId <> 11
            GROUP BY t.TipologiaBigliettoId
            ORDER BY t.TipologiaBigliettoPeso";
    } else {
        // Query speciale per la prenotazione libera (LineaId 14)
        $sqlCorse = "SELECT * 
            FROM RT_TipologiaBiglietto 
            WHERE TipoTour = 1 
            AND Stato = 1 
            AND Cancella = 0 
            AND OccupaPosto = 1
            AND TipologiaBiglietto NOT LIKE '%exclusive%'
            AND TipologiaBiglietto NOT LIKE '%coppia%'
            ORDER BY TipologiaBigliettoPeso";
    }

    $arr_tour = $db->fetch_array($sqlCorse);

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
                    <div class="form-container" style="padding:20px 0px;">
                        <div class="available-item" style="text-align:center;">
                            Non &egrave; possibile modificare il seguente biglietto. La data del tour del biglietto &egrave; gi&agrave; trascorsa.<br>
                            Torna alla <a style="display:contents" href="./index.php">home</a> per altre operazioni.
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="form-container">
                        <div class="card-cta">
                            <p>Compila i campi e conferma la prenotazione!</p>
                        </div>
                        <?php if (count($arr_tour) > 0) { ?>
                            <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="position-relative" id="ticket_form">
                                <div class="form-group form-icon">
                                    <label for="data">Passeggeri</label>
                                    <?php if ($tipoTour == 1) { ?>
                                        <select name="tipo_biglietto" id="tipo_biglietto">
                                            <option value="">-- Seleziona --</option>
                                            <?php foreach ($arr_tour as $k => $list) { ?>
                                                <option data-description="<?= $list['TipologiaBigliettoDescr'] ?>" value="BigliettoTipologiaPax[<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>]" <?= isset($_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']]) ? 'selected' : 0; ?>><?= $list['TipologiaBiglietto'] ?> </option>
                                            <?php } ?>
                                        </select>
                                        <div id="note_biglietti" style="margin-top:10px;"></div>
                                    <?php } ?>
                                    <?php foreach ($arr_tour as $k => $list) { ?>
                                        <div class="control-group border-bottom" <?php if ($tipoTour == 1) {
                                                echo "style='display:none'";
                                            } ?>>
                                            <label for="tipo_tour d-flex align-items-center" style="max-width: 90%;"><span class="title" <?= (isset($list['TipologiaBigliettoDescr'])) ? "style='margin-bottom: 0px !important;'" : "" ?>>
												<?= $list['TipologiaBiglietto'] ?></span>
												<?php if (!empty($list['TipologiaBigliettoDescr'])): ?>
													<div class="note_tour" style="margin-top:10px; margin-bottom: 32px;"><?= htmlspecialchars($list['TipologiaBigliettoDescr']) ?></div>
												<?php endif; ?>
											</label>
                                            
                                            <div class="number-input">
                                                <button type="button" class="btn btn-secondary btn-decrement" aria-label="Decrement">-</button>
                                                <input type="number" min="0" <?= ($tipoTour == 1) ? 'max="1"' : '' ?> name="BigliettoTipologiaPax[<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>]" value="<?= isset($_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']]) ? $_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']] : 0; ?>" id="ticket-<?= $k ?>" class="number-ticket">
                                                <button type="button" class="btn btn-secondary btn-increment" aria-label="Increment">+</button>
                                            </div>
                                            <input type="hidden" name="TipoBigliettoId<?= $k ?>" id="TipoBigliettoId<?= $k ?>" value="<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>">
                                            <input type="hidden" name="passangerId<?= $k ?>" id="passangerId<?= $k ?>" value="<?= $list['TipologiaBigliettoId'] ?>">
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="px-5">
                                    <button class="btn btn-primary w-full btn-big mt-5 rounded-pill" id="continua" type="submit">
                                        CONTINUA
                                    </button>
                                </div>
                            </form>
                        <?php } else { ?>
                            <div class="available-item" style="text-align:center;">
                                Non ci sono tour per l'esperieza selezionata.
                                Torna allo <a style="display:contents" href="/prenota/3.php">step precedente</a> o alla <a style="display:contents" href="/index.php">home</a> per selezionare una nuova data.
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <?php include_once("app.php"); ?>
        <script type="text/javascript">
            $(document).ready(function () {
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

                // ...existing code...
                $('.btn-decrement').click(function () {
                    var input = $(this).next('.number-ticket');
                    var currentValue = parseInt(input.val());
                    if (currentValue > parseInt(input.attr('min'))) {
                        input.val(currentValue - 1);
                        input.change();
                    }
                });

                $('.btn-increment').click(function () {
                    var input = $(this).prev('.number-ticket');
                    var currentValue = parseInt(input.val());
                    if (input.attr('max') === undefined || currentValue < parseInt(input.attr('max'))) {
                        input.val(currentValue + 1);
                        input.change();
                    }
                });

                $('input[type="number"]').on('keydown', function (event) {
                    // Ottieni il valore attuale dell'input
                    var valoreAttuale = $(this).val();

                    // Controlla se il tasto premuto è un numero o un tasto speciale come backspace o delete
                    if ((event.keyCode >= 48 && event.keyCode <= 57) || // numeri sopra la tastiera
                        (event.keyCode >= 96 && event.keyCode <= 105) || // numeri sul tastierino numerico
                        event.keyCode === 8 || // backspace
                        event.keyCode === 9 || // tab
                        event.keyCode === 13 || // invio
                        event.keyCode === 46 || // delete
                        event.keyCode === 37 || // freccia sinistra
                        event.keyCode === 39) { // freccia destra
                        // Se il tasto premuto è valido, lascia che l'evento si verifichi normalmente
                        return true;
                    } else {
                        // Se il tasto premuto non è valido, previeni l'evento predefinito e non aggiornare il valore dell'input
                        event.preventDefault();
                        return false;
                    }
                });
                $('#logout').click(function () {
                    var formData = {
                        action: "logoutBrowser",
                    };

                    $.ajax({
                        url: '<?php echo Config::$UrlMobile; ?>',
                        type: "POST",
                        data: formData,
                        dataType: 'json',
                        success: function (responce) {
                            window.location = "./login.php";
                        },
                        error: function (xhr, ajaxOptions, thrownError) {

                        },
                    });

                });

                $('#back').click(function () {
                    <?php if ($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
                        window.location.href = "./index.php";
                    <?php } else { ?>
                        window.location = "./new-ticket.php";
                    <?php } ?>
                });

                $('.number-ticket').change(function () {
                    if ($(this).val() < 0) {
                        $(this).val(0);
                    }

                    <?php if ($tipoTour == 1) { ?>
                        if ($(this).val() > 1) {
                            $(this).val(1);
                        }
                        if ($(this).val() == 1) {
                            // Imposta il valore degli altri input number a 0
                            $('input[type="number"]').not(this).val(0);
                        }
                    <?php } ?>

                });

                $("#ticket_form").submit(function (e) {
                    var inputs = document.querySelectorAll('input[type="number"]');
                    var atLeastOneFilled = Array.from(inputs).some(function (input) {
                        return input.value !== '0';
                    });
                    e.preventDefault();
                    if (!atLeastOneFilled) {
                        alert('Inserisci almeno un passeggero.');
                        e.preventDefault(); // Prevent form submission
                    } else {
                        $.ajax({
                            type: 'POST',
                            url: "<?php echo $_SERVER['REQUEST_URI']; ?>", // Change to the actual filename of this page
                            data: $('#ticket_form').serialize(),
                            success: function (response) {
                                window.location = "./port-choice.php";
                            }
                        });
                    }
                });

                $("#tipo_biglietto").change(function () {
                    var value = $("#tipo_biglietto").val();
                    $('.number-ticket').val(0);
                    $('input[name="' + value + '"]').val(1);
                    if (value != '') {
                        var description = $(this).find('option:selected').data('description');
                        $('#note_biglietti').html('<u>Note:</u> ' + description);
                    } else {
                        $('#note_biglietti').html('');
                    }
                });

            });
        </script>
    </body>

    </html>
    <?php
}

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

    if (!isset($_REQUEST['do'])) {
        $do = '';
    } else {
        $do = $_REQUEST['do'];
    }

    switch ($do) {
        default:
            nuovaTicket();
            break;
    }
} else {
    // se l'utente non è loggato
    header('Location: ./login.php');
}
?>
