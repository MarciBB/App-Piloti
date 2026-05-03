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
class Gestore {
	public $ArrGestore;
	public $GestoreId;
	public $conn;
	public $GestoreDatiGenerali;
	function __construct($GestoreId = null) {
		$this->GestoreId = $GestoreId;
	}
	public function inizializzaDatiGenerali($GestoreId) {
		global $user;
		$db = $this->conn;
		$sql = "SELECT * From ViewListaGestori WHERE OdcIdRef=$user->OdcId and GestoreId=$this->GestoreId";
		$row = $db->query_first ( $sql );
		
		if (! empty ( $row ['OdcIdRef'] ))
			$this->GestoreDatiGenerali = $row;
		else {
			return 0;
		}
	}
	public function isPrimario($id) {
		$db = $this->conn;
		$sql = "SELECT GestorePrimario From Gestore where GestoreId = $id";
		$row = $db->fetch_array ( $sql );
		
		return $row [0] ['GestorePrimario'];
	}
	public function getGruppiGestori() {
		$db = $this->conn;
		$sql = "SELECT GestoreGruppoId,Gruppo From GestoreGruppo where Stato=1 and Cancella=0 order by Gruppo asc";
		$row = $db->fetch_array ( $sql );
		
		return ($row);
	}
	public function getGestoreFigli($idgestore) {
		$db = Database::obtain();
		$db->connect();
		$SQL = "call Gestore_proc('S',$idgestore,0,0,'')";
		$rows = $db->fetch_array($SQL);
		$out = array();
		foreach ($rows as $myrow) {
			$out[] = $myrow["GestoreId"];
		}
		return $out;
	}
	public function getGestoreAll($idgestore) {
		$db = Database::obtain();
		$db->connect();
		$SQL = "call Gestore_proc('S',$idgestore,0,0,'')";
		$rows = $db->fetch_array($SQL);
		$out = array();
		foreach ($rows as $myrow) {
			$out[] = array(
				"GestoreId" => $myrow["GestoreId"],
				"RagioneSociale" => utf8_encode($myrow["RagioneSociale"])
			);
		}
		$this->ArrGestore = $out;
	}
	
	public function getAllForSelect()
    {
        global $user;
        $db = $this->conn;

        $sql = "select GestoreId, RagioneSociale as Gestore from Gestore
				where Stato = 1 and Cancella = 0
				order by Gestore ASC";

        return $db->fetch_array($sql);
    }
}
?>
