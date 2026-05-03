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
class TipologiaBiglietto {
    
  
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
        $sql = "SELECT * From RT_TipologiaBiglietto WHERE TipologiaBigliettoId=$Id and OdcIdRef=$user->OdcId";      
    //  echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

    
public function getAll($perTipo = false, $tipoTour = null) {
	$db=$this->conn;
    if(isset($tipoTour)) {
        $sqltour = " AND TipoTour = $tipoTour ";
    } else {
        $sqltour = '';
    }
	if(!$perTipo) {
		$sql = "SELECT TipologiaBigliettoId, TipologiaBiglietto From RT_TipologiaBiglietto WHERE Cancella = 0 $sqltour Order By TipologiaBiglietto";
		return $db->fetch_array($sql);
	} else {
		$biglietti = array();
		$sql = "SELECT TipologiaBigliettoId, TipologiaBiglietto From RT_TipologiaBiglietto WHERE Cancella = 0 AND OccupaPosto = 1 $sqltour Order By TipologiaBiglietto";
		$biglietti['passeggeri'] = $db->fetch_array($sql);
		$sql = "SELECT TipologiaBigliettoId, TipologiaBiglietto From RT_TipologiaBiglietto WHERE Cancella = 0 AND OccupaPosto = 0 $sqltour Order By TipologiaBiglietto";
		$biglietti['servizi'] = $db->fetch_array($sql);
		return $biglietti;
	}
}
    
   
    
    
}
?>
