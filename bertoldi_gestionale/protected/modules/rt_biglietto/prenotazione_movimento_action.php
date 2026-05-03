<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_."class.DT.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.PagamentoTipo.php");
include_once($classespath_."class.Prenotazione.php");

global $ModuloId;
global $user;

$ModuloId = 2;// modulo base mediazione

if(is_object($user)) {
	$permessi=$user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0) {
		if (!empty($_POST)) {
			switch($_POST['action']) {
				case "AggiungiPrenotazioneMovimento":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						aggiungiPrenotazioneMovimento();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
				case "ModificaPrenotazioneMovimento":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						aggiungiPrenotazioneMovimento($_POST['PrenotazioneMovimentoId']);
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
				case "calcoloSupplemento":
					calcoloSupplemento($_POST['importo'], $_POST['tipoPagamento']);
				break;
				case "calcoloDataScadenza":
					calcoloDataScadenza($_POST['dataMovimento'], $_POST['oraMovimento'], $_POST['tipoPagamento']);
				break;
				case "checkCoupon":
					checkCoupon($_POST['tipoPagamento']);
				break;
				case "controllaCodiceCoupon":
					checkCodiceCoupon($_POST['codiceCoupon'], $_POST['prenotazioneId']);
					break;
			}
		} // end verifica permessi
		else {
			Errors::$ErrorePermessiModulo;
		}
	}
}
// se l'utente non � loggato
else {
	header("Location: /logout.php");
}

/* versione singola */
function aggiungiPrenotazioneMovimento($PrenotazioneMovimentoId = null) {

	global $user;
	$db = new Database();
	$db->connect();

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$dt = new DT();

	$movimento = $_POST['Movimento'];
	$oldTipoMovimento = $movimento['TipoMovimento']; 
	$movimento['Data'] = $dt->format($_POST['DataMovimento'] . ' ' . $_POST['OraMovimento'], "d/m/Y H:i", "Y-m-d H:i:s");
	$movimento['Importo'] = floatval(str_replace(',', '.', str_replace('.', '', $movimento['Importo'])));
	$movimento['Supplemento'] = floatval(str_replace(',', '.', str_replace('.', '', $movimento['Supplemento'])));
	$movimento['ImportoPagato'] = floatval(str_replace(',', '.', str_replace('.', '', $movimento['ImportoPagato'])));
	$movimento['ScontrinoInvioAuto'] = (isset($movimento['ScontrinoInvioAuto']) && ($movimento['ScontrinoInvioAuto'] == true || $movimento['ScontrinoInvioAuto'] == "true")) ? 1 : 0;

	$sql = "Select * From RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
	$rowPrenotazione = $db->query_first($sql);
	$gestoreIdRef = $rowPrenotazione['GestoreIdRef'];


	if(isset($_POST['CodiceCoupon']) && $_POST['CodiceCoupon'] != ''){
		$movimento['Coupon'] = $_POST['CodiceCoupon'];
	}
	
	if($movimento['DataPagamento'] != '') {
		$movimento['DataPagamento'] = $dt->format($movimento['DataPagamento'], "d/m/Y", "Y-m-d H:i:s");
	} else {
		$movimento['DataPagamento'] = 'NULL';
	}
	if($_POST['DataScadenza'] != '') {
		$movimento['Scadenza'] = $dt->format($_POST['DataScadenza'] . ' ' . $_POST['OraScadenza'], "d/m/Y H:i", "Y-m-d H:i:s");
	} else {
		$movimento['Scadenza'] = 'NULL';
	}
	if($movimento['CanalePagamentoId'] == '') {
		$movimento['CanalePagamentoId'] = 0;
	}
	if(!isset($movimento['Fattura']) || $movimento['Fattura'] === ''){
		$movimento['Fattura'] = 0;
	} else {
		$movimento['Fattura'] = intval($movimento['Fattura']);
	}
	if(!isset($movimento['FatturaNote']) || trim($movimento['FatturaNote']) === ''){
		$movimento['FatturaNote'] = 'NULL';
	}
	
	if (!isset($PrenotazioneMovimentoId)) {
		$movimento = $storico->operazioni_insert($movimento, $user);
		$movimento['GestoreIdRef'] = $gestoreIdRef;
		$result = $db->insert("RT_PrenotazioneMovimento", $movimento);
		$movimentoId = $result;
	} else {
		$sql = "Select TipoMovimento, ImportoPagato from RT_PrenotazioneMovimento where PrenotazioneMovimentoId = $PrenotazioneMovimentoId";
		$temp = $db->query_first($sql);
		$oldTipoMovimento = $temp['TipoMovimento'];
		$oldImportoPagato = $temp['ImportoPagato'];
		$movimento = $storico->operazioni_update($movimento, $user);
		$movimento['GestoreIdRef'] = $gestoreIdRef;
		$result = $db->update("RT_PrenotazioneMovimento", $movimento, "PrenotazioneMovimentoId=".$PrenotazioneMovimentoId." AND OdcIdRef=".$user->OdcId);
		$movimentoId = $result;
	}
	//se l'insert/update � andata a buon fine calcolo la somma degli importi dei movimenti
	$output = array();
	$output['prenotazioneId'] = $movimento['PrenotazioneId'];
	$output['corsaId'] = $_POST['CorsaId'];
	if($result) {
		$output['result'] = true;
		$output['tipoPagamento'] = $movimento['PagamentoTipoId'];
		$output['movimentoId'] = $movimentoId;
		
		//prende la prenotazione in sessione
		$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGenerali();

		//prende il tipo di pagamento selezionato
		$pagamentoTipoObj = new PagamentoTipo($movimento['PagamentoTipoId']);
		$pagamentoTipoObj->conn = $db;
		$pagamentoTipoObj->inizializzaDatiGenerali();
		$pagamentoTipo = $pagamentoTipoObj->DatiGenerali;

		$movimentoObj = new PrenotazioneMovimento();
		$movimentoObj->conn = $db;

		//prende gli importi totali
		$totaleImporti = $movimentoObj->getTotaliImporti($movimento['PrenotazioneId']);
		
		//prende la data di scadenza pi� piccola
		$minScadenza = $movimentoObj->getMinScadenza($movimento['PrenotazioneId']);

		//aggiorno la prenotazione
		$prenotazione['TotalePagato'] = floatval($totaleImporti['TotalePagato']);
		if ($prenotazione_wizard->DatiGenerali['Multi']) {
			
			$sql = "Select CodicePrenotazione from RT_Prenotazione where PrenotazioneId = ".$movimento['PrenotazioneId'];;
			$row = $db->query_first($sql);
			$sql =  "Select SUM(TotaleDaPagare) TotMulti from RT_Prenotazione where CodicePrenotazione = '".$row['CodicePrenotazione']."' and (PrenotazioneStato = 3 OR  PrenotazioneStato = 1)";
			$rowPrenotazioni = $db->query_first($sql);
			
			$prenotazione['TotaleDaPagareMulti'] = floatval($totaleImporti['TotaleDaPagare']);
			if($rowPrenotazioni['TotMulti'] == $totaleImporti['TotalePagato']){
				$prenotazione['TotaleResiduo'] = 0;
			} else {
				$prenotazione['TotaleResiduo'] = $totaleImporti['TotaleDaPagare'];
			}
				
// 			$prenotazione['TotaleResiduo'] = $prenotazione['TotaleDaPagareMulti'] - $totaleImporti['TotalePagato'];

		} else {
			$prenotazione['TotaleDaPagare'] = floatval($totaleImporti['TotaleDaPagare']);
			$prenotazione['TotaleResiduo'] = $prenotazione['TotaleDaPagare'] - $totaleImporti['TotalePagato'];
		}
		//$prenotazione['TotaleResiduo'] = ($prenotazione['TotalePagato'] == 0)? floatval($totaleImporti['TotalePrenotazione']) : 0;
		
		// se il pagamento � "A bordo" imposta pagato uguale a 2
		if ($movimento['PagamentoTipoId'] == "7") {
			$prenotazione['ABordo'] = intval(1);
		} else {
			$prenotazione['ABordo'] = intval(0);
		}
		$prenotazione['Pagato'] = intval($prenotazione['TotaleResiduo'] <= 0);
		
		$prenotazione['ScadenzaPrenotazione'] = $minScadenza;
		if($prenotazione_wizard->DatiGenerali['PrenotazioneStato'] == 14){
			$prenotazione_wizard->DatiGenerali['PrenotazioneStato'] = 1;
			$prenotazione['PrenotazioneStato'] = 1;
		}
		$prenotazione = $storico->operazioni_update($prenotazione, $user);
		if($prenotazione['ScadenzaPrenotazione'] == ''){
			unset($prenotazione['ScadenzaPrenotazione']);
		}
		$result = $db->update("RT_Prenotazione", $prenotazione, "PrenotazioneId=".$movimento['PrenotazioneId']." AND OdcIdRef=".$user->OdcId);
		
		//se il residuo � minore o uguale a zero emmetti biglietto
		if($prenotazione['TotaleResiduo'] <= 0){
			if($prenotazione['TotaleResiduo'] <= 0 && ((isset($oldImportoPagato) && $oldImportoPagato==0))|| !isset($oldImportoPagato)){
				if($prenotazione['TotaleResiduo'] <= 0 && !$prenotazione_wizard->DatiGenerali['Pagato'] && $prenotazione_wizard->DatiGenerali['PrenotazioneStato'] != 3) {
					$output['emetti'] = true;
					$output['emettiExtra'] = false;
				} else {
					$sql = "select COALESCE(sum(b.NumeroPax),0) as NumServizi from RT_PrenotazioneBiglietto b
							left join RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId 
							where t.OccupaPosto = 0 and b.PrenotazioneId = ".$movimento['PrenotazioneId'];
					$temp1 = $db->query_first($sql);
					$sql = "select count(*) as BigliettiServizi from RT_PrenotazioneTitolo b
							left join RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
							where t.OccupaPosto = 0 and b.PrenotazioneId = ".$movimento['PrenotazioneId']." and TipoTitolo = 'E'";
					$temp2 = $db->query_first($sql);
					
					if($temp2['BigliettiServizi'] == 0 && $temp1['NumServizi'] == 0){
						$output['emetti'] = false;
						$output['emettiExtra'] = true;
					} else if($temp1['NumServizi']>0 && $temp2['BigliettiServizi'] < $temp1['NumServizi']){
						$output['emetti'] = true;
						$output['emettiExtra'] = false;
					} else {
						$output['emetti'] = false;
						$output['emettiExtra'] = true;
					}
	
				}
			} else {
				$sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
				$titoli = $db->fetch_array($sql);
				if(count($titoli) == 0 && $prenotazione_wizard->DatiGenerali['PrenotazioneStato'] == 3) {
					$output['emetti'] = true;
					$output['emettiExtra'] = false;
				} else {
					$output['emetti'] = false;
					$output['emettiExtra'] = false;
				}
			}
		} else {
			$output['emetti'] = false;
			$output['emettiExtra'] = false;
		}

		//se ci sono titolo gia emessi controllo
// 		if($prenotazione_wizard->DatiGenerali['PrenotazioneStato'] == 3) {
// 			$sql = "SELECT sum(ImportoTitolo) as ImportoTitolo, count(*) as NumTitoli FROM RT_PrenotazioneTitolo where TipoTitolo = 'E' and PrenotazioneId = ".$movimento['PrenotazioneId'];
// 			$titoliImporto = $db->query_first($sql);
// 			if($titoliImporto['ImportoTitolo'] <= $prenotazione['TotaleDaPagare']){
// 				$sql = "SELECT t.PrenotazioneTitoloId, t.ImportoTitolo, b.PrezzoTotalePax  FROM RT_PrenotazioneTitolo t 
// 						left join RT_PrenotazioneNumero p on (t.PrenotazioneNumeroId = p.PrenotazioneNumeroId and t.PrenotazioneId = p.PrenotazioneId)
// 						left join RT_PrenotazioneBiglietto b on (p.PrenotazioneId = b.PrenotazioneId and t.TipologiaBiglietto =b.TipologiaBiglietto)
// 						where t.TipoTitolo = 'E' and t.PrenotazioneId = ".$movimento['PrenotazioneId'];
// 				$titoliVerificare = $db->fetch_array($sql);
// 				foreach ($titoliVerificare as $t){
// 					if($t['ImportoTitolo'] < $t['PrezzoTotalePax']){
// 						//aggiorno l'importo del titolo
// 						$sql = "UPDATE RT_PrenotazioneTitolo SET ImportoTitolo = ".$t['PrezzoTotalePax']." where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
// 						$db->query($sql);

// 						//aggiorno le provvigioni
// 						$sql = "SELECT * FROM RT_PrenotazioneTitoloProvvigione where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
// 						$tempProviggioni = $db->fetch_array($sql);
// 						foreach ($tempProviggioni as $prov){
// 							$importoAgenzia = number_format ($t['PrezzoTotalePax']*$prov['PercentualeAgenzia']/100+$prov['FissoAgenzia'],2);
// 							$sql = "UPDATE RT_PrenotazioneTitoloProvvigione SET ImportoAgenzia = ".$importoAgenzia." where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
// 							$db->query($sql);
// 						}
						
// 						//aggiorno l'iva dal confine
// 						$sql = "SELECT * FROM RT_PrenotazioneTitoloIva where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
// 						$tempIva = $db->fetch_array($sql);
// 						$sql = "select * from RT_ViewImportiPerIva003 where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
// 						$scorporoR = $db->query_first($sql);
// 						if (!is_null($scorporoR['ScorporoP'])){
// 							$Scorporo = $scorporoR['ScorporoP'];
// 						} else {
// 							$Scorporo = $scorporoR['ScorporoD'];
// 						} 
// 						foreach ($tempIva as $iva){
// 							$TotImponibile = number_format (($t['PrezzoTotalePax']/count($tempIva)*$iva['KmPercorsiTerritorio'])/$iva['KmPercorsiTotale'],2);
// 							$TotIva = number_format ($TotImponibile-($TotImponibile/$Scorporo), 2);
							
// 							$sql = "UPDATE RT_PrenotazioneTitoloIva SET ImportoTitolo = ".$t['PrezzoTotalePax'].",
// 									ImportoTitoloPerConfine = $TotImponibile, ImportoIvaConfine = $TotIva
// 									where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
// 							$db->query($sql);
// 						}
// 					}
// 				}
// 			}
// 		}
		
		if($output['emetti'] == false && $output['emettiExtra'] == false && $movimento['Importo'] != $movimento['ImportoPagato']){
			if(trim($prenotazione_wizard->DatiGenerali['ClienteCellularePrefisso']) != '' && trim($prenotazione_wizard->DatiGenerali['ClienteCellulare']) != '' && $pagamentoTipo['MsgSMSStato']) {
				$output['sendSMS'] = true;
			} else {
				$output['sendSMS'] = false;
			}
	
			if(trim($prenotazione_wizard->DatiGenerali['ClienteEmail']) != '' && $pagamentoTipo['MsgEmailStato']) {
				$output['sendEmail'] = true;
			} else {
				$output['sendEmail'] = false;
			}
		} else {
			$output['sendSMS'] = false;
			$output['sendEmail'] = false;
		}
		
		//aggiorno le prenotazioni correlate
		aggiornaPrenotazioneMovimentoCorrelate($movimento, $prenotazione_wizard->DatiGenerali['CodicePrenotazione'], $prenotazione, $minScadenza);
	} else {
		$output['result'] = false;
		$output['emetti'] = false;
		$output['sendSMS'] = false;
		$output['sendEmail'] = false;
	}
	
	//se tipo pagamento = coupon incremento di 1 gli utilizzi effettuati
	$sql = "SELECT EmettiCoupon From RT_PagamentoTipo WHERE PagamentoTipoId = ".$movimento['PagamentoTipoId'];
	$rowEmetti = $db->query_first($sql);
	if($rowEmetti['EmettiCoupon'] == 1){
		$output['coupon'] = $_POST['CodiceCoupon'];
		
		$sql = "SELECT * From RT_Coupon WHERE Codice = '".$_POST['CodiceCoupon']."'";
		$rowCoupon = $db->query_first($sql);
		
		$dataCoupon['Valore'] = $rowCoupon['Valore'] - $movimento['ImportoPagato'];
		if($dataCoupon['Valore'] <= 0){
			$dataCoupon['Utilizzi'] = $rowCoupon['Utilizzi']+1;
			if($dataCoupon['Utilizzi'] < $rowCoupon['MaxUtilizzi']){
				$dataCoupon['Valore'] = $rowCoupon['Importo'];
			}
		}
		$dataCoupon = $storico->operazioni_update($dataCoupon, $user);
		$result = $db->update("RT_Coupon", $dataCoupon, "CouponId=".$rowCoupon['CouponId']);
		
		$sql = "Select TotaleResiduo FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
		$rowResiduo = $db->query_first($sql);
		if($rowResiduo['TotaleResiduo']>0){
			$movimentoNewResiduo['PrenotazioneId'] = $movimento['PrenotazioneId'];
			$movimentoNewResiduo['PagamentoTipoId'] = 7;
			$movimentoNewResiduo['TipoMovimento'] = 'P';
			$movimentoNewResiduo['Causale'] = 'Residuo da pagare dopo coupon';
			$movimentoNewResiduo['Data'] = date("Y-m-d H:i:s");
			$movimentoNewResiduo['Importo'] = $rowResiduo['TotaleResiduo'];
			$movimentoNewResiduo['Supplemento'] = 0;
			$movimentoNewResiduo['ImportoPagato'] = 0;
			$movimentoNewResiduo = $storico->operazioni_insert($movimentoNewResiduo, $user);
			$result = $db->insert("RT_PrenotazioneMovimento", $movimentoNewResiduo);
			
			
			$sql = "select * from RT_PrenotazioneMovimento where PrenotazioneId = ".$movimento['PrenotazioneId']." and Importo = '".$movimento['Importo']."'";
			$tempRow = $db->query_first($sql);	
			$movimentoF['Causale'] = 'Parte pagamento con coupon - Cod. '.$_POST['CodiceCoupon'];
			$movimentoF = $storico->operazioni_update($movimentoF, $user);
			$result = $db->update("RT_PrenotazioneMovimento", $movimentoF, "PrenotazioneMovimentoId=".$tempRow['PrenotazioneMovimentoId']." AND OdcIdRef=".$user->OdcId);
		}
	} else {
		$output['coupon'] = 0;
	}

	echo json_encode($output);
}

/* versione multi prenotazione */
function aggiornaPrenotazioneMovimentoCorrelate($movimento, $codicePrenotazione, $totaleImporti, $minScadenza) {

	global $user;
	$db = new Database();
	$db->connect();

	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	//seleziono tutte le prenotazioni correlate
	$sql = "SELECT PrenotazioneId, TotalePrenotazione FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $codicePrenotazione . "' AND PrenotazioneId != '" . $movimento['PrenotazioneId'] . "' AND OdcIdRef = " . $user->OdcId;
	$prenotazioni = $db->fetch_array($sql);
	foreach ($prenotazioni as $prenotazione) {
		$prenotazione_new['TotaleResiduo'] = ($totaleImporti['TotalePagato'] == 0)? floatval($prenotazione['TotalePrenotazione']) : 0;
		// se il pagamento � "A bordo" imposta pagato uguale a 2
		if ($movimento['PagamentoTipoId'] == "7") {
			$prenotazione_new['Pagato'] = intval(2);
		} else {
			$prenotazione_new['Pagato'] = intval($prenotazione_new['TotaleResiduo'] <= 0);
		}
		$prenotazione_new['ScadenzaPrenotazione'] = $minScadenza;
		$prenotazione_new = $storico->operazioni_update($prenotazione_new, $user);
		$result = $db->update("RT_Prenotazione", $prenotazione_new, "PrenotazioneId=".$prenotazione['PrenotazioneId']." AND OdcIdRef=".$user->OdcId);
	}
}

function calcoloDataScadenza($dataMovimento, $oraMovimento, $pagamentoTipoId){

	global $user;
	$db = new Database();
	$db->connect();

	$pagamentoTipoObj = new PagamentoTipo($pagamentoTipoId);
	$pagamentoTipoObj->conn = $db;
	$pagamentoTipoObj->inizializzaDatiGenerali();
	$pagamentoTipo = $pagamentoTipoObj->DatiGenerali;

	if(isset($pagamentoTipo['TempoScadenza']) && $pagamentoTipo['TempoScadenza'] != 0){
		$date = new DT($dataMovimento.' '.$oraMovimento,'d/m/Y H:i');
		$date->addMinutes($pagamentoTipo['TempoScadenza']);
		$dataScadenza = $date->getDate("d/m/Y");
		$oraScadenza = $date->getDate("H:i");

		echo json_encode(array('result'=>true, 'dataScadenza'=>$dataScadenza, 'oraScadenza'=>$oraScadenza));
	}else{
		echo json_encode(array('result'=>true, 'dataScadenza'=>'', 'oraScadenza'=>''));
	}

}

function calcoloSupplemento($importo, $pagamentoTipoId){
	global $user;
	$db = new Database();
	$db->connect();

	$pagamentoTipoObj = new PagamentoTipo($pagamentoTipoId);
	$pagamentoTipoObj->conn = $db;
	$pagamentoTipoObj->inizializzaDatiGenerali();
	$pagamentoTipo = $pagamentoTipoObj->DatiGenerali;

	$importo = floatval(str_replace(',', '.', str_replace('.', '', $importo)));
	$supplemento = number_format(round($pagamentoTipo['SupplementoFisso']+($importo*$pagamentoTipo['SupplementoPercentuale']/100),2), 2, ",", ".");

	echo json_encode(array('result'=>true, 'supplemento'=>$supplemento));
}

function checkCoupon($pagamentoTipoId){
	global $user;
	$db = new Database();
	$db->connect();
	
	$sql = "Select EmettiCoupon FROM RT_PagamentoTipo WHERE PagamentoTipoId = $pagamentoTipoId";
	$row = $db->query_first($sql);
	
	if($row['EmettiCoupon']==1){
		echo json_encode(array('result'=>true));
	} else {
		echo json_encode(array('result'=>false));
	}	
}

function checkCodiceCoupon($codice, $prenotazioneId){
	global $user;
	$db = new Database();
	$db->connect();
	$sql = "Select * FROM RT_Coupon WHERE Codice = '$codice' and MaxUtilizzi > Utilizzi and Stato = 1 AND (DaVendere = 0 OR (DaVendere = 1 AND VenditaStato = 2))";
	
	$row = $db->query_first($sql);
	if(isset($row['CouponId'])){
		
		$sql = "SELECT * FROM RT_PrenotazionePercorso
				where PrenotazioneId = $prenotazioneId AND Direzione = 'A'";
		$rowAndata = $db->query_first($sql);
		
		$sql = "SELECT * FROM RT_PrenotazionePercorso
				where PrenotazioneId = $prenotazioneId AND Direzione = 'R'";
		$rowRitorno = $db->query_first($sql);
		
		$resultA = true;
		$resultR = true;
		$trattaA = true;
		$trattaR = true;
		$messageA = 'OK';
		$messageR = 'OK';
		if(!isset($rowRitorno['CorsaDataPartenza'])){
			$messageR = 'NO';
		}

		// --- Fasce orarie check ---
		$fasceOrarie = array();
		$sqlFasce = "SELECT OraInizio as Dalle, OraFine as Alle FROM RT_CouponFasciaOraria WHERE CouponId = " . $row['CouponId'];
		$fasceRows = $db->fetch_array($sqlFasce);
		foreach($fasceRows as $fascia) {
			// Dalle/Alle sono in formato 'HH:MM'
			$fasceOrarie[] = array('dalle' => $fascia['Dalle'], 'alle' => $fascia['Alle']);
		}

		// Funzione di utilità per il check dell'orario in una delle fasce
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
			$orarioAndata = isset($rowAndata['CorsaOrarioPartenza']) ? substr($rowAndata['CorsaOrarioPartenza'],0,5) : null;
			if($orarioAndata && !isOrarioInFasce($orarioAndata, $fasceOrarie)) {
				$resultA = false;
				$messageA = "Fascia oraria non valida";
			}
			// Ritorno
			$orarioRitorno = isset($rowRitorno['CorsaOrarioPartenza']) ? substr($rowRitorno['CorsaOrarioPartenza'],0,5) : null;
			if($orarioRitorno && !isOrarioInFasce($orarioRitorno, $fasceOrarie)) {
				$resultR = false;
				$messageR = "Fascia oraria non valida";
			}
		}

		//verifico se la data di partenza > di validaDa
		if(isset($row['ValidoDa']) && $row['ValidoDa'] != '0000-00-00'){
			$partenza = new DateTime($rowAndata['CorsaDataPartenza']);
			$partenza->setTime(0, 0, 0);
			$datetime = new DateTime($row['ValidoDa']);
			$datetime->setTime(0, 0, 0);
			if($partenza < $datetime){
				$resultA = false;
				$messageA = "Data di partenza non valida";
			}
			
			if(isset($rowRitorno['CorsaDataPartenza'])  && $rowRitorno['CorsaDataPartenza'] != '0000-00-00') {
				$partenza = new DateTime($rowRitorno['CorsaDataPartenza']);
				$partenza->setTime(0, 0, 0);
				$datetime = new DateTime($row['ValidoDa']);
				$datetime->setTime(0, 0, 0);
				if($partenza < $datetime){
					$resultR = false;
					$messageR = "Data di partenza non valida";
				}
			}
		}
		//verifico se la data di partenza < di validaA
		if(isset($row['ValidoA']) && $row['ValidoA'] != '0000-00-00'){
			$partenza = new DateTime($rowAndata['CorsaDataPartenza']);
			$partenza->setTime(0, 0, 0);
			$datetime = new DateTime($row['ValidoA']);
			$datetime->setTime(0, 0, 0);
			if($partenza > $datetime){
				$resultA = false;
				$messageA = "Data di partenza non valida";
			}
				
			if(isset($rowRitorno['CorsaDataPartenza']) && $rowRitorno['CorsaDataPartenza'] != '0000-00-00') {
				$partenza = new DateTime($rowRitorno['CorsaDataPartenza']);
				$partenza->setTime(0, 0, 0);
				$datetime = new DateTime($row['ValidoA']);
				$datetime->setTime(0, 0, 0);
				if($partenza > $datetime){
					$resultR = false;
					$messageR = "Data di partenza non valida";
				}
			}
		}

		//verifico il comune di partenza
		if(isset($row['PartenzaId']) && $row['PartenzaId']!=0){
			   // Nuova logica: diverso e non contenuto come valore
			$partenzaIds = array_map('trim', explode(',', $row['PartenzaId']));
			if($row['PartenzaId'] != $rowAndata['ComuneSalitaId'] && !in_array($rowAndata['ComuneSalitaId'], $partenzaIds)){
				$trattaA = false;
				$resultA = false;
				$messageA = "Fermata di partenza non valida";
			}
			if(isset($rowRitorno['ComuneDiscesaId']) && ($row['PartenzaId'] != $rowRitorno['ComuneSalitaId'] && !in_array($rowRitorno['ComuneSalitaId'], $partenzaIds))){
				$trattaR = false;
				$resultR = false;
				$messageR = "Fermata di partenza non valida";
			}
		}
		
		//verifico il comune di destinazione
		if(isset($row['DestinazioneId']) && $row['DestinazioneId']!=0){
			$destinazioneIds = array_map('trim', explode(',', $row['DestinazioneId']));
			if($row['DestinazioneId'] != $rowAndata['ComuneDiscesaId'] && !in_array($rowAndata['ComuneDiscesaId'], $destinazioneIds)){
				$trattaA = false;
				$resultA = false;
				$messageA = "Fermata di destinazione non valida";
			}
			if(isset($rowRitorno['ComuneDiscesaId']) && ($row['DestinazioneId'] != $rowRitorno['ComuneDiscesaId'] && !in_array($rowRitorno['ComuneDiscesaId'], $destinazioneIds))){
				$trattaR = false;
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
		if(isset($row['LineaId']) && $row['LineaId'] != 0){
			if($row['LineaId'] == $rowAndata['LineaId'] || strpos($row['LineaId']. ',', $rowAndata['LineaId'] . ',') === false){
				$resultA = false;
				$resultR = false;
				$messageA = "Esperienza / tour non valido";
			}
		}

		//verifico il gestore
		if(isset($row['GestoreId']) && $row['GestoreId']!=0){
			if($rowAndata['GestoreIdRef'] != $row['GestoreId']){
				$resultA = false;
				$resultR = false;
				$messageA = "Gestore non valido";
			}
		}
		
		if($resultA && $resultR) {
			echo json_encode(array('result'=>true, 'importo'=>$row['Valore'], 'percentuale'=>$row['Percentuale'], 'andata'=>$messageA, 'ritorno'=>$messageR));
		} else if((!$resultA && !$resultR) || (!$resultA && !isset($dataRitorno))) {
			echo json_encode(array('result'=>false, 'message'=>'Coupon non valido', 'andata'=>$messageA, 'ritorno'=>$messageR));
		} else {
			$sql = "Select TotaleDaPagare from RT_Prenotazione where PrenotazioneId = $prenotazioneId";
			$totale = $db->query_first($sql);
			$importo = $totale['TotaleDaPagare']*$row['Percentuale']/100;
			echo json_encode(array('result'=>true, 'importo'=>"".number_format($importo, 2, ',', ' '), 'percentuale'=>0, 'andata'=>$messageA, 'ritorno'=>$messageR));
		}
	} else {
		echo json_encode(array('result'=>false, 'message'=>'Codice non valido', 'andata'=>'N.D.', 'ritorno'=>'N.D.'));
	}
}
?>
