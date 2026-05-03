<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Odc.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");

$ModuloId=3;



global $organismo_wizard;
$organismo_wizard=null;

if(isset($_SESSION['ORGANISMO_WIZARD'])) {
$organismo_wizard=unserialize($_SESSION['ORGANISMO_WIZARD']);
}







function update()
{
global $user,$organismo_wizard,$db;
$MediazioneId=$organismo_wizard->OdcId;

$storico=new StoricoOperazioni();
$storico->conn=$db;



$OdcId=$organismo_wizard->OdcId;

$step_corrente=$_POST['step_corrente'];
$data=$_POST['Organismo']; 
   
   $data=$storico->operazioni_update($data,$user);
$result=$db->update("Odc", $data, "OdcId=".$user->OdcId);


if ($result)
echo("ok");
else
echo("no");
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
                            
                            
                            

				
                                  
                                case "update":
                                 
                                 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   update();   
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

// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>