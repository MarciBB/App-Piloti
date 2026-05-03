<?php

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();

$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "/class.Sede.php");
include_once($classespath_ . "/class.Fermata.php");
include_once($classespath_ . "/class.Tratta.php");
include_once($classespath_ . "/class.Prenotazione.php");
include_once($classespath_ . "/class.Corsa.php");
include_once($classespath_ . "/class.Linea.php");
include_once($classespath_ . "/class.Percorso.php");
include_once($classespath_ . "/class.Orario.php");
include_once($classespath_ . "/class.Listino.php");
include_once($classespath_ . "/class.TipologiaBus.php");
include_once($classespath_ . "class.DT.php");
include_once($classespath_ . "Graph/class.DisponibilitaGraph.php");
include_once($classespath_ . "Graph/class.LineaGraph.php");
include_once($classespath_ . "/class.PrenotazioneMovimento.php");
include_once($classespath_ . "Graph/class.GrafoTratte.php");
include_once($classespath_ . "/class.Sms.php");
include_once($classespath_ . "/class.ServiceWhatsapp.php");
include_once($classespath_ . "/class.ServiceGooglePeople.php");
include_once($classespath_ . "Graph/class.GraphUtil.php");
include_once($classespath_ . "/class.ServiceFiscalGateway.php");
include_once($classespath_ . "/class.ServiceSalesManago.php");
include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');


use Stripe\Stripe;

$ModuloId = 2;

global $prenotazione_wizard, $dizionarioEmail, $user;
$funzione_edit = false;
$prenotazione_wizard = null;

//controllo su operatore connesso
if(!isset($user->SedeLegale)) {
	unset($_SESSION['OPERATORE']); 
	header("Location: principale.php?start=1");
	exit;
}

if (isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
	$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
}




function RimborsoParziale() {
	//     ini_set('display_errors', 1);
	//     ini_set('error_reporting', E_ALL);

	global $user, $prenotazione_wizard, $dizionario;

	$db = new Database();
	$db->connect();
	$prenotazione_wizard->conn = $db;
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$dt = new DT();

	$PrenotazioneNumeroId = $_POST['PrenotazioneNumeroId'];
	$Tragitto = $_POST['Tragitto'];
	$ImportoRimborsabile = floatval(str_replace(',', '.', str_replace('.', '', $_POST['ValoreRimborso'])));
	$ImportoRimborsabile = $ImportoRimborsabile * (-1);
	$TipoMovimento = $_POST['TipoMovimento'];
	$PagamentoTipoId = $_POST['PagamentoTipoId'];
	$DataRimborso = $_POST['DataRimborso'] . " " . date('H:i:s', time());
	$DataRimborso = $dt->format($DataRimborso, "d/m/Y H:i:s", "Y-m-d H:i:s");
	//controllo emissione coupon
	$sql = "SELECT EmettiCoupon FROM RT_PagamentoTipo Where PagamentoTipoId = $PagamentoTipoId";
	$emettiCoupon = $db->query_first($sql);
	$codice = '';

	//recupero info rimuovi da google calendar
	$googleCalendarRimuovi = isset($_POST['GoogleCalendarRimuovi']) ? $_POST['GoogleCalendarRimuovi'] : 0;

	if($googleCalendarRimuovi == 1) {
		$dup = null;
		$dup['Escludi'] = 1;
		$dup = $storico->operazioni_update($dup, $user);

		if ($Tragitto == '0') {
			$db->update("RT_PrenotazioneDettaglio", $dup, "PrenotazioneNumero=$PrenotazioneNumeroId");
		} else {
			$db->update("RT_PrenotazioneDettaglio", $dup, "PrenotazioneNumero=$PrenotazioneNumeroId and Tragitto='$Tragitto'");
		}
	}

	$sql = "select PrenotazioneId, TipologiaBigliettoId, PasseggeroId from RT_PrenotazioneNumero where PrenotazioneNumeroId=$PrenotazioneNumeroId";
	$row1 = $db->query_first($sql);

	$PrenotazioneId = $row1['PrenotazioneId'];
	$idMovimento = null;
	if ($PrenotazioneId > 0) {
		$TipologiaBigliettoId = $row1['TipologiaBigliettoId'];
		$PasseggeroId = $row1['PasseggeroId'];

		$dup = null;
		$dup['PrenotazioneId'] = $PrenotazioneId;
		$dup['TipologiaBigliettoId'] = $TipologiaBigliettoId;
		$dup['PasseggeroId'] = $PasseggeroId;
		$dup = $storico->operazioni_insert($dup, $user);
		$NuovaPrenotazioneNumero = $db->insert("RT_PrenotazioneNumero", $dup);

		$sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId = $PrenotazioneNumeroId";
		$titolo = $db->query_first($sql);

		$movimento = array();
		$movimento['PrenotazioneId'] = $PrenotazioneId;
		$movimento['TipoMovimento'] = $TipoMovimento;
		$movimento['PagamentoTipoId'] = $PagamentoTipoId;
		$CausaleTragitto = ($Tragitto == '0') ? 'Andata e Ritorno' : $Tragitto;
		$movimento['Causale'] = "Rimborso titolo " . $titolo['Codice'] . " tragitto " . $CausaleTragitto;
		if ($emettiCoupon['EmettiCoupon'] == 1) {
			$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
			$random_string_length = 10;
			for ($i = 0; $i < $random_string_length; $i++) {
				$codiceCoupon .= $characters[rand(0, strlen($characters) - 1)];
			}
			$movimento['Causale'] .= '<br>Codice coupon di rimborso: ' . $codiceCoupon;
			$movimento['Coupon'] = $codiceCoupon;
		}
		$movimento['Data'] = $DataRimborso;
		$movimento['Importo'] = $ImportoRimborsabile;
		$movimento['Supplemento'] = 0;
		$movimento['DataPagamento'] = $DataRimborso;
		$movimento['ImportoPagato'] = $ImportoRimborsabile;
		$movimento['Scadenza'] = 'NULL';
		$movimento['CodicePagamento'] = 'NULL';
		$movimento['CanalePagamentoId'] = 'NULL';
		$movimento = $storico->operazioni_insert($movimento, $user);
		$idMovimento = $db->insert("RT_PrenotazioneMovimento", $movimento);
	}

	$sql = "select * from RT_PrenotazioneDettaglio where PrenotazioneNumero=$PrenotazioneNumeroId ";
	if ($Tragitto != '0')
		$sql .= " and Tragitto='$Tragitto'";

	$sql .= " order by TipoServizio desc";

	$ArrObject = $db->fetch_array($sql);
	$x = sizeof($ArrObject);
	$y = 0;

	$ImportoRimborsabilePerTratta = $ImportoRimborsabile;
	$ImportoResiduo = $ImportoRimborsabile;
	$arrTragitto[$Tragitto] = $ImportoResiduo;
	if ($Tragitto == '0') {
		$arrTragitto['Andata'] = $ImportoResiduo / 2;
		$arrTragitto['Ritorno'] = $ImportoResiduo / 2;
		$ImportoRimborsabilePerTratta = $ImportoRimborsabile / 2;
	}

	while ($y < $x) {
		$row = null;
		$row = $ArrObject[$y];
		$TipoServizio = $row['TipoServizio'];
		$CorsaId = $row['CorsaInizioItinerario'];
		if ($TipoServizio == 'Bus') {
			//$CorsaId=$row['CorsaInizioItinerario'];
			$DataInizio = $row['DataInizioItinerario'];
			$PrenotazioneId = $row['PrenotazioneId'];

			$sql = "Select PrenotazionePercorsoId,PasseggeriEsclusi from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and CorsaDataPartenza='$DataInizio' and CorsaId=$CorsaId";
			$row1 = $db->query_first($sql);

			$sql = "SELECT t.OccupaPosto FROM RT_PrenotazioneNumero n
					left join RT_tipologiaBiglietto t on t.TipologiaBigliettoId = n.TipologiaBigliettoId
					where n.PrenotazioneNumeroId = $PrenotazioneNumeroId";
			$rowTempPosto = $db->query_first($sql);
			if ($rowTempPosto['OccupaPosto'] == 1) {
				$esclusi = $row1['PasseggeriEsclusi'] + 1;
			} else {
				$esclusi = $row1['PasseggeriEsclusi'];
			}
			$PrenotazionePercorsoId = $row1['PrenotazionePercorsoId'];

			if($googleCalendarRimuovi == 1) {
				$dup = null;
				$dup['PasseggeriEsclusi'] = $esclusi;
				$dup = $storico->operazioni_update($dup, $user);
				$db->update("RT_PrenotazionePercorso", $dup, "PrenotazionePercorsoId=$PrenotazionePercorsoId");
			}
		}

		$row['PrenotazioneDettaglioId'] = null;
		unset($row['PrenotazioneDettaglioId']);
		$row['PrenotazioneNumero'] = $NuovaPrenotazioneNumero;
		$ImportoOldPrenotazione = $row['Importo'];
		$TragittoCorrente = $row['Tragitto'];
		$row['Importo'] = $ImportoRimborsabilePerTratta;
		$Venduto = $row['Importo'];
		$s = "select LineaId from RT_Corsa where CorsaId=$CorsaId";
		$rows = $db->query_first($s);

		$LineaId = $rows['LineaId'];
		$sql33 = "select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and LineaId=$LineaId";

		$row33 = $db->query_first($sql33);
		$PercAge = 0;
		$FissoAge = 0;
		if ($row33['GestoreConvenzioneId'] > 0) {
			$PercAge = $row33['Percentuale'];
			$FissoAge = $row33['Fisso'];
		}

		$ImportoBase = $Venduto * (-1);
		$ImportoAgenziaNetto = number_format($ImportoBase * ($PercAge / 100) + $FissoAge, 4);

		/*$row['ImportoAgenzia']=$ImportoAgenziaNetto;
	    $row['PercentualeAgenzia']=$PercAge;
	    $row['FissoAgenzia']=$FissoAge;
	    $row['AliquotaBiglietto']=$aliquota_b;
	    $row['AliquotaProvvigione']=$aliquota_p;  
	    $row['DaBonificare']=$DaBonificare; 
	    $row['DaFatturare']=$DaFatturare; */

		if ($user->GestoreId == 1) {
			$row['DaBonificare'] = 0;
			$row['DaFatturare'] = 0;
		}

		$row['Rimborso'] = 1;
		$row['Escludi'] = 1;

		$row = $storico->operazioni_insert($row, $user);
		$db->insert("RT_PrenotazioneDettaglio", $row);
		$del = $db->delete("RT_PrenotazionePosto", "PrenotazioneNumeroId=$PrenotazioneNumeroId and CorsaId=$CorsaId");

		$y++;
	}

	$PrenotazioneObj = new Prenotazione($PrenotazioneId);
	$PrenotazioneObj->conn = $db;
	$codice = $PrenotazioneObj->EmettiBigliettiRimborso($NuovaPrenotazioneNumero, $ImportoRimborsabile);
	if (isset($idMovimento)) {
		$movimento['Causale'] .= '- Titolo ' . $codice;
		$db->update("RT_PrenotazioneMovimento", $movimento, "PrenotazioneMovimentoId=" . $idMovimento);
	}

	// setta lo stato rimborsato se solo se � completamente rimborsata e si decide di rimuovere il tour
	if($googleCalendarRimuovi == 1) {
		$PrenotazioneObj->isRimborsata($PrenotazioneId);
	}

	$modifica = 0;


	//Emissione Coupon
	if ($emettiCoupon['EmettiCoupon'] == 1) {
		$sql = "SELECT * 
			FROM RT_PrenotazioneTitolo Where 
			PrenotazioneId = $PrenotazioneId and PrenotazioneNumeroId = $NuovaPrenotazioneNumero and TipoTitolo = 'R'";
		$titoloInfo = $db->query_first($sql);
		$coupon['CouponNome'] = 'Rimborso per biglietto ' . $titoloInfo['Codice'] . '/' . $titoloInfo['Anno'];
		$coupon['Importo'] = -$ImportoRimborsabile;
		$coupon['MaxUtilizzi'] = 1;
		$coupon['Valore'] = $coupon['Importo'];
		$coupon['Utilizzi'] = 0;
		$coupon['Codice'] = $codiceCoupon;
		$coupon['TitoloRimborsatoId'] = $titoloInfo['PrenotazioneTitoloId'];
		$coupon = $storico->operazioni_insert($coupon, $user);
		$result = $db->insert("RT_Coupon", $coupon);
	}

	/**controllo disponibilita posti**/
	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoA = $prenotazione_wizard->DatiGeneraliPercorso;
	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
	$DatiGeneraliPercorsoR = $prenotazione_wizard->DatiGeneraliPercorso;
	aggiornaDisponibilita($DatiGeneraliPercorsoA['CorsaId'], $DatiGeneraliPercorsoA['CorsaDataPartenza']);
	if (isset($DatiGeneraliPercorsoR['CorsaDataPartenza'])) {
		aggiornaDisponibilita($DatiGeneraliPercorsoR['CorsaId'], $DatiGeneraliPercorsoR['CorsaDataPartenza']);
	}
	/**fine controllo disponibilita posti**/

	//annulla ricevuta emessa con fiscal gateway
	if(isset($idMovimento) && $emettiCoupon['EmettiCoupon'] != 1) {
		//se è stata emessa una ricevuta con fiscal gateway viene annullata ed il metodo gestisce già il caso di ricevuta non emessa
		$annullataRicevuta = fiscalGatewayAnnullaRicevuta($idMovimento);
	}
	
	echo ("ok" . '_' . $modifica . '_' . $PrenotazioneId . '_' . 3 . '_' . $CorsaId);
}

function RimborsoExtra()
{
	ini_set('display_errors', 0);
	ini_set('error_reporting', E_ALL);

	global $user, $prenotazione_wizard;
	$db = new Database();
	$db->connect();

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$dt = new DT();

	$PrenotazioneNumeroId = $_POST['PrenotazioneNumeroId'];
	$Tragitto = $_POST['Tragitto'];
	$ImportoRimborsabile = floatval(str_replace(',', '.', str_replace('.', '', $_POST['ValoreRimborso'])));
	$ImportoRimborsabile = $ImportoRimborsabile * (-1);
	$TipoMovimento = $_POST['TipoMovimento'];
	$CodiceTitolo = $_POST['CodiceTitolo'];
	$PagamentoTipoId = $_POST['PagamentoTipoId'];
	$DataRimborso = $_POST['DataRimborso'] . " " . date('H:i:s', time());
	$DataRimborso = $dt->format($DataRimborso, "d/m/Y H:i:s", "Y-m-d H:i:s");
	//controllo emissione coupon
	$sql = "SELECT EmettiCoupon FROM RT_PagamentoTipo Where PagamentoTipoId = $PagamentoTipoId";
	$emettiCoupon = $db->query_first($sql);
	$codice = '';

	// 	$dup = null;
	// 	$dup['Escludi'] = 1;
	// 	$dup = $storico->operazioni_update($dup,$user);

	// 	if ($Tragitto == '0') {
	// 		$db->update("RT_PrenotazioneDettaglio", $dup,"PrenotazioneNumero=$PrenotazioneNumeroId");
	// 	} else {
	// 		$db->update("RT_PrenotazioneDettaglio", $dup,"PrenotazioneNumero=$PrenotazioneNumeroId and Tragitto='$Tragitto'");
	// 	}

	$sql = "select PrenotazioneId, TipologiaBigliettoId, PasseggeroId from RT_PrenotazioneNumero where PrenotazioneNumeroId=$PrenotazioneNumeroId";
	$row1 = $db->query_first($sql);

	$PrenotazioneId = $row1['PrenotazioneId'];
	$idMovimento = null;
	if ($PrenotazioneId > 0) {
		$TipologiaBigliettoId = $row1['TipologiaBigliettoId'];
		$PasseggeroId = $row1['PasseggeroId'];

		// 		$dup = null;
		// 		$dup['PrenotazioneId'] = $PrenotazioneId;
		// 		$dup['TipologiaBigliettoId'] = $TipologiaBigliettoId;
		// 		$dup['PasseggeroId'] = $PasseggeroId;
		// 		$dup = $storico->operazioni_insert($dup,$user);
		// 		$NuovaPrenotazioneNumero = $db->insert("RT_PrenotazioneNumero", $dup);
		$NuovaPrenotazioneNumero = $PrenotazioneNumeroId;
		$sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId = $PrenotazioneNumeroId";
		$titolo = $db->query_first($sql);

		$movimento = array();
		$movimento['PrenotazioneId'] = $PrenotazioneId;
		$movimento['TipoMovimento'] = $TipoMovimento;
		$movimento['PagamentoTipoId'] = $PagamentoTipoId;
		$CausaleTragitto = ($Tragitto == '0') ? 'Andata e Ritorno' : $Tragitto;
		$movimento['Causale'] = "Rimborso titolo " . $CodiceTitolo . " tragitto " . $CausaleTragitto;
		if ($emettiCoupon['EmettiCoupon'] == 1) {
			$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
			$random_string_length = 10;
			for ($i = 0; $i < $random_string_length; $i++) {
				$codiceCoupon .= $characters[rand(0, strlen($characters) - 1)];
			}
			$movimento['Causale'] .= '<br>Codice coupon di rimborso: ' . $codiceCoupon . ' ';
			$movimento['Coupon'] .= $codiceCoupon;
		}
		$movimento['Data'] = $DataRimborso;
		$movimento['Importo'] = $ImportoRimborsabile;
		$movimento['Supplemento'] = 0;
		$movimento['DataPagamento'] = $DataRimborso;
		$movimento['ImportoPagato'] = $ImportoRimborsabile;
		$movimento['Scadenza'] = 'NULL';
		$movimento['CodicePagamento'] = 'NULL';
		$movimento['CanalePagamentoId'] = 'NULL';
		$movimento = $storico->operazioni_insert($movimento, $user);
		$idMovimento = $db->insert("RT_PrenotazioneMovimento", $movimento);
	}

	$sql = "select * from RT_PrenotazioneDettaglio where PrenotazioneNumero=$PrenotazioneNumeroId ";
	if ($Tragitto != '0')
		$sql .= " and Tragitto='$Tragitto'";

	$sql .= " order by TipoServizio desc";

	$ArrObject = $db->fetch_array($sql);
	$x = sizeof($ArrObject);
	$y = 0;

	$ImportoRimborsabilePerTratta = $ImportoRimborsabile;
	$ImportoResiduo = $ImportoRimborsabile;
	$arrTragitto[$Tragitto] = $ImportoResiduo;
	if ($Tragitto == '0') {
		$arrTragitto['Andata'] = $ImportoResiduo / 2;
		$arrTragitto['Ritorno'] = $ImportoResiduo / 2;
		$ImportoRimborsabilePerTratta = $ImportoRimborsabile / 2;
	}

	$CorsaId = $ArrObject[0]['CorsaInizioItinerario'];

	$PrenotazioneObj = new Prenotazione($PrenotazioneId);
	$PrenotazioneObj->conn = $db;
	$codice = $PrenotazioneObj->EmettiBigliettiRimborsoExtra($NuovaPrenotazioneNumero, $ImportoRimborsabile);
	if (isset($idMovimento)) {
		$movimento['Causale'] .= '- Titolo ' . $codice;
		$db->update("RT_PrenotazioneMovimento", $movimento, "PrenotazioneMovimentoId=" . $idMovimento);
	}
	// setta lo stato rimborsato se solo se � completamente rimborsata
	$PrenotazioneObj->isRimborsata($PrenotazioneId);

	$modifica = 0;


	//Emissione Coupon

	if ($emettiCoupon['EmettiCoupon'] == 1) {
		$sql = "SELECT *
		FROM RT_PrenotazioneTitolo Where
		PrenotazioneId = $PrenotazioneId and PrenotazioneNumeroId = $NuovaPrenotazioneNumero and TipoTitolo = 'R'";
		$titoloInfo = $db->query_first($sql);
		$coupon['CouponNome'] = 'Rimborso per biglietto ' . $titoloInfo['Codice'] . '/' . $titoloInfo['Anno'];
		$coupon['Importo'] = -$ImportoRimborsabile;
		$coupon['MaxUtilizzi'] = 1;
		$coupon['Utilizzi'] = 0;

		$coupon['Codice'] = $codiceCoupon;
		$coupon = $storico->operazioni_insert($coupon, $user);
		$result = $db->insert("RT_Coupon", $coupon);
	}
	echo ("ok" . '_' . $modifica . '_' . $PrenotazioneId . '_' . 3 . '_' . $CorsaId);
}




function GetImportoMassimoRimborsabile()
{
	global $user, $prenotazione_wizard, $dizionario;

	$db = new Database();
	$db->connect();

	$Tragitto = $_POST['Tragitto'];
	$PrenotazioneNumeroId = $_POST['PrenotazioneNumeroId'];
	$Codice = $_POST['Codice'];

	//recupero info se � ritorno open
	$sql = "select * from RT_PrenotazioneNumero n
			left join RT_Prenotazione p on p.PrenotazioneId = n.PrenotazioneId
			where
			n.PrenotazioneNumeroId = " . $PrenotazioneNumeroId;
	$rowTemp = $db->query_first($sql);
	$isOpen = $rowTemp['RitornoOpen'];

	//recupero info andata/ritorno
	$sql = "select count(*) as viaggi from RT_PrenotazioneNumero n
	left join RT_Prenotazione p on p.PrenotazioneId = n.PrenotazioneId
	left join RT_PrenotazionePercorso pp on pp.PrenotazioneId = n.PrenotazioneId and pp.PrenotazioneId = p.PrenotazioneId
	where
	n.PrenotazioneNumeroId = " . $PrenotazioneNumeroId;
	$rowTemp = $db->query_first($sql);
	$viaggi = $rowTemp['viaggi'];

	$sql = "SELECT SUM(Importo) Importo, PrenotazioneId, DataPartenza, OrarioPartenza, LineaId, ComunePartenza, ComuneArrivo FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero = $PrenotazioneNumeroId";
	if ($Tragitto != '0') {
		$sql .= " AND Tragitto = '$Tragitto'";
	}

	$result = $db->query_first($sql);

	//recupero l'importo del titolo da rimborsare
	$sql = "SELECT ImportoTitolo, PasseggeroId FROM RT_PrenotazioneTitolo where Codice = '$Codice'";
	$rowTempTitolo = $db->query_first($sql);

	//recupero l'importo del titolo già rimborsato
	$sql = "SELECT sum(ImportoTitolo) as Rimborso FROM RT_PrenotazioneTitolo where TipoTitolo = 'R' AND PasseggeroId = " . $rowTempTitolo['PasseggeroId'];
	$rowRimborsoTitolo = $db->query_first($sql);


	$ImportoRimborso = (isset($rowRimborsoTitolo['Rimborso'])) ? $rowRimborsoTitolo['Rimborso'] : 0;
	$Importo = $rowTempTitolo['ImportoTitolo'];
	$dataInizioCorsa = $result['DataPartenza'] . " " . $result['OrarioPartenza'];
	$lineaId = $result['LineaId'];

	$sql = "SELECT TotaleResiduo, TotalePaxPrenotati FROM RT_Prenotazione WHERE PrenotazioneId = " . $result['PrenotazioneId'];
	$result2 = $db->query_first($sql);
	$TotalePaxPrenotati = 1;
	if(isset($result2['TotalePaxPrenotati'])) {
		$Residuo = $result2['TotaleResiduo'] / $TotalePaxPrenotati;
	} else {
		$Residuo = 0;
	}
	

	$MaxRimborsabile = $Importo - $Residuo + $ImportoRimborso;

	if ($viaggi == 2 && $Tragitto != '0') {
		$MaxRimborsabile = $MaxRimborsabile / 2;
	}
	$MaxRimborsabile = number_format($MaxRimborsabile, 2, ",", ".");

	/*calcolo della penale*/
	$sql = "SELECT r.idnazione FROM Comune c
		LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
		LEFT JOIN Regione r on r.RegioneId = p.RegioneId
		where Comune = '" . $result['ComunePartenza'] . "'";
	$partenzaN = $date = $db->query_first($sql);
	$sql = "SELECT r.idnazione FROM Comune c
		LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
		LEFT JOIN Regione r on r.RegioneId = p.RegioneId
		where Comune = '" . $result['ComuneArrivo'] . "'";
	$arrivoN = $date = $db->query_first($sql);
	$tipoCorsa = 'I';
	if ($partenzaN['idnazione'] == $arrivoN['idnazione']) {
		$tipoCorsa = 'N';
	}

	$sql = "SELECT RimborsoRegolaId, NomeRegola, GiorniPrima, OrePrima FROM RT_RimborsoRegola 
			Where TipoPrenotazione like '%" . $tipoCorsa . "%' and Stato=1
			order by GiorniPrima desc, OrePrima desc";
	$regole = $db->fetch_array($sql);

	$dateSoglia = array();
	foreach ($regole as $key => $row) {
		$datetime = new DateTime($dataInizioCorsa);
		$datetime->modify('-' . $row['GiorniPrima'] . ' day');
		$datetime->modify('-' . $row['OrePrima'] . ' hour');
		$dateSoglia[$row['RimborsoRegolaId']] = $datetime;
	}
	$today = new DateTime();
	if (($Tragitto == 'Ritorno' && $isOpen == 1) || ($today < $dateSoglia[$regole[0]['RimborsoRegolaId']])) {
		$data = array('importo' => $MaxRimborsabile, 'penale' => '',);
		echo json_encode($data);
	} else {
		$selectDate = $regole[0]['RimborsoRegolaId'];
		foreach ($dateSoglia as $id => $dataCheck) {
			if ($today > $dataCheck) {
				$selectDate = $id;
			}
		}
		$sql = "Select * FROM RT_RimborsoPenale 
		left join RT_RimborsoRegola on (RT_RimborsoRegola.RimborsoRegolaId = RT_RimborsoPenale.RimborsoRegolaId) 
		where RT_RimborsoPenale.LineaId = $lineaId and RT_RimborsoPenale.RimborsoRegolaId = $selectDate";
		$penale = $db->fetch_array($sql);

		if ($user->IsAdmin) {
			$penale[0]['Fisso'] = 0;
			$penale[0]['Percentuale'] = 0;
		}

		$MaxRimborsabile = $Importo - $Residuo - $penale[0]['Fisso'] - ($Importo * $penale[0]['Percentuale'] / 100) + $ImportoRimborso;
		if ($viaggi == 2 && $Tragitto != '0') {
			$MaxRimborsabile = $MaxRimborsabile / 2;
		}
		$MaxRimborsabile = number_format($MaxRimborsabile, 2, ",", "");
		$Rimborsabile = $Importo - $Residuo;
		if ($viaggi == 2 && $Tragitto != '0') {
			$Rimborsabile = $Rimborsabile / 2;
		}
		$Rimborsabile = number_format($Rimborsabile, 2, ",", ".");

		if ($user->IsAdmin) {
			$messaggioPenale = $dizionario['biglietto']['no_penale_admin'];
		} else {
			if ($Tragitto != '') {
				$messaggioPenale = $dizionario['biglietto']['m_penale'] . " " . $Rimborsabile . "&euro;,<br>" . $dizionario['biglietto']['m_penale2'] . " " . $penale[0]['Fisso'] .
					"&euro; " . $dizionario['biglietto']['m_penale3'] . " " . $penale[0]['Percentuale'] . "%<br>" . $dizionario['biglietto']['m_penale4'] . " " .
					$penale[0]['GiorniPrima'] . " " . $dizionario['biglietto']['m_penale5'] . " " . $penale[0]['OrePrima'] . " " . $dizionario['biglietto']['m_penale6'];
			} else {
				$messaggioPenale = '';
			}
		}
		$data = array('importo' => $MaxRimborsabile, 'penale' => $messaggioPenale,);
		echo json_encode($data);
	}
}


function CalcolaPrezzo()
{
	global $user, $HtmlCommon;
	$page = new Form();
	$db = new Database();
	$db->connect();
	$prenotazione = new Prenotazione();
	$prenotazione->conn = $db;

	$prezzo_finale = $prenotazione->CalcolaPrezzo(null, null, null);

	$db->close();
}

function MostraSchemaBus()
{
	global $user, $HtmlCommon, $prenotazione_wizard, $dizionario;;
	$page = new Form();
	$db = new Database();
	$db->connect();

	$CorsaId = $_REQUEST['CorsaId'];
	$Data = $_REQUEST['Data'];
	$CorsaIdA = $_REQUEST['CorsaAndataId'];
	$CorsaIdR = $_REQUEST['CorsaRitornoId'];
	$DataCorsaAndata = $_REQUEST['DataAndata'];
	$DataCorsaRitorno = $_REQUEST['DataRitorno'];
	$tipoviaggio = $_REQUEST['TipoViaggio'];
	$PrenotazioneId = 0;
	if (is_object(($prenotazione_wizard))) {
		$PrenotazioneId = $prenotazione_wizard->Id;
	}

?>




	<script>
		$(function() {
			$("#tabs_posti").tabs();

		});
	</script>



	<div id="tabs_posti">
		<ul>
			<li class="tab_chiudi"><a href="#tabs-100"><?= $dizionario['generale']['chiudi'] ?></a></li>
			<li><a href="#tabs-101"><?= $dizionario['biglietto']['preferenza_andata'] ?></a></li>
			<li><a href="#tabs-102"><?= $dizionario['biglietto']['preferenza_ritorno'] ?></a></li>
		</ul>
		<div id="tabs-100"></div>

		<?
		$f = 0;
		$riservati_a = 0;
		$riservati_r = 0;
		while ($f < $tipoviaggio) {

			$d = $f + 1;
			$postodiar = "Andata";
			if ($f == 1)
				$postodiar = "Ritorno";

		?>

			<div id="tabs-10<?= $d ?>">
				<div class="disposizionepiani">
					<!-- LEGENDA -->
					<table class="legenda">
						<tbody>
							<tr>
								<td colspan="2">
									<h2>Legenda</h2>
								</td>
							</tr>
							<tr>
								<td class="legenda_simbolo"><img src="../images/empty_seat.png" alt="Posto Libero" /></td>
								<td class="legenda_testo">
									<p>Posto Libero</p>
								</td>
							</tr>
							<tr>
								<td class="legenda_simbolo"><img src="../images/taken_seat.png" alt="Posto Prenotato" /></td>
								<td class="legenda_testo">
									<p>Posto Prenotato</p>
								</td>
							</tr>
							<tr>
								<td class="legenda_simbolo"><img src="../images/male_seat.png" alt="Posto Uomo" /></td>
								<td class="legenda_testo">
									<p>Posto Occupato Maschile</p>
								</td>
							</tr>
							<tr>
								<td class="legenda_simbolo"><img src="../images/girl_seat.png" alt="Posto Donna" /></td>
								<td class="legenda_testo">
									<p>Posto Occupato Femminile</p>
								</td>
							</tr>
							<tr>
								<td class="legenda_simbolo"><img src="../images/not_seat.png" alt="Posto Occupato" /></td>
								<td class="legenda_testo">
									<p>Posto Occupato Non Specificato</p>
								</td>
							</tr>
							<tr>
								<td class="legenda_simbolo">n.p.</td>
								<td class="legenda_testo">
									<p>Nessuna Preferenza</p>
								</td>
							</tr>
							<tr>
								<td class="legenda_simbolo">p.i.</td>
								<td class="legenda_testo">
									<p>Piano Inferiore</p>
								</td>
							</tr>
						</tbody>
					</table>
					<!-- FINE LEGENDA -->
					<h2>
						<span class="brain_colorh2">disposizione dei posti a sedere per la corsa di <?= $postodiar ?></span>
					</h2>
					<?
					$cor = new Corsa($CorsaIdA);
					$cor->conn = $db;
					$cor->inizializzaDatiGenerali();
					$arr_cor = $cor->DatiGenerali;
					$TipologiaBusId = $arr_cor['TipologiaBusDefaultId'];

					$tb = new TipologiaBus($TipologiaBusId);
					$tb->conn = $db;
					$tb->inizializzaDatiGenerali();
					$arr_tb = $tb->DatiGenerali;

					$npiani = 1;
					$NumeroPiani = $arr_tb['NumeroPiani'];
					$NumeroColonne = $arr_tb['Colonne'];
					$NumeroRighe = $arr_tb['Righe'];
					// $TipologiaBusId=3;

					while ($npiani <= $NumeroPiani) {
					?>
						<div style="width: 20%; float: both;" class="disposizionepiani">
							<h2>Disposizione posti piano <?= $npiani ?></h2>
							<table style="width: 97%;" cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
								<tbody>
									<tr class="rowIntestazione">
										<td></td>
										<?
										$i = 0;
										$alphabet = array(
											'A',
											'B',
											'C',
											'D',
											'E',
											'F',
											'G',
											'H',
											'I',
											'J',
											'K',
											'L',
											'M',
											'N',
											'O',
											'P',
											'Q',
											'R',
											'S',
											'T',
											'U',
											'V',
											'W',
											'X',
											'Y',
											'Z'
										);
										while ($i < $NumeroColonne) {
										?>
											<td><?= $alphabet[$i] ?> </td>
										<?
											$i++;
										}
										?>
									</tr>
									<?

									$i = 0;

									while ($i < $NumeroRighe) {

									?>
										<tr>
											<td><?= $i + 1 ?></td>
											<?
											$n = 0;
											while ($n < $NumeroColonne) {
												// $BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];
												$fisso = "";
												$percentuale = "";
												$rigacorrente = $i + 1;
												$colonnacorrente = $n + 1;
												$sql = "Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and TipologiaBusId=$TipologiaBusId and OdcIdRef=$user->OdcId";
												// echo($sql);
												$row1 = $db->query_first($sql);
												$NumeroPosto = "";
												$DescrizionePosto = "";
												if (!empty($row1['TipologiaBusId'])) {
													$NumeroPosto = $row1['NumeroPosto'];
													$DescrizionePosto = $row1['DescrizionePosto'];
												}

												// echo($sql);
												$CorsaId = $CorsaIdA;
												$Data = $DataCorsaAndata;
												$st = "A";
												if ($f == 1) {
													$st = "R";
													$CorsaId = $CorsaIdR;
													$Data = $DataCorsaRitorno;
												}

											?>
												<td>

													<?

													$sql = "Select * from RT_ViewCorsaDataElencoPostiPrenotati where TipoPrenotazione=1 and Piano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and CorsaId=$CorsaId and CorsaDataPartenza='$Data' and OdcIdRef=$user->OdcId";
													// echo($sql);

													$row2 = $db->query_first($sql);
													$isReserved = 0;
													$Pid = 0;
													$Preferenza = 0;
													$TipoPrenotazione = 0;
													$ClienteSessoId = 0;
													$clienteNome = "";
													if (!empty($row2['OdcIdRef'])) {

														// print_r($row2);
														$isReserved = 1;
														$Preferenza = $row2['PreferenzaPiano'];
														$Pid = $row2['PrenotazioneId'];
														$TipoPrenotazione = $row2['TipoPrenotazione'];
														$ClienteSessoId = $row2['ClienteSessoId'];
														$clienteNome = $row2['ClienteNome'];
													}

													if (!$isReserved) {

														if ($NumeroPosto > 0) {

															// echo($DescrizionePosto);
													?>
															<center>
																<input class="stile_sedile" onclick="javascript:ScegliPosto(this,'<?= strtolower($postodiar) ?>');" type="checkbox" alt="<?= $DescrizionePosto ?>" name="PostoScelto<?= $st ?>[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]" value="<?= $NumeroPosto ?>"><label for="PostoScelto<?= $st ?>[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]"></label>
																<br /> <br />
																<? if (($rigacorrente > 0) and ($rigacorrente <= 5)) { ?>
																	<select name="PostoScelto<?= $st ?>I[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]">
																		<option value="0">n.p.</option>
																		<option value="1">p.i.</option>
																	</select>
																<?

																} else {
																?>
																	<input type="hidden" name="PostoSceltoI[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]" value="0" />
																<?
																}
																?>

															</center>



															<?

														}
													} else {

														if ($PrenotazioneId > 0) {

															if ($PrenotazioneId == $Pid) 							// prenotazione corrente
															{
																if ($f == 0)
																	$riservati_a++;
																else
																	$riservati_r++;

															?>
																<center>
																	<input checked class="stile_sedile" onclick="javascript:ScegliPosto(this,'<?= strtolower($postodiar) ?>');" type="checkbox" alt="<?= $DescrizionePosto ?>" name="PostoScelto<?= $st ?>[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]" value="<?= $NumeroPosto ?>"><label for="PostoScelto<?= $st ?>[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]"></label>
																	<br /> <br /> <select name="PostoScelto<?= $st ?>I[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]">
																		<option <? if ($Preferenza == 0) echo ("selected") ?> value="0">n.p.</option>
																		<option <? if ($Preferenza == 1) echo ("selected") ?> value="1">p.i.</option>
																	</select>
																</center> <span class="nome_cliente"><?= $clienteNome ?></span>
															<?
															} else {
																if ($TipoPrenotazione == 1) {
																	if ($ClienteSessoId == 1)
																		print("<center><strong><img src='../images/male_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>");
																	elseif ($ClienteSessoId == 2)
																		print("<center><strong><img src='../images/girl_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>");
																	else
																		print("<center><strong><img src='../images/not_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>");
																}

																/*
								 * else { ?> <center> <input checked class="stile_sedile" onclick="javascript:ScegliPosto(this,'<?=strtolower($postodiar)?>');" type="checkbox" alt="<?=$DescrizionePosto?>" name="PostoScelto[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]" value="<?=$NumeroPosto?>"><label for="PostoScelto[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"></label> <br /> <br /> <select name="PostoSceltoI[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"> <option <? if ($Preferenza==0) echo ("selected")?> value="0">n.p.</option> <option <? if ($Preferenza==1) echo ("selected")?> value="1">p.i.</option> </select> </center> <span class="nome_cliente">1111<?=$clienteNome?></span> <? }
								 */
															}
														} else {
															if ($TipoPrenotazione == 1) {
																if ($ClienteSessoId == 1)
																	print("<center><strong><img src='../images/male_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>");
																elseif ($ClienteSessoId == 2)
																	print("<center><strong><img src='../images/girl_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>");
																else
																	print("<center><strong><img src='../images/not_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>");
															} else {
															?>
																<center>
																	<input checked class="stile_sedile" onclick="javascript:ScegliPosto(this,'<?= strtolower($postodiar) ?>');" type="checkbox" alt="<?= $DescrizionePosto ?>" name="PostoScelto<?= $st ?>[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]" value="<?= $NumeroPosto ?>"><label for="PostoScelto<?= $st ?>[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]"></label>
																	<br /> <br /> <select name="PostoScelto<?= $st ?>I[<?= $npiani . "_" . $rigacorrente . "_" . $colonnacorrente . "_" . $CorsaId . "_" . $TipologiaBusId ?>]">
																		<option <? if ($Preferenza == 0) echo ("selected") ?> value="0">n.p.</option>
																		<option <? if ($Preferenza == 1) echo ("selected") ?> value="1">p.i.</option>
																	</select>
																</center> <span class="nome_cliente">Fabio Della Selva</span>
													<?
															}
														}
													}

													?>
												</td>


											<?

												$n++;
											}
											?>



										</tr>
									<?
										$i++;
									}

									?>

								</tbody>
							</table>
						</div>
						<input id="posti_riservati_a" name="posti_riservati_a" value="<?= $riservati_a ?>" type="hidden"> <input id="posti_riservati_r" name="posti_riservati_r" value="<?= $riservati_r ?>" type="hidden">


					<?
						$npiani++;
					}
					?>

				</div>
			</div>
		<?
			$f++;
		}
		?>
	</div>

<?
	exit();
}

function GetNotePerTratta()
{
	global $user, $HtmlCommon, $prenotazione_wizard, $dizionario;
	$page = new Form();
	$db = new Database();
	$db->connect();

	$CorsaIdA = $_REQUEST['CorsaAndataId'];
	$CorsaIdR = $_REQUEST['CorsaRitornoId'];
	$DataA = $_REQUEST['CorsaDataAndata'];
	$DataR = $_REQUEST['CorsaDataRitorno'];
	$PartenzaId = $_REQUEST['PartenzaId'];
	$DestinazioneId = $_REQUEST['DestinazioneId'];

	$Tp = $_REQUEST['TV'];

	$Nota1A = "";
	$Nota2A = "";
	$Nota3A = "";
	$Nota4A = "";
	$Nota5A = "";
	$Nota1R = "";
	$Nota2R = "";
	$Nota3R = "";
	$Nota4R = "";
	$Nota5R = "";

	if (is_object(($prenotazione_wizard))) {
		$prenotazione_wizard->conn = $db;
		$PrenotazioneId = $prenotazione_wizard->Id;
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		$NomeCliente = $DatiGeneraliArr['ClienteNome'];
		$CellulareCliente = $DatiGeneraliArr['ClienteCellulare'];
		$SessoIdCliente = $DatiGeneraliArr['ClienteSessoId'];
		$TipoViaggioId = $DatiGeneraliArr['TipoViaggioId'];

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;

		$PrenotazionePercorsoA = $DatiGeneraliPercorsoArr['PrenotazionePercorsoId'];

		$TipoNota = 'S';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";

		$row1 = $db->query_first($sql);
		$Nota1A = "";
		if (!empty($row1['Nota']))
			$Nota1A = $row1['Nota'];

		$TipoNota = 'D';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first($sql);
		$Nota2A = "";
		if (!empty($row1['Nota']))
			$Nota2A = $row1['Nota'];

		$TipoNota = 'B';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first($sql);
		$Nota3A = "";
		if (!empty($row1['Nota']))
			$Nota3A = $row1['Nota'];

		$TipoNota = 'P';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first($sql);
		$Nota4A = "";
		if (!empty($row1['Nota']))
			$Nota4A = $row1['Nota'];

		$TipoNota = 'G';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first($sql);
		$Nota5A = "";
		if (!empty($row1['Nota']))
			$Nota5A = $row1['Nota'];

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
		$DatiGeneraliPercorsoArrR = $prenotazione_wizard->DatiGeneraliPercorso;
		$PrenotazionePercorsoA = $DatiGeneraliPercorsoArrR['PrenotazionePercorsoId'];

		$TipoNota = 'S';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";

		$row1 = $db->query_first($sql);
		$Nota1R = "";
		if (!empty($row1['Nota']))
			$Nota1R = $row1['Nota'];

		$TipoNota = 'D';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first($sql);
		$Nota2R = "";
		if (!empty($row1['Nota']))
			$Nota2R = $row1['Nota'];

		$TipoNota = 'B';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first($sql);
		$Nota3R = "";
		if (!empty($row1['Nota']))
			$Nota3R = $row1['Nota'];

		$TipoNota = 'P';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first($sql);
		$Nota4R = "";
		if (!empty($row1['Nota']))
			$Nota4R = $row1['Nota'];

		$TipoNota = 'G';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first($sql);
		$Nota5R = "";
		if (!empty($row1['Nota']))
			$Nota5R = $row1['Nota'];
	}

?>

	<script>
		$(function() {
			$("#tabs").tabs();
			$("#tabs1").tabs();
			$("#tabs2").tabs();
		});
	</script>
	<table>
		<tr>
			<td>
				<div id="BoxNoteAndata">
					<h3><?= $dizionario['biglietto']['note_andata'] ?></h3>

					<div id="tabs">
						<ul>
							<li><a href="#tabs-10"><?= $dizionario['biglietto']['note_salita'] ?></a></li>
							<li><a href="#tabs-11"><?= $dizionario['biglietto']['note_discesa'] ?></a></li>
							<li><a href="#tabs-12"><?= $dizionario['biglietto']['note_biglietto'] ?></a></li>
							<li><a href="#tabs-13"><?= $dizionario['biglietto']['note_posti'] ?></a></li>
							<li><a href="#tabs-14"><?= $dizionario['biglietto']['note_generiche'] ?></a></li>
						</ul>
						<div id="tabs-10">
							<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_fermata_salita'] ?></p>
							<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdA ?>_1]" 
								onkeydown="if(event.key === 'Enter') { 
									event.preventDefault(); 
									this.value += '\n'; 
									this.focus(); 
								}"><?= $Nota1A ?></textarea>
						</div>
						<div id="tabs-11">
							<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_fermata_discesa'] ?></p>
							<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdA ?>_2]" 
								onkeydown="if(event.key === 'Enter') { 
									event.preventDefault(); 
									this.value += '\n'; 
									this.focus(); 
								}"><?= $Nota2A ?></textarea>
						</div>
						<div id="tabs-12">
							<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_biglietto_r'] ?></p>
							<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdA ?>_3]" 
								onkeydown="if(event.key === 'Enter') { 
									event.preventDefault(); 
									this.value += '\n'; 
									this.focus(); 
								}"><?= $Nota3A ?></textarea>
						</div>
						<div id="tabs-13">
							<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_posto_r'] ?></p>
							<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdA ?>_4]" 
								onkeydown="if(event.key === 'Enter') { 
									event.preventDefault(); 
									this.value += '\n'; 
									this.focus(); 
								}"><?= $Nota4A ?></textarea>
						</div>
						<div id="tabs-14">
							<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_generiche_r'] ?></p>
							<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdA ?>_5]" 
								onkeydown="if(event.key === 'Enter') { 
									event.preventDefault(); 
									this.value += '\n'; 
									this.focus(); 
								}"><?= $Nota5A ?></textarea>
						</div>
					</div>
				</div>
			</td>
			<!-- Note di ritorno -->

			<? if ($Tp == 2) { ?>
				<td>
					<div id="BoxNoteRitorno">
						<h3><?= $dizionario['biglietto']['note_ritorno'] ?></h3>

						<div id="tabs1">
							<ul>
								<li><a href="#tabs-20"><?= $dizionario['biglietto']['note_salita'] ?></a></li>
								<li><a href="#tabs-21"><?= $dizionario['biglietto']['note_discesa'] ?></a></li>
								<li><a href="#tabs-22"><?= $dizionario['biglietto']['note_biglietto'] ?></a></li>
								<li><a href="#tabs-23"><?= $dizionario['biglietto']['note_posti'] ?></a></li>
								<li><a href="#tabs-24"><?= $dizionario['biglietto']['note_generiche'] ?></a></li>
							</ul>
							<div id="tabs-20">
								<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_fermata_salita'] ?></p>
								<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdR ?>_1]"
									onkeydown="if(event.key === 'Enter') { 
										event.preventDefault(); 
										this.value += '\n'; 
										this.focus(); 
									}"><?= $Nota1R ?></textarea>
							</div>
							<div id="tabs-21">
								<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_fermata_discesa'] ?></p>
								<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdR ?>_2]"
									onkeydown="if(event.key === 'Enter') { 
										event.preventDefault(); 
										this.value += '\n'; 
										this.focus(); 
									}"><?= $Nota2R ?></textarea>
							</div>
							<div id="tabs-22">
								<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_biglietto_r'] ?></p>
								<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdR ?>_3]"
									onkeydown="if(event.key === 'Enter') { 
										event.preventDefault(); 
										this.value += '\n'; 
										this.focus(); 
									}"><?= $Nota3R ?></textarea>
							</div>
							<div id="tabs-23">
								<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_posto_r'] ?></p>
								<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdR ?>_4]"
									onkeydown="if(event.key === 'Enter') { 
										event.preventDefault(); 
										this.value += '\n'; 
										this.focus(); 
									}"><?= $Nota4R ?></textarea>
							</div>
							<div id="tabs-24">
								<p class="txt_on_txtarea"><?= $dizionario['biglietto']['note_generiche_r'] ?></p>
								<textarea rows="3" cols="140" id="" name="PrenotazioneNote[<?= $CorsaIdR ?>_5]"
									onkeydown="if(event.key === 'Enter') { 
										event.preventDefault(); 
										this.value += '\n'; 
										this.focus(); 
									}"><?= $Nota5R ?></textarea>
							</div>
						</div>
					</div>
				</td>
			<?php
			}
			?>
		</tr>
	</table>

	<?php
	if (Config::$sceltaPostiBus) {
		//recupero info posti bus 
		//viaggio andata
		$sql = "SELECT b.Righe, b.Colonne, b.TipologiaBusId FROM RT_TipologiaBus b
			LEFT JOIN RT_Corsa c ON c.TipologiaBusDefaultId = b.TipologiaBusId
			WHERE c.CorsaId = " . $CorsaIdA;
		$dim = $db->query_first($sql);
		$sql = "SELECT COUNT(*) as tot FROM RT_PrenotazioneTitoloPosto
			WHERE CorsaId = $CorsaIdA AND CorsaDataPartenza = '$DataA'
			AND ComuneId = $PartenzaId AND PrenotazioneId = $PrenotazioneId";
		$countRow = $db->query_first($sql);
		if (isset($countRow['tot'])) {
			$count = $countRow['tot'];
		} else {
			$count = 0;
		}
		$busCompleto = true;
		if ($Tp == 2) {
			//viaggio ritorno
			$sql = "SELECT b.Righe, b.Colonne, b.TipologiaBusId FROM RT_TipologiaBus b
			LEFT JOIN RT_Corsa c ON c.TipologiaBusDefaultId = b.TipologiaBusId
			WHERE c.CorsaId = " . $CorsaIdR;
			$dimR = $db->query_first($sql);
			$sql = "SELECT COUNT(*) as tot FROM RT_PrenotazioneTitoloPosto WHERE CorsaId = $CorsaIdR AND CorsaDataPartenza = '$DataR' AND ComuneId = $DestinazioneId AND PrenotazioneId = $PrenotazioneId";
			$countRow = $db->query_first($sql);
			if (isset($countRow['tot'])) {
				$countR = $countRow['tot'];
			} else {
				$countR = 0;
			}
			$busCompletoR = true;
		} ?>
		<br>
		<h2 class="sezione_prenotazioni"><?= $dizionario['biglietto']['posti_bus'] ?></h2>
		<p><?= $dizionario['biglietto']['posti_info'] ?></p>
		<div id="BoxNoteAndata">

			<div id="tabs2">
				<ul>
					<li><a href="#tabs-p1"><?= $dizionario['biglietto']['posti_salita'] ?></a></li>
					<input type='hidden' id='postoA_tot' value='<?php echo $count; ?>'>
					<?php if ($Tp == 2) { ?>
						<li><a href="#tabs-p2"><?= $dizionario['biglietto']['posti_discesa'] ?></a></li>
						<input type='hidden' id='postoR_tot' value='<?php echo $countR; ?>'>
					<?php } ?>
				</ul>
				<br>
				<img src='/images/bus/taken_seat2.png' style='width:30px; margin:5px;'> <?= $dizionario['biglietto']['posto_occupato2'] ?>
				<img src='/images/bus/taken_seat.png' style='width:30px; margin:5px;'> <?= $dizionario['biglietto']['posto_occupato'] ?>
				<img src='/images/bus/empty_seat.png' style='width:30px; margin:5px;'> <?= $dizionario['biglietto']['posto_libero'] ?>
				<br>
				<div id="tabs-p1">
					<table style="border-color: black; border: solid 1px;">
						<?php
						$rig = 1;
						$col = 1;
						while ($rig <= $dim['Righe']) {
							echo "<tr style=\"border-color: black; border: solid 1px;\">";
							$col = 1;
							while ($col <= $dim['Colonne']) {
								echo "<td style=\"border-color: black; border: solid 1px;\">";
								$sql = "SELECT * FROM RT_TipologiaBusDettaglioPosto 
									WHERE TipologiaBusId = " . $dim['TipologiaBusId'] . "
									AND Riga = $rig AND Colonna = $col";
								$rowTemp = $db->query_first($sql);
								if (isset($rowTemp['TipologiaBusId'])) {
									//posto prenotabile
									$sql = "SELECT * FROM RT_PrenotazioneTitoloPosto 
										WHERE CorsaId = $CorsaIdA AND CorsaDataPartenza = '$DataA'
										AND ComuneId = $PartenzaId AND NumeroPosto = " . $rowTemp['NumeroPosto'];
									$rowPostoTemp = $db->query_first($sql);
									if (isset($rowPostoTemp['PrenotazioneTitoloPostoId'])) {
										if ($PrenotazioneId == $rowPostoTemp['PrenotazioneId']) {
											//posto occupato da prenotazione
											echo "<input type='hidden' name='postiA[" . $rig . "_" . $col . "]' id='postoA_" . $rig . "_" . $col . "' value='1'>";
											echo "<img src='/images/bus/taken_seat2.png' class='posto_occupato' id='postoA_" . $rig . "_" . $col . "_img' style='width:30px; margin:5px;' onclick='occupaPosto(\"postoA_" . $rig . "_" . $col . "\", \"A\")'>";
										} else {
											//posto occupato
											echo "<img src='/images/bus/taken_seat.png' style='width:30px; margin:5px;'>";
										}
									} else {
										//posto libero
										echo "<input type='hidden' name='postiA[" . $rig . "_" . $col . "]' id='postoA_" . $rig . "_" . $col . "' value='0'>";
										echo "<img src='/images/bus/empty_seat.png' class='posto_libero' id='postoA_" . $rig . "_" . $col . "_img' style='width:30px; margin:5px;' onclick='occupaPosto(\"postoA_" . $rig . "_" . $col . "\", \"A\")'>";
										$busCompleto = false;
									}
								} else {
									//posto non prenotabile
									echo "";
								}
								echo "</td>";
								$col++;
							}
							echo "</tr>";
							$rig++;
						} ?>
					</table>
					<?php if ($busCompleto) {
						echo $dizionario['biglietti']['posti_completo'];
					} ?>
				</div>
				<?php if ($Tp == 2) { ?>
					<div id="tabs-p2">
						<table style="border-color: black; border: solid 1px;">
							<?php
							$rig = 1;
							$col = 1;
							while ($rig <= $dimR['Righe']) {
								echo "<tr style=\"border-color: black; border: solid 1px;\">";
								$col = 1;
								while ($col <= $dimR['Colonne']) {
									echo "<td style=\"border-color: black; border: solid 1px;\">";
									$sql = "SELECT * FROM RT_TipologiaBusDettaglioPosto 
									WHERE TipologiaBusId = " . $dimR['TipologiaBusId'] . "
									AND Riga = $rig AND Colonna = $col";
									$rowTemp = $db->query_first($sql);
									if (isset($rowTemp['TipologiaBusId'])) {
										//posto prenotabile
										$sql = "SELECT * FROM RT_PrenotazioneTitoloPosto 
										WHERE CorsaId = $CorsaIdR AND CorsaDataPartenza = '$DataR'
										AND ComuneId = $DestinazioneId AND NumeroPosto = " . $rowTemp['NumeroPosto'];
										$rowPostoTemp = $db->query_first($sql);
										if (isset($rowPostoTemp['PrenotazioneTitoloPostoId'])) {
											if ($PrenotazioneId == $rowPostoTemp['PrenotazioneId']) {
												//posto occupato da prenotazione
												echo "<input type='hidden' name='postiR[" . $rig . "_" . $col . "]' id='postoR_" . $rig . "_" . $col . "' value='1'>";
												echo "<img src='/images/bus/taken_seat2.png' class='posto_occupato' id='postoR_" . $rig . "_" . $col . "_img' style='width:30px; margin:5px;' onclick='occupaPosto(\"postoR_" . $rig . "_" . $col . "\", \"R\")'>";
											} else {
												//posto occupato
												echo "<img src='/images/bus/taken_seat.png' style='width:30px; margin:5px;'>";
												$busCompletoR = false;
											}
										} else {
											//posto libero
											echo "<input type='hidden' name='postiR[" . $rig . "_" . $col . "]' id='postoR_" . $rig . "_" . $col . "' value='0'>";
											echo "<img src='/images/bus/empty_seat.png' class='posto_libero' id='postoR_" . $rig . "_" . $col . "_img' style='width:30px; margin:5px;' onclick='occupaPosto(\"postoR_" . $rig . "_" . $col . "\", \"R\")'>";
										}
									} else {
										//posto non prenotabile
										echo "";
									}
									echo "</td>";
									$col++;
								}
								echo "</tr>";
								$rig++;
							} ?>
						</table>
						<?php if ($busCompletoR) {
							echo $dizionario['biglietti']['posti_completo'];
						} ?>
					</div>
				<?php } ?>


			</div>
		</div>
	<?php
	}
}

function GetPasseggeri()
{
	global $user, $HtmlCommon, $prenotazione_wizard, $dizionario;
	$page = new Form();
	$db = new Database();
	$db->connect();

	$passeggeri = null;
	$mod = 0;
	$principalePresente = false;
	if (isset($_POST['Passeggeri']) || isset($_POST['PasseggeriInseriti'])) {

		foreach ($_POST['PasseggeriInseriti'] as $passeggero) {
			$passeggeri[] = array(
				'TipoBigliettoId' => $passeggero['TipoBigliettoId'],
				'TipologiaBiglietto' => $passeggero['TipologiaBiglietto'],
				'Cognome' => $passeggero['Cognome'],
				'Nome' => $passeggero['Nome'],
				'SessoId' => $passeggero['SessoId'],
				'Eta' => $passeggero['Eta'],
				'Principale' => $passeggero['Principale']
			);
		}

		$passeggeriGet = $_POST['Passeggeri'];

		foreach ($passeggeriGet as $passeggeroGet) {

			$id_descr = explode("_", $passeggeroGet['TipoId']);

			$count = 0;
			if (!empty($passeggeri)) {
				foreach ($passeggeri as $passeggero) {
					if ($passeggero['TipoBigliettoId'] == $id_descr[0]) $count++;
				}
			}

			for ($i = $count; $i < $passeggeroGet['Qnt']; $i++) {
				$passeggeri[] = array(
					'TipoBigliettoId' => $id_descr[0],
					'TipologiaBiglietto' => $id_descr[1],
					'Cognome' => '',
					'Nome' => '',
					'SessoId' => '',
					'Eta' => '',
					'Principale' => '0'
				);
			}
		}
	} else {
		if (is_object($prenotazione_wizard)) {

			$mod = 1;

			$prenotazione_wizard->conn = $db;
			$prenotazione_wizard->inizializzaDatiGenerali();
			$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

			$PrenotazioneId = $prenotazione_wizard->Id;
			$Stato = $DatiGeneraliArr['PrenotazioneStato'];

			$passeggeri = $prenotazione_wizard->GetPasseggeri();
		} else if (isset($_SESSION['PRENOTAZIONE_MULTI'])) {
			$prenotazione_multi = $_SESSION['PRENOTAZIONE_MULTI'];

			$prenotazione_wizard = new Prenotazione();
			$prenotazione_wizard->Id = $prenotazione_multi[0]['PrenotazioneId'];
			$prenotazione_wizard->conn = $db;
			$prenotazione_wizard->inizializzaDatiGenerali();
			$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

			$PrenotazioneId = $prenotazione_wizard->Id;
			$Stato = $DatiGeneraliArr['PrenotazioneStato'];

			$passeggeri = $prenotazione_wizard->GetPasseggeri();
		}
	}


	if (!isset($Stato) && isset($_POST['StatoPassaggio'])) {
		$Stato = $_POST['StatoPassaggio'];
	} else {
		$Stato = null;
	}

	if (isset($passeggeri)) {

		//controllo se e' inserito almeno un passeggero che occupa posto, quindi con nominativo
		foreach ($passeggeri as $passeggero) {
			$sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = " . $passeggero['TipoBigliettoId'];
			$tempTipo = $db->query_first($sql);
			if ($tempTipo['OccupaPosto'] == 1) {
				$principalePresente = true;
			}
		}

	?>
		<script type="text/javascript">
			totalePasseggeri = <?= count($passeggeri) ?>;
		</script>

		<table width="100%" cellspacing="0" cellpadding="0" border="0" id="pagamentiTabella">
			<tr class="rowIntestazione">
				<td style="width:80%;"><?= $dizionario['generale']['biglietto'] ?></td>

				<td><?= $dizionario['generale']['rimuovi'] ?></td>
				<td><?= $dizionario['generale']['principale'] ?></td>
			</tr>

			<?php
			$checkedOne = false;
			foreach ($passeggeri as $index => $passeggero) {
				if ($passeggero['Principale']) {
					$checkedOne = true;
				}
			}
			echo "<input type='hidden' name='StatoPassaggio' id='StatoPassaggio' value='$Stato'>";
			$servizi = array();

			foreach ($passeggeri as $index => $passeggero) {
				$sql = "select * from RT_TipologiaBiglietto where TipologiaBigliettoId = " . $passeggero['TipoBigliettoId'];
				$row = $db->query_first($sql);
				if ($row['OccupaPosto'] == 0) {
					if (!isset($passeggero['TipologiaBiglietto']) || $passeggero['TipologiaBiglietto'] == '') {
						$passeggero['TipologiaBiglietto'] = $row['TipologiaBiglietto'];
					}
					$passeggero['Cognome'] = $passeggero['TipologiaBiglietto'];
					$passeggero['Nome'] = $passeggero['TipologiaBiglietto'];
					$passeggero['SessoId'] = 3;
					$passeggero['Eta'] = 0;
					if (isset($servizi[$passeggero['TipoBigliettoId']])) {
						$servizi[$passeggero['TipoBigliettoId']]['tot'] += 1;
						$servizi[$passeggero['TipoBigliettoId']]['index'] = $index;
					} else {
						$servizi[$passeggero['TipoBigliettoId']]['tot'] = 1;
						$servizi[$passeggero['TipoBigliettoId']]['desc'] = $passeggero['TipologiaBiglietto'];
						$servizi[$passeggero['TipoBigliettoId']]['index'] = $index;
					}

			?>
					<tr id="riga<?= $index ?>" class="rowBianca passeggero">
						<input class="passeggeroBiglietto" type="hidden" name="Passeggeri[<?= $index ?>][TipologiaBiglietto]" id="PasseggeroBiglietto<?= $index ?>" value="<?= $passeggero['TipologiaBiglietto'] ?>">
						<input class="passeggeroId" type="hidden" value="<?= $passeggero['TipoBigliettoId'] ?>" id="<?= "Passeggeri[$index][TipoBigliettoId]" ?>" name="<?= "Passeggeri[$index][TipoBigliettoId]" ?>">
						<input class="passeggeroCognome" type="hidden" name="Passeggeri[<?= $index ?>][Cognome]" id="PasseggeroCognome<?= $index ?>" value="<?= $passeggero['Cognome'] ?>">
						<input class="passeggeroNome" type="hidden" name="Passeggeri[<?= $index ?>][Nome]" id="PasseggeroNome<?= $index ?>" value="<?= $passeggero['Nome'] ?>">
						<input class="passeggeroSesso" type="hidden" name="Passeggeri[<?= $index ?>][SessoId]" id="PasseggeroSesso<?= $index ?>" value="<?= $passeggero['SessoId'] ?>">
						<input class="passeggeroEta" type="hidden" name="Passeggeri[<?= $index ?>][Eta]" id="PasseggeroEta<?= $index ?>" value="<?= $passeggero['Eta'] ?>">
					</tr>
				<?php
				} else {

					$passeggero['Cognome'] = $passeggero['TipologiaBiglietto'];
					$passeggero['Nome'] = $passeggero['TipologiaBiglietto'];
					$passeggero['SessoId'] = 3;
					$passeggero['Eta'] = 0;

				?>
					<tr id="riga<?= $index ?>" class="rowBianca passeggero">
						<td>
							<span class="passeggeroBiglietto"><?= $passeggero['TipologiaBiglietto'] ?></span>
							<input class="passeggeroId" type="hidden" value="<?= $passeggero['TipoBigliettoId'] ?>" id="<?= "Passeggeri[$index][TipoBigliettoId]" ?>" name="<?= "Passeggeri[$index][TipoBigliettoId]" ?>">
							<input class="passeggeroCognome" type="hidden" name="Passeggeri[<?= $index ?>][Cognome]" id="PasseggeroCognome<?= $index ?>" value="<?= $passeggero['Cognome'] ?>">
							<input class="passeggeroNome" type="hidden" name="Passeggeri[<?= $index ?>][Nome]" id="PasseggeroNome<?= $index ?>" value="<?= $passeggero['Nome'] ?>">
							<input class="passeggeroSesso" type="hidden" name="Passeggeri[<?= $index ?>][SessoId]" id="PasseggeroSesso<?= $index ?>" value="<?= $passeggero['SessoId'] ?>">
							<input class="passeggeroEta" type="hidden" name="Passeggeri[<?= $index ?>][Eta]" id="PasseggeroEta<?= $index ?>" value="<?= $passeggero['Eta'] ?>">


						</td>

						<th scope="row">
							<?
							if (!isset($Stato) || $Stato != 3) {
							?>
								<a class="rimuoviBiglietti" href="javascript:rimuoviBiglietto('<?= $passeggero['TipoBigliettoId'] . '_' . $passeggero['TipologiaBiglietto'] ?>', 'riga<?= $index ?>')">rimuovi</a>
							<?
							}
							?>
						</th>
						<th scope="row" style="text-align: center;">
							<?php
							$principaleChecked = "";
							if ($passeggero['Principale'] || !$checkedOne) {
								$principaleChecked = "checked";
								$checkedOne = true;
							}
							?>
							<input class="passeggeroPrincipale" type="radio" name="PasseggeriPrincipale" style="float: none;" value="<?php echo $index; ?>" <?php echo $principaleChecked; ?> onChange="javascript:changePrincipale();" />
						</th>
					</tr>
			<?php
				}
			}
			?>
		</table>
		<br>
		<h3><?= $dizionario['tipo_big']['servizi_selezionati'] ?></h3>
		<?php
		foreach ($servizi as $chiave => $valore) {
			$id = $chiave;
			echo "<h4>x" . $valore['tot'] . " " . $valore['desc'];
			if (!isset($Stato) || $Stato != 3) {
				echo ' <a class="rimuovi_'.$id.'" href="javascript:rimuoviBiglietto(\'' . $id . '_' . $valore['desc'] . '\',\'riga' . $valore['index'] . '\')">' . $dizionario['generale']['rimuovi'] . '</a></h4><br>';
			} else {
				echo '</h4><br>';
			}
		}
	} else {
		?>
		<script type="text/javascript">
			var totalePasseggeri = 0;
		</script>
	<?php
	}
	?>
<?
}

function AbilitaTipoPrenotazione()
{
	global $user, $HtmlCommon;
	$page = new Form();
	$db = new Database();
	$db->connect();
	$CorsaId = $_REQUEST['CorsaId'];
	$FermataIdAP = $_REQUEST['FermataIdAP'];
	$Libera = false;
	if (isset($_REQUEST['Libera'])) {
		$Libera = $_REQUEST['Libera'];
	}


	$prenotazione = new Prenotazione();
	$prenotazione->conn = $db;
	$abilitazioneprenotazione = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaId, $FermataIdAP, $Libera);

	echo ($abilitazioneprenotazione);

	exit();
}

function GetTipologiaBiglietti()
{

	global $user, $HtmlCommon, $prenotazione_wizard, $dizionario;
	$page = new Form();
	$db = new Database();
	$db->connect();

	$readonly = '';

	$CorsaId = $_REQUEST['CorsaId'];
	$FermataIdAP = $_REQUEST['FermataIdAP'];
	$FermataIdAD = $_REQUEST['FermataIdAD'];

	$CorsaRitornoId = $_REQUEST['CorsaRitornoId'];
	$FermataIdRP = $_REQUEST['FermataIdRP'];
	$FermataIdRD = $_REQUEST['FermataIdRD'];
	$PartenzaId = $_REQUEST['PartenzaId'];
	$DestinazioneId = $_REQUEST['DestinazioneId'];

	$Data = $_REQUEST['Data'];
	$TV = $_REQUEST['TV'];
	$TipoTour = $_REQUEST['TipoTour'];

	$libera = false;
	if (isset($_REQUEST['Libera']) && $_REQUEST['Libera'] == 1) {
		$libera = true;
	}

	$ar = 1;
	$ar1 = 1;
	if ($TV == 1) {
		$ar1 = 0;
		$ar = 0;
	}

	$totpax = 0;
	$totalissimo = 0;

	$prenotazione = new Prenotazione(null);
	$prenotazione->conn = $db;
	$PrenotazioneId = 0;
	$penale = 0;
	if (is_object(($prenotazione_wizard))) {
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		$penale = $DatiGeneraliArr['Penale'];
		$PrenotazioneId = $prenotazione_wizard->Id;
		$Stato = $DatiGeneraliArr['PrenotazioneStato'];

		$mod = 1;
	} else if (isset($_SESSION['PRENOTAZIONE_MULTI'])) {
		$prenotazione_multi = $_SESSION['PRENOTAZIONE_MULTI'];

		$prenotazione_wizard = null;
		$PrenotazioneId = $prenotazione_multi[0]['PrenotazioneId'];
		$Stato = $DatiGeneraliArr['PrenotazioneStato'];
	}

	$readOnlyReduction = '';
	if ($mod == 1) {
		if ($Stato == 3 && !($user->IsAdmin || $user->GestoreId == 1 || $user->GestoreId == 2)) {
			$readOnlyReduction = "readonly = 'readonly'";
			//$readOnlyReduction = $readonly;
		} else {
			$readOnlyReduction = $readonly;
		}
	}
	$DataRitorno = $_REQUEST['DataR'];
	if ($TV == 1) {
		$CorsaRitornoId = 0;
	}

	$arrPrezziBiglietti = $prenotazione->GetTipologiaBigliettiPrezzi(0, $PrenotazioneId, $prenotazione_wizard, $Data, $CorsaId, $FermataIdAP, $FermataIdAD, $CorsaRitornoId, $FermataIdRP, $FermataIdRD, $TV, null, null, null, $PrenotazioneId, null, null, $DataRitorno, null, null, $TipoTour, $libera, $PartenzaId, $DestinazioneId);

	/*
	 * $PrenotazioneId=0; $mod=0; $DataPartenzaOriginale=null; $DataRitornoOriginale=null; if (is_object(($prenotazione_wizard))) { $prenotazione_wizard->conn=$db; $PrenotazioneId=$prenotazione_wizard->Id; $mod=1; $prenotazione_wizard->inizializzaDatiGenerali(); $DatiGeneraliArr=$prenotazione_wizard->DatiGenerali; $TipoViaggioOriginale=$DatiGeneraliArr['TipoViaggioId']; $prenotazione_wizard->inizializzaDatiGeneraliPercorso('A'); $DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso; $DataPartenzaOriginale=$DatiGeneraliPercorsoArr['CorsaDataPartenza']; if ($TipoViaggioOriginale==2) { $prenotazione_wizard->inizializzaDatiGeneraliPercorso('R'); $DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso; $DataRitornoOriginale=$DatiGeneraliPercorsoArr['CorsaDataPartenza']; } } $prenotazione=new Prenotazione(); $prenotazione->conn=$db; $arr_tratte=$prenotazione->GetTratte($CorsaId, $FermataIdAP, $FermataIdAD); if ($CorsaRitornoId>0) $arr_tratter=$prenotazione->GetTratte($CorsaRitornoId, $FermataIdRP, $FermataIdRD); $arr_listini=$prenotazione->GetListini($arr_tratte,$FermataIdAP,$FermataIdAD,$CorsaId); if ($CorsaRitornoId>0) $arr_listinir=$prenotazione->GetListini($arr_tratter,$FermataIdRP,$FermataIdRD,$CorsaRitornoId); $readonly=""; if ($user->SedeLegale!=1) $readonly="readonly"; $at=0; while ($at<sizeof($arr_tratte)) { if ($at>0) $tratteid.=",".$arr_tratte[$at]['TrattaId']; else $tratteid=$arr_tratte[$at]['TrattaId']; $at++; } $ar=0; $sql="select distinct TipologiaBigliettoId,TipologiaBiglietto from RT_ViewPrenotazioneTipoBiglietti where TrattaId In($tratteid) and AR=$ar"; // echo($sql); //if ($user->SedeLegale!=1) //$sql="select distinct TipologiaBigliettoId,TipologiaBiglietto from RT_ViewPrenotazioneTipoBiglietti where TrattaId In($tratteid) and AR=$ar and (TipologiaBigliettoId<>11 and TipologiaBigliettoId<>12 and TipologiaBigliettoId<>13 and TipologiaBigliettoId<>14)"; //echo($sql); $ArrObject = $db->fetch_array($sql); $i=0; $numerobiglietti=-1;
	 */
	$isAdmin = $user->IsAdmin;

	unset($arrPrezziBiglietti[9999]);

	$sql = "SELECT GestorePrimario From Gestore where GestoreId = $user->GestoreId";
	$row = $db->fetch_array($sql);

	$isPrimario = $isAdmin;

	$canViewAll = false;
	if (($user->GestoreId == 1) || ($user->GestoreId == 2))
		$canViewAll = true;


?>


	<div class="brain_rowAll">
		
		<?php if($TipoTour == 1) {
			$page->create_textbox($dizionario['biglietto']['numero_biglietti_privato'],"TipoTourPasseggeri","Prenotazione[TipoTourPasseggeri]",(isset($DatiGeneraliArr['TipoTourPasseggeri']))? $DatiGeneraliArr['TipoTourPasseggeri'] : 0,1,"brain_campoForm",array("onChange"=>"'javascript:selezionaPasseggeri();'"),"","10");
				print("<br style=\"clear:both;\"/>");
		} ?>
	
		<table width="100%" cellspacing="0" cellpadding="0" border="0" id="pagamentiTabella">
			<tbody>
				<tr class="rowIntestazione">
					<th scope="row">n. pax</th>
					<td><?= $dizionario['generale']['biglietto'] ?></td>
					<?php if ($canViewAll) { ?>
						<!--<td><?= $dizionario['biglietto']['andata'] ?></td>-->
						<!--<td><?= $dizionario['biglietto']['ritorno'] ?></td>-->
						<?php if (!$libera) { ?>
							<td><?= $dizionario['biglietto']['importo_listino'] ?></td>
						<?php } else { ?>
							<td><?= $dizionario['generale']['importo'] ?> (&euro;)</td>
						<?php } ?>
						<!--<td><?= $dizionario['biglietto']['sconto_ar'] ?></td>-->
						<?php if (!$libera) { ?>
							<td><?= $dizionario['biglietto']['var'] ?></td>
						<?php } ?>
					<?php } ?>
					<?php if (!$libera) { ?>
						<td><?= $dizionario['biglietto']['importo_per_pax'] ?></td>
						<td><?= $dizionario['biglietto']['importo_proposto'] ?></td>
					<?php } ?>
					<?php if ($canViewAll) { ?>
						<td><?= $dizionario['biglietto']['riduzione'] ?></td>
					<?php } else { ?>
						<td style="display:none"><?= $dizionario['biglietto']['riduzione'] ?></td>
					<?php } ?>
					<?php if ($canViewAll) { ?>
						<td><?= $dizionario['biglietto']['aumento'] ?></td>
					<?php } else { ?>
						<td style="display:none"><?= $dizionario['biglietto']['aumento'] ?></td>
					<?php } ?>
					<td style="width:10%"><?= $dizionario['biglietto']['tot'] ?></td>
				</tr>

				<?php
				$x = 0;
				$tipoPrecedente = 0;
				$totalePax = 0;
				
				while ($x < sizeof($arrPrezziBiglietti)) {

					$BigliettoId = $arrPrezziBiglietti[$x]['BigliettoId'];
					$DescrizioneBiglietto = $arrPrezziBiglietti[$x]['DescrizioneBiglietto'];
					$pax = $arrPrezziBiglietti[$x]['Pax'];
					$PrezzoPax = $arrPrezziBiglietti[$x]['PrezzoPax'];
					$base = $arrPrezziBiglietti[$x]['PrezzoBasePax'];
					$PrezzoAndata = $arrPrezziBiglietti[$x]['PrezzoAndata'];
					$PrezzoRitorno = $arrPrezziBiglietti[$x]['PrezzoRitorno'];
					$riduzione = $arrPrezziBiglietti[$x]['Riduzione'];
					$aumento = $arrPrezziBiglietti[$x]['Aumento'];
					$riduzioneNote = $arrPrezziBiglietti[$x]['RiduzioneNote'];
					$aumentoNote = $arrPrezziBiglietti[$x]['AumentoNote'];
					$PrezzoAndata = $arrPrezziBiglietti[$x]['PrezzoAndata'];
					$ScontoAR = $arrPrezziBiglietti[$x]['ScontoAR'];
					$finale = $arrPrezziBiglietti[$x]['Totale'];
					$PercSconto1 = $arrPrezziBiglietti[$x]['PromozioneSconto'];

					$sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = $BigliettoId";
					$tempB = $db->query_first($sql);

					if ($PrezzoPax >= 0) {
						$PrezzoPax_f = number_format($PrezzoPax, 2, ",", ".");
						$PercSconto_f = number_format($PercSconto1, 2, ",", ".");
						$finale_f = number_format($finale, 2, ",", ".");
						$base_f = number_format($base, 2, ",", ".");
						$riduzione_f = number_format($riduzione, 2, ",", ".");
						$aumento_f = number_format($aumento, 2, ",", ".");
						$TotaleAR = $PrezzoAndata + $PrezzoRitorno;
						$PrezzoAndata = number_format($PrezzoAndata, 2, ",", ".");
						$PrezzoRitorno = number_format($PrezzoRitorno, 2, ",", ".");
						$TotaleAR = number_format($TotaleAR, 2, ",", ".");

						$sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = $BigliettoId";
						$row = $db->fetch_array($sql);;
						if ($tipoPrecedente != $row[0]['OccupaPosto']) {
							if ($libera) {
								$colspan = 6;
							} else {
								$colspan = 12;
							}
							if ($row[0]['OccupaPosto'] == 1) {
								echo "<tr><td colspan='$colspan'>" . $dizionario['tipo_big']['passeggeri'] . "</td></tr>";
							} else {
								echo "<tr><td colspan='$colspan'>" . $dizionario['tipo_big']['servizi'] . "</td></tr>";
							}
						}
						$tipoPrecedente = $row[0]['OccupaPosto'];
						$totalePax += $pax;
				?>

						<tr class="rowBianca">

							<th scope="row">
								<input id="Pax<?= $x ?>" onchange="javascript:<?= ($row[0]['OccupaPosto'] == 0) ? "ModificaBiglietti(this, $BigliettoId)" : "CalcolaPrezzoTipoBiglietto(this)"; ?>;" onfocus="javascript:SalvaNumeroBiglietti(this);" type="text" maxlength="3" size="3" value="<?= $pax ?>" name="BigliettoTipologiaPax[<?= $BigliettoId ?>_<?= $DescrizioneBiglietto ?>]" class="required digits <?= ($row[0]['OccupaPosto'] == 0) ? "servizioInput" : ""; ?>" <?= (isset($Stato) && $Stato == 3 && $row[0]['OccupaPosto'] == 1) ? "readonly" : ""; ?>>
								<?


								$page->create_textbox_hidden("TipoBigliettoId" . $x, $BigliettoId . "_" . $DescrizioneBiglietto);
								$page->create_textbox_hidden("Prezzo" . $x, $PrezzoPax_f);
								$page->create_textbox_hidden("Totale" . $x, 0);
								?>
							</th>
							<td id="<?= "Biglietto" . $x ?>"><?= $DescrizioneBiglietto ?>
								<?php if ($row[0]['OccupaPosto'] && $BigliettoId != 11) { ?>
									<br><small style="font-size:12px">(<?= $tempB['EtaDa'] ?> - <?= $tempB['EtaA'] ?> anni)</small>
								<?php } ?>
							</td>

							<?php if ($canViewAll) { ?>
								<!--  <td><?= $PrezzoAndata ?>&euro;</td> -->
								<!--  <td><?= $PrezzoRitorno ?>&euro;</td> -->
								<?php if (!$libera || ($libera && !$row[0]['OccupaPosto'])) { ?>
									<td><?= $TotaleAR ?>&euro;</td>
								<?php } ?>
								<!--  <td><?= $ScontoAR ?>%</td> -->
								<?php if (!$libera) { ?>
									<td><?= $PercSconto_f ?>%</td>
								<?php } ?>
							<?php  } ?>
							<?php if (!$libera) { ?>
								<td><?= $PrezzoPax_f ?> &euro;</td>
								<td id="PrezzoParziale<?= $x ?>"><?= $base_f ?> &euro; </td>
							<?php } ?>
							<?php if ($canViewAll && (!$libera || ($libera && !$row[0]['OccupaPosto']))) { ?>
								<td>
							<?php } else { ?>
								<td style="display:none">
							<?php } ?>
								<input <?= $readOnlyReduction ?> id="PrezzoRiduzione<?= $x ?>" onchange="javascript:ControlloResidio(<?= $x ?>); CalcolaPrezzoTipoBiglietto(); ShowRiduzioneNote(<?= $x ?>, <?= $BigliettoId ?>);" type="number" step="0.01" min="0" maxlength="8" size="8" value="<?= $riduzione ?>" name="BigliettoTipologiaPaxRid[<?= $BigliettoId ?>][Valore]" class="numberDE">

								<input type="hidden" id="oldRiduzione<?= $x ?>" value="<?= $riduzione ?>">
							</td>

								<?php if ($canViewAll) { ?>
									<?php if($libera && $row[0]['OccupaPosto']) { ?>
										<td colspan="3">
									<?php } else { ?>
										<td>
									<?php } ?>
								<?php } else { ?>
									<td style="display:none">
								<?php } ?>


									<input <?= $readonly ?> id="PrezzoAumento<?= $x ?>" onchange="javascript:ControlloResidioAumento(<?= $x ?>); CalcolaPrezzoTipoBiglietto(); ShowAumentoNote(<?= $x ?>, <?= $BigliettoId ?>);" type="number" step="0.01" min="0" maxlength="8" size="8" value="<?= $aumento ?>" name="BigliettoTipologiaPaxAum[<?= $BigliettoId ?>][Valore]" class="numberDE">
									<input type="hidden" id="oldAumento<?= $x ?>" value="<?= $aumento ?>">

									</td>

									<td id="PrezzoFinale<?= $x ?>" class="prezzo_grande"><?= $finale_f ?> &euro; </td>



						</tr>

						<?
						if ($aumentoNote != '') {
						?>
							<tr id=NoteAumento<?= $x ?>>
								<td colspan="12">
									<div class="brain_campiform">
										<label for="BigliettoTipologiaPaxAum[<?= $BigliettoId ?>][Note]"><? $dizionario['biglietto']['note_aumento'] ?></label>
										<input <?= $readonly ?> id="AumentoNote<?= $x ?>" type="text" value="<?= $aumentoNote ?>" name="BigliettoTipologiaPaxAum[<?= $BigliettoId ?>][Note]" class="">
									</div>
								</td>
							</tr>
						<?
						} else {
						?>
							<tr id=NoteAumento<?= $x ?>></tr>
						<?
						}
						?>

						<?
						if ($riduzioneNote != '') {
						?>
							<tr id=NoteRiduzione<?= $x ?>>
								<td colspan="12">
									<div class="brain_campiform">
										<label for="BigliettoTipologiaPaxRid[<?= $BigliettoId ?>][Note]"><? $dizionario['biglietto']['note_riduzione'] ?></label>
										<input <?= $readonly ?> id="RiduzioneNote<?= $x ?>" type="text" value="<?= $riduzioneNote ?>" name="BigliettoTipologiaPaxRid[<?= $BigliettoId ?>][Note]" class="">
									</div>
								</td>
							</tr>
						<?
						} else {
						?>
							<tr id=NoteRiduzione<?= $x ?>></tr>
						<?
						}
						?>

				<?

						// $page->create_textbox("Prezzo",$BigliettoPrezzo,"BigliettoTipologiaPax[$stringa]",$PrezzoPax,1,"brain_campiform",array(
						// "class"=>"'required'"));

						$totpax += $pax;
						$totalissimo += $finale;
					}
					$x++;
				}


				$sql = "select TotalePrenotazione, TotaleResiduo from RT_Prenotazione where PrenotazioneId=$PrenotazioneId";
				$r = $db->query_first($sql);
				$oldTotalePrenotazione = 0;
				$oldResiduo = 0;
				if (!empty($r['TotalePrenotazione'])) {
					$oldTotalePrenotazione = $r['TotalePrenotazione'];
					$oldResiduo = (int)$r['TotaleResiduo'];
				}



				/*
	 * $i++; }
	 */

				$totalissimo_f = number_format($totalissimo, 2, ",", ".");
				$page->create_textbox_hidden("NumeroBiglietti", $x);
				$page->create_textbox_hidden("TotalePax", $totalePax);
				$page->create_textbox_hidden("TotalePaxScelti", 0);
				$newResiduo = 0;
				if ($oldTotalePrenotazione > 0)
					$newResiduo = $totalissimo - $oldTotalePrenotazione + $penale;
				//          echo "$newResiduo = $totalissimo - $oldTotalePrenotazione + $penale ";

				?>
				<input id='newResiduo' name='newResiduo' type='hidden' value='<?php echo $newResiduo; ?>' />
				<input id='residuoTotale' name='residuoTotale' type='hidden' value='0' />
				<input id='oldResiduo' name='oldResiduo' type='hidden' value='<?php echo $oldResiduo; ?>' />

				<tr class="rowIntestazione">
					<th scope="row">&nbsp;</th>
					<td></td>
					<?php if ($canViewAll) { ?>
						<!-- <td><strong></strong></td>-->
						<!-- <td><strong></strong></td>-->
						<!-- <td><strong></strong></td>-->
						<td><strong></strong></td>
						<?php if(!$libera) { ?>
							<td><strong></strong></td>
						<?php } ?>
					<?php } ?>


					<?php if ($canViewAll) { ?>
						<td><strong></strong></td>
					<?php } else { ?>
						<td style="display:none"><strong></strong></td>
					<?php } ?>
					<?php if ($canViewAll) { ?>
						<td><strong></strong></td>
					<?php } else { ?>
						<td style="display:none"><strong></strong></td>
					<?php } ?>
					<?php if (!$libera) { ?>
						<td><strong></strong></td>
						<td><strong></strong></td>
					<?php } ?>
					<td id="PrezzoTotalePax" class="prezzo_grande"><strong><?= $totalissimo_f ?> &euro; </strong></td>

				</tr>
				<? if ($newResiduo > 0) {
					$variazionePrezzo =  number_format($newResiduo, 2, ",", ".") . " EURO";

				?>
					<tr>
						<td colspan="12">
							<div style="text-align:center;font-size:22px;color:#F00;"><strong><?= $dizionario['biglietto']['attenzione_variazione'] ?> + <b id="variazioneIndicazione"><?php echo ($variazionePrezzo); ?></b><strong></div>
						</td>
					</tr>
					<!-- <tr><td colspan="12"><div style="text-align:center;font-size:12px;color:#F00;"><strong>Dopo aver effettuato la modifica della prenotazione occorrer&agrave; creare un movimento contabile pari a <?php echo ($variazionePrezzo) ?> <strong></div></td></tr>-->

				<?
				} ?>
				<?php if ($penale > 0) {
					$penaleForm =  number_format($penale, 2, ",", ".") . " EURO"; ?>
					<tr>
						<td colspan="12">
							<div style="text-align:center;font-size:16px;color:#F00;"><strong><?= $dizionario['biglietto']['attenzione_penale'] ?> + <?php echo ($penaleForm); ?><strong></div>
						</td>
					</tr>
				<?php } ?>

			</tbody>
		</table>
	</div>
	<?php

}

function GetFermate()
{

	global $user, $prenotazione_wizard, $dizionario;
	$page = new Form();
	$db = new Database();
	$db->connect();

	/*recupero dati da request e db*/
	$CorsaId = $_REQUEST['CorsaId'];
	$cor = new Corsa($CorsaId);
	$cor->conn = $db;
	$cor->inizializzaDatiGenerali();
	$lineaId = $cor->DatiGenerali['LineaId'];
	$ComuneAndataId = $_REQUEST['ComuneAndata'];
	$ComuneRitornoId = $_REQUEST['ComuneRitorno'];
	$DataSelezionataA = $_REQUEST['DataSelezionataA'];
	if (isset($_REQUEST['CorsaRitornoAperto'])) {
		$CorsaRitornoAperto = $_REQUEST['CorsaRitornoAperto'];
	} else {
		$CorsaRitornoAperto = null;
	}
	if (isset($_REQUEST['CorsaAndata'])) {
		$CorsaAndata = $_REQUEST['CorsaAndata'];
	} else {
		$CorsaAndata = null;
	}
	if (isset($_REQUEST['DataAndata'])) {
		$DataAndata = $_REQUEST['DataAndata'];
	} else {
		$DataAndata = null;
	}
	if (isset($_REQUEST['Tp'])) {
		$Tp = $_REQUEST['Tp'];
	} else {
		$Tp = null;
	}
	if (isset($_REQUEST['Tf'])) {
		$TipoFermata = $_REQUEST['Tf'];
	} else {
		$TipoFermata = null;
	}

	$arr_fermate = array();
	$arr_fermate_d = array();
	$trattaPartenza = null;
	$trattaArrivo = null;

	//verificare se sono in modifica
	if (is_object(($prenotazione_wizard))) {
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso($Tp);
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
	}

	if ($Tp == 'A') {
		$TV = 1;
	} else {
		$TV = 2;
	}
	/*recupero le fermate*/
	$change = true;
	if (is_object($prenotazione_wizard)) {
		if ($TV == 1) {
			$change = $prenotazione_wizard->checkIfChanged($ComuneAndataId, $ComuneRitornoId, $TV, $DataSelezionataA, $CorsaId);
		} else {
			$change = $prenotazione_wizard->checkIfChanged($ComuneRitornoId, $ComuneAndataId, $TV, $DataAndata, $CorsaAndata, $DataSelezionataA, $CorsaId);
		}
	}

	if (!$change) {
		/*in caso di modifica*/
		$FermataSalitaId = $DatiGeneraliPercorsoArr['FermataSalitaId'];
		$FermataDiscesaId = $DatiGeneraliPercorsoArr['FermataDiscesaId'];
		if (isset($CorsaRitornoAperto) && $CorsaRitornoAperto == "true") {

			//caso di ritorno open
			$sql = "select distinct f.FermataId,
			concat(f.FermataNome, ' ( Open ) ', t.TrattaNome) AS FermataOrario,
			t.TrattaNome, f.FermataNome, concat('Open') as Orario
			from RT_Fermata f
			left Join RT_Tratta t on (t.TrattaId = f.TrattaId)
			left join RT_Orario o on (f.FermataId = o.FermataId)
			where f.ComuneId = $ComuneAndataId
			and f.FermataId = $FermataSalitaId";
			$arr_fermate = $db->fetch_array($sql);

			$sql = "select distinct f.FermataId as FermataIdDrop,
			concat(f.FermataNome, ' ( Open ) ', t.TrattaNome) AS FermataOrarioDrop,
			t.TrattaNome, f.FermataNome, concat('Open') as Orario
			from RT_Fermata f
			left Join RT_Tratta t on (t.TrattaId = f.TrattaId)
			left join RT_Orario o on (f.FermataId = o.FermataId)
			where f.ComuneId = $ComuneRitornoId
			and f.FermataId = $FermataDiscesaId";
			$arr_fermate_d = $db->fetch_array($sql);
		} else {
			//caso normale
			$sql = "SELECT distinct FermataId,FermataOrario,TrattaNome,Orario,FermataNome, TrattaPeso From RT_ElencoFermataOrarioPK where FermataId=$FermataSalitaId and CorsaId = $CorsaId";
			$arr_fermate = $db->fetch_array($sql);
			if (count($arr_fermate) == 0) {
				$sql = "SELECT FermataSalitaId as FermataId, FermataSalita as FermataNome, time(DataOraSalita) as Orario,  
					CONCAT(FermataSalita, ' (', time(DataOraSalita), ')') as FermataOrario
					FROM RT_PrenotazionePercorso where Direzione = '$Tp' AND PrenotazioneId=$prenotazione_wizard->Id";
				$arr_fermate = $db->fetch_array($sql);
			}
			$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome,Orario,FermataNome, TrattaPeso From RT_ElencoFermataOrarioDO WHERE FermataIdDrop=$FermataDiscesaId and CorsaId = $CorsaId";
			$arr_fermate_d = $db->fetch_array($sql);

			if (count($arr_fermate_d) == 0) {
				$sql = "SELECT FermataDiscesaId as FermataIdDrop, FermataDiscesa as FermataNome, time(DataOraDiscesa) as Orario,
				CONCAT(FermataDiscesa, ' (', time(DataOraDiscesa),  ')') as FermataOrario
				FROM RT_PrenotazionePercorso where Direzione = '$Tp' AND PrenotazioneId=$prenotazione_wizard->Id";
				$arr_fermate_d = $db->fetch_array($sql);
			}
		}
	} else {
		$sql = "select * from RT_PercorsoBreve where ComunePickupId=$ComuneAndataId and ComuneDropOffId=$ComuneRitornoId and CorsaId=$CorsaId";
		$r = $db->query_first($sql);
		$KmPercorsi = $r['KmPercorsi'];
		if (!empty($r['PercorsoBreveId'])) {
			$trattaPartenza = $r['TrattaPickupId'];
			$trattaArrivo = $r['TrattaDropOffId'];
		} else {

			$grafo = null;
			$grafo = new GrafoTratte($lineaId, $CorsaId, $db, $ComuneAndataId, $ComuneRitornoId);

			$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);

			$pre = new Prenotazione();
			$pre->conn = $db;
			$pre->CreatePercorsoBreve($ComuneAndataId, $ComuneRitornoId, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $CorsaId, $lineaId);
		}

		if (isset($CorsaRitornoAperto) && $CorsaRitornoAperto == "true") {
			$sql = "select distinct f.FermataId, 
					concat(f.FermataNome, ' ( Open ) ', t.TrattaNome) AS FermataOrario, 
					t.TrattaNome, f.FermataNome, concat('Open') as Orario, t.TrattaPeso 
					from RT_Fermata f 
					left Join RT_Tratta t on (t.TrattaId = f.TrattaId)
					left join RT_Orario o on (f.FermataId = o.FermataId) 
					where f.ComuneId = $ComuneAndataId 
					and t.Stato = 1 and t.Cancella = 0 and f.Stato = 1 and f.Cancella = 0 and f.IsPickup = 1
					order by TrattaPeso desc";
			$arr_fermate = $db->fetch_array($sql);
			if (count($arr_fermate) > 1) {
				$temp = array();
				$temp[] = $arr_fermate[0];
				$arr_fermate = $temp;
			}

			$sql = "select distinct f.FermataId as FermataIdDrop,
				concat(f.FermataNome, ' ( Open ) ', t.TrattaNome) AS FermataOrarioDrop,
				t.TrattaNome, f.FermataNome, concat('Open') as Orario, t.TrattaPeso  
				from RT_Fermata f
				left Join RT_Tratta t on (t.TrattaId = f.TrattaId)
				left join RT_Orario o on (f.FermataId = o.FermataId)
				where f.ComuneId = $ComuneRitornoId
				and t.Stato = 1 and t.Cancella = 0 and f.Stato = 1 and f.Cancella = 0 and f.IsDropOff = 1
				order by TrattaPeso desc";
			$arr_fermate_d = $db->fetch_array($sql);
			if (count($arr_fermate_d) > 1) {
				$temp = array();
				$temp[] = $arr_fermate_d[0];
				$arr_fermate_d = $temp;
			}
		} else {
			$sql = "SELECT distinct FermataId,FermataOrario,TrattaNome,FermataNome,Orario, TrattaPeso From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$ComuneAndataId  and OdcIdRef=$user->OdcId  order by TrattaPeso desc ";
			$arr_fermate = $db->fetch_array($sql);
			if (count($arr_fermate) > 1) {
				$temp = array();
				$temp[] = $arr_fermate[0];
				$arr_fermate = $temp;
			}

			$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome,FermataNome,Orario, TrattaPeso From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$CorsaId and ComuneId=$ComuneRitornoId  and OdcIdRef=$user->OdcId order by TrattaPeso asc";
			$arr_fermate_d = $db->fetch_array($sql);
			if (count($arr_fermate_d) > 1) {
				$temp = array();
				$temp[] = $arr_fermate_d[0];
				$arr_fermate_d = $temp;
			}
		}
	}

	/*creazione array per select*/
	$x1 = 0;
	while ($x1 < sizeof($arr_fermate)) {
		$arr_fermate[$x1]['FermataOrario'] = $arr_fermate[$x1]['FermataNome'] . " (" . $arr_fermate[$x1]['Orario'] . ")";
		$x1++;
	}
	$x1 = 0;
	while ($x1 < sizeof($arr_fermate_d)) {
		$arr_fermate_d[$x1]['FermataOrarioDrop'] = $arr_fermate_d[$x1]['FermataNome'] . " (" . $arr_fermate_d[$x1]['Orario'] . ")";
		$x1++;
	}


	if ($Tp == "A") 	// viaggio di andata
	{
		print("<table style='width: 98%;'><tr><td style='width:50%'>");
		print("<div id=\"FermataSalitaA\">");

		// fermata di pickup
		$def_fermata = 0;
		if (sizeof($arr_fermate) == 1) {
			$def_fermata = $arr_fermate[0]['FermataId'];
		}
		if ($FermataSalitaId > 0) {
			$def_fermata = $FermataSalitaId;
		}

		$page->create_select($dizionario['biglietto']['label_salita'], "Fermata[FermataId" . $Tp . "P]", "FermataId" . $Tp . "P", "brain_campoForm label100", $arr_fermate, $def_fermata, "FermataId", "FermataOrario", array(
			"onChange" => "'javascript:MostraTipoBiglietti(\"A\");'",
			"class" => "'required'"
		), 1);
		print("</div></td>");
		// fermata di dropoff
		print("<td style='width:50%'><div id=\"FermataDiscesaA\">");
		$def_fermata = 0;
		if (sizeof($arr_fermate_d) == 1) {
			$def_fermata = $arr_fermate_d[0]['FermataIdDrop'];
		}
		if ($FermataDiscesaId > 0) {
			$def_fermata = $FermataDiscesaId;
		}

		$page->create_select($dizionario['biglietto']['label_discesa'], "Fermata[FermataId" . $Tp . "D]", "FermataId" . $Tp . "D", "brain_campoForm label100", $arr_fermate_d, $def_fermata, "FermataIdDrop", "FermataOrarioDrop", array(
			"onChange" => "'javascript:MostraTipoBiglietti(\"A\");'",
			"class" => "'required'"
		), 1);
		print("</div></td></tr></table>");
	} else if ($Tp == "R") {	// viaggio di ritorno
		print("<table style='width: 98%;'><tr><td style='width:50%'>");
		print("<div id=\"FermataSalitaR\">");
		// fermata di pickup
		$def_fermata = 0;
		if (sizeof($arr_fermate) == 1)
			$def_fermata = $arr_fermate[0]['FermataId'];
		if ($FermataSalitaId > 0)
			$def_fermata = $FermataSalitaId;

		$page->create_select($dizionario['biglietto']['label_salita_ritorno'], "Fermata[FermataId" . $Tp . "P]", "FermataId" . $Tp . "P", "brain_campoForm label100", $arr_fermate, $def_fermata, "FermataId", "FermataOrario", array(
			"onChange" => "'javascript:MostraTipoBiglietti((\"R\"));'",
			"class" => "'required'"
		), 1);
		print("</div></td>");
		// fermata di dropoff
		print("<td style='width:50%'><div id=\"FermataDiscesaR\">");
		$def_fermata = 0;
		if (sizeof($arr_fermate_d) == 1)
			$def_fermata = $arr_fermate_d[0]['FermataIdDrop'];

		if ($FermataDiscesaId > 0)
			$def_fermata = $FermataDiscesaId;

		$page->create_select($dizionario['biglietto']['label_discesa_ritorno'], "Fermata[FermataId" . $Tp . "D]", "FermataId" . $Tp . "D", "brain_campoForm label100", $arr_fermate_d, $def_fermata, "FermataIdDrop", "FermataOrarioDrop", array(
			"onChange" => "'javascript:MostraTipoBiglietti(\"A\");'",
			"class" => "'required'"
		), 1);
		print("</div></td></tr></table>");
	}
	exit();
}

function GetCorse()
{
	global $user, $prenotazione_wizard, $dizionario;
	if (isset($_REQUEST['DataDal'])) {
		$DataDal = $_REQUEST['DataDal'];
	} else {
		$DataDal = null;
	}
	if (isset($_REQUEST['DataAl'])) {
		$DataAl = $_REQUEST['DataAl'];
	} else {
		$DataAl = null;
	}

	$ComunePartenza = $_REQUEST['PartenzaId'];
	$ComuneArrivo = $_REQUEST['DestinazioneId'];
	$Tp = $_REQUEST['Tp'];
	$TipoTour = $_REQUEST['TipoTour'];

	$CorsaId = 0;
	$db = new Database();
	$db->connect();

	if (is_object(($prenotazione_wizard))) {
		$prenotazione_wizard->conn = $db;
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		$NomeCliente = $DatiGeneraliArr['ClienteNome'];
		$CellulareCliente = $DatiGeneraliArr['ClienteCellulare'];
		$SessoIdCliente = $DatiGeneraliArr['ClienteSessoId'];
		$TipoViaggioId = $DatiGeneraliArr['TipoViaggioId'];

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso($Tp);
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;

		$ComuneSalitaId = $DatiGeneraliPercorsoArr['ComuneSalitaId'];
		$ComuneDiscesaId = $DatiGeneraliPercorsoArr['ComuneDiscesaId'];
		$FermataSalitaAId = $DatiGeneraliPercorsoArr['FermataSalitaId'];
		$FermataDiscesaAId = $DatiGeneraliPercorsoArr['FermataDiscesaId'];
		$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];
		$DataCorsa = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
	}

	if (($ComunePartenza > 0) and ($ComuneArrivo > 0)) {
		include_once("biglietto_corseandata_datatable.php");
	?>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables<?= $Tp ?>">
			<thead>
				<tr class="brain_tabellaTr">
					<th width="2%"></th>
					<th width="20%"><?= $dizionario['generale']['corsa'] ?></th>
					<th width="20%"><?= $dizionario['generale']['linea'] ?></th>
					<th width="10%"><?= $dizionario['generale']['data'] ?></th>
					<th width="10%"><?= $dizionario['generale']['giorno_up'] ?></th>
					<th width="10%"><?= $dizionario['biglietto']['partenza_p'] ?></th>
					<th width="10%"><?= $dizionario['partenza']['posti_disponibili'] ?></th>
				</tr>
				<tr class="brain_tabellaFilter">
					<th><input type="hidden" /></th>
					<th><input type="text" /></th>

					<th><input type="text" /></th>
					<th><input type="text" /></th>
					<th><input type="text" /></th>
					<th><input type="text" /></th>
					<th><input type="hidden" /></th>

				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="10" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?= $dizionario['generale']['caricamento_in_corso'] ?></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="10"></td>
				</tr>
			</tfoot>
		</table>


<?

	}

	exit();
}

function rimborsa()
{
	global $user, $prenotazione_wizard;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	$PrenotazioneId = $prenotazione_wizard->Id;
	$prenotazione_wizard->conn = $db;
	$x = $prenotazione_wizard->RimborsaBiglietti();

	echo ("R@" . $PrenotazioneId);
	exit();
}

function annulla($annullaViaggio = false)
{
	global $user, $prenotazione_wizard, $dizionario;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$prenotazione_wizard->conn = $db;
	if($annullaViaggio) {
		$stato = 16;
	} else {
		$stato = 4;
	}
	$x = $prenotazione_wizard->AnnullaPrenotazione($stato, $annullaViaggio);


	/**controllo disponibilita posti**/
	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoA = $prenotazione_wizard->DatiGeneraliPercorso;
	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
	$DatiGeneraliPercorsoR = $prenotazione_wizard->DatiGeneraliPercorso;

	aggiornaDisponibilita($DatiGeneraliPercorsoA['CorsaId'], $DatiGeneraliPercorsoA['CorsaDataPartenza']);
	if (isset($DatiGeneraliPercorsoR['CorsaDataPartenza'])) {
		aggiornaDisponibilita($DatiGeneraliPercorsoR['CorsaId'], $DatiGeneraliPercorsoR['CorsaDataPartenza']);
	}
	/**fine controllo disponibilita posti**/

	if(!$annullaViaggio) {
		/*annullamento scrontini emessi se presenti*/
		$sql = "select * from RT_PrenotazioneMovimento where PrenotazioneId=$prenotazione_wizard->Id AND ScrontinoId IS NOT NULL and ScontrinoIdAnnullato IS NOT NULL";
		$rows = $db->fetch_array($sql);
		$service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);
		foreach($rows as $row){
			$service->deleteBill($row['ScrontinoId']);
		}
		/*fine annullamento scrontini emessi se presenti*/
	}

	echo ("ok||" . $dizionario['biglietto']['mess_annulla']);
	exit();
}

function conferma_prenotazione()
{
	global $user, $prenotazione_wizard, $dizionario;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	$prenId = $prenotazione_wizard->Id;
	
	$data1['PrenotazioneStato'] = 1;
	$data1 = $storico->operazioni_update($data1, $user);
	$db->update("RT_Prenotazione", $data1, "PrenotazioneId = $prenId");

	echo ("ok||" . $dizionario['generale']['conferma_prenotazione_mess']);
	exit();
}

function modifica_info_passeggeri()
{
	global $user, $prenotazione_wizard, $dizionario;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	$data = $_POST['Prenotazione'];
	$data1['ClienteNome'] = $data['ClienteNome'];
	$data1['ClienteCellularePrefisso'] = $data['ClienteCellularePrefisso'];
	$data1['ClienteCellulare'] = $data['ClienteCellulare'];
	$data1['ClienteCellulareFamiliare'] = $data['ClienteCellulareFamiliare'];
	$data1['ClienteEmail'] = $data['ClienteEmail'];
	$data1['Assicurazione'] = intval($data['Assicurazione']);
	$data1 = $storico->operazioni_update($data1, $user);
	
	$prenId = $prenotazione_wizard->Id;

	$db->update("RT_Prenotazione", $data1, "PrenotazioneId=$prenId");

	// scrivi note
	$PrenotazioneNote = $_POST['PrenotazioneNote'];
	$sql = "select CorsaId,PrenotazionePercorsoId from RT_PrenotazionePercorso where PrenotazioneId=$prenId and Stato=1 and Cancella=0";
	$arrPercorsi = $db->fetch_array($sql);

	$db->delete("RT_PrenotazionePercorsoNote", "PrenotazioneId=$prenId");
	$PrenotazioneId = $prenId;

	foreach ($arrPercorsi as $r) {
		$CorsaId = $r['CorsaId'];
		$data_nota = null;
		$lastidA = $r['PrenotazionePercorsoId'];

		$data_nota['Nota'] = $PrenotazioneNote[$CorsaId . "_1"];
		$data_nota['TipoNota'] = 'S';
		$data_nota['PrenotazioneId'] = $PrenotazioneId;
		$data_nota['PrenotazionePercorsoId'] = $lastidA;

		if (!empty($data_nota['Nota'])) {
			$idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
		}

		$data_nota['Nota'] = $PrenotazioneNote[$CorsaId . "_2"];
		$data_nota['TipoNota'] = 'D';
		$data_nota['PrenotazioneId'] = $PrenotazioneId;
		$data_nota['PrenotazionePercorsoId'] = $lastidA;

		if (!empty($data_nota['Nota'])) {
			$idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
		}

		$data_nota['Nota'] = $PrenotazioneNote[$CorsaId . "_3"];
		$data_nota['TipoNota'] = 'B';
		$data_nota['PrenotazioneId'] = $PrenotazioneId;
		$data_nota['PrenotazionePercorsoId'] = $lastidA;

		if (!empty($data_nota['Nota'])) {
			$idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
		}

		$data_nota['Nota'] = $PrenotazioneNote[$CorsaId . "_4"];
		$data_nota['TipoNota'] = 'P';
		$data_nota['PrenotazioneId'] = $PrenotazioneId;
		$data_nota['PrenotazionePercorsoId'] = $lastidA;

		if (!empty($data_nota['Nota'])) {
			$idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
		}

		$data_nota['Nota'] = $PrenotazioneNote[$CorsaId . "_5"];
		$data_nota['TipoNota'] = 'G';
		$data_nota['PrenotazioneId'] = $PrenotazioneId;
		$data_nota['PrenotazionePercorsoId'] = $lastidA;

		if (!empty($data_nota['Nota'])) {
			$idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
		}
	}

	$sql = "select * from RT_PrenotazionePasseggeri 
        where PrenotazioneId = $prenId and Stato = 1 and Cancella = 0";
	$arrPasseggeri = $db->fetch_array($sql);
	$count = 0;
	$passeggeriPost = $_POST['Passeggeri'];
	foreach ($arrPasseggeri as $p) {
		$dataPasseggero = array();
		$dataPasseggero['Cognome'] = $passeggeriPost[$count]['Cognome'];
		$dataPasseggero['Nome'] = $passeggeriPost[$count]['Nome'];
		$dataPasseggero['SessoId'] = $passeggeriPost[$count]['SessoId'];
		$dataPasseggero['Eta'] = $passeggeriPost[$count]['Eta'];
		$dataPasseggero = $storico->operazioni_update($dataPasseggero, $user);
		$db->update("RT_PrenotazionePasseggeri", $dataPasseggero, "PrenotazionePasseggeroId=" . $p['PrenotazionePasseggeroId']);
		$count++;
	}

	echo ($dizionario['generale']['modifica_corretta']);
	exit();
}

function aggiorna_corsa()
{
	global $user, $prenotazione_wizard, $dizionario;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$CorsaAndata = $_POST['CorsaSelezionataA'];
	$DataSelezionataA = $_POST['DataSelezionataA'];
	$FermataAndataP = $data_fermate['FermataIdAP'];
	$FermataAndataD = $data_fermate['FermataIdAD'];

	$sql = "SELECT * FROM RT_Corsa WHERE CorsaId = $CorsaAndata";
	$corsaA = $db->query_first($sql);
	$sql = "SELECT * FROM RT_Linea WHERE LineaId = " . $corsaA['LineaId'];
	$lineaA = $db->query_first($sql);
	$sql = "SELECT * FROM RT_Percorso WHERE PercorsoId = " . $lineaA['PercorsoId'];
	$percorsoA = $db->query_first($sql);

	$prenotazioneId = $prenotazione_wizard->Id;
	$data['CorsaId'] = $corsaA['CorsaId'];
	$data['LineaId'] = $lineaA['LineaId'];
	$data['PercorsoId'] = $percorsoA['PercorsoId'];
	$data['CorsaNome'] = $corsaA['CorsaNome'];
	$data['LineaNome'] = $lineaA['LineaNome'];
	$data['PercorsoNome'] = $percorsoA['PercorsoNome'];
	$data['CorsaDataPartenza'] = $DataSelezionataA;

	$db->update("RT_PrenotazionePercorso", $data, "PrenotazioneId=$prenotazioneId AND Direzione='A'");
	$db->update("RT_PrenotazioneDettaglio", $data, "PrenotazioneId=$prenotazioneId AND Tragitto ='Andata'");
	aggiornaDisponibilita($CorsaAndata, $DataSelezionataA);

	if ($data_prenotazione['TipoViaggioId'] == 2) {
		$CorsaRitorno = $_POST['CorsaSelezionataR'];
		$DataSelezionataR = $_POST['DataSelezionataR'];
		$FermataRitornoP = $data_fermate['FermataIdRP'];
		$FermataRitornoD = $data_fermate['FermataIdRD'];

		$sql = "SELECT * FROM RT_Corsa WHERE CorsaId = $CorsaRitorno";
		$corsaR = $db->query_first($sql);
		$sql = "SELECT * FROM RT_Linea WHERE LineaId = " . $corsaR['LineaId'];
		$lineaR = $db->query_first($sql);
		$sql = "SELECT * FROM RT_Percorso WHERE PercorsoId = " . $lineaR['PercorsoId'];
		$percorsoR = $db->query_first($sql);

		$dataR['CorsaId'] = $corsaR['CorsaId'];
		$dataR['LineaId'] = $lineaR['LineaId'];
		$dataR['PercorsoId'] = $percorsoR['PercorsoId'];
		$dataR['CorsaNome'] = $corsaR['CorsaNome'];
		$dataR['LineaNome'] = $lineaR['LineaNome'];
		$dataR['PercorsoNome'] = $percorsoR['PercorsoNome'];
		$dataR['CorsaDataPartenza'] = $DataSelezionataR;

		$db->update("RT_PrenotazionePercorso", $dataR, "PrenotazioneId=$prenotazioneId AND Direzione='R'");
		$db->update("RT_PrenotazioneDettaglio", $data, "PrenotazioneId=$prenotazioneId AND Tragitto ='Ritorno'");
		aggiornaDisponibilita($CorsaRitorno, $DataSelezionataR);
	}

	echo ($dizionario['generale']['modifica_corretta']);
	exit();
}

function annulla_forzato()
{
	global $user, $prenotazione_wizard;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$prenotazione_wizard->conn = $db;
	$x = $prenotazione_wizard->AnnullaPrenotazione(10);

	echo ("ok");
	exit();
}

function modifica()
{
	create(1);
}

function create($modifica = 0)
{
	global $user, $prenotazione_wizard, $dizionario;

	$db = new Database();
	$db->connect();

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$arr_old_dati = null;
	$arr_old_dati_percorso_a = null;
	$arr_old_dati_percorso_r = null;
	$OldPax = 0;
	$OldDataPartenzaA = '';
	$OldDataPartenzaR = '';
	$OldCorsaPartenzaA = 0;
	$OldCorsaPartenzaR = 0;
	$OldPrenotazioneStato = 0;
	$oldtratte_a = null;
	$oldtratte_r = null;
	$daconfermare = 0;

	$cambioData = $_POST['cambio_data'];
	$infoBiglietto = $_POST['Biglietto'];

	$oldprenotazioneId = 0;
	$old_listino = null;
	$leadZoho = null;
	$leadZohoData = null;
	/** recupero vecchia prenotazione se in modifica **/
	//se si e' in modifica si recupera la prenotazione vecchia che sara' sostituita
	if ($modifica == 1) {
		$prenotazione_wizard->conn = $db;
		$old_prenotazioneid = $prenotazione_wizard->Id;
		$oldprenotazioneId = $old_prenotazioneid;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$arr_old_dati = $prenotazione_wizard->DatiGenerali;
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
		$arr_old_dati_percorso_a = $prenotazione_wizard->DatiGeneraliPercorso;
		$oldTipoViaggio = $arr_old_dati['TipoViaggioId'];
		$OldPax = $arr_old_dati['TotalePaxPrenotati'];
		$OldPrenotazioneStato = $arr_old_dati['PrenotazioneStato'];
		$OldDataPartenzaA = $arr_old_dati_percorso_a['CorsaDataPartenza'];
		$OldCorsaPartenzaA = $arr_old_dati_percorso_a['CorsaId'];
		$OldRitornoOpen = $arr_old_dati['RitornoOpen'];
		$OldComuneSalitaId = $arr_old_dati_percorso_a['ComuneSalitaId'];
		$OldComuneDiscesaId = $arr_old_dati_percorso_a['ComuneDiscesaId'];
		$OldOrarioSalita = substr($arr_old_dati_percorso_a['DataOraSalita'], 11, 5);
		$OldOrarioDiscesa = substr($arr_old_dati_percorso_a['DataOraDiscesa'], 11, 5);

		$oldLiberaTitolo = $arr_old_dati['LiberaTitolo'];

		$oldScontrinoInvioWeb = $arr_old_dati['ScontrinoInvioWeb'];
		
		$leadZoho = $arr_old_dati['ZohoLeadId'];
		$leadZohoData = $arr_old_dati['ZohoDataUpdate'];
		
		//recupero flotta
		$sql = "SELECT * FROM RT_Corsa WHERE CorsaId = $OldCorsaPartenzaA";
		$flottaRowTemp = $db->query_first($sql);
		$OldFlottaId = $flottaRowTemp['FlottaDefaultId'];

		//recupero supplemento
		$sqlSupp = "select sum(Supplemento) as tot from RT_PrenotazioneMovimento Where 
			TipoMovimento = 'I' and PrenotazioneId = $oldprenotazioneId and Stato = 1 and ImportoPagato > 0";
		$temp = $db->query_first($sqlSupp);
		$supplemento = $temp['tot'];

		$s = "select * from RT_PrenotazioneTratta where CorsaId=$OldCorsaPartenzaA and  Stato=1 and Cancella=0 and PrenotazioneId=$old_prenotazioneid order by PrenotazioneTrattaId asc";

		$oldtratte_a = $db->fetch_array($s);

		// print_r($oldtratte_a);

		if ($oldTipoViaggio == 2) {
			$prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
			$arr_old_dati_percorso_r = $prenotazione_wizard->DatiGeneraliPercorso;

			$OldDataPartenzaR = $arr_old_dati_percorso_r['CorsaDataPartenza'];
			$OldCorsaPartenzaR = $arr_old_dati_percorso_r['CorsaId'];

			$s = "select * from RT_PrenotazioneTratta where CorsaId=$OldCorsaPartenzaR and  Stato=1 and Cancella=0 and PrenotazioneId=$old_prenotazioneid order by PrenotazioneTrattaId asc";
			$oldtratte_r = $db->fetch_array($s);
		}

		//recupero listino giorni prima
		$sql = "SELECT ListinoIdPromozioneGGprima,ListinoIdPromozioneGGprimaR FROM RT_PrenotazioneBiglietto where prenotazioneid = " . $old_prenotazioneid;
		$oldlistinoRow = $db->fetch_array($sql);
		$old_listino = $oldlistinoRow[0]['ListinoIdPromozioneGGprima'];
		$old_listinoR = $oldlistinoRow[0]['ListinoIdPromozioneGGprimaR'];
	}

	/** fine recupero vecchia prenotazione se in modifica **/

	$prenotazione = new Prenotazione();
	$prenotazione->conn = $db;

	/** inizio recupero dati dalla form **/
	// prelevo i dati del form ed aggiorno tutte le propriet� dell'oggetto
	$StatoPrenotazione = 1;
	if (isset($_POST['Avanti']))
		$StatoPrenotazione = 13;

	//imposta stato 13 per agenzia non verificata
	$sql = "SELECT Verificato FROM Gestore WHERE GestoreId=$user->GestoreId";
	$row = $db->query_first($sql);
	if ($row['Verificato'] == 0) {
		$StatoPrenotazione = 13;
	}

	$data_prenotazione = $_POST['Prenotazione'];
	$temp = $data_prenotazione['ClienteNome'];
	$data_prenotazione['ClienteNome'] = ucwords($temp);
	$data_prenotazione['PrenotazioneStato'] = $StatoPrenotazione;

	//recupero tipo tour
	$TipoTour = $data_prenotazione['TipoTour'];

	//recupero prenotazione libera o standard
	$Libera = isset($data_prenotazione['Libera']) ? $data_prenotazione['Libera'] : 0;
	$DataPartenzaLibera = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['DataPartenzaLibera'])));
	$DataPartenzaLiberaF = $_POST['DataPartenzaLibera'];
	$OrarioPartenzaLibera = $_POST['OrarioPartenzaLibera'];
	$OrarioArrivoLibera = $_POST['OrarioArrivoLibera'];
	$BarcaLibera = $_POST['BarcaLibera'];
	$TitoloLibera = $_POST['TitoloLibera'];
	$data_prenotazione['LiberaTitolo'] = $TitoloLibera;
	
	//canale pagamento
	if ($user->GestoreId == 1) {
		$data_prenotazione['Canale'] = 'backoffice';
	} else {
		$data_prenotazione['Canale'] = 'agenzia';
	}

	//se multi prenotazione
	if ($data_prenotazione['Multi']) {
		if (!isset($_SESSION['PRENOTAZIONE_MULTI'])) {
			$data_prenotazione['CodicePrenotazione'] = $prenotazione->GetProgressivoCodicePrenotazione();
		} else {
			$data_prenotazione['CodicePrenotazione'] = $_SESSION['PRENOTAZIONE_MULTI'][0]['CodicePrenotazione'];
		}
	} else {
		$data_prenotazione['CodicePrenotazione'] = $prenotazione->GetProgressivoCodicePrenotazione();
	}

	$data_prenotazione = $storico->operazioni_insert($data_prenotazione, $user);
	
	$data_prenotazione['GestoreIdRef'] = $_POST['GestoreIdRef'];
	$data_prenotazione['ScontrinoInvioWeb'] = $oldScontrinoInvioWeb;

	if($modifica == 1 && isset($leadZoho)) {
		$data_prenotazione['ZohoLeadId'] = $leadZoho;
		$data_prenotazione['ZohoDataUpdate'] = $leadZohoData;
	}
	
	$ritornoOpen = $_POST['Prenotazione']['RitornoOpen'];
	$data_fermate = $_POST['Fermata'];
	$PrenotazioneNote = $_POST['PrenotazioneNote'];

	$TipoTourPasseggeri = isset($_POST['Prenotazione']['TipoTourPasseggeri']) ? $_POST['Prenotazione']['TipoTourPasseggeri'] : 0;
	$TipoTour = isset($_POST['Prenotazione']['TipoTour']) ? $_POST['Prenotazione']['TipoTour'] : 0;

	$conferma_errori = $_POST['RichiestaCofermaErrori'];

	if (!$Libera) {
		$CorsaAndata = $_POST['CorsaSelezionataA'];
		$DataSelezionataA = $_POST['DataSelezionataA'];
		$FermataAndataP = $data_fermate['FermataIdAP'];
		$FermataAndataD = $data_fermate['FermataIdAD'];
	} else {
		/********Inizio Prenotazione Libera ************/
		//se ho selezionato una prenotazione libera creo corsa, fermate, tratta ed orario
		$DataSelezionataA = $DataPartenzaLibera;

		if ($modifica == 1 && $DataPartenzaLibera == $OldDataPartenzaA && $BarcaLibera == $OldFlottaId
			&& $OrarioPartenzaLibera == $OldOrarioSalita && $OrarioArrivoLibera == $OldOrarioDiscesa) {
			$CorsaAndata = $OldCorsaPartenzaA;
		} else if ($modifica == 1 && ($DataPartenzaLibera != $OldDataPartenzaA || $BarcaLibera != $OldFlottaId
			|| $OrarioPartenzaLibera != $OldOrarioSalita || $OrarioArrivoLibera != $OldOrarioDiscesa)) {
			$CorsaAndata = $OldCorsaPartenzaA;

			$corsaLibera = array();
			if(isset($TitoloLibera)) {
				$corsaLibera['CorsaNome'] = $TitoloLibera;
			} else {
				$corsaLibera['CorsaNome'] = 'Libera ' . $DataPartenzaLiberaF;
			}
			$corsaLibera['FlottaDefaultId'] = $BarcaLibera;
			$sql = "SELECT * FROM RT_Flotta WHERE FlottaId = $BarcaLibera";
			$flotta = $db->query_first($sql);
			$corsaLibera['TipologiaBusDefaultId'] = $flotta['TipologiaBusId'];
			$corsaLibera['AttivaDal'] = $DataPartenzaLibera;
			$corsaLibera['AttivaAl'] = $DataPartenzaLibera;
			$corsaLibera['VendibileDal'] = $DataPartenzaLibera;
			$corsaLibera['VendibileAl'] = $DataPartenzaLibera;
			$corsaLibera['OrarioPartenza'] = $OrarioPartenzaLibera;
			$corsaLibera['OrarioArrivo'] = $OrarioArrivoLibera;
			$corsaLibera = $storico->operazioni_update($corsaLibera, $user);

			$db->update("RT_Corsa", $corsaLibera, "CorsaId=" . $CorsaAndata);
		} else {

			//creazione corsa
			$corsaLibera = array();
			$corsaLibera['LineaId'] = 14;
			if(isset($TitoloLibera)) {
				$corsaLibera['CorsaNome'] = $TitoloLibera;
			} else {
				$corsaLibera['CorsaNome'] = 'Libera ' . $DataPartenzaLiberaF;
			}
			$corsaLibera['FlottaDefaultId'] = $BarcaLibera;
			//recupero tipologia bus dalla flotta
			$sql = "SELECT * FROM RT_Flotta WHERE FlottaId = $BarcaLibera";
			$flotta = $db->query_first($sql);
			$corsaLibera['TipologiaBusDefaultId'] = $flotta['TipologiaBusId'];
			$corsaLibera['CorsaPeso'] = 1;
			$corsaLibera['AttivaDal'] = $DataPartenzaLibera;
			$corsaLibera['AttivaAl'] = $DataPartenzaLibera;
			$corsaLibera['VendibileDal'] = $DataPartenzaLibera;
			$corsaLibera['VendibileAl'] = $DataPartenzaLibera;
			$corsaLibera['OrePrimaStopVendita'] = '00:30:00';
			$corsaLibera['IncludiFeriale'] = 1;
			$corsaLibera['IncludiPrefestivo'] = 1;
			$corsaLibera['IncludiFestivo'] = 1;
			$corsaLibera['OrarioPartenza'] = $OrarioPartenzaLibera;
			$corsaLibera['OrarioArrivo'] = $OrarioArrivoLibera;
			$corsaLibera['NextDay'] = 0;
			$corsaLibera = $storico->operazioni_insert($corsaLibera, $user);
			$CorsaAndata = $db->insert("RT_Corsa", $corsaLibera);
			//inserisco i giorni della settimana della corsa
			for ($i = 1; $i <= 7; $i++) {
				$corsaSettLibera = array();
				$corsaSettLibera['CorsaId'] = $CorsaAndata;
				$corsaSettLibera['SettimanaId'] = $i;
				$corsaSettLibera = $storico->operazioni_insert($corsaSettLibera, $user);
				$db->insert("RT_CorsaSettimana", $corsaSettLibera);
			}
		}

		//verifica esistenza tratta
		$sql = "SELECT Tratta.TrattaId, Fermata1.FermataId AS FermataIdPartenza, Fermata2.FermataId AS FermataIdDestinazione
				FROM RT_Tratta AS Tratta
				JOIN RT_Fermata AS Fermata1 ON Tratta.TrattaId = Fermata1.TrattaId
				JOIN RT_Fermata AS Fermata2 ON Tratta.TrattaId = Fermata2.TrattaId
				WHERE Fermata1.ComuneId = " . $infoBiglietto['PartenzaId'] . " AND Fermata1.IsPickup = 1 
				AND Fermata2.ComuneId = " . $infoBiglietto['DestinazioneId'] . " AND Fermata2.IsDropOff = 1 
				AND Tratta.LineaId = 14";

		$rowTrattaLibera = $db->query_first($sql);

		if (isset($rowTrattaLibera['TrattaId']) && $rowTrattaLibera['TrattaId'] > 0) {
			$FermataAndataP = $rowTrattaLibera['FermataIdPartenza'];
			$FermataAndataD = $rowTrattaLibera['FermataIdDestinazione'];
		} else {
			//creazione tratta
			$trattaLibera = array();
			$trattaLibera['LineaId'] = 14;
			$trattaLibera['TrattaNome'] = creaNomeTratta($infoBiglietto['PartenzaId'], $infoBiglietto['DestinazioneId']);
			$trattaLibera['TrattaPeso'] = 1;
			$trattaLibera['NodoPeso'] = 1;
			$trattaLibera['TrattaTipoId'] = 1;
			$trattaLibera['MezzoId'] = 1;
			$trattaLibera['TrattaDirezioneId'] = 1;
			$trattaLibera['DaConfermare'] = 0;
			$trattaLibera['KmTratta'] = calcolaOre($OrarioPartenzaLibera, $OrarioArrivoLibera);
			$trattaLibera['TipologiaBusDefaultId'] = null;
			$trattaLibera = $storico->operazioni_insert($trattaLibera, $user);
			$trattaLiberaId = $db->insert("RT_Tratta", $trattaLibera);

			//creazione fermata	partenza
			$fermataLibera = array();
			$fermataLibera['TrattaId'] = $trattaLiberaId;
			$fermataLibera['ComuneId'] = $infoBiglietto['PartenzaId'];
			$fermataLibera['FermataNome'] = getComune($infoBiglietto['PartenzaId'])['Comune'];
			$fermataLibera['FermataPeso'] = 1;
			$fermataLibera['IsPickup'] = 1;
			$fermataLibera['IsDropOff'] = 0;
			$fermataLibera['IsInterscambio'] = 0;
			$fermataLibera['IsBlackList'] = 0;
			$fermataLibera['IsDaConfermare'] = 0;
			$fermataLibera['ImportanzaTratta'] = 0;
			$fermataLibera['WebSelling'] = 0;
			$fermataLibera['KmInizioTratta'] = 0;
			$fermataLibera = $storico->operazioni_insert($fermataLibera, $user);
			$FermataAndataP = $db->insert("RT_Fermata", $fermataLibera);

			//creazione fermata	destinazione
			$fermataLibera = array();
			$fermataLibera['TrattaId'] = $trattaLiberaId;
			$fermataLibera['ComuneId'] = $infoBiglietto['DestinazioneId'];
			$fermataLibera['FermataNome'] = getComune($infoBiglietto['DestinazioneId'])['Comune'];
			$fermataLibera['FermataPeso'] = 2;
			$fermataLibera['IsPickup'] = 0;
			$fermataLibera['IsDropOff'] = 1;
			$fermataLibera['IsInterscambio'] = 0;
			$fermataLibera['IsBlackList'] = 0;
			$fermataLibera['IsDaConfermare'] = 0;
			$fermataLibera['ImportanzaTratta'] = 0;
			$fermataLibera['WebSelling'] = 0;
			$fermataLibera['KmInizioTratta'] = $trattaLibera['KmTratta'];
			$fermataLibera = $storico->operazioni_insert($fermataLibera, $user);
			$FermataAndataD = $db->insert("RT_Fermata", $fermataLibera);
		}

		//inserimento orari corsa
		//orario partenza
		$orarioLibero = array();
		$orarioLibero['Orario'] = $OrarioPartenzaLibera;
		$orarioLibero['GiorniAggiuntivi'] = 0;
		$orarioLibero['FermataId'] = $FermataAndataP;
		$orarioLibero['CorsaId'] = $CorsaAndata;
		if ($modifica == 1) {
			$sql = "SELECT *
					FROM `RT_Orario`
					WHERE CorsaId = $CorsaAndata
					ORDER BY OrarioId ASC;";
			$rowOrarioLibera = $db->query_first($sql);
			$orarioLibero = $storico->operazioni_update($orarioLibero, $user);
			$db->update("RT_Orario", $orarioLibero, "OrarioId = " . $rowOrarioLibera['OrarioId']);
		} else {
			$orarioLibero = $storico->operazioni_insert($orarioLibero, $user);
			$db->insert("RT_Orario", $orarioLibero);
		}
		//orario destinazione
		$orarioLibero = array();
		$orarioLibero['Orario'] = $OrarioArrivoLibera;
		$orarioLibero['GiorniAggiuntivi'] = 0;
		$orarioLibero['FermataId'] = $FermataAndataD;
		$orarioLibero['CorsaId'] = $CorsaAndata;
		if ($modifica == 1) {
			$sql = "SELECT *
					FROM `RT_Orario`
					WHERE CorsaId = $CorsaAndata
					ORDER BY OrarioId DESC;";
			$rowOrarioLibera = $db->query_first($sql);
			$orarioLibero = $storico->operazioni_update($orarioLibero, $user);
			$db->update("RT_Orario", $orarioLibero, "OrarioId = " . $rowOrarioLibera['OrarioId']);
		} else {
			$orarioLibero = $storico->operazioni_insert($orarioLibero, $user);
			$db->insert("RT_Orario", $orarioLibero);
		}
		/********FINE Prenotazione Libera ************/
	}

	$IdTratteAndata = $prenotazione->GetTratte($CorsaAndata, $FermataAndataP, $FermataAndataD);
	$listini_id = $prenotazione->GetListini($IdTratteAndata, $FermataAndataP, $FermataAndataD, $CorsaAndata);

	// print_r($listini_id);
	$TratteComplete = $IdTratteAndata;
	$ListiniCompleti = $listini_id;
	$CorsaRitorno = 0;
	$FermataRitornoP = null;
	$FermataRitornoD = null;
	$DataSelezionataR = "";
	$IdTratteRitorno = array();
	if ($data_prenotazione['TipoViaggioId'] == 2) {
		$CorsaRitorno = $_POST['CorsaSelezionataR'];
		$DataSelezionataR = $_POST['DataSelezionataR'];
		$FermataRitornoP = $data_fermate['FermataIdRP'];
		$FermataRitornoD = $data_fermate['FermataIdRD'];
		$IdTratteRitorno = $prenotazione->GetTratte($CorsaRitorno, $FermataRitornoP, $FermataRitornoD);
		$listini_idr = $prenotazione->GetListini($IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno);

		$c = 0;

		while ($c < sizeof($IdTratteRitorno)) {
			$TratteComplete[] = $IdTratteRitorno[$c];
			$TrattaId = $IdTratteRitorno[$c]['TrattaId'];
			$ListiniCompleti[$TrattaId] = $listini_idr[$TrattaId];
			$c++;
		}
		$c = 0;
	}

	/** fine recupero dati dalla form **/

	// Check
	$check = true;
	$err = "";


	// verifica da confermare
	$dcr = 0;

	$dca = $prenotazione->GetDaConfermare($IdTratteAndata);
	if ($CorsaRitorno > 0)
		$dcr = $prenotazione->GetDaConfermare($IdTratteRitorno);

	$arr_Fermate[0]['FermataId'] = $FermataAndataP;
	$arr_Fermate[1]['FermataId'] = $FermataAndataD;
	if ($CorsaRitorno > 0) {
		$arr_Fermate[2]['FermataId'] = $FermataRitornoP;
		$arr_Fermate[3]['FermataId'] = $FermataRitornoD;
	}
	$FdaConf = $prenotazione->GetFermateDaConfermare($arr_Fermate);

	//se sono un operatore bertoldi il controllo da confermare non viene fatto
	if($user->GestoreId == 1) {
		$dcr = $dca = $FdaConf = 0;
	}

	//recupero comune fermate
	$sql = "SELECT ComuneId from RT_Fermata where FermataId = $FermataAndataP";
	$comuneAndataP = $db->query_first($sql);
	$comuneAndataP = $comuneAndataP['ComuneId'];
	$sql = "SELECT ComuneId from RT_Fermata where FermataId = $FermataAndataD";
	$comuneAndataD = $db->query_first($sql);
	$comuneAndataD = $comuneAndataD['ComuneId'];

	/*
	 *
	 */
	// ********************************************************************************

	//controllo validita ritorno open
	if ($oldTipoViaggio == 2 && $OldRitornoOpen == 1) {
		$today = new DateTime();
		$scadenzaOpen = new DateTime($OldDataPartenzaA);
		$scadenzaOpen->modify('+6 months');
		if ($today > $scadenzaOpen) {
			$errore = " " . $dizionario['biglietto']['mess_open'];
			echo ($errore);
			exit();
		}
	}


	// controllo la validita del numero di posti
	$arr_biglietti_prenotati = $_POST['BigliettoTipologiaPax'];
	$arr_biglietti_riduzione = $_POST['BigliettoTipologiaPaxRid'];
	$arr_biglietti_aumento = $_POST['BigliettoTipologiaPaxAum'];

	$ar1 = 0;
	if ($CorsaRitorno > 0)
		$ar1 = 1;
	$NumeroTotalePax = $prenotazione->ScriviTbBiglietti($arr_biglietti_prenotati, 0, $arr_biglietti_riduzione, $arr_biglietti_aumento, $TratteComplete, $ListiniCompleti, $FermataAndataP, $FermataAndataD, $DataSelezionataA, $ar1, $IdTratteAndata);

	$err .= $prenotazione->CheckNumeroPax($NumeroTotalePax);

	// controllo da biglietto emesso a lista d'attesa
	$errore = "";
	if ($OldPrenotazioneStato == 3) {
		if ($CorsaRitorno > 0) {
			$tp = 2;
		} else {
			$tp = 1;
		}
		$change = $prenotazione->checkIfChanged($comuneAndataP, $comuneAndataD, $tp, $DataSelezionataA, $CorsaAndata, $DataSelezionataR, $CorsaRitorno);
		if ($change && !$Libera) {
			$errore = $prenotazione->CheckDisponibilitaPax($DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata",  $comuneAndataP,  $comuneAndataD);
			if ($CorsaRitorno > 0) {
				$errore = $prenotazione->CheckDisponibilitaPax($DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno", $comuneAndataD,  $comuneAndataP);
			}
			if ($errore != '') {
				$errore .= ". " . $dizionario['biglietto']['mess_err_biglietto_emesso'] . "##999";
				echo ($errore);
				exit();
			}
		}
	}

	//controllo numero posti dei servizi e limite	
	foreach ($arr_biglietti_prenotati as $chiave => $valore) {
		if($valore > 0) {
			$temp = explode("_", $chiave);
			$sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = " . $temp[0];
			$tempRow = $db->query_first($sql);
			if ($tempRow['OccupaPosto'] == 0) {
				$passeggeriNumTemp = $NumeroTotalePax;
				if($TipoTour == 1){
					$passeggeriNumTemp = $TipoTourPasseggeri;
				}
				$result = checkLimiteServizi($temp[0], $CorsaAndata, $DataSelezionataA, $CorsaRitorno, $DataSelezionataR, $valore, $passeggeriNumTemp);
				if ($result['result'] == false) {
					$err .= $result['messaggio'];
				}
			}
		}
	}

	// Controllo corsa Ritorno se viaggio
	if ($user->SedeLegale != 1 && !$Libera) {
		$err .= $prenotazione->CheckDisponibilitaPax($DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata", $comuneAndataP,  $comuneAndataD);
		if ($CorsaRitorno > 0)
			$err .= $prenotazione->CheckDisponibilitaPax($DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno", $comuneAndataD,  $comuneAndataP);
	}
	// Controllo stato della prentoazione con yes no
	if ($data_prenotazione['TipoViaggioId'] == 2)
		$err .= $prenotazione->CheckCoerenzaAR($DataSelezionataA, $DataSelezionataR);

	$TipoPreR = -1;

	$TipoPreAP = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataP);
	$TipoPreAD = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataD);

	if ($CorsaRitorno > 0) {
		$TipoPreRP = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaRitorno, $FermataRitornoP);
		$TipoPreRD = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataRitornoD);
	}

	//controllo del doppio inserimento di una prenotazione per gestire casi anomali 
	// get ultima prenotazione per utente
	$sql = "select DataIns from RT_Prenotazione where OdcIdRef=$user->OdcId and OpeIns=$user->OperatoreId order by DataIns desc";

	$row = $db->query_first($sql);
	$DataIns = '';
	if (!empty($row['DataIns'])) {

		$DataIns = $row['DataIns'];
		$datacorrente = date('Y-m-d H:i:s');
		$dt1 = new DT();
		$secondi = $dt1->difference($datacorrente, $DataIns, 'Y-m-d H:i:s', 'second');
		if ($secondi <= 10)
			$err .= $dizionario['biglietto']['mess_err_due_prenotazioni'];
	}

	if ($CorsaRitorno > 0) {
		$sqA = "SELECT rt_linea.PercorsoId AS PercorsoId 
		FROM RT_Corsa AS rt_corsa 
		LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
		LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
		WHERE rt_corsa.Cancella = 0
		AND rt_corsa.CorsaId = $CorsaAndata";
		$rowA = $db->query_first($sqA);
		$PercorsoAndataId = $rowA['PercorsoId'];

		$sqR = "SELECT rt_linea.PercorsoId AS PercorsoId 
		FROM RT_Corsa AS rt_corsa 
		LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
		LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
		WHERE rt_corsa.Cancella = 0
		AND rt_corsa.CorsaId = $CorsaRitorno";
		$rowR = $db->query_first($sqR);
		$PercorsoRitornoId = $rowR['PercorsoId'];
	}

	if ($err != '') 	// chiedo di corregere gli errori con messaggio all'utente
	{
		echo ($err);
		exit();
	}

	$tratta_confermare = "salita";
	if ($dcr)
		$tratta_confermare = "discesa";
	//controllo validita' fermate in base all'agenzia
	if (($user->SedeLegale != 1) and ($dca + $dcr + $FdaConf > 0) and ($StatoPrenotazione == 3)) {

		echo ($dizionario['biglietto']['mess_err_fermate_no_confermate']);
		exit();
	}
	$trattedifferenti = false;
	if ($modifica == 1) {
		if (((sizeof($oldtratte_a)) != (sizeof($IdTratteAndata))) or ((sizeof($oldtratte_r)) != (sizeof($IdTratteRitorno))))
			$trattedifferenti = true;
		else {
			$qtr = 0;
			while ($qtr < sizeof($oldtratte_a)) {
				if ($oldtratte_a[$qtr]['TrattaId'] != $IdTratteAndata[$qtr]['TrattaId'])
					$trattedifferenti = true;
				$qtr++;
			}
			$qtr = 0;
			if (sizeof($oldtratte_r) > 0) {
				while ($qtr < sizeof($oldtratte_r)) {
					if ($oldtratte_r[$qtr]['TrattaId'] != $IdTratteRitorno[$qtr]['TrattaId'])
						$trattedifferenti = true;
					$qtr++;
				}
			}
		}
	}

	if (($conferma_errori > 0) and (($conferma_errori < 100)) and ($user->SedeLegale = 1)) {
		$daconfermare = 0;
		if ((($dca + $dcr + $FdaConf) > 0)) {
			if ($modifica == 1) {
				if ($trattedifferenti) {
					echo ($dizionario['biglietto']['mess_fermate_da_confermare'] . "##100");
					$data_prenotazione['PrenotazioneStato'] = 2;
					exit();
					$StatoPrenotazione = 2;
					$daconfermare = 1;
				} else {
					if ($OldPrenotazioneStato == 2) {
						$StatoPrenotazione = 2;
						$daconfermare = 1;
					}
				}
			} else {
				echo ($dizionario['biglietto']['mess_tratta_da_confermare'] . " $tratta_confermare " . $dizionario['biglietto']['mess_tratta_da_confermare2'] . "##100");
				$data_prenotazione['PrenotazioneStato'] = 2;
				exit();
				$StatoPrenotazione = 2;
				$daconfermare = 1;
			}
		} else
			$trattedifferenti = false;
	}

	// verifico se il numero di pax e' cambiato
	if (($conferma_errori > 0) and ($conferma_errori < 200) and ($user->SedeLegale == 1) && !$Libera) {
		$errore = '';
		$daconfermare = 0;

		$OldPax = $arr_old_dati['TotalePaxPrenotati'];
		$OldDataPartenzaA = $arr_old_dati_percorso_a['CorsaDataPartenza'];
		$OldCorsaPartenzaA = $arr_old_dati_percorso_a['CorsaId'];

		if ($DataSelezionataA != $OldDataPartenzaA || $CorsaAndata != $OldCorsaPartenzaA || ($NumeroTotalePax != $OldPax)) {
			$errore = $prenotazione->CheckDisponibilitaPax($DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata", $comuneAndataP,  $comuneAndataD);
			if ($errore != '') {
				$errore .= ". Sono terminati i posti nella corsa di andata";
			}
		}

		if ($CorsaRitorno > 0) {

			$OldDataPartenzaR = $arr_old_dati_percorso_r['CorsaDataPartenza'];

			if ($DataSelezionataR != $OldDataPartenzaR) {
				$errore .= $prenotazione->CheckDisponibilitaPax($DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno", $comuneAndataD,  $comuneAndataP);
				if ($errore != '') {
					$errore .= ". Sono terminati i posti nella corsa di ritorno";
				}
			}
		}

		if ($errore != '') {
			$errore .= ". Inserire la prenotazione in lista d'attesa?##200";
			echo ($errore);
			exit();
			$daconfermare = 1;
			$data_prenotazione['PrenotazioneStato'] = 5;
			$StatoPrenotazione = 5;
		}
	}

	$arr_PostoSceltoA = null;
	$arr_PostoSceltoAI = null;
	if(isset($_POST['PostoSceltoA'])) {
		$arr_PostoSceltoA = $_POST['PostoSceltoA'];
		$length_a = count(array_keys($arr_PostoSceltoA, "0"));
		$selezionati_andata = sizeof($arr_PostoSceltoA) - $length_a;
	} else {
		$selezionati_andata = 0;
	}

	if(isset($_POST['PostoSceltoA'])) {
		$arr_PostoSceltoR = $_POST['PostoSceltoR'];
		$length_r = count(array_keys($arr_PostoSceltoR, "0"));
		$selezionati_ritorno = sizeof($arr_PostoSceltoR) - $length_r;
	} else {
		$selezionati_ritorno = 0;
	}
	
	if (($selezionati_andata > 0) and ($selezionati_andata != $NumeroTotalePax)) {
		echo ($dizionario['biglietto']['mess_posti_prenotati']);
		$check = false;
		exit();
	}
	if ($CorsaRitorno > 0) {

		if (($selezionati_andata > 0) and ($selezionati_ritorno != $NumeroTotalePax)) {
			echo ($dizionario['biglietto']['mess_posti_prenotati_ritorno']);
			$check = false;
			exit();
		} elseif ($selezionati_andata != $selezionati_ritorno) {
			echo ($dizionario['biglietto']['mess_preferenza_posti']);
			$check = false;
			exit();
		}
	}

	if (($data_prenotazione['TipoViaggioId'] == 2) and (!($CorsaRitorno > 0)) and ($ritornoOpen == 0)) {
		echo ($dizionario['biglietto']['mess_no_ritorno']);
		$check = false;
		exit();
	}

	if (!$CorsaAndata > 0) {
		echo ($dizionario['biglietto']['mess_no_andata']);
		$check = false;
		exit();
	}

	if (($conferma_errori < 400) and (isset($_POST['Emetti'])) and ($_POST['Emetti'] != '')) {
		echo ($dizionario['biglietto']['mess_emetti'] . "##400");
		$check = false;
		exit();
	}

	if ($check == true) {

		if ($modifica == 1) {
			$prenotazione_wizard->conn = $db;
			$PrenotazioneId = $prenotazione_wizard->Id;
			$data['Stato'] = 0;
			$data['Cancella'] = 1;
			$data1 = $data;
			$data1['PrenotazioneStato'] = 6;
			$data1['Stato'] = 1;
			$data1['Cancella'] = 0;
			
			$data = $storico->operazioni_update($data, $user);
			$data1 = $storico->operazioni_update($data1, $user);
			
			$result = $db->update("RT_Prenotazione", $data1, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId");
			$result = $db->update("RT_PrenotazionePercorso", $data1, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId");
			$result = $db->update("RT_PrenotazionePosto", $data, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId");
			$result = $db->update("RT_PrenotazionePercorsoNote", $data, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId");
			$result = $db->update("RT_PrenotazioneTariffa", $data, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId");
			$result = $db->update("RT_PrenotazioneTratta", $data, "PrenotazioneId=" . $PrenotazioneId . " and OdcIdRef=$user->OdcId");
			// faccio update a stato cancellato / modificata e creo una nuova prenotazione

			//aggiorno il dettaglio per escludere la prenotazione
			$dataDettaglio = null;
			$dataDettaglio['Escludi'] = 1;
			$dataDettaglio = $storico->operazioni_update($dataDettaglio, $user);

			$db->update("RT_PrenotazioneDettaglio", $dataDettaglio, "PrenotazioneId = $old_prenotazioneid");
		}
		// verifico se le fermate sono da confermare

		if (($modifica != 1) or ($trattedifferenti)) {

			if ((($dca + $dcr + $FdaConf) > 0)) {
				$data_prenotazione['PrenotazioneStato'] = 2;
				$StatoPrenotazione = 2;
				$daconfermare = 1;
			}
		}

		if (($modifica != 1) or ($conferma_errori == 200) && !$Libera) {

			$errore = $prenotazione->CheckDisponibilitaPax($DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata", $comuneAndataP,  $comuneAndataD);

			if ($CorsaRitorno > 0)
				$errore .= $prenotazione->CheckDisponibilitaPax($DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno", $comuneAndataD,  $comuneAndataP);

			if ($errore != '') {

				$data_prenotazione['PrenotazioneStato'] = 5;
				$StatoPrenotazione = 5;
				$daconfermare = 1;
			}
		}

		$lastidA = $db->insert("RT_Prenotazione", $data_prenotazione);
		$NuovaPrenotazioneId = $lastidA;

		if ($lastidA != false) {

			$ok = $prenotazione->SettaId($lastidA);

			$ar1 = 0;
			$TV = 1;
			if ($CorsaRitorno > 0) {
				$ar1 = 1;
				$TV = 2;
			}

			//inserisco i passeggeri
			$passeggeri = $_POST['Passeggeri'];
			$indexPrincipale = $_POST['PasseggeriPrincipale'];
			$principaleNome = '';
			$principaleCognome = '';
			foreach ($passeggeri as $index => $passeggero) {
				$temp = $passeggero['Nome'];
				$passeggero['Nome'] = ucwords($temp);
				$temp = $passeggero['Cognome'];
				$passeggero['Cognome'] = ucwords($temp);
				if ($indexPrincipale == $index) {
					$passeggero['Principale'] = 1;
					$principaleNome = $passeggero['Nome'];
					$principaleCognome = $passeggero['Cognome'];
				} else {
					$passeggero['Principale'] = 0;
				}

				$passeggero['PrenotazioneId'] = $NuovaPrenotazioneId;
				$passeggero = $storico->operazioni_insert($passeggero, $user);
				$passeggero['GestoreIdRef'] = $data_prenotazione['GestoreIdRef'];
				unset($passeggero['TipologiaBiglietto']);
				$db->insert("RT_PrenotazionePasseggeri", $passeggero);
			}

			// $x=$prenotazione->ScriviTbBiglietti($arr_biglietti_prenotati,1,$arr_biglietti_riduzione,$arr_biglietti_aumento,$TratteComplete,$ListiniCompleti,$FermataAndataP,$FermataAndataD,$DataSelezionataA,$ar1,$IdTratteAndata);

			$OldId = null;
			$PrenotazioneOld = null;


			$arrPrezzi = $prenotazione->GetTipologiaBigliettiPrezzi(1, $NuovaPrenotazioneId, $prenotazione_wizard, $DataSelezionataA, $CorsaAndata, $FermataAndataP, $FermataAndataD, $CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $TV, $arr_biglietti_prenotati, $arr_biglietti_aumento, $arr_biglietti_riduzione, $PrenotazioneOld, $old_prenotazioneid, $OldPrenotazioneStato, $DataSelezionataR, $old_listino, $old_listinoR, $TipoTour, $Libera, null, null, $data_prenotazione['GestoreIdRef']);
			// 			print_r($arrPrezzi); 
			// if ($modifica!=1)
			$xx = $prenotazione->GeneraCodiciPrenotazione($NumeroTotalePax);
			$x = $prenotazione->ScriviTbTratte($CorsaAndata, $IdTratteAndata, 'A', $data_prenotazione['GestoreIdRef']);

			$DataPartenza = $_POST['DataSelezionataA'];
			if ($Libera) {
				$DataPartenza = $DataSelezionataA;
			}
			$x = $prenotazione->ScriviTbPercorso($CorsaAndata, $FermataAndataP, $FermataAndataD, $DataPartenza, 'A', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax, $data_prenotazione['GestoreIdRef']);

			$x = $prenotazione->ScriviTbTariffe($listini_id, $IdTratteAndata, $CorsaRitorno, $FermataAndataP, $FermataAndataD, $DataPartenza, null);
			if(isset($arr_PostoSceltoA) && isset($arr_PostoSceltoAI)) {
				$x = $prenotazione->ScriviTbPostoScelto($arr_PostoSceltoA, $arr_PostoSceltoAI, $CorsaAndata, $DataPartenza);
			}

			if ($CorsaRitorno > 0) {
				$x = $prenotazione->ScriviTbTratte($CorsaRitorno, $IdTratteRitorno, 'R', $data_prenotazione['GestoreIdRef']);
				$DataPartenza = $_POST['DataSelezionataR'];
				$x = $prenotazione->ScriviTbPercorso($CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $DataPartenza, 'R', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax, $data_prenotazione['GestoreIdRef']);
				$listini_id_r = $prenotazione->GetListini($IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno);
				$x = $prenotazione->ScriviTbPostoScelto($arr_PostoSceltoR, $arr_PostoSceltoIR, $CorsaRitorno, $DataPartenza);
			} else
				$CorsaRitorno = 0;

			$abilitazioneprenotazione = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataP, $Libera);

			if ($modifica == 1) {
				$OldId = $prenotazione_wizard->Id;

				$PrenotazioneOld = new Prenotazione($OldId);
				$PrenotazioneOld->conn = $db;
				$PrenotazioneOld->inizializzaDatiGenerali();
				$arr_generali = $PrenotazioneOld->DatiGenerali;

				$dupd = null;
				$dupd['PrenotazioneId'] = $NuovaPrenotazioneId;
				$dupd['OdcIdRef'] = $arr_generali['OdcIdRef'];
				$dupd['GestoreIdRef'] = $data_prenotazione['GestoreIdRef'];
				$dupd = $storico->operazioni_update($dupd, $user);
				$dupd1 = $dupd;
				$dupd1['CodicePrenotazione'] = $arr_generali['CodicePrenotazione'];
				$dupd1['ScadenzaPrenotazione'] = ($arr_generali['ScadenzaPrenotazione'] != '') ? $arr_generali['ScadenzaPrenotazione'] : 'NULL';
				$result = $db->update("RT_Prenotazione", $dupd1, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId");

				$dupd['PrenotazioneId'] = $lastidA;
				$result = $db->update("RT_PreparazioneBus", $dupd, "PrenotazioneId=" . $OldId . " and OdcIdRef=$user->OdcId");
				$result = $db->update("RT_PreparazioneNavette", $dupd, "PrenotazioneId=" . $OldId . " and OdcIdRef=$user->OdcId");
			}

			$TipoViaggio1 = "Corsa Semplice";
			if ($CorsaRitorno > 0)
				$TipoViaggio1 = "Andata/Ritorno";

			$return = $prenotazione->CreateDettaglioPrenotazione($CorsaAndata, "Andata", $TipoViaggio1, $StatoPrenotazione, $data_prenotazione['GestoreIdRef']);

			if ($CorsaRitorno > 0)
				$return = $prenotazione->CreateDettaglioPrenotazione($CorsaRitorno, "Ritorno", $TipoViaggio1, $StatoPrenotazione, $data_prenotazione['GestoreIdRef']);

			$oldGestoreIdRef = isset($arr_old_dati['GestoreIdRef']) ? intval($arr_old_dati['GestoreIdRef']) : 0;
			$newGestoreIdRef = isset($data_prenotazione['GestoreIdRef']) ? intval($data_prenotazione['GestoreIdRef']) : 0;
			if ($modifica == 1 && $OldPrenotazioneStato == 3 && $oldGestoreIdRef > 0 && $newGestoreIdRef > 0 && $oldGestoreIdRef != $newGestoreIdRef) {
				$lineaId = 0;
				$rowLinea = $db->query_first("SELECT LineaId FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $NuovaPrenotazioneId LIMIT 1");
				if (isset($rowLinea['LineaId'])) {
					$lineaId = intval($rowLinea['LineaId']);
				}
				if ($lineaId == 0 && $old_prenotazioneid > 0) {
					$rowLinea = $db->query_first("SELECT LineaId FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $old_prenotazioneid LIMIT 1");
					if (isset($rowLinea['LineaId'])) {
						$lineaId = intval($rowLinea['LineaId']);
					}
				}
				$provId = 0;
				if ($lineaId > 0) {
					$rowProv = $db->query_first("SELECT ProvvigioneId FROM GestoreConvenzione WHERE GestoreId = $newGestoreIdRef AND LineaId = $lineaId");
					if (isset($rowProv['ProvvigioneId'])) {
						$provId = intval($rowProv['ProvvigioneId']);
					}
				}
				$titoli = $db->fetch_array("SELECT PrenotazioneTitoloId, TipologiaBigliettoId, ImportoTitolo FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = $NuovaPrenotazioneId");
				if (sizeof($titoli) == 0 && $old_prenotazioneid > 0) {
					$titoli = $db->fetch_array("SELECT PrenotazioneTitoloId, TipologiaBigliettoId, ImportoTitolo FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = $old_prenotazioneid");
				}
				foreach ($titoli as $t) {
					$ptid = intval($t['PrenotazioneTitoloId']);
					$bigliettoId = intval($t['TipologiaBigliettoId']);
					$fisso = 0.0;
					$perc = 0.0;
					if ($provId > 0 && $bigliettoId > 0) {
						$par = $db->query_first("SELECT Fisso, Percentuale FROM RT_ProvvigioneBiglietto WHERE ProvvigioneId = $provId AND BigliettoId = $bigliettoId");
						if (isset($par['Fisso'])) {
							$fisso = floatval($par['Fisso']);
						}
						if (isset($par['Percentuale'])) {
							$perc = floatval($par['Percentuale']);
						}
					}
					$importoTitolo = floatval($t['ImportoTitolo']);
					$importoAgenzia = ($importoTitolo * ($perc / 100.0)) + $fisso;
					$dup = array();
					$dup['GestoreId'] = $newGestoreIdRef;
					$dup['FissoAgenzia'] = $fisso;
					$dup['PercentualeAgenzia'] = $perc;
					$dup['ImportoAgenzia'] = $importoAgenzia;
					$dup = $storico->operazioni_update($dup, $user);
					$rowCheck = $db->query_first("SELECT PrenotazioneTitoloProvvigioneId FROM RT_PrenotazioneTitoloProvvigione WHERE PrenotazioneTitoloId = $ptid AND GestoreId = $oldGestoreIdRef LIMIT 1");
					if (isset($rowCheck['PrenotazioneTitoloProvvigioneId'])) {
						$db->update("RT_PrenotazioneTitoloProvvigione", $dup, "PrenotazioneTitoloProvvigioneId = " . $rowCheck['PrenotazioneTitoloProvvigioneId']);
					} else {
						$ins = array();
						$ins['PrenotazioneTitoloId'] = $ptid;
						$ins['GestoreId'] = $newGestoreIdRef;
						$ins['FissoAgenzia'] = $fisso;
						$ins['PercentualeAgenzia'] = $perc;
						$ins['ImportoAgenzia'] = $importoAgenzia;
						$ins = $storico->operazioni_insert($ins, $user);
						$db->insert("RT_PrenotazioneTitoloProvvigione", $ins);
					}
				}
			}

			if ($user->SedeLegale == 1) {

				$data1a['DaFatturare'] = 0;
				$data1a['DaBonificare'] = 0;

				$result = $db->update("RT_PrenotazioneDettaglio", $data1a, "GestoreIdRef=1");
			}

			if (($modifica == 1) and ($cambioData == 3)) {

				$data11['PrenotazioneStato'] = 3;

				$result = $db->update("RT_Prenotazione", $data11, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId");
				$result = $db->update("RT_PrenotazionePercorso", $data11, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId");

				$data1ag['PrenotazioneId'] = $NuovaPrenotazioneId;
				$result = $db->update("RT_PrenotazioneTitolo", $data1ag, "PrenotazioneId=$old_prenotazioneid");

				// echo("cambio data per la prenotazione ".$old_prenotazioneid." con ".$NuovaPrenotazioneId);
				$sql = "Select PrenotazioneNumeroId from RT_PrenotazioneNumero where PrenotazioneId=$NuovaPrenotazioneId order by PasseggeroId asc";
				$ArrObjectP = $db->fetch_array($sql);

				$sql = "Select PrenotazioneNumeroId from RT_PrenotazioneNumero where PrenotazioneId=$old_prenotazioneid";
				$ArrObjectPOld = $db->fetch_array($sql);

				$npre = 0;
				while ($npre < sizeof($ArrObjectP)) {
					$PrenotazioneNumeroId = $ArrObjectP[$npre]['PrenotazioneNumeroId'];
					$PrenotazioneNumeroIdOld = $ArrObjectPOld[$npre]['PrenotazioneNumeroId'];

					$data1ag1['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
					$result = $db->update("RT_PrenotazioneTitolo", $data1ag1, "PrenotazioneNumeroId=$PrenotazioneNumeroIdOld");

					$npre++;
				}
			}

			//controllo penale
			if ($modifica == 1) {

				$penale = checkModificaPenaleResult();
				$prenotazione->inizializzaDatiGenerali();
				$totaliImporti = $prenotazione->GetTotaliPrenotazione();

				$prenotazione_wizard->inizializzaDatiGenerali();
				$penaleOld = $prenotazione_wizard->GetPenale();
				$totaliImportiOld = $prenotazione_wizard->GetTotaliPrenotazione();
				$penaleImporto =  $penaleOld['Penale'] + $penale['fisso'] + $totaliImporti['TotalePrenotazione'] * $penale['perc'] / 100;

				if ($penaleImporto > 0 || $supplemento > 0) {
					$dataPenale = null;
					$dataPenale['Penale'] = round($penaleImporto, 2);
					$dataPenale['TotalePrenotazione'] = $totaliImporti['TotalePrenotazione'] + $dataPenale['Penale'];
					$dataPenale['TotaleDaPagare'] = $totaliImporti['TotaleDaPagare'] + $dataPenale['Penale'] + $supplemento;
					// 					$dataPenale['TotaleDaPagare'] = $totaliImporti['TotaleDaPagare'] + $dataPenale['Penale'];
					$dataPenale['TotaleResiduo'] = $totaliImporti['TotaleResiduo'] + $dataPenale['Penale'];
					$db->update("RT_Prenotazione", $dataPenale, "PrenotazioneId = " . $NuovaPrenotazioneId);
				}
			}

			/** gestione dei movimenti in caso di modifica **/
			if ($modifica == 1) {
				$prenotazione->inizializzaDatiGenerali();
				$totaliImporti = $prenotazione->GetTotaliPrenotazione();

				// print("<pre>");
				// print("NUOVI IMPORTO<BR />");
				//                                 print_r($totaliImporti);

				$prenotazione_wizard->inizializzaDatiGenerali();
				$index = $prenotazione_wizard->GetTotaliPrenotazione();
				// print("VECCHI IMPORTO<BR />");
				//print_r($totaliImportiOld);

				//Copio i movimenti e li metto come annullati
				$OldId = $prenotazione_wizard->Id;

				$movimentoObj = new PrenotazioneMovimento();
				$movimentoObj->conn = $db;

				$oldMovimenti = $movimentoObj->getAllPrenotazioneMovimento($OldId);

				foreach ($oldMovimenti as $newMovimento) {
					$newMovimento['PrenotazioneId'] = $NuovaPrenotazioneId;
					$newMovimento['Data'] = ($newMovimento['Data'] != '') ? $newMovimento['Data'] : 'NULL';
					$newMovimento['DataPagamento'] = ($newMovimento['DataPagamento'] != '') ? $newMovimento['DataPagamento'] : 'NULL';
					$newMovimento['Scadenza'] = ($newMovimento['Scadenza'] != '') ? $newMovimento['Scadenza'] : 'NULL';
					$newMovimento['Coupon'] = (isset($newMovimento['Coupon']) && $newMovimento['Coupon'] != '') ? $newMovimento['Coupon'] : 'NULL';
					$newMovimento['ScontrinoId'] = ($newMovimento['ScontrinoId'] != '') ? $newMovimento['ScontrinoId'] : 'NULL';
					$newMovimento['ScontrinoData'] = ($newMovimento['ScontrinoData'] != '') ? $newMovimento['ScontrinoData'] : 'NULL';
					$newMovimento['ScontrinoTipo'] = ($newMovimento['ScontrinoTipo'] != '') ? $newMovimento['ScontrinoTipo'] : 'NULL';
					$newMovimento['ScontrinoInvioAuto'] = ($newMovimento['ScontrinoInvioAuto'] != '') ? $newMovimento['ScontrinoInvioAuto'] : 'NULL';
					$newMovimento['ScontrinoIdAnnullato'] = ($newMovimento['ScontrinoIdAnnullato'] != '') ? $newMovimento['ScontrinoIdAnnullato'] : 'NULL';
					$newMovimento['ScontrinoDataAnnullato'] = ($newMovimento['ScontrinoDataAnnullato'] != '') ? $newMovimento['ScontrinoDataAnnullato'] : 'NULL';
					$newMovimento['ScontrinoNotifica'] = ($newMovimento['ScontrinoNotifica'] != '') ? $newMovimento['ScontrinoNotifica'] : 'NULL';
					$newMovimento['ScontrinoDataInvio'] = ($newMovimento['ScontrinoDataInvio'] != '') ? $newMovimento['ScontrinoDataInvio'] : 'NULL';
					$newMovimento['SedeIns'] = ($newMovimento['SedeIns'] != '') ? $newMovimento['SedeIns'] : 'NULL';

					//verifico se e' stato effettuato pagamento con cupon per riattivare il coupon
					if ($newMovimento['TipoMovimento'] != 'A') {
						$codiceCoupon = "";
						if (strpos($newMovimento['Causale'], 'coupon - Cod.') !== false) {
							$index = strpos($newMovimento['Causale'], 'Cod. ');
							$codiceCoupon = substr($newMovimento['Causale'], $index + 5, strlen($newMovimento['Causale']) - 1);
						}
						if(isset($codiceCoupon) && $codiceCoupon != "") {
							$sql = "select * from RT_Coupon where Codice = '$codiceCoupon'";
							$row = $db->query_first($sql);
							$row['Valore'] = $row['Valore'] + $newMovimento['ImportoPagato'];
							if ($row['Valore'] == $row['Importo']) {
								$row['Utilizzi'] = $row['Utilizzi'] - 1;
							}
							$row = $storico->operazioni_update($row, $user);
							$result = $db->update("RT_Coupon", $row, "Codice='$codiceCoupon'");
						}
					}

					if ($totaliImporti['TotalePrenotazione'] != $totaliImportiOld['TotalePrenotazione']) {
						//annullo i movimenti copiati se la prenotazione non ha biglietti emessi
						if ($prenotazione->DatiGenerali['PrenotazioneStato'] != 3) {
							$newMovimento['Cancella'] = 1;
							$newMovimento['TipoMovimento'] = "A";
							$newMovimento['Causale'] = "Annullato per modifica";
						} elseif ($prenotazione->DatiGenerali['PrenotazioneStato'] == 3 && $newMovimento['TipoMovimento'] == "P") {
							$newMovimento['Cancella'] = 1;
							$newMovimento['TipoMovimento'] = "A";
							$newMovimento['Causale'] = "Annullato per modifica dopo emissione";
						}
					}

					// 					if($ritornoOpen == 0 && $OldRitornoOpen == 1 ){
					// 						$dataTempOpen['TotalePagato'] = $dataTempOpen['TotalePagato']+$newMovimento['Importo'];
					// 						$dataTempOpen['TotaleResiduo'] = $dataTempOpen['TotaleResiduo']-$newMovimento['Importo'];
					// 					}
					if ($newMovimento['TipoMovimento'] == "I") {
						$newMovimento['Scadenza'] = 'NULL';
						$data['ScadenzaPrenotazione'] = 'NULL';
						$db->update("RT_Prenotazione", $data, "PrenotazioneId = " . $NuovaPrenotazioneId);
					}
					
					$db->insert("RT_PrenotazioneMovimento", $newMovimento);
					
				}
				// 				print_r($totaliImporti);
				// 				print_r($totaliImportiOld);
				//crea un nuovo movimento contabile con il totale della prenotazione modificata
				if ($totaliImporti['TotalePrenotazione'] != $totaliImportiOld['TotalePrenotazione']) {
					$movimento = array();
					$movimento['PrenotazioneId'] = $NuovaPrenotazioneId;
					$movimento['PagamentoTipoId'] = 7;
					$movimento['Data'] = date("Y-m-d");
					$movimento['Supplemento'] = 0;
					$movimento['DataPagamento'] = 'NULL';
					$movimento['ImportoPagato'] = 0;
					$movimento['Scadenza'] = 'NULL';
					$movimento['CodicePagamento'] = 'NULL';
					$movimento['CanalePagamentoId'] = 'NULL';
					$movimento['Importo'] = $totaliImporti['TotaleResiduo'];
					$differenza = $totaliImporti['TotaleDaPagare'] - $totaliImportiOld['TotalePagato'];
					if (($prenotazione->DatiGenerali['PrenotazioneStato'] == 3)) {

						if ($differenza >= 0) {
							if ($differenza > 0) {
								$movimento['TipoMovimento'] = "P";
								$movimento['Causale'] = "Richiesta del residuo";
								$movimento = $storico->operazioni_insert($movimento, $user);
								$movimento['Importo'] = $differenza;
								$db->insert("RT_PrenotazioneMovimento", $movimento);
							}
							$dup = array();
							$dup['ABordo'] = 1;
							$dup['TotalePagato'] = $totaliImportiOld['TotalePagato'];
							$dup['TotaleResiduo'] = $differenza;
							$result = $db->update("RT_Prenotazione", $dup, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId");
							$modifica = 2;
						}
					} else {

						$sqlCount = "SELECT COUNT(*) as cnt FROM RT_PrenotazioneMovimento WHERE PrenotazioneId = " . $NuovaPrenotazioneId;
						$rowCount = $db->query_first($sqlCount);
						if ($rowCount['cnt'] > 0) {
							$movimento['TipoMovimento'] = "I";
							$movimento['Causale'] = "Rettifica nuovo importo";
							$movimento = $storico->operazioni_insert($movimento, $user);
							$db->insert("RT_PrenotazioneMovimento", $movimento);
						}

						
						// aggiorno la prenotazione impostando pagamento a bordo
						$dup = array();
						$dup['ABordo'] = 1;
						$result = $db->update("RT_Prenotazione", $dup, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId");

						$modifica = 2;
					}
				} else if ($totaliImporti['TotalePrenotazione'] == $totaliImportiOld['TotalePrenotazione'] && $totaliImporti['TotalePrenotazione'] > 0) {
					$dup = array();
					if ($totaliImportiOld['TotaleResiduo'] == 0) {
						$dup['Pagato'] = 1;
					} else {
						$dup['Pagato'] = 0;
					}
					$dup['TotalePagato'] = $totaliImportiOld['TotalePagato'];
					$dup['TotaleResiduo'] = $totaliImportiOld['TotaleResiduo'];
					$result = $db->update("RT_Prenotazione", $dup, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId");
				}
			}
			/** fine della gestione dei movimenti in caso di modifica**/

			/** controllo emissione biglietti rimborsati in caso di modifica**/
			// controllo la presenza di biglietti rimborsati e copio nella nuova prenotazione
			if ($modifica >= 1) {
				// recupero le informazioni per il numero di righe da rimborsare
				$sql = "SELECT count(*) Numero, pd.Nome, pd.Cognome, pd.SessoId, pd.Eta, pd.Tragitto, pn.PasseggeroId
						FROM RT_PrenotazioneDettaglio pd INNER JOIN RT_PrenotazioneNumero pn ON (pd.PrenotazioneNumero = pn.PrenotazioneNumeroId) WHERE pd.PrenotazioneId = $old_prenotazioneid AND pd.Escludi = 1 AND pd.Rimborso = 1
						GROUP BY pn.PasseggeroId";
				$rimborsate = $db->fetch_array($sql);

				foreach ($rimborsate as $rimborsata) {
					$sql = "SELECT pn.PasseggeroId, TipologiaBigliettoId FROM RT_PrenotazioneDettaglio pd INNER JOIN RT_PrenotazioneNumero pn ON (pd.PrenotazioneNumero = pn.PrenotazioneNumeroId) 
					WHERE pd.PrenotazioneId = $NuovaPrenotazioneId AND Nome = '" . $rimborsata['Nome'] . "' AND Cognome = '" . $rimborsata['Cognome'] . "' AND SessoId = '" . $rimborsata['SessoId'] . "' AND Eta = '" . $rimborsata['Eta'] . "' AND Escludi = 0 AND Rimborso = 0";
					$passeggero = $db->query_first($sql);

					// aggiorno prenotazione dettaglio e imposto escludi = 1
					$sql = "UPDATE RT_PrenotazioneDettaglio pd
							LEFT JOIN
       	 					RT_PrenotazioneNumero pn
							ON pd.PrenotazioneNumero = pn.PrenotazioneNumeroId
							SET pd.Escludi = 1
							WHERE pn.PasseggeroId = " . $passeggero['PasseggeroId'];
					if ($rimborsata['Numero'] == 2) {
						$sql .= " AND (pd.Tragitto = 'Andata' OR pd.Tragitto = 'Ritorno')";
					} else {
						$sql .= " AND pd.Tragitto = '" . $rimborsata['Tragitto'] . "'";
					}
					$db->query($sql);

					// recupero i percorsi andata e ritorno
					$percorsi = array();
					if ($rimborsata['Numero'] == 2) {
						$sql = "SELECT DISTINCT pp.PrenotazionePercorsoId, pd.DataInizioItinerario, pd.CorsaId, pd.Tragitto FROM RT_PrenotazioneDettaglio pd
						INNER JOIN RT_PrenotazioneNumero pn ON (pd.PrenotazioneNumero = pn.PrenotazioneNumeroId)
						INNER JOIN RT_PrenotazionePercorso pp ON (pd.PrenotazioneId = pp.PrenotazioneId)
						WHERE pd.CorsaId = pp.CorsaId AND pd.DataInizioItinerario = pp.CorsaDataPartenza
						AND pn.PasseggeroId = " . $passeggero['PasseggeroId'];
					} else {
						$sql = "SELECT DISTINCT pp.PrenotazionePercorsoId, pd.DataInizioItinerario, pd.CorsaId, pd.Tragitto FROM RT_PrenotazioneDettaglio pd
						INNER JOIN RT_PrenotazioneNumero pn ON (pd.PrenotazioneNumero = pn.PrenotazioneNumeroId)
						INNER JOIN RT_PrenotazionePercorso pp ON (pd.PrenotazioneId = pp.PrenotazioneId)
						WHERE pd.CorsaId = pp.CorsaId AND pd.DataInizioItinerario = pp.CorsaDataPartenza
						AND pn.PasseggeroId = " . $passeggero['PasseggeroId'] . " AND pd.Tragitto = '" . $rimborsata['Tragitto']  . "'";
					}
					$percorsi = $db->fetch_array($sql);

					// aggiunge il prenotazione numero
					$prenotazioneNumeroData = null;
					$prenotazioneNumeroData['PrenotazioneId'] = $NuovaPrenotazioneId;
					$prenotazioneNumeroData['TipologiaBigliettoId'] = $passeggero['TipologiaBigliettoId'];
					$prenotazioneNumeroData['PasseggeroId'] = $passeggero['PasseggeroId'];
					$prenotazioneNumeroData = $storico->operazioni_insert($prenotazioneNumeroData, $user);
					$NuovaPrenotazioneNumero = $db->insert("RT_PrenotazioneNumero", $prenotazioneNumeroData);

					// aggiorna gli esclusi in prenotazione percorso
					foreach ($percorsi as $percorso) {
						$DataInizio = $percorso['DataInizioItinerario'];
						$CorsaId = $percorso['CorsaId'];

						$sql = "SELECT PrenotazionePercorsoId, PasseggeriEsclusi FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $NuovaPrenotazioneId AND CorsaDataPartenza = '$DataInizio' AND CorsaId = $CorsaId";
						$row1 = $db->query_first($sql);
						$esclusi = $row1['PasseggeriEsclusi'] + 1;

						$prenotazionePercorsoData = null;
						$prenotazionePercorsoData['PasseggeriEsclusi'] = $esclusi;
						$prenotazionePercorsoData = $storico->operazioni_update($prenotazionePercorsoData, $user);
						$db->update("RT_PrenotazionePercorso", $prenotazionePercorsoData, "PrenotazionePercorsoId = " . $percorso['PrenotazionePercorsoId']);
					}

					// aggiungo le righe in prenotazione dettaglio dei rimborsi
					$sql = "SELECT pd.*
							FROM RT_PrenotazioneDettaglio pd INNER JOIN RT_PrenotazioneNumero pn ON (pd.PrenotazioneNumero = pn.PrenotazioneNumeroId) 
							WHERE pd.PrenotazioneId = $old_prenotazioneid AND pd.Escludi = 1 AND pd.Rimborso = 1 AND pn.PasseggeroId = " . $rimborsata['PasseggeroId'];
					$rimborsateDuplicare = $db->fetch_array($sql);

					foreach ($rimborsateDuplicare as $duplicare) {
						unset($duplicare['PrenotazioneDettaglioId']);
						$duplicare['PrenotazioneId'] = $NuovaPrenotazioneId;
						$duplicare['Rimborso'] = 1;
						$duplicare['Escludi'] = 1;
						$duplicare['PrenotazioneNumero'] = $NuovaPrenotazioneNumero;
						$duplicare = $storico->operazioni_insert($duplicare, $user);
						$db->insert("RT_PrenotazioneDettaglio", $duplicare);
					}
				}
			}
			/** fine controllo emissione biglietti rimborsati in caso di modifica**/

			if ($modifica >= 1) {
				//per sicurezza
				$dataT['Stato'] = 1;
				$dataT['Cancella'] = 0;
				$result = $db->update("RT_Prenotazione", $dataT, "PrenotazioneId=" . $NuovaPrenotazioneId);
			}

			/**controllo importo titoli di viaggio se emessi e in fase di modifica**/
			/*if ($modifica >= 1) {
				//se ci sono titolo gia emessi controllo
				if($prenotazione->DatiGenerali['PrenotazioneStato'] == 3) {
					$sql = "SELECT sum(ImportoTitolo) as ImportoTitolo, count(*) as NumTitoli FROM RT_PrenotazioneTitolo where TipoTitolo = 'E' and PrenotazioneId = ".$NuovaPrenotazioneId;
					$titoliImporto = $db->query_first($sql);
					if($titoliImporto['ImportoTitolo'] <= $prenotazione->DatiGenerali['TotaleDaPagare']){
						$sql = "SELECT t.PrenotazioneTitoloId, t.ImportoTitolo, b.PrezzoTotalePax  FROM RT_PrenotazioneTitolo t
						left join RT_PrenotazioneNumero p on (t.PrenotazioneNumeroId = p.PrenotazioneNumeroId and t.PrenotazioneId = p.PrenotazioneId)
						left join RT_PrenotazioneBiglietto b on (p.PrenotazioneId = b.PrenotazioneId and t.TipologiaBiglietto =b.TipologiaBiglietto)
						where t.TipoTitolo = 'E' and t.PrenotazioneId = ".$NuovaPrenotazioneId;
						$titoliVerificare = $db->fetch_array($sql);
						foreach ($titoliVerificare as $t){
							if($t['ImportoTitolo'] < $t['PrezzoTotalePax']){
								//aggiorno l'importo del titolo
								$sql = "UPDATE RT_PrenotazioneTitolo SET ImportoTitolo = ".$t['PrezzoTotalePax']." where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
								$db->query($sql);

								//aggiorno le provvigioni
								$sql = "SELECT * FROM RT_PrenotazioneTitoloProvvigione where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
								$tempProviggioni = $db->fetch_array($sql);
								foreach ($tempProviggioni as $prov){
									$importoAgenzia = number_format ($t['PrezzoTotalePax']*$prov['PercentualeAgenzia']/100+$prov['FissoAgenzia'],2);
									$sql = "UPDATE RT_PrenotazioneTitoloProvvigione SET ImportoAgenzia = ".$importoAgenzia." where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
									$db->query($sql);
								}
								
								//aggiorno l'iva dal confine
								$sql = "SELECT * FROM RT_PrenotazioneTitoloIva where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
								$tempIva = $db->fetch_array($sql);
								$sql = "select * from RT_ViewImportiPerIva003 where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
								$scorporoR = $db->query_first($sql);
								if (!is_null($scorporoR['ScorporoP'])){
									$Scorporo = $scorporoR['ScorporoP'];
								} else {
									$Scorporo = $scorporoR['ScorporoD'];
								} 
								foreach ($tempIva as $iva){
									$TotImponibile = number_format (($t['PrezzoTotalePax']/count($tempIva)*$iva['KmPercorsiTerritorio'])/$iva['KmPercorsiTotale'],2);
									$TotIva = number_format ($TotImponibile-($TotImponibile/$Scorporo), 2);
									
									$sql = "UPDATE RT_PrenotazioneTitoloIva SET ImportoTitolo = ".$t['PrezzoTotalePax'].",
											ImportoTitoloPerConfine = $TotImponibile, ImportoIvaConfine = $TotIva
											where PrenotazioneTitoloId = ".$t['PrenotazioneTitoloId'];
									$db->query($sql);
								}
							}
						}
					}
				}
			}^/
			/**fine controllo importo titoli di viaggio se emessi e in fase di modifica**/

			/** posti su bus **/
			if ($modifica == 1) {
				$sql = "DELETE FROM RT_PrenotazioneTitoloPosto
					WHERE PrenotazioneId = $old_prenotazioneid ";
				$db->query($sql);
			}

			/* posti bus nodo per nodo */

			$sql = "SELECT * FROM RT_PrenotazionePercorso where PrenotazioneId = $NuovaPrenotazioneId";
			$tempPercorso = $db->fetch_array($sql);
			$postiPercorso = array();
			$percorsoCount = 0;
			foreach ($tempPercorso as $percoso) {
				$postiBus = array();
				$grafo = GraphUtil::interscambio($percoso['LineaId'], $percoso['CorsaId'], $percoso['CorsaDataPartenza'], $percoso['ComuneSalitaId'], $percoso['ComuneDiscesaId'], $db);
				$flotta = array();
				if (count($grafo->flotta) > 1) {
					$trovatoComune = false;
					foreach ($grafo->flotta[0]->comuni as $k => $comune) {
						if ($comune['comune'] == $percoso['ComuneSalitaId']) {
							$trovatoComune = true;
						}
					}
					if ($trovatoComune) {
						$flotta = $grafo->flotta;
					} else {
						$flotta[0] = $grafo->flotta[1];
						$flotta[1] = $grafo->flotta[0];
					}
				} else {
					$flotta = $grafo->flotta;
				}
				if (!is_array($flotta)) {
					$flotta = array();
				}
				foreach ($flotta as $bus) {
					$inizioBus = false;
					$trattaId = $bus->trattaId;
					//controllo trattaprincipale
					$sql = "SELECT * FROM RT_Tratta WHERE TrattaId = " . $trattaId;
					$tempTratta = $db->query_first($sql);
					if (isset($tempTratta['TrattaPrincipaleId'])) {
						$trattaId = $tempTratta['TrattaPrincipaleId'];
					}
					foreach ($bus->comuni as $k => $comune) {
						if (isset($comune['passeggeri']) && count($comune['passeggeri']) > 0) {
							$tempInterscambio = array();
							$tempInterscambio['isInterscambio'] = 0;
							$tempInterscambio['TrattaId'] = $trattaId;

							if ($inizioBus == false) {
								//salita del passeggero
								$tempInterscambio['comuneId'] = $bus->comuni[$k]['comune'];
								$inizioBus = true;
								$tempInterscambio['isInterscambio'] = 1;
								if (!isset($bus->comuni[$k + 1]['passeggeri']) || (isset($bus->comuni[$k + 1]['passeggeri']) && count($bus->comuni[$k + 1]['passeggeri']) == 0)) {
									if ($percoso['ComuneDiscesaId'] != $bus->comuni[$k + 1]['comune']) {
										// discesa del passeggero per interscambio lungo la tratta
										$nextInterscambio['comuneId'] = $bus->comuni[$k + 1]['comune'];
										$nextInterscambio['isInterscambio'] = 2;
									} else {
										// discesa del passeggero a destinazione
										$nextInterscambio['comuneId'] = $bus->comuni[$k + 1]['comune'];
										$nextInterscambio['isInterscambio'] = 3;
									}
								}
							} else {
								if (!isset($bus->comuni[$k + 1])) {
									// discesa del passeggero per interscambio a fine tratta
									$tempInterscambio['comuneId'] = $bus->comuni[$k]['comune'];
									$tempInterscambio['isInterscambio'] = 2;
								} else {
									$tempInterscambio['comuneId'] = $bus->comuni[$k]['comune'];
									$tempInterscambio['isInterscambio'] = 0;

									if (!isset($bus->comuni[$k + 1]['passeggeri']) || (isset($bus->comuni[$k + 1]['passeggeri']) && count($bus->comuni[$k + 1]['passeggeri']) == 0)) {
										if ($percoso['ComuneDiscesaId'] != $bus->comuni[$k + 1]['comune']) {
											// discesa del passeggero per interscambio lungo la tratta
											$nextInterscambio['comuneId'] = $bus->comuni[$k + 1]['comune'];
											$nextInterscambio['isInterscambio'] = 2;
										} else {
											// discesa del passeggero a destinazione
											$nextInterscambio['comuneId'] = $bus->comuni[$k + 1]['comune'];
											$nextInterscambio['isInterscambio'] = 3;
										}
									}
								}
							}
							$postiBus[] = $tempInterscambio;
							if (isset($nextInterscambio)) {
								$nextInterscambio['TrattaId'] = $trattaId;
								$postiBus[] = $nextInterscambio;
								unset($nextInterscambio);
							}
						}
					}
				}
				if (count($postiBus) > 0) {
					foreach ($postiBus as $temp) {
						$sql = "SELECT c.Comune, f.ComuneId, o.Orario, o.GiorniAggiuntivi FROM RT_Orario o
							LEFT JOIN RT_Fermata f ON f.FermataId = o.FermataId
							LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
							LEFT JOIN Comune c ON c.ComuneId =f.ComuneId
							WHERE f.ComuneId = " . $temp['comuneId'] . "
							AND o.Orario IS NOT NULL AND o.GiorniAggiuntivi IS NOT NULL
							AND t.LineaId = " . $percoso['LineaId'] . "
							AND o.CorsaId = " . $percoso['CorsaId'] . "
							AND f.Cancella = 0
							and o.Cancella = 0
							and t.cancella = 0
							GROUP BY f.ComuneId
							ORDER BY o.GiorniAggiuntivi ASC, o.Orario ASC";
						$rowI = $db->query_first($sql);
						if (isset($rowI['ComuneId'])) {
							$rowI['Interscambio'] = $temp['isInterscambio'];
							$rowI['TrattaId'] = $temp['TrattaId'];
							$rowI['CorsaId'] = $percoso['CorsaId'];
							$rowI['CorsaDataPartenza'] = $percoso['CorsaDataPartenza'];
							$postiPercorso[$percorsoCount][] = $rowI;
						}
					}
				}
				$percorsoCount++;
			}
			/*fine posti bus*/


			/*inserimento posti bus*/
			if (isset($_POST['postiA'])) {
				$posti = $_POST['postiA'];
				$numeroPosti = array();
				foreach ($posti as $key => $value) {
					if ($value == 1) {
						$temp = explode("_", $key);
						$sql = "SELECT * FROM RT_TipologiaBusDettaglioPosto p
			             			left join RT_Corsa c on c.TipologiaBusDefaultId = p.TipologiaBusId
									WHERE p.Riga = $temp[0] AND p.Colonna = $temp[1] AND c.CorsaId = $CorsaAndata";
						$rowTemp = $db->query_first($sql);
						$numeroPosti[] = $rowTemp['NumeroPosto'];
					}
				}
				$iiP = 0;
			}
			if (isset($_POST['postiR'])) {
				$postiR = $_POST['postiR'];
				$numeroPostiR = array();
				foreach ($postiR as $key => $value) {
					if ($value == 1) {
						$temp = explode("_", $key);
						$sql = "SELECT * FROM RT_TipologiaBusDettaglioPosto p
						left join RT_Corsa c on c.TipologiaBusDefaultId = p.TipologiaBusId
						WHERE p.Riga = $temp[0] AND p.Colonna = $temp[1] AND c.CorsaId = $CorsaRitorno";
						$rowTemp = $db->query_first($sql);
						$numeroPostiR[] = $rowTemp['NumeroPosto'];
					}
				}
				$iiPR = 0;
			}
			$sql = "SELECT * FROM RT_PrenotazioneNumero n
			left join RT_TipologiaBiglietto b on n.TipologiaBigliettoId = b.TipologiaBigliettoId
			where n.PrenotazioneId=$NuovaPrenotazioneId 
			order by n.PasseggeroId asc";
			$ArrObjectP = $db->fetch_array($sql);
			foreach ($ArrObjectP as $tempTipo) {
				if ($tempTipo['OccupaPosto'] == 1) {
					foreach ($postiPercorso as $id => $p) {
						if ($id == 0) {
							$tempTipoViaggio = 'A';
						} else {
							$tempTipoViaggio = 'R';
						}
						foreach ($p as $k => $fermate) {
							$fermate['PrenotazioneId'] = $NuovaPrenotazioneId;
							$fermate['PrenotazioneNumeroId'] = $tempTipo['PrenotazioneNumeroId'];
							$fermate['PrenotazioneTitoloId'] = null;
							$fermate['TipoViaggio'] = $tempTipoViaggio;
							$fermate['Ordine'] = $k;
							if ($id == 0) {
								if (!isset($_POST['postiA']) || $k != 0) {
									$numeroBiglietto = GraphUtil::getNumeroBiglietto(
										null,
										$k,
										$tempTipoViaggio,
										$fermate['CorsaId'],
										$fermate['CorsaDataPartenza'],
										$fermate['TrattaId'],
										$fermate['ComuneId'],
										$fermate['Interscambio'],
										$db,
										$tempTipo['PrenotazioneNumeroId']
									);
								} else {
									$numeroBiglietto['NumeroBus'] = 1;
									$numeroBiglietto['NumeroPosto'] = $numeroPosti[$iiP];
									$iiP++;
								}
							} else {
								if (!isset($_POST['postiR']) || $k != 0) {
									$numeroBiglietto = GraphUtil::getNumeroBiglietto(
										null,
										$k,
										$tempTipoViaggio,
										$fermate['CorsaId'],
										$fermate['CorsaDataPartenza'],
										$fermate['TrattaId'],
										$fermate['ComuneId'],
										$fermate['Interscambio'],
										$db,
										$tempTipo['PrenotazioneNumeroId']
									);
								} else {
									$numeroBiglietto['NumeroBus'] = 1;
									$numeroBiglietto['NumeroPosto'] = $numeroPostiR[$iiPR];
									$iiPR++;
								}
							}

							$fermate['NumeroBus'] = $numeroBiglietto['NumeroBus'];
							$fermate['NumeroPosto'] = $numeroBiglietto['NumeroPosto'];
							$db->insert("RT_PrenotazioneTitoloPosto", $fermate);
						}
					}
				}
			}
			/*fine inserimento posti bus*/


			/**controllo disponibilita posti**/
			aggiornaDisponibilita($CorsaAndata, $DataSelezionataA);

			if ($CorsaRitorno > 0) {
				aggiornaDisponibilita($CorsaRitorno, $DataSelezionataR);
			}
			/**fine controllo disponibilita posti**/
			
			/***inizio blocca corse stessa barca stesso orario ***/
			if($TipoTour == 1) {
				$sqlCorsa = "select * from RT_Corsa where CorsaId = ".$CorsaAndata;
				$rowCorsa = $db->query_first($sqlCorsa);
				$sql = "select 
							appcal.AppCalendarioData, c.CorsaId 
						from
							`RT_Corsa` c
							join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
							join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
							join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
							join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
						where appcal.AppCalendarioData >= DATE(NOW())
							and c.Stato = 1 AND c.Cancella = 0
							and RT_Linea.TipoTour = 1
							and c.TipologiaBusDefaultId = ".$rowCorsa['TipologiaBusDefaultId']."
							and appcal.AppCalendarioData = '$DataSelezionataA'
							and (c.OrarioPartenza >= '".$rowCorsa['OrarioPartenza']."' or c.OrarioArrivo <= '".$rowCorsa['OrarioArrivo']."')
							and c.CorsaId <> $CorsaAndata";
				
				$corseBloccare = $db->fetch_array($sql);
				foreach($corseBloccare as $c){
					$sql = "select * from RT_CorsaBloccoWeb where CorsaId = ".$c['CorsaId']." AND DataPartenza = '".$c['AppCalendarioData']."'";
					
					$rowTemp = $db->query_first($sql);
					if(!isset($rowTemp['CorsaId'])) {
						$data = [
							'CorsaId' => $c['CorsaId'],
							'DataPartenza' => $c['AppCalendarioData'],
						];
						$data = $storico->operazioni_insert($data, $user);
						$db->insert("RT_CorsaBloccoWeb", $data);
						$db->insert("RT_CorsaBlocco", $data);
					}
				}
				if($modifica == 1 && ($OldCorsaPartenzaA != $CorsaAndata || $OldDataPartenzaA != $DataSelezionataA) ) {
					$sqlCorsa = "select * from RT_Corsa where CorsaId = ".$OldCorsaPartenzaA;
					$rowCorsa = $db->query_first($sqlCorsa);
					$sql = "select 
								appcal.AppCalendarioData, c.CorsaId 
							from
								`RT_Corsa` c
								join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
								join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
								join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
								join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
							where appcal.AppCalendarioData >= DATE(NOW())
								and c.Stato = 1 AND c.Cancella = 0
								and RT_Linea.TipoTour = 1
								and c.TipologiaBusDefaultId = ".$rowCorsa['TipologiaBusDefaultId']."
								and appcal.AppCalendarioData = '$OldDataPartenzaA'
								and (c.OrarioPartenza >= '".$rowCorsa['OrarioPartenza']."' or c.OrarioArrivo <= '".$rowCorsa['OrarioArrivo']."')
								and c.CorsaId <> $OldCorsaPartenzaA";
					$corseSbloccare = $db->fetch_array($sql);
					foreach($corseSbloccare as $c){
						$db->query("DELETE FROM RT_CorsaBloccoWeb WHERE CorsaId = ".$c['CorsaId']." AND DataPartenza = '".$c['AppCalendarioData']."'");
						$db->query("DELETE FROM RT_CorsaBlocco WHERE CorsaId = ".$c['CorsaId']." AND DataPartenza = '".$c['AppCalendarioData']."'");
					}
				}
			}
			/***fine blocca corse stessa barca stesso orario***/
			

			/** inserimento Google People **/
			$servicePeople = new ServiceGooglePeople($db);
			$servicePeople->insertPeople($principaleNome, $principaleCognome, $data_prenotazione['ClienteEmail'], $data_prenotazione['ClienteCellularePrefisso'] . $data_prenotazione['ClienteCellulareFamiliare']);
			/** FINE inserimento Google People **/

			if ((isset($data_prenotazione['Multi']) && $data_prenotazione['Multi'] == 1)) {
				echo ("ok" . '_' . $modifica . '_' . $NuovaPrenotazioneId . '_' . $data_prenotazione['Multi']);
			} else {
				if (($abilitazioneprenotazione == 1) or ((isset($_POST['Emetti'])) and ($_POST['Emetti'] != '')) and ($daconfermare == 0)) // emissione ticket obbligatorio
					echo ("E@" . $NuovaPrenotazioneId);
				else
					echo ("ok" . '_' . $modifica . '_' . $NuovaPrenotazioneId . '_' . $data_prenotazione['Multi']);
			}
			$prenotazione_wizard = new Prenotazione($NuovaPrenotazioneId);
			$_SESSION['PRENOTAZIONE_WIZARD'] = serialize($prenotazione_wizard);

			//se multi prenotazione
			if (!$modifica && $data_prenotazione['Multi']) {
				if (!isset($_SESSION['PRENOTAZIONE_MULTI'])) {
					$prenotazione_multi = array();
				} else {
					$prenotazione_multi = $_SESSION['PRENOTAZIONE_MULTI'];
				}

				$data_prenotazione['PrenotazioneId'] = $lastidA;
				$prenotazione_multi[] = $data_prenotazione;
				$_SESSION['PRENOTAZIONE_MULTI'] = $prenotazione_multi;
			} else {
				unset($prenotazione_multi);
				unset($_SESSION['PRENOTAZIONE_MULTI']);
			}
		} else
			echo ("no1");
		exit();
	} else {
		echo ("no2");
		exit();
	}
	exit();
}

function aggiornaDisponibilita($corsaId, $data)
{
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

	$corsa = $db->query_first($sql);


	$sql = "DELETE FROM RT_DisponibilitaPostiCron WHERE LineaId = " . $corsa['LineaId'] . " and CorsaId = " . $corsaId . " and DataPartenza = '" . $data . "'";
	$db->query($sql);

	$grafo = new DisponibilitaGraph($corsa['LineaId'], $corsaId, $data, $db);
	foreach ($grafo->graph->nodes as $k => $pickup) {
		$sql = "select t.TrattaId, t.TrattaPeso, KmInizioTratta from RT_Fermata f
				left join RT_Tratta t on t.TrattaId = f.TrattaId
	 			left join RT_Orario o on o.FermataId = f.FermataId
				where t.LineaId = " . $corsa['LineaId'] . " and f.ComuneId = $k
					and f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
					and o.CorsaId = $corsaId and o.Orario is not null and o.Stato = 1 and o.Cancella = 0
					order by TrattaPeso asc, f.ImportanzaTratta desc";
		$tempRow = $db->query_first($sql);
		if (isset($grafo->gruppiDispo[$k])) {
			$sql = "INSERT INTO RT_DisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti, TrattaId, PesoTratta, KmInizioTratta) VALUES
					(" . $corsa['LineaId'] . "," . $corsaId . ", '" . $data . "'," . $k . ",
							" . $grafo->gruppiDispo[$k]->totalePasseggeri . ",  " . $tempRow['TrattaId'] . ", " . $tempRow['TrattaPeso'] . ", " . $tempRow['KmInizioTratta'] . ")";
			$db->query($sql);
		} else {
			$tot = 0;
			$sql = "INSERT INTO RT_DisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti, TrattaId, PesoTratta, KmInizioTratta) VALUES
					(" . $corsa['LineaId'] . "," . $corsaId . ", '" . $data . "'," . $k . ",
						$tot, " . $tempRow['TrattaId'] . ", " . $tempRow['TrattaPeso'] . ", " . $tempRow['KmInizioTratta'] . ")";
			$db->query($sql);
		}
	}


	$totPostiBus = $corsa['PostiTotali'];
	$postiRP = $corsa['PostiRealmentePrenotati'];
	$tot1 = 0;
	$sql = "DELETE FROM RT_MaxDisponibilitaPostiCron WHERE LineaId = " . $corsa['LineaId'] . " and CorsaId = " . $corsa['CorsaId'] . " and DataPartenza = '" . $corsa['AppCalendarioData'] . "'";
	$r = $db->query($sql);


	foreach ($grafo->graph->nodes as $k => $pickup) {
		if (isset($grafo->gruppiDispo[$k])) {
			$tot = $grafo->gruppiDispo[$k]->totalePasseggeri;
			if ($tot > $tot1)
				$tot1 = $tot;
			// 			$tot = $grafo->gruppiDispo[$k]->totalePasseggeri + $grafo->graph->nodes[$k]->salite;


		}
	}

	if ($tot1 > 0) {
		$sql = "select * from RT_DisponibilitaPostiCron
			where Posti = $tot1 and CorsaId = " . $corsa['CorsaId'] . " and
        			DataPartenza = '" . $corsa['AppCalendarioData'] . "'
        	order by PesoTratta desc";
		$row2 =  $db->query_first($sql);
		$peso = $row2['PesoTratta'];

		$km = $row2['KmInizioTratta'];
		$trattaId = $row2['TrattaId'];
		if (isset($peso)) {
			$sql = "SELECT Max(Posti) as postiM, c.* FROM RT_DisponibilitaPostiCron c
				where CorsaId = " . $corsa['CorsaId'] . " and DataPartenza = '" . $corsa['AppCalendarioData'] . "'
					and PesoTratta = $peso
					and c.Posti > 0 and c.Posti<>" . $tot1 . " and TrattaId <> $trattaId group by TrattaId
					order by postiM desc";

			$row3 = $db->fetch_array($sql);
			$postiOccupatiTratta = 0;

			foreach ($row3 as $num => $val) {
				if ($val['postiM'] < $tot1 / 2) {
					$sql = "SELECT Posti, KmInizioTratta FROM RT_DisponibilitaPostiCron c
							where CorsaId = " . $corsa['CorsaId'] . " and DataPartenza = '" . $corsa['AppCalendarioData'] . "'
							and TrattaId  = " . $val['TrattaId'] . "
							and c.Posti = " . $val['postiM'] . "
							group by TrattaId";

					$row4 = $db->fetch_array($sql);

					if ($row4[0]['KmInizioTratta'] > $km - 50 && $row4[0]['KmInizioTratta'] < $km + 50)
						$postiOccupatiTratta += $row4[0]['Posti'];
				}
			}

			$tot1 += $postiOccupatiTratta;
		}

		$sql = "INSERT INTO RT_MaxDisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti,PostiRP) VALUES
					(" . $corsa['LineaId'] . "," . $corsa['CorsaId'] . ", '" . $corsa['AppCalendarioData'] . "'," . $k . ",
							" . $tot1 . "," . $postiRP . " )";
		$db->query($sql);
	}
}

//azione per passare lo stato della prenotazione da confermare a confermata
function ConfermaPrenotazione()
{
	// Recupera l'ID della prenotazione dalla richiesta
	$PrenotazioneId = $_REQUEST['PrenotazioneId'];
	global $user, $prenotazione_wizard;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	// Aggiorna lo stato della prenotazione a "confermata" (stato 1)
	$data['PrenotazioneStato'] = 1;
	$data = $storico->operazioni_update($data, $user);

	// Aggiorna la tabella delle prenotazioni con il nuovo stato
	$result = $db->update("RT_Prenotazione", $data, "PrenotazioneId=" . $PrenotazioneId . " and OdcIdRef=$user->OdcId");

	// Aggiorna anche la tabella dei percorsi associati alla prenotazione
	$result = $db->update("RT_PrenotazionePercorso", $data, "PrenotazioneId=" . $PrenotazioneId . " and OdcIdRef=$user->OdcId");

	// Restituisce una risposta di successo
	echo ("ok");
	exit();
}

function ConfermaPrenotazioneLista()
{
	$PrenotazioneId = $_REQUEST['PrenotazioneId'];
	global $user, $prenotazione_wizard;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	$dt = new DT();

	// verifica posti disponibili
	$pre = new Prenotazione($PrenotazioneId);
	$pre->conn = $db;
	$pre->inizializzaDatiGenerali();
	$arr_pre = $pre->DatiGenerali;
	$NumeroPax = $arr_pre['TotalePaxPrenotati'];

	$pre->inizializzaDatiGeneraliPercorso('A');
	$arr_old_dati_percorso_a = $pre->DatiGeneraliPercorso;
	$oldTipoViaggio = $arr_pre['TipoViaggioId'];
	$DataPartenza = $arr_old_dati_percorso_a['CorsaDataPartenza'];
	// print_r($arr_old_dati_percorso_a);
	$CorsaIdPartenza = $arr_old_dati_percorso_a['CorsaId'];
	$LineaNome = $arr_old_dati_percorso_a['LineaNome'];
	$lineaId = $arr_old_dati_percorso_a['LineaId'];
	$CorsaNome = $arr_old_dati_percorso_a['CorsaNome'];
	$ComuneSalitaId = $arr_old_dati_percorso_a['ComuneSalitaId'];
	$ComuneDiscesaId = $arr_old_dati_percorso_a['ComuneDiscesaId'];
	$l = $LineaNome . " - ($CorsaNome)";
	$sql = "select PostiRealmenteDisponibili,CorsaId, PostiTotali from RT_ViewOperativitaBlocchi where OdcIdRef=$user->OdcId and CorsaId=$CorsaIdPartenza and AppCalendarioData='$DataPartenza'";
	$DataPartenza1 = $dt->format($DataPartenza, "Y-m-d", "d/m/Y");
	$row = $db->query_first($sql);
	$posti_disponibili = 0;
	if ($row['CorsaId'] > 0)
		$posti_disponibili = $row['PostiRealmenteDisponibili'] + $NumeroPax;
	$errore_posti = 0;
	if ($NumeroPax > $posti_disponibili) {
		$grafo1 = new DisponibilitaGraph($lineaId, $CorsaIdPartenza, $DataPartenza, $db, 200, false);
		$p = $grafo1->getPostiDisponibili($ComuneSalitaId, $ComuneDiscesaId, $row['PostiTotali']);
		if ($p == 10000) {
			$p = $row['PostiTotali'];
		}
		$postiDisponibiliGrafo = $row['PostiTotali'] - $p + $NumeroPax;
		if ($NumeroPax > $postiDisponibiliGrafo) {
			$errore_posti = 1;
			$posti = $NumeroPax - $postiDisponibiliGrafo;
			echo ("\nPer poter validare la prenotazione e' necessario aggiungere alla corsa $l del $DataPartenza1 . n. " . $posti . " posti");
		}
	}

	if ($oldTipoViaggio == 2) {
		$pre->inizializzaDatiGeneraliPercorso('R');
		$arr_old_dati_percorso_a = $pre->DatiGeneraliPercorso;
		$oldTipoViaggio = $arr_pre['TipoViaggioId'];
		$DataPartenza = $arr_old_dati_percorso_a['CorsaDataPartenza'];
		$DataPartenza1 = $dt->format($DataPartenza, "Y-m-d", "d/m/Y");
		$CorsaIdPartenza = $arr_old_dati_percorso_a['CorsaId'];
		$LineaNome = $arr_old_dati_percorso_a['LineaNome'];
		$lineaId = $arr_old_dati_percorso_a['LineaId'];
		$CorsaNome = $arr_old_dati_percorso_a['CorsaNome'];
		$ComuneSalitaId = $arr_old_dati_percorso_a['ComuneSalitaId'];
		$ComuneDiscesaId = $arr_old_dati_percorso_a['ComuneDiscesaId'];
		$l = $LineaNome . " - ($CorsaNome)";
		$sql = "select PostiRealmenteDisponibili,CorsaId, PostiTotali from RT_ViewOperativitaBlocchi where OdcIdRef=$user->OdcId and CorsaId=$CorsaIdPartenza and AppCalendarioData='$DataPartenza'";
		// echo($sql);
		$row = $db->query_first($sql);
		$posti_disponibili = 0;
		if ($row['CorsaId'] > 0)
			$posti_disponibili = $row['PostiRealmenteDisponibili'];

		if ($NumeroPax > $posti_disponibili) {
			$grafo1 = new DisponibilitaGraph($lineaId, $CorsaIdPartenza, $DataPartenza, $db, 200, false);
			$p = $grafo1->getPostiDisponibili($ComuneSalitaId, $ComuneDiscesaId, $row['PostiTotali']);
			if ($p == 10000) {
				$p = $row['PostiTotali'];
			}
			$postiDisponibiliGrafo = $row['PostiTotali'] - $p + $NumeroPax;
			if ($NumeroPax > $postiDisponibiliGrafo) {
				$errore_posti = 1;
				$posti = $NumeroPax - $postiDisponibiliGrafo + $NumeroPax;
				echo ("\nPer poter validare la prenotazione è necessario aggiungere alla corsa $l del $DataPartenza1 . n. " . $posti . " posti");
			}
		}
	}
	if ($errore_posti == 1)
		exit();
	// exit();
	// verifica se ci sono tratte da confermare
	$sql = "SELECT * FROM 
		
		(SELECT RT_PrenotazioneTratta.PrenotazioneId AS PrenotazioneId, RT_Tratta.DaConfermare AS DaConfermare, RT_PrenotazioneTratta.OdcIdRef AS OdcIdRef
		FROM 
		RT_PrenotazioneTratta AS RT_PrenotazioneTratta
		JOIN RT_Tratta AS RT_Tratta ON (RT_PrenotazioneTratta.TrattaId = RT_Tratta.TrattaId)
		JOIN RT_Prenotazione AS RT_Prenotazione ON (RT_Prenotazione.PrenotazioneId = RT_PrenotazioneTratta.PrenotazioneId)
		WHERE
		RT_Tratta.DaConfermare = 1
		AND RT_Prenotazione.Stato = 1
		AND RT_Prenotazione.Cancella = 0) AS RT_CheckPrenotazioneDaConfermare
		 
	WHERE OdcIdRef = $user->OdcId AND PrenotazioneId = $PrenotazioneId";
	$row = $db->query_first($sql);

	if (!empty($row['PrenotazioneId'])) {
		$data = null;
		$data['PrenotazioneStato'] = 2;
		$res = $db->update("RT_Prenotazione", $data, "PrenotazioneId=$PrenotazioneId");
		$res1 = $db->update("RT_PrenotazionePercorso", $data, "PrenotazioneId=$PrenotazioneId");
		echo ("La prenotazione e' stata posta nello stato DA CONFERMARE in quanto la tratta scelta è soggetta a conferma. E' importante aggiungere posti alla corsa nella sezione gestione.");
		exit();
	} else {
		$data = null;
		$data['PrenotazioneStato'] = 1;
		$data = $storico->operazioni_update($data, $user);
		$res = $db->update("RT_Prenotazione", $data, "PrenotazioneId=$PrenotazioneId");
		$res1 = $db->update("RT_PrenotazionePercorso", $data, "PrenotazioneId=$PrenotazioneId");
		echo ("La prenotazione e' stata posta nello stato: CONFERMATA. E' importante aggiungere posti alla corsa nella sezione gestione.");
		exit();
	}
	exit();
}

function EmettiTitoliDiViaggio()
{
	global $db, $prenotazione_wizard, $user, $dizionario;
	$coupon = $_GET['coupon'];
	$movimentoId = $_GET['movimentoId'];

	$db = new Database();
	$db->connect();
	if (isset($coupon) && $coupon != '0') {
		$sql = "SELECT * FROM RT_Coupon where CouponNome like '%Rimborso%' and Codice = '" . $coupon . "'";
		$row = $db->query_first($sql);
		if (isset($row['CouponId'])) {
			$couponProvvigioni = true;
		} else {
			$couponProvvigioni = false;
		}
	} else {
		$couponProvvigioni = false;
	}
	$PrenotazioneId = $prenotazione_wizard->Id;
	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
	$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];
	$LineaId = $DatiGeneraliPercorsoArr['LineaId'];
	$dataOrarioSalita = $DatiGeneraliPercorsoArr['DataOraSalita'];
	$dataCorsaPartenza = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
	$orarioCorsaPartenza = $DatiGeneraliPercorsoArr['CorsaOrarioPartenza'];
	$comuneSalitaId = $DatiGeneraliPercorsoArr['ComuneSalitaId'];
	$comuneSalita = $DatiGeneraliPercorsoArr['ComuneSalita'];
	if (!$DatiGeneraliArr['Multi']) {
		$prenotazione_wizard->EmettiBiglietti(null, $couponProvvigioni);
	} else {
		$sql = "SELECT PrenotazioneId 
				FROM RT_Prenotazione 
				WHERE CodicePrenotazione = '" . $DatiGeneraliArr['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $DatiGeneraliArr['PrenotazioneStato'] . " AND Cancella = 0 AND OdcIdRef = " . $user->OdcId;

		$prenotazioni = $db->fetch_array($sql);
		foreach ($prenotazioni as $prenotazione) {
			$prenotazioneObj = new Prenotazione($prenotazione['PrenotazioneId']);
			$prenotazioneObj->conn = $db;
			$prenotazioneObj->EmettiBiglietti(null, $couponProvvigioni);
		}
	}

	$output = array();
	$output['prenotazioneId'] = $PrenotazioneId;
	$output['corsaId'] = $CorsaId;

	//controllo per invio messaggio all'autista
	//se la corsa e' gia' partita verifico se per l'orario fermata e' ancora in tempo
	$diff2 = 0;
	$dataPartenzaFermata = new DateTime($dataOrarioSalita);
	$dataPartenzaCorsa = new DateTime($dataCorsaPartenza . " " . $orarioCorsaPartenza);
	$dataOra = new DateTime();
	if ($dataOra > $dataPartenzaCorsa && $dataOra < $dataPartenzaFermata) {
		$tel = array();
		//la corsa � partita ma sono ancora in tempo a prendere il bus ed invio un messaggio all'autista
		$sql = "select * from RT_GestioneOttimizzataFlotta where LineaId = $LineaId and CorsaId = $CorsaId CorsaDataPartenza='$dataCorsaPartenza'";
		$rowTemp1 = $db->fetch_array($sql);
		if (count($rowTemp1) > 0) {
			//
			$sql = "SELECT BusPartenza FROM RT_GestioneOttimizzataNodo 
			where LineaId = $LineaId and CorsaId = $CorsaId and CorsaDataPartenza = '$dataCorsaPartenza' and Comune = $comuneSalitaId";
			$rowTemp2 = $db->query_first($sql);
			if (count($rowTemp2) > 0) {
				//il bus passa per la fermata ed avviso l'autista del bus
				$sql = "SELECT a1.Cellulare as autista1, a2.Cellulare as autista2 FROM RT_PreparazioneBusAutisti ba
						left join RT_Autisti a1 on a1.AutistiId = ba.Autista1
						left join RT_Autisti a2 on a2.AutistiId = ba.Autista2
						where ba.BusId = " . $rowTemp2['BusPartenza'];
				$rowTemp3 = $db->query_first($sql);
				if ($rowTemp3['autista1'] != "") {
					$tel[] = $rowTemp3['autista1'];
				}
				if ($rowTemp3['autista2'] != "") {
					$tel[] = $rowTemp3['autista2'];
				}
			} else {
				//nessun bus passa per quella fermata, avviso tutti gli autisti
				$sql = "SELECT a1.Cellulare as autista1, a2.Cellulare as autista2 FROM RT_PreparazioneBusAutisti ba
						left join RT_Autisti a1 on a1.AutistiId = ba.Autista1
						left join RT_Autisti a2 on a2.AutistiId = ba.Autista2
						where ba.DataPartenza = '$dataCorsaPartenza' and CorsaId = $CorsaId";
				$rowTemp3 = $db->fetch_array($sql);
				foreach ($rowTemp3 as $k => $v) {
					if ($v['autista1'] != "") {
						$tel[] = $v['autista1'];
					}
					if ($v['autista2'] != "") {
						$tel[] = $v['autista2'];
					}
				}
			}
		} else {
			//invio messaggio al numero di default
			$sql = "Select Cellulare from RT_Linea where LineaId = $LineaId";
			$rowTemp3 = $db->query_first($sql);
			if ($rowTemp3['Cellulare'] != "") {
				$tel[] = $rowTemp3['Cellulare'];
			}
		}

		$Sms = new Sms();
		$Sms->conn = $db;
		$Messaggio = "<![CDATA[" . $dizionario['generale']['sms_nuova'] . " " . $comuneSalita . " http://onebus.braincomputing.net/protected/modules/rt_biglietto/viewCorsa.php?CorsaId=" . $CorsaId . "&DataCorsa=" . $dataCorsaPartenza . "]]>";

		foreach ($tel as $t) {
			$Sms->SmsCliccaTelNoPrefisso($t, $Messaggio);
		}
	}

	if (Config::$notificaWhatsapp) {
		$whatsapp = new ServiceWhatsapp($db);
		$whatsapp->invioWhatsapp($PrenotazioneId, $user);
	}

	if (isset($movimentoId) && $movimentoId != '0') {
		$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
		$movimento = $db->query_first($sql);
		if($movimento['ScontrinoInvioAuto'] == 1) {
			fiscalGatewayEmettiRicevuta($movimentoId);
			$dataMovimento = array();
			$dataMovimento['ScontrinoNotifica'] = 1;
			$updInviaTitolo=$db->update('RT_PrenotazioneMovimento', $dataMovimento,"PrenotazioneMovimentoId = ".$movimentoId);
		}
	}

	if (Config::$salesmanago_enabled) {
		$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
		$prenotazione = $db->query_first($sql);

		$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $PrenotazioneId";
		$prenotazionePercorso = $db->query_first($sql);

		$sql = "SELECT * FROM RT_PrenotazioneBiglietto WHERE PrenotazioneId = $PrenotazioneId";
		$prenotazioneBiglietti = $db->fetch_array($sql);
		$descrizioneBiglietto = '';
		foreach ($prenotazioneBiglietti as $biglietto) {
			$descrizioneBiglietto .= $biglietto['NumeroPax']. " x " . $biglietto['TipologiaBiglietto'] . ', ';
		}
		$descrizioneBiglietto = rtrim($descrizioneBiglietto, ', ');

		$sql = "SELECT * FROM PrefissoTelefono WHERE Prefisso = '".$prenotazione['ClienteCellularePrefisso']."'";
		$prefisso = $db->query_first($sql);

		$sm = new ServiceSalesManago($db);
		$result = $sm->sendEvent($prenotazione['PrenotazioneId'], 
						ServiceSalesManago::EVENT_RESERVATION, 
						$prenotazione['ClienteEmail'], 
						$prenotazionePercorso['LineaNome'],
						$prenotazione['TotaleDaPagare'],
						$prenotazione['Lingua'],
						$prenotazione['TipoTour'] == 1 ? 'Tour Privato' : 'Tour Gruppo', 
						$descrizioneBiglietto,
							'+'.$prenotazione['ClienteCellularePrefisso'].$prenotazione['ClienteCellulare'],
							$prenotazione['ClienteNome'],
							$prefisso['Nazione'],
						null,
						'office'
						);
	}

	echo json_encode($output);
}



function EmettiTitoliExtra()
{
	global $db, $prenotazione_wizard, $user;

	$coupon = $_GET['coupon'];
	$movimentoId = $_GET['movimentoId'];

	$db = new Database();
	$db->connect();
	if (isset($coupon) && $coupon != '0') {
		$sql = "SELECT * FROM RT_Coupon where CouponNome like '%Rimborso%' and Codice = '" . $coupon . "'";
		$row = $db->query_first($sql);
		if (isset($row['CouponId'])) {
			$couponProvvigioni = true;
		} else {
			$couponProvvigioni = false;
		}
	} else {
		$couponProvvigioni = false;
	}

	$PrenotazioneId = $prenotazione_wizard->Id;
	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
	$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];

	$sql = "SELECT * FROM RT_PrenotazioneMovimento m 
		where  Prenotazioneid = $PrenotazioneId and TipoMovimento = 'I' 
		order by PrenotazioneMovimentoId desc;";
	$tempRoxMov = $db->query_first($sql);
	$totaleExtra = $tempRoxMov['ImportoPagato'];

	$prenotazione_wizard->conn = $db;
	if (!$DatiGeneraliArr['Multi']) {
		$prenotazione_wizard->EmettiBigliettiExtra($totaleExtra, $couponProvvigioni);
	} else {
		$sql = "SELECT PrenotazioneId FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $DatiGeneraliArr['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $DatiGeneraliArr['PrenotazioneStato'] . " AND Cancella = 0 AND OdcIdRef = " . $user->OdcId;
		$prenotazioni = $db->fetch_array($sql);

		foreach ($prenotazioni as $prenotazione) {
			$prenotazioneObj = new Prenotazione($prenotazione['PrenotazioneId']);
			$prenotazioneObj->conn = $db;
			$prenotazioneObj->EmettiBigliettiExtra($totaleExtra, $couponProvvigioni);
		}
	}

	$output = array();
	$output['prenotazioneId'] = $PrenotazioneId;
	$output['corsaId'] = $CorsaId;

	echo json_encode($output);
}

function getCorsaRitornoAperta()
{
	global $db, $prenotazione_wizard, $user;
	/**recupero dati**/
	if (isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
		$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
	}
	//corsa andata
	$CorsaAndataId = $_REQUEST['CorsaAndataId'];
	$corsaObj = new Corsa();
	$corsaObj->conn = $db;
	$ritorno = $corsaObj->getCorsRitornoAperto($CorsaAndataId);
	echo json_encode($ritorno);
}

function getCorseOpt()
{
	global $db, $prenotazione_wizard, $user;
	/**recupero dati**/
	if (isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
		$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
	}
	//data andata
	$DataFiltroA = $_REQUEST['DataFiltroA'];
	//data ritorno
	$DataFiltroR = $_REQUEST['DataFiltroR'];
	//tipo viaggio A=andata, R=ritorno
	$Tp = $_REQUEST['Tp'];
	//corsaId selezionata in caso di modifica
	$CorsaId_post = $_REQUEST['Corsaid'];
	//dataCorsa selezionata in caso di modifica
	$DataCorsa_post = $_REQUEST['DataCorsa'];
	//tipo tour 0=gruppo, 1=privato
	$TipoTour = $_REQUEST['TipoTour'];

	/*recupero dati se sto modificando/visualizzando una prenotazione*/
	$ComuneSalitaId_post = 0;
	$ComuneDiscesaId_post = 0;
	$CorsaPrenotata_post = 0;
	$DataPrenotata_post = null;
	if (is_object(($prenotazione_wizard))) {
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso($Tp);
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
		$ComuneSalitaId_post = $DatiGeneraliPercorsoArr['ComuneSalitaId'];
		$ComuneDiscesaId_post = $DatiGeneraliPercorsoArr['ComuneDiscesaId'];
		$CorsaPrenotata_post = $DatiGeneraliPercorsoArr['CorsaId'];
		$DataPrenotata_post = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
		$LineaId_post = $DatiGeneraliPercorsoArr['LineaId'];
	}

	//recupero comuni da form
	if ($_REQUEST['ComunePartenzaId'] > 0) {
		$ComunePartenzaId = $_REQUEST['ComunePartenzaId'];
	}
	if ($_REQUEST['ComuneDestinazioneId'] > 0) {
		$ComuneDestinazioneId = $_REQUEST['ComuneDestinazioneId'];
	}
	if ($Tp == 'R') {
		$ComuneDestinazioneId = $_REQUEST['ComunePartenzaId'];
		$ComunePartenzaId = $_REQUEST['ComuneDestinazioneId'];
	}
	//calcolo Data Inizio ricerca
	if ($Tp == 'A') {
		$dataInizio = $DataFiltroA;
	} else {
		$dataInizio = $DataFiltroR;
	}
	$pieces = explode("/", $dataInizio);
	$from_unix_time = mktime(0, 0, 0, $pieces[1], $pieces[0], $pieces[2]);
	//$day_before = strtotime("-1 day", $from_unix_time);
	//$day_before_formatted = date('Y-m-d', $day_before);
	//$dateRicerca[] = $day_before_formatted;

	$day = strtotime("+0 day", $from_unix_time);
	$dateRicerca[] = date('Y-m-d', $day);

	//$day_after = strtotime("+1 day", $from_unix_time);
	//$day_after_formatted = date('Y-m-d', $day_after);
	//$dateRicerca[] = $day_after_formatted;

	$aColumns = array('CorsaId', 'CorsaNome', 'LineaNome', 'DataPartenzaFormattata', 'AppSettimanaGiornoDescr', 'OrarioPartenza', 'AppCalendarioData', 'LineaId', 'PostiRealmenteDisponibili');
	/*applicazione delle regole di filtraggio e ricerca*/
	//ordine
	$sOrder = "";
	if (isset($_GET['iSortCol_0'])) {
		for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
			if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
				$sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . $db->escape($_GET['sSortDir_' . $i]) . ", ";
			}
		}
		$sOrder = substr_replace($sOrder, "", -2);
		if ($sOrder == "ORDER BY") {
			$sOrder = "";
		}
	}
	$sWhere = "RT_Corsa.Stato = 1 AND RT_Linea.TipoTour = $TipoTour ";
	//filtraggio
	for ($i = 0; $i < count($aColumns); $i++) {
		$j = $i;
		if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
			if ($sWhere == "") {
				$sWhere = " ";
			} else {
				$sWhere .= " AND ";
			}

			$sWhere .= $aColumns[$j] . " LIKE '%" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
		}
	}

	$flag_stop_vendite = 1;
	$sede = new Sede();
	$sede->conn = $db;

	$sede->inizializza($user->SedeId);
	$flag_stop_vendite = $sede->VenditaOltreOrario;
	if ($flag_stop_vendite == 0)
		$sWhere .= " AND (TIMEDIFF(ADDTIME(
				CONCAT(RT_AppCalendario.AppCalendarioData,' 00:00:00'),
				RT_Corsa.OrarioPartenza
			),NOW()))>0";

	//recupero delle corse per data
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => 0,
		"iTotalDisplayRecords" => 0,
		"aaData" => array()
	);

	$corsaObj = new Corsa();
	$corsaObj->conn = $db;
	$count = 0;

	foreach ($dateRicerca as $data) {
		$count++;
		$search = true;
		$ii = 1;
		while ($search) {
			$ii++;
			if ($ii > 2) {
				break;
			}

			$corse = $corsaObj->getCorseValideBO($data, $sOrder, $sWhere);
			if (count($corse) <= 0 && $data != $dateRicerca[1]) {
				$pieces = explode("-", $data);

				$from_unix_time = mktime(0, 0, 0, $pieces[1], $pieces[2], $pieces[0]);
				switch ($count) {
					case 1:
						$new_day = strtotime("-1 day", $from_unix_time);
						break;
					case 3:
						$new_day = strtotime("+1 day", $from_unix_time);
				}
				$data = date('Y-m-d', $new_day);
			} else if (count($corse) <= 0 && $data == $dateRicerca[1]) {
				$search = false;
			} else {
				$search = false;
				foreach ($corse as $key => $corsa) {

					/*verifica se deve visualizzare*/
					$visualizza = false;
					$cId = $corsa['CorsaId'];
					$lId = $corsa['LineaId'];
					$dId = $corsa['AppCalendarioData'];
					                     //echo "corsaID: ".$corsa['CorsaId']."<br>";                    

					$sql = "SELECT * FROM RT_CorsaBlocco WHERE CorsaId = $cId AND DataPartenza = '$dId'";
					$tempRow = $db->fetch_array($sql);
					if (count($tempRow) > 0) {
						//corsa bloccata
						$visualizza = false;
						// echo "corsa bloccata<br>";
						if (($cId == $CorsaPrenotata_post)  and ($ComunePartenzaId == $ComuneSalitaId_post)  and ($dId == $DataPrenotata_post) and ($ComuneDestinazioneId == $ComuneDiscesaId_post)) {
							// 							echo "corsa selezionata<br>";
							//caso in cui si � una corsa gi� selezionata nella prenotazine la visualizzo direttamente
							$visualizza = true;
							//controllo presenza della corsa nella tabella percorso breve
							$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, 0);
							$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
							$pre = new Prenotazione();
							$pre->conn = $db;
							$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaStato=1 order by TrattaPeso desc ";
							$arr_fermate = $db->fetch_array($sql);
							$trattaPartenza = $arr_fermate[0]['TrattaId'];
							$sql = "SELECT distinct FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId  and TrattaStato=1 order by TrattaPeso asc";
							$arr_fermate_d = $db->fetch_array($sql);
							$trattaArrivo = $arr_fermate_d[0]['TrattaId'];
						}
					} else {
						// 						echo "non bloccata<br>";
						// 						//corsa non bloccata
						if (($cId == $CorsaPrenotata_post)  and ($ComunePartenzaId == $ComuneSalitaId_post)  and ($dId == $DataPrenotata_post) and ($ComuneDestinazioneId == $ComuneDiscesaId_post)) {
							// 							echo "corsa selezionata<br>";
							//caso in cui si � una corsa gi� selezionata nella prenotazine la visualizzo direttamente
							$visualizza = true;
							//controllo presenza della corsa nella tabella percorso breve
							$trattaPartenza = null;
							$trattaArrivo = null;
							$sql = "select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$CorsaPrenotata_post";
							$r = $db->query_first($sql);
							if (!empty($r['PercorsoBreveId'])) {
								// 								echo "lettura tabella<br>";
								$trattaPartenza = $r['TrattaPickupId'];
								$trattaArrivo = $r['TrattaDropOffId'];
								$visualizza = true;
							} else {
								// 								echo "lettura grafo<br>";
								$grafo = new GrafoTratte($LineaId_post, $CorsaPrenotata_post, $db, $ComunePartenzaId, $ComuneDestinazioneId);
								$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
								$pre = new Prenotazione();
								$pre->conn = $db;
								$ritorno = $pre->CreatePercorsoBreve($ComunePartenzaId, $ComuneDestinazioneId, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $CorsaPrenotata_post, $LineaId_post);
								unset($grafo);
								unset($TrattePercorse);
								unset($pre);
								unset($ritorno);
							}
						} else {
							// 							echo "corsa non selezionata<br>";
							// caso in cui � una corsa non selezionata
							$trattaPartenza = null;
							$trattaArrivo = null;
							//controllo se le fermate sono attive
							$sql = "select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$cId";
							$r = $db->query_first($sql);
							if (!empty($r['PercorsoBreveId'])) {
								// 								echo "lettura tabella<br>";
								$trattaPartenza = $r['TrattaPickupId'];
								$trattaArrivo = $r['TrattaDropOffId'];
								$visualizza = true;
							} else {
								// 								echo "<br><br>lettura grafo<br>";
								$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId);
								if (isset($grafo->flotta[0])) {
									$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
								} else {
									$TrattePercorse = null;
								}
								$pre = new Prenotazione();
								$pre->conn = $db;
								$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaStato=1 order by TrattaPeso desc ";
								$arr_fermate = $db->fetch_array($sql);
								if (isset($arr_fermate[0])) {
									$trattaPartenza = $arr_fermate[0]['TrattaId'];
								} else {
									$trattaPartenza = null;
								}
								$sql = "SELECT distinct FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId  and TrattaStato=1 order by TrattaPeso asc";
								$arr_fermate_d = $db->fetch_array($sql);
								if (isset($arr_fermate_d[0])) {
									$trattaArrivo = $arr_fermate_d[0]['TrattaId'];
								} else {
									$trattaArrivo = null;
								}
								$ritorno = $pre->CreatePercorsoBreve($ComunePartenzaId, $ComuneDestinazioneId, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $cId, $lId);
								unset($grafo);
								unset($TrattePercorse);
								unset($pre);
								unset($ritorno);
							}

							if (isset($trattaPartenza) && isset($trattaArrivo)) {
								// 								echo "tratta esistente<br>";
								$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaStato=1 order by TrattaPeso desc ";
								// 								echo "fermata pk ".$sql."<br>";
								$arr_fermate = $db->fetch_array($sql);
								$trattaPartenza = $arr_fermate[0]['TrattaId'];
								$sql = "SELECT distinct FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId  and TrattaStato=1 order by TrattaPeso asc";
								// 								echo "fermata dp ".$sql."<br>";
								$arr_fermate_d = $db->fetch_array($sql);
								$trattaArrivo = $arr_fermate_d[0]['TrattaId'];
								if ((sizeof($arr_fermate) > 0) and (sizeof($arr_fermate_d) > 0)) {
									// 									echo "fermata esistente<br>";
									$visualizza = true;
								} else {
									// 									echo "fermata non esistente<br>";
									$visualizza = false;
								}
							} else {
								// 								echo "tratta non esistente<br>";
								$visualizza = false;
							}

							//controllo se la tratta � vendibile
							$sql = "SELECT TratteNonVendibiliId from RT_TratteNonVendibili  WHERE ComunePickUpId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId  ";
							$arr_esclusi = $db->fetch_array($sql);
							if ((sizeof($arr_esclusi) > 0)) {
								$visualizza = false;
								//                             	echo "non vendibile<br>";
							}

							//controllo convenzione
							$sql = "SELECT * FROM GestoreConvenzione where GestoreId = " . $user->GestoreId . " and LineaId = $lId and Now()<=ValidaAl and Now()>=ValidaDal";
							$arr_convenzione = $db->fetch_array($sql);
							if ((sizeof($arr_convenzione) == 0)) {
								$visualizza = false;
								//                             	echo "non convenzione<br>";
							}

							//controllo se la tariffa e' 0
							if($Tp == 'A') {
								$sql = "SELECT * FROM RT_CorsaTariffa 
										where CorsaId = $cId
										AND FermataPickup = $ComunePartenzaId
										AND FermataDropOff = $ComuneDestinazioneId
										AND TipologiaBigliettoId <> 11 
										AND Tariffa > 0";
								$arr_tariffe = $db->fetch_array($sql);
								if ((sizeof($arr_tariffe) == 0)) {
									$visualizza = false;
								}
							}
						}
					}

					// 					/*se la corsa e' visualizzabile l'aggiungo alla lista*/
					if ($visualizza) {
						$row = array();
						for ($i = 0; $i < count($aColumns); $i++) {
							if ($aColumns[$i] == "CorsaNome") {
								$row[] = utf8_decode($corsa['CorsaNome']);
							} else if ($aColumns[$i] == "CorsaId") {
								$CorsaId = $corsa['CorsaId'];
								$DataPartenza = $corsa['AppCalendarioData'];

								$dataodierna = Date('Y-m-d');
								$dt = new DT();
								$diff = $dt->compare($dataodierna, $DataPartenza, 'Y-m-d');

								//se la corsa e' gia' partita verifico se per l'orario fermata � ancora in tempo
								$diff2 = 0;
								$sql = "SELECT o.Orario, o.GiorniAggiuntivi 
										FROM RT_Orario o
										LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
										where o.CorsaId = $CorsaId and f.ComuneId = $ComunePartenzaId ";
								$rowTemp = $db->query_first($sql);
								$dataPartenzaHM = new DateTime($dataodierna . ' ' . $rowTemp['Orario']);
								$dataPartenzaHM->modify('+' . $rowTemp['GiorniAggiuntivi'] . ' day');
								$dataOggiHM = new DateTime();
								if ($dataOggiHM > $dataPartenzaHM) {
									$diff2 = 1;
								}


								$OrarioPartenza = $corsa['OrarioPartenza'];
								$name_input = "Corsa[$CorsaId.]";
								$arr_field = $Tp . "_" . $CorsaId . "_" . $DataPartenza;
								$arr_field = "";

								/* General output */
								// 								$disponibili=$corsa['PostiCorsaDefault']+$corsa['PostiCorsaAggiunti']-$corsa['PostiRealmentePrenotati'];
								// 								if (($disponibili>0) or ($CorsaId_post==$CorsaId) or ($user->SedeLegale==1)) {
								$ck = "";
								if (($CorsaId_post == $CorsaId) and ($DataCorsa_post == $DataPartenza))
									$ck = "checked";
								if ($Tp == 'A') {
									$mod = "$('#modificaData').val(1);";
								} else {
									$mod = "$('#modificaDataRitorno').val(1);";
								}
								$row[] = "<input id=\"a$CorsaId$DataPartenza\" data-id=\"$CorsaId\" $ck type=\"radio\" name=\"Corsa$Tp\" onclick=\"javascript:ControllaDataPassata('$diff','$diff2');$mod$('#CorsaSelezionata$Tp').val($CorsaId);$('#DataSelezionata$Tp').val('$DataPartenza');MostraFermate();\" />";

							} else if ($aColumns[$i] == 'PostiRealmenteDisponibili') {
								
								//recupero il tipo tour della linea
								$sqlLinea = "select * from RT_Linea where LineaId = $lId";
								$lineaTemp = $db->query_first($sqlLinea);
								$tipoTour = $lineaTemp['TipoTour'];

								//calcolo i posti disponibili in base al tipo tour e se il cron del posti disponibili è già avviato
								$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, true);
								$string = '';
								$f = new Fermata();
								$f->conn = $db;
								$first = true;
								foreach ($grafo->flotta as $flotta) {

									foreach ($flotta->comuni as $c => $comune) {
										if (!$f->isInterscambioLinea($lId, $comune['comune'])) {
											$sql = "SELECT * FROM RT_Orario o
													LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
													where o.CorsaId = $cId
													and f.ComuneId = " . $comune['comune'] . "
													and o.Stato = 1 and o.Cancella = 0
													and f.Stato = 1 and f.Cancella = 0 and o.Orario IS NOT NULL;";
											$checkOra = $db->fetch_array($sql);
											if (count($checkOra) > 0) {
												if ($first) {
													$string .= $comune['comune'];
													$first = false;
												} else {
													$string .= ',' . $comune['comune'];
												}
											}
										}
									}
								}
								if ($string == '') {
									$tempR['Posti'] = 0;
								} else {
									$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron 
	                                where CorsaId = $cId and DataPartenza = '$dId' and Comune IN ($string) ";

									$tempR = $db->query_first($sql);
								}
								
								$sql = "Select b.TotalePosti
									from RT_TipologiaBus b
									left join RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
									where c.CorsaId = $CorsaId";
								$tempR = $db->query_first($sql);
								$postiCorsaDefault = $tempR['TotalePosti'];

								if (isset($tempR['Posti'])) {
									$postiOccupati = $tempR['Posti'];

									$sql = "Select TrattaId from RT_DisponibilitaPostiCron 
                                			where CorsaId = $cId and DataPartenza = '$dId' and Posti = $postiOccupati and Comune IN ($string)";
									$tratta =  $db->query_first($sql);
									
									
									$sql = "SELECT TipologiaBusDefaultId from RT_Tratta c
											WHERE TrattaId = " . $tratta['TrattaId'];
											
									$check = $db->query_first($sql);
									if (isset($check['TipologiaBusDefaultId']) && $check['TipologiaBusDefaultId'] > 0) {
										$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
													(select IFNULL((select SUM(c1.NumeroPax)
													from RT_CorsaPaxTratta c1
													where
													c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.TrattaId = " . $tratta['TrattaId'] . " and c1.OdcIdRef = 1
												    group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
												   ) AS `PostiTotali`
												from RT_Tratta c
												join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
												where c.TrattaId = " . $tratta['TrattaId'];
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

									$disponibili = intval($tempR1['PostiTotali']) - intval($postiOccupati);
																		
									if($tipoTour == 1 && intval($postiOccupati) > 0) {
										$disponibili = 0;
									} else if ($tipoTour == 1 && intval($postiOccupati) == 0) {
										$disponibili = 1;
									}
								} else {

									//calcolo posti realmente prenotati
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
									if (isset($tempR1['PostiRealmentePrenotati'])) {
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
									if (!isset($tempR['PostiAggiunti'])) {
										$sql = "select IFNULL((select SUM(c1.NumeroPax)
										from RT_CorsaPax c1
										where
										c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1
										group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
										$tempR = $db->query_first($sql);
									}
									if (isset($tempR['PostiAggiunti'])) {
										$postiCorsaAggiunti = $tempR['PostiAggiunti'];
									} else {
										$postiCorsaAggiunti = 0;
									}

									$disponibili = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
									
									if($tipoTour == 1 && $postiRealmentePrenotati > 0) {
										$disponibili = 0;
									} else if($tipoTour == 1 && $postiRealmentePrenotati == 0) {
										$disponibili = 1;
									}
								}

								if($tipoTour == 0) {
	
									//controllo posti inizio tratta
									$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
									where CorsaId = $cId and DataPartenza = '$dId' and TrattaId = $trattaPartenza ";
									
									$tempR = $db->query_first($sql);
									if (isset($tempR['Posti'])) {
										$tempOccupatiInizio = $tempR['Posti'];
									} else {
										$tempOccupatiInizio = 0;
									}
									$sql = "Select ( $postiCorsaDefault +
									(select IFNULL((select SUM(c1.NumeroPax)
									from RT_CorsaPaxTratta c1
									where
									c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.TrattaId = " . $trattaPartenza . " and c1.OdcIdRef = 1
														group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
													   ) AS `PostiTotali`
													from RT_Tratta c
													where c.TrattaId = " . $trattaPartenza;

									$tempR1 = $db->query_first($sql);
									$tempInizioTot = $tempR1['PostiTotali'];
									$tempDisponibili = $tempInizioTot - $tempOccupatiInizio;
						
									if ($tempDisponibili  > 0) {
										if ($tempDisponibili < $disponibili) {
											$row[] = $tempDisponibili;
										} else {
											$row[] = $disponibili;
										}
									} else {
										$row[] = 0;
									}

								} else {
									$row[] = $disponibili;
								}
								

							} elseif ($aColumns[$i] == 'AppSettimanaGiornoDescr') {
								$sql = "SELECT AppSettimanaGiornoDescr FROM RT_AppCalendario c 
										left join RT_AppSettimana s on s.AppSettimanaGiorno = c.GiornoSettimana
										where c.AppCalendarioData = '$DataPartenza'";
								$tempR = $db->query_first($sql);
								$row[] = $tempR['AppSettimanaGiornoDescr'];
							} elseif (($aColumns[$i] != 'PostiRealmenteDisponibili') and ($aColumns[$i] != 'CorsaNome') and ($aColumns[$i] != '') and ($aColumns[$i] != 'PostiRealmentePrenotati') and ($aColumns[$i] != 'AppCalendarioData') and ($aColumns[$i] != 'LineaId')) {
								// 								/* General output */
								$row[] = ($corsa[$aColumns[$i]]);
							}
						}

						$output['aaData'][] = array_decode_list($row);
						$output["iTotalRecords"] = $output["iTotalRecords"] + 1;
						$output["iTotalDisplayRecords"] = $output["iTotalDisplayRecords"] + 1;
					}
				}
			}
		}
	}

	// 	if (is_object ( ($prenotazione_wizard) )) {		
	// 		$isSearched = false;
	// 		//verifico se la corsa della prenotazione modificata e' presente nella lista di quelle cercate
	// 		foreach($output['aaData'] as $entry){
	// 			if(strpos($entry[0],'val('.$CorsaPrenotata_post.')') !== false  && strpos($entry[0],$DataPrenotata_post) !== false ){
	// 				$isSearched = true;
	// 			}
	// 		}
	// 		if(!$isSearched){
	// 			//aggiunta della corsa alla lista se non e' presente tra quelle ricercate
	// 			$corsaModifica = $corsaObj->getCorsaValidaNoVincoli2($DataPrenotata_post, $CorsaPrenotata_post, $LineaId_post);
	// 			$row = array();
	//  			if(isset($corsaModifica[ 'CorsaNome' ]) && $corsaModifica[ 'CorsaNome' ] != ""){
	// 			for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
	// 				if ( $aColumns[$i] == "CorsaNome") {
	// 					$row[] = utf8_decode($corsaModifica[ 'CorsaNome' ]);
	// 				} else if ( $aColumns[$i] == "CorsaId") {
	// 					$CorsaId=$corsaModifica['CorsaId'];
	// 					$DataPartenza=$corsaModifica['AppCalendarioData'];

	// 					$dataodierna=Date('Y-m-d');
	// 					$dt=new DT();
	// 					$diff=$dt->compare($dataodierna,$DataPartenza,'Y-m-d');

	// 					$OrarioPartenza=$corsaModifica['OrarioPartenza'];
	// 					$name_input="Corsa[$CorsaId.]";
	// 					$arr_field=$Tp."_".$CorsaId."_".$DataPartenza;
	// 					$arr_field="";

	// 					/* General output */

	// 						$ck = "";
	// 						if (($CorsaId_post == $CorsaId) and ($DataCorsa_post == $DataPartenza))
	// 							$ck = "checked";

	// 						$row[] = "<input $ck type=\"radio\" name=\"Corsa$Tp\" onclick=\"javascropt:ControllaDataPassata('$diff');$('#CorsaSelezionata$Tp').val($CorsaId);$('#DataSelezionata$Tp').val('$DataPartenza');MostraFermate();\" />";
	// 				} else if ( $aColumns[$i] == 'PostiRealmenteDisponibili') {
	// 					$CorsaId=$corsaModifica['CorsaId'];
	// 					$DataPartenza=$corsaModifica['AppCalendarioData'];
	// 					$sql = "select IFNULL((select 
	// 						       count(0)
	// 						   from
	// 						    `RT_PrenotazionePercorso`
	// 						    join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
	// 						    join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
	// 						     and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
	// 						     and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
	// 						    join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
	// 						    left join `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
	// 						    left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
	// 						   where
	// 						    ((`RT_Prenotazione`.`Cancella` = 0)
	// 						     and (`RT_PrenotazionePercorso`.`Cancella` = 0)
	// 						     and (`RT_PrenotazionePercorso`.`Stato` = 1)
	// 						     and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
	// 						     and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
	// 						     and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
	// 						     and (`tb`.`OccupaPosto` = 1))
	// 						     and `RT_PrenotazionePercorso`.`CorsaId` = $CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataPartenza'
	// 						   group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0) as PostiRealmentePrenotati";

	// 					$postiRealmentePrenotati = $db->query_first($sql)['PostiRealmentePrenotati'];

	// 					$sql = "select IFNULL((select SUM(c1.NumeroPax)
	// 						   from RT_CorsaPax c1
	// 						   where
	// 						   c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1
	// 						   group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
	// 					$postiCorsaAggiunti = $db->query_first($sql)['PostiAggiunti'];

	// 					$sql = "Select b.TotalePosti 
	// 							from RT_TipologiaBus b
	// 							left join RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
	// 							where c.CorsaId = $CorsaId";
	// 					$postiCorsaDefault = $db->query_first($sql)['TotalePosti'];

	// 					$disponibili = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
	// 					$row[] = $disponibili;
	// 				} elseif ( ( $aColumns[$i] != 'CorsaNome' ) and ( $aColumns[$i] != '' ) and ($aColumns[$i] != 'AppCalendarioData') and ($aColumns[$i] != 'LineaId')) {
	// 					/* General output */
	// 					$row[] = ($corsaModifica[ $aColumns[$i] ]);
	// 				}
	// 			}
	// 			array_unshift ( $output['aaData'], array_decode_list($row));
	// 			$output["iTotalRecords"] = $output["iTotalRecords"]+1;
	// 			$output["iTotalDisplayRecords"] = $output["iTotalDisplayRecords"]+1;
	//  			}
	// 		}
	// 	}

	echo json_encode($output);
}

function checkModificaPenaleResult()
{
	global $db, $prenotazione_wizard, $dizionario, $user;

	$m_nominativo = $_POST['modificaNominativo'];
	$m_itinerario = $_POST['modificaItinerario'];
	$m_data = $_POST['modificaData'];
	$m_dataRitorno = $_POST['modificaDataRitorno'];
	// 	$ritorno_open = $_POST['Prenotazione[RitornoOpen]'];
	$prenotazione_wizard->conn = $db;
	$old_prenotazioneid = $prenotazione_wizard->Id;

	$sqlData = "SELECT DataIns FROM RT_Prenotazione WHERE PrenotazioneId = $old_prenotazioneid";
	$rowData = $db->query_first($sqlData);
	$dataIns = $rowData['DataIns'];

	$sql = "SELECT RitornoOpen FROM RT_Prenotazione WHERE PrenotazioneId = $old_prenotazioneid";
	$ritorno_open = $db->query_first($sql);

	// superate ore max per andata
	$slq = "Select RT_PrenotazioneDettaglio.DataPartenza, RT_PrenotazioneDettaglio.OrarioPartenza, RT_PrenotazioneDettaglio.LineaId, RT_PrenotazioneDettaglio.Tragitto, RT_Linea.TempoMaxModifica, RT_Linea.LineaId, 
	RT_PrenotazioneDettaglio.ComunePartenza, RT_PrenotazioneDettaglio.ComuneArrivo
	FROM RT_PrenotazioneDettaglio
	left join RT_Linea on (RT_Linea.Lineaid = RT_PrenotazioneDettaglio.LineaId)
	Where RT_PrenotazioneDettaglio.PrenotazioneId = $old_prenotazioneid and RT_PrenotazioneDettaglio.Tragitto = 'Andata'
	group by RT_PrenotazioneDettaglio.DataPartenza";
	$date = $db->query_first($slq);

	$sql = "SELECT r.idnazione FROM Comune c
	LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
	LEFT JOIN Regione r on r.RegioneId = p.RegioneId
	where Comune = '" . $date['ComunePartenza'] . "'";
	$partenzaN = $date = $db->query_first($slq);
	$sql = "SELECT r.idnazione FROM Comune c
	LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
	LEFT JOIN Regione r on r.RegioneId = p.RegioneId
	where Comune = '" . $date['ComuneArrivo'] . "'";
	$arrivoN = $date = $db->query_first($slq);
	$tipoCorsa = 'I';
	if (
		isset($partenzaN['idnazione']) &&
		isset($arrivoN['idnazione']) &&
		$partenzaN['idnazione'] == $arrivoN['idnazione']
	) {
		$tipoCorsa = 'N';
	}

	$today = new DateTime();
	$sogliaLimite = new DateTime($date['DataPartenza'] . ' ' . $date['OrarioPartenza']);

	//     $corsaPartita = 0;
	//     if ($today>$sogliaLimite)
	//     	$corsaPartita = 1;



	$sogliaLimite->modify('-' . $date['TempoMaxModifica'] . ' hour');
	if (($m_nominativo || $m_itinerario || $m_data) && $today > $sogliaLimite) {
		if($user->IsAdmin || $user->GestoreId == 1) {
			$isAdmin = 1;
		} else {
			$isAdmin = 0;
		}
		$dataReturn = array('messaggio' => $dizionario['biglietto']['mess_no_modifica_andata'] . ' ' . $date['DataPartenza'] . ' ' . $date['OrarioPartenza'] . '. ' . $dizionario['biglietto']['mess_no_modifica_ultima'] . ' ' . date_format($sogliaLimite, 'H:i d/m/Y') . '', 'modifica' => 'no', 'penale' => 'no', 'fisso' => 0, 'perc' => 0, 'isAdmin' => $isAdmin);
		return $dataReturn;
	}
	if ($ritorno_open['RitornoOpen'] == 0) {
		// superate ore max per ritorno
		if ($m_dataRitorno == 1) {
			$slq = "Select RT_PrenotazioneDettaglio.DataPartenza, RT_PrenotazioneDettaglio.OrarioPartenza, RT_PrenotazioneDettaglio.LineaId, RT_PrenotazioneDettaglio.Tragitto, RT_Linea.TempoMaxModifica, RT_Linea.LineaId
			FROM RT_PrenotazioneDettaglio
			left join RT_Linea on (RT_Linea.Lineaid = RT_PrenotazioneDettaglio.LineaId)
			Where RT_PrenotazioneDettaglio.PrenotazioneId = $old_prenotazioneid and RT_PrenotazioneDettaglio.Tragitto = 'Ritorno'
			group by RT_PrenotazioneDettaglio.DataPartenza";
			$dateR = $db->query_first($slq);
			if (isset($dateR['DataPartenza'])) {
				$sogliaLimiteR = new DateTime($dateR['DataPartenza'] . ' ' . $dateR['OrarioPartenza']);
				$sogliaLimiteR->modify('-' . $dateR['TempoMaxModifica'] . ' hour');
				if ($today > $sogliaLimiteR) {
					$dataReturn = array('messaggio' => $dizionario['biglietto']['mess_no_modifica_ritorno'] . ' ' . $dateR['DataPartenza'] . ' ' . $dateR['OrarioPartenza'] . '. ' . $dizionario['biglietto']['mess_no_modifica_ultima'] . ' ' . date_format($sogliaLimiteR, 'H:i d/m/Y') . '', 'modifica' => 'no', 'penale' => 'no', 'fisso' => 0, 'perc' => 0, 'isAdmin' => $user->IsAdmin);
					return $dataReturn;
				}
			}
		}
	}

	$fisso = 0;
	$perc = 0;
	//non sono superate le soglie max per la modifica e si controllano le regole
	//nominativo
	if ($m_nominativo != 0) {
		$sql = "SELECT ModificaRegolaId, NomeRegola, GiorniPrima, OrePrima FROM RT_ModificaRegola where TipoModifica = 'N' 
                and TipoPrenotazione like '%" . $tipoCorsa . "%' 
                order by GiorniPrima desc, OrePrima desc";
		$regole = $db->fetch_array($sql);
		$dateSoglia = array();
		foreach ($regole as $key => $row) {
			$datetime = new DateTime($date['DataPartenza']);
			$datetime->modify('-' . $row['GiorniPrima'] . ' day');
			$datetime->modify('-' . $row['OrePrima'] . ' hour');
			$dateSoglia[$row['ModificaRegolaId']] = $datetime;
		}
		$today = new DateTime();
		if ($today < $dateSoglia[$regole[0]['ModificaRegolaId']]) {
			$fisso = 0;
			$perc = 0;
		} else {
			$selectDate = $regole[0]['ModificaRegolaId'];
			foreach ($dateSoglia as $id => $dataCheck) {
				if ($today > $dataCheck) {
					$selectDate = $id;
				}
			}

			$sql = "Select * FROM RT_ModificaPenale
			left join RT_ModificaRegola on (RT_ModificaRegola.ModificaRegolaId = RT_ModificaPenale.ModificaRegolaId)
			where RT_ModificaPenale.LineaId = " . $date['LineaId'] . " and RT_ModificaPenale.ModificaRegolaId = $selectDate";

			$penale = $db->fetch_array($sql);
			$fisso = $penale[0]['Fisso'];
			$perc = $penale[0]['Percentuale'];
		}
	}
	//itinerario
	if ($m_itinerario != 0) {
		$sql = "SELECT ModificaRegolaId, NomeRegola, GiorniPrima, OrePrima FROM RT_ModificaRegola where TipoModifica = 'I' 
			and TipoPrenotazione like '%" . $tipoCorsa . "%' 
			order by GiorniPrima desc, OrePrima desc";
		$regole = $db->fetch_array($sql);

		$dateSoglia = array();
		foreach ($regole as $key => $row) {
			$datetime = new DateTime($date['DataPartenza']);
			$datetime->modify('-' . $row['GiorniPrima'] . ' day');
			$datetime->modify('-' . $row['OrePrima'] . ' hour');
			$dateSoglia[$row['ModificaRegolaId']] = $datetime;
		}
		$today = new DateTime();
		if ($today < $dateSoglia[$regole[0]['ModificaRegolaId']]) {
			$fisso = 0;
			$perc = 0;
		} else {
			$selectDate = $regole[0]['ModificaRegolaId'];
			foreach ($dateSoglia as $id => $dataCheck) {
				if ($today > $dataCheck) {
					$selectDate = $id;
				}
			}
			$sql = "Select * FROM RT_ModificaPenale
			left join RT_ModificaRegola on (RT_ModificaRegola.ModificaRegolaId = RT_ModificaPenale.ModificaRegolaId)
			where RT_ModificaPenale.LineaId = " . $date['LineaId'] . " and RT_ModificaPenale.ModificaRegolaId = $selectDate";
			$penale = $db->fetch_array($sql);
			$fisso = $penale[0]['Fisso'];
			$perc = $penale[0]['Percentuale'];
		}
	}
	//data andata
	if ($m_data != 0) {
		$sql = "SELECT ModificaRegolaId, NomeRegola, GiorniPrima, OrePrima FROM RT_ModificaRegola where TipoModifica = 'D' 
			and TipoPrenotazione like '%" . $tipoCorsa . "%' 
			order by GiorniPrima desc, OrePrima desc";
		$regole = $db->fetch_array($sql);

		$dateSoglia = array();
		foreach ($regole as $key => $row) {
			$datetime = new DateTime($date['DataPartenza']);
			$datetime->modify('-' . $row['GiorniPrima'] . ' day');
			$datetime->modify('-' . $row['OrePrima'] . ' hour');
			$dateSoglia[$row['ModificaRegolaId']] = $datetime;
		}
		$today = new DateTime();
		if ($today < $dateSoglia[$regole[0]['ModificaRegolaId']]) {
			$fisso = 0;
			$perc = 0;
		} else {
			$selectDate = $regole[0]['ModificaRegolaId'];
			foreach ($dateSoglia as $id => $dataCheck) {
				if ($today > $dataCheck) {
					$selectDate = $id;
				}
			}
			$sql = "Select * FROM RT_ModificaPenale
			left join RT_ModificaRegola on (RT_ModificaRegola.ModificaRegolaId = RT_ModificaPenale.ModificaRegolaId)
			where RT_ModificaPenale.LineaId = " . $date['LineaId'] . " and RT_ModificaPenale.ModificaRegolaId = $selectDate";
			$penale = $db->fetch_array($sql);
			$fisso = $penale[0]['Fisso'];
			$perc = $penale[0]['Percentuale'];
		}
	}

	if ($ritorno_open['RitornoOpen'] == 0) {
		//data ritorno
		$sql = "Select RitornoOpen FROM RT_Prenotazione Where PrenotazioneId = $old_prenotazioneid";
		$tempOpen = $db->query_first($sql);
		if ($tempOpen['RitornoOpen'] == 0) {
			if ($m_dataRitorno != 0) {
				$sql = "SELECT ModificaRegolaId, NomeRegola, GiorniPrima, OrePrima FROM RT_ModificaRegola where TipoModifica = 'D' 
							and TipoPrenotazione like '%" . $tipoCorsa . "%' 
							order by GiorniPrima desc, OrePrima desc";
				$regole = $db->fetch_array($sql);

				$dateSoglia = array();
				foreach ($regole as $key => $row) {
					$datetime = new DateTime($dateR['DataPartenza']);
					$datetime->modify('-' . $row['GiorniPrima'] . ' day');
					$datetime->modify('-' . $row['OrePrima'] . ' hour');
					$dateSoglia[$row['ModificaRegolaId']] = $datetime;
				}
				$today = new DateTime();
				if ($today < $dateSoglia[$regole[0]['ModificaRegolaId']]) {
					$fisso = 0;
					$perc = 0;
				} else {
					$selectDate = $regole[0]['ModificaRegolaId'];
					foreach ($dateSoglia as $id => $dataCheck) {
						if ($today > $dataCheck) {
							$selectDate = $id;
						}
					}
					$sql = "Select * FROM RT_ModificaPenale
					left join RT_ModificaRegola on (RT_ModificaRegola.ModificaRegolaId = RT_ModificaPenale.ModificaRegolaId)
					where RT_ModificaPenale.LineaId = " . $dateR['LineaId'] . " and RT_ModificaPenale.ModificaRegolaId = $selectDate";
					$penale = $db->fetch_array($sql);
					$fisso = $penale[0]['Fisso'];
					$perc = $penale[0]['Percentuale'];
				}
			}
		}
	}
	// 	if($dataIns <= Config::$dataVersione){
	// 		$fisso = 0;
	// 		$perc = 0;
	// 	}

	if ($fisso > 0 || $perc > 0) {
		$dataReturn = array('messaggio' => $dizionario['biglietto']['mess_modifica_penale'] . ' ' . $fisso . ' ' . $dizionario['biglietto']['mess_modifica_penale2'] . ' ' . $perc . '.', 'modifica' => 'si', 'penale' => 'si', 'fisso' => $fisso, 'perc' => $perc, 'isAdmin' => $user->IsAdmin);
	} else {
		$dataReturn = array('messaggio' => '', 'modifica' => 'si', 'penale' => 'no', 'fisso' => 0, 'perc' => 0, 'isAdmin' => $user->IsAdmin);
	}
	return $dataReturn;
}

function checkModificaPenale()
{
	global $db, $prenotazione_wizard;
	$dataReturn = checkModificaPenaleResult();

	echo json_encode($dataReturn);
	exit();
}

function checkDuplicatoNominativo()
{
	global $db, $prenotazione_wizard, $dizionario;
	/*Controllo nome e cognome in altre prenotazioni della stessa corsa*/
	parse_str($_POST['data'], $searcharray);
	$prenotazione = $searcharray['Prenotazione'];
	$DataSelezionataA = $searcharray['DataSelezionataA'];
	$DataSelezionataR = $searcharray['DataSelezionataR'];
	$clienteNome = $prenotazione['ClienteNome'];
	$messaggio = "";

	$sql = "SELECT count(*) as tot, RT_Prenotazione.CodicePrenotazione FROM RT_PrenotazioneDettaglio
			left join RT_Prenotazione on (RT_Prenotazione.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId)
			where RT_Prenotazione.ClienteNome = '" . $clienteNome . "'
			and (RT_PrenotazioneDettaglio.DataInizioItinerario = '" . $DataSelezionataA . "' or RT_PrenotazioneDettaglio.DataInizioItinerario = '" . $DataSelezionataR . "')
			and	(RT_Prenotazione.PrenotazioneStato = 3 or RT_Prenotazione.PrenotazioneStato = 1)";

	$result = $db->query_first($sql);
	if ($result['tot'] > 0) {
		$messaggio .= $dizionario['biglietto']['mess_nome_presente'] . " " . $clienteNome . " " . $dizionario['biglietto']['mess_nome_presente2'] . " " . $result['CodicePrenotazione'] . " " . $dizionario['biglietto']['mess_nome_presente3'];
		echo json_encode(array('messaggio' => $messaggio, 'result' => $resultSearch));
	} else {
		echo json_encode(array('result' => false));
	}
}

function checkLimiteServizi($bigliettoId, $corsaIdA, $dataA, $corsaIdR, $dataR, $valore, $numeroTotalePax)
{
	global $db, $prenotazione_wizard, $dizionario, $user;

	$sql = "Select TipologiaBiglietto from RT_TipologiaBiglietto where TipologiaBigliettoId = $bigliettoId";
	$result = $db->query_first($sql);
	$nome = $result['TipologiaBiglietto'];
	//recupero limiti
	$sql = "Select Limite, LimiteMin, LimiteOre, LimitePerNumPassegeri from RT_ListinoServizi where CorsaId = 0 and BigliettoId = $bigliettoId";
	$result = $db->query_first($sql);
	$limiteA = $result['Limite'];
	$limiteR = 0;
	$limiteMin = $result['LimiteMin'];
	$limiteOre = $result['LimiteOre'];
	$limitePerNumPassegeri = $result['LimitePerNumPassegeri'];

	if (isset($corsaIdR) && $corsaIdR > 0) {
		$sql = "Select Limite from RT_ListinoServizi where CorsaId = 0 and BigliettoId = $bigliettoId";
		$result = $db->query_first($sql);
		$limiteR = $result['Limite'];
	}

	//recupero numero biglietti prenotati per corsa di andata
	$sql = "Select count(*) as tot, d.TipologiaBiglietto as tipo from RT_PrenotazioneDettaglio d
	left join RT_TipologiaBiglietti t on (d.TipologiaBiglietto = t.TipologiaBiglietto)
	where d.CorsaId = $corsaIdA and d.BigliettoId = $bigliettoId and d.DataInizioItinerario = '$dataA' and t.TipologiaBigliettoId = $bigliettoId";
	$result = $db->query_first($sql);
	$numA = $result['tot'];
	if(!isset($numA)) {
		$numA = 0;
	}
	//recupero numero biglietti prenotati per corsa di ritorno
	if (isset($corsaIdR) && $corsaIdR > 0) {
		$sql = "Select count(*) as tot, d.TipologiaBiglietto as tipo from RT_PrenotazioneDettaglio d
		left join RT_TipologiaBiglietti t on (d.TipologiaBiglietto = t.TipologiaBiglietto)
		where d.CorsaId = $corsaIdR and d.BigliettoId = $bigliettoId and d.DataInizioItinerario = '$dataR' and t.TipologiaBigliettoId = $bigliettoId";
		$result = $db->query_first($sql);
		$numR = $result['tot'];
		if(!isset($numR)) {
			$numR = 0;
		}
	}

	//verifica se superato limite numero massimo di biglietti per corsa di andata
	$resultSearch = true;
	$messaggio = '';
	if ($limiteA > 0 && ($numA + $valore) > $limiteA) {
		$resultSearch = false;
		$messaggio = $dizionario['tipo_big']['mes_limite1'] . " " . $nome . " " . $dizionario['tipo_big']['mes_limite_andata_superato'] . " " . $dataA." (max. $limiteA per tour)";
	}
	//verifica se superato limite numero massimo di biglietti per corsa di ritorno
	if (isset($corsaIdR) && $corsaIdR > 0 && $limiteR > 0 && (($numR + $valore) > $limiteR)) {
		$resultSearch = false;
		$messaggio .= " " . $dizionario['tipo_big']['mes_limite1'] . " " . $nome . " " . $dizionario['tipo_big']['mes_limite_ritorno_superato'] . " " . $dataR." (max. $limiteR per tour)";
	}
	
	//verifica se non e' superato numero minimo di biglietti per prenotazione
	if($limiteMin > 0 && $valore > 0 && $limiteMin > $valore) {
		$resultSearch = false;
		$tempMessaggio = sprintf($dizionario['tipo_big']['mes_limite_min'], $limiteMin, $nome);
		$messaggio .= " " .$tempMessaggio;
	}

	//verifica se superato limite numero di biglietti per passeggeri
	$numNecessari = ceil($numeroTotalePax / $limitePerNumPassegeri);
	if($user->GestoreId != 1 && $limitePerNumPassegeri > 0 && $valore < $numNecessari) {
		$resultSearch = false;
		$messaggio .= " " . sprintf($dizionario['tipo_big']['mes_limite_num_passegeri'], $nome, $numNecessari, $limitePerNumPassegeri);
	}
	
	//verificato se non e' superato limite tempo prima della partenza
	if($limiteOre > 0) {
		$resultSearch = false;
		
		$sql = "SELECT * FROM RT_Corsa WHERE CorsaId = ".$corsaIdA ;
		$result = $db->query_first($sql);
		$oraPartenza = $result['OrarioPartenza'];
		
		$dataAttuale = date("Y-m-d");
		$orarioAttuale = date("H:i");
		$timestampAttuale = strtotime("$dataAttuale $orarioAttuale");
		
		$timestampCorsa = strtotime("$dataA $oraPartenza");
		$timestampSottratto = $timestampCorsa - ($limiteOre * 3600);
		if ($timestampAttuale > $timestampSottratto) {
			$resultSearch = false;
			$tempMessaggio = sprintf($dizionario['tipo_big']['mes_limite_ore'], $nome, $limiteOre);
			$messaggio .= " " .$tempMessaggio;
		}	
	}

	return array('messaggio' => $messaggio, 'result' => $resultSearch);
}

function CancellaMovimento()
{
	global $db, $prenotazione_wizard, $user, $dizionario;
	$prenotazioneId = $_GET['PrenotazioneId'];
	$movimentoId = $_GET['PrenotazioneMovimentoId'];

	$db = new Database();
	$db->connect();

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$sql = "Select * from RT_PrenotazioneMovimento where PrenotazioneMovimentoId = $movimentoId";
	$movimento = $db->query_first($sql);

	$sql = "select TotalePrenotazione, TotaleDaPagare, TotalePagato, TotaleResiduo from RT_Prenotazione where PrenotazioneId = $prenotazioneId";
	$prenotazione = $db->query_first($sql);

	//aggiorna prenotazione
	$prenotazione['TotalePrenotazione'] = $prenotazione['TotalePrenotazione'] - $movimento['Importo'];
	$prenotazione['TotaleDaPagare'] = $prenotazione['TotaleDaPagare'] - $movimento['Importo'];
	$prenotazione['TotaleResiduo'] = $prenotazione['TotaleResiduo'] - $movimento['Importo'];
	$prenotazione = $storico->operazioni_update($prenotazione, $user);
	$db->update("RT_Prenotazione", $prenotazione, "PrenotazioneId=$prenotazioneId");

	//aggiorna movimento
	$dm['TipoMovimento'] = 'A';
	$dm['Causale'] = 'Movimento annullato da operatore';
	$dm = $storico->operazioni_update($dm, $user);
	$db->update("RT_PrenotazioneMovimento", $dm, "PrenotazioneMovimentoId=$movimentoId");

	//aggiorna residuo
	$sql = "select PrenotazioneBigliettoId, RiduzionePax, PrezzoTotalePax from RT_PrenotazioneBiglietto where PrenotazioneId = $prenotazioneId";
	$biglietto = $db->query_first($sql);
	$dbig['RiduzionePax'] = $biglietto['RiduzionePax'] + $movimento['Importo'];
	$dbig['PrezzoTotalePax'] = $biglietto['PrezzoTotalePax'] - $movimento['Importo'];
	$dbig = $storico->operazioni_update($dbig, $user);
	$db->update("RT_PrenotazioneBiglietto", $dbig, "PrenotazioneBigliettoId=" . $biglietto['PrenotazioneBigliettoId']);

	echo ("ok||" . 'Pagamento annullato');
	exit();
}

function invioMessaggio()
{

	global $db, $prenotazione_wizard, $user, $dizionario;
	$prenotazioneId = $_POST['PrenotazioneId'];
	$tipo = $_POST['Tipo'];
	$messaggio = $_POST['Messaggio'];

	$db = new Database();
	$db->connect();

	$whatsapp = new ServiceWhatsapp($db);
	if ($tipo == 'ticket') {
		$r = $whatsapp->invioWhatsapp($prenotazioneId, $user, $messaggio);
	} else if (
		$tipo == 'payment' || $tipo == 'payment_intesasanpaolo' || $tipo == 'payment_paypal'
		|| $tipo == 'payment_banca5' || $tipo == 'payment_postepay' || $tipo == 'payment_barzahlen'
	) {
		$r = $whatsapp->invioMessaggioWhatsapp($messaggio, $prenotazioneId, $user, 'payment');
	} else {
		$r = $whatsapp->invioMessaggioWhatsapp("Comunicazione dello staff:", $prenotazioneId, $user);
		$r = $whatsapp->invioMessaggioWhatsapp($messaggio, $prenotazioneId, $user);
	}

	echo ('Messaggio inviato correttamente_3_' . $prenotazioneId . '_0');
	exit();
}

function stripe($prenotazioneId)
{
	global $user, $prenotazione_wizard, $dizionario;
	$db = new Database();
	$db->connect();

	ini_set('display_errors', 0);
	ini_set('error_reporting', E_ALL);

	$sql = "select GestoreConvenzioneId,Percentuale,Fisso from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId";
	$row1 = $db->query_first($sql);
	$percentuale_a = 0;
	$fisso_a = 0;
	if ($row1['GestoreConvenzioneId'] > 0) {
		$percentuale_a = $row1['Percentuale'];
		$fisso_a = $row1['Fisso'];
	}

	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	$totaliImporti = $prenotazione_wizard->GetTotaliPrenotazione();

	$daPagare = $totaliImporti['TotaleResiduo'];
	$totalePagato = $totaliImporti['TotalePagato'];

	//pagamento per agenzie non verificate
	$sql = "SELECT * FROM Gestore WHERE GestoreId = " . $user->GestoreId;
	$gestore = $db->query_first($sql);
	if (Config::$pagamentoProvvigioniAgenzie  && !$gestore['Verificato']) {
		//recupero provvigione
		$sql = "select PrezzoTotalePax, NumeroPax, TipologiaBigliettoId, PrenotazioneId
						from RT_PrenotazioneBiglietto
						where PrenotazioneId = $prenotazioneId";


		$rowsB = $db->fetch_array($sql);
		$provTotale = 0;
		if ($totalePagato == 0) {
			foreach ($rowsB as $rowB) {
				$sql = "select * from RT_GestoreProvvigioneDettaglio
      							where GestoreId = " . $user->GestoreId . " AND BigliettoId = " . $rowB['TipologiaBigliettoId'];
				$rowTemp = $db->query_first($sql);

				$provvigione = $rowB['PrezzoTotalePax'] * $rowTemp['Percentuale'] / 100 + ($rowTemp['Fisso'] * $rowB['NumeroPax']);
				$provTotale += $provvigione;
			}
		} else {
			$sql = "select * from RT_GestoreProvvigioneDettaglio
      							where GestoreId = " . $user->GestoreId . " AND BigliettoId = 17";
			$rowTemp = $db->query_first($sql);
			$provTotale = $daPagare * $rowTemp['Percentuale'] / 100 + ($rowTemp['Fisso']);
		}
		$daPagare = $daPagare - $provTotale;
	}

	//$importoAgenzia=($daPagare*$percentuale_a/100)-$fisso_a;
	$dcorr = date('Y-m-d');
	if ($dcorr >= '2019-05-01')
		//$ivaAgenzia=($importoAgenzia*Config::$AppIva)/100;
		$ImportoTotale_final_banca = $daPagare;

	$url_sito = Config::$httpHost;
	$bank_pagina_grazie = Config::$pageGrazieStripe;
	$bank_pagina_grazie .= "?prenotazioneId=" . $prenotazioneId;

	Stripe::setApiKey(Config::$StripeSecretKey);
	$session = \Stripe\Checkout\Session::create([
		//'payment_method_types' => ['card'],
		'line_items' => [[
			'price_data' => [
				'currency' => 'eur',
				'product_data' => [
					'name' => 'Ticket Bertoldi Boats',
				],
				'unit_amount' => intval($ImportoTotale_final_banca * 100),
			],
			'quantity' => 1,
		]],
		'mode' => 'payment',
		'success_url' => $bank_pagina_grazie . '&session_id={CHECKOUT_SESSION_ID}',
		'cancel_url' => $url_sito,
	]);
	echo  $session->id;
	exit();
}

function stripeSendLinkAgenzia($prenotazioneId) {
	global $user, $prenotazione_wizard, $dizionario, $db;
	
	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
	$prenotazione = $db->query_first($sql);

	//recupero gestore
	$sql = "SELECT * FROM Gestore WHERE GestoreId = " . $prenotazione['GestoreIdRef'];
	$gestore = $db->query_first($sql);

	//recupero importo delle provvigioni delle agenzie
	$sql = "SELECT GestoreConvenzioneId,Percentuale,Fisso FROM RT_GestoreProvvigioneDettaglio WHERE GestoreId = ".$gestore['GestoreId'];
	$row1 = $db->query_first($sql);
	$percentuale_a = 0;
	$fisso_a = 0;
	if ($row1['GestoreConvenzioneId'] > 0) {
		$percentuale_a = floatval($row1['Percentuale']);
		$fisso_a = floatval($row1['Fisso']);
	}

	$daPagare = floatval($prenotazione['TotaleResiduo']) - (floatval($prenotazione['TotaleResiduo']) * $percentuale_a / 100) - $fisso_a;

	if(isset($gestore['Email']) && $gestore['Email'] != '') {
		//recupero link per pagamento
		$sessionId = stripeLink($prenotazioneId, false, false, true);
		$url = 'https://' . $_SERVER['SERVER_NAME'].'/pagamento_stripe_link.php?session_id=' . $sessionId;

		//i dati per l'invio sono presenti nella tabella ODC
		$sql = "SELECT * FROM Odc WHERE OdcId = 1";
		$odc = $db->query_first($sql);
	
		// setto il percorso del template da usare come contenuto del email
		$emailtemplate = $_SERVER['DOCUMENT_ROOT']."/protected/modules/rt_previaggio/pdftemplate/pagamento_online_agenzia.html";
		$fh = fopen($emailtemplate,"r");
		$content_html = fread($fh,filesize($emailtemplate));
		fclose($fh);
		$content_html = str_replace("[__CODICE__]", $prenotazione['CodicePrenotazione'], $content_html);
		$content_html = str_replace("[__NOMEAGENZIA__]", $gestore['RagioneSociale'], $content_html);
		$content_html = str_replace("[__URL__]", $url, $content_html);
		$content_html = str_replace("[__TOTALE__]", number_format($daPagare, 2, ',', '.') . ' &euro;', $content_html);

		$mail= new PHPMailer(); // create the mail
		$mail->Subject = "Bertoldi Boats - Pagamento Prenotazione ".$prenotazione['CodicePrenotazione'];
		$mail->AddAddress($gestore['Email']);
		$mail->MsgHTML($content_html);
		$mail->IsSMTP();
		$mail->SMTPDebug  = null;
		$mail->SMTPSecure = 'ssl';// SMTP account password
		$mail->SMTPAuth = true;
		$mail->IsHTML(true);
			
		// setto il from    
		$from = $odc['EmailSmtp'];
		$fromName = $odc['NomeEmailSmtp'];
		$mail->SetFrom($from, $fromName);  
		$mail->Host = $odc['ServerSmtp'];				// Server SMTP
		$mail->Port = $odc['PortaSmtp'];				// Server SMTP Port
		$mail->Username = $odc['UserSmtp'];           	// SMTP account username
		$mail->Password = $odc['PwdSmtp'];      
	
		// SMTP account password
		$mail->Send();  
		
		echo  "ok";
		exit();
	} else {
		echo  "no";
		exit();
	}

}

function stripeSendLink($prenotazioneId) {
	global $user, $prenotazione_wizard, $dizionario, $db, $dizionarioEmail;
	
	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
	$prenotazione = $db->query_first($sql);

	if(isset($prenotazione['ClienteEmail']) && $prenotazione['ClienteEmail'] != '') {
		//recupero link per pagamento
		$sessionId = stripeLink($prenotazioneId, false, true, true);
		$url = 'https://' . $_SERVER['SERVER_NAME'].'/pagamento_stripe_link.php?session_id=' . $sessionId;

		//i dati per l'invio sono presenti nella tabella ODC
		$sql = "SELECT * FROM Odc WHERE OdcId = 1";
		$odc = $db->query_first($sql);
	
		// setto il percorso del template da usare come contenuto del email in base alla lingua della prenotazione
		$pagamentoFileName = (!isset($prenotazione['Lingua']) || $prenotazione['Lingua'] == 'it') ? 'pagamento_online_cliente.html' : 'pagamento_online_cliente_'.$prenotazione['Lingua'].'.html';
		$emailtemplate = $_SERVER['DOCUMENT_ROOT']."/protected/modules/rt_previaggio/pdftemplate/$pagamentoFileName";
		//$emailtemplate = $_SERVER['DOCUMENT_ROOT']."/protected/modules/rt_previaggio/pdftemplate/pagamento_online_cliente.html";
		$fh = fopen($emailtemplate,"r");
		$content_html = fread($fh,filesize($emailtemplate));
		fclose($fh);
		$content_html = str_replace("[__CODICE__]", $prenotazione['CodicePrenotazione'], $content_html);
		$content_html = str_replace("[__NOMECLIENTE__]", $prenotazione['ClienteNome'], $content_html);
		$content_html = str_replace("[__URL__]", $url, $content_html);
		$content_html = str_replace("[__TOTALE__]", number_format($prenotazione['TotaleResiduo'], 2, ',', '.') . ' &euro;', $content_html);

		$mail= new PHPMailer(); // create the mail
		$mail->Subject = "Bertoldi Boats - ".$dizionarioEmail[$prenotazione['Lingua']]['pagamento_link']." ".$prenotazione['CodicePrenotazione'];
		$mail->AddAddress($prenotazione['ClienteEmail']);
		$mail->MsgHTML($content_html);
		$mail->IsSMTP();
		$mail->SMTPDebug  = null;
		$mail->SMTPSecure = 'ssl';// SMTP account password
		$mail->SMTPAuth = true;
		$mail->IsHTML(true);
			
		// setto il from    
		$from = $odc['EmailSmtp'];
		$fromName = $odc['NomeEmailSmtp'];
		$mail->SetFrom($from, $fromName);  
		$mail->Host = $odc['ServerSmtp'];				// Server SMTP
		$mail->Port = $odc['PortaSmtp'];				// Server SMTP Port
		$mail->Username = $odc['UserSmtp'];           	// SMTP account username
		$mail->Password = $odc['PwdSmtp'];      
	
		// SMTP account password
		$mail->Send();  
		
		if (Config::$salesmanago_enabled) {
			$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $prenotazioneId";
			$prenotazionePercorso = $db->query_first($sql);

			$sql = "SELECT * FROM RT_PrenotazioneBiglietto WHERE PrenotazioneId = $prenotazioneId";
			$prenotazioneBiglietti = $db->fetch_array($sql);
			$descrizioneBiglietto = '';
			foreach ($prenotazioneBiglietti as $biglietto) {
				$descrizioneBiglietto .= $biglietto['NumeroPax']. " x " . $biglietto['TipologiaBiglietto'] . ', ';
			}
			$descrizioneBiglietto = rtrim($descrizioneBiglietto, ', ');

			$sql = "SELECT * FROM PrefissoTelefono WHERE Prefisso = '".$prenotazione['ClienteCellularePrefisso']."'";
			$prefisso = $db->query_first($sql);

			$sm = new ServiceSalesManago($db);
			$result = $sm->sendEvent($prenotazione['PrenotazioneId'], 
							ServiceSalesManago::EVENT_OFFER, 
							$prenotazione['ClienteEmail'], 
							$prenotazionePercorso['LineaNome'],
							$prenotazione['TotaleDaPagare'],
							$prenotazione['Lingua'],
							$prenotazione['TipoTour'] == 1 ? 'Tour Privato' : 'Tour Gruppo', 
							$descrizioneBiglietto,
							'+'.$prenotazione['ClienteCellularePrefisso'].$prenotazione['ClienteCellulare'],
							$prenotazione['ClienteNome'],
							$prefisso['Nazione'],
							null,
						'office',
						$url
						);
		}

		echo  "ok";
		exit();
	} else {
		echo  "no";
		exit();
	}

}


function stripeLink($prenotazioneId, $post = true, $cliente = true, $sendEmail = false)
{
	global $user, $prenotazione_wizard, $dizionario;
	$db = new Database();
	$db->connect();

	ini_set('display_errors', 0);
	ini_set('error_reporting', E_ALL);

	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	$datiPrenotazione = $prenotazione_wizard->DatiGenerali;
	$totaliImporti = $prenotazione_wizard->GetTotaliPrenotazione();
	$cliente_email = $datiPrenotazione['ClienteEmail'];
	if (!isset($cliente_email) || $cliente_email == '') {
		$cliente_email = 'noemail';
	}
	$daPagare = $totaliImporti['TotaleResiduo'];

	if(!$cliente) {
		//recupero il gestore della prenotazione
		$gestoreId = $datiPrenotazione['GestoreIdRef'];

		//recupero importo delle provvigioni delle agenzie
		$sql = "SELECT GestoreConvenzioneId,Percentuale,Fisso FROM RT_GestoreProvvigioneDettaglio WHERE GestoreId = $gestoreId";
		$row1 = $db->query_first($sql);
		$percentuale_a = 0;
		$fisso_a = 0;
		if ($row1['GestoreConvenzioneId'] > 0) {
			$percentuale_a = $row1['Percentuale'];
			$fisso_a = $row1['Fisso'];
		}
		$daPagare = floatval($daPagare) - (floatval($daPagare) * $percentuale_a / 100) - $fisso_a;
	}

	$ImportoTotale_final_banca = $daPagare;

	$url_sito = Config::$UrlDominio;
	$bank_pagina_grazie = Config::$UrlDominio . "grazie.php";
	$bank_pagina_grazie .= "?OrderId=" . $prenotazioneId . "&em=" . $cliente_email;

	Stripe::setApiKey(Config::$StripeLinkSecretKey);
	$session = \Stripe\Checkout\Session::create([
		//'payment_method_types' => ['card','paypal','amazon_pay'],
		'line_items' => [[
			'price_data' => [
				'currency' => 'eur',
				'product_data' => [
					'name' => 'Ticket Bertoldi Boats',
				],
				'unit_amount' => intval($ImportoTotale_final_banca * 100),
			],
			'quantity' => 1,
		]],
		'mode' => 'payment',
		'success_url' => $bank_pagina_grazie . '&session_id={CHECKOUT_SESSION_ID}',
		'cancel_url' => $url_sito,
	]);

	if(!$sendEmail) {
		if (Config::$salesmanago_enabled) {
			$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $prenotazioneId";
			$prenotazionePercorso = $db->query_first($sql);

			$sql = "SELECT * FROM RT_PrenotazioneBiglietto WHERE PrenotazioneId = $prenotazioneId";
			$prenotazioneBiglietti = $db->fetch_array($sql);
			$descrizioneBiglietto = '';
			foreach ($prenotazioneBiglietti as $biglietto) {
				$descrizioneBiglietto .= $biglietto['NumeroPax']. " x " . $biglietto['TipologiaBiglietto'] . ', ';
			}
			$descrizioneBiglietto = rtrim($descrizioneBiglietto, ', ');

			$sql = "SELECT * FROM PrefissoTelefono WHERE Prefisso = '".$datiPrenotazione['ClienteCellularePrefisso']."'";
			$prefisso = $db->query_first($sql);

			$sm = new ServiceSalesManago($db);
			$result = $sm->sendEvent($datiPrenotazione['PrenotazioneId'], 
							ServiceSalesManago::EVENT_OFFER, 
							$datiPrenotazione['ClienteEmail'], 
							$prenotazionePercorso['LineaNome'],
							$datiPrenotazione['TotaleDaPagare'],
							$datiPrenotazione['Lingua'],
							$datiPrenotazione['TipoTour'] == 1 ? 'Tour Privato' : 'Tour Gruppo', 
							$descrizioneBiglietto,
							'+'.$datiPrenotazione['ClienteCellularePrefisso'].$datiPrenotazione['ClienteCellulare'],
							$datiPrenotazione['ClienteNome'],
							$prefisso['Nazione'],
							null,
							'office'
						);
		}
	}

	// Salva il sessionId e la prenotazioneId nella tabella RT_PrenotazioneStripeLink er effettuare i controlli in fase di pagamento
    $dataInsert = array();
    $dataInsert['PrenotazioneId'] = $prenotazioneId;
    $dataInsert['SessionId'] = $session->id;
    $dataInsert['Data'] = date('Y-m-d H:i:s');
    $db->insert("RT_PrenotazioneStripeLink", $dataInsert);

	if($post) {
		echo  $session->id;
		exit();
	} else {
		return $session->id;
	}

}

function ripristinaPrenotazioneWeb()
{
	global $db, $prenotazione_wizard, $dizionario, $user;
	$CodiceTransazione = time();
	$PrenotazioneId = $prenotazione_wizard->Id;

	$s = "select PrenotazioneTransazioneWeb from RT_PrenotazioneTransazione where PrenotazioneId=$PrenotazioneId";
	$row = $db->query_first($s);
	
	if (empty($row['PrenotazioneTransazioneWeb'])) {
		$data = null;
		$data['PrenotazioneId'] = $PrenotazioneId;
		$data['TipoPagamentoId'] = $_POST['tipoPagamento'];
		$data['CodiceTransazione'] = $CodiceTransazione;
		$data['payment_type'] = 'instant';
		$data['payment_status'] = 'Completed';
		$data['address_status'] = 'confirmed';
		$data['payer_status'] = 'verified';
		$data['first_name'] = $user->Cognome;
		$data['last_name'] = $user->Nome;
		$data['payer_email'] = 'info@bertoldiboats.com';
		$data['payer_id'] = 1;
		$data['mc_gross'] = 1;
		$data['ImportoPrenotazione'] = 1;
		$transactionId = $db->insert("RT_PrenotazioneTransazione", $data);

		if (Config::$salesmanago_enabled) {
			$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
			$prenotazione = $db->query_first($sql);

			$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $PrenotazioneId";
			$prenotazionePercorso = $db->query_first($sql);

			$sql = "SELECT * FROM RT_PrenotazioneBiglietto WHERE PrenotazioneId = $PrenotazioneId";
			$prenotazioneBiglietti = $db->fetch_array($sql);
			$descrizioneBiglietto = '';
			foreach ($prenotazioneBiglietti as $biglietto) {
				$descrizioneBiglietto .= $biglietto['NumeroPax']. " x " . $biglietto['TipologiaBiglietto'] . ', ';
			}
			$descrizioneBiglietto = rtrim($descrizioneBiglietto, ', ');

			$sql = "SELECT * FROM PrefissoTelefono WHERE Prefisso = '".$prenotazione['ClienteCellularePrefisso']."'";
			$prefisso = $db->query_first($sql);

			$sm = new ServiceSalesManago($db);
			$result = $sm->sendEvent($prenotazione['PrenotazioneId'], 
							ServiceSalesManago::EVENT_PURCHASE, 
							$prenotazione['ClienteEmail'], 
							$prenotazionePercorso['LineaNome'],
							$prenotazione['TotaleDaPagare'],
							$prenotazione['Lingua'],
							$prenotazione['TipoTour'] == 1 ? 'Tour Privato' : 'Tour Gruppo', 
							$descrizioneBiglietto,
								'+'.$prenotazione['ClienteCellularePrefisso'].$prenotazione['ClienteCellulare'],
								$prenotazione['ClienteNome'],
								$prefisso['Nazione'],
								null,
							'office'
							);
		}
	}

	$data1 = array();
	$data1['PrenotazioneStato'] = 11;
	$data1['ScadenzaPrenotazione'] = null;
	$db->update("RT_Prenotazione", $data1, "PrenotazioneId=$PrenotazioneId");

	$data1 = array();
	$data1['PrenotazioneStato'] = 11;
	$db->update("RT_PrenotazionePercorso", $data1, "PrenotazioneId=$PrenotazioneId");

	/**controllo disponibilita posti**/
	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoA = $prenotazione_wizard->DatiGeneraliPercorso;
	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
	$DatiGeneraliPercorsoR = $prenotazione_wizard->DatiGeneraliPercorso;
	aggiornaDisponibilita($DatiGeneraliPercorsoA['CorsaId'], $DatiGeneraliPercorsoA['CorsaDataPartenza']);
	if (isset($DatiGeneraliPercorsoR['CorsaDataPartenza'])) {
		aggiornaDisponibilita($DatiGeneraliPercorsoR['CorsaId'], $DatiGeneraliPercorsoR['CorsaDataPartenza']);
	}
	/**fine controllo disponibilita posti**/

	echo ("ok||" . 'La prenotazione è in fase di ripristino');
}
function calcolaOre($orarioPartenza, $orarioArrivo)
{
	// Crea oggetti DateTime per le due variabili
	$partenzaDateTime = new DateTime($orarioPartenza);
	$arrivoDateTime = new DateTime($orarioArrivo);

	// Calcola la differenza tra le due date
	$differenza = $partenzaDateTime->diff($arrivoDateTime);

	// Ottieni il numero totale di ore dalla differenza
	$oreTotali = $differenza->h;

	return $oreTotali;
}

function getComune($comuneId)
{
	global $db;
	$sql = "Select * FROM Comune WHERE ComuneId = $comuneId";
	$comune = $db->query_first($sql);
	return $comune;
}

function creaNomeTratta($partenzaId, $destinazioneId)
{
	global $db;
	$partenza = getComune($partenzaId);
	$destinazione = getComune($destinazioneId);

	return $partenza['Comune'] . " - " . $destinazione['Comune'];
}

function fiscalGatewayEmettiRicevuta($movimentoId){
    global $db;
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
		case 6:
            $paymentMethodsType = 'CARD'; // Agenzia
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

function fiscalGatewayDownloadRicevuta(){
	global $db, $user;
	$service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);
	
	//recupero movimento
	$movimentoId = $_POST['MovimentoId'];
	$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
	$movimento = $db->query_first($sql);
	
	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
	$prenotazione = $db->query_first($sql);
	
	$result = $service->getBillDownloadUrl($movimento['ScontrinoId']); 
	
	echo json_encode($result);
	//return $result;
}


function fiscalGatewayInviaRicevuta($movimentoId){
	global $db, $user, $dizionarioEmail;

	//$emailtemplate = $_SERVER['DOCUMENT_ROOT']."/protected/modules/rt_previaggio/pdftemplate/scontrino.html";

	$service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);
	
	//recupero movimento
	$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
	$movimento = $db->query_first($sql);
	
	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
	$prenotazione = $db->query_first($sql);

	//recupero template email in base alla lingua della prenotazione
	$scontrinoFileName = (!isset($prenotazione['Lingua']) || $prenotazione['Lingua'] == 'it') ? 'scontrino.html' : 'scontrino_'.$prenotazione['Lingua'].'.html';
	$emailtemplate = $_SERVER['DOCUMENT_ROOT']."/protected/modules/rt_previaggio/pdftemplate/$scontrinoFileName";
	
	$result = $service->getBillDownloadUrl($movimento['ScontrinoId']);

	if(!isset($result['response']['result']['url']) || empty($result['response']['result']['url'])) {
		// se non c'è l'url non invio nulla
		$dataMovimento = array();
		$dataMovimento['ScontrinoNotifica'] = 1;
		$updInviaTitolo = $db->update('RT_PrenotazioneMovimento', $dataMovimento, "PrenotazioneMovimentoId = ".$movimentoId);
		echo json_encode(true);
	}
	
	// setto il percorso del template da usare come contenuto del email
	$fh = fopen($emailtemplate,"r");
	$content_html = fread($fh,filesize($emailtemplate));
	fclose($fh);
	$content_html = str_replace("[__URL__]", $result['response']['result']['url'], $content_html);
	
	//i dati per l'invio sono presenti nella tabella ODC
	$sql = "SELECT * FROM Odc WHERE OdcId = $user->OdcId";
	$odc = $db->query_first($sql);
	
    $mail= new PHPMailer(); // create the mail
    $mail->Subject = "Bertoldi Boats - ".$dizionarioEmail[$prenotazione['Lingua']]['scontrino']." ".$prenotazione['CodicePrenotazione'];
	$mail->AddAddress($prenotazione['ClienteEmail']);
	$mail->MsgHTML($content_html);
	$mail->IsSMTP();
	$mail->SMTPDebug  = null;
	$mail->SMTPSecure = 'ssl';// SMTP account password
	$mail->SMTPAuth = true;
	$mail->IsHTML(true);
        
    // setto il from    
    $from = $odc['EmailSmtp'];
    $fromName = $odc['NomeEmailSmtp'];
	$mail->SetFrom($from, $fromName);  
	$mail->Host = $odc['ServerSmtp'];				// Server SMTP
	$mail->Port = $odc['PortaSmtp'];				// Server SMTP Port
	$mail->Username = $odc['UserSmtp'];           	// SMTP account username
	$mail->Password = $odc['PwdSmtp'];      

    // SMTP account password
	$mail->Send();

	//aggiorno la data di invio della ricevuta via email
	$data = [
		'ScontrinoDataInvio' => date("Y-m-d H:i:s"),
	];
	$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
	
	
	echo json_encode(true);
	//return $result;
}

function fiscalGatewayEmettiNotaCredito($movimentoId){
	global $db;
	$service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);
	
	//recupero movimento
	$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
	$movimento = $db->query_first($sql);
	
	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
	$prenotazione = $db->query_first($sql);
	
	//recupero dell'ordine nota di credito scontrino
	$sql = "SELECT MAX(ScontrinoId) AS max FROM RT_PrenotazioneMovimento WHERE ScontrinoTipo = 2";
	$orderRow = $db->query_first($sql);
	if(isset($orderRow['max'])) {
		$creditNoteId = intval($orderRow['max']) + 1;
	} else {
		$creditNoteId = 1;
	}
	$creditNoteId = "N".strval($creditNoteId);
	
	//recupero ordine id a cui è legata la nota di credito
	$sql = "SELECT * FROM RT_PrenotazioneMovimento
		WHERE
			PrenotazioneId = ".$movimento['PrenotazioneId']." 
				AND TipoMovimento = 'I'
				AND PrenotazioneMovimentoId < ".$movimento['PrenotazioneMovimentoId']." 
				AND ScontrinoId IS NOT NULL";
	$orderRow = $db->query_first($sql);
	//se non è presente lo scontrino ritorno false e non emette la nota di credito
	if(!isset($orderRow['ScontrinoId'])) {
		return false;
	}
	$orderId = strval($orderRow['ScontrinoId']);
	
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
	$amount = (int) abs(round(floatval($movimento['ImportoPagato']) * 100));

	//recupero prodotto
	$productId = $prenotazione['CodicePrenotazione'];
	
	//invio richiesta emissione ricevuta con Fiscal Gateway
	//echo "$orderId, $creditNoteId, ".$movimento['Causale'].", $productId, $amount,". Config::$fiscalGatewayVAT; die();
	$result = $service->postBillCreditNote($orderId, $creditNoteId, $movimento['Causale'], $productId, $amount, Config::$fiscalGatewayVAT);

	if (isset($result['status_code'], $result['response']['success']) &&
		$result['status_code'] === 200 &&
		$result['response']['success'] === true) {
		//salvataggio info scontrino in PrenotazioneMovimento
		$data = [
			'ScontrinoId' => $orderId,
			'ScontrinoData' => date("Y-m-d H:i:s"),
			'ScontrinoTipo' => '2',
		];
		$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
		return true;
	} else {
		return false;
	}
}

function fiscalGatewayAnnullaRicevuta($movimentoId, $post = false){
	global $db;
	$service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);
	
	//recupero movimento
	$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
	$movimento = $db->query_first($sql);
	
	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
	$prenotazione = $db->query_first($sql);
	
	//recupero ordine id da annullare
	$sql = "SELECT * FROM RT_PrenotazioneMovimento
		WHERE
			PrenotazioneId = ".$movimento['PrenotazioneId']." 
				AND TipoMovimento = 'I'
				AND PrenotazioneMovimentoId <= ".$movimento['PrenotazioneMovimentoId']." 
				AND ScontrinoId IS NOT NULL";
	$orderRow = $db->query_first($sql);
	
	//se non è presente lo scontrino ritorno false e non emette la nota di credito
	if(!isset($orderRow['ScontrinoId'])) {
		if ($post) {
            echo json_encode(array('success' => false, 'message' => 'Scontrino non trovato'));
            return;
        }
        return false;
	}
	$orderId = strval($orderRow['ScontrinoId']);
	
	//invio richiesta annullamento ricevuta con Fiscal Gateway
	$result = $service->deleteBill($orderId);
	
	//memorizzo l'annullamento della ricevuta digitale
	if (isset($result['status_code'], $result['response']['success']) &&
		$result['status_code'] === 200 &&
		$result['response']['success'] === true) {
		//salvataggio info scontrino in PrenotazioneMovimento
		$data = [
			'ScontrinoIdAnnullato' => $orderId,
			'ScontrinoDataAnnullato' => date("Y-m-d H:i:s"),
		];
		$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
		if(abs($movimento['ImportoPagato']) < abs($orderRow['ImportoPagato'])) {
			//invio su fiscal gateway la differenza dell'importo totale e del rimborso crendo un nuovo scontrino
			//recupero id dell'ordine scontrino
			$newOrderId = $service->getNumeroOrdine($db);

			//recupero tipo pagamento
			$paymentMethodsType = $service->getTipoPagamento($orderRow['PagamentoTipoId']);

			//recupero importo
			$amount = (int) round(abs(floatval($orderRow['ImportoPagato']) - abs($movimento['ImportoPagato'])) * 100);

			//recupero prodotto
			$productId = $prenotazione['CodicePrenotazione'];

			//invio richiesta emissione ricevuta con Fiscal Gateway
			$result = $service->postBillReceipt($newOrderId, $paymentMethodsType, $amount, $productId, Config::$fiscalGatewayVAT);
			if (isset($result['status_code'], $result['response']['success']) &&
				$result['status_code'] === 200 &&
				$result['response']['success'] === true) {
					//salvataggio info scontrino in PrenotazioneMovimento
					$data = [
						'ScontrinoId' => $newOrderId,
						'ScontrinoData' => date("Y-m-d H:i:s"),
						'ScontrinoTipo' => '1',
						'ScontrinoInvioAuto' => $orderRow['ScontrinoInvioAuto'],
					];
					$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
					if($orderRow['ScontrinoInvioAuto'] == 1) {
						$dataMovimento = array();
						$dataMovimento['ScontrinoNotifica'] = 1;
						$updInviaTitolo=$db->update('RT_PrenotazioneMovimento', $dataMovimento,"PrenotazioneMovimentoId = ".$movimentoId);
					}
			}
		}
		if ($post) {
                echo json_encode(array('success' => true, 'message' => 'Ricevuta annullata con successo'));
                return;
            }
		return true;
	} elseif (isset($result['response']['success']) && 
			$result['response']['success'] === false &&
			isset($result['response']['error']['code']) &&
			$result['response']['error']['code'] === 106) {
		// Caso speciale: lo scontrino è già stato annullato (error code 106)
		// Memorizzo lo stesso l'annullamento senza fare altro
		$data = [
			'ScontrinoIdAnnullato' => $orderId,
			'ScontrinoDataAnnullato' => date("Y-m-d H:i:s"),
		];
		$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
		if ($post) {
			echo json_encode(array('success' => true, 'message' => 'Ricevuta già annullata'));
			return;
		}
		return true;
        
	} else {
		if ($post) {
			$errorMessage = isset($result['response']['error']['message']) ? 
				$result['response']['error']['message'] : 'Errore durante l\'annullamento della ricevuta';
			echo json_encode(array('success' => false, 'message' => $errorMessage));
			return;
		}
		return false;
	}
}

/*
 * function update($id) { global $user; $db= new Database(); $db->connect(); $storico=new StoricoOperazioni(); $storico->conn=$db; // prelevo i dati del form ed aggiorno tutte le proprietÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â  dell'oggetto $data=$_POST['Tratta']; $data=$storico->operazioni_update($data,$user); $result=$db->update("RT_Tratta", $data, "TrattaId=".$id." and OdcIdRef=$user->OdcId"); if ($result) echo("ok"); else echo("no"); exit(); }
 */

if (is_object($user)) {
	$db = new Database();
	$db->connect();
	$user->conn = $db;
	$permessi = $user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi) > 0) {

		if (!empty($_POST)) {
			switch ($_POST['action']) {
				

				case "FiscalGatewayEmettiRicevuta":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						fiscalGatewayEmettiRicevuta($_POST['MovimentoId']);
					else
						Errors::$ErrorePermessiModuloFunzione();
					break;
				
				case "FiscalGatewayInviaRicevuta":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						fiscalGatewayInviaRicevuta($_POST['MovimentoId']);
					else
						Errors::$ErrorePermessiModuloFunzione();
					break;
				
				case "FiscalGatewayDownloadRicevuta":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						fiscalGatewayDownloadRicevuta();
					else
						Errors::$ErrorePermessiModuloFunzione();
					break;
					
				case "FiscalGatewayEmettiNotaCredito":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						fiscalGatewayEmettiNotaCredito($_POST['MovimentoId']);
					else
						Errors::$ErrorePermessiModuloFunzione();
					break;

				case "FiscalGatewayAnnullaRicevuta":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso)) {
						if(isset($_POST['post']) && $_POST['post'] == 'true') {
							$post = true;
						} else {
							$post = false;
						}
						fiscalGatewayAnnullaRicevuta($_POST['MovimentoId'], $post);
					} else {
						Errors::$ErrorePermessiModuloFunzione();
					}
					break;
					
				case "ripristina_web":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						ripristinaPrenotazioneWeb();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "invioMessaggio":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						invioMessaggio();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "checkDuplicatoNominativo":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						checkDuplicatoNominativo();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "checkModificaPenale":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						CheckModificaPenale();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "RimborsoParziale":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						RimborsoParziale();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "RimborsoExtra":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						RimborsoExtra();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "GetPasseggeri":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						GetPasseggeri();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "GetImportoMassimoRimborsabile":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						GetImportoMassimoRimborsabile();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "create":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						create();
					else
						Errors::$ErrorePermessiModuloFunzione;

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni

					break;

				case "del":
					$FunzioneId = 3;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;

				case "update":
					if (isset($_POST['ModificaAnagrafica'])) {

						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							modifica_info_passeggeri();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}

					if (isset($_POST['AggiornaCorsa'])) {

						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							aggiorna_corsa();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}

					if (isset($_POST['Rimborsa'])) {
						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							rimborsa();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}
					if (isset($_POST['Annulla'])) {

						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							annulla();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}

					if (isset($_POST['AnnullaViaggio'])) {

						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							annulla(true);
						else
							Errors::$ErrorePermessiModuloFunzione;
					}

					if (isset($_POST['AnnullaForzato'])) {
						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							annulla_forzato();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}
					
					if (isset($_POST['ConfermaPrenotazione'])) {

						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							conferma_prenotazione();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}

					if (isset($_POST['Modifica'])) {
						$FunzioneId = 2;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							modifica();
						else
							Errors::$ErrorePermessiModuloFunzione;
					} else {

						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							modifica();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;
			}
		} elseif (!empty($_REQUEST)) {

			switch ($_REQUEST['do']) {
				case "stripe":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						stripe($_REQUEST['prenotazioneId']);
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;
				case "stripeLink":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						stripeLink($_REQUEST['prenotazioneId']);
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "stripeSendLink":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						stripeSendLink($_REQUEST['prenotazioneId']);
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "stripeSendLinkAgenzia":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						stripeSendLinkAgenzia($_REQUEST['prenotazioneId']);
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;
					
				case "CancellaMovimento":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						CancellaMovimento();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "ConfermaPrenotazione":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						ConfermaPrenotazione();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "ConfermaPrenotazioneLista":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						ConfermaPrenotazioneLista();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "GetCorse":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						GetCorse();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "GetFermate":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						GetFermate();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "GetTipologiaBiglietti":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						GetTipologiaBiglietti();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "AbilitaTipoPrenotazione":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						AbilitaTipoPrenotazione();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "GetNotePerTratta":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						GetNotePerTratta();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "MostraSchemaBus":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						MostraSchemaBus();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "EmettiTitoliDiViaggio":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						EmettiTitoliDiViaggio();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "EmettiTitoliExtra":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						EmettiTitoliExtra();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "getCorseOpt":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						getCorseOpt();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;

				case "GetCorsaRitornoAperta":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso))
						getCorsaRitornoAperta();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;
					
				
			}
		}
	} 	// end verifica permessi
	else {
		Errors::$ErrorePermessiModulo;
	}
}

// se l'utente non e' loggato
else {
	header("Location: /logout.php");
}
