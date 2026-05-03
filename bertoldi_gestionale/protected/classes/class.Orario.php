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
class Orario {
    
  
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
        $sql = "SELECT * From RT_ElencoOrario WHERE OrarioId=$Id and OdcIdRef=$user->OdcId";      
    //  echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
    
    
}

public function getOrarioByCorsaFermata($CorsaId,$FermataId)
{
    
     global $user;
        $db=$this->conn;
        $Id=$this->Id;
        //$sql = "SELECT * From RT_ElencoOrario WHERE FermataId=$FermataId and CorsaId=$CorsaId and OdcIdRef=$user->OdcId";      
    	$sql= "select 
        `RT_Orario`.`OrarioId` AS `OrarioId`,
        `RT_Orario`.`Orario` AS `Orario`,
        `RT_Orario`.`FermataId` AS `FermataId`,
        `RT_Orario`.`CorsaId` AS `CorsaId`,
        `RT_Orario`.`DataIns` AS `DataIns`,
        `RT_Orario`.`OpeIns` AS `OpeIns`,
        `RT_Orario`.`SedeIns` AS `SedeIns`,
        `RT_Orario`.`IpIns` AS `IpIns`,
        `RT_Orario`.`DataAgg` AS `DataAgg`,
        `RT_Orario`.`OpeAgg` AS `OpeAgg`,
        `RT_Orario`.`SedeAgg` AS `SedeAgg`,
        `RT_Orario`.`IpAgg` AS `IpAgg`,
        `RT_Orario`.`Stato` AS `Stato`,
        `RT_Orario`.`Cancella` AS `Cancella`,
        `RT_Orario`.`UpdateCount` AS `UpdateCount`,
        `RT_Orario`.`OdcIdRef` AS `OdcIdRef`,
        `RT_Orario`.`GestoreIdRef` AS `GestoreIdRef`,
        `RT_ElencoFermata`.`TrattaId` AS `TrattaId`,
        `RT_ElencoFermata`.`ComuneId` AS `ComuneId`,
        `RT_ElencoFermata`.`FermataNome` AS `FermataNome`,
        `RT_ElencoFermata`.`IsPickup` AS `IsPickup`,
        `RT_ElencoFermata`.`IsDropOff` AS `IsDropOff`,
        `RT_ElencoFermata`.`IsInterscambio` AS `IsInterscambio`,
        `RT_ElencoFermata`.`Prezzo` AS `Prezzo`,
        `RT_ElencoFermata`.`LineaNome` AS `LineaNome`,
        `RT_ElencoFermata`.`TrattaNome` AS `TrattaNome`,
        `RT_ElencoFermata`.`PercorsoNome` AS `PercorsoNome`,
        `RT_ElencoFermata`.`Comune` AS `Comune`,
        `RT_ElencoFermata`.`FermataPeso` AS `FermataPeso`,
        `RT_Corsa`.`CorsaNome` AS `CorsaNome`,
        `RT_Corsa`.`CorsaPeso` AS `CorsaPeso`,
        `RT_Corsa`.`LineaId` AS `LineaId`,
        `RT_ElencoFermata`.`TrattaPeso` AS `TrattaPeso`,
        `RT_Orario`.`GiorniAggiuntivi` AS `GiorniAggiuntivi`
    from
        ((`RT_Orario`
        left join `RT_ElencoFermata` ON ((`RT_Orario`.`FermataId` = `RT_ElencoFermata`.`FermataId`)))
        left join `RT_Corsa` ON ((`RT_Orario`.`CorsaId` = `RT_Corsa`.`CorsaId`)))
    where
        ((`RT_Orario`.`Cancella` = 0)
            and (`RT_Corsa`.`Cancella` = 0))
    		and `RT_Orario`.`FermataId`=$FermataId and `RT_Orario`.`CorsaId`=$CorsaId and `RT_Orario`.`OdcIdRef`=$user->OdcId
    group by `RT_ElencoFermata`.`FermataId` , `RT_ElencoFermata`.`TrattaId` , `RT_ElencoFermata`.`ComuneId` , `RT_Corsa`.`CorsaId`";
    //  echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef']))
      return $row;
        else
            return null;
    
    
    
}

    
    
   
    
    
}
?>
