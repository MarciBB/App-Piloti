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
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");

global $ModuloId;
$ModuloId=18;// modulo base mediazione



function show_list()
{
global $user,$HtmlCommon,$ModuloId;
$HtmlCommon->html_titolo_pagina("Elenco comuni",0,"","");
$HtmlCommon->html_titolo_box("Elenco comuni");
$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','comune','comune.php?do=add','aggiungi comune');
include_once("comune_datatable.php");
?>



	
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
		    
			<tr class="brain_tabellaTr">
                              <th width="40%">comune</th>
			      <th width="20%">provincia</th>  
			      <th width="20%">regione</th>
                              <th width="20%">nazione</th>
                              <th width="20%">edit</th>
                           
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
		
		</table>
	

   <?
}


function cerca_comune()
{
global $db;
include_once("comune_datatable_cerca.php");
global $user,$HtmlCommon;
//$HtmlCommon->html_titolo_box("Ricerca comune");
$fieldtoupdate=$_REQUEST['fieldtoupdate'];
$labeltoupdate=$_REQUEST['labeltoupdate'];

?>

<input type="hidden" id="fieldtoupdate" value="<?=$fieldtoupdate?>" />
<input type="hidden" id="labeltoupdate" value="<?=$labeltoupdate?>" />            

<div class="SelezionaComune">
	
	<div class="SelezionaComuneHeader">
		<h2>Seleziona comune</h2>
		<a class="chiudi" href="javascript:void(0);" onclick="ChiudiElencoComuni();" title="Chiudi">Chiudi</a>
	</div>
	
	<div class="SelezionaComuneContent">
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
		    
			<tr class="brain_tabellaTr">
			      <th width="20%">nazione</th>
			      <th width="20%">regione</th>
			      <th width="20%">provincia</th>  
			      <th width="40%">comune</th>
		      </tr>
		    
			<tr class="brain_tabellaFilter">
			       <th><input type="text" /></th> 
			       <th><input type="text" /></th> 
			       <th><input type="text" /></th>
			       <th><input type="text" /></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="4" class="dataTables_empty">Caricamento in corso...</td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<th align="left" colspan="4"><a class="brain_add" href="javascript:void(0);" onclick="ExternalLoad('comune','comune.php?do=add',this);" title="Aggiungi Comune">aggiungi comune</a></th>
			</tr> 
		</tfoot> 
		</table>
	</div>
</div>
<?
   
}

function add()
{
    
global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  
$gestore=new Gestore();
$gestore->conn=$db;
$gestore->getGestoreAll($user->GestoreId);
$arr_gestori=$gestore->ArrGestore;
//print_r($arr_gestori);
$nazione=new Nazione(null);
$nazione->conn=$db;
$nazione->getAllNazione();
$arr_nazione=$nazione->ArrNazione;
include_once("comune_validator.php");       

$HtmlCommon->html_titolo_pagina("Aggiungi comune",1,"comune","comune.php");
$HtmlCommon->html_titolo_box("Aggiungi comune");


 $arr_stato[]= array("StatoId" => '0',"Stato" => 'Non Attivo');
 $arr_stato[]= array("StatoId" => '1',"Stato" => 'Attivo');

 
 
?>
 <div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                    
                            
                         <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica anagraficaSelector">
                                <div class="brain_data-content">    


        <?
        $page->create_textbox_hidden("action","create");
        $page->create_textbox_hidden_differentId("NazioneId","NazioneId",$ComuneResidenzaId);
        $selector="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'nazione','nazione.php?do=cerca&fieldtoupdate=NazioneId&labeltoupdate=Nazione','bottom');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona nazione\" border=\"0\" /></a>";
        $selector_ann="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"$('#NazioneId').val('0');$('#Nazione').val('');\" style=\"margin-left:2px\"><img src=\"/images/iconset/delete_gray.gif\" alt=\"cancella campo\"  border=\"0\" /></a>";
        $selector.=$selector_ann; 
        $page->create_textbox_with_sel("Nazione","Nazione","Nazione","",1,"brain_campoForm",array("class"=>"'required'","readonly"=>"'readonly'"),$selector);
        
        print("<br style=\"clear:both;\"/>");
                                 
                           
        $page->create_textbox_hidden_differentId("RegioneId","RegioneId",$ComuneResidenzaId);
        $selector="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'regione','regione.php?do=cerca&fieldtoupdate=RegioneId&labeltoupdate=Regione','bottom');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona regione\" border=\"0\" /></a>";
        $selector_ann="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"$('#RegioneId').val('0');$('#Regione').val('');\" style=\"margin-left:2px\"><img src=\"/images/iconset/delete_gray.gif\" alt=\"cancella campo\"  border=\"0\" /></a>";
        $selector.=$selector_ann; 
        $page->create_textbox_with_sel("Regione","Regione","Regione","",1,"brain_campoForm",array("class"=>"'required'","readonly"=>"'readonly'"),$selector);
                
         print("<br style=\"clear:both;\"/>");
                           
        $page->create_textbox_hidden_differentId("ProvinciaId","ProvinciaId",$ComuneResidenzaId);
        $selector="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'provincia','provincia.php?do=cerca&fieldtoupdate=ProvinciaId&labeltoupdate=Provincia','bottom');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona provincia\" border=\"0\" /></a>";
        $selector_ann="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"$('#RegioneId').val('0');$('#Regione').val('');\" style=\"margin-left:2px\"><img src=\"/images/iconset/delete_gray.gif\" alt=\"cancella campo\"  border=\"0\" /></a>";
        $selector.=$selector_ann; 
        $page->create_textbox_with_sel("Provincia","Provincia","Provincia","",1,"brain_campoForm",array("class"=>"'required'","readonly"=>"'readonly'"),$selector);
         
          print("<br style=\"clear:both;\"/>");
        
        $page->create_textbox("Comune","ComuneId","Comune","",1,"brain_campoForm",array("class"=>"'required'"));      
         $page->create_textbox("Cap","CapId","Cap","",1,"brain_campoForm",array("class"=>"'required'"));   
         print("<br style=\"clear:both;\"/>");
         ?>
                             
                              
                                <div id="elenco_comuni"></div>     
                                
                                </div>
                         </div>
                        <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva","salva aula","brain_salva","submit");
                                 // $page->create_button("Cancella","Cancella","elimina aula","brain_cancella","button");
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    </div>   
		</div>
                             
                           

<?
    
    
    
    exit();
}

function get_comune()
{
    
global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();

$ProvinciaId=$_REQUEST['ProvinciaId'];
$sql="SELECT ComuneId, Comune FROM Comune WHERE provincia=$ProvinciaId order by Comune ASC";

$ArrObject1 = $db->fetch_array($sql);
$page->create_select("Comune","Campeggio[CampeggioComune]","CampeggioComune","brain_campiform",$ArrObject1,"0","ComuneId","Comune",
array("class"=>"'required'"),1);
exit();
}


function get_comune_dominio()
{
    
global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();
?>
<script language="javascript" type="text/javascript">
			   
jQuery(document).ready(function() {
	jQuery("#selectitems1").multiselect();
});

</script>
<?
$ProvinciaId=$_REQUEST['ProvinciaId'];
$DominioId=$_REQUEST['DominioId'];
print("<br style=\"clear:both;\"/>");
print("<br style=\"clear:both;\"/>");
?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> Seleziona i Comuni </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $ArrTipo=Array();
                $sql1="SELECT ComuneId FROM CEV_DominioGeografico WHERE DominioId=$DominioId";
                $ArrObject2 = $db->fetch_array($sql1);
                foreach ($ArrObject2 as &$value) {
                    array_push($ArrTipo,$value['ProvinciaId']);
                }

                $sql="SELECT ComuneId, Comune FROM Comune WHERE provincia=$ProvinciaId order by Comune ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['ComuneId']; ?>"<?php if (in_array($value['ComuneId'], $ArrTipo)) : echo ' selected="selected"'; endif; ?>><?php echo $value['Comune']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
exit();
}


function get_comune_campeggio()
{
    
global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();
?>
<script language="javascript" type="text/javascript">
			   
jQuery(document).ready(function() {
	jQuery("#selectitems1").multiselect();
});

</script>
<?
$ProvinciaId=$_REQUEST['ProvinciaId'];
$CampeggioId=$_REQUEST['CampeggioId'];
print("<br style=\"clear:both;\"/>");
print("<br style=\"clear:both;\"/>");
?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> Seleziona i Comuni </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $ArrTipo=Array();
                $sql1="SELECT ComuneId FROM CEV_CampeggioPrivilegio WHERE CampeggioId=$CampeggioId";
                $ArrObject2 = $db->fetch_array($sql1);
                foreach ($ArrObject2 as &$value) {
                    array_push($ArrTipo,$value['ProvinciaId']);
                }

                $sql="SELECT ComuneId, Comune FROM Comune WHERE provincia=$ProvinciaId order by Comune ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['ComuneId']; ?>"<?php if (in_array($value['ComuneId'], $ArrTipo)) : echo ' selected="selected"'; endif; ?>><?php echo $value['Comune']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
exit();
}


function get_comune_bundle()
{
    
global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();
?>
<script language="javascript" type="text/javascript">
			   
jQuery(document).ready(function() {
	jQuery("#selectitems1").multiselect();
});

</script>
<?
$ProvinciaId=$_REQUEST['ProvinciaId'];
$BundleId=$_REQUEST['BundleId'];
print("<br style=\"clear:both;\"/>");
print("<br style=\"clear:both;\"/>");
?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> Seleziona i Comuni </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $ArrTipo=Array();
                $sql1="SELECT ComuneId FROM CEV_BundleGeografico WHERE BundleId=$BundleId";
                $ArrObject2 = $db->fetch_array($sql1);
                foreach ($ArrObject2 as &$value) {
                    array_push($ArrTipo,$value['ProvinciaId']);
                }

                $sql="SELECT ComuneId, Comune FROM Comune WHERE provincia=$ProvinciaId order by Comune ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['ComuneId']; ?>"<?php if (in_array($value['ComuneId'], $ArrTipo)) : echo ' selected="selected"'; endif; ?>><?php echo $value['Comune']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
exit();
}


if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
   
   
			if(!isset($do)) 
			$do='';
		
		
			switch($do) {
                                
                                case "add":
					$FunzioneId=2;
                                            add();
                                      
                                case "cerca":
				
                               cerca_comune();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                                case "get_comune":
				
                               get_comune();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                             case "get_comune_dominio":
				
                               get_comune_dominio();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                            case "get_comune_bundle":
				
                               get_comune_dominio();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            case "get_comune_campeggio":
				
                               get_comune_campeggio();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;

				default:
				$FunzioneId=1;
                                show_list();    
                         		// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
			}
	
} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>