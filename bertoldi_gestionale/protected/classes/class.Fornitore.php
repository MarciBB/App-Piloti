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
class Fornitore {
    
  
    public $Id;
    public $conn;
    public $DatiGenerali;



	function __construct( $Id = null ) {
	    $this->Id = $Id;
	}

	public function inizializzaDatiGenerali()
	{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_Fornitori WHERE Cancella=0 and FornitoreId = $Id and OdcIdRef = $user->OdcId order by Nome";      
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef'])) {
			$this->DatiGenerali=$row;
        } else {
            print("errore");
            exit();                     
        }   
	}

	public function getAllForSelect()
	{ 
		global $user;
		$db = $this->conn;
		$sql = "SELECT FornitoreId, Nome From RT_Fornitori WHERE Stato=1 AND Cancella=0 AND OdcIdRef = $user->OdcId ORDER BY Nome";
		$rows = $db->fetch_array($sql);
		$arr_fornitori = array();
		foreach ($rows as $key=>$row){
			$arr_fornitori[$key]['FornitoreId'] = $row['FornitoreId'];
			$arr_fornitori[$key]['Fornitore'] = $row['Nome'];
		}
		return $arr_fornitori;
	}
	
	public function getCategorieFornitore() {
		global $user;
		$db = $this->conn;
		$sql = "SELECT FornitoriCategoriaId, Nome From RT_FornitoriCategorie ORDER BY Nome";
		$rows = $db->fetch_array($sql);
		$arr_fornitori = array();
		foreach ($rows as $key=>$row){
			$arr_fornitori[$key]['FornitoriCategoriaId'] = $row['FornitoriCategoriaId'];
			$arr_fornitori[$key]['FornitoriCategoria'] = $row['Nome'];
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

}
?>
