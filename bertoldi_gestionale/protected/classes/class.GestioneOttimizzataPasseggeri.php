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
class GestioneOttimizzataPasseggeri  {

	public $DatiGenerali;
	public $conn;

	function __construct($db) {
	    $this->conn = $db;
	}

	public function getNumPasseggeri($lineaId, $corsaId, $corsaDataPartenza, $bus)
	{
	        $sql = "SELECT Comune, COUNT(ComuneArrivo) as totale
	        		FROM RT_GestioneOttimizzataPasseggeri
					WHERE LineaId=$lineaId 
	        		AND CorsaId=$corsaId 
	        		AND CorsaDataPartenza='$corsaDataPartenza' 
	        		AND Bus=$bus 
	        		GROUP BY (Comune)";
	        $rows = $this->conn->fetch_array($sql);
	        return $rows;
	}
	
	public function getPasseggeri($lineaId, $corsaId, $corsaDataPartenza, $bus, $comuneId)
	{
		$sql = "SELECT PrenotazioneNumero 
				FROM RT_GestioneOttimizzataPasseggeri
				WHERE LineaId=$lineaId 
				AND CorsaId=$corsaId 
				AND CorsaDataPartenza='$corsaDataPartenza' 
				AND Bus=$bus 
				AND Comune=$comuneId";
		$rows = $this->conn->fetch_array($sql);
		return $rows;
	}
	
	public function deletePasseggero($lineaId, $corsaId, $corsaDataPartenza, $prenotazioneNumero)
	{
		$sql = "DELETE
				FROM RT_GestioneOttimizzataPasseggeri
				WHERE LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND PrenotazioneNumero=$prenotazioneNumero";
		$this->conn->query($sql);
	}
	
	public function isExist($lineaId, $corsaId, $corsaDataPartenza, $prenotazioneNumero, $bus, $comuneId)
	{
		$sql = "SELECT PrenotazioneNumero
				FROM RT_GestioneOttimizzataPasseggeri
				WHERE LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND PrenotazioneNumero=$prenotazioneNumero
				AND Bus=$bus 
				AND Comune=$comuneId";
		$row = $this->conn->query_first($sql);
		if(sizeof($row)>0){
			return true;
		}else{
			return false;
		}
	}
	
	public function getPasseggeriBus($lineaId, $corsaId, $corsaDataPartenza, $bus)
	{
		$sql = "SELECT PrenotazioneNumero 
				FROM RT_GestioneOttimizzataPasseggeri 
				WHERE LineaId=$lineaId 
				AND CorsaId=$corsaId 
				AND CorsaDataPartenza='$corsaDataPartenza' 
				AND Bus=$bus 
				Group By PrenotazioneNumero;";
		$rows = $this->conn->fetch_array($sql);
		return $rows;
	}
	
	public function getNumTotalePasseggeriBus($lineaId, $corsaId, $corsaDataPartenza, $bus){
		$sql = "SELECT Count(*) as Tot, Comune, Ordine 
				FROM RT_GestioneOttimizzataPasseggeri
				WHERE LineaId=$lineaId 
				AND CorsaId=$corsaId 
				AND CorsaDataPartenza='$corsaDataPartenza' 
				AND Bus=$bus	
				GROUP BY Comune ORDER BY Ordine";
		
		$rows = $this->conn->fetch_array($sql);
		return $rows;
	}
	
	public function getNumPasseggeriBusComune($lineaId, $corsaId, $corsaDataPartenza, $bus, $comune){
		$sql = "SELECT Count(*) as Tot
		FROM RT_GestioneOttimizzataPasseggeri
		WHERE LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'
		AND Bus=$bus
		AND Comune=$comune";
		
		$rows = $this->conn->fetch_array($sql);
		return $rows;
	}
	
	public function getComuniBus($lineaId, $corsaId, $corsaDataPartenza, $bus){
		$sql = "SELECT Comune, Ordine 
				FROM RT_GestioneOttimizzataNodo
				WHERE LineaId=$lineaId 
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza' 
				AND (BusPartenza=$bus OR BusArrivo=$bus)
				GROUP BY RT_GestioneOttimizzataNodo.Comune ORDER BY RT_GestioneOttimizzataNodo.Ordine";
		
		$rows = $this->conn->fetch_array($sql);
		return $rows;
	}
	
	
	public function getPercorsoPasseggeri($lineaId, $corsaId, $corsaDataPartenza, $numeroPrenotazione){
		$sql = "SELECT RT_GestioneOttimizzataPasseggeri.Comune, RT_GestioneOttimizzataFlotta.Nome 
				FROM RT_GestioneOttimizzataPasseggeri 
				LEFT JOIN RT_GestioneOttimizzataFlotta ON (RT_GestioneOttimizzataFlotta.GestioneOttimizzataFlottaId=RT_GestioneOttimizzataPasseggeri.Bus)
				WHERE 
				RT_GestioneOttimizzataPasseggeri.LineaId=$lineaId 
				AND RT_GestioneOttimizzataPasseggeri.CorsaId=$corsaId
				AND RT_GestioneOttimizzataPasseggeri.CorsaDataPartenza='$corsaDataPartenza' 
				AND RT_GestioneOttimizzataPasseggeri.PrenotazioneNumero=$numeroPrenotazione 
				ORDER BY RT_GestioneOttimizzataPasseggeri.Bus ASC, RT_GestioneOttimizzataPasseggeri.Ordine ASC";
		$rows = $this->conn->fetch_array($sql);
		return $rows;
	}
}
?>