<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/stampa_report.css" type="text/css" />
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



function stampa_foglio_navette()
{
    global $abilita_modifica,$tratta_wizard,$db,$user;
?>


         
         <?
 $dt=new DT();
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
//Serve per definire il tipo di stampa
$type = $_REQUEST['type']; 
 $trattaid=$_REQUEST['TrattaId'];
if ($trattaid>0)
 $sql="select TrattaNome,TrattaId,CorsaNome,DataPartenza from RT_FoglioNavette where TrattaId=$trattaid and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by TrattaNome,TrattaId,CorsaNome,DataPartenza";
else
$sql="select TrattaNome,TrattaId,CorsaNome,DataPartenza from RT_FoglioNavette where  OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by TrattaNome,TrattaId,CorsaNome,DataPartenza";    

$ArrObject = $db->fetch_array($sql);
$numerotratte=sizeof($ArrObject); 
if ($numerotratte>0)
{
    $nt=0;
    while ($nt<$numerotratte)
    {
        $TrattaId=$ArrObject[$nt]['TrattaId'];
      
        $NomeTratta=$ArrObject[$nt]['TrattaNome'];  
        $CorsaNome=$ArrObject[$nt]['CorsaNome'];
        $DataPartenza=$ArrObject[$nt]['DataPartenza'];
        $DataPartenzaFormattata=$dt->format($DataPartenza, "y-m-d", "d/m/Y");
       
    
    
         $numero_navette=1; 
         $contanavette=1;
         
           $sql="select max(NumeroNavetta) as countnavette from RT_PreparazioneNavette where TrattaId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by TrattaId,DataPartenza,CorsaId";
           $row = $db->query_first($sql);
           $numero_navette=1;
           if (!empty($row['countnavette']))
           $numero_navette=$row['countnavette'];
           
           
           
           
           
           
      
         $contanavette=1;
         while ($contanavette<=$numero_navette)
         {
              $sql="select PaxPerTratta from RT_FoglioNavettePaxTrattaNavetta where NumeroNavetta=$contanavette and OdcIdRef=$user->OdcId and TrattaId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
          // echo($sql);
            $row1 = $db->query_first($sql);
           $PaxPerTratta=1;
           if (!empty($row1['PaxPerTratta']))
           $PaxPerTratta=$row1['PaxPerTratta'];
    
?>
<div class="container_tabella_always_<?= $type ?>">
    
    <div class="intestazione_tabella">
  		
        <!--<img src="/images/intestazione.png" width="100%" alt=""/> p class="intestazione_data">23/11/2012</p-->
  <?
                    $targa="";
                    $autista="";
                  
               
               $sq="select * from RT_PreparazioneNavetteAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and TrattaId=$TrattaId and NavettaNumero=$contanavette";
// echo($sq);
 $targa="";
        $autista="";
    $r=$db->query_first($sq);
    if ($r['TrattaId']>0)
    {
        $targa=$r['Navetta'];
        $autista=$r['Autista'];
    }
  
  ?>
  
   <!--p class="intestazione_data"><span class="etichetta_riquadro">Pullman:</span><span class="riquadro"><?//=$Pullman?></span> <span class="etichetta_riquadro">Telefono:</span><span class="riquadro"><?//=$Telefono?></span> <span class="etichetta_riquadro">Autista1:</span><span class="riquadro"><?//=$Autista1?></span> <span class="etichetta_riquadro">Autista2:</span><span class="riquadro"><?//=$Autista2?></span></p-->
   <table class="intestazione_info">
   		<thead>
        	<tr>
                 
            	<td>
                	Pullman
                </td>
               	<td>
                	Autista 
                </td>
               	
            </tr>
        </thead>
        <tbody>
        	<tr>
                    <td>
                	<?=$targa?>
                </td>
            	
               	<td>
                	<?=$autista?>
                </td>
            </tr>
        </tbody>        
   </table>  
  
  </div>
    
    
   <div class="brain_formModifica">
     <div class="brain_data-content">     
         <h2>Foglio Navetta <?=$NomeTratta?> - n. <?=$contanavette?></h2>         
         <h2><?=$CorsaNome?> del <?=$DataPartenzaFormattata?> - Pax <?=$PaxPerTratta?></h2>   
<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
            <tbody>
            <tr class="rowIntestazione">
                <td>Posti</td>
                <td>Cliente</td>
                <td>Telefono</td>
                <td>Note</td>
            </tr>       
            
            <?
           
            $sql="select * from RT_FoglioNavettaStampa where TrattaId=$TrattaId and NumeroNavetta=$contanavette and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenzaUfficiale='$DataPartenza'";
           // echo($sql);
            
            $ArrObjectP = $db->fetch_array($sql);
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
            $check='';
              while ($np<$numeropasseggeri)
             {
                  
                $Tragitto=$ArrObjectP[$np]['Tragitto'];
                $ClienteNome=ucwords(strtolower($ArrObjectP[$np]['ClienteNome']));
                $FermataSalita=$ArrObjectP[$np]['FermataPartenza'];
                $FermataDiscesa=$ArrObjectP[$np]['FermataArrivo'];
                $ComuneSalita=  strtoupper($ArrObjectP[$np]['ComunePartenza']);
                $ComuneDiscesa=$ArrObjectP[$np]['ComuneArrivo'];
                $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
                $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
                $OraSalita=$ArrObjectP[$np]['DataPartenza']." ".$ArrObjectP[$np]['OrarioPartenza'];
                $OraDiscesa=$ArrObjectP[$np]['DataArrivo']." ".$ArrObjectP[$np]['OrarioArrivo'];
                $OraSalitaF=$dt->format($OraSalita, "Y-m-d H:i:s", "d/m/Y H:i:s");
                $OraDiscesaF=$dt->format($OraDiscesa, "Y-m-d H:i:s", "d/m/Y H:i:s");
               
            //   print($checknew." ".$check);
                if ($ComuneSalita=='LAGONEGRO')
                {
                    $ComuneSalita=$ComuneDiscesa; 
                    $OraSalita=$OraDiscesa;
                    $OraSalitaF=$OraDiscesaF;
                    $FermataSalita=$FermataDiscesa;
                }
                 $checknew=trim($ComuneSalita." - ".$FermataSalita);
            //    print($check." ".$checknew."<br />");
                if ($check=='')
                {
                $check=trim($ComuneSalita." - ".$FermataSalita);
                
                 /*$sql="select sum(TotalePaxPrenotati) as TotalePasseggeri from RT_FoglioNavettaStampa where TrattaId=$TrattaId and NumeroNavetta=$contanavette and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza'";
                 $rsum=$db->query_first($sql);
                  $TotalePaxNavetta=0;
                 if (!empty($rsum['TotalePasseggeri']))
                $TotalePaxNavetta=$rsum['TotalePasseggeri'];*/
                
                
                ?>
                <tr class="fine_record">
                    <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    <td colspan="5"><h3><?=$check?> - <?=$OraSalitaF?></h3></td>
                </tr>
                <?
                }
                elseif($check!=$checknew)
                {
                    $check=trim($ComuneSalita." - ".$FermataSalita);
                    ?>
                <tr class="fine_record">
                     <td>&nbsp;</td>
                       <td>&nbsp;</td>
                    <td colspan="5"><h3><?=$check?> - <?=$OraSalitaF?></h3></td>
                </tr>
                <?
                    
                }
                ?>
            
            <tr class="fine_record">
                 <td class="campo_grassetto"><?=$TotalePaxPrenotati?></td>
                 <td class="campo_grassetto"><?=$ClienteNome?></td>
                <!-- <td><strong><?=$ComuneSalita?></strong> - <?=$OraSalitaF." ".$FermataSalita?></td>
                 <td><strong> <?=$ComuneDiscesa?></strong> - <?=$OraDiscesaF." ".$FermataDiscesa?></td>-->
                 <td><?=$ClienteCellulare?></td>
                 <?
                  // verifico se il cliente ha delle note 
                 $TipoNota="S";
                 $TipoNota1="Note salita: ";
                 
                 if ($Tragitto=='Ritorno')
                 {
                     $TipoNota="D";
                     $TipoNota1="Note discesa: ";
                 }
                 
            $sql="SELECT
RT_PrenotazionePercorsoNote.Nota,
RT_PrenotazionePercorsoNote.TipoNota,
RT_PrenotazionePercorso.CorsaId,
RT_PrenotazionePercorso.CorsaDataPartenza,
RT_PrenotazionePercorso.PrenotazioneId
FROM
RT_PrenotazionePercorso
INNER JOIN RT_PrenotazionePercorsoNote ON RT_PrenotazionePercorso.PrenotazionePercorsoId = RT_PrenotazionePercorsoNote.PrenotazionePercorsoId AND RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazionePercorsoNote.PrenotazioneId
 where CorsaDataPartenza='$DataPartenza' and CorsaId=$CorsaId and RT_PrenotazionePercorso.PrenotazioneId=$PrenotazioneId and RT_PrenotazionePercorsoNote.TipoNota='S'";       
            
         //  echo($sql);
           $ArrObjectNote = $db->fetch_array($sql);
            /*$row = $db->query_first($sql);
            $numero_note=0;
            $ArrObjectNote = $db->fetch_array($sql);
            $n_note=sizeof($ArrObjectNote);
            $nota=" - ";
            if ($n_note>0)
              $nota=$TipoNota1.$ArrObjectNote[0]['Nota'];*/
            
            $n_note=sizeof($ArrObjectNote);
               $contanote=0;
               $Nota="";
               while($contanote<$n_note)
               {
                  $TipoNota= $ArrObjectNote[$contanote]['TipoNota'];
                   $Nota1= $ArrObjectNote[$contanote]['Nota'];
                  if ($TipoNota=='S')
                      $Nota.=" - <strong>Note Salita:</strong> ".$Nota1;
                  if ($TipoNota=='D')
                      $Nota.=" - <strong>Note Discesa:</strong> ".$Nota1;
                   if ($TipoNota=='B')
                      $Nota.=" - <strong>Note Biglietto:</strong> ".$Nota1;
                    //if ($TipoNota=='P')
                      //$Nota.=" - <strong>Note Posto:</strong> ".$Nota1;
                     if ($TipoNota=='G')
                      $Nota.=" - <strong>Note Autista:</strong> ".$Nota1;
              $contanote++; 
                     
               }
            
            ?>
        
                 
                 <td><?=$Nota?></td>
            </tr>
            
                <?
                $np++;
            }
            
            ?>
            
            
           </tbody>
          </table>
           </div>
  </div>
</div><!-- fine container_tabella -->
<?      $contanavette++;
        }
        ?>
                  
   
         <?
        $nt++;
    }
}
?>

<!-- creare foglio di default -->

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
		                stampa_foglio_navette();    
                		break;
			}
		

	

} 
// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}
?>