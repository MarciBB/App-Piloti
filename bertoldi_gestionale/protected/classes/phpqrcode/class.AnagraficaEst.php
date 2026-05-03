<?php

class AnagraficaEst {
    
    public $AnagraficaId;
    public $AnagraficaTipoId;
    public $AnagraficaTipo;
    public $CognomeRagioneSociale;
    public $Nome;
    public $CodiceFiscale;
    public $PartitaIva;
    public $DataNascita;
 
    public $Telefono;
    public $Fax;
    public $Cellulare;
    public $Email;
    public $EmailPec;
    public $OpeIns;
    public $SedeIns;
    public $IpIns;
    public $OpeAgg;
    public $SedeAgg;
    public $IpAgg;
    public $Stato;
    public $Cancella;
    public $OdcIdRef;
    public $GestoreIdRef;
    public $IndirizzoResidenza;
    public $CapResidenza;
    
    
    public $NazioneNascita;
    public $NazioneNascitaId;
    public $RegioneNascita;
    public $RegioneNascitaId;
    public $ComuneNascita;
    public $ComuneNascitaId;
    
    public $NazioneResidenza;
    public $NazioneResidenzaId;
    public $RegioneResidenza;
    public $RegioneResidenzaId;
    public $ComuneResidenza;
    public $ComuneResidenzaId;
    
    
  
    public $MaxIdResidenza;
    
    
    
    
    public $conn;
    
    function __construct($AnagraficaId) {
        $this->AnagraficaId = $AnagraficaId;
    }

    
    function inizializza() {
        
       
        global $user;
        $db=$this->conn;
        $id=$this->AnagraficaId;
        $sql = "SELECT * From AnagraficaEstDettaglio WHERE OdcIdRef=$user->OdcId and AnagraficaId=$id";
       
        $row = $db->query_first($sql);
			
		
            // se esiste l'anagrafica
            if(!empty($row['AnagraficaId']))
            {

                    $this->AnagraficaTipoId=$row['AnagraficaTipoId'];
                    $this->AnagraficaTipo=$row['AnagraficaTipo'];
                    $this->CognomeRagioneSociale=$row['CognomeRagioneSociale'];
                    $this->Nome=$row['Nome'];
                    $this->CodiceFiscale=$row['CodiceFiscale'];
                    $this->PartitaIva=$row['PartitaIva'];
                    $this->DataNascita=$row['DataNascita'];
                    $this->ComuneNascitaId=$row['ComuneNascitaId'];
                    $this->Telefono=$row['Telefono'];
                    $this->Fax=$row['Fax'];
                    $this->Cellulare=$row['Cellulare'];
                    $this->Email=$row['Email'];
                    $this->EmailPec=$row['EmailPec'];
                    $this->OpeIns=$row['OpeIns'];
                    $this->SedeIns=$row['SedeIns'];
                    $this->IpAgg=$row['IpAgg'];
                    $this->Stato=$row['Stato'];
                    $this->OdcIdRef=$row['OdcIdRef'];
                    
                     $this->IndirizzoResidenza=$row['IndirizzoResidenza'];
                      $this->CapResidenza=$row['CapResidenza'];
                    
                    
                    
                    $this->ComuneNascita=$row['ComuneNascita'];
                    $this->ComuneNascitaId=$row['ComuneNascitaId'];
                    
                    $this->RegioneNascita=$row['RegioneNascita'];
                    $this->RegioneNascitaId=$row['RegioneNascitaId'];
                    
                    $this->NazioneNascita=$row['NazioneNascita'];
                    $this->NazioneNascitaId=$row['NazioneNascitaId'];
                    
                     $this->ComuneResidenza=$row['ComuneResidenza'];
                    $this->ComuneResidenzaId=$row['ComuneResidenzaId'];
                    
                    $this->RegioneResidenza=$row['RegioneResidenza'];
                    $this->RegioneResidenzaId=$row['RegioneResidenzaId'];
                    
                    $this->NazioneResidenza=$row['NazioneResidenza'];
                    $this->NazioneResidenzaId=$row['NazioneResidenzaId'];
                    
                    
                    $this->MaxIdResidenza=$row['MaxIdResidenza'];
                    
                    
  
                    

            }
        }
    

    
    

   





    
}
?>
