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
		$sql = "SELECT 
		rt_fermata.FermataId AS FermataId,
		rt_fermata.TrattaId AS TrattaId,
		rt_fermata.ComuneId AS ComuneId,
		rt_fermata.FermataNome AS FermataNome,
		rt_fermata.IsPickup AS IsPickup,
		rt_fermata.IsDropOff AS IsDropOff,
		rt_fermata.IsInterscambio AS IsInterscambio,
		rt_fermata.Prezzo AS Prezzo,
		rt_fermata.DataIns AS DataIns,
		rt_fermata.OpeIns AS OpeIns,
		rt_fermata.SedeIns AS SedeIns,
		rt_fermata.IpIns AS IpIns,
		rt_fermata.DataAgg AS DataAgg,
		rt_fermata.OpeAgg AS OpeAgg,
		rt_fermata.SedeAgg AS SedeAgg,
		rt_fermata.IpAgg AS IpAgg,
		rt_fermata.Stato AS Stato,
		rt_fermata.Cancella AS Cancella,
		rt_fermata.UpdateCount AS UpdateCount,
		rt_fermata.OdcIdRef AS OdcIdRef,
		rt_fermata.GestoreIdRef AS GestoreIdRef,
		rt_linea.LineaNome AS LineaNome,
		rt_tratta.TrattaNome AS TrattaNome,
		rt_percorso.PercorsoNome AS PercorsoNome,
		comune.Comune AS Comune,
		rt_fermata.FermataPeso AS FermataPeso,
		rt_tratta.LineaId AS LineaId,rt_tratta.TrattaPeso AS TrattaPeso,
		rt_fermata.Latitudine AS Latitudine,
		rt_fermata.Longitudine AS Longitudine,
		rt_fermata.IsBlackList AS IsBlackList,
		rt_corsa.CorsaId AS CorsaId,rt_tratta.MezzoId AS MezzoId,
		rt_fermata.WebSelling AS WebSelling,
		rt_fermata.IsDaConfermare AS IsDaConfermare,
		rt_fermata.KmInizioTratta AS KmInizioTratta,
		rt_tratta.NodoPeso AS NodoPeso 
		FROM RT_Fermata AS rt_fermata 
		JOIN RT_Tratta AS rt_tratta ON (rt_fermata.TrattaId = rt_tratta.TrattaId) 
		JOIN RT_Linea AS rt_linea ON (rt_tratta.LineaId = rt_linea.LineaId) 
		JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId) 
		JOIN Comune AS comune ON (rt_fermata.ComuneId = comune.ComuneId) 
		JOIN RT_Corsa AS rt_corsa ON (rt_linea.LineaId = rt_corsa.LineaId) 
		WHERE rt_fermata.Cancella = 0 
		AND rt_tratta.Cancella = 0 
		AND rt_linea.Cancella = 0 
		AND rt_percorso.Cancella = 0
		AND rt_fermata.FermataId = $Id
		AND rt_fermata.OdcIdRef = $user->OdcId";      
 	      
		$row = $db->query_first($sql);
	        
		if (!empty($row['OdcIdRef']))
			$this->DatiGenerali=$row;
		else{
			print("errore");
			exit();
		}
	}
	
        public function getComuneIdByFermataId($fermataId)
        {
                global $user;
				$db=$this->conn;
                 $sql = "SELECT ComuneId From RT_Fermata WHERE FermataId=$fermataId and OdcIdRef=$user->OdcId"; 
                 $row = $db->query_first($sql);
                 if (!empty($row['ComuneId']))
                     return $row['ComuneId'];
                 else
                     return null;
        }
        
	public function getDistinctFermataPickup()
	{
		global $user;
		$db=$this->conn;
		$Id=$this->Id;
		$sql = "SELECT 
		rt_fermata.FermataId AS FermataId,
		rt_fermata.TrattaId AS TrattaId,
		rt_fermata.ComuneId AS ComuneId,
		rt_fermata.FermataNome AS FermataNome,
		rt_fermata.IsPickup AS IsPickup,
		rt_fermata.IsDropOff AS IsDropOff,
		rt_fermata.IsInterscambio AS IsInterscambio,
		rt_fermata.Prezzo AS Prezzo,
		rt_fermata.DataIns AS DataIns,
		rt_fermata.OpeIns AS OpeIns,
		rt_fermata.SedeIns AS SedeIns,
		rt_fermata.IpIns AS IpIns,
		rt_fermata.DataAgg AS DataAgg,
		rt_fermata.OpeAgg AS OpeAgg,
		rt_fermata.SedeAgg AS SedeAgg,
		rt_fermata.IpAgg AS IpAgg,
		rt_fermata.Stato AS Stato,
		rt_fermata.Cancella AS Cancella,
		rt_fermata.UpdateCount AS UpdateCount,
		rt_fermata.OdcIdRef AS OdcIdRef,
		rt_fermata.GestoreIdRef AS GestoreIdRef,
		rt_linea.LineaNome AS LineaNome,
		rt_tratta.TrattaNome AS TrattaNome,
		rt_percorso.PercorsoNome AS PercorsoNome,
		comune.Comune AS Comune,
		rt_fermata.FermataPeso AS FermataPeso,
		rt_tratta.LineaId AS LineaId,rt_tratta.TrattaPeso AS TrattaPeso,
		rt_fermata.Latitudine AS Latitudine,
		rt_fermata.Longitudine AS Longitudine,
		rt_fermata.IsBlackList AS IsBlackList,
		rt_corsa.CorsaId AS CorsaId,rt_tratta.MezzoId AS MezzoId,
		rt_fermata.WebSelling AS WebSelling,
		rt_fermata.IsDaConfermare AS IsDaConfermare,
		rt_fermata.KmInizioTratta AS KmInizioTratta,
		rt_tratta.NodoPeso AS NodoPeso 
		FROM RT_Fermata AS rt_fermata 
		JOIN RT_Tratta AS rt_tratta ON (rt_fermata.TrattaId = rt_tratta.TrattaId) 
		JOIN RT_Linea AS rt_linea ON (rt_tratta.LineaId = rt_linea.LineaId) 
		JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId) 
		JOIN Comune AS comune ON (rt_fermata.ComuneId = comune.ComuneId) 
		JOIN RT_Corsa AS rt_corsa ON (rt_linea.LineaId = rt_corsa.LineaId) 
		WHERE rt_fermata.Cancella = 0 
		AND rt_tratta.Cancella = 0 
		AND rt_linea.Cancella = 0 
		AND rt_percorso.Cancella = 0
		AND rt_fermata.FermataId = $Id
		AND rt_fermata.OdcIdRef = $user->OdcId";      
	    //  echo($sql);
		$row = $db->query_first($sql);
	        
		if (!empty($row['OdcIdRef']))
	        $this->DatiGenerali=$row;
		else{
			print("errore");
			exit();
		}
	}
	
	
	public function getAllByCorsaComune($CorsaId,$ComuneId,$tipo)
	{
		global $user;
		$db=$this->conn;
	        
		if ($tipo=="P")
			$sql = "SELECT distinct FermataId,FermataOrario,TrattaNome From RT_ElencoFermataOrarioPK WHERE IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$ComuneId  and OdcIdRef=$user->OdcId order by TrattaPeso desc ";      
		else
			$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome From RT_ElencoFermataOrarioDO WHERE IsDropOff=1 and  CorsaId=$CorsaId and ComuneId=$ComuneId  and OdcIdRef=$user->OdcId order by TrattaPeso asc";      
	 //echo($sql);
		return ($db->fetch_array($sql));
	        
	    
	}
	
	public function getTrattaByComune($comuneId, $lineaId){
		$sql = "SELECT
				RT_Tratta.TrattaNome, RT_Tratta.TrattaId
				FROM
				RT_Tratta
				INNER JOIN RT_Fermata ON RT_Tratta.TrattaId = RT_Fermata.TrattaId
				WHERE
				RT_Fermata.ComuneId=$comuneId AND RT_Tratta.LineaId=$lineaId";
		$rows = $this->conn->fetch_array($sql);
		return $rows;
	}

    public function isInterscambio($comuneId){
    	$sql = "SELECT DISTINCT ComuneId 
    			FROM RT_Fermata 
    			WHERE ComuneId=$comuneId AND isInterscambio=1";
    	$rows = $this->conn->query_first($sql);
    	if($rows==false){
    		return false;
    	}else{
    		return true;
    	}
    }
    
	public function getComuniInizioTratta($lineaId){
		$sql = "SELECT MIN(RT_Fermata.FermataPeso) as max, RT_Tratta.TrattaId 
				FROM RT_Fermata 
				LEFT JOIN RT_Tratta ON (RT_Fermata.TrattaId=RT_Tratta.TrattaId) 
				WHERE LineaId = $lineaId
				AND RT_Tratta.Stato = 1 AND RT_Tratta.Cancella = 0
				AND RT_Fermata.Stato = 1 AND RT_Fermata.Cancella = 0
				GROUP BY(RT_Tratta.TrattaId)";
		$rows = $this->conn->fetch_array($sql);
		$comuni = array();
		foreach ($rows as $row){
			$sql = "SELECT ComuneId 
				FROM RT_Fermata 
				WHERE
				TrattaId=".$row['TrattaId']."
				 AND FermataPeso=".$row['max'];
			$idComune = $this->conn->query_first($sql);
			if(!in_array($idComune['ComuneId'], $comuni)){
				$comuni[]=$idComune['ComuneId'];
			}
		}
		return $comuni;
	}
	
	public function isInizioTratta($lineaId, $comuneId){
		$sql = "SELECT MIN(RT_Fermata.FermataPeso) as max, RT_Tratta.TrattaId
				FROM RT_Fermata
				LEFT JOIN RT_Tratta ON (RT_Fermata.TrattaId=RT_Tratta.TrattaId)
				WHERE LineaId = $lineaId
				AND RT_Tratta.Stato = 1 AND RT_Tratta.Cancella = 0
				AND RT_Fermata.Stato = 1 AND RT_Fermata.Cancella = 0
				GROUP BY(RT_Tratta.TrattaId)";
		$rows = $this->conn->fetch_array($sql);
		$comuni = array();
		foreach ($rows as $row){
			$sql = "SELECT ComuneId
					FROM RT_Fermata
					WHERE
					TrattaId=".$row['TrattaId']."
					AND FermataPeso=".$row['max'];
			$idComune = $this->conn->query_first($sql);
			if(!in_array($idComune['ComuneId'], $comuni)){
				$comuni[]=$idComune['ComuneId'];
			}
		}
		return in_array($comuneId,$comuni);
	}
	
	public function getProssimoComune($trattaId, $comune){
		$sql = "SELECT ComuneId FROM RT_Fermata f 
				LEFT JOIN RT_Tratta t ON (f.TrattaId = t.TrattaId) 
				where t.TrattaId=$trattaId 
				order by f.FermataPeso";
		$rows = $this->conn->fetch_array($sql);
// 		echo $sql;
		foreach ($rows as $key=>$value){
			if($value['ComuneId']==$comune){
				return $rows[$key+1]['ComuneId'];
			}
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
	
	public function getTrattaComuniLinea($comune1, $comune2, $lineaid){
		$sql = "SELECT f1.TrattaId FROM RT_Fermata f1
		LEFT JOIN RT_Tratta t1 ON (f1.TrattaId = t1.TrattaId)
		WHERE f1.ComuneId=$comune2
		AND t1.LineaId = $lineaid
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
	
	
	public function getComuniFineTratta($lineaId){
		$sql = "SELECT MAX(RT_Fermata.FermataPeso) as max, RT_Tratta.TrattaId 
				FROM RT_Fermata 
				LEFT JOIN RT_Tratta ON (RT_Fermata.TrattaId=RT_Tratta.TrattaId) 
				WHERE LineaId = $lineaId
				AND RT_Tratta.Stato = 1 AND RT_Tratta.Cancella = 0
				AND RT_Fermata.Stato = 1 AND RT_Fermata.Cancella = 0
				GROUP BY(RT_Tratta.TrattaId)";
		$rows = $this->conn->fetch_array($sql);
		$comuni = array();
		foreach ($rows as $row){
			$sql = "SELECT ComuneId 
				FROM RT_Fermata 
				WHERE 
				TrattaId=".$row['TrattaId']."
				 AND FermataPeso=".$row['max'];
			$idComune = $this->conn->query_first($sql);
			if(!in_array($idComune['ComuneId'], $comuni)){
				$comuni[]=$idComune['ComuneId'];
			}
		}
		return $comuni;
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
	
	public function isInterscambioLinea($lineaId, $comuneId){
		$sql = "SELECT Count(*) as tot
				FROM RT_Fermata
				LEFT JOIN RT_Tratta ON (RT_Fermata.TrattaId=RT_Tratta.TrattaId)
				WHERE RT_Tratta.LineaId=$lineaId
				AND RT_Fermata.ComuneId=$comuneId
				AND RT_Fermata.IsInterscambio=1;";
// 		echo $sql;
		$rows = $this->conn->query_first($sql);
                
		if(isset($rows['tot']) && $rows['tot']>0){
			return true;
		}else{
			return false;
		}
	}
}
?>
