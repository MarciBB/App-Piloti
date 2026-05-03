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
class Flotta {
    
  
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
	    $sql = "SELECT * From RT_Flotta WHERE Cancella=0 and FlottaId=$Id and OdcIdRef=$user->OdcId";
	    $row = $db->query_first($sql);
	        
        if (!empty($row['OdcIdRef']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
        }
	}
	
	public function getNumPosti($idBus){
		$sql = "SELECT RT_TipologiaBus.TotalePosti FROM RT_GestioneOttimizzataFlotta 
				LEFT JOIN RT_Flotta ON (RT_Flotta.FlottaId=RT_GestioneOttimizzataFlotta.BusId)
				LEFT JOIN RT_TipologiaBus ON (RT_Flotta.TipologiaBusId=RT_TipologiaBus.TipologiaBusId)
				WHERE RT_GestioneOttimizzataFlotta.GestioneOttimizzataFlottaId=$idBus";
		$row = $this->conn->query_first($sql);
		return $row['TotalePosti'];
	}
	
	public function getNome($idBus){
		$sql = "SELECT Nome FROM RT_Flotta
			WHERE FlottaId=$idBus";
		$row = $this->conn->query_first($sql);
		return $row['Nome'];
	}
	
	
	public function getAllForSelect()
	{
		global $user;
		$db=$this->conn;
		$sql = "SELECT FlottaId, Targa From RT_Flotta WHERE Cancella = 0 order by FlottaId";
		
		return ($db->fetch_array($sql));
	}
	
	public function getAllForSelectModel()
	{
		global $user;
		$db=$this->conn;
		$sql = "SELECT FlottaId, Modello From RT_Flotta WHERE Cancella = 0 order by FlottaId";
		
		return ($db->fetch_array($sql));
	}
	
	
    
}
?>
