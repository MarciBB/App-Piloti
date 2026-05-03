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
class Listino {
    
  
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
        $sql = "SELECT * From RT_Listino where ListinoId=$Id and OdcIdRef=$user->OdcId";      

        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef']))
        $this->DatiGenerali=$row;
        else
        {
            $this->DatiGenerali=null;
               
            
        }
    
    
}

    
    
   
    
    
}
?>
