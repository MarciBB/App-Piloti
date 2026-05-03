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
class Linea {
    
  
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
        $sql = "SELECT * From RT_Linea WHERE LineaId=$Id and OdcIdRef=$user->OdcId";      
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

    
    public function getAllForSelect()
{        global $user;
        $db=$this->conn;
        $LineaId=$this->Id;
        if (!$this->Id)
            $LineaId=0;
         $sql = "SELECT LineaId,LineaNome From RT_Linea WHERE ((Cancella=0 and Stato=1) or (LineaId=$LineaId)) and OdcIdRef=$user->OdcId order by LineaNome";      
     
         return ($db->fetch_array($sql));
        
    
}
   
    
    
}
?>
