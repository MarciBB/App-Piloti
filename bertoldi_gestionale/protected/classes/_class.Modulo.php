<?php
class Modulo {    
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
        $sql = "SELECT * From Modulo WHERE codice='$codmodulo' AND id_odc=$odc";       
       
        $row = $db->query_first($sql);
            // se esiste l'anagrafica
            if(!empty($row['id_modulo']))
            {
                $this->IdModulo=$row['id_modulo'];
                $this->OdcId=$row['id_odc'];
                $this->Nome=$row['nome'];
                $this->Codice=$row['codice'];
                $this->Descrizione=$row['descrizione'];
                $this->File=$row['file'];
                $this->Stato=$row['stato'];
            }
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
            $db=$this->conn;
            $sql = "SELECT * From ViewModuloSegnalibri WHERE id_modulo=$idModulo";
            $queryid = $db->query($sql);
            while($row=$db->fetch($queryid)){
                $bookmark=$row['segnalibro'];
                $view=$row['vista'];
                $campo=$row['campo'];
                $comunicazione=$row['comunicazione'];
                $domicilio=$row['domicilio'];
                if($view!=""){
                    if($campo=="parteistante"){
                         $sql_1="SELECT * FROM $view WHERE Mediazioneid=$idMediazione and MediazioneSoggettoParteTipoId=1";
                         $queryid_1 = $db->query($sql_1);
                         $valore="<br/>";
                          while($row_1=$db->fetch($queryid_1)){
                            $valore.="- <strong>".$row_1['nomecompleto']."</strong> ";
                            if($row_1['CodiceFiscale']!="") $valore.=" C.F. <strong>".$row_1['CodiceFiscale']."</strong>, ";
                            $valore.="residente in <strong>".$row_1['Indirizzo']."</strong> - <strong>".$row_1['Cap']."</strong> - <strong>".$row_1['Comune']."</strong><br/>";  
                          }
                          //$valore.="<br/>";
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                    }else if($campo=="controparte"){
                        $sql_1="SELECT * FROM $view WHERE Mediazioneid=$idMediazione and MediazioneSoggettoParteTipoId=2";
                         $queryid_1 = $db->query($sql_1);
                         $valore="<br/>";
                         while($row_1=$db->fetch($queryid_1)){
                            $valore.="- <strong>".$row_1['nomecompleto']."</strong> ";
                             if($row_1['CodiceFiscale']!="") $valore.=" C.F. <strong>".$row_1['CodiceFiscale']."</strong>,<br/> ";
                             $valore.="residente in <strong>".$row_1['Indirizzo']."</strong> - <strong>".$row_1['Cap']."</strong> - <strong>".$row_1['Comune']."</strong><br/>";  
                          }
                         //$valore.="<br/>";
                          $valore="<strong>".$valore."</strong>";
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                    }else if($campo=="partegiuridica"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione";
                         $queryid_1 = $db->query($sql_1);
                         $valore="<br/>";
                         while($row_1=$db->fetch($queryid_1)){
                            $valore.="- <strong>".$row_1['Rappresentante']."</strong>, in qualit&aacute; di <strong>".$row_1['Funzione']."</strong> della societ&aacute; <strong>".$row_1['RagioneSociale']."</strong> con sede in <strong>".$row_1['Indirizzo']."</strong> - <strong>".$row_1['Cap']."</strong> - <strong>".$row_1['Comune']."</strong><br/>";   
                          }
                          //$valore.="<br/>";
                          $valore="<strong>".$valore."</strong>";
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                    }else if($campo=="personafisica"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione and MediazioneSoggettoParteTipoId=1";
                         $queryid_1 = $db->query($sql_1);
                         $valore="<br/>";
                         while($row_1=$db->fetch($queryid_1)){
                            $valore.="- Il/la sottoscritto/a <strong>".$row_1['nomecompleto']."</strong>,"; 
                            if($row_1['ComuneNascita']!="") $valore.=" nato/o a <strong>".$row_1['ComuneNascita']."</strong> il <strong>".$row_1['DataNascita']."</strong>,";
                            if($row_1['CodiceFiscale']!="") $valore.=" C.F. <strong>".$row_1['CodiceFiscale']."</strong>,";
                            $valore.=" residente in <strong>".$row_1['Indirizzo']."</strong> - <strong>".$row_1['Cap']."</strong> - <strong>".$row_1['Comune']."</strong>,";
                            if($row_1['Telefono']!="") $valore.=" tel. <strong>".$row_1['Telefono']."</strong>,";
                            if($row_1['Fax']!="") $valore.=" fax. <strong>".$row_1['Fax']."</strong>,";
                            if($row_1['Cellulare']!="") $valore.=" Cell. <strong>".$row_1['Cellulare']."</strong>,";
                            if($row_1['Email']!="")$valore.=" e-mail. <strong>".$row_1['Email']."</strong>";   
                            $valore.="<br/>";                            
                          }                                                    
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                    }else if($campo=="personagiuridica"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione";
                         $queryid_1 = $db->query($sql_1);
                         $valore="<br/>";
                         while($row_1=$db->fetch($queryid_1)){
                            $valore.="- In qualit&aacute; di <strong>".$row_1['Funzione']."</strong> della societ&aacute; <strong>".$row_1['RagioneSociale']."</strong> con sede in <strong>".$row_1['Indirizzo']."</strong> - <strong>".$row_1['Cap']."</strong> - <strong>".$row_1['Comune']."</strong>";
                            if($row_1['PartitaIva']!="") $valore.=" P.iva <strong>".$row_1['PartitaIva']."</strong>,";
                            if($row_1['Telefono']!="") $valore.=" tel. <strong>".$row_1['Telefono']."</strong>,";
                            if($row_1['Fax']!="") $valore.=" fax. <strong>".$row_1['Fax']."</strong>,";
                            if($row_1['Email']!="") $valore.=" e-mail. <strong>".$row_1['Email']."</strong>";
                            $valore.="<br/>Rappresentato da <strong>".$row_1['Rappresentante']."</strong>, residente in <strong>".$row_1['indirizzoRapp']."</strong> - <strong>".$row_1['capRapp']."</strong> - <strong>".$row_1['comuneRapp']."</strong>,";
                            if($row_1['telefonoRapp']!="") $valore.=" tel. <strong>".$row_1['telefonoRapp']."</strong>,";
                            if($row_1['faxRapp']!="") $valore.=" fax. <strong>".$row_1['faxRapp']."</strong>,";
                            if($row_1['cellRapp']!="") $valore.=" cell. <strong>".$row_1['cellRapp']."</strong>,";
                            if($row_1['emailRapp']!="") $valore.=" e-mail. <strong>".$row_1['emailRapp']."</strong>";
                            $valore.="<br/>";
                          }
                          //$valore.="<br/>";                          
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                    }else if($campo=="mediatorecompleto"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione";                        
                         $queryid_1 = $db->query($sql_1);
                         $valore="<br/>";
                         $med="";
                         while($row_1=$db->fetch($queryid_1)){                             
                             
                             if(($row_1['nomemediatore']!="")and($row_1['nomemediatore']!=$med)){
                                $valore.="Avv.<strong> ".$row_1['nomemediatore']."</strong>";                            
                                if($row_1['mediatore_tel']!="") $valore.=" Tel <strong>".$row_1['mediatore_tel']."</strong>,";
                                if($row_1['mediatore_fax']!="") $valore.=" Fax <strong>".$row_1['mediatore_fax']."</strong>,";
                                if($row_1['mediatore_cell']!="") $valore.=" Fax <strong>".$row_1['mediatore_cell']."</strong>,";
                                if($row_1['mediatore_mail']!="") $valore.=" E-mail. <strong>".$row_1['mediatore_mail']."</strong>";                            
                                $valore.="<br/>";
                                $med=$row_1['nomemediatore'];
                             }
                          }
                          //$valore.="<br/>";                          
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                    }else if($campo=="soggettiesterni"){
                        $sql_1="SELECT * FROM $view WHERE MediazioneId=$idMediazione";                        
                         $queryid_1 = $db->query($sql_1);
                         $valore="<br/>";
                         $med="";
                         while($row_1=$db->fetch($queryid_1)){
                             if(($row_1['CognomeNome']!="")and($row_1['CognomeNome']!=$med)){
                                $valore.="Avv.<strong> ".$row_1['CognomeNome']."</strong>";                            
                                if($row_1['Indirizzo']!="") $valore.=" Indirizzo <strong>".$row_1['Indirizzo']."</strong>,";
                                if($row_1['Comune']!="") $valore.=" - <strong>".$row_1['Comune']."</strong>,";
                                if($row_1['Provincia']!="") $valore.=" <strong>(".$row_1['Provincia'].")</strong>, ";
                                $valore.="con specifica procura che si allega in calce alla presente domanda,<br/>";
                                $valore.="indica i seguenti recapiti dove desidera ricevere le comunicazioni del procedimento di mediazione<br/>";
                                if($row_1['Telefono']!="") $valore.=" Tel: <strong>".$row_1['Telefono']."</strong>,";
                                if($row_1['Fax']!="") $valore.=" Fax: <strong>".$row_1['Fax']."</strong>,";
                                if($row_1['Email']!="") $valore.=" E-mail: <strong>".$row_1['Email']."</strong>";  
                                if($row_1['EmailPec']!="") $valore.=" Pec: <strong>".$row_1['EmailPec']."</strong>";  
                                $valore.="<br/>";
                                $med=$row_1['CognomeNome'];
                             }
                          }                                                 
                          $content=str_replace($bookmark,utf8_decode($valore),$content);
                    }else{
                        if($domicilio!=0){
                            if($idDomicilio==5){
                                $sql_1="SELECT MediazioneSoggettoParteId,$campo FROM $view WHERE MediazioneSoggettoParteId=$idMediazioneSoggetto";
                            }else{
                                $sql_1="SELECT MediazioneSoggettoParteId,$campo FROM MediazioneParteDomicilio WHERE MediazioneSoggettoParteId=$idMediazioneSoggetto";
                            }
                           
                        }else{                        
                            if($comunicazione==0)
                                $sql_1="SELECT Mediazioneid,$campo FROM $view WHERE Mediazioneid=$idMediazione";
                            else if($idComunicazione==0)
                                   $sql_1="SELECT Mediazioneid,$campo,min(MediazioneSoggettoComunicazioneId) FROM $view WHERE Mediazioneid=$idMediazione";
                                 else
                                   $sql_1="SELECT Mediazioneid,$campo FROM $view WHERE Mediazioneid=$idMediazione and MediazioneSoggettoComunicazioneId=$idComunicazione";                                                                                          
                        }
                        $row_1 = $db->query_first($sql_1);                        
                        $valore=$row_1[$campo];                       
                        $valore="<strong>".$valore."</strong>";                        
                        $content=str_replace($bookmark,utf8_decode($valore),$content);                        
                    }
                }
            }            
            return $content;
        }
        
        function compilaSegnalibriFattura($content,$idModulo,$idFattura,$InGestoreFigli){
            $db=$this->conn;
            $sql = "SELECT * From ViewModuloSegnalibri WHERE id_modulo=$idModulo";
          
            
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
                        }else{
                            $sql_1="SELECT FatturaId,$campo FROM $view WHERE FatturaId=$idFattura and GestoreIdRef  IN ($InGestoreFigli)";                  
                            $row_1 = $db->query_first($sql_1);
                            if($row_1){
                                $valore=$row_1[$campo];
                                if(($bookmark=='##fattura_tot_iva##')OR($bookmark=='##fattura_tot_fattura##')OR($bookmark=='##fattura_imponibile##') or ($bookmark=='##fattura_nonimponibile##')){                                    
                                    $valore=number_format($valore, 2, ',', '.');
                                }
                                $valore="<strong>".$valore."</strong>";                        
                                $content=str_replace($bookmark,utf8_decode($valore),$content); 
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
                            $valore.="<strong>".$row_1[$campo]."</strong> - ";                            
                        }                      
                        $content=str_replace($bookmark,utf8_decode(substr($valore,0,strlen($valore)-3)),$content);
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
                        $valore="<strong>".$valore."</strong>";                        
                        $content=str_replace($bookmark,utf8_decode($valore),$content);
                        $i=$i+1; 
                    }                    
                }else{
                  $content="";  
                }              
            }
            if($i==0) $content="";            
            return $content;
        }
        
        function compilaSegnalibriNC($content,$idModulo,$idFattura,$InGestoreFigli){
            
            $db=$this->conn;
            $sql = "SELECT * From ViewModuloSegnalibri WHERE id_modulo=$idModulo";
          
            
            $queryid = $db->query($sql);
            while($row=$db->fetch($queryid)){
                $bookmark=$row['segnalibro'];
                $view=$row['vista'];
                $campo=$row['campo'];
                $i=0;
                if($view!=""){
                    if($view=="ViewFatturaElenco"){                        
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
                        }else{
                            $sql_1="SELECT FatturaId,$campo FROM $view WHERE FatturaId=$idFattura and GestoreIdRef  IN ($InGestoreFigli)";                  
                            $row_1 = $db->query_first($sql_1);
                            if($row_1){
                                $valore=$row_1[$campo];
                                if(($bookmark=='##fattura_tot_iva##')OR($bookmark=='##fattura_tot_fattura##')OR($bookmark=='##fattura_imponibile##') or ($bookmark=='##fattura_nonimponibile##')){                                    
                                    $valore=number_format($valore, 2, ',', '.');
                                }
                                $valore="<strong>".$valore."</strong>";                        
                                $content=str_replace($bookmark,utf8_decode($valore),$content); 
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
                            $valore.="<strong>".$row_1[$campo]."</strong> - ";                            
                        }                      
                        $content=str_replace($bookmark,utf8_decode(substr($valore,0,strlen($valore)-3)),$content);
                        $i=$i+1;
                    }else{
                       $sql_1="SELECT FatturaId,MediazioneId FROM ViewFatturaElenco WHERE FatturaId=$idFattura and GestoreIdRef  IN ($InGestoreFigli)";                                                
                        $queryid_1 = $db->query($sql_1);
                        while($row=$db->fetch($queryid_1)){
                             $idMediazione=$row['MediazioneId'];
                        }
                        $sql_1="SELECT Mediazioneid,$campo FROM $view WHERE Mediazioneid=$idMediazione";                       
                        $row_1 = $db->query_first($sql_1);                        
                        $valore=$row_1[$campo];                       
                        $valore="<strong>".$valore."</strong>";                        
                        $content=str_replace($bookmark,utf8_decode($valore),$content);
                        $i=$i+1; 
                    }                    
                }else{
                  $content="";  
                }              
            }
            if($i==0) $content="";            
            return $content;
        }
}
?>
