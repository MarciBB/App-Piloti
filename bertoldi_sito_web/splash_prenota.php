<?php
//Autore: Marco Casaburi
//Data ultima modifica: 17/05/2024
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
use Stripe\Stripe;

$PageTitle = $dizionario['conferma']['titolo'];
$PageDescription = "";
$PageKeywords = "";
$page_title = $PageTitle;

$config = new Config();
$run = $config->load();
$classespath_ = Config::$classespath;
$db = new Database();
$conn = $db->connect();

//lo Stato della prenotazione è impostato di default ad IN ATTESA DI PAGAMENTO WEB (11)
$PrenotazioneStato = 11;
//die("blocco forzato");

ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL);

if(isset($_SESSION['USER'])) {
	$userInfo = $_SESSION['USER'];
} else {
	$userInfo = null;
}
if(!isset($_SESSION['tipo_tour']) || !isset($_SESSION['CorsaId']) ) {
	// Effettua il redirect alla pagina index.php
	header("Location: /index.php");
	exit; // Termina lo script per evitare l'esecuzione di ulteriori istruzioni
}

?>
<html>
<head>
<?php
//if(isset($_SESSION['CURRENT_SEARCH'])) {
	$classespath_ = $basepath."/protected/classes";
	include_once($basepath . "/include/meta.php");
	include_once($basepath . "/include/ipg-util.php");
//}
if(isset($_POST['fattura']) && $_POST['fattura'] == 1) {
    $fattura = true;
} else {
    $fattura = false;
}
?>
</head>
<body class="main-bg" id="nuova-prenotazione">
<?php

$posti_disponibili = true;

$tempPickup = explode('_',$_SESSION['PickupId']);
$tempDroppoff = explode('_',$_SESSION['DropoffId']);

if(isset($_SESSION['CorsaId']) && isset($_SESSION['seleziona_lesperienza'])) {
	 
	$criteri = [
		'TipoPercorsoId' => 1,
		'TipoTour' => $_SESSION['tipo_tour'],
		'TipoViaggioId' => $_SESSION['tipo_viaggio'],
		'LineaId' => $_SESSION['seleziona_lesperienza'],
		'CorsaId' => $_SESSION['CorsaId'],
		'DataPartenza' => $_SESSION['ticket_date'],
		'DataPartenzaAndata' => $_SESSION['ticket_date'],
		'ComuneDropOffId' => $tempDroppoff[0],
		'ComuneIdDropOff' => $tempDroppoff[0],
		'ComuneDropOff' => $_SESSION['Dropoff'],
		'ComunePickUpId' => $tempPickup[0],
		'ComuneIdPickup' => $tempPickup[0],
		'ComunePickUp' => $_SESSION['Pickup'],
		'FermataPickUp' => $tempPickup[1],
		'DataPickUp' => $_SESSION['ticket_date'],
		'OrarioPickUp' => $_SESSION['start_time'],
		'FermataDropOff' => $tempDroppoff[1],
		'DataDropOff' => $_SESSION['ticket_date'],
		'OrarioDropOff' => $_SESSION['end_time'],
		'DataPartenzaRitorno' => (($_SESSION['tipo_viaggio'] == 2) ? $_SESSION['ticket_date'] : null),
		'CorsaIdRitorno' => (($_SESSION['tipo_viaggio'] == 2) ? $_SESSION['CorsaIdR'] : null),
	];

	$gestore = $_SESSION['gestore'];

	$scontrinoInvio = $_SESSION['scontrinoInvio']; // 0=RICEVUTA, 1=FATTURA

	//recupero se la prenotazione e' da confermare
	$daConfermare = false;
	if(isset($_SESSION['da_confermare'])) {
		$daConfermare = $_SESSION['da_confermare']; //true = da confermare, false = da pagare
	}
	if(isset($_SESSION['da_confermareR'])) {
		$daConfermare = $daConfermare || $_SESSION['da_confermareR'];
	}
	//se la prenotazione e' da confermare, lo stato della prenotazione e' impostato a 2 (Da confermare)
	if($daConfermare) {
		$PrenotazioneStato = 2;
	}

	$search = new WebSearch($criteri);

	//$search = unserialize($_SESSION['CURRENT_SEARCH']);
	$search->conn = $db;
	$arr = $search->ArrSearch;
	$search->getCorseDisponibiliByDateGrafo('A', 'A', null, $arr['CorsaId']); 
	$search->ItinerarioIdAndata = 0;
	if($_SESSION['tipo_viaggio'] == 2) {
		$search->getCorseDisponibiliByDateGrafo('R', 'R', null, $arr['CorsaIdRitorno']); 
		$search->ItinerarioIdRitorno = 0;
	}

	// recupero i dati che arrivano dal search
	if($arr['TipoViaggioId'] == 3){
		$TipoViaggioId = 2;
		$arr['TipoViaggioId'] = 2;
		$open = true;
	} else {
		$TipoViaggioId = $arr['TipoViaggioId'];
		$open = false;
	}
	$ElencoCorseAndata = $search->ArrCorseAndata;
	$ItAndata = $search->ItinerarioIdAndata;
	
	$PercorsoAndata = $ElencoCorseAndata[$ItAndata];

	if (!$open && $arr['TipoViaggioId'] == 2) {
		$ElencoCorseRitorno = $search->ArrCorseRitorno;
		$ItRitorno = $search->ItinerarioIdRitorno;
		$PercorsoRitorno = $ElencoCorseRitorno[$ItRitorno];
	} else if($open) {
		//recupero le informazioni del ritorno open
		if($PercorsoAndata['LineaId'] == 1){
			$PercorsoRitorno['LineaId'] = 2;
		} else if ($PercorsoAndata['LineaId'] == 2){
			$PercorsoRitorno['LineaId'] = 1;
		} else if ($PercorsoAndata['LineaId'] == 13) {
			$PercorsoRitorno['LineaId'] = 14;
		} else if ($PercorsoAndata['LineaId'] == 14) {
			$PercorsoRitorno['LineaId'] = 13;
		} else if ($PercorsoAndata['LineaId'] == 16) {
			$PercorsoRitorno['LineaId'] = 17;
		} else {
			$PercorsoRitorno['LineaId'] = 16;
		}
		
		$sql= "Select * from RT_Corsa where LineaId = ".$PercorsoRitorno['LineaId']." AND RitornoAperto = 1";
		$rowCorsa = $db->query_first($sql);
		$PercorsoRitorno['CorsaId'] = $rowCorsa['CorsaId'];
		
		$PercorsoRitorno['ComunePickUpId'] = $PercorsoAndata['ComuneDropOffId'];
		$PercorsoRitorno['ComunePickUp'] = $PercorsoAndata['ComuneDropOff'];
		$sql = "SELECT FermataId, FermataNome FROM RT_Fermata f
				left join RT_Tratta t on t.TrattaId = f.TrattaId
				where f.ComuneId = ".$PercorsoRitorno['ComunePickUpId']." and t.LineaId = ".$PercorsoRitorno['LineaId']." and f.IsPickup = 1 
				and f.IsBlackList = 0 and f.Stato = 1 and f.Cancella = 0
				and t.Stato = 1 and t.Cancella = 0
				order by f.ImportanzaTratta desc";
		$rowFermata = $db->query_first($sql);
		$PercorsoRitorno['FermataPickUpId'] = $rowFermata['FermataId'];
		$PercorsoRitorno['FermataPickUp'] = $rowFermata['FermataPickUp'];
		
		$PercorsoRitorno['ComuneDropOffId'] = $PercorsoAndata['ComunePickUpId'];
		$PercorsoRitorno['ComuneDropOff'] = $PercorsoAndata['ComunePickUp'];
		$sql = "SELECT FermataId, FermataNome FROM RT_Fermata f
				left join RT_Tratta t on t.TrattaId = f.TrattaId
				where f.ComuneId = ".$PercorsoRitorno['ComuneDropOffId']." and t.LineaId = ".$PercorsoRitorno['LineaId']." and f.IsDropOff = 1
				and f.IsBlackList = 0 and f.Stato = 1 and f.Cancella = 0
				and t.Stato = 1 and t.Cancella = 0
				order by f.ImportanzaTratta desc";
		$rowFermata = $db->query_first($sql);
		$PercorsoRitorno['FermataDropOffId'] = $rowFermata['FermataId'];
		$PercorsoRitorno['FermataDropOff'] = $rowFermata['FermataPickUp'];
		
		$PercorsoRitorno['km'] = $PercorsoAndata['km'];
		$PercorsoRitorno['Prezzo'] = $PercorsoAndata['Prezzo'];
		$PercorsoRitorno['DataPickUp'] = '2050-1-1';
		$PercorsoRitorno['OrarioPickUp'] = '00:00:00';
		$PercorsoRitorno['DataPartenza'] = '2050-1-1';
		$PercorsoRitorno['DataDropOff'] = '2050-1-1';
		$PercorsoRitorno['OrarioDropOff'] = '00:00:00';
	}
	
	if($open) {
	    $scontoAndataRitorno = 0;
	} else {
	    $scontoAndataRitorno = $search->ScontoAndataRitorno;
	}
	
	
	$DataPartenzaAndata = $arr['DataPartenzaAndata'];
	$DataPartenzaRitorno = $arr['DataPartenzaRitorno'];
	
	$importoTotale_finale = $_SESSION['total_price'];
	$orarioPartenzaAndata = $_SESSION['start_time'];
	
	//controllo codice coupon e calcolo residuo
	// GESTIONE MULTI-COUPON - VALIDAZIONE SINGOLA
	$isCoupon = false;
	$importo_coupon = 0;
	$resto_coupon = 0;
	$importoResiduo = $importoTotale_finale;
	$couponList = array();

	if (isset($_SESSION['coupons']) && is_array($_SESSION['coupons']) && count($_SESSION['coupons']) > 0) {
		foreach ($_SESSION['coupons'] as $coupon) {
			$codice_coupon = $coupon['codice'];
			$sql = "SELECT * FROM RT_Coupon WHERE Codice = '".$db->escape($codice_coupon)."' AND MaxUtilizzi > Utilizzi";
			$rowCoupon = $db->query_first($sql);

			if(isset($rowCoupon['CouponId'])){
				$resultA = true;
				$resultR = true;
				$trattaA = true;
				$trattaR = true;
				$messageA = 'OK';
				$messageR = 'OK';
				if(!isset($DataPartenzaRitorno)){
					$resultR = true;
				}

				// --- Fasce orarie check ---
				$fasceOrarie = array();
				$sqlFasce = "SELECT OraInizio as Dalle, OraFine as Alle FROM RT_CouponFasciaOraria WHERE CouponId = " . $rowCoupon['CouponId'];
				$fasceRows = $db->fetch_array($sqlFasce);
				foreach($fasceRows as $fascia) {
					// Dalle/Alle sono in formato 'HH:MM'
					$fasceOrarie[] = array('dalle' => $fascia['Dalle'], 'alle' => $fascia['Alle']);
				}

				// Funzione di utilità per il check
				function isOrarioInFasce($orario, $fasce) {
					foreach($fasce as $fascia) {
						// Normalizza i formati aggiungendo :00 se mancano i secondi
						$orario_norm = strlen($orario) == 5 ? $orario . ':00' : $orario;
						$dalle_norm = strlen($fascia['dalle']) == 5 ? $fascia['dalle'] . ':00' : $fascia['dalle'];
						$alle_norm = strlen($fascia['alle']) == 5 ? $fascia['alle'] . ':00' : $fascia['alle'];
						
						if ($orario_norm >= $dalle_norm && $orario_norm <= $alle_norm) {
							return true;
						}
					}
					return false;
				}

				// Se ci sono fasce orarie, controllo l'orario di partenza
				if(count($fasceOrarie) > 0) {
					// Andata
					$orarioAndata = isset($orarioPartenzaAndata) ? $orarioPartenzaAndata : null;
					if($orarioAndata && !isOrarioInFasce($orarioAndata, $fasceOrarie)) {
						$resultA = false;
						$messageA = "Fascia oraria non valida";
					}
					// Ritorno
					$orarioRitorno = isset($orarioPartenzaAndata) ? $orarioPartenzaAndata : null;
					if($orarioRitorno && !isOrarioInFasce($orarioRitorno, $fasceOrarie)) {
						$resultR = false;
						$messageR = "Fascia oraria non valida";
					}
				} 

				//verifico se la data di partenza > di validaDa
				if(isset($rowCoupon['ValidoDa']) && $rowCoupon['ValidoDa'] != '0000-00-00'){
					$partenza = new DateTime($DataPartenzaAndata);
					$partenza->setTime(0, 0, 0);
					$datetime = new DateTime($rowCoupon['ValidoDa']);
					$datetime->setTime(0, 0, 0);
					if($partenza < $datetime){
						$resultA = false;
					}
					if(isset($DataPartenzaRitorno)) {
						$partenza = new DateTime($DataPartenzaRitorno);
						$partenza->setTime(0, 0, 0);
						$datetime = new DateTime($rowCoupon['ValidoDa']);
						$datetime->setTime(0, 0, 0);
						if($partenza < $datetime){
							$resultR = false;
						}
					}
				}
				//verifico se la data di partenza < di validaA
				if(isset($rowCoupon['ValidoA']) && $rowCoupon['ValidoA'] != '0000-00-00'){
					$partenza = new DateTime($DataPartenzaAndata);
					$partenza->setTime(0, 0, 0);
					$datetime = new DateTime($rowCoupon['ValidoA']);
					$datetime->setTime(0, 0, 0);
					if($partenza > $datetime){
						$resultA = false;
					}
					if(isset($DataPartenzaRitorno)) {
						$partenza = new DateTime($DataPartenzaRitorno);
						$partenza->setTime(0, 0, 0);
						$datetime = new DateTime($rowCoupon['ValidoA']);
						$datetime->setTime(0, 0, 0);
						if($partenza > $datetime){
							$resultR = false;
						}
					}
				}
				//verifico il comune di partenza
				if(isset($row['PartenzaId']) && $row['PartenzaId']!=0){
					// Nuova logica: diverso e non contenuto come valore
					$partenzaIds = array_map('trim', explode(',', $row['PartenzaId']));
					if($row['PartenzaId'] != $PercorsoAndata['ComunePickUpId'] && !in_array($PercorsoAndata['ComunePickUpId'], $partenzaIds)){
						$resultA = false;
						$messageA = "Fermata di partenza non valida";
					}
					if(isset($DataPartenzaRitorno) && ($row['PartenzaId'] != $PercorsoRitorno['ComunePickUpId'] && !in_array($PercorsoRitorno['ComunePickUpId'], $partenzaIds))){
						$resultR = false;
						$messageR = "Fermata di partenza non valida";
					}
				}
				
				//verifico il comune di destinazione
				if(isset($row['DestinazioneId']) && $row['DestinazioneId']!=0){
					$destinazioneIds = array_map('trim', explode(',', $row['DestinazioneId']));
					if($row['DestinazioneId'] != $PercorsoAndata['ComuneDropOffId'] && !in_array($PercorsoAndata['ComuneDropOffId'], $destinazioneIds)){
						$resultA = false;
						$messageA = "Fermata di destinazione non valida";
					}
					if(isset($DataPartenzaRitorno) && ($row['DestinazioneId'] != $PercorsoRitorno['ComuneDropOffId'] && !in_array($PercorsoRitorno['ComuneDropOffId'], $destinazioneIds))){
						$resultR = false;
						$messageR = "Fermata di destinazione non valida";
					}
				}

				
				if(isset($dataRitorno) && ($trattaA || $trattaR)
						&& (strpos($messageA, 'Fermata') !== false
								|| strpos($messageR, 'Fermata') !== false)){
					$resultA = true;
					$resultR = true;
				}
				//verifico la linea
				if(isset($rowCoupon['LineaId']) && $rowCoupon['LineaId'] != 0){
					if($rowCoupon['LineaId'] == $PercorsoAndata['LineaId'] || strpos($rowCoupon['LineaId']. ',', $PercorsoAndata['LineaId'] . ',') === false){
						$resultA = false;
						$resultR = false;
						$messageA = "Esperienza / tour non valido";
					}
				}
				//verifico il gestore
				if(isset($rowCoupon['GestoreId']) && $rowCoupon['GestoreId']!=0){
					if($PercorsoAndata['GestoreIdRef'] != $rowCoupon['GestoreId']){
						$resultA = false;
						$resultR = false;
						$messageA = "Gestore non valido";
					}
				}

				// Se valido, calcola valore e aggiungi a couponList
				if($resultA && $resultR) {
					$isCoupon = true;
					if(isset($rowCoupon['Importo']) && isset($rowCoupon['Valore']) && $rowCoupon['Valore'] > 0) {
						$valore_coupon = floatval($rowCoupon['Valore']);	
					} else {
						$valore_coupon = 0;
					}
					if ($valore_coupon == 0 && isset($rowCoupon['Percentuale'])) {
						$valore_coupon = round($importoTotale_finale * $rowCoupon['Percentuale'] / 100, 1);
					}
					$importo_coupon += $valore_coupon;
					$couponList[] = array('codice' => $codice_coupon, 'valore' => $valore_coupon);
				}
			}
		}
		$importoResiduo = $importoTotale_finale - $importo_coupon;
		if ($importoResiduo < 0) {
			$resto_coupon = abs($importoResiduo);
			$importoResiduo = 0;
		}
	}

    //controllo sicurezza su importo finale
	if (!($importoTotale_finale > 0))
		die($dizionario['prenota']['errore']);
	
	//Ricontrollo l'effettiva disponibilit� dei posti
	// calcolo il numero di biglietti acquistato
	$totale_pax = 0;
	$biglietti = $_SESSION['BigliettoTipologiaPax'];
	foreach ($biglietti as $bigliettoId => $numero) {
		$sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = $bigliettoId";
		$tipo = $db->query_first($sql);
		if($tipo['OccupaPosto'] == 1) {
			$totale_pax += $numero;
		}
	}
	
	//Andata
	$lId = $PercorsoAndata['LineaId'];
	$cId = $PercorsoAndata['CorsaId'];
	$CorsaId = $PercorsoAndata['CorsaId'];
	$DataPartenza = $PercorsoAndata['DataPartenza'];
	$dId = $PercorsoAndata['DataPartenza'];
	$ComunePartenzaId = $PercorsoAndata['ComunePickUpId'];
	$ComuneDestinazioneId = $PercorsoAndata['ComuneDropOffId'];

	//calcolo disponibilita
	$trattaPartenza = null;
	$trattaArrivo = null;
	$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, true);
	$TrattePercorse = $grafo->getTratte(null, $trattaPartenza, $trattaArrivo);
	 
	$string = '';
	$f = new Fermata();
	$f->conn = $db;
	$first = true;
	foreach($grafo->flotta as $flotta){
			
		foreach ($flotta->comuni as $c => $comune){
	
			if(!$f->isInterscambioLinea($lId, $comune['comune'])){
				if($first){
					$string .= $comune['comune'];
					$first = false;
				} else {
					$string .= ','.$comune['comune'];
				}
			}
		}
	}
	$tempR = null;
	if($string != '') {
		$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
		where CorsaId = $cId and DataPartenza = '$dId' and Comune IN ($string) ";
		
		$tempR = $db->query_first($sql);
	}
	
	$sql = "Select b.TotalePosti
		from RT_TipologiaBus b
		left join RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
		where c.CorsaId = $cId";
	$tempR1 = $db->query_first($sql);
	$postiCorsaDefault = $tempR1['TotalePosti'];
		
	if(isset($tempR['Posti'])){
		$postiOccupati = $tempR['Posti'];
			
		$sql = "Select TrattaId from RT_DisponibilitaPostiCron
		where CorsaId = $cId and DataPartenza = '$dId' and Posti = $postiOccupati and Comune IN ($string)";
		$tratta =  $db->query_first($sql);
			
		$sql = "SELECT TipologiaBusDefaultId from RT_Tratta c
							WHERE TrattaId = ".$tratta['TrattaId'];
		$check = $db->query_first($sql);
		if(isset($check['TipologiaBusDefaultId']) && $check['TipologiaBusDefaultId'] > 0) {
			$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
			(select IFNULL((select SUM(c1.NumeroPax)
			from RT_CorsaPaxTratta c1
			where
			c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.TrattaId = ".$tratta['TrattaId']." and c1.OdcIdRef = 1
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
			c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.OdcIdRef = 1
			group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0))
			) AS `PostiTotali`
			from RT_Corsa c
			join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
			where c.CorsaId = $cId";
			$tempR1 = $db->query_first($sql);
		}
	
		$disponibiliAndata = intval($tempR1['PostiTotali']) - intval($postiOccupati);
		
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
		and `RT_PrenotazionePercorso`.`CorsaId` = $CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataPartenza'
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
		c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1 and c1.TrattaId = $trattaPartenza
		group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
		$tempR = $db->query_first($sql);
		if(!isset($tempR['PostiAggiunti'])){
			$sql = "select IFNULL((select SUM(c1.NumeroPax)
			from RT_CorsaPax c1
			where
			c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1
			group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
			$tempR = $db->query_first($sql);
		}
		if(isset($tempR['PostiAggiunti'])){
			$postiCorsaAggiunti = $tempR['PostiAggiunti'];
		} else {
			$postiCorsaAggiunti = 0;
		}
				
		$disponibiliAndata = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
		
	}

	//controllo posti inizio tratta
	$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK
	WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$ComunePartenzaId and TrattaStato=1 order by TrattaPeso desc ";
	$arr_fermate=$db->fetch_array($sql);
	$trattaPartenza = $arr_fermate[0]['TrattaId'];
	
	$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
	where CorsaId = $cId and DataPartenza = '$CorsaId' and TrattaId = $trattaPartenza ";
	$tempR = $db->query_first($sql);
	if(isset($tempR['Posti'])) {
		$tempOccupatiInizio = $tempR['Posti'];
	} else {
		$tempOccupatiInizio = 0;
	}
	$sql = "Select ($postiCorsaDefault +
	(select IFNULL((select SUM(c1.NumeroPax)
	from RT_CorsaPaxTratta c1
	where
	c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.TrattaId = ".$trattaPartenza." and c1.OdcIdRef = 1
							    group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
		   ) AS `PostiTotali`
		from RT_Tratta c
		where c.TrattaId = ".$trattaPartenza;

	$tempR1 = $db->query_first($sql);
	$tempInizioTot = $tempR1['PostiTotali'];
	$tempDisponibili = $tempInizioTot - $tempOccupatiInizio;
	if($tempDisponibili  > 0) {
		$disponibiliAndata = $tempDisponibili;
	} else {
		$disponibiliAndata = 0;
	}

	//Ritorno se presente
	if ($TipoViaggioId == 2 && !$open) {
		$lId = $PercorsoRitorno['LineaId'];
		$cId = $PercorsoRitorno['CorsaId'];
		$CorsaId = $PercorsoRitorno['CorsaId'];
		$DataPartenza = $PercorsoRitorno['DataPartenza'];
		$dId = $PercorsoRitorno['DataPartenza'];
		$ComunePartenzaId = $PercorsoRitorno['ComunePickUpId'];
		$ComuneDestinazioneId = $PercorsoRitorno['ComuneDropOffId'];
		
		//calcolo disponibilita
		$trattaPartenza = null;
		$trattaArrivo = null;
		$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, true);
		$TrattePercorse = $grafo->getTratte(null, $trattaPartenza, $trattaArrivo);
		 
		$string = '';
		$f = new Fermata();
		$f->conn = $db;
		$first = true;
		foreach($grafo->flotta as $flotta){
				
			foreach ($flotta->comuni as $c => $comune){
		
				if(!$f->isInterscambioLinea($lId, $comune['comune'])){
					if($first){
						$string .= $comune['comune'];
						$first = false;
					} else {
						$string .= ','.$comune['comune'];
					}
				}
			}
		}
		$tempR = null;
		if($string != '') {
			$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
			where CorsaId = $cId and DataPartenza = '$dId' and Comune IN ($string) ";
			
			$tempR = $db->query_first($sql);
		}
		
		$sql = "Select b.TotalePosti
			from RT_TipologiaBus b
			left join RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
			where c.CorsaId = $cId";
		$tempR1 = $db->query_first($sql);
		$postiCorsaDefault = $tempR1['TotalePosti'];
		
		if(isset($tempR['Posti'])){
			$postiOccupati = $tempR['Posti'];
				
			$sql = "Select TrattaId from RT_DisponibilitaPostiCron
			where CorsaId = $cId and DataPartenza = '$dId' and Posti = $postiOccupati and Comune IN ($string)";
			$tratta =  $db->query_first($sql);
				
			$sql = "SELECT TipologiaBusDefaultId from RT_Tratta c
							WHERE TrattaId = ".$tratta['TrattaId'];
			$check = $db->query_first($sql);
			if(isset($check['TipologiaBusDefaultId']) && $check['TipologiaBusDefaultId'] > 0) {
				$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
				(select IFNULL((select SUM(c1.NumeroPax)
				from RT_CorsaPaxTratta c1
				where
				c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.TrattaId = ".$tratta['TrattaId']." and c1.OdcIdRef = 1
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
				c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.OdcIdRef = 1
				group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0))
				) AS `PostiTotali`
				from RT_Corsa c
				join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
				where c.CorsaId = $cId";
				$tempR1 = $db->query_first($sql);
			}
	
			$disponibiliRitorno = intval($tempR1['PostiTotali']) - intval($postiOccupati);
				
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
			and `RT_PrenotazionePercorso`.`CorsaId` = $CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataPartenza'
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
			c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1 and c1.TrattaId = $trattaPartenza
			group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
			$tempR = $db->query_first($sql);
			if(!isset($tempR['PostiAggiunti'])){
				$sql = "select IFNULL((select SUM(c1.NumeroPax)
				from RT_CorsaPax c1
				where
				c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1
				group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
				$tempR = $db->query_first($sql);
			}
			if(isset($tempR['PostiAggiunti'])){
				$postiCorsaAggiunti = $tempR['PostiAggiunti'];
			} else {
				$postiCorsaAggiunti = 0;
			}
				
			
				
			$disponibiliRitorno = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
				
		}
		
		//controllo posti inizio tratta
		$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK
		WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$ComunePartenzaId and TrattaStato=1 order by TrattaPeso desc ";
		$arr_fermate=$db->fetch_array($sql);
		$trattaPartenza = $arr_fermate[0]['TrattaId'];
		
		$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
		where CorsaId = $cId and DataPartenza = '$CorsaId' and TrattaId = $trattaPartenza ";
		$tempR = $db->query_first($sql);
		if(isset($tempR['Posti'])) {
			$tempOccupatiInizio = $tempR['Posti'];
		} else {
			$tempOccupatiInizio = 0;
		}
		$sql = "Select ($postiCorsaDefault +
		(select IFNULL((select SUM(c1.NumeroPax)
		from RT_CorsaPaxTratta c1
		where
		c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.TrattaId = ".$trattaPartenza." and c1.OdcIdRef = 1
							    group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
							   ) AS `PostiTotali`
							from RT_Tratta c
							where c.TrattaId = ".$trattaPartenza;
		$tempR1 = $db->query_first($sql);
		$tempInizioTot = $tempR1['PostiTotali'];
		$tempDisponibili = $tempInizioTot - $tempOccupatiInizio;
		if($tempDisponibili  > 0) {
			$disponibiliRitorno = $disponibiliRitorno;
		} else {
			$disponibiliRitorno = 0;
		}
		
	}
	
	
		
	if ($totale_pax > $disponibiliAndata) {
		$posti_disponibili = false;
	} elseif ($TipoViaggioId == 2 && !$open) {
		if ($totale_pax > $disponibiliRitorno) {
			$posti_disponibili = false;
		}
	} 
	
	$nome_sito = $dizionario['prenota']['piattaforma_pagamento'];
	if ($posti_disponibili) {
		// recupero i dati che arrivano della form
		switch ($_SESSION['pagamento']) {
			case "paypal":
				$metodo_pagamento = 1;
				break;
			case "stripe":
				$metodo_pagamento = 2;
				break;
			case "da_confermare":
				$metodo_pagamento = 100;
				break;
			default:
				$metodo_pagamento = 1;
				break;
		}

		$tipoPagamentoId = -1;
		if($metodo_pagamento == 2) {
		    $nome_sito="Stripe";
		    $tipoPagamentoId = 22;
		} else if($metodo_pagamento == 1) {
			$nome_sito = "PayPal";
			$tipoPagamentoId = 5;
		} else {
			$nome_sito = "Da confermare";
			$tipoPagamentoId = 0;
		}
		
		
		$bigliettiTemp = $_SESSION['BigliettoTipologiaPax'];//$_POST['biglietti'];
		$biglietti = array();
		//$passeggeri = $_POST['passeggeri'];
		
		$bigliettiNome = $_SESSION['TipoBigliettoId'];
		$passeggeri = array();
		$principale = true;
		$countPasseggeri = 0;
		$indicePasseggeroPrincipale = 0;
		foreach ($bigliettiTemp as $key => $tipoBiglietto) {
			$sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = $key";
			$tipo = $db->query_first($sql);
			for ($i = 1; $i <= $tipoBiglietto; $i++) {
				$p = array();
				$p['Cognome'] = $bigliettiNome[$key];
				$p['Nome'] = $bigliettiNome[$key];
				$p['SessoId'] = '3';
				$p['Eta'] = '18';
				$p['TipoBigliettoId'] = $key;
				if ($principale && $tipo['OccupaPosto'] == 1) {
					$p['Principale'] = 1;
					$principale = false;
					$indicePasseggeroPrincipale = $countPasseggeri;
				} else {
					$p['Principale'] = 0;
				}
				$passeggeri[] = $p;
			}
			$biglietti[$key]['numero'] = $tipoBiglietto;
			$countPasseggeri++;
		}
		$totpasseggeri = count($passeggeri);
		
		$prezzo_totale = $_SESSION['totalprice'];//$_POST['prezzo_totale'];
		
		// ricalcolo il prezzo del biglietto
		$prezzo_totale_ricalcolo = 0;
		foreach ($biglietti as $tipologiaId => $biglietto) {
			$prezzoTotaleAndata = 0;
			$prezzoTotaleRitorno = 0;
			$prezzoTotaleBiglietto = 0;
			$variazione_prezzo = 0;
			$prezzoTotaleRitornoNoSconto = 0;
			
			//trovo il motiplicatore
			$moltiplicatore = 0;
			
			//calcolo il listino id della variazione
			$listinoId = $search->GetScontoPromozioneAttivaListinoId($PercorsoAndata['CorsaId'], $DataPartenzaAndata, 1, $tipologiaId);
			
			//trovo la variazione prezzo andata
			$variazionePrezzoAndata = $search->GetScontoPromozioneAttiva($PercorsoAndata['CorsaId'], $DataPartenzaAndata, 1, $tipologiaId);
			
			//calcolo il prezzo totale andata
			$sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoID = ".$tipologiaId;
			$tipoRow = $db->query_first($sql);
			if($tipoRow['OccupaPosto'] == 1) {
				$sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = ".$PercorsoAndata['CorsaId']." AND TipologiaBigliettoId = $tipologiaId AND FermataPickup = ".$PercorsoAndata['ComunePickUpId']." AND FermataDropoff = ".$PercorsoAndata['ComuneDropOffId'];
				$tempTariffa = $db->query_first($sql);
				$prezzoTotaleAndata = $tempTariffa['Tariffa'];
			} else {
				$sql = "SELECT Prezzo FROM RT_ListinoServizi WHERE BigliettoId = ".$tipologiaId;
				$tempTariffa = $db->query_first($sql);
				$prezzoTotaleAndata = $tempTariffa['Prezzo'];
			}
			
			//applico la variazione di prezzo
			$prezzoTotaleAndata = $prezzoTotaleAndata + ($prezzoTotaleAndata * $variazionePrezzoAndata / 100);
		
			//effettua l'arrotonadamento del prezzo al 5 euro superiore
			$prezzoTotaleAndata_frac = $prezzoTotaleAndata - floor($prezzoTotaleAndata);
			//$prezzoTotaleAndata = intval($prezzoTotaleAndata);
			//if ($prezzoTotaleAndata_frac > 0) {
			//	$prezzoTotaleAndata += 1;
			//}
			$prezzoTotaleAndata = arrotondaFattore($prezzoTotaleAndata, 5);
		
			$prezzoTotaleAndataNoSconto = $prezzoTotaleAndata;
		
			if ($arr['TipoViaggioId'] == 2 && !$open) {
				//trovo la variazione prezzo ritorno
				$variazionePrezzoRitorno = $search->GetScontoPromozioneAttiva($PercorsoRitorno['CorsaId'], $DataPartenzaRitorno, 1, $tipologiaId);

				//calcolo il prezzo totale ritorno
				$sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = ".$PercorsoRitorno['CorsaId']." AND TipologiaBigliettoId = $tipologiaId AND FermataPickup = ".$PercorsoRitorno['ComunePickUpId']." AND FermataDropoff = ".$PercorsoRitorno['ComuneDropOffId'];
				$tempTariffa = $db->query_first($sql);
				$prezzoTotaleRitorno = $tempTariffa['Tariffa'];
		
				//applico la variazione di prezzo
				$prezzoTotaleRitorno = $prezzoTotaleRitorno + ($prezzoTotaleRitorno * $variazionePrezzoRitorno / 100);
				
				//effettua l'arrotonadamento del prezzo al 5 euro superiore
				$prezzoTotaleRitorno_frac = $prezzoTotaleRitorno - floor($prezzoTotaleRitorno);
				//$prezzoTotaleRitorno = intval($prezzoTotaleRitorno);
				//if ($prezzoTotaleRitorno_frac > 0) {
				//	$prezzoTotaleRitorno += 1;
				//}
				$prezzoTotaleRitorno = arrotondaFattore($prezzoTotaleRitorno, 5);
		
				$prezzoTotaleRitornoNoSconto = $prezzoTotaleRitorno;
			} else if ($arr['TipoViaggioId'] == 2 && $open) {
				$sql = "select LineaId from RT_Corsa where CorsaId = ".$PercorsoAndata['CorsaId'];
				$lineaId = $db->query_first($sql);
				
				if($lineaId['LineaId'] == 1){
                	$lineaIdR = 2;
                } else if ($lineaId['LineaId'] == 2){
                	$lineaIdR = 1;
                } else if ($lineaId['LineaId'] == 13) {
                	$lineaIdR = 14;
                } else if ($lineaId['LineaId'] == 14) {
                	$lineaIdR = 13;
                }  else if ($lineaId['LineaId'] == 16) {
                	$lineaIdR = 17;
                } else {
                	$lineaIdR = 16;
                }
				 
				$sql = "select CorsaId from RT_Corsa where LineaId = $lineaIdR and RitornoAperto = 1";
				$corsaIdR = $db->query_first($sql);
				
				//calcolo il prezzo totale ritorno open
				$sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = ".$corsaIdR['CorsaId']." AND TipologiaBigliettoId = $tipologiaId AND FermataPickup = ".$PercorsoAndata['ComuneDropOffId']." AND FermataDropoff = ".$PercorsoAndata['ComunePickUpId'];
				$tempTariffa = $db->query_first($sql);
				$prezzoTotaleRitorno = $tempTariffa['Tariffa'];
				
			}
			// calolto il totale del biglietto andata e ritorno
			$prezzoTotaleBiglietto = $prezzoTotaleAndata + $prezzoTotaleRitorno;
			//applica lo sconto se andata e ritorno
			if (($arr['TipoViaggioId'] == 2 && !$open) || ($arr['TipoViaggioId'] == 2 && $open)) {
				$prezzoTotaleBiglietto = $prezzoTotaleBiglietto - ($prezzoTotaleBiglietto * $scontoAndataRitorno / 100);
			}
		
			//effettua l'arrotonadamento del prezzo al 5 euro superiore
			$prezzoTotaleBiglietto = arrotondaFattore($prezzoTotaleBiglietto, 5);
			//$prezzoTotaleBiglietto_frac = $prezzoTotaleBiglietto - floor($prezzoTotaleBiglietto);
			//$prezzoTotaleBiglietto = intval($prezzoTotaleBiglietto);
			//if ($prezzoTotaleBiglietto_frac > 0) {
			//	$prezzoTotaleBiglietto += 1;
			//}

			
			$biglietti[$tipologiaId]['moltiplicatore'] = $moltiplicatore;
			$biglietti[$tipologiaId]['variazione'] = $variazione_prezzo;
			$biglietti[$tipologiaId]['listino_promozione'] = $listinoId;
			$biglietti[$tipologiaId]['prezzo'] = $prezzoTotaleBiglietto;
			$biglietti[$tipologiaId]['prezzoTotale'] = ($prezzoTotaleBiglietto * $biglietto['numero']);
			$biglietti[$tipologiaId]['prezzoAndataNoSconto'] = $prezzoTotaleAndataNoSconto;
			$biglietti[$tipologiaId]['prezzoRitornoNoSconto'] = $prezzoTotaleRitornoNoSconto;

			$prezzo_totale_ricalcolo += $biglietti[$tipologiaId]['prezzoTotale'];
		}

		//controllo se il metodo di pagamento � di tipo paypal. 
		// Se e' di tipo paypal aggiungo le variazioni di prezzo.
		if($tipoPagamentoId != 5){
			$importoTotale_finale = $prezzo_totale_ricalcolo;
		}else{
			$sql = "SELECT * FROM RT_PagamentoTipo WHERE PagamentoTipoId = $tipoPagamentoId";
			$row = $db->query_first($sql);
			$importoTotale_finale = ($prezzo_totale_ricalcolo * $row['SupplementoPercentuale']/100)+
								$prezzo_totale_ricalcolo+$row['SupplementoFisso'];
			$importoTotale_finale = round($importoTotale_finale,2);
		}
		
		// calcolo il numero di km andata
		$kmAndata = 0;
		foreach ($search->ArrCorseAndata[$search->ItinerarioIdAndata]['tratte'] as $km){
			$kmAndata += $km;
		}
		
		// calcolo il numero di km ritorno se presente
		$kmRitorno = 0;
		if($TipoViaggioId == 2 && !$open){
			foreach ($search->ArrCorseRitorno[$search->ItinerarioIdRitorno]['tratte'] as $km){
				$kmRitorno += $km;
			}
		} else if($TipoViaggioId == 2 && $open){
			$kmRitorno = $kmAndata;
		}
		
		$importoResiduo = $importoTotale_finale - $importo_coupon;
		if($importoResiduo < 0){
			$importoResiduo = 0;
		}
		// Prenotazione
		$dataP['CodicePrenotazione'] = getProgressivoCodicePrenotazione();
		$dataP['PrenotazioneStato'] = $PrenotazioneStato;
		$dataP['PrenotazioneStatoPagamento'] = null;
		$dataP['NonViaggiata'] = 0;
		$dataP['TipoViaggioId'] = $TipoViaggioId;
		$dataP['PrenotazioneModPre'] = null;
		$dataP['ClienteNome'] = $_SESSION['nome'];
		$dataP['ClienteSessoId'] = 3;
		$dataP['ClienteCellularePrefisso'] = $_SESSION['prefisso'];
		$dataP['ClienteCellulare'] = $_SESSION['tel'];
		if(isset($_SESSION['tel'])) {
		    $dataP['ClienteCellulareFamiliare'] = $_SESSION['tel'];
		} else {
		    $dataP['ClienteCellulareFamiliare'] = null;
		}
		$dataP['ClienteEmail'] = $_SESSION['mail'];
		$dataP['ConsensoPrivacy'] = isset($_SESSION['consenso_privacy']) ? $_SESSION['consenso_privacy'] : 0;
		$dataP['ConsensoMarketing'] = isset($_SESSION['consenso_marketing']) ? $_SESSION['consenso_marketing'] : 0;
		$dataP['ConsensoProfilazione'] = isset($_SESSION['consenso_profilazione']) ? $_SESSION['consenso_profilazione'] : 0;
		$dataP['ClienteFidelityCardId'] = null;
		$dataP['TotalePaxPrenotati'] = $totale_pax;
		$dataP['TotalePrenotazione'] = $prezzo_totale_ricalcolo;
		if(!$open){
			$dataP['RitornoOpen'] = 0;
		} else {
			$dataP['RitornoOpen'] = 1;
		}
		$dataP['TipoPagamentoId'] = null;
		$dataP['Pagato'] = 0;
		$dataP['ABordo'] = 0;
		$dataP['ScadenzaPrenotazione'] = null;
		$dataP['TotaleDaPagare'] = $importoTotale_finale;
		$dataP['TotaleDaPagareMulti'] = 0;
		$dataP['TotalePagato'] = $importoTotale_finale-$importoResiduo; //$dataP['TotalePagato'] = 0;
		$dataP['TotaleResiduo'] = $importoResiduo;//$dataP['TotaleResiduo'] = $importoTotale_finale;
		$dataP['KmPercorsiAndata'] = $kmAndata;
		$dataP['KmPercorsiRitorno'] = $kmRitorno;
		$dataP['KmPercorsiTotale'] = $kmRitorno + $kmAndata;
		$dataP['Multi'] = 0;
		$dataP['MembershipClubCode'] = (isset($userInfo['MembershipClubCode'])) ? $userInfo['MembershipClubCode'] : null;
		$dataP['Canale'] = Config::$canalePagamento;
		$dataP['TipoTour'] = $arr['TipoTour']; 
		$dataP['ScontrinoInvioWeb'] = ($scontrinoInvio == 0) ? 1 : 0 ; //1=invia scontrino digitale, 0=non invia scontrino digitale
		$dataP['Lingua'] = $_SESSION['lang'];
		$dataP = getOperazioniInsert($dataP, $gestore);
		
		$PrenotazioneId = $db->insert("RT_Prenotazione", $dataP);

		//*****************VERIFICA INSERIMENTO PRENOTAZIONE *************//
		// VERIFICA ESSENZIALE: Controllo che la prenotazione sia stata inserita correttamente
		if (empty($PrenotazioneId) || !is_numeric($PrenotazioneId)) {
			// Salva i dati di errore nella tabella di log
			$dataErrore = array();
			$dataErrore['DataInserimento'] = date('Y-m-d H:i:s');
			$dataErrore['DataPrenotazione'] = json_encode($dataP, JSON_UNESCAPED_UNICODE);
			$dataErrore['ErroreDescrizione'] = 'Errore inserimento prenotazione - PrenotazioneId non valido';
			
			// Log dell'errore
			error_log("ERRORE CRITICO: Tentativo di pagamento con PrenotazioneId non valido");

			// Inserisci nella tabella di errore
			$db->insert("RT_PrenotazioneErroreLog", $dataErrore);
			
			// Redirect a pagina di errore
			?>
			<script>
				window.location.href = '/errore.php<?= ($sessionId != '') ? '?session_id='.$sessionId : '' ?>';
			</script>
			<?php
			exit();
		}

		// Verifica aggiuntiva: controlla che la prenotazione esista nel database
		$sql = "SELECT PrenotazioneId FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
		$checkPrenotazione = $db->query_first($sql);

		if (empty($checkPrenotazione['PrenotazioneId'])) {
			// Salva i dati di errore nella tabella di log
			$dataErrore = array();
			$dataErrore['DataInserimento'] = date('Y-m-d H:i:s');
			$dataErrore['DataPrenotazione'] = json_encode($dataP, JSON_UNESCAPED_UNICODE);
			$dataErrore['ErroreDescrizione'] = 'Errore verifica prenotazione - PrenotazioneId ' . $PrenotazioneId . ' non trovato nel database';
			
			error_log("ERRORE CRITICO: PrenotazioneId $PrenotazioneId non trovato nel database");
			
			// Inserisci nella tabella di errore
			$db->insert("RT_PrenotazioneErroreLog", $dataErrore);
			
			?>
			<script>
				window.location.href = '/errore.php<?= ($sessionId != '') ? '?session_id='.$sessionId : '' ?>';
			</script>
			<?php
			exit();
		}
		//*****************FINE VERIFICA INSERIMENTO PRENOTAZIONE *************//

		$cliente_email = $_SESSION['mail'];

		// Movimento Coupon MULTI
		if ($isCoupon && count($couponList) > 0) {
			foreach ($couponList as $coupon) {
				$dataM = array();
				$dataM['PrenotazioneId'] = $PrenotazioneId;
				$dataM['PagamentoTipoId'] = 12;
				$dataM['TipoMovimento'] = 'I';
				$dataM['Causale'] = 'Parte pagamento con coupon - Cod. '.$coupon['codice'];
				$dataM['Data'] = date('Y-m-d H:i:s');
				$dataM['Importo'] = $coupon['valore'];
				$dataM['Supplemento'] = 0;
				$dataM['DataPagamento'] = date('Y-m-d H:i:s');
				$dataM['ImportoPagato'] = $coupon['valore'];
				$dataM = getOperazioniInsert($dataM, $gestore);
				$PrenotazioneMovimentoId = $db->insert("RT_PrenotazioneMovimento", $dataM);
				// Aggiorna utilizzi coupon
				$sql = "SELECT * From RT_Coupon WHERE Codice = '".$coupon['codice']."'";
				$rowCoupon = $db->query_first($sql);
				$dataCoupon = array();
				$dataCoupon['Valore'] = 0;
				$dataCoupon['Utilizzi'] = $rowCoupon['Utilizzi']+1;
				if($dataCoupon['Utilizzi'] < $rowCoupon['MaxUtilizzi']){
					$dataCoupon['Valore'] = $rowCoupon['Importo'];
				}
				$dataCoupon = getOperazioniUpdate($dataCoupon);
				$result = $db->update("RT_Coupon", $dataCoupon, "CouponId=".$rowCoupon['CouponId']);
			}
		}
		
		// Prenotazione Biglietto
		$descrizioneBiglietto = "";
		foreach ($biglietti as $tipologiaId => $biglietto){
			if($biglietto['numero'] >= 1) {
				$sql = "SELECT TipologiaBiglietto FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = $tipologiaId";
				$row = $db->query_first($sql);
		
				$dataPB['PrenotazioneId'] = $PrenotazioneId;
				$dataPB['TipologiaBigliettoId'] = $tipologiaId;
				$dataPB['TipologiaBiglietto'] = $row['TipologiaBiglietto'];
				$dataPB['NumeroPax'] = $biglietto['numero'];
				$dataPB['PrezzoAndata'] = $biglietto['prezzoAndataNoSconto'];
				if($TipoViaggioId == 1){
					$dataPB['PrezzoRitorno'] = 0;
					$dataPB['PercScontoAR'] = 0;
				}else{
					$dataPB['PrezzoRitorno'] = $biglietto['prezzoRitornoNoSconto'];
					$dataPB['PercScontoAR'] = $scontoAndataRitorno;
				}
				$dataPB['PrezzoARpieno'] = $biglietto['prezzoRitornoNoSconto'] + $biglietto['prezzoAndataNoSconto'];
				$dataPB['Moltiplicatore'] = $biglietto['moltiplicatore'];
				$dataPB['PerScontoPromozioneGGprima'] = $biglietto['variazione'];
				$dataPB['ListinoIdPromozioneGGprima'] = $biglietto['listino_promozione'];
				$dataPB['PrezzoPax'] = $biglietto['prezzo'];
				$dataPB['PrezzoBasePax'] = $biglietto['prezzoTotale'];
				$dataPB['RiduzionePax'] = 0;
				$dataPB['AumentoPax'] = 0;
				$dataPB['PrezzoTotalePax'] = $biglietto['prezzoTotale'];
				$dataPB = getOperazioniInsert($dataPB, $gestore);

				$descrizioneBiglietto .= $biglietto['numero'] . ' x ' . $row['TipologiaBiglietto'] . ", ";
		
				$PrenotazioneBigliettoId = $db->insert("RT_PrenotazioneBiglietto", $dataPB);
			}
		}
		$descrizioneBiglietto = rtrim($descrizioneBiglietto, ", ");
		
		//Prenotazione Passeggero, Numero e Dettaglio
		$indexPrincipale = $indicePasseggeroPrincipale;
		$principaleNome = '';
		$principaleCognome = '';
		foreach ($passeggeri as $index => $passeggero) {
			// Prenotazione Passeggero
			$dataPP['PrenotazioneId'] = $PrenotazioneId;
			$dataPP['TipoBigliettoId'] = $passeggero['TipoBigliettoId'];
			$dataPP['Cognome'] = $passeggero['Cognome'];
			$dataPP['Nome'] = $passeggero['Nome'];
			$dataPP['SessoId'] = $passeggero['SessoId'];
			$dataPP['Eta'] = $passeggero['Eta'];
			
			if ($indexPrincipale == $index) {
				$dataPP['Principale'] = 1;
				$principaleNome = $passeggero['Nome'];
				$principaleCognome = $passeggero['Cognome'];
			} else {
				$dataPP['Principale'] = 0;
			}
			
			$dataPP = getOperazioniInsert($dataPP, $gestore);
			 
			$PasseggeroId = $db->insert("RT_PrenotazionePasseggeri", $dataPP);
			 
			// Prenotazione Numero
			$dataPN['PrenotazioneId'] = $PrenotazioneId;
			$dataPN['TipologiaBigliettoId'] = $passeggero['TipoBigliettoId'];
			$dataPN['PasseggeroId'] = $PasseggeroId;
			$dataPN['CodiceQrcode'] = getCodiceQRCode();
			$dataPN = getOperazioniInsert($dataPN, $gestore);
		
			$PrenotazioneNumero = $db->insert("RT_PrenotazioneNumero", $dataPN);
			 
			// Prenotazione Dettaglio
			// Andata
			$sql = "SELECT TipologiaBiglietto FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = " . $passeggero['TipoBigliettoId'];
			$row = $db->query_first($sql);
			 
			$dataPD['PrenotazioneId'] = $PrenotazioneId;
			$dataPD['Cognome'] = $passeggero['Cognome'];
			$dataPD['Nome'] = $passeggero['Nome'];
			$dataPD['SessoId'] = $passeggero['SessoId'];
			$dataPD['Eta'] = $passeggero['Eta'];
			$dataPD['TipoServizio'] = 'Bus';
			$dataPD['TipologiaBiglietto'] = $row['TipologiaBiglietto'];
			 
			$dataPD['ComunePartenza'] = $PercorsoAndata['ComunePickUp'];
			$dataPD['FermataPartenza'] = $PercorsoAndata['FermataPickUp'];
			$dataPD['DataPartenza'] = $PercorsoAndata['DataPickUp'];
			$dataPD['OrarioPartenza'] = $PercorsoAndata['OrarioPickUp'];
			$dataPD['ComuneArrivo'] = $PercorsoAndata['ComuneDropOff'];
			$dataPD['FermataArrivo'] = $PercorsoAndata['FermataDropOff'];
			$dataPD['DataArrivo'] = $PercorsoAndata['DataDropOff'];
			$dataPD['OrarioArrivo'] = $PercorsoAndata['OrarioDropOff'];
			$dataPD['Tragitto'] = "Andata";
		
			if ($TipoViaggioId == 1) {
				$dataPD['TipoViaggio'] = "Corsa Semplice";
			} else {
				$dataPD['TipoViaggio'] = "Andata/Ritorno";
			}
		
			$sql = "SELECT p.PercorsoId, l.LineaId, c.CorsaId, p.PercorsoNome, l.LineaNome, c.CorsaNome FROM RT_Corsa c LEFT JOIN RT_Linea l ON (c.LineaId = l.LineaId) LEFT JOIN RT_Percorso p ON (l.PercorsoId = p.PercorsoId) WHERE c.CorsaId = " . $PercorsoAndata['CorsaId'];
			$row = $db->query_first($sql);
		
			$dataPD['PercorsoId'] = $row['PercorsoId'];
			$dataPD['LineaId'] = $row['LineaId'];
			$dataPD['CorsaId'] = $row['CorsaId'];
			$dataPD['PercorsoNome'] = $row['PercorsoNome'];
			$dataPD['LineaNome'] = $row['LineaNome'];
			$dataPD['CorsaNome'] = $row['CorsaNome'];
			$prezzoAndataNoSconto = $biglietti[$passeggero['TipoBigliettoId']]['prezzoAndataNoSconto'];
			if ($TipoViaggioId == 1) {
				$dataPD['Importo'] = $biglietti[$passeggero['TipoBigliettoId']]['prezzo'];
			} else {
				$dataPD['Importo'] = $prezzoAndataNoSconto - ($prezzoAndataNoSconto * $scontoAndataRitorno / 100);
			}
			$dataPD['ImportoAgenzia'] = null;
			$dataPD['PercentualeAgenzia'] = null;
			$dataPD['FissoAgenzia'] = null;
			$dataPD['AliquotaBiglietto'] = 0;
			$dataPD['AliquotaProvvigione'] = 0;
			$dataPD['ProvvigioneNetta'] = null;
			$dataPD['DaBonificare'] = null;
			$dataPD['DaFatturare'] = null;
			$dataPD['PrenotazioneNumero'] = $PrenotazioneNumero;
			$dataPD['DataInizioItinerario'] = $PercorsoAndata['DataPartenza'];
			$dataPD['CorsaInizioItinerario'] = $PercorsoAndata['CorsaId'];
			$dataPD['VoucherInviato'] = 0;
			$dataPD['Escludi'] = 0;
			$dataPD['Rimborso'] = 0;
			$dataPD = getOperazioniInsert($dataPD, $gestore);
			 
			$db->insert("RT_PrenotazioneDettaglio", $dataPD);
			
			// Ritorno se presente
			if ($TipoViaggioId == 2) {
				$dataPD['ComunePartenza'] = $PercorsoRitorno['ComunePickUp'];
				$dataPD['FermataPartenza'] = $PercorsoRitorno['FermataPickUp'];
				$dataPD['DataPartenza'] = $PercorsoRitorno['DataPickUp'];
				$dataPD['OrarioPartenza'] = $PercorsoRitorno['OrarioPickUp'];
				$dataPD['ComuneArrivo'] = $PercorsoRitorno['ComuneDropOff'];
				$dataPD['FermataArrivo'] = $PercorsoRitorno['FermataDropOff'];
				$dataPD['DataArrivo'] = $PercorsoRitorno['DataDropOff'];
				$dataPD['OrarioArrivo'] = $PercorsoRitorno['OrarioDropOff'];
				$dataPD['Tragitto'] = "Ritorno";
				$dataPD['TipoViaggio'] = "Andata/Ritorno";
				 
				$sql = "SELECT p.PercorsoId, l.LineaId, c.CorsaId, p.PercorsoNome, l.LineaNome, c.CorsaNome FROM RT_Corsa c LEFT JOIN RT_Linea l ON (c.LineaId = l.LineaId) LEFT JOIN RT_Percorso p ON (l.PercorsoId = p.PercorsoId) WHERE c.CorsaId = " . $PercorsoRitorno['CorsaId'];
				$row = $db->query_first($sql);
				 
				$dataPD['PercorsoId'] = $row['PercorsoId'];
				$dataPD['LineaId'] = $row['LineaId'];
				$dataPD['CorsaId'] = $row['CorsaId'];
				$dataPD['PercorsoNome'] = $row['PercorsoNome'];
				$dataPD['LineaNome'] = $row['LineaNome'];
				$dataPD['CorsaNome'] = $row['CorsaNome'];
				$prezzoRitornoNoSconto = $biglietti[$passeggero['TipoBigliettoId']]['prezzoRitornoNoSconto'];
				$dataPD['Importo'] = $prezzoRitornoNoSconto - ($prezzoRitornoNoSconto * $scontoAndataRitorno / 100);
				$dataPD['DataInizioItinerario'] = $PercorsoRitorno['DataPartenza'];
				$dataPD = getOperazioniInsert($dataPD, $gestore);
				 
				$db->insert("RT_PrenotazioneDettaglio", $dataPD);
			}
		}
		
		// Prenotazione Percorso Andata
		$sql = "SELECT p.PercorsoId, l.LineaId, c.CorsaId, p.PercorsoNome, l.LineaNome, c.CorsaNome, c.OrarioPartenza FROM RT_Corsa c LEFT JOIN RT_Linea l ON (c.LineaId = l.LineaId) LEFT JOIN RT_Percorso p ON (l.PercorsoId = p.PercorsoId) WHERE c.CorsaId = " . $PercorsoAndata['CorsaId'];
		$row = $db->query_first($sql);
		$dataPPer['CorsaId'] = $row['CorsaId'];
		$dataPPer['LineaId'] = $row['LineaId'];
		$dataPPer['PercorsoId'] = $row['PercorsoId'];
		$dataPPer['PrenotazioneId'] = $PrenotazioneId;
		$dataPPer['PrenotazioneStato'] = $PrenotazioneStato;
		$dataPPer['PercorsoNome'] = $row['PercorsoNome'];
		$dataPPer['LineaNome'] = $row['LineaNome'];
		$dataPPer['CorsaNome'] = $row['CorsaNome'];
		$dataPPer['CorsaDataPartenza'] = $PercorsoAndata['DataPartenza'];
		$dataPPer['CorsaOrarioPartenza'] = $row['OrarioPartenza'];
		$dataPPer['ComuneSalitaId'] = $PercorsoAndata['ComunePickUpId'];
		$dataPPer['ComuneSalita'] = $PercorsoAndata['ComunePickUp'];
		$dataPPer['ComuneDiscesaId'] = $PercorsoAndata['ComuneDropOffId'];
		$dataPPer['ComuneDiscesa'] = $PercorsoAndata['ComuneDropOff'];
		$dataPPer['FermataSalitaId'] = $PercorsoAndata['FermataPickUpId'];
		$dataPPer['FermataSalita'] = $PercorsoAndata['FermataPickUp'];
		$dataPPer['FermataDiscesaId'] = $PercorsoAndata['FermataDropOffId'];
		$dataPPer['FermataDiscesa'] = $PercorsoAndata['FermataDropOff'];
		$dataPPer['Direzione'] = "A";
		$dataPPer['KmPercorsi'] = $kmAndata;//$PercorsoAndata['km'];
		$dataPPer['DataOraSalita'] = $PercorsoAndata['DataPickUp'] . " " . $PercorsoAndata['OrarioPickUp'];
		$dataPPer['DataOraDiscesa'] = $PercorsoAndata['DataDropOff'] . " " . $PercorsoAndata['OrarioDropOff'];
		$dataPPer['PasseggeriEsclusi'] = 0;
		$dataPPer = getOperazioniInsert($dataPPer, $gestore);
		 
		$db->insert("RT_PrenotazionePercorso", $dataPPer);
		
		// Prenotazione Percorso Ritorno se esiste
		if (isset($PercorsoRitorno)) {
			$sql = "SELECT p.PercorsoId, l.LineaId, c.CorsaId, p.PercorsoNome, l.LineaNome, c.CorsaNome, c.OrarioPartenza FROM RT_Corsa c LEFT JOIN RT_Linea l ON (c.LineaId = l.LineaId) LEFT JOIN RT_Percorso p ON (l.PercorsoId = p.PercorsoId) WHERE c.CorsaId = " . $PercorsoRitorno['CorsaId'];
			$row = $db->query_first($sql);
			$dataPPer['CorsaId'] = $row['CorsaId'];
			$dataPPer['LineaId'] = $row['LineaId'];
			$dataPPer['PercorsoId'] = $row['PercorsoId'];
			$dataPPer['PrenotazioneId'] = $PrenotazioneId;
			$dataPPer['PrenotazioneStato'] = $PrenotazioneStato;
			$dataPPer['PercorsoNome'] = $row['PercorsoNome'];
			$dataPPer['LineaNome'] = $row['LineaNome'];
			$dataPPer['CorsaNome'] = $row['CorsaNome'];
			$dataPPer['CorsaDataPartenza'] = $PercorsoRitorno['DataPartenza'];
			$dataPPer['CorsaOrarioPartenza'] = $row['OrarioPartenza'];
			$dataPPer['ComuneSalitaId'] = $PercorsoRitorno['ComunePickUpId'];
			$dataPPer['ComuneSalita'] = $PercorsoRitorno['ComunePickUp'];
			$dataPPer['ComuneDiscesaId'] = $PercorsoRitorno['ComuneDropOffId'];
			$dataPPer['ComuneDiscesa'] = $PercorsoRitorno['ComuneDropOff'];
			$dataPPer['FermataSalitaId'] = $PercorsoRitorno['FermataPickUpId'];
			$dataPPer['FermataSalita'] = $PercorsoRitorno['FermataPickUp'];
			$dataPPer['FermataDiscesaId'] = $PercorsoRitorno['FermataDropOffId'];
			$dataPPer['FermataDiscesa'] = $PercorsoRitorno['FermataDropOff'];
			$dataPPer['Direzione'] = "R";
			$dataPPer['KmPercorsi'] = $kmRitorno; //$PercorsoRitorno['km'];
			$dataPPer['DataOraSalita'] = $PercorsoRitorno['DataPickUp'] . " " . $PercorsoRitorno['OrarioPickUp'];
			$dataPPer['DataOraDiscesa'] = $PercorsoRitorno['DataDropOff'] . " " . $PercorsoRitorno['OrarioDropOff'];
			$dataPPer['PasseggeriEsclusi'] = 0;
			$dataPPer = getOperazioniInsert($dataPPer, $gestore);
		
			$db->insert("RT_PrenotazionePercorso", $dataPPer);
		}
		
		// Prenotazione Tratta Andata
		foreach ($PercorsoAndata['tratte'] as $trattaId => $trattaKm) {
			$sql = "SELECT * FROM RT_Tratta WHERE TrattaId = " . $trattaId;
			$row = $db->query_first($sql);
			$dataPT['PrenotazioneId'] = $PrenotazioneId;
			$dataPT['TrattaId'] = $trattaId;
			$dataPT['TrattaNome'] = $row['TrattaNome'];
			$dataPT['TrattaPeso'] = $row['TrattaPeso'];
			$dataPT['TrattaNodo'] = $row['NodoPeso'];
			$dataPT['TrattaNote'] = null;
			$dataPT['TrattaDirezione'] = 'A';
			$dataPT['CorsaId'] = $PercorsoAndata['CorsaId'];
			$dataPT = getOperazioniInsert($dataPT, $gestore);
			 
			$db->insert("RT_PrenotazioneTratta", $dataPT);
		}
		
		// Prenotazione Tratta Ritorno se esiste
		if (isset($PercorsoRitorno)) {
			foreach ($PercorsoRitorno['tratte'] as $trattaId => $trattaKm) {
				$sql = "SELECT * FROM RT_Tratta WHERE TrattaId = " . $trattaId;
				$row = $db->query_first($sql);
				$dataPT['PrenotazioneId'] = $PrenotazioneId;
				$dataPT['TrattaId'] = $trattaId;
				$dataPT['TrattaNome'] = $row['TrattaNome'];
				$dataPT['TrattaPeso'] = $row['TrattaPeso'];
				$dataPT['TrattaNodo'] = $row['NodoPeso'];
				$dataPT['TrattaNote'] = null;
				$dataPT['TrattaDirezione'] = 'A';
				$dataPT['CorsaId'] = $PercorsoRitorno['CorsaId'];
				$dataPT = getOperazioniInsert($dataPT, $gestore);
				 
				$db->insert("RT_PrenotazioneTratta", $dataPT);
			}
		}

		//invio evento SALESmanago
		if (Config::$salesmanago_enabled) {
			//recupero la nazione dal prefisso del cellulare
			$sql = "SELECT * FROM PrefissoTelefono WHERE Prefisso = '".$dataP['ClienteCellularePrefisso']."'";
			$prefisso = $db->query_first($sql);

			$sql = "SELECT * FROM RT_Linea WHERE LineaId = ".$dataPPer['LineaId'];
			$rowLinea = $db->query_first($sql);

			$sm = new ServiceSalesManago($db);
			$consentDetails = array();
			$agreementDate = round(microtime(true) * 1000);
			$ipAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
			if ($_SESSION['lang'] != 'it') {
				$acceptPrivacy = (isset($_SESSION['consenso_privacy']) && $_SESSION['consenso_privacy'] == 1);
				$acceptMarketing = (isset($_SESSION['consenso_marketing']) && $_SESSION['consenso_marketing'] == 1);
				$acceptProfilazione = (isset($_SESSION['consenso_profilazione']) && $_SESSION['consenso_profilazione'] == 1);
				$consentDetails[] = array('consentName' => 'PRIVACY_ENG', 'consentAccept' => $acceptPrivacy, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5496);
				$consentDetails[] = array('consentName' => 'MARKETING_ENG', 'consentAccept' => $acceptMarketing, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5497);
				$consentDetails[] = array('consentName' => 'PROFILAZIONE_ENG', 'consentAccept' => $acceptProfilazione, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5498);
			} else {
				$acceptPrivacy = (isset($_SESSION['consenso_privacy']) && $_SESSION['consenso_privacy'] == 1);
				$acceptMarketing = (isset($_SESSION['consenso_marketing']) && $_SESSION['consenso_marketing'] == 1);
				$acceptProfilazione = (isset($_SESSION['consenso_profilazione']) && $_SESSION['consenso_profilazione'] == 1);
				$consentDetails[] = array('consentName' => 'PRIVACY_ITA', 'consentAccept' => $acceptPrivacy, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5493);
				$consentDetails[] = array('consentName' => 'MARKETING_ITA', 'consentAccept' => $acceptMarketing, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5494);
				$consentDetails[] = array('consentName' => 'PROFILAZIONE_ITA', 'consentAccept' => $acceptProfilazione, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5495);
			}
			$result = $sm->sendEvent($PrenotazioneId, 
							ServiceSalesManago::EVENT_CART, 
							$cliente_email, 
							$dataPPer['LineaNome'],
							$importoTotale_finale, 
							$_SESSION['lang'], 
							$arr['TipoTour'] == 1 ? 'Tour Privato' : 'Tour Gruppo', 
							$descrizioneBiglietto,
							'+'.$dataP['ClienteCellularePrefisso'].$dataP['ClienteCellulare'],
							$dataP['ClienteNome'],
							$prefisso['Nazione'],
							$rowLinea['LinkDescrizione'],
							'web',
							$consentDetails
						);
		}
	}
	
	
	
	
	
	/**aggiorna disponibilita posti**/
	if(isset($PrenotazioneId)) {
		$prenotazione = new Prenotazione();
		$prenotazione->Id = $PrenotazioneId;
		$prenotazione->conn = $db;

		aggiornaDisponibilita($PercorsoAndata['CorsaId'], $PercorsoAndata['DataPartenza']);
		if (isset($PercorsoRitorno['CorsaId']) && $PercorsoRitorno['CorsaId'] > 0) {
			aggiornaDisponibilita($PercorsoRitorno['CorsaId'], $PercorsoRitorno['DataPartenza']);
		}
	}
	/**fine aggiorna disponibilita posti**/
	
	/** inserimento Google People **/
	//$servicePeople = new ServiceGooglePeople($db);
	//$servicePeople->insertPeople($principaleNome, $principaleCognome, $dataP['ClienteEmail'], $dataP['ClienteCellularePrefisso'].$dataP['ClienteCellulare']);
	/** FINE inserimento Google People **/
	
	/**emissione fattura **/
	if($fattura) {
	    emettiFattura($PrenotazioneId);
	}
	/**FINE emissione fattura**/
	?>
	
	<!-- Top Header
	================================================== -->
	<?php include_once($basepath."/include/top_header2.php"); ?>
	
	<div id="page" class="hfeed site fullwidth">
    	
        
        <div class="main-container">
			<div class="content">
				<div style="margin-bottom:10px;" class="benvenuto-plugin">
					<h2><?=$dizionario['prenota']['prenota_il_tuo_tour']?></h2>
				</div>
				<div class="form-container" style="padding:20px 0px;">
						<div class="available-item" style="text-align:center;">

						<?php
						$stringa = '';
						if ($PrenotazioneStato != 2) {
							$stringa = "<span>".$dizionario['prenota']['reindirizza_1'] . " " . $nome_sito . " ".$dizionario['prenota']['reindirizza_2'];
							$stringaButton = '';
							if ($posti_disponibili == false){
								$stringa = "<span>".$dizionario['prenota']['no_procedere_1'] . "</span><br />".$dizionario['prenota']['no_procedere_2'];
							}
							if($isCoupon && $importoResiduo == 0){
								$stringa = "<span>".$dizionario['prenota']['prenotazione_1'] . 
								$stringaButton .= "<br><button type='button' class='btn btn-big btn-primary' style='width: 100%;' onclick=\"window.location.href='index.php" . (($sessionId != '') ? '?session_id='.$sessionId : '') . "'\">".$dizionario['conferma']['torna_home_page']."</button>";
								$stringa .= "</span>";
							}
						} else {
							$stringa = "<span>".$dizionario['conferma']['prenotazione_da_confermare'];
							$stringaButton = "<br><br><button type='button' class='btn btn-big btn-primary' style='width: 100%;' onclick=\"window.location.href='index.php" . (($sessionId != '') ? '?session_id='.$sessionId : '') . "'\">".$dizionario['conferma']['torna_home_page']."</button>";
							$stringa .= "</span>";
						}
						?>
						
						<!-- Header
						================================================== -->
						<div id="header2" style="padding-top: 40px;" class="iframe-none">
							<div class="container-loader">
								<div class="sixteen columns">
									<div class="info2_container">
										<p class="big_text" style="font-size: 20px; font-weight: bold; line-height: 24px; text-align: center"><?php echo $dizionario['prenota']['attenzione'];?></p>
										<hr style="border: 0; border-bottom: 3px solid #3D7DA9"/>				
											<p class="big_text" style="font-size: 20px; font-weight: bold; line-height: 24px;">
												<br/><br/><br/><br/>
												<?php if ($PrenotazioneStato != 2) { ?>	
													<img src="/images/ajax_loader_red.gif" alt="Ajax loader" />
													<br/><br/>
												<?php } ?>
												<?=$stringa?>
												<?=$stringaButton?>
											</p>
											<br/><br/><br/><br/>
									</div><!-- banner_container -->
								</div><!-- sixteen -->
							</div><!-- container -->
						</div><!-- header -->

						<div class="new-ticket iframe-show confirm-step">
							<p class="confirm-step-text">
								<?=$stringa?>
							</p>
							<?=$stringaButton?>
						</div>
						
						
						<!-- Main
						================================================== -->
						<div id="main">
							<div class="container-loader">
								<div class="sixteen columns">                               		
										
									<form action="">
										<div class="submit_holder">
											<div class="prezzo_show" >
												<?=$dizionario['prenota']['grazie']?>
											</div><!-- prezzo_show -->
										</div><!-- submit_holder -->                        
									</form>
									
									<br/><br/><br/><br/><br/><br/>
								</div><!-- sixteen -->
								<div class="clear"></div>
							</div><!-- container -->
						</div><!-- main -->
						</div>
					</div>
			
			</div>
		</div>

    
		<?php include_once($basepath."/include/footer.php"); ?>
	
	</div>
	
    <?php

	clearSession();
    
    if($isCoupon && $importoResiduo == 0){
    	// Prenotazione pagata interamente con uno o più coupon
    	EmettiTitoliDiViaggio($PrenotazioneId);
    } else {

	

		if ($posti_disponibili && $PrenotazioneStato != 2) {
			$url_sito = Config::$UrlDominio;
			$PAYPAL_LINK=Config::$PayPalUrl;
	        
			$PAYPAL_MAIL =  Config::$PayPalEmail;
			
   
			$bank_pagina_grazie = $url_sito."/grazie.php?OrderId=".$PrenotazioneId."&em=".$cliente_email;
			if($gestore != -1){
				$bank_pagina_grazie = $url_sito."/grazie.php?OrderId=".$PrenotazioneId."&em=".$cliente_email."&code=".$gestore['CodiceAzienda'];
			}
			$base_url = $url_sito;
			
			if ($metodo_pagamento == 1) {
				echo "
				<form id=\"form_paypal\" target=\"_top\" name=\"form_paypal\" action=\"$PAYPAL_LINK\" method=\"post\">
					<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
					<input type=\"hidden\" name=\"business\" value=\"$PAYPAL_MAIL\">
					<input type=\"hidden\" name=\"item_name\" value=\"Biglietto Bertoldi Boats\">
					<input type=\"hidden\" name=\"item_number\" value=\"$PrenotazioneId\">
					<input type=\"hidden\" name=\"amount\" value=\"$importoResiduo\">
					<input type=\"hidden\" name=\"page_style\" value=\"Primary\">
					<input type=\"hidden\" name=\"return\" value=\"$bank_pagina_grazie\">
					<input type=\"hidden\" name=\"cancel_return\" value=\"$base_url\">
					<input type=\"hidden\" name=\"notify_url\" value=\"".Config::$PayPalNotifyUrl."\">
					<input type=\"hidden\" name=\"rm\" value=\"0\">
					<input type=\"hidden\" name=\"no_note\" value=\"0\">
					<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
					<input type=\"hidden\" name=\"custom\" value=\"$PrenotazioneId\">
				</form>
				";
				
				?>
				
				<script language="javascript">
					$('#form_paypal').submit(); 
			    </script>
			   <?php
			} elseif ($metodo_pagamento==2) {
			    
			    Stripe::setApiKey($config::$StripeSecretKey);
			    $session = \Stripe\Checkout\Session::create([
			        'payment_method_types' => ['card'],
			        'line_items' => [[
			            'price_data' => [
			                'currency' => 'eur',
			                'product_data' => [
			                    'name' => 'Ticket Bertoldi Boats',
			                ],
			                'unit_amount' => $importoResiduo*100,
						],
			            'quantity' => 1,
			        ]],
			        'mode' => 'payment',
			        'success_url' => $bank_pagina_grazie.'&session_id={CHECKOUT_SESSION_ID}',
			        'cancel_url' => $base_url,
			    ]);
			    ?>
				<script src="https://js.stripe.com/v3/"></script>
				<script type="text/javascript">
					var stripe = Stripe('<?php echo $config::$StripePublicKey?>');
					stripe.redirectToCheckout({ sessionId: '<?php echo $session->id;?>' });
				</script>
				 
			    <?php   
	   		} elseif ($metodo_pagamento==3) {
	   			$bank_pagina_grazie = $url_sito."grazie.php?OrderId=".$PrenotazioneId."&em=".$cliente_email;
	   			$base_url = $url_sito;
	   			$url_notification = 'http://'.Config::$ServerBOName.'/protected/cron/telecash_notifica_automatica.php';
	   			$hash = createHash( "$importoResiduo","978" );
	   			echo "
	   			<form id=\"form_telecash\" method=\"post\" action=\"".Config::$telecashLink."\">
				<input type=\"hidden\" name=\"txntype\" value=\"sale\">
				<input type=\"hidden\" name=\"timezone\" value=\"Europe/Berlin\"/> 
				<input type=\"hidden\" name=\"txndatetime\" value=\"". getDateTime() ."\"/>
				<input type=\"hidden\" name=\"hash_algorithm\" value=\"SHA256\"/>
				<input type=\"hidden\" name=\"hash\" value=\"". $hash. "\"/>
				<input type=\"hidden\" name=\"storename\" value=\"".Config::$telecashStoreId."\"/>
				<input type=\"hidden\" name=\"mode\" value=\"payonly\"/>
				<input type=\"hidden\" name=\"chargetotal\" value=\"$importoResiduo\"/>
				<input type=\"hidden\" name=\"currency\" value=\"978\"/>
				<input type=\"hidden\" name=\"responseSuccessURL\" value=\"".$bank_pagina_grazie."\"/>
				<input type=\"hidden\" name=\"responseFailURL\" value=\"".$base_url."\"/>
				<input type=\"hidden\" name=\"transactionNotificationURL\" value=\"".$url_notification."\"/>
				<input type=\"hidden\" name=\"custom\" value=\"$PrenotazioneId\">
	   			
	   			</form>";
	   			//    die($PAYPAL_MAIL);
	   			?>		   
	   			<script language="javascript">
	   				document.forms['form_telecash'].submit();
	   			</script>
	   			<?php
	   		}
		}
    }
   	?>
   
    <!-- Controllo iframe
	================================================== -->
    <script type="text/javascript">
		document.addEventListener('DOMContentLoaded', function() {
			if (window.self !== window.top) {
				document.body.classList.add('iframe-hidden');
			}
		});
    </script>
	
<!-- End Document
================================================== -->
</body>
</html>
<?php
	clearSession();
}



function EmettiTitoliDiViaggio($PrenotazioneId) {
	global $db, $user;
	$user = new UtenteWeb();
	$user->conn = $db;
	$prenotazione_wizard = new Prenotazione();
	$prenotazione_wizard->Id = $PrenotazioneId;
	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
	$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];

	$prenotazione_wizard->conn = $db;
	if (!$DatiGeneraliArr['Multi']) {
		$prenotazione_wizard->EmettiBiglietti();
	} else {
		$sql = "SELECT PrenotazioneId
				FROM RT_Prenotazione
				WHERE PrenotazioneId = '" . $DatiGeneraliArr['PrenotazioneId'] . "' AND PrenotazioneStato = " . $DatiGeneraliArr['PrenotazioneStato'] . " AND Cancella = 0";

		$prenotazioni = $db->fetch_array($sql);

		foreach ($prenotazioni as $prenotazione) {
			$prenotazioneObj = new Prenotazione($prenotazione['PrenotazioneId']);
			$prenotazioneObj->conn = $db;
			$prenotazioneObj->EmettiBiglietti();
		}
	}
	header("location: /");
}
   
function getOperazioniInsert($campi_tabella, $gestore) {
    global $OperatoreId, $OdcId, $SedeId, $GestoreId;
  
	$OdcId = 1;
	$GestoreId = 1;

	if(isset($gestore) && $gestore != -1){
		$GestoreId = $gestore['GestoreId'];
	}
	$OperatoreId = 5;
	$SedeId = 1;
  
	$campi_tabella['OpeIns'] = $OperatoreId;
	$campi_tabella['SedeIns'] = $SedeId;
	$campi_tabella['DataIns'] = date('Y-m-d H:i:s');
	$campi_tabella['IpIns'] = getenv('REMOTE_ADDR');  
	$campi_tabella['OdcIdRef'] = $OdcId;
	$campi_tabella['GestoreIdRef'] = $GestoreId;
	$campi_tabella['Cancella'] = 0;
	$campi_tabella['Stato'] = 1;
    
    return $campi_tabella;
    
}

function getOperazioniUpdate($campi_tabella)
{
	global $OperatoreId, $OdcId, $SedeId, $GestoreId;
	$OdcId=1;
	$GestoreId=1;
	$OperatoreId=5;
	$SedeId=1;
       
	$campi_tabella['OpeAgg'] = $OperatoreId;
	$campi_tabella['SedeAgg'] = $SedeId;
	$campi_tabella['DataAgg'] = date('Y-m-d H:i:s');
	$campi_tabella['IpAgg'] = getenv('REMOTE_ADDR');

	return $campi_tabella;

}

function getProgressivoCodicePrenotazione()
{
	global $db;

	$sql = "SELECT CodicePrenotazione codice FROM RT_Prenotazione ORDER BY CodicePrenotazione Desc";

	$row = $db->query_first($sql);

	$id = "";
	if ((!empty($row['codice']))) {

		$id = intval(substr($row['codice'], 2)) + 1;
	} else {
		$id = 1;
	}

	return Config::$identificativoPrenotazione . str_pad($id, 11, "0", STR_PAD_LEFT);
}

function getCodiceQRCode(){
	$N_Caratteri = 32;
	$Stringa = "";
	for($I=0;$I<$N_Caratteri;$I++){
		do{
			$N = Ceil(rand(48,122));
		}while(!((($N >= 48) && ($N <= 57)) || (($N >= 65) && ($N <= 90)) || (($N >= 97) && ($N <= 122))));
		$Stringa = $Stringa.Chr ($N);
	}
	return $Stringa;
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
				where t.LineaId = ".$corsa['LineaId']." and f.ComuneId = $k
				and f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
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

function emettiFattura($prenotazioneId) {
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
            where PrenotazioneMovimentoId = $movimentoId";
    $movimento = $db->query_first($sql);
    
    $sql = "SELECT Max(Progressivo) as Ultimo
                FROM FatturaInCloudViaggiatore
                WHERE Tipo = '$tipo'";
    $temp1 = $db->query_first($sql);
    if(!isset($temp1['Ultimo'])) {
        $progressivo = 1;
    } else {
        $progressivo = $temp1['Ultimo'] +1;
    }
    
//     $fatturaNumero = $progressivo."-OB/".date('Y');
    $siglaFattura = '-OB';
    
    $nome = $_POST['fattura_ragionesociale'];
    $indirizzo_via = $_POST['fattura_indirizzo'];
    $indirizzo_cap = $_POST['fattura_cap'];
    $indirizzo_provincia = $_POST['fattura_provincia'];
    $indirizzo_citta = $_POST['fattura_comune'];
    $indirizzo_stato = $_POST['fattura_nazione'];
    $nazione = new Nazione($indirizzo_stato);
    $nazione->conn=$db;
    $nazione->inizializzaDatiGenerali();
    $paese = $nazione->DatiGenerali['Nazione'];
    $paese_iso = $nazione->DatiGenerali['ISO2'];
    $lingua = 'it';
    $piva = $_POST['fattura_partita_iva'];
    $cf = $_POST['fattura_codice_fiscale'];
    $pec = $_POST['fattura_emailpec'];
    $codice_destinatario = $_POST['fattura_codice_destinatario'];
    $email = $_POST['fattura_email'];
    $tel = $_POST['fattura_tel'];
    $fax = '';
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
    
    $sql = "INSERT INTO FatturaInCloudFatturare
                        (Tipo, PrenotazioneId, Nome, IndirizzoVia, IndirizzoCap, IndirizzoCitta, IndirizzoProvincia, Paese, PaeseISO, Lingua, PIVA, CF, Articolo,
                        ArticoloCodIva, ArticoloQuantita, ArticoloPrezzoNetto, ArticoloPrezzoLordo, PagamentoData, PagamentoScadenza, PagamentoImporto, CodiceDestinatario, PEC,
                        Email, Tel, Fax)
                    VALUES
    					('$tipo', $prenotazioneId, '$nome', '$indirizzo_via', '$indirizzo_cap', '$indirizzo_citta', '$indirizzo_provincia', '$paese',
                        '$paese_iso', '$lingua', '$piva', '$cf', '$articolo_nome',
                        3, $articolo_quantita, $articolo_prezzo_netto, $articolo_prezzo_lordo,
                        '$pagamento_data_saldo', '$pagamento_data_scadenza', $pagamento_importo, '$codice_destinatario', '$pec',
                        '$email', '$tel', '$fax')";
    //                 echo $sql;
    $db->query($sql);
    
//     $service = new ServiceFatturaInCloud($db);
//     $result = $service->inviaFatturaCliente($nome, $indirizzo_via, $indirizzo_cap, $indirizzo_provincia, $indirizzo_citta,
//         $paese, $paese_iso, $lingua, $piva, $cf, $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto,
//         $articolo_prezzo_lordo, $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
//         $email, $tel, $fax, $fa_data, $fatturaNumero, $progressivo, $siglaFattura, $tipo, $statoFattura,
//         $prenotazione['CodicePrenotazione'], $prenotazioneId, $movimentoId, null, $movimento['PagamentoTipo']);
    
//     $result = ['PrenotazioneId' => $prenotazioneId, 'CorsaId' => ""];
//     return $result;
}

function arrotondaFattore($prezzo, $fattore = 5) {
    $resto = $prezzo % $fattore;
    if ($resto >= ($fattore / 2)) {
        // Arrotonda al multiplo superiore
        return ceil($prezzo / $fattore) * $fattore;
    } else {
        // Arrotonda al multiplo inferiore
        return floor($prezzo / $fattore) * $fattore;
    }
}
?>
