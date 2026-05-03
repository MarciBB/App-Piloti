<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/home_2.css" type="text/css" />
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.TipologiaBus.php");

$ModuloId=1;



function carica_menu_percorso($step_corrente,$mod)
{
global $abilita_modifica,$tratta_wizard,$db, $dizionario;
//$tratta_wizard->conn=$db;
//$menu=$tratta_wizard->getMenuWizard();
    $mod=1;
   
    $CorsaId=$_REQUEST['CorsaId'];
    $DataPartenza=$_REQUEST['DataPartenza'];
   
$menu=array(
   
    1=>$dizionario['post']['menu_quadratura']
    //2=>"Emissione titoli"
    
    
    
    );


    ?>
                <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
			<ul>
                         <?
                         $contamenu=1;
                         while ($contamenu<=1)
                         {
                          $class1="";
                          $class2="";
                          
                          if ($contamenu==$step_corrente)
                          {
                              $class1="sel";
                              $class2="brain_firstspan sel";
                          }
                             
                          $StatoStep="";
                            
                          if ( ($contamenu<=5) or (($contamenu>5) and ($mod))) { ?>
                            
                            <li class="<?=$class1?>">
                                <span class="<?=$class2?>">
                                  <? //if (($contamenu<=$step_corrente) or ($mod==1))
                                  if ($mod)
                                  {
                                     
                                      
                                   ?>
                                             <a href="javascript:void(0);" onclick="loadMediazioneStep('rt_postviaggio','postviaggio.php?do=GestionePostViaggio&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
                                    <?

                                  }
                                  else
                                    echo($menu[$contamenu]);
                                   
                              
                                
                           ?>     
                                </span>
                                
                                
                                
                                
                            
                            </li>
                            <?
                          }
                             
                             
                             $contamenu++;
                         }
                         
                         ?>
                         			</ul>
		</div>
 
 <?
    
    
    
}

function show_list()
{
    $step=$_REQUEST['step'];
    
    if ($step==1)  
      quadratura();
    elseif ($step==2)  
        emissione_titoli();
    else
        quadratura();
  
}


function GetTipologiaBiglietti($PrenotazioneId,$CorsaId,$FermataIdAP,$FermataIdAD,$TV)
{

global $user,$HtmlCommon,$prenotazione_wizard;
$page=new Form();
$db= new Database();
$db->connect(); 


$ar=1;

if ($TV==1)
    $ar=0;




$prenotazione=new Prenotazione();
$prenotazione->conn=$db;
$arr_tratte=$prenotazione->GetTratte($CorsaId, $FermataIdAP, $FermataIdAD);

$arr_listini=$prenotazione->GetListini($arr_tratte,$FermataIdAP,$FermataIdAD,$CorsaId);



  $at=0;
  
 
  
  while ($at<sizeof($arr_tratte))
  {
     
      if ($at>0)
      $tratteid.=",".$arr_tratte[$at]['TrattaId'];
      else
           $tratteid=$arr_tratte[$at]['TrattaId'];
      
      
      $at++;
  }

  

  
  $sql="select distinct TipologiaBigliettoId,TipologiaBiglietto from RT_ViewPrenotazioneTipoBiglietti where TrattaId In($tratteid) and AR=$ar ";

  
  
  $ArrObject = $db->fetch_array($sql);


$i=0;
$numerobiglietti=-1;

?>
<div class="brain_rowAll">
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" id="pagamentiTabella">
                                  <tbody><tr class="rowIntestazione">
                                        <th scope="row">n. pax</th>
                                         <td>biglietto</td>
                                         <td>prezzo per pax (&euro;)</td>
                                         <td>prezzo base (&euro;)</td>
                                         <td>riduzione (&euro;)</td>
                                         <td>aumento (&euro;)</td>
                                         <td>prezzo finale (&euro;)</td>
                                  </tr>
<?
$totpax=0;
    while ($i< sizeof($ArrObject))
    {
    $BigliettoId=$ArrObject[$i]['TipologiaBigliettoId'];
    $BigliettoPrezzo=$ArrObject[$i]['TipologiaBiglietto'];
    $stringa=$BigliettoId."_".$BigliettoPrezzo;
   
       $BigliettoDescr=$BigliettoPrezzo;
          
       
        
       $ntratte=0;
       $PrezzoPax=0;     
       while($ntratte<sizeof($arr_tratte))
            {
                $TrattaId=$arr_tratte[$ntratte]['TrattaId'];
                $ListinoId=$arr_listini[$TrattaId]['ListinoId'];  
                $sql="Select * from RT_ListinoBiglietto where ListinoId=$ListinoId and BigliettoId=$BigliettoId and  OdcIdRef=$user->OdcId and Cancella=0";
                $ArrPrezzo = $db->query_first($sql);
                $listinoNome="";
                
                if (!empty($ArrPrezzo['ListinoId']))
                  $PrezzoPax+=$ArrPrezzo['Prezzo'];  
                   
         
                
             
                
                $ntratte++;
            }
            
       
     $sql="select * from RT_PrenotazioneBiglietto where TipologiaBigliettoId=$BigliettoId and PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and cancella=0";
   
     
     $rowtipo = $db->query_first($sql);     
     $pax=0;
     $riduzione=0;
     $aumento=0;
     $base=0;
     $finale=0;
     if (!empty($rowtipo['TipologiaBigliettoId']))
      {
        $pax= $rowtipo['NumeroPax'];
        $totpax=$totpax+$pax;
        $PrezzoPax=  $rowtipo['PrezzoPax'];
        $riduzione=$rowtipo['RiduzionePax'];
        
      
       
        $aumento=$rowtipo['AumentoPax'];
        $base=$rowtipo['PrezzoBasePax'];
        $finale=$rowtipo['PrezzoTotalePax'];
      }
     $totalissimo+=$finale;
            
            
       if ($PrezzoPax>0)
       {
       $PrezzoPax_f= number_format($PrezzoPax, 2, ",", ".");
        $totalissimo_f= number_format($totalissimo, 2, ",", ".");
         $finale_f= number_format($finale, 2, ",", ".");
          $base_f= number_format($base, 2, ",", ".");
            $riduzione_f= number_format($riduzione, 2, ",", ".");
              $aumento_f= number_format($aumento, 2, ",", ".");
          
          
          
          
          
       $BigliettoPrezzo.=" ( ".$PrezzoPax_f." &euro; )";    
                 
?>

        <tr class="rowBianca">
         
           <th scope="row">
               <input id="Pax<?=$i?>" onchange="javascript:CalcolaPrezzoTipoBiglietto();" type="text" maxlength="3" size="3" value="<?=$pax?>" name="BigliettoTipologiaPax[<?=$BigliettoId?>_<?=$BigliettoDescr?>]" class="required digits">
               <?
           
           
           
         /*  $page->create_textbox($BigliettoPrezzo,"Pax".$i,"BigliettoTipologiaPax[$stringa]",0,1,"brain_campiform",array(
       "onChange"=>"'javascript:CalcolaPrezzoTipoBiglietto($BigliettoId,\"$BigliettoPrezzo\",$PrezzoPax,this);'",
       "class"=>"'required number'"));*/
        $page->create_textbox_hidden("Prezzo".$i,$PrezzoPax);
        $page->create_textbox_hidden("Totale".$i,0);        
           ?>
           </th>
            <td><?=$ArrObject[$i]['TipologiaBiglietto']?></td>
           <td><?=$PrezzoPax_f?></td>
           <td id="PrezzoParziale<?=$i?>"><?=$base_f?> &euro; </td>
           
           <td>    
               <input id="PrezzoRiduzione<?=$i?>" onchange="javascript:CalcolaPrezzoTipoBiglietto();" type="text" maxlength="8" size="8" value="<?=$riduzione_f?>" name="BigliettoTipologiaPaxRid[<?=$BigliettoId?>]" class="required numberDE">
           </td>
           
           <td>    
               <input id="PrezzoAumento<?=$i?>" onchange="javascript:CalcolaPrezzoTipoBiglietto();" type="text" maxlength="8" size="8" value="<?=$aumento_f?>" name="BigliettoTipologiaPaxAum[<?=$BigliettoId?>]" class="required numberDE">
           </td>
           
           <td id="PrezzoFinale<?=$i?>" class="prezzo_grande"><?=$finale_f?> &euro; </td>
           
           
           
        </tr>                                  
<?
    

//   $page->create_textbox("Prezzo",$BigliettoPrezzo,"BigliettoTipologiaPax[$stringa]",$PrezzoPax,1,"brain_campiform",array(
      // "class"=>"'required'"));
       
       $numerobiglietti++;
       
       }    
        
        
        
        
        
        
        
        
        
        
       $i++; 
        
    }
 

    $page->create_textbox_hidden("NumeroBiglietti",$numerobiglietti); 
     $page->create_textbox_hidden("TotalePax",$totpax); 
     $page->create_textbox_hidden("TotalePaxScelti",0); 
?>

<tr class="rowIntestazione">
<th scope="row">&nbsp;</th>
<td></td>
<td><strong></strong></td>
<td><strong></strong></td>
<td><strong></strong></td>
<td><strong></strong></td>
<td id="PrezzoTotalePax" class="prezzo_grande"><strong><?=$totalissimo_f?> &euro; </strong></td>

</tr>
</tbody></table>
</div> 
                                  
                                
                                            


                                 
<?
}


function emissione_titoli()
{
global $user,$HtmlCommon;
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
$db= new Database();
$db->connect();
$corsaobj=new Corsa($CorsaId);
$corsaobj->conn=$db;
$corsaobj->inizializzaDatiGenerali();
$arr_corsa=$corsaobj->DatiGenerali;

$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
$titolo=$titolo." / Emissione Titoli";

$HtmlCommon->html_titolo_pagina($titolo);
$HtmlCommon->html_titolo_box($titolo);
$DataPartenzaF=$_REQUEST['DataPartenza'];

carica_menu_percorso(2);



include_once("postviaggio_validator.php");    
?>
<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

<form id="application_form" name="application_form"  method="post" action="#">
<div class="brain_formModifica">
     <div class="brain_data-content">     
     
 



        <? // determina il numero di   emettere 
         
  
  
//$TitoliDaEmettere=0;
  
 
$sql="select distinct(BusId) as BusIden from  RT_PreparazioneBus where CorsaId=$CorsaId and DataPartenza='$DataPartenza' and  OdcIdRef=$user->OdcId order by BusId desc";

$ArrObject = $db->fetch_array($sql);

$numeropullman=sizeof($ArrObject); 

$nt=0;
//while ($nt<$numerotratte)
while ($nt<$numeropullman)
{
   
    $BusId=$ArrObject[$nt]['BusIden'];
   
    
    $tb=new TipologiaBus($BusId);
    $tb->conn=$db;
    $tb->inizializzaDatiGenerali();
    $arr_tb=$tb->DatiGenerali;
    $NomePullman=$arr_tb['TipologiaBus'];
   
    
    
   // $sql="select distinct(BusNumero) as BusNumeroIden from  RT_FoglioBusCarico where CorsaId=$CorsaId and DataPartenza='$DataPartenza' and  OdcIdRef=$user->OdcId and BusId=$BusId order by BusNumeroIden asc";
$sql="select distinct(BusNumero) as BusNumeroIden from  RT_PreparazioneBus where CorsaId=$CorsaId and DataPartenza='$DataPartenza' and  OdcIdRef=$user->OdcId and BusId=$BusId order by BusNumeroIden asc";
      

// echo($sql);
    $ArrObjectNP = $db->fetch_array($sql);

    $numeropullman_tipo=sizeof($ArrObjectNP); 
    
    $nptipo=0;
    ?>
       
    <?
    while ($nptipo<$numeropullman_tipo)
    {
    $busNumeroT=$ArrObjectNP[$nptipo]['BusNumeroIden'];

  
    $sql_da_incassare="SELECT RT_PreparazioneBus.DataPartenza, RT_PreparazioneBus.BusId, RT_PreparazioneBus.CorsaId, RT_PreparazioneBus.OdcIdRef, RT_PreparazioneBus.BusNumero, sum(RT_ImportoPerPrenotazione.DaIncassare) as IncassoDaVerificare
FROM
RT_PreparazioneBus
INNER JOIN RT_Prenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId
INNER JOIN RT_ImportoPerPrenotazione ON RT_Prenotazione.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId
INNER JOIN RT_ViewPrenotazioniAndata ON RT_PreparazioneBus.PrenotazioneId = RT_ViewPrenotazioniAndata.PrenotazioneId AND RT_PreparazioneBus.DataPartenza = RT_ViewPrenotazioniAndata.DataInizioItinerario AND RT_PreparazioneBus.CorsaId = RT_ViewPrenotazioniAndata.CorsaInizioItinerario

WHERE
RT_Prenotazione.PrenotazioneStato = 1 and BusId=$BusId and BusNumero=$busNumeroT and CorsaId=$CorsaId and DataPartenza='$DataPartenza'
GROUP BY
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero ";
    
  //  echo($sql_da_incassare);
    
    $row=$db->query_first($sql_da_incassare);
    $IncassoDaVerificare=0;
    if ($row['IncassoDaVerificare']>0)
        $IncassoDaVerificare=$row['IncassoDaVerificare'];
    
   $sql_da_incassare="SELECT
RT_PreparazioneBus.CorsaId AS CorsaId,
RT_PreparazioneBus.DataPartenza AS DataPartenza,
RT_PreparazioneBus.BusId AS BusId,
RT_PreparazioneBus.BusNumero AS BusNumero,
RT_PreparazioneBus.OdcIdRef AS OdcIdRef,
RT_PrenotazioneDettaglio.ComunePartenza AS ComunePartenza,
RT_PrenotazioneDettaglio.FermataPartenza AS FermataPartenza,
RT_PreparazioneBus.PrenotazioneId AS PrenotazioneId,
RT_PrenotazioneDettaglio.ComuneArrivo AS ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo AS FermataArrivo,
count(`RT_PrenotazioneDettaglio`.`PrenotazioneDettaglioId`) AS NumeroPax,
RT_Prenotazione.ClienteNome AS ClienteNome,
RT_Prenotazione.ClienteCellulare AS ClienteCellulare,
RT_PrenotazioneDettaglio.Tragitto AS Tragitto,
RT_Prenotazione.PrenotazioneStato AS PrenotazioneStato,
RT_Prenotazione.TotalePaxPrenotati AS TotalePaxPrenotati,
RT_Prenotazione.TipoViaggioId AS TipoViaggioId,
RT_ImportoPerPrenotazione.DaIncassare AS TotaleImportoPrenotazione,
RT_PrenotazionePercorso.PrenotazionePercorsoId AS PrenotazionePercorsoId,
RT_PrenotazionePercorso.ComuneSalita AS ComunePartenzaI,
RT_PrenotazionePercorso.FermataSalita AS FermataPartenzaI,
RT_Prenotazione.ClienteSessoId AS ClienteSessoId,
RT_PrenotazioneDettaglio.DataArrivo AS DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo AS OrarioArrivo,
RT_PrenotazionePercorso.ComuneDiscesa AS ComuneDiscesa,
RT_PrenotazionePercorso.FermataDiscesa AS FermataDiscesa,
RT_PrenotazionePercorso.DataOraDiscesa AS DataOraDiscesa,
RT_Prenotazione.CodicePrenotazione AS CodicePrenotazione,
RT_Prenotazione.GestoreIdRef AS GestoreIdRef,
RT_PrenotazioneDettaglio.TipoViaggio AS TipoViaggio,
RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneStato as Variazioni
FROM
((((RT_PreparazioneBus
INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId) AND (RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId))))
INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId)))
INNER JOIN RT_ImportoPerPrenotazione ON ((RT_Prenotazione.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId)))
INNER JOIN RT_PrenotazionePercorso ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazionePercorso.CorsaId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazionePercorso.CorsaDataPartenza))))
LEFT JOIN RT_FoglioCaricoCorseElencoModificheDettaglio ON RT_PreparazioneBus.CorsaId = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaDataPartenza AND RT_PreparazioneBus.PrenotazioneId = RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneId
where ((`RT_PrenotazioneDettaglio`.`TipoServizio` = _latin1'Bus') and ((`RT_Prenotazione`.`PrenotazioneStato` = 1) or (`RT_Prenotazione`.`PrenotazioneStato` = 3))) and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza'  and BusId=$BusId and BusNumero=$busNumeroT
group by `RT_PreparazioneBus`.`CorsaId`,`RT_PreparazioneBus`.`DataPartenza`,`RT_PreparazioneBus`.`BusNumero`,`RT_PreparazioneBus`.`BusId`,`RT_PrenotazioneDettaglio`.`PrenotazioneId`,`RT_PrenotazioneDettaglio`.`ComunePartenza`,`RT_PrenotazioneDettaglio`.`DataPartenza`
order by `RT_Prenotazione`.`ClienteNome`
";
   // die($sql_da_incassare);
      $ArrObjectP = $db->fetch_array($sql_da_incassare);
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
            $TotaleIncasso=0;
              while ($np<$numeropasseggeri)
             {
                $ClienteNome= ucwords(strtolower($ArrObjectP[$np]['ClienteNome']));
                //$FermataSalita=$ArrObjectP[$np]['FermataPartenza'];
                $FermataSalita=$ArrObjectP[$np]['FermataPartenzaI'];
                $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
                //$ComuneSalita=$ArrObjectP[$np]['ComunePartneza'];
                $ComuneSalita=$ArrObjectP[$np]['ComunePartenzaI'];
                $ComuneDiscesa=$ArrObjectP[$np]['ComuneDiscesa'];
                $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
                $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
             //   $OraSalita=$ArrObjectP[$np]['DataOraSalita'];
                $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
                $PrenotazioneStatoId=$ArrObjectP[$np]['PrenotazioneStato'];
            //  $PrenotazioneTitoloId1=$ArrObjectP[$np]['PrenotazioneTitoloId'];
              
                
                $Tipo=$ArrObjectP[$np]['Tragitto'];
                $PrenotazionePercorsoId=$ArrObjectP[$np]['PrenotazionePercorsoId'];
                $Variazioni=$ArrObjectP[$np]['Variazioni'];
                 $Operazione="";
                if (!empty($Variazioni))
                $Operazione="v";
                
                
               /* 
                $Operazione=$ArrObjectP[$np]['Operazione'];
                $PrenotazionePercorsoId=$ArrObjectP[$np]['PrenotazionePercorsoId'];
                */
                
                $s="select PrenotazioneTitoloId from RT_PrenotazioneTitolo where PrenotazioneId=$PrenotazioneId and DataIns>'$DataPartenza'";
                $rowt=$db->query_first($s);
                
                
                $Importo=0;
                $Importo1=0;
                if (empty($rowt['PrenotazioneTitoloId']))
                {
                $Importo=$ArrObjectP[$np]['TotaleImportoPrenotazione'];
                $Importo1= number_format($Importo, 2,",",".");
                
                }
                
                $TotaleIncasso=$TotaleIncasso+$Importo;
           $np++;     
          }       
    
 
        $IncassoPre=$TotaleIncasso;
    
   
    
    $sql_da_incassare="Select IncassoPost from RT_PostViaggioIncassoPost where BusId=$BusId and BusNumero=$busNumeroT and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
    
    $sql_da_incassare="SELECT
RT_PreparazioneBus.CorsaId AS CorsaId,
RT_PreparazioneBus.DataPartenza AS DataPartenza,
RT_PreparazioneBus.BusId AS BusId,
RT_PreparazioneBus.BusNumero AS BusNumero,
RT_PreparazioneBus.OdcIdRef AS OdcIdRef,
RT_PrenotazioneDettaglio.ComunePartenza AS ComunePartenza,
RT_PrenotazioneDettaglio.FermataPartenza AS FermataPartenza,
RT_PreparazioneBus.PrenotazioneId AS PrenotazioneId,
RT_PrenotazioneDettaglio.ComuneArrivo AS ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo AS FermataArrivo,
count(`RT_PrenotazioneDettaglio`.`PrenotazioneDettaglioId`) AS NumeroPax,
RT_Prenotazione.ClienteNome AS ClienteNome,
RT_Prenotazione.ClienteCellulare AS ClienteCellulare,
RT_PrenotazioneDettaglio.Tragitto AS Tragitto,
RT_Prenotazione.PrenotazioneStato AS PrenotazioneStato,
RT_Prenotazione.TotalePaxPrenotati AS TotalePaxPrenotati,
RT_Prenotazione.TipoViaggioId AS TipoViaggioId,
RT_ImportoPerPrenotazione.DaIncassare AS TotaleImportoPrenotazione,
RT_PrenotazionePercorso.PrenotazionePercorsoId AS PrenotazionePercorsoId,
RT_PrenotazionePercorso.ComuneSalita AS ComunePartenzaI,
RT_PrenotazionePercorso.FermataSalita AS FermataPartenzaI,
RT_Prenotazione.ClienteSessoId AS ClienteSessoId,
RT_PrenotazioneDettaglio.DataArrivo AS DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo AS OrarioArrivo,
RT_PrenotazionePercorso.ComuneDiscesa AS ComuneDiscesa,
RT_PrenotazionePercorso.FermataDiscesa AS FermataDiscesa,
RT_PrenotazionePercorso.DataOraDiscesa AS DataOraDiscesa,
RT_Prenotazione.CodicePrenotazione AS CodicePrenotazione,
RT_Prenotazione.GestoreIdRef AS GestoreIdRef,
RT_PrenotazioneDettaglio.TipoViaggio AS TipoViaggio,
RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneStato as Variazioni
FROM
((((RT_PreparazioneBus
INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId) AND (RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId))))
INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId)))
INNER JOIN RT_ImportoPerPrenotazione ON ((RT_Prenotazione.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId)))
INNER JOIN RT_PrenotazionePercorso ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazionePercorso.CorsaId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazionePercorso.CorsaDataPartenza))))
LEFT JOIN RT_FoglioCaricoCorseElencoModificheDettaglio ON RT_PreparazioneBus.CorsaId = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaDataPartenza AND RT_PreparazioneBus.PrenotazioneId = RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneId
where ((`RT_PrenotazioneDettaglio`.`TipoServizio` = _latin1'Bus') and ((`RT_Prenotazione`.`PrenotazioneStato` = 1) or (`RT_Prenotazione`.`PrenotazioneStato` = 3))) and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza'  and BusId=$BusId and BusNumero=$busNumeroT
group by `RT_PreparazioneBus`.`CorsaId`,`RT_PreparazioneBus`.`DataPartenza`,`RT_PreparazioneBus`.`BusNumero`,`RT_PreparazioneBus`.`BusId`,`RT_PrenotazioneDettaglio`.`PrenotazioneId`,`RT_PrenotazioneDettaglio`.`ComunePartenza`,`RT_PrenotazioneDettaglio`.`DataPartenza`
order by `RT_Prenotazione`.`ClienteNome`
";
   // die($sql_da_incassare);
      $ArrObjectP = $db->fetch_array($sql_da_incassare);
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
            $TotaleIncasso=0;
              while ($np<$numeropasseggeri)
             {
                $ClienteNome= ucwords(strtolower($ArrObjectP[$np]['ClienteNome']));
                //$FermataSalita=$ArrObjectP[$np]['FermataPartenza'];
                $FermataSalita=$ArrObjectP[$np]['FermataPartenzaI'];
                $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
                //$ComuneSalita=$ArrObjectP[$np]['ComunePartneza'];
                $ComuneSalita=$ArrObjectP[$np]['ComunePartenzaI'];
                $ComuneDiscesa=$ArrObjectP[$np]['ComuneDiscesa'];
                $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
                $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
             //   $OraSalita=$ArrObjectP[$np]['DataOraSalita'];
                $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
                $PrenotazioneStatoId=$ArrObjectP[$np]['PrenotazioneStato'];
            //  $PrenotazioneTitoloId1=$ArrObjectP[$np]['PrenotazioneTitoloId'];
              
                
              $Tipo=$ArrObjectP[$np]['Tragitto'];
                $PrenotazionePercorsoId=$ArrObjectP[$np]['PrenotazionePercorsoId'];
                $Variazioni=$ArrObjectP[$np]['Variazioni'];
                 $Operazione="";
                if (!empty($Variazioni))
                $Operazione="v";
                
                
               /* 
                $Operazione=$ArrObjectP[$np]['Operazione'];
                $PrenotazionePercorsoId=$ArrObjectP[$np]['PrenotazionePercorsoId'];
                */
                $Importo=0;
                $Importo1=0;
                if (($Tipo!='Ritorno'))
                {
                $Importo=$ArrObjectP[$np]['TotaleImportoPrenotazione'];
                $Importo1= number_format($Importo, 2,",",".");
                
                }
                
                $TotaleIncasso=$TotaleIncasso+$Importo;
           $np++;     
          }       
    
 
        $IncassoPost=$TotaleIncasso;
        
           $sql_da_incassare="SELECT
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.OdcIdRef,
RT_PreparazioneBus.BusNumero,
count(RT_PrenotazioneNumero.PrenotazioneNumeroId) as TotaleViaggiatoriAutobus
FROM
RT_PreparazioneBus
INNER JOIN RT_Prenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId
INNER JOIN RT_PrenotazioneNumero ON RT_Prenotazione.PrenotazioneId = RT_PrenotazioneNumero.PrenotazioneId
WHERE (PrenotazioneStato=1 or PrenotazioneStato=3) and 
BusId=$BusId and BusNumero=$busNumeroT and CorsaId=$CorsaId and DataPartenza='$DataPartenza'
GROUP BY
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero
";
   
    $row=$db->query_first($sql_da_incassare);
    $TotaleViaggiatoriAutobus=0;
    
    if ($row['TotaleViaggiatoriAutobus']>0)
        {
        $TotaleViaggiatoriAutobus=$row['TotaleViaggiatoriAutobus'];
       
          }
          
           $sql_da_incassare="sELECT
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.OdcIdRef,
RT_PreparazioneBus.BusNumero,
count(RT_PrenotazioneNumero.PrenotazioneNumeroId) as TitoliDaEmettere
FROM
RT_PreparazioneBus
INNER JOIN RT_Prenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId
INNER JOIN RT_PrenotazioneNumero ON RT_Prenotazione.PrenotazioneId = RT_PrenotazioneNumero.PrenotazioneId
LEFT JOIN RT_PrenotazioneTitolo ON RT_PrenotazioneNumero.PrenotazioneNumeroId = RT_PrenotazioneTitolo.PrenotazioneNumeroId
WHERE
RT_Prenotazione.PrenotazioneStato = 1 AND
RT_PrenotazioneTitolo.PrenotazioneId IS NULL AND
RT_PrenotazioneTitolo.PrenotazioneTitoloId IS NULL and
BusId=$BusId and BusNumero=$busNumeroT and CorsaId=$CorsaId and DataPartenza='$DataPartenza'
GROUP BY
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero
          
";
$IncassoTot=$IncassoPost+$IncassoPre;
    $row=$db->query_first($sql_da_incassare);
    $TitoliDaEmettere=0;
    
    if ($row['TitoliDaEmettere']>0)
        {
        $TitoliDaEmettere=$row['TitoliDaEmettere'];
       
          }
        
  ?>
  
  <br /><br />
  
         <div class="titoli_di_viaggio">
                      <h2><?=$NomePullman?> - n.<?=$busNumeroT?></h2>
         <ul>
             <li>
                Viaggiatori effettivi: <span class="numero_titoli"><?=$TotaleViaggiatoriAutobus?></span> 
             </li>
             <li>
                Titoli da emettere: <span class="numero_titoli"><?= $TitoliDaEmettere?></span> 
             </li>
             
            
             <li>
                  Incassato a bordo da quadrare: <span class="numero_titoli"><?=  number_format($IncassoDaVerificare,2,",",".")?> &euro;</span> 
             </li>
             
             <li>
                  Incasso Quadrato: <span class="numero_titoli"><?=  number_format($IncassoPost,2,",",".")?> &euro;</span> 
             </li>
              <li>
                  Incasso Totale: <span class="numero_titoli"><?=  number_format($IncassoTot,2,",",".")?> &euro;</span> 
             </li>
                       
         </ul>
       <? if ($TitoliDaEmettere>0) { ?>
         <a href="#" id="<?=$BusId?>_<?=$busNumeroT?>" title="conferma quadratura autobus corrente" onclick="javascript:QuadraPullmanCorrente('<?=$BusId?>_<?=$busNumeroT?>','<?=$CorsaId?>','<?=$DataPartenza?>','<?=$BusId?>','<?=$busNumeroT?>');">CONFERMA E QUADRA AUTOBUS</a> 
         <? } ?>
         <br/>
         </div>
         
         
         
         
<?
     $nptipo++;
   }
   $nt++;
    }
  ?>
         
         
         
     </div>
</div>
     <? //spara_pulsanti_wizard(0) ?>
    
    
</form>
</div>

<?
    
    
}




function quadratura()
{
global $user,$HtmlCommon;
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
$db= new Database();
$db->connect();
$corsaobj=new Corsa($CorsaId);
$corsaobj->conn=$db;
$corsaobj->inizializzaDatiGenerali();
$arr_corsa=$corsaobj->DatiGenerali;

$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
$titolo=$titolo." / Quadratura";

$HtmlCommon->html_titolo_pagina($titolo);
$HtmlCommon->html_titolo_box($titolo);
$DataPartenzaF=$_REQUEST['DataPartenza'];

carica_menu_percorso(1);


include_once("postviaggio_quadratura_validator.php");           
include_once("postviaggio_quadratura_datatable.php");

global $user,$HtmlCommon,$db,$ModuloId, $dizionario;    
?>   
<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

<form id="application_form" name="application_form"  method="post" action="#">
<div class="brain_formModifica">
     <div class="brain_data-content">   
         <h2>Quadratura</h2>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
            
            	<tr class="brain_tabellaTr">
                        <th width="15%"><?=$dizionario['post']['bus']?></th>
                        <th width="5%">N</th>
                        <th width="15%"><?=$dizionario['generale']['stato']?></th>
                        <th width="10%"><?=$dizionario['post']['viaggiato']?></th>
                        <th width="10%"><?=$dizionario['generale']['num_itinerario']?></th>
						<th width="20%"><?=$dizionario['generale']['cliente']?></th>
                        <th width="20%"><?=$dizionario['generale']['da']?></th>
                        <th width="20%"><?=$dizionario['generale']['a']?></th>
                         <th width="10%"><?=$dizionario['biglietto']['tipo_viaggio']?></th>
                        <th width="5%"><?=$dizionario['generale']['pax']?></th>
                        <th width="10%"><?=$dizionario['generale']['totale']?></th>
                        <th width="5%"><?=$dizionario['post']['set']?></th>
                        <th width="5%"></th>
                       
		</tr>
            
		<tr class="brain_tabellaFilter">
                    <th><input type="text" /></th> 
                        <th><input type="text" /></th> 
                        <th><input type="text" /></th> 
                         <th><input type="text" /></th> 
                          <th><input type="text" /></th> 
			<th><input type="text" /></th> 
                        <th><input type="text" /></th> 
                        <th><input type="text" /></th> 
			<th><input type="text" /></th> 
                        <th><input type="text" /></th> 
                        <th><input type="text" /></th> 
                    <th><input type="hidden" /></th> 
                    <th><input type="hidden" /></th> 
			
		</tr>
	</thead>
	<tbody>
         
		<tr>
			<td colspan="13" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
		</tr>
	</tbody>
	
</table>
     </div>
</form>
</div>
</div>
<?   
$db->close();
 }

function QuadraturaPrenotazione()
{

  global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  

$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];
$PrenotazioneId=$_REQUEST['PrenotazioneId'];

$HtmlCommon->html_titolo_box ("Quadra prenotazione del ".$DataPartenza);
$Prenotazione=new Prenotazione($PrenotazioneId);
$Prenotazione->conn=$db;
$Prenotazione->inizializzaDettagliCarico();
$arr_carico=$Prenotazione->DatiGeneraliCarico;



$FermataIdAP=$arr_carico['FermataSalitaId'];
$FermataIdAD=$arr_carico['FermataDiscesaId'];

$ComuneSalitaId=$arr_carico['ComuneSalitaId'];
$ComuneDiscesaId=$arr_carico['ComuneDiscesaId'];


$TV=$arr_carico['TipoViaggioId'];

$Corsa=new Corsa($CorsaId);
$Corsa->conn=$db;
$arr_partenze=$Corsa->getAllPickup();
$arr_arrivi=$Corsa->getAllDropOff();

     include_once("postviaggio_validator.php");  
?>

<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                                    
                                    <?
                                    
                                    $page->create_select("Partenza:","Biglietto[PartenzaId]","PartenzaId","brain_campiform",$arr_partenze,$ComuneSalitaId,"ComuneId","Comune",  array(
                                                  "class"=>"'required'",
                                                  "onChange"=>"'javascript:MostraPossibiliDestinazioni(this);MostraElencoCorse();'"
                                                   ),1);
		
               
                $page->create_select("Destinazione:","Biglietto[DestinazioneId]","DestinazioneId","brain_campiform",$arr_arrivi,$ComuneDiscesaId,"ComuneId","Comune",array(
                                                  "class"=>"'required'",
                                                  "onChange"=>"'javascript:MostraElencoCorse();'"
                                                   ),1);
                
                
          /*       $page->create_select("Tipo viaggio:","Prenotazione[TipoViaggioId]","TipoViaggioId","brain_campiform",$arr_viaggio,0,"ViaggioId","Viaggio",array(
                                                  "class"=>"'required'",
                                                  "onChange"=>"'javascript:MostraElencoCorse();'"
                                                   ),1);*/
                                print("<br style=\"clear:both;\"/>");
                   print("<br style=\"clear:both;\"/>");     
                                    ?>
                                    
                                    
                                   
                                    <div id="InfoBiglietti">
                                         <?  print("<br style=\"clear:both;\"/>"); ?>
                                             <h2 class="sezione_prenotazioni">Biglietti e pax</h2>
  
                                                <div id="TipologiaBiglietti">
                                                        <?   GetTipologiaBiglietti($PrenotazioneId,$CorsaId,$FermataIdAP,$FermataIdAD,$TV);  ?>

                                                </div>   
                                     </div> 
                                </div>
                         </div>
                        <div class="divSubmit">
                                    <?
                                    
                                  $page->create_button("Quadra","Quadra","Quadra","brain_salva","submit");
                                  //$page->create_button("Cancella","Cancella","elimina","brain_cancella","button");
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    </div>   
		</div>
<?  
}

function spara_pulsanti_wizard($steptogo)
{
    
global $funzione_edit;

if ($funzione_edit)
    spara_pulsanti_edit($steptogo);
else
{
if (!$funzione_edit)
$page=new Form();
    
?>
<div class="divSubmit">
     
        <?  $page->create_button("Procedi","Procedi","Procedi","brain_salva","submit"); ?>
        
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>
    <?
    
    
}
}

function spara_pulsanti_edit($steptogo)
{
   
global $abilita_modifica;
    
    $page=new Form();
    
    ?>
<div class="divSubmit">
                         
        <?  $page->create_button("Salva","Salva","salva","brain_salva","submit"); ?>
       
         <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla">Annulla</a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>  
    <?
}

if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
	if(!isset($_REQUEST['do'])){
    	$do='';
    } else {
    	$do=$_REQUEST['do'];
    }
		
		
			switch($do) {
                                
                              case "QuadraturaPrenotazione":
                                 
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                      QuadraturaPrenotazione();   
                                  else
                                    Errors::$ErrorePermessiModuloFunzione;
                                  
                                  break;  

				default:
		                show_list();    
                		break;
			}
		

	

} 
// se l'utente non ÃƒÂ¨ loggato
else {
header("Location: /logout.php");
}
?>