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
include_once($classespath_."class.ListinoFasceKm.php");
include_once($classespath_."class.Comune.php");


$ModuloId=3;


function controllaFasciaKilometrica($valoreKm,$db,$idEdit=null) {
    $res = '';
    $sql = "SELECT * From RT_ListinoMoltiplicatore";
    
    $rows = $db->fetch_array($sql);
    
    foreach ($rows as $row){   
        if($idEdit==null){
            if($row['KmDa'] <= $valoreKm && $valoreKm <= $row['KmA']){
                $res = 'Il valore kilometrico '.$valoreKm.' già esiste in un range. ';
                break;
            }
        }
        else {
            if($row['KmDa'] <= $valoreKm && $valoreKm <= $row['KmA'] && $idEdit != $row['ListinoMoltiplicatore']){
                $res = 'Il valore kilometrico '.$valoreKm.' già esiste in un range. ';
                break;
            }
        }
    }
    
    return $res;
}

function controlloDeiKm($KmDa,$KmA,$db,$idEdit){
    if(is_int($KmDa) == false){
        //$controllo = 'I campi KmDa e KmA non devono avere decimali';
        //$controllo = 'valore '.is_int($KmDa);
    }
    else if($KmDa>$KmA){
        $controllo = 'I KmDa devono essere minori dei KmA';
    }
    else if($KmDa==$KmA){
        $controllo = 'I KmDa e i KmA non possono essere uguali';
    }
    else {
        $controllo = controllaFasciaKilometrica($KmDa,$db,$idEdit);
        $controllo .= controllaFasciaKilometrica($KmA,$db,$idEdit);
    }
    
    return $controllo;
}

function create()
{
global $user;
$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;

$controllo = '';

// prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
$data=$_POST['ListinoMoltiplicatore'];
$data=$storico->operazioni_insert($data,$user);

//$data['KmDa'] = floor($data['KmDa']);
//$data['KmA'] = floor($data['KmA']);

$controllo = controlloDeiKm($data['KmDa'],$data['KmA'],$db);

if($controllo == ''){
    $lastidA=$db->insert("RT_ListinoMoltiplicatore", $data);
    if ($lastidA!=false)
    {
         echo("ok".",".$lastidA);    
    }
    else
        echo("no");
}
else {
    echo ("esistente".",".$controllo);
}
exit();   
  
}


function update($id)
{
global $user;

$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;
  
// prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
$data=$_POST['ListinoMoltiplicatore'];
$data=$storico->operazioni_update($data,$user);

//$data['KmDa'] = floor($data['KmDa']);
//$data['KmA'] = floor($data['KmA']);

$controllo = controlloDeiKm($data['KmDa'],$data['KmA'],$db,$id);

if($controllo == ''){
    $result=$db->update("RT_ListinoMoltiplicatore", $data, "ListinoMoltiplicatore=".$id." and OdcIdRef=$user->OdcId");
    if ($result)
        echo("ok");
    else
        echo("no");
}
else {
    echo ("esistente".",".$controllo);
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