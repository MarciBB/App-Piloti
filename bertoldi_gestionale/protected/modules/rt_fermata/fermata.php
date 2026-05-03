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
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.Comune.php");
$ModuloId=28;

function add()
{
    global $HtmlCommon, $user, $dizionario;  

    $db = new Database();
    $db->connect();  
    $page = new Form();  

    include_once("fermata_validator.php");       
    $HtmlCommon->html_titolo_pagina($dizionario['fermata']['titolo_aggiungi'], 0, "rt_fermata", "fermata.php");
    $HtmlCommon->html_titolo_box($dizionario['fermata']['titolo_aggiungi']);  

    $arr_stato = [
        ["StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']],
        ["StatoId" => '1', "Stato" => $dizionario['generale']['attivo']]
    ];

    $arr_is = [
        ["ArrIsId" => '0', "ArrIs" => $dizionario['generale']['no']],
        ["ArrIsId" => '1', "ArrIs" => $dizionario['generale']['si']]
    ];
    ?>
    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_formModifica">
                    <div class="brain_data-content">
                        <?php
                        $page->create_textbox_hidden("action", "create");
                        $page->create_textbox_hidden_differentId("FermataComuneId", "Fermata[ComuneId]", '');

                        $selector = "<a id=\"FermataComuneIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'comune','comune.php?do=cerca&fieldtoupdate=FermataComuneId&labeltoupdate=FermataComune','bottom');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona comune\" border=\"0\" /></a>";
                        $selector_ann = "<a id=\"FermataComuneIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"$('#FermataComuneId').val('0');$('#FermataComune').val('');\" style=\"margin-left:2px\"><img src=\"/images/iconset/delete_gray.gif\" alt=\"cancella campo\"  border=\"0\" /></a>";
                        $selector .= $selector_ann;

                        $page->create_textbox_with_sel($dizionario['generale']['comune'], "FermataComune", "FermataComune", '', 1, "brain_campoForm", ["class" => "'required'", "readonly" => "'readonly'"], $selector);
                        ?>

                        <div id="elenco_comuni"></div>
                        
                        <?php
                        print("<br style=\"clear:both;\"/>");
                        $page->create_textbox($dizionario['generale']['fermata'], "FermataNome", "Fermata[FermataNome]", "", 1, "brain_campoForm campiformBig", ["class" => "'required'"]);  
                        print("<br style=\"clear:both;\"/>");
                         
                        $page->create_textbox($dizionario['fermata']['latitudine'], "FermataLatitudine", "Fermata[Latitudine]", "", 0, "brain_campoForm campiformBig", ["class" => "'number'"]);           
                        $page->create_textbox($dizionario['fermata']['longitudine'], "FermataLongitudine", "Fermata[Longitudine]", "", 0, "brain_campoForm campiformBig", ["class" => "'number'"]);           
                        $page->create_textbox($dizionario['fermata']['durata_da_inizio'], "KmInizioTratta", "Fermata[KmInizioTratta]", "", 1, "brain_campoForm campiformBig", ["class" => "'number required'"]);            
                        print("<br style=\"clear:both;\"/>");

                        $page->create_select($dizionario['fermata']['pickup'], "Fermata[IsPickup]", "PickupId", "brain_campoForm", $arr_is, 0, "ArrIsId", "ArrIs", ["class" => "'required'"], 1);
                        $page->create_select($dizionario['fermata']['dropoff'], "Fermata[IsDropOff]", "IsDropOffId", "brain_campoForm", $arr_is, 0, "ArrIsId", "ArrIs", ["class" => "'required'"], 1);
                        print("<br style=\"clear:both;\"/>");
                        $page->create_select($dizionario['fermata']['interscambio'], "Fermata[IsInterscambio]", "IsInterscambioId", "brain_campoForm", $arr_is, 0, "ArrIsId", "ArrIs", ["class" => "'required'"], 1);
                        $page->create_select($dizionario['fermata']['black_list'], "Fermata[IsBlackList]", "IsBlackListId", "brain_campoForm", $arr_is, 0, "ArrIsId", "ArrIs", ["class" => "'required'"], 1);
                        $page->create_select($dizionario['fermata']['da_confermare'], "Fermata[IsDaConfermare]", "IsDaConfermare", "brain_campoForm", $arr_is, 0, "ArrIsId", "ArrIs", ["class" => "'required'"], 1);
                        $page->create_select($dizionario['fermata']['webselling'], "Fermata[WebSelling]", "WebSelling", "brain_campoForm", $arr_is, 0, "ArrIsId", "ArrIs", ["class" => "'required'"], 1);
                        
                        print("<br style=\"clear:both;\"/>");
                        $page->create_textbox($dizionario['generale']['peso'], "Peso", "Fermata[FermataPeso]", "", 1, "brain_campoForm", ["class" => "'required'"]);
                        $page->create_select($dizionario['generale']['stato'], "Fermata[Stato]", "StatoId", "brain_campoForm", $arr_stato, 1, "StatoId", "Stato", ["class" => "'required'"], 1);
                        print("<br style=\"clear:both;\"/>");

                        if (isset($_REQUEST['TrattaId'])) {
                        ?>
                            <input type="hidden" name="Fermata[TrattaId]" value="<?= $_REQUEST['TrattaId'] ?>" />
                        <?php
                        }

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




function edit($FermataId)
{
    include_once("fermata_validator.php");

    global $HtmlCommon, $user, $dizionario;

    $db = new Database();
    $db->connect();
    $page = new Form();

    $Fermata = new Fermata();
    $Fermata->Id = $FermataId;
    $Fermata->conn = $db;
    $Fermata->inizializzaDatiGenerali();
    $arrFermata = $Fermata->DatiGenerali;

    $HtmlCommon->html_titolo_pagina($dizionario['fermata']['titolo_edit'], 0, "rt_fermata", "fermata.php");
    $HtmlCommon->html_titolo_box($dizionario['fermata']['titolo_edit'] . " - " . $arrFermata['FermataNome']);

    $arr_stato[] = array("StatoId" => '0', "Stato" => $dizionario['generale']['disattiva']);
    $arr_stato[] = array("StatoId" => '1', "Stato" => $dizionario['generale']['attiva']);
    $arr_is[] = array("ArrIsId" => '0', "ArrIs" => $dizionario['generale']['no']);
    $arr_is[] = array("ArrIsId" => '1', "ArrIs" => $dizionario['generale']['si']);

    ?>
    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_formModifica">
                    <div class="brain_data-content">
                        <?php
                        $page->create_textbox_hidden("action", "update");
                        $page->create_textbox_hidden("idpost", $FermataId);
                        $page->create_textbox_hidden_differentId("FermataComuneId", "Fermata[ComuneId]", $arrFermata['ComuneId']);
                        
                        $selector = "<a id=\"FermataComuneIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'comune','comune.php?do=cerca&fieldtoupdate=FermataComuneId&labeltoupdate=FermataComune','bottom');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona comune\" border=\"0\" /></a>";
                        $selector_ann = "<a id=\"FermataComuneIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"$('#FermataComuneId').val('0');$('#FermataComune').val('');\" style=\"margin-left:2px\"><img src=\"/images/iconset/delete_gray.gif\" alt=\"cancella campo\" border=\"0\" /></a>";
                        $selector .= $selector_ann;
                        $page->create_textbox_with_sel($dizionario['generale']['comune'], "FermataComune", "FermataComune", $arrFermata['Comune'], 1, "brain_campoForm", array("class" => "'required'", "readonly" => "'readonly'"), $selector);

                        ?>
                        <div id="elenco_comuni"></div>
                        <?php
                        print("<br style=\"clear:both;\"/>");
                        $page->create_textbox($dizionario['generale']['fermata'], "Fermata", "Fermata[FermataNome]", $arrFermata['FermataNome'], 1, "brain_campoForm campiformBig", array("class" => "'required'"));
                        print("<br style=\"clear:both;\"/>");
                        $page->create_textbox($dizionario['fermata']['latitudine'], "FermataLatitudine", "Fermata[Latitudine]", $arrFermata['Latitudine'], 0, "brain_campoForm campiformBig", array("class" => "'number'"));
                        $page->create_textbox($dizionario['fermata']['longitudine'], "FermataLongitudine", "Fermata[Longitudine]", $arrFermata['Longitudine'], 0, "brain_campoForm campiformBig", array("class" => "'number'"));
                        $page->create_textbox($dizionario['fermata']['durata_da_inizio'], "KmInizioTratta", "Fermata[KmInizioTratta]", $arrFermata['KmInizioTratta'], 1, "brain_campoForm campiformBig", array("class" => "'number required'"));
                        print("<br style=\"clear:both;\"/>");

                        $page->create_select($dizionario['fermata']['pickup'], "Fermata[IsPickup]", "PickupId", "brain_campoForm", $arr_is, $arrFermata['IsPickup'], "ArrIsId", "ArrIs", array("class" => "'required'"), 1);
                        $page->create_select($dizionario['fermata']['dropoff'], "Fermata[IsDropOff]", "IsDropOffId", "brain_campoForm", $arr_is, $arrFermata['IsDropOff'], "ArrIsId", "ArrIs", array("class" => "'required'"), 1);
                        print("<br style=\"clear:both;\"/>");
                        $page->create_select($dizionario['fermata']['interscambio'], "Fermata[IsInterscambio]", "IsInterscambioId", "brain_campoForm", $arr_is, $arrFermata['IsInterscambio'], "ArrIsId", "ArrIs", array("class" => "'required'"), 1);
                        $page->create_select($dizionario['fermata']['black_list'], "Fermata[IsBlackList]", "IsBlackListId", "brain_campoForm", $arr_is, $arrFermata['IsBlackList'], "ArrIsId", "ArrIs", array("class" => "'required'"), 1);
                        $page->create_select($dizionario['fermata']['da_confermare'], "Fermata[IsDaConfermare]", "IsDaConfermare", "brain_campoForm", $arr_is, $arrFermata['IsDaConfermare'], "ArrIsId", "ArrIs", array("class" => "'required'"), 1);
                        $page->create_select($dizionario['fermata']['webselling'], "Fermata[WebSelling]", "WebSelling", "brain_campoForm", $arr_is, $arrFermata['WebSelling'], "ArrIsId", "ArrIs", array("class" => "'required'"), 1);
                        print("<br style=\"clear:both;\"/>");

                        $page->create_textbox($dizionario['generale']['peso'], "Peso", "Fermata[FermataPeso]", $arrFermata['FermataPeso'], 1, "brain_campoForm", array("class" => "'required'"));
                        $page->create_select($dizionario['generale']['stato'], "Fermata[Stato]", "StatoId", "brain_campoForm", $arr_stato, $arrFermata['Stato'], "StatoId", "Stato", array("class" => "'required'"), 1);
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



function show_list()
{
global $user,$HtmlCommon, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['fermata']['titolo_elenco']);
$HtmlCommon->html_titolo_box($dizionario['fermata']['titolo_elenco']);
$db= new Database();
$db->connect();
include_once("sede_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="20%"><?$dizionario['gestore']['gestore']?></th>
            <th width="20%"><?$dizionario['generale']['comune']?></th>
            <th width="25%"><?$dizionario['generale']['indirizzo']?></th>
            <th width="5%"><?$dizionario['generale']['telefono']?></th>
            <th width="5%"><?$dizionario['generale']['fax']?></th>
            <th width="10%"><?$dizionario['generale']['email']?></th>
            <th width="10%"><?$dizionario['fermata']['codice']?></th>
            <th width="5%"><?$dizionario['generale']['edita']?></th>
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
            <td colspan="8" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?$dizionario['generale']['caricamento_in_corso']?></td>
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
                                           edit($_REQUEST['FermataId']);
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