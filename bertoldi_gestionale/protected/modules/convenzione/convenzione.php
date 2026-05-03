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
include_once($classespath_."class.Convenzione.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");



global $ModuloId;
$ModuloId=27;// modulo base mediazione
global $user;
global $convenzione_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$convenzione_wizard=null;

if(isset($_SESSION['CONVENZIONE_WIZARD'])) {
$convenzione_wizard=unserialize($_SESSION['CONVENZIONE_WIZARD']);
}




function show_list()
{
global $user,$HtmlCommon, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['convenzione']['titolo_convenzione']);
$HtmlCommon->html_titolo_box($dizionario['convenzione']['titolo_convenzione']);
$db= new Database();
$db->connect();


global $user,$HtmlCommon,$db,$ModuloId;    
 

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','convenzione','convenzione.php?do=add',$dizionario['convenzione']['aggiungi']);
              



include_once("convenzione_datatable.php");

?>

                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
            
            	<tr class="brain_tabellaTr">
                      
			<th width="30%"><?=$dizionario['convenzione']['convenzione']?></th>
		        <th width="20%"><?=$dizionario['generale']['tipo']?></th>
                        <th width="50%"><?=$dizionario['generale']['descrizione']?></th>
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
   
}





function edit($ConvenzioneId)
{
  
    global $convenzione_wizard,$db,$user;   
    $convenzione_wizard=new Convenzione($ConvenzioneId);

    $_SESSION['CONVENZIONE_WIZARD']=serialize($convenzione_wizard);
    add(1);
}


function carica_menu_convenzione($step_corrente,$mod)
{
global $abilita_modifica,$convenzione_wizard,$db, $dizionario;
$convenzione_wizard->conn=$db;
//$menu=$convenzione_wizard->getMenuWizard();

   
$menu=array(
    1=>$dizionario['convenzione']['dati_generali'],
    2=>$dizionario['convenzione']['importo']
   
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
                                             <a href="javascript:void(0);" onclick="loadMediazioneStep('convenzione','convenzione.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
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
 
global $convenzione_wizard,$user,$db, $dizionario;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create"; 

 
$arr_tipo_convenzione[]= array("ConvenzioneTipoId" => '1',"ConvenzioneTipo" => $dizionario['convenzione']['percentuale']);
$arr_tipo_convenzione[]= array("ConvenzioneTipoId" => '2',"ConvenzioneTipo" => $dizionario['convenzione']['importo_fisso']);       

$arr_stato_convenzione[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attiva']);
$arr_stato_convenzione[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattiva']); 
        
if (is_object($convenzione_wizard) and ($convenzione_wizard->ConvenzioneId))
{
    $ConvenzioneId=$convenzione_wizard->ConvenzioneId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $convenzione_wizard->conn=$db;
    $convenzione_wizard->inizializzaDatiGenerali();
    $arr_convenzione=$convenzione_wizard->ConvenzioneDatiGenerali;
    $_SESSION['CONVENZIONE_WIZARD']=serialize($convenzione_wizard);
    
   // print_r($row);
    if($arr_convenzione['ConvenzioneId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}    



include_once("convenzione_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			<div class="brain_formModifica">
                           
                            <h2><span class="brain_colorh2"><?=$convenzione_wizard->ConvenzioneDatiGenerali['Convenzione']?> - </span><?=$dizionario['convenzione']['dati_generali']?></h2>
                        
                           <div class="brain_data-content">                  
                            
			  <? 
                             
                                 $page->create_textbox($dizionario['convenzione']['nome_convenzione'],"Convenzione[Convenzione]","Convenzione[Convenzione]",$arr_convenzione['Convenzione'],1,"brain_campiform",array("class"=>"'required'"));           
                                 print(" <br style=\"clear:both;\"/>");
                                 
                                     $page->create_texarea($dizionario['generale']['descrizione'],"Descrizione","Convenzione[DescrizioneConvenzione]",$arr_convenzione['DescrizioneConvenzione'],1,"brain_campoFormBig",array("class"=>"'required'"),"");
                                  
                              
                             /*    print(" <br style=\"clear:both;\"/>");
                                 $page->create_select("Tipo Convenzione","Convenzione[ConvenzioneTipoId]","Convenzione[ConvenzioneTipoId]","brain_campiform",$arr_tipo_convenzione,$arr_convenzione['ConvenzioneTipoId'],"ConvenzioneTipoId","ConvenzioneTipo",
                         array("class"=>"'required'"),1);*/
                                 
                                  $page->create_textbox_hidden("Convenzione[ConvenzioneTipoId]",1);
                                 
                                 
                                  print(" <br style=\"clear:both;\"/>");
                                   print(" <br style=\"clear:both;\"/>");
                                 $page->create_select($dizionario['generale']['stato'],"Convenzione[Stato]","Convenzione[Stato]","brain_campiform",$arr_stato_convenzione,$arr_convenzione['Stato'],"StatoId","Stato",
                         array("class"=>"'required'"),1);
                                 
                                 
                
                            
                          ?>
                               
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}




function add_step_tariffario()
{
 
    
$step_corrente=2;
 
global $convenzione_wizard,$user,$db, $dizionario;

$page=new Form();
 $dt=new DT();


 
$action="update_impostazioni_email"; 
        
        
if (is_object($convenzione_wizard) and ($convenzione_wizard->ConvenzioneId))
{
  
    global $convenzione_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create"; 

        
        
if (is_object($convenzione_wizard) and ($convenzione_wizard->ConvenzioneId))
{
  
    $ConvenzioneId=$convenzione_wizard->ConvenzioneId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $convenzione_wizard->conn=$db;
    $convenzione_wizard->inizializzaDatiGenerali();
    $arr_odc=$convenzione_wizard->ConvenzioneDatiGenerali;
    $_SESSION['CONVENZIONE_WIZARD']=serialize($convenzione_wizard);
    
   // print_r($row);
    if($arr_odc['ConvenzioneId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}  
    
  
}    

$arr_stato[]= array("MedNotId" => '0',"MedNot" => 'NON inviare la mail al mediatore in fase di nomina');
$arr_stato[]= array("MedNotId" => '1',"MedNot" => 'Invia una email al mediatore in fase di nomina');

include_once("convenzione_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			<div class="brain_formModifica">
                             <h2><span class="brain_colorh2"><?=$convenzione_wizard->ConvenzioneDatiGenerali['Odc']?> - </span><?=$dizionario['convenzione']['impostazioni_email']?>]</h2>
                           <div class="brain_data-content">                  
                            
				
                                
                               <? 
                               
                            
                            print("<h3>Impostazioni Generali</h3>"); 
                            
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['convenzione']['nome_convenzione'],"Convenzione[Convenzione]","Convenzione[Convenzione]",$arr_odc['Convenzione'],1,"brain_campiform",array("class"=>"'required'"));           
                 
                  $page->create_select($dizionario['convenzione']['nomina_mediatore'],"Convenzione[EmailsNotificaAlMediatore]","Convenzione[EmailsNotificaAlMediatore]","brain_campiform",$arr_stato,$arr_odc['EmailsNotificaAlMediatore'],"MedNotId","MedNot",
                         array("class"=>"'required'"),1);
                 
                 /*$page->create_textbox("Mittente Email","Convenzione[EmailSmtp]","Convenzione[EmailSmtp]",$arr_odc['EmailSmtp'],1,"brain_campiform",array("class"=>"'required email'"));           
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox("Server Smtp","Convenzione[ServerSmtp]","Convenzione[ServerSmtp]",$arr_odc['ServerSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 $page->create_textbox("Porta Smtp","Convenzione[PortaSmtp]","Convenzione[PortaSmtp]",$arr_odc['PortaSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                print("<br style=\"clear:both;\"/>");
                 $page->create_textbox("Username","Convenzione[UserSmtp]","Convenzione[UserSmtp]",$arr_odc['UserSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 $page->create_textbox_password("Password","Convenzione[PwdSmtp]","Convenzione[PwdSmtp]",$arr_odc['PwdSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 
                  */
                   print("<br style=\"clear:both;\"/>");
                  
                 
                 
                 print("<h3>".$dizionario['convenzione']['notifica_email']."</h3>"); 
                            
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['convenzione']['nuova_mediazione'],"Convenzione[EmailsNotificaNuovaMediazione]","Convenzione[EmailsNotificaNuovaMediazione]",$arr_odc['EmailsNotificaNuovaMediazione'],0,"brain_campiform",array("class"=>"'email'"));           
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['convenzione']['in_approvazione'],"Convenzione[EmailPerApprovazione]","Convenzione[EmailPerApprovazione]",$arr_odc['EmailPerApprovazione'],0,"brain_campiform",array("class"=>"'email'"));           
                  print("<br style=\"clear:both;\"/>");
                 $page->create_select($dizionario['convenzione']['nomina_mediatore'],"Convenzione[EmailsNotificaAlMediatore]","Convenzione[EmailsNotificaAlMediatore]","brain_campiform",$arr_stato,$arr_odc['EmailsNotificaAlMediatore'],"MedNotId","MedNot",
                         array("class"=>"'required'"),1);
                 
                 print("<br style=\"clear:both;\"/>");
                            
                            
                          
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
 
global $convenzione_wizard,$user,$db, $dizionario;

$page=new Form();
 $dt=new DT();


 
$action="update_importi"; 
        
        
if (is_object($convenzione_wizard) and ($convenzione_wizard->ConvenzioneId))
{
  
    global $convenzione_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create_convenzione_dettaglio"; 

        
        
if (is_object($convenzione_wizard) and ($convenzione_wizard->ConvenzioneId))
{
  
    $ConvenzioneId=$convenzione_wizard->ConvenzioneId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $convenzione_wizard->conn=$db;
    $convenzione_wizard->inizializzaDatiGenerali();
    $arr_convenzione=$convenzione_wizard->ConvenzioneDatiGenerali;
    
    $mediatore_wizard->conn=$db;
    $dettagli=$convenzione_wizard->inizializzaConvenzioneDettagli();
    $ConvenzioneDettagli=$convenzione_wizard->ConvenzioneDettagli;
    
    $_SESSION['CONVENZIONE_WIZARD']=serialize($convenzione_wizard);
    
   // print_r($row);
   
                         
}  
    
  
}    



include_once("convenzione_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",0);
                        ?>
                            
                  
			<div class="brain_formModifica">
                             <h2><span class="brain_colorh2"><?=$convenzione_wizard->ConvenzioneDatiGenerali['Convenzione']?> - </span><?=$dizionario['convenzione']['dettaglio']?></h2>
                           <div class="brain_data-content">                  
                            
				
                                
                               <? 
                               
                            
                            print("<h3>Sconti</h3>"); 
                            
                 print("<br style=\"clear:both;\"/>");
               
                 $numero_riferimenti=0;
                               $oldtipo="";
                               
                               $sql = "SELECT * from MediazionePagamentoRif where AbilitatoPerConvenzione=1 order by peso asc ";
                            $arr_riferimenti= $db->fetch_array($sql);
                               
                               while ($numero_riferimenti<sizeof($arr_riferimenti))
                               {
                                   
                                   //print($arr_materia[$numero_materie]['Materia']);
                                   $RifId=$arr_riferimenti[$numero_riferimenti]['MediazionePagamentoRifID'];
                                   $Rif=$arr_riferimenti[$numero_riferimenti]['MediazionePagamentoRif'];
                                 
                                   $valorecompetenza=0;
                                   if (isset($ConvenzioneDettagli))
                                   {
                                            foreach($ConvenzioneDettagli as $pos => $val)
                                              {
                                              if($val['PagamentoRifId']==$RifId)
                                                  $valorecompetenza=$val['PercentualeSconto'];
                                                
                                                
                                              }
                                       
                                       
                                   }
                                   
                                  /* if ($MateriaTipoId!=$oldtipo)
                                   {
                                       $oldtipo=$MateriaTipoId;
                                       $m="Opzionali";
                                       if ($MateriaTipoId==1)
                                           $m="Obbligatorie";
                                      
                                      print("<br style=\"clear:both;\"/>");  
                                      print("<h3>Materie ".$m."</h3>"); 
                                   
                                      
                                   }*/
                                           
                                       
                                  
                                   $page->create_textbox($Rif,"Riferimento".$RifId,$RifId,$valorecompetenza,0,"brain_campoForm",array("class"=>"'numeric'"),"","5","5","% di sconto da applicare");
                            
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
 include_once("convenzione_validator.php");  
 
 global $HtmlCommon,$db,$convenzione_wizard,$funzione_edit,$abilita_modifica, $dizionario;



if (!$step)
{
$convenzione_wizard=null;    
unset($convenzione_wizard);
$_SESSION['CONVENZIONE_WIZARD']=null;
unset($_SESSION['CONVENZIONE_WIZARD']);
$step=1;
}
$mod=0;

if (is_object($convenzione_wizard))
{


    
    $mod=1;
    $ConvenzioneId=$convenzione_wizard->ConvenzioneId;
    $convenzione_wizard->conn=$db;
    $convenzione_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$convenzione_wizard->ConvenzioneDatiGenerali;
    $abilita_modifica=true;
    
    
    $HtmlCommon->html_titolo_pagina($dizionario['convenzione']['titolo_modifica']." ".$DatiGeneraliArr['Convenzione'],1,"convenzione","convenzione.php");
} 
/*else
{    
    $mod=0;    
    $HtmlCommon->html_titolo_pagina("Aggiungi mediatore",1,"mediatore","mediatore.php");
               $abilita_modifica=false;
}*/
 if ($step>2)
     $step=2;
        carica_menu_convenzione($step,$mod);
 
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
    
global $funzione_edit, $dizionario;;

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
    
         <a href="javascript:void(0);" onclick="loadMainContent('mediatore','mediatore.php',this);" title="Home" class="brain_annulla"><?=$dizionario['convenzione']['annulla']?></a>
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
    if (is_object($convenzione_wizard))
        $convenzione_wizard->conn=$db;
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
                                           edit($_REQUEST['ConvenzioneId']);
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