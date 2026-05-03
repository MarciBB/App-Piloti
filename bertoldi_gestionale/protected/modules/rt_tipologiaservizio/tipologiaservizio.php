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

include_once($classespath_."class.TipologiaServizio.php");

$ModuloId=17;

function add()
{
global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  



include_once("tipologiaservizio_validator.php");       
 $HtmlCommon->html_titolo_pagina("Aggiungi tipologia servizio",0,"rt_tipologiaservizio","tipologiaservizio.php");

$HtmlCommon->html_titolo_box("Aggiungi tipologia servizio");  
$arr_stato[]= array("StatoId" => '0',"Stato" => 'Non Attivo');
$arr_stato[]= array("StatoId" => '1',"Stato" => 'Attivo');

?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action","create");
                $page->create_textbox("Tipologia Servizio","TipologiaServizio","TipologiaServizio[TipologiaServizio]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
               
               
                print("<br style=\"clear:both;\"/>");
                 $page->create_texarea("Descrizione","TipologiaServizioDescr","TipologiaServizio[TipologiaServizioDescr]","",1,"brain_campoForm",array("class"=>"'required'","cols"=>"'60'","rows"=>"'12'"));      
              
                print("<br style=\"clear:both;\"/>");
                 $page->create_textbox("Peso","Peso","TipologiaServizio[TipologiaServizioPeso]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
               
                $page->create_select("Stato","TipologiaServizio[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",
                array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
              
               
                ?>
                                      </div>
                         </div>
                             <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva","salva","brain_salva","submit");
                                  
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    </div>   
		</div>
                                    
                                    
<?
}



function edit($TipologiaServizioId)
{
include_once("tipologiaservizio_validator.php");      


  global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  


$tipologiaservizio=new TipologiaServizio($TipologiaServizioId);
$tipologiaservizio->Id=$TipologiaServizioId;
$tipologiaservizio->conn=$db;
$tipologiaservizio->inizializzaDatiGenerali();
$arrTipologiaServizio=$tipologiaservizio->DatiGenerali;






 $HtmlCommon->html_titolo_pagina("Edita Tipologia Servizio - ".$arrTipologiaServizio['TipologiaServizio'],1,"rt_tipologiaservizio","tipologiaservizio.php");
$HtmlCommon->html_titolo_box ("Edita Tipologia Servizio - ".$arrTipologiaServizio['TipologiaServizio']);
$arr_stato[]= array("StatoId" => '0',"Stato" => 'Disattiva');
$arr_stato[]= array("StatoId" => '1',"Stato" => 'Attiva');
?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$TipologiaServizioId);
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox("TipologiaServizio","Tipologia Servizio","TipologiaServizio[TipologiaServizio]",$arrTipologiaServizio['TipologiaServizio'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                 print("<br style=\"clear:both;\"/>");
                 $page->create_texarea("Descrizione","TipologiaServizio[TipologiaServizioDescr]","TipologiaServizio[TipologiaServizioDescr]",$arrTipologiaServizio['TipologiaServizioDescr'],1,"brain_campoForm",array("class"=>"'required'","cols"=>"'60'","rows"=>"'12'"));      
              
                 
                 
                print("<br style=\"clear:both;\"/>");     
                 $page->create_textbox("Peso","Peso","TipologiaServizio[TipologiaServizioPeso]",$arrTipologiaServizio['TipologiaServizioPeso'],1,"brain_campoForm",array("class"=>"'required'"));
                
               
                $page->create_select("Stato","TipologiaServizio[Stato]","StatoId","brain_campoForm",$arr_stato,$arrTipologiaServizio['Stato'],"StatoId","Stato",
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
                                  $page->create_button("Salva","Salva","salva","brain_salva","submit");
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
global $user,$HtmlCommon,$db,$ModuloId;   
$HtmlCommon->html_titolo_pagina("Elenco tipologie servizi");
$HtmlCommon->html_titolo_box("Elenco tipologie servizi");
$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tipologiaservizio','tipologiaservizio.php?do=add','aggiungi tipologia servizio');


include_once("tipologiaservizio_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="5%">stato</th>
            <th width="30%">tipologia servizio</th>
            <th width="30%">descrizione</th>
            <th width="10%">peso</th>
            <th width="5%">edita</th>
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
    <tfoot> 
        <tr>
            <td colspan="5" ></td>
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
			 $do=$_REQUEST['do'];
			if(!isset($do)) 
			$do='';
		
		
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
                                           edit($_REQUEST['TipologiaServizioId']);
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