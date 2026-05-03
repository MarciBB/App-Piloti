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
class TrattaDirezione {
    
  
    public $Id;
    public $conn;
    public $DatiGenerali;



function __construct($Id=null) {
    $this->Id = $Id;
}


public function getAll()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_TrattaDirezione WHERE OdcIdRef=$user->OdcId order by AppTrattaDirezione Asc";      
     
        $row = $db->fetch_array($sql);
        return $row;
      
    
    
}

    
    
   
    
    
}
?>
