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
class Prenotazione {
    
  
    public $Id;
    public $conn;
    public $DatiGenerali;
    public $DatiGeneraliPercorso;
    public $DatiGeneraliCarico;
    


function __construct($Id=null) {
    $this->Id = $Id;
}

public function TitoliEmessiYesNo()
{
    global $user;
        $db=$this->conn;
        $PrenotazioneId=$this->Id;
        $sql="select * from RT_PrenotazioneTitolo where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Stato=1 and Cancella=0";
        $row = $db->query_first($sql);
        
        if (!empty($row['PrenotazioneId']))
            return true;
        else
            return false;
    
}


public function CheckPrivilegiPrenotazione()
{
    // verifico se posso anche solo pernotare
    global $user;
}



public function CheckStatoPrenotazione()
 {
    // verifico se posso anche solo prenotare
    global $user;
 }

public function GetDaConfermare($arr_tratte)
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        
  //      print_r($arr_tratte);
       $data=$arr_tratte;
       $valori_tratte=0;
        foreach ($data as $chiave => $valore)
         {
          
           
            $valori_tratte.=",".$valore['TrattaId'];
         }
        
        
        
        $sql="select * from RT_Tratta where TrattaId IN ($valori_tratte) and Stato=1 and Cancella=0 and DaConfermare=1";
        $row = $db->query_first($sql);
    
    if (!empty($row['TrattaId']))
            return 1;
        else
            return 0;
    

}   


public function CreateDettaglioPrenotazione ($CorsaId,$Tragitto,$TipoViaggio,$stato)
{
     global $user;
     $db=$this->conn;
     $PrenotazioneId=$this->Id;
     $storico=new StoricoOperazioni();
$storico->conn=$db;
      $dt=new DT();
      
     
      
      $sql="select * from RT_AppAliquota where OdcIdRef=$user->OdcId and ValidaDal<=Now() and ValidaAl>=Now() and Stato=1 and Cancella=0";
              
                  $row22=$db->query_first($sql);
                  $aliquota_b=0;
                  $aliquota_p=0;
                  if ($row22['AliquotaId']>0)
                   {
                      
                       $aliquota_b=$row22['AliquotaBiglietto'];
                       $aliquota_p=$row22['AliquotaProvvigioni'];
                      
                  }   
               
    
    $sql="select * from RT_PrenotazionePercorso where OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId and Stato=1 and Cancella=0 and CorsaId=$CorsaId";
   
   
    $ArrPercorso = $db->fetch_array($sql); 
   
    
     $sql="SELECT
RT_PrenotazioneTratta.PrenotazioneTrattaId,
RT_PrenotazioneTratta.PrenotazioneId,
RT_PrenotazioneTratta.TrattaId,
RT_PrenotazioneTratta.TrattaNome,
RT_PrenotazioneTratta.TrattaPeso,
RT_PrenotazioneTratta.TrattaNodo,
RT_PrenotazioneTratta.TrattaNote,
RT_PrenotazioneTratta.TrattaDirezione,
RT_PrenotazioneTratta.CorsaId,
RT_PrenotazioneTratta.DataIns,
RT_PrenotazioneTratta.OpeIns,
RT_PrenotazioneTratta.SedeIns,
RT_PrenotazioneTratta.IpIns,
RT_PrenotazioneTratta.DataAgg,
RT_PrenotazioneTratta.OpeAgg,
RT_PrenotazioneTratta.SedeAgg,
RT_PrenotazioneTratta.IpAgg,
RT_PrenotazioneTratta.Stato,
RT_PrenotazioneTratta.Cancella,
RT_PrenotazioneTratta.UpdateCount,
RT_PrenotazioneTratta.OdcIdRef,
RT_PrenotazioneTratta.GestoreIdRef,
RT_Tratta.TrattaNome,
RT_Tratta.LineaId,
RT_Tratta.TrattaPeso,
RT_Tratta.NodoPeso,
RT_Tratta.MezzoId
FROM
RT_PrenotazioneTratta
INNER JOIN RT_Tratta ON RT_PrenotazioneTratta.TrattaId = RT_Tratta.TrattaId
where RT_PrenotazioneTratta.CorsaId=$CorsaId and RT_PrenotazioneTratta.OdcIdRef=$user->OdcId and RT_PrenotazioneTratta.PrenotazioneId=$PrenotazioneId";
    $ArrTratte = $db->fetch_array($sql); 
      
      
        $sql="select TipologiaBigliettoId,TipologiaBiglietto,AumentoPax,RiduzionePax,NumeroPax,PrezzoTotalePax from RT_PrenotazioneBiglietto where OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId and Stato=1 and Cancella=0";
//echo($sql);
$ArrObjectP = $db->fetch_array($sql);
$tipologieBigliettiCount=sizeof($ArrObjectP);
$tpb=0;

while ($tpb<$tipologieBigliettiCount)
{
    $TipologiaBigliettoId=$ArrObjectP[$tpb]['TipologiaBigliettoId'];
    $TipologiaBiglietto=$ArrObjectP[$tpb]['TipologiaBiglietto'];
    $NumeroPax=$ArrObjectP[$tpb]['NumeroPax'];
    $PrezzoTotalePax=$ArrObjectP[$tpb]['PrezzoTotalePax'];
    $AumentoPax=$ArrObjectP[$tpb]['AumentoPax'];
    $RiduzionePax=$ArrObjectP[$tpb]['RiduzionePax'];
   
    $nper=0;
    $numeroPercorsi=sizeof($ArrPercorso);
    while($nper<$numeroPercorsi)
    {
     
       
        $dataToIns['PrenotazioneId']=$PrenotazioneId;
        $dataToIns['Tragitto']=$Tragitto; 
        $dataToIns['PercorsoNome']=$ArrPercorso[$nper]['PercorsoNome']; 
        $dataToIns['LineaNome']=$ArrPercorso[$nper]['LineaNome']; 
        $dataToIns['CorsaNome']=$ArrPercorso[$nper]['CorsaNome']; 
        
       
        
        $ntr=0;
        $numeroTratte=sizeof($ArrTratte);
         while($ntr<$numeroTratte)
         {
             $LineaId=$ArrTratte[$ntr]['LineaId'];
             $dataToIns['LineaId']=$LineaId; 
             $trattaId=$ArrTratte[$ntr]['TrattaId']; 
             
              // percentuale agenzia
               $sql="select PercorsoId from RT_Linea where OdcIdRef=$user->OdcId and LineaId=$LineaId";
               $row=$db->query_first($sql);
               $PercorsoId=$row['PercorsoId'];
                  $dataToIns['PercorsoId']=$PercorsoId;
                  $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
                
                  $row=$db->query_first($sql);
                  $percentuale_a=0;
                  $fisso_a=0;
                  if ($row['GestoreConvenzioneId']>0)
                   {
                      
                      $percentuale_a=$row['Percentuale'];
                      $fisso_a=$row['Fisso'];
                      
                  }   
                  $dataToIns['AliquotaBiglietto']=$aliquota_b;
                  $dataToIns['AliquotaProvvigione']=$aliquota_p;
             
             $MezzoId=$ArrTratte[$ntr]['MezzoId']==1;
             
             $Mezzo="Bus";
             
             if ($MezzoId==1)
             $Mezzo="Navetta";
             
             $dataToIns['TipoServizio']=$Mezzo; 
             $dataToIns['TipologiaBiglietto']=$TipologiaBiglietto; 
             $dataToIns['CorsaId']=$ArrTratte[$ntr]['CorsaId']; 
             $dataToIns['TipoViaggio']=$TipoViaggio;
             
              $dataToIns['DataInizioItinerario']=date("Y-m-d", strtotime($ArrPercorso[$nper]['CorsaDataPartenza']));
                 $dataToIns['CorsaInizioItinerario']=$CorsaId; 
             
             if ($numeroTratte==1)
             {
                  $dataToIns['ComunePartenza']=$ArrPercorso[$nper]['ComuneSalita']; 
                  $dataToIns['ComuneArrivo']=$ArrPercorso[$nper]['ComuneDiscesa']; 
                  $dataToIns['FermataPartenza']=$ArrPercorso[$nper]['FermataSalita']; 
                  $dataToIns['FermataArrivo']=$ArrPercorso[$nper]['FermataDiscesa'];
                  
                  $fsalitaid=$ArrPercorso[$nper]['FermataSalitaId']; 
                  $fdiscesaid=$ArrPercorso[$nper]['FermataDiscesaId'];
                  
                  $sql="select GiorniAggiuntivi,FermataId from RT_Orario where CorsaId=$CorsaId and FermataId=$fsalitaid";
                  
                  $row22=$db->query_first($sql);
                  $DataPartenza1=Date('Y-m-d');
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     $dt=new DT($ArrPercorso[$nper]['DataOraSalita'],'Y-m-d');
                     $dt->addDays(0);
                     $DataPartenza1=$dt->getDate('Y-m-d'); 
                  }
                  
                   $sql="select GiorniAggiuntivi,FermataId from RT_Orario where CorsaId=$CorsaId and FermataId=$fdiscesaid";
                   $row22=$db->query_first($sql);
                  $DataDiscesa1=Date('Y-m-d');
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     $dt=new DT($ArrPercorso[$nper]['DataOraDiscesa'],'Y-m-d');
                     $dt->addDays(0);
                     $DataDiscesa1=$dt->getDate('Y-m-d'); 
                  }
                  
                  $dataToIns['DataPartenza']=  $DataPartenza1;
                  $dataToIns['OrarioPartenza']=date("H:i:s", strtotime($ArrPercorso[$nper]['DataOraSalita']));
                  $dataToIns['DataArrivo']=  $DataDiscesa1;
                  $dataToIns['OrarioArrivo']=date("H:i:s", strtotime($ArrPercorso[$nper]['DataOraDiscesa']));
                  
                  
                  
             }
             elseif ($numeroTratte==2)
             {
                 if ($ntr==0)
                 {
                      $dataToIns['ComunePartenza']=$ArrPercorso[$nper]['ComuneSalita']; 
                      $dataToIns['FermataPartenza']=$ArrPercorso[$nper]['FermataSalita']; 
                      $dataToIns['DataPartenza']=  date("Y-m-d", strtotime($ArrPercorso[$nper]['DataOraSalita']));
                      $dataToIns['OrarioPartenza']=date("H:i:s", strtotime($ArrPercorso[$nper]['DataOraSalita']));
                      // determina l'ultima fermata di interscambio della tratta corrente
                     
                      $sq="SELECT
RT_Fermata.FermataNome,
RT_Fermata.IsInterscambio,
RT_Fermata.TrattaId,
RT_Fermata.FermataId,
Comune.Comune,
RT_Fermata.FermataPeso,
RT_Orario.Orario,
RT_Orario.GiorniAggiuntivi,
RT_Orario.CorsaId
FROM
RT_Fermata
INNER JOIN Comune ON RT_Fermata.ComuneId = Comune.ComuneId
INNER JOIN RT_Orario ON RT_Fermata.FermataId = RT_Orario.FermataId where RT_Fermata.TrattaId=$trattaId and CorsaId=$CorsaId  order by RT_Fermata.FermataPeso desc limit 1";
            
                 $row22=$db->query_first($sq);
                
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     $dt=new DT($dataToIns['DataPartenza'],'Y-m-d');
                     $dt->addDays($ggadd);
                     $DataArrivo=$dt->getDate('Y-m-d');
                     $dataToIns['ComuneArrivo']=$row22['Comune']; 
                     $dataToIns['FermataArrivo']=$row22['FermataNome'];
                     $dataToIns['DataArrivo']=  $DataArrivo;
                     $dataToIns['OrarioArrivo']=$row22['Orario'];
              
                   }               
                      
                      
                      
                 }
                  elseif ($ntr==1)
                 {
                     $dataToIns['ComuneArrivo']=$ArrPercorso[$nper]['ComuneDiscesa']; 
                     $dataToIns['FermataArrivo']=$ArrPercorso[$nper]['FermataDiscesa'];
                     $dataToIns['DataArrivo']=  date("Y-m-d", strtotime($ArrPercorso[$nper]['DataOraDiscesa']));
                     $dataToIns['OrarioArrivo']=date("H:i:s", strtotime($ArrPercorso[$nper]['DataOraDiscesa']));
                // determina la prima fermata di pickup della tratta corrente
                     
                     
                     $sq="SELECT
RT_Fermata.FermataNome,
RT_Fermata.IsInterscambio,
RT_Fermata.TrattaId,
RT_Fermata.FermataId,
Comune.Comune,
RT_Fermata.FermataPeso,
RT_Orario.Orario,
RT_Orario.GiorniAggiuntivi,
RT_Orario.CorsaId
FROM
RT_Fermata
INNER JOIN Comune ON RT_Fermata.ComuneId = Comune.ComuneId
INNER JOIN RT_Orario ON RT_Fermata.FermataId = RT_Orario.FermataId where RT_Fermata.TrattaId=$trattaId and  CorsaId=$CorsaId order by RT_Fermata.FermataPeso asc limit 1";
                      
                 $row22=$db->query_first($sq);
                
                  if ($row22['FermataId']>0)
                   {
                     $ggadd=$row22['GiorniAggiuntivi'];
                     $dt=new DT($dataToIns['DataPartenza'],'Y-m-d');
                     $dt->addDays($ggadd);
                     $DataArrivo=$dt->getDate('Y-m-d');
                     $dataToIns['ComunePartenza']=$row22['Comune']; 
                     $dataToIns['FermataPartenza']=$row22['FermataNome'];
                     $dataToIns['DataPartenza']=  $DataArrivo;
                     $dataToIns['OrarioPartenza']=$row22['Orario'];
              
                   }     
                
                  }
             }
             $sq="Select PrenotazioneTariffaId,ListinoPrezzo,ConRitorno from RT_PrenotazioneTariffa where  PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=$TipologiaBigliettoId and Stato=1 and Cancella=0 order by ListinoPrezzo desc";
              
                 if ($MezzoId==1)  
               $sq="Select PrenotazioneTariffaId,ListinoPrezzo,ConRitorno from RT_PrenotazioneTariffa where  PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=$TipologiaBigliettoId and Stato=1 and Cancella=0 order by ListinoPrezzo asc";
              
               $row22=$db->query_first($sq);
               $PrezzoBase=$row22['ListinoPrezzo'];
               $ConRitorno=$row22['ConRitorno']+1;
               $PrezzoBase=$PrezzoBase/$ConRitorno;
               $delta=0;
                 
               if ($numeroTratte==2)
               {
                     if ($Mezzo=='Navetta')
                     $delta=($AumentoPax-$RiduzionePax)/$NumeroPax;
               }
             //   echo($numeroTratte);
              else
             {
                   
                     if ($Mezzo=='Bus')
                     $delta=($AumentoPax-$RiduzionePax)/$NumeroPax;
                     
                    
               }
           
               if ($TipoViaggio!="Corsa Semplice")
               $delta=$delta/2;
                  
                 
                     $moltiplicatore=1; 
                
                  if ($stato==7)
                      $moltiplicatore=-1; 
               $PrezzoBase=($PrezzoBase+$delta)*$moltiplicatore;
                     $arr=$this->ImportiPrenotazione($PrezzoBase,$percentuale_a,$fisso_a,$aliquota_b,$aliquota_p);
                     $dataToIns['ImportoAgenzia']=$arr[0];      
                     $dataToIns['DaBonificare']=$arr[1];      
                     $dataToIns['DaFatturare']=$arr[2];      
                      $dataToIns['PercentualeAgenzia']=$percentuale_a;    
                    $dataToIns['FissoAgenzia']=$fisso_a;    
                 
                    
                  $dataToIns['Importo']=$PrezzoBase;     
               
             $cs=1;
               $dataToIns=$storico->operazioni_insert($dataToIns, $user);
            
               $sq="select PrenotazioneNumeroId from RT_PrenotazioneNumero where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=$TipologiaBigliettoId and Stato=1 and Cancella=0";
             
               $ArrPN = $db->fetch_array($sq);
                $SizeArrPN=sizeof($ArrPN);
                $cs=0;
                while ($cs<$SizeArrPN)
                {
                    $PrenotazioneNumero=$ArrPN[$cs]['PrenotazioneNumeroId'];
                    $dataToIns['PrenotazioneNumero']=$PrenotazioneNumero;
                  
                     $ins=$db->insert("RT_PrenotazioneDettaglio",$dataToIns);
                       $cs++;
             
                }
          
             
            
             $ntr++;
         }
        
        
        
        
        
        
        
        $nper++; 
    }
    
   
    
    // per ogni percorso
        // per ogni tratta
    
    
    $tpb++;
}






    
   return true; 
}
public function CreateDettaglioPrenotazioneOld ($CorsaId)
{
     global $user;
     $db=$this->conn;
     $PrenotazioneId=$this->Id;
     $storico=new StoricoOperazioni();
$storico->conn=$db;
      $dt=new DT();
     $sql="select * from RT_FoglioCaricoTitoliDiViaggio where OdcIdRef=$user->OdcId and CorsaId=$CorsaId  and PrenotazioneId=$PrenotazioneId";
//echo($sql);
$ArrObjectP = $db->fetch_array($sql);
 $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
            $dettaglio=null;
            
            $sql="select * from RT_AppAliquota where OdcIdRef=$user->OdcId and ValidaDal<=Now() and ValidaAl>=Now() and Stato=1 and Cancella=0";
              
                  $row22=$db->query_first($sql);
                  $aliquota_b=0;
                  $aliquota_p=0;
                  if ($row22['AliquotaId']>0)
                   {
                      
                       $aliquota_b=$row22['AliquotaBiglietto'];
                       $aliquota_p=$row22['AliquotaProvvigioni'];
                      
                  }   
               
            
          $DNAP='';
          $DNAA='';
          $DNRP='';
          $DNRA='';
          
          
               while ($np<$numeropasseggeri)
             {
                  $moltiplicatore=1; 
                  $PrenStatoId=$ArrObjectP[$np]['PrenStatoId'];
                  if ($PrenStatoId==7)
                      $moltiplicatore=-1; 
                  
                  
                  $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                  $Agenzia=$ArrObjectP[$np]['RagioneSociale'];
                  $Percorso=$ArrObjectP[$np]['PercorsoNome'];
                  $Salita=$ArrObjectP[$np]['ComuneSalita'];
                  $Discesa=$ArrObjectP[$np]['ComuneDiscesa'];
                  $TrattaIdSalita=$ArrObjectP[$np]['TrattaIdP'];
                  $TrattaIdDiscesa=$ArrObjectP[$np]['TrattaIdD'];
                  
                  
                  $SiglaSalita=$ArrObjectP[$np]['SiglaSalita'];
                  $SiglaDiscesa=$ArrObjectP[$np]['SiglaDiscesa'];
                  $ClienteNome=$ArrObjectP[$np]['ClienteNome'];
                  $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
                  $TotalePrenotazione=$ArrObjectP[$np]['TotalePrenotazione'];
                  $TipoViaggio=$ArrObjectP[$np]['TipoViaggio'];
                  $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
                  $TotalePrenotazioneF=  number_format($TotalePrenotazione,2,",","");
                  $DataCorsa=$ArrObjectP[$np]['DataCorsa'];
                  $CorsaDataPartenza=$ArrObjectP[$np]['CorsaDataPartenza'];
                  $OraCorsa=  substr($ArrObjectP[$np]['CorsaOrarioPartenza'],0,5);
                  $DataOperazione= $ArrObjectP[$np]['DataOperazione'];
                  $CodicePrenotazione= $ArrObjectP[$np]['CodicePrenotazione'];
                  $PrenotazioneNumero= $ArrObjectP[$np]['PrenotazioneNumeroId'];
                  $LineaIdentificativo= $ArrObjectP[$np]['LineaIdentificativo'];
                  $BusNumero= $ArrObjectP[$np]['BusNumero'];
                  $FermataSalita=$ArrObjectP[$np]['FermataSalita'];
                  $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
                  $OraSalita=$ArrObjectP[$np]['DataOraSalitaF'];
                  $OraSalitaBus=$ArrObjectP[$np]['DataOraSalitaF'];
                  $OraDiscesa=$ArrObjectP[$np]['DataOraDiscesaF'];
                  $LineaDa=$ArrObjectP[$np]['LineaDa'];
                  $LineaA=$ArrObjectP[$np]['LineaA'];
                  $TotalePostiPrenotati=$ArrObjectP[$np]['TotalePostiPrenotati'];
                  $TipologiaBigliettoId=$ArrObjectP[$np]['TipologiaBigliettoId'];
                  $OrarioPartenza=substr($ArrObjectP[$np]['OrarioPartenza'],0,5);
                  $OrarioArrivo=substr($ArrObjectP[$np]['OrarioArrivo'],0,5);
                  $NextDay=$ArrObjectP[$np]['NextDay'];
                  
                  $MezzoSalita=$ArrObjectP[$np]['MezzoSalita'];
                  $MezzoDiscesa=$ArrObjectP[$np]['MezzoDiscesa'];  
                  
                  $LineaNome=$ArrObjectP[$np]['LineaNome'];
                  $PercorsoNome=$ArrObjectP[$np]['PercorsoNome'];  
                  $CorsaNome=$ArrObjectP[$np]['CorsaNome'];  
                  $LineaId=$ArrObjectP[$np]['LineaId'];
                  
                  
                  // percentuale agenzia
                  
                  $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
                  //echo($sql);
                  $row=$db->query_first($sql);
                  $percentuale_a=0;
                  $fisso_a=0;
                  if ($row['GestoreConvenzioneId']>0)
                   {
                      
                      $percentuale_a=$row['Percentuale'];
                      $fisso_a=$row['Fisso'];
                      
                  }   
                  
                
                  
                  $PercorsoId=$ArrObjectP[$np]['PercorsoId'];
                  
                         // prima fermata su tratta bus
                  
                         $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and MezzoId=2 order by FermataPeso asc limit 1";
                        
                         $row = $db->query_first($sql);
                         $DataRitornoR=" - ";
                         $FermataNomeP="";
                         $FermataOrarioP="";
                         $ComuneNomeP="";
                         if ($row['ComuneId']>0)
                        {
                        
                         $FermataNomeP=$row['FermataNome'];
                         $FermataOrarioP=($row['Orario']);
                         $ComuneNomeP=$row['Comune'];
                        }    
                        
                        // prima fermata su tratta bus
                  
                         $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and MezzoId=2 order by FermataPeso desc limit 1";
                        
                         $row = $db->query_first($sql);
                         
                         $FermataNomeD="";
                         $FermataOrarioD="";
                         $ComuneNomeD="";
                         if ($row['ComuneId']>0)
                        {
                         $dt=new DT();
                         $FermataNomeD=$row['FermataNome'];
                         $FermataOrarioD=($row['Orario']);
                         $ComuneNomeD=$row['Comune'];
                        }  
                  
                  
                  // se salgo sul pulman e scendo con pullman
                  
                 $CorsaDataPartenza_na='';
                 $CorsaDataPartenza_nr='';  
                 $CorsaDataArrivo_nr='';
                 if (($MezzoSalita==2) and ($MezzoDiscesa==2))       
                 {
                     $ComuneSalitaBus=$Salita;
                     $FermataSalitaBus=$FermataSalita;
                     $OraFermataSalitaBus=$OrarioPartenza;
                     $OraFermataSalitaBus1=substr($OraSalita,-5);
                     $ComuneDiscesaBus=$Discesa;
                     $FermataDiscesaBus=$FermataDiscesa;
                     $OraFermataDiscesaBus=substr($OraDiscesa,-5);
                     
                      $Ca=substr($OraDiscesa,0,10);
                     $CorsaDataArrivo_ba=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                     $CorsaOraArrivo_ba=$OraFermataDiscesaBus;
                     
                     
                     
                 }
                 // se salgo con la navetta e scendo con il pullman
                 elseif(($MezzoSalita==1) and ($MezzoDiscesa==2))
                 {
                     $ComuneSalitaBus=$ComuneNomeP;
                     $FermataSalitaBus=$FermataNomeP;
                     $OraFermataSalitaBus=substr($FermataOrarioP,0,5);
                     $OraFermataSalitaBus1=$OraFermataSalitaBus;
                     $ComuneDiscesaBus=$Discesa;
                     $FermataDiscesaBus=$FermataDiscesa;
                      $OraFermataDiscesaBus=substr($OraDiscesa,-5);
                      
                     
                     
                     $Ca=substr($OraDiscesa,0,10);
                     $CorsaDataArrivo_ba=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                     $CorsaOraArrivo_ba=$OraFermataDiscesaBus;
                     
                     
                            $ComuneSalitaNavetta=$Salita;
                            $FermataSalitaNavetta=$FermataSalita;
                            $OraFermataSalitaNavetta=substr($OraSalita,-5);
                        
                            $ComuneDiscesaNavetta=$ComuneSalitaBus;
                            $FermataDiscesaNavetta=$FermataSalitaBus;
                            $OraFermataDiscesaNavetta=$OraFermataSalitaBus;
                             $CorsaDataPartenza_na=$CorsaDataPartenza;
                            
                            
                     $Ca=substr($OraSalita,0,10);
                     $CorsaDataArrivo_na=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                     $CorsaOraArrivo_na=$OraFermataDiscesaNavetta;
                     
                     $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and TrattaId=$TrattaIdSalita and MezzoId=1  order by FermataPeso desc limit 1";
                       
                         $row = $db->query_first($sql);
                         
                         $FermataNomeD="";
                         $FermataOrarioD="";
                         $ComuneNomeD="";
                         if ($row['ComuneId']>0)
                        {
                            $ComuneDiscesaNavetta=$row['Comune'];
                            $FermataDiscesaNavetta=$row['FermataNome'];
                            $CorsaOraArrivo_na=$row['Orario'];
                        
                          
                            
                           
                             
                    
                        
                            
                        }  
                     
                     
                     
                     
                     
                 }
                 // se salgo con il pullman e scendo con la navetta
                 elseif(($MezzoSalita==2) and ($MezzoDiscesa==1))
                 {
                     $ComuneSalitaBus=$Salita;
                     $FermataSalitaBus=$FermataSalita;
                     $OraFermataSalitaBus=substr($OraSalita,-5);
                     $OraFermataSalitaBus1=substr($OraSalita,-5);
                     
                     
                     $ComuneDiscesaBus=$ComuneNomeD;
                     $FermataDiscesaBus=$FermataNomeD;
                     $OraFermataDiscesaBus=substr($FermataOrarioD,0,5);
                     
                     $CorsaDataArrivo_ba=substr($OraDiscesa,0,10);
                     $CorsaOraArrivo_ba=$OraFermataDiscesaBus;
                     
                     $Ca=substr($OraDiscesa,0,10);
                     $CorsaDataArrivo_ba=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                     $CorsaOraArrivo_ba=$OraFermataDiscesaBus;
                     
                     
                       $sql="select FermataNome,Orario,Comune,ComuneId,GiorniAggiuntivi from RT_ElencoFermataOrario where  TrattaId=$TrattaIdDiscesa and CorsaId=$CorsaId and MezzoId=1  order by FermataPeso asc limit 1";

                        //   echo($sql);
                         $row = $db->query_first($sql);
                         
                         $FermataNomeD="";
                         $FermataOrarioD="";
                         $ComuneNomeD="";
                         if ($row['ComuneId']>0)
                        {
                            $ComuneSalitaNavetta=$row['Comune'];
                            $FermataSalitaNavetta=$row['FermataNome'];
                            $OraFermataSalitaNavetta=$row['Orario'];
                        
                            $ComuneDiscesaNavetta=$Discesa;
                            $FermataDiscesaNavetta=$FermataDiscesa;
                            $OraFermataDiscesaNavetta=$OraDiscesa;
                            $GiorniAggiuntivi=$row['GiorniAggiuntivi'];
                            
                             $Ca=substr($OraDiscesa,0,10);
                             
                             /*$dtp=new DT($Ca,'Y-m-d H:i:s');
                             $dtp->addDays($GiorniAggiuntivi);
                             $Ca=$dtp->getDate();
                             */
                             
                             $CorsaDataArrivo_na=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                             $CorsaDataPartenza_na=$CorsaDataArrivo_na;
                             $CorsaOraArrivo_na=substr($OraDiscesa,-5);
                             
                     
                        
                            
                        }  
                     
                     
                     
                 }
                 $DataInizioItinerario=$CorsaDataPartenza;
                 
                 
                 
                 
                  $CorsaInizioItinerario=$CorsaId;
                  $dettaglio_ba['PrenotazioneId']=$PrenotazioneId;     
                  $dettaglio_ba['TipoServizio']='Bus';       
                  $dettaglio_ba['Tragitto']='Andata';
                  $dettaglio_ba['ComunePartenza']=$ComuneSalitaBus;
                  $dettaglio_ba['FermataPartenza']=$FermataSalitaBus;
                  
                  $CorsaDataPartenza1=$CorsaDataPartenza;
                  if ($MezzoSalita==2)
                      {
                      $dt_a=new DT($OraSalitaBus,'d/m/Y H:i');
                      $CorsaDataPartenza1= $dt_a->getDate('Y-m-d');
                  }
                  
                  
                  
                  $dettaglio_ba['DataPartenza']=$CorsaDataPartenza1;
                  
                  
                  
                  $dettaglio_ba['DataInizioItinerario']=$DataInizioItinerario;
                  $dettaglio_ba['CorsaInizioItinerario']=$CorsaInizioItinerario;
                  
                  $dettaglio_ba['OrarioPartenza']=$OraFermataSalitaBus1;
                  $dettaglio_ba['ComuneArrivo']=$ComuneDiscesaBus;
                  $dettaglio_ba['FermataArrivo']=$FermataDiscesaBus;
                  $dettaglio_ba['TipoViaggio']=$TipoViaggio;  
                 // $dettaglio_ba['OrarioArrivo']=$OraFermataDiscesaBus;
                  $dettaglio_ba['LineaId']=$LineaId;
                  $dettaglio_ba['PercorsoId']=$PercorsoId;
                  $dettaglio_ba['CorsaId']=$CorsaId;
                  $dettaglio_ba['LineaNome']=$LineaNome;
                  $dettaglio_ba['PercorsoNome']=$PercorsoNome;
                  $dettaglio_ba['CorsaNome']=$CorsaNome;
                  
                  $dettaglio_ba['DataArrivo']=$CorsaDataArrivo_ba;
                  $dettaglio_ba['OrarioArrivo']=$CorsaOraArrivo_ba;
                  
                 
             
                  
                 
                  
               
                         if ($NextDay>0)
                             $OrarioArrivo=$OrarioArrivo." (+".$NextDay."gg)";
                  
                  $DataRitorno="";
                  if ($TipoViaggioId==2)
                  {
                      $sql="select CorsaId,CorsaDataPartenza from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and Direzione='R'";
                         $row = $db->query_first($sql);
                         $DataRitornoR=" - ";
                         
                         $CorsaIdRitorno=0;
                         if ($row['CorsaDataPartenza'])
                        {
                        
                         $DataRitorno=$row['CorsaDataPartenza'];
                         $CorsaIdRitorno=$row['CorsaId'];
                        }    
                        
                        $sql="select * from RT_ViewElencoPrenotazioniTicketComuni where  OdcIdRef=$user->OdcId and CorsaId=$CorsaIdRitorno and PrenotazioneId=$PrenotazioneId and CorsaDataPartenza='$DataRitorno'";
                   //     echo($sql);
                        $row_r=$db->query_first($sql);
                        
                        $OraCorsaR=  substr($row_r['CorsaOrarioPartenza'],0,5);
                        $DataCorsaR=$row_r['DataCorsa'];
                        $CorsaDataPartenzaR=$row_r['CorsaDataPartenza'];
                        $SalitaR=$row_r['ComuneSalita'];
                        $DiscesaR=$row_r['ComuneDiscesa'];
                        $SiglaSalitaR=$row_r['SiglaProvinciaSR'];
                        $SiglaDiscesaR=$row_r['SiglaProvinciaDR'];
                        $PrenotazioneNumeroR= $row_r['PrenotazioneNumeroId'];
                        
                         $FermataSalitaR=$row_r['FermataSalita'];
                         $FermataDiscesaR=$row_r['FermataDiscesa'];
                         $OraSalitaR=$row_r['DataOraSalitaF'];
                         $OraDiscesaR=$row_r['DataOraDiscesaF'];
                         $LineaDaR=$row_r['LineaDa'];
                         $LineaAR=$row_r['LineaA'];
                         
                         $OrarioPartenzaR=substr($row_r['OrarioPartenza'],0,5);
                         $OrarioArrivoR=substr($row_r['OrarioArrivo'],0,5);
                         $NextDayR=$row_r['NextDay'];
                         
                         
                         $MezzoSalitaR=$row_r['MezzoSalita'];
                         $MezzoDiscesaR=$row_r['MezzoDiscesa'];  
                         $LineaNomeR=$row_r['LineaNome'];
                         $PercorsoNomeR=$row_r['PercorsoNome'];  
                         $CorsaNomeR=$row_r['CorsaNome'];  
                         $LineaIdR=$row_r['LineaId'];
                        $PercorsoIdR=$row_r['PercorsoId'];
                         
                         $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaIdRitorno and MezzoId=2 order by FermataPeso desc limit 1";
                        
                         $row = $db->query_first($sql);
                         
                         $FermataNomePR="";
                         $FermataOrarioPR="";
                         $ComuneNomePR="";
                         if ($row['ComuneId']>0)
                        {
                        
                         $FermataNomePR=$row['FermataNome'];
                         $FermataOrarioPR=($row['Orario']);
                         $ComuneNomePR=$row['Comune'];
                        }    
                        
                        $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaIdRitorno and MezzoId=2 order by FermataPeso asc limit 1";
                        
                         $row = $db->query_first($sql);
                         
                         $FermataNomeDR="";
                         $FermataOrarioDR="";
                         $ComuneNomeDR="";
                         if ($row['ComuneId']>0)
                        {
                         $dt=new DT();
                         $FermataNomeDR=$row['FermataNome'];
                         $FermataOrarioDR=($row['Orario']);
                         $ComuneNomeDR=$row['Comune'];
                        }    
                        
                        
                  if (($MezzoSalitaR==2) and ($MezzoDiscesaR==2))       
                 {
                     $ComuneSalitaBusR=$SalitaR;
                     $FermataSalitaBusR=$FermataSalitaR;
                     $OraFermataSalitaBusR=substr($OraSalitaR,-5);
                     
                     $ComuneDiscesaBusR=$DiscesaR;
                     $FermataDiscesaBusR=$FermataDiscesaR;
                     $OraFermataDiscesaBusR=substr($OraDiscesaR,-5);
                    
                     $Ca=substr($OraDiscesaR,0,10);
                     $CorsaDataArrivo_br=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                     $CorsaOraArrivo_br=$OraFermataDiscesaBusR;
                    
                    
                 }
                 // se salgo con la navetta e scendo con il pullman
                 elseif(($MezzoSalitaR==1) and ($MezzoDiscesaR==2))
                 {
                     
                     $ComuneSalitaBusR=$ComuneNomeDR;
                     $FermataSalitaBusR=$FermataNomeDR;
                     $OraFermataSalitaBusR=substr($FermataOrarioDR,0,5);
                     
                     $ComuneDiscesaBusR=$DiscesaR;
                     $FermataDiscesaBusR=$FermataDiscesaR;
                     $OraFermataDiscesaBusR=$OrarioArrivoR;
                     
                     
                   $Ca=substr($OraDiscesaR,0,10);
                     $CorsaDataArrivo_br=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                     $CorsaOraArrivo_br=$OraFermataDiscesaBusR;
                     
                     
                     
                     $sql="select FermataNome,Orario,Comune,ComuneId,GiorniAggiuntivi from RT_ElencoFermataOrario where CorsaId=$CorsaIdRitorno and MezzoId=1  order by FermataPeso desc limit 1";
                     //   echo($sql);
                         $row = $db->query_first($sql);
                         
                         $FermataNomeD="";
                         $FermataOrarioD="";
                         $ComuneNomeD="";
                         if ($row['ComuneId']>0)
                        {
                             $GA=$row['GiorniAggiuntivi'];
                            $ComuneDiscesaNavettaR=$row['Comune'];
                            $FermataDiscesaNavettaR=$row['FermataNome'];
                            $OraFermataDiscesaNavettaR=substr($row['Orario'],0,5);
                        
                            $ComuneSalitaNavettaR=$SalitaR;
                            $FermataSalitaNavettaR=$FermataSalitaR;
                            $OraFermataSalitaNavettaR=substr($OraSalitaR,-5);
                            
                             $Ca=substr($OraSalitaR,0,10);
                             
                             
                             $CorsaDataArrivo_nr=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                             $CorsaOraArrivo_nr=$OraFermataDiscesaNavettaR;
                             $CorsaDataPartenza_nr=$CorsaDataPartenzaR;
                             $CorsaDataArrivo_nr=$CorsaDataPartenza_nr;
                            
                        }  
                     
                     
                 }
                 // se salgo con il pullman e scendo con la navetta
                 elseif(($MezzoSalitaR==2) and ($MezzoDiscesaR==1))
                 {
                     $ComuneSalitaBusR=$SalitaR;
                     $FermataSalitaBusR=$FermataSalitaR;
                     $OraFermataSalitaBusR=substr($OraSalitaR,-5);
                     
                       $ComuneDiscesaBusR=$ComuneNomePR;
                     $FermataDiscesaBusR=$FermataNomePR;
                     $OraFermataDiscesaBusR=substr($FermataOrarioPR,0,5);
                     
                     $Ca=substr($OraDiscesaR,0,10);
                     $CorsaDataArrivo_br=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                     $CorsaOraArrivo_br=$OraFermataDiscesaBusR;
                     
                    
                            $ComuneDiscesaNavettaR=$DiscesaR;
                            $FermataDiscesaNavettaR=$FermataDiscesaR;
                            $OraFermataDiscesaNavettaR="il ".$OraDiscesaR;
                        
                            $ComuneSalitaNavettaR=$ComuneDiscesaBusR;
                            $FermataSalitaNavettaR=$FermataDiscesaBusR;
                            $OraFermataSalitaNavettaR=$OraFermataDiscesaBusR;
                            
                           
                             $Ca=substr($OraDiscesaR,0,10);
                             $CorsaDataArrivo_nr=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                             $CorsaOraArrivo_nr=substr($OraDiscesaR,-5);
                             $CorsaDataPartenza_nr=$CorsaDataArrivo_nr;
                             $CorsaDataArrivo_nr=$CorsaDataPartenza_nr;
                    
                 }         
                   if ($NextDayR>0)
                             $OrarioArrivo=$OrarioArrivo." (+".$NextDayR."gg)";
                  
                   
                   
                   
                  $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaIdR";
                
                  $row=$db->query_first($sql);
                  $percentuale_r=0;
                  $fisso_r=0;
                  if ($row['GestoreConvenzioneId']>0)
                   {
                      
                     $percentuale_r=$row['Percentuale'];
                      $fisso_r=$row['Fisso'];
                      
                  }   
                   
                  
                  $CorsaDataPartenzaR1=$CorsaDataPartenzaR;
                  if ($MezzoSalita==2)
                      {
                      $dt_a=new DT($OraSalitaR,'d/m/Y H:i');
                      $CorsaDataPartenzaR1= $dt_a->getDate('Y-m-d');
                  } 
                 
                  
                  $dettaglio_br['PrenotazioneId']=$PrenotazioneId;     
                  $dettaglio_br['TipoServizio']='Bus';  
                  $dettaglio_br['TipoViaggio']=$TipoViaggio;  
                  $dettaglio_br['Tragitto']='Ritorno';
                  $dettaglio_br['ComunePartenza']=$ComuneSalitaBusR;
                  $dettaglio_br['FermataPartenza']=$FermataSalitaBusR;
                  $dettaglio_br['DataPartenza']=$CorsaDataPartenzaR1;
                  $dettaglio_br['OrarioPartenza']=$OraFermataSalitaBusR;
                  $dettaglio_br['ComuneArrivo']=$ComuneDiscesaBusR;
                  $dettaglio_br['FermataArrivo']=$FermataDiscesaBusR;
                  $dettaglio_br['DataInizioItinerario']=$DataRitorno;
                  $dettaglio_br['CorsaInizioItinerario']=$CorsaIdRitorno;
                  
                  $dettaglio_br['LineaId']=$LineaIdR;
                  $dettaglio_br['PercorsoId']=$PercorsoIdR;
                  $dettaglio_br['CorsaId']=$CorsaIdRitorno;
                  
                  $dettaglio_br['LineaNome']=$LineaNomeR;
                  $dettaglio_br['PercorsoNome']=$PercorsoNomeR;
                  $dettaglio_br['CorsaNome']=$CorsaNomeR;
                   $dettaglio_br['DataArrivo']=$CorsaDataArrivo_br;
                  $dettaglio_br['OrarioArrivo']=$CorsaOraArrivo_br;
                  $dettaglio_br['PrenotazioneNumero']=$PrenotazioneNumero;
                  
                  $dettaglio_br['AliquotaBiglietto']=$aliquota_b;
                  $dettaglio_br['AliquotaProvvigione']=$aliquota_p;
                  
                   
                        
                      
                  }
                  
                 
                  
                  // calcolo l'importo
                  
                    $sql="select * from RT_PrenotazioneTotalePerServizioPax where  PrenotazioneId=$PrenotazioneId and AppMezzo='Bus' and TipologiaBigliettoId=$TipologiaBigliettoId";
                    
                    $row_i=$db->query_first($sql);
                    $TotBus=0;
                    $TotBusF="0";
                     $TotalePostiPrenotati=1;
                     
                       $NumeroPax=1;
                    $AumentoPax=0;
                    $RiduzionePax=0;
                    $TipologiaBiglietto="";
                    if (!empty($row_i['TotalePerTipologia'])){
                        $TotBus=$TotBus+$row_i['TotalePerTipologia']/$TotalePostiPrenotati;
                       
                         $NumeroPax=$row_i['NumeroPax'];
                         $RiduzionePax=$row_i['RiduzionePax'];
                         $AumentoPax=$row_i['AumentoPax'];
                          $TipologiaBiglietto=$row_i['TipologiaBiglietto'];
                           
                           if (!($ComuneSalitaNavetta<>""))
                           {
                           $delta1=($AumentoPax-$RiduzionePax)/$NumeroPax;
                           $TotBus=$TotBus+$delta1;
                           }
                          
                    }
                    
                    $sql="select TotalePerTipologia from RT_PrenotazioneTotalePerServizioPax where  PrenotazioneId=$PrenotazioneId and AppMezzo='Navetta' and TipologiaBigliettoId=$TipologiaBigliettoId";
                    $row_i=$db->query_first($sql);
                    $TotNav=0;
                    $TotNavF="0";
                    $TotalePostiPrenotati=1;
                  
                    if (!empty($row_i['TotalePerTipologia'])){
                        $TotNav=($row_i['TotalePerTipologia']/$TotalePostiPrenotati)*($moltiplicatore);
                        $TotNavF=number_format($TotNav,2,",","");
                      
                    }
                    
                   
                   
                   $delta=($AumentoPax-$RiduzionePax)/$NumeroPax;
                   $TotNav=($TotNav+$delta)*($moltiplicatore);
                   $TotBusF=number_format($TotBus,2,",","");
                   $TotaleBiglietto=$TotBus+$TotNav;
                   
                   
                  if ($TipoViaggioId==2)
                  {
                     $TotBus=$TotBus/2;
                     $TotNav=$TotNav/2;
                     $dettaglio_br['Importo']=$TotBus;
                     $dettaglio_br['TipologiaBiglietto']=$TipologiaBiglietto;
                     /*$DaRisonoscereAgenziaR=($TotBus*$percentuale_r/100)+$fisso_r;
                     $dettaglio_br['ImportoAgenzia']=$DaRisonoscereAgenziaR;*/
                     
                     $arr=$this->ImportiPrenotazione($TotBus,$percentuale_r,$fisso_r,$aliquota_b,$aliquota_p);
                     $dettaglio_br['ImportoAgenzia']=$arr[0];      
                     $dettaglio_br['DaBonificare']=$arr[1];      
                     $dettaglio_br['DaFatturare']=$arr[2];      
                     
                     $dettaglio_br['PercentualeAgenzia']=$percentuale_r;    
                     $dettaglio_br['FissoAgenzia']=$fisso_r;     
                  }
                  $dettaglio_ba['Importo']=$TotBus;
                  $dettaglio_ba['TipologiaBiglietto']=$TipologiaBiglietto;
                  $dettaglio_ba['PrenotazioneNumero']=$PrenotazioneNumero;
                  
                  $dettaglio_ba['AliquotaBiglietto']=$aliquota_b;
                  $dettaglio_ba['AliquotaProvvigione']=$aliquota_p;
                  
                     
                  /*$TotBus1=$TotBus*($aliquota_b/100+1);
                  $DaRisonoscereAgenziaA=($TotBus1*$percentuale_a/100)+$fisso_a;
                  $DaRisonoscereAgenziaA=  number_format($DaRisonoscereAgenziaA,2);*/
                    
                 //   echo($DaRisonoscereAgenziaA);
                   $arr=$this->ImportiPrenotazione($TotBus,$percentuale_a,$fisso_a,$aliquota_b,$aliquota_p);
                   $dettaglio_ba['ImportoAgenzia']=$arr[0];      
                   $dettaglio_ba['DaBonificare']=$arr[1];      
                   $dettaglio_ba['DaFatturare']=$arr[2];      
                   
                    $dettaglio_ba['PercentualeAgenzia']=$percentuale_a;    
                    $dettaglio_ba['FissoAgenzia']=$fisso_a;    
                   
                   if ($tipo_titolo=='R')
                   $TotaleBiglietto=$TotaleBiglietto*(-1);    
                   
                   $TotaleBigliettoF=number_format($TotaleBiglietto,2,",","");
                   
                   
                   
                   $DataEticket=$ArrObjectP[$np]['DataEticket'];
                   $AnnoEticket=$ArrObjectP[$np]['Anno'];
                   $Eticket=$ArrObjectP[$np]['Eticket'];
                   
                   $CodiceBiglietto=$Eticket."/".$AnnoEticket;
                   $DataTitolo=$DataEticket;
                   
                   $dettaglio_ba=$storico->operazioni_insert($dettaglio_ba, $user);
                   $idins=$db->insert("RT_PrenotazioneDettaglio",$dettaglio_ba);
                 //  print_r($dettaglio_ba);
                  
                   if ($ComuneSalitaNavetta<>"")
                   {
                       
                        $dettaglio_na['PrenotazioneId']=$PrenotazioneId;     
                        $dettaglio_na['TipoServizio']='Navetta';       
                        $dettaglio_na['Tragitto']='Andata';
                        $dettaglio_na['ComunePartenza']=$ComuneSalitaNavetta;
                        $dettaglio_na['FermataPartenza']=$FermataSalitaNavetta;
                        $dettaglio_na['DataPartenza']=$CorsaDataPartenza_na;
                        $dettaglio_na['OrarioPartenza']=$OraFermataSalitaNavetta;
                        $dettaglio_na['ComuneArrivo']=$ComuneDiscesaNavetta;
                        $dettaglio_na['FermataArrivo']=$FermataDiscesaNavetta;
                        $dettaglio_na['TipoViaggio']=$TipoViaggio;  
                        $dettaglio_na['LineaId']=$LineaId;
                        $dettaglio_na['PercorsoId']=$PercorsoId;
                        $dettaglio_na['CorsaId']=$CorsaId;
                        $dettaglio_na['LineaNome']=$LineaNome;
                        $dettaglio_na['PercorsoNome']=$PercorsoNome;
                        $dettaglio_na['CorsaNome']=$CorsaNome;
                        $dettaglio_na['TipologiaBiglietto']=$TipologiaBiglietto;
                        $dettaglio_na['Importo']=$TotNav;
                        $dettaglio_na['PrenotazioneNumero']=$PrenotazioneNumero;
                        $dettaglio_na['DataInizioItinerario']=$DataInizioItinerario;
                        $dettaglio_na['CorsaInizioItinerario']=$CorsaInizioItinerario;
                        
                       // $DaRisonoscereAgenziaNA=($TotNav*$percentuale_a/100)+$fisso_a;
                       // $dettaglio_na['ImportoAgenzia']=$DaRisonoscereAgenziaNA;
                        
                        $arr=$this->ImportiPrenotazione($TotNav,$percentuale_a,$fisso_a,$aliquota_b,$aliquota_p);
                        $dettaglio_na['ImportoAgenzia']=$arr[0];      
                        $dettaglio_na['DaBonificare']=$arr[1];      
                        $dettaglio_na['DaFatturare']=$arr[2];    
                        
                        
                         $dettaglio_na['PercentualeAgenzia']=$percentuale_a;    
                          $dettaglio_na['FissoAgenzia']=$fisso_a;  
                        
                         $dettaglio_na['DataArrivo']=$CorsaDataArrivo_na;
                         $dettaglio_na['OrarioArrivo']=$CorsaOraArrivo_na;
                         
                           $dettaglio_na['AliquotaBiglietto']=$aliquota_b;
                            $dettaglio_na['AliquotaProvvigione']=$aliquota_p;
                         
                         $dettaglio_na=$storico->operazioni_insert($dettaglio_na, $user);
                         $idins=$db->insert("RT_PrenotazioneDettaglio",$dettaglio_na);
                        
                      //print_r($dettaglio_na);
                       
                   }
                   
                   if ($TipoViaggioId==2)
                   {
                        $dettaglio_br=$storico->operazioni_insert($dettaglio_br, $user);
                         $idins=$db->insert("RT_PrenotazioneDettaglio",$dettaglio_br);
                      //    print_r($dettaglio_br);
                   
                    if ($ComuneSalitaNavettaR<>"")
                   {
                        $dettaglio_nr['PrenotazioneId']=$PrenotazioneId;     
                        $dettaglio_nr['TipoServizio']='Navetta';       
                        $dettaglio_nr['Tragitto']='Ritorno';
                        $dettaglio_nr['ComunePartenza']=$ComuneSalitaNavettaR;
                        $dettaglio_nr['FermataPartenza']=$FermataSalitaNavettaR;
                        $dettaglio_nr['DataPartenza']=$CorsaDataPartenza_nr;
                        $dettaglio_nr['OrarioPartenza']=$OraFermataSalitaNavettaR;
                        $dettaglio_nr['ComuneArrivo']=$ComuneDiscesaNavettaR;
                        $dettaglio_nr['FermataArrivo']=$FermataDiscesaNavettaR;
                        $dettaglio_nr['TipoViaggio']=$TipoViaggio;  
                        $dettaglio_nr['LineaId']=$LineaIdR;
                        $dettaglio_nr['PercorsoId']=$PercorsoIdR;
                        $dettaglio_nr['CorsaId']=$CorsaIdRitorno;
                        $dettaglio_nr['LineaNome']=$LineaNomeR;
                         $dettaglio_nr['PercorsoNome']=$PercorsoNomeR;
                         $dettaglio_nr['CorsaNome']=$CorsaNomeR;
                         $dettaglio_nr['TipologiaBiglietto']=$TipologiaBiglietto;
                         $dettaglio_nr['Importo']=$TotNav;
                         $dettaglio_nr['DataArrivo']=$CorsaDataArrivo_nr;
                         $dettaglio_nr['OrarioArrivo']=$CorsaOraArrivo_nr;
                         $dettaglio_nr['PrenotazioneNumero']=$PrenotazioneNumero;
                        $dettaglio_nr['DataInizioItinerario']=$DataRitorno;
                        $dettaglio_nr['CorsaInizioItinerario']=$CorsaIdRitorno;
                         
                         /*$DaRisonoscereAgenziaRN=($TotNav*$percentuale_r/100)+$fisso_r;
                         $dettaglio_nr['ImportoAgenzia']=$DaRisonoscereAgenziaRN;*/
                         
                        $arr=$this->ImportiPrenotazione($TotNav,$percentuale_r,$fisso_r,$aliquota_b,$aliquota_p);
                        $dettaglio_nr['ImportoAgenzia']=$arr[0];      
                        $dettaglio_nr['DaBonificare']=$arr[1];      
                        $dettaglio_nr['DaFatturare']=$arr[2];    
                        
                         
                         $dettaglio_nr['PercentualeAgenzia']=$percentuale_r;    
                         $dettaglio_nr['FissoAgenzia']=$fisso_r;  
                         $dettaglio_nr['AliquotaBiglietto']=$aliquota_b;
                         $dettaglio_nr['AliquotaProvvigione']=$aliquota_p;
                         
                         $dettaglio_nr=$storico->operazioni_insert($dettaglio_nr, $user);
                         $idins=$db->insert("RT_PrenotazioneDettaglio",$dettaglio_nr);
                        //print_r($dettaglio_nr);
                       
                   }
                       
                   }
                   
                  
                   
                 $np++;  
             }
    
   return true; 
}

public function ImportiPrenotazione($Venduto,$PercAge,$FissoAge,$PercB,$PercP)
{
    $ImportoBase=  number_format($Venduto/($PercB/100+1),4);
    $ImportoAgenziaNetto=number_format($ImportoBase*($PercAge/100)+$FissoAge,4);
    $DaFatturare=number_format($ImportoAgenziaNetto*($PercP/100+1),4);
    $DaBonificare=number_format($Venduto-$DaFatturare,4);
   
    
   // print($Venduto);
    
    $arr[0]=$ImportoAgenziaNetto;
    $arr[1]=$DaBonificare;
    $arr[2]=$DaFatturare;
    
    
 
    
    return $arr;
    
}

public function GeneraCorrispettivo($PrenotazioneTitoloId,$CorsaId,$tipo_titolo)
{
     global $user;
     $db=$this->conn;
     $PrenotazioneId=$this->Id;
     $arr_corrispettivo=null;
$sql="select * from RT_FoglioCaricoTitoliDiViaggio where TipoTitolo='E' and  CorsaId=$CorsaId and PrenotazioneTitoloId=$PrenotazioneTitoloId and OdcIdRef=$user->OdcId ";



 $ArrObjectP = $db->fetch_array($sql);
 $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
               while ($np<$numeropasseggeri)
             {
                  $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                  $Agenzia=$ArrObjectP[$np]['RagioneSociale'];
                  $Percorso=$ArrObjectP[$np]['PercorsoNome'];
                  $Salita=$ArrObjectP[$np]['ComuneSalita'];
                  $Discesa=$ArrObjectP[$np]['ComuneDiscesa'];
                  $SiglaSalita=$ArrObjectP[$np]['SiglaSalita'];
                  $SiglaDiscesa=$ArrObjectP[$np]['SiglaDiscesa'];
                  $ClienteNome=$ArrObjectP[$np]['ClienteNome'];
                  $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
                  $TotalePrenotazione=$ArrObjectP[$np]['TotalePrenotazione'];
                  $TipoViaggio=$ArrObjectP[$np]['TipoViaggio'];
                  $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
                  $TotalePrenotazioneF=  number_format($TotalePrenotazione,2,",","");
                  $DataCorsa=$ArrObjectP[$np]['DataCorsa'];
                  $OraCorsa=  substr($ArrObjectP[$np]['CorsaOrarioPartenza'],0,5);
                  $DataOperazione= $ArrObjectP[$np]['DataOperazione'];
                  $CodicePrenotazione= $ArrObjectP[$np]['CodicePrenotazione'];
                  $PrenotazioneNumero= $ArrObjectP[$np]['PrenotazioneNumeroId'];
                  $LineaIdentificativo= $ArrObjectP[$np]['LineaIdentificativo'];
                  $BusNumero= $ArrObjectP[$np]['BusNumero'];
                  $FermataSalita=$ArrObjectP[$np]['FermataSalita'];
                  $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
                  $OraSalita=$ArrObjectP[$np]['DataOraSalitaF'];
                  $OraDiscesa=$ArrObjectP[$np]['DataOraDiscesaF'];
                  $LineaDa=$ArrObjectP[$np]['LineaDa'];
                  $LineaA=$ArrObjectP[$np]['LineaA'];
                  $TotalePostiPrenotati=$ArrObjectP[$np]['TotalePostiPrenotati'];
                  $TipologiaBigliettoId=$ArrObjectP[$np]['TipologiaBigliettoId'];
                  $OrarioPartenza=substr($ArrObjectP[$np]['OrarioPartenza'],0,5);
                  $OrarioArrivo=substr($ArrObjectP[$np]['OrarioArrivo'],0,5);
                  $NextDay=$ArrObjectP[$np]['NextDay'];
                  
                  $MezzoSalita=$ArrObjectP[$np]['MezzoSalita'];
                  $MezzoDiscesa=$ArrObjectP[$np]['MezzoDiscesa'];    
                  
                  
                  // prima fermata su tratta bus
                  
                         $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and MezzoId=2 order by FermataPeso asc limit 1";
                        
                         $row = $db->query_first($sql);
                         $DataRitornoR=" - ";
                         $FermataNomeA="";
                         $FermataOrarioA="";
                         $ComuneNomeA="";
                         if ($row['ComuneId']>0)
                        {
                         $dt=new DT();
                         $FermataNomeA=$row['FermataNome'];
                         $FermataOrarioA=($row['Orario']);
                         $ComuneNomeA=$row['Comune'];
                        }    
                  
                  
                  if ($MezzoSalita==2) // bus
                  {
                      $OrarioPartenza=$OraSalita;
                      $LineaDa=$Salita;
                      
                       $OrarioArrivo=$OraDiscesa;
                      $LineaA=$Discesa;
                      $FermataPartenza=$FermataSalita;
                  }
                 else
                   {
                    
                      $OrarioPartenza=substr($FermataOrarioA,0,5);
                      $LineaDa=$ComuneNomeA;
                       $FermataPartenza=$FermataNomeA;
                     
                  }
                
                         
                         
      
                         if ($NextDay>0)
                             $OrarioArrivo=$OrarioArrivo." (+".$NextDay."gg)";
                  
                  
                 /* if ($TipoViaggioId==2)
                  {
                      $sql="select CorsaId,CorsaDataPartenza from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and Direzione='R'";
                         $row = $db->query_first($sql);
                         $DataRitornoR=" - ";
                         $CorsaIdRitorno=0;
                         if ($row['CorsaDataPartenza'])
                        {
                         $dt=new DT();
                         $DataRitorno=$row['CorsaDataPartenza'];
                         $CorsaIdRitorno=$row['CorsaId'];
                        }    
                        
                        $sql="select * from RT_ViewElencoPrenotazioniTicketComuni where  OdcIdRef=$user->OdcId and CorsaId=$CorsaIdRitorno and PrenotazioneId=$PrenotazioneId and CorsaDataPartenza='$DataRitorno'";
                   //     echo($sql);
                        $row_r=$db->query_first($sql);
                        
                        $OraCorsaR=  substr($row_r['CorsaOrarioPartenza'],0,5);
                        $DataCorsaR=$row_r['DataCorsa'];
                        $SalitaR=$row_r['ComuneSalita'];
                        $DiscesaR=$row_r['ComuneDiscesa'];
                        $SiglaSalitaR=$row_r['SiglaProvinciaSR'];
                        $SiglaDiscesaR=$row_r['SiglaProvinciaDR'];
                        $PrenotazioneNumeroR= $row_r['PrenotazioneNumeroId'];
                        
                         $FermataSalitaR=$row_r['FermataSalita'];
                         $FermataDiscesaR=$row_r['FermataDiscesa'];
                         $OraSalitaR=$row_r['DataOraSalitaF'];
                         $OraDiscesaR=$row_r['DataOraDiscesaF'];
                         $LineaDaR=$row_r['LineaDa'];
                         $LineaAR=$row_r['LineaA'];
                         
                         $OrarioPartenzaR=substr($row_r['OrarioPartenza'],0,5);
                         $OrarioArrivoR=substr($row_r['OrarioArrivo'],0,5);
                         $NextDayR=$row_r['NextDay'];
                         
                         
                        $MezzoSalitaR=$row_r['MezzoSalita'];
                        $MezzoDiscesaR=$row_r['MezzoDiscesa'];  
                        
                        
                         $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaIdRitorno and MezzoId=2 order by FermataPeso desc limit 1";
                        
                         $row = $db->query_first($sql);
                         
                         $FermataNomeR="";
                         $FermataOrarioR="";
                         $ComuneNomeR="";
                         if ($row['ComuneId']>0)
                        {
                         $dt=new DT();
                         $FermataNomeR=$row['FermataNome'];
                         $FermataOrarioR=($row['Orario']);
                         $ComuneNomeR=$row['Comune'];
                        }    
                        
                        if ($MezzoSalita==2)
                  {
                      $OrarioPartenzaR=$OraSalitaR;
                      $LineaDaR=$SalitaR;
                      
                       $OrarioArrivoR=$OraDiscesaR;
                      $LineaAR=$DiscesaR;
                      $FermataArrivoR=$DiscesaR;
                     
                  }
                  else
                   {
                      
                      $OrarioArrivoR=$FermataOrarioR;
                      $LineaDaR=$ComuneNomeR;
                      $FermataArrivoR=$FermataNomeR;
                      
                     
                  }
                  
                        
                        
                   if ($NextDayR>0)
                             $OrarioArrivo=$OrarioArrivo." (+".$NextDayR."gg)";
                        
                      
                  }*/
                  
                 
                  
                    $sql="select * from RT_PrenotazioneTotalePerServizioPax where  PrenotazioneId=$PrenotazioneId and AppMezzo='Bus' and TipologiaBigliettoId=$TipologiaBigliettoId";
                    
                    $row_i=$db->query_first($sql);
                    $TotBus=0;
                    $TotBusF="0";
                     $TotalePostiPrenotati=1;
                     
                       $NumeroPax=1;
                    $AumentoPax=0;
                    $RiduzionePax=0;
                    $TipologiaBiglietto="";
                    if (!empty($row_i['TotalePerTipologia'])){
                        $TotBus=$TotBus+$row_i['TotalePerTipologia']/$TotalePostiPrenotati;
                       
                         $NumeroPax=$row_i['NumeroPax'];
                         $RiduzionePax=$row_i['RiduzionePax'];
                         $AumentoPax=$row_i['AumentoPax'];
                          $TipologiaBiglietto=$row_i['TipologiaBiglietto'];
                    }
                    
                    $sql="select TotalePerTipologia from RT_PrenotazioneTotalePerServizioPax where  PrenotazioneId=$PrenotazioneId and AppMezzo='Navetta' and TipologiaBigliettoId=$TipologiaBigliettoId";
                    $row_i=$db->query_first($sql);
                    $TotNav=0;
                    $TotNavF="0";
                    $TotalePostiPrenotati=1;
                  
                    if (!empty($row_i['TotalePerTipologia'])){
                        $TotNav=$row_i['TotalePerTipologia']/$TotalePostiPrenotati;
                        $TotNavF=number_format($TotNav,2,",","");
                      
                    }
                    
                   
                   
                   $delta=($AumentoPax-$RiduzionePax)/$NumeroPax;
                   $TotNav=$TotNav+$delta;
                   $TotBusF=number_format($TotBus,2,",","");
                   $TotaleBiglietto=$TotBus+$TotNav;
                   
                   if ($tipo_titolo=='R')
                   $TotaleBiglietto=$TotaleBiglietto*(-1);    
                   
                   $TotaleBigliettoF=number_format($TotaleBiglietto,2,",","");
                   
                   
                   
                   $DataEticket=$ArrObjectP[$np]['DataEticket'];
                   $AnnoEticket=$ArrObjectP[$np]['Anno'];
                   $Eticket=$ArrObjectP[$np]['Eticket'];
                   
                   $CodiceBiglietto=$Eticket."/".$AnnoEticket;
                   $DataTitolo=$DataEticket;
                   
                   $arr_corrispettivo[0]['TipoServizio']='Bus';
                   $arr_corrispettivo[0]['Importo']=$TotBus;
                   $arr_corrispettivo[0]['Partenza']=$LineaDa;
                   $arr_corrispettivo[0]['Destinazione']=$Discesa;
                   $arr_corrispettivo[0]['TipoViaggioId']=$TipoViaggioId;
                   $arr_corrispettivo[0]['Percorso']=$Percorso;
                   $arr_corrispettivo[0]['Tratta']=$TrattaBus;
                   $arr_corrispettivo[0]['TipologiaBiglietto']=$TipologiaBiglietto;
                   
                   if ($TotNav<>0)
                   {
                   $arr_corrispettivo[1]['TipoServizio']='Navetta';
                   $arr_corrispettivo[1]['Importo']=$TotNav;
                   
                   
                   
                   
                   $arr_corrispettivo[0]['TipoViaggioId']=$TipoViaggioId;
                   $arr_corrispettivo[0]['Percorso']=$Percorso;
                   $arr_corrispettivo[0]['Tratta']=$TrattaNavetta;
                   $arr_corrispettivo[0]['TipologiaBiglietto']=$TipologiaBiglietto;
                   }
                   
                  
                   
                 $np++;
             }
             
             return $arr_corrispettivo;
}


public function EmettiBiglietti($PrenotazioneId = null) {
	global $user, $OperatoreId, $OdcId, $SedeId, $GestoreId;

 	$OdcId=1;
        $GestoreId=1;
        $OperatoreId=42;
        $SedeId=36;

 	$db = $this->conn;


	if (!isset($PrenotazioneId)) {
		$PrenotazioneId = $this->Id;
	}

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$sql="Select PercorsoId,LineaId from RT_PrenotazionePercorso where Stato=1 AND Cancella=0 AND PrenotazioneId=$PrenotazioneId";
// 	echo $sql;
	$row = $db->query_first($sql);
	$PercorsoId = $row['PercorsoId'];
	$LineaId = $row['LineaId'];

	$sql="Select gp.IdentificativoBiglietto from Gestore g LEFT JOIN GestoreGruppo gp ON (g.GestoreGruppoId = gp.GestoreGruppoId) where GestoreId=$GestoreId";
	$gestore = $db->query_first($sql);
	$IdentificativoBiglietto = $gestore['IdentificativoBiglietto'];
	if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
		$IdentificativoBiglietto = 'OB';
	}
	
	$sql="Select * from RT_PrenotazioneNumero where PrenotazioneId=$PrenotazioneId and OdcIdRef=$OdcId and Cancella=0 order by PrenotazioneNumeroId asc";
	$prenotazioneNumero = $db->fetch_array($sql);

	$sql="Select TipoViaggioId from RT_Prenotazione where PrenotazioneId=$PrenotazioneId";
	$viaggio = $db->query_first($sql);
	$TipoViaggioId=$viaggio['TipoViaggioId'];

	$countNumeroTitoli = count($prenotazioneNumero);
	/*recupero l'ultimo movimento per verificare l'importo venduto, diverso dall'importo del titolo */
	$sql = "SELECT * FROM RT_PrenotazioneMovimento where PrenotazioneId = $PrenotazioneId AND TipoMovimento = 'I' order by DataIns DESC, DataAgg DESC";
	$tempMovimento = $db->query_first($sql);
	if($tempMovimento['PagamentoTipoId'] == 12){
		$importoMovimento = 0;
	} else {
		$importoMovimento = $tempMovimento['ImportoPagato'];
		if($viaggio['TotaleDaPagare'] == $importoMovimento){
			$prezzoTotale = true;
		} else {
			$prezzoTotale = false;
		}
	}

	foreach ($prenotazioneNumero as $numero) {
		$sql = "select * from RT_PrenotazioneTitolo where PrenotazioneNumeroId = ".$numero['PrenotazioneNumeroId'];
		$tempCount = $db->fetch_array($sql);
		if(count($tempCount) == 0){
			$sql="SELECT NumeroPax, PrezzoPax, RiduzionePax, AumentoPax FROM RT_PrenotazioneBiglietto
			WHERE PrenotazioneId=$PrenotazioneId AND TipologiaBigliettoId=".$numero['TipologiaBigliettoId'];
			$Importi = $db->query_first($sql);
			$PrenotazioneNumeroId = $numero['PrenotazioneNumeroId'];
			$TipologiaBigliettoId = $numero['TipologiaBigliettoId'];

			$sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$GestoreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
			$row=$db->query_first($sql);
			$percentuale_a=0;
			$fisso_a=0;
			if ($row['GestoreConvenzioneId']>0)
			{
				$percentuale_a=$row['Percentuale'];
				$fisso_a=$row['Fisso'];

			}
			$sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = $TipologiaBigliettoId";
			$tempTipo = $db->query_first($sql);
			if($tempTipo['OccupaPosto'] == 1){
				$progressivo = $this->GetProgressivoTitoloDiViaggio(date('Y'),$PercorsoId);
				$identificativoServizio = "";
			} else {
				$progressivo = $this->GetProgressivoTitoloDiViaggioServizi(date('Y'),$PercorsoId);
				$identificativoServizio = "E-";
			}
			$codice = $identificativoServizio.$IdentificativoBiglietto . '-' . Str_pad($progressivo, 8, "0", STR_PAD_LEFT);
			$titolo = array();
			$titolo['PrenotazioneId'] = $PrenotazioneId;
			$titolo['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
			$titolo['Codice'] = $codice;
			$titolo['Anno'] = date('Y');
			$titolo['Progressivo'] = $progressivo;
			$titolo['TipoTitolo'] = "E";
			$titolo['PercorsoId'] = $PercorsoId;
				
			$sql = "SELECT pn.PrenotazioneNumeroId, pn.PasseggeroId, pp.Cognome, pp.Nome, pp.SessoId, pp.Eta, pn.TipologiaBigliettoId, tb.TipologiaBiglietto
			FROM RT_PrenotazioneNumero pn
			LEFT JOIN RT_PrenotazionePasseggeri pp ON (pn.PasseggeroId = pp.PrenotazionePasseggeroId)
			LEFT jOIN RT_TipologiaBiglietto tb ON (tb.TipologiaBigliettoId = pp.TipoBigliettoId)
			WHERE pn.PrenotazioneNumeroId = $PrenotazioneNumeroId";
			$datiPasseggero = $db->query_first($sql);
			$titolo['PasseggeroId'] = $datiPasseggero['PasseggeroId'];
			$titolo['Cognome'] = $datiPasseggero['Cognome'];
			$titolo['Nome'] = $datiPasseggero['Nome'];
			$titolo['SessoId'] = $datiPasseggero['SessoId'];
			$titolo['Eta'] = $datiPasseggero['Eta'];
			$titolo['TipologiaBigliettoId'] = $datiPasseggero['TipologiaBigliettoId'];
			$titolo['TipologiaBiglietto'] = $datiPasseggero['TipologiaBiglietto'];

			$titolo['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
			if($prezzoTotale) {
				$titolo['ImportoVenduto'] = $titolo['ImportoTitolo'];
			} else {
				$titolo['ImportoVenduto'] = $importoMovimento / $countNumeroTitoli;
			}
			
			
			/*$titolo['PercentualeAgenzia'] = $percentuale_a;
			 $titolo['FissoAgenzia'] = $fisso_a;
			 $titolo['ImportoAgenzia'] = $fisso_a+($titolo['ImportoTitolo']*$percentuale_a)/100;
			*/
			$titolo = $storico->operazioni_insert($titolo, $user);
			$titoloId=$db->insert("RT_PrenotazioneTitolo", $titolo);
			 
			/*inserimento IVA*/
			$sql="select * from RT_ViewImportiPerIva003 where PrenotazioneTitoloId=$titoloId";
			$ivaarr = $db->fetch_array($sql);
			if(count($ivaarr) > 0) {
			    foreach ($ivaarr as $arr_iva) {
			        $Scorporo=0;
			        $ConfineId=0;
			        $Aliquota=0;
			        $Importo=$arr_iva['ImportoTitolo'];
			        
			        if ($TipoViaggioId==2)
			            $Importo=$Importo/2;
			            
			            $KmTot=$arr_iva['KmPercorsi'];
			            if ($arr_iva['np']==$arr_iva['nd']) {
			                $TotImponibile=$Importo;
			                $TotIva=$TotImponibile-($TotImponibile/$arr_iva['ScorporoP']);
			                $KmSuConfine=$KmTot;
			                $Scorporo=$arr_iva['ScorporoD'];
			                $ConfineId=$arr_iva['cidp'];
			                $Aliquota=$arr_iva['AliquotaPartenza'];
			            } else {
			                if (!is_null($arr_iva['ScorporoP'])) {
			                    $Scorporo=$arr_iva['ScorporoP'];
			                    $ConfineId=$arr_iva['cidp'];
			                    $Aliquota=$arr_iva['AliquotaPartenza'];
			                } else {
			                    $Scorporo=$arr_iva['ScorporoD'];
			                    $ConfineId=$arr_iva['cidd'];
			                    $Aliquota=$arr_iva['AliquotaDestinazione'];
			                }
			                
			                
			                $KmPercorsiTotali= $arr_iva['KmPercorsi'];
			                $KmSuConfine=$arr_iva['KmTotali'];
			                $TotImponibile=(($Importo*$KmSuConfine)/$KmPercorsiTotali);
			                $TotIva=$TotImponibile-($TotImponibile/$Scorporo);
			                
			            }
			            if($KmSuConfine > 0) {
			                if($Aliquota == 0) {
			                    $TotIva = 0;
			                }
			                $diva=array();
			                $Importo=  number_format($Importo,2);
			                $Imponibile=  number_format($TotImponibile,2);
			                $TotIva=  number_format($TotIva,2);
			                $diva['ConfineId']=$ConfineId;
			                $diva['PrenotazioneTitoloId']=$titoloId;
			                $diva['KmPercorsiTotale']=$KmTot;
			                $diva['KmPercorsiTerritorio']=$KmSuConfine;
			                $diva['AliquotaIva']=$Aliquota;
			                $diva['ImportoTitolo']=$Importo;
			                $diva['ImportoTitoloPerConfine']=$Imponibile;
			                $diva['ImportoIvaConfine']=$TotIva;
			                $diva = $storico->operazioni_insert($diva, $user);
			                $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
			            }
			    }
			} else if(Config::$ivaTerritoriaItaliano){
			    //se non è presente in tabella alcun record il viaggio avviene su territorio italiano
			    $Importo=$titolo['ImportoTitolo'];
			    if ($TipoViaggioId==2)
			        $Importo=$Importo/2;
			        
			        $sql = "SELECT * FROM RT_Confine WHERE ConfineId = 2";
			        $confine=$db->query_first($sql);
			        
			        $diva=array();
			        $Imponibile = $Importo;
			        $TotImponibile = $Imponibile;
			        $TotIva = $TotImponibile-($TotImponibile/$confine['ValoreScorporo']);
			        $Importo=  number_format($Importo,2);
			        $Imponibile=  number_format($TotImponibile,2);
			        $TotIva=  number_format($TotIva,2);
			        $diva['ConfineId']=2;
			        $diva['PrenotazioneTitoloId']=$titoloId;
			        $diva['KmPercorsiTotale']=$kmArray['KmPercorsiAndata'];
			        $diva['KmPercorsiTerritorio']=$kmArray['KmPercorsiAndata'];
			        $diva['AliquotaIva']=$confine['Aliquota'];
			        $diva['ImportoTitolo']=$Importo;
			        $diva['ImportoTitoloPerConfine']=$Imponibile;
			        $diva['ImportoIvaConfine']=$TotIva;
			        $diva = $storico->operazioni_insert($diva, $user);
			        $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
			}
			/* FINE inserimento IVA */
			 
			 


			$provv=array();
			$provv['PrenotazioneTitoloId']=$titoloId;
			$provv['GestoreId']=$GestoreId;

			//$provv['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
			$provv['PercentualeAgenzia'] = $percentuale_a;
			$provv['FissoAgenzia'] = $fisso_a;
			$provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoVenduto']*$percentuale_a)/100;
			$provv = $storico->operazioni_insert($provv, $user);
			$provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);


			$finito=false;
			$gestorePadreId=$GestoreId;
			$conta=0;
			while($finito==false || $conta<100)
			{
				if($gestorePadreId>0){
					$gestoreNew=new Gestore($gestorePadreId);
					$gestoreNew->conn=$db;
					$gestoreNew->inizializzaDatiGenerali(null);
					$gestorePadreId=$gestoreNew->GestoreDatiGenerali['GestorePadre'];
	
					if ($gestorePadreId>0)
					{
						$sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$gestorePadreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
						$row=$db->query_first($sql);
						$percentuale_a=0;
						$fisso_a=0;
						if ($row['GestoreConvenzioneId']>0)
						{
							$percentuale_a=$row['Percentuale'];
							$fisso_a=$row['Fisso'];
						}
	
						$provv=array();
						$provv['PrenotazioneTitoloId']=$titoloId;
						$provv['GestoreId']=$gestorePadreId;
	
						//$provv['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
						$provv['PercentualeAgenzia'] = $percentuale_a;
						$provv['FissoAgenzia'] = $fisso_a;
						$provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoVenduto']*$percentuale_a)/100;
						$provv = $storico->operazioni_insert($provv, $user);
						$provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
	
	
	
					}
					else
						$finito=true;
				} else {
					$finito=true;
				}


				$conta++;
			}

		}

	}

	$data = array();
	$data['PrenotazioneStato'] = 3;
	$data=$storico->operazioni_update($data,$user);

	$result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$OdcId and PrenotazioneId=$PrenotazioneId");
	$result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$OdcId and PrenotazioneId=$PrenotazioneId");
}



// public function EmettiBiglietti($NumeroBiglietti,$CorsaIdAndata,$CorsaIdRitorno,$tipo)
// {
//     global $user;
//     $db=$this->conn;
//     $storico=new StoricoOperazioni();
//     $storico->conn=$db;
//     $PrenotazioneId=$this->Id;
   
//     $nt=0;
    
//      $sql="Select * from RT_ElencoCorsa where CorsaId=$CorsaIdAndata";
    
     
//     $row = $db->query_first($sql);
//     $PercorsoId=$row['PercorsoId'];
//     $Identificativo=$row['Identificativo'];
    
  
//     $data['PrenotazioneStato']=3;
//     $result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId"); 
//     $data=$storico->operazioni_update($data,$user);
//     $result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId"); 
    
    
    
//     $sql="Select * from RT_PrenotazioneNumero where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0 order by PrenotazioneNumeroId asc";
    
    
//     $ArrObject = $db->fetch_array($sql);
//      $nt=0;
//      while($nt<sizeof($ArrObject))
//      {
//     $PrenotazioneNumeroId=$ArrObject[$nt]['PrenotazioneNumeroId'];
    
    
//    /* while($nt<$NumeroBiglietti)
//     {*/
//         $anno=Date('Y');
//         $progressivo=$this->GetProgressivoTitoloDiViaggio($anno,$PercorsoId); // get codice
//         $codice=$Identificativo."-".$this->GetCodiceTitoloDiViaggio($anno,$PercorsoId); // get codice
//         $d1['Anno']=$anno;
//         $d1['Codice']=$codice;
//         $d1['PrenotazioneId']=$this->Id;
//         $d1['TipoTitolo']='E';
//         $d1['Progressivo']=$progressivo;
//         $d1['PercorsoId']=$PercorsoId;
//         $d1['PrenotazioneNumeroId']=$PrenotazioneNumeroId;
        
//         if ($tipo=='EP')
//             $d1['PostViaggio']=1;
        
//         $d1=$storico->operazioni_insert($d1,$user);
        
        
        
//         $PrenotazioneTitoloId=$db->insert("RT_PrenotazioneTitolo", $d1);
        
        
//         /*$arr_corrispettivo=$this->GeneraCorrispettivo($PrenotazioneTitoloId,$CorsaIdAndata,$tipo);
//         $arr_corrispettivo_bus=$arr_corrispettivo[0];
//         $arr_corrispettivo_bus['PrenotazioneTitoloId']=$PrenotazioneTitoloId;
//         $arr_corrispettivo_bus=$storico->operazioni_insert($arr_corrispettivo_bus,$user);
//         $lastidA=$db->insert("RT_PrenotazioneTitoloDettaglio", $arr_corrispettivo_bus);
//         $importo_titolo=$arr_corrispettivo_bus['Importo'];
//         if ($arr_corrispettivo[1]['TipoServizio']=='Navetta')
//         {
//           $arr_corrispettivo_nav=$arr_corrispettivo[1];
//           $arr_corrispettivo_nav['PrenotazioneTitoloId']=$PrenotazioneTitoloId;
//           $arr_corrispettivo_nav=$storico->operazioni_insert($arr_corrispettivo_nav,$user);
//           $lastidA=$db->insert("RT_PrenotazioneTitoloDettaglio", $arr_corrispettivo_nav);
//              $importo_titolo=$importo_titolo+$arr_corrispettivo_nav['Importo'];
//         }
//         $arr_importo_titolo['ImportoTitolo']=$importo_titolo;    
//         $db->update("RT_PrenotazioneTitolo",$arr_importo_titolo,"PrenotazioneTitoloId=".$PrenotazioneTitoloId);
//        */
        
        
        
        
       
        
//         // genera corrispettivo
        
        
        
        
        
        
//         /*if ($CorsaIdRitorno>0)
//         {
//             $d1['CorsaId']=$CorsaIdRitorno;
//             $lastidA=$db->insert("RT_PrenotazioneTitolo", $d1);
//         }*/
        
        
       
//         $nt++;
//     }
    
    
// }


public function GeneraCodiciPrenotazione($NumeroBiglietti)
{
   
    global $user;
    $db=$this->conn;
     $storico=new StoricoOperazioni();
    $storico->conn=$db;
    $PrenotazioneId=$this->Id;
   
    $nt=0;
   
     $sql="Select * from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0";
     $ArrObject = $db->fetch_array($sql);
    
     
     
     $ntb=0;
     
    
     
     while($ntb<sizeof($ArrObject))
     {
         $TipologiaBigliettoId=$ArrObject[$ntb]['TipologiaBigliettoId'];
         $NumeroPaxBiglietto=$ArrObject[$ntb]['NumeroPax'];
   
 $nt=0;
    while($nt<$NumeroPaxBiglietto)
    {
        $d1=null;
        $d1['PrenotazioneId']=$this->Id;
        $d1['TipologiaBigliettoId']=$TipologiaBigliettoId;
       
        $d1=$storico->operazioni_insert($d1,$user);
        $lastidA=$db->insert("RT_PrenotazioneNumero", $d1);
       
        
     
        $nt++;
    }
       $ntb++;
    }
    
}

public function AnnullaPrenotazione($stato)
{
    global $user;
    $db=$this->conn;
    $storico=new StoricoOperazioni();
    $storico->conn=$db;
    $PrenotazioneId=$this->Id;
    
     $data['PrenotazioneStato']=$stato;
     $data=$storico->operazioni_update($data,$user);
    
    $result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId"); 
    $result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId"); 
    
    
    
}

public function RimborsaBiglietti()
{
    
    global $user;
    $db=$this->conn;
    $storico=new StoricoOperazioni();
    $storico->conn=$db;
    $PrenotazioneId=$this->Id;
    
    
     $sql="Select * from RT_PrenotazioneTitolo where PrenotazioneId=$PrenotazioneId and TipoTitolo='E' and OdcIdRef=$user->OdcId and Stato=1 and Cancella=0 order by PrenotazioneTitoloId asc";
     $ArrObject = $db->fetch_array($sql);
     
     $conta=0;
     
     
     while($conta<sizeof($ArrObject))
     {
         $PrenotazioneTitoloId=$ArrObject[$conta]['PrenotazioneTitoloId'];
         $PercorsoId=$ArrObject[$conta]['PercorsoId'];
         $ImportoTitolo=$ArrObject[$conta]['ImportoTitolo'];
         $PrenotazioneNumeroId=$ArrObject[$conta]['PrenotazioneNumeroId'];
         
            $sql="Select * from RT_ElencoCorsa where PercorsoId=$PercorsoId";
            $row = $db->query_first($sql);
          
            $Identificativo=$row['Identificativo'];
         
         $anno=Date('Y');
          $progressivo=$this->GetProgressivoTitoloDiViaggio($anno,$PercorsoId); // get codice
       
         $codice=$Identificativo."-".$this->GetCodiceTitoloDiViaggio($anno,$PercorsoId); // get codice
         $d1['Anno']=$anno;
         $d1['Codice']=$codice;
         $d1['PrenotazioneId']=$this->Id;
         $d1['TipoTitolo']='R';
         $d1['Progressivo']=$progressivo;
         $d1['PercorsoId']=$PercorsoId;
         $d1['ImportoTitolo']=$ImportoTitolo;
         $d1['PrenotazioneNumeroId']=$PrenotazioneNumeroId;
         $d1=$storico->operazioni_insert($d1,$user);
         $lastidA=$db->insert("RT_PrenotazioneTitolo", $d1);
         
          $sql="Select * from RT_PrenotazioneTitoloDettaglio where PrenotazioneTitoloId=$PrenotazioneTitoloId";
     $ArrObject_t = $db->fetch_array($sql);
     
     $contat=0;
     
     
     while($contat<sizeof($ArrObject_t))
     {
         $d1t['PrenotazioneTitoloId']=$lastidA;
         $d1t['TipoServizio']=$ArrObject_t[$contat]['TipoServizio'];
         $d1t['Importo']=($ArrObject_t[$contat]['Importo'])*(-1);
         $d1t['TipologiaBiglietto']=$ArrObject_t[$contat]['TipologiaBiglietto'];
         $d1t=$storico->operazioni_insert($d1t,$user);
         $pp=$db->insert("RT_PrenotazioneTitoloDettaglio", $d1t);
         
        $contat++; 
     }
         
         
         
         
         $d2['PrenotazioneTitoloIdEmesso']=$PrenotazioneTitoloId;
         $d2['PrenotazioneTitoloIdRimborsato']=$lastidA;
         $d2=$storico->operazioni_insert($d2,$user);
         
         $lastidA=$db->insert("RT_PrenotazioneTitoloRimborsato", $d2);
         
         $conta++;
     }
     
     
    $data['PrenotazioneStato']=7;
    $data=$storico->operazioni_update($data,$user);
    $result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId"); 
    $result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId"); 
   
   
    
     
    
}

public function getStatoPrenotazioneStato($StatoId)
{
global $user;
$db=$this->conn;    
$PrenotazioneId=$this->Id;
$sql = "SELECT * from RT_AppPrenotazioneStato where PrenotazioneStatoid=$StatoId";
$row = $db->query_first($sql);
$PrenotazioneStato=$row['PrenotazioneStato'];
return $PrenotazioneStato;
}


public function getStatoPrenotazioneStatoIdByCorsa($CorsaId)
{
    
global $user;
$db=$this->conn;    
$PrenotazioneId=$this->Id;
$sql = "SELECT * from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and Stato=1 and Cancella=0";


$row = $db->query_first($sql);
$CorsaStato=$row['PrenotazioneStato'];

return $CorsaStato;
}


private function GetProgressivoTitoloDiViaggio()
{
	$db = $this->conn;

	$sql = "SELECT MAX(Progressivo) progressivo from RT_PrenotazioneTitolo where Codice NOT LIKE 'E-%'";
	if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
		$sql .= " AND Codice LIKE 'OB-%'";
	}
	
	$row = $db->query_first($sql);

	if ((!empty($row['progressivo'])) and ($row['progressivo'] > 0))
		return $row['progressivo'] + 1;

	return 1;
}

private function GetProgressivoTitoloDiViaggioServizi()
{
	$db = $this->conn;

	$sql = "SELECT MAX(Progressivo) progressivo from RT_PrenotazioneTitolo where Codice LIKE 'E-%'";
	if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
		$sql .= " AND Codice LIKE '%OB-%'";
	}

	$row = $db->query_first($sql);

	if ((!empty($row['progressivo'])) and ($row['progressivo'] > 0))
		return $row['progressivo'] + 1;

	return 1;
}

private function GetCodiceTitoloDiViaggio($y,$PercorsoId)
{
global $user;
$db=$this->conn;    

$sql = "SELECT * from RT_CalcolaCodiceBiglietto where Anno=$y and OdcIdRef=$user->OdcId and PercorsoId=$PercorsoId";

$row = $db->query_first($sql);
$ProgressivoOdc=1;
if (!empty($row['AttualeNumero']))
    $ProgressivoOdc=$row['AttualeNumero']+1;

$l=strlen($ProgressivoOdc);



$c=0;
$ProgressivoOdc_c="";
while($l<=6)
{
  $ProgressivoOdc_c.="0";
$l++;
  
}

$ProgressivoOdc_c1=$ProgressivoOdc_c."".$ProgressivoOdc;

return $ProgressivoOdc_c1;

}
public function GetTipologiaPrenotazioneAbilitata($CorsaAndataId,$FermataAndataId)
 {
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        
        
$corsa = new Corsa($CorsaAndataId);
$corsa->conn=$db;
$corsa->inizializzaDatiGenerali();
$arr_corsa=$corsa->DatiGenerali;
$lineaId=$arr_corsa['LineaId'];
 
$sql="select * from RT_ElencoConvenzioniAttive where LineaId=$lineaId and GestoreId=$user->GestoreId";


$row = $db->query_first($sql);
$r=0;
if (!empty($row['GestoreConvenzioneId']))
{
    $SoloPrenotazione=$row['SoloPrenotazione'];
    if ($SoloPrenotazione==0)
     $r=1; // emissione titolo obbligatoria
    else
     {
       
        $sql="select * from RT_Fermata where OdcIdRef=$user->OdcId and FermataId=$FermataAndataId";
     
        
        $row = $db->query_first($sql);
        $IsBlackList=$row['IsBlackList'];
        
        if ($IsBlackList==1)
            $r=1; // emissione titolo obbligatoria
        else
           $r=2; // consentita la sola prenotazione
        
        
     }   
}
 // nessun permesso
      
return $r;
    
    
}

public function inizializzaDatiGenerali()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_Prenotazione WHERE PrenotazioneId=$Id and OdcIdRef=1";      
        $row = $db->query_first($sql);
        
        if (!empty($row['PrenotazioneId']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
        
}

public function inizializzaDettagliCarico()
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_FoglioBusCarico WHERE PrenotazioneId=$Id and OdcIdRef=$user->OdcId";      
        $row = $db->query_first($sql);
        
        if (!empty($row['PrenotazioneId']))
        $this->DatiGeneraliCarico=$row;
        else
        {
            print("errore");
            exit();
               
            
        }
        
}


public function inizializzaDatiGeneraliPercorso($Direzione)
{
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_PrenotazionePercorso WHERE PrenotazioneId=$Id and Direzione='$Direzione' and cancella=0 and OdcIdRef=1";      
        $row = $db->query_first($sql);
        
        if (!empty($row['PrenotazioneId']))
        $this->DatiGeneraliPercorso=$row;
        else
        {
           $this->DatiGeneraliPercorso=null;
           
           
               
            
        }
        
}




public function CheckCoerenzaAR($DataA,$DataR)
        {
$to_time = strtotime($DataR);
$from_time = strtotime($DataA);
$secondi_residui=round(($to_time - $from_time));

$errore="";
if (!($secondi_residui>0))
    $errore="La data del ritorno deve essere successiva alla data di andata!";

return $errore;
    
}



public function CheckNumeroPax($NumeroPax)
{
    $errore="";
    if (!($NumeroPax>0))
        $errore="Inserire almeno un passeggero \n";
    
    return $errore;
}

public function CheckDisponibilitaPax($DataPartenza,$CorsaId,$NumeroPaxRichiesti,$Percorso, $pickup = null, $dropoff = null)
{
    global $user;
    $db=$this->conn;
    $ritornoOpen = $_POST['Prenotazione']['RitornoOpen'];
    if($ritornoOpen == 0){
// 		$sql="select PostiRealmenteDisponibili, PostiTotali, LineaId from RT_ViewElencoGestioneOperativita_new where OdcIdRef=$user->OdcId
// 	             and CorsaId=$CorsaId and AppCalendarioData='$DataPartenza' limit 1" ;
    	$sql = "select LineaId from RT_Corsa where CorsaId = $CorsaId";
    	$tempL = $db->query_first($sql);
    	$LineaId = $tempL['LineaId'];

    	$grafo = new GrafoTratte($LineaId, $CorsaId, $db, $pickup, $dropoff, true);
    	$string = '';
    	$f = new Fermata();
    	$f->conn = $db;
    	$first = true;
    	foreach($grafo->flotta as $flotta){
    		
    		foreach ($flotta->comuni as $c => $comune){
// 				if(count($comune['passeggeri']) > 0) {
					if(!$f->isInterscambioLinea($LineaId, $comune['comune'])){
						if($first){
							$string .= $comune['comune'];
							$first = false;
						} else {
							$string .= ','.$comune['comune'];
						}
					}
// 				}										
			}
    	}
    	$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
    	where CorsaId = $CorsaId and DataPartenza = '$DataPartenza' and Comune IN ($string) ";
		$tempR = $db->query_first($sql);
		
		$sql = "Select b.TotalePosti
    		from RT_TipologiaBus b
    		left join RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
    		where c.CorsaId = $CorsaId";
		$tempR1 = $db->query_first($sql);
		$postiCorsaDefault = $tempR1['TotalePosti'];	
			
    	
    	if(isset($tempR['Posti'])){
    			
    		$postiOccupati = $tempR['Posti'];
    		
    		$sql = "Select TrattaId from RT_DisponibilitaPostiCron
    		where CorsaId = $CorsaId and DataPartenza = '$DataPartenza' and Posti = $postiOccupati and Comune IN ($string)";
    		
    		$tratta =  $db->query_first($sql);
    		
    		$sql = "SELECT TipologiaBusDefaultId from RT_Tratta c
					WHERE TrattaId = ".$tratta['TrattaId'];

    		$check = $db->query_first($sql);
    		if(isset($check['TipologiaBusDefaultId']) && $check['TipologiaBusDefaultId']>0) {
    			$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
    			(select IFNULL((select SUM(c1.NumeroPax)
    			from RT_CorsaPaxTratta c1
    			where
    			c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.TrattaId = ".$tratta['TrattaId']."
				    group by c1.CorsaId , c1.DataPartenza , TrattaId),0))
				   ) AS `PostiTotali`
				from RT_Tratta c
				join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
				where c.TrattaId = ".$tratta['TrattaId'];
    			$tempR1 = $db->query_first($sql);
    		} else {
    			$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
    			(select IFNULL((select SUM(c1.NumeroPax)
    			from RT_CorsaPax c1
    			where
    			c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza'
    			group by c1.CorsaId , c1.DataPartenza),0))
    			) AS `PostiTotali`
    			from RT_Corsa c
    			join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
    			where c.CorsaId = $CorsaId";
    			$tempR1 = $db->query_first($sql);
    		}
    		
    		$disponibili = intval($tempR1['PostiTotali']) - intval($postiOccupati);
    		$totali = intval($tempR1['PostiTotali']);
    	} else {
    		$sql = "select IFNULL((select
    		count(0)
    		from
    		`RT_PrenotazionePercorso`
    		join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
    		join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
    		and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
    		and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
    		join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
    		left join `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
    		left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
    		where
    		((`RT_Prenotazione`.`Cancella` = 0)
    		and (`RT_PrenotazionePercorso`.`Cancella` = 0)
    		and (`RT_PrenotazionePercorso`.`Stato` = 1)
    		and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
    		and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
    		and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
    		and (`tb`.`OccupaPosto` = 1))
    		and `RT_PrenotazionePercorso`.`CorsaId` = $CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataPartenza'
    		group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0) as PostiRealmentePrenotati";
    		$tempR1 = $db->query_first($sql);
    		if(isset($tempR1['PostiRealmentePrenotati'])){
    			$postiRealmentePrenotati = $tempR1['PostiRealmentePrenotati'];
    		} else {
    			$postiRealmentePrenotati = 0;
    		}
    		$sql = "select IFNULL((select SUM(c1.NumeroPax)
    		from RT_CorsaPax c1
    		where
    		c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1
    		group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
    		$tempR = $db->query_first($sql);
    		if(isset($tempR['PostiAggiunti'])){
    			$postiCorsaAggiunti = $tempR['PostiAggiunti'];
    		} else {
    			$postiCorsaAggiunti = 0;
    		}
    		
    		$disponibili = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
    		$totali = $postiCorsaDefault + $postiCorsaAggiunti;
    	}
    	
    	//controllo posti inizio tratta
    	$inizio = true;
    	if(isset($pickup)) {
	    	$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK 
	    	WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$pickup and TrattaStato=1 order by TrattaPeso desc ";
	    	$arr_fermate=$db->fetch_array($sql);
	    	$trattaPartenza = $arr_fermate[0]['TrattaId'];
	    	
	    	$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
	    	where CorsaId = $CorsaId and DataPartenza = '$DataPartenza' and TrattaId = $trattaPartenza ";
	    	$tempR = $db->query_first($sql);
	    	if(isset($tempR['Posti'])) {
	    		$tempOccupatiInizio = $tempR['Posti'];
	    	} else {
	    		$tempOccupatiInizio = 0;
	    	}
	    	$sql = "Select ($postiCorsaDefault +
	    	(select IFNULL((select SUM(c1.NumeroPax)
	    	from RT_CorsaPaxTratta c1
	    	where
	    	c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.TrattaId = ".$trattaPartenza." and c1.OdcIdRef = 1
												    group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
												   ) AS `PostiTotali`
												from RT_Tratta c
												where c.TrattaId = ".$trattaPartenza;
	    	$tempR1 = $db->query_first($sql);
	    	$tempInizioTot = $tempR1['PostiTotali'];
	    	$tempDisponibili = $tempInizioTot - $tempOccupatiInizio;
	    	if($tempDisponibili  > 0) {
	    		if($tempDisponibili < $disponibili) {
	    			$disponibili = $tempDisponibili;
	    		} else {
	    			$disponibili = $disponibili;
	    		}
	    	} else {
	    		$disponibili = 0;
	    		$inizio = false;
	    	}
    	}

    	$row = array();
    	$row['LineaId'] = $LineaId;
    	$row['PostiRealmenteDisponibili'] = $disponibili;
    	$row['PostiTotali'] = $totali;
    	
		$err = "Posti non disponibili";
		if (!empty($row['LineaId'])) {
			$pd = $row['PostiRealmenteDisponibili'];
			$pcDefault = $row['PostiTotali'];
			$lineaId = $row['LineaId'];
			$check=($pd-$NumeroPaxRichiesti);
			if (!($check>=0) ){
				if(isset($pickup) && isset($dropoff) && $inizio){
					$grafo1 = new DisponibilitaGraph($lineaId, $CorsaId, $DataPartenza, $db, 200, false);
					$p = $grafo1->getPostiDisponibili($pickup, $dropoff, $row['PostiTotali']);

					$sql = "select * from RT_MaxDisponibilitaPostiCron where
							CorsaId = $CorsaId and LineaId = $lineaId and DataPartenza = '$DataPartenza'";
					$rtemp = $db->query_first($sql);
					if( $p > $row['PostiTotali']){
						echo "posti occupati: ".$rtemp['Posti']."-------";
					} else {
						echo "posti occupati: ".$p."-------";
					}
					$t = $pcDefault - $p;
					if($t <= 0 || ($NumeroPaxRichiesti > $t)){ 
						$err="Per la corsa di ".$Percorso." non sono disponibili i posti richiesti. \n";
					} else {
						$err="";
					}
				} else {
					$err="Per la corsa di ".$Percorso." non sono disponibili i posti richiesti.\n";
				}
			}else{
				$err="";
			}
		}
    } else {
    	$err = "";
    }

    return $err;

}

public function CheckDisponibilitaPaxRes($DataPartenza,$CorsaId,$NumeroPaxRichiesti,$Percorso, $pickup = null, $dropoff = null)
{
	global $user;
	$db=$this->conn;
	$ritornoOpen = $_POST['Prenotazione']['RitornoOpen'];
	if($ritornoOpen == 0){
		// 		$sql="select PostiRealmenteDisponibili, PostiTotali, LineaId from RT_ViewElencoGestioneOperativita_new where OdcIdRef=$user->OdcId
		// 	             and CorsaId=$CorsaId and AppCalendarioData='$DataPartenza' limit 1" ;


		$sql = "select
		`RT_Corsa`.`LineaId` AS `LineaId`,
		(`RT_TipologiaBus`.`TotalePosti` + (select IFNULL((select SUM(c.NumeroPax)
		from
		RT_CorsaPax c
		where
		c.Cancella = 0
		and c.CorsaId = $CorsaId
		and c.DataPartenza = '$DataPartenza'
		and c.OdcIdRef = $user->OdcId
		group by c.CorsaId , c.DataPartenza , c.OdcIdRef),0))) AS `PostiTotali`,

		((`RT_TipologiaBus`.`TotalePosti` + (
		select IFNULL((select SUM(c.NumeroPax)
		from
		RT_CorsaPax c
		where
		c.Cancella = 0
		and c.CorsaId = $CorsaId
		and c.DataPartenza = '$DataPartenza'
		and c.OdcIdRef = $user->OdcId
		group by c.CorsaId , c.DataPartenza , c.OdcIdRef),0)))
		- (
		select IFNULL((select
		COUNT(*) as Tot
		from
		(((((((`RT_PrenotazioneDettaglio` `pd`
		left join `RT_Prenotazione` `p` ON ((`pd`.`PrenotazioneId` = `p`.`PrenotazioneId`)))
		left join `RT_AppPrenotazioneStato` `apps` ON ((`p`.`PrenotazioneStato` = `apps`.`PrenotazioneStatoId`)))
		left join `Operatore` `o` ON ((`p`.`OpeIns` = `o`.`OperatoreId`)))
		left join `Gestore` `g` ON ((`o`.`GestoreId` = `g`.`GestoreId`)))
		left join `RT_PrenotazioneDettaglioCompleto` `rdc` ON (((`p`.`PrenotazioneId` = `rdc`.`PrenotazioneId`)
		and (`rdc`.`PrenotazioneId` = `pd`.`PrenotazioneId`)
		and (`rdc`.`PrenotazioneNumero` = `pd`.`PrenotazioneNumero`)
		and (`rdc`.`DataPartenza` = `pd`.`DataPartenza`))))
		left join `RT_PrenotazioneBiglietto` `pb` ON (((`pb`.`PrenotazioneId` = `p`.`PrenotazioneId`)
		and (`pb`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
		left join `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBigliettoId` = `pb`.`TipologiaBigliettoId`)
		)))
		where
		(apps.OccupaPosti = 1
		and (`pd`.`Escludi` <> 1)
		and (`pd`.`Rimborso` <> 1))
		and `tp`.`OccupaPosto` = 1
		and (`rdc`.Codice not like 'E-%' or `rdc`.Codice is null)
			
		and pd.CorsaId=$CorsaId and pd.DataInizioItinerario='$DataPartenza'),0))) AS `PostiRealmenteDisponibili`


		from
		`RT_Corsa`
		join `RT_CorsaSettimana` ON (`RT_Corsa`.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
		join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
		join `RT_AppCalendario` ON (`RT_AppSettimana`.`AppSettimanaGiorno` = `RT_AppCalendario`.`GiornoSettimana`)

		join `RT_TipologiaBus` ON (`RT_Corsa`.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
		where
		`RT_Corsa`.`Cancella` = 0
		and `RT_AppCalendario`.`AppCalendarioData` >= `RT_Corsa`.`AttivaDal`
		and `RT_AppCalendario`.`AppCalendarioData` <= `RT_Corsa`.`AttivaAl`
		and ((`RT_AppCalendario`.`Feriale` = `RT_Corsa`.`IncludiFeriale` AND `RT_Corsa`.`IncludiFeriale` = 1)
		or (`RT_AppCalendario`.`Prefestivo` = `RT_Corsa`.`IncludiPrefestivo` AND `RT_Corsa`.`IncludiPrefestivo` = 1)
		or (`RT_AppCalendario`.`Festivo` = `RT_Corsa`.`IncludiFestivo` AND `RT_Corsa`.`IncludiFestivo` = 1))

		and `RT_Corsa`.`OdcIdRef`=$user->OdcId and `RT_Corsa`.CorsaId=$CorsaId and `RT_AppCalendario`.`AppCalendarioData`='$DataPartenza'
		group by `RT_Corsa`.`CorsaId` , `RT_AppCalendario`.`AppCalendarioData`";

		$row= $db->query_first($sql);

		if (!empty($row['LineaId'])) {
			// 			echo "eccomi";
			$pd = $row['PostiRealmenteDisponibili'];
			$pcDefault = $row['PostiTotali'];
			$lineaId = $row['LineaId'];
			$check=($pd-$NumeroPaxRichiesti);
			if (!($check>=0)){
				return 1;
			}else{
				return 0;
			}
		}
	} else {
		return 0;
	}
}









public function GetTratte($CorsaId,$FermataP,$FermataD)
{
     global $user;
        $db=$this->conn;
    $sql="select distinct TrattaId,NodoPeso from RT_ViewTratteFermateOrario where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and (FermataId=$FermataP)" ;
  
  $ArrObject = $db->fetch_array($sql);
  $tratta_p=$ArrObject[0]['TrattaId'];
  $nodo_p=$ArrObject[0]['NodoPeso'];
  
  $sql="select distinct TrattaId,NodoPeso from RT_ViewTratteFermateOrario where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and (FermataId=$FermataD)" ;
  $ArrObject = $db->fetch_array($sql);
  $tratta_d=$ArrObject[0]['TrattaId'];
   $nodo_d=$ArrObject[0]['NodoPeso'];
   $sql="select distinct TrattaId from RT_ViewTratteFermateOrario where NodoPeso>$nodo_p and NodoPeso<$nodo_d and IsInterscambio=1";
      
       
   
   if ($tratta_p==$tratta_d)
   {
    
       $arr_tratte[0]['TrattaId']=$tratta_p;
   }
       
   else
   {
       
       $sql="select distinct TrattaId from RT_ViewTratteFermateOrario where (NodoPeso>$nodo_p and NodoPeso<$nodo_d and IsInterscambio=1) or (TrattaId=$tratta_p or TrattaId=$tratta_d)  order by NodoPeso,TrattaPeso";
       $arr_tratte = $db->fetch_array($sql);
       //print_r($arr_tratte);
       
   }
   
   return $arr_tratte;
    
}   



public function GetListini($arr_tratte,$FermataP,$FermataD,$CorsaId)
{
     global $user;
        $db=$this->conn;
    
        
     $NumeroTratte=sizeof($arr_tratte);
     if ($NumeroTratte==1)
     {
            $TrattaId=$arr_tratte[0]['TrattaId'];
            $sql="select distinct ListinoId from RT_CorsaTariffa where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and FermataPickup=$FermataP and FermataDropOff=$FermataD and TrattaId=$TrattaId order by CorsaTariffaId desc" ;
            
            $ArrObject = $db->fetch_array($sql);
            $listino_id[$TrattaId]['ListinoId']=$ArrObject[0]['ListinoId'];
            return ($listino_id);
     }
     else
     {
         $TrattaPartenza=$arr_tratte[0]['TrattaId'];
       $sql="SELECT
RT_CorsaTariffa.*
FROM
RT_CorsaTariffa
INNER JOIN RT_Fermata ON RT_CorsaTariffa.FermataDropOff = RT_Fermata.FermataId
WHERE
RT_Fermata.IsInterscambio = 1 AND
RT_CorsaTariffa.Cancella = 0 AND
RT_CorsaTariffa.CorsaId = $CorsaId AND
RT_CorsaTariffa.FermataPickup = $FermataP AND
RT_CorsaTariffa.TrattaId = $TrattaPartenza ORDER BY
RT_CorsaTariffa.CorsaTariffaId DESC Limit 1";  
       
       
     
    

  $ArrObject = $db->fetch_array($sql);
  $listino_id[$TrattaPartenza]['ListinoId']=$ArrObject[0]['ListinoId'];
  
  
  
  
  
    

$inizio_array=0;
$fine_array=sizeof($arr_tratte)-1;

$conta=1;

while ($conta<$fine_array)
{
    $tratta_intermedia=$arr_tratte[$conta]['TrattaId'];
    
    $sql="SELECT
RT_CorsaTariffa.*
FROM
RT_CorsaTariffa
INNER JOIN RT_Fermata ON RT_CorsaTariffa.FermataDropOff = RT_Fermata.FermataId
WHERE
(RT_Fermata.IsInterscambio = 1 AND
RT_Fermata.Cancella = 0 and RT_Fermata.Stato=1) and RT_CorsaTariffa.CorsaId = $CorsaId AND
    RT_CorsaTariffa.TrattaId = $tratta_intermedia 

ORDER BY
RT_CorsaTariffa.CorsaTariffaId DESC Limit 1";
    
    
    
    $ArrObject = $db->fetch_array($sql);
  $listino_id[$tratta_intermedia]['ListinoId']=$ArrObject[0]['ListinoId'];
    $conta++;
    
    
}


  
  
  $TrattaDestinazione=$arr_tratte[sizeof($arr_tratte)-1]['TrattaId'];
  
  
  $sql="SELECT
RT_CorsaTariffa.*
FROM
RT_CorsaTariffa
INNER JOIN RT_Fermata ON RT_CorsaTariffa.FermataPickup = RT_Fermata.FermataId
WHERE
RT_CorsaTariffa.CorsaId = $CorsaId AND
RT_CorsaTariffa.TrattaId = $TrattaDestinazione AND
RT_CorsaTariffa.FermataDropOff = $FermataD and 
RT_CorsaTariffa.Cancella = 0 
ORDER BY RT_Fermata.FermataPeso asc,RT_CorsaTariffa.CorsaTariffaId DESC  limit 1";
  
 $ArrObject = $db->fetch_array($sql);
  $listino_id[$TrattaDestinazione]['ListinoId']=$ArrObject[0]['ListinoId'];

  // echo($sql);

         
          return $listino_id;     
     }
     
        
 
        
    
}   


public function CalcolaPrezzo($ArrObject,$arr_listini,$arr_tratte)
{
$prezzo_totale="110,25";
return $prezzo_totale;
    
} 


public function ScriviTbTariffe($arr_listini,$arr_tratte,$corsaritorno)
{
      global $user;
    $db=$this->conn;
    $PrenotazioneId=$this->Id;
    $storico=new StoricoOperazioni();
    $storico->conn=$db;
    $conritorno=0;
    if ($corsaritorno>0)
        $conritorno=1;
    
    
     $sql="Select * from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0";
     $ArrObject = $db->fetch_array($sql);
    
     
     
     $ntb=0;
     
    
     
     while($ntb<sizeof($ArrObject))
     {
         $TipoBigliettoId=$ArrObject[$ntb]['TipologiaBigliettoId'];
         $TipoBiglietto=$ArrObject[$ntb]['TipologiaBiglietto'];
         $NumeroPaxBiglietto=$ArrObject[$ntb]['NumeroPax'];
         
         // per ogni tratta
         $TotalePerTipologia=0;
         $ntratte=0;
            while($ntratte<sizeof($arr_tratte))
            {

                $TrattaId=$arr_tratte[$ntratte]['TrattaId'];
                $ListinoId=$arr_listini[$TrattaId]['ListinoId'];

                $sql="Select * from RT_ListinoBiglietto where ListinoId=$ListinoId and BigliettoId=$TipoBigliettoId and  OdcIdRef=$user->OdcId and Cancella=0 order by ListinoBigliettoId desc";
                
               
                $ArrPrezzo = $db->query_first($sql);
                $PrezzoPax=0;
                $listinoNome="";
                
                if (!empty($ArrPrezzo['ListinoId']))
                {
                  $PrezzoPax=$ArrPrezzo['Prezzo'];  
                  $TotalePerTipologia+=$NumeroPaxBiglietto*$PrezzoPax;
                  $l=new Listino($ListinoId);
                  $l->conn=$db;
                  $l->inizializzaDatiGenerali();
                  $arr_l=$l->DatiGenerali;
                  $listinoNome=$arr_l['ListinoNome'];
                   
                }
                 $d1=null;
                 unset($d1);
                $d1['PrenotazioneId']=$PrenotazioneId;
                $d1['TipologiaBigliettoId']=$TipoBigliettoId;
                $d1['TipologiaBiglietto']=$TipoBiglietto;
                $d1['ListinoId']=$ListinoId;
                $d1['ListinoPrezzo']=$PrezzoPax;
                $d1['ListinoNome']=$listinoNome;
                $d1['TrattaId']=$TrattaId;
                $d1['ConRitorno']=$conritorno;
              

                $d1=$storico->operazioni_insert($d1,$user);
                $lastidA=$db->insert("RT_PrenotazioneTariffa", $d1);
             
                $ntratte++;
            }
            
            // update ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¹
           /* if (($TotalePerTipologia>0) and ($TipoBigliettoId>0))
            {
                 $data_upd['PrezzoTotalePax']=$TotalePerTipologia;
                 $db->update("RT_PrenotazioneBiglietto",$data_upd,"TipologiaBigliettoId=$TipoBigliettoId and PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId");
  
                
            }*/
          
             
         
         
         
         $ntb++;
     }
    
    // seleziono gli id delle tipologie di biglietti prenotati
    
    // per ogni tipobiglietto RT_ListinoBiglietto e prendo il prezzo
    
    // per ogni tratta faccio un insert
    
    return true;
    
}

public function ScriviTbPostoScelto($arr_posti_scelti,$arr_posti_scelti_inf,$CorsaId,$DataPartenza)
{
    global $user;
    $db=$this->conn;
    $PrenotazioneId=$this->Id;
    $storico=new StoricoOperazioni();
    
    
     $sql="Select * from RT_PrenotazioneNumero where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0 order by PrenotazioneNumeroId asc";
   
     $ArrObject = $db->fetch_array($sql);
    
    
    
    $data=$arr_posti_scelti;
    $ck="";
    
    if (sizeof($data>0))
    {
        $quanti=0; 
     foreach ($data as $chiave => $valore)
    {
        
     $ck=$chiave;
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     $arr_chiave=explode('_', $chiave);
     
         $nPiano=$arr_chiave[0];
         $nRiga=$arr_chiave[1];
         $nColonna=$arr_chiave[2];
         $CorsaSel=$arr_chiave[3];
         $TipologiaBusId=$arr_chiave[4];
           $d1=null;
           $DescrizionePosto="lato finestrino";
        if ($CorsaSel==$CorsaId)
        {
            
            
            
            $sql="Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$nPiano and Riga=$nRiga and Colonna=$nColonna and TipologiaBusId=$TipologiaBusId and OdcIdRef=$user->OdcId";
           // echo ($sql);
            
            $row1 = $db->query_first($sql);
            $NumeroPosto="";
            $DescrizionePosto="";
    
             if (!empty($row1['TipologiaBusId']))
            {
             $NumeroPosto=$row1['NumeroPosto'];
             $DescrizionePosto=$row1['DescrizionePosto'];
            }
            $preferenza_inferiore=$arr_posti_scelti_inf[$ck];
           
            
             $d1['CorsaId']=$CorsaId;
             $d1['DataPartenza']=$DataPartenza;
             $d1['Piano']=$nPiano;
             $d1['PreferenzaPiano']=$preferenza_inferiore;
             $d1['Riga']=$nRiga;
             $d1['Colonna']=$nColonna;
             $d1['Posto']=$valore;
             $d1['DescrizionePosto']=$DescrizionePosto;
             $d1['PrenotazioneId']=$PrenotazioneId;
             $d1['TipoPrenotazione']=1;
             $d1['PrenotazioneNumeroId']=$ArrObject[$quanti]['PrenotazioneNumeroId'];
             
             
           
             
            // print_r($d1);
             if ($d1['PrenotazioneNumeroId']>0)
             {
             $d1=$storico->operazioni_insert($d1,$user);
             $lastidA=$db->insert("RT_PrenotazionePosto", $d1);
             }
        }
                $quanti++;

     }
    }
    
    
}    
        

public function ScriviTbBiglietti($data,$scrivi,$arr_riduzione,$arr_aumento,$arr_tratte,$arr_listini)
{
    global $user;
    $db=$this->conn;
    $PrenotazioneId=$this->Id;
    $storico=new StoricoOperazioni();
    
    
    $storico->conn=$db;
    $TotaleBiglietti=0;
    foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     
     
     $arr_chiave=explode('_', $chiave);
     
    
        $TipoBigliettoId=$arr_chiave[0];
        $DescrizioneBiglietto=$arr_chiave[1];
        $pax=$valore;
        $d1['PrenotazioneId']=$PrenotazioneId;
        $d1['TipologiaBigliettoId']=$TipoBigliettoId;
        $d1['TipologiaBiglietto']=$DescrizioneBiglietto;
        $d1['NumeroPax']=$pax;
       
        if ($valore>0)
        {
            
            $tot_aumento=$arr_aumento[$TipoBigliettoId];
            $tot_riduzione=$arr_riduzione[$TipoBigliettoId];
            
            
         
            
            if ($scrivi) 
                {
                 $d1['RiduzionePax']=$tot_riduzione;
                 $d1['AumentoPax']=$tot_aumento;
                
                
                 $ntratte=0;
       $PrezzoPax=0;     
       while($ntratte<sizeof($arr_tratte))
            {
                $TrattaId=$arr_tratte[$ntratte]['TrattaId'];
                $ListinoId=$arr_listini[$TrattaId]['ListinoId'];  
                $sql="Select * from RT_ListinoBiglietto where ListinoId=$ListinoId and BigliettoId=$TipoBigliettoId and  OdcIdRef=$user->OdcId and Cancella=0";
                
                  
               
                $ArrPrezzo = $db->query_first($sql);
          
                $listinoNome="";
                
                if (!empty($ArrPrezzo['ListinoId']))
                  $PrezzoPax+=$ArrPrezzo['Prezzo'];  
                  $ntratte++;
            }
              
                
                  $d1['PrezzoPax']=$PrezzoPax;
                  $d1['PrezzoBasePax']=$PrezzoPax*$pax;
                  $d1['PrezzoTotalePax']=$d1['PrezzoBasePax']-$tot_riduzione+$tot_aumento;
                  $d1=$storico->operazioni_insert($d1,$user);
                 
                  
                  
                  }
            $lastidA=$db->insert("RT_PrenotazioneBiglietto", $d1);
            $TotaleBiglietti+=$valore;
        }
    }
    $data_upd['TotalePaxPrenotati']=$TotaleBiglietti;
     if ($scrivi) 
    $db->update("RT_Prenotazione",$data_upd,"PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId");
    
    return $TotaleBiglietti;
}


public function ScriviTbTratte($CorsaId,$arrTratte,$TrattaDirezione)
{
    global $user;
    $db=$this->conn;
    $PrenotazioneId=$this->Id;
    $storico=new StoricoOperazioni();
    $storico->conn=$db;
    
  
    
    
    $i=0;
    while ($i< sizeof($arrTratte))
    {
    $TrattaId=$arrTratte[$i]['TrattaId'];
    $tratta=new Tratta($TrattaId);
    $tratta->conn=$db;
    $tratta->inizializzaDatiGenerali();
    $TrattaNome=$tratta->DatiGenerali['TrattaNome'];
    $TrattaPeso=$tratta->DatiGenerali['TrattaPeso'];
    $TrattaNodo=$tratta->DatiGenerali['NodoPeso'];
    
    $prenotazione_tratte=null;
    unset($prenotazione_tratte);
    $prenotazione_tratte['PrenotazioneId']=$PrenotazioneId;
    $prenotazione_tratte['TrattaId']=$TrattaId;
    $prenotazione_tratte['TrattaNome']=$TrattaNome;
    $prenotazione_tratte['TrattaPeso']=$TrattaPeso;
    $prenotazione_tratte['TrattaNodo']=$TrattaNodo;
    $prenotazione_tratte['TrattaDirezione']=$TrattaDirezione;   
    $prenotazione_tratte['CorsaId']=$CorsaId;   
    
    
    
    $chiave=$TrattaId."_".$CorsaId."_".$TrattaDirezione;
    //$prenotazione_tratte['TrattaNote']=$arrTrattaNote[$chiave];
    $prenotazione_tratte=$storico->operazioni_insert($prenotazione_tratte,$user);
    $lastidA=$db->insert("RT_PrenotazioneTratta", $prenotazione_tratte);   
    //print_r($prenotazione_tratte);
    
    $i++;
    }
    
    return true;
}



 
public function ScriviTbPercorso($CorsaId,$Pickup,$DropOff,$DataCorsa,$Direzione,$StatoPrenotazione,$arr_note,$NumeroTotalePax)
{
    
global $user;
$db=$this->conn;
$PrenotazioneId=$this->Id;
$storico=new StoricoOperazioni();
$storico->conn=$db;


$corsa=new Corsa($CorsaId);
$corsa->conn=$db;
$corsa->inizializzaDatiGenerali();
$arr_corsa=$corsa->DatiGenerali;
$CorsaNome=$arr_corsa['CorsaNome'];
$OrarioPartenza=$arr_corsa['OrarioPartenza'];

$fermata=new Fermata($Pickup);
$fermata->conn=$db;
$fermata->inizializzaDatiGenerali();
$arr_fermata=$fermata->DatiGenerali;

$fermata1=new Fermata($DropOff);
$fermata1->conn=$db;
$fermata1->inizializzaDatiGenerali();
$arr_fermata1=$fermata1->DatiGenerali;
$data_prenotazione_percorso=null;

$data_prenotazione_percorso['PrenotazioneId']=$PrenotazioneId;
$data_prenotazione_percorso['Direzione']=$Direzione;
$data_prenotazione_percorso['CorsaId']=$CorsaId;
$data_prenotazione_percorso['CorsaNome']=$CorsaNome;
$data_prenotazione_percorso['CorsaDataPartenza']=$DataCorsa;
$data_prenotazione_percorso['CorsaOrarioPartenza']=$OrarioPartenza;
$data_prenotazione_percorso['ComuneSalitaId']=$arr_fermata['ComuneId'];
$data_prenotazione_percorso['ComuneSalita']=$arr_fermata['Comune'];


$data_prenotazione_percorso['ComuneDiscesaId']=$arr_fermata1['ComuneId'];
$data_prenotazione_percorso['ComuneDiscesa']=$arr_fermata1['Comune'];

$data_prenotazione_percorso['LineaNome']=$arr_fermata['LineaNome'];
$data_prenotazione_percorso['PercorsoNome']=$arr_fermata['PercorsoNome'];

$data_prenotazione_percorso['FermataSalitaId']=$Pickup;
$data_prenotazione_percorso['FermataSalita']=$arr_fermata['FermataNome'];
$data_prenotazione_percorso['FermataDiscesaId']=$DropOff;
$data_prenotazione_percorso['FermataDiscesa']=$arr_fermata1['FermataNome'];

// determina stato prenotazione
$data_prenotazione_percorso['PrenotazioneStato']=$StatoPrenotazione;





$orario=new Orario();
$orario->conn=$db;
$arr_orario_salita=$orario->getOrarioByCorsaFermata($CorsaId, $Pickup);
$arr_orario_discesa=$orario->getOrarioByCorsaFermata($CorsaId, $DropOff);

$giorni_agg_partenza=$arr_orario_salita['GiorniAggiuntivi'];
$orario_partenza=$arr_orario_salita['Orario'];

$giorni_agg_arrivo=$arr_orario_discesa['GiorniAggiuntivi'];
$orario_arrivo=$arr_orario_discesa['Orario'];

$dt=new DT($DataCorsa,"Y-m-d");
$dt->addDays($giorni_agg_partenza);
$DataPartenza=$dt->getDate();
$DataOraPartenza=$DataPartenza." ".$orario_partenza;


$dt=new DT($DataCorsa,"Y-m-d");
$dt->addDays($giorni_agg_arrivo);
$DataArrivo=$dt->getDate();
$DataOraArrivo=$DataArrivo." ".$orario_arrivo;   

$data_prenotazione_percorso['DataOraSalita']=$DataOraPartenza;
$data_prenotazione_percorso['DataOraDiscesa']=$DataOraArrivo;


$data_prenotazione_percorso=$storico->operazioni_insert($data_prenotazione_percorso,$user);



$lastidA=$db->insert("RT_PrenotazionePercorso", $data_prenotazione_percorso);   



//$xx=$this->GeneraCodiciPrenotazione($NumeroTotalePax, $lastidA);


$data_nota['Nota']=$arr_note[$CorsaId."_1"];
$data_nota['TipoNota']='S';
$data_nota['PrenotazioneId']=$PrenotazioneId;
$data_nota['PrenotazionePercorsoId']=$lastidA;

if (!empty($data_nota['Nota']))
$idnote=$db->insert("RT_PrenotazionePercorsoNote",$data_nota);   


$data_nota['Nota']=$arr_note[$CorsaId."_2"];
$data_nota['TipoNota']='D';
$data_nota['PrenotazioneId']=$PrenotazioneId;
$data_nota['PrenotazionePercorsoId']=$lastidA;

if (!empty($data_nota['Nota']))
$idnote=$db->insert("RT_PrenotazionePercorsoNote",$data_nota);   


$data_nota['Nota']=$arr_note[$CorsaId."_3"];
$data_nota['TipoNota']='B';
$data_nota['PrenotazioneId']=$PrenotazioneId;
$data_nota['PrenotazionePercorsoId']=$lastidA;

if (!empty($data_nota['Nota']))
$idnote=$db->insert("RT_PrenotazionePercorsoNote",$data_nota);   

$data_nota['Nota']=$arr_note[$CorsaId."_4"];
$data_nota['TipoNota']='P';
$data_nota['PrenotazioneId']=$PrenotazioneId;
$data_nota['PrenotazionePercorsoId']=$lastidA;

if (!empty($data_nota['Nota']))
$idnote=$db->insert("RT_PrenotazionePercorsoNote",$data_nota); 


$data_nota['Nota']=$arr_note[$CorsaId."_5"];
$data_nota['TipoNota']='G';
$data_nota['PrenotazioneId']=$PrenotazioneId;
$data_nota['PrenotazionePercorsoId']=$lastidA;

if (!empty($data_nota['Nota']))
$idnote=$db->insert("RT_PrenotazionePercorsoNote",$data_nota); 

return true;
}
    
 public function SettaId($PrenotazioneId)
 {
     $this->Id=$PrenotazioneId;
     return true;
     
 }
 
 public function EmettiBigliettiRimborso($PrenotazioneNumeroId, $ImportoRimborso) {
 	global $user, $OperatoreId, $OdcId, $SedeId, $GestoreId;

 	
//  	if (($tipoPercorso==1) or ($tipoPercorso=='1'))
//  	{
 		$OdcId=1;
$GestoreId=1;
$OperatoreId=42;
$SedeId=36;
//  	}
 	
 	$db = $this->conn;
 
 	$PrenotazioneId = $this->Id;
 
 	$storico = new StoricoOperazioni();
 	$storico->conn = $db;
 
 	$sql="Select PercorsoId,LineaId from RT_PrenotazionePercorso where Stato=1 AND Cancella=0 AND PrenotazioneId=$PrenotazioneId";
 	$row = $db->query_first($sql);
 	$PercorsoId = $row['PercorsoId'];
 	$LineaId = $row['LineaId'];
 
 	$sql="Select gp.IdentificativoBiglietto from Gestore g LEFT JOIN GestoreGruppo gp ON (g.GestoreGruppoId = gp.GestoreGruppoId) where GestoreId=$GestoreId";
 	$gestore = $db->query_first($sql);
 	$IdentificativoBiglietto = $gestore['IdentificativoBiglietto'];
 
 	$sql="Select TipoViaggioId from RT_Prenotazione where PrenotazioneId=$PrenotazioneId";
 	$viaggio = $db->query_first($sql);
 	$TipoViaggioId=$viaggio['TipoViaggioId'];
 
 
 	$sql="Select * from RT_PrenotazioneNumero where PrenotazioneNumeroId=$PrenotazioneNumeroId and PrenotazioneId=$PrenotazioneId and OdcIdRef=$OdcId and Cancella=0 order by PrenotazioneNumeroId asc";
 	$prenotazioneNumero = $db->fetch_array($sql);
 
 	foreach ($prenotazioneNumero as $numero) {
 
 		$TipologiaBigliettoId = $numero['TipologiaBigliettoId'];
 
 		$sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$GestoreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
 		$row=$db->query_first($sql);
 		$percentuale_a=0;
 		$fisso_a=0;
 		if ($row['GestoreConvenzioneId']>0)
 		{
 			$percentuale_a=$row['Percentuale'];
 			$fisso_a=$row['Fisso'];
 
 		}
 
 
 		$sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = $TipologiaBigliettoId";
 		$tempTipo = $db->query_first($sql);
 		if($tempTipo['OccupaPosto'] == 1){
 			$progressivo = $this->GetProgressivoTitoloDiViaggio(date('Y'),$PercorsoId);
 			$identificativoServizio = "";
 		} else {
 			$progressivo = $this->GetProgressivoTitoloDiViaggioServizi(date('Y'), $PercorsoId);
 			$identificativoServizio = "E-";
 		}
 		$codice = $identificativoServizio.$IdentificativoBiglietto . '-' . Str_pad($progressivo, 8, "0", STR_PAD_LEFT);
 
 		//         $progressivo = $this->GetProgressivoTitoloDiViaggio();
 		// 		$codice = $IdentificativoBiglietto . '-' . Str_pad($progressivo, 8, "0", STR_PAD_LEFT);
 		$titolo = array();
 		$titolo['PrenotazioneId'] = $PrenotazioneId;
 		$titolo['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
 		$titolo['Codice'] = $codice;
 		$titolo['Anno'] = date('Y');
 		$titolo['Progressivo'] = $progressivo;
 		$titolo['TipoTitolo'] = "R";
 		$titolo['PercorsoId'] = $PercorsoId;
 
 		$sql = "SELECT pn.PrenotazioneNumeroId, pn.PasseggeroId, pp.Cognome, pp.Nome, pp.SessoId, pp.Eta, pn.TipologiaBigliettoId, tb.TipologiaBiglietto
 		FROM RT_PrenotazioneNumero pn
 		LEFT JOIN RT_PrenotazionePasseggeri pp ON (pn.PasseggeroId = pp.PrenotazionePasseggeroId)
 		LEFT jOIN RT_TipologiaBiglietto tb ON (tb.TipologiaBigliettoId = pp.TipoBigliettoId)
 		WHERE pn.PrenotazioneNumeroId = $PrenotazioneNumeroId";
 
 		$datiPasseggero = $db->query_first($sql);
 		$titolo['PasseggeroId'] = $datiPasseggero['PasseggeroId'];
 		$titolo['Cognome'] = $datiPasseggero['Cognome'];
 		$titolo['Nome'] = $datiPasseggero['Nome'];
 		$titolo['SessoId'] = $datiPasseggero['SessoId'];
 		$titolo['Eta'] = $datiPasseggero['Eta'];
 		$titolo['TipologiaBigliettoId'] = $datiPasseggero['TipologiaBigliettoId'];
 		$titolo['TipologiaBiglietto'] = $datiPasseggero['TipologiaBiglietto'];
 
 		$titolo['ImportoTitolo'] = $ImportoRimborso;
 		/*$titolo['PercentualeAgenzia'] = $percentuale_a;
 		 $titolo['FissoAgenzia'] = $fisso_a;
 		 $titolo['ImportoAgenzia'] = $fisso_a+($titolo['ImportoTitolo']*$percentuale_a)/100;
 		 */
 		$titolo = $storico->operazioni_insert($titolo, $user);
 		$titoloId=$db->insert("RT_PrenotazioneTitolo", $titolo);
 
 
 		/*inserimento IVA*/
 		$sql="select * from RT_ViewImportiPerIva003 where PrenotazioneTitoloId=$titoloId";
 		$ivaarr = $db->fetch_array($sql);
 		if(count($ivaarr) > 0) {
 		    foreach ($ivaarr as $arr_iva) {
 		        $Scorporo=0;
 		        $ConfineId=0;
 		        $Aliquota=0;
 		        $Importo=$arr_iva['ImportoTitolo'];
 		        
 		        if ($TipoViaggioId==2)
 		            $Importo=$Importo/2;
 		            
 		            $KmTot=$arr_iva['KmPercorsi'];
 		            if ($arr_iva['np']==$arr_iva['nd']) {
 		                $TotImponibile=$Importo;
 		                $TotIva=$TotImponibile-($TotImponibile/$arr_iva['ScorporoP']);
 		                $KmSuConfine=$KmTot;
 		                $Scorporo=$arr_iva['ScorporoD'];
 		                $ConfineId=$arr_iva['cidp'];
 		                $Aliquota=$arr_iva['AliquotaPartenza'];
 		            } else {
 		                if (!is_null($arr_iva['ScorporoP'])) {
 		                    $Scorporo=$arr_iva['ScorporoP'];
 		                    $ConfineId=$arr_iva['cidp'];
 		                    $Aliquota=$arr_iva['AliquotaPartenza'];
 		                } else {
 		                    $Scorporo=$arr_iva['ScorporoD'];
 		                    $ConfineId=$arr_iva['cidd'];
 		                    $Aliquota=$arr_iva['AliquotaDestinazione'];
 		                }
 		                
 		                
 		                $KmPercorsiTotali= $arr_iva['KmPercorsi'];
 		                $KmSuConfine=$arr_iva['KmTotali'];
 		                $TotImponibile=(($Importo*$KmSuConfine)/$KmPercorsiTotali);
 		                $TotIva=$TotImponibile-($TotImponibile/$Scorporo);
 		                
 		            }
 		            if($KmSuConfine > 0) {
 		                if($Aliquota == 0) {
 		                    $TotIva = 0;
 		                }
 		                $diva=array();
 		                $Importo=  number_format($Importo,2);
 		                $Imponibile=  number_format($TotImponibile,2);
 		                $TotIva=  number_format($TotIva,2);
 		                $diva['ConfineId']=$ConfineId;
 		                $diva['PrenotazioneTitoloId']=$titoloId;
 		                $diva['KmPercorsiTotale']=$KmTot;
 		                $diva['KmPercorsiTerritorio']=$KmSuConfine;
 		                $diva['AliquotaIva']=$Aliquota;
 		                $diva['ImportoTitolo']=$Importo;
 		                $diva['ImportoTitoloPerConfine']=$Imponibile;
 		                $diva['ImportoIvaConfine']=$TotIva;
 		                $diva = $storico->operazioni_insert($diva, $user);
 		                $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
 		            }
 		    }
 		} else if(Config::$ivaTerritoriaItaliano){
 		    //se non è presente in tabella alcun record il viaggio avviene su territorio italiano
 		    $Importo=$titolo['ImportoTitolo'];
 		    if ($TipoViaggioId==2)
 		        $Importo=$Importo/2;
 		        
 		        $sql = "SELECT * FROM RT_Confine WHERE ConfineId = 2";
 		        $confine=$db->query_first($sql);
 		        
 		        $diva=array();
 		        $Imponibile = $Importo;
 		        $TotImponibile = $Imponibile;
 		        $TotIva = $TotImponibile-($TotImponibile/$confine['ValoreScorporo']);
 		        $Importo=  number_format($Importo,2);
 		        $Imponibile=  number_format($TotImponibile,2);
 		        $TotIva=  number_format($TotIva,2);
 		        $diva['ConfineId']=2;
 		        $diva['PrenotazioneTitoloId']=$titoloId;
 		        $diva['KmPercorsiTotale']=$kmArray['KmPercorsiAndata'];
 		        $diva['KmPercorsiTerritorio']=$kmArray['KmPercorsiAndata'];
 		        $diva['AliquotaIva']=$confine['Aliquota'];
 		        $diva['ImportoTitolo']=$Importo;
 		        $diva['ImportoTitoloPerConfine']=$Imponibile;
 		        $diva['ImportoIvaConfine']=$TotIva;
 		        $diva = $storico->operazioni_insert($diva, $user);
 		        $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
 		}
 		/* FINE inserimento IVA */
 
 
 
 		$provv=array();
 		$provv['PrenotazioneTitoloId']=$titoloId;
 		$provv['GestoreId']=$GestoreId;
 
 		//$provv['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
 		$provv['PercentualeAgenzia'] = $percentuale_a;
 		$provv['FissoAgenzia'] = $fisso_a;
 		$provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoTitolo']*$percentuale_a)/100;
 		$provv = $storico->operazioni_insert($provv, $user);
 		$provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
 
 
 		$finito=false;
 		$gestorePadreId=$GestoreId;
 		$conta=0;
 		while($finito==false || $conta<100)
 		{
 			if($gestorePadreId>0){
	 			$gestoreNew=new Gestore($gestorePadreId);
	 			$gestoreNew->conn=$db;
	 			$gestoreNew->inizializzaDatiGenerali(null);
	 			$gestorePadreId=$gestoreNew->GestoreDatiGenerali['GestorePadre'];
	 
	 			if ($gestorePadreId>0)
	 			{
	 				$sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$gestorePadreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
	 				$row=$db->query_first($sql);
	 				$percentuale_a=0;
	 				$fisso_a=0;
	 				if ($row['GestoreConvenzioneId']>0)
	 				{
	 					$percentuale_a=$row['Percentuale'];
	 					$fisso_a=$row['Fisso'];
	 				}
	 
	 				$provv=array();
	 				$provv['PrenotazioneTitoloId']=$titoloId;
	 				$provv['GestoreId']=$gestorePadreId;
	 
	 				//$provv['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
	 				$provv['PercentualeAgenzia'] = $percentuale_a;
	 				$provv['FissoAgenzia'] = $fisso_a;
	 				$provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoTitolo']*$percentuale_a)/100;
	 				$provv = $storico->operazioni_insert($provv, $user);
	 				$provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
	 
	 
	 
	 			}
	 			else
	 				$finito=true;
	 
 			} else {
 				$finito=true;
 			}
 			$conta++;
 		}
 
 
 
 	}
 
 	$data = array();
 	$data['PrenotazioneStato'] = 3;
 	$data=$storico->operazioni_update($data,$user);
 
 	$result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$OdcId and PrenotazioneId=$PrenotazioneId");
 
 	$result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$OdcId and PrenotazioneId=$PrenotazioneId");
 }
 
 
 
 public function isRimborsata($PrenotazioneId) {
 	global $user, $OperatoreId, $OdcId, $SedeId, $GestoreId;

 	
//  	if (($tipoPercorso==1) or ($tipoPercorso=='1'))
//  	{
 		$OdcId=1;
$GestoreId=1;
$OperatoreId=42;
$SedeId=36;
//  	}
 	
 	$db = $this->conn;
 	
 	$storico = new StoricoOperazioni();
 	$storico->conn = $db;
 
 	$sql = "SELECT count(*) Prenotazioni
 	FROM RT_PrenotazioneDettaglio pd
 	WHERE pd.PrenotazioneId = $PrenotazioneId AND pd.Rimborso = 0
 	GROUP BY pd.PrenotazioneId";
 	$row = $db->query_first($sql);
 	$prenotazioni = $row['Prenotazioni'];
 
 	$sql = "SELECT count(*) Rimborsate
 	FROM RT_PrenotazioneDettaglio pd
 	WHERE pd.PrenotazioneId = $PrenotazioneId AND pd.Rimborso = 1
 	GROUP BY pd.PrenotazioneId";
 	$row = $db->query_first($sql);
 	$rimborsate = $row['Rimborsate'];
 
 	// se le prenotazioni sono uguali alle rimborsate cambio lo stato a rimborsata
 	if ($prenotazioni == $rimborsate) {
 		$dataPrenotazione['PrenotazioneStato'] = 7;
 		$dataPrenotazione = $storico->operazioni_update($dataPrenotazione, $user);
 		$db->update("RT_Prenotazione", $dataPrenotazione, "PrenotazioneId = " . $PrenotazioneId);
 			
 		$dataPercorso['PrenotazioneStato'] = 7;
 		$dataPercorso = $storico->operazioni_update($dataPercorso, $user);
 		$db->update("RT_PrenotazionePercorso", $dataPercorso, "PrenotazioneId = " . $PrenotazioneId);
 	}
 }

    public function GetTotaliPrenotazione() {
        global $user;
        $db = $this->conn;
        $row=null;
        if ($this->DatiGenerali['Multi']) {
            $sql = "SELECT SUM(TotalePrenotazione) TotalePrenotazione, SUM(TotaleDaPagare) TotaleDaPagare, MAX(TotaleDaPagareMulti) TotaleDaPagareMulti, SUM(TotalePagato) TotalePagato, SUM(TotaleResiduo) TotaleResiduo FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $this->DatiGenerali['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $this->DatiGenerali['PrenotazioneStato']  . " AND OdcIdRef = $user->OdcId";
            $row = $db->query_first($sql);
            $row['TotaleDaPagareMulti'] = $row['TotaleDaPagare'];
        } else {
            $sql = "SELECT SUM(TotalePrenotazione) TotalePrenotazione, SUM(TotaleDaPagare) TotaleDaPagare, SUM(TotalePagato) TotalePagato, SUM(TotaleResiduo) TotaleResiduo FROM RT_Prenotazione WHERE PrenotazioneId = " . $this->DatiGenerali['PrenotazioneId'] ;
            $row = $db->query_first($sql);
        }
        return $row;
    }
	
	public function arrotonda($prezzo)
    {
        // Versione precedente: arrotondava sempre per eccesso all'intero superiore
        // $prezzo_c = $prezzo - floor($prezzo);
        // $prezzo = intval($prezzo);
        // if ($prezzo_c > 0) {
        //     $prezzo += 1;
        // }
        // return $prezzo;

        // Nuova versione: arrotonda al multiplo di 5 più vicino per eccesso
        $fattore = 5;
        $resto = $prezzo % $fattore;
        if ($resto >= ($fattore / 2)) {
            // Arrotonda al multiplo superiore
            return ceil($prezzo / $fattore) * $fattore;
        } else {
            // Arrotonda al multiplo inferiore
            return floor($prezzo / $fattore) * $fattore;
        }
    }
	 
	public function GetScontoPromozioneAttiva($CorsaId,$DataCorsa,$Pax,$TipoBigliettoId) {
        //die();
        //global $user;
        $db=$this->conn;
        
        $PostiPrenotati=1;
        $PostiPrenotati1=0;
        
        $sql="select
		        `RT_PrenotazionePercorso`.`CorsaId` AS `CorsaId`,
		        `RT_PrenotazionePercorso`.`CorsaDataPartenza` AS `CorsaDataPartenza`,
		        (sum((`RT_Prenotazione`.`TotalePaxPrenotati` - `RT_PrenotazionePercorso`.`PasseggeriEsclusi`)) - (select
		                count(0)
		            from
		                (`RT_PrenotazioneTitolo` `t`
		                left join `RT_PrenotazioneDettaglio` `n` ON (((`t`.`PrenotazioneNumeroId` = `n`.`PrenotazioneNumero`)
		                    and (`t`.`PrenotazioneId` = `n`.`PrenotazioneId`))))
		            where
		                ((`n`.`CorsaId` = `RT_PrenotazionePercorso`.`CorsaId`)
		                    and (`n`.`DataInizioItinerario` = `RT_PrenotazionePercorso`.`CorsaDataPartenza`)
		                    and (`t`.`Codice` like 'E-%')))) AS `TotalePaxPrenotati`,
		        `RT_Prenotazione`.`OdcIdRef` AS `OdcIdRef`
		    from
		        ((`RT_Prenotazione`
		        join `RT_PrenotazionePercorso` ON ((`RT_Prenotazione`.`PrenotazioneId` = `RT_PrenotazionePercorso`.`PrenotazioneId`)))
		        join `RT_AppPrenotazioneStato` ON ((`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)))
		    where
		        ((`RT_Prenotazione`.`Cancella` = 0)
		            and (`RT_PrenotazionePercorso`.`Cancella` = 0)
		            and (`RT_PrenotazionePercorso`.`Stato` = 1)
		            and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)) and
				`RT_PrenotazionePercorso`.CorsaId=$CorsaId and `RT_PrenotazionePercorso`.CorsaDataPartenza='$DataCorsa'
		    group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`";
        
        $row = $db->query_first($sql);
        if (!empty($row['TotalePaxPrenotati']))
        {
            $PostiPrenotati=$row['TotalePaxPrenotati'];
            $PostiPrenotati1=$PostiPrenotati;
        } else {
            $PostiPrenotati=0;
            $PostiPrenotati1=0;
        }
        
        $PostiPrenotati2=$PostiPrenotati+$Pax;
        
        // seleziono le promozioni attive per il numero di pax che si vuole prenotare
        $sql="SELECT
		RT_Scontistica.ListinoId
		FROM
		RT_Scontistica
		LEFT JOIN RT_ScontisticaBiglietto ON RT_Scontistica.ListinoId = RT_ScontisticaBiglietto.ListinoId
		LEFT JOIN RT_ScontisticaCorsa ON RT_ScontisticaBiglietto.ListinoId = RT_ScontisticaCorsa.ListinoId
		LEFT JOIN RT_ScontisticaCorsaDettaglio ON RT_ScontisticaCorsa.ScontisticaCorsaId = RT_ScontisticaCorsaDettaglio.ScontisticaCorsaId
		WHERE
		RT_ScontisticaBiglietto.BigliettoId = $TipoBigliettoId AND
		RT_Scontistica.DaPax <= $PostiPrenotati2 AND
		RT_Scontistica.APax >= $PostiPrenotati2 AND
		CorsaId=$CorsaId and
		RT_ScontisticaCorsa.Dal <='$DataCorsa' AND
		RT_ScontisticaCorsa.Al >= '$DataCorsa' AND
		RT_Scontistica.Stato = 1 AND
		RT_Scontistica.Cancella = 0 AND
		RT_ScontisticaBiglietto.Stato = 1 AND
		RT_ScontisticaBiglietto.Cancella = 0 AND
		RT_Scontistica.AttivaDal <= Now() AND
		RT_Scontistica.AttivaAl >= Now() AND
		RT_ScontisticaCorsa.Stato = 1
		order by RT_Scontistica.ListinoPeso,DaPax,APax";
        //     echo $sql."<br><br>";
        $row = $db->query_first($sql);
        $ListinoId=0;
        if (!empty($row['ListinoId']))
        {
            $ListinoId=$row['ListinoId'];
        }
        
        return $ListinoId;
    }
    
}
?>
