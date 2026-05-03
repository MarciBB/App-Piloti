<?php

class Mediatore {
    
    public $MediatoreId;
    
    public $MediatoreDatiGenerali;
    public $MediatoreRapportoOdc;
     public $MediatoreMaterie;
      public $MediatoreLingue;
      public $ArrMediatoreTipo=Array();
    
    public $conn;
    
    function __construct($MediatoreId) {
        $this->MediatoreId = $MediatoreId;
    }
    
    
     function inizializzaDatiGenerali()
     {
         global $user;
        $db=$this->conn;
        $MediatoreId=$this->MediatoreId;
        $sql = "SELECT * from MediatoreDettaglio WHERE OdcIdRef=$user->OdcId and MediatoreId=$MediatoreId";
        $row = $db->query_first($sql);
        $this->MediatoreDatiGenerali=$row;
     }
     
     function inizializzaRapportoOdc()
     {
        $db=$this->conn;
        $MediatoreId=$this->MediatoreId;
        $sql = "SELECT * from MediatoreContratto WHERE MediatoreId=$MediatoreId limit 1 ";
        $row = $db->query_first($sql);
        $this->MediatoreRapportoOdc=$row;
     }
     
      function inizializzaCompetenzaMaterie()
     {
        $db=$this->conn;
        $MediatoreId=$this->MediatoreId;
        $sql = "SELECT * from MediatoreMaterie WHERE MediatoreId=$MediatoreId";
        
        $row = $db->fetch_array($sql);
        $this->MediatoreMaterie=$row;
      
        return $row;
     }
     
       function inizializzaCompetenzaLingue()
     {
        $db=$this->conn;
        $MediatoreId=$this->MediatoreId;
        $sql = "SELECT * from MediatoreLingue WHERE MediatoreId=$MediatoreId";
        
        $row = $db->fetch_array($sql);
        $this->MediatoreLingue=$row;
      
        return $row;
     }
    
    public function getAllMediatoreTipo()
{
        $db=$this->conn;
        $sql = "SELECT * From MediatoreTipo";
        $this->ArrMediatoreTipo = $db->fetch_array($sql);
    
}
  
      public function getAllMediatori()
{
        global $user;
        $OdcId=$user->OdcId;
        $db=$this->conn;
        $sql = "SELECT Mediatore.MediatoreId, Concat(Mediatore.CognomeRagioneSociale,' ',Mediatore.Nome) as NomeMediatore FROM Mediatore where OdcIdRef=$OdcId order by NomeMediatore";
        
        return ($db->fetch_array($sql));
    
}


    

}
?>