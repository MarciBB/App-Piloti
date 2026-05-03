<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_."class.DT.php");
include_once($classespath_."class.ServiceFatturaInCloud.php");
include_once($classespath_."class.FatturaInCloudViaggiatore.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Nazione.php");

global $ModuloId;
global $user;

$ModuloId = 2;// modulo base mediazione

if(is_object($user)) {
	$permessi=$user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0) {
		if (!empty($_POST)) {
			switch($_POST['action']) {
				case "add_invoice":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						elettiFattura();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
				case "add_receipt":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
					    elettiFattura();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
				case "add_credit_note":
				    $FunzioneId=2;
				    $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				    if (sizeof($permesso))
				        elettiFattura();
			        else
			            Errors::$ErrorePermessiModuloFunzione;
			            // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
	            break;

			}
		} // end verifica permessi
		else {
			Errors::$ErrorePermessiModulo;
		}
	}
}
// se l'utente non è loggato
else {
	header("Location: /logout.php");
}

/* versione singola */
function elettiFattura() {

	global $user;
	$db = new Database();
	$db->connect();
	$prenotazioneId = $_POST['PrenotazioneId'];
	$movimentoId = $_POST['MovimentoId'];
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
	
	$tipo = $_POST['Tipo'];
	$sql = "SELECT Max(Progressivo) as Ultimo
                FROM FatturaInCloudViaggiatore
                WHERE PrenotazioneId = $prenotazioneId and Tipo = '$tipo'";
	$temp1 = $db->query_first($sql);
	if(!isset($temp1['Ultimo'])) {
	    $progressivo = 1;
	} else {
	    $progressivo = $temp1['Ultimo'] +1;
	}
	
    switch ($tipo) {
        case 'invoice':
            $fatturaNumero = $progressivo."-OB/".date('Y');
            $siglaFattura = '-OB';
            break;
        case 'receipt':
            $fatturaNumero = $progressivo."-OBR/".date('Y');
            $siglaFattura = '-OBR';
            break;
        case 'credit_note':
            $fatturaNumero = $progressivo."-OBNC/".date('Y');
            $siglaFattura = '-OBNC';
            break;
    }
	
	$nome = $_POST['Nome'];
	$indirizzo_via = $_POST['IndirizzoVia'];
	$indirizzo_cap = $_POST['IndirizzoCap'];
	$indirizzo_provincia = $_POST['IndirizzoProvincia'];
	$indirizzo_citta = $_POST['IndirizzoComune'];
	$indirizzo_stato = $_POST['IndirizzoStato'];
	$nazione = new Nazione($indirizzo_stato);
	$nazione->conn=$db;
	$nazione->inizializzaDatiGenerali();
	$paese = $nazione->DatiGenerali['Nazione'];
	$paese_iso = $nazione->DatiGenerali['ISO2'];
	$lingua = 'it';
	$piva = $_POST['PIVA'];
	$cf = $_POST['CF'];
	$pec = $_POST['PEC'];
	$codice_destinatario = $_POST['Codice_destinatario'];
	$email = $_POST['Email'];
	$tel = $_POST['Tel'];
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
	$pagamento_data_scadenza = $_POST['FatturaData'];
	$pagamento_data_saldo = $_POST['FatturaData'];
	$fa_data = $_POST['FatturaData'];
	if($_POST['FatturaStato'] == 0) {
	   $statoFattura = 'SALDATO';
	} else {
	    $statoFattura = 'NON SALDATO';
	}
	$service = new ServiceFatturaInCloud($db);
	$result = $service->inviaFatturaCliente($nome, $indirizzo_via, $indirizzo_cap, $indirizzo_provincia, $indirizzo_citta,
	    $paese, $paese_iso, $lingua, $piva, $cf, $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto,
	    $articolo_prezzo_lordo, $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
	    $email, $tel, $fax, $fa_data, $fatturaNumero, $progressivo, $siglaFattura, $tipo, $statoFattura,
	    $prenotazione['CodicePrenotazione'], $prenotazioneId, $movimentoId, null, $movimento['PagamentoTipo']);
    
	$result = ['PrenotazioneId' => $prenotazioneId, 'CorsaId' => ""];
	echo json_encode($result);
}

?>