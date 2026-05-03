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
class Convenzione {
    
   
    public $ConvenzioneId;
    public $conn;
    public $ConvenzioneDatiGenerali;
    public $ConvenzioneDettagli;



function __construct($ConvenzioneId=null) {
    $this->ConvenzioneId = $ConvenzioneId;
}

public function inizializzaDatiGenerali($ConvenzioneId)
{
         global $user;
        $db=$this->conn;
        $ConvenzioneId=$this->ConvenzioneId;
        $sql = "SELECT * From Convenzione WHERE ConvenzioneId=$ConvenzioneId";      
        
        $row = $db->query_first($sql);
        
        if (!empty($row['ConvenzioneId']))
        $this->ConvenzioneDatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

     function inizializzaConvenzioneDettagli()
     {
        $db=$this->conn;
        $ConvenzioneId=$this->ConvenzioneId;
        $sql = "SELECT * from ConvenzioneDettaglio WHERE ConvenzioneId=$ConvenzioneId";
        //echo($sql);
        $row = $db->fetch_array($sql);
        $this->ConvenzioneDettagli=$row;
      
        return $row;
     }
   
    
    
}
?>
