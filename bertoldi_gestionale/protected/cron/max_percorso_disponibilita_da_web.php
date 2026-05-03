<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
//$basepath=$_SERVER['DOCUMENT_ROOT'];
//include_once($basepath."/main_include.php");
 ini_set('display_errors', 0);
 ini_set('error_reporting', E_ERROR);
 ini_set('max_execution_time', 36000); //300 seconds = 5 

$modulespath_ = $basepath."/protected/modules/";
$classespath_ = $basepath."/protected/classes/";
include_once ($classespath_ . "/class.Gestore.php");
include_once ($classespath_ . "/class.Sede.php");
include_once ($classespath_ . "/class.Fermata.php");
include_once ($classespath_ . "/class.Tratta.php");
include_once ($classespath_ . "/class.Prenotazione.php");
include_once ($classespath_ . "/class.Corsa.php");
include_once ($classespath_ . "/class.Linea.php");
include_once ($classespath_ . "/class.Percorso.php");
include_once ($classespath_ . "/class.Orario.php");
include_once ($classespath_ . "/class.Listino.php");
include_once ($classespath_ . "/class.TipologiaBus.php");
include_once($classespath_."/Graph/class.LineaGraph.php");
include_once ($classespath_ . "/class.PrenotazioneMovimento.php");
include_once ($classespath_ . "/Graph/class.DisponibilitaGraph.php");

$db = new Database ();
$db->connect ();

$CorsaAndata=1;

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
WHERE rt_corsa.Cancella = 0 
AND rt_appcalendario.AppCalendarioData >= rt_corsa.AttivaDal 
AND rt_appcalendario.AppCalendarioData <= rt_corsa.AttivaAl 
AND (rt_appcalendario.Feriale = rt_corsa.IncludIFeriale OR rt_appcalendario.Prefestivo = rt_corsa.IncludiPrefestivo OR rt_appcalendario.Festivo = rt_corsa.IncludIFestivo)
AND rt_appcalendario.AppCalendarioData >= DATE_SUB(NOW(), INTERVAL 5 DAY)
AND rt_appcalendario.AppCalendarioData <= DATE_SUB(NOW(), INTERVAL - 10 DAY)
GROUP BY rt_corsa.CorsaId, rt_appcalendario.AppCalendarioData 
HAVING PostiRealmentePrenotati > 0
ORDER BY rt_appcalendario.AppCalendarioData, rt_linea.PercorsoId, rt_linea.LineaNome, rt_corsa.CorsaNome";

$corse=$db->fetch_array($sql);
// echo count($corse);
foreach ($corse as $corsa){
	$totPostiBus = $corsa['PostiTotali'];
    $postiRP=$corsa['PostiRealmentePrenotati'];
        $tot1=0;
	$sql = "DELETE FROM RT_MaxDisponibilitaPostiCron WHERE LineaId = ".$corsa['LineaId']." and CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'";
	$r=$db->query($sql);
	
//  	echo "LineaId = ".$corsa['LineaId']." and CorsaId = ".$corsa['CorsaId']." DataPartenza = '".$corsa['AppCalendarioData']."<br>";

	$grafo = new DisponibilitaGraph($corsa['LineaId'], $corsa['CorsaId'], $corsa['AppCalendarioData'], $db, 200);
// 	$now = date("Y-m-d H:i:s");
// 	echo "fine grafo $now<br>"; 
	
	foreach($grafo->graph->nodes as $k=>$pickup){
		if(isset($grafo->gruppiDispo[$k])){ 
                    $tot=$grafo->gruppiDispo[$k]->totalePasseggeri;
                    if ($tot>$tot1)
                        $tot1=$tot;
// 			$tot = $grafo->gruppiDispo[$k]->totalePasseggeri + $grafo->graph->nodes[$k]->salite;
			
                       
		} 
	}

        
        if ($tot1>0)
        {
        	$sql = "select * from RT_DisponibilitaPostiCron
        	where Posti = $tot1 and CorsaId = ".$corsa['CorsaId']." and 
        			DataPartenza = '".$corsa['AppCalendarioData']."' 
        	order by PesoTratta desc";
        	$row2 =  $db->query_first($sql);
        	$peso = $row2['PesoTratta'];
        	
//         	echo $sql."<br>";
        	$km = $row2['KmInizioTratta'];
        	$trattaId = $row2['TrattaId'];
        	if(isset($peso)){
	        	$sql = "SELECT Max(Posti) as postiM, c.* FROM RT_DisponibilitaPostiCron c 
				where CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'  
				and PesoTratta = $peso 
				and c.Posti > 0 and c.Posti<>". $tot1 ." and TrattaId <> $trattaId group by TrattaId 
				order by postiM desc";
// 	        	echo $sql."<br>";
	        	
				$row3 = $db->fetch_array($sql);
				$postiOccupatiTratta = 0;
// 				$km = 0;
				foreach ($row3 as $num => $val){
					if($val['postiM']<$tot1/2){
					$sql = "SELECT Posti, KmInizioTratta FROM RT_DisponibilitaPostiCron c
							where CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'
							and TrattaId  = ".$val['TrattaId']."
							and c.Posti = ".$val['postiM']."
							group by TrattaId";
// 					echo $sql."<br>"; 
					$row4 = $db->fetch_array($sql);

					if($row4[0]['KmInizioTratta']>$km-0 && $row4[0]['KmInizioTratta']<$km+0)
						$postiOccupatiTratta += $row4[0]['Posti'];

					}
				}
				
				$tot1 += $postiOccupatiTratta;
// 				if($tot1 > $totPostiBus){
// 					$tot1 = $totPostiBus;
// 				}
        	}
//         	echo "<br><br>";
//         	echo $tot1;
			if($tot1 > $totPostiBus && $tot1 >$postiRP){
				$tot1 = $postiRP;
			}
        	
            $sql = "INSERT INTO RT_MaxDisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti,PostiRP) VALUES
					(".$corsa['LineaId'].",".$corsa['CorsaId'].", '".$corsa['AppCalendarioData']."',".$k.",
							".$tot1.",".$postiRP." )";
            $db->query($sql);
        }
        unset($grafo);
//         echo "fine inserimento <br>";
}
$db->close();
echo("aggiornamento completato");
// CREATE TABLE `csreisen`.`RT_DisponibilitaPostiCron` (
// 		`DisponibilitaPostiId` INT NOT NULL AUTO_INCREMENT,
// 		`LineaId` INT(11) NULL,
// 		`CorsaId` INT(11) NULL,
// 		`DataPartenza` DATE NULL,
// 		`Comune` INT(11) NULL,
// 		`Posti` INT(11) NULL,
// 		PRIMARY KEY (`DisponibilitaPostiId`));
          
        
          
        
?>

