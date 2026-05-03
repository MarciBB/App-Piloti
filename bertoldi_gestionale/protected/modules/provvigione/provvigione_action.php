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
include_once($classespath_."class.Provvigione.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");

$ModuloId=33;



global $provvigione_wizard;
$provvigione_wizard=null;

if(isset($_SESSION['PROVVIGIONE_WIZARD'])) {
$provvigione_wizard=unserialize($_SESSION['PROVVIGIONE_WIZARD']);
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

$data=$_POST['Provvigione'];
$data['FissoPerPratica']=str_replace(",",".",$data['FissoPerPratica']);
$data=$storico->operazioni_insert($data,$user);
$data1['ProvvigioneDefault']=0;
if ($data['ProvvigioneDefault']==1)
    $result=$db->update("Provvigione", $data1, "OdcIdRef=$user->OdcId");


$lastidA=$db->insert("Provvigione", $data);
if ($lastidA!=false)
{
    $provvigione_wizard=new Provvigione($lastidA);
    $provvigione_wizard->ProvvigioneDatiGenerali=$data;
    $_SESSION['PROVVIGIONE_WIZARD']=serialize($provvigione_wizard);
    echo("ok");    
}
else
echo("no");

$db->close();
exit();   
}



function create_provvigione_dettaglio()
{
global $user,$db,$provvigione_wizard;
$storico=new StoricoOperazioni();
$storico->conn=$db;
$provvigione_wizard->conn=$db;
$cid=$provvigione_wizard->ProvvigioneId;
$db->query("delete from ProvvigioneDettaglio where ProvvigioneId=$cid");
                               
                               $numero_riferimenti=0;
                               $sql = "SELECT * from MediazionePagamentoRif where AbilitatoPerProvvigione=1 order by peso asc ";
                               $arr_riferimenti= $db->fetch_array($sql);                               

                                while ($numero_riferimenti<sizeof($arr_riferimenti))
                                {
                                       
                                  //  $materia=$arr_materia[$numero_riferimenti][''];
                                    $RifId=$arr_riferimenti[$numero_riferimenti]['MediazionePagamentoRifID'];
                                    $PercentualeRiconosciuta=$_POST[$RifId];
                                    $data=null;
                                    
                                   
                                    
                                    $data['PagamentoRifId']=$RifId;
                                    $data['ProvvigioneId']=$provvigione_wizard->ProvvigioneId;
                                    $data['PercentualeRiconosciuta']=$PercentualeRiconosciuta;
                                    print_r($data);
                                    if($PercentualeRiconosciuta>0)
                                    {
                                    $data=$storico->operazioni_insert($data,$user);
                                    $last_id=$db->insert("ProvvigioneDettaglio", $data);
                                    }
                                    $numero_riferimenti++;
                                }
                               
                                 $mat=$provvigione_wizard->inizializzaProvvigioneDettagli();
                                 $_SESSION['PROVVIGIONE_WIZARD']=serialize($provvigione_wizard);
                            

   
echo("ok");    
$db->close();
exit();   
  
}


function update()
{
global $user,$provvigione_wizard,$db;

$storico=new StoricoOperazioni();
$storico->conn=$db;

$ProvvigioneId=$provvigione_wizard->ProvvigioneId;
$data=$_POST['Provvigione']; 
   $data1['ProvvigioneDefault']=0;
if ($data['ProvvigioneDefault']==1)
    $result=$db->update("Provvigione", $data1, "OdcIdRef=$user->OdcId");

$data=$storico->operazioni_update($data,$user);
$result=$db->update("Provvigione", $data, "ProvvigioneId=$ProvvigioneId and OdcIdRef=$user->OdcId");


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
                                  
                                 case "create_provvigione_dettaglio":
                                 
                                 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   create_provvigione_dettaglio();   
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