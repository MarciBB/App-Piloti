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

include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.TipologiaBus.php");

global $ModuloId;
$ModuloId=29;// modulo base mediazione
global $user;
global $tratta_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$tratta_wizard=null;

if(isset($_SESSION['TRATTA_WIZARD'])) {
$tratta_wizard=unserialize($_SESSION['TRATTA_WIZARD']);
}








function show_list()
{
   
global $user,$HtmlCommon,$ModuloId;
$HtmlCommon->html_titolo_pagina("Elenco Percorsi",0,"","");
$HtmlCommon->html_titolo_box("Elenco Percorsi");
$db= new Database();
$db->connect();



include_once("tratta_wz_validator.php");           
include_once("tratta_datatable.php");

global $user,$HtmlCommon,$db,$ModuloId;    
 

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tratta','tratta_wz.php?do=add','aggiungi percorso');

?>   
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
            
            	<tr class="brain_tabellaTr">
                    <th width="10%">Stato</th>
			<th width="90%">Percorso</th>
			<th width="5%">Peso</th>
			<th width="5%">edita</th>
		</tr>
            
		<tr class="brain_tabellaFilter">
                        <th><input type="text" /></th> 
			<th><input type="text" /></th> 
			<th><input type="text" /></th> 
			<th><input type="hidden" /></th> 
		</tr>
	</thead>
	<tbody>
         
		<tr>
			<td colspan="4" class="dataTables_empty">Caricamento in corso...</td>
		</tr>
	</tbody>
	<tfoot> 
		<tr> 
			<td colspan="4"></td>
		</tr> 
	</tfoot> 
</table>
<?   
$db->close();

}


function edit($TrattaId) {
    
    global $tratta_wizard,$db,$user;   
    $tratta_wizard=new Tratta($TrattaId);
  
    $_SESSION['TRATTA_WIZARD']=serialize($tratta_wizard);
    add(1);
}


function carica_menu_percorso($step_corrente,$mod)
{
global $abilita_modifica,$tratta_wizard,$db;
//$tratta_wizard->conn=$db;
//$menu=$tratta_wizard->getMenuWizard();

   
$menu=array(
    1=>"Tratta",
    2=>"Elenco fermate"
   
    );


    ?>
                <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
			<ul>
                         <?
                         $contamenu=1;
                         while ($contamenu<=2)
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
                                             <a href="javascript:void(0);" onclick="loadMediazioneStep('rt_tratta','tratta_wz.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
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

function add_step_tratta() {
	$step_corrente=1;
 	global $tratta_wizard,$user,$db;

 	$page=new Form();
 	$dt=new DT();

 	$azione="add"; 
 	$action="create"; 

    $TrattaId=0;
        
	if (is_object($tratta_wizard) and ($tratta_wizard->Id)) {
	    $TrattaId=$tratta_wizard->Id;
	    
	    $tratta_wizard->conn=$db;
	    $tratta_wizard->inizializzaDatiGenerali();
	    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
	    $_SESSION['TRATTA_WIZARD']=serialize($tratta_wizard);
	    
	    if($DatiGeneraliArr['TrattaId'])
	    {
	        $azione="edit";     
	        $action="update"; 
	    }                   
	}

	include_once("tratta_wz_validator.php");
	?>
	<form id="application_form" name="application_form"  method="post" action="#">
    	<?php
        $page->create_textbox_hidden("action",$action);
    	$page->create_textbox_hidden("step_corrente",$step_corrente);
    	$page->create_textbox_hidden("step_successivo",$step_corrente+1);
    	?>
                            
     	<div class="brain_formModifica">
     		<?php if ($action=="create") { ?>
            	<h2>Informazioni generali</h2>
            <?php } else { ?>
            	<h2><span class="brain_colorh2"><?=$tratta_wizard->DatiGenerali['TrattaNome']?></span></h2>
            <?php } ?>
            <div class="brain_data-content">                  
			<?php 
			form_tipo1($azione, $TrattaId);
            ?>
            
            <br style="clear:both;"/>                                 
        </div></div>
     	<?php spara_pulsanti_wizard(0) ?>
                
	</form>
	<?php   
	$db->Close();   
}

function form_tipo1($azione,$AnagraficaId) {
	global $HtmlCommon,$db,$user,$tratta_wizard, $dizionario;    
	/*$db= new Database();
	$db->connect();*/
	$page=new Form();
	$dt=new DT();
	
	$arr_stato[]= array("StatoId" => '0',"Stato" => 'Non Attivo');
	$arr_stato[]= array("StatoId" => '1',"Stato" => 'Attivo');
	
	$TrattaTipo=new TrattaTipo();
	$TrattaTipo->conn=$db;
	$arr_tratta_tipo=$TrattaTipo->getAll();
	
	$Mezzo = new Mezzo();
	$Mezzo->conn = $db;
	$arr_mezzo  =$Mezzo->getAll();
	
	$TrattaDirezione = new TrattaDirezione();
	$TrattaDirezione->conn=$db;
	$arr_tratta_direzione=$TrattaDirezione->getAll();
	
	$tipologiabus = new TipologiaBus();
	$tipologiabus->conn=$db;
	$arr_tipologiabus=$tipologiabus->getAllForSelect();
	
	$TrattaNome = "";
	$PercorsoPeso = "";
	$PercorsoStato = 0;
	$NodoPeso = 1;
	$TrattaDirezioneId = 1;
	$TipologiaBusDefaultId = null;
	
	if ($azione=="edit") {    
	    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
	    $TrattaNome=$DatiGeneraliArr['TrattaNome'];
	    $TrattaTipoId=$DatiGeneraliArr['TrattaTipoId'];
	    $MezzoId=$DatiGeneraliArr['MezzoId'];
	    $TrattaDirezioneId=$DatiGeneraliArr['TrattaDirezioneId'];
	    $TrattaPeso=$DatiGeneraliArr['TrattaPeso'];
	    $NodoPeso=$DatiGeneraliArr['NodoPeso'];
	    $TrattaStato=$DatiGeneraliArr['Stato'];
	    $KmTratta=$DatiGeneraliArr['KmTratta'];
	    $TipologiaBusId = $DatiGeneraliArr['TipologiaBusDefaultId'];
	}
	
	$page->create_textbox_hidden("action","update");
	$page->create_textbox_hidden("Tratta[TrattaDirezioneId]", $TrattaDirezioneId);
	$page->create_textbox_hidden("Tratta[NodoPeso]", $NodoPeso);
	$page->create_textbox_hidden("Tratta[TipologiaBusDefaultId]", $TipologiaBusDefaultId);
	$page->create_textbox($dizionario['tratta']['tratta'],"TrattaNome","Tratta[TrattaNome]",$TrattaNome,1,"brain_campoForm",array("class"=>"'required'"));           
	$page->create_select($dizionario['tratta']['tipologia'],"Tratta[TrattaTipoId]","TrattaTipoId","brain_campoForm",$arr_tratta_tipo,$TrattaTipoId,"AppTrattaTipoId","AppTrattaTipo",array("class"=>"'required'"),1);
	$page->create_select($dizionario['percorso']['mezzo'],"Tratta[MezzoId]","MezzoId","brain_campoForm",$arr_mezzo,$MezzoId,"AppMezzoId","AppMezzo",array("class"=>"'required'"),1);
	$page->create_textbox($dizionario['generale']['peso'],"Peso","Tratta[TrattaPeso]",$TrattaPeso,1,"brain_campoForm",array("class"=>"'required'"));
	$page->create_textbox($dizionario['tratta']['km_tratta'],"KmTratta","Tratta[KmTratta]",$KmTratta,1,"brain_campoForm",array("class"=>"'required number'"));
	//$page->create_select($dizionario['generale']['tipo_bus'],"Tratta[TipologiaBusDefaultId]","TipologiaBusDefaultId","brain_campoForm",$arr_tipologiabus,$TipologiaBusId,"TipologiaBusId","TipologiaBus",null,0);
	               
	print("<br style=\"clear:both;\"/>");
	$page->create_select("Stato","Tratta[Stato]","StatoId","brain_campoForm",$arr_stato,$TrattaStato,"StatoId","Stato", array("class"=>"'required'"),1);
	
	?>
	        
	<div id="elenco_comuni"></div>
	<?
	print("<br style=\"clear:both;\"/>");
}



function add_step_fermate_orari() {
    // Imposta lo step corrente come 2
    $step_corrente = 2;
    
    // Variabili globali usate nella funzione
    global $tratta_wizard, $user, $db, $dizionario;

    // Crea un nuovo oggetto Form per gestire il rendering del modulo
    $page = new Form();
    $dt = new DT();

    // Recupera l'ID della tratta dal wizard
    $TrattaId = $tratta_wizard->Id;

    // inizializzo array per select Si/NO
    $arr_is = [
        ["ArrIsId" => '0', "ArrIs" => $dizionario['generale']['no']],
        ["ArrIsId" => '1', "ArrIs" => $dizionario['generale']['si']]
    ];

    // inizializzo array per select stato
    $arr_stato = [
        ["StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']],
        ["StatoId" => '1', "Stato" => $dizionario['generale']['attivo']]
    ];

    // inizializzo array per select tratta
    $sql = "SELECT TrattaId,TrattaNome as Tratta 
        FROM RT_Tratta WHERE Stato = 1 AND Cancella = 0
        ORder by TrattaPeso ASC, TrattaNome ASC";
    $arrTratta = $db->fetch_array($sql);

    // Includi il file di validazione specifico per le fermate e orari
    include_once("tratta_wz_validator.php");
    ?>

    <!-- Inizio del form HTML -->
    <form id="application_form" name="application_form" method="post" action="#">
        <?php
        // Crea campi nascosti per mantenere traccia dello step corrente e del prossimo step
        $page->create_textbox_hidden("step_corrente", $step_corrente);
        $page->create_textbox_hidden("step_successivo", $step_corrente + 1);
        ?>
        
        <!-- Contenitore per la form -->
        <div class="brain_formModifica formGestoreEdita">
            <!-- Intestazione della sezione -->
            <h2>
                <span class="brain_colorh2"><?= $tratta_wizard->DatiGenerali['TrattaNome'] ?> - </span>
                <?php echo $dizionario['tratta']['elenco_fermate']; ?>
            </h2>
            
            <br /><br />
            
            <!-- Pulsante per aggiungere una nuova fermata -->
            <div class="GestoreSedeAdd">
                <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_fermata','fermata.php?do=add&TrattaId=<?= $TrattaId ?>');" title="<?php echo $dizionario['fermata']['titolo_aggiungi']; ?>">
                    <i class="fa fa-plus" aria-hidden="true"></i> 
                    <?php echo $dizionario['fermata']['titolo_aggiungi']; ?>
                </a>
            </div>
            
            <br />
            
            <!-- Tabella per l'elenco delle fermate -->
            <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                <tbody>
                    <tr class="rowIntestazione">
                        <!-- Intestazioni delle colonne della tabella -->
                        <td><?php echo $dizionario['generale']['peso']; ?></td>
                        <td><?php echo $dizionario['generale']['comune']; ?></td>
                        <td><?php echo $dizionario['generale']['fermata']; ?></td>
                        <td><?php echo $dizionario['tratta']['durata_inizio']; ?></td>
                        <td><?php echo $dizionario['fermata']['pickup']; ?></td>
                        <td><?php echo $dizionario['fermata']['dropoff']; ?></td>
                        <td><?php echo $dizionario['fermata']['interscambio']; ?></td>
                        <td><?php echo $dizionario['fermata']['black_list']; ?></td>
                        <td><?php echo $dizionario['fermata']['da_confermare']; ?></td>
                        <td><?php echo $dizionario['fermata']['webselling']; ?></td>
                        <td><?php echo $dizionario['generale']['stato']; ?></td>
                        <td><?php echo $dizionario['generale']['edita']; ?></td>
                        <td><?php echo $dizionario['autista']['elimina']; ?></td>
                    </tr>

                    <?php
                    // Query per selezionare le fermate legate alla tratta corrente
                    $sql = "SELECT * FROM RT_ElencoFermata WHERE TrattaId = $TrattaId AND OdcIdRef = $user->OdcId GROUP BY FermataId ORDER BY FermataPeso ASC, FermataNome ASC";
                    $ArrObject = $db->fetch_array($sql);
                    $i = 0;

                    // Ciclo che elenca tutte le fermate restituite dalla query
                    while ($i < sizeof($ArrObject)) {
                        $FermataId = $ArrObject[$i]['FermataId'];
                        $FermataNome = $ArrObject[$i]['FermataNome'];
                        $Comune = $ArrObject[$i]['Comune'];
                        $ComuneId = $ArrObject[$i]['ComuneId'];
                        $KmInizioTratta = $ArrObject[$i]['KmInizioTratta'];
                        $FermataPeso = $ArrObject[$i]['FermataPeso'];
                        $FermataStato = $ArrObject[$i]['Stato'];
                        $IsPickup = $ArrObject[$i]['IsPickup'];
                        $IsDropOff = $ArrObject[$i]['IsDropOff'];
                        $IsBlackList = $ArrObject[$i]['IsBlackList'];
                        $IsInterscambio = $ArrObject[$i]['IsInterscambio'];
                        $IsDaConfermare = $ArrObject[$i]['IsDaConfermare'];
                        $WebSelling = $ArrObject[$i]['WebSelling'];
                        $LineaId = $ArrObject[$i]['LineaId'];
                        ?>

                        <!-- Riga della tabella per ogni fermata -->
                        <tr class="rowBianca">
                            <td><span><?= $FermataPeso ?></span></td>
                            <td><span><?= $Comune ?></span></td>
                            <td><span><?= $FermataNome ?></span></td>
                            <td><span><?= $KmInizioTratta ?></span></td>
                            <td><span><?= ($IsPickup == 1) ? "SI" : "NO"; ?></span></td>
                            <td><span><?= ($IsDropOff == 1) ? "SI" : "NO"; ?></span></td>
                            <td><span><?= ($IsInterscambio == 1) ? "SI" : "NO"; ?></span></td>
                            <td><span><?= ($IsBlackList == 1) ? "SI" : "NO"; ?></span></td>
                            <td><span><?= ($IsDaConfermare == 1) ? "SI" : "NO"; ?></span></td>
                            <td><span><?= ($WebSelling == 1) ? "SI" : "NO"; ?></span></td>
                            <td><span><?= ($FermataStato) ? "attiva" : "disattiva"; ?></span></td>

                            <!-- Azione per modificare la fermata -->
                            <td>
                                <a title="edita" onclick="javascript:ExternalLoad('rt_fermata','fermata.php?do=edit&amp;FermataId=<?= $FermataId ?>');" href="#">
                                    <i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i>
                                </a>
                            </td>

                            <!-- Azione per eliminare la fermata -->
                            <td>
                                <a title="elimina" href="#" onclick="javascript:CancellaFermata(<?= $FermataId ?>);" title="<?= $dizionario['autista']['elimina']; ?>">
                                    <img alt="<?= $dizionario['autista']['elimina']; ?>" src='/images/delete_gray.gif' />
                                </a>
                            </td>
                        </tr>

                        <?php
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
            <!-- Fine tabella fermate -->

            <br />

            <!-- Pulsante per aggiungere una nuova fermata -->
            <div class="GestoreSedeAdd">
                <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_fermata','fermata.php?do=add&TrattaId=<?= $TrattaId ?>');" title="<?php echo $dizionario['fermata']['titolo_aggiungi']; ?>">
                    <i class="fa fa-plus" aria-hidden="true"></i> 
                    <?php echo $dizionario['fermata']['titolo_aggiungi']; ?>
                </a>
                <a class="accordion_link" style="cursor:pointer;"><u><i class="fa fa-plus" aria-hidden="true"></i> <?= $dizionario['fermata']['aggiornamento_massa'] ?></u></a>
            </div>
            <div class="accordion">
                
                
                <div class="accordion-content" style="display: none;">
                    <p style="padding-bottom: 0px"><?= $dizionario['fermata']['aggiornamento_massa_descrizione'] ?></p>
                        <div style="width: 1000px; background-color: #E4E4E4;">
                            <?php
                            $page->create_select($dizionario['tratta']['tratta'], "massTrattaId", "massTrattaId", "brain_campoForm", $arrTratta, -1, "TrattaId", "Tratta", array("multiple" => "multiple"), 1);
                            $page->create_select($dizionario['fermata']['pickup'], "massIsPickup", "massIsPickup", "brain_campoForm", $arr_is, -1, "ArrIsId", "ArrIs", null, 0);
                            $page->create_select($dizionario['fermata']['dropoff'], "massIsDropOff", "massIsDropOff", "brain_campoForm", $arr_is, -1, "ArrIsId", "ArrIs", null, 0);
                            $page->create_select($dizionario['fermata']['interscambio'], "massIsInterscambio", "massIsInterscambio", "brain_campoForm", $arr_is, -1, "ArrIsId", "ArrIs", null, 0);
                            $page->create_select($dizionario['fermata']['black_list'], "massIsBlackList", "massIsBlackList", "brain_campoForm", $arr_is, -1, "ArrIsId", "ArrIs", null, 0);
                            $page->create_select($dizionario['fermata']['da_confermare'], "massIsDaConfermare", "massIsDaConfermare", "brain_campoForm", $arr_is, -1, "ArrIsId", "ArrIs", null, 0);
                            $page->create_select($dizionario['fermata']['webselling'], "massWebSelling", "massWebSelling", "brain_campoForm", $arr_is, -1, "ArrIsId", "ArrIs", null, 0);
                            $page->create_select($dizionario['generale']['stato'], "massStato", "massStato", "brain_campoForm", $arr_stato, -1, "StatoId", "Stato", null, 0);
                            $page->create_button($dizionario['proviggione']['aggiorna'],"massAggiorna",$dizionario['proviggione']['aggiorna'],"brain_salva","button");
                            print("<br style=\"clear:both;\"/>"); 
                            ?>
                        </div>
                    
                </div>
            </div>
            
        </div>        
    </form>
    <?php
    // Chiudi la connessione al database
    $db->Close();   
}


function add($step) {
	include_once("tratta_wz_validator.php");  
 	global $HtmlCommon,$db,$tratta_wizard,$funzione_edit,$abilita_modifica;
	if (!$step) {
		$tratta_wizard=null;    
		unset($tratta_wizard);
		$_SESSION['TRATTA_WIZARD']=null;
		unset($_SESSION['TRATTA_WIZARD']);
		$step=1;
	}
	$mod=0;
	if (is_object($tratta_wizard)) {
	    $TrattaId=$tratta_wizard->Id;
	    $tratta_wizard->conn=$db;
	    $tratta_wizard->inizializzaDatiGenerali();
	    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
	    $Stato=$DatiGeneraliArr['Stato'];
	    $mod=1;
    	$abilita_modifica=true;
    	$HtmlCommon->html_titolo_pagina("Editazione tratta ".$DatiGeneraliArr['TrattaNome'],1,"rt_tratta","tratta.php");
	} else {        
		$mod=0;
        $abilita_modifica=false;
        $HtmlCommon->html_titolo_pagina("Aggiungi tratta",1,"rt_tratta","tratta_wz.php");
	}
	
	carica_menu_percorso($step,$mod);
 
	?>
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
 	<?       
 	
 	if ($step==1)
		add_step_tratta();
	elseif ($step==2)
		add_step_fermate_orari();	

	$db= new Database();
    $db->connect();

	?>
	        
	</div>
	<?php 
}

function spara_pulsanti_wizard($steptogo) {
    // Accesso alla variabile globale $funzione_edit
    global $funzione_edit;

    // Se la funzione di modifica ($funzione_edit) è attiva, chiama spara_pulsanti_edit()
    if ($funzione_edit) {
        spara_pulsanti_edit($steptogo);
    } else {
        // Se la funzione di modifica non è attiva, crea un nuovo oggetto Form
        if (!$funzione_edit) {
            $page = new Form();
        }
        ?>

        <!-- Contenitore per i pulsanti di submit -->
        <div class="divSubmit">
            <!-- Crea il pulsante "Salva" che avanza al prossimo step -->
            <?php $page->create_button("Salva", "Salva", "avanti", "brain_salva", "submit"); ?>

            <!-- Se c'è uno step precedente, crea anche il pulsante "Indietro" -->
            <?php  
            if ($steptogo > 0) {
                $page->create_button("indietro", "indietro", "indietro", "brain_back", "button");
            }
            ?>

            <!-- Link per annullare la compilazione e tornare alla home -->
            <a href="javascript:void(0);" onclick="loadMainContent('rt_tratta','tratta.php',this);" title="Home" class="brain_annulla">
                Annulla
            </a>

            <!-- Select nascosta per gestire la tracklist (se necessaria) -->
            <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select>

            <!-- Fine del form -->
            </form>
        </div>

        <?php
    }
}

function spara_pulsanti_edit($steptogo) {
    // Variabile globale usata per abilitare la modifica
    global $abilita_modifica;

    // Crea un nuovo oggetto Form
    $page = new Form();
    ?>

    <!-- Contenitore per i pulsanti di submit -->
    <div class="divSubmit">
        <!-- Crea il pulsante "Salva" per salvare le modifiche -->
        <?php $page->create_button("Salva", "Salva", "salva", "brain_salva", "submit"); ?>

        <!-- Link per annullare la modifica e tornare alla home della mediazione -->
        <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla">
            Annulla
        </a>

        <!-- Select nascosta per eventuale tracciamento (se necessaria) -->
        <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select>

        <!-- Fine del form -->
        </form>
    </div>

    <?php
}




if (is_object($user)) {

    /*  
    ID - FUNZIONE
    1    Lista
    2    Aggiunta
    3    Cancellazione
    4    Modifica
    5    Esportazione
    6    Importazione
    7    Stampa
    */

    // Connessione al database
    $db = new Database();
    $db->connect();
    $user->conn = $db;

    // Se esiste l'oggetto $tratta_wizard, assegno la connessione al database
    if (is_object($tratta_wizard)) {
        $tratta_wizard->conn = $db;
    }

    // Ottengo i permessi dell'utente per il modulo corrente
    $permessi = $user->get_permessi_modulo($ModuloId);

    // Verifica se ci sono permessi assegnati
    if (sizeof($permessi) > 0) {
        // Controllo l'azione richiesta
        $do = $_REQUEST['do'] ? $_REQUEST['do'] : ''; // Se 'do' non è settato, lo imposto come stringa vuota
        
        // Switch per gestire diverse azioni in base al valore di $do
        switch ($do) {

            case "add": // Aggiunta di un nuovo record
                $FunzioneId = 2; // ID funzione per "Aggiunta"
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                
                // Controllo se l'utente ha il permesso per eseguire l'azione
                if (sizeof($permesso)) {
                    add($_REQUEST['step']); // Esegui funzione di aggiunta
                } else {
                    $errore->stampa_errore(2); // Mostra errore di permesso negato
                }
                break;

            case "edit": // Modifica di un record esistente
                $FunzioneId = 4; // ID funzione per "Modifica"
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                
                // Controllo permesso per la modifica
                if (sizeof($permesso)) {
                    edit($_REQUEST['TrattaId']); // Esegui funzione di modifica
                } else {
                    $errore->stampa_errore(2); // Mostra errore di permesso negato
                }
                break;

            default: // Azione predefinita: mostrare la lista
                $FunzioneId = 1; // ID funzione per "Lista"
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                // Controllo permesso per visualizzare la lista
                if (sizeof($permesso)) {
                    show_list(); // Esegui funzione per mostrare la lista
                } else {
                    $errore->stampa_errore(2); // Mostra errore di permesso negato
                }
                break;
        }
    } else {
        // Se l'utente non ha permessi per il modulo, mostra errore
        $errore->stampa_errore(1); // Errore di accesso non autorizzato
    }

} else {
    // Se l'utente non è loggato, reindirizza alla pagina di logout
    header("Location: /logout.php");
}

?>