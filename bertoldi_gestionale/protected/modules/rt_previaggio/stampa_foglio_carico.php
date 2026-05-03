<?
$css_rnd=time();

?>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/stampa_report.css?<?=$css_rnd?>" type="text/css" />

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
include_once($classespath_."class.TipologiaBus.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."Graph/class.LineaGraph.php");


$ModuloId=1;

function stampa_organizzazione() {
	
	global $db, $user, $dizionario;
	
	// serve per definire il tipo di stampa
	$type = $_REQUEST['type'];
	
	//dati per generare l'organizzaizone
	$DataPartenza = $_REQUEST['DataPartenza'];
	$CorsaId = $_REQUEST['CorsaId'];
	$BusId = $_REQUEST['BusId'];
	$LineaId = $_REQUEST['LineaId'];
	?><title><?php echo $dizionario['pre']['organizzazione_viaggio']." $DataPartenza $CorsaId $BusId"?></title><?php
	$dt = new DT();
	$note = array();

	?>
	
	<div class="container_tabella_always_<?= $type ?>">
	
		<?php
			//recupero le informazioni del bus
			$sql = "SELECT gof.Nome, tb.TipologiaBus, f.Targa, f.Cellulare,  pba.Autista1, pba.Autista2 FROM RT_GestioneOttimizzataFlotta gof 
			LEFT JOIN RT_Flotta f ON (gof.BusId = f.FlottaId) 
			LEFT JOIN RT_TipologiaBus tb ON (f.TipologiaBusId = tb.TipologiaBusId)
			LEFT JOIN RT_PreparazioneBusAutisti pba ON (pba.BusId = gof.GestioneOttimizzataFlottaId)  
			WHERE gof.GestioneOttimizzataFlottaId = '" . $BusId . "' AND pba.CorsaId = '" . $CorsaId . "' AND pba.DataPartenza = '" . $DataPartenza . "'";
			$bus = $db->query_first($sql);
			
			$sql = "SELECT a.Nome, a.Cognome FROM RT_Autisti a WHERE a.AutistiId = " . $bus['Autista1'];
			$autista1 = $db->query_first($sql);
			
			$sql = "SELECT a.Nome, a.Cognome FROM RT_Autisti a WHERE a.AutistiId = " . $bus['Autista2'];
			$autista2 = $db->query_first($sql);
			
			$NumeroPullman = $bus['Nome'];
			$Pullman = $bus['TipologiaBus'] . " " . $bus['Targa'];
			$Telefono = $bus['Cellulare'];
			$Autista1 = ($autista1['Cognome']) . " " . ($autista1['Nome']);
			$Autista2 = ($autista2['Cognome']) . " " . ($autista2['Nome']);
			
			//formattazione data corsa
			$DataPartenzaFormattata = $dt->format($DataPartenza, "y-m-d", "d/m/Y");
			//recupero numero totale pax
			$sql = "SELECT COUNT(DISTINCT PrenotazioneNumero) AS Pax FROM RT_GestioneOttimizzataPasseggeri WHERE Bus=$BusId AND LineaId=$LineaId AND CorsaId=$CorsaId AND CorsaDataPartenza='$DataPartenza';";
			$paxPerCorsa = $db->query_first($sql);
			$PaxPerCorsa = $paxPerCorsa['Pax'];
			
			//recupero di tutte le informazioni dell'autobus da db
 			$corsa = new LineaGraph($LineaId, $CorsaId, $DataPartenza, $db, true);

			?>
			<div class="intestazione_tabella">
			<div style = "text-align:center;"><h1>BERTOLDI BOATS</h1></div>
				<table class="intestazione_info">
	   				<thead>
	        			<tr>
	                    	<td><?=$dizionario['pre']['num']?></td>
	            			<td><?=$dizionario['pre']['pullman']?></td>
	               			<td><?=$dizionario['generale']['telefono']?></td>
	               			<td><?=$dizionario['pre']['autista1']?></td>
	               			<td><?=$dizionario['pre']['autista2']?></td>
	            		</tr>
	        		</thead>
	        		<tbody>
	        			<tr>
	                    	<td><?=$NumeroPullman?></td>
	            			<td><?=$Pullman?></td>
	               			<td><?=$Telefono?></td>
	               			<td><?=($Autista1)?></td>
	               			<td><?=($Autista2)?></td>
	            		</tr>
	        		</tbody>        
	   			</table>  
	 	 	</div>
	  	
	  	
		  	<div class="brain_formModifica">
		     	<div class="brain_data-content">     
	         		<h2 class="intestazione_carico"><?=$dizionario['pre']['foglio_di_carico']?>  -  <?=$dizionario['pre']['corsa_del']?> <?=$DataPartenzaFormattata?> - <?=$dizionario['generale']['pax']?> <?=$PaxPerCorsa?></h2>
					<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule2">
	            		<tbody>
	            			<tr class="rowIntestazione">
	                			<td>&nbsp;</td>
				                <td><?=$dizionario['pre']['note']?></td>
				                <td><?=$dizionario['pre']['cod']?></td>
				                <td><?=$dizionario['pre']['b_v']?></td>
				                <td><?=$dizionario['generale']['cliente']?></td>
				                <td><?=$dizionario['tipo_big']['tipo_biglietto']?></td>
				                <td><?=$dizionario['generale']['partenza']?></td>
				                <td><?=$dizionario['pre']['dest_finale']?></td>
				                <td><?=$dizionario['pre']['tel']?></td>
	                
				                <td><?=$dizionario['generale']['totale']?></td>
				                <td><?=$dizionario['pre']['b']?></td>
				            </tr>
				            <?php
				            $TotaleIncasso = 0;
				            $countNote = 0;
				            foreach ($corsa->flotta[$BusId]->comuni as $index => $comune){
								//calcolo delle salite
								$TotPickupFermata = 0;
								$TotDaFermata = 0;
								//calcolo persone che salgono dalla fermata
								if($corsa->graph->nodes[$comune['comune']]->salite>0){
									foreach ($corsa->graph->nodes[$comune['comune']]->bigliettiSalite as $dest=>$passeg){
										foreach ($passeg as $value){
											if(in_array($value, $comune['passeggeri'][$dest])){
												$TotPickupFermata++;
												$TotDaFermata++;
											}
										}
									}
								}
								//calcolo persone che salgono da altri bus
								if(sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza)>1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo)>1){
									foreach ($corsa->graph->nodes[$comune['comune']]->busArrivo as $tempBusId){
										if($tempBusId != $BusId){
											$indexComuneTempBusId = -1;
											foreach ($corsa->flotta[$tempBusId]->comuni as $key=>$value){
												if(strcmp($value['comune'],$comune['comune'])==0){
													$indexComuneTempBusId = $key;
												}
											}
											if(isset($comune['passeggeri'])){
												foreach ($comune['passeggeri'] as $key => $passeggeri){
													foreach ($passeggeri as $p){
														if(isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri']) && isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri'][$key])){
															if(in_array($p, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri'][$key])){
																$TotPickupFermata++;
															}
														}
													}
												}
											}
										}
									}
								}
								
								//recupero nome del comune
								$c = new Comune($comune['comune']);
								$c->conn = $db;
								$c->inizializzaDatiGenerali();
								$ComuneP = $c->Comune;
								
								if($TotPickupFermata > 0) {
					            	$p = new PrenotazioneDettaglio(null);
					            	$p->conn = $db;
					            	
					            	
					            	//persone che salgono dalla fermata
					            	if($corsa->graph->nodes[$comune['comune']]->salite > 0) {
										$gruppiPasseggeri = array();
					            		foreach ($corsa->graph->nodes[$comune['comune']]->bigliettiSalite as $dest => $passeg){
					            			foreach ($passeg as $value) {
					            				if(in_array($value, $corsa->flotta[$BusId]->comuni[$index]['passeggeri'][$dest])){
					            					$r = $p->getPrenotazioneDettaglio($value, $LineaId, $CorsaId, $DataPartenza);
					            					
					            					$gruppiPasseggeri[$r['FermataPartenza']][] = $r;
					            				}
					            			}
					            		}
					            			
				            			foreach ($gruppiPasseggeri as $fermata => $passeggeri) {
											$DataPartenzaFermata = $dt->format($passeggeri[0]['DataPartenza'], "y-m-d", "d/m/Y")
											?>
											<tr class="gruppo_persone">
												<td>&nbsp;</td>
					            				<td>&nbsp;</td>
												<td colspan="11" class="intestazione_corsa"><?php echo $ComuneP." - ".($fermata); ?> - <?php echo $DataPartenzaFermata . " " . $passeggeri[0]['OrarioPartenza']." - PAX ".$TotPickupFermata; ?></td>
											</tr>
											<?php
											
				            				foreach ($passeggeri as $passeggero) {
												//recupero codice prenotazione
												$CodicePrenotazione = $p->getCodicePrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
												
												//recupero nome cliente prenotazione
												$ClienteNomePrenotazione = $p->getPrenotazioneClienteNome($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
												
												//recupero cellulare
												$ClienteCellulare = $p->getCellulare($CodicePrenotazione);
												
												//recupero importo, tipo titolo, codice biglietto
												$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=".$p->getIdPrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
												$titoloEsiste = $db->query_first($sql);
												if($titoloEsiste['Tot']>0){
													//titolo emesso, puo' essere pagato, avere qualche residuo con scadenza o da pagare a bordo
													$TitoloEmesso = "E";
													
													$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneId =". $passeggero['PrenotazioneId'] ." AND TipoMovimento='P' AND PagamentoTipoId=7";
													$rowMovimenti = $db->fetch_array($sql);
													if(count($rowMovimenti)==0){
														//biglietto gi� pagato o con residuo con scadenza
														$Importo = 0;
													}else{
														//biglietto con residuo da pagare a bordo
														$sql = "SELECT COUNT(*) as count FROM RT_PrenotazioneDettaglio WHERE CorsaId=$CorsaId and PrenotazioneId= ". $passeggero['PrenotazioneId'] ." AND Escludi=0";
														
                                                                                                                $rowContPersone = $db->query_first($sql);
														$Importo = $rowMovimenti[0]['Importo'] / $rowContPersone['count'];  
													}
													
													$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=".$p->getIdPrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
													$biglietto = $db->query_first($sql);
													$codiceBiglietto = $biglietto['Codice']."/".$biglietto['Anno'];
												}else{
													//titolo non emesso, pu� avere pagamento a bordo o con scadenza
													$TitoloEmesso = "A";
													$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneId =". $passeggero['PrenotazioneId'] ." AND TipoMovimento='I' AND PagamentoTipoId=7";
													$rowMovimenti = $db->fetch_array($sql);
													if(count($rowMovimenti)==0){
														//pagamento con scadenza
														$Importo = 0;
													}else{
														//pagamento a bordo
														$Importo = $passeggero['Importo'];
                                                                                                               
													}
													
													
													
													$codiceBiglietto = $CodicePrenotazione."/".$passeggero['PrenotazioneNumero'];
												}
												
												//recupero note
												if(array_key_exists($CodicePrenotazione, $note)){
													$indiceNota = $note[$CodicePrenotazione]['indice'];							
												}else{
													$idPrenotazione = $p->getIdPrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);											
													$sql = "SELECT Nota, TipoNota FROM RT_PrenotazionePercorsoNote WHERE PrenotazioneId=".$idPrenotazione;
													$notePrenotazione = $db->fetch_array($sql);
													if(sizeof($notePrenotazione)>0){
														$countNote++;
														$tempC['indice'] = $countNote;
														$tempC['note'] = $notePrenotazione;
														$note[$CodicePrenotazione] = $tempC;
														$indiceNota = $tempC['indice'];
													}else{
														$indiceNota = "&nbsp;";
													}
												}
												
												//recupero andata e ritorno
												$sql = "SELECT DataPartenza, Tragitto FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero=".$passeggero['PrenotazioneNumero'];
												$rows = $db->fetch_array($sql);
												$dataAR = "&nbsp;";
												$tragitto = "";
												if(count($rows) == 1){
													$tragitto = "S";
												}else if(count($rows) >= 2){
													foreach ($rows as $row){
														if($row['DataPartenza']==$DataPartenza){
															$temp = $row['Tragitto'];
														}
													}
													if(strcmp($temp, "Andata")){
														$dataAR = $rows[1]['DataPartenza'];
														$tragitto = "A";
                                                        $Importo=$Importo*2;
													}else{
														$dataAR = "&nbsp;";
														$tragitto = "R";
													}
												}
												$TotaleIncasso += $Importo;
												?>
				            					<tr>
				            						<td>O</td>
				            						<td><?=$indiceNota?></td><!-- NOTE -->
				            						<td><?=$CodicePrenotazione?></td>
				            						<td><?=$codiceBiglietto?></td>
				            						<td class="overflow_testo_1"><?=($ClienteNomePrenotazione)." (".($passeggero['Cognome']). ")"?></td>
				            						<td><?=$passeggero['TipologiaBiglietto']?></td>
	            									<td class="overflow_testo_1"><?=($passeggero['ComunePartenza'])?></td>
	            									<td><?=($passeggero['ComuneArrivo'])." - ".($passeggero['FermataArrivo'])?></td>
	            									<td><?=$ClienteCellulare?></td>
	            									<td><strong class="larghezza_prezzo"><?=number_format($Importo, 2,",",".")?> &euro;</strong></td>
	                 								<td><?=$TitoloEmesso?></td>
	            								</tr>
	            								<?php
											}
				            			}			            			
					           		}
					            	 							
					           		//persone che salgono dagli altri bus
					           		if(sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza)>1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo)>1){
										if($TotDaFermata==0){?>
											<tr class="gruppo_persone">
												<td>&nbsp;</td>
					            				<td>&nbsp;</td>
												<td colspan="13" class="intestazione_corsa"><?php echo $ComuneP." - Interscambio bus - PAX ".$TotPickupFermata; ?></td>
											</tr>
										<?php }
					           			foreach ($corsa->graph->nodes[$comune['comune']]->busArrivo as $tempBusId){
					           				if($tempBusId != $BusId){
					           					$indexComuneTempBusId = -1;
					           					foreach ($corsa->flotta[$tempBusId]->comuni as $key=>$value){
					           							
					           						if(strcmp($value['comune'],$comune['comune'])==0){
					           							$indexComuneTempBusId = $key;
					           						}
					           					}
					           					
					           					foreach ($corsa->flotta[$BusId]->comuni[$index]['passeggeri'] as $key => $passeggeri){
					           						foreach ($passeggeri as $pass){
					           							if(isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri']) &&
					           								isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri'][$key]) &&
					           									in_array($pass, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri'][$key])){
					           								$r = $p->getPrenotazioneDettaglio($pass, $LineaId, $CorsaId,$DataPartenza);
					           								//recupero codice prenotazione
	           												$CodicePrenotazione = $p->getCodicePrenotazione($pass, $LineaId, $CorsaId, $DataPartenza);
															
															//recupero nome cliente prenotazione
															$ClienteNomePrenotazione = $p->getPrenotazioneClienteNome($pass, $LineaId, $CorsaId, $DataPartenza);
	           												
	           												//recupero cellulare
															$ClienteCellulare = $p->getCellulare($CodicePrenotazione);
															
															//recupero importo, tipo titolo, codice biglietto
															$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=".$p->getIdPrenotazione($pass, $LineaId, $CorsaId, $DataPartenza);
															$titoloEsiste = $db->query_first($sql);
															if($titoloEsiste['Tot']>0){
																$TitoloEmesso = "E";
																$Importo = number_format(0, 2,",",".") . "&euro;";
																
																$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=".$p->getIdPrenotazione($pass, $LineaId, $CorsaId, $DataPartenza);
																$biglietto = $db->query_first($sql);
																$codiceBiglietto = $biglietto['Codice']."/".$biglietto['Anno'];
															}else{
																$TitoloEmesso = "A";
																$Importo = "Pagato";
																
																$codiceBiglietto = $CodicePrenotazione."/".$r['PrenotazioneNumero'];
															}
															
															//recupero note
															if(array_key_exists($CodicePrenotazione, $note)){
																$indiceNota = $note[$CodicePrenotazione]['indice'];
															}else{
																$idPrenotazione = $p->getIdPrenotazione($r['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
																$sql = "SELECT Nota, TipoNota FROM RT_PrenotazionePercorsoNote WHERE PrenotazioneId=".$idPrenotazione;
																$notePrenotazione = $db->fetch_array($sql);
																if(sizeof($notePrenotazione)>0){
																	$countNote++;
																	$tempC['indice'] = $countNote;
																	$tempC['note'] = $notePrenotazione;
																	$note[$CodicePrenotazione] = $tempC;
																	$indiceNota = $tempC['indice'];
																}else{
																	$indiceNota = "&nbsp;";
																}
															}
															
															//recupero andata e ritorno
															$sql = "SELECT DataPartenza, Tragitto FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero=".$r['PrenotazioneNumero'];
															$rows = $db->fetch_array($sql);
															$dataAR = "&nbsp;";
															$tragitto = "";
															if(count($rows) == 1){
																$tragitto = "S";
															}else if(count($rows) >= 2){
																foreach ($rows as $row){
																	if($row['DataPartenza']==$DataPartenza){
																		$temp = $row['Tragitto'];
																	}
																}
																if(strcmp($temp, "Andata")){
																	$dataAR = $rows[1]['DataPartenza'];
																	$tragitto = "A";
																}else{
																	$dataAR = "&nbsp;";
																	$tragitto = "R";
																}
															}
															
															//recupero targa autobus
															$sql = "SELECT gof.Nome, f.Targa FROM RT_GestioneOttimizzataFlotta gof
																	LEFT JOIN RT_Flotta f ON (gof.BusId = f.FlottaId)
																	WHERE gof.Nome =".$corsa->flotta[$tempBusId]->nome." AND gof.CorsaId=".$CorsaId." AND gof.CorsaDataPartenza = '".$DataPartenza."' AND gof.LineaId=".$LineaId;
															$busTarga = $db->query_first($sql);
															?>
							            					<tr>
							            						<td>O</td>
							            						<td><?=$indiceNota?></td><!-- NOTE -->
							            						<td><?=$CodicePrenotazione?></td>
							            						<td><?=$codiceBiglietto?></td>
							            						<td class="overflow_testo_1"><?=($ClienteNomePrenotazione)." (".($passeggero['Cognome']). ")"?></td>
							            						<td><?=$r['TipologiaBiglietto']?></td>
				            									<td class="overflow_testo_1"><?="Autobus ".$corsa->flotta[$tempBusId]->nome." - ".$dizionario['flotta']['targa'].": ".$busTarga['Targa']?></td>
				            									<td><?=($r['ComuneArrivo'])." - ".($r['FermataArrivo'])?></td>
				            									<td><?=$ClienteCellulare?></td>
				            									<td><strong class="larghezza_prezzo"><?=$Importo?></strong></td>
				                 								<td><?=$TitoloEmesso?></td>
				            								</tr>
	           												<?php	
	           											}
	           										}
	           									}
	           								}
	           							}
	           						} 
	           						?><tr class="container_tabella_always_1"><td></td></tr><?php
								}
								 
							}
							?>
							
			          	</tbody>
		          	</table>
		          	
		          	<br/><hr/><hr/><br/>
	            	<!-- contolla i campi sottostanti -->
	                <table style=" width: 100%">
	                    <tr>
	                        <td class="passeggeri_prezzo_2">
	                           <?=$dizionario['pre']['tot_pax']?>: <?=$PaxPerCorsa?>
	                        </td>
	                        <td class="passeggeri_prezzo_2">
	                            <?  $TotaleIncasso1= number_format($TotaleIncasso, 2,",","."); ?>
	                           <?=$dizionario['pre']['tot_incassare']?>: <?=$TotaleIncasso1?> &euro;
	                        </td>
	                    </tr>
	                </table>
	            
		            <div class="footer_tabella">
		            	<h2 style="text-align:left"><?=$dizionario['pre']['tot_pax']?>:_____________ <span style="float: right;"><?=$dizionario['pre']['tot_incasso']?>:_____________</span></h2>
		                <div style="clear:both"></div>
		  			</div>
	          	</div>
			</div>
	</div><!-- fine container_tabella -->
	<!-- foglio di scarico -->
	<div class="container_tabella_always_<?= $type ?>">
		<div style = "text-align:center;"><h1>BERTOLDI BOATS</h1></div>
		<div class="intestazione_tabella">
			<table class="intestazione_info">
   				<thead>
        			<tr>
                    	<td><?=$dizionario['pre']['num']?></td>
            			<td><?=$dizionario['pre']['pullman']?></td>
               			<td><?=$dizionario['generale']['telefono']?></td>
               			<td><?=$dizionario['pre']['autista1']?></td>
               			<td><?=$dizionario['pre']['autista2']?></td>
            		</tr>
        		</thead>
        		<tbody>
        			<tr>
                    	<td><?=$NumeroPullman?></td>
            			<td><?=$Pullman?></td>
               			<td><?=$Telefono?></td>
               			<td><?=($Autista1)?></td>
               			<td><?=($Autista2)?></td>
            		</tr>
        		</tbody>        
   			</table>  
 	 	</div>
  	
  	
	  	<div class="brain_formModifica">
	     	<div class="brain_data-content">     
	         	<h2 class="intestazione_carico"><?=$dizionario['pre']['foglio_scarico']?>  -  <?=$dizionario['pre']['corsa_del']?> <?=$DataPartenzaFormattata?></h2>
					<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule2">
	            		<tbody>
	            			<tr class="rowIntestazione">
	                			<td>&nbsp;</td>
				                <td><?=$dizionario['pre']['note']?></td>
				                <td><?=$dizionario['pre']['cod']?></td>
				                <td><?=$dizionario['pre']['b_v']?></td>
				                <td><?=$dizionario['generale']['cliente']?></td>
				                <td><?=$dizionario['tipo_big']['tipo_biglietto']?></td>
				                <td><?=$dizionario['generale']['partenza']?></td>
				                <td><?=$dizionario['pre']['destinazione']?></td>
				            </tr>
				            <?php 
		            		foreach ($corsa->flotta[$BusId]->comuni as $index => $comune){
							//calcolo pax discese
								$TotDropoffFermata = 0;
								$TotDDaFermata = 0;
								//calcolo delle persone che scendono alla fermata
								if($corsa->graph->nodes[$comune['comune']]->discese>0){
									foreach ($corsa->flotta[$BusId]->comuni[$index-1]['passeggeri'][$comune['comune']] as $pass){
										if(in_array($pass, $corsa->flotta[$BusId]->comuni[$index-1]['passeggeri'][$comune['comune']])){
											$TotDropoffFermata++;
											$TotDDaFermata++;
										}
									}
								}
								
								//calcolo delle persone che scendono per salire in un altri bus
								if(sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza)>1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo)>1){
									foreach ($corsa->graph->nodes[$comune['comune']]->busPartenza as $tempBusId){
										if($tempBusId != $BusId){
											$indexComuneTempBusId = -1;
											foreach ($corsa->flotta[$tempBusId]->comuni as $key=>$value){
												if(strcmp($value['comune'],$comune['comune'])==0){
													$indexComuneTempBusId = $key;
												}
											}
											foreach ($corsa->flotta[$BusId]->comuni[$index-1]['passeggeri'] as $key => $passeggeri){
												foreach ($passeggeri as $pass){
													if(isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri']) &&
														isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key]) &&
														in_array($pass, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])){
														$TotDropoffFermata++;
													}
												}
											}
										}
									}
								}
								if($TotDropoffFermata > 0) {
									$p = new PrenotazioneDettaglio(null);
									$p->conn = $db;
									//recupero nome del comune
									$c = new Comune($comune['comune']);
									$c->conn = $db;
									$c->inizializzaDatiGenerali();
									$ComuneD = $c->Comune;
									
									//persone che scendono alla fermata
									if($corsa->graph->nodes[$comune['comune']]->discese>0){
										$gruppiPasseggeri = array();
										foreach ($corsa->flotta[$BusId]->comuni[$index-1]['passeggeri'][$comune['comune']] as $value){
											if(in_array($value, $corsa->flotta[$BusId]->comuni[$index-1]['passeggeri'][$comune['comune']])){
												$r = $p->getPrenotazioneDettaglio($value, $LineaId, $CorsaId, $DataPartenza);
												$gruppiPasseggeri[$r['FermataArrivo']][] = $r;
											}
										}
										
												 
										foreach ($gruppiPasseggeri as $fermata => $passeggeri) {
											$DataArrivoFermata = $dt->format($passeggeri[0]['DataArrivo'], "y-m-d", "d/m/Y")
											?>
											<tr class="gruppo_persone">
												<td>&nbsp;</td>
					            				<td>&nbsp;</td>
												<td colspan="11" class="intestazione_corsa"><?php echo ($ComuneD)." - ".($fermata); ?> - <?php echo $DataArrivoFermata . " " . $passeggeri[0]['OrarioArrivo'] ." - PAX ".$TotDropoffFermata; ?></td>
											</tr>
											<?php
										
				            				foreach ($passeggeri as $passeggero) {
												//recupero codice prenotazione
												$CodicePrenotazione = $p->getCodicePrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
												
												//recupero nome cliente prenotazione
												$ClienteNomePrenotazione = $p->getPrenotazioneClienteNome($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
												
												//recupero codice biglietto
												$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=".$p->getIdPrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
												$titoloEsiste = $db->query_first($sql);
												if($titoloEsiste['Tot']>0){							
													$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=".$p->getIdPrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
													$biglietto = $db->query_first($sql);
													$codiceBiglietto = $biglietto['Codice']."/".$biglietto['Anno'];
												}else{
													$codiceBiglietto = $CodicePrenotazione."/".$passeggero['PrenotazioneNumero'];
												}
												
												//recupero indice note
												if(array_key_exists($CodicePrenotazione, $note)){
													$indiceNota = $note[$CodicePrenotazione]['indice'];
												}else{
													$indiceNota = "&nbsp;";
												}
												?>
												<tr>
													<td>O</td>
													<td><?=$indiceNota?></td><!-- NOTE -->
				                					<td><?=$CodicePrenotazione?></td>
				                					<td><?=$codiceBiglietto?></td>
				                					<td><?=($ClienteNomePrenotazione)." (".($passeggero['Cognome']). ")"?></td>
				                					<td><?=$passeggero['TipologiaBiglietto']?></td>
				                					<td><?=($passeggero['ComunePartenza'])?></td>
				                					<td><?=($passeggero['ComuneArrivo'])?></td>
												</tr>
											<?php 
											}
										}
									}

									//persone che scendono per salire in altri bus
									if(sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza)>1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo)>1){
										if($TotDDaFermata == 0){?>
										<tr class="gruppo_persone">
											<td>&nbsp;</td>
				            				<td>&nbsp;</td>
											<td colspan="6" class="intestazione_corsa"><?php echo ($ComuneD)." - ".$dizionario['pre']['inter_bus']." - PAX ".$TotDropoffFermata;?></td>
										</tr>
										<?php }
										$p = new PrenotazioneDettaglio(null);
										$p->conn = $db;
										foreach ($corsa->graph->nodes[$comune['comune']]->busPartenza as $tempBusId){
											if($tempBusId != $BusId){
												$indexComuneTempBusId = -1;
												foreach ($corsa->flotta[$tempBusId]->comuni as $key=>$value){
													if(strcmp($value['comune'],$comune['comune'])==0){
														$indexComuneTempBusId = $key;
													}
												}
												foreach ($corsa->flotta[$BusId]->comuni[$index-1]['passeggeri'] as $key => $passeggeri2){
													foreach ($passeggeri2 as $value){
														if(isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]) &&
															isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri']) &&
															isset($corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key]) &&
															in_array($value, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])){

															//recupero informazioni passeggero					
															$r = $p->getPrenotazioneDettaglio($value, $LineaId, $CorsaId, $DataPartenza);
																													
															//recupero codice prenotazione
															$CodicePrenotazione = $p->getCodicePrenotazione($r['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
														
															//recupero nome cliente prenotazione
															$ClienteNomePrenotazione = $p->getPrenotazioneClienteNome($r['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
														
															//recupero codice biglietto
															$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=".$p->getIdPrenotazione($r['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
															$titoloEsiste = $db->query_first($sql);
															if($titoloEsiste['Tot']>0){
																$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=".$p->getIdPrenotazione($r['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
																$biglietto = $db->query_first($sql);
																$codiceBiglietto = $biglietto['Codice']."/".$biglietto['Anno'];
															}else{
																$codiceBiglietto = $CodicePrenotazione."/".$passeggero['PrenotazioneNumero'];
															}
															
															//recupero indice note
															if(array_key_exists($CodicePrenotazione, $note)){
																$indiceNota = $note[$CodicePrenotazione]['indice'];
															}else{
																$indiceNota = "&nbsp;";
															}
															
															//recupero targa autobus
															$sql = "SELECT gof.Nome, f.Targa FROM RT_GestioneOttimizzataFlotta gof 
																	LEFT JOIN RT_Flotta f ON (gof.BusId = f.FlottaId) 
																	WHERE gof.Nome =".$corsa->flotta[$tempBusId]->nome." AND gof.CorsaId=".$CorsaId." AND gof.CorsaDataPartenza = '".$DataPartenza."' AND gof.LineaId=".$LineaId;
															$busTarga = $db->query_first($sql);
															
															?>
															<tr>
																<td>O</td>
																<td><?=$indiceNota?></td><!-- NOTE -->
							                					<td><?=$CodicePrenotazione?></td>
							                					<td><?=$codiceBiglietto?></td>
							                					<td><?=($ClienteNomePrenotazione)." (".($passeggero['Cognome']). ")"?></td>
							                					<td><?=$r['TipologiaBiglietto']?></td>
							                					<td><?=($r['ComunePartenza'])?></td>
							                					<td><?="Autobus ".$corsa->flotta[$tempBusId]->nome." - ".$dizionario['flotta']['targa'].": ".$busTarga['Targa']?></td>
															</tr>
															<?php
														}
													}
												}
											}
										}
									}
																
									
									?><tr class="container_tabella_always_1"><td></td></tr><?php
								} 
								
							}			            
							?>
				    	</tbody>
		     		</table>
		     	</div>
		     </div>
	</div><!-- fine container_tabella -->
	     
	<!-- foglio note -->
	<div class="container_tabella_always_<?= $type ?>_1">
	<div style = "text-align:center;"><h1>BERTOLDI BOATS</h1></div>
		<div class="intestazione_tabella">
			<table class="intestazione_info">
   				<thead>
        			<tr>
                    	<td><?=$dizionario['pre']['num']?></td>
            			<td><?=$dizionario['pre']['pullman']?></td>
               			<td><?=$dizionario['generale']['telefono']?></td>
               			<td><?=$dizionario['pre']['autista1']?></td>
               			<td><?=$dizionario['pre']['autista2']?></td>
            		</tr>
        		</thead>
        		<tbody>
        			<tr>
                    	<td><?=$dizionario['pre']['num']?></td>
            			<td><?=$dizionario['pre']['pullman']?></td>
               			<td><?=$dizionario['generale']['telefono']?></td>
               			<td><?=$dizionario['pre']['autista1']?></td>
               			<td><?=$dizionario['pre']['autista2']?></td>
            		</tr>
        		</tbody>        
   			</table>  
 	 	</div>
  	
  	
	  	<div class="brain_formModifica">
	     	<div class="brain_data-content">     
	         	<h2 class="intestazione_carico"><?=$dizionario['pre']['foglio_note']?>  -  <?=$dizionario['pre']['corsa_del']?> <?=$DataPartenzaFormattata?></h2>
					<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule2">
	            		<tbody>
	            			<tr class="rowIntestazione">
	                			<td>&nbsp;</td>
				                <td><?=$dizionario['pre']['note']?></td>
				                <td><?=$dizionario['pre']['cod_prenotazione']?></td>
				            </tr>
				            <?php
				            foreach ($note as $codicePrenotazione => $notePasseggero){
								$testoNota = "";
							 	foreach ($notePasseggero['note'] as $nota){
									$testoNota .= "- ";
									if(strcmp($nota['TipoNota'],"S")==0 || strcmp($nota['TipoNota'],"A")==0){
										$testoNota .= "<b>".$dizionario['pre']['note_salita']."</b> ";
									} else if(strcmp($nota['TipoNota'],"D")==0){
										$testoNota .= "<b>".$dizionario['pre']['note_disc']."</b> ";
									} else if(strcmp($nota['TipoNota'],"B")==0){
										$testoNota .= "<b>".$dizionario['pre']['note_bigl']."</b> ";
									} else if(strcmp($nota['TipoNota'],"P")==0){
										$testoNota .= "<b>".$dizionario['pre']['note_posto']."</b> ";
									} else if(strcmp($nota['TipoNota'],"G")==0){
										$testoNota .= "<b>".$dizionario['pre']['note_generiche']."</b> ";
									}
									$testoNota .= $nota['Nota'] ." ";
	 							}
	 							
					            ?>
					            <tr>
									<td>&nbsp;</td>
									<td><?=$notePasseggero['indice']?></td><!-- NOTE -->
					                <td><?=$codicePrenotazione?></td>
					            </tr>
					            <tr>
					            	<td colspan="3"><?=$testoNota?></td>
					            </tr>
		  						<?php
	  						}
	  						?>
	  					</tbody>
	  				</table>
	  		</div>
	  	</div>
	</div><!-- fine container_tabella -->   		
	<?
}

function stampa_foglio_navette()
{

global $abilita_modifica,$tratta_wizard,$db,$user;

$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];

 $dt=new DT();
 $DataPartenzaFormattata=$dt->format($DataPartenza, "y-m-d", "d/m/Y");
 $c=new Corsa($CorsaId);
 $c->conn=$db;
$c->inizializzaDatiGenerali();

 $arr_c=$c->DatiGenerali;
 
 $OraPartenza=$arr_c['OrarioPartenza'];              



if (isset($_REQUEST['TipoBus']))
{
    $BusId=$_REQUEST['TipoBus'];
    $sql="select TipologiaBus,BusId,CorsaNome,DataPartenza from RT_FoglioCaricoBusPerCorsa where BusId=$BusId and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' ";
}
else 
$sql="select TipologiaBus,BusId,CorsaNome,DataPartenza from RT_FoglioCaricoBusPerCorsa where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";


$ArrObject = $db->fetch_array($sql);
$numerotratte=sizeof($ArrObject); 
if ($numerotratte>0)
{
    $nt=0;
    while ($nt<$numerotratte)
    {
        $BusId=$ArrObject[$nt]['BusId'];
      
        $TipologiaBus=$ArrObject[$nt]['TipologiaBus'];  
        $CorsaNome=$ArrObject[$nt]['CorsaNome'];
        $DataPartenza=$ArrObject[$nt]['DataPartenza'];
    
       
    
    
         $numero_navette=1; 
         $contanavette=1;
         if (!isset($_REQUEST['TipoBus']))
         {
           $sql="select max(BusNumero) as countnavette from RT_PreparazioneBus where BusId=$BusId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by BusId,DataPartenza,CorsaId";
         
           
           $row = $db->query_first($sql);
           $numero_navette=1;
           if (!empty($row['countnavette']))
           $numero_navette=$row['countnavette'];
         $contanavette=1;
         }
         else
         {
             $contanavette=$_REQUEST['NumeroBus'];
             $numero_navette=$contanavette;
             
         }
         while ($contanavette<=$numero_navette)
         {
             $TotaleIncasso=0;
            $sql="select PostiPerPullman from RT_FoglioCaricoPasseggeriPerBusPerCorsa where BusNumero=$contanavette and OdcIdRef=$user->OdcId and BusId=$BusId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
            
            
            $row1 = $db->query_first($sql);
            $PaxPerTratta=1;
            if (!empty($row1['PostiPerPullman']))
            $PaxPerTratta=$row1['PostiPerPullman'];
    
?>
<div class="container_tabella_always_<?= $type ?>">
  <div class="intestazione_tabella">
  		
        <!--<img src="/images/intestazione.png" width="100%" alt=""/> p class="intestazione_data">23/11/2012</p-->
  <?
                     $Pullman="";
                    $Autista1="";
                    $Autista2="";
                    $Telefono="";
                    $NumeroPullman="";
               
                $sq="select * from RT_PreparazioneBusAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$contanavette";

                
                $r=$db->query_first($sq);
                
                
                
                if ($r['BusId']>0)
                {
                    $Pullman=$r['Pullman'];
                    $Autista1=$r['Autista1'];
                    $Autista2=$r['Autista2'];
                    $Telefono=$r['Telefono'];
                    $NumeroPullman=$r['NumeroPullman'];
                    
                }
  
  ?>
  
   <!--p class="intestazione_data"><span class="etichetta_riquadro">Pullman:</span><span class="riquadro"><?//=$Pullman?></span> <span class="etichetta_riquadro">Telefono:</span><span class="riquadro"><?//=$Telefono?></span> <span class="etichetta_riquadro">Autista1:</span><span class="riquadro"><?//=$Autista1?></span> <span class="etichetta_riquadro">Autista2:</span><span class="riquadro"><?//=$Autista2?></span></p-->
   <table class="intestazione_info">
   		<thead>
        	<tr>
                    <td>
                	Numero
                </td>
            	<td>
                	Barca
                </td>
               	<td>
                	Telefono
                </td>
               	<td>
                	Autista 1
                </td>
               	<td>
                	Autista 2
                </td>
            </tr>
        </thead>
        <tbody>
        	<tr>
                    <td>
                	<?=$NumeroPullman?>
                </td>
            	<td>
                	<?=$Pullman?>
                </td>
               	<td>
                	<?=$Telefono?>
                </td>
               	<td>
                	<?=$Autista1?>
                </td>
               	<td>
                	<?=$Autista2?>
                </td>
            </tr>
        </tbody>        
   </table>  
  
  </div>
  <div class="brain_formModifica">
     <div class="brain_data-content">     
         <h2 class="intestazione_carico">Foglio di carico  -  Corsa del <?=$DataPartenzaFormattata?> <?=$OraPartenza?> - Pax <?=$PaxPerTratta?></h2>         
      <!--  <h2 class="intestazione_corsa">Corsa del <?=$DataPartenzaFormattata?> <?=$OraPartenza?> - Pax <?=$PaxPerTratta?></h2>   -->
<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule2">
            <tbody>
            <tr class="rowIntestazione">
                <td></td>
                <td> - </td>
                <td>P</td>
                <td>Cliente</td>
                <td>Partenza</td>
                <td>Arrivo</td>
                <td>Tel</td>
                
                <td>Totale</td>
                <td>B</td>
                <!--<td>P.<br/>A.</td>-->
                <td>Data A/R</td>
                <td>T</td>
                <td>I</td>
                <td>R</td>
                <td>I<br/>A<br />R</td>
                <td>R<br/>A<br />R</td>
            </tr>       
            
            <?
            /* $sql="select * from RT_FoglioBusCaricoPickupFermata1 where CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
             $ArrCarico = $db->fetch_array($sql);
            */
            
            // ciclo le fermate
            
            
            $sql="select * from RT_StampaCaricoCompletoFermata where CorsaId=$CorsaId and DataPartenza='$DataPartenza' and BusId=$BusId and BusNumero=$contanavette order by DataPickup,OrarioPartenza";
           $sql="select `RT_PreparazioneBus`.`CorsaId` AS `CorsaId`,`RT_PreparazioneBus`.`BusId` AS `BusId`,`RT_PreparazioneBus`.`BusNumero` AS `BusNumero`,`RT_PreparazioneBus`.`OdcIdRef` AS `OdcIdRef`,`RT_PrenotazioneDettaglio`.`ComunePartenza` AS `ComunePartenza`,`RT_PrenotazioneDettaglio`.`FermataPartenza` AS `FermataPartenza`,count(`RT_PrenotazioneDettaglio`.`PrenotazioneDettaglioId`) AS `TotPickupFermata`,`RT_PrenotazioneDettaglio`.`OrarioPartenza` AS `OrarioPartenza`,`RT_PreparazioneBus`.`DataPartenza` AS `DataPartenza`,`RT_PrenotazioneDettaglio`.`DataPartenza` AS `DataPickup`
FROM
((RT_PreparazioneBus
INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId))))
INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId)))
where (`RT_PrenotazioneDettaglio`.`TipoServizio` = _latin1'Bus') and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza' and BusId=$BusId and BusNumero=$contanavette 
group by `RT_PreparazioneBus`.`CorsaId`,`RT_PreparazioneBus`.`DataPartenza`,`RT_PreparazioneBus`.`BusNumero`,`RT_PreparazioneBus`.`BusId`,`RT_PrenotazioneDettaglio`.`ComunePartenza`,`RT_PrenotazioneDettaglio`.`FermataPartenza`,`RT_PreparazioneBus`.`OdcIdRef`

order by DataPickup,OrarioPartenza
";
  //order by `RT_PrenotazioneDettaglio`.`DataPartenza`,`RT_PrenotazioneDettaglio`.`OrarioPartenza`
  //          
            $ArrObjectPk = $db->fetch_array($sql);
            $npk=sizeof($ArrObjectPk);
         //   echo("<br />".$sql);
           // die();
            $pk=0;
              while ($pk<$npk)
             {
                  
         /*  $FermataPId=$ArrObjectPk[$pk]['NFermataId'];
             $FermataP=$ArrObjectPk[$pk]['NFermata'];
             $ComuneP=$ArrObjectPk[$pk]['NComune'];
             $TotPickupFermata=$ArrObjectPk[$pk]['TotPickupFermata'];*/
                   $FermataP=$ArrObjectPk[$pk]['FermataPartenza'];
              $ComuneP=$ArrObjectPk[$pk]['ComunePartenza'];
              $FermataP1=  addslashes($ArrObjectPk[$pk]['FermataPartenza']);
              $ComuneP1=  addslashes($ArrObjectPk[$pk]['ComunePartenza']);
              $TotPickupFermata=$ArrObjectPk[$pk]['TotPickupFermata'];
              
              $DataPartenzaP1=  ($ArrObjectPk[$pk]['DataPickup']." ".$ArrObjectPk[$pk]['OrarioPartenza']);
              $dt=new DT ($DataPartenzaP1,"Y-m-d H:i:s");
              $DataPartenzaP=$dt->getDate('d/m/Y H:i');
              
             
              
             
            //  $sql="select * from RT_FoglioBusCarico where NFermataId=$FermataPId and BusId=$BusId and BusNumero=$contanavette and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' order by ClienteNome asc";
            $sql="select * from RT_StampaCaricoCompleto where CorsaId=$CorsaId and DataPartenza='$DataPartenza' and FermataPartenza='$FermataP1' and ComunePartenza='$ComuneP1' and BusId=$BusId and BusNumero=$contanavette order by ClienteNome asc";
          
            $sql="SELECT
RT_PreparazioneBus.CorsaId AS CorsaId,
RT_PreparazioneBus.DataPartenza AS DataPartenza,
RT_PreparazioneBus.BusId AS BusId,
RT_PreparazioneBus.BusNumero AS BusNumero,
RT_PreparazioneBus.OdcIdRef AS OdcIdRef,
RT_PrenotazioneDettaglio.ComunePartenza AS ComunePartenza,
RT_PrenotazioneDettaglio.FermataPartenza AS FermataPartenza,
RT_PreparazioneBus.PrenotazioneId AS PrenotazioneId,
RT_PrenotazioneDettaglio.ComuneArrivo AS ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo AS FermataArrivo,
count(`RT_PrenotazioneDettaglio`.`PrenotazioneDettaglioId`) AS NumeroPax,
RT_Prenotazione.ClienteNome AS ClienteNome,
RT_Prenotazione.ClienteCellulare AS ClienteCellulare,
RT_PrenotazioneDettaglio.Tragitto AS Tragitto,
RT_Prenotazione.PrenotazioneStato AS PrenotazioneStato,
RT_Prenotazione.TotalePaxPrenotati AS TotalePaxPrenotati,
RT_Prenotazione.TipoViaggioId AS TipoViaggioId,
RT_ImportoPerPrenotazione.DaIncassare AS TotaleImportoPrenotazione,
RT_PrenotazionePercorso.PrenotazionePercorsoId AS PrenotazionePercorsoId,
RT_PrenotazionePercorso.ComuneSalita AS ComunePartenzaI,
RT_PrenotazionePercorso.FermataSalita AS FermataPartenzaI,
RT_Prenotazione.ClienteSessoId AS ClienteSessoId,
RT_PrenotazioneDettaglio.DataArrivo AS DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo AS OrarioArrivo,
RT_PrenotazionePercorso.ComuneDiscesa AS ComuneDiscesa,
RT_PrenotazionePercorso.FermataDiscesa AS FermataDiscesa,
RT_PrenotazionePercorso.DataOraDiscesa AS DataOraDiscesa,
RT_Prenotazione.CodicePrenotazione AS CodicePrenotazione,
RT_Prenotazione.GestoreIdRef AS GestoreIdRef,
RT_PrenotazioneDettaglio.TipoViaggio AS TipoViaggio,
RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneStato as Variazioni
FROM
((((RT_PreparazioneBus
INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId) AND (RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId))))
INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId)))
INNER JOIN RT_ImportoPerPrenotazione ON ((RT_Prenotazione.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId)))
INNER JOIN RT_PrenotazionePercorso ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazionePercorso.CorsaId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazionePercorso.CorsaDataPartenza))))
LEFT JOIN RT_FoglioCaricoCorseElencoModificheDettaglio ON RT_PreparazioneBus.CorsaId = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaDataPartenza AND RT_PreparazioneBus.PrenotazioneId = RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneId
where ((`RT_PrenotazioneDettaglio`.`TipoServizio` = _latin1'Bus') and ((`RT_Prenotazione`.`PrenotazioneStato` = 1) or (`RT_Prenotazione`.`PrenotazioneStato` = 3))) and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza' and FermataPartenza='$FermataP1' and ComunePartenza='$ComuneP1' and BusId=$BusId and BusNumero=$contanavette
group by `RT_PreparazioneBus`.`CorsaId`,`RT_PreparazioneBus`.`DataPartenza`,`RT_PreparazioneBus`.`BusNumero`,`RT_PreparazioneBus`.`BusId`,`RT_PrenotazioneDettaglio`.`PrenotazioneId`,`RT_PrenotazioneDettaglio`.`ComunePartenza`,`RT_PrenotazioneDettaglio`.`DataPartenza`
order by `RT_Prenotazione`.`ClienteNome`
";


             $ArrObjectP = $db->fetch_array($sql);
            ?>
            <tr class="gruppo_persone">
             	<td></td>
                <td></td>
                <td colspan="10" class="intestazione_corsa">&nbsp;</td>                
             </tr>  
             <tr class="gruppo_persone">
             	<td></td>
                <td></td>
                <td colspan="10" class="intestazione_corsa"><?=$ComuneP." - "?><span><?=$FermataP." (".$DataPartenzaP.") "?></span><?=" - Pax: ".$TotPickupFermata?></td>                
             </tr>       
             <tr></tr>
            <?
             
            
            
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
              while ($np<$numeropasseggeri)
             {
                $ClienteNome= ucwords(strtolower($ArrObjectP[$np]['ClienteNome']));
                //$FermataSalita=$ArrObjectP[$np]['FermataPartenza'];
                $FermataSalita=$ArrObjectP[$np]['FermataPartenzaI'];
                $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
                //$ComuneSalita=$ArrObjectP[$np]['ComunePartneza'];
                $ComuneSalita=$ArrObjectP[$np]['ComunePartenzaI'];
                $ComuneDiscesa=$ArrObjectP[$np]['ComuneDiscesa'];
                $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
                $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
             //   $OraSalita=$ArrObjectP[$np]['DataOraSalita'];
                $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
                $PrenotazioneStatoId=$ArrObjectP[$np]['PrenotazioneStato'];
            //  $PrenotazioneTitoloId1=$ArrObjectP[$np]['PrenotazioneTitoloId'];
              $TitoloEmesso="";
              if ($PrenotazioneStatoId==3)
                 $TitoloEmesso="E";
              $DataArrivoP1=  ($ArrObjectP[$np]['DataArrivo']." ".$ArrObjectP[$np]['OrarioArrivo']);
              $dt=new DT ($DataArrivoP1,"Y-m-d H:i:s");
              $DataArrivoP=$dt->getDate('d/m/Y H:i');
                
              $Tipo=$ArrObjectP[$np]['Tragitto'];
                $PrenotazionePercorsoId=$ArrObjectP[$np]['PrenotazionePercorsoId'];
                $Variazioni=$ArrObjectP[$np]['Variazioni'];
                 $Operazione="";
                if (!empty($Variazioni))
                $Operazione="v";
                
                
               /* 
                $Operazione=$ArrObjectP[$np]['Operazione'];
                $PrenotazionePercorsoId=$ArrObjectP[$np]['PrenotazionePercorsoId'];
                */
                $Importo=0;
                $Importo1=0;
                if (($PrenotazioneStatoId==1) and ($Tipo!='Ritorno'))
                {
                    
                    
                    
                    
                $Importo=$ArrObjectP[$np]['TotaleImportoPrenotazione'];
                
                
                $Importo1= number_format($Importo, 2,",",".");
                
                }
                
                $TotaleIncasso=$TotaleIncasso+$Importo;
                
                $PostiAssegnati="0";
                
               $DataRitorno=" - ";
                
                // calcola posti assegnati
                 $sql="select CorsaDataPartenza from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and Direzione='R'";
                // echo($sql);
                 
                 $row = $db->query_first($sql);
               $DataRitornoR=" - ";
                if ($row['CorsaDataPartenza'])
                    {
                      $dt=new DT();
                       $DataRitorno=$row['CorsaDataPartenza'];
                      $DataRitornoR=$dt->format($DataRitorno, "y-m-d", "d/m/Y");
               
                   }
                   
                    if ($DataRitorno==$DataPartenza){
                    
                    $DataRitornoR=' - ';
                    $Tipo='Ritorno';
                     }
                     elseif ($DataRitorno==' - ')
                    $Tipo='Semplice';
                  else 
                    $Tipo='Andata';
                   
               if ($Tipo=='Ritorno')    
                 {
                     $sql="select CorsaDataPartenza from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and Direzione='A'";
                // echo($sql);
                 
                 $row = $db->query_first($sql);
               $DataRitornoR=" - ";
                if ($row['CorsaDataPartenza'])
                    {
                      $dt=new DT();
                       $DataRitorno=$row['CorsaDataPartenza'];
                       $DataRitornoR=$dt->format($DataRitorno, "y-m-d", "d/m/Y");
               
                   }
                   
               }
                
               
                   
                
                
                
              // verifico se il cliente ha delle note 
            $sql="select * from RT_PrenotazionePercorsoNote where PrenotazionePercorsoId=$PrenotazionePercorsoId";       
            $row = $db->query_first($sql);
           $numero_note=0;
             $ArrObjectNote = $db->fetch_array($sql);
            $n_note=sizeof($ArrObjectNote);
           $note_simbolo="";
            if ($n_note>0)
              $note_simbolo=" *";
                ?>
            <tr>
              <td>O</td>
              <td><?=$Operazione?></td>
              <td><strong><?=$TotalePaxPrenotati?><?=$note_simbolo?></strong></td>
              <td class="overflow_testo_1"><strong><?=$ClienteNome ?></strong></td>
              <td class="overflow_testo_1"><?=$ComuneSalita?></td>
              <td class="overflow_testo_2"><?=$ComuneDiscesa." - ".$FermataDiscesa?></td>
              <td><?=$ClienteCellulare?></td>
              
                <td><strong class="larghezza_prezzo"><?=$Importo1?> &euro;</strong></td>
                 <td><?=$TitoloEmesso?></td>
             <?
 
  $PaxAssegnatiText="";
			  if ($PaxAssegnati>0)
			  $PaxAssegnatiText=$PaxAssegnati;
			  ?>
             <!-- <td class="pass_assegnati<?=$PaxAssegnatiText?>" style="background:#eee; text-align:center"><?=$PaxAssegnatiText?></td>-->
              
              <td><?=$DataRitornoR?></td>
              <td><?=substr($Tipo,0,1)?></td>
              <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and (TipologiaBigliettoId=1)";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and (TipologiaBigliettoId=11)";
                 $row = $db->query_first($sql);
                
                if (!empty($row['NumeroPax']))
                $paxx=$paxx+$row['NumeroPax'];
                
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and (TipologiaBigliettoId=3)";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                
                 $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and (TipologiaBigliettoId=13)";
                 $row = $db->query_first($sql);
               
                if (!empty($row['NumeroPax']))
                $paxx=$paxx+$row['NumeroPax'];
                
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=2 ";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=12";
                 $row = $db->query_first($sql);
               
                if (!empty($row['NumeroPax']))
                $paxx=$paxx+$row['NumeroPax'];
                
                
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=4 ";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                
                 $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=14";
                 $row = $db->query_first($sql);
                
                if (!empty($row['NumeroPax']))
                 $paxx=$paxx+$row['NumeroPax'];
                
                
                ?>
              <td><?=$paxx?></td>
                
                
                
                
            
              
              
           </tr>
                <?
                
                $np++;
            }
           
                $pk++;
            }
            
            // fine ciclo fermate
            
            ?>
            
            
           </tbody>
          </table>
                                
            <br/><hr/><hr/><br/>
            	<!-- contolla i campi sottostanti -->
                <table style=" width: 100%">
                    <tr>
                        <td class="passeggeri_prezzo_2">
                           Totale Pax: <?=$PaxPerTratta?>
                        </td>
                        <td class="passeggeri_prezzo_2">
                            <?  $TotaleIncasso1= number_format($TotaleIncasso, 2,",","."); ?>
                           Totale da Incassare: <?=$TotaleIncasso1?> &euro;
                        </td>
                    </tr>
                </table>
            
            <div class="footer_tabella">
            	<h2 style="text-align:left">Totale Pax:_____________ <span style="float: right;">Totale Incasso:_____________</span></h2>
                <div style="clear:both"></div>
  				<!--img src="/images/footer.png"  width="100%" alt=""/-->
        		<!--p class="intestazione_data">23/11/2012</p-->
  			</div>
        </div>
	</div>
</div><!-- fine container_tabella -->
<?
if ($_REQUEST['OnlyCarico']=='true')
    die();
?>

<div class="container_tabella_always_<?= $type ?>">
  <div class="intestazione_tabella">
  		
        <!--<img src="/images/intestazione.png" width="100%" alt=""/> p class="intestazione_data">23/11/2012</p-->
  <?
                     $Pullman="";
                    $Autista1="";
                    $Autista2="";
                    $Telefono="";
                    $NumeroPullman="";
               
                $sq="select * from RT_PreparazioneBusAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$contanavette";
                $r=$db->query_first($sq);
                
                
                
                if ($r['BusId']>0)
                {
                    $Pullman=$r['Pullman'];
                    $Autista1=$r['Autista1'];
                    $Autista2=$r['Autista2'];
                    $Telefono=$r['Telefono'];
                    $NumeroPullman=$r['NumeroPullman'];
                    
                }
  
  ?>
  
   <!--p class="intestazione_data"><span class="etichetta_riquadro">Pullman:</span><span class="riquadro"><?//=$Pullman?></span> <span class="etichetta_riquadro">Telefono:</span><span class="riquadro"><?//=$Telefono?></span> <span class="etichetta_riquadro">Autista1:</span><span class="riquadro"><?//=$Autista1?></span> <span class="etichetta_riquadro">Autista2:</span><span class="riquadro"><?//=$Autista2?></span></p-->
   <table class="intestazione_info">
   		<thead>
        	<tr>
                    <td>
                	Numero
                </td>
            	<td>
                	Barca
                </td>
               	<td>
                	Telefono
                </td>
               	<td>
                	Autista 1
                </td>
               	<td>
                	Autista 2
                </td>
            </tr>
        </thead>
        <tbody>
        	<tr>
                    <td>
                	<?=$NumeroPullman?>
                </td>
            	<td>
                	<?=$Pullman?>
                </td>
               	<td>
                	<?=$Telefono?>
                </td>
               	<td>
                	<?=$Autista1?>
                </td>
               	<td>
                	<?=$Autista2?>
                </td>
            </tr>
        </tbody>        
   </table>  
  
  </div>
    
  <div class="brain_formModifica">
     <div class="brain_data-content">     
             <h2 class="intestazione_carico">Foglio di Scarico  -  Corsa del <?=$DataPartenzaFormattata?> <?=$OraPartenza?></h2>         
    
<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule2">
            <tbody>
          <!-- <tr class="rowIntestazione">
                <td> - </td>
                <td>P</td>
                <td>Cliente</td>
                <td>Partenza</td>
                <td>Arrivo</td>
                <td>Tel</td>
                <td>Totale</td>
                <td>P.<br/>A.</td>
                <td>Data R.</td>
                <td>Tipo</td>
                <td>I<br/>n<br/>t<br/>e<br/>r<br/>i</td>
                <td>R<br/>i<br/>d<br/>o<br/>t<br/>t<br/>i</td>
                <td>A<br/>R</td>
                <td>R<br/>i<br/>d<br/>o<br/>t<br/>t<br/>i<br/><br/>A<br/>R</td>
            </tr>      --> 
            
            <?
            
            // ciclo le fermate
            
            $PrenotatiSuBus="";
            $sql="select * from RT_FoglioScaricoPaxFermata where CorsaId=$CorsaId and DataPartenza='$DataPartenza' and BusId=$BusId and BusNumero=$contanavette and OdcIdRef=$user->OdcId  order by DataOraDiscesa asc";
            
            $sql="SELECT

RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero,
RT_PrenotazioneDettaglio.ComuneArrivo as ComuneDiscesa,
RT_PrenotazioneDettaglio.FermataArrivo as FermataDiscesa,
RT_PrenotazioneDettaglio.DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo,
sum(RT_Prenotazione.TotalePaxPrenotati) as Pax
FROM
RT_Prenotazione
INNER JOIN RT_PreparazioneBus ON RT_Prenotazione.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId
INNER JOIN RT_PrenotazioneDettaglio ON RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaInizioItinerario AND RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario AND RT_Prenotazione.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId
WHERE
(RT_Prenotazione.PrenotazioneStato = 1 OR
RT_Prenotazione.PrenotazioneStato = 3) AND
RT_PrenotazioneDettaglio.DataArrivo <> '0000-00-00' AND
RT_PreparazioneBus.CorsaId = $CorsaId AND
RT_PreparazioneBus.DataPartenza = '$DataPartenza' AND
RT_PreparazioneBus.BusId = $BusId AND
RT_PreparazioneBus.BusNumero = $contanavette AND 
RT_PrenotazioneDettaglio.TipoServizio = 'Bus'
GROUP BY
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero,
RT_PrenotazioneDettaglio.ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo
ORDER BY
RT_PrenotazioneDettaglio.DataArrivo ASC,
RT_PrenotazioneDettaglio.OrarioArrivo ASC,
RT_PrenotazioneDettaglio.ComuneArrivo ASC,
RT_PrenotazioneDettaglio.FermataArrivo ASC
";

           // echo($sql);
            $ArrObjectPk = $db->fetch_array($sql);
            $npk=sizeof($ArrObjectPk);
            $pk=0;
            $reset=0;
            $ComunePrecedente='';
              while ($pk<$npk)
             {
                  
           
             $FermataP=  ($ArrObjectPk[$pk]['FermataDiscesa']);
             $ComuneP=($ArrObjectPk[$pk]['ComuneDiscesa']);
            // $FermataP=  addslashes($ArrObjectPk[$pk]['FermataDiscesa']);
            // $ComuneP1=addslashes($ArrObjectPk[$pk]['ComuneDiscesa']);
             $TotPickupFermata=$ArrObjectPk[$pk]['Pax'];
             $DataOraDiscesa=$ArrObjectPk[$pk]['DataArrivo']." ".$ArrObjectPk[$pk]['OrarioArrivo'];
             $FermataP1=  addslashes($FermataP);
              $ComuneP1=  addslashes($ComuneP);
              
            
            // $second=$dt->compare($DataOraCorsa,$DataOraDiscesa , 'Y-m-d H:i:s');
           
            $sq="SELECT
count(RT_PrenotazioneDettaglio.PrenotazioneDettaglioId) as TotaleFermata
FROM
RT_Prenotazione
INNER JOIN RT_PreparazioneBus ON RT_Prenotazione.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId
INNER JOIN RT_PrenotazioneDettaglio ON RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaInizioItinerario AND RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario AND RT_Prenotazione.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId
WHERE
(RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3) and ComunePartenza<>'' and FermataPartenza<>''
and ComuneArrivo='$ComuneP1' 
and FermataArrivo='$FermataP1'
and RT_PreparazioneBus.CorsaId=$CorsaId 
and RT_PreparazioneBus.BusId=$BusId
and RT_PreparazioneBus.BusNumero=$contanavette
and RT_PreparazioneBus.DataPartenza='$DataPartenza'

GROUP BY
RT_PrenotazioneDettaglio.ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo,
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero

ORDER BY
RT_PrenotazioneDettaglio.DataArrivo ASC,
RT_PrenotazioneDettaglio.OrarioArrivo ASC,
RT_PrenotazioneDettaglio.ComuneArrivo ASC,
RT_PrenotazioneDettaglio.FermataArrivo ASC";
    
          //   ECHO($sq);
             $rowpax = $db->query_first($sq);
             $PaxFermataScarico=$rowpax['TotaleFermata'];
           
            
             $second=-1;
            if ($second==-1)
             {
                      $DataOraDiscesaF=$dt->format($DataOraDiscesa, "Y-m-d H:i:s", "d/m/Y H:i:s");
                     
                      
                      // $sql="select * from RT_StampaCaricoCompleto where ComuneDiscesa='$ComuneP1' and FermataDiscesa='$FermataP1' and CorsaId=$CorsaId and DataPartenza='$DataPartenza' and BusId=$BusId and BusNumero=$contanavette and OdcIdRef=$user->OdcId";
                       $sql="SELECT
RT_Prenotazione.PrenotazioneId,
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero,
RT_PrenotazioneDettaglio.ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo,
RT_PrenotazioneDettaglio.DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo,
RT_Prenotazione.ClienteNome,
RT_Prenotazione.TotalePaxPrenotati 
FROM
RT_Prenotazione
INNER JOIN RT_PreparazioneBus ON RT_Prenotazione.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId
INNER JOIN RT_PrenotazioneDettaglio ON RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaInizioItinerario AND RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario AND RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId
WHERE
(RT_Prenotazione.PrenotazioneStato = 1 OR
RT_Prenotazione.PrenotazioneStato = 3) AND
RT_PrenotazioneDettaglio.DataArrivo <> '0000-00-00' AND
RT_PreparazioneBus.CorsaId = $CorsaId AND
RT_PreparazioneBus.DataPartenza = '$DataPartenza' AND
RT_PreparazioneBus.BusId = $BusId AND
RT_PreparazioneBus.BusNumero = $contanavette and (ComuneArrivo='$ComuneP1') and FermataArrivo='$FermataP1'
GROUP BY
RT_PreparazioneBus.CorsaId,
RT_PreparazioneBus.DataPartenza,
RT_PreparazioneBus.BusId,
RT_PreparazioneBus.BusNumero,
RT_PrenotazioneDettaglio.ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo,
RT_PrenotazioneDettaglio.DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo,
RT_Prenotazione.PrenotazioneId
ORDER BY
RT_Prenotazione.ClienteNome ASC
";
                 
             //  echo($sql."<br />");
             $ArrObjectP = $db->fetch_array($sql);
             $ComunePrecedente=$ComuneP;
           if ($PaxFermataScarico>0)  
                {
            ?>
             <tr>
                <td colspan="10"><span style="font-size:18px;" id="comune_scarico"><?=$ComuneP?> - <?=$FermataP?> - PAX: <?=$PaxFermataScarico?> - </span> <?=$DataOraDiscesaF?> <!-- - Pax <?=$TotPickupFermata?> --></td>                 
             </tr>       
            
            
            <?
             
            
            
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
            $ElencoClienti="";
              while ($np<$numeropasseggeri)
             {
                $ClienteNome=  ucwords(strtolower($ArrObjectP[$np]['ClienteNome']));
                $TipoServizioN=$ArrObjectP[$np]['TipoServizio'];
                $pren=$ArrObjectP[$np]['PrenotazioneId'];
                $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
           
            if ($np==0)
            {
                $PrenotatiSuBus=$pren;
                 $ElencoClienti=$ClienteNome." (<strong>".$TotalePaxPrenotati."</strong>)";
            }
               
            else
            {
                 $PrenotatiSuBus.=",".$pren;
                  $ElencoClienti.=", ".$ClienteNome." (<strong>".$TotalePaxPrenotati."</strong>)";
            }
           
            
                ?>
                <?
                $np++;
            }
           
           print("<tr><td colspan=\"10\">".$ElencoClienti."</td></tr>");
           }
             }
                $pk++;
            }
            
            // fine ciclo fermate
            
         $sql="
select max(NumeroNavetta) as countnavette
FROM
RT_PreparazioneNavette
INNER JOIN RT_Tratta ON RT_PreparazioneNavette.TrattaId = RT_Tratta.TrattaId
WHERE
RT_PreparazioneNavette.CorsaId = $CorsaId AND
RT_PreparazioneNavette.DataPartenza = '$DataPartenza' AND
RT_Tratta.NodoPeso = 1
group by RT_PreparazioneNavette.TrattaId,DataPartenza,CorsaId             
";
         
 // $ArrTrovate = $db->fetch_array($sql);       
  
 // echo($sql);
  
  if (strtoupper($ComuneP)=="LAGONEGRO")
  {
      ?>
              <tr>
                <td colspan="10"><span style="font-size:18px;" id="comune_scarico">In partenza da lagonegro</td>                 
             </tr>  
             
             
             
      <?       
      
      
     $sql="select TrattaNome,TrattaId,DataPartenza,CorsaId from RT_FoglioNavette where PrenotazioneId IN ($PrenotatiSuBus) and   OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by TrattaNome,TrattaId,CorsaId,DataPartenza";    
    //   echo($sql);
     $ArrObject=null;
$ArrObject = $db->fetch_array($sql);
$numerotratte=sizeof($ArrObject); 
if ($numerotratte>0)
{
    $nt=0;
    while ($nt<$numerotratte)
    {
         
        
        
        $TrattaId=$ArrObject[$nt]['TrattaId'];
      
        $NomeTratta=$ArrObject[$nt]['TrattaNome'];  
      //  $CorsaNome=$ArrObject[$nt]['CorsaNome'];
        $DataPartenza1=$ArrObject[$nt]['DataPartenza'];
        $DataPartenzaFormattata=$dt->format($DataPartenza1, "y-m-d", "d/m/Y");
       
    
    
         $numero_navette1=1; 
         $contanavette1=1;
         
           $sql="select max(NumeroNavetta) as countnavette from RT_PreparazioneNavette where TrattaId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' group by TrattaId,DataPartenza,CorsaId";
         
           
           $row = $db->query_first($sql);
           $numero_navette1=1;
           if (!empty($row['countnavette']))
           $numero_navette1=$row['countnavette'];
           
        
         $contanavette1=1;
         while ($contanavette1<=$numero_navette1)
         {
              $sql="select PaxPerTratta from RT_FoglioNavettePaxTrattaNavetta where NumeroNavetta=$contanavette1 and OdcIdRef=$user->OdcId and TrattaId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";
         $sql="SELECT
RT_FoglioNavette.TrattaId AS TrattaId,
RT_FoglioNavette.NumeroNavetta AS NumeroNavetta,
RT_FoglioNavette.CorsaId AS CorsaId,
RT_FoglioNavette.OdcIdRef AS OdcIdRef,
RT_FoglioNavette.DataPartenza AS DataPartenza,
sum(`RT_FoglioNavette`.`TotalePaxPrenotati`) AS PaxPerTratta
from `RT_FoglioNavette` where
NumeroNavetta=$contanavette1 and OdcIdRef=$user->OdcId and TrattaId=$TrattaId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' and  PrenotazioneId IN ($PrenotatiSuBus)
group by `RT_FoglioNavette`.`TrattaId`,`RT_FoglioNavette`.`NumeroNavetta`,`RT_FoglioNavette`.`DataPartenza`,`RT_FoglioNavette`.`CorsaId`,`RT_FoglioNavette`.`OdcIdRef`";
//echo($sql);
            
            $row1 = $db->query_first($sql);
           $PaxPerTratta=0;
           if (!empty($row1['PaxPerTratta']))
           $PaxPerTratta=$row1['PaxPerTratta'];
             $sql="select * from RT_FoglioNavettaStampa where PrenotazioneId IN ($PrenotatiSuBus) and TrattaId=$TrattaId and NumeroNavetta=$contanavette1 and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenzaUfficiale='$DataPartenza'";
             $sql="SELECT
RT_Prenotazione.PrenotazioneId,
RT_PreparazioneNavette.CorsaId,
RT_PreparazioneNavette.DataPartenza,
RT_PreparazioneNavette.TrattaId,
RT_PreparazioneNavette.NumeroNavetta,
RT_Prenotazione.ClienteNome,
RT_Prenotazione.TotalePaxPrenotati,
RT_PrenotazioneDettaglio.ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo,
RT_PrenotazioneDettaglio.DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo
FROM
RT_Prenotazione
INNER JOIN RT_PreparazioneNavette ON RT_Prenotazione.PrenotazioneId = RT_PreparazioneNavette.PrenotazioneId
INNER JOIN RT_PrenotazioneDettaglio ON RT_PreparazioneNavette.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PreparazioneNavette.CorsaId = RT_PrenotazioneDettaglio.CorsaId AND RT_PreparazioneNavette.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario
WHERE
RT_Prenotazione.Stato = 1 AND
RT_Prenotazione.Cancella = 0 AND
RT_PrenotazioneDettaglio.TipoServizio = 'Navetta' and 
RT_Prenotazione.PrenotazioneId IN ($PrenotatiSuBus) and RT_PreparazioneNavette.TrattaId=$TrattaId and RT_PreparazioneNavette.NumeroNavetta=$contanavette1 and RT_PreparazioneNavette.CorsaId=$CorsaId and RT_PreparazioneNavette.DataPartenza='$DataPartenza'
GROUP BY
RT_PrenotazioneDettaglio.PrenotazioneId
ORDER BY 
RT_PrenotazioneDettaglio.DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo, 
RT_PrenotazioneDettaglio.ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo,
RT_Prenotazione.ClienteNome";
             

  //echo($sql);
            $ArrObjectP=null;
            $ArrObjectP = $db->fetch_array($sql);
            $numeropasseggeri=sizeof($ArrObjectP);
          //  print_r($ArrObjectP);
            
           
            $check="";
            $checknew="";
            
             ?>
               <tr>
                <td colspan="10"><span id="comune_scarico"> <h2 style="font-size:20px;border-bottom:none;"><?=$NomeTratta?> ( <?=$PaxPerTratta?> ) - navetta n. <?=$contanavette?></h2> </td>                 
             </tr>  
                 
        <?   
            
            $ContaPax=0;
              $ElencoClienti='';
               $np=0;
              if ($PaxPerTratta>0)
              {
              while ($np<$numeropasseggeri)
             {
                  
                //$Tragitto=$ArrObjectP[$np]['Tragitto'];
                $ClienteNome= ucwords(strtolower($ArrObjectP[$np]['ClienteNome']));
               // $FermataSalita=$ArrObjectP[$np]['FermataPartenza'];
                $FermataDiscesa=$ArrObjectP[$np]['FermataArrivo'];
               // $ComuneSalita=  strtoupper($ArrObjectP[$np]['ComunePartenza']);
                $ComuneDiscesa=$ArrObjectP[$np]['ComuneArrivo'];
                $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                $TotalePaxPrenotati=$ArrObjectP[$np]['TotalePaxPrenotati'];
              $ContaPax=$ContaPax+$TotalePaxPrenotati;
                // $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
                //$OraSalita=$ArrObjectP[$np]['DataPartenza']." ".$ArrObjectP[$np]['OrarioPartenza'];
                $OraDiscesa=$ArrObjectP[$np]['DataArrivo']." ".$ArrObjectP[$np]['OrarioArrivo'];
                //$OraSalitaF=$dt->format($OraSalita, "Y-m-d H:i:s", "d/m/Y H:i:s");
                $OraDiscesaF=$dt->format($OraDiscesa, "Y-m-d H:i:s", "d/m/Y H:i:s");
              $FermataP1=  addslashes($FermataDiscesa);
              $ComuneP1=  addslashes($ComuneDiscesa);
              
              
                   $checknew=$ComuneDiscesa." - ".$FermataDiscesa;
                   
                   
                $sq="SELECT

count(RT_PrenotazioneDettaglio.PrenotazioneDettaglioId) as TotalePaxFermata
FROM RT_Prenotazione INNER JOIN RT_PreparazioneNavette ON RT_Prenotazione.PrenotazioneId = RT_PreparazioneNavette.PrenotazioneId INNER JOIN RT_PrenotazioneDettaglio ON RT_PreparazioneNavette.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PreparazioneNavette.CorsaId = RT_PrenotazioneDettaglio.CorsaId AND RT_PreparazioneNavette.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario
WHERE RT_Prenotazione.Stato = 1 AND RT_Prenotazione.Cancella = 0 AND RT_PrenotazioneDettaglio.TipoServizio = 'Navetta' 
and RT_Prenotazione.PrenotazioneId IN ($PrenotatiSuBus)  and RT_PreparazioneNavette.NumeroNavetta=$contanavette1 and RT_PreparazioneNavette.CorsaId=$CorsaId and RT_PreparazioneNavette.DataPartenza='$DataPartenza'
    and RT_PrenotazioneDettaglio.ComuneArrivo='$ComuneP1' and FermataArrivo='$FermataP1'
GROUP BY
RT_PrenotazioneDettaglio.ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo,
RT_PreparazioneNavette.NumeroNavetta,
RT_PreparazioneNavette.DataPartenza,
RT_PreparazioneNavette.CorsaId,
RT_PreparazioneNavette.TrattaId";   
                   
              
                if ($check=='')
                {
                     $rowpax=$db->query_first($sq);
               $paxfermata=$rowpax['TotalePaxFermata'];
                $check=$ComuneDiscesa." - ".$FermataDiscesa;
                ?>
                  <tr><td colspan="10"><span id="comune_scarico" style="font-size:18px;"><?=$check?> - PAX: <?=$paxfermata?> - </span> <?=$OraDiscesaF?></td></tr>
            
                <?
                }
                elseif(($check!=$checknew))
                {
                     $rowpax=$db->query_first($sq);
               $paxfermata=$rowpax['TotalePaxFermata'];
                    $check=$checknew;
                    
                    ?>
                         <tr><td colspan="10"><?=$ElencoClienti?></td></tr>
                        <tr><td colspan="10"><span id="comune_scarico" style="font-size:18px;"><?=$check?> - PAX: <?=$paxfermata?> - </span> <?=$OraDiscesaF?></td></tr>
                <?
                    $ElencoClienti='';
                }
                
                 if ($ElencoClienti=='')
                  $ElencoClienti=$ClienteNome." (<strong>".$TotalePaxPrenotati."</strong>)";
                 else
                  $ElencoClienti.=", ".$ClienteNome." (<strong>".$TotalePaxPrenotati."</strong>)";
           
                 if (($numeropasseggeri==1) or ($np==$numeropasseggeri-1))
                 {
                    ?>
                             <tr><td colspan="10"><?=$ElencoClienti?></td></tr>
                     <?
                 }
                $np++;
            }
            }
            ?>
                       
            
           
        
<?      $contanavette1++;
        }
        ?>
                  
   
         <?
        $nt++;
    }
    
    
} 
      
  }
            
            
            
            
            ?>
            
            
           </tbody>
          </table>
                                
          
        </div>
	</div>
</div><!-- fine container_tabella -->


<div class="container_tabella_always_<?= $type ?>">
  <div class="intestazione_tabella">
  		
        <!--<img src="/images/intestazione.png" width="100%" alt=""/> p class="intestazione_data">23/11/2012</p-->
  <?
                     $Pullman="";
                    $Autista1="";
                    $Autista2="";
                    $Telefono="";
                    $NumeroPullman="";
               
                $sq="select * from RT_PreparazioneBusAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$contanavette";
                $r=$db->query_first($sq);
                
                
                
                if ($r['BusId']>0)
                {
                    $Pullman=$r['Pullman'];
                    $Autista1=$r['Autista1'];
                    $Autista2=$r['Autista2'];
                    $Telefono=$r['Telefono'];
                    $NumeroPullman=$r['NumeroPullman'];
                    
                }
  
  ?>
  
   <!--p class="intestazione_data"><span class="etichetta_riquadro">Pullman:</span><span class="riquadro"><?//=$Pullman?></span> <span class="etichetta_riquadro">Telefono:</span><span class="riquadro"><?//=$Telefono?></span> <span class="etichetta_riquadro">Autista1:</span><span class="riquadro"><?//=$Autista1?></span> <span class="etichetta_riquadro">Autista2:</span><span class="riquadro"><?//=$Autista2?></span></p-->
   
   <table class="intestazione_info">
   		<thead>
        	<tr>
                    <td>
                	Numero
                </td>
            	<td>
                	Barca
                </td>
               	<td>
                	Telefono
                </td>
               	<td>
                	Autista 1
                </td>
               	<td>
                	Autista 2
                </td>
            </tr>
        </thead>
        <tbody>
        	<tr>
                    <td>
                	<?=$NumeroPullman?>
                </td>
            	<td>
                	<?=$Pullman?>
                </td>
               	<td>
                	<?=$Telefono?>
                </td>
               	<td>
                	<?=$Autista1?>
                </td>
               	<td>
                	<?=$Autista2?>
                </td>
            </tr>
        </tbody>        
   </table>  
  
  </div>
  <div class="brain_formModifica">
     <div class="brain_data-content">   
     	
            <h2 class="intestazione_carico">Foglio Note  -  Corsa del <?=$DataPartenzaFormattata?> <?=$OraPartenza?></h2>         
    
<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule2">
            <tbody>
            <tr class="rowIntestazione">
               <td> - </td>
                <td>P</td>
                <td>Cliente</td>
                <td>Partenza</td>
                <td>Arrivo</td>
                <td>Tel</td>
                <td>Totale</td>
                <!--<td>P.<br/>A.</td>-->
                <td>Data A/R</td>
                <td>Tipo</td>
                <td>I</td>
                <td>R</td>
                <td>I<br/>A<br />R</td>
                <td>R<br/>A<br />R</td>
            </tr>       
            
            <?
            
            // ciclo le fermate
           
            
          
             
             $sql="select * from RT_StampaCaricoCompleto where  BusId=$BusId and BusNumero=$contanavette and OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' order by ClienteNome asc";
                 $sql="SELECT
RT_PreparazioneBus.CorsaId AS CorsaId,
RT_PreparazioneBus.DataPartenza AS DataPartenza,
RT_PreparazioneBus.BusId AS BusId,
RT_PreparazioneBus.BusNumero AS BusNumero,
RT_PreparazioneBus.OdcIdRef AS OdcIdRef,
RT_PrenotazioneDettaglio.ComunePartenza AS ComunePartenza,
RT_PrenotazioneDettaglio.FermataPartenza AS FermataPartenza,
RT_PreparazioneBus.PrenotazioneId AS PrenotazioneId,
RT_PrenotazioneDettaglio.ComuneArrivo AS ComuneArrivo,
RT_PrenotazioneDettaglio.FermataArrivo AS FermataArrivo,
count(`RT_PrenotazioneDettaglio`.`PrenotazioneDettaglioId`) AS NumeroPax,
RT_Prenotazione.ClienteNome AS ClienteNome,
RT_Prenotazione.ClienteCellulare AS ClienteCellulare,
RT_PrenotazioneDettaglio.Tragitto AS Tragitto,
RT_Prenotazione.PrenotazioneStato AS PrenotazioneStato,
RT_Prenotazione.TotalePaxPrenotati AS TotalePaxPrenotati,
RT_Prenotazione.TipoViaggioId AS TipoViaggioId,
RT_ImportoPerPrenotazione.DaIncassare AS TotaleImportoPrenotazione,
RT_PrenotazionePercorso.PrenotazionePercorsoId AS PrenotazionePercorsoId,
RT_PrenotazionePercorso.ComuneSalita AS ComunePartenzaI,
RT_PrenotazionePercorso.FermataSalita AS FermataPartenzaI,
RT_Prenotazione.ClienteSessoId AS ClienteSessoId,
RT_PrenotazioneDettaglio.DataArrivo AS DataArrivo,
RT_PrenotazioneDettaglio.OrarioArrivo AS OrarioArrivo,
RT_PrenotazionePercorso.ComuneDiscesa AS ComuneDiscesa,
RT_PrenotazionePercorso.FermataDiscesa AS FermataDiscesa,
RT_PrenotazionePercorso.DataOraDiscesa AS DataOraDiscesa,
RT_Prenotazione.CodicePrenotazione AS CodicePrenotazione,
RT_Prenotazione.GestoreIdRef AS GestoreIdRef,
RT_PrenotazioneDettaglio.TipoViaggio AS TipoViaggio,
RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneStato as Variazioni
FROM
((((RT_PreparazioneBus
INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId) AND (RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId))))
INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId)))
INNER JOIN RT_ImportoPerPrenotazione ON ((RT_Prenotazione.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId)))
INNER JOIN RT_PrenotazionePercorso ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazionePercorso.CorsaId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazionePercorso.CorsaDataPartenza))))
LEFT JOIN RT_FoglioCaricoCorseElencoModificheDettaglio ON RT_PreparazioneBus.CorsaId = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaDataPartenza AND RT_PreparazioneBus.PrenotazioneId = RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneId
where ((`RT_PrenotazioneDettaglio`.`TipoServizio` = _latin1'Bus') and ((`RT_Prenotazione`.`PrenotazioneStato` = 1) or (`RT_Prenotazione`.`PrenotazioneStato` = 3))) and BusId=$BusId and BusNumero=$contanavette and RT_PreparazioneBus.OdcIdRef=$user->OdcId and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza'
group by `RT_PreparazioneBus`.`CorsaId`,`RT_PreparazioneBus`.`DataPartenza`,`RT_PreparazioneBus`.`BusNumero`,`RT_PreparazioneBus`.`BusId`,`RT_PrenotazioneDettaglio`.`PrenotazioneId`,`RT_PrenotazioneDettaglio`.`ComunePartenza`,`RT_PrenotazioneDettaglio`.`DataPartenza`
order by `RT_Prenotazione`.`ClienteNome`
";
                 
     
             $ArrObjectP=null;
             $ArrObjectP = $db->fetch_array($sql);
           
             
            
            
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
              while ($np<$numeropasseggeri)
             {
                 $ClienteNome= ucwords(strtolower($ArrObjectP[$np]['ClienteNome']));
                //$FermataSalita=$ArrObjectP[$np]['FermataPartenza'];
                $FermataSalita=$ArrObjectP[$np]['FermataPartenzaI'];
                $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
                //$ComuneSalita=$ArrObjectP[$np]['ComunePartneza'];
                $ComuneSalita=$ArrObjectP[$np]['ComunePartenzaI'];
                $ComuneDiscesa=$ArrObjectP[$np]['ComuneDiscesa'];
                $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                $TotalePaxPrenotati=$ArrObjectP[$np]['NumeroPax'];
                $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
             //   $OraSalita=$ArrObjectP[$np]['DataOraSalita'];
                $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
                $PrenotazioneStatoId=$ArrObjectP[$np]['PrenotazioneStato'];
              //  $PaxAssegnati=$ArrObjectP[$np]['PaxAssegnati'];
                $Tipo=$ArrObjectP[$np]['Tragitto'];
                $PrenotazionePercorsoId=$ArrObjectP[$np]['PrenotazionePercorsoId'];
                  $Variazioni=$ArrObjectP[$np]['Variazioni'];
                   $Operazione="";
                if (!empty($Variazioni))
                $Operazione="v";
                $Importo=0;
                $Importo1=0;
                if ($PrenotazioneStatoId==1)
                {
                $Importo=$ArrObjectP[$np]['TotaleImportoPrenotazione'];
                $Importo1= number_format($Importo, 2,",",".");
                
                }
                
                $TotaleIncasso=$TotaleIncasso+$Importo;
                
                $PostiAssegnati="0";
                
               $DataRitorno=" - ";
                
                // calcola posti assegnati
                 $sql="select CorsaDataPartenza from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and Direzione='R'";
                // echo($sql);
                 
                 $row = $db->query_first($sql);
               $DataRitornoR=" - ";
                if ($row['CorsaDataPartenza'])
                    {
                      $dt=new DT();
                     $DataRitorno=$row['CorsaDataPartenza'];
                      $DataRitornoR=$dt->format($DataRitorno, "y-m-d", "d/m/Y");
               
                }
                 
                
               if ($DataRitorno==$DataPartenza){
                    
                    $DataRitornoR=' - ';
                    $Tipo='Ritorno';
                     }
                     elseif ($DataRitorno==' - ')
                    $Tipo='Semplice';
                  else 
                    $Tipo='Andata';
                   
               if ($Tipo=='Ritorno')    
                 {
                     $sql="select CorsaDataPartenza from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and Direzione='A'";
                // echo($sql);
                 
                 $row = $db->query_first($sql);
               $DataRitornoR=" - ";
                if ($row['CorsaDataPartenza'])
                    {
                      $dt=new DT();
                       $DataRitorno=$row['CorsaDataPartenza'];
                       $DataRitornoR=$dt->format($DataRitorno, "y-m-d", "d/m/Y");
               
                   }
                   
               }
                
                
                
              // verifico se il cliente ha delle note 
            $sql="select * from RT_PrenotazionePercorsoNote where PrenotazionePercorsoId=$PrenotazionePercorsoId";       
            $row = $db->query_first($sql);
           $numero_note=0;
             $ArrObjectNote = $db->fetch_array($sql);
            $n_note=sizeof($ArrObjectNote);
           $note_simbolo="";
            if ($n_note>0)
              $note_simbolo=" *";
            
            if ($n_note>0)
                 {
                ?>
            <tr>
                <td><?=$Operazione?></td>
              <td><strong><?=$TotalePaxPrenotati?><?=$note_simbolo?></strong></td>
              <td><strong><?=$ClienteNome ?></strong></td>
              <td><?=$ComuneSalita?></td>
              <td><?=$ComuneDiscesa?></td>
              <td><?=$ClienteCellulare?></td>
              <td><strong><?=$Importo1?> &euro;</strong></td>
              <? $PaxAssegnatiText="";
			  if ($PaxAssegnati>0)
			  $PaxAssegnatiText=$PaxAssegnati;
			  ?>
           <!--   <td class="pass_assegnati<?=$PaxAssegnatiText?>" style="background:#eee; text-align:center"><?=$PaxAssegnatiText?></td> -->
              <td><?=$DataRitornoR?></td>
              <td><?=$Tipo?></td>
             
             <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and (TipologiaBigliettoId=1)";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and (TipologiaBigliettoId=11)";
                 $row = $db->query_first($sql);
                
                if (!empty($row['NumeroPax']))
                $paxx=$paxx+$row['NumeroPax'];
                
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and (TipologiaBigliettoId=3)";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                
                 $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and (TipologiaBigliettoId=13)";
                 $row = $db->query_first($sql);
               
                if (!empty($row['NumeroPax']))
                $paxx=$paxx+$row['NumeroPax'];
                
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=2 ";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=12";
                 $row = $db->query_first($sql);
               
                if (!empty($row['NumeroPax']))
                $paxx=$paxx+$row['NumeroPax'];
                
                
                ?>
              <td><?=$paxx?></td>
                 <?
              // stampo i biglietti per tipologia
                $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=4 ";
                 $row = $db->query_first($sql);
                $paxx=0;
                if (!empty($row['NumeroPax']))
                $paxx=$row['NumeroPax'];
                
                 $sql="select NumeroPax from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId=14";
                 $row = $db->query_first($sql);
                
                if (!empty($row['NumeroPax']))
                 $paxx=$paxx+$row['NumeroPax'];
                
                
                ?>
              <td><?=$paxx?></td>
              
           </tr>
           <?
          
               $n_note=sizeof($ArrObjectNote);
               $contanote=0;
               $Nota="";
               while($contanote<$n_note)
               {
                  $TipoNota= $ArrObjectNote[$contanote]['TipoNota'];
                   $Nota1= $ArrObjectNote[$contanote]['Nota'];
                  if ($TipoNota=='S')
                      $Nota.=" - <strong>Note Salita:</strong> ".$Nota1;
                  if ($TipoNota=='D')
                      $Nota.=" - <strong>Note Discesa:</strong> ".$Nota1;
                   if ($TipoNota=='B')
                      $Nota.=" - <strong>Note Biglietto:</strong> ".$Nota1;
                    if ($TipoNota=='P')
                      $Nota.=" - <strong>Note Posto:</strong> ".$Nota1;
                     if ($TipoNota=='G')
                      $Nota.=" - <strong>Note Autista:</strong> ".$Nota1;
              $contanote++; 
                     
               }
           
           ?>
           
           <tr>
               <td></td>
               <td></td>
               
               <td colspan="12">
                   <?=$Nota?></td>
               
           </tr>
                <?
                }
                $np++;
            }
            
                
            
            
            // fine ciclo fermate
            
            ?>
            
            
           </tbody>
          </table>
          <h4 class="note_uff">Note Ufficio</h4>
          <table>
          	<tbody>
            	<tr>
                	<td class="note_ufficio">
                    	
                    </td>
                </tr>
            </tbody>
          </table>
                                
            <br/><hr/><hr/><br/>
            	<!-- contolla i campi sottostanti -->
             
        </div>
	</div>
</div><!-- fine container_tabella -->





<div class="container_tabella_always_<?= $type ?>">
  <div class="intestazione_tabella">
  		
        <!--<img src="/images/intestazione.png" width="100%" alt=""/> p class="intestazione_data">23/11/2012</p-->
  <?
                     $Pullman="";
                    $Autista1="";
                    $Autista2="";
                    $Telefono="";
                    $NumeroPullman="";
               
                $sq="select * from RT_PreparazioneBusAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$contanavette";
                $r=$db->query_first($sq);
                
                
                
                if ($r['BusId']>0)
                {
                    $Pullman=$r['Pullman'];
                    $Autista1=$r['Autista1'];
                    $Autista2=$r['Autista2'];
                    $Telefono=$r['Telefono'];
                    $NumeroPullman=$r['NumeroPullman'];
                    
                }
  
  ?>
  
   <!--p class="intestazione_data"><span class="etichetta_riquadro">Pullman:</span><span class="riquadro"><?//=$Pullman?></span> <span class="etichetta_riquadro">Telefono:</span><span class="riquadro"><?//=$Telefono?></span> <span class="etichetta_riquadro">Autista1:</span><span class="riquadro"><?//=$Autista1?></span> <span class="etichetta_riquadro">Autista2:</span><span class="riquadro"><?//=$Autista2?></span></p-->
   <table class="intestazione_info">
   		<thead>
        	<tr>
                    <td>
                	Numero
                </td>
            	<td>
                	Barca
                </td>
               	<td>
                	Telefono
                </td>
               	<td>
                	Autista 1
                </td>
               	<td>
                	Autista 2
                </td>
            </tr>
        </thead>
        <tbody>
        	<tr>
                    <td>
                	<?=$NumeroPullman?>
                </td>
            	<td>
                	<?=$Pullman?>
                </td>
               	<td>
                	<?=$Telefono?>
                </td>
               	<td>
                	<?=$Autista1?>
                </td>
               	<td>
                	<?=$Autista2?>
                </td>
            </tr>
        </tbody>        
   </table>  
  
  </div>
  <div class="brain_formModifica">
     <div class="brain_data-content">     
             <h2 class="intestazione_carico">Disposizione posti  -  Corsa del <?=$DataPartenzaFormattata?> <?=$OraPartenza?></h2>         
    
         
         <?
         
         $tb=new TipologiaBus($BusId);
    $tb->conn=$db;
    $tb->inizializzaDatiGenerali();
    $arr_tb=$tb->DatiGenerali;
    
    $NumeroPiani=$arr_tb['NumeroPiani'];
    $NumeroColonne=$arr_tb['Colonne'];
    $NumeroRighe=$arr_tb['Righe'];
         $busNumeroT=$contanavette;
         
         $npiani=1;
     while ($npiani<=$NumeroPiani)
     {
     ?>
    <br />
    <h2 class="intestazione_carico">Piano n. <?=$npiani?></h2>
    <table style="width:97%;"  width="100%" id="stampa_disposizione_posti">
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
                                 <td><?=$i+1?></td>
                             <?
                             $n=0;
                             while ($n< $NumeroColonne)
                             {
                                //$BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];   
                                $fisso="";
                                $percentuale="";
                                $rigacorrente=$i+1;
                                $colonnacorrente=$n+1;
                                
                                /*$sql="Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and TipologiaBusId=$BusId and OdcIdRef=$user->OdcId";
                                
                                $row1 = $db->query_first($sql);
                                $NumeroPosto="";
                                $DescrizionePosto="";
                                if (!empty($row1['TipologiaBusId']))
                                {
                                   $NumeroPosto=$row1['NumeroPosto'];
                                   $DescrizionePosto=$row1['DescrizionePosto'];
                                }*/
                                
                               
                               
                                  //  echo($sql);
                               
                                     
                             ?>
                              <td class="cella_posto">
                         
                         
           
	<? 
        $NumeroPosto=1;
        if ($NumeroPosto>0)
        {
          /*  $sql="Select * from RT_ViewCorsaDataElencoPostiPrenotati where BusNumero=$busNumeroT and Piano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId order by ClienteNome asc";
               $sql="Select * from RT_FoglioCaricoPosti where  Piano=$npiani and BusId=$BusId and BusNumero=$busNumeroT and Riga=$rigacorrente and Colonna=$colonnacorrente and CorsaId=$CorsaId and DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId";
            */       
               
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
RT_Prenotazione.ClienteCellulare
FROM
RT_PreparazioneBus
INNER JOIN RT_PrenotazionePosto ON RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePosto.PrenotazioneId AND RT_PreparazioneBus.CorsaId = RT_PrenotazionePosto.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_PrenotazionePosto.DataPartenza
INNER JOIN RT_Prenotazione ON RT_PrenotazionePosto.PrenotazioneId = RT_Prenotazione.PrenotazioneId
WHERE
(RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3) and Piano=$npiani and BusId=$BusId and BusNumero=$busNumeroT and Riga=$rigacorrente and Colonna=$colonnacorrente and RT_PreparazioneBus.CorsaId=$CorsaId and RT_PreparazioneBus.DataPartenza='$DataPartenza' and RT_PreparazioneBus.OdcIdRef=$user->OdcId";    
               
           // echo($sql);
            $row2 = $db->query_first($sql);
            $isReserved=0;
                             $Pid=0;
                             $Preferenza=0;
                             $ClienteNome="";
                             $ClienteSessoId=0;
                             $PrenotazionePostoId=0;
                             $TipoPrenotazione=0;
                              if (!empty($row2['OdcIdRef']))
                              {
                                  $isReserved=1;
                                  $PrenotazionePostoId=$row2['PrenotazionePostoId'];
                                  $Preferenza=$row2['PreferenzaPiano'];
                                  $Pid=$row2['PrenotazioneId'];
                                  $ClienteNome= ucwords(strtolower($row2['ClienteNome']));
                                  $ClienteSessoId=$row2['ClienteSessoId'];
                                  $TipoPrenotazione=$row2['TipoPrenotazione'];
                              }
            
            
            // se riservato
                              ?>
                
         <?  
         $classe_sesso="nd";
         if ($ClienteSessoId==1)
             $classe_sesso="maschio";
         elseif ($ClienteSessoId==2)
         $classe_sesso="femmina";
         
         if ($isReserved)
            {
            ?>
         <ul class="stampa_disposizione_posto <?=$classe_sesso?>">     
         <li class="" style="position: relative;">
         <? 
         if ($TipoPrenotazione==1)
             echo("<strong>".$ClienteNome."</strong>");
         else
             echo($ClienteNome);
         
         ?>
         </li>
         </ul>
        <?
            } 
          
        }
        ?>
        

    
      
        

        
</div>    
	
            
         
	
                              <span class="vuoto"></span>  
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
         
         
         
         
         
                                
            <br/><hr/><hr/><br/>
            	<!-- contolla i campi sottostanti -->
             
        </div>
	</div>
</div><!-- fine container_tabella -->

<!-- ETICHETTE -->



<?      $contanavette++;
        }
        ?>
                  
   
         <?
        $nt++;
    }
}
?>



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
		                stampa_organizzazione();    
                		break;
			}
		

	

} 
// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}
?>