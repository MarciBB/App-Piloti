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

include_once($classespath_."class.TipologiaBiglietto.php");

$ModuloId=17;

function add() {
	global $HtmlCommon,$user, $dizionario;  

	$db = new Database();
	$db->connect();  
	$page=new Form();  
	
	include_once("tipologiabiglietto_validator.php");       
	$HtmlCommon->html_titolo_pagina($dizionario['tipo_big']['titolo_aggiungi'],1,"rt_tipologiabiglietto","tipologiabiglietto.php");

	$HtmlCommon->html_titolo_box($dizionario['tipo_big']['titolo_aggiungi']);  


	$arr_stato = array();
	$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
	
	$arr_ar = array();
	$arr_ar[]= array("ARId" => '0',"AR" => $dizionario['biglietto']['solo_andata']);
	$arr_ar[]= array("ARId" => '1',"AR" => $dizionario['biglietto']['andata_ritorno']);
	
	$arr_tour = array();
	$arr_tour[]= array("TipoTourId" => '0',"TipoTour" => $dizionario['generale']['tour_gruppo']);
	$arr_tour[]= array("TipoTourId" => '1',"TipoTour" => $dizionario['generale']['tour_privato']);
	$arr_tour[]= array("TipoTourId" => '2',"TipoTour" => $dizionario['generale']['entrambi']);
	
	$arr_occupaposto = array();
	$arr_occupaposto[]= array("OccupaPostoId" => '0',"OccupaPosto" => $dizionario['tipo_big']['servizio']);
	$arr_occupaposto[]= array("OccupaPostoId" => '1',"OccupaPosto" => $dizionario['tipo_big']['passeggero']);
	
	$arr_tipoprezzo = array();
	$arr_tipoprezzo[]= array("TipoPrezzoId" => '0',"TipoPrezzo" => $dizionario['tipo_big']['prezzo_fisso']);
	$arr_tipoprezzo[]= array("TipoPrezzoId" => '1',"TipoPrezzo" => $dizionario['tipo_big']['prezzo_percentuale']);
	
	$sql = "Select * from RT_TipologiaBiglietto where TipoPrezzo = 0 AND Stato = 1 AND Cancella = 0 AND OccupaPosto = 1 order by TipologiaBiglietto ASC";
	$tipi = $db->fetch_array($sql);
	$arr_biglietti = array();
	foreach($tipi as $t){
		$arr_biglietti[] = array("TipologiaBigliettoId" => $t['TipologiaBigliettoId'],"TipologiaBiglietto" => $t['TipologiaBiglietto']);
	}
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
			<form id="application_form" name="application_form" method="post" action="#">
				<div class="brain_formModifica">
					<div class="brain_data-content">    
		                <?php
		                $page->create_textbox_hidden("action","create");
		                $page->create_textbox_hidden("TipologiaBiglietto[AR]", 0);
		                
		                $page->create_textbox($dizionario['biglietto']['dett_tipologia'],"TipologiaBiglietto","TipologiaBiglietto[TipologiaBiglietto]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
		               
		                $page->create_select($dizionario['generale']['tipo_tour'],"TipologiaBiglietto[TipoTour]","TipoTourId","brain_campoForm",$arr_tour,0,"TipoTourId","TipoTour", array("class"=>"'required'"),1);
		               
		                print("<br style=\"clear:both;\"/>");
						$page->create_texarea($dizionario['generale']['descrizione'],"TipologiaBigliettoDescr","TipologiaBiglietto[TipologiaBigliettoDescr]","",1,"brain_campoForm",array("class"=>"'required'","cols"=>"'60'","rows"=>"'12'"));     
		                 
						print("<br style=\"clear:both;\"/>");
						$page->create_select($dizionario['tipo_big']['tipo'],"TipologiaBiglietto[OccupaPosto]","OccupaPosto","brain_campoForm",$arr_occupaposto,1,"OccupaPostoId","OccupaPosto", array("class"=>"'required'"),1);
								
		                 
						print("<br style=\"clear:both;\"/>");
						print($dizionario['tipo_big']['descrizione_tipo']);
						print("<br style=\"clear:both;\"/>");
						
						print("<br style=\"clear:both;\"/>");
						$page->create_select($dizionario['tipo_big']['tipo_prezzo'], "TipologiaBiglietto[TipoPrezzo]", "TipoPrezzo", "brain_campoForm", $arr_tipoprezzo, 0, "TipoPrezzoId", "TipoPrezzo", array("class"=>"'required'"),1);
						$page->create_select($dizionario['tipo_big']['tipo_riferimento'], "TipologiaBiglietto[TipoBigliettoIdRiferimento]", "TipoBigliettoIdRiferimento", "brain_campoForm", $arr_biglietti, "", "TipologiaBigliettoId", "TipologiaBiglietto", array("class"=>"'required'", "disabled" =>"'disabled'"),1);
						print("<br style=\"clear:both;\"/>");
						$page->create_textbox($dizionario['generale']['eta']." ".$dizionario['generale']['da'],"EtaDa","TipologiaBiglietto[EtaDa]","",1,"brain_campoForm campiformBig",array("class"=>"'required number'"));    
		                
						$page->create_textbox($dizionario['generale']['eta']." ".$dizionario['generale']['a'],"EtaA","TipologiaBiglietto[EtaA]","",1,"brain_campoForm campiformBig",array("class"=>"'required number'"));       
						print("<br style=\"clear:both;\"/>");

						$page->create_textbox($dizionario['generale']['peso'],"Peso","TipologiaBiglietto[TipologiaBigliettoPeso]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
		               
		                $page->create_select($dizionario['generale']['stato'],"TipologiaBiglietto[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",
		                array("class"=>"'required'"),1);
		                print("<br style=\"clear:both;\"/>");
						?>
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


function edit($TipologiaBigliettoId) {
	include_once("tipologiabiglietto_validator.php");
  	global $HtmlCommon,$user, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  

	$tipologiabiglietto=new TipologiaBiglietto($TipologiaBigliettoId);
	$tipologiabiglietto->Id=$TipologiaBigliettoId;
	$tipologiabiglietto->conn=$db;
	$tipologiabiglietto->inizializzaDatiGenerali();
	$arrTipologiaBiglietto=$tipologiabiglietto->DatiGenerali;

	$HtmlCommon->html_titolo_pagina($dizionario['tipo_big']['titolo_modifica']." - ".$arrTipologiaBiglietto['TipologiaBiglietto'],1,"rt_tipologiabiglietto","tipologiabiglietto.php");
	$HtmlCommon->html_titolo_box ($dizionario['tipo_big']['titolo_modifica']." - ".$arrTipologiaBiglietto['TipologiaBiglietto']);
	$arr_stato = array();
	$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
	
	$arr_ar = array();
	$arr_ar[]= array("ARId" => '0',"AR" => $dizionario['biglietto']['solo_andata']);
	$arr_ar[]= array("ARId" => '1',"AR" => $dizionario['biglietto']['andata_ritorno']);
	
	$arr_tour = array();
	$arr_tour[]= array("TipoTourId" => '0',"TipoTour" => $dizionario['generale']['tour_gruppo']);
	$arr_tour[]= array("TipoTourId" => '1',"TipoTour" => $dizionario['generale']['tour_privato']);
	$arr_tour[]= array("TipoTourId" => '2',"TipoTour" => $dizionario['generale']['entrambi']);

	$arr_occupaposto = array();
	$arr_occupaposto[]= array("OccupaPostoId" => '0',"OccupaPosto" => $dizionario['tipo_big']['servizio']);
	$arr_occupaposto[]= array("OccupaPostoId" => '1',"OccupaPosto" => $dizionario['tipo_big']['passeggero']);
	
	$arr_tipoprezzo = array();
	$arr_tipoprezzo[]= array("TipoPrezzoId" => '0',"TipoPrezzo" => $dizionario['tipo_big']['prezzo_fisso']);
	$arr_tipoprezzo[]= array("TipoPrezzoId" => '1',"TipoPrezzo" => $dizionario['tipo_big']['prezzo_percentuale']);
	
	$sql = "Select * from RT_TipologiaBiglietto where TipoPrezzo = 0 AND Stato = 1 AND Cancella = 0 AND OccupaPosto = 1 order by TipologiaBiglietto ASC";
	$tipi = $db->fetch_array($sql);
	$arr_biglietti = array();
	foreach($tipi as $t){
		$arr_biglietti[] = array("TipologiaBigliettoId" => $t['TipologiaBigliettoId'],"TipologiaBiglietto" => $t['TipologiaBiglietto']);
	}
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
			<form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
			                <?php
				            $page->create_textbox_hidden("action","update");
			                $page->create_textbox_hidden("idpost",$TipologiaBigliettoId);
			                $page->create_textbox_hidden("TipologiaBiglietto[AR]", $arrTipologiaBiglietto['AR']);
			                print("<br style=\"clear:both;\"/>");
			                if($TipologiaBigliettoId == 17){
				                echo $dizionario['biglietto']['tipo_default'];
				                print("<br style=\"clear:both;\"/>");
				                print("<br style=\"clear:both;\"/>");
			                }
			                $page->create_textbox($dizionario['biglietto']['dett_tipologia'],"Tipologia Biglietto","TipologiaBiglietto[TipologiaBiglietto]",$arrTipologiaBiglietto['TipologiaBiglietto'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
			                 
			                $page->create_select($dizionario['generale']['tipo_tour'],"TipologiaBiglietto[TipoTour]","TipoTourId","brain_campoForm",$arr_tour,$arrTipologiaBiglietto['TipoTour'],"TipoTourId","TipoTour", array("class"=>"'required'"),1);
			                
			                print("<br style=\"clear:both;\"/>");
			                $page->create_texarea($dizionario['generale']['descrizione'],"TipologiaBiglietto[TipologiaBigliettoDescr]","TipologiaBiglietto[TipologiaBigliettoDescr]",$arrTipologiaBiglietto['TipologiaBigliettoDescr'],1,"brain_campoForm",array("class"=>"'required'","cols"=>"'60'","rows"=>"'12'"));      
			                 
			                print("<br style=\"clear:both;\"/>");
			                 
			                $page->create_select($dizionario['tipo_big']['tipo'],"TipologiaBiglietto[OccupaPosto]","OccupaPosto","brain_campoForm",$arr_occupaposto,$arrTipologiaBiglietto['OccupaPosto'],"OccupaPostoId","OccupaPosto",
			                 		array("class"=>"'required'"),1);
			                 
			                print("<br style=\"clear:both;\"/>");
			                print($dizionario['tipo_big']['descrizione_tipo']);
			                
							print("<br style=\"clear:both;\"/>");
							$page->create_select($dizionario['tipo_big']['tipo_prezzo'], "TipologiaBiglietto[TipoPrezzo]", "TipoPrezzo", "brain_campoForm", $arr_tipoprezzo, $arrTipologiaBiglietto['TipoPrezzo'], "TipoPrezzoId", "TipoPrezzo", array("class"=>"'required'"),1);
							$page->create_select($dizionario['tipo_big']['tipo_riferimento'], "TipologiaBiglietto[TipoBigliettoIdRiferimento]", "TipoBigliettoIdRiferimento", "brain_campoForm", $arr_biglietti, $arrTipologiaBiglietto['TipoBigliettoIdRiferimento'], "TipologiaBigliettoId", "TipologiaBiglietto", array("class"=>"'required'"),1);
						 
			                print("<br style=\"clear:both;\"/>");
			                $page->create_textbox($dizionario['generale']['eta']." ".$dizionario['generale']['da'],"EtaDa","TipologiaBiglietto[EtaDa]",$arrTipologiaBiglietto['EtaDa'],1,"brain_campoForm campiformBig",array("class"=>"'required number'"));    
			                 
			                $page->create_textbox($dizionario['generale']['eta']." ".$dizionario['generale']['a'],"EtaA","TipologiaBiglietto[EtaA]",$arrTipologiaBiglietto['EtaA'],1,"brain_campoForm campiformBig",array("class"=>"'required number'"));       
			                  
			                print("<br style=\"clear:both;\"/>");     
			                $page->create_textbox($dizionario['generale']['peso'],"Peso","TipologiaBiglietto[TipologiaBigliettoPeso]",$arrTipologiaBiglietto['TipologiaBigliettoPeso'],1,"brain_campoForm",array("class"=>"'required'"));
			                
							if($TipologiaBigliettoId == 17){
			               		$prop = array("class"=>"'required'", "disabled"=>"'disabled'");
							} else {
			               		$prop = array("class"=>"'required'");
							}
			              
			                $page->create_select($dizionario['generale']['stato'],"TipologiaBiglietto[Stato]","StatoId","brain_campoForm",$arr_stato,$arrTipologiaBiglietto['Stato'],"StatoId","Stato",
			                     $prop,1);
			                print("<br style=\"clear:both;\"/>");
			                
			                /*$page->create_select("Gestore","Sede[GestoreId]","GestoreId","brain_campoForm",$arr_gestori,$GestoreId,"GestoreId","RagioneSociale",
			                array("class"=>"'required'"),1);*/
			                
			                print("<br style=\"clear:both;\"/>");
			                ?>
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


function show_list()
{
global $user,$HtmlCommon,$db,$ModuloId, $dizionario;   
$HtmlCommon->html_titolo_pagina($dizionario['tipo_big']['titolo_elenco'], 0, "", "");
$HtmlCommon->html_titolo_box($dizionario['tipo_big']['titolo_elenco']);
$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tipologiabiglietto','tipologiabiglietto.php?do=add',$dizionario['tipo_big']['aggiungi']);


include_once("tipologiabiglietto_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="5%"><?=$dizionario['generale']['stato']?></th>
            <th width="30%"><?=$dizionario['tipo_big']['tipo_biglietto']?></th>
            <th width="30%"><?=$dizionario['generale']['descrizione']?></th>
            <th width="10%"><?=$dizionario['generale']['peso']?></th>
            <th width="5%"><?=$dizionario['tipo_big']['tipo']?></th>
            <th width="5%"><?=$dizionario['generale']['tipo_tour']?></th>
            <th width="5%"><?=$dizionario['generale']['edita']?></th>
        </tr>
        <tr class="brain_tabellaFilter">
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th>
            <th></th>
            <th></th>
            <th><input type="hidden" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="7" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
        </tr>
    </tbody>
    <tfoot> 
        <tr>
            <td colspan="7" ></td>
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
			if(!isset($_REQUEST['do'])) {
				$do = '';
			} else {
				$do = $_REQUEST['do'];
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
					   edit($_REQUEST['TipologiaBigliettoId']);
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