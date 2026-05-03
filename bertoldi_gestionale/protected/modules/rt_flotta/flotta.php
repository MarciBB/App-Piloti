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
include_once($classespath_."class.TipologiaBus.php");
include_once($classespath_."class.Flotta.php");

$ModuloId=19;

function add()
{
global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  



include_once("flotta_validator.php");       
 $HtmlCommon->html_titolo_pagina($dizionario['flotta']['titolo_aggiungi'],0,"rt_flotta","flotta.php");

$HtmlCommon->html_titolo_box($dizionario['flotta']['titolo_aggiungi']);  
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

$tipologiabus=new TipologiaBus();
$tipologiabus->conn=$db;
$arr_tipologiabus=$tipologiabus->getAllForSelect();



?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action","create");
                
                  $page->create_select($dizionario['generale']['tipo'],"Flotta[TipologiaBusId]","TipologiaBusId","brain_campoForm",$arr_tipologiabus,1,"TipologiaBusId","TipologiaBus",
                  array("class"=>"'required'"),1);
                
                 print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['flotta']['modello'],"Modello","Flotta[Modello]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
                $page->create_textbox($dizionario['flotta']['targa'],"Targa","Flotta[Targa]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
              
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['cell'],"Cellulare","Flotta[Cellulare]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
              
                $page->create_select($dizionario['generale']['stato'],"Flotta[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",
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



function edit($flottaId)
{
include_once("flotta_validator.php");      


  global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  


$flotta=new Flotta($flottaId);
$flotta->Id=$flottaId;
$flotta->conn=$db;
$flotta->inizializzaDatiGenerali();
$arrflotta=$flotta->DatiGenerali;

$tipologiabus=new TipologiaBus();
$tipologiabus->conn=$db;
$arr_tipologiabus=$tipologiabus->getAllForSelect();





 $HtmlCommon->html_titolo_pagina($dizionario['flotta']['titolo_edit_mezzo']." - ".$arrflotta['Modello']." ".$arrflotta['Targa'],1,"rt_flotta","flotta.php");
$HtmlCommon->html_titolo_box ($dizionario['flotta']['titolo_edit_mezzo']." - ".$arrflotta['Modello']." ".$arrflotta['Targa']);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattiva']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attiva']);
?>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$flottaId);
                  $page->create_select($dizionario['generale']['tipo'],"Flotta[TipologiaBusId]","TipologiaBusId","brain_campoForm",$arr_tipologiabus,$arrflotta['TipologiaBusId'],"TipologiaBusId","TipologiaBus",
                  array("class"=>"'required'"),1);
                
                 print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['flotta']['modello'],"Modello","Flotta[Modello]",$arrflotta['Modello'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
                $page->create_textbox($dizionario['flotta']['targa'],"Targa","Flotta[Targa]",$arrflotta['Targa'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
              
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['cell'],"Cellulare","Flotta[Cellulare]",$arrflotta['Cellulare'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
              
                $page->create_select($dizionario['generale']['stato'],"Flotta[Stato]","StatoId","brain_campoForm",$arr_stato,$arrflotta['Stato'],"StatoId","Stato",
                array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
                ?>
            </div>
                         </div>
                        <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva",$dizionario['flotta']['salva'],"brain_salva","submit");
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
global $user,$HtmlCommon,$db,$ModuloId, $dizionario;   
$HtmlCommon->html_titolo_pagina($dizionario['flotta']['titolo_elenco_mezzi']);
$HtmlCommon->html_titolo_box($dizionario['flotta']['titolo_elenco_mezzi']);

$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_flotta','flotta.php?do=add',$dizionario['flotta']['aggiungi_mezzo']);


include_once("flotta_datatable.php");

?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="5%"><?=$dizionario['generale']['stato']?></th>
            <th width="15%"><?=$dizionario['flotta']['modello']?></th>
            <th width="15%"><?=$dizionario['flotta']['targa']?></th>
            <th width="15%"><?=$dizionario['generale']['tipo']?></th>
            <th width="5%"><?=$dizionario['flotta']['piani']?></th>
            <th width="5%"><?=$dizionario['flotta']['posti']?></th>
            <th width="5%"><?=$dizionario['generale']['edita']?></th>
            
        </tr>
        <tr class="brain_tabellaFilter">
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
            <td colspan="7" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
        </tr>
    </tbody>
    <tfoot> 
        <tr>
            <td colspan="7" ></td>
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
                                           edit($_REQUEST['FlottaId']);
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