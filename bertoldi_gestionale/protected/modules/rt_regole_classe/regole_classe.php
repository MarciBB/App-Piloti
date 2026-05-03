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
include_once($classespath_."class.Provvigione.php");
include_once($classespath_."class.RegolaRimborso.php");

$ModuloId=43;

function add()
{
    global $HtmlCommon,$user, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  

	include_once("regole_classe_validator.php");       
 	$HtmlCommon->html_titolo_pagina($dizionario['regole']['titolo_aggiungi_rimborso'],0,"rt_regole_classe","regole_classe.php");

	$HtmlCommon->html_titolo_box($dizionario['regole']['titolo_aggiungi_rimborso']);  
	$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
	$arr_tipo[]= array("TipoPrenotazione" => 'I',"Valore" => $dizionario['regole']['internazionale']);
	$arr_tipo[]= array("TipoPrenotazione" => 'N',"Valore" => $dizionario['regole']['nazionale']);
	$arr_tipo[]= array("TipoPrenotazione" => 'IN',"Valore" => $dizionario['regole']['int_naz']);
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action","create");
      			print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['regole']['aggiungi_reg_rimborso'],"NomeRegola","RegolaRimborso[NomeRegola]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['regole']['giorni_prima'],"GiorniPrima","RegolaRimborso[GiorniPrima]","",1,"brain_campoForm",array("class"=>"'required'"));
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['regole']['ore_prima'],"OrePrima","RegolaRimborso[OrePrima]","",1,"brain_campoForm",array("class"=>"'required'"));
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['regole']['tipo_p'],"RegolaRimborso[TipoPrenotazione]","TipoPrenotazione","brain_campoForm",$arr_tipo,1,"TipoPrenotazione","Valore",array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['generale']['stato'],"RegolaRimborso[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",array("class"=>"'required'"),1);
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


function edit($ProvvigioneId) {
	include_once("regole_classe_validator.php");      
	global $HtmlCommon,$user, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  

	$RegolaRimborso = new RegolaRimborso($ProvvigioneId);
	$RegolaRimborso->conn=$db;
	$RegolaRimborso->inizializzaDatiGenerali();
	$arrProvvigione = $RegolaRimborso->DatiGenerali;

	$HtmlCommon->html_titolo_pagina($dizionario['regole']['titolo_edit_rimborso'],0,"rt_regole_classe","regole_classe.php");
	$HtmlCommon->html_titolo_box ($dizionario['regole']['titolo_edit_rimborso']." - ".$arrProvvigione['NomeRegola']);
	$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattiva']);
	$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attiva']);
	
	$arr_tipo[]= array("TipoPrenotazione" => 'I',"Valore" => $dizionario['regole']['internazionale']);
	$arr_tipo[]= array("TipoPrenotazione" => 'N',"Valore" => $dizionario['regole']['nazionale']);
	$arr_tipo[]= array("TipoPrenotazione" => 'IN',"Valore" => $dizionario['regole']['int_naz']);
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$ProvvigioneId);
                ?>
				<div id="elenco_comuni"></div>
    			<?
      			print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['regole']['reg_di_rimborso'],"NomeRegola","RegolaRimborso[NomeRegola]",$arrProvvigione['NomeRegola'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['regole']['giorni_prima'],"GiorniPrima","RegolaRimborso[GiorniPrima]",$arrProvvigione['GiorniPrima'],1,"brain_campoForm",array("class"=>"'required'"));
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['regole']['ore_prima'],"OrePrima","RegolaRimborso[OrePrima]",$arrProvvigione['OrePrima'],1,"brain_campoForm",array("class"=>"'required'"));
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['regole']['tipo_p'],"RegolaRimborso[TipoPrenotazione]","TipoPrenotazione","brain_campoForm",$arr_tipo,$arrProvvigione['TipoPrenotazione'],"TipoPrenotazione","Valore",array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['generale']['stato'],"RegolaRimborso[Stato]","StatoId","brain_campoForm",$arr_stato,$arrProvvigione['Stato'],"StatoId","Stato",array("class"=>"'required'"),1);
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
                	edit($_REQUEST['RimborsoRegolaId']);
                else
                    Errors::$ErrorePermessiModuloFunzione;                   
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
			default:
				$FunzioneId=2;
                add(); 
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