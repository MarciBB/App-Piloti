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



include_once($classespath_."class.Scontistica.php");


global $ModuloId;
$ModuloId=35;// modulo base mediazione




global $user;
global $listino_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$listino_wizard=null;










function show_list()
{
   
global $user,$HtmlCommon,$ModuloId, $dizionario;
$HtmlCommon->html_titolo_pagina("Gestione listini",0,"","");
$HtmlCommon->html_titolo_box("Gestione listini");
$db= new Database();
$db->connect();



include_once("listino_validator.php");           
include_once("listino_datatable.php");

global $user,$HtmlCommon,$db,$ModuloId;    
 

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
//if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tratta','tratta_wz.php?do=add','aggiungi percorso');

?>   
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
            
            	<tr class="brain_tabellaTr">
                    <th width="10%"><?=$dizionario['generale']['stato']?></th>
			<th width="%75"><?=$dizionario['listino']['listino']?></th>
			<th width="10%"><?=$dizionario['generale']['peso']?></th>
                      <th width="5%"><?=$dizionario['generale']['esita']?></th>
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
			<td colspan="4" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['listino']['caricamento_in_corso']?></td>
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


function edit($ListinoId)
{
    
    global $listino_wizard,$db,$user;   
    $listino_wizard=new Scontistica($ListinoId);
  
    
    $_SESSION['LISTINO_WIZARD']=serialize($listino_wizard);
    add(1);
}


function carica_menu_listini($step_corrente,$mod)
{
global $abilita_modifica,$listino_wizard,$db, $dizionario;
//$listino_wizard->conn=$db;
//$menu=$listino_wizard->getMenuWizard();

   
$menu=array(
    1=>$dizionario['listino']['menu_variazioni'],
    2=>$dizionario['listino']['menu_prezzi']
  
   //3=>"Tariffe servizi aggiuntivi"
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
                                  if (true)
                                  {
                                     
                                      
                                   ?>
                                             <a href="javascript:void(0);" onclick="loadMediazioneStep('rt_scontistica','listino.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
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
 
 <?
    
    
    
}

function add_step_tratta()
{
 $step_corrente=1;
 
global $listino_wizard,$user,$db, $dizionario;

 $page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create"; 

        $TrattaId=0;
        
if (is_object($listino_wizard) and ($listino_wizard->Id))
{
  
    $TrattaId=$listino_wizard->Id;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $listino_wizard->conn=$db;
    $listino_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$listino_wizard->DatiGenerali;
    $_SESSION['LISTINO_WIZARD']=serialize($listino_wizard);
    
   // print_r($row);
    if($DatiGeneraliArr['ListinoId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}    




?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			<div class="brain_formModifica">
                            <? if ($action=="create") { ?>
                            <h2>Informazioni generali</h2>
                            <? } else { ?>
                            <h2><span class="brain_colorh2"><?=$listino_wizard->DatiGenerali['TrattaNome']?></span></h2>
                         <? } ?>
                           <div class="brain_data-content">                  
                            
				
                                
                               <? 
                              form_tipo1($azione, $TrattaId);
                            
                          ?>
                               
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}




function form_tipo1($azione,$AnagraficaId)
{
global $HtmlCommon,$db,$user,$listino_wizard, $dizionario;    
/*$db= new Database();
$db->connect();*/
$page=new Form();
$dt=new DT();





$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

$TrattaTipo=new TrattaTipo();
$TrattaTipo->conn=$db;
$arr_tratta_tipo=$TrattaTipo->getAll();

$Mezzo=new Mezzo();
$Mezzo->conn=$db;
$arr_mezzo=$Mezzo->getAll();

$TrattaDirezione=new TrattaDirezione();
$TrattaDirezione->conn=$db;
$arr_tratta_direzione=$TrattaDirezione->getAll();

$LineaNome="";
$PercorsoPeso="";
$PercorsoStato=0;

if ($azione=="edit")
{    
    
    $DatiGeneraliArr=$listino_wizard->DatiGenerali;
    $LineaNome=$DatiGeneraliArr['LineaNome'];
    $LineaArea=$DatiGeneraliArr['LineaArea'];
    $LineaPeso=$DatiGeneraliArr['LineaPeso'];
     $LineaStato=$DatiGeneraliArr['Stato'];
    
}

                $page->create_textbox_hidden("action","create");
                $page->create_textbox($dizionario['generale']['linea'],"LineaNome","Linea[LineaNome]",$LineaNome,1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                $page->create_textbox($dizionario['linea']['area'],"Area","Linea[LineaArea]",$LineaArea,1,"brain_campoForm campiformBig",array("class"=>"'required'"));
                $page->create_textbox($dizionario['generale']['peso'],"Peso","Linea[LineaPeso]",$LineaPeso,1,"brain_campoForm",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['generale']['stato'],"Linea[Stato]","StatoId","brain_campoForm",$arr_stato,$LineaStato,"StatoId","Stato",
                array("class"=>"'required'"),1);
             
    
    
        
    ?>

        
<div id="elenco_comuni"></div>
    <?
    print("<br style=\"clear:both;\"/>");
 
 
}



function add_step_listino_prezzi() {

	$step_corrente=1;
 
	global $listino_wizard,$user,$db, $dizionario;

	$page=new Form();
	$dt=new DT();
	$LineaId=$listino_wizard->Id;
	include_once("listino_validator.php");
	?>

	<form id="application_form" name="application_form"  method="post" action="#">
		<?php
		   $page->create_textbox_hidden("step_corrente",$step_corrente);
		   $page->create_textbox_hidden("step_successivo",0);
		   $page->create_textbox_hidden("action","create");
		?>
                            
		<div class="brain_formModifica formGestoreEdita">
			<h2><span class="brain_colorh2"><?php $dizionario['listino']['def_prezzi_variazione']?></span></h2>
			<table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
				<tbody>
					<tr class="rowIntestazione">
						<td></td>
                                 
						<?php
                        $sql="Select * from RT_TipologiaBiglietto where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId order by TipoTour ASC, OccupaPosto DESC, TipologiaBigliettoPeso asc";

                        $ArrObjectTB = $db->fetch_array($sql);
                        $i=0;
                        
						?>
						<td><?=$dizionario['tipo_big']['tipo_biglietto']?></td>
					</tr>
					<?php
                    $sql="Select * from RT_Scontistica where Stato=1 and Cancella=0 and  OdcIdRef=$user->OdcId";
                                            
					$ArrObject = $db->fetch_array($sql);
					$i=0;
					$tratta_old=0;
                    while ($i< sizeof($ArrObject)) {
						$ClasseId=$ArrObject[$i]['ListinoId'];
						$ClasseNome=$ArrObject[$i]['ListinoNome'];
						$Da=$ArrObject[$i]['DaPax'];
						$A=$ArrObject[$i]['APax'];

						?>
                        <tr>
							<td style="vertical-align: top; padding:10px;"><?=$ClasseNome?></td>
                            <?php
                            $n=0;
							?>
							<td>
							<?php
                            while ($n< sizeof($ArrObjectTB)) {
                                $BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];   
                                $prezzo="";
                                $sql="Select * from RT_ScontisticaBiglietto where BigliettoId=$BigliettoId and ListinoId=$ClasseId and OdcIdRef=$user->OdcId";
                                $row1 = $db->query_first($sql);
                                
                                if (!empty($row1['ListinoBigliettoId']))
                                $prezzo=str_replace(".",",",$row1['Prezzo']);
                                $prezzo=$row1['Prezzo'];
                                 
								?>
								<div style="float:left; width:100px; height:50px; margin:5px;">
									<label for="ListinoBiglietto['<?=$ClasseId."_".$BigliettoId?>']"><?=$ArrObjectTB[$n]['TipologiaBiglietto']?></label><br>
									<input  type="text" id="ListinoBiglietto['<?=$ClasseId."_".$BigliettoId?>']" name="ListinoBiglietto['<?=$ClasseId."_".$BigliettoId?>']" value="<?=$prezzo?>" SIZE="7" MAXLENGTH="7">
								</div>
								<?php
                                 
                                $n++;
                            }
                            ?>
							</td>
						</tr>
                        <?php  

                        $i++;
					} ?>
				</tbody>
                         </table>
                        </div>
                           <div class="divSubmit">
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
   
   
				
</div>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}




function add_step_listino() {

	$step_corrente=2;
 
	global $listino_wizard,$user,$db, $dizionario;

	$page=new Form();
	$dt=new DT();

	$listino_wizard=$listino_wizard->Id;

	include_once("listino_validator.php");
	?>

	<form id="application_form" name="application_form"  method="post" action="#">
		<?
		   $page->create_textbox_hidden("step_corrente",$step_corrente);
		   $page->create_textbox_hidden("step_successivo",$step_corrente+1);
		?>
                            
                  
		<div class="brain_formModifica formGestoreEdita">
			<h2><?=$dizionario['listino']['classi_variazioni_intervallo']?></h2>
                       
			<br />
			<br />
			<div class="GestoreSedeAdd">
				<a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=add&ListinoId=<?=$ListinoId?>');" title="aggiungi listino"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['listino']['aggiungi_classe_scontistica']?></a>
			</div>
                            
			<br />
			<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
				<tbody>
					<tr class="rowIntestazione">
						<td><?=$dizionario['generale']['peso']?></td>
						<td><?=$dizionario['listino']['variazione']?></td>
						<td><?=$dizionario['listino']['attiva_dal']?></td>
						<td><?=$dizionario['listino']['attiva_al']?></td>
						<td><?=$dizionario['listino']['da_pax']?></td>
						<td><?=$dizionario['listino']['a_pax']?></td>
						<td><?=$dizionario['listino']['n_corse']?></td>
						<td><?=$dizionario['generale']['stato']?></td>
						<td><?=$dizionario['generale']['edita']?></td>
						<td><?=$dizionario['listino']['associa_corse']?></td>
					</tr>
					<?php
					$sql="Select * from RT_Scontistica where OdcIdRef=$user->OdcId order by ListinoPeso asc";
						 
					$ArrObject = $db->fetch_array($sql);
					$i=0;
					while ($i< sizeof($ArrObject)) {
						$ListinoId=$ArrObject[$i]['ListinoId'];
						$ListinoNome=$ArrObject[$i]['ListinoNome'];
						$ListinoPeso=$ArrObject[$i]['ListinoPeso'];
						$ListinoStato=$ArrObject[$i]['Stato'];
						$AttivaDal=$ArrObject[$i]['AttivaDal'];
						$AttivaAl=$ArrObject[$i]['AttivaAl'];
						$Da=$ArrObject[$i]['DaPax'];
						$A=$ArrObject[$i]['APax'];
						$NumeroCorseAttive=$ArrObject[$i]['NumeroCorseAttive'];
						?>
						<!-- QUI L'ELENCO DELLE FERMATE -->
						<tr class="rowBianca">
							<td><span><?=$ListinoPeso?></span></td>
							<td><span><?=$ListinoNome?></span></td>
							<td><span><?=$AttivaDal?></span></td>
							<td><span><?=$AttivaAl?></span></td>
							<td><span><?=$Da?></span></td>
							<td><span><?=$A?></span></td>
							<td><span><?=$NumeroCorseAttive?></span></td>
							<td><span>
								<?
								if ($ListinoStato)
									print($dizionario['generale']['attivo']);
								else
									print($dizionario['generale']['disattivo']);    
								?>
							</span></td>
							<td><a title="edita" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=edit&amp;ListinoId=<?=$ListinoId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
							<td><a title="Corse" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=addPromozioneCorsa&amp;ListinoId=<?=$ListinoId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
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
				<a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=add&TrattaId=<?=$TrattaId?>');" title="aggiungi classe di variazione prezzi"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['listino']['aggiungi_classe_scontistica']?></a>
			</div>
		</div>                
	</form>
	<?php
	$db->Close();   
}


function add($step) {
	include_once("listino_validator.php");  
 
	global $HtmlCommon,$db,$listino_wizard,$funzione_edit,$abilita_modifica, $dizionario;

	if (!$step) {
		$listino_wizard=null;    
		unset($listino_wizard);
		$_SESSION['LISTINO_WIZARD']=null;
		unset($_SESSION['LISTINO_WIZARD']);
		$step=1;
	}
	$mod=0;
	if (is_object($listino_wizard)) {
		$LineaId=$listino_wizard->Id;
		$listino_wizard->conn=$db;
		$listino_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr=$listino_wizard->DatiGenerali;
		$Stato=$DatiGeneraliArr['Stato'];
		$mod=1;
		$abilita_modifica=true;
		$HtmlCommon->html_titolo_pagina($dizionario['listino']['titolo_variazione_intervallo']." ".$DatiGeneraliArr['ListinoNome'],0,"rt_scontistica","listino.php");
	} else {
		$mod=0;
        $abilita_modifica=false;
        $HtmlCommon->html_titolo_pagina($dizionario['listino']['titolo_variazione_intervallo'],0,"rt_scontistica","listino.php");
	}    
    carica_menu_listini($step,$mod);
 
	?>
		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
	<?php       
	
	if ($step==1)
		add_step_listino();
	elseif ($step==2)
		add_step_listino_prezzi();
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
    if (is_object($listino_wizard))
        $listino_wizard->conn=$db;
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
                                            add($_REQUEST['step']);
                                        else
                                           $errore->stampa_errore(2);
                                        
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                               
				case "edit":
				
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                
                                        if (sizeof($permesso))
                                           edit($_REQUEST['ListinoId']);
                                        else
                                            $errore->stampa_errore(2);
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "cerca":
				
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
                                
                                        if (sizeof($permesso))
                                          cerca_mediatore();
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
// se l'utente non ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¨ loggato
else {
header("Location: /logout.php");
}
?>