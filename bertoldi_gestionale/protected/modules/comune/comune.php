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
$HtmlCommon->html_titolo_pagina($dizionario['comune']['titolo_comune'],0,"","");
$HtmlCommon->html_titolo_box($dizionario['comune']['titolo_comune']);
$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','comune','comune.php?do=add',$dizionario['comune']['aggiungi']);
include_once("comune_datatable.php");
?>



	
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
		    
			<tr class="brain_tabellaTr">
                            <th width="40%"><?=$dizionario['comune']['comune']?></th>
			      			<th width="20%"><?=$dizionario['comune']['provincia']?></th>  
			      			<th width="20%"><?=$dizionario['comune']['regione']?></th>
                            <th width="20%"><?=$dizionario['comune']['nazione']?></th>
                            <th width="20%"><?=$dizionario['comune']['edit']?></th>
                           
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
				<td colspan="5" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		
		</table>
	

   <?
}


function cerca_comune()
{
global $db;
include_once("comune_datatable_cerca.php");
global $user,$HtmlCommon, $dizionario;
//$HtmlCommon->html_titolo_box("Ricerca comune");
$fieldtoupdate=$_REQUEST['fieldtoupdate'];
$labeltoupdate=$_REQUEST['labeltoupdate'];

?>

<input type="hidden" id="fieldtoupdate" value="<?=$fieldtoupdate?>" />
<input type="hidden" id="labeltoupdate" value="<?=$labeltoupdate?>" />            

<div class="SelezionaComune">
	
	<div class="SelezionaComuneHeader">
		<h2><?=$dizionario['comune']['seleziona']?></h2>
		<a class="chiudi" href="javascript:void(0);" onclick="ChiudiElencoComuni();" title="Chiudi">Chiudi</a>
	</div>
	
	<div class="SelezionaComuneContent">
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
		    
			<tr class="brain_tabellaTr">
			      <th width="20%"><?=$dizionario['comune']['nazione']?></th>
			      <th width="20%"><?=$dizionario['comune']['regione']?></th>
			      <th width="20%"><?=$dizionario['comune']['provincia']?></th>  
			      <th width="40%"><?=$dizionario['comune']['comune']?></th>
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
				<td colspan="4" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<th align="left" colspan="4"><a class="brain_add" href="javascript:void(0);" onclick="ExternalLoad('comune','comune.php?do=add',this);" title="Aggiungi Comune"><i class="fa fa-plus" aria-hidden="true"></i>  <?=$dizionario['comune']['aggiungi']?></a></th>
			</tr> 
		</tfoot> 
		</table>
	</div>
</div>
<?
   
}

function add() {
    
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
    include_once("comune_validator.php");       
    
    $HtmlCommon->html_titolo_pagina($dizionario['comune']['titolo_aggiungi'],1,"comune","comune.php");
    $HtmlCommon->html_titolo_box($dizionario['comune']['titolo_aggiungi']);
    
     $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['comune']['non_attivo']);
     $arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['comune']['attivo']);
    
     ?>
     <div id="brain_form_content" class="brain_row brain_contenuto">
    	<div class="brain_boxIntero">  
			<form id="application_form" name="application_form" method="post" action="#">
				<div class="brain_formModifica anagraficaSelector">
					<div class="brain_data-content">    
                    <?php
                    $page->create_textbox_hidden("action","create");
                    $page->create_textbox_hidden_differentId("NazioneId","NazioneId",$ComuneResidenzaId);
                    ?>
            		<div class="brainGestoreSedi">
                    <?php
                    $page->create_select($dizionario['comune']['nazione'],"Campeggio[CampeggioNazione]","CampeggioNazione","brain_campiform",$arr_nazione,$CampeggioNazione,"NazioneId","Nazione",
                    array("class"=>"'required'","onChange"=>"javascript:getRegione(this.value);"),1);                 
                    ?>
                    <br style="clear:both;"/>
                    <br style="clear:both;"/>
                    <div id="Regione">
                    <?php if($azione=="edit"){    
                            $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=$CampeggioNazione order by regione ASC";
                            $ArrObject1 = $db->fetch_array($sql);
                            $page->create_select($dizionario['comune']['regione'],"Campeggio[CampeggioRegione]","CampeggioRegione","brain_campiform",$ArrObject1,$CampeggioRegione,"RegioneId","Regione",
                            array("class"=>"'required'","onChange"=>"javascript:getProvincia(this.value);"),1);                
                    } ?>
                    </div>
                    <br style="clear:both;"/>
                    <br style="clear:both;"/>
                    <div id="Provincia">
                	<?php if($azione=="edit"){    
                        $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=$CampeggioRegione order by provincia ASC";
                        $ArrObject1 = $db->fetch_array($sql);
                        $page->create_select($dizionario['comune']['provincia'],"Campeggio[CampeggioProvincia]","CampeggioProvincia","brain_campiform",$ArrObject1,$CampeggioProvincia,"ProvinciaId","Provincia",
                        array("class"=>"'required'","onChange"=>"javascript:getComune(this.value);"),1);
                        print(" <br style=\"clear:both;\"/>");
                    } ?>
                	</div>
            	</div>
                <?php
                print("<br style=\"clear:both;\"/>");
            
                $page->create_textbox($dizionario['comune']['comune'],"ComuneId","Comune","",1,"brain_campoForm",array("class"=>"'required'"));      
                $page->create_textbox($dizionario['comune']['cap'],"CapId","Cap","",1,"brain_campoForm",array("class"=>"'required'"));   
                print("<br style=\"clear:both;\"/>");

                $sql="select * from RT_Confine where Cancella=0";
                $ArrObject = $db->fetch_array($sql);
           
               $nconf=sizeof($ArrObject);
               $c=0;
               while($c<$nconf) {
                   $Confine=$ArrObject[$c]['Confine'];
                   $ConfineId=$ArrObject[$c]['ConfineId'];
				   
				   echo "<input type='hidden' id='ComuneConfine".$ConfineId."' name='ComuneConfine[$ConfineId]' value='0'>";
                       
                   $c++;
               } ?>
               <div id="elenco_comuni"></div>     
        		</div>
    		</div>
    		<div class="divSubmit">
    		<?php
              $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
             // $page->create_button("Cancella","Cancella","elimina aula","brain_cancella","button");
            ?>
    		</div>     
    	</form>
    </div>   
    </div>
    <?php
    exit();
}

function edit($ComuneId) {
    
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
    
    $Comune=new Comune($ComuneId);
    $Comune->Id=$ComuneId;
    $Comune->conn=$db;
    $Comune->inizializzaDatiGenerali();
    $arrComune=$Comune->DatiGenerali;
    
    include_once("comune_validator.php");       
    
    $HtmlCommon->html_titolo_pagina($dizionario['comune']['titolo_edita'],1,"comune","comune.php");
    $HtmlCommon->html_titolo_box($dizionario['comune']['titolo_edita']);
    
    $azione="edit";
    
    $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['comune']['non_attivo']);
    $arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['comune']['attivo']);
     
    ?>
     <div id="brain_form_content" class="brain_row brain_contenuto">
    		<div class="brain_boxIntero">
            	<form id="application_form" name="application_form" method="post" action="#">
             		<div class="brain_formModifica anagraficaSelector">
                    	<div class="brain_data-content">    
                            <?php
                            $page->create_textbox_hidden("action","update");
                            $page->create_textbox_hidden("ComuneId",$ComuneId);  
                            ?>
            				<div class="brainGestoreSedi">
                			<?php
    
                            $page->create_select($dizionario['comune']['nazione'],"Campeggio[CampeggioNazione]","CampeggioNazione","brain_campiform",$arr_nazione,$arrComune['NazioneId'],"NazioneId","Nazione",
                            array("class"=>"'required'","onChange"=>"javascript:getRegione(this.value);"),1);                 
                             ?>
                            <br style="clear:both;"/>
                            <br style="clear:both;"/>
                            <div id="Regione">
                                <?php if($azione=="edit"){    
                                    $sql="SELECT RegioneId, Regione FROM Regione WHERE idnazione=".$arrComune['NazioneId']." order by regione ASC";
                                    $ArrObject1 = $db->fetch_array($sql);
                                    $page->create_select($dizionario['comune']['regione'],"Campeggio[CampeggioRegione]","CampeggioRegione","brain_campiform",$ArrObject1,$arrComune['RegioneId'],"RegioneId","Regione",
                                    array("class"=>"'required'","onChange"=>"javascript:getProvincia(this.value);"),1);                
                                } ?>
                			</div>
                            <br style="clear:both;"/>
                            <br style="clear:both;"/>
                            <div id="Provincia">
                                <?php if($azione=="edit"){    
                                    $sql="SELECT ProvinciaId, Provincia FROM Provincia WHERE RegioneId=".$arrComune['RegioneId']." order by provincia ASC";
                                    $ArrObject1 = $db->fetch_array($sql);
                                    $page->create_select($dizionario['comune']['provincia'],"Campeggio[CampeggioProvincia]","CampeggioProvincia","brain_campiform",$ArrObject1,$arrComune['ProvinciaId'],"ProvinciaId","Provincia",
                                    array("class"=>"'required'","onChange"=>"javascript:getComune(this.value);"),1);
                                    print(" <br style=\"clear:both;\"/>");
                                } ?>
                			</div>
            			</div>
            		<?php
                    print("<br style=\"clear:both;\"/>");
            
                    $page->create_textbox($dizionario['comune']['comune'],"ComuneId","Comune",$arrComune['Comune'],1,"brain_campoForm",array("class"=>"'required'"));      
                    $page->create_textbox("Cap","CapId","Cap",$arrComune['cap'],1,"brain_campoForm",array("class"=>"'required'"));   
                    print("<br style=\"clear:both;\"/>");
             
                    $sql="select * from RT_Confine where Cancella=0";
                    $ArrObject = $db->fetch_array($sql);
           
                   $nconf=sizeof($ArrObject);
                   $c=0;
                   while($c<$nconf) {
                       $Confine=$ArrObject[$c]['Confine'];
                       $ConfineId=$ArrObject[$c]['ConfineId'];
                       
                       $sql="select Km from RT_ComuneConfine where ComuneId=$ComuneId and ConfineId=$ConfineId";
                       $row=$db->query_first($sql);
                       $Km=0;
                       if (!empty($row['Km']))
                           $Km=$row['Km'];
                       
					   echo "<input type='hidden' id='ComuneConfine".$ConfineId."' name='ComuneConfine[$ConfineId]' value='$Km'>";
                       
                       $c++;
                   } ?>
                   <div id="elenco_comuni"></div>     
    
                    </div>
             	</div>
            	<div class="divSubmit">
                    <?php
                  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
                 // $page->create_button("Cancella","Cancella","elimina aula","brain_cancella","button");
                    ?>
        		</div>
        	</form>
        </div>   
    </div>
    <?php
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














if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
   
   
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
				
                                edit($_REQUEST['ComuneId']);   
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
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
                            
                            case "get_comune_rebuild":
				
                               get_comune_rebuild();    
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