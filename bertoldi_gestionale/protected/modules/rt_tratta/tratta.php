<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();

include_once($classespath_."class.Form.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");

include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.TipologiaBus.php");
$ModuloId=28;

function add() {
	global $HtmlCommon,$user, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  

	$TrattaTipo=new TrattaTipo();
	$TrattaTipo->conn=$db;
	$arr_tratta_tipo=$TrattaTipo->getAll();

	$Mezzo=new Mezzo();
	$Mezzo->conn=$db;
	$arr_mezzo=$Mezzo->getAll();

	$TrattaDirezione=new TrattaDirezione();
	$TrattaDirezione->conn=$db;
	$arr_tratta_direzione=$TrattaDirezione->getAll();

	$tipologiabus=new TipologiaBus();
	$tipologiabus->conn=$db;
	$arr_tipologiabus=$tipologiabus->getAllForSelect();

	include_once("tratta_validator.php");       
	 $HtmlCommon->html_titolo_pagina($dizionario['tratta']['titolo_aggiungi'],0,"rt_tratta","tratta.php");

	$HtmlCommon->html_titolo_box($dizionario['tratta']['titolo_aggiungi']);  
	$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

	$arr_da_confermare[]= array("ConfermareId" => '0',"Confermare" => $dizionario['generale']['no']);
	$arr_da_confermare[]= array("ConfermareId" => '1',"Confermare" => $dizionario['generale']['si']);

	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
			<div class="brain_boxIntero">
					   <form id="application_form" name="application_form" method="post" action="#">
							 <div class="brain_formModifica">
									<div class="brain_data-content">    
					<?php
					$page->create_textbox_hidden("action","create");
					$page->create_textbox_hidden("Tratta[TrattaDirezioneId]", 1);
					$page->create_textbox_hidden("Tratta[NodoPeso]", 1);
					$page->create_textbox_hidden("Tratta[DaConfermare]", 0);
					$page->create_textbox_hidden("Tratta[TipologiaBusDefaultId]", 0);
					$page->create_textbox($dizionario['tratta']['tratta'],"TrattaNome","Tratta[TrattaNome]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
					$page->create_select($dizionario['tratta']['tipo'].":","Tratta[TrattaTipoId]","TrattaTipoId","brain_campoForm",$arr_tratta_tipo,0,"AppTrattaTipoId","AppTrattaTipo",array("class"=>"'required'"),1);
					$page->create_select($dizionario['tratta']['mezzo'].":","Tratta[MezzoId]","MezzoId","brain_campoForm",$arr_mezzo,0,"AppMezzoId","AppMezzo",array("class"=>"'required'"),1);
					$page->create_textbox($dizionario['generale']['peso'],"Peso","Tratta[TrattaPeso]","",1,"brain_campoForm",array("class"=>"'required'"));
					$page->create_textbox($dizionario['tratta']['km_tratta'],"KmTratta","Tratta[KmTratta]","",1,"brain_campoForm",array("class"=>"'required number'"));  
					//$page->create_select($dizionario['generale']['tipo_bus'],"Tratta[TipologiaBusDefaultId]","TipologiaBusDefaultId","brain_campoForm",$arr_tipologiabus,0,"TipologiaBusId","TipologiaBus",null,0);
					$page->create_select($dizionario['generale']['stato'],"Tratta[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",
					array("class"=>"'required'"),1);
					print("<br style=\"clear:both;\"/>");
					if (isset($_REQUEST['LineaId'])) {
				   ?>
					<input type="hidden" name="Tratta[LineaId]" value="<?=$_REQUEST['LineaId']?>" />                    
					<? }
				   
					
					print("<br style=\"clear:both;\"/>");
					?>
					</div>
				</div>
				<div class="divSubmit">
				<?
					$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
				?>
				</div>     
			 </form>
		</div>   
	</div>
										
	<?php
}


function updateFermata() {
	global $HtmlCommon,$user, $dizionario;

	$db = new Database();
	$db->connect();
	$page = new Form();
	
	include_once("tratta_validator.php");
	$HtmlCommon->html_titolo_pagina($dizionario['tratta']['modifica_fermata'],0,"rt_tratta","tratta.php");

	$HtmlCommon->html_titolo_box($dizionario['tratta']['modifica_fermata']);
	$sql = "select distinct c.Comune, c.ComuneId from RT_Fermata f
			left join Comune c on c.ComuneId = f.ComuneId
			where f.Cancella = 0 and Comune <> ''
			order by c.Comune ASC";
	$comuni = $db->fetch_array($sql);
	
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
		
                   <form id="application_form" name="application_form" method="post" action="#">
                   
                         <div class="brain_formModifica">
                         <?php echo $dizionario['tratta']['seleziona_comune_fermate']; ?>
                                <div class="brain_data-content">
					                <?
					                
					                $page->create_textbox_hidden("action","updateFermate");
					                $page->create_textbox_hidden("return","1");
					                $page->create_select($dizionario['generale']['comune'],"ComuneId","ComuneId","brain_campoForm",$comuni,null,"ComuneId","Comune",array("class"=>"'required'"), 1);
					                print("<br style=\"clear:both;\"/>");
					                $page->create_textbox($dizionario['tratta']['fermata_nome'],"FermataNome","FermataNome",null,1,"brain_campoForm campiformBig",array("class"=>"'required'"));
					                print("<br style=\"clear:both;\"/>");
					                ?>
					                <div id="fermateTratta">
					                	<table id="brain_datatables" class="display">
											<thead>
												<tr class="brain_tabellaTr">
													<th></th>
													<th><?php echo $dizionario['generale']['fermata'];?></th>
													<th><?php echo $dizionario['generale']['tratta'];?></th>
													<th><?php echo $dizionario['generale']['linea'];?></th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td colspan = '4'>
														<i><?php echo $dizionario['tratta']['no_fermate'] ?></i>
													</td>
												</tr>
											</tbody>
										</table>
					                </div>
                                 </div>
                         </div>
                         <div class="divSubmit">
                         	<?php
                         	$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
                            ?>
                         </div>     
                   </form>
		</div>   
	</div>                                  
<?php
}

function edit($TrattaId)
{
	include_once("tratta_validator.php");      


	  global $HtmlCommon,$user, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  


	$tratta=new Tratta();
	$tratta->Id=$TrattaId;
	$tratta->conn=$db;
	$tratta->inizializzaDatiGenerali();
	$arrTratta=$tratta->DatiGenerali;

	$TrattaTipo=new TrattaTipo();
	$TrattaTipo->conn=$db;
	$arr_tratta_tipo=$TrattaTipo->getAll();

	$Mezzo=new Mezzo();
	$Mezzo->conn=$db;
	$arr_mezzo=$Mezzo->getAll();

	$TrattaDirezione=new TrattaDirezione();
	$TrattaDirezione->conn=$db;
	$arr_tratta_direzione=$TrattaDirezione->getAll();

	$tipologiabus=new TipologiaBus();
	$tipologiabus->conn=$db;
	$arr_tipologiabus=$tipologiabus->getAllForSelect();

	$HtmlCommon->html_titolo_pagina($dizionario['tratta']['titolo_edita'],0,"rt_tratta","tratta.php");
	$HtmlCommon->html_titolo_box ($dizionario['tratta']['titolo_edita']." - ".$arrTratta['TrattaNome']);
	$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
	$arr_da_confermare[]= array("ConfermareId" => '0',"Confermare" => $dizionario['generale']['no']);
	$arr_da_confermare[]= array("ConfermareId" => '1',"Confermare" => $dizionario['generale']['si']);

		$DaConfermare=$arrTratta['DaConfermare'];
		
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
			<div class="brain_boxIntero">
					   <form id="application_form" name="application_form" method="post" action="#">
							 <div class="brain_formModifica">
									<div class="brain_data-content">   
					<?php
					$page->create_textbox_hidden("action","update");
					$page->create_textbox_hidden("idpost",$TrattaId);
					$page->create_textbox_hidden("Tratta[TrattaDirezioneId]", $arrTratta['TrattaDirezioneId']);
					$page->create_textbox_hidden("Tratta[NodoPeso]", $arrTratta['NodoPeso']);
					$page->create_textbox_hidden("Tratta[DaConfermare]", $DaConfermare);
					$page->create_textbox_hidden("Tratta[TipologiaBusDefaultId]", $arrTratta['TipologiaBusDefaultId']);
					print("<br style=\"clear:both;\"/>");
					$page->create_textbox($dizionario['tratta']['tratta'],"TrattaNome","Tratta[TrattaNome]",$arrTratta['TrattaNome'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
					$page->create_select($dizionario['tratta']['tipologia'].":","Tratta[TrattaTipoId]","TrattaTipoId","brain_campoForm",$arr_tratta_tipo,$arrTratta['TrattaTipoId'],"AppTrattaTipoId","AppTrattaTipo",array("class"=>"'required'"),1);
					$page->create_select($dizionario['tratta']['mezzo'].":","Tratta[MezzoId]","MezzoId","brain_campoForm",$arr_mezzo,$arrTratta['MezzoId'],"AppMezzoId","AppMezzo",array("class"=>"'required'"),1);
					$page->create_textbox($dizionario['generale']['peso'],"Peso","Tratta[TrattaPeso]",$arrTratta['TrattaPeso'],1,"brain_campoForm",array("class"=>"'required'"));
					$page->create_textbox($dizionario['tratta']['km_tratta'],"KmTratta","Tratta[KmTratta]",$arrTratta['KmTratta'],1,"brain_campoForm",array("class"=>"'required number'"));
					//$page->create_select($dizionario['generale']['tipo_bus'],"Tratta[TipologiaBusDefaultId]","TipologiaBusDefaultId","brain_campoForm",$arr_tipologiabus,$arrTratta['TipologiaBusDefaultId'],"TipologiaBusId","TipologiaBus",null,0);           
					$page->create_select($dizionario['generale']['stato'],"Tratta[Stato]","StatoId","brain_campoForm",$arr_stato,$arrTratta['Stato'],"StatoId","Stato",array("class"=>"'required'"),1);
					print("<br style=\"clear:both;\"/>");
					
					/*$page->create_select("Gestore","Sede[GestoreId]","GestoreId","brain_campoForm",$arr_gestori,$GestoreId,"GestoreId","RagioneSociale",
					array("class"=>"'required'"),1);*/
					
					print("<br style=\"clear:both;\"/>");
					?>
				</div>
							 </div>
							<div class="divSubmit">
										<?
									  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
									  //$page->create_button("Cancella","Cancella","elimina","brain_cancella","button");
										?>
											  

								</div>     
								 
								 
							</form>
						</div>   
			</div>
	<?php  
}


function show_list() {
	global $user, $HtmlCommon, $dizionario, $nascondi;
	$HtmlCommon->html_titolo_pagina($dizionario['tratta']['titolo_elenco']);
	$HtmlCommon->html_titolo_box($dizionario['tratta']['titolo_elenco']);
	$db= new Database();
	$db->connect();
	$nascondi = null;
	if(isset($_GET['nascondi'])) {
		$nascondi = $_GET['nascondi'];
	}
	include_once("tratta_validator.php");
	include_once("tratta_datatable.php");
	 
	if (isset($nascondi) && $nascondi == 'true'){
		$HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tratta','tratta.php?nascondi=false',$dizionario['tratta']['visualizza']);
	} else {
		$HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tratta','tratta.php?nascondi=true',$dizionario['tratta']['nascondi']);
	}
	
	?>
	<div class="brain_servicebar">
		<a class="brain_aggiungi est" href="#" onclick="javascript:ExternalLoad('rt_tratta','tratta.php?do=updateFermata');" title="<?php echo $dizionario['tratta']['modifica_fermata']?>"><?php echo $dizionario['tratta']['modifica_fermata'];?></a>
	</div>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	    <thead>
	        <tr class="brain_tabellaTr">
	            <th width="5%"><?=$dizionario['generale']['stato']?></th>
	            <th width="20%"><?=$dizionario['generale']['tratta']?></th>
	            <th width="10%"><?=$dizionario['generale']['tipo']?></th>
	            <th width="10%"><?=$dizionario['tratta']['mezzo']?></th>
	            <th width="10%"><?=$dizionario['linea']['area']?></th>
	            <th width="20%"><?=$dizionario['generale']['linea']?></th>
	            <th width="20%"><?=$dizionario['tratta']['percorso']?></th>
	            <th width="5%"><?=$dizionario['generale']['edita']?></th>
	            <th width="5%"><?php echo $dizionario['autista']['elimina'];?></th>
	        </tr>
	        <tr class="brain_tabellaFilter">
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
	            <td colspan="9" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
	        </tr>
	    </tbody>
	    <tfoot> 
	        <tr>
	            <td colspan="9" ></td>
	        </tr> 
	    </tfoot> 
	</table>
	
	<?   
}



if(is_object($user)) {
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
	            case "add":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						add();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					break;
                                
				case "edit":
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						edit($_REQUEST['TrattaId']);
					else
						Errors::$ErrorePermessiModuloFunzione;    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					break;
                                
				case "edit_wz":
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						edit($_REQUEST['TrattaId']);
					else
						Errors::$ErrorePermessiModuloFunzione;    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					break;
                             
				case "updateFermata":
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						updateFermata();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;
				
				default:
				$FunzioneId=1;
                                show_list();    
                         		// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
			}
		
	} // end verifica permessi
	else {
            Errors::$ErrorePermessiModulo;
            
        }

} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>