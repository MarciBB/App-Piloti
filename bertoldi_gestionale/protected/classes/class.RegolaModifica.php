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
class RegolaModifica {
    
  
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
	    $sql = "SELECT * From RT_ModificaRegola where ModificaRegolaId=$Id";      
	    $row = $db->query_first($sql);
	    $this->DatiGenerali=$row;
	}
	
	public function getAllForSelect() { 
		global $user;
	    $db=$this->conn;
	    $sql = "SELECT ModificaRegolaId, NomeRegola From RT_ModificaRegola WHERE Stato = 1 order by NomeRegola";      
	    return ($db->fetch_array($sql));
	} 
}
?>
