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
class MediazioneTipoRichiesta {
    
public $MediazioneTipoRichiestaId;
public $MediazioneTipoRichiesta;
public $ArrMediazioneTipoRichiesta=Array();



public $conn;



function __construct($MediazioneTipoRichiestaId) {
    $this->MediazioneTipoRichiestaId = $MediazioneTipoRichiestaId;
}



public function getAll()
{
        $db=$this->conn;
        $sql = "SELECT MediazioneTipoRichiestaId,MediazioneTipoRichiesta From MediazioneTipoRichiesta ";
        $this->ArrMediazioneTipoRichiesta=$db->fetch_array($sql);
       
        return ($this->ArrMediazioneTipoRichiesta);
        
    
}
    
    

}
?>

