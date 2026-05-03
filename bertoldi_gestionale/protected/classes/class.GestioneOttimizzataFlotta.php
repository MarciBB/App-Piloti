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
class GestioneottimizzataFlotta  {

	public $id;
	public $DatiGenerali;
	public $conn;

	function __construct($id, $db) {
	    $this->Id = $id;
	    $this->conn = $db;
	}
	
	
	public function inizializzaDatiGenerali()
	{
	        global $user;
	        $db=$this->conn;
	        $Id=$this->Id;
	        $sql = "SELECT * From RT_GestioneOttimizzataFlotta WHERE GestioneOttimizzataFlottaId=$this->Id";
	        //echo($sql);
	        $row = $db->query_first($sql);
	        
	        if (!empty($row['GestioneOttimizzataFlottaId']))
	        	$this->DatiGenerali=$row;
	        else
	        {
	            print("errore");
	            exit();
	        }
	}


	public function getNumBus($lineaId, $corsaId, $corsaDataPartenza)
	{
	        $sql = "SELECT COUNT(*) as tot 
	        		FROM RT_GestioneOttimizzataFlotta
					WHERE 
	        		LineaId=$lineaId 
	        		AND CorsaId=$corsaId 
	        		AND CorsaDataPartenza='$corsaDataPartenza';";
	        $row = $this->conn->query_first($sql);
	        return $row['tot'];
	}
	
	public function getNome($idBus){
		$sql = "SELECT Nome FROM RT_GestioneOttimizzataFlotta
				WHERE GestioneOttimizzataFlottaId=$idBus";
		$row = $this->conn->query_first($sql);
		return $row['Nome'];
	}
	
	public function getMaxNome($lineaId, $corsaId, $dataPartenza){
		$sql = "SELECT Max(Nome) as max
		FROM RT_GestioneOttimizzataFlotta 
		WHERE LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$dataPartenza'";
		$row = $this->conn->query_first($sql);
		return $row['max'];
	}
}
?>