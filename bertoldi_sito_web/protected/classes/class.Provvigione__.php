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
    
   
    public $ProvvigioneId;
    public $conn;
    public $ProvvigioneDatiGenerali;
    public $ProvvigioneDettagli;



function __construct($ProvvigioneId=null) {
    $this->ProvvigioneId = $ProvvigioneId;
}

public function inizializzaDatiGenerali($ProvvigioneId)
{
         global $user;
        $db=$this->conn;
        $ProvvigioneId=$this->ProvvigioneId;
        $sql = "SELECT * From Provvigione WHERE ProvvigioneId=$ProvvigioneId";      
        
        $row = $db->query_first($sql);
        
        if (!empty($row['ProvvigioneId']))
        $this->ProvvigioneDatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

     function inizializzaProvvigioneDettagli()
     {
        $db=$this->conn;
        $ProvvigioneId=$this->ProvvigioneId;
        $sql = "SELECT * from ProvvigioneDettaglio WHERE ProvvigioneId=$ProvvigioneId";
       
        $row = $db->fetch_array($sql);
        $this->ProvvigioneDettagli=$row;
      
        return $row;
     }
   
    
    
}
?>
