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
class AnagraficaTipo  {
    
public $AnagraficaTipoId;
public $AnagraficaTipo;
public $AnagraficaTipoSigla;
public $ArrAnagraficaTipo=Array();



public $conn;



function __construct($AnagraficaTipoId) {
    $this->AnagraficaTipoId = $AnagraficaTipoId;
}



public function getAllAnagraficaTipo()
{
        $db=$this->conn;
        $sql = "SELECT * From AnagraficaTipo where AnagraficaTipoStato=1 order by AnagraficaTipoOrdinamento asc";
        $this->ArrAnagraficaTipo = $db->fetch_array($sql);
    
}
    
    

}
?>
