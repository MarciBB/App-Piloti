<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 


$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.Comune.php");


$ModuloId=28;



function create() {
	global $user;
	$db= new Database();
	$db->connect();
	$storico=new StoricoOperazioni();
	$storico->conn = $db;
    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
	$data = $_POST['Fermata'];
	if($data['Latitudine'] == ''){
		$data['Latitudine'] = 'null';
	}
	if($data['Longitudine'] == ''){
		$data['Longitudine'] = 'null';
	}
	if($data['KmInizioTratta'] == ''){
		$data['KmInizioTratta'] = 'null';
	}
	$data = $storico->operazioni_insert($data,$user);
	
	$lastidA = $db->insert("RT_Fermata", $data);
	if ($lastidA!=false) {
		echo("ok".",".$lastidA);    
	} else {
		echo("no");
	}
	exit();
}


function update($id) {
	global $user;

	$db= new Database();
	$db->connect();
	$storico=new StoricoOperazioni();
	$storico->conn=$db;

    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
	$data = $_POST['Fermata'];

	if($data['Latitudine'] == ''){
		$data['Latitudine'] = 'null';
	}
	if($data['Longitudine'] == ''){
		$data['Longitudine'] = 'null';
	}
	if($data['KmInizioTratta'] == ''){
		$data['KmInizioTratta'] = 'null';
	}
	$data = $storico->operazioni_update($data,$user);

	$result = $db->update("RT_Fermata", $data, "FermataId=".$id." and OdcIdRef=$user->OdcId");

	if ($result) {
		echo("ok".",".$id);
	} else {
		echo("no");
	}
	exit(); 
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
                                 
                                 $FunzioneId=2;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   create();   
                                  else
                                    Errors::$ErrorePermessiModuloFunzione;
                                
                                
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					
					
				break;
				
				case "del":
				$FunzioneId=3;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
				
				case "update":
                               
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						update($_POST['idpost']);    
					else
						Errors::$ErrorePermessiModuloFunzione;    
                                
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
				break;
                }
              }
	} // end verifica permessi
	else {
            Errors::$ErrorePermessiModulo;
            
        }

}

// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>