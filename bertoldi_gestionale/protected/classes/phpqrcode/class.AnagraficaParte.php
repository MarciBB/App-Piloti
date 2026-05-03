<?php

class AnagraficaParte {
    
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
        $sql = "SELECT * From AnagraficaParteDettaglio WHERE OdcIdRef=$user->OdcId and AnagraficaId=$id";
      
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
		
		function getAnagraficaMediazioni($user,$GestoriId)
		{
		
        $db=$this->conn;
        $id=$this->AnagraficaId;
		$sql = "SELECT MediazioneId From AnagraficaMediazioni WHERE MediazioneId>0 and AnagraficaId=$id and OdcIdRef=$user->OdcId and GestoreIdRef IN ($GestoriId)";
		
		$row = $db->fetch_array($sql);
		
	
		return $row;
		
		}
                
                
                
public function SendMailActivationAreaWeb($user,$codicemediazione,$password)
{
    
         $sql = "SELECT * From AddOnParametriOdc WHERE OdcId=$user->OdcId and AddOnParametroId=3";       
         $row1 = $this->conn->query_first($sql);
         $url_area_utente=$row1['AddOnParametroValue'];
         
    
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        
        $dt=new DT();
        
            
            $mail= new Email; // create the mail
            $mail->SetFrom($odc_fromMail, $odc_fromName);     
            
        
        
        
        $anagrafica=$this->CognomeRagioneSociale." ".$this->Nome;
        $email_anagrafica=$this->Email;
         $appname=Config::$application_name;
        $message= "Gentile $anagrafica ,\n";
        $message.="$odc_nome La informa che il  Suo account e' stato attivato e che da oggi potra' accedere all'area web $url_area_utente per poter visualizzare e monitorare lo stato della pratica di mediazione $codicemediazione. \n";
        $message.="Di seguito le credenziali di accesso:  \n\n";
        $message.="Username: ".$codicemediazione." \n";
        $message.="Password: ".$password." \n";
        
       
        $message.="\n\n Il Coordinatore";
        $mail->Subject = 'Attivazione account utente - Protocollo: '.$codicemediazione ;
         
        if (!empty($email_anagrafica))
         {
             $mail->AddAddress(trim($email_anagrafica));
        $mail->SetBody($message); 
        
        // Autenticazione SMTP
        $mail->IsSMTP();
        $mail->SMTPAuth=true;
        $mail->Host=$odc_serverSmtp; // Server SMTP
        $mail->Port=$odc_portaSmtp;                    // Porta SMTP
        $mail->Username=$odc_username; // SMTP account username
        $mail->Password=$odc_password;        // SMTP account password
        //$mail->Send();
            if($mail->Send())
            {
                $res= 'Email inviata correttamente';
            }
            else
            {
                $res= 'Errore: email non inviata. ' . $mail->ErrorInfo;
            }
        }
       
            
            
       
    
    
    return $res;
    
}
    

    
    

   





    
}
?>
