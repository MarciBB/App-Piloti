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
class Comune  {
    
public $ComuneId;
public $Comune;
public $ArrComune=Array();



public $conn;



function __construct($ComuneId) {
    $this->RegioneId = $ComuneId;
}





public function getComuneByIdRegione($RegioneId_)
{
        $db=$this->conn;
        $sql = "SELECT ComuneId,Comune From Comune where RegioneId=$RegioneId_";
        $this->ArrComune = $db->fetch_array($sql);
    
}


    
    

}
?>
