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
  // include_once("../rt_biglietto/biglietto_validator.php");
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
  $CorsaId = 0; 
  if (isset($_POST['CorsaId'])) {
    $CorsaId = $_POST['CorsaId'];
  }
  
  if (isset($_SESSION['CorsaId'])) {
	$CorsaId = $_SESSION['CorsaId'];
	$corsaAndataSql = "SELECT * FROM RT_Corsa WHERE CorsaId = ".$CorsaId;
	$corsaAndata = $db->query_first($corsaAndataSql);
	$tipologiaBusIdA = $corsaAndata['TipologiaBusDefaultId'];
	$orarioArrivoA = $corsaAndata['OrarioArrivo'];
  }

  if (isset($_POST['DataPartenza'])) {
    $dt = new DT();
    $post_dal = $_POST['DataPartenza'];
    $DataPartenza = $_POST['DataPartenza'];
    $dateTime = new DateTime($DataPartenza);
    $datacorrente = $dateTime->format('d/m/Y');
  }

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
  
  //recupera il ritorno se è di tipo gruppo
  $sql = "SELECT * FROM RT_Linea WHERE LineaId = ".$selezionaLesperienza;
  $lineaRow = $db->query_first($sql);
  $sql = "SELECT * FROM RT_Linea WHERE LineaDa = '".$lineaRow['LineaA']."' AND LineaA = '".$lineaRow['LineaDa']."'";
  $lineaRow = $db->query_first($sql);
  $selezionaLesperienzaR = $lineaRow['LineaId'];

  if (isset($_SESSION['BigliettoTipologiaPax'])) {
    $bigliettoTipologiaPax = $_SESSION['BigliettoTipologiaPax'];
  }

  if (isset($_SESSION['TipoBigliettoId'])) {
    $tipoBigliettoId = $_SESSION['TipoBigliettoId'];
	
	$postiTotali = 0;
	foreach ($bigliettoTipologiaPax as $key => $value) {
		$postiTotali += $value;
	}
  }

  if (isset($_SESSION['passangerId'])) {
    $passangerId = $_SESSION['passangerId'];
    $ids = array_values($passangerId);
    $result = implode(', ', $ids);
  }
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['CorsaIdR'] = $_POST['CorsaIdR'];
    $_SESSION['total_priceR'] = $_POST['total_priceR'];
    $_SESSION['PickupIdR'] = $_POST['PickupIdR'];
	$_SESSION['PickupR'] = $_POST['PickupR'];
    $_SESSION['DropoffIdR'] = $_POST['DropoffIdR'];
	$_SESSION['DropoffR'] = $_POST['DropoffR'];
    $_SESSION['start_timeR'] = $_POST['start_timeR'];
    $_SESSION['end_timeR'] = $_POST['end_timeR'];
    $_SESSION['time_diffrenceR'] = $_POST['time_diffrenceR'];
    $_SESSION['locationR'] = $_POST['locationR'];
  }

  include_once('../rt_biglietto/biglietto_action2.php');

  $comunePartenzaId = $porto_partenza;
  $comuneDestinazioneId = $porto_destinazione;
  $tp = 'R';
  $dataFiltroA =  $DataFiltroA;
  $dataFiltroR = $DataFiltroA;
  $corsaid = '';
  $dataCorsa = '';


	if(isset($selezionaLesperienzaR)) {
		$resultR = getCorseOpt($comunePartenzaId, $comuneDestinazioneId, $tp, $dataFiltroA, $dataFiltroR, $corsaid, $dataCorsa, $tipoTour, $selezionaLesperienzaR);
	} else {
		$resultR = array();
	}
	
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
            <div class="date" data-date="<?= $DataFiltroA; ?>"><?= date('M d, Y', strtotime($_SESSION['ticket_date'])) ?></div>
          </div>
        </div>
        <div class="available-list">
		  <?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
			<div class="available-item" style="text-align:center;">
				Non &egrave; possibile modificare il seguente biglietto. La data del tour del biglietto &egrave; gi&agrave; trascorsa.<br>
				Torna alla <a style="display:contents" href="./index.php">home</a> per altre operazioni.
			</div>
		  <?php }  else { ?>
				<?php if($tipo_viaggio == 2) {

				  if (json_decode($resultR)->iTotalRecords > 0) :
					$countCorse = 0;
					?>
					<div class="form-container">
						<div class="card-cta" style="margin-top: 2rem;">
							<p style="font-size:1rem;">Seleziona la corsa di ritorno del tour desiderata!</p>
						</div>
					</div>
					<?php
					$data_ora_attuale = new DateTime();
					$formato = 'd/m/Y H:i:s';
					$arrayCorseVisualizzate = array();

					foreach (json_decode($resultR)->aaData as $key => $list) :
						$data_ora_spec = DateTime::createFromFormat($formato, $list[4] . " " . $list[6]);
            // Estrai la data da $data_ora_spec in formato Y-m-d
						$dataCorsaSpec = $data_ora_spec ? $data_ora_spec->format('Y-m-d') : '';
						// 0: CorsaId
						// 1: FermataId
						// 2: Corsa nome
						// 3: Linea nome
						// 4: Data partenza
						// 5: giorno settimana
						// 6: Orario partenza
						// 7: Posti totali
						// 8: Posti occupati
						
						//Controllo corsa stessa barca dell'andata
						$corsaRitornoSql = "SELECT * FROM RT_Corsa WHERE CorsaId = ".$list[0];
						$corsaRitorno = $db->query_first($corsaRitornoSql);

						if($corsaRitorno['TipologiaBusDefaultId'] == $tipologiaBusIdA && $corsaRitorno['OrarioPartenza'] >= $orarioArrivoA && $data_ora_attuale < $data_ora_spec ) {
					
							if($list[4] == $DataFiltroA 
								&& (($tipoTour == 0 && $list[7] >= $postiTotali) || 
									($tipoTour == 1 && $list[8] == 0 && $list[7] >= $_SESSION['postiTotali']))
								&& !in_array($list[6], $arrayCorseVisualizzate)
								) {

                  //recupero la corsa
                  $sql = "SELECT * FROM RT_Corsa WHERE CorsaId = ".$list[0]." AND Stato = 1 AND Cancella = 0";
                  $corsaTemp = $db->query_first($sql);

                  //verifico se non esiste una corsa con la stessa barca nello stesso momento
                  $sql = "select pp.* from RT_PrenotazionePercorso pp
                        left join RT_Corsa c on c.CorsaId = pp.CorsaId
                        where pp.CorsaDataPartenza = '".$dataCorsaSpec."' and c.FlottaDefaultId = ".$corsaTemp['FlottaDefaultId']."
                        and pp.CorsaOrarioPartenza >= '".$corsaTemp['OrarioPartenza']."' and pp.CorsaOrarioPartenza < '".$corsaTemp['OrarioArrivo']."'
                        and pp.PrenotazioneStato NOT IN (6,4) and pp.CorsaId <> '".$corsaTemp['CorsaId']."'";
                        $checkFlotta = $db->query_first($sql);
                  if(isset($checkFlotta['PrenotazionePercorsoId']) && $checkFlotta['PrenotazionePercorsoId'] > 0) {
                    continue; // salta questa corsa se c'è un conflitto di orario
                  }
                  
								//calcolo prezzo e verifico validità dei biglietti per far comparire la corsa
								$totalPrice = 0;
								$validita = true;
								foreach ($bigliettoTipologiaPax as $key => $value) {
									//varifica validita
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
									
									//calcolo totale importo
									$sql = "SELECT * FROM RT_CorsaTariffa WHERE  FermataPickup = $porto_destinazione AND FermataDropOff = $porto_partenza AND TipologiaBigliettoId = $key AND CorsaId = ".$list[0];
									
									$tariffaInfo = $db->query_first($sql);
									$totalPrice += $tariffaInfo['Tariffa'] * $value;
									
									//calcolo dello sconto
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
									$countCorse++;
									$arrayCorseVisualizzate[] = $list[6];

									$sql = "SELECT * FROM RT_Orario o LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId WHERE CorsaId = $list[0] AND (f.ComuneId = $porto_partenza OR f.ComuneId = $porto_destinazione) AND o.Stato = 1 AND o.Cancella = 0 and f.Cancella = 0 and f.Stato = 1";
									$arrData = $db->fetch_array($sql);

									// print_r($user);
									$time1 = new DateTime($arrData[0]['Orario']);
									$time2 = new DateTime($arrData[1]['Orario']);
									$timeDifference = $time1->diff($time2);

									$formattedDifference = sprintf(
									'%dh %dm',
									$timeDifference->h + $timeDifference->days * 24, // Include days in hours
									$timeDifference->i
									);

							  ?>

							  <form action="" method="post" id="ticket_form_<?= $key; ?>" class="ticket_form_class <?php if($list[0] == $_SESSION['CorsaIdR']) echo "corsaSelezionata";?>">
								<div class="available-item">
								  <div class="travel row">
									<div class="departure col-md-5 col-sm-12"> 

									  <input type="hidden" value="<?= $list[0] ?>" name="CorsaIdR">
									  <input type="hidden" value="<?= $totalPrice ?>" name="total_priceR">
									  <input type="hidden" value="<?= $arrData[0]['ComuneId'] . '_' . $arrData[0]['FermataId'] ?>" name="PickupIdR">
									  <input type="hidden" value="<?= $arrData[0]['FermataNome'] ?>" name="PickupR">
									  <input type="hidden" value="<?= $arrData[1]['ComuneId'] . '_' . $arrData[1]['FermataId'] ?>" name="DropoffIdR">
									  <input type="hidden" value="<?= $arrData[1]['FermataNome'] ?>" name="DropoffR">
									  <input type="hidden" value="<?= date('H:i', strtotime($arrData[0]['Orario'])) ?>" name="start_timeR">
									  <input type="hidden" value="<?= date('H:i', strtotime($arrData[1]['Orario'])) ?>" name="end_timeR">
									  <input type="hidden" value="<?= $formattedDifference ?>" name="time_diffrenceR">
									  <input type="hidden" value="<?= $list[2] ?>" name="locationR">


									  <div class="time"><?= date('H:i', strtotime($arrData[0]['Orario'])) ?></div>
									  <div class="location"><?= $porto_destinazione_nome ?> - <?= $arrData[0]['FermataNome'] ?></div>
									</div>
									<div class="duration col-md-2 col-sm-12">
									  <div class="time"> <?= $formattedDifference; ?></div>
									  <div class="arrow-container">
										<div class="arrow"></div>
									  </div>
									</div>
									<div class="arrival col-md-5 col-sm-12">
									  <div class="time"><!--<?= date('H:i', strtotime($arrData[1]['Orario'])) ?>--></div>
									  <div class="location"><?= $porto_partenza_nome ?> - <?= $arrData[1]['FermataNome'] ?></div>
									</div>
								  </div>
								  <hr />
								  <div class="actions">
									<?php if($totalPrice > 0) { ?>
										<div class="price"> <img src="img/user.png" alt="" /> <span><?= number_format($totalPrice, 2, ',', '.'); ?>€ tot.</span> </div> 
									<?php } else { ?>
										<div class="price"> </div>
									<?php } ?>
									<div class="buttons next">
									  <!-- <?= $list[1] ?> -->
									  <button type="submit" class="btn btn-secondary buttons-label" name="submit"><?= (isset($_SESSION['CorsaIdR']) && $list[0] == $_SESSION['CorsaIdR']) ? "TOUR SELEZIONATO" : "SELEZIONA TOUR";?></button>
									</div>

								  </div>
								</div>
							  </form>
							  
						  <?php
								}
							}
						}
					endforeach;
				  else:
					$countCorse = 0;
				  endif;
				  
				  if($countCorse == 0) { ?>
					<div class="available-item" style="text-align:center;">
						Non ci sono tour per la data e biglietti selezionati.
						Torna allo <a style="display:contents" href="./port-choice.php">step precedente</a> o alla <a style="display:contents" href="./index.php">home</a> per selezionare una nuova data.
					</div>
				  
				  <?php } 
				  
				} 
				  
			  } ?>
		  
		  
		  
        </div>
      </div>
    </div>

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

        var ComunePartenzaId = <?php echo $porto_partenza; ?>;
        var ComuneDestinazioneId = <?php echo $porto_destinazione; ?>;
        var DataFiltroA = $('.date').data('date');

        var TipoTour = <?php echo $tipoTour; ?>;
        var total_price = <?php if(isset($totalPrice)) { echo $totalPrice; } else { echo "0";} ?>;

        $('#back').click(function() {
			<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
				window.location.href = "./index.php";
			<?php } else { ?>
				window.location = "./ticket-prices.php";
			<?php } ?>
        })

        $(".ticket_form_class").submit(function(e) {
          console.log(e);
          e.preventDefault();
		  $.ajax({
			type: 'POST',
			url: "<?php echo $_SERVER['REQUEST_URI']; ?>", // Change to the actual filename of this page
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

// Funzione per rimuovere il testo tra parentesi tonde
function rimuoviParentesiComune($string) {
    // Espressione regolare per rimuovere qualsiasi testo tra parentesi (con spazi opzionali)
    return preg_replace('/\s*\(.*?\)\s*/i', '', $string);
}
?>