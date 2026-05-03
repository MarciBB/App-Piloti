<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();

include_once($classespath_."class.Form.php");
include_once($classespath_."class.Aula.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");

$ModuloId=16;

function add()
{
  global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  
$sedecorrente=$user->SedeId;
$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);


$sede=new Sede();
$sede->conn=$db;
$arr_sedi=$sede->getSediByGestori($InGestoreFigli);
//print_r($arr_gestori);
include_once("aula_validator.php");       
$HtmlCommon->html_titolo_pagina("Aggiungi aula");
$HtmlCommon->html_titolo_box("Aggiungi aula");  
 $arr_stato[]= array("StatoId" => '0',"Stato" => 'Disattiva');
 $arr_stato[]= array("StatoId" => '1',"Stato" => 'Attiva');
if (isset($_REQUEST['SedeId']))
    $sedecorrente=$_REQUEST['SedeId'];
 
?>

  <div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                    
                            
                         <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                             
                             
                           <?
                           
                           $page->create_textbox_hidden("action","create");
                           
                           
                  if (!$sedecorrente>0)         
                 $page->create_select("Sede","Aula[SedeId]","Aula[SedeId]","brain_campoForm",$arr_sedi,$sedecorrente,'SedeId','Sede',
                 array("class"=>"'required'"),1);
                 else
                 $page->create_select("Sede","Aula[SedeId]","Aula[SedeId]","brain_campoForm",$arr_sedi,$sedecorrente,'SedeId','Sede',
                 array("class"=>"'required'","javascript:onchange"=>"'this.selectedIndex = 1;'"),1);
                     
                  
                  print("<br style=\"clear:both;\"/>");
                           
                  $page->create_textbox("Aula","Aula[Aula]","Aula[Aula]","",1,"brain_campoForm",array("class"=>"'required'"));           
                
                  $page->create_textbox("Capienza","Aula[Capienza]","Aula[Capienza]","",1,"brain_campoForm",array("class"=>"'required digits'","maxlength"=>"'2'","maxsize"=>"'2'"));           
                
                 print("<br style=\"clear:both;\"/>");

                $page->create_select("Stato","Aula[Stato]","Aula[Stato]","brain_campoForm",$arr_stato,1,"StatoId","Stato",
                         array("class"=>"'required'"),1);

                 print("<br style=\"clear:both;\"/>");
               
                           
                           
                           ?>
                             
                        </div>
                         </div>
                        <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva","salva aula","brain_salva","submit");
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
include_once("aula_validator.php");      
$AulaId=$_REQUEST['AulaId'];

  global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  



$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);
$sede=new Sede();
$sede->conn=$db;
$arr_sedi=$sede->getSediByGestori($InGestoreFigli);


$aula=new Aula();
$aula->conn=$db;
$aula->inizializza($AulaId);

$Aulacorrente=$aula->Aula;
$Capienza=$aula->Capienza;
$Stato=$aula->Stato;
$Sedecorrente=$aula->SedeId;


if (isset($_REQUEST['SedeId']))
    $sedecorrente=$_REQUEST['SedeId'];

 
$HtmlCommon->html_titolo_pagina("Edita Aula - ".$Aulacorrente);
$HtmlCommon->html_titolo_box ("Edita Aula - ".$Aulacorrente);
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
                            $page->create_textbox_hidden("idpost",$AulaId);
                           
                           
                           
                if (!$sedecorrente>0)         
                 $page->create_select("Sede","Aula[SedeId]","Aula[SedeId]","brain_campoForm",$arr_sedi,$sedecorrente,'SedeId','Sede',
                 array("class"=>"'required'"),1);
                 else
                 $page->create_select("Sede","Aula[SedeId]","Aula[SedeId]","brain_campoForm",$arr_sedi,$sedecorrente,'SedeId','Sede',
                 array("class"=>"'required'","onchange"=>"'this.selectedIndex = 1;alert(\"Non è possibile cambiare la sede\")'"),1);
                  print("<br style=\"clear:both;\"/>");
                           
                  $page->create_textbox("Aula","Aula[Aula]","Aula[Aula]",$Aulacorrente,1,"brain_campoForm",array("class"=>"'required'"));           
                
                  $page->create_textbox("Capienza","Aula[Capienza]","Aula[Capienza]",$Capienza,1,"brain_campoForm",array("class"=>"'required digits'","maxlength"=>"'2'","maxsize"=>"'2'"));           
                
                 print("<br style=\"clear:both;\"/>");

                $page->create_select("Stato","Aula[Stato]","Aula[Stato]","brain_campoForm",$arr_stato,$Stato,"StatoId","Stato",
                         array("class"=>"'required'"),1);

                 print("<br style=\"clear:both;\"/>");
                           
                           
                           ?>
                             
                             
                              		
					
                       </div>
                         </div>
                        <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva","salva aula","brain_salva","submit");
                                 
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    </div>   
		</div>


<?
    
    
    
}


function show_list()
{
global $user,$HtmlCommon;
$HtmlCommon->html_titolo_pagina("Elenco sedi");
$HtmlCommon->html_titolo_box("Elenco sedi");
$db= new Database();
$db->connect();

                /*$mysqli=new mysqli("localhost","dbadmin","dbadmin" ,"concilia_odc");
		$SQL = "call Gestore_proc('S',1,0,0,'')";  
                // 1. (S=Select, I=Insert, U=Update, D=delete) 2. tipo,gestoreid,odcid,nuovo gestore padre,nuovo nome gestore  

		if (($result = $mysqli->query($SQL))===false) {
		printf("Invalid query: %s\nWhole query: %s\n", $mysqli->error, $SQL);
		exit();
		} 
                while ($myrow=$result->fetch_array(MYSQLI_ASSOC))
                {
                    echo($myrow["GestoreId"]."-".$myrow['RagioneSociale']."<br />");
                    
                }
                $result->close();
                $mysqli->close();
                exit();*/
                



include_once("aula_datatable.php");

?>


            

               
                
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
	<thead>
            
            	<tr class="brain_tabellaTr">
                      
			<th width="10%">aula</th>
		        <th width="5%">capienza</th>
                        <th width="30%">indirizzo</th>
                        <th width="25%">comune</th>
                        <th width="25%">gestore</th>
	                <th width="5%">edita</th>
                      
		</tr>
            
		<tr class="brain_tabellaFilter">
		      
                       
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
                        
			<td colspan="9" class="dataTables_empty">Caricamento in corso...</td>
                       
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
	    if (!empty($_REQUEST)){
			$do = $_REQUEST['do'];
		} else {
			$do='';
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