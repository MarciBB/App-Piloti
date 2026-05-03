<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Classe che permette l'interrogazione del database e la gestione di tutte le informazioni dei tragitti
 * Data ultima modifica: 24-09-2014
 *
 * @author Marco Casaburi
 */
class WebSearch {
    
  
    public $Id;
    public $conn;
    public $ArrSearch;
    public $ArrCorseAndata;  
    public $ArrCorseRitorno;  
    public $ItinerarioIdAndata;
    public $ItinerarioIdRitorno;
    public $RiduzioneAndata;
    public $RiduzioneRitorno;
    public $RiduzioneAR;
    public $ScontoAndataRitorno;
     
    
function __construct($criteri) {
    $this->ArrSearch = $criteri;
    $this->ArrCorseAndata=null;
    $this->ArrCorseRitorno=null;
    $this->ItinerarioIdAndata=-1;
    $this->ItinerarioIdRitorno=-1;
    
    $this->RiduzioneAndata=0;
    $this->RiduzioneRitorno=0;
    $this->RiduzioneAR=0;
    $this->ScontoAndataRitorno = Config::$scontoAR;
    
}

//public function GetScontoPromozioneAttiva($CorsaId,$DataCorsa,$Pax,$TipoBigliettoId)
//{
    
        //global $user;
        /*$db=$this->conn;
        
       $PostiPrenotati=1;
       $PostiPrenotati1=0;
        $sql="select TotalePaxPrenotati from RT_ViewSingolaCorsaPostiRealmentePrenotati where CorsaId=$CorsaId and CorsaDataPartenza='$DataCorsa'";
         $row = $db->query_first($sql);  
         if (!empty($row['TotalePaxPrenotati']))  
         {
              $PostiPrenotati=$row['TotalePaxPrenotati'];
               $PostiPrenotati1=$PostiPrenotati;
         }

        $sql="SELECT
RT_Scontistica.DaPax,
RT_Scontistica.APax,
RT_Scontistica.AttivaDal,
RT_Scontistica.AttivaAl,
RT_ScontisticaBiglietto.BigliettoId,
RT_ScontisticaBiglietto.Prezzo,
RT_ScontisticaCorsa.Dal,
RT_ScontisticaCorsa.Al,
RT_ScontisticaCorsaDettaglio.CorsaId
FROM
RT_Scontistica
INNER JOIN RT_ScontisticaBiglietto ON RT_Scontistica.ListinoId = RT_ScontisticaBiglietto.ListinoId
INNER JOIN RT_ScontisticaCorsa ON RT_ScontisticaBiglietto.ListinoId = RT_ScontisticaCorsa.ListinoId
INNER JOIN RT_ScontisticaCorsaDettaglio ON RT_ScontisticaCorsa.ScontisticaCorsaId = RT_ScontisticaCorsaDettaglio.ScontisticaCorsaId
WHERE
RT_ScontisticaBiglietto.BigliettoId = $TipoBigliettoId AND
RT_Scontistica.DaPax <= $PostiPrenotati AND
RT_Scontistica.APax >= $PostiPrenotati AND
CorsaId=$CorsaId and 
RT_ScontisticaCorsa.Dal <='$DataCorsa' AND
RT_ScontisticaCorsa.Al >= '$DataCorsa' AND    
RT_Scontistica.Stato = 1 AND
RT_Scontistica.Cancella = 0 AND
RT_ScontisticaBiglietto.Stato = 1 AND
RT_ScontisticaBiglietto.Cancella = 0 AND
RT_Scontistica.AttivaDal <= Now() AND
RT_Scontistica.AttivaAl >= Now() AND
RT_ScontisticaCorsa.Stato = 1
order by RT_Scontistica.ListinoId desc";

       
        $row = $db->query_first($sql);  
 $Sconto=0;
 $PostiInPromozione=0;
 if (!empty($row['Prezzo']))  
 {
      $Sconto=$row['Prezzo'];
      $Da=$row['DaPax'];
      $A=$row['APax'];
      $PostiInPromozione=$A-$Da-$PostiPrenotati1+1;
     
      if ($PostiInPromozione>=$Pax)
      {
        $PostiInPromozione=$Pax;  
        $Sconto=$Sconto*$PostiInPromozione;  
      }
        
   else {
        $Sconto=$Sconto*($PostiInPromozione);
        }
     
 }
    */
//    $Sconto=0;
        
   
//    return $Sconto;
    
    
    
//}

public function SetDataInizioA($value)
     {
    
    $this->ArrSearch['DataPartenzaAndata']=$value;
    
}   
public function SetDataInizioR($value)
     {
    
    $this->ArrSearch['DataPartenzaRitorno']=$value;
    
}

public function setItinerarioAndataScelto($iditinerario)
{
    $this->ItinerarioIdAndata=$iditinerario;
    
}

public function setItinerarioRitornoScelto($iditinerario)
{
    $this->ItinerarioIdRitorno=$iditinerario;
    
}

function getSearchGraphComunePickup($testo,$FermataDropOff = null,$TipoPercorsoId = null) {
	$comuniPickup = array();

	$sql = "SELECT DISTINCT
				comune.ComuneId, comune.Comune,
				Provincia.Provincia, Provincia.sigla,
				Nazione.Nazione
			FROM RT_CorsaTariffa AS rt_corsatariffa
			JOIN Comune AS comune ON (rt_corsatariffa.FermataPickup = comune.ComuneId)
			
			INNER JOIN Provincia ON comune.provincia = Provincia.ProvinciaId
			INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
			INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
			
			WHERE comune.Comune LIKE '%$testo%'  
            AND comune.ComuneId IN (
                SELECT ComuneId FROM RT_Fermata f 
                LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
                where f.ComuneId = comune.ComuneId and f.Stato = 1 
                and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
            ) ";
	if(isset($FermataDropOff) && $FermataDropOff!=""){
		$sql .= " AND comune.ComuneId <> $FermataDropOff";
	}
	$sql .= " ORDER BY comune.Comune";
	$comuniPickup = $this->conn->fetch_array($sql);
	
	return $comuniPickup;
}


function getSearchGraphFermateDropOff($ComunePickupId, $FermataPickup, $testo, $TipoPercorsoId = null) {
	$comuniDropOff = array();
	
	if(isset($ComunePickupId)) {
	    $sql = "SELECT c.FermataDropOff as ComuneId, comune.Comune, Provincia.Provincia, Provincia.sigla,
				Nazione.Nazione
                    FROM RT_CorsaTariffa c
					LEFT JOIN RT_Fermata f on f.ComuneId = c.FermataDropOff
					LEFT JOIN RT_Tratta t ON (t.TrattaId = f.TrattaId)
                    LEFT JOIN Comune comune ON comune.ComuneId = f.ComuneId 
                INNER JOIN Provincia ON comune.provincia = Provincia.ProvinciaId
				INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
				INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId                   
                WHERE c.TipologiaBigliettoId = 17 AND c.Tariffa > 0 AND c.FermataPickup = $ComunePickupId AND f.Stato = 1
					AND t.Stato = 1 AND
            		 t.Cancella = 0 AND
            		 t.DaConfermare = 0
                    AND (t.LineaId = 16 OR t.LineaId = 17)
                    AND comune.Comune LIKE '%$testo%' 
                    GROUP BY c.FermataDropOff, comune.Comune";
	    $comuniDropOff = $this->conn->fetch_array($sql);
	    return $comuniDropOff;
	}
	
	
	$sql = "SELECT DISTINCT
				comune.ComuneId AS ComuneId,
				comune.Comune AS Comune,
				Provincia.Provincia, Provincia.sigla,
				Nazione.Nazione
				FROM
				RT_CorsaTariffa AS rt_corsatariffa
				JOIN Comune AS comune ON (rt_corsatariffa.FermataDropOff = comune.ComuneId)
				INNER JOIN Provincia ON comune.provincia = Provincia.ProvinciaId
				INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
				INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
				WHERE comune.Comune LIKE '%$testo%' 
                AND comune.ComuneId IN (
                    SELECT ComuneId FROM RT_Fermata f 
                    LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
                    where f.ComuneId = comune.ComuneId and f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
                )";
	if(isset($ComunePickupId) && $ComunePickupId!=""){
		$sql .= " AND comune.ComuneId <> $ComunePickupId";
	}
	$sql .= " ORDER BY comune.Comune";
	$comuniDropOff = $this->conn->fetch_array($sql);
	
	return $comuniDropOff;
}

function readCacheRoute(){
    $filePath = "chaceRoute.log";
    if (file_exists($filePath)){
        $objData = file_get_contents($filePath);
        $obj = unserialize($objData);
        if (!empty($obj) && isset($obj['save'])){
            $data = $obj['save'];
            if($data >= date('Y-m-d')) {
                unset($obj['save']);
                return $obj;
            } else {
                return null;
            }
        }
    }
    return null;
}


function getSearchComunePickup($testo,$FermataDropOff,$All,$TipoPercorsoId)
{
    if ((empty($FermataDropOff)) and (($All==0)))
        {
      
          
$sql="SELECT
Comune.ComuneId,
Comune.Comune,
Provincia.Provincia,
Provincia.sigla,
RT_Fermata.FermataNome,
RT_Percorso.TipoPercorsoId,
Nazione.Nazione
FROM
RT_Fermata
INNER JOIN Comune ON RT_Fermata.ComuneId = Comune.ComuneId
INNER JOIN Provincia ON Comune.provincia = Provincia.ProvinciaId
INNER JOIN RT_Tratta ON RT_Fermata.TrattaId = RT_Tratta.TrattaId
INNER JOIN RT_Linea ON RT_Tratta.LineaId = RT_Linea.LineaId
INNER JOIN RT_Percorso ON RT_Linea.PercorsoId = RT_Percorso.PercorsoId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
WHERE
RT_Fermata.IsPickup = 1 AND
RT_Fermata.Stato = 1 AND
RT_Fermata.Cancella = 0 AND
RT_Fermata.WebSelling = 1 and 
RT_Fermata.IsDaConfermare = 0 and 
Comune.Comune like '%$testo%' and 
RT_Percorso.TipoPercorsoId=$TipoPercorsoId   
GROUP BY
RT_Fermata.FermataNome,
Comune.Comune,
Provincia.Provincia,
Provincia.sigla,
Comune.ComuneId,
RT_Percorso.TipoPercorsoId,
Nazione.NazioneId
ORDER BY Nazione.Nazione,Comune.Comune ASC,RT_Fermata.FermataNome asc";          
 
   
    }
    elseif ((empty($FermataDropOff)) and (($All==1)))
    {
       $sql="SELECT
ComunePickup.Comune AS Comune,
ComunePickup.ComuneId AS ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataNome,
Nazione.Nazione
FROM
((RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId))))
INNER JOIN Comune AS ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId
INNER JOIN Comune AS ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
INNER JOIN Provincia ON ComunePickup.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
WHERE RT_PickupPerCorsa.TipoPercorsoId=$TipoPercorsoId and ( (RT_PickupPerCorsa.IsPickup = 1)  AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.IsDaConfermare=0))
GROUP BY ComunePickup.Comune, RT_PickupPerCorsa.FermataNome,Nazione.NazioneId
order by Nazione.Nazione,ComunePickup.Comune asc , RT_PickupPerCorsa.FermataNome";
       
    }
     elseif ((!empty($FermataDropOff)) and (($All==1)))
    {
       $sql="SELECT
ComunePickup.Comune AS Comune,
ComunePickup.ComuneId AS ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataNome
FROM ((RT_PickupPerCorsa INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId)))) INNER JOIN Comune as ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId INNER JOIN Comune as ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
WHERE RT_PickupPerCorsa.TipoPercorsoId=$TipoPercorsoId and ( (RT_PickupPerCorsa.IsPickup = 1)  AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.IsDaConfermare=0)) and  RT_DropOffPerCorsa.FermataNome like '%$FermataDropOff%' and (RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) AND (RT_DropOffPerCorsa.IsDaConfermare=0)
GROUP BY ComunePickup.Comune, RT_PickupPerCorsa.FermataNome
order by ComunePickup.Comune asc , RT_PickupPerCorsa.FermataNome";
       
      $sql="SELECT
ComunePickup.Comune AS Comune,
ComunePickup.ComuneId AS ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataNome,
Nazione.Nazione
FROM
((RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId))))
INNER JOIN Comune AS ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId
INNER JOIN Comune AS ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
INNER JOIN Provincia ON ComunePickup.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
WHERE RT_PickupPerCorsa.TipoPercorsoId=$TipoPercorsoId and ( (RT_PickupPerCorsa.IsPickup = 1)  AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.IsDaConfermare=0)) and  RT_DropOffPerCorsa.FermataNome like '%$FermataDropOff%' and (RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) AND (RT_DropOffPerCorsa.IsDaConfermare=0)
GROUP BY ComunePickup.Comune, RT_PickupPerCorsa.FermataNome,Nazione.NazioneId
order by Nazione.Nazione,ComunePickup.Comune asc , RT_PickupPerCorsa.FermataNome";
        
    }
   else
    {
       
         $sql="SELECT
ComunePickup.Comune AS Comune,
ComunePickup.ComuneId AS ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataNome
FROM ((RT_PickupPerCorsa INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId)))) INNER JOIN Comune as ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId INNER JOIN Comune as ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
WHERE RT_PickupPerCorsa.TipoPercorsoId=$TipoPercorsoId and ( ComunePickup.Comune  like '%$testo%' and (RT_PickupPerCorsa.IsPickup = 1)  AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.IsDaConfermare=0)) and  RT_DropOffPerCorsa.FermataNome like '%$FermataDropOff%' and (RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) AND (RT_DropOffPerCorsa.IsDaConfermare=0)
GROUP BY ComunePickup.Comune, RT_PickupPerCorsa.FermataNome
order by ComunePickup.Comune asc , RT_PickupPerCorsa.FermataNome";
       
         $sql="SELECT
ComunePickup.Comune AS Comune,
ComunePickup.ComuneId AS ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataNome,
Nazione.Nazione
FROM
((RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId))))
INNER JOIN Comune AS ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId
INNER JOIN Comune AS ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
INNER JOIN Provincia ON ComunePickup.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
WHERE RT_PickupPerCorsa.TipoPercorsoId=$TipoPercorsoId and ( ComunePickup.Comune  like '%$testo%' and (RT_PickupPerCorsa.IsPickup = 1)  AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.IsDaConfermare=0)) and  RT_DropOffPerCorsa.FermataNome like '%$FermataDropOff%' and (RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) AND (RT_DropOffPerCorsa.IsDaConfermare=0)
GROUP BY ComunePickup.Comune, RT_PickupPerCorsa.FermataNome,Nazione.NazioneId
order by Nazione.Nazione,ComunePickup.Comune asc , RT_PickupPerCorsa.FermataNome
";
         
   /*    $sql="SELECT
ComunePickup.Comune AS Comune,
ComunePickup.ComuneId AS ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataNome
FROM ((RT_PickupPerCorsa INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId)))) INNER JOIN Comune as ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId INNER JOIN Comune as ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
WHERE ( (RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) and (RT_DropOffPerCorsa.IsDaConfermare=0)  (RT_PickupPerCorsa.IsPickup = 1)  AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.IsDaConfermare=0)) and  RT_DropOffPerCorsa.FermataNome like '%$FermataDropOff%' and ComunePickup.Comune like '%$testo%'
GROUP BY ComunePickup.Comune, RT_PickupPerCorsa.FermataNome
order by ComunePickup.Comune asc , RT_PickupPerCorsa.FermataNome";*/
        
    }    
   //  echo($sql);
   return $this->conn->fetch_array($sql);
}

function getSearchFermateDropOff($ComunePickupId,$FermataPickup,$testo,$All,$TipoPercorsoId)
{
    $sql="";

if (($ComunePickupId>0) and ($All==0))   
    {
    $sql="SELECT
ComunePickup.Comune,
ComunePickup.ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataPickup,
ComuneDropOff.Comune AS ComuneDropOff,
ComuneDropOff.ComuneId AS ComuneIdDropOff,
RT_DropOffPerCorsa.FermataNome AS FermataDropOff,
Nazione.Nazione
FROM
((RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId))))
INNER JOIN Comune AS ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId
INNER JOIN Comune AS ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
INNER JOIN Provincia ON ComuneDropOff.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
WHERE RT_DropOffPerCorsa.TipoPercorsoId=$TipoPercorsoId and ( (RT_PickupPerCorsa.IsPickup = 1) and (RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_DropOffPerCorsa.IsDaConfermare=0) AND (RT_PickupPerCorsa.IsDaConfermare=0) AND ComunePickup.ComuneId = '$ComunePickupId') and (RT_PickupPerCorsa.FermataNome='$FermataPickup') and ComuneDropOff.Comune like '%$testo%'
GROUP BY Nazione.NazioneId,ComuneDropOff.Comune, ComunePickup.Comune, RT_PickupPerCorsa.FermataNome, RT_PickupPerCorsa.FermataNome,ComuneDropOff.ComuneId,RT_DropOffPerCorsa.FermataNome
order by Nazione.Nazione,ComuneDropOff.Comune asc , RT_DropOffPerCorsa.FermataNome";
}
elseif (($ComunePickupId>0) and ($All==1))   
    {
    $sql="SELECT
ComunePickup.Comune,
ComunePickup.ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataPickup,
ComuneDropOff.Comune AS ComuneDropOff,
ComuneDropOff.ComuneId AS ComuneIdDropOff,
RT_DropOffPerCorsa.FermataNome AS FermataDropOff,
Nazione.Nazione
FROM
((RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId))))
INNER JOIN Comune AS ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId
INNER JOIN Comune AS ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
INNER JOIN Provincia ON ComuneDropOff.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
WHERE RT_DropOffPerCorsa.TipoPercorsoId=$TipoPercorsoId and ( (RT_PickupPerCorsa.IsPickup = 1) and (RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_DropOffPerCorsa.IsDaConfermare=0) AND (RT_PickupPerCorsa.IsDaConfermare=0) AND ComunePickup.ComuneId = '$ComunePickupId') and (RT_PickupPerCorsa.FermataNome='$FermataPickup')
GROUP BY Nazione.NazioneId, ComuneDropOff.Comune, ComunePickup.Comune, RT_PickupPerCorsa.FermataNome, RT_PickupPerCorsa.FermataNome,ComuneDropOff.ComuneId,RT_DropOffPerCorsa.FermataNome
order by Nazione.Nazione,ComuneDropOff.Comune asc , RT_DropOffPerCorsa.FermataNome";
}
elseif (!empty($ComunePickupId))   
    {
    $sql="SELECT
ComunePickup.Comune,
ComunePickup.ComuneId,
RT_PickupPerCorsa.FermataNome AS FermataPickup,
ComuneDropOff.Comune AS ComuneDropOff,
ComuneDropOff.ComuneId AS ComuneIdDropOff,
RT_DropOffPerCorsa.FermataNome AS FermataDropOff,
Nazione.Nazione
FROM
((RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId))))
INNER JOIN Comune AS ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId
INNER JOIN Comune AS ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
INNER JOIN Provincia ON ComuneDropOff.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
WHERE RT_DropOffPerCorsa.TipoPercorsoId=$TipoPercorsoId and ( (RT_PickupPerCorsa.IsPickup = 1) and (RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_DropOffPerCorsa.IsDaConfermare=0) AND (RT_PickupPerCorsa.IsDaConfermare=0))
GROUP BY Nazione.NazioneId,ComuneDropOff.Comune, ComunePickup.Comune, RT_PickupPerCorsa.FermataNome, RT_PickupPerCorsa.FermataNome,ComuneDropOff.ComuneId,RT_DropOffPerCorsa.FermataNome
order by Nazione.Nazione,ComuneDropOff.Comune asc , RT_DropOffPerCorsa.FermataNome";
}
else
 {
     $sql="SELECT
ComuneDropOff.Comune AS ComuneDropOff,
ComuneDropOff.ComuneId AS ComuneIdDropOff,
RT_DropOffPerCorsa.FermataNome AS FermataDropOff,
Nazione.Nazione

FROM
((RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON ((RT_DropOffPerCorsa.CorsaId = RT_PickupPerCorsa.CorsaId))))
INNER JOIN Comune AS ComunePickup ON RT_PickupPerCorsa.ComuneId = ComunePickup.ComuneId
INNER JOIN Comune AS ComuneDropOff ON RT_DropOffPerCorsa.ComuneId = ComuneDropOff.ComuneId
INNER JOIN Provincia ON ComuneDropOff.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
WHERE RT_DropOffPerCorsa.TipoPercorsoId=$TipoPercorsoId and ((RT_DropOffPerCorsa.IsDropOff = 1)  AND (RT_DropOffPerCorsa.WebSelling=1) AND (RT_PickupPerCorsa.WebSelling=1) AND (RT_DropOffPerCorsa.IsDaConfermare=0) AND (RT_PickupPerCorsa.IsDaConfermare=0) AND ComuneDropOff.Comune like '%$testo%')
GROUP BY Nazione.NazioneId,ComuneDropOff.Comune, ComuneDropOff.Comune, RT_DropOffPerCorsa.FermataNome
order by Nazione.Nazione,ComuneDropOff.Comune asc , RT_DropOffPerCorsa.FermataNome";
}   

//echo($sql);
 return $this->conn->fetch_array($sql);   
}

function getSeachNumeroTratte($FermataPickupID,$FermataDropOffID)
{
$sql="SELECT RT_Fermata.FermataId, RT_Fermata.TrattaId, RT_Fermata.FermataPeso, Comune.Comune
FROM RT_Fermata INNER JOIN Comune ON RT_Fermata.ComuneId = Comune.ComuneId
WHERE RT_Fermata.WebSelling = 1 and RT_Fermata.IsDaConfermare = 0 and RT_Fermata.IsPickup = 1 AND RT_Fermata.Stato = 1 AND RT_Fermata.Cancella = 0 AND Comune.Comune = '$ComunePickup'";
    
 // recupero trattaid,fermatapeso,trattapes   
    
}

function getCorseDisponibiliByDateGrafo($Tragitto, $viaggio = 'A', $Data = null, $CorsaId = null){  
	if(!isset($Data)){
		$DataCorrente = Date('Y-m-d');
	}else{
		$DataCorrente = $Data;
	}
	
	if ($Tragitto=='R')
	{
		//ritorno
		$DataPartenza = $this->ArrSearch['DataPartenzaRitorno'];
		$ComuneDropOffId = $this->ArrSearch['ComuneIdPickup'];
		$ComunePickupId = $this->ArrSearch['ComuneIdDropOff'];
		if(!isset($CorsaId)) {
			$sql = "SELECT LineaRitorno FROM RT_Linea WHERE LineaId=".$this->ArrSearch['TipoPercorsoId'];
			$row = $this->conn->query_first($sql);
			$TipoPercorsoId = $row['LineaRitorno'];
			
			$kmTemp = 0;
			$TrattePercorse = array();
			
			$sql="select * from RT_PercorsoBreveWeb where ComunePickupId = $ComuneDropOffId and ComuneDropOffId = $ComunePickupId and KmPercorsi > 0";
			
			$r = $this->conn->query_first($sql);
			
			if (!empty($r['PercorsoBreveId'])) {
				$kmTemp = $r['KmPercorsi'];
			}
			else
			{
				$grafo = new GrafoTratte($this->ArrSearch['TipoPercorsoId'], null, $this->conn, $ComuneDropOffId, $ComunePickupId);
				if(isset($grafo->flotta[0])){
					foreach ($grafo->flotta[0]->percorso as $arco){
						$kmTemp += $grafo->graph->edges[$arco]->peso;
					}
				}
			}
		} else {
			$sql = "SELECT LineaId FROM RT_Corsa WHERE CorsaId = " . $CorsaId;
			$row = $this->conn->query_first($sql);
			$TipoPercorsoId = $row['LineaId'];
		}
		
	} else {
		//andata
		$DataPartenza = $this->ArrSearch['DataPartenzaAndata'];
		$ComunePickupId =  $this->ArrSearch['ComuneIdPickup'];
		$ComuneDropOffId = $this->ArrSearch['ComuneIdDropOff'];
		
		$TipoPercorsoId= $this->ArrSearch['LineaId'];
	}
	$arrayDatiCorsa = array();
	
	$todays_date = date("d-m-Y");
	$today = strtotime($todays_date);
	$DataPartenza_new = strtotime($DataPartenza);
	if ($DataPartenza_new >= $today){
		list($yyyy, $mm, $dd) = explode('-', $DataPartenza);
		$GiornoSettimana = date("w",mktime(0,0,0,$mm, $dd, $yyyy));  
		
		$sql = "SELECT * FROM RT_Corsa c
				LEFT JOIN RT_CorsaSettimana cs ON (c.CorsaId = cs.CorsaId)
				LEFT JOIN RT_AppSettimana aps ON (cs.SettimanaId = aps.AppSettimanaId)
				WHERE c.Stato = 1 AND
				c.LineaId = $TipoPercorsoId AND 
				c.Cancella = 0 AND 
				(c.AttivaDal <= '$DataPartenza' AND c.AttivaAl >= '$DataPartenza') AND
				(c.VendibileDal <= '$DataCorrente' AND c.VendibileAl >= '$DataCorrente') AND
				aps.AppSettimanaGiorno = $GiornoSettimana AND 
				((c.IncludiFeriale = (SELECT Feriale FROM RT_AppCalendario WHERE AppCalendarioData = '$DataPartenza') AND c.IncludiFeriale = 1) OR
				(c.IncludiPrefestivo = (SELECT Prefestivo FROM RT_AppCalendario WHERE AppCalendarioData = '$DataPartenza') AND c.IncludiPrefestivo = 1) OR
				(c.IncludiFestivo = (SELECT Festivo FROM RT_AppCalendario WHERE AppCalendarioData = '$DataPartenza') AND c.IncludiFestivo = 1)) AND
				c.CorsaId NOT IN (SELECT CorsaId From RT_CorsaBloccoWeb WHERE DataPartenza = '$DataPartenza') AND
				c.CorsaId NOT IN (SELECT CorsaId From RT_CorsaBlocco WHERE DataPartenza = '$DataPartenza')";
				
		if(isset($CorsaId)) {
			$sql .= " AND c.CorsaId = ".$CorsaId;
		}

		$corse = $this->conn->fetch_array($sql);
		
		if(date('Y-m-d') == $DataPartenza){
			$remVector = array();
			$countCorsa = 0;
			foreach ($corse as $corsa) {
				$ora_attuale = date("H:m:s");
				if($ora_attuale>$corsa['OrarioPartenza']){
					$remVector[] = $countCorsa;
				}
				$countCorsa++;
			}
			foreach ($remVector as $index){
				unset($corse[$index]);
			}
		}

		$prenotazione = new Prenotazione();
		$prenotazione->conn = $this->conn;
		foreach ($corse as $corsa) {
				$km = 0;
				$TrattePercorse = array();	
				
				$grafo = new GrafoTratte($TipoPercorsoId, $corsa['CorsaId'], $this->conn, $ComunePickupId, $ComuneDropOffId, true);
				if(isset($grafo->flotta[0])){
					$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
						
					$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId 
					From RT_ElencoFermataOrarioPK 
					WHERE Stato=1 and Cancella=0 and IsPickup=1 and  
					CorsaId=".$corsa['CorsaId']." and ComuneId=$ComunePickupId and TrattaStato=1 order by TrattaPeso desc ";
					$arr_fermate=$this->conn->fetch_array($sql);
		        	if(isset($arr_fermate[0])) {
		        	    $trattaPartenza = $arr_fermate[0]['TrattaId'];
		        	} else {
		        	    $trattaPartenza = null;
		        	}
		        	$sql = "SELECT distinct FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=".$corsa['CorsaId']." and ComuneId=$ComuneDropOffId  and TrattaStato=1 order by TrattaPeso asc";
		        	$arr_fermate_d=$this->conn->fetch_array($sql);
		        	if(isset($arr_fermate_d[0])) {
		        	     $trattaArrivo = $arr_fermate_d[0]['TrattaId'];
		        	} else {
		        	     $trattaArrivo = null;
		        	}
					if(isset($trattaPartenza) && isset($trattaArrivo)) {
						foreach ($grafo->flotta[0]->percorso as $arco){
							$km += $grafo->graph->edges[$arco]->peso;
						}
					}
				}
				
				//$tratte = $prenotazione->GetTratte($corsa['CorsaId'], null, null, $DataPartenza, $ComunePickupId, $ComuneDropOffId);
				//foreach ($tratte as $tratta) {
				//	$TrattePercorse[$tratta['TrattaId']] = $tratta['Km'];
				//}
				//if($km != 0) {
					
					$datiCorsa['ComunePickUpId'] = $ComunePickupId;
					$datiCorsa['ComuneDropOffId'] = $ComuneDropOffId;
					$datiCorsa['CorsaId'] = $corsa['CorsaId'];
					$datiCorsa['LineaId'] = $TipoPercorsoId;
					$datiCorsa['km'] = $km;
					$datiCorsa['tratte'] = $TrattePercorse;

					//id biglietto adulti
					$standard = 17;
					
					//trovo la variazione prezzo
					$variazione_prezzo = $this->GetScontoPromozioneAttiva($corsa['CorsaId'], $DataPartenza, 1, $standard);
					
					//calcolo il prezzo totale andata
					$sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = ".$corsa['CorsaId']." AND Tariffa > 0 AND FermataPickup = $ComunePickupId AND FermataDropoff = $ComuneDropOffId";

					$tempTariffa = $this->conn->query_first($sql);
					$prezzoTotale = $tempTariffa['Tariffa'];
// 					echo $sql."<br>";
// 					echo "!!!!".$prezzoTotale."!!!!";
					if(isset($prezzoTotale) && $prezzoTotale > 0 || $Tragitto == 'R') {
						//applico la variazione di prezzo
						$prezzoTotale = $prezzoTotale + ($prezzoTotale * $variazione_prezzo / 100);
						
						//applico lo sconto del andata e ritorno
						if ($this->ArrSearch['TipoViaggioId'] == 2) {
							$prezzoTotale = $prezzoTotale - ($prezzoTotale * $this->ScontoAndataRitorno / 100); 
						}
						//effettua l'arrotonadamento del prezzo all'euro superiore
						$prezzo_frac = $prezzoTotale - floor($prezzoTotale);
						$prezzo = intval($prezzoTotale);
						if ($prezzo_frac > 0) {
							$prezzo += 1;
						}
						
						$datiCorsa['Prezzo'] = $prezzo;
	
						//ricavo la fermata di partenza
						$sql = "SELECT * FROM RT_Fermata f 
								LEFT JOIN Comune c ON (c.ComuneId=f.ComuneId)
								LEFT JOIN RT_Orario o ON (o.FermataId=f.FermataId) 
								WHERE f.ComuneId=$ComunePickupId 
                                AND o.CorsaId=".$corsa['CorsaId'];
						$rowFermata = $this->conn->query_first($sql);
						$datiCorsa['ComunePickUp'] = $rowFermata['Comune'];
						$datiCorsa['FermataPickUpId'] = $rowFermata['FermataId'];
						$datiCorsa['FermataPickUp'] = $rowFermata['FermataNome'];
						list($anno,$mese,$giorno) = explode("-",$DataPartenza);
						$datiCorsa['DataPickUp'] = date("Y-m-d",mktime(0,0,0,$mese,$giorno+$rowFermata['GiorniAggiuntivi'],$anno));
						$datiCorsa['OrarioPickUp'] = $rowFermata['Orario'];
						$datiCorsa['DataPartenza'] = $DataPartenza;
						
						//ricavo la fermata di arrivo
						$sql = "SELECT * FROM RT_Fermata f 
								LEFT JOIN Comune c ON (c.ComuneId=f.ComuneId) 
								LEFT JOIN RT_Orario o ON (o.FermataId=f.FermataId) 
								WHERE f.ComuneId=$ComuneDropOffId 
                                AND o.CorsaId=".$corsa['CorsaId'];
						$rowFermata = $this->conn->query_first($sql);
						$datiCorsa['ComuneDropOff'] = $rowFermata['Comune'];
						$datiCorsa['FermataDropOffId'] = $rowFermata['FermataId'];
						$datiCorsa['FermataDropOff'] = $rowFermata['FermataNome'];
						$datiCorsa['DataDropOff'] = date("Y-m-d",mktime(0,0,0,$mese,$giorno+$rowFermata['GiorniAggiuntivi'],$anno));
						$datiCorsa['OrarioDropOff'] = $rowFermata['Orario'];
						
						$arrayDatiCorsa[] = $datiCorsa;
					}
				//}		
		}
	}	
	if ($Tragitto == 'A') {
		$this->ArrCorseAndata = $arrayDatiCorsa;
	} else {
		$this->ArrCorseRitorno = $arrayDatiCorsa;
	}
	
	return $arrayDatiCorsa;
}


function getCorseDisponibiliByIntervalloGrafo($Tragitto){
	$DataCorrente = Date('Y-m-d');
	if ($Tragitto=='R')
	{
		//ritorno
		$DataPartenza = $this->ArrSearch['DataPartenzaRitorno'];
		$ComuneDropOffId = $this->ArrSearch['ComuneIdPickup'];
		$ComunePickupId = $this->ArrSearch['ComuneIdDropOff'];
		$sql = "SELECT LineaRitorno FROM RT_Linea WHERE LineaId=".$this->ArrSearch['LineaId'];
		$row = $this->conn->query_first($sql);
		$TipoPercorsoId = $row['LineaRitorno'];
	} else {
		//andata
		$DataPartenza = $this->ArrSearch['DataPartenzaAndata'];
		$ComunePickupId =  $this->ArrSearch['ComuneIdPickup'];
		$ComuneDropOffId = $this->ArrSearch['ComuneIdDropOff'];
	
		$TipoPercorsoId= $this->ArrSearch['LineaId'];
	}
	
	//giorni delle corse
	$arrayGiorni = array();
	for($ii = -3; $ii <= 3; $ii++){
		$Data = new DT($DataPartenza,'Y-m-d');
		$Data->addDays($ii);
		$arrayGiorni[] = $Data->getDate();
	}
	
	$dateConCorse = array();
	foreach ($arrayGiorni as $partenza){
		list($yyyy, $mm, $dd) = explode('-', $partenza);
		$GiornoSettimana = date("w",mktime(0,0,0,$mm, $dd, $yyyy));

		$sql = "SELECT * FROM RT_Corsa c
		LEFT JOIN RT_CorsaSettimana cs ON (c.CorsaId = cs.CorsaId)
		LEFT JOIN RT_AppSettimana aps ON (cs.SettimanaId = aps.AppSettimanaId)
		WHERE c.Stato = 1 AND
		c.LineaId = $TipoPercorsoId AND 
		c.Cancella = 0 AND
		(c.AttivaDal <= '$partenza' AND c.AttivaAl >= '$partenza') AND
		(c.VendibileDal <= '$DataCorrente' AND c.VendibileAl >= '$DataCorrente') AND
		aps.AppSettimanaGiorno = $GiornoSettimana AND
		((c.IncludiFeriale = (SELECT Feriale FROM RT_AppCalendario WHERE AppCalendarioData = '$partenza') AND c.IncludiFeriale = 1) OR
		(c.IncludiPrefestivo = (SELECT Prefestivo FROM RT_AppCalendario WHERE AppCalendarioData = '$partenza') AND c.IncludiPrefestivo = 1) OR
		(c.IncludiFestivo = (SELECT Festivo FROM RT_AppCalendario WHERE AppCalendarioData = '$partenza') AND c.IncludiFestivo = 1)) AND
		c.CorsaId NOT IN (SELECT CorsaId From RT_CorsaBloccoWeb WHERE DataPartenza = '$partenza') AND
		c.CorsaId NOT IN (SELECT CorsaId From RT_CorsaBlocco WHERE DataPartenza = '$partenza') AND
		NOW() < CONCAT('$partenza',' ',c.OrarioPartenza)";
		$corse = $this->conn->fetch_array($sql);
		
		if(date('Y-m-d') == $DataPartenza){
			$remVector = array();
			$countCorsa = 0;
			foreach ($corse as $corsa) {
				$ora_attuale = date("H:m:s");
				if($ora_attuale>$corsa['OrarioPartenza']){
					$remVector[] = $countCorsa;
				}
				$countCorsa++;
			}
			foreach ($remVector as $index){
				unset($corse[$index]);
			}
		}
		
		foreach ($corse as $corsa) {
			$exist = false;
			
			$sql="select * from RT_PercorsoBreveWeb where ComunePickupId = $ComunePickupId and ComuneDropOffId = $ComuneDropOffId and CorsaId = " . $corsa['CorsaId'];
			$r = $this->conn->query_first($sql);
			
			if (!empty($r['PercorsoBreveId'])) {
				$km = $r['KmPercorsi'];
				$trattaPartenza = $r['TrattaPickupId'];
				$trattaArrivo = $r['TrattaDropOffId'];
				
				if ($km > 0) {
					$exist = true;
				}
			} else {
				$grafo = new GrafoTratte($TipoPercorsoId, $corsa['CorsaId'], $this->conn, $ComunePickupId, $ComuneDropOffId);
				
				if(isset($grafo->flotta[0])) {
					$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
					$exist = true;
				}
			}
			
			if($exist){
				// controlla se le fermate sono attive
				//ricavo la fermata di partenza
				$sql = "SELECT * FROM RT_Fermata f
				LEFT JOIN Comune c ON (c.ComuneId=f.ComuneId)
				LEFT JOIN RT_Orario o ON (o.FermataId=f.FermataId)
				WHERE f.ComuneId=$ComunePickupId AND o.CorsaId=".$corsa['CorsaId'];
				$rowFermata = $this->conn->query_first($sql);
				$comunePickUp = $rowFermata['Comune'];
				$comunePickUpId = $rowFermata['ComuneId'];
				$fermataPickUp = $rowFermata['FermataNome'];

				//ricavo la fermata di arrivo
				$sql = "SELECT * FROM RT_Fermata f
				LEFT JOIN Comune c ON (c.ComuneId=f.ComuneId)
				LEFT JOIN RT_Orario o ON (o.FermataId=f.FermataId)
				WHERE f.ComuneId=$ComuneDropOffId AND o.CorsaId=".$corsa['CorsaId'];
				$rowFermata = $this->conn->query_first($sql);
				$comuneDropOff = $rowFermata['Comune'];
				$comuneDropOffId = $rowFermata['ComuneId'];
				$fermataDropOff = $rowFermata['FermataNome'];
										
				if(isset($comunePickUpId) && isset($comuneDropOffId)){
					$sql = "SELECT * FROM RT_CorsaTariffa WHERE TipologiaBigliettoId = 17 AND FermataPickup = $comunePickUpId AND FermataDropOff = $comuneDropOffId and CorsaId = ".$corsa['CorsaId'] ;
					$rowTemp = $this->conn->query_first($sql);
					if( isset($rowTemp['CorsaTariffaId']) && $rowTemp['CorsaTariffaId'] > 0){
						$fermateAttive = true;
						if ((!isset($comunePickUp) || empty($comunePickUp)) || (!isset($fermataPickUp) || empty($fermataPickUp)) || 
							(!isset($comuneDropOff) || empty($comuneDropOff)) || (!isset($fermataDropOff) || empty($fermataDropOff))) {
							$fermateAttive = false;
						}
					} else {
						$fermateAttive = false;
					}
				} else {
					$fermateAttive = false;
				}
				
				if($exist && $fermateAttive) {
					$dateConCorse[] = $partenza;
				}
			}
		}
	}
	
	return $dateConCorse;
}

function getCorseDisponibiliByDate($Tragitto,$Data=null)
{
    
    $DataCorrente=Date('Y-m-d');
    $ComunePickupId=  $this->ArrSearch['ComuneIdPickup'];
    $ComuneDropOffId=$this->ArrSearch['ComuneIdDropOff'];
    $FermataPickup=$this->ArrSearch['FermataPickup'];
    $FermataDropOff=$this->ArrSearch['FermataDropOff'];
    
    $DataPartenza=$this->ArrSearch['DataPartenzaAndata'];
    if ($Tragitto=='R')
    {
         $DataPartenza=$this->ArrSearch['DataPartenzaRitorno'];
         $ComuneDropOffId=$this->ArrSearch['ComuneIdPickup'];
         $ComunePickupId=$this->ArrSearch['ComuneIdDropOff'];
         $FermataDropOff=$this->ArrSearch['FermataPickup'];
         $FermataPickup=$this->ArrSearch['FermataDropOff'];
    }
    $TipoPercorsoId= $this->ArrSearch['TipoPercorsoId'];   
    $TipoViaggio=$this->ArrSearch['TipoViaggioId'];
   $mag=">=";
    $oremancanti="00:00:00";
    if ($TipoPercorsoId==2)
    {
         $mag=">=";
         $oremancanti="00:00:00";
    }
        $mag=">=";
    $sql="SELECT
RT_PickupPerCorsa.TrattaId AS TrattaPickup,
RT_DropOffPerCorsa.TrattaId AS TrattaDropOff,
RT_PickupPerCorsa.ComuneId AS ComunePickup,
RT_DropOffPerCorsa.ComuneId AS ComuneDropOff,
RT_PickupPerCorsa.FermataNome AS FermataPickup,
RT_DropOffPerCorsa.FermataNome AS FermataDropOff,
RT_TrattaPickup.TrattaPeso AS TrattaPesoPickup,
RT_TrattaDropOff.TrattaPeso AS TrattaPesoDropOff,
RT_TrattaPickup.MezzoId AS MezzoPickup,
RT_TrattaDropOff.MezzoId AS MezzoDropOff,
RT_PickupPerCorsa.FermataId AS FermataIdPickup,
RT_DropOffPerCorsa.FermataId AS FermataIdDropOff,
RT_TrattaPickup.NodoPeso AS NodoPesoPickup,
RT_TrattaDropOff.NodoPeso AS NodoPesoDropOff,
RT_PickupPerCorsa.CorsaId,
RT_ViewOperativitaBlocchi.PercorsoNome,
RT_ViewOperativitaBlocchi.LineaArea,
RT_ViewOperativitaBlocchi.LineaNome,
RT_ViewOperativitaBlocchi.CorsaNome,
RT_ViewOperativitaBlocchi.AppSettimanaGiorno,
RT_ViewOperativitaBlocchi.AppSettimanaGiornoDescr,
RT_ViewOperativitaBlocchi.AttivaDal,
RT_ViewOperativitaBlocchi.AttivaAl,
RT_ViewOperativitaBlocchi.AppCalendarioData,
RT_ViewOperativitaBlocchi.IncludiFeriale,
RT_ViewOperativitaBlocchi.IncludiPrefestivo,
RT_ViewOperativitaBlocchi.IncludiFestivo,
RT_ViewOperativitaBlocchi.Feriale,
RT_ViewOperativitaBlocchi.Prefestivo,
RT_ViewOperativitaBlocchi.Festivo,
RT_ViewOperativitaBlocchi.PercorsoId,
RT_ViewOperativitaBlocchi.LineaId,
RT_ViewOperativitaBlocchi.CorsaId,
RT_ViewOperativitaBlocchi.OdcIdRef,
RT_ViewOperativitaBlocchi.OrarioPartenza,
RT_ViewOperativitaBlocchi.CorsaBloccoId,
RT_ViewOperativitaBlocchi.CorsaBloccata,
RT_ViewOperativitaBlocchi.DataPartenzaFormattata,
RT_ViewOperativitaBlocchi.PostiCorsaDefault,
RT_ViewOperativitaBlocchi.PostiCorsaAggiunti,
RT_ViewOperativitaBlocchi.PostiCorsaPrenotati,
RT_ViewOperativitaBlocchi.PostiTotali,
RT_ViewOperativitaBlocchi.PostiDisponibili,
RT_ViewOperativitaBlocchi.VendibileDal,
RT_ViewOperativitaBlocchi.VendibileAl,
RT_ViewOperativitaBlocchi.OrePrimaStopVendita,
RT_ViewOperativitaBlocchi.DataOraPartenza,
RT_ViewOperativitaBlocchi.OreMancanti,
RT_ViewOperativitaBlocchi.TipologiaBusDefaultId
FROM
RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON RT_PickupPerCorsa.CorsaId = RT_DropOffPerCorsa.CorsaId
INNER JOIN RT_Tratta AS RT_TrattaPickup ON RT_PickupPerCorsa.TrattaId = RT_TrattaPickup.TrattaId
INNER JOIN RT_Tratta AS RT_TrattaDropOff ON RT_DropOffPerCorsa.TrattaId = RT_TrattaDropOff.TrattaId
INNER JOIN RT_ViewOperativitaBlocchi ON RT_DropOffPerCorsa.CorsaId = RT_ViewOperativitaBlocchi.CorsaId
WHERE
RT_ViewOperativitaBlocchi.VendibileDal<='$DataCorrente' and VendibileAl>='$DataCorrente' and
RT_ViewOperativitaBlocchi.AttivaDal<='$DataPartenza' and AttivaAl>='$DataPartenza' and
RT_PickupPerCorsa.WebSelling = 1 AND
RT_PickupPerCorsa.WebSelling = 1 AND
RT_ViewOperativitaBlocchi.OreMancanti > '$oremancanti' and 
RT_ViewOperativitaBlocchi.CorsaBloccata=0 AND
RT_PickupPerCorsa.ComuneId = $ComunePickupId AND
RT_DropOffPerCorsa.ComuneId = $ComuneDropOffId AND
RT_PickupPerCorsa.FermataNome = '$FermataPickup' AND
RT_DropOffPerCorsa.FermataNome = '$FermataDropOff' AND
RT_PickupPerCorsa.IsDaConfermare =0 AND
RT_DropOffPerCorsa.IsDaConfermare = 0 AND
RT_ViewOperativitaBlocchi.CorsaBloccata = 0 AND
RT_ViewOperativitaBlocchi.CorsaBloccoWebId is null AND
RT_PickupPerCorsa.TipoPercorsoId=$TipoPercorsoId and 
RT_DropOffPerCorsa.TipoPercorsoId=$TipoPercorsoId and     
AppCalendarioData='$DataPartenza' and AppCalendarioData$mag'$DataCorrente';";
    // RT_ViewOperativitaBlocchi.PostiDisponibili > 0 AND
return $this->conn->fetch_array($sql);
    
}


function getCorseDisponibiliByIntervallo($Tragitto)
{
    $ComunePickupId=  $this->ArrSearch['ComuneIdPickup'];
    $ComuneDropOffId=$this->ArrSearch['ComuneIdDropOff'];
    $FermataPickup=$this->ArrSearch['FermataPickup'];
    $FermataDropOff=$this->ArrSearch['FermataDropOff'];
    $DataPartenza=$this->ArrSearch['DataPartenzaAndata'];
     if ($Tragitto=='R')
         {
         $DataPartenza=$this->ArrSearch['DataPartenzaRitorno'];
          $ComuneDropOffId=  $this->ArrSearch['ComuneIdPickup'];
    $ComunePickupId=$this->ArrSearch['ComuneIdDropOff'];
     $FermataDropOff=$this->ArrSearch['FermataPickup'];
    $FermataPickup=$this->ArrSearch['FermataDropOff'];
     }
        $TipoPercorsoId= $this->ArrSearch['TipoPercorsoId'];   
     
     $DataInizio=new DT($DataPartenza,'Y-m-d');
     $DataInizio->addDays(-3);
     $DataInizioIntervallo=$DataInizio->getDate();
     
     $DataFine=new DT($DataPartenza,'Y-m-d');
     $DataFine->addDays(3);
     $DataFineIntervallo=$DataFine->getDate();
     
     
     $DataCorrente=Date('Y-m-d');
      $TipoViaggio=$this->ArrSearch['TipoViaggioId'];
     $mag=">=";
    if ($TipoPercorsoId==2)
        $mag=">=";
    $sql="SELECT
RT_PickupPerCorsa.TrattaId AS TrattaPickup,
RT_DropOffPerCorsa.TrattaId AS TrattaDropOff,
RT_PickupPerCorsa.ComuneId AS ComunePickup,
RT_DropOffPerCorsa.ComuneId AS ComuneDropOff,
RT_PickupPerCorsa.FermataNome AS FermataPickup,
RT_DropOffPerCorsa.FermataNome AS FermataDropOff,
RT_TrattaPickup.TrattaPeso AS TrattaPesoPickup,
RT_TrattaDropOff.TrattaPeso AS TrattaPesoDropOff,
RT_TrattaPickup.MezzoId AS MezzoPickup,
RT_TrattaDropOff.MezzoId AS MezzoDropOff,
RT_PickupPerCorsa.FermataId AS FermataIdPickup,
RT_DropOffPerCorsa.FermataId AS FermataIdDropOff,
RT_TrattaPickup.NodoPeso AS NodoPesoPickup,
RT_TrattaDropOff.NodoPeso AS NodoPesoDropOff,
RT_PickupPerCorsa.CorsaId,
RT_ViewOperativitaBlocchi.PercorsoNome,
RT_ViewOperativitaBlocchi.LineaArea,
RT_ViewOperativitaBlocchi.LineaNome,
RT_ViewOperativitaBlocchi.CorsaNome,
RT_ViewOperativitaBlocchi.AppSettimanaGiorno,
RT_ViewOperativitaBlocchi.AppSettimanaGiornoDescr,
RT_ViewOperativitaBlocchi.AttivaDal,
RT_ViewOperativitaBlocchi.AttivaAl,
RT_ViewOperativitaBlocchi.AppCalendarioData,
RT_ViewOperativitaBlocchi.IncludiFeriale,
RT_ViewOperativitaBlocchi.IncludiPrefestivo,
RT_ViewOperativitaBlocchi.IncludiFestivo,
RT_ViewOperativitaBlocchi.Feriale,
RT_ViewOperativitaBlocchi.Prefestivo,
RT_ViewOperativitaBlocchi.Festivo,
RT_ViewOperativitaBlocchi.PercorsoId,
RT_ViewOperativitaBlocchi.LineaId,
RT_ViewOperativitaBlocchi.CorsaId,
RT_ViewOperativitaBlocchi.OdcIdRef,
RT_ViewOperativitaBlocchi.OrarioPartenza,
RT_ViewOperativitaBlocchi.CorsaBloccoId,
RT_ViewOperativitaBlocchi.CorsaBloccata,
RT_ViewOperativitaBlocchi.DataPartenzaFormattata,
RT_ViewOperativitaBlocchi.PostiCorsaDefault,
RT_ViewOperativitaBlocchi.PostiCorsaAggiunti,
RT_ViewOperativitaBlocchi.PostiCorsaPrenotati,
RT_ViewOperativitaBlocchi.PostiTotali,
RT_ViewOperativitaBlocchi.PostiDisponibili,
RT_ViewOperativitaBlocchi.VendibileDal,
RT_ViewOperativitaBlocchi.VendibileAl,
RT_ViewOperativitaBlocchi.OrePrimaStopVendita,
RT_ViewOperativitaBlocchi.DataOraPartenza,
RT_ViewOperativitaBlocchi.OreMancanti,
RT_ViewOperativitaBlocchi.TipologiaBusDefaultId
FROM 
RT_PickupPerCorsa
INNER JOIN RT_DropOffPerCorsa ON RT_PickupPerCorsa.CorsaId = RT_DropOffPerCorsa.CorsaId
INNER JOIN RT_Tratta AS RT_TrattaPickup ON RT_PickupPerCorsa.TrattaId = RT_TrattaPickup.TrattaId
INNER JOIN RT_Tratta AS RT_TrattaDropOff ON RT_DropOffPerCorsa.TrattaId = RT_TrattaDropOff.TrattaId
INNER JOIN RT_ViewOperativitaBlocchi ON RT_DropOffPerCorsa.CorsaId = RT_ViewOperativitaBlocchi.CorsaId
WHERE
RT_PickupPerCorsa.WebSelling = 1 AND
RT_DropOffPerCorsa.WebSelling = 1 AND
RT_PickupPerCorsa.ComuneId = $ComunePickupId AND
RT_DropOffPerCorsa.ComuneId = $ComuneDropOffId AND
RT_PickupPerCorsa.FermataNome = '$FermataPickup' AND
RT_DropOffPerCorsa.FermataNome = '$FermataDropOff' AND
    RT_PickupPerCorsa.IsDaConfermare =0 AND
RT_DropOffPerCorsa.IsDaConfermare = 0 AND
RT_ViewOperativitaBlocchi.CorsaBloccata = 0 AND
RT_ViewOperativitaBlocchi.CorsaBloccoWebId is null AND
RT_ViewOperativitaBlocchi.OreMancanti > '00:00:00' AND AppCalendarioData$mag'$DataInizioIntervallo' AND AppCalendarioData<='$DataFineIntervallo' and 
RT_PickupPerCorsa.TipoPercorsoId=$TipoPercorsoId and 
RT_DropOffPerCorsa.TipoPercorsoId=$TipoPercorsoId and    
RT_ViewOperativitaBlocchi.VendibileDal<='$DataCorrente' and VendibileAl>='$DataCorrente'




GROUP BY AppCalendarioData";
//echo($sql);    
   //RT_ViewOperativitaBlocchi.PostiDisponibili > 0 AND 
return $this->conn->fetch_array($sql);
    
}

public function getListinoTratta($FermataPickupId,$FermataDropOffId,$CorsaId,$TrattaId,$TipoViaggio)
{
    
$sql="select ListinoId from RT_CorsaTariffa where CorsaId=$CorsaId and FermataPickup=$FermataPickupId and FermataDropOff=$FermataDropOffId and TrattaId=$TrattaId and Stato=1 and Cancella=0 order by CorsaTariffaId desc limit 1";    
//echo($sql);

$row=$this->conn->query_first($sql);   
$ListinoId=$row['ListinoId'];
return $ListinoId;

}

public function getPrezzoTratta($ListinoId,$TipoViaggio)
{
    

$TipologiaBigliettoId=1;
if ($TipoViaggio==2)
    $TipologiaBigliettoId=2;
    
$sql="select Prezzo from RT_ListinoBiglietto where ListinoId=$ListinoId and BigliettoId=$TipologiaBigliettoId and Stato=1 and Cancella=0 order by ListinoBigliettoId desc limit 1";    
//echo($sql);
$row=$this->conn->query_first($sql);   
$Prezzo=$row['Prezzo'];
if ($TipoViaggio==2)
    $Prezzo=$Prezzo/2;

return $Prezzo;

}

public function getPrezzoTrattaBambini($ListinoId,$TipoViaggio)
{
    

$TipologiaBigliettoId=3;
if ($TipoViaggio==2)
    $TipologiaBigliettoId=4;
    
$sql="select Prezzo from RT_ListinoBiglietto where ListinoId=$ListinoId and BigliettoId=$TipologiaBigliettoId and Stato=1 and Cancella=0 order by ListinoBigliettoId desc limit 1";    
//echo($sql);
$row=$this->conn->query_first($sql);   
$Prezzo=$row['Prezzo'];
if ($TipoViaggio==2)
    $Prezzo=$Prezzo/2;

return $Prezzo;

}


public function getCorseCalendario($arrCorse,$Tragitto)
{
    $db=$this->conn;
    
    
    if ($Tragitto=='A')
    $this->ArrCorseAndata=null;
    if ($Tragitto=='R')
    $this->ArrCorseRitorno=null;
    $ct=0;
       
      // print_r($arrCorse);
       //die();
             $arrPercorso=null; 
            while($ct<sizeof($arrCorse))
            {
               $arrPercorso=null;
                
                $TrattaPickup=$arrCorse[$ct]['TrattaPickup'];
                $TrattaDropOff=$arrCorse[$ct]['TrattaDropOff'];
                $FermataIdPickup=$arrCorse[$ct]['FermataIdPickup'];
                $FermataIdDropOff=$arrCorse[$ct]['FermataIdDropOff'];
                $FermataPickup=$arrCorse[$ct]['FermataPickup'];
                $FermataDropOff=$arrCorse[$ct]['FermataDropOff'];
                $CorsaId=$arrCorse[$ct]['CorsaId'];
                $numeroTratte=$arrCorse[$ct]['NodoPesoDropOff']+1;
                $DataPartenzaCorsa=$arrCorse[$ct]['AppCalendarioData'];
                $PostiDisponibili=$arrCorse[$ct]['PostiDisponibili'];
                $OrarioPartenza=null;
                $OrarioArrivo=null;
                $numeroTratte=2;
                
                if ($TrattaPickup==$TrattaDropOff)
                    $numeroTratte=1;
                
                
                
                $ComunePartenza1=$this->getComuneById($this->ArrSearch['ComuneIdPickup']); 
                $ComuneArrivo1=$this->getComuneById($this->ArrSearch['ComuneIdDropOff']); 
                 $ComunePartenza=$ComunePartenza1; 
                $ComuneArrivo=$ComuneArrivo1; 
                if ($Tragitto=='R')
                {
                $ComunePartenza=$ComuneArrivo1; 
                $ComuneArrivo=$ComunePartenza1; 
                }
                
                $ComunePartenzaBase=$ComunePartenza; 
                $ComuneArrivoBase=$ComuneArrivo; 
                
                
                $TipoViaggioId=$this->ArrSearch['TipoViaggioId'];
                
                if ($numeroTratte==1)
                {
                  
                  $FermataPartenza=$FermataPickup; 
                  $FermataArrivo=$FermataDropOff; 
                  
                  $sql="select GiorniAggiuntivi,FermataId,Orario from RT_Orario where CorsaId=$CorsaId and FermataId=$FermataIdPickup";
                 
                  $row22=$db->query_first($sql);
                  $DataPartenza1=Date('Y-m-d');
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     $dt=new DT($DataPartenzaCorsa,'Y-m-d');
                     $dt->addDays($ggadd);
                     $DataPartenza1=$dt->getDate('Y-m-d'); 
                     $OrarioPartenza=$row22['Orario'];
                  }
                  
                   $sql="select GiorniAggiuntivi,FermataId,Orario from RT_Orario where CorsaId=$CorsaId and FermataId=$FermataIdDropOff";
                  
                   $row22=$db->query_first($sql);
                  $DataDiscesa1=Date('Y-m-d');
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     //die($ArrPercorso[$nper]['DataOraDiscesa']);
                     $dt=new DT($DataPartenzaCorsa,'Y-m-d');
                     $dt->addDays($ggadd);
                     $DataDiscesa1=$dt->getDate('Y-m-d'); 
                    
                     $OrarioArrivo=$row22['Orario'];
                  }
                 
                   $s="SELECT RT_Fermata.FermataId, RT_Mezzo.AppMezzo,RT_Tratta.TrattaId FROM RT_Tratta
INNER JOIN RT_Fermata ON RT_Fermata.TrattaId = RT_Tratta.TrattaId INNER JOIN RT_Mezzo ON RT_Tratta.MezzoId = RT_Mezzo.AppMezzoId 
where RT_Fermata.FermataId=$FermataIdPickup";
                  $rowfermata=$db->query_first($s);
                  
                  $arrPercorso[0]['CorsaDataPartenza']=$arrCorse[$ct]['AppCalendarioData'];
                  
                  
                   $arrPercorso[0]['CorsaId']=$CorsaId;
                   $arrPercorso[0]['Mezzo']=$rowfermata['AppMezzo'];
                   $arrPercorso[0]['TrattaId']=$rowfermata['TrattaId'];
                  $arrPercorso[0]['DataPartenza']=$DataPartenza1;
                  $arrPercorso[0]['OrarioPartenza']=$OrarioPartenza;
                  $arrPercorso[0]['ComunePartenza']=$ComunePartenzaBase;
                  $arrPercorso[0]['DataArrivo']=$DataDiscesa1;
                  $arrPercorso[0]['OrarioArrivo']=$OrarioArrivo;
                  $arrPercorso[0]['ComuneArrivo']=$ComuneArrivoBase;
                  $arrPercorso[0]['PostiDisponibili']=$PostiDisponibili;
                  $arrPercorso[0]['FermataPartenza']=$FermataPickup;
                  $arrPercorso[0]['FermataArrivo']=$FermataDropOff;
                  $ListinoId=$this->getListinoTratta($FermataIdPickup, $FermataIdDropOff, $CorsaId, $TrattaPickup, $TipoViaggioId);
                  $arrPercorso[0]['ListinoId']=$ListinoId;
                  $arrPercorso[0]['PrezzoTratta']=$this->getPrezzoTratta($ListinoId, $TipoViaggioId);
                  $arrPercorso[0]['PrezzoTrattaRidotto']=$this->getPrezzoTrattaBambini($ListinoId, $TipoViaggioId);
                  
                  $arrPercorso[0]['PercorsoNome']=$arrCorse[$ct]['PercorsoNome'];  
                  $arrPercorso[0]['LineaNome']=$arrCorse[$ct]['LineaNome'];  
                  $arrPercorso[0]['CorsaNome']=$arrCorse[$ct]['CorsaNome'];  
                  
                 }
                 elseif ($numeroTratte==2)
                 {
                     $ntr=0;
                     while($ntr<$numeroTratte)
                     {
                           $f=$FermataIdDropOff;
                         if ($ntr==0)
                             $f=$FermataIdPickup;
                         $s="SELECT RT_Fermata.FermataId, RT_Mezzo.AppMezzo,RT_Tratta.TrattaId FROM RT_Tratta
INNER JOIN RT_Fermata ON RT_Fermata.TrattaId = RT_Tratta.TrattaId INNER JOIN RT_Mezzo ON RT_Tratta.MezzoId = RT_Mezzo.AppMezzoId 
where RT_Fermata.FermataId=$f";
                  $rowfermata=$db->query_first($s);
                   $arrPercorso[$ntr]['Mezzo']=$rowfermata['AppMezzo'];
                    $arrPercorso[$ntr]['TrattaId']=$rowfermata['TrattaId'];
                    $arrPercorso[$ntr]['CorsaId']=$CorsaId;
                    $arrPercorso[$ntr]['CorsaDataPartenza']=$arrCorse[$ct]['AppCalendarioData'];
                  
                         if ($ntr==0)
                         {
                             $FermataPartenza=$FermataPickup; 
                             $FermataArrivo=$FermataDropOff; 
                  
                            $sql="select GiorniAggiuntivi,FermataId,Orario from RT_Orario where CorsaId=$CorsaId and FermataId=$FermataIdPickup";
                            $row22=$db->query_first($sql);
                            $DataPartenza1=Date('Y-m-d');
                            if ($row22['FermataId']>0)
                             {
                               $ggadd=$row22['GiorniAggiuntivi'];
                               $dt=new DT($DataPartenzaCorsa,'Y-m-d');
                               $dt->addDays($ggadd);
                               $DataPartenza1=$dt->getDate('Y-m-d'); 
                               $OrarioPartenza=$row22['Orario'];
                            }
                     
                      $sq="SELECT RT_Fermata.FermataNome, RT_Fermata.IsInterscambio, RT_Fermata.TrattaId, RT_Fermata.FermataId, 
Comune.Comune, RT_Fermata.FermataPeso, RT_Orario.Orario, RT_Orario.GiorniAggiuntivi, RT_Orario.CorsaId FROM
RT_Fermata INNER JOIN Comune ON RT_Fermata.ComuneId = Comune.ComuneId INNER JOIN RT_Orario ON RT_Fermata.FermataId = RT_Orario.FermataId where RT_Fermata.TrattaId=$TrattaPickup and CorsaId=$CorsaId  order by RT_Fermata.FermataPeso desc limit 1";
          
                 $row22=$db->query_first($sq);
                
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     //$dt=new DT($dataToIns['DataPartenza'],'Y-m-d');
                     $dt=new DT($DataPartenzaCorsa,'Y-m-d');
                     $dt->addDays($ggadd);
                     $DataArrivo=$dt->getDate('Y-m-d');
                      // echo($DataArrivo);
                     $ComuneArrivo=$row22['Comune']; 
                     $FermataArrivo=$row22['FermataNome'];
                     $DataDiscesa1=$DataArrivo;
                     $OrarioArrivo=$row22['Orario'];
                    $FermataIdDropOffCalcolato=$row22['FermataId'];
                   }
                   $f=$FermataIdPickup;
                   if ($ntr==0)
                       $f=$FermataIdDropOff;
                   
                 
                       
                   
                  $arrPercorso[$ntr]['DataPartenza']=$DataPartenza1;
                  $arrPercorso[$ntr]['OrarioPartenza']=$OrarioPartenza;
                  $arrPercorso[$ntr]['ComunePartenza']=$ComunePartenzaBase;
                  $arrPercorso[$ntr]['DataArrivo']=$DataDiscesa1;
                  $arrPercorso[$ntr]['OrarioArrivo']=$OrarioArrivo;
                  $arrPercorso[$ntr]['ComuneArrivo']=$ComuneArrivo;
                  $arrPercorso[$ntr]['PostiDisponibili']=$PostiDisponibili;
                  $arrPercorso[$ntr]['FermataPartenza']=$FermataPickup;
                  $arrPercorso[$ntr]['FermataArrivo']=$FermataArrivo;
                 // $arrPercorso[$ntr]['PrezzoTratta']=$this->getPrezzoTratta($FermataIdPickup, $FermataIdDropOffCalcolato, $CorsaId, $TrattaPickup, $TipoViaggioId);
                  $ListinoId=$this->getListinoTratta($FermataIdPickup, $FermataIdDropOffCalcolato, $CorsaId, $TrattaPickup, $TipoViaggioId);
                  $arrPercorso[$ntr]['ListinoId']=$ListinoId;
                  $arrPercorso[$ntr]['PrezzoTratta']=$this->getPrezzoTratta($ListinoId, $TipoViaggioId);
                  $arrPercorso[$ntr]['PrezzoTrattaRidotto']=$this->getPrezzoTrattaBambini($ListinoId, $TipoViaggioId);
                 
                  $arrPercorso[$ntr]['PercorsoNome']=$arrCorse[$ct]['PercorsoNome'];  
                  $arrPercorso[$ntr]['LineaNome']=$arrCorse[$ct]['LineaNome'];  
                  $arrPercorso[$ntr]['CorsaNome']=$arrCorse[$ct]['CorsaNome'];  
                   
                      
                 }
                  elseif ($ntr==1)
                 {
                   $sql="select GiorniAggiuntivi,FermataId,Orario from RT_Orario where CorsaId=$CorsaId and FermataId=$FermataIdDropOff";
                   
                   $row22=$db->query_first($sql);
                  $DataDiscesa1=Date('Y-m-d');
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     $dt=new DT($DataPartenzaCorsa,'Y-m-d');
                     $dt->addDays($ggadd);
                     $DataDiscesa1=$dt->getDate('Y-m-d'); 
                     $OrarioArrivo=$row22['Orario'];
                  }
                     
                     
$sq="SELECT
RT_Fermata.FermataNome,
RT_Fermata.IsInterscambio,
RT_Fermata.TrattaId,
RT_Fermata.FermataId,
Comune.Comune,
RT_Fermata.FermataPeso,
RT_Orario.Orario,
RT_Orario.GiorniAggiuntivi,
RT_Orario.CorsaId
FROM
RT_Fermata
INNER JOIN Comune ON RT_Fermata.ComuneId = Comune.ComuneId
INNER JOIN RT_Orario ON RT_Fermata.FermataId = RT_Orario.FermataId where RT_Fermata.TrattaId=$TrattaDropOff and  CorsaId=$CorsaId order by RT_Fermata.FermataPeso asc limit 1";
                      
                 $row22=$db->query_first($sq);
                
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     $dt=new DT($DataPartenzaCorsa,'Y-m-d');
                     $dt->addDays($ggadd);
                     $DataArrivo=$dt->getDate('Y-m-d');
                     $ComunePartenza=$row22['Comune']; 
                     $FermataPartenza=$row22['FermataNome'];
                      $DataPartenza1=  $DataArrivo;
                      $OrarioPartenza=$row22['Orario'];
              $FermataIdPickupCalcolato=$row22['FermataId'];
                   } 
                   
                  $arrPercorso[$ntr]['DataPartenza']=$DataPartenza1;
                  $arrPercorso[$ntr]['OrarioPartenza']=$OrarioPartenza;
                  $arrPercorso[$ntr]['ComunePartenza']=$ComunePartenza;
                  $arrPercorso[$ntr]['DataArrivo']=$DataDiscesa1;
                  $arrPercorso[$ntr]['OrarioArrivo']=$OrarioArrivo;
                  $arrPercorso[$ntr]['ComuneArrivo']=$ComuneArrivoBase;
                  $arrPercorso[$ntr]['PostiDisponibili']=$PostiDisponibili;
                  $arrPercorso[$ntr]['FermataPartenza']=$FermataPartenza;
                  $arrPercorso[$ntr]['FermataArrivo']=$FermataDropOff;
                  $ListinoId=$this->getListinoTratta($FermataIdPickupCalcolato, $FermataIdDropOff, $CorsaId, $TrattaDropOff, $TipoViaggioId);
                  $arrPercorso[$ntr]['ListinoId']=$ListinoId;
                  $arrPercorso[$ntr]['PrezzoTratta']=$this->getPrezzoTratta($ListinoId, $TipoViaggioId);
                  $arrPercorso[$ntr]['PrezzoTrattaRidotto']=$this->getPrezzoTrattaBambini($ListinoId, $TipoViaggioId);
                  
                  $arrPercorso[$ntr]['PercorsoNome']=$arrCorse[$ct]['PercorsoNome'];  
                  $arrPercorso[$ntr]['LineaNome']=$arrCorse[$ct]['LineaNome'];  
                  $arrPercorso[$ntr]['CorsaNome']=$arrCorse[$ct]['CorsaNome'];  
               
                  }  
                         
                       
                         
                         $ntr++;
                     }
                     
                     
                
                  }    
                    if ($Tragitto=='A')
                    $this->ArrCorseAndata[]=$arrPercorso;
                    else
                    $this->ArrCorseRitorno[]=$arrPercorso;    
                
                
                $ct++;
            }
            
            if ($Tragitto=='A')
            return $this->ArrCorseAndata;
            else
              return  $this->ArrCorseRitorno;
    
}

public function getComuneById($ComuneId)
{
    $sql="select Comune from Comune where ComuneId=$ComuneId";
    $row=  $this->conn->query_first($sql);
    return $row['Comune'];
    
    
}

public function getComuneNazioneById($ComuneId)
{
	$sql="SELECT Comune.ComuneId, Comune.Comune,
			Provincia.Provincia, Provincia.sigla,
			Nazione.Nazione
			FROM Comune
			INNER JOIN Provincia ON Comune.provincia = Provincia.ProvinciaId
			INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
			INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
			WHERE
			Comune.ComuneId=$ComuneId";
	$row=  $this->conn->query_first($sql);
	return $row;


}


public function printTrattaRisultato($classe,$rowspan,$colspan,$elemento,$dimensione,$campo1,$campo2,$campo3)
  {
    
                     $conta=0;
                     // prezzo pieno='PrezzoTratta'
                     //prezzo scontato='PrezzoScontatoTrattaF'
                     //sconto=Sconto
                         echo("<td>");
                      if (($campo1=='PrezzoTratta') and ($elemento[$conta]['Sconto']>0))
                       $promozione="<p style='margin-bottom: 5px;'>Biglietto in promozione. Risparmia <strong>".$elemento[$conta]['Sconto']."&euro;</strong> per ogni biglietto</p><p style='color:#F00;margin-bottom: 5px; text-align: center; font-weight: bold; font-size: 16px'>".$elemento[$conta]['PrezzoTrattaScontatoF']."&euro;</p><p style='text-align: center;margin-bottom: 10px;font-weight: bold'><strike style='color: #666; font-style: italic'>".$elemento[$conta]['PrezzoTratta']."&euro;</strike></p><p style='margin-bottom: 0px; font-style: italic; font-size: 11px;color:#666;'>*Promozione soggetta a disponibilita' dei posti.</p>";
                    while($conta<$dimensione)
                     {
                       if (($conta>0) and ($campo2=='OrarioPartenza'))
                           echo("&nbsp;");
                       
                       elseif (($conta==0) and ($campo2=='OrarioArrivo') and ($dimensione>1))
                           echo("&nbsp;<hr class='separator' style='margin:5px 0 2px !important'>");
                       
                       else
                       {
                              
                           if (($campo1=='PrezzoTratta') and ($elemento[$conta]['Sconto']>0))
                               echo("<a class=\"promo\" title=\"".$promozione."\" href='javascript: void(0)'>".$elemento[$conta]['PrezzoTrattaScontatoF']."<img src='/images/hand_ok.png' /></a>");
                               else
                               echo($elemento[$conta][$campo1]);
                              if ($campo2!='')echo(" ".$elemento[$conta][$campo2]);
							  if($conta == 0 && $dimensione != 1){
                              	echo "<hr class='separator' style='margin:5px 0 2px !important'>";  
							  }
                           
                       }    
                      
                        
                        $conta++;
                    }   
                      echo("</td>");
}      

function getGiorniSettimana($giorno)
{
	global $dizionario;
$arrGiorni=null;
$arrGiorni[0]=$dizionario['DOMENICA'];
$arrGiorni[1]=$dizionario['LUNEDI'];
$arrGiorni[2]=$dizionario['MARTEDI'];
$arrGiorni[3]=$dizionario['MERCOLEDI'];
$arrGiorni[4]=$dizionario['GIOVEDI'];
$arrGiorni[5]=$dizionario['VENERDI'];
$arrGiorni[6]=$dizionario['SABATO'];

return $arrGiorni[$giorno];    
    
}

function nazioneComune($comuneId){
	$sql = "SELECT Comune.ComuneId, Comune.Comune,
			Provincia.Provincia, Provincia.sigla,
			Nazione.Nazione
			FROM Comune
			INNER JOIN Provincia ON Comune.provincia = Provincia.ProvinciaId
			INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
			INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
			WHERE
			Comune.ComuneId=$idComune";
	$row = $this->conn->first_query($sql);
	return $row['Nazione'];
}

	public function ListinoMoltiplicatore($Km, $CorsaId = null)
	{
		$db = $this->conn;
	
		$sql="select Moltiplicatore from RT_ListinoMoltiplicatore where KmDa <= $Km and KmA >= $Km";
		// echo($sql);
		$row=$db->query_first($sql);
		if (!empty($row['Moltiplicatore']))
			return $row['Moltiplicatore'];
		else
			return 1;
	}

	public function GetScontoPromozioneAttiva($CorsaId, $DataCorsa, $Pax, $TipoBigliettoId, &$listinoId = null) {
		
		$db = $this->conn;
		
// 		$PostiPrenotati = 1;
// 		$sql = "select TotalePaxPrenotati from RT_ViewSingolaCorsaPostiRealmentePrenotati where CorsaId=$CorsaId and CorsaDataPartenza='$DataCorsa'";
// 		$row = $db->query_first ( $sql );
// 		if (! empty ( $row ['TotalePaxPrenotati'] )) {
// 			$PostiPrenotati = $row ['TotalePaxPrenotati'];
// 		}
		$PostiPrenotati=1;		
		$sql="select
		        `RT_PrenotazionePercorso`.`CorsaId` AS `CorsaId`,
		        `RT_PrenotazionePercorso`.`CorsaDataPartenza` AS `CorsaDataPartenza`,
		        (sum((`RT_Prenotazione`.`TotalePaxPrenotati` - `RT_PrenotazionePercorso`.`PasseggeriEsclusi`)) - (select
		                count(0)
		            from
		                (`RT_PrenotazioneTitolo` `t`
		                left join `RT_PrenotazioneDettaglio` `n` ON (((`t`.`PrenotazioneNumeroId` = `n`.`PrenotazioneNumero`)
		                    and (`t`.`PrenotazioneId` = `n`.`PrenotazioneId`))))
		            where
		                ((`n`.`CorsaId` = `RT_PrenotazionePercorso`.`CorsaId`)
		                    and (`n`.`DataInizioItinerario` = `RT_PrenotazionePercorso`.`CorsaDataPartenza`)
		                    and (`t`.`Codice` like 'E-%')))) AS `TotalePaxPrenotati`,
		        `RT_Prenotazione`.`OdcIdRef` AS `OdcIdRef`
		    from
		        ((`RT_Prenotazione`
		        join `RT_PrenotazionePercorso` ON ((`RT_Prenotazione`.`PrenotazioneId` = `RT_PrenotazionePercorso`.`PrenotazioneId`)))
		        join `RT_AppPrenotazioneStato` ON ((`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)))
		    where
		        ((`RT_Prenotazione`.`Cancella` = 0)
		            and (`RT_PrenotazionePercorso`.`Cancella` = 0)
		            and (`RT_PrenotazionePercorso`.`Stato` = 1)
		            and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)) and
				`RT_PrenotazionePercorso`.CorsaId=$CorsaId and `RT_PrenotazionePercorso`.CorsaDataPartenza='$DataCorsa'
		    group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`";
		
		$row = $db->query_first($sql);
		if (!empty($row['TotalePaxPrenotati']))
		{
		    $PostiPrenotati=$row['TotalePaxPrenotati'];
		} else {
		    $PostiPrenotati=0;
		}
		$PostiPrenotati2=$PostiPrenotati+1;
		// seleziono le promozioni attive per il numero di pax che si vuole prenotare
		$sql = "SELECT
		RT_Scontistica.DaPax,
		RT_Scontistica.APax,
		RT_Scontistica.AttivaDal,
		RT_Scontistica.AttivaAl,
		RT_ScontisticaBiglietto.BigliettoId,
		RT_ScontisticaBiglietto.Prezzo,
		RT_ScontisticaCorsa.Dal,
		RT_ScontisticaCorsa.Al,
		RT_ScontisticaCorsaDettaglio.CorsaId,
		RT_Scontistica.ListinoId
		FROM
		RT_Scontistica
		INNER JOIN RT_ScontisticaBiglietto ON RT_Scontistica.ListinoId = RT_ScontisticaBiglietto.ListinoId
		INNER JOIN RT_ScontisticaCorsa ON RT_ScontisticaBiglietto.ListinoId = RT_ScontisticaCorsa.ListinoId
		INNER JOIN RT_ScontisticaCorsaDettaglio ON RT_ScontisticaCorsa.ScontisticaCorsaId = RT_ScontisticaCorsaDettaglio.ScontisticaCorsaId
		WHERE
		RT_ScontisticaBiglietto.BigliettoId = $TipoBigliettoId AND
		RT_Scontistica.DaPax <= $PostiPrenotati2 AND
		RT_Scontistica.APax >= $PostiPrenotati2 AND
		CorsaId=$CorsaId and
		RT_ScontisticaCorsa.Dal <='$DataCorsa' AND
		RT_ScontisticaCorsa.Al >= '$DataCorsa' AND
		RT_Scontistica.Stato = 1 AND
		RT_Scontistica.Cancella = 0 AND
		RT_ScontisticaBiglietto.Stato = 1 AND
		RT_ScontisticaBiglietto.Cancella = 0 AND
		RT_Scontistica.AttivaDal <= Now() AND
		RT_Scontistica.AttivaAl >= Now() AND
		RT_ScontisticaCorsa.Stato = 1
		order by ListinoPeso,DaPax,APax";
		
		$row = $db->query_first ( $sql );
		$Prezzo = 0;
		if (! empty ( $row ['Prezzo'] )) {
			$Prezzo = $row ['Prezzo'];
			$listinoId = $row ['ListinoId'];
		}
		
		return $Prezzo;
	}
	
	public function GetScontoPromozioneAttivaListinoId($CorsaId, $DataCorsa, $Pax, $TipoBigliettoId, &$listinoId = null) {
	
		$db = $this->conn;
	
		$PostiPrenotati = 1;
		$sql = "select TotalePaxPrenotati from RT_ViewSingolaCorsaPostiRealmentePrenotati where CorsaId=$CorsaId and CorsaDataPartenza='$DataCorsa'";
		$row = $db->query_first ( $sql );
		if (! empty ( $row ['TotalePaxPrenotati'] )) {
			$PostiPrenotati = $row ['TotalePaxPrenotati'];
		}
	
		// seleziono le promozioni attive per il numero di pax che si vuole prenotare
		$sql = "SELECT
		RT_Scontistica.DaPax,
		RT_Scontistica.APax,
		RT_Scontistica.AttivaDal,
		RT_Scontistica.AttivaAl,
		RT_ScontisticaBiglietto.BigliettoId,
		RT_ScontisticaBiglietto.Prezzo,
		RT_ScontisticaCorsa.Dal,
		RT_ScontisticaCorsa.Al,
		RT_ScontisticaCorsaDettaglio.CorsaId,
		RT_Scontistica.ListinoId
		FROM
		RT_Scontistica
		INNER JOIN RT_ScontisticaBiglietto ON RT_Scontistica.ListinoId = RT_ScontisticaBiglietto.ListinoId
		INNER JOIN RT_ScontisticaCorsa ON RT_ScontisticaBiglietto.ListinoId = RT_ScontisticaCorsa.ListinoId
		INNER JOIN RT_ScontisticaCorsaDettaglio ON RT_ScontisticaCorsa.ScontisticaCorsaId = RT_ScontisticaCorsaDettaglio.ScontisticaCorsaId
		WHERE
		RT_ScontisticaBiglietto.BigliettoId = $TipoBigliettoId AND
		RT_Scontistica.DaPax <= $PostiPrenotati AND
		RT_Scontistica.APax >= $PostiPrenotati AND
		CorsaId=$CorsaId and
		RT_ScontisticaCorsa.Dal <='$DataCorsa' AND
		RT_ScontisticaCorsa.Al >= '$DataCorsa' AND
		RT_Scontistica.Stato = 1 AND
		RT_Scontistica.Cancella = 0 AND
		RT_ScontisticaBiglietto.Stato = 1 AND
		RT_ScontisticaBiglietto.Cancella = 0 AND
		RT_Scontistica.AttivaDal <= Now() AND
		RT_Scontistica.AttivaAl >= Now() AND
		RT_ScontisticaCorsa.Stato = 1
		order by RT_Scontistica.ListinoId desc";
	
		$row = $db->query_first ( $sql );
		$listinoId = 0;
		if (! empty ( $row ['ListinoId'] )) {
			$listinoId = $row ['ListinoId'];
		}
	
		return $listinoId;
	}
	
	public function CreatePercorsoBreve($ComuneAndataId,$ComuneRitornoId,$db,$TrattePercorse,$trattaPartenza,$trattaArrivo,$CorsaAndata,$lineaId)
	{
		global $user;
	
		if($trattaPartenza)
		{
			$kmTot=0;
			foreach ($TrattePercorse as $chiave => $valore)
			{
				 
				$Km=$valore;
				$kmTot+=$Km;
			}
			 
			$data=null;
			$data['ComunePickupId']=$ComuneAndataId;
			$data['ComuneDropOffId']=$ComuneRitornoId;
			$data['TrattaPickupId']=$trattaPartenza;
			$data['TrattaDropOffId']=$trattaArrivo;
			$data['KmPercorsi']=$kmTot;
			$data['CorsaId']=$CorsaAndata;
			//  print_r($data);
			$lastid=$db->insert("RT_PercorsoBreveWeb",$data);
			foreach ($TrattePercorse as $chiave => $valore)
			{
				$data=null;
				$data['PercorsoBreveId']=$lastid;
				$data['TrattaId']=$chiave;
				$data['Km']=$valore;
	
				$db->insert("RT_PercorsoBreveWebTratte",$data);
	
			}
		}
		else
		{
			$data=null;
			$data['ComunePickupId']=$ComuneAndataId;
			$data['ComuneDropOffId']=$ComuneRitornoId;
			$data['KmPercorsi']=0;
			$data['CorsaId']=$CorsaAndata;
			$lastid=$db->insert("RT_PercorsoBreveWeb",$data);
		}
		 
		 
	
	
		return true;
		 
		 
	}

}
?>
