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
class MediazioneStato {
    
public $StatoMediazioneId;
public $StatoMediazione;
public $ArrStatoMediazione=Array();
public $conn;



function __construct($StatoMediazioneId=null) {
    $this->StatoMediazioneId = $StatoMediazioneId;
}



public function getAll()
{
        $db=$this->conn;
        $sql = "SELECT MediazioneStatoId,MediazioneStato from MediazioneStato order by MediazioneStatoId asc";
        $this->ArrStatoMediazione=$db->fetch_array($sql);
       
        return ($this->ArrStatoMediazione);
        
    
}

public function getStatoMediazioneById($IdMediazioneStato)
{
        $db=$this->conn;
        $sql = "SELECT MediazioneStatoId,MediazioneStato from MediazioneStato where MediazioneStatoId=$IdMediazioneStato";
        $row = $db->query_first($sql);        
        // se l'operatore esiste ed appartiene ad un gestore per cui esiste la sede indicata
       return $row['MediazioneStato'];
    return "compilazione";
        
        
    
}


    
    

}
?>

