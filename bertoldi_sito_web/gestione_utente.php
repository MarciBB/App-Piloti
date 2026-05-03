<?php

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.UtenteWeb.php");
include_once($classespath_ . "class.NotificaAutomaticaMessaggiInvio.php");
include_once($classespath_ . "class.StoricoOperazioni.php");
include_once($classespath_ . "class.Prenotazione.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Operatore.php");
include_once($classespath_ . "class.Sede.php");


global $db;
$db = new Database();
$db->connect();

if (!empty($_POST)) {
	switch ($_POST['action']) {
		case "login":
			login();
			break;
		case "logout":
			logout();
			break;
		case "registrazione":
			registrazione();
			break;
		case "registrazione-agenzia":
			registrazioneAgenzia();
			break;
		case "recupero-password":
			recupero();
			break;
		case "modifica-password":
			modificaPassword();
			break;
		case "modifica":
			modifica();
			break;
		case "getComune":
			getComune();
			break;
		case "checkMembershipCode":
			checkMembershipCode();
			break;
		case "rimborso":
			rimborso();
			break;
		case "checkCoupon":
			checkCodiceCoupon($_POST['codiceCoupon'],$_POST['comunePartenzaId'],$_POST['comuneArrivoId'],$_POST['dataAndata'],$_POST['dataRitorno'], $_POST['totale'], $_POST['lineaId'], $_POST['gestoreId'], $_POST['orarioCorsaAndata'], $_POST['orarioCorsaRitorno']);
			break;
		case "cambioLingua":
			cambioLingua($_POST['lang']);
			break;
		case "checkCodiceAgenzia":
			checkCodiceAgenzia($_POST['codiceAgenzia']);
			break;
	}
} else {
	if (!empty($_GET)) {
		switch ($_GET['action']) {

			case "validazione";
				validazione();
				break;
		}
	}
	echo json_encode(array('error' => 'action'));
}

function checkCodiceAgenzia($codiceAgenzia) {
	global $db;
	$db = new Database();
	$db->connect();
	$sql = "Select * FROM Gestore where CodiceAzienda = '$codiceAgenzia' and Stato = 1";
	$row = $db->query_first($sql);
	if(isset($row['GestoreId'])){
		//$_SESSION['gestore'] = $gestore;
    	//$_SESSION['code_gestore'] = '?code='.$codiceAgenzia;
		echo json_encode(array('result'=>true, 'agenzia'=>$row['RagioneSociale']));
	} else {
		//$_SESSION['gestore'] = -1;
        //$_SESSION['code_gestore'] = '';
		echo json_encode(array('result'=>false, 'agenzia'=>'Bertoldi Boats'));
	}

}

function checkCodiceCoupon($codice, $comunePartenzaId, $comuneArrivoId, $dataAndata, $dataRitorno = null, $totale, $lineaId = null, $gestoreId = null, $orarioCorsaAndata = null, $orarioCorsaRitorno = null){
	global $db;
	$db = new Database();
	$db->connect();
	$sql = "Select * FROM RT_Coupon WHERE Codice = '$codice' and MaxUtilizzi > Utilizzi and Stato = 1 AND (DaVendere = 0 OR (DaVendere = 1 AND VenditaStato = 2))";
	$row = $db->query_first($sql);

	if(isset($row['CouponId'])){
		
		$resultA = true;
		$resultR = true;
		$trattaA = true;
		$trattaR = true;
		$messageA = 'OK';
		$messageR = 'OK';
		if(!isset($dataRitorno)){
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
			$orarioAndata = isset($orarioCorsaAndata) ? $orarioCorsaAndata : null;
			if($orarioAndata && !isOrarioInFasce($orarioAndata, $fasceOrarie)) {
				$resultA = false;
				$messageA = "Fascia oraria non valida";
			}
			// Ritorno
			$orarioRitorno = isset($orarioCorsaRitorno) ? $orarioCorsaRitorno : null;
			if($orarioRitorno && !isOrarioInFasce($orarioRitorno, $fasceOrarie)) {
				$resultR = false;
				$messageR = "Fascia oraria non valida";
			}
		}

		//verifico se la data di partenza > di validaDa
		if(isset($row['ValidoDa']) && $row['ValidoDa'] != '0000-00-00'){
			$partenza = new DateTime($dataAndata);
			$partenza->setTime(0, 0, 0);
			$datetime = new DateTime($row['ValidoDa']);
			$datetime->setTime(0, 0, 0);
			if($partenza < $datetime){
				$resultA = false;
				$messageA = "Data di partenza non valida";
			}
			
			if(isset($dataRitorno)) {
				$partenza = new DateTime($dataRitorno);
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
			$partenza = new DateTime($dataAndata);
			$partenza->setTime(0, 0, 0);
			$datetime = new DateTime($row['ValidoA']);
			$datetime->setTime(0, 0, 0);
			if($partenza > $datetime){
				$resultA = false;
				$messageA = "Data di partenza non valida";
			}
				
			if(isset($dataRitorno)) {
				$partenza = new DateTime($dataRitorno);
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
			if($row['PartenzaId'] != $comunePartenzaId && !in_array($comunePartenzaId, $partenzaIds)){
				$trattaA = false;
				$resultA = false;
				$messageA = "Fermata di partenza non valida";
			}
			if(isset($dataRitorno) && ($row['PartenzaId'] != $comuneArrivoId && !in_array($comuneArrivoId, $partenzaIds))){
				$trattaR = false;
				$resultR = false;
				$messageR = "Fermata di partenza non valida";
			}
		}

		//verifico il comune di destinazione
		if(isset($row['DestinazioneId']) && $row['DestinazioneId']!=0){
			$destinazioneIds = array_map('trim', explode(',', $row['DestinazioneId']));
			if($row['DestinazioneId'] != $comuneArrivoId && $row['DestinazioneId'] != $comuneArrivoId && !in_array($comuneArrivoId, $destinazioneIds)){
				$trattaA = false;
				$resultA = false;
				$messageA = "Fermata di destinazione non valida";
			}
			if(isset($dataRitorno) && ($row['DestinazioneId'] != $comunePartenzaId && !in_array($comunePartenzaId, $destinazioneIds))){
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

		if(isset($row['LineaId']) && $row['LineaId']!=0){
			if($row['LineaId'] == $lineaId || strpos($row['LineaId']. ',', $lineaId . ',') === false){
				$resultA = false;
				$resultR = false;
				$messageA = "Esperienza non valida ".$row['LineaId']." - ".$lineaId;
			}
		}

		if($gestoreId == -1) {
			$gestoreId = 1;
		}
		if(isset($row['GestoreId']) && $row['GestoreId'] != -1){
			if($gestoreId != $row['GestoreId']){
				$resultA = false;
				$resultR = false;
				$messageA = "Agenzia non valida";
			}
		}

		if(($resultA && $resultR)) {
			echo json_encode(array('result'=>true, 'importo'=>$row['Valore'], 'percentuale'=>$row['Percentuale'], 'andata'=>$messageA, 'ritorno'=>$messageR));
		} else if((!$resultA && !$resultR) || (!$resultA && !isset($dataRitorno))) {
			echo json_encode(array('result'=>false, 'message'=>'Coupon non valido', 'andata'=>$messageA, 'ritorno'=>$messageR));
		} else {
			$importo = $totale*$row['Percentuale']/200;
			$importo = round($importo,1);
			echo json_encode(array('result'=>true, 'importo'=>"".number_format($importo, 2, ',', ' '), 'percentuale'=>0, 'andata'=>$messageA, 'ritorno'=>$messageR));
		}
	} else {
		echo json_encode(array('result'=>false, 'message'=>'Codice non valido'));
	}
}

function checkMembershipCode()
{
	global $db;
	$db = new Database();
	$db->connect();
	$codice = $_POST['code'];
	$sql = "SELECT * FROM RT_MembershipClub m
			left join RT_MembershipProfilo p on m.MembershipProfiloId = p.MembershipProfiloId
			WHERE m.MembershipClubCode = '$codice' AND m.Stato = 1 AND p.Stato = 1";
	$row = $db->fetch_array($sql);
	if (count($row) > 0) {
		echo json_encode(array('result' => true, 'profilo' => $row[0]));
	} else {
		echo json_encode(array('result' => false));
	}
}

function getComune()
{
	global $db;

	$db = new Database();
	$db->connect();
	$sql = "SELECT ComuneId, Comune FROM Comune c
		left join Provincia p on c.provincia = p.ProvinciaId
		left join Regione r on r.RegioneId = p.RegioneId
		left join Nazione n on r.idNazione = n.NazioneId
		where n.NazioneId = " . $_POST['nazione'] . " and c.Comune like '" . $_POST['comune'] . "%'";
	$result = $db->fetch_array($sql);
	echo json_encode(array('result' => $result));
}


function validazione()
{
	global $db;
	$utenteObj = new UtenteWeb();
	$utenteObj->conn = $db;
	$email = $_GET['email'];
	$codiceValidazione = $_GET['codiceValidazione'];

	$result = $utenteObj->validazione($email, $codiceValidazione);
	if ($result == false) {
		header("Location: /login.php?tipo=vf");
		die();
	} else {
		header("Location: /login.php?tipo=v");
		die();
	}
}

function registrazioneAgenzia()
{
	global $db, $dizionario;

	$sql = "SELECT * FROM Gestore WHERE PartitaIva = '" . $_POST['partitaIva'] . "' OR CodiceFiscale = '" . $_POST['codiceFiscale'] . "'";
	$row = $db->fetch_array($sql);
	if (count($row) > 0) {
		echo "-1";
		exit();
	}

	$sql = "SELECT * FROM Operatore WHERE Username = '" . $_POST['username'] . "' OR Email = '" . $_POST['emailRivendita'] . "'";
	$row = $db->fetch_array($sql);
	if (count($row) > 0) {
		echo "-2";
		exit();
	}

	$gestione = new Gestore();
	$gestione->conn = $db;
	$gestoreId = $gestione->registrazione(
		$_POST['ragioneSociale'],
		$_POST['partitaIva'],
		$_POST['codiceFiscale'],
		$_POST['indirizzo'],
		$_POST['cap'],
		$_POST['comune'],
		$_POST['telefono'],
		$_POST['fax'],
		$_POST['email'],
		$_POST['emailPec'],
		1,
		$_POST['privacy'],
		$_POST['privacy_accetto_1'],
		$_POST['privacy_accetto_2']
	);
	if ($gestoreId == false) {
		echo "-3";
		exit();
	}
	$sede = new Sede();
	$sede->conn = $db;
	$sedeId = $sede->registrazione($gestoreId, $_POST['comuneRivendita'], $_POST['indirizzoRivendita'], $_POST['telefonoRivendita'], $_POST['faxRivendita'], $_POST['emailRivendita'], $_POST['codiceSede']);

	$operatore = new Operatore();
	$operatore->conn = $db;
	$operatoreId = $operatore->registrazione($gestoreId, $_POST['username'], $_POST['password'], $_POST['cognome'], $_POST['nome'], $_POST['emailRivendita']);


	if ($operatoreId == false) {
		echo 'false';
		exit();
	} else {
		/**invio email**/
		$invioEmail = new NotificaAutomaticaMessaggiInvio();
		$Oggetto = "Onebus S.r.l. - Conferma la tua registrazione di agenzia";
		$Messaggio = $_POST['ragioneSociale'] . $dizionario['area_clienti']['email_agenzia_conferma'];
		$Messaggio .= '<br> Codice Rivendita assegnato: ' . $sedeId;
		$Destinatari = $_POST['email'];

		$invioEmail->InvioEmailSemplice($Messaggio, $Oggetto, $Destinatari, 1);

		/**invio email**/
		$invioEmail = new NotificaAutomaticaMessaggiInvio();
		$Oggetto = "Onebus S.r.l. - Nuova Agenzia";
		$Messaggio = "&Egrave; arrivata una nuova richiesta di registrazione di una agenzia."
			. "<br>Regione Sociale: " . $_POST['ragioneSociale'] . "<br>Partita IVA: " . $_POST['partitaIva']
			. "<br>Codice Fiscale: " . $_POST['codiceFiscale'] . "<br>Sede: " . $_POST['indirizzo'] . ", " . $_POST['cap'] . " " . $_POST['comune']
			. "<br>Telefono: " . $_POST['telefono'] . "<br>FAX: " . $_POST['fax'] . "<br>Email: " . $_POST['email'] . "<br>Email PEC: " . $_POST['emailPec']
			. "<br>Operatore: " . $_POST['cognome'] . " " . $_POST['nome'] . "<br>Nome utente: " . $_POST['username'];

		$Destinatari = 'agenzie@onebus.it';

		$invioEmail->InvioEmailSemplice($Messaggio, $Oggetto, $Destinatari, 1);

		echo 'true';
	}
}

function registrazione()
{
	global $db;
	$utenteObj = new UtenteWeb();
	$utenteObj->conn = $db;
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$result = $utenteObj->registrazione($_POST['nome'], $_POST['cognome'], $_POST['email'], $password, $_POST['numeroCellulare'], $_POST['sesso'], $_POST['codiceFiscale'], $_POST['partitaIva'], $_POST['comune'], $_POST['indirizzo'], $_POST['numeroPersonale'], $_POST['numeroFamiliare'], $_POST['cap'], $_POST['privacy'], $_POST['privacy_accetto_1'], $_POST['newsletter'], $_POST['privacy_accetto_2']);

	$profilo = $utenteObj->autenticazione($_POST['email'], $password, true);

	if ($result == false) {
		echo 'false';
	} else {
		/**invio email**/
		$invioEmail = new NotificaAutomaticaMessaggiInvio();
		$Oggetto = "Bertoldi Boats - Conferma la tua registrazione";
		$url = $_SERVER['SERVER_NAME'] . '/gestione_utente.php?action=validazione&email=' . $profilo['Email'] . '&codiceValidazione=' . $profilo['ValidazioneCodice'];
		$Messaggio = "La tua registrazione &egrave; andata a buon fine. Per poter confermare il tuo account ed accedere all'area riservata clicca sul seguente link:
			<br><br><a href='$url'>Conferma la tua registrazione</a>";
		$Destinatari = $profilo['Email'];

		$invioEmail->InvioEmailSemplice($Messaggio, $Oggetto, $Destinatari, 1);
		echo 'true';
	}
}

function modifica()
{
	global $db, $user;
	$utenteObj = new UtenteWeb();
	$utenteObj->conn = $db;
	if ($_POST['password'] != '') {
		$lastProf = $utenteObj->autenticazione($_POST['email'], $_POST['password']);
		if (isset($lastProf['Email'])) {
			$password = $_POST['password'];
		} else {
			$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		}
	} else {
		$userInfo = $_SESSION['USER'];
		$password = $userInfo['PasswordWeb'];
		// print_r($userInfo);
	}
	//$password="Testtest1";
	$result = $utenteObj->modifica($_POST['nome'], $_POST['cognome'], $_POST['email'], $_POST['numeroCellulare'], $_POST['sesso'], $_POST['codiceFiscale'], $_POST['partitaIva'], $_POST['comune'], $_POST['indirizzo'], $_POST['numeroTelefono'], $_POST['numeroFamiliare'], $password);
	$profilo = $utenteObj->autenticazione($_POST['email'], $password, true);

	if ($result == false) {
		echo json_encode(array('result' => 'false'));
	} else {
		$_SESSION['USER'] = $profilo;
		echo json_encode(array('result' => 'true', 'profilo' => $profilo));
	}
}

function cambioLingua($lang)
{
	$_SESSION['lang'] = $lang;
	echo json_encode(array('result' => 'true'));
}

function login()
{
	global $db;
	$email = $_POST['email'];
	$password = $_POST['password'];
	$utenteObj = new UtenteWeb();
	$utenteObj->conn = $db;

	$profilo = $utenteObj->autenticazione($_POST['email'], $password);
	if (isset($profilo['Email'])) {
		$_SESSION['USER'] = $profilo;
		if (isset($_POST['tipo']) && $_POST['tipo'] == "c") {
			echo json_encode(array('result' => 'truec', 'profilo' => $profilo));
		} else {
			echo json_encode(array('result' => 'true', 'profilo' => $profilo));
		}
	} else {
		echo json_encode(array('result' => 'false'));
	}
}

function logout()
{
	unset($_SESSION['USER']);
	echo json_encode(array('result' => 'true'));
}

function recupero()
{
	global $db;
	$email = $_POST['email'];
	$utenteObj = new UtenteWeb();
	$utenteObj->conn = $db;

	$profilo = $utenteObj->recuperoPassword($_POST['email']);

	if (isset($profilo['Email'])) {

		/**invio email**/
		$invioEmail = new NotificaAutomaticaMessaggiInvio();
		$Oggetto = "Bertoldi Boats - Modifica Password";
		$url = $_SERVER['SERVER_NAME'] . '/modifica-password.php?action=recupero&email=' . $profilo['Email'] . '&codiceRecupero=' . $profilo['RecuperoCodice'];
		$Messaggio = "E' stata richiesta la modifica della password dell'account associato a questa email, &egrave; possibile procedere con la modifica tramite questo link: 
		<br><br><a href='$url'>Modifica la tua password</a>";
		$Destinatari = $profilo['Email'];
		$invioEmail->InvioEmailSemplice($Messaggio, $Oggetto, $Destinatari, 1);

		echo json_encode(array('result' => 'true'));
	} else {
		echo json_encode(array('result' => 'false'));
	}
}

function modificaPassword()
{
	global $db;
	$email = $_POST['email'];
	$codice = $_POST['codice'];
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$utenteObj = new UtenteWeb();
	$utenteObj->conn = $db;

	$result = $utenteObj->modificaPassword($email, $password, $codice);
	if ($result == true) {
		echo json_encode(array('result' => 'true'));
	} else {
		echo json_encode(array('result' => 'false'));
	}
}

function rimborso()
{
	// 	ini_set('display_errors', 1);
	// 	ini_set('error_reporting', E_ALL);
	$titoloId = $_POST['titoloId'];
	global $user, $prenotazione_wizard, $db;
	$user = new UtenteWeb();
	$user->conn = $db;

	$sql = "select * from RT_PrenotazioneTitolo t
	left join RT_Prenotazione p on p.PrenotazioneId = t.PrenotazioneId
	where PrenotazioneTitoloId = $titoloId";
	$tempTitolo = $db->query_first($sql);

	$prenotazione_wizard = new Prenotazione($tempTitolo['PrenotazioneId']);
	$prenotazione_wizard->conn = $db;

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$dt = new DT();

	$PrenotazioneNumeroId = $tempTitolo['PrenotazioneNumeroId'];
	$Tragitto = $_POST['tragitto'];

	$sql = "select distinct Tragitto, RT_PrenotazioneDettaglio.LineaId,  RT_PrenotazioneDettaglio.DataPartenza,  RT_PrenotazioneDettaglio.OrarioPartenza
			from RT_PrenotazioneDettaglio
			where PrenotazioneNumero=" . $PrenotazioneNumeroId . " and Escludi=0";

	$ArrNumeroTragitti = $db->fetch_array($sql);
	$dataInizioCorsa = $ArrNumeroTragitti[0]['DataPartenza'] . " " . $ArrNumeroTragitti[0]['OrarioPartenza'];

	//calcolo penale
	$sql = "SELECT RimborsoRegolaId, NomeRegola, GiorniPrima, OrePrima FROM RT_RimborsoRegola
			Where TipoPrenotazione like '%N%' and Stato=1
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
	$selectDate = $regole[0]['RimborsoRegolaId'];
	foreach ($dateSoglia as $id => $dataCheck) {
		if ($today > $dataCheck) {
			$selectDate = $id;
		}
	}
	$sql = "Select * FROM RT_RimborsoPenale
	left join RT_RimborsoRegola on (RT_RimborsoRegola.RimborsoRegolaId = RT_RimborsoPenale.RimborsoRegolaId)
	where RT_RimborsoPenale.LineaId = 1 and RT_RimborsoPenale.RimborsoRegolaId = $selectDate";
	$penale = $db->fetch_array($sql);

	$ImportoConPenale = $tempTitolo['ImportoTitolo'] - $penale[0]['Fisso'] - ($tempTitolo['ImportoTitolo'] * $penale[0]['Percentuale'] / 100);

	if ($ImportoConPenale < 0) {
		$ImportoConPenale = 0;
	}

	if (($tempTitolo['TipoViaggioId'] == 2 && $Tragitto == '0') || ($tempTitolo['TipoViaggioId'] == 1 && $Tragitto == 'Andata')) {
		$ImportoRimborsabile = $ImportoConPenale * (-1);
	} else {
		$ImportoRimborsabile = round($ImportoConPenale / 2, 2) * (-1);
	}

	$TipoMovimento = 'R';
	$PagamentoTipoId = 12;
	$DataRimborso = date('Y-m-d H:i:s');

	//controllo emissione coupon
	$sql = "SELECT EmettiCoupon FROM RT_PagamentoTipo Where PagamentoTipoId = $PagamentoTipoId";
	$emettiCoupon = $db->query_first($sql);
	$codice = '';

	$dup = null;
	$dup['Escludi'] = 1;
	$dup = $storico->operazioni_update($dup, $user);

	if ($Tragitto == '0') {
		$db->update("RT_PrenotazioneDettaglio", $dup, "PrenotazioneNumero=$PrenotazioneNumeroId");
	} else {
		$db->update("RT_PrenotazioneDettaglio", $dup, "PrenotazioneNumero=$PrenotazioneNumeroId and Tragitto='$Tragitto'");
	}
	$sql = "select PrenotazioneId, TipologiaBigliettoId, PasseggeroId from RT_PrenotazioneNumero where PrenotazioneNumeroId=$PrenotazioneNumeroId";
	$row1 = $db->query_first($sql);

	$PrenotazioneId = $row1['PrenotazioneId'];
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
				$codice .= $characters[rand(0, strlen($characters) - 1)];
			}
			$movimento['Causale'] .= '<br>Codice coupon di rimborso: ' . $codice;
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
		$db->insert("RT_PrenotazioneMovimento", $movimento);
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
			left join RT_TipologiaBiglietto t on t.TipologiaBigliettoId = n.TipologiaBigliettoId
			where n.PrenotazioneNumeroId = $PrenotazioneNumeroId";
			$rowTempPosto = $db->query_first($sql);
			if ($rowTempPosto['OccupaPosto'] == 1) {
				$esclusi = $row1['PasseggeriEsclusi'] + 1;
			} else {
				$esclusi = $row1['PasseggeriEsclusi'];
			}
			$PrenotazionePercorsoId = $row1['PrenotazionePercorsoId'];
			$dup = null;
			$dup['PasseggeriEsclusi'] = $esclusi;
			$dup = $storico->operazioni_update($dup, $user);
			$db->update("RT_PrenotazionePercorso", $dup, "PrenotazionePercorsoId=$PrenotazionePercorsoId");
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
		$row = $storico->operazioni_insert($row, $user);

		$db->insert("RT_PrenotazioneDettaglio", $row);
		$del = $db->delete("RT_PrenotazionePosto", "PrenotazioneNumeroId=$PrenotazioneNumeroId and CorsaId=$CorsaId");
		$y++;
	}

	$PrenotazioneObj = new Prenotazione($PrenotazioneId);
	$PrenotazioneObj->conn = $db;
	$titoloId = $PrenotazioneObj->EmettiBigliettiRimborso($NuovaPrenotazioneNumero, $ImportoRimborsabile);
	// setta lo stato rimborsato se solo se � completamente rimborsata
	$PrenotazioneObj->isRimborsata($PrenotazioneId);
	$modifica = 0;


	//Emissione Coupon
	if ($emettiCoupon['EmettiCoupon'] == 1) {
		$userInfo = $_SESSION['USER'];
		$sql = "SELECT *
		FROM RT_PrenotazioneTitolo Where
		PrenotazioneId = $PrenotazioneId and PrenotazioneNumeroId = $NuovaPrenotazioneNumero and TipoTitolo = 'R'";
		$titoloInfo = $db->query_first($sql);
		$coupon['CouponNome'] = 'Rimborso per biglietto ' . $titoloInfo['Codice'] . '/' . $titoloInfo['Anno'];
		$coupon['Importo'] = -$ImportoRimborsabile;
		$coupon['MaxUtilizzi'] = 1;
		$coupon['Valore'] = $coupon['Importo'];
		$coupon['Utilizzi'] = 0;
		$coupon['Codice'] = $codice;
		$coupon['MembershipClubCode'] = $userInfo['MembershipClubCode'];
		$coupon = $storico->operazioni_insert($coupon, $user);
		$coupon['Cancella'] = 0;
		$result = $db->insert("RT_Coupon", $coupon);
		$invioEmail = new NotificaAutomaticaMessaggiInvio();
		$Oggetto = "Onebus S.r.l. - Conferma rimborso eseguita";
		$Messaggio = "La tua richiesta di rimborso &egrave; stata eseguita. &Egrave; stato emesso un coupon dal valori pari al rimborso, accedi alla pagina 'I miei coupon' per poter visualizzare il codice da utilizzare per i tuoi futuri acquisti.
				<br><br>Grazie per averci scelto.";
		$Destinatari = $userInfo['Email'];

		$invioEmail->InvioEmailSemplice($Messaggio, $Oggetto, $Destinatari, 1);
	}
}
