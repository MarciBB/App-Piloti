<?php 
$basepath="/srv/www/htdocs/rocco.braincomputing.com";
include_once("main_include.php");
$classespath_=$basepath."/protected/classes";
$modulespath_=$basepath."/protected/modules";
$config=new Config();
$run=$config->load(); 


include_once($classespath_."/class.Sede.php");
include_once($classespath_."/class.Fermata.php");
include_once($classespath_."/class.Tratta.php");
include_once($classespath_."/class.Prenotazione.php");
include_once($classespath_."/class.Corsa.php");
include_once($classespath_."/class.Orario.php");
include_once($classespath_."/class.Listino.php");
include_once($classespath_."/class.TipologiaBus.php");
$errors=new Errors();
$ModuloId=1;

$x=EmissioneAutomaticaTitoli();
function EmissioneAutomaticaTitoli()
{
global $user,$db;
$db=new Database();
$db->connect();


// seleziono tutti gli id prenotazione per cui non è stato emesso un titolo
// chiamo la funzione di emissione titoli


$sql="SELECT
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.OdcIdRef,
RT_PreparazioneBus.BusNumero,
RT_PreparazioneBus.PrenotazioneId
FROM
RT_PreparazioneBus
INNER JOIN RT_Prenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId
INNER JOIN RT_ImportoPerPrenotazione ON RT_Prenotazione.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId
INNER JOIN RT_ViewPrenotazioniAndata ON RT_PreparazioneBus.PrenotazioneId = RT_ViewPrenotazioniAndata.PrenotazioneId AND RT_PreparazioneBus.DataPartenza = RT_ViewPrenotazioniAndata.DataInizioItinerario AND RT_PreparazioneBus.CorsaId = RT_ViewPrenotazioniAndata.CorsaInizioItinerario
WHERE  RT_Prenotazione.PrenotazioneStato=1 and DataPartenza>='2013-07-2013' and DataPartenza<=DATE_FORMAT(now(),'Y-m-d') order by DataPartenza asc ,CorsaId asc";
$ArrObject = $db->fetch_array($sql);
$n_prenotazioni=sizeof($ArrObject); 

$nt=0;
//while ($nt<$numerotratte)
while ($nt<=$n_prenotazioni)
{
     $PrenotazioneId=$ArrObject[$nt]['PrenotazioneId'];
     $CorsaId=$ArrObject[$nt]['CorsaId'];
     $NumeroTotalePax=0;
     $prenotazione = new Prenotazione($PrenotazioneId);
     print("prenotazione ".$PrenotazioneId."\n");
     $prenotazione->conn=$db;
     $x=$prenotazione->EmettiBigliettiAuto(0,$CorsaId,0,'EP');
     $prenotazione=null;
     unset($prenotazione);
     if ($nt==5)
         die();
    
   $nt++;
}
    echo("ok");
    exit();
    
}



?>
