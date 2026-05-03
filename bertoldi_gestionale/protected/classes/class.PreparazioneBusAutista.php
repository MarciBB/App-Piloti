<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author Luca Casaburi
 */
class PreparazioneBusAutista  {

	public $Id;
	public $DatiGenerali;
	public $conn;

	function __construct($db) {
	    $this->conn = $db;
	}
	
	
	public function inizializzaDatiGenerali($id)
	{
	        global $user;
	        $this->Id = $Id;
	        $db=$this->conn;
	        $Id=$this->Id;
	        $sql = "SELECT * From RT_PrenotazioneBusAutisti WHERE PrenotazioneBusAutistiId=$Id";
	        //echo($sql);
	        $row = $db->query_first($sql);
	        
	        if (!empty($row['Comune']))
	        	$this->DatiGenerali=$row;
	        else
	        {
	            print("errore");
	            exit();
	        }
	}
	
	public function getAutisti($dataPartenza, $corsaId, $lineaId, $busId)
	{
		$db=$this->conn;
		$sql = "SELECT Autista1, Autista2
				FROM RT_PreparazioneBusAutisti
				WHERE DataPartenza='$dataPartenza'
				AND Corsaid=$corsaId
				AND LineaId=$lineaId
				AND BusId=$busId";
		$row = $db->query_first($sql);
		return $row;
	}


	public function exist($dataPartenza, $corsaId, $lineaId, $busId)
	{
	        $db=$this->conn;
	        $sql = "SELECT PreparazioneBusAutistiId 
	        		FROM RT_PreparazioneBusAutisti 
	        		WHERE DataPartenza='$dataPartenza'
	        		AND Corsaid=$corsaId
	        		AND LineaId=$lineaId
	        		AND BusId=$busId";
	        $row = $db->query_first($sql);
	        return $row['PreparazioneBusAutistiId'];
	}
}
?>
