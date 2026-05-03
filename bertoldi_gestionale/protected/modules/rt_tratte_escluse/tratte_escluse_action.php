<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 


$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

include_once($classespath_."/class.Sede.php");
include_once($classespath_."/class.TratteNonVendibili.php");




$ModuloId=46;




function update($id)
{
global $user;

$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;

$db->delete('RT_TratteNonVendibili');
$arrPickup=$_POST['pickup'];
$arrDropOff=$_POST['dropoff'];

foreach($arrPickup as $key=>$value)
{
    $data=array();
    $data['ComunePickUpId']=$value;
    $data['ComuneDropOffId']=$arrDropOff[$key];
    
    $data=$storico->operazioni_insert($data,$user);
    $db->insert('RT_TratteNonVendibili', $data);
    
}
   /*$tnv=new TratteNonVendibili();
   $tnv->conn=$db;
   $r=$tnv->eliminaFermateNonVendibili();
   */

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
	
	
		if (!empty($_POST))
		{
			switch($_POST['action']) {

				
				
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