<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
$db=new Database();
$conn=$db->connect();    


if (true) {



  $PrenotazioneId=11330;
  $CodiceTransazione='5DH51593HJ091510Y';
  $mc_gross=62.00;
  
$s="select TotalePrenotazione from RT_PrenotazioneLista where PrenotazioneId=$PrenotazioneId group by PrenotazioneId";  
$row=$db->query_first($s);
$totale_prenotazione=$row['TotalePrenotazione'];
$payment_status='Completed';


// verifico che non esista il codice transazione
$s="select PrenotazioneTransazioneWeb from RT_PrenotazioneTransazione where CodiceTransazione='$CodiceTransazione'";
$row=$db->query_first($s);

if (empty($row['PrenotazioneTransazioneWeb']))
{

 //verifico che lo stato sia completed   
 if ($payment_status=='Completed')
{
    
   
$sql="select PrenotazioneId from RT_Prenotazione where Stato=1 and Cancella=0 and PrenotazioneStato=11 and OpeIns=$OperatoreId and PrenotazioneId=$PrenotazioneId";
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
$data['TipoPagamentoId']=1;
$data['CodiceTransazione']=$CodiceTransazione;
$data['payment_status']=$_POST['payment_status'];
/*$data['payment_type']=$_POST['payment_type'];

$data['address_status']=$_POST['address_status'];
$data['payer_status']=$_POST['payer_status'];
$data['first_name']=$_POST['first_name'];
$data['last_name']=$_POST['last_name'];
$data['payer_email']=$_POST['payer_email'];
$data['payer_id']=$_POST['payer_id'];
$data['mc_gross']=$_POST['mc_gross'];*/
$data['ImportoPrenotazione']=$totale_prenotazione;
$transactionId=$db->insert("RT_PrenotazioneTransazione", $data);
/*$dup['PrenotazioneStato']=1;
$up1=$db->update("RT_Prenotazione", $dup,"PrenotazioneId=".$PrenotazioneId);
$up2=$db->update("RT_PrenotazionePercorso", $dup,"PrenotazioneId=".$PrenotazioneId);*/
}
} 
} 

} elseif (strcmp ($res, "INVALID") == 0) {
    // log for manual investigation
}
//}

?>

