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
class Regione  {
    
public $RegioneId;
public $Regione;
public $ArrRegione=Array();
public $DatiGenerali;



public $conn;



function __construct($RegioneId) {
    $this->RegioneId = $RegioneId;
    $this->Id = $Id;
}



public function inizializzaDatiGenerali()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_ElencoRegione WHERE RegioneId=$Id";
        //echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['Regione']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

public function getAllRegione()
{
        $db=$this->conn;
        $sql = "SELECT RegioneId,Regione From Regione";
        $this->ArrRegione = $db->fetch_array($sql);
    
}

public function getRegioneByIdNazione($NazioneId_)
{
        $db=$this->conn;
        $sql = "SELECT RegioneId,Regione From Regione where NazioneId=$NazioneId_";
        
        $this->ArrRegione = $db->fetch_array($sql);
    
}


    
    

}
?>
