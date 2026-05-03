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
class Mezzo {
    
  
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
        $sql = "SELECT * From RT_Mezzo WHERE AppMezzoId=$Id and OdcIdRef=$user->OdcId";      
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

public function getAll()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_Mezzo WHERE OdcIdRef=$user->OdcId order by AppMezzo Asc";      
     
        $row = $db->fetch_array($sql);
        return $row;
      
    
    
}

    
    
   
    
    
}
?>
