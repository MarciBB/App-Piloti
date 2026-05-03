<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<?php 
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
include_once($classespath_ . "class.Provvigione.php");

global $ModuloId;
$ModuloId = 27;

global $user;
global $provvigione_wizard, $funzione_edit, $abilita_modifica;

$funzione_edit = false;
$provvigione_wizard = null;

function show_list()
{
    global $user, $HtmlCommon, $ModuloId, $dizionario;
    $HtmlCommon->html_titolo_pagina($dizionario['proviggione']['titolo_gestione'], 0, "", "");
    $HtmlCommon->html_titolo_box($dizionario['proviggione']['titolo_gestione']);
    $db = new Database();
    $db->connect();

    include_once("provvigione_validator.php");
    include_once("provvigione_datatable.php");

    $aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
    //if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tratta','tratta_wz.php?do=add','aggiungi percorso');
    ?>   
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
        <thead>
            <tr class="brain_tabellaTr">
                <th width="10%"><?= $dizionario['generale']['stato'] ?></th>
                <th width="%75"><?= $dizionario['proviggione']['proviggione'] ?></th>
                <th width="10%"><?= $dizionario['generale']['peso'] ?></th>
                <th width="5%"><?= $dizionario['generale']['edita'] ?></th>
            </tr>
            <tr class="brain_tabellaFilter">
                <th><input type="text" /></th> 
                <th><input type="text" /></th> 
                <th><input type="text" /></th> 
                <th><input type="hidden" /></th> 
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?= $dizionario['proviggione']['caricamento_in_corso'] ?></td>
            </tr>
        </tbody>
        <tfoot> 
            <tr> 
                <td colspan="4"></td>
            </tr> 
        </tfoot> 
    </table>
    <?php
    $db->close();
}
function edit($ProvvigioneId)
{
    global $provvigione_wizard, $db, $user;
    $provvigione_wizard = new Provvigione($ProvvigioneId);
    $_SESSION['PROVVIGIONE_WIZARD'] = serialize($provvigione_wizard);
    add(1);
}

function carica_menu_provvigioni($step_corrente, $mod)
{
    global $abilita_modifica, $provvigione_wizard, $db, $dizionario;

    $menu = array(
        1 => $dizionario['proviggione']['menu_tipo'],
        2 => $dizionario['proviggione']['menu_provvigioni']
    );

    ?>
    <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
        <ul>
            <?php
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
                            <?php
                            if (true) { ?>
                                <a href="javascript:void(0);" onclick="loadMediazioneStep('rt_provvigione','provvigione.php?do=add&step=<?= $contamenu ?>',this);" title="<?= $menu[$contamenu] ?>"><?= $menu[$contamenu] . " " . $StatoStep ?></a>
                            <?php
                            } else {
                                echo($menu[$contamenu]);
                            }
                            ?>
                        </span>
                    </li>
                <?php
                }
                $contamenu++;
            }
            ?>
        </ul>
    </div>
    <?php
}

function add_step_tratta()
{
    $step_corrente = 1;

    global $provvigione_wizard, $user, $db, $dizionario;

    $page = new Form();
    $dt = new DT();

    $azione = "add";
    $action = "create";

    $TrattaId = 0;

    if (is_object($provvigione_wizard) and ($provvigione_wizard->Id)) {
        $TrattaId = $provvigione_wizard->Id;

        $provvigione_wizard->conn = $db;
        $provvigione_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr = $provvigione_wizard->DatiGenerali;
        $_SESSION['PROVVIGIONE_WIZARD'] = serialize($provvigione_wizard);

        if ($DatiGeneraliArr['ProvvigioneId']) {
            $azione = "edit";
            $action = "update";
        }
    }
    ?>

    <form id="application_form" name="application_form" method="post" action="#">
        <?php
        $page->create_textbox_hidden("action", $action);
        $page->create_textbox_hidden("step_corrente", $step_corrente);
        $page->create_textbox_hidden("step_successivo", $step_corrente + 1);
        ?>

        <div class="brain_formModifica">
            <?php if ($action == "create") { ?>
                <h2>Informazioni generali</h2>
            <?php } else { ?>
                <h2><span class="brain_colorh2"><?= $provvigione_wizard->DatiGenerali['TrattaNome'] ?></span></h2>
            <?php } ?>
            <div class="brain_data-content">
                <?php form_tipo1($azione, $TrattaId); ?>
                <br style="clear:both;" />
            </div>
        </div>
        <?php spara_pulsanti_wizard(0) ?>
    </form>

    <?php
    $db->Close();
}
function form_tipo1($azione, $AnagraficaId)
{
    global $HtmlCommon, $db, $user, $provvigione_wizard, $dizionario;
    /*$db= new Database();
    $db->connect();*/
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
        $DatiGeneraliArr = $provvigione_wizard->DatiGenerali;
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
    <?php
    print("<br style=\"clear:both;\"/>");
}
function add_step_provvigione_prezzi()
{
    $step_corrente = 1;

    global $provvigione_wizard, $user, $db, $dizionario;

    $page = new Form();
    $dt = new DT();

    $LineaId = $provvigione_wizard->Id;

    include_once("provvigione_validator.php");

    //recupero linee
    $sql = "Select * from RT_ElencoLinea where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId";
    $ArrObjectLinea = $db->fetch_array($sql);

    //recupero tipo biglietto
    $sql = "Select * from RT_TipologiaBiglietto where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId";
    $ArrObjectTB = $db->fetch_array($sql);

    //recupero provvigioni
    $sql = "Select * from RT_Provvigione where Stato=1 and Cancella=0 and  OdcIdRef=$user->OdcId";
    $ArrObject = $db->fetch_array($sql);

    //array per select provvigione
    $selectProvvgione = array();
    foreach($ArrObject as $key => $value) {
        $selectProvvgione[$key]['ProvvigioneId'] = $value['ProvvigioneId'];
        $selectProvvgione[$key]['Provvigione'] = $value['ProvvigioneNome'];
    }

    //array per select linea
    $selectLinea = array();
    foreach($ArrObjectLinea as $key => $value) {
        $selectLinea[$key]['LineaId'] = $value['LineaId'];
        $selectLinea[$key]['Linea'] = $value['LineaNome'];
    }

    ?>

    <form id="application_form" name="application_form" method="post" action="#">
        <?php
        $page->create_textbox_hidden("step_corrente", $step_corrente);
        $page->create_textbox_hidden("step_successivo", 0);
        $page->create_textbox_hidden("action", "create");
        ?>

        <div class="brain_formModifica formGestoreEdita" >
            <h2><span class="brain_colorh2"><?= $dizionario['proviggione']['def_prezzi'] ?></span></h2>

            
            
            <div class="accordion">
                <a style="cursor:pointer;"><u><i class="fa fa-plus" aria-hidden="true"></i> <?= $dizionario['proviggione']['aggiornamento_massa'] ?></u></a>
                
                <div class="accordion-content" style="display: none;">
                    <p style="padding-bottom: 0px"><?= $dizionario['proviggione']['aggiornamento_massa_descrizione'] ?></p>
                        <div style="width: 500px; background-color: #E4E4E4;">
                            <?php
                            $page->create_select($dizionario['proviggione']['proviggione'], "massProvvigioneId", "massProvvigioneId", "brain_campoForm", $selectProvvgione, -1, "ProvvigioneId", "Provvigione", null, 0);
                            $page->create_select($dizionario['generale']['linea'], "massLineaId", "massLineaId", "brain_campoForm", $selectLinea, -1, "LineaId", "Linea", null, 0);
                            $page->create_textbox($dizionario['proviggione']['perc'],"massPercentuale","massPercentuale", 0, 1, "brain_campoForm", array("class"=>"'numberDE'"), "", "10");
                            $page->create_textbox($dizionario['proviggione']['fisso'],"massFisso","massFisso",0, 1, "brain_campoForm", array("class"=>"'numberDE'"), "", "10");
                            $page->create_button($dizionario['proviggione']['aggiorna'],"massAggiorna",$dizionario['proviggione']['aggiorna'],"brain_salva","button");
                            print("<br style=\"clear:both;\"/>"); 
                            ?>
                        </div>
                    
                </div>
            </div>


            <?php
            // ciclo per linea
            $ii = 0;
            while ($ii < sizeof($ArrObjectLinea)) {

                $LineaNome = $ArrObjectLinea[$ii]['LineaNome'];
                $LineaId = $ArrObjectLinea[$ii]['LineaId'];
                ?>
                <h3><?= $LineaNome ?></h3>

                <table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                    <tbody>
                    <tr class="rowIntestazione">
                        <td></td>

                        <?php
                        
                        $i = 0;
                        while ($i < sizeof($ArrObjectTB)) {
                            $TBId = $ArrObjectTB[$i]['TipologiaBigliettoId'];
                            $TBNome = $ArrObjectTB[$i]['TipologiaBiglietto'];
                            ?>
                            <td><?= $TBNome ?> </td>

                            <?php
                            $i++;
                        }
                        ?>
                    </tr>
                    <?php
                   
                    $i = 0;
                    $tratta_old = 0;
                    while ($i < sizeof($ArrObject)) {
                        $ClasseId = $ArrObject[$i]['ProvvigioneId'];
                        $ClasseNome = $ArrObject[$i]['ProvvigioneNome'];
                        ?>

                        <tr>
                            <td><?= $ClasseNome ?></td>
                            <?php
                            $n = 0;
                            while ($n < sizeof($ArrObjectTB)) {
                                $BigliettoId = $ArrObjectTB[$n]['TipologiaBigliettoId'];
                                $fisso = "";
                                $percentuale = "";
                                $sql = "Select * from RT_ProvvigioneBiglietto where LineaId=$LineaId and BigliettoId=$BigliettoId and ProvvigioneId=$ClasseId and OdcIdRef=$user->OdcId";
                                $row1 = $db->query_first($sql);

                                if (!empty($row1['ProvvigioneBigliettoId'])) {
                                    $fisso = str_replace(".", ",", $row1['Fisso']);
                                    $percentuale = str_replace(".", ",", $row1['Percentuale']);
                                }
                                ?>

                                <td>
                                    <table>
                                        <tr>
                                            <td>
                                                <?= $dizionario['proviggione']['perc'] ?>
                                            </td>
                                            <td>
                                                <?= $dizionario['proviggione']['fisso'] ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input class="numberDE" type="text"
                                                       name="ProvvigioneBigliettoPerc[<?= $LineaId . "_" . $ClasseId . "_" . $BigliettoId ?>]"
                                                       value="<?= $percentuale ?>" SIZE="6" MAXLENGTH="6">
                                            </td>
                                            <td>
                                                <input class="numberDE" type="text"
                                                       name="ProvvigioneBigliettoFisso[<?= $LineaId . "_" . $ClasseId . "_" . $BigliettoId ?>]"
                                                       value="<?= $fisso ?>" SIZE="7" MAXLENGTH="7">
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <?php
                                $n++;
                            }
                            ?>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                    </tbody>
                </table>
                <?php
                $ii++;
            }
            ?>
        </div>
        <div class="divSubmit">
            <?php $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>
        </div>
    </form>

    <?php
    $db->Close();
}
function add_step_provvigione()
{
    $step_corrente = 2;

    global $provvigione_wizard, $user, $db, $dizionario;

    $page = new Form();
    $dt = new DT();

    $provvigione_wizard = $provvigione_wizard->Id;

    include_once("provvigione_validator.php");
    ?>

    <form id="application_form" name="application_form" method="post" action="#">
        <?php
        $page->create_textbox_hidden("step_corrente", $step_corrente);
        $page->create_textbox_hidden("step_successivo", $step_corrente + 1);
        ?>

        <div class="brain_formModifica formGestoreEdita">
            <h2><?= $dizionario['proviggione']['classi'] ?></h2>

            <br />
            <br />
            <div class="GestoreSedeAdd">
                <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_provvigione_classe','provvigione_classe.php?do=add&ProvvigioneId=<?= $ProvvigioneId ?>');" title="aggiungi tipo Provvigione"><i class="fa fa-plus" aria-hidden="true"></i> <?= $dizionario['proviggione']['aggiungi_classe'] ?></a>
            </div>

            <br />
            <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                <tbody>
                    <tr class="rowIntestazione">
                        <td><?= $dizionario['generale']['peso'] ?></td>
                        <td><?= $dizionario['proviggione']['proviggione'] ?></td>
                        <td><?= $dizionario['generale']['stato'] ?></td>
                        <td><?= $dizionario['generale']['esita'] ?></td>
                    </tr>

                    <?php
                    $sql = "Select * from RT_Provvigione where OdcIdRef=$user->OdcId order by ProvvigionePeso asc";

                    $ArrObject = $db->fetch_array($sql);
                    $i = 0;
                    while ($i < sizeof($ArrObject)) {
                        $ProvvigioneId = $ArrObject[$i]['ProvvigioneId'];
                        $ProvvigioneNome = $ArrObject[$i]['ProvvigioneNome'];
                        $ProvvigionePeso = $ArrObject[$i]['ProvvigionePeso'];
                        $ProvvigioneStato = $ArrObject[$i]['Stato'];
                        ?>
                        <tr class="rowBianca">
                            <td><span><?= $ProvvigionePeso ?></span></td>
                            <td><span><?= $ProvvigioneNome ?></span></td>
                            <td><span>
                                <?php
                                if ($ProvvigioneStato)
                                    print("attivo");
                                else
                                    print("disattivo");
                                ?>
                                </span></td>
                            <td><a title="edita" onclick="javascript:ExternalLoad('rt_provvigione_classe','provvigione_classe.php?do=edit&amp;ProvvigioneId=<?= $ProvvigioneId ?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
            <br />
            <div class="GestoreSedeAdd">
                <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_provvigione_classe','provvigione_classe.php?do=add&TrattaId=<?= $TrattaId ?>');" title="aggiungi tipo Provvigione"><i class="fa fa-plus" aria-hidden="true"></i> <?= $dizionario['proviggione']['aggiungi_tipo'] ?></a>
            </div>
        </div>
    </form>

    <?php
    $db->Close();
}

function add($step)
{
    include_once("provvigione_validator.php");

    global $HtmlCommon, $db, $provvigione_wizard, $funzione_edit, $abilita_modifica, $dizionario;

    if (!$step) {
        $provvigione_wizard = null;
        unset($provvigione_wizard);
        $_SESSION['PROVVIGIONE_WIZARD'] = null;
        unset($_SESSION['PROVVIGIONE_WIZARD']);
        $step = 1;
    }
    $mod = 0;
    $GestoreStato = -1;
    if (is_object($provvigione_wizard)) {
        $ProvvigioneId = $provvigione_wizard->Id;
        $provvigione_wizard->conn = $db;
        $provvigione_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr = $provvigione_wizard->DatiGenerali;
        $Stato = $DatiGeneraliArr['Stato'];
        $mod = 1;
        $abilita_modifica = true;
        $HtmlCommon->html_titolo_pagina($dizionario['proviggione']['titolo_provvigione_prezzi'] . " " . $DatiGeneraliArr['ProvvigioneNome'], 0, "rt_Provvigione", "Provvigione.php");
    } else {
        $mod = 0;
        $abilita_modifica = false;
        $HtmlCommon->html_titolo_pagina($dizionario['proviggione']['titolo_provvigione_prezzi'], 0, "rt_Provvigione", "Provvigione.php");
    }

    carica_menu_provvigioni($step, $mod);
    ?>

    <div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
    <?php
    if ($step == 1)
        add_step_provvigione();
    elseif ($step == 2)
        add_step_provvigione_prezzi();

    $db = new Database();
    $db->connect();
    ?>
    </div>

    <?php if ($GestoreStato >= 0) { ?>
        <div class="brain_stato-mediazione">
            <h3>Stato gestore</h3>
            <form id="CambiaStatoMediazioneId">
                <input type="hidden" name="action" value="cambia_stato_gestore" />
                <?php if ($GestoreStato) { ?>
                    <i class="fa fa-check-circle green" aria-hidden="true"></i> <?= $dizionario['generale']['attivo'] ?>
                    <p><?= $dizionario['listino']['attivo_desc'] ?></p>
                <?php } ?>
                <?php if ($GestoreStato == 0) { ?>
                    <i class="fa fa-times-circle red" aria-hidden="true"></i> <?= $dizionario['generale']['disattivo'] ?>
                    <p><?= $dizionario['listino']['disattivo_desc'] ?></p>
                <?php } ?>
                <div class="CambiaStatoMediazione">
                    <select name="Stato">
                        <option value="1" <?php if ($GestoreStato == 1) echo ("selected") ?>><?= $dizionario['generale']['attivo'] ?></option>
                        <option value="0" <?php if ($GestoreStato == 0) echo ("selected") ?>><?= $dizionario['generale']['disattivo'] ?></option>
                    </select>
                </div>
                <div class="CambiaStatoMediazioneSubmit">
                    <input class="brain_CambiaStato" type="submit" name="CambiaStato" value="Cambia" />
                </div>
            </form>
        </div>
    <?php
    }
}

function spara_pulsanti_wizard_box()
{
    global $dizionario;
    $page = new Form();
    ?>
    <div class="divSubmit">
        <?php $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>
        <a href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi" class="brain_annulla"><?= $dizionario['generale']['chiudi'] ?></a>
        <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select>
    </div>
    <?php
}

function spara_pulsanti_wizard($steptogo)
{
    global $funzione_edit, $dizionario;

    if ($funzione_edit) {
        spara_pulsanti_edit($steptogo);
    } else {
        $page = new Form();
        ?>
        <div class="divSubmit">
            <?php $page->create_button("Salva", "Salva", $dizionario['generale']['avanti'], "brain_salva", "submit"); ?>
            <?php if ($steptogo > 0) $page->create_button("indietro", "indietro", $dizionario['generale']['indietro'], "brain_back", "button"); ?>
            <a href="javascript:void(0);" onclick="loadMainContent('mediatore', 'mediatore.php', this);" title="Home" class="brain_annulla"><?= $dizionario['generale']['annulla'] ?></a>
            <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select>
        </div>
        <?php
    }
}

function spara_pulsanti_edit($steptogo)
{
    global $abilita_modifica, $dizionario;

    $page = new Form();
    ?>
    <div class="divSubmit">
        <?php $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>
        <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla"><?= $dizionario['generale']['annulla'] ?></a>
        <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select>
    </div>
    <?php
}

if (is_object($user)) {
    /* ID - FUNZIONE
    1   Lista
    2   Aggiunta
    3   Cancellazione
    4   Modifica
    5   Esportazione
    6   Importazione
    7   Stampa
    */

    $db = new Database();
    $db->connect();
    $user->conn = $db;
    if (is_object($provvigione_wizard)) {
        $provvigione_wizard->conn = $db;
    }
    $permessi = $user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi) > 0) {
        if (!isset($_REQUEST['do'])) {
            $do = '';
        } else {
            $do = $_REQUEST['do'];
        }

        switch ($do) {
            case "add":
                $FunzioneId = 2;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso)) {
                    add($_REQUEST['step']);
                } else {
                    $errore->stampa_errore(2);
                }
                break;

            case "edit":
                $FunzioneId = 1;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso)) {
                    edit($_REQUEST['ProvvigioneId']);
                } else {
                    $errore->stampa_errore(2);
                }
                break;

            case "cerca":
                $FunzioneId = 1;
                $permesso = $user->ControllModuloFunzionePermesso(2, $FunzioneId);
                if (sizeof($permesso)) {
                    cerca_mediatore();
                } else {
                    $errore->stampa_errore(2);
                }
                break;

            default:
                $FunzioneId = 1;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso)) {
                    show_list();
                } else {
                    $errore->stampa_errore(2);
                }
                break;
        }
    } else {
        $errore->stampa_errore(1);
    }
} else {
    header("Location: /logout.php");
}
?>