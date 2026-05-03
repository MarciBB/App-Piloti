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

global $ModuloId;
$ModuloId=28;// modulo base mediazione
global $user;
global $tratta_wizard,$funzione_edit,$abilita_modifica, $dizionario;

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


function edit($TrattaId)
{
    
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
    2=>"Fermate ed orari",
    3=>"Tariffe"
    );


    ?>
                <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
			<ul>
                         <?
                         $contamenu=1;
                         while ($contamenu<=3)
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
 
 <?
    
    
    
}

function add_step_tratta()
{
 $step_corrente=1;
 
global $tratta_wizard,$user,$db;

 $page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create"; 

        $TrattaId=0;
        
if (is_object($tratta_wizard) and ($tratta_wizard->Id))
{
  
    $TrattaId=$tratta_wizard->Id;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $tratta_wizard->conn=$db;
    $tratta_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
    $_SESSION['TRATTA_WIZARD']=serialize($tratta_wizard);
    
   // print_r($row);
    if($DatiGeneraliArr['TrattaId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}    



include_once("tratta_wz_validator.php");
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
                            <h2><span class="brain_colorh2"><?=$tratta_wizard->DatiGenerali['TrattaNome']?></span></h2>
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



function add_step_tariffe()
{
 $step_corrente=3;
 
global $tratta_wizard,$user,$db;

 $page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create"; 

        $TrattaId=0;
        
if (is_object($tratta_wizard) and ($tratta_wizard->Id))
{
  
    $TrattaId=$tratta_wizard->Id;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $tratta_wizard->conn=$db;
    $tratta_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
    $_SESSION['TRATTA_WIZARD']=serialize($tratta_wizard);
    
   // print_r($row);
    if($DatiGeneraliArr['TrattaId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}    



include_once("tratta_wz_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",0);
                        ?>
                            
                  
			<div class="brain_formModifica">
                            <? if ($action=="create") { ?>
                            <h2>Informazioni generali</h2>
                            <? } else { ?>
                            <h2><span class="brain_colorh2"><?=$tratta_wizard->DatiGenerali['TrattaNome']?></span> - Gestione Tariffe</h2>
                         <? } ?>
                           <div class="brain_data-content">                  
                            
				
                                
                          <div class="brainGestoreSedi">
                                 
                               
                                 
                                 <div>
                                     <p><strong>Partenza da: </strong><br>
                                     
                                    
                                    </p><table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                                    <tbody><tr class="rowIntestazione">
                                    <td>Pickup</td>
                                    <td>DropOff</td>
                                    <td>Importo</td>
                                    </tr>
                                    
                                    
                                        
                                    <tr class="rowBianca">
                                    <td><span>Salerno</span></td>
                                    <td><span>Milano</span></td>
                                    <td><input type="text"></td>


                                  
                                    </tr>
                                     
                                    
                                    
                                    </tbody>
                                    </table>
                                     
                                         
                                         
                                     
                                 </div>
                                 
                                 
                                                           </div>
                               
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}

function form_tipo1($azione,$AnagraficaId)
{
global $HtmlCommon,$db,$user,$tratta_wizard;    
/*$db= new Database();
$db->connect();*/
$page=new Form();
$dt=new DT();





$arr_stato[]= array("StatoId" => '0',"Stato" => 'Non Attivo');
$arr_stato[]= array("StatoId" => '1',"Stato" => 'Attivo');

$TrattaTipo=new TrattaTipo();
$TrattaTipo->conn=$db;
$arr_tratta_tipo=$TrattaTipo->getAll();

$Mezzo=new Mezzo();
$Mezzo->conn=$db;
$arr_mezzo=$Mezzo->getAll();

$TrattaDirezione=new TrattaDirezione();
$TrattaDirezione->conn=$db;
$arr_tratta_direzione=$TrattaDirezione->getAll();

$TrattaNome="";
$PercorsoPeso="";
$PercorsoStato=0;

if ($azione=="edit")
{    
    
    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
    $TrattaNome=$DatiGeneraliArr['TrattaNome'];
    $TrattaTipoId=$DatiGeneraliArr['TrattaTipoId'];
    $MezzoId=$DatiGeneraliArr['MezzoId'];
    $TrattaDirezioneId=$DatiGeneraliArr['TrattaDirezioneId'];
   $TrattaPeso=$DatiGeneraliArr['TrattaPeso'];
      $TrattaStato=$DatiGeneraliArr['Stato'];
    
}

                $page->create_textbox_hidden("action","update");
                $page->create_textbox("Tratta","TrattaNome","Tratta[TrattaNome]",$TrattaNome,1,"brain_campoForm",array("class"=>"'required'"));           
                $page->create_select("Tipologia:","Tratta[TrattaTipoId]","TrattaTipoId","brain_campoForm",$arr_tratta_tipo,$TrattaTipoId,"AppTrattaTipoId","AppTrattaTipo",array("class"=>"'required'"),1);
		$page->create_select("Mezzo:","Tratta[MezzoId]","MezzoId","brain_campoForm",$arr_mezzo,$MezzoId,"AppMezzoId","AppMezzo",array("class"=>"'required'"),1);
		$page->create_select("Direzione:","Tratta[TrattaDirezioneId]","TrattaDirezioneId","brain_campoForm",$arr_tratta_direzione,$TrattaDirezioneId,"AppTrattaDirezioneId","AppTrattaDirezione",null,0);
		 $page->create_textbox("Peso","Peso","Tratta[TrattaPeso]",$TrattaPeso,1,"brain_campoForm",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                $page->create_select("Stato","Tratta[Stato]","StatoId","brain_campoForm",$arr_stato,$TrattaStato,"StatoId","Stato",
                array("class"=>"'required'"),1);
             
    
    
        
    ?>

        
<div id="elenco_comuni"></div>
    <?
    print("<br style=\"clear:both;\"/>");
 
 
}



function add_step_fermate_orari()
{

$step_corrente=2;
 
global $tratta_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

$TrattaId=$tratta_wizard->Id;
 

include_once("tratta_wz_validator_stop.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			<div class="brain_formModifica formGestoreEdita">
                             <h2><span class="brain_colorh2"><?=$tratta_wizard->DatiGenerali['TrattaNome']?> - </span>Fermate ed orari</h2>
                       
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                                    <tbody><tr class="rowIntestazione">
                                    <td>Fermata</td>
                                    <td>Comune</td>
                                    <td>Pickup</td>
                                    <td>DropOff</td>
                                    <td>Interscabio</td>
                                    <td>Stato</td>
                                    <td>Edita</td>
                                    </tr>

                         <?
                        $sql="Select * from RT_ElencoFermata where TrattaId=$TrattaId and OdcIdRef=$user->OdcId order by FermataPeso asc";
                     
                        
                        $ArrObject = $db->fetch_array($sql);
                        $i=0;
                          while ($i< sizeof($ArrObject))
                            {
                              $FermataId=$ArrObject[$i]['FermataId'];
                              $FermataNome=$ArrObject[$i]['FermataNome'];
                              $Comune=$ArrObject[$i]['Comune'];
                              $FermataPeso=$ArrObject[$i]['FermataPeso'];
                              $FermataStato=$ArrObject[$i]['Stato'];
                              $IsPickup=$ArrObject[$i]['IsPickup'];
                              $IsDropOff=$ArrObject[$i]['IsDropOff'];
                              $IsInterscambio=$ArrObject[$i]['IsInterscambio'];
                              
                            ?>
                             <!-- QUI L'ELENCO DELLE FERMATE -->
                                <tr class="rowBianca">
                                    <td><span><?=$FermataNome?></span></td>
                                    <td><span><?=$Comune?></span></td>
                                    <td><span><? if ($IsPickup==1) echo("SI"); else echo("NO"); ?></span></td>
                                    <td><span><? if ($IsDropOff==1) echo("SI"); else echo("NO"); ?></span></td>
                                    <td><span><? if ($IsInterscambio==1) echo("SI"); else echo("NO"); ?></span></td>
                                    <td><span>
                                    <?
                                    if ($FermataStato)
                                    print("attivo");
                                    else
                                    print("disattivo");    

                                    ?>
                                    </span></td>
                                </tr>
                           
                             
                             
                             <h2><strong><?=$FermataNome?> - comune di <?=$Comune?></strong></h2>
                             <div class="brainGestoreSedi">
                                 
                                 <div class="brain_colLeft">
                                     <p><strong>Fermata: <?=$FermataNome?> <br /> Comune: <?=$Comune?> </strong><br />
                                     <?
                                    print("<br />");
                                     print("Pickup: ");
                                     if ($IsPickup==1) echo("SI"); else echo("NO");
                                     print("<br />");
                                     print("DropOff: ");
                                      if ($IsDropOff==1) echo("SI"); else echo("NO");
                                     print("<br />");
                                     print("Interscambio: ");
                                      if ($IsInterscambio==1) echo("SI"); else echo("NO");
                                     print("<br />");
                                      print("<br />");
                                     if ($FermataStato)
                                     print("<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"".$dizionario['generale']['attiva']."\"></i>");
                                     else
                                     print("<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"".$dizionario['generale']['disattiva']."\"></i>");    
                                     
                                     print("</p><br />");
                                     
                                     ?>
                                  
                                     <div class="GestoreSedeModifica">
                                         <a class="edita" href="#" onclick="javascript:ExternalLoad('rt_fermata','fermata.php?do=edit&amp;FermataId=<?=$FermataId?>');" title="edita">MODIFICA</a>
                                     </div>
                                         
                                 </div>
                                 
                                 <div class="brain_colRight">
                                     <p><strong>Orari</strong> - <a onclick="javascript:ExternalLoad('rt_orario','orario.php?do=add&FermataId=<?=$FermataId?>');" href="javascript:void(0);" class="brain_add"><i class="fa fa-plus" aria-hidden="true"></i> aggiungi orario</a> <br />
                                     
                                    
                                    <table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                                    <tbody><tr class="rowIntestazione">
                                    <td>Orario</td>
                                    <td>Corsa</td>
                                    <td>Percorso</td>
                                    <td>Stato</td>
                                    <td>Edita</td>
                                    </tr>
                                    
                                    
                                        
                                    <?
                                      $sql="Select * from RT_ElencoOrario where FermataId=$FermataId and OdcIdRef=$user->OdcId order by CorsaPeso asc,Orario asc";
                                    
                                      $ArrObject1 = $db->fetch_array($sql);
                                      
                                      $j=0;
                                      while ($j< sizeof($ArrObject1))
                                        {
                                          $OrarioId=$ArrObject1[$j]['OrarioId'];
                                           $CorsaId=$ArrObject1[$j]['CorsaId'];
                                          $Orario=$ArrObject1[$j]['Orario'];
                                          $CorsaNome=$ArrObject1[$j]['CorsaNome'];
                                          $PercorsoNome=$ArrObject1[$j]['PercorsoNome'];
                                          $OrarioStato=$ArrObject1[$j]['Stato'];
                                          
                                          ?>
                                    <tr class="rowBianca">
                                         <td><span><?=$Orario?></span></td>
                                         <td><span><?=$CorsaNome?></span></td>
                                         <td><span><?=$PercorsoNome?></span></td>
                                         
                                       
                                         <td>
											<span>
                                             <?
                                             if ($OrarioStato)
                                                 print("attivo");
                                                 else
                                                 print("disattivo");    
                                             
                                             ?>
                                            </span> 
                                             
                                         </td>
                                         <td><a title="edita" onclick="javascript:ExternalLoad('rt_orario','orario.php?do=edit&amp;OrarioId=<?=$OrarioId?>&CorsaId=<?=$CorsaId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
                                        </tr>
                                            <?
                                          
                                          
                                      $j++;    
                                          
                                          
                                        } 
                                    ?>
                                        
                                  <!-- <tfoot><tr> 
                                        <th bgcolor="#eaeaea" align="left" colspan="4"><a onclick="javascript:ExternalLoad('rt_tratta','tratta.php?do=add&LineaId=<?=$LineaId?>');" href="javascript:void(0);" class="brain_add">aggiungi tratta</a></th> 
                                    </tr></tfoot>-->
                                    
                                    
                                    </tbody>
                                    </table>
                                     
                                         
                                         
                                     
                                 </div>
                                 
                                 
                              <? 
                              
                              
                              ?>
                             </div> 
                              <?
                              print("<br style=\"clear:both;\"/>");
                              print("<br style=\"clear:both;\"/>");
                              
                              $i++;
                          }
                        
                        
                        
                        
                        ?>
                              </tbody>
                            </table>
                             <!-- FINE -->
                       <!-- <div class="GestoreSedeAdd">
                             <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_fermata','fermata.php?do=add&TrattaId=<?=$TrattaId?>');" title="aggiungi fermata">aggiungi fermata</a>
                        </div>-->
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        </div>
                            <? //spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}



function add($step)
{
 include_once("tratta_wz_validator.php");  
 
 global $HtmlCommon,$db,$tratta_wizard,$funzione_edit,$abilita_modifica;





if (!$step)
{
$tratta_wizard=null;    
unset($tratta_wizard);
$_SESSION['TRATTA_WIZARD']=null;
unset($_SESSION['TRATTA_WIZARD']);
$step=1;
}
$mod=0;
$GestoreStato=-1;
if (is_object($tratta_wizard))
{
    
    
    $TrattaId=$tratta_wizard->Id;
    $tratta_wizard->conn=$db;
    $tratta_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
    $Stato=$DatiGeneraliArr['Stato'];
    $mod=1;
     $abilita_modifica=true;
     $HtmlCommon->html_titolo_pagina("Editazione tratta ".$DatiGeneraliArr['TrattaNome'],1,"rt_tratta","tratta_wz.php");
} 
else
{        $mod=0;
        $abilita_modifica=false;
        $HtmlCommon->html_titolo_pagina("Aggiungi percorso",1,"rt_tratta","tratta_wz.php");
}

  
        
        carica_menu_percorso($step,$mod);
 
 ?>

		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
 <?       
 

if ($step==1)
add_step_tratta();
elseif ($step==2)
add_step_fermate_orari();

elseif ($step==3)
add_step_tariffe();



$db= new Database();
    $db->connect();

?>
                   
 <!-- </div> -->
        
	</div>

<? if ($GestoreStato>=0) { ?>

<div class="brain_stato-mediazione">
 <h3>Stato gestore</h3>
<form id="CambiaStatoMediazioneId">
    
    <input type="hidden" name="action" value="cambia_stato_gestore" />
     
    <? if ($GestoreStato){?><i class="fa fa-check-circle green" aria-hidden="true"></i> <?=$dizionario['generale']['attivo']?><p><?=$dizionario['listino']['attivo_desc']?></p><? } ?>
	<? if ($GestoreStato==0){?><i class="fa fa-times-circle red" aria-hidden="true"></i> <?=$dizionario['generale']['disattivo']?><p><?=$dizionario['listino']['disattivo_desc']?></p><? } ?>

     <div class="CambiaStatoMediazione">
          
    
    <select name="Stato">
        <option value="1" <? if ($GestoreStato==1) echo ("selected")?> >attivo</option>
        <option value="0" <? if ($GestoreStato==0) echo ("selected")?> >disattivo</option>
    </select>   
    </div>
    <div class="CambiaStatoMediazioneSubmit">
        <input class="brain_CambiaStato" type="submit" name="CambiaStato" value="Cambia" />
    </div>   
</form>    
    
</div>
<?
}
    
}

function spara_pulsanti_wizard_box()
{
$page=new Form();
    
?>
<div class="divSubmit">
                                
        <?  $page->create_button("Salva","Salva","Salva","brain_salva","submit"); ?>
   
   
    
         <a href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi" class="brain_annulla">Chiudi</a>
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
                                
        <?  $page->create_button("Salva","Salva","avanti","brain_salva","submit"); ?>
    <?  
    if ($steptogo>0)
    $page->create_button("indietro","indietro","indietro","brain_back","button"); ?>
    
         <a href="javascript:void(0);" onclick="loadMainContent('mediatore','mediatore.php',this);" title="Home" class="brain_annulla">Annulla compilazione</a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
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
                         
        <?  $page->create_button("Salva","Salva","salva","brain_salva","submit"); ?>
       
         <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla">Annulla</a>
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
    if (is_object($tratta_wizard))
        $tratta_wizard->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
			if(!isset($do)) 
			$do='';
		
		
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
				
                                 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                // print_r($permesso);
                                        if (sizeof($permesso))
                                           edit($_REQUEST['TrattaId']);
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
// se l'utente non ÃƒÂ¨ loggato
else {
header("Location: /logout.php");
}
?>