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

$ModuloId=29;



global $tratta_wizard;
$tratta_wizard=null;

if(isset($_SESSION['TRATTA_WIZARD'])) {
$tratta_wizard=unserialize($_SESSION['TRATTA_WIZARD']);
}


function cambia_stato()
{
    global $user,$tratta_wizard,$db;
    $storico=new StoricoOperazioni();
	$storico->conn=$db;
    
    $GestoreId=$tratta_wizard->GestoreId;
    
    $data['Stato']=$_POST['Stato'];
    $data=$storico->operazioni_update($data,$user);
    
    $res=$db->update('Gestore', $data, "GestoreId=".$GestoreId);
 echo("Lo stato del gestore e' stato cambiato");
 exit();
}

function elimina_fermata()
{
	global $user,$tratta_wizard,$db;
	$storico=new StoricoOperazioni();
	$storico->conn=$db;

	$fermataId = $_GET['FermataId'];
	
	$data['Stato'] = 0;
	$data['Cancella'] = 1;
	$data=$storico->operazioni_update($data,$user);

	$res=$db->update('RT_Fermata', $data, "FermataId=".$fermataId);
	echo json_encode(array('result'=>true));
}

function create()
{
	global $user, $db;

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$dt = new DT();
	$data = $_POST['Percorso'];

	$data = $storico->operazioni_insert($data, $user);
	$lastidA = $db->insert("RT_Percorso", $data);

	if ($lastidA != false) {
		echo("ok" . "," . $lastidA);
	} else {
		echo("no");
	}

	$db->close();
	exit();
}

function update()
{
	global $user, $tratta_wizard, $db;
	$Id = $tratta_wizard->Id;

	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	$dt = new DT();

	$step_corrente=$_POST['step_corrente'];
	$data = $_POST['Tratta'];
	if(!isset($data['TipologiaBusDefaultId']) || $data['TipologiaBusDefaultId'] == '') {
		$data['TipologiaBusDefaultId'] = 'null';
	}
	$data=$storico->operazioni_update($data,$user);
	$result=$db->update("RT_Tratta", $data, "TrattaId=".$Id);

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

function massUpdate() {
	global $user, $db;
	$massTrattaId = $_POST['massTrattaId'];
	$massIsPickup = $_POST['massIsPickup'];
	$massIsDropOff = $_POST['massIsDropOff'];
	$massIsInterscambio = $_POST['massIsInterscambio'];
	$massIsBlackList = $_POST['massIsBlackList'];
	$massIsDaConfermare = $_POST['massIsDaConfermare'];
	$massWebSelling = $_POST['massWebSelling'];
	$massStato = $_POST['massStato'];

	//recupero i dati della tratta
	$massTrattaIdString = implode(',', $massTrattaId);
	
	$data = array();
	if($massIsPickup != '') {
		$data['IsPickup'] = $massIsPickup;
	}
	if($massIsDropOff != '') {
		$data['IsDropOff'] = $massIsDropOff;
	}
	if($massIsInterscambio != '') {
		$data['IsInterscambio'] = $massIsInterscambio;
	}
	if($massIsBlackList != '') {
		$data['IsBlackList'] = $massIsBlackList;
	}
	if($massIsDaConfermare != '') {
		$data['IsDaConfermare'] = $massIsDaConfermare;
	}
	if($massWebSelling != '') {
		$data['WebSelling'] = $massWebSelling;
	}
	if($massStato != '') {
		$data['Stato'] = $massStato;
	}

	$db->update("RT_Fermata", $data, "TrattaId IN (".$massTrattaIdString.")");
	echo("ok");
	exit();

}

  

if(is_object($user)) {
    
$db= new Database();
$db->connect();
$user->conn=$db;
$permessi=$user->get_permessi_modulo($ModuloId);
if (sizeof($permessi)>0)
{   
	
		if (!empty($_GET)) {
			switch($_GET['action']) {
				case "CancellaFermata":
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						elimina_fermata();
					else
						echo("no");
					break;
			}
		}
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
                    break;
				case "massUpdate":
					$FunzioneId=4;
						$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						massUpdate();    
					else
						echo("no");
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