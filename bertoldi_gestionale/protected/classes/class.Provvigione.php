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
class Provvigione {
    
  
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
        $sql = "SELECT * From RT_Provvigione where ProvvigioneId=$Id and OdcIdRef=$user->OdcId";      

        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef']))
        $this->DatiGenerali=$row;
        else
        {
            $this->DatiGenerali=null;
               
            
        }
    
    
}

public function getAllForSelect()
{ global $user;
        $db=$this->conn;
         $sql = "SELECT ProvvigioneId,ProvvigioneNome From RT_Provvigione WHERE Cancella=0 and Stato=1 and OdcIdRef=$user->OdcId order by ProvvigioneNome";      
      // echo($sql);
         return ($db->fetch_array($sql));
        
    
}

    
    
   
    
    
}
?>
