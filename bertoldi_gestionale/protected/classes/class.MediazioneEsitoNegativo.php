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
class MediazioneEsitoNegativo {
    
public $MediazioneEsitoNegativoId;
public $MediazioneEsitoNegativo;
public $ArrMediazioneEsitoNegativo=Array();
public $conn;



function __construct($MediazioneEsitoNegativoId=null) {
    $this->MediazioneEsitoNegativoId = $MediazioneEsitoNegativoId;
}



public function getAll()
{
        $db=$this->conn;
        $sql = "SELECT MediazioneEsitoNegativoId,MediazioneEsitoNegativo from MediazioneEsitoNegativo order by MediazioneEsitoNegativo asc";
        $this->ArrMediazioneEsitoNegativo=$db->fetch_array($sql);
       
        return ($this->ArrMediazioneEsitoNegativo);
        
    
}

public function getStatoMediazioneById($MediazioneEsitoNegativoId)
{
        $db=$this->conn;
        $sql = "SELECT MediazioneEsitoNegativoId,MediazioneEsitoNegativo from MediazioneEsitoNegativo where MediazioneEsitoNegativoId=$MediazioneEsitoNegativoId";
        $row = $db->query_first($sql);        
        // se l'operatore esiste ed appartiene ad un gestore per cui esiste la sede indicata
       return $row['MediazioneEsitoNegativo'];
  
        
        
    
}


    
    

}
?>

