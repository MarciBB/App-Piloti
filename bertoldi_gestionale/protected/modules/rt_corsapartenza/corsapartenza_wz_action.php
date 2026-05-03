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

$ModuloId=28;



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



function create()
{
global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$data=$_POST['Percorso'];

$data=$storico->operazioni_insert($data,$user);
$lastidA=$db->insert("RT_Percorso", $data);
if ($lastidA!=false)
{
    echo("ok".",".$lastidA);    
}
else
echo("no");

$db->close();
exit(); 

  
}




function update()
{
global $user,$tratta_wizard,$db;
$Id=$tratta_wizard->Id;

$storico=new StoricoOperazioni();
$storico->conn=$db;
$dt=new DT();

$step_corrente=$_POST['step_corrente'];
$data=$_POST['Tratta']; 
$data=$storico->operazioni_update($data,$user);
$result=$db->update("RT_Tratta", $data, "TrattaId=".$Id);

if ($result)
{
    echo("ok");
    $db->close();
    exit();
}
else
{
    echo("no");
    $db->close();
    exit();   
}    

    
  
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