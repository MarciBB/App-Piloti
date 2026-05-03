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
include_once($classespath_."class.Listino.php");


$ModuloId=3;

function add()
{
    global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  

include_once("listino_classe_validator.php");       
 $HtmlCommon->html_titolo_pagina($dizionario['listino']['aggiungi_classe'],0,"rt_listino_classe","listino_classe.php");

$HtmlCommon->html_titolo_box($dizionario['listino']['aggiungi_classe']);  
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
$arr_is[]= array("ArrIsId" => '0',"ArrIs" => $dizionario['generale']['no']);
$arr_is[]= array("ArrIsId" => '1',"ArrIs" => $dizionario['generale']['si']);
?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action","create");
      
                 print("<br style=\"clear:both;\"/>");

                
                
                
                $page->create_textbox($dizionario['listino']['classe_di_listino'],"ListinoNome","Listino[ListinoNome]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                
                 print("<br style=\"clear:both;\"/>");
                 
               
                $page->create_textbox($dizionario['generale']['peso'],"Peso","Listino[ListinoPeso]","",1,"brain_campoForm",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['generale']['stato'],"Listino[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",
                array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
               
                
                
                
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



function edit($ListinoId)
{
include_once("listino_classe_validator.php");      


  global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  


$Listino=new Listino($ListinoId);
$Listino->conn=$db;
$Listino->inizializzaDatiGenerali();
$arrListino=$Listino->DatiGenerali;



 $HtmlCommon->html_titolo_pagina($dizionario['listino']['titolo_edita_classe'],0,"rt_listino_classe","listino_classe.php");
$HtmlCommon->html_titolo_box ($dizionario['listino']['titolo_edita_classe']." - ".$arrFermata['ListinoNome']);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattiva']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attiva']);
$arr_is[]= array("ArrIsId" => '0',"ArrIs" => $dizionario['generale']['no']);
$arr_is[]= array("ArrIsId" => '1',"ArrIs" => $dizionario['generale']['si']);
?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$ListinoId);
              
                ?>

        
<div id="elenco_comuni"></div>
    <?
    print("<br style=\"clear:both;\"/>");
                
                
               $page->create_textbox($dizionario['listino']['classe_di_listino'],"Listino","Listino[ListinoNome]",$arrListino['ListinoNome'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                 print("<br style=\"clear:both;\"/>");
               
                    
                      $page->create_textbox($dizionario['generale']['peso'],"Peso","Listino[ListinoPeso]",$arrListino['ListinoPeso'],1,"brain_campoForm",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                    
                $page->create_select($dizionario['generale']['stato'],"Listino[Stato]","StatoId","brain_campoForm",$arr_stato,$arrListino['Stato'],"StatoId","Stato",
                     array("class"=>"'required'"),1);
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
$HtmlCommon->html_titolo_pagina("Elenco sedi");
$HtmlCommon->html_titolo_box("Elenco sedi");
$db= new Database();
$db->connect();
include_once("sede_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="20%">gestore</th>
            <th width="20%">comune</th>
            <th width="25%">indirizzo</th>
            <th width="5%">telefono</th>
            <th width="5%">fax</th>
            <th width="10%">email</th>
            <th width="10%">codice</th>
            <th width="5%">edita</th>
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
            <td colspan="8" class="dataTables_empty">Caricamento in corso...</td>
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
                                           edit($_REQUEST['ListinoId']);
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