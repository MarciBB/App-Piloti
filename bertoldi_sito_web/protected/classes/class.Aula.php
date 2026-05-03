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
class Aula {
    
    public $conn;
    public $Stato;
    public $Aula;
    public $AulaId;
    public $Capienza;
    public $SedeId;
    

    function __construct() {
        
    }
    
     function inizializza($AulaId) {
        
       
        
        $db=$this->conn;
        $sql = "SELECT * From ElencoAuleView WHERE AulaId=$AulaId";
       
        $row = $db->query_first($sql);
			
		
            // se esiste l'anagrafica
            if(!empty($row['AulaId']))
            {

                    $this->Aula=$row['Aula'];
                    $this->Capienza=$row['Capienza'];
                    $this->Stato=$row['Stato'];
                    $this->SedeId=$row['SedeId'];
                    
                    
                    
                    
  
                    

            }
        }
        
        public function getAuleBySede($SedeId,$AulaId)
        {
        $out = array();
        $db=$this->conn;
        $sql = "SELECT AulaId, Aula,Stato from Aula where SedeId=$SedeId and Cancella=0";
        return $db->fetch_array($sql);
        }    
        
         public function getAuleDisponibili($SedeId,$Data,$Ora)
        {
        $out = array();
        $db=$this->conn;
        $sql = "SELECT AulaId, Aula,Stato from Aula where SedeId=$SedeId and Cancella=0";
        return $db->fetch_array($sql);
        }    
    
   

    
    
}
?>
