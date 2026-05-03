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
class MembershipClub {
    
  
    public $Id;
    public $conn;
    public $DatiGenerali;



function __construct($Id=null) {
    $this->Id = $Id;
}

public function inizializzaDatiGenerali()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_MembershipClub WHERE MembershipClubId=$Id and OdcIdRef=$user->OdcId";      
     
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef'])) {
            if(!isset($row['ComuneResidenzaId'])){
                $row['ComuneResidenzaId'] = '';
                $row['Comune'] = '';
                $row['idnazione'] = '';
                
            } else {
                $sqlComune = "SELECT * FROM Comune c
                    LEFT JOIN Provincia p ON c.Provincia = p.ProvinciaId 
                    LEFT JOIN Regione r ON r.RegioneId = p.RegioneId 
                    WHERE c.ComuneId =  ".$row['ComuneResidenzaId'];
                $rowTemp = $db->query_first($sqlComune);
                $row['ComuneResidenzaId'] = $rowTemp['ComuneId'];
                $row['Comune'] = $rowTemp['Comune'];
                $row['idnazione'] = $rowTemp['idnazione'];;
            }
            $this->DatiGenerali=$row;
        } else {
            print("errore");
            exit();
        }
    
    
}


    
    
   
    
    
}
?>
