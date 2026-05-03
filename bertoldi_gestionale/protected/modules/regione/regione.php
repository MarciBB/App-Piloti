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
global $user,$HtmlCommon,$ModuloId, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['regione']['titolo_elenco'],0,"","");
$HtmlCommon->html_titolo_box($dizionario['regione']['titolo_elenco']);
$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','regione','regione.php?do=add',$dizionario['regione']['titolo_elenco']);
include_once("regione_datatable.php");
?>

		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
		    
			<tr class="brain_tabellaTr">                              
			      <th width="50%"><?=$dizionario['regione']['regione']?></th>
                              <th width="45%"><?=$dizionario['regione']['nazione']?></th>
                              <th width="5%"><?=$dizionario['regione']['edita']?></th>
                            
                           
		      </tr>
		    
			<tr class="brain_tabellaFilter">
			       <th><input type="text" /></th> 
			       <th><input type="text" /></th> 			       
                             
                               <th><input type="hidden" /></th>
                       
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="4" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['regione']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		
		</table>
   <?
}


function cerca_regione()
{
global $db;
include_once("regione_datatable_cerca.php");
global $user,$HtmlCommon;
//$HtmlCommon->html_titolo_box("Ricerca comune");
$fieldtoupdate=$_REQUEST['fieldtoupdate'];
$labeltoupdate=$_REQUEST['labeltoupdate'];

?>

<input type="hidden" id="fieldtoupdate" value="<?=$fieldtoupdate?>" />
<input type="hidden" id="labeltoupdate" value="<?=$labeltoupdate?>" />            

<div class="SelezionaComune">
	
	<div class="SelezionaComuneHeader">
		<h2><?=$dizionario['regione']['seleziona']?></h2>
		<a class="chiudi" href="javascript:void(0);" onclick="ChiudiElencoComuni();" title="Chiudi"><?=$dizionario['generale']['chiudi']?></a>
	</div>
	
	<div class="SelezionaComuneContent">
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
		    
			<tr class="brain_tabellaTr">
                            <th width="50%"><?=$dizionario['generale']['regione']?></th>
			      <th width="50%"><?=$dizionario['generale']['nazione']?></th>
			     
		      </tr>
		    
			<tr class="brain_tabellaFilter">
			       <th><input type="text" /></th> 
			       <th><input type="text" /></th> 
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="2" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<th align="left" colspan="2"><a class="brain_add" href="javascript:void(0);" onclick="ExternalLoad('regione','regione.php?do=add',this);" title="Aggiungi Regione"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['regione']['aggiungi']?></a></th>
			</tr> 
		</tfoot> 
		</table>
	</div>
</div>
<?
   
}


function add()
{
    
global $HtmlCommon,$user, $dizionario;  

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
include_once("regione_validator.php");       

$HtmlCommon->html_titolo_pagina($dizionario['regione']['titolo_aggiungi'],1,"regione","regione.php");
$HtmlCommon->html_titolo_box($dizionario['regione']['titolo_aggiungi']);

$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

 
 
?>
 <div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                    
                            
                         <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica anagraficaSelector">
                                <div class="brain_data-content">    


        <?
        $page->create_textbox_hidden("action","create");        
        ?>
        <div class="brainGestoreSedi">
            <?

            $page->create_select($dizionario['generale']['nazione'],"Campeggio[CampeggioNazione]","CampeggioNazione","brain_campiform",$arr_nazione,$CampeggioNazione,"NazioneId","Nazione",
            array("class"=>"'required'","onChange"=>"javascript:getRegione(this.value);"),1);                 
             ?>
            <br style="clear:both;"/>
            <br style="clear:both;"/>            
        </div>
        <?
          print("<br style=\"clear:both;\"/>");
        
        $page->create_textbox($dizionario['generale']['regione'],"RegioneId","Regione","",1,"brain_campoForm",array("class"=>"'required'"));
         print("<br style=\"clear:both;\"/>");
         ?>                  

         </div>
         </div>                
        <div class="divSubmit">
                    <?
                  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
                 // $page->create_button("Cancella","Cancella","elimina aula","brain_cancella","button");
                    ?>


            </div>     


        </form>
    </div>   
</div>
<?
    exit();
}

 function edit($RegioneId)
{
    
global $HtmlCommon,$user, $dizionario;  

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

$Regione=new Regione($RegioneId);
$Regione->Id=$RegioneId;
$Regione->conn=$db;
$Regione->inizializzaDatiGenerali();
$arrRegione=$Regione->DatiGenerali;

include_once("regione_validator.php");       

$HtmlCommon->html_titolo_pagina($dizionario['regione']['titolo_edita'],1,"regione","regione.php");
$HtmlCommon->html_titolo_box($dizionario['regione']['titolo_edita']);

$azione="edit";

 $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
 
?>
 <div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                    
                            
                         <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica anagraficaSelector">
                                <div class="brain_data-content">    


        <?
        $page->create_textbox_hidden("action","update");
        $page->create_textbox_hidden("RegioneId",$RegioneId);  
        ?>
        <div class="brainGestoreSedi">
            <?

            $page->create_select($dizionario['generale']['nazione'],"Campeggio[CampeggioNazione]","CampeggioNazione","brain_campiform",$arr_nazione,$arrRegione['NazioneId'],"NazioneId","Nazione",
            array("class"=>"'required'","onChange"=>"javascript:getRegione(this.value);"),1);                 
             ?>
            <br style="clear:both;"/>
            <br style="clear:both;"/>            
        </div>
        <?
          print("<br style=\"clear:both;\"/>");
        
         $page->create_textbox($dizionario['generale']['regione'],"RegioneId","Regione",$arrRegione['Regione'],1,"brain_campoForm",array("class"=>"'required'"));
         print("<br style=\"clear:both;\"/>");
         ?>  

         </div>
         </div>
        <div class="divSubmit">
                    <?
                  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
                 // $page->create_button("Cancella","Cancella","elimina aula","brain_cancella","button");
                    ?>


            </div>
        </form>
    </div>   
</div>
                             
                           

<?
    
    
    
    exit();
}





function get_regione()
{
    
global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();

$NazioneId=$_REQUEST['NazioneId'];
$sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

$ArrObject1 = $db->fetch_array($sql);
$page->create_select($dizionario['comune']['regione'],"RegioneZonaId","RegioneZonaId","brain_campiform",$ArrObject1,$RegioneId,"RegioneId","Regione",
array("class"=>"'required'","onChange"=>"javascript:getProvincia(this.value);"),1);
exit();
}

function get_regione_camping()
{
    
global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();

$NazioneId=$_REQUEST['NazioneId'];
$sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

$ArrObject1 = $db->fetch_array($sql);
$page->create_select($dizionario['comune']['regione'],"Campeggio[CampeggioRegione]","CampeggioRegione","brain_campiform",$ArrObject1,$RegioneId,"RegioneId","Regione",
array("class"=>"'required'","onChange"=>"javascript:getProvincia(this.value);"),1);
exit();
}

function get_regione_news()
{
    
global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();

$NazioneId=$_REQUEST['NazioneId'];
$sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

$ArrObject1 = $db->fetch_array($sql);
$page->create_select($dizionario['generale']['regione'],"News[NewsRegione]","NewsRegione","brain_campiform",$ArrObject1,$RegioneId,"RegioneId","Regione",
array("onChange"=>"javascript:getProvincia(this.value);"),0);
exit();
}

function get_regione_dominio()
{
    
global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();

$NazioneId=$_REQUEST['NazioneId'];
$DominioId=$_REQUEST['DominioId'];
$TipoId=$_REQUEST['TipoId'];
print("<br style=\"clear:both;\"/>");
$sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

$ArrObject1 = $db->fetch_array($sql);
$page->create_select($dizionario['generale']['regione'],"RegioneZonaId","RegioneZonaId","brain_campiform",$ArrObject1,$RegioneId,"RegioneId","Regione",
array("class"=>"'required'","onChange"=>"javascript:getProvinciaDominio(this.value,$DominioId,$TipoId);"),1);
?>
<div id="getProvincia">
</div>
<?

exit();
}

function get_regione_rebuild()
{
    
global $HtmlCommon,$user, $dizionario;  

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
$NazioneId=$_REQUEST['NazioneId'];
$TipoId=$_REQUEST['TipoId'];

if($TipoId==5){
    print("<br style=\"clear:both;\"/>");
    ?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> <?=$dizionario['regione']['seleziona']?> </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?

                $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['RegioneId']; ?>"><?php echo $value['Regione']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
    
}else{

    print("<br style=\"clear:both;\"/>");
    $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

    $ArrObject1 = $db->fetch_array($sql);
    $page->create_select("Regione","RegioneZonaId","RegioneZonaId","brain_campiform",$ArrObject1,$RegioneId,"RegioneId","Regione",
    array("class"=>"'required'","onChange"=>"javascript:getProvinciaRebuild(this.value,0,$TipoId);"),1);
    ?>
    <div id="getProvincia">
    </div>
    <?
}
exit();
}


function get_regione_bundle()
{
    
global $HtmlCommon,$user, $dizionario;  

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
$NazioneId=$_REQUEST['NazioneId'];
$BundleId=$_REQUEST['BundleId'];
$TipoId=$_REQUEST['TipoId'];

if($TipoId==5){
    print("<br style=\"clear:both;\"/>");
    ?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> <?=$dizionario['regione']['seleziona']?> </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $ArrTipo=Array();
                $sql1="SELECT RegioneId FROM CEV_BundleGeografico WHERE BundleId=$BundleId";
                $ArrObject2 = $db->fetch_array($sql1);
                foreach ($ArrObject2 as &$value) {
                    array_push($ArrTipo,$value['RegioneId']);
                }

                $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['RegioneId']; ?>"><?php echo $value['Regione']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
    
}else{

    print("<br style=\"clear:both;\"/>");
    $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

    $ArrObject1 = $db->fetch_array($sql);
    $page->create_select("Regione","RegioneZonaId","RegioneZonaId","brain_campiform",$ArrObject1,$RegioneId,"RegioneId","Regione",
    array("class"=>"'required'","onChange"=>"javascript:getProvinciaBundle(this.value,$BundleId,$TipoId);"),1);
    ?>
    <div id="getProvincia">
    </div>
    <?
}
exit();
}

function get_regione_campeggio()
{
    
global $HtmlCommon,$user, $dizionario;  

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
$NazioneId=$_REQUEST['NazioneId'];
$CampeggioId=$_REQUEST['CampeggioId'];
$TipoId=$_REQUEST['TipoId'];


if($TipoId==5){
    print("<br style=\"clear:both;\"/>");
    ?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> <?=$dizionario['regione']['seleziona']?> </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $ArrTipo=Array();
                $sql1="SELECT RegioneId FROM CEV_CampeggioPrivilegio WHERE CampeggioId=$CampeggioId";
                $ArrObject2 = $db->fetch_array($sql1);
                foreach ($ArrObject2 as &$value) {
                    array_push($ArrTipo,$value['RegioneId']);
                }

                $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['RegioneId']; ?>"><?php echo $value['Regione']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
    
}else{
    print("<br style=\"clear:both;\"/>");
    $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$NazioneId order by regione ASC";

    $ArrObject1 = $db->fetch_array($sql);
    $page->create_select("Regione","RegioneZonaId","RegioneZonaId","brain_campiform",$ArrObject1,$RegioneId,"RegioneId","Regione",
    array("class"=>"'required'","onChange"=>"javascript:getProvinciaCampeggio(this.value,$CampeggioId,$TipoId);"),1);
    ?>
    <div id="getProvincia">
    </div>
    <?
}
exit();
}


function traduci($RegioneId)
{

include_once("regione_validator.php");      


global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  

$step_corrente=2;
$Regione=new Regione($RegioneId);
$Regione->Id=$RegioneId;
$Regione->conn=$db;
$Regione->inizializzaDatiGenerali();
$arrRegione=$Regione->DatiGenerali;

$HtmlCommon->html_titolo_pagina("Traduci Regione - ".$arrRegione['Regione'],1,"regione","regione.php");
$HtmlCommon->html_titolo_box ("Traduci Regione - ".$arrRegione['Regione']);
$arr_stato[]= array("StatoId" => '0',"Stato" => 'Disattiva');
$arr_stato[]= array("StatoId" => '1',"Stato" => 'Attiva');

$sql="Select * from CEV_Lingua where LinguaDefault=0 and OdcIdRef=$user->OdcId order by LinguaPeso asc";
$ArrObject = $db->fetch_array($sql);

?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                       <?
                       $page->create_textbox_hidden("step_corrente",$step_corrente);
                       $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                       ?>
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","updateTraduzione");
                $page->create_textbox_hidden("idpost",$RegioneId);
                ?>
                                    
                <div style="padding-bottom:5px">Campo da tradurre: <strong>nome della regione</strong></div>
                <div style="padding-bottom:5px">Campo in lingua predefinita: <strong><?=$arrRegione['Regione']?></strong></div>
                <div style="padding-bottom:5px"><i>Traduci in:</i></div>
                <?
                print("<br style=\"clear:both;\"/>");                
                foreach ($ArrObject as &$value) {
                    $LinguaIdTrad=$value['LinguaId'];
                    $LinguaNome=$value['LinguaNome'];
                    $nomeArr=$LinguaIdTrad."_RegioneTradNome";
                    
                    $sql="Select * from RegioneTrad where RegioneTradLinguaId=$LinguaIdTrad and RegioneId=$RegioneId and OdcIdRef=$user->OdcId";                   
                    $ArrObject1 = $db->fetch_array($sql);                    
                    if(sizeof($ArrObject1)>0)
                        $valoreTrad=$ArrObject1[0]['RegioneTradNome'];
                    else
                        $valoreTrad="";                    
                    $page->create_textbox($LinguaNome,"RegioneNome","Regione[$nomeArr]",$valoreTrad,0,"brain_campoForm");                    
                }
                print("<br style=\"clear:both;\"/>");
                print("<br style=\"clear:both;\"/>");
                ?>                               
                </div>
                </div>
                <div class="divSubmit">
                <?
                $page->create_button("Salva","Salva","salva","brain_salva","submit");
                //$page->create_button("Cancella","Cancella","elimina","brain_cancella","button");
                ?>
                </div>       
                </form>
            </div>   
        </div>
<?  
}


if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
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
                                            add();
                                      
                                case "cerca":
				
                               cerca_regione();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                                case "edit":
				
                                edit($_REQUEST['RegioneId']);   
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                                case "get_regione":
				
                               get_regione();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                                case "get_regione_camping":
				
                               get_regione_camping();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                            case "get_regione_news":
				
                               get_regione_news();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                            case "get_regione_dominio":
				
                               get_regione_dominio();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                            case "get_regione_bundle":
				
                               get_regione_bundle();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            case "get_regione_campeggio":
				
                               get_regione_campeggio();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            case "get_regione_rebuild":
				
                               get_regione_rebuild();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            case "traduci":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           traduci($_REQUEST['RegioneId']);
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            

				default:
				$FunzioneId=1;
                                show_list();    
                         		// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
			}
		
	} // end verifica permessi
	else {
            Errors::$ErrorePermessiModulo;
            
        }

} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>