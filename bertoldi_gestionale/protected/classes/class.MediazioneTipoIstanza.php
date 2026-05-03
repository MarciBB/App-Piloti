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
class MediazioneTipoIstanza  {
    
public $MediazioneTipoIstanzaId;
public $MediazioneTipoIstanza;
public $ArrMediazioneTipoIstanza=Array();



public $conn;



function __construct($MediazioneTipoIstanzaId) {
    $this->MediazioneTipoIstanzaId = $MediazioneTipoIstanzaId;
}



public function getAll()
{
        $db=$this->conn;
        $sql = "SELECT MediazioneTipoIstanzaId,MediazioneTipoIstanza From MediazioneTipoIstanza ";
        $this->ArrMediazioneTipoIstanza=$db->fetch_array($sql);
       
        return ($this->ArrMediazioneTipoIstanza);
        
    
}
    
    

}
?>

