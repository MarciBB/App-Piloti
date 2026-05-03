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

$data['RegioneId']=$dataGeo['RegioneId'];
$data['Provincia']=$_POST['Provincia'];
$data['sigla']=$_POST['Sigla'];
$ProvinciaId=$_POST['ProvinciaId'];

$sql="SELECT Provincia,sigla FROM Provincia WHERE Provincia=".$data['Provincia'];
$row = $db->query_first($sql);
if ((($row['Provincia']==$data['Provincia'])and ($row['sigla']!=$data['sigla']))or($row['Provincia']!=$data['Provincia'])){
    
    $result=$db->update("Provincia", $data, "ProvinciaId=".$ProvinciaId);

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

$data['RegioneId']=$dataGeo['CampeggioRegione'];
$data['Provincia']=$_POST['Provincia'];
$data['sigla']=$_POST['Sigla'];

$sql="SELECT Provincia FROM Provincia WHERE Provincia='".$data['Provincia']."'";
echo($sql);
$row = $db->query_first($sql);
if ($row['Provincia']!=$data['Provincia']){
  
    $lastidA=$db->insert("Provincia", $data);
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