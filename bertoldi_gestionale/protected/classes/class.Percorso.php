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
class Percorso {
    
  
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
        $sql = "SELECT * From RT_Percorso WHERE PercorsoId=$Id and OdcIdRef=$user->OdcId";      
     // echo($sql);
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
{        global $user;
        $db=$this->conn;
        $PercorsoId=$this->Id;
        if (!$this->Id)
            $PercorsoId=0;
         $sql = "SELECT PercorsoId,PercorsoNome From RT_Percorso WHERE ((Cancella=0 and Stato=1) or (Percorsoid=$PercorsoId)) and OdcIdRef=$user->OdcId order by PercorsoNome";      
       //  echo($sql);
         return ($db->fetch_array($sql));
        
    
}  
    
    
}
?>
