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
class Autista {
    
  
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
	        $sql = "SELECT * From RT_Autisti WHERE Cancella=0 and AutistiId=$Id and OdcIdRef=$user->OdcId order by Cognome, Nome";      
	        $row = $db->query_first($sql);
	        
	        if (!empty($row['OdcIdRef']))
	        $this->DatiGenerali=$row;
	        else
	        {
	            print("errore");
	            exit();                     
	        }   
	}
	
	public function getAllForSelect()
	{ 
		global $user;
		$db=$this->conn;
		$sql = "SELECT AutistiId, Nome, Cognome From RT_Autisti WHERE Stato=1 AND Cancella=0 AND OdcIdRef=$user->OdcId ORDER BY Cognome";
		$rows = $db->fetch_array($sql);
		$arr_autisti = array();
		foreach ($rows as $key=>$row){
			$arr_autisti[$key]['AutistiId'] = $row['AutistiId'];
			$arr_autisti[$key]['Autisti'] = $row['Cognome']." ".$row['Nome'];
		}
		return $arr_autisti;
	}
   
	public function getPin($idAutista){
		global $user;
		$db=$this->conn;
		$sql = "SELECT * From RT_ClientApp WHERE AutistaId = '$idAutista' and Stato = 1 and Cancella = 0";
		$rows = $db->fetch_array($sql);
		return $rows;
	}
	
	public function checkPin($pin){
		global $user;
		$db=$this->conn;
		$sql = "SELECT * From RT_ClientApp WHERE Pin = '$pin' and Stato = 1 and Cancella = 0";
		$rows = $db->fetch_array($sql);
		if(count($rows)>0){
			return true;
		}else{ 
			return false;
		}
	}
	
	public function addPin($pin, $autistaId){
		global $user;
		$db=$this->conn;
		$storico = new StoricoOperazioni();
		$storico->conn = $db;
		$row['Pin'] = $pin;
		$row['AutistaId'] = $autistaId;
		$row = $storico->operazioni_insert($row, $user);
		$result = $db->insert("RT_ClientApp", $row);
		return $result;
	}
	
	
	public function deletePin($pinId){
		global $user;
		$db=$this->conn;
		$storico = new StoricoOperazioni();
		$storico->conn = $db;
		$row['Cancella'] = 1;
		$row['Stato'] = 0;
		$row = $storico->operazioni_update($row, $user);
		$result = $db->update("rt_clientapp", $row, "ClientAppId=".$pinId);
		return $result;
	}
	
	public function getAccount($username){
		$db=$this->conn;
		$sql = "SELECT * From RT_Autisti WHERE Username = '$username'";
		$rows = $db->fetch_array($sql);
		return $rows[0];
	}
}
?>
