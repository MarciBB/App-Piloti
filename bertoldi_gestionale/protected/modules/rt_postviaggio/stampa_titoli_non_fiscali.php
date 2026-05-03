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

$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
// serve per definire il tipo di stampa
$type = $_REQUEST['type']; 
         
$sql="select TipologiaBus,BusId,CorsaNome,DataPartenza from RT_FoglioBusCarico where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by TipologiaBus,BusId,CorsaNome,DataPartenza";


$ArrObject = $db->fetch_array($sql);
$numerotratte=sizeof($ArrObject); 
if ($numerotratte>0)
{
    $nt=0;
    while ($nt<$numerotratte)
    {
        $BusId=$ArrObject[$nt]['BusId'];
      
        $TipologiaBus=$ArrObject[$nt]['TipologiaBus'];  
        $CorsaNome=$ArrObject[$nt]['CorsaNome'];
        $DataPartenza=$ArrObject[$nt]['DataPartenza'];
    
       
    
    
         $numero_navette=1; 
         $contanavette=1;
         
           $sql="select max(BusNumero) as countnavette from RT_PreparazioneBus where BusId=$BusId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by BusId,DataPartenza,CorsaId";
           
           
           $row = $db->query_first($sql);
           $numero_navette=1;
           if (!empty($row['countnavette']))
           $numero_navette=$row['countnavette'];
           
           
           
           
           
           
      
         $contanavette=1;
         while ($contanavette<=$numero_navette)
         {
             $TotaleIncasso=0;
            $sql="select PostiPerPullman from RT_FoglioBusCaricoPaxPullman where BusNumero=$contanavette and OdcIdRef=$user->OdcId and BusId=$BusId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
            $row1 = $db->query_first($sql);
            $PaxPerTratta=1;
            if (!empty($row1['PostiPerPullman']))
            $PaxPerTratta=$row1['PostiPerPullman'];
    
?>
<div class="container_tabella_<?= $type ?>">
  <div class="intestazione_tabella">
  		<img src="/images/intestazione.png" alt=""/>
        <!--p class="intestazione_data">23/11/2012</p-->
  </div>
  <div class="brain_formModifica">
     <div class="brain_data-content">     
         <h2 class="intestazione_carico">Foglio di carico  bus <?=$TipologiaBus?> - n. <?=$contanavette?></h2>         
         <h2 class="intestazione_corsa"><?=$CorsaNome?> del <?=$DataPartenza?> - Pax <?=$PaxPerTratta?></h2>   
<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule2">
            <tbody>
            <tr class="rowIntestazione">
                <td> - </td>
                <td>P</td>
                <td>Cliente</td>
                <td>Partenza</td>
                <td>Arrivo</td>
                <td>Tel</td>
                <td>Totale</td>
                <td>P.<br/>A.</td>
                <td>Data R.</td>
                <td>Tipo</td>
                <td>I<br/>n<br/>t<br/>e<br/>r<br/>i</td>
                <td>R<br/>i<br/>d<br/>o<br/>t<br/>t<br/>i</td>
                <td>A<br/>R</td>
                <td>R<br/>i<br/>d<br/>o<br/>t<br/>t<br/>i<br/><br/>A<br/>R</td>
            </tr>       
            
            <?
            
            // ciclo le fermate
            
            
            $sql="select * from RT_FoglioBusCaricoPickupFermata where CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
         //  echo($sql);
            $ArrObjectPk = $db->fetch_array($sql);
            $npk=sizeof($ArrObjectPk);
            $pk=0;
              while ($pk<$npk)
             {
                  
             $FermataPId=$ArrObjectPk[$pk]['NFermataId'];
             $FermataP=$ArrObjectPk[$pk]['NFermata'];
             $ComuneP=$ArrObjectPk[$pk]['NComune'];
             $TotPickupFermata=$ArrObjectPk[$pk]['TotPickupFermata'];
             
             $sql="select * from RT_FoglioBusCarico where NFermataId=$FermataPId and BusId=$BusId and BusNumero=$contanavette and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
            
             
             $ArrObjectP = $db->fetch_array($sql);
            ?>
             <tr class="gruppo_persone">
             	<td></td>
                <td></td>
                <td><?=$FermataP." ".$ComuneP." ".$TotPickupFermata?></td>                
             </tr>       
             <tr></tr>
            <?
             
            
            
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
              while ($np<$numeropasseggeri)
             {
                $ClienteNome=$ArrObjectP[$np]['ClienteNome'];
                $FermataSalita=$ArrObjectP[$np]['FermataSalita'];
                $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
                $ComuneSalita=$ArrObjectP[$np]['ComuneSalita'];
                $ComuneDiscesa=$ArrObjectP[$np]['ComuneDiscesa'];
                $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
                $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
                $OraSalita=$ArrObjectP[$np]['DataOraSalita'];
                $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
                $PrenotazioneStatoId=$ArrObjectP[$np]['PrenotazioneStatoId'];
                $PaxAssegnati=$ArrObjectP[$np]['PaxAssegnati'];
                $Tipo=$ArrObjectP[$np]['Tipo'];
                $Operazione=$ArrObjectP[$np]['Operazione'];
                
                $Importo=0;
                $Importo1=0;
                if ($PrenotazioneStatoId==1)
                {
                $Importo=$ArrObjectP[$np]['TotaleImportoPrenotazione'];
                $Importo1= number_format($Importo, 2,",",".");
                
                }
                
                $TotaleIncasso=$TotaleIncasso+$Importo;
                
                $PostiAssegnati="0";
                
               $DataRitorno=" - ";
                
                // calcola posti assegnati
                 $sql="select CorsaDataPartenza from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and Direzione='R'";
                // echo($sql);
                 
                 $row = $db->query_first($sql);
               $DataRitornoR=" - ";
                if ($row['CorsaDataPartenza'])
                    {
                      $dt=new DT();
                     $DataRitorno=$row['CorsaDataPartenza'];
                      $DataRitornoR=$dt->format($DataRitorno, "y-m-d", "d/m/Y");
               
                }
                 
                
                if ($DataRitorno==$DataPartenza)
                    $Tipo='Ritorno';
                elseif ($DataRitorno==' - ')
                    $Tipo='Semplice';
                else 
                    $Tipo='Andata';
                
                
              
            
               
                ?>
            <tr>
                 <td><?=$Operazione?></td>
              <td><?=$TotalePaxPrenotati?></td>
              <td><?=$ClienteNome?></td>
              <td><?=$ComuneSalita?></td>
              <td><?=$ComuneDiscesa?></td>
              <td><?=$ClienteCellulare?></td>
              <td><?=$Importo1?> &euro;</td>
              <td><?=$PaxAssegnati?></td>
              <td><?=$DataRitornoR?></td>
              <td><?=$Tipo?></td>
              <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=1";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=3";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=2";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=4";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                ?>
              <td><?=$paxx?></td>
                
                
                
                
            
              
              
           </tr>
                <?
                $np++;
            }
            
                $pk++;
            }
            
            // fine ciclo fermate
            
            ?>
            
            
           </tbody>
          </table>
                                
            <br/><hr/><hr/><br/>
            	<!-- contolla i campi sottostanti -->
                <table style=" width: 100%">
                    <tr>
                        <td class="passeggeri_prezzo_2">
                           Totale Pax: <?=$PaxPerTratta?>
                        </td>
                        <td class="passeggeri_prezzo_2">
                            <?  $TotaleIncasso1= number_format($TotaleIncasso, 2,",","."); ?>
                           Totale da Incassare: <?=$TotaleIncasso1?> &euro;
                        </td>
                    </tr>
                </table>
            
            <div class="footer_tabella">
  				<img src="/images/footer.png" alt=""/>
        		<!--p class="intestazione_data">23/11/2012</p-->
  			</div>
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