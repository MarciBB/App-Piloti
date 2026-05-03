<?php
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
include_once($classespath_ . "class.PrefissoTelefono.php");


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
	ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
	if(!isset($_SESSION['tipo_tour'])) {
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
    if (isset($_REQUEST['DataPartenza'])) {
        $DataPartenza = $_REQUEST['DataPartenza'];
    }
    if (isset($_REQUEST['CorsaId'])) {
        $CorsaId = $_REQUEST['CorsaId'];
    }

    $DataPartenza = date('Y-m-d');
    $datacorrente = Date('d/m/Y');
    $CorsaId = 0; //$arr_corse[0]['CorsaId'];
    if (isset($_POST['CorsaId']))
        $CorsaId = $_POST['CorsaId'];

    if (isset($_POST['DataPartenza'])) {
        $dt = new DT();
        $post_dal = $_POST['DataPartenza'];
        $DataPartenza = $_POST['DataPartenza'];
        $dateTime = new DateTime($DataPartenza);
        $datacorrente = $dateTime->format('d/m/Y');
    }

	if (isset($_SESSION['PrenotazioneId']) && !isset($_SESSION['nome'])) { 
		$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId'];
		$rowTemp = $db->query_first($sql);
		$_SESSION['nome'] = $rowTemp['ClienteNome'];
		$_SESSION['tel'] = $rowTemp['ClienteCellulare'];
		$_SESSION['mail'] = $rowTemp['ClienteEmail'];
		$_SESSION['prefisso'] = $rowTemp['ClienteCellularePrefisso'];
		$_SESSION['prenotazione'] = $rowTemp;
		$sql = "SELECT * FROM `RT_PrenotazioneMovimento` WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId']." AND TipoMovimento = 'I'";
		$rowTemp = $db->query_first($sql);
		if($rowTemp['PagamentoTipoId'] == 1) {
			$_SESSION['pagamento'] = 'contanti';
		} else {
			$_SESSION['pagamento'] = 'pos';
		}
		
	}
	
	if(!isset($_SESSION['mail'])) {
		$_SESSION['mail'] = Config::$mobileEmailDefault;
	}

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Store the form input values in the session
        $_SESSION['nome'] = isset($_POST['nome']) ? $_POST['nome'] : '';
        $_SESSION['tel'] = isset($_POST['tel']) ? $_POST['tel'] : '';
        $_SESSION['mail'] = isset($_POST['mail']) ? $_POST['mail'] : '';
        $_SESSION['pagamento'] = isset($_POST['pagamento']) ? $_POST['pagamento'] : '';
		$_SESSION['prefisso'] = isset($_POST['prefisso']) ? $_POST['prefisso'] : '';
		$_SESSION['scontrinoInvio'] = isset($_POST['scontrinoInvio']) ? $_POST['scontrinoInvio'] : '';

        // Redirect to the same page to prevent form resubmission on page refresh
        header("Location: ./confirm-booking.php");
        exit();
    }


    $tipoTour = $_SESSION['tipo_tour'];
	$tipo_viaggio = $_SESSION['tipo_viaggio'];
    $seleziona_lesperienza = $_SESSION['seleziona_lesperienza'];

    $sqlServiziExtra = "SELECT t.*, s.Prezzo, s.Limite FROM RT_TipologiaBiglietto t LEFT JOIN RT_ListinoServizi s on s.BigliettoId = t.TipologiaBigliettoId WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = 1 AND t.OccupaPosto = 0 ORDER BY t.TipologiaBigliettoPeso";
    $arrServiziExtra = $db->fetch_array($sqlServiziExtra);

	$totalPrice = $_SESSION['totalprice'];
	
	//imposto valore di default del prefisso su Italia
	if(!isset($_SESSION['prefisso']) || $_SESSION['prefisso'] == '') {
		$_SESSION['prefisso'] = 39;
	}
	
	//recupero elenco prefissi telefono
	$prefissoObj = new PrefissoTelefono($db);
	$arr_prefisso = $prefissoObj->getAllForSelect();

    ?>

    <body class="main-bg" id="nuova-prenotazione-3-1">
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
        <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" class="new-ticket">

            <div class="main-container">
                <div class="content">
                    <div class="info-bar">
                        <button type="button" class="btn btn-rounded btn-primary" id="back">
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
					<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
						<div class="form-container" style="padding:20px 0px;">
							<div class="available-item" style="text-align:center;">
								Non &egrave; possibile modificare il seguente biglietto. La data del tour del biglietto &egrave; gi&agrave; trascorsa.<br>
								Torna alla <a style="display:contents" href="./index.php">home</a> per altre operazioni.
							</div>
						</div>
					<?php }  else { ?>
					
						<div class="title">
							<span>Informazioni di Contatto</span>
							<div class="dashes"></div>
						</div>
						<div>
							<div class="form-group">
								<label for="nome">Nominativo</label>
								<input id="nome" type="text" name="nome" value="<?php echo isset($_SESSION['nome']) ? $_SESSION['nome'] : ''; ?>" required />
							</div>
							<div class="form-group">
								<label>Recapito Telefonico</label>
								<div class="row">
									<div class="col-md-3">
										<label for="prefisso">Prefisso</label>
										<select name="prefisso" id="prefisso" required>
											<option selected="" value="">-</option>
											<?php foreach($arr_prefisso as $pref) {
												if(isset($_SESSION['prefisso']) && $_SESSION['prefisso'] == $pref['Prefisso']) {
													echo "<option selected='selected' value='".$pref['Prefisso']."'>".$pref['Descrizione']."</option>";
												} else {
													echo "<option value='".$pref['Prefisso']."'>".$pref['Descrizione']."</option>";
												}
											} ?>
										</select>
									</div>
									<div class="col-md-9">
										<label for="tel">Numero Telefonico</label>
										<input id="tel" type="text" name="tel" value="<?php echo isset($_SESSION['tel']) ? $_SESSION['tel'] : ''; ?>" required />
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="mail">Indirizzo Email</label>
								<input id="mail" type="text" name="mail" value="<?php echo isset($_SESSION['mail']) ? $_SESSION['mail'] : ''; ?>" required />
							</div>
						</div>
						<div class="title">
							<span>Metodo di Pagamento</span>
							<div class="dashes"></div>
						</div>
						<div>
							<div class="form-group">
								<div class="checkbox-control">
									<label for="contanti">Contanti</label>
									<input type="radio" name="pagamento" value="contanti" id="contanti" <?php echo (isset($_SESSION['pagamento']) && $_SESSION['pagamento'] == 'contanti') ? 'checked' : ''; ?> required />
								</div>
								<div class="checkbox-control">
									<label for="pos">POS</label>
									<input type="radio" name="pagamento" value="pos" id="pos" <?php echo (isset($_SESSION['pagamento']) && $_SESSION['pagamento'] == 'pos') ? 'checked' : ''; ?> required />
								</div>
							</div>
						</div>
						<div class="title">
							<span>Ricevuta Digitale</span>
							<div class="dashes"></div>
						</div>
						<div>
							<div class="form-group">
								<label for="scontrinoInvio">Invia Ricevuta Digitale (sarà inviata all'email indicata)</label>
								<div class="row">
									<div class="col-md-3">
										<select name="scontrinoInvio" id="scontrinoInvio" required>
											<option selected="" value="0">NO</option>
											<option value="1">SI</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
                </div>
            </div>

            <div class="sticky bottom action-bottombar">
                <div class="info">
					<?php if(!isset($_SESSION['prenotazione'])) { ?>
						<span>Prezzo Totale</span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format($totalPrice, 2, ',', '.'); ?>€</span> 
						</div>
					<?php } else if(isset($_SESSION['prenotazione']) && $totalPrice <= $_SESSION['prenotazione']['TotalePrenotazione']) {  ?>
						<span>Prezzo Totale</span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format(0, 2, ',', '.'); ?>€</span> 
						</div>
						<span>Nessuna variazione di prezzo per modifica di <?=$_SESSION['prenotazione']['CodicePrenotazione']?></span>
					<?php } else if(isset($_SESSION['prenotazione']) && $totalPrice > $_SESSION['prenotazione']['TotalePrenotazione']) { ?>
						<span>Prezzo Totale</span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format($totalPrice, 2, ',', '.'); ?>€</span> 
						</div>
						<span>Importo Pagato per <?=$_SESSION['prenotazione']['CodicePrenotazione']?></span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format($_SESSION['prenotazione']['TotalePrenotazione'], 2, ',', '.'); ?>€</span> 
						</div>
						<span>Residuo</span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format($totalPrice - $_SESSION['prenotazione']['TotalePrenotazione'], 2, ',', '.'); ?>€</span> 
						</div>
					<?php } ?>
                </div>
                <div>
                    <input type="submit" value="CONTINUA" class="btn btn-big btn-primary" id="continua" />
                </div>
            </div>
        </form>

        <?php include_once("app.php"); ?>
        <script type="text/javascript">
            $(document).ready(function() {
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
                        },
                        error: function(xhr, ajaxOptions, thrownError) {

                        },
                    });

                });

                $('#back').click(function() {
					<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
						window.location.href = "./index.php";
					<?php } else { ?>
						<?php if($tipoTour == 0) { ?>
							<?php if($tipo_viaggio == 2) { ?>
								window.location = "./ticket-prices-return.php";
							<?php } else { ?>
								window.location = "./ticket-prices.php";
							<?php } ?>
						<?php } else { ?>
							window.location = "./extra-services.php";
						<?php } ?>
					<?php } ?>
                });
				
				var form = $('#contactForm');
				var telInput = $('#tel');
				var mailInput = $('#mail');
				var telError = $('#telError');
				var mailError = $('#mailError');

				telInput.on('input', function() {
					var telValue = telInput.val();
					var phonePattern = /^\+?(?:[0-9] ?){6,14}[0-9]$/;

					if (!phonePattern.test(telValue)) {
						telInput[0].setCustomValidity('Inserisci un numero di telefono valido, compreso il prefisso internazionale (es. +393461122333).');
					} else {
						telInput[0].setCustomValidity('');
					}
				});

				mailInput.on('input', function() {
					var mailValue = mailInput.val();
					var mailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
					
				   if (!mailPattern.test(mailValue)) {
						mailInput[0].setCustomValidity('Inserisci un indirizzo email valido.');
					} else {
						mailInput[0].setCustomValidity('');
					}
				});

				form.on('submit', function(event) {
					var isValid = true;
					var phonePattern = /^\+(?:[0-9] ?){6,14}[0-9]$/;
					var telValue = telInput.val();

					 if (!phonePattern.test(telValue)) {
						telInput[0].setCustomValidity('Inserisci un numero di telefono valido, compreso il prefisso internazionale (es. +393460636551).');
						isValid = false;
					 }

					var mailValue = mailInput.val();
					var mailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
					if (!mailPattern.test(mailValue)) {
						mailError.text('Inserisci un indirizzo email valido.');
						isValid = false;
					}

					if (!isValid) {
						event.preventDefault();
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
}
// se l'utente non Ã¨ loggato
else {
    header('Location: ./login.php');
}
?>