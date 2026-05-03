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
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Provvigione.php");
include_once($classespath_."class.GestoreProvvigione.php");
include_once($classespath_."class.Linea.php");
$ModuloId=16;

function add()
{
    global $HtmlCommon, $user, $dizionario;

    $db = new Database();
    $db->connect();
    $page = new Form();
    $gestore = new Gestore();
    $gestore->conn = $db;
    $gestore->getGestoreAll($user->GestoreId);
    $arr_gestori = $gestore->ArrGestore;

    include_once("gestore_convenzione_validator.php");

    $HtmlCommon->html_titolo_pagina($dizionario['gestore_convenzione']['titolo_aggiungi']);
    $HtmlCommon->html_titolo_box($dizionario['gestore_convenzione']['titolo_aggiungi']);

    $provvigione = new Provvigione();
    $provvigione->conn = $db;
    $arr_provvigione = $provvigione->getAllForSelect();

    $linea = new Linea();
    $linea->conn = $db;
    $arr_linea = $linea->getAllForSelect();

    $arr_stato[] = ["StatoId" => '0', "Stato" => $dizionario['generale']['disattivo']];
    $arr_stato[] = ["StatoId" => '1', "Stato" => $dizionario['generale']['attivo']];

    $arr_sino[] = ["SiNoId" => '0', "SiNo" => $dizionario['generale']['no']];
    $arr_sino[] = ["SiNoId" => '1', "SiNo" => $dizionario['generale']['si']];
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            // Datepicker
            var d = new Date();
            $(function () {
                $("#DataDalId, #DataAlId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dateFormat: 'dd/mm/yy',
                    firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
                });
            });
			
			$('#selectAllLineaId').click(function () {
				$('#LineaId option[value!=""]').attr('selected', 'selected'); // Seleziona tutte le opzioni
				$('#LineaId').trigger('change'); // Trigger per aggiornare eventuali listener
			});
        });
    </script>

    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_formModifica">
                    <div class="brain_data-content">
                        <?php 
                        $page->create_textbox_hidden("action", "create");
                        $page->create_select(
                            $dizionario['generale']['linea']. ' (<a id="selectAllLineaId" style="cursor:pointer;"><u>'.$dizionario['generale']['seleziona_linee'].'</u></a>)',
                            "LineaId[]",
                            "LineaId",
                            "brain_campoForm",
                            $arr_linea,
                            0,
                            "LineaId",
                            "LineaNome",
                            ["class" => "'required'",
							"multiple" => "multiple"],
                            1
                        );

                        $page->create_select(
                            $dizionario['gestore']['provvigione'],
                            "Convenzione[ProvvigioneId]",
                            "ProvvigioneId",
                            "brain_campoForm",
                            $arr_provvigione,
                            0,
                            "ProvvigioneId",
                            "ProvvigioneNome",
                            ["class" => "'required'"],
                            1
                        );
						
						print(" <br style=\"clear:both;\"/>");
						
                        $page->create_textbox(
                            $dizionario['gestore_convenzione']['attiva_dal'],
                            "DataDalId",
                            "Convenzione[ValidaDal]",
                            "",
                            1,
                            "brain_campoForm",
                            ["class" => "'required italianDate'"],
                            "",
                            "10"
                        );

                        $page->create_textbox(
                            $dizionario['gestore_convenzione']['attiva_al'],
                            "DataAlId",
                            "Convenzione[ValidaAl]",
                            "",
                            1,
                            "brain_campoForm",
                            ["class" => "'required italianDate'"],
                            "",
                            "10"
                        );
						
						print(" <br style=\"clear:both;\"/>");

                        $page->create_select(
                            $dizionario['gestore_convenzione']['consenti_prenotazione'],
                            "Convenzione[SoloPrenotazione]",
                            "SoloPrenotazioneId",
                            "brain_campoForm",
                            $arr_sino,
                            0,
                            "SiNoId",
                            "SiNo",
                            ["class" => "'required'"],
                            1
                        );

                        $page->create_select(
                            $dizionario['gestore_convenzione']['consenti_attesa'],
                            "Convenzione[ListaAttesa]",
                            "ListaAttesa",
                            "brain_campoForm",
                            $arr_sino,
                            0,
                            "SiNoId",
                            "SiNo",
                            ["class" => "'required'"],
                            1
                        );

                        $page->create_select(
                            $dizionario['generale']['stato'],
                            "Convenzione[Stato]",
                            "StatoId",
                            "brain_campoForm",
                            $arr_stato,
                            1,
                            "StatoId",
                            "Stato",
                            ["class" => "'required'"],
                            1
                        );

                        if (isset($_REQUEST['GestoreId'])) {
                            echo '<input type="hidden" name="Convenzione[GestoreId]" value="' . $_REQUEST['GestoreId'] . '" />';
                        } else {
                            $page->create_select(
                                $dizionario['gestore']['gestore'],
                                "Convenzione[GestoreId]",
                                "GestoreId",
                                "brain_campoForm",
                                $arr_gestori,
                                $user->GestoreId,
                                'GestoreId',
                                'RagioneSociale',
                                ["class" => "'required'"],
                                1
                            );
                        }
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




function edit()
{
    include_once("gestore_convenzione_validator.php");
    $GestoreConvenzioneId = $_REQUEST['GestoreConvenzioneId'];

    global $HtmlCommon, $user, $dizionario;
    $dt = new DT();
    $db = new Database();
    $db->connect();
    $page = new Form();
    $gestore = new Gestore();
    $gestore->conn = $db;
    $gestore->getGestoreAll($user->GestoreId);
    $arr_gestori = $gestore->ArrGestore;

    $gestore_provvigione = new GestoreProvvigione($GestoreConvenzioneId);
    $gestore_provvigione->conn = $db;
    $gestore_provvigione->inizializzaDatiGenerali();
    $arr_gestore_prov = $gestore_provvigione->DatiGenerali;

    $provvigione = new Provvigione();
    $provvigione->conn = $db;
    $arr_provvigione = $provvigione->getAllForSelect();

    $linea = new Linea();
    $linea->conn = $db;
    $arr_linea = $linea->getAllForSelect($arr_gestore_prov['LineaId']);

    $ConvenzioneNome = $arr_gestore_prov['ProvvigioneNome'];

    $HtmlCommon->html_titolo_pagina(
        $dizionario['gestore_convenzione']['titolo_modifica_convenzione'] . " - " . $ConvenzioneNome
    );
    $HtmlCommon->html_titolo_box(
        $dizionario['gestore_convenzione']['titolo_modifica_convenzione'] . " - " . $ConvenzioneNome
    );

    $arr_stato[] = ["StatoId" => '0', "Stato" => $dizionario['generale']['disattiva']];
    $arr_stato[] = ["StatoId" => '1', "Stato" => $dizionario['generale']['attiva']];

    $arr_sino[] = ["SiNoId" => '0', "SiNo" => $dizionario['generale']['no']];
    $arr_sino[] = ["SiNoId" => '1', "SiNo" => $dizionario['generale']['si']];
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            var d = new Date();
            $(function () {
                $("#DataDalId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    dateFormat: 'dd/mm/yy',
                    firstDay: 1
                });

                $("#DataAlId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    dateFormat: 'dd/mm/yy',
                    firstDay: 1
                });
            });
        });
    </script>
    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_formModifica">
                    <div class="brain_data-content">
                        <?php
                        $page->create_textbox_hidden("action", "update");
                        $page->create_textbox_hidden("idpost", $GestoreConvenzioneId);
                        $page->create_select(
                            $dizionario['generale']['linea'],
                            "Convenzione[LineaId]",
                            "LineaId",
                            "brain_campoForm",
                            $arr_linea,
                            $arr_gestore_prov['LineaId'],
                            "LineaId",
                            "LineaNome",
                            ["class" => "'required'"],
                            1
                        );

                        $page->create_select(
                            $dizionario['gestore']['provvigione'],
                            "Convenzione[ProvvigioneId]",
                            "ProvvigioneId",
                            "brain_campoForm",
                            $arr_provvigione,
                            $arr_gestore_prov['ProvvigioneId'],
                            "ProvvigioneId",
                            "ProvvigioneNome",
                            ["class" => "'required'"],
                            1
                        );

						print(" <br style=\"clear:both;\"/>");

                        $page->create_textbox(
                            $dizionario['gestore_convenzione']['attiva_dal'],
                            "DataDalId",
                            "Convenzione[ValidaDal]",
                            $dt->format($arr_gestore_prov['ValidaDal'], "Y-m-d", "d/m/Y"),
                            1,
                            "brain_campoForm",
                            ["class" => "'required italianDate'"],
                            "",
                            "10"
                        );

                        $page->create_textbox(
                            $dizionario['gestore_convenzione']['attiva_al'],
                            "DataAlId",
                            "Convenzione[ValidaAl]",
                            $dt->format($arr_gestore_prov['ValidaAl'], "Y-m-d", "d/m/Y"),
                            1,
                            "brain_campoForm",
                            ["class" => "'required italianDate'"],
                            "",
                            "10"
                        );
						
						print(" <br style=\"clear:both;\"/>");

                        $page->create_select(
                            $dizionario['gestore_convenzione']['consenti_prenotazione'],
                            "Convenzione[SoloPrenotazione]",
                            "SoloPrenotazioneId",
                            "brain_campoForm",
                            $arr_sino,
                            $arr_gestore_prov['SoloPrenotazione'],
                            "SiNoId",
                            "SiNo",
                            ["class" => "'required'"],
                            1
                        );

                        $page->create_select(
                            $dizionario['gestore_convenzione']['consenti_attesa'],
                            "Convenzione[ListaAttesa]",
                            "SoloPrenotazioneId",
                            "brain_campoForm",
                            $arr_sino,
                            $arr_gestore_prov['ListaAttesa'],
                            "SiNoId",
                            "SiNo",
                            ["class" => "'required'"],
                            1
                        );

                        $page->create_select(
                            $dizionario['generale']['stato'],
                            "Convenzione[Stato]",
                            "StatoId",
                            "brain_campoForm",
                            $arr_stato,
                            $arr_gestore_prov['Stato'],
                            "StatoId",
                            "Stato",
                            ["class" => "'required'"],
                            1
                        );

                        ?>
                        <input type="hidden" name="Convenzione[GestoreId]" value="<?=$arr_gestore_prov['GestoreId']?>" />
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


if (is_object($user)) {
    // Connessione al database
    $db = new Database();
    $db->connect();
    $user->conn = $db;

    // Recupera i permessi del modulo
    $permessi = $user->get_permessi_modulo($ModuloId);

    // Verifica se ci sono permessi per il modulo
    if (sizeof($permessi) > 0) {
        // Recupera il parametro 'do' dall'input
        $do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';

        // Switch per gestire le azioni richieste
        switch ($do) {
            case "add":
                $FunzioneId = 2; // ID della funzione per "add"
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                
                if (sizeof($permesso)) {
                    add(); // Esegue la funzione di aggiunta
                } else {
                    echo Errors::$ErrorePermessiModuloFunzione;
                }
                break;

            case "edit":
                $FunzioneId = 4; // ID della funzione per "edit"
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                
                if (sizeof($permesso)) {
                    edit($_REQUEST['GestoreConvenzioneId']); // Esegue la funzione di modifica
                } else {
                    echo Errors::$ErrorePermessiModuloFunzione;
                }
                break;

            // Puoi aggiungere altri case per gestire altre azioni
            
            default:
                // Azione non riconosciuta
                echo Errors::$ErrorePermessiModuloFunzione;
                break;
        }
    } else {
        // L'utente non ha permessi per il modulo
        echo Errors::$ErrorePermessiModulo;
    }
} else {
    // L'utente non è autenticato, reindirizza alla pagina di logout
    header("Location: /logout.php");
}
?>
