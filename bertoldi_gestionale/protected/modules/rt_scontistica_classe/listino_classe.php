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
include_once($classespath_."class.Scontistica.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.DT.php");

  ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);

$ModuloId=35;

function addPromozioneCorsa($ListinoId)
{
	global $HtmlCommon,$user,$db, $dizionario;  

	$db = new Database();
	$db->connect();  
	$page = new Form();
	
	$HtmlCommon->html_titolo_pagina($dizionario['listino']['titolo_gestione_promozione'],0,"","");
	$HtmlCommon->html_titolo_box ($dizionario['listino']['titolo_gestione_promozione']);
	?>
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	
		<h2><?=$dizionario['listino']['associazione_variazione']?></h2>
		<div class="brain_formModifica formGestoreEdita">
		
		<div class="GestoreSedeAdd"><a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=addScontisticaCorsa&ListinoId=<?=$ListinoId?>');" title="aggiungi / rimuovi corse"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['listino']['aggiungi_corse_promozione']?></a></div>
		
		<br />
			<table width="100%" cellspacing="0" cellpadding="0" border="0"
				id="gestoreElencoAule">
				<tbody>
					<tr class="rowIntestazione">
						<td><?=$dizionario['generale']['stato']?></td>
						<td><?=$dizionario['generale']['dal']?></td>
						<td><?=$dizionario['generale']['al']?></td>
						<td><?=$dizionario['generale']['corse']?></td>
						<td><?=$dizionario['generale']['edita']?></td>
					</tr>
			
					<?
					$sql="SELECT
RT_ScontisticaCorsa.ScontisticaCorsaId,
RT_ScontisticaCorsa.ListinoId,
RT_ScontisticaCorsa.Dal,
RT_ScontisticaCorsa.Al,
RT_ScontisticaCorsa.Stato,
RT_ScontisticaCorsaDettaglio.CorsaId,
RT_Corsa.CorsaNome,
RT_Linea.LineaNome
FROM
RT_ScontisticaCorsa
INNER JOIN RT_ScontisticaCorsaDettaglio ON RT_ScontisticaCorsa.ScontisticaCorsaId = RT_ScontisticaCorsaDettaglio.ScontisticaCorsaId
INNER JOIN RT_Corsa ON RT_ScontisticaCorsaDettaglio.CorsaId = RT_Corsa.CorsaId
INNER JOIN RT_Linea ON RT_Corsa.LineaId = RT_Linea.LineaId where ListinoId=$ListinoId";
	//	echo($sql);	
					$ArrObject = $db->fetch_array($sql);
					
					$validitaBiglietto = array();
					foreach ($ArrObject as $row) {
                                           
						if (isset($validitaBiglietto[$row['ScontisticaCorsaId']])) {
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Corse'] .= "<br /> Linea: " . $row['LineaNome']." - Corsa: ".$row['CorsaNome'];
						} else {
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Dal'] = $row['Dal'];
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Stato'] = $row['Stato'];
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Al'] = $row['Al'];
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Corse'] = "Linea: ".$row['LineaNome']."- Corsa: ".$row['CorsaNome'];
						}
                                                
					}
					
					foreach ($validitaBiglietto as $ScontisticaCorsaId => $row) {
						
					?>
						<!-- QUI L'ELENCO DELLE FERMATE -->
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
							<td><span><?=$row['Corse']?></span></td>
							<td><a title="edita" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=editScontisticaCorsa&ListinoId=<?=$ListinoId?>&ScontisticaCorsaId=<?= $ScontisticaCorsaId?>');" href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
			<!-- FINE --> 
			<br />
			
			<div class="GestoreSedeAdd"><a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=addScontisticaCorsa&ListinoId=<?=$ListinoId?>');" title="aggiungi / rimuovi validit&agrave; biglietto"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['listino']['aggiungi_validita_biglietto']?></a></div>
		</div>
	</div>
	<?php
}

function editScontisticaCorsa($ListinoId, $ScontisticaCorsaId = null)
{
    global $HtmlCommon, $user, $db, $dizionario;

    $db = new Database();
    $db->connect();
    $page = new Form();
    $dt = new DT();

    include_once("listino_classe_validator.php");

    $validitaBiglietto = array();

	$elenco_corse = array();
    if ($ScontisticaCorsaId != null) {
        $sql = "SELECT * FROM RT_ScontisticaCorsa WHERE ScontisticaCorsaId = $ScontisticaCorsaId";
        $validitaBiglietto = $db->query_first($sql);
		
		$sql = "SELECT * FROM RT_Corsa WHERE LineaId = ".$validitaBiglietto['LineaId']." AND Stato = 1 AND Cancella = 0 ORDER BY CorsaPeso ASC";
		$elenco_corse = $db->fetch_array($sql);
    }

    $HtmlCommon->html_titolo_pagina($dizionario['listino']['titolo_inserisci_validita'], 0, "", "");
    $HtmlCommon->html_titolo_box($dizionario['listino']['titolo_inserisci_validita']);

    $linea = new Linea();
    $linea->conn = $db;
	$elenco_linee = $linea->getAllForSelect();
	
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            // Datepicker
            var d = new Date();
            $(function() {
                $("#DataDalId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy',
                    firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>'
                });

                $("#DataAlId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy',
                    firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>'
                });
            });
        });
    </script>

    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_data-content">
                    <?php
                    if ($ScontisticaCorsaId == null) {
                        $page->create_textbox_hidden("action", "AggiungiPromozioneCorsa");
                    } else {
                        $page->create_textbox_hidden("action", "AggiungiPromozioneCorsa");
                        $page->create_textbox_hidden("ScontisticaCorsaId", $ScontisticaCorsaId);
                    }
                    $page->create_textbox_hidden("ListinoId", $ListinoId);

                    if ($ScontisticaCorsaId == null) {
                        $page->create_textbox(
                            $dizionario['generale']['dal'] . ":",
                            "DataDalId",
                            "RT_ScontisticaCorsa[Dal]",
                            "",
                            1,
                            "brain_campoForm",
                            array("class" => "'required italianDate'"),
                            "",
                            "10"
                        );
                        $page->create_textbox(
                            $dizionario['generale']['al'] . ":",
                            "DataAlId",
                            "RT_ScontisticaCorsa[Al]",
                            "",
                            1,
                            "brain_campoForm",
                            array("class" => "'required italianDate'"),
                            "",
                            "10"
                        );
						
						print("<br style=\"clear:both;\"/>");
						
						$page->create_select($dizionario['generale']['linea'], 
							"RT_ScontisticaCorsa[LineaId]", 
							"LineaId", 
							"brain_campoForm", 
							$elenco_linee, 
							'', 
							"LineaId", 
							"LineaNome", 
							["class" => "'required'", "onchange" => "onLineaIdChange(this);"], 
							1
						);
                    } else {
                        $page->create_textbox(
                            $dizionario['generale']['dal'] . ":",
                            "DataDalId",
                            "RT_ScontisticaCorsa[Dal]",
                            $dt->format($validitaBiglietto["Dal"], "Y-m-d", "d/m/Y"),
                            1,
                            "brain_campoForm",
                            array("class" => "'required italianDate'"),
                            "",
                            "10"
                        );
                        $page->create_textbox(
                            $dizionario['generale']['al'] . ":",
                            "DataAlId",
                            "RT_ScontisticaCorsa[Al]",
                            $dt->format($validitaBiglietto["Al"], "Y-m-d", "d/m/Y"),
                            1,
                            "brain_campoForm",
                            array("class" => "'required italianDate'"),
                            "",
                            "10"
                        );

                        $statoChecked = $validitaBiglietto["Stato"] == 1 ? array("checked" => "'checked'") : "";
                        $page->create_input_checkbox(
                            $dizionario['listino']['abilitata'],
                            "StatoId",
                            "RT_ScontisticaCorsa[Stato]",
                            1,
                            null,
                            "brain_campoForm",
                            $statoChecked,
                            ""
                        );
						
						print("<br style=\"clear:both;\"/>");
						
						$page->create_select($dizionario['generale']['linea'], 
							"RT_ScontisticaCorsa[LineaId]", 
							"LineaId", 
							"brain_campoForm", 
							$elenco_linee, 
							$validitaBiglietto["LineaId"], 
							"LineaId", 
							"LineaNome", 
							["class" => "'required'", "onchange" => "onLineaIdChange(this);"], 
							1
						);
                    }

                    print("<br style=\"clear:both;\"/>");

					echo "<div id='elencoCorse'>";
                    foreach ($elenco_corse as $biglietto) {
                        if ($ScontisticaCorsaId == null) {
                            $page->create_input_checkbox(
                                $biglietto['CorsaNome'],
                                $biglietto['CorsaNome'],
                                "ValiditaPromozioneCorsa[]",
                                $biglietto['CorsaId'],
                                null,
                                "brain_campoForm",
                                "",
                                ""
                            );
                        } else {
                            $attribute = "";
                            $CorsaId1 = $biglietto['CorsaId'];
                            $sql = "SELECT ScontisticaCorsaId FROM RT_ScontisticaCorsaDettaglio WHERE ScontisticaCorsaId = $ScontisticaCorsaId AND CorsaId = $CorsaId1";
                            $r = $db->query_first($sql);
                            $exist = !empty($r['ScontisticaCorsaId']);

                            if ($exist) {
                                $attribute = array("checked" => "'checked'");
                            }

                            $page->create_input_checkbox(
                                $biglietto['LineaNome'] . " " . $biglietto['CorsaNome'],
                                $biglietto['LineaNome'] . " " . $biglietto['CorsaNome'],
                                "ValiditaPromozioneCorsa[]",
                                $biglietto['CorsaId'],
                                null,
                                "brain_campoForm",
                                $attribute,
                                ""
                            );
                        }
                    }
					echo "</div>";
                    ?>
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


function add()
{
    global $HtmlCommon, $user, $dizionario;

    $db = new Database();
    $db->connect();
    $page = new Form();
    $dt = new DT();

    include_once("listino_classe_validator.php");

    $HtmlCommon->html_titolo_pagina($dizionario['listino']['titolo_aggiungi_classe_variazione'], 0, "rt_scontistica_classe", "listino_classe.php");
    $HtmlCommon->html_titolo_box($dizionario['listino']['titolo_aggiungi_classe_variazione']);

    $arr_stato = [
        ["StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']],
        ["StatoId" => '1', "Stato" => $dizionario['generale']['attivo']]
    ];

    $arr_is = [
        ["ArrIsId" => '0', "ArrIs" => $dizionario['generale']['no']],
        ["ArrIsId" => '1', "ArrIs" => $dizionario['generale']['si']]
    ];
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
                    weekHeader: 'Sm',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy',
                    firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>'
                });

                $("#DataAlId").datepicker({
                    monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
                    monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
                    monthStatus: '<?=$dizionario['generale']['mese_status']?>',
                    yearStatus: '<?=$dizionario['generale']['anno_status']?>',
                    weekHeader: 'Sm',
                    weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
                    dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
                    dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
                    dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
                    dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
                    dateStatus: '<?=$dizionario['generale']['data_status']?>',
                    dateFormat: 'dd/mm/yy',
                    firstDay: 1,
                    initStatus: '<?=$dizionario['generale']['seleziona_data']?>'
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
                        $page->create_textbox_hidden("action", "create");
                        echo "<br style='clear:both;'/>";

                        $page->create_textbox($dizionario['listino']['classe_variazione'], "ListinoNome", "Listino[ListinoNome]", "", 1, "brain_campoForm campiformBig", ["class" => "'required'"]);
                        echo "<br style='clear:both;'/>";

                        $page->create_textbox($dizionario['corsa']['attiva_dal'], "DataDalId", "Listino[AttivaDal]", "", 1, "brain_campoForm", ["class" => "'required italianDate'"], "", "10");
                        $page->create_textbox($dizionario['corsa']['attiva_al'], "DataAlId", "Listino[AttivaAl]", "", 1, "brain_campoForm", ["class" => "'required italianDate'"], "", "10");
                        echo "<br style='clear:both;'/>";

                        $page->create_textbox($dizionario['listino']['da_pax'], "Listino", "Listino[DaPax]", 0, 1, "brain_campoForm campiformBig", ["class" => "'required numeric'"]);
                        echo "<br style='clear:both;'/>";

                        $page->create_textbox($dizionario['listino']['a_pax'], "Listino", "Listino[APax]", 0, 1, "brain_campoForm campiformBig", ["class" => "'required numeric'"]);
                        echo "<br style='clear:both;'/>";

                        $page->create_textbox($dizionario['generale']['peso'], "Peso", "Listino[ListinoPeso]", "", 1, "brain_campoForm", ["class" => "'required'"]);
                        echo "<br style='clear:both;'/>";

                        $page->create_select($dizionario['generale']['stato'], "Listino[Stato]", "StatoId", "brain_campoForm", $arr_stato, 1, "StatoId", "Stato", ["class" => "'required'"], 1);
                        echo "<br style='clear:both;'/>";
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




function edit($ListinoId)
{
include_once("listino_classe_validator.php");      


  global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  
$dt=new DT();

$Listino=new Scontistica($ListinoId);
$Listino->conn=$db;
$Listino->inizializzaDatiGenerali();
$arrListino=$Listino->DatiGenerali;



 $HtmlCommon->html_titolo_pagina($dizionario['listino']['titolo_edit_variazione_prezzi'],0,"rt_scontistica_classe","listino_classe.php");
$HtmlCommon->html_titolo_box ($dizionario['listino']['titolo_edit_variazione_prezzi']." - ".$arrFermata['ListinoNome']);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
$arr_is[]= array("ArrIsId" => '0',"ArrIs" => $dizionario['generale']['no']);
$arr_is[]= array("ArrIsId" => '1',"ArrIs" => $dizionario['generale']['si']);
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
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$ListinoId);
              
                ?>

        
<div id="elenco_comuni"></div>
    <?
    print("<br style=\"clear:both;\"/>");
                
                
               $page->create_textbox($dizionario['listino']['classe_di_listino'],"Listino","Listino[ListinoNome]",$arrListino['ListinoNome'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                 print("<br style=\"clear:both;\"/>");
                 
                  $page->create_textbox($dizionario['corsa']['attiva_dal'],"DataDalId","Listino[AttivaDal]",$dt->format($arrListino['AttivaDal'], "Y-m-d", "d/m/Y"),1,"brain_campoForm",array("class"=>"'required italianDate'"),"","10");
                $page->create_textbox($dizionario['corsa']['attiva_al'],"DataAlId","Listino[AttivaAl]",$dt->format($arrListino['AttivaAl'], "Y-m-d", "d/m/Y"),1,"brain_campoForm",array("class"=>"'required italianDate'"),"","10");
                
                 
                 $page->create_textbox($dizionario['listino']['da_pax'],"Listino","Listino[DaPax]",$arrListino['DaPax'],1,"brain_campoForm campiformBig",array("class"=>"'required numeric'"));           
                 print("<br style=\"clear:both;\"/>");
                 
                 $page->create_textbox($dizionario['listino']['a_pax'],"Listino","Listino[APax]",$arrListino['APax'],1,"brain_campoForm campiformBig",array("class"=>"'required numeric'"));           
                 print("<br style=\"clear:both;\"/>");
                
                    
                      $page->create_textbox($dizionario['generale']['peso'],"Peso","Listino[ListinoPeso]",$arrListino['ListinoPeso'],1,"brain_campoForm",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                    
                $page->create_select($dizionario['generale']['stato'],"Listino[Stato]","StatoId","brain_campoForm",$arr_stato,$arrListino['Stato'],"StatoId","Stato",
                     array("class"=>"'required'"),1);
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
global $user,$HtmlCommon;
$HtmlCommon->html_titolo_pagina("Elenco sedi");
$HtmlCommon->html_titolo_box("Elenco sedi");
$db= new Database();
$db->connect();
include_once("sede_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="20%">gestore</th>
            <th width="20%">comune</th>
            <th width="25%">indirizzo</th>
            <th width="5%">telefono</th>
            <th width="5%">fax</th>
            <th width="10%">email</th>
            <th width="10%">codice</th>
            <th width="5%">edita</th>
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
            <td colspan="8" class="dataTables_empty">Caricamento in corso...</td>
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
                                           edit($_REQUEST['ListinoId']);
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                 case "addPromozioneCorsa":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           addPromozioneCorsa($_REQUEST['ListinoId']);
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "addScontisticaCorsa":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           editScontisticaCorsa($_REQUEST['ListinoId'],null);
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "editScontisticaCorsa":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           editScontisticaCorsa($_REQUEST['ListinoId'],$_REQUEST['ScontisticaCorsaId']);
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