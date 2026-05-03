<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['area_clienti']['prenotazioni'];
$PageDescription = $dizionario['area_clienti']['descr'];
$PageKeywords = $dizionario['area_clienti']['key'];

$page_title = $dizionario['area_clienti']['prenotazioni'];
$page_parent = $dizionario['area_clienti']['descr'];

$LinkActive = 4;

if ( ! session_id() ) {
   session_start();
}

$userInfo = $_SESSION['USER'];

if(!isset($userInfo) || $userInfo == "" ){
    header('location: /area-clienti.php');
    die();
}

global $search;

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;

global $db;
$db = new Database();
$db->connect();

$sql = "SELECT p.PrenotazioneId, p.PrenotazioneStato as PrenotazioneStatoId, p.CodicePrenotazione, p.TotalePaxPrenotati, p.DataIns, p.TipoViaggioId, p.TipoTour, s.PrenotazioneStato, d.ComunePartenza, d.ComuneArrivo, d.DataInizioItinerario, p.PrenotazioneStato as IdStato FROM RT_Prenotazione p
left join RT_PrenotazioneDettaglio d on p.PrenotazioneId = d.Prenotazioneid
left join RT_AppPrenotazioneStato s on p.PrenotazioneStato = s.PrenotazioneStatoId
where p.MembershipClubCode = '".$userInfo['MembershipClubCode']."' and (p.PrenotazioneStato = 1 OR p.PrenotazioneStato = 3 OR p.PrenotazioneStato = 11 OR p.PrenotazioneStato = 4 OR p.PrenotazioneStato = 7  OR p.PrenotazioneStato = 2)
and d.TipoServizio = 'Bus' Group By p.PrenotazioneId";
 
$rows = $db->fetch_array($sql);
?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>
<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
		<div class="content">
			<div style="margin-bottom:10px;" class="benvenuto-user"><?=$dizionario['prenota']['benvenuto']?> <b><?php 
				if(isset($user)){
						echo $user['Nome'].' '.$user['CognomeRagioneSociale'];
				} else {
						echo $dizionario['prenota']['ospite'];
				} ?>
				</b>
				<?php
				if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1){
					echo '<br>'.$dizionario['prenota']['prenotazione_gestita_da'].'<b>'.$_SESSION['gestore']['RagioneSociale']."</b>";
				}
				?>
				</div>
			<div style="margin-bottom:10px;margin-top:0px;">
				<h3 style="margin-top:0px;"><b><?=$PageTitle?></b></h3>
			</div>
			<div class="ticket-list">
			
				<div class="filters">
				  <span><a href="/area-clienti.php"><?=$dizionario['area_clienti']['area']?></a> / <?=$dizionario['area_clienti']['prenotazioni']?></span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<div style="margin:15px !important;">
								<div class="row">
									<div class="col-md-8">
										<div class="ticket-list">
											<div class="filters">
												<span>Prenotazioni</span>	
											</div>
											<div class="allTour">
												<?php if (count($rows) > 0) {
													foreach ($rows as $t) {
														$dateTime = new DateTime($t['DataIns']);
														$formattedDate = $dateTime->format('d/m/Y');
														$formattedTime = $dateTime->format('H:i'); ?>
														<div class="ticket">
															<div class="info">
																<span class="title"><?= $t['CodicePrenotazione'] ?></span>
																<span><?php 
																$date = new DateTime($t['DataInizioItinerario']);
																echo $date->format('d/m/Y');?>
															</span>
															- 
															<span><?php if($t['TipoTour'] == 1) 
																			echo "Tour privato"; 
																		else
																			echo "Tour di gruppo" ?>
															</span>
															<br>
															<span><?php echo $t['ComunePartenza']?></span> - <span><?php echo $t['ComuneArrivo']?></span><br>
															<span><?php echo $t['PrenotazioneStato']?></span><br>
																			<span class="date"> - <?= $formattedDate . ' ore ' . $formattedTime ?></span>
															</div>
															<?php if($t['PrenotazioneStatoId'] == 3) { ?>
																<a href="/mostra-ticket.php?ticket=<?=$t['PrenotazioneId']?>"><img src="./css/imgs/edit.png" alt=""></a>
															<?php } ?>
														</div>

													<?php }
												} else { ?>
													<div class="ticket">
														<div class="info">
															<i>Nessun biglietto acquistato</i>
														</div>
													</div>
												<?php } ?>
											</div>
										</div>
									</div>
            				
									<div class="col-md-4">

											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/profilo.php'"><i class="fa fa-user"></i> <?=$dizionario['area_clienti']['profilo']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/mie-prenotazioni.php'"><i class="fa fa-ticket  "></i> <?=$dizionario['area_clienti']['prenotazioni']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/miei-coupon.php'"><i class="fa fa-ticket  "></i> <?=$dizionario['area_clienti']['coupon']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="logout-profilo"><i class="fa fa-sign-out"></i> <?=$dizionario['area_clienti']['logout']?></button>

										
									</div>
								</div>
							</div>
						</div>
					</div>
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

</html>