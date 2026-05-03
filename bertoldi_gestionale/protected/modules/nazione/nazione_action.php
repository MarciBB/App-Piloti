<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 


$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

include_once($classespath_."/class.AnagraficaParte.php");


$ModuloId=1;


function update($id)
{
global $user;

$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;

$data['Nazione']=$_POST['Nazione'];
$NazioneId=$_POST['NazioneId'];

$sql="SELECT Nazione FROM Nazione WHERE Nazione='".$data['Nazione']."'";
$row = $db->query_first($sql);
if ($row['Nazione']!=$data['Nazione']){
    
    $result=$db->update("Nazione", $data, "NazioneId=".$NazioneId);

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
}else{
    echo("duplicato");
        $db->close();
        exit();
}   
  
}



function create()
{
global $user;
$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;

$data['Nazione']=$_POST['Nazione'];

$sql="SELECT Nazione FROM Nazione WHERE Nazione='".$data['Nazione']."'";
echo($sql);
$row = $db->query_first($sql);
if ($row['Nazione']!=$data['Nazione']){
  
    $lastidA=$db->insert("Nazione", $data);
    if ($lastidA!=false)
    {
        echo("ok".",".$lastidA);    
    }
    else{
        echo("no");
        exit();     
    }    
 
}else{
    echo("duplicato");
    exit();
}
}

function updateTraduzione($id)
{
global $user;

$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;
$data=$_POST['Nazione'];
ksort($data);
print_r($data);
$lingua="0";
foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
          
     $arr_chiave=explode('_', $chiave);
     $NazioneTradLinguaId=$arr_chiave[0];
     $NazioneTradValore=$arr_chiave[1];
     if($lingua==0) $lingua= $NazioneTradLinguaId;
     
     if($lingua!=$NazioneTradLinguaId){
       $lastidA=$db->delete("NazioneTrad","NazioneTradLinguaId=$lingua and NazioneId=$id and OdcIdRef=$user->OdcId");
        if($d1['NazioneTradNazione']!=""){         
            $d1=$storico->operazioni_insert($d1,$user);
            print_r($d1);
            $lastidA=$db->insert("NazioneTrad", $d1);
            $lingua=$NazioneTradLinguaId;
        }  
     }
     
     $d1['NazioneTradLinguaId']=$NazioneTradLinguaId;
     if($NazioneTradValore=='NazioneTradNazione') $d1['NazioneTradNazione']=$data[$NazioneTradLinguaId."_".$NazioneTradValore];
     $d1['NazioneId']=$id;     
     
 }
 $lastidA=$db->delete("NazioneTrad","NazioneTradLinguaId=$NazioneTradLinguaId and NazioneId=$id and OdcIdRef=$user->OdcId");
 if($d1['NazioneTradNazione']!=""){         
        $d1=$storico->operazioni_insert($d1,$user);
        print_r($d1);
        $lastidA=$db->insert("NazioneTrad", $d1);
        $lingua=$NazioneTradLinguaId;
    }
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
                                           update($_POST['NazioneId']);    
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
				break;
                                case "updateTraduzione":
                               
				 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           updateTraduzione($_POST['idpost']);    
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;
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
header("Nazione: /logout.php");
}
?>