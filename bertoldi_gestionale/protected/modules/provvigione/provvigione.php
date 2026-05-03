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
include_once($classespath_."class.Odc.php");
include_once($classespath_."class.Provvigione.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");



global $ModuloId;
$ModuloId=33;// modulo base mediazione
global $user;
global $provvigione_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$provvigione_wizard=null;

if(isset($_SESSION['PROVVIGIONE_WIZARD'])) {
$provvigione_wizard=unserialize($_SESSION['PROVVIGIONE_WIZARD']);
}




function show_list()
{
global $user,$HtmlCommon;
$HtmlCommon->html_titolo_pagina("Elenco profili provvigioni");
$HtmlCommon->html_titolo_box("Elenco profili provvigioni");
$db= new Database();
$db->connect();


global $user,$HtmlCommon,$db,$ModuloId;    
 

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','provvigione','provvigione.php?do=add','aggiungi provvigione');
              



include_once("provvigione_datatable.php");

?>

                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
            
            	<tr class="brain_tabellaTr">
                      
			
                        <th width="35%">provvigione</th>
		        <th width="10%">da</th>
                        <th width="10%">a</th>
                        <th width="40%">descrizione</th>
                        <th width="5%">edita</th>
                      
		</tr>
            
		<tr class="brain_tabellaFilter">
		      
                       
			<th><input type="text" /></th> 
                        <th><input type="text" /></th> 
                         <th><input type="text" /></th> 
                          <th><input type="text" /></th> 
                      <th><input type="hidden" /></th> 
			
		</tr>
	</thead>
	<tbody>
         
		<tr>
                        
			<td colspan="5" class="dataTables_empty">Caricamento in corso...</td>
                       
		</tr>
	</tbody>
	<tfoot> 
		<tr> 
		<td colspan="5"></td>
		</tr> 
	</tfoot> 
</table>

<?
   
}





function edit($provvigioneId)
{
    global $provvigione_wizard,$db,$user;   
    $provvigione_wizard=new Provvigione($provvigioneId);
    $_SESSION['PROVVIGIONE_WIZARD']=serialize($provvigione_wizard);
    add(1);
}


function carica_menu_provvigione($step_corrente,$mod)
{
global $abilita_modifica,$provvigione_wizard,$db;
$provvigione_wizard->conn=$db;

   
$menu=array(
    1=>"Dati generali",
    2=>"Importi"
   
    );



    ?>
                <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
			<ul>
                         <?
                         $dim_menu=sizeof($menu);
                         $contamenu=1;
                         while ($contamenu<=$dim_menu)
                         {
                          $class1="";
                          $class2="";
                          
                          if ($contamenu==$step_corrente)
                          {
                              $class1="sel";
                              $class2="brain_firstspan sel";
                          }
                             
                          $StatoStep="";
                            
                          if ( ($contamenu<=$dim_menu) or (($contamenu>$dim_menu) and ($mod))) { ?>
                            
                            <li class="<?=$class1?>">
                                <span class="<?=$class2?>">
                                  <? //if (($contamenu<=$step_corrente) or ($mod==1))
                                  if ($mod)
                                  {
                                     
                                      
                                   ?>
                                             <a href="javascript:void(0);" onclick="loadMediazioneStep('provvigione','provvigione.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
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

function add_step_dati_generali()
{
 $step_corrente=1;
 
global $provvigione_wizard,$user,$db;

$page=new Form();
$dt=new DT();

 $azione="add"; 
 $action="create"; 

 
$arr_stato_provvigione[]= array("StatoId" => '1',"Stato" => 'Attiva');
$arr_stato_provvigione[]= array("StatoId" => '0',"Stato" => 'Disattiva'); 

$arr_default_provvigione[]= array("ProvvigioneDefaultId" => '1',"ProvvigioneDefault" => 'Si');
$arr_default_provvigione[]= array("ProvvigioneDefaultId" => '0',"ProvvigioneDefault" => 'No'); 



        
if (is_object($provvigione_wizard) and ($provvigione_wizard->ProvvigioneId))
{
    
    $provvigioneId=$provvigione_wizard->ProvvigioneId;
    $provvigione_wizard->conn=$db;
    $provvigione_wizard->inizializzaDatiGenerali();
    $arr_provvigione=$provvigione_wizard->ProvvigioneDatiGenerali;
   $FissoPerPratica=str_replace(".",",",$arr_provvigione['FissoPerPratica']);
    
    $_SESSION['PROVVIGIONE_WIZARD']=serialize($provvigione_wizard);
    
   // print_r($row);
    if($arr_provvigione['ProvvigioneId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}    



include_once("provvigione_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			<div class="brain_formModifica">
                           
                                       <h2><span class="brain_colorh2"><?=$provvigione_wizard->ProvvigioneDatiGenerali['Provvigione']?> - da <?=$arr_provvigione['DaNumeroMediazioni']?> a <?=$arr_provvigione['ANumeroMediazioni']?> mediazioni - </span>Dati generali</h2>
                        
                       
                        
                           <div class="brain_data-content">                  
                            
			  <? 
                             
                                 $page->create_textbox("Nome Provvigione","Provvigione[Provvigione]","Provvigione[Provvigione]",$arr_provvigione['Provvigione'],1,"brain_campiform",array("class"=>"'required'"));           
                                 print(" <br style=\"clear:both;\"/>");
                                
                                 /*  $page->create_textbox("Da n. mediazioni","Provvigione[DaNumeroMediazioni]","Provvigione[DaNumeroMediazioni]",$arr_provvigione['DaNumeroMediazioni'],1,"brain_campiform",array("class"=>"'numeric'"),"","6","6","");
                                   $page->create_textbox("A n. mediazioni","Provvigione[ANumeroMediazioni]","Provvigione[ANumeroMediazioni]",$arr_provvigione['ANumeroMediazioni'],1,"brain_campiform",array("class"=>"'numeric'"),"","6","6","");*/
                                     $page->create_textbox("Importo fisso per pratica","Provvigione[FissoPerPratica]","Provvigione[FissoPerPratica]",$FissoPerPratica,1,"brain_campiform",array("class"=>"'required'"),"","8","8","");
                                 
                                   
                                   print(" <br style=\"clear:both;\"/>");
                                    
                                   /*  $page->create_select("Provvigione di default","Provvigione[ProvvigioneDefault]","Provvigione[ProvvigioneDefault]","brain_campiform",$arr_default_provvigione,$arr_provvigione['ProvvigioneDefault'],"ProvvigioneDefaultId","ProvvigioneDefault",
                                 array("class"=>"'required'"),1,"assegna ai nuovi promoter inseriti");
                                 print(" <br style=\"clear:both;\"/>");
                                 */
                                 $page->create_texarea("Descrizione","Descrizione","Provvigione[DescrizioneProvvigione]",$arr_provvigione['DescrizioneProvvigione'],1,"brain_campoFormBig",array("class"=>"'required'"),"");
                                 print(" <br style=\"clear:both;\"/>");
                                 $page->create_select("Stato","Provvigione[Stato]","Provvigione[Stato]","brain_campiform",$arr_stato_provvigione,$arr_provvigione['Stato'],"StatoId","Stato",
                                 array("class"=>"'required'"),1);
                                 
                                 
                
                            
                          ?>
                               
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}






function add_step_importi()
{

    
$step_corrente=2;
 
global $provvigione_wizard,$user,$db;

$page=new Form();
 $dt=new DT();


 
$action="create_provvigione_dettaglio"; 
        
        
if (is_object($provvigione_wizard) and ($provvigione_wizard->ProvvigioneId))
{
  
    global $provvigione_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create_provvigione_dettaglio"; 

        
        
if (is_object($provvigione_wizard) and ($provvigione_wizard->ProvvigioneId))
{
  
    $provvigioneId=$provvigione_wizard->ProvvigioneId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $provvigione_wizard->conn=$db;
    $provvigione_wizard->inizializzaDatiGenerali();
    $arr_provvigione=$provvigione_wizard->ProvvigioneDatiGenerali;
    
    $mediatore_wizard->conn=$db;
    $dettagli=$provvigione_wizard->inizializzaProvvigioneDettagli();
    $provvigioneDettagli=$provvigione_wizard->ProvvigioneDettagli;
    
    $_SESSION['PROVVIGIONE_WIZARD']=serialize($provvigione_wizard);
    
   // print_r($row);
   
                         
}  
    
  
}    



include_once("provvigione_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",0);
                        ?>
                            
                  
			<div class="brain_formModifica">
                             <h2><span class="brain_colorh2"><?=$provvigione_wizard->ProvvigioneDatiGenerali['Provvigione']?> - da <?=$arr_provvigione['DaNumeroMediazioni']?> a <?=$arr_provvigione['ANumeroMediazioni']?> mediazioni - </span>Dettaglio provvigione</h2>
                           <div class="brain_data-content">                  
                            
				
                                
                               <? 
                               
                            
                            print("<h3>Percentuali riconosciute</h3>"); 
                            
                 print("<br style=\"clear:both;\"/>");
               
                 $numero_riferimenti=0;
                               $oldtipo="";
                               
                               $sql = "SELECT * from MediazionePagamentoRif where AbilitatoPerprovvigione=1 order by peso asc ";
                            $arr_riferimenti= $db->fetch_array($sql);
                               
                               while ($numero_riferimenti<sizeof($arr_riferimenti))
                               {
                                   
                                   //print($arr_materia[$numero_materie]['Materia']);
                                   $RifId=$arr_riferimenti[$numero_riferimenti]['MediazionePagamentoRifID'];
                                   $Rif=$arr_riferimenti[$numero_riferimenti]['MediazionePagamentoRif'];
                                 
                                   $valorecompetenza=0;
                                   if (isset($provvigioneDettagli))
                                   {
                                            foreach($provvigioneDettagli as $pos => $val)
                                              {
                                              if($val['PagamentoRifId']==$RifId)
                                                  $valorecompetenza=$val['PercentualeRiconosciuta'];
                                                
                                                
                                              }
                                       
                                       
                                   }
                                   
                                 
                                       
                                  
                                   $page->create_textbox($Rif,"Riferimento".$RifId,$RifId,$valorecompetenza,1,"brain_campoForm",array("class"=>"'numeric'"),"","3","3","% riconosciuta");
                            
                                   $numero_riferimenti++;
                               }
                             print("<br style=\"clear:both;\"/>");
                 
                            
                            
                          
                     ?>                                      
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}



function add($step)
{
 include_once("provvigione_validator.php");  
 
 global $HtmlCommon,$db,$provvigione_wizard,$funzione_edit,$abilita_modifica;



if (!$step)
{
$provvigione_wizard=null;    
unset($provvigione_wizard);
$_SESSION['PROVVIGIONE_WIZARD']=null;
unset($_SESSION['PROVVIGIONE_WIZARD']);
$step=1;
}
$mod=0;

if (is_object($provvigione_wizard))
{


    
    $mod=1;
    $provvigioneId=$provvigione_wizard->ProvvigioneId;
    $provvigione_wizard->conn=$db;
    $provvigione_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$provvigione_wizard->ProvvigioneDatiGenerali;
    $abilita_modifica=true;
    
    
    $HtmlCommon->html_titolo_pagina("Editazione provvigione ".$DatiGeneraliArr['Provvigione'],1,"provvigione","provvigione.php");
} 
/*else
{    
    $mod=0;    
    $HtmlCommon->html_titolo_pagina("Aggiungi mediatore",1,"mediatore","mediatore.php");
               $abilita_modifica=false;
}*/
 if ($step>2)
     $step=2;
        carica_menu_provvigione($step,$mod);
 
 ?>

		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
 <?       
 

if ($step==1)
add_step_dati_generali();
elseif ($step==2)
add_step_importi();

 




$db= new Database();
    $db->connect();

?>
                   
 <!-- </div> -->
        
	</div>

<?
    
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
    if (is_object($provvigione_wizard))
        $provvigione_wizard->conn=$db;
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
				
                                 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                 //print_r($permesso);
                                        if (sizeof($permesso))
                                           edit($_REQUEST['ProvvigioneId']);
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
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>