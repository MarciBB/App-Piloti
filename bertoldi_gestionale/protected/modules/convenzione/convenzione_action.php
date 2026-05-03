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
include_once($classespath_."class.Convenzione.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");

$ModuloId=27;



global $convenzione_wizard;
$convenzione_wizard=null;

if(isset($_SESSION['CONVENZIONE_WIZARD'])) {
$convenzione_wizard=unserialize($_SESSION['CONVENZIONE_WIZARD']);
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


 // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto

$data=$_POST['Convenzione'];
$data=$storico->operazioni_insert($data,$user);
$lastidA=$db->insert("Convenzione", $data);
if ($lastidA!=false)
{
        
    
    $convenzione_wizard=new Convenzione($lastidA);
    $convenzione_wizard->ConvenzioneDatiGenerali=$data;
    
   
    
    $_SESSION['CONVENZIONE_WIZARD']=serialize($convenzione_wizard);
    
echo("ok");    
}
else
echo("no");
$db->close();
exit();   
  
}



function create_convenzione_dettaglio()
{
global $user,$db,$convenzione_wizard;
$storico=new StoricoOperazioni();
$storico->conn=$db;
$convenzione_wizard->conn=$db;
$cid=$convenzione_wizard->ConvenzioneId;
$db->query("delete from ConvenzioneDettaglio where ConvenzioneId=$cid");
                               
                               $numero_riferimenti=0;
                               $sql = "SELECT * from MediazionePagamentoRif where AbilitatoPerConvenzione=1 order by peso asc ";
                               $arr_riferimenti= $db->fetch_array($sql);                               

                                while ($numero_riferimenti<sizeof($arr_riferimenti))
                                {
                                    echo("qui");
                                    
                                  //  $materia=$arr_materia[$numero_riferimenti][''];
                                    $RifId=$arr_riferimenti[$numero_riferimenti]['MediazionePagamentoRifID'];
                                    $PercentualeSconto=$_POST[$RifId];
                                    $data=null;
                                    
                                    echo($RifId." ".$PercentualeSconto);
                                    
                                    $data['PagamentoRifId']=$RifId;
                                    $data['ConvenzioneId']=$convenzione_wizard->ConvenzioneId;
                                    $data['PercentualeSconto']=$PercentualeSconto;
                                    if($PercentualeSconto>0)
                                    {
                                    $data=$storico->operazioni_insert($data,$user);
                                    $last_id=$db->insert("ConvenzioneDettaglio", $data);
                                    }
                                    $numero_riferimenti++;
                                }
                               
                                 $mat=$convenzione_wizard->inizializzaConvenzioneDettagli();
                                 $_SESSION['CONVENZIONE_WIZARD']=serialize($convenzione_wizard);
                            

   
echo("ok");    
$db->close();
exit();   
  
}


function update()
{
global $user,$convenzione_wizard,$db;

$storico=new StoricoOperazioni();
$storico->conn=$db;



$ConvenzioneId=$convenzione_wizard->ConvenzioneId;

$data=$_POST['Convenzione']; 
   
   $data=$storico->operazioni_update($data,$user);
$result=$db->update("Convenzione", $data, "ConvenzioneId=$ConvenzioneId and OdcIdRef=$user->OdcId");


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
                                  
                                 case "create_convenzione_dettaglio":
                                 
                                 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   create_convenzione_dettaglio();   
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