<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
$db=new Database();
$conn=$db->connect();    

$paymentID = $_REQUEST['paymentid'];
$result = $_REQUEST['result'];
$auth = $_REQUEST['auth'];
$ref = $_REQUEST['ref'];
$tranid = $_REQUEST['tranid'];
$trackid = $_REQUEST['trackid'];
$details = $_REQUEST['udf1'];
$responsecode = $_REQUEST['responsecode'];
 

  $PrenotazioneId=$_POST['trackid'];
  $CodiceTransazione=$_POST['tranid'];
  $payment_status=$_REQUEST['result'];


  
  
  
// verifico che non esista il codice transazione
$s="select PrenotazioneTransazioneWeb from RT_PrenotazioneTransazione where CodiceTransazione='$CodiceTransazione'";
$row=$db->query_first($s);

if (empty($row['PrenotazioneTransazioneWeb']))
{

 //verifico che lo stato sia completed   
 if (($responsecode=='00') or ($responsecode=='000'))
{
    
   
$sql="select PrenotazioneId from RT_Prenotazione where Stato=1 and Cancella=0 and PrenotazioneStato=11  and PrenotazioneId=$PrenotazioneId";
//die($sql);
$row=$db->query_first($sql);

if (!empty($row['PrenotazioneId']))
{
$data=null;
$data['PrenotazioneId']=$PrenotazioneId;
$data['OpeIns']=$OperatoreId;
$data['SedeIns']=$SedeId;
$data['DataIns']=date('Y-m-d H:i:s');
$data['IpIns']=getenv('REMOTE_ADDR');  
$data['OdcIdRef']=$OdcId;
$data['GestoreIdRef']=$GestoreId;
$data['Cancella']=0;
$data['Stato']=1;
$data['TipoPagamentoId']=2;
$data['CodiceTransazione']=$CodiceTransazione;

$udf4=$_REQUEST['udf4'];
$udf4arr=explode(";",$udf4);
$cliente_nome=$udf4arr[0];
$ClienteEmail=$udf4arr[1];
$importo=$udf4arr[2];
$reply = "REDIRECT=" . "http://www.roccobus.it/grazie.php?OrderId=".$PrenotazioneId."&em=".$ClienteEmail;
$data['payment_type']="instant";
$data['payment_status']='Completed';
//$data['address_status']=$_POST['address_status'];
//$data['payer_status']=$_POST['payer_status'];
/*$data['first_name']=$_POST['first_name'];*/

$data['last_name']=$cliente_nome;
$data['payer_email']=$ClienteEmail;
//$data['payer_id']=$_POST['payer_id'];
$data['mc_gross']=$importo;
$data['ImportoPrenotazione']=$importo;
$transactionId=$db->insert("RT_PrenotazioneTransazione", $data);
/*$dup['PrenotazioneStato']=1;
$up1=$db->update("RT_Prenotazione", $dup,"PrenotazioneId=".$PrenotazioneId);
$up2=$db->update("RT_PrenotazionePercorso", $dup,"PrenotazioneId=".$PrenotazioneId);*/
}
}
else
$reply = "REDIRECT=" . "http://www.roccobus.it/errore.php";

} 
else
$reply = "REDIRECT=" . "http://www.roccobus.it/errore.php";

echo $reply;

//}

?>

