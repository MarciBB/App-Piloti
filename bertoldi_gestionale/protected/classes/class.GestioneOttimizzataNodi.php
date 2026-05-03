<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 
 *
 * @author Luca Casaburi
 */
class GestioneOttimizzataNodi  {

	public $conn;

	function __construct($db) {
	    $this->conn = $db;
	}

	public function existPartenza($lineaId, $corsaId, $corsaDataPartenza, $busId, $comune){
		$sql = "SELECT COUNT(*) as tot 
		FROM RT_GestioneOttimizzataNodo
				WHERE LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND BusPartenza=$busId
				AND Comune=$comune";
	    $row = $this->conn->query_first($sql);
	    if($row['tot']>0)
	    	return true;
	    else
	    	return false;
	}
	
	public function existArrivo($lineaId, $corsaId, $corsaDataPartenza, $busId, $comune){
		$sql = "SELECT COUNT(*) as tot 
		FROM RT_GestioneOttimizzataNodo
		WHERE LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'
		AND BusArrivo=$busId
		AND Comune=$comune";
		$row = $this->conn->query_first($sql);
		if($row['tot']>0)
			return true;
		else
			return false;
	}
	
	public function getBusArrivoComune($lineaId, $corsaId, $corsaDataPartenza, $comune){
		$sql = "SELECT BusPartenza
				FROM RT_GestioneOttimizzataNodo
				WHERE LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND Comune=$comune";
		$row = $this->conn->fetch_array($sql);
		return $row;
	}
	
	public function getComuneInterscambio($lineaId, $corsaId, $corsaDataPartenza, $busArrivo, $busPartenza){
		$sql = "SELECT COUNT(*) as Tot, Comune  
				FROM RT_GestioneOttimizzataNodo
				WHERE LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND (BusArrivo = $busArrivo OR 
				BusPartenza = $busPartenza)
				GROUP BY Comune;";
		$rows = $this->conn->fetch_array($sql);
		$comune = null;
		foreach ($rows as $row){
			if($row['Tot']>1){
				$comune = $row['Comune'];
			}
		}
		return $comune;
	}
	
	public function getBusIncontro($lineaId, $corsaId, $corsaDataPartenza, $bus){ 
		$sql = "SELECT BusPartenza, Comune FROM RT_GestioneOttimizzataNodo 
				WHERE Comune IN (
					SELECT Comune FROM RT_GestioneOttimizzataNodo 
					WHERE BusArrivo=$bus 
					AND LineaId=$lineaId 
					AND CorsaId=$corsaId 
					AND CorsaDataPartenza='$corsaDataPartenza')
				AND BusPartenza IS NOT NULL AND BusPartenza!=$bus
				AND LineaId=$lineaId 
				AND CorsaId=$corsaId 
				AND CorsaDataPartenza='$corsaDataPartenza'
		Group by (BusPartenza)";
		$row = $this->conn->fetch_array($sql);
		return $row;
	}
	
	
}
?>