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



public $conn;



function __construct($RegioneId) {
    $this->RegioneId = $RegioneId;
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
