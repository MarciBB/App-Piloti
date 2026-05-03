<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author m.casaburi
 */
class CanalePagamento {


	public $Id;
	public $conn;
	public $DatiGenerali;

	function __construct($Id = null) {
		$this->Id = $Id;
	}

	public function inizializzaDatiGenerali() {
		global $user;
		$db = $this->conn;
		$Id = $this->Id;
		
		$sql = "SELECT * FROM RT_CanalePagamento WHERE CanalePagamentoId=$Id";
		
		$row = $db->query_first($sql);
		
		if (!empty($row['CanaleId']))
			$this->DatiGenerali = $row;
		else
		{
			print("errore");
			exit();
		}
	}
	
	public function getAllForSelect() {
		global $user;
		$db = $this->conn;
		$sql = "SELECT * FROM RT_CanalePagamento";
		return $db->fetch_array($sql);
	}
}
?>