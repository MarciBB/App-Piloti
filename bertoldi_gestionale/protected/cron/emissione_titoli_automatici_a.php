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
include_once($classespath_."/class.DT.php");
$errors=new Errors();
$ModuloId=1;

$x=EmissioneAutomaticaTitoli();
function EmissioneAutomaticaTitoli()
{
global $user,$db;
$db=new Database();
$db->connect();


// seleziono tutti gli id prenotazione per cui non è stato emesso un titolo
// chiamoemi     la funzione di emissione titoli
$DataProcedura="2013-07-24";
$all=false;

if ($all==false)
    $DataProcedura=Date('Y-m-d');

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
WHERE  RT_Prenotazione.PrenotazioneStato=1 and DataPartenza<'$DataProcedura' order by DataPartenza asc ,CorsaId asc";
$oggi=date('Y-m-d');
$dt=new DT($oggi,'Y-m-d');
$dt->addDays(-3);
$DataMinima=$dt->getDate('Y-m-d');



if ($all==false)
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
WHERE  RT_Prenotazione.PrenotazioneStato=1 and DataPartenza>='$DataMinima' and DataPartenza<'$DataProcedura' order by DataPartenza asc ,CorsaId asc";

$ArrObject = $db->fetch_array($sql);
$n_prenotazioni=sizeof($ArrObject); 

$nt=0;
//$n_prenotazioni=50;
//while ($nt<$numerotratte)

$DataTitolo=date('Y-m-d H:i:s');

    
while ($nt<=$n_prenotazioni)
{
     $PrenotazioneId=$ArrObject[$nt]['PrenotazioneId'];
     
     $DataPartenza=$ArrObject[$nt]['DataPartenza']." 23:59:59";
     
     if ($all==false)
         $DataTitolo=$DataPartenza;
     
     $CorsaId=$ArrObject[$nt]['CorsaId'];
     $NumeroTotalePax=0;
     $prenotazione = new Prenotazione($PrenotazioneId);
     print("prenotazione ".$PrenotazioneId."\n");
     $prenotazione->conn=$db;
     $x=$prenotazione->EmettiBigliettiAuto(0,$CorsaId,0,'EP',$DataTitolo);
     $prenotazione=null;
     unset($prenotazione);
    
    
   $nt++;
}
    echo("ok");
    exit();
    
}



?>
