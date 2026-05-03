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
class NotaDiCredito {
    
    public $conn;
    public $OdcId;
    public $Odc;
    public $Numero; 
    public $Data; 
    public $Destinatario; 
    public $TotaleFattura;
    

    function __construct() {
        
    }
    
     function inizializza($FatturaId) {
                
        $db=$this->conn;
        $sql = "SELECT * From ViewFatturaElenco WHERE FatturaId=$FatturaId";
       
        $row = $db->query_first($sql);
            // se esiste l'anagrafica
            if(!empty($row['FatturaId']))
            {
                $this->GestoreId=$row['GestoreIdRef'];
                $this->OdcId=$row['OdcIdRef'];
                $this->Numero=$row['NumeroFattura'];
                $this->Data=$row['DataFattura'];                    
                $this->TotaleFattura=$row['TotaleFattura'];
            }
        }
 
}
?>
