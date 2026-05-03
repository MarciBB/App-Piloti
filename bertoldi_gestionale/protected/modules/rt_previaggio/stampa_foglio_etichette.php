<?
$css_rnd=time();
?>
<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/stampa_report.css?<?=$css_rnd?>" type="text/css" />
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
include_once($classespath_."class.TipologiaBus.php");


$ModuloId=1;



function stampa_foglio_navette()
{

global $abilita_modifica,$tratta_wizard,$db,$user;

$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];

 $dt=new DT();
 $DataPartenzaFormattata=$dt->format($DataPartenza, "y-m-d", "d/m/Y");
 $c=new Corsa($CorsaId);
 $c->conn=$db;
$c->inizializzaDatiGenerali();

 $arr_c=$c->DatiGenerali;
 
 $OraPartenza=$arr_c['OrarioPartenza'];              

// serve per definire il tipo di stampa
$type = $_REQUEST['type']; 

if (isset($_REQUEST['TipoBus']))
{
    $BusId=$_REQUEST['TipoBus'];
    $sql="select TipologiaBus,BusId,CorsaNome,DataPartenza from RT_FoglioCaricoBusPerCorsa where BusId=$BusId and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' ";
}
else 
$sql="select TipologiaBus,BusId,CorsaNome,DataPartenza from RT_FoglioCaricoBusPerCorsa where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";



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
         if (!isset($_REQUEST['TipoBus']))
         {
           $sql="select max(BusNumero) as countnavette from RT_PreparazioneBus where BusId=$BusId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by BusId,DataPartenza,CorsaId";
           $row = $db->query_first($sql);
           $numero_navette=1;
           if (!empty($row['countnavette']))
           $numero_navette=$row['countnavette'];
         $contanavette=1;
         }
         else
         {
             $contanavette=$_REQUEST['NumeroBus'];
             $numero_navette=$contanavette;
             
         }
         while ($contanavette<=$numero_navette)
         {
             $TotaleIncasso=0;
          $sql="select PostiPerPullman from RT_FoglioCaricoPasseggeriPerBusPerCorsa where BusNumero=$contanavette and OdcIdRef=$user->OdcId and BusId=$BusId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
              $row1 = $db->query_first($sql);
            $PaxPerTratta=1;
            if (!empty($row1['PostiPerPullman']))
            $PaxPerTratta=$row1['PostiPerPullman'];
    
?>
<div class="container_tabella_<?= $type ?>">
  <div class="intestazione_tabella">
  		
        <!--<img src="/images/intestazione.png" width="100%" alt=""/> p class="intestazione_data">23/11/2012</p-->
  <?
                     $Pullman="";
                    $Autista1="";
                    $Autista2="";
                    $Telefono="";
                    $NumeroPullman="";
               
                $sq="select * from RT_PreparazioneBusAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$contanavette";
                $r=$db->query_first($sq);
                
                
                
                if ($r['BusId']>0)
                {
                    $Pullman=$r['Pullman'];
                    $Autista1=$r['Autista1'];
                    $Autista2=$r['Autista2'];
                    $Telefono=$r['Telefono'];
                    $NumeroPullman=$r['NumeroPullman'];
                    
                }
  
  ?>
  
  

<!-- ETICHETTE -->

<div class="container_tabella_<?= $type ?>">
  
  <div class="brain_formModifica">
     <div class="brain_data-content">     
          
        
         
         <?
         $contapasseggeri=0;
         $tb=new TipologiaBus($BusId);
    $tb->conn=$db;
    $tb->inizializzaDatiGenerali();
    $arr_tb=$tb->DatiGenerali;
    
    $NumeroPiani=$arr_tb['NumeroPiani'];
    $NumeroColonne=$arr_tb['Colonne'];
    $NumeroRighe=$arr_tb['Righe'];
         $busNumeroT=$contanavette;
         
         $npiani=1;
     while ($npiani<=$NumeroPiani)
     {
     ?>
                             <?
                        $i=0;
                        $alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
                          while ($i< $NumeroColonne)
                            {
                             ?>
                          
                            <?  
                              $i++;
                            }
                          ?>
                                    </tr>
                       <?
                     
                        $i=0;
                        
                          while ($i< $NumeroRighe)
                            {
                             
                               ?>
                           
                             <?
                             $n=0;
                             while ($n< $NumeroColonne)
                             {
                                //$BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];   
                                $fisso="";
                                $percentuale="";
                                $rigacorrente=$i+1;
                                $colonnacorrente=$n+1;
                                $sql="Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and TipologiaBusId=$BusId and OdcIdRef=$user->OdcId";
                               
                                $row1 = $db->query_first($sql);
                                $NumeroPosto="";
                                $DescrizionePosto="";
                                if (!empty($row1['TipologiaBusId']))
                                {
                                   $NumeroPosto=$row1['NumeroPosto'];
                                   $DescrizionePosto=$row1['DescrizionePosto'];
                                }
                                
                               
                               
                                  //  echo($sql);
                               
                                     
                             ?>
                      
                         
                         
		
	<?
       // $NumeroPosto=1;
        if ($NumeroPosto>0)
        {
            $sql="Select * from RT_ViewCorsaDataElencoPostiPrenotati where BusNumero=$busNumeroT and Piano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId";
                 $sql="Select * from RT_FoglioCaricoPosti where  Piano=$npiani and BusId=$BusId and BusNumero=$busNumeroT and Riga=$rigacorrente and Colonna=$colonnacorrente and CorsaId=$CorsaId and DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId";
         
            $sql="SELECT
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.PrenotazioneId,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero,
RT_PreparazioneBus.OdcIdRef,
RT_PrenotazionePosto.Riga,
RT_PrenotazionePosto.Colonna,
RT_PrenotazionePosto.Posto,
RT_PrenotazionePosto.DescrizionePosto,
RT_PrenotazionePosto.Piano,
RT_PrenotazionePosto.PreferenzaPiano,
RT_PrenotazionePosto.TipoPrenotazione,
RT_Prenotazione.TipoViaggioId,
RT_Prenotazione.PrenotazioneModPre,
RT_Prenotazione.ClienteNome,
RT_Prenotazione.ClienteSessoId,
RT_Prenotazione.ClienteCellulare
FROM
RT_PreparazioneBus
INNER JOIN RT_PrenotazionePosto ON RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePosto.PrenotazioneId AND RT_PreparazioneBus.CorsaId = RT_PrenotazionePosto.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_PrenotazionePosto.DataPartenza
INNER JOIN RT_Prenotazione ON RT_PrenotazionePosto.PrenotazioneId = RT_Prenotazione.PrenotazioneId
WHERE
(RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3) and Piano=$npiani and BusId=$BusId and BusNumero=$busNumeroT and Riga=$rigacorrente and Colonna=$colonnacorrente and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza' and RT_PreparazioneBus.OdcIdRef=$user->OdcId";    
 
            $row2 = $db->query_first($sql);
            $isReserved=0;
                             $Pid=0;
                             $Preferenza=0;
                             $ClienteNome="";
                             $ClienteSessoId=0;
                             $PrenotazionePostoId=0;
                             $TipoPrenotazione=0;
                              if (!empty($row2['OdcIdRef']))
                              {
                                  $isReserved=1;
                                  $PrenotazionePostoId=$row2['PrenotazionePostoId'];
                                  $Preferenza=$row2['PreferenzaPiano'];
                                  $Pid=$row2['PrenotazioneId'];
                                  $ClienteNome=  ucwords(strtolower($row2['ClienteNome']));
                                  $ClienteSessoId=$row2['ClienteSessoId'];
                                  $TipoPrenotazione=$row2['TipoPrenotazione'];
                              }
            
            
            // se riservato
                              ?>
                
         <?  
         $classe_sesso="nd";
         if ($ClienteSessoId==1)
             $classe_sesso="maschio";
         elseif ($ClienteSessoId==2)
         $classe_sesso="femmina";
    $contapasseggeri_old=0;     
    if (!empty($ClienteNome))
    {
        
    if (($contapasseggeri==0)  or ($contapasseggeri==4))
    {
        if ($contapasseggeri>0)
        {
            ?>
                                    <div style="page-break-after:always;"></div>
            <?
                                    
            
        }
        $contapasseggeri=0;
        ?>
      <!--  <div class="blocco_passeggero_container">   -->
            
        <?
    }
        ?>
    
        <div class="blocco_passeggero">
            <p class="numero"><?=$NumeroPosto?></p>
            <p class="nome_persona"><?=$ClienteNome?></p>
            <p class="posto"><?=$DescrizionePosto?></p>
        </div><!-- fine blocco_passeggero -->
        <?
    if (($contapasseggeri==0))
    {
       
        ?>
    <!-- </div> -->
        <?
    } 
    $contapasseggeri++;
     }
    }
                                 
                                 $n++;
                             }
                               $i++;
                          }
                       
                       
         
   
         $npiani++;
     }
         
         ?>
         
         
         
         
         
                                
            <br/><hr/><hr/><br/>
            	<!-- contolla i campi sottostanti -->
             
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