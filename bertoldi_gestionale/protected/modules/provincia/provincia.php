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
include_once($classespath_."class.Provincia.php");
include_once($classespath_."class.Comune.php");

global $ModuloId;
$ModuloId=18;// modulo base mediazione



function show_list()
{
global $user,$HtmlCommon,$ModuloId, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['provincia']['titolo_elenco'],0,"","");
$HtmlCommon->html_titolo_box($dizionario['provincia']['titolo_elenco']);
$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','provincia','provincia.php?do=add',$dizionario['provincia']['aggiungi']);
include_once("provincia_datatable.php");
?>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
		    
			<tr class="brain_tabellaTr">                              
			      <th width="55%"><?=$dizionario['generale']['provincia']?></th>  
			      <th width="20%"><?=$dizionario['generale']['regione']?></th>
                              <th width="20%"><?=$dizionario['generale']['nazione']?></th>
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
		
		</table>

   <?
}



function cerca_regione()
{
global $db;
include_once("provincia_datatable_cerca.php");
global $user,$HtmlCommon, $dizionario;
//$HtmlCommon->html_titolo_box("Ricerca comune");
$fieldtoupdate=$_REQUEST['fieldtoupdate'];
$labeltoupdate=$_REQUEST['labeltoupdate'];

?>

<input type="hidden" id="fieldtoupdate" value="<?=$fieldtoupdate?>" />
<input type="hidden" id="labeltoupdate" value="<?=$labeltoupdate?>" />            

<div class="SelezionaComune">
	
	<div class="SelezionaComuneHeader">
		<h2>Seleziona regione</h2>
		<a class="chiudi" href="javascript:void(0);" onclick="ChiudiElencoComuni();" title="Chiudi"><?=$dizionario['generale']['chiudi']?></a>
	</div>
	
	<div class="SelezionaComuneContent">
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
		    
			<tr class="brain_tabellaTr">
                            <th width="34%"><?=$dizionario['generale']['provincia']?></th>
                              <th width="33%"><?=$dizionario['generale']['regione']?></th>
			      <th width="33%"><?=$dizionario['generale']['nazione']?></th>
			     
		      </tr>
		    
			<tr class="brain_tabellaFilter">
			       <th><input type="text" /></th> 
			       <th><input type="text" /></th> 
                                <th><input type="text" /></th> 
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="3" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<th align="left" colspan="3"><a class="brain_add" href="javascript:void(0);" onclick="ExternalLoad('provincia','provincia.php?do=add',this);" title="Aggiungi Regione"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['generale']['aggiungi']?></a></th>
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

$nazione=new Nazione(null);
$nazione->conn=$db;
$nazione->getAllNazione();
$arr_nazione=$nazione->ArrNazione;
//print_r($arr_gestori);
include_once("provincia_validator.php");       

$HtmlCommon->html_titolo_pagina($dizionario['provincia']['titolo_aggiungi'],1,"provincia","provincia.php");
$HtmlCommon->html_titolo_box($dizionario['provincia']['titolo_aggiungi']);


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
            <div id="Regione">            
            </div>            
        </div>
        <?
        print("<br style=\"clear:both;\"/>");        
        $page->create_textbox($dizionario['generale']['provincia'],"ProvinciaId","Provincia","",1,"brain_campoForm",array("class"=>"'required'"));               
        $page->create_textbox($dizionario['provincia']['sigla'],"SiglaId","Sigla","",1,"brain_campoForm",array("class"=>"'required'"));               
        
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

function get_provincia()
{
    
global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();

$RegioneId=$_REQUEST['RegioneId'];
$sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

$ArrObject1 = $db->fetch_array($sql);
$page->create_select($dizionario['comune']['provincia'],"ProvinciaZonaId","ProvinciaZonaId","brain_campiform",$ArrObject1,"0","ProvinciaId","Provincia",
array("class"=>"'required'"),1);
exit();
}

function get_provincia_camping()
{
    
global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();

$RegioneId=$_REQUEST['RegioneId'];
$sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

$ArrObject1 = $db->fetch_array($sql);
$page->create_select($dizionario['comune']['provincia'],"Campeggio[CampeggioProvincia]","CampeggioProvincia","brain_campiform",$ArrObject1,"0","ProvinciaId","Provincia",
array("class"=>"'required'","onChange"=>"javascript:getComune(this.value);"),1);
exit();
}

function get_provincia_dominio()
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
$RegioneId=$_REQUEST['RegioneId'];
$DominioId=$_REQUEST['DominioId'];
$TipoId=$_REQUEST['TipoId'];

if($TipoId==2){
    print("<br style=\"clear:both;\"/>");
    ?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> <?=$dizionario['provincia']['seleziona']?> </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $ArrTipo=Array();
                $sql1="SELECT ProvinciaId FROM CEV_DominioGeografico WHERE DominioId=$DominioId";
                $ArrObject2 = $db->fetch_array($sql1);
                foreach ($ArrObject2 as &$value) {
                    array_push($ArrTipo,$value['ProvinciaId']);
                }

                $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['ProvinciaId']; ?>"><?php echo $value['Provincia']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
    
}else{
    print("<br style=\"clear:both;\"/>");
    print("<br style=\"clear:both;\"/>");
    $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

    $ArrObject1 = $db->fetch_array($sql);
    $page->create_select("Provincia","Campeggio[CampeggioProvincia]","CampeggioProvincia","brain_campiform",$ArrObject1,"0","ProvinciaId","Provincia",
array("class"=>"'required'","onChange"=>"javascript:getComune(this.value,$DominioId);"),1);
    ?>
<div id="getComune">
</div>
<?
}

exit();
}

function get_provincia_rebuild()
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
$RegioneId=$_REQUEST['RegioneId'];
$TipoId=$_REQUEST['TipoId'];

if($TipoId==2){
    print("<br style=\"clear:both;\"/>");
    ?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> <?=$dizionario['provincia']['seleziona']?> </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['ProvinciaId']; ?>"><?php echo $value['Provincia']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
    
}else{
    print("<br style=\"clear:both;\"/>");
    print("<br style=\"clear:both;\"/>");
    $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

    $ArrObject1 = $db->fetch_array($sql);
    $page->create_select("Provincia","Campeggio[CampeggioProvincia]","CampeggioProvincia","brain_campiform",$ArrObject1,"0","ProvinciaId","Provincia",
array("class"=>"'required'","onChange"=>"javascript:getComune(this.value,0);"),1);
    ?>
<div id="getComune">
</div>
<?
}

exit();
}



function edit($ProvinciaId)
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

$Provincia=new Provincia($ProvinciaId);
$Provincia->Id=$ProvinciaId;
$Provincia->conn=$db;
$Provincia->inizializzaDatiGenerali();
$arrProvincia=$Provincia->DatiGenerali;

include_once("provincia_validator.php");       

$azione="edit";

$HtmlCommon->html_titolo_pagina($dizionario['provincia']['titolo_edita'],1,"provincia","provincia.php");
$HtmlCommon->html_titolo_box($dizionario['provincia']['titolo_edita']);

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
        $page->create_textbox_hidden("ProvinciaId",$ProvinciaId);  
        ?>
        <div class="brainGestoreSedi">
            <?

            $page->create_select($dizionario['generale']['nazione'],"Campeggio[CampeggioNazione]","CampeggioNazione","brain_campiform",$arr_nazione,$arrProvincia['NazioneId'],"NazioneId","Nazione",
            array("class"=>"'required'","onChange"=>"javascript:getRegione(this.value);"),1);                 
             ?>
            <br style="clear:both;"/>
            <br style="clear:both;"/>
            <div id="Regione">
            <?    
            if($azione=="edit"){    
                $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=".$arrProvincia['NazioneId']." order by regione ASC";
                $ArrObject1 = $db->fetch_array($sql);
                $page->create_select($dizionario['generale']['regione'],"Campeggio[RegioneId]","ProvinciaRegione","brain_campiform",$ArrObject1,$arrProvincia['RegioneId'],"RegioneId","Regione",
                array("class"=>"'required'","onChange"=>"javascript:getProvincia(this.value);"),1);                
            }
            ?>
            </div>            
        </div>
        <?
          print("<br style=\"clear:both;\"/>");
        $page->create_textbox($dizionario['generale']['provincia'],"ProvinciaId","Provincia",$arrProvincia['Provincia'],1,"brain_campoForm",array("class"=>"'required'"));               
        $page->create_textbox($dizionario['provincia']['sigla'],"SiglaId","Sigla",$arrProvincia['sigla'],1,"brain_campoForm",array("class"=>"'required'"));               
        print("<br style=\"clear:both;\"/>");
         ?>
                             

                <div id="elenco_comuni"></div>     

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



function get_provincia_bundle()
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
$RegioneId=$_REQUEST['RegioneId'];
$BundleId=$_REQUEST['BundleId'];
$TipoId=$_REQUEST['TipoId'];

if($TipoId==2){
    print("<br style=\"clear:both;\"/>");
    ?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> <?=$dizionario['provincia']['seleziona']?> </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $ArrTipo=Array();
                $sql1="SELECT ProvinciaId FROM CEV_BundleGeografico WHERE BundleId=$BundleId";
                $ArrObject2 = $db->fetch_array($sql1);
                foreach ($ArrObject2 as &$value) {
                    array_push($ArrTipo,$value['ProvinciaId']);
                }

                $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['ProvinciaId']; ?>"><?php echo $value['Provincia']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
    
}else{
    print("<br style=\"clear:both;\"/>");
    print("<br style=\"clear:both;\"/>");
    $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

    $ArrObject1 = $db->fetch_array($sql);
    $page->create_select("Provincia","Campeggio[CampeggioProvincia]","CampeggioProvincia","brain_campiform",$ArrObject1,"0","ProvinciaId","Provincia",
array("class"=>"'required'","onChange"=>"javascript:getComune(this.value,$BundleId);"),1);
    ?>
<div id="getComune">
</div>
<?
}

exit();
}

function get_provincia_campeggio()
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
$RegioneId=$_REQUEST['RegioneId'];
$CampeggioId=$_REQUEST['CampeggioId'];
$TipoId=$_REQUEST['TipoId'];

if($TipoId==2){
    print("<br style=\"clear:both;\"/>");
    ?>
        <div class="" style="padding-top:5px;">
        <label for="DominioNazione"> Seleziona le Province </label><br/>
        <div class="" style="padding-top:5px;">
            <select name="selectitems1[]" id="selectitems1" multiple="multiple" style="width:500px; height: 150px;">
                <?php foreach ($items as $i => $v) { ?>

                <?php } ?>

                <?
                $ArrTipo=Array();
                $sql1="SELECT ProvinciaId FROM CEV_CampeggioPrivilegio WHERE CampeggioId=$CampeggioId";
                $ArrObject2 = $db->fetch_array($sql1);
                foreach ($ArrObject2 as &$value) {
                    array_push($ArrTipo,$value['ProvinciaId']);
                }

                $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

                $ArrObject1 = $db->fetch_array($sql);                
                foreach ($ArrObject1 as &$value) {                    
                    ?>                                   
                    <option value="<?php echo $value['ProvinciaId']; ?>"><?php echo $value['Provincia']; ?></option> 
                    <?
                }
                ?> 
          </select>  
        </div>
        </div>
        <?
    
}else{
    print("<br style=\"clear:both;\"/>");
    print("<br style=\"clear:both;\"/>");
    $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$RegioneId order by Provincia ASC";

    $ArrObject1 = $db->fetch_array($sql);
    $page->create_select("Provincia","Campeggio[CampeggioProvincia]","CampeggioProvincia","brain_campiform",$ArrObject1,"0","ProvinciaId","Provincia",
array("class"=>"'required'","onChange"=>"javascript:getComune(this.value,$CampeggioId);"),1);
    ?>
<div id="getComune">
</div>
<?
}

exit();
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
                                 break;
                             
                             case "edit":
				
                                edit($_REQUEST['ProvinciaId']);   
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                                case "cerca":
				
                               cerca_regione();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                                 case "get_provincia":
				
                               get_provincia();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                                case "get_provincia_camping":
				
                               get_provincia_camping();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "get_provincia_dominio":
				
                               get_provincia_dominio();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                            case "get_provincia_rebuild":
				
                               get_provincia_rebuild();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                                case "get_provincia_bundle":
				
                               get_provincia_bundle();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                            
                            case "get_provincia_campeggio":
				
                               get_provincia_campeggio();    
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