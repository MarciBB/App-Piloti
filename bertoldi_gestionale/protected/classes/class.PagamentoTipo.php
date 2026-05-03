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
class PagamentoTipo {


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
		
		$sql = "SELECT * From RT_PagamentoTipo WHERE PagamentoTipoId=$Id and OdcIdRef=$user->OdcId";
		
		$row = $db->query_first($sql);

		if (!empty($row['OdcIdRef']))
			$this->DatiGenerali = $row;
		else
		{
			print("errore");
			exit();
		}
	}
	
	public function getAll() {
		global $user;
		$db=$this->conn;
		
		$sql = "SELECT * From RT_PagamentoTipo WHERE OdcIdRef=$user->OdcId AND Stato = 1 AND Cancella = 0";
		
		$rows = $db->fetch_array($sql);
		return $rows;
	}
	
	public function getAllForSelect($tipo = 0) {
		global $user;
		$db = $this->conn;
	
		$user->inizializza($user->OperatoreId);
		
		$sql = "SELECT PagamentoTipoId, PagamentoTipo From RT_PagamentoTipo WHERE 
				(OdcIdRef=$user->OdcId 
				AND Stato = 1 
				AND Cancella = 0 
				AND (GestorePrimario = $user->GestorePrimario or GestorePrimario = 2)) ";
		if($tipo > 0){
			$sql.= "OR PagamentoTipoId = $tipo";
		}
		
		$rows = $db->fetch_array($sql);
		return $rows;
	}
	
	public function getTipoFromSelect($tipo = 0) {
		global $user;
		$db = $this->conn;
	
		$user->inizializza($user->OperatoreId);
	
		$sql = "SELECT PagamentoTipoId, PagamentoTipo From RT_PagamentoTipo WHERE PagamentoTipoId = $tipo";
		
		$rows = $db->fetch_array($sql);
		return $rows;
	}
	
	public function getAllForContabilita() {
		global $user;
		$db = $this->conn;
	
		$user->inizializza($user->OperatoreId);
		if($user->GestorePrimario == 1){
			$sql = "SELECT PagamentoTipoId, PagamentoTipo From RT_PagamentoTipo WHERE Stato = 1 AND Cancella = 0 ";
		} else {
			$sql = "SELECT PagamentoTipoId, PagamentoTipo From RT_PagamentoTipo WHERE OdcIdRef=$user->OdcId AND Stato = 1 AND Cancella = 0 AND (GestorePrimario = $user->GestorePrimario or GestorePrimario = 2) ";
		}	
		$rows = $db->fetch_array($sql);
		return $rows;
	}
	
	public function getAllRimborsoForSelect() {
		global $user;
		$db = $this->conn;
	
		$user->inizializza($user->OperatoreId);
	
		$sql = "SELECT PagamentoTipoId, EtichettaRimborso From RT_PagamentoTipo WHERE 
			OdcIdRef=$user->OdcId 
			AND Stato = 1 
			AND Cancella = 0 
			AND (GestorePrimario = $user->GestorePrimario or GestorePrimario = 2) 
			AND VisualizzaInRimborso = true";
	
		$rows = $db->fetch_array($sql);
		return $rows;
	}
	
	public function getTipoRimborsoForSelect($tipo) {
		global $user;
		$db = $this->conn;
	
		$user->inizializza($user->OperatoreId);
	
		$sql = "SELECT PagamentoTipoId, EtichettaRimborso From RT_PagamentoTipo WHERE
		 	 PagamentoTipoId = $tipo";
	
		$rows = $db->fetch_array($sql);
		return $rows;
	}
}
?>
