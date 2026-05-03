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
class Calcola_Tariffa {
    
   
    public $conn;
    public $Saldo;
    public $Acconto;
    public $Segreteria;



function __construct($OdcId=null) {
    $this->OdcId = $OdcId;
}

public function getTariffa($OdcId)
{
         global $user;
        $db=$this->conn;
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";      
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcId']))
        $this->OdcDatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

    
   
    
    
}
?>
