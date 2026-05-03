<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/stampa_report.css" type="text/css" />
<link rel="stylesheet" href="/css/stampa_biglietto.css" type="text/css" />
</head>
<!-- <body onload="window.print();">-->
<body>
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Aula.php");


$ModuloId=1;



function stampa_titoli_non_fiscali()
{

global $abilita_modifica,$tratta_wizard,$db,$user;

$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
// serve per definire il tipo di stampa
$type = $_REQUEST['type']; 
$BusNumero=$_REQUEST['NumeroBus'];
$TipoBusId=$_REQUEST['TipoBus'];
$seller_type=$_REQUEST['seller_type'];

$sql="select * from RT_FoglioCaricoTitoliNonFiscali where BusNumero=$BusNumero and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and BusId=$TipoBusId and CorsaDataPartenza='$DataPartenza'";

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
                         $FermataNomeP="";
                         $FermataOrarioP="";
                         $ComuneNomeP="";
                         if ($row['ComuneId']>0)
                        {
                         $dt=new DT();
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
                        
                 if (($MezzoSalita==2) and ($MezzoDiscesa==2))       
                 {
                     $ComuneSalitaBus=$Salita;
                     $FermataSalitaBus=$FermataSalita;
                     $OraFermataSalitaBus=substr($OraSalita,-5);
                     
                     $ComuneDiscesaBus=$Discesa;
                     $FermataDiscesaBus=$FermataDiscesa;
                     $OraFermataDiscesaBus=substr($OraDiscesa,-5);
                 }
                 // se salgo con la navetta e scendo con il pullman
                 elseif(($MezzoSalita==1) and ($MezzoDiscesa==2))
                 {
                     $ComuneSalitaBus=$ComuneNomeP;
                     $FermataSalitaBus=$FermataNomeP;
                     $OraFermataSalitaBus=substr($FermataOrarioP,0,5);
                     
                     $ComuneDiscesaBus=$Discesa;
                     $FermataDiscesaBus=$FermataDiscesa;
                      $OraFermataDiscesaBus=substr($OraDiscesa,-5);
                     
                     
                            $ComuneSalitaNavetta=$Salita;
                            $FermataSalitaNavetta=$FermataSalita;
                            $OraFermataSalitaNavetta=" il ".$OraSalita;
                        
                            $ComuneDiscesaNavetta=$ComuneSalitaBus;
                            $FermataDiscesaNavetta=$FermataSalitaBus;
                            $OraFermataDiscesaNavetta=" ore ".$OraFermataSalitaBus;
                     
                     
                 }
                 // se salgo con il pullman e scendo con la navetta
                 elseif(($MezzoSalita==2) and ($MezzoDiscesa==1))
                 {
                     $ComuneSalitaBus=$Salita;
                     $FermataSalitaBus=$FermataSalita;
                     $OraFermataSalitaBus=$OrarioPartenza;
                     
                     $ComuneDiscesaBus=$ComuneNomeD;
                     $FermataDiscesaBus=$FermataNomeD;
                     $OraFermataDiscesaBus=substr($FermataOrarioD,0,5);
                     
                       $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and MezzoId=1  order by FermataPeso asc limit 1";
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
                            $OraFermataDiscesaNavetta="il ".$OraDiscesa;
                        
                            
                        }  
                     
                     
                     
                 }       
               
                         if ($NextDay>0)
                             $OrarioArrivo=$OrarioArrivo." (+".$NextDay."gg)";
                  
                  
                  if ($TipoViaggioId==2)
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
                         
                         $FermataNomePR="";
                         $FermataOrarioPR="";
                         $ComuneNomePR="";
                         if ($row['ComuneId']>0)
                        {
                         $dt=new DT();
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
                     
                     
                     $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaIdRitorno and MezzoId=1  order by FermataPeso desc limit 1";
                     //   echo($sql);
                         $row = $db->query_first($sql);
                         
                         $FermataNomeD="";
                         $FermataOrarioD="";
                         $ComuneNomeD="";
                         if ($row['ComuneId']>0)
                        {
                            $ComuneDiscesaNavettaR=$row['Comune'];
                            $FermataDiscesaNavettaR=$row['FermataNome'];
                            $OraFermataDiscesaNavettaR="ore ".substr($row['Orario'],0,5);
                        
                            $ComuneSalitaNavettaR=$SalitaR;
                            $FermataSalitaNavettaR=$FermataSalitaR;
                            $OraFermataSalitaNavettaR="il ".$OraSalitaR;
                        
                            
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
                     
                     
                            $ComuneDiscesaNavettaR=$DiscesaR;
                            $FermataDiscesaNavettaR=$FermataDiscesaR;
                            $OraFermataDiscesaNavettaR="il ".$OraDiscesaR;
                        
                            $ComuneSalitaNavettaR=$ComuneDiscesaBusR;
                            $FermataSalitaNavettaR=$FermataDiscesaBusR;
                            $OraFermataSalitaNavettaR=$OraFermataDiscesaBusR;
                        
                     
                    
                 }         
                   if ($NextDayR>0)
                             $OrarioArrivo=$OrarioArrivo." (+".$NextDayR."gg)";
                        
                      
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
                   $TotBus=$TotBus+$delta;
                   $TotBusF=number_format($TotBus,2,",","");
                   $TotaleBiglietto=$TotBus+$TotNav;
                   
                   if ($tipo_titolo=='R')
                   $TotaleBiglietto=$TotaleBiglietto*(-1);    
                   
                   $TotaleBigliettoF=number_format($TotaleBiglietto,2,",","");
                   
                   
                  
                   $DataEticket=$ArrObjectP[$np]['DataEticket'];
                   $AnnoEticket=$ArrObjectP[$np]['Anno'];
                   $Eticket=$ArrObjectP[$np]['Eticket'];
                   
                   $CodiceBiglietto=$CodicePrenotazione."/".$PrenotazioneNumero;
                   $DataTitolo=$DataCorsa;
  ?>  

    <div>
<table id="t_top<?=$seller_type?>" class="container_tabella_<?= $type ?>">
    	<tbody>
        	<tr>
            	<td class="top_big">
                    <ul class="cella_madre">
                    	<table class="informazioni_viaggiatore">
                        	<tbody>
                            	<tr>                                	
                              	<td>
                                        <li>Titolo: <span><?=$CodiceBiglietto?></span></li>
                                    </td>
                                    <td>
                                        <li>del: <span><?=$DataTitolo?></span></li>
                                    </td>
                                    <td>
                                        <li>Tipo bigl.: <span><?=$TipologiaBiglietto?></span></li>
                                    </td>
                                 
                                </tr>
                                <tr>
                                	<td colspan="2">
                                    	<ul>
                                            <li>Nominativo: <span class="campo_big"><?=utf8_decode($ClienteNome)?></span></li>
                                        </ul>
                                    </td>
                                    <td>
                                        <li>Prezzo tot: <span class="campo_big"><?=$TotaleBigliettoF?> &euro;</span></li>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                        <? 
                        
                        $tipo_titolo='E';
                        if ($tipo_titolo=='E') { ?>
                        <table class="informazioni_viaggio">
                        	<tbody>
                            	<tr>
                                	<td colspan="2">
                                    	<li><span style="float:right; font-weight: normal">prezzo: <span class="campo_big_2">&euro; <?=$TotBusF?></span></span>Servizio di linea: <span class="campo_big_2"><?=$Percorso?></span></li>
                                    </td>
                                </tr>
                            	<tr>
                                	<td colspan="2">
                                    	<ul>
                                    		<li class="campo_big_2" style="text-align:center">ANDATA Partenza del: <span><?=$DataCorsa?></span></li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    	<ul>
                                    		<li>da: <span class="campo_big_2"><?=utf8_decode($ComuneSalitaBus)?></span> ore:<span><?=$OraFermataSalitaBus?></span> <span><?=utf8_decode($FermataSalitaBus)?></span></li>
                                        </ul>
                                    </td>
                                    <td>
                                    	<ul>
                                    		<li>a: <span class="campo_big_2"><?=utf8_decode($ComuneDiscesaBus)?></span> ore:<span><?=$OraFermataDiscesaBus?></span> <span><?=utf8_decode($FermataDiscesaBus)?></span></li>
                                        </ul>
                                    </td>
                                    <!--td>
                                        <li>Tipo bigl.: <span><?=$TipoViaggio?></span></li>
                                    </td-->
                                </tr>
                                <? if ($TipoViaggioId==2)
                        			{
                        		?>
                                <tr>
                                	<td colspan="2">
                                    	<ul>
                                    		<li class="campo_big_2"  style="text-align:center">RITORNO Partenza del: <span><?=$DataCorsaR?></span></li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    	<ul>
                                    		<li>da: <span class="campo_big_2"><?=utf8_decode($ComuneSalitaBusR)?></span> ore:<span><?=$OraFermataSalitaBusR?></span> <span><?=utf8_decode($FermataSalitaBusR)?></span></li>
                                        </ul>
                                    </td>
                                    <td>
                                    	<ul>
                                    		<li>a: <span class="campo_big_2"><?=utf8_decode($ComuneDiscesaBusR)?></span> ore:<span><?=$OraFermataDiscesaBusR?></span> <span><?=utf8_decode($FermataDiscesaBusR)?></span></li>
                                        </ul>
                                    </td>
                                    <!--td>
                                        <li>Tipo bigl.: <span><?=$TipoViaggio?></span></li>
                                    </td-->
                                </tr>
                                <?
                        			}
                        		?>
                            </tbody>
                        </table>
                        <li style="text-align:center"></li>
                            <? if ($TotNav>0) { ?>
                        
                        <table class="informazioni_viaggio">
                        	<tbody>
                            	<tr>
                                	<td colspan="2">
                                        <ul>
                                            <li style="font-size:9px">
                                        		<span style="float:right; font-weight: normal;">prezzo: <span class="campo_big_2">&euro; <?=$TotNavF?></span></span>SERVIZIO SHUTTLE 
                                            </li>
                                        </ul>
                                	</td>
                                </tr>
                            	<tr>
                                	<td>
                                    	<ul>
                                        	<li>ANDATA</li>
                                            <li>da: <span><?=utf8_decode($ComuneSalitaNavetta)?></span> <span><?=$OraFermataSalitaNavetta?> <?=utf8_decode($FermataSalitaNavetta)?></span></li>
                                            <li>a: <span><?=utf8_decode($ComuneDiscesaNavetta)?></span> <span><?=$OraFermataDiscesaNavetta?> <?=utf8_decode($FermataDiscesaNavetta)?></span></li>
                                           
                                        </ul>
                                    </td>
                                    <td>
                                    	<ul>
                                        	<li>RITORNO</li>
                                            <li>da: <span><?=utf8_decode($ComuneSalitaNavettaR)?></span> <span><?=$OraFermataSalitaNavettaR?> <?=utf8_decode($FermataSalitaNavettaR)?></span></li>
                                            <li>a: <span><?=utf8_decode($ComuneDiscesaNavettaR)?></span> <span><?=$OraFermataDiscesaNavettaR?> <?=utf8_decode($FermataDiscesaNavettaR)?></span></li>
                                         
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?} ?>
                        <? }
                        else
                        {
                           ?> 
                        <h2>TITOLO DI RIMBORSO</h2>
                            <?
                        }
                        
                        ?>
                        
                        
                        
                        <li class="rivendita<?=$TipoViaggioId?>" style="font-size:8px">Rivendita: <span><?=$Agenzia?></span> emesso con RE.TICKET/POWERED by www.braincomputing.com</li>
                    </ul>
                    <p></p>
                </td>
                    
                <td class="top_small">
                    <ul class="cella_figlia">
                        
                        <? 
                        if ($tipo_titolo=='E')
                        {
                        
                        if($TipoViaggioId==2)
                        {
                            
                         
                        
                            ?>
                        
                    	<li>RITORNO:</li>
                    	   
                        <li>Titolo: <span><?=$CodiceBiglietto?></span></li>
                        <li>Del: <span><?=$DataTitolo?></span></li>
                        <li>Nome: <span><?=utf8_decode($ClienteNome)?></span></li>
                        <li>Prezzo: <span><?=$TotaleBigliettoF?> &euro;</span></li>  
                        <li>Corsa del: <span><?=$DataCorsaR?></span></li>
                        <li>Part.: <span><?=utf8_decode($SalitaR)." (".$SiglaSalitaR.")"?></span></li>
                        <li>Dest.: <span><?=utf8_decode($DiscesaR)." (".$SiglaDiscesaR.")"?></span></li>
                        <li>Tipo bigl.: <span><?=$TipologiaBiglietto?></span></li>
                        <li>Rivendita: <span><?=$Agenzia?></span></li>
                      
                        <?
                        }
                        
                        else {
						?>
                        	<li class="scrivi_verticale">S<br/>O<br/>L<br/>O<br/><br/>A<br/>N<br/>D<br/>A<br/>T<br/>A</li>	
                        <?
                        }
                        }
                        else
                            {
						?>
                        	<li class="scrivi_verticale">R<br/>I<br/>M<br/>B<br/>O<br/>R<br/>S<br/>O</li>	
                        <?
                        }
                        ?>
                    </ul>
                    <p></p>
                </td>
                <td class="top_small">
                    <ul class="cella_figlia">
                        <?
                         if ($tipo_titolo=='E')
                        {?>
                            
                         
                    	<li>ANDATA:</li>
                    	 <li>Titolo: <span><?=$CodiceBiglietto?></span></li>
                        <li>Del: <span><?=$DataTitolo?></span></li>
                       
                        <li>Nome: <span><?=utf8_decode($ClienteNome)?></span></li>
                        <li>Prezzo: <span><?=$TotaleBigliettoF?> &euro;</span></li> 
                        <li>Corsa del: <span><?=$DataCorsa?></span></li>
                        <li>Part.: <span><?=utf8_decode($Salita)." (".$SiglaSalita.")"?></span></li>
                        <li>Dest.: <span><?=utf8_decode($Discesa)." (".$SiglaDiscesa.")"?></span></li>
                        <li>Tipo bigl.: <span><?=$TipologiaBiglietto?></span></li>
                        <li>Rivendita: <span><?=$Agenzia?></span></li>
                         <?
                        }
                        else {
                            ?>
                            
                                 	<li class="scrivi_verticale">R<br/>I<br/>M<br/>B<br/>O<br/>R<br/>S<br/>O</li>	
                  
                            <?
                        }
                        ?>
                        
                    </ul>
                    <p></p>
                </td>
                     <? if ($seller_type==2) { ?>
                   <td class="top_small_2">
                    <ul class="cella_figlia">
                    	<li>Agenzia:</li>
                        <li>TITOLO DI RIMBORSO</li>
                    	 <li>Titolo: <span><?=$CodiceBiglietto?></span></li>
                        <li>Del: <span><?=$DataTitolo?></span></li>
                       
                        <li>Nome: <span><?=utf8_decode($ClienteNome)?></span></li>
                        <li>Prezzo: <span><?=$TotaleBigliettoF?> &euro;</span></li> 
                        <li>Corsa del: <span><?=$DataCorsa?></span></li>
                        <li>Part.: <span><?=utf8_decode($Salita)." (".$SiglaSalita.")"?></span></li>
                        <li>Dest.: <span><?=utf8_decode($Discesa)." (".$SiglaDiscesa.")"?></span></li>
                        <li>Tipo bigl.: <span><?=$TipologiaBiglietto?></span></li>
                        <li>Rivendita: <span><?=$Agenzia?></span></li>
                               </ul>
                    <p></p>
                </td>
                    <?
               }
               ?>
               
            </tr>
        </tbody>
    </table>
        <?
        $np++;
             }
             
             if ($np==0)
                 print("Non ci sono biglietti da preparare");
             ?>
    </div>
    </body>
    </html>
<?

}






if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
			 $do=$_REQUEST['do'];
			if(!isset($do)) 
			$do='';
		
		
			switch($do) {
                                
                              

				default:
                                    stampa_titoli_non_fiscali();    
                		break;
			}
		

	

} 
// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}
?>