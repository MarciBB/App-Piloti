<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/stile_prenotazioni.css" />
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."/class.TipologiaBus.php");
include_once($classespath_."/class.DT.php");
include_once ($classespath_ . "Graph/class.DisponibilitaGraph.php");
include_once ($classespath_ . "Graph/class.LineaGraph.php");
include_once ($classespath_ . "/class.PrenotazioneMovimento.php");
include_once ($classespath_ . "Graph/class.GrafoTratte.php");
global $ModuloId;
$ModuloId=7;// modulo base mediazione
global $user;
global $prenotazione_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$prenotazione_wizard=null;

if(isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
$prenotazione_wizard=unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
}




function exportExcel(){
	global $user,$HtmlCommon,$ModuloId;
	$db= new Database();
	$db->connect();
	
	$CorsaId=$_REQUEST['CorsaId'];
	$CorsaIdA=$CorsaId;
	$DataCorsa=$_REQUEST['DataCorsa'];
	$Order=$_REQUEST['Order'];
	
	$sql = "SELECT StatoPrenotazione as 'Stato Prenotazione', 
			RagioneSociale as Agenzia,
			NumeroBiglietto as 'Num. Biglietto', 
			Cliente,
			ClienteCellulare as Cellulare,
			OrarioPartenza as 'Orario Fermata',
			ComunePartenza as Partenza,
			ComuneArrivo as Arrivo,
			TipoViaggio as 'Tipo Viaggio',
			Importo as Totale
			From RT_ViewBigliettiPrenotati
			Where CorsaId=$CorsaId and DataPartenza ='".$DataCorsa."'";
	
	$data = $db->fetch_array($sql);
	$filename = "corsa.xls";
	
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: application/vnd.ms-excel");
	
	$flag = false;
	foreach($data as $row) {
		if(!$flag) {
			// display field/column names as first row
			echo implode("\t", array_keys($row)) . "\n";
			$flag = true;
		}
		array_walk($row, 'cleanData');
		echo implode("\t", array_values($row)) . "\n";
	}
	
	$db->close();
}



function show_list() {
   
	global $user,$HtmlCommon,$ModuloId, $dizionario, $db;
	$HtmlCommon->html_titolo_pagina($dizionario['biglietto']['titolo_prenotatu_su_corsa'],0,"","");
	$HtmlCommon->html_titolo_box($dizionario['biglietto']['titolo_prenotatu_su_corsa']);
	$db= new Database();
	$db->connect();
	
	$CorsaId=$_REQUEST['CorsaId'];
	$DataCorsa=$_REQUEST['DataCorsa'];
	
	aggiornaDisponibilita($CorsaId, $DataCorsa);
	
	include_once("biglietto_validator.php");           

	
	$sql = "select LineaId FROM RT_Corsa where CorsaId = $CorsaId";
	$linea = $db->query_first($sql);
	$lineaId = $linea['LineaId'];
	
	$sql = "select TrattaId, TrattaNome from RT_Tratta where LineaId = $lineaId AND Stato = 1 AND Cancella = 0 order by TrattaNome";
	$tratte = $db->fetch_array($sql);
?>   

<script>
	$(function() {
		$( "#tabs_posti" ).tabs();
               
});
</script>
         
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
    	<tr class="brain_tabellaTr">
       		<th width="70%"><?=$dizionario['tratta']['tratta']?></th>
            <th><?=$dizionario['partenza']['pt']?></th>
			<th><?=$dizionario['partenza']['pd']?></th>           
		</tr>
	</thead>
	<tbody>
         <?php foreach($tratte as $tratta){?>
         	<?php 
         	$sql = "SELECT * FROM RT_Fermata WHERE TrattaId = ".$tratta['TrattaId']." AND Stato = 1 AND Cancella = 0";
         	 
         	$fermate = $db->fetch_array($sql);
         	$first = true;
         	$f = new Fermata();
         	$f->conn = $db;
         	$string = "";
         	foreach ($fermate as $fermata){
         		if(!$f->isInterscambioLinea($lineaId, $fermata['ComuneId'])){
         			$sql = "SELECT * FROM RT_Orario o 
							LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
							where f.TrattaId = ".$tratta['TrattaId']." and o.CorsaId = $CorsaId 
							and f.ComuneId = ".$fermata['ComuneId']." 
							and o.Stato = 1 and o.Cancella = 0
							and f.Stato = 1 and f.Cancella = 0 and o.Orario IS NOT NULL;";
         			$checkOra= $db->fetch_array($sql);
         			if(count($checkOra) > 0){
	         			if($first){
	         				$string .= $fermata['ComuneId'];
	         				$first = false;
	         			} else {
	         				$string .= ','.$fermata['ComuneId'];
	         			}
         			}
         		}
         	}
//          	echo $tratta['TrattaId']." : ".$string."<br><br>";
         	
         	if($string == ''){
         		$tempR['Posti'] = 0;
         	} else {
         		$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
         				where CorsaId = $CorsaId and DataPartenza = '$DataCorsa' and Comune IN ($string) ";
         		$tempR = $db->query_first($sql);
         	}
         	if(isset($tempR['Posti'])){
         			
         		$postiOccupati = $tempR['Posti'];
         			
         		$sql = "SELECT TipologiaBusDefaultId from RT_Tratta c
											WHERE TrattaId = ".$tratta['TrattaId'];
         		$check = $db->query_first($sql);
         		if(isset($check['TipologiaBusDefaultId']) && $check['TipologiaBusDefaultId']>0) {
         			$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
         			(select IFNULL((select SUM(c1.NumeroPax)
         			from RT_CorsaPaxTratta c1
         			where
         			c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataCorsa' and c1.TrattaId = ".$tratta['TrattaId']." and c1.OdcIdRef = 1
												    group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
												   ) AS `PostiTotali`
												from RT_Tratta c
												join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
												where c.TrattaId = ".$tratta['TrattaId'];
         			$tempR1 = $db->query_first($sql);
         		} else {
         			$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
         			(select IFNULL((select SUM(c1.NumeroPax)
         			from RT_CorsaPax c1
         			where
         			c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataCorsa' and c1.OdcIdRef = 1
         			group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0))
         			) AS `PostiTotali`
         			from RT_Corsa c
         			join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
         			where c.CorsaId = $CorsaId";
         			$tempR1 = $db->query_first($sql);
         		}
         	
         		$disponibili = intval($tempR1['PostiTotali']) - intval($postiOccupati);
         		$totali = intval($tempR1['PostiTotali']);
         			
         	} else {
         		$sql = "select IFNULL((select
         		count(0)
         		from
         		`RT_PrenotazionePercorso`
         		join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
         		join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
         		and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
         		and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
         		join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
         		left join `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
         		left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
         		where
         		((`RT_Prenotazione`.`Cancella` = 0)
         		and (`RT_PrenotazionePercorso`.`Cancella` = 0)
         		and (`RT_PrenotazionePercorso`.`Stato` = 1)
         		and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
         		and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
         		and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
         		and (`tb`.`OccupaPosto` = 1))
         		and `RT_PrenotazionePercorso`.`CorsaId` = $CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataCorsa'
         		group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0) as PostiRealmentePrenotati";
         		$tempR1 = $db->query_first($sql);
         		if(isset($tempR1['PostiRealmentePrenotati'])){
         			$postiRealmentePrenotati = $tempR1['PostiRealmentePrenotati'];
         		} else {
         			$postiRealmentePrenotati = 0;
         		}
         			
         			
         		$sql = "select IFNULL((select SUM(c1.NumeroPax)
         		from RT_CorsaPaxTratta c1
         		where
         		c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataCorsa' and c1.OdcIdRef = 1 and c1.TrattaId =.". $tratta['TrattaId']."
         		group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
         		$tempR = $db->query_first($sql);
         		if(!isset($tempR['PostiAggiunti'])){
         			$sql = "select IFNULL((select SUM(c1.NumeroPax)
         			from RT_CorsaPax c1
         			where
         			c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataCorsa' and c1.OdcIdRef = 1
         			group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
         			$tempR = $db->query_first($sql);
         		}
         		if(isset($tempR['PostiAggiunti'])){
         			$postiCorsaAggiunti = $tempR['PostiAggiunti'];
         		} else {
         			$postiCorsaAggiunti = 0;
         		}
         			
         		$sql = "Select b.TotalePosti
         		from RT_TipologiaBus b
         		left join RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
         		where c.CorsaId = $CorsaId";
         		$tempR = $db->query_first($sql);
         		$postiCorsaDefault = $tempR['TotalePosti'];
         			
         		$disponibili = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
         		$totali = $postiCorsaDefault + $postiCorsaAggiunti;
         	}
         	
         	?>
        	<tr>
				<td><?php echo $tratta['TrattaNome'];?></td>
				<td><?php echo $totali;?></td>
				<td><?php echo $disponibili;?></td>
			</tr> 
         <?php } ?>
	</tbody>
	
</table>
<?   
$db->close();

}


function aggiornaDisponibilita($corsaId, $data){
	global $db;

	$sql = "SELECT
	rt_corsa.CorsaId AS CorsaId,
	rt_appcalendario.AppCalendarioData AS AppCalendarioData,
	DATE_FORMAT(rt_appcalendario.AppCalendarioData, _utf8 '%d/%m/%Y') AS DataPartenzaFormattata,
	rt_corsa.CorsaNome AS CorsaNome,
	rt_corsa.LineaId AS LineaId,
	rt_linea.LineaNome AS LineaNome,
	rt_corsa.OrarioPartenza AS OrarioPartenza,
	IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti),0,rt_viewsingolacorsapostiaggiunti.PostiAggiunti) AS PostiAggiunti,
	IF(ISNULL(rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati),0,rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati) AS TotalePrenotati,
	IF(ISNULL(rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati),0,rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati) AS PostiRealmentePrenotati,
	(rt_tipologiabus.TotalePosti + IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti),0,rt_viewsingolacorsapostiaggiunti.PostiAggiunti)) AS PostiTotali,
	((rt_tipologiabus.TotalePosti + IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti),0,rt_viewsingolacorsapostiaggiunti.PostiAggiunti)) - IF(ISNULL(rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati),0,rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati)) AS PostiRealmenteDisponibili,
	IF(ISNULL(rt_corsabloccoweb.CorsaBloccoId),0,1) AS CorsaWebBloccata,
	IF(ISNULL(rt_corsablocco.CorsaBloccoId),0,1) AS CorsaBloccata,
	rt_corsaconsolidamento.DataIns AS DataConsolidamentoF,
	rt_corsainiziopreparazione.DataIns AS DataInizializzazioneF,
	rt_corsa.OdcIdRef AS OdcIdRef,
	rt_corsa.GestoreIdRef AS GestoreIdRef
	FROM RT_Corsa AS rt_corsa
	JOIN RT_CorsaSettimana AS rt_corsasettimana ON(rt_corsa.CorsaId = rt_corsasettimana.CorsaId)
	JOIN RT_AppSettimana AS rt_appsettimana ON (rt_corsasettimana.SettimanaId = rt_appsettimana.AppSettimanaId)
	JOIN RT_AppCalendario AS rt_appcalendario ON (rt_appsettimana.AppSettimanaGiorno = rt_appcalendario.GiornoSettimana)
	JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
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
	AND (rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazionedettaglio.Escludi = 0)
	OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 1)
	OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 0 AND rt_prenotazione.ScadenzaPrenotazione > rt_prenotazionedettaglio.DataPartenza)
	OR (rt_prenotazione.ABordo = 1 AND rt_prenotazione.PrenotazioneStato <> 6 AND rt_prenotazione.PrenotazioneStato <> 4 AND rt_prenotazione.PrenotazioneStato <> 7 AND rt_prenotazione.PrenotazioneStato <> 3)
	OR (rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazione.ABordo = 1 AND rt_prenotazionedettaglio.Escludi = 0)
	GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
	) AS rt_viewsingolacorsapostiprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiprenotati.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostiprenotati.CorsaDataPartenza)
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
	JOIN RT_TipologiaBus AS rt_tipologiabus ON (rt_corsa.TipologiaBusDefaultId = rt_tipologiabus.TipologiaBusId)
	LEFT JOIN RT_CorsaBloccoWeb AS rt_corsabloccoweb ON (rt_corsa.CorsaId = rt_corsabloccoweb.CorsaId AND rt_appcalendario.AppCalendarioData = rt_corsabloccoweb.DataPartenza)
	LEFT JOIN RT_CorsaBlocco AS rt_corsablocco ON (rt_corsa.CorsaId = rt_corsablocco.CorsaId AND rt_appcalendario.AppCalendarioData = rt_corsablocco.DataPartenza)
	LEFT JOIN RT_CorsaInizioPreparazione AS rt_corsainiziopreparazione ON(rt_corsa.CorsaId = rt_corsainiziopreparazione.CorsaId AND rt_appcalendario.AppCalendarioData = rt_corsainiziopreparazione.DataCorsa)
	LEFT JOIN RT_CorsaConsolidamento AS rt_corsaconsolidamento ON (rt_corsa.CorsaId = rt_corsaconsolidamento.CorsaId AND rt_appcalendario.AppCalendarioData = rt_corsaconsolidamento.DataCorsa)
	WHERE rt_corsa.CorsaId = $corsaId and rt_appcalendario.AppCalendarioData = '$data'";

	$corsa=$db->query_first($sql);


	$sql = "DELETE FROM RT_DisponibilitaPostiCron WHERE LineaId = ".$corsa['LineaId']." and CorsaId = ".$corsaId." and DataPartenza = '".$data."'";
	$db->query($sql);

	$grafo = new DisponibilitaGraph($corsa['LineaId'], $corsaId, $data, $db);
	foreach($grafo->graph->nodes as $k=>$pickup){
		$sql = "select t.TrattaId, t.TrattaPeso, KmInizioTratta from RT_Fermata f
				left join RT_Tratta t on t.TrattaId = f.TrattaId
	 			left join RT_Orario o on o.FermataId = f.FermataId
				where t.LineaId = ".$corsa['LineaId']." and f.ComuneId = $k
				and f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
				and o.CorsaId = $corsaId and o.Orario is not null and o.Stato = 1 and o.Cancella = 0
				order by TrattaPeso asc, f.ImportanzaTratta desc";
		$tempRow = $db->query_first($sql);
		if(isset($grafo->gruppiDispo[$k])){
			$sql = "INSERT INTO RT_DisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti, TrattaId, PesoTratta, KmInizioTratta) VALUES
					(".$corsa['LineaId'].",".$corsaId.", '".$data."',".$k.",
							".$grafo->gruppiDispo[$k]->totalePasseggeri.",  ".$tempRow['TrattaId'].", ".$tempRow['TrattaPeso'].", ".$tempRow['KmInizioTratta'].")";
			$db->query($sql);
		} else {
			$tot = 0;
			$sql = "INSERT INTO RT_DisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti, TrattaId, PesoTratta, KmInizioTratta) VALUES
					(".$corsa['LineaId'].",".$corsaId.", '".$data."',".$k.",
					$tot, ".$tempRow['TrattaId'].", ".$tempRow['TrattaPeso'].", ".$tempRow['KmInizioTratta'].")";
			$db->query($sql);
		}
	}


	$totPostiBus = $corsa['PostiTotali'];
	$postiRP=$corsa['PostiRealmentePrenotati'];
	$tot1=0;
	$sql = "DELETE FROM RT_MaxDisponibilitaPostiCron WHERE LineaId = ".$corsa['LineaId']." and CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'";
	$r=$db->query($sql);


	foreach($grafo->graph->nodes as $k=>$pickup){
		if(isset($grafo->gruppiDispo[$k])){
			$tot=$grafo->gruppiDispo[$k]->totalePasseggeri;
			if ($tot>$tot1)
				$tot1=$tot;
			// 			$tot = $grafo->gruppiDispo[$k]->totalePasseggeri + $grafo->graph->nodes[$k]->salite;


		}
	}

	if ($tot1>0){
		$sql = "select * from RT_DisponibilitaPostiCron
		where Posti = $tot1 and CorsaId = ".$corsa['CorsaId']." and
        			DataPartenza = '".$corsa['AppCalendarioData']."'
        	order by PesoTratta desc";
		$row2 =  $db->query_first($sql);
		$peso = $row2['PesoTratta'];

		$km = $row2['KmInizioTratta'];
		$trattaId = $row2['TrattaId'];
		if(isset($peso)){
			$sql = "SELECT Max(Posti) as postiM, c.* FROM RT_DisponibilitaPostiCron c
				where CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'
				and PesoTratta = $peso
				and c.Posti > 0 and c.Posti<>". $tot1 ." and TrattaId <> $trattaId group by TrattaId
				order by postiM desc";

			$row3 = $db->fetch_array($sql);
			$postiOccupatiTratta = 0;

			foreach ($row3 as $num => $val){
				if($val['postiM']<$tot1/2){
					$sql = "SELECT Posti, KmInizioTratta FROM RT_DisponibilitaPostiCron c
							where CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'
							and TrattaId  = ".$val['TrattaId']."
							and c.Posti = ".$val['postiM']."
							group by TrattaId";

					$row4 = $db->fetch_array($sql);

					if($row4[0]['KmInizioTratta']>$km-50 && $row4[0]['KmInizioTratta']<$km+50)
						$postiOccupatiTratta += $row4[0]['Posti'];
				}
			}

			$tot1 += $postiOccupatiTratta;

		}

		$sql = "INSERT INTO RT_MaxDisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti,PostiRP) VALUES
					(".$corsa['LineaId'].",".$corsa['CorsaId'].", '".$corsa['AppCalendarioData']."',".$k.",
							".$tot1.",".$postiRP." )";
		$db->query($sql);

	}
}


if(is_object($user)) {
    
/*      ID - FUNZIONE
1	Lista
2	Aggiunta
3	Cancellazione
4	Modifica
5	Esportazione
6	Importazione
7	Stampa
 */ 
 
    
    
    
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
	    if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }
		
		
			switch($do) {
				default:
                                    $FunzioneId=1;
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            show_list();
                                        else
                                           $errore->stampa_errore(2);
                               
                         		// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
			}
		
	} // end verifica permessi
	else {
           $errore->stampa_errore(1);
            
        }

} 
// se l'utente non e' loggato
else {
header("Location: /logout.php");
}
?>