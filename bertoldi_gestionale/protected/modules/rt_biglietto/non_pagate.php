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

global $ModuloId;
$ModuloId=38; // modulo base mediazione
global $user;
global $prenotazione_wizard, $funzione_edit, $abilita_modifica;

$funzione_edit = false;
$prenotazione_wizard = null;

if(isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
	$prenotazione_wizard=unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
}

function show_list()
{
   
	global $user,$HtmlCommon,$ModuloId, $dizionario;
	$HtmlCommon->html_titolo_pagina($dizionario['biglietto']['titolo_non_pagate'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['biglietto']['titolo_non_pagate']);
	$db= new Database();
	$db->connect();

	include_once("biglietto_validator.php");           
	include_once("biglietto_datatable_nonpagate.php");

	global $user,$HtmlCommon,$db,$ModuloId;    
 
	$aggiungi = $user->ControllModuloFunzionePermesso($ModuloId,2);
	if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_biglietto','biglietto.php?do=add',$dizionario['prenotazioni']['btn_aggiungi']);

?>   
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>

		<tr class="brain_tabellaTr">
			<th width="8%"><?=$dizionario['generale']['stato']?></th>
            <th width="15%"><?=$dizionario['generale']['agenzia']?></th>
            <th width="8%"><?=$dizionario['biglietto']['data_operazione']?></th>
			<th width="8%"><?=$dizionario['generale']['num_itinerario']?></th>
			<th width="15%"><?=$dizionario['generale']['cliente']?></th>
            <th width="15%"><?=$dizionario['generale']['telefono']?></th>
            <th width="15%"><?=$dizionario['generale']['corsa']?></th>
			<th width="8%"><?=$dizionario['generale']['data_partenza']?></th>
            <th width="10%"><?=$dizionario['generale']['da']?></th>
            <th width="10%"><?=$dizionario['generale']['a']?></th>
            <th width="5%"><?=$dizionario['generale']['pax']?></th>
            <th width="5%"><?=$dizionario['generale']['totale']?></th>
            <th width="5%"><?=$dizionario['biglietto']['data_scadenza']?></th>
            <th width="3%"><?=$dizionario['generale']['edita']?></th>
		</tr>

		<tr	class="brain_tabellaFilter">
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
			<th><input type="text" /></th>
			<th><input type="hidden" /></th>
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

    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {
	    if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }

    	switch($do) {

    		default:
    			$FunzioneId=2;
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