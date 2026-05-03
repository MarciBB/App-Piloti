<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath.'/main_include.php');

$PageTitle = $dizionario['ticket']['titolo'];
$PageDescription = $dizionario['ticket']['descr'];
$PageKeywords = $dizionario['ticket']['key'];

$page_title = "Home";

$LinkActive = 1;

if(isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];
} else {
	$user = null;
}

$db = new Database();
$db->connect();

if(!isset($_SESSION['tipo_tour'])) {
	// Effettua il redirect alla pagina index.php
	header("Location: /index.php");
	exit; // Termina lo script per evitare l'esecuzione di ulteriori istruzioni
}

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

  if (isset($_SESSION['BigliettoTipologiaPax'])) {
    $bigliettoTipologiaPax = $_SESSION['BigliettoTipologiaPax'];


	//calcolo posti totali selezionati ed il massimo del numero posti possibili in base al tipo di biglietto selezionato se è a tipo intervallo (tour privato)
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

  if (isset($_SESSION['TipoBigliettoId'])) {
    $tipoBigliettoId = $_SESSION['TipoBigliettoId'];
  }

  if (isset($_SESSION['passangerId'])) {
    $passangerId = $_SESSION['passangerId'];
    $ids = array_values($passangerId);
    $result = implode(', ', $ids);
  }
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
	$_SESSION['da_confermare'] = $_POST['da_confermare'];
	
  }

  include_once('../protected/modules/rt_biglietto/biglietto_action2.php');

  $comunePartenzaId = $porto_partenza;
  $comuneDestinazioneId = $porto_destinazione;
  $tp = 'A';
  $dataFiltroA =  $DataFiltroA;
  $dataFiltroR = $DataFiltroA;
  $corsaid = '';
  $dataCorsa = '';

  $result = getCorseOpt($comunePartenzaId, $comuneDestinazioneId, $tp, $dataFiltroA, $dataFiltroR, $corsaid, $dataCorsa, $tipoTour, $selezionaLesperienza);

  $prenotazione = new Prenotazione();
  $prenotazione->conn = $db;
?>

<!DOCTYPE html>
<html>
<head>
<?php 
include_once($basepath."/include/meta.php");

?>
</head>

<body class="main-bg" id="nuova-prenotazione-2">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>  

	
    <!-- Wrapper
	================================================== -->
	<div id="page" class="hfeed site fullwidth">
    	
        
        <div class="main-container">
			<div class="content">
				<div style="margin-bottom:10px;" class="benvenuto-plugin">
					<h2><?=$dizionario['prenota']['prenota_il_tuo_tour']?></h2>
					<p><?=$dizionario['prenota']['segui_passaggi']?></p>
				</div>
			<div class="info-bar">
			  <button class="btn btn-rounded btn-primary" id="back">
				<svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
				  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
				</svg>
			  </button>
			  <?php
					if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1){
						echo '<div class="center-info">';
						echo '<br>'.$dizionario['prenota']['prenotazione_gestita_da'].'<b>'.$_SESSION['gestore']['RagioneSociale']."</b>";
						echo '</div>';
					}
				?>
			  <div class="info">
				<?php if (isset($_POST['PrenotazioneId']) || isset($_SESSION['PrenotazioneId'])) { ?> 
					<span><?=$dizionario['prenota']['modifica_ticket']?></span>
				<?php } else { ?>
					<span><?=$dizionario['prenota']['nuovo_ticket']?></span>							
				<?php } ?>
				<div class="date" data-date="<?= $DataFiltroA; ?>"><?= date('M d, Y', strtotime($_SESSION['ticket_date'])) ?></div>
			  </div>
			</div>
			<div class="available-list">
				<div class="available-list-header hidden">
					<?=$dizionario['prenota']['seleziona_corsa']?>
				</div>
			  <?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
				<div class="available-item" style="text-align:center;">
					<?=$dizionario['prenota']['no_modifica_data_trascorsa']?>
					<br>
					<?=$dizionario['prenota']['torna_alla']?> <a style="display:contents" href="/index.php<?= $_SESSION['code_gestore'] ?>">home</a> <?=$dizionario['prenota']['per_altre_operazioni']?>
				</div>
			  <?php }  else { ?>
					
				  <?php
				  
				  if (json_decode($result)->iTotalRecords > 0) :
					$countCorse = 0;
					?>
					<div class="form-container">
						<div class="card-cta">
							<p><?=$dizionario['prenota']['seleziona_corsa']?>!</p>
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
						// 0: CorsaId
						// 1: FermataId
						// 2: Corsa nome
						// 3: Linea nome
						// 4: Data partenza formattata
						// 5: giorno settimana
						// 6: Orario partenza
						// 7: Posti totali
						// 8: Posti occupati						

						if($data_ora_attuale < $data_ora_spec && 
							($list[4] == $DataFiltroA && 
							(($tipoTour == 0 && $list[7] >= $postiTotali) || 
								($tipoTour == 1 && $list[8] == 0 && $list[7] >= $postiTotali))
							)
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
								$sql = "SELECT * FROM RT_CorsaTariffa WHERE  FermataPickup = $porto_partenza AND FermataDropOff = $porto_destinazione AND TipologiaBigliettoId = $key AND CorsaId = ".$list[0];
								
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
								$arrayCorseVisualizzate[] = $list[6];
								$countCorse++;
							
							//calcolo orari e durata
							  $sql = "SELECT * FROM RT_Orario o 
								  LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId WHERE CorsaId = $list[0] 
								  AND (f.ComuneId = $porto_partenza OR f.ComuneId = $porto_destinazione) 
								  AND o.Stato = 1 
								  AND o.Cancella = 0
								  AND f.Cancella = 0 AND f.Stato = 1
								  order by f.FermataPeso asc";
							
							  $arrData = $db->fetch_array($sql);
							  $time1 = new DateTime($arrData[0]['Orario']);
							  $time2 = new DateTime($arrData[1]['Orario']);
							  $timeDifference = $time1->diff($time2);
							 
							  $formattedDifference = sprintf(
								'%dh %dm',
								$timeDifference->h + $timeDifference->days * 24, // Include days in hours
								$timeDifference->i
							  );

							//calcolo fermata da confermare
							$sql = "SELECT f.* FROM RT_Fermata f
										LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId
										WHERE t.LineaId = $selezionaLesperienza AND f.Stato = 1 AND f.Cancella = 0
										AND f.IsDaConfermare = 1
										AND (f.ComuneId = $porto_partenza OR f.ComuneId = $porto_destinazione)
										GROUP BY f.ComuneId";
							$arrData2 = $db->fetch_array($sql);
							$daConfermare = false;
							if(count($arrData2) > 0) {
								$daConfermare = true;
							}

					  ?>
						
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
								  <input type="hidden" value="<?= $daConfermare ?>" name="da_confermare">

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
								  <div class="time"><?= date('H:i', strtotime($arrData[1]['Orario'])) ?></div>
								  <div class="location"><?= $porto_destinazione_nome ?> - <?= $arrData[1]['FermataNome'] ?></div>
								</div>
							  </div>
							  <hr />
							  <div class="actions">

							   <?php if($daConfermare) { ?>
									<div class="note">
										<i><?=$dizionario['prenota']['prenotazione_da_confermare_tour']?></i>
									</div>
							    <?php } ?>
							  		
								<div class="price"> <img src="/images/user.png" alt="" /> <span><?= number_format($totalPrice, 2, ',', '.'); ?>€ tot.</span> </div>

								<div class="buttons next">
								  <!-- <?= $list[1] ?> -->
								  <button type="submit" class="btn btn-secondary buttons-label" name="submit"><?= (isset($_SESSION['CorsaId']) && $list[0] == $_SESSION['CorsaId']) ? $dizionario['prenota']['partenza_selezionata'] : $dizionario['prenota']['seleziona_partenza'];?></button>
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
				  
				  if($countCorse == 0) { ?>
					<div class="available-item" style="text-align:center;">
						<?=$dizionario['prenota']['no_tour_data']?><br>
						<?=$dizionario['prenota']['torna_allo']?> <a style="display:contents" href="/prenota/3.php"><?=$dizionario['prenota']['step_precedente']?></a> <?=$dizionario['prenota']['o_alla']?> <a style="display:contents" href="/index.php<?= $_SESSION['code_gestore'] ?>">home</a> <?=$dizionario['prenota']['per_selezionare_data']?>.
					</div>
				  
				  <?php } 
			  } ?>
			  
			</div>
		  </div>
		</div>
        
        
        
            

		<!-- Bottom
		================================================== -->
		<?php include_once($basepath."/include/bottom.php"); ?>
        	 
        
        <!-- Footer
        ================================================== -->
        <?php include_once($basepath."/include/footer.php"); ?>    
        
    </div>
    <!-- #page -->
  
   	<?php include_once($basepath."/include/html_close.php"); ?>   

</body>
<script type="text/javascript">
      $(document).ready(function() {
        var ComunePartenzaId = <?php echo $porto_partenza; ?>;
        var ComuneDestinazioneId = <?php echo $porto_destinazione; ?>;
        var DataFiltroA = $('.date').data('date');
        // echo $tipo_viaggio.'</br>';
        var TipoTour = <?php echo $tipoTour; ?>;
        var total_price = <?php if(isset($totalPrice)) { echo $totalPrice; } else { echo "0";} ?>;

        $('#back').click(function() {
			<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
				window.location.href = "/index.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
			<?php } else { ?>
				<?php if($_SESSION['Skip3Step'] == 0) { ?>
					window.location = "/prenota/3.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
				<?php } else { ?>
					window.location = "/prenota/2.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
				<?php } ?>
			<?php } ?>
        })

        $(".ticket_form_class").submit(function(e) {
          e.preventDefault();
    
          // Recupera i dati dal form corrente
          var formData = $(this);
          var corsaId = formData.find('input[name="CorsaId"]').val();
          var totalPrice = parseFloat(formData.find('input[name="total_price"]').val());
          var pickup = formData.find('input[name="Pickup"]').val();
          var dropoff = formData.find('input[name="Dropoff"]').val();
          var startTime = formData.find('input[name="start_time"]').val();
          var endTime = formData.find('input[name="end_time"]').val();
          var location = formData.find('input[name="location"]').val();

          // Invio evento a dataLayer
          if (typeof dataLayer !== 'undefined') {
              dataLayer.push({
                  event: "add_to_cart",
                  ecommerce: {
                      currency: "EUR",
                      value: totalPrice,
                      items: [{
                          item_id: "BOAT_<?= $_SESSION['tipo_tour'] == 1 ? 'PRIVATE' : 'GROUP' ?>_" + corsaId,
                          item_name: "<?php 
                              $sql = "SELECT LineaNome FROM RT_Linea WHERE LineaId = " . $_SESSION['seleziona_lesperienza'];
                              $linea = $db->query_first($sql);
                              echo addslashes($linea['LineaNome']);
                          ?>",
                          affiliation: "Bertoldi Boats",
                          item_brand: "Bertoldi Boats",
                          item_category: "Tour Barca",
                          item_category2: "<?= $_SESSION['tipo_tour'] == 1 ? 'Tour Privato' : 'Tour Gruppo' ?>",
                          item_category3: "<?php
                              if($_SESSION['tipo_viaggio'] == 1) {
                                  echo 'Solo Andata';
                              } else if($_SESSION['tipo_viaggio'] == 2) {
                                  echo 'Andata e Ritorno';
                              } else {
                                  echo 'Tour';
                              }
                          ?>",
                          item_category4: location,
                          location_id: "lago_garda",
                          price: totalPrice,
                          quantity: <?= isset($postiTotali) ? intval($postiTotali) : 1 ?>,
                          custom_parameters: {
                              corsa_id: corsaId,
                              tour_date: "<?= $_SESSION['ticket_date'] ?>",
                              tour_type: "<?= $_SESSION['tipo_tour'] == 1 ? 'private' : 'group' ?>",
                              travel_type: "<?= $_SESSION['tipo_viaggio'] ?>",
                              porto_partenza: "<?= $_SESSION['porto_partenza_nome'] ?>",
                              porto_destinazione: "<?= $_SESSION['porto_destinazione_nome'] ?>",
                              pickup_location: pickup,
                              dropoff_location: dropoff,
                              start_time: startTime,
                              end_time: endTime,
                              passengers: <?= isset($postiTotali) ? intval($postiTotali) : 1 ?>,
                          }
                      }]
                  }
              });
          }

          $.ajax({
              type: 'POST',
              url: "<?php echo $_SERVER['REQUEST_URI']; ?>",
              data: $(this).serialize(),
              success: function(response) {
                  <?php if($tipo_viaggio == 1) { ?>
                      window.location = "/prenota/6.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
                  <?php } else { ?>
                      window.location = "/prenota/5.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
                  <?php } ?>
              }
          });
        });
       
      });
    </script>
</html>
