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

include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Orario.php");
$ModuloId=28;

global $tratta_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$tratta_wizard=null;

if(isset($_SESSION['TRATTA_WIZARD'])) {
$tratta_wizard=unserialize($_SESSION['TRATTA_WIZARD']);
}

function add()
{
global $HtmlCommon,$user,$tratta_wizard, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  

 $tratta_wizard->conn=$db;
    $tratta_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
    $_SESSION['TRATTA_WIZARD']=serialize($tratta_wizard);

$corsa=new Corsa();
$corsa->conn=$db;
$arrCorsa=$corsa->getAllByLineaId($DatiGeneraliArr['LineaId']);


include_once("orario_validator.php");       
 $HtmlCommon->html_titolo_pagina($dizionario['ora']['titolo_aggiungi'],0,"rt_orario","orario.php");

$HtmlCommon->html_titolo_box("Aggiungi orario");  
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

?>
<script type="text/javascript"> 
     $(document).ready(function() {
        
         $("#OrarioId").mask("99:99");   
        
   

 });
        
</script>


<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action","create");
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['ora']['orario_fermata'],"OrarioId","Orario[Orario]","",1,"brain_campoForm",array("class"=>"'required'"),"","3");

                  print("<br style=\"clear:both;\"/>");            
                $page->create_select($dizionario['generale']['corsa'],"Orario[CorsaId]","CorsaId","brain_campoForm",$arrCorsa,0,"CorsaId","CorsaNome",
                     array("class"=>"'required'"),1);
                
                
                print("<br style=\"clear:both;\"/>");            
                $page->create_select($dizionario['generale']['stato'],"Orario[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",
                     array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>"); 
                
                
               
                if (isset($_REQUEST['FermataId'])) {
               ?>
                <input type="hidden" name="Orario[FermataId]" value="<?=$_REQUEST['FermataId']?>" />                    
                <? }
               
                
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



function edit($OrarioId)
{
include_once("orario_validator.php");      


  global $HtmlCommon,$user,$tratta_wizard, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  
$dt=new DT();

$orario=new Orario();
$orario->Id=$OrarioId;
$orario->conn=$db;
$orario->inizializzaDatiGenerali();
$arrOrario=$orario->DatiGenerali;

     $tratta_wizard->conn=$db;
    $tratta_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$tratta_wizard->DatiGenerali;
    $_SESSION['TRATTA_WIZARD']=serialize($tratta_wizard);

$corsa=new Corsa();
$corsa->conn=$db;
$arrCorsa=$corsa->getAllByLineaId($DatiGeneraliArr['LineaId']);


 $HtmlCommon->html_titolo_pagina($dizionario['ora']['titolo_modifica'],0,"rt_orario","corsa.php");
$HtmlCommon->html_titolo_box ($dizionario['ora']['titolo_modifica']." - ".$arrOrario['Orario']." corsa: ".$arrOrario['CorsaNome']);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
?>
<script type="text/javascript"> 
    $(document).ready(function() {
        
         $("#OrarioId").mask("99:99");   
        
   

 });
        
</script>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                
             
                
                
                
                
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$OrarioId);
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['ora']['orario_fermata'],"OrarioId","Orario[Orario]",$arrOrario['Orario'],1,"brain_campoForm",array("class"=>"'required'"),"","3");

                  print("<br style=\"clear:both;\"/>");            
                $page->create_select($dizionario['generale']['corsa'],"Orario[CorsaId]","CorsaId","brain_campoForm",$arrCorsa,$arrOrario['CorsaId'],"CorsaId","CorsaNome",
                     array("class"=>"'required'"),1);
                
                
                print("<br style=\"clear:both;\"/>");            
                $page->create_select($dizionario['generale']['stato'],"Orario[Stato]","StatoId","brain_campoForm",$arr_stato,$arrOrario['Stato'],"StatoId","Stato",
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
                                  $page->create_button("Salva","Salva",$dizionario['salva']['corsa'],"brain_salva","submit");
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
                                           edit($_REQUEST['OrarioId']);
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