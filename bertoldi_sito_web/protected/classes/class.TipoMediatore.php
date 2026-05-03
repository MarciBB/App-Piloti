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
class TipoMediatore  {
    
public $TipoMediatoreId;
public $Tipo;
public $ArrMediatoreTipo=Array();


public $conn;


function __construct($TipoMediatoreId) {
    $this->TipoMediatoreId = $TipoMediatoreId;
}

   public function getAllMediatoreTipo()
{
        $db=$this->conn;
        $sql = "SELECT * From MediatoreTipo";
        $this->ArrMediatoreTipo = $db->fetch_array($sql);
    
} 

   public function getAllMediatoreTipoAdd()
{
        $db=$this->conn;
        $sql = "SELECT * From MediatoreTipo WHERE Stato=1 and Cancella='n'";
        $this->ArrMediatoreTipo = $db->fetch_array($sql);
    
}

}
?>
