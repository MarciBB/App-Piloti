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
class MediazioneModPre {
    
public $MediazioneModPreId;
public $MediazioneModPre;
public $MediazioneModPreTipoId;
public $ArrMediazioneModPre=Array();



public $conn;



function __construct($MediazioneModPreId) {
    $this->MediazioneModPreId = $MediazioneModPreId;
}



public function getAll()
{
        $db=$this->conn;
        $sql = "SELECT MediazioneModPreId,MediazioneModPre From MediazioneModPre order by MediazioneModPre asc";
        $this->ArrMediazioneModPre=$db->fetch_array($sql);
       
        return ($this->ArrMediazioneModPre);
        
    
}
    
    

}
?>

