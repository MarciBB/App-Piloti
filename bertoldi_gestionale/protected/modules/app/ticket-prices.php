<?php
// Imposta il percorso base del documento
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include_app.php");

// Inizializza la configurazione
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

// Inizializza la gestione degli errori
$errors = new Errors();

// Includi le classi necessarie
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.TipologiaBus.php");
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

// Connessione al database
$db = new Database();
$db->connect();

// Controllo login autista
if (!isset($_SESSION['autista'])) {
  header('Location: ./login.php');
}
$autista = $_SESSION['autista'];

// Imposta cookie cross-site
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

/**
 * Funzione per la creazione di un nuovo ticket
 */
function nuovaTicket()
{
  if (!isset($_SESSION['tipo_tour'])) {
    // Effettua il redirect alla pagina index.php se la sessione non è valida
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
    <link rel="stylesheet" href="./css/new-ticket.css" />
    <link rel="stylesheet" href="./css/style.css" />
    <link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>

    <script type="text/javascript" src="/js/menu_hover.js"></script>
    <script type="text/javascript" language="javascript" src="/js/dialogbox.js?v=3"></script>
    <script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
    <script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
    <script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
    <script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <!-- Bootstrap CSS/JS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
    <script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
      <script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
  </head>
  <?php
  // Inizializzazione variabili e oggetti
  $codiceControllo = 'gC_6XHrZvC9$avW!';
  global $user, $HtmlCommon, $dizionario, $autista;
  $db = new Database();
  $db->connect();

  // Simulazione utente amministratore (da sostituire con dati reali in produzione)
  $user = new Operatore();
  $user->OperatoreId = 1;
  $user->OdcId = 1;
  $user->IsAdmin = 1;
  $user->GestoreId = 1;

  // Recupero dati da request o sessione
  if (isset($_REQUEST['DataPartenza'])) {
    $DataPartenza = $_REQUEST['DataPartenza'];
  }
  if (isset($_REQUEST['CorsaId'])) {
    $CorsaId = $_REQUEST['CorsaId'];
  }

  // Imposta la data di partenza e la corsa corrente
  $DataPartenza = date('Y-m-d');
  $datacorrente = Date('d/m/Y');
  $CorsaId = 0;
  if (isset($_POST['CorsaId'])) {
    $CorsaId = $_POST['CorsaId'];
  }
  if (isset($_SESSION['CorsaId'])) {
    $CorsaId = $_SESSION['CorsaId'];
  }
  if (isset($_POST['DataPartenza'])) {
    $dt = new DT();
    $post_dal = $_POST['DataPartenza'];
    $DataPartenza = $_POST['DataPartenza'];
    $dateTime = new DateTime($DataPartenza);
    $datacorrente = $dateTime->format('d/m/Y');
    $_SESSION['ticket_date'] = $_POST['DataPartenza'];
  }

  // Recupera i nomi dei porti di partenza e destinazione
  $porto_partenza = $_SESSION['porto_partenza'];
  $sqlPartenza = "SELECT * FROM Comune WHERE ComuneId = ".$porto_partenza;
  $resultRowPartenza = $db->query_first($sqlPartenza);
  $porto_partenza_nome = rimuoviParentesiComune($resultRowPartenza['Comune']);
  $_SESSION['porto_partenza_nome'] = $porto_partenza_nome;

  $porto_destinazione = $_SESSION['porto_destinazione'];
  $sqlDestinazione = "SELECT * FROM Comune WHERE ComuneId = ".$porto_destinazione;
  $resultRowDestinazione = $db->query_first($sqlDestinazione);
  $porto_destinazione_nome = rimuoviParentesiComune($resultRowDestinazione['Comune']);
  $_SESSION['porto_destinazione_nome'] = $porto_destinazione_nome;

  // Tipo viaggio e tour
  if (isset($_SESSION['tipo_viaggio'])) {
    $tipo_viaggio = $_SESSION['tipo_viaggio'];
  } else {
    $tipo_viaggio = 1;
    $_SESSION['tipo_viaggio'] = 1;
  }
  $tipoTour = $_SESSION['tipo_tour'];
  $selezionaLesperienza = $_SESSION['seleziona_lesperienza'];
  $DataFiltroA =  date('d/m/Y', strtotime($_SESSION['ticket_date']));
  $DataFiltroASelected = $_SESSION['ticket_date'];

  // Calcolo posti totali selezionati
  if (isset($_SESSION['BigliettoTipologiaPax'])) {
    $bigliettoTipologiaPax = $_SESSION['BigliettoTipologiaPax'];
    if($tipoTour == 1) {
      $postiTotali = 0;
      foreach ($bigliettoTipologiaPax as $key => $value) {
        $sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = " . $key;
        $temp = $db->query_first($sql);
        $postiTotali = (int)estraiPax($temp['TipologiaBiglietto']);
      }
    } else {
      $postiTotali = 0;
      foreach ($bigliettoTipologiaPax as $key => $value) {
        $postiTotali += $value;
      }
    }
    $_SESSION['postiTotali'] = $postiTotali;
  }

  // Recupera altri dati di sessione
  if (isset($_SESSION['TipoBigliettoId'])) {
    $tipoBigliettoId = $_SESSION['TipoBigliettoId'];
  }
  if (isset($_SESSION['passangerId'])) {
    $passangerId = $_SESSION['passangerId'];
    $ids = array_values($passangerId);
    $result = implode(', ', $ids);
  }

  // Gestione POST per selezione corsa
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['DataPartenza'])) {

    if ($selezionaLesperienza == 14) {
        // Solo i nuovi campi per prenotazione libera
        if (isset($_POST['TitoloLibera'])) {
            $_SESSION['TitoloLibera'] = $_POST['TitoloLibera'];
        }
        if (isset($_POST['OrarioPartenzaLibera'])) {
            $_SESSION['OrarioPartenzaLibera'] = $_POST['OrarioPartenzaLibera'];
        }
        if (isset($_POST['OrarioArrivoLibera'])) {
            $_SESSION['OrarioArrivoLibera'] = $_POST['OrarioArrivoLibera'];
        }
        if (isset($_POST['BarcaLibera'])) {
            $_SESSION['BarcaLibera'] = $_POST['BarcaLibera'];
            $sql = "Select * FROM RT_Flotta WHERE FlottaId = " . $_POST['BarcaLibera'];
            $result = $db->query_first($sql);
            $_SESSION['BarcaLiberaNome'] = $result['Modello'];
        }
        if (isset($_POST['total_price'])) {
            $_SESSION['total_price'] = $_POST['total_price'];
            $_SESSION['LiberaImporto'] = $_POST['total_price'];
        }
        $_SESSION['prenotazione_libera'] = 1;
        $_SESSION['start_time'] = $_POST['OrarioPartenzaLibera'];
        $_SESSION['end_time'] = $_POST['OrarioArrivoLibera'];
        $_SESSION['Pickup'] = 'Porto';
        $_SESSION['Dropoff'] = 'Porto';
        $_SESSION['CorsaId'] = null;
        $time1 = new DateTime($_POST['OrarioPartenzaLibera']);
        $time2 = new DateTime($_POST['OrarioArrivoLibera']);
        $timeDifference = $time1->diff($time2);

        // Formatta la differenza oraria
        $formattedDifference = sprintf(
          '%dh %dm',
          $timeDifference->h + $timeDifference->days * 24,
          $timeDifference->i
        );
        $_SESSION['time_diffrence'] = $formattedDifference;
        $_SESSION['PickupId'] = $_SESSION['porto_partenza'];
        $_SESSION['DropoffId'] = $_SESSION['porto_destinazione'];
      } else {
        // Campi standard già presenti
        $_SESSION['CorsaId'] = $_POST['CorsaId'];
        $_SESSION['total_price'] = $_POST['total_price'];
        $_SESSION['PickupId'] = $_POST['PickupId'];
        $_SESSION['Pickup'] = $_POST['Pickup'];
        $_SESSION['DropoffId'] = $_POST['DropoffId'];
        $_SESSION['Dropoff'] = $_POST['Dropoff'];
        $_SESSION['start_time'] = $_POST['start_time'];
        $_SESSION['end_time'] = $_POST['end_time'];
        $_SESSION['time_diffrence'] = $_POST['time_diffrence'];
        $_SESSION['location'] = $_POST['location'];
        $_SESSION['prenotazione_libera'] = 0;
    }
}


  if ($selezionaLesperienza == 14) {
      // Query per recuperare le barche disponibili
      $sqlBarche = "SELECT * FROM RT_Flotta WHERE Stato = 1 AND Cancella = 0";
      $barche = $GLOBALS['db']->fetch_array($sqlBarche);
      ?>
      <body class="main-bg" id="nuova-prenotazione-2">
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
                  <span>Corsa Libera</span>
                  <form id="change-date-form" method="post" style="display:inline;">
                    <input type="hidden" name="DataPartenza" id="DataPartenza_hidden" value="<?= isset($_SESSION['ticket_date']) ? date('d/m/Y', strtotime($_SESSION['ticket_date'])) : date('d/m/Y') ?>">
                    <span class="date" id="date-display" style="cursor:pointer; text-decoration:underline;">
                      <?= date('M d, Y', strtotime($_SESSION['ticket_date'])) ?>
                    </span>
                  </form>
                </div>
            </div>
            <div class="form-container">
              <div class="card-cta">
                <p>Compila i dati della prenotazione libera:</p>
              </div>
              <form action="" method="post" id="libera_form" class="libera_form_class">
                <div class="form-group">
                  <label for="TitoloLibera">Titolo</label>
                  <input type="text" name="TitoloLibera" id="TitoloLibera" maxlength="255" class="form-control" required
                    value="<?php echo isset($_SESSION['TitoloLibera']) ? htmlspecialchars($_SESSION['TitoloLibera']) : ''; ?>">
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6" style="margin-right:1%;max-width:49%;padding: 16px 20px;">
                    <label for="OrarioPartenzaLibera">Orario Partenza</label>
                    <input type="text" name="OrarioPartenzaLibera" id="OrarioPartenzaLibera" class="form-control Orario" required
                      value="<?php echo isset($_SESSION['OrarioPartenzaLibera']) ? htmlspecialchars($_SESSION['OrarioPartenzaLibera']) : ''; ?>">
                  </div>
                  <div class="form-group col-md-6" style="padding: 16px 20px;">
                    <label for="OrarioArrivoLibera">Orario Arrivo</label>
                    <input type="text" name="OrarioArrivoLibera" id="OrarioArrivoLibera" class="form-control Orario" required
                      value="<?php echo isset($_SESSION['OrarioArrivoLibera']) ? htmlspecialchars($_SESSION['OrarioArrivoLibera']) : ''; ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label for="BarcaLibera">Barca</label>
                  <select name="BarcaLibera" id="BarcaLibera" class="form-control" required>
                    <option value="">Seleziona una barca</option>
                    <?php
                    if (is_array($barche)) {
                      foreach ($barche as $barca) {
                        $selected = (isset($_SESSION['BarcaLibera']) && $_SESSION['BarcaLibera'] == $barca['FlottaId']) ? 'selected' : '';
                        echo '<option value="' . $barca['FlottaId'] . '" ' . $selected . '>' . htmlspecialchars($barca['Modello']) . '</option>';
                      }
                    }
                    ?>
                  </select>
                </div>
                <div class="form-group">
                  <label for="total_price">Importo (€)</label>
                  <input type="number" step="0.01" min="0" name="total_price" id="total_price" class="form-control" required
                    value="<?php echo isset($_SESSION['LiberaImporto']) ? htmlspecialchars($_SESSION['LiberaImporto']) : ''; ?>">
                </div>
                <div class="px-5">
                    <button class="btn btn-primary w-full btn-big mt-5 rounded-pill" id="continua" type="submit">
                        CONTINUA
                    </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <script type="text/javascript">
        $(document).ready(function() {
          // Maschera orario (es: 00:00)
          $('.Orario').mask('99:99');
          // Gestione submit form corsa libera (puoi personalizzare la logica di invio)
          $('#libera_form').submit(function(e) {
            // e.preventDefault();
            // Aggiungi qui eventuale validazione/invio AJAX se necessario
          });
          // Logout handler
          $('#logout').click(function() {
            var formData = { action: "logoutBrowser" };
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
          // Controllo input solo numeri e separatore decimale per il campo importo
          $('#total_price').on('keypress', function(e) {
            var char = String.fromCharCode(e.which);
            // Permetti solo numeri, punto e virgola
            if (!/[0-9.,]/.test(char)) {
              e.preventDefault();
              return false;
            }
            // Permetti solo un separatore decimale (punto o virgola)
            var val = $(this).val();
            if ((char === '.' || char === ',') && (val.indexOf('.') !== -1 || val.indexOf(',') !== -1)) {
              e.preventDefault();
              return false;
            }
          });
          // Gestione pulsante "indietro"
          $('#back').click(function() {
            <?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
              window.location.href = "./index.php";
            <?php } else { ?>
              <?php if($_SESSION['Skip3Step'] == 0) { ?>
                window.location = "./port-choice.php";
              <?php } else { ?>
                window.location = "./choose-seats.php";
              <?php } ?>
            <?php } ?>
          });

          $(".libera_form_class").submit(function(e) {
            e.preventDefault();
            $.ajax({
              type: 'POST',
              url: "<?php echo $_SERVER['REQUEST_URI']; ?>",
              data: $(this).serialize(),
              success: function(response) {
                  window.location = "./extra-services.php";
              }
            });
          });
        });
        </script>
      </body>
      </html>
      <?php
      // Blocca l'esecuzione del resto della pagina se si è in modalità corsa libera
      return;
    }

  // Azioni biglietto
  include_once('../rt_biglietto/biglietto_action2.php');

  // Parametri per la ricerca delle corse
  $comunePartenzaId = $porto_partenza;
  $comuneDestinazioneId = $porto_destinazione;
  $tp = 'A';
  $dataFiltroA =  $DataFiltroA;
  $dataFiltroR = $DataFiltroA;
  $corsaid = '';
  $dataCorsa = '';

  // Recupera le corse disponibili
  $result = getCorseOpt($comunePartenzaId, $comuneDestinazioneId, $tp, $dataFiltroA, $dataFiltroR, $corsaid, $dataCorsa, $tipoTour, $selezionaLesperienza);

  $prenotazione = new Prenotazione();
  $prenotazione->conn = $db;
  ?>

  <body class="main-bg" id="nuova-prenotazione-2">
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
        <div class="available-list">
          <?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
            <!-- Messaggio se la data è già trascorsa -->
            <div class="available-item" style="text-align:center;">
              Non &egrave; possibile modificare il seguente biglietto. La data del tour del biglietto &egrave; gi&agrave; trascorsa.<br>
              Torna alla <a style="display:contents" href="./index.php">home</a> per altre operazioni.
            </div>
          <?php }  else { ?>
            <?php
            // Visualizza le corse disponibili
            if (json_decode($result)->iTotalRecords > 0) :
              $countCorse = 0;
            ?>
            <div class="form-container">
              <div class="card-cta">
                <p>Seleziona la corsa del tour desiderata!</p>
              </div>
            </div>
            <?php
            $data_ora_attuale = new DateTime();
            $formato = 'd/m/Y H:i:s';
            $arrayCorseVisualizzate = array();
            foreach (json_decode($result)->aaData as $key => $list) :
              $data_ora_spec = DateTime::createFromFormat($formato, $list[4] . " " . $list[6]);
						// Estrai la data da $data_ora_spec in formato Y-m-d
						$dataCorsaSpec = $data_ora_spec ? $data_ora_spec->format('Y-m-d') : '';
              // $list: array con dati corsa
              // 0: CorsaId, 
              // 1: FermataId, 
              // 2: Corsa nome, 
              // 3: Linea nome, 
              // 4: Data partenza, 
              // 5: giorno settimana, 
              // 6: Orario partenza, 
              // 7: Posti totali, 
              // 8: Posti occupati

              // Verifica se la corsa è valida e non già visualizzata
              if($data_ora_attuale < $data_ora_spec &&
                ($list[4] == $DataFiltroA &&
                (($tipoTour == 0 && $list[7] >= $postiTotali) ||
                  ($tipoTour == 1 && $list[8] == 0 && $list[7] >= $postiTotali))
                )
                && !in_array($list[6], $arrayCorseVisualizzate)
              ) {
                // Recupera la corsa dal DB
                $sql = "SELECT * FROM RT_Corsa WHERE CorsaId = ".$list[0]." AND Stato = 1 AND Cancella = 0";
                $corsaTemp = $db->query_first($sql); 

                // Verifica conflitti di orario con la stessa barca
                $sql = "select pp.* from RT_PrenotazionePercorso pp
                  left join RT_Corsa c on c.CorsaId = pp.CorsaId
                  where pp.CorsaDataPartenza = '".$dataCorsaSpec."' and c.FlottaDefaultId = ".$corsaTemp['FlottaDefaultId']."
                  and pp.CorsaOrarioPartenza >= '".$corsaTemp['OrarioPartenza']."' and pp.CorsaOrarioPartenza < '".$corsaTemp['OrarioArrivo']."'
                  and pp.PrenotazioneStato NOT IN (6,4) and pp.CorsaId <> '".$corsaTemp['CorsaId']."'";
                $checkFlotta = $db->query_first($sql);
                if(isset($checkFlotta['PrenotazionePercorsoId']) && $checkFlotta['PrenotazionePercorsoId'] > 0) {
                  continue; // Salta questa corsa se c'è un conflitto di orario
                }

                // Calcolo prezzo totale e validità biglietti
                $totalPrice = 0;
                $validita = true;
                foreach ($bigliettoTipologiaPax as $key => $value) {
                  // Verifica validità biglietto
                  $sql = "SELECT *
                    FROM `RT_ValiditaBiglietto`v
                    LEFT JOIN RT_ValiditaBigliettoDettaglio b on b.ValiditaBigliettoId = v.ValiditaBigliettoId
                    WHERE CorsaId = ".$list[0]."
                    AND '".$DataFiltroASelected."' >= Dal
                    AND '".$DataFiltroASelected."' <= Al
                    AND v.Stato = 1 AND b.Stato = 1 AND v.Cancella = 0 AND b.Cancella = 0
                    AND BigliettoId = $key";
                  $validitaRow = $db->query_first($sql);
                  if(!isset($validitaRow) || !isset($validitaRow['ValiditaBigliettoId'])) {
                    $validita = false;
                  }

                  // Calcolo totale importo
                  $sql = "SELECT * FROM RT_CorsaTariffa WHERE  FermataPickup = $porto_partenza AND FermataDropOff = $porto_destinazione AND TipologiaBigliettoId = $key AND CorsaId = ".$list[0];
                  $tariffaInfo = $db->query_first($sql);
                  $totalPrice += $tariffaInfo['Tariffa'] * $value;

                  // Calcolo sconto eventuale
                  $PercSconto = 0;
                  $ListinoScontoId = $prenotazione->GetScontoPromozioneAttiva($list[0], $DataFiltroASelected, 1, $key);
                  $sql = "select Prezzo from RT_ScontisticaBiglietto where ListinoId = $ListinoScontoId and BigliettoId = $key and Stato = 1 and Cancella = 0";
                  $rowsconto = $db->query_first($sql);
                  if (!empty($rowsconto['Prezzo'])) {
                    $PercSconto = $rowsconto['Prezzo'];
                    if($PercSconto != 0) {
                      $totalPrice = $totalPrice + $totalPrice * $PercSconto / 100;
                      $totalPrice = $prenotazione->arrotonda($totalPrice);
                    }
                  }
                }
                if($validita) {
                  $arrayCorseVisualizzate[] = $list[6];
                  $countCorse++;

                  // Recupera orari di partenza e arrivo
                  $sql = "SELECT * FROM RT_Orario o
                    LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId WHERE CorsaId = $list[0]
                    AND (f.ComuneId = $porto_partenza OR f.ComuneId = $porto_destinazione)
                    AND o.Stato = 1
                    AND o.Cancella = 0
                    AND f.Cancella = 0 AND f.Stato = 1";
                  $arrData = $db->fetch_array($sql);
                  $time1 = new DateTime($arrData[0]['Orario']);
                  $time2 = new DateTime($arrData[1]['Orario']);
                  $timeDifference = $time1->diff($time2);

                  // Formatta la differenza oraria
                  $formattedDifference = sprintf(
                    '%dh %dm',
                    $timeDifference->h + $timeDifference->days * 24,
                    $timeDifference->i
                  );
                  ?>
                  <!-- Form di selezione corsa -->
                  <form action="" method="post" id="ticket_form_<?= $key; ?>" class="ticket_form_class <?php if(isset($_SESSION['CorsaId']) && $list[0] == $_SESSION['CorsaId']) echo "corsaSelezionata";?>">
                    <div class="available-item">
                      <div class="travel row">
                        <div class="departure col-md-5 col-sm-12">
                          <input type="hidden" value="<?= $list[0] ?>" name="CorsaId">
                          <input type="hidden" value="<?= $totalPrice ?>" name="total_price">
                          <input type="hidden" value="<?= $arrData[0]['ComuneId'] . '_' . $arrData[0]['FermataId'] ?>" name="PickupId">
                          <input type="hidden" value="<?= $arrData[0]['FermataNome'] ?>" name="Pickup">
                          <input type="hidden" value="<?= $arrData[1]['ComuneId'] . '_' . $arrData[1]['FermataId'] ?>" name="DropoffId">
                          <input type="hidden" value="<?= $arrData[1]['FermataNome'] ?>" name="Dropoff">
                          <input type="hidden" value="<?= date('H:i', strtotime($arrData[0]['Orario'])) ?>" name="start_time">
                          <input type="hidden" value="<?= date('H:i', strtotime($arrData[1]['Orario'])) ?>" name="end_time">
                          <input type="hidden" value="<?= $formattedDifference ?>" name="time_diffrence">
                          <input type="hidden" value="<?= $list[2] ?>" name="location">
                          <div class="time"><?= date('H:i', strtotime($arrData[0]['Orario'])) ?></div>
                          <div class="location"><?= $porto_partenza_nome ?> - <?= $arrData[0]['FermataNome'] ?></div>
                        </div>
                        <div class="duration col-md-2 col-sm-12">
                          <div class="time"> <?= $formattedDifference; ?></div>
                          <div class="arrow-container">
                            <div class="arrow"></div>
                          </div>
                        </div>
                        <div class="arrival col-md-5 col-sm-12">
                          <div class="time"><!--<?= date('H:i', strtotime($arrData[1]['Orario'])) ?>--></div>
                          <div class="location"><?= $porto_destinazione_nome ?> - <?= $arrData[1]['FermataNome'] ?></div>
                        </div>
                      </div>
                      <hr />
                      <div class="actions">
                        <div class="price">
                          <img src="img/user.png" alt="" />
                          <span><?= number_format($totalPrice, 2, ',', '.'); ?>€ tot.</span>
                        </div>
                        <div class="buttons next">
                          <button type="submit" class="btn btn-secondary buttons-label" name="submit"><?= (isset($_SESSION['CorsaId']) && $list[0] == $_SESSION['CorsaId']) ? "TOUR SELEZIONATO" : "SELEZIONA TOUR";?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                  <?php
                }
              }
            endforeach;
            else:
              $countCorse = 0;
            endif;

            // Nessuna corsa disponibile
            if($countCorse == 0) { ?>
              <div class="available-item" style="text-align:center;">
                Non ci sono tour per la data e biglietti selezionati.<br>
                Torna allo <a style="display:contents" href="./choose-seats.php">step precedente</a> o alla <a style="display:contents" href="./index.php">home</a> per selezionare una nuova data.
              </div>
            <?php }
          } ?>
        </div>
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
        var formData = { action: "logoutBrowser" };
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

      // Gestione pulsante "indietro"
      $('#back').click(function() {
        <?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
          window.location.href = "./index.php";
        <?php } else { ?>
          <?php if($_SESSION['Skip3Step'] == 0) { ?>
            window.location = "./port-choice.php";
          <?php } else { ?>
            window.location = "./choose-seats.php";
          <?php } ?>
        <?php } ?>
      });

      // Gestione invio form selezione corsa
      $(".ticket_form_class").submit(function(e) {
        e.preventDefault();
        $.ajax({
          type: 'POST',
          url: "<?php echo $_SERVER['REQUEST_URI']; ?>",
          data: $(this).serialize(),
          success: function(response) {
            <?php if($tipo_viaggio == 1) { ?>
              window.location = "./extra-services.php";
            <?php } else { ?>
              window.location = "./ticket-prices-return.php";
            <?php } ?>
          }
        });
      });
    });
    </script>
  </body>
  </html>
  <?php
}

// Flusso principale: verifica login e permessi
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
  // Se l'utente non è loggato, redirect al login
  header('Location: ./login.php');
}

/**
 * Funzione per rimuovere il testo tra parentesi tonde da una stringa
 * @param string $string
 * @return string
 */
function rimuoviParentesiComune($string) {
  // Espressione regolare per rimuovere qualsiasi testo tra parentesi (con spazi opzionali)
  return preg_replace('/\s*\(.*?\)\s*/i', '', $string);
}
?>
