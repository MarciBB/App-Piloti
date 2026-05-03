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
include_once($classespath_."class.TipologiaBus.php");
$ModuloId=26;



global $tipologiabus_wizard;
$tipologiabus_wizard=null;

if(isset($_SESSION['TIPOLOGIABUS_WIZARD'])) {
$tipologiabus_wizard=unserialize($_SESSION['TIPOLOGIABUS_WIZARD']);
}





function create()
{
global $user,$tipologiabus_wizard;
$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;



    
    // prelevo i dati del form ed aggiorno tutte le proprietÃ  dell'oggetto

$data=$_POST['TipologiaBus'];
$data['TotalePosti']=$data['PostiPrimoPiano']+$data['PostiSecondoPiano'];

$numeropiani=0;
if ($data['PostiPrimoPiano']>0)
    $numeropiani++;

if ($data['PostiSecondoPiano']>0)
    $numeropiani++;


$data['NumeroPiani']=$numeropiani;
/*
$dataup['IsDefault']=0;
if ($data['IsDefault']==1)
$result=$db->update("RT_TipologiaBus", $dataup, "OdcIdRef=$user->OdcId");

*/

$data=$storico->operazioni_insert($data,$user);


$lastidA=$db->insert("RT_TipologiaBus", $data);
if ($lastidA!=false)
{
    $tipologiabus_wizard=new TipologiaBus($lastidA);
    $tipologiabus_wizard->conn=$db;
    $tipologiabus_wizard->inizializzaDatiGenerali();
    $_SESSION['TIPOLOGIABUS_WIZARD']=serialize($tipologiabus_wizard);
    
    
echo("ok".",".$lastidA);    
}
else
echo("no");
exit();   
  
}



function create_dettaglio()
{
global $user,$db,$tipologiabus_wizard;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;



$dt=new DT();
$data=$_POST['TipologiaBusDettaglioPosto'];
$data1=$_POST['TipologiaBusDescrizionePosto'];
$TipologiaBusId=$tipologiabus_wizard->Id;


 $lastidA=$db->delete("RT_TipologiaBusDettaglioPosto","OdcIdRef=$user->OdcId and TipologiaBusId=$TipologiaBusId");
 foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     
     
     $arr_chiave=explode('_', $chiave);
     
    
     
     $numeropiano=$arr_chiave[0];
     $riga=$arr_chiave[1];
     $colonna=$arr_chiave[2];
     
     $key=$numeropiano."_".$riga."_".$colonna;
     
     
  
     
     $d1['TipologiaBusId']=$TipologiaBusId;
     
     $d1['Riga']=$riga;
     $d1['Colonna']=$colonna;
     $d1['NumeroPosto']=$valore;
     $d1['NumeroPiano']=$numeropiano;
     $d1['DescrizionePosto']=$data1[$chiave];
     
     print_r($d1);
   
       if (($valore>0) or (!empty($data1[$chiave])))
         { 
        $d1=$storico->operazioni_insert($d1,$user);
         $lastidA=$db->insert("RT_TipologiaBusDettaglioPosto", $d1);
      }
 }

  echo("ok");

$db->close();
exit(); 

  
}

  
function update()
{
global $user,$tipologiabus_wizard;

$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;


    
    // prelevo i dati del form ed aggiorno tutte le proprietÃ  dell'oggetto

$data=$_POST['TipologiaBus'];
$data['TotalePosti']=$data['PostiPrimoPiano']+$data['PostiSecondoPiano'];

$numeropiani=0;
if ($data['PostiPrimoPiano']>0)
    $numeropiani++;

if ($data['PostiSecondoPiano']>0)
    $numeropiani++;


$data['NumeroPiani']=$numeropiani;
$data=$storico->operazioni_update($data,$user);


$id=$tipologiabus_wizard->Id;
/*
$dataup['IsDefault']=0;
if ($data['IsDefault']==1)
$result=$db->update("RT_TipologiaBus", $dataup, "OdcIdRef=$user->OdcId");
*/
$result=$db->update("RT_TipologiaBus", $data, "TipologiaBusId=".$id." and OdcIdRef=$user->OdcId");


if ($result)
{
    $tipologiabus_wizard->conn=$db;
    $tipologiabus_wizard->inizializzaDatiGenerali();
    $_SESSION['TIPOLOGIABUS_WIZARD']=serialize($tipologiabus_wizard);
    echo("ok");
}

else
echo("no");
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
                                  
                                  break;
                                  
                                 
                                  case "create_dettaglio":
                                 
                                 $FunzioneId=2;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   create_dettaglio();   
                                  else
                                    Errors::$ErrorePermessiModuloFunzione;
                                  
                                  break;
                                  
                                  case "update":
                                 
                                 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   update();   
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

// se l'utente non ÃƒÂ¨ loggato
else {
header("Location: /logout.php");
}
?>