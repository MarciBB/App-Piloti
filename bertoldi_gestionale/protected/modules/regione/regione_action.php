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


$dataGeo=$_POST['Campeggio'];
$data['idnazione']=$dataGeo['CampeggioNazione'];
$data['Regione']=$_POST['Regione'];
$RegioneId=$_POST['RegioneId'];

$sql="SELECT Regione FROM Regione WHERE Regione='".$data['Regione']."'";
echo($sql);
$row = $db->query_first($sql);
if (true){
    $result=$db->update("Regione", $data, "RegioneId=".$RegioneId);

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

    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
$dataGeo=$_POST['Campeggio'];
$data['idnazione']=$dataGeo['CampeggioNazione'];
$data['Regione']=$_POST['Regione'];


$sql="SELECT Regione FROM Regione WHERE Regione='".$data['Regione']."'";
echo($sql);
$row = $db->query_first($sql);
if ($row['Regione']!=$data['Regione']){
    $lastidA=$db->insert("Regione", $data);
    if ($lastidA!=false)
    {
    echo("ok".",".$lastidA);    
    }
    else
    echo("no");   
}else{
  echo("duplicato");  
}

exit();   
  
}


function updateTraduzione($id)
{
global $user;

$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;
$data=$_POST['Regione'];
ksort($data);
print_r($data);
$lingua="0";
foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
          
     $arr_chiave=explode('_', $chiave);
     $RegioneTradLinguaId=$arr_chiave[0];
     $RegioneTradValore=$arr_chiave[1];
     if($lingua==0) $lingua= $RegioneTradLinguaId;
     
     if($lingua!=$RegioneTradLinguaId){
       $lastidA=$db->delete("RegioneTrad","RegioneTradLinguaId=$lingua and RegioneId=$id and OdcIdRef=$user->OdcId");
        if($d1['RegioneTradNome']!=""){         
            $d1=$storico->operazioni_insert($d1,$user);
            print_r($d1);
            $lastidA=$db->insert("RegioneTrad", $d1);
            $lingua=$RegioneTradLinguaId;
        }  
     }
     
     $d1['RegioneTradLinguaId']=$RegioneTradLinguaId;    
     if($RegioneTradValore=='RegioneTradNome') $d1['RegioneTradNome']=$data[$RegioneTradLinguaId."_".$RegioneTradValore];
     $d1['RegioneId']=$id;     
     
 }
 $lastidA=$db->delete("RegioneTrad","RegioneTradLinguaId=$RegioneTradLinguaId and RegioneId=$id and OdcIdRef=$user->OdcId");
 if($d1['RegioneTradNome']!=""){         
        $d1=$storico->operazioni_insert($d1,$user);
        print_r($d1);
        $lastidA=$db->insert("RegioneTrad", $d1);
        $lingua=$RegioneTradLinguaId;
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
				case "updateTraduzione":
                               
				 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           updateTraduzione($_POST['idpost']);    
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;
				break;
				case "del":
				$FunzioneId=3;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
				
				case "update":
                               
				 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           update($_POST['id']);    
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