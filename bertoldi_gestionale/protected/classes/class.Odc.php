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
class Odc {
    
   
    public $OdcId;
    public $conn;
    public $OdcDatiGenerali;



function __construct($OdcId=null) {
    $this->OdcId = $OdcId;
}

public function inizializzaDatiGenerali($OdcId)
{
         global $user;
        $db=$this->conn;
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";      
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcId']))
        $this->OdcDatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

public function getOdcAll()
{
    
        global $user;
        $db=$this->conn;
        $sql = "SELECT * From Odc where OdcId<>1 order by Odc asc";      
        $row = $db->fetch_array($sql);
        
       
        return $row;
    
}

    
   
    
    
}
?>
