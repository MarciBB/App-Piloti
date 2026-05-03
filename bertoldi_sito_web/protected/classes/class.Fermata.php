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
class Fermata {
    
  
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
        $sql = "SELECT * From RT_ElencoFermata WHERE FermataId=$Id and OdcIdRef=$user->OdcId";      
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

public function getDistinctFermataPickup()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_ElencoFermata WHERE FermataId=$Id and OdcIdRef=$user->OdcId";      
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


public function getAllByCorsaComune($CorsaId,$ComuneId,$tipo)
{ global $user;
        $db=$this->conn;
        
       if ($tipo=="P")
        $sql = "SELECT distinct FermataId,FermataOrario From RT_ElencoFermataOrarioPK WHERE IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$ComuneId  and OdcIdRef=$user->OdcId order by Orario";      
        else
      $sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop From RT_ElencoFermataOrarioDO WHERE IsDropOff=1 and  CorsaId=$CorsaId and ComuneId=$ComuneId  and OdcIdRef=$user->OdcId order by Orario";      
      

 //echo($sql);
         return ($db->fetch_array($sql));
        
    
}

public function isPickup($lineaId, $comuneId){
	$sql = "SELECT Count(*) as tot
	FROM RT_Fermata
	LEFT JOIN RT_Tratta ON (RT_Fermata.TrattaId=RT_Tratta.TrattaId)
	WHERE RT_Tratta.LineaId=$lineaId
	AND RT_Fermata.ComuneId=$comuneId
	AND RT_Fermata.IsPickup=1;";

	$rows = $this->conn->query_first($sql);
	if($rows['tot']>0){
		return true;
	}else{
		return false;
	}
}

public function isDropOff($lineaId, $comuneId){
	$sql = "SELECT Count(*) as tot
			FROM RT_Fermata
			LEFT JOIN RT_Tratta ON (RT_Fermata.TrattaId=RT_Tratta.TrattaId)
			WHERE RT_Tratta.LineaId=$lineaId
			AND RT_Fermata.ComuneId=$comuneId
			AND RT_Fermata.IsDropOff=1;";

	$rows = $this->conn->query_first($sql);
	if($rows['tot']>0){
		return true;
	}else{
		return false;
	}
}

	public function isInterscambioLinea($lineaId, $comuneId){
		$sql = "SELECT Count(*) as tot
		FROM RT_Fermata
		LEFT JOIN RT_Tratta ON (RT_Fermata.TrattaId=RT_Tratta.TrattaId)
		WHERE RT_Tratta.LineaId=$lineaId
		AND RT_Fermata.ComuneId=$comuneId
		AND RT_Fermata.IsInterscambio=1;";
	
		$rows = $this->conn->query_first($sql);
		if($rows['tot']>0){
			return true;
		}else{
			return false;
		}
	}
	
	public function getTratta($comune1, $comune2){
		$sql = "SELECT f1.TrattaId FROM RT_Fermata f1
				LEFT JOIN RT_Tratta t1 ON (f1.TrattaId = t1.TrattaId)
				WHERE f1.ComuneId=$comune2
				AND f1.TrattaId In
				(SELECT f.TrattaId FROM RT_Fermata f
				LEFT JOIN RT_Tratta t ON (f.TrattaId = t.TrattaId)
				where ComuneId=$comune1)";
		$rows = $this->conn->query_first($sql);
		if(count($rows)>0){
			return $rows['TrattaId'];
		}else{
			return false;
		}
	}
   
    
    
}
?>
