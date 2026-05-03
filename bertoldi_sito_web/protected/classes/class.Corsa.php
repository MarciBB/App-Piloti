<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author a.esposito
 */
class Corsa {
    
  
    public $Id;
    public $conn;
    public $DatiGenerali;



function __construct($Id=null) {
    $this->Id = $Id;
}

public function inizializzaDatiGenerali()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_Corsa WHERE CorsaId=$Id and OdcIdRef=$user->OdcId";      
    //  echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

function getPostiPrenotati($CorsaId,$DataPartenza)
{
    global $user;
     $db=$this->conn;
     $sql = "SELECT TotalePaxPrenotati From RT_ViewSingolaCorsaPostiPrenotati WHERE CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza'  and OdcIdRef=$user->OdcId";      
 
     
     $row = $db->query_first($sql);
 $postiprenotati=0;
 if (!empty($row['TotalePaxPrenotati']))
 $postiprenotati=$row['TotalePaxPrenotati'];
    
    
    
    return $postiprenotati;
}   
function getAllByLineaId($LineaId)

{
       global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_ElencoCorsa WHERE LineaId=$LineaId and OdcIdRef=$user->OdcId";      
   //  echo($sql);
        $row = $db->fetch_array($sql);
        return $row;
    
    
}

function getAllPickup()
{
     global $user;
      $db=$this->conn;
      $Id=$this->Id;
      $sql = "SELECT * From RT_FoglioCaricoPickupPerCorsa where CorsaId=$Id order by Comune asc";      
      $row = $db->fetch_array($sql);
      return $row;
    
    
}

function getAllDropOff()
{
        global $user;
         $db=$this->conn;
         $Id=$this->Id;
         $sql = "SELECT * From RT_FoglioCaricoDropOffPerCorsa where CorsaId=$Id order by Comune asc";      
   
        $row = $db->fetch_array($sql);
        return $row;
    
    
}
function getAllFermatePartenza()

{
       global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_DistinctPartenze where OdcIdRef=$user->OdcId";      
   //  echo($sql);
        $row = $db->fetch_array($sql);
        return $row;
    
    
}
    
   
    
function getAllFermateArrivo() {
   global $user;
	$db=$this->conn;
	$Id=$this->Id;
	$sql = "SELECT * From RT_DistinctArrivi where OdcIdRef=$user->OdcId";      
	$row = $db->fetch_array($sql);
	return $row;    
}


function getCorseValideBO($data, $sOrder, $sWhere)
{
	global $user;
	$db = $this->conn;
	$sql1 = "";
	if ($sWhere != "") {
		$sql1 = "and $sWhere";
	}
	$sql = "
		SELECT
			RT_Corsa.CorsaId AS CorsaId,
			RT_AppCalendario.AppCalendarioData AS AppCalendarioData,
			DATE_FORMAT(RT_AppCalendario.AppCalendarioData, _utf8 '%d/%m/%Y') AS DataPartenzaFormattata,
			RT_Corsa.CorsaNome AS CorsaNome,
			RT_Corsa.LineaId AS LineaId,
			RT_Linea.LineaNome AS LineaNome,
			RT_Corsa.OrarioPartenza AS OrarioPartenza,
			RT_Corsa.OdcIdRef AS OdcIdRef,
			RT_Corsa.GestoreIdRef AS GestoreIdRef
		FROM
			RT_Corsa
			JOIN RT_CorsaSettimana ON RT_Corsa.CorsaId = RT_CorsaSettimana.CorsaId
			JOIN RT_AppSettimana ON RT_CorsaSettimana.SettimanaId = RT_AppSettimana.AppSettimanaId
			JOIN RT_AppCalendario ON RT_AppSettimana.AppSettimanaGiorno = RT_AppCalendario.GiornoSettimana
			JOIN RT_Linea ON RT_Corsa.LineaId = RT_Linea.LineaId
			JOIN RT_TipologiaBus ON RT_Corsa.TipologiaBusDefaultId = RT_TipologiaBus.TipologiaBusId
			LEFT JOIN RT_CorsaBloccoWeb ON (
				RT_Corsa.CorsaId = RT_CorsaBloccoWeb.CorsaId
				AND RT_AppCalendario.AppCalendarioData = RT_CorsaBloccoWeb.DataPartenza
			)
			LEFT JOIN RT_CorsaBlocco ON (
				RT_Corsa.CorsaId = RT_CorsaBlocco.CorsaId
				AND RT_AppCalendario.AppCalendarioData = RT_CorsaBlocco.DataPartenza
			)
		WHERE
			RT_Corsa.Cancella = 0
			AND RT_Corsa.Stato = 1
			AND RT_AppCalendario.AppCalendarioData >= RT_Corsa.AttivaDal
			AND RT_AppCalendario.AppCalendarioData <= RT_Corsa.AttivaAl
			AND (
				(RT_AppCalendario.Feriale = RT_Corsa.IncludiFeriale AND RT_Corsa.IncludiFeriale = 1)
				OR (RT_AppCalendario.Prefestivo = RT_Corsa.IncludiPrefestivo AND RT_Corsa.IncludiPrefestivo = 1)
				OR (RT_AppCalendario.Festivo = RT_Corsa.IncludiFestivo AND RT_Corsa.IncludiFestivo = 1)
			)
			AND RT_AppCalendario.AppCalendarioData = '$data'
			$sql1
		GROUP BY
			RT_Corsa.CorsaId,
			RT_AppCalendario.AppCalendarioData
		ORDER BY
			RT_AppCalendario.AppCalendarioData,
			RT_Linea.PercorsoId,
			RT_Linea.LineaNome,	
		";

		if(isset($sOrder) && $sOrder != "") {
			$sql .= " $sOrder ,";
		}
		$sql .= " RT_Corsa.CorsaPeso ASC,
				RT_Corsa.CorsaNome";
		
		$row = $db->fetch_array($sql);
		return $row;
	}
    
}
?>
