<?php
include_once('c_path_include.php');
ini_set('display_errors', 1);

/* Configurazione log */
//$file = '/var/log/csreisen_c_emissioneTitoliAutomatici.log';
$time_start = microtime(true);

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] START\n', FILE_APPEND | LOCK_EX);
/* LOG */

$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$dt_include=$basepath.'/protected/classes/class.DT.php';

include_once($config_include);
include_once($database_include);
include_once($dt_include);

global $user;
global $db;

$config = new Config();
$run = $config->loadCron($type);
  
$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";

include_once ($classespath_ . "Graph/Graph.php");
include_once($classespath_."class.Operatore.php");
include_once($classespath_."class.StoricoOperazioni.php");
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
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.PagamentoTipo.php");
include_once($classespath_."class.PrefissoTelefono.php");
include_once ($classespath_ . "Graph/class.DisponibilitaGraph.php");
include_once ($classespath_ . "Graph/class.GrafoTratte.php");
include_once ($classespath_ . "Graph/class.GraphUtil.php");
include_once($classespath_."class.ServiceWhatsapp.php");
include_once($classespath_."class.ServiceFatturaInCloud.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_ . "class.ServiceFiscalGateway.php");
include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');

$db = new Database();
$db->connect();

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] include e connessione db eseguita\n', FILE_APPEND | LOCK_EX);
/* LOG */

$currentDate = date('Y-m-d H:i:s');

$sql = "SELECT p.PrenotazioneId, pt.CodiceTransazione, p.OpeIns, pt.TipoPagamentoId FROM RT_Prenotazione p
		INNER JOIN RT_PrenotazioneTransazione pt ON (p.PrenotazioneId = pt.PrenotazioneId)
		WHERE (p.PrenotazioneStato = 1 OR p.PrenotazioneStato = 11 OR p.PrenotazioneStato = 13 OR (p.PrenotazioneStato = 3 AND p.TotaleResiduo > 0)) AND  
		(pt.payment_status = 'Completed' OR pt.payment_status = 'APPROVED' OR pt.payment_status = 'GENEHMIGT')";
$rows = $db->fetch_array($sql);

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] eseguita query per recupero prenotazioni\n', FILE_APPEND | LOCK_EX);
/* LOG */

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] inizio for each prenotazioni\n', FILE_APPEND | LOCK_EX);
/* LOG */

foreach ($rows as $prenotazioneRow){
    // recupero l'utente della prenotazione
    $user = getUser($prenotazioneRow['PrenotazioneId']);
        
	$prenotazione_wizard = new Prenotazione($prenotazioneRow['PrenotazioneId']);
	
	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	$prenotazione = $prenotazione_wizard->DatiGenerali;
	$totaliImporti = $prenotazione_wizard->GetTotaliPrenotazione();
	
	$totale_residuo = $totaliImporti['TotaleResiduo'];
	$totale_prenotazione = $totaliImporti['TotaleDaPagare'];
	$CodiceTransazione = $prenotazioneRow['CodiceTransazione'];
	$TipoPagamentoId = $prenotazioneRow['TipoPagamentoId'];
        
	//inserisco il movimento
	$movimentoId = aggiungiPrenotazioneMovimento($prenotazione, $totale_prenotazione, $CodiceTransazione, $totale_residuo, $TipoPagamentoId);
	
	//emetto biglietti
	if($prenotazione['PrenotazioneStato'] != 3){
		if (!$prenotazione['Multi']) {
			$prenotazione_wizard->EmettiBiglietti($prenotazione['PrenotazioneId']);
		} else {
			$sql = "SELECT PrenotazioneId FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $prenotazione['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $prenotazione['PrenotazioneStato'] . " AND Cancella = 0";
			$prenotazioni = $db->fetch_array($sql);
		
			foreach ($prenotazioni as $prenotazione) {
				$prenotazione_wizard->EmettiBiglietti($prenotazione['PrenotazioneId']);
			}
		}
		//emissione fattura
		$sql = "SELECT * FROM FatturaInCloudFatturare WHERE PrenotazioneId = ".$prenotazione['PrenotazioneId'];
		$row = $db->query_first($sql);
		if(isset($row['FatturaId'])) {
		    emettiFattura($prenotazione['PrenotazioneId'], $row);
		}

		//emissione ricevuta se è stata effettuata la prenotazione da web
		if($movimentoId && $prenotazione['ScontrinoInvioWeb'] == 1) {
			//emissione scontrino digitale
			fiscalGatewayEmettiRicevuta($movimentoId);
			//invio via email dello scontrino del pagamento effettuato
			$dataMovimento = ['ScontrinoNotifica' => 1];
			$result = $db->update("RT_PrenotazioneMovimento", $dataMovimento, "PrenotazioneMovimentoId = " . $movimentoId);
		}
	} else {
		$prenotazione_wizard->EmettiBigliettiExtra($totale_residuo);
	}
	
	//aggiorno posti disponibilita
	$NumeroTotalePax = $prenotazione ['TotalePaxPrenotati'];
	$sql = "select * from RT_PrenotazionePercorso where PrenotazioneId = ".$prenotazione['PrenotazioneId']." and Direzione = 'A'";
	$percorsoA = $db->query_first($sql);
	$sql = "select * from RT_PrenotazionePercorso where PrenotazioneId = ".$prenotazione['PrenotazioneId']." and Direzione = 'R'";
	$percorsoR = $db->query_first($sql);
	aggiornaDisponibilita($percorsoA['CorsaDataPartenza'], $percorsoA['CorsaId']);
	if ($prenotazione['TipoViaggioId'] == 2) {
		aggiornaDisponibilita($percorsoR['CorsaDataPartenza'], $percorsoR['CorsaId']);
	}
	
	//invio notifica whatsapp
	if(Config::$notificaWhatsapp){
		$whatsapp = new ServiceWhatsapp($db);
		$whatsapp->invioWhatsapp($prenotazione['PrenotazioneId'], $user);
	}
	
}


/*********COUPON ***************/
$sql = "SELECT p.CouponId, pt.CodiceTransazione, p.OpeIns, pt.TipoPagamentoId 
		FROM RT_Coupon p
		INNER JOIN RT_CouponTransazione pt ON (p.CouponId = pt.CouponId)
		WHERE p.VenditaStato = 1  AND  
		(pt.payment_status = 'Completed' OR pt.payment_status = 'APPROVED' OR pt.payment_status = 'GENEHMIGT')";
echo $sql;
$rows = $db->fetch_array($sql);

$storico = new StoricoOperazioni();
$storico->conn = $db;

foreach ($rows as $couponRow){
	// recupero l'utente della prenotazione
    $user = getUserByCoupon($couponRow['CouponId']);

	//aggiorna lo stato del coupon
    $data['VenditaStato'] = 2;
	$data = $storico->operazioni_update($data, $user);
	$result = $db->update("RT_Coupon", $data, "CouponId=".$couponRow['CouponId']);

}


/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] fine for each prenotazioni\n', FILE_APPEND | LOCK_EX);
/* LOG */

$db->close();

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] chiusura database\n', FILE_APPEND | LOCK_EX);
/* LOG */

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] END - Tempo esecuzione: ' . microtime(true) - $time_start . '\n', FILE_APPEND | LOCK_EX);
/* LOG */

// ELENCO FUNZIONI
/* versione singola */
function aggiungiPrenotazioneMovimento($prenotazione, $totale_prenotazione, $codiceTransazione, $residuo = null, $TipoPagamentoId = null) {
	global $user, $db;
	
	if($TipoPagamentoId == 1){
		$TipoPagamentoId = "5";
	} else if($TipoPagamentoId == 22){
	    $TipoPagamentoId = "22";
	}
	
	if(isset($residuo) && $residuo > 0){
		$importo = $residuo;
		$causale = "Pagamento per emissione titolo.";
	} else {
		$importo = $residuo;
		$causale = "Pagamento anticipato per acquisto biglietto.";
	}
	
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$movimento = array();
	$movimento['PrenotazioneId'] = $prenotazione['PrenotazioneId'];
	if($prenotazione['OpeIns'] == 13 || $prenotazione['OpeIns'] == 42){ 
		$movimento['PagamentoTipoId'] = $TipoPagamentoId;
	} else {
		$movimento['PagamentoTipoId'] = $TipoPagamentoId;
	}
	$movimento['TipoMovimento'] = "I";
	$movimento['Causale'] = $causale;
	$movimento['CodicePagamento'] = $codiceTransazione;
	$movimento['CanalePagamentoId'] = "4";
	$movimento['Data'] = date("Y-m-d H:i:s");
	$movimento['Importo'] = $importo;
	$movimento['Supplemento'] = 0;
	$movimento['ImportoPagato'] = $importo;
	$movimento['DataPagamento'] = date("Y-m-d H:i:s");
	$movimento['Scadenza'] = "0000-00-00";

	$movimento = $storico->operazioni_insert($movimento, $user);
	$result = $db->insert("RT_PrenotazioneMovimento", $movimento);
	
	//se l'insert � andata a buon fine calcolo la somma degli importi dei movimenti
	$output = array();
	$output['prenotazioneId'] = $movimento['PrenotazioneId'];
	if($result) {
		$movimentoId = $result;
		$output['result'] = true;

		//aggiorno la prenotazione
		if ($prenotazione['Multi']) {
			$prenotazione_new['TotaleDaPagareMulti'] = $totale_prenotazione;
		} else {
			$prenotazione_new['TotaleDaPagare'] = $totale_prenotazione;
		}
		$prenotazione_new['TotalePagato'] = $totale_prenotazione;
		$prenotazione_new['TotaleResiduo'] = ($movimento['Importo'] == 0)? $importo : 0;
		$prenotazione_new['TipoPagamentoId'] = $movimento['PagamentoTipoId'];
		// se il pagamento � "A bordo" imposta pagato uguale a 2
		if ($movimento['PagamentoTipoId'] == "7") {
			$prenotazione_new['Pagato'] = intval(2);
		} else {
			$prenotazione_new['Pagato'] = intval($prenotazione['TotaleResiduo'] <= 0);
		}
		$prenotazione_new['ScadenzaPrenotazione'] = $movimento['Scadenza'];

		$prenotazione_new = $storico->operazioni_update($prenotazione_new, $user);
		$result = $db->update("RT_Prenotazione", $prenotazione_new, "PrenotazioneId=".$prenotazione['PrenotazioneId']);

		//aggiorno le prenotazioni correlate
		aggiornaPrenotazioneMovimentoCorrelate($prenotazione['CodicePrenotazione'], $movimento, $prenotazione_new);
		
		//rimuovo il movimento contabile se presente un movimento di tipo P
		if(isset($residuo) && $residuo > 0){
			$data['stato'] = 0;
			$data['cancella'] = 1;
			$result = $db->update("RT_PrenotazioneMovimento", $data, "TipoMovimento = 'P' AND PrenotazioneId=".$prenotazione['PrenotazioneId']);
		}
		return $movimentoId;
	} else {
		$output['result'] = false;
		return false;
	}
}

/* versione multi prenotazione */
function aggiornaPrenotazioneMovimentoCorrelate($codicePrenotazione, $movimento, $prenotazioneRoot) {
	global $user, $db;

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	//seleziono tutte le prenotazioni correlate
	$sql = "SELECT PrenotazioneId, TotalePrenotazione FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $codicePrenotazione . "' AND PrenotazioneId != '" . $movimento['PrenotazioneId'] . "'";
	$prenotazioni = $db->fetch_array($sql);

	foreach ($prenotazioni as $prenotazione) {
		$prenotazione_new['TotaleResiduo'] = ($prenotazioneRoot['TotalePagato'] == 0)? floatval($prenotazione['TotalePrenotazione']) : 0;
		$prenotazione_new['TipoPagamentoId'] = $movimento['PagamentoTipoId'];
		// se il pagamento � "A bordo" imposta pagato uguale a 2
		if ($movimento['PagamentoTipoId'] == "7") {
			$prenotazione_new['Pagato'] = intval(2);
		} else {
			$prenotazione_new['Pagato'] = intval($prenotazione_new['TotaleResiduo'] <= 0);
		}
		$prenotazione_new['ScadenzaPrenotazione'] = $prenotazioneRoot['ScadenzaPrenotazione'];

		$prenotazione_new = $storico->operazioni_update($prenotazione_new, $user);
		$result = $db->update("RT_Prenotazione", $prenotazione_new, "PrenotazioneId=".$prenotazione['PrenotazioneId']);
	}
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

	$grafo = new DisponibilitaGraph($corsa['LineaId'], $corsaId, $data, $db, 200);
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

/* prendo i dati dell'utente */
function getUser($prenotazioneId) {
    global $db;
    
    $sql = "SELECT OpeIns,SedeIns,GestoreIdRef,OdcIdRef FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
    
    $row = $db->query_first($sql);
    $operatoreId = $row['OpeIns'];
    
    $user = new Operatore();
    $user->conn = $db;
    
    $user->OperatoreId=$row['OpeIns'];
    $user->GestoreId=$row['GestoreIdRef'];
    $user->SedeId=$row['SedeIns'];
    $user->OdcId=$row['OdcIdRef']; 
    
    return $user;
    //$user->inizializza($operatoreId);
    
}

function getUserByCoupon($couponId) {
    global $db;
    
    $sql = "SELECT OpeIns,SedeIns,GestoreIdRef,OdcIdRef FROM RT_Coupon WHERE CouponId = $couponId";
    echo $sql;
    $row = $db->query_first($sql);
    $operatoreId = $row['OpeIns'];
    
    $user = new Operatore();
    $user->conn = $db;
    
    $user->OperatoreId=$row['OpeIns'];
    $user->GestoreId=$row['GestoreIdRef'];
    $user->SedeId=$row['SedeIns'];
    $user->OdcId=$row['OdcIdRef']; 
    
    return $user;
    //$user->inizializza($operatoreId);
    
}

function emettiFattura($prenotazioneId, $dati) {
    global $db;
    
    $tipo = 'invoice';
    
    $prenotazioneObj = new Prenotazione();
    $prenotazioneObj->Id = $prenotazioneId;
    $prenotazioneObj->conn = $db;
    $prenotazioneObj->inizializzaDatiGenerali();
    $prenotazione = $prenotazioneObj->DatiGenerali;
    $totaliImporti = $prenotazioneObj->GetTotaliPrenotazione();
    
    $sql = "Select m.*, t.PagamentoTipo from RT_PrenotazioneMovimento m
            left join RT_PagamentoTipo t on t.PagamentoTipoId = m.PagamentoTipoId
            where PrenotazioneId = $prenotazioneId";
    $movimento = $db->query_first($sql);
    $movimentoId = $movimento['PrenotazioneMovimentoId'];
    $sql = "SELECT Max(Progressivo) as Ultimo
                FROM FatturaInCloudViaggiatore
                WHERE Tipo = '$tipo'";
    $temp1 = $db->query_first($sql);
    if(!isset($temp1['Ultimo'])) {
        $progressivo = 1;
    } else {
        $progressivo = $temp1['Ultimo'] +1;
    }
    $fatturaNumero = $progressivo."-OB/".date('Y');
    $siglaFattura = '-OB';
    
    $nome = $dati['Nome'];
    $indirizzo_via = $dati['IndirizzoVia'];
    $indirizzo_cap = $dati['IndirizzoCap'];
    $indirizzo_provincia = $dati['IndirizzoProvincia'];
    $indirizzo_citta = $dati['IndirizzoCitta'];
    $indirizzo_stato = $dati['Paese'];
    $paese = $dati['Paese'];
    $paese_iso = $dati['PaeseISO'];
    $lingua = 'it';
    $piva = $dati['PIVA'];
    $cf = $dati['CF'];
    $pec = $dati['PEC'];
    $codice_destinatario = $dati['CodiceDestinatario'];
    $email = $dati['Email'];
    $tel = $dati['Tel'];
    $fax = $dati['Fax'];
    $articolo_quantita = 1;
    $articolo_nome = "Biglietti di viaggio";
    $sqlT = "Select * from RT_PrenotazioneTitolo WHERE PrenotazioneId = $prenotazioneId AND TipoTitolo = 'E' AND Stato = 1 AND Cancella = 0";
    $rowsT = $db->fetch_array($sqlT);
    $articolo_nota = "";
    foreach ($rowsT as $t) {
        if(isset($t['Codice'])) {
            $articolo_nota .= $t['Codice'].',';
        }
    }
    $articolo_nota = rtrim($articolo_nota, ',');
    $articolo_prezzo_netto = abs($movimento['ImportoPagato']) / 1.1;
    $articolo_prezzo_lordo = abs($movimento['ImportoPagato']);
    $pagamento_importo = abs($movimento['ImportoPagato']);
    $pagamento_data_scadenza = date('Y-m-d');
    $pagamento_data_saldo = date('Y-m-d');
    $fa_data = date('Y-m-d');
    $statoFattura = 'SALDATO';
    $service = new ServiceFatturaInCloud($db);
    $result = $service->inviaFatturaCliente($nome, $indirizzo_via, $indirizzo_cap, $indirizzo_provincia, $indirizzo_citta,
        $paese, $paese_iso, $lingua, $piva, $cf, $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto,
        $articolo_prezzo_lordo, $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
        $email, $tel, $fax, $fa_data, $fatturaNumero, $progressivo, $siglaFattura, $tipo, $statoFattura,
        $prenotazione['CodicePrenotazione'], $prenotazioneId, $movimentoId, null, $movimento['PagamentoTipo']);
    $result = ['PrenotazioneId' => $prenotazioneId, 'CorsaId' => ""];
    
    return $result;
}

function fiscalGatewayEmettiRicevuta($movimentoId){
    global $db, $user;
    $service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);
    
    //recupero movimento
    $sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
    $movimento = $db->query_first($sql);
    
    //recupero prenotazione
    $sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
    $prenotazione = $db->query_first($sql);
    
    //recupero dell'ordine scontrino
    $sql = "SELECT MAX(ScontrinoId) AS max FROM RT_PrenotazioneMovimento WHERE ScontrinoTipo = 1";
    $orderRow = $db->query_first($sql);
    if(isset($orderRow['max'])) {
        $orderId = intval($orderRow['max']) + 1;
    } else {
        $orderId = 13;
    }
    $orderId = strval($orderId);
    
    //recupero tipo pagamento
    switch ($movimento['PagamentoTipoId']) {
        case 1:
            $paymentMethodsType = 'CASH'; // Contanti
            break;
        case 2:
            $paymentMethodsType = 'CARD'; // Postapay
            break;
        case 3:
            $paymentMethodsType = 'CARD'; // Carta di credito su POS fisico
            break;
        case 4:
            $paymentMethodsType = 'BANK_TRANSFER'; // Bonifico Bancario
            break;
        case 5:
            $paymentMethodsType = 'CARD'; // PayPal (considerato pagamento elettronico)
            break;
        case 7:
            $paymentMethodsType = 'CASH'; // A bordo (NO RICEVUTA) 
            break;
        case 12:
            $paymentMethodsType = 'CARD'; // Coupon (NO RICEVUTA) 
            break;
        case 22:
            $paymentMethodsType = 'CARD'; // Stripe (pagamento con carta)
            break;
        case 23:
            $paymentMethodsType = 'CARD'; // Pagamento in hotel (ipotizziamo contanti)
            break;
        default:
            $paymentMethodsType = 'CASH'; // Caso predefinito per nuovi/metodi non mappati
    }
    
    //recupero importo
    $amount = (int) round(floatval($movimento['ImportoPagato']) * 100);

    //verifico se non ci sono rimborsi emessi che vanno a modificare l'importo totale pagato e registrato
    $sqlRimborsi = "select SUM(ImportoPagato) as rimborso from RT_PrenotazioneMovimento WHERE TipoMovimento = 'R' and PrenotazioneId = ".$movimento['PrenotazioneId'];
    $rimborsoRow = $db->query_first($sqlRimborsi);
    
    if(isset($rimborsoRow['rimborso']) && $rimborsoRow['rimborso'] <= 0) {
        $amount = (int) round((floatval($movimento['ImportoPagato']) + floatval($rimborsoRow['rimborso'])) * 100);
    }

    //recupero prodotto
    $productId = $prenotazione['CodicePrenotazione'];
    
    // Tentativo di emissione ricevuta con retry in caso di orderId già esistente
    $maxAttempts = 10;
    $attempts = 0;
    $currentOrderId = $orderId;
    $result = null;
    
    while ($attempts < $maxAttempts) {
        //invio richiesta emissione ricevuta con Fiscal Gateway
        $result = $service->postBillReceipt($currentOrderId, $paymentMethodsType, $amount, $productId, Config::$fiscalGatewayVAT);
        
        // Se la richiesta è andata a buon fine, esce dal loop
        if (isset($result['status_code'], $result['response']['success']) &&
            $result['status_code'] === 200 &&
            $result['response']['success'] === true) {
            break;
        }
        
        // Se il bill esiste già (error code 107), incrementa orderId e riprova
        if (isset($result['response']['success']) && 
            $result['response']['success'] === false &&
            isset($result['response']['error']['code']) &&
            $result['response']['error']['code'] === 107) {
            $currentOrderId = strval(intval($currentOrderId) + 1);
            $attempts++;
        } else {
            // Per altri tipi di errore, esce dal loop
            break;
        }
    }
    
    if (isset($result['status_code'], $result['response']['success']) &&
        $result['status_code'] === 200 &&
        $result['response']['success'] === true) {
        //salvataggio info scontrino in PrenotazioneMovimento
        $data = [
            'ScontrinoId' => $currentOrderId,
            'ScontrinoData' => date("Y-m-d H:i:s"),
            'ScontrinoTipo' => '1',
        ];
        $resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
        return true;
    } else {
        return false;
    }
}

?>