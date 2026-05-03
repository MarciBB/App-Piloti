<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/home_2.css" type="text/css" />
 

<!-- Fine Live Search -->
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."/class.TipologiaBus.php");

include_once($classespath_."class.DT.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.GestioneOttimizzataFlotta.php");
include_once($classespath_."class.Autisti.php");
include_once($classespath_."class.Flotta.php");
include_once($classespath_."class.PreparazioneBusAutista.php");
include_once($classespath_."class.GestioneOttimizzataModifiche.php");
include_once($classespath_."class.Prenotazione.php");


$ModuloId=1;



function carica_menu_percorso($step_corrente,$mod)
{
	global $abilita_modifica,$tratta_wizard,$db, $dizionario;
	//$tratta_wizard->conn=$db;
	//$menu=$tratta_wizard->getMenuWizard();
    $mod=1;
   
    $CorsaId=$_REQUEST['CorsaId'];
    $DataPartenza=$_REQUEST['DataPartenza'];
   
	$menu=array(
		0=>$dizionario['pre']['menu_gestione'],
		1=>$dizionario['pre']['menu_autobus']
    );
	
	if(Config::$organizzazionePostiManuale) {
		$menu[1] = $dizionario['pre']['menu_autobus'];
	}

    ?>
    <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
		<ul>
        	<?
            	$contamenu=0;
                while ($contamenu<=1)
                {
                	$class1="";
                	$class2="";
	                if ($contamenu==$step_corrente)
                    {
                    	$class1="sel";
                        $class2="brain_firstspan sel";
                    }
					$StatoStep="";
                    
					if ( ($contamenu<=1) or (($contamenu>1) and ($mod))) { 
						if($contamenu != 1 || ($contamenu == 1 && Config::$organizzazionePostiManuale)) {?>
							 <li class="<?=$class1?>">
								<span class="<?=$class2?>">
									
									<?php 	
									if ($mod)
									{
									?>
										<a href="javascript:void(0);" onclick="loadMediazioneStep('rt_previaggio','previaggio.php?do=GestionePreviaggio&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
									<?
									}
									else
										echo($menu[$contamenu]);
									?>     
								</span>
							</li>
							<?
						}
                    }
					$contamenu++;
				}
				?>
		</ul>
	</div>
 <?  
}



function show_list()
{
	if(isset($_REQUEST['step'])){
    	$step = $_REQUEST['step'];
	} else {
		$step = 0;
	}
    
    if ($step==0)
    	gestioneOttimizzata();
//     elseif ($step==1)
//         inizializzazione();
//     elseif ($step==2)
//         prenotazioni_da_confermare();
//     elseif ($step==3)  
//       	prepara_navetta();
//     elseif ($step==4)  
//     	prepara_bus();
//     elseif ($step==5)
//         disposizione_posti();
//     elseif ($step==6)
//     	consolida_corsa();
    elseif ($step==1)
    	autobusPasseggeri();
    else
       gestioneOttimizzata();
}


function autobusPasseggeri()
{
	global $user,$HtmlCommon, $dizionario;
	$DataPartenza=$_REQUEST['DataPartenza'];
	$CorsaId=$_REQUEST['CorsaId'];
	$db= new Database();
	$db->connect();
	$corsaobj=new Corsa($CorsaId);
	$corsaobj->conn=$db;
	$corsaobj->inizializzaDatiGenerali();
	$arr_corsa=$corsaobj->DatiGenerali;
	$lineaId = $arr_corsa['LineaId'];
	
	$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
	$titolo=$titolo." / Barche e Passeggeri";

	$HtmlCommon->html_titolo_pagina($titolo);
	$HtmlCommon->html_titolo_box($titolo);
	$DataPartenzaF=$_REQUEST['DataPartenza'];

	$sql="select TotaleDaDefinireBus from RT_PreparazioneBusPaxDaDefinire where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza'";

	$row = $db->query_first($sql);
	$TotaleDaDefinire=0;
	if (!empty($row['TotaleDaDefinireBus']))
		$TotaleDaDefinire=$row['TotaleDaDefinireBus'];


	carica_menu_percorso(1, null);


	$storico=new StoricoOperazioni();
	$storico->conn=$db;

	unset($_SESSION['PREPARAZIONE_BUS']);
	$_SESSION['PREPARAZIONE_BUS']=array();

	//prelevo tutte le tratte
	// prelevo tutti i prenotati
	include_once("previaggio_validator.php");
	?>
	
	<style>
		h1 { padding: .2em; margin: 0; }
		#products { float:left; width: 500px; margin-right: 2em; }
	        #products1 { float:left; width: 500px; margin-right: 2em; }
		#cart { width: 200px; float: left; }
		/* style the list to maximize the droppable hitarea */
	        #catalog1 ol { margin: 0; padding: 1em 0 1em 3em; }
		#catalog ol { margin: 0; padding: 1em 0 1em 3em; }
	        
	        .ui-accordion-content { height: "auto";}
		</style>
	<script>
		//script per aggiunta nuovo bus
		function  ProssimoNodo(ComuneId, DestinazioneId, Last){
			data=null;
			var dataPartenza = "<?php echo $DataPartenza?>";
			var last;
			if(Last == null)
				last = $("#last").val();
			else
				last = Last;
			
			$.ajax({
				type: "POST",
 				dataType: "json",
				url: "/protected/modules/rt_previaggio/previaggio_action.php",
				data: {'action': "GetNodi", 'LineaId':<?=$lineaId?>, 'CorsaId':<?=$CorsaId?>, 'DataPartenza': dataPartenza, 'Comune':ComuneId, 'Destinazione':DestinazioneId, 'last':last},
				success: function(data) {
					var br = $("<br/>");
					$('#selectPercorso').append(br);
					var index;
					var last;
					for (index = 0; index < data.nodes.length; ++index) {
						var r = $('<br/><span>Comune di passaggio: '+data.nodesNomi[index]+'</span><br/>');
						$('#selectPercorso').append(r);
						r = $("<input type='hidden' id='Percorso"+data.nodes[index]+"' name='nodo[]' value='"+data.nodes[index]+"'/>");
						$('#selectPercorso').append(r);
						last = data.nodes[index];
					}
					if(data.child.length>0){
						var option="<option value=''>- <?=$dizionario['generale']['seleziona']?> -</option>";
						for (index = 0; index < data.child.length; ++index) {
							option += "<option value='"+data.child[index]+"'>"+data.childNomi[index]+"</option>";
						}
						var r = $("<br/><span> <?=$dizionario['pre']['comune_interscambio']?>:</span><select id='Percorso"+ComuneId+"' class='selectNodo' name='nodo[]'>"+option+"</select><br/>");
						$('#selectPercorso').append(r);

						if(last==null)
							last = $("#inizioComune").val();

						r = $("<input type='hidden' id='last"+ComuneId+"' name='last"+ComuneId+"' value='"+last+"'/>");
						$('#selectPercorso').append(r);	
					}
// 					if(last!=""){
// 						$("#last").val(last);
// 					}
					$('#selectPercorso').append(r);
					
					data=null;
				},
				error: function(a,b,c){
			    	alert(a.status);
			    	alert(b);
			    	alert(c);
		    	}
			});
		}
		
		$('#selectPercorso').die('change').live('change','.selectNodo', function(e){
			var id = $(e.target).attr('id');
			$("#"+id+" ~ select").remove();
			$("#"+id+" ~ span").remove();
			$("#"+id+" ~ br").remove();
			var select = $(e.target).val();
			var last = $(e.target).next().val();
			ProssimoNodo(select,$("#FinePercorso").val(),last);	
		});
		
	
		//script per according
		$(function() {
			$( "#catalog" ).accordion({
				autoHeight: false,
				navigation: true
			});
	                
			
	                $( "#catalog tr" ).draggable({
				appendTo: "body"
				//helper: "clone"
			});
	                
	        $( "#catalog1" ).accordion({
				autoHeight: false,
				navigation: true
			});
			$(".link_hash").click(function(event){
				window.location.hash=this.hash;
			});
			
	                $( "#catalog1 tr" ).draggable({
				appendTo: "body"
				//helper: "clone"
			});
		});
	</script>
        
   <?
	$gestioneModifiche = new GestioneOttimizzataModifiche($db);
	$nuovePrenotazioni = $gestioneModifiche->getAllNuovePrenotazioni($lineaId, $CorsaId, $DataPartenza);
	$prenDett = new PrenotazioneDettaglio(null);
	$prenDett->conn = $db;
   ?>     
 	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

		<form id="application_form" name="application_form"  method="post" action="#">
			<div class="brain_formModifica">
     			<div class="brain_data-content">      
					<div class="demo">
						
						<div id="products">
							<h1 class="ui-widget-header"><?=$dizionario['pre']['passeggeri_da_definire']?> <?=sizeof($nuovePrenotazioni)?></h1>
                			<div id="catalog">

								<? 
								include_once("previaggio_colum_filtering.php");
								?>
								
								<div id="elenco_clienti">
							        <br/>
									<a href="javascript:void(0);" id="sel_tutto" class="tasti_sel"><?=$dizionario['pre']['sel_tutto']?></a>
							        <a href="javascript:void(0);" id="desel_tutto" class="tasti_sel"><?=$dizionario['pre']['desel_tutto']?></a>        
							        <a href="javascript:void(0);" id="sel_inverso" class="tasti_sel"><?=$dizionario['pre']['sel_inverso']?></a>
							        <br/><br/>
							        <table id="SelTratta1" class="display gallery ui-helper-reset ui-helper-clearfix">
							        	<thead>
							            
							            	<tr class="brain_tabellaTr">
							                      <th width="3%"><?=$dizionario['generale']['pax']?></th>
							                      <th width="35%"><?=$dizionario['generale']['cliente']?></th>
							                      <th width="32%"><?=$dizionario['generale']['partenza']?></th>
							                      <th width="32%"><?=$dizionario['biglietto']['destinazione']?></th> 
											</tr>          
											<tr class="brain_tabellaFilter">
							                    <th><input type="text" /></th> 
							                    <th><input type="text" /></th> 
							                    <th><input type="text" /></th> 
							                    <th><input type="text" /></th> 
											</tr>
										</thead>
							    		<tbody>
							        		<?php	        		
							        		foreach ($nuovePrenotazioni as $p){
												$passeggero = $prenDett->getPrenotazioneDettaglio($p['PrenotazioneNumero'], $lineaId, $CorsaId, $DataPartenza);
							        			?>
							        			<tr id="PrenotazioneId_<?=$p['PrenotazioneNumero']?>" class="dragme ui-widget-content ui-corner-tr multidraggable" style="position: relative;"> 
								                    <td class="cell_0"><p></p></td> 
								                    <td class="cell_1"><?=$passeggero['Cognome']." ".$passeggero['Nome']?></td>
								                    <td class="cell_2"><?=$passeggero['ComunePartenza']." - ".$passeggero['FermataPartenza']?></td>
								                    <td class="cell_3"><?=$passeggero['ComuneArrivo']." - ".$passeggero['FermataArrivo']?></td>
							        			</tr>
										        <?php 
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
    					<?php //tipologie Bus e tratte
						$gruppo = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, true);
						$f = new Fermata();
						$f->conn = $db;
						$partenze = $f->getComuniInizioTratta($lineaId);
						$arrivi = $f->getComuniFineTratta($lineaId);
						$interscambio = array();
						
						foreach ($partenze as $partenza){
							if(array_key_exists($partenza, $interscambio)){
								$interscambio[$partenza]['Tipo'] .= " - ".$dizionario['pre']['inizio_tratta'];
							}else{
								$c = new Comune($partenza);
								$c->conn = $db;
								$c->inizializzaDatiGenerali();
								unset($temp);
								$temp['ComuneId'] = $partenza;
								$temp['Comune'] = $c->Comune;
								$temp['Tipo'] = 'Inizio Tratta';
								$interscambio[$partenza] = $temp;
							}
						}

						foreach ($gruppo->graph->nodes as $comune){
							if($f->isInterscambio($comune->id)==true){
								if(array_key_exists($comune->id, $interscambio)){
									$interscambio[$comune->id]['Tipo'] .= " - ".$dizionario['pre']['interscambio'];
								}else{
									$c = new Comune($comune->id);
									$c->conn = $db;
									$c->inizializzaDatiGenerali();
									unset($temp);
									$temp['ComuneId'] = $comune->id;
									$temp['Comune'] = $c->Comune;
									$temp['Tipo'] = 'Interscambio';
									$interscambio[$comune->id] = $temp;
								}
							}
							
							if($f->isPickup($lineaId, $comune->id)==true){
								if(array_key_exists($comune->id, $interscambio)){
									$interscambio[$comune->id]['Tipo'] .= " - ".$dizionario['pre']['pickup'];
								}else{
									$c = new Comune($comune->id);
									$c->conn = $db;
									$c->inizializzaDatiGenerali();
									unset($temp);
									$temp['ComuneId'] = $comune->id;
									$temp['Comune'] = $c->Comune;
									$temp['Tipo'] = 'Pick Up';
									$interscambio[$comune->id] = $temp;
								}
							}
							
						}

					/*	foreach ($arrivi as $arrivo){
							if(array_key_exists($arrivo, $interscambio)){
								$interscambio[$arrivo]['Tipo'] .= " - Fine Tratta";
							}else{
								$c = new Comune($arrivo);
								$c->conn = $db;
								$c->inizializzaDatiGenerali();
								unset($temp);
								$temp['ComuneId'] = $arrivo;
								$temp['Comune'] = $c->Comune;
								$temp['Tipo'] = 'Fine Tratta';
								$interscambio[$arrivo] = $temp;
							}
						}
						*/
						
						$sql="select * from  RT_ElencoTipologiaBus where OdcIdRef=$user->OdcId order by TipologiaBusId desc";
						$ArrObject = $db->fetch_array($sql);
						$numerotratte=sizeof($ArrObject); 
						?>
    					<div id="products1">
							<h1 class="ui-widget-header">Bus</h1>	
							<div id="catalog1">
							
							<?
							foreach ($interscambio as $comune){
								$TrattaId=$comune['ComuneId'];
								$NomeTratta=$comune['Comune'];
								?>
								<h3 class="menuitem"><a class="link_hash" href="#<?=$TrattaId?>"><?=$NomeTratta.": ".$comune['Tipo']?></a></h3>
								<div>
									<ul id="tt_<?=$TrattaId?>">
										<div class="demo ui-widget ui-helper-clearfix">
											<a title="aggiungi bus" onclick="AggiungiBus('<?=$TrattaId?>');" class="brain_aggiungi est"><?=$dizionario['pre']['aggiungi_bus']?></a>

											<? // pullman preparati 
											$contanavette=1;
											$numero_navette=1;
											if (sizeof($gruppo->graph->nodes[$comune['ComuneId']]->busPartenza)>0)
												$numero_navette = sizeof($gruppo->graph->nodes[$comune['ComuneId']]->busPartenza);
											foreach ($gruppo->graph->nodes[$comune['ComuneId']]->busPartenza as $bus){
												$indexComune = 0;												
												foreach ($gruppo->flotta[$bus]->comuni as $key=>$comuneSearch){
													if($comuneSearch['comune']==$comune['ComuneId']){
														$indexComune = $key;
													}
												} 
												
												$PostiGestiti = sizeof($gruppo->flotta[$bus]->comuni[$indexComune]['passeggeri']);
												?>
												<div id="Navetta_<?=$bus?>_<?=$TrattaId?>" class="trash ui-widget-content ui-state-default">
													<h4 class="ui-widget-header header_con_icone"><span class="pullman_header"><?=$dizionario['pre']['pulman_n']?> <?=$gruppo->flotta[$bus]->nome?> (<?=$PostiGestiti?>)</span><br/>
										            <a class="icona_rimuovi" onclick="javascript:NascondiBus('<?=$gruppo->flotta[$bus]->id?>')"><img src="../images/remove-icon.png" alt="Rimuovi"/></a> 
										            <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_carico.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&BusId=<?=$gruppo->flotta[$bus]->id?>&LineaId=<?=$lineaId?>"><img src="../images/print-icon.png" alt="Stampa"/> <?=$dizionario['pre']['org']?></a>
										            <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_carico_pdf.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&BusId=<?=$gruppo->flotta[$bus]->id?>&LineaId=<?=$lineaId?>"><img src="../images/print-icon.png" alt="Stampa pdf"/> <?=$dizionario['pre']['org_pdf']?></a>
										            <a target="_new" class="icona_stampa" href="/protected/modules/rt_previaggio/stampa_voucher_di_viaggio.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&BusId=<?=$gruppo->flotta[$bus]->id?>&LineaId=<?=$lineaId?>&seller_type=1"><img src="../images/print-icon.png" alt="Stampa"/> <?=$dizionario['pre']['voucher']?></a>
            
            										<br class="clear:both" /></h4>
        											<a href="#pul_<?=$contanavette?>_<?=$TrattaId?>"></a>
        
        											<table class="gallery ui-helper-reset display">
        												<tbody>
            											<?   
              											$np=0;
        												$numeropasseggeri=0;
        												$sql="select * from RT_PreparazioneBusPerTipologia where  BusNumero=$contanavette and  CorsaId=$CorsaId and DataPartenza='$DataPartenza' and BusId=$TrattaId order by ClienteNome asc";
    													$ArrObjectP = $db->fetch_array($sql);
    													$numeropasseggeri=sizeof($ArrObjectP);

														foreach ($gruppo->flotta[$bus]->comuni[$indexComune]['passeggeri'] as $dest=>$passeggeri){
															foreach ($passeggeri as $key=>$biglietto){
													 			$p = $prenDett->getPrenotazioneDettaglio($biglietto, $lineaId, $CorsaId, $DataPartenza);
															    
															    $Class="even";
															    if ( ($np%2)==0 ) { 
																	$Class = "odd"; 
																}else{
																	$Class = "even"; 
																}      
	    														?>
																<tr id="PrenotazioneId_<?=$biglietto?>" class="ui-widget-content ui-corner-tr dragme <?=$Class?>"> 
														        	<td class="cell_0"></td>
														        	<td class="cell_1"><?=$p['Cognome']." ".$p['Nome']?></td>
														        	<td class="cell_2"><?=$p['ComunePartenza']." - ".$p['FermataPartenza']?></td>
													        		<td class="cell_3"><?=$p['ComuneArrivo']." - ".$p['FermataArrivo']?></td>
														        </tr>
																<?
																$np++;
															}		
														}
														?>
         											</tbody>
      											</table>
											</div>
											<? 
											$contanavette++;
										}                                
										
     								?>       
							</div><!-- End demo -->
						</ul>
					</div>
 					<?
 					} 		
					?>		
							
								
            		<?php 		
 					$lastidA=$db->delete("RT_PreparazioneBus","CorsaId=$CorsaId and  DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId");
					$data=$_SESSION['PREPARAZIONE_BUS'];
					foreach ($data as $chiave => $valore){ 
					    $chiave=str_replace("'","",$chiave);
					    $chiave=str_replace("\\","",$chiave);
					    $arr_chiave=explode('_', $valore);
					    $BusId=$arr_chiave[0];
					    $BusNumero=$arr_chiave[1];
					    $PrenotazioneId=$chiave;
     
					    $d1['PrenotazioneId']=$PrenotazioneId;
					    $d1['BusId']=$BusId;
					   	$d1['BusNumero']=$BusNumero;
					    $d1['CorsaId']=$CorsaId;
					    $d1['DataPartenza']=$DataPartenza;
     					if ($PrenotazioneId>0){ 
				        	$d1=$storico->operazioni_insert($d1,$user);
				            $lastidA=$db->insert("RT_PreparazioneBus", $d1);
      					}
 					}
					?> 
				</div>
			</div>
		</div><!-- End demo -->
    </div>
	</div>
</form>

</div>
	
	<style>
		 gallery { float: left; width: 90%; min-height: 12em; } * html .gallery { height: 12em; } /* IE6 */
		.gallery.custom-state-active { background: #eee; }
		.gallery li { width: auto; padding: 0.4em; margin: 0 0.4em 0.4em 0; text-align: center; }
		.gallery li h5 { margin: 0 0 0.4em; cursor: move; }
		.gallery li a { float: right; }
		.gallery li a.ui-icon-zoomin { float: left; }
		.gallery li img { width: 100%; cursor: move; }
	
		.trash { float: left; width: 100%; min-height: 4em; padding: 1%;} * html .trash { height: 8em; } /* IE6 */
		.trash h4 { line-height: 16px; margin: 0 0 0.4em; }
		.trash h4 .ui-icon { float: left; }
		.trash .gallery h5 { display: none; }
	</style>
    <script src="/js/ui.multidraggable.js"></script>
	<script>
	$(function() {
		// there's the gallery and the trash
		    var $gallery = $( ".gallery" ),
			$trash = $( "#trash1" );
		
		$(".multidraggable").click(function(event) {
            $(this).toggleClass('grouped');
     	});
		
		//seleziona/deseleziona elementi
		$("#sel_tutto").click(function(event) {
			$("#SelTratta1 tr.dragme").addClass('grouped');
		});
		$("#desel_tutto").click(function(event) {
			$("#SelTratta1 tr.dragme").removeClass('grouped');
		});
		$("#sel_inverso").click(function(event) {
			$("#SelTratta1 tr.dragme").toggleClass('grouped');
		});

		// let the gallery items be draggable
		$( ".dragme", $gallery).draggable({
			cancel: "a.ui-icon", // clicking an icon won't initiate dragging
			revert: "invalid", // when not dropped, the item will revert back to its initial position
			containment: $( "#demo-frame" ).length ? "#demo-frame" : "document", // stick to demo-frame if present
			helper: "clone",
			cursor: "move"
		});
		// let the trash be droppable, accepting the gallery items       
        d=1;
        numerotratte=2;
        /* while (d<=numerotratte)
        {*/
        $(".trash").droppable({
            hoverClass: "ui-state-active",
            accept: "li, .dragme, .grouped",
			activeClass: "ui-state-highlight",
			drop: function( event, ui ) {
				//var num = ui.draggable.find(id).length;
				if($(".grouped").length != 0)
				{
					var IDs = [];
					$("#SelTratta1").find(".grouped").each(function(){ IDs.push(this.id); });
					//alert(IDs.length);
					for(var i=0; i<IDs.length; i++)
					{
						xx=deleteImage( IDs[i], this, 1);
					}
				}
				else 
				{
					deleteImage( ui.draggable, this, 2); 
				}
                                	
                CorsaId='<?=$CorsaId?>';
                DataPartenza='<?=$DataPartenza?>';
                dialog_box_previaggio();
                loadMediazioneStep("rt_previaggio","previaggio.php?do=GestionePreviaggio&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&step=1",this);   
			}
        });  
		// let the gallery be droppable as well, accepting items from the trash
		$gallery.droppable({
			accept: ".trash li, .dragme",
			activeClass: "custom-state-active",
			drop: function( event, ui ) {                          
				recycleImage( ui.draggable );
			}
		});

		// image deletion function
		var recycle_icon = "";
        function deleteImage( $item,oggetto,tipo ) {
        	BudId=$(oggetto).attr("id");
            $MoveTo = oggetto;
			if(tipo==1)
            	PrenotazioneId=$item;
			else if(tipo==2)
				PrenotazioneId=$item.attr("id");
    
           CorsaId='<?=$CorsaId?>';
           DataPartenza='<?=$DataPartenza?>';
           LineaId='<?=$lineaId?>';
           page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=PreparaPasseggeroBus&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&Prenotazione="+PrenotazioneId+"&BusId="+BudId+"&LineaId="+LineaId;
           $.get(page_to_load, function(data){
	           msg1=jQuery.trim(data);
	           if (msg1!='ok'){ 
	           		alert(data);
	           }
           });
           return 1;
		}

		// image recycle function
		var trash_icon = "<a href='link/to/trash/script/when/we/have/js/off' title='Delete this image' class='ui-icon ui-icon-trash'>Delete image</a>";
		function recycleImage( $item ) {
        	PrenotazioneId=$item.attr("id");
        	CorsaId='<?=$CorsaId?>';
        	DataPartenza='<?=$DataPartenza?>';
        	LineaId='<?=$lineaId?>';
                   
			$item.fadeOut(function() {
				$item
					.find( "a.ui-icon-refresh" )
					.remove()
					.end()
					.append( trash_icon )
					.find( "img" )
					.css( "height", "72px" )
					.end()
					.appendTo( $gallery )
					.fadeIn();
			});
           	dialog_box_previaggio();
            page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=RimuoviPasseggeroBus&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&Prenotazione="+PrenotazioneId+"&LineaId="+LineaId;
            $.get(page_to_load, function(data){
				loadMediazioneStep("rt_previaggio","previaggio.php?do=GestionePreviaggio&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&step=1",this);
            });
		}

		
		// resolve the icons behavior with event delegation
		$( "ul.gallery > li" ).click(function( event ) {
			
			var $item = $( this ),
				$target = $( event.target );

			if ( $target.is( "a.ui-icon-trash" ) ) {
				deleteImage( $item );
			} else if ( $target.is( "a.ui-icon-zoomin" ) ) {
				viewLargerImage( $target );
			} else if ( $target.is( "a.ui-icon-refresh" ) ) {
				recycleImage( $item );
			}

			return false;
		});
	});
        
        function AggiungiBus(ComuneId)
        {
        	var dataPartenza = "<?php echo $DataPartenza?>";
        	$.ajax({
				type: "POST",
				url: "/protected/modules/rt_previaggio/previaggio_action.php",
				data: {'action': "AggiungiBus", 'lineaId':<?=$lineaId?>, 'corsaId':<?=$CorsaId?>, 'dataPartenza': dataPartenza, 'comuneId': ComuneId},
				success: function(data) {
					dialog_box();
					$("#brain_listaSelezione").html(data);
					adatta_dialog_box();
				}
			});
		}
        
    
        function NascondiBus(BusId){
        	CorsaId='<?=$CorsaId?>';
        	LineaId='<?=$lineaId?>'
           	DataPartenza='<?=$DataPartenza?>';
           	stringa="Eliminando il bus i passeggeri dovranno essere riposizionati. Continuare?";
           	conferma = confirm(stringa);
           	if (conferma){
           		page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=RimuoviBus&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&BusId="+BusId+"&LineaId="+LineaId;
                $.get(page_to_load, function(data){
	            	msg1=jQuery.trim(data);
	                if (msg1=='ok'){
	                	alert("<?=$dizionario['pre']['alert_eliminato']?>");
	                	loadMediazioneStep("rt_previaggio","previaggio.php?do=GestionePreviaggio&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&step=1",this);
	                }
            	});
           	} 
        }    
	</script>
<?  
}


function gestioneOttimizzata(){
	global $user,$HtmlCommon, $dizionario;
	$DataPartenza=$_REQUEST['DataPartenza'];
	$CorsaId=$_REQUEST['CorsaId'];
	include_once("previaggio_validator.php");
	$db= new Database();
	$db->connect();
	$corsaobj=new Corsa($CorsaId);
	$corsaobj->conn=$db;
	$corsaobj->inizializzaDatiGenerali();
	$arr_corsa=$corsaobj->DatiGenerali;
	
	$titolo = $arr_corsa['CorsaNome']." del ".$DataPartenza;
	$titolo = $titolo." / ".$dizionario['pre']['menu_gestione'];
	
	$HtmlCommon->html_titolo_pagina($titolo, null, null, null);
	$HtmlCommon->html_titolo_box($titolo);
	$page = new Form();
	
	carica_menu_percorso(0, null);
	
	$lineaId = $arr_corsa['LineaId'];
	
	$gestioneOttimizzata = new GestioneOttimizzataFlotta(null, $db);
	$gestioneOttimizzata->conn = $db;
	$caricaDB=false;
	if($gestioneOttimizzata->getNumBus($lineaId, $CorsaId, $DataPartenza)>0){
		$caricaDB=true;
	}else{
		$caricaDB=false;
		$gruppo = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false);
	}
	$gruppo = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, true);
	
	$prenDett = new PrenotazioneDettaglio(null);
	$prenDett->conn=$db;
	?>
	
	<style>
		h1 { padding: .2em; margin: 0; }
		#products { float:left; width: 500px; margin-right: 2em; }
	    #products1 { float:left; width: 500px; margin-right: 2em; }
		#cart { width: 200px; float: left; }
		/* style the list to maximize the droppable hitarea */
	    #catalog1 ol { margin: 0; padding: 1em 0 1em 3em; }
		#catalog ol { margin: 0; padding: 1em 0 1em 3em; }  
	    .ui-accordion-content { height: "auto";}
	</style>
        
	<script> 
		$(function() {
			$( "#catalog" ).accordion({
				autoHeight: false,
				navigation: true
			});
			
	                $( "#catalog tr" ).draggable({
				appendTo: "body"
				//helper: "clone"
			});
	                
	                $( "#catalog1" ).accordion({
				autoHeight: false,
				navigation: true
			});
			
			$( ".accordion_bus" ).accordion({
				autoHeight: false,
				navigation: true,
				collapsible: true,
				autoHeight: false,
				navigation: true,
				header: '.accordion_header'
			});
			
	                $( "#catalog1 tr" ).draggable({
				appendTo: "body"
			});
			
		});
	</script>
	
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	<div class="brain_formModifica formGestoreEdita">
				<?=$dizionario['pre']['operazioni_eseguite']?>
		<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
	    	<thead>
	            <tr class="rowIntestazione">
	            	<td><?=$dizionario['generale']['cliente']?></td>
					<td><?=$dizionario['generale']['comune_partenza']?></td>
					<td><?=$dizionario['generale']['comune_arrivo']?></td>
					<td><?=$dizionario['pre']['cod_prenotazione']?></td>
	                <td><?=$dizionario['pre']['operazione']?></td>
				</tr>
			</thead>
			<tbody>
				<?php
				$gestioneModifiche = new GestioneOttimizzataModifiche($db);
				$modifiche = $gestioneModifiche->getAll($lineaId, $CorsaId, $DataPartenza);
				foreach ($modifiche as $mod){
					if($mod['PrenotazioneNumero']==0){
						$sql = "DELETE FROM RT_GestioneOttimizzataModifiche
								WHERE
								GestioneOttimizzataModificheId=".$mod['GestioneOttimizzataModificheId'];
								$db->query($sql);
					}else{
						$info = $prenDett->getPrenotazioneDettaglio($mod['PrenotazioneNumero'], $lineaId, $CorsaId, $DataPartenza);
					?>
					<tr>
						<td><?=$info['Cognome']." ".$info['Nome']?></td>
						<td><?=$info['ComunePartenza']?></td>
						<td><?=$info['ComuneArrivo']?></td>
						<td>
							<?php
								$p =  new Prenotazione($mod['PrenotazioneId']);
								$p->conn = $db;
								$p->inizializzaDatiGenerali();
								echo $p->DatiGenerali['CodicePrenotazione']; 
							?>
						</td>
						<td>
							<?php
								if($mod['Aggiungi']==0){
									echo $dizionario['pre']['prenotazione_eliminata'];
								}else if($mod['Aggiungi']==1){
									echo $dizionario['pre']['prenotazione_nuova'];
								}else{
									echo $dizionario['pre']['prenotazione_da_ripristinare'];
								} 
							?>
						</td>
					</tr>
				<?php
					}	
				} 
				?>
			</tbody>
		</table>
		<br/>
		<br/>
				
		<?php 
			$flottaObj = new Flotta();
			$flottaObj->conn = $db;
			$arr_flotta = $flottaObj->getAllForSelect();
			$autisti =  new Autista();
			$autisti->conn = $db;
			$arr_autisti = $autisti->getAllForSelect();
			$busAutista = new PreparazioneBusAutista($db);
			$countBus = 0;
			$indexBus = array();
			
			asort($gruppo->flotta);
			foreach ($gruppo->flotta as $bus){
			
				$flotta = new GestioneOttimizzataFlotta($bus->id,$db);
				$flotta->inizializzaDatiGenerali();
				$busDB = $flotta->DatiGenerali;
				$autisti = $busAutista->getAutisti($DataPartenza, $CorsaId, $lineaId, $bus->id);
			 
				?>
			<div class="accordion_bus">
				<h1 class="accordion_header"> <?php
					$c = new Comune($bus->comuni[0]['comune']);
					$c->conn = $db;
					$c->inizializzaDatiGenerali();
					$comunePartenza = $c->Comune;
					$c = new Comune($bus->comuni[sizeof($bus->comuni)-1]['comune']);
					$c->conn = $db;
					$c->inizializzaDatiGenerali();
					$comuneArrivo = $c->Comune;
					$title = "Barca ".$bus->nome." - Tratta: ".$comunePartenza." - ".$comuneArrivo;
					echo $title;
					
					
				?>
				</h1>
    
				<div class="accordion_content">
				
    				<form id="application_form_<?=$bus->id?>" name="application_form_<?=$bus->id?>"  method="post" action="#">
    				<a target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_carico.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&BusId=<?=$bus->id?>&LineaId=<?=$lineaId?>"><img src="../images/print-icon.png" alt="Stampa"/> <?=$dizionario['pre']['org']?></a>
    				<a target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_carico_pdf.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&BusId=<?=$bus->id?>&LineaId=<?=$lineaId?>"><img src="../images/print-icon.png" alt="Stampa"/> <?=$dizionario['pre']['org_pdf']?></a>
					<a target="_new" href="/protected/modules/rt_previaggio/stampa_voucher_di_viaggio.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&BusId=<?=$bus->id?>&LineaId=<?=$lineaId?>&seller_type=1"><img src="../images/print-icon.png" alt="Stampa"/> <?=$dizionario['pre']['voucher']?></a>
      				
      				<input type="hidden" id="DataPartenza" name="DataPartenza" value="<?=$DataPartenza?>">
      				<input type="hidden" id="CorsaId" name="CorsaId" value="<?=$CorsaId?>">
      				<input type="hidden" id="LineaId" name="LineaId" value="<?=$lineaId?>">
      				<input type="hidden" id="BusId" name="BusId" value="<?=$bus->id?>">
        				<input type="hidden" name="action" value="UpdateAutobus"> 
        				<input type="hidden" name="step_successivo" id="step_successivo" value="0"> 
 					<div class="form_autisti">
		               <?   
		               $page->create_select($dizionario['flotta']['autobus'],"FlottaId","FlottaId","brain_campiform",$arr_flotta, $busDB['BusId'], "FlottaId","Targa",null,1);
		               print("<br style=\"clear:both;\"/>");
		               print("<br style=\"clear:both;\"/>");
		               $page->create_select($dizionario['pre']['autista1'],"Autista1","Autista1","brain_campiform",$arr_autisti,$autisti['Autista1'],"AutistiId","Autisti",null,0);
		               $page->create_select($dizionario['pre']['autista2'],"Autista2","Autista2","brain_campiform",$arr_autisti,$autisti['Autista2'],"AutistiId","Autisti",null,0);             
		               print("<br style=\"clear:both;\"/>");
		               print("<br style=\"clear:both;\"/>");
		               $page->create_button($dizionario['pre']['registra_dati'],"Salva",$dizionario['generale']['salva'],"brain_salva","submit"); 
		               ?>
    		   		</div> 
         		</form> 
  				<script type="text/javascript"> 
				function submit_form_previaggio_<?=$bus->id?>(){
 			        var messaggio;
 			        var id;
 			        var step_successivo=$("#step_successivo").val();
			        var CorsaId='<?=$CorsaId?>';
			        var DataPartenza='<?=$DataPartenza?>';
			        
			        $.ajax({
			           type: "POST",
			           url: "/protected/modules/rt_previaggio/previaggio_action.php",
			           data: $("#application_form_<?=$bus->id?>").serialize(),
			           success: function(msg) { 
			              msg=jQuery.trim(msg);
			              if(msg.indexOf(',')>-1)
			              {
			                  arr_response=msg.split(",");
			                  messaggio=arr_response[0];
			                  id=arr_response[1];
			              }
			              else
			                  messaggio=msg;               
			              avviso_operazione("ok");
			              loadMainContent('rt_previaggio','previaggio.php?do=add&step='+step_successivo+'&CorsaId='+CorsaId+'&DataPartenza='+DataPartenza,this);
			            } // end success
			         });
			    }
			    
				$("#application_form_<?=$bus->id?>").validate({
	                submitHandler: function(form) {
	               		// $(form).ajaxSubmit();
	               		submit_form_previaggio_<?=$bus->id?>();
	           		} 
	   		});
				</script>
		 				
				<table class="tabellaPercorso_<?=$bus->id?>" width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
		    		<thead>
			            <tr class="rowIntestazione">
			            	<td><?=$dizionario['generale']['comune']?></td>
							<td><?=$dizionario['pre']['salite_pickup']?></td>
							<td><?=$dizionario['pre']['salite_inter']?></td>
							<td><?=$dizionario['pre']['discese_drop']?></td>
							<td><?=$dizionario['pre']['discese_inter']?></td>
			                <td><?=$dizionario['pre']['tot_passeggeri']?></td>
			                <td><?=$dizionario['pre']['dettagli']?></td>
						</tr>
					</thead>
					<tbody>
						<?php
						if(isset($bus->comuni) && sizeof($bus->comuni)>0){
							foreach ($bus->comuni as $index => $comune){
								//salite
								$countSalitePickup = 0;
								//calcolo persone che salgono dalla fermata
								if($gruppo->graph->nodes[$comune['comune']]->salite>0){
									foreach ($gruppo->graph->nodes[$comune['comune']]->bigliettiSalite as $dest=>$passeg){
										foreach ($passeg as $value){
											if(in_array($value, $comune['passeggeri'][$dest])){
												$countSalitePickup++;
											}
										}
									}
								}
								
								//calcolo persone che salgono da altri bus
								$countSaliteInterscambio = 0;
								if(sizeof($gruppo->graph->nodes[$comune['comune']]->busPartenza)>=1 || sizeof($gruppo->graph->nodes[$comune['comune']]->busArrivo)>=1){
									foreach ($gruppo->graph->nodes[$comune['comune']]->busArrivo as $tempBusId){
										if($tempBusId != $bus->id){
											$indexComuneTempBusId = -1;
											foreach ($gruppo->flotta[$tempBusId]->comuni as $key=>$value){
												if(strcmp($value['comune'],$comune['comune'])==0){
													$indexComuneTempBusId = $key;
												}
											}
											if(isset($comune['passeggeri'])){
												foreach ($comune['passeggeri'] as $key => $passeggeri){
													foreach ($passeggeri as $p){
														if(isset($gruppo->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri'][$key]) && in_array($p, $gruppo->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri'][$key])){
															$countSaliteInterscambio++;
														}
													}
												}
											}
										}
									}
								}
							
								//discese
								$countDisceseDropoff = 0;
								//calcolo delle persone che scendono alla fermata
								if($gruppo->graph->nodes[$comune['comune']]->discese>0){
									if(isset($bus->comuni[$index-1]['passeggeri'])){
										foreach ($bus->comuni[$index-1]['passeggeri'][$comune['comune']] as $p){
											if(in_array($p, $bus->comuni[$index-1]['passeggeri'][$comune['comune']])){
												$countDisceseDropoff++;
											}
										}
									}
								}
								
								//calcolo delle persone che scendono per salire in un altri bus
								$countDisceseInterscambio = 0;
								$removeDiscesa = false;
								if( $index == (sizeof($bus->comuni)-1) ){
									$removeDiscesa = true;
								}
								if(sizeof($gruppo->graph->nodes[$comune['comune']]->busPartenza)>=1 || sizeof($gruppo->graph->nodes[$comune['comune']]->busArrivo)>=1){
									
									foreach ($gruppo->graph->nodes[$comune['comune']]->busPartenza as $tempBusId){
										if($tempBusId != $bus->id){
											$indexComuneTempBusId = -1;
											foreach ($gruppo->flotta[$tempBusId]->comuni as $key => $value){
												if(strcmp($value['comune'],$comune['comune'])==0){
													$indexComuneTempBusId = $key;
												}
											}
											if($index-1 >= 0){
												if(isset($bus->comuni[$index-1]['passeggeri'])){
													foreach ($bus->comuni[$index-1]['passeggeri'] as $key => $passeggeri){
														foreach ($passeggeri as $p){
															if(isset($gruppo->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri']) && isset($gruppo->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])){
																if(in_array($p, $gruppo->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])){
																	$countDisceseInterscambio++;
																}
															}
														}
													}
												}
											}
										}
									}
								}
								
							if($countSalitePickup > 0 || $countSaliteInterscambio > 0 || $countDisceseDropoff > 0 || $countDisceseInterscambio > 0){
							?>
								<tr>
							<?php 
							} else {
							?>
								<tr class="percorsoCompleto">
							<?php 
							}
							?>
								<td>
									<?php 
										$c = new Comune($comune['comune']);
										$c->conn = $db;
										$c->inizializzaDatiGenerali();
										echo $c->Comune;
									?>
								</td>
								<td><?=$countSalitePickup?></td>
								<td><?=$countSaliteInterscambio?></td>
								<td><?=$countDisceseDropoff?></td>
								<td><?=$countDisceseInterscambio?></td>
				                <td>
				                	<?php
				                		$tot = 0;
				                		if(isset($comune['passeggeri'])){
					                		foreach ($comune['passeggeri'] as $key=>$value){
												$tot += sizeof($value);
											}
				                		}
				                		if($removeDiscesa){
				                			echo $tot-$countDisceseInterscambio;
				                		} else {
											echo $tot;
				                		}
				                	?>
				                </td>
				                <td>
				                	<a id="dettaglio<?php echo $bus->id."".$comune['comune'];?>" href="javascript:void(0);">Dettaglio</a>
				                	<script type="text/javascript">
										$('#dettaglio<?php echo $bus->id."".$comune['comune'];?>').click(function () {
											var biglietti = new Array();
											<?php
												foreach ($edge->info as $key => $value){
											?>
													biglietti[<?=$key?>] = <?=$value?>;
											<?php 
												}
											?>
											var dataPartenza = "<?php echo $DataPartenza?>";
											$.ajax({
												type: "POST",
												url: "/protected/modules/rt_previaggio/previaggio_action.php",
												data: {'action': "DettaglioPasseggeriBus", 'lineaId':<?=$lineaId?>, 'corsaId':<?=$CorsaId?>, 'dataPartenza': dataPartenza, 'comuneId': <?=$comune['comune']?>, 'busId':<?=$bus->id?>, 'indexComune':<?=$index?>, 'title':'<?=$title?>', 'salitePickup':<?=$countSalitePickup?>, 'saliteInterscambio':<?=$countSaliteInterscambio?>, 'disceseDropoff':<?=$countDisceseDropoff?>, 'disceseInterscambio':<?=$countDisceseInterscambio?>, 'totPassegeri':<?=$tot?>, 'remove':'<?=$removeDiscesa?>'},
												success: function(data) {
													dialog_box();
													$("#brain_listaSelezione").html(data);
													adatta_dialog_box();
												}
											});
										});
									</script>
				                </td>
							</tr>
							<?php
							
							}
						}
						?>
					</tbody>
				</table>
				<br/>
				<form action="">
					<input type="checkbox" id="percorsoCompleto_<?=$bus->id?>" name="percorsoCompleto_<?=$bus->id?>"/> <?=$dizionario['pre']['completo']?>
				</form>
				<script type="text/javascript">
					$("#percorsoCompleto_<?=$bus->id?>").click(function(){
						if($(this).is(":checked")){
							$(".tabellaPercorso_<?=$bus->id?> .percorsoCompleto").show();
						}else{
							$(".tabellaPercorso_<?=$bus->id?> .percorsoCompleto").hide();
						}
					});
				</script>
			</div> 
		</div>
			
			<br/>
			<?php 
			}
		?>
			<?=$dizionario['pre']['comuni_inter']?>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
	    		<thead>
		            <tr class="rowIntestazione">
		            	<td><?=$dizionario['generale']['comune']?></td>
						<td><?=$dizionario['pre']['bus_arrivo']?></td>
						<td><?=$dizionario['pre']['bus_partenza']?></td>
					</tr>
				</thead>
				<tbody>
					<?php
					$f = new Fermata();
					$f->conn = $db;
					$sql = "SELECT Distinct(Comune) FROM RT_GestioneOttimizzataNodo 
							WHERE LineaId=$lineaId 
							AND CorsaId=$CorsaId 
							AND CorsaDataPartenza='$DataPartenza'
							ORDER BY Ordine";
					$rows = $db->fetch_array($sql);
					foreach ($rows as $key => $value){
						$comune = $gruppo->graph->nodes[$value['Comune']];
						if($f->isInterscambio($comune->id)==true && (sizeof($comune->busArrivo)>1 || sizeof($comune->busPartenza)>1)){
							?>
							<tr>
								<td>
									<?php
									$c = new Comune($comune->id);
									$c->conn = $db;
									$c->inizializzaDatiGenerali();
									echo $c->Comune;
									?>
								</td>
								<td>
									<?php
									foreach ($comune->busArrivo as $bus){
										$sql = "SELECT COUNT(*) as tot FROM RT_GestioneOttimizzataPasseggeri 
												WHERE LineaId=$lineaId 
												AND CorsaId=$CorsaId 
												AND CorsaDataPartenza='$DataPartenza' 
												AND Comune=$comune->id 
												AND Bus=$bus";
										$cont = $db->query_first($sql);
										echo "Barca ".$gruppo->flotta[$bus]->nome." (".$cont['tot'].") - ";
									}
									?>
								</td>
								<td>
									<?php
									foreach ($comune->busPartenza as $bus){
										$sql = "SELECT COUNT(*) as tot FROM RT_GestioneOttimizzataPasseggeri
												WHERE LineaId=$lineaId
												AND CorsaId=$CorsaId
												AND CorsaDataPartenza='$DataPartenza'
												AND Comune=$comune->id
												AND Bus=$bus";
										$cont = $db->query_first($sql);
										echo "Barca ".$gruppo->flotta[$bus]->nome." (".$cont['tot'].") - ";
									}
									?>
								</td>
							</tr>
							<?php
						}
					} 
					?>
				</tbody>
			</table>		
				
		<?php
		
		$page = new Form();
		?>
		<form id="application_form_gestione" class="brain_formModifica" name="application_form_gestion" method="post" action="#">
			<div class="brain_data-content"><?
				$page->create_textbox_hidden("action","DettaglioPasseggeroViaggio");
				$page->create_textbox_hidden("corsaId", $CorsaId);
				$page->create_textbox_hidden("lineaId", $lineaId);
				$page->create_textbox_hidden("dataPartenza", $DataPartenza);
				
				$arr_passeggeri = array();
				$rows = $prenDett->getPrenotazioniDettaglio($lineaId, $CorsaId, $DataPartenza);
				
				foreach ($rows as $key => $value){
					$arr_passeggeri[$key]['PasseggeroId']=$value['PrenotazioneNumero'];
					$arr_passeggeri[$key]['Passeggero']=$value['ClienteNome']." (".$value["Nome"].")";
				}
				$page->create_select("Passeggero","passeggero","passeggero","brain_campoForm",$arr_passeggeri,-1,"PasseggeroId","Passeggero",null,1);
			?></div>
			
			<div class="divSubmit"><?
				$page->create_button("dettaglioPasseggero","dettaglioPasseggero","Dettaglio","brain_salva","submit");
			?></div>
		</form>

		
		<a id="reset" href="#"><?=$dizionario['pre']['reset']?></a>
		
		<script type="text/javascript">
			$('#reset').click(function (event) {
				$("body").append('<div id="dialog-reset_wait" title="<?=$dizionario['generale']['caricamento_in_corso']?>" style="display: none;">'+
			  			'<p>'+
							'<?=$dizionario['pre']['attendere']?>'+
						'</p>'+
					'</div>');
				var dataPartenza = "<?php echo $DataPartenza?>";
				
				$.ajax({
					type: "POST",
					url: "/protected/modules/rt_previaggio/previaggio_action.php",
					data: {'action': "ResetOrganizzazione", 'LineaId':<?=$lineaId?>, 'CorsaId':<?=$CorsaId?>, 'DataPartenza': dataPartenza},
					success: function(data) {
						$("#dialog-reset_wait").dialog("destroy");
           	          	$("#dialog-reset_wait").remove();
						loadMainContent('rt_previaggio','previaggio.php?do=add&step=0&CorsaId=<?=$CorsaId?>&DataPartenza='+dataPartenza,this);
					},
					beforeSend: function(){
						$("#dialog-reset_wait" ).dialog({
			            	closeOnEscape: false,
			            	open: function(event, ui) { 
				            	$(".ui-dialog-titlebar-close").hide();         	          	
			            	},
			           		resizable: false,
			           	    modal: true
		            	});
						
					}
				});
				
				event.preventDefault();

				
			});
		</script>
		
	</div>	
	<?php
	$db->close();
}

function prenotazioni_da_confermare()
{
global $user,$HtmlCommon, $dizionario;
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
$db= new Database();
$db->connect();
$corsaobj=new Corsa($CorsaId);
$corsaobj->conn=$db;
$corsaobj->inizializzaDatiGenerali();
$arr_corsa=$corsaobj->DatiGenerali;

$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
$titolo=$titolo." / Consolidamento";

$HtmlCommon->html_titolo_pagina($titolo);
$HtmlCommon->html_titolo_box($titolo);
$DataPartenzaF=$_REQUEST['DataPartenza'];






carica_menu_percorso(2);



include_once("previaggio_validator.php");    
?>
<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

<form id="application_form" name="application_form"  method="post" action="#">
<div class="brain_formModifica">
     <div class="brain_data-content">     
         <h2>Prenotazioni da confermare o in lista d'attesa</h2>
         
         <?
         $sql="select Numero from  RT_ViewElencoPrenotazioneDaConfermareNumero where CorsaDataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId";
     //    echo($sql);
         $row=$db->query_first($sql);
         $n_daconfermare=0;
                 if ($row['Numero']>0)
                    $n_daconfermare=$row['Numero']; 
                 
            $sql="select Numero from RT_ViewElencoPrenotazioneAttesaNumero where CorsaDataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId";
         $row=$db->query_first($sql);
         $n_lista=0;
                 if ($row['Numero']>0)
                    $n_lista=$row['Numero'];       
                 
         if ($n_daconfermare>0)
         {
             ?>
         <h1>Per la corsa corrente sono presenti <?=$n_daconfermare?> passeggeri da confermare</h1>
             <?
         }
         
          if ($n_lista>0)
         {
             ?>
         <h1>Per la corsa corrente sono presenti <?=$n_lista?> passeggeri in lista d'attesa</h1>
             <?
         }
         if ($n_lista+$n_daconfermare==0)
         print("<h1>Nessun passeggero da verificare</h1>");
         ?>
         
     </div>
</div>
     <? //spara_pulsanti_wizard(0) ?>
    
    
</form>
</div>

<?
    
    
}

function consolida_corsa()
{
global $user,$HtmlCommon;
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
$db= new Database();
$db->connect();
$corsaobj=new Corsa($CorsaId);
$corsaobj->conn=$db;
$corsaobj->inizializzaDatiGenerali();
$arr_corsa=$corsaobj->DatiGenerali;

$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
$titolo=$titolo." / Consolidamento";

$HtmlCommon->html_titolo_pagina($titolo);
$HtmlCommon->html_titolo_box($titolo);
$DataPartenzaF=$_REQUEST['DataPartenza'];


carica_menu_percorso(6);



$sql="select * from RT_CorsaConsolidamento where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataCorsa='$DataPartenza' and Stato=1 and Cancella=0";



  $row = $db->query_first($sql);
  $consolidamento=false; 
  $DataConsolidamento="";
  if ( (!empty($row['ConsolidamentoCorsaId'])) and ($row['ConsolidamentoCorsaId']>0))
  {
         $consolidamento=true;
         $DataConsolidamento=$row['DataIns'];
  }
      

include_once("previaggio_validator.php");    
?>
<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

<form id="application_form" name="application_form"  method="post" action="#">
<div class="brain_formModifica">
     <div class="brain_data-content">     
         <h2>Consolidamento Corsa</h2>
         
         <? if ($consolidamento==true)
         {
             ?>
         
         <p>La corsa del <?=$DataPartenza?>  � gi� stata consolidata il <?=$DataConsolidamento?></p>
         
      
         <?
         }
         else
         {
             ?>
            Corsa del <?=$DataPartenza?> da consolidare<br />
              <a href="#" title="Consolida la corsa" onclick="javascript:ConsolidaCorsa('<?=$CorsaId?>','<?=$DataPartenza?>','<?=$DataPartenzaF?>');">Consolida Corsa</a> 
        
             <?
         }
         ?>
         
     </div>
</div>
     <? //spara_pulsanti_wizard(0) ?>
    
    
</form>
</div>

<?
    
    
}

function inizializzazione()
{
global $user,$HtmlCommon;
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
$db= new Database();
$db->connect();
$corsaobj=new Corsa($CorsaId);
$corsaobj->conn=$db;
$corsaobj->inizializzaDatiGenerali();
$arr_corsa=$corsaobj->DatiGenerali;

$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
$titolo=$titolo." / Inizializzazione corsa";

$HtmlCommon->html_titolo_pagina($titolo);
$HtmlCommon->html_titolo_box($titolo);
$DataPartenzaF=$_REQUEST['DataPartenza'];


carica_menu_percorso(1);



$sql="select * from RT_CorsaInizioPreparazione where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataCorsa='$DataPartenza' and Stato=1 and Cancella=0";



  $row = $db->query_first($sql);
  $consolidamento=false; 
  $DataConsolidamento="";
  if ( (!empty($row['InizioPreparazioneCorsaId'])) and ($row['InizioPreparazioneCorsaId']>0))
  {
         $consolidamento=true;
         $DataConsolidamento=$row['DataIns'];
  }
      

include_once("previaggio_validator.php");    
?>
<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

<form id="application_form" name="application_form"  method="post" action="#">
<div class="brain_formModifica">
     <div class="brain_data-content">     
         <h2>Inizializzazione preparazione</h2>
         
         <? if ($consolidamento==true)
         {
             ?>
         
         <p>La preparazione della corsa � iniziata in data <strong><?=$DataConsolidamento?></strong></p>
            <a href="#" title="Inizializza ora" onclick="javascript:InizializzaCorsa('<?=$CorsaId?>','<?=$DataPartenza?>','<?=$DataPartenzaF?>');">Aggiorna con data e ora corrente</a> 
        
      
         <?
         }
         else
         {
             ?>
             La preparazione della corsa � da inizializzare. <br />
              <a href="#" title="Inizializza ora" onclick="javascript:InizializzaCorsa('<?=$CorsaId?>','<?=$DataPartenza?>','<?=$DataPartenzaF?>');">Inizializza ora</a> 
        
             <?
         }
         ?>
         <br /><br />
              <div class="boxContentHP">
                <table class="tabella_modulo">
                	<thead>
                    	<tr>
                            <td>Cliente</td>
                            <td>Da</td>
                            <td>A</td>
                           <td>Tipo modifica</td>
                           <td>PDF</td>
                        </tr>
                    </thead>
                	<tbody>
                <?
                  $sql="select * from RT_CorseElencoModificheDettaglio where CorsaDataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId";
                //  echo($sql);
                  $ArrObject = $db->fetch_array($sql);
                  $c=0;
?>
                        <h2>Modifiche avvenute post inizializzazione</h2>
<?

//    print_r($ArrObject);
                  while($c<sizeof($ArrObject))
                  {
                      $ClienteNome=$ArrObject[$c]['ClienteNome'];
                      $ComuneSalita=$ArrObject[$c]['ComuneSalita'];
                      $ComuneDiscesa=$ArrObject[$c]['ComuneDiscesa'];
                      $ClienteNome=$ArrObject[$c]['ClienteNome'];
                      $PrenotazioneStato=$ArrObject[$c]['PrenotazioneStato'];
                      $PrenId=$ArrObject[$c]['PrenotazioneId'];
                     
					  if(($c%2)==0){
						$odd_even = "odd";  
					  }
					  else {
						$odd_even = "even";
					  }
                    ?>
                    <tr class="<?=$odd_even?>">
                    	<td><?=$ClienteNome?></td>
                        <td><?=$ComuneSalita?></td>
                        <td><?=$ComuneDiscesa?></td>
                        <td><?=$PrenotazioneStato?></td>
                        <td><a target="_new" class="icona_stampa" href="/protected/modules/rt_previaggio/stampa_titoli_di_viaggio_pdf.php?added=true&type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&seller_type=1&PrenotazioneId=<?=$PrenId?>"><img src="../images/print-icon.png" alt="Stampa PDF"/> PDF</a>
            </td>
                    </tr>
                    <?
                     $c++; 
                  }
?>
        			</tbody>
                </table>                        
                
            </div>
              
              
     </div>
</div>
     <? //spara_pulsanti_wizard(0) ?>
    
    
</form>
</div>

<?
    
    
}

function disposizione_posti()
{
global $user,$HtmlCommon;
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
$db= new Database();
$db->connect();
$corsaobj=new Corsa($CorsaId);
$corsaobj->conn=$db;
$corsaobj->inizializzaDatiGenerali();
$arr_corsa=$corsaobj->DatiGenerali;

$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
$titolo=$titolo." / Disposizine dei posti";
$page=new Form();
$HtmlCommon->html_titolo_pagina($titolo);
$HtmlCommon->html_titolo_box($titolo);
$DataPartenzaF=$_REQUEST['DataPartenza'];

carica_menu_percorso(5);


 $storico=new StoricoOperazioni();
  $storico->conn=$db;   

unset($_SESSION['PREPARAZIONE_BUS']);
$_SESSION['PREPARAZIONE_BUS']=array();

//prelevo tutte le tratte
// prelevo tutti i prenotati
include_once("previaggio_validator.php");    
?>
	
	
	
	<style>
	h1 { padding: .2em; margin: 0; }
	#products { float:left; width: 500px; margin-right: 2em; }
        #products1 { float:left; width: 500px; margin-right: 2em; }
	#cart { width: 200px; float: left; }
	/* style the list to maximize the droppable hitarea */
        #catalog1 ol { margin: 0; padding: 1em 0 1em 3em; }
	#catalog ol { margin: 0; padding: 1em 0 1em 3em; }
        
        .ui-accordion-content { height: "auto";}
	</style>
        
        <script>
	
	</script>
        
	<script>
            
            
            
	$(function() {
		$( "#catalog" ).accordion({
			autoHeight: false,
			navigation: true
		});
		
                $( "#catalog tr" ).draggable({
			appendTo: "body"
			//helper: "clone"
		});
                
                $( "#catalog1" ).accordion({
			autoHeight: false,
			navigation: true
		});
		
		$( ".accordion_bus" ).accordion({
			autoHeight: false,
			navigation: true,
			collapsible: true,
			autoHeight: false,
			navigation: true,
			header: '.accordion_header'
		});
		
                $( "#catalog1 tr" ).draggable({
			appendTo: "body"
			//helper: "clone"
		});
		
	});
	</script>
        
        
 <div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

<form id="application_form" name="application_form"  method="post" action="#">
      <input type="hidden" id="DataPartenza" name="DataPartenza" value="<?=$DataPartenza?>">
      <input type="hidden" id="CorsaId" name="CorsaId" value="<?=$CorsaId?>">
       <input type="hidden" name="action" value="create_dettaglio_autobus">
       <input type="hidden" name="step_successivo" id="step_successivo" value="4">
              
<div class="brain_formModifica">
     <div class="brain_data-content">      
<div class="demo">
	
<div id="products">	
<? 

$sql="select distinct(BusId) as BusIden from  RT_PreparazioneBus where CorsaId=$CorsaId and DataPartenza='$DataPartenza' and  OdcIdRef=$user->OdcId order by BusId desc";

$ArrObject = $db->fetch_array($sql);

$numeropullman=sizeof($ArrObject); 
$contabus=0;
$nt=0;
// 
?>
<!-- inizio accordion -->
<div class="accordion_bus">
<?
while ($nt<$numeropullman)
{
   
    $BusId=$ArrObject[$nt]['BusIden'];
   
    
    $tb=new TipologiaBus($BusId);
    $tb->conn=$db;
    $tb->inizializzaDatiGenerali();
    $arr_tb=$tb->DatiGenerali;
    
    $NumeroPiani=$arr_tb['NumeroPiani'];
    $NumeroColonne=$arr_tb['Colonne'];
    $NumeroRighe=$arr_tb['Righe'];
    
    
    $sql="select distinct(BusNumero) as BusNumeroIden from  RT_PreparazioneBus where CorsaId=$CorsaId and DataPartenza='$DataPartenza' and  OdcIdRef=$user->OdcId and BusId=$BusId order by BusNumeroIden asc";
   
    $ArrObjectNP = $db->fetch_array($sql);

    $numeropullman_tipo=sizeof($ArrObjectNP); 
    
    $nptipo=0;
    
    while ($nptipo<$numeropullman_tipo)
    {
        $contabus++;
    $busNumeroT=$ArrObjectNP[$nptipo]['BusNumeroIden'];
    
    ?>
    <br />
    <br />
    <h1 class="accordion_header">Pullman <?=$arr_tb['TipologiaBus']?> N. <?=$busNumeroT?></h1>
    <div class="accordion_content">
        
        <div class="form_autisti">
               
               <?
                    $Pullman="";
                    $Autista1="";
                    $Autista2="";
                    $Telefono="";
                    $NumeroPullman=$contabus;
                $sq="select * from RT_PreparazioneBusAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$busNumeroT";
                $r=$db->query_first($sq);
                
                
                
                if ($r['BusId']>0)
                {
                    $Pullman=$r['Pullman'];
                    $Autista1=$r['Autista1'];
                    $Autista2=$r['Autista2'];
                    $Telefono=$r['Telefono'];
                    $NumeroPullman=$r['NumeroPullman'];
                    
                }
                 $page->create_textbox("NumeroPullman","N�","DettaglioPullman[NumeroPullman_".$BusId."_".$busNumeroT."]",$NumeroPullman,1,"brain_campiform",array("class"=>"'required'"));
               
                $page->create_textbox("Pullman","Pullman","DettaglioPullman[Pullman_".$BusId."_".$busNumeroT."]",$Pullman,0,"brain_campiform",null);
                $page->create_textbox("Telefono","Telefono","DettaglioPullman[Telefono_".$BusId."_".$busNumeroT."]",$Telefono,0,"brain_campiform",null);
                  print("<br style=\"clear:both;\"/>");
                $page->create_textbox("Autista1","Autista1","DettaglioPullman[Autista1_".$BusId."_".$busNumeroT."]",$Autista1,0,"brain_campiform",null);
                $page->create_textbox("Autista2","Autista2","DettaglioPullman[Autista2_".$BusId."_".$busNumeroT."]",$Autista2,0,"brain_campiform",null);
              
                 print("<br style=\"clear:both;\"/>");
                 $page->create_button("Registra dati barca","Salva","Salva","brain_salva","submit"); ?>
        
            
        </div>
        
      
    <?
    print("<br style=\"clear:both;\"/>");
    
    $npiani=1;
    $checkesclusi=true;
     $str_prenotazioni_numero="0";
     while ($npiani<=$NumeroPiani)
     {
         
  
  if ($checkesclusi)     
  {
    $checkesclusi=false;  
   $sql_esclusi="SELECT
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.PrenotazioneId,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero,
RT_Prenotazione.ClienteNome,
RT_Prenotazione.ClienteSessoId,
RT_PrenotazionePosto.PrenotazioneNumeroId,
RT_PrenotazionePosto.Posto,
RT_PrenotazionePosto.PreferenzaPiano,
RT_PrenotazionePosto.DescrizionePosto
FROM
RT_PreparazioneBus
INNER JOIN RT_Prenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId
INNER JOIN RT_PrenotazionePosto ON RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePosto.PrenotazioneId AND RT_PreparazioneBus.CorsaId = RT_PrenotazionePosto.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_PrenotazionePosto.DataPartenza
LEFT JOIN RT_TipologiaBusDettaglioPosto ON RT_PrenotazionePosto.Riga = RT_TipologiaBusDettaglioPosto.Riga AND RT_PrenotazionePosto.Colonna = RT_TipologiaBusDettaglioPosto.Colonna AND RT_PrenotazionePosto.Piano = RT_TipologiaBusDettaglioPosto.NumeroPiano AND RT_PreparazioneBus.BusId = RT_TipologiaBusDettaglioPosto.TipologiaBusId
WHERE
(RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3)  and BusId=$BusId and BusNumero=$busNumeroT and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza' and RT_PreparazioneBus.OdcIdRef=$user->OdcId 
and RT_TipologiaBusDettaglioPosto.TipologiaBusDettaglioPostoId IS NULL";
 $nesclusi=0; 
 $ArrObjectEsclusi = $db->fetch_array($sql_esclusi);     
 $lung=sizeof($ArrObjectEsclusi);
 
 if ($lung>0)
     {
 ?>
        <br />
            <h2>Esclusi</h2>
    <table style="width:97%;" cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                                <tbody>
 <?       
 while($nesclusi<sizeof($ArrObjectEsclusi))
     {
      $PrenotazionePostoId=$ArrObjectEsclusi[$nesclusi]['PrenotazionePostoId'];
      $Preferenza=$ArrObjectEsclusi[$nesclusi]['PreferenzaPiano'];
      $Pid=$ArrObjectEsclusi[$nesclusi]['PrenotazioneId'];
      $ClienteNome=$ArrObjectEsclusi[$nesclusi]['ClienteNome'];
      $ClienteSessoId=$ArrObjectEsclusi[$nesclusi]['ClienteSessoId'];
      $TipoPrenotazione=$ArrObjectEsclusi[$nesclusi]['TipoPrenotazione'];
      $PrenotazioneNumeroId=$ArrObjectEsclusi[$nesclusi]['PrenotazioneNumeroId'];
      $DescrizionePosto=$ArrObjectEsclusi[$nesclusi]['DescrizionePosto'];
      $PreferenzaPiano=$ArrObjectEsclusi[$nesclusi]['PreferenzaPiano'];
      $Posto=$ArrObjectEsclusi[$nesclusi]['Posto'];
      $pref="No";
      if ($PreferenzaPiano==1)
          $pref="Si";
                           
                                $classe_sesso="nd";
                            if ($ClienteSessoId==1)
                                $classe_sesso="maschio";
                            elseif ($ClienteSessoId==2)
                            $classe_sesso="femmina";   
     
    ?>
     
    <tr>                           
    <td>    
    <div class="trash ui-widget-content ui-state-default ui-droppable classe_posti" id="Posto_conta_<?=$nesclusi?>">
	 <ul class="gallery ui-helper-reset ui-droppable">     
         <li class="ui-widget-content ui-corner-tr ui-draggable <?=$classe_sesso?>" id="PrenotazioneId_<?=$PrenotazioneNumeroId?>" style="position: relative;">
         <? 
     
             echo("<strong>".$ClienteNome." (A) </strong>");
             echo("  Numero Posto:".$Posto." Piano Inferiore: ".$pref);
         
         ?>
         </li>
         </ul>
    </div> 
    </td>
    </tr>                             
    <? 
    
    
                            
                            $nesclusi++;
    }  
  }
         
     ?>
    </tbody>
    </table>
            
 <?           
  }
  ?>
    <br />
   
    <h2>Piano n. <?=$npiani?></h2>
    <table style="width:97%;" cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                                <tbody>
                                <tr class="rowIntestazione">
                                    <td></td>
                             <?
                        $i=0;
                        $alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
                          while ($i< $NumeroColonne)
                            {
                             ?>
                             <td><?=$alphabet[$i]?> </td>
                            <?  
                              $i++;
                            }
                          ?>
                                    </tr>
                       <?
                     
                        $i=0;
                       
                          while ($i< $NumeroRighe)
                            {
                             
                               ?>
                             <tr>
                                 <td class="cella_posto"><?=$i+1?></td>
                             <?
                             $n=0;
                             while ($n< $NumeroColonne)
                             {
                               
                                $fisso="";
                                $percentuale="";
                                $rigacorrente=$i+1;
                                $colonnacorrente=$n+1;
                                $sql="Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and TipologiaBusId=$BusId and OdcIdRef=$user->OdcId";
                               // echo($sql);
                                $row1 = $db->query_first($sql);
                                $NumeroPosto="";
                                $DescrizionePosto="";
                                if (!empty($row1['TipologiaBusId']))
                                {
                                   $NumeroPosto=$row1['NumeroPosto'];
                                   $DescrizionePosto=$row1['DescrizionePosto'];
                                }
                                
                               
                               
                                  //  echo($sql);
                               
                                     
                             ?>
                              <td class="cella_posto">
                         
                         
		
	<? 
       ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);  
       
        if ($NumeroPosto>0)
        {
                           // $sql="Select * from RT_FoglioCaricoPosti where PrenotazioneNumeroId not in($str_prenotazioni_numero) and  BusId=$BusId and BusNumero=$busNumeroT and Riga=$rigacorrente and Colonna=$colonnacorrente and CorsaId=$CorsaId and DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId";
                        
                            
$sql="SELECT
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.PrenotazioneId,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero,
RT_PreparazioneBus.OdcIdRef,
RT_PrenotazionePosto.Riga,
RT_PrenotazionePosto.Colonna,
RT_PrenotazionePosto.Posto,
RT_PrenotazionePosto.DescrizionePosto,
RT_PrenotazionePosto.Piano,
RT_PrenotazionePosto.PreferenzaPiano,
RT_PrenotazionePosto.TipoPrenotazione,
RT_Prenotazione.TipoViaggioId,
RT_Prenotazione.PrenotazioneModPre,
RT_Prenotazione.ClienteNome,
RT_Prenotazione.ClienteSessoId,
RT_Prenotazione.ClienteCellulare,
RT_PrenotazionePosto.PrenotazionePostoId,
PrenotazioneNumeroId
FROM
RT_PreparazioneBus
INNER JOIN RT_PrenotazionePosto ON RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePosto.PrenotazioneId AND RT_PreparazioneBus.CorsaId = RT_PrenotazionePosto.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_PrenotazionePosto.DataPartenza
INNER JOIN RT_Prenotazione ON RT_PrenotazionePosto.PrenotazioneId = RT_Prenotazione.PrenotazioneId
WHERE
(RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3) and Piano=$npiani and BusId=$BusId and BusNumero=$busNumeroT and Riga=$rigacorrente and Colonna=$colonnacorrente and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza' and RT_PreparazioneBus.OdcIdRef=$user->OdcId
GROUP BY
RT_PreparazioneBus.PrenotazioneId
                                    
                                    ";   

//die($sql);

 /*
*/
                            
                            
                            $ArrObjectDup = $db->fetch_array($sql);
                           // $row2 = $db->query_first($sql);
                             $isReserved=0;
                            
            
           
             
         if (sizeof($ArrObjectDup)>0)    
            {
             
             if ($npiani==1)
                 $str_prenotazioni_numero.=",".$PrenotazioneNumeroId;
          
             
           $dup=0;
             $Preferenza=0;
                             $ClienteNome="";
                             $ClienteSessoId=0;
                             $PrenotazionePostoId=0;
                             $TipoPrenotazione=0;
                             $PrenotazioneNumeroId=0;
                           
           while($dup<  sizeof($ArrObjectDup))
            { 
               
             $Pid=0;
                                  $isReserved=1;
                                  $PrenotazionePostoId=$ArrObjectDup[$dup]['PrenotazionePostoId'];
                                  $Preferenza=$ArrObjectDup[$dup]['PreferenzaPiano'];
                                  $Pid=$ArrObjectDup[$dup]['PrenotazioneId'];
                                  $ClienteNome=$ArrObjectDup[$dup]['ClienteNome'];
                                  $ClienteSessoId=$ArrObjectDup[$dup]['ClienteSessoId'];
                                  $TipoPrenotazione=$ArrObjectDup[$dup]['TipoPrenotazione'];
                                  $PrenotazioneNumeroId=$ArrObjectDup[$dup]['PrenotazioneNumeroId'];
                           
                                $classe_sesso="nd";
                            if ($ClienteSessoId==1)
                                $classe_sesso="maschio";
                            elseif ($ClienteSessoId==2)
                            $classe_sesso="femmina";   
               
               
               
               
            ?>
          <div class="trash ui-widget-content ui-state-default ui-droppable classe_posti" id="Posto_<?=$rigacorrente."_".$colonnacorrente."_".$npiani?>">
	 <ul class="gallery ui-helper-reset ui-droppable">     
         <li class="ui-widget-content ui-corner-tr ui-draggable <?=$classe_sesso?>" id="PrenotazioneId_<?=$PrenotazioneNumeroId?>" style="position: relative;">
         <? 
         if ($TipoPrenotazione==1)
             echo("<strong>".$ClienteNome." (A) </strong>");
         else
             echo($ClienteNome);
         
         ?>
         </li>
         </ul>
        <?
        $dup++;
         }
            } 
            else
            {
             // seleziono le prenotazioni senza posto   
                $ClienteNome="";
                
                         //   $sql="Select * from RT_FoglioBusCaricoPostiNonAssegnati where ((Riga=$rigacorrente and Colonna=$colonnacorrente) or (Riga is null  Posto>0)) and  BusNumero=$busNumeroT and CorsaId=$CorsaId and DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId";
                         //   $sql="Select * from RT_FoglioCaricoPosti where BusNumero=$busNumeroT and BusId=$BusId and Riga is Null  and CorsaId=$CorsaId and DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId";

               
                
$sql="SELECT RT_PreparazioneBus.CorsaId, RT_PreparazioneBus.DataPartenza, RT_PreparazioneBus.PrenotazioneId, RT_PreparazioneBus.BusId, RT_PreparazioneBus.BusNumero, RT_PreparazioneBus.OdcIdRef, RT_Prenotazione.ClienteNome, RT_Prenotazione.ClienteSessoId, RT_Prenotazione.ClienteCellulare, RT_Prenotazione.ClienteFidelityCardId, RT_Prenotazione.PrenotazioneModPre, RT_Prenotazione.TipoViaggioId, RT_PrenotazioneNumero.PrenotazioneNumeroId, RT_PrenotazionePosto.CorsaId, RT_PrenotazionePosto.DataPartenza, RT_PrenotazionePosto.Riga, RT_PrenotazionePosto.Colonna, RT_PrenotazionePosto.Posto, RT_PrenotazionePosto.DescrizionePosto, RT_PrenotazionePosto.Piano, RT_PrenotazionePosto.PreferenzaPiano, RT_PrenotazionePosto.TipoPrenotazione
FROM
RT_PreparazioneBus
INNER JOIN RT_Prenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId
INNER JOIN RT_PrenotazioneNumero ON RT_Prenotazione.PrenotazioneId = RT_PrenotazioneNumero.PrenotazioneId
LEFT JOIN RT_PrenotazionePosto ON RT_PrenotazioneNumero.PrenotazioneNumeroId = RT_PrenotazionePosto.PrenotazioneNumeroId AND RT_PreparazioneBus.CorsaId = RT_PrenotazionePosto.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_PrenotazionePosto.DataPartenza
WHERE
(RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3) and BusId=$BusId and BusNumero=$busNumeroT and Riga IS NULL and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza' and RT_PreparazioneBus.OdcIdRef=$user->OdcId";    
         
                            $row2 = $db->query_first($sql);
                            $isReserved=0;
                             $Pid=0;
                              $PNid=0;
                             $Preferenza=0;
                             $ClienteNome="";
                             $ClienteSessoId=0;
                             $PrenotazionePostoId=0;
                              if (!empty($row2['OdcIdRef']))
                              {
                                  
                                  $isReserved=1;
                                  $Preferenza=$row2['PreferenzaPiano'];
                                  $Pid=$row2['PrenotazioneId'];
                                   $PNid=$row2['PrenotazioneNumeroId'];
                                  $ClienteNome=$row2['ClienteNome'];
                                    $ClienteSessoId=$row2['ClienteSessoId'];
                                  $d1=null;
                                $d1['CorsaId']=$CorsaId;
                                $d1['DataPartenza']=$DataPartenza;
                                $d1['Piano']=$npiani;
                                $d1['PreferenzaPiano']=0;
                                $d1['Riga']=$rigacorrente;
                                $d1['Colonna']=$colonnacorrente;
                                $d1['Posto']=$NumeroPosto;
                              //  $d1['DescrizionePosto']=$DescrizionePosto;
                                $d1['PrenotazioneId']=$Pid;
                                $d1['PrenotazioneNumeroId']=$PNid;
                                // print_r($d1);
                                 $d1=$storico->operazioni_insert($d1,$user);
                                 
                               
                                 $lastidA=$db->insert("RT_PrenotazionePosto", $d1);
                                 $PrenotazionePostoId=$PNid;
                                  
                                 
                                            
                              }
                              
                               $classe_sesso="nd";
         if ($ClienteSessoId==1)
             $classe_sesso="maschio";
         elseif ($ClienteSessoId==2)
         $classe_sesso="femmina";
         
                              ?>
              <div class="trash ui-widget-content ui-state-default ui-droppable classe_posti" id="Posto_<?=$rigacorrente."_".$colonnacorrente."_".$npiani?>">
                                <ul class="gallery ui-helper-reset ui-droppable">     
                                <? if ($Pid>0)
                                {?>
                                <li class="ui-widget-content ui-corner-tr ui-draggable <?=$classe_sesso?>" id="PrenotazioneId_<?=$PrenotazionePostoId?>" style="position: relative;">
                                <? echo($ClienteNome);?>
                                    
                                </li>
                                <?
                                }
                                ?>
                                    
                                </ul>
              <?
                
            }
            
            
            ?>
               
             <?
        }
        ?>
        
</div>    
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
    
    
    <?
         
   
         $npiani++;
     }
	 ?>
     </div><!-- fine content accordion -->
	 
     <?
     // fine accordion
     $nptipo++;
}
    
    
    
 $nt++;   
}    
    
    ?>   
                
 </div><!-- fine accordion_bus -->       
	</div>
</div>

</div><!-- End demo -->
     </div>
     </div>
     <?// spara_pulsanti_wizard(0) ?>
</form>

 </div>
	<style>
	gallery { float: left; width: 90%; min-height: 12em; } * html .gallery { height: 12em; } /* IE6 */
	.gallery.custom-state-active { background: #eee; }
	.gallery li { width: auto; padding: 0.4em; margin: 0 0.4em 0.4em 0; text-align: center; }
	.gallery li h5 { margin: 0 0 0.4em; cursor: move; }
	.gallery li a { float: right; }
	.gallery li a.ui-icon-zoomin { float: left; }
	.gallery li img { width: 100%; cursor: move; }

	.trash { float: left; width: 100%; min-height: 4em; padding: 1%;} * html .trash { height: 8em; } /* IE6 */
	.trash h4 { line-height: 16px; margin: 0 0 0.4em; }
	.trash h4 .ui-icon { float: left; }
	.trash .gallery h5 { display: none; }
	</style>
	<script>
	$(function() {
		// there's the gallery and the trash
		       var $gallery = $( ".gallery" ),
			$trash = $( "#trash1" );

		// let the gallery items be draggable
		$( "li", $gallery ).draggable({
			cancel: "a.ui-icon", // clicking an icon won't initiate dragging
			revert: "invalid", // when not dropped, the item will revert back to its initial position
			containment: $( ".brain_formModifica" ).length ? ".brain_formModifica" : "document", // stick to demo-frame if present
			helper: "clone",
			cursor: "move"
		});

		// let the trash be droppable, accepting the gallery items
                
                d=1;
                numerotratte=2;
               /* while (d<=numerotratte)
                    {*/
                      
                        $(".trash").droppable({
                         hoverClass: "ui-state-active",
                         accept: ".gallery > li",
			 activeClass: "ui-state-highlight",
			 drop: function( event, ui ) {
				deleteImage( ui.draggable,this );
                                
			}
                        });
                    /*    d=d+1;
                    }*/
                    
                
		

		// let the gallery be droppable as well, accepting items from the trash
		$gallery.droppable({
			accept: ".trash li",
			activeClass: "custom-state-active",
			drop: function( event, ui ) {
				recycleImage( ui.draggable );
			}
		});

		// image deletion function
		var recycle_icon = "";
                    function deleteImage( $item,oggetto ) {
                        TrattaNavetta=$(oggetto).attr("id");
                      $MoveTo = oggetto;
                      //Id=Oggetto.attr("id");
                      
                      PrenotazioneId=$item.attr("id");
                     
                    // aggiorno array disposizione  
                    
                    
                      
                     // alert(PrenotazioneId);
                      
                      $item.fadeOut(function() {
				var $list = $( "ul", $MoveTo ).length ?
					$( "ul", $MoveTo ) :
					$( "<ul class='gallery ui-helper-reset'/>" ).appendTo( $MoveTo );
                                 

				$item.find( "a.ui-icon-trash" ).remove();
                              
				$item.append( recycle_icon ).appendTo( $list ).fadeIn(function() {
                                    	$item
						//.animate({ width: "48px" })
						.find( "img" )
							.animate({ height: "0px" });
				});
			});
		
                PrenotazionePosto=PrenotazioneId+'_'+TrattaNavetta;
                CorsaId='<?=$CorsaId?>';
                DataPartenza='<?=$DataPartenza?>';
             
                 page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=DisponiPosti&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&PrenotazionePosto="+PrenotazionePosto;
                 $.get(page_to_load, function(data){
                 // alert(data);

                } );
                
                }

		// image recycle function
		var trash_icon = "<a href='link/to/trash/script/when/we/have/js/off' title='Delete this image' class='ui-icon ui-icon-trash'>Delete image</a>";
		function recycleImage( $item ) {
			$item.fadeOut(function() {
				$item
					.find( "a.ui-icon-refresh" )
						.remove()
					.end()
					//.css( "width", "96px")
					.append( trash_icon )
					.find( "img" )
						.css( "height", "72px" )
					.end()
					.appendTo( $gallery )
					.fadeIn();
			});
		}

		
		// resolve the icons behavior with event delegation
		$( "ul.gallery > li" ).click(function( event ) {
			var $item = $( this ),
				$target = $( event.target );

			if ( $target.is( "a.ui-icon-trash" ) ) {
				deleteImage( $item );
			} else if ( $target.is( "a.ui-icon-zoomin" ) ) {
				viewLargerImage( $target );
			} else if ( $target.is( "a.ui-icon-refresh" ) ) {
				recycleImage( $item );
			}

			return false;
		});
	});
        
        function AggiungiNavetta(TrattaId)
        {
           divsize=$("#tt_"+TrattaId+" > div > div:visible").size();
           k=(divsize)+1;
           $( "#Navetta_"+k+"_"+TrattaId).show();
            
            
        }
        
    
        function NascondiNavetta(k,TrattaId)
        {
           
            $("#Navetta_"+k+"_"+TrattaId).hide();
             
             
             /*divsize=$("#tt_"+TrattaId+" > div > div:visible").size();
             k=(divsize)+1;
             $( "#Navetta_"+k+"_"+TrattaId).show();*/
            
            
        }
        
        
	</script>


<?
   
}

function prepara_bus()
{
global $user,$HtmlCommon;
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
$db= new Database();
$db->connect();
$corsaobj=new Corsa($CorsaId);
$corsaobj->conn=$db;
$corsaobj->inizializzaDatiGenerali();
$arr_corsa=$corsaobj->DatiGenerali;

$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
$titolo=$titolo." / Preparazione Barca";

$HtmlCommon->html_titolo_pagina($titolo);
$HtmlCommon->html_titolo_box($titolo);
$DataPartenzaF=$_REQUEST['DataPartenza'];

$sql="select TotaleDaDefinireBus from RT_PreparazioneBusPaxDaDefinire where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza'";
  
  $row = $db->query_first($sql);
  $TotaleDaDefinire=0; 
  if (!empty($row['TotaleDaDefinireBus']))
         $TotaleDaDefinire=$row['TotaleDaDefinireBus'];


carica_menu_percorso(4);


 $storico=new StoricoOperazioni();
  $storico->conn=$db;   

unset($_SESSION['PREPARAZIONE_BUS']);
$_SESSION['PREPARAZIONE_BUS']=array();

//prelevo tutte le tratte
// prelevo tutti i prenotati
include_once("previaggio_validator.php");    
?>
	
	
	
	<style>
	h1 { padding: .2em; margin: 0; }
	#products { float:left; width: 500px; margin-right: 2em; }
        #products1 { float:left; width: 500px; margin-right: 2em; }
	#cart { width: 200px; float: left; }
	/* style the list to maximize the droppable hitarea */
        #catalog1 ol { margin: 0; padding: 1em 0 1em 3em; }
	#catalog ol { margin: 0; padding: 1em 0 1em 3em; }
        
        .ui-accordion-content { height: "auto";}
	</style>
        
       
        
	<script>
            
            
            
	$(function() {
		$( "#catalog" ).accordion({
			autoHeight: false,
			navigation: true
		});
                
		
                $( "#catalog tr" ).draggable({
			appendTo: "body"
			//helper: "clone"
		});
                
        $( "#catalog1" ).accordion({
			autoHeight: false,
			navigation: true
		});
		$(".link_hash").click(function(event){
			window.location.hash=this.hash;
		});
		
                $( "#catalog1 tr" ).draggable({
			appendTo: "body"
			//helper: "clone"
		});
		
	});
	</script>
        
        
 <div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

<form id="application_form" name="application_form"  method="post" action="#">
<div class="brain_formModifica">
     <div class="brain_data-content">      
<div class="demo">
	
<div id="products">
	<h1 class="ui-widget-header">Passeggeri da definire <?=$TotaleDaDefinire?></h1>	
                <div id="catalog">

<? 


$sql="select * from  RT_ElencoTipologiaBus where OdcIdRef=$user->OdcId order by TipologiaBusId desc";

$ArrObject = $db->fetch_array($sql);

$numerotratte=sizeof($ArrObject); 

$nt=0;
//while ($nt<$numerotratte)
include_once("previaggio_colum_filtering.php");
while ($nt<1)
{
    $TrattaId=$ArrObject[$nt]['TipologiaBusId'];
    $NomeTratta=$ArrObject[$nt]['TipologiaBus'];
    
    ?>

                    
	<div id="elenco_clienti">
            <!--label id="filtro_0" for="filtro_cell_0">Pax</label>            
            <label id="filtro_1" for="filtro_cell_1">Nome Cognome</label>            
            <label id="filtro_2" for="filtro_cell_2">Salita</label>
            <label id="filtro_3" for="filtro_cell_3">Discesa</label>
            <br/>
			<input type="text" name="filtro_cell_0" id="input_filtro_cell_0" />
            <input type="text" name="filtro_cell_1" id="input_filtro_cell_1" />
            <input type="text" name="filtro_cell_2" id="input_filtro_cell_2" />
            <input type="text" name="filtro_cell_3" id="input_filtro_cell_3" />
            <!--<input type="text" name="filtro" id="input_filtro" />-->
            <!--br/>
            <br/-->
		<!--	
        
        	<ul id="SelTratta1" class="gallery ui-helper-reset ui-helper-clearfix">   
	       		<li>
                </li>
        
    	-->
        <br/>
		<a href="#" id="sel_tutto" class="tasti_sel">Seleziona tutto</a>
        <a href="#" id="desel_tutto" class="tasti_sel">Deseleziona tutto</a>        
        <a href="#" id="sel_inverso" class="tasti_sel">Seleziona inverso</a>
        <br/><br/>
        <table id="SelTratta1" class="display gallery ui-helper-reset ui-helper-clearfix">
        	<thead>
            
            	<tr class="brain_tabellaTr">
                      <th width="3%">Pax</th>
                      <th width="35%">Cliente</th>
                      <th width="32%">Partenza</th>
                      <th width="32%">Destinazione</th>
                    
		</tr>
            
		<tr class="brain_tabellaFilter">
                    <th><input type="text" /></th> 
                    <th><input type="text" /></th> 
                    <th><input type="text" /></th> 
                    <th><input type="text" /></th> 
                    
		</tr>
	</thead>
        <tbody>
    
	<?
     
        $np=0;
        $numeropasseggeri=0;
        
        
         $sql="select * from RT_PrenotazioniNonOrganizzateBus where CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza' order by ClienteNome asc";
  // echo($sql);
    $ArrObjectP = $db->fetch_array($sql);
    $numeropasseggeri=sizeof($ArrObjectP);
        
while ($np<$numeropasseggeri)
{
    
   
      $ClienteNome=$ArrObjectP[$np]['ClienteNome'];
    $ClienteId=$ArrObjectP[$np]['PrenotazioneId'];
    $FermataSalita=$ArrObjectP[$np]['FermataSalita'];
    $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
    $Csalita=$ArrObjectP[$np]['ComuneSalita']." ".$FermataSalita;
    $Cdiscesa=$ArrObjectP[$np]['ComuneDiscesa']." ".$FermataDiscesa;
    $ClienteId=$ArrObjectP[$np]['PrenotazioneId'];
    $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
    ?>
    
    	<tr id="PrenotazioneId_<?=$ClienteId?>" class="dragme ui-widget-content ui-corner-tr multidraggable" style="position: relative;"> 
                    <td class="cell_0"><p><?=$TotalePaxPrenotati?></p></td>
                    <td class="cell_1"><?=$ClienteNome?></td>
                    <td class="cell_2"><?=$Csalita?></td>
                    <td class="cell_3"><?=$Cdiscesa?></td>
               <!--     <td><a href="link/to/trash/script/when/we/have/js/off" title="Elimina" class="ui-icon ui-icon-trash">rimuovi</a></td>-->
        </tr>

        <!--<li id="PrenotazioneId_<?=$ClienteId?>" class="ui-widget-content ui-corner-tr multidraggable">
             <table class="viaggio_elenco">
             	<tr> 
                    <td class="cell_0"><p><?=$TotalePaxPrenotati?></p></td>
                    <td class="cell_1"><?=$ClienteNome?></td>
                    <td class="cell_2"><?=$FermataSalita?></td>
                    <td class="cell_3"><?=$FermataDiscesa?></td>
               <!--     <td><a href="link/to/trash/script/when/we/have/js/off" title="Elimina" class="ui-icon ui-icon-trash">rimuovi</a></td>-->
               <!-- </tr>
              </table> -->
            
		<!--<strong><?=$TotalePaxPrenotati?> - <?=$ClienteNome?> (<?=$FermataSalita." / ".$FermataDiscesa?> </strong>
		<a href="link/to/trash/script/when/we/have/js/off" title="Elimina" class="ui-icon ui-icon-trash">rimuovi</a>-->
	<!-- </li> -->
        
        
<?
$np++;
}
?>
        <?

?>
	</tbody>
</table>
                    
 <? 
 $nt++;
 ?>
</div>
 <?
 } ?>                   
		
              
                  
	</div>
</div>
    
    
    <div id="products1">
	<h1 class="ui-widget-header">Bus</h1>	
	<div id="catalog1">
            
            
            
            <?
            $nt=0;
while ($nt<$numerotratte)
{
             $TrattaId=$ArrObject[$nt]['TipologiaBusId'];
             $NomeTratta=$ArrObject[$nt]['TipologiaBus'];
    
    ?>
            
		<h3 class="menuitem"><a class="link_hash" href="#<?=$TrattaId?>"><?=$NomeTratta?></a></h3>
		<div>
                          
                               
                    
			
<ul id="tt_<?=$TrattaId?>">
	<div class="demo ui-widget ui-helper-clearfix">
            

<a title="aggiungi bus" onclick="AggiungiBus('<?=$TrattaId?>');" class="brain_aggiungi est">aggiungi pullman</a>

<? // pullman preparati 
         $numero_navette=1; 
         $contanavette=1;
         
           $sql="select max(BusNumero) as countnavette from RT_PreparazioneBus where BusId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by BusId,DataPartenza,CorsaId";
           $row = $db->query_first($sql);
           $numero_navette=1;
           if (!empty($row['countnavette']))
           $numero_navette=$row['countnavette'];
           
          
      
         
         while ($contanavette<=$numero_navette)
         {
            
             
$sql="Select * from RT_FoglioCaricoTotalePaxPerPullman where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and BusId=$TrattaId and BusNumero=$contanavette and OdcIdRef=$user->OdcId";


$row_gestiti = $db->query_first($sql);
$PostiGestiti=0;

if (!empty($row_gestiti['OdcIdRef']))
   $PostiGestiti=$row_gestiti['TotalePosti'];
             
?>
        

<div id="Navetta_<?=$contanavette?>_<?=$TrattaId?>" class="trash ui-widget-content ui-state-default">
	<h4 class="ui-widget-header header_con_icone"><span class="pullman_header">Pullman n. <?=$contanavette?> (<?=$PostiGestiti?>)</span><br/>
            <a class="icona_rimuovi" onclick="javascript:NascondiBus('<?=$TrattaId?>')"><img src="../images/remove-icon.png" alt="Rimuovi"/></a> <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_etichette.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TipoBus=<?=$TrattaId?>&NumeroBus=<?=$contanavette?>"><img src="../images/print-icon.png" alt="Stampa"/> Etichet.</a>
            <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_carico.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TipoBus=<?=$TrattaId?>&NumeroBus=<?=$contanavette?>"><img src="../images/print-icon.png" alt="Stampa"/> Org.</a> <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_carico.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TipoBus=<?=$TrattaId?>&NumeroBus=<?=$contanavette?>&OnlyCarico=true"><img src="../images/print-icon.png" alt="Stampa"/> Car</a> 
            <a target="_new" class="icona_stampa" href="/protected/modules/rt_previaggio/stampa_titoli_di_viaggio.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TipoBus=<?=$TrattaId?>&NumeroBus=<?=$contanavette?>&seller_type=1"><img src="../images/print-icon.png" alt="Stampa"/> E-Tick</a>
            <a target="_new" class="icona_stampa" href="/protected/modules/rt_previaggio/stampa_titoli_di_viaggio_pdf.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TipoBus=<?=$TrattaId?>&NumeroBus=<?=$contanavette?>&seller_type=1"><img src="../images/print-icon.png" alt="Stampa PDF"/> PDF</a>
            
            <br class="clear:both" /></h4>
        <a href="#pul_<?=$contanavette?>_<?=$TrattaId?>"></a>
        <!--
        <ul class="gallery ui-helper-reset">
        -->
        <table class="gallery ui-helper-reset display">
        	<tbody>
            
            
         <?   
              $np=0;
        $numeropasseggeri=0;
        
        
         $sql="select * from RT_PreparazioneBusPerTipologia where  BusNumero=$contanavette and  CorsaId=$CorsaId and DataPartenza='$DataPartenza' and BusId=$TrattaId order by ClienteNome asc";
      //   echo($sql);
         
   
    $ArrObjectP = $db->fetch_array($sql);
    $numeropasseggeri=sizeof($ArrObjectP);
        
while ($np<$numeropasseggeri)
{
    
   
    $ClienteNome=$ArrObjectP[$np]['ClienteNome'];
    $ClienteId=$ArrObjectP[$np]['PrenotazioneId'];
    $FermataSalita=$ArrObjectP[$np]['FermataSalita'];
    $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
    $Csalita=$ArrObjectP[$np]['ComuneSalita'];
    $Cdiscesa=$ArrObjectP[$np]['ComuneDiscesa'];
    
    $ClienteId=$ArrObjectP[$np]['PrenotazioneId'];
    $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
    $_SESSION['PREPARAZIONE_BUS'][$ClienteId]=$TrattaId."_".$contanavette;
    $Class="even";
    
    if ( ($np%2)==0 ) { $Class = "odd"; }
    else {$Class = "even"; }      
    ?>

		<tr id="PrenotazioneId_<?=$ClienteId?>" class="ui-widget-content ui-corner-tr dragme <?=$Class?>"> 
                    <td class="cell_0"><?=$TotalePaxPrenotati?></td>
                    <td class="cell_1"><?=$ClienteNome?></td>
                    <td class="cell_2"><?=$Csalita?></td>
                    <td class="cell_3"><?=$Cdiscesa?></td>
               <!--     <td><a href="link/to/trash/script/when/we/have/js/off" title="Elimina" class="ui-icon ui-icon-trash">rimuovi</a></td>-->
        </tr>
    	<!--
        <li id="PrenotazioneId_<?=$ClienteId?>" class="ui-widget-content ui-corner-tr">
        	 <table class="viaggio_elenco">
             <tr> 
                    <td class="cell_0"><?=$TotalePaxPrenotati?></td>
                    <td class="cell_1"><?=$ClienteNome?></td>
                    <td class="cell_2"><?=$FermataSalita?></td>
                    <td class="cell_3"><?=$FermataDiscesa?></td>
               <!--     <td><a href="link/to/trash/script/when/we/have/js/off" title="Elimina" class="ui-icon ui-icon-trash">rimuovi</a></td>-->
             <!--/tr>
             </table>
		<!--<strong><?=$TotalePaxPrenotati?> - <?=$ClienteNome?> (<?=$FermataSalita." / ".$FermataDiscesa?> </strong>-->
		<!--<a href="link/to/trash/script/when/we/have/js/off" title="Elimina" class="ui-icon ui-icon-trash">rimuovi</a>-->
		<!--</li>-->
        
<?
$np++;
}
?>
         <!--</ul>-->
         </tbody>
      </table>

        
</div>
<? 

$contanavette++;
}                                

$k=$contanavette;
while ($k<(10))
{
    ?>
    <div id="Navetta_<?=$k."_".$TrattaId?>" class="trash ui-widget-content ui-state-default" style="display:none">
	<h4 class="ui-widget-header header_con_icone"> <span class="pullman_header">Pullman n. <?=$k?> (0)</span><br/><a class="icona_rimuovi" onclick="javascript:NascondiBus('<?=$k?>','<?=$TrattaId?>')"><img src="../images/remove-icon.png" alt="Rimuovi"/></a> <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_carico.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TipoBus=<?=$TrattaId?>&NumeroBus=<?=$k?>"><img src="../images/print-icon.png" alt="Stampa"/> Stampa 1</a> <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_titoli_di_viaggio.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TipoBus=<?=$TrattaId?>&NumeroBus=<?=$contanavette?>&seller_type=1"><img src="../images/print-icon.png" alt="Stampa"/> E-Ticket</a><br style="clear:both"/></h4>
       
    </div>        
    <?
    
    $k++;
}
     ?>       



</div><!-- End demo -->
        
	
</ul>
	
               
                 <? 
 $nt++;
 ?>
</div>
 <?
 } 
 $lastidA=$db->delete("RT_PreparazioneBus","CorsaId=$CorsaId and  DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId");

 
 $data=$_SESSION['PREPARAZIONE_BUS'];


 foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     
     
     $arr_chiave=explode('_', $valore);
     $BusId=$arr_chiave[0];
     $BusNumero=$arr_chiave[1];
     $PrenotazioneId=$chiave;
     
    
     $d1['PrenotazioneId']=$PrenotazioneId;
     $d1['BusId']=$BusId;
     $d1['BusNumero']=$BusNumero;
     $d1['CorsaId']=$CorsaId;
     $d1['DataPartenza']=$DataPartenza;
     
     
     
     if ($PrenotazioneId>0)
         { 
             $d1=$storico->operazioni_insert($d1,$user);
             $lastidA=$db->insert("RT_PreparazioneBus", $d1);
      }
 }
 
 
 ?> 
                
                
                
                
        
	</div>
</div>

</div><!-- End demo -->
     </div>
     </div>
     <?// spara_pulsanti_wizard(0) ?>
</form>

 </div>
	<style>
	 gallery { float: left; width: 90%; min-height: 12em; } * html .gallery { height: 12em; } /* IE6 */
	.gallery.custom-state-active { background: #eee; }
	.gallery li { width: auto; padding: 0.4em; margin: 0 0.4em 0.4em 0; text-align: center; }
	.gallery li h5 { margin: 0 0 0.4em; cursor: move; }
	.gallery li a { float: right; }
	.gallery li a.ui-icon-zoomin { float: left; }
	.gallery li img { width: 100%; cursor: move; }

	.trash { float: left; width: 100%; min-height: 4em; padding: 1%;} * html .trash { height: 8em; } /* IE6 */
	.trash h4 { line-height: 16px; margin: 0 0 0.4em; }
	.trash h4 .ui-icon { float: left; }
	.trash .gallery h5 { display: none; }
	</style>
    <script src="/js/ui.multidraggable.js"></script>
	<script>
	$(function() {
		// there's the gallery and the trash
		    var $gallery = $( ".gallery" ),
			$trash = $( "#trash1" );
		
		$(".multidraggable").click(function(event) {
            $(this).toggleClass('grouped');
     	});
		
		//seleziona/deseleziona elementi
		$("#sel_tutto").click(function(event) {
			$("#SelTratta1 tr.dragme").addClass('grouped');
		});
		$("#desel_tutto").click(function(event) {
			$("#SelTratta1 tr.dragme").removeClass('grouped');
		});
		$("#sel_inverso").click(function(event) {
			$("#SelTratta1 tr.dragme").toggleClass('grouped');
		});

		// let the gallery items be draggable
		$( ".dragme", $gallery).draggable({
			cancel: "a.ui-icon", // clicking an icon won't initiate dragging
			revert: "invalid", // when not dropped, the item will revert back to its initial position
			containment: $( "#demo-frame" ).length ? "#demo-frame" : "document", // stick to demo-frame if present
			helper: "clone",
			cursor: "move"
		});
		/*$("li").draggable({
			start: function(event, ui) {
			  posTopArray = [];
			  posLeftArray = [];
			  if ($(this).hasClass("grouped")) {  // Loop through each element and store beginning start and left positions
				   $(".grouped").each(function(i) {
						thiscsstop = $(this).css('top');
						if (thiscsstop == 'auto') thiscsstop = 0; // For IE
	
						thiscssleft = $(this).css('left');
						if (thiscssleft == 'auto') thiscssleft = 0; // For IE
	
						posTopArray[i] = parseInt(thiscsstop);
						posLeftArray[i] = parseInt(thiscssleft);
				   });
			  }
	
			  begintop = $(this).offset().top; // Dragged element top position
			  beginleft = $(this).offset().left; // Dragged element left position
		 	  },
		 	  drag: function(event, ui) {
			  	var topdiff = $(this).offset().top - begintop;  // Current distance dragged element has traveled vertically
			  	var leftdiff = $(this).offset().left - beginleft; // Current distance dragged element has traveled horizontally
	
				  if ($(this).hasClass("grouped")) {
					   $(".grouped").each(function(i) {
							$(this).css('top', posTopArray[i] + topdiff); // Move element veritically - current css top + distance dragged element has travelled vertically
							$(this).css('left', posLeftArray[i] + leftdiff); // Move element horizontally - current css left + distance dragged element has travelled horizontally
				  });
			    }
		 },
		 revert: "invalid",
		 //containment: $( "#demo-frame" ).length ? "#demo-frame" : "document", // stick to demo-frame if present
		 helper: "clone",
		 cursor: "move"
		});*/
						

		// let the trash be droppable, accepting the gallery items       
        d=1;
        numerotratte=2;
        /* while (d<=numerotratte)
        {*/
        $(".trash").droppable({
            hoverClass: "ui-state-active",
            accept: "li, .dragme, .grouped",
			activeClass: "ui-state-highlight",
			drop: function( event, ui ) {
				//var num = ui.draggable.find(id).length;
				if($(".grouped").length != 0)
				{
					var IDs = [];
					$("#SelTratta1").find(".grouped").each(function(){ IDs.push(this.id); });
					//alert(IDs.length);
					for(var i=0; i<IDs.length; i++)
					{
						xx=deleteImage( IDs[i], this, 1);
					}
				}
				else 
				{
					deleteImage( ui.draggable, this, 2); 
				}
                                	
                CorsaId='<?=$CorsaId?>';
                DataPartenza='<?=$DataPartenza?>';
                dialog_box_previaggio();
                
                loadMediazioneStep("rt_previaggio","previaggio.php?do=GestionePreviaggio&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&step=3",this);   
                           
				/*var num = $(".grouped").length;
				for(var i=0; i<num-1; i++){
					alert($(".grouped").attr("id"))	;				
				}*/
				//alert(num);
				//alert(ui.draggable.attr("id"));
				//deleteImage( ui.draggable, this);       
			}
        });
        /*    d=d+1;
        }*/
        
		// let the gallery be droppable as well, accepting items from the trash
		$gallery.droppable({
			accept: ".trash li, .dragme",
			activeClass: "custom-state-active",
			drop: function( event, ui ) {                          
				recycleImage( ui.draggable );
			}
		});

		// image deletion function
		var recycle_icon = "";
        function deleteImage( $item,oggetto,tipo ) {
            TrattaNavetta=$(oggetto).attr("id");
            $MoveTo = oggetto;
						
			//alert($item.attr("id"));
			
            //Id=Oggetto.attr("id");
            //PrenotazioneId=$item.attr("id");
			if(tipo==1)
               PrenotazioneId=$item;
			else if(tipo==2)
			   PrenotazioneId=$item.attr("id");
           // aggiorno array disposizione  
                    
                    
                      
           // alert(PrenotazioneId);
                      
                     
		
                PrenotazioneTrattaNavetta=PrenotazioneId+'_'+TrattaNavetta;
                CorsaId='<?=$CorsaId?>';
                DataPartenza='<?=$DataPartenza?>';
             dialog_box_previaggio();
                 page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=PreparaBusArray&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&Prenotazione="+PrenotazioneTrattaNavetta;
                 $.get(page_to_load, function(data){
                 msg1=jQuery.trim(data);
                 
                 if (msg1!='ok')
                     {
                         alert(data);
                     
                     }
                     else
                         {
                          /*   $item.fadeOut(function() {
                           
				var $list = $( "ul", $MoveTo ).length ?
					$( "ul", $MoveTo ) :
					$( "<ul class='gallery ui-helper-reset'/>" ).appendTo( $MoveTo );
                                 

				$item.find( "a.ui-icon-trash" ).remove();
                              
				$item.append( recycle_icon ).appendTo( $list ).fadeIn(function() {
                                    	$item
						//.animate({ width: "48px" })
						.find( "img" )
							.animate({ height: "0px" });
				});
			});*/
                             
                             
                         }
                     
                 

                } );
                return 1;
                }

		// image recycle function
		var trash_icon = "<a href='link/to/trash/script/when/we/have/js/off' title='Delete this image' class='ui-icon ui-icon-trash'>Delete image</a>";
		function recycleImage( $item ) {
                   PrenotazioneId=$item.attr("id");
                   CorsaId='<?=$CorsaId?>';
                DataPartenza='<?=$DataPartenza?>';
                   
                   
                   
			$item.fadeOut(function() {
				$item
					.find( "a.ui-icon-refresh" )
						.remove()
					.end()
					//.css( "width", "96px")
					.append( trash_icon )
					.find( "img" )
						.css( "height", "72px" )
					.end()
					.appendTo( $gallery )
					.fadeIn();
			});
                        dialog_box_previaggio();
                         page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=RimuoviBusArray&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&Prenotazione="+PrenotazioneId;
                        $.get(page_to_load, function(data){
							loadMediazioneStep("rt_previaggio","previaggio.php?do=GestionePreviaggio&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&step=3",this);
                             
                         });
                 
                        
		}

		
		// resolve the icons behavior with event delegation
		$( "ul.gallery > li" ).click(function( event ) {
			
			var $item = $( this ),
				$target = $( event.target );

			if ( $target.is( "a.ui-icon-trash" ) ) {
				deleteImage( $item );
			} else if ( $target.is( "a.ui-icon-zoomin" ) ) {
				viewLargerImage( $target );
			} else if ( $target.is( "a.ui-icon-refresh" ) ) {
				recycleImage( $item );
			}

			return false;
		});
	});
        
        function AggiungiBus(TrattaId)
        {
           divsize=$("#tt_"+TrattaId+" > div > div:visible").size();
           k=(divsize)+1;
           $( "#Navetta_"+k+"_"+TrattaId).show();
            
            
        }
        
    
        function NascondiBus(BusNumber,BusId)
        {
           CorsaId='<?=$CorsaId?>';
           DataPartenza='<?=$DataPartenza?>';
           stringa="Eliminando il bus i passeggeri dovranno essere riposizionati. Continuare?";
           conferma = confirm(stringa);
       
            if (conferma)
           {
               
                         page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=RimuoviBus&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&BusId="+BusId+"&BusNumero="+BusNumber;
                        $.get(page_to_load, function(data){
                            
                            
                            msg1=jQuery.trim(data);
                 
                            if (msg1=='ok')
                                {
                                    alert("Barca eliminata dalla preparazione");
                                    loadMediazioneStep("rt_previaggio","previaggio.php?do=GestionePreviaggio&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&step=3",this);
                                }
                            	
                             
                         });

                //$("#Navetta_"+k+"_"+TrattaId).hide();

           }   
             /*divsize=$("#tt_"+TrattaId+" > div > div:visible").size();
             k=(divsize)+1;
             $( "#Navetta_"+k+"_"+TrattaId).show();*/   
            
        }
        
	</script>

<?
   
}

function prepara_navetta()
{
global $user,$HtmlCommon;
$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
$db= new Database();
$db->connect();
$corsaobj=new Corsa($CorsaId);
$corsaobj->conn=$db;
$corsaobj->inizializzaDatiGenerali();
$arr_corsa=$corsaobj->DatiGenerali;

$titolo=$arr_corsa['CorsaNome']." del ".$DataPartenza;
$titolo=$titolo." / Preparazione delle navette";

$HtmlCommon->html_titolo_pagina($titolo);
$HtmlCommon->html_titolo_box($titolo);
$DataPartenzaF=$_REQUEST['DataPartenza'];

carica_menu_percorso(3);



unset($_SESSION['PREPARAZIONE_NAVETTE']);
$_SESSION['PREPARAZIONE_NAVETTE']=array();

//prelevo tutte le tratte
// prelevo tutti i prenotati
include_once("previaggio_validator.php");    
?>
	
<style type="text/css" title="currentStyle">
    @import "/DataTables/media/css/demo_page.css";
    @import "/DataTables/media/css/demo_table.css";
</style>	
	
	<style>
	h1 { padding: .2em; margin: 0; }
	#products { float:left; width: 500px; margin-right: 2em; }
        #products1 { float:left; width: 500px; margin-right: 2em; }
	#cart { width: 200px; float: left; }
	/* style the list to maximize the droppable hitarea */
        #catalog1 ol { margin: 0; padding: 1em 0 1em 3em; }
	#catalog ol { margin: 0; padding: 1em 0 1em 3em; }
        
        .ui-accordion-content { height: "auto";}
	</style>
        
        <script>
	
	</script>
        
	<script>
            
            
            
	$(function() {
		$( "#catalog" ).accordion({
			autoHeight: false,
			navigation: true
		});
		
                $( "#catalog .dragme" ).draggable({
		    	appendTo: "body"
			//helper: "clone"
		});
                
                $( "#catalog1" ).accordion({
			autoHeight: false,
			navigation: true
		});
		
                $( "#catalog1 .dragme" ).draggable({
			appendTo: "body"
			//helper: "clone"
		});
		
	});
	</script>
        
        
 <div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">            

<form id="application_form" name="application_form"  method="post" action="#">
<div class="brain_formModifica">
     <div class="brain_data-content">      
<input type="hidden" id="step_successivo" name="step_successivo" value="2">

<? 

$sql="select * from RT_CorsaConsolidamento where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataCorsa='$DataPartenza' and Stato=1 and Cancella=0";
$row = $db->query_first($sql);
$consolidamento=false; 
  if ( (!empty($row['ConsolidamentoCorsaId'])) and ($row['ConsolidamentoCorsaId']>0))
    $consolidamento=true;
  
  if (!$consolidamento)
  {
      // crea consolidamento
        $d1['DataCorsa']=$DataPartenza;
        $d1['CorsaId']=$CorsaId;
   //   $d1=$storico->operazioni_insert($d1, $user);
        $lastidA=$db->insert("RT_CorsaConsolidamento", $d1);

      
  }


$temp=null;

   $sql="select * from RT_PrenotazioniNonOrganizzateInNavette where CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza' order by ClienteNome asc";
 //echo($sql);
   $ArrObjectP = $db->fetch_array($sql);
   $numeropasseggeri=sizeof($ArrObjectP);
   
 $storico=new StoricoOperazioni();
  $storico->conn=$db;   
  
    
         // echo($numeropasseggeri);
        $np1=0;
        $d11=null;
        while ($np1<$numeropasseggeri)
        {
            $TrattaId=$ArrObjectP[$np1]['TrattaId'];
            $ClienteNome=$ArrObjectP[$np1]['ClienteNome'];
            $ClienteId=$ArrObjectP[$np1]['PrenotazioneId'];
            $FermataSalita=$ArrObjectP[$np1]['FermataSalita'];
            $FermataDiscesa=$ArrObjectP[$np1]['FermataDiscesa'];
            $ClienteId=$ArrObjectP[$np1]['PrenotazioneId'];
            $TotalePaxPrenotati=$ArrObjectP[$np1]['TotalePaxPrenotati'];
             $Csalita=$ArrObjectP[$np]['ComuneSalita'];
    $Cdiscesa=$ArrObjectP[$np]['ComuneDiscesa'];
                unset($d11);
                $d11=null;
                $d11['PrenotazioneId']=$ClienteId;
                $d11['TrattaId']=$TrattaId;
                $d11['NumeroNavetta']=1;
                $d11['CorsaId']=$CorsaId;
                $d11['DataPartenza']=$DataPartenza;
                $d11['OpeIns']=$user->OperatoreId;
                $d11['SedeIns']=$user->SedeId;
                $d11['DataIns']=date('Y-m-d H:i:s');
                $d11['IpIns']=getenv('REMOTE_ADDR');  
                $d11['OdcIdRef']=$user->OdcId;
                $d11['GestoreIdRef']=$user->GestoreId;
               
         
                
               // print_r($d11);
                
                $lastidA=$db->insert("RT_PreparazioneNavette", $d11);
         //   exit();
           $np1++; 
        }      
            



$sql="select PrenotazioneId from RT_PreparazioneNavette where  CorsaId=$CorsaId and DataPartenza='$DataPartenza' ";
$ArrObject = $db->fetch_array($sql);  

$organizzata=0;

if (sizeof($ArrObject)>0)
    $organizzata=1;

$sql="select distinct TrattaNome,TrattaId from RT_ViewPasseggeriPerNavettaTratta where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza' and AppMezzo='Navetta'";

$ArrObject = $db->fetch_array($sql);

$numerotratte=sizeof($ArrObject); 


if ($numerotratte>0)
{    ?>
 <div class="demo">
     <div id="products">
        <h1 class="ui-widget-header">Passeggeri</h1>	
            <div id="catalog">
         <?

            $nt=0;
            //while ($nt<$numerotratte)
            while ($nt<1)
            {
                $TrattaId=$ArrObject[$nt]['TrattaId'];
                $NomeTratta=$ArrObject[$nt]['TrattaNome'];
            ?>

            <h3><span class="numero_clienti_da_definire"></span><a href="#">Box temporaneo</a></h3>
            <div>   
            <!--ul id="SelTratta1" class="gallery ui-helper-reset ui-helper-clearfix"><li></li></ul-->
            <table id="SelTratta1" class="gallery ui-helper-reset display ui-helper-clearfix"></table>
            
            </div>
            <?
            $nt++;
            } ?>                   
            </div>
     </div>
    
    
    <div id="products1">
	<h1 class="ui-widget-header">Navette <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_navette.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&NumeroNavetta=1"><img src="../images/print-icon.png" alt="Stampa"/> foglio navette.</a><br style="clear:both;"></h1>	
	<div id="catalog1">
            <?
            $nt=0;
while ($nt<$numerotratte)
{
$TrattaId=$ArrObject[$nt]['TrattaId'];
$NomeTratta=$ArrObject[$nt]['TrattaNome'];
 $idcaricati="0";
  ?>
            
    <h3><a href="#"><?=$NomeTratta?></a></h3>
    <div>
        <ul id="tt_<?=$TrattaId?>">
            <div class="demo ui-widget ui-helper-clearfix">
                <a title="aggiungi navetta" onclick="AggiungiNavetta('<?=$TrattaId?>');" class="brain_aggiungi est">aggiungi navetta</a>
                    
         <? 
         $numero_navette=1; 
         $contanavette=1;
         
           $sql="select max(NumeroNavetta) as countnavette from RT_PreparazioneNavette where TrattaId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by TrattaId,DataPartenza,CorsaId";
           $row = $db->query_first($sql);
           $numero_navette=1;
           if (!empty($row['countnavette']))
           $numero_navette=$row['countnavette'];
           
           
      
         
         while ($contanavette<=$numero_navette)
         {
 
             
 $PostiGestiti=sizeof($ArrObjectP1);             
$sql="Select * from RT_FoglioCaricoTotalePaxPerNavettaIniziali where CorsaDataPartenza='$DataPartenza' and CorsaId=$CorsaId and TrattaId=$TrattaId  and OdcIdRef=$user->OdcId";
$row_gestiti = $db->query_first($sql);


$PostiGestiti=0;
if (!empty($row_gestiti['OdcIdRef']))
   $PostiGestiti=$row_gestiti['TotalePosti'];             


           
$sql="Select * from RT_FoglioCaricoTotalePaxPerNavetta where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and TrattaId=$TrattaId and NumeroNavetta=$contanavette and OdcIdRef=$user->OdcId";
$row_gestiti = $db->query_first($sql);


if (!empty($row_gestiti['OdcIdRef']))
   $PostiGestiti=$row_gestiti['TotalePosti'];             
 
 $sq="select * from RT_PreparazioneNavetteAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and TrattaId=$TrattaId and NavettaNumero=$contanavette";
// echo($sq);
 $targa="";
        $autista="";
    $r=$db->query_first($sq);
    if ($r['TrattaId']>0)
    {
        $targa=$r['Navetta'];
        $autista=$r['Autista'];
    }

         ?>       
                
                
                <div id="Navetta_<?=$contanavette?>_<?=$TrattaId?>" class="trash ui-widget-content ui-state-default">
                    <h4 class="ui-widget-header header_con_icone"> <span class="pullman_header">Navetta n. <?=$contanavette?> (<?=$PostiGestiti?>)</span>
                     <p><span class="lab_nav">targa</span> <input type="text" value="<?=$targa?>" class="inp_nav" name="DettaglioNavetta[Navetta_<?=$TrattaId?>_<?=$contanavette?>]" /> <span class="lab_nav">autista</span><input type="text" class="inp_nav" value="<?=$autista?>" name="DettaglioNavetta[Autista_<?=$TrattaId?>_<?=$contanavette?>]" /> <input type="hidden" name="action" value="create_dettaglio_navetta"><input type="hidden" name="CorsaId" value="<?=$CorsaId?>"><input type="hidden" name="DataPartenza" value="<?=$DataPartenza?>"><input type="submit" value="Invia" class="but_nav"></p>
                    <br/><a class="icona_rimuovi" onclick="javascript:NascondiNavetta(<?=$contanavette?>,'<?=$TrattaId?>')"><img src="../images/remove-icon.png" alt="Rimuovi"/></a> <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_navette.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TrattaId=<?=$TrattaId?>&NumeroNavetta=<?=$contanavette?>"><img src="../images/print-icon.png" alt="Stampa"/> foglio navette.</a><br style="clear:both"/></h4>
              
		<!--a href="#nav_<?=$contanavette?>_<?=$TrattaId?>"></a-->
                    
                    
        <!--<ul class="gallery ui-helper-reset">-->
        <table class="gallery ui-helper-reset display">
        	<tbody> 
                     
         <?   $np=0;
        $numeropasseggeri=0;
        
        if (($nt==0) and (empty($idcaricati)))
            $idcaricati.="0";
       
       
     //  $idcaricati="";
            
        
        $sql_pren="select PrenotazioneId from RT_PreparazioneNavette where NumeroNavetta=$contanavette and TrattaId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' ";
        $sql_pren1="select PrenotazioneId from RT_PreparazioneNavette where TrattaId<>$TrattaId and CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza'";
        
        if ($organizzata==1)
          $sql="select * from RT_ViewPasseggeriPerNavettaTratta where (PrenotazioneId IN ($sql_pren)) and   CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza' Group by PrenotazioneId order by ClienteNome";
        else
          $sql="select * from RT_ViewPasseggeriPerNavettaTratta where TrattaId=$TrattaId and CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza' Group by PrenotazioneId order by ClienteNome asc";
        
    //   echo($sql);
       
       
        $ArrObjectP = $db->fetch_array($sql);
        
        
      
        
        
        $numeropasseggeri=sizeof($ArrObjectP);
         // echo($numeropasseggeri);
        
        while ($np<$numeropasseggeri)
        {
            $ClienteNome=$ArrObjectP[$np]['ClienteNome'];
            $ClienteId=$ArrObjectP[$np]['PrenotazioneId'];
            $FermataSalita=$ArrObjectP[$np]['FermataSalita'];
            $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
            $ClienteId=$ArrObjectP[$np]['PrenotazioneId'];
            $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
            $idcaricati.=",".$ClienteId;
             $Csalita=$ArrObjectP[$np]['ComuneSalita'];
             $Cdiscesa=$ArrObjectP[$np]['ComuneDiscesa'];
            
            
            $sql="select PrenotazioneId,TrattaId,NumeroNavetta from RT_PreparazioneNavette where PrenotazioneId=$ClienteId and TrattaId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
            $ow=$db->query_first($sql);
            $aggiungi=false;
            
            
            if ($ow['PrenotazioneId']>0) // se presente
            {
             // print_r ($ow);
                
                $ttr=$ow['TrattaId'];
                $nnv=$ow['NumeroNavetta'];
            
                if (($TrattaId==$ttr) and ($nnv==$contanavette))
                    $aggiungi=true;
                else
                {
                    $aggiungi=false;
                    $temp[$ttr][]=$ClienteId;
                    
                }
                     
            }
       //  $aggiungi=true;
     
         
         if ($aggiungi)
         {  
            $_SESSION['PREPARAZIONE_NAVETTE'][$ClienteId."_".$TrattaId]=$TrattaId."_".$contanavette;
    ?>
    	
        <tr id="PrenotazioneId_<?=$ClienteId?>" class="dragme ui-widget-content ui-corner-tr" style="position: relative"> 
                    <td class="cell_0"><?=$TotalePaxPrenotati?></td>
                    <td class="cell_1"><?=$ClienteNome?></td>
                    <td class="cell_2"><?=$Csalita?></td>
                    <td class="cell_3"><?=$Cdiscesa?></td>
               <!--     <td><a href="link/to/trash/script/when/we/have/js/off" title="Elimina" class="ui-icon ui-icon-trash">rimuovi</a></td>-->
        </tr>
        
    	<!--
        <li id="PrenotazioneId_<?=$ClienteId?>" class="ui-widget-content ui-corner-tr">
            <table class="viaggio_elenco">
                <tr> 
                    <td class="cell_0"><?=$TotalePaxPrenotati?></td>
                    <td class="cell_1"><?=$ClienteNome?></td>
                    <td class="cell_2"><?=$FermataSalita?></td>
                    <td class="cell_3"><?=$FermataDiscesa?></td>
               <!--     <td><a href="link/to/trash/script/when/we/have/js/off" title="Elimina" class="ui-icon ui-icon-trash">rimuovi</a></td>-->
                <!--</tr>
            </table>           
          <!-- <strong><?=$TotalePaxPrenotati?> - <?=$ClienteNome?> (<?=$FermataSalita." / ".$FermataDiscesa?>) </strong>-->
		<!--/li-->
        
        <?
        }
$np++;
         
}
?>
         <!--/ul-->
         </tbody>
      </table>

        
</div>

                                
<? 
$contanavette++;
         }
         
         
         
$k=$contanavette;
while ($k<(10))
{
    ?>
    <div id="Navetta_<?=$k."_".$TrattaId?>" class="trash ui-widget-content ui-state-default" style="display:none">
	
             <h4 class="ui-widget-header header_con_icone"><span class="pullman_header">Navetta n. <?=$K?> (0)</span>
              <p><span class="lab_nav">targa</span> <input type="text" class="inp_nav" name="DettaglioNavetta[Navetta_<?=$TrattaId?>_<?=$k?>]" /> <span class="lab_nav">autista</span><input type="text" class="inp_nav" name="DettaglioNavetta[Autista_<?=$TrattaId?>_<?=$k?>]" /> <input type="hidden" name="create_dettaglio_navetta"><input type="hidden" name="action" value="create_dettaglio_navetta"><input type="hidden" name="CorsaId" value="<?=$CorsaId?>"><input type="hidden" name="DataPartenza" value="<?=$DataPartenza?>"><input type="submit" value="Invia" class="but_nav"></p>
                  
            
             <br/><a class="icona_rimuovi" onclick="javascript:NascondiNavetta(1,'<?=$TrattaId?>')"><img src="../images/remove-icon.png" alt="Rimuovi"/></a> <a class="icona_stampa" target="_new" href="/protected/modules/rt_previaggio/stampa_foglio_navette.php?type=1&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>&TrattaId=<?=$TrattaId?>&NumeroNavetta=<?=$k?>"><img src="../images/print-icon.png" alt="Stampa"/> foglio navette.</a><br style="clear:both"/></h4>
                <table class="gallery ui-helper-reset display">
        	<tbody> 
                </tbody>
                </table>
    </div>        
    <?
    
    $k++;
}
     ?>       



</div>
        
	
</ul>
	
                
                 <? 
 $nt++;
 ?>
</div>
 <?
 

 } 
 // print_r($temp);
 ?> 
                
                
                
                
        
	</div>
</div>

</div><!-- End demo -->
     
	<style>
	gallery { float: left; width: 90%; min-height: 12em; } * html .gallery { height: 12em; } /* IE6 */
	.gallery.custom-state-active { background: #eee; }
	.gallery li { width: auto; padding: 0.4em; margin: 0 0.4em 0.4em 0; text-align: center; }
	.gallery li h5 { margin: 0 0 0.4em; cursor: move; }
	.gallery li a { float: right; }
	.gallery li a.ui-icon-zoomin { float: left; }
	.gallery li img { width: 100%; cursor: move; }

	.trash { float: left; width: 100%; min-height: 4em; padding: 1%;} * html .trash { height: 8em; } /* IE6 */
	.trash h4 { line-height: 16px; margin: 0 0 0.4em; }
	.trash h4 .ui-icon { float: left; }
	.trash .gallery h5 { display: none; }
	</style>
	<script>
	$(function() {		
		// there's the gallery and the trash
		       var $gallery = $( ".gallery" ),
			$trash = $( "#trash1" );

		// let the gallery items be draggable
		$( ".dragme", $gallery ).draggable({
			cancel: "a.ui-icon", // clicking an icon won't initiate dragging
			revert: "invalid", // when not dropped, the item will revert back to its initial position
			containment: $( ".brain_formModifica" ).length ? ".brain_formModifica" : "document", // stick to demo-frame if present
			helper: "clone",
			cursor: "move"
		});

		// let the trash be droppable, accepting the gallery items
                
                d=1;
                numerotratte=2;
               /* while (d<=numerotratte)
                    {*/
                      
             $(".trash").droppable({
             hoverClass: "ui-state-active",
             accept: ".gallery li, tr",
			 activeClass: "ui-state-highlight",
			 drop: function( event, ui ) {
                         
				deleteImage( ui.draggable,this );
                                
			}
                        });
                    /*    d=d+1;
                    }*/
                    
                
		

		// let the gallery be droppable as well, accepting items from the trash
		$gallery.droppable({
			accept: ".trash li, tr",
			activeClass: "custom-state-active",
			drop: function( event, ui ) {
				recycleImage( ui.draggable );
			}
		});

		// image deletion function
		var recycle_icon = "<a href='link/to/recycle/script/when/we/have/js/off' title='Recycle this image' class='ui-icon ui-icon-refresh'>Recycle image</a>";
                var recycle_icon = "";
                    function deleteImage( $item,oggetto ) {
                        TrattaNavetta=$(oggetto).attr("id");
                      $MoveTo = oggetto;
                      //Id=Oggetto.attr("id");
                      PrenotazioneId=$item.attr("id");
                      
                      
                    // aggiorno array disposizione  
                    
                    
                      
                     // alert(PrenotazioneId);
                      
                      $item.fadeOut(function() {
                        
				var $list = $( "ul", $MoveTo ).length ?
					$( "table", $MoveTo ) :
					$( "<table class='gallery ui-helper-reset display'/>" ).appendTo( $MoveTo );
                                 

				$item.find( "a.ui-icon-trash" ).remove();
                              
				$item.append( recycle_icon ).appendTo( $list ).fadeIn(function() {
                                    	$item
						//.animate({ width: "48px" })
						.find( "img" )
							.animate({ height: "0px" });
				});
			});
		
                PrenotazioneTrattaNavetta=PrenotazioneId+'_'+TrattaNavetta;
                CorsaId='<?=$CorsaId?>';
                DataPartenza='<?=$DataPartenza?>';
           //  dialog_box_previaggio();
                 page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=PreparaNavettaArray&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&Prenotazione="+PrenotazioneTrattaNavetta;
                 $.get(page_to_load, function(data){
                 // alert(data);

                } );
                
                }

		// image recycle function
		var trash_icon = "<a href='link/to/trash/script/when/we/have/js/off' title='Delete this image' class='ui-icon ui-icon-trash'>Delete image</a>";
		function recycleImage( $item ) {
                    
                     PrenotazioneId=$item.attr("id");
                     TrattaId=$item.parent().parent().parent().attr("id");
                  // TrattaId=1;
                   CorsaId='<?=$CorsaId?>';
                DataPartenza='<?=$DataPartenza?>';
                    
			$item.fadeOut(function() {
				$item
					.find( "a.ui-icon-refresh" )
						.remove()
					.end()
					//.css( "width", "96px")
					//.append( trash_icon )
					.find( "img" )
						.css( "height", "72px" )
					.end()
					.appendTo( $gallery )
					.fadeIn();
			});
                        
                       
                        page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=RimuoviNavettaArray&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&Prenotazione="+PrenotazioneId+"&TrattaNavetta="+TrattaId;
                        $.get(page_to_load, function(data){
                             
                         });
		}

		
		// resolve the icons behavior with event delegation
		$( "ul.gallery > li" ).click(function( event ) {
			var $item = $( this ),
				$target = $( event.target );

			if ( $target.is( "a.ui-icon-trash" ) ) {
				deleteImage( $item );
			} else if ( $target.is( "a.ui-icon-zoomin" ) ) {
				viewLargerImage( $target );
			} else if ( $target.is( "a.ui-icon-refresh" ) ) {
				recycleImage( $item );
			}

			return false;
		});
	});
        
        function AggiungiNavetta(TrattaId)
        {
           divsize=$("#tt_"+TrattaId+" > div > div:visible").size();
           k=(divsize)+1;
           $( "#Navetta_"+k+"_"+TrattaId).show();
            
            
        }
        
        function NascondiNavetta(NavettaNumero,TrattaId)
        {
           CorsaId='<?=$CorsaId?>';
           DataPartenza='<?=$DataPartenza?>';
           stringa="Eliminando la navetta i passeggeri verranno posizionati sulla navetta originaria. Continuare?";
           conferma = confirm(stringa);
       
            if (conferma)
           {
               
                         page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=RimuoviNavetta&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&TrattaId="+TrattaId+"&NumeroNavetta="+NavettaNumero;
                        $.get(page_to_load, function(data){
                            
                            
                            msg1=jQuery.trim(data);
                 
                            if (msg1=='ok')
                                {
                                    alert("La navetta � stata eliminata dalla preparazione");
                                    loadMediazioneStep("rt_previaggio","previaggio.php?do=GestionePreviaggio&CorsaId="+CorsaId+"&DataPartenza="+DataPartenza+"&step=2",this);
                                }
                            	
                             
                         });

                //$("#Navetta_"+k+"_"+TrattaId).hide();

           }   
             /*divsize=$("#tt_"+TrattaId+" > div > div:visible").size();
             k=(divsize)+1;
             $( "#Navetta_"+k+"_"+TrattaId).show();*/   
            
        }
        
	</script>


<?
}
else
{
    ?>
        <h1>Nessun servizio Navetta prenotato</h1>
        <?
    
}
    ?>
        </div>
     </div>
    
</form>

 </div>
        <?
        
     
$data1=$_SESSION['PREPARAZIONE_NAVETTE'];
$lastidA=$db->delete("RT_PreparazioneNavette","CorsaId=$CorsaId and DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId");

  
 foreach ($data1 as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     $arr_chiave1=explode('_', $chiave);
     $arr_chiave=explode('_', $valore);
     $TrattaId=$arr_chiave[0];
     $NavettaNumero=$arr_chiave[1];
     $PrenotazioneId=$arr_chiave1[0];
     
     
     
    $d1=null;
     $d1['PrenotazioneId']=$PrenotazioneId;
     $d1['TrattaId']=$TrattaId;
     $d1['NumeroNavetta']=$NavettaNumero;
     $d1['CorsaId']=$CorsaId;
     $d1['DataPartenza']=$DataPartenza;
     
   //  $_SESSION['PREPARAZIONE_NAVETTE'][$ClienteId."_".$TrattaId]=$TrattaId."_".$contanavette;
     
  $storico=new StoricoOperazioni();
  $storico->conn=$db;   
     
     if ($PrenotazioneId>0)
         { 
             $d1=$storico->operazioni_insert($d1,$user);
            
             $lastidA=$db->insert("RT_PreparazioneNavette", $d1);
             //print($lastidA);
          }
    }
 }



function spara_pulsanti_wizard($steptogo)
{
    
global $funzione_edit;

if ($funzione_edit)
    spara_pulsanti_edit($steptogo);
else
{
if (!$funzione_edit)
$page=new Form();
    
?>
<div class="divSubmit">
     
        <?  $page->create_button("Procedi","Procedi","Procedi","brain_salva","submit"); ?>
        
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>
    <?
    
    
}
}

function spara_pulsanti_edit($steptogo)
{
   
global $abilita_modifica;
    
    $page=new Form();
    
    ?>
<div class="divSubmit">
                         
        <?  $page->create_button("Salva","Salva","salva","brain_salva","submit"); ?>
       
         <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla">Annulla</a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>  
    <?
}

if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
	if(!isset($_REQUEST['do'])){
    	$do='';
    } else {
    	$do=$_REQUEST['do'];
    }
		
		
			switch($do) {
                                
                              

				default:
		                show_list();    
                		break;
			}
		

	

} 
// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}
?>