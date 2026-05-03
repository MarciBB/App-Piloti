<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author l.casaburi
 */
class Mobile
{


	public $Id;
	public $conn;
	public $DatiGenerali;



	function __construct($Id = null)
	{
		$this->Id = $Id;
	}

	public function inizializzaDatiGenerali()
	{
		global $user;
		$db = $this->conn;
		$Id = $this->Id;
		$sql = "SELECT * From RT_Autisti WHERE Cancella=0 and AutistiId=$Id and OdcIdRef=$user->OdcId order by Cognome, Nome";
		$row = $db->query_first($sql);

		if (!empty($row['OdcIdRef']))
			$this->DatiGenerali = $row;
		else {
			print("errore");
			exit();
		}
	}


	public function getBus($idAutista, $data)
	{
		$db = $this->conn;
		//seleziona gli autobus dell'autosta per la determinata data
		$sql = "SELECT gf.GestioneOttimizzataFlottaId as BusId,
			  gf.LineaId as LineaId,
			  l.LineaNome as LineaNome,
				gf.CorsaId as CorsaId,
				c.CorsaNome as CorsaNome,
				gf.CorsaDataPartenza as CorsaDataPartenza,
				a1.AutistiId as AutistaId1,
				concat(a1.Cognome, ' ', a1.Nome) as Autista1,
				a2.AutistiId as AutistaId2,
				concat(a2.Cognome, ' ', a2.Nome) as Autista2,
				tb.TipologiaBus as TipologiaBus,
				f.Targa as Targa,
				f.Cellulare as Cellulare
			FROM RT_GestioneOttimizzataFlotta gf
			LEFT JOIN RT_PreparazioneBusAutisti pba ON pba.BusId = gf.GestioneOttimizzataFlottaId
			LEFT JOIN RT_Flotta f ON (gf.BusId = f.FlottaId) 
			LEFT JOIN RT_TipologiaBus tb ON (f.TipologiaBusId = tb.TipologiaBusId)
			LEFT JOIN RT_Autisti a1 ON pba.Autista1 = a1.AutistiId
			LEFT JOIN RT_Autisti a2 ON pba.Autista2 = a2.AutistiId
			LEFT JOIN RT_Linea l ON gf.LineaId = l.LineaId
			LEFT JOIN RT_Corsa c ON c.CorsaId = gf.CorsaId and l.LineaId = c.LineaId
			WHERE gf.CorsaDataPartenza = '$data' AND (a1.AutistiId = $idAutista OR a2.AutistiId = $idAutista)";
		$rowsBus = $db->fetch_array($sql);

		$dt = new DT();
		foreach ($rowsBus as $row) {
			$BusId = $row['BusId'];
			$sql = "Select * from RT_MobileCarico where BusId = $BusId";
			$rowCount = $db->fetch_array($sql);
			//se il bus non � inizializzato lo inizializzo
			if (count($rowCount) == 0) {
				$corsa = new LineaGraph($row['LineaId'], $row['CorsaId'], $row['CorsaDataPartenza'], $db, true);
				$BusId = $row['BusId'];
				$LineaId = $row['LineaId'];
				$CorsaId = $row['CorsaId'];
				$DataPartenza = $row['CorsaDataPartenza'];

				foreach ($corsa->flotta[$BusId]->comuni as $index => $comune) {
					//calcolo delle salite
					$TotPickupFermata = 0;
					$TotDaFermata = 0;
					//calcolo persone che salgono dalla fermata
					if ($corsa->graph->nodes[$comune['comune']]->salite > 0) {
						foreach ($corsa->graph->nodes[$comune['comune']]->bigliettiSalite as $dest => $passeg) {
							foreach ($passeg as $value) {
								if (in_array($value, $comune['passeggeri'][$dest])) {
									$TotPickupFermata++;
									$TotDaFermata++;
								}
							}
						}
					}
					//calcolo persone che salgono da altri bus
					if (sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza) > 1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo) > 1) {
						foreach ($corsa->graph->nodes[$comune['comune']]->busArrivo as $tempBusId) {
							if ($tempBusId != $BusId) {
								$indexComuneTempBusId = -1;
								foreach ($corsa->flotta[$tempBusId]->comuni as $key => $value) {
									if (strcmp($value['comune'], $comune['comune']) == 0) {
										$indexComuneTempBusId = $key;
									}
								}
								foreach ($comune['passeggeri'] as $key => $passeggeri) {
									foreach ($passeggeri as $p) {
										if (in_array($p, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId - 1]['passeggeri'][$key])) {
											$TotPickupFermata++;
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
					$ComuneP = utf8_decode($c->Comune);
					$ComunePId = $comune['comune'];

					if ($TotPickupFermata > 0) {
						$p = new PrenotazioneDettaglio();
						$p->conn = $db;

						//persone che salgono dalla fermata
						if ($corsa->graph->nodes[$comune['comune']]->salite > 0) {
							$gruppiPasseggeri = array();
							foreach ($corsa->graph->nodes[$comune['comune']]->bigliettiSalite as $dest => $passeg) {
								foreach ($passeg as $value) {
									if (in_array($value, $corsa->flotta[$BusId]->comuni[$index]['passeggeri'][$dest])) {
										$r = $p->getPrenotazioneDettaglio($value, $LineaId, $CorsaId, $DataPartenza);
										$gruppiPasseggeri[$r['FermataPartenza']][] = $r;
									}
								}
							}

							foreach ($gruppiPasseggeri as $fermata => $passeggeri) {
								$DataPartenzaFermataN = $passeggeri[0]['DataPartenza'];
								$DataPartenzaFermata = $dt->format($passeggeri[0]['DataPartenza'], "y-m-d", "d/m/Y");

								//inserimento comune pickup
								$sql = "INSERT INTO RT_MobileCarico (BusId,ComuneId, Comune, Fermata, DataPartenza, DataPartenzaFormattata, OrarioPartenza, Pax, Caricati, Interscambio, Assenti, AutistaId) VALUES
										($BusId, $ComunePId, '" . utf8_decode(str_replace("'", "\'", $ComuneP)) . "', '" . utf8_decode(str_replace("'", "\'", $fermata)) . "', '$DataPartenzaFermataN' ,'$DataPartenzaFermata', '" . $passeggeri[0]['OrarioPartenza'] . "',$TotPickupFermata,0, 0, 0, $idAutista)";
								$idCarico = $db->query($sql);
								$idCarico = mysql_insert_id();

								foreach ($passeggeri as $passeggero) {
									//recupero codice prenotazione
									$CodicePrenotazione = $p->getCodicePrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);

									//recupero cellulare
									$ClienteCellulare = $p->getCellulare($CodicePrenotazione);

									//recupero importo, tipo titolo, codice biglietto
									$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $passeggero['PrenotazioneNumero'];
									$titoloEsiste = $db->query_first($sql);
									if ($titoloEsiste['Tot'] > 0) {
										//titolo emesso, puo' essere pagato, avere qualche residuo con scadenza o da pagare a bordo
										$TitoloEmesso = "E";

										$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneId =" . $passeggero['PrenotazioneId'] . " AND TipoMovimento='P' AND PagamentoTipoId=7";
										$rowMovimenti = $db->fetch_array($sql);
										if (count($rowMovimenti) == 0) {
											//biglietto gi� pagato o con residuo con scadenza
											$Importo = 0;
										} else {
											//biglietto con residuo da pagare a bordo
											$sql = "SELECT COUNT(*) as count FROM RT_PrenotazioneDettaglio WHERE CorsaId=$CorsaId and PrenotazioneId= " . $passeggero['PrenotazioneId'] . " AND Escludi=0";

											$rowContPersone = $db->query_first($sql);
											$Importo = $rowMovimenti[0]['Importo'] / $rowContPersone['count'];
										}
										$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $passeggero['PrenotazioneNumero'];
										$biglietto = $db->query_first($sql);
										$codiceBiglietto = $biglietto['Codice'] . "/" . $biglietto['Anno'];
									} else {
										//titolo non emesso, pu� avere pagamento a bordo o con scadenza
										$TitoloEmesso = "A";
										$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneId =" . $passeggero['PrenotazioneId'] . " AND TipoMovimento='I' AND PagamentoTipoId=7";
										$rowMovimenti = $db->fetch_array($sql);
										if (count($rowMovimenti) == 0) {
											//pagamento con scadenza
											$Importo = 0;
										} else {
											//pagamento a bordo
											$Importo = $passeggero['Importo'];
										}
										$codiceBiglietto = $CodicePrenotazione . "/" . $passeggero['PrenotazioneNumero'];
									}

									//recupero note
									if (array_key_exists($CodicePrenotazione, $note)) {
										$indiceNota = $note[$CodicePrenotazione]['indice'];
									} else {
										$idPrenotazione = $p->getIdPrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
										$sql = "SELECT Nota, TipoNota FROM RT_PrenotazionePercorsoNote WHERE PrenotazioneId=" . $idPrenotazione;
										$notePrenotazione = $db->fetch_array($sql);
										if (sizeof($notePrenotazione) > 0) {
											$countNote++;
											$tempC['indice'] = $countNote;
											$tempC['note'] = $notePrenotazione;
											$note[$CodicePrenotazione] = $tempC;
											$indiceNota = $tempC['indice'];
										} else {
											$indiceNota = "&nbsp;";
										}
									}

									//recupero andata e ritorno
									$sql = "SELECT DataPartenza, Tragitto FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero=" . $passeggero['PrenotazioneNumero'];
									$rows = $db->fetch_array($sql);
									$dataAR = "&nbsp;";
									$tragitto = "";
									if (count($rows) == 1) {
										$tragitto = "S";
									} else if (count($rows) >= 2) {
										foreach ($rows as $row) {
											if ($row['DataPartenza'] == $DataPartenza) {
												$temp = $row['Tragitto'];
											}
										}
										if (strcmp(temp, "Andata")) {
											$dataAR = $rows[1]['DataPartenza'];
											$tragitto = "A";
											$Importo = $Importo * 2;
										} else {
											$dataAR = "&nbsp;";
											$tragitto = "R";
										}
									}
									$TotaleIncasso += $Importo;
									//inserimento passeggeri con pickup da comune
									$passeggero['ComunePartenza'] = str_replace("'", "\'", $passeggero['ComunePartenza']);
									$passeggero['ComuneArrivo'] = str_replace("'", "\'", $passeggero['ComuneArrivo']);
									$passeggero['FermataArrivo'] = str_replace("'", "\'", $passeggero['FermataArrivo']);
									$passeggero['Nome'] = str_replace("'", "\'", $passeggero['Nome']);
									$passeggero['Cognome'] = str_replace("'", "\'", $passeggero['Cognome']);
									$sql = "INSERT INTO RT_MobileCaricoPasseggeri (BusId, PrenotazioneNumero, CodicePrenotazione, CodiceBiglietto, Cliente, 
											TipologiaBiglietto, ComunePartenza, ComuneArrivo, FermataArrivo, ClienteCellulare, DaPagare, TitoloEmesso, 
											DataAR, Tragitto, Caricato, ComuneId, IdCarico, AutistaId) VALUES
									($BusId, " . $passeggero['PrenotazioneNumero'] . ", '$CodicePrenotazione', '$codiceBiglietto', '" . utf8_decode($passeggero['Nome']) . " " . utf8_decode($passeggero['Cognome']) . "',
									 '" . $passeggero['TipologiaBiglietto'] . "', '" . $passeggero['ComunePartenza'] . "',
									'" . $passeggero['ComuneArrivo'] . "',	'" . $passeggero['FermataArrivo'] . "', '$ClienteCellulare', 
									'" . number_format($Importo, 2, ",", ".") . "', '$TitoloEmesso','$dataAR','$tragitto', 0, $ComunePId, $idCarico, $idAutista)";
									$db->query($sql);
								}
							}
						}

						//persone che salgono dagli altri bus
						if (sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza) > 1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo) > 1) {
							if ($TotDaFermata == 0) {
								//inserimento comune pickup interscambio 
								$sql = "SELECT * FROM RT_Orario o
										left join RT_Fermata f on f.FermataId = o.FermataId  
										where f.ComuneId = $ComunePId and o.CorsaId = $CorsaId";
								$tempOrario = $biglietto = $db->query_first($sql);
								if ($tempOrario['GiorniAggiuntivi'] == 0) {
									$DataPartenzaFermataN = $tempOrario['DataPartenza'];
								} else {
									$aggiuntivi = intval($tempOrario['GiorniAggiuntivi']) + 1;
									$DataPartenzaFermataN = date('Y-m-d', strtotime($tempOrario['DataPartenza'] . ' + ' . $aggiuntivi . ' days'));
								}
								$timestamp = strtotime($DataPartenzaFermataN);
								$DataPartenzaFermata = date("d/m/Y", $timestamp);
								$OrarioPartenza = $tempOrario['Orario'];

								$sql = "INSERT INTO RT_MobileCarico (BusId,ComuneId, Comune, Fermata, DataPartenza, DataPartenzaFormattata, OrarioPartenza, Pax, Caricati, Interscambio, Assenti, AutistaId) VALUES
								($BusId, $ComunePId, '$ComuneP', 'Interscambio bus', '$DataPartenzaFermataN' ,'$DataPartenzaFermata', '$OrarioPartenza', $TotPickupFermata,0, 1, 0, $idAutista)";
								$idCarico = $db->query($sql);
								$idCarico = mysql_insert_id();
							}
							foreach ($corsa->graph->nodes[$comune['comune']]->busArrivo as $tempBusId) {
								if ($tempBusId != $BusId) {
									$indexComuneTempBusId = -1;
									foreach ($corsa->flotta[$tempBusId]->comuni as $key => $value) {

										if (strcmp($value['comune'], $comune['comune']) == 0) {
											$indexComuneTempBusId = $key;
										}
									}

									foreach ($corsa->flotta[$BusId]->comuni[$index]['passeggeri'] as $key => $passeggeri) {
										foreach ($passeggeri as $pass) {
											if (in_array($pass, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId - 1]['passeggeri'][$key])) {
												$r = $p->getPrenotazioneDettaglio($pass, $LineaId, $CorsaId, $DataPartenza);
												//recupero codice prenotazione
												$CodicePrenotazione = $p->getCodicePrenotazione($pass, $LineaId, $CorsaId, $DataPartenza);

												//recupero cellulare
												$ClienteCellulare = $p->getCellulare($CodicePrenotazione);

												//recupero importo, tipo titolo, codice biglietto
												$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $r['PrenotazioneNumero'];
												$titoloEsiste = $db->query_first($sql);
												if ($titoloEsiste['Tot'] > 0) {
													$TitoloEmesso = "E";
													$Importo = number_format(0, 2, ",", ".") . "&euro;";

													$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $r['PrenotazioneNumero'];
													$biglietto = $db->query_first($sql);
													$codiceBiglietto = $biglietto['Codice'] . "/" . $biglietto['Anno'];
												} else {
													$TitoloEmesso = "A";
													$Importo = "Pagato";

													$codiceBiglietto = $CodicePrenotazione . "/" . $r['PrenotazioneNumero'];
												}

												//recupero note
												if (array_key_exists($CodicePrenotazione, $note)) {
													$indiceNota = $note[$CodicePrenotazione]['indice'];
												} else {
													$idPrenotazione = $p->getIdPrenotazione($r['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);
													$sql = "SELECT Nota, TipoNota FROM RT_PrenotazionePercorsoNote WHERE PrenotazioneId=" . $idPrenotazione;
													$notePrenotazione = $db->fetch_array($sql);
													if (sizeof($notePrenotazione) > 0) {
														$countNote++;
														$tempC['indice'] = $countNote;
														$tempC['note'] = $notePrenotazione;
														$note[$CodicePrenotazione] = $tempC;
														$indiceNota = $tempC['indice'];
													} else {
														$indiceNota = "&nbsp;";
													}
												}

												//recupero andata e ritorno
												$sql = "SELECT DataPartenza, Tragitto FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero=" . $r['PrenotazioneNumero'];
												$rows = $db->fetch_array($sql);
												$dataAR = "&nbsp;";
												$tragitto = "";
												if (count($rows) == 1) {
													$tragitto = "S";
												} else if (count($rows) >= 2) {
													foreach ($rows as $row) {
														if ($row['DataPartenza'] == $DataPartenza) {
															$temp = $row['Tragitto'];
														}
													}
													if (strcmp(temp, "Andata")) {
														$dataAR = $rows[1]['DataPartenza'];
														$tragitto = "A";
													} else {
														$dataAR = "&nbsp;";
														$tragitto = "R";
													}
												}

												//recupero targa autobus
												$sql = "SELECT gof.Nome, f.Targa FROM RT_GestioneOttimizzataFlotta gof
														LEFT JOIN RT_Flotta f ON (gof.BusId = f.FlottaId)
														WHERE gof.Nome =" . $corsa->flotta[$tempBusId]->nome . " AND gof.CorsaId=" . $CorsaId . " AND gof.CorsaDataPartenza = '" . $DataPartenza . "' AND gof.LineaId=" . $LineaId;
												$busTarga = $db->query_first($sql);

												//inserimento carico passeggeri da interscambio
												$r['ComuneArrivo'] = str_replace("'", "\'", $r['ComuneArrivo']);
												$r['FermataArrivo'] = str_replace("'", "\'", $r['FermataArrivo']);
												$r['Nome'] = str_replace("'", "\'", $r['Nome']);
												$r['Cognome'] = str_replace("'", "\'", $r['Cognome']);
												$sql = "INSERT INTO RT_MobileCaricoPasseggeri (BusId, PrenotazioneNumero, CodicePrenotazione, CodiceBiglietto, Cliente,
														TipologiaBiglietto, ComunePartenza, ComuneArrivo, FermataArrivo, ClienteCellulare, DaPagare, TitoloEmesso,
														DataAR, Tragitto, Caricato, ComuneId, IdCarico, AutistaId) VALUES
														($BusId, " . $r['PrenotazioneNumero'] . ", '$CodicePrenotazione', '$codiceBiglietto', '" . utf8_decode($r['Nome']) . " " . utf8_decode($r['Cognome']) . "',
									 					'" . $r['TipologiaBiglietto'] . "', 'Autobus " . $corsa->flotta[$tempBusId]->nome . " - Targa: " . $busTarga['Targa'] . "',
														'" . $r['ComuneArrivo'] . "',	'" . $r['FermataArrivo'] . "', '$ClienteCellulare',
														'" . number_format($Importo, 2, ",", ".") . "', '$TitoloEmesso','$dataAR','$tragitto',0, $ComunePId, $idCarico, $idAutista)";
												$db->query($sql);
											}
										}
									}
								}
							}
						}
					}
				}
			}

			/*scarico passeggeri*/
			foreach ($corsa->flotta[$BusId]->comuni as $index => $comune) {
				//calcolo pax discese
				$TotDropoffFermata = 0;
				$TotDDaFermata = 0;
				//calcolo delle persone che scendono alla fermata
				if ($corsa->graph->nodes[$comune['comune']]->discese > 0) {
					foreach ($corsa->flotta[$BusId]->comuni[$index - 1]['passeggeri'][$comune['comune']] as $pass) {
						if (in_array($pass, $corsa->flotta[$BusId]->comuni[$index - 1]['passeggeri'][$comune['comune']])) {
							$TotDropoffFermata++;
							$TotDDaFermata++;
						}
					}
				}

				//calcolo delle persone che scendono per salire in un altri bus
				if (sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza) > 1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo) > 1) {
					foreach ($corsa->graph->nodes[$comune['comune']]->busPartenza as $tempBusId) {
						if ($tempBusId != $BusId) {
							$indexComuneTempBusId = -1;
							foreach ($corsa->flotta[$tempBusId]->comuni as $key => $value) {
								if (strcmp($value['comune'], $comune['comune']) == 0) {
									$indexComuneTempBusId = $key;
								}
							}
							foreach ($corsa->flotta[$BusId]->comuni[$index - 1]['passeggeri'] as $key => $passeggeri) {
								foreach ($passeggeri as $pass) {
									if (in_array($pass, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])) {
										$TotDropoffFermata++;
									}
								}
							}
						}
					}
				}
				if ($TotDropoffFermata > 0) {
					$p = new PrenotazioneDettaglio();
					$p->conn = $db;
					//recupero nome del comune
					$c = new Comune($comune['comune']);
					$c->conn = $db;
					$c->inizializzaDatiGenerali();
					$ComuneD = $c->Comune;
					$ComuneDId = $comune['comune'];

					//persone che scendono alla fermata
					if ($corsa->graph->nodes[$comune['comune']]->discese > 0) {
						$gruppiPasseggeri = array();
						foreach ($corsa->flotta[$BusId]->comuni[$index - 1]['passeggeri'][$comune['comune']] as $value) {
							if (in_array($value, $corsa->flotta[$BusId]->comuni[$index - 1]['passeggeri'][$comune['comune']])) {
								$r = $p->getPrenotazioneDettaglio($value, $LineaId, $CorsaId, $DataPartenza);
								$gruppiPasseggeri[$r['FermataArrivo']][] = $r;
							}
						}


						foreach ($gruppiPasseggeri as $fermata => $passeggeri) {
							$DataArrivoFermataN = $passeggeri[0]['DataArrivo'];
							$DataArrivoFermata = $dt->format($passeggeri[0]['DataArrivo'], "y-m-d", "d/m/Y");

							//inserimento comune pickup
							$ComuneD = str_replace("'", "\'", $ComuneD);
							$fermata = str_replace("'", "\'", $fermata);
							$sql = "INSERT INTO RT_MobileScarico (BusId,ComuneId, Comune, Fermata, DataArrivo, DataArrivoFormattata, OrarioPartenza, Pax, Scaricati, Interscambio, AutistaId) VALUES
							($BusId, $ComuneDId, '" . utf8_decode(str_replace("'", "\'", $ComuneD)) . "', '" . utf8_decode(str_replace("'", "\'", $fermata)) . "', '$DataArrivoFermataN' ,'$DataArrivoFermata', '" . $passeggeri[0]['OrarioArrivo'] . "',$TotDropoffFermata,0, 0, $idAutista)";
							$idScarico = $db->query($sql);
							$idScarico = mysql_insert_id();

							foreach ($passeggeri as $passeggero) {
								//recupero codice prenotazione
								$CodicePrenotazione = $p->getCodicePrenotazione($passeggero['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);

								//recupero codice biglietto
								$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $passeggero['PrenotazioneNumero'];
								$titoloEsiste = $db->query_first($sql);
								if ($titoloEsiste['Tot'] > 0) {
									$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $passeggero['PrenotazioneNumero'];
									$biglietto = $db->query_first($sql);
									$codiceBiglietto = $biglietto['Codice'] . "/" . $biglietto['Anno'];
								} else {
									$codiceBiglietto = $CodicePrenotazione . "/" . $passeggero['PrenotazioneNumero'];
								}

								//recupero indice note
								if (array_key_exists($CodicePrenotazione, $note)) {
									$indiceNota = $note[$CodicePrenotazione]['indice'];
								} else {
									$indiceNota = "&nbsp;";
								}

								//inserimento passeggeri con pickup da comune
								$passeggero['ComunePartenza'] = str_replace("'", "\'", $passeggero['ComunePartenza']);
								$passeggero['ComuneArrivo'] = str_replace("'", "\'", $passeggero['ComuneArrivo']);
								$passeggero['Nome'] = str_replace("'", "\'", $passeggero['Nome']);
								$passeggero['Cognome'] = str_replace("'", "\'", $passeggero['Cognome']);
								$sql = "INSERT INTO RT_MobileScaricoPasseggeri (BusId, PrenotazioneNumero, CodicePrenotazione, CodiceBiglietto, Cliente,
								TipologiaBiglietto, ComunePartenza, ComuneArrivo, Scaricato, ComuneId, IdScarico, AutistaId) VALUES
								($BusId, " . $passeggero['PrenotazioneNumero'] . ", '$CodicePrenotazione', '$codiceBiglietto', '" . utf8_decode($passeggero['Nome']) . " " . utf8_decode($passeggero['Cognome']) . "',
									 '" . $passeggero['TipologiaBiglietto'] . "', '" . utf8_decode($passeggero['ComunePartenza']) . "',
									'" . utf8_decode($passeggero['ComuneArrivo']) . "', 0, $ComuneDId, $idScarico, $idAutista)";
								$db->query($sql);
							}
						}
					}

					//persone che scendono per salire in altri bus
					if (sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza) > 1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo) > 1) {
						if ($TotDDaFermata == 0) {
							//inserimento comune pickup interscambio
							$ComuneD = str_replace("'", "\'", $ComuneD);
							$sql = "INSERT INTO RT_MobileScarico (BusId,ComuneId, Comune, Fermata, DataArrivo, DataArrivoFormattata, OrarioPartenza, Pax, Scaricati, Interscambio, AutistaId) VALUES
							($BusId, $ComuneDId, '" . utf8_decode(str_replace("'", "\'", $ComuneD)) . "', 'Interscambio Bus', '' ,'', '',$TotDropoffFermata,0, 1, $idAutista)";
							$idScarico = $db->query($sql);
							$idScarico = mysql_insert_id();
						}
						$p = new PrenotazioneDettaglio();
						$p->conn = $db;
						foreach ($corsa->graph->nodes[$comune['comune']]->busPartenza as $tempBusId) {
							if ($tempBusId != $BusId) {
								$indexComuneTempBusId = -1;
								foreach ($corsa->flotta[$tempBusId]->comuni as $key => $value) {
									if (strcmp($value['comune'], $comune['comune']) == 0) {
										$indexComuneTempBusId = $key;
									}
								}
								foreach ($corsa->flotta[$BusId]->comuni[$index - 1]['passeggeri'] as $key => $passeggeri2) {
									foreach ($passeggeri2 as $value) {
										if (in_array($value, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])) {

											//recupero informazioni passeggero
											$r = $p->getPrenotazioneDettaglio($value, $LineaId, $CorsaId, $DataPartenza);

											//recupero codice prenotazione
											$CodicePrenotazione = $p->getCodicePrenotazione($r['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);

											//recupero codice biglietto
											$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $r['PrenotazioneNumero'];
											$titoloEsiste = $db->query_first($sql);
											if ($titoloEsiste['Tot'] > 0) {
												$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $r['PrenotazioneNumero'];
												$biglietto = $db->query_first($sql);
												$codiceBiglietto = $biglietto['Codice'] . "/" . $biglietto['Anno'];
											} else {
												$codiceBiglietto = $CodicePrenotazione . "/" . $r['PrenotazioneNumero'];
											}

											//recupero indice note
											if (array_key_exists($CodicePrenotazione, $note)) {
												$indiceNota = $note[$CodicePrenotazione]['indice'];
											} else {
												$indiceNota = "&nbsp;";
											}

											//recupero targa autobus
											$sql = "SELECT gof.Nome, f.Targa FROM RT_GestioneOttimizzataFlotta gof
												LEFT JOIN RT_Flotta f ON (gof.BusId = f.FlottaId)
												WHERE gof.Nome =" . $corsa->flotta[$tempBusId]->nome . " AND gof.CorsaId=" . $CorsaId . " AND gof.CorsaDataPartenza = '" . $DataPartenza . "' AND gof.LineaId=" . $LineaId;
											$busTarga = $db->query_first($sql);

											//inserimento passeggeri con pickup da comune
											$r['ComunePartenza'] = str_replace("'", "\'", $r['ComunePartenza']);
											$r['Nome'] = str_replace("'", "\'", $r['Nome']);
											$r['Cognome'] = str_replace("'", "\'", $r['Cognome']);
											$sql = "INSERT INTO RT_MobileScaricoPasseggeri (BusId, PrenotazioneNumero, CodicePrenotazione, CodiceBiglietto, Cliente,
											TipologiaBiglietto, ComunePartenza, ComuneArrivo, Scaricato, ComuneId, IdScarico, AutistaId) VALUES
											($BusId, " . $r['PrenotazioneNumero'] . ", '$CodicePrenotazione', '$codiceBiglietto', '" . utf8_decode($r['Nome']) . " " . utf8_decode($r['Cognome']) . "',
										 '" . $r['TipologiaBiglietto'] . "', '" . utf8_decode($r['ComunePartenza']) . "',
										'Autobus " . $corsa->flotta[$tempBusId]->nome . " - Targa: " . $busTarga['Targa'] . "', 0, $ComuneDId, $idScarico, $idAutista)";
											$db->query($sql);
										}
									}
								}
							}
						}
					}
				}
			}

			//foglio di scambio
			foreach ($corsa->flotta[$BusId]->comuni as $index => $comune) {
				//calcolo pax discese
				$TotDropoffFermata = 0;
				$TotDDaFermata = 0;

				//calcolo delle persone che scendono per salire in un altri bus
				if (sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza) > 1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo) > 1) {
					foreach ($corsa->graph->nodes[$comune['comune']]->busPartenza as $tempBusId) {
						if ($tempBusId != $BusId) {
							$indexComuneTempBusId = -1;
							foreach ($corsa->flotta[$tempBusId]->comuni as $key => $value) {
								if (strcmp($value['comune'], $comune['comune']) == 0) {
									$indexComuneTempBusId = $key;
								}
							}
							foreach ($corsa->flotta[$BusId]->comuni[$index - 1]['passeggeri'] as $key => $passeggeri) {
								foreach ($passeggeri as $pass) {
									if (in_array($pass, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])) {
										$TotDropoffFermata++;
									}
								}
							}
						}
					}
				}
				if ($TotDropoffFermata > 0) {
					//recupero nome del comune
					$c = new Comune($comune['comune']);
					$c->conn = $db;
					$c->inizializzaDatiGenerali();
					$ComuneD = $c->Comune;
					$ComuneDId = $comune['comune'];
					//persone che scendono per salire in altri bus
					if (sizeof($corsa->graph->nodes[$comune['comune']]->busPartenza) > 1 || sizeof($corsa->graph->nodes[$comune['comune']]->busArrivo) > 1) {
						if ($TotDDaFermata == 0) {

							//inserisco comune
							$ComuneD = str_replace("'", "\'", $ComuneD);
							$sql = "INSERT INTO RT_MobileScambio (BusId, ComuneId, Comune, Fermata, Pax, AutistaId) VALUES
							($BusId, $ComuneDId, '" . utf8_decode(str_replace("'", "\'", $ComuneD)) . "', 'Interscambio Bus',$TotDropoffFermata, $idAutista)";
							$idScambio = $db->query($sql);
							$idScambio = mysql_insert_id();
						}
						$p = new PrenotazioneDettaglio();
						$p->conn = $db;
						foreach ($corsa->graph->nodes[$comune['comune']]->busPartenza as $tempBusId) {
							if ($tempBusId != $BusId) {
								$indexComuneTempBusId = -1;
								foreach ($corsa->flotta[$tempBusId]->comuni as $key => $value) {
									if (strcmp($value['comune'], $comune['comune']) == 0) {
										$indexComuneTempBusId = $key;
									}
								}
								foreach ($corsa->flotta[$BusId]->comuni[$index - 1]['passeggeri'] as $key => $passeggeri2) {
									foreach ($passeggeri2 as $value) {
										if (in_array($value, $corsa->flotta[$tempBusId]->comuni[$indexComuneTempBusId]['passeggeri'][$key])) {

											//recupero informazioni passeggero
											$r = $p->getPrenotazioneDettaglio($value, $LineaId, $CorsaId, $DataPartenza);

											//recupero codice prenotazione
											$CodicePrenotazione = $p->getCodicePrenotazione($r['PrenotazioneNumero'], $LineaId, $CorsaId, $DataPartenza);

											//recupero codice biglietto
											$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $r['PrenotazioneNumero'];
											$titoloEsiste = $db->query_first($sql);
											if ($titoloEsiste['Tot'] > 0) {
												$sql = "SELECT Codice, Anno FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId=" . $r['PrenotazioneNumero'];
												$biglietto = $db->query_first($sql);
												$codiceBiglietto = $biglietto['Codice'] . "/" . $biglietto['Anno'];
											} else {
												$codiceBiglietto = $CodicePrenotazione . "/" . $passeggero['PrenotazioneNumero'];
											}

											//recupero indice note
											if (array_key_exists($CodicePrenotazione, $note)) {
												$indiceNota = $note[$CodicePrenotazione]['indice'];
											} else {
												$indiceNota = "&nbsp;";
											}

											//recupero targa autobus
											$sql = "SELECT gof.Nome, f.Targa FROM RT_GestioneOttimizzataFlotta gof
													LEFT JOIN RT_Flotta f ON (gof.BusId = f.FlottaId)
													WHERE gof.Nome =" . $corsa->flotta[$tempBusId]->nome . " AND gof.CorsaId=" . $CorsaId . " AND gof.CorsaDataPartenza = '" . $DataPartenza . "' AND gof.LineaId=" . $LineaId;
											$busTarga = $db->query_first($sql);

											//inserimento passeggeri con pickup da comune
											$r['ComunePartenza'] = str_replace("'", "\'", $r['ComunePartenza']);
											$r['ComuneArrivo'] = str_replace("'", "\'", $r['ComuneArrivo']);
											$r['Nome'] = str_replace("'", "\'", $r['Nome']);
											$r['Cognome'] = str_replace("'", "\'", $r['Cognome']);
											$sql = "INSERT INTO RT_MobileScambioPasseggeri (BusId, PrenotazioneNumero, CodicePrenotazione, CodiceBiglietto, Cliente,
											TipologiaBiglietto, ComunePartenza, ComuneArrivo, BusInterscambio, IdScambio, AutistaId) VALUES
											($BusId, " . $r['PrenotazioneNumero'] . ", '$CodicePrenotazione', '$codiceBiglietto', '" . utf8_decode($r['Nome']) . " " . utf8_decode($r['Cognome']) . "',
											 '" . $r['TipologiaBiglietto'] . "', '" . utf8_decode($r['ComunePartenza']) . "',
											'" . utf8_decode($r['ComuneArrivo']) . "', 'Autobus " . $corsa->flotta[$tempBusId]->nome . " - Targa: " . $busTarga['Targa'] . "', $idScambio, $idAutista)";
											$db->query($sql);
										}
									}
								}
							}
						}
					}
				}
			}
		}



		return $rowsBus;
	}

	public function updateSingoloBus($corsaId, $busId, $busNumero, $data, $idAutista)
	{
		$db = $this->conn;

		$sql = "SELECT * FROM RT_CorsaInizioPreparazione c
		left join RT_MobileCarico m on (m.CorsaId = c.CorsaId and c.DataCorsa = m.CorsaDataPartenza)
		where m.CorsaDataPartenza = '$data' and m.CorsaId = $corsaId and m.TipoServizio = 'Bus' and m.BusId = $busId and BusNumero = $busNumero";
		$inizializzazione = $db->fetch_array($sql);
		if (count($inizializzazione) == 0) {
			$sql = "delete from RT_MobileCarico where CorsaId = $corsaId and BusId = $busId and BusNumero = $busNumero and CorsaDataPartenza = '$data' and TipoServizio = 'Bus'";
			$db->query($sql);

			$sql = "delete from RT_MobileCaricoPasseggeri  where CorsaId = $corsaId and BusId = $busId and BusNumero = $busNumero and CorsaDataPartenza = '$data' and TipoServizio = 'Bus'";
			$db->query($sql);

			$sql = "INSERT INTO RT_MobileCarico (
				FermataNome,
				Comune,
				Orario,
				Pax,
				Caricati,
				DaModificare,
				Assenti,
				CorsaId,
				CorsaDataPartenza,
				TipoServizio,
				DataPickup,
				BusId,
				BusNumero,
				OrarioArrivo,
				DataArrivo
				) SELECT
				`RT_PrenotazioneDettaglio`.`FermataPartenza` AS `FermataPartenza`,
				`RT_PrenotazioneDettaglio`.`ComunePartenza` AS `ComunePartenza`,
				`RT_PrenotazioneDettaglio`.`OrarioPartenza` AS `OrarioPartenza`,
				count(
				`RT_PrenotazioneDettaglio`.`PrenotazioneDettaglioId`
				) AS `TotPickupFermata`,
				0 AS Caricati,
				0 as DaModificare,
				0 as Assenti,
				RT_PreparazioneBus.CorsaId,
				RT_PreparazioneBus.DataPartenza,
				'Bus' AS TipoServizio,
				`RT_PrenotazioneDettaglio`.`DataPartenza` AS `DataPickup`,
				`RT_PreparazioneBus`.`BusId`,
				`RT_PreparazioneBus`.`BusNumero`,
				`RT_PrenotazioneDettaglio`.OrarioArrivo,
				`RT_PrenotazioneDettaglio`.DataArrivo
				FROM
				((RT_PreparazioneBus
				INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId))))
				INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId)))
				LEFT JOIN RT_MobileCarico ON RT_PreparazioneBus.CorsaId = RT_MobileCarico.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_MobileCarico.CorsaDataPartenza AND RT_PreparazioneBus.BusId = RT_MobileCarico.BusId AND RT_PreparazioneBus.BusNumero = RT_MobileCarico.BusNumero AND RT_PrenotazioneDettaglio.FermataPartenza = RT_MobileCarico.FermataNome AND RT_PrenotazioneDettaglio.ComunePartenza = RT_MobileCarico.Comune
				WHERE
				(RT_PrenotazioneDettaglio.TipoServizio = _latin1 'Bus')
				AND RT_PreparazioneBus.CorsaId = $corsaId
				AND RT_PreparazioneBus.DataPartenza = '$data'
				AND RT_PrenotazioneDettaglio.Escludi = 0
				AND RT_MobileCarico.IdCarico IS NULL
				and (RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3)
				GROUP BY
				`RT_PreparazioneBus`.`CorsaId`,
				`RT_PreparazioneBus`.`DataPartenza`,
				`RT_PreparazioneBus`.`BusNumero`,
				`RT_PreparazioneBus`.`BusId`,
				`RT_PrenotazioneDettaglio`.`ComunePartenza`,
				`RT_PrenotazioneDettaglio`.`FermataPartenza`,
				`RT_PreparazioneBus`.`OdcIdRef`
				ORDER BY
				OrarioPartenza ASC";
			$db->query($sql);
			//             	die($sql);
			$sql = "INSERT INTO RT_MobileCaricoPasseggeri (
				PrenotazioneId,
				ComunePartenza,
				FermataPartenza,
				ComuneArrivo,
				FermataArrivo,
				CorsaDataPartenza,
				CorsaId,
				ClienteNome,
				ClienteCellulare,
				TipoViaggio,
				Pax,
				Caricati,
				Interi,
				Ridotti,
				NotaCarico,
				Tragitto,
				DataRitorno,
				TipoServizio,
				BusId,
				BusNumero,
				PrenotazioneStato,
				CorsaRitornoId,
				TotaleImportoPrenotazione,
				DataPickup,
				DataDropOff,
				OraPickup,
				OraDropOff,
				AutistaId
				) SELECT
				RT_PreparazioneBus.PrenotazioneId AS PrenotazioneId,
				RT_PrenotazioneDettaglio.ComunePartenza AS ComunePartenza,
				RT_PrenotazioneDettaglio.FermataPartenza AS FermataPartenza,
				RT_PrenotazioneDettaglio.ComuneArrivo AS ComuneArrivo,
				RT_PrenotazioneDettaglio.FermataArrivo AS FermataArrivo,
				RT_PreparazioneBus.DataPartenza AS DataPartenza,
				RT_PreparazioneBus.CorsaId AS CorsaId,
				RT_Prenotazione.ClienteNome AS ClienteNome,
				RT_Prenotazione.ClienteCellulare AS ClienteCellulare,
				RT_PrenotazioneDettaglio.TipoViaggio,
				Count(
				RT_PrenotazioneDettaglio.PrenotazioneId
				) AS TotalePaxPrenotati,
				0 AS 'Caricati',
				(SELECT count(*) as Interi FROM RT_PrenotazioneDettaglio g where g.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and TipologiaBiglietto like '%Intero%' and DataInizioItinerario='$data' and CorsaInizioItinerario=$corsaId and TipoServizio='Bus') as Interi,
				(SELECT count(*) as Ridotti FROM RT_PrenotazioneDettaglio g1 where g1.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and TipologiaBiglietto like '%Ridotto%' and DataInizioItinerario='$data' and CorsaInizioItinerario=$corsaId and TipoServizio='Bus') as Ridotti,
				(SELECT Nota
				FROM
				RT_PrenotazionePercorsoNote
            	INNER JOIN RT_PrenotazionePercorso ON RT_PrenotazionePercorsoNote.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId and RT_PrenotazionePercorsoNote.PrenotazionePercorsoId = RT_PrenotazionePercorso.PrenotazionePercorsoId
				WHERE
				RT_PrenotazionePercorso.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and
				RT_PrenotazionePercorsoNote.TipoNota = 'S' AND
				RT_PrenotazionePercorso.CorsaId = $corsaId AND
				RT_PrenotazionePercorso.CorsaDataPartenza = '$data' AND
				RT_PrenotazionePercorso.Stato = 1 AND
				RT_PrenotazionePercorso.Cancella = 0) as NotaCarico,
				RT_PrenotazioneDettaglio.Tragitto AS Tragitto,
				(SELECT p1.CorsaDataPartenza FROM RT_PrenotazionePercorso p1 WHERE p1.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and p1.Direzione = 'R') as DataRitorno,
				'Bus' AS TipoServizio,
				RT_PreparazioneBus.BusId,
				RT_PreparazioneBus.BusNumero,
				RT_Prenotazione.PrenotazioneStato,
				(SELECT p1.CorsaId FROM RT_PrenotazionePercorso p1 WHERE p1.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and p1.Direzione = 'R') as CorsaRitornoId,
				RT_ImportoPerPrenotazione.DaIncassare AS TotaleImportoPrenotazione,
				RT_PrenotazioneDettaglio.DataPartenza,
				RT_PrenotazioneDettaglio.DataArrivo,
				RT_PrenotazioneDettaglio.OrarioPartenza,
				RT_PrenotazioneDettaglio.OrarioArrivo,
				$idAutista as AutistaId
				FROM
				((((RT_PreparazioneBus
				INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId) AND (RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId))))
				INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId))))
				INNER JOIN RT_PrenotazionePercorso ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazionePercorso.CorsaId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazionePercorso.CorsaDataPartenza))))
				LEFT JOIN RT_FoglioCaricoCorseElencoModificheDettaglio ON RT_PreparazioneBus.CorsaId = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaDataPartenza AND RT_PreparazioneBus.PrenotazioneId = RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneId
				LEFT JOIN RT_MobileCaricoPasseggeri ON RT_PreparazioneBus.CorsaId = RT_MobileCaricoPasseggeri.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_MobileCaricoPasseggeri.CorsaDataPartenza AND RT_PreparazioneBus.BusId = RT_MobileCaricoPasseggeri.BusId AND RT_PreparazioneBus.BusNumero = RT_MobileCaricoPasseggeri.BusNumero AND RT_Prenotazione.PrenotazioneId = RT_MobileCaricoPasseggeri.PrenotazioneId
				LEFT JOIN RT_ImportoPerPrenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId
				WHERE
				((RT_PrenotazioneDettaglio.TipoServizio = _latin1 'Bus')
				AND ((RT_Prenotazione.PrenotazioneStato = 1) OR (RT_Prenotazione.PrenotazioneStato = 3)))
				AND RT_PreparazioneBus.CorsaId = $corsaId
				AND RT_PreparazioneBus.DataPartenza = '$data'
				AND RT_PreparazioneBus.BusId = $busId
				AND RT_PreparazioneBus.BusNumero = $busNumero
				AND RT_PrenotazioneDettaglio.Escludi = 0
				AND RT_MobileCaricoPasseggeri.IdCaricoPasseggeri IS NULL
				GROUP BY
				`RT_PreparazioneBus`.`CorsaId`,
				`RT_PreparazioneBus`.`DataPartenza`,
				`RT_PreparazioneBus`.`BusNumero`,
				`RT_PreparazioneBus`.`BusId`,
				`RT_PrenotazioneDettaglio`.`PrenotazioneId`,
				`RT_PrenotazioneDettaglio`.`ComunePartenza`,
				`RT_PrenotazioneDettaglio`.`DataPartenza`
				ORDER BY
				`RT_Prenotazione`.`ClienteNome`";

			$db->query($sql);
			//               die($sql);
			$sql = "UPDATE RT_MobileCaricoPasseggeri AS t
				INNER JOIN (
				SELECT
				RT_PrenotazionePercorso.ComuneDiscesa,
				RT_PrenotazionePercorso.FermataDiscesa,
				RT_PrenotazionePercorso.DataOraDiscesa,
				RT_PrenotazionePercorso.CorsaId,
				RT_PrenotazionePercorso.CorsaDataPartenza,
				RT_PrenotazionePercorso.PrenotazioneId
				FROM
				RT_MobileCaricoPasseggeri
				INNER JOIN RT_PrenotazionePercorso ON RT_MobileCaricoPasseggeri.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId
				AND RT_MobileCaricoPasseggeri.CorsaId = RT_PrenotazionePercorso.CorsaId
				AND RT_MobileCaricoPasseggeri.CorsaDataPartenza = RT_PrenotazionePercorso.CorsaDataPartenza
				WHERE
				RT_PrenotazionePercorso.Cancella = 0
				AND RT_PrenotazionePercorso.Stato = 1 and
				RT_PrenotazionePercorso.CorsaId=$corsaId and
				RT_PrenotazionePercorso.CorsaDataPartenza='$data'
				) AS t1 ON t.PrenotazioneId = t1.PrenotazioneId
				AND t.CorsaDataPartenza = t1.CorsaDataPartenza
				AND t.CorsaId = t1.CorsaId
				SET t.ComuneArrivoFinale = t1.ComuneDiscesa,
				t.FermataArrivoFinale = t1.FermataDiscesa
				";
			$db->query($sql);
		} else {

			$sql = "select RT_Prenotazione.PrenotazioneId, RT_Prenotazione.DataIns,
		RT_Prenotazione.DataAgg, `RT_Prenotazione`.`PrenotazioneStato` AS `PrenotazioneStato`,
		(`RT_Prenotazione`.`TotalePaxPrenotati` - `RT_PrenotazionePercorso`.`PasseggeriEsclusi`) as Pax
		from
		(((`RT_PrenotazionePercorso`
		join `RT_CorsaInizioPreparazione` ON (((`RT_PrenotazionePercorso`.`CorsaId` = `RT_CorsaInizioPreparazione`.`CorsaId`) and (`RT_PrenotazionePercorso`.`CorsaDataPartenza` = `RT_CorsaInizioPreparazione`.`DataCorsa`))))
		join `RT_Prenotazione` ON ((`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`))))
				where
				(((`RT_CorsaInizioPreparazione`.`DataIns` < `RT_PrenotazionePercorso`.`DataIns`)
				or (`RT_CorsaInizioPreparazione`.`DataIns` < `RT_PrenotazionePercorso`.`DataAgg`)) or (`RT_CorsaInizioPreparazione`.`DataIns` > `RT_PrenotazionePercorso`.`DataIns` and `RT_PrenotazionePercorso`.`DataAgg` is null and RT_PrenotazionePercorso.PrenotazioneStato = 6))
				and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$data'
				and `RT_PrenotazionePercorso`.`CorsaId` = $corsaId
				order by `RT_PrenotazionePercorso`.`CorsaDataPartenza`";
			$news = $db->fetch_array($sql);
			foreach ($news as $entry) {
				$prenotazioneId = $entry['PrenotazioneId'];
				$sql = "Select * FROM RT_MobileCaricoPasseggeri WHERE PrenotazioneId = $prenotazioneId and CorsaId = $corsaId
				and CorsaDataPartenza = '$data' and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus'";
				$p = $db->fetch_array($sql);

				if (count($p) != 0) {
					//cancellazione e modifica
					if (($entry['PrenotazioneStato'] == 1 || $entry['PrenotazioneStato'] == 3) && $p[0]['PrenotazioneStato'] != $entry['PrenotazioneStato']) {
						//emissione biglietto
						$sql = "UPDATE RT_MobileCaricoPasseggeri SET PrenotazioneStato = " . $entry['PrenotazioneStato'] . " WHERE PrenotazioneId = $prenotazioneId and CorsaId = $corsaId and CorsaDataPartenza = '$data' and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus'";
						$db->query($sql);
					} else if (!(($entry['PrenotazioneStato'] == 1 || $entry['PrenotazioneStato'] == 3) && $p[0]['PrenotazioneStato'] == $entry['PrenotazioneStato'])) {
						$sql = "SELECT * FROM RT_PrenotazioneDettaglio WHERE CorsaId = $corsaId and DataInizioItinerario = '$data' and TipoServizio = 'Bus' and PrenotazioneId = $prenotazioneId";
						$b = $db->fetch_array($sql);
						$sql = "Select * FROM RT_MobileCarico WHERE CorsaId = $corsaId and CorsaDataPartenza = '$data' and BusId = $busId and BusNumero = $busNumero and FermataNome = '" . $b[0]['FermataPartenza'] . "' and Comune = '" . $b[0]['ComunePartenza'] . "'";
						$tempFermata = $db->fetch_array($sql);

						//cancellazione e modifica
						$caricati = 0;
						$modificati = 0;
						$assenti = 0;
						$pax = $tempFermata[0]['Pax'] - $p[0]['Pax'];
						if ($p[0]['Caricati'] == 1) {
							$caricati = $p[0]['Pax'];
							$modificati = 0;
							$assenti = 0;
						} else if ($p[0]['Caricati'] == 2) {
							$caricati = 0;
							$modificati = $p[0]['Pax'];
							$assenti = 0;
						} else if ($p[0]['Caricati'] == 3) {
							$caricati = 0;
							$modificati = 0;
							$assenti = $p[0]['Pax'];
						} else {
							$caricati = 0;
							$modificati = 0;
							$assenti = 0;
						}

						//aggiornamento fermata
						if ($pax == 0) {
							$sql = "DELETE FROM RT_MobileCarico Where IdCarico = " . $tempFermata[0]['IdCarico'];
							$db->query($sql);
						} else {
							$sql = "UPDATE RT_MobileCarico SET Pax = Pax -" . $p[0]['Pax'] . ", Caricati = (Caricati - $caricati), DaModificare = (DaModificare - $modificati), Assenti = (Assenti - $assenti) Where IdCarico = " . $tempFermata[0]['IdCarico'];
							$db->query($sql);
						}

						$sql = "DELETE FROM RT_MobileCaricoPasseggeri WHERE PrenotazioneId = $prenotazioneId and CorsaId = $corsaId and CorsaDataPartenza = '$data' and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus'";
						$db->query($sql);
					}
				} else if ($entry['PrenotazioneStato'] == 1 || $entry['PrenotazioneStato'] == 3) {
					//controllo bus
					$sql = "SELECT * FROM RT_PreparazioneBus WHERE PrenotazioneId = $prenotazioneId and CorsaId = $corsaId and DataPartenza = '$data' and BusId = $busId and BusNumero = $busNumero";
					$countTemp = $db->fetch_array($sql);

					if (count($countTemp) > 0) {
						//inserimento
						//controllo su MobileCarico
						$sql = "SELECT * FROM RT_PrenotazioneDettaglio WHERE CorsaId = $corsaId and DataInizioItinerario = '$data' and TipoServizio = 'Bus' and PrenotazioneId = $prenotazioneId";
						$b = $db->fetch_array($sql);
						$sql = "SELECT * FROM RT_MobileCarico WHERE Comune = '" . $b[0]['ComunePartenza'] . "' and FermataNome = '" . $b[0]['FermataPartenza'] . "' and CorsaId = $corsaId and CorsaDataPartenza = '$data' and BusId = $busId and TipoServizio = 'Bus' and BusNumero = $busNumero and TipoServizio = 'Bus'";
						$f = $db->fetch_array($sql);

						if (count($f) == 0) {
							//Inserimento nuova fermata
							$sql = "INSERT INTO RT_MobileCarico (
								FermataNome,
								Comune,
								Orario,
								Pax,
								Caricati,
								DaModificare,
								Assenti,
								CorsaId,
								CorsaDataPartenza,
								TipoServizio,
								DataPickup,
								BusId,
								BusNumero,
								OrarioArrivo,
								DataArrivo
								) SELECT
								`RT_PrenotazioneDettaglio`.`FermataPartenza` AS `FermataPartenza`,
								`RT_PrenotazioneDettaglio`.`ComunePartenza` AS `ComunePartenza`,
										`RT_PrenotazioneDettaglio`.`OrarioPartenza` AS `OrarioPartenza`,
										count(
										`RT_PrenotazioneDettaglio`.`PrenotazioneDettaglioId`
										) AS `TotPickupFermata`,
										0 AS Caricati,
										0 as DaModificare,
										0 as Assenti,
												RT_PreparazioneBus.CorsaId,
										RT_PreparazioneBus.DataPartenza,
										'Bus' AS TipoServizio,
										`RT_PrenotazioneDettaglio`.`DataPartenza` AS `DataPickup`,
												`RT_PreparazioneBus`.`BusId`,
												`RT_PreparazioneBus`.`BusNumero`,
												`RT_PrenotazioneDettaglio`.OrarioArrivo,
													`RT_PrenotazioneDettaglio`.DataArrivo
													FROM
													((RT_PreparazioneBus
													INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId))))
															INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId)))
																	LEFT JOIN RT_MobileCarico ON RT_PreparazioneBus.CorsaId = RT_MobileCarico.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_MobileCarico.CorsaDataPartenza AND RT_PreparazioneBus.BusId = RT_MobileCarico.BusId AND RT_PreparazioneBus.BusNumero = RT_MobileCarico.BusNumero AND RT_PrenotazioneDettaglio.FermataPartenza = RT_MobileCarico.FermataNome AND RT_PrenotazioneDettaglio.ComunePartenza = RT_MobileCarico.Comune
			            				WHERE
			            				(RT_PrenotazioneDettaglio.TipoServizio = _latin1 'Bus')
			            				AND RT_PreparazioneBus.CorsaId = $corsaId
			            				AND RT_PreparazioneBus.DataPartenza = '$data'
			            				AND RT_PrenotazioneDettaglio.Escludi = 0
			            				AND RT_MobileCarico.IdCarico IS NULL
			            				and (RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3)
			            					GROUP BY
			            					`RT_PreparazioneBus`.`CorsaId`,
			            					`RT_PreparazioneBus`.`DataPartenza`,
			            					`RT_PreparazioneBus`.`BusNumero`,
			            					`RT_PreparazioneBus`.`BusId`,
			            					`RT_PrenotazioneDettaglio`.`ComunePartenza`,
	            				`RT_PrenotazioneDettaglio`.`FermataPartenza`,
	            				`RT_PreparazioneBus`.`OdcIdRef`
			            				ORDER BY
			            				OrarioPartenza ASC";
							$db->query($sql);
						} else {
							//aggiornamento fermata esistente
							$sql = "UPDATE RT_MobileCarico SET Pax = Pax + " . $entry['Pax'] . " Where CorsaId = $corsaId and CorsaDataPartenza = '$data' and BusId = $busId and BusNumero = $busNumero and FermataNome = '" . $b[0]['FermataPartenza'] . "' and Comune = '" . $b[0]['ComunePartenza'] . "' and TipoServizio = 'Bus'";
							$db->query($sql);
						}


						$sql = "INSERT INTO RT_MobileCaricoPasseggeri (
			            				PrenotazioneId,
			            				ComunePartenza,
			            				FermataPartenza,
			            				ComuneArrivo,
			            				FermataArrivo,
			            				CorsaDataPartenza,
			            				CorsaId,
			            				ClienteNome,
			            				ClienteCellulare,
			            				TipoViaggio,
			            				Pax,
			            				Caricati,
			            				Interi,
			            				Ridotti,
			            				NotaCarico,
			            				Tragitto,
			            				DataRitorno,
			            				TipoServizio,
			            				BusId,
			            				BusNumero,
			            				PrenotazioneStato,
			            				CorsaRitornoId,
			            				TotaleImportoPrenotazione,
			            				DataPickup,
			            				DataDropOff,
			            				OraPickup,
			            				OraDropOff,
			            				AutistaId
			            				) SELECT
			            				RT_PreparazioneBus.PrenotazioneId AS PrenotazioneId,
			            				RT_PrenotazioneDettaglio.ComunePartenza AS ComunePartenza,
			            				RT_PrenotazioneDettaglio.FermataPartenza AS FermataPartenza,
			            				RT_PrenotazioneDettaglio.ComuneArrivo AS ComuneArrivo,
			            				RT_PrenotazioneDettaglio.FermataArrivo AS FermataArrivo,
			            				RT_PreparazioneBus.DataPartenza AS DataPartenza,
			            				RT_PreparazioneBus.CorsaId AS CorsaId,
			            				RT_Prenotazione.ClienteNome AS ClienteNome,
			            				RT_Prenotazione.ClienteCellulare AS ClienteCellulare,
			            				RT_PrenotazioneDettaglio.TipoViaggio,
			            				Count(
			            				RT_PrenotazioneDettaglio.PrenotazioneId
			            				) AS TotalePaxPrenotati,
			            				0 AS 'Caricati',
			            				(SELECT count(*) as Interi FROM RT_PrenotazioneDettaglio g where g.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and TipologiaBiglietto like '%Intero%' and DataInizioItinerario='$data' and CorsaInizioItinerario=$corsaId and TipoServizio='Bus') as Interi,
			            				(SELECT count(*) as Ridotti FROM RT_PrenotazioneDettaglio g1 where g1.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and TipologiaBiglietto like '%Ridotto%' and DataInizioItinerario='$data' and CorsaInizioItinerario=$corsaId and TipoServizio='Bus') as Ridotti,
			            				(SELECT Nota
			            				FROM
			            				RT_PrenotazionePercorsoNote
            							INNER JOIN RT_PrenotazionePercorso ON RT_PrenotazionePercorsoNote.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId and RT_PrenotazionePercorsoNote.PrenotazionePercorsoId = RT_PrenotazionePercorso.PrenotazionePercorsoId
			            				WHERE
			            				RT_PrenotazionePercorso.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and
			            				RT_PrenotazionePercorsoNote.TipoNota = 'S' AND
			            				RT_PrenotazionePercorso.CorsaId = $corsaId AND
			            				RT_PrenotazionePercorso.CorsaDataPartenza = '$data' AND
			            				RT_PrenotazionePercorso.Stato = 1 AND
			            					RT_PrenotazionePercorso.Cancella = 0) as NotaCarico,
	            			RT_PrenotazioneDettaglio.Tragitto AS Tragitto,
														(SELECT p1.CorsaDataPartenza FROM RT_PrenotazionePercorso p1 WHERE p1.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and p1.Direzione = 'R') as DataRitorno,
	            			'Bus' AS TipoServizio,
														RT_PreparazioneBus.BusId,
														RT_PreparazioneBus.BusNumero,
														RT_Prenotazione.PrenotazioneStato,
														(SELECT p1.CorsaId FROM RT_PrenotazionePercorso p1 WHERE p1.PrenotazioneId = RT_PreparazioneBus.PrenotazioneId and p1.Direzione = 'R') as CorsaRitornoId,
														RT_ImportoPerPrenotazione.DaIncassare AS TotaleImportoPrenotazione,
														RT_PrenotazioneDettaglio.DataPartenza,
														RT_PrenotazioneDettaglio.DataArrivo,
														RT_PrenotazioneDettaglio.OrarioPartenza,
														RT_PrenotazioneDettaglio.OrarioArrivo,
														$idAutista as AutistaId
														FROM
														((((RT_PreparazioneBus
														INNER JOIN RT_PrenotazioneDettaglio ON (((RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaId) AND (RT_PreparazioneBus.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId))))
														INNER JOIN RT_Prenotazione ON ((RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId))))
														INNER JOIN RT_PrenotazionePercorso ON (((RT_PreparazioneBus.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId) AND (RT_PreparazioneBus.CorsaId = RT_PrenotazionePercorso.CorsaId) AND (RT_PreparazioneBus.DataPartenza = RT_PrenotazionePercorso.CorsaDataPartenza))))
														LEFT JOIN RT_FoglioCaricoCorseElencoModificheDettaglio ON RT_PreparazioneBus.CorsaId = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_FoglioCaricoCorseElencoModificheDettaglio.CorsaDataPartenza AND RT_PreparazioneBus.PrenotazioneId = RT_FoglioCaricoCorseElencoModificheDettaglio.PrenotazioneId
														LEFT JOIN RT_MobileCaricoPasseggeri ON RT_PreparazioneBus.CorsaId = RT_MobileCaricoPasseggeri.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_MobileCaricoPasseggeri.CorsaDataPartenza AND RT_PreparazioneBus.BusId = RT_MobileCaricoPasseggeri.BusId AND RT_PreparazioneBus.BusNumero = RT_MobileCaricoPasseggeri.BusNumero AND RT_Prenotazione.PrenotazioneId = RT_MobileCaricoPasseggeri.PrenotazioneId
														LEFT JOIN RT_ImportoPerPrenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId
														WHERE
														((RT_PrenotazioneDettaglio.TipoServizio = _latin1 'Bus')
														AND ((RT_Prenotazione.PrenotazioneStato = 1) OR (RT_Prenotazione.PrenotazioneStato = 3)))
														AND RT_PreparazioneBus.CorsaId = $corsaId
														AND RT_PreparazioneBus.DataPartenza = '$data'
														AND RT_PreparazioneBus.BusId = $busId
														AND RT_PreparazioneBus.BusNumero = $busNumero
														AND RT_PrenotazioneDettaglio.Escludi = 0
														AND RT_MobileCaricoPasseggeri.IdCaricoPasseggeri IS NULL
														and RT_PreparazioneBus.PrenotazioneId = $prenotazioneId
														GROUP BY
														`RT_PreparazioneBus`.`CorsaId`,
														`RT_PreparazioneBus`.`DataPartenza`,
														`RT_PreparazioneBus`.`BusNumero`,
														`RT_PreparazioneBus`.`BusId`,
														`RT_PrenotazioneDettaglio`.`PrenotazioneId`,
														`RT_PrenotazioneDettaglio`.`ComunePartenza`,
														`RT_PrenotazioneDettaglio`.`DataPartenza`
														ORDER BY
														`RT_Prenotazione`.`ClienteNome`";
						$db->query($sql);
						//               die($sql);
						$sql = "UPDATE RT_MobileCaricoPasseggeri AS t
														INNER JOIN (
														SELECT
														RT_PrenotazionePercorso.ComuneDiscesa,
														RT_PrenotazionePercorso.FermataDiscesa,
														RT_PrenotazionePercorso.DataOraDiscesa,
														RT_PrenotazionePercorso.CorsaId,
														RT_PrenotazionePercorso.CorsaDataPartenza,
														RT_PrenotazionePercorso.PrenotazioneId
														FROM
														RT_MobileCaricoPasseggeri
														INNER JOIN RT_PrenotazionePercorso ON RT_MobileCaricoPasseggeri.PrenotazioneId = RT_PrenotazionePercorso.PrenotazioneId
														AND RT_MobileCaricoPasseggeri.CorsaId = RT_PrenotazionePercorso.CorsaId
														AND RT_MobileCaricoPasseggeri.CorsaDataPartenza = RT_PrenotazionePercorso.CorsaDataPartenza
														WHERE
														RT_PrenotazionePercorso.Cancella = 0
														AND RT_PrenotazionePercorso.Stato = 1 and
														RT_PrenotazionePercorso.CorsaId=$corsaId and
														RT_PrenotazionePercorso.CorsaDataPartenza='$data'
														and RT_PrenotazionePercorso.PrenotazioneId = $prenotazioneId
														) AS t1 ON t.PrenotazioneId = t1.PrenotazioneId
														AND t.CorsaDataPartenza = t1.CorsaDataPartenza
														AND t.CorsaId = t1.CorsaId
														SET t.ComuneArrivoFinale = t1.ComuneDiscesa,
														t.FermataArrivoFinale = t1.FermataDiscesa
														";
						$db->query($sql);
					}
				}
			}
		}
	}


	public function getFermate($busId)
	{
		$db = $this->conn;

		$sql = "SELECT * FROM RT_MobileCarico 
		where BusId = $busId
		order by IdCarico";

		$rows = $db->fetch_array($sql);

		return $rows;
	}

	public function getFermateScarico($corsaId, $corsaDataPartenza, $busId)
	{
		$db = $this->conn;
		$sql = "SELECT *
			FROM RT_MobileScarico
			WHERE BusId = $busId";
		$rows = $db->fetch_array($sql);

		return $rows;
	}

	public function getFermateScambio($corsaId, $corsaDataPartenza, $busId)
	{
		$db = $this->conn;
		$sql = "SELECT *
		FROM RT_MobileScambio
		WHERE BusId = $busId";
		$rows = $db->fetch_array($sql);

		return $rows;
	}

	public function updateFermateCarico($trattaId, $corsaId, $corsaDataPartenza, $arrayFermate, $arrayPasseggeri)
	{
		$db = $this->conn;
		$sql = "DELETE FROM RT_MobileCarico
    			WHERE CorsaDataPartenza = '$corsaDataPartenza' and TrattaId = $trattaId and CorsaId = $corsaId";
		$result = $db->query($sql);

		$sql = "DELETE FROM RT_MobileCaricoPasseggeri
				WHERE CorsaDataPartenza = '$corsaDataPartenza' and TrattaId = $trattaId and CorsaId = $corsaId";
		$result = $db->query($sql);

		foreach ($arrayFermate as $entry) {
			$sql = "INSERT INTO RT_MobileCarico VALUES (null,'" . $entry["FermataNome"] . "', '" . $entry["Comune"] . "','" . $entry["Orario"] . "'," . $entry["FermataId"] . "," . $entry["ComuneId"] . "," . $entry["Pax"] . "," . $entry["Caricati"] . "," . $entry["CorsaId"] . ",'" . $entry["CorsaDataPartenza"] . "'," . $entry["TrattaId"] . "," . $entry["DaModificare"] . ")";
			$result = $db->query($sql);
		}

		foreach ($arrayPasseggeri as $temp) {
			foreach ($temp as $entry) {
				$temp = $rows[0]['CorsaRitornoId'];
				if ($rows[0]['CorsaRitornoId'] == "") {
					$temp = "null";
				}
				$sql = "INSERT INTO RT_MobileCaricoPasseggeri VALUES (null," . $entry['PrenotazioneId'] . ",'" . $entry['ComunePartenza'] . "','" .
					$entry['FermataPartenza'] . "','" . $entry['ComuneArrivo'] . "','" . $entry['FermataArrivo'] . "'," . $entry['FermataId'] . "," . $entry['TrattaId'] . ",'" .
					$entry['CorsaDataPartenza'] . "'," . $entry['CorsaId'] . ",'" . $entry['ClienteNome'] . "','" . $entry['ClienteCellulare'] . "','" .
					$entry['TipoViaggio'] . "'," . $entry['Pax'] . "," . $entry['Caricati'] . ",'" . $entry['Tragitto'] . "','" .
					$entry['DataRitorno'] . "'," . $entry['Interi'] . "," . $entry['Ridotti'] . ",'" . $entry['NotaCarico'] . "'," . $entry['PrenotazioneStato'] . "," . $temp . ")";
				$result = $db->query($sql);
			}
		}
		return true;
	}

	public function updateFermateScarico($trattaId, $corsaId, $corsaDataPartenza, $arrayFermate, $arrayPasseggeri)
	{
		$db = $this->conn;
		$sql = "DELETE FROM RT_MobileScarico
		WHERE CorsaDataPartenza = '$corsaDataPartenza' and TrattaId = $trattaId and CorsaId = $corsaId";
		$result = $db->query($sql);

		$sql = "DELETE FROM RT_MobileScaricoPasseggeri
		WHERE CorsaDataPartenza = '$corsaDataPartenza' and TrattaId = $trattaId and CorsaId = $corsaId";
		$result = $db->query($sql);

		foreach ($arrayFermate as $entry) {
			$sql = "INSERT INTO RT_MobileScarico VALUES (null,'" . $entry["FermataNome"] . "', '" . $entry["Comune"] . "','" . $entry["Orario"] . "'," . $entry["FermataId"] . "," . $entry["ComuneId"] . "," . $entry["Pax"] . "," . $entry["Scaricati"] . "," . $entry["CorsaId"] . ",'" . $entry["CorsaDataPartenza"] . "'," . $entry["TrattaId"] . ")";
			$result = $db->query($sql);
		}

		foreach ($arrayPasseggeri as $temp) {
			foreach ($temp as $entry) {

				$sql = "INSERT INTO RT_MobileScaricoPasseggeri VALUES (null," . $entry['PrenotazioneId'] . "," . $entry['FermataId'] . "," . $entry['TrattaId'] . ",'" .
					$entry['CorsaDataPartenza'] . "'," . $entry['CorsaId'] . ",'" . $entry['ClienteNome'] . "','" . $entry['ClienteCellulare'] . "','" .
					$entry['TipoViaggio'] . "'," . $entry['Pax'] . "," . $entry['Scaricati'] . ",'" . $entry['NotaScarico'] . "')";
				$result = $db->query($sql);
			}
		}
		return true;
	}

	public function resetFermateCarico($trattaId, $corsaId, $corsaDataPartenza)
	{
		$db = $this->conn;
		$sql = "DELETE FROM RT_MobileCarico
		WHERE CorsaDataPartenza = '$corsaDataPartenza' and TrattaId = $trattaId and CorsaId = $corsaId";
		$result = $db->query($sql);

		$sql = "DELETE FROM RT_MobileCaricoPasseggeri
		WHERE CorsaDataPartenza = '$corsaDataPartenza' and TrattaId = $trattaId and CorsaId = $corsaId";
		$result = $db->query($sql);

		return true;
	}

	public function resetFermateScarico($trattaId, $corsaId, $corsaDataPartenza)
	{
		$db = $this->conn;
		$sql = "DELETE FROM RT_MobileScarico
		WHERE CorsaDataPartenza = '$corsaDataPartenza' and TrattaId = $trattaId and CorsaId = $corsaId";
		$result = $db->query($sql);

		$sql = "DELETE FROM RT_MobileScaricoPasseggeri
		WHERE CorsaDataPartenza = '$corsaDataPartenza' and TrattaId = $trattaId and CorsaId = $corsaId";
		$result = $db->query($sql);

		return true;
	}

	public function getPasseggeriFermata($busId, $idCarico)
	{
		$db = $this->conn;

		$sql = "SELECT * from RT_MobileCaricoPasseggeri 
			WHERE BusId = $busId and IdCarico =$idCarico;";
		$passeggeri = $db->fetch_array($sql);
		$count = 0;
		foreach ($passeggeri as $p) {
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and n.TipoNota = 'D';";
			$rowsTemp = $db->fetch_array($sql);
			$passeggeri[$count]['NotaDiscesa'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneId'] . " and n.TipoNota = 'B';";
			$rowsTemp = $db->fetch_array($sql);
			$passeggeri[$count]['NotaBiglietto'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneId'] . " and n.TipoNota = 'P';";
			$rowsTemp = $db->fetch_array($sql);
			$passeggeri[$count]['NotaPosti'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneId'] . " and n.TipoNota = 'G';";
			$rowsTemp = $db->fetch_array($sql);
			$passeggeri[$count]['NotaGenerica'] = $rowsTemp[0]['Nota'];

			$count++;
		}

		return $passeggeri;
	}

	public function getPasseggeriFermataScarico($corsaId, $corsaDataPartenza, $busId, $idScarico)
	{
		$db = $this->conn;
		$sql = "select * from RT_MobileScaricoPasseggeri 
				where BusId = $busId and IdScarico = $idScarico";

		$rows = $db->fetch_array($sql);

		$count = 0;
		foreach ($rows as $p) {
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and TipoNota = 'D';";
			$rowsTemp = $db->fetch_array($sql);
			$rows[$count]['NotaDiscesa'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and TipoNota = 'B';";
			$rowsTemp = $db->fetch_array($sql);
			$rows[$count]['NotaBiglietto'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and TipoNota = 'P';";
			$rowsTemp = $db->fetch_array($sql);
			$rows[$count]['NotaPosti'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and TipoNota = 'G';";
			$rowsTemp = $db->fetch_array($sql);
			$rows[$count]['NotaGenerica'] = $rowsTemp[0]['Nota'];

			$count++;
		}

		return $rows;
	}
	public function getPasseggeriFermataScambio($corsaId, $corsaDataPartenza, $busId, $idScambio)
	{
		$db = $this->conn;
		$sql = "select * from RT_MobileScambioPasseggeri
		where BusId = $busId and IdScambio = $idScambio";
		$rows = $db->fetch_array($sql);

		$count = 0;
		foreach ($rows as $p) {
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and TipoNota = 'D';";
			$rowsTemp = $db->fetch_array($sql);
			$rows[$count]['NotaDiscesa'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and TipoNota = 'B';";
			$rowsTemp = $db->fetch_array($sql);
			$rows[$count]['NotaBiglietto'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and TipoNota = 'P';";
			$rowsTemp = $db->fetch_array($sql);
			$rows[$count]['NotaPosti'] = $rowsTemp[0]['Nota'];
			$sql = "SELECT n.Nota FROM RT_PrenotazionePercorsoNote n
					left join RT_PrenotazioneNumero p on  p.PrenotazioneId = n.PrenotazioneId
					where p.PrenotazioneNumero = " . $p['PrenotazioneNumero'] . " and TipoNota = 'G';";
			$rowsTemp = $db->fetch_array($sql);
			$rows[$count]['NotaGenerica'] = $rowsTemp[0]['Nota'];

			$count++;
		}

		return $rows;
	}



	public function restoreFermateScarico($trattaId, $corsaId, $corsaDataPartenza, $tipoServizio, $busId, $busNumero)
	{
		$db = $this->conn;

		$sql = "SELECT * from RT_MobileScarico WHERE CorsaDataPartenza = '$corsaDataPartenza' and BusId = $busId and BusNumero = $busNumero and TipoServizio='$tipoServizio' and CorsaId = $corsaId";

		// 		echo "<br>$sql<br>";
		$rows = $db->fetch_array($sql);
		$passeggeri = array();
		$ii = 0;
		foreach ($rows as $entry) {

			$comuneDestinazione =  addslashes($entry['Comune']);
			$fermataDestinazione =  addslashes($entry['FermataNome']);

			$sql = "SELECT * from RT_MobileScaricoPasseggeri WHERE CorsaDataPartenza = '$corsaDataPartenza' and BusId = $busId and BusNumero=$busNumero and CorsaId = $corsaId and FermataNome='$fermataDestinazione' and ComuneDestinazione ='$comune';";
			$passeggeri[$ii] = $db->fetch_array($sql);
			$ii++;
		}

		$result['fermate'] = $rows;
		$result['passeggeri'] = $passeggeri;

		return $result;
	}

	public function restoreBigliettazione($trattaId, $corsaId, $corsaDataPartenza, $ordine, $busId, $busNumero)
	{
		$db = $this->conn;
		if ($ordine == 1) {
			$sql = "SELECT mp.*, b.*, pp.Posto FROM RT_MobileBigliettazione b 
				LEFT JOIN RT_MobileCaricoPasseggeri mp ON 
				( mp.PrenotazioneId = b.PrenotazioneId AND mp.CorsaId = b.CorsaId AND mp.CorsaDataPartenza = b.CorsaDataPartenza )
				LEFT JOIN RT_PrenotazionePosto pp ON 
				(pp.PrenotazioneId =mp.PrenotazioneId AND pp.CorsaId = mp.CorsaId AND pp.DataPartenza = mp.CorsaDataPartenza)
				WHERE b.CorsaDataPartenza = '$corsaDataPartenza' 
				AND b.BusId = $busId AND b.CorsaId = $corsaId 
				AND b.BusNumero = $busNumero 
				AND mp.Caricati = 1
				group by pp.PrenotazioneId 
				order by pp.Posto";
		} else {
			$sql = "SELECT mp.*, b.*, pp.Posto FROM RT_MobileBigliettazione b
				LEFT JOIN RT_MobileCaricoPasseggeri mp ON
				( mp.PrenotazioneId = b.PrenotazioneId AND mp.CorsaId = b.CorsaId AND mp.CorsaDataPartenza = b.CorsaDataPartenza )
				LEFT JOIN RT_PrenotazionePosto pp ON
				(pp.PrenotazioneId =mp.PrenotazioneId AND pp.CorsaId = mp.CorsaId AND pp.DataPartenza = mp.CorsaDataPartenza)
				WHERE b.CorsaDataPartenza = '$corsaDataPartenza'
				AND b.BusId = $busId AND b.CorsaId = $corsaId
				AND b.BusNumero = $busNumero
				AND mp.Caricati = 1
				group by pp.PrenotazioneId
				order by b.ClienteNome";
		}

		$rows = $db->fetch_array($sql);
		return $rows;
	}

	public function restoreValidazione($trattaId, $corsaId, $corsaDataPartenza, $ordine, $busId, $busNumero)
	{
		$db = $this->conn;

		$sql = "SELECT * from RT_MobileValidazione v 
			left join RT_MobileCaricoPasseggeri mp on (mp.PrenotazioneId = v.PrenotazioneId and mp.CorsaId = v.CorsaId and mp.BusId = v.BusId and mp.BusNumero = v.BusNumero and mp.TipoServizio = v.TipoServizio) 
			WHERE v.CorsaDataPartenza = '$corsaDataPartenza'
			AND v.BusId = $busId
			AND v.CorsaId = $corsaId
			AND v.BusNumero = $busNumero
			AND v.TipoServizio = 'Bus'
			AND mp.Caricati = 1 ";

		if ($ordine == 1) {
			$sql = "SELECT * from RT_MobileValidazione v 
			left join RT_MobileCaricoPasseggeri mp on (mp.PrenotazioneId = v.PrenotazioneId and mp.CorsaId = v.CorsaId and mp.BusId = v.BusId and mp.BusNumero = v.BusNumero and mp.TipoServizio = v.TipoServizio) 
			LEFT JOIN RT_PrenotazionePosto pp ON (pp.PrenotazioneId =mp.PrenotazioneId AND pp.CorsaId = mp.CorsaId AND pp.DataPartenza = mp.CorsaDataPartenza) 
			WHERE v.CorsaDataPartenza = '$corsaDataPartenza'
			AND v.BusId = $busId
			AND v.CorsaId = $corsaId
			AND v.BusNumero = $busNumero
			AND v.TipoServizio = 'Bus'
			AND mp.Caricati = 1 
			group by pp.PrenotazioneId order by pp.Posto";
		} else {
			$sql = "SELECT * from RT_MobileValidazione v
			left join RT_MobileCaricoPasseggeri mp on (mp.PrenotazioneId = v.PrenotazioneId and mp.CorsaId = v.CorsaId and mp.BusId = v.BusId and mp.BusNumero = v.BusNumero and mp.TipoServizio = v.TipoServizio)
			LEFT JOIN RT_PrenotazionePosto pp ON (pp.PrenotazioneId =mp.PrenotazioneId AND pp.CorsaId = mp.CorsaId AND pp.DataPartenza = mp.CorsaDataPartenza)
			WHERE v.CorsaDataPartenza = '$corsaDataPartenza'
			AND v.BusId = $busId
			AND v.CorsaId = $corsaId
			AND v.BusNumero = $busNumero
			AND v.TipoServizio = 'Bus'
			AND mp.Caricati = 1
			group by pp.PrenotazioneId order by v.ClienteNome";
		}
		$rows = $db->fetch_array($sql);

		return $rows;
	}


	public function getBigliettiIncassare($corsaId, $corsaDataPartenza, $ordine, $busId, $busNumero, $trattaId, $autistaId)
	{
		$db = $this->conn;
		$sql = "SELECT
					*,
				TotaleImportoPrenotazione as Importo 
				FROM
					RT_MobileCaricoPasseggeri
				WHERE
					Caricati = 1 and PrenotazioneStato = 1 
				AND CorsaId = $corsaId
				AND CorsaDataPartenza = '$corsaDataPartenza'
				AND BusId = $busId
				AND BusNumero = $busNumero
				AND TipoServizio = 'Bus'";


		if ($ordine == 1) {
			$sql .= " order by DataPickup,OraPickup";
		} else {
			$sql .= " order by ClienteNome";
		}
		$rows = $db->fetch_array($sql);
		for ($ii = 0; $ii < count($rows); $ii++) {
			$sql = "Select * from RT_MobileBigliettazione where PrenotazioneId = " . $rows[$ii]['PrenotazioneId'] . " and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus'";
			$tempRes = $db->fetch_array($sql);
			if (count($tempRes) == 0) {
				$rows[$ii]['Incassato'] = 0;
				$rows[$ii]['Pagamento'] = "";
				$rows[$ii]['Conferma'] = "";
				$fermata = $rows[$ii]['FermataPartenza'];
				$comune = $rows[$ii]['ComunePartenza'];
				$sql = "SELECT FermataId, FermataPeso FROM RT_Fermata f left join Comune c on (c.ComuneId = f.ComuneId) where f.FermataNome = '$fermata' and c.Comune = '$comune' and f.TrattaId = $trattaId;";
				$temp = $db->fetch_array($sql);
				$rows[$ii]['FermataPeso'] = $temp[0]['FermataPeso'];
				$rows[$ii]['FermataId'] = $temp[0]['FermataId'];
				$this->updateIncasso($busId, $busNumero, $corsaId, $corsaDataPartenza, $rows[$ii], $trattaId, $autistaId);
			}
		}

		return $rows;
	}

	public function getBigliettiValidare($trattaId, $corsaId, $corsaDataPartenza, $ordine, $busId, $busNumero, $autistaId, $tipoServizio)
	{
		$db = $this->conn;

		if ($ordine == 1) {
			$sql = "SELECT mp.*, pp.Posto FROM RT_MobileCaricoPasseggeri mp
				LEFT JOIN RT_PrenotazionePosto pp ON 
				(pp.PrenotazioneId =mp.PrenotazioneId AND pp.CorsaId = mp.CorsaId AND pp.DataPartenza = mp.CorsaDataPartenza)
				WHERE mp.Caricati = 1 
				AND mp.CorsaId = $corsaId 
				AND mp.CorsaDataPartenza = '$corsaDataPartenza' 
				AND mp.BusId = $busId 
				AND mp.BusNumero = $busNumero 
				AND mp.TipoServizio = '$tipoServizio'
				group by pp.PrenotazioneId 
				order by pp.Posto";
		} else {
			$sql = "SELECT
				*
				FROM
				RT_MobileCaricoPasseggeri
				WHERE
				Caricati = 1 
				AND CorsaId = $corsaId
				AND CorsaDataPartenza = '$corsaDataPartenza'
				AND BusId = $busId
				AND BusNumero = $busNumero
				AND TipoServizio = '$tipoServizio' order by ClienteNome";
		}
		$rows = $db->fetch_array($sql);

		for ($ii = 0; $ii < count($rows); $ii++) {
			$sql = "Select * from RT_MobileValidazione where PrenotazioneId = " . $rows[$ii]['PrenotazioneId'] . " and BusId = $busId and BusNumero = $busNumero and TipoServizio = '$tipoServizio'";
			$tempRes = $db->fetch_array($sql);
			if (count($tempRes) == 0) {
				$rows[$ii]['Validato'] = 0;
				$rows[$ii]['InteriV'] = 0;
				$rows[$ii]['RidottiV'] = 0;

				$fermata = $rows[$ii]['FermataPartenza'];
				$comune = $rows[$ii]['ComunePartenza'];
				$sql = "SELECT FermataId, FermataPeso FROM RT_Fermata f left join Comune c on (c.ComuneId = f.ComuneId) where f.FermataNome = '$fermata' and c.Comune = '$comune' and f.TrattaId = $trattaId;";
				$temp = $db->fetch_array($sql);
				$rows[$ii]['FermataPeso'] = $temp[0]['FermataPeso'];
				$rows[$ii]['FermataId'] = $temp[0]['FermataId'];

				$this->updateValidazione($busId, $busNumero, $corsaId, $corsaDataPartenza, $rows[$ii], $trattaId, $autistaId, $tipoServizio);
			}
		}

		return $rows;
	}

	public function updateIncasso($busId, $busNumero, $corsaId, $corsaDataPartenza, $prenotazione, $trattaId, $autistaId)
	{
		$db = $this->conn;
		$sql = "SELECT * FROM RT_MobileBigliettazione WHERE BusId = $busId  and BusNumero = $busNumero and  
				CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza' and PrenotazioneId = " . $prenotazione['PrenotazioneId'];
		$rows = $db->fetch_array($sql);
		if (count($rows) > 0) {
			$sql = "UPDATE RT_MobileBigliettazione
					SET Incassato = 1, Pagamento = '" . $prenotazione['Pagamento'] . "', Conferma = '" . $prenotazione['Conferma'] . "' and AutistaId = $autistaId
					WHERE BusId = $busId  and BusNumero = $busNumero  and
					CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza' and PrenotazioneId =" . $prenotazione['PrenotazioneId'];
			$result = $db->query($sql);
			$sql = "UPDATE RT_MobileCaricoPasseggeri
					SET Caricati = 1, PrenotazioneStato = 3
					WHERE BusId = $busId  and BusNumero = $busNumero  and TipoServizio = 'Bus' and
					CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza' and PrenotazioneId =" . $prenotazione['PrenotazioneId'];
			$result = $db->query($sql);
			// 			$sql = "UPDATE RT_MobileCarico
			// 				SET Caricati = Caricati + ".$prenotazione['Pax']."
			// 				WHERE BusId = $busId  and BusNumero = $busNumero  and TipoServizio = 'Bus' and
			// 				CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza'";
			// 			$result = $db->query($sql);
		} else {
			$sql = "INSERT INTO RT_MobileBigliettazione VALUES (null," . $prenotazione['PrenotazioneId'] . "," .
				$trattaId . ",'" . $prenotazione['CorsaDataPartenza'] . "'," . $prenotazione['CorsaId'] . ",'" .
				$prenotazione['ClienteNome'] . "','" . $prenotazione['ClienteCellulare'] . "'," . $prenotazione['Pax'] . ",'" .
				$prenotazione['Importo'] . "'," . $prenotazione['Incassato'] . "," . $prenotazione['FermataId'] . "," .
				$prenotazione['FermataPeso'] . ",'" . $prenotazione['Pagamento'] . "','" . $prenotazione['Conferma'] . "'," . $prenotazione['BusId'] . ", " . $prenotazione['BusNumero'] . ",'" . $prenotazione['TipoServizio'] . "'," . $autistaId . ")";
			$result = $db->query($sql);
		}
		return $result;
	}

	public function updateValidazione($busId, $busNumero, $corsaId, $corsaDataPartenza, $prenotazione, $trattaId, $autistaId, $tipoServizio)
	{
		$db = $this->conn;
		$sql = "SELECT * FROM RT_MobileValidazione WHERE BusId = $busId  and BusNumero = $busNumero and 
		CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza' and PrenotazioneId = " . $prenotazione['PrenotazioneId'] . " and TipoServizio = '$tipoServizio'";
		$rows = $db->fetch_array($sql);
		if (count($rows) > 0) {
			$sql = "UPDATE RT_MobileValidazione
					SET Validato = 1,
						InteriV = " . $prenotazione['InteriV'] . ", RidottiV = " . $prenotazione['RidottiV'] . "
						WHERE BusId = $busId  and BusNumero = $busNumero  and
						CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza' and PrenotazioneId =" . $prenotazione['PrenotazioneId'];
			$result = $db->query($sql);
		} else {


			$sql = "INSERT INTO RT_MobileValidazione VALUES (null," . $prenotazione['PrenotazioneId'] . "," .
				$trattaId . ",'" . $prenotazione['CorsaDataPartenza'] . "'," . $prenotazione['CorsaId'] . ",'" .
				$prenotazione['ClienteNome'] . "','" . $prenotazione['ClienteCellulare'] . "'," . $prenotazione['Pax'] . "," .
				$prenotazione['Validato'] . "," . $prenotazione['FermataId'] . "," .
				$prenotazione['FermataPeso'] . "," . $prenotazione['Interi'] . "," . $prenotazione['Ridotti'] . "," . $prenotazione['InteriV'] . "," . $prenotazione['RidottiV'] . "," . $prenotazione['BusId'] . "," . $prenotazione['BusNumero'] . ",'" . $prenotazione['TipoServizio'] . "'," . $autistaId . ")";
			$result = $db->query($sql);
		}
		return $result;
	}

	public function updateCaricobyQRcode($busId, $corsaId, $corsaDataPartenza, $qrcode)
	{
		$db = $this->conn;
		$sql = "select * FROM RT_PrenotazioneNumero where CodiceQrcode = '$qrcode'";
		$rowsP = $db->query_first($sql);
		$numero = $rowsP['PrenotazioneNumeroId'];

		$sql = "SELECT * FROM RT_MobileCaricoPasseggeri WHERE BusId = $busId  and PrenotazioneNumero = $numero";
		$rowsD = $db->query_first($sql);
		$result2 = 1;
		if (isset($rowsD['PrenotazioneNumero']) && $rowsD['Caricato'] != 1) {
			$result2 = 0;
			$lastValue = $rows['Caricato'];
			$sql = "UPDATE RT_MobileCaricoPasseggeri
				SET Caricato = 1
				WHERE BusId = $busId  and PrenotazioneNumero = $numero";
			$result = $db->query($sql);

			$sql = "Select * from RT_MobileCarico where BusId = $busId and Comune = '" . $rowsD['ComunePartenza'] . "'";
			$rows = $db->fetch_array($sql);

			if ($lastValue != 0) {
				$sql = "UPDATE RT_MobileCarico ";
				if ($lastValue == 1) {
					$caricati = $rows[0]['Caricati'] - 1;
					$sql .= "set Caricati = $caricati";
				} else if ($lastValue == 3) {
					$assenti = $rows[0]['Assenti'] - 1;
					$sql .= "set Assenti = $assenti";
				}
				$sql .= " WHERE BusId = $busId and Comune = '" . $rowsD['ComunePartenza'] . "'";
				$result = $db->query($sql);
			}
			$caricati = $rows[0]['Caricati'] + 1;
			$sql = "UPDATE RT_MobileCarico set Caricati = $caricati
			WHERE BusId = $busId and Comune = '" . $rowsD['ComunePartenza'] . "'";
			$result = $db->query($sql);
		}

		return $result2;
	}

	public function updateCarico($busId, $corsaId, $corsaDataPartenza, $prenotazione, $lastValue)
	{
		$db = $this->conn;
		$sql = "SELECT * FROM RT_MobileCarico WHERE BusId = $busId  and IdCarico = " . $prenotazione['IdCarico'];

		$rows = $db->fetch_array($sql);

		$sql = "UPDATE RT_MobileCaricoPasseggeri
				SET Caricato = " . $prenotazione['Caricato'] . "
						WHERE BusId = $busId  and PrenotazioneNumero = " . $prenotazione['PrenotazioneNumero'];
		$result = $db->query($sql);

		if ($lastValue != 0) {
			$sql = "UPDATE RT_MobileCarico ";
			if ($lastValue == 1) {
				$caricati = $rows[0]['Caricati'] - 1;
				$sql .= "set Caricati = $caricati";
			} else if ($lastValue == 3) {
				$assenti = $rows[0]['Assenti'] - 1;
				$sql .= "set Assenti = $assenti";
			}
			$sql .= " WHERE BusId = $busId and IdCarico = " . $prenotazione['IdCarico'];

			$result = $db->query($sql);
		}

		$sql = "UPDATE RT_MobileCarico ";
		if ($prenotazione['Caricato'] == 1) {
			$caricati = $rows[0]['Caricati'] + 1;
			$sql .= "set Caricati = $caricati";
		} else if ($prenotazione['Caricato'] == 3) {
			$assenti = $rows[0]['Assenti'] + 1;
			$sql .= "set Assenti = $assenti";
		}
		$sql .= " WHERE BusId = $busId and IdCarico = " . $prenotazione['IdCarico'];
		$result = $db->query($sql);

		return true;
	}

	public function updateCaricoZero($busId, $corsaId, $corsaDataPartenza, $prenotazione, $lastValue)
	{
		$db = $this->conn;
		$sql = "SELECT * FROM RT_MobileCarico WHERE BusId = $busId  and IdCarico = " . $prenotazione['IdCarico'];

		$rows = $db->fetch_array($sql);

		$sql = "UPDATE RT_MobileCaricoPasseggeri
				SET Caricato = " . $prenotazione['Caricato'] . "
					WHERE BusId = $busId  and PrenotazioneNumero = " . $prenotazione['PrenotazioneNumero'];

		$result = $db->query($sql);

		$sql = "UPDATE RT_MobileCarico ";
		if ($lastValue == 1) {
			$caricati = $rows[0]['Caricati'] - 1;
			$sql .= "set Caricati = $caricati";
		} else if ($lastValue == 3) {
			$assenti = $rows[0]['Assenti'] - 1;
			$sql .= "set Assenti = $assenti";
		}
		$sql .= " WHERE BusId = $busId and IdCarico = " . $prenotazione['IdCarico'];

		$result = $db->query($sql);

		return true;
	}

	public function updateScarico($busId, $corsaId, $corsaDataPartenza, $prenotazione)
	{
		$db = $this->conn;
		$sql = "SELECT * FROM RT_MobileScarico WHERE BusId = $busId and IdScarico = " . $prenotazione['IdScarico'];

		$rows = $db->fetch_array($sql);

		$sql = "SELECT Scaricato FROM RT_MobileScaricoPasseggeri WHERE BusId = $busId and IdScarico = " . $prenotazione['IdScarico'];
		$rowsT = $db->fetch_array($sql);
		if ($rowsT[0]['Scaricato'] == 0) {

			$sql = "UPDATE RT_MobileScaricoPasseggeri
					SET Scaricato = 1
							WHERE BusId = $busId and PrenotazioneNumero =" . $prenotazione['PrenotazioneNumero'];
			$result = $db->query($sql);

			$caricati = $rows[0]['Scaricati'] + 1;
			$sql = "UPDATE RT_MobileScarico 
				SET Scaricati = $caricati 
				WHERE IdScarico = " . $prenotazione['IdScarico'];
			$result = $db->query($sql);
		} else {
			$result = 1;
		}

		return $result;
	}

	public function updateScaricoZero($busId, $corsaId, $corsaDataPartenza, $prenotazione)
	{
		$db = $this->conn;
		$sql = "SELECT * FROM RT_MobileScarico WHERE BusId = $busId  and IdScarico = " . $prenotazione['IdScarico'];

		$rows = $db->fetch_array($sql);


		$sql = "SELECT Scaricato FROM RT_MobileScaricoPasseggeri WHERE BusId = $busId and IdScarico = " . $prenotazione['IdScarico'];
		$rowsT = $db->fetch_array($sql);
		if ($rowsT[0]['Scaricato'] == 1) {

			$sql = "UPDATE RT_MobileScaricoPasseggeri
			SET Scaricato = 0
			WHERE BusId = $busId  and PrenotazioneNumero =" . $prenotazione['PrenotazioneNumero'];
			$result = $db->query($sql);

			$caricati = $rows[0]['Scaricati'] - 1;
			$sql = "UPDATE RT_MobileScarico
			SET Scaricati = $caricati
			WHERE IdScarico = " . $prenotazione['IdScarico'];
			$result = $db->query($sql);
		} else {
			$result = 1;
		}

		return $result;
	}


	public function existQrcode($codice, $corsaId, $dataCorsa, $busId, $busNumero, $tipoServizio)
	{
		$db = $this->conn;
		$sql = "SELECT * FROM RT_MobileQrcode WHERE Codice = '$codice' and CorsaId = $corsaId and DataCorsa = '$dataCorsa' and BusId = $busId
		and BusNumero = $busNumero and TipoServizio='$tipoServizio'";
		$rows = $db->fetch_array($sql);

		if (count($rows) == 0) {
			$sql = "INSERT INTO RT_MobileQrcode VALUES (null,'" . $codice . "', $corsaId, '$dataCorsa', $busId, $busNumero, '$tipoServizio' )";
			$result = $db->query($sql);
			return 0;
		} else {
			return 1;
		}
	}

	public function getPrenotazioniModifica($trattaId, $corsaId, $corsaDataPartenza, $tipoServizio, $busId, $busNumero)
	{
		$db = $this->conn;
		$sql = "SELECT * FROM RT_MobileCaricoPasseggeri 
				WHERE Caricati = 2 and CorsaId = $corsaId and BusId=$busId and TipoServizio='$tipoServizio' and BusNumero=$busNumero and CorsaDataPartenza = '$corsaDataPartenza'";
		$rows = $db->fetch_array($sql);
		$count = 0;
		foreach ($rows as $row) {
			$sql = "select ComuneSalita as ComuneSalitaFinale, FermataSalita  from RT_PrenotazionePercorso where PrenotazioneId = " . $row['PrenotazioneId'];
			$rowTemp = $db->fetch_array($sql);
			$rows[$count]['ComuneSalitaFinale'] = $rowTemp[0]['ComuneSalitaFinale'];
			$rows[$count]['FermataSalitaFinale'] = $rowTemp[0]['FermataSalita'];
			$count++;
		}
		return $rows;
	}

	public function getCorseRitornoModifica($ComunePartenzaId, $ComuneDestinazioneId, $DataCorsa, $CorsaId_post = null, $DataCorsa_post = null)
	{
		$db = $this->conn;

		//data inizio ricerca
		$dataInizio = $DataCorsa;

		/*recupero dati se sto modificando/visualizzando una prenotazione*/
		$ComuneSalitaId_post = 0;
		$ComuneDiscesaId_post = 0;
		$CorsaPrenotata_post = 0;
		$DataPrenotata_post = null;
		if (isset($CorsaId_post) && $CorsaId_post > 0) {
			$ComuneSalitaId_post = $ComunePartenzaId;
			$ComuneDiscesaId_post = $ComuneDestinazioneId;
			$CorsaPrenotata_post = $CorsaId_post;
			$DataPrenotata_post = $DataCorsa_post;
		}


		$pieces = explode("-", $dataInizio);
		$from_unix_time = mktime(0, 0, 0, $pieces[1], $pieces[2], $pieces[0]);
		$day_before = strtotime("-1 day", $from_unix_time);
		$day_before_formatted = date('Y-m-d', $day_before);
		$dateRicerca[] = $day_before_formatted;

		$day = strtotime("+0 day", $from_unix_time);
		$dateRicerca[] = date('Y-m-d', $day);

		$day_after = strtotime("+1 day", $from_unix_time);
		$day_after_formatted = date('Y-m-d', $day_after);
		$dateRicerca[] = $day_after_formatted;

		$aColumns = array('CorsaId', 'CorsaNome', 'LineaNome', 'DataPartenzaFormattata', 'AppSettimanaGiornoDescr', 'OrarioPartenza', 'AppCalendarioData', 'LineaId');
		/*applicazione delle regole di filtraggio e ricerca*/
		$sOrder = "";
		$sWhere = " ";

		/*non + prevista la vendita oltr l'orario previsto*/
		$flag_stop_vendite = 0;
		$sede->conn = $db;
		if ($flag_stop_vendite == 0)
			$sWhere .= " timediff(addtime(RT_AppCalendario.AppCalendarioData, RT_Corsa.OrarioPartenza), now()) > 0 ";

		//recupero delle corse per data
		$output = array(
			"sEcho" => intval($_GET['sEcho']),
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

						$sql = "SELECT * FROM RT_CorsaBlocco WHERE CorsaId = $cId AND DataPartenza = '$dId'";
						$tempRow = $db->fetch_array($sql);
						if (count($tempRow) > 0) {
							//corsa bloccata
							$visualizza = false;
						} else {
							//corsa non bloccata
							if (($cId == $CorsaPrenotata_post)  and ($ComunePartenzaId == $ComuneSalitaId_post)  and ($dId == $DataPrenotata_post) and ($ComuneDestinazioneId == $ComuneDiscesaId_post)) {
								//caso in cui si � una corsa gi� selezionata nella prenotazine la visualizzo direttamente
								$visualizza = true;
								//controllo presenza della corsa nella tabella percorso breve
								$trattaPartenza = null;
								$trattaArrivo = null;
								$sql = "select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$CorsaPrenotata_post";
								$r = $db->query_first($sql);
								if (!empty($r['PercorsoBreveId'])) {
									$trattaPartenza = $r['TrattaPickupId'];
									$trattaArrivo = $r['TrattaDropOffId'];
									$visualizza = true;
								} else {
									$grafo = new GrafoTratte($LineaId_post, $CorsaPrenotata_post, $db, $ComunePartenzaId, $ComuneDestinazioneId);
									$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
									$pre = new Prenotazione();
									$pre->conn = $db;
									$ritorno = $pre->CreatePercorsoBreve($ComunePartenzaId, $ComuneDestinazioneId, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $CorsaPrenotata_post, $LineaId_post);
								}
							} else {
								// caso in cui � una corsa non selezionata
								$trattaPartenza = null;
								$trattaArrivo = null;
								//controllo se le fermate sono attive
								$sql = "select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$cId";
								$r = $db->query_first($sql);

								if (!empty($r['PercorsoBreveId'])) {
									$trattaPartenza = $r['TrattaPickupId'];
									$trattaArrivo = $r['TrattaDropOffId'];
									$visualizza = true;
								} else {
									$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId);
									$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
									$pre = new Prenotazione();
									$pre->conn = $db;
									$ritorno = $pre->CreatePercorsoBreve($ComunePartenzaId, $ComuneDestinazioneId, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $cId, $lId);
								}

								if (isset($trattaPartenza) && isset($trattaArrivo)) {
									$sql = "SELECT distinct FermataId,FermataOrario,TrattaNome From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaId=$trattaPartenza and TrattaStato=1 order by TrattaPeso desc ";
									$arr_fermate = $db->fetch_array($sql);
									$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId and TrattaId=$trattaArrivo  and TrattaStato=1 order by TrattaPeso asc";
									$arr_fermate_d = $db->fetch_array($sql);
									if ((sizeof($arr_fermate) > 0) and (sizeof($arr_fermate_d) > 0)) {
										$visualizza = true;
									} else {
										$visualizza = false;
									}
								} else {
									$visualizza = false;
								}

								//controllo se la tratta � vendibile
								$sql = "SELECT TratteNonVendibiliId from RT_TratteNonVendibili  WHERE ComunePickUpId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId  ";
								$arr_esclusi = $db->fetch_array($sql);
								if ((sizeof($arr_esclusi) > 0)) {
									$visualizza = false;
								}
							}
						}

						/*se la corsa � visualizzabile l'aggiungo alla lista*/
						if ($visualizza) {
							// 							$row = $corsa;
							// 							for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
							// 								if ( $aColumns[$i] == "CorsaNome") {
							// 									$row[] = utf8_decode($corsa[ 'CorsaNome' ]);
							// 								} else if ( $aColumns[$i] == "CorsaId") {
							// 									$CorsaId=$corsa['CorsaId'];
							// 									$DataPartenza=$corsa['AppCalendarioData'];

							// 									$dataodierna=Date('Y-m-d');
							// 									$dt=new DT();
							// 									$diff=$dt->compare($dataodierna,$DataPartenza,'Y-m-d');

							// 									$OrarioPartenza=$corsa['OrarioPartenza'];
							// 									$name_input="Corsa[$CorsaId.]";
							// 									$arr_field=$Tp."_".$CorsaId."_".$DataPartenza;
							// 									$arr_field="";

							// 									/* General output */
							// 									$disponibili=$corsa['PostiCorsaDefault']+$corsa['PostiCorsaAggiunti']-$corsa['PostiRealmentePrenotati'];
							// 									// 								if (($disponibili>0) or ($CorsaId_post==$CorsaId) or ($user->SedeLegale==1)) {
							// 									$ck = "";
							// 									if (($CorsaId_post == $CorsaId) and ($DataCorsa_post == $DataPartenza))
							// 										$ck = "checked";
							// 									if($Tp == 'A'){
							// 										$mod = "$('#modificaData').val(1);";
							// 									} else {
							// 										$mod = "$('#modificaDataRitorno').val(1);";
							// 									}
							// 									$row[] = "<input $ck type=\"radio\" name=\"Corsa$Tp\" onclick=\"javascript:ControllaDataPassata('$diff');$mod$('#CorsaSelezionata$Tp').val($CorsaId);$('#DataSelezionata$Tp').val('$DataPartenza');MostraFermate();\" />";
							// 									// 								} else {
							// 									// 									$row[] = '';
							// 									// 								}
							// 								} else if ( $aColumns[$i] == 'PostiRealmentePrenotati') {
							// 									$PostiPrenotati=$corsa['PostiRealmentePrenotati'];
							// 									$AppCalendarioData=$corsa['AppCalendarioData'];
							// 									if ($PostiPrenotati>0)
							// 										$row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','realmente_prenotati_corsa.php?do=show_list&amp;CorsaId=".$CorsaId."&DataCorsa=".$AppCalendarioData."');\" title=\"elenco prenotati\">".$PostiPrenotati."</a>";
							// 									else
							// 										$row[]= $PostiPrenotati;
							// 								} else if ( $aColumns[$i] == 'PostiRealmenteDisponibili') {
							// 									$disponibili=$corsa['PostiCorsaDefault']+$corsa['PostiCorsaAggiunti']-$corsa['PostiRealmentePrenotati'];
							// 									$row[] = $disponibili;
							// 								} elseif (( $aColumns[$i] != 'PostiRealmenteDisponibili' ) and ( $aColumns[$i] != 'CorsaNome' ) and ( $aColumns[$i] != '' ) and ( $aColumns[$i] != 'PostiRealmentePrenotati' ) and ($aColumns[$i] != 'AppCalendarioData') and ($aColumns[$i] != 'LineaId')) {
							// 									/* General output */
							// 									$row[] = ($corsa[ $aColumns[$i] ]);
							// 								}
							// 							}

							$output['aaData'][] = $corsa;
							$output["iTotalRecords"] = $output["iTotalRecords"] + 1;
							$output["iTotalDisplayRecords"] = $output["iTotalDisplayRecords"] + 1;
						}
					}
				}
			}
		}

		if (isset($CorsaId_post)) {
			$isSearched = false;
			//verifico se la corsa della prenotazione modificata � presente nella lista di quelle cercate
			foreach ($output['aaData'] as $entry) {
				if (strpos($entry[0], 'val(' . $CorsaPrenotata_post . ')') !== false  && strpos($entry[0], $DataPrenotata_post) !== false) {
					$isSearched = true;
				}
			}
			if (!$isSearched) {
				//aggiunta della corsa alla lista se non � presente tra quelle ricercate
				$corsaModifica = $corsaObj->getCorsaValidaNoVincoli($DataPrenotata_post, $CorsaPrenotata_post, $LineaId_post);
				$row = array();
				if (isset($corsaModifica['CorsaNome']) && $corsaModifica['CorsaNome'] != "") {
					for ($i = 0; $i < count($aColumns); $i++) {
						if ($aColumns[$i] == "CorsaNome") {
							$row[] = utf8_decode($corsaModifica['CorsaNome']);
						} else if ($aColumns[$i] == "CorsaId") {
							$CorsaId = $corsaModifica['CorsaId'];
							$DataPartenza = $corsaModifica['AppCalendarioData'];

							$dataodierna = Date('Y-m-d');
							$dt = new DT();
							$diff = $dt->compare($dataodierna, $DataPartenza, 'Y-m-d');

							$OrarioPartenza = $corsaModifica['OrarioPartenza'];
							$name_input = "Corsa[$CorsaId.]";
							$arr_field = $Tp . "_" . $CorsaId . "_" . $DataPartenza;
							$arr_field = "";

							/* General output */
							$disponibili = $corsaModifica['PostiCorsaDefault'] + $corsaModifica['PostiCorsaAggiunti'] - $corsaModifica['PostiRealmentePrenotati'];
							// 					if (($disponibili>0) or ($CorsaId_post==$CorsaId) or ($user->SedeLegale==1)) {
							$ck = "";
							if (($CorsaId_post == $CorsaId) and ($DataCorsa_post == $DataPartenza))
								$ck = "checked";

							$row[] = "<input $ck type=\"radio\" name=\"Corsa$Tp\" onclick=\"javascropt:ControllaDataPassata('$diff');$('#CorsaSelezionata$Tp').val($CorsaId);$('#DataSelezionata$Tp').val('$DataPartenza');MostraFermate();\" />";
							// 					} else {
							// 						$row[] = '';
							// 					}
						} else if ($aColumns[$i] == 'PostiRealmentePrenotati') {
							$PostiPrenotati = $corsaModifica['PostiRealmentePrenotati'];
							$AppCalendarioData = $corsaModifica['AppCalendarioData'];
							if ($PostiPrenotati > 0)
								$row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','prenotati_corsa.php?do=show_list&amp;CorsaId=" . $CorsaId . "&DataCorsa=" . $AppCalendarioData . "');\" title=\"elenco prenotati\">" . $PostiPrenotati . "</a>";
							else
								$row[] = $PostiPrenotati;
						} else if ($aColumns[$i] == 'PostiRealmenteDisponibili') {
							$disponibili = $corsaModifica['PostiCorsaDefault'] + $corsaModifica['PostiCorsaAggiunti'] - $corsaModifica['PostiRealmentePrenotati'];
							$row[] = $disponibili;
						} elseif (($aColumns[$i] != 'PostiRealmenteDisponibili') and ($aColumns[$i] != 'CorsaNome') and ($aColumns[$i] != '') and ($aColumns[$i] != 'PostiRealmentePrenotati') and ($aColumns[$i] != 'AppCalendarioData') and ($aColumns[$i] != 'LineaId')) {
							/* General output */
							$row[] = ($corsaModifica[$aColumns[$i]]);
						}
					}
					array_unshift($output['aaData'], array_decode_list($row));
					$output["iTotalRecords"] = $output["iTotalRecords"] + 1;
					$output["iTotalDisplayRecords"] = $output["iTotalDisplayRecords"] + 1;
				}
			}
		}

		return $output;
	}

	function getTipologiaBiglietti($comunePickup, $fermataSalitaId, $comuneDropoff, $fermataDiscesaId, $corsaAndataId, $corsaRitornoId, $dataAndata, $daraRitorno, $TV, $lineaIdR)
	{
		$db = $this->conn;
		$PrenotazioneId = null;
		$prenotazione_wizard = null;
		$prenotazione = new Prenotazione();
		$prenotazione->conn = $db;

		$FermataIdRP = null;
		$FermataIdRD = null;
		if ($TV == 2) {
			$result = $this->getFermateGrafo($corsaRitornoId, $comuneDropoff, $comunePickup, $lineaIdR);
			$FermataIdRP = $result['pickup'][0]['FermataId'];
			$FermataIdRD = $result['dropoff'][0]['FermataIdDrop'];
		}
		$arrPrezziBiglietti = $prenotazione->GetTipologiaBigliettiPrezzi(0, $PrenotazioneId, $prenotazione_wizard, $dataAndata, $corsaAndataId, $fermataSalitaId, $fermataDiscesaId, $corsaRitornoId, $FermataIdRP, $FermataIdRD, $TV, null, null, null, $PrenotazioneId, null, null, $daraRitorno);

		$arrayPrezzi = array();
		foreach ($arrPrezziBiglietti as $b) {
			$sql = "Select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = " . $b['BigliettoId'];
			$temp = $db->query_first($sql);
			$b['OccupaPosto'] = $temp['OccupaPosto'];
			$arrayPrezzi[] = $b;
		}

		return $arrayPrezzi;
	}

	function modifica($pId, $arr_biglietti_prenotati, $arr_biglietti_riduzione, $arr_biglietti_aumento, $dataRitorno, $corsaRitornoId, $dataAndata, $corsaAndataId, $comunePickup, $fermataPickup, $comuneDropoff, $fermataDropoff, $tipoViaggio)
	{
		global $user;
		$db = $this->conn;

		$storico = new StoricoOperazioni();
		$storico->conn = $db;
		$prenotazione_wizard = new Prenotazione($pId);

		$daconfermare = 0;

		$cambioData = 1;
		$prenotazione_wizard->conn = $db;
		$old_prenotazioneid = $prenotazione_wizard->Id;
		$oldprenotazioneId = $old_prenotazioneid;
		$prenotazione_wizard->inizializzaDatiGeneraliMobile();
		$arr_old_dati = $prenotazione_wizard->DatiGenerali;
		$prenotazione_wizard->inizializzaDatiGeneraliPercorsoMobile('A');
		$arr_old_dati_percorso_a = $prenotazione_wizard->DatiGeneraliPercorso;
		$oldTipoViaggio = $arr_old_dati['TipoViaggioId'];
		$OldPax = $arr_old_dati['TotalePaxPrenotati'];
		$OldPrenotazioneStato = $arr_old_dati['PrenotazioneStato'];
		$OldDataPartenzaA = $arr_old_dati_percorso_a['CorsaDataPartenza'];
		$OldCorsaPartenzaA = $arr_old_dati_percorso_a['CorsaId'];

		$s = "select * from RT_PrenotazioneTratta where CorsaId=$OldCorsaPartenzaA and  Stato=1 and Cancella=0 and PrenotazioneId=$old_prenotazioneid order by PrenotazioneTrattaId asc";
		$oldtratte_a = $db->fetch_array($s);

		if ($oldTipoViaggio == 2) {
			$prenotazione_wizard->inizializzaDatiGeneraliPercorsoMobile('R');
			$arr_old_dati_percorso_r = $prenotazione_wizard->DatiGeneraliPercorso;

			$OldDataPartenzaR = $arr_old_dati_percorso_r['CorsaDataPartenza'];
			$OldCorsaPartenzaR = $arr_old_dati_percorso_r['CorsaId'];

			$s = "select * from RT_PrenotazioneTratta where CorsaId=$OldCorsaPartenzaR and  Stato=1 and Cancella=0 and PrenotazioneId=$old_prenotazioneid order by PrenotazioneTrattaId asc";
			$oldtratte_r = $db->fetch_array($s);
		}
		// prelevo i dati del form ed aggiorno tutte le proprieta' dell'oggetto
		$StatoPrenotazione = 1;
		$data_prenotazione['ClienteCellulareFamiliare'] = $arr_old_dati['ClienteCellulareFamiliare'];
		$data_prenotazione['ClienteCellulare'] = $arr_old_dati['ClienteCellulare'];
		$data_prenotazione['ClienteEmail'] = $arr_old_dati['ClienteEmail'];
		$data_prenotazione['ClienteNome'] = $arr_old_dati['ClienteNome'];
		$data_prenotazione['ClienteSessoId'] = $arr_old_dati['ClienteSessoId'];
		$data_prenotazione['Promozione'] = $arr_old_dati['Promozione'];
		$data_prenotazione['TipoViaggioId'] = $tipoViaggio;
		$data_prenotazione['PrenotazioneStato'] = $StatoPrenotazione;
		$data_prenotazione['CodicePrenotazione'] = strtoupper(uniqid());

		$data_prenotazione = $storico->operazioni_insert($data_prenotazione, $user);
		//  		print_r($data_prenotazione);
		$sql = "SELECT * FROM roccoautolinee.RT_PrenotazionePercorso where PrenotazioneId = $pId and Direzione='A'";
		$rowsT = $db->fetch_array($sql);
		$data_fermate['FermataIdAD'] = $fermataPickup; //$rowsT[0]['FermataSalitaId'];
		$data_fermate['FermataIdAP'] = $fermataDropoff; //$rowsT[0]['FermataDiscesaId'];

		$PrenotazioneNote[$rowsT[0]['CorsaId'] . '_1'] = '';
		$PrenotazioneNote[$rowsT[0]['CorsaId'] . '_2'] = '';
		$PrenotazioneNote[$rowsT[0]['CorsaId'] . '_3'] = '';
		$PrenotazioneNote[$rowsT[0]['CorsaId'] . '_4'] = '';
		$PrenotazioneNote[$rowsT[0]['CorsaId'] . '_5'] = '';

		$CorsaAndata = $corsaAndataId; //$rowsT[0]['CorsaId'];
		$DataSelezionataA = $dataAndata; //$rowsT[0]['CorsaDataPartenza'];
		$FermataAndataP = $data_fermate['FermataIdAD'];
		$FermataAndataD = $data_fermate['FermataIdAP'];

		$prenotazione = new Prenotazione();
		$prenotazione->conn = $db;

		$IdTratteAndata = $prenotazione->GetTratte($CorsaAndata, $FermataAndataP, $FermataAndataD);
		$listini_id = $prenotazione->GetListini($IdTratteAndata, $FermataAndataP, $FermataAndataD, $CorsaAndata);

		//print_r($listini_id);

		$CorsaRitorno = 0;
		$DataSelezionataR = "";
		if ($data_prenotazione['TipoViaggioId'] == 2) {
			$CorsaRitorno = $corsaRitornoId; //$rowsR[0]['CorsaId'];
			$DataSelezionataR = $dataRitorno; //$rowsR[0]['CorsaDataPartenza'];
			$sql = "SELECT * FROM RT_PrenotazionePercorso where PrenotazioneId = $pId and Direzione='R'";
			//  			$rowsR=$db->fetch_array($sql);
			//  			if(count($rowsR) > 0 ){
			//  				echo "xxxxxxxx";
			//  				$FermataRitornoP = $rowsR[0]['FermataSalitaId'];
			//  				$FermataRitornoD = $rowsR[0]['FermataDiscesaId'];
			//  			} else {
			//  				echo "yyyyyy";
			//ritorno pickup
			$sql = "SELECT  RT_Fermata.ComuneId, RT_Fermata.FermataNome
				from (((`RT_Corsa` join `RT_Tratta` on((`RT_Corsa`.`LineaId` = `RT_Tratta`.`LineaId`))) 
				join `RT_Fermata` on((`RT_Tratta`.`TrattaId` = `RT_Fermata`.`TrattaId`))) 
				join `RT_Orario` on(((`RT_Fermata`.`FermataId` = `RT_Orario`.`FermataId`) 
				and (`RT_Corsa`.`CorsaId` = `RT_Orario`.`CorsaId`))))
				where RT_Corsa.CorsaId=$CorsaAndata and RT_Fermata.FermataId = $FermataAndataP";
			$rowFP = $db->query_first($sql);

			$sql = "SELECT  distinct RT_Tratta.TrattaId,RT_Tratta.NodoPeso, RT_Corsa.CorsaId, RT_Fermata.FermataId 
				from (((`RT_Corsa` join `RT_Tratta` on((`RT_Corsa`.`LineaId` = `RT_Tratta`.`LineaId`))) 
				join `RT_Fermata` on((`RT_Tratta`.`TrattaId` = `RT_Fermata`.`TrattaId`))) 
				join `RT_Orario` on(((`RT_Fermata`.`FermataId` = `RT_Orario`.`FermataId`) 
				and (`RT_Corsa`.`CorsaId` = `RT_Orario`.`CorsaId`))))
				where RT_Corsa.CorsaId=104 and RT_Fermata.ComuneId = " . $rowFP['ComuneId'] . " and FermataNome = '" . $rowFP['FermataNome'] . "'
				order by `RT_Corsa`.`CorsaPeso`,`RT_Tratta`.`NodoPeso`,`RT_Tratta`.`TrattaPeso`,`RT_Fermata`.`FermataPeso`";
			$rowFP2 = $db->query_first($sql);
			$FermataRitornoP = $rowFP2['FermataId'];

			//ritorno dropoff
			$sql = "SELECT  RT_Fermata.ComuneId, RT_Fermata.FermataNome
 				from (((`RT_Corsa` join `RT_Tratta` on((`RT_Corsa`.`LineaId` = `RT_Tratta`.`LineaId`)))
 				join `RT_Fermata` on((`RT_Tratta`.`TrattaId` = `RT_Fermata`.`TrattaId`)))
 				join `RT_Orario` on(((`RT_Fermata`.`FermataId` = `RT_Orario`.`FermataId`)
 				and (`RT_Corsa`.`CorsaId` = `RT_Orario`.`CorsaId`))))
 				where RT_Corsa.CorsaId=$CorsaAndata and RT_Fermata.FermataId = $FermataAndataD";
			$rowFD = $db->query_first($sql);

			$sql = "SELECT  distinct RT_Tratta.TrattaId,RT_Tratta.NodoPeso, RT_Corsa.CorsaId, RT_Fermata.FermataId
				from (((`RT_Corsa` join `RT_Tratta` on((`RT_Corsa`.`LineaId` = `RT_Tratta`.`LineaId`)))
				join `RT_Fermata` on((`RT_Tratta`.`TrattaId` = `RT_Fermata`.`TrattaId`)))
				join `RT_Orario` on(((`RT_Fermata`.`FermataId` = `RT_Orario`.`FermataId`)
				and (`RT_Corsa`.`CorsaId` = `RT_Orario`.`CorsaId`))))
				where RT_Corsa.CorsaId=104 and RT_Fermata.ComuneId = " . $rowFD['ComuneId'] . " and FermataNome = '" . $rowFD['FermataNome'] . "'
				order by `RT_Corsa`.`CorsaPeso`,`RT_Tratta`.`NodoPeso`,`RT_Tratta`.`TrattaPeso`,`RT_Fermata`.`FermataPeso`";
			$rowFD2 = $db->query_first($sql);
			$FermataRitornoD = $rowFD2['FermataId'];

			//  			}

			// 			$FermataRitornoP = $fermataDropoff; //$rowsR[0]['FermataSalitaId'];
			// 			$FermataRitornoD = $fermataPickup; //$rowsR[0]['FermataDiscesaId'];
			$IdTratteRitorno = $prenotazione->GetTratte($CorsaRitorno, $FermataRitornoP, $FermataRitornoD);
		}
		//********************************************************************************

		// controllo la validita del numero di posti
		// 		$arr_biglietti_prenotati['11_Omaggio Intero'] = 0;
		// 		$arr_biglietti_prenotati['13_Omaggio Ridotto'] = 0;
		// 		$arr_biglietti_prenotati['1_Intero'] = 2;
		// 		$arr_biglietti_prenotati['3_Ridotto'] = 0;

		// 		$arr_biglietti_prenotati['12_Omaggio Intero'] = 0;
		// 		$arr_biglietti_prenotati['14_Omaggio Ridotto'] = 0;
		// 		$arr_biglietti_prenotati['2_Intero'] = 2;
		// 		$arr_biglietti_prenotati['4_Ridotto'] = 0;

		// 		$arr_biglietti_riduzione['11'] = 0;
		// 		$arr_biglietti_riduzione['13'] = 0;
		// 		$arr_biglietti_riduzione['1'] = 0;
		// 		$arr_biglietti_riduzione['3'] = 0;

		// 		$arr_biglietti_aumento['11'] = 0;
		// 		$arr_biglietti_aumento['13'] = 0;
		// 		$arr_biglietti_aumento['1'] = 0;
		// 		$arr_biglietti_aumento['3'] = 0;

		$NumeroTotalePax = $prenotazione->ScriviTbBiglietti($arr_biglietti_prenotati, 0, $arr_biglietti_riduzione, $arr_biglietti_aumento, $IdTratteAndata, $listini_id);

		// 		//Controllo corsa Ritorno se viaggio
		// 		if ($user->SedeLegale!=1) {
		// 			$err.=$prenotazione->CheckDisponibilitaPax($DataSelezionataA,$CorsaAndata,$NumeroTotalePax,"Andata");
		// 			if ($CorsaRitorno>0)
		// 				$err.=$prenotazione->CheckDisponibilitaPax($DataSelezionataR,$CorsaRitorno,$NumeroTotalePax,"Ritorno");
		// 		}

		$StatoPrenotazione = $OldPrenotazioneStato;

		$check = true;
		if ($check == true) {
			$prenotazione_wizard->conn = $db;
			$PrenotazioneId = $prenotazione_wizard->Id;
			$data['Stato'] = 0;
			$data['Cancella'] = 1;
			$data1 = $data;
			$data1['PrenotazioneStato'] = 6;
			$data = $storico->operazioni_update($data, $user);
			$result = $db->update("RT_Prenotazione", $data1, "PrenotazioneId=" . $old_prenotazioneid);
			$result = $db->update("RT_PrenotazionePercorso", $data1, "PrenotazioneId=" . $old_prenotazioneid);
			$result = $db->update("RT_PrenotazionePosto", $data, "PrenotazioneId=" . $old_prenotazioneid);
			//$result=$db->update("RT_PrenotazionePercorsoNote", $data,"PrenotazioneId=".$old_prenotazioneid);
			$result = $db->update("RT_PrenotazioneTariffa", $data, "PrenotazioneId=" . $old_prenotazioneid);
			$result = $db->update("RT_PrenotazioneTratta", $data, "PrenotazioneId=" . $PrenotazioneId);

			// faccio update a stato cancellato / modificata e creo una nuova prenotazione

			$lastidA = $db->insert("RT_Prenotazione", $data_prenotazione);
			$NuovaPrenotazioneId = $lastidA;
			if ($lastidA != false) {
				$ok = $prenotazione->SettaId($lastidA);
				$x = $prenotazione->ScriviTbBiglietti($arr_biglietti_prenotati, 1, $arr_biglietti_riduzione, $arr_biglietti_aumento, $IdTratteAndata, $listini_id);

				//if ($modifica!=1)
				$xx = $prenotazione->GeneraCodiciPrenotazione($NumeroTotalePax);
				$x = $prenotazione->ScriviTbTratte($CorsaAndata, $IdTratteAndata, 'A');

				$DataPartenza = $DataSelezionataA;
				$x = $prenotazione->ScriviTbPercorso($CorsaAndata, $FermataAndataP, $FermataAndataD, $DataPartenza, 'A', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax);
				$x = $prenotazione->ScriviTbTariffe($listini_id, $IdTratteAndata, $CorsaRitorno);
				$x = $prenotazione->ScriviTbPostoScelto($arr_PostoSceltoA, $arr_PostoSceltoAI, $CorsaAndata, $DataPartenza);
				if ($CorsaRitorno > 0) {
					$x = $prenotazione->ScriviTbTratte($CorsaRitorno, $IdTratteRitorno, 'R');
					$DataPartenza = $DataSelezionataR;
					$x = $prenotazione->ScriviTbPercorso($CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $DataPartenza, 'R', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax);
					$listini_id_r = $prenotazione->GetListini($IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno);
					// $x=$prenotazione->ScriviTbTariffe($listini_id_r,$IdTratteRitorno,$CorsaRitorno);
					$x = $prenotazione->ScriviTbPostoScelto($arr_PostoSceltoR, $arr_PostoSceltoIR, $CorsaRitorno, $DataPartenza);
				} else
					$CorsaRitorno = 0;
				$abilitazioneprenotazione = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataP);

				$OldId = $prenotazione_wizard->Id;
				$d11['PrenotazioneId'] = $OldId;

				$PrenotazioneOld = new Prenotazione($OldId);
				$PrenotazioneOld->conn = $db;
				$PrenotazioneOld->inizializzaDatiGenerali();
				$arr_generali = $PrenotazioneOld->DatiGenerali;


				$dupd = null;
				//  $dupd['DataIns']=$arr_generali['DataIns'];
				$dupd['PrenotazioneId'] = $NuovaPrenotazioneId;
				//$dupd['IpIns']=$arr_generali['IpIns'];
				$dupd['OdcIdRef'] = $arr_generali['OdcIdRef'];
				$dupd['GestoreIdRef'] = $arr_generali['GestoreIdRef'];
				//$dupd['OpeIns']=$arr_generali['OpeIns'];
				$dupd = $storico->operazioni_update($dupd, $user);
				$dupd1 = $dupd;
				$dupd1['CodicePrenotazione'] = $arr_generali['CodicePrenotazione'];

				$result = $db->update("RT_Prenotazione", $dupd1, "PrenotazioneId=" . $NuovaPrenotazioneId);
				$dupd['PrenotazioneId'] = $lastidA;
				$result = $db->update("RT_PreparazioneBus", $dupd, "PrenotazioneId=" . $OldId);
				//solo quessta parte
				//$result=$db->update("RT_PreparazioneNavette", $dupd,"PrenotazioneId=".$OldId." and OdcIdRef=$user->OdcId");
				//fine parte
				// $result=$db->update("RT_PrenotazioneNumero", $dupd,"PrenotazioneId=".$OldId." and OdcIdRef=$user->OdcId");

				$TipoViaggio1 = "Corsa Semplice";
				if ($CorsaRitorno > 0) {
					$TipoViaggio1 = "Andata/Ritorno";
				}

				$return = $prenotazione->CreateDettaglioPrenotazione($CorsaAndata, "Andata", $TipoViaggio1, $StatoPrenotazione);
				if ($CorsaRitorno > 0)
					$return = $prenotazione->CreateDettaglioPrenotazione($CorsaRitorno, "Ritorno", $TipoViaggio1, $StatoPrenotazione);

				//  }
				$data1a['DaFatturare'] = 0;
				$data1a['DaBonificare'] = 0;

				$result = $db->update("RT_PrenotazioneDettaglio", $data1a, "GestoreIdRef=1");
				if (($abilitazioneprenotazione == 1) or ($OldPrenotazioneStato == 3)) // emissione ticket obbligatorio
				{
					if ($cambioData == 0)
						$x = $prenotazione->EmettiBigliettiAuto($NumeroTotalePax, $CorsaAndata, $CorsaRitorno);
				}
				if ($OldPrenotazioneStato == 3 && $cambioData == 1) {
					$data11['PrenotazioneStato'] = 3;

					$result = $db->update("RT_Prenotazione", $data11, "PrenotazioneId=" . $NuovaPrenotazioneId);
					$result = $db->update("RT_PrenotazionePercorso", $data11, "PrenotazioneId=" . $NuovaPrenotazioneId);

					$data1ag['PrenotazioneId'] = $NuovaPrenotazioneId;
					$result = $db->update("RT_PrenotazioneTitolo", $data1ag, "PrenotazioneId=$old_prenotazioneid");

					$sql = "Select PrenotazioneNumeroId from RT_PrenotazioneNumero where PrenotazioneId=$NuovaPrenotazioneId";
					$ArrObjectP = $db->fetch_array($sql);

					$sql = "Select PrenotazioneNumeroId from RT_PrenotazioneNumero where PrenotazioneId=$old_prenotazioneid";
					$ArrObjectPOld = $db->fetch_array($sql);

					$npre = 0;
					while ($npre < sizeof($ArrObjectP)) {
						$PrenotazioneNumeroId = $ArrObjectP[$npre]['PrenotazioneNumeroId'];
						$PrenotazioneNumeroIdOld = $ArrObjectPOld[$npre]['PrenotazioneNumeroId'];

						$data1ag1['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
						$result = $db->update("RT_PrenotazioneTitolo", $data1ag1, "PrenotazioneNumeroId=$PrenotazioneNumeroIdOld");

						$npre++;
					}
				}
				if ($abilitazioneprenotazione == 3) {
					$data11 = null;
					$data11['PrenotazioneStato'] = 11;

					$result = $db->update("RT_Prenotazione", $data11, "PrenotazioneId=" . $NuovaPrenotazioneId);
					$result = $db->update("RT_PrenotazionePercorso", $data11, "PrenotazioneId=" . $NuovaPrenotazioneId);
				}
				$sql = "select PrenotazioneStato from RT_PrenotazionePercorso where PrenotazioneId=$NuovaPrenotazioneId";
				$rr = $db->query_first($sql);
				if (!empty($rr['PrenotazioneStato'])) {
					$statoNuovo = $rr['PrenotazioneStato'];
					$d = null;
					$d['PrenotazioneStato'] = $statoNuovo;
					$db->update("RT_Prenotazione", $d, "PrenotazioneId=$NuovaPrenotazioneId");
				}
				return $NuovaPrenotazioneId;
			}
		}
	}

	public function cancellaPrenotazione($prenotazioneId, $corsaId)
	{
		$db = $this->conn;

		$sql = "DELETE FROM RT_MobileCaricoPasseggeri
		WHERE PrenotazioneId = $prenotazioneId and CorsaId = $corsaId and TipoServizio='Bus'";
		$result = $db->query($sql);
		return $result;
	}

	public function caricoPrenotazione($prenotazioneId, $corsaId, $corsaDataPartenza, $busId, $busNumero, $tipoServizio, $oldprenotazioneId)
	{
		$db = $this->conn;

		$sql = "SELECT * FROM RT_MobileCaricoPasseggeri where PrenotazioneId = $prenotazioneId and CorsaId = $corsaId and
		CorsaDataPartenza = '$corsaDataPartenza' and BusId = $busId and BusNumero = $busNumero and TipoServizio = '$tipoServizio';";
		$info = $db->query_first($sql);

		//aggiornamento MobileCarico
		$sql = "SELECT sum(Pax) as Caricati FROM RT_MobileCaricoPasseggeri where ComunePartenza = '" . $info['ComunePartenza'] . "' and FermataPartenza = '" . $info['FermataPartenza'] . "' and Caricati = 1;";
		$caricati = $db->query_first($sql);
		if ($caricati['Caricati'] == "") {
			$caricati['Caricati'] = "0";
		}
		$sql = "SELECT sum(Pax) as Modificati FROM RT_MobileCaricoPasseggeri where ComunePartenza = '" . $info['ComunePartenza'] . "' and FermataPartenza = '" . $info['FermataPartenza'] . "' and Caricati = 2;";
		$modificati = $db->query_first($sql);
		if ($modificati['Modificati'] == "") {
			$modificati['Modificati'] = "0";
		}
		$sql = "SELECT sum(Pax)as Pax FROM RT_MobileCaricoPasseggeri where ComunePartenza = '" . $info['ComunePartenza'] . "' and FermataPartenza = '" . $info['FermataPartenza'] . "';";
		$pax = $db->query_first($sql);

		$sql = " UPDATE RT_MobileCarico
					SET DaModificare = " . $modificati['Modificati'] . ", Caricati = " . $caricati['Caricati'] . ", Pax = " . $pax['Pax'] . "
					WHERE FermataNome = '" . $info['FermataPartenza'] . "' and Comune = '" . $info['ComunePartenza'] . "' and CorsaId = $corsaId and
		CorsaDataPartenza = '$corsaDataPartenza' and BusId = $busId and BusNumero = $busNumero and TipoServizio = '$tipoServizio';";
		$result = $db->query($sql);


		// 		//update Scarico Passeggeri
		// 		$sql = "select f.FermataNome, c.Comune from RT_Fermata f 
		// 				left join Comune c on (f.ComuneId = c. ComuneId)
		// 				where FermataId = $fermataId";
		// 		$temp = $db->fetch_array($sql);

		// 		$comune =  addslashes($temp[0]['Comune']);
		// 		$fermata =  addslashes($temp[0]['FermataNome']);

		// 		$sql = "SELECT PrenotazioneId, CorsaDataPartenza, CorsaId, ClienteNome, ClienteCellulare, Pax, ComuneArrivo as ComuneDestinazione, FermataArrivo as FermataNome, BusId, BusNumero, TipoServizio, AutistaId
		// 		FROM RT_MobileCaricoPasseggeri
		// 		WHERE CorsaDataPartenza = '$corsaDataPartenza' and CorsaId = $corsaId and BusId = $busId and BusNumero = $busNumero and TipoServizio ='$tipoServizio' and FermataArrivo = '".$info['FermataArrivo']."' and ComuneArrivo = '".$info['ComuneArrivo']."' and PrenotazioneId = $prenotazioneId";

		// 		$rows = $db->fetch_array($sql);

		// 		$rows[0]['Scaricati'] = 0;
		// 		//nota
		// 		$sql = "SELECT Nota FROM roccoautolinee.RT_PrenotazionePercorsonote where prenotazioneId = ".$rows[0]['PrenotazioneId']." and TipoNota = 'D';";
		// 		$rowsTemp = $db->fetch_array($sql);
		// 		$rows[0]['NotaScarico'] = $rowsTemp[0]['Nota'];

		// 		if($rows[0]['NotaScarico'] == ""){
		// 			$nota = "null";
		// 		} else {
		// 			$nota = "'".$rows[0]['NotaScarico']."'";
		// 		}
		// 		if($rows[0]['AutistaId'] == ""){
		// 			$autista = "null";
		// 		} else {
		// 			$autista = "'".$rows[0]['AutistaId']."'";
		// 		}


		// 		$sql = "INSERT INTO RT_MobileScaricoPasseggeri VALUES (null,". $rows[0]['PrenotazioneId'].",". $fermataId.",".$trattaId.",'".
		// 				$rows[0]['CorsaDataPartenza']."',".$rows[0]['CorsaId'].",'".$rows[0]['ClienteNome']."','".$rows[0]['ClienteCellulare']."',".$rows[0]['Pax'].",".$rows[0]['Scaricati'].",".$nota.",".$rows[0]['BusId'].",".$rows[0]['BusNumero'].",'".$rows[0]['TipoServizio']."',".$autista.",'".$rows[0]['FermataNome']."','".$rows[0]['ComuneDestinazione']."')";
		// 		$result = $db->query($sql);


		// 		//aggiornamento MobileScarico

		// 		$sql = "SELECT sum(Pax) FROM RT_MobileScaricoPasseggeri where ComuneDestinazione = '".$info['ComuneArrivo']."' and FermataNome = '".$info['FermataArrivo']."' and Scaricati = 1;";
		// 		$scaricati = $db->query_first($sql);
		// 		$sql = "SELECT sum(Pax) FROM RT_MobileScaricoPasseggeri where ComuneDestinazione = '".$info['ComuneArrivo']."' and FermataNome = '".$info['FermataArrivo']."';";
		// 		$pax = $db->query_first($sql);

		// 		$sql =" UPDATE RT_MobileScarico
		// 		SET Scaricati = $scaricati, Pax = $pax
		// 		WHERE FermataNome = '".$info['FermataArrivo']."' and Comune = '".$info['ComuneArrivo']."' and CorsaId = $corsaId and
		// 		CorsaDataPartenza = '$corsaDataPartenza' and BusId = $busId and BusNumero = $busNumero and TipoServizio = '$tipoServizio';";
		// 		$result = $db->query($sql);


	}


	public function contabilitaPrevisto($corsaIdS, $corsaDataPartenzaS, $busId)
	{
		$db = $this->conn;

		$sql = "Select sum(DaPagare) as TotPrevisto FROM RT_MobileCaricoPasseggeri b
		where b.BusId = $busId";
		$result = $db->fetch_array($sql);
		return $result[0]['TotPrevisto'];
	}


	public function contabilitaPos($corsaIdS, $corsaDataPartenzaS, $busId)
	{
		$db = $this->conn;

		$sql = "Select sum(DaPagare) as Pos FROM RT_MobileCaricoPasseggeri b
		where b.Pagamento = 'pos' and b.BusId = $busId";
		$result = $db->fetch_array($sql);
		return $result[0]['Pos'];
	}

	public function contabilitaContanti($corsaIdS, $corsaDataPartenzaS, $busId)
	{
		$db = $this->conn;

		$sql = "Select sum(DaPagare) as Contanti FROM RT_MobileCaricoPasseggeri b
		where b.Pagamento = 'contanti' and
		b.BusId = $busId";
		$result = $db->fetch_array($sql);
		return $result[0]['Contanti'];
	}

	public function contabilitaResiduoCarico($corsaIdS, $corsaDataPartenzaS, $busId)
	{
		$db = $this->conn;
		$sql = "Select sum(DaPagare) as Residuo FROM RT_MobileCaricoPasseggeri b
		where b.DaPagare > 0 and b.Pagamento is null and
		b.BusId = $busId and b.Caricato = 1";

		$result = $db->fetch_array($sql);
		return $result[0]['Residuo'];
	}

	public function contabilitaResiduoTotale($corsaIdS, $corsaDataPartenzaS, $busId)
	{
		$db = $this->conn;

		$sql = "Select sum(DaPagare) as ResiduoTotale FROM RT_MobileCaricoPasseggeri b
		where (b.Caricato = 0 or b.Caricato = 3) and
		b.BusId = $busId";
		$result = $db->fetch_array($sql);
		return $result[0]['ResiduoTotale'];
	}


	public function cerca($nomeCliente, $biglietto)
	{

		$nomeCliente = str_replace("'", "\'", $nomeCliente);
		$biglietto = str_replace("'", "\'", $biglietto);
		$db = $this->conn;

		$sql = "SELECT p.PrenotazioneId, p.CodicePrenotazione, p.ClienteNome, p.ClienteCellularePrefisso, p.ClienteCellulare,
		p.ClienteCellulareFamiliare, t.Codice, t.Anno, t.Cognome, t.Nome, t.SessoId, t.Eta, t.TipologiaBiglietto
		FROM RT_PrenotazioneTitolo t
		LEFT JOIN RT_Prenotazione p ON t.PrenotazioneId = p.PrenotazioneId
		WHERE t.TipoTitolo = 'E' AND
		(concat(t.Cognome, ' ', t.Nome) LIKE '%$nomeCliente%' or concat(t.Nome, ' ', t.Cognome) LIKE '%$nomeCliente%')
		AND p.PrenotazioneStato = 3 AND t.Codice like '%$biglietto%'
		order by t.DataIns desc";

		$rows = $db->fetch_array($sql);
		for ($ii = 0; $ii < count($rows); $ii++) {
			$sql = "Select * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = " . $rows[$ii]['PrenotazioneId'] . " AND Direzione = 'A'";
			$tempA = $db->query_first($sql);

			$sql = "Select * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = " . $rows[$ii]['PrenotazioneId'] . " AND Direzione = 'R'";
			$tempR = $db->query_first($sql);

			$rows[$ii]['Andata'] = $tempA;
			$rows[$ii]['Ritorno'] = $tempR;
		}

		return $rows;
	}

	public function getInfo($corsaIdS, $corsaDataPartenzaS, $busId, $busNumero, $tipoServizio, $prenotazioneId)
	{
		$db = $this->conn;

		$sql = "SELECT m.*, p.ClienteEmail FROM RT_MobileCaricoPasseggeri m 
		left join RT_Prenotazione p on p.PrenotazioneId = m.PrenotazioneId
		where m.PrenotazioneId = $prenotazioneId and m.CorsaId = $corsaIdS and m.CorsaDataPartenza = '$corsaDataPartenzaS' and
		m.BusId = $busId and m.BusNumero = $busNumero and m.TipoServizio = '$tipoServizio'";
		$rowsTemp = $db->fetch_array($sql);

		return $rowsTemp[0];
	}

	public function selectUser($autistaId)
	{
		global $user;
		$db = $this->conn;
		$sql = "Select OperatoreId From RT_Autisti where AutistiId = $autistaId";
		$rows = $db->fetch_array($sql);
		if (isset($rows[0]['OperatoreId'])) {
			$user->inizializzaMobile($rows[0]['OperatoreId']);
		} else {
			$user->inizializzaMobile(1);
		}
	}

	public function selectUserByOperatoreId($operatoreId = 1)
	{
		global $user;
		if (!isset($user)) {
			$db = $this->conn;
			$user = new Operatore();
			$user->conn = $db;
		}
		if (isset($operatoreId)) {
			$user->inizializzaMobile($operatoreId);
		} else {
			$user->inizializzaMobile(1);
		}
	}

	public function getOrarioPartenzaBus($busId)
	{
		$db = $this->conn;

		$sql = "SELECT min(IdCarico), OrarioPartenza FROM RT_MobileCarico where BusId = $busId";

		$rowsTemp = $db->fetch_array($sql);
		return $rowsTemp[0]['OrarioPartenza'];
	}

	public function getPartenzaBus($busId)
	{
		$db = $this->conn;

		$sql = "SELECT n.Comune, n.Ordine, c.Comune as Nome
				FROM RT_GestioneOttimizzataNodo n
				LEFT JOIN Comune c on c.ComuneId = n.Comune
				where n.BusPartenza = $busId
				order by ordine";

		$rowsTemp = $db->fetch_array($sql);
		return $rowsTemp[0]['Nome'];
	}

	public function getArrivoBus($busId)
	{
		$db = $this->conn;

		$sql = "SELECT n.Comune, n.Ordine, c.Comune as Nome
		FROM RT_GestioneOttimizzataNodo n
		LEFT JOIN Comune c on c.ComuneId = n.Comune
		where n.BusArrivo = $busId
		order by ordine desc";

		$rowsTemp = $db->fetch_array($sql);
		return $rowsTemp[0]['Nome'];
	}

	public function getTotPax($corsaIdS, $corsaDataPartenzaS, $busId, $lineaId)
	{
		$db = $this->conn;

		$sql = "SELECT COUNT(*) AS Pax FROM RT_MobileCaricoPasseggeri WHERE BusId=$busId ;";
		$rowsTemp = $db->fetch_array($sql);
		$tot = $rowsTemp[0]['Pax'];
		if (!isset($tot) || $tot == null) {
			$tot = 0;
		}
		return $tot;
	}

	public function getTotCaricatiPax($corsaIdS, $corsaDataPartenzaS, $busId)
	{
		$db = $this->conn;

		$sql = "SELECT COUNT(Caricato) AS Pax FROM RT_MobileCaricoPasseggeri WHERE BusId=$busId and Caricato =1;";
		$rowsTemp = $db->fetch_array($sql);
		$tot = $rowsTemp[0]['Pax'];
		if (!isset($tot) || $tot == null) {
			$tot = 0;
		}
		return $tot;
	}

	public function getInfoPrenotazione($prenotazioneId)
	{
		$db = $this->conn;

		$sql = "SELECT ClienteNome, ClienteSessoId, ClienteCellulare, ClienteCellulareFamiliare, ClienteEmail
		 FROM RT_Prenotazione where PrenotazioneId = $prenotazioneId";
		$rowsTemp = $db->fetch_array($sql);
		return $rowsTemp[0];
	}

	public function getNotePrenotazione($prenotazioneId)
	{
		$db = $this->conn;

		$sql = "SELECT * FROM RT_PrenotazionePercorsoNote 
		where (TipoNota = 'G' OR TipoNota = 'S') and PrenotazioneId = $prenotazioneId";
		$rowsTemp = $db->fetch_array($sql);
		return $rowsTemp;
	}

	public function contabilitaContantiLista($corsaIdS, $corsaDataPartenzaS, $busId)
	{
		$db = $this->conn;

		$sql = "Select * FROM RT_MobileCaricoPasseggeri b
			where b.Pagamento = 'contanti' and
			b.BusId = $busId";
		$result = $db->fetch_array($sql);
		return $result;
	}

	public function contabilitaPOSLista($corsaIdS, $corsaDataPartenzaS, $busId)
	{
		$db = $this->conn;

		$sql = "Select * FROM RT_MobileCaricoPasseggeri b
		where b.Pagamento = 'pos' and
		b.BusId = $busId";
		$result = $db->fetch_array($sql);
		return $result;
	}


	public function getAllPickup()
	{
		$db = $this->conn;
		$sql = "SELECT DISTINCT 
		comune.ComuneId AS ComuneId,
		comune.Comune AS Comune,
		rt_corsatariffa.OdcIdRef AS OdcIdRef 
		FROM 
		RT_CorsaTariffa AS rt_corsatariffa 
		JOIN Comune AS comune ON (rt_corsatariffa.FermataPickup = comune.ComuneId)
		ORDER BY comune.Comune";
		$row = $db->fetch_array($sql);
		return $row;
	}

	public function getAllDropOff()
	{
		$db = $this->conn;
		$sql = "SELECT DISTINCT 
		comune.ComuneId AS ComuneId,
		comune.Comune AS Comune,
		rt_corsatariffa.OdcIdRef AS OdcIdRef 
		FROM 
		RT_CorsaTariffa AS rt_corsatariffa 
		JOIN Comune AS comune ON (rt_corsatariffa.FermataDropOff = comune.ComuneId)
		ORDER BY comune.Comune";

		$row = $db->fetch_array($sql);
		return $row;
	}

	public function getFermateGrafo($CorsaId, $ComuneAndataId, $ComuneRitornoId, $lineaId)
	{
		global $user;
		$db = $this->conn;
		$sql = "select * from RT_PercorsoBreve where ComunePickupId=$ComuneAndataId and ComuneDropOffId=$ComuneRitornoId and CorsaId=$CorsaId";
		$r = $db->query_first($sql);
		$KmPercorsi = $r['KmPercorsi'];
		if (!empty($r['PercorsoBreveId'])) {
			$trattaPartenza = $r['TrattaPickupId'];
			$trattaArrivo = $r['TrattaDropOffId'];
		} else {
			$grafo = null;
			$grafo = new GrafoTratte($lineaId, $CorsaId, $db, $ComuneAndataId, $ComuneRitornoId);
			$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);

			$pre = new Prenotazione();
			$pre->conn = $db;
			$pre->CreatePercorsoBreve($ComuneAndataId, $ComuneRitornoId, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $CorsaId, $lineaId);
		}

		$sql = "SELECT distinct FermataId,FermataOrario,TrattaNome,FermataNome,Orario From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$ComuneAndataId  and OdcIdRef=$user->OdcId and TrattaId=$trattaPartenza order by TrattaPeso desc ";
		$arr_fermate = $db->fetch_array($sql);
		$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome,FermataNome,Orario From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$CorsaId and ComuneId=$ComuneRitornoId  and TrattaId=$trattaArrivo and OdcIdRef=$user->OdcId order by TrattaPeso asc";
		$arr_fermate_d = $db->fetch_array($sql);
		return array('pickup' => $arr_fermate, 'dropoff' => $arr_fermate_d);
	}

	public function getFermateByComune($CorsaId, $ComuneId, $tipo)
	{
		$db = $this->conn;
		if ($tipo == "P")
			$sql = "SELECT distinct FermataId,FermataOrario From RT_ElencoFermataOrarioPK WHERE IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$ComuneId order by Orario";
		else
			$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop From RT_ElencoFermataOrarioDO WHERE IsDropOff=1 and  CorsaId=$CorsaId and ComuneId=$ComuneId order by Orario";
		$row = $db->fetch_array($sql);
		return $row;
	}

	public function getInfoIncasso($prenotazione, $busId)
	{
		$db = $this->conn;
		$sql = "SELECT sum(DaPagare) as DaPagare, CodicePrenotazione from RT_MobileCaricoPasseggeri where CodicePrenotazione = '" . $prenotazione['CodicePrenotazione'] . "'";
		$row = $db->fetch_array($sql);
		return $row[0];
	}

	public function incassoCarico($busId, $corsaId, $corsaDataPartenza, $prenotazione, $lastValue, $conferma, $email, $cell, $pagamento)
	{
		$db = $this->conn;
		//aggiorno incasso rt_mobilecaricopasseggeri
		$sql = "UPDATE RT_MobileCaricoPasseggeri SET ConfermaPagamento='$conferma', sms = '$cell', mail = '$email', Pagamento = '$pagamento' WHERE BusId = $busId and CodicePrenotazione = '" . $prenotazione['CodicePrenotazione'] . "'";
		$result = $db->query($sql);

		//aggiorno incasso movimenti
		$sql = "SELECT * FROM RT_PrenotazioneMovimento m
				left join RT_Prenotazione p ON p.PrenotazioneId = m.PrenotazioneId
				where p.CodicePrenotazione = '" . $prenotazione['CodicePrenotazione'] . "' and (p.PrenotazioneStato = 3 or p.PrenotazioneStato = 1)
				and (m.TipoMovimento = 'I' or m.TipoMovimento = 'P') and m.Importo <> m.ImportoPagato";
		$rows = $db->fetch_array($sql);
		foreach ($rows as $r) {
			$sql = "UPDATE RT_PrenotazioneMovimento SET ImportoPagato='" . $r['Importo'] . "', TipoMovimento = 'I' WHERE PrenotazioneMovimentoId = " . $r['PrenotazioneMovimentoId'];
			$result = $db->query($sql);

			$sql = "UPDATE RT_Prenotazione SET TotaleResiduo = 0, TotalePagato = TotaleDaPagare WHERE PrenotazioneId = " . $r['PrenotazioneId'];
			$result = $db->query($sql);
		}

		return $result;
	}

	public function EmettiTitoliDiViaggio($busId, $corsaId, $corsaDataPartenza, $prenotazione, $lastValue, $conferma, $email, $cell)
	{
		global $db, $user;

		$sql = "Select * from RT_Prenotazione Where CodicePrenotazione = '" . $prenotazione['CodicePrenotazione'] . "' and PrenotazioneStato = 1";
		$rowTemEmetti = $db->fetch_array($sql);
		$pId = $rowTemEmetti[0]['PrenotazioneId'];

		$prenotazione_wizard = new Prenotazione($pId);
		$db = new Database();
		$db->connect();

		$sql = "UPDATE RT_MobileCarico SET TitoloEmesso='E' WHERE CodicePrenotazione = '" . $prenotazione['CodicePrenotazione'] . "' ";
		$db->query($sql);

		$PrenotazioneId = $prenotazione_wizard->Id;
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
		$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];

		$prenotazione_wizard->conn = $db;
		if (!$DatiGeneraliArr['Multi']) {
			$prenotazione_wizard->EmettiBiglietti();
		} else {
			$sql = "SELECT PrenotazioneId FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $DatiGeneraliArr['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $DatiGeneraliArr['PrenotazioneStato'] . " AND Cancella = 0 AND OdcIdRef = " . $user->OdcId;
			$prenotazioni = $db->fetch_array($sql);

			foreach ($prenotazioni as $prenotazione) {
				$prenotazioneObj = new Prenotazione($prenotazione['PrenotazioneId']);
				$prenotazioneObj->conn = $db;
				$prenotazioneObj->EmettiBiglietti();
			}
		}

		$output = array();
		$output['prenotazioneId'] = $PrenotazioneId;
		$output['corsaId'] = $CorsaId;

		echo json_encode($output);
	}

	public function getPostiBus($corsaId, $dataPartenza, $tipoServizio, $busId, $numeroBus)
	{
		$db = $this->conn;
		$sql = "Select NumeroPiani, Colonne, Righe from RT_TipologiaBus where TipologiaBusId = $busId";
		$arr_tb = $db->query_first($sql);

		$NumeroPiani = $arr_tb['NumeroPiani'];
		$NumeroColonne = $arr_tb['Colonne'];
		$NumeroRighe = $arr_tb['Righe'];
		$npiani = 1;
		$m = array();
		while ($npiani <= $NumeroPiani) {
			$i = 0;
			$m[$npiani] = array();
			while ($i < $NumeroRighe) {
				$m[$npiani][$i] = array();
				$n = 0;
				while ($n < $NumeroColonne) {
					$m[$npiani][$i][$n] = array();
					$rigacorrente = $i + 1;
					$colonnacorrente = $n + 1;

					$sql = "Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and TipologiaBusId=$busId";
					$row1 = $db->query_first($sql);
					$NumeroPosto = "";
					$DescrizionePosto = "";
					if (!empty($row1['TipologiaBusId'])) {
						$NumeroPosto = $row1['NumeroPosto'];
						$DescrizionePosto = $row1['DescrizionePosto'];
					}

					if ($NumeroPosto > 0) {
						$sql = "SELECT
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
	            		(RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3) and Piano=$npiani and BusId=$busId and BusNumero=$numeroBus and Riga=$rigacorrente and Colonna=$colonnacorrente and RT_PreparazioneBus.CorsaId=$corsaId and RT_PreparazioneBus.DataPartenza='$dataPartenza' 
	            		GROUP BY
	            		RT_PreparazioneBus.PrenotazioneId";
						$ArrObjectDup = $db->fetch_array($sql);
						if (sizeof($ArrObjectDup) > 0 && isset($ArrObjectDup[0]['ClienteNome']) && $ArrObjectDup[0]['ClienteNome'] != '') {

							$dup = 0;
							$Preferenza = 0;
							$ClienteNome = "";
							$ClienteSessoId = 0;
							$PrenotazionePostoId = 0;
							$TipoPrenotazione = 0;
							$PrenotazioneNumeroId = 0;

							while ($dup <  sizeof($ArrObjectDup)) {

								$Pid = 0;
								$isReserved = 1;
								$PrenotazionePostoId = $ArrObjectDup[$dup]['PrenotazionePostoId'];
								$Preferenza = $ArrObjectDup[$dup]['PreferenzaPiano'];
								$Pid = $ArrObjectDup[$dup]['PrenotazioneId'];
								$ClienteNome = $ArrObjectDup[$dup]['ClienteNome'];
								$ClienteSessoId = $ArrObjectDup[$dup]['ClienteSessoId'];
								$TipoPrenotazione = $ArrObjectDup[$dup]['TipoPrenotazione'];
								$PrenotazioneNumeroId = $ArrObjectDup[$dup]['PrenotazioneNumeroId'];

								$classe_sesso = "nd";
								if ($ClienteSessoId == 1)
									$classe_sesso = "maschio";
								elseif ($ClienteSessoId == 2)
									$classe_sesso = "femmina";

								if ($TipoPrenotazione == 1)
									$m[$npiani][$i][$n]['cliente'] = "$ClienteNome (A)";
								else
									$m[$npiani][$i][$n]['cliente'] = $ClienteNome;


								$dup++;
							}
						} else {
							// seleziono le prenotazioni senza posto   
							$ClienteNome = "";

							$sql = "SELECT RT_PreparazioneBus.CorsaId, RT_PreparazioneBus.DataPartenza, RT_PreparazioneBus.PrenotazioneId, RT_PreparazioneBus.BusId, RT_PreparazioneBus.BusNumero, RT_PreparazioneBus.OdcIdRef, RT_Prenotazione.ClienteNome, RT_Prenotazione.ClienteSessoId, RT_Prenotazione.ClienteCellulare, RT_Prenotazione.ClienteFidelityCardId, RT_Prenotazione.PrenotazioneModPre, RT_Prenotazione.TipoViaggioId, RT_PrenotazioneNumero.PrenotazioneNumeroId, RT_PrenotazionePosto.CorsaId, RT_PrenotazionePosto.DataPartenza, RT_PrenotazionePosto.Riga, RT_PrenotazionePosto.Colonna, RT_PrenotazionePosto.Posto, RT_PrenotazionePosto.DescrizionePosto, RT_PrenotazionePosto.Piano, RT_PrenotazionePosto.PreferenzaPiano, RT_PrenotazionePosto.TipoPrenotazione
		            		FROM
		            		RT_PreparazioneBus
		            		INNER JOIN RT_Prenotazione ON RT_PreparazioneBus.PrenotazioneId = RT_Prenotazione.PrenotazioneId
		            		INNER JOIN RT_PrenotazioneNumero ON RT_Prenotazione.PrenotazioneId = RT_PrenotazioneNumero.PrenotazioneId
		            		LEFT JOIN RT_PrenotazionePosto ON RT_PrenotazioneNumero.PrenotazioneNumeroId = RT_PrenotazionePosto.PrenotazioneNumeroId AND RT_PreparazioneBus.CorsaId = RT_PrenotazionePosto.CorsaId AND RT_PreparazioneBus.DataPartenza = RT_PrenotazionePosto.DataPartenza
		            		INNER JOIN RT_PrenotazioneDettaglio ON RT_PrenotazioneNumero.PrenotazioneNumeroId = RT_PrenotazioneDettaglio.PrenotazioneNumero AND RT_PreparazioneBus.DataPartenza = RT_PrenotazioneDettaglio.DataInizioItinerario AND RT_PreparazioneBus.CorsaId = RT_PrenotazioneDettaglio.CorsaInizioItinerario
		            		WHERE
		            		(RT_Prenotazione.PrenotazioneStato = 1 or RT_Prenotazione.PrenotazioneStato = 3) and BusId=$busId and BusNumero=$busNumero and Riga IS NULL and RT_PreparazioneBus.CorsaId=$corsaId and RT_PreparazioneBus.DataPartenza='$dataPartenza'  AND
		            		RT_PrenotazioneDettaglio.Escludi = 0";

							$row2 = $db->query_first($sql);
							$isReserved = 0;
							$Pid = 1;
							$PNid = 0;
							$Preferenza = 0;
							$ClienteNome = "";
							$ClienteSessoId = 0;
							$PrenotazionePostoId = 0;

							$classe_sesso = "nd";
							if ($ClienteSessoId == 1)
								$classe_sesso = "maschio";
							elseif ($ClienteSessoId == 2)
								$classe_sesso = "femmina";

							if ($Pid > 0) {
								$m[$npiani][$i][$n]['cliente'] = $ClienteNome;
							}
						}
					}
					$n++;
				}
				$i++;
			}
			$npiani++;
		}
		$result['matrice'] = $m;
		$result['piani'] = $NumeroPiani;
		$result['colonne'] = $NumeroColonne;
		$result['righe'] = $NumeroRighe;
		return $result;
	}

	public function insertFoglioViaggio($bus)
	{
		$db = $this->conn;
		$sql = "Select * from RT_FoglioViaggio where BusId = " . $bus['BusId'];
		$arr = $db->fetch_array($sql);

		if (count($arr) > 0) {
			//aggiornamento
			$autista1 = str_replace("'", "\'", $bus['Autista1']);
			$autista2 = str_replace("'", "\'", $bus['Autista2']);
			$sql = "UPDATE RT_FoglioViaggio
				SET AutistaId1=" . $bus['AutistaId1'] . ", AutistaId2=" . $bus['AutistaId2'] . ",
				Autista1 = '$autista1', Autista2 = '$autista2', TipologiaBus = '" . $bus['TipologiaBus'] . "',
					Targa = '" . $bus['Targa'] . "', Cellulare = '" . $bus['Cellulare'] . "' 
				WHERE BusId=" . $bus['BusId'];
		} else {
			//inserimento
			$linea = str_replace("'", "\'", $bus['LineaNome']);
			$corsa = str_replace("'", "\'", $bus['CorsaNome']);
			$autista1 = str_replace("'", "\'", $bus['Autista1']);
			$autista2 = str_replace("'", "\'", $bus['Autista2']);
			$partenza = str_replace("'", "\'", $bus['Partenza']);
			$arrivo = str_replace("'", "\'", $bus['Arrivo']);
			$sql = "INSERT INTO RT_FoglioViaggio (BusId, LineaId, LineaNome, CorsaId, CorsaNome, CorsaDataPartenza, AutistaId1, AutistaId2, Autista1, Autista2, TipologiaBus, Targa, Cellulare, Partenza, Arrivo, OrarioPartenza, KmPartenza, KmArrivo)
			VALUES (" . $bus['BusId'] . ", " . $bus['LineaId'] . ", '$linea', " . $bus['CorsaId'] . ", '$corsa',
			'" . $bus['CorsaDataPartenza'] . "', " . $bus['AutistaId1'] . ", " . $bus['AutistaId2'] . ", '$autista1', '$autista2', '" . $bus['TipologiaBus'] . "', '" . $bus['Targa'] . "',
					'" . $bus['Cellulare'] . "', '$partenza', '$arrivo', '" . $bus['OrarioPartenza'] . "', 0, 0)";
			$db->query($sql);
		}
	}

	public function resetFoglioViaggio($idAutista, $data)
	{
		$db = $this->conn;
		$sql = "Select * from RT_FoglioViaggio where (AutistaId1 = $idAutista or AutistaId2 = $idAutista) and CorsaDataPartenza = '$data'";
		$arr = $db->fetch_array($sql);

		foreach ($arr as $bus) {
			$sql = "select * from RT_GestioneOttimizzataFlotta where GestioneOttimizzataFlottaId = " . $bus['BusId'];
			$temp = $db->fetch_array($sql);
			if (count($temp) <= 0) {
				$sql = "DELETE FROM RT_FoglioViaggio
    					WHERE BusId = " . $bus['BusId'];
				$db->query($sql);
			}
		}
	}

	public function getInfoFoglioViaggio($busId)
	{
		$db = $this->conn;
		$sql = "Select * from RT_FoglioViaggio where BusId = $busId";
		$row = $db->query_first($sql);

		return $row;
	}

	public function getSpeseFoglioViaggio($busId)
	{
		$db = $this->conn;
		$sql = "Select * from RT_FoglioViaggioSpesa f
		left join RT_FoglioViaggioTipoSpesa t on t.FoglioViaggioTipoSpesaId = f.FoglioViaggioTipoSpesaId
		left join RT_FoglioViaggio v on v.FoglioViaggioId = f.FoglioViaggioId
		where v.BusId = $busId";
		$row = $db->fetch_array($sql);

		return $row;
	}

	public function updateInfoFoglioViaggio($targa, $kmPartenza, $kmArrivo, $busId, $autistaId)
	{
		$db = $this->conn;
		$sql = "UPDATE RT_FoglioViaggio
				SET Targa='" . $targa . "', KmPartenza=" . $kmPartenza . ",
				KmArrivo = $kmArrivo, AutistaIdUpdate = $autistaId
				WHERE BusId=" . $busId;

		$row = $db->query($sql);

		return $row;
	}

	public function getTipoSpesa()
	{
		$db = $this->conn;
		$sql = "Select * from RT_FoglioViaggioTipoSpesa";

		$row = $db->fetch_array($sql);

		return $row;
	}

	public function getPagamentoSpesa()
	{
		$db = $this->conn;
		$sql = "Select * from RT_FoglioViaggioPagamento";

		$row = $db->fetch_array($sql);
		return $row;
	}

	public function saveSpesa($busId, $idAutista, $tipo, $km, $importo, $litri, $pagamento, $spesaId)
	{
		$db = $this->conn;
		if ($spesaId == 0) {
			$sql = "Select * from RT_FoglioViaggio where BusId = " . $busId;
			$row = $db->query_first($sql);
			if (!isset($litri) || $litri == '') {
				$litri = 0;
			}
			$sql = "INSERT INTO RT_FoglioViaggioSpesa (FoglioViaggioId, FoglioViaggioTipoSpesaId, EventoKm, NumeroLitri, Importo, AutistaId, FoglioViaggioPagamentoId) VALUES
					(" . $row['FoglioViaggioId'] . ", $tipo, $km, $litri, $importo, $idAutista, $pagamento)";
			$result = $db->query($sql);
		} else {
			$sql = "UPDATE RT_FoglioViaggioSpesa SET
					FoglioViaggioTipoSpesaId = $tipo, EventoKm = $km, Importo = $importo, AutistaId = $idAutista, FoglioViaggioPagamentoId = $pagamento
					where FoglioViaggioSpesaId = $spesaId";
			$result = $db->query($sql);
		}
		return $result;
	}

	public function getSpesa($spesaId)
	{
		$db = $this->conn;
		$sql = "Select * from RT_FoglioViaggioSpesa where FoglioViaggioSpesaId = " . $spesaId;
		$row = $db->query_first($sql);

		return $row;
	}



	public function prenota($notaAndata, $notaRitorno, $arr_biglietti_prenotati, $arr_biglietti_riduzione, $arr_biglietti_aumento, $daraRitorno, $corsaRitornoId, $dataAndata, $corsaAndataId, $comunePickup, $fermataPickup, $comuneDropoff, $fermataDropoff, $tipoViaggio, $passeggeri, $clienteNome, $clienteCellulare, $clienteCellulareFamiliare, $clienteEmail, $clienteSessoId, $clienteEta, $t, $congiunti, $CodicePrenotazione, $tipoTour, $comunePickupR, $fermataPickupR, $comuneDropoffR, $fermataDropoffR, $clientePrefisso = 39, $gestoreIdRef = null, $libera = 0, $liberaTitolo = null)
	{
		global $user;

		$emetti = false;
		$db = $this->conn;

		$storico = new StoricoOperazioni();
		$storico->conn = $db;
		$modifica = 0;

		$prenotazione = new Prenotazione();
		$prenotazione->conn = $db;

		/** inizio recupero dati dalla form **/
		// prelevo i dati del form ed aggiorno tutte le propriet� dell'oggetto
		$StatoPrenotazione = 1;
		$TV = $tipoViaggio;
		$temp = $clienteNome;
		$data_prenotazione['ClienteNome'] = ucwords($temp);
		$data_prenotazione['ClienteCellulareFamiliare'] = $clienteCellulareFamiliare;
		$data_prenotazione['ClienteCellularePrefisso'] = $clientePrefisso;
		$data_prenotazione['ClienteCellulare'] = $clienteCellulare;
		$data_prenotazione['ClienteEmail'] = $clienteEmail;
		$data_prenotazione['ClienteSessoId'] = $clienteSessoId;
		$data_prenotazione['PrenotazioneStato'] = $StatoPrenotazione;
		$data_prenotazione['Multi'] = 0;
		$data_prenotazione['RitornoOpen'] = 0;
		$data_prenotazione['TipoViaggioId'] = $tipoViaggio;
		$data_prenotazione['TipoTour'] = $tipoTour;
		$data_prenotazione['Canale'] = "app_it";
		$data_prenotazione['Libera'] = $libera;
		$data_prenotazione['LiberaTitolo'] = $liberaTitolo;
		
		//genero codice prenotazione
		if (isset($CodicePrenotazione))
			$data_prenotazione['CodicePrenotazione'] = $CodicePrenotazione;
		else
			$data_prenotazione['CodicePrenotazione'] = $prenotazione->GetProgressivoCodicePrenotazione();

		$data_prenotazione = $storico->operazioni_insert($data_prenotazione, $user);
		//assegno gestoreIdRef
		if(isset($gestoreIdRef)) {
			$data_prenotazione['GestoreIdRef'] = $gestoreIdRef;
		}

		$ritornoOpen = 0;
		//fermate
		$data_fermate['FermataIdAP'] = $fermataPickup;
		$data_fermate['FermataIdAD'] = $fermataDropoff;
		$data_fermate['FermataIdRP'] = $fermataPickupR;
		$data_fermate['FermataIdRD'] = $fermataDropoffR;

		//note
		$PrenotazioneNote[$corsaAndataId . '_1'] = '';
		$PrenotazioneNote[$corsaAndataId . '_2'] = '';
		$PrenotazioneNote[$corsaAndataId . '_3'] = '';
		$PrenotazioneNote[$corsaAndataId . '_4'] = '';
		$PrenotazioneNote[$corsaAndataId . '_5'] = $notaAndata;
		if ($TV == 2) {
			$PrenotazioneNote[$corsaRitornoId . '_1'] = '';
			$PrenotazioneNote[$corsaRitornoId . '_2'] = '';
			$PrenotazioneNote[$corsaRitornoId . '_3'] = '';
			$PrenotazioneNote[$corsaRitornoId . '_4'] = '';
			$PrenotazioneNote[$corsaRitornoId . '_5'] = $notaRitorno;
		}

		$conferma_errori = 0;

		$CorsaAndata = $corsaAndataId;
		$DataSelezionataA = $dataAndata;
		$FermataAndataP = $data_fermate['FermataIdAP'];
		$FermataAndataD = $data_fermate['FermataIdAD'];

		$IdTratteAndata = $prenotazione->GetTratte($CorsaAndata, $FermataAndataP, $FermataAndataD);
		$listini_id = $prenotazione->GetListini($IdTratteAndata, $FermataAndataP, $FermataAndataD, $CorsaAndata);

		// print_r($listini_id);
		$TratteComplete = $IdTratteAndata;
		$ListiniCompleti = $listini_id;
		$CorsaRitorno = 0;
		$DataSelezionataR = "";
		$FermataRitornoP = null;
		$FermataRitornoD = null;
		if ($data_prenotazione['TipoViaggioId'] == 2) {
			$CorsaRitorno = $corsaRitornoId;
			$DataSelezionataR = $daraRitorno;
			$FermataRitornoP = $data_fermate['FermataIdRP'];
			$FermataRitornoD = $data_fermate['FermataIdRD'];
			$IdTratteRitorno = $prenotazione->GetTratte($CorsaRitorno, $FermataRitornoP, $FermataRitornoD);
			$listini_idr = $prenotazione->GetListini($IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno);

			$c = 0;

			while ($c < sizeof($IdTratteRitorno)) {
				$TratteComplete[] = $IdTratteRitorno[$c];
				$TrattaId = $IdTratteRitorno[$c]['TrattaId'];
				$ListiniCompleti[$TrattaId] = $listini_idr[$TrattaId];
				$c++;
			}
			$c = 0;
		}
		/** fine recupero dati dalla form **/

		// Check
		$check = true;
		$err = "";


		// verifica da confermare
		$dcr = 0;

		$dca = $prenotazione->GetDaConfermare($IdTratteAndata);
		if ($CorsaRitorno > 0)
			$dcr = $prenotazione->GetDaConfermare($IdTratteRitorno);

		$arr_Fermate[0]['FermataId'] = $FermataAndataP;
		$arr_Fermate[1]['FermataId'] = $FermataAndataD;
		if ($CorsaRitorno > 0) {
			$arr_Fermate[2]['FermataId'] = $FermataRitornoP;
			$arr_Fermate[3]['FermataId'] = $FermataRitornoD;
		}
		$FdaConf = $prenotazione->GetFermateDaConfermare($arr_Fermate);


		//recuper comune fermate
		$sql = "SELECT ComuneId from RT_Fermata where FermataId = $FermataAndataP";
		$comuneAndataP = $db->query_first($sql);
		$comuneAndataP = $comuneAndataP['ComuneId'];
		$sql = "SELECT ComuneId from RT_Fermata where FermataId = $FermataAndataD";
		$comuneAndataD = $db->query_first($sql);
		$comuneAndataD = $comuneAndataD['ComuneId'];

		/*
		 *
		*/
		// ********************************************************************************
 

		// controllo la validita del numero di posti

		$ar1 = 0;
		if ($CorsaRitorno > 0)
			$ar1 = 1;
		$NumeroTotalePax = $prenotazione->ScriviTbBiglietti($arr_biglietti_prenotati, 0, $arr_biglietti_riduzione, $arr_biglietti_aumento, $TratteComplete, $ListiniCompleti, $FermataAndataP, $FermataAndataD, $DataSelezionataA, $ar1, $IdTratteAndata);

		$err .= $prenotazione->CheckNumeroPax($NumeroTotalePax);

		// Controllo corsa Ritorno se viaggio
		$err .= $prenotazione->CheckDisponibilitaPax($DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata", $comuneAndataP,  $comuneAndataD);

		if ($CorsaRitorno > 0)
			$err .= $prenotazione->CheckDisponibilitaPax($DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno", $comuneAndataD,  $comuneAndataP);

		// Controllo stato della prentoazione con yes no
		if ($data_prenotazione['TipoViaggioId'] == 2)
			$err .= $prenotazione->CheckCoerenzaAR($DataSelezionataA, $DataSelezionataR, true);

		$TipoPreR = -1;

		$TipoPreAP = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataP);
		$TipoPreAD = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataD);

		if ($CorsaRitorno > 0) {
			$TipoPreRP = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaRitorno, $FermataRitornoP);
			$TipoPreRD = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataRitornoD);
		}


		//controllo del doppio inserimento di una prenotazione per gestire casi anomali
		// get ultima prenotazione per utente
		$sql = "select DataIns from RT_Prenotazione where OdcIdRef=$user->OdcId and OpeIns=$user->OperatoreId order by DataIns desc";

		$row = $db->query_first($sql);
		$DataIns = '';
		if (!empty($row['DataIns'])) {

			$DataIns = $row['DataIns'];
			$datacorrente = date('Y-m-d H:i:s');
			$dt1 = new DT();
			$secondi = $dt1->difference($datacorrente, $DataIns, 'Y-m-d H:i:s', 'second');
			if ($secondi <= 10)
				$err .= $dizionario['biglietto']['mess_err_due_prenotazioni'];
			// echo($secondi);
		}

		if ($CorsaRitorno > 0) {
			$sqA = "SELECT rt_linea.PercorsoId AS PercorsoId
			FROM RT_Corsa AS rt_corsa 
			LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
			LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
			WHERE rt_corsa.Cancella = 0
			AND rt_corsa.CorsaId = $CorsaAndata";
			$rowA = $db->query_first($sqA);
			$PercorsoAndataId = $rowA['PercorsoId'];

			$sqR = "SELECT rt_linea.PercorsoId AS PercorsoId
			FROM RT_Corsa AS rt_corsa 
			LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
			LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
			WHERE rt_corsa.Cancella = 0
			AND rt_corsa.CorsaId = $CorsaRitorno";
			$rowR = $db->query_first($sqR);
			$PercorsoRitornoId = $rowR['PercorsoId'];

			if ($PercorsoAndataId != $PercorsoRitornoId)
				$err .= $dizionario['biglietto']['mess_err_linee_differenti'];
		}

		// $err="true";
		if ($err != '') 	// chiedo di corregere gli errori
		{
			echo ($err);
			exit();
		}
		$tratta_confermare = "salita";
		if ($dcr)
			$tratta_confermare = "discesa";
		//controllo validita' fermate in base all'agenzia
		if (($user->SedeLegale != 1) and ($dca + $dcr + $FdaConf > 0) and ($StatoPrenotazione == 3)) {

			echo ($dizionario['biglietto']['mess_err_fermate_no_confermate']);
			exit();
		}
		$trattedifferenti = false;
		if ($modifica == 1) {
			if (((sizeof($oldtratte_a)) != (sizeof($IdTratteAndata))) or ((sizeof($oldtratte_r)) != (sizeof($IdTratteRitorno))))
				$trattedifferenti = true;
			else {
				$qtr = 0;
				while ($qtr < sizeof($oldtratte_a)) {
					if ($oldtratte_a[$qtr]['TrattaId'] != $IdTratteAndata[$qtr]['TrattaId'])
						$trattedifferenti = true;
					$qtr++;
				}
				$qtr = 0;
				if (sizeof($oldtratte_r) > 0) {
					while ($qtr < sizeof($oldtratte_r)) {
						if ($oldtratte_r[$qtr]['TrattaId'] != $IdTratteRitorno[$qtr]['TrattaId'])
							$trattedifferenti = true;
						$qtr++;
					}
				}
			}
		}

		if (($conferma_errori > 0) and (($conferma_errori < 100)) and ($user->SedeLegale = 1)) {
			$daconfermare = 0;
			if ((($dca + $dcr + $FdaConf) > 0)) {

				echo ($dizionario['biglietto']['mess_tratta_da_confermare'] . " $tratta_confermare " . $dizionario['biglietto']['mess_tratta_da_confermare2'] . "##100");
				$data_prenotazione['PrenotazioneStato'] = 2;
				exit();
				$StatoPrenotazione = 2;
				$daconfermare = 1;
			} else
				$trattedifferenti = false;
		}




		if (($data_prenotazione['TipoViaggioId'] == 2) and (!($CorsaRitorno > 0)) and ($ritornoOpen == 0)) {
			echo ($dizionario['biglietto']['mess_no_ritorno']);
			$check = false;
			exit();
		}

		if (!$CorsaAndata > 0) {
			echo ($dizionario['biglietto']['mess_no_andata']);
			$check = false;
			exit();
		}

		if (($conferma_errori < 400) && $emetti) {
			echo ($dizionario['biglietto']['mess_emetti'] . "##400");
			$check = false;
			exit();
		}

		if ($check == true) {

			// verifico se le fermate sono da confermare

			if ($trattedifferenti) {

				if ((($dca + $dcr + $FdaConf) > 0)) {
					$data_prenotazione['PrenotazioneStato'] = 2;
					$StatoPrenotazione = 2;
					$daconfermare = 1;
				}
			}

			if ($conferma_errori == 200) {

				$errore = $prenotazione->CheckDisponibilitaPax($DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata", $comuneAndataP,  $comuneAndataD);

				if ($CorsaRitorno > 0)
					$errore .= $prenotazione->CheckDisponibilitaPax($DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno", $comuneAndataD,  $comuneAndataP);

				if ($errore != '') {

					$data_prenotazione['PrenotazioneStato'] = 5;
					$StatoPrenotazione = 5;
					$daconfermare = 1;
				}
			}

			$lastidA = $db->insert("RT_Prenotazione", $data_prenotazione);
			$NuovaPrenotazioneId = $lastidA;
			$prenotazione_wizard = new Prenotazione($NuovaPrenotazioneId);
			$prenotazione_wizard->conn = $db;

			if ($lastidA != false) {

				$ok = $prenotazione->SettaId($lastidA);

				$ar1 = 0;
				$TV = 1;
				if ($CorsaRitorno > 0) {
					$ar1 = 1;
					$TV = 2;
				}

				//inserisco i passeggeri
				//$indexPrincipale = 0;

				foreach ($passeggeri as $index => $passeggero) {
					$temp = $passeggero['Nome'];
					$passeggero['Nome'] = ucwords($temp);
					$temp = $passeggero['Cognome'];
					$passeggero['Cognome'] = ucwords($temp);
					/*if ($passeggero[]) {
						$passeggero['Principale'] = 1;
					} else {
						$passeggero['Principale'] = 0;
					}*/

					$passeggero['PrenotazioneId'] = $NuovaPrenotazioneId;

					$passeggero = $storico->operazioni_insert($passeggero, $user);
					unset($passeggero['TipologiaBiglietto']);
					$db->insert("RT_PrenotazionePasseggeri", $passeggero);
				}
 
				$PrenotazioneOld = null;
				$old_prenotazioneid = null;
				$OldPrenotazioneStato = null;

				$arrPrezzi = $prenotazione->GetTipologiaBigliettiPrezzi(1, $NuovaPrenotazioneId, $prenotazione_wizard, $DataSelezionataA, $CorsaAndata, $FermataAndataP, $FermataAndataD, $CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $TV, $arr_biglietti_prenotati, $arr_biglietti_aumento, $arr_biglietti_riduzione, $PrenotazioneOld, $old_prenotazioneid, $OldPrenotazioneStato, $DataSelezionataR, null, null, $tipoTour, $libera, null, null, $data_prenotazione['GestoreIdRef']); 

				// if ($modifica!=1)
				$xx = $prenotazione->GeneraCodiciPrenotazione($NumeroTotalePax);
				$x = $prenotazione->ScriviTbTratte($CorsaAndata, $IdTratteAndata, 'A', $data_prenotazione['GestoreIdRef']);
				// $dataAndata = date('d/m/Y', strtotime($dataAndata));

				$DataPartenza = $dataAndata;
				$x = $prenotazione->ScriviTbPercorso($CorsaAndata, $FermataAndataP, $FermataAndataD, $DataPartenza, 'A', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax, $data_prenotazione['GestoreIdRef']);

				$x = $prenotazione->ScriviTbTariffe($listini_id, $IdTratteAndata, $CorsaRitorno, $FermataAndataP, $FermataAndataD, $DataPartenza, null);
				if(isset($arr_PostoSceltoA) && isset($arr_PostoSceltoAI)) {
					$x = $prenotazione->ScriviTbPostoScelto($arr_PostoSceltoA, $arr_PostoSceltoAI, $CorsaAndata, $DataPartenza);
				}

				if ($CorsaRitorno > 0) {
					$x = $prenotazione->ScriviTbTratte($CorsaRitorno, $IdTratteRitorno, 'R', $data_prenotazione['GestoreIdRef']);
					$DataPartenza = $daraRitorno;
					$x = $prenotazione->ScriviTbPercorso($CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $DataPartenza, 'R', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax, $data_prenotazione['GestoreIdRef']);
					$listini_id_r = $prenotazione->GetListini($IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno);
					// $x=$prenotazione->ScriviTbTariffe($listini_id_r,$IdTratteRitorno,$CorsaRitorno);
					$x = $prenotazione->ScriviTbPostoScelto($arr_PostoSceltoR, $arr_PostoSceltoIR, $CorsaRitorno, $DataPartenza);
				} else
					$CorsaRitorno = 0;

				$abilitazioneprenotazione = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataP);

				$TipoViaggio1 = "Corsa Semplice";
				if ($CorsaRitorno > 0)
					$TipoViaggio1 = "Andata/Ritorno";

				$return = $prenotazione->CreateDettaglioPrenotazione($CorsaAndata, "Andata", $TipoViaggio1, $StatoPrenotazione, $data_prenotazione['GestoreIdRef']);
				if ($CorsaRitorno > 0)
					$return = $prenotazione->CreateDettaglioPrenotazione($CorsaRitorno, "Ritorno", $TipoViaggio1, $StatoPrenotazione, $data_prenotazione['GestoreIdRef']);

				// }

				if ($user->SedeLegale) {

					$data1a['DaFatturare'] = 0;
					$data1a['DaBonificare'] = 0;

					$result = $db->update("RT_PrenotazioneDettaglio", $data1a, "GestoreIdRef=1");
				}


				if (($abilitazioneprenotazione == 1) || ($emetti) && ($daconfermare == 0)) // emissione ticket obbligatorio
					echo ("E@" . $NuovaPrenotazioneId);
				else

					echo ("ok" . '_' . $modifica . '_' . $NuovaPrenotazioneId . '_' . $data_prenotazione['Multi']);
			} else {
				echo ("no1");
				exit();
			}
		} else {
			echo ("no2");
			exit();
		}
		return $NuovaPrenotazioneId;
	}

	public function prenotaWithoutEcho($notaAndata, $notaRitorno, $arr_biglietti_prenotati, $arr_biglietti_riduzione, $arr_biglietti_aumento, $daraRitorno, $corsaRitornoId, $dataAndata, $corsaAndataId, $comunePickup, $fermataPickup, $comuneDropoff, $fermataDropoff, $tipoViaggio, $passeggeri, $clienteNome, $clienteCellulare, $clienteCellulareFamiliare, $clienteEmail, $clienteSessoId, $clienteEta, $StatoPrenotazione = 1, $clienteCellularePrefisso = 39, $canale = 'api')
	{

		global $user;
		$emetti = false;
		$db = $this->conn;
		$prenotazione_wizard = null;
		$PrenotazioneOld = null;
		$old_prenotazioneid = null;
		$OldPrenotazioneStato = null;

		$storico = new StoricoOperazioni();
		$storico->conn = $db;

		$prenotazione = new Prenotazione();
		$prenotazione->conn = $db;

		//recupero TipoTour da corsa
		$sql = "select l.LineaId, l.TipoTour from RT_Linea l
				left join RT_Corsa c on c.LineaId = l.LineaId
				where c.CorsaId = ".$corsaAndataId;
		$tempRowLinea = $db->query_first($sql);		
		$lineaId = $tempRowLinea['LineaId'];
		$TipoTour = $tempRowLinea['TipoTour'];	

		/** inizio recupero dati dalla form **/
		// prelevo i dati del form ed aggiorno tutte le propriet� dell'oggetto
		$TV = $tipoViaggio;
		$temp = $clienteNome;
		$data_prenotazione['ClienteNome'] = ucwords($temp);
		$data_prenotazione['ClienteCellulareFamiliare'] = $clienteCellulareFamiliare;
		$data_prenotazione['ClienteCellularePrefisso'] = $clienteCellularePrefisso;
		$data_prenotazione['ClienteCellulare'] = $clienteCellulare;
		$data_prenotazione['ClienteEmail'] = $clienteEmail;
		$data_prenotazione['ClienteSessoId'] = $clienteSessoId;
		$data_prenotazione['PrenotazioneStato'] = $StatoPrenotazione;
		$data_prenotazione['Multi'] = 0;
		$data_prenotazione['RitornoOpen'] = 0;
		$data_prenotazione['TipoViaggioId'] = $tipoViaggio;
		$data_prenotazione['Canale'] = $canale;
		$data_prenotazione['TipoTour'] = $TipoTour;
		//genero codice prenotazione
		$data_prenotazione['CodicePrenotazione'] = $prenotazione->GetProgressivoCodicePrenotazione();
		$data_prenotazione = $storico->operazioni_insert($data_prenotazione, $user);

		$ritornoOpen = 0;
		//fermate
		$data_fermate['FermataIdAP'] = $fermataPickup;
		$data_fermate['FermataIdAD'] = $fermataDropoff;
		$data_fermate['FermataIdRP'] = null;
		$data_fermate['FermataIdRD'] =  null;
		if ($TV == 2) {
			$sql = "SELECT * FROM RT_Corsa WHERE CorsaId = $corsaRitornoId";
			$tempRowLinea = $db->query_first($sql);
			$lineaIdR = $tempRowLinea['LineaId'];
			$result = $this->getFermateGrafo($corsaRitornoId, $comuneDropoff, $comunePickup, $lineaIdR);
			$data_fermate['FermataIdRP'] = $result['pickup'][0]['FermataId'];
			$data_fermate['FermataIdRD'] =  $result['dropoff'][0]['FermataIdDrop'];


			$sql = "SELECT f.FermataId, f.FermataNome, o.Orario, o.GiorniAggiuntivi FROM RT_Orario o
                            LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
                            WHERE o.CorsaId = $corsaRitornoId AND f.ComuneId = $comuneDropoff";
			$checkFermate = $db->query_first($sql);
			$temp_fermata['FermataId'] = $checkFermate['FermataId'];
			$temp_fermata['FermataOrario'] = $checkFermate['Orario'];
			$temp_fermata['FermataNome'] = $checkFermate['FermataNome'];
			$temp_fermata['Orario'] = $checkFermate['Orario'];
			$temp_fermata['Orario'] = $checkFermate['GiorniAggiuntivi'];
			$arr_fermate[] = $temp_fermata;
			$data_fermate['FermataIdRP'] = $arr_fermate[0]['FermataId'];

			$sql = "SELECT f.FermataId, f.FermataNome, o.Orario, o.GiorniAggiuntivi FROM RT_Orario o
                            LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
                            WHERE o.CorsaId = $corsaRitornoId AND f.ComuneId = $comunePickup";
			$checkFermate = $db->query_first($sql);
			$temp_fermataD['FermataIdDrop'] = $checkFermate['FermataId'];
			$temp_fermataD['FermataOrarioDrop'] = $checkFermate['Orario'];
			$temp_fermataD['FermataNome'] = $checkFermate['FermataNome'];
			$temp_fermataD['Orario'] = $checkFermate['Orario'];
			$arr_fermate_d[] = $temp_fermataD;
			$data_fermate['FermataIdRD'] = $arr_fermate_d[0]['FermataIdDrop'];
		}

		//note
		$PrenotazioneNote[$corsaAndataId . '_1'] = '';
		$PrenotazioneNote[$corsaAndataId . '_2'] = '';
		$PrenotazioneNote[$corsaAndataId . '_3'] = '';
		$PrenotazioneNote[$corsaAndataId . '_4'] = '';
		$PrenotazioneNote[$corsaAndataId . '_5'] = $notaAndata;
		if ($TV == 2) {
			$PrenotazioneNote[$corsaRitornoId . '_1'] = '';
			$PrenotazioneNote[$corsaRitornoId . '_2'] = '';
			$PrenotazioneNote[$corsaRitornoId . '_3'] = '';
			$PrenotazioneNote[$corsaRitornoId . '_4'] = '';
			$PrenotazioneNote[$corsaRitornoId . '_5'] = $notaRitorno;
		}

		$conferma_errori = 0;

		$CorsaAndata = $corsaAndataId;
		$DataSelezionataA = $dataAndata;
		$FermataAndataP = $data_fermate['FermataIdAP'];
		$FermataAndataD = $data_fermate['FermataIdAD'];

		$IdTratteAndata = $prenotazione->GetTratte($CorsaAndata, $FermataAndataP, $FermataAndataD, $DataSelezionataA);
		if (isset($IdTratteAndata) && count($IdTratteAndata) > 0) {
			$listini_id = $prenotazione->GetListini($IdTratteAndata, $FermataAndataP, $FermataAndataD, $CorsaAndata);
		} else {
			$listini_id = array();
			$listini_id['ListinoId'] = 1;
		}

		// print_r($listini_id);
		$TratteComplete = $IdTratteAndata;
		$ListiniCompleti = $listini_id;
		$CorsaRitorno = 0;
		$DataSelezionataR = "";
		if ($data_prenotazione['TipoViaggioId'] == 2) {
			$CorsaRitorno = $corsaRitornoId;
			$DataSelezionataR = $daraRitorno;
			$FermataRitornoP = $data_fermate['FermataIdRP'];
			$FermataRitornoD = $data_fermate['FermataIdRD'];
			$IdTratteRitorno = $prenotazione->GetTratte($CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $DataSelezionataR);
			if (isset($IdTratteRitorno) && count($IdTratteRitorno) > 0) {
				$listini_idr = $prenotazione->GetListini($IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno);
			} else {
				$listini_idr = array();
				$listini_idr['ListinoId'] = 1;
			}
			$c = 0;

			while ($c < sizeof($IdTratteRitorno)) {
				$TratteComplete[] = $IdTratteRitorno[$c];
				$TrattaId = $IdTratteRitorno[$c]['TrattaId'];
				$ListiniCompleti[$TrattaId] = $listini_idr[$TrattaId];
				$c++;
			}
			$c = 0;
		} else {
			$CorsaRitorno = null;
			$DataSelezionataR = null;
			$FermataRitornoP = null;
			$FermataRitornoD = null;
		}
		/** fine recupero dati dalla form **/

		// Check
		$check = true;
		$err = "";


		// verifica da confermare
		$dcr = 0;

		$dca = $prenotazione->GetDaConfermare($IdTratteAndata);
		if ($CorsaRitorno > 0)
			$dcr = $prenotazione->GetDaConfermare($IdTratteRitorno);

		$arr_Fermate[0]['FermataId'] = $FermataAndataP;
		$arr_Fermate[1]['FermataId'] = $FermataAndataD;
		if ($CorsaRitorno > 0) {
			$arr_Fermate[2]['FermataId'] = $FermataRitornoP;
			$arr_Fermate[3]['FermataId'] = $FermataRitornoD;
		}
		$FdaConf = $prenotazione->GetFermateDaConfermare($arr_Fermate);


		//recuper comune fermate
		$sql = "SELECT ComuneId from RT_Fermata where FermataId = $FermataAndataP";
		$comuneAndataP = $db->query_first($sql);
		$comuneAndataP = $comuneAndataP['ComuneId'];
		$sql = "SELECT ComuneId from RT_Fermata where FermataId = $FermataAndataD";
		$comuneAndataD = $db->query_first($sql);
		$comuneAndataD = $comuneAndataD['ComuneId'];

		/*
	         *
	         */
		// ********************************************************************************


		// controllo la validita del numero di posti

		$ar1 = 0;
		if ($CorsaRitorno > 0)
			$ar1 = 1;
		$NumeroTotalePax = $prenotazione->ScriviTbBiglietti($arr_biglietti_prenotati, 0, $arr_biglietti_riduzione, $arr_biglietti_aumento, $TratteComplete, $ListiniCompleti, $FermataAndataP, $FermataAndataD, $DataSelezionataA, $ar1, $IdTratteAndata);

		$err .= $prenotazione->CheckNumeroPax($NumeroTotalePax);

		// Controllo corsa Ritorno se viaggio

		$err .= $prenotazione->CheckDisponibilitaPax($DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata", $comuneAndataP,  $comuneAndataD, $congiunti);
		if ($CorsaRitorno > 0)
			$err .= $prenotazione->CheckDisponibilitaPax($DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno", $comuneAndataD,  $comuneAndataP, $congiunti);

		// Controllo stato della prentoazione con yes no
		if ($data_prenotazione['TipoViaggioId'] == 2)
			$err .= $prenotazione->CheckCoerenzaAR($DataSelezionataA, $DataSelezionataR);

		$TipoPreR = -1;

		$TipoPreAP = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataP);
		$TipoPreAD = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataD);

		if ($CorsaRitorno > 0) {
			$TipoPreRP = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaRitorno, $FermataRitornoP);
			$TipoPreRD = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataRitornoD);
		}


		//controllo del doppio inserimento di una prenotazione per gestire casi anomali
		// get ultima prenotazione per utente
		$sql = "select DataIns from RT_Prenotazione where OdcIdRef=$user->OdcId and OpeIns=$user->OperatoreId order by DataIns desc";

		$row = $db->query_first($sql);
		$DataIns = '';
		if (!empty($row['DataIns'])) {

			$DataIns = $row['DataIns'];
			$datacorrente = date('Y-m-d H:i:s');
			$dt1 = new DT();
			$secondi = $dt1->difference($datacorrente, $DataIns, 'Y-m-d H:i:s', 'second');
			if ($secondi <= 10)
				$err .= $dizionario['biglietto']['mess_err_due_prenotazioni'];
			// echo($secondi);
		}

		if ($CorsaRitorno > 0) {
			$sqA = "SELECT rt_linea.PercorsoId AS PercorsoId
			FROM RT_Corsa AS rt_corsa
			LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
			LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
			WHERE rt_corsa.Cancella = 0
			AND rt_corsa.CorsaId = $CorsaAndata";
			$rowA = $db->query_first($sqA);
			$PercorsoAndataId = $rowA['PercorsoId'];

			$sqR = "SELECT rt_linea.PercorsoId AS PercorsoId
			FROM RT_Corsa AS rt_corsa
			LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
			LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
			WHERE rt_corsa.Cancella = 0
			AND rt_corsa.CorsaId = $CorsaRitorno";
			$rowR = $db->query_first($sqR);
			$PercorsoRitornoId = $rowR['PercorsoId'];

			if ($PercorsoAndataId != $PercorsoRitornoId)
				$err .= $dizionario['biglietto']['mess_err_linee_differenti'];
		}

		// $err="true";
		if ($err != '') 	// chiedo di corregere gli errori
		{
			// 			if($err == "La data del ritorno deve essere successiva alla data di andata!"){
			// 				return '{"code":"105","message":"Invalid journey"}';
			// 			}
			return 'error';
		}
		$tratta_confermare = "salita";
		if ($dcr)
			$tratta_confermare = "discesa";
		//controllo validita' fermate in base all'agenzia
		if (($user->SedeLegale != 1) and ($dca + $dcr + $FdaConf > 0) and ($StatoPrenotazione == 3)) {

			echo ($dizionario['biglietto']['mess_err_fermate_no_confermate']);
			exit();
		}
		$trattedifferenti = false;
		if (isset($modifica) && $modifica == 1) {
			if (((sizeof($oldtratte_a)) != (sizeof($IdTratteAndata))) or ((sizeof($oldtratte_r)) != (sizeof($IdTratteRitorno))))
				$trattedifferenti = true;
			else {
				$qtr = 0;
				while ($qtr < sizeof($oldtratte_a)) {
					if ($oldtratte_a[$qtr]['TrattaId'] != $IdTratteAndata[$qtr]['TrattaId'])
						$trattedifferenti = true;
					$qtr++;
				}
				$qtr = 0;
				if (sizeof($oldtratte_r) > 0) {
					while ($qtr < sizeof($oldtratte_r)) {
						if ($oldtratte_r[$qtr]['TrattaId'] != $IdTratteRitorno[$qtr]['TrattaId'])
							$trattedifferenti = true;
						$qtr++;
					}
				}
			}
		}

		if (($conferma_errori > 0) and (($conferma_errori < 100)) and ($user->SedeLegale = 1)) {
			$daconfermare = 0;
			if ((($dca + $dcr + $FdaConf) > 0)) {

				echo ($dizionario['biglietto']['mess_tratta_da_confermare'] . " $tratta_confermare " . $dizionario['biglietto']['mess_tratta_da_confermare2'] . "##100");
				$data_prenotazione['PrenotazioneStato'] = 2;
				exit();
				$StatoPrenotazione = 2;
				$daconfermare = 1;
			} else
				$trattedifferenti = false;
		}




		if (($data_prenotazione['TipoViaggioId'] == 2) and (!($CorsaRitorno > 0)) and ($ritornoOpen == 0)) {
			echo ($dizionario['biglietto']['mess_no_ritorno']);
			$check = false;
			exit();
		}

		if (!$CorsaAndata > 0) {
			echo ($dizionario['biglietto']['mess_no_andata']);
			$check = false;
			exit();
		}

		if (($conferma_errori < 400) && $emetti) {
			echo ($dizionario['biglietto']['mess_emetti'] . "##400");
			$check = false;
			exit();
		}

		if ($check == true) {

			// verifico se le fermate sono da confermare

			if ($trattedifferenti) {

				if ((($dca + $dcr + $FdaConf) > 0)) {
					$data_prenotazione['PrenotazioneStato'] = 2;
					$StatoPrenotazione = 2;
					$daconfermare = 1;
				}
			}

			if ($conferma_errori == 200) {

				$errore = $prenotazione->CheckDisponibilitaPax($DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata", $comuneAndataP,  $comuneAndataD, $congiunti);

				if ($CorsaRitorno > 0)
					$errore .= $prenotazione->CheckDisponibilitaPax($DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno", $comuneAndataD,  $comuneAndataP, $congiunti);

				if ($errore != '') {

					$data_prenotazione['PrenotazioneStato'] = 5;
					$StatoPrenotazione = 5;
					$daconfermare = 1;
				}
			}


			$lastidA = $db->insert("RT_Prenotazione", $data_prenotazione);
			$NuovaPrenotazioneId = $lastidA;

			if ($lastidA != false) {

				$ok = $prenotazione->SettaId($lastidA);

				$ar1 = 0;
				$TV = 1;
				if ($CorsaRitorno > 0) {
					$ar1 = 1;
					$TV = 2;
				}

				//inserisco i passeggeri
				// 				$indexPrincipale = 0;
				foreach ($passeggeri as $index => $passeggero) {
					$temp = $passeggero['Nome'];
					$passeggero['Nome'] = ucwords($temp);
					$temp = $passeggero['Cognome'];
					$passeggero['Cognome'] = ucwords($temp);
					// 					if ($indexPrincipale == $index) {
					// 						$passeggero['Principale'] = 1;
					// 					} else {
					// 						$passeggero['Principale'] = 0;
					// 					}

					$passeggero['PrenotazioneId'] = $NuovaPrenotazioneId;
					$passeggero = $storico->operazioni_insert($passeggero, $user);
					unset($passeggero['TipologiaBiglietto']);
					$db->insert("RT_PrenotazionePasseggeri", $passeggero);
				}

				
				$OldId = null;
				$PrenotazioneOld = null;
				$old_listino = null;
				$old_listinoR = null;
				$Libera = 0;

				$arrPrezzi = $prenotazione->GetTipologiaBigliettiPrezzi(1, $NuovaPrenotazioneId, $prenotazione_wizard, $DataSelezionataA, $CorsaAndata, $FermataAndataP, $FermataAndataD, $CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $TV, $arr_biglietti_prenotati, $arr_biglietti_aumento, $arr_biglietti_riduzione, $PrenotazioneOld, $old_prenotazioneid, $OldPrenotazioneStato, $DataSelezionataR, $old_listino, $old_listinoR, $TipoTour, $Libera, null, null, $data_prenotazione['GestoreIdRef']);
				//$arrPrezzi = $prenotazione->GetTipologiaBigliettiPrezzi(1, $NuovaPrenotazioneId, $prenotazione_wizard, $DataSelezionataA, $CorsaAndata, $FermataAndataP, $FermataAndataD, $CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $TV, $arr_biglietti_prenotati, $arr_biglietti_aumento, $arr_biglietti_riduzione, $PrenotazioneOld, $old_prenotazioneid, $OldPrenotazioneStato, $DataSelezionataR);
				// if ($modifica!=1)
				$xx = $prenotazione->GeneraCodiciPrenotazione($NumeroTotalePax);
				$x = $prenotazione->ScriviTbTratte($CorsaAndata, $IdTratteAndata, 'A');

				$DataPartenza = $dataAndata;

				$x = $prenotazione->ScriviTbPercorso($CorsaAndata, $FermataAndataP, $FermataAndataD, $DataPartenza, 'A', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax);
				$x = $prenotazione->ScriviTbTariffe($listini_id, $IdTratteAndata, $CorsaRitorno, $FermataAndataP, $FermataAndataD, $DataPartenza, null);
				$arr_PostoSceltoAI = null;
				$arr_PostoSceltoA = null;
				$x = $prenotazione->ScriviTbPostoScelto($arr_PostoSceltoA, $arr_PostoSceltoAI, $CorsaAndata, $DataPartenza);

				if ($CorsaRitorno > 0) {
					$x = $prenotazione->ScriviTbTratte($CorsaRitorno, $IdTratteRitorno, 'R');
					$DataPartenza = $daraRitorno;
					$x = $prenotazione->ScriviTbPercorso($CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $DataPartenza, 'R', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax);
					$listini_id_r = $prenotazione->GetListini($IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno);
					// $x=$prenotazione->ScriviTbTariffe($listini_id_r,$IdTratteRitorno,$CorsaRitorno);
					$x = $prenotazione->ScriviTbPostoScelto($arr_PostoSceltoR, $arr_PostoSceltoIR, $CorsaRitorno, $DataPartenza);
				} else
					$CorsaRitorno = 0;

				$abilitazioneprenotazione = $prenotazione->GetTipologiaPrenotazioneAbilitata($CorsaAndata, $FermataAndataP);

				$TipoViaggio1 = "Corsa Semplice";
				if ($CorsaRitorno > 0)
					$TipoViaggio1 = "Andata/Ritorno";

				$return = $prenotazione->CreateDettaglioPrenotazione($CorsaAndata, "Andata", $TipoViaggio1, $StatoPrenotazione);
				if ($CorsaRitorno > 0)
					$return = $prenotazione->CreateDettaglioPrenotazione($CorsaRitorno, "Ritorno", $TipoViaggio1, $StatoPrenotazione);

				// }
				if ($user->SedeLegale == 1) {

					$data1a['DaFatturare'] = 0;
					$data1a['DaBonificare'] = 0;

					$result = $db->update("RT_PrenotazioneDettaglio", $data1a, "GestoreIdRef=1");
				}


				if (($abilitazioneprenotazione == 1) || ($emetti) && ($daconfermare == 0)) {
					// emissione ticket obbligatorio
					return $NuovaPrenotazioneId;
				} else {
					return $NuovaPrenotazioneId;
				}
			} else
				return "error";
		} else {
			return "error";
		}
		return $NuovaPrenotazioneId;
	}

	public function inviaTicketEmail($prenotazioneId)
	{

		global $user;
		$db = $this->conn;

		$storico = new StoricoOperazioni();
		$storico->conn = $db;
		$data = array();
		$data['InviaTitolo'] = 1;
		$data = $storico->operazioni_update($data, $user);
		$result = $db->update("RT_PrenotazioneTitolo", $data, "PrenotazioneId=" . $prenotazioneId);

		return $result;
	}

	public function annulla($prenotazioneId)
	{
		$db = $this->conn;
		$prenotazione_wizard = new Prenotazione($prenotazioneId);
		$prenotazione_wizard->conn = $db;
		$x = $prenotazione_wizard->AnnullaPrenotazione(4);

		/**controllo congiunti**/
		if (Config::$opzioneCongiunti && Config::$aggiungiPostiCongiunti) {
			$sql = "Select Congiunti from RT_Prenotazione WHERE PrenotazioneId = " . $prenotazioneId;
			$row = $db->query_first($sql);
			if (isset($row['Congiunti']) && $row['Congiunti'] == 1) {
				$db->delete("RT_CorsaPaxTratta", "PrenotazioneId = " . $prenotazione_wizard->Id);
			}
		}
		/**fine controllo congiunti**/

		/**controllo disponibilita posti**/
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
		$DatiGeneraliPercorsoA = $prenotazione_wizard->DatiGeneraliPercorso;
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
		$DatiGeneraliPercorsoR = $prenotazione_wizard->DatiGeneraliPercorso;
		$dispo = new Disponibilita();
		$dispo->aggiornaDisponibilita($DatiGeneraliPercorsoA['CorsaId'], $DatiGeneraliPercorsoA['CorsaDataPartenza']);
		if (isset($DatiGeneraliPercorsoR['CorsaDataPartenza'])) {
			$dispo->aggiornaDisponibilita($DatiGeneraliPercorsoR['CorsaId'], $DatiGeneraliPercorsoR['CorsaDataPartenza']);
		}
		/**fine disponibilita posti**/


		return $prenotazioneId;
	}

	// Funzione per emettere una ricevuta fiscale tramite Fiscal Gateway
	public function fiscalGatewayEmettiRicevuta($movimentoId){
		global $db, $user;
		// Istanzia il servizio Fiscal Gateway con i parametri di configurazione
		$service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);
		
		// Recupera il movimento di pagamento dalla tabella RT_PrenotazioneMovimento
		$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
		$movimento = $db->query_first($sql);
		
		// Recupera la prenotazione associata al movimento
		$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
		$prenotazione = $db->query_first($sql);
		
		// Recupera il prossimo identificativo ordine per lo scontrino (incrementale)
		$sql = "SELECT MAX(ScontrinoId) AS max FROM RT_PrenotazioneMovimento WHERE ScontrinoTipo = 1";
		$orderRow = $db->query_first($sql);
		if(isset($orderRow['max'])) {
			$orderId = intval($orderRow['max']) + 1;
		} else {
			$orderId = 13; // valore di default se non esistono scontrini precedenti
		}
		$orderId = strval($orderId);
		
		// Determina il tipo di pagamento da inviare al Fiscal Gateway in base al campo PagamentoTipoId
		switch ($movimento['PagamentoTipoId']) {
			case 1:
				$paymentMethodsType = 'CASH'; // Contanti
				break;
			case 2:
				$paymentMethodsType = 'CARD'; // Postapay
				break;
			case 3:
				$paymentMethodsType = 'CARD'; // Carta di credito su POS fisico
				break;
			case 4:
				$paymentMethodsType = 'BANK_TRANSFER'; // Bonifico Bancario
				break;
			case 5:
				$paymentMethodsType = 'CARD'; // PayPal 
				break;
			case 6:
            	$paymentMethodsType = 'CARD'; // Agenzia
			case 7:
				$paymentMethodsType = 'CASH'; // A bordo 
				break;
			case 12:
				$paymentMethodsType = 'CARD'; // Coupon 
				break;
			case 22:
				$paymentMethodsType = 'CARD'; // Stripe 
				break;
			case 23:
				$paymentMethodsType = 'CARD'; // Pagamento in hotel 
				break;
			default:
				$paymentMethodsType = 'CASH'; // Default per metodi non mappati
		}
		
		// Calcola l'importo in centesimi (intero)
		$amount = (int) round(floatval($movimento['ImportoPagato']) * 100);

		// Recupera il codice prodotto (codice prenotazione)
		$productId = $prenotazione['CodicePrenotazione'];

		// Invio della richiesta di emissione ricevuta al Fiscal Gateway
		$result = $service->postBillReceipt($orderId, $paymentMethodsType, $amount, $productId, Config::$fiscalGatewayVAT);

		// Se la risposta è positiva, aggiorna i dati dello scontrino sul movimento
		if (isset($result['status_code'], $result['response']['success']) &&
			$result['status_code'] === 200 &&
			$result['response']['success'] === true) {
			// Aggiorna i campi relativi allo scontrino nella tabella RT_PrenotazioneMovimento
			$data = [
				'ScontrinoId' => $orderId,
				'ScontrinoData' => date("Y-m-d H:i:s"),
				'ScontrinoTipo' => '1',
			];
			$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
			return true;
		} else {
			// In caso di errore restituisce false
			return false;
		}
	}

	public function inviaScontrinoEmail($movimentoId)
	{
		global $db, $user;
		$db = $this->conn;
		
		// Prepara i dati per aggiornare la notifica dello scontrino
		$dataMovimento = array();
		$dataMovimento['ScontrinoNotifica'] = 1;
		
		// Aggiorna il campo ScontrinoNotifica a 1 per il movimento specificato
		$updInviaTitolo = $db->update('RT_PrenotazioneMovimento', $dataMovimento, "PrenotazioneMovimentoId = ".$movimentoId);
		
		// Restituisce sempre true
		return true;
	}

	public function incassoABordo($prenotazioneId, $autistaId, $pagamentoId = null)
	{
		global $db, $user;
		$db = $this->conn;
		$storico = new StoricoOperazioni();
		$storico->conn = $db;

		$sql = 'SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ' . $prenotazioneId;
		$prenotazione = $db->query_first($sql);

		$gestoreIdRef = $prenotazione['GestoreIdRef'];
		
		$sql = 'SELECT sum(ImportoPagato) as totale FROM RT_PrenotazioneMovimento WHERE PrenotazioneId = ' . $prenotazioneId ." AND TipoMovimento = 'I' AND Stato = 1 AND Cancella = 0";
		$rowImporto = $db->query_first($sql);
		if(isset($rowImporto['totale'])) {
			$importoPagato = $rowImporto['totale'];
		} else {
			$importoPagato = 0;
		}

		$data['PrenotazioneId'] = $prenotazioneId;
		$data['PagamentoTipoId'] = $pagamentoId ? $pagamentoId : 7;
		$data['TipoMovimento'] = 'I';
		$data['Causale'] = 'Pagamento a bordo';
		$data['Data'] = date('Y-m-d H:i:s');
		$data['Importo'] = $prenotazione['TotaleDaPagare'] - $importoPagato;
		$data['Supplemento'] = 0;
		$data['DataPagamento'] = date('Y-m-d H:i:s');
		$data['ImportoPagato'] = $prenotazione['TotaleDaPagare'] - $importoPagato;
		$data = $storico->operazioni_insert($data, $user);
		$data['GestoreIdRef'] = $gestoreIdRef;
		$lastId = $db->insert("RT_PrenotazioneMovimento", $data);

		$dataPrenotazione['TotaleResiduo'] = 0;
		$dataPrenotazione['TotalePagato'] = $prenotazione['TotaleDaPagare'];
		$dataPrenotazione['TotaleDaPagare'] = $prenotazione['TotaleDaPagare'];
		$dataPrenotazione = $storico->operazioni_update($dataPrenotazione, $user);
		$result = $db->update("RT_Prenotazione", $dataPrenotazione, "PrenotazioneId=$prenotazioneId");

		return $lastId;
	}

	public function EmettiABordo($prenotazioneId, $autistaId)
	{
		global $db, $user;

		$sql = "Select * from RT_Prenotazione Where PrenotazioneId = $prenotazioneId";
		$rowPrenotazione = $db->query_first($sql);
		$pId = $rowPrenotazione['PrenotazioneId'];

		$gestoreIdRef = $rowPrenotazione['GestoreIdRef'];

		$prenotazione_wizard = new Prenotazione($prenotazioneId);
		$db = new Database();
		$db->connect();

		$PrenotazioneId = $prenotazione_wizard->Id;
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
		$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];
		$prenotazione_wizard->conn = $db;
		if (!$DatiGeneraliArr['Multi']) {
			$prenotazione_wizard->EmettiBiglietti();
		} else {
			$sql = "SELECT PrenotazioneId FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $DatiGeneraliArr['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $DatiGeneraliArr['PrenotazioneStato'] . " AND Cancella = 0 AND OdcIdRef = " . $user->OdcId;
			$prenotazioni = $db->fetch_array($sql);

			foreach ($prenotazioni as $prenotazione) {
				$prenotazioneObj = new Prenotazione($prenotazione['PrenotazioneId']);
				$prenotazioneObj->conn = $db;
				$prenotazioneObj->EmettiBiglietti();
			}
		}


		$findAutistaSql = "SELECT ProvvigioneVendite FROM RT_Autisti WHERE AutistiId = $autistaId";
		$autista = $db->query_first($findAutistaSql);
		$storico = new StoricoOperazioni();


		$provvigioneVendite = floatval($autista['ProvvigioneVendite']);
		if ($provvigioneVendite > 0) {
			$prenotazioneTitoloSql = "SELECT PrenotazioneTitoloId, ImportoTitolo FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = $prenotazioneId";
			$prenotazioneTitolo = $db->fetch_array($prenotazioneTitoloSql);

			foreach ($prenotazioneTitolo as $titolo) {
				$ImportoTitolo = floatval($titolo['ImportoTitolo']);
				$ImportoAutista = ($ImportoTitolo * $provvigioneVendite) / 100;
				$dataProvvigioneAutista = [];
				$dataProvvigioneAutista['PrenotazioneTitoloId'] = $titolo['PrenotazioneTitoloId'];
				$dataProvvigioneAutista['PercentualeAutista'] = $provvigioneVendite;
				$dataProvvigioneAutista['AutistaId'] = $autistaId;
				$dataProvvigioneAutista['ImportoAutista'] = $ImportoAutista;
				$dataProvvigioneAutista['daBonificare'] = $ImportoTitolo - $ImportoAutista;
				$dataProvvigioneAutista = $storico->operazioni_insert($dataProvvigioneAutista, $user);

				$db->insert('RT_PrenotazioneTitoloProvvigioneAutista', $dataProvvigioneAutista);
			}
		}

		$output = array();
		$output['prenotazioneId'] = $PrenotazioneId;
		$output['corsaId'] = $CorsaId;

		return $output;
	}
}
