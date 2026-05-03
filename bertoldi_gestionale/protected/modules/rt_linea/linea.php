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


include_once($classespath_."class.Linea.php");
$ModuloId=28;

function add() {
    global $HtmlCommon, $user, $dizionario;

    $db = new Database();
    $db->connect();
    $page = new Form();

    include_once("linea_validator.php");

    $HtmlCommon->html_titolo_pagina($dizionario['linea']['titolo_aggiungi'], 0, "rt_linea", "linea.php");
    $HtmlCommon->html_titolo_box($dizionario['linea']['titolo_aggiungi']);

    // Stato options
    $arr_stato = [
        ["StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']],
        ["StatoId" => '1', "Stato" => $dizionario['generale']['attivo']]
    ];

    // Tour type options
    $arr_tour = [
        ["TipoTourId" => '0', "TipoTour" => $dizionario['generale']['tour_gruppo']],
        ["TipoTourId" => '1', "TipoTour" => $dizionario['generale']['tour_privato']]
    ];

    // Yes/No options
    $arr_sino = [
        ["Id" => '0', "Value" => $dizionario['generale']['no']],
        ["Id" => '1', "Value" => $dizionario['generale']['si']]
    ];
    ?>
    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_formModifica">
                    <div class="brain_data-content">
                        <?php
                        $page->create_textbox_hidden("action", "create");

                        $page->create_textbox(
                            $dizionario['generale']['linea'],
                            "LineaNome",
                            "Linea[LineaNome]",
                            "",
                            1,
                            "brain_campoForm campiformBig",
                            ["class" => "'required'"]
                        );
                        print("<br style=\"clear:both;\"/>");

                        $page->create_texarea(
                            $dizionario['generale']['descrizione'],
                            "LineaDescrizione",
                            "Linea[LineaDescrizione]",
                            "",
                            1,
                            "brain_campoForm",
                            ["cols" => "'60'", "rows" => "'5'"]
                        );
                        print("<br style=\"clear:both;\"/>");

                        $page->create_textbox(
                            $dizionario['linea']['area'],
                            "Area",
                            "Linea[LineaArea]",
                            "",
                            1,
                            "brain_campoForm campiformBig",
                            ["class" => "'required'"]
                        );

                        $page->create_select(
                            $dizionario['generale']['tipo_tour'],
                            "Linea[TipoTour]",
                            "TipoTourId",
                            "brain_campoForm",
                            $arr_tour,
                            0,
                            "TipoTourId",
                            "TipoTour",
                            ["class" => "'required'"],
                            1
                        );

                        $page->create_textbox(
                            $dizionario['generale']['peso'],
                            "Peso",
                            "Linea[LineaPeso]",
                            "",
                            1,
                            "brain_campoForm",
                            ["class" => "'required'"]
                        );
                        print("<br style=\"clear:both;\"/>");

                        $page->create_textbox(
                            $dizionario['generale']['da'],
                            "LineaDa",
                            "Linea[LineaDa]",
                            "",
                            1,
                            "brain_campoForm campiformBig",
                            ["class" => "'required'"]
                        );

                        $page->create_textbox(
                            $dizionario['generale']['a'],
                            "LineaA",
                            "Linea[LineaA]",
                            "",
                            1,
                            "brain_campoForm campiformBig",
                            ["class" => "'required'"]
                        );
                        print("<br style=\"clear:both;\"/>");

                        $page->create_textbox(
                            $dizionario['linea']['termine_max_rimborso'],
                            "TempoMaxAnnullamento",
                            "Linea[TempoMaxAnnullamento]",
                            "",
                            1,
                            "brain_campoForm campiformBig",
                            ["class" => "'required'"]
                        );

                        $page->create_textbox(
                            $dizionario['linea']['termine_max_modifica'],
                            "TempoMaxModifica",
                            "Linea[TempoMaxModifica]",
                            "",
                            1,
                            "brain_campoForm campiformBig",
                            ["class" => "'required'"]
                        );
                        print("<br style=\"clear:both;\"/>");

                        $page->create_textbox(
                            $dizionario['generale']['cell'],
                            "Cellulare",
                            "Linea[Cellulare]",
                            "",
                            1,
                            "brain_campoForm campiformBig",
                            ["class" => "'required'"]
                        );
                        print("<br style=\"clear:both;\"/>");

                        $page->create_textbox(
                            $dizionario['linea']['link_descrizione'],
                            "LinkDescrizione",
                            "Linea[LinkDescrizione]",
                            "",
                            0,
                            "brain_campoForm campiformBig",
                            null
                        );
                        print("<br style=\"clear:both;\"/>");

                        $page->create_textbox(
                            $dizionario['linea']['yolo_codice_fascia'],
                            "YoloCodiceFascia",
                            "Linea[YoloCodiceFascia]",
                            "",
                            0,
                            "brain_campoForm campiformBig",
                            null
                        );
                        print("<br style=\"clear:both;\"/>");

                        $page->create_select(
                            $dizionario['fermata']['webselling'],
                            "Linea[IsWebSelling]",
                            "IsWebSelling",
                            "brain_campoForm",
                            $arr_sino,
                            1,
                            "Id",
                            "Value",
                            ["class" => "'required'"],
                            1
                        );

                        $page->create_select(
                            $dizionario['generale']['stato'],
                            "Linea[Stato]",
                            "StatoId",
                            "brain_campoForm",
                            $arr_stato,
                            1,
                            "StatoId",
                            "Stato",
                            ["class" => "'required'"],
                            1
                        );
                        print("<br style=\"clear:both;\"/>");

                        ?>
                        <input type="hidden" name="Linea[PercorsoId]" value="<?= $_REQUEST['PercorsoId'] ?>" />
                        <?php
                        
                        print("<br style=\"clear:both;\"/>");
                        ?>
                    </div>
                </div>
                <div class="divSubmit">
                    <?php
                    $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit");
                    ?>
                </div>
            </form>
        </div>
    </div>
    <?php
}




function edit($LineaId) {
	include_once("linea_validator.php");      
	global $HtmlCommon,$user, $dizionario;  

	$db= new Database();
	$db->connect();  
	$page=new Form();  


	$linea=new Linea();
	$linea->Id=$LineaId;
	$linea->conn=$db;
	$linea->inizializzaDatiGenerali();
	$arrLinea=$linea->DatiGenerali;



	$HtmlCommon->html_titolo_pagina($dizionario['linea']['titolo_edita'],0,"rt_linea","linea.php");
	$HtmlCommon->html_titolo_box ($dizionario['linea']['titolo_edita']." - ".$arrLinea['LineaNome']);
	
	$arr_stato = array();
	$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
	
	$arr_tour = array();
	$arr_tour[]= array("TipoTourId" => '0',"TipoTour" => $dizionario['generale']['tour_gruppo']);
	$arr_tour[]= array("TipoTourId" => '1',"TipoTour" => $dizionario['generale']['tour_privato']);
	
	$arr_sino = array();
	$arr_sino[]= array("Id" => '0',"Value" => $dizionario['generale']['no']);
	$arr_sino[]= array("Id" => '1',"Value" => $dizionario['generale']['si']);
	
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$LineaId);
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['linea'],"LineaNome","Linea[LineaNome]",$arrLinea['LineaNome'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
				print("<br style=\"clear:both;\"/>");
				$page->create_texarea($dizionario['generale']['descrizione'],"LineaDescrizione","Linea[LineaDescrizione]",$arrLinea['LineaDescrizione'],1,"brain_campoForm",array("cols"=>"'60'","rows"=>"'5'"));     
				print("<br style=\"clear:both;\"/>");
				
                $page->create_textbox($dizionario['linea']['area'],"Area","Linea[LineaArea]",$arrLinea['LineaArea'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));
                
				$page->create_select($dizionario['generale']['tipo_tour'],"Linea[TipoTour]","TipoTourId","brain_campoForm",$arr_tour,$arrLinea['TipoTour'],"TipoTourId","TipoTour",array("class"=>"'required'"),1);
                
				$page->create_textbox($dizionario['generale']['peso'],"Peso","Linea[LineaPeso]",$arrLinea['LineaPeso'],1,"brain_campoForm",array("class"=>"'required'"));
                
                 print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['da'],"LineaDa","Linea[LineaDa]",$arrLinea['LineaDa'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                $page->create_textbox($dizionario['generale']['a'],"LineaA","Linea[LineaA]",$arrLinea['LineaA'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
            
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['linea']['termine_max_rimborso'],"TempoMaxAnnullamento","Linea[TempoMaxAnnullamento]",$arrLinea['TempoMaxAnnullamento'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['linea']['termine_max_modifica'],"TempoMaxModifica","Linea[TempoMaxModifica]",$arrLinea['TempoMaxModifica'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['generale']['cell'],"Cellulare","Linea[Cellulare]",$arrLinea['Cellulare'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");

                $page->create_textbox(
                            $dizionario['linea']['link_descrizione'],
                            "LinkDescrizione",
                            "Linea[LinkDescrizione]",
                            $arrLinea['LinkDescrizione'],
                            0,
                            "brain_campoForm campiformBig",
                            null
                        );
                print("<br style=\"clear:both;\"/>");

                $page->create_textbox(
                            $dizionario['linea']['yolo_codice_fascia'],
                            "YoloCodiceFascia",
                            "Linea[YoloCodiceFascia]",
                            $arrLinea['YoloCodiceFascia'],
                            0,
                            "brain_campoForm campiformBig",
                            null
                        );
                print("<br style=\"clear:both;\"/>");

				$page->create_select($dizionario['fermata']['webselling'],"Linea[IsWebSelling]","IsWebSelling","brain_campoForm",$arr_sino,$arrLinea['IsWebSelling'],"Id","Value",
					array("class"=>"'required'"),1);
				
                $page->create_select($dizionario['generale']['stato'],"Linea[Stato]","StatoId","brain_campoForm",$arr_stato,$arrLinea['Stato'],"StatoId","Stato",
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
$HtmlCommon->html_titolo_pagina($dizionario['linea']['titolo_elenco']);
$HtmlCommon->html_titolo_box($dizionario['linea']['titolo_elenco']);
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
            <th width="10%"><?=$dizionario['corsa']['codice']?></th>
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
            <td colspan="8" class="dataTables_empty"><?=$dizionario['generale']['caricamento_in_corsa']?></td>
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
                                           edit($_REQUEST['LineaId']);
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