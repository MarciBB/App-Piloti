<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link
	rel="stylesheet" href="/css/home.css" type="text/css" />
	
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Lingua.php");

$ModuloId=1;

function show_list()
{
	global $user,$HtmlCommon,$db,$dizionario;
	//$HtmlCommon->html_titolo_pagina("Dashboard", 0, "", "");
	//$HtmlCommon->html_titolo_box("Dashboard");
	$db = new Database();
	$db->connect();

	$dt = new DT();
	
	$gestore=new Gestore();
	$gestore->conn=$db;
	$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
	$InGestoreFigli=implode(",", $gestorefigli);

	$permesso = $user->ControllModuloFunzionePermesso(28,1);
	
	$lineaId = array();
	if(!isset($_POST['lineaid'])){
		$sql = "SELECT LineaId FROM RT_Linea
				LEFT JOIN Gestore ON (RT_Linea.GestoreGruppoId = Gestore.GestoreGruppoId)
				WHERE Gestore.GestoreId = $user->GestoreId";
		$lineaId = $db->query_first($sql);
	} else {
		$linea_post = $_POST['lineaid'];
		$lineaId['LineaId'] = $linea_post;
	}
	
	//array delle barche della flotta
	$sqlBarche = "SELECT * FROM RT_Flotta WHERE Cancella = 0 AND Stato = 1 ORDER BY Modello ASC";
	$barche = $db->fetch_array($sqlBarche);
	
	?>
	<style>
	.filter {
		margin-right: 8px;
		border: none;
		padding: 4px 9px;
		border-radius: 16px;
		background-color: #fff;
		margin-left: 1rem;
	}
	</style>
<div id="LeftSideHp"> 
		
	<div class="main-container">
		<div class="content">
		  <div class="filter-row">
			<div class="mode">Modalità di Visualizzazione</div>
			<div class="btns">
			  <button class="btn btn-primary" onclick="giornaliera();" id="giornoButton">Giornaliera</button>
			  <button class="btn btn-outline-gray" onclick="settimanale();" id="settimanaButton">Settimanale</button>
			  <button class="btn btn-outline-gray" onclick="mensile();" id="meseButton">Mensile</button>
			  <button class="btn btn-outline-gray" onclick="libera();" id="liberaButton">Seleziona date</button>
			  
			</div>
			
		  </div>
		   
		  <div class="filter-row">
			<div class="mode" style="display:flex;">
				<div class="form">
					<label for="barca">Barca</label>
					<select id="barca" class="filter" onchange="aggiornaGrafi();">
						<option value="">Tutti</option>
						<?php foreach($barche as $b) { ?>
							<option value="<?=$b['FlottaId']?>"><?=$b['Modello']?></option>
						<?php } ?>
					</select>
				</div>
				<div class="form">
					<label for="tipoNoleggio">Tipo Noleggio</label>
					<select id="tipoNoleggio" class="filter" onchange="aggiornaGrafi();">
						<option value="">Tutti</option>
						<option value="0">Noleggio con conducente</option>
						<option value="1">Noleggio con licenza</option>
						<option value="2">Exlusive</option>
					</select>
				</div>
				<div class="form">
					<label for="areaLavoro">Area Lavoro</label>
					<select id="areaLavoro" class="filter" onchange="aggiornaGrafi();">
						<option value="">Tutti</option>
						<option value="0">Sirmione</option>
						<option value="1">Desenzano</option>
					</select>
				</div>
			
			</div>
			<div class="btns">
			  <div id="dateLibera" class="btns" style="display:none;">
				Da <input type="date" id="daLibera" class="filter" value="<?=date('Y')."-01-01"?>" onchange="libera();">
				A <input type="date" id="aLibera" class="filter" value="<?= date('Y')."-12-31"?>" onchange="libera();">
			  </div>
			   
			</div>
			
		  </div>
		  
		  <div class="stats-row">
			<div class="stat-card">
			  <div class="stat-info">
				<div class="title">
				  <div class="label">Entrate</div>
				  <div class="value"><span id="entrateValue">-</span>&euro;</div>
				  <div class=""><small style="color:red;">Anno precedente: <span id="entrateValuePrev" >-</span>&euro;</small></div>
				</div>
				<div class="icon">
				  <img src="/images/icon-entrare.png">
				</div>
			  </div>
			  <hr>
			  <div class="action-row">
				<a href="javascript:void(0);" onclick="loadMainContentFromMenu('rt_contabilita','stat_venduto_provvigioni.php','3');">
				  <span>Vedi tutte le Entrate</span>
				  <img src="/images/arrow-white.png" alt="">
				</a>
			  </div>
			</div>
			<div class="stat-card">
			  <div class="stat-info">
				<div class="title">
				  <div class="label">Prenotazioni</div>
				  <div class="value"><span id="prenotazioniValue">-</span></div>
				  <div class=""><small style="color:red;">Anno precedente: <span id="prenotazioniValuePrev" >-</span></small></div>
				</div>
				<div class="icon">
				  <img src="/images/icon-prenotazioni.png">
				</div>
			  </div>
			  <hr>
			  <div class="action-row">
				<a href="javascript:void(0);" onclick="loadMainContentFromMenu('rt_biglietto','biglietto.php','1');">
				  <span>Vedi tutte le Prenotazioni</span>
				  <img src="/images/arrow-white.png" alt="">
				</a>
			  </div>
			</div>
			<?php if($user->GestoreId == 1) { ?>
				<div class="stat-card">
				  <div class="stat-info">
					<div class="title">
					  <div class="label">Tour</div>
					  <div class="value"><span id="tourValue">-</span></div>
					  <div class=""><small style="color:red;">Anno precedente: <span id="tourValuePrev" >-</span></small></div>
					  
					</div>
					<div class="icon">
					  <img src="/images/icon-barche.png">
					</div>
				  </div>
				  <hr>
				  <div class="action-row">
					<a href="javascript:void(0);" onclick="loadMainContentFromMenu('rt_corsapartenza','corsapartenza.php','6');">
					  <span>Vedi tutti i Tour</span>
					  <img src="/images/arrow-white.png" alt="">
					</a>
				  </div>
				</div>
			<?php } ?>
		  </div>
		  <div class="graph-row">
			<div class="graph-card">
			  <div class="title">
				<span>Statistiche</span>
				<a href="javascript:void(0);" onclick="loadMainContentFromMenu('rt_contabilita','stat_corrispettivi.php','3');">
				  Vai a Statistiche
				  <img src="/images/arrow.png" alt="">
				</a>
			  </div>
			  <div class="graph-container" id="graph-container">
				<canvas id="myChart" width="400" height="200"></canvas>
			  </div>
			</div>
		</div>
	  </div>	
		
		
	<?php if($user->GestoreId == 1) { ?>
	
	<div class="detail-row">
		<div class="graph-card">
		  <div class="title">
			<span>Dettagli Tour</span>
			<a href="javascript:void(0);" onclick="loadMainContentFromMenu('rt_corsapartenza','corsapartenza.php','6');">
			  Vai a Gestione
			  <img src="/images/arrow.png" alt="">
			</a>
		  </div>
	<div>
			
	
		<div class="boxHP" id="boxMediazioniHP">
			<?php
			if($user->IsAdmin == 1){
				$sql = "Select LineaId, LineaNome
						FROM RT_Linea
						WHERE GestoreGruppoId is not null";
				$linee = $db->fetch_array($sql);
				?>
				<?=$dizionario['home']['seleziona_linea']?>:
				<select id="LineaidSelection">
					<?php foreach ($linee as $l){
						if($lineaId['LineaId'] == $l['LineaId']){
							echo "<option value=".$l['LineaId']." selected='selected'>".$l['LineaNome']."</option>";
						} else {
							echo "<option value=".$l['LineaId'].">".$l['LineaNome']."</option>";
						}
					}?>
				</select>
				<script type="text/javascript">
					$( document ).ready(function() {
						$('#LineaidSelection').change(function(data){
							page_to_load="/protected/modules/home/home.php";
							data = {};
							data['lineaid'] =  this.value;
							$('#brain_loading').css('display','inline');
							$.ajax({
								type: "POST",
								url: page_to_load,
								data: data,
								success: function(data) {
									$('#brain_main-content').html(data);
									$('#brain_loading').css('display','none');
								}
							});
						});
					});
				</script>
				<br><br>
				<?php 
			}
			?>
		
		
			<?php if($user->GestoreId == 1 || $user->GestoreId == 2){?>
			<div class="box_left">
			
				<h1 class="boxTitolo" style="color: #174570 !important"><i class="fa fa-ship" aria-hidden="true"></i> <?=$dizionario['home']['modifiche_successive_preparazione']?></h1>
				<div class="boxContentHP">
					<table class="tabella_modulo">
						<thead>
							<tr>
								<td><?=$dizionario['generale']['linea']?></td>
								<td><?=$dizionario['generale']['corsa']?></td>
								<td><?=$dizionario['generale']['data_modifica']?></td>
								<td><?=$dizionario['generale']['tipo_modifica']?></td>
								<td>&nbsp;</td>
							</tr>
						</thead>
						<tbody>
							<?php
							
							$sql="SELECT t2.*
									FROM (SELECT DISTINCT MAX(p.DataAgg) DataAgg, pd.DataInizioItinerario
									FROM RT_Prenotazione p INNER JOIN RT_PrenotazioneDettaglio pd ON (p.PrenotazioneId = pd.PrenotazioneId)
									INNER JOIN (SELECT DISTINCT gop.LineaId, gop.CorsaId, gop.CorsaDataPartenza, gop.DataIns FROM RT_GestioneOttimizzataPasseggeri gop) i ON (pd.LineaId = i.LineaId AND pd.CorsaId = i.CorsaId AND pd.DataInizioItinerario = i.CorsaDataPartenza)
									INNER JOIN RT_AppPrenotazioneStato aps ON (p.PrenotazioneStato = aps.PrenotazioneStatoId)
									WHERE p.DataAgg > i.DataIns AND p.OdcIdRef = $user->OdcId AND p.GestoreIdRef = $user->GestoreId
									GROUP BY pd.CorsaId, pd.DataInizioItinerario) t1
									INNER JOIN (SELECT DISTINCT p.DataAgg, pd.LineaNome, pd.CorsaId, pd.CorsaNome, pd.DataInizioItinerario, aps.PrenotazioneStatoId, aps.PrenotazioneStato
									FROM RT_Prenotazione p INNER JOIN RT_PrenotazioneDettaglio pd ON (p.PrenotazioneId = pd.PrenotazioneId)
									INNER JOIN (SELECT DISTINCT gop.LineaId, gop.CorsaId, gop.CorsaDataPartenza, gop.DataIns FROM RT_GestioneOttimizzataPasseggeri gop) i ON (pd.LineaId = i.LineaId AND pd.CorsaId = i.CorsaId AND pd.DataInizioItinerario = i.CorsaDataPartenza)
									INNER JOIN RT_AppPrenotazioneStato aps ON (p.PrenotazioneStato = aps.PrenotazioneStatoId)
									WHERE p.DataAgg > i.DataIns AND p.OdcIdRef = $user->OdcId AND p.GestoreIdRef = $user->GestoreId and pd.LineaId = ".$lineaId['LineaId']." ) t2 on t2.DataInizioItinerario=t1.DataInizioItinerario and t1.DataAgg=t2.DataAgg";
							   
							$ArrObject = $db->fetch_array($sql);
							$c=0;

							while($c<sizeof($ArrObject))
							{
								$lineanome=$ArrObject[$c]['LineaNome'];
								$corsaanome=$ArrObject[$c]['CorsaNome'];
								$del=$ArrObject[$c]['DataInizioItinerario'];
								$data=$dt->format($ArrObject[$c]['DataAgg'], "Y-m-d H:i:s", "d/m/Y H:i:s");
								
								switch ($ArrObject[$c]['PrenotazioneStatoId']) {
									case 1:
										$PrenotazioneStato = "Agg. prenot.";
									break;
									
									case 3:
										$PrenotazioneStato = "Eme. biglietto";
									break;
									
									case 4:
										$PrenotazioneStato = "Ann. prenot.";
									break;
										
									case 6:
										$PrenotazioneStato = "Sost. prenot.";
									break;
									
									default:
										$PrenotazioneStato = $ArrObject[$c]['PrenotazioneStato'];
									break;
								}
								
						  if(($c%2)==0){
							$odd_even = "odd";
						  }
						  else {
							$odd_even = "even";
						  }
						  ?>
							<tr class="<?=$odd_even?>">
								<td><?=$lineanome?></td>
								<td><?=$del?></td>
								<td><?=$data?></td>
								<td><strong><?=$PrenotazioneStato?></strong>
								<td>
									<a title="edita" onclick="loadMainContent('rt_previaggio','previaggio.php?do=GestionePreViagigo&DataPartenza=<?php echo $del; ?>&CorsaId=<?php echo $ArrObject[$c]['CorsaId']; ?>',this);" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" title="pre viaggio" alt="pre viaggio"></i></a>
								</td>
								</td>
							</tr>
							<?
							$c++;
							}
							?>
						</tbody>
					</table>

				</div>
			</div>
			<?php } ?>
			
			<?php if($user->GestoreId == 1 || $user->GestoreId == 2){?>
			<div class="box_right">
				<h1 class="boxTitolo"><i class="fa fa-ship" aria-hidden="true"></i> <?=$dizionario['home']['corse_consolidare']?></h1>
				<div class="boxContentHP">
					<table class="tabella_modulo">
						<thead>
							<tr>
								<td><?=$dizionario['generale']['linea']?></td>
								<td><?=$dizionario['generale']['data']?></td>
								<td><?=$dizionario['generale']['ora']?></td>
							</tr>
						</thead>
						<tbody>
							<?
							$sql = "SELECT 
							rt_viewelencogestioneoperativita.LineaNome AS LineaNome,
							rt_viewelencogestioneoperativita.DataPartenzaFormattata AS DataPartenzaFormattata,
							rt_viewelencogestioneoperativita.AppCalendarioData AS AppCalendarioData,
							rt_viewelencogestioneoperativita.OrarioPartenza AS OrarioPartenza,
							rt_viewelencogestioneoperativita.DataOraPartenza AS DataOraPartenza,
							rt_viewelencogestioneoperativita.DataConsolidamento AS DataConsolidamento,
							rt_viewelencogestioneoperativita.DataInizializzazione AS DataInizializzazione,
							rt_viewelencogestioneoperativita.OdcIdRef AS OdcIdRef,
							rt_viewelencogestioneoperativita.CorsaId AS CorsaId,
							rt_viewelencogestioneoperativita.CorsaBloccata AS CorsaBloccata,
							rt_viewelencogestioneoperativita.PostiCorsaPrenotati AS PostiCorsaPrenotati 
							FROM (
								SELECT 
								rt_viewoperativitablocchi.PercorsoNome AS PercorsoNome,
								rt_viewoperativitablocchi.LineaArea AS LineaArea,
								rt_viewoperativitablocchi.LineaNome AS LineaNome,
								rt_viewoperativitablocchi.CorsaNome AS CorsaNome,
								rt_viewoperativitablocchi.AppSettimanaGiorno AS AppSettimanaGiorno,
								rt_viewoperativitablocchi.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
								rt_viewoperativitablocchi.AttivaDal AS AttivaDal,
								rt_viewoperativitablocchi.AttivaAl AS AttivaAl,
								rt_viewoperativitablocchi.AppCalendarioData AS AppCalendarioData,
								rt_viewoperativitablocchi.IncludiFeriale AS IncludiFeriale,
								rt_viewoperativitablocchi.IncludiPrefestivo AS IncludiPrefestivo,
								rt_viewoperativitablocchi.IncludiFestivo AS IncludiFestivo,
								rt_viewoperativitablocchi.Feriale AS Feriale,
								rt_viewoperativitablocchi.Prefestivo AS Prefestivo,
								rt_viewoperativitablocchi.Festivo AS Festivo,
								rt_viewoperativitablocchi.PercorsoId AS PercorsoId,
								rt_viewoperativitablocchi.LineaId AS LineaId,
								rt_viewoperativitablocchi.CorsaId AS CorsaId,
								rt_viewoperativitablocchi.OdcIdRef AS OdcIdRef,
								rt_viewoperativitablocchi.OrarioPartenza AS OrarioPartenza,
								rt_viewoperativitablocchi.CorsaBloccoId AS CorsaBloccoId,
								rt_viewoperativitablocchi.CorsaBloccata AS CorsaBloccata,
								rt_viewoperativitablocchi.DataPartenzaFormattata AS DataPartenzaFormattata,
								rt_viewoperativitablocchi.PostiCorsaDefault AS PostiCorsaDefault,
								rt_viewoperativitablocchi.PostiCorsaAggiunti AS PostiCorsaAggiunti,
								rt_viewoperativitablocchi.PostiCorsaPrenotati AS PostiCorsaPrenotati,
								rt_viewoperativitablocchi.PostiTotali AS PostiTotali,
								rt_viewoperativitablocchi.PostiDisponibili AS PostiDisponibili,
								rt_viewoperativitablocchi.VendibileDal AS VendibileDal,
								rt_viewoperativitablocchi.VendibileAl AS VendibileAl,
								rt_viewoperativitablocchi.OrePrimaStopVendita AS OrePrimaStopVendita,
								rt_viewoperativitablocchi.DataOraPartenza AS DataOraPartenza,
								rt_viewoperativitablocchi.OreMancanti AS OreMancanti,
								rt_viewoperativitablocchi.TipologiaBusDefaultId AS TipologiaBusDefaultId,
								rt_corsaconsolidamento.DataIns AS DataConsolidamento,
								rt_corsainiziopreparazione.DataIns AS DataInizializzazione,
								IF((rt_corsaconsolidamento.DataIns IS NOT NULL), DATE_FORMAT(rt_corsaconsolidamento.DataIns, _utf8 '%d/%m/%Y %H:%i:%s'), _utf8 '') AS DataConsolidamentoF,
								IF((rt_corsainiziopreparazione.DataIns IS NOT NULL), DATE_FORMAT(rt_corsainiziopreparazione.DataIns, _utf8 '%d/%m/%Y %H:%i:%s'), _utf8 '') AS DataInizializzazioneF,
								IF(ISNULL(rt_corsabloccoweb.CorsaBloccoId), 0, 1) AS CorsaWebBloccata,
								rt_viewoperativitablocchi.PostiRealmenteDisponibili AS PostiRealmenteDisponibili,
								rt_viewoperativitablocchi.PostiRealmentePrenotati AS PostiRealmentePrenotati 
								FROM (
									SELECT 
									rt_viewoperativitablocchi01.PercorsoNome AS PercorsoNome,
									rt_viewoperativitablocchi01.LineaArea AS LineaArea,
									rt_viewoperativitablocchi01.LineaNome AS LineaNome,
									rt_viewoperativitablocchi01.CorsaNome AS CorsaNome,
									rt_viewoperativitablocchi01.AppSettimanaGiorno AS AppSettimanaGiorno,
									rt_viewoperativitablocchi01.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
									rt_viewoperativitablocchi01.AttivaDal AS AttivaDal,
									rt_viewoperativitablocchi01.AttivaAl AS AttivaAl,
									rt_viewoperativitablocchi01.AppCalendarioData AS AppCalendarioData,
									rt_viewoperativitablocchi01.IncludiFeriale AS IncludiFeriale,
									rt_viewoperativitablocchi01.IncludiPrefestivo AS IncludiPrefestivo,
									rt_viewoperativitablocchi01.IncludiFestivo AS IncludiFestivo,
									rt_viewoperativitablocchi01.Feriale AS Feriale,
									rt_viewoperativitablocchi01.Prefestivo AS Prefestivo,
									rt_viewoperativitablocchi01.Festivo AS Festivo,
									rt_viewoperativitablocchi01.PercorsoId AS PercorsoId,
									rt_viewoperativitablocchi01.LineaId AS LineaId,
									rt_viewoperativitablocchi01.CorsaId AS CorsaId,
									rt_viewoperativitablocchi01.OdcIdRef AS OdcIdRef,
									rt_viewoperativitablocchi01.OrarioPartenza AS OrarioPartenza,
									rt_corsablocco.CorsaBloccoId AS CorsaBloccoId,
									IF(ISNULL(rt_corsablocco.CorsaBloccoId), 0, 1) AS CorsaBloccata,
									rt_viewoperativitablocchi01.DataPartenzaFormattata AS DataPartenzaFormattata,
									rt_viewoperativitablocchi01.PostiCorsaDefault AS PostiCorsaDefault,
									rt_viewoperativitablocchi01.PostiCorsaAggiunti AS PostiCorsaAggiunti,
									rt_viewoperativitablocchi01.PostiCorsaPrenotati AS PostiCorsaPrenotati,
									(rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) AS PostiTotali,
									((rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) - rt_viewoperativitablocchi01.PostiCorsaPrenotati) AS PostiDisponibili,
									((rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) - rt_viewoperativitablocchi01.PostiRealmentePrenotati) AS PostiRealmenteDisponibili,
									rt_viewoperativitablocchi01.VendibileDal AS VendibileDal,
									rt_viewoperativitablocchi01.VendibileAl AS VendibileAl,
									rt_viewoperativitablocchi01.OrePrimaStopVendita AS OrePrimaStopVendita,
									rt_viewoperativitablocchi01.DataOraPartenza AS DataOraPartenza,
									rt_viewoperativitablocchi01.OreMancanti AS OreMancanti,
									rt_viewoperativitablocchi01.TipologiaBusDefaultId AS TipologiaBusDefaultId,
									rt_corsabloccoweb.CorsaBloccoId AS CorsaBloccoWebId,
									rt_viewoperativitablocchi01.PostiRealmentePrenotati AS PostiRealmentePrenotati 
									FROM (
										SELECT 
										rt_percorso.PercorsoNome AS PercorsoNome,
										rt_linea.LineaArea AS LineaArea,
										rt_linea.LineaNome AS LineaNome,
										rt_corsa.CorsaNome AS CorsaNome,
										rt_appsettimana.AppSettimanaGiorno AS AppSettimanaGiorno,
										rt_appsettimana.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
										rt_corsa.AttivaDal AS AttivaDal,
										rt_corsa.AttivaAl AS AttivaAl,
										rt_appcalendario.AppCalendarioData AS AppCalendarioData,
										DATE_FORMAT(rt_appcalendario.AppCalendarioData, _utf8 '%d/%m/%Y') AS DataPartenzaFormattata,
										rt_corsa.IncludiFeriale AS IncludiFeriale,
										rt_corsa.IncludiPrefestivo AS IncludiPrefestivo,
										rt_corsa.IncludiFestivo AS IncludiFestivo,
										rt_appcalendario.Feriale AS Feriale,
										rt_appcalendario.Prefestivo AS Prefestivo,
										rt_appcalendario.Festivo AS Festivo,
										rt_percorso.PercorsoId AS PercorsoId,
										rt_linea.LineaId AS LineaId,
										rt_corsa.CorsaId AS CorsaId,
										rt_percorso.OdcIdRef AS OdcIdRef,
										rt_corsa.OrarioPartenza AS OrarioPartenza,
										rt_corsa.VendibileDal AS VendibileDal,
										rt_corsa.VendibileAl AS VendibileAl,
										rt_corsa.OrePrimaStopVendita AS OrePrimaStopVendita,
										ADDTIME(rt_appcalendario.AppCalendarioData, rt_corsa.OrarioPartenza) AS DataOraPartenza,
										TIMEDIFF(ADDTIME(rt_appcalendario.AppCalendarioData, rt_corsa.OrarioPartenza), NOW()) AS OreMancanti,
										rt_corsa.TipologiaBusDefaultId AS TipologiaBusDefaultId,
										rt_tipologiabus.TotalePosti AS PostiCorsaDefault,
										IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti), 0, rt_viewsingolacorsapostiaggiunti.PostiAggiunti) AS PostiCorsaAggiunti,
										IF(isnull(rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati), 0, rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati) AS PostiCorsaPrenotati,
										rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati AS PostiRealmentePrenotati 
										FROM RT_Percorso AS rt_percorso 
										LEFT JOIN RT_Linea AS rt_linea ON (rt_percorso.PercorsoId = rt_linea.PercorsoId) 
										LEFT JOIN RT_Corsa AS rt_corsa ON (rt_linea.LineaId = rt_corsa.LineaId) 
										LEFT JOIN RT_CorsaSettimana AS rt_corsasettimana ON (rt_corsa.CorsaId = rt_corsasettimana.CorsaId) 
										LEFT JOIN RT_AppSettimana AS rt_appsettimana ON (rt_corsasettimana.SettimanaId = rt_appsettimana.AppSettimanaId) 
										LEFT JOIN RT_AppCalendario AS rt_appcalendario ON (rt_appsettimana.AppSettimanaGiorno = rt_appcalendario.GiornoSettimana)
										LEFT JOIN RT_TipologiaBus AS rt_tipologiabus ON (rt_corsa.TipologiaBusDefaultId = rt_tipologiabus.TipologiaBusId)
										LEFT JOIN (
											SELECT 
											rt_corsapax.CorsaId AS CorsaId,
											rt_corsapax.DataPartenza AS DataPartenza,
											SUM(rt_corsapax.NumeroPax) AS PostiAggiunti,
											rt_corsapax.OdcIdRef AS OdcIdRef 
											FROM RT_CorsaPax AS rt_corsapax 
											WHERE rt_corsapax.Cancella = 0 
											GROUP BY rt_corsapax.CorsaId, rt_corsapax.DataPartenza, rt_corsapax.OdcIdRef
										) AS rt_viewsingolacorsapostiaggiunti ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiaggiunti.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostiaggiunti.DataPartenza) 
										LEFT JOIN (
											SELECT 
											rt_prenotazionepercorso.CorsaId AS CorsaId,
											rt_prenotazionepercorso.CorsaDataPartenza AS CorsaDataPartenza,
											(COUNT(rt_prenotazione.PrenotazioneId) - SUM(rt_prenotazionedettaglio.Escludi)) AS TotalePaxPrenotati,
											rt_prenotazione.OdcIdRef AS OdcIdRef 
											FROM RT_PrenotazionePercorso AS rt_prenotazionepercorso 
											JOIN RT_Prenotazione AS rt_prenotazione ON (rt_prenotazionepercorso.PrenotazioneId = rt_prenotazione.PrenotazioneId)
											JOIN RT_PrenotazioneDettaglio AS rt_prenotazionedettaglio ON (rt_prenotazionepercorso.PrenotazioneId = rt_prenotazionedettaglio.PrenotazioneId AND rt_prenotazionedettaglio.ComunePartenza = rt_prenotazionepercorso.ComuneSalita AND rt_prenotazionedettaglio.PrenotazioneId = rt_prenotazione.PrenotazioneId) 
											JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazionepercorso.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId) 
											WHERE rt_prenotazione.Cancella = 0
											AND rt_prenotazionepercorso.Cancella = 0
											AND rt_prenotazionepercorso.Stato = 1
											AND rt_appprenotazionestato.OccupaPosti = 1
											AND (
												(rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazionedettaglio.Escludi = 0) 
												OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 1)
												OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 0 AND rt_prenotazione.ScadenzaPrenotazione > rt_prenotazionedettaglio.DataPartenza)
												OR (rt_prenotazione.ABordo = 1 AND rt_prenotazione.PrenotazioneStato <> 6 AND rt_prenotazione.PrenotazioneStato <> 4 AND rt_prenotazione.PrenotazioneStato <> 7 AND rt_prenotazione.PrenotazioneStato <> 3)
												OR (rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazione.ABordo = 1 AND rt_prenotazionedettaglio.Escludi = 0)
											)
											GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
										) AS rt_viewsingolacorsapostiprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiprenotati.CorsaId AND rt_viewsingolacorsapostiprenotati.CorsaDataPartenza = rt_appcalendario.AppCalendarioData) 
										LEFT JOIN (
											SELECT 
											rt_prenotazionepercorso.CorsaId AS CorsaId,
											rt_prenotazionepercorso.CorsaDataPartenza AS CorsaDataPartenza,
											SUM(rt_prenotazione.TotalePaxPrenotati - rt_prenotazionepercorso.PasseggeriEsclusi) AS TotalePaxPrenotati,
											rt_prenotazione.OdcIdRef AS OdcIdRef 
											FROM RT_Prenotazione AS rt_prenotazione 
											JOIN RT_PrenotazionePercorso AS rt_prenotazionepercorso ON (rt_prenotazione.PrenotazioneId = rt_prenotazionepercorso.PrenotazioneId)
											JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazionepercorso.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId)
											WHERE rt_prenotazione.Cancella = 0
											AND rt_prenotazionepercorso.Cancella = 0
											AND rt_prenotazionepercorso.Stato = 1
											AND rt_appprenotazionestato.OccupaPosti = 1
											GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
										) AS rt_viewsingolacorsapostirealmenteprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostirealmenteprenotati.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostirealmenteprenotati.CorsaDataPartenza) 
										WHERE rt_percorso.Stato = 1
										AND rt_percorso.Cancella = 0
										AND rt_linea.Stato = 1
										AND rt_linea.Cancella = 0
										AND rt_corsa.Stato = 1
										AND rt_corsa.Cancella = 0
										AND rt_appcalendario.AppCalendarioData >= rt_corsa.AttivaDal
										AND rt_appcalendario.AppCalendarioData <= rt_corsa.AttivaAl
										AND (rt_appcalendario.Feriale = rt_corsa.IncludiFeriale OR rt_appcalendario.Prefestivo = rt_corsa.IncludiPrefestivo OR rt_appcalendario.Festivo = rt_corsa.IncludiFestivo)
										ORDER BY rt_appcalendario.AppCalendarioData
									) AS rt_viewoperativitablocchi01 
									LEFT JOIN RT_CorsaBlocco AS rt_corsablocco ON (rt_viewoperativitablocchi01.CorsaId = rt_corsablocco.CorsaId AND rt_viewoperativitablocchi01.AppCalendarioData = rt_corsablocco.DataPartenza) 
									LEFT JOIN RT_CorsaBloccoWeb AS rt_corsabloccoweb ON (rt_viewoperativitablocchi01.AppCalendarioData = rt_corsabloccoweb.DataPartenza AND rt_viewoperativitablocchi01.CorsaId = rt_corsabloccoweb.CorsaId) 
									ORDER BY rt_viewoperativitablocchi01.AppCalendarioData, rt_viewoperativitablocchi01.OrarioPartenza, rt_viewoperativitablocchi01.CorsaNome
								) AS rt_viewoperativitablocchi 
								LEFT JOIN RT_CorsaConsolidamento AS rt_corsaconsolidamento ON (rt_viewoperativitablocchi.AppCalendarioData = rt_corsaconsolidamento.DataCorsa AND rt_viewoperativitablocchi.CorsaId = rt_corsaconsolidamento.CorsaId) 
								LEFT JOIN RT_CorsaInizioPreparazione AS rt_corsainiziopreparazione ON (rt_viewoperativitablocchi.AppCalendarioData = rt_corsainiziopreparazione.DataCorsa AND rt_viewoperativitablocchi.CorsaId = rt_corsainiziopreparazione.CorsaId) 
								LEFT JOIN RT_CorsaBloccoWeb AS rt_corsabloccoweb ON (rt_viewoperativitablocchi.CorsaId = rt_corsabloccoweb.CorsaId AND rt_viewoperativitablocchi.AppCalendarioData = rt_corsabloccoweb.DataPartenza) 
								GROUP BY rt_viewoperativitablocchi.CorsaId, rt_viewoperativitablocchi.AppCalendarioData
							) AS rt_viewelencogestioneoperativita 
							LEFT JOIN RT_Linea ON (RT_Linea.LineaNome = rt_viewelencogestioneoperativita.LineaNome)
							WHERE rt_viewelencogestioneoperativita.PostiCorsaPrenotati > 0 
							AND ISNULL(rt_viewelencogestioneoperativita.DataConsolidamento) 
							AND rt_viewelencogestioneoperativita.DataOraPartenza >= NOW() 
							AND rt_viewelencogestioneoperativita.DataOraPartenza <= (NOW() + INTERVAL 3 DAY)
							AND rt_viewelencogestioneoperativita.OdcIdRef = $user->OdcId 
							AND RT_Linea.GestoreGruppoId = " . $lineaId['LineaId'];
	// 						   echo($sql);
							$ArrObject = $db->fetch_array($sql);
							$c=0;
							//    print_r($ArrObject);
							while($c<sizeof($ArrObject))
							{
								$lineanome=$ArrObject[$c]['LineaNome'];
								$del=$ArrObject[$c]['DataPartenzaFormattata'];
								$OrarioPartenza=$ArrObject[$c]['OrarioPartenza'];
						  if(($c%2)==0){
							$odd_even = "odd";
						  }
						  else {
							$odd_even = "even";
						  }
						  ?>
							<tr class="<?=$odd_even?>">
								<td><?=$lineanome?></td>
								<td><?=$del?></td>
								<td><?=$OrarioPartenza?></td>
							</tr>
							<?
							$c++;
							}
							?>
						</tbody>
					</table>

				</div>
			</div>
			<?php } ?>
			<!-- box_left -->

			<div class="box_left">
				<h1 class="boxTitolo"><i class="fa fa-ship" aria-hidden="true"></i> <?=$dizionario['home']['passeggeri_lista_attesa']?></h1>
				<div class="boxContentHP">
					<table class="tabella_modulo">
						<thead>
							<tr>
								<td><?=$dizionario['generale']['linea']?></td>
								<td><?=$dizionario['generale']['data']?></td>
								<td><?=$dizionario['generale']['ora']?></td>
								<td><?=$dizionario['generale']['pax']?></td>
							</tr>
						</thead>
						<tbody>
							<?
							$sql = "SELECT
							rt_viewoperativitablocchi.PercorsoNome AS PercorsoNome,
							rt_viewoperativitablocchi.LineaArea AS LineaArea,
							rt_viewoperativitablocchi.LineaNome AS LineaNome,
							rt_viewoperativitablocchi.CorsaNome AS CorsaNome,
							rt_viewoperativitablocchi.AppSettimanaGiorno AS AppSettimanaGiorno,
							rt_viewoperativitablocchi.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
							rt_viewoperativitablocchi.AttivaDal AS AttivaDal,
							rt_viewoperativitablocchi.AttivaAl AS AttivaAl,
							rt_viewoperativitablocchi.AppCalendarioData AS AppCalendarioData,
							rt_viewoperativitablocchi.IncludiFeriale AS IncludiFeriale,
							rt_viewoperativitablocchi.IncludiPrefestivo AS IncludiPrefestivo,
							rt_viewoperativitablocchi.IncludiFestivo AS IncludiFestivo,
							rt_viewoperativitablocchi.Feriale AS Feriale,
							rt_viewoperativitablocchi.Prefestivo AS Prefestivo,
							rt_viewoperativitablocchi.Festivo AS Festivo,
							rt_viewoperativitablocchi.PercorsoId AS PercorsoId,
							rt_viewoperativitablocchi.LineaId AS LineaId,
							rt_viewoperativitablocchi.CorsaId AS CorsaId,
							rt_viewoperativitablocchi.OdcIdRef AS OdcIdRef,
							rt_viewoperativitablocchi.OrarioPartenza AS OrarioPartenza,
							rt_viewoperativitablocchi.CorsaBloccoId AS CorsaBloccoId,
							rt_viewoperativitablocchi.CorsaBloccata AS CorsaBloccata,
							rt_viewoperativitablocchi.DataPartenzaFormattata AS DataPartenzaFormattata,
							rt_viewoperativitablocchi.PostiCorsaDefault AS PostiCorsaDefault,
							rt_viewoperativitablocchi.PostiCorsaAggiunti AS PostiCorsaAggiunti,
							rt_viewoperativitablocchi.PostiCorsaPrenotati AS PostiCorsaPrenotati,
							rt_viewoperativitablocchi.PostiDisponibili AS PostiDisponibili,
							rt_viewoperativitablocchi.VendibileDal AS VendibileDal,
							rt_viewoperativitablocchi.VendibileAl AS VendibileAl,
							rt_viewoperativitablocchi.OrePrimaStopVendita AS OrePrimaStopVendita,
							rt_viewoperativitablocchi.DataOraPartenza AS DataOraPartenza,
							rt_viewoperativitablocchi.OreMancanti AS OreMancanti,
							rt_viewoperativitablocchi.TipologiaBusDefaultId AS TipologiaBusDefaultId,
							rt_viewelencoprenotazioneattesanumero.Numero AS Numero 
							FROM (
								SELECT 
								rt_viewoperativitablocchi01.PercorsoNome AS PercorsoNome,
								rt_viewoperativitablocchi01.LineaArea AS LineaArea,
								rt_viewoperativitablocchi01.LineaNome AS LineaNome,
								rt_viewoperativitablocchi01.CorsaNome AS CorsaNome,
								rt_viewoperativitablocchi01.AppSettimanaGiorno AS AppSettimanaGiorno,
								rt_viewoperativitablocchi01.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
								rt_viewoperativitablocchi01.AttivaDal AS AttivaDal,
								rt_viewoperativitablocchi01.AttivaAl AS AttivaAl,
								rt_viewoperativitablocchi01.AppCalendarioData AS AppCalendarioData,
								rt_viewoperativitablocchi01.IncludiFeriale AS IncludiFeriale,
								rt_viewoperativitablocchi01.IncludiPrefestivo AS IncludiPrefestivo,
								rt_viewoperativitablocchi01.IncludiFestivo AS IncludiFestivo,
								rt_viewoperativitablocchi01.Feriale AS Feriale,
								rt_viewoperativitablocchi01.Prefestivo AS Prefestivo,
								rt_viewoperativitablocchi01.Festivo AS Festivo,
								rt_viewoperativitablocchi01.PercorsoId AS PercorsoId,
								rt_viewoperativitablocchi01.LineaId AS LineaId,
								rt_viewoperativitablocchi01.CorsaId AS CorsaId,
								rt_viewoperativitablocchi01.OdcIdRef AS OdcIdRef,
								rt_viewoperativitablocchi01.OrarioPartenza AS OrarioPartenza,
								rt_corsablocco.CorsaBloccoId AS CorsaBloccoId,
								IF(ISNULL(rt_corsablocco.CorsaBloccoId), 0, 1) AS CorsaBloccata,
								rt_viewoperativitablocchi01.DataPartenzaFormattata AS DataPartenzaFormattata,
								rt_viewoperativitablocchi01.PostiCorsaDefault AS PostiCorsaDefault,
								rt_viewoperativitablocchi01.PostiCorsaAggiunti AS PostiCorsaAggiunti,
								rt_viewoperativitablocchi01.PostiCorsaPrenotati AS PostiCorsaPrenotati,
								(rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) AS PostiTotali,
								((rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) - rt_viewoperativitablocchi01.PostiCorsaPrenotati) AS PostiDisponibili,
								((rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) - rt_viewoperativitablocchi01.PostiRealmentePrenotati) AS PostiRealmenteDisponibili,
								rt_viewoperativitablocchi01.VendibileDal AS VendibileDal,
								rt_viewoperativitablocchi01.VendibileAl AS VendibileAl,
								rt_viewoperativitablocchi01.OrePrimaStopVendita AS OrePrimaStopVendita,
								rt_viewoperativitablocchi01.DataOraPartenza AS DataOraPartenza,
								rt_viewoperativitablocchi01.OreMancanti AS OreMancanti,
								rt_viewoperativitablocchi01.TipologiaBusDefaultId AS TipologiaBusDefaultId,
								rt_corsabloccoweb.CorsaBloccoId AS CorsaBloccoWebId,
								rt_viewoperativitablocchi01.PostiRealmentePrenotati AS PostiRealmentePrenotati 
								FROM (
									SELECT 
									rt_percorso.PercorsoNome AS PercorsoNome,
									rt_linea.LineaArea AS LineaArea,
									rt_linea.LineaNome AS LineaNome,
									rt_corsa.CorsaNome AS CorsaNome,
									rt_appsettimana.AppSettimanaGiorno AS AppSettimanaGiorno,
									rt_appsettimana.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
									rt_corsa.AttivaDal AS AttivaDal,
									rt_corsa.AttivaAl AS AttivaAl,
									rt_appcalendario.AppCalendarioData AS AppCalendarioData,
									DATE_FORMAT(rt_appcalendario.AppCalendarioData, _utf8 '%d/%m/%Y') AS DataPartenzaFormattata,
									rt_corsa.IncludiFeriale AS IncludiFeriale,
									rt_corsa.IncludiPrefestivo AS IncludiPrefestivo,
									rt_corsa.IncludiFestivo AS IncludiFestivo,
									rt_appcalendario.Feriale AS Feriale,
									rt_appcalendario.Prefestivo AS Prefestivo,
									rt_appcalendario.Festivo AS Festivo,
									rt_percorso.PercorsoId AS PercorsoId,
									rt_linea.LineaId AS LineaId,
									rt_corsa.CorsaId AS CorsaId,
									rt_percorso.OdcIdRef AS OdcIdRef,
									rt_corsa.OrarioPartenza AS OrarioPartenza,
									rt_corsa.VendibileDal AS VendibileDal,
									rt_corsa.VendibileAl AS VendibileAl,
									rt_corsa.OrePrimaStopVendita AS OrePrimaStopVendita,
									ADDTIME(rt_appcalendario.AppCalendarioData, rt_corsa.OrarioPartenza) AS DataOraPartenza,
									TIMEDIFF(ADDTIME(rt_appcalendario.AppCalendarioData, rt_corsa.OrarioPartenza), NOW()) AS OreMancanti,
									rt_corsa.TipologiaBusDefaultId AS TipologiaBusDefaultId,
									rt_tipologiabus.TotalePosti AS PostiCorsaDefault,
									IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti), 0, rt_viewsingolacorsapostiaggiunti.PostiAggiunti) AS PostiCorsaAggiunti,
									IF(isnull(rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati), 0, rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati) AS PostiCorsaPrenotati,
									rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati AS PostiRealmentePrenotati 
									FROM RT_Percorso AS rt_percorso 
									LEFT JOIN RT_Linea AS rt_linea ON (rt_percorso.PercorsoId = rt_linea.PercorsoId) 
									LEFT JOIN RT_Corsa AS rt_corsa ON (rt_linea.LineaId = rt_corsa.LineaId) 
									LEFT JOIN RT_CorsaSettimana AS rt_corsasettimana ON (rt_corsa.CorsaId = rt_corsasettimana.CorsaId) 
									LEFT JOIN RT_AppSettimana AS rt_appsettimana ON (rt_corsasettimana.SettimanaId = rt_appsettimana.AppSettimanaId) 
									LEFT JOIN RT_AppCalendario AS rt_appcalendario ON (rt_appsettimana.AppSettimanaGiorno = rt_appcalendario.GiornoSettimana)
									LEFT JOIN RT_TipologiaBus AS rt_tipologiabus ON (rt_corsa.TipologiaBusDefaultId = rt_tipologiabus.TipologiaBusId)
									LEFT JOIN (
										SELECT 
										rt_corsapax.CorsaId AS CorsaId,
										rt_corsapax.DataPartenza AS DataPartenza,
										SUM(rt_corsapax.NumeroPax) AS PostiAggiunti,
										rt_corsapax.OdcIdRef AS OdcIdRef 
										FROM RT_CorsaPax AS rt_corsapax 
										WHERE rt_corsapax.Cancella = 0 
										GROUP BY rt_corsapax.CorsaId, rt_corsapax.DataPartenza, rt_corsapax.OdcIdRef
									) AS rt_viewsingolacorsapostiaggiunti ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiaggiunti.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostiaggiunti.DataPartenza) 
									LEFT JOIN (
										SELECT 
										rt_prenotazionepercorso.CorsaId AS CorsaId,
										rt_prenotazionepercorso.CorsaDataPartenza AS CorsaDataPartenza,
										(COUNT(rt_prenotazione.PrenotazioneId) - SUM(rt_prenotazionedettaglio.Escludi)) AS TotalePaxPrenotati,
										rt_prenotazione.OdcIdRef AS OdcIdRef 
										FROM RT_PrenotazionePercorso AS rt_prenotazionepercorso 
										JOIN RT_Prenotazione AS rt_prenotazione ON (rt_prenotazionepercorso.PrenotazioneId = rt_prenotazione.PrenotazioneId)
										JOIN RT_PrenotazioneDettaglio AS rt_prenotazionedettaglio ON (rt_prenotazionepercorso.PrenotazioneId = rt_prenotazionedettaglio.PrenotazioneId AND rt_prenotazionedettaglio.ComunePartenza = rt_prenotazionepercorso.ComuneSalita AND rt_prenotazionedettaglio.PrenotazioneId = rt_prenotazione.PrenotazioneId) 
										JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazionepercorso.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId) 
										WHERE rt_prenotazione.Cancella = 0
										AND rt_prenotazionepercorso.Cancella = 0
										AND rt_prenotazionepercorso.Stato = 1
										AND rt_appprenotazionestato.OccupaPosti = 1
										AND (
											(rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazionedettaglio.Escludi = 0) 
											OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 1)
											OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 0 AND rt_prenotazione.ScadenzaPrenotazione > rt_prenotazionedettaglio.DataPartenza)
											OR (rt_prenotazione.ABordo = 1 AND rt_prenotazione.PrenotazioneStato <> 6 AND rt_prenotazione.PrenotazioneStato <> 4 AND rt_prenotazione.PrenotazioneStato <> 7 AND rt_prenotazione.PrenotazioneStato <> 3)
											OR (rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazione.ABordo = 1 AND rt_prenotazionedettaglio.Escludi = 0)
										)
										GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
									) AS rt_viewsingolacorsapostiprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiprenotati.CorsaId AND rt_viewsingolacorsapostiprenotati.CorsaDataPartenza = rt_appcalendario.AppCalendarioData) 
									LEFT JOIN (
										SELECT 
										rt_prenotazionepercorso.CorsaId AS CorsaId,
										rt_prenotazionepercorso.CorsaDataPartenza AS CorsaDataPartenza,
										SUM(rt_prenotazione.TotalePaxPrenotati - rt_prenotazionepercorso.PasseggeriEsclusi) AS TotalePaxPrenotati,
										rt_prenotazione.OdcIdRef AS OdcIdRef 
										FROM RT_Prenotazione AS rt_prenotazione 
										JOIN RT_PrenotazionePercorso AS rt_prenotazionepercorso ON (rt_prenotazione.PrenotazioneId = rt_prenotazionepercorso.PrenotazioneId)
										JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazionepercorso.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId)
										WHERE rt_prenotazione.Cancella = 0
										AND rt_prenotazionepercorso.Cancella = 0
										AND rt_prenotazionepercorso.Stato = 1
										AND rt_appprenotazionestato.OccupaPosti = 1
										GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
									) AS rt_viewsingolacorsapostirealmenteprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostirealmenteprenotati.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostirealmenteprenotati.CorsaDataPartenza) 
									WHERE rt_percorso.Stato = 1
									AND rt_percorso.Cancella = 0
									AND rt_linea.Stato = 1
									AND rt_linea.Cancella = 0
									AND rt_corsa.Stato = 1
									AND rt_corsa.Cancella = 0
									AND rt_appcalendario.AppCalendarioData >= rt_corsa.AttivaDal
									AND rt_appcalendario.AppCalendarioData <= rt_corsa.AttivaAl
									AND (rt_appcalendario.Feriale = rt_corsa.IncludiFeriale OR rt_appcalendario.Prefestivo = rt_corsa.IncludiPrefestivo OR rt_appcalendario.Festivo = rt_corsa.IncludiFestivo)
									ORDER BY rt_appcalendario.AppCalendarioData
								) AS rt_viewoperativitablocchi01 
								LEFT JOIN RT_CorsaBlocco AS rt_corsablocco ON (rt_viewoperativitablocchi01.CorsaId = rt_corsablocco.CorsaId AND rt_viewoperativitablocchi01.AppCalendarioData = rt_corsablocco.DataPartenza) 
								LEFT JOIN RT_CorsaBloccoWeb AS rt_corsabloccoweb ON (rt_viewoperativitablocchi01.AppCalendarioData = rt_corsabloccoweb.DataPartenza AND rt_viewoperativitablocchi01.CorsaId = rt_corsabloccoweb.CorsaId) 
								ORDER BY rt_viewoperativitablocchi01.AppCalendarioData, rt_viewoperativitablocchi01.OrarioPartenza, rt_viewoperativitablocchi01.CorsaNome
							) AS rt_viewoperativitablocchi 
							JOIN (
								SELECT 
								SUM(rt_viewelencoprenotazionelista.TotalePostiPrenotati) AS Numero,
								rt_viewelencoprenotazionelista.CorsaDataPartenza AS CorsaDataPartenza,
								rt_viewelencoprenotazionelista.CorsaId AS CorsaId,
								rt_viewelencoprenotazionelista.OdcIdRef AS OdcIdRef 
								FROM rt_viewelencoprenotazionelista 
								GROUP BY rt_viewelencoprenotazionelista.CorsaDataPartenza, rt_viewelencoprenotazionelista.CorsaId, rt_viewelencoprenotazionelista.GestoreIdRef
							) AS rt_viewelencoprenotazioneattesanumero ON (rt_viewoperativitablocchi.CorsaId = rt_viewelencoprenotazioneattesanumero.CorsaId AND rt_viewoperativitablocchi.AppCalendarioData = rt_viewelencoprenotazioneattesanumero.CorsaDataPartenza)
							LEFT JOIN RT_Linea ON (RT_Linea.LineaNome = rt_viewoperativitablocchi.LineaNome)
							WHERE rt_viewoperativitablocchi.OdcIdRef = $user->OdcId and rt_viewoperativitablocchi.GestoreIdRef = $user->GestoreId 
							AND RT_Linea.GestoreGruppoId = ".$lineaId['LineaId'];
	// 						echo($sql);
							$ArrObject = $db->fetch_array($sql);
							$c=0;
							//    print_r($ArrObject);
							while($c<sizeof($ArrObject))
							{
								$lineanome=$ArrObject[$c]['LineaNome'];
								$del=$ArrObject[$c]['DataPartenzaFormattata'];
								$OrarioPartenza=$ArrObject[$c]['OrarioPartenza'];
								$Pax=$ArrObject[$c]['Numero'];
						  if(($c%2)==0){
							$odd_even = "odd";
						  }
						  else {
							$odd_even = "even";
						  }
						  ?>
							<tr class="<?=$odd_even?>">
								<td><?=$lineanome?></td>
								<td><?=$del?></td>
								<td><?=$OrarioPartenza?></td>
								<td><?=$Pax?></td>
							</tr>
							<?
							$c++;
							}
							?>
						</tbody>
					</table>
				</div>
			</div>

			
			 <div class="box_left">
				<h1 class="boxTitolo"><i class="fa fa-ship" aria-hidden="true"></i> <?=$dizionario['home']['posti_esaurimento']?></h1>
				 <div class="boxContentHP">
					<table class="tabella_modulo">
						<thead>
							<tr>
								<td><?=$dizionario['generale']['linea']?></td>
								<td><?=$dizionario['generale']['data']?></td>
								<td><?=$dizionario['generale']['ora']?></td>
								<td><?=$dizionario['generale']['pre']?></td>
								<td><?=$dizionario['generale']['disp']?></td>
							</tr>
						</thead>
						<tbody>
					<?
					  $sql = "SELECT 
					  rt_viewoperativitablocchi.PercorsoNome AS PercorsoNome,
					  rt_viewoperativitablocchi.LineaArea AS LineaArea,
					  rt_viewoperativitablocchi.LineaNome AS LineaNome,
					  rt_viewoperativitablocchi.CorsaNome AS CorsaNome,
					  rt_viewoperativitablocchi.AppSettimanaGiorno AS AppSettimanaGiorno,
					  rt_viewoperativitablocchi.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
					  rt_viewoperativitablocchi.AttivaDal AS AttivaDal,
					  rt_viewoperativitablocchi.AttivaAl AS AttivaAl,
					  rt_viewoperativitablocchi.AppCalendarioData AS AppCalendarioData,
					  rt_viewoperativitablocchi.IncludiFeriale AS IncludiFeriale,
					  rt_viewoperativitablocchi.IncludiPrefestivo AS IncludiPrefestivo,
					  rt_viewoperativitablocchi.IncludiFestivo AS IncludiFestivo,
					  rt_viewoperativitablocchi.Feriale AS Feriale,
					  rt_viewoperativitablocchi.Prefestivo AS Prefestivo,
					  rt_viewoperativitablocchi.Festivo AS Festivo,
					  rt_viewoperativitablocchi.PercorsoId AS PercorsoId,
					  rt_viewoperativitablocchi.LineaId AS LineaId,
					  rt_viewoperativitablocchi.CorsaId AS CorsaId,
					  rt_viewoperativitablocchi.OdcIdRef AS OdcIdRef,
					  rt_viewoperativitablocchi.OrarioPartenza AS OrarioPartenza,
					  rt_viewoperativitablocchi.CorsaBloccoId AS CorsaBloccoId,
					  rt_viewoperativitablocchi.CorsaBloccata AS CorsaBloccata,
					  rt_viewoperativitablocchi.DataPartenzaFormattata AS DataPartenzaFormattata,
					  rt_viewoperativitablocchi.PostiCorsaDefault AS PostiCorsaDefault,
					  rt_viewoperativitablocchi.PostiCorsaAggiunti AS PostiCorsaAggiunti,
					  rt_viewoperativitablocchi.PostiCorsaPrenotati AS PostiCorsaPrenotati,
					  rt_viewoperativitablocchi.PostiDisponibili AS PostiDisponibili,
					  rt_viewoperativitablocchi.VendibileDal AS VendibileDal,
					  rt_viewoperativitablocchi.VendibileAl AS VendibileAl,
					  rt_viewoperativitablocchi.OrePrimaStopVendita AS OrePrimaStopVendita,
					  rt_viewoperativitablocchi.DataOraPartenza AS DataOraPartenza,
					  rt_viewoperativitablocchi.OreMancanti AS OreMancanti,
					  rt_viewoperativitablocchi.TipologiaBusDefaultId AS TipologiaBusDefaultId,
					  rt_viewoperativitablocchi.PostiRealmenteDisponibili AS PostiRealmenteDisponibili,
					  rt_viewoperativitablocchi.PostiRealmentePrenotati AS PostiRealmentePrenotati 
					  FROM (
							SELECT 
							rt_viewoperativitablocchi01.PercorsoNome AS PercorsoNome,
							rt_viewoperativitablocchi01.LineaArea AS LineaArea,
							rt_viewoperativitablocchi01.LineaNome AS LineaNome,
							rt_viewoperativitablocchi01.CorsaNome AS CorsaNome,
							rt_viewoperativitablocchi01.AppSettimanaGiorno AS AppSettimanaGiorno,
							rt_viewoperativitablocchi01.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
							rt_viewoperativitablocchi01.AttivaDal AS AttivaDal,
							rt_viewoperativitablocchi01.AttivaAl AS AttivaAl,
							rt_viewoperativitablocchi01.AppCalendarioData AS AppCalendarioData,
							rt_viewoperativitablocchi01.IncludiFeriale AS IncludiFeriale,
							rt_viewoperativitablocchi01.IncludiPrefestivo AS IncludiPrefestivo,
							rt_viewoperativitablocchi01.IncludiFestivo AS IncludiFestivo,
							rt_viewoperativitablocchi01.Feriale AS Feriale,
							rt_viewoperativitablocchi01.Prefestivo AS Prefestivo,
							rt_viewoperativitablocchi01.Festivo AS Festivo,
							rt_viewoperativitablocchi01.PercorsoId AS PercorsoId,
							rt_viewoperativitablocchi01.LineaId AS LineaId,
							rt_viewoperativitablocchi01.CorsaId AS CorsaId,
							rt_viewoperativitablocchi01.OdcIdRef AS OdcIdRef,
							rt_viewoperativitablocchi01.OrarioPartenza AS OrarioPartenza,
							rt_corsablocco.CorsaBloccoId AS CorsaBloccoId,
							IF(ISNULL(rt_corsablocco.CorsaBloccoId), 0, 1) AS CorsaBloccata,
							rt_viewoperativitablocchi01.DataPartenzaFormattata AS DataPartenzaFormattata,
							rt_viewoperativitablocchi01.PostiCorsaDefault AS PostiCorsaDefault,
							rt_viewoperativitablocchi01.PostiCorsaAggiunti AS PostiCorsaAggiunti,
							rt_viewoperativitablocchi01.PostiCorsaPrenotati AS PostiCorsaPrenotati,
							(rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) AS PostiTotali,
							((rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) - rt_viewoperativitablocchi01.PostiCorsaPrenotati) AS PostiDisponibili,
							((rt_viewoperativitablocchi01.PostiCorsaDefault + rt_viewoperativitablocchi01.PostiCorsaAggiunti) - rt_viewoperativitablocchi01.PostiRealmentePrenotati) AS PostiRealmenteDisponibili,
							rt_viewoperativitablocchi01.VendibileDal AS VendibileDal,
							rt_viewoperativitablocchi01.VendibileAl AS VendibileAl,
							rt_viewoperativitablocchi01.OrePrimaStopVendita AS OrePrimaStopVendita,
							rt_viewoperativitablocchi01.DataOraPartenza AS DataOraPartenza,
							rt_viewoperativitablocchi01.OreMancanti AS OreMancanti,
							rt_viewoperativitablocchi01.TipologiaBusDefaultId AS TipologiaBusDefaultId,
							rt_corsabloccoweb.CorsaBloccoId AS CorsaBloccoWebId,
							rt_viewoperativitablocchi01.PostiRealmentePrenotati AS PostiRealmentePrenotati 
							FROM (
								SELECT 
								rt_percorso.PercorsoNome AS PercorsoNome,
								rt_linea.LineaArea AS LineaArea,
								rt_linea.LineaNome AS LineaNome,
								rt_corsa.CorsaNome AS CorsaNome,
								rt_appsettimana.AppSettimanaGiorno AS AppSettimanaGiorno,
								rt_appsettimana.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
								rt_corsa.AttivaDal AS AttivaDal,
								rt_corsa.AttivaAl AS AttivaAl,
								rt_appcalendario.AppCalendarioData AS AppCalendarioData,
								DATE_FORMAT(rt_appcalendario.AppCalendarioData, _utf8 '%d/%m/%Y') AS DataPartenzaFormattata,
								rt_corsa.IncludiFeriale AS IncludiFeriale,
								rt_corsa.IncludiPrefestivo AS IncludiPrefestivo,
								rt_corsa.IncludiFestivo AS IncludiFestivo,
								rt_appcalendario.Feriale AS Feriale,
								rt_appcalendario.Prefestivo AS Prefestivo,
								rt_appcalendario.Festivo AS Festivo,
								rt_percorso.PercorsoId AS PercorsoId,
								rt_linea.LineaId AS LineaId,
								rt_corsa.CorsaId AS CorsaId,
								rt_percorso.OdcIdRef AS OdcIdRef,
								rt_corsa.OrarioPartenza AS OrarioPartenza,
								rt_corsa.VendibileDal AS VendibileDal,
								rt_corsa.VendibileAl AS VendibileAl,
								rt_corsa.OrePrimaStopVendita AS OrePrimaStopVendita,
								ADDTIME(rt_appcalendario.AppCalendarioData, rt_corsa.OrarioPartenza) AS DataOraPartenza,
								TIMEDIFF(ADDTIME(rt_appcalendario.AppCalendarioData, rt_corsa.OrarioPartenza), NOW()) AS OreMancanti,
								rt_corsa.TipologiaBusDefaultId AS TipologiaBusDefaultId,
								rt_tipologiabus.TotalePosti AS PostiCorsaDefault,
								IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti), 0, rt_viewsingolacorsapostiaggiunti.PostiAggiunti) AS PostiCorsaAggiunti,
								IF(isnull(rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati), 0, rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati) AS PostiCorsaPrenotati,
								rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati AS PostiRealmentePrenotati 
								FROM RT_Percorso AS rt_percorso 
								LEFT JOIN RT_Linea AS rt_linea ON (rt_percorso.PercorsoId = rt_linea.PercorsoId) 
								LEFT JOIN RT_Corsa AS rt_corsa ON (rt_linea.LineaId = rt_corsa.LineaId) 
								LEFT JOIN RT_CorsaSettimana AS rt_corsasettimana ON (rt_corsa.CorsaId = rt_corsasettimana.CorsaId) 
								LEFT JOIN RT_AppSettimana AS rt_appsettimana ON (rt_corsasettimana.SettimanaId = rt_appsettimana.AppSettimanaId) 
								LEFT JOIN RT_AppCalendario AS rt_appcalendario ON (rt_appsettimana.AppSettimanaGiorno = rt_appcalendario.GiornoSettimana)
								LEFT JOIN RT_TipologiaBus AS rt_tipologiabus ON (rt_corsa.TipologiaBusDefaultId = rt_tipologiabus.TipologiaBusId)
								LEFT JOIN (
									SELECT 
									rt_corsapax.CorsaId AS CorsaId,
									rt_corsapax.DataPartenza AS DataPartenza,
									SUM(rt_corsapax.NumeroPax) AS PostiAggiunti,
									rt_corsapax.OdcIdRef AS OdcIdRef 
									FROM RT_CorsaPax AS rt_corsapax 
									WHERE rt_corsapax.Cancella = 0 
									GROUP BY rt_corsapax.CorsaId, rt_corsapax.DataPartenza, rt_corsapax.OdcIdRef
								) AS rt_viewsingolacorsapostiaggiunti ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiaggiunti.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostiaggiunti.DataPartenza) 
								LEFT JOIN (
									SELECT 
									rt_prenotazionepercorso.CorsaId AS CorsaId,
									rt_prenotazionepercorso.CorsaDataPartenza AS CorsaDataPartenza,
									(COUNT(rt_prenotazione.PrenotazioneId) - SUM(rt_prenotazionedettaglio.Escludi)) AS TotalePaxPrenotati,
									rt_prenotazione.OdcIdRef AS OdcIdRef 
									FROM RT_PrenotazionePercorso AS rt_prenotazionepercorso 
									JOIN RT_Prenotazione AS rt_prenotazione ON (rt_prenotazionepercorso.PrenotazioneId = rt_prenotazione.PrenotazioneId)
									JOIN RT_PrenotazioneDettaglio AS rt_prenotazionedettaglio ON (rt_prenotazionepercorso.PrenotazioneId = rt_prenotazionedettaglio.PrenotazioneId AND rt_prenotazionedettaglio.ComunePartenza = rt_prenotazionepercorso.ComuneSalita AND rt_prenotazionedettaglio.PrenotazioneId = rt_prenotazione.PrenotazioneId) 
									JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazionepercorso.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId) 
									WHERE rt_prenotazione.Cancella = 0
									AND rt_prenotazionepercorso.Cancella = 0
									AND rt_prenotazionepercorso.Stato = 1
									AND rt_appprenotazionestato.OccupaPosti = 1
									AND (
										(rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazionedettaglio.Escludi = 0) 
										OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 1)
										OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 0 AND rt_prenotazione.ScadenzaPrenotazione > rt_prenotazionedettaglio.DataPartenza)
										OR (rt_prenotazione.ABordo = 1 AND rt_prenotazione.PrenotazioneStato <> 6 AND rt_prenotazione.PrenotazioneStato <> 4 AND rt_prenotazione.PrenotazioneStato <> 7 AND rt_prenotazione.PrenotazioneStato <> 3)
										OR (rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazione.ABordo = 1 AND rt_prenotazionedettaglio.Escludi = 0)
									)
									GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
								) AS rt_viewsingolacorsapostiprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiprenotati.CorsaId AND rt_viewsingolacorsapostiprenotati.CorsaDataPartenza = rt_appcalendario.AppCalendarioData) 
								LEFT JOIN (
									SELECT 
									rt_prenotazionepercorso.CorsaId AS CorsaId,
									rt_prenotazionepercorso.CorsaDataPartenza AS CorsaDataPartenza,
									SUM(rt_prenotazione.TotalePaxPrenotati - rt_prenotazionepercorso.PasseggeriEsclusi) AS TotalePaxPrenotati,
									rt_prenotazione.OdcIdRef AS OdcIdRef 
									FROM RT_Prenotazione AS rt_prenotazione 
									JOIN RT_PrenotazionePercorso AS rt_prenotazionepercorso ON (rt_prenotazione.PrenotazioneId = rt_prenotazionepercorso.PrenotazioneId)
									JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazionepercorso.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId)
									WHERE rt_prenotazione.Cancella = 0
									AND rt_prenotazionepercorso.Cancella = 0
									AND rt_prenotazionepercorso.Stato = 1
									AND rt_appprenotazionestato.OccupaPosti = 1
									GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
								) AS rt_viewsingolacorsapostirealmenteprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostirealmenteprenotati.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostirealmenteprenotati.CorsaDataPartenza) 
								WHERE rt_percorso.Stato = 1
								AND rt_percorso.Cancella = 0
								AND rt_linea.Stato = 1
								AND rt_linea.Cancella = 0
								AND rt_corsa.Stato = 1
								AND rt_corsa.Cancella = 0
								AND rt_appcalendario.AppCalendarioData >= rt_corsa.AttivaDal
								AND rt_appcalendario.AppCalendarioData <= rt_corsa.AttivaAl
								AND (rt_appcalendario.Feriale = rt_corsa.IncludiFeriale OR rt_appcalendario.Prefestivo = rt_corsa.IncludiPrefestivo OR rt_appcalendario.Festivo = rt_corsa.IncludiFestivo)
								ORDER BY rt_appcalendario.AppCalendarioData
							) AS rt_viewoperativitablocchi01 
							LEFT JOIN RT_CorsaBlocco AS rt_corsablocco ON (rt_viewoperativitablocchi01.CorsaId = rt_corsablocco.CorsaId AND rt_viewoperativitablocchi01.AppCalendarioData = rt_corsablocco.DataPartenza) 
							LEFT JOIN RT_CorsaBloccoWeb AS rt_corsabloccoweb ON (rt_viewoperativitablocchi01.AppCalendarioData = rt_corsabloccoweb.DataPartenza AND rt_viewoperativitablocchi01.CorsaId = rt_corsabloccoweb.CorsaId) 
							ORDER BY rt_viewoperativitablocchi01.AppCalendarioData, rt_viewoperativitablocchi01.OrarioPartenza, rt_viewoperativitablocchi01.CorsaNome
					  ) AS rt_viewoperativitablocchi 
					  LEFT JOIN RT_Linea ON (RT_Linea.LineaNome = rt_viewoperativitablocchi.LineaNome)
					  WHERE rt_viewoperativitablocchi.DataOraPartenza >= NOW() 
					  AND rt_viewoperativitablocchi.CorsaBloccata = 0 
					  AND rt_viewoperativitablocchi.PostiDisponibili <= 5
					  AND rt_viewoperativitablocchi.OdcIdRef = $user->OdcId AND rt_viewoperativitablocchi.GestoreIdRef = $user->GestoreId 
					  AND RT_Linea.GestoreGruppoId = " . $lineaId['LineaId'];
	//                  echo($sql);
					  $ArrObject = $db->fetch_array($sql);
					  $c=0;
				 
					  while($c<sizeof($ArrObject))
					  {
						  $lineanome=$ArrObject[$c]['LineaNome'];
						  $del=$ArrObject[$c]['DataPartenzaFormattata'];
						  $OrarioPartenza=$ArrObject[$c]['OrarioPartenza'];
						  $PostiCorsaPrenotati=$ArrObject[$c]['PostiRealmentePrenotati'];
						  $PostiCorsaDisponibili=$ArrObject[$c]['PostiRealmenteDisponibili'];
						   
						  if(($c%2)==0){
							$odd_even = "odd";  
						  }
						  else {
							$odd_even = "even";
						  }
						?>
						<tr class="<?=$odd_even?>">
							<td><?=$lineanome?></td>
							<td><?=$del?></td>
							<td><?=$OrarioPartenza?></td>
							<td><?=$PostiCorsaPrenotati?></td>
							<td><?=$PostiCorsaDisponibili?></td>
						</tr>
						<?
						 $c++; 
					  }
						?>
						</tbody>
					</table> 
				</div>
			  </div>
			  <!-- box_left -->

			<!-- BOX PRENOTAZIONI IN SCADENZA -->
			<div class="box_right">
				<h1 class="boxTitolo"><i class="fa fa-ship" aria-hidden="true"></i> <?=$dizionario['home']['prenotazione_in_scadenza']?></h1>
				<div class="boxContentHP">
					<table class="tabella_modulo">
						<thead>
							<tr>
								<td><?=$dizionario['generale']['prenotazione']?></td>
								<td><?=$dizionario['generale']['scadenza']?></td>
								<td><?=$dizionario['generale']['totale']?></td>
							</tr>
						</thead>
						<tbody>
							<?
							$currentDate = date("Y-m-d H:i:s");
							$sql="SELECT RT_Prenotazione.*, SUM(TotaleDaPagare) AS Totale 
									FROM RT_Prenotazione 
									LEFT JOIN RT_PrenotazioneDettaglio ON RT_PrenotazioneDettaglio.PrenotazioneId = RT_Prenotazione.PrenotazioneId
									WHERE RT_Prenotazione.PrenotazioneStato = 1 AND RT_Prenotazione.Pagato = 0 AND RT_Prenotazione.ScadenzaPrenotazione >= '" . $currentDate . "' AND RT_Prenotazione.OdcIdRef=$user->OdcId AND RT_Prenotazione.GestoreIdRef=$user->GestoreId
									AND RT_PrenotazioneDettaglio.LineaId = ".$lineaId['LineaId']."  
									GROUP BY CodicePrenotazione";

							$ArrObject = $db->fetch_array($sql);

							foreach ($ArrObject as $i => $prenotazione) {
								if (($i % 2) == 0) {
									$odd_even = "odd";
								} else {
									$odd_even = "even";
								}
							?>
								<tr class="<?=$odd_even?>">
									<td><?=$prenotazione['CodicePrenotazione']?></td>
									<td><?=$dt->format($prenotazione['ScadenzaPrenotazione'], "Y-m-d H:i:s", "d/m/Y H:i")?></td>
									<td><?=number_format($prenotazione['Totale'], 2, ",", ".")?></td>
								</tr>
							<?
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
		</div>
	</div>
	
<?php } ?>

</div>


<div id="RightSideHp"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

	var periodoSelezionato = 'giorno';
	<?php if($user->GestoreId == 1 ) {?>
		var countLoading = 4;
	<?php } else { ?>
		var countLoading = 3;
	<?php } ?>
	
	function grafoPrenotazioni(periodo) {
		var data = {
			action: 'prenotazioniGrafo',
			periodo: periodo,
			da: $("#daLibera").val(),
			a: $("#aLibera").val(),
			barca: $("#barca").val(),
			tipoNoleggio: $("#tipoNoleggio").val(),
			areaLavoro: $("#areaLavoro").val()
		};

		var ctx = document.getElementById('myChart');
		
		if (ctx) {
			// Se il canvas esiste, rimuovilo
			ctx.parentNode.removeChild(ctx);
		}

		// Crea un nuovo canvas
		var newCanvas = document.createElement('canvas');
		newCanvas.id = 'myChart';

		// Aggiungi il nuovo canvas al DOM
		var container = document.getElementById('graph-container'); // Sostituisci con l'ID del contenitore del grafico
		container.appendChild(newCanvas);

		// Esegui la chiamata AJAX
		$.ajax({
			type: 'POST',
			url: '/protected/modules/home/home_action.php',
			data: data,
			dataType: 'json',
			success: function(response) {
				// Ottieni i dati dalla risposta
				var labels = [];
				var dataValues = [];

				response.forEach(function(item) {
					if (periodo === 'settimana') {
						labels.push('Settimana ' + item.week);
					} else if (periodo === 'mese') {
						// Se il periodo è "mese," formatta l'etichetta come Gen, Feb, Mar, ...
						var monthLabels = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
						labels.push(monthLabels[item.month - 1] + " " + item.year);
					} else if (periodo === 'libera') {
						// Se il periodo è "mese," formatta l'etichetta come Gen, Feb, Mar, ...
						var monthLabels = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
						labels.push(monthLabels[item.month - 1] + " " + item.year);
					} else {
						// Se il periodo è "giorno," mostra la data come "1/10/2023"
						var formattedLabel = item.day + '/' + item.month + '/' + item.year;
						labels.push(formattedLabel);
					}
					dataValues.push(item.total);
				});

				// Crea un nuovo grafico con i nuovi dati
				var newCtx = newCanvas.getContext('2d');
				var myChart = new Chart(newCtx, {
					type: 'line',
					data: {
						labels: labels,
						datasets: [{
							label: 'Prenotazioni',
							data: dataValues,
							fill: false,
							borderColor: 'rgba(75, 192, 192, 1)',
							borderWidth: 2
						}]
					},
					options: {
						scales: {
							y: {
								beginAtZero: true
							}
						}
					}
				});
				checkEndLoading();
			},
			error: function(xhr, status, error) {
				console.log('Errore nella chiamata AJAX:', error);
				checkEndLoading();
			}
		});
	}

	function checkEndLoading() {
		countLoading--;
		if(countLoading == 0) {
			$('#layer_nero2').css('display','none');
			$('#brain_loading').css('display','none');
			<?php if($user->GestoreId == 1 ) {?>
				countLoading = 4;
			<?php } else { ?>
				countLoading = 3;
			<?php } ?>
		}
	}
	
	function prenotazioni(periodo) {
		$('#layer_nero2').css('display','inline');
		$('#brain_loading').css('display','inline');
		// Imposta i parametri per la chiamata AJAX
		var data = {
			action: 'prenotazioni',
			periodo: periodo,
			da: $("#daLibera").val(),
			a: $("#aLibera").val(),
			barca: $("#barca").val(),
			tipoNoleggio: $("#tipoNoleggio").val(),
			areaLavoro: $("#areaLavoro").val()
		};

		// Esegui la chiamata AJAX
		$.ajax({
			type: 'POST', 
			url: '/protected/modules/home/home_action.php',
			data: data,
			dataType: 'json', 
			success: function(response) {
				$("#prenotazioniValue").html(response.currentTotal);
				$("#prenotazioniValuePrev").html(response.previousTotal);
				
				checkEndLoading();
			},
			error: function(xhr, status, error) {
				$("#prenotazioniValue").html("-");
				$("#prenotazioniValuePrev").html("-");
				checkEndLoading();
				console.log('Errore nella chiamata AJAX:', error);
			}
		});
	}
	
	function entrate(periodo) {
		$('#layer_nero2').css('display','inline');
		$('#brain_loading').css('display','inline');
		// Imposta i parametri per la chiamata AJAX
		var data = {
			action: 'entrate',
			periodo: periodo,
			da: $("#daLibera").val(),
			a: $("#aLibera").val(),
			barca: $("#barca").val(),
			tipoNoleggio: $("#tipoNoleggio").val(),
			areaLavoro: $("#areaLavoro").val()
		};

		// Esegui la chiamata AJAX
		$.ajax({
			type: 'POST',
			url: '/protected/modules/home/home_action.php',
			data: data,
			dataType: 'json',
			success: function(response) {
				var importoFormattato = response.currentTotal.replace(".", ",");
				var importoFormattatoPrev = response.previousTotal.replace(".", ",");
				$("#entrateValue").html(importoFormattato);
				$("#entrateValuePrev").html(importoFormattatoPrev);
				checkEndLoading();
			},
			error: function(xhr, status, error) {
				$("#entrateValue").html("-");
				$("#entrateValuePrev").html("-");
				checkEndLoading();
				console.log('Errore nella chiamata AJAX:', error);
			}
		});
	}
	

	function tour(periodo) {
		$('#layer_nero2').css('display','inline');
		$('#brain_loading').css('display','inline');
		// Imposta i parametri per la chiamata AJAX
		var data = {
			action: 'tour',
			periodo: periodo,
			da: $("#daLibera").val(),
			a: $("#aLibera").val(),
			barca: $("#barca").val(),
			tipoNoleggio: $("#tipoNoleggio").val(),
			areaLavoro: $("#areaLavoro").val()
		};

		// Esegui la chiamata AJAX
		$.ajax({
			type: 'POST',
			url: '/protected/modules/home/home_action.php',
			data: data,
			dataType: 'json',
			success: function(response) {
				$("#tourValue").html(response.currentTotal);
				$("#tourValuePrev").html(response.previousTotal);
				checkEndLoading();
			},
			error: function(xhr, status, error) {
				$("#tourValue").html("-");
				$("#tourValuePrev").html("-");
				console.log('Errore nella chiamata AJAX:', error);
			}
		});
	}
	
	function giornaliera() {
		periodoSelezionato = 'giorno';
		
		prenotazioni('giorno');
		entrate('giorno');
		grafoPrenotazioni('giorno');
		<?php if($user->GestoreId == 1) { ?>
			tour('giorno');
		<?php } ?>
		$('#dateLibera').hide();
		
		$("#giornoButton").removeClass("btn-outline-gray").addClass("btn-primary");
		$("#settimanaButton").removeClass("btn-primary").addClass("btn-outline-gray");
		$("#meseButton").removeClass("btn-primary").addClass("btn-outline-gray");
		$("#liberaButton").removeClass("btn-primary").addClass("btn-outline-gray");
		
	}
	
	function settimanale() {
		periodoSelezionato = 'settimana';
		
		prenotazioni('settimana');
		entrate('settimana');
		grafoPrenotazioni('settimana');
		<?php if($user->GestoreId == 1) { ?>
			tour('settimana');
		<?php } ?>
		$('#dateLibera').hide();
		
		$("#settimanaButton").removeClass("btn-outline-gray").addClass("btn-primary");
		$("#giornoButton").removeClass("btn-primary").addClass("btn-outline-gray");
		$("#meseButton").removeClass("btn-primary").addClass("btn-outline-gray");
		$("#liberaButton").removeClass("btn-primary").addClass("btn-outline-gray");
	}
	 
	function mensile() {
		periodoSelezionato = 'mese';
		
		prenotazioni('mese');
		entrate('mese');
		grafoPrenotazioni('mese');
		<?php if($user->GestoreId == 1) { ?>
			tour('mese');
		<?php } ?>
		$('#dateLibera').hide();
		
		$("#liberaButton").removeClass("btn-primary").addClass("btn-outline-gray");
		$("#meseButton").removeClass("btn-outline-gray").addClass("btn-primary");
		$("#giornoButton").removeClass("btn-primary").addClass("btn-outline-gray");
		$("#settimanaButton").removeClass("btn-primary").addClass("btn-outline-gray");
	}
	
	function libera() {
		periodoSelezionato = 'libera';
		
		prenotazioni('libera');
		entrate('libera');
		grafoPrenotazioni('libera');
		<?php if($user->GestoreId == 1) { ?>
			tour('libera');
		<?php } ?>	
		
		$('#dateLibera').css("display", "contents"); 
		$("#liberaButton").removeClass("btn-outline-gray").addClass("btn-primary");
		$("#giornoButton").removeClass("btn-primary").addClass("btn-outline-gray");
		$("#settimanaButton").removeClass("btn-primary").addClass("btn-outline-gray");
		$("#meseButton").removeClass("btn-primary").addClass("btn-outline-gray");
	}
	
	function aggiornaGrafi() {
		prenotazioni(periodoSelezionato);
		entrate(periodoSelezionato);
		grafoPrenotazioni(periodoSelezionato);
		<?php if($user->GestoreId == 1) { ?>
			tour(periodoSelezionato);
		<?php } ?>
	}

	$(document).ready(function() {
		periodoSelezionato = 'giorno';
		
		$('#barca').change(function(){
			if ($('#barca').val() == "") {
				$('#tipoNoleggio').removeAttr('disabled').removeAttr('readonly');
				$('#areaLavoro').removeAttr('disabled').removeAttr('readonly');
			} else {
				$('#tipoNoleggio').val("");
				$('#areaLavoro').val("");
				$('#tipoNoleggio').attr('disabled', 'disabled').attr('readonly', 'readonly');
				$('#areaLavoro').attr('disabled', 'disabled').attr('readonly', 'readonly');
			}
		});
		
		$('#tipoNoleggio, #areaLavoro').change(function(){
			if ($('#tipoNoleggio').val() == "" && $('#areaLavoro').val() == "") {
				$('#barca').removeAttr('disabled').removeAttr('readonly');
			} else {
				$('#barca').val("");
				$('#barca').attr('disabled', 'disabled').attr('readonly', 'readonly');
			}
		});
		
		prenotazioni('giorno');
		<?php if($user->GestoreId == 1) { ?>
			tour('giorno');
		<?php } ?>
		entrate('giorno');
		grafoPrenotazioni('giorno');
		
	});
</script>








<?php
 
}

if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);

    if(!isset($_REQUEST['do'])){
    	$do='';
    } else {
    	$do=$_REQUEST['do'];
    }

    switch($do) {
		default:
    		show_list();
    	break;
	}

}
// se l'utente non è loggato
else {
	header("Location: /logout.php");
}
?>
