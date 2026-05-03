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
class Nazione  {
    
    public $NazioneId;
    public $Nazione;
    public $ArrNazione=Array();
    public $DatiGenerali;
    
    
    public $conn;
    
    
    
    function __construct($NazioneId) {
        $this->NazioneId = $NazioneId;
        $this->Id = $NazioneId;
    }
    
    
    public function inizializzaDatiGenerali()
    {
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From Nazione WHERE NazioneId=$Id";
        //echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['Nazione']))
            $this->DatiGenerali=$row;
            else
            {
                print("errore");
                exit();
                
                
            }
            
            
    }
    
    public function getAllNazione()
    {
        $db=$this->conn;
        $sql = "SELECT * From Nazione";
        $this->ArrNazione = $db->fetch_array($sql);
        
    }
    
    
    
}
?>
