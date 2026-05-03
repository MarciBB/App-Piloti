<?php
// ini_set('display_errors', 1);
//     ini_set('error_reporting', E_ERROR);
//     ini_set('max_execution_time', 36000);
  
include_once($classespath_."Graph/Graph.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."class.Flotta.php");
include_once($classespath_."class.GestioneOttimizzataFlotta.php");
include_once($classespath_."class.Fermata.php");

class InterscambioGraph{
	
	public $idLinea;
	public $idCorsa;
	public $corsaDataPartenza;
	public $conn;
	public $graph;
	public $flotta;
	public $flottaNew;
	public $postiBus;
	public $unire;
	
	function __construct($idLinea, $idCorsa, $corsaDataPartenza, $conn, $comunePartenza, $comuneDestinazione){
		$sql = "SELECT TotalePosti
				FROM RT_TipologiaBus
				WHERE TipologiaBusId=1";
		$row = $conn->query_first($sql);

		$this->postiBus = $row['TotalePosti'];
		unset($row);
		
		/*creazione grafo*/
		$this->graph = new Graph();
		$this->idLinea = $idLinea;
		$this->idCorsa = $idCorsa;
		$this->corsaDataPartenza = $corsaDataPartenza;
		$this->conn = $conn;
		$db=$this->conn;
        
		if (! isset ( $idCorsa )) {
			$sql = "SELECT TrattaId FROM RT_Tratta
			WHERE LineaId = $this->idLinea AND 
			Stato = 1 AND
			Cancella = 0 AND
			DaConfermare = 0
			ORDER BY TrattaTipoId, TrattaPeso ASC";
// 			echo $sql;
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
					$nodoFermata = new NodoFermata ( $fermata ['ComuneId'] );
					$this->graph->addNode ( $nodoFermata );
				}
				$i = 0;
				while ( $i < (sizeof ( $rowsFermate ) - 1) ) {
					$temp1 = $rowsFermate [$i];
					$temp2 = $rowsFermate [$i + 1];
					if($temp2 ['ComuneId'] != $temp1 ['ComuneId']){
						if(!array_key_exists($temp2 ['ComuneId'] . '-' . $temp1 ['ComuneId'], $this->graph->edges) && !array_key_exists($temp1 ['ComuneId'] . '-' . $temp2 ['ComuneId'], $this->graph->edges)){
							$this->graph->connectNodes ( $temp1 ['ComuneId'], $temp2 ['ComuneId'], 0, $temp2 ['KmInizioTratta'] - $temp1 ['KmInizioTratta'] );
							$this->graph->edges [$temp1 ['ComuneId'] . '-' . $temp2 ['ComuneId']]->info3 [$tratta ['TrattaId']] = $tratta ['TrattaId'];
						}
					}
					$i ++;
				}
			}
			
// 			print_r($this->graph->nodes);
		} else {
// 			echo "bha";
			//selezione tratte
			$sql = "SELECT DISTINCT t.TrattaId
			FROM RT_Tratta t 
			WHERE t.Stato = 1 AND
			t.Cancella = 0 AND
			t.LineaId = $this->idLinea AND 
			t.DaConfermare = 0
			ORDER BY TrattaTipoId, TrattaPeso ASC";

			$rowsTratte = $db->fetch_array ( $sql );
// 			echo "<br>Tratte selezionate<br>"; var_dump($rowsTratte);
// 			echo "<br>Corsa $idCorsa";
			foreach ( $rowsTratte as $key => $tratta ) {
				//selezione comuni per tratta
				$sql = "SELECT ComuneId, KmInizioTratta FROM RT_Fermata as f
					WHERE f.Stato = 1 AND
					f.Cancella = 0 AND
					f.IsDaConfermare = 0 AND
					f.IsBlackList = 0 AND
					f.TrattaId = " . $tratta ['TrattaId'] . " 
					ORDER BY f.FermataPeso";

				$rowsFermate = $db->fetch_array ( $sql );

				//inserimento nodi in grafo
				foreach ( $rowsFermate as $key2 => $fermata ) {
					$nodoFermata = new NodoFermata ( $fermata ['ComuneId'] );
					$this->graph->addNode ( $nodoFermata );
				}
				$i = 0;
				while ( $i < (sizeof ( $rowsFermate ) - 1) ) {
					$temp1 = $rowsFermate [$i];
					$temp2 = $rowsFermate [$i + 1];
					
					if($temp2 ['ComuneId'] != $temp1 ['ComuneId']){
						if(!array_key_exists($temp2 ['ComuneId'] . '-' . $temp1 ['ComuneId'], $this->graph->edges) && !array_key_exists($temp1 ['ComuneId'] . '-' . $temp2 ['ComuneId'], $this->graph->edges)){
							$this->graph->connectNodes ( $temp1 ['ComuneId'], $temp2 ['ComuneId'], 0, $temp2 ['KmInizioTratta'] - $temp1 ['KmInizioTratta'] );
							$this->graph->edges [$temp1 ['ComuneId'] . '-' . $temp2 ['ComuneId']]->info3 [$tratta ['TrattaId']] = $tratta ['TrattaId'];
						}
					}
					$i ++;
				}
			}
		}
		//echo($sql);
		if(isset($idCorsa)){
			/*inizializzazione salite e discese*/
            $saliteTotali=0;
            $disceseTotali=0;
            
            $this->graph->nodes[$comunePartenza]->salite = 1;
            $this->graph->nodes[$comunePartenza]->destinazioni[$comuneDestinazione] = 1;
            
            $this->graph->nodes[$comuneDestinazione]->discese = 1;
            $this->graph->nodes[$comunePartenza]->biglietti[$comuneDestinazione] = array();
            $this->graph->nodes[$comunePartenza]->bigliettiSalite[$comuneDestinazione] = array();
			$this->graph->nodes[$comunePartenza]->biglietti[$comuneDestinazione][]=1;
            $this->graph->nodes[$comunePartenza]->bigliettiSalite[$comuneDestinazione][]=1;
            
		}
		
		/*calcolo discendenti dei nodi*/
		$this->graph->calculateDescent2();
		
		if(isset($idCorsa)){
			
			
// 				echo "<br> inizio gruppi";
				/*calcolo gruppi*/
				$this->calcolaGruppi();
// 				echo "<br> fine gruppi";
				
				//creazione flotta
				$edgeInseriti = array();
				
				$id = 0;
				foreach ( $rowsTratte as $key => $tratta ) {
					$bus = new Bus($id);
					$bus->comuni = array();
                                      // $bus->comuni = (array) null;
					$bus->percorso = array();
					$bus->trattaId = $tratta ['TrattaId'];
					
					//selezione comuni per tratta
					$sql = "SELECT ComuneId, KmInizioTratta FROM RT_Fermata as f
					WHERE f.Stato = 1 AND
					f.Cancella = 0 AND
					f.IsDaConfermare = 0 AND
					f.IsBlackList = 0 AND
					f.TrattaId = " . $tratta ['TrattaId'] . "
					ORDER BY f.FermataPeso";
                                      
					$rowsFermate = $db->fetch_array ( $sql );
					
					$comuni = array();
					
					$inizio = null;
					$i = 0;
					$passeggeri = false;
					while ( $i < (sizeof ( $rowsFermate ) - 1) ) {
						
						$temp1 = $rowsFermate [$i];
						$temp2 = $rowsFermate [$i + 1];
						$edge = $temp1 ['ComuneId'] . '-' . $temp2 ['ComuneId'];
						
						if(!array_key_exists($edge, $edgeInseriti)) {
							$edgeInseriti[$edge] = $edge;
							
							$temp['comune'] = $temp1 ['ComuneId'];
							$temp['passeggeri'] = array();
							if(array_key_exists ( $edge , $this->graph->edges )){
								foreach ($this->graph->edges[$edge]->info4 as $key => $value){
									$temp['passeggeri'][$key] = $value;
								}
									
								$bus->comuni[] = $temp;
								$bus->percorso[] = $edge;
								if(count($this->graph->edges[$edge]->info4) > 0) {
									$passeggeri = true;
								}
							}
						} else {
							$temp['comune'] = $temp1 ['ComuneId'];
							$temp['passeggeri'] = array();
							$bus->comuni[] = $temp;
							$bus->percorso[] = $edge;
						}
						$i ++;
					}
					
					if($passeggeri){
						$bus->id = $id;
						$bus->nome = "";
						$this->flottaNew[] = $bus;
						while ( $i < (sizeof ( $rowsFermate ) - 1) ) {
							$temp1 = $bus->comuni [$i]['comune'];
							if(isset($rowsFermate [$i + 1])){
								$temp2 = $rowsFermate [$i + 1]['comune'];
							} else {
								$temp2 = null;
							}
							$this->graph->nodes[$temp1]->busPartenza[$id] = $id;
							if(isset($temp2)){
								$this->graph->nodes[$temp2 ['ComuneId']]->busArrivo[$id] = $id;
							}
						}
					}
					$id++;
				}
				$this->flotta = $this->flottaNew;
				//fine creazione flotta

				//sistemazione bus
				if(isset($this->flotta)){
					foreach ($this->flotta as $id => $bus){
			
						if(count($bus->percorso)==1){
							$bus->comuni[1] = $bus->comuni[0];
							$temp['comune'] = $this->graph->edges[$bus->percorso[0]]->nodeA;
							$temp['passeggeri'] = $this->graph->edges[$bus->percorso[0]]->info4;
							$bus->comuni[0] = $temp;
			
							$this->graph->nodes[$this->graph->edges[$bus->percorso[0]]->nodeA]->busPartenza[$id]=$id;
							$this->graph->nodes[$this->graph->edges[$bus->percorso[0]]->nodeB]->busArrivo[$id]=$id;
						}
			
					}
				
					foreach ($this->flotta as $bus){
			
						$ultimaFermata = sizeof($bus->comuni);
						$deposito = 0;
						$countDepositi = 0;
						foreach ($bus->comuni[$ultimaFermata-1]['passeggeri'] as $ultimoComune => $passeggeri){
							$deposito =  $ultimoComune;
							$countDepositi++;
						}
						if(array_key_exists($deposito, $this->graph->nodes[$bus->comuni[$ultimaFermata - 1]['comune']]->children)){
							if(intval($bus->comuni[count($bus->comuni)-1]['comune'])==intval($bus->comuni[0]['comune'])){
								$ultimaFermata = $ultimaFermata -1;
							}
			
							$bus->comuni[$ultimaFermata]['comune'] = $deposito;
							$bus->comuni[$ultimaFermata]['passeggeri'] = array();
				
							$bus->percorso[$ultimaFermata] = $bus->comuni[$ultimaFermata-1]['comune']."-".$deposito;
			
						}else{
							$ultimaTratta = sizeof($bus->percorso) - 1;
							$deposito = $this->graph->edges[$bus->percorso[$ultimaTratta]]->nodeB;
							$bus->comuni[$ultimaFermata]['comune'] = $deposito;
							$bus->comuni[$ultimaFermata]['passeggeri'] = $this->graph->edges[$bus->percorso[$ultimaTratta]]->info4;
						}
					}
				}
				//fine sistemazione bus

		}
// 		print_r($this->graph->nodes);
	}
	
	
	
	private function calcolaGruppi(){
		/*inizializzazione gruppi*/
		$listaGruppi = array();
              
		foreach ($this->graph->nodes as $node){
			if(isset($node) && isset($node->salite) && $node->salite>0){
				$this->graph->nodes[$node->id]->gruppo = new Gruppo($node->id);
				$gruppo = $this->graph->nodes[$node->id]->gruppo;
				$listaGruppi[] = $this->graph->nodes[$node->id]->gruppo;
			}
		}
		
		/*calcolo iterativo*/
		$ii=0;
		while(sizeof($listaGruppi)>0){
  			$ii++;
  			if($ii>100){
  				break;
  			} 
		
			$gruppoAvanza = $this->getProssimoGruppo($listaGruppi);
			if(!isset($gruppoAvanza->posizione)){
				continue;
			}
// 			echo "<br>Posizione ".$gruppoAvanza->posizione;
			
			//discesa 
			if(array_key_exists($gruppoAvanza->posizione, $gruppoAvanza->passeggeri)){
				$gruppoAvanza->passeggeri[$gruppoAvanza->posizione] = 0;
				unset($gruppoAvanza->passeggeri[$gruppoAvanza->posizione]);
				$gruppoAvanza->totalePasseggeri = $gruppoAvanza->totalePasseggeri - $this->graph->nodes[$gruppoAvanza->posizione]->discese;
			}
			//salita
			if(is_array($this->graph->nodes[$gruppoAvanza->posizione]->destinazioni)){
				foreach ($this->graph->nodes[$gruppoAvanza->posizione]->destinazioni as $destinazione => $num){
					if(array_key_exists($destinazione, $gruppoAvanza->passeggeri)){
						$gruppoAvanza->passeggeri[$destinazione] = $gruppoAvanza->passeggeri[$destinazione] + $num;
					}else{
						$gruppoAvanza->passeggeri[$destinazione] = $num;
					}
					$gruppoAvanza->totalePasseggeri = $gruppoAvanza->totalePasseggeri + $num;
				}
			}
			
			$nuoviGruppi = array();
                       
			//divisione per destinazione
			foreach ($gruppoAvanza->passeggeri as $destinazione => $num){
// 				echo "<br>destinazione $destinazione <br>";
// 				echo "<br>discendenti"; var_dump($this->graph->nodes[$child]->descents);	
				
				$privata = 0;
				$prossimaFermata = array();
// 				echo "<br>figli<br>";
				foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child){
// 					echo "$child, ";
					if(strcmp($destinazione, $child)==0){
						$prossimaFermata[$child] = $child;
					}else{
						if(!is_array($this->graph->nodes[$child]->descents)){
							$this->graph->nodes[$child]->descents = array();
						}
						if(in_array($destinazione, $this->graph->nodes[$child]->descents)){
							if($privata == 0){
								$privata = 1;
								$prossimaFermata[$child] = $child;
							}else if($privata == 1){
								$privata = 2;
								$prossimaFermata[$child] = $child;
							}
						}
					}
				}

				$nextNode = array_shift ( $prossimaFermata );
				
				unset($prossimaFermata);
				unset($privata);
				unset($min);
				if(isset($nextNode)){
					if(!isset($this->graph->nodes[$nextNode]->gruppo)){
						$this->graph->nodes[$nextNode]->gruppo = new Gruppo($nextNode);
					}
					if(!array_key_exists($destinazione, $this->graph->nodes[$nextNode]->gruppo->passeggeri)){
						$this->graph->nodes[$nextNode]->gruppo->passeggeri[$destinazione] = $num;
					}else{
						$this->graph->nodes[$nextNode]->gruppo->passeggeri[$destinazione] = $this->graph->nodes[$nextNode]->gruppo->passeggeri[$destinazione] + $num;
					}
					$this->graph->nodes[$nextNode]->gruppo->totalePasseggeri = $this->graph->nodes[$nextNode]->gruppo->totalePasseggeri + $num;
					if(isset($this->graph->nodes[$nextNode]->biglietti[$destinazione])){
						$this->graph->nodes[$nextNode]->biglietti[$destinazione] = array_merge($this->graph->nodes[$nextNode]->biglietti[$destinazione], $this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione]);
					}else{
						$this->graph->nodes[$nextNode]->biglietti[$destinazione] = $this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione];
					}
			
					$this->graph->nodes[$nextNode]->gruppo->posizionePrecedente[$gruppoAvanza->posizione] = $gruppoAvanza->posizione;
			
					$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info2=$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info2 + $num;
					$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info = array_merge($this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info, $this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione]);
					if(!isset($this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info4[$destinazione])){
						$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info4[$destinazione] = array();
					}
			
					$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info4[$destinazione] = array_merge($this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info4[$destinazione], $this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione]);
	
	// 				echo "<br>destinazione scelta $nextNode";
					$nuoviGruppi[$nextNode] = $nextNode;
					unset($nextNode);
				}
			}
			
			//organizzazione fermata successivo
			foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child){
				if($this->graph->nodes[$child]->salite>0){
					if(!isset($this->graph->nodes[$child]->gruppo)){
						$this->graph->nodes[$child]->gruppo = new Gruppo($child);
					}
					$nuoviGruppi[$child] = $child;
				}
			}
			
			//caricamento in lista
			foreach ($nuoviGruppi as $key => $nodo){
				if(!in_array($this->graph->nodes[$nodo]->gruppo, $listaGruppi)){
					array_push($listaGruppi, $this->graph->nodes[$nodo]->gruppo);
				}
			}
			unset($nuoviGruppi);

			unset($this->graph->nodes[$gruppoAvanza->posizione]->gruppo);
			unset($this->graph->nodes[$gruppoAvanza->posizione]->biglietti);
		}
 	}

	private function getProssimoGruppo(&$listaGruppi){
		$gruppo = null;
		while(!isset($gruppo)){
			$gruppo = array_pop($listaGruppi);
			if(!$this->findPosizione($gruppo, $listaGruppi)){
				return $gruppo;
			}else{
				array_unshift($listaGruppi, $gruppo);
				$gruppo = null;
			}
		}
	}
	 
	private function findPosizione($gruppo, $listaGruppi){
		$find = false;
		if(sizeof($listaGruppi)==0){
			return false;
		}else{
			if(isset($gruppo)){
 				$i=0;
 				while(!$find && $i<sizeof($listaGruppi)){
					$altro = $listaGruppi[$i]->posizione;	
					if(in_array($gruppo->posizione, $this->graph->nodes[$altro]->descents)){
						$find = true;
					}
  					$i++;
				}
			}
			return $find;
		}
	}

	public function deleteAll($lineaId, $corsaId, $corsaDataPartenza){
		//caricamento bus
// 		$sql = "SELECT BusId FROM RT_GestioneOttimizzataFlotta
// 			WHERE
// 			LineaId=$lineaId
// 			AND CorsaId=$corsaId
// 			AND CorsaDataPartenza='$corsaDataPartenza'";
// 		$rows = $this->conn->fetch_array($sql);
// 		if(sizeof($rows)>0){
// 			foreach ($rows as $row){
// 				$sql = "DELETE FROM RT_Flotta
// 						WHERE
// 						FlottaId=".$row['BusId'];
// 				$this->conn->query($sql);
// 			}
// 		}
		
		$sql = "DELETE FROM RT_GestioneOttimizzataNodo
				WHERE
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'";
		$this->conn->query($sql);
		$sql = "DELETE FROM RT_GestioneOttimizzataFlotta
				WHERE
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'";
		$this->conn->query($sql);
		$sql = "DELETE FROM RT_GestioneOttimizzataPasseggeri
				WHERE
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'";
		$this->conn->query($sql);
		$sql = "DELETE FROM RT_PreparazioneBusAutisti
				WHERE
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND DataPartenza='$corsaDataPartenza'";
		$this->conn->query($sql);
		$sql = "DELETE FROM RT_GestioneOttimizzataModifiche
				WHERE
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'";
		$this->conn->query($sql);
		$sql = "DELETE FROM RT_GestionePrenotazioniSenzaBus
				WHERE
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND DataPartenza='$corsaDataPartenza'";
		$this->conn->query($sql);
	}
	
	function deleteNumeroPrenotazione($numeroPrenotazione, $lineaId, $corsaId, $corsaDataPartenza){
		//conto il numero di volte in cui � presente numero Prenotazione
		$sql = "SELECT COUNT(*) as tot 
				FROM RT_GestioneOttimizzataPasseggeri
				WHERE
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND PrenotazioneNumero=$numeroPrenotazione";
		$tot = $this->conn->query_first($sql);
		//se >0 deve essere cancellato, altrimenti e' stato gia' cancellato o non e' presente
		if($tot['tot']>0){
			$sql = "DELETE FROM RT_GestioneOttimizzataPasseggeri
					WHERE
					LineaId=$lineaId
					AND CorsaId=$corsaId
					AND CorsaDataPartenza='$corsaDataPartenza'
					AND PrenotazioneNumero=$numeroPrenotazione";
			$this->conn->query($sql);
			//memorizzazione operazione di eliminazione dalla gestione
			$operazione['LineaId'] = $lineaId;
			$operazione['CorsaId'] = $corsaId;
			$operazione['CorsaDataPartenza'] = $corsaDataPartenza;
			$operazione['PrenotazioneNumero'] = $numeroPrenotazione;
			$operazione['Aggiungi'] = 0;
			$prenotazioneDettaglio = new PrenotazioneDettaglio();
			$prenotazioneDettaglio->conn = $this->conn;
			$operazione['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione($numeroPrenotazione, $lineaId, $corsaId, $corsaDataPartenza);
			$this->conn->insert('RT_GestioneOttimizzataModifiche', $operazione);
		}
	} 
	
	function salvaDB($lineaId, $corsaId, $corsaDataPartenza){
		global $user, $direzioneCOnnessione;
// 		if ($this->direzioneConnessione==1)
        $this->deleteAll($lineaId, $corsaId, $corsaDataPartenza);
		
		$db = $this->conn;
		$sql = "SELECT FlottaId
				FROM RT_Flotta
				WHERE IsDefault=1";
		$row = $db->query_first($sql);
		$idFlotta = $row['FlottaId'];
		
		$storico=new StoricoOperazioni();
		$storico->conn = $db;
		
		//inserimento bus di default
		$count=1;
		foreach ($this->flotta as $i=>$bus){
			
			//inserimento bus in linea
			$data = array();
			$data['LineaId'] = $lineaId;
			$data['CorsaId'] = $corsaId;
			$data['CorsaDataPartenza'] = $corsaDataPartenza;
			$data['BusId'] = $idFlotta;
			$data['Nome'] = $count;
			
			$km = 0;
// 			foreach ($bus->percorso as $edge){
// 				if(array_key_exists($edge, $this->graph->edges)){
// 					$km += $this->graph->edges[$edge]->peso;
// 				}else{
// 					list($nodeA, $nodeB) = split('[/.-]', $edge);
// 					$km += $this->graph->getKmPercorsoBreve($nodeA, $nodeB);
// 				}
// 			}
			$data['KmPercorsi'] = $km;
			
			$count++;
			$data=$storico->operazioni_insert($data, $user);
			$lastid = $db->insert("RT_GestioneOttimizzataFlotta", $data);
			
			$daCambiare = $bus->id;
			$bus->id = $lastid;
                        unset($comunePre);
			foreach ($bus->comuni as $key=>$comune){
				//aggiornamento info bus nei nodi
				if(isset($this->graph->nodes[$comune['comune']]->busPartenza[$daCambiare])){
					$this->graph->nodes[$comune['comune']]->busPartenza[$daCambiare] = $bus->id;
				}
				if(isset($this->graph->nodes[$comune['comune']]->busArrivo[$daCambiare])){
					$this->graph->nodes[$comune['comune']]->busArrivo[$daCambiare] = $bus->id;
				}
			
				//inserimento nodi
				if(isset($comunePre) || (count($bus->percorso)==1 && $key==0)){
					$data = array();
					$data['LineaId'] = $lineaId;
					$data['CorsaId'] = $corsaId;
					$data['CorsaDataPartenza'] = $corsaDataPartenza;
                                        				
					$data['BusPartenza'] = $bus->id;
                                        
                    if($key-1>=0){
                    	$data['Ordine'] = $key-1;
                    	$data['Comune'] = $comunePre;
                    }else{
                    	$data['Ordine'] = 0;
                    	$data['Comune'] = $comune['comune'];
                    }
                    
                    if(isset($bus->percorso[$key]) && isset($this->graph->edges[$bus->percorso[$key]])){
						$tratta = $this->graph->edges[$bus->percorso[$key]]->info3;
                    } else {
                    	$tratta = null;
                    }
                    if((!isset($tratta) || count($tratta)==0) && ($key-2)>=0 && isset($this->graph->edges[$bus->percorso[$key-2]]->info3)){
                    	$tratta = $this->graph->edges[$bus->percorso[$key-2]]->info3;
                    }
                    if(isset($tratta)) {
						$tt = array_shift($tratta);
						$data['TrattaId'] = $tt;
                    } else {
                    	$data['TrattaId'] = 0;
                    }
                    $data=$storico->operazioni_insert($data, $user);
                    $lastid = $db->insert("RT_GestioneOttimizzataNodo", $data);
				}
				if($key>0){
					$data = array();
					$data['LineaId'] = $lineaId;
					$data['CorsaId'] = $corsaId;
					$data['CorsaDataPartenza'] = $corsaDataPartenza;
					$data['Comune'] = $comune['comune'];
					$data['BusArrivo'] = $bus->id;
					$data['Ordine'] = $key;
					
					if(count($bus->percorso)>1){
						if(isset($bus->percorso[$key]) && isset($this->graph->edges[$bus->percorso[$key]])){
							$tratta = $this->graph->edges[$bus->percorso[$key]]->info3;
						} else {
							$tratta = null;
						}
                    }else{
                    	if(isset($bus->percorso[$key-1])){
                    		$tratta = $this->graph->edges[$bus->percorso[$key-1]]->info3;
                    	} else {
                    		$tratta = null;
                    	}
                    }
                    if((!isset($tratta) || count($tratta)==0) && $key-2>=0 && isset($this->graph->edges[$bus->percorso[$key-2]]->info3)){
                    	$tratta = $this->graph->edges[$bus->percorso[$key-2]]->info3;
                    }
                    if(isset($tratta)) {
						$tt = array_shift($tratta);
						$data['TrattaId'] = $tt;
                    } else {
                    	$data['TrattaId'] = 0;
                    }
					$data=$storico->operazioni_insert($data, $user);                                 
                    $lastid = $db->insert("RT_GestioneOttimizzataNodo", $data);
				}
				if(count($bus->percorso)>1)
                                    $comunePre = $comune['comune'];
				
				//inserimento passeggeri
				foreach ($comune['passeggeri'] as $dest => $biglietti){
					foreach ($biglietti as $key1 => $numeroPrenotazione){
						$data = array();
						$data['LineaId'] = $lineaId;
						$data['CorsaId'] = $corsaId;
						$data['CorsaDataPartenza'] = $corsaDataPartenza;
						$data['Comune'] = $comune['comune'];
						$data['ComuneArrivo'] = $dest;
						$data['Bus'] = $bus->id;
						$data['PrenotazioneNumero'] = $numeroPrenotazione;
						$data['Ordine'] = $key;
						
						$data=$storico->operazioni_insert($data, $user);
						$lastid = $db->insert("RT_GestioneOttimizzataPasseggeri", $data);
					}
				}
			}			
		}
		return $count;
	}
	
	function caricaDB($lineaId, $corsaId, $corsaDataPartenza){
		$db=$this->conn;
		
		//caricamento bus
		$sql = "SELECT GestioneOttimizzataFlottaId FROM RT_GestioneOttimizzataFlotta 
				WHERE 
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'";
		$rows = $db->fetch_array($sql);
		foreach ($rows as $row){
			$bus = new Bus($row['GestioneOttimizzataFlottaId']);
			$flotta = new GestioneOttimizzataFlotta(null, $db);
			$flotta->conn=$db;
			$bus->nome=$flotta->getNome($row['GestioneOttimizzataFlottaId']);
			
			$this->flotta[$row['GestioneOttimizzataFlottaId']] = $bus;
		}
		
		//caricamento bus in nodi
		foreach ($this->graph->nodes as $node){
			$sql = "SELECT BusPartenza, BusArrivo
					FROM RT_GestioneOttimizzataNodo
					WHERE
					LineaId=$lineaId
					AND CorsaId=$corsaId
					AND CorsaDataPartenza='$corsaDataPartenza'
					AND Comune=$node->id";
			$rows = $db->fetch_array($sql);
			foreach ($rows as $row){
				if(isset($row['BusPartenza'])){
					$node->busPartenza[$row['BusPartenza']] = $row['BusPartenza'];	
				}
				if(isset($row['BusArrivo'])){
					$node->busArrivo[$row['BusArrivo']] = $row['BusArrivo'];
				}
			}
		}
		
		if(is_array($this->flotta)){
			foreach ($this->flotta as $bus){
				$sql = "SELECT Comune, Ordine
				FROM RT_GestioneOttimizzataNodo
				WHERE
				LineaId=$lineaId
				AND CorsaId=$corsaId
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND (BusArrivo=$bus->id OR BusPartenza=$bus->id)
				Order BY Ordine";
				$rows = $db->fetch_array($sql);
				foreach ($rows as $row){
					if(!isset($bus->comuni[$row['Ordine']]['comune'])){
					$bus->comuni[$row['Ordine']]['comune'] = $row['Comune'];
						}
				}
			}
		
		
			$prenotazioneDettaglio = new PrenotazioneDettaglio(null);
			$prenotazioneDettaglio->conn = $db;
			//caricamento passeggeri in bus
			foreach ($this->flotta as $bus){
				//selezione dei passeggeri tramite numero prenotazione
				$sql = "SELECT Comune, ComuneArrivo, PrenotazioneNumero, Ordine
						FROM RT_GestioneOttimizzataPasseggeri
						WHERE
						LineaId=$lineaId
						AND CorsaId=$corsaId
						AND CorsaDataPartenza='$corsaDataPartenza'
						AND Bus=$bus->id
						ORDER BY Ordine, PrenotazioneNumero";
				$rows = $db->fetch_array($sql);
				foreach ($rows as $row){	
					//controllo se numero prenotazione e' incluso (per rimborso o cambio)
					if(!($prenotazioneDettaglio->isEscluso($row['PrenotazioneNumero'], $lineaId, $corsaId, $corsaDataPartenza))){
						//caricamento dal db
						if(!isset($bus->comuni[$row['Ordine']]['comune'])){
							$bus->comuni[$row['Ordine']]['comune'] = $row['Comune'];
						}
						$bus->comuni[$row['Ordine']]['passeggeri'][$row['ComuneArrivo']][] = $row['PrenotazioneNumero'];
						if(isset($this->graph->nodes[$row['Comune']]->destinazioni[$row['ComuneArrivo']]))
		 					$this->graph->nodes[$row['Comune']]->destinazioni[$row['ComuneArrivo']]++;
						else
							$this->graph->nodes[$row['Comune']]->destinazioni[$row['ComuneArrivo']]=1;
					}else{
						//cancellazione numero prenotazione dal db
						$this->deleteNumeroPrenotazione($row['PrenotazioneNumero'], $lineaId, $corsaId, $corsaDataPartenza);
					}
				}
			}
		
			foreach ($this->flotta as $bus){
				$ultimaFermata = sizeof($bus->comuni);
				$deposito = 0;
				if(isset($bus->comuni[$ultimaFermata-1]['passeggeri'])){
					foreach ($bus->comuni[$ultimaFermata-1]['passeggeri'] as $ultimoComune => $passeggeri){
						$deposito =  $ultimoComune;
					}
				}
			
				if(isset($this->graph->nodes[$deposito]->destinazioni[$deposito]))
					$this->graph->nodes[$deposito]->destinazioni[$deposito]++;
				else
					$this->graph->nodes[$deposito]->destinazioni[$deposito]=1;
			}
		}
	}
		
	function gestioneCaricamentoPrenotazione($prenotazioneNumero, $idLinea, $idCorsa, $corsaDataPartenza){
		//conto il numero di volte in cui e' presente numero Prenotazione
		$sql = "SELECT COUNT(*) as tot 
				FROM RT_GestioneOttimizzataPasseggeri
				WHERE
				LineaId=$idLinea
				AND CorsaId=$idCorsa
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND PrenotazioneNumero=$prenotazioneNumero";
		$tot = $this->conn->query_first($sql);
		//controllo se ci sono gia' operazioni su questo numero prenotazione
		$sql = "SELECT COUNT(*) as tot
				FROM RT_GestioneOttimizzataModifiche
				WHERE
				LineaId=$idLinea
				AND CorsaId=$idCorsa
				AND CorsaDataPartenza='$corsaDataPartenza'
				AND PrenotazioneNumero=$prenotazioneNumero";
		$modifiche = $this->conn->query_first($sql);
		if($tot['tot']==0 && $modifiche['tot']==0){
			//� una nuova prenotazione eseguita dopo la generazione automatica e va segnalata
			$prenotazione['LineaId'] = $idLinea;
			$prenotazione['CorsaId'] = $idCorsa;
			$prenotazione['NumeroPrenotazione'] = $prenotazioneNumero;
			$prenotazione['DataPartenza'] = $corsaDataPartenza;
			$this->conn->insert('RT_GestionePrenotazioniSenzaBus', $prenotazione);
			
			//memorizzo operazione di nuova prenotazione
			$operazione['LineaId'] = $idLinea;
			$operazione['CorsaId'] = $idCorsa;
			$operazione['CorsaDataPartenza'] = $corsaDataPartenza;
			$operazione['PrenotazioneNumero'] = $prenotazioneNumero;
			$operazione['Aggiungi'] = 1;
			$prenotazioneDettaglio = new PrenotazioneDettaglio(null);
			$prenotazioneDettaglio->conn = $this->conn;
			$operazione['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione($prenotazioneNumero, $idLinea, $idCorsa, $corsaDataPartenza);
			$this->conn->insert('RT_GestioneOttimizzataModifiche', $operazione);
		}
	}

	function checkGestioneOttimizzataModifiche($idLinea, $idCorsa, $corsaDataPartenza){
		$sql = "SELECT * FROM RT_GestioneOttimizzataModifiche gom LEFT JOIN RT_Prenotazione p on (gom.PrenotazioneId=p.PrenotazioneId) WHERE gom.LineaId=$idLinea AND gom.CorsaId=$idCorsa AND gom.CorsaDataPartenza='$corsaDataPartenza'";
		$rows = $this->conn->fetch_array($sql);
		foreach ($rows as $key=>$value){
			if($value['PrenotazioneStato']==6 || ($value['PrenotazioneStato']==4 && $value['Aggiungi']==1)){
				$sql = "DELETE FROM RT_GestioneOttimizzataModifiche
						WHERE
						GestioneOttimizzataModificheId=".$value['GestioneOttimizzataModificheId'];
				$this->conn->query($sql);
			}
		}
		
		$sql = "DELETE FROM RT_GestioneOttimizzataModifiche
						WHERE
						PrenotazioneNumero=0";
		$this->conn->query($sql);
		$sql = "DELETE FROM RT_GestioneOttimizzataPasseggeri
						WHERE
						PrenotazioneNumero=0";
		$this->conn->query($sql);
		
	}
	
	//quando viene richiamata la prima volta $comuneIdPartenza = $comuneId e percorso � un array vuoto
	public function getPercorsi($comuneIdPartenza, $comuneId, $percorso){
// 		echo "<br>".$comuneId;
		if(!array_key_exists($comuneIdPartenza, $this->graph->nodes)){

			//comune Partenza non esiste nel grafo
			return -1;
		} else {
			//comune Partenza esiste nel grafo
			//aggiungo il nodo al percorso
			$percorso[] = intval($comuneId);
			if($this->graph->nodes[$comuneId]->isLeaf){
				//se il comune visitato � una foglia aggiungo il nodo al percorso e lo restituisco
				$new = array();
				foreach ($percorso as $k => $v) {
					$new[$k] = $v;
				}
				$this->graph->nodes[$comuneIdPartenza]->percorsi[] = $new;
				unset($percorso);
			} else {
				//se il comune visitato non � una foglia percorro tutti i suoi figli
				foreach ($this->graph->nodes[$comuneId]->children as $c => $value){

					if((isset($this->graph->nodes[$c]->children) && !array_key_exists($comuneId, $this->graph->nodes[$c]->children))
							&& (isset($this->graph->nodes[$c]->descents) && !array_key_exists($comuneId, $this->graph->nodes[$c]->descents))
							&& !array_key_exists($c, $this->graph->nodes[$c]->descents) && !array_key_exists($c, $this->graph->nodes[$c]->children)
							&& !array_key_exists($c, $percorso) && !array_key_exists($comuneIdPartenza, $this->graph->nodes[$c]->descents)){
						$newPercorso = $percorso;
						$this->getPercorsi($comuneIdPartenza, $c, $newPercorso);
					}
					
				}
				unset($percorso);
				return 0;
			}
		}
	}
	
	public function mergeFlotta($gruppo, $salva = true){
		
		$flotta = $gruppo->flotta;
		if(isset($flotta)){
			foreach($flotta as $busNew){
				$totale = false;
				if(isset($this->flotta)) {
					foreach($this->flotta as $k => $bus){
						$tratta = null;
						$merge = false;
						if( $busNew->trattaId == $bus->trattaId) {
							//bus della stessta tratta
							$merge = true;
							$tratta = $bus->trattaId;
						} else {
							$sql = "Select TrattaPrincipaleId from RT_Tratta WHERE TrattaId = ".$busNew->trattaId;
							$row = $this->conn->query_first($sql);
							if(isset($row['TrattaPrincipaleId']) && $row['TrattaPrincipaleId'] == $bus->trattaId){
								$tratta = $bus->trattaId;
								$merge = true;
							} else {
								$sql = "Select TrattaPrincipaleId from RT_Tratta WHERE TrattaId = ".$bus->trattaId;
								$row = $this->conn->query_first($sql);
								if(isset($row['TrattaPrincipaleId']) && $row['TrattaPrincipaleId'] == $busNew->trattaId){
									$tratta = $busNew->trattaId;
									$merge = true;
								} else {
									$merge = false;
								}
							} 	
						}
						
						if($merge){
							$totale = true;
							$this->flotta[$k]->trattaId = $tratta;
							foreach($this->flotta[$k]->comuni as $keyComnune => $comune) {
								if(count($comune['passeggeri']) == 0){
									$this->flotta[$k]->comuni[$keyComnune]['passeggeri'] = $busNew->comuni[$keyComnune]['passeggeri'];
									$this->graph->nodes[$comune['comune']] = $gruppo->graph->nodes[$comune['comune']];
								} else {
									if(count($busNew->comuni[$keyComnune]['passeggeri']) > 0){
										foreach($busNew->comuni[$keyComnune]['passeggeri'] as $comuneId => $passegeri){
											if(array_key_exists($comuneId,$comune['passeggeri'])){
												$temp = array();
												foreach ($this->flotta[$k]->comuni[$keyComnune]['passeggeri'][$comuneId] as $p) {
													if(!in_array($p, $temp)){
														$temp[] = $p;
													}
												}
												foreach ($passegeri as $p) {
													if(!in_array($p, $temp)){
														$temp[] = $p;
													}
												}
												$this->flotta[$k]->comuni[$keyComnune]['passeggeri'][$comuneId] = $temp;
											} else {
												$this->flotta[$k]->comuni[$keyComnune]['passeggeri'][$comuneId] = $passegeri;
											}
										}
									}
		
								}
							}
						}
					}
				}
				if(!$totale){
					$this->flotta[] = $busNew;
				}
			}
		}
		/*salvataggio su db*/
		if(sizeof($this->flotta)>0 && $salva){
			$count = $this->salvaDB($this->idLinea, $this->idCorsa, $this->corsaDataPartenza);
			
			
			//controllo posti bus
			foreach($this->flotta as $id => $bus){
// 				var_dump($bus);
				$nome = $id+1;
				$sql = "SELECT count(*) as tot, Comune FROM RT_GestioneOttimizzataPasseggeri
						where Bus = $nome AND LineaId = $this->idLinea AND CorsaId = $this->idCorsa
						AND CorsaDataPartenza = '".$this->corsaDataPartenza."'
						GROUP BY Comune";
				$row = $this->conn->fetch_array($sql);
				$max = false;
				foreach($row as $temp){
					if(isset($temp['tot']) && $temp['tot'] > $this->postiBus){
						$max = true;
					}
				}
				
				if($max){
					$sql = "select * from RT_GestioneOttimizzataFlotta WHERE
							LineaId = $this->idLinea AND CorsaId = $this->idCorsa AND
							CorsaDataPartenza = '".$this->corsaDataPartenza."' AND Nome = $nome";
					$rowFlotta = $this->conn->query_first($sql);
					unset($rowFlotta['GestioneOttimizzataFlottaId']);
					$rowFlotta['Nome'] = $count;
					$this->conn->insert('RT_GestioneOttimizzataFlotta', $rowFlotta);
					
					$sql = "select * from RT_GestioneOttimizzataNodo WHERE
							LineaId = $this->idLinea AND CorsaId = $this->idCorsa AND
							CorsaDataPartenza = '".$this->corsaDataPartenza."' AND 
					(BusArrivo = $nome OR BusPartenza = $nome)";
					$rowNodo = $this->conn->fetch_array($sql);
					foreach($rowNodo as $tempNodo){
						unset($tempNodo['GestioneOttimizzataNodoId']);
						if(isset($tempNodo['BusArrivo'])){
							$tempNodo['BusArrivo'] = $count;
						} else {
							unset($tempNodo['BusArrivo']);
						}
						if(isset($tempNodo['BusPartenza'])){
							$tempNodo['BusPartenza'] = $count;
						} else {
							unset($tempNodo['BusPartenza']);
						}
						$this->conn->insert('RT_GestioneOttimizzataNodo', $tempNodo);
					}
					
					foreach($row as $temp){
						if($temp['tot'] > $this->postiBus){
							$sql = "select * from RT_GestioneOttimizzataPasseggeri WHERE
									LineaId = $this->idLinea AND CorsaId = $this->idCorsa AND
									CorsaDataPartenza = '".$this->corsaDataPartenza."' AND Bus = $nome
							AND Comune = ".$temp['Comune'];
							$rowPasseggeri = $this->conn->fetch_array($sql);
							$countPasseggeri = 0;
							foreach($rowPasseggeri as $p){
								$countPasseggeri++;
								if($countPasseggeri > $this->postiBus){
									$dup['Bus'] = $count;
									$this->conn->update("RT_GestioneOttimizzataPasseggeri", $dup,"GestioneOttimizzataPasseggeriId=".$p['GestioneOttimizzataPasseggeriId']);
								}
							}
						}
					}
					
					$count++;
				}
				
			}
		}
		
	}
	
}
?>