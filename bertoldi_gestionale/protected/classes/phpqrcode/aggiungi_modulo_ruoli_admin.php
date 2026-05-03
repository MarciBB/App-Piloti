<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();

include_once($classespath_."class.Form.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Odc.php");
$ModuloId=11;
$user=1;
$sede=1;
$modulo=11;

$ruolo=22;
$odc=5;
$gestore=5;



aggiungi_modulo($user,$sede,$modulo,$ruolo,$odc,$gestore);

function aggiungi_modulo($OpeIns,$SedeIns,$ModuloId,$RuoloId,$OdcId,$GestoreId)
{
    
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $storico=new StoricoOperazioni();
    $storico->conn=$db;

    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $data=$_POST['Ruolo'];
  
  $data=$storico->operazioni_insert($data,$user);
  
  $data['RuoloId']=$RuoloId;  
  $data['OdcIdRef']=$OdcId;
  $data['GestoreIdRef']=$GestoreId;
  $data['AppModuloId']=$ModuloId;
  $data['AppPermessoId']=1;
  $data['OpeIns']=$OpeIns;
  $data['SedeIns']=$SedeIns;
  
  $i=1;
  while ($i<8)
  {
        $data['AppModuloFunzioneId']=$i;
        
        print_r($data);
        $i++;
        $lastidA=$db->insert("RuoloPermesso", $data);
      
      
  }
  
  
    
    
    
    
}






?>