<?php
class ModuloWord {    
    public $conn;
    public $OdcId;
    public $File;
    public $Nome;
    public $Codice;
    public $Descrizione;
    public $Stato;
    public $IdModulo;

    function __construct() {
        
    }
    
     function inizializza($codmodulo, $odc) {
                
        $db=$this->conn;
        $sql = "SELECT * From WordViewModulo WHERE ModuloWordCodice='$codmodulo' AND OdcId=$odc";
      
       
        $row = $db->query_first($sql);
            // se esiste l'anagrafica
            if(!empty($row['ModuloWordId']))
            {
                $this->IdModulo=$row['ModuloWordId'];
                $this->OdcId=$odc;
                $this->Nome=$row['ModuloWord'];
                $this->Codice=$codmodulo;
                $this->Descrizione=$row['ModuloWordDescrizione'];
                $this->File=$row['ModuloWordFile'];
                $this->Stato=1;
            }
            else
                exit();
        }
        
        function getHeader($odc){
            $odc_header="";
            $db=$this->conn;
            $sql = "SELECT * From Odc WHERE OdcId=$odc";
       
            $row = $db->query_first($sql);              
            if(!empty($row['OdcId']))
            {
                $odc_header="<page_header>".$row['HeaderModulo']."</page_header>";               
            }
            return $odc_header;
        }
        
        function getFooter($gestore){
            $odc_footer="";
            $db=$this->conn;
            $sql = "SELECT * From Gestore WHERE GestoreId=$gestore";
       
            $row = $db->query_first($sql);              
            if(!empty($row['GestoreId']))
            {
                $odc_footer="<page_footer>".$row['FooterModulo']."</page_footer>";               
            }
            return $odc_footer;
        }
        
        function compilaSegnalibri($content,$idModulo,$idMediazione,$idComunicazione,$idDomicilio,$idMediazioneSoggetto){
            $content_word=$content;
            $db=$this->conn;
         //   $sql = "SELECT * From WordViewModuloSegnalibro WHERE AppModuloWordId=$idModulo";
            
            $sql = "SELECT * From AppSegnalibro";
            
            $queryid = $db->query($sql);
            $bookmark='##DataOdierna##';
            $valore=date("d/m/Y");
            $content=str_replace($bookmark,utf8_decode($valore),$content);
            $SegnalibriDaSostituire[$bookmark]=($valore);
            
            
            
            while($row=$db->fetch($queryid)){
                
                $bookmark=$row['AppSegnalibro'];
                $view=$row['AppSegnalibroVista'];
                $campo=$row['AppSegnalibroCampo'];
                $comunicazione=$row['AppSegnalibroComunicazione'];
                $domicilio=$row['AppSegnalibroDomicilio'];
              
                
                if($view!=""){
                    if($campo=="ParteIstante"){
                         $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione and MediazioneSoggettoParteTipoId=1";
                         $queryid_1 = $db->query($sql_1);
                         $valore="";
                          while($row_1=$db->fetch($queryid_1)){
                              $res="residente in";
                            $valore.="- ".$row_1['CognomeRagioneSociale'];
                            if(!empty($row_1['Nome'])) 
                                $valore.=" ".$row_1['Nome'];
                            
                            if(!empty($row_1['CodiceFiscale']))  $valore.=" C.F. ".$row_1['CodiceFiscale']." ";
                            if(!empty($row_1['PartitaIva'])) 
                                {
                                $valore.=" P.I. ".$row_1['PartitaIva']." ";
                                $res="con sede legale in";
                                }
                            $valore.=$res." ".$row_1['Indirizzo']." - ".$row_1['Cap']." - ".$row_1['Comune']."\par ";  
                          
                          //  $valore.="\par";
                         //   print_r($row_1);
                            
                          }
                          
                          //$valore.="\par";
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                    }
                     elseif($bookmark=="##ParteIstanteElencoBase##"){
                         $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione and MediazioneSoggettoParteTipoId=1";
                         $queryid_1 = $db->query($sql_1);
                         $valore="";
                         $conta=1;
                          while($row_1=$db->fetch($queryid_1)){
                            
                            $valore.=" ".$row_1['CognomeRagioneSociale'];
                            if(!empty($row_1['Nome'])) 
                                $valore.=" ".$row_1['Nome'];
                            
                            if(!empty($row_1['CodiceFiscale']))  
                                $valore.=" C.F. ".$row_1['CodiceFiscale'];
                            if(!empty($row_1['PartitaIva'])) 
                                $valore.=" P.I. ".$row_1['PartitaIva'];
                            
                           if ($db->num_rows($queryid_1)>$conta)
                            $valore.=", ";
                            $conta++;
                            
                            
                          }
                          //$valore.="\par";
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                         
                         
                          }
                          
                    elseif($campo=="ParteConvenuta"){
                        
                         $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione and MediazioneSoggettoParteTipoId=2";
                         $queryid_1 = $db->query($sql_1);
                         $valore="";
                          while($row_1=$db->fetch($queryid_1)){
                              $res="residente in";
                            $valore.="- ".$row_1['CognomeRagioneSociale'];
                            if(!empty($row_1['Nome'])) 
                                $valore.=" ".$row_1['Nome'];
                            
                            if(!empty($row_1['CodiceFiscale']))  $valore.=" C.F. ".$row_1['CodiceFiscale']." ";
                            if(!empty($row_1['PartitaIva'])) 
                                {
                                $valore.=" P.I. ".$row_1['PartitaIva']." ";
                                $res="con sede legale in";
                                }
                            $valore.=$res." ".$row_1['Indirizzo']." - ".$row_1['Cap']." - ".$row_1['Comune']."\par ";  
                          
                          //  $valore.="\par";
                         //   print_r($row_1);
                            
                          }
                          
                          //$valore.="\par";
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                        
                        
                    }
                    elseif($bookmark=="##ControparteElencoBase##"){
                         $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione and MediazioneSoggettoParteTipoId=2";
                         $queryid_1 = $db->query($sql_1);
                         $valore="";
                         $conta=1;
                          while($row_1=$db->fetch($queryid_1)){
                            
                            $valore.=" ".$row_1['CognomeRagioneSociale'];
                            if(!empty($row_1['Nome'])) 
                                $valore.=" ".$row_1['Nome'];
                            
                            if(!empty($row_1['CodiceFiscale']))  
                                $valore.=" C.F. ".$row_1['CodiceFiscale'];
                            if(!empty($row_1['PartitaIva'])) 
                                $valore.=" P.I. ".$row_1['PartitaIva'];
                            
                           if ($db->num_rows($queryid_1)>$conta)
                            $valore.=", ";
                            $conta++;
                            
                            
                          }
                          //$valore.="\par";
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                    }
                    
                    
                    elseif($bookmark=="##ParteIstanteRagioniPretesa##"){
                         $sql_1="SELECT $campo FROM $view WHERE MediazioneId=$idMediazione and MediazioneSoggettoParteTipoId=1 and $campo is not null limit 1";
                         $queryid_1 = $db->query($sql_1);
                         $valore="";
                         $conta=1;
                          if($row_1=$db->fetch($queryid_1)){
                            
                            $valore=$row_1[$campo];
                            
                          }
                          
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                    }
                    
                    
                    elseif($campo=="partegiuridica"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione";
                         $queryid_1 = $db->query($sql_1);
                          $valore="\par";
                         while($row_1=$db->fetch($queryid_1)){
                            $valore.="- ".$row_1['Rappresentante'].", in qualit&aacute; di ".$row_1['Funzione']." della societ&aacute; ".$row_1['RagioneSociale']." con sede in ".$row_1['Indirizzo']." - ".$row_1['Cap']." - ".$row_1['Comune']."\par";   
                          }
                          //$valore.="\par";
                          $valore="".$valore."";
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                    }elseif($campo=="personafisica"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione and MediazioneSoggettoParteTipoId=1";
                         $queryid_1 = $db->query($sql_1);
                          $valore="\par";
                         while($row_1=$db->fetch($queryid_1)){
                            $valore.="- Il/la sottoscritto/a ".$row_1['nomecompleto'].","; 
                            if($row_1['ComuneNascita']!="") $valore.=" nato/o a ".$row_1['ComuneNascita']." il ".$row_1['DataNascita'].",";
                            if($row_1['CodiceFiscale']!="") $valore.=" C.F. ".$row_1['CodiceFiscale'].",";
                            $valore.=" residente in ".$row_1['Indirizzo']." - ".$row_1['Cap']." - ".$row_1['Comune'].",";
                            if($row_1['Telefono']!="") $valore.=" tel. ".$row_1['Telefono'].",";
                            if($row_1['Fax']!="") $valore.=" fax. ".$row_1['Fax'].",";
                            if($row_1['Cellulare']!="") $valore.=" Cell. ".$row_1['Cellulare'].",";
                            if($row_1['Email']!="")$valore.=" e-mail. ".$row_1['Email']."";   
                            $valore.="\par";                            
                          }                                                    
                           $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                    }elseif($campo=="personagiuridica"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione";
                         $queryid_1 = $db->query($sql_1);
                         $valore="\par";
                         while($row_1=$db->fetch($queryid_1)){
                            $valore.="- In qualit&aacute; di ".$row_1['Funzione']." della societ&aacute; ".$row_1['RagioneSociale']." con sede in ".$row_1['Indirizzo']." - ".$row_1['Cap']." - ".$row_1['Comune']."";
                            if($row_1['PartitaIva']!="") $valore.=" P.iva ".$row_1['PartitaIva'].",";
                            if($row_1['Telefono']!="") $valore.=" tel. ".$row_1['Telefono'].",";
                            if($row_1['Fax']!="") $valore.=" fax. ".$row_1['Fax'].",";
                            if($row_1['Email']!="") $valore.=" e-mail. ".$row_1['Email']."";
                            $valore.="\parRappresentato da ".$row_1['Rappresentante'].", residente in ".$row_1['indirizzoRapp']." - ".$row_1['capRapp']." - ".$row_1['comuneRapp'].",";
                            if($row_1['telefonoRapp']!="") $valore.=" tel. ".$row_1['telefonoRapp'].",";
                            if($row_1['faxRapp']!="") $valore.=" fax. ".$row_1['faxRapp'].",";
                            if($row_1['cellRapp']!="") $valore.=" cell. ".$row_1['cellRapp'].",";
                            if($row_1['emailRapp']!="") $valore.=" e-mail. ".$row_1['emailRapp']."";
                             $valore="\par";
                          }
                          //$valore.="\par";                          
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                    }elseif($campo=="mediatorecompleto"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione";                        
                         $queryid_1 = $db->query($sql_1);
                          $valore="\par";
                         $med="";
                         while($row_1=$db->fetch($queryid_1)){                             
                             
                             if(($row_1['nomemediatore']!="")and($row_1['nomemediatore']!=$med)){
                                $valore.="Avv. ".$row_1['nomemediatore']."";                            
                                if($row_1['mediatore_tel']!="") $valore.=" Tel ".$row_1['mediatore_tel'].",";
                                if($row_1['mediatore_fax']!="") $valore.=" Fax ".$row_1['mediatore_fax'].",";
                                if($row_1['mediatore_cell']!="") $valore.=" Fax ".$row_1['mediatore_cell'].",";
                                if($row_1['mediatore_mail']!="") $valore.=" E-mail. ".$row_1['mediatore_mail']."";                            
                                $valore.="\par";
                                $med=$row_1['nomemediatore'];
                             }
                          }
                          //$valore.="\par";                          
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                    }elseif($campo=="soggettiesterni"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione";                        
                         $queryid_1 = $db->query($sql_1);
                          $valore="\par";
                         $med="";
                         while($row_1=$db->fetch($queryid_1)){
                             if(($row_1['CognomeNome']!="")and($row_1['CognomeNome']!=$med)){
                                $valore.="Avv. ".$row_1['CognomeNome']."";                            
                                if($row_1['Indirizzo']!="") $valore.=" Indirizzo ".$row_1['Indirizzo'].",";
                                if($row_1['Comune']!="") $valore.=" - ".$row_1['Comune'].",";
                                if($row_1['Provincia']!="") $valore.=" (".$row_1['Provincia']."), ";
                                $valore.="con specifica procura che si allega in calce alla presente domanda,\par";
                                $valore.="indica i seguenti recapiti dove desidera ricevere le comunicazioni del procedimento di mediazione\par";
                                if($row_1['Telefono']!="") $valore.=" Tel: ".$row_1['Telefono'].",";
                                if($row_1['Fax']!="") $valore.=" Fax: ".$row_1['Fax'].",";
                                if($row_1['Email']!="") $valore.=" E-mail: ".$row_1['Email']."";  
                                if($row_1['EmailPec']!="") $valore.=" Pec: ".$row_1['EmailPec']."";  
                                $valore.="\par";
                                $med=$row_1['CognomeNome'];
                             }
                          }                                                 
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                           $SegnalibriDaSostituire[$bookmark]=($valore);
                    }
                   
                    
                    else{
                       
                        
                        if($domicilio!=0){
                            if($idDomicilio==5){
                                $sql_1="SELECT MediazioneSoggettoParteId,$campo FROM $view WHERE MediazioneSoggettoParteId=$idMediazioneSoggetto";
                            }else{
                                $sql_1="SELECT MediazioneSoggettoParteId,$campo FROM MediazioneParteDomicilio WHERE MediazioneSoggettoParteId=$idMediazioneSoggetto";
                            }
                           
                        }else{ 
                                $sql_1="SELECT $campo FROM $view WHERE MediazioneId=$idMediazione";
                                
                               
                                
                           
                           /* if($comunicazione==0)
                            {
                                $sql_1="SELECT Mediazioneid,$campo FROM $view WHERE Mediazioneid=$idMediazione";
                                
                            }
                            else
                            {
                                
                                if($idComunicazione==0)
                                   $sql_1="SELECT Mediazioneid,$campo,min(MediazioneSoggettoComunicazioneId) FROM $view WHERE Mediazioneid=$idMediazione";
                                 else
                                   $sql_1="SELECT Mediazioneid,$campo FROM $view WHERE Mediazioneid=$idMediazione and MediazioneSoggettoComunicazioneId=$idComunicazione";                                                                                          
                            }*/
                        }
                        $row_1 = $db->query_first($sql_1);                        
                        $valore=$row_1[$campo];                       
                        //$valore="".$valore.""; 
                        
                                if (($campo=='ValoreRichiestoTot'))
                                {
                                   
                                     $valore=number_format($valore, 2, ',' , '.');
                                 }
                                 if (($bookmark=='##MediazioneImportoTotaleIvaInc##') or ($bookmark=='##MediazioneDirittiSegreteriaIvaInc##') or ($bookmark=='##MediazioneIndennitaIvaInc##'))
                                 {
                                     $iva=  Config::$AppIva;
                                    $totale=$valore+($valore*$iva/100);
                                     $valore=number_format($totale, 2, ',' , '.');
                                     
                                 }
                        
                         $content=str_replace($bookmark,utf8_decode($valore),$content);
                         $SegnalibriDaSostituire[$bookmark]=($valore);
                      
                    }
                }
            }            
            $content=$this->populate_RTF($SegnalibriDaSostituire, $content_word);
            return $content;
        }
        
        function compilaSegnalibriFattura($content,$idModulo,$idFattura,$InGestoreFigli){
            $content_word=$content;
            $db=$this->conn;
            $sql = "SELECT * From ViewModuloSegnalibri WHERE id_modulo=$idModulo";
          
            $SegnalibriDaSostituire=null;
            $queryid = $db->query($sql);
            while($row=$db->fetch($queryid)){
                $bookmark=$row['segnalibro'];
                $view=$row['vista'];
                $campo=$row['campo'];
                $i=0;
                if($view!=""){
                    if($view=="ViewFattura"){                        
                        if($campo=="dettaglio_completo"){
                             $sql_1="SELECT * FROM $view WHERE FatturaId=$idFattura and GestoreIdRef  IN ($InGestoreFigli)"; 
                             
                             
                             $queryid_1 = $db->query($sql_1);                         
                             while($row_1=$db->fetch($queryid_1)){
                                $valore.="<tr><td style=\"width:60%\">".$row_1['Dett_Descrizione']."</td>";
                                $valore.="<td style=\"width:15%\">&nbsp;</td><td style=\"width:5%\">&euro;</td>";                                                                  
                                $dett=number_format($row_1['Dett_Imponibile'], 2, ',', '.');
                                if ($row_1['Dett_NonImponibile']>0)
                                $dett=number_format($row_1['Dett_NonImponibile'], 2, ',', '.');
                                $valore.="<td style=\"width:15%;text-align: right\">".$dett."</td></tr>";                                                        
                              }                                                   
                               $content=str_replace($bookmark,utf8_decode($valore),$content);                   
                               $SegnalibriDaSostituire[$bookmark]=($valore);
                        }else{
                            $sql_1="SELECT FatturaId,$campo FROM $view WHERE FatturaId=$idFattura and GestoreIdRef  IN ($InGestoreFigli)";                  
                            $row_1 = $db->query_first($sql_1);
                            if($row_1){
                                $valore=$row_1[$campo];
                                if(($bookmark=='##fattura_tot_iva##')OR($bookmark=='##fattura_tot_fattura##')OR($bookmark=='##fattura_imponibile##') or ($bookmark=='##fattura_nonimponibile##')){                                    
                                    $valore=number_format($valore, 2, ',', '.');
                                }
                                $valore="".$valore."";                        
                                $content=str_replace($bookmark,utf8_decode($valore),$content); 
                                 $SegnalibriDaSostituire[$bookmark]=($valore);
                                $i=$i+1;
                            }                           
                        }
                    }else if(($bookmark=="##controparte_nome##")or($bookmark=="##parteistante_nome##")){
                        $sql_1="SELECT FatturaId,MediazioneId FROM ViewFattura WHERE FatturaId=$idFattura and GestoreIdRef  IN ($InGestoreFigli)";                                                
                        $queryid_1 = $db->query($sql_1);
                        while($row=$db->fetch($queryid_1)){
                             $idMediazione=$row['MediazioneId'];
                        }
                        $valore="";
                        $sql_1="SELECT Mediazioneid,$campo FROM $view WHERE Mediazioneid=$idMediazione";                        
                        $queryid_1 = $db->query($sql_1);
                        while($row_1=$db->fetch($queryid_1)){                                                
                            $valore.="".$row_1[$campo]." - ";                            
                        }                      
                        $content=str_replace($bookmark,utf8_decode(substr($valore,0,strlen($valore)-3)),$content);
                         $SegnalibriDaSostituire[$bookmark]=$valore;
                        $i=$i+1;
                    }else{
                       $sql_1="SELECT FatturaId,MediazioneId FROM ViewFattura WHERE FatturaId=$idFattura and GestoreIdRef  IN ($InGestoreFigli)";                                                
                        $queryid_1 = $db->query($sql_1);
                        while($row=$db->fetch($queryid_1)){
                             $idMediazione=$row['MediazioneId'];
                        }
                        $sql_1="SELECT Mediazioneid,$campo FROM $view WHERE Mediazioneid=$idMediazione";                       
                        $row_1 = $db->query_first($sql_1);                        
                        $valore=$row_1[$campo];                       
                        $valore="".$valore."";                        
                        $content=str_replace($bookmark,utf8_decode($valore),$content);
                         $SegnalibriDaSostituire[$bookmark]=($valore);
                        $i=$i+1; 
                    }                    
                }else{
                  $content="";  
                }              
            }
            if($i==0) 
                $content="";            
            $content=$this->populate_RTF($SegnalibriDaSostituire, $content_word);
            return $content;
        }
        
        
        
         function populate_RTF($vars, $doc_file) {
             
            
        $replacements = array ('\\' => "\\\\",
                               '{'  => "\{",
                               '}'  => "\}");
        $doc_file=  Config::$odcfile;
        $doc_file=$doc_file.$this->OdcId."/".$this->File;
        
        $document = file_get_contents($doc_file);
        if(!$document) {
            return false;
        }

        foreach($vars as $key=>$value) {
            $key=trim($key);
           // $key=str_replace("##","",$key);
            $search = $key;
 $document = str_replace($search, $value, $document);
            foreach($replacements as $orig => $replace) {
                $value = str_replace($orig, $replace, $value);
            }

           
        }
      
        return $document;
    }
        
        
        
        
        
        
}
?>
