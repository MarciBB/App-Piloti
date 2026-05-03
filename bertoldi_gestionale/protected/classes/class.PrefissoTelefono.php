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
class PrefissoTelefono {
    
  
   
    public $conn;
    
	function __construct($db=null) {
	    $this->conn = $db;
	}
	
	
	public function getAllForSelect()
	{ 
		global $user;
		$db=$this->conn;
		$sql = "SELECT Prefisso, Nazione From PrefissoTelefono Order By Nazione";      
		$rows = $db->fetch_array($sql);
		$prefissi = array();
		foreach ($rows as $row){
			$temp['Prefisso'] = $row['Prefisso'];
			$temp['Descrizione'] = $row['Nazione']." +".$row['Prefisso'];
			$prefissi[] = $temp;
 		}
 		return $prefissi;
	}
	    
}
?>
