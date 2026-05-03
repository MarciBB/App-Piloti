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
include_once($classespath_."class.Scontistica.php");
include_once($classespath_."class.Comune.php");


$ModuloId=35;

function aggiungiScontisticaCorsa()
{
	global $user;
	$dt=new DT();
	$db= new Database();
	$db->connect();
	$storico=new StoricoOperazioni();
	$storico->conn=$db;
$ListinoId=$_POST['ListinoId'];
$ScontisticaCorsaId=null;
if (isset($_POST['ScontisticaCorsaId']))
  $ScontisticaCorsaId=$_POST['ScontisticaCorsaId'];  
	// prelevo i dati del form ed aggiorno tutte le proprieta'� dell'oggetto

	$data=$_POST['RT_ScontisticaCorsa'];
	$data['ListinoId']=$ListinoId;
	$data['Dal']=$dt->format($data['Dal'], "d/m/Y", "Y-m-d");
	$data['Al']=$dt->format($data['Al'], "d/m/Y", "Y-m-d");
	
	
	$lastid = 0;
	if($ScontisticaCorsaId == null){
		$data=$storico->operazioni_insert($data,$user);
		$lastid = $db->insert("RT_ScontisticaCorsa", $data);
	
	}else{
		$data['ScontisticaCorsaId']=$ScontisticaCorsaId;
		$data['Stato']=(isset($data['Stato']))? $data['Stato'] : 0;
		$data=$storico->operazioni_update($data,$user);
		$result=$db->update("RT_ScontisticaCorsa", $data, "ScontisticaCorsaId=".$ScontisticaCorsaId." and OdcIdRef=$user->OdcId");
		if($result){
			$result=$db->delete("RT_ScontisticaCorsaDettaglio","ScontisticaCorsaId=$ScontisticaCorsaId and OdcIdRef=$user->OdcId");
			$lastid = $ScontisticaCorsaId;
		}
	}
	
	if ($lastid > 0)
	{
		$datas1=$_POST['ValiditaPromozioneCorsa'];
		print_r($datas1);
		$datas['ScontisticaCorsaId']=$lastid;
		$datas=$storico->operazioni_insert($datas,$user);
	
		foreach ($datas1 as $tipo) {
			$datas['CorsaId'] = $tipo;
			$db->insert("RT_ScontisticaCorsaDettaglio", $datas);
		}
	
		echo("ok".",".$lastidA);
	}
	else
		echo("no");

	exit();

}

function create()
{
global $user;
$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;
$dt=new DT();


    
    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto

$data=$_POST['Listino'];
$data['AttivaDal']=$dt->format($data['AttivaDal'], "d/m/Y", "Y-m-d");
$data['AttivaAl']=$dt->format($data['AttivaAl'], "d/m/Y", "Y-m-d");
$data=$storico->operazioni_insert($data,$user);


$lastidA=$db->insert("RT_Scontistica", $data);
if ($lastidA!=false)
{
 
echo("ok".",".$lastidA);    
}
else
echo("no");
exit();   
  
}


function update($id)
{
global $user;

$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;
$dt=new DT();

    
    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto

$data=$_POST['Listino'];
$data['AttivaDal']=$dt->format($data['AttivaDal'], "d/m/Y", "Y-m-d");
$data['AttivaAl']=$dt->format($data['AttivaAl'], "d/m/Y", "Y-m-d");
$data=$storico->operazioni_update($data,$user);


$result=$db->update("RT_Scontistica", $data, "ListinoId=".$id." and OdcIdRef=$user->OdcId");


if ($result)
echo("ok");
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
                                
                                
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					
					
				break;
                                
                                case "AggiungiPromozioneCorsa":
                                 
                                 $FunzioneId=2;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   aggiungiScontisticaCorsa();   
                                  else
                                    Errors::$ErrorePermessiModuloFunzione;
                                
                                
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					
					
				break;
				
                                
                                case "del":
				$FunzioneId=3;
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