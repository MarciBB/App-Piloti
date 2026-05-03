<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/stile_prenotazioni.css" />
<?php 
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
include_once($classespath_."/class.TipologiaBus.php");
include_once($classespath_."/class.DT.php");
global $ModuloId;
$ModuloId=7;// modulo base mediazione
global $user;
global $prenotazione_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$prenotazione_wizard=null;

if(isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
$prenotazione_wizard=unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
}




function exportExcel(){
	global $user,$HtmlCommon,$ModuloId;
	$db= new Database();
	$db->connect();
	
	$CorsaId=$_REQUEST['CorsaId'];
	$CorsaIdA=$CorsaId;
	$DataCorsa=$_REQUEST['DataCorsa'];
	$Order=$_REQUEST['Order'];
	
	$sql = "SELECT StatoPrenotazione as 'Stato Prenotazione', 
			RagioneSociale as Agenzia,
			NumeroBiglietto as 'Num. Biglietto', 
			Cliente,
			ClienteCellulare as Cellulare,
			OrarioPartenza as 'Orario Fermata',
			ComunePartenza as Partenza,
			ComuneArrivo as Arrivo,
			TipoViaggio as 'Tipo Viaggio',
			Importo as Totale
			From RT_ViewBigliettiPrenotati
			Where CorsaId=$CorsaId and DataPartenza ='".$DataCorsa."'";
	
	$data = $db->fetch_array($sql);
	$filename = "corsa.xls";
	
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: application/vnd.ms-excel");
	
	$flag = false;
	foreach($data as $row) {
		if(!$flag) {
			// display field/column names as first row
			echo implode("\t", array_keys($row)) . "\n";
			$flag = true;
		}
		array_walk($row, 'cleanData');
		echo implode("\t", array_values($row)) . "\n";
	}
	
	$db->close();
}



function show_list()
{
   
global $user,$HtmlCommon,$ModuloId, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['biglietto']['titolo_servizi_su_corsa'],0,"","");
$HtmlCommon->html_titolo_box($dizionario['biglietto']['titolo_servizi_su_corsa']);
$db= new Database();
$db->connect();

$CorsaId=$_REQUEST['CorsaId'];
$CorsaIdA=$CorsaId;
$DataCorsa=$_REQUEST['DataCorsa'];
$Order=$_REQUEST['Order'];
$Data=$DataCorsa;
$DataCorsaAndata=$DataCorsa;
include_once("biglietto_validator.php");           
include_once("biglietto_datatable_servizi.php");

global $user,$HtmlCommon,$db,$ModuloId;    
 


?>   






<script>
	$(function() {
		$( "#tabs_posti" ).tabs();
               
});
</script>



         
<a href="#" onclick="self.location.href = 'protected/modules/rt_biglietto/exportServiziExcel.php?CorsaId=<?php echo $CorsaId;?>&DataCorsa=<?php echo $DataCorsa;?>&action=xls&Order=<?php echo $Order;?>'"><?=$dizionario['biglietto']['esporta_lista']?></a>
<br/><br/> 
<?=$dizionario['biglietto']['scegli_ordine']?><br/>
<ul style="list-style-type: inherit; padding-left:20px;">
	<li><a href="#" onclick="ExternalLoad('rt_biglietto','servizi_corsa.php?do=show_list&CorsaId=<?php echo $CorsaId;?>&DataCorsa=<?php echo $DataCorsa;?>&Order=0');"><?=$dizionario['biglietto']['ordina_partenza']?></a></li>
	<li><a href="#" onclick="ExternalLoad('rt_biglietto','servizi_corsa.php?do=show_list&CorsaId=<?php echo $CorsaId;?>&DataCorsa=<?php echo $DataCorsa;?>&Order=1');"><?=$dizionario['biglietto']['ordina_arrivo']?></a></li>         
</ul>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
    	<tr class="brain_tabellaTr">
       		<th width="1%"><?=$dizionario['generale']['stato']?></th>
            <th width="15%"><?=$dizionario['generale']['agenzia']?></th>
            <th width="10%"><?=$dizionario['generale']['num_itinerario']?></th>
            <th width="10%"><?=$dizionario['generale']['cliente']?></th>
			<th width="8%"><?=$dizionario['biglietto']['num_biglietto']?></th>
			<th width="15%"><?=$dizionario['tipo_big']['servizio']?></th>
            <th width="5%"><?=$dizionario['biglietto']['data_fermata']?></th>
            <th width="5%"><?=$dizionario['biglietto']['ora_fermata']?></th>
            <th width="10%"><?=$dizionario['generale']['da']?></th>
            <th width="10%"><?=$dizionario['generale']['a']?></th>
            <th width="1%"><?=$dizionario['generale']['tipo']?></th>
            <th width="5%"><?=$dizionario['generale']['totale']?></th>              
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
            <th><input type="text" /></th> 		
		</tr>
	</thead>
	<tbody>
         
		<tr>
			<td colspan="15" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
		</tr>
	</tbody>
	
</table>
<?   
$db->close();

}





if(is_object($user)) {
    
/*      ID - FUNZIONE
1	Lista
2	Aggiunta
3	Cancellazione
4	Modifica
5	Esportazione
6	Importazione
7	Stampa
 */ 
 
    
    
    
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    if (is_object($tratta_wizard))
        $tratta_wizard->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
	    if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }
		
		
			switch($do) {
                            
				case 'exportExcel':       
										$FunzioneId=1;
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            exportExcel();
                                        else
                                           $errore->stampa_errore(2);        

				


				default:
                                    $FunzioneId=1;
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            show_list();
                                        else
                                           $errore->stampa_errore(2);
                               
                         		// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
			}
		
	} // end verifica permessi
	else {
           $errore->stampa_errore(1);
            
        }

} 
// se l'utente non ÃƒÆ’Ã‚Â¨ loggato
else {
header("Location: /logout.php");
}
?>