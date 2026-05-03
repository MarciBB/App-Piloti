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

include_once($classespath_."class.Listino.php");


global $ModuloId;
$ModuloId=3;// modulo base mediazione




global $user;
global $listino_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$listino_wizard=null;




function show_list() {
   
	global $user,$HtmlCommon,$ModuloId, $dizionario, $db;
	$HtmlCommon->html_titolo_pagina($dizionario['listino']['titolo_gestione'],0,"","");
	$HtmlCommon->html_titolo_box($dizionario['listino']['titolo_gestione']);
	$db= new Database();
	$db->connect();

	include_once("listino_validator.php");           
	include_once("listino_datatable.php"); 

	$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
	//if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tratta','tratta_wz.php?do=add','aggiungi percorso');

	?>   
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    	<thead>
        	<tr class="brain_tabellaTr">
				<th width="10%"><?=$dizionario['generale']['stato']?></th>
				<th width="%75"><?=$dizionario['listino']['listino']?></th>
				<th width="10%"><?=$dizionario['generale']['peso']?></th>
				<th width="5%"><?=$dizionario['generale']['edita']?></th>
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
				<td colspan="4" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
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
    $listino_wizard=new Listino($ListinoId);
  
    
    $_SESSION['LISTINO_WIZARD']=serialize($listino_wizard);
    add(1);
}


function carica_menu_listini($step_corrente,$mod) {
	global $abilita_modifica,$listino_wizard,$db, $dizionario;
	$menu=array(
//     1=>$dizionario['listino']['menu_listino'],
    1=>$dizionario['listino']['menu_prezzo'],
//     3=>$dizionario['listino']['menu_km'],
	2=>$dizionario['listino']['menu_servizi']
    
  
   //3=>"Tariffe servizi aggiuntivi"
    );
    ?>
    <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
		<ul>
        <?
        $contamenu=1;
        while ($contamenu<=2){
			$class1="";
			$class2="";
                          
			if ($contamenu==$step_corrente) {
				$class1="sel";
				$class2="brain_firstspan sel";
            }
                             
			$StatoStep="";
                            
			if ( ($contamenu<=4) or (($contamenu>4) and ($mod))) { ?>
                            
				<li class="<?=$class1?>">
					<span class="<?=$class2?>">
                		<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_listino','listino.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
                    </span>
                </li>
			<? }
            $contamenu++;
		}
        ?>
		</ul>
	</div> 
 <?
}

function add_step_tratta()
{
 $step_corrente=1;
 
global $listino_wizard,$user,$db;

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


function add_step_listino_prezzi()
{

$step_corrente=1;
 
global $listino_wizard,$user,$db, $dizionario;

$page=new Form();
 $dt=new DT();

$LineaId=$listino_wizard->Id;
 

include_once("listino_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",3);
                           $page->create_textbox_hidden("action","create");
                        ?>
                            
                  
			<div class="brain_formModifica formGestoreEdita">
                             <h2><span class="brain_colorh2"><?=$dizionario['listino']['definizione_mess']?></span></h2>
                         <table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                                    <tbody>
                                    <tr class="rowIntestazione">
                                      <td></td>
                                 
                                 <?
                        $sql="Select * from RT_TipologiaBiglietto where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId and OccupaPosto = 1 order by TipologiaBigliettoPeso asc ";
                       
                        
                        $ArrObjectTB = $db->fetch_array($sql);
                        $i=0;
                          while ($i< sizeof($ArrObjectTB))
                            {
                              $TBId=$ArrObjectTB[$i]['TipologiaBigliettoId'];
                              $TBNome=$ArrObjectTB[$i]['TipologiaBiglietto'];
                            ?>
                             <td><?=$TBNome?> </td>
                              
                            <?  
                              $i++;
                          }
                      ?>
                                    </tr>
                       <?
                        $sql="Select * from RT_Listino where Stato=1 and Cancella=0 and  OdcIdRef=$user->OdcId";
                      
                        
                        $ArrObject = $db->fetch_array($sql);
                        $i=0;
                        $tratta_old=0;
                          while ($i< sizeof($ArrObject))
                            {
                               $ClasseId=$ArrObject[$i]['ListinoId'];
                               $ClasseNome=$ArrObject[$i]['ListinoNome'];
                              
                              
                               ?>
                                    
                                    
                             <tr>
                                 <td><?=$ClasseNome?></td>
                             <?
                             $n=0;
                             while ($n< sizeof($ArrObjectTB))
                             {
                                $BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];   
                                $prezzo="";
                                $sql="Select * from RT_ListinoBiglietto where BigliettoId=$BigliettoId and ListinoId=$ClasseId and OdcIdRef=$user->OdcId";
                                $row1 = $db->query_first($sql);
                                
                                if (!empty($row1['ListinoBigliettoId']))
                                $prezzo=str_replace(".",",",$row1['Prezzo']);
                               
                            //  echo($sql);
                                 
                                 
                             ?>
                              <td><input class="numberDE" type="text" name="ListinoBiglietto['<?=$ClasseId."_".$BigliettoId?>']" value="<?=$prezzo?>" SIZE="7" MAXLENGTH="7"> </td>
                             
                              <?
                                 
                                 $n++;
                             }
                             ?>
                             
                             
                             
                             </tr>
                            <?  
                              $i++;
                          }
                       
                       
                       ?>
                                    
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


function add_step_listino_fascekm()
{

$step_corrente=3;
 
global $listino_wizard,$user,$db, $dizionario;

$page=new Form();
 $dt=new DT();

$listino_wizard=$listino_wizard->Id;
 

include_once("listino_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",4);
                        ?>
                            
                  
			<div class="brain_formModifica formGestoreEdita">
                             <h2><?=$dizionario['listino']['listini_per_fasce_km']?></h2>
                       
                             <br />
                              <br />
                        <div class="GestoreSedeAdd">
                             <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_listino_fascekm','listino_fascekm.php?do=add&ListinoId=<?=$ListinoId?>');" title="aggiungi listino"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['listino']['aggiungi_fasce_km']?></a>
                        </div>
                            
                              <br />
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                                    <tbody><tr class="rowIntestazione">
                                    <td><?=$dizionario['generale']['da']?></td>
                                    <td><?=$dizionario['generale']['a']?></td>
                                    <td><?=$dizionario['listino']['molt']?></td>
                                    <td><?=$dizionario['generale']['edita']?></td>
                                    </tr>

                         <?
                        $sql="Select * from RT_ListinoMoltiplicatore";
                     
                        
                        $ArrObject = $db->fetch_array($sql);
                        $i=0;
                          while ($i< sizeof($ArrObject))
                            {
                              $ListinoId=$ArrObject[$i]['ListinoMoltiplicatore'];
                              $KmDa=$ArrObject[$i]['KmDa'];
                              $KmA=$ArrObject[$i]['KmA'];
                              $Moltiplicatore=$ArrObject[$i]['Moltiplicatore'];
                              
                            ?>
                             <!-- QUI L'ELENCO DELLE FERMATE -->
                                <tr class="rowBianca">
                                    <td><span><?=$KmDa?></span></td>
                                    <td><span><?=$KmA?></span></td>
                                      <td><span><?=$Moltiplicatore?></span></td>
                                     
                                   
                                         <td><a title="edita" onclick="javascript:ExternalLoad('rt_listino_fascekm','listino_fascekm.php?do=edit&amp;ListinoId=<?=$ListinoId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
                                    
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
                             <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_listino_fascekm','listino_fascekm.php?do=add&TrattaId=<?=$TrattaId?>');" title="aggiungi classe di listino"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['listino']['aggiungi_fasce_km']?></a>
                        </div>
                        
                        </div>
                            <? //spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}


function add_step_listino_servizi()
{

	$step_corrente=4;

	global $listino_wizard,$user,$db, $dizionario;

	$page=new Form();
	$dt=new DT();

	$LineaId=$listino_wizard->Id;


	include_once("listino_validator.php");
	
	$sql="Select * from RT_TipologiaBiglietto 
		where Stato=1 and Cancella=0 
		and OccupaPosto = 0 
		order by TipologiaBigliettoPeso asc ";
                        
	$ArrObjectTB = $db->fetch_array($sql);
	?>
	<style>
	.no_border, .no_border td{
		border:none !important;
	}
	</style>
	<form id="application_form" name="application_form"  method="post" action="#">
		<?php
		   $page->create_textbox_hidden("step_corrente",$step_corrente);
		   $page->create_textbox_hidden("step_successivo",0);
		   $page->create_textbox_hidden("action","createServizi");
		?>
                            
                  
		<div class="brain_formModifica formGestoreEdita">
		<h2><span class="brain_colorh2"><?=$dizionario['listino']['definizione_mess_2']?></span></h2>
		
		
		<?php
		$n=0;
		while ($n< sizeof($ArrObjectTB)) {
			$BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];
			$TBNome=$ArrObjectTB[$n]['TipologiaBiglietto'];
			$prezzo="";
			$sql="Select * from RT_ListinoServizi where BigliettoId=$BigliettoId and CorsaId=0";
			$row1 = $db->query_first($sql);
			
			if (!empty($row1['ListinoServizioId'])){
				$prezzo = str_replace(".",",",$row1['Prezzo']);
				$limite = $row1['Limite'];
				$limiteMin = $row1['LimiteMin'];
				$limiteOre = $row1['LimiteOre'];
                $limitePerNumPassegeri = $row1['LimitePerNumPassegeri'];
			} else {
				$limite = 0;
				$prezzo = 0;
				$limiteMin = 0;
				$limiteOre = 0;
                $limitePerNumPassegeri = 0;
			}
			 
			?>
			<div style="width:300px; display: inline-flex;">
				<table class="no_border" id="gestoreElencoAule" style="width:100%">
					<thead>
						<tr class="rowIntestazione">
							<td colspan="2"><?=$TBNome?> </td>
						</tr>
					</thead>
					<tr>
						<tr>
							<td><?=$dizionario['listino']['prezzo']?></td>
							<td>	
								<input style="float:none" class="numberDE" type="text" name="ListinoBigliettoPrezzo['<?=$ClasseId."_".$BigliettoId?>']" value="<?=$prezzo?>" SIZE="7" MAXLENGTH="7"> 
							</td>
						</tr>
						<tr>
							<td>
							<?=$dizionario['listino']['limite']?></td>
							<td>
							<input style="float:none" class="number" type="text" name="ListinoBigliettoLimite['<?=$ClasseId."_".$BigliettoId?>']" value="<?=$limite?>" SIZE="7" MAXLENGTH="7">
							</td>
						</tr>
						<tr>
							<td>
							<?=$dizionario['listino']['limite_min']?></td>
							<td>
							<input style="float:none" class="number" type="text" name="ListinoBigliettoLimiteMin['<?=$ClasseId."_".$BigliettoId?>']" value="<?=$limiteMin?>" SIZE="7" MAXLENGTH="7">
							</td>
						</tr>
						<tr>
							<td>
							<?=$dizionario['listino']['limite_ore']?></td>
							<td>
							<input style="float:none" class="number" type="text" name="ListinoBigliettoLimiteOre['<?=$ClasseId."_".$BigliettoId?>']" value="<?=$limiteOre?>" SIZE="7" MAXLENGTH="7">
							</td>
						</tr>
                        <tr>
							<td>
							<?=$dizionario['listino']['limite_per_num_passegeri']?></td>
							<td>
							<input style="float:none" class="number" type="text" name="ListinoBigliettoLimitePassegeri['<?=$ClasseId."_".$BigliettoId?>']" value="<?=$limitePerNumPassegeri?>" SIZE="7" MAXLENGTH="7">
							</td>
						</tr>
					</tr>
				</table>
			</div>
		  <?
			 $n++;
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


function add_step_listino_tipo()
{
	$step_corrente=4;

	global $listino_wizard,$user,$db, $dizionario;

	$page=new Form();
	$dt=new DT();

	$LineaId=$listino_wizard->Id;


	include_once("listino_validator.php");
	
	$sql="Select b.*, b1.TipologiaBiglietto as BigliettoRiferimento
		from RT_TipologiaBiglietto b
		LEFT JOIN RT_TipologiaBiglietto b1 ON b.TipoBigliettoIdRiferimento = b1.TipologiaBigliettoId
		where b.Stato=1 and b.Cancella=0 
		and b.OccupaPosto = 1 
		and b.TipoPrezzo = 1 
		order by TipologiaBigliettoPeso asc ";
   
	
	$ArrObjectTB = $db->fetch_array($sql);
	?>
			<style>
			.no_border, .no_border td{
					border:none !important;
			}
			</style>
			<form id="application_form" name="application_form"  method="post" action="#">
				<?
				   $page->create_textbox_hidden("step_corrente",$step_corrente);
				   $page->create_textbox_hidden("step_successivo",0);
				   $page->create_textbox_hidden("action","createListinoTipo");
				?>
                            
                  
			<div class="brain_formModifica formGestoreEdita">
				<h2><span class="brain_colorh2"><?=$dizionario['listino']['definizione_mess_3']?></span></h2>
				
				<?php
				$n=0;
				while ($n< sizeof($ArrObjectTB)) {
					$BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];
					$BigliettoRiferimento = $ArrObjectTB[$n]['BigliettoRiferimento'];
					$TBNome=$ArrObjectTB[$n]['TipologiaBiglietto'];
					$prezzo="";
					$sql="Select * from RT_ListinoTipo where BigliettoId=$BigliettoId";
					$row1 = $db->query_first($sql);
					
					if (!empty($row1['ListinoTipoId'])){
						$variazione = str_replace('.', ',', $row1['Variazione']);
					} else {
						$variazione = 0;
					}
					 
					?>
					<div style="width:300px; display: inline-flex;">
						<table class="no_border" id="gestoreElencoAule" style="width:100%">
							<thead>
								<tr class="rowIntestazione">
									<td colspan="3"><?=$TBNome?> </td>
								</tr>
							</thead>
							<tr>
								<td><?=$dizionario['listino']['variazione']?></td>
								<td>	
									<input style="float:none" class="numberDE" type="text" name="ListinoBigliettoVariazione[<?=$BigliettoId?>]" value="<?=$variazione?>" SIZE="7" MAXLENGTH="7"> 
								</td>
								<td>
									% di <?=$BigliettoRiferimento?>
								</td>
									</tr>
									<tr>
								<td>
							</tr>
						</table>
				    </div>
				  
				 
				  <?
					 
					 $n++;
				}
				?>
			</div>
			<div class="divSubmit">                  
			<?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>	
			</div>      
	</form>
		
 
<?php
    
    
 $db->Close();   
}


function add_step_listino()
{

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
                             <h2><?=$dizionario['listino']['classi_listino']?></h2>
                       
                             <br />
                              <br />
                        <div class="GestoreSedeAdd">
                             <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_listino_classe','listino_classe.php?do=add&ListinoId=<?=$ListinoId?>');" title="aggiungi listino"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['listino']['aggiungi_classe']?></a>
                        </div>
                            
                              <br />
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                                    <tbody><tr class="rowIntestazione">
                                    <td><?=$dizionario['generale']['peso']?></td>
                                    <td><?=$dizionario['listino']['listino']?></td>
                                    <td><?=$dizionario['generale']['stato']?></td>
                                    <td><?=$dizionario['generale']['edita']?></td>
                                    </tr>

                         <?
                        $sql="Select * from RT_Listino where OdcIdRef=$user->OdcId order by ListinoPeso asc";
                     
                        
                        $ArrObject = $db->fetch_array($sql);
                        $i=0;
                          while ($i< sizeof($ArrObject))
                            {
                              $ListinoId=$ArrObject[$i]['ListinoId'];
                              $ListinoNome=$ArrObject[$i]['ListinoNome'];
                              $ListinoPeso=$ArrObject[$i]['ListinoPeso'];
                              $ListinoStato=$ArrObject[$i]['Stato'];
                              
                            ?>
                             <!-- QUI L'ELENCO DELLE FERMATE -->
                                <tr class="rowBianca">
                                    <td><span><?=$ListinoPeso?></span></td>
                                     <td><span><?=$ListinoNome?></span></td>
                                    
                                     
                                    <td><span>
                                    <?
                                    if ($ListinoStato)
                                    print("attivo");
                                    else
                                    print("disattivo");    

                                    ?>
                                    </span></td>
                                         <td><a title="edita" onclick="javascript:ExternalLoad('rt_listino_classe','listino_classe.php?do=edit&amp;ListinoId=<?=$ListinoId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
                                    
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
                             <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_listino_classe','listino_classe.php?do=add&TrattaId=<?=$TrattaId?>');" title="aggiungi classe di listino"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['listino']['aggiungi_classe']?></a>
                        </div>
                        </div>
                            <? //spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}


/**
 * Funzione per gestire l'aggiunta di un listino passo dopo passo.
 * 
 * @param int $step Il passo corrente nel processo guidato (wizard).
 */
function add($step)
{
    // Includi il validatore per il listino
    include_once("listino_validator.php");  

    // Dichiarazione delle variabili globali
    global $HtmlCommon, $db, $listino_wizard, $funzione_edit, $abilita_modifica, $dizionario;

    // Se non è fornito un passo, inizializza il wizard
    if (!$step)
    {
        $listino_wizard = null;    
        unset($listino_wizard);
        $_SESSION['LISTINO_WIZARD'] = null;
        unset($_SESSION['LISTINO_WIZARD']);
        $step = 1; // Inizia dal primo passo
    }

    $mod = 0; // Flag per indicare se è in modalità modifica
    $GestoreStato = -1; // Stato predefinito

    // Controlla se l'oggetto wizard esiste
    if (is_object($listino_wizard))
    {
        // Inizializza i dati per il listino esistente
        $LineaId = $listino_wizard->Id;
        $listino_wizard->conn = $db;
        $listino_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr = $listino_wizard->DatiGenerali;
        $Stato = $DatiGeneraliArr['Stato'];
        $mod = 1; // Modalità modifica
        $abilita_modifica = true;

        // Mostra il titolo con il nome del listino
        $HtmlCommon->html_titolo_pagina(
            $dizionario['listino']['titolo_listino'] . " " . $DatiGeneraliArr['ListinoNome'], 
            0, 
            "rt_listino", 
            "listino.php"
        );
    } 
    else
    {
        // Creazione di un nuovo listino
        $mod = 0; // Modalità aggiunta
        $abilita_modifica = false;

        // Mostra il titolo predefinito
        $HtmlCommon->html_titolo_pagina(
            $dizionario['listino']['titolo_listino'], 
            0, 
            "rt_listino", 
            "listino.php"
        );
    }

    // Carica il menu per il wizard del listino
    carica_menu_listini($step, $mod);
    ?>
    <!-- Contenitore principale per il wizard -->
    <div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
    <?php       
    // Carica il passo appropriato in base al passo corrente
    if ($step == 1)
        add_step_listino_tipo(); // Passo 1: Definizione del tipo di listino
    elseif ($step == 2)
        add_step_listino_servizi(); // Passo 2: Definizione dei servizi del listino
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
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['stato'],"brain_salva","submit"); ?>
   
   
    
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