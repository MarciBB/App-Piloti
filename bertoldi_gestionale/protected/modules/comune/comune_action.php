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

$data['provincia']=$dataGeo['CampeggioProvincia'];
$data['Comune']=$_POST['Comune'];
$data['cap']=$_POST['Cap'];
$ComuneId=$_POST['ComuneId'];

$sql="SELECT Comune,cap FROM Comune WHERE Comune=".$data['Comune'];
$row = $db->query_first($sql);
if ((($row['Comune']==$data['Comune'])and ($row['cap']!=$data['cap']))or($row['Comune']!=$data['Comune'])){
    
    $result=$db->update("Comune", $data, "ComuneId=".$ComuneId);
    
    
        $dataConfine=null;
        $dataConfine=$_POST['ComuneConfine'];
        $db->delete("RT_ComuneConfine","ComuneId=".$ComuneId);
         foreach ($dataConfine as $chiave => $valore)
         { 
             $chiave=str_replace("'","",$chiave);
             $chiave=str_replace("\\","",$chiave);
             $ConfineId=$chiave;
             
             $d1=null;
             $d1['ComuneId']=$ComuneId;
             $d1['ConfineId']=$ConfineId;
             $d1['Km']=$valore;
             if (!empty($valore) and ($valore>=0) and ($valore!=""))
                 { 
                 $d1=$storico->operazioni_insert($d1,$user);
                 $lastid1=$db->insert("RT_ComuneConfine", $d1);
              }
         }
    

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


$dataGeo=$_POST['Campeggio'];

$data['provincia']=$dataGeo['CampeggioProvincia'];
$data['Comune']=$_POST['Comune'];
$data['cap']=$_POST['Cap'];

$sql="SELECT Comune FROM Comune WHERE Comune='".$data['Comune']."'";
echo($sql);
$row = $db->query_first($sql);
if ($row['Comune']!=$data['Comune']){
  
    $lastidA=$db->insert("Comune", $data);
    if ($lastidA!=false)
    {
        $dataConfine=null;
        $dataConfine=$_POST['ComuneConfine'];
        
         foreach ($dataConfine as $chiave => $valore)
         { 
             $chiave=str_replace("'","",$chiave);
             $chiave=str_replace("\\","",$chiave);
             $ConfineId=$chiave;
             $ComuneId=$lastidA;
             $d1=null;
             $d1['ComuneId']=$ComuneId;
             $d1['ConfineId']=$ConfineId;
             $d1['Km']=$valore;
             if (!empty($valore) and ($valore>=0) and ($valore!=""))
                 { 
                 $d1=$storico->operazioni_insert($d1,$user);
                 $lastid1=$db->insert("RT_ComuneConfine", $d1);
              }
         }
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
                                           update($_POST['ComuneId']);    
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