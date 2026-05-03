<?php

class Lingua {
    
public $LinguaId;
public $Lingua;
public $ArrLingua=Array();



public $conn;



function __construct($LinguaId) {
    $this->LinguaId = $LinguaId;
}



public function getAll()
{
        $db=$this->conn;
        $sql = "SELECT LinguaId,Lingua From Lingua order by Lingua asc";
        $this->ArrLingua=$db->fetch_array($sql);
       
        return ($this->ArrLingua);
        
    
}
    
    

}
?>

