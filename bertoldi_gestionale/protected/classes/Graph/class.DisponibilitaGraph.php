<?php
// Impostazioni opzionali per la visualizzazione degli errori
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ERROR);
// ini_set('max_execution_time', 36000);

// Inclusione delle classi necessarie
include_once($classespath_ . "Graph/Graph.php");
include_once($classespath_ . "class.PrenotazioneDettaglio.php");
include_once($classespath_ . "class.Flotta.php");
include_once($classespath_ . "class.GestioneOttimizzataFlotta.php");
include_once($classespath_ . "class.Fermata.php");
include_once($classespath_ . "Graph/class.LineaGraph.php");

// Classe che rappresenta un nodo fermata nel grafo della disponibilità
class NodoFermataDisp extends Node {
	// public $salite;
	// public $discese;
	public $destinazioni;
	public $gruppo;
	public $biglietti;
	public $bigliettiSalite;
	public $busPartenza;
	public $busArrivo;
	public $importanza;
	public $totDiscese;
	public $flag;

	// Costruttore
	function __construct($idNode) {
		parent::__construct($idNode);
		$salite = 0;
		$discese = 0;
		$this->destinazioni = array();
		$this->gruppo = new GruppoDisp();
		$this->biglietti = array();
		$this->busPartenza = array();
		$this->busArrivo = array();
		$this->importanza = array();
		$this->totDiscese = 0;
		$this->flag = false;
	}
}

// Classe che rappresenta un gruppo di passeggeri
class GruppoDisp {
	public $passeggeri; // array <destinazione, num. passeggeri>
	public $totalePasseggeri;
	public $posizione;
	public $posizionePrecedente;

	// Costruttore
	function __construct($idPosizione = null) {
		$this->passeggeri = array();
		$this->totalePasseggeri = 0;
		$this->posizione = $idPosizione;
		$this->posizionePrecedente = array();
	}

	// Verifica se il gruppo è nuovo
	public function isNew() {
		if (isset($this->posizione)) {
			return true;
		} else {
			return false;
		}
	}
}

// Classe che rappresenta un bus
class BusDisp {
	public $id;
	public $comuni;
	public $percorso;
	public $nome;

	// Costruttore
	function __construct($id) {
		$this->id = $id;
		$this->percorso = array();
		$this->comuni = array();
	}
}

// Classe principale per la gestione della disponibilità dei posti
class DisponibilitaGraph {

	public $idLinea;
	public $conn;
	public $graph;
	public $flotta;
	public $flottaNew;
	public $postiBus;
	public $gruppiDispo;
	public $idCorsa;
	public $dataPartenza;

	// Costruttore
	function __construct($idLinea, $idCorsa, $corsaDataPartenza, $conn, $postiMax = 100, $crone = true) {
		$this->idCorsa = $idCorsa;
		$this->dataPartenza = $corsaDataPartenza;
		$this->gruppiDispo = array();
		$this->postiBus = $postiMax;
		unset($row);
		$this->flotta = array();

		// Creazione del grafo
		$this->graph = new Graph();
		$this->idLinea = $idLinea;
		$this->conn = $conn;
		$db = $this->conn;

		// Recupero delle tratte
		$sql = "SELECT DISTINCT t.TrattaId
			FROM RT_Tratta t
			WHERE t.Stato = 1 AND
			t.Cancella = 0 AND
			t.LineaId = $this->idLinea AND
			t.DaConfermare = 0
			ORDER BY TrattaPeso ASC";
		$rowsTratte = $db->fetch_array($sql);

		// Ciclo sulle tratte per recuperare le fermate
		foreach ($rowsTratte as $key => $tratta) {
			$sql = "SELECT ComuneId, KmInizioTratta FROM RT_Fermata as f
				WHERE f.Stato = 1 AND
				f.Cancella = 0 AND
				f.IsDaConfermare = 0 AND
				f.IsBlackList = 0 AND
				f.TrattaId = " . $tratta['TrattaId'] . "
				ORDER BY f.FermataPeso";
			$rowsFermate = $db->fetch_array($sql);

			// Aggiunta dei nodi al grafo
			foreach ($rowsFermate as $key2 => $fermata) {
				$nodoFermata = new NodoFermataDisp($fermata['ComuneId']);
				$this->graph->addNode($nodoFermata);
			}

			// Connessione dei nodi nel grafo
			$i = 0;
			while ($i < (sizeof($rowsFermate) - 1)) {
				$temp1 = $rowsFermate[$i];
				$temp2 = $rowsFermate[$i + 1];
				if ($temp2['ComuneId'] != $temp1['ComuneId']) {
					if (!array_key_exists($temp2['ComuneId'] . '-' . $temp1['ComuneId'], $this->graph->edges) &&
						!array_key_exists($temp1['ComuneId'] . '-' . $temp2['ComuneId'], $this->graph->edges)) {
						$this->graph->connectNodes(
							$temp1['ComuneId'],
							$temp2['ComuneId'],
							0,
							$temp2['KmInizioTratta'] - $temp1['KmInizioTratta']
						);
						$this->graph->edges[$temp1['ComuneId'] . '-' . $temp2['ComuneId']]->info3[$tratta['TrattaId']] = $tratta['TrattaId'];
					}
				}
				$i++;
			}
		}

		// Se richiesto, inizializza salite e fermate
		if ($crone) {
			$saliteTotali = 0;
			$disceseTotali = 0;
			foreach ($this->graph->nodes as $node) {
				// Query per le salite
				$sql = "SELECT
						RT_PrenotazioneDettaglio.PrenotazioneNumero, RT_PrenotazionePercorso.ComuneDiscesaId
						FROM
						RT_PrenotazionePercorso
						INNER JOIN RT_Prenotazione ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						INNER JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazioneDettaglio.ComunePartenza=RT_PrenotazionePercorso.ComuneSalita AND RT_PrenotazioneDettaglio.PrenotazioneId=RT_Prenotazione.PrenotazioneId)
						INNER JOIN RT_AppPrenotazioneStato ON (RT_AppPrenotazioneStato.PrenotazioneStatoId = RT_Prenotazione.PrenotazioneStato)
						LEFT JOIN RT_TipologiaBiglietto ON (RT_PrenotazioneDettaglio.TipologiaBiglietto = RT_TipologiaBiglietto.TipologiaBiglietto)
						WHERE
						RT_PrenotazionePercorso.CorsaId=$idCorsa
						AND RT_PrenotazionePercorso.CorsaDataPartenza='$corsaDataPartenza'
						AND RT_PrenotazionePercorso.LineaId=$idLinea
						AND RT_PrenotazionePercorso.ComuneSalitaId=$node->id
						AND (RT_AppPrenotazioneStato.OccupaPosti = 1
							and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
							and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1))
						and RT_TipologiaBiglietto.OccupaPosto = 1
						GROUP BY RT_PrenotazioneDettaglio.PrenotazioneDettaglioId";
				$rows = $db->fetch_array($sql);

				$node->salite = 0;
				foreach ($rows as $row) {
					if (array_key_exists($row['ComuneDiscesaId'], $this->graph->nodes)) {
						$node->salite++;
						$saliteTotali++;
						if (array_key_exists($row['ComuneDiscesaId'], $node->destinazioni)) {
							$node->destinazioni[$row['ComuneDiscesaId']] = $node->destinazioni[$row['ComuneDiscesaId']] + 1;
						} else {
							$node->destinazioni[$row['ComuneDiscesaId']] = 1;
						}
					}
				}

				// Query per le discese
				$sql = "SELECT
						RT_PrenotazioneDettaglio.PrenotazioneNumero
						FROM
						RT_PrenotazionePercorso
						INNER JOIN RT_Prenotazione ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						INNER JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazioneDettaglio.ComunePartenza=RT_PrenotazionePercorso.ComuneSalita AND RT_PrenotazioneDettaglio.PrenotazioneId=RT_Prenotazione.PrenotazioneId)
						INNER JOIN RT_AppPrenotazioneStato ON (RT_AppPrenotazioneStato.PrenotazioneStatoId = RT_Prenotazione.PrenotazioneStato)
						WHERE
						RT_PrenotazionePercorso.CorsaId=$idCorsa 
						AND RT_PrenotazionePercorso.CorsaDataPartenza='$corsaDataPartenza'
						AND RT_PrenotazionePercorso.LineaId=$idLinea
						AND RT_PrenotazionePercorso.ComuneDiscesaId=$node->id
						AND (RT_AppPrenotazioneStato.OccupaPosti = 1
							and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
							and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1))";
				$rowsDiscese = $db->fetch_array($sql);
				$node->discese = sizeof($rowsDiscese);
				$disceseTotali += sizeof($rowsDiscese);

				// Gestione biglietti
				foreach ($rows as $row) {
					if (isset($row['ComuneDiscesaId'])) {
						if (!isset($node->biglietti[$row['ComuneDiscesaId']])) {
							$node->biglietti[$row['ComuneDiscesaId']] = array();
							$node->bigliettiSalite[$row['ComuneDiscesaId']] = array();
						}
						$node->biglietti[$row['ComuneDiscesaId']][] = $row['PrenotazioneNumero'];
						$node->bigliettiSalite[$row['ComuneDiscesaId']][] = $row['PrenotazioneNumero'];
					}
				}
			}
			unset($rows);
		}

		// Calcolo dei discendenti dei nodi
		$this->graph->calculateDescent2();

		// Calcolo dei gruppi e creazione della flotta
		if ($crone) {
			$this->calcolaGruppi();

			$edgeInseriti = array();
			$id = 0;
			foreach ($rowsTratte as $key => $tratta) {
				$bus = new Bus($id);
				$bus->comuni = array();
				$bus->percorso = array();

				// Selezione dei comuni per tratta
				$sql = "SELECT ComuneId, KmInizioTratta FROM RT_Fermata as f
					WHERE f.Stato = 1 AND
					f.Cancella = 0 AND
					f.IsDaConfermare = 0 AND
					f.IsBlackList = 0 AND
					f.TrattaId = " . $tratta['TrattaId'] . "
					ORDER BY f.FermataPeso";
				$rowsFermate = $db->fetch_array($sql);

				$comuni = array();
				$inizio = null;
				$i = 0;
				$passeggeri = false;
				while ($i < (sizeof($rowsFermate) - 1)) {
					$temp1 = $rowsFermate[$i];
					$temp2 = $rowsFermate[$i + 1];
					$edge = $temp1['ComuneId'] . '-' . $temp2['ComuneId'];

					if (!array_key_exists($edge, $edgeInseriti)) {
						$edgeInseriti[$edge] = $edge;
						$temp['comune'] = $temp1['ComuneId'];
						$temp['passeggeri'] = array();
						if (array_key_exists($edge, $this->graph->edges)) {
							foreach ($this->graph->edges[$edge]->info4 as $key => $value) {
								$temp['passeggeri'][$key] = $value;
							}
							$bus->comuni[] = $temp;
							$bus->percorso[] = $edge;
							if (count($this->graph->edges[$edge]->info4) > 0) {
								$passeggeri = true;
							}
						}
					} else {
						$temp['comune'] = $temp1['ComuneId'];
						$temp['passeggeri'] = array();
						$bus->comuni[] = $temp;
						$bus->percorso[] = $edge;
					}
					$i++;
				}

				// Se ci sono passeggeri, aggiungi il bus alla flotta
				if ($passeggeri) {
					$bus->id = $id;
					$bus->nome = "";
					$this->flottaNew[] = $bus;
					while ($i < (sizeof($rowsFermate) - 1)) {
						$temp1 = $bus->comuni[$i]['comune'];
						if (isset($rowsFermate[$i + 1])) {
							$temp2 = $rowsFermate[$i + 1]['comune'];
						} else {
							$temp2 = null;
						}
						$this->graph->nodes[$temp1]->busPartenza[$id] = $id;
						if (isset($temp2)) {
							$this->graph->nodes[$temp2['ComuneId']]->busArrivo[$id] = $id;
						}
					}
				}
				$id++;
			}
			$this->flotta = $this->flottaNew;

			// Sistemazione dei bus
			if (isset($this->flotta)) {
				foreach ($this->flotta as $id => $bus) {
					if (count($bus->percorso) == 1) {
						$bus->comuni[1] = $bus->comuni[0];
						$temp['comune'] = $this->graph->edges[$bus->percorso[0]]->nodeA;
						$temp['passeggeri'] = $this->graph->edges[$bus->percorso[0]]->info4;
						$bus->comuni[0] = $temp;

						$this->graph->nodes[$this->graph->edges[$bus->percorso[0]]->nodeA]->busPartenza[$id] = $id;
						$this->graph->nodes[$this->graph->edges[$bus->percorso[0]]->nodeB]->busArrivo[$id] = $id;
					}
				}

				foreach ($this->flotta as $bus) {
					$ultimaFermata = sizeof($bus->comuni);
					$deposito = 0;
					$countDepositi = 0;
					foreach ($bus->comuni[$ultimaFermata - 1]['passeggeri'] as $ultimoComune => $passeggeri) {
						$deposito = $ultimoComune;
						$countDepositi++;
					}
					if (array_key_exists($deposito, $this->graph->nodes[$bus->comuni[$ultimaFermata - 1]['comune']]->children)) {
						if (intval($bus->comuni[count($bus->comuni) - 1]['comune']) == intval($bus->comuni[0]['comune'])) {
							$ultimaFermata = $ultimaFermata - 1;
						}
						$bus->comuni[$ultimaFermata]['comune'] = $deposito;
						$bus->comuni[$ultimaFermata]['passeggeri'] = array();
						$bus->percorso[$ultimaFermata] = $bus->comuni[$ultimaFermata - 1]['comune'] . "-" . $deposito;
					} else {
						$ultimaTratta = sizeof($bus->percorso) - 1;
						$deposito = $this->graph->edges[$bus->percorso[$ultimaTratta]]->nodeB;
						$bus->comuni[$ultimaFermata]['comune'] = $deposito;
						$bus->comuni[$ultimaFermata]['passeggeri'] = $this->graph->edges[$bus->percorso[$ultimaTratta]]->info4;
					}
				}
			}
			// Fine sistemazione bus
		}
	}

	// Restituisce il numero di posti disponibili tra due fermate
	public function getPostiDisponibili($pickup, $dropoff, $maxPosti = 49) {
		$db = $this->conn;
		$sql = "select * from RT_DisponibilitaPostiCron where CorsaId = $this->idCorsa and DataPartenza = '$this->dataPartenza'";
		$row = $this->conn->fetch_array($sql);
		if (count($row) > 0) {
			foreach ($this->graph->nodes as $k => $n) {
				$this->graph->nodes[$k]->flag = 0;
			}
			$postiOccupati = $this->getPosti($pickup, $dropoff);
			if ($postiOccupati < $maxPosti) {
				$tot1 = $postiOccupati;
				$sql = "select DisponibilitaPostiId from RT_DisponibilitaPostiCron 
					where CorsaId = $this->idCorsa and DataPartenza = '$this->dataPartenza' and
					Comune = $pickup";
				$rowP = $this->conn->query_first($sql);
				$IdPickup = $rowP['DisponibilitaPostiId'];

				$sql = "select DisponibilitaPostiId from RT_DisponibilitaPostiCron
					where CorsaId = $this->idCorsa and DataPartenza = '$this->dataPartenza' and
					Comune = $dropoff";
				$rowD = $this->conn->query_first($sql);
				$IdDropoff = $rowD['DisponibilitaPostiId'];

				$sql = "select * from RT_DisponibilitaPostiCron 
					where Posti = $postiOccupati and CorsaId = $this->idCorsa and DataPartenza = '$this->dataPartenza'
					and DisponibilitaPostiId >= $IdPickup and DisponibilitaPostiId <= $IdDropoff
					order by PesoTratta desc";
				$row2 = $this->conn->query_first($sql);
				$peso = $row2['PesoTratta'];
				$km = $row2['KmInizioTratta'];
				$trattaId = $row2['TrattaId'];

				$sql = "SELECT Max(Posti) as postiM, c.* FROM RT_DisponibilitaPostiCron c 
					where CorsaId = $this->idCorsa and DataPartenza = '$this->dataPartenza' 
					and PesoTratta = $peso 
					and DisponibilitaPostiId >= $IdPickup and DisponibilitaPostiId <= $IdDropoff
					and c.Posti > 0 and c.Posti<>". $tot1 ." and TrattaId <> $trattaId
					group by TrattaId 
					order by postiM desc";
				$row3 = $this->conn->fetch_array($sql);
				$postiOccupatiTratta = 0;
				$km = 0;
				foreach ($row3 as $num => $val) {
					if ($val['postiM'] < $tot1 / 2) {
						$sql = "SELECT Posti, KmInizioTratta FROM RT_DisponibilitaPostiCron c
									where CorsaId = ".$this->idCorsa." and DataPartenza = '".$this->dataPartenza."'
									and TrattaId  = ".$val['TrattaId']."
									and c.Posti = ".$val['postiM']."
									group by TrattaId";
						$row4 = $db->fetch_array($sql);
						if ($row4[0]['KmInizioTratta'] > $km - 50 && $row4[0]['KmInizioTratta'] < $km + 50)
							$postiOccupatiTratta += $row4[0]['Posti'];
					}
				}
				$postiOccupati += $postiOccupatiTratta;
			}
		} else {
			$postiOccupati = 10000;
		}
		return $postiOccupati;
	}

	// Restituisce il numero di posti per un nodo e una fermata di discesa
	function getPosti($nodoId, $dropoff) {
		$this->graph->nodes[$nodoId]->flag = 1;
		$sql = "select Posti from RT_DisponibilitaPostiCron where CorsaId = $this->idCorsa and DataPartenza = '$this->dataPartenza' and Comune = $nodoId";
		$row = $this->conn->query_first($sql);
		$posti = $row['Posti'];
		if ($this->graph->nodes[$nodoId]->isLeaf || $nodoId == $dropoff) {
			return $posti;
		} else {
			$postiC = 0;
			foreach ($this->graph->nodes[$nodoId]->children as $k => $c) {
				if ($this->graph->nodes[$k]->flag == 0 && !array_key_exists($nodoId, $this->graph->nodes[$k]->descents)) {
					if (array_key_exists($dropoff, $this->graph->nodes[$k]->descents)) {
						$temp = $this->getPosti($k, $dropoff);
						if ($temp > $postiC) {
							$postiC = $temp;
						}
					}
				}
			}
			if ($postiC > $posti) {
				return $postiC;
			} else {
				return $posti;
			}
		}
	}

	// Calcola i gruppi di passeggeri nel grafo
	private function calcolaGruppi() {
		$listaGruppi = array();
		foreach ($this->graph->nodes as $node) {
			if (isset($node) && isset($node->salite) && $node->salite > 0) {
				$this->graph->nodes[$node->id]->gruppo = new GruppoDisp($node->id);
				$gruppo = $this->graph->nodes[$node->id]->gruppo;
				$listaGruppi[] = $this->graph->nodes[$node->id]->gruppo;
			}
		}

		// Calcolo iterativo dei gruppi
		$ii = 0;
		while (sizeof($listaGruppi) > 0) {
			$ii++;
			if ($ii > 100) {
				break;
			}
			$gruppoAvanza = $this->getProssimoGruppo($listaGruppi);
			if ($gruppoAvanza == null) {
				break;
			}
			$this->gruppiDispo[$gruppoAvanza->posizione] = $gruppoAvanza;

			// Gestione discesa
			if (array_key_exists($gruppoAvanza->posizione, $gruppoAvanza->passeggeri)) {
				$gruppoAvanza->passeggeri[$gruppoAvanza->posizione] = 0;
				unset($gruppoAvanza->passeggeri[$gruppoAvanza->posizione]);
				$gruppoAvanza->totalePasseggeri = $gruppoAvanza->totalePasseggeri - $this->graph->nodes[$gruppoAvanza->posizione]->discese;
			}

			// Gestione salita
			if (isset($this->graph->nodes[$gruppoAvanza->posizione]->destinazioni) && is_array($this->graph->nodes[$gruppoAvanza->posizione]->destinazioni)) {
				foreach ($this->graph->nodes[$gruppoAvanza->posizione]->destinazioni as $destinazione => $num) {
					if (array_key_exists($destinazione, $gruppoAvanza->passeggeri)) {
						$gruppoAvanza->passeggeri[$destinazione] = $gruppoAvanza->passeggeri[$destinazione] + $num;
					} else {
						$gruppoAvanza->passeggeri[$destinazione] = $num;
					}
					$gruppoAvanza->totalePasseggeri = $gruppoAvanza->totalePasseggeri + $num;
				}
			}

			$this->gruppiDispo[$gruppoAvanza->posizione] = $gruppoAvanza;

			$nuoviGruppi = array();

			// Divisione per destinazione
			foreach ($gruppoAvanza->passeggeri as $destinazione => $num) {
				$privata = 0;
				$prossimaFermata = array();
				if(isset($this->graph->nodes[$gruppoAvanza->posizione]) && isset($this->graph->nodes[$gruppoAvanza->posizione]->children)) {
					foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child) {
						if (strcmp($destinazione, $child) == 0) {
							$prossimaFermata[$child] = $child;
						} else {
							if (in_array($destinazione, $this->graph->nodes[$child]->descents)) {
								if ($privata == 0) {
									$privata = 1;
									$prossimaFermata[$child] = $child;
								} else if ($privata == 1) {
									$privata = 2;
									$prossimaFermata[$child] = $child;
								}
							}
						}
					}
				}
				$nextNode = array_shift($prossimaFermata);
				unset($prossimaFermata);
				unset($privata);
				unset($min);

				if (isset($nextNode) && $nextNode != '') {
					if (!isset($this->graph->nodes[$nextNode]->gruppo)) {
						$this->graph->nodes[$nextNode]->gruppo = new GruppoDisp($nextNode);
					}
					if (!array_key_exists($destinazione, $this->graph->nodes[$nextNode]->gruppo->passeggeri)) {
						$this->graph->nodes[$nextNode]->gruppo->passeggeri[$destinazione] = $num;
					} else {
						$this->graph->nodes[$nextNode]->gruppo->passeggeri[$destinazione] = $this->graph->nodes[$nextNode]->gruppo->passeggeri[$destinazione] + $num;
					}
					$this->graph->nodes[$nextNode]->gruppo->totalePasseggeri = $this->graph->nodes[$nextNode]->gruppo->totalePasseggeri + $num;
					if (isset($this->graph->nodes[$nextNode]->biglietti[$destinazione])) {
						$this->graph->nodes[$nextNode]->biglietti[$destinazione] = array_merge(
							$this->graph->nodes[$nextNode]->biglietti[$destinazione],
							$this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione]
						);
					} else {
						$this->graph->nodes[$nextNode]->biglietti[$destinazione] = $this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione];
					}
					$this->graph->nodes[$nextNode]->gruppo->posizionePrecedente[$gruppoAvanza->posizione] = $gruppoAvanza->posizione;
					$this->graph->edges[$gruppoAvanza->posizione . "-" . $nextNode]->info2 = $this->graph->edges[$gruppoAvanza->posizione . "-" . $nextNode]->info2 + $num;
					$this->graph->edges[$gruppoAvanza->posizione . "-" . $nextNode]->info = array_merge(
						$this->graph->edges[$gruppoAvanza->posizione . "-" . $nextNode]->info,
						$this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione]
					);
					if (!isset($this->graph->edges[$gruppoAvanza->posizione . "-" . $nextNode]->info4[$destinazione])) {
						$this->graph->edges[$gruppoAvanza->posizione . "-" . $nextNode]->info4[$destinazione] = array();
					}
					$this->graph->edges[$gruppoAvanza->posizione . "-" . $nextNode]->info4[$destinazione] = array_merge(
						$this->graph->edges[$gruppoAvanza->posizione . "-" . $nextNode]->info4[$destinazione],
						$this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione]
					);
					$nuoviGruppi[$nextNode] = $nextNode;
				}
				unset($nextNode);
			}

			// Organizzazione fermata successiva
			if(isset($this->graph->nodes[$gruppoAvanza->posizione]) && isset($this->graph->nodes[$gruppoAvanza->posizione]->children)) {
				foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child) {
					if (isset($this->graph->nodes[$child]) && $this->graph->nodes[$child]->salite > 0) {
						if (!isset($this->graph->nodes[$child]->gruppo)) {
							$this->graph->nodes[$child]->gruppo = new GruppoDisp($child);
						}
						$nuoviGruppi[$child] = $child;
					}
				}
				foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child) {
					if ($this->graph->nodes[$child]->salite > 0) {
						if (!isset($this->graph->nodes[$child]->gruppo)) {
							$this->graph->nodes[$child]->gruppo = new GruppoDisp($child);
						}
						$nuoviGruppi[$child] = $child;
					}
				}
			}
			

			// Caricamento in lista
			foreach ($nuoviGruppi as $key => $nodo) {
				if (!in_array($this->graph->nodes[$nodo]->gruppo, $listaGruppi)) {
					array_push($listaGruppi, $this->graph->nodes[$nodo]->gruppo);
				}
			}
			unset($nuoviGruppi);
			unset($this->graph->nodes[$gruppoAvanza->posizione]->gruppo);
			unset($this->graph->nodes[$gruppoAvanza->posizione]->biglietti);
		}
	}

	// Restituisce il prossimo gruppo da processare
	private function getProssimoGruppo(&$listaGruppi) {
		$gruppo = null;
		$jj = 0;
		while (!isset($gruppo)) {
			$jj++;
			if ($jj > 100) {
				return null;
			}
			$gruppo = array_pop($listaGruppi);
			if (!$this->findPosizione($gruppo, $listaGruppi)) {
				return $gruppo;
			} else {
				array_unshift($listaGruppi, $gruppo);
				$gruppo = null;
			}
		}
	}

	// Verifica la posizione di un gruppo nella lista
	private function findPosizione($gruppo, $listaGruppi) {
		$find = false;
		if (sizeof($listaGruppi) == 0) {
			return false;
		} else {
			if (isset($gruppo)) {
				$i = 0;
				while (!$find && $i < sizeof($listaGruppi)) {
					$altro = $listaGruppi[$i]->posizione;
					if (in_array($gruppo->posizione, $this->graph->nodes[$altro]->descents)) {
						$find = true;
					}
					$i++;
				}
			}
			return $find;
		}
	}
}
?>
