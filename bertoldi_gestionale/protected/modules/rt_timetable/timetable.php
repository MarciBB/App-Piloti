<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<?php
set_time_limit( 0 );
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Ruolo.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Nazione.php");
include_once($classespath_ . "class.Regione.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once($classespath_ . "class.Percorso.php");
include_once($classespath_ . "class.Linea.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.TrattaTipo.php");
include_once($classespath_ . "class.Mezzo.php");
include_once($classespath_ . "class.TrattaDirezione.php");

global $ModuloId;
$ModuloId = 20;
global $user;
global $linea_wizard, $funzione_edit, $abilita_modifica;

$funzione_edit = false;
$linea_wizard = null;

if (isset($_SESSION['LINEA_WIZARD'])) {
    $linea_wizard = unserialize($_SESSION['LINEA_WIZARD']);
}

function show_list() {

    global $user, $HtmlCommon, $ModuloId, $dizionario;
    $HtmlCommon->html_titolo_pagina($dizionario['timetable']['titolo_gestione'], 0, "", "");
    $HtmlCommon->html_titolo_box($dizionario['timetable']['titolo_gestione']);
    $db = new Database();
    $db->connect();



    include_once("timetable_validator.php");
    include_once("timetable_datatable.php");

    global $user, $HtmlCommon, $db, $ModuloId;


    $aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
//if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tratta','tratta_wz.php?do=add','aggiungi percorso');
    ?>   
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
        <thead>

            <tr class="brain_tabellaTr">
                <th width="10%"><?=$dizionario['generale']['stato']?></th>
                <th width="35%"><?=$dizionario['generale']['linea']?></th>
                <th width="20%"><?=$dizionario['linea']['area']?></th>
                <th width="35%"><?=$dizionario['percorso']['percorso']?></th>
                <th width="5%"><?=$dizionario['generale']['edita']?></th>
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
                <td colspan="5" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
            </tr>
        </tbody>
        <tfoot> 
            <tr> 
                <td colspan="5"></td>
            </tr> 
        </tfoot> 
    </table>
    <?
    $db->close();
}

function edit($LineaId) {

    global $linea_wizard, $db, $user;
    $linea_wizard = new Linea($LineaId);


    $_SESSION['LINEA_WIZARD'] = serialize($linea_wizard);
    add(1);
}

function carica_menu_percorso($step_corrente, $mod) {
    global $abilita_modifica, $linea_wizard, $db, $dizionario;
	//$linea_wizard->conn=$db;
	//$menu=$linea_wizard->getMenuWizard();
    $menu = array(
        1 => $dizionario['timetable']['menu_corse'],
        2 => $dizionario['timetable']['menu_tariffe']
            //3=>"Tariffe servizi aggiuntivi"
    );
    ?>
    <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
        <ul>
    <?
    $contamenu = 1;
    while ($contamenu <= 2) {
        $class1 = "";
        $class2 = "";

        if ($contamenu == $step_corrente) {
            $class1 = "sel";
            $class2 = "brain_firstspan sel";
        }

        $StatoStep = "";

        if (($contamenu <= 2) or (($contamenu > 2) and ($mod))) { ?>

        	<li class="<?= $class1 ?>">
            	<span class="<?= $class2 ?>">
                <? if ($mod) { ?>
                	<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_timetable','timetable.php?do=add&step=<?= $contamenu ?>',this);" title="<?= $menu[$contamenu] ?>"><?= $menu[$contamenu] . " " . $StatoStep ?></a>        
                <? } else {
                        echo($menu[$contamenu]);
        		}?>     
                </span>
            </li>
         <? }
            $contamenu++;
		}
		?>

        </ul>
    </div>

    <?
}

function add_step_tratta() {
    $step_corrente = 1;

    global $linea_wizard, $user, $db, $HtmlCommon, $dizionario;

    $page = new Form();
    $dt = new DT();

    $azione = "add";
    $action = "create";

    $TrattaId = 0;
    $HtmlCommon->html_titolo_box($dizionario['timetable']['titolo_gestione']);
    if (is_object($linea_wizard) and ($linea_wizard->Id)) {

        $TrattaId = $linea_wizard->Id;

        /* $sql = "SELECT * from Mediazione where MediazioneId=$MediazioneId and Cancella=0";
          $row = $db->query_first($sql); */

        $linea_wizard->conn = $db;
        $linea_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr = $linea_wizard->DatiGenerali;
        $_SESSION['LINEA_WIZARD'] = serialize($linea_wizard);

        // print_r($row);
        if ($DatiGeneraliArr['LineaId']) {
            $azione = "edit";
            $action = "update";
        }
    }
    ?>

    <form id="application_form" name="application_form"  method="post" action="#">
    <?
    $page->create_textbox_hidden("action", $action);
    $page->create_textbox_hidden("step_corrente", $step_corrente);
    $page->create_textbox_hidden("step_successivo", $step_corrente + 1);
    ?>


        <div class="brain_formModifica">
    <? if ($action == "create") { ?>
                <h2><?=$dizionario['movimento']['info_generali']?></h2>
    <? } else { ?>
                <h2><span class="brain_colorh2"><?= $linea_wizard->DatiGenerali['TrattaNome'] ?></span></h2>
    <? } ?>
            <div class="brain_data-content">                  



    <?
    form_tipo1($azione, $TrattaId);
    ?>



                <br style="clear:both;"/>                                 
            </div></div>
    <? spara_pulsanti_wizard(0) ?>

    </form>


    <?
    $db->Close();
}

function form_tipo1($azione, $AnagraficaId) {
    global $HtmlCommon, $db, $user, $linea_wizard, $dizionario;
    /* $db= new Database();
      $db->connect(); */
    $page = new Form();
    $dt = new DT();

    $arr_stato[] = array("StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']);
    $arr_stato[] = array("StatoId" => '1', "Stato" => $dizionario['generale']['attivo']);

    $TrattaTipo = new TrattaTipo();
    $TrattaTipo->conn = $db;
    $arr_tratta_tipo = $TrattaTipo->getAll();

    $Mezzo = new Mezzo();
    $Mezzo->conn = $db;
    $arr_mezzo = $Mezzo->getAll();

    $TrattaDirezione = new TrattaDirezione();
    $TrattaDirezione->conn = $db;
    $arr_tratta_direzione = $TrattaDirezione->getAll();

    $LineaNome = "";
    $PercorsoPeso = "";
    $PercorsoStato = 0;

    if ($azione == "edit") {

        $DatiGeneraliArr = $linea_wizard->DatiGenerali;
        $LineaNome = $DatiGeneraliArr['LineaNome'];
        $LineaArea = $DatiGeneraliArr['LineaArea'];
        $LineaPeso = $DatiGeneraliArr['LineaPeso'];
        $LineaStato = $DatiGeneraliArr['Stato'];
    }

    $page->create_textbox_hidden("action", "create");
    $page->create_textbox($dizionario['generale']['linea'], "LineaNome", "Linea[LineaNome]", $LineaNome, 1, "brain_campoForm campiformBig", array("class" => "'required'"));
    $page->create_textbox($dizionario['linea']['area'], "Area", "Linea[LineaArea]", $LineaArea, 1, "brain_campoForm campiformBig", array("class" => "'required'"));
    $page->create_textbox($dizionario['generale']['peso'], "Peso", "Linea[LineaPeso]", $LineaPeso, 1, "brain_campoForm", array("class" => "'required'"));

    print("<br style=\"clear:both;\"/>");
    $page->create_select($dizionario['generale']['stato'], "Linea[Stato]", "StatoId", "brain_campoForm", $arr_stato, $LineaStato, "StatoId", "Stato", array("class" => "'required'"), 1);
    ?>


    <div id="elenco_comuni"></div>
    <?
    print("<br style=\"clear:both;\"/>");
}

function add_step_fermate_orari() {

    $step_corrente = 1;

    global $linea_wizard, $user, $db, $dizionario;

    $page = new Form();
    $dt = new DT();

    $LineaId = $linea_wizard->Id;


    include_once("timetable_validator.php");
    ?>
    <script type="text/javascript"> 
        $(document).ready(function() {
            
            $(".Orario").mask("99:99");   
            
       

        });
            
    </script>
    <form id="application_form" name="application_form"  method="post" action="#">
    <?
    $page->create_textbox_hidden("step_corrente", $step_corrente);
    $page->create_textbox_hidden("step_successivo", $step_corrente + 1);
    $page->create_textbox_hidden("action", "create");
    ?>


        <div class="brain_formModifica formGestoreEdita">
            <h2><span class="brain_colorh2"><?= $linea_wizard->DatiGenerali['LineaNome'] ?> - </span><?=$dizionario['timetable']['orari_partenza']?></h2>
            <table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                <tbody>
                    <tr class="rowIntestazione">
                        <td><?=$dizionario['generale']['tratta']?></td>          
                        <td><?=$dizionario['generale']['fermata']?></td>      

    <?
    $sql = "Select * from RT_Corsa where Cancella=0 and LineaId=$LineaId 
    and Stato = 1 AND CURDATE() < AttivaAl AND RitornoAperto = 0 
    AND OdcIdRef=$user->OdcId order by CorsaPeso asc";


    $ArrObjectCorse = $db->fetch_array($sql);
    $i = 0;
    while ($i < sizeof($ArrObjectCorse)) {
        $CorsaId = $ArrObjectCorse[$i]['CorsaId'];
        $CorsaNome = $ArrObjectCorse[$i]['CorsaNome'];
        //$CorsaArea = $ArrObjectCorse[$i]['CorsaArea'];
        $CorsaPeso = $ArrObjectCorse[$i]['CorsaPeso'];
        $AttivaDal = $ArrObjectCorse[$i]['AttivaDal'];
        $AttivaAl = $ArrObjectCorse[$i]['AttivaAl'];

        $CorsaStato = $ArrObjectCorse[$i]['Stato'];
        $OrarioPartenza = $ArrObjectCorse[$i]['OrarioPartenza'];
        $OrarioArrivo = $ArrObjectCorse[$i]['OrarioArrivo'];
        $dal = $dt->format($AttivaDal, "Y-m-d", "d/m/Y");
        $al = $dt->format($AttivaAl, "Y-m-d", "d/m/Y");
        ?>
                            <td><?= $CorsaNome ?><br /><?=$dizionario['generale']['dal']?> <?= $dal ?> <?=$dizionario['generale']['al']?> <?= $al ?> <br /><?=$dizionario['generale']['dalle']?> <?= substr($OrarioPartenza, 0, 5) . " ".$dizionario['generale']['alle']." " . substr($OrarioArrivo, 0, 5) ?>
                                <br /><br />
                                <div style="float: left; margin-right: 8px">
                                    <label style="font-weight: bold" for="segno_offset_valore_<?= $CorsaId ?>" ><?=$dizionario['timetable']['segno']?></label><br/><br/>
                                    <select id="segno_offset_valore_<?= $CorsaId ?>">
                                        <option value="+">+</option>
                                        <option value="-">-</option>
                                    </select>
                                </div>
                                <div style="float: left; margin-right: 8px">
                                    <label style="font-weight: bold" for="ora_offset_valore_<?= $CorsaId ?>" ><?=$dizionario['timetable']['ore']?></label><br/><br/>
                                    <select id="ora_offset_valore_<?= $CorsaId ?>">
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>
                                        <option value="21">21</option>
                                        <option value="22">22</option>
                                        <option value="23">23</option>
                                    </select>
                                </div>
                                <div style="float: left; margin-right: 8px">
                                    <label style="font-weight: bold" for="minuti_offset_valore_<?= $CorsaId ?>" ><?=$dizionario['timetable']['minuti']?></label><br/><br/>
                                    <select id="minuti_offset_valore_<?= $CorsaId ?>">
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>
                                        <option value="21">21</option>
                                        <option value="22">22</option>
                                        <option value="23">23</option>
                                        <option value="24">24</option>
                                        <option value="25">25</option>
                                        <option value="26">26</option>
                                        <option value="27">27</option>
                                        <option value="28">28</option>
                                        <option value="29">29</option>
                                        <option value="30">30</option>
                                        <option value="31">31</option>
                                        <option value="32">32</option>
                                        <option value="33">33</option>
                                        <option value="34">34</option>
                                        <option value="35">35</option>
                                        <option value="36">36</option>
                                        <option value="37">37</option>
                                        <option value="38">38</option>
                                        <option value="39">39</option>
                                        <option value="40">40</option>
                                        <option value="41">41</option>
                                        <option value="42">42</option>
                                        <option value="43">43</option>
                                        <option value="44">44</option>
                                        <option value="45">45</option>
                                        <option value="46">46</option>
                                        <option value="47">47</option>
                                        <option value="48">48</option>
                                        <option value="49">49</option>
                                        <option value="50">50</option>
                                        <option value="51">51</option>
                                        <option value="52">52</option>
                                        <option value="53">53</option>
                                        <option value="54">54</option>
                                        <option value="55">55</option>
                                        <option value="56">56</option>
                                        <option value="57">57</option>
                                        <option value="58">58</option>
                                        <option value="59">59</option>
                                    </select>
                                </div>
                                <div style="float: right;"><br/><br/>
                                    <input type="button" value="Aggiorna" onclick="aggiungiValoreOrario($('#segno_offset_valore_<?= $CorsaId ?>').val(),$('#ora_offset_valore_<?= $CorsaId ?>').val(),$('#minuti_offset_valore_<?= $CorsaId ?>').val(),<?= $CorsaId ?>)"/>
                                </div>
                                <br />
                                <!--input type="text" class="offset_<?= $CorsaId ?>" onblur="aggiornaValori(<?= $CorsaId ?>,this.value)" maxlength="4" size="4" value="0" name="Varia['<?= $CorsaId ?>']"-->

                            </td>

        <?
        $i++;
    }
    ?>
                    </tr>
    <?
    $sql = "Select * from RT_ElencoFermata where Cancella=0 and LineaId=$LineaId and CorsaId=$CorsaId and  OdcIdRef=$user->OdcId order by NodoPeso,TrattaPeso,TrattaId,FermataPeso asc";


    $ArrObject = $db->fetch_array($sql);
    $i = 0;
    $tratta_old = 0;
    while ($i < sizeof($ArrObject)) {
        $FermataId = $ArrObject[$i]['FermataId'];
        $TrattaId = $ArrObject[$i]['TrattaId'];
        $TrattaNome = $ArrObject[$i]['TrattaNome'];
        $FermataNome = $ArrObject[$i]['FermataNome'];
        $FermataComune = $ArrObject[$i]['Comune'];

        $first=0;
        if ($tratta_old != $TrattaId) {
            $first=1;
            $tratta_old = $TrattaId;
            $n = 0;
            ?>
                            <tr>
                                <td><span><strong><?= $TrattaNome ?></strong></span></td>
                                <td></td>
            <?
            while ($n < sizeof($ArrObjectCorse)) {
                ?>
                                    <td></td>
                                    <?
                                    $n++;
                                }
                                ?>

                            </tr>  

                                <?
                            }
                            ?>


                        <tr>
                            <td></td>
                        <!-- <td> -- <? echo($tratta_old . " " . $TrattaId); ?><span><?= $TrattaNome ?></span></td>-->
                            <td><span><?= $FermataNome ?> - (<?= $FermataComune ?>)</span></td>
                            <?
                            $n = 0;
                            while ($n < sizeof($ArrObjectCorse)) {
                                $CorsaID = $ArrObjectCorse[$n]['CorsaId'];
                                $sql = "Select * from RT_Orario where CorsaId=$CorsaID and FermataId=$FermataId and OdcIdRef=$user->OdcId";
                                $row1 = $db->query_first($sql);
                                $orario = "";
                                $dd_aggiuntivi = 0;
                                if (!empty($row1['OrarioId'])) {
                                    $orario = $row1['Orario'];
                                    $dd_aggiuntivi = $row1['GiorniAggiuntivi'];
                                }
                                ?>
                                <td>
                                    <input class="Orario ora_<?= $CorsaID ?> ora_<?= $CorsaID ?>_<?= $i ?>" corsa="<?= $CorsaID ?>" type="text" name="Timetable['<?= $CorsaID . "_" . $FermataId ?>']" value="<?= $orario ?>" SIZE="7" MAXLENGTH="7"> 
                                    <input class="number giorno_<?= $CorsaID ?> giorno_<?= $CorsaID ?>_<?= $i ?> first_<?=$first?>"  corsa="<?= $CorsaID ?>" type="text" name="Timetable1[<?= $CorsaID . "_" . $FermataId ?>]" value="<?= $dd_aggiuntivi ?>" SIZE="2" MAXLENGTH="2"> (+gg)

                                </td>

                            <?
                            $n++;
                        }
                        ?>



                        </tr>
                        <?
                        $i++;
                    }
                    ?>

                </tbody>
            </table>
        </div>
        <div class="divSubmit">

                    <? $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>



        </div>

    </form>


                        <?
                        $db->Close();
                    }

function add_step_tariffe($corsaId = null) {
    ini_set('display_errors', 0);
    ini_set('error_reporting', E_ALL);
    $contamenu = 0;
    $step_corrente = 2;
    global $linea_wizard, $user, $db, $dizionario;
    $page = new Form();
    $dt = new DT();
    $storico = new StoricoOperazioni();
    $storico->conn=$db;
    
    //recupero lineaid e corsaid se si seleziona una corsa
    $LineaId = $linea_wizard->Id;
    $CorsaId = 0;
    if(isset($corsaId)) {
        $CorsaId = $corsaId;
    }
    $connessioni=1;
    $maxConnessioni=6;
    
    if (isset($_REQUEST['LineaId'])) {
        $LineaId = $_REQUEST['LineaId'];
    }
    
    if(isset($_REQUEST['TrattaId'])) {
        $TrattaId = $_REQUEST['TrattaId'];
    } else {
        $TrattaId = 0;
    }
    if(isset($_REQUEST['PickupId'])) {
        $PickupId = $_REQUEST['PickupId'];
    } else {
        $PickupId = 0;
    }
    if(isset($_REQUEST['DropoffId'])) {
        $DropoffId = $_REQUEST['DropoffId'];
    } else {
        $DropoffId = 0;
    }
    
    $oggi = date('Y-m-d');
    $sql = "Select * from RT_Corsa
			where  AttivaAl>='$oggi' and Stato = 1
			and Cancella = 0 and LineaId = $LineaId
			and OdcIdRef = $user->OdcId
			order by CorsaPeso asc, CorsaNome asc";
    $ArrObjectCorse = $db->fetch_array($sql);
    $selectCorse = array();
    $first = true;
    foreach($ArrObjectCorse as $corsa){
//         if($first && !isset($corsaId)){
//             $CorsaId = $corsa['CorsaId'];
//             $first = false;
//         }
        $temp['CorsaId'] = $corsa['CorsaId'];
        $temp['Corsa'] = $corsa['CorsaNome'];
        $selectCorse[] = $temp;
    }

    if(isset($_GET['CorsaId'])){
        $CorsaId = $_GET['CorsaId'];
        //$LineaId = $_GET['LineaId'];
    }
    //fine recupero lineaId e corsa
                        
    //recupero tratte
    if(isset($LineaId)){
        $arrayTratta = array();
        $sql = "select TrattaNome as Tratta, TrattaId from RT_Tratta
            where Stato = 1 AND Cancella = 0 AND LineaId = $LineaId";
        $arrayTratta = $db->fetch_array($sql);
        array_unshift($arrayTratta,array("TrattaId" => "0", "Tratta" => "Tutte le tratte"));
                        
        $arrayPickup = array();
        $sql = "select c.Comune as Pickup, c.ComuneId as PickupId from Comune c
    		left join RT_Fermata f on f.ComuneId = c.ComuneId
    		left join RT_Tratta t on t.TrattaId = f.TrattaId
    		where t.Stato = 1 and t.Cancella = 0 and f.Stato = 1 and f.Cancella = 0
    		and f.IsPickup = 1 and t.LineaId = $LineaId";
        $arrayPickup = $db->fetch_array($sql);
        array_unshift($arrayPickup,array("PickupId" => "0", "Pickup" => "Tutte le partenze"));
        
        $arrayDropoff = array();
        $sql = "select c.Comune as Dropoff, c.ComuneId as DropoffId from Comune c
	    	left join RT_Fermata f on f.ComuneId = c.ComuneId
	    	left join RT_Tratta t on t.TrattaId = f.TrattaId
	    	where t.Stato = 1 and t.Cancella = 0 and f.Stato = 1 and f.Cancella = 0
	    	and f.IsDropoff = 1 and t.LineaId = $LineaId";
        $arrayDropoff = $db->fetch_array($sql);
        array_unshift($arrayDropoff,array("DropoffId" => "0", "Dropoff" => "Tutte le destinazioni"));
        
		//recupero il tipo di linea se tour di gruppo o privato
		$sql = "Select * from RT_Linea WHERE LineaId = $LineaId";
		$linea = $db->query_first($sql);
		$TipoTour = $linea['TipoTour'];
	
		//recupero le tipologia biglietto a prezzo fisso di del tipo del tour della linea
		$sql = "Select * from RT_TipologiaBiglietto WHERE 
			TipoTour = $TipoTour 
			AND TipoPrezzo = 0 
			AND Stato = 1 
			AND Cancella = 0 
			AND OccupaPosto = 1
			ORDER BY TipologiaBigliettoPeso ASC";
		$arrayBiglietti = $db->fetch_array($sql);
		
    }
    ?>
 
    <form id="application_form" name="application_form"  method="post" action="#">
		
    <?php
    //step del menu laterale
    $page->create_textbox_hidden("step_corrente", $step_corrente);
    $page->create_textbox_hidden("step_successivo", 2);
    $page->create_textbox_hidden("action", "create_tariffe");
	
// 	$sql = "Select * from RT_Listino where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId and (ListinoId=5 or ListinoId=1) order by ListinoPeso asc";
// 	$ArrObjectListino = $db->fetch_array($sql);
    ?>
		
    <div class="brain_formModifica formGestoreEdita">
    	<h2><span class="brain_colorh2"><?=$dizionario['generale']['linea']?> <?= $linea_wizard->DatiGenerali['LineaNome'] ?> - </span><?=$dizionario['timetable']['tariffe']?></h2>
        <p>
        	<?=$dizionario['timetable']['avviso_tariffe']?>
		</p>
	    <?php
	    $page->create_textbox_hidden("LineaId", $LineaId);
	    $page->create_select($dizionario['generale']['corsa'],"CorsaId","CorsaId","brain_campiform campiformBig",$selectCorse,$CorsaId,"CorsaId","Corsa",null,1);
	    
	    $page->create_select($dizionario['generale']['tratta'],"TrattaId","TrattaId","brain_campiform",$arrayTratta,$TrattaId,"TrattaId","Tratta",null,1);
	    $page->create_select($dizionario['generale']['partenza'],"PickupId","PickupId","brain_campiform",$arrayPickup,$PickupId,"PickupId","Pickup",null,1);
	    $page->create_select($dizionario['biglietto']['destinazione'],"DropoffId","DropoffId","brain_campiform",$arrayDropoff,$DropoffId,"DropoffId","Dropoff",null,1);
	    print(" <br style=\"clear:both;\"/>");
	    print(" <br style=\"clear:both;\"/>");
	    ?>
	    <a href="#" onclick="ExternalLoad('rt_timetable','timetable.php?do=aumentoPrezzi&amp;LineaId=<?=$LineaId?>&amp;CorsaId=<?=$CorsaId?>',this);" title="edita"><?php echo $dizionario['timetable']['aumento'];?></a>
	    
	    <script>
			$(document).ready(function(){
				$("#CorsaId, #TrattaId, #PickupId, #DropoffId").change(function(){
					var value = $("#CorsaId").val();
					var tratta = $("#TrattaId").val();
					var pickup = $("#PickupId").val();
					var dropoff = $("#DropoffId").val();
					loadMainContent1('rt_timetable','timetable.php?do=add&step=2&CorsaId='+value+'&LineaId=<?php echo $LineaId;?>&TrattaId='+tratta+'&PickupId='+pickup+'&DropoffId='+dropoff,this);
				});
			});
	    </script>
		<?php 
            
		if($CorsaId == 0){
			echo "Nessuna corsa selezionata";
			die();
		}
	    
		foreach ($arrayBiglietti as $biglietto) { 
				$bigliettoId = $biglietto['TipologiaBigliettoId'];
				?>
				<h4 style="margin-top: 10px;"><b><?=$dizionario['biglietto']['dett_tipologia']?></b> <?=$biglietto['TipologiaBiglietto']?></h4>
				<?php
		
		
			$arrayHtml = array();
			$connessioni=1;
			while ($connessioni <=1) {
				 
				$grafo = new LineaGraph($LineaId, $CorsaId, null, $db, false, null, 0, true, true);
				$connessioni++;
				
				
				foreach ($grafo->graph->nodes as $pickupId => $comune){
					$sql = "SELECT * FROM RT_Fermata f
							LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId 
							WHERE ComuneId = '$pickupId' AND t.LineaId = $LineaId and IsPickup = 1";
				
					$ArrObjectCorse = $db->fetch_array($sql);
					if( (sizeof($ArrObjectCorse) > 0) and (count($comune->descents)>0)){
						$sql = "SELECT Comune FROM Comune 
							WHERE ComuneId = $pickupId";
							
						$sql="SELECT
							Comune.Comune,
							Provincia.RegioneId
							FROM
							Comune
							INNER JOIN Provincia ON Comune.provincia = Provincia.ProvinciaId
							WHERE
							ComuneId = $pickupId";
							
						$infoPickup = $db->query_first($sql);
							
						$regioneId=$infoPickup['RegioneId'];
					
						$arrayHtml[$pickupId]['comune'] = $infoPickup['Comune'];
						foreach ($comune->descents as $dropoffId => $discesa){    
							$sql = "SELECT
									c.Comune,
									Provincia.RegioneId
								FROM
									RT_Fermata AS f
								LEFT JOIN RT_Tratta AS t ON t.TrattaId = f.TrattaId
								LEFT JOIN Comune AS c ON c.ComuneId = f.ComuneId
								INNER JOIN Provincia ON c.provincia = Provincia.ProvinciaId
								WHERE
									f.ComuneId = $dropoffId
								AND t.LineaId = $LineaId AND f.IsDropoff = 1";
							
							$ArrObjectCorse = $db->query_first($sql);
						
							//controllo tratta
							$viewTratta = true;
							if($TrattaId > 0) {
								$sql = "SELECT * FROM RT_Fermata WHERE TrattaId = $TrattaId and ComuneId = $pickupId";
								$tempTratta = $db->query_first($sql);
								if(isset($tempTratta['ComuneId'])){
									$viewTratta = true;
								} else {
									$viewTratta = false;
								}
							}
							//controllo pickup
							$viewPickup = true;
							if($PickupId > 0){
								if($PickupId == $pickupId){
									$viewPickup = true;
								} else {
									$viewPickup = false;
								}
							}
						
							if(isset($ArrObjectCorse['Comune']) && ($viewTratta && $viewPickup)){
										
								$sql = "SELECT Tariffa FROM RT_CorsaTariffa t
								where t.FermataDropoff = $dropoffId AND t.FermataPickup = $pickupId AND CorsaId = $CorsaId AND TipologiaBigliettoId = $bigliettoId";
								
								$info = $db->query_first($sql);
								if(isset($info['Tariffa'])){
									$prezzo = $info['Tariffa'];
								} else {
									$prezzo = 0;
								}
								
								//controllo trattaDropoff
								$viewTrattaD = true;
								if($TrattaId > 0) {
									$sql = "SELECT * FROM RT_Fermata WHERE TrattaId = $TrattaId and ComuneId = $dropoffId";
									$tempTratta = $db->query_first($sql);
									if(isset($tempTratta['ComuneId'])){
										$viewTrattaD = true;
									} else {
										$viewTrattaD = false;
									}
								}
								//controllo pickup
								$viewDropoff = true;
								if($DropoffId > 0){
									if($DropoffId == $dropoffId){
										$viewDropoff = true;
									} else {
										$viewDropoff = false;
									}
								}
								if($viewDropoff && $viewTrattaD) {
									$arrayHtml[$pickupId]['destinazioni'][$LineaId . "_" . $bigliettoId . "_" . $pickupId . "_" . $dropoffId . "_". $CorsaId]['prezzo'] = $prezzo;
									$arrayHtml[$pickupId]['destinazioni'][$LineaId . "_" . $bigliettoId . "_" . $pickupId . "_" . $dropoffId . "_". $CorsaId]['comune'] = $ArrObjectCorse['Comune'];
								}
							}
						}
					}
				}

			}
           
			foreach($arrayHtml as $pickupId => $array){
				if(isset($array['destinazioni']) && count($array['destinazioni']) > 0) {
					echo "<h3>".$array['comune']."</h3>";
					
					$riga = 0;
					foreach($array['destinazioni'] as $key => $prezzo) {
						if($riga == 0){
							echo "<tr style='width: 7% !important; vertical-align:center;'>";
						}
						?>
						<div style="width:200px; display: inline-flex;">
							<table>
								<tr><td>
									<label for="Prezzi['<?= $key ?>']"><?php echo $prezzo['comune'];?></label>
								</td></tr>
								<tr><td>
									<input class="numberDE" type="text" name="Prezzi['<?= $key ?>']" value="<?= $prezzo['prezzo'] ?>" SIZE="7">
								</td></tr>
							</table>
						</div>
						<?php
						$riga++;
						if($riga >=10){
							$riga = 0;
							echo "</tr>";
						}
					}
					
				}
			} ?>
			<hr style="margin: 10px 0px;">
		<?php } ?>

        </div>
        
        <div class="divSubmit">
        	<fieldset>
				<legend><?=$dizionario['timetable']['salva']?></legend>
        		<input style="float:none !important;" type="radio" name="tipoCreazione" value="1" checked="checked"/><?=$dizionario['timetable']['corsa_corrente']?> 
        		<input style="float:none !important;" type="radio" name="tipoCreazione" value="2"/><?=$dizionario['timetable']['corsa_tutte']?>
        		<input style="float:none !important;" type="radio" name="tipoCreazione" value="3"/><?=$dizionario['timetable']['corsa_seleziona']?>
        		<?php 
        			$page->create_select(null,"CorsaIdSelect[]","CorsaIdSelect","brain_campiform campiformBig",$selectCorse,$CorsaId,"CorsaId","Corsa",array("multiple" => "multiple"),1);
	  			?>
    		</fieldset>
        
    		<?php $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>
        </div>
    	</form>
		<?php
    	$db->Close();
	}
	
	function aumentoPrezzi() {
	    include_once("timetable_validator.php");
	    global $HtmlCommon,$user,$db, $dizionario;
	    
	    $db= new Database();
	    $db->connect();
	    $page=new Form();
	    
	    $CorsaId=$_REQUEST['CorsaId'];
	    $LideaId=$_REQUEST['LineaId'];
	    
	    $page=new Form();
	    
	    $HtmlCommon->html_titolo_pagina($dizionario['timetable']['aumento'],0,"","");
	    $HtmlCommon->html_titolo_box ($dizionario['timetable']['aumento']);
	    
	    ?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">  
		<form id="application_form1" name="application_form1"  method="post" action="#">   
			<div class="brain_formModifica" style="width: auto;min-height:auto;">
					<div class="brain_data-content">
		<?php
	        $page->create_textbox_hidden("action","aumentoPrezzi");
	        $page->create_textbox_hidden("corsaId",$CorsaId);
	        $page->create_textbox_hidden("lideaId",$LideaId);
	    ?>
			<div class="formGestoreEdita">                   
	            <?php echo $dizionario['timetable']['aumento_1'];?>
	        	<br />
	        	<?php 
	        		$page->create_textbox($dizionario['timetable']['aumento_diminuzione'].":", "aumento", "aumento", 0, 1, "brain_campoForm campoAuto", array("class"=>"'required numberDE'"));
				?>
				
			</div>
			<div class="divSubmit">
	    		<?php $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>
	        </div>
	        </div></div>
		</form>                    
	</div></div>
<?php
}

function add_step_tariffe_servizi_aggiuntivi() {

	$step_corrente = 3;

	global $linea_wizard, $user, $db, $dizionario;

	$page = new Form();
	$dt = new DT();

	$LineaId = $linea_wizard->Id;
    ?>
    
    <script type="text/javascript"> 
        $(document).ready(function() {
            $(".Orario").mask("99:99");   
        });
            
    </script>
    <form id="application_form" name="application_form"  method="post" action="#">
    <?
    $page->create_textbox_hidden("step_corrente", $step_corrente);
    $page->create_textbox_hidden("step_successivo", 0);
    $page->create_textbox_hidden("action", "create_tariffe_servizio");


    $sql = "Select * from RT_TipologiaServizio where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId order by TipologiaServizioPeso asc";

    $ArrObjectTipoBiglietto = $db->fetch_array($sql);
    ?>






        <div class="brain_formModifica formGestoreEdita">
            <h2><span class="brain_colorh2"><?= $linea_wizard->DatiGenerali['LineaNome'] ?> - </span>Tariffe</h2>
                        <?
                        $sql = "Select * from RT_Corsa where Stato=1 and Cancella=0 and LineaId=$LineaId and OdcIdRef=$user->OdcId order by CorsaPeso asc";


                        $ArrObjectCorse = $db->fetch_array($sql);
                        $f = 0;
                        while ($f < sizeof($ArrObjectCorse)) {
                            $CorsaId = $ArrObjectCorse[$f]['CorsaId'];
                            $CorsaNome = $ArrObjectCorse[$f]['CorsaNome'];
                            $CorsaArea = $ArrObjectCorse[$f]['CorsaArea'];
                            $CorsaPeso = $ArrObjectCorse[$f]['CorsaPeso'];
                            $CorsaStato = $ArrObjectCorse[$f]['Stato'];
                            $OrarioPartenza = $ArrObjectCorse[$f]['OrarioPartenza'];
                            $OrarioArrivo = $ArrObjectCorse[$f]['OrarioArrivo'];
                            $AttivaDal = $ArrObjectCorse[$f]['AttivaDal'];
                            $AttivaAl = $ArrObjectCorse[$f]['AttivaAl'];

                            $dal = $dt->format($AttivaDal, "Y-m-d", "d/m/Y");
                            $al = $dt->format($AttivaAl, "Y-m-d", "d/m/Y");
                            ?>
                <br /><br /><h2><span class="brain_colorh2"><?= $CorsaNome ?> - </span><?=$dizionario['generale']['dal']?> <?= $dal . " ".$dizionario['generale']['al']." " . $al ?> (<?= $OrarioPartenza . " - " . $OrarioArrivo ?>)</h2>



                            <?
                            /* carico tutte le tratte */



                            $sql = "Select * from RT_Tratta where Stato=1 and Cancella=0 and LineaId=$LineaId and OdcIdRef=$user->OdcId order by TrattaPeso asc";


                            $ArrObjectTratte = $db->fetch_array($sql);
                            $tr = 0;
                            while ($tr < sizeof($ArrObjectTratte)) {
                                $TrattaId = $ArrObjectCorse[$tr]['TrattaId'];
                                $TrattaNome = $ArrObjectCorse[$tr]['TrattaNome'];
                                ?>            






                    <table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                        <tbody>
                        <!--<tr class="rowIntestazione">
                        <td>Pickup / DropOff</td>  -->


                    <?
                    $sql = "Select * from RT_ElencoOrario where Stato=1 and Cancella=0 and CorsaId=$CorsaId and TrattaId=$TrattaId and OdcIdRef=$user->OdcId and (IsDropOff=1 or IsInterscambio=1) order by TrattaPeso,FermataPeso asc";


                    $ArrObjectDp = $db->fetch_array($sql);
                    /* $i=0;
                      while ($i< sizeof($ArrObjectDp))
                      {
                      $FermataId=$ArrObjectDp[$i]['FermataId'];
                      $TrattaId=$ArrObjectDp[$i]['TrattaId'];
                      $TrattaNome=$ArrObjectDp[$i]['TrattaNome'];
                      $FermataNome=$ArrObjectDp[$i]['FermataNome'];
                      $FermataComune=$ArrObjectDp[$i]['Comune'];
                     */
                    ?>
            <!-- <td><?= $FermataComune ?><br /><?= $FermataNome ?></td>-->

            <?
            /* $i++;
              } */
            ?>
                            </tr>

            <!-- <tr>

            <td></td> 
            <?
            /*    $ArrObjectDp = $db->fetch_array($sql);
              $i=0;
              while ($i< sizeof($ArrObjectDp))
              {
              ?>

              <td>
              <table style="width:100%;border:none;">
              <tr>
              <?

              $tb=0;
              $wt1=100/sizeof($ArrObjectTipoBiglietto);
              $wt=$wt1."%";
              while ($tb< sizeof($ArrObjectTipoBiglietto))
              {
              $TipoBigliettoId=$ArrObjectTipoBiglietto[$tb]['TipologiaBigliettoId'];
              $TipoBiglietto=$ArrObjectTipoBiglietto[$tb]['TipologiaBiglietto'];
              ?>
              <td style="width:<?=$wt?>;border:none;border-right:1px solid #D6D6D6;"><strong><?=$TipoBiglietto?></strong></td>

              <?
              $tb++;
              }
              ?>
              </tr></table>

              </td>
              <?
              $i++;
              } */
            ?>
            </tr>-->

            <?
            $sql = "Select * from RT_ElencoFermata where Stato=1 and Cancella=0 and IsPickup=1 and LineaId=$LineaId and OdcIdRef=$user->OdcId order by TrattaPeso,FermataPeso asc";


            $ArrObject = $db->fetch_array($sql);
            $i = 0;
            $tratta_old = 0;
            while ($i < sizeof($ArrObject)) {
                $FermataId = $ArrObject[$i]['FermataId'];
                $TrattaId = $ArrObject[$i]['TrattaId'];
                $TrattaNome = $ArrObject[$i]['TrattaNome'];
                $TrattaPeso = $ArrObject[$i]['TrattaPeso'];
                $FermataNome = $ArrObject[$i]['FermataNome'];
                $FermataComune = $ArrObject[$i]['Comune'];
                $FermataPeso = $ArrObject[$i]['FermataPeso'];
                ?>


                                <tr class="rowIntestazione">
                                    <td><?=$dizionario['timetable']['p_e_d']?></td>  


                        <?
                        //  $sql="Select * from RT_ElencoOrario where CorsaId=$CorsaId and OdcIdRef=$user->OdcId and IsDropOff=1 order by TrattaPeso,FermataPeso asc";
                        // $ArrObjectDp = $db->fetch_array($sql);
                        $dd = 0;
                        while ($dd < sizeof($ArrObjectDp)) {
                            $FermataIdd = $ArrObjectDp[$dd]['FermataId'];
                            $TrattaIdd = $ArrObjectDp[$dd]['TrattaId'];
                            $TrattaNomed = $ArrObjectDp[$dd]['TrattaNome'];
                            $FermataNomed = $ArrObjectDp[$dd]['FermataNome'];
                            $FermataComuned = $ArrObjectDp[$dd]['Comune'];
                            ?>
                                        <td><strong><?= $FermataComuned ?><br /><?= $FermataNomed ?></strong></td>

                            <?
                            $dd++;
                        }
                        ?>
                                </tr>        

                                <tr>



                                    <td><strong><?= $FermataComune ?><br /><?= $FermataNome ?></strong></td>
                        <?
                        $n = 0;
                        $FermataPesoDropOff = 0;
                        while ($n < sizeof($ArrObjectDp)) {
                            $FermataIdDropOff = $ArrObjectDp[$n]['FermataId'];
                            $TrattaPesoDropOff = $ArrObjectDp[$n]['TrattaPeso'];
                            $TrattaIdDropOff = $ArrObjectDp[$n]['TrattaId'];
                            $FermataPesoDropOff = $ArrObjectDp[$n]['FermataPeso'];


                            /* $sql="Select * from RT_Orario where CorsaId=$CorsaID and FermataId=$FermataId and OdcIdRef=$user->OdcId";
                              $row1 = $db->query_first($sql);
                              $orario="";
                              if (!empty($row1['OrarioId']))
                              $orario=$row1['Orario']
                             */
                            /*  echo($FermataPeso." ".$FermataPesoDropOff."<br />");
                              if ($FermataPeso<$FermataPesoDropOff)
                              { */
                            ?>


                                    <?
                                    // echo($TrattaId." ".$TrattaIdDropOff." ".$FermataPeso." ".$FermataPesoDropOff);
                                    if ((($TrattaId == $TrattaIdDropOff) and ($FermataPeso >= $FermataPesoDropOff)) or ($TrattaPeso > $TrattaPesoDropOff)) {
                                        ?>
                                            <td>&nbsp;</td>
                                        <?
                                    } else {
                                        ?>



                                        <?
                                        /*  $k=0;
                                          echo( sizeof($ArrObjectDp));
                                          while ($k< sizeof($ArrObjectDp))
                                          { */
                                        ?>

                                            <td>
                                                <table style="width:100%;border:none;">
                                                    <tr> 
                        <?
                        $tb = 0;
                        while ($tb < sizeof($ArrObjectTipoBiglietto)) {
                            $TipoBigliettoId = $ArrObjectTipoBiglietto[$tb]['TipologiaServizioId'];
                            $TipoBiglietto = $ArrObjectTipoBiglietto[$tb]['TipologiaServizio'];


                            $sql = "Select * from RT_CorsaTariffaServizio where Stato=1 and Cancella=0 and CorsaId=$CorsaId and TipologiaServizioId=$TipoBigliettoId and FermataPickup=$FermataId and FermataDropOff=$FermataIdDropOff and OdcIdRef=$user->OdcId";
                            // echo($sql);  

                            $row1 = $db->query_first($sql);
                            $prezzo = "";
                            if (!empty($row1['CorsaTariffaServizioId']))
                                $prezzo = $row1['Tariffa'];

                            $prezzo = str_replace(".", ",", $prezzo);
                            ?>
                                                        <tr>
                                                            <td style="width:<?= $wt ?>%;border:none;"><?= $TipoBiglietto ?></td> 

                                                            <td style="width:<?= $wt ?>%;border:none;"><input class="numberDE" type="text" name="Prezzi['<?= $TipoBigliettoId . "_" . $CorsaId . "_" . $FermataId . "_" . $FermataIdDropOff ?>']" value="<?= $prezzo ?>" SIZE="7"></td> 
                                                        </tr>
                                            <?
                                            $tb++;
                                        }
                                        ?>
                                        </tr> 
                                </table>
                                </td>
                                        <?
                                        /* $k++;
                                          } */
                                    }
                                    ?>
                                 <!-- <table><tr>
                    <? /*
                      $tb=0;
                      while ($tb< sizeof($ArrObjectTipoBiglietto))
                      {
                      $TipoBigliettoId=$ArrObjectTipoBiglietto[$tb]['TipologiaBigliettoId'];
                      ?>
                      <td><input class="Prezzo" type="text" name="Prezzi['<?=$TipoBigliettoId."_".$CorsaId."_".$FermataId."_".$FermataIdDropOff?>']" value="<?=$prezzo?>" SIZE="10" MAXLENGTH="10"></td>

                      <?
                      $tb++;
                      }
                     */
                    ?>
                                      </tr>
                                  </table>-->


                                    <?
                                    /*  }
                                      else
                                      {
                                      ?>
                                      <td></td>
                                      <?
                                      } */
                                    $n++;
                                }
                                ?>



                        </tr>
                                    <?
                                    $i++;
                                }
                                ?>

                    </tbody>
                    </table>
                                <?
                                $tr++;
                            }
                            $f++;
                        }
                        ?>
        </div>

        <div class="divSubmit">

                        <? $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>



        </div>

    </form>


                        <?
                        $db->Close();
                    }

function add($step) {
	include_once("timetable_validator.php");

    global $HtmlCommon, $db, $linea_wizard, $funzione_edit, $abilita_modifica, $dizionario;

    if (!$step) {
		$linea_wizard = null;
		unset($linea_wizard);
		$_SESSION['LINEA_WIZARD'] = null;
		unset($_SESSION['LINEA_WIZARD']);
		$step = 1;
    }
    $mod = 0;
    $GestoreStato = -1;
    if (is_object($linea_wizard)) {
		$LineaId = $linea_wizard->Id;
		$linea_wizard->conn = $db;
		$linea_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $linea_wizard->DatiGenerali;
		$Stato = $DatiGeneraliArr['Stato'];
		$mod = 1;
		$abilita_modifica = true;
		$HtmlCommon->html_titolo_pagina($dizionario['timetable']['titolo_timetable']." " . $DatiGeneraliArr['LineaNome'], 1, "rt_timetable", "timetable.php");
	} else {
		$mod = 0;
		$abilita_modifica = false;
		$HtmlCommon->html_titolo_pagina($dizionario['timetable']['titolo_aggiungi_percorso'], 1, "rt_tratta", "tratta_wz.php");
	}

	carica_menu_percorso($step, $mod);
	?>

	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
	<? if ($step == 1)
		add_step_fermate_orari();
	elseif ($step == 2)
		add_step_tariffe();

	$db = new Database();
	$db->connect();
	?>

    </div>
	<? if ($GestoreStato >= 0) { ?>
	<div class="brain_stato-mediazione">
		<h3><?=$dizionario['gestore']['stato_gestore']?></h3>
		<form id="CambiaStatoMediazioneId">
			<input type="hidden" name="action" value="cambia_stato_gestore" />
	
			<?php if ($GestoreStato){?><i class="fa fa-check-circle green" aria-hidden="true"></i> <?=$dizionario['generale']['attivo']?><p><?=$dizionario['listino']['attivo_desc']?></p><? } ?>
			<?php if ($GestoreStato==0){?><i class="fa fa-times-circle red" aria-hidden="true"></i> <?=$dizionario['generale']['disattivo']?><p><?=$dizionario['listino']['disattivo_desc']?></p><? } ?>

			<div class="CambiaStatoMediazione">
				<select name="Stato">
					<option value="1" <? if ($GestoreStato == 1)
						echo ("selected") ?> >attivo</option>
					<option value="0" <? if ($GestoreStato == 0)
						echo ("selected") ?> >disattivo</option>
				</select>   
			</div>
			<div class="CambiaStatoMediazioneSubmit">
				<input class="brain_CambiaStato" type="submit" name="CambiaStato" value="Cambia" />
			</div>   
		</form>    
	</div>
	<? }
}

function spara_pulsanti_wizard_box()
{
	global $dizionario;
$page=new Form();
    
?>
<div class="divSubmit">
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
   
   
    
         <a href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi" class="brain_annulla"><?=$dizionario['generale']['chiudi']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>
    <?

}


function spara_pulsanti_wizard($steptogo)
{
    
global $funzione_edit, $dizionario;

if ($funzione_edit)
    spara_pulsanti_edit($steptogo);
else
{
if (!$funzione_edit)
$page=new Form();
    
?>
<div class="divSubmit">
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['avanti'],"brain_salva","submit"); ?>
    <?  
    if ($steptogo>0)
    $page->create_button("indietro","indietro",$dizionario['generale']['indietro'],"brain_back","button"); ?>
    
         <a href="javascript:void(0);" onclick="loadMainContent('mediatore','mediatore.php',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>
    <?
    
    
}
}

function spara_pulsanti_edit($steptogo)
{
   
global $abilita_modifica, $dizionario;
    
    $page=new Form();
    
    ?>
<div class="divSubmit">
                         
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
       
         <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>  
    <?
}
        

if (is_object($user)) {

    /*      ID - FUNZIONE
      1	Lista
      2	Aggiunta
      3	Cancellazione
      4	Modifica
      5	Esportazione
      6	Importazione
      7	Stampa
     */




    $db = new Database();
    $db->connect();
    $user->conn = $db;
    if (is_object($linea_wizard))
        $linea_wizard->conn = $db;
    $permessi = $user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi) > 0) {
	    if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }


        switch ($do) {
            case "aumentoPrezzi":
                $FunzioneId = 2;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso))
                    aumentoPrezzi();
                    else
                $errore->stampa_errore(2);
                
                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
                break;
            case "add":
                $FunzioneId = 2;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso))
                    add($_REQUEST['step']);
                else
                    $errore->stampa_errore(2);

                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                break;


            case "edit":

                $FunzioneId = 4;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                // print_r($permesso);
                if (sizeof($permesso))
                    edit($_REQUEST['LineaId']);
                else
                    $errore->stampa_errore(2);

                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                break;

            case "cerca":

                $FunzioneId = 1;
                $permesso = $user->ControllModuloFunzionePermesso(2, $FunzioneId);

                if (sizeof($permesso))
                    cerca_mediatore();
                else
                    $errore->stampa_errore(2);

                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                break;




            default:
                $FunzioneId = 1;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso))
                    show_list();
                else
                    $errore->stampa_errore(2);

                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                break;
        }
    } // end verifica permessi
    else {
        $errore->stampa_errore(1);
    }
}
// se l'utente non e' loggato
else {
    header("Location: /logout.php");
}
?>