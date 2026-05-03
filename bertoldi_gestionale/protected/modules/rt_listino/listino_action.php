<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Mediatore.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");


include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Listino.php");
$ModuloId=3;



global $tratta_wizard;
$tratta_wizard=null;

if(isset($_SESSION['TRATTA_WIZARD'])) {
$tratta_wizard=unserialize($_SESSION['TRATTA_WIZARD']);
}





function create()
{
global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$data=$_POST['ListinoBiglietto'];
 $lastidA=$db->delete("RT_ListinoBiglietto","OdcIdRef=$user->OdcId");
 foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     
     
     $arr_chiave=explode('_', $chiave);
     $ListinoId=$arr_chiave[0];
     $BigliettoId=$arr_chiave[1];
     $Prezzo=$valore;
     
    
     $d1['ListinoId']=$ListinoId;
     $d1['BigliettoId']=$BigliettoId;
     $d1['Prezzo']=str_replace(",",".",$Prezzo);
     
    
     print_r($d1);
     
    
     
     if (!empty($Prezzo) and ($Prezzo>=0) and ($Prezzo!=""))
         { 
        $d1=$storico->operazioni_insert($d1,$user);
         $lastidA=$db->insert("RT_ListinoBiglietto", $d1);
      }
 }

    echo("ok".",".$lastidA);    

$db->close();
exit(); 

  
}

/**
 * Funzione per creare un servizio e salvare i dati nel database.
 */
function createServizio()
{
    // Accesso alle variabili globali $user e $db per gestire le operazioni legate al database e all'utente.
    global $user, $db;

    // Istanzia la classe StoricoOperazioni e assegna la connessione al database.
    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // Istanzia la classe DT (Data Transfer).
    $dt = new DT();

    // Recupera i dati inviati tramite POST.
    $dataLimite = $_POST['ListinoBigliettoLimite'];
    $dataLimiteMin = $_POST['ListinoBigliettoLimiteMin'];
    $dataLimiteOre = $_POST['ListinoBigliettoLimiteOre'];
    $dataLimitePassegeri = $_POST['ListinoBigliettoLimitePassegeri'];
    $dataPrezzi = $_POST['ListinoBigliettoPrezzo'];

    // Cancella i record esistenti per l'utente corrente dal database.
    $lastidA = $db->delete("RT_ListinoServizi", "OdcIdRef=$user->OdcId");

    // Cicla sui dati dei prezzi inviati via POST.
    foreach ($dataPrezzi as $chiave => $valore) {
        $label = $chiave;

        // Rimuove eventuali caratteri speciali dalla chiave.
        $chiave = str_replace("'", "", $chiave);
        $chiave = str_replace("\\", "", $chiave);

        // Divide la chiave in base al separatore "_" per ottenere i dettagli.
        $arr_chiave = explode('_', $chiave);
        $CorsaId = isset($arr_chiave[0]) && $arr_chiave[0] != '' ? $arr_chiave[0] : 0;
        $BigliettoId = $arr_chiave[1];
        $Prezzo = $valore;

        // Recupera i limiti associati alla chiave.
        $Limite = $dataLimite[$label];
        $LimiteMin = $dataLimiteMin[$label];
        $LimiteOre = $dataLimiteOre[$label];
        $LimitePassegeri = $dataLimitePassegeri[$label];

        // Prepara i dati per l'inserimento.
        $d1['CorsaId'] = $CorsaId;
        $d1['BigliettoId'] = $BigliettoId;
        $d1['Prezzo'] = str_replace(",", ".", $Prezzo);
        $d1['Limite'] = $Limite;
        $d1['LimiteMin'] = $LimiteMin;
        $d1['LimiteOre'] = $LimiteOre;
        $d1['LimitePerNumPassegeri'] = $LimitePassegeri;

        // Verifica che il prezzo sia valido prima di procedere con l'inserimento.
        if (!empty($Prezzo) && ($Prezzo >= 0) && ($Prezzo != "")) {
            // Inserisce l'operazione nel registro storico e ottiene i dati aggiornati.
            $d1 = $storico->operazioni_insert($d1, $user);

            // Inserisce i dati nella tabella del listino servizi.
            $lastidA = $db->insert("RT_ListinoServizi", $d1);
        }
    }

    // Restituisce l'esito dell'operazione.
    echo("ok" . "," . $lastidA);

    // Chiude la connessione al database.
    $db->close();
    exit();
}

function createListinoTipo() {
    // Accesso alla variabile globale $user e $db per gestire le operazioni legate al database e all'utente.
    global $user, $db;

    // Istanzia la classe StoricoOperazioni e assegna la connessione al database.
    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // Istanzia la classe DT (Data Transfer).
    $dt = new DT();

    // Recupera la variazione del listino biglietto dai dati POST.
    $dataVariazione = $_POST['ListinoBigliettoVariazione'];

    // Cancella i record esistenti per l'utente corrente dal database.
    $lastidA = $db->delete("RT_ListinoTipo", "OdcIdRef=$user->OdcId");

    // Cicla sui dati di variazione inviati via POST.
    foreach ($dataVariazione as $chiave => $valore) {
        // Converti l'ID del biglietto in un numero intero.
        $BigliettoId = intval($chiave);

        // Recupera e formatta la variazione come numero con 2 decimali.
        $Variazione = number_format((float)str_replace(',', '.', trim($valore)), 2, '.', '');

        // Prepara i dati per l'inserimento.
        $d1['BigliettoId'] = $BigliettoId;
        $d1['Variazione'] = $Variazione;

        // Inserisce l'operazione nel registro storico e ottiene i dati aggiornati.
        $d1 = $storico->operazioni_insert($d1, $user);

        // Inserisce i dati nella tabella del listino tipo.
        $lastidA = $db->insert("RT_ListinoTipo", $d1);
    }

    // Recupera la data corrente.
    $oggi = date('Y-m-d');

    // Query per selezionare le corse attive dal database.
    $sql = "SELECT * FROM RT_Corsa WHERE AttivaAl >= '$oggi' AND Stato = 1 AND Cancella = 0 ORDER BY CorsaPeso ASC";
    $ArrObjectCorse = $db->fetch_array($sql);

    // Cancella le tariffe delle corse associate.
    foreach ($ArrObjectCorse as $corsa) {
        $db->delete("RT_CorsaTariffa", "CorsaId = " . $corsa['CorsaId']);
    }

    // Query per selezionare le tipologie di biglietto attive.
    $sql = "SELECT * FROM RT_TipologiaBiglietto WHERE Stato = 1 AND Cancella = 0 AND OccupaPosto = 1 ORDER BY TipologiaBigliettoPeso ASC";
    $ArrObjectTipoBiglietto = $db->fetch_array($sql);

    // Query per ottenere le variazioni del listino tipo.
    $sql = "SELECT * FROM RT_ListinoTipo";
    $ArrVariazioni = $db->fetch_array($sql);

    // Crea un array per le variazioni del biglietto.
    $variazioni = array();
    foreach ($ArrVariazioni as $var) {
        $variazioni[$var['BigliettoId']] = $var['Variazione'];
    }
    $variazioni[17] = 0; // Imposta la variazione per l'ID 17 a 0.

    $count = 0; // Contatore per il numero di operazioni.

    // Query per selezionare le tariffe di linea.
    $sql = "SELECT * FROM RT_LineaTariffa";
    $dataT = $db->fetch_array($sql);

    // Cicla su ogni tariffa di linea recuperata.
    foreach ($dataT as $t) {
        $LineaId = $t['LineaId'];
        $PkId = $t['FermataPickup'];
        $DoffId = $t['FermataDropOff'];
        $tariffa = $t['Tariffa'];

        // Query per selezionare le corse attive per la linea specifica.
        $oggi = date('Y-m-d');
        $sql = "SELECT * FROM RT_Corsa WHERE AttivaAl >= '$oggi' AND Stato = 1 AND Cancella = 0 AND LineaId = $LineaId ORDER BY CorsaPeso ASC";
        $ArrObjectCorse = $db->fetch_array($sql);

        // Inizio della query di inserimento delle tariffe delle corse.
        $qCorsa = "INSERT INTO RT_CorsaTariffa
            (`TipologiaBigliettoId`, `TrattaId`, `CorsaId`, `FermataPickup`, `FermataDropOff`, `ListinoId`, `Tariffa`, `OpeIns`, `SedeIns`, `DataIns`, `IpIns`, `OdcIdRef`, `GestoreIdRef`, `Cancella`, `Stato`)
            VALUES ";

        $countCorsa = 0; // Contatore per le corse.
        $limite = (count($ArrObjectCorse) * count($ArrObjectTipoBiglietto)) - 1; // Limite per il ciclo.

        // Cicla su ogni corsa.
        foreach ($ArrObjectCorse as $corsa) {
            // Cicla su ogni tipologia di biglietto.
            foreach ($ArrObjectTipoBiglietto as $tipoBiglietto) {
                // Prepara i dati per l'inserimento nella tabella delle tariffe delle corse.
                $dc['TipologiaBigliettoId'] = $tipoBiglietto['TipologiaBigliettoId'];
                $dc['TrattaId'] = 0;
                $dc['CorsaId'] = $corsa['CorsaId'];
                $dc['FermataPickup'] = $PkId;
                $dc['FermataDropOff'] = $DoffId;
                $dc['ListinoId'] = 1;
                $dc['Tariffa'] = floatval($tariffa + ($variazioni[$tipoBiglietto['TipologiaBigliettoId']] * $tariffa) / 100);

                // Inserisce l'operazione nel registro storico e ottiene i dati aggiornati.
                $dc = $storico->operazioni_insert($dc, $user);

                $vc = ''; // Variabile per costruire i valori della query.
                foreach ($dc as $key => $val) {
                    if (strtolower($val) == 'null') $vc .= "NULL, ";
                    elseif (strtolower($val) == 'now()') $vc .= "NOW(), ";
                    else $vc .= "'" . $db->escape($val) . "', ";
                }

                // Aggiunge i valori alla query.
                $qCorsa .= " (" . rtrim($vc, ', ') . ")";
                if ($countCorsa != $limite) {
                    $qCorsa .= ",";
                }
                $countCorsa++;
            }
        }

        // Esegue la query di inserimento delle tariffe delle corse.
        $db->query($qCorsa);

        $count++; // Incrementa il contatore.
    }

    echo("ok" . "," . $lastidA); // Restituisce l'esito dell'operazione.

    $db->close(); // Chiude la connessione al database.
    exit(); // Termina lo script.
}





  

if(is_object($user)) {
    
$db= new Database();
$db->connect();
$user->conn=$db;
$permessi=$user->get_permessi_modulo($ModuloId);
if (sizeof($permessi)>0)
{   
	
	
		if (!empty($_POST))
		{
			switch($_POST['action']) {
                            
                            
                            

				case "create":
                	$FunzioneId=1;
                    $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                    if (sizeof($permesso))
                    	create();   
                    else
                    	Errors::$ErrorePermessiModuloFunzione;
                    break;
                case "createServizi":
                	$FunzioneId=1;
                    $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                    if (sizeof($permesso))
                    	createServizio();
                    else
                    	Errors::$ErrorePermessiModuloFunzione;
                    break;
                 case "createListinoTipo":
                    	$FunzioneId=1;
                    	$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                    	if (sizeof($permesso))
                    		createListinoTipo();
                    	else
                    		Errors::$ErrorePermessiModuloFunzione;
                    	break;
                
                                  
                                 
                                  
                                 
                                
                                
                                
                                
                                
                               
                                
                                
                                		
				
                                
                }
              }
	} // end verifica permessi
	else {
           echo("no");
            
        }

}

// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}
?>