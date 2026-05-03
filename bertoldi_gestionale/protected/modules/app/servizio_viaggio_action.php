<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
include_once($classespath_."class.Form.php");
include_once($classespath_."/class.Sede.php");

include_once($classespath_."/class.Fermata.php");
include_once($classespath_."/class.Tratta.php");
include_once($classespath_."/class.Prenotazione.php");
include_once($classespath_."/class.Corsa.php");
include_once($classespath_."/class.Orario.php");
include_once($classespath_."/class.Listino.php");
include_once($classespath_."/class.TipologiaBus.php");

include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.DT.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once ($classespath_ . "Graph/class.GraphUtil.php");
include_once($classespath_."class.GestioneOttimizzataFlotta.php");
include_once($classespath_."class.PreparazioneBusAutista.php");
include_once($classespath_."class.GestioneOttimizzataPasseggeri.php");
include_once($classespath_."class.GestioneOttimizzataModifiche.php");
include_once($classespath_."class.GestioneOttimizzataNodi.php");
include_once($classespath_."class.Flotta.php");
include_once($classespath_."class.Autisti.php");


$errors=new Errors();
$ModuloId=1;

function PreparaPasseggeroBus()
{
	global $user,$db;
	
	$storico=new StoricoOperazioni();
	$storico->conn=$db;

	$tempBus=$_REQUEST['BusId'];
	$arr_ptn =  explode("_", $tempBus);
	$busId=$arr_ptn[1];
	$comuneId=$arr_ptn[2];
	$tempBus=$_REQUEST['Prenotazione'];
	$arr_ptn =  explode("_", $tempBus);
	$prenotazioneNumero = $arr_ptn[1];
	
	$corsaId=$_REQUEST['CorsaId'];
	$dataPartenza=$_REQUEST['DataPartenza'];
	$lineaId=$_REQUEST['LineaId'];

	$sql = "SELECT Count(*) as tot FROM RT_GestioneOttimizzataPasseggeri
			WHERE LineaId=$lineaId
			AND CorsaId=$corsaId
			AND CorsaDataPartenza='$dataPartenza'
			AND Bus=$busId
			AND PrenotazioneNumero=$prenotazioneNumero
			AND Comune=$comuneId";

	$row = $db->query_first($sql);
	if($row['tot']==0){
		
	
		$dettaglio = new PrenotazioneDettaglio();
		$dettaglio->conn=$db;
		$infoPrenotazione = $dettaglio->getPrenotazioneDettaglio($prenotazioneNumero, $lineaId, $corsaId, $dataPartenza);
		//controllo se comune di salita � lungo il tragitto dell'bus
		$gestioneNodi = new GestioneOttimizzataNodi($db);
		if(!$gestioneNodi->existPartenza($lineaId, $corsaId, $dataPartenza, $busId, $infoPrenotazione['ComuneSalitaId'])){
			//comune di partenza non presente sulla tratta
			echo "Il comune di salita del passeggero".$info['Cognome']." ".$info['Nome']." non � attraversato da questo autobus";	
		}else{
			//selezione bus del viaggio
			$busPresi = array();
			$busViaggio = busInterscambio($lineaId, $corsaId, $dataPartenza, $busId, $infoPrenotazione['ComuneDiscesaId'], $gestioneNodi, $busPresi);
			if($busViaggio!=null){
				$gestionePasseggeri = new GestioneOttimizzataPasseggeri($db);
				$flotta = new Flotta();
				$flotta->conn = $db;
				
				$comunePartenza = $infoPrenotazione['ComuneSalitaId'];
				$comuneArrivo = $busViaggio[sizeof($busViaggio)-1]['Comune'];
				$posto=true;
				
				for($ii=(sizeof($busViaggio)-1);$ii>=0;$ii--){
					$numPostiTipoBus = $flotta->getNumPosti($busViaggio[$ii]['Bus']);
					 
					$comuni = $gestionePasseggeri->getComuniBus($lineaId, $corsaId, $dataPartenza, $busViaggio[$ii]['Bus']);
					foreach ($comuni as $key=>$comune){
					
						if(strcmp($comunePartenza, $comune['Comune'])==0){
							$indexPartenza = $comune['Ordine'];
						}
						if(strcmp($comuneArrivo, $comune['Comune'])==0 ){
							$indexArrivo = $comune['Ordine'];
						}

					}
										
					$comunePartenza = $comuneArrivo;
					if($ii-1<0){
						$comuneArrivo=$busViaggio[0]['Comune'];
					}else{
						$comuneArrivo=$busViaggio[$ii-1]['Comune'];
					}
											
					for($kk=$indexPartenza; $kk<=$indexArrivo; $kk++){
						$tot = $gestionePasseggeri->getNumPasseggeriBusComune($lineaId, $corsaId, $dataPartenza, $busViaggio[$ii]['Bus'], $busViaggio[$ii]['Comune']);
						if($tot['Tot']>$numPostiTipoBus){
							$posto=false;
						}
					}
				}
				if($posto==true){
					
					$sql = "DELETE FROM RT_GestioneOttimizzataPasseggeri
							WHERE PrenotazioneNumero=$prenotazioneNumero
							AND LineaId=$lineaId
							AND CorsaId=$corsaId
							AND CorsaDataPartenza='$dataPartenza'";
					$db->query($sql);
					
					$storico = new StoricoOperazioni();
					$storico->conn = $db;
					
				$comunePartenza = $infoPrenotazione['ComuneSalitaId'];
				$comuneArrivo = $busViaggio[sizeof($busViaggio)-1]['Comune'];
					
					for($ii=(sizeof($busViaggio)-1);$ii>=0;$ii--){						
						
						$numPostiTipoBus = $flotta->getNumPosti($busViaggio[$ii]['Bus']);
						$comuni = $gestionePasseggeri->getComuniBus($lineaId, $corsaId, $dataPartenza, $busViaggio[$ii]['Bus']);
						foreach ($comuni as $key=>$comune){
							if(strcmp($comunePartenza, $comune['Comune'])==0){
								$indexPartenza = $comune['Ordine'];
							}
							if(strcmp($comuneArrivo, $comune['Comune'])==0){
								$indexArrivo = $comune['Ordine'];
							}
	
						}
						
						$comunePartenza = $comuneArrivo;
						if($ii-1<0){
							$comuneArrivo=$busViaggio[0]['Comune'];
						}else{
							$comuneArrivo=$busViaggio[$ii-1]['Comune'];
						}
											
						for($kk=$indexPartenza; $kk<$indexArrivo; $kk++){
							
							$passeggero['LineaId'] = $lineaId;
							$passeggero['CorsaId'] = $corsaId;
							$passeggero['CorsaDataPartenza'] = $dataPartenza; 
							$passeggero['Comune'] = $comuni[$kk]['Comune'];
							$passeggero['ComuneArrivo'] = $infoPrenotazione['ComuneDiscesaId'];
							$passeggero['Bus'] = $busViaggio[$ii]['Bus'];
							$passeggero['PrenotazioneNumero'] = $prenotazioneNumero;
							$passeggero['Ordine'] = $kk;
							$passeggero = $storico->operazioni_insert($passeggero, $user);
							$db->insert("RT_GestioneOttimizzataPasseggeri", $passeggero);
						}
					}
					$sql = "DELETE FROM RT_GestioneOttimizzataModifiche 
							WHERE Lineaid=$lineaId 
							AND CorsaId=$corsaId 
							AND CorsaDataPartenza='$dataPartenza'
							AND PrenotazioneNumero=$prenotazioneNumero";
					$db->query($sql);
					$sql = "DELETE FROM RT_GestionePrenotazioniSenzaBus
							WHERE Lineaid=$lineaId
							AND CorsaId=$corsaId
							AND DataPartenza='$dataPartenza'
							AND NumeroPrenotazione=$prenotazioneNumero";
					$db->query($sql);
					echo "ok";
				}else{
					echo "Posti non disponibili";
				}
			}else{
				echo ("Non ci sono autobus per permettere al passeggero di andare da ".$infoPrenotazione['ComunePartenza']." a ".$infoPrenotazione['ComuneArrivo']); 
			}
		}
	}else{
		echo "ok";
	}
}

function busInterscambio($lineaId, $corsaId, $dataPartenza, $bus, $comuneArrivo, $gestioneNodi, $busPresi){
	//caso base bus arriva a comune
	if($gestioneNodi->existArrivo($lineaId, $corsaId, $dataPartenza, $bus, $comuneArrivo)){
		$temp['Comune'] = $comuneArrivo;
		$temp['Bus'] = $bus;
		$vector[] = $temp;
		return $vector;
	}
	
	//caso iterativo
	$busPresi[] = $bus;
	$busArray = $gestioneNodi->getBusIncontro($lineaId, $corsaId, $dataPartenza, $bus);
	foreach ($busArray as $k=>$b){
		if(!in_array($b['BusPartenza'],$busPresi)){
			$responce[] = busInterscambio($lineaId, $corsaId, $dataPartenza, $b['BusPartenza'], $comuneArrivo, $gestioneNodi, $busPresi);
		}
	}
	foreach ($responce as $k=>$r){
		if($r!=null){
			$temp['Comune'] = $busArray[$k]['Comune'];
			$temp['Bus'] = $bus;
			$r[]=$temp;
			return $r;
		}
	}
	 return null;	
}

function RimuoviPasseggeroBus(){
	global $user,$db;

	$prenotazione = $_REQUEST['Prenotazione'];
	$corsaId = $_REQUEST['CorsaId'];
	$dataPartenza = $_REQUEST['DataPartenza'];
	$lineaId = $_REQUEST['LineaId'];

	$arr_ptn =  explode("_", $prenotazione);
	$prenotazioneNumero = $arr_ptn[1];
	
	$sql = "SELECT Count(*) as tot  
			FROM RT_GestionePrenotazioniSenzaBus 
			WHERE LineaId=$lineaId 
			AND CorsaId=$corsaId 
			AND DataPartenza='$dataPartenza' 
			AND NumeroPrenotazione=$prenotazioneNumero";
	echo $sql;
	$row = $db->query_first($sql);
	if($row['tot']==0){
		$db->delete("RT_GestioneOttimizzataPasseggeri","PrenotazioneNumero=$prenotazioneNumero AND CorsaId=$corsaId AND LineaId=$lineaId AND CorsaDataPartenza='$dataPartenza'");
		
		$senzaBus['LineaId'] = $lineaId;
		$senzaBus['CorsaId'] = $corsaId;
		$senzaBus['DataPartenza'] = $dataPartenza;
		$senzaBus['NumeroPrenotazione'] = $prenotazioneNumero;
		$db->insert("RT_GestionePrenotazioniSenzaBus", $senzaBus);
		
		$operazione['LineaId']=$lineaId;
		$operazione['CorsaId']=$corsaId;
		$operazione['CorsaDataPartenza']=$dataPartenza;
		$operazione['PrenotazioneNumero']=$prenotazioneNumero;
		$prenotazioneDettaglio = new PrenotazioneDettaglio();
		$prenotazioneDettaglio->conn = $db;
		$operazione['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione($prenotazioneNumero, $lineaId, $corsaId, $dataPartenza);
		$operazione['Aggiungi']=2;
		$db->insert('RT_GestioneOttimizzataModifiche',$operazione);
	}
}


function CreaNuovoBus(){
	global $db, $user;
	
	$corsaId = $_POST['corsaId'];
	$dataPartenza = $_POST['dataPartenza'];
	$lineaId = $_POST['lineaId'];
	$step = $_POST['step_sucessivo'];
	
	$comuni = $_POST['nodo'];
	$tot = sizeOf($comuni);
	
	$gruppo = new LineaGraph($lineaId, null, null, $db, true);
	
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	$flottaId = $_POST['FlottaId'];
	
	$tableFlotta = new GestioneOttimizzataFlotta();
	$tableFlotta->conn = $db;
	
	$flotta['Nome'] = $tableFlotta->getMaxNome($lineaId, $corsaId, $dataPartenza) + 1;
	
	$gestioneFlotta['BusId'] = $flottaId;
	$gestioneFlotta['Nome'] = $flotta['Nome'];
	$gestioneFlotta['CorsaId'] = $corsaId;
	$gestioneFlotta['LineaId'] = $lineaId;
	$gestioneFlotta['CorsaDataPartenza'] = $dataPartenza;
	
	$km = $gruppo->graph->edges[$_POST['inizioComune']."-".$comuni[0]]->peso;
	for($ii=0; $ii<$tot; $ii++){
		$km += $gruppo->graph->edges[$comuni[$ii]."-".$comuni[$ii+1]]->peso;
	}
	$gestioneFlotta['KmPercorsi'] = $km;
	
	$gestioneFlotta=$storico->operazioni_insert($gestioneFlotta, $user);
	$busId = $db->insert("RT_GestioneOttimizzataFlotta", $gestioneFlotta);
	
	$prenotazioneBusAutisti['CorsaId'] = $corsaId;
	$prenotazioneBusAutisti['LineaId'] = $lineaId;
	$prenotazioneBusAutisti['DataPartenza'] = $dataPartenza;
	$prenotazioneBusAutisti['Autista1'] = $_POST['Autista1'];
	$prenotazioneBusAutisti['Autista2'] = $_POST['Autista2'];
	$prenotazioneBusAutisti['BusId'] = $busId;
	$prenotazioneBusAutisti=$storico->operazioni_insert($prenotazioneBusAutisti, $user);
	$lastidA=$db->insert("RT_PreparazioneBusAutisti", $prenotazioneBusAutisti);
	
	
	
	$busPartenza['LineaId'] = $lineaId;
	$busPartenza['CorsaId'] = $corsaId;
	$busPartenza['CorsaDataPartenza'] = $dataPartenza;
	$busPartenza['Comune'] = $_POST['inizioComune'];
	$busPartenza['BusPartenza'] = $busId;
	$busPartenza['Ordine'] = 0;
	
	$tratta = $gruppo->graph->edges[$_POST['inizioComune']."-".$comuni[0]]->info3;
	$tt = array_shift($tratta);
	
	$busPartenza['TrattaId'] = $tt;
	
	$busPartenza = $storico->operazioni_insert($busPartenza, $user);
	$lastidA=$db->insert("RT_GestioneOttimizzataNodo", $busPartenza);
	
	for($ii=0; $ii<$tot-1; $ii++){
		$busPartenza['LineaId'] = $lineaId;
		$busPartenza['CorsaId'] = $corsaId;
		$busPartenza['CorsaDataPartenza'] = $dataPartenza;
		$busPartenza['Comune'] = $comuni[$ii];
		$busPartenza['BusPartenza'] = $busId;
		$busPartenza['Ordine'] = $ii+1;
		
		$tratta = $gruppo->graph->edges[$comuni[$ii]."-".$comuni[$ii+1]]->info3;
		$tt = array_shift($tratta);
		$busPartenza['TrattaId'] = $tt;
		
		$busPartenza = $storico->operazioni_insert($busPartenza, $user);
		$lastidA=$db->insert("RT_GestioneOttimizzataNodo", $busPartenza);
		
		$busArrivo['LineaId'] = $lineaId;
		$busArrivo['CorsaId'] = $corsaId;
		$busArrivo['CorsaDataPartenza'] = $dataPartenza;
		$busArrivo['Comune'] = $comuni[$ii];
		$busArrivo['BusArrivo'] = $busId;
		$busArrivo['Ordine'] = $ii+1;
		
		$tratta = $gruppo->graph->edges[$comuni[$ii]."-".$comuni[$ii+1]]->info3;
		$tt = array_shift($tratta);
		$busArrivo['TrattaId'] = $tt;
		
		$busArrivo = $storico->operazioni_insert($busArrivo, $user);
		$lastidA=$db->insert("RT_GestioneOttimizzataNodo", $busArrivo);
	}
	
	$busArrivo['LineaId'] = $lineaId;
	$busArrivo['CorsaId'] = $corsaId;
	$busArrivo['CorsaDataPartenza'] = $dataPartenza;
	$busArrivo['Comune'] = $comuni[$tot-1];
	$busArrivo['BusArrivo'] = $busId;
	$busArrivo['Ordine'] = $tot;
	
	$tratta = $gruppo->graph->edges[$comuni[$tot-2]."-".$comuni[$tot-1]]->info3;
	$tt = array_shift($tratta);
	$busArrivo['TrattaId'] = $tt;
	
	$busArrivo = $storico->operazioni_insert($busArrivo, $user);
	$lastidA=$db->insert("RT_GestioneOttimizzataNodo", $busArrivo);
	
	
	echo json_encode(array('corsaId'=>$corsaId, 'dataPartenza'=>$dataPartenza, 'lineaId'=>$lineaId, 'step'=>$step));
}

function GetNodi(){
	global $db;
	
	$fermataObj = new Fermata();
	$fermataObj->conn = $db;
	
	$last = $_POST['last'];
	$lineaId = $_POST['LineaId'];
	$comune = $_POST['Comune'];
	$corsaId = $_POST['CorsaId'];
	$dataPartenza = $_POST['DataPartenza'];
	$destinazione = $_POST['Destinazione'];
	
	$gruppo = new LineaGraph($lineaId, $corsaId, $dataPartenza, $db, true);
	$nodes = array();
	$child = array();
	$search = false;
	while($search==false){	
		if(sizeof($gruppo->graph->nodes[$comune]->children)>1){
			if(!$fermataObj->isInterscambioLinea($lineaId, $comune) && !$fermataObj->isInizioTratta($lineaId, $comune)){
				$trattaId = $fermataObj->getTrattaComuniLinea($last, $comune, $lineaId);
				$tempComune = $fermataObj->getProssimoComune($trattaId, $comune);
				if(in_array($destinazione, $gruppo->graph->nodes[$tempComune]->descents)){
					$nodes[] = $tempComune;
					$last = $comune;
					$comune = $tempComune;
				}else{
					$tempArray = array();
					foreach ($gruppo->graph->nodes[$comune]->children as $key=>$c){
						if(strcmp($c,$destinazione)==0){
							$tempArray = array();
							$tempArray[] = $c;
							$child = array();
							$search = true;
						}else if($search == false){
							if(in_array($destinazione, $gruppo->graph->nodes[$c]->descents)){
								$tempArray[] = $c;
							}
						}
					}
					if(sizeof($tempArray)>1){
						foreach ($tempArray as $temp){
							$child[] = $temp;
						}
						$search = true;
					}else{
						$child = array();
						$nodes[] = $tempArray[0];
						$last = $comune;
						$comune = $tempArray[0];
					}
				}	
			}else{
				$tempArray = array();
				foreach ($gruppo->graph->nodes[$comune]->children as $key=>$c){
					if(strcmp($c,$destinazione)==0){
						$tempArray = array();
						$tempArray[] = $c;
						$child = array();
						$search = true;
					}else if($search == false){
						if(in_array($destinazione, $gruppo->graph->nodes[$c]->descents)){
							$tempArray[] = $c;
						}
					}
				}
				if(sizeof($tempArray)>1){
					foreach ($tempArray as $temp){
						$child[] = $temp;
					}
					$search = true;
				}else{
					$child = array();
					$nodes[] = $tempArray[0];
					$last = $comune;
					$comune = $tempArray[0];
				}
			}	
		}else if(sizeof($gruppo->graph->nodes[$comune]->children)==1){
			foreach ($gruppo->graph->nodes[$comune]->children as $key=>$c){
				$last = $comune;
				$comune = $c;
				$nodes[] = $c;
				if(strcmp($comune,$destinazione)==0){
					$search=true;
					$child = array();
				}
			}
		}else{
			$search = true;
		}
	}
	
	$nodesNomi = array();
	if(sizeof($nodes)>0){
		foreach ($nodes as $index=>$node){
			$comuneObj = new Comune($node);
			$comuneObj->conn = $db;
			$comuneObj->inizializzaDatiGenerali();
			$nodesNomi[$index] =  $comuneObj->Comune;
		}
	}
	
	$childNomi = array();
	if(sizeof($child)>0){
		foreach ($child as $index=>$node){
			$comuneObj = new Comune($node);
			$comuneObj->conn = $db;
			$comuneObj->inizializzaDatiGenerali();
			$childNomi[$index] =  $comuneObj->Comune;
		}
	}
	echo json_encode(array('nodes'=>$nodes, 'child'=>$child, 'nodesNomi'=>$nodesNomi, 'childNomi'=>$childNomi));
}


function AggiungiBus(){
	global $user, $HtmlCommon, $db;
	include_once("previaggio_validator.php");
	$lineaId = $_POST['lineaId'];
	$comune = $_POST['comuneId'];
	$corsaId = $_POST['corsaId'];
	$dataPartenza = $_POST['dataPartenza'];
	
	$c = new Comune($comune);
	$c->conn = $db;
	$c->inizializzaDatiGenerali();

	$HtmlCommon->html_titolo_pagina("Aggiungi Bus - Comune di partenza: $c->Comune",0,"","");
	$HtmlCommon->html_titolo_box("Aggiungi Bus - Comune di partenza: $c->Comune");
	
	$gruppo = new LineaGraph($lineaId, $corsaId, $dataPartenza, $db, true);
	$page=new Form();
	$arr_comuni = array();
	foreach ($gruppo->graph->nodes[$comune]->descents as $desc){
		$c = new Comune($desc);
		$c->conn = $db;
		$c->inizializzaDatiGenerali();
		$temp1['ComuneId'] = $desc;
		$temp1['Comune'] =  $c->Comune;
		$arr_comuni[] = $temp1;
	}
	
	$flottaObj = new Flotta();
	$flottaObj->conn = $db;
	$arr_flotta = $flottaObj->getAllForSelect();
	$autisti =  new Autista();
	$autisti->conn = $db;
	$arr_autisti = $autisti->getAllForSelect();
	?>
	<script type="text/javascript">
		$('#FinePercorso').change(function(){
			$('#selectPercorso').children().remove();
			ProssimoNodo(<?=$comune?>, $("#FinePercorso").val());
		});
	</script>
	
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
		<div class="brain_formModifica formGestoreEdita">
			<form id="application_form_addBus" name="application_form"  method="post" action="#">
					<div class="brain_data-content">
		               <?
		               $page->create_textbox_hidden("action","CreaNuovoBus");
		               $page->create_textbox_hidden("corsaId", $corsaId);
		               $page->create_textbox_hidden("lineaId", $lineaId);
		               $page->create_textbox_hidden("dataPartenza", $dataPartenza);
		               $page->create_textbox_hidden("step_sucessivo", 1);
		               $page->create_textbox_hidden("inizioComune", $comune);
		               $page->create_textbox_hidden("last", $comune);

		               $page->create_select("Autobus","FlottaId","FlottaId","brain_campiform",$arr_flotta, -1, "FlottaId","Targa",null,1);
		               print("<br style=\"clear:both;\"/>");
		               $page->create_select("Autista1","Autista1","Autista1","brain_campiform",$arr_autisti,-1,"AutistiId","Autisti",null,0);
		               $page->create_select("Autista2","Autista2","Autista2","brain_campiform",$arr_autisti,-1,"AutistiId","Autisti",null,0);
		               print("<br style=\"clear:both;\"/>");
		               print("<br style=\"clear:both;\"/>");
		               $page->create_select("Fine Percorso","FinePercorso","FinePercorso","brain_campiform",$arr_comuni, -1, "ComuneId","Comune",null,1);
		               print("<br style=\"clear:both;\"/>");
		               ?>
		               <br/>
		               Definire il tragitto che l'autobus deve percorrerre scegliendo per quali comuni proseguire in caso si incontrano punti di interscambio lungo il tragitto<br/>
               		   <div id="selectPercorso">
               		   </div>
               		   <?php
               		   print("<br style=\"clear:both;\"/>");
		               $page->create_button("Registra dati autobus","Salva","Salva","brain_salva","submit"); 
		               ?>
	        		</div>
        		</form>
		</div>
	</div>
	
	
	<?php
}

function ResetOrganizzazione(){
	global $user, $db;
	$lineaId = $_POST['LineaId'];
	$CorsaId = $_POST['CorsaId'];
	$DataPartenza = $_POST['DataPartenza'];
	$gruppo = GraphUtil::merge($lineaId, $CorsaId, $DataPartenza, $db, true);
// 	$gruppo = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,1);
//     $gruppo1 = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,2);
//     $gruppo->mergeFlotta($gruppo1, false);
//     $gruppo2 = new LineaGraph($lineaId, $CorsaId, $DataPartenza, $db, false,null,0,false,3);
//     $gruppo->mergeFlotta($gruppo2, true);
}

function UpdateAutobus(){
	global $user, $db;
	
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	$dt = new DT();
	$corsaId = $_POST['CorsaId'];
	$lineaId = $_POST['LineaId'];
	$dataPartenza = $_POST['DataPartenza'];
	$flottaId = $_POST['FlottaId'];
	$busId = $_POST['BusId'];
	
	$autobus['BusId'] = $flottaId;
	$db->update("RT_GestioneOttimizzataFlotta",$autobus,"GestioneOttimizzataFlottaId=$busId");
	
	$prenotazioneBusAutisti['CorsaId'] = $corsaId;
	$prenotazioneBusAutisti['LineaId'] = $lineaId;
	$prenotazioneBusAutisti['DataPartenza'] = $dataPartenza;
	$prenotazioneBusAutisti['Autista1'] = $_POST['Autista1'];
	$prenotazioneBusAutisti['Autista2'] = $_POST['Autista2'];
	$prenotazioneBusAutisti['BusId'] = $busId;
	
	$busAutista = new PreparazioneBusAutista($db);
	$idBusAutisti = $busAutista->exist($dataPartenza, $corsaId, $lineaId, $busId);
	if($idBusAutisti>0){
		$db->update("RT_PreparazioneBusAutisti",$prenotazioneBusAutisti,"PreparazioneBusAutistiId=$idBusAutisti");
	}else{
		$prenotazioneBusAutisti=$storico->operazioni_insert($prenotazioneBusAutisti, $user);
		$lastidA=$db->insert("RT_PreparazioneBusAutisti", $prenotazioneBusAutisti);
	}
	
	$tipologiaBus = new TipologiaBus();
	$tipologiaBus->conn = $db;
	$numPosti = $tipologiaBus->getNumPosti($flottaId);
	$gestionePasseggeri = new GestioneOttimizzataPasseggeri($db);
	$numPasseggeri = $gestionePasseggeri->getNumPasseggeri($lineaId, $corsaId, $dataPartenza, $busId);
	foreach ($numPasseggeri as $key=>$dest){ 
		if($numPosti<$dest['totale']){
			$passeggeri = $gestionePasseggeri->getPasseggeri($lineaId, $corsaId, $dataPartenza, $busId, $dest['Comune']);
			for($i=$numPosti;$i<sizeof($passeggeri);$i++){
				foreach ($numPasseggeri as $temp=>$value){
					if($gestionePasseggeri->isExist($lineaId, $corsaId, $dataPartenza, $passeggeri[$i]['PrenotazioneNumero'], $busId, $dest['Comune'])){
						$value--;
					}
				}
				$gestionePasseggeri->deletePasseggero($lineaId, $corsaId, $dataPartenza, $passeggeri[$i]['PrenotazioneNumero']);
				$operazione['LineaId']=$lineaId;
				$operazione['CorsaId']=$corsaId;
				$operazione['CorsaDataPartenza']=$dataPartenza;
				$operazione['PrenotazioneNumero']=$passeggeri[$i]['PrenotazioneNumero'];
				$prenotazioneDettaglio = new PrenotazioneDettaglio();
				$prenotazioneDettaglio->conn = $db;
				$operazione['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione($passeggeri[$i]['PrenotazioneNumero'], $lineaId, $corsaId, $dataPartenza);
				$operazione['Aggiungi']=2;
				$db->insert('RT_GestioneOttimizzataModifiche',$operazione);
			}
		}
	}
	
	
}

function DettaglioPasseggeriBus(){

	global $user, $HtmlCommon, $db;
	$user=new Operatore();
        $user->OperatoreId=1;
        $user->OdcId=1;
        $user->GestoreId=1;
        $user->IsAdmin=1;
        
	$db= new Database();
	$db->connect();
	
	$removeDiscesa = $_POST['remove'];
	$lineaId = $_POST['lineaId'];
	$comune = $_POST['comuneId'];
	$indexComune = $_POST['indexComune'];
	$busId =  $_POST['busId'];
	$corsaId = $_POST['corsaId'];
	$title = $_POST['title'];
	$dataPartenza = $_POST['dataPartenza'];
	$salitePickup = $_POST['salitePickup'];
	$saliteInterscambio = $_POST['saliteInterscambio'];
	$disceseDropoff = $_POST['disceseDropoff'];
	$disceseInterscambio = $_POST['disceseInterscambio'];
	$totPasseggeri = $_POST['totPassegeri'];
	
	$c = new Comune($comune);
	$c->conn = $db;
	$c->inizializzaDatiGenerali();
	
	$HtmlCommon->html_titolo_pagina("$title / Dettaglio $c->Comune",0,"","");
	$HtmlCommon->html_titolo_box ("$title / Dettaglio $c->Comune");
	
	$gestioneOttimizzata = new GestioneOttimizzataFlotta(null, $db);
	$gestioneOttimizzata->conn = $db;
	$caricaDB=false;
	if($gestioneOttimizzata->getNumBus($lineaId, $corsaId, $dataPartenza)>0){
		$caricaDB=true;
	}else{
		$caricaDB=false;
	}
	//$gruppo = new LineaGraph($lineaId, $corsaId, $dataPartenza, $db, $caricaDB);
        
        $gruppo = new LineaGraph($lineaId, $corsaId, $dataPartenza, $db, $caricaDB,null,0,false,0);
               
        
	$p = new PrenotazioneDettaglio(null);
	$p->conn = $db;
	
	?>
		
		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
			<div class="brain_formModifica formGestoreEdita" style="width: 91% !important;">
				<h1>Riepilogo</h1>
				<br/>
				<table width="100%" cellspacing="0" cellpadding="0" border="0"
						class="table" id="gestoreElencoAule">
						<thead>
							<tr class="rowIntestazione">
								<td>Salite</td>
								<td>Salite (I)</td>
								<td>Discese</td>
								<td>Discese (I)</td>
								<td>A bordo</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?=$salitePickup?></td>
								<td><?=$saliteInterscambio?></td>
								<td><?=$disceseDropoff?></td>
								<td><?=$disceseInterscambio?></td>
								<td><?=$totPasseggeri?></td>
							</tr>
						</tbody>
				</table>
				<br/>
				<h1>Dettaglio</h1>
				<br/>
				Salite
				<table width="100%" cellspacing="0" cellpadding="0" border="0"
						id="gestoreElencoAule">
						<tbody>
							<tr class="rowIntestazione">
								
								<td>Cliente</td>
                                <td>Tel</td>
								<td>Da</td>
								<td>A</td>
								<td>Prov</td>
								<?php if(Config::$opzioneCongiunti) { ?>
									<td>Congiunti</td>
								<?php } ?>
							</tr>
							
							<?php
									
									//persone che salgono dalla fermata
									if($gruppo->graph->nodes[$comune]->salite>0){
										foreach ($gruppo->graph->nodes[$comune]->bigliettiSalite as $dest=>$passeg){					
											foreach ($passeg as $value){
												if(in_array($value, $gruppo->flotta[$busId]->comuni[$indexComune]['passeggeri'][$dest])){
													$r = $p->getPrenotazioneDettaglio($value, $_POST['lineaId'], $_POST['corsaId'],$_POST['dataPartenza']);	
													?>
													<tr>
														
														<td>
															<?=  utf8_decode($r['Nome']." ".$r['Cognome'])?>	
														</td>
                                                        <td>
                                                        	<a href="tel:<?=$r['ClienteCellulare']?>"><?=$r['ClienteCellulare']?></a>
                                                        </td>
														<!--<td>
															<?=$r['ComunePartenza']." - ".$r['FermataPartenza']?>						
														</td>
														<td>
															<?=$r['ComuneArrivo']." - ".$r['FermataArrivo']?>						
														</td>-->
                                                                                                                
                                                                                                                <!--<td>
															<?=$r['ComunePartenza']." - ".$r['FermataPartenza']?>						
														</td>
														<td>
															<?=$r['ComuneArrivo']." - ".$r['FermataArrivo']?>						
														</td>-->                                                  
                                                       <td>
															<?=$r['ComunePartenza']?>						
														</td>
														<td>
															<?=$r['ComuneArrivo']?>						
														</td>
														<td>                                                      
															/
														</td>
														<?php if(Config::$opzioneCongiunti) { ?>
															<td>                                                      
																<?php if($r['Congiunti'] == 1) { ?>
																	Si - <?=$r['CodicePrenotazione']?>
																<?php } else { ?>
																	No
																<?php } ?>
															</td>
														<?php } ?>
													</tr>
													<?php 	
												}
											}
										}	
									}
									
									//persone che salgono dagli altri bus
									if(sizeof($gruppo->graph->nodes[$comune]->busPartenza)>=1 || sizeof($gruppo->graph->nodes[$comune]->busArrivo)>=1){
										foreach ($gruppo->graph->nodes[$comune]->busArrivo as $tempBusId){
											if($tempBusId != $busId){
												$indexComuneTempBusId = -1;
												foreach ($gruppo->flotta[$tempBusId]->comuni as $key=>$value){
									
													if(strcmp($value['comune'],$comune)==0){
														$indexComuneTempBusId = $key;
													}
												}
												foreach ($gruppo->flotta[$busId]->comuni[$indexComune]['passeggeri'] as $key => $passeggeri){
													foreach ($passeggeri as $pass){
														if(in_array($pass, $gruppo->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri'][$key])){
															$r = $p->getPrenotazioneDettaglio($pass, $_POST['lineaId'], $_POST['corsaId'],$_POST['dataPartenza']);
															?>
															<tr>
																<!--<td><?=$p->getCodicePrenotazione($pass, $lineaId, $corsaId, $dataPartenza)?></td>-->
																<td>
																	<?=utf8_decode($r['Nome']." ".$r['Cognome'])?>	
																</td>
                                                                <td>
                                                                	<a href="tel:<?=$r['ClienteCellulare']?>"><?=$r['ClienteCellulare']?></a>
                                                                </td>
																<td>
																	<?=$r['ComunePartenza']?>						
																</td>
																<td>
																	<?=$r['ComuneArrivo']?>						
																</td>
																<td>
																	<?="B ".$gruppo->flotta[$tempBusId]->nome?>
																</td>
																<?php if(Config::$opzioneCongiunti) { ?>
																	<td>                                                      
																		<?php if($r['Congiunti'] == 1) { ?>
																			Si - <?=$r['CodicePrenotazione']?>
																		<?php } else { ?>
																			No
																		<?php } ?>
																	</td>
																<?php } ?>
															</tr>
															<?php	
														}
													}
												}
											}
										}
									}
							
 							?>
							
						</tbody>
				</table>
				
				<br/>
				Discese
				<table width="100%" cellspacing="0" cellpadding="0" border="0"
						id="gestoreElencoAule">
						<tbody>
							<tr class="rowIntestazione">
                                <td>Cliente</td>
                                <td>Tel</td>
								<td>Da</td>
								<td>A</td>
								<td>Bus</td>
								<?php if(Config::$opzioneCongiunti) { ?>
									<td>Congiunti</td>
								<?php } ?>
							</tr>
							<?php
								//persone che scendono alla fermata
								if($gruppo->graph->nodes[$comune]->discese>0){
									foreach ($gruppo->flotta[$busId]->comuni[$indexComune-1]['passeggeri'][$comune] as $value){
										if(in_array($value, $gruppo->flotta[$busId]->comuni[$indexComune-1]['passeggeri'][$comune])){
											$r = $p->getPrenotazioneDettaglio($value, $_POST['lineaId'], $_POST['corsaId'],$_POST['dataPartenza']);
											?>
											<tr>
												<td>
													<?=utf8_decode($r['Nome']." ".$r['Cognome'])?>		
												</td>
                                                <td>
                                                	<a href="tel:<?=$r['ClienteCellulare']?>"><?=$r['ClienteCellulare']?></a>
                                                </td>
												<td>
													<?=$r['ComunePartenza']?>						
												</td>
												<td>
													<?=$r['ComuneArrivo']?>						
												</td>
												<td>
													/
												</td>
												<?php if(Config::$opzioneCongiunti) { ?>
													<td>                                                      
														<?php if($r['Congiunti'] == 1) { ?>
															Si - <?=$r['CodicePrenotazione']?>
														<?php } else { ?>
															No
														<?php } ?>
													</td>
												<?php } ?>
											</tr>
											<?php 
										}
									}
								}
								
								//persone che scendono per salire in altri bus
								
								$last = sizeof($bus->comuni)-1;
								
								$removeDiscesaP = array();
								if(sizeof($gruppo->graph->nodes[$comune]->busPartenza)>=1 || sizeof($gruppo->graph->nodes[$comune]->busArrivo)>=1){
									foreach ($gruppo->graph->nodes[$comune]->busPartenza as $tempBusId){
										if($tempBusId != $busId){
											$indexComuneTempBusId = -1;
											foreach ($gruppo->flotta[$tempBusId]->comuni as $key=>$value){
												if(strcmp($value['comune'],$comune)==0){
													$indexComuneTempBusId = $key;
												}
											}
											foreach ($gruppo->flotta[$busId]->comuni[$indexComune-1]['passeggeri'] as $key => $passeggeri){
												foreach ($passeggeri as $value){
													if(in_array($value, $gruppo->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])){
														$r = $p->getPrenotazioneDettaglio($value, $_POST['lineaId'], $_POST['corsaId'],$_POST['dataPartenza']);
														$removeDiscesaP[$value] = $value;
														?>
														<tr>
															<td>
																<?=utf8_decode($r['Nome']." ".$r['Cognome'])?>	
															</td>
                                                            <td>
                                                            	<a href="tel:<?=$r['ClienteCellulare']?>"><?=$r['ClienteCellulare']?></a>
                                                            </td>
															<td>
																<?=$r['ComunePartenza']?>						
															</td>
															<td>
																<?=$r['ComuneArrivo']?>						
															</td>
															<td>
																Autobus <?=$gruppo->flotta[$tempBusId]->nome?>
															</td>
															<?php if(Config::$opzioneCongiunti) { ?>
																<td>                                                      
																	<?php if($r['Congiunti'] == 1) { ?>
																		Si - <?=$r['CodicePrenotazione']?>
																	<?php } else { ?>
																		No
																	<?php } ?>
																</td>
															<?php } ?>
														</tr>
														<?php
													}
												}
											}
										}
									}
								}
							?>
							
						</tbody>
				</table>
				
				<br/>
				Passeggeri sul Bus
				<table width="100%" cellspacing="0" cellpadding="0" border="0"
						id="gestoreElencoAule">
						<tbody>
							<tr class="rowIntestazione">
								<td>Cliente</td>
                                <td>Tel</td>
								<td>Da</td>
								<td>A</td>
								<?php if(Config::$opzioneCongiunti) { ?>
									<td>Congiunti</td>
								<?php } ?>
							</tr>
							<?php
							foreach ($gruppo->flotta[$busId]->comuni[$indexComune]['passeggeri'] as $idDest=>$arrayPasseggeri){
								foreach ($arrayPasseggeri as $passeggero){
									if(!$removeDiscesa || ($removeDiscesa && !array_key_exists($passeggero, $removeDiscesaP))){
									$r = $p->getPrenotazioneDettaglio($passeggero, $_POST['lineaId'], $_POST['corsaId'],$_POST['dataPartenza']);
									?>
									<tr>
										<td>
											<?=utf8_decode($r['Nome']." ".$r['Cognome'])?>	
										</td>
                                        <td>
                                        	<a href="tel:<?=$r['ClienteCellulare']?>"><?=$r['ClienteCellulare']?></a>
										</td>
										<td>
											<?=$r['ComunePartenza']?>						
										</td>
										<td>
											<?=$r['ComuneArrivo']?>						
										</td>
										<?php if(Config::$opzioneCongiunti) { ?>
											<td>                                                      
												<?php if($r['Congiunti'] == 1) { ?>
													Si - <?=$r['CodicePrenotazione']?>
												<?php } else { ?>
													No
												<?php } ?>
											</td>
										<?php } ?>
									</tr>
									<?php
									}
								}			
							} 
							?>
						</tbody>
				</table>
					<!-- FINE --> 
				<br />
			</div>
		</div>
	<?php 	
	
}

function CaricoBus(){
	global $user, $HtmlCommon, $db;

	$db= new Database();
	$db->connect();

	$lineaId = $_POST['lineaId'];
	$busId =  $_POST['busId'];
	$corsaId = $_POST['corsaId'];
	$title = $_POST['title'];
	$dataPartenza = $_POST['dataPartenza'];
	
	$HtmlCommon->html_titolo_pagina("$title / Carico Passegeri",0,"","");
	$HtmlCommon->html_titolo_box ("$title / Carico Passegeri");
	
	$sql = "SELECT * FROM RT_MobileCaricoPasseggeri where BusId = $busId AND Caricato = 1";
	$ArrCarico = $db->fetch_array($sql);
	$sql = "SELECT * FROM RT_MobileCaricoPasseggeri where BusId = $busId AND (Caricato = 0 || Caricato = 2)";
	$ArrAssenti = $db->fetch_array($sql);
	?>
		
		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
			<div class="brain_formModifica formGestoreEdita">
				<h1>Biglietti Validati</h1>
				<table class="table"
						id="gestoreElencoAule">
						<tbody>
							<tr class="rowIntestazione">
								<td>Num. Itin.</td>
								<td>Passeggero</td>
								<td>Da</td>
								<td>A</td>
							</tr>
							<?php foreach($ArrCarico as $c) {?>
								<tr>
									<td><?php echo $c['CodicePrenotazione']?></td>
									<td><?php echo $c['Cliente']?></td>
									<td><?php echo $c['ComunePartenza']?></td>
									<td><?php echo $c['ComuneArrivo']?></td>
								</tr>
							<?php } ?>
							
						</tbody>
				</table>
				<br>
				<h1>Assenti</h1>
				<table class="table"
						id="gestoreElencoAule">
						<tbody>
							<tr class="rowIntestazione">
								<td>Num. Itin.</td>
								<td>Passeggero</td>
								<td>Da</td>
								<td>A</td>
							</tr>
							<?php foreach($ArrAssenti as $c) {?>
								<tr>
									<td><?php echo $c['CodicePrenotazione']?></td>
									<td><?php echo $c['Cliente']?></td>
									<td><?php echo $c['ComunePartenza']?></td>
									<td><?php echo $c['ComuneArrivo']?></td>
								</tr>
							<?php } ?>
							
						</tbody>
				</table>

					<!-- FINE --> 
				<br />
			</div>
		</div>
	<?php 	
	
}

function DettaglioPasseggeri() {
	$biglietti = $_POST['bigliettiId'];
	$intestazione = $_POST['intestazione'];
	
	global $HtmlCommon,$user,$db;

	$db = new Database();
	$db->connect();

	$HtmlCommon->html_titolo_pagina("Dettaglio del percorso: ".$intestazione,0,"","");
	$HtmlCommon->html_titolo_box ("Dettaglio del percorso: ".$intestazione);

	$p = new PrenotazioneDettaglio();
	$p->conn = $db;
	?>
	
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	
		<div class="brain_formModifica formGestoreEdita">

			<br />
			
			<table width="100%" cellspacing="0" cellpadding="0" border="0"
					id="gestoreElencoAule">
					<tbody>
						<tr class="rowIntestazione">
							<td>Passeggero</td>
							<td>Tipologia Biglietto</td>
							<td>Comune Partenza</td>
							<td>Comune Destinazione</td>
						</tr>
						<?php
						foreach ($biglietti as $key => $value){
							$r = $p->getPrenotazioneDettaglio($value, $_POST['lineaId'], $_POST['corsaId'],$_POST['dataPartenza']);
							?>
							<tr class="rowBianca">
								<td><span>
									<?=$r['Nome']." ".$r['Cognome']?>
								</span></td>
								<td><span>
									<?=$r['TipologiaBiglietto']?>
								</span></td>
								<td><span>
									<?=$r['ComunePartenza']?>
								</span></td>
								<td><span>
									<?=$r['ComuneArrivo']?>
								</span></td>
							</tr>
						<?php
						}
						?>
					</tbody>
			</table>
					<!-- FINE --> 
			<br />
		</div>
	</div>
	<?php 
}


function DettaglioPasseggeroViaggio(){
	global $user, $HtmlCommon, $db;

	$passeggero = $_POST['passeggero'];
	$lineaId = $_POST['lineaId'];
	$corsaId = $_POST['corsaId'];
	$dataPartenza = $_POST['dataPartenza'];

	$prenotazioneDettaglio = new PrenotazioneDettaglio();
	$prenotazioneDettaglio->conn = $db;
	$comune = $prenotazioneDettaglio->getInfo($passeggero, $_POST['lineaId'], $_POST['corsaId'], $_POST['dataPartenza']);

	$gestionePasseggeri = new GestioneOttimizzataPasseggeri($db);
	$percorso = $gestionePasseggeri->getPercorsoPasseggeri($lineaId, $corsaId, $dataPartenza, $passeggero);

	$HtmlCommon->html_titolo_pagina("Dettaglio Viaggio ".$comune['Nome']." ".$comune['Cognome'],0,"","");
	$HtmlCommon->html_titolo_box ("Dettaglio Viaggio ".$comune['Nome']." ".$comune['Cognome']);

	?>
 	
 	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	
		<div class="brain_formModifica formGestoreEdita">

			<br />
			<table width="100%" cellspacing="0" cellpadding="0" border="0"
					id="gestoreElencoAule">
					<tbody>
						<tr class="rowIntestazione">
							<td>Tipologia Biglietto</td>
							<td>Comune Salita</td>
							<td>Comune Discesa</td>
						</tr>
						<tr>
							<td><span>
								<?=$comune['TipologiaBiglietto']?>
							</span></td>
							<td><span>
								<?=$comune['ComunePartenza']?>
							</span></td>
							<td><span>
								<?=$comune['ComuneArrivo']?>
							</span></td>
						</tr>
 					</tbody>
			</table>
					<!-- FINE --> 
			<br/>
			<br/>
			<table width="100%" cellspacing="0" cellpadding="0" border="0"
					id="gestoreElencoAule">
					<tbody>
						<tr class="rowIntestazione">
							<td>Comuni di transito</td>
							<td>Autobus</td>
						</tr>
						
						<?php
						$fermata = new Fermata();
						$fermata->conn = $db;
						foreach ($percorso as $key=>$value){
						?>
							<tr>		
								<td><span>
									<?php
									$comuneObj = new Comune($value['Comune']);
									$comuneObj->conn = $db;
									$comuneObj->inizializzaDatiGenerali();
									echo $comuneObj->Comune;
									?>
								</span></td>
								<td><span>
									<?=$value['Nome']?>
								</span></td>
							</tr>
						<?php 
						} 
						?>
 					</tbody>
			</table>		
					
			<br />
		</div>
	</div>
	
	
 	<?php 
	$db->close();
}

function EmettiTitoliViaggio()
{
global $user,$db;
$storico=new StoricoOperazioni();
$storico->conn=$db;

$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['CorsaData'];

// seleziono tutti gli id prenotazione per cui non è stato emesso un titolo
// chiamo la funzione di emissione titoli

$sql="select * from  RT_ViewElencoPrenotazioneDaConfermare where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza'";

$ArrObject = $db->fetch_array($sql);
$n_prenotazioni=sizeof($ArrObject); 

$nt=0;
//while ($nt<$numerotratte)
while ($nt<$n_prenotazioni)
{
    $PrenotazioneId=$ArrObject[$nt]['PrenotazioneId'];
      $NumeroTotalePax=$ArrObject[$nt]['TotalePostiPrenotati'];
     $sql="select * from  RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and CorsaId<>$CorsaId and  Stato=1 and Cancella=0";
    
    $ArrObject1 = $db->fetch_array($sql);
    $n_corse=sizeof($ArrObject1); 
    $nt1=0;
    $CorsaRitorno=0;
    if ($n_corse==1)
         $CorsaRitorno=$ArrObject1[0]['CorsaId'];

         $prenotazione = new Prenotazione($PrenotazioneId);
         $prenotazione->conn=$db;
         
         
          $x=$prenotazione->EmettiBiglietti($NumeroTotalePax,$CorsaId,$CorsaRitorno);

    
   $nt++;
}
    echo("ok");
    exit();
    
}


function ConsolidaCorsa()
{
global $user,$db;
$storico=new StoricoOperazioni();
$storico->conn=$db;

$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['CorsaData'];

$d1['DataCorsa']=$DataPartenza;
$d1['CorsaId']=$CorsaId;


$d1=$storico->operazioni_insert($d1, $user);
$lastidA=$db->insert("RT_CorsaConsolidamento", $d1);

if ($lastidA>0)
    echo("ok");
else 
    echo("no");

    exit();
    
}

function InizializzaCorsa()
{
global $user,$db;
$storico=new StoricoOperazioni();
$storico->conn=$db;

$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['CorsaData'];

$d1['DataCorsa']=$DataPartenza;
$d1['CorsaId']=$CorsaId;


$d1=$storico->operazioni_insert($d1, $user);
$db->delete("RT_CorsaInizioPreparazione", "CorsaId=$CorsaId and DataCorsa='$DataPartenza' and OdcIdRef=$user->OdcId");
$lastidA=$db->insert("RT_CorsaInizioPreparazione", $d1);

if ($lastidA>0)
    echo("ok");
else 
    echo("no");

    exit();
    
}
function PreparaNavette()
{

global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$PrenotazioneTrattaNavetta=$_REQUEST['Prenotazione'];
$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];
$arr_ptn=  explode("_", $PrenotazioneTrattaNavetta);

$PrenotazioneId=$arr_ptn[1];
$NavettaNumero=$arr_ptn[3];
$TrattaId=$arr_ptn[4];

$_SESSION['PREPARAZIONE_NAVETTE'][$PrenotazioneId."_".$TrattaId]=$TrattaId."_".$NavettaNumero;


$lastidA=$db->delete("RT_PreparazioneNavette","CorsaId=$CorsaId and DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId");
$data=$_SESSION['PREPARAZIONE_NAVETTE'];


 foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     
     
     $arr_chiave=explode('_', $valore);
     $arr_chiave1=explode('_', $chiave);
     $TrattaId=$arr_chiave[0];
     $NavettaNumero=$arr_chiave[1];
     $PrenotazioneId=$arr_chiave1[0];
     
    
     $d1['PrenotazioneId']=$PrenotazioneId;
     $d1['TrattaId']=$TrattaId;
     $d1['NumeroNavetta']=$NavettaNumero;
     $d1['CorsaId']=$CorsaId;
     $d1['DataPartenza']=$DataPartenza;
     
     
     
     if ($PrenotazioneId>0)
         { 
             $d1=$storico->operazioni_insert($d1,$user);
             $lastidA=$db->insert("RT_PreparazioneNavette", $d1);
      }
 }

       
// delete where prenotazione id
}


function PreparaBus()
{

global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$PrenotazioneTrattaNavetta=$_REQUEST['Prenotazione'];
$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];
$arr_ptn=  explode("_", $PrenotazioneTrattaNavetta);

$PrenotazioneId=$arr_ptn[1];
$PullmanNumero=$arr_ptn[3];
$BusId=$arr_ptn[4];

// seleziona posti occupati in base a corsa, data, vus

$sql="Select TotalePaxPrenotati,OdcIdRef from RT_Prenotazione where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId";

$row1 = $db->query_first($sql);
$PostiTemp=0;

if (!empty($row1['OdcIdRef']))
   $PostiTemp=$row1['TotalePaxPrenotati'];



$row1 = $db->query_first($sql);


if (!empty($row1['OdcIdRef']))
   $PostiGestiti+=$row1['TotalePosti'];

$sql="Select * from RT_FoglioCaricoTotalePaxPerPullman where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and BusId=$BusId and BusNumero=$PullmanNumero and OdcIdRef=$user->OdcId";


$row1 = $db->query_first($sql);
$PostiGestiti=0;

if (!empty($row1['OdcIdRef']))
   $PostiGestiti=$row1['TotalePosti'];

$PostiGestiti=$PostiGestiti+$PostiTemp;

    $tb=new TipologiaBus($BusId);
    $tb->conn=$db;
    $tb->inizializzaDatiGenerali();
    $arr_tb=$tb->DatiGenerali;
    
    $PostiDisponibili=$arr_tb['TotalePosti'];
    
if ($PostiDisponibili<$PostiGestiti)
    echo("Impossibile posizionare i clienti sul bus selezionato. L'autobus non può contenere ".$PostiGestiti." viaggiatori");
else
{


$_SESSION['PREPARAZIONE_BUS'][$PrenotazioneId]=$BusId."_".$PullmanNumero;


  
$data=$_SESSION['PREPARAZIONE_BUS'];


 foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     
     
     $arr_chiave=explode('_', $valore);
     $BusId=$arr_chiave[0];
     $BusNumero=$arr_chiave[1];
     $PrenotazioneId=$chiave;
     
    
     $lastidA1=$db->delete("RT_PreparazioneBus","CorsaId=$CorsaId and PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId");

     
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
 echo("ok");
//echo($PostiGestiti);
}   
// delete where prenotazione id
}



function RimuoviPrenotazioneBus()
{

global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$Prenotazione=$_REQUEST['Prenotazione'];
$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];

$arr_ptn=  explode("_", $Prenotazione);

$PrenotazioneId=$arr_ptn[1];
echo($PrenotazioneId);

$db->delete("RT_PreparazioneBus","PrenotazioneId=$PrenotazioneId and CorsaId=$CorsaId and OdcIdRef=$user->OdcId");


$_SESSION['PREPARAZIONE_BUS'][$PrenotazioneId]=null;
unset($_SESSION['PREPARAZIONE_BUS'][$PrenotazioneId]);
$data=$_SESSION['PREPARAZIONE_BUS'];
echo("ok");

// delete where prenotazione id
}

function RimuoviBus(){
	global $user,$db;
	
	$BusId=$_REQUEST['BusId'];
	$CorsaId=$_REQUEST['CorsaId'];
	$DataPartenza=$_REQUEST['DataPartenza'];
	$LineaId=$_REQUEST['LineaId'];
	
	$gestionePasseggeri = new GestioneOttimizzataPasseggeri($db);
	$passeggeri = $gestionePasseggeri->getPasseggeriBus($LineaId, $CorsaId, $DataPartenza, $BusId);
	$prenotazioneDettaglio = new PrenotazioneDettaglio();
	$prenotazioneDettaglio->conn = $db;
	foreach ($passeggeri as $passeggero){
		$operazione['LineaId'] = $LineaId;
		$operazione['CorsaId'] = $CorsaId;
		$operazione['CorsaDataPartenza'] = $DataPartenza;
		$operazione['PrenotazioneNumero'] = $passeggero['PrenotazioneNumero'];
		$operazione['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
		$operazione['Aggiungi']=2;
		$db->insert('RT_GestioneOttimizzataModifiche',$operazione);
		$del=$db->delete("RT_GestioneOttimizzataPasseggeri", "PrenotazioneNumero=".$passeggero['PrenotazioneNumero']);
	}
	$del=$db->delete("RT_GestioneOttimizzataFlotta", "GestioneOttimizzataFlottaId=$BusId");
	$del=$db->delete("RT_GestioneOttimizzataNodo", "BusArrivo=$BusId OR BusPartenza=$BusId");
	echo("ok");
	
	// delete where prenotazione id
}


function RimuoviNavetta()
{
global $user,$db;

$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$TrattaId=$_REQUEST['TrattaId'];
$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];
$NumeroNavetta=$_REQUEST['NumeroNavetta'];

$arr_ptn=  explode("_", $Prenotazione);

$PrenotazioneId=$arr_ptn[1];
echo($PrenotazioneId);

$db->delete("RT_PreparazioneNavetta","TrattaId=$BusId and NumeroNavetta=$NumeroNavetta and CorsaId=$CorsaId and DataPartenza='$DataPartenza' and OdcIdRef=$user->OdcId");

echo("ok");

// delete where prenotazione id
}

function RimuoviPrenotazioneNavetta()
{

global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$Prenotazione=$_REQUEST['Prenotazione'];
$TrattaNavetta=$_REQUEST['TrattaNavetta'];
$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];

$arr_ptn=  explode("_", $Prenotazione);
$arr_tn=  explode("_", $TrattaNavetta);

$PrenotazioneId=$arr_ptn[1];
$trattaid=$arr_tn[2];


$db->delete("RT_PreparazioneNavette","PrenotazioneId=$PrenotazioneId and TrattaId=$trattaid and OdcIdRef=$user->OdcId");


$_SESSION['PREPARAZIONE_NAVETTE'][$PrenotazioneId."_".$trattaid]=null;
unset($_SESSION['PREPARAZIONE_NAVETTE'][$PrenotazioneId."_".$trattaid]);
$data=$_SESSION['PREPARAZIONE_NAVETTE'];
echo("ok");

// delete where prenotazione id
}


function DisponiPosti()
{

global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$PrenotazionePosto=$_REQUEST['PrenotazionePosto'];
$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];
$arr_ptn=  explode("_", $PrenotazionePosto);

$PrenotazionePostoId=$arr_ptn[1];

$nriga=$arr_ptn[3];
$ncolonna=$arr_ptn[4];
$npiano=$arr_ptn[5];
  

     
    
     $d1['Riga']=$nriga;
     $d1['Colonna']=$ncolonna;
  //   $d1['Posto']=$nposto;
     $d1['Piano']=$npiano;
     $d1=$storico->operazioni_update($d1, $user);
     print($PrenotazionePostoId);
     
     $db->update("RT_PrenotazionePosto",$d1,"OdcIdRef=$user->OdcId and PrenotazioneNumeroId=$PrenotazionePostoId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'");
     
     
     
     

       
// delete where prenotazione id
}


function create_dettaglio_autobus()
{
global $user,$db;
$storico=new StoricoOperazioni();
$storico->conn=$db;
$dt=new DT();
$CorsaId=$_POST['CorsaId'];
$DataPartenza=$_POST['DataPartenza'];
$data=$_POST['DettaglioPullman'];
$d1=null;
foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     $arr_chiave=explode('_', $chiave);
     
     
     $campo=$arr_chiave[0];
     $BusId=$arr_chiave[1];
     $BusNumero=$arr_chiave[2];
     $ValoreCampo=$valore;
     
    
     $d1[$campo]=$ValoreCampo;
     $d1['BusId']=$BusId;
     $d1['BusNumero']=$BusNumero;
     $d1['CorsaId']=$CorsaId;
     $d1['DataPartenza']=$DataPartenza;
     $d1=$storico->operazioni_insert($d1, $user);
     
     print_r($d1);
     
     
    $sq="select * from RT_PreparazioneBusAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$BusNumero";
    echo($sq);
    $r=$db->query_first($sq);
    if ($r['BusId']>0)
    {
        $du[$campo]=$ValoreCampo;
        $db->update("RT_PreparazioneBusAutisti",$du,"DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and BusId=$BusId and BusNumero=$BusNumero");
    }
 else {
        
        $db->insert("RT_PreparazioneBusAutisti",$d1);
     
    }
     
     
     
 }
    
    
}


function create_dettaglio_navetta()
{
global $user,$db;
$storico=new StoricoOperazioni();
$storico->conn=$db;
$dt=new DT();
$CorsaId=$_POST['CorsaId'];
$DataPartenza=$_POST['DataPartenza'];
$data=$_POST['DettaglioNavetta'];
$d1=null;
foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     $arr_chiave=explode('_', $chiave);
     
     
     $campo=$arr_chiave[0];
     $TrattaId=$arr_chiave[1];
     $NavettaNumero=$arr_chiave[2];
     $ValoreCampo=$valore;
     
    
     $d1[$campo]=$ValoreCampo;
     $d1['TrattaId']=$TrattaId;
     $d1['NavettaNumero']=$NavettaNumero;
     $d1['CorsaId']=$CorsaId;
     $d1['DataPartenza']=$DataPartenza;
     $d1=$storico->operazioni_insert($d1, $user);
     
     
     
     
    $sq="select * from RT_PreparazioneNavetteAutisti where DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and TrattaId=$TrattaId and NavettaNumero=$NavettaNumero";
   // echo($sq);
    $r=$db->query_first($sq);
    if ($r['TrattaId']>0)
    {
        $du[$campo]=$ValoreCampo;
        $db->update("RT_PreparazioneNavetteAutisti",$du,"DataPartenza='$DataPartenza' and CorsaId=$CorsaId and OdcIdRef=$user->OdcId and TrattaId=$TrattaId and NavettaNumero=$NavettaNumero");
    }
 else {
        
        $db->insert("RT_PreparazioneNavetteAutisti",$d1);
     
    }
     
     
     
 }
    
    
}

if ($_REQUEST['bypass']=='true')
{
       DettaglioPasseggeriBus();
       exit();
} else if ($_REQUEST['bypass']=='trueCarico') {
	CaricoBus();
}
    

// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}

?>
