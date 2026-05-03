<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
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
include_once($classespath_."class.TipologiaBus.php");
include_once($classespath_."class.ServiceGoogleCalendar.php");
include_once($classespath_."class.Flotta.php");

global $ModuloId;
$ModuloId=7;// modulo base mediazione
global $user;
global $corsapartenza_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$corsapartenza_wizard=null;

if(isset($_SESSION['CORSAPARTENZA_WIZARD'])) {
$corsapartenza_wizard=unserialize($_SESSION['CORSAPARTENZA_WIZARD']);
}

function show_list()
{
    global $user, $HtmlCommon, $dizionario;
    $HtmlCommon->html_titolo_pagina($dizionario['partenza']['titolo_gestione_partenza'], null, null, null);
    $HtmlCommon->html_titolo_box($dizionario['partenza']['titolo_gestione_partenza']);
    $db = new Database();
    $db->connect();
    include_once("corsapartenza_validator.php");
    include_once("corsapartenza_datatable.php");
    ?>
	<style>
		.select-corsa, #select-all-corse { float: none; }

	</style>
    <div class="brain_servicebar">
        <a class="brain_aggiungi est" href="#" onclick="javascript:ExternalLoad('rt_corsapartenza','corsapartenza.php?do=calendario');" title="<?= $dizionario['partenza']['calendario'] ?>">
            <i class="fa fa-calendar" aria-hidden="true"></i> <?= $dizionario['partenza']['calendario']; ?>
        </a>
    </div>
	<div class="brain_servicebar">  
		<a class="brain_aggiungi est" href="#" onclick="javascript:ExternalLoad('rt_corsapartenza','corsapartenza.php?do=calendario_flotta');" title="<?= $dizionario['partenza']['calendario_flotta'] ?>">
            <i class="fa fa-calendar" aria-hidden="true"></i> <?= $dizionario['partenza']['calendario_flotta']; ?>
        </a>
    </div>
	<div style="margin-bottom:10px; display: flex; align-items: center; gap: 10px;" >
		<select id="mass-action-corse" class="brain_aggiungi est" style="height:32px;
			margin-right: 8px;
			border: none;
			padding: 4px 9px;
			border-radius: 16px;
			background-color: #fff;
			margin-left: 1rem;
			}">
			<option value=""><?=$dizionario['partenza']['selezione_azione']?></option>
			<option value="blocca"><?=$dizionario['partenza']['blocca_corse']?></option>
			<option value="sblocca"><?=$dizionario['partenza']['sblocca_corse']?></option>
			<option value="blocca_web"><?=$dizionario['partenza']['blocca_corse_web']?></option>
			<option value="sblocca_web"><?=$dizionario['partenza']['sblocca_corse_web']?></option>
		</select>
		<button id="btn-mass-action-corse" class="brain_aggiungi est" type="button" style="width: auto;
			margin: 0;
				margin-bottom: 0px;
				margin-left: 0px;
			margin-bottom: 0px;
			margin-left: 0px;
			position: relative;
			background-color: #00A4AA;
			border-radius: 42px;
			padding: 6px 12px;
			display: flex;
			flex-direction: row;
			justify-content: center;
			align-items: center;
			text-align: center;
			border: none;
			color: #fff;
			text-decoration: none;
			cursor:pointer;"><i class="fa fa-refresh" style="margin-right: 5px;"></i>  <?=$dizionario['proviggione']['aggiorna']?></button>
	</div>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
			<tr class="brain_tabellaTr">
				<th width="3%"><input type="checkbox" id="select-all-corse" /></th>
				<th width="5%"><?= $dizionario['partenza']['stato_web'] ?></th>
				<th width="5%"><?= $dizionario['generale']['stato'] ?></th>
				<th width="10%"><?= $dizionario['generale']['corsa'] ?></th>
				<th width="15%"><?= $dizionario['generale']['linea'] ?></th>
				<th width="6%"><?= $dizionario['generale']['data_partenza'] ?></th>
				<th width="6%"><?= $dizionario['generale']['ora_partenza'] ?></th>
				<th width="3%"><?= $dizionario['partenza']['pt'] ?></th>
				<th width="3%"><?= $dizionario['partenza']['pp'] ?></th>
				<th width="3%"><?= $dizionario['partenza']['prp'] ?></th>
				<th width="3%"><?= $dizionario['partenza']['sp'] ?></th>
				<th width="3%"><?= $dizionario['generale']['pax'] ?></th>
				<th width="3%"><?= $dizionario['generale']['trasferisci'] ?></th>
				<th width="3%"><?= $dizionario['partenza']['cambia'] ?></th>
				<th width="3%"><?= $dizionario['partenza']['blocca'] ?></th>
				<th width="3%"><?= $dizionario['partenza']['blocca_web'] ?></th>
				<th width="3%"><?= $dizionario['partenza']['pre'] ?></th>
			</tr>
			<tr class="brain_tabellaFilter">
				<th><input type="checkbox" disabled style="opacity:0.5;" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="text" /></th>
				<th><input type="text" /></th>
				<th><input type="text" /></th>
				<th><input type="text" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="22" class="dataTables_empty">
					<i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?= $dizionario['generale']['caricamento_in_corso'] ?>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="22"></td>
			</tr>
		</tfoot>
	</table>
    <?php
}


function AddPax() {
    global $HtmlCommon,$user,$db, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  
	
	
	$CorsaId=$_REQUEST['CorsaId'];
	$DataPartenza=$_REQUEST['DataPartenza'];
	    
	$corsa=new Corsa($CorsaId);
	$corsa->conn=$db;
	$corsa->inizializzaDatiGenerali();
	$arr_corsa=$corsa->DatiGenerali;
	$CorsaNome=$arr_corsa['CorsaNome'];

	$sql = "select TrattaId, TrattaNome from RT_Tratta where LineaId = ".$arr_corsa['LineaId']." AND Stato = 1 AND Cancella = 0 order by TrattaNome";
	$tratte = $db->fetch_array($sql);

	$HtmlCommon->html_titolo_pagina($dizionario['partenza']['titolo_aggiungi_posti'],0,"","");
	$HtmlCommon->html_titolo_box ($dizionario['partenza']['titolo_aggiungi_posti2']." ".$CorsaNome." ".$dizionario['biglietto']['del']." ".$DataPartenza);
	include_once("corsapartenza_validator.php");
	?>
	<style>
		.widthOK{width: auto !important;}
	</style>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
	    	<form id="application_form" name="application_form" method="post" action="#">
	        	<div class="brain_data-content">    
	            <?php
	            $page->create_textbox_hidden("action","AggiungiPax");
	            $page->create_textbox_hidden("CorsaPaxTratta[CorsaId]",$CorsaId);
	            $page->create_textbox_hidden("CorsaPaxTratta[DataPartenza]",$DataPartenza);
	
	            $page->create_select($dizionario['tratta']['tratta'],"CorsaPaxTratta[TrattaId]","TrattaId","brain_campoForm campiformSmall widthOK",$tratte,$tratte['TrattaId'],"TrattaId","TrattaNome",
	            		array("class"=>"'required'"),1);

	      		$page->create_textbox($dizionario['partenza']['num_pax'],"Pax","CorsaPaxTratta[NumeroPax]","",1,"brain_campoForm",array("class"=>"'required number'"));
	               
	            ?>
	            </div>
	                        
	            <div class="divSubmit">
	            <?php
	            	$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
	            ?>
				</div>     
	      </form>
	   </div>   
	</div>
	<?
	exit();
}

function GestionePax() {
    
    global $HtmlCommon,$user,$db, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  

	$CorsaId=$_REQUEST['CorsaId'];
	$DataPartenza=$_REQUEST['DataPartenza'];
	$DataPartenzaFormattata = date('d/m/Y', strtotime($DataPartenza));

	$page=new Form();  
	
	$corsa=new Corsa($CorsaId);
	$corsa->conn=$db;
	$corsa->inizializzaDatiGenerali();
	$arr_corsa=$corsa->DatiGenerali;
	$CorsaNome=$arr_corsa['CorsaNome'];
	$BusDefault=$arr_corsa['TipologiaBusDefaultId'];
	
	$tipologiabus=new TipologiaBus($BusDefault);
	$tipologiabus->conn=$db;
	$tipologiabus->inizializzaDatiGenerali();
	$arr_tipologiabus=$tipologiabus->DatiGenerali;
	$posti_default=$arr_tipologiabus['TotalePosti'];
	
	$HtmlCommon->html_titolo_pagina($dizionario['partenza']['titolo_gestione_pax'],0,"","");
	$HtmlCommon->html_titolo_box ($dizionario['partenza']['titolo_gestione_pax2']." ".$CorsaNome." ".$dizionario['biglietto']['del']." ".$DataPartenzaFormattata);
	
	?>
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
	<?
    	$page->create_textbox_hidden("step_corrente",$step_corrente);
        $page->create_textbox_hidden("step_successivo",0);
        $page->create_textbox_hidden("action","create");
    ?>
		<div class="brain_formModifica formGestoreEdita">                   
        <div class="GestoreSedeAdd">
        	<a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_corsapartenza','corsapartenza.php?do=addpax&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>');" title="aggiungi / rimuovi pax"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['partenza']['aggiungi_pax']?></a>
        </div>
                            
        <br />
        <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
        	<tbody><tr class="rowIntestazione">
            	<td><?=$dizionario['partenza']['data_inserimento']?></td>
                <td><?=$dizionario['partenza']['operatore']?></td>
                <td><?=$dizionario['partenza']['num_posti']?></td>
                <td><?=$dizionario['tratta']['tratta']?></td>
			</tr>
			<?
			$sql = "SELECT 
			        c.CorsaId AS CorsaId,
			        c.DataPartenza AS DataPartenza,
			        c.NumeroPax AS NumeroPax,
			        c.DataIns AS DataIns,
			        c.OdcIdRef AS OdcIdRef,
			        c.CorsaPaxTrattaId AS CorsaPaxTrattaId,
			        o.Cognome AS Cognome,
			        o.Nome AS Nome,
			        t.TrattaNome AS TrattaNome
			    from
			        RT_CorsaPaxTratta c
			        join Operatore o ON (c.OpeIns = o.OperatoreId)
					join RT_Tratta t ON (t.TrattaId = c.TrattaId)
			    where
			        c.Cancella = 0 AND c.CorsaId = $CorsaId AND c.DataPartenza = '$DataPartenza' order by DataIns asc";

		   $ArrObject = $db->fetch_array($sql);
           $i=0;
           while ($i< sizeof($ArrObject)) {
           		$CorsaPaxId=$ArrObject[$i]['CorsaPaxTrattaId'];
                $NumeroPax=$ArrObject[$i]['NumeroPax'];
                $Operatore=$ArrObject[$i]['Cognome']." ".$ArrObject[$i]['Nome'];
                $DataIns=$ArrObject[$i]['DataIns'];
                $TrattaNome=$ArrObject[$i]['TrattaNome'];
            ?>
             <!-- QUI L'ELENCO DELLE FERMATE -->
             <tr class="rowBianca">
             	<td><span><?=$DataIns?></span></td>
                <td><span><?=$Operatore?></span></td>
                <td><span><?=$NumeroPax?></span></td>
                <td><span><?=$TrattaNome?></span></td>
             </tr>
             <?
             	$i++;
             }
             ?>
             </tbody>
		</table>
		<!-- FINE -->
		<br />
		<div class="GestoreSedeAdd">
			<a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_corsapartenza','corsapartenza.php?do=addpax&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>');" title="aggiungi / rimuovi pax"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['partenza']['aggiungi_pax']?></a>
 		</div>
	</div>                      
</div>
<?
}

function CambiaCorsa() {
    
    global $HtmlCommon,$user,$db, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  

	//recupero info da request
	$CorsaId = $_REQUEST['CorsaId'];
	$DataPartenza = $_REQUEST['DataPartenza'];
	$DataPartenzaFormattata = date('d/m/Y', strtotime($DataPartenza));
	
	//recupero le barche per la select
	$flotta = new Flotta();
    $flotta->conn = $db;
    $arr_barca = $flotta->getAllForSelectModel();

	//inizializzo costruttore pagine
	$page=new Form();  
	
	//inizializzo i dati della corsa
	$corsa=new Corsa($CorsaId);
	$corsa->conn=$db;
	$corsa->inizializzaDatiGenerali();
	$arr_corsa=$corsa->DatiGenerali;
	$CorsaNome = $arr_corsa['CorsaNome'];
	$BusDefault = $arr_corsa['FlottaDefaultId'];
	$OrarioPartenza = $arr_corsa['OrarioPartenza'];
	$OrarioArrivo = $arr_corsa['OrarioArrivo'];
	
	//impsoto titolo finestra
	$HtmlCommon->html_titolo_pagina($dizionario['partenza']['titolo_cambio_data'],0,"","");
	$HtmlCommon->html_titolo_box ($dizionario['partenza']['titolo_cambio_data']." ".$CorsaNome." ".$dizionario['biglietto']['del']." ".$DataPartenzaFormattata);
	
	include_once("corsapartenza_validator.php");
	?>
	<style>
		#brain_boxCentrato{
			width:700px;
		}   
	</style>
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	<script type="text/javascript">
		$(document).ready(function() {
			// Datepicker
			$(function() {
				$( "#DataPartenza" ).datepicker({
					monthNames:
						[<?=$dizionario['generale']['nome_mesi']?>],
						monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
						monthStatus: '<?=$dizionario['generale']['mese_status']?>',
						yearStatus: '<?=$dizionario['generale']['anno_status']?>',
						weekHeader: 'Sm', weekStatus: '',
						weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
					dayNames:
						[<?=$dizionario['generale']['nome_giorni']?>],
						dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
						dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
						dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
						dateStatus: '<?=$dizionario['generale']['data_status']?>',
						dateFormat: 'dd/mm/yy', firstDay: 1,
						initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
						dateFormat: 'dd/mm/yy'
				});
				
				$(".Orario").mask("99:99");   
			});
		});
	</script>	
	<form id="application_form_cambia" name="application_form" method="post" action="#">
		<?php
			$page->create_textbox_hidden("action","CambiaCorsa");
			$page->create_textbox_hidden("CorsaIdOld", $CorsaId);
			$page->create_textbox_hidden("DataPartenzaOld",$DataPartenza);
			$page->create_textbox_hidden("BarcaOld",$BusDefault);
			$page->create_textbox_hidden("OrarioPartenzaOld",$OrarioPartenza);
			$page->create_textbox_hidden("OrarioArrivoOld",$OrarioArrivo);
		?>
		<div class="brain_data-content">   
			<?= $page->create_textbox($dizionario['biglietto']['libera_data_andata'],"DataPartenza","DataPartenza",(isset($DataPartenza))? $DataPartenzaFormattata : date('d/m/Y'), 1, "brain_campoForm campiformSmall ", array("class"=>"'required italianDate'"), "", "10") ?>
			<?= $page->create_textbox($dizionario['biglietto']['libera_orario_partenza'],"OrarioPartenza","OrarioPartenza",(isset($OrarioPartenza))? $OrarioPartenza : "", 1, "brain_campoForm campiformSmall ", array("class"=>"'required Orario'"), "", "10") ?>
			<?= $page->create_textbox($dizionario['biglietto']['libera_orario_destinazione'],"OrarioArrivo","OrarioArrivo",(isset($OrarioArrivo))? $OrarioArrivo : "", 1, "brain_campoForm campiformSmall ", array("class"=>"'required Orario'"), "", "10") ?>
			<?= $page->create_select($dizionario['generale']['flotta'],"Barca","Barca","brain_campoForm campiformSmall ",$arr_barca,(isset($BusDefault))? $BusDefault : '',"FlottaId","Modello", array(
				"class"=>"'required'",
				),1); ?>
		
		</div>  
		<div class="divSubmit">
			<?php
				$page->create_button("Salva","Salva",$dizionario['generale']['cambia'],"brain_salva","submit");
			?>
		</div>
	</form>
</div>
<?
}

function TrasferisciCorsa() {
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();
	$page = new Form();

	// Recupero info da request
	$CorsaId = $_REQUEST['CorsaId'];
	$DataPartenza = isset($_REQUEST['DataPartenza']) ? $_REQUEST['DataPartenza'] : date('Y-m-d');
	$DataPartenzaFormattata = date('d/m/Y', strtotime($DataPartenza));

	// Recupero dati corsa selezionata
	$corsa = new Corsa($CorsaId);
	$corsa->conn = $db;
	$corsa->inizializzaDatiGenerali();
	$arr_corsa = $corsa->DatiGenerali;
	$CorsaNome = $arr_corsa['CorsaNome'];
	$LineaId = $arr_corsa['LineaId'];

	// Recupero tutte le corse della stessa LineaId
	$corse_linea = $corsa->getAllByLineaId($LineaId, $DataPartenza);

	// Modalità disponibili
	$modalita = [
		["id" => "trasferisci", "nome" => $dizionario['partenza']['trasferisci_corsa']],
		["id" => "scambia", "nome" => $dizionario['partenza']['scambia_corsa']]
	];

	// Titolo finestra
	$HtmlCommon->html_titolo_pagina($dizionario['partenza']['titolo_trasferisci_corsa'], 0, "", "");
	$HtmlCommon->html_titolo_box(($dizionario['partenza']['titolo_trasferisci_corsa']) . " " . $CorsaNome . " " . ($dizionario['biglietto']['del']) . " " . $DataPartenzaFormattata);

	include_once("corsapartenza_validator.php");
	?>
	<style>
		#brain_boxCentrato {
			width: 700px;
		}
	</style>
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
		<script type="text/javascript">
			$(document).ready(function () {
				// Datepicker
				$(function () {
					$("#DataPartenza").datepicker({
						monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
						monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
						monthStatus: '<?=$dizionario['generale']['mese_status']?>',
						yearStatus: '<?=$dizionario['generale']['anno_status']?>',
						weekHeader: 'Sm', weekStatus: '',
						weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
						dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
						dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
						dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
						dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
						dateStatus: '<?=$dizionario['generale']['data_status']?>',
						dateFormat: 'dd/mm/yy', firstDay: 1,
						initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
						dateFormat: 'dd/mm/yy'
					});
				});
			});
		</script>
		<form id="application_form_trasferisci" name="application_form_trasferisci" method="post" action="#">
			<?php
			$page->create_textbox_hidden("action", "TrasferisciCorsaSubmit");
			$page->create_textbox_hidden("CorsaIdOld", $CorsaId);
			$page->create_textbox_hidden("DataPartenzaOld", $DataPartenza);
			?>
			<div class="brain_data-content">
				<?php
				// Selezione data
				$page->create_textbox($dizionario['biglietto']['libera_data_andata'], "DataPartenza", "DataPartenza", $DataPartenzaFormattata, 1, "brain_campoForm campiformSmall", array("class" => "'required italianDate'"), "", "10");

				// Select per corsa
				$page->create_select($dizionario['generale']['corsa'], "NuovaCorsaId", "NuovaCorsaId", "brain_campoForm campiformSmall", $corse_linea, '', "CorsaId", "CorsaNome", array("class" => "'required'"), 1);

				// Select per modalità
				$page->create_select_no_default($dizionario['partenza']['modalita_trasferimento'], "ModalitaTrasferimento", "ModalitaTrasferimento", "brain_campoForm campiformSmall", $modalita, '', "id", "nome", array("class" => "'required'"), 1);
				?>
			</div>
			<div class="divSubmit">
				<?php
				$page->create_button("Salva", "Salva", $dizionario['partenza']['conferma_trasferimento'], "brain_salva", "submit");
				?>
			</div>
		</form>
	</div>
	<?php
}

function spara_pulsanti_wizard_box()
{
	global $dizionario;
$page=new Form();
    
?>
<div class="divSubmit">
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
   
   
    
         <a href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi" class="brain_annulla"><?=$dizionario['generale']['chiudi']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
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
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['avanti'],"brain_salva","submit"); ?>
    <?  
    if ($steptogo>0)
    $page->create_button("indietro","indietro",$dizionario['generale']['indietro'],"brain_back","button"); ?>
    
         <a href="javascript:void(0);" onclick="loadMainContent('mediatore','mediatore.php',this);" title="Home" class="brain_annulla"><?=$dizionario['convenzione']['annulla']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>
    <?
    
    
}
}

function spara_pulsanti_edit($steptogo)
{
   
global $abilita_modifica, $dizionario;
    
    $page=new Form();
    
    ?>
<div class="divSubmit">
                         
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
       
         <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>  
    <?php
}

function calendarioFlotta() {
	global $HtmlCommon,$user, $dizionario;
	
	$db = new Database();
	$db->connect();
	
	$service = new ServiceGoogleCalendar($db);
	$flottaCalendari = $service->getCalendarFlotta();
	
	$page = new Form();

	include_once("corsapartenza_validator.php");
	$HtmlCommon->html_titolo_pagina($dizionario['partenza']['calendario_flotta'],0,"rt_corsapartenza","corsapartenza.php");

	$HtmlCommon->html_titolo_box($dizionario['partenza']['calendario_flotta']);
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero" id="calendario">
			 
				
				<table id="brain_datatables" class="display">
					<thead>
						<tr class="brain_tabellaTr">
							<th><?php echo $dizionario['flotta']['autobus'];?></th>
							<th><?php echo $dizionario['flotta']['targa'];?></th>
							<th style="width:80px"><?php echo $dizionario['flotta']['visualizza_calendario'];?></th>
							<th style="width:80px"><?php echo $dizionario['flotta']['aggiungi_calendario'];?></th>
						</tr>
					</thead>
					<tbody>
				   <?php
				   $num = 0;
				   foreach($flottaCalendari as $barca){ ?>
						<tr>
							<td style='text-align:center;'><?=$barca['Modello']?></td>
							<td style='text-align:center;'><?=$barca['Targa']?></td>
							<td style='text-align:center;'><a href="<?=$barca['UrlCalendar']?>" target='_blank'><i class="fa fa-calendar" aria-hidden="true"></i></a></td>
							<td style='text-align:center;'><a href="<?=$barca['UrlAdd']?>" target='_blank'><i class="fa fa-plus" aria-hidden="true"></i></a></td>
						</tr>
				   <?php } ?>
				   </tbody>
				 </table> 

		</div>   
	</div>                                  
<?
}

function calendario() {
	global $HtmlCommon,$user, $dizionario;
	if(isset($_POST['anno'])){
		$anno = $_POST['anno'];
	} else if(isset($_GET['anno']) && $_GET['anno']!=""){
		$anno = $_GET['anno'];
	} else {
		$anno = date('Y');
	}
	
	if(isset($_POST['mese'])){
		$mese = $_POST['mese'];
	} else if(isset($_GET['mese']) && $_GET['mese']!=""){
		$mese = $_GET['mese'];
	} else {
		$mese = date('m');
	}

	$db = new Database();
	$db->connect();
	$page = new Form();

	include_once("corsapartenza_validator.php");
	$HtmlCommon->html_titolo_pagina($dizionario['partenza']['calendario'],0,"rt_corsapartenza","corsapartenza.php");

	$HtmlCommon->html_titolo_box($dizionario['partenza']['calendario']);
	$sql = "select * from RT_AppCalendario
			where YEAR(AppCalendarioData) = $anno and month(AppCalendarioData) = $mese";
	$calendario = $db->fetch_array($sql);

	$mesi = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile',
                'Maggio', 'Giugno', 'Luglio', 'Agosto',
                'Settembre', 'Ottobre', 'Novembre','Dicembre');
	if($mese == 1){
		$mese_pre = 12;
		$anno_pre = $anno - 1;
		$mese_suc = $mese + 1;
		$anno_suc = $anno;
	} elseif($mese == 12) {
		$mese_pre = $mese - 1;
		$anno_pre = $anno;
		$mese_suc = 1;
		$anno_suc = $anno + 1;
	} else {
		$mese_pre = $mese - 1;
		$anno_pre = $anno;
		$mese_suc = $mese + 1;
		$anno_suc = $anno;
	}
	
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero" id="calendario">
		
                   <form id="application_form" name="application_form" method="post" action="#">
                   
                         <div class="brain_formModifica">
                         	
                         	<table id="brain_datatables" class="display">
                         		<thead>
                         			<tr class="brain_tabellaTr">
                         				<th colspan="7">
	                         				<h2 style="text-align: center; color:white;">
	                         				<a style="color:white;" href="#" onclick="javascript:calendario(<?php echo $mese_pre.",".$anno_pre?>);"> << </a>
	                         				<?php echo $mesi[$mese-1]." ".$anno;?>
	                         				<a style="color:white;" href="#" onclick="javascript:calendario(<?php echo $mese_suc.",".$anno_suc?>);"> >> </a>
	                         				</h2>
                         				</th>
                         			</tr>
                         			<tr class="brain_tabellaTr">
                         				<th><?php echo $dizionario['generale']['lunedi'];?></th>
                         				<th><?php echo $dizionario['generale']['martedi'];?></th>
                         				<th><?php echo $dizionario['generale']['mercoledi'];?></th>
                         				<th><?php echo $dizionario['generale']['giovedi'];?></th>
                         				<th><?php echo $dizionario['generale']['venerdi'];?></th>
                         				<th><?php echo $dizionario['generale']['sabato'];?></th>
                         				<th><?php echo $dizionario['generale']['domenica'];?></th>
                         			</tr>
                         		</thead>
                         		<tbody>
                               <?php
                               $num = 0;
                               foreach($calendario as $k => $day){
                                   
                               		if($k == 0 && ($day['GiornoSettimana'] > 1 || $day['GiornoSettimana'] == 0)){
                               			echo "<tr>";
                               			if($day['GiornoSettimana'] == 0){
                               				$fine = 6;
                               			} else {
                               				$fine = $day['GiornoSettimana']-1;
                               			}
                               			for($ii = 0; $ii < $fine; $ii++ ) {
                               				echo "<td style='width:15%;'></td>";
                               				$num++;
                               			}
                               		}
                               			if($num == 0){
                               				echo "<tr>";
                               			}
                               			$r = getCalendario($day['AppCalendarioData']);
                               			
                               			echo "<td style='width:15%;vertical-align: top;'><b>".date('d', strtotime($day['AppCalendarioData']))."</b><br>";
                               			echo "<div style='text-align:center;'>";
                               			foreach($r as $c){
                               				echo "<div style='text-align:center; border: 1px solid; #000'>";
                               				$blocco="bloccare";
                               				$bloccospan = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"".$dizionario['generale']['attiva']."\"></i>";
                               				if ($c['CorsaBloccata']==1){
                               					$blocco="Sbloccare";
                               					$bloccospan = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"".$dizionario['generale']['disattiva']."\"></i>";
                               				}
                               				$bloccoweb="bloccare";
                               				$bloccowebspan = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"".$dizionario['generale']['attiva']."\"></i>";
                               				if ($c['CorsaWebBloccata']==1){
                               					$bloccoweb="Sbloccare";
                               					$bloccowebspan = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"".$dizionario['generale']['disattiva']."\"></i>";
                               				}
                               				echo "<b>".$c['CorsaNome']."</b><br>";
                               				echo "<table style='width:100%; border: 0px;'><tr><td colspan='2' style='text-align:center;'>operatore</td><td colspan='2' style='text-align:center;'>web</td></tr><tr>";
                               				echo "<td style='text-align:center;'>".$bloccospan."</td>";
                               				echo "<td style='text-align:center;'><a href=\"#\" onclick=\"javascript:BloccaSbloccaCorsa(".$c['CorsaBloccata'].",".$c['CorsaId'].",'".$c['CorsaNome']."','".$c['AppCalendarioData']."','".$c['OrarioPartenza']."','$blocco', true, '$mese', '$anno');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a></td>";
                               				echo "<td style='text-align:center;'>".$bloccowebspan."</td>";
                               				echo "<td style='text-align:center;'><a href=\"#\" onclick=\"javascript:BloccaSbloccaCorsaWeb(".$c['CorsaWebBloccata'].",".$c['CorsaId'].",'".$c['CorsaNome']."','".$c['AppCalendarioData']."','".$c['OrarioPartenza']."','$bloccoweb', true, '$mese', '$anno');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a></td>";
                               				echo "</tr></table></div>";
                               			}
                               			echo "</div></td>";
                               			if($num == 6){
                               				echo "</tr>";
                               				$num = 0;
                               			} else {
                               				$num++;
                               			}
                               		
                               } ?>
                               </tbody>
                             </table> 
                         </div>
                             
                   </form>
		</div>   
	</div>                                  
<?
}

function getCalendario($data){
	global $db,$user;
	$OdcIdRef=$user->OdcId;
	
	$sql1= "select 
		c.`CorsaId` AS `CorsaId`, 
		appcal.`AppCalendarioData` AS `AppCalendarioData`, 
		date_format(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`, 
		c.`CorsaNome` AS `CorsaNome`, 
		c.`LineaId` AS `LineaId`, 
		`RT_Linea`.`LineaNome` AS `LineaNome`, 
		c.`OrarioPartenza` AS `OrarioPartenza`, 
		if(isnull(`RT_CorsaBloccoWeb`.`CorsaBloccoId`), 0, 1) AS `CorsaWebBloccata`, 
		if(isnull(`RT_CorsaBlocco`.`CorsaBloccoId`), 0, 1) AS `CorsaBloccata`,
		if(isnull(`RT_MaxDisponibilitaPostiCron`.`Posti`), 0, `RT_MaxDisponibilitaPostiCron`.`Posti`) AS `MaxPostiOccupati` 
		from `RT_Corsa` c join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`) 
		join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`) 
		join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`) 
		join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`) 
		join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`) 
		left join `RT_CorsaBloccoWeb` ON (c.`CorsaId` = `RT_CorsaBloccoWeb`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaBloccoWeb`.`DataPartenza`) 
		left join `RT_CorsaBlocco` ON (c.`CorsaId` = `RT_CorsaBlocco`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaBlocco`.`DataPartenza`) 
		left join `RT_CorsaInizioPreparazione` ON (c.`CorsaId` = `RT_CorsaInizioPreparazione`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaInizioPreparazione`.`DataCorsa`) 
		left join `RT_CorsaConsolidamento` ON (c.`CorsaId` = `RT_CorsaConsolidamento`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaConsolidamento`.`DataCorsa`) 
		left join `RT_MaxDisponibilitaPostiCron` ON (appcal.`AppCalendarioData` = `RT_MaxDisponibilitaPostiCron`.`DataPartenza` and c.`CorsaId` = `RT_MaxDisponibilitaPostiCron`.`CorsaId`) 
		where ((c.`Cancella` = 0) and (appcal.`AppCalendarioData` >= c.`AttivaDal`) and (appcal.`AppCalendarioData` <= c.`AttivaAl`) and ((appcal.`Feriale` = c.`IncludiFeriale`) or (appcal.`Prefestivo` = c.`IncludiPrefestivo`) or (appcal.`Festivo` = c.`IncludiFestivo`))) and c.`OdcIdRef`=1 
		and appcal.`AppCalendarioData`='$data' 
		group by c.`CorsaId` , appcal.`AppCalendarioData` order by appcal.`AppCalendarioData` , `RT_Linea`.`PercorsoId` , `RT_Linea`.`LineaNome` , c.`CorsaNome`
	";
	$calendario = $db->fetch_array($sql1);
	
	return $calendario;
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
    if (is_object($corsapartenza_wizard))
        $corsapartenza_wizard->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
	    if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }

		switch($do) {
			case "addpax":
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
				if (sizeof($permesso))
					AddPax();
				else
					$errore->stampa_errore(2);
				break;
                
			case "GestionePax":
				$FunzioneId=2;
				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				$permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
				if (sizeof($permesso))
					GestionePax();
				else
					$errore->stampa_errore(2);
                break;
				
			case "CambiaCorsa":
				$FunzioneId=2;
				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				$permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
				if (sizeof($permesso))
					CambiaCorsa();
				else
					$errore->stampa_errore(2);
                break;

			case "TrasferisciCorsa":
				$FunzioneId=2;
				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				$permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
				if (sizeof($permesso))
					TrasferisciCorsa();
				else
					$errore->stampa_errore(2);
                break;
                
             case "calendario":
				$FunzioneId=4;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					calendario();
				else
					Errors::$ErrorePermessiModuloFunzione;
                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
                break;
				
			case "calendario_flotta":
				$FunzioneId=4;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					calendarioFlotta();
				else
					Errors::$ErrorePermessiModuloFunzione;
                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
                break;
                
			default:
				$FunzioneId=4;
				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					show_list();    
				else
					$errore->stampa_errore(2);  
                break;
			}
		
	} // end verifica permessi
	else {
           $errore->stampa_errore(1);
            
        }

} 

else {
header("Location: /logout.php");
}
?>