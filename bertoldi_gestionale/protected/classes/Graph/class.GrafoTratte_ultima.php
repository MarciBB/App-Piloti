<?php
class NodoGrafoTratte extends Node {
	// public $salite;
	// public $discese;
	public $destinazioni;
	public $gruppo;
	public $biglietti;
	public $bigliettiSalite;
	public $busPartenza;
	public $busArrivo;
	public $importanza;
	// public $operazioni;
	function __construct($idNode) {
		parent::__construct ( $idNode );
		$salite = 0;
		$discese = 0;
		$destinazioni = array ();
		$gruppo = new GruppoGrafoTratte ();
		$biglietti = array ();
		$this->busPartenza = array ();
		$this->busArrivo = array ();
		$this->importanza = array ();
	}
}
class GruppoGrafoTratte {
	public $passeggeri; // array <destinazione, num. passeggeri>
	public $totalePasseggeri;
	public $posizione;
	function __construct($idPosizione = null) {
		$this->passeggeri = array ();
		$this->totalePasseggeri = 0;
		$this->posizione = $idPosizione;
	}
	public function isNew() {
		if (isset ( $this->posizione )) {
			return true;
		} else {
			return false;
		}
	}
}
class BusGrafoTratte {
	public $id;
	public $comuni;
	public $percorso;
	public $nome;
	function __construct($id) {
		$this->id = $id;
		$this->percorso = array ();
		$this->comuni = array ();
	}
}
class GrafoTratte {
	public $idLinea;
	public $conn;
	public $graph;
	public $flotta;
	public $postiBus;
	public $partenza;
	public $ritornoOpen;
	
	function __construct($idLinea, $idCorsa, $conn, $partenza, $destinazione) {
		$this->idLinea = $idLinea;
		$this->idCorsa = $idCorsa;
		$this->conn = $conn;
		$db = $this->conn;
		$this->graph = new Graph ();
		$this->partenza = $partenza;
		
//  		echo "CorsaId =".$idCorsa." $idLinea $partenza $destinazione<br>";
		
		//ritonoOpen
		$sql = "Select RitornoAperto FROM RT_Corsa WHERE CorsaId = $idCorsa";
		$tempCorsa = $db->fetch_array ( $sql );
		$this->ritornoOpen = $tempCorsa[0]['RitornoAperto'];
		

		$result = $this->onlyTratta($partenza, $destinazione);
		if($result['km']>-1){
			$this->graph->addNode ( $partenza );
		 	$this->graph->addNode ( $destinazione );
			$this->graph->connectNodes ( $partenza, $destinazione, 0, $result['km'], $result['tratta'] );
		
			$this->flotta[0] = new BusGrafoTratte(0);
			$p[0] = 1; 
			$pasTemp[$destinazione] = $p;
			$comuniTemp[0]['comune'] = $partenza;
			$comuniTemp[0]['passeggeri'] = $pasTemp;
			$comuniTemp[1]['comune'] = $destinazione;
			$comuniTemp[1]['passeggeri'] = array();
			$this->flotta[0]->comuni = $comuniTemp;
			
			$this->flotta[0]->percorso[0] = $partenza.'-'.$destinazione;
 			$this->graph->edges[$partenza.'-'.$destinazione]->info3[$result['tratta']]=$result['tratta'];	
		}else{
			$sql = "SELECT TotalePosti
					FROM RT_TipologiaBus
					WHERE IsDefault=1";
			$row = $conn->query_first ( $sql );
			
			$this->postiBus = $row ['TotalePosti'];
			unset ( $row );
			$this->flotta = array ();
			/* creazione grafo */			
			if (! isset ( $idCorsa ) || $this->ritornoOpen == 1) {
				$sql = "SELECT TrattaId FROM RT_Tratta 
						WHERE LineaId = $this->idLinea AND
						Stato = 1 AND 
						Cancella = 0 AND 
						DaConfermare = 0
						ORDER BY TrattaNome";
				
				$rowsTratte = $db->fetch_array ( $sql );
				
				foreach ( $rowsTratte as $key => $tratta ) {
					$sql = "SELECT ComuneId, KmInizioTratta FROM RT_Fermata 
							WHERE TrattaId=" . $tratta ['TrattaId'] . " AND
							Stato = 1 AND
							Cancella = 0 AND 
							IsDaConfermare = 0 AND 
							IsBlackList = 0 		 
							ORDER BY FermataPeso";
					
					$rowsFermate = $db->fetch_array ( $sql );
					
					foreach ( $rowsFermate as $key2 => $fermata ) {
						$nodoFermata = new NodoGrafoTratte ( $fermata ['ComuneId'] );
						$this->graph->addNode ( $nodoFermata );
					}
					$i = 0;
					while ( $i < (sizeof ( $rowsFermate ) - 1) ) {
						$temp1 = $rowsFermate [$i];
						$temp2 = $rowsFermate [$i + 1];
						
						if($temp2 ['ComuneId'] != $temp1 ['ComuneId']){
							if(!array_key_exists($temp2 ['ComuneId'] . '-' . $temp1 ['ComuneId'], $this->graph->edges) && ($temp1 ['ComuneId'] != $temp2 ['ComuneId'])){
								$this->graph->connectNodes ( $temp1 ['ComuneId'], $temp2 ['ComuneId'], 0, $temp2 ['KmInizioTratta'] - $temp1 ['KmInizioTratta'], $tratta ['TrattaId'] );
								$this->graph->edges [$temp1 ['ComuneId'] . '-' . $temp2 ['ComuneId']]->info3 [$tratta ['TrattaId']] = $tratta ['TrattaId'];
								
							}
						}
						$i ++;
					}
				}
				unset ( $rowsTratte );
			} else {
				$sql = "SELECT DISTINCT t.TrattaId 
						FROM RT_Tratta t LEFT JOIN RT_CorsaTariffa ct ON (t.TrattaId = ct.TrattaId)
						WHERE t.Stato = 1 AND 
						t.Cancella = 0 AND 
						t.LineaId = $this->idLinea AND
						ct.CorsaId = $idCorsa AND 
						t.DaConfermare = 0";
				
				$rowsTratte = $db->fetch_array ( $sql );
				$corsaDeleted = false;
				if(count($rowsTratte) == 0){
					$corsaDeleted = true;
					$sql = "SELECT TrattaId FROM RT_Tratta
					WHERE LineaId = $this->idLinea AND
					Stato = 1 AND
					Cancella = 0 AND
					DaConfermare = 0
					ORDER BY TrattaNome";

					$rowsTratte = $db->fetch_array ( $sql );
				}
				
				foreach ( $rowsTratte as $key => $tratta ) {
					$rowsFermate = array();
					if(!$corsaDeleted){
					
						$sql = "SELECT f.ComuneId, f.KmInizioTratta FROM RT_Fermata f
								LEFT JOIN RT_Orario o ON (f.FermataId = o.FermataId)
								WHERE f.Stato = 1 AND
								f.Cancella = 0 AND 
								f.IsDaConfermare = 0 AND 
								f.IsBlackList = 0 AND 
								f.TrattaId = " . $tratta ['TrattaId'] . " AND (
								f.FermataId IN (SELECT FermataPickup FROM RT_CorsaTariffa WHERE TrattaId = " . $tratta ['TrattaId'] . " AND CorsaId = $idCorsa) OR
								f.FermataId IN (SELECT FermataDropOff FROM RT_CorsaTariffa WHERE TrattaId = " . $tratta ['TrattaId'] . " AND CorsaId = $idCorsa)) AND
								o.CorsaId =  $idCorsa
								ORDER BY FermataPeso";
						
						$rowsFermate = $db->fetch_array ( $sql );
					} else {
						
						$this->ritornoOpen = 1;
						$sql = "SELECT ComuneId, KmInizioTratta FROM RT_Fermata 
							WHERE TrattaId=" . $tratta ['TrattaId'] . " AND
							Stato = 1 AND
							Cancella = 0 AND 
							IsDaConfermare = 0 AND 
							IsBlackList = 0 AND (IsPickup = 1 OR IsDropOff = 1 OR IsInterscambio = 1)
							ORDER BY FermataPeso";
							
						$rowsFermate = $db->fetch_array ( $sql );
						
					}
					
					foreach ( $rowsFermate as $key2 => $fermata ) {
						$nodoFermata = new NodoGrafoTratte ( $fermata ['ComuneId'] );
						$this->graph->addNode ( $nodoFermata );
					}
					$i = 0;
					while ( $i < (sizeof ( $rowsFermate ) - 1) ) {
						$temp1 = $rowsFermate [$i];
						$temp2 = $rowsFermate [$i + 1];
						if($temp2 ['ComuneId'] != $temp1 ['ComuneId']){
							if(!array_key_exists($temp2 ['ComuneId'] . '-' . $temp1 ['ComuneId'], $this->graph->edges) && !array_key_exists($temp1 ['ComuneId'] . '-' . $temp2 ['ComuneId'], $this->graph->edges)){
								$this->graph->connectNodes ( $temp1 ['ComuneId'], $temp2 ['ComuneId'], 0, $temp2 ['KmInizioTratta'] - $temp1 ['KmInizioTratta'], $tratta ['TrattaId'] );
								$this->graph->edges [$temp1 ['ComuneId'] . '-' . $temp2 ['ComuneId']]->info3 [$tratta ['TrattaId']] = $tratta ['TrattaId'];
							}
						}
						$i ++;
					}
				}
				unset ( $rowsTratte );
			}

			/* calcolo discendenti dei nodi */
 			$this->graph->calculateDescent ();

// 			if (isset ( $idCorsa )) {
				/* calcolo importanza discendenti */
// 				$this->calcoloImportanza ( $db );
				if (isset ( $partenza ) && isset ( $destinazione ) && isset($this->graph->nodes [$partenza]) && isset($this->graph->nodes [$destinazione])) {
// 					echo "Esistono!!!!!!!!!!!!!!!!<br>";
					$this->graph->nodes [$partenza]->salite = 1;
					$this->graph->nodes [$partenza]->destinazioni [$destinazione] = 1;
					$this->graph->nodes [$partenza]->biglietti [$destinazione] [] = 1;
					$this->graph->nodes [$partenza]->bigliettiSalite [$destinazione] [] = 1;
					
					$this->graph->nodes [$destinazione]->discese = 1;
					
					if (array_key_exists($destinazione, $this->graph->nodes[$partenza]->descents)) {
						/* calcolo gruppi */						
						$this->calcolaGruppi ();	
					}
				}
// 			}
		} 
	}
	
	private function calcolaGruppi() {
		/* inizializzazione gruppi */
		$listaGruppi = array ();
		$firstInterscambio = false;
		
		foreach ( $this->graph->nodes as $node ) {
			if (isset($node) && isset($node->salite) && $node->salite > 0) {
				$this->graph->nodes [$node->id]->gruppo = new GruppoGrafoTratte ( $node->id );
				$gruppo = $this->graph->nodes [$node->id]->gruppo;
				$listaGruppi [] = $this->graph->nodes [$node->id]->gruppo;
			}
		}
		
		/* calcolo iterativo */
		$ii = 0;
		while ( sizeof ( $listaGruppi ) > 0 ) {
			$ii++;  
			if($ii>100){ 
			break;
			}
			
			$gruppoAvanza = $this->getProssimoGruppo ( $listaGruppi );
// 			echo "<br>cicli$ii:$gruppoAvanza->posizione zz";
			
			// discesa
			if (array_key_exists ( $gruppoAvanza->posizione, $gruppoAvanza->passeggeri )) {
				$gruppoAvanza->passeggeri [$gruppoAvanza->posizione] = 0;
				unset ( $gruppoAvanza->passeggeri [$gruppoAvanza->posizione] );
				$gruppoAvanza->totalePasseggeri = $gruppoAvanza->totalePasseggeri - $this->graph->nodes [$gruppoAvanza->posizione]->discese;
			}
			// salita
			foreach ( $this->graph->nodes [$gruppoAvanza->posizione]->destinazioni as $destinazione => $num ) {
				if (array_key_exists ( $destinazione, $gruppoAvanza->passeggeri )) {
					$gruppoAvanza->passeggeri [$destinazione] = $gruppoAvanza->passeggeri [$destinazione] + $num;
				} else {
					$gruppoAvanza->passeggeri [$destinazione] = $num;
				}
				$gruppoAvanza->totalePasseggeri = $gruppoAvanza->totalePasseggeri + $num;
			}
			
			$nuoviGruppi = array ();
			
			$sql = "SELECT * FROM RT_Fermata f
							left join RT_Tratta t on (t.TrattaId = f.TrattaId)
							where comuneid = ".$gruppoAvanza->posizione." and isInterscambio = 1";
			$rows = $this->conn->fetch_array ( $sql );
			$isInterscambio = false;
			if(count($rows)>0){
				$isInterscambio = false;
			}
// 			echo "salita e discesa ok<br>";
			// divisione per destinazione
			
			foreach ( $gruppoAvanza->passeggeri as $destinazione => $num ) {
// 				echo "passeggeri $destinazione<br>";
				if(!in_array ( $destinazione, $this->graph->nodes ['4002']->descents )){
					if(!$firstInterscambio && !$isInterscambio && count($this->graph->nodes [$gruppoAvanza->posizione]->children)>1){
						
						$countTempC = 0;
						foreach ($this->graph->nodes [$gruppoAvanza->posizione]->children as $child ) {
							$firstInterscambio = true;
							$sql = "SELECT f.TrattaId FROM RT_Fermata f
							left join RT_Tratta t on (f.trattaid = t.trattaid)
							where f.comuneid = $child and t.lineaid = ".$this->idLinea." and t.trattaid in (
							SELECT f.TrattaId FROM RT_Fermata f
							left join RT_Tratta t on (f.trattaid = t.trattaid)
							where f.comuneid = $this->partenza and t.lineaid = ".$this->idLinea.")";
							$rowsTemp2 = $this->conn->fetch_array ( $sql );

							if(count($rowsTemp2)==0 && !in_array ( $destinazione, $this->graph->nodes [$child]->descents )){
								$countTempC++;
							}
						}
						
						if($countTempC == count($this->graph->nodes [$gruppoAvanza->posizione]->children)){
							return;
						}
					}
				}
				
				$privata = 0;
				$prossimaFermata = array ();
				foreach ( $this->graph->nodes [$gruppoAvanza->posizione]->children as $child ) {
					
					if (strcmp ( $destinazione, $child ) == 0) {
						$prossimaFermata [$child] = $child;
					} else {
						if (in_array ( $destinazione, $this->graph->nodes [$child]->descents )) {
							if ($privata == 0) {
								$privata = 1;
								$prossimaFermata [$child] = $child;
							} else if ($privata == 1) {
								$privata = 2;
								$prossimaFermata [$child] = $child;
							}
						}
					}
				}
// 				print_r($prossimaFermata);
				
				if (count ( $prossimaFermata ) > 1) {
					
					$maxImportanza = 100;
					$tempRemove = array ();
					foreach ( $prossimaFermata as $key => $prossima ) {
						if ($this->graph->nodes [$prossima]->importanza [$destinazione] < $maxImportanza) {
							$maxImportanza = $this->graph->nodes [$prossima]->importanza [$destinazione];
						}
					}
					
					foreach ( $prossimaFermata as $key => $prossima ) {
						if ($this->graph->nodes [$prossima]->importanza [$destinazione] > $maxImportanza) {
							$tempRemove [] = $key;
						}
					}
					foreach ( $tempRemove as $trem ) {
						unset ( $prossimaFermata [$trem] );
					}
				}
// 				echo "ordinamento importanza";
// 				print_r($prossimaFermata);
				
				if (sizeof ( $prossimaFermata ) > 0) {
					if (sizeof ( $prossimaFermata ) == 1) {
						// estrai il primo
// 						echo "primo<br>";
						$nextNode = array_shift ( $prossimaFermata );
					} else {
// 						echo "percorso breve<br>";
						//percorso piu breve
// 						echo "<br>breve $gruppoAvanza->posizione $destinazione";
						$nextNode = $this->graph->percorsoBreve($gruppoAvanza->posizione, $destinazione);
						if($nextNode == -1){
							$nextNode = array_shift ( $prossimaFermata );
						}
//  					$nextNode = array_pop($prossimaFermata);
					}
				}
// 				print_r($nextNode);
				unset ( $prossimaFermata );
				unset ( $privata );
				unset ( $min );
				
				if (! isset ( $this->graph->nodes [$nextNode]->gruppo )) {
					$this->graph->nodes [$nextNode]->gruppo = new GruppoGrafoTratte ( $nextNode );
				}
				if (! array_key_exists ( $destinazione, $this->graph->nodes [$nextNode]->gruppo->passeggeri )) {
					$this->graph->nodes [$nextNode]->gruppo->passeggeri [$destinazione] = $num;
				} else {
					$this->graph->nodes [$nextNode]->gruppo->passeggeri [$destinazione] = $this->graph->nodes [$nextNode]->gruppo->passeggeri [$destinazione] + $num;
				}
				$this->graph->nodes [$nextNode]->gruppo->totalePasseggeri = $this->graph->nodes [$nextNode]->gruppo->totalePasseggeri + $num;
				if (isset ( $this->graph->nodes [$nextNode]->biglietti [$destinazione] )) {
					$this->graph->nodes [$nextNode]->biglietti [$destinazione] = array_merge ( $this->graph->nodes [$nextNode]->biglietti [$destinazione], $this->graph->nodes [$gruppoAvanza->posizione]->biglietti [$destinazione] );
				} else {
					$this->graph->nodes [$nextNode]->biglietti [$destinazione] = $this->graph->nodes [$gruppoAvanza->posizione]->biglietti [$destinazione];
				}
				
				$this->graph->edges [$gruppoAvanza->posizione . "-" . $nextNode]->info2 = $this->graph->edges [$gruppoAvanza->posizione . "-" . $nextNode]->info2 + $num;
				$this->graph->edges [$gruppoAvanza->posizione . "-" . $nextNode]->info = array_merge ( $this->graph->edges [$gruppoAvanza->posizione . "-" . $nextNode]->info, $this->graph->nodes [$gruppoAvanza->posizione]->biglietti [$destinazione] );
				if (! isset ( $this->graph->edges [$gruppoAvanza->posizione . "-" . $nextNode]->info4 [$destinazione] )) {
					$this->graph->edges [$gruppoAvanza->posizione . "-" . $nextNode]->info4 [$destinazione] = array ();
				}
				
				$this->graph->edges [$gruppoAvanza->posizione . "-" . $nextNode]->info4 [$destinazione] = array_merge ( $this->graph->edges [$gruppoAvanza->posizione . "-" . $nextNode]->info4 [$destinazione], $this->graph->nodes [$gruppoAvanza->posizione]->biglietti [$destinazione] );
				$nuoviGruppi [$nextNode] = $nextNode;
				unset ( $nextNode );
			}
			
			// organizzazione fermata successivo
			foreach ( $this->graph->nodes [$gruppoAvanza->posizione]->children as $child ) {
				if ($this->graph->nodes [$child]->salite > 0) {
					if (! isset ( $this->graph->nodes [$child]->gruppo )) {
						$this->graph->nodes [$child]->gruppo = new GruppoGrafoTratte ( $child );
					}
					$nuoviGruppi [$child] = $child;
				}
			}
			
			// caricamento in lista
			foreach ( $nuoviGruppi as $key => $nodo ) {
				if (! in_array ( $this->graph->nodes [$nodo]->gruppo, $listaGruppi )) {
					array_push ( $listaGruppi, $this->graph->nodes [$nodo]->gruppo );
				}
			}
			unset ( $nuoviGruppi );
			// bus
// 			echo "bus ok<br>";
			$countEdge = 0;
			foreach ( $this->graph->nodes [$gruppoAvanza->posizione]->children as $child ) {
				$edgeId = $gruppoAvanza->posizione . "-" . $child;
				if ($this->graph->edges [$edgeId]->info2 > 0) {
					$countEdge ++;
				}
			}
			foreach ( $this->graph->nodes [$gruppoAvanza->posizione]->children as $child ) {
				$edgeId = $gruppoAvanza->posizione . "-" . $child;
				if ($this->graph->edges [$edgeId]->info2 > 0) {
					$fermataObj = new Fermata ();
					$fermataObj->conn = $this->conn;
					
					if ($fermataObj->isInterscambioLinea ( $this->idLinea, $gruppoAvanza->posizione )) {
						
						if (sizeof ( $this->graph->nodes [$gruppoAvanza->posizione]->busArrivo ) == 0) {
							// nuovo bus
							$this->nuovoBus ( $edgeId );
						} else {
							// avanza bus
							$this->busAvanza2 ( $edgeId, $this->conn );
							
							// $this->nuovoBus($edgeId);
							// $this->taggingBus($edgeId);
						}
					} else {
						if (sizeof ( $this->graph->nodes [$gruppoAvanza->posizione]->busArrivo ) == 0) {
							// nuovo bus
							$this->nuovoBus ( $edgeId );
						} else {
							// bus avanza
							$this->busAvanza ( $edgeId );
						}
					}
				}
			}
			// unset($this->edges[$edgeId]->info4);
			// unset($this->edges[$edgeId]->info3);
			// unset($this->edges[$edgeId]->info2);
			// unset($this->edges[$edgeId]->info);
			
			unset ( $this->graph->nodes [$gruppoAvanza->posizione]->gruppo );
			unset ( $this->graph->nodes [$gruppoAvanza->posizione]->biglietti );
		}
		
		foreach ( $this->flotta as $bus ) {
			$ultimaFermata = sizeof ( $bus->comuni );
			$deposito = 0;
			foreach ( $bus->comuni [$ultimaFermata - 1] ['passeggeri'] as $ultimoComune => $passeggeri ) {
				$deposito = $ultimoComune;
			}
			if (array_key_exists ( $deposito, $this->graph->nodes [$bus->comuni [$ultimaFermata - 1] ['comune']]->children )) {
				$bus->comuni [$ultimaFermata] ['comune'] = $deposito;
				$bus->comuni [$ultimaFermata] ['passeggeri'] = array ();
				if (! in_array ( $bus->comuni [$ultimaFermata - 1] ['comune'] . "-" . $deposito, $bus->percorso ))
					$bus->percorso [$ultimaFermata] = $bus->comuni [$ultimaFermata - 1] ['comune'] . "-" . $deposito;
			} else {
				$ultimaTratta = sizeof ( $bus->percorso ) - 1;
				$deposito = $this->graph->edges [$bus->percorso [$ultimaTratta]]->nodeB;
				$bus->comuni [$ultimaFermata - 1] ['comune'] = $deposito;
				$bus->comuni [$ultimaFermata - 1] ['passeggeri'] = array ();
			}
		}
	}
	private function busAvanza($edgeId) {
		$busConsiderati = array ();
		// i pulman in arrivo avanzano mantenedo gli stessi passeggeri a bordo
		foreach ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busArrivo as $nomeBus => $bus ) {
			
			if ($this->graph->edges [$edgeId]->info2 > 0) {
				$lastComune = sizeof ( $this->flotta [$bus]->comuni ) - 1;
				if ($this->flotta [$bus]->comuni [$lastComune] ['comune'] != $this->graph->edges [$edgeId]->nodeA) {
					
					// se il pulman non � stato gia' usato per questo comune
					$temp ['comune'] = $this->graph->edges [$edgeId]->nodeA;
					$temp ['passeggeri'] = array ();
					$count = 0;
					foreach ( $this->flotta [$bus]->comuni [$lastComune] ['passeggeri'] as $dest => $passeggeri ) {
						if ($this->graph->edges [$edgeId]->info2 > 0) {
							$tempPasseggeri = array ();
							foreach ( $passeggeri as $key => $passeggero ) {
								if (in_array ( $passeggero, $this->graph->edges [$edgeId]->info4 [$dest] )) {
									if ($dest != $this->graph->edges [$edgeId]->nodeA)
										$tempPasseggeri [] = $passeggero;
									
									$keyInfo4 = array_search ( $passeggero, $this->graph->edges [$edgeId]->info4 [$dest] );
									unset ( $this->graph->edges [$edgeId]->info4 [$dest] [$keyInfo4] );
									if (count ( $this->graph->edges [$edgeId]->info4 [$dest] ) <= 0) {
										unset ( $this->graph->edges [$edgeId]->info4 [$dest] );
									}
									$this->graph->edges [$edgeId]->info2 --;
									$count ++;
								}
							}
							if (count ( $tempPasseggeri ) > 0)
								$temp ['passeggeri'] [$dest] = $tempPasseggeri;
						}
					}
					if (count ( $temp ['passeggeri'] ) > 0) {
						$this->flotta [$bus]->comuni [$lastComune + 1] = $temp;
						$this->flotta [$bus]->percorso [$lastComune + 1] = $edgeId;
						$this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza [$bus] = $bus;
						$this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$bus] = $bus;
					}
					$busConsiderati [$bus] = $count;
				}
			}
		}
		
		// carico i nuovi passeggeri non presenti sugli autobus precedentemente
		if ($this->graph->edges [$edgeId]->info2 > 0) {
			foreach ( $busConsiderati as $bus => $postiOccupati ) {
				if ($postiOccupati < $this->postiBus) {
					$lastComune = sizeof ( $this->flotta [$bus]->comuni ) - 1;
					$tempDest = array ();
					foreach ( $this->graph->edges [$edgeId]->info4 as $dest => $passeggeri ) {
						$tempRemove = array ();
						foreach ( $passeggeri as $key => $passeggero ) {
							if ($postiOccupati < $this->postiBus) {
								$this->flotta [$bus]->comuni [$lastComune] ['passeggeri'] [$dest] [] = $passeggero;
								$postiOccupati ++;
								$tempRemove [] = $passeggero;
							}
						}
						foreach ( $tempRemove as $removePasseggero ) {
							$key = array_search ( $removePasseggero, $passeggeri );
							unset ( $this->graph->edges [$edgeId]->info4 [$dest] [$key] );
							$this->graph->edges [$edgeId]->info2 --;
						}
						if (count ( $passeggeri ) <= 0) {
							$tempDest [] = $dest;
						}
					}
					foreach ( $tempDest as $removeDest ) {
						unset ( $this->graph->edges [$edgeId]->info4 [$removeDest] );
					}
				}
			}
		}
		
		if ($this->graph->edges [$edgeId]->info2 > 0) {
			$this->nuovoBus ( $edgeId );
		}
	}
	private function busAvanza2($edgeId, $db) {
		$busConsiderati = array ();
		// i pulman in arrivo avanzano mantenedo gli stessi passeggeri a bordo
		foreach ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busArrivo as $nomeBus => $bus ) {
			if ($this->graph->edges [$edgeId]->info2 > 0) {
				$lastComune = sizeof ( $this->flotta [$bus]->comuni ) - 1;
				if ($this->flotta [$bus]->comuni [$lastComune] ['comune'] != $this->graph->edges [$edgeId]->nodeA) {
					
					// se il pulman non � stato gia' usato per questo comune
					$temp ['comune'] = $this->graph->edges [$edgeId]->nodeA;
					$temp ['passeggeri'] = array ();
					$count = 0;
					foreach ( $this->flotta [$bus]->comuni [$lastComune] ['passeggeri'] as $dest => $passeggeri ) {
						if ($this->graph->edges [$edgeId]->info2 > 0) {
							$tempPasseggeri = array ();
							foreach ( $passeggeri as $key => $passeggero ) {
								if (in_array ( $passeggero, $this->graph->edges [$edgeId]->info4 [$dest] )) {
									if ($dest != $this->graph->edges [$edgeId]->nodeA)
										$tempPasseggeri [] = $passeggero;
									
									$keyInfo4 = array_search ( $passeggero, $this->graph->edges [$edgeId]->info4 [$dest] );
									unset ( $this->graph->edges [$edgeId]->info4 [$dest] [$keyInfo4] );
									if (count ( $this->graph->edges [$edgeId]->info4 [$dest] ) <= 0) {
										unset ( $this->graph->edges [$edgeId]->info4 [$dest] );
									}
									$this->graph->edges [$edgeId]->info2 --;
									$count ++;
								}
							}
							if (count ( $tempPasseggeri ) > 0)
								$temp ['passeggeri'] [$dest] = $tempPasseggeri;
						}
					}
					if (count ( $temp ['passeggeri'] ) > 0) {
						$this->flotta [$bus]->comuni [$lastComune + 1] = $temp;
						$this->flotta [$bus]->percorso [$lastComune + 1] = $edgeId;
						$this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza [$bus] = $bus;
						$this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$bus] = $bus;
					}
					$busConsiderati [$bus] = $count;
				}
			}
		}
		
		// carico i nuovi passeggeri non presenti sugli autobus precedentemente
		if ($this->graph->edges [$edgeId]->info2 > 0) {
			foreach ( $busConsiderati as $bus => $postiOccupati ) {
				if ($postiOccupati < $this->postiBus) {
					$lastComune = sizeof ( $this->flotta [$bus]->comuni ) - 1;
					$tempDest = array ();
					foreach ( $this->graph->edges [$edgeId]->info4 as $dest => $passeggeri ) {
						$tempRemove = array ();
						foreach ( $passeggeri as $key => $passeggero ) {
							if ($postiOccupati < $this->postiBus) {
								$this->flotta [$bus]->comuni [$lastComune] ['passeggeri'] [$dest] [] = $passeggero;
								$postiOccupati ++;
								$tempRemove [] = $passeggero;
							}
						}
						foreach ( $tempRemove as $removePasseggero ) {
							$key = array_search ( $removePasseggero, $passeggeri );
							unset ( $this->graph->edges [$edgeId]->info4 [$dest] [$key] );
							$this->graph->edges [$edgeId]->info2 --;
						}
						if (count ( $passeggeri ) <= 0) {
							$tempDest [] = $dest;
						}
					}
					foreach ( $tempDest as $removeDest ) {
						unset ( $this->graph->edges [$edgeId]->info4 [$removeDest] );
					}
				}
			}
		}
		
		if ($this->graph->edges [$edgeId]->info2 > 0) {
			$this->nuovoBus ( $edgeId );
		}
		
		if (sizeof ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza ) > 1) {
			$this->travasoBus ( $edgeId, $db );
			$this->travasoDivisioneBus ( $edgeId, $db );
		}
	}
	private function travasoBus($edgeId, $db) {
		$fermataObj = new Fermata ();
		$fermataObj->conn = $db;
		
		foreach ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo as $nomeBus1 => $bus1 ) {
			foreach ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo as $nomeBus2 => $bus2 ) {
				if ($nomeBus1 != $nomeBus2) {
					foreach ( $this->flotta [$bus1]->comuni [count ( $this->flotta [$bus1]->comuni ) - 1] ['passeggeri'] as $dest => $passeggeri ) {
						
						if (array_key_exists ( $dest, $this->flotta [$bus2]->comuni [count ( $this->flotta [$bus2]->comuni ) - 1] ['passeggeri'] )) {
							$temp = $passeggeri;
							foreach ( $this->flotta [$bus2]->comuni [count ( $this->flotta [$bus2]->comuni ) - 1] ['passeggeri'] [$dest] as $keyPass => $passeggero2 ) {
								$temp [] = $passeggero2;
							}
							
							if (count ( $passeggeri ) > count ( $this->flotta [$bus2]->comuni [count ( $this->flotta [$bus2]->comuni ) - 1] ['passeggeri'] [$dest] )) {
								$this->flotta [$bus1]->comuni [count ( $this->flotta [$bus1]->comuni ) - 1] ['passeggeri'] [$dest] = $temp;
								unset ( $this->flotta [$bus2]->comuni [count ( $this->flotta [$bus2]->comuni ) - 1] ['passeggeri'] [$dest] );
							} else {
								$this->flotta [$bus2]->comuni [count ( $this->flotta [$bus2]->comuni ) - 1] ['passeggeri'] [$dest] = $temp;
								unset ( $this->flotta [$bus1]->comuni [count ( $this->flotta [$bus1]->comuni ) - 1] ['passeggeri'] [$dest] );
							}
						}
					}
				}
			}
		}
	}
	private function travasoDivisioneBus($edgeId, $db) {
		$fermataObj = new Fermata ();
		$fermataObj->conn = $db;
		
		foreach ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo as $nomeBus1 => $bus1 ) {
			foreach ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo as $nomeBus2 => $bus2 ) {
				if ($nomeBus1 != $nomeBus2) {
					$add = array ();
					foreach ( $this->flotta [$bus1]->comuni [count ( $this->flotta [$bus1]->comuni ) - 1] ['passeggeri'] as $dest => $passeggeri ) {
						$count1 = 0;
						$count2 = 0;
						foreach ( $this->flotta [$bus1]->comuni [count ( $this->flotta [$bus1]->comuni ) - 1] ['passeggeri'] as $dest2 => $passeggeri2 ) {
							$res = $fermataObj->getTratta ( $dest, $dest2 );
							if ($res != false) {
								$count1 += count ( $passeggeri2 );
							}
						}
						
						foreach ( $this->flotta [$bus2]->comuni [count ( $this->flotta [$bus2]->comuni ) - 1] ['passeggeri'] as $dest2 => $passeggeri2 ) {
							$res = $fermataObj->getTratta ( $dest, $dest2 );
							if ($res != false) {
								$count2 += count ( $passeggeri2 );
							}
						}
						
						if ($count1 <= $count2) {
							$this->flotta [$bus2]->comuni [count ( $this->flotta [$bus2]->comuni ) - 1] ['passeggeri'] [$dest] = $this->flotta [$bus1]->comuni [count ( $this->flotta [$bus1]->comuni ) - 1] ['passeggeri'] [$dest];
							unset ( $this->flotta [$bus1]->comuni [count ( $this->flotta [$bus1]->comuni ) - 1] ['passeggeri'] [$dest] );
						}
					}
					
					if (count ( $this->flotta [$bus1]->comuni [count ( $this->flotta [$bus1]->comuni ) - 1] ['passeggeri'] ) <= 0) {
						unset ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza [$bus1] );
						unset ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$bus1] );
						unset ( $this->flotta [$bus1]->percorso [count ( $this->flotta [$bus1]->percorso ) - 1] );
					}
				}
			}
		}
	}
	private function taggingBus($edgeId) {
		if (sizeof ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza ) == 1 && sizeof ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busArrivo ) == 1) {
			// caso in cui un bus arriva ed un bus parte
			$busA = array_pop ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busArrivo );
			$busB = array_pop ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza );
			
			$this->mergeBus ( $busA, $busB );
			
			$this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza [$busA] = $busA;
			$this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busArrivo [$busA] = $busA;
			
			unset ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$busB] );
			$this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$busA] = $busA;
		} else {
			// caso in cui 1 o piu' bus arrivano e 1 o piu' bus partono
			$tempCount = array ();
			foreach ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza as $busParte ) {
				foreach ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busArrivo as $busArriva ) {
					$lastIndexBusArriva = sizeof ( $this->flotta [$busArriva]->comuni ) - 1;
					
					if ($this->flotta [$busArriva]->comuni [$lastIndexBusArriva] ['comune'] != $this->graph->edges [$edgeId]->nodeA) {
						$count = 0;
						if (count ( $this->flotta [$busParte]->comuni ) <= 1) {
							foreach ( $this->flotta [$busParte]->comuni [0] as $fermata => $passeggeri ) {
								if (isset ( $passeggeri ) && sizeof ( $passeggeri ) > 0) {
									foreach ( $passeggeri as $d => $gruppo ) {
										if (isset ( $gruppo )) {
											foreach ( $gruppo as $persona ) {
												if (isset ( $persona ) && isset ( $this->flotta [$busArriva]->comuni [$lastIndexBusArriva] ['passeggeri'] [$d] )) {
													if (in_array ( $persona, $this->flotta [$busArriva]->comuni [$lastIndexBusArriva] ['passeggeri'] [$d] )) {
														$count ++;
													}
												}
											}
										}
									}
								}
							}
							$tempCount [$busArriva] [$busParte] = $count;
						}
					}
				}
			}
			
			$copyCount = $tempCount;
			$servito = array ();
			foreach ( $tempCount as $busArriva => $temp ) {
				$max = 0;
				$idBus = - 1;
				
				foreach ( $temp as $busParte => &$tot ) {
					if ($max <= $tot && ! in_array ( $busParte, $servito )) {
						$max = $tot;
						$idBus = $busParte;
						$tot = - 1;
					}
				}
				
				if ($idBus != - 1) {
					$servito [$idBus] = $idBus;
				}
				
				if ($idBus > - 1 && $idBus != $busArriva) {
					$this->mergeBus ( $busArriva, $idBus );
					
					$this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza [$busArriva] = $busArriva;
					$this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busArrivo [$busArriva] = $busArriva;
					
					unset ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$idBus] );
					unset ( $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza [$idBus] );
					$this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$busArriva] = $busArriva;
				}
			}
		}
	}
	private function mergeBus($busA, $busB) {
		$index = sizeof ( $this->flotta [$busA]->comuni );
		$this->flotta [$busA]->comuni [$index] = $this->flotta [$busB]->comuni [0];
		$this->flotta [$busA]->percorso [$index] = $this->flotta [$busB]->percorso [0];
		unset ( $this->flotta [$busB] );
	}
	private function nuovoBus($edgeId) {
		$postiDisponibili = 0;
		$selectedBus = 0;
		$selectedBusIndex = 0;
		$this->graph->edges [$edgeId]->info4 = array_reverse ( $this->graph->edges [$edgeId]->info4, true );
		foreach ( $this->graph->edges [$edgeId]->info4 as $key => $value ) {
			$value = array_reverse ( $value, true );
			if ($postiDisponibili == 0) {
				// posti esauriti, si crea un nuovo bus
				$busId = sizeof ( $this->flotta );
				while ( array_key_exists ( $busId, $this->flotta ) ) {
					$busId ++;
				}
				$bus = new BusGrafoTratte ( $busId );
				$postiDisponibili = $this->postiBus;
				$this->flotta [$busId] = $bus;
				$selectedBus = $busId;
			}
			if ($postiDisponibili >= sizeof ( $value )) {
				// posti disponibili
				$temp = array ();
				$temp ['comune'] = $this->graph->edges [$edgeId]->nodeA;
				$temp ['passeggeri'] [$key] = $value;
				if (! isset ( $this->flotta [$selectedBus]->comuni [$selectedBusIndex] )) {
					$this->flotta [$selectedBus]->comuni [$selectedBusIndex] = array ();
				}
				if (! isset ( $this->flotta [$selectedBus]->percorso [$selectedBusIndex] )) {
					$this->flotta [$selectedBus]->percorso [$selectedBusIndex] = array ();
				}
				if ((isset ( $this->flotta [$selectedBus]->comuni [$selectedBusIndex] ['comune'] )) && ($temp ['comune'] == $this->flotta [$selectedBus]->comuni [$selectedBusIndex] ['comune'])) {
					$this->flotta [$selectedBus]->comuni [$selectedBusIndex] ['passeggeri'] [$key] = $temp ['passeggeri'] [$key];
				} else {
					$this->flotta [$selectedBus]->comuni [$selectedBusIndex] = $temp;
					$this->flotta [$selectedBus]->percorso [$selectedBusIndex] = $edgeId;
				}
				
				if (! in_array ( $selectedBus, $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza )) {
					$this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza [$selectedBus] = $selectedBus;
				}
				if (! in_array ( $selectedBus, $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo )) {
					$this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$selectedBus] = $selectedBus;
				}
				$postiDisponibili -= sizeof ( $value );
			} else {
				// posti non disponibili
				$tempPasseggeri = $value;
				while ( sizeof ( $tempPasseggeri ) > 0 ) {
					$tempPasseggeri = $this->caricoPasseggeriEntrano ( $edgeId, $selectedBus, $tempPasseggeri, $postiDisponibili, $selectedBusIndex, $key );
					if (sizeof ( $tempPasseggeri ) > 0 && sizeof ( $tempPasseggeri ) > $postiDisponibili) {
						// nuovo bus
						$busId = sizeof ( $this->flotta );
						while ( array_key_exists ( $busId, $this->flotta ) ) {
							$busId ++;
						}
						$bus = new BusGrafoTratte ( $busId );
						$postiDisponibili = $this->postiBus;
						$this->flotta [$busId] = $bus;
						$selectedBus = $busId;
					}
				}
			}
		}
	}
	private function caricoPasseggeriEntrano($edgeId, $selectedBus, $tempPasseggeri, &$postiDisponibili, $selectedBusIndex, $key) {
		$tempPasseggeri1 = array ();
		$temp ['comune'] = $this->graph->edges [$edgeId]->nodeA;
		$temp ['passeggeri'] [$key] = array ();
		// carico solo i passeggeri con idPrenotazione unico che ci entrano
		$tempPasseggeri = array_reverse ( $tempPasseggeri, true );
		
		while ( sizeof ( $tempPasseggeri ) > 0 ) {
			$p = array_pop ( $tempPasseggeri );
			$sql = "SELECT b.PrenotazioneNumeroId FROM RT_PrenotazioneNumero as a
			LEFT JOIN RT_PrenotazioneNumero as b ON (a.PrenotazioneId=b.PrenotazioneId)
			WHERE
			a.PrenotazioneNumeroId = $p;";
			$rows = $this->conn->fetch_array ( $sql );
			$rows = array_reverse ( $rows, true );
			if (sizeof ( $rows ) < $this->postiBus) {
				if (sizeof ( $rows ) < $postiDisponibili) {
					foreach ( $rows as $row ) {
						array_push ( $temp ['passeggeri'] [$key], $row ['PrenotazioneNumeroId'] );
						unset ( $tempPasseggeri [array_search ( $row ['PrenotazioneNumeroId'], $tempPasseggeri )] );
					}
					$postiDisponibili -= sizeof ( $rows );
				} else {
					array_push ( $tempPasseggeri1, $p );
				}
			} else {
				foreach ( $rows as $row ) {
					if ($postiDisponibili > 0) {
						if (in_array ( $row ['PrenotazioneNumeroId'], $tempPasseggeri ) || $row ['PrenotazioneNumeroId'] == $p) {
							array_push ( $temp ['passeggeri'] [$key], $row ['PrenotazioneNumeroId'] );
							$postiDisponibili --;
						}
					} else {
						array_push ( $tempPasseggeri1, $row ['PrenotazioneNumeroId'] );
					}
					if (in_array ( $row ['PrenotazioneNumeroId'], $tempPasseggeri )) {
						unset ( $tempPasseggeri [array_search ( $row ['PrenotazioneNumeroId'], $tempPasseggeri )] );
					}
				}
			}
			unset ( $rows );
		}
		if (sizeof ( $temp ['passeggeri'] [$key] ) > 0) {
			if (! isset ( $this->flotta [$selectedBus]->comuni [$selectedBusIndex] )) {
				$this->flotta [$selectedBus]->comuni [$selectedBusIndex] = array ();
			}
			if (! isset ( $this->flotta [$selectedBus]->percorso [$selectedBusIndex] )) {
				$this->flotta [$selectedBus]->percorso [$selectedBusIndex] = array ();
			}
			
			if (isset ( $this->flotta [$selectedBus]->comuni [$selectedBusIndex] ['comune'] ) && ($temp ['comune'] == $this->flotta [$selectedBus]->comuni [$selectedBusIndex] ['comune'])) {
				$this->flotta [$selectedBus]->comuni [$selectedBusIndex] ['passeggeri'] [$key] = $temp ['passeggeri'] [$key];
			} else {
				$this->flotta [$selectedBus]->comuni [$selectedBusIndex] = $temp;
				$this->flotta [$selectedBus]->percorso [$selectedBusIndex] = $edgeId;
			}
			
			if (! in_array ( $selectedBus, $this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza )) {
				$this->graph->nodes [$this->graph->edges [$edgeId]->nodeA]->busPartenza [$selectedBus] = $selectedBus;
			}
			if (! in_array ( $selectedBus, $this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo )) {
				$this->graph->nodes [$this->graph->edges [$edgeId]->nodeB]->busArrivo [$selectedBus] = $selectedBus;
			}
		}
		unset ( $tempPasseggeri );
		return $tempPasseggeri1;
	}
	private function getProssimoGruppo(&$listaGruppi) {
		$gruppo = null;
		while ( ! isset ( $gruppo ) ) {
			$gruppo = array_pop ( $listaGruppi );
			if (! $this->findPosizione ( $gruppo, $listaGruppi )) {
				return $gruppo;
			} else {
				array_unshift ( $listaGruppi, $gruppo );
				$gruppo = null;
			}
		}
	}
	private function findPosizione($gruppo, $listaGruppi) {
		$find = false;
		if (sizeof ( $listaGruppi ) == 0) {
			return false;
		} else {
			if (isset ( $gruppo )) {
				$i = 0;
				while ( ! $find && $i < sizeof ( $listaGruppi ) ) {
					$altro = $listaGruppi [$i]->posizione;
					if (in_array ( $gruppo->posizione, $this->graph->nodes [$altro]->descents )) {
						$find = true;
					}
					$i ++;
				}
			}
			return $find;
		}
	}
	function deleteAll($lineaId, $corsaId, $corsaDataPartenza) {
		// caricamento bus
		$sql = "SELECT BusId FROM RT_GestioneOttimizzataFlotta
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'";
		$rows = $this->conn->fetch_array ( $sql );
		// if(sizeof($rows)>0){
		// foreach ($rows as $row){
		// $sql = "DELETE FROM RT_Flotta
		// WHERE
		// FlottaId=".$row['BusId'];
		// $this->conn->query($sql);
		// }
		// }
		
		$sql = "DELETE FROM RT_GestioneOttimizzataNodo
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'";
		$this->conn->query ( $sql );
		$sql = "DELETE FROM RT_GestioneOttimizzataFlotta
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'";
		$this->conn->query ( $sql );
		$sql = "DELETE FROM RT_GestioneOttimizzataPasseggeri
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'";
		$this->conn->query ( $sql );
		$sql = "DELETE FROM RT_PreparazioneBusAutisti
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND DataPartenza='$corsaDataPartenza'";
		$this->conn->query ( $sql );
		$sql = "DELETE FROM RT_GestioneOttimizzataModifiche
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'";
		$this->conn->query ( $sql );
		$sql = "DELETE FROM RT_GestionePrenotazioniSenzaBus
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND DataPartenza='$corsaDataPartenza'";
		$this->conn->query ( $sql );
	}
	function deleteNumeroPrenotazione($numeroPrenotazione, $lineaId, $corsaId, $corsaDataPartenza) {
		// conto il numero di volte in cui � presente numero Prenotazione
		$sql = "SELECT COUNT(*) as tot
		FROM RT_GestioneOttimizzataPasseggeri
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'
		AND PrenotazioneNumero=$numeroPrenotazione";
		$tot = $this->conn->query_first ( $sql );
		// se >0 deve essere cancellato, altrimenti e' stato gia' cancellato o non e' presente
		if ($tot ['tot'] > 0) {
			$sql = "DELETE FROM RT_GestioneOttimizzataPasseggeri
		WHERE
		LineaId=$lineaId
		AND CorsaId=$corsaId
		AND CorsaDataPartenza='$corsaDataPartenza'
		AND PrenotazioneNumero=$numeroPrenotazione";
			$this->conn->query ( $sql );
			// memorizzazione operazione di eliminazione dalla gestione
			$operazione ['LineaId'] = $lineaId;
			$operazione ['CorsaId'] = $corsaId;
			$operazione ['CorsaDataPartenza'] = $corsaDataPartenza;
			$operazione ['PrenotazioneNumero'] = $numeroPrenotazione;
			$operazione ['Aggiungi'] = 0;
			$prenotazioneDettaglio = new PrenotazioneDettaglio ();
			$prenotazioneDettaglio->conn = $this->conn;
			$operazione ['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione ( $numeroPrenotazione, $lineaId, $corsaId, $corsaDataPartenza );
			$this->conn->insert ( 'RT_GestioneOttimizzataModifiche', $operazione );
		}
	}
	function salvaDB($lineaId, $corsaId, $corsaDataPartenza) {
		global $user;
		$this->deleteAll ( $lineaId, $corsaId, $corsaDataPartenza );
		
		$db = $this->conn;
		$sql = "SELECT FlottaId
	FROM RT_Flotta
	WHERE IsDefault=1";
		$row = $db->query_first ( $sql );
		$idFlotta = $row ['FlottaId'];
		
		$storico = new StoricoOperazioni ();
		$storico->conn = $db;
		
		// inserimento bus di default
		$count = 1;
		foreach ( $this->flotta as $i => $bus ) {
			
			// inserimento bus in linea
			$data = array ();
			$data ['LineaId'] = $lineaId;
			$data ['CorsaId'] = $corsaId;
			$data ['CorsaDataPartenza'] = $corsaDataPartenza;
			$data ['BusId'] = $idFlotta;
			$data ['Nome'] = $count;
			
			$km = 0;
			foreach ( $bus->percorso as $edge ) {
				$km += $this->graph->edges [$edge]->peso;
			}
			$data ['KmPercorsi'] = $km;
			
			$count ++;
			$data = $storico->operazioni_insert ( $data, $user );
			$lastid = $db->insert ( "RT_GestioneOttimizzataFlotta", $data );
			
			$daCambiare = $bus->id;
			$bus->id = $lastid;
			
			unset ( $comunePre );
			foreach ( $bus->comuni as $key => $comune ) {
				
				// aggiornamento info bus nei nodi
				if (isset ( $this->graph->nodes [$comune ['comune']]->busPartenza [$daCambiare] )) {
					$this->graph->nodes [$comune ['comune']]->busPartenza [$daCambiare] = $bus->id;
				}
				if (isset ( $this->graph->nodes [$comune ['comune']]->busArrivo [$daCambiare] )) {
					$this->graph->nodes [$comune ['comune']]->busArrivo [$daCambiare] = $bus->id;
				}
				
				// inserimento nodi
				if (isset ( $comunePre )) {
					$data = array ();
					$data ['LineaId'] = $lineaId;
					$data ['CorsaId'] = $corsaId;
					$data ['CorsaDataPartenza'] = $corsaDataPartenza;
					$data ['Comune'] = $comunePre;
					$data ['BusPartenza'] = $bus->id;
					$data ['Ordine'] = $key - 1;
					
					$tratta = $this->graph->edges [$bus->percorso [$key]]->info3;
					$tt = array_shift ( $tratta );
					$data ['TrattaId'] = $tt;
					
					$data = $storico->operazioni_insert ( $data, $user );
					$lastid = $db->insert ( "RT_GestioneOttimizzataNodo", $data );
				}
				if ($key > 0) {
					$data = array ();
					$data ['LineaId'] = $lineaId;
					$data ['CorsaId'] = $corsaId;
					$data ['CorsaDataPartenza'] = $corsaDataPartenza;
					$data ['Comune'] = $comune ['comune'];
					$data ['BusArrivo'] = $bus->id;
					$data ['Ordine'] = $key;
					
					$tratta = $this->graph->edges [$bus->percorso [$key]]->info3;
					$tt = array_shift ( $tratta );
					$data ['TrattaId'] = $tt;
					
					$data = $storico->operazioni_insert ( $data, $user );
					$lastid = $db->insert ( "RT_GestioneOttimizzataNodo", $data );
				}
				
				$comunePre = $comune ['comune'];
				
				// inserimento passeggeri
				foreach ( $comune ['passeggeri'] as $dest => $biglietti ) {
					foreach ( $biglietti as $key1 => $numeroPrenotazione ) {
						$data = array ();
						$data ['LineaId'] = $lineaId;
						$data ['CorsaId'] = $corsaId;
						$data ['CorsaDataPartenza'] = $corsaDataPartenza;
						$data ['Comune'] = $comune ['comune'];
						$data ['ComuneArrivo'] = $dest;
						$data ['Bus'] = $bus->id;
						$data ['PrenotazioneNumero'] = $numeroPrenotazione;
						$data ['Ordine'] = $key;
						
						$data = $storico->operazioni_insert ( $data, $user );
						$lastid = $db->insert ( "RT_GestioneOttimizzataPasseggeri", $data );
					}
				}
			}
		}
	}
	function caricaDB($lineaId, $corsaId, $corsaDataPartenza) {
		$db = $this->conn;
		
		// caricamento bus
		$sql = "SELECT GestioneOttimizzataFlottaId FROM RT_GestioneOttimizzataFlotta
	WHERE
	LineaId=$lineaId
				AND CorsaId=$corsaId
					AND CorsaDataPartenza='$corsaDataPartenza'";
		$rows = $db->fetch_array ( $sql );
		foreach ( $rows as $row ) {
			$bus = new BusGrafoTratte ( $row ['GestioneOttimizzataFlottaId'] );
			$flotta = new GestioneOttimizzataFlotta ();
			$flotta->conn = $db;
			$bus->nome = $flotta->getNome ( $row ['GestioneOttimizzataFlottaId'] );
			
			$this->flotta [$row ['GestioneOttimizzataFlottaId']] = $bus;
		}
		
		// caricamento bus in nodi
		foreach ( $this->graph->nodes as $node ) {
			$sql = "SELECT BusPartenza, BusArrivo
	FROM RT_GestioneOttimizzataNodo
	WHERE
	LineaId=$lineaId
	AND CorsaId=$corsaId
	AND CorsaDataPartenza='$corsaDataPartenza'
					AND Comune=$node->id";
			$rows = $db->fetch_array ( $sql );
			foreach ( $rows as $row ) {
				if (isset ( $row ['BusPartenza'] )) {
					$node->busPartenza [$row ['BusPartenza']] = $row ['BusPartenza'];
				}
				if (isset ( $row ['BusArrivo'] )) {
					$node->busArrivo [$row ['BusArrivo']] = $row ['BusArrivo'];
				}
			}
		}
		
		foreach ( $this->flotta as $bus ) {
			$sql = "SELECT Comune, Ordine
	FROM RT_GestioneOttimizzataNodo
	WHERE
	LineaId=$lineaId
	AND CorsaId=$corsaId
	AND CorsaDataPartenza='$corsaDataPartenza'
		AND (BusArrivo=$bus->id OR BusPartenza=$bus->id)
			Order BY Ordine";
			$rows = $db->fetch_array ( $sql );
			foreach ( $rows as $row ) {
				if (! isset ( $bus->comuni [$row ['Ordine']] ['comune'] )) {
					$bus->comuni [$row ['Ordine']] ['comune'] = $row ['Comune'];
				}
			}
		}
		
		$prenotazioneDettaglio = new PrenotazioneDettaglio ();
		$prenotazioneDettaglio->conn = $db;
		// caricamento passeggeri in bus
		foreach ( $this->flotta as $bus ) {
			// selezione dei passeggeri tramite numero prenotazione
			$sql = "SELECT Comune, ComuneArrivo, PrenotazioneNumero, Ordine
			FROM RT_GestioneOttimizzataPasseggeri
				WHERE
				LineaId=$lineaId
					AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND Bus=$bus->id
				ORDER BY Ordine, PrenotazioneNumero";
			$rows = $db->fetch_array ( $sql );
			foreach ( $rows as $row ) {
				// controllo se numero prenotazione e' incluso (per rimborso o cambio)
				if (! ($prenotazioneDettaglio->isEscluso ( $row ['PrenotazioneNumero'], $lineaId, $corsaId, $corsaDataPartenza ))) {
					// caricamento dal db
					if (! isset ( $bus->comuni [$row ['Ordine']] ['comune'] )) {
						$bus->comuni [$row ['Ordine']] ['comune'] = $row ['Comune'];
					}
					$bus->comuni [$row ['Ordine']] ['passeggeri'] [$row ['ComuneArrivo']] [] = $row ['PrenotazioneNumero'];
					if (isset ( $this->graph->nodes [$row ['Comune']]->destinazioni [$row ['ComuneArrivo']] ))
						$this->graph->nodes [$row ['Comune']]->destinazioni [$row ['ComuneArrivo']] ++;
					else
						$this->graph->nodes [$row ['Comune']]->destinazioni [$row ['ComuneArrivo']] = 1;
				} else {
					// cancellazione numero prenotazione dal db
					$this->deleteNumeroPrenotazione ( $row ['PrenotazioneNumero'], $lineaId, $corsaId, $corsaDataPartenza );
				}
			}
		}
		
		foreach ( $this->flotta as $bus ) {
			$ultimaFermata = sizeof ( $bus->comuni );
			$deposito = 0;
			foreach ( $bus->comuni [$ultimaFermata - 1] ['passeggeri'] as $ultimoComune => $passeggeri ) {
				$deposito = $ultimoComune;
			}
			
			if (isset ( $this->graph->nodes [$deposito]->destinazioni [$deposito] ))
				$this->graph->nodes [$deposito]->destinazioni [$deposito] ++;
			else
				$this->graph->nodes [$deposito]->destinazioni [$deposito] = 1;
		}
	}
	function gestioneCaricamentoPrenotazione($prenotazioneNumero, $idLinea, $idCorsa, $corsaDataPartenza) {
		// conto il numero di volte in cui e' presente numero Prenotazione
		$sql = "SELECT COUNT(*) as tot
	FROM RT_GestioneOttimizzataPasseggeri
	WHERE
	LineaId=$idLinea
	AND CorsaId=$idCorsa
	AND CorsaDataPartenza='$corsaDataPartenza'
	AND PrenotazioneNumero=$prenotazioneNumero";
		$tot = $this->conn->query_first ( $sql );
		// controllo se ci sono gia' operazioni su questo numero prenotazione
		$sql = "SELECT COUNT(*) as tot
	FROM RT_GestioneOttimizzataModifiche
	WHERE
	LineaId=$idLinea
		AND CorsaId=$idCorsa
		AND CorsaDataPartenza='$corsaDataPartenza'
		AND PrenotazioneNumero=$prenotazioneNumero";
		$modifiche = $this->conn->query_first ( $sql );
		if ($tot ['tot'] == 0 && $modifiche ['tot'] == 0) {
			// � una nuova prenotazione eseguita dopo la generazione automatica e va segnalata
			$prenotazione ['LineaId'] = $idLinea;
			$prenotazione ['CorsaId'] = $idCorsa;
			$prenotazione ['NumeroPrenotazione'] = $prenotazioneNumero;
			$prenotazione ['DataPartenza'] = $corsaDataPartenza;
			$this->conn->insert ( 'RT_GestionePrenotazioniSenzaBus', $prenotazione );
			
			// memorizzo operazione di nuova prenotazione
			$operazione ['LineaId'] = $idLinea;
			$operazione ['CorsaId'] = $idCorsa;
			$operazione ['CorsaDataPartenza'] = $corsaDataPartenza;
			$operazione ['PrenotazioneNumero'] = $prenotazioneNumero;
			$operazione ['Aggiungi'] = 1;
			$prenotazioneDettaglio = new PrenotazioneDettaglio ();
			$prenotazioneDettaglio->conn = $this->conn;
			$operazione ['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione ( $prenotazioneNumero, $idLinea, $idCorsa, $corsaDataPartenza );
			$this->conn->insert ( 'RT_GestioneOttimizzataModifiche', $operazione );
		}
	}
        
	function calcoloImportanza($db) {
		$sql = "SELECT TrattaId FROM RT_Tratta WHERE LineaId=$this->idLinea";
		$rowsTratte = $db->fetch_array ( $sql );

		foreach ( $rowsTratte as $key => $tratta ) {
			$sql = "SELECT ComuneId, ImportanzaTratta FROM RT_Fermata WHERE TrattaId=" . $tratta ['TrattaId'] . " ORDER BY FermataPeso";
			$rowsFermate = $db->fetch_array ( $sql );
			$tot = count($rowsFermate);
			for($jj = 0; $jj < $tot; $jj++){
				$key2=$jj;
				$fermata=$rowsFermate[$key2];
				for($kk = $jj+1; $kk<$tot; $kk++){
					$key3 = $kk;
					$fermata2=$rowsFermate[$key3];
					if (array_key_exists ( $fermata2 ['ComuneId'], $this->graph->nodes [$fermata ['ComuneId']]->descents )) {
						if (! isset ( $this->graph->nodes [$fermata ['ComuneId']]->importanza [$fermata2 ['ComuneId']] ) || ($this->graph->nodes [$fermata ['ComuneId']]->importanza [$fermata2 ['ComuneId']] > $fermata2 ['ImportanzaTratta'])) {
							$this->graph->nodes [$fermata ['ComuneId']]->importanza [$fermata2 ['ComuneId']] = $fermata2 ['ImportanzaTratta'];
						}
					}
				}
			}
		}
		
		foreach ( $this->graph->nodes as $key => $node ) {
			foreach ( $this->graph->nodes [$key]->descents as $key2 => $value2 ) {
				if (isset ( $this->graph->nodes [$key]->importanza [$key2] ) && $this->graph->nodes [$key]->importanza [$key2] > 0) {
					foreach ( $this->graph->nodes [$key]->descents as $key3 => $value3 ) {
						if (in_array ( $key3, $this->graph->nodes [$key2]->descents )) {
							
							$this->graph->nodes [$key]->importanza [$key3] = $this->graph->nodes [$key]->importanza [$key2];
						}
					}
				}
				if (! isset ( $this->graph->nodes [$key]->importanza [$key2] )) {
					$this->graph->nodes [$key]->importanza [$key2] = 0;
				}
			}
		}
	}
	
public function getTratte($percorso, &$trattaPartenza, &$trattaArrivo){
		$end = false;
		$dim = count($percorso);
		$tratte = array();
		for($ii = 0; $ii<$dim; $ii++){
			$tratte[$ii] = -1;
		}
	
		while(!$end){
	
			for($ii = 0; $ii<$dim; $ii++){
				if($tratte[$ii] == -1){
					if(count($this->graph->edges[$percorso[$ii]]->info3)==1){
						foreach ($this->graph->edges[$percorso[$ii]]->info3 as $tratta)
							$tratte[$ii] = $tratta;
					}else{
						if(($ii-1 > 0) && ($tratte[$ii-1] != -1) && in_array($this->graph->edges[$percorso[$ii]]->info3, $tratte[$ii-1])){
							$tratte[$ii] = $tratte[$ii-1];
						}else if(($ii+1 < $dim) && ($tratte[$ii+1] != -1) && in_array($this->graph->edges[$percorso[$ii]]->info3, $tratte[$ii+1])){
							$tratte[$ii] = $tratte[$ii+1];
						}else{
							foreach ($this->graph->edges[$percorso[$ii]]->info3 as $tratta)
								$tratte[$ii] = $tratta;
						}
					}
				}
			}
	
			$count = 0;
			$countOk = 0;
			for($ii = 0; $ii<$dim; $ii++){
				if($tratte[$ii] == -1 && (($ii-1 > 0) && ($tratte[$ii-1] != -1) || ($ii+1 < $dim) && ($tratte[$ii+1] != -1))){
					$count++;
				}
				if($tratte[$ii] > -1){
					$countOk ++;
				}
			}
				
			if($count == 0){
	
				for($ii = 0; $ii<$dim; $ii++){
					if($tratte[$ii] == -1){
						foreach ($this->graph->edges[$percorso[$ii]]->info3 as $tratta)
							$tratte[$ii] = $tratta;
	
					}
				}
	
			}
				
			if($countOk==$dim){
				$end=true;
			}
				
		}
	
		$resoconto = array();
		$trattaPartenza = $tratte[0];
		$trattaArrivo = $tratte[count($tratte) - 1];
	
		foreach ($tratte as $key => $tratta){
			if(isset($resoconto[$tratta])){
				$resoconto[$tratta] += $this->graph->edges[$percorso[$key]]->peso;
			}else{
				$resoconto[$tratta] = $this->graph->edges[$percorso[$key]]->peso;
			}
		}
		return $resoconto;
	}
	
public function onlyTratta($pickup, $dropoff){
		$result['km'] = -1;
		$result['tratta'] = -1;
// 		echo "<br><br>corsaaa ".$this->idCorsa;
		if(isset($pickup) && isset($dropoff)){
			if(isset($this->idCorsa)){
			$sql = "SELECT f.TrattaId FROM RT_Fermata f
					LEFT JOIN RT_Orario o ON (f.FermataId = o.FermataId)
					WHERE f.Stato = 1 AND
					f.Cancella = 0 AND
					f.IsDaConfermare = 0 AND
					f.IsBlackList = 0 AND
					f.WebSelling = 1 AND (
					f.FermataId IN (SELECT FermataPickup FROM RT_CorsaTariffa WHERE CorsaId = ".$this->idCorsa.") OR
					f.FermataId IN (SELECT FermataDropOff FROM RT_CorsaTariffa WHERE CorsaId = ".$this->idCorsa.")) AND
					o.CorsaId =  ".$this->idCorsa." AND
						f.ComuneId = $pickup and
		
						f.TrattaId IN (
						SELECT f.TrattaId FROM RT_Fermata f
						LEFT JOIN RT_Orario o ON (f.FermataId = o.FermataId)
						WHERE f.Stato = 1 AND
						f.Cancella = 0 AND
						f.IsDaConfermare = 0 AND
						f.IsBlackList = 0 AND
						f.WebSelling = 1 AND
						o.CorsaId =  ".$this->idCorsa." AND
						(
						f.FermataId IN (SELECT FermataPickup FROM RT_CorsaTariffa WHERE CorsaId = ".$this->idCorsa.") OR
							f.FermataId IN (SELECT FermataDropOff FROM RT_CorsaTariffa WHERE CorsaId = ".$this->idCorsa.")) AND
							f.ComuneId=$dropoff
							)";
			}else{
				$sql = "SELECT f.TrattaId FROM RT_Fermata f
					LEFT JOIN RT_Orario o ON (f.FermataId = o.FermataId)
					WHERE f.Stato = 1 AND
					f.Cancella = 0 AND
					f.IsDaConfermare = 0 AND
					f.IsBlackList = 0 AND
					f.WebSelling = 1 AND 
									f.ComuneId = $pickup and
				
									f.TrattaId IN (
									SELECT f.TrattaId FROM RT_Fermata f
									LEFT JOIN RT_Orario o ON (f.FermataId = o.FermataId)
									WHERE f.Stato = 1 AND
									f.Cancella = 0 AND
									f.IsDaConfermare = 0 AND
									f.IsBlackList = 0 AND
									f.WebSelling = 1 and
										f.ComuneId=$dropoff
										)";
			}
// 			echo $sql;
			$row = $this->conn->fetch_array ( $sql );
			if(count($row)!=0){
				$result['tratta'] = $row[0]['TrattaId'];
				$sql = "SELECT KmInizioTratta FROM RT_Fermata where ComuneId=$pickup AND TrattaId = ".$result['tratta'];
				$rowA = $this->conn->fetch_array ( $sql );
				$sql = "SELECT KmInizioTratta FROM RT_Fermata where ComuneId=$dropoff AND TrattaId = ".$result['tratta'];
				$rowB = $this->conn->fetch_array ( $sql );
				$result['km'] = $rowB[0]['KmInizioTratta'] - $rowA[0]['KmInizioTratta'];
			}else{
				$result['km'] = -2;
				$result['tratta'] = -2;
			}
		}
// 		var_dump( $result);
		return $result;
	}
	
}

?>