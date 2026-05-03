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

$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
//Serve per definire il tipo di stampa
$type = $_REQUEST['type']; 
 
$sql="select TrattaNome,TrattaId,CorsaNome,DataPartenza from RT_FoglioNavette where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by TrattaNome,TrattaId,CorsaNome,DataPartenza";


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
<div class="container_tabella_<?= $type ?>">
   <div class="brain_formModifica">
     <div class="brain_data-content">     
         <h2>Foglio Navetta <?=$NomeTratta?> - n. <?=$contanavette?></h2>         
         <h2><?=$CorsaNome?> del <?=$DataPartenza?> - Pax <?=$PaxPerTratta?></h2>   
<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
            <tbody>
            <tr class="rowIntestazione">
                <td>P</td>
                <td>Cliente</td>
                <td>Partenza</td>
                <td>Arrivo</td>
                <td>Tel</td>
                <td>Presa</td>
            </tr>       
            
            <?
            $sql="select * from RT_FoglioNavette where TrattaId=$TrattaId and NumeroNavetta=$contanavette and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
            //echo($sql);
            
            $ArrObjectP = $db->fetch_array($sql);
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
                   
                ?>
            <tr>
                <td></td>
                <td></td>
            	<td class="presa"><?=$OraSalita." ".$FermataSalita?></td>
            </tr>
            <tr class="fine_record">
                 <td class="campo_grassetto"><?=$TotalePaxPrenotati?></td>
                 <td class="campo_grassetto"><?=$ClienteNome?></td>
                 <td><?=$ComuneSalita?></td>
                 <td><?=$ComuneDiscesa?></td>
                 <td><?=$ClienteCellulare?></td>
                 <td></td>
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