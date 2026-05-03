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
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");



global $ModuloId;
$ModuloId=2;// modulo base mediazione
global $user;
global $organismo_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$organismo_wizard=null;

if(isset($_SESSION['ORGANISMO_WIZARD'])) {
$organismo_wizard=unserialize($_SESSION['ORGANISMO_WIZARD']);
}

function edit_tariffario()
{
global $user,$HtmlCommon;
$HtmlCommon->html_titolo_pagina("Edita tariffario");
$HtmlCommon->html_titolo_box("Edita tariffario");
$db= new Database();
$db->connect();
$page=new Form();
    
 $ValidoDal=$_REQUEST['ValidoDal'];
 $ValidoAl=$_REQUEST['ValidoAl'];
 
 include_once("organismo_validator.php");
?>

<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
            
            	<tr class="brain_tabellaTr">
                        <th width="5%">Fascia</th>
                        <th width="5%">Spese di Avvio</th>
                        <th width="10%">Compensi di Mediazione</th>
		        <th width="5%">edita</th>
                      
		</tr>
            
                 <tr class="brain_tabellaFilter">     
                     <th><span></span><input type="hidden" /></th>  
                      <th><span></span><input type="hidden" /></th>  
                      <th><span class="hidden"></span><input type="text" /></th> 
                      <th><span class="hidden"></span><input type="text" /></th> 
                      <th><span></span><input type="hidden" /></th> 
                      <th><span class="hidden"></span><input type="text" /></th> 
                      <th><span></span><input type="hidden" /></th> 
                      <th></th>
		</tr>
	</thead>
	<tbody>
         
		<tr>
                        
			<td colspan="8" class="dataTables_empty">Caricamento in corso...</td>
                       
		</tr>
	</tbody>
</table>


<!--			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("update",$action);
                           $page->create_textbox_hidden("ValidoDal",$ValidoDal);
                           $page->create_textbox_hidden("ValidoAl",$ValidoAl);
                        ?>
                            
                  
			<div class="brain_formModifica">
                             <h2><span class="brain_colorh2">Edita Tariffario</h2>
                           <div class="brain_data-content">  
 
 <?
    $sql = "SELECT * from Tariffario where ValidoDal='$ValidoDal' and ValidoAl='$ValidoAl' and OdcId=$user->OdcId order by TariffarioId asc ";
  
    
    $arr_tariffario= $db->fetch_array($sql);
                               $n=0;
                               while ($n<sizeof($arr_tariffario))
                               {

                                    //print($arr_materia[$numero_materie]['Materia']);
                                    $TariffarioId=$arr_tariffario[$n]['TariffarioId'];
                                    $Tariffario=$arr_tariffario[$n]['Tariffario'];
                                    $TariffarioDa=$arr_tariffario[$n]['TariffarioDa'];
                                    $TariffarioA=$arr_tariffario[$n]['TariffarioA'];
                                    $SpeseAvvioProcedura= number_format($arr_tariffario[$n]['SpeseAvvioProcedura'], 2, ',' , '.');
                                    $SpeseMediazioneParte=number_format($arr_tariffario[$n]['SpeseMediazioneParte'], 2, ',' , '.');
                                    $IndennitaInizialeMinima=number_format($arr_tariffario[$n]['IndennitaInizialeMinima'], 2, ',' , '.');
                                
                                  
                                   $page->create_textbox("Da","DaId","DaId",$TariffarioDa,1,"brain_campiform",array("class"=>"'required'"),"","8","8");
                                   $page->create_textbox("A","AId","AId",$TariffarioA,1,"brain_campiform",array("class"=>"'required'"),"","8","8");
                                   $page->create_textbox("Spese di Avvio","SpeseAvvioProcedura","SpeseAvvioProcedura",$SpeseAvvioProcedura,1,"brain_campiform",array("class"=>"'required'"),"","8","8");
                                   $page->create_textbox("Spese Mediazione Parte","SpeseMediazioneParte","SpeseMediazioneParte",$SpeseMediazioneParte,1,"brain_campiform",array("class"=>"'required'"),"","8","8");
                                   $page->create_textbox("IndennitaInizialeMinima","IndennitaInizialeMinima","IndennitaInizialeMinima",$IndennitaInizialeMinima,1,"brain_campiform",array("class"=>"'required'"),"","8","8"); 
                            
                                   $n++;
                                    print("<br style=\"clear:both;\"/>");
                               }
                               
                               print("<br style=\"clear:both;\"/>");
                 
                            
                            
                          
                     ?>                                      
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(0) ?>
                
	</form>-->
		<?
    
    $db->Close();   
}



function show_list()
{
global $user,$HtmlCommon;
$HtmlCommon->html_titolo_pagina("Elenco organismi");
$HtmlCommon->html_titolo_box("Elenco organismi");
$db= new Database();
$db->connect();


global $user,$HtmlCommon,$db,$ModuloId;    
 

/*$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','odc','odc.php?do=add','aggiungi organismo');
  */              



include_once("organismo_datatable.php");

?>

                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
            
            	<tr class="brain_tabellaTr">
                      
			<th width="80%">Organismo</th>
		        <th width="20%">n.autorizzazione</th>
                        <th width="5%">edita</th>
                      
		</tr>
            
		<tr class="brain_tabellaFilter">
		      
                       
			<th><input type="text" /></th> 
                        <th><input type="text" /></th> 
			<th><input type="hidden" /></th> 
			
		</tr>
	</thead>
	<tbody>
         
		<tr>
                        
			<td colspan="9" class="dataTables_empty">Caricamento in corso...</td>
                       
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



function cerca_mediatore()
{
$db= new Database();
$db->connect();

include_once("mediatore_datatable_cerca.php");
global $user,$HtmlCommon,$ModuloId;

$HtmlCommon->html_titolo_pagina("Ricerca mediatore",0,"","");
$HtmlCommon->html_titolo_box("Ricerca mediatore");
/*
$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','anagraficaest','anagraficaest.php?do=add','aggiungi anagrafica est');
*/
?>


            

<!--<div><a href="javascript:void(0);" onclick="ExternalLoad('anagrafica','anagrafica.php?do=add',this);" title="Crea Anagrafica">aggiungi anagrafica</a></div>-->
                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
            
            	<tr class="brain_tabellaTr">
                        <th width="10%">tipo</th>
			<th width="15%">cognome</th>
                        <th width="15%">nome</th>
			<th width="10%">codice fiscale</th>
			<th width="10%">partita iva</th>
			<th width="25%">indirizzo</th>
			<th width="15%">residenza</th>
		</tr>
            
		<tr class="brain_tabellaFilter">
			<th><span></span><input type="hidden" /></th>
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
			<td colspan="7" class="dataTables_empty">Caricamento in corso...</td>
		</tr>
	</tbody>
	<tfoot> 
		<tr> 
			<td colspan="7"></td>
		</tr> 
	</tfoot> 
</table>
<?
}




function edit($OrganismoId)
{
    
    global $organismo_wizard,$db,$user;   
    $organismo_wizard=new Odc($OrganismoId);

    
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);
    add(1);
}


function carica_menu_organismo($step_corrente,$mod)
{
global $abilita_modifica,$organismo_wizard,$db;
$organismo_wizard->conn=$db;
//$menu=$organismo_wizard->getMenuWizard();

   
$menu=array(
    1=>"Dati generali",
    2=>"Impostazioni emails",
   // 3=>"Tariffario",
    3=>"Impostazioni Avanzate"
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
                                             <a href="javascript:void(0);" onclick="loadMediazioneStep('organismo','organismo.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
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
 
global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create"; 

        
        
if (is_object($organismo_wizard) and ($organismo_wizard->OdcId))
{
  
    $OrganismoId=$organismo_wizard->OdcId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $organismo_wizard->conn=$db;
    $organismo_wizard->inizializzaDatiGenerali();
    $arr_odc=$organismo_wizard->OdcDatiGenerali;
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);
    
   // print_r($row);
    if($arr_odc['OdcId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}    



include_once("organismo_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			<div class="brain_formModifica">
                           
                            <h2><span class="brain_colorh2"><?=$organismo_wizard->OdcDatiGenerali['Odc']?> - </span>Dati generali</h2>
                        
                           <div class="brain_data-content">                  
                            
			  <? 
                             
                                 $page->create_textbox("Organismo","Organismo[Odc]","Organismo[Odc]",$arr_odc['Odc'],1,"brain_campiform",array("class"=>"'required'"));           
                                // $page->create_textbox("N.autorizzazione","Organismo[NumeroAutorizzazione]","Organismo[NumeroAutorizzazione]",$arr_odc['NumeroAutorizzazione'],1,"brain_campiform",array("class"=>"'required digits'"));           
                                
                                 $page->create_textbox("Responsabile","Organismo[Responsabile]","Organismo[Responsabile]",$arr_odc['Responsabile'],1,"brain_campiform",array("class"=>"'required'"));           
                                  print(" <br style=\"clear:both;\"/>");
                                  $page->create_textbox("Email","Organismo[Email]","Organismo[Email]",$arr_odc['Email'],1,"brain_campiform",array("class"=>"'required email'"));           
                                  $page->create_textbox("Email Pec","Organismo[EmailPec]","Organismo[EmailPec]",$arr_odc['EmailPec'],0,"brain_campiform",array("class"=>"'email'"));           
                                  $page->create_textbox("Sito Web","Organismo[SitoWeb]","Organismo[SitoWeb]",$arr_odc['SitoWeb'],0,"brain_campiform",null);           
                                 
                
                            
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
 
    
$step_corrente=3;
 global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 
$azione="create_disponibilita"; 
        
        
if (is_object($organismo_wizard) and ($organismo_wizard->OrganismoId))
{
  
    $OrganismoId=$organismo_wizard->OrganismoId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $organismo_wizard->conn=$db;
    $organismo_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$organismo_wizard->MediatoreDatiGenerali;
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);  
   // print_r($row);
    if($DatiGeneraliArr['OrganismoId'])
    {
        $azione="edit";     
        $action="update_disponibilita"; 
    }
}

include_once("organismo_validator.php");
?>

<form id="application_form" name="application_form"  method="post" action="#">
    <div class="brain_formModifica">
         <h2><span class="brain_colorh2"><?=$organismo_wizard->OdcDatiGenerali['Odc']?> - </span>GESTIONE TARIFFE</h2>
         <p>La gestione del tariffario non è disponibile.</p>
<?
 $page->create_textbox_hidden("step_corrente",$step_corrente);
 $page->create_textbox_hidden("step_successivo",1);

?>
    </div>
    <? 
    spara_pulsanti_wizard(3); 
    ?>
    </form>
  

<?
 $db->Close();   
}



function add_step_impostazioni_email()
{
 
    
$step_corrente=2;
 
global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();


 
$action="update_impostazioni_email"; 
        
        
if (is_object($organismo_wizard) and ($organismo_wizard->OdcId))
{
  
    global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create"; 

        
        
if (is_object($organismo_wizard) and ($organismo_wizard->OdcId))
{
  
    $OrganismoId=$organismo_wizard->OdcId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $organismo_wizard->conn=$db;
    $organismo_wizard->inizializzaDatiGenerali();
    $arr_odc=$organismo_wizard->OdcDatiGenerali;
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);
    
   // print_r($row);
    if($arr_odc['OdcId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}  
    
  
}    

$arr_stato[]= array("MedNotId" => '0',"MedNot" => 'NON inviare la mail al mediatore in fase di nomina');
$arr_stato[]= array("MedNotId" => '1',"MedNot" => 'Invia una email al mediatore in fase di nomina');

include_once("organismo_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			<div class="brain_formModifica">
                             <h2><span class="brain_colorh2"><?=$organismo_wizard->OdcDatiGenerali['Odc']?> - </span>Impostazioni Email</h2>
                           <div class="brain_data-content">                  
                            
				
                                
                               <? 
                               
                            
                            print("<h3>Impostazioni Email (Opzionale)</h3>"); 
                            
                 print("<br style=\"clear:both;\"/>");
                   print("<p><strong>Inserisci il nome del mittente delle comunicazioni inviate dal sistema.</strong></p>"); 
                
                 $page->create_textbox("Intestazione Mittente","Organismo[EmailSmtp]","Organismo[NomeEmailSmtp]",$arr_odc['NomeEmailSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 
                 /*$page->create_textbox("Mittente Email","Organismo[EmailSmtp]","Organismo[EmailSmtp]",$arr_odc['EmailSmtp'],1,"brain_campiform",array("class"=>"'required email'"));           
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox("Server Smtp","Organismo[ServerSmtp]","Organismo[ServerSmtp]",$arr_odc['ServerSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 $page->create_textbox("Porta Smtp","Organismo[PortaSmtp]","Organismo[PortaSmtp]",$arr_odc['PortaSmtp'],1,"brain_campiform",array("class"=>"'required digits'"));           
                print("<br style=\"clear:both;\"/>");
                 $page->create_textbox("Username","Organismo[UserSmtp]","Organismo[UserSmtp]",$arr_odc['UserSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 $page->create_textbox_password("Password","Organismo[PwdSmtp]","Organismo[PwdSmtp]",$arr_odc['PwdSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 */
                  
                   print("<br style=\"clear:both;\"/>");
                  
                 
                 
                 print("<h3>Notifiche via Email</h3>"); 
                            
                 print("<br style=\"clear:both;\"/>");
                  print("<p><strong>Inserisci l'indirizzo email a cui deve pervenire la comunicazione che sul sistema è stata caricata una nuova mediazione.</strong></p>"); 
                 
                 $page->create_textbox("Nuova Mediazione","Organismo[EmailsNotificaNuovaMediazione]","Organismo[EmailsNotificaNuovaMediazione]",$arr_odc['EmailsNotificaNuovaMediazione'],0,"brain_campiform",array("class"=>"'email'"));           
                 print("<br style=\"clear:both;\"/>");
                
               /*  print("<p><strong>Imposta l'indirizzo email a cui deve pervenire la comunicazione che sul sistema è stata caricata una nuova mediazione in attesa di approvazione.</strong></p>"); 
              
                 $page->create_textbox("Mediazioni in Approvazione","Organismo[EmailPerApprovazione]","Organismo[EmailPerApprovazione]",$arr_odc['EmailPerApprovazione'],0,"brain_campiform",array("class"=>"'email'"));           
                  print("<br style=\"clear:both;\"/>");*/
                 
                  print("<p><strong>Definisci se la comunicazione per la nomina di mediatore deve essere spedita via email.</strong></p>"); 
              
                  $page->create_select("Nomina Mediatore via email","Organismo[EmailsNotificaAlMediatore]","Organismo[EmailsNotificaAlMediatore]","brain_campiform",$arr_stato,$arr_odc['EmailsNotificaAlMediatore'],"MedNotId","MedNot",
                         array("class"=>"'required'"),1);
                 
                 print("<br style=\"clear:both;\"/>");
                            
                            
                          
                     ?>                                      
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(2) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}


function add_step_impostazioni_avanzate()
{
 
    
$step_corrente=3;
 
global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();


 
$action="update_impostazioni_email"; 
        
        
if (is_object($organismo_wizard) and ($organismo_wizard->OdcId))
{
  
    global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 $action="create"; 

        
        
if (is_object($organismo_wizard) and ($organismo_wizard->OdcId))
{
  
    $OrganismoId=$organismo_wizard->OdcId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $organismo_wizard->conn=$db;
    $organismo_wizard->inizializzaDatiGenerali();
    $arr_odc=$organismo_wizard->OdcDatiGenerali;
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);
    
   // print_r($row);
    if($arr_odc['OdcId'])
    {
        $azione="edit";     
        $action="update"; 
    }
                         
}  
    
  
}    

$arr_stato[]= array("MedNotId" => '0',"MedNot" => 'Protocollo Autonomo');
$arr_stato[]= array("MedNotId" => '1',"MedNot" => 'Protocollo Automatico');

include_once("organismo_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",0);
                        ?>
                            
                  
			<div class="brain_formModifica">
                             <h2><span class="brain_colorh2"><?=$organismo_wizard->OdcDatiGenerali['Odc']?> - </span>Impostazioni Email</h2>
                           <div class="brain_data-content">                  
                            
				
                                
                               <? 
                               
  $ProgressivoOdc=$arr_odc['ProssimoNumeroProtocollo'];
 
  $y=date('Y');
   $annoProgressivoOdc=$y;
if ($arr_odc['ProssimoNumeroProtocollo']==0)
{
$sql="Select NumeroMediazioni from MediazioniConteggioPerOdc where OdcIdRef=$user->OdcId and AnnoPresentazioneIstanza='$y'";
$row1 = $db->query_first($sql);
$ProgressivoOdc=1;
 
if($row1['NumeroMediazioni']>0)
   $ProgressivoOdc=$row1['NumeroMediazioni']+1;
}     


$ProssimoNumeroFattura=1;
if ($arr_odc['ProssimoNumeroFattura']>0)
{
    $ProssimoNumeroFattura=$arr_odc['ProssimoNumeroFattura'];
} 
else
   {
$sql = "SELECT * from MediazioneProssimoNumeroFattura where OdcIdRef=$user->OdcId";
$row1 = $db->query_first($sql);

if (!empty($row1['ProssimoNumeroFattura']))
{
    $ProssimoNumeroFattura=$row1['ProssimoNumeroFattura'];
    
}
    
    
} 
    
                               
                   print("<h3>Impostazioni Fattura (opzionale)</h3>"); 
                            
                   print("<br style=\"clear:both;\"/>");
                   print("<p><strong>Indicare il prossimo numero di fattura che dovrà essere generato dal sistema. Una volta impostato il numero iniziale il sistema adotterà, per le fatture successive, il numero progressivo consecutivo.</strong></p>"); 
                
                   $page->create_textbox("Prossimo Numero Fattura","Organismo[ProssimoNumeroFattura]","Organismo[ProssimoNumeroFattura]",$ProssimoNumeroFattura,0,"brain_campiform",array("class"=>"'digits'"));   
                   print("<br style=\"clear:both;\"/>");
                  
                   print("<h3>Impostazioni Protocollo (opzionale)</h3>"); 
                   print("<p><strong>Indicare il numero progressivo e l'anno del prossimo numero protocollo.  Una volta impostato il numero iniziale il sistema adotterà, per le procedure successive, il numero progressivo consecutivo.</strong></p>"); 
                
                  $page->create_textbox("Prossimo Numero Protocollo","Organismo[ProssimoNumeroProtocollo]","Organismo[ProssimoNumeroProtocollo]",$ProgressivoOdc,0,"brain_campiform",array("class"=>"'digits'"));   
                  $page->create_textbox("Anno Protocollo","Organismo[ProssimoNumeroProtocolloAnno]","Organismo[ProssimoNumeroProtocolloAnno]",$annoProgressivoOdc,0,"brain_campiform",array("class"=>"'digits'"));     
                 
                 
                 /*$page->create_textbox("Mittente Email","Organismo[EmailSmtp]","Organismo[EmailSmtp]",$arr_odc['EmailSmtp'],1,"brain_campiform",array("class"=>"'required email'"));           
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox("Server Smtp","Organismo[ServerSmtp]","Organismo[ServerSmtp]",$arr_odc['ServerSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 $page->create_textbox("Porta Smtp","Organismo[PortaSmtp]","Organismo[PortaSmtp]",$arr_odc['PortaSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                print("<br style=\"clear:both;\"/>");
                 $page->create_textbox("Username","Organismo[UserSmtp]","Organismo[UserSmtp]",$arr_odc['UserSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 $page->create_textbox_password("Password","Organismo[PwdSmtp]","Organismo[PwdSmtp]",$arr_odc['PwdSmtp'],1,"brain_campiform",array("class"=>"'required'"));           
                 
                  */
                 
                            
                            
                          
                     ?>                                      
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(2) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}

function add_step_competenza_materie()
{
    
$step_corrente=3;
 
global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

$materia=new Materia(null);
$materia->conn=$db;
$arr_materia=$materia->getAll();
 
$action="create_competenza_materie"; 
        

if (is_object($organismo_wizard) and ($organismo_wizard->OrganismoId))
{
  
    $OrganismoId=$organismo_wizard->OrganismoId;
    
    
    $organismo_wizard->conn=$db;
    $mat=$organismo_wizard->inizializzaCompetenzaMaterie();
    $MediatoreMaterie=$organismo_wizard->MediatoreMaterie;
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);
    
   
}    



include_once("organismo_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			<div class="brain_formModifica">
                             <h2><span class="brain_colorh2"><?=$organismo_wizard->MediatoreDatiGenerali['CognomeRagioneSociale']." ".$organismo_wizard->MediatoreDatiGenerali['Nome']?> - </span>Competenza Materie</h2>
                           <div class="brain_data-content">                  
                            
				
                                
                               <?
                               $numero_materie=0;
                               $oldtipo="";
                               while ($numero_materie<sizeof($arr_materia))
                               {
                                   
                                   //print($arr_materia[$numero_materie]['Materia']);
                                   $materia=$arr_materia[$numero_materie]['Materia'];
                                   $materiaid=$arr_materia[$numero_materie]['MateriaId'];
                                   $MateriaTipoId=$arr_materia[$numero_materie]['MateriaTipoId'];
                                   $valorecompetenza=0;
                                   if (isset($MediatoreMaterie))
                                   {
                                            foreach($MediatoreMaterie as $pos => $val)
                                              {
                                              if($val['MateriaId']==$materiaid)
                                                  $valorecompetenza=$val['CompetenzaPercentuale'];
                                                
                                                
                                              }
                                       
                                       
                                   }
                                   
                                   if ($MateriaTipoId!=$oldtipo)
                                   {
                                       $oldtipo=$MateriaTipoId;
                                       $m="Opzionali";
                                       if ($MateriaTipoId==1)
                                           $m="Obbligatorie";
                                      
                                      print("<br style=\"clear:both;\"/>");  
                                      print("<h3>Materie ".$m."</h3>"); 
                                   
                                      
                                   }
                                           
                                       
                                  
                                   $page->create_textbox($materia,"Materia".$materiaid,$materiaid,$valorecompetenza,0,"brain_campoForm",array("class"=>"'numeric'"),"","4","4","% di competenza");
                            
                                   $numero_materie++;
                               }
                             print("<br style=\"clear:both;\"/>");
                             
                               
                               
                             // form_tipo1($azione, $OrganismoId);
                            
                          ?>
                               
                               
             
                <br style="clear:both;"/>                                 
                </div></div>
                            <? spara_pulsanti_wizard(2) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}


function add_step_disponibilita()
{
 $step_corrente=4;
 
global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

 $azione="add"; 
 
$azione="create_disponibilita"; 
        
        
if (is_object($organismo_wizard) and ($organismo_wizard->OrganismoId))
{
  
    $OrganismoId=$organismo_wizard->OrganismoId;
    
    /*$sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
    $row = $db->query_first($sql);*/
    
    $organismo_wizard->conn=$db;
    $organismo_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$organismo_wizard->MediatoreDatiGenerali;
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);  
   // print_r($row);
    if($DatiGeneraliArr['OrganismoId'])
    {
        $azione="edit";     
        $action="update_disponibilita"; 
    }
}

include_once("organismo_validator.php");
?>

<form id="application_form" name="application_form"  method="post" action="#">
    <div class="brain_formModifica">
         <h2><span class="brain_colorh2"><strong><?=$DatiGeneraliArr['CognomeRagioneSociale']?> -</strong></span> Rapporto ODC</h2>
         <p>Dettaglio delle disponibilit&agrave; del mediatore nelle sede disponibili.</p>

        <div class="brain_data-content">                  
            <?
             $page->create_textbox_hidden("step_corrente",$step_corrente);
             $page->create_textbox_hidden("step_successivo",$step_corrente+1);
             include_once("mediatore_disponibilita_datatable.php");
            ?>
	    
	    <div class="mediazione_soggetto colorePI">
		

		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		    <caption>Elenco disponibilit&agrave;</caption>                
		    <thead>
			<tr class="brain_tabellaTr">
			    <th width="10%">data dal</th>
			    <th width="10%">data al</th>
                            <th width="15%">orario</th>                            
			    <th width="10%">giorno</th>
			    <th width="34%">comune</th>
                            <th width="3%">fest.</th>
                            <th width="3%">fer.</th>
			    <th width="10%">azione</th>                        
			</tr>
		    </thead>
		    <tbody>
			<tr>
			    <td colspan="8" class="dataTables_empty">Caricamento in corso...</td>
			</tr>
		    </tbody>
		    <tfoot>
			<tr> 
			    <th colspan="8" align="left" bgcolor="#EAEAEA"><a class="brain_add" href="javascript:void(0);" onclick="javascript:ExternalLoad('mediatore','mediatore.php?do=add_disponibilita');"><i class="fa fa-plus" aria-hidden="true"></i> Aggiungi disponibilit&agrave;</a></th> 
			</tr>
		    </tfoot>
		    
		</table>
	</div>                           
        </div>
    </div>
    <? 
    spara_pulsanti_wizard(3); 
    ?>
    </form>
  

<?
 $db->Close();   
}



function add_disponibilita()
{
    ?>
<script type="text/javascript"> 
    $(document).ready(function() {
        
        
   // Datepicker
	var d = new Date();
	$(function() {
		$( ".date" ).datepicker({
		monthNames:
				['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
				monthStatus: 'Mostra un altro mese',
				yearStatus: 'Mostra un altro anno',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: 'Settimana dell\'anno',
		dayNames:
				['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
				dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
				dayStatus: 'Imposta DD come primo giorno della settimana',
				dateStatus: 'Seleziona DD, M d',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: 'Seleziona una data',
                                dateFormat: 'dd/mm/yy'});
	});
	

 });

</script> 
<script language="javascript" type="text/javascript">
			   
/*
Auther :: Dharmendra Patri
Email  :: admin@icymic.com
Site   :: http://dpatri.com
  http://icymic.com
*/

//this will move selected items from source list to destination list     
function move_list_items(sourceid, destinationid)
{
 $("#"+sourceid+"  option:selected").appendTo("#"+destinationid);
if ( (sourceid=="giorniSelezionati") &&  ($("#"+sourceid+"  option").size()==0))
    $("#"+sourceid).addClass(("required"));


if ( (sourceid=="sediSelezionate") &&  ($("#"+sourceid+"  option").size()==0))
    $("#"+sourceid).addClass(("required"));
   
}

//this will move all selected items from source list to destination list
function move_list_items_all(sourceid, destinationid)
{
/*
if ( (sourceid=="giorniSelezionati") &&  ($("#"+sourceid+"  option").size()>1))
 $("#"+sourceid+"  option:selected").appendTo("#"+destinationid);
else
alert("E' necessario selezionare almeno un valore");

if ( (sourceid=="giorniSelezionare"))*/
        $("#"+sourceid+"  option").appendTo("#"+destinationid);
    if ( (sourceid=="giorniSelezionati") &&  ($("#"+sourceid+"  option").size()==0))
    $("#"+sourceid).addClass(("required"));


if ( (sourceid=="sediSelezionate") &&  ($("#"+sourceid+"  option").size()==0))
    $("#"+sourceid).addClass(("required"));
    

}

/*
@param1 - sourceid - This is the id of the multiple select box whose item has to be moved.
@param2 - destinationid - This is the id of the multiple select box to where the iterms should be moved.
*/


</script>
<?
global $organismo_wizard,$user,$db,$HtmlCommon;
$page=new Form();
 $dt=new DT();


$azione="add";  
$action="create_disponibilita"; 

$sede=new Sede();

$sede->conn=$db;
 
$arr_sede=$sede->getSediByOdc($user->OdcId);



$HtmlCommon->html_titolo_box("Aggiungi disponibilità");


if (is_object($organismo_wizard) and ($organismo_wizard->OrganismoId))
{
  
    $OrganismoId=$organismo_wizard->OrganismoId;    
    $organismo_wizard->conn=$db;
    $organismo_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$organismo_wizard->MediatoreDatiGenerali;
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);
   
}    

include_once("organismo_validator_stop.php");
?>
<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter"> 			
                        <form class="nogo" id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("Dispo[OrganismoId]",$OrganismoId);
                        ?>                                          
			<div id="elencosoggetti" class="brain_formModifica">

                        <h2><span class="brain_colorh2"><strong><?=$DatiGeneraliArr['CognomeRagioneSociale']?> -</strong></span> Disponibilit&agrave;</h2>
                        <p>Indicare la disponibilita' del mediatore nelle sede disponibili. E' possibile aggiungere una o piu' disponibilita'.</p>
			<div class="brain_data-content" id="brain_data-content">
                    <div class="mediazione_soggetto coloreBlu" id="soggetto_1">
                            <div class="titolo_disponibilita">
                                    <h3>Dettagli disponibilita'</h3>
                            </div>
                            <div class="brain_boxInternoForm">
                                    <div class="brain_campoForm">
                                            <span class="titoloCampi">periodo:</span>
                                            <label for="periodoDal">dal</label>                                           
                                            <input type="text" class="required valid date" value="" id="Dispo[DispoDataDal]" name="Dispo[DispoDataDal]" maxlength="255" size="8">
                                            <label for="periodoAl">dal</label>
                                            <input type="text" class="required valid date" value="" id="Dispo[DispoDataAll]" name="Dispo[DispoDataAl]" maxlength="255" size="8">
                                            <input type="checkbox" value="checkbox" id="Dispo[IncludiFeriali]" name="Dispo[IncludiFeriali]">
                                            <label for="giorniFeriali">includi giorni feriali</label>
                                            <input type="checkbox" value="checkbox" id="Dispo[IncludiFestivi]" name="Dispo[IncludiFestivi]">
                                            <label for="giorniFestivi">includi giorni festivi</label>
                                    </div>	
                                    <div class="brain_campoForm">
                                            <span class="titoloCampi">orario:</span>
                                            <label for="orarioDalle">dalle</label>
                                            <select id="Dispo[DispoOreDal]" name="Dispo[DispoOreDal]" class="required">
                                                    <option value="" selected="">-- : --</option>
                                                    <option value="08:00">08:00</option>
                                                    <option value="09:00">09:00</option>
                                                    <option value="10:00">10:00</option>
                                                    <option value="11:00">11:00</option>
                                                    <option value="12:00">12:00</option>
                                                    <option value="13:00">13:00</option>
                                                    <option value="14:00">14:00</option>
                                                    <option value="15:00">15:00</option>
                                                    <option value="16:00">16:00</option>
                                                    <option value="17:00">17:00</option>
                                                    <option value="18:00">18:00</option>
                                                    <option value="19:00">19:00</option>
                                                    <option value="20:00">20:00</option>
                                            </select>
                                            <label for="orarioAlle">dal</label>
                                            <select id="Dispo[DispoOreAl]" name="Dispo[DispoOreAl]" class="required">
                                                    <option value="" selected="">-- : --</option>
                                                    <option value="08:00">08:00</option>
                                                    <option value="09:00">09:00</option>
                                                    <option value="10:00">10:00</option>
                                                    <option value="11:00">11:00</option>
                                                    <option value="12:00">12:00</option>
                                                    <option value="13:00">13:00</option>
                                                    <option value="14:00">14:00</option>
                                                    <option value="15:00">15:00</option>
                                                    <option value="16:00">16:00</option>
                                                    <option value="17:00">17:00</option>
                                                    <option value="18:00">18:00</option>
                                                    <option value="19:00">19:00</option>
                                                    <option value="20:00">20:00</option>
                                            </select>
                                    </div>	
                                    <div class="brain_campoForm">
                                            <span class="titoloCampi">giorni settimana:</span>
                                            <div class="multiSelectLeft">
                                                    <select id="giorniSelezionare" multiple="multiple" >
                                                            <option value="1">Luned&igrave;</option>
                                                            <option value="2">Marted&igrave;</option>
                                                            <option value="3">Mercoled&igrave;</option>
                                                            <option value="4">Gioved&igrave;</option>
                                                            <option value="5">Venerd&igrave;</option>
                                                            <option value="6">Sabato</option>
                                                            <option value="7">Domenica</option>
                                                    </select>
                                            </div>
                                            <div class="multiSelectCenter">
                                                   <!-- <input id="moverightall" type="button" value=">>" onclick="move_list_items_all('giorniSelezionare','giorniSelezionati');" /><br />-->
                                                    <input id="moveright" type="button" value=">" onclick="move_list_items('giorniSelezionare','giorniSelezionati');" /><br />
                                                    <input id="moveleft" type="button" value="<" onclick="move_list_items('giorniSelezionati','giorniSelezionare');" /><br />
                                                   <!-- <input id="moveleftall" type="button" value="<<" onclick="move_list_items_all('giorniSelezionati','giorniSelezionare');" />-->
                                            </div>
                                            <div class="multiSelectRight">
                                                    <select id="giorniSelezionati" multiple="multiple" name="DispoGiorni[GiornoId][]" class="required">
                                                    </select>
                                            </div>
                                    </div>	
                                    <div class="brain_campoForm">
                                            <span class="titoloCampi">sedi:</span>
                                            <div class="multiSelectLeft">
                                                    <select id="sediSelezionare" multiple="multiple" >
                                                        <?
                                                        foreach ($arr_sede as &$value) {
                                                            ?>
                                                             <option value="<?=$value['SedeId']?>"><?=$value['SedeComune']?> - <?=$value['SedeIndirizzo']?></option>
                                                             <?
                                                            }       
                                                        ?>                                                            
                                                    </select>
                                            </div>
                                            <div class="multiSelectCenter">
                                                   <!-- <input id="moverightall" type="button" value=">>" onclick="move_list_items_all('sediSelezionare','sediSelezionate');" /><br />-->
                                                    <input id="moveright" type="button" value=">" onclick="move_list_items('sediSelezionare','sediSelezionate');" /><br />
                                                    <input id="moveleft" type="button" value="<" onclick="move_list_items('sediSelezionate','sediSelezionare');" /><br />
                                                   <!-- <input id="moveleftall" type="button" value="<<" onclick="move_list_items_all('sediSelezionate','sediSelezionare');" />-->
                                            </div>
                                            <div class="multiSelectRight">
                                                    <select id="sediSelezionate" multiple="multiple" name="DispoSedi[SedeId][]" class="required">
                                                    </select>
                                            </div>
                                    </div>	
                            <br style="clear:both;">
                            </div>	

                    </div>							
							<!-- fine soggetto -->
                </div>
                </div>
                <? spara_pulsanti_wizard_box() ?>
                
	</form>
</div>	
 
<?  
 $db->Close();   
}

function edit_disponibilita($MediatoreDisponibilitaId)
{
    ?>
<script type="text/javascript"> 
    $(document).ready(function() {
        
        
   // Datepicker
	var d = new Date();
	$(function() {
		$( ".date" ).datepicker({
		monthNames:
				['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
				monthStatus: 'Mostra un altro mese',
				yearStatus: 'Mostra un altro anno',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: 'Settimana dell\'anno',
		dayNames:
				['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
				dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
				dayStatus: 'Imposta DD come primo giorno della settimana',
				dateStatus: 'Seleziona DD, M d',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: 'Seleziona una data',
                                dateFormat: 'dd/mm/yy'});
	});
	

 });

</script> 
<script language="javascript" type="text/javascript">
			   
/*
Auther :: Dharmendra Patri
Email  :: admin@icymic.com
Site   :: http://dpatri.com
  http://icymic.com
*/

//this will move selected items from source list to destination list     
function move_list_items(sourceid, destinationid)
{
 $("#"+sourceid+"  option:selected").appendTo("#"+destinationid);
if ( (sourceid=="giorniSelezionati") &&  ($("#"+sourceid+"  option").size()==0))
    $("#"+sourceid).addClass(("required"));


if ( (sourceid=="sediSelezionate") &&  ($("#"+sourceid+"  option").size()==0))
    $("#"+sourceid).addClass(("required"));
   
}

//this will move all selected items from source list to destination list
function move_list_items_all(sourceid, destinationid)
{
/*
if ( (sourceid=="giorniSelezionati") &&  ($("#"+sourceid+"  option").size()>1))
 $("#"+sourceid+"  option:selected").appendTo("#"+destinationid);
else
alert("E' necessario selezionare almeno un valore");

if ( (sourceid=="giorniSelezionare"))*/
        $("#"+sourceid+"  option").appendTo("#"+destinationid);
    if ( (sourceid=="giorniSelezionati") &&  ($("#"+sourceid+"  option").size()==0))
    $("#"+sourceid).addClass(("required"));


if ( (sourceid=="sediSelezionate") &&  ($("#"+sourceid+"  option").size()==0))
    $("#"+sourceid).addClass(("required"));
    

}

/*
@param1 - sourceid - This is the id of the multiple select box whose item has to be moved.
@param2 - destinationid - This is the id of the multiple select box to where the iterms should be moved.
*/


</script>
<?
global $organismo_wizard,$user,$db,$HtmlCommon;


$sql="SELECT * FROM MediatoreDisponibilita WHERE MediatoreDisponibilitaId=$MediatoreDisponibilitaId";
$row1=$db->query_first($sql);

$page=new Form();
$HtmlCommon->html_titolo_box("Modifica disponibilità");
 $dt=new DT();
$dataDal=$dt->format($row1['DispoDataDal'],"Y-m-d", "d/m/Y");
$dataAl=$dt->format($row1['DispoDataAl'],"Y-m-d", "d/m/Y");

$azione="update";  
$action="update_disponibilita"; 

$sede=new Sede();
$sede->conn=$db;
$arr_sede=$sede->getSediByOdc($user->OdcId);

if (is_object($organismo_wizard) and ($organismo_wizard->OrganismoId))
{
  
    $OrganismoId=$organismo_wizard->OrganismoId;    
    $organismo_wizard->conn=$db;
    $organismo_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$organismo_wizard->MediatoreDatiGenerali;
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);
   
}    

include_once("organismo_validator_stop.php");
?>
<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter"> 			
                        <form class="nogo" id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("MediatoreDisponibilitaId",$MediatoreDisponibilitaId);
                           $page->create_textbox_hidden("Dispo[OrganismoId]",$OrganismoId);
                        ?>                                          
			<div id="elencosoggetti" class="brain_formModifica">

                        <h2><span class="brain_colorh2"><strong><?=$DatiGeneraliArr['CognomeRagioneSociale']?> -</strong></span> Disponibilit&agrave;</h2>
                        <p>Indicare la disponibilita' del mediatore nelle sede disponibili. E' possibile aggiungere una o piu' disponibilita'.</p>
			<div class="brain_data-content" id="brain_data-content">
                    <div class="mediazione_soggetto coloreBlu" id="soggetto_1">
                            <div class="titolo_disponibilita">
                                    <h3>Dettagli disponibilita'</h3>
                            </div>
                            <div class="brain_boxInternoForm">
                                    <div class="brain_campoForm">
                                            <span class="titoloCampi">periodo:</span>
                                            <label for="periodoDal">dal</label>                                           
                                            <input type="text" class="required valid date" value="<?=$dataDal?>" id="Dispo[DispoDataDal]" name="Dispo[DispoDataDal]" maxlength="255" size="8">
                                            <label for="periodoAl">dal</label>
                                            <input type="text" class="required valid date" value="<?=$dataAl?>" id="Dispo[DispoDataAll]" name="Dispo[DispoDataAl]" maxlength="255" size="8">
                                            <input type="checkbox" value="checkbox" id="Dispo[IncludiFeriali]" name="Dispo[IncludiFeriali]">
                                            <label for="giorniFeriali">includi giorni feriali</label>
                                            <input type="checkbox" value="checkbox" id="Dispo[IncludiFestivi]" name="Dispo[IncludiFestivi]">
                                            <label for="giorniFestivi">includi giorni festivi</label>
                                    </div>                               
                                    <div class="brain_campoForm">
                                            <span class="titoloCampi">orario:</span>
                                            <label for="orarioDalle">dalle</label>
                                            <select id="Dispo[DispoOreDal]" name="Dispo[DispoOreDal]" class="required">
                                                    <option value="" selected="">-- : --</option>
                                                    <?
                                                    for($i=8;$i<21;$i++){
                                                        if($i<10) 
                                                            $ora="0".$i.":00";
                                                        else
                                                            $ora=$i.":00";
                                                    ?>
                                                    <option value="<?=$ora?>" <?if($row1['DispoOreDal']==$ora) echo("selected='selected'");?>><?=$ora?></option>
                                                    <?
                                                    }
                                                    ?>                                     
                                            </select>
                                            <label for="orarioAlle">dal</label>
                                            <select id="Dispo[DispoOreAl]" name="Dispo[DispoOreAl]" class="required">
                                                    <option value="" selected="">-- : --</option>
                                                    <?
                                                    for($i=8;$i<21;$i++){
                                                        if($i<10) 
                                                            $ora="0".$i.":00";
                                                        else
                                                            $ora=$i.":00";
                                                    ?>
                                                    <option value="<?=$ora?>" <?if($row1['DispoOreAl']==$ora) echo("selected='selected'");?>><?=$ora?></option>
                                                    <?
                                                    }
                                                    ?>                                                      
                                            </select>
                                    </div>	
                                    <div class="brain_campoForm">
                                            <span class="titoloCampi">giorni settimana:</span>
                                            <div class="multiSelectLeft">
                                                    <select id="giorniSelezionare" multiple="multiple" >
                                                        <?
                                                        $sql1="SELECT MediatoreDisponibilitaId,GiornoId FROM ViewDisponibilitaMediatore WHERE MediatoreDisponibilitaId=$MediatoreDisponibilitaId GROUP BY MediatoreDisponibilitaId,GiornoId ORDER BY GiornoId ASC";
                                                        $queryid1 = $db->query($sql1);
                                                        $giorni=array();
                                                        while($row1=$db->fetch($queryid1)){
                                                            array_push($giorni,$row1['GiornoId']);                                                            
                                                        }                                                        
                                                        for($i=1;$i<8;$i++){
                                                        if(!in_array($i,$giorni)){
                                                            switch($i) {
                                                                case "1":
                                                                    echo("<option value=\"1\">Luned&igrave;</option>");
                                                                break;
                                                                case "2":
                                                                        echo("<option value=\"2\">Marted&igrave;</option>");
                                                                    break;
                                                                case "3":
                                                                        echo("<option value=\"3\">Mercoled&igrave;</option>");
                                                                    break;
                                                                case "4":
                                                                        echo("<option value=\"4\">Gioved&igrave;</option>");
                                                                    break;
                                                                case "5":
                                                                        echo("<option value=\"5\">Venerd&igrave;</option>");
                                                                    break;
                                                                case "6":
                                                                        echo("<option value=\"6\">Sabato</option>");
                                                                    break;
                                                                case "7":
                                                                        echo("<option value=\"7\">Domenica</option>");
                                                                    break;
                                 
                                                                }
                                                            }
                                                        }
                                                        ?>                                                       
                                                    </select>
                                            </div>                                            
                                            <div class="multiSelectCenter">
                                                   <!-- <input id="moverightall" type="button" value=">>" onclick="move_list_items_all('giorniSelezionare','giorniSelezionati');" /><br />-->
                                                    <input id="moveright" type="button" value=">" onclick="move_list_items('giorniSelezionare','giorniSelezionati');" /><br />
                                                    <input id="moveleft" type="button" value="<" onclick="move_list_items('giorniSelezionati','giorniSelezionare');" /><br />
                                                   <!-- <input id="moveleftall" type="button" value="<<" onclick="move_list_items_all('giorniSelezionati','giorniSelezionare');" />-->
                                            </div>                                            
                                            <div class="multiSelectRight">
                                                    <select id="giorniSelezionati" multiple="multiple" name="DispoGiorni[GiornoId][]" <? if (sizeof($giorni)==0) echo ("class=\"required\"")?> >
                                                    <?
                                                    $day="";
                                                    for($i=1;$i<8;$i++){
                                                        if(in_array($i,$giorni)){
                                                            switch($i) {
                                                                case "1":
                                                                    echo("<option value=\"1\">Luned&igrave;</option>");
                                                                    $day.="1,";
                                                                break;
                                                                case "2":
                                                                    echo("<option value=\"2\">Marted&igrave;</option>");
                                                                    $day.="2,";
                                                                    break;
                                                                case "3":
                                                                        echo("<option value=\"3\">Mercoled&igrave;</option>");
                                                                        $day.="3,";
                                                                    break;
                                                                case "4":
                                                                        echo("<option value=\"4\">Gioved&igrave;</option>");
                                                                    $day.="4,";
                                                                    break;
                                                                case "5":
                                                                        echo("<option value=\"5\">Venerd&igrave;</option>");
                                                                    $day.="5,";
                                                                    break;
                                                                case "6":
                                                                        echo("<option value=\"6\">Sabato</option>");
                                                                    $day.="6,";
                                                                    break;
                                                                case "7":
                                                                        echo("<option value=\"7\">Domenica</option>");
                                                                    $day.="7,";
                                                                    break;                                 
                                                            }
                                                        }
                                                    }
                                                    $page->create_textbox_hidden("giorniSel",$day);
                                                        ?>
                                                    </select>
                                            </div>
                                    </div>                                
                                    <div class="brain_campoForm">
                                            <span class="titoloCampi">sedi:</span>
                                            <div class="multiSelectLeft">
                                                    <select id="sediSelezionare" multiple="multiple" >
                                                        <?
                                                        $sql1="SELECT MediatoreDisponibilitaId,SedeId FROM ViewDisponibilitaMediatore WHERE MediatoreDisponibilitaId=$MediatoreDisponibilitaId GROUP BY MediatoreDisponibilitaId,SedeId ORDER BY SedeId ASC";
                                                        $queryid1 = $db->query($sql1);
                                                        $sedi=array();
                                                        while($row1=$db->fetch($queryid1)){
                                                            array_push($sedi,$row1['SedeId']);                                                            
                                                        }                                                                                                                
                                                        foreach ($arr_sede as &$value) {
                                                             if(!in_array($value['SedeId'],$sedi)){
                                                            ?>
                                                             <option value="<?=$value['SedeId']?>"><?=$value['SedeComune']?> - <?=$value['SedeIndirizzo']?></option>
                                                             <?
                                                            }
                                                        }
                                                        ?>                                                            
                                                    </select>
                                            </div>                                           
                                            <div class="multiSelectCenter">
                                                   <!-- <input id="moverightall" type="button" value=">>" onclick="move_list_items_all('sediSelezionare','sediSelezionate');" /><br />-->
                                                    <input id="moveright" type="button" value=">" onclick="move_list_items('sediSelezionare','sediSelezionate');" /><br />
                                                    <input id="moveleft" type="button" value="<" onclick="move_list_items('sediSelezionate','sediSelezionare');" /><br />
                                                   <!-- <input id="moveleftall" type="button" value="<<" onclick="move_list_items_all('sediSelezionate','sediSelezionare');" />-->
                                            </div>
                                            <div class="multiSelectRight">
                                                    <select id="sediSelezionate" multiple="multiple" name="DispoSedi[SedeId][]" <? if (sizeof($arr_sede)==0) echo ("class=\"required\"")?> >
                                                        <?
                                                        $sede="";
                                                        foreach ($arr_sede as &$value) {
                                                             if(in_array($value['SedeId'],$sedi)){
                                                                 $sede.=$value['SedeId'].",";
                                                                 ?>
                                                             <option value="<?=$value['SedeId']?>"><?=$value['SedeComune']?> - <?=$value['SedeIndirizzo']?></option>
                                                             <?
                                                            }
                                                        }
                                                        $page->create_textbox_hidden("sediSel",$sede);
                                                        ?>  
                                                    </select>
                                            </div>
                                    </div>	
                            <br style="clear:both;">
                            </div>	

                    </div>							
							<!-- fine soggetto -->
                </div>
                </div>
                <? spara_pulsanti_wizard_box() ?>
                
	</form>
</div>	
 
<?  
 $db->Close();   
}

function add_competenza_lingue()
{
    
$step_corrente=5;
 
global $organismo_wizard,$user,$db;

$page=new Form();
 $dt=new DT();

$lingua=new Lingua (null);
$lingua->conn=$db;
$arr_lingua=$lingua->getAll();
 
$action="create_competenza_lingue"; 
        

if (is_object($organismo_wizard) and ($organismo_wizard->OrganismoId))
{
  
    $OrganismoId=$organismo_wizard->OrganismoId;
    $organismo_wizard->conn=$db;
    $mat=$organismo_wizard->inizializzaCompetenzaLingue();
    $MediatoreLingue=$organismo_wizard->MediatoreLingue;
    
    $_SESSION['ORGANISMO_WIZARD']=serialize($organismo_wizard);
    
}    

include_once("organismo_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente);
                        ?>
                            
                  
			<div class="brain_formModifica">
                                <h2><span class="brain_colorh2"><?=$organismo_wizard->MediatoreDatiGenerali['CognomeRagioneSociale']." ".$organismo_wizard->MediatoreDatiGenerali['Nome']?> - </span>Competenza lingue</h2>
                           <div class="brain_data-content">                  
                            
				
                                
                               <?
                               $numero_lingue=0;
                               $oldtipo="";
                               while ($numero_lingue<sizeof($arr_lingua))
                               {
                                   
                                   //print($arr_materia[$numero_materie]['Materia']);
                                   $lingua=$arr_lingua[$numero_lingue]['Lingua'];
                                   $linguaid=$arr_lingua[$numero_lingue]['LinguaId'];
                                   
                                   $valorecompetenza=0;
                                   if (isset($MediatoreLingue))
                                   {
                                            foreach($MediatoreLingue as $pos => $val)
                                              {
                                              if($val['LinguaId']==$linguaid)
                                                  $valorecompetenza=$val['ConoscenzaLingua'];
                                                
                                                
                                              }
                                       
                                       
                                   }
                                   $page->create_textbox($lingua,"Lingua".$linguaid,$linguaid,$valorecompetenza,0,"brain_campoForm",array("class"=>"'numeric'"),"","4","4","% di competenza");
                            
                                   $numero_lingue++;
                               }
                             print("<br style=\"clear:both;\"/>");
                             
                               
                               
                             // form_tipo1($azione, $OrganismoId);
                            
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
 include_once("organismo_validator.php");  
 
 global $HtmlCommon,$db,$organismo_wizard,$funzione_edit,$abilita_modifica;





if (!$step)
{
$organismo_wizard=null;    
unset($organismo_wizard);
$_SESSION['ORGANISMO_WIZARD']=null;
unset($_SESSION['ORGANISMO_WIZARD']);
$step=1;
}
$mod=0;

if (is_object($organismo_wizard))
{
    
    $mod=1;
    $OrganismoId=$organismo_wizard->OdcId;
    $organismo_wizard->conn=$db;
    $organismo_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$organismo_wizard->OdcDatiGenerali;
    $abilita_modifica=true;
    $HtmlCommon->html_titolo_pagina("Editazione Organismo ".$DatiGeneraliArr['Odc'],0,"organismo","organismo.php");
} 
/*else
{    
    $mod=0;    
    $HtmlCommon->html_titolo_pagina("Aggiungi mediatore",1,"mediatore","mediatore.php");
               $abilita_modifica=false;
}*/
 
        carica_menu_organismo($step,$mod);
 
 ?>

		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
 <?       
 

if ($step==1)
add_step_dati_generali();
elseif ($step==2)
add_step_impostazioni_email();
elseif ($step==10)
add_step_tariffario();
elseif ($step==3)
add_step_impostazioni_avanzate();

 




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
    
         <a href="javascript:void(0);" onclick="loadMainContent('home','home.php',this);" title="Home" class="brain_annulla">Annulla compilazione</a>
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

function edit_documenti_allegati()
{
global $user,$HtmlCommon,$organismo_wizard,$db;    

$page=new Form();
$action="update_documento_allegato";
$MediazioneDocumentoId=$_REQUEST['MediazioneDocumentoId'];

$sql="SELECT * FROM ViewMediazioneDocumento WHERE MediazioneDocumentoId=$MediazioneDocumentoId";
$row1=$db->query_first($sql);

$SoggettoTipoId=$row1['SoggettoTipoId'];

if ($SoggettoTipoId==1)
    $parte="PARTE ISTANTE";
else
    $parte="CONTROPARTE";
$HtmlCommon->html_titolo_box("Edita documento ".$parte);
$MediazioneId=$organismo_wizard->OrganismoId;

$sql = "SELECT MediazioneSoggettoParteId,CognomeRagioneSociale from MediazioneSoggettoVersioneFattiView where MediazioneId=$MediazioneId  and MediazioneSoggettoParteTipoId=$SoggettoTipoId order by CognomeRagioneSociale asc";
$arr_soggetti_parte= $db->fetch_array($sql);

include_once("mediazione_validator_upload.php");  
?>
<div class="brain_row">
		<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">     
<form class="nogo" id="application_form" name="application_form"  method="post" action="/protected/modules/mediazione/mediazione_action.php">
                        <?
                           $page->create_textbox_hidden("action",$action);
                           $page->create_textbox_hidden("Allegato[SoggettoTipoId]",$SoggettoTipoId);
                           $page->create_textbox_hidden("MediazioneDocumentoId",$MediazioneDocumentoId);
                        ?>                            
                            
       <div class="brain_formModifica">
            <div class="brain_campoForm"><label for="Documento">Documento</label>
                <a href="protected/modules/modulo/genera_pdf.php?do=open_file&filename=<?=$row1['FileFisico']?>" title="<?=$row1['NomeFile']?>" ><?=$row1['NomeFile']?></a> 
            </div>
       
             <?
             if($row1['RiservatoMediatore']=="") 
                 $chk="";
             else
                 $chk="checked";
            
             //$page->create_input_file("Documento","Documento","Documento","",1,"brain_campoForm",array("class"=>"'required'"),"");
             $page->create_select("Soggetto","Allegato[SoggettoId]","Allegato[SoggettoId]","brain_campoForm required",$arr_soggetti_parte,$row1['SoggettoId'],"MediazioneSoggettoParteId","CognomeRagioneSociale", array("class"=>"'required'"),1,"");        
             $page->create_input_checkbox("Riservato","RiservatoMediatore","Allegato[RiservatoMediatore]","",0,"brain_campoForm",array($chk=>$chk),"");
             $page->create_texarea("Note","Note","Allegato[Note]",$row1['Note'],0,"brain_campoForm",array("cols"=>"'60'","rows"=>"'12'"),"");      
             ?>
          
                <br style="clear:both;"/>                                 
                </div>
               <? spara_pulsanti_wizard_box(); ?>
	</form>
                </div>
 </div>


<?
    
$db->Close();    
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
    if (is_object($organismo_wizard))
        $organismo_wizard->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
    		$do=$_REQUEST['do'];
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
                                           edit($_REQUEST['OrganismoId']);
                                        else
                                            $errore->stampa_errore(2);
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "edit_tariffario":
				 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                // print_r($permesso);
                                        if (sizeof($permesso))
                                           edit_tariffario();
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
                                           edit($user->OdcId);    
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