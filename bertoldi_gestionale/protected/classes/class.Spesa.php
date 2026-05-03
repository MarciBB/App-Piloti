<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author braincomputing
 */
class Spesa {
    
  
    public $Id;
    public $conn;
    public $DatiGenerali;



	function __construct( $Id = null ) {
	    $this->Id = $Id;
	}

	public function inizializzaDatiGenerali()
	{
        global $user;
        $db = $this->conn;
        $Id = $this->Id;
        $sql = "SELECT * From RT_Spese WHERE Cancella=0 and SpesaId = $Id and OdcIdRef = $user->OdcId"; 
		
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef'])) {
			$this->DatiGenerali=$row;
        } else {
            print("errore");
            exit();                     
        }   
	}

	public function getCategorie() {
		global $user;
		$db = $this->conn;
		$sql = "SELECT FornitoriCategoriaId, Nome From RT_FornitoriCategorie ORDER BY Nome";
		$rows = $db->fetch_array($sql);
		$arr_fornitori = array();
		foreach ($rows as $key=>$row){
			$arr_fornitori[$key]['CategoriaId'] = $row['FornitoriCategoriaId'];
			$arr_fornitori[$key]['Categoria'] = $row['Nome'];
		}
		return $arr_fornitori;
	}
	
	public function getCategorieById($FornitoriCategoriaId) {
		global $user;
		$db = $this->conn;
		$sql = "SELECT FornitoriCategoriaId, Nome From RT_FornitoriCategorie WHERE FornitoriCategoriaId = $FornitoriCategoriaId";
		$row = $db->query_first($sql);
		
		return $row;
	}

	public function getDestinazioni() {
		global $user;
		$db = $this->conn;
		$sql = "SELECT SpeseDestinazioneId, Nome From RT_SpeseDestinazione ORDER BY Nome";
		$rows = $db->fetch_array($sql);
		$arr_fornitori = array();
		foreach ($rows as $key=>$row){
			$arr_fornitori[$key]['DestinazioneId'] = $row['SpeseDestinazioneId'];
			$arr_fornitori[$key]['Destinazione'] = $row['Nome'];
		}
		return $arr_fornitori;
	}
	
	public function getDestinazioneById($SpeseDestinazioneId) {
		global $user;
		$db = $this->conn;
		$sql = "SELECT SpeseDestinazioneId, Nome From RT_SpeseDestinazione WHERE SpeseDestinazioneId = $SpeseDestinazioneId";
		$row = $db->query_first($sql);
		
		return $row;
	}

	public function getPagamenti() {
		global $user;
		$db = $this->conn;
		$sql = "SELECT SpesePagamentoId, Nome From RT_SpesePagamento ORDER BY Nome";
		$rows = $db->fetch_array($sql);
		$arr_fornitori = array();
		foreach ($rows as $key=>$row){
			$arr_fornitori[$key]['PagamentoId'] = $row['SpesePagamentoId'];
			$arr_fornitori[$key]['Pagamento'] = $row['Nome'];
		}
		return $arr_fornitori;
	}
	
	public function getPagamentoById($SpesePagamentoId) {
		global $user;
		$db = $this->conn;
		$sql = "SELECT SpesePagamentoId, Nome From RT_SpesePagamento WHERE SpesePagamentoId = $SpesePagamentoId";
		$row = $db->query_first($sql);
		
		return $row;
	}

}
?>
