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
include_once($classespath_."class.DT.php");
include_once($classespath_."class.Autisti.php");

include_once($classespath_."class.Coupon.php");

global $ModuloId;
global $user;

$ModuloId=45;// modulo base mediazione


if(is_object($user)) {

	/*  ID - FUNZIONE
		1	Lista
		2	Aggiunta
		3	Cancellazione
		4	Modifica
		5	Esportazione
		6	Importazione
		7	Stampa
	*/
	
	$permessi = $user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0) {
		if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }

		switch($do) {

			case "add":
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					add();
				else
					$errore->stampa_errore(2);

				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			case "edit":
				$FunzioneId=4;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					add($_GET['CouponId']);
				else
					$errore->stampa_errore(2);
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			case "spese":
				$FunzioneId=1;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					show_spese($_GET['FoglioViaggioId']);
				else
					$errore->stampa_errore(2);
				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
				
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
// se l'utente non � loggato
else {
	header("Location: /logout.php");
}

function show_list() {
	global $user, $HtmlCommon, $ModuloId, $db, $dizionario;
	
	$HtmlCommon->html_titolo_pagina($dizionario['foglioviaggio']['titolo_gestione'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['foglioviaggio']['titolo_gestione']);

	$db= new Database();
	$db->connect();
	
	include_once("foglioviaggio_datatable.php");
	include_once("foglioviaggio_validator.php");

	?>   
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    	<thead>
            <tr class="brain_tabellaTr">
            	<th width="10%"><?=$dizionario['generale']['corsa']?></th>
				<th width="10%"><?=$dizionario['generale']['linea']?></th>
				<th width="10%"><?=$dizionario['generale']['data']?></th>
				<th width="10%"><?=$dizionario['generale']['ora']?></th>
                <th width="10%"><?=$dizionario['pre']['autista1']?></th>
                <th width="10%"><?=$dizionario['pre']['autista2']?></th>
                <th width="10%"><?=$dizionario['flotta']['tipo']?></th>
                <th width="10%"><?=$dizionario['flotta']['targa']?></th>
                <th width="10%"><?=$dizionario['generale']['partenza']?></th>
                <th width="10%"><?=$dizionario['generale']['arrivo']?></th>
                <th width="10%"><?=$dizionario['foglioviaggio']['km_partenza']?></th>
                <th width="10%"><?=$dizionario['foglioviaggio']['km_arrivo']?></th>
                <th width="5%"><?=$dizionario['foglioviaggio']['spese']?></th>
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
				<th><input type="hidden" /></th>
			</tr>
		</thead>
		<tbody> 
			<tr>
				<td colspan="13" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<td colspan="13"></td>
			</tr> 
		</tfoot> 
	</table>
	<?   
	$db->close();
}

function show_spese($foglioViaggioId) {
	global $HtmlCommon, $user, $db, $dizionario;
	
	$db = new Database();
	$db->connect();
	
	
	$sql="SELECT s.*, t.Nome as TipoSpesa, p.Nome as Pagamento, a.Cognome, a.Nome  FROM RT_FoglioViaggioSpesa s 
	left join RT_FoglioViaggioTipoSpesa t on t.FoglioViaggioTipoSpesaId = s.FoglioViaggioTipoSpesaId
	left join RT_FoglioViaggioPagamento p on p.FoglioViaggioPagamentoId = s.FoglioViaggioPagamentoId
	left join RT_Autisti a on a.AutistiId = s.AutistaId
	WHERE 
	s.FoglioViaggioId = $foglioViaggioId
	order by EventoKm asc";
// 	echo $sql;
	$row = $db->fetch_array($sql);
	$HtmlCommon->html_titolo_pagina($dizionario['foglioviaggio']['titolo_spesa'], 1, "rt_foglioviaggio", "foglioviaggio.php");
	$HtmlCommon->html_titolo_box ($dizionario['foglioviaggio']['titolo_spesa']);
	?>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	    	<thead>
	            <tr class="brain_tabellaTr">
	            	<th width="10%"><?=$dizionario['foglioviaggio']['eventkm']?></th>
					<th width="10%"><?=$dizionario['foglioviaggio']['tipo']?></th>
					<th width="10%"><?=$dizionario['foglioviaggio']['importo']?></th>
					<th width="10%"><?=$dizionario['foglioviaggio']['pagamento']?></th>
	                <th width="10%"><?=$dizionario['foglioviaggio']['numero_litri']?></th>
	                <th width="30%"><?=$dizionario['foglioviaggio']['autista']?></th>
				</tr>
			</thead>
			<tbody> 
			<?php foreach ($row as $r){ ?>
				<tr>
					<td>
						<?php echo $r['EventoKm'];?>
					</td>
					<td>
						<?php echo $r['TipoSpesa'];?>
					</td>
					<td>
						<?php echo $r['Importo'];?>
					</td>
					<td>
						<?php echo $r['Pagamento'];?>
					</td>
					<td>
						<?php echo $r['NumeroLitri'];?>
					</td>
					<td>
						<?php echo $r['Cognome']." ".$r['Nome'];?>
					</td>
				</tr>
			<?php }?>
			</tbody>
			<tfoot> 
				<tr> 
					<td colspan="13"></td>
				</tr> 
			</tfoot> 
		</table>
		<?   
		$db->close();
	
}

function add($CouponId = null) {
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();
	
	$page = new Form();
	$dt = new DT();
	
	$autista = array();
	if(isset($CouponId)) {
		$couponObj = new Coupon($CouponId);
		$couponObj->conn = $db;
		$couponObj->inizializzaDatiGenerali();
		$coupon = $couponObj->DatiGenerali;
		
		$HtmlCommon->html_titolo_pagina($dizionario['coupon']['titolo_modifica'], 1, "rt_coupon", "coupon.php");
		$HtmlCommon->html_titolo_box ($dizionario['coupon']['titolo_modifica']);
	} else {
		$HtmlCommon->html_titolo_pagina($dizionario['coupon']['titolo_inserisci'], 1, "rt_coupon", "coupon.php");
		$HtmlCommon->html_titolo_box ($dizionario['coupon']['titolo_inserisci']);
	}
	
	$arr_stato[]= array("StatoId" => '0', "Stato" => $dizionario['generale']['disattivo']);
	$arr_stato[]= array("StatoId" => '1', "Stato" => $dizionario['generale']['attivo']);
	
	include_once("foglioviaggio_validator.php");
	?>
   
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    <?
					if($CouponId == null) {
						$page->create_textbox_hidden("action","AggiungiCoupon");
					} else {
						$page->create_textbox_hidden("action","ModificaCoupon");
						$page->create_textbox_hidden("CouponId", $CouponId);
					}
					
					$page->create_textbox($dizionario['coupon']['descrizione'].":", "Descrizione", "Coupon[CouponNome]", (isset($coupon['CouponNome']))? $coupon['CouponNome'] : "", 1, "brain_campoForm campiformBig", array("class"=>"'required'"));
					print("<br style=\"clear:both;\"/>");
					if($CouponId == null){
					?>
						<fieldset>
        					<legend><?=$dizionario['coupon']['coupon_da_creare']?></legend>
        					<input style="float:none !important;" type="radio" name="tipoCreazione" value="one" checked="checked"/><?=$dizionario['coupon']['singolo_coupon']?> 
        					<input style="float:none !important;" type="radio" name="tipoCreazione" value="more"/><?=$dizionario['coupon']['piu_coupon']?>  
    					</fieldset>
					<?php
						print("<br style=\"clear:both;\"/>");
					} 
					
					$page->create_textbox($dizionario['coupon']['codice'].":", "Codice", "Coupon[Codice]", (isset($coupon['Codice']))? $coupon['Codice'] : "", 1, "brain_campoForm", array("class"=>"'required'"));
					?>
					<div class="brain_campoForm" style="padding-top: 26px; vertical-align:middle; padding-left:40px"><a href="#" id="getCodiceCoupon"><?=$dizionario['coupon']['genera_codice']?></a></div>
					<?php 
					if($CouponId == null){
						$page->create_textbox($dizionario['coupon']['num_coupon'].":", "numeroCoupon", "numeroCoupon", "1", 1, "brain_campoForm", array("class"=>"'required number'"));
					}
					print("<br style=\"clear:both;\"/>");
					$page->create_textbox($dizionario['generale']['importo'].":", "Importo", "Coupon[Importo]", (isset($coupon['Importo']))? $coupon['Importo'] : "", 0, "brain_campoForm", array("class"=>"'required numberDE'"));
					print("<br style=\"clear:both;\"/>");
					$page->create_textbox($dizionario['coupon']['max_utilizzi'].":", "MaxUtilizzi", "Coupon[MaxUtilizzi]", (isset($coupon['MaxUtilizzi']))? $coupon['MaxUtilizzi'] : "", 0, "brain_campoForm", array("class"=>"'required number'"));

					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['generale']['stato'], "Coupon[Stato]", "Stato", "brain_campoForm", $arr_stato, (isset($coupon['Stato']))? $coupon['Stato'] : 1, "StatoId", "Stato", array("class"=>"'required'"), 1);
				?>
				</div>
				<div class="divSubmit"><?
					$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
				?></div>
			</form>
				</div>   
		</div>        
<?	
}
?>