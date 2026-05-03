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
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."Graph/class.LineaGraph.php");


include_once($classespath_."class.Percorso.php");

global $ModuloId;
$ModuloId=28;
global $user;
global $percorso_wizard;

$percorso_wizard=null;

if(isset($_SESSION['PERCORSO_WIZARD'])) {
	$percorso_wizard=unserialize($_SESSION['PERCORSO_WIZARD']);
}

function show_list() {
   
    global $user, $HtmlCommon, $ModuloId, $dizionario;

    // Visualizza il titolo della pagina e della box
    $HtmlCommon->html_titolo_pagina($dizionario['percorso']['titolo_elenco'], 0, "", "");
    $HtmlCommon->html_titolo_box($dizionario['percorso']['titolo_elenco']);

    // Inizializza la connessione al database
    $db = new Database();
    $db->connect();

    // Includi i file necessari per la validazione e la gestione della datatable
    include_once("percorso_validator.php");           
    include_once("percorso_datatable.php");

    // Verifica i permessi per l'aggiunta di nuovi percorsi
    global $user, $HtmlCommon, $db, $ModuloId;    
    $aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);

    // Se l'utente ha i permessi di aggiunta, mostra il pulsante per aggiungere nuovi percorsi
    if (sizeof($aggiungi)) {
        $HtmlCommon->html_tasto_lista('brain_aggiungi est', 'rt_percorso', 'percorso.php?do=add', $dizionario['percorso']['aggiungi']);
    }

    ?>   

    <!-- Struttura della tabella per mostrare i percorsi -->
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
        <thead>
            <tr class="brain_tabellaTr">
                <th width="10%"><?=$dizionario['generale']['stato']?></th>
                <th width="90%"><?=$dizionario['percorso']['percorso']?></th>
                <th width="5%"><?=$dizionario['generale']['peso']?></th>
                <th width="5%"><?=$dizionario['generale']['edita']?></th>
            </tr>
            
            <!-- Filtri per la tabella, con campi di input per la ricerca -->
            <tr class="brain_tabellaFilter">
                <th><input type="text" /></th> 
                <th><input type="text" /></th> 
                <th><input type="text" /></th> 
                <th><input type="hidden" /></th> 
            </tr>
        </thead>

        <tbody>
            <!-- Riga di caricamento in corso quando non ci sono dati disponibili -->
            <tr>
                <td colspan="4" class="dataTables_empty">
                    <i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br>
                    <?=$dizionario['generale']['caricamento_in_corso']?>
                </td>
            </tr>
        </tbody>

        <tfoot> 
            <tr> 
                <td colspan="4"></td>
            </tr> 
        </tfoot> 
    </table>
    
    <?php   

    // Chiudi la connessione al database
    $db->close();
}



function edit($PercorsoId)
{
    
    global $percorso_wizard,$db,$user;   
    $percorso_wizard=new Percorso($PercorsoId);
  
    
    $_SESSION['PERCORSO_WIZARD']=serialize($percorso_wizard);
    add(1);
}


function carica_menu_percorso($step_corrente,$mod) {
	global $percorso_wizard, $db, $dizionario;

	$menu=array(
	    1=>$dizionario['percorso']['menu_percorso'],
	    2=>$dizionario['percorso']['menu_linee']
    );
    ?>
	<div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
		<ul>
		<?php
			$contamenu=1;
			while ($contamenu<=2) {
				$class1="";
				$class2="";
                          
				if ($contamenu==$step_corrente) {
					$class1="sel";
					$class2="brain_firstspan sel";
				}
                             
				$StatoStep="";
                            
				if ( ($contamenu<=2) or (($contamenu>2) and ($mod))) { ?>
                            
					<li class="<?=$class1?>">
						<span class="<?=$class2?>">
							<?php if ($mod) { ?>
								<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_percorso','percorso.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
							<?php
							} else
								echo($menu[$contamenu]);
							?>     
						</span>
					</li>
				<?php }
				$contamenu++;
				}
			?>
		</ul>
	</div>
	<?php  
}

function add_step_percorso() {
    $step_corrente = 1; // Imposta il primo step del percorso

    global $percorso_wizard, $user, $db, $dizionario;

    // Inizializzazione delle variabili per la pagina e per le azioni del form
    $page = new Form();
    $dt = new DT();
    $azione = "add"; 
    $action = "create"; 
    $PercorsoId = 0;

    // Verifica se esiste un oggetto percorso_wizard con un ID
    if (is_object($percorso_wizard) && $percorso_wizard->Id) {
        $PercorsoId = $percorso_wizard->Id; // Assegna l'ID del percorso

        // Collega il percorso_wizard al database e inizializza i dati generali
        $percorso_wizard->conn = $db;
        $percorso_wizard->inizializzaDatiGenerali();
        
        // Recupera i dati generali del percorso e li salva in sessione
        $DatiGeneraliArr = $percorso_wizard->DatiGenerali;
        $_SESSION['PERCORSO_WIZARD'] = serialize($percorso_wizard);
        
        // Se è stato impostato un PercorsoId nei dati generali, cambia azione in "edit"
        if ($DatiGeneraliArr['PercorsoId']) {
            $azione = "edit";     
            $action = "update"; 
        }
    }

    // Include il file per la validazione del percorso
    include_once("percorso_validator.php");
    ?>

    <!-- Inizio del modulo per il percorso -->
    <form id="application_form" name="application_form" method="post" action="#">
        <?php
            $page->create_textbox_hidden("action", $action);
            $page->create_textbox_hidden("step_corrente", $step_corrente);
            $page->create_textbox_hidden("step_successivo", $step_corrente + 1);
        ?>

        <div class="brain_formModifica">
            <?php if ($action == "create") { ?>
                <h2><?=$dizionario['percorso']['info_generali']?></h2>
            <?php } else { ?>
                <h2><span class="brain_colorh2"><?=$percorso_wizard->DatiGenerali['PercorsoNome']?></span></h2>
            <?php } ?>
            
            <div class="brain_data-content">                  
                <?php
                    // Richiama la funzione per creare il form personalizzato per il tipo di azione
                    form_tipo1($azione, $PercorsoId);
                ?>
                
                <br style="clear:both;"/>                                 
            </div>
        </div>
        
        <!-- Pulsanti per la navigazione del wizard -->
        <?php spara_pulsanti_wizard(0); ?>
    </form>

    <?php
    // Chiudi la connessione al database
    $db->Close();   
}



function form_tipo1($azione, $AnagraficaId) {
    global $HtmlCommon, $db, $user, $percorso_wizard, $dizionario;
    
    // Inizializza oggetti necessari per il form e il datatable
    $page = new Form();
    $dt = new DT();

    // Imposta i valori di default per il percorso
    $PercorsoNome = "";
    $PercorsoPeso = "";
	$Identificativo = "";
    $PercorsoStato = 0;

    // Definisce le opzioni per il campo "Stato" utilizzando i valori dal dizionario
    $arr_stato[] = array("StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']);
    $arr_stato[] = array("StatoId" => '1', "Stato" => $dizionario['generale']['attivo']);

    // Se l'azione è "edit", carica i dati generali esistenti dal percorso_wizard
    if ($azione == "edit") {
        $DatiGeneraliArr = $percorso_wizard->DatiGenerali;
        $PercorsoNome = $DatiGeneraliArr['PercorsoNome'];
        $PercorsoPeso = $DatiGeneraliArr['PercorsoPeso'];
        $PercorsoStato = $DatiGeneraliArr['Stato'];
        $Identificativo = $DatiGeneraliArr['Identificativo'];
    }

    // Crea il campo di testo per il nome del percorso
    $page->create_textbox(
        $dizionario['percorso']['nome'], // Etichetta
        "PercorsoNome", // ID campo
        "Percorso[PercorsoNome]", // Nome campo
        $PercorsoNome, // Valore di default
        1, // Campo obbligatorio
        "brain_campiform campiformBig", // Classe CSS
        array("class" => "'required'")
    );
    print("<br style=\"clear:both;\"/>");

    // Crea il campo di testo per l'identificativo del percorso
    $page->create_textbox(
        $dizionario['percorso']['identificativo'],
        "Identificativo",
        "Percorso[Identificativo]",
        $Identificativo,
        1,
        "brain_campiform",
        array("class" => "'required'"),
		"<br />", "20", "2"
    );
    print("<br style=\"clear:both;\"/>");

    // Crea il campo di testo per il peso del percorso
    $page->create_textbox(
        $dizionario['generale']['peso'],
        "Peso",
        "Percorso[PercorsoPeso]",
        $PercorsoPeso,
        1,
        "brain_campiform",
        array("class" => "'required digits'"),
        "",
        "2",
        "2"
    );
    print("<br style=\"clear:both;\"/>");

    // Crea il campo di selezione per lo stato del percorso
    $page->create_select(
        $dizionario['generale']['stato'],
        "Percorso[Stato]",
        "StatoId",
        "brain_campiform campiformBig",
        $arr_stato,
        $PercorsoStato,
        "StatoId",
        "Stato",
        array("class" => "'required'"),
        1
    );
    print("<br style=\"clear:both;\"/>");

    ?>

    <!-- Contenitore per l'elenco dei comuni, se necessario -->
    <div id="elenco_comuni"></div>
    
    <?php
    print("<br style=\"clear:both;\"/>");
}

function add_step_linee_tratte() {

	$step_corrente=2;
	global $percorso_wizard,$user,$db, $dizionario;
	
	$page=new Form();
	$dt=new DT();

	$PercorsoId=$percorso_wizard->Id;
	$percorso_wizard->conn=$db;
	$percorso_wizard->inizializzaDatiGenerali();
	
	if(isset($_GET['disattivaTratta'])) {
		if($_GET['disattivaTratta'] == 'false'){
			$disattivaTratta = false;
		} else {
			$disattivaTratta = true;
		}
	} else {
		$disattivaTratta = true;
	}

	if(isset($_GET['disattivaCorsa'])) {
		if($_GET['disattivaCorsa'] == 'false'){
			$disattivaCorsa = false;
		} else {
			$disattivaCorsa = true;
		}
	} else {
		$disattivaCorsa = true;
	}

	include_once("percorso_validator_stop.php");
	?>
	<form id="application_form" name="application_form"  method="post" action="#">
    <?php
    	$page->create_textbox_hidden("step_corrente",$step_corrente);
    	$page->create_textbox_hidden("step_successivo",$step_corrente+1);
	?>              
		<div class="brain_formModifica formGestoreEdita">
        <h2><span class="brain_colorh2"><?=$percorso_wizard->DatiGenerali['PercorsoNome']?> - </span><?=$dizionario['percorso']['linee_tratte']?></h2>
        <?php               
        $sql="Select * from RT_Linea where PercorsoId=$PercorsoId and LineaId <> 14 and OdcIdRef=$user->OdcId and Cancella = 0 order by LineaPeso asc";
		$ArrObject = $db->fetch_array($sql);
		$i=0;
		while ($i< sizeof($ArrObject)) {
			$LineaId = $ArrObject[$i]['LineaId'];
			$LineaNome = $ArrObject[$i]['LineaNome'];
			$LineaArea = $ArrObject[$i]['LineaArea'];
			$LineaTipoTour = $ArrObject[$i]['TipoTour'];
			$LineaStato = $ArrObject[$i]['Stato'];
			?>
			<h2><strong><?=$LineaNome?></strong></h2>
			<div class="brainGestoreSedi">
                                 
			<div class="brain_colLeft">
            	<p><strong>Linea <?=$LineaNome?></strong><br />
                <?php
                print($LineaArea);
				if($LineaTipoTour == 0) {
					echo "<br /><span style='font-size: 10px;'><b>".$dizionario['generale']['tour_gruppo']."</b></span>";
				} else {
					echo "<br /><span style='font-size: 10px;'><b>".$dizionario['generale']['tour_privato']."</b></span>";
				}
                
				print("<br />");
				print("<br />");
                                    
				if ($LineaStato)
				    print("<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"".$dizionario['generale']['attiva']."\"></i>");
				else
				    print("<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"".$dizionario['generale']['disattiva']."\"></i>");    
                                     
				print("</p><br />");
                                     
				?>
                <div class="GestoreSedeModifica">
					<a class="edita" href="#" onclick="javascript:ExternalLoad('rt_linea','linea.php?do=edit&amp;LineaId=<?=$LineaId?>');" title="<?=$dizionario['generale']['modifica_up']?>" style="padding: 16px 0px;"><?=$dizionario['generale']['modifica_up']?></a>
				</div>
				<div class="GestoreSedeModifica">
					<a class="edita" href="#" onclick="javascript:ExternalLoad('rt_percorso','percorso.php?do=tariffe&amp;LineaId=<?=$LineaId?>');" title="<?=$dizionario['timetable']['menu_tariffe']?>" style="padding: 16px 0px;"><?=$dizionario['timetable']['menu_tariffe']?></a>
				</div>
				<div class="GestoreSedeModifica">
					<a class="edita cancella" href="#" onclick="javascript:confermaCancella(<?=$LineaId?>);" title="<?=$dizionario['generale']['rimuovi']?>" style="padding: 16px 0px;"><?=$dizionario['generale']['rimuovi']?></a>
				</div>
				<!--
				<div class="GestoreSedeModifica">
					<a class="edita" href="#" onclick="javascript:ExternalLoad('rt_percorso','percorso.php?do=tariffeComune&amp;LineaId=<?=$LineaId?>');" title="tariffe">Tariffe Comune</a>
				</div>
				-->
			</div>
            <div class="brain_colRight">
				<p>
					<strong><?=$dizionario['percorso']['elenco_tratte']?></strong> - <a onclick="javascript:ExternalLoad('rt_tratta','tratta.php?do=add&LineaId=<?=$LineaId?>');" href="javascript:void(0);" class="brain_add"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['percorso']['aggiungi_tratta']?></a>
					&nbsp&nbsp&nbsp
					<?php if($disattivaTratta) { ?>
						<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_percorso','percorso.php?do=add&step=2&disattivaCorsa=<?php echo $disattivaCorsa?>&disattivaTratta=false',this);" >Visualizza tutte le tratte</a>
					<?php } else { ?> 
						<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_percorso','percorso.php?do=add&step=2&disattivaCorsa=<?php echo $disattivaCorsa?>&disattivaTratta=true',this);" >Non visualizzare tratte disattivate</a>
					<?php } ?>
					 
				<br/>
				
				<table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
					<tbody>
						<tr class="rowIntestazione">
							<td><?=$dizionario['generale']['peso']?></td>
							<td><?=$dizionario['generale']['tratta']?></td>
							<td><?=$dizionario['generale']['durata']?></td>
							<td><?=$dizionario['generale']['tipo']?></td>
							<td><?=$dizionario['percorso']['mezzo']?></td>
							<td><?=$dizionario['generale']['stato']?></td>
							<td><?=$dizionario['generale']['edita']?></td>
						</tr>
						<?
                        $sql = "Select * from RT_ElencoTratta where LineaId=$LineaId and OdcIdRef=$user->OdcId and cancella = 0";
                        if($disattivaTratta){
                        	$sql .= " and stato = 1 ";
                        }
                        $sql .= " order by NodoPeso,TrattaPeso";
						$ArrObject1 = $db->fetch_array($sql);
                                      
						$j=0;
						while ($j< sizeof($ArrObject1)) {
							$TrattaId=$ArrObject1[$j]['TrattaId'];
							$TrattaNome=$ArrObject1[$j]['TrattaNome'];
							$TrattaPeso=$ArrObject1[$j]['TrattaPeso'];
							$TrattaNodo=$ArrObject1[$j]['NodoPeso'];
							$TrattaTipo=$ArrObject1[$j]['AppTrattaTipo'];
							$TrattaDirezione=$ArrObject1[$j]['AppTrattaDirezione'];
							$TrattaMezzo=$ArrObject1[$j]['AppMezzo'];
							$TrattaStato=$ArrObject1[$j]['Stato'];
							$Km=$ArrObject1[$j]['KmTratta'];
							?>
							<tr class="rowBianca">
								<td><span><?=$TrattaPeso?></span></td>
								<td><span><?=$TrattaNome?></span></td>
								<td><span><?=$Km?></span></td>
                                         
								<td><span><?=$TrattaTipo?></span></td>
								<td><span><?=$TrattaMezzo?></span></td>
                                <td>
									<span>
									<?
									if ($TrattaStato)
										print($dizionario['generale']['attiva']);
									else
										print($dizionario['generale']['disattiva']);    
									?>
                                    </span> 
								</td>
								<td><a title="edita" onclick="javascript:ExternalLoad('rt_tratta','tratta.php?do=edit&amp;TrattaId=<?=$TrattaId?>&LineaId=<?=$LineaId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
							</tr>
							<?                                          
							$j++;    
						} 
						?>
						</tbody>
					</table>
					<br /><br />
                                     
					<p><strong><?=$dizionario['percorso']['elenco_corse']?></strong> - <a onclick="javascript:ExternalLoad('rt_corsa','corsa.php?do=add&LineaId=<?=$LineaId?>');" href="javascript:void(0);" class="brain_add"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['percorso']['aggiungi_corsa']?></a></p>
					&nbsp&nbsp&nbsp
					<?php if($disattivaCorsa) { ?>
						<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_percorso','percorso.php?do=add&step=2&disattivaCorsa=false&disattivaTratta=<?php echo $disattivaTratta?>',this);" >Visualizza tutte le corse</a>
					<?php } else { ?> 
						<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_percorso','percorso.php?do=add&step=2&disattivaCorsa=true&disattivaTratta=<?php echo $disattivaTratta?>',this);" >Non visualizzare le corse disattivate</a>
					<?php } ?>
					<br />

					<table cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule" >
						<tbody>
							<tr class="rowIntestazione">
								<td><?=$dizionario['generale']['peso']?></td>
								<td><?=$dizionario['generale']['corsa']?></td>
								<td><?=$dizionario['generale']['dal']?></td>
								<td><?=$dizionario['generale']['al']?></td>
								<td><?=$dizionario['percorso']['p']?></td>
								<td><?=$dizionario['percorso']['a']?></td>
								<td><?=$dizionario['generale']['stato']?></td>
								<td><?=$dizionario['generale']['edita']?></td>
								<td><?=$dizionario['percorso']['biglietti']?></td>
								<td><?=$dizionario['percorso']['ducplica']?></td>
							</tr>  
							<?
							$corsaObj = new Corsa();
							$corsaObj->conn = $db;
							$ArrObject1 = $corsaObj->elencoCorseNoObsolete($LineaId, $user->OdcId, $disattivaCorsa);

							$j=0;
							while ($j< sizeof($ArrObject1)) {
								$CorsaId=$ArrObject1[$j]['CorsaId'];
								$CorsaNome=$ArrObject1[$j]['CorsaNome'];
								$CorsaPeso=$ArrObject1[$j]['CorsaPeso'];
								$AttivaDal=$ArrObject1[$j]['AttivaDal'];
								$AttivaAl=$ArrObject1[$j]['AttivaAl'];
								$IncludiFeriale=$ArrObject1[$j]['IncludiFeriale'];
								$IncludiPrefestivo=$ArrObject1[$j]['IncludiPrefestivo'];
								$IncludiFestivo=$ArrObject1[$j]['IncludiFestivo'];
								$OrarioPartenza=$ArrObject1[$j]['OrarioPartenza'];
								$OrarioArrivo=$ArrObject1[$j]['OrarioArrivo'];
								$CorsaStato=$ArrObject1[$j]['Stato'];
							?>
							<tr class="rowBianca">
								<td><span><?=$CorsaPeso?></span></td>
								<td><span><?=$CorsaNome?></span></td>
								<td><span><?=$AttivaDal?></span></td>
								<td><span><?=$AttivaAl?></span></td>
								<td><span><?=$OrarioPartenza?></span></td>
								<td><span><?=$OrarioArrivo?></span></td>
								<td>
									<span>
									<?
									if ($CorsaStato)
										print($dizionario['generale']['attiva']);
									else
										print($dizionario['generale']['disattiva']);    
									?>
									</span> 
								 </td>
								<td><a title="edita" onclick="javascript:ExternalLoad('rt_corsa','corsa.php?do=edit&amp;CorsaId=<?=$CorsaId?>&LineaId=<?=$LineaId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
								<td><a title="edita" onclick="javascript:ExternalLoad('rt_corsa','corsa.php?do=gestioneValiditaBiglietto&amp;CorsaId=<?=$CorsaId?>&LineaId=<?=$LineaId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
                                <td><a title="edita" onclick="javascript:DuplicaCorsa('<?=$CorsaId?>','<?=$CorsaNome?>');" href="#"><i class="fa fa-plus-circle blue" aria-hidden="true" title="duplica" alt="duplica"></i></td>
							</tr>
							<?
							$j++;    
						} 
						?>
						</tbody>
					</table>
				</div>
			</div> 
			<?
			print("<br style=\"clear:both;\"/>");
			print("<br style=\"clear:both;\"/>");
			$i++;
		}
		?>
		<div class="GestoreSedeAdd">
			<a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_linea','linea.php?do=add&PercorsoId=<?=$PercorsoId?>');" title="aggiungi linea"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['percorso']['aggiungi_linea']?></a>
		</div>
	</div>
</form>

<?php
	$db->Close();   
}



function add($step) {
    // Includo il validatore per il percorso
    include_once("percorso_validator.php");
    
    global $HtmlCommon, $db, $percorso_wizard, $dizionario;

    // Se lo step non è specificato, inizializza il wizard di percorso
    if (!$step) {
        $percorso_wizard = null;
        unset($percorso_wizard);
        $_SESSION['PERCORSO_WIZARD'] = null;
        unset($_SESSION['PERCORSO_WIZARD']);
        $step = 1;  // Imposta step iniziale
    }

    $mod = 0;             // Modalità: 0 = aggiunta, 1 = modifica

    // Se `$percorso_wizard` esiste, avvia in modalità modifica
    if (isset($percorso_wizard) && is_object($percorso_wizard)) {
        $PercorsoId = $percorso_wizard->Id;
        $percorso_wizard->conn = $db;
        $percorso_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr = $percorso_wizard->DatiGenerali;
        $Stato = $DatiGeneraliArr['Stato'];
        $mod = 1;  // Modalità modifica

        // Visualizza il titolo della pagina di modifica
        $HtmlCommon->html_titolo_pagina($dizionario['percorso']['titolo_modifica_percorso'] . " " . $DatiGeneraliArr['PercorsoNome'], 1, "rt_percorso", "percorso.php");
    } else {
        // Modalità aggiunta
        $mod = 0;

        // Visualizza il titolo della pagina di aggiunta
        $HtmlCommon->html_titolo_pagina($dizionario['percorso']['titolo_aggiungi_percorso'], 1, "rt_percorso", "percorso.php");
    }

    // Carica il menu del wizard del percorso con lo step e la modalità corrente
    carica_menu_percorso($step, $mod);
    ?>
    <div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
    <?php
    // Seleziona il modulo per lo step specifico
    if ($step == 1) {
        add_step_percorso();
    } elseif ($step == 2) {
        add_step_linee_tratte();
    }

    // Riavvia la connessione al database
    $db = new Database();
    $db->connect();
    ?>
    </div>
    
    <?php
}


function spara_pulsanti_wizard($steptogo) {
    global $dizionario;
	
    $page = new Form();
	?>
	<div class="divSubmit">
		<?php  
		// Crea un pulsante "Salva" che funge anche da pulsante "Avanti" per procedere al prossimo step del wizard
		$page->create_button("Salva", "Salva", $dizionario['generale']['avanti'], "brain_salva", "submit");

		// Se `$steptogo` è maggiore di 0, crea un pulsante "Indietro" per tornare allo step precedente
		if ($steptogo > 0) {
			$page->create_button("indietro", "indietro", $dizionario['generale']['indietro'], "brain_back", "button");
		}
		?>

		<!-- Link per annullare l'operazione e tornare alla pagina principale -->
		<a href="javascript:void(0);" onclick="loadMainContent('rt_percorso','percorso.php',this);" 
		   title="Home" class="brain_annulla"><?= $dizionario['generale']['annulla'] ?></a>

	</div>
	<?php
}

function tariffe(){
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once("percorso_validator.php");
	global $HtmlCommon,$db,$percorso_wizard, $dizionario;
	
	$linea=new Linea();
	$linea->Id=$_GET['LineaId'];
	$linea->conn=$db;
	$linea->inizializzaDatiGenerali();
	$arrLinea=$linea->DatiGenerali;
	
	$HtmlCommon->html_titolo_pagina($dizionario['timetable']['menu_tariffe'],0,"rt_percorso","percorso.php");
	$HtmlCommon->html_titolo_box ($dizionario['timetable']['menu_tariffe']." - ".$arrLinea['LineaNome']);
	
	$page = new Form();
	$dt = new DT();
	$storico = new StoricoOperazioni();
	$storico->conn=$db;
	
	//recupero lineaid e corsaid se si seleziona una corsa
	$LineaId = $linea->Id;
	$CorsaId = 0;
	
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
	?>
	 
	    <form id="application_form" name="application_form"  method="post" action="#">
			
	    <?php
	    //step del menu laterale
	    $page->create_textbox_hidden("action", "create_tariffe");
	    $page->create_textbox_hidden("ComuneId", "-1");
		$page->create_textbox_hidden("TipologiaBigliettoId", "-1");
		
	    ?>
			
	    <div class="brain_formModifica formGestoreEdita">
	        <?=$dizionario['timetable']['avviso_tariffe']?>
	        <br style="clear:both;"/>  
		    <br style="clear:both;"/>          
		    <?php
		    $page->create_textbox_hidden("LineaId", $LineaId);

		    $grafo = new LineaGraph($LineaId, null, null, $db, false, null, 0, true);

			foreach ($arrayBiglietti as $biglietto) { 
				$bigliettoId = $biglietto['TipologiaBigliettoId'];
				?>
				<h4><b><?=$dizionario['biglietto']['dett_tipologia']?></b> <?=$biglietto['TipologiaBiglietto']?></h4>
				<?php foreach ($grafo->graph->nodes as $pickupId => $comune){
				
					$sql = "SELECT * FROM RT_Fermata f
							LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId 
							WHERE ComuneId = '$pickupId' AND t.LineaId = $LineaId and IsPickup = 1";
					
					$ArrObjectCorse = $db->fetch_array($sql);
					if(sizeof($ArrObjectCorse) > 0){
						$sql = "SELECT Comune, ComuneId FROM Comune 
							WHERE ComuneId = $pickupId";
						$infoPickup = $db->query_first($sql);
						echo "<br><h3>".$infoPickup['Comune']."</h3>";
						
						$riga = 0;
						?>
						<table id="tariffe_<?php echo $infoPickup['ComuneId'];?>">
							<tr>
								<td>
								<?php 
								if(isset($comune->descents)) {
									foreach ($comune->descents as $dropoffId => $discesa){
										/*if($riga == 0){
											echo "<tr style='width: 7% !important; vertical-align:center;'>";
										}*/
										$sql = "SELECT Comune FROM RT_Fermata f
												LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId 
												LEFT JOIN Comune c ON c.ComuneId = f.ComuneId
												WHERE f.ComuneId = $dropoffId AND t.LineaId = $LineaId and f.IsDropoff = 1";
										$ArrObjectCorse = $db->query_first($sql);
										
										if(isset($ArrObjectCorse['Comune'])){
											$sql = "SELECT Tariffa FROM RT_LineaTariffa t
											where t.FermataDropoff = $dropoffId 
											AND t.FermataPickup = $pickupId 
											AND LineaId = $LineaId
											AND TipologiaBigliettoId = $bigliettoId";
											
											$info = $db->query_first($sql);
											if(isset($info['Tariffa'])){
												$prezzo = $info['Tariffa'];
											} else {
												$prezzo = 0;
											}
											?>
											<div style="width:300px; display: inline-flex;">
												<table>
													<tr><td>
														<label for="Prezzi['<?= $LineaId . "_" . $bigliettoId . "_" . $pickupId . "_" . $dropoffId ?>']"><?php echo $ArrObjectCorse['Comune'];?></label>
													</td></tr>
													<tr><td>
														<input class="numberDE" type="text" name="Prezzi['<?= $LineaId . "_" . $bigliettoId . "_" . $pickupId . "_" . $dropoffId ?>']" value="<?= $prezzo ?>" SIZE="7">
													</td></tr>
												</table>
											</div>
											<?php
											$riga++;
										}
										/*if($riga >=16){
											$riga = 0;
											echo "</tr>";
										}*/
									}
								}
								?>
								</td>
							</tr>
						<tr><td>
							<input type="button" name="Salva" class="brain_salva salvaComune" value="Salva" onclick="salvaComune('<?= $LineaId ?>', '<?= $infoPickup['ComuneId']?>', '<?=$biglietto['TipologiaBigliettoId']?>')">
						</td></tr>
						</table>
						<?php 
					}
				} ?>
				<hr>
			<?php 
			}
	        ?>
	        </div>
	        <div class="divSubmit">
	    		<? $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>
	        </div>
	    	</form>
			<?php
}


function tariffeComune(){
	include_once("percorso_validator.php");
	
	global $HtmlCommon,$db,$percorso_wizard, $dizionario;

	$linea=new Linea();
	$linea->Id=$_GET['LineaId'];
	$linea->conn=$db;
	$linea->inizializzaDatiGenerali();
	$arrLinea=$linea->DatiGenerali;

	$HtmlCommon->html_titolo_pagina($dizionario['timetable']['menu_tariffe'],0,"rt_percorso","percorso.php");
	$HtmlCommon->html_titolo_box ($dizionario['timetable']['menu_tariffe']." - ".$arrLinea['LineaNome']);

	$page = new Form();
	$dt = new DT();
	$storico = new StoricoOperazioni();
	$storico->conn=$db;

	//recupero lineaid e corsaid se si seleziona una corsa
	$LineaId = $linea->Id;
	
	//recupero la lista di pickup
	$sql = "Select c.ComuneId, c.Comune from RT_Fermata f
		left  join RT_Tratta t on t.TrattaId = f.TrattaId
		left join Comune c on c.ComuneId = f.ComuneId 
		WHERE t.LineaId = $LineaId and t.Stato = 1 and t.Cancella = 0 and f.Stato = 1 and f.Cancella = 0 and f.IsPickup = 1
		group by c.ComuneId, c.Comune 
		order by c.Comune ASC ";
	$ArrObjectFermate = $db->fetch_array($sql);
	$selectFermate = array();
	foreach($ArrObjectFermate as $fermata){
		$temp['ComuneId'] = $fermata['ComuneId'];
		$temp['Comune'] = $fermata['Comune'];
		$selectFermate[] = $temp;
	}
	if(isset($_GET['ComuneId'])){
		$ComuneId = $_GET['ComuneId'];
	} else {
		$ComuneId = 0;
	}

	?>
	 
	    <form id="application_form" name="application_form"  method="post" action="#">
			
	    <?
	    //step del menu laterale
	    $page->create_textbox_hidden("action", "create_tariffe_comune");
		
	// 	$sql = "Select * from RT_Listino where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId and (ListinoId=5 or ListinoId=1) order by ListinoPeso asc";
	// 	$ArrObjectListino = $db->fetch_array($sql);
	    ?>
			
	    <div class="brain_formModifica formGestoreEdita">
	        I prezzi visualizzati sono quelli per tipo <b>Adulto</b>. Il prezzo degli altri biglietti sar&agrave; calcolato in base al sovrappezzo in aumento o diminuzione impostato in Admin > Gestione Listino Prezzi > Tipo Biglietti
	        <br style="clear:both;"/><br>           
		    <?php
		    $page->create_select($dizionario['generale']['fermata'],"ComuneId","ComuneId","brain_campiform campiformBig",$selectFermate,$ComuneId,"ComuneId","Comune",null,1);
		    ?>
		    <script>
				$(document).ready(function(){
					$("#ComuneId").change(function(){
						var value = $("#ComuneId").val();
						page_to_load='/protected/modules/rt_percorso/percorso_action.php?do=getComuneTariffa&ComuneId='+value+'&LineaId=<?php echo $LineaId;?>';
					   	$.get(page_to_load, function(data){
					    	$("#matrice").html(data);
					    	$("#buttonComuneTariffe").show();
					    } );
					});
				});
		     </script>
		     <div id="matrice"></div>
		   
		        <div class="divSubmit" style="display:none;" id="buttonComuneTariffe">
		    		<?php $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>
		        </div>
	    	</form>
			<?

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

    // Inizializza connessione al database e associa all'utente e al percorso wizard, se presente
    $db = new Database();
    $db->connect();
    $user->conn = $db;
    if (is_object($percorso_wizard))
        $percorso_wizard->conn = $db;

    // Recupera i permessi per il modulo corrente
    $permessi = $user->get_permessi_modulo($ModuloId);
    
    if (sizeof($permessi) > 0) {
        
        // Imposta l'azione richiesta dall'utente
        $do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';
        
        switch ($do) {

            case "add":
                // Funzione di aggiunta (FunzioneId=2)
                $FunzioneId = 2;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso)){
                    $step = null;
					if(isset($_REQUEST['step'])) {
						$step = $_REQUEST['step'];
					}
					add($step);
                } else {
                    $errore->stampa_errore(2);
                }
				break;

            case "edit":
                // Funzione di modifica (FunzioneId=4)
                $FunzioneId = 4;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso))
                    edit($_REQUEST['PercorsoId']);
                else
                    $errore->stampa_errore(2);
                break;
				
            case "tariffe":
                // Visualizza tariffe (FunzioneId=1)
                $FunzioneId = 1;
                $permesso = $user->ControllModuloFunzionePermesso(2, $FunzioneId);
                if (sizeof($permesso))
                    tariffe();
                else
                    $errore->stampa_errore(2);
                break;

            case "tariffeComune":
                // Visualizza tariffe per comune (FunzioneId=1)
                $FunzioneId = 1;
                $permesso = $user->ControllModuloFunzionePermesso(2, $FunzioneId);
                if (sizeof($permesso))
                    tariffeComune();
                else
                    $errore->stampa_errore(2);
                break;

            default:
                // Funzione di default: visualizza lista (FunzioneId=1)
                $FunzioneId = 1;
                $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                if (sizeof($permesso))
                    show_list();
                else
                    $errore->stampa_errore(2);
                break;
        }

    } else {
        // Nessun permesso disponibile per l'utente loggato
        $errore->stampa_errore(1);
    }

} else {
    // L'utente non è loggato, reindirizza alla pagina di logout
    header("Location: /logout.php");
}
?>
