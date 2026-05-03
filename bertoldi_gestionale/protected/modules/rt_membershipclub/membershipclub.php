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
include_once($classespath_."class.MembershipClub.php");
include_once($classespath_."class.AnagraficaTipo.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Lingua.php");



global $ModuloId;
$ModuloId=32;// modulo base mediazione
global $user;
global $membership_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$membership_wizard=null;

if(isset($_SESSION['MEMBERSHIP_WIZARD'])) {
$membership_wizard=unserialize($_SESSION['MEMBERSHIP_WIZARD']);
}




function show_list() {
    
global $user,$HtmlCommon,$ModuloId, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['membership']['titolo'],0,"","");
$HtmlCommon->html_titolo_box($dizionario['membership']['titolo']);
$db= new Database();
$db->connect();

global $user,$HtmlCommon,$db,$ModuloId;    
include_once("membershipclub_validator.php");
include_once("membershipclub_datatable.php");

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi Member','rt_membershipclub','membershipclub.php?do=add',$dizionario['membership']['aggiungi']);

?>
       
                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
		<tr class="brain_tabellaTr">
			<th width="5%"><?=$dizionario['generale']['stato']?></th>
		    <th width="5%"><?=$dizionario['corsa']['codice']?></th>
			<th width="20%"><?=$dizionario['generale']['cognome']?></th>
			<th width="20%"><?=$dizionario['generale']['nome']?></th>
			<th width="10%"><?=$dizionario['generale']['codice_fiscale']?></th>
			<th width="10%"><?=$dizionario['gestore']['partita_iva']?></th>
            <th width="20%"><?=$dizionario['generale']['email']?></th>
            <th width="10%"><?=$dizionario['generale']['telefono']?></th>
            <th width="5%"><?=$dizionario['generale']['edita']?></th>
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
			<td colspan="9"></td>
		</tr> 
	</tfoot> 
</table>
<?
}



function cerca_member()
{
$db= new Database();
$db->connect();

include_once("membershipclub_datatable_cerca.php");
global $user,$HtmlCommon,$ModuloId, $dizionario;

$HtmlCommon->html_titolo_pagina($dizionario['membership']['titolo_cerca'],0,"","");
$HtmlCommon->html_titolo_box($dizionario['membership']['titolo_cerca']);
/*
$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','anagraficaest','anagraficaest.php?do=add','aggiungi anagrafica est');
*/
?>


            

<!--<div><a href="javascript:void(0);" onclick="ExternalLoad('anagrafica','anagrafica.php?do=add',this);" title="Crea Anagrafica">aggiungi anagrafica</a></div>-->
                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
            
            	<tr class="brain_tabellaTr">
		        <th width="5%"><?=$dizionario['corsa']['codice']?></th>
			<th width="20%"><?=$dizionario['generale']['cognome']?></th>
			<th width="15%"><?=$dizionario['generale']['nome']?></th>
			<th width="10%"><?=$dizionario['generale']['codice_fiscale']?></th>
			<th width="10%"><?=$dizionario['generale']['partita_iva']?></th>
			<th width="20%"><?=$dizionario['generale']['indirizzo']?></th>
                        <th width="10%"><?=$dizionario['generale']['comune']?></th>
                        <th width="5%"><?=$dizionario['generale']['provincia']?></th>
                	
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
			
		</tr>
	</thead>
	<tbody>
         
		<tr>
			<td colspan="9" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
		</tr>
	</tbody>
	<tfoot> 
		<tr> 
			<td colspan="9"></td>
		</tr> 
	</tfoot> 
</table>
<?
}




function edit($MembershipClubId)
{
    
    global $membership_wizard,$db,$user;   
    $membership_wizard=new MembershipClub($MembershipClubId);
  
    
    $_SESSION['MEMBERSHIP_WIZARD']=serialize($membership_wizard);
    add(1);
}


function carica_menu_mediazione($step_corrente,$mod)
{
global $abilita_modifica,$membership_wizard,$db, $dizionario;
$membership_wizard->conn=$db;
//$menu=$membership_wizard->getMenuWizard();

   
$menu=array(
    1=>$dizionario['membership']["dati_generali"]
   // 2=>"Provvigioni"
   );



    ?>
                <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
			<ul>
                         <?
                         $contamenu=1;
                         while ($contamenu<=1)
                         {
                          $class1="";
                          $class2="";
                          
                          if ($contamenu==$step_corrente)
                          {
                              $class1="sel";
                              $class2="brain_firstspan sel";
                          }
                             
                          $StatoStep="";
                            
                          if ( ($contamenu<=2) or (($contamenu>2) and ($mod))) { ?>
                            
                            <li class="<?=$class1?>">
                                <span class="<?=$class2?>">
                                  <? //if (($contamenu<=$step_corrente) or ($mod==1))
                                  if ($mod)
                                  {
                                     
                                      
                                   ?>
                                             <a href="javascript:void(0);" onclick="loadMediazioneStep('rt_membershipclub','membershipclub.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
                                    <?

                                  }
                                  else
                                    echo($menu[$contamenu]);
                                   
                              
                                
                           ?>     
                                </span>
                                
                                
                                
                                
                            
                            </li>
                            <?
                          }
                             
                             
                             $contamenu++;
                         }
                         
                         ?>
                         
                         
                                                     
				<!--<li class="sel"><span class="brain_firstspan sel"><a href="">Dati generali</a></span></li>
				<li class=""><span class="">Parte istante</span></li>
				<li class=""><span class="">Controparte</span></li>
				<li class=""><span class="">Oggetto e ragioni della pretesa</span></li>
				<li class=""><span class="">Documenti allegati</span></li>
				<li class=""><span class="">Pagamenti</span></li>
				<li class=""><span class="">Incontro di mediazione</span></li>-->
			</ul>
		</div>
 
	<?php
}

function add_step_dati_generali() {
    $step_corrente=1;
 
    global $membership_wizard,$user,$db, $dizionario;

    $page=new Form();
    $dt=new DT();

     $azione="add"; 
     $action="create"; 

    if (is_object($membership_wizard) and ($membership_wizard->Id)) {
  
        $Id=$membership_wizard->Id;
        
        $membership_wizard->conn=$db;
        $membership_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr = $membership_wizard->DatiGenerali;
        $_SESSION['MEMBERSHIP_WIZARD'] = serialize($membership_wizard);
        
        if($DatiGeneraliArr['MembershipClubId']) {
            $azione="edit";     
            $action="update"; 
        }
    }    
    include_once("membershipclub_validator.php");
    ?>
	<form id="application_form" name="application_form"  method="post" action="#">
    <?php
       $page->create_textbox_hidden("action",$action);
       $page->create_textbox_hidden("step_corrente",$step_corrente);
       $page->create_textbox_hidden("step_successivo",'0');
       if(isset($Id) && $Id > 0) {
           $page->create_textbox_hidden("MemberId",$Id);
       } else {
           $page->create_textbox_hidden("MemberId","-1");
       }
    ?>
    	<div class="brain_formModifica">
            <?php if ($action=="create") { 
                   $_SESSION['MEMBERSHIP_WIZARD']=null;
                   unset($_SESSION['MEMBERSHIP_WIZARD']);
                ?>
            	<h2>Dati generali</h2>
            <?php } else { ?>
            	<h2><span class="brain_colorh2"><?=$membership_wizard->DatiGenerali['CognomeRagioneSociale']." ".$membership_wizard->DatiGenerali['Nome']." (".$membership_wizard->DatiGenerali['MembershipClubCode'].")"?> -  </span><?$dizionario['dati_generali']['membership']?></h2>
            <?php } ?>
           	<div class="brain_data-content">                  
                <?php 
                form_tipo1($azione, $Id);
                ?>
            	<br style="clear:both;"/>                                 
        	</div>
        </div>
        <?php spara_pulsanti_wizard(0) ?>
	</form>
	<?php
    $db->Close();   
}


function form_tipo1($azione, $AnagraficaId) {
    global $HtmlCommon,$db,$user,$membership_wizard, $dizionario;    
    
    $page=new Form();
    $dt=new DT();

    $CognomeRagioneSociale="";
    $Nome="";
    $CodiceFiscale="";
    $PartitaIva="";
    $Indirizzo="";
    $Cap="";
    $NazioneResidenzaId=1;
    $RegioneResidenzaId=0;
    $ComuneResidenzaId=0;
    $IndirizzoResidenza="";
    $CapResidenza="";
    
    $Telefono="";
    $Cellulare="";
    $Email="";
    $EmailPec="";
    $arr_comune_residenza=array();
    $nazione_residenza=new Nazione(null);
    $nazione_residenza->conn=$db;
    $nazione_residenza->getAllNazione();
    $arr_nazione_residenza=$nazione_residenza->ArrNazione;
    $Stato=0;
    $ProfiloProvvigionale=0;
    $Termini = 1;
    $TrattamentoDati = 1;
    $Newsletter = 1;
    $Comunicazioni = 1;
    
    
    $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
    $arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
  
    $arr_privacy[]= array("PrivacyId" => '0',"Privacy" => $dizionario['generale']['no']);
    $arr_privacy[]= array("PrivacyId" => '1',"Privacy" => $dizionario['generale']['si']);

    if ($azione=="edit") {    
    
        $DatiGeneraliArr=$membership_wizard->DatiGenerali;    
        $CognomeRagioneSociale=$DatiGeneraliArr['CognomeRagioneSociale'];
        $Nome=$DatiGeneraliArr['Nome'];
        $CodiceFiscale=$DatiGeneraliArr['CodiceFiscale'];
        $PartitaIva=$DatiGeneraliArr['PartitaIva'];
        $Fax=$DatiGeneraliArr['Fax'];
        $Telefono=$DatiGeneraliArr['Telefono'];
        $Cellulare=$DatiGeneraliArr['Cellulare'];
        $Email=$DatiGeneraliArr['Email'];
        $EmailPec=$DatiGeneraliArr['EmailPec'];
    
        $IndirizzoResidenza=$DatiGeneraliArr['IndirizzoResidenza'];
        $CapResidenza=$DatiGeneraliArr['CapResidenza'];
        $ComuneResidenza=$DatiGeneraliArr['Comune'];
  
        $ComuneResidenzaId=$DatiGeneraliArr['ComuneResidenzaId'];
        $NazioneResidenzaId=$DatiGeneraliArr['idnazione'];
        $Stato=$DatiGeneraliArr['Stato'];
        $Termini = $DatiGeneraliArr['Termini'];
        $TrattamentoDati = $DatiGeneraliArr['TrattamentoDati'];
        $Newsletter = $DatiGeneraliArr['Newsletter'];
        $Comunicazioni = $DatiGeneraliArr['Comunicazioni'];
    }
    
    print("<h3>".$dizionario['membership']['anagrafica']."</h3>");
    
    $page->create_textbox($dizionario['membership']['cognome_rag_social'],"CognomeRagioneSociale","Member[CognomeRagioneSociale]",$CognomeRagioneSociale,1,"brain_campiform",array("class"=>"'required'"));
    $page->create_textbox($dizionario['generale']['nome'],"Nome","Member[Nome]",$Nome,0,"brain_campiform",null);
    print("<br style=\"clear:both;\"/>");
    $page->create_textbox($dizionario['generale']['codice_fiscale'],"CodiceFiscale","Member[CodiceFiscale]",$CodiceFiscale,0,"brain_campiform",array("onBlur"=>"'if(ControllaCF($(\"#CodiceFiscale\").val())!=\"\") alert(ControllaCF($(\"#CodiceFiscale\").val()));'"),"","","16");
    $page->create_textbox($dizionario['gestore']['partita_iva'],"PartitaIva","Member[PartitaIva]",$PartitaIva,0,"brain_campiform",array("onBlur"=>"'if(ControllaPIVA($(\"#PartitaIva\").val())!=\"\") alert(ControllaPIVA($(\"#PartitaIva\").val()));'"),"","","11");
    print("<br style=\"clear:both;\"/>");
    
    print("<h3>Residenza / Sede</h3>"); 
    $page->create_textbox($dizionario['generale']['indirizzo'],"Indirizzo","Member[IndirizzoResidenza]",$IndirizzoResidenza,1,"brain_campiform ",array("class"=>"'required'"),"","35");
    $page->create_textbox($dizionario['comune']['cap'],"Cap","Member[CapResidenza]",$CapResidenza,1,"brain_campiform",array("class"=>"'required digits'"),"","5","5");
    print(" <br style=\"clear:both;\"/>");
    
    $page->create_select($dizionario['comune']['nazione'],"StatoResidenzaId","StatoResidenzaId","brain_campiform",$arr_nazione_residenza,$NazioneResidenzaId,"NazioneId","Nazione",
    array("class"=>"'required'"),1);        
    
    $page->create_textbox_hidden_differentId("ComuneResidenzaId","Member[ComuneResidenzaId]",$ComuneResidenzaId);
        
    $selector="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'comune','comune.php?do=cerca&fieldtoupdate=ComuneResidenzaId&labeltoupdate=ComuneResidenza','top');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona comune\" border=\"0\" /></a>";
    $selector_ann="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"$('#ComuneResidenzaId').val('0');$('#ComuneResidenza').val('');\" style=\"margin-left:2px\"><img src=\"/images/iconset/delete_gray.gif\" alt=\"cancella campo\"  border=\"0\" /></a>";
    $selector.=$selector_ann; 
    $page->create_textbox_with_sel($dizionario['generale']['comune'],"ComuneResidenza","ComuneResidenza",$ComuneResidenza,1,"brain_campiform",array("class"=>"'required'","readonly"=>"'readonly'"),$selector);
    ?>
 
	<div id="elenco_comuni"></div>
        <?php
        print("<br style=\"clear:both;\"/>");
    
        print("<br style=\"clear:both;\"/>");
        print("<h3>".$dizionario['generale']['recapiti']."</h3>"); 
        $page->create_textbox($dizionario['generale']['telefono'],"Telefono","Member[Telefono]",$Telefono,0,"brain_campiform","");
        $page->create_textbox($dizionario['generale']['cell'],"Cellulare","Member[Cellulare]",$Cellulare,1,"brain_campiform",array("class"=>"'required'"));
        $page->create_textbox($dizionario['generale']['fax'],"Fax","Member[Fax]",$Fax,0,"brain_campiform","");
        print("<br style=\"clear:both;\"/>");
        $page->create_textbox($dizionario['membership']['email'],"Email","Member[Email]",$Email,1,"brain_campiform campiformBig",array("class"=>"'required email'"));
        $page->create_textbox($dizionario['generale']['emailpec'],"EmailPec","Member[EmailPec]",$EmailPec,0,"brain_campiform campiformBig",array("class"=>"'email'"));
        print("<br style=\"clear:both;\"/>");

        $page->create_select($dizionario['generale']['termini'],"Member[Termini]","PrivacyId","brain_campiform",$arr_privacy,$Termini,"PrivacyId","Privacy",
            array("class"=>"'required'"),1);
        $page->create_select($dizionario['generale']['trattamento_dati'],"Member[TrattamentoDati]","PrivacyId","brain_campiform",$arr_privacy,$TrattamentoDati,"PrivacyId","Privacy",
            array("class"=>"'required'"),1);
        $page->create_select($dizionario['generale']['comunicazioni'],"Member[Comunicazioni]","PrivacyId","brain_campiform",$arr_privacy,$Comunicazioni,"PrivacyId","Privacy",
            array("class"=>"'required'"),1);
        $page->create_select($dizionario['generale']['newsletter'],"Member[Newsletter]","PrivacyId","brain_campiform",$arr_privacy,$Newsletter,"PrivacyId","Privacy",
            array("class"=>"'required'"),1);
        
        print("<br style=\"clear:both;\"/>");print("<br style=\"clear:both;\"/>");
        
        $page->create_select($dizionario['generale']['stato'],"Member[Stato]","StatoId","brain_campiform",$arr_stato,$Stato,"StatoId","Stato",
            array("class"=>"'required'"),1);
    
}
















function add($step) {
    include_once("membershipclub_validator.php");  
    global $HtmlCommon,$db,$membership_wizard,$funzione_edit,$abilita_modifica, $dizionario;

    if (!$step) {
        $membership_wizard=null;    
        unset($membership_wizard);
        $_SESSION['MEMBERSHIP_WIZARD']=null;
        unset($_SESSION['MEMBERSHIP_WIZARD']);
        $step=1;
    }
    $mod=0;
    $PromoterStato=-1;
    if (is_object($membership_wizard)) {
        $mod=1;
        $Id=$membership_wizard->Id;
        $membership_wizard->conn=$db;
        $membership_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr=$membership_wizard->DatiGenerali;
        $PromoterStato=$DatiGeneraliArr['Stato'];
        $abilita_modifica=true;
        $HtmlCommon->html_titolo_pagina($dizionario['membership']['titolo_modifica_member']." ".$DatiGeneraliArr['CognomeRagioneSociale']." ".$DatiGeneraliArr['Nome'],1,"rt_membershipclub","membershipclub.php");
    } else {    
        $mod=0;    
        $HtmlCommon->html_titolo_pagina($dizionario['membership']['titolo_aggiungi_member'],1,"rt_membershipclub","membershipclub.php");
                   $abilita_modifica=false;
    }
    carica_menu_mediazione($step,$mod);
    ?>
		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
 	<?php       
    if ($step==1)
        add_step_dati_generali();

    $db= new Database();
    $db->connect();

    ?>  
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

function spara_pulsanti_wizard($steptogo) {
    
	global $funzione_edit, $dizionario;

	if ($funzione_edit) {
	    spara_pulsanti_edit($steptogo);
	} else {
		if (!$funzione_edit){
			$page=new Form();
		}
	?>
		<div class="divSubmit">
	                                
	        <?  
	        $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
	   		<?  
	    	if ($steptogo>0){
	    		$page->create_button("indietro","indietro",$dizionario['generale']['indietro'],"brain_back","button"); ?>
	    
	         	<a href="javascript:void(0);" onclick="loadMainContent('rt_membershipclub','membershipclub.php',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
	         	<select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
			<?php } ?>
					
		</div>
	<?    
	}
}

function spara_pulsanti_edit($steptogo)
{
   
global $abilita_modifica;
    
    $page=new Form();
    
    ?>
<div class="divSubmit">
                         
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
       
         <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>  
    <?
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
    if (is_object($membership_wizard))
        $membership_wizard->conn=$db;
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
                                        $ModuloId=3;
					$FunzioneId=2;
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            add($_REQUEST['step']);
                                        else
                                           $errore->stampa_errore(2);
                                        
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                               
				case "edit":
				$ModuloId=3;
                                 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                // print_r($permesso);
                                        if (sizeof($permesso))
                                           edit($_REQUEST['MembershipClubId']);
                                        else
                                            $errore->stampa_errore(2);
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "cerca":
				
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
                                
                                        if (sizeof($permesso))
                                          cerca_member();
                                        else
                                            $errore->stampa_errore(2);
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "add_disponibilita":
                                    $ModuloId=3;
					$FunzioneId=2;
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            add_disponibilita();
                                        else
                                           $errore->stampa_errore(2);
                                        
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "edit_disponibilita":
                                    $ModuloId=3;
					$FunzioneId=2;
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            edit_disponibilita($_REQUEST['MediatoreDisponibilitaId']);
                                        else
                                           $errore->stampa_errore(2);
                                        
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                               


				default:
				$ModuloId=3;
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
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>