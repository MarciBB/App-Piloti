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
class GestioneottimizzataModifiche  {

	public $id;
	public $DatiGenerali;
	public $conn;

	function __construct($db) {
	    $this->conn = $db;
	}


	public function getAll($lineaId, $corsaId, $dataPartenza)
	{
	        $sql = "SELECT *  
	        		FROM RT_GestioneOttimizzataModifiche
	        		WHERE LineaId=$lineaId
					AND CorsaId=$corsaId
					AND CorsaDataPartenza='$dataPartenza'";
	        $rows = $this->conn->fetch_array($sql);
	        return $rows;
	}
	
	public function getAllNuovePrenotazioni($lineaId, $corsaId, $dataPartenza)
	{
		$sql = "SELECT PrenotazioneNumero
	        	FROM RT_GestioneOttimizzataModifiche
				WHERE (Aggiungi=1 OR Aggiungi=2)
				AND LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$dataPartenza'";
		$rows = $this->conn->fetch_array($sql);
		return $rows;
	}
}
?>