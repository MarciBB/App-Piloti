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
class AddOn {
    
public $AddOnId;
public $conn;



function __construct($AddOnId) {
    $this->AddOnId = $AddOnId;
}



public function verificaAddOn($AddOnId)
{
        global $user;
         $db=$this->conn;
         $sqladdon="select * from AddOnAttiviPerOdc where AddOnId=$AddOnId and OdcId=$user->OdcId";
         $rowaddon = $db->query_first($sqladdon);
         if (!empty($rowaddon['AddOnId']))
          return true;
         else
          return false;
        
    
}

    
    

}
?>

