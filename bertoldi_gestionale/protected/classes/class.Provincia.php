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
class Provincia  {
    
public $ProvinciaId;
public $Provincia;
public $ArrProvincia=Array();
public $DatiGenerali;



public $conn;



function __construct($ProvinciaId) {
    $this->ProvinciaId = $ProvinciaId;
    $this->Id = $Id;
}

public function inizializzaDatiGenerali()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_ElencoProvincia WHERE ProvinciaId=$Id";
        //echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['Provincia']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

public function getAllProvincia()
{
        $db=$this->conn;
        $sql = "SELECT ProvinciaId,Provincia From Provincia";
        $this->ArrRegione = $db->fetch_array($sql);
    
}

public function getProvinciaByIdRegione($RegioneId_)
{
        $db=$this->conn;
        $sql = "SELECT ProvinciaId,Provincia From Provincia where RegioneId=$RegioneId_";
        
        $this->ArrRegione = $db->fetch_array($sql);
    
}


    
    

}
?>
