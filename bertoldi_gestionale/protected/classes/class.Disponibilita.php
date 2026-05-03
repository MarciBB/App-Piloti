<?php
$classespath_ = $basepath."/protected/classes/";
include_once ($classespath_ . "Graph/class.GraphUtil.php");
include_once ($classespath_ . "Graph/class.DisponibilitaGraph.php");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class
 *
 * @author Antonio
 */
class Disponibilita {
//put your code here
    
    
    function __construct() {
	
	}
    
    
    
    
    public function aggiornaDisponibilita($corsaId, $data, $conn = null){
		global $db;
		if(isset($conn)){
			$db = $conn;
		}
	
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
    
    
    
    
}
