<?php
// Single Page Booking - Bertoldi Boats
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include_app.php");
$config = new Config();
$config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.TipologiaBus.php");
include_once($classespath_ . "class.DT.php");
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

session_start();
if (!isset($_SESSION['autista'])) {
    header('Location: ./login.php');
    exit;
}
$autista = $_SESSION['autista'];
$db = new Database();
$db->connect();

// Recupera dati iniziali
$slqGestore = "SELECT GestoreId, RagioneSociale as Gestore FROM Gestore WHERE Stato = 1 AND Cancella = 0 ORDER BY RagioneSociale ASC";
$arr_gestore = $db->fetch_array($slqGestore);

// Funzione per step AJAX con logica reale
function getStepData($step, $params) {
    global $db;
    switch($step) {
        case 'esperienze':
            $tipoTour = isset($params['tipo_tour']) ? intval($params['tipo_tour']) : 0;
            $sqlCorse = "SELECT l.LineaId, l.LineaNome, l.LineaDescrizione FROM RT_Linea l LEFT JOIN RT_Percorso p ON p.PercorsoId = l.PercorsoId WHERE l.Stato = 1 AND l.Cancella = 0 AND l.TipoTour = $tipoTour AND p.Stato = 1 AND p.Cancella = 0 AND l.LineaId <> 14 AND l.IsWebSelling = 1 ORDER BY p.PercorsoPeso, l.LineaPeso, l.LineaNome;";
            $arr_tour = $db->fetch_array($sqlCorse);
            return json_encode($arr_tour);
        case 'posti':
            // Calcola posti disponibili per la linea selezionata e data
            $lineaId = isset($params['lineaId']) ? intval($params['lineaId']) : 0;
            $dataPartenza = isset($params['dataPartenza']) ? $params['dataPartenza'] : date('Y-m-d');
            $sql = "SELECT MAX(PostiTotali) as max_posti FROM RT_Corsa WHERE LineaId = $lineaId AND DataPartenza = '$dataPartenza' AND Stato = 1 AND Cancella = 0";
            $row = $db->query_first($sql);
            $max_posti = $row ? intval($row['max_posti']) : 50;
            // Calcola prenotati
            $sql2 = "SELECT SUM(PostiPrenotati) as prenotati FROM RT_Prenotazione WHERE LineaId = $lineaId AND DataPartenza = '$dataPartenza' AND Stato = 1 AND Cancella = 0";
            $row2 = $db->query_first($sql2);
            $prenotati = $row2 ? intval($row2['prenotati']) : 0;
            $posti_disponibili = $max_posti - $prenotati;
            return json_encode(['posti_disponibili' => max($posti_disponibili,1)]);
        case 'porti':
            $sql = "SELECT PortoId, PortoNome FROM Porto WHERE Stato = 1 AND Cancella = 0 ORDER BY PortoNome ASC";
            $arr_porti = $db->fetch_array($sql);
            return json_encode($arr_porti);
        case 'prezzi':
            // Recupera prezzi dalla tabella prezzi per la linea selezionata
            $lineaId = isset($params['lineaId']) ? intval($params['lineaId']) : 0;
            $sql = "SELECT PrezzoAdulto, PrezzoBambino FROM RT_Linea WHERE LineaId = $lineaId";
            $row = $db->query_first($sql);
            return json_encode(['adulto' => $row['PrezzoAdulto'], 'bambino' => $row['PrezzoBambino']]);
        case 'extra':
            // Recupera servizi extra disponibili
            $sql = "SELECT ServizioId, ServizioNome, Prezzo FROM RT_ServizioExtra WHERE Stato = 1 AND Cancella = 0";
            $arr_extra = $db->fetch_array($sql);
            $result = [];
            foreach($arr_extra as $e) {
                $result[$e['ServizioNome']] = $e['Prezzo'];
            }
            return json_encode($result);
        case 'save_step':
            // Salva i dati step in sessione
            foreach($params as $k=>$v) $_SESSION[$k] = $v;
            return json_encode(['ok'=>true]);
        default:
            return json_encode([]);
    }
}

// Gestione chiamate AJAX
if (isset($_POST['ajax_step'])) {
    $step = $_POST['ajax_step'];
    $params = isset($_POST['params']) ? $_POST['params'] : [];
    echo getStepData($step, $params);
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
    <script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</head>
<body class="main-bg" id="nuova-prenotazione">
    <!-- Top menu con logo e logout -->
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
            <!-- Barra info con titolo -->
            <div class="info-bar">
                <button class="btn btn-rounded btn-primary" id="back">
                    <svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <div class="info">
                    <span>Prenotazione Tour</span>
                </div>
            </div>
            <div class="form-container">
                <div class="card-cta">
                    <p>Compila i campi e conferma la prenotazione!</p>
                </div>
                <!-- Stepper e step single page -->
                <form id="bookingForm">
                    <div class="stepper" style="margin-bottom:20px;">
                        <span class="step-indicator" id="indicator-1">1. Gestore</span>
                        <span class="step-indicator" id="indicator-2">2. Tipo Tour</span>
                        <span class="step-indicator" id="indicator-3">3. Esperienza</span>
                        <span class="step-indicator" id="indicator-4">4. Posti</span>
                        <span class="step-indicator" id="indicator-5">5. Porto</span>
                        <span class="step-indicator" id="indicator-6">6. Prezzi</span>
                        <span class="step-indicator" id="indicator-7">7. Extra</span>
                        <span class="step-indicator" id="indicator-8">8. Dati Passeggeri</span>
                    </div>
                    <!-- Step 1: Gestore -->
                    <div class="step active" id="step1">
                        <div class="form-group">
                            <label for="seleziona_gestore">Seleziona Azienda / Hotel che emette il biglietto</label>
                            <select name="seleziona_gestore" id="seleziona_gestore">
                                <option value="">- seleziona -</option>
                                <?php foreach ($arr_gestore as $t) { ?>
                                    <option value="<?= $t['GestoreId']; ?>"> <?= $t['Gestore']; ?> </option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary w-full btn-big next-step">Avanti</button>
                    </div>
                    <!-- Step 2: Tipo Tour -->
                    <div class="step" id="step2">
                        <div class="form-group">
                            <label for="tipo_tour">Tipo Tour</label>
                            <select name="tipo_tour" id="tipo_tour">
                                <option value="0">Tour di gruppo </option>
                                <option value="1">Tour privato </option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary w-full btn-big next-step">Avanti</button>
                    </div>
                    <!-- Step 3: Esperienza -->
                    <div class="step" id="step3">
                        <div class="form-group">
                            <label for="seleziona_esperienza">Seleziona l'esperienza</label>
                            <select name="seleziona_esperienza" id="seleziona_esperienza"></select>
                            <div id="note_tour" style="margin-top:10px;font-size: 14px;"></div>
                        </div>
                        <button type="button" class="btn btn-primary w-full btn-big next-step">Avanti</button>
                    </div>
                    <!-- Step 4: Posti -->
                    <div class="step" id="step4">
                        <div class="form-group">
                            <label for="posti">Numero di posti</label>
                            <input type="number" name="posti" id="posti" min="1" max="50" required>
                        </div>
                        <button type="button" class="btn btn-primary w-full btn-big next-step">Avanti</button>
                    </div>
                    <!-- Step 5: Porto -->
                    <div class="step" id="step5">
                        <div class="form-group">
                            <label for="porto">Porto di partenza</label>
                            <select name="porto" id="porto"></select>
                        </div>
                        <button type="button" class="btn btn-primary w-full btn-big next-step">Avanti</button>
                    </div>
                    <!-- Step 6: Prezzi -->
                    <div class="step" id="step6">
                        <div class="form-group">
                            <label>Prezzi</label>
                            <div id="prezzi"></div>
                        </div>
                        <button type="button" class="btn btn-primary w-full btn-big next-step">Avanti</button>
                    </div>
                    <!-- Step 7: Extra -->
                    <div class="step" id="step7">
                        <div class="form-group">
                            <label>Servizi Extra</label>
                            <div id="extra"></div>
                        </div>
                        <button type="button" class="btn btn-primary w-full btn-big next-step">Avanti</button>
                    </div>
                    <!-- Step 8: Dati Passeggeri -->
                    <div class="step" id="step8">
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" name="nome" id="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="cognome">Cognome</label>
                            <input type="text" name="cognome" id="cognome" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" required>
                        </div>
                        <button type="submit" class="btn btn-success w-full btn-big">Procedi alla conferma</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let currentStep = 1;
function showStep(step) {
    $('.step').removeClass('active');
    $('#step' + step).addClass('active');
    $('.step-indicator').removeClass('font-weight-bold');
    $('#indicator-' + step).addClass('font-weight-bold');
}
function ajaxStep(step, params, callback) {
    $.post('', { ajax_step: step, params: params }, function(data) {
        callback(data);
    }, 'json');
}
$(document).ready(function() {
    // Stepper avanzamento
    $('.next-step').click(function() {
        let valid = true;
        $('#step' + currentStep + ' :input[required]').each(function(){
            if(!this.value) valid = false;
        });
        if (valid) {
            // Salva dati step in sessione
            let stepData = {};
            $('#step' + currentStep + ' :input').each(function(){
                stepData[$(this).attr('name')] = $(this).val();
            });
            ajaxStep('save_step', stepData, function(){});
            currentStep++;
            showStep(currentStep);
            // Carica dati step
            if (currentStep === 3) {
                let tipoTour = $('#tipo_tour').val();
                ajaxStep('esperienze', { tipo_tour: tipoTour }, function(data) {
                    let $sel = $('#seleziona_esperienza');
                    $sel.empty();
                    $sel.append('<option value="">- seleziona -</option>');
                    $.each(data, function(i, t) {
                        $sel.append('<option value="'+t.LineaId+'">'+t.LineaNome+' - '+t.LineaDescrizione+'</option>');
                    });
                });
            }
            if (currentStep === 4) {
                let lineaId = $('#seleziona_esperienza').val();
                let dataPartenza = new Date().toISOString().slice(0,10);
                ajaxStep('posti', { lineaId: lineaId, dataPartenza: dataPartenza }, function(data) {
                    $('#posti').attr('max', data.posti_disponibili);
                });
            }
            if (currentStep === 5) {
                ajaxStep('porti', {}, function(data) {
                    let $sel = $('#porto');
                    $sel.empty();
                    $sel.append('<option value="">- seleziona -</option>');
                    $.each(data, function(i, t) {
                        $sel.append('<option value="'+t.PortoId+'">'+t.PortoNome+'</option>');
                    });
                });
            }
            if (currentStep === 6) {
                let lineaId = $('#seleziona_esperienza').val();
                ajaxStep('prezzi', { lineaId: lineaId }, function(data) {
                    $('#prezzi').html('Adulto: ' + data.adulto + '€<br>Bambino: ' + data.bambino + '€');
                });
            }
            if (currentStep === 7) {
                ajaxStep('extra', {}, function(data) {
                    let html = '';
                    $.each(data, function(k, v) {
                        html += k + ': ' + v + '€<br>';
                    });
                    $('#extra').html(html);
                });
            }
        } else {
            alert('Compila tutti i campi obbligatori!');
        }
    });
    // Submit finale
    $('#bookingForm').submit(function(e) {
        e.preventDefault();
        // Qui puoi inviare tutti i dati raccolti via AJAX o POST
        // Per demo, redirect a conferma
        window.location = 'confirm-booking.php';
    });
    showStep(currentStep);
});
</script>
</body>
</html>
