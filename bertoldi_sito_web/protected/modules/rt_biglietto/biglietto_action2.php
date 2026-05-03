<?php

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include_app.php");
$config = new Config();
$run = $config->load();

$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "/class.Sede.php");
include_once($classespath_ . "/class.Fermata.php");
include_once($classespath_ . "/class.Tratta.php");
include_once($classespath_ . "/class.Prenotazione.php");
include_once($classespath_ . "/class.Corsa.php");
include_once($classespath_ . "/class.Linea.php");
include_once($classespath_ . "/class.Percorso.php");
include_once($classespath_ . "/class.Orario.php");
include_once($classespath_ . "/class.Listino.php");
include_once($classespath_ . "/class.TipologiaBus.php");
include_once($classespath_ . "/class.UtenteWeb.php");
include_once($classespath_ . "/class.DT.php");
include_once($classespath_ . "Graph/class.DisponibilitaGraph.php");
include_once($classespath_ . "Graph/class.LineaGraph.php");
include_once($classespath_ . "Graph/class.GrafoTratte.php");
//include_once($classespath_ . "Graph/class.GraphUtil.php");

use Stripe\Stripe;

$ModuloId = 2; 

global $prenotazione_wizard;
$funzione_edit = false;
$prenotazione_wizard = null;

if (isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
	$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
}


function getCorseOpt($ComunePartenzaId, $ComuneDestinazioneId, $Tp, $DataFiltroA, $DataFiltroR, $Corsaid, $DataCorsa, $TipoTour, $LineaId)
{
	$_REQUEST['ComunePartenzaId'] = $ComunePartenzaId;
	$_REQUEST['ComuneDestinazioneId'] = $ComuneDestinazioneId;
	$_REQUEST['Tp'] = $Tp;
	$_REQUEST['DataFiltroA'] = $DataFiltroA;
	$_REQUEST['DataFiltroR'] = $DataFiltroR;
	$_REQUEST['Corsaid'] = $Corsaid;
	$_REQUEST['DataCorsa'] = $DataCorsa;
	$_REQUEST['TipoTour'] = $TipoTour;

	global $db, $prenotazione_wizard, $user;
	
	$user = new UtenteWeb();
	
	/**recupero dati**/
	if (isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
		$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
	}
	//data andata
	$DataFiltroA = $_REQUEST['DataFiltroA'];
	//data ritorno
	$DataFiltroR = $_REQUEST['DataFiltroR'];
	//tipo viaggio A=andata, R=ritorno
	$Tp = $_REQUEST['Tp'];
	//corsaId selezionata in caso di modifica
	$CorsaId_post = $_REQUEST['Corsaid'];
	//dataCorsa selezionata in caso di modifica
	$DataCorsa_post = $_REQUEST['DataCorsa'];
	//tipo tour 0=gruppo, 1=privato
	$TipoTour = $_REQUEST['TipoTour'];
	
	/*recupero dati se sto modificando/visualizzando una prenotazione*/
	$ComuneSalitaId_post = 0;
	$ComuneDiscesaId_post = 0;
	$CorsaPrenotata_post = 0;
	$DataPrenotata_post = null;
	if (is_object(($prenotazione_wizard))) {

		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso($Tp);
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
		$ComuneSalitaId_post = $DatiGeneraliPercorsoArr['ComuneSalitaId'];
		$ComuneDiscesaId_post = $DatiGeneraliPercorsoArr['ComuneDiscesaId'];
		$CorsaPrenotata_post = $DatiGeneraliPercorsoArr['CorsaId'];
		$DataPrenotata_post = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
		$LineaId_post = $DatiGeneraliPercorsoArr['LineaId'];
	}


	//recupero comuni da form
	if ($_REQUEST['ComunePartenzaId'] > 0) {
		$ComunePartenzaId = $_REQUEST['ComunePartenzaId'];
	}
	if ($_REQUEST['ComuneDestinazioneId'] > 0) {
		$ComuneDestinazioneId = $_REQUEST['ComuneDestinazioneId'];
	}
	if ($Tp == 'R') {
		$ComuneDestinazioneId = $_REQUEST['ComunePartenzaId'];
		$ComunePartenzaId = $_REQUEST['ComuneDestinazioneId'];
	}
	//calcolo Data Inizio ricerca
	if ($Tp == 'A') {
		$dataInizio = $DataFiltroA;
	} else {
		$dataInizio = $DataFiltroR;
	}
	$pieces = explode("/", $dataInizio);
	$from_unix_time = mktime(0, 0, 0, $pieces[1], $pieces[0], $pieces[2]);
	$day_before = strtotime("-1 day", $from_unix_time);
	$day_before_formatted = date('Y-m-d', $day_before);
	$dateRicerca[] = $day_before_formatted;

	$day = strtotime("+0 day", $from_unix_time);
	$dateRicerca[] = date('Y-m-d', $day);

	$day_after = strtotime("+1 day", $from_unix_time);
	$day_after_formatted = date('Y-m-d', $day_after);
	$dateRicerca[] = $day_after_formatted;

	$aColumns = array('CorsaId', 'CorsaNome', 'LineaNome', 'DataPartenzaFormattata', 'AppSettimanaGiornoDescr', 'OrarioPartenza', 'AppCalendarioData', 'LineaId', 'PostiRealmenteDisponibili');
	/*applicazione delle regole di filtraggio e ricerca*/
	//ordine
	$sOrder = "";
	if (isset($_GET['iSortCol_0'])) {
		for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
			if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
				$sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
			}
		}
		$sOrder = substr_replace($sOrder, "", -2);
		if ($sOrder == "ORDER BY") {
			$sOrder = "";
		}
	}
	// Aggiungi ordinamento per numero trasferimenti (decrescente) in testa
	$orderTrasferimenti = "(SELECT COUNT(*) FROM RT_CorsaTrasferimento ct WHERE DataPartenza_2 = RT_AppCalendario.AppCalendarioData AND CorsaId_2 = RT_Corsa.CorsaId) DESC";
	if ($sOrder) {
		$sOrder = $orderTrasferimenti . ", " . $sOrder;
	} else {
		$sOrder = $orderTrasferimenti;
	}
	
	$sWhere = "RT_Corsa.Stato = 1 AND RT_Linea.TipoTour = $TipoTour AND RT_Linea.LineaId = $LineaId ";
	//filtraggio
	for ($i = 0; $i < count($aColumns); $i++) {
		$j = $i;
		if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
			if ($sWhere == "") {
				$sWhere = " ";
			} else {
				$sWhere .= " AND ";
			}

			$sWhere .= $aColumns[$j] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
		}
	}

	$flag_stop_vendite = 1;
	$sede = new Sede();
	$sede->conn = $db;

	$sede->inizializza($user->SedeId);
	$flag_stop_vendite = 0;
	if ($flag_stop_vendite == 0)
		$sWhere .= " AND (TIMEDIFF(ADDTIME(
				CONCAT(RT_AppCalendario.AppCalendarioData,' 00:00:00'),
				RT_Corsa.OrarioPartenza
			),NOW())) > `RT_Corsa`.OrePrimaStopVendita ";

	//recupero delle corse per data
	$sEcho = null;
	if(isset($_GET['sEcho'])){
		$sEcho = intval($_GET['sEcho']);
	}
	$output = array(
		"sEcho" => $sEcho,
		"iTotalRecords" => 0,
		"iTotalDisplayRecords" => 0,
		"aaData" => array()
	);

	$corsaObj = new Corsa();
	$corsaObj->conn = $db;
	$count = 0;

	foreach ($dateRicerca as $data) {
		$count++;
		$search = true;
		$ii = 1;
		while ($search) {
			$ii++;
			if ($ii > 3) {
				break;
			}

			$corse = $corsaObj->getCorseValideBO($data, $sOrder, $sWhere);
			if (count($corse) <= 0 && $data != $dateRicerca[1]) {
				$pieces = explode("-", $data);

				$from_unix_time = mktime(0, 0, 0, $pieces[1], $pieces[2], $pieces[0]);
				switch ($count) {
					case 1:
						$new_day = strtotime("-1 day", $from_unix_time);
						break;
					case 3:
						$new_day = strtotime("+1 day", $from_unix_time);
				}
				$data = date('Y-m-d', $new_day);
			} else if (count($corse) <= 0 && $data == $dateRicerca[1]) {
				$search = false;
			} else {
				$search = false;
				foreach ($corse as $key => $corsa) {

					/*verifica se deve visualizzare*/
					$visualizza = false;
					$cId = $corsa['CorsaId'];
					$lId = $corsa['LineaId'];
					$dId = $corsa['AppCalendarioData'];
					//                     echo "corsaID: ".$corsa['CorsaId']."<br>";                    

					$sql = "SELECT * FROM RT_CorsaBlocco WHERE CorsaId = $cId AND DataPartenza = '$dId'";
					$tempRow = $db->fetch_array($sql);
					if (count($tempRow) > 0) {
						//corsa bloccata
						$visualizza = false;
						// 						echo "corsa bloccata<br>";
						if (($cId == $CorsaPrenotata_post)  and ($ComunePartenzaId == $ComuneSalitaId_post)  and ($dId == $DataPrenotata_post) and ($ComuneDestinazioneId == $ComuneDiscesaId_post)) {
							// 							echo "corsa selezionata<br>";
							//caso in cui si � una corsa gi� selezionata nella prenotazine la visualizzo direttamente
							$visualizza = true;
							//controllo presenza della corsa nella tabella percorso breve
							$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, 0);
							$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
							$pre = new Prenotazione();
							$pre->conn = $db;
							$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaStato=1 order by TrattaPeso desc ";
							$arr_fermate = $db->fetch_array($sql);
							if (is_array($arr_fermate) && count($arr_fermate) > 0) {
							    $trattaPartenza = $arr_fermate[0]['TrattaId'];
							} else {
							    $trattaPartenza = null;
							}
							$sql = "SELECT distinct FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId  and TrattaStato=1 order by TrattaPeso asc";
							$arr_fermate_d = $db->fetch_array($sql);
							if (is_array($arr_fermate_d) && count($arr_fermate_d) > 0) {
							    $trattaArrivo = $arr_fermate_d[0]['TrattaId'];
							} else {
							    $trattaArrivo = null;
							}
						}
					} else {
						// 						echo "non bloccata<br>";
						// 						//corsa non bloccata
						if (($cId == $CorsaPrenotata_post)  and ($ComunePartenzaId == $ComuneSalitaId_post)  and ($dId == $DataPrenotata_post) and ($ComuneDestinazioneId == $ComuneDiscesaId_post)) {
							// 							echo "corsa selezionata<br>";
							//caso in cui si � una corsa gi� selezionata nella prenotazine la visualizzo direttamente
							$visualizza = true;
							//controllo presenza della corsa nella tabella percorso breve
							$trattaPartenza = null;
							$trattaArrivo = null;
							$sql = "select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$CorsaPrenotata_post";
							$r = $db->query_first($sql);
							if (!empty($r['PercorsoBreveId'])) {
								// 								echo "lettura tabella<br>";
								if (is_array($arr_fermate) && count($arr_fermate) > 0) {
									$trattaPartenza = $arr_fermate[0]['TrattaId'];
								} else {
									$trattaPartenza = null;
								}
									$trattaArrivo = $r['TrattaDropOffId'];
									$visualizza = true;
								if (is_array($arr_fermate_d) && count($arr_fermate_d) > 0) {
									$trattaArrivo = $arr_fermate_d[0]['TrattaId'];
								} else {
									$trattaArrivo = null;
								}
								// 								echo "lettura grafo<br>";
								$grafo = new GrafoTratte($LineaId_post, $CorsaPrenotata_post, $db, $ComunePartenzaId, $ComuneDestinazioneId);
								$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
								$pre = new Prenotazione();
								$pre->conn = $db;
								$ritorno = $pre->CreatePercorsoBreve($ComunePartenzaId, $ComuneDestinazioneId, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $CorsaPrenotata_post, $LineaId_post);
								unset($grafo);
								unset($TrattePercorse);
								unset($pre);
								unset($ritorno);
							}
						} else {
							// 							echo "corsa non selezionata<br>";
							// caso in cui � una corsa non selezionata
							$trattaPartenza = null;
							$trattaArrivo = null;
							//controllo se le fermate sono attive
							$sql = "select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$cId";
							$r = $db->query_first($sql);
							if (!empty($r['PercorsoBreveId'])) {
								// 								echo "lettura tabella<br>";
								$trattaPartenza = $r['TrattaPickupId'];
								$trattaArrivo = $r['TrattaDropOffId'];
								$visualizza = true;
							} else {
								// 								echo "<br><br>lettura grafo<br>";
								$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId);
								//if (isset($grafo->flotta[0])) {
								//	$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
								//} else {
								//	$TrattePercorse = null;
								//}
								//$pre = new Prenotazione();
								//$pre->conn = $db;
								$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaStato=1 order by TrattaPeso desc ";
								$arr_fermate = $db->fetch_array($sql);
								if (isset($arr_fermate[0])) {
									$trattaPartenza = $arr_fermate[0]['TrattaId'];
								} else {
									$trattaPartenza = null;
								}
								$sql = "SELECT distinct FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId  and TrattaStato=1 order by TrattaPeso asc";
								$arr_fermate_d = $db->fetch_array($sql);
								if (isset($arr_fermate_d[0])) {
									$trattaArrivo = $arr_fermate_d[0]['TrattaId'];
								} else {
									$trattaArrivo = null;
								}
								//$ritorno = $pre->CreatePercorsoBreve($ComunePartenzaId, $ComuneDestinazioneId, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $cId, $lId);
								unset($grafo);
								//unset($TrattePercorse);
								//unset($pre);
								//unset($ritorno);
							}

							if (isset($trattaPartenza) && isset($trattaArrivo)) {
								// 								echo "tratta esistente<br>";
								$sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaStato=1 order by TrattaPeso desc ";
								// 								echo "fermata pk ".$sql."<br>";
								$arr_fermate = $db->fetch_array($sql);
								if (isset($arr_fermate[0])) {
									$trattaPartenza = $arr_fermate[0]['TrattaId'];
								} else {
									$trattaPartenza = null;
								}
								$sql = "SELECT distinct FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId  and TrattaStato=1 order by TrattaPeso asc";
								// 								echo "fermata dp ".$sql."<br>";
								$arr_fermate_d = $db->fetch_array($sql);
								if (isset($arr_fermate_d[0])) {
									$trattaArrivo = $arr_fermate_d[0]['TrattaId'];
								} else {
									$trattaArrivo = null;
								}
								if ((sizeof($arr_fermate) > 0) and (sizeof($arr_fermate_d) > 0)) {
									// 									echo "fermata esistente<br>";
									$visualizza = true;
								} else {
									// 									echo "fermata non esistente<br>";
									$visualizza = false;
								}
							} else {
								// 								echo "tratta non esistente<br>";
								$visualizza = false;
							}

							//controllo se la tratta � vendibile
							/*$sql = "SELECT TratteNonVendibiliId from RT_TratteNonVendibili  WHERE ComunePickUpId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId  ";
							$arr_esclusi = $db->fetch_array($sql);
							if ((sizeof($arr_esclusi) > 0)) {
								$visualizza = false;
								//echo "non vendibile<br>";
							}*/

							//controllo convenzione
							$sql = "SELECT * FROM GestoreConvenzione where GestoreId = " . $user->GestoreId . " and LineaId = $lId and Now()<=ValidaAl and Now()>=ValidaDal";
							$arr_convenzione = $db->fetch_array($sql);
							if ((sizeof($arr_convenzione) == 0)) {
								$visualizza = false;
								//                             	echo "non convenzione<br>";
							}

							//controllo se la tariffa e' 0 solo per l'andata
							if ($Tp == 'A') {
								
								$sql = "SELECT * FROM RT_CorsaTariffa 
										where CorsaId = $cId
										AND FermataPickup = $ComunePartenzaId
										AND FermataDropOff = $ComuneDestinazioneId
										AND TipologiaBigliettoId <> 11 
										AND Tariffa > 0";
								$arr_tariffe = $db->fetch_array($sql);
								if ((sizeof($arr_tariffe) == 0)) {
									$visualizza = false;
								}
							}
						}
					}

					/*se la corsa e' visualizzabile l'aggiungo alla lista*/
					
					if ($visualizza) {
						$row = array();
						for ($i = 0; $i < count($aColumns); $i++) {

							if ($aColumns[$i] == "CorsaNome") {
								$row[] = utf8_decode($corsa['CorsaNome']);
							} else if ($aColumns[$i] == "CorsaId") {
								$CorsaId = $corsa['CorsaId'];
								$DataPartenza = $corsa['AppCalendarioData'];

								$dataodierna = Date('Y-m-d');
								$dt = new DT();
								$diff = $dt->compare($dataodierna, $DataPartenza, 'Y-m-d');

								//se la corsa � gi� partita verifico se per l'orario fermata � ancora in tempo
								$diff2 = 0;
								$sql = "SELECT o.Orario, o.GiorniAggiuntivi 
										FROM RT_Orario o
										LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
										where o.CorsaId = $CorsaId and f.ComuneId = $ComunePartenzaId ";
								$rowTemp = $db->query_first($sql);
								$dataPartenzaHM = new DateTime($dataodierna . ' ' . $rowTemp['Orario']);
								$dataPartenzaHM->modify('+' . $rowTemp['GiorniAggiuntivi'] . ' day');
								$dataOggiHM = new DateTime();
								if ($dataOggiHM > $dataPartenzaHM) {
									$diff2 = 1;
								}


								$OrarioPartenza = $corsa['OrarioPartenza'];
								$name_input = "Corsa[$CorsaId.]";
								$arr_field = $Tp . "_" . $CorsaId . "_" . $DataPartenza;
								$arr_field = "";

								/* General output */
								// 								$disponibili=$corsa['PostiCorsaDefault']+$corsa['PostiCorsaAggiunti']-$corsa['PostiRealmentePrenotati'];
								// 								if (($disponibili>0) or ($CorsaId_post==$CorsaId) or ($user->SedeLegale==1)) {
								$ck = "";
								if (($CorsaId_post == $CorsaId) and ($DataCorsa_post == $DataPartenza))
									$ck = "checked";
								if ($Tp == 'A') {
									$mod = "$('#modificaData').val(1);";
								} else {
									$mod = "$('#modificaDataRitorno').val(1);";
								}
								$row[] = $CorsaId;
								$row[] = "<input id=\"a$CorsaId$DataPartenza\" data-id=\"$CorsaId\" $ck type=\"radio\" name=\"Corsa$Tp\" onclick=\"javascript:ControllaDataPassata('$diff','$diff2');$mod$('#CorsaSelezionata$Tp').val($CorsaId);$('#DataSelezionata$Tp').val('$DataPartenza');MostraFermate();\" />";
								// 								} else {
								// 									$row[] = '';
								// 								}

								// array_push($CorsaId, $row);
							} else if ($aColumns[$i] == 'PostiRealmenteDisponibili') {

								$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, true);
								$string = '';
								$f = new Fermata();
								$f->conn = $db;
								$first = true;
								foreach ($grafo->flotta as $flotta) {

									foreach ($flotta->comuni as $c => $comune) {
										if (!$f->isInterscambioLinea($lId, $comune['comune'])) {
											$sql = "SELECT * FROM RT_Orario o
													LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
													where o.CorsaId = $cId
													and f.ComuneId = " . $comune['comune'] . "
													and o.Stato = 1 and o.Cancella = 0
													and f.Stato = 1 and f.Cancella = 0 and o.Orario IS NOT NULL;";
											$checkOra = $db->fetch_array($sql);
											if (count($checkOra) > 0) {
												if ($first) {
													$string .= $comune['comune'];
													$first = false;
												} else {
													$string .= ',' . $comune['comune'];
												}
											}
										}
									}
								}
								if ($string == '') {
									$tempR['Posti'] = 0;
								} else {
									$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron 
	                                where CorsaId = $cId and DataPartenza = '$dId' and Comune IN ($string) ";

									$tempR = $db->query_first($sql);
								}
								
								$sql = "Select b.TotalePosti
									from RT_TipologiaBus b
									left join RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
									where c.CorsaId = $CorsaId";
								$tempR1 = $db->query_first($sql);
								$postiCorsaDefault = $tempR1['TotalePosti'];

								if (isset($tempR['Posti']) && $string != '') {

									$postiOccupati = $tempR['Posti'];

									$sql = "Select TrattaId from RT_DisponibilitaPostiCron 
                                			where CorsaId = $cId and DataPartenza = '$dId' and Posti = $postiOccupati and Comune IN ($string)";
									$tratta =  $db->query_first($sql);

									$sql = "SELECT TipologiaBusDefaultId from RT_Tratta c
											WHERE TrattaId = " . $tratta['TrattaId'];
									$check = $db->query_first($sql);
									if (isset($check['TipologiaBusDefaultId']) && $check['TipologiaBusDefaultId'] > 0) {
										$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
													(select IFNULL((select SUM(c1.NumeroPax)
													from RT_CorsaPaxTratta c1
													where
													c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.TrattaId = " . $tratta['TrattaId'] . " and c1.OdcIdRef = 1
												    group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
												   ) AS `PostiTotali`
												from RT_Tratta c
												join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
												where c.TrattaId = " . $tratta['TrattaId'];
										$tempR1 = $db->query_first($sql);
									} else {
										$sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
												(select IFNULL((select SUM(c1.NumeroPax)
												from RT_CorsaPax c1
												where
												c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.OdcIdRef = 1
												group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0))
												) AS `PostiTotali`
												from RT_Corsa c
												join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
												where c.CorsaId = $cId";
										$tempR1 = $db->query_first($sql);
									}

									$disponibili = intval($tempR1['PostiTotali']) - intval($postiOccupati);
									$occupati = intval($postiOccupati);
								} else {
									//calcolo posti realmente prenotati
									$sql = "select IFNULL((select
									count(0)
									from
									`RT_PrenotazionePercorso`
									join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
									join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
									and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
									and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
									join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
									left join `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
									left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
									where
									((`RT_Prenotazione`.`Cancella` = 0)
									and (`RT_PrenotazionePercorso`.`Cancella` = 0)
									and (`RT_PrenotazionePercorso`.`Stato` = 1)
									and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
									and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
									and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
									and (`tb`.`OccupaPosto` = 1))
									and `RT_PrenotazionePercorso`.`CorsaId` = $CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataPartenza'
									group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0) as PostiRealmentePrenotati";
									$tempR1 = $db->query_first($sql);
									if (isset($tempR1['PostiRealmentePrenotati'])) {
										$postiRealmentePrenotati = $tempR1['PostiRealmentePrenotati'];
									} else {
										$postiRealmentePrenotati = 0;
									}

									$sql = "select IFNULL((select SUM(c1.NumeroPax)
									from RT_CorsaPaxTratta c1
									where
									c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1 and c1.TrattaId = $trattaPartenza
									group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
									$tempR = $db->query_first($sql);
									if (!isset($tempR['PostiAggiunti'])) {
										$sql = "select IFNULL((select SUM(c1.NumeroPax)
										from RT_CorsaPax c1
										where
										c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1
										group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
										$tempR = $db->query_first($sql);
									}
									if (isset($tempR['PostiAggiunti'])) {
										$postiCorsaAggiunti = $tempR['PostiAggiunti'];
									} else {
										$postiCorsaAggiunti = 0;
									}

									

									$disponibili = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
									$occupati = $postiRealmentePrenotati;
								}

								//controllo posti inizio tratta
								$sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
								where CorsaId = $cId and DataPartenza = '$dId' and TrattaId = $trattaPartenza ";
								$tempR = $db->query_first($sql);
								if (isset($tempR['Posti'])) {
									$tempOccupatiInizio = $tempR['Posti'];
								} else {
									$tempOccupatiInizio = 0;
								}
								$sql = "Select ($postiCorsaDefault +
								(select IFNULL((select SUM(c1.NumeroPax)
								from RT_CorsaPaxTratta c1
								where
								c1.Cancella = 0 and c1.CorsaId = $cId and c1.DataPartenza = '$dId' and c1.TrattaId = " . $trattaPartenza . " and c1.OdcIdRef = 1
												    group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
												   ) AS `PostiTotali`
												from RT_Tratta c
												where c.TrattaId = " . $trattaPartenza;
								$tempR1 = $db->query_first($sql);
								$tempInizioTot = $tempR1['PostiTotali'];
								$tempDisponibili = $tempInizioTot - $tempOccupatiInizio;
								if ($tempDisponibili  > 0) {
									if ($tempDisponibili < $disponibili) {
										$row[] = $tempDisponibili;
									} else {
										$row[] = $disponibili;
									}
									$row[] = $occupati;
								} else {
									$row[] = 0;
								}
								
							} elseif ($aColumns[$i] == 'AppSettimanaGiornoDescr') {
								$sql = "SELECT AppSettimanaGiornoDescr FROM RT_AppCalendario c 
										left join RT_AppSettimana s on s.AppSettimanaGiorno = c.GiornoSettimana
										where c.AppCalendarioData = '$DataPartenza'";
								$tempR = $db->query_first($sql);
								$row[] = $tempR['AppSettimanaGiornoDescr'];
							} elseif (($aColumns[$i] != 'PostiRealmenteDisponibili') and ($aColumns[$i] != 'CorsaNome') and ($aColumns[$i] != '') and ($aColumns[$i] != 'PostiRealmentePrenotati') and ($aColumns[$i] != 'AppCalendarioData') and ($aColumns[$i] != 'LineaId')) {
								// 								/* General output */
								$row[] = ($corsa[$aColumns[$i]]);
							}
						}
						$output['aaData'][] = array_decode_list($row);
						// $output['aaData'][] = json_encode($row);

						$output["iTotalRecords"] = $output["iTotalRecords"] + 1;
						$output["iTotalDisplayRecords"] = $output["iTotalDisplayRecords"] + 1;
					}
				}
			}
		}
	}

	return json_encode($output);
}
