<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>                            
                                                        
			    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                           <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
			    <title>Bertoldi Boats - App Operatore</title>
			    
			    <link rel="stylesheet" type="text/css" href="/css/reset.css" />
			    <link rel="stylesheet" type="text/css" href="/css/style.css?v4" />
                            <link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css"/>
			    <script type="text/javascript" src="/js/jquery.min.js"></script>
                            <script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
                            <script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
			    <script type="text/javascript" src="/js/menu_hover.js"></script> 
			    <script type="text/javascript" language="javascript" src="/js/dialogbox.js?v=3"></script>
			    <script type="text/javascript" language="javascript" src="/js/common.js"></script>
			    <script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
			    <script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
                              <script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>
                          
                             
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
                              
                              <style>
                                  #accordion_content_custom .ui-accordion .ui-accordion-content { 
                                      padding: 1em 1em!important;
                                  }
                                  
                              </style>                      
                              
                              
                              
                            
				<!--[if lte IE 8]>
				<link rel="stylesheet" href="/css/styleIE.css" type="text/css" />
				<![endif]-->    
                                
                                <link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/home_2.css" type="text/css" />
                                
			</head>
			<body>


 

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
include_once ($classespath_ . "Graph/class.GraphUtil.php");
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
global $db;
 $db=new Database();
    $db->connect();









function gestioneOttimizzata(){
    
    $codiceControllo='gC_6XHrZvC9$avW!';
    
    
    if (!isset($_POST['CorsaId']))
    {
    $code=$_REQUEST['code'];
    if ($code!=$codiceControllo)
        die("servizio non disponibile");
        
    }
   
   
              
   
	global $user,$HtmlCommon, $dizionario;
        $user=new Operatore();
        $user->OperatoreId=1;
        $user->OdcId=1;
         $user->IsAdmin=1;
         $user->GestoreId=1;
	$DataPartenza=$_REQUEST['DataPartenza'];
	$CorsaId=$_REQUEST['CorsaId'];
        
        $DataPartenza=date('Y-m-d');
        $datacorrente=Date('d/m/Y');
        $CorsaId=1;
        
        if (isset($_POST['CorsaId']))
            $CorsaId=$_POST['CorsaId'];
        
        if (isset($_POST['Dal']))
        {
            $dt=new DT();
            $post_dal=$_POST['Dal'];
            $DataPartenza=$dt->format($post_dal, "d/m/Y", "Y-m-d");
            $datacorrente=$post_dal;
        }
        $all=false;
        if (isset($_REQUEST['all_route']))
            $all=true;
        
        if (isset($_POST['all_route']))
            $all=true;
        
        
        
	include_once("previaggio_validator.php");
	$db= new Database();
	$db->connect();
	$corsaobj=new Corsa($CorsaId);
	$corsaobj->conn=$db;
	$corsaobj->inizializzaDatiGenerali();
	$arr_corsa=$corsaobj->DatiGenerali;
	
	$titolo = $arr_corsa['CorsaNome']." del ".$datacorrente;
	
	
	
	//$HtmlCommon->html_titolo_box($titolo);
	$page = new Form();
	
	
	$lineaId = $arr_corsa['LineaId'];
        
        
             $gestioneModifiche = new GestioneOttimizzataModifiche($db);
	    $modifiche = $gestioneModifiche->getAll($lineaId, $CorsaId, $DataPartenza);
            
        
        if (count($modifiche)>0)
        {
        	$gruppo = GraphUtil::merge($lineaId, $CorsaId, $DataPartenza, $db, true);
//         $gruppo = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,1);
//         $gruppo1 = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,2);
//         $gruppo->mergeFlotta($gruppo1, false);
//         $gruppo2 = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,3);
//         $gruppo->mergeFlotta($gruppo2, true);
    
        }
        
        
        
        
	
	$gestioneOttimizzata = new GestioneOttimizzataFlotta(null, $db);
	$gestioneOttimizzata->conn = $db;
	$caricaDB=false;
	if($gestioneOttimizzata->getNumBus($lineaId, $CorsaId, $DataPartenza)>0){
		$caricaDB=true;
	}else{
		$caricaDB=false;
		$gruppo = GraphUtil::merge($lineaId, $CorsaId, $DataPartenza, $db, true);
//         $gruppo = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,1);
// //         var_dump($gruppo->flotta);
//         $gruppo1 = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,2);
// //         echo "************************";
//         $gruppo->mergeFlotta($gruppo1, false);
// //         var_dump($gruppo->flotta);
//         $gruppo2 = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,3);
// //         var_dump($gruppo2->graph->nodes[6468]);
//         $gruppo->mergeFlotta($gruppo2, true);    
// //         echo "!!!!!!!!!!!!!!!!!!!!!!";
// //         var_dump($gruppo->flotta);
	}
     
	 $gruppo = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, true,null,0,false,0);
	
	$prenDett = new PrenotazioneDettaglio(null);
	$prenDett->conn=$db;
        
        $sql="SELECT
	CorsaId,
	CorsaNome,AttivaDal,AttivaAl
FROM
	RT_Corsa
WHERE
	LineaId = 1
AND Stato = 1
AND Cancella = 0 and AttivaAl>=CURDATE() and AttivaDal<=CURDATE()
ORDER BY
	CorsaPeso ASC";
$arr_corse=$db->fetch_array($sql);
	
	?>
        
	


      
			    <div id="brain_loading"><?php echo $dizionario['generale']['caricamento_dati'];?></div>

                            <div id="layer_nero2" class="notifica">
                                    <div id="loading_big_loading"><img src="/images/ajax-loader.gif" alt="<?php echo $dizionario['generale']['caricamento_attendere']; ?>" /><p><?php echo $dizionario['generale']['caricamento_attendere'];?></p></div>
                            </div>
                            <!-- loading div -->

                            <div id="layer_nero" class="notifica">
                                    <div id="loading_big_loading"><img src="/images/ajax-loader.gif" alt="<?php echo $dizionario['generale']['caricamento_attendere'];?>" /><p><?php echo $dizionario['generale']['caricamento_attendere'];?></p></div>

                            </div>
                            <div id="loading_big_ok" class="notifica">
                            <div id="loading_big_loading"><img src="/images/loading_ok.png" alt="<?php echo $dizionario['generale']['operazione_completata'];?>" /><p><?php echo $dizionario['generale']['operazione_completata'];?></p></div>
                            </div>
                            <div id="loading_big_no" class="notifica">
                            <div id="loading_big_loading"><img src="/images/loading_err.png" alt="<?php echo $dizionario['generale']['errore_operazione'];?>" /><p><?php echo $dizionario['generale']['errore_operazione'];?></p></div>
                            </div>

                            <!-- loading div -->
                                    
                                    
                                 <div id="brain_oscura" class="brain_oscura">
				    <div id="brain_boxCentrato_mobile">
					<div id="brain_listaSelezione" class="brain_dialogbox"></div>
					<br style="clear:both;"/>
				    </div>
                                </div>   
                            
                            <div id="brain_oscura_pre" class="brain_oscura">
				   
                                </div>  
                                
			<div class="brainFiltri">
				<form   method="post" action="servizio_viaggio.php">
<?
    if ($all)
    {
       ?>
         <input type="hidden" name="all_route" value="true" />
       <?                             
    }
    
     $page->create_select_no_default($dizionario['generale']['corsa'],"CorsaId","CorsaId","rowForm",$arr_corse,$CorsaId,"CorsaId","CorsaNome",
    array("class"=>"'required'"),1);
 
                                    if ($all)
                                    {
                                    ?>
                                    <div class="rowForm">
						<label for="Dal"><?=$dizionario['generale']['data_corsa']?></label>
						<input class="required" type="text" value="<?=$datacorrente?>" id="Dal" name="Dal" maxlength="255" size="10">
					
				    </div>
                                    
                                    <?
                                    } else { ?>
                                    <input type="hidden" name="Dal" value="<?=$datacorrente?>" />   
                                    <?
                                    
                                    }
                                    ?>
                                    
                                    
                                   	<div class="rowForm">
						<input name="applica" type="submit" value="Cerca" />
					</div>
					<br style="clear:both;" />
				</form>
			</div>  
                            
                            <?php $HtmlCommon->html_titolo_pagina($titolo, null, null, null); ?>
        <? if ($CorsaId>0) { 
            
            
       
            
            
            
            ?>
            
                            
                            
                         
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	<div class="brain_formModifica formGestoreEdita" style="width: 105%!important;padding:0px!important;border:none!important;background: none!important;">
				<!--<?=$dizionario['pre']['operazioni_eseguite']?>-->
		<!--<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
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
		<br/>-->
				
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
				<h1 class="accordion_header" style="width:100%!important;padding:10px 0px 12px 26px!important;"> <?php
					$c = new Comune($bus->comuni[0]['comune']);
					$c->conn = $db;
					$c->inizializzaDatiGenerali();
					$comunePartenza = $c->Comune;
					$c = new Comune($bus->comuni[sizeof($bus->comuni)-1]['comune']);
					$c->conn = $db;
					$c->inizializzaDatiGenerali();
					$comuneArrivo = $c->Comune;
                                        
                                        if ($comunePartenza=='Otranto')
                                            $comunePartenza='Lecce';
                                        
                                        if ($comuneArrivo=='Martina Franca')
                                            $comuneArrivo='Bari';
                                        
                                         if ($comunePartenza=='Martina Franca')
                                            $comunePartenza='Bari';
                                        
                                         if ($comuneArrivo=='Otranto')
                                            $comuneArrivo='Lecce';
                                        
                                       if ($comunePartenza=='Caserta')
                                            $comunePartenza='Napoli';
                                        
                                         if ($comuneArrivo=='Caserta')
                                            $comuneArrivo='Napoli';
                                        
                                        
                                        
                                        
					$title = "Autobus ".$bus->nome." - Tratta: ".$comunePartenza." - ".$comuneArrivo;
                                        $title = $comunePartenza." - ".$comuneArrivo;
                                        $title.=" (Autobus ".$bus->nome.")";
					echo $title;	
				?>
				</h1>
    
				<div id="accordion_content_custom" class="accordion_content" style="width: 100%!important;padding:1em 1em!important;">
				
    				 
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
		 				
				<table class="tabellaPercorso_<?=$bus->id?>" width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule" style="font-size:110% !important;text-align: center;">
		    		<thead>
			            <tr class="rowIntestazione">
                                        <td>Comune</td>
                                        <td><strong>S</strong></td>
                                                    <td>S(I)</td>
                                                    <td><strong>D</strong></td>
                                                    <td>D(I)</td>
			                <td>V</td>
			                <td>Pax</td>
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
								<tr style="display:none;">
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
				                			$tot = $tot-$countDisceseInterscambio;
				                		} else {
											$tot;
				                		}
				                		if($tot < 0){
				                			$tot = 0;
				                		}
				                		echo $tot;
				                	?>
				                </td>
				                <td>
				                	<a id="dettaglio<?php echo $bus->id."".$comune['comune'];?>" href="javascript:void(0);"><img style="vertical-align: middle;" src="/images/visualizza_item.png" alt="Dettaglio posti"></a>
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
												url: "/protected/modules/rt_previaggio/servizio_viaggio_action.php?bypass=true",
												data: {'action': "DettaglioPasseggeriBus", 'lineaId':<?=$lineaId?>, 'corsaId':<?=$CorsaId?>, 'dataPartenza': dataPartenza, 'comuneId': <?=$comune['comune']?>, 'busId':<?=$bus->id?>, 'indexComune':<?=$index?>, 'title':'<?=$title?>', 'salitePickup':<?=$countSalitePickup?>, 'saliteInterscambio':<?=$countSaliteInterscambio?>, 'disceseDropoff':<?=$countDisceseDropoff?>, 'disceseInterscambio':<?=$countDisceseInterscambio?>, 'totPassegeri':<?=$tot?>, 'remove':'<?=$removeDiscesa?>'},
												success: function(data) {
                                                                                                    //alert(data);
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
										echo "Autobus ".$gruppo->flotta[$bus]->nome." (".$cont['tot'].") - ";
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
										echo "Autobus ".$gruppo->flotta[$bus]->nome." (".$cont['tot'].") - ";
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
		

		
		
		
	</div>	
        </div>
        <?} else 
           print("Selezionare una corsa"); ?> 
        
<script type="text/javascript"> 
    $(document).ready(function() {
        
        
   // Datepicker
	var d = new Date();
	
        
        $(function() {
		$( "#Dal" ).datepicker({
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
                            
                            
                        </body>
                        </html>
	<?php
	$db->close();
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
                                    gestioneOttimizzata();    
                		break;
			}
		

	

} 
// se l'utente non Ã¨ loggato
else {
 gestioneOttimizzata();  
}
?>