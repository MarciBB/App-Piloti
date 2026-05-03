<?php

/**
 * Description of class
 *
 * @author a.esposito
 */
class Mediazione {
    
    public $MediazioneId;
    public $MediazioneTipoIstanzaId;
    public $MediazioneTipoRichiestaId;
    public $MediazioneModPreId;
    public $SedeDesignataId;
    public $MediazioneDatiGenerali;
    public $MediazioneSoggettiInstante;
    public $MediazioneSoggettiControparte;
    public $Codice;
    public $MediazioneStatoId;
    
    public $conn;
    
   
 function __construct($MediazioneId) {
        $this->MediazioneId = $MediazioneId;
        
    }
    
 function inizializzaDatiGenerali()
 {
     $this->DeterminaGratuitoPatrocinio();
     
    global $user;
    $db=$this->conn;
    $MediazioneId=$this->MediazioneId;
    $sql = "SELECT * from Mediazione where OdcIdRef=$user->OdcId and MediazioneId=$MediazioneId and Cancella=0";
   
    $row = $db->query_first($sql);
   
    if (!empty($row['Mediazioneid']))
    $this->MediazioneDatiGenerali=$row;
    else
    {
        print("errore");
        exit();
        
    }   
    
 }
 
 function DeterminaGratuitoPatrocinio ()
 {
 
    global $user;
    $db=$this->conn;
    $MediazioneId=$this->MediazioneId;
    $sql = "SELECT * from MediazioneSoggettoParte where OdcIdRef=$user->OdcId and MediazioneId=$MediazioneId and Stato=1 and Cancella=0 and PatrocinioGratuito=1";
  
    $row = $db->query_first($sql);
    $data['GratuitoPatrocinio']=0;
    if (!empty($row['MediazioneId']))
      $data['GratuitoPatrocinio']=1;
      $res=$this->conn->update('Mediazione', $data, "Mediazioneid=".$this->MediazioneId);
      
     
     
 }
 function inizializzaPagamenti()
 {
    $db=$this->conn;
    $MediazioneId=$this->MediazioneId;
     
     
 }
 
 public function getMenuWizard()
 {
    $menuw=array(
    1=>"Dati generali",
    2=>"Parte istante",
    3=>"Controparte",
    4=>"Ragioni della pretesa",
    5=>"Documenti allegati",
    6=>"Pagamenti",
    7=>"Incontro di mediazione",
    8=>"Comunicazioni",
    9=>"Esito mediazione"
    );  
    return $menuw;
     
     
     
 }
 
 public function checkAttesaDati()
 {
     $MediazioneStato=$this->MediazioneDatiGenerali['MediazioneStatoId'];
    
      $menu=$this->getMenuWizard();
      $menusize=sizeof($menu);
      $contamenu=1;
      $inattesa=false;
       while ($contamenu<=$menusize)
       {
           
            $StatoStep=$this->getStatoStep($contamenu);
            if ($StatoStep!="")
                $inattesa=true;
           
           $contamenu++;
       }
      
       if ($inattesa)
        $data['InAttesaDiDatiId']=1;
       else
        $data['InAttesaDiDatiId']=2;    
       $res=$this->conn->update('Mediazione', $data, "Mediazioneid=".$this->MediazioneId);
               
       
      return $inattesa;
    
     
 }
 
 function aggiornaPagamentiConvenzione($ConvenzioneId,$RiferimentoId,$ParteId,$imponibile,$Iva)
 {
     global $user;
     $storico=new StoricoOperazioni();
     if ($ConvenzioneId>0)
     {
     $db=$this->conn;
     
      $sql = "SELECT MediazioneSoggettoParteId,ConvenzioneId from MediazioneSoggettoParte where MediazioneSoggettoParteId=$ParteId and ConvenzioneId=$ConvenzioneId";
      $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
     
     if ($sizeArrSoggetti>0)
     {
       $sql = "SELECT Convenzione from Convenzione where ConvenzioneId=$ConvenzioneId";
       $row2 = $db->query_first($sql);      
       $nomeconvenzione="";
     if (!empty($row2['Convenzione']))
         $nomeconvenzione=$row2['Convenzione'];
     
       $sql = "SELECT PercentualeSconto from ConvenzioneDettaglio where ConvenzioneId=$ConvenzioneId and PagamentoRifId=$RiferimentoId";
     
       
       $row1 = $db->query_first($sql);                
            if (!empty($row1['PercentualeSconto']))
            {  
                $PercentualeSconto=$row1['PercentualeSconto'];
                $data=array();
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$RiferimentoId;
                $data['TipoMovimentoId']=1;
                $data['ConvenzioneId']=$ConvenzioneId;
                $data['ScontoApplicatoPerc']=$PercentualeSconto;
                $data['NomeConvenzione']=$nomeconvenzione;
                $data['Iva']=$Iva;
                $data['Imponibile']=($imponibile*$PercentualeSconto/100)*(-1);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
            }
     }
     }
     return true;
     
 }
 
 function getScaglioneTariffario($user)
 {
     global $user;
     $db=$this->conn;
     $MateriaId=$this->MediazioneDatiGenerali['MateriaId'];
     $ValoreRichiestoTot=$this->MediazioneDatiGenerali['ValoreRichiestoTot'];
     $DataPresentazioneIstanza=$this->MediazioneDatiGenerali['DataPresentazioneIstanza'];
    
     $dt=new DT();
     $DataPresentazione=$dt->format($DataPresentazioneIstanza, "Y-m-d", "Y-m-d");
     
     $mt=new Materia($MateriaId);
     $mt->conn=$db;
     $MateriaTipoId=$mt->getTipoMateriaByIdMateria($MateriaId);
      $db=$this->conn;
      $sql = "SELECT NumeroScaglione From Tariffario where OdcId=$user->OdcId and TariffarioDa<=$ValoreRichiestoTot and TariffarioA>=$ValoreRichiestoTot and ValidoDal<='$DataPresentazione' and ValidoAl>='$DataPresentazione'";
      
      
      $row = $db->query_first($sql);   
     
     if (sizeof($row)>0)
     {
         
         return ($row['NumeroScaglione']);
     }
     else
          return 0;
     
 }
 
 
 function aggiornaPagamentiGratuitoPatrocinio()
 {
     global $user; 
     $db=$this->conn;
      $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where PatrocinioGratuito=1 and  Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId";

       $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      
      
      $i=0;
     
      while ($i<$sizeArrSoggetti)
      {
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
         
          $dataupdate['Imponibile']=0;
          
          
          
          $db->update("MediazioneSoggettoPartePagamento",$dataupdate,"Iva>0 and MediazionePagamentoRifId<>15 and MediazionePagamentoRifId<>12 and MediazioneSoggettoParteId=$ParteId and ((MediazioneFatturaId is null) or (MediazioneFatturaId=0))");
          $i++;
      }
     
      
     return true;
     
 }
 
 
 function aggiornaPagamentiPresentazione($user)
 {
     global $user;
     $storico=new StoricoOperazioni();
     $db=$this->conn;
     
     
    
     $aggiorna_patrocinio=$this->aggiornaPagamentiGratuitoPatrocinio();
    
    
    
     
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $GenerazioneCodiceSoloDiritti=$row['GenerazioneCodiceSoloDiritti'];
       
     
     
     
     
     
     
     $MateriaId=$this->MediazioneDatiGenerali['MateriaId'];
     $ValoreRichiestoTot=$this->MediazioneDatiGenerali['ValoreRichiestoTot'];
     $DataPresentazioneIstanza=$this->MediazioneDatiGenerali['DataPresentazioneIstanza'];
    
     $dt=new DT();
     $DataPresentazione=$dt->format($DataPresentazioneIstanza, "Y-m-d", "Y-m-d");
     
     $mt=new Materia($MateriaId);
     $mt->conn=$db;
     $MateriaTipoId=$mt->getTipoMateriaByIdMateria($MateriaId);
     // obbligatoria 1  riduzione di 1/3 - opzionale 2
     
      $db=$this->conn;
      $sql = "SELECT TariffarioId,SpeseAvvioProcedura,SpeseMediazioneParte,RiduzioneUnTerzoPerParte,Aumento20PercParte,PercRiduzioneObbligatoria,DecretoMinisteriale,IndennitaInizialeMinima,PercAumentoMateriaComplessa,PercAumentoProposta,PercAumentoEsitoPositivo 
      From Tariffario where OdcId=$user->OdcId and TariffarioDa<=$ValoreRichiestoTot and TariffarioA>=$ValoreRichiestoTot and ValidoDal<='$DataPresentazione' and ValidoAl>='$DataPresentazione'";
       //echo($sql);
      
     $row = $db->query_first($sql);                
     if (!empty($row['TariffarioId']))
     {
     $DirittiDiSegreteria=$row['SpeseAvvioProcedura'];
     $IndennitaDiMediazioneBase=$row['SpeseMediazioneParte'];
     $IndennitaDiMediazioneRidotta=$row['RiduzioneUnTerzoPerParte'];
     $PercRiduzioneObbligatoria=$row['PercRiduzioneObbligatoria'];
     $DecretoMinisteriale=$row['DecretoMinisteriale'];
     $IndennitaInizialeMinima=$row['IndennitaInizialeMinima'];
     $PercAumentoMateriaComplessa=$row['PercAumentoMateriaComplessa'];    
     
  //   echo($IndennitaDiMediazioneBase." ----".$PercRiduzioneObbligatoria." -----");
     

     $Iva=  Config::$AppIva;
  
     $IndennitaMediazione=0;
     if ($MateriaTipoId==1)
        // $IndennitaMediazione=$IndennitaDiMediazioneRidotta;
         $IndennitaMediazione=$IndennitaDiMediazioneBase-($IndennitaDiMediazioneBase*$PercRiduzioneObbligatoria);
     elseif ($MateriaTipoId==2)
         $IndennitaMediazione=$IndennitaDiMediazioneBase;
     
    //print_r($this->MediazioneDatiGenerali);
     
     // crea record pagamenti presentazione
     
      // creo record diritti di segreteria
      $sql = "SELECT MediazioneSoggettoParteId,ConvenzioneId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=1 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";
   
      
      $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      
      
      $i=0;
      
      while ($i<$sizeArrSoggetti)
      {
         
          
           
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
          $ConvenzioneId=$ArrSoggetti[$i]['ConvenzioneId'];
          
          // diritti di segreteria
           $riferimento=1;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=$DirittiDiSegreteria;
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$DirittiDiSegreteria,$Iva);
                
                
                
            }
            
            
        // nella fase iniziale vengono generati solo i diritti di segreteria. Se esiste il codice mediazione allora si procede con 
        // la generazione dei record dei pagamenti.    
        if ((($this->MediazioneDatiGenerali['Codice']) and (!empty($this->MediazioneDatiGenerali['Codice']))) or ($GenerazioneCodiceSoloDiritti==0))
        {
            $IndennitaMediazioneTot=0;
            $riferimento=0;
            $InednnitaMediazioneSaldo=0;
            $checkaccettazionecontroparte=0;
            // se congiunta applico il 100%
            if ($this->MediazioneDatiGenerali['mediazioneTipoIstanzaId']==2)
            {
            $checkaccettazionecontroparte=0;
            $riferimento=4;
            $IndennitaMediazioneTot=$IndennitaMediazione;
            }
            else // verifico se calcolare la tariffa iniziale o appartenente alle modifiche di un decreto legge
            {
               
                if ($DecretoMinisteriale==145)    
                {
                $checkaccettazionecontroparte=0;
                $riferimento=10; // 50% indennita iniziale minima anche in assenza di controparte
                $IndennitaMediazioneTot=$IndennitaInizialeMinima;
                $IndennitaMediazioneSaldoParte=$IndennitaMediazione-$IndennitaInizialeMinima;
              /*  echo($IndennitaMediazione."<br />");
                echo($IndennitaInizialeMinima."<br />");
                 echo($IndennitaMediazioneSaldoParte."<br />");*/
                
                }
                else
                {
                $checkaccettazionecontroparte=1;
                $riferimento=2; // 50% indennita di mediazione    
                $IndennitaMediazioneTot=$IndennitaMediazione;
                $IndennitaMediazioneSaldoParte=($IndennitaMediazione/2);   
                }
            }   
            
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                if ($riferimento==2)
                $data['Imponibile']=($IndennitaMediazioneTot/2);
                else
                $data['Imponibile']=($IndennitaMediazioneTot);    
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$IndennitaMediazioneTot,$Iva);
            }
            
             
             $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";
             
$ArrSoggetti1=$db->fetch_array($sql);
             
             // se la controparte ha accettato
             //print_r($ArrSoggetti1);
             if ((sizeof($ArrSoggetti1)>0) and ($this->MediazioneDatiGenerali['mediazioneTipoIstanzaId']!=2))
             {
              
                 // se la mediazione non è congiunta applico il saldo della mediazione alla parte istante
                 
                  $riferimento=3;
                  
                  
                    
                    $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
                    $row1 = $db->query_first($sql);                
                    if (empty($row1['MediazioneSoggettoParteId']))
                    {
                        $data['MediazioneSoggettoParteId']=$ParteId;
                        $data['MediazionePagamentoRifId']=$riferimento;
                        $data['TipoMovimentoId']=1;
                        $data['Iva']=$Iva;
                       // $data['Imponibile']=($IndennitaMediazione/2);
                         $data['Imponibile']=$IndennitaMediazioneSaldoParte;
                        $data=$storico->operazioni_insert($data,$user);
                        $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                        $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$IndennitaMediazioneSaldoParte,$Iva);
                    }
             }
             else
             {
             // se la controparte NON ha accettato
             
              $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and (MediazioneAccettazioneId=2 OR MediazioneAccettazioneId=3) and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";
              
              $ArrSoggetti1=$db->fetch_array($sql);
             //echo($sql);
              if ((sizeof($ArrSoggetti1)>0) and ($checkaccettazionecontroparte==1) ) // solo se si necessita di applicare l'importo in funzione 
                  // della mancata accettazione della controparte
             {
                      //$riferimento=7;
		      $riferimento=3;
                      //echo($sql);
                      //print_r($ArrSoggetti1);
                    //  $parte1=$ArrSoggetti1['MediazioneSoggettoParteId'];
                    
                    $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
                    $row1 = $db->query_first($sql);                
                    if (empty($row1['MediazioneSoggettoParteId']))
                    {
                        $data['MediazioneSoggettoParteId']=$ParteId;
                        $data['MediazionePagamentoRifId']=$riferimento;
                        $data['TipoMovimentoId']=1;
                        $data['Iva']=$Iva;
                       // $data['Imponibile']=$IndennitaMediazione-($IndennitaMediazione*2/3);
			$data['Imponibile']=$IndennitaMediazione/2;
                        $data=$storico->operazioni_insert($data,$user);
                        $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                         $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$IndennitaMediazione,$Iva);
                    }
             }
             }
             
             
             
             if ($this->MediazioneDatiGenerali['MediazioneComplessa']==1)
             {
                 $riferimento=8;
                 $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
                    $row1 = $db->query_first($sql);                
                    if (empty($row1['MediazioneSoggettoParteId']))
                    {
                        $data['MediazioneSoggettoParteId']=$ParteId;
                        $data['MediazionePagamentoRifId']=$riferimento;
                        $data['TipoMovimentoId']=1;
                        $data['Iva']=$Iva;
                        $data['Imponibile']=($IndennitaMediazione*$PercAumentoMateriaComplessa);
                        $data=$storico->operazioni_insert($data,$user);
                        $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                         $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$data['Imponibile'],$Iva);
                    }
                 
                 
             }
            
        }     
          /* fine controllo esistenza codice mediazione*/  
          $i++;
      }
     }
     
      // creo record pagamenti controparte
     if ($this->MediazioneDatiGenerali['DifferenteCentroInteressiControparte']<>1)
      $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";
     else
      $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId";   
     
     $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      $i=0;
      while ($i<$sizeArrSoggetti)
      {
          
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
          
          // diritti di segreteria
            $riferimento=1;
            
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=$DirittiDiSegreteria;
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                 $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$DirittiDiSegreteria,$Iva);
            }
            // 100% indennita di mediazione
            $riferimento=4;
            //echo($ParteId." ");
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=$IndennitaMediazione;
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
             $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$IndennitaMediazione,$Iva);
            
                
                
            }
            
             if ($this->MediazioneDatiGenerali['MediazioneComplessa']==1)
             {
                 $riferimento=8;
                 $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
                    $row1 = $db->query_first($sql);                
                    if (empty($row1['MediazioneSoggettoParteId']))
                    {
                        $data['MediazioneSoggettoParteId']=$ParteId;
                        $data['MediazionePagamentoRifId']=$riferimento;
                        $data['TipoMovimentoId']=1;
                        $data['Iva']=$Iva;
                        $data['Imponibile']=($IndennitaMediazione*$PercAumentoMateriaComplessa);
                        $data=$storico->operazioni_insert($data,$user);
                        $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                         $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$data['Imponibile'],$Iva);
                    }
                 
                 
             }
                
            
            
          $i++;
      }
     
     
     $aggiorna_patrocinio=$this->aggiornaPagamentiPropostaInContumacia();
     
    return true;
 }
 
 function getIndennitaMediazioneValoreProposto()
 {
      global $user;
      $db=$this->conn;
     $MateriaId=$this->MediazioneDatiGenerali['MateriaId'];
     $ValoreRichiestoTot=$this->MediazioneDatiGenerali['ValoreRichiestoTot'];
     $DataPresentazioneIstanza=$this->MediazioneDatiGenerali['DataPresentazioneIstanza'];
    
     $dt=new DT();
     $DataPresentazione=$dt->format($DataPresentazioneIstanza, "Y-m-d", "Y-m-d");
     
     $mt=new Materia($MateriaId);
     $mt->conn=$db;
     $MateriaTipoId=$mt->getTipoMateriaByIdMateria($MateriaId);
     // obbligatoria 1  riduzione di 1/3 - opzionale 2
     
      $db=$this->conn;
      $sql = "SELECT TariffarioId,SpeseAvvioProcedura,SpeseMediazioneParte,RiduzioneUnTerzoPerParte,Aumento20PercParte,PercRiduzioneObbligatoria,DecretoMinisteriale,IndennitaInizialeMinima,PercAumentoMateriaComplessa,PercAumentoProposta,PercAumentoEsitoPositivo  
      From Tariffario where  OdcId=$user->OdcId and TariffarioDa<=$ValoreRichiestoTot and TariffarioA>=$ValoreRichiestoTot and ValidoDal<='$DataPresentazione' and ValidoAl>='$DataPresentazione'";
      
      
     $row = $db->query_first($sql);                
     if (!empty($row['TariffarioId']))
     {
     $DirittiDiSegreteria=$row['SpeseAvvioProcedura'];
     $IndennitaDiMediazioneBase=$row['SpeseMediazioneParte'];
     $IndennitaDiMediazioneRidotta=$row['RiduzioneUnTerzoPerParte'];
     $PercRiduzioneObbligatoria=$row['PercRiduzioneObbligatoria'];
     $DecretoMinisteriale=$row['DecretoMinisteriale'];
     $IndennitaInizialeMinima=$row['IndennitaInizialeMinima'];
     $PercAumentoMateriaComplessa=$row['PercAumentoMateriaComplessa'];            

     $Iva=  Config::$AppIva;
  
     $IndennitaMediazione=0;
     if ($MateriaTipoId==1)
        // $IndennitaMediazione=$IndennitaDiMediazioneRidotta;
         $IndennitaMediazione=$IndennitaDiMediazioneBase-($IndennitaDiMediazioneBase*$PercRiduzioneObbligatoria);
     elseif ($MateriaTipoId==2)
         $IndennitaMediazione=$IndennitaDiMediazioneBase;
     
     
     $SaldoIndennitaValoreProposto=$DirittiDiSegreteria+$IndennitaMediazione;
     }
     return $SaldoIndennitaValoreProposto;
 
 }
 
 function aggiornaEsitoMediazionePositiva($user)
 {
      global $user;
     $storico=new StoricoOperazioni();
     $db=$this->conn;
     $MateriaId=$this->MediazioneDatiGenerali['MateriaId'];
     $ValoreRichiestoTot=$this->MediazioneDatiGenerali['ValoreAccordatoTot'];
     $ValoreInizialeTot=$this->MediazioneDatiGenerali['ValoreRichiestoTot'];
     
     $DataPresentazioneIstanza=$this->MediazioneDatiGenerali['DataPresentazioneIstanza'];
    
     $dt=new DT();
     $DataPresentazione=$dt->format($DataPresentazioneIstanza, "Y-m-d", "Y-m-d");
     
     $mt=new Materia($MateriaId);
     $mt->conn=$db;
     $MateriaTipoId=$mt->getTipoMateriaByIdMateria($MateriaId);
     // obbligatoria 1  riduzione di 1/3 - opzionale 2
     
      $db=$this->conn;
      
      $sql = "SELECT TariffarioId,SpeseAvvioProcedura,SpeseMediazioneParte,RiduzioneUnTerzoPerParte,Aumento20PercParte,PercRiduzioneObbligatoria,DecretoMinisteriale,IndennitaInizialeMinima,PercAumentoMateriaComplessa,PercAumentoProposta,PercAumentoEsitoPositivo   
      From Tariffario where  OdcId=$user->OdcId and TariffarioDa<=$ValoreRichiestoTot and TariffarioA>=$ValoreRichiestoTot and ValidoDal<='$DataPresentazione' and ValidoAl>='$DataPresentazione'";
    
      
     $row1 = $db->query_first($sql);   
     $IndennitaMediazione1=0;
     if (!empty($row1['TariffarioId']))
     {
     $DirittiDiSegreteria=$row1['SpeseAvvioProcedura'];
     $IndennitaDiMediazioneBase=$row1['SpeseMediazioneParte'];
     $IndennitaDiMediazioneRidotta=$row1['RiduzioneUnTerzoPerParte'];
     
     $PercRiduzioneObbligatoria=$row1['PercRiduzioneObbligatoria'];
     $DecretoMinisteriale=$row1['DecretoMinisteriale'];
     $IndennitaInizialeMinima=$row1['IndennitaInizialeMinima'];
     $PercAumentoEsitoPositivo=$row1['PercAumentoEsitoPositivo'];            

     $Iva=  Config::$AppIva;
  
     $IndennitaMediazione=0;
     if ($MateriaTipoId==1)
     {
        // $IndennitaMediazione=$IndennitaDiMediazioneRidotta;
         $IndennitaMediazione1=$IndennitaDiMediazioneBase-($IndennitaDiMediazioneBase*$PercRiduzioneObbligatoria);
     }
     elseif ($MateriaTipoId==2)
         $IndennitaMediazione1=$IndennitaDiMediazioneBase;
     
    
     
     }
       $RichiestaProposta=$this->MediazioneDatiGenerali['RichiestaProposta'];
   
      
      $sql = "SELECT TariffarioId,SpeseAvvioProcedura,SpeseMediazioneParte,RiduzioneUnTerzoPerParte,Aumento20PercParte,PercRiduzioneObbligatoria,DecretoMinisteriale,IndennitaInizialeMinima,PercAumentoMateriaComplessa,PercAumentoProposta,PercAumentoEsitoPositivo   
      From Tariffario where  OdcId=$user->OdcId and TariffarioDa<=$ValoreRichiestoTot and TariffarioA>=$ValoreRichiestoTot and ValidoDal<='$DataPresentazione' and ValidoAl>='$DataPresentazione'";
      
      
     $row = $db->query_first($sql);                
     if (!empty($row['TariffarioId']))
     {
     $DirittiDiSegreteria=$row['SpeseAvvioProcedura'];
     $IndennitaDiMediazioneBase=$row['SpeseMediazioneParte'];
     $PercRiduzioneObbligatoria=$row['PercRiduzioneObbligatoria'];
     $DecretoMinisteriale=$row['DecretoMinisteriale'];
     $IndennitaInizialeMinima=$row['IndennitaInizialeMinima'];
     $PercAumentoEsitoPositivo=$row['PercAumentoEsitoPositivo'];                  

     $Iva=  Config::$AppIva;
  
     $IndennitaMediazione=0;
     if ($MateriaTipoId==1)
        // $IndennitaMediazione=$IndennitaDiMediazioneRidotta;
         $IndennitaMediazione=$IndennitaDiMediazioneBase-($IndennitaDiMediazioneBase*$PercRiduzioneObbligatoria);
     elseif ($MateriaTipoId==2)
         $IndennitaMediazione=$IndennitaDiMediazioneBase;
     
     $SaldoValoreProposto=$this->getIndennitaMediazioneValoreProposto();
     $SaldoDopoEsito=$DirittiDiSegreteria+$IndennitaMediazione;
     
     $diff_saldo=$SaldoDopoEsito-$SaldoValoreProposto;
     
      if (($RichiestaProposta!=2) or ($user->OdcId!=1))
     {  
     
     
      $sql = "SELECT MediazioneSoggettoParteId,ConvenzioneId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=1 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";
      $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      $i=0;
      while ($i<$sizeArrSoggetti)
      {
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
          $ConvenzioneId=$ArrSoggetti[$i]['ConvenzioneId'];
          // Integrazione indennità di mediazione (esito positivo)
           $riferimento=6;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=($IndennitaMediazione1*$PercAumentoEsitoPositivo);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$data['Imponibile'],$Iva);
            }
            if ($diff_saldo>0)
            {
                
                $riferimento=9;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=($diff_saldo);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$diff_saldo,$Iva);
            }
                
                
                
            }
            
          $i++;
      }
      
     }
     
      // creo record pagamenti
      if ($this->MediazioneDatiGenerali['DifferenteCentroInteressiControparte']==1)
     $sql = "SELECT MediazioneSoggettoParteId,ConvenzioneId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc";
      else
     $sql = "SELECT MediazioneSoggettoParteId,ConvenzioneId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";     
          
      
      $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      $i=0;
      while ($i<$sizeArrSoggetti)
      {
          $ConvenzioneId=$ArrSoggetti[$i]['ConvenzioneId'];
          
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
          
          $riferimento=6;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=($IndennitaMediazione1*$PercAumentoEsitoPositivo);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                 $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$data['Imponibile'],$Iva);
            }
            
             if ($diff_saldo>0)
            {
                
                $riferimento=9;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=($diff_saldo);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                 $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$diff_saldo,$Iva);
            }
                
                
                
            }
            
            
            
            
          $i++;
      }
     
     }
     
     
    return true;
 }
 
 
 function aggiornaPagamentiPropostaInContumacia()
 {
     
     
     global $user;
     $storico=new StoricoOperazioni();
     $db=$this->conn;
     $MateriaId=$this->MediazioneDatiGenerali['MateriaId'];
     $ValoreRichiestoTot=$this->MediazioneDatiGenerali['ValoreRichiestoTot'];
     $DataPresentazioneIstanza=$this->MediazioneDatiGenerali['DataPresentazioneIstanza'];
     
     $RichiestaProposta=$this->MediazioneDatiGenerali['RichiestaProposta'];
     $dt=new DT();
     $DataPresentazione=$dt->format($DataPresentazioneIstanza, "Y-m-d", "Y-m-d");
     
     $mt=new Materia($MateriaId);
     $mt->conn=$db;
     $MateriaTipoId=$mt->getTipoMateriaByIdMateria($MateriaId);
     // obbligatoria 1  riduzione di 1/3 - opzionale 2
     
      $db=$this->conn;
      $sql = "SELECT TariffarioId,SpeseAvvioProcedura,SpeseMediazioneParte,RiduzioneUnTerzoPerParte,Aumento20PercParte,PercRiduzioneObbligatoria,DecretoMinisteriale,IndennitaInizialeMinima,PercAumentoMateriaComplessa,PercAumentoProposta,PercAumentoEsitoPositivo   
      From Tariffario where  OdcId=$user->OdcId and TariffarioDa<=$ValoreRichiestoTot and TariffarioA>=$ValoreRichiestoTot and ValidoDal<='$DataPresentazione' and ValidoAl>='$DataPresentazione'";
      
      
     $row = $db->query_first($sql);
     // l'aumento della proposta si applica solo se la materia volontaria
     
     
     if ((!empty($row['TariffarioId'])) and ($RichiestaProposta==2))
     {
     $DirittiDiSegreteria=$row['SpeseAvvioProcedura'];
     $IndennitaDiMediazioneBase=$row['SpeseMediazioneParte'];
     $PercRiduzioneObbligatoria=$row['PercRiduzioneObbligatoria'];
     $DecretoMinisteriale=$row['DecretoMinisteriale'];
     $IndennitaInizialeMinima=$row['IndennitaInizialeMinima'];
     $PercAumentoMateriaComplessa=$row['PercAumentoMateriaComplessa'];
     $PercAumentoProposta=$row['PercAumentoProposta'];   

     $Iva=  Config::$AppIva;
  
     $IndennitaMediazione=0;
     if ($MateriaTipoId==1)
        // $IndennitaMediazione=$IndennitaDiMediazioneRidotta;
         $IndennitaMediazione=$IndennitaDiMediazioneBase-($IndennitaDiMediazioneBase*$PercRiduzioneObbligatoria);
     elseif ($MateriaTipoId==2)
         $IndennitaMediazione=$IndennitaDiMediazioneBase;
     
     $SaldoDopoEsito=$DirittiDiSegreteria+$IndennitaMediazione;
     
      $sql = "SELECT MediazioneSoggettoParteId,ConvenzioneId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=1 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";
      $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      $i=0;
      while ($i<$sizeArrSoggetti)
      {
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
          $ConvenzioneId=$ArrSoggetti[$i]['ConvenzioneId'];
          // formulazione proposta
           $riferimento=14;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=($IndennitaMediazione*$PercAumentoProposta);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                  $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$data['Imponibile'],$Iva);
            }
          $i++;
      }
     }
     
     
    /*  if ($this->MediazioneDatiGenerali['DifferenteCentroInteressiControparte']==1)
      $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc";
      else
      $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";    
          
      $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      $i=0;
      if ((!empty($row['TariffarioId'])) and ($RichiestaProposta==2))
      {
      while ($i<$sizeArrSoggetti)
      {
          
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
          
          $riferimento=14;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=($IndennitaMediazione*$PercAumentoProposta);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$data['Imponibile'],$Iva);
            }
            
            
          $i++;
      }
      }*/
    
     
     
     
 }
     
     
     
 
 function aggiornaEsitoNegativoProposta($user)
 {
     
   
      global $user;
     $storico=new StoricoOperazioni();
     $db=$this->conn;
     $MateriaId=$this->MediazioneDatiGenerali['MateriaId'];
     $ValoreRichiestoTot=$this->MediazioneDatiGenerali['ValoreRichiestoTot'];
     $DataPresentazioneIstanza=$this->MediazioneDatiGenerali['DataPresentazioneIstanza'];
     
     $RichiestaProposta=$this->MediazioneDatiGenerali['RichiestaProposta'];
     $dt=new DT();
     $DataPresentazione=$dt->format($DataPresentazioneIstanza, "Y-m-d", "Y-m-d");
     
     $mt=new Materia($MateriaId);
     $mt->conn=$db;
     $MateriaTipoId=$mt->getTipoMateriaByIdMateria($MateriaId);
     // obbligatoria 1  riduzione di 1/3 - opzionale 2
     
      $db=$this->conn;
      $sql = "SELECT TariffarioId,SpeseAvvioProcedura,SpeseMediazioneParte,RiduzioneUnTerzoPerParte,Aumento20PercParte,PercRiduzioneObbligatoria,DecretoMinisteriale,IndennitaInizialeMinima,PercAumentoMateriaComplessa,PercAumentoProposta,PercAumentoEsitoPositivo   
      From Tariffario where  OdcId=$user->OdcId and TariffarioDa<=$ValoreRichiestoTot and TariffarioA>=$ValoreRichiestoTot and ValidoDal<='$DataPresentazione' and ValidoAl>='$DataPresentazione'";
      
      
     $row = $db->query_first($sql);
     // l'aumento della proposta si applica solo se la materia volontaria
     if ($MateriaTipoId==2)
     {
     
     if ((!empty($row['TariffarioId'])) and ($RichiestaProposta==1))
     {
     $DirittiDiSegreteria=$row['SpeseAvvioProcedura'];
     $IndennitaDiMediazioneBase=$row['SpeseMediazioneParte'];
     $PercRiduzioneObbligatoria=$row['PercRiduzioneObbligatoria'];
     $DecretoMinisteriale=$row['DecretoMinisteriale'];
     $IndennitaInizialeMinima=$row['IndennitaInizialeMinima'];
     $PercAumentoMateriaComplessa=$row['PercAumentoMateriaComplessa'];
     $PercAumentoProposta=$row['PercAumentoProposta'];   

     $Iva=  Config::$AppIva;
  
     $IndennitaMediazione=0;
     if ($MateriaTipoId==1)
        // $IndennitaMediazione=$IndennitaDiMediazioneRidotta;
         $IndennitaMediazione=$IndennitaDiMediazioneBase-($IndennitaDiMediazioneBase*$PercRiduzioneObbligatoria);
     elseif ($MateriaTipoId==2)
         $IndennitaMediazione=$IndennitaDiMediazioneBase;
     
     $SaldoDopoEsito=$DirittiDiSegreteria+$IndennitaMediazione;
     
      $sql = "SELECT MediazioneSoggettoParteId,ConvenzioneId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=1 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";
      $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      $i=0;
      while ($i<$sizeArrSoggetti)
      {
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
          $ConvenzioneId=$ArrSoggetti[$i]['ConvenzioneId'];
          // formulazione proposta
           $riferimento=5;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=($IndennitaMediazione*$PercAumentoProposta);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                  $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$data['Imponibile'],$Iva);
            }
          $i++;
      }
     }
     
      // creo record pagamenti
      if ($this->MediazioneDatiGenerali['DifferenteCentroInteressiControparte']==1)
      $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc";
      else
      $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoParte where MediazioneSoggettoParteTipoId=2 and MediazioneAccettazioneId=1 and Mediazioneid=$this->MediazioneId and Stato=1 and Cancella=0 order by MediazioneSoggettoParteId asc limit 1";    
          
      $ArrSoggetti=$db->fetch_array($sql);
      $sizeArrSoggetti=sizeof($ArrSoggetti);
      
      $i=0;
      if ((!empty($row['TariffarioId'])) and ($RichiestaProposta==1))
      {
      while ($i<$sizeArrSoggetti)
      {
          
          $ParteId=$ArrSoggetti[$i]['MediazioneSoggettoParteId'];
          
          $riferimento=5;
            $sql = "SELECT MediazioneSoggettoParteId from MediazioneSoggettoPartePagamento where MediazioneSoggettoParteId=$ParteId and Stato=1 and Cancella=0 and MediazionePagamentoRifId=$riferimento";
            $row1 = $db->query_first($sql);                
            if (empty($row1['MediazioneSoggettoParteId']))
            {
                $data['MediazioneSoggettoParteId']=$ParteId;
                $data['MediazionePagamentoRifId']=$riferimento;
                $data['TipoMovimentoId']=1;
                $data['Iva']=$Iva;
                $data['Imponibile']=($IndennitaMediazione*$PercAumentoProposta);
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("MediazioneSoggettoPartePagamento", $data); 
                $upd_conv=$this->aggiornaPagamentiConvenzione($ConvenzioneId,$riferimento,$ParteId,$data['Imponibile'],$Iva);
            }
            
            
          $i++;
      }
      }
     }
     
     
     
    return true;
 }
 
 
public function getNumeroIncontriMediazioneSvolti()
{
    
    $db=$this->conn;
     $MediazioneId=$this->MediazioneId;
      $oggi=date("y-m-d H:i:s");
    $sql = "SELECT Count(MediazioneIncontroId) as NumeroIncontri from MediazioneIncontri where (data<'$oggi' or MediazioneAccettazioneId>1) and MediazioneAccettazioneId is not Null and Mediazioneid=$MediazioneId";
    //echo($sql);
     $row1 = $db->query_first($sql);             
    if (!empty($row1['NumeroIncontri']))
        return $row1['NumeroIncontri'];
    else
        return 0;
     
    
    
}


public function getProssimoIncontro()
{
    
    $db=$this->conn;
     $MediazioneId=$this->MediazioneId;
    $sql = "SELECT *  from MediazioneDettaglioProssimoIncontro where Mediazioneid=$MediazioneId";
    $row = $db->query_first($sql);
    return $row;
     
    
    
}


public function stampaListaElencoIncontri()
{
include_once("mediazione_incontri_datatables.php");

?>
              
                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables_incontri">
<caption>Storico incontri di mediazione</caption>
	<thead>
            
            	<tr class="brain_tabellaTr">
          		<th width="15%">data/ora</th>
			<th width="20%">sede</th>
                        <th width="10%">aula</th>
		        <th width="5%">durata</th>
                        <th width="15%">mediatore</th>
                        <th width="15%">co-mediatore</th>
                        <th width="15%">uditore</th>
                        <th width="10%">conferma mediatore</th>
                        <th width="10%">esito</th>
                        
                        
          	</tr>
	</thead>
	<tbody>
         
		<tr>
                        
			<td colspan="9" class="dataTables_empty">Caricamento in corso...</td>
                       
		</tr>
	</tbody>
         <tfoot> 
		<tr> 
			<th colspan="9" align="left" bgcolor="#EAEAEA"></th>       
               </tr> 
	</tfoot> 
	<!--<tfoot> 
		<tr> 
			
			<th colspan="3" align="left" bgcolor="#EAEAEA"><a class="brain_add" href="javascript:void(0);" onclick="javascript:ExternalLoad('mediazione','mediazione.php?do=add_pagamento&SoggettoTipoId=2');">Aggiungi</a></th> 
		</tr> 
	</tfoot> -->
</table>
    
    
    <?
}

public function GetMaxDataIncontro()
{
    
     $db=$this->conn;
     $MediazioneId=$this->MediazioneId;
    $sql = "SELECT MaxDataIncontro from MediazioneMaxDataIncontro where MediazioneId=$MediazioneId";
  //  echo($sql);
    $row = $db->query_first($sql);
   
    if (!empty($row['MaxDataIncontro']))
        return $row['MaxDataIncontro'];
    else
        return null;
        
   
}

function getValoreMinimoIncasso($SoggettoTipoId,$GruppoPagamento)
{
    
    
    
    
}

function getResiduoPerIncasso ($SoggettoTipoId,$GruppoPagamento)
{
    
    
    
}

function getTotaleResiduoParte($MediazioneParteTipo)
{
    $db=$this->conn;
    $MediazioneId=$this->MediazioneId;
    $sql = "SELECT TotaleDovutoParti from MediazioneTotaleDovutoParti where MediazioneSoggettoParteTipoId=$MediazioneParteTipo and  MediazioneId=$MediazioneId";
    $row = $db->query_first($sql);
    if($row['TotaleDovutoParti'])
        return $row['TotaleDovutoParti'];
    else
        return 0;
    
    
}

public function SendMailToOdcAdmin($CodiceMediazione,$user)
{
    
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_notifica_admin=$row['EmailsNotificaNuovaMediazione'];
        $dt=new DT();
        if ($odc_email_notifica_admin)
        {
            
            $mail= new Email; // create the mail
            $mail->SetFrom($odc_fromMail, $odc_fromName);     
            
        $operatore=$user->Cognome." ".$user->Nome;
        $sede=new Sede();
        $oggetto_mediazione=$this->MediazioneDatiGenerali['Oggetto'];
        $data_ins_mediazione=$dt->format($this->MediazioneDatiGenerali['DataIns'],"Y-m-d H:i:s","d/m/Y H:i:s");
        $sede->conn=$this->conn;
        $sede->inizializza($this->MediazioneDatiGenerali['SedeIns']);
        $codice_sede=$sede->Comune;
        
         $message= "Gentile responsabile dell'Organismo $odc_nome,\n";
        $message.="La informiamo che sul sistema ".Config::$application_name." e' stata protocollata una nuova istanza di mediazione \n";
        $message.=" dall'operatore $operatore \n";
        $message.="in data $data_ins_mediazione dalla sede di $sede->Indirizzo - $sede->Comune \n";
        $message.="avente come oggetto: $oggetto_mediazione. \n";
        
        
         $message.="\n Sistema automatico di notifica ".Config::$application_name;
         $mail->Subject = 'Nuova Mediazione - protocollo '.$CodiceMediazione;
         
         $arr_email=explode(";", $odc_email_notifica_admin);
        //$arr_email=implode($odc_email_notifica_admin,";");
         
        $n_email=count($arr_email);
        
       // print($row['EmailsNotificaNuovaMediazione']);
        
        //print($n_email);
        
        if ($n_email==0)
        $mail->AddAddress(trim($odc_email_notifica_admin));
        else
        {
            $n=0;
            while($n<$n_email)
            {
            //    print($arr_email[$n]);
                $mail->AddAddress(trim($arr_email[$n]));
                $n++;
                
            }
            
            
            
        }
        
        
        
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


public function SendMailToOdcAdminIstanzaWeb($CodiceMediazione,$user)
{
    
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_notifica_admin=$row['EmailsNotificaNuovaMediazione'];
        $dt=new DT();
        if ($odc_email_notifica_admin)
        {
            
            $mail= new Email; // create the mail
            $mail->SetFrom($odc_fromMail, $odc_fromName);     
            
         $operatore=$user->Cognome." ".$user->Nome;
        $sede=new Sede();
        $oggetto_mediazione=$this->MediazioneDatiGenerali['Oggetto'];
        $data_ins_mediazione=$dt->format($this->MediazioneDatiGenerali['DataIns'],"Y-m-d H:i:s","d/m/Y H:i:s");
        $sede->conn=$this->conn;
        $sede->inizializza($this->MediazioneDatiGenerali['SedeIns']);
        $codice_sede=$sede->Comune;
        $appname=Config::$application_name;
         $message= "Gentile responsabile dell'Organismo $odc_nome,\n";
        $message.="La informiamo che sul sistema $appname e' stata creata una nuova istanza di mediazione";
        $message.=" dall'operatore web \n";
        $message.="in data $data_ins_mediazione dalla sede Web \n";
        $message.="avente come oggetto: $oggetto_mediazione. \n";
        
        
         $message.="\n Sistema automatico di notifica ".Config::$application_name;
         $mail->Subject = 'Nuova Mediazione WEB - protocollo temporaneo '.$CodiceMediazione;
         
         $arr_email=explode(";", $odc_email_notifica_admin);
        //$arr_email=implode($odc_email_notifica_admin,";");
         
        $n_email=count($arr_email);
        
       // print($row['EmailsNotificaNuovaMediazione']);
        
        //print($n_email);
        
        if ($n_email==0)
        $mail->AddAddress(trim($odc_email_notifica_admin));
        else
        {
            $n=0;
            while($n<$n_email)
            {
            //    print($arr_email[$n]);
                $mail->AddAddress(trim($arr_email[$n]));
                $n++;
                
            }
            
            
            
        }
        
        
        
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


public function SendMailToOdcPromoterIstanzaWeb($CodiceMediazione,$user)
{
    
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_notifica_admin=$row['EmailsNotificaNuovaMediazione'];
        $dt=new DT();
        if ($odc_email_notifica_admin)
        {
            
            $mail= new Email; // create the mail
            $mail->SetFrom($odc_fromMail, $odc_fromName);     
            
         $operatore=$user->Cognome." ".$user->Nome;
        $sede=new Sede();
        $oggetto_mediazione=$this->MediazioneDatiGenerali['Oggetto'];
        
        $promoterid=$this->MediazioneDatiGenerali['PromoterId'];
        $promoter=new Promoter($promoterid);
        $promoter->conn=$this->conn;
        $promoter->inizializzaDatiGenerali();
        $anagraficapromoter=$promoter->PromoterDatiGenerali['CognomeRagioneSociale']." ".$promoter->PromoterDatiGenerali['Nome'];
        $emailpromoter=$promoter->PromoterDatiGenerali['Email'];
                
        
        
        
        $data_ins_mediazione=$dt->format($this->MediazioneDatiGenerali['DataIns'],"Y-m-d H:i:s","d/m/Y H:i:s");
        $sede->conn=$this->conn;
        $sede->inizializza($this->MediazioneDatiGenerali['SedeIns']);
        $codice_sede=$sede->Comune;
        $appname=Config::$application_name;
        $message= "Gentile $anagraficapromoter ,\n";
        $message.="$odc_nome La informa che l'inoltro della domanda di mediazione presentata in data $data_ins_mediazione e' andato a buon fine. \n";
        $message.="Il codice temporaneo attribuito e' $CodiceMediazione, Le ricordiamo che siamo in attesa di ricevere il pagamento per poter assegnare alla mediazione il codice protocollo definitivo. \n";
        $message.="Le ricordiamo altresi' di inviare tutta la documentazione utile per la pratica all'indirizzo email $odc_email_risposta indicando nell'oggetto il numero di pratica di riferimento. \n";
         
        $message.="\n Il Coordinatore";
         $mail->Subject = 'Protocollo temporaneo n. '.$CodiceMediazione;
         
         $arr_email=explode(";", $odc_email_notifica_admin);
        //$arr_email=implode($odc_email_notifica_admin,";");
         
        $mail->AddAddress(trim($emailpromoter));
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


public function SendMailNotificaPresentazioneWeb($CodiceMediazione,$user)
{
    
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_risposta=$row['Email'];
        $dt=new DT();
        $mail= new Email; // create the mail
        $mail->SetFrom($odc_fromMail, $odc_fromName);     
            
        $operatore=$user->Cognome." ".$user->Nome;
        $sede=new Sede();
        $oggetto_mediazione=$this->MediazioneDatiGenerali['Oggetto'];
        $emailpernotifica=$this->MediazioneDatiGenerali['EmailAvvenutaPresentazione'];
        $data_ins_mediazione=$dt->format($this->MediazioneDatiGenerali['DataIns'],"Y-m-d H:i:s","d/m/Y H:i:s");
        $sede->conn=$this->conn;
        $sede->inizializza($this->MediazioneDatiGenerali['SedeIns']);
        $codice_sede=$sede->Comune;
        $appname=Config::$application_name;
        
         $importo=number_format($this->MediazioneDatiGenerali['ValoreRichiestoTot'], 2, ',' , '.');
      
        
        $message= "Gentile Cliente ,\n";
        $message.="$odc_nome La informa che l'inoltro della domanda di mediazione presentata in data $data_ins_mediazione, ";
        $message.="avente ad oggetto ".$this->MediazioneDatiGenerali['Oggetto']." il cui valore e' pari ad euro ".$importo.", e' andata a buon fine \n\n";
        $message.="Il codice temporaneo attribuito e' $CodiceMediazione, Le ricordiamo che siamo in attesa di ricevere il pagamento per poter assegnare alla mediazione il codice protocollo definitivo. \n";
      
        
        $message.="\n Il Coordinatore";
        
        
        
        
        $mail->Subject = 'Presentazione istanza: codice temporano n. '.$CodiceMediazione;
         
       
         
        $mail->AddAddress(trim($emailpernotifica));
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
            
            
       
    
    
    return $res;
    
}


public function SendMailToOdcPromoterIstanzaWebDef($CodiceMediazione,$user)
{
    
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_notifica_admin=$row['EmailsNotificaNuovaMediazione'];
        $dt=new DT();
        if ($odc_email_notifica_admin)
        {
            
            $mail= new Email; // create the mail
            $mail->SetFrom($odc_fromMail, $odc_fromName);     
            
        $operatore=$user->Cognome." ".$user->Nome;
        $sede=new Sede();
        $oggetto_mediazione=$this->MediazioneDatiGenerali['Oggetto'];
        
        $promoterid=$this->MediazioneDatiGenerali['PromoterId'];
        $promoter=new Promoter($promoterid);
        $promoter->conn=$this->conn;
        $promoter->inizializzaDatiGenerali();
        $anagraficapromoter=$promoter->PromoterDatiGenerali['CognomeRagioneSociale']." ".$promoter->PromoterDatiGenerali['Nome'];
        $emailpromoter=$promoter->PromoterDatiGenerali['Email'];
                
        
        
        
        $data_ins_mediazione=$dt->format($this->MediazioneDatiGenerali['DataIns'],"Y-m-d H:i:s","d/m/Y H:i:s");
        $sede->conn=$this->conn;
        $sede->inizializza($this->MediazioneDatiGenerali['SedeIns']);
        $codice_sede=$sede->Comune;
        $appname=Config::$application_name;
        $message= "Gentile $anagraficapromoter ,\n";
        $message.="$odc_nome La informa che e' stato attribuito il protocollo definitivo numero $CodiceMediazione al procedimento di mediazione presentato in data $data_ins_mediazione avente ad oggetto $oggetto_mediazione. \n";
        $message.="Attraverso le credenziali in suo possesso potra' monitorare la mediazione e seguirne il procedimento. \n";
        
        $message.="\n Il Coordinatore";
         $mail->Subject = 'Protocollo definitivo n. '.$CodiceMediazione;
         
         $arr_email=explode(";", $odc_email_notifica_admin);
        //$arr_email=implode($odc_email_notifica_admin,";");
         
        $mail->AddAddress(trim($emailpromoter));
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



public function SendMailArchiviazioneFascicolo($CodiceMediazione,$user)
{
    
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_notifica_admin=$row['EmailsNotificaNuovaMediazione'];
        $dt=new DT();
        $mail= new Email; // create the mail
        $mail->SetFrom($odc_fromMail, $odc_fromName);     
            $CodiceMediazione=$this->MediazioneDatiGenerali['Codice'];
        $operatore=$user->Cognome." ".$user->Nome;
        $sede=new Sede();
        $oggetto_mediazione=$this->MediazioneDatiGenerali['Oggetto'];
        $data_ins_mediazione=$dt->format($this->MediazioneDatiGenerali['DataIns'],"Y-m-d H:i:s","d/m/Y H:i:s");
        $sede->conn=$this->conn;
        $sede->inizializza($this->MediazioneDatiGenerali['SedeIns']);
        $codice_sede=$sede->Comune;
        $responsabile_sede_email=$sede->Email;
        $message= "Gentile responsabile della sede di  $sede->Comune - $sede->Indirizzo ,\n";
        $message.="La informiamo che la pratica n. $CodiceMediazione  e' stata archiviata correttamente. \n";
        $message.="\n Sistema automatico di notifica ".Config::$application_name." per l'Organismo $Odc";
        $mail->Subject = 'Archiviazione fascicolo - protocollo '.$CodiceMediazione;
         
        if ($responsabile_sede_email)
            {
            $mail->AddAddress(trim($responsabile_sede_email));
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

public function SendMailToODCPerApprovazione ($user)
    {
        
        
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        
        $mail= new Email; // create the mail
        $mail->SetFrom($odc_fromMail, $odc_fromName);        
      
       
       
        
        $operatore=$user->Cognome." ".$user->Nome;
        $sede=new Sede();
        $oggetto_mediazione=$this->MediazioneDatiGenerali['Oggetto'];
        $data_ins_mediazione=$this->MediazioneDatiGenerali['DataIns'];
        $sede->conn=$this->conn;
        $sede->inizializza($this->MediazioneDatiGenerali['SedeIns']);
        $codice_sede=$sede->CodiceSede;
        
        $message= "Gentile responsabile dell'Organismo $odc_nome,\n";
        $message.="e' richiesta l'approvazione del procedimento di mediazione \n";
        $message.="creato dall'operatore $operatore \n";
        $message.="in data $data_ins_mediazione presso la sede $codice_sede \n";
        $message.="avente come oggetto: $oggetto_mediazione. \n";
        
        $message.="\n\nGrazie";
         $mail->Subject = 'Richiesta approvazione procedimento di mediazione '.$operatore." - ".$codice_sede;
        $mail->AddAddress(trim($odc_email_approvazione));
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
    
      
  public function SendMailNominaMediatore ($user)
    {
        
        
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_mediatore=$row['EmailsNotificaAlMediatore'];
        $odc_email_risposta=$row['Email'];
        
        
        if ($odc_email_mediatore==1)    
        {
        
        $dt=new DT();
        
        $sql = "SELECT * From ViewMediazioneProssimoIncontro_1 WHERE MediazioneId=$this->MediazioneId";        
        $row = $this->conn->query_first($sql);
        $Mediatore=$row['CognomeRagioneSociale']." ".$row['Nome'];
        $Uditore="";
        if ($row['CognomeUd'])
        $Uditore=$row['CognomeUd']." ".$row['NomeUd'];
        $Parte=$row['Parte'];
        $Controparte=$row['Controparte'];
        $MediatoreId=$row['MediatoreId'];
        
        $DataIncontro=$dt->format($row['data'],"Y-m-d H:i:s","d/m/Y");
        
        
        $minutif="0";
        $oraf="0";
        $minuti=$dt->getMinute($row['data'],"Y-m-d H:i:s");
        $ora=$dt->getHour($row['data'],"Y-m-d H:i:s");
         
        if (((int)$minuti)<10)
            $minutif="0".$minuti;
        else
             $minutif=$minuti;          
        
        
        if (((int)$ora)<10)
            $oraf="0".$ora;
        else
             $oraf=$ora;       
        
        $OraIncontro=$oraf.":".$minutif;
        $Indirizzo=$row['Indirizzo'];
        $Comune=$row['Comune']." (".$row['sigla'].")";
        
        $EmailMediatore=$row['Email'];
         
        
        // se esiste l'email 
        
        if (!empty($EmailMediatore))
        {
        
         $mail= new Email; // create the mail
         $mail->SetFrom($odc_fromMail, $odc_fromName);   
        
        
        
        
       $importo=number_format($this->MediazioneDatiGenerali['ValoreRichiestoTot'], 2, ',' , '.');
        
        
        $message= "Gentile ".$Mediatore.",\n".$odc_nome." La informa che e' stato nominato mediatore per per la pratica Prot.n. ".$this->MediazioneDatiGenerali['Codice']." vertente tra ".$Parte." / ".$Controparte." ,\n";
        $message.="avente ad oggetto ".$this->MediazioneDatiGenerali['Oggetto']." il cui valore e' pari ad euro ".$importo.". \n\n";
        $message.="L'incontro si terra' in data ".$DataIncontro." alle ore ".$OraIncontro." presso la sede di ".$odc_nome."  di ".$Comune." in ".$Indirizzo.". \n";
        if ($Uditore!="")
        $message.="Il mediatore nominato, ai fini del tirocinio assistito di cui all'art. 4 comma 3 D. M. 180/2010 e' il dott. ".$Uditore." \n";
        
        
        
     if ($user->CompensoMediatoreObbligatorio==1)
     {
       $s="select * from ViewMediatoreMediazioneCompenso where MediazioneId=$this->MediazioneId and MediatoreId=$MediatoreId";
      $ArrCompensi=$this->conn->fetch_array($s);
     $ArrCompensiSize=sizeof($ArrCompensi);
      
     
      $i=0;
       $message.="\nDi seguito il compenso definito dal gestore:\n";
      while ($i<$ArrCompensiSize)
      {
          $CompensoDescr=$ArrCompensi[$i]['CompensoRif'];
          $Compenso1=$ArrCompensi[$i]['Compenso'];
          $Compenso=number_format($Compenso1, 2, ',' , '.');
       
          
          
        
         
          
          $message.="\n - ".$CompensoDescr.": ".$Compenso." euro \n";
          
          
          
      $i++;
      }
      if ($i==0)
          $message.="NESSUN COMPENSO DEFINITO:\n"; 
     }   
       
        
        $message.="\nE' gradita una conferma dell'accettazione dell'incarico, a mezzo pec, all'indirizzo ".$odc_email_risposta." entro 24 ore dalla ricezione della stessa . \n";
        $message.="Cordiali saluti. \n";
        
        $message.="\n\nIl Coordinatore";
        
        $message.="\n\n\n Sistema automatico di notifica ".Config::$application_name;
        
        $mail->Subject = 'Nomina di mediatore '.$this->MediazioneDatiGenerali['Codice'];
        $mail->AddAddress(trim($EmailMediatore));
       
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
        else
            $res= "email non settata";
    }
        
     //   print($res);
    }
    
    public function SendMailNominaCoMediatore ($user)
    {
        
        
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_mediatore=$row['EmailsNotificaAlMediatore'];
        $odc_email_risposta=$row['Email'];
        
        
        if ($odc_email_mediatore==1)    
        {
        
        $dt=new DT();
        
        $sql = "SELECT * From ViewMediazioneProssimoIncontro_1 WHERE MediazioneId=$this->MediazioneId";        
        $row = $this->conn->query_first($sql);
        
        $Mediatore=$row['CognomeCo']." ".$row['NomeCo'];
        $Uditore="";
        $Mediatore2=$row['CognomeRagioneSociale']." ".$row['Nome'];
        $Parte=$row['Parte'];
        $Controparte=$row['Controparte'];
        $MediatoreId=$row['MediatoreId'];
        
        $DataIncontro=$dt->format($row['data'],"Y-m-d H:i:s","d/m/Y");
        
        
        $minutif="0";
        $oraf="0";
        $minuti=$dt->getMinute($row['data'],"Y-m-d H:i:s");
        $ora=$dt->getHour($row['data'],"Y-m-d H:i:s");
         
        if (((int)$minuti)<10)
            $minutif="0".$minuti;
        else
             $minutif=$minuti;          
        
        
        if (((int)$ora)<10)
            $oraf="0".$ora;
        else
             $oraf=$ora;       
        
        $OraIncontro=$oraf.":".$minutif;
        $Indirizzo=$row['Indirizzo'];
        $Comune=$row['Comune']." (".$row['sigla'].")";
        
        $EmailMediatore=$row['Email1'];
         
        
        // se esiste l'email 
        
        if (!empty($EmailMediatore))
        {
        
         $mail= new Email; // create the mail
         $mail->SetFrom($odc_fromMail, $odc_fromName);   
        
        
        
        
       $importo=number_format($this->MediazioneDatiGenerali['ValoreRichiestoTot'], 2, ',' , '.');
        
        
        $message= "Gentile ".$Mediatore.",\n".$odc_nome." La informa che e' stato nominato co-mediatore per per la pratica Prot.n. ".$this->MediazioneDatiGenerali['Codice']." vertente tra ".$Parte." / ".$Controparte." ,\n";
        $message.="avente ad oggetto ".$this->MediazioneDatiGenerali['Oggetto']." il cui valore e' pari ad euro ".$importo.". \n\n";
        $message.="L'incontro si terra' in data ".$DataIncontro." alle ore ".$OraIncontro." presso la sede di ".$odc_nome."  di ".$Comune." in ".$Indirizzo.". \n";
        $message.="Il mediatore nominato e' il dott. ".$Mediatore2." \n";
        
        
        
     if ($user->CompensoMediatoreObbligatorio==1)
     {
       $s="select * from ViewMediatoreMediazioneCompenso where MediazioneId=$this->MediazioneId and MediatoreId=$MediatoreId";
      $ArrCompensi=$this->conn->fetch_array($s);
     $ArrCompensiSize=sizeof($ArrCompensi);
      
     
      $i=0;
       $message.="\nDi seguito il compenso definito dal gestore: \n";
      while ($i<$ArrCompensiSize)
      {
            $CompensoDescr=$ArrCompensi[$i]['CompensoRif'];
          $Compenso1=$ArrCompensi[$i]['Compenso'];
          $Compenso=number_format($Compenso1, 2, ',' , '.');
       
          
          
        
         
          
          $message.="\n - ".$CompensoDescr.": ".$Compenso." euro \n";
          
          
          
      $i++;
      }
      if ($i==0)
          $message.="NESSUN COMPENSO DEFINITO:\n"; 
     }   
       
        
        $message.="\nE' gradita una conferma dell'accettazione dell'incarico, a mezzo pec, all'indirizzo ".$odc_email_risposta." entro 24 ore dalla ricezione della stessa . \n";
        $message.="Cordiali saluti. \n";
        
        $message.="\n\nIl Coordinatore";
        
        $message.="\n\n\n Sistema automatico di notifica ".Config::$application_name;
        
        $mail->Subject = 'Nomina di co-mediatore '.$this->MediazioneDatiGenerali['Codice'];
        $mail->AddAddress(trim($EmailMediatore));
       
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
        else
            $res= "email non settata";
    }
        
     //   print($res);
    }
    
    
 

  public function SendMailNominaUditore ($user)
    {
        
        
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
      
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_mediatore=$row['EmailsNotificaAlMediatore'];
        $odc_email_risposta=$row['Email'];
        
        
        if ($odc_email_mediatore==1)    
        {
        
        $dt=new DT();
        
        $sql = "SELECT * From ViewMediazioneProssimoIncontro_1 WHERE MediazioneId=$this->MediazioneId";        
         
        $row = $this->conn->query_first($sql);
        $Mediatore=$row['CognomeUd2']." ".$row['NomeUd2'];
        $Mediatore2=$row['CognomeRagioneSociale']." ".$row['Nome'];
        $Parte=$row['Parte'];
        $Controparte=$row['Controparte'];
        
        $DataIncontro=$dt->format($row['data'],"Y-m-d H:i:s","d/m/Y");
        
        
        $minutif="0";
        $oraf="0";
        $minuti=$dt->getMinute($row['data'],"Y-m-d H:i:s");
        $ora=$dt->getHour($row['data'],"Y-m-d H:i:s");
         
        if (((int)$minuti)<10)
            $minutif="0".$minuti;
        else
             $minutif=$minuti;          
        
        
        if (((int)$ora)<10)
            $oraf="0".$ora;
        else
             $oraf=$ora;       
        
        $OraIncontro=$oraf.":".$minutif;
        $Indirizzo=$row['Indirizzo'];
        $Comune=$row['Comune']." (".$row['sigla'].")";
        
        $EmailMediatore=$row['EmailUd2'];
         
        
        // se esiste l'email 
       // echo ("qui".$EmailMediatore);
       
        if (!empty($EmailMediatore))
        {
        
         $mail= new Email; // create the mail
         $mail->SetFrom($odc_fromMail, $odc_fromName);   
        
        
        
        
       $importo=number_format($this->MediazioneDatiGenerali['ValoreRichiestoTot'], 2, ',' , '.');
        
        
        $message= "Gentile ".$Mediatore.",\n".$odc_nome." La informa che e' stato nominato uditore,ai fini del tirocinio assistito di cui all'art. 4 comma 3 D.M. 180/2010, per per la pratica Prot.n. ".$this->MediazioneDatiGenerali['Codice']." vertente tra ".$Parte." / ".$Controparte." ,\n";
        $message.="avente ad oggetto ".$this->MediazioneDatiGenerali['Oggetto']." il cui valore e' pari ad euro ".$importo.". \n\n";
        $message.="L'incontro incontro si terra' in data ".$DataIncontro." alle ore ".$OraIncontro." presso la sede di ".$odc_nome."  di ".$Comune." in ".$Indirizzo.". \n";
        $message.="Il mediatore nominato e' il dott. ".$Mediatore2." \n";
        
        
        $message.="E' gradita una conferma dell'accettazione dell'incarico, a mezzo pec, all'indirizzo ".$odc_email_risposta." entro 24 ore dalla ricezione della stessa . \n";
        $message.="Cordiali saluti. \n";
        
        $message.="\n\nIl Coordinatore";
        
        $message.="\n\n\n Sistema automatico di notifica ".Config::$application_name;
        
        $mail->Subject = 'Nomina di uditore '.$this->MediazioneDatiGenerali['Codice'];
        $mail->AddAddress(trim($EmailMediatore));
       
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
        else
            $res= "email non settata";
    }
        
     //   print($res);
    }
    
    public function GetNumeroControparti()
        {
         $sql = "SELECT NumeroControparti From MediazioniNumeroControparti WHERE MediazioneId=$this->MediazioneId";      
         $row = $this->conn->query_first($sql);
         $NumeroControparti=$row['NumeroControparti'];
         if ($NumeroControparti>0)
             return $NumeroControparti;
         else
             return 0;
         }    
            
            
     public function SendMailNominaUditore2 ($user)
    {
        
        
        $sql = "SELECT * From Odc WHERE OdcId=$user->OdcId";        
      
        $row = $this->conn->query_first($sql);
        $odc_nome=$row['Odc'];
        $odc_fromMail=$row['EmailSmtp'];
        $odc_fromName=$row['NomeEmailSmtp'];
        $odc_serverSmtp=$row['ServerSmtp'];
        $odc_portaSmtp=$row['PortaSmtp'];
        $odc_username=$row['UserSmtp'];
        $odc_password=$row['PwdSmtp'];
        $odc_email_approvazione=$row['EmailPerApprovazione'];
        $odc_email_mediatore=$row['EmailsNotificaAlMediatore'];
        $odc_email_risposta=$row['Email'];
        
        
        if ($odc_email_mediatore==1)    
        {
        
        $dt=new DT();
        
        $sql = "SELECT * From ViewMediazioneProssimoIncontro_1 WHERE MediazioneId=$this->MediazioneId";        
         
        $row = $this->conn->query_first($sql);
        $Mediatore=$row['CognomeUd']." ".$row['NomeUd'];
        $Mediatore2=$row['CognomeRagioneSociale']." ".$row['Nome'];
        $Parte=$row['Parte'];
        $Controparte=$row['Controparte'];
        
        $DataIncontro=$dt->format($row['data'],"Y-m-d H:i:s","d/m/Y");
        
        
        $minutif="0";
        $oraf="0";
        $minuti=$dt->getMinute($row['data'],"Y-m-d H:i:s");
        $ora=$dt->getHour($row['data'],"Y-m-d H:i:s");
         
        if (((int)$minuti)<10)
            $minutif="0".$minuti;
        else
             $minutif=$minuti;          
        
        
        if (((int)$ora)<10)
            $oraf="0".$ora;
        else
             $oraf=$ora;       
        
        $OraIncontro=$oraf.":".$minutif;
        $Indirizzo=$row['Indirizzo'];
        $Comune=$row['Comune']." (".$row['sigla'].")";
        
        $EmailMediatore=$row['Email2'];
         
        
        // se esiste l'email 
       // echo ("qui".$EmailMediatore);
       
        if (!empty($EmailMediatore))
        {
        
         $mail= new Email; // create the mail
         $mail->SetFrom($odc_fromMail, $odc_fromName);   
        
        
        
        
       $importo=number_format($this->MediazioneDatiGenerali['ValoreRichiestoTot'], 2, ',' , '.');
        
        
        $message= "Gentile ".$Mediatore.",\n".$odc_nome." La informa che e' stato nominato uditore,ai fini del tirocinio assistito di cui all'art. 4 comma 3 D.M. 180/2010, per per la pratica Prot.n. ".$this->MediazioneDatiGenerali['Codice']." vertente tra ".$Parte." / ".$Controparte." ,\n";
        $message.="avente ad oggetto ".$this->MediazioneDatiGenerali['Oggetto']." il cui valore e' pari ad euro ".$importo.". \n\n";
        $message.="L'incontro incontro si terra' in data ".$DataIncontro." alle ore ".$OraIncontro." presso la sede di ".$odc_nome."  di ".$Comune." in ".$Indirizzo.". \n";
        $message.="Il mediatore nominato e' il dott. ".$Mediatore2." \n";
        
        
        $message.="E' gradita una conferma dell'accettazione dell'incarico, a mezzo pec, all'indirizzo ".$odc_email_risposta." entro 24 ore dalla ricezione della stessa . \n";
        $message.="Cordiali saluti. \n";
        
        $message.="\n\nIl Coordinatore";
        
        $message.="\n\n\n Sistema automatico di notifica ".Config::$application_name;
        
        $mail->Subject = 'Nomina di uditore '.$this->MediazioneDatiGenerali['Codice'];
        $mail->AddAddress(trim($EmailMediatore));
       
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
        else
            $res= "email non settata";
    }
        
     //   print($res);
    }


function getStatoStep($step)
{
     $db=$this->conn;
    $MediazioneId=$this->MediazioneId;
    switch($step) {

                case "1":
                    if ( ($this->MediazioneDatiGenerali['MediazioneStatoId']==6) and ( (!$this->MediazioneDatiGenerali['DataArchiviazioneIstanza']) or ($this->MediazioneDatiGenerali['DataArchiviazioneIstanza']=='0000-00-00')))
                    return "attesa";
                    else
                        return "";
                    
                    
                    
                    
                    
                case "2":
                // verifico il domicilio eletto.    
                    $sql = "SELECT DomiciliMancanti from CheckDomicilioPerMediazione where MediazioneId=$MediazioneId and MediazioneSoggettoParteTipoId=1";
                    $row = $db->query_first($sql);
                    if ($row['DomiciliMancanti']>0)
                    return "attesa";
                    else
                    return "";    
                    
                    
                  
                    
                    
                    
                case "3":
                // verifico se tutte le controparti hanno accettato
                    $attesa="";
                    // verifico il domicilio eletto.    
                    $sql = "SELECT DomiciliMancanti from CheckDomicilioPerMediazione where MediazioneId=$MediazioneId and MediazioneSoggettoParteTipoId=2";
                    $row = $db->query_first($sql);
                    if ($row['DomiciliMancanti']>0)
                    {
                    return "attesa";
                    }
                   
                    
                    $sql = "SELECT Count(Mediazioneid) as NumeroStati from MediazioneCheckAttesaControparte where MediazioneId=$MediazioneId";
                  // echo($sql);
                    $row = $db->query_first($sql);
                    if ($row['NumeroStati']==0)
                    return "";
                    else
                    return "attesa";    
                        
                    

                

                break;
                
                
                // verifica dei pagamenti
                 case "6":
                   
                    $sql = "SELECT CheckPagamento from MediazioneCheckPagamenti where CheckPagamento=1 and MediazioneId=$MediazioneId";
                  
                    if (($MediazioneId==18) and ($this->MediazioneDatiGenerali['MediazioneStatoId']==6))
                    
                         return "";
                    else
                    {
                    // verifico se tutte le controparti hanno accettato
                   
                    $row = $db->query_first($sql);
                    if (!empty($row['CheckPagamento']))
                    return "";
                    else
                    return "attesa";   
                    }
                    
                
                
                 case "7":
                     $prossimoIncontro=$this->getProssimoIncontro();
					//print_r($prossimoIncontro);
                      if (($prossimoIncontro['MediazioneAccettazioneId']<>1) or (($prossimoIncontro['MediazioneIncontroEsitoId']<>1)))
			 {
                            return "attesa";
			}
                      else
                      return "";    
					 
                break;
                
                
                case "9":
                   
                          
                    
                    $sql = "SELECT MediazioneId from MediazioneCheckObbligoVerbale where MediazioneId=$MediazioneId";
                    $row = $db->query_first($sql);
                    if(!empty($row['MediazioneId']))
                    return "attesa";
                    else
                    return "";   
                        
                    

                
                break;
                
                case "10":
                   
                          
                    if ( ($this->MediazioneDatiGenerali['mediazioneverbaleEsitoId']) and ( (!$this->MediazioneDatiGenerali['ArchiviazioneFascicolo'])))
                     return "attesa";
                    else
                        return "";
                    
                  
                        
                    

                
                break;
                
                                
                }
                                    
                                    
    
    
    
    
    
}
    
}
?>