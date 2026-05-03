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
class TipologiaBus {
    
  
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
        $sql = "SELECT * From RT_TipologiaBus WHERE Cancella=0 and TipologiaBusId=$Id and OdcIdRef=$user->OdcId order by TipologiaBus";      
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
{ global $user;
        $db=$this->conn;
         $sql = "SELECT TipologiaBusId,TipologiaBus From RT_TipologiaBus WHERE Cancella=0 and OdcIdRef=$user->OdcId order by TipologiaBus";      
     //   echo($sql);
         return ($db->fetch_array($sql));
        
    
}
    
    
public function getDefault()
{ global $user;
        $db=$this->conn;
         $sql = "SELECT TipologiaBusId,TipologiaBus From RT_TipologiaBus WHERE Cancella=0 and IsDefault=1 and OdcIdRef=$user->OdcId order by TipologiaBus";      
     //   echo($sql);
         return ($db->fetch_array($sql));
        
    
}   
    
    
}
?>
