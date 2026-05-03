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
class Tratta {
    
  
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
        $sql = "SELECT * From RT_Tratta WHERE TrattaId=$Id and OdcIdRef=$user->OdcId";      
      //echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

function getAll()

{
       global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_ElencoTratta WHERE OdcIdRef=$user->OdcId";      
     
        $row = $db->fetch_array($sql);
        return $row;
    
    
}
    
    
   
    
    
}
?>
