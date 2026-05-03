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



function traduci($NazioneId)
{

    include_once("nazione_validator.php");      


global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  

$Nazione=new Nazione($NazioneId);
$Nazione->Id=$NazioneId;
$Nazione->conn=$db;
$Nazione->inizializzaDatiGenerali();
$arrNazione=$Nazione->DatiGenerali;

$HtmlCommon->html_titolo_pagina($dizionario['nazione']['titolo_traduci']." - ".$arrNazione['Nazione'],1,"nazione","nazione.php");
$HtmlCommon->html_titolo_box ($dizionario['nazione']['titolo_traduci']." - ".$arrNazione['Nazione']);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattiva']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attiva']);

$sql="Select * from CEV_Lingua where LinguaDefault=0 and OdcIdRef=$user->OdcId order by LinguaPeso asc";
$ArrObject = $db->fetch_array($sql);

?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                                                <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","updateTraduzione");
                $page->create_textbox_hidden("idpost",$NazioneId);
                ?>
                
                <div style="padding-bottom:5px"><?=$dizionario['nazione']['campo_tradurre']?> <strong>nome Nazione</strong></div>
                <div style="padding-bottom:5px"><?=$dizionario['nazione']['campo_lingua']?> <strong><?=$arrNazione['Nazione']?></strong></div>
                <div style="padding-bottom:5px"><i><?=$dizionario['nazione']['traduci_in']?></i></div>
                <?
                print("<br style=\"clear:both;\"/>");                
                foreach ($ArrObject as &$value) {
                    $LinguaIdTrad=$value['LinguaId'];
                    $LinguaNome=$value['LinguaNome'];
                    $nomeArr=$LinguaIdTrad."_NazioneTradNazione";
                    
                    $sql="Select * from NazioneTrad where NazioneTradLinguaId=$LinguaIdTrad and NazioneId=$NazioneId and OdcIdRef=$user->OdcId";                   
                    $ArrObject1 = $db->fetch_array($sql);                    
                    if(sizeof($ArrObject1)>0)
                        $valoreTrad=$ArrObject1[0]['NazioneTradNazione'];
                    else
                        $valoreTrad="";
                    $page->create_textbox($LinguaNome,"Nazione","Nazione[$nomeArr]",$valoreTrad,0,"brain_campoForm");                           
               
                }
                print("<br style=\"clear:both;\"/>");
                print("<br style=\"clear:both;\"/>");
                ?> 
                                                            
                </div>
                </div>
                <div class="divSubmit">
                <?
                $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
                //$page->create_button("Cancella","Cancella","elimina","brain_cancella","button");
                ?>
                </div>       
                </form>
            </div>   
        </div>
<?  
}

function show_list()
{
global $user,$HtmlCommon,$ModuloId, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['nazione']['titolo_elenco'],0,"","");
$HtmlCommon->html_titolo_box($dizionario['nazione']['titolo_elenco']);
$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','nazione','nazione.php?do=add',$dizionario['nazione']['aggiungi_nazione']);
include_once("nazione_datatable.php");
?>

		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
			<tr class="brain_tabellaTr">                              
			      <th width="95%"><?=$dizionario['comune']['nazione']?></th>                              
                              <th width="5%"><?=$dizionario['generale']['edita']?></th>
                              
                           
		      </tr>
		    
			<tr class="brain_tabellaFilter">
			       <th><input type="text" /></th> 
			       <th><input type="hidden" /></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="2" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		
		</table>

   <?
}



function cerca_nazione()
{
global $db;
include_once("nazione_datatable_cerca.php");
global $user,$HtmlCommon, $dizionario;
//$HtmlCommon->html_titolo_box("Ricerca comune");
$fieldtoupdate=$_REQUEST['fieldtoupdate'];
$labeltoupdate=$_REQUEST['labeltoupdate'];

?>

<input type="hidden" id="fieldtoupdate" value="<?=$fieldtoupdate?>" />
<input type="hidden" id="labeltoupdate" value="<?=$labeltoupdate?>" />            

<div class="SelezionaComune">
	
	<div class="SelezionaComuneHeader">
		<h2><?=$dizionario['nazione']['seleziona_nazione']?></h2>
		<a class="chiudi" href="javascript:void(0);" onclick="ChiudiElencoComuni();" title="Chiudi"><?=$dizionario['generale']['chiudi']?></a>
	</div>
	
	<div class="SelezionaComuneContent">
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables_nazione">
		<thead>
		    
			<tr class="brain_tabellaTr">
			      <th width="100%"><?=$dizionario['comune']['nazione']?></th>
			     
		      </tr>
		    
			<tr class="brain_tabellaFilter">
			       <th><input type="text" /></th> 
			      
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="1" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<th align="left" colspan="1"><a class="brain_add" href="javascript:void(0);" onclick="ExternalLoad('nazione','nazione.php?do=add',this);" title="Aggiungi Nazione"><i class="fa fa-plus" aria-hidden="true"></i>  <?=$dizionario['nazione']['aggiungi_nazione']?></a></th>
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
     
include_once("nazione_validator.php");  

$HtmlCommon->html_titolo_pagina($dizionario['nazione']['titolo_aggiungi'],1,"nazione","nazione.php");
$HtmlCommon->html_titolo_box($dizionario['nazione']['titolo_aggiungi']);


 $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattivo']);
 $arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

 
 
?>
 <div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                    
                            
                         <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica anagraficaSelector">
                                <div class="brain_data-content">    


        <?
        $page->create_textbox_hidden("action","create");        
               
        $page->create_textbox($dizionario['comune']['nazione'],"Nazione","Nazione","",1,"brain_campoForm",array("class"=>"'required'"));
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

 function edit($NazioneId)
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
$Nazione=new Nazione($NazioneId);
$Nazione->Id=$NazioneId;
$Nazione->conn=$db;
$Nazione->inizializzaDatiGenerali();
$arrNazione=$Nazione->DatiGenerali;


include_once("nazione_validator.php");       

$HtmlCommon->html_titolo_pagina($dizionario['nazione']['titolo_edita'],1,"nazione","nazione.php");
$HtmlCommon->html_titolo_box($dizionario['nazione']['titolo_edita']);

$azione="edit";

 $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattivo']);
 $arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
 
?>
 <div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                    
                            
     <form id="application_form" name="application_form" method="post" action="#">
     <div class="brain_formModifica anagraficaSelector">
            <div class="brain_data-content">
        <?
        $page->create_textbox_hidden("action","update");
        $page->create_textbox_hidden("NazioneId",$NazioneId);  
        
         $page->create_textbox($dizionario['comune']['nazione'],"Nazione","Nazione",$arrNazione['Nazione'],1,"brain_campoForm",array("class"=>"'required'"));
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
				
                                edit($_REQUEST['NazioneId']);   
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                case "cerca":
				
                               cerca_nazione();    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                           
                            case "traduci":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           traduci($_REQUEST['NazioneId']);
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