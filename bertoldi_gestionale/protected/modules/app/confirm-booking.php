<?php
// Imposta il percorso base del documento
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include_app.php");

// Inizializza la configurazione
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

// Abilita la visualizzazione degli errori per debugging
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$ModuloId = 1;
global $db, $autista, $user;
$db = new Database();
$db->connect();

// Controllo login: se non loggato, redirect a login.php
if (!isset($_SESSION['autista'])) {
    header('Location: ./login.php');
}
$autista = $_SESSION['autista'];

// Imposta cookie cross-site
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

/**
 * Funzione principale per la visualizzazione e gestione del nuovo ticket
 */
function nuovaTicket()
{
    // Se la sessione non contiene il tipo tour, redirect a index.php
    if (!isset($_SESSION['tipo_tour'])) {
        header("Location: ./index.php");
        exit;
    }
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html lang="en" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <!-- Meta e risorse CSS/JS -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Bertoldi Boats - Nuovo Ticket</title>
        <link rel="manifest" href="./manifest.json">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="./css/style.css" />
        <link rel="stylesheet" href="./css/new-ticket.css" />
        <link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css" />
        <!-- Librerie JS e CSS di terze parti -->
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
        <title>Bertoldi Boats - Nuovo Ticket</title>
    </head>
    <?php
    // Variabili di configurazione e recupero dati sessione
    $codiceControllo = 'gC_6XHrZvC9$avW!';
    global $user, $HtmlCommon, $dizionario, $autista;
    $db = new Database();
    $db->connect();

    // Simulazione utente operatore (da sostituire con dati reali in produzione)
    $user = new Operatore();
    $user->OperatoreId = 1;
    $user->OdcId = 1;
    $user->IsAdmin = 1;
    $user->GestoreId = 1;

    // Recupero dati corsa e data partenza
    $CorsaId = 0;
    if (isset($_SESSION['CorsaId'])) {
        $CorsaId = $_SESSION['CorsaId'];
    }
    $DataPartenza = date('Y-m-d');
    $datacorrente = Date('d/m/Y');
    if (isset($_SESSION['ticket_date'])) {
        $dt = new DT();
        $DataPartenza = $_SESSION['ticket_date'];
        $dateTime = new DateTime($DataPartenza);
        $datacorrente = $dateTime->format('d/m/Y');
    }

    $tipoTour = $_SESSION['tipo_tour'];
    $seleziona_lesperienza = $_SESSION['seleziona_lesperienza'];

    // Query per servizi extra disponibili
    $sqlServiziExtra = "SELECT t.*, s.Prezzo, s.Limite FROM RT_TipologiaBiglietto t LEFT JOIN RT_ListinoServizi s on s.BigliettoId = t.TipologiaBigliettoId WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = 1 AND t.OccupaPosto = 0 ORDER BY t.TipologiaBigliettoPeso";
    $arrServiziExtra = $db->fetch_array($sqlServiziExtra);

    // Imposta il metodo di pagamento nella sessione
    $_SESSION['pagamentoId'] = $_SESSION['pagamento'] == 'contanti' ? 1 : 3;

    // Recupero dati gestore
    $sql = "SELECT * FROM Gestore WHERE GestoreId = " . $_SESSION['GestoreIdRef'];
    $gestore = $db->query_first($sql);
    ?>
    <body class="main-bg modal-success-open" id="conferma-prenotazione">
        <div id="top-menu">
            <a href="/protected/modules/app/">
                <img src="./img/logo.png" class="logo" />
            </a>
            <button class="btn btn-outline">
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
                        <div class="date"><?= date('M d, Y', strtotime($_SESSION['ticket_date'])) ?></div>
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
                <?php } else { ?>
                    <!-- Form di riepilogo ticket -->
                    <form action="" class="new-ticket">
                        <div class="title">
                            <span>Azienda / Hotel che emette il biglietto</span>
                            <div class="dashes"></div>
                        </div>
                        <div class="action-title">
                            <span><?= $gestore['RagioneSociale'] ?></span>
                            <a href="/protected/modules/app/new-ticket.php" title="Modifica passeggeri"><img src="./img/edit-primary.png" alt="Modifica passeggeri"></a>
                        </div>
                        <div class="title">
                            <span>Tour</span>
                            <div class="dashes"></div>
                        </div>
                        <?php if ($_SESSION['tipo_viaggio'] == 2) { ?>
                            <div style="text-align:center;"><span><b>Andata</b></span></div>
                        <?php } ?>
                        <?php if (isset($_SESSION['prenotazione_libera']) && $_SESSION['prenotazione_libera'] == 1) { ?>
                            <div style="text-align:center;"><span><b>Prenotazione Libera</b></span></div>
                        <?php } ?>
                        <?php if (isset($_SESSION['prenotazione_libera']) && $_SESSION['prenotazione_libera'] == 1) { ?>
                            <div style="text-align:center;"><span><?= $_SESSION['BarcaLiberaNome'] ?></span></div>
                        <?php } ?>
                        <div>
                            <div class="travel-info row">
                                <div class="departure col-md-5 col-sm-12">
                                    <div class="time"><?= $_SESSION['start_time'] ?></div>
                                    <div class="location"><?= $_SESSION['porto_partenza_nome'] ?> - <?= $_SESSION['Pickup'] ?></div>
                                </div>
                                <div class="duration col-md-2 col-sm-12">
                                    <div class="time"><?= $_SESSION['time_diffrence'] ?></div>
                                    <div class="arrow-container">
                                        <div class="arrow"></div>
                                    </div>
                                </div>
                                <div class="arrival col-md-5 col-sm-12">
                                    <div class="time"><?= $_SESSION['end_time'] ?></div>
                                    <div class="location"><?= $_SESSION['porto_destinazione_nome'] ?> - <?= $_SESSION['Dropoff'] ?></div>
                                </div>
                            </div>
                        </div>
                        <?php if ($_SESSION['tipo_viaggio'] == 2) { ?>
                            <div style="text-align:center;"><span><b>Ritorno</b></span></div>
                            <div>
                                <div class="travel-info row">
                                    <div class="departure col-md-5 col-sm-12">
                                        <div class="time"><?= $_SESSION['start_timeR'] ?></div>
                                        <div class="location"><?= $_SESSION['porto_destinazione_nome'] ?> - <?= $_SESSION['PickupR'] ?></div>
                                    </div>
                                    <div class="duration col-md-2 col-sm-12">
                                        <div class="time"><?= $_SESSION['time_diffrenceR'] ?></div>
                                        <div class="arrow-container">
                                            <div class="arrow"></div>
                                        </div>
                                    </div>
                                    <div class="arrival col-md-5 col-sm-12">
                                        <div class="time"><?= $_SESSION['end_timeR'] ?></div>
                                        <div class="location"><?= $_SESSION['porto_partenza_nome'] ?> - <?= $_SESSION['DropoffR'] ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="title">
                            <span>Passeggeri</span>
                            <div class="dashes"></div>
                        </div>
                        <div>
                            <?php
                            // Ciclo su tutte le tipologie di biglietto selezionate
                            foreach ($_SESSION['TipoBigliettoId'] as $key => $value) {
                                ?>
                                <div class="action-title">
                                    <span><?= $_SESSION['BigliettoTipologiaPax'][$key] . 'x ' . $value ?></span>
                                    <?php
                                    $sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = $key";
                                    $temp = $db->query_first($sql);
                                    if ($temp['OccupaPosto'] == 1) {
                                        ?>
                                        <a href="/protected/modules/app/choose-seats.php" title="Modifica passeggeri"><img src="./img/edit-primary.png" alt="Modifica passeggeri" /></a>
                                    <?php } else { ?>
                                        <a href="/protected/modules/app/extra-services.php" title="Modifica servizi"><img src="./img/edit-primary.png" alt="Modifica servizi" /></a>
                                    <?php } ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="title">
                            <span>Contatti </span>
                            <div class="dashes"></div>
                        </div>
                        <div class="mb-32">
                            <div class="row-info">
                                <div class="label">Nominativo</div>
                                <div class="info"><?= $_SESSION['nome'] ?></div>
                            </div>
                            <div class="row-info">
                                <div class="label">Indirizzo Email</div>
                                <div class="info"><?= $_SESSION['mail'] ?></div>
                            </div>
                            <div class="row-info">
                                <div class="label">Recapito Telefonico</div>
                                <div class="info">+<?= $_SESSION['prefisso'] ?> <?= $_SESSION['tel'] ?></div>
                            </div>
                        </div>
                        <div class="title">
                            <span>Metodo di Pagamento</span>
                            <div class="dashes"></div>
                        </div>
                        <div>
                            <div class="action-title">
                                <span><?= strtoupper($_SESSION['pagamento']) ?></span>
                                <a href="/protected/modules/app/passenger-data.php"><img src="./img/edit-primary.png" alt="" /></a>
                            </div>
                        </div>
                        <div class="title">
                            <span>Invia Ricevuta Digitale (sarà inviata all'email indicata)</span>
                            <div class="dashes"></div>
                        </div>
                        <div>
                            <div class="action-title">
                                <span><?= ($_SESSION['scontrinoInvio'] == 1) ? 'SI' : 'NO' ?></span>
                                <a href="/protected/modules/app/passenger-data.php"><img src="./img/edit-primary.png" alt="" /></a>
                            </div>
                        </div>
                    </form>
                <?php } ?>
            </div>
            <!-- Modal di conferma prenotazione -->
            <div class="modal-success d-none">
                <div class="modal-success-content">
                    <div class="ticket-card">
                        <div class="modal-success-title">Prenotazione Confermata!</div>
                        <p>Invia conferma al cliente oppure stampa i dettagli del tour.</p>
                        <input type="hidden" class="corsaId">
                        <input type="hidden" class="prenotazioneId">
                        <div class="modal-success-actions" style="display: block !important;">
                            <form method="post" action="<?php echo Config::$UrlMobile; ?>" id="search" target="_blank">
                                <input type="hidden" name="prenotazioneId" id="prenotazioneId" class="prenotazioneId" value="">
                                <input type="hidden" name="corsaId" id="corsaId" class="corsaId">
                                <input type="hidden" name="dataPartenza" id="dataPartenza" value="<?= $DataPartenza ?>">
                                <input type="hidden" name="action" id="action" value="stampaCartaceoPdf">
                                <button type="submit" class="btn btn-big btn-secondary" style="width:100%;">STAMPA TICKET</button>
                            </form>
                            <button id="inviaTicketEmail" type="button" class="btn btn-big btn-secondary" style="width:100%; margin:5px 0;">
                                Invia Conferma Via Email
                            </button>
                            <a class="btn btn-big btn-secondary home btn-black" id="showTicket" style="width: 100%; margin: 0 0 5px 0;">Vedi Biglietto</a>
                            <a class="btn btn-big btn-secondary home btn-black" href="/protected/modules/app/" style="width:100%;">Home</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal invio ticket -->
            <div class="modal" id="modal-invio">
                <div class="modal-content">
                    <div class="ticket-card">
                        <div class="modal-title">Ticket Inviato!</div>
                        <p>Il ticket è stato inviato all'email del ciente in formato digitale.</p>
                        <div class="modal-actions" style="text-align:center;">
                            <button class="btn btn-big btn-black" data-dismiss="modal">
                                <span>Procedi</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Barra inferiore con prezzo e conferma -->
            <div class="sticky bottom action-bottombar">
                <div class="info">
                    <?php if (!isset($_SESSION['prenotazione'])) { ?>
                        <span>Prezzo Totale</span>
                        <div class="user">
                            <img src="./img/user-primary.png" alt="" />
                            <span><?= number_format($_SESSION['totalprice'], 2, ',', '.'); ?>€</span>
                        </div>
                    <?php } else if (isset($_SESSION['prenotazione']) && $_SESSION['totalprice'] <= $_SESSION['prenotazione']['TotalePrenotazione']) { ?>
                        <span>Prezzo Totale</span>
                        <div class="user">
                            <img src="./img/user-primary.png" alt="" />
                            <span><?= number_format(0, 2, ',', '.'); ?>€</span>
                        </div>
                        <span>Nessuna variazione di prezzo per modifica di <?= $_SESSION['prenotazione']['CodicePrenotazione'] ?></span>
                    <?php } else if (isset($_SESSION['prenotazione']) && $_SESSION['totalprice'] > $_SESSION['prenotazione']['TotalePrenotazione']) { ?>
                        <span>Prezzo Totale</span>
                        <div class="user">
                            <img src="./img/user-primary.png" alt="" />
                            <span><?= number_format($_SESSION['totalprice'], 2, ',', '.'); ?>€</span>
                        </div>
                        <span>Importo Pagato per <?= $_SESSION['prenotazione']['CodicePrenotazione'] ?></span>
                        <div class="user">
                            <img src="./img/user-primary.png" alt="" />
                            <span><?= number_format($_SESSION['prenotazione']['TotalePrenotazione'], 2, ',', '.'); ?>€</span>
                        </div>
                        <span>Residuo</span>
                        <div class="user">
                            <img src="./img/user-primary.png" alt="" />
                            <span><?= number_format($_SESSION['totalprice'] - $_SESSION['prenotazione']['TotalePrenotazione'], 2, ',', '.'); ?>€</span>
                        </div>
                    <?php } ?>
                </div>
                <div>
                    <button type="button" class="btn btn-big btn-primary confirm">CONTINUA</button>
                </div>
            </div>
        </div>
        <?php
        // Array di esempio per i posti (non utilizzato direttamente qui)
        include_once("app.php");
        $arr_posti = array();
        for ($i = 1; $i <= 10; $i++) {
            $arr_posti[] = array('postiId' => $i, 'posti' => $i);
        }
        ?>
        <script type="text/javascript">
            // Funzione per submit e redirect dopo stampa ticket
            function submitForm(event) {
                var form = document.getElementById('search');
                form.target = '_blank';
                form.submit();
                setTimeout(function () {
                    window.location.href = 'index.php';
                }, 1000);
            }
            $(document).ready(function () {
                // Invio ticket via email
                $("#inviaTicketEmail").click(function () {
                    var prenotazioneId = $('#prenotazioneId').val();
                    inviaTicketEmail(prenotazioneId);
                });

                // Imposta valori nascosti da localStorage
                $('.prenotazioneId').val(localStorage.getItem('prenotazioneId'));
                $('.corsaId').val(localStorage.getItem('corsaId'));

                // Logout handler
                $('#logout').click(function () {
                    var formData = { action: "logoutBrowser" };
                    $.ajax({
                        url: '<?php echo Config::$UrlMobile; ?>',
                        type: "POST",
                        data: formData,
                        dataType: 'json',
                        success: function (responce) {
                            window.location = "./login.php";
                        }
                    });
                });

                // Dati passeggeri per la prenotazione
                var passangersData = <?= json_encode($_SESSION['BigliettoTipologiaPax']) ?>;
                var TipoBigliettoIdData = <?= json_encode($_SESSION['TipoBigliettoId']) ?>;

                // Conferma prenotazione
                $('.confirm').on('click', function () {
                    $.ajax({
                        url: `/protected/modules/mobile/mobile.php`,
                        type: "post",
                        data: {
                            action: 'prenotaBigliettoABordo',
                            PickupId: "<?= $_SESSION['PickupId'] ?>",
                            DropoffId: "<?= $_SESSION['DropoffId'] ?>",
                            DataPartenza: "<?= $_SESSION['ticket_date'] ?>",
                            CorsaId: "<?= isset($_SESSION['CorsaId']) ? $_SESSION['CorsaId'] : '' ?>",
                            TipoViaggio: "<?= $_SESSION['tipo_viaggio'] ?>",
                            DataPartenzaR: "<?= ($_SESSION['tipo_viaggio'] == 2) ? $_SESSION['ticket_date'] : '' ?>",
                            CorsaIdR: "<?= ($_SESSION['tipo_viaggio'] == 2) ? $_SESSION['CorsaIdR'] : '' ?>",
                            PickupIdR: "<?= ($_SESSION['tipo_viaggio'] == 2) ? $_SESSION['PickupIdR'] : '' ?>",
                            DropoffIdR: "<?= ($_SESSION['tipo_viaggio'] == 2) ? $_SESSION['DropoffIdR'] : '' ?>",
                            AutistaId: "<?= $_SESSION['autista']['AutistiId']; ?>",
                            Email: "<?= $_SESSION['mail'] ?>",
                            Posti: 5,
                            passangersData: passangersData,
                            TipoBigliettoIdData: TipoBigliettoIdData,
                            Congiunti: 0,
                            TipoTour: "<?= $tipoTour ?>",
                            nome: "<?= $_SESSION['nome'] ?>",
                            tel: "<?= $_SESSION['tel'] ?>",
                            prefisso: "<?= $_SESSION['prefisso'] ?>",
                            CodicePrenotazione: "<?= isset($_SESSION['prenotazione']['CodicePrenotazione']) ? $_SESSION['prenotazione']['CodicePrenotazione'] : '' ?>",
                            PrenotazioneId: "<?= isset($_SESSION['prenotazione']['PrenotazioneId']) ? $_SESSION['prenotazione']['PrenotazioneId'] : '' ?>",
                            GestoreIdRef: "<?= $_SESSION['GestoreIdRef'] ?>",
                            TitoloLibera: "<?= isset($_SESSION['TitoloLibera']) ? $_SESSION['TitoloLibera'] : '' ?>",
                            OrarioPartenzaLibera: "<?= isset($_SESSION['OrarioPartenzaLibera']) ? $_SESSION['OrarioPartenzaLibera'] : '' ?>",
                            OrarioArrivoLibera: "<?= isset($_SESSION['OrarioArrivoLibera']) ? $_SESSION['OrarioArrivoLibera'] : '' ?>",
                            BarcaLibera: "<?= isset($_SESSION['BarcaLibera']) ? $_SESSION['BarcaLibera'] : '' ?>",
                            PrenotazioneLibera: "<?= isset($_SESSION['prenotazione_libera']) ? $_SESSION['prenotazione_libera'] : 0 ?>",
                            LiberaImporto: "<?= isset($_SESSION['LiberaImporto']) ? str_replace(array("\r", "\n"), '', $_SESSION['LiberaImporto']) : '' ?>",
                        },
                        success: function (response) {
                            // Parsing della risposta JSON
                            var jsonStartIndex = response.indexOf('{');
                            var jsonSubstring = response.substring(jsonStartIndex);
                            try {
                                var jsonResponse = JSON.parse(jsonSubstring);
                                var resultObject = jsonResponse.result;
                                // Seconda chiamata AJAX per emissione biglietto
                                $.ajax({
                                    url: `/protected/modules/mobile/mobile.php`,
                                    type: "post",
                                    data: {
                                        action: 'emettiABordo',
                                        DataPartenza: "<?= $_SESSION['ticket_date'] ?>",
                                        CorsaId: "<?= $_SESSION['CorsaId'] ?>",
                                        AutistaId: "<?= $_SESSION['autista']['AutistiId']; ?>",
                                        PrenotazioneId: resultObject,
                                        pagamentoId: "<?= $_SESSION['pagamentoId'] ?>",
                                        scontrinoInvio: <?= $_SESSION['scontrinoInvio'] ?>
                                    },
                                    success: function (output) {
                                        try {
                                            if (typeof output !== "string" || output.trim() === "") {
                                                throw new Error("L'output è vuoto o non è una stringa valida.");
                                            }
                                            var result = JSON.parse(output);
                                            $('.prenotazioneId').val(result.result.prenotazioneId);
                                            $('.corsaId').val(result.result.corsaId);
                                            localStorage.setItem('prenotazioneId', result.result.prenotazioneId);
                                            localStorage.setItem('corsaId', result.result.corsaId);
                                            $('.modal-success').removeClass('d-none');
                                            $('#showTicket').attr(
                                                'href',
                                                "./show-ticket.php?PrenotazioneId=" + result.result.prenotazioneId +
                                                "&CorsaId=" + result.result.corsaId +
                                                "&DataPartenza=<?= $DataPartenza ?>"
                                            );
                                        } catch (e) {
                                            alert(output);
                                        }
                                    }
                                });
                            } catch (error) {
                                alert(response);
                                console.error("Error parsing JSON:", error);
                            }
                        }
                    });
                });

                // Gestione pulsante "indietro"
                $('#back').click(function () {
                    <?php if ($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
                        window.location.href = "./index.php";
                    <?php } else { ?>
                        window.location = "./passenger-data.php";
                    <?php } ?>
                });
            });
        </script>
    <?php
}

// Logica di routing principale
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

    // Gestione azioni tramite parametro 'do'
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
    // Se l'utente non è loggato, redirect a login
    header('Location: ./login.php');
}
?>
