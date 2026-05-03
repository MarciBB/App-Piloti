<?php
// Includi file e classi principali
$basepath = $_SERVER['DOCUMENT_ROOT'];
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

// Controllo login operatore
if (!isset($_SESSION['autista'])) {
    header('Location: ./login.php');
}
$autista = $_SESSION['autista'];
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

// Funzione principale per la gestione della pagina tour
function gestioneOttimizzata()
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <!-- Meta e risorse -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Bertoldi Boats - App Operatore</title>
        <link rel="manifest" href="./manifest.json">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="/css/style.css?v4" />
        <link rel="stylesheet" type="text/css" href="./css/app.css" />
        <link rel="stylesheet" type="text/css" href="./css/style.css" />
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
        <script>
            // Inizializzazione UI e drag&drop
            $(function () {
                $("#catalog").accordion({
                    autoHeight: false,
                    navigation: true
                });
                $("#catalog tr").draggable({
                    appendTo: "body"
                });
                $("#catalog1").accordion({
                    autoHeight: false,
                    navigation: true
                });
                $(".accordion_bus").accordion({
                    autoHeight: false,
                    navigation: true,
                    collapsible: true,
                    header: '.accordion_header'
                });
                $("#catalog1 tr").draggable({
                    appendTo: "body"
                });
            });
        </script>
        <style>
            #accordion_content_custom .ui-accordion .ui-accordion-content {
                padding: 1em 1em !important;
            }
        </style>
        <!--[if lte IE 8]>
        <link rel="stylesheet" href="/css/styleIE.css" type="text/css" />
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
        <link rel="stylesheet" href="/css/home.css" type="text/css" />
        <link rel="stylesheet" href="/css/home_2.css" type="text/css" />
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    </head>
    <body class="main-bg" id="home-applicativo">
        <!-- Fine Live Search -->
        <?php
        // Controllo di sicurezza opzionale
        $codiceControllo = 'gC_6XHrZvC9$avW!';
        /*
        if (!isset($_POST['CorsaId'])) {
            $code = $_REQUEST['code'];
            if ($code != $codiceControllo) {
                header('Location: ./login.php');
                die("servizio non disponibile");
            }
        }
        */
        global $user, $HtmlCommon, $dizionario, $autista;
        include_once("previaggio_validator.php");
        $db = new Database();
        $db->connect();
        $user = new Operatore();
        $user->OperatoreId = 1;
        $user->OdcId = 1;
        $user->IsAdmin = 1;
        $user->GestoreId = 1;
        // Recupero parametri GET
        $DataPartenza = date('Y-m-d');
        $datacorrente = Date('d/m/Y');
        $CorsaId = 0;
        if (isset($_GET['CorsaId']))
            $CorsaId = $_GET['CorsaId'];
        if (isset($_GET['DataPartenza'])) {
            $dt = new DT();
            $post_dal = $_GET['DataPartenza'];
            $DataPartenza = $_GET['DataPartenza'];
            $dateTime = new DateTime($DataPartenza);
            $datacorrente = $dateTime->format('d/m/Y');
        }
        // Recupero corsa selezionata
        $sqlCorsa = "select 
            c.`CorsaId` AS `CorsaId`,
            appcal.`AppCalendarioData` AS `AppCalendarioData`,
            date_format(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`,
            c.`CorsaNome` AS `CorsaNome`,
            c.`LineaId` AS `LineaId`,
            `RT_Linea`.`LineaNome` AS `LineaNome`,
            c.`OrarioPartenza` AS `OrarioPartenza`
        from
            `RT_Corsa` c
            join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
            join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
            join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
            join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
        where appcal.`AppCalendarioData` = '$DataPartenza' and c.`CorsaId` = $CorsaId";
        $corsa = $db->query_first($sqlCorsa);
        $dateTime = new DateTime($corsa['OrarioPartenza']);
        $formattedTime = $dateTime->format('H:i');

        // Recupero lista prenotazioni per la corsa selezionata
        // Query raggruppata per PrenotazioneId con conteggio dettagli
        $sqlPrenotazioni = "SELECT
            (CASE
                WHEN (MAX(apps.PrenotazioneStato) = _latin1'Biglietto emesso') THEN _utf8'BE'
                ELSE _utf8'C'
            END) AS StatoPrenotazione,
            MAX(g.RagioneSociale) AS RagioneSociale,
            (CASE
                WHEN (MAX(rdc.Codice) IS NOT NULL)
                    THEN CONCAT(CONVERT(MAX(rdc.Codice) USING utf8), _utf8' / ', CAST(MAX(rdc.Anno) AS CHAR(5) CHARACTER SET utf8))
                WHEN (MAX(rdc.Codice) IS NULL AND MAX(apps.PrenotazioneStato) = _latin1'Biglietto emesso')
                    THEN (CONVERT((SELECT CONCAT(Codice,_utf8' / ',Anno) FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = p.PrenotazioneId AND TipoTitolo = 'E' LIMIT 1) USING utf8))
                ELSE CONCAT(CONVERT(MAX(rdc.CodicePrenotazione) USING utf8), _utf8' / ', CAST(MAX(rdc.PrenotazioneNumero) AS CHAR(10) CHARACTER SET utf8))
            END) AS NumeroBiglietto,
            (CASE
                WHEN (MAX(tp.OccupaPosto) = 1) THEN CONCAT(MAX(p.ClienteNome), _latin1' (Tipo Biglietto: ', MAX(pd.Nome), _latin1')')
                ELSE CONCAT('Servizio: ', _latin1' ', MAX(pd.Nome))
            END) AS Cliente,
            CONCAT(_latin1'+', MAX(p.ClienteCellularePrefisso), _latin1' ', MAX(p.ClienteCellulare)) AS ClienteCellulare,
            MIN(pd.OrarioPartenza) AS OrarioPartenza,
            MAX(pd.OrarioArrivo) AS OrarioArrivo,
            MAX(pd.DataArrivo) AS DataArrivo,
            MAX(pd.ComunePartenza) AS ComunePartenza,
            MAX(pd.ComuneArrivo) AS ComuneArrivo,
            (CASE
                WHEN (MAX(pd.TipoViaggio) = _latin1'Corsa Semplice') THEN _utf8'CS'
                ELSE _utf8'A/R'
            END) AS TipoViaggio,
            (CASE
                WHEN (MAX(pd.TipoViaggio) = _latin1'Corsa Semplice') THEN ROUND(SUM(pd.Importo), 2)
                ELSE ROUND((MAX(pb.PrezzoPax) + ((MAX(pb.AumentoPax) - MAX(pb.RiduzionePax)) / MAX(pb.NumeroPax))), 2)
            END) AS Importo,
            MAX(pd.CorsaId) AS CorsaId,
            MAX(pd.DataPartenza) AS DataPartenza,
            MAX(pd.DataInizioItinerario) AS DataInizioItinerario,
            p.PrenotazioneId AS PrenotazioneId,
            MAX(p.DataIns) AS DataIns,
            COUNT(pd.PrenotazioneDettaglioId) AS NumeroDettagli,
			pc.ValidatoMolo
        FROM
            (((((((RT_PrenotazioneDettaglio pd
            LEFT JOIN RT_Prenotazione p ON (pd.PrenotazioneId = p.PrenotazioneId))
            LEFT JOIN RT_AppPrenotazioneStato apps ON (p.PrenotazioneStato = apps.PrenotazioneStatoId))
            LEFT JOIN Operatore o ON (p.OpeIns = o.OperatoreId))
            LEFT JOIN Gestore g ON (o.GestoreId = g.GestoreId))
            LEFT JOIN RT_PrenotazioneDettaglioCompleto rdc ON ((p.PrenotazioneId = rdc.PrenotazioneId)
                AND (rdc.PrenotazioneId = pd.PrenotazioneId)
                AND (rdc.PrenotazioneNumero = pd.PrenotazioneNumero)
                AND (rdc.DataPartenza = pd.DataPartenza)
                AND (rdc.CorsaId = pd.CorsaId)))
            LEFT JOIN RT_PrenotazioneBiglietto pb ON ((pb.PrenotazioneId = p.PrenotazioneId)
                AND (pb.TipologiaBiglietto = pd.TipologiaBiglietto)))
            LEFT JOIN RT_TipologiaBiglietto tp ON (tp.TipologiaBigliettoId = pb.TipologiaBigliettoId))
			LEFT JOIN RT_PrenotazionePercorso pc ON (pc.PrenotazioneId = p.PrenotazioneId)
        WHERE
            (((p.PrenotazioneStato = 3)
                OR (p.PrenotazioneStato = 1)
                OR (p.PrenotazioneStato = 2))
                AND (pd.Escludi <> 1)
                AND (pd.Rimborso <> 1))
                AND tp.OccupaPosto = 1
                AND (rdc.Codice NOT LIKE 'E-%' OR rdc.Codice IS NULL)
                AND pd.CorsaId = $CorsaId
                AND pd.DataPartenza = '$DataPartenza'
				AND pc.CorsaId = $CorsaId
        GROUP BY
            p.PrenotazioneId
        ORDER BY
            NumeroBiglietto";
        $arr_scontrini = $db->fetch_array($sqlPrenotazioni);
        ?>
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
                <div style="margin-bottom:10px;">
                    <b><?= $corsa['LineaNome'] ?>, <?= $corsa['CorsaNome'] ?></b>
                    <br>
                    <?= $corsa['DataPartenzaFormattata'] ?> ore <?= $formattedTime ?>
                </div>
                <?php
                // Calcolo totali passeggeri e validati
                $totalePasseggeri = 0;
                $totaleValidati = 0;
                if (is_array($arr_scontrini) && count($arr_scontrini) > 0) {
                    foreach ($arr_scontrini as $t) {
                        $totalePasseggeri += (int)$t['NumeroDettagli'];
                        if (isset($t['ValidatoMolo']) && $t['ValidatoMolo'] == 1) {
                            $totaleValidati += (int)$t['NumeroDettagli'];
                        }
                    }
                }
                ?>

                <form method="post" action="index.php" id="search">
                    <input type="hidden" name="DataPartenza" id="DataPartenza" value="<?= $DataPartenza ?>">
                    <button type="submit" class="btn btn-big btn-primary" style="width: 100%;">TORNA INDIETRO</button>
                </form>
                <div class="actions"></div>
                <div class="ticket-list">
                    <div class="filters" style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center;">
                            <span>Passeggeri</span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span style="margin-right: 15px;">Validati: <?= $totaleValidati ?> / <?= $totalePasseggeri ?></span>
                            <button>
                                <i class="fa fa-chevron-up showTicket upArrow" aria-hidden="true"></i>
                                <i class="fa fa-chevron-down showTicket downArrow" aria-hidden="true" style="display:none;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="allTicket">
                        <?php if (count($arr_scontrini) > 0) {
                            foreach ($arr_scontrini as $t) {
                                $dateTime = new DateTime($t['DataIns']);
                                $formattedDate = $dateTime->format('d/m/Y');
                                $formattedTime = $dateTime->format('H:i');
                                // Stato validazione molo
                                $validatoIcon = '';
                                if (isset($t['ValidatoMolo']) && $t['ValidatoMolo'] == 1) {
                                    $validatoIcon = '<span style="color: #218838; font-weight: bold; font-size: 1.2em;">&#10003; Validato</span>';
                                } else {
                                    $validatoIcon = '<span style="color: #dc3545; font-weight: bold; font-size: 1.2em;">&#10007; Da validare</span>';
                                }
                                ?>
                                <!-- Card biglietto -->
                                <a class="link_no_underline" href='show-ticket.php?PrenotazioneId=<?= $t['PrenotazioneId'] ?>&CorsaId=<?= $CorsaId ?>&DataPartenza=<?= $DataPartenza ?>'>
                                    <div class="ticket">
                                        <div class="info">
                                            <span class="title">Num. Biglietto <?= $t['NumeroBiglietto'] ?> 
                                                <br>
                                                <?= $t['Cliente'] ?>
                                                <br>
                                                N. Passeggeri: <?= $t['NumeroDettagli'] ?>
                                            </span>
											<?= $validatoIcon ?><br>
                                            <span class="date"> - <?= $formattedDate . ' ore ' . $formattedTime ?></span>
                                        </div>
                                        <a href='show-ticket.php?PrenotazioneId=<?= $t['PrenotazioneId'] ?>&CorsaId=<?= $CorsaId ?>&DataPartenza=<?= $DataPartenza ?>'><i class="fa fa-eye buttonList" aria-hidden="true"></i></a>
                                    </div>
                                </a>
                            <?php }
                        } else { ?>
                            <div class="ticket">
                                <div class="info">
                                    <i>Nessuno scontrino emesso</i>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once("app.php"); ?>
        <script type="text/javascript">
            $(document).ready(function () {
                // Logout handler
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
                // Espandi/comprimi lista biglietti
                $('.showTicket').click(function () {
                    var display = $('.allTicket').css('display');
                    $('.allTicket').slideToggle();
                    if (display != 'none') {
                        $('.showTicket.upArrow').hide();
                        $('.showTicket.downArrow').show();
                    } else {
                        $('.showTicket.upArrow').show();
                        $('.showTicket.downArrow').hide();
                    }
                });
            });
        </script>
    </body>
    </html>
    <?php
    $db->close();
}

// Avvio della pagina
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
            gestioneOttimizzata();
            break;
    }
} else {
    // Se l'utente non è loggato
    header('Location: ./login.php');
}
?>