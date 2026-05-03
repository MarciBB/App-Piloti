<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."Graph/class.LineaGraph.php");


$ModuloId=28;



global $percorso_wizard;
$percorso_wizard=null;

if(isset($_SESSION['PERCORSO_WIZARD'])) {
$percorso_wizard=unserialize($_SESSION['PERCORSO_WIZARD']);
}


function DuplicaCorsa($CorsaId) {
    global $user, $percorso_wizard, $db;

    // Seleziona la corsa specificata dal database, filtrando per CorsaId e OdcId dell'utente
    $sql = "SELECT * FROM RT_Corsa WHERE CorsaId = $CorsaId AND OdcIdRef = $user->OdcId";
    $ArrObject = $db->query_first($sql);

    // Rimuove l'ID della corsa per evitare duplicati e modifica il nome della corsa
    $ArrObject['CorsaId'] = null;
    unset($ArrObject['CorsaId']);
    $ArrObject['CorsaNome'] = $ArrObject['CorsaNome'] . " duplicata"; // Aggiunge "duplicata" al nome
    $ArrObject['OpeIns'] = $user->OperatoreId; // Inserisce l'ID dell'operatore
    $ArrObject['SedeIns'] = $user->SedeId; // Inserisce l'ID della sede
    $ArrObject['DataIns'] = date('Y-m-d H:i:s'); // Data di inserimento
    $ArrObject['IpIns'] = getenv('REMOTE_ADDR'); // IP dell'utente che inserisce
    $ArrObject['OdcIdRef'] = $user->OdcId; // Riferimento dell'Odc dell'utente
    $ArrObject['GestoreIdRef'] = $user->GestoreId; // Riferimento del gestore dell'utente

    // Inserisce la corsa duplicata nella tabella RT_Corsa e ottiene l'ID dell'ultima corsa inserita
    $lastidA = $db->insert("RT_Corsa", $ArrObject);

    // Definisce le colonne da inserire per le tabelle RT_CorsaSettimana, RT_CorsaTariffa e RT_Orario
    $sql_basic = 'DataIns, OpeIns, SedeIns, IpIns, OdcIdRef, GestoreIdRef';
    $sql_basic_ins = "'" . date('Y-m-d H:i:s') . "', " . $user->OperatoreId . ", " . $user->SedeId . ", '" . getenv('REMOTE_ADDR') . "', " . $user->OdcId . ", " . $user->GestoreId;

    // Se l'inserimento della corsa duplicata è riuscito
    if ($lastidA != false) {
        // Duplica le settimane associate alla corsa
        $sql = "SELECT $sql_basic_ins, $lastidA, SettimanaId, Stato, Cancella FROM RT_CorsaSettimana WHERE CorsaId = $CorsaId AND OdcIdRef = $user->OdcId";
        $sql1 = "INSERT INTO RT_CorsaSettimana ($sql_basic, CorsaId, SettimanaId, Stato, Cancella) $sql";
        $db->query($sql1);
    }

    // Duplica le tariffe associate alla corsa
    if ($lastidA != false) {
        $sql = "SELECT $sql_basic_ins, $lastidA, TipologiaBigliettoId, FermataPickup, FermataDropOff, TrattaId, ListinoId, Tariffa, Stato, Cancella FROM RT_CorsaTariffa WHERE CorsaId = $CorsaId AND OdcIdRef = $user->OdcId";
        $sql1 = "INSERT INTO RT_CorsaTariffa ($sql_basic, CorsaId, TipologiaBigliettoId, FermataPickup, FermataDropOff, TrattaId, ListinoId, Tariffa, Stato, Cancella) $sql";
        $db->query($sql1);
    }

    // Duplica gli orari associati alla corsa
    if ($lastidA != false) {
        $sql = "SELECT $sql_basic_ins, $lastidA, Orario, GiorniAggiuntivi, FermataId, Stato, Cancella FROM RT_Orario WHERE CorsaId = $CorsaId AND OdcIdRef = $user->OdcId";
        $sql1 = "INSERT INTO RT_Orario ($sql_basic, CorsaId, Orario, GiorniAggiuntivi, FermataId, Stato, Cancella) $sql";
        $db->query($sql1);
    }
	
	 // Duplica i record di validità del biglietto
    if ($lastidA != false) {
        // Seleziona i dettagli di validità del biglietto associati alla corsa originale
        $sql = "SELECT * FROM RT_ValiditaBiglietto WHERE CorsaId = $CorsaId ";
		
		$validitaBigliettoResult = $db->fetch_array($sql);

        // Esegui il ciclo sui risultati per inserire ogni record duplicato
        foreach ($validitaBigliettoResult as $validitaBiglietto) {
			//memorizzo l'id della validità vecchia
			$oldIdValidita = $validitaBiglietto['ValiditaBigliettoId'];
            // Rimuove l'ID della validità del biglietto e aggiorna le informazioni di inserimento
            unset($validitaBiglietto['ValiditaBigliettoId']);
            $validitaBiglietto['CorsaId'] = $lastidA; // Imposta il nuovo CorsaId
            $validitaBiglietto['DataIns'] = date('Y-m-d H:i:s');
            $validitaBiglietto['OpeIns'] = $user->OperatoreId;
            $validitaBiglietto['SedeIns'] = $user->SedeId;
            $validitaBiglietto['IpIns'] = getenv('REMOTE_ADDR');
            $validitaBiglietto['OdcIdRef'] = $user->OdcId;
            $validitaBiglietto['GestoreIdRef'] = $user->GestoreId;

            // Inserisce il record duplicato nella tabella RT_ValiditaBiglietto
            $validitaBigliettoId = $db->insert("RT_ValiditaBiglietto", $validitaBiglietto);

            // Duplica i dettagli di validità del biglietto associati alla validità appena inserita
            if ($validitaBigliettoId != false) {
                $sqlDetail = "SELECT * FROM RT_ValiditaBigliettoDettaglio WHERE ValiditaBigliettoId = " . $oldIdValidita;
                $validitaBigliettoDettaglioResult = $db->fetch_array($sqlDetail);
				
				//var_dump($validitaBigliettoDettaglioResult);
                foreach( $validitaBigliettoDettaglioResult as $dettaglio ) {
                    // Rimuove l'ID del dettaglio e aggiorna il ValiditaBigliettoId
                    unset($dettaglio['ValiditaBigliettoId']);
                    $dettaglio['ValiditaBigliettoId'] = $validitaBigliettoId; // Imposta il nuovo ID di validità
                    $dettaglio['DataIns'] = date('Y-m-d H:i:s');
                    $dettaglio['OpeIns'] = $user->OperatoreId;
                    $dettaglio['SedeIns'] = $user->SedeId;
                    $dettaglio['IpIns'] = getenv('REMOTE_ADDR');
                    $dettaglio['OdcIdRef'] = $user->OdcId;
                    $dettaglio['GestoreIdRef'] = $user->GestoreId;

                    // Inserisce il record duplicato nella tabella RT_ValiditaBigliettoDettaglio
                    $db->insert("RT_ValiditaBigliettoDettaglio", $dettaglio);
                }
            }
        }
    }
}

function cambia_stato()
{
    global $user,$percorso_wizard,$db;
    $storico=new StoricoOperazioni();
	$storico->conn=$db;
    
    $GestoreId=$percorso_wizard->GestoreId;
    
    $data['Stato'] = $_POST['Stato'];
    $data=$storico->operazioni_update($data,$user);
    
    $res=$db->update('Gestore', $data, "GestoreId=".$GestoreId);
	echo("Lo stato del gestore e' stato cambiato");
	exit();
}

function cancella_linea()
{
    global $user, $percorso_wizard, $db;
    $storico = new StoricoOperazioni();
	$storico->conn = $db;
    
	$lineaId = $_POST['LineaId'];
	
    $data['Cancella'] = 1;
	$data['Stato'] = 0;
    $data = $storico->operazioni_update($data, $user);
    
    $res = $db->update('RT_Corsa', $data, "LineaId = ".$lineaId);
	
	$res = $db->update('RT_Tratta', $data, "LineaId = ".$lineaId);
	
	$res = $db->update('RT_Linea', $data, "LineaId = ".$lineaId);
	echo("La linea è stata cancellata");
	exit();
}

function disattiva_linea()
{
    global $user, $percorso_wizard, $db;
    $storico = new StoricoOperazioni();
	$storico->conn = $db;
    
	$lineaId = $_POST['LineaId'];
	
    $data['Cancella'] = 0;
	$data['Stato'] = 0;
    $data = $storico->operazioni_update($data, $user);
    
    $res=$db->update('RT_Linea', $data, "LineaId = ".$lineaId);
	echo("La linea è stata disattivata");
	exit();
}

function check_linea()
{
    global $user, $percorso_wizard, $db;
    $storico = new StoricoOperazioni();
	$storico->conn = $db;
    
	$lineaId = $_POST['LineaId'];
	$sql = "SELECT count(*) as tot FROM RT_PrenotazionePercorso 
			WHERE LineaId = ".$lineaId." AND 
			(PrenotazioneStato = 1 OR PrenotazioneStato = 3) 
			AND Stato = 1 AND Cancella = 0";
	$row = $db->query_first($sql);
    
    if(isset($row['tot']) && $row['tot'] == 0) {
		echo("ok");
	} else {
		echo("no");
	}
	exit();
}


function create() {
	global $user,$db,$percorso_wizard;

	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$dt = new DT();
	$data = $_POST['Percorso'];

	$data = $storico->operazioni_insert($data,$user);
	$lastidA = $db->insert("RT_Percorso", $data);
	if ($lastidA!=false) {
		
		$percorso_wizard=new Percorso($lastidA);
		$percorso_wizard->conn=$db;
		$percorso_wizard->inizializzaDatiGenerali();
		$_SESSION['PERCORSO_WIZARD']=serialize($percorso_wizard);
		
		echo("ok".",".$lastidA);    
	} else {
		echo("no");
	}
	$db->close();
	exit(); 
}




function update() {
    global $user, $percorso_wizard, $db;

    // Recupera l'ID del percorso dal wizard
    $Id = $percorso_wizard->Id;

    // Inizializza la classe StoricoOperazioni per tracciare le operazioni
    $storico = new StoricoOperazioni();
    $storico->conn = $db;
    $dt = new DT();

    // Ottiene i dati relativi al passo corrente e al percorso
    $step_corrente = $_POST['step_corrente'];
    $data = $_POST['Percorso'];

    // Registra l'operazione di aggiornamento nel registro storico
    $data = $storico->operazioni_update($data, $user);

    // Esegue l'aggiornamento del percorso nel database
    $result = $db->update("RT_Percorso", $data, "PercorsoId=" . $Id);

    // Controlla il risultato dell'aggiornamento e restituisce un esito
    if ($result) {
        echo("ok");
        $db->close();
        exit();
    } else {
        echo("no");
        $db->close();
        exit();   
    }    
}



function resetPercorsoBreve()
{
    
   global $user,$percorso_wizard,$db;
    $Id=$percorso_wizard->Id; 
    $sql="truncate table RT_PercorsoBreve";
    $db->query($sql);
    
    $sql="truncate table RT_PercorsoBreveTratte";
    $db->query($sql);
    
    $sql="truncate table RT_PercorsoBreveWeb";
    $db->query($sql);
    
    $sql="truncate table RT_PercorsoBreveWebTratte";
    $db->query($sql);
    echo("ok");
}
  
function create_tariffe() {
	global $user,$db;
	/*
	 $db = new Database();
	 $db->connect();*/
	$storico=new StoricoOperazioni();
	$storico->conn=$db;

	$dt=new DT();
	$post2 = getRealPOSTTariffe();
	$data=$post2['Prezzi'];
	$LineaId = $_POST['LineaId'];

	//segno le vecchie tariffe della linea da sostituire
	$dataUpdate['Salva'] = 1;
	$res=$db->update('RT_LineaTariffa', $dataUpdate, "LineaId=$LineaId");
	
	//recupero le corse da aggiornare con le nuove tariffe
	$oggi = date('Y-m-d');
	$sql = "Select * from RT_Corsa where  AttivaAl>='$oggi' and Cancella = 0 and LineaId = $LineaId order by CorsaPeso asc";
	$ArrObjectCorse = $db->fetch_array($sql);
	
	//cancello le tariffe della corsa
	foreach($ArrObjectCorse as $corsa){
		$db->delete("RT_CorsaTariffa", "CorsaId = ".$corsa['CorsaId']);
	}

	
	//inizio a scrivere la queri di inserimento in LineaTariffa
	$q="INSERT INTO RT_LineaTariffa
	(`TrattaId`, `LineaId`, `TipologiaBigliettoId`, `FermataPickup`, `FermataDropOff`, `Tariffa`, `OpeIns`, `SedeIns`, `DataIns`, `IpIns`, `OdcIdRef`, `GestoreIdRef`, `Cancella`, `Stato`)
	VALUES ";
	$count = 0;

	foreach ($data as $chiave => $tariffa) {
		$v='';

		$chiave=str_replace("'","",$chiave);
		$chiave=str_replace("\\","",$chiave);

		$arr_chiave=explode('_', $chiave);
		$TipologiaBigliettoId=$arr_chiave[1];
		$LineaId=$arr_chiave[0];
		$PkId=$arr_chiave[2];
		$DoffId=$arr_chiave[3];
		
		//recupero le variazioni di prezzo delle varie tipologie di prezzo
		$sql = "Select * from RT_TipologiaBiglietto where Stato = 1 and Cancella = 0 AND OccupaPosto = 1 
		AND ((TipoPrezzo = 1 AND TipoBigliettoIdRiferimento = ".$TipologiaBigliettoId.") OR TipologiaBigliettoId = ".$TipologiaBigliettoId.")
		order by TipologiaBigliettoPeso asc ";
		$ArrObjectTipoBiglietto = $db->fetch_array($sql);

		$sql = "Select l.* from RT_ListinoTipo l
			left join RT_TipologiaBiglietto b ON b.TipologiaBigliettoId = l.BigliettoId
			WHERE b.TipoBigliettoIdRiferimento = ".$TipologiaBigliettoId;
	
		$ArrVariazioni = $db->fetch_array($sql);
		$variazioni = array();
		$stringaBiglietti = '';
		foreach($ArrVariazioni as $var){
			$variazioni[$var['BigliettoId']] = $var['Variazione'];
			$stringaBiglietti .= $var['BigliettoId'].',';
		}
		$variazioni[$TipologiaBigliettoId] = 0;
		$stringaBiglietti .= $TipologiaBigliettoId;

		//continuo query di inserimento in linea tariffa
		$d1['TrattaId'] = 0;
		$d1['LineaId'] = $LineaId;
		$d1['TipologiaBigliettoId'] = $TipologiaBigliettoId;
		$d1['FermataPickup'] = $PkId;
		$d1['FermataDropOff'] = $DoffId;
		// 	    $d1['ListinoId'] = 1 ;
		$d1['Tariffa'] = floatval($tariffa);

		$d1=$storico->operazioni_insert($d1,$user);

		foreach($d1 as $key => $val){
			if(strtolower($val)=='null') $v.="NULL, ";
			elseif(strtolower($val)=='now()') $v.="NOW(), ";
			else $v.= "'".$db->escape($val)."', ";
		}

		$q .= " (". rtrim($v, ', ') .")";
		if($count != (sizeof($data)-1)){
			$q .= ",";
		}

		//inizio a scrivere la queri di inserimento in CorsaTariffa
		$qCorsa="INSERT INTO RT_CorsaTariffa
				(`TipologiaBigliettoId`, `TrattaId`, `CorsaId`, `FermataPickup`, `FermataDropOff`, `ListinoId`, `Tariffa`, `OpeIns`, `SedeIns`, `DataIns`, `IpIns`, `OdcIdRef`, `GestoreIdRef`, `Cancella`, `Stato`)
				VALUES ";
		$countCorsa = 0;
		$limite = (count($ArrObjectCorse)*count($ArrObjectTipoBiglietto))-1;
		
		foreach ($ArrObjectCorse as $corsa){
			 
			foreach ($ArrObjectTipoBiglietto as $tipoBiglietto){
				$dc['TipologiaBigliettoId'] = $tipoBiglietto['TipologiaBigliettoId'];
				$dc['TrattaId'] = 0;
				$dc['CorsaId'] = $corsa['CorsaId'];
				$dc['FermataPickup'] = $PkId;
				$dc['FermataDropOff'] = $DoffId;
				$dc['ListinoId'] = 1 ;
				$dc['Tariffa'] = floatval($tariffa + ($variazioni[$tipoBiglietto['TipologiaBigliettoId']]*$tariffa)/100);

				$dc=$storico->operazioni_insert($dc,$user);

				$vc='';
				foreach($dc as $key => $val){
					if(strtolower($val)=='null') $vc.="NULL, ";
					elseif(strtolower($val)=='now()') $vc.="NOW(), ";
					else $vc.= "'".$db->escape($val)."', ";
				}

				$qCorsa .= " (". rtrim($vc, ', ') .")";
				if($countCorsa != $limite){
					$qCorsa .= ",";
				}
				$countCorsa++;
			}
			 

		}
		//Inserisco le nuove tariffe delle corse
		$db->query($qCorsa);


		$count++;
	}

	//dopo l'inderimento delle tariffe della linea effettuo la cancellazione delle vecchie tariffe
	$db->query($q);
	$db->delete("RT_LineaTariffa", "LineaId=$LineaId and Salva = 1");

	echo("stop");

	exit();
}

function create_tariffe_comune() {
	global $user,$db;
	/*
	 $db = new Database();
	 $db->connect();*/
	$storico=new StoricoOperazioni();
	$storico->conn=$db;

	$dt=new DT();
	$post2 = getRealPOSTTariffe();
	$data=$post2['Prezzi'];
	$LineaId = $_POST['LineaId'];
	$ComuneId = $_POST['ComuneId'];
	$TipologiaBigliettoId = $_POST['TipologiaBigliettoId'];

	//segno le vecchie tariffe della linea da sostituire
	$dataUpdate['Salva'] = 1;
	$res=$db->update('RT_LineaTariffa', $dataUpdate, "LineaId=$LineaId AND FermataPickup = $ComuneId AND TipologiaBigliettoId = $TipologiaBigliettoId");

	//recupero le corse da aggiornare con le nuove tariffe
	$oggi = date('Y-m-d');
	$sql = "Select * from RT_Corsa where  AttivaAl>='$oggi' and Cancella = 0 and LineaId = $LineaId order by CorsaPeso asc";
	$ArrObjectCorse = $db->fetch_array($sql);

	//recupero le variazioni di prezzo delle varie tipologie di prezzo
	$sql = "Select * from RT_TipologiaBiglietto where Stato = 1 and Cancella = 0 AND OccupaPosto = 1 
		AND ((TipoPrezzo = 1 AND TipoBigliettoIdRiferimento = ".$TipologiaBigliettoId.") OR TipologiaBigliettoId = ".$TipologiaBigliettoId.")
		order by TipologiaBigliettoPeso asc ";
	$ArrObjectTipoBiglietto = $db->fetch_array($sql);

	$sql = "Select l.* from RT_ListinoTipo l
		left join RT_TipologiaBiglietto b ON b.TipologiaBigliettoId = l.BigliettoId
		WHERE b.TipoBigliettoIdRiferimento = ".$TipologiaBigliettoId;
	
	$ArrVariazioni = $db->fetch_array($sql);
	$variazioni = array();
	$stringaBiglietti = '';
	foreach($ArrVariazioni as $var){
		$variazioni[$var['BigliettoId']] = $var['Variazione'];
		$stringaBiglietti .= $var['BigliettoId'].',';
	}
	$variazioni[$TipologiaBigliettoId] = 0;
	$stringaBiglietti .= $TipologiaBigliettoId;

	//cancello le tariffe della corsa
	foreach($ArrObjectCorse as $corsa){
		$db->delete("RT_CorsaTariffa", "CorsaId = ".$corsa['CorsaId']." AND FermataPickup = $ComuneId AND TipologiaBigliettoId IN ($stringaBiglietti)");
	}
	
	//inizio a scrivere la queri di inserimento in LineaTariffa
	$q="INSERT INTO RT_LineaTariffa
	(`TrattaId`, `LineaId`, `TipologiaBigliettoId`, `FermataPickup`, `FermataDropOff`, `Tariffa`, `OpeIns`, `SedeIns`, `DataIns`, `IpIns`, `OdcIdRef`, `GestoreIdRef`, `Cancella`, `Stato`)
	VALUES ";
	$count = 0;

	foreach ($data as $chiave => $tariffa) {
		$v='';

		$chiave=str_replace("'","",$chiave);
		$chiave=str_replace("\\","",$chiave);

		$arr_chiave=explode('_', $chiave);
		$TipologiaBigliettoId=$arr_chiave[1];
		$LineaId=$arr_chiave[0];
		$PkId=$arr_chiave[2];
		$DoffId=$arr_chiave[3];
		 
		
		$d1['TrattaId'] = 0;
		$d1['LineaId'] = $LineaId;
		$d1['TipologiaBigliettoId'] = $TipologiaBigliettoId;
		$d1['FermataPickup'] = $PkId;
		$d1['FermataDropOff'] = $DoffId;
		// 	    $d1['ListinoId'] = 1 ;
		$d1['Tariffa'] = floatval($tariffa);

		$d1=$storico->operazioni_insert($d1,$user);

		foreach($d1 as $key => $val){
			if(strtolower($val)=='null') $v.="NULL, ";
			elseif(strtolower($val)=='now()') $v.="NOW(), ";
			else $v.= "'".$db->escape($val)."', ";
		}

		$q .= " (". rtrim($v, ', ') .")";
		if($count != (sizeof($data)-1)){
			$q .= ",";
		}

		//inizio a scrivere la queri di inserimento in CorsaTariffa
		$qCorsa="INSERT INTO RT_CorsaTariffa
				(`TipologiaBigliettoId`, `TrattaId`, `CorsaId`, `FermataPickup`, `FermataDropOff`, `ListinoId`, `Tariffa`, `OpeIns`, `SedeIns`, `DataIns`, `IpIns`, `OdcIdRef`, `GestoreIdRef`, `Cancella`, `Stato`)
				VALUES ";
		$countCorsa = 0;
		$limite = (count($ArrObjectCorse)*count($ArrObjectTipoBiglietto))-1;

		foreach ($ArrObjectCorse as $corsa){

			foreach ($ArrObjectTipoBiglietto as $tipoBiglietto){
				$dc['TipologiaBigliettoId'] = $tipoBiglietto['TipologiaBigliettoId'];
				$dc['TrattaId'] = 0;
				$dc['CorsaId'] = $corsa['CorsaId'];
				$dc['FermataPickup'] = $PkId;
				$dc['FermataDropOff'] = $DoffId;
				$dc['ListinoId'] = 1 ;
				$dc['Tariffa'] = floatval($tariffa + ($variazioni[$tipoBiglietto['TipologiaBigliettoId']]*$tariffa)/100);

				$dc=$storico->operazioni_insert($dc,$user);

				$vc='';
				foreach($dc as $key => $val){
					if(strtolower($val)=='null') $vc.="NULL, ";
					elseif(strtolower($val)=='now()') $vc.="NOW(), ";
					else $vc.= "'".$db->escape($val)."', ";
				}

				$qCorsa .= " (". rtrim($vc, ', ') .")";
				if($countCorsa != $limite){
					$qCorsa .= ",";
				}
				$countCorsa++;
			}


		}
		//Inserisco le nuove tariffe delle corse
		$db->query($qCorsa);
 		echo $qCorsa;


		$count++;
	}

	//dopo l'inderimento delle tariffe della linea effettuo la cancellazione delle vecchie tariffe
	$db->query($q);
// 	echo $q;
	$db->delete("RT_LineaTariffa", "LineaId=$LineaId and Salva = 1");

	echo("stop");

	exit();
}



function getRealPOSTTariffe() {
	$pairs = explode("&", file_get_contents("php://input"));
	$vars = array();
	foreach ($pairs as $pair) {
		$nv = explode("=", $pair);
		$name = urldecode($nv[0]);
		$value = urldecode($nv[1]);
		if (strpos($name,'Prezzi') !== false) {
			$temp = explode("[", $name);
			$nameT = $temp[0];
			$index = $temp[1];
			$index = trim($index, "]");
			$vars[$nameT][$index] = $value;
		} else {
			$vars[$name] = $value;
		}
	}
	return $vars;
}

function getComuneTariffa(){
	global $HtmlCommon,$db,$percorso_wizard,$funzione_edit,$abilita_modifica, $dizionario;

	$linea=new Linea();
	$linea->Id=$_GET['LineaId'];
	$linea->conn=$db;
	$linea->inizializzaDatiGenerali();
	$arrLinea=$linea->DatiGenerali;
	
	$page = new Form();
	$dt = new DT();
	$storico = new StoricoOperazioni();
	$storico->conn=$db;

	$ComuneId = $_GET['ComuneId'];
	$LineaId = $_GET['LineaId'];
	
	
	$pickupId = $ComuneId;
	$page->create_textbox_hidden("LineaId", $LineaId);
	$grafo = new LineaGraph($LineaId, null, null, $db, false, null, 0, true);
		
	$sql = "SELECT * FROM RT_Fermata f
	LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId
	WHERE ComuneId = '$pickupId' AND t.LineaId = $LineaId and IsPickup = 1";
	
	$ArrObjectCorse = $db->fetch_array($sql);
	if(sizeof($ArrObjectCorse) > 0){
		$sql = "SELECT Comune FROM Comune
		WHERE ComuneId = $pickupId";
		$infoPickup = $db->query_first($sql);
		echo "<br><h3>".$infoPickup['Comune']."</h3>";
		 
		$riga = 0;
		?>
		<table>
		<?php 
			$comune = $grafo->graph->nodes[$pickupId];
    		foreach ($comune->descents as $dropoffId => $discesa){
    			if($riga == 0){
    				echo "<tr style='width: 7% !important; vertical-align:center;'>";
    			}
    			$sql = "SELECT Comune FROM RT_Fermata f
    			LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId 
    			LEFT JOIN Comune c ON c.ComuneId = f.ComuneId
    			WHERE f.ComuneId = $dropoffId AND t.LineaId = $LineaId and f.IsDropoff = 1";
		    	$ArrObjectCorse = $db->query_first($sql);
		    	
		    	if(isset($ArrObjectCorse['Comune'])){
					$sql = "SELECT Tariffa FROM RT_LineaTariffa t
					where t.FermataDropoff = $dropoffId AND t.FermataPickup = $pickupId AND LineaId = $LineaId";
					
					$info = $db->query_first($sql);
					if(isset($info['Tariffa'])){
						$prezzo = $info['Tariffa'];
					} else {
						$prezzo = 0;
					}
		    		?>
	    		<td style="vertical-align:center; max-width: 80px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><label for="Prezzi['<?= $LineaId . "_" . $pickupId . "_" . $dropoffId ?>']"><?php echo $ArrObjectCorse['Comune'];?></label>
	    		<input class="numberDE" type="text" name="Prezzi['<?= $LineaId . "_" . $pickupId . "_" . $dropoffId ?>']" value="<?= $prezzo ?>" SIZE="7">
	    		</td>
	    		<?php
		    		$riga++;
		    	}
		    	if($riga >=14){
		    		$riga = 0;
		    		echo "</tr>";
		    	}
    		}
    		?>
    		</table>
    		<?php 
	    	}
        ?>
        </div>
	<?php 
	
	
}

if(is_object($user)) {
    
	$db= new Database();
	$db->connect();
	$user->conn=$db;
	$permessi=$user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0) {   	
		if (!empty($_POST)) {
			switch($_POST['action']) {
	            case "create":
	            	$FunzioneId=2;
	                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
	                if (sizeof($permesso))
	                	create();   
	                else
	                	Errors::$ErrorePermessiModuloFunzione;
	                break;
	            case "update":
	            	$FunzioneId=4;
	                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
	                if (sizeof($permesso))
	                	update();   
	                else
	                	echo("no");
	                break;
	            case "cambia_stato":
	            	$FunzioneId=4;
	                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
	                if (sizeof($permesso))
	                	cambia_stato();    
	                else
	                	echo("no");
	                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
					break;
				case "cancella_linea":
	            	$FunzioneId=4;
	                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
	                if (sizeof($permesso))
	                	cancella_linea();    
	                else
	                	echo("no");
	                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
					break;
				case "disattiva_linea":
	            	$FunzioneId=4;
	                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
	                if (sizeof($permesso))
	                	disattiva_linea();    
	                else
	                	echo("no");
	                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
					break;
				case "check_linea":
	            	$FunzioneId=4;
	                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
	                if (sizeof($permesso))
	                	check_linea();    
	                else
	                	echo("no");
	                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
					break;
	            case "create_tariffe":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						create_tariffe();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;
				case "create_tariffe_comune":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						create_tariffe_comune();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;
					
	       }
		} elseif (!empty($_REQUEST)) {
			switch($_REQUEST['do']) {
		        case "resetPercorso":
	            	$FunzioneId=2;
	                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
	                if (sizeof($permesso))
	                	resetPercorsoBreve();   
	                else
	                Errors::$ErrorePermessiModuloFunzione;
	                break;
				case "DuplicaCorsa":
		            $FunzioneId=2;
	                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
	                if (sizeof($permesso))
	                	DuplicaCorsa($_REQUEST['CorsaId']);   
	                else
	                	Errors::$ErrorePermessiModuloFunzione;
	                break;
                case "getComuneTariffa":
                	$FunzioneId=2;
                	$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                	if (sizeof($permesso))
                		getComuneTariffa();
                	else
                		Errors::$ErrorePermessiModuloFunzione;
                	break;
	        }
	    }
	} else {
		echo("no");
	}

} else {
	header("Location: /logout.php");
}
?>