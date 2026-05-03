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
include_once($classespath_."class.Scontistica.php");
$ModuloId=35;



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
 $lastidA=$db->delete("RT_ScontisticaBiglietto","OdcIdRef=$user->OdcId");
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
         $lastidA=$db->insert("RT_ScontisticaBiglietto", $d1);
      }
 }

    echo("ok".",".$lastidA);    

$db->close();
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
                                 
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   create();   
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