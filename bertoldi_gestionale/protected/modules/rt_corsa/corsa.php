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
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.TipologiaBus.php");
include_once($classespath_."class.TipologiaBiglietto.php");
include_once($classespath_."class.Flotta.php");

$ModuloId=28;

function add()
{
    global $HtmlCommon, $user, $dizionario;

    $db = new Database();
    $db->connect();
    $page = new Form();

    include_once("corsa_validator.php");
    $HtmlCommon->html_titolo_pagina($dizionario['corsa']['titolo_aggiungi'], 0, "rt_tratta", "tratta.php");
    $HtmlCommon->html_titolo_box($dizionario['corsa']['titolo_aggiungi']);

    $arr_stato[] = array("StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']);
    $arr_stato[] = array("StatoId" => '1', "Stato" => $dizionario['generale']['attivo']);

    $tipologiabus = new TipologiaBus();
    $tipologiabus->conn = $db;
    $arr_tipologiabus = $tipologiabus->getAllForSelect();
	
	$flotta = new Flotta();
    $flotta->conn = $db;
    $arr_flotta = $flotta->getAllForSelectModel();
	
    ?>
    <script type="text/javascript">
        $(document).ready(function () {

            // Datepicker
            var d = new Date();
            $(function () {
                $("#DataDalId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm', weekStatus: '',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy', firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                    dateFormat: 'dd/mm/yy'
                });

                $("#DataAlId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm', weekStatus: '',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy', firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                    dateFormat: 'dd/mm/yy'
                });

                $("#DataVendibileAlId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm', weekStatus: '',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy', firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                    dateFormat: 'dd/mm/yy'
                });

                $("#DataVendibileDalId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm', weekStatus: '',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy', firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                    dateFormat: 'dd/mm/yy'
                });

            });


        });
        $("#OrarioPartenzaId").mask("99:99");
        $("#OrarioArrivoId").mask("99:99");
        $("#OrePrimaStopVenditaId").mask("99:99");
    </script>

    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_formModifica">
                    <div class="brain_data-content">
                        <h3><?=$dizionario['corsa']['info_corsa']?></h3>
                        <?php
                        $page->create_textbox_hidden("action", "create");
                        $page->create_textbox($dizionario['generale']['corsa'], "CorsaNome", "Corsa[CorsaNome]", "", 1, "brain_campoForm campiformBig", array("class" => "'required'"));
                        //print("<br style=\"clear:both;\"/>");
                        //$page->create_select($dizionario['generale']['tipo'], "Corsa[TipologiaBusDefaultId][]", "TipologiaBusDefaultId", "brain_campoForm campiformBig", $arr_tipologiabus, 0, "TipologiaBusId", "TipologiaBus", array("class" => "'required'", "multiple" => "''"), 1);
                        print("<br style=\"clear:both;\"/>");
                        $page->create_select($dizionario['generale']['flotta'], "Corsa[FlottaDefaultId][]", "FlottaDefaultId", "brain_campoForm campiformBig", $arr_flotta, 0, "FlottaId", "Modello", array("class" => "'required'", "multiple" => "''"), 1);
						print("<br style=\"clear:both;\"/>");
                        $page->create_textbox($dizionario['generale']['peso'], "Peso", "Corsa[CorsaPeso]", "", 1, "brain_campoForm", array("class" => "'required'"));
                        $page->create_select($dizionario['generale']['stato'], "Corsa[Stato]", "StatoId", "brain_campoForm", $arr_stato, 1, "StatoId", "Stato", array("class" => "'required'"), 1);
                        print("<br style=\"clear:both;\"/>");
                        ?>
                        <h3><?=$dizionario['corsa']['periodo_operativita']?></h3>
                        <?php
                        $page->create_textbox($dizionario['corsa']['attiva_dal'], "DataDalId", "Corsa[AttivaDal]", "", 1, "brain_campoForm", array("class" => "'required italianDate'"), "", "10");
                        $page->create_textbox($dizionario['corsa']['attiva_al'], "DataAlId", "Corsa[AttivaAl]", "", 1, "brain_campoForm", array("class" => "'required italianDate'"), "", "10");
                        print("<br style=\"clear:both;\"/>");
                        $page->create_textbox($dizionario['corsa']['orario_partenza'], "OrarioPartenzaId", "Corsa[OrarioPartenza]", "", 1, "brain_campoForm", array("class" => "'required'"), "", "10");
                        $page->create_textbox($dizionario['corsa']['orario_arrivo'], "OrarioArrivoId", "Corsa[OrarioArrivo]", "", 1, "brain_campoForm", array("class" => "'required'"), "", "10");
                        print("<br style=\"clear:both;\"/>");
                        $page->create_input_checkbox($dizionario['corsa']['feriali'], "Feriali", "Corsa[IncludiFeriale]", 0, 0, "brain_campoForm", 0, "");
                        $page->create_input_checkbox($dizionario['corsa']['prefestivi'], "PreFestivi", "Corsa[IncludiPrefestivo]", 0, 0, "brain_campoForm", 0, "");
                        $page->create_input_checkbox($dizionario['corsa']['festivi'], "Festivi", "Corsa[IncludiFestivo]", 0, 0, "brain_campoForm", 0, "");
                        print("<br style=\"clear:both;\"/>");
                        ?>
                        <h3><?=$dizionario['corsa']['giorni_operativita']?></h3>
                        <?php
                        $page->create_input_checkbox($dizionario['generale']['lunedi'], "Settimana[Settimana2]", "Settimana[Settimana2]", 0, 0, "brain_campoForm", 0, "");
                        $page->create_input_checkbox($dizionario['generale']['martedi'], "Settimana[Settimana3]", "Settimana[Settimana3]", 0, 0, "brain_campoForm", 0, "");
                        $page->create_input_checkbox($dizionario['generale']['mercoledi'], "Settimana[Settimana4]", "Settimana[Settimana4]", 0, 0, "brain_campoForm", 0, "");
                        $page->create_input_checkbox($dizionario['generale']['giovedi'], "Settimana[Settimana5]", "Settimana[Settimana5]", 0, 0, "brain_campoForm", 0, "");
                        $page->create_input_checkbox($dizionario['generale']['venerdi'], "Settimana[Settimana6]", "Settimana[Settimana6]", 0, 0, "brain_campoForm", 0, "");
                        $page->create_input_checkbox($dizionario['generale']['sabato'], "Settimana[Settimana7]", "Settimana[Settimana7]", 0, 0, "brain_campoForm", 0, "");
                        $page->create_input_checkbox($dizionario['generale']['domenica'], "Settimana[Settimana1]", "Settimana[Settimana1]", 0, 0, "brain_campoForm", 0, "");
                        print("<br style=\"clear:both;\"/>");
                        ?>
                        <h3><?=$dizionario['corsa']['intervallo_validita']?></h3>
                        <?php
                        $page->create_textbox($dizionario['corsa']['vendibile_dal'], "DataVendibileDalId", "Corsa[VendibileDal]", "", 1, "brain_campoForm", array("class" => "'required italianDate'"), "", "10");
                        $page->create_textbox($dizionario['corsa']['vendibile_al'], "DataVendibileAlId", "Corsa[VendibileAl]", "", 1, "brain_campoForm", array("class" => "'required italianDate'"), "", "10");
                        $page->create_textbox($dizionario['corsa']['ore_prima_vendita'], "OrePrimaStopVenditaId", "Corsa[OrePrimaStopVendita]", "02:00", 1, "brain_campoForm", array("class" => "'required'"), "", "10");
                        print("<br style=\"clear:both;\"/>");

                        print("<br style=\"clear:both;\"/>");
                        if (isset($_REQUEST['LineaId'])) {
                        ?>
                            <input type="hidden" name="Corsa[LineaId]" value="<?=$_REQUEST['LineaId']?>" />
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




function edit($CorsaId)
{
    include_once("corsa_validator.php");
    global $HtmlCommon, $user, $dizionario;

    $db = new Database();
    $db->connect();
    $page = new Form();
    $dt = new DT();

    $corsa = new Corsa();
    $corsa->Id = $CorsaId;
    $corsa->conn = $db;
    $corsa->inizializzaDatiGenerali();
    $arrCorsa = $corsa->DatiGenerali;

    $HtmlCommon->html_titolo_pagina($dizionario['corsa']['titolo_edit'], 0, "rt_corsa", "corsa.php");
    $HtmlCommon->html_titolo_box($dizionario['corsa']['titolo_edit'] . " - " . $arrCorsa['CorsaNome']);
    $arr_stato[] = array("StatoId" => '0', "Stato" => $dizionario['generale']['disattiva']);
    $arr_stato[] = array("StatoId" => '1', "Stato" => $dizionario['generale']['attiva']);

    $flotta = new Flotta();
    $flotta->conn = $db;
    $arr_flotta = $flotta->getAllForSelectModel();

    $lun = null;
    $mar = null;
    $mer = null;
    $gio = null;
    $ven = null;
    $sab = null;
    $dom = null;

    $sql = "Select * from RT_CorsaSettimana where CorsaId=$CorsaId and OdcIdRef=$user->OdcId";

    $ArrObject = $db->fetch_array($sql);
    $i = 0;
    $tratta_old = 0;
    while ($i < sizeof($ArrObject)) {

        if ($ArrObject[$i]['SettimanaId'] == 1)
            $dom = array("checked" => "'checked'");
        elseif ($ArrObject[$i]['SettimanaId'] == 2)
            $lun = array("checked" => "'checked'");
        elseif ($ArrObject[$i]['SettimanaId'] == 3)
            $mar = array("checked" => "'checked'");
        elseif ($ArrObject[$i]['SettimanaId'] == 4)
            $mer = array("checked" => "'checked'");
        elseif ($ArrObject[$i]['SettimanaId'] == 5)
            $gio = array("checked" => "'checked'");
        elseif ($ArrObject[$i]['SettimanaId'] == 6)
            $ven = array("checked" => "'checked'");
        elseif ($ArrObject[$i]['SettimanaId'] == 7)
            $sab = array("checked" => "'checked'");


        $i++;
    }
	
	//recupero linea e tipo tour
	$sql = "SELECT RT_Linea.* FROM RT_Linea 
			LEFT JOIN RT_Corsa ON RT_Corsa.LineaId = RT_Linea.LineaId
			WHERE CorsaId = ".$CorsaId;
	$linea = $db->query_first($sql);

    // inizializzo array delle corse della select per il salvataggio di massa delle corse
    $sql = "SELECT c.CorsaId, CONCAT(l.LineaNome, ' - ', c.CorsaNome) as Corsa FROM RT_Corsa c
                LEFT JOIN RT_Linea l ON l.LineaId = c.LineaId
                WHERE l.PercorsoId = ".$linea['PercorsoId']." 
                    AND c.Stato = 1 AND c.Cancella = 0 
                    AND l.Stato = 1 AND l.Cancella = 0
                ORDER BY LineaPeso ASC, LineaNome ASC, CorsaPeso ASC, CorsaNome ASC ";
    $selectCorse = $db->fetch_array($sql);

    ?>
    <script type="text/javascript">
        $(document).ready(function () {


            // Datepicker
            var d = new Date();
            $(function () {
                $("#DataDalId").datepicker({
                    monthNames:
                        [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm', weekStatus: '',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames:
                        [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy', firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                    dateFormat: 'dd/mm/yy'
                });

                $("#DataAlId").datepicker({
                    monthNames:
                        [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm', weekStatus: '',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames:
                        [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy', firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                    dateFormat: 'dd/mm/yy'
                });

                $("#DataVendibileDalId").datepicker({
                    monthNames:
                        [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm', weekStatus: '',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames:
                        [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy', firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                    dateFormat: 'dd/mm/yy'
                });


                $("#DataVendibileAlId").datepicker({
                    monthNames:
                        [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm', weekStatus: '',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames:
                        [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy', firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                    dateFormat: 'dd/mm/yy'
                });


            });


        });
        $("#OrarioPartenzaId").mask("99:99");
        $("#OrarioArrivoId").mask("99:99");
        $("#OrePrimaStopVenditaId").mask("99:99");
    </script>
    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_formModifica">
                    <div class="brain_data-content">
                        <h3><?=$dizionario['corsa']['info_corsa']?></h3>
                        <?php
                        $feriale = "";
                        $prefestivo = "";
                        $festivo = "";

                        if ($arrCorsa['IncludiFeriale'] == 1)
                            $feriale = array("checked" => "'checked'");

                        if ($arrCorsa['IncludiPrefestivo'] == 1)
                            $prefestivo = array("checked" => "'checked'");

                        if ($arrCorsa['IncludiFestivo'] == 1)
                            $festivo = array("checked" => "'checked'");

                        $page->create_textbox_hidden("action", "update");
                        $page->create_textbox_hidden("idpost", $CorsaId);
						$page->create_textbox_hidden("Corsa[TipologiaBusDefaultId]", $arrCorsa['TipologiaBusDefaultId']);
						
                        print("<br style=\"clear:both;\"/>");
                        $page->create_textbox($dizionario['generale']['corsa'], "CorsaNome", "Corsa[CorsaNome]", $arrCorsa['CorsaNome'], 1, "brain_campoForm campiformBig", array("class" => "'required'"));
                        print("<br style=\"clear:both;\"/>");

                        print("<br style=\"clear:both;\"/>");
                        $page->create_select($dizionario['generale']['flotta'], "Corsa[FlottaDefaultId]", "FlottaDefaultId", "brain_campoForm campiformBig", $arr_flotta, $arrCorsa['FlottaDefaultId'], "FlottaId", "Modello", array("class" => "'required'"), 1);

                        print("<br style=\"clear:both;\"/>");

                        $page->create_textbox($dizionario['generale']['peso'], "Peso", "Corsa[CorsaPeso]", $arrCorsa['CorsaPeso'], 1, "brain_campoForm", array("class" => "'required'"));
                        $page->create_select($dizionario['generale']['stato'], "Corsa[Stato]", "StatoId", "brain_campoForm", $arr_stato, $arrCorsa['Stato'], "StatoId", "Stato",
                            array("class" => "'required'"), 1);
                        print("<br style=\"clear:both;\"/>");
                        ?>
                        <h3><?=$dizionario['corsa']['periodo_operativita']?></h3>
                        <?php
                        $page->create_textbox($dizionario['corsa']['attiva_dal'], "DataDalId", "Corsa[AttivaDal]", $dt->format($arrCorsa['AttivaDal'], "Y-m-d", "d/m/Y"), 1, "brain_campoForm", array("class" => "'required italianDate'"), "", "10");
                        $page->create_textbox($dizionario['corsa']['attiva_al'], "DataAlId", "Corsa[AttivaAl]", $dt->format($arrCorsa['AttivaAl'], "Y-m-d", "d/m/Y"), 1, "brain_campoForm", array("class" => "'required italianDate'"), "", "10");
                        print("<br style=\"clear:both;\"/>");
                        $page->create_input_checkbox($dizionario['corsa']['feriali'], "Feriali", "Corsa[IncludiFeriale]", 1, $arrCorsa['IncludiFeriale'], "brain_campoForm", $feriale, "");
                        $page->create_input_checkbox($dizionario['corsa']['prefestivi'], "PreFestivi", "Corsa[IncludiPrefestivo]", 1, $arrCorsa['IncludiPrefestivo'], "brain_campoForm", $prefestivo, "");
                        $page->create_input_checkbox($dizionario['corsa']['festivi'], "Festivi", "Corsa[IncludiFestivo]", 1, $arrCorsa['IncludiFestivo'], "brain_campoForm", $festivo, "");

                        print("<br style=\"clear:both;\"/>");
                        $page->create_textbox($dizionario['corsa']['orario_partenza'], "OrarioPartenzaId", "Corsa[OrarioPartenza]", $arrCorsa['OrarioPartenza'], 1, "brain_campoForm", array("class" => "'required'"), "", "10");

                        $page->create_textbox($dizionario['corsa']['orario_arrivo'], "OrarioArrivoId", "Corsa[OrarioArrivo]", $arrCorsa['OrarioArrivo'], 1, "brain_campoForm", array("class" => "'required'"), "", "10");
                        print("<br style=\"clear:both;\"/>");
                        ?> <h3><?=$dizionario['corsa']['giorni_operativita']?></h3>
                        <?php
                        $page->create_input_checkbox($dizionario['generale']['lunedi'], "Settimana[Settimana2]", "Settimana[Settimana2]", 0, 0, "brain_campoForm", $lun, "");
                        $page->create_input_checkbox($dizionario['generale']['martedi'], "Settimana[Settimana3]", "Settimana[Settimana3]", 0, 0, "brain_campoForm", $mar, "");
                        $page->create_input_checkbox($dizionario['generale']['mercoledi'], "Settimana[Settimana4]", "Settimana[Settimana4]", 0, 0, "brain_campoForm", $mer, "");
                        $page->create_input_checkbox($dizionario['generale']['giovedi'], "Settimana[Settimana5]", "Settimana[Settimana5]", 0, 0, "brain_campoForm", $gio, "");
                        $page->create_input_checkbox($dizionario['generale']['venerdi'], "Settimana[Settimana6]", "Settimana[Settimana6]", 0, 0, "brain_campoForm", $ven, "");
                        $page->create_input_checkbox($dizionario['generale']['sabato'], "Settimana[Settimana7]", "Settimana[Settimana7]", 0, 0, "brain_campoForm", $sab, "");
                        $page->create_input_checkbox($dizionario['generale']['domenica'], "Settimana[Settimana1]", "Settimana[Settimana1]", 0, 0, "brain_campoForm", $dom, "");

                        print("<br style=\"clear:both;\"/>");
                        ?>
                        <h3><?=$dizionario['corsa']['intervallo_validita']?></h3>
                        <?php
                        $page->create_textbox($dizionario['corsa']['vendibile_dal'], "DataVendibileDalId", "Corsa[VendibileDal]", $dt->format($arrCorsa['VendibileDal'], "Y-m-d", "d/m/Y"), 1, "brain_campoForm", array("class" => "'required italianDate'"), "", "10");
                        $page->create_textbox($dizionario['corsa']['vendibile_al'], "DataVendibileAlId", "Corsa[VendibileAl]", $dt->format($arrCorsa['VendibileAl'], "Y-m-d", "d/m/Y"), 1, "brain_campoForm", array("class" => "'required italianDate'"), "", "10");
                        $page->create_textbox($dizionario['corsa']['ore_prima_vendita'], "OrePrimaStopVenditaId", "Corsa[OrePrimaStopVendita]", $arrCorsa['OrePrimaStopVendita'], 1, "brain_campoForm", array("class" => "'required'"), "", "10");

                        print("<br style=\"clear:both;\"/>");
                        ?>
                    </div>
                </div>
                <div class="divSubmit">
                    <fieldset>
                        <legend><?=$dizionario['timetable']['salva']?></legend>
                        <input style="float:none !important;" type="radio" name="tipoCreazione" value="1" checked="checked"/><?=$dizionario['timetable']['corsa_corrente']?> 
                        <input style="float:none !important;" type="radio" name="tipoCreazione" value="2"/><?=$dizionario['timetable']['corsa_tutte_linea']?>
                        <input style="float:none !important;" type="radio" name="tipoCreazione" value="3"/><?=$dizionario['timetable']['corsa_tutte_percorso']?>
                        <input style="float:none !important;" type="radio" name="tipoCreazione" value="4"/><?=$dizionario['timetable']['corsa_seleziona']?>
                        
                    </fieldset>
                    <br><br>*<?=$dizionario['corsa']['salva_info']?>
                    <?php 
                        $page->create_select($dizionario['corsa']['seleziona_corse'], "CorsaIdSelect[]","CorsaIdSelect","brain_campiform campiformBig",$selectCorse,$CorsaId,"CorsaId","Corsa",array("multiple" => "multiple"),1);
                    ?>

                    <?php
                    $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit");
                    //$page->create_button("Cancella","Cancella","elimina","brain_cancella","button");
                    ?>
                </div>
            </form>
        </div>
    </div>
    <?php
}



function gestioneValiditaBiglietto($CorsaId)
{
	global $HtmlCommon,$user,$db, $dizionario;  

	$db = new Database();
	$db->connect();  
	$page = new Form();
	
	$HtmlCommon->html_titolo_pagina($dizionario['corsa']['titolo_gestione'],0,"","");
	$HtmlCommon->html_titolo_box ($dizionario['corsa']['titolo_gestione']);
	?>
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	
		<div class="brain_formModifica formGestoreEdita">
		
		<div class="GestoreSedeAdd"><a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_corsa','corsa.php?do=addValiditaBiglietto&CorsaId=<?=$CorsaId?>');" title="aggiungi / rimuovi validit&agrave; biglietto"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['corsa']['aggiungi_validita']?></a></div>
		
		<br />
			<table width="100%" cellspacing="0" cellpadding="0" border="0"
				id="gestoreElencoAule">
				<tbody>
					<tr class="rowIntestazione">
						<td><?=$dizionario['generale']['stato']?></td>
						<td><?=$dizionario['generale']['dal']?></td>
						<td><?=$dizionario['generale']['al']?></td>
						<td><?=$dizionario['generale']['biglietti']?></td>
						<td><?=$dizionario['generale']['edita']?></td>
					</tr>
			
					<?
					$sql="Select * from RT_ElencoValiditaBiglietto where OdcIdRef=$user->OdcId and CorsaId=$CorsaId";
					
					$ArrObject = $db->fetch_array($sql);
					
					$validitaBiglietto = array();
					foreach ($ArrObject as $row) {
						if (isset($validitaBiglietto[$row['ValiditaBigliettoId']])) {
							$validitaBiglietto[$row['ValiditaBigliettoId']]['Biglietti'] .= ", " . $row['TipologiaBiglietto'];
						} else {
							$validitaBiglietto[$row['ValiditaBigliettoId']]['Dal'] = $row['Dal'];
							$validitaBiglietto[$row['ValiditaBigliettoId']]['Stato'] = $row['Stato'];
							$validitaBiglietto[$row['ValiditaBigliettoId']]['Al'] = $row['Al'];
							$validitaBiglietto[$row['ValiditaBigliettoId']]['Biglietti'] = $row['TipologiaBiglietto'];
						}
					}
					
					foreach ($validitaBiglietto as $validitaBigliettoId => $row) {
						
					?>
						<tr class="rowBianca">
							<td><?php
								if($row['Stato'] == 1){
									?><i class="fa fa-check-circle green" aria-hidden="true" title="<?= $dizionario['generale']['attiva']?>"></i><?php 
								}else{
									?><i class="fa fa-times-circle red" aria-hidden="true" title="<?= $dizionario['generale']['disattiva']?>"></i><?php
								} 
							?></td>
							<td><span><?=$row['Dal']?></span></td>
							<td><span><?=$row['Al']?></span></td>
							<td><span><?=$row['Biglietti']?></span></td>
							<td><a title="edita" onclick="javascript:ExternalLoad('rt_corsa','corsa.php?do=editValiditaBiglietto&CorsaId=<?=$CorsaId?>&ValiditaBigliettoId=<?= $validitaBigliettoId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
			<!-- FINE --> 
			<br />
			
			<div class="GestoreSedeAdd"><a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_corsa','corsa.php?do=addValiditaBiglietto&CorsaId=<?=$CorsaId?>');" title="aggiungi / rimuovi validit&agrave; biglietto"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['corsa']['aggiungi_validita']?></a></div>
		</div>
	</div>
	<?php
}

function editValiditaBiglietto($CorsaId, $ValiditaBigliettoId = null)
{
	global $HtmlCommon, $user, $db, $dizionario;  

	$db = new Database();
	$db->connect();  
	$page = new Form();
	$dt=new DT();
	
	include_once("corsa_validator.php"); 
	
	$validitaBiglietto = array();
	$corsa=new Corsa();
	if($ValiditaBigliettoId != null){
		$corsa->Id = $CorsaId;
		$corsa->conn = $db;
		$corsa->inizializzaDatiGenerali();
		$validitaBiglietto = $corsa->getValiditaBiglietto($ValiditaBigliettoId);
	}
	
	//recupero linea e tipo tour
	$sql = "SELECT RT_Linea.* FROM RT_Linea 
			LEFT JOIN RT_Corsa ON RT_Corsa.LineaId = RT_Linea.LineaId
			WHERE CorsaId = ".$CorsaId;
	$linea = $db->query_first($sql);
	
	$HtmlCommon->html_titolo_pagina($dizionario['corsa']['titolo_inserisci_validita'],0,"","");
	$HtmlCommon->html_titolo_box ($dizionario['corsa']['titolo_inserisci_validita']);
	
	$tipoBiglietto = new TipologiaBiglietto();
	$tipoBiglietto->conn = $db;
	$tipiBiglietti = $tipoBiglietto->getAll(true, $linea['TipoTour']);

    // inizializzo array delle corse della select per il salvataggio di massa delle corse
    $sql = "SELECT c.CorsaId, CONCAT(l.LineaNome, ' - ', c.CorsaNome) as Corsa FROM RT_Corsa c
        LEFT JOIN RT_Linea l ON l.LineaId = c.LineaId
        WHERE l.PercorsoId = ".$linea['PercorsoId']." 
            AND c.Stato = 1 AND c.Cancella = 0 
            AND l.Stato = 1 AND l.Cancella = 0
        ORDER BY LineaPeso ASC, LineaNome ASC, CorsaPeso ASC, CorsaNome ASC ";
	$selectCorse = $db->fetch_array($sql);

	?>
	<script type="text/javascript"> 
    $(document).ready(function() { 
            
	   // Datepicker
		var d = new Date();
		$(function() {
			$( "#DataDalId" ).datepicker({
				monthNames:
					[<?=$dizionario['generale']['nome_mesi']?>],
					monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
					monthStatus: '<?=$dizionario['generale']['mese_status']?>',
					yearStatus: '<?=$dizionario['generale']['anno_status']?>',
					weekHeader: 'Sm', weekStatus: '',
					weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
				dayNames:
					[<?=$dizionario['generale']['nome_giorni']?>],
					dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
					dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
					dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
					dateStatus: '<?=$dizionario['generale']['data_status']?>',
					dateFormat: 'dd/mm/yy', firstDay: 1,
					initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
		            dateFormat: 'dd/mm/yy'
			});
		
		    $( "#DataAlId" ).datepicker({
		    	monthNames:
					[<?=$dizionario['generale']['nome_mesi']?>],
					monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
					monthStatus: '<?=$dizionario['generale']['mese_status']?>',
					yearStatus: '<?=$dizionario['generale']['anno_status']?>',
					weekHeader: 'Sm', weekStatus: '',
					weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
				dayNames:
					[<?=$dizionario['generale']['nome_giorni']?>],
					dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
					dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
					dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
					dateStatus: '<?=$dizionario['generale']['data_status']?>',
					dateFormat: 'dd/mm/yy', firstDay: 1,
					initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
		            dateFormat: 'dd/mm/yy'
			});
			});
	
    });
   </script>
    
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero wizart">
			<form id="application_form" class="brain_formModifica" name="application_form" method="post" action="#">

				<div class="brain_data-content"><?
					if($ValiditaBigliettoId == null){
						$page->create_textbox_hidden("action","AggiungiValiditaBiglietto");
					}else{
						$page->create_textbox_hidden("action","ModificaValiditaBiglietto");
						$page->create_textbox_hidden("validitaBigliettoId",$ValiditaBigliettoId);
					}
					$page->create_textbox_hidden("corsaId",$CorsaId);
					if($ValiditaBigliettoId == null){
						$page->create_textbox($dizionario['generale']['dal'].":", "DataDalId", "ValiditaBiglietto[Dal]", "", 1, "brain_campiform", array("class"=>"'required italianDate'"), "", "10");
						$page->create_textbox($dizionario['generale']['al'].":", "DataAlId", "ValiditaBiglietto[Al]", "", 1, "brain_campiform", array("class"=>"'required italianDate'"), "", "10");
					}else{
						$page->create_textbox($dizionario['generale']['dal'].":", "DataDalId", "ValiditaBiglietto[Dal]", $dt->format($validitaBiglietto["Dal"], "Y-m-d", "d/m/Y"), 1, "brain_campiform", array("class"=>"'required italianDate'"), "", "10");
						$page->create_textbox($dizionario['generale']['al'].":", "DataAlId", "ValiditaBiglietto[Al]", $dt->format($validitaBiglietto["Al"], "Y-m-d", "d/m/Y"), 1, "brain_campiform", array("class"=>"'required italianDate'"), "", "10");
						$statoChecked = "";
						if($validitaBiglietto["Stato"] == 1){
							$statoChecked = array("checked"=>"'checked'");
						}
						$page->create_input_checkbox($dizionario['corsa']['abilitato'],"StatoId","ValiditaBiglietto[Stato]",1,null,"brain_campiform",$statoChecked,"");
					}
					
					print("<br style=\"clear:both;\"/>");
					
					print("<h2>Passeggeri</h2>");
					foreach ($tipiBiglietti['passeggeri'] as $biglietto) {
						if($ValiditaBigliettoId == null){
							$page->create_input_checkbox($biglietto['TipologiaBiglietto'],$biglietto['TipologiaBiglietto'],"ValiditaBigliettoTipo[]",$biglietto['TipologiaBigliettoId'],null,"brain_campiform","","");
						}else{
							$attribute = "";
							if($corsa->existValiditaBigliettoDettaglio($ValiditaBigliettoId,$biglietto['TipologiaBigliettoId']) != null){
								$attribute = array("checked"=>"'checked'");
							}
			                
							$page->create_input_checkbox($biglietto['TipologiaBiglietto'],$biglietto['TipologiaBiglietto'],"ValiditaBigliettoTipo[]",$biglietto['TipologiaBigliettoId'],null,"brain_campiform",$attribute,"");
						}
					}
					print("<br style=\"clear:both;\"/>");
					print("<h2>Servizi</h2>");
					foreach ($tipiBiglietti['servizi'] as $biglietto) {
						if($ValiditaBigliettoId == null){
							$page->create_input_checkbox($biglietto['TipologiaBiglietto'],$biglietto['TipologiaBiglietto'],"ValiditaBigliettoTipo[]",$biglietto['TipologiaBigliettoId'],null,"brain_campiform","","");
						}else{
							$attribute = "";
							if($corsa->existValiditaBigliettoDettaglio($ValiditaBigliettoId,$biglietto['TipologiaBigliettoId']) != null){
								$attribute = array("checked"=>"'checked'");
							}
			                
							$page->create_input_checkbox($biglietto['TipologiaBiglietto'],$biglietto['TipologiaBiglietto'],"ValiditaBigliettoTipo[]",$biglietto['TipologiaBigliettoId'],null,"brain_campiform",$attribute,"");
						}
					}
				?></div>

				<div class="divSubmit">
                    <fieldset>
                        <legend><?=$dizionario['timetable']['salva']?></legend>
                        <input style="float:none !important;" type="radio" name="tipoCreazione" value="1" checked="checked"/><?=$dizionario['timetable']['corsa_corrente']?> 
                        <input style="float:none !important;" type="radio" name="tipoCreazione" value="2"/><?=$dizionario['timetable']['corsa_tutte_linea']?>
                        <input style="float:none !important;" type="radio" name="tipoCreazione" value="3"/><?=$dizionario['timetable']['corsa_tutte_percorso']?>
                        <input style="float:none !important;" type="radio" name="tipoCreazione" value="4"/><?=$dizionario['timetable']['corsa_seleziona']?>
                        
                    </fieldset>
                    <br><br>*<?=$dizionario['corsa']['salva_info_validita']?>
                    <?php 
                        $page->create_select($dizionario['corsa']['seleziona_corse'], "CorsaIdSelect[]","CorsaIdSelect","brain_campiform campiformBig",$selectCorse,$CorsaId,"CorsaId","Corsa",array("multiple" => "multiple"),1);
                    ?>
                    <?
					$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
				?></div>
			</form>
		</div>
	</div>
	<?php
}

// Controlla se l'oggetto $user esiste e procede con l'inizializzazione
if (is_object($user)) {
    // Inizializzazione e connessione al database
    $db = new Database();
    $db->connect();
    $user->conn = $db; // Assegna la connessione al database all'utente
    
    // Ottiene i permessi dell'utente per il modulo specificato
    $permessi = $user->get_permessi_modulo($ModuloId);

    // Controlla se sono presenti permessi
    if (sizeof($permessi) > 0) {    
        // Imposta la variabile $do in base alla richiesta; default è una stringa vuota
        $do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';

        // Switch case per gestire diverse operazioni in base al valore di $do
        switch ($do) {
            // Caso per aggiungere un elemento
            case "add":
                $FunzioneId = 2;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                
                // Verifica i permessi e chiama la funzione 'add' se l'utente è autorizzato
                if (sizeof($permesso)) {
                    add();
                } else {
                    Errors::$ErrorePermessiModuloFunzione;
                }
                break;

            // Caso per modificare un elemento
            case "edit":
                $FunzioneId = 4;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                // Verifica i permessi e chiama la funzione 'edit' se l'utente è autorizzato
                if (sizeof($permesso)) {
                    edit($_REQUEST['CorsaId']);
                } else {
                    Errors::$ErrorePermessiModuloFunzione;
                }
                break;

            // Caso per gestione della validità del biglietto
            case "gestioneValiditaBiglietto":
                $FunzioneId = 4;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                // Verifica i permessi e chiama la funzione 'gestioneValiditaBiglietto' se l'utente è autorizzato
                if (sizeof($permesso)) {
                    gestioneValiditaBiglietto($_REQUEST['CorsaId']);
                } else {
                    Errors::$ErrorePermessiModuloFunzione;
                }
                break;

            // Caso per aggiungere la validità del biglietto
            case "addValiditaBiglietto":
                $FunzioneId = 4;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                // Verifica i permessi e chiama la funzione 'editValiditaBiglietto' se l'utente è autorizzato
                if (sizeof($permesso)) {
                    editValiditaBiglietto($_REQUEST['CorsaId']);
                } else {
                    Errors::$ErrorePermessiModuloFunzione;
                }
                break;

            // Caso per modificare la validità del biglietto
            case "editValiditaBiglietto":
                $FunzioneId = 4;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                // Verifica i permessi e chiama la funzione 'editValiditaBiglietto' con parametri specifici se autorizzato
                if (sizeof($permesso)) {
                    editValiditaBiglietto($_REQUEST['CorsaId'], $_REQUEST['ValiditaBigliettoId']);
                } else {
                    Errors::$ErrorePermessiModuloFunzione;
                }
                break;

            // Caso predefinito per mostrare la lista
            default:
                $FunzioneId = 2;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                
                // Verifica i permessi e chiama la funzione 'add' se l'utente è autorizzato
                if (sizeof($permesso)) {
                    add();
                } else {
                    Errors::$ErrorePermessiModuloFunzione;
                }
                break;
        }
    } 
    // Se l'utente non ha i permessi necessari, mostra un errore
    else {
        Errors::$ErrorePermessiModulo;
    }
} 
// Se l'utente non è loggato, reindirizza alla pagina di logout
else {
    header("Location: /logout.php");
}
?>
