<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
include_once($classespath_."class.Form.php");
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


function SetViaggiato()
{
global $user,$db;
$storico=new StoricoOperazioni();
$storico->conn=$db;

$PrenotazioneId=$_REQUEST['PrenotazioneId'];
$Stato=$_REQUEST['Stato'];

$data['NonViaggiata']=$Stato;
$db->update("RT_Prenotazione",$data,"PrenotazioneId=".$PrenotazioneId);

    
    
}

function QuadraPullmanCorrente()
{
global $user,$db;
$storico=new StoricoOperazioni();
$storico->conn=$db;

$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['CorsaData'];
$BusId=$_REQUEST['BusId'];
$BusNumero=$_REQUEST['BusNumero'];

// seleziono tutti gli id prenotazione per cui non è stato emesso un titolo
// chiamo la funzione di emissione titoli

$sql="select * from  RT_PostviaggioBigliettiDaEmettere where OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$BusNumero and CorsaInizioItinerario=$CorsaId and DataInizioItinerario='$DataPartenza'";
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
WHERE BusId=$BusId and BusNumero=$BusNumero and CorsaId=$CorsaId and DataPartenza='$DataPartenza' AND RT_Prenotazione.PrenotazioneStato=1 and DataPartenza<=DATE_FORMAT(now(),'Y-m-d')";
$ArrObject = $db->fetch_array($sql);
$n_prenotazioni=sizeof($ArrObject); 

$nt=0;
//while ($nt<$numerotratte)
while ($nt<=$n_prenotazioni)
{
     $PrenotazioneId=$ArrObject[$nt]['PrenotazioneId'];
     $NumeroTotalePax=0;
     $prenotazione = new Prenotazione($PrenotazioneId);
     $prenotazione->conn=$db;
     $x=$prenotazione->EmettiBiglietti(0,$CorsaId,0,'EP');

    
   $nt++;
}
    echo("ok");
    exit();
    
}



if(is_object($user)) {
    
$db= new Database();
$db->connect();
$user->conn=$db;
$permessi=$user->get_permessi_modulo($ModuloId);
if (sizeof($permessi)>0)
{   
	
	
		if (!empty($_REQUEST))
		{
			switch($_REQUEST['do']) {
                            
                            
                            
                                case "SetViaggiato":
                                 
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                      SetViaggiato();   
                                  else
                                    Errors::$ErrorePermessiModuloFunzione;
                                  
                                  break;  
				
                                       
                               case "QuadraPullmanCorrente":
                                 
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                      QuadraPullmanCorrente();   
                                  else
                                    Errors::$ErrorePermessiModuloFunzione;
                                  
                                  break;  
                                
                                
                               
                               
                                
                                
                                		
				
                                
                }
              }
	} // end verifica permessi
	else {
           echo("no");
            
        }

}

// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}

?>
