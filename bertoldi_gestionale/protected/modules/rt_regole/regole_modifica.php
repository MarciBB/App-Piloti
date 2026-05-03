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

include_once($classespath_."class.RegolaModifica.php");

global $ModuloId;
$ModuloId = 44;

global $user;
global $regolaModifica_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$regolaModifica_wizard = null;


function show_list() {
   
	global $user,$HtmlCommon,$ModuloId, $dizionario;
	$HtmlCommon->html_titolo_pagina($dizionario['regole']['titolo_gestione_modifica'],0,"","");
	$HtmlCommon->html_titolo_box($dizionario['regole']['titolo_gestione_modifica']);
	$db = new Database();
	$db->connect();
	include_once("regole_modifica_validator.php");           
	include_once("regole_modifica_datatable.php");
	global $db;    
 

	$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);

	?>   
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		    <thead>
            	<tr class="brain_tabellaTr">
                    <th width="10%"><?=$dizionario['generale']['stato']?></th>
					<th width="%60"><?=$dizionario['regole']['regola_modifica']?></th>
					<th width="10%"><?=$dizionario['regole']['giorni_prima']?></th>
					<th width="10%"><?=$dizionario['regole']['ore_prima']?></th>
					<th width="10%"><?=$dizionario['regole']['tipo_modifica']?></th>
                    <th width="5%"><?=$dizionario['generale']['edita']?></th>
				</tr>
            
				<tr class="brain_tabellaFilter">
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
					<td colspan="5" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
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


function edit() {
    global $regolaModifica_wizard, $db, $user;   
    
    add(1);
}


function carica_menu_provvigioni($step_corrente,$mod) {
	global $abilita_modifica,$regolaModifica_wizard,$db, $dizionario;
   
	$menu=array(
	    1=>$dizionario['regole']['menu_regole_modifica'],
	    2=>$dizionario['regole']['menu_penalita']
	    );
    ?>
    <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
		<ul>
			<?
            $contamenu=1;
            while ($contamenu<=2) {
            	$class1 = "";
                $class2 = "";
                if ($contamenu == $step_corrente) {
                	$class1="sel";
                    $class2="brain_firstspan sel";
                }
                $StatoStep="";
                if ( ($contamenu<=2) or (($contamenu>2) and ($mod))) { ?>
                            
                	<li class="<?=$class1?>">
                    	<span class="<?=$class2?>">
                        	<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_regole','regole_modifica.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
                        </span>
                    </li>
                 <?
                 }
                 $contamenu++;
			}
            ?>
			</ul>
		</div>
	 <?    
}


function add_step_provvigione_prezzi() {

	$step_corrente=1;
	global $regolaModifica_wizard,$user,$db, $dizionario;

	$page=new Form();
 	$dt=new DT();

	include_once("regole_modifica_validator.php");
	?>
	<form id="application_form" name="application_form"  method="post" action="#">
    <?php
    	$page->create_textbox_hidden("step_corrente",$step_corrente);
        $page->create_textbox_hidden("step_successivo",2);
        $page->create_textbox_hidden("action","create");
    ?>
        <div class="brain_formModifica formGestoreEdita">
        <h2><span class="brain_colorh2"><?=$dizionario['regole']['def_penalita_modifica']?></span></h2>
        <?
        	// ciclo per linea
            $sql="Select * from RT_ElencoLinea where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId";
            $ArrObjectLinea = $db->fetch_array($sql);
            $ii=0;
            while ($ii< sizeof($ArrObjectLinea)) {
            	$LineaNome=$ArrObjectLinea[$ii]['LineaNome'];
                $LineaId=$ArrObjectLinea[$ii]['LineaId'];
                
                $sql="Select * from RT_ModificaRegola where Stato=1 and Cancella=0 order by GiorniPrima desc, OrePrima desc";
                $ArrObjectTB = $db->fetch_array($sql);
                ?>
                <h3><?=$LineaNome?></h3>
                <table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                	<tbody>
                    	<tr class="rowIntestazione">
                        	<td><?=$dizionario['regole']['regola']?></td>
                        	<td><?=$dizionario['generale']['tipo']?></td>
                        	<td><?=$dizionario['regole']['su_modifica']?></td>
                        	<td style="width:15%; text-align: center;"><?=$dizionario['regole']['perc']?></td>
                        	<td style="width:15%; text-align: center;"><?=$dizionario['regole']['fisso']?></td>
 						</tr>
 						
 						  <?
 						foreach ($ArrObjectTB as $key => $row){
							?><tr><?php 
							$TBId=$row['ModificaRegolaId'];
							$TBNome=$row['NomeRegola'];

							?>
                            <td><?=$TBNome?> </td>
                            <td>
	                            <?php if($row['TipoPrenotazione'] == 'I'){
	                            	echo $dizionario['regole']['internazionale'];
	                            } else if($row['TipoPrenotazione'] == 'N'){
									echo $dizionario['regole']['nazionale'];
								} else {
									echo $dizionario['regole']['int_naz'];
								}?> 
							</td>
							<td>
	                            <?php if($row['TipoModifica'] == 'N'){
	                            	echo $dizionario['regole']['nome'];
	                            } else if($row['TipoModifica'] == 'D'){
									echo $dizionario['regole']['data'];
								} else if($row['TipoModifica'] == 'I'){
									echo $dizionario['regole']['itinerario'];
								}?> 
							</td>
                            <?  
							$sql="Select * from RT_ModificaPenale where ModificaRegolaId = ".$row['ModificaRegolaId']." and LineaId = $LineaId";
							$ArrObject = $db->fetch_array($sql);
							if(count($ArrObject)>0){
								?><td style="width:15%; text-align: center;"><input class="numberDE" style="float: none;" type="text" name="ModificaPercentuale[<?=$LineaId."_".$TBId?>]" value="<?=$ArrObject[0]['Percentuale']?>" SIZE="6" MAXLENGTH="6"></td>
									<td style="width:15%; text-align: center;"><input class="numberDE" style="float: none;" type="text" name="ModificaFisso[<?=$LineaId."_".$TBId?>]" value="<?=$ArrObject[0]['Fisso']?>" SIZE="6" MAXLENGTH="6"></td><?php 
							} else {
								?><td style="width:15%; text-align: center;"><input class="numberDE" style="float: none;" type="text" name="ModificaPercentuale[<?=$LineaId."_".$TBId?>]" value="" SIZE="6" MAXLENGTH="6"></td>
									<td style="width:15%; text-align: center;"><input class="numberDE" style="float: none;" type="text" name="ModificaFisso[<?=$LineaId."_".$TBId?>]" value="" SIZE="6" MAXLENGTH="6"></td><?php 
							}
							?></tr><?php 
						}
                      	?>	
 						
                       </tbody>
					</table>
                    	<?
						$ii++;
						}
						?>     
                        </div>
						<div class="divSubmit">        
        					<?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
						</div>
				</form>
		
 
				<?
    $db->Close();   
}




function add_step_provvigione() {

	$step_corrente = 2;
	global $regolaModifica_wizard,$user,$db, $dizionario;
	$page = new Form();
	$dt = new DT();

	include_once("regole_modifica_validator.php");
	
	?>
	<form id="application_form" name="application_form"  method="post" action="#">
    	<?php
        $page->create_textbox_hidden("step_corrente",$step_corrente);
        $page->create_textbox_hidden("step_successivo",$step_corrente+1);
        ?>
        <div class="brain_formModifica formGestoreEdita">
			<h2>Regole di Modifica</h2>
            <br />
            <br />
            <div class="GestoreSedeAdd">
            	<a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_regole_classe','regole_modifica_classe.php?do=add');" title="aggiungi Regola Modifica"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['regole']['aggiungi_regola_modifica']?></a>
			</div>
			<br />
            <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
            	<tbody><tr class="rowIntestazione">
                	<td><?=$dizionario['regole']['nome_regola']?></td>
                    <td><?=$dizionario['regole']['giorni_prima']?></td>
                    <td><?=$dizionario['regole']['ore_prima']?></td>
                    <td><?=$dizionario['regole']['tipo_percorso']?></td>
                    <td><?=$dizionario['regole']['tipo_modifica']?></td>
                    <td><?=$dizionario['generale']['stato']?></td>
                    <td><?=$dizionario['generale']['edita']?></td>
                </tr>
				<?
                        $sql="Select * from RT_ModificaRegola order by GiorniPrima desc, OrePrima desc";
                        $ArrObject = $db->fetch_array($sql);
                        $i=0;
                          while ($i< sizeof($ArrObject))
                            {  
                            ?>
                             
                                <tr class="rowBianca">
                                    <td><span><?=$ArrObject[$i]['NomeRegola']?></span></td>
                                    <td><span><?=$ArrObject[$i]['GiorniPrima']?></span></td>
                                    <td><span><?=$ArrObject[$i]['OrePrima']?></span></td>
                                    <td><span>
                                    	<?php if($ArrObject[$i]['TipoPrenotazione'] == 'I'){
                                    		echo $dizionario['regole']['internazionale'];
                                    	} else if($ArrObject[$i]['TipoPrenotazione'] == 'N'){
											echo $dizionario['regole']['nazionale'];
                            			} else {
                            				echo $dizionario['regole']['int_naz'];
										}?>
                                    </span></td>
                                    <td><span>
                                    	<?php if($ArrObject[$i]['TipoModifica'] == 'N'){
                                    		echo $dizionario['regole']['nome'];
                                    	} else if($ArrObject[$i]['TipoModifica'] == 'D'){
											echo $dizionario['regole']['data'];	
                            			} else if($ArrObject[$i]['TipoModifica'] == 'I'){
                            				echo $dizionario['regole']['itinerario'];
										}?>
                                    </span></td> 
                                    <td><span>
                                    <?
                                    if ($ArrObject[$i]['Stato'] == 1)
                                    	print($dizionario['generale']['attivo']);
                                    else
                                    	print($dizionario['generale']['disattivo']);    

                                    ?>
                                    </span></td>
                                         <td><a title="edita" onclick="javascript:ExternalLoad('rt_regole_classe','regole_modifica_classe.php?do=edit&amp;ModificaRegolaId=<?=$ArrObject[$i]['ModificaRegolaId']?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
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
                             <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_regole_classe','regole_modifica_classe.php?do=add');" title="aggiungi Regola Modifica"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['regole']['aggiungi_regola_modifica']?></a>
                        </div>

                        </div>             
		</form> 
	<?
 	$db->Close();   
}


function add($step) {
	include_once("regole_modifica_validator.php");  
 	global $HtmlCommon,$db,$regolaModifica_wizard,$funzione_edit,$abilita_modifica, $dizionario;

	if (!$step) {
		$regolaModifica_wizard=null;    
		unset($regolaModifica_wizard);
		$_SESSION['REGOLAMODIFICA_WIZARD']=null;
		unset($_SESSION['REGOLAMODIFICA_WIZARD']);
		$step=1;
	}
	$mod=0;
	$GestoreStato=-1;
	if (is_object($regolaModifica_wizard)) {
	    $ProvvigioneId = $regolaModifica_wizard->Id;
	    $regolaModifica_wizard->conn = $db;
	    $regolaModifica_wizard->inizializzaDatiGenerali();
	    $DatiGeneraliArr = $regolaModifica_wizard->DatiGenerali;
	    $Stato=$DatiGeneraliArr['Stato'];
	    $mod=1;
	    $abilita_modifica=true;
	    $HtmlCommon->html_titolo_pagina($dizionario['regole']['titolo_regola_modifica']." ".$DatiGeneraliArr['NomeRegola'],0,"rt_regole","regole_modifica.php");
	} else {
        $mod=0;
        $abilita_modifica=false;
        $HtmlCommon->html_titolo_pagina($dizionario['regole']['titolo_regola_modifica'],0,"rt_regole","regole_modifica.php");
	}
    carica_menu_provvigioni($step,$mod);
	 ?>
		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
	 <?       
 
 	if ($step==1)
 		add_step_provvigione();
	elseif ($step==2)
		add_step_provvigione_prezzi();

	?>    
		</div>
	<?  
}

function spara_pulsanti_wizard_box(){
	global $dizionario;
	$page=new Form();

	?>
	<div class="divSubmit">
    <?php  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
         <a href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi" class="brain_annulla"><?=$dizionario['generale']['chiudi']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>			
	</div>
    <?
}

function spara_pulsanti_wizard($steptogo)
{

	global $funzione_edit, $dizionario;

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
    
         <a href="javascript:void(0);" onclick="loadMainContent('mediatore','mediatore.php',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
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
    if (is_object($regolaModifica_wizard))
        $regolaModifica_wizard->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
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
                    	add($_REQUEST['step']);
                    else
                    	$errore->stampa_errore(2);      
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					break;           
				case "edit":
					$FunzioneId = 1;
                    $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                
 					if (sizeof($permesso))
                    	edit();
					else
                    	$errore->stampa_errore(2);
					break;
                                
                case "cerca":
					$FunzioneId = 1;
                    $permesso = $user->ControllModuloFunzionePermesso(2,$FunzioneId);
                    if (sizeof($permesso))
                    	cerca_mediatore();
                    else
                    	$errore->stampa_errore(2);            
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					break;
					
				default:
					$FunzioneId = 1;
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
} else {
	header("Location: /logout.php");
}




?>