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

$ModuloId=16;

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
include_once("sede_validator.php");       
$HtmlCommon->html_titolo_pagina($dizionario['sede']['titolo_aggiungi']);
$HtmlCommon->html_titolo_box($dizionario['sede']['titolo_aggiungi']);  
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

$arr_sino[]= array("VenditaOltreOrarioId" => '0',"VenditaOltreOrario" =>  $dizionario['generale']['no']);
$arr_sino[]= array("VenditaOltreOrarioId" => '1',"VenditaOltreOrario" => $dizionario['generale']['si']);

?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action","create");
                $page->create_textbox($dizionario['generale']['indirizzo'],"Indirizzo","Sede[Indirizzo]","",1,"brain_campoForm",array("class"=>"'required'"));           
                $page->create_textbox_hidden_differentId("ComuneResidenzaId","Sede[ComuneId]",$ComuneResidenzaId);
                $selector="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'comune','comune.php?do=cerca&fieldtoupdate=ComuneResidenzaId&labeltoupdate=ComuneResidenza','bottom');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona comune\" width=\"26\" height=\"27\" border=\"0\" /></a>";
                $page->create_textbox_with_sel($dizionario['sede']['comune_sede'],"ComuneResidenza","ComuneResidenza",$ComuneResidenza,1,"brain_campoForm",array("class"=>"'required'","readonly"=>"'readonly'"),$selector);           
                print("<div id=\"elenco_comuni\"></div>");
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['telefono'],"Telefono","Sede[Telefono]","",0,"brain_campoForm",null);
                $page->create_textbox($dizionario['generale']['fax'],"Fax","Sede[Fax]","",0,"brain_campoForm",null);
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['email'],"Email","Sede[Email]",$Email,0,"brain_campoForm",array("class"=>"'email'"));
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['sede']['codice_sede'],"CodiceSede","Sede[CodiceSede]","",1,"brain_campoForm",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['sede']['vendita_ora'],"Sede[VenditaOltreOrario]","VenditaOltreOrarioId","brain_campoForm",$arr_sino,0,"VenditaOltreOrarioId","VenditaOltreOrario",
                array("class"=>"'required'"),1);
                
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['generale']['stato'],"Sede[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",
                array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
                if (isset($_REQUEST['GestoreId'])) {
               ?>
                <input type="hidden" name="Sede[GestoreId]" value="<?=$_REQUEST['GestoreId']?>" />                    
                <? }
                else
                $page->create_select($dizionario['gestore']['gestore'],"Sede[GestoreId]","GestoreId","brain_campoForm",$arr_gestori,$user->GestoreId,'GestoreId','RagioneSociale',
                array("class"=>"'required'"),1);
                
                
                print("<br style=\"clear:both;\"/>");
                ?>
                                      </div>
                         </div>
            <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
                                  
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    </div>   
		</div>
                                    
                                    
<?
}



function edit()
{
include_once("sede_validator.php");      
$SedeId=$_REQUEST['SedeId'];

  global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  
$gestore=new Gestore();
$gestore->conn=$db;
$gestore->getGestoreAll($user->GestoreId);
$arr_gestori=$gestore->ArrGestore;

$sede=new Sede();
$sede->conn=$db;
$sede->inizializza($SedeId);

$GestoreId=$sede->GestoreId;
$Gestore=$sede->Gestore;
$ComuneId=$sede->ComuneId;
$Indirizzo=$sede->Indirizzo;
$Comune=$sede->Comune;
$Telefono=$sede->Telefono;
$Fax=$sede->Fax;
$Email=$sede->Email;
$Stato=$sede->Stato;
$CodiceSede=$sede->CodiceSede; 
$VenditaOltreOrario=$sede->VenditaOltreOrario;
$HtmlCommon->html_titolo_pagina($dizionario['sede']['titolo_edita']." - ".$Indirizzo." ".$Comune);
$HtmlCommon->html_titolo_box ($dizionario['sede']['titolo_edita']." - ".$Indirizzo." ".$Comune);
$arr_stato[]= array("StatoId" => '0',"Stato" =>  $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
$arr_sino[]= array("VenditaOltreOrarioId" => '0',"VenditaOltreOrario" => $dizionario['generale']['no']);
$arr_sino[]= array("VenditaOltreOrarioId" => '1',"VenditaOltreOrario" => $dizionario['generale']['si']);
?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$SedeId);
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['indirizzo'],"Indirizzo","Sede[Indirizzo]",$Indirizzo,1,"brain_campoForm",array("class"=>"'required'"));           
                $page->create_textbox_hidden_differentId("ComuneResidenzaId","Sede[ComuneId]",$ComuneId);
                $selector="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'comune','comune.php?do=cerca&fieldtoupdate=ComuneResidenzaId&labeltoupdate=ComuneResidenza','bottom');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona comune\" width=\"26\" height=\"27\" border=\"0\" /></a>";
                $page->create_textbox_with_sel($dizionario['sede']['comune_sede'],"ComuneResidenza","ComuneResidenza",$Comune,1,"brain_campoForm",array("class"=>"'required'","readonly"=>"'readonly'"),$selector);           
                print("<div id=\"elenco_comuni\"></div>");
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['telefono'],"Telefono","Sede[Telefono]",$Telefono,0,"brain_campoForm",null);
                $page->create_textbox($dizionario['generale']['fax'],"Fax","Sede[Fax]",$Fax,0,"brain_campoForm",null);
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['email'],"Email","Sede[Email]",$Email,0,"brain_campoForm",array("class"=>"'email'"));
                    print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['sede']['vendita_ora'],"Sede[VenditaOltreOrario]","VenditaOltreOrarioId","brain_campoForm",$arr_sino,$VenditaOltreOrario,"VenditaOltreOrarioId","VenditaOltreOrario",
                array("class"=>"'required'"),1);
              
                
                
                print("<br style=\"clear:both;\"/>");            
                $page->create_select($dizionario['generale']['stato'],"Sede[Stato]","StatoId","brain_campoForm",$arr_stato,$Stato,"StatoId","Stato",
                     array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
                
                /*$page->create_select("Gestore","Sede[GestoreId]","GestoreId","brain_campoForm",$arr_gestori,$GestoreId,"GestoreId","RagioneSociale",
                array("class"=>"'required'"),1);*/
                
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
global $user,$HtmlCommon, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['sede']['titolo_elenco']);
$HtmlCommon->html_titolo_box($dizionario['sede']['titolo_elenco']);
$db= new Database();
$db->connect();
include_once("sede_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="20%"><?=$dizionario['gestore']['gestore']?></th>
            <th width="20%"><?=$dizionario['generale']['comune']?></th>
            <th width="25%"><?=$dizionario['generale']['indirizzo']?></th>
            <th width="5%"><?=$dizionario['generale']['telefono']?></th>
            <th width="5%"><?=$dizionario['generale']['fax']?></th>
            <th width="10%"><?=$dizionario['generale']['email']?></th>
            <th width="10%"><?=$dizionario['sede']['codice']?></th>
            <th width="5%"><?=$dizionario['generale']['edita']?></th>
        </tr>
        <tr class="brain_tabellaFilter">
            <th><span></span><input type="hidden" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="hidden" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="8" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
        </tr>
    </tbody>
    <tfoot> 
        <tr>
            <td colspan="8" ></td>
        </tr> 
    </tfoot> 
</table>

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
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            add();
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;
                                        
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "edit":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           edit($_REQUEST['SedeId']);
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