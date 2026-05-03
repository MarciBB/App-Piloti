<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Mediatore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");


global $ModuloId;
$ModuloId=16;// modulo base mediazione
global $user;
global $gestore_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$gestore_wizard=null;

if(isset($_SESSION['GESTORE_WIZARD'])) {
	$gestore_wizard=unserialize($_SESSION['GESTORE_WIZARD']);
}


function show_list()
{
    global $user, $HtmlCommon, $ModuloId, $dizionario;

    // Imposta il titolo della pagina
    $HtmlCommon->html_titolo_pagina($dizionario['gestore']['titolo_elenco'], 0, "", "");
    $HtmlCommon->html_titolo_box($dizionario['gestore']['titolo_elenco']);

    // Connessione al database
    $db = new Database();
    $db->connect();

    // Includi il datatable
    include_once("gestore_datatable.php");

    global $user, $HtmlCommon, $db, $ModuloId;

    // Controllo dei permessi per l'aggiunta
    $aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
    if (sizeof($aggiungi)) {
        $HtmlCommon->html_tasto_lista('brain_aggiungi est', 'gestore', 'gestore.php?do=add', $dizionario['gestore']['aggiungi']);
    }
    ?>

    <table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
        <thead>
            <tr class="brain_tabellaTr">
                <th width="5%"><?=$dizionario['generale']['stato']?></th>
                <th width="35%"><?=$dizionario['gestore']['ragione_sociale']?></th>
                <th width="12%"><?=$dizionario['gestore']['partita_iva']?></th>
                <th width="20%"><?=$dizionario['generale']['indirizzo']?></th>
                <th width="15%"><?=$dizionario['generale']['comune']?></th>
                <th width="20%"><?=$dizionario['generale']['telefono']?></th>
                <th width="20%"><?=$dizionario['generale']['codice_azienda']?></th>
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
                <td colspan="8" class="dataTables_empty">
                    <i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br>
                    <?=$dizionario['generale']['caricamento_in_corso']?>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8"></td>
            </tr>
        </tfoot>
    </table>
    <?php
}



function edit($GestoreId) {
    global $gestore_wizard,$db,$user;   
    $gestore_wizard=new Gestore($GestoreId);
  
    $_SESSION['GESTORE_WIZARD']=serialize($gestore_wizard);
    add(1);
}


function carica_menu_gestore($step_corrente, $mod)
{
    global $abilita_modifica, $gestore_wizard, $db, $dizionario;

    $gestore_wizard->conn = $db;

    // Definizione del menu
    $menu = array(
        1 => $dizionario['generale']['agenzia'],
        2 => $dizionario['generale']['rivendite'],
        3 => $dizionario['generale']['convenzioni']
    );
    ?>

    <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
        <ul>
            <?php
            $contamenu = 1;
            while ($contamenu <= 3) {
                $class1 = "";
                $class2 = "";

                // Determina le classi CSS per il menu selezionato
                if ($contamenu == $step_corrente) {
                    $class1 = "sel";
                    $class2 = "brain_firstspan sel";
                }

                $StatoStep = "";

                // Condizioni per mostrare il menu
                if (($contamenu <= 3) || (($contamenu > 3) && ($mod))) { ?>
                    <li class="<?=$class1?>">
                        <span class="<?=$class2?>">
                            <?php 
                            // Controlla la modalità per abilitare il link
                            if ($mod) { ?>
                                <a href="javascript:void(0);" 
                                   onclick="loadMediazioneStep('gestore', 'gestore.php?do=add&step=<?=$contamenu?>', this);" 
                                   title="<?=$menu[$contamenu]?>">
                                    <?=$menu[$contamenu] . " " . $StatoStep?>
                                </a>
                            <?php 
                            } else {
                                echo($menu[$contamenu]);
                            } ?>
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


function add_step_gestore() {
    $step_corrente=1;
 
    global $gestore_wizard,$user,$db, $dizionario;

    $page=new Form();
    $dt=new DT();
    
    $azione="add"; 
    $action="create"; 
        
    if (is_object($gestore_wizard) and ($gestore_wizard->GestoreId)) {
  
        $GestoreId=$gestore_wizard->GestoreId;
    
        $gestore_wizard->conn=$db;
        $gestore_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr = $gestore_wizard->GestoreDatiGenerali;
        $_SESSION['GESTORE_WIZARD']=serialize($gestore_wizard);
        
        if($DatiGeneraliArr['GestoreId']) {
            $azione="edit";     
            $action="update";
            $codiceAzienda = $DatiGeneraliArr['CodiceAzienda'];
        }
    }    

    include_once("gestore_validator.php");
    ?>

	<form id="application_form" name="application_form"  method="post" action="#">
        <?php
           $page->create_textbox_hidden("action",$action);
           $page->create_textbox_hidden("step_corrente",$step_corrente);
           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
        ?>
        
		<div class="brain_formModifica">
			<?php if ($action=="create") { ?>
				<h2>Dati generali</h2>
			<?php } else { ?>
				<h2><span class="brain_colorh2"><?=$gestore_wizard->GestoreDatiGenerali['RagioneSociale']?> - </span><?=$dizionario['gestore']['gestore']?></h2>
                <b><?= $dizionario['generale']['codice_azienda'] ?>: <?=$codiceAzienda?></b> 
            <?php } ?>
			<div class="brain_data-content">                  
            	<?php 
                form_tipo1($azione, $GestoreId);
                ?>
    			<br style="clear:both;"/>                                 
    		</div>
    	</div>
		<? spara_pulsanti_wizard(0) ?>
	</form>
	<?php
    $db->Close();   
}


function form_tipo1($azione,$AnagraficaId) {
    global $HtmlCommon,$db,$user,$gestore_wizard, $dizionario;    
    $page=new Form();
    $dt=new DT();

    $RagioneSociale="";
    $CodiceFiscale="";
    $PartitaIva="";
    $Indirizzo="";
    $Cap="";
    $NazioneResidenzaId=1;
    $ComuneResidenzaId=0;
    $IndirizzoResidenza="";
    $CapResidenza="";
    
    $Telefono="";
    $Fax="";
    $Email="";
    $EmailPec="";
    
    $Termini = 1;
    $TrattamentoDati = 1;
    $Comunicazioni = 1;
    
    $GestorePrimario = 0;
	$GestoreGruppoId = 2;
    
    $arr_regione_residenza=array();
    $arr_comune_residenza=array();
    $nazione_residenza=new Nazione(null);
    $nazione_residenza->conn=$db;
    $nazione_residenza->getAllNazione();
    $arr_nazione_residenza=$nazione_residenza->ArrNazione;
   
    $gestore=new Gestore();
    $gestore->conn=$db;
    $gestore->getGestoreAll($user->GestoreId);
    $arr_gestore=$gestore->ArrGestore;
    $arr_gruppo_gestore=$gestore->getGruppiGestori();

    $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattivo']);
    $arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

    $arr_verificato[]= array("VerificatoId" => '0',"Verificato" => $dizionario['generale']['no']);
    $arr_verificato[]= array("VerificatoId" => '1',"Verificato" => $dizionario['generale']['si']);
    $GestorePadre=1;
    if ($azione=="edit") {    
    
        $DatiGeneraliArr=$gestore_wizard->GestoreDatiGenerali;
        $RagioneSociale=$DatiGeneraliArr['RagioneSociale'];
        $CodiceFiscale=$DatiGeneraliArr['CodiceFiscale'];
        $PartitaIva=$DatiGeneraliArr['PartitaIva'];
        $Telefono=$DatiGeneraliArr['Telefono'];
        $Fax=$DatiGeneraliArr['Fax'];
        $Email=$DatiGeneraliArr['Email'];
        $EmailPec=$DatiGeneraliArr['EmailPec'];
        $GestorePadre=$DatiGeneraliArr['GestorePadre'];
        
        $IndirizzoResidenza=$DatiGeneraliArr['Indirizzo'];
        $CapResidenza=$DatiGeneraliArr['Cap'];
        $ComuneResidenza=$DatiGeneraliArr['Comune'];
        $ComuneResidenzaId=$DatiGeneraliArr['ComuneId'];
        $NazioneResidenzaId=$DatiGeneraliArr['NazioneId'];
        $GestoreStato=$DatiGeneraliArr['Stato'];
        $GestoreVerificato=$DatiGeneraliArr['Verificato'];
        $GestorePrimario=$DatiGeneraliArr['GestorePrimario'];
        $GestoreGruppoId=$DatiGeneraliArr['GestoreGruppoId'];
        
        $Termini = $DatiGeneraliArr['Termini'];
        $TrattamentoDati = $DatiGeneraliArr['TrattamentoDati'];
        $Comunicazioni = $DatiGeneraliArr['Comunicazioni'];
    }
    
    $arr_sino[]= array("SiNoId" => '0',"SiNo" => $dizionario['generale']['no']);
    $arr_sino[]= array("SiNoId" => '1',"SiNo" => $dizionario['generale']['si']);
    
    print("<h3>Anagrafica</h3>"); 
    
    $page->create_select($dizionario['gestore']['agenzia_padre'],"GestorePadreId","GestorePadreId","brain_campiform",$arr_gestore,$GestorePadre,"GestoreId","RagioneSociale",
        array("class"=>"'required'"),0); 

    $page->create_select($dizionario['gestore']['gestore_primario'],"Gestore[GestorePrimario]","GestorePrimarioId","brain_campiform",$arr_sino,$GestorePrimario,"SiNoId","SiNo",
            array("class"=>"'required'"),1);
 
	$page->create_textbox_hidden("Gestore[GestoreGruppoId]", $GestoreGruppoId);
          
    print("<br style=\"clear:both;\"/>");
    print("<br style=\"clear:both;\"/>");
    
    $page->create_textbox($dizionario['gestore']['ragione_sociale'],"RagioneSociale","Gestore[RagioneSociale]",$RagioneSociale,1,"brain_campiform",array("class"=>"'required'"));
    print(" <br style=\"clear:both;\"/>");
    $page->create_textbox($dizionario['gestore']['partita_iva'],"PartitaIva","Gestore[PartitaIva]",$PartitaIva,1,"brain_campiform",array("class"=>"'required'"),"","","16");
   
    $page->create_textbox($dizionario['generale']['codice_fiscale'],"CodiceFiscale","Gestore[CodiceFiscale]",$CodiceFiscale,1,"brain_campiform",array("class"=>"'required'"),"","","16");
    print("<br style=\"clear:both;\"/>");
    
    print("<h3>Sede</h3>"); 
    $page->create_textbox($dizionario['generale']['indirizzo'],"Indirizzo","Gestore[Indirizzo]",$IndirizzoResidenza,1,"brain_campiform ",array("class"=>"'required'"),"","35");
    $page->create_textbox($dizionario['comune']['cap'],"Cap","Gestore[Cap]",$CapResidenza,1,"brain_campiform",array("class"=>"'required digits'"),"","5","5");
    print(" <br style=\"clear:both;\"/>");
    
    $page->create_select($dizionario['generale']['stato'],"StatoResidenzaId","StatoResidenzaId","brain_campiform",$arr_nazione_residenza,$NazioneResidenzaId,"NazioneId","Nazione",
    array("class"=>"'required'"),1);        
    
    $page->create_textbox_hidden_differentId("ComuneResidenzaId","Gestore[ComuneId]",$ComuneResidenzaId);
        
    $selector="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"CaricaElencoComuni($(this),'comune','comune.php?do=cerca&fieldtoupdate=ComuneResidenzaId&labeltoupdate=ComuneResidenza','top');\"><img src=\"/images/arrow_add.png\" alt=\"Seleziona comune\" border=\"0\" /></a>";
    $selector_ann="<a id=\"ComuneResidenzaIdSeleziona\" href=\"javascript:void(0);\" class=\"flyselection\" onclick=\"$('#ComuneResidenzaId').val('0');$('#ComuneResidenza').val('');\" style=\"margin-left:2px\"><img src=\"/images/iconset/delete_gray.gif\" alt=\"cancella campo\"  border=\"0\" /></a>";
    $selector.=$selector_ann; 
    $page->create_textbox_with_sel($dizionario['generale']['comune'],"ComuneResidenza","ComuneResidenza",$ComuneResidenza,1,"brain_campiform",array("class"=>"'required'","readonly"=>"'readonly'"),$selector);
    
    ?>
  
	<div id="elenco_comuni"></div>
    <?php
    print("<br style=\"clear:both;\"/>");

    print("<br style=\"clear:both;\"/>");
    print("<h3>".$dizionario['generale']['recapiti']."</h3>"); 
    $page->create_textbox($dizionario['generale']['telefono'],"Telefono","Gestore[Telefono]",$Telefono,0,"brain_campiform","");
    $page->create_textbox($dizionario['generale']['fax'],"Fax","Gestore[Fax]",$Fax,0,"brain_campiform",null);
    print("<br style=\"clear:both;\"/>");
    $page->create_textbox($dizionario['generale']['email'],"Email","Gestore[Email]",$Email,0,"brain_campiform campiformBig",array("class"=>"'email'"));
    $page->create_textbox($dizionario['generale']['emailpec'],"EmailPec","Gestore[EmailPec]",$EmailPec,0,"brain_campiform campiformBig",array("class"=>"'email'"));
    
    
    print("<br style=\"clear:both;\"/>");
    
    $page->create_select($dizionario['generale']['termini'],"Gestore[Termini]","PrivacyId","brain_campiform", $arr_sino, $Termini, "SiNoId", "SiNo",
        array("class"=>"'required'"),1);
    $page->create_select($dizionario['generale']['trattamento_dati'],"Gestore[TrattamentoDati]","PrivacyId","brain_campiform",$arr_sino,$TrattamentoDati,"SiNoId", "SiNo",
        array("class"=>"'required'"),1);
    $page->create_select($dizionario['generale']['comunicazioni'],"Gestore[Comunicazioni]","PrivacyId","brain_campiform",$arr_sino,$Comunicazioni,"SiNoId", "SiNo",
        array("class"=>"'required'"),1);
    
    print("<br style=\"clear:both;\"/>");print("<br style=\"clear:both;\"/>");
    
    $page->create_select($dizionario['generale']['stato'],"Gestore[Stato]","StatoId","brain_campiform campiformBig",$arr_stato,$GestoreStato,"StatoId","Stato",array("class"=>"'required'"),1);
    print("<br style=\"clear:both;\"/>");
    
    print("<h3>".$dizionario['gestore']['tipo_agenzia']."</h3>");
    print("<br style=\"clear:both;\"/>");
    $page->create_select($dizionario['gestore']['verificato'],"Gestore[Verificato]","VerificatoId","brain_campiform campiformBig", $arr_verificato, $GestoreVerificato, "VerificatoId", "Verificato",array("class"=>"'required'"),1);
    print("<br style=\"clear:both;\"/>");
 
}



function add_step_sedi_aule()
{
    $step_corrente = 2;

    global $gestore_wizard, $user, $db, $dizionario;

    $page = new Form();
    $dt = new DT();

    $GestoreId = $gestore_wizard->GestoreId;

    include_once("gestore_validator_stop.php");
    ?>

    <form id="application_form" name="application_form" method="post" action="#">
        <?php
        // Hidden fields for step navigation
        $page->create_textbox_hidden("step_corrente", $step_corrente);
        $page->create_textbox_hidden("step_successivo", $step_corrente + 1);
        ?>

        <div class="brain_formModifica formGestoreEdita">
            <h2>
                <span class="brain_colorh2"><?=$gestore_wizard->GestoreDatiGenerali['RagioneSociale']?> - </span>
                <?=$dizionario['gestore']['rivendite']?>
            </h2>
            <?php
            $sql = "SELECT * FROM ElencoSediView WHERE GestoreId = $GestoreId";
            $ArrObject = $db->fetch_array($sql);

            $i = 0;
            while ($i < sizeof($ArrObject)) {
                $SedeId = $ArrObject[$i]['SedeId'];
                $CodiceSede = $ArrObject[$i]['CodiceSede'];
                $Indirizzo = $ArrObject[$i]['Indirizzo'];
                $Comune = $ArrObject[$i]['Comune'];
                $Telefono = $ArrObject[$i]['Telefono'];
                $Fax = $ArrObject[$i]['Fax'];
                $Email = $ArrObject[$i]['Email'];
                $Stato = $ArrObject[$i]['Stato'];
                ?>

                <div class="brainGestoreSedi">
                    <div class="brain_colLeft">
                        <p>
                            <strong><?=$dizionario['gestore']['sede']?> <?=$CodiceSede?></strong><br />
                            <?php
                            echo $Indirizzo . " " . $Comune . "<br />";
                            echo "Tel.: " . $Telefono . "<br />";
                            echo "Fax.: " . $Fax . "<br />";
                            echo "Email.: " . $Email . "<br /><br />";
                            
                            if ($Stato) {
                                echo '<i class="fa fa-check-circle green" aria-hidden="true"></i>';
                            } else {
                                echo '<i class="fa fa-times-circle red" aria-hidden="true"></i>';
                            }
                            ?>
                        </p>
                        <div class="GestoreSedeModifica">
                            <a class="edita" href="#" onclick="javascript:ExternalLoad('sede', 'sede.php?do=edit&amp;SedeId=<?=$SedeId?>');" title="edita">
                                <?=$dizionario['generale']['modifica_up']?>
                            </a>
                        </div>
                    </div>
                </div>

                <?php
                echo '<br style="clear:both;" />';
                echo '<br style="clear:both;" />';
                $i++;
            }
            ?>

            <div class="GestoreSedeAdd">
                <a class="brain_add" href="#" onclick="javascript:ExternalLoad('sede', 'sede.php?do=add&GestoreId=<?=$GestoreId?>');" title="aggiungi rivendita">
                    <i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['gestore']['aggiungi_rivendita']?>
                </a>
            </div>
        </div>
    </form>

    <?php
    $db->Close();
}



function add_step_provvigione()
{
    $step_corrente = 2;

    global $gestore_wizard, $user, $db, $dizionario;

    $page = new Form();
    $dt = new DT();

    $GestoreId = $gestore_wizard->GestoreId;

    include_once("gestore_validator_stop.php");
    ?>

    <form id="application_form" name="application_form" method="post" action="#">
        <?php
        // Hidden fields for step navigation
        $page->create_textbox_hidden("step_corrente", $step_corrente);
        $page->create_textbox_hidden("step_successivo", $step_corrente + 1);
        ?>

        <div class="brain_formModifica formGestoreEdita">
            <h2>
                <span class="brain_colorh2"><?=$gestore_wizard->GestoreDatiGenerali['RagioneSociale']?> - </span>
                <?=$dizionario['gestore']['convenzioni']?>
            </h2>

            <div class="GestoreSedeAdd">
                <a class="brain_add" href="#" onclick="javascript:ExternalLoad('gestore_convenzione', 'gestore_convenzione.php?do=add&GestoreId=<?=$GestoreId?>');" title="aggiungi convenzione">
                    <i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['gestore']['aggiungi_convenzione']?>
                </a>
            </div>

            <br />

            <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                <thead>
                    <tr class="rowIntestazione">
                        <td><?=$dizionario['generale']['linea']?></td>
                        <td><?=$dizionario['gestore']['provvigione']?></td>
                        <td><?=$dizionario['generale']['dal']?></td>
                        <td><?=$dizionario['generale']['al']?></td>
                        <td><?=$dizionario['gestore']['solo_prenotazione']?></td>
                        <td><?=$dizionario['generale']['lista_attesa']?></td>
                        <td><?=$dizionario['generale']['stato']?></td>
                        <td><?=$dizionario['generale']['edita']?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM RT_ElencoGestoreProvvigione WHERE GestoreId = $GestoreId AND OdcIdRef = $user->OdcId";
                    $ArrObject = $db->fetch_array($sql);
                    $i = 0;

                    while ($i < sizeof($ArrObject)) {
                        $GestoreConvenzioneId = $ArrObject[$i]['GestoreConvenzioneId'];
                        $ProvvigioneNome = $ArrObject[$i]['ProvvigioneNome'];
                        $LineaNome = $ArrObject[$i]['LineaNome'];
                        $AttivaDal = $ArrObject[$i]['ValidaDal'];
                        $AttivaAl = $ArrObject[$i]['ValidaAl'];
                        $SoloPrenotazione = $ArrObject[$i]['SoloPrenotazione'];
                        $ListaAttesa = $ArrObject[$i]['ListaAttesa'];
                        $Stato = $ArrObject[$i]['Stato'];
                        ?>

                        <tr class="rowBianca">
                            <td><span><?=$LineaNome?></span></td>
                            <td><span><?=$ProvvigioneNome?></span></td>
                            <td><span><?=$AttivaDal?></span></td>
                            <td><span><?=$AttivaAl?></span></td>
                            <td><span>
                                <?php
                                echo $SoloPrenotazione ? $dizionario['generale']['si'] : $dizionario['generale']['no'];
                                ?>
                            </span></td>
                            <td><span>
                                <?php
                                echo $ListaAttesa ? $dizionario['generale']['si'] : $dizionario['generale']['no'];
                                ?>
                            </span></td>
                            <td><span>
                                <?php
                                echo $Stato ? $dizionario['generale']['attiva'] : $dizionario['generale']['disattiva'];
                                ?>
                            </span></td>
                            <td>
                                <a title="edita" onclick="javascript:ExternalLoad('gestore_convenzione', 'gestore_convenzione.php?do=edit&amp;GestoreConvenzioneId=<?=$GestoreConvenzioneId?>');" href="#">
                                    <i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i>
                                </a>
                            </td>
                        </tr>

                        <?php
                        $i++;
                    }
                    ?>
                </tbody>
            </table>

            <br />

            <div class="GestoreSedeAdd">
                <a class="brain_add" href="#" onclick="javascript:ExternalLoad('gestore_convenzione', 'gestore_convenzione.php?do=add&GestoreId=<?=$GestoreId?>');" title="aggiungi convenzione">
                    <i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['gestore']['aggiungi_convenzione']?>
                </a>
            </div>
        </div>
    </form>

    <?php
    $db->Close();
}



function add($step) {
    include_once("gestore_validator.php");  
 
    global $HtmlCommon,$db,$gestore_wizard,$funzione_edit,$abilita_modifica, $dizionario;

    if (!$step) {
        $gestore_wizard=null;    
        unset($gestore_wizard);
        $_SESSION['GESTORE_WIZARD']=null;
        unset($_SESSION['GESTORE_WIZARD']);
        $step=1;
    }
    $mod=0;
    $GestoreStato=-1;
    if (is_object($gestore_wizard)) {
        $GestoreId=$gestore_wizard->GestoreId;
        $gestore_wizard->conn=$db;
        $gestore_wizard->inizializzaDatiGenerali($GestoreId);
        $DatiGeneraliArr=$gestore_wizard->GestoreDatiGenerali;
        $GestoreStato=$DatiGeneraliArr['Stato'];
        $mod=1;
        $abilita_modifica=true;
        $HtmlCommon->html_titolo_pagina($dizionario['gestore']['titolo_edita_agenzia']." ".$DatiGeneraliArr['RagioneSociale'],1,"gestore","gestore.php");
    } else {    
        $mod=0;
        $abilita_modifica=false;
        $HtmlCommon->html_titolo_pagina($dizionario['gestore']['titolo_aggiungi_agenzia'],1,"gestore","gestore.php");
    }
    
    carica_menu_gestore($step,$mod);
    ?>
    <div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
    <?php       
     
    if ($step==1)
        add_step_gestore();
    elseif ($step==2)
        add_step_sedi_aule();
    elseif ($step==3)
        add_step_provvigione();
    
    $db= new Database();
    $db->connect();
   
    ?>
    </div>
    
    <?php if ($GestoreStato>=100) { ?>
    
    	<div class="brain_stato-mediazione">
     	<h3><?=$dizionario['gestore']['stato_gestore']?></h3>
        	<form id="CambiaStatoMediazioneId">
            
            	<input type="hidden" name="action" value="cambia_stato_gestore" />
             
                <?php if ($GestoreStato){?><i class="fa fa-check-circle green" aria-hidden="true"></i><p><?=$dizionario['gestore']['mess_attivo']?></p><? } ?>
                <?php if ($GestoreStato==0){?><i class="fa fa-times-circle red" aria-hidden="true"></i><p><?=$dizionario['gestore']['mess_disattivo']?></p><? } ?>
                
             	<div class="CambiaStatoMediazione">
                    <select name="Stato">
                        <option value="1" <? if ($GestoreStato==1) echo ("selected")?> ><?=$dizionario['generale']['attivo']?></option>
                        <option value="0" <? if ($GestoreStato==0) echo ("selected")?> ><?=$dizionario['generale']['disattivo']?></option>
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


function spara_pulsanti_wizard($steptogo)
{
    global $funzione_edit, $dizionario;

    // Se la funzione è in modalità edit, invoca spara_pulsanti_edit
    if ($funzione_edit) {
        spara_pulsanti_edit($steptogo);
    } else {
        $page = new Form();
        ?>
        <div class="divSubmit">
            <?php
            // Crea il pulsante "Salva"
            $page->create_button("Salva", "Salva", $dizionario['generale']['avanti'], "brain_salva", "submit");

            // Se ci sono step da fare, crea il pulsante "Indietro"
            if ($steptogo > 0) {
                $page->create_button("indietro", "indietro", $dizionario['generale']['indietro'], "brain_back", "button");
            }
            ?>

            <!-- Link per annullare -->
            <a href="javascript:void(0);" onclick="loadMainContent('gestore','gestore.php',this);" title="Home" class="brain_annulla">
                <?=$dizionario['convenzione']['annulla']?>
            </a>

            <!-- Campo select hidden (presumibilmente per tracciamento) -->
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
        <!-- Pulsante "Salva" -->
        <?php
        $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit");
        ?>

        <!-- Link per annullare (torna alla home) -->
        <a href="javascript:void(0);" onclick="loadMainContent('mediazione', 'mediazione.php?step=2', this);" title="Home" class="brain_annulla">
            <?=$dizionario['generale']['annulla']?>
        </a>

        <!-- Campo select hidden (presumibilmente per tracciamento) -->
        <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select>
    </div>
    <?php
}




if (is_object($user)) {

    /*      ID - FUNZIONE
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

    if (is_object($gestore_wizard)) {
        $gestore_wizard->conn = $db;
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
                $FunzioneId = 4;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                
                if (sizeof($permesso)) {
                    edit($_REQUEST['GestoreId']);
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
