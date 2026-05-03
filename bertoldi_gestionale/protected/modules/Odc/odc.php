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

$ModuloId=24;

function add()
{
  global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  
$sedecorrente=$user->SedeId;
$odc=new Odc();
$odc->conn=$db;



include_once("odc_validator.php");       
$HtmlCommon->html_titolo_pagina($dizionario['odc']['aggiungi']);
$HtmlCommon->html_titolo_box($dizionario['odc']['aggiungi']);  
 $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattiva']);
 $arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attiva']);

 
?>

  <div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                    
                            
                         <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                             
                             
                           <?
                           
                           $page->create_textbox_hidden("action","create");
                           
                           
                  if (!$sedecorrente>0)         
                 $page->create_select($dizionario['generale']['sede'],"Aula[SedeId]","Aula[SedeId]","brain_campoForm",$arr_sedi,$sedecorrente,'SedeId','Sede',
                 array("class"=>"'required'"),1);
                 else
                 $page->create_select($dizionario['generale']['sede'],"Aula[SedeId]","Aula[SedeId]","brain_campoForm",$arr_sedi,$sedecorrente,'SedeId','Sede',
                 array("class"=>"'required'","javascript:onchange"=>"'this.selectedIndex = 1;'"),1);
                     
                  
                  print("<br style=\"clear:both;\"/>");
                           
                  $page->create_textbox($dizionario['odc']['aula'],"Aula[Aula]","Aula[Aula]","",1,"brain_campoForm",array("class"=>"'required'"));           
                
                  $page->create_textbox($dizionario['odc']['capienza'],"Aula[Capienza]","Aula[Capienza]","",1,"brain_campoForm",array("class"=>"'required digits'","maxlength"=>"'2'","maxsize"=>"'2'"));           
                
                 print("<br style=\"clear:both;\"/>");

                $page->create_select($dizionario['generale']['stato'],"Aula[Stato]","Aula[Stato]","brain_campoForm",$arr_stato,1,"StatoId","Stato",
                         array("class"=>"'required'"),1);

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
    
    
    
}



function edit()
{
include_once("odc_validator.php");      
$OdcId=$_REQUEST['OdcId'];

  global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  

$odc=new Odc($OdcId);
$odc->conn=$db;
$odc->inizializzaDatiGenerali($OdcId);
$arr_odc=$odc->OdcDatiGenerali;

$HtmlCommon->html_titolo_pagina($dizionario['odc']['titolo_edita']." - ".$arr_odc['Odc']);
$HtmlCommon->html_titolo_box ($dizionario['odc']['titolo_edita']." - ".$arr_odc['Odc']);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
    
 
?>

<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                    
                            
                         <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                           <?
                           
                            $page->create_textbox_hidden("action","update");
                            $page->create_textbox_hidden("id",$OdcId);
                           
                           
                           
                
                           
                  $page->create_textbox($dizionario['odc']['organismo'],"Odc[Odc]","Odc[Odc]",$arr_odc['Odc'],1,"brain_campoForm",array("class"=>"'required'"));           
                  $page->create_textbox($dizionario['odc']['n_autorizzazioni'],"Odc[NumeroAutorizzazione]","Odc[NumeroAutorizzazione]",$arr_odc['NumeroAutorizzazione'],1,"brain_campoForm",array("class"=>"'required digits'"));           
                
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['odc']['mittente'],"Odc[EmailSmtp]","Odc[NomeEmailSmtp]",$arr_odc['NomeEmailSmtp'],1,"brain_campoForm",array("class"=>"'required'"));           
                 $page->create_textbox($dizionario['odc']['mittente_email'],"Odc[EmailSmtp]","Odc[EmailSmtp]",$arr_odc['EmailSmtp'],1,"brain_campoForm",array("class"=>"'required'"));           
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['odc']['server'],"Odc[ServerSmtp]","Odc[ServerSmtp]",$arr_odc['ServerSmtp'],1,"brain_campoForm",array("class"=>"'required'"));           
                 $page->create_textbox($dizionario['odc']['mittente_email'],"Odc[PortaSmtp]","Odc[PortaSmtp]",$arr_odc['PortaSmtp'],1,"brain_campoForm",array("class"=>"'required'"));           
                print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['odc']['username'],"Odc[UserSmtp]","Odc[UserSmtp]",$arr_odc['UserSmtp'],1,"brain_campoForm",array("class"=>"'required'"));           
                 $page->create_textbox_password($dizionario['odc']['password'],"Odc[PwdSmtp]","Odc[PwdSmtp]",$arr_odc['PwdSmtp'],1,"brain_campoForm",array("class"=>"'required'"));           
                 print("<br style=\"clear:both;\"/>");
                

                $page->create_select($dizionario['generale']['stato'],"Odc[Stato]","Odc[Stato]","brain_campoForm",$arr_stato,$arr_odc['Stato'],"StatoId","Stato",
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


function show_list()
{
global $user,$HtmlCommon, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['odc']['titolo_elenco']);
$HtmlCommon->html_titolo_box($dizionario['odc']['titolo_elenco']);
$db= new Database();
$db->connect();


global $user,$HtmlCommon,$db,$ModuloId;    
 

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','odc','odc.php?do=add',$dizionario['odc']['aggiungi']);
                



include_once("odc_datatable.php");

?>

                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
            
            	<tr class="brain_tabellaTr">
                      
			<th width="80%"><?=$dizionario['odc']['organismo']?></th>
		        <th width="20%"><?=$dizionario['odc']['n_autorizzazioni']?></th>
                        <th width="5%"><?=$dizionario['generale']['edita']?></th>
                      
		</tr>
            
		<tr class="brain_tabellaFilter">
		      
                       
			<th><input type="text" /></th> 
                        <th><input type="text" /></th> 
			<th><input type="hidden" /></th> 
			
		</tr>
	</thead>
	<tbody>
         
		<tr>
                        
			<td colspan="9" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
                       
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