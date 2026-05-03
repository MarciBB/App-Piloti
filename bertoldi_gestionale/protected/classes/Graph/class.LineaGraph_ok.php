<?php
include_once($classespath_."Graph/Graph.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."class.Flotta.php");
include_once($classespath_."class.GestioneOttimizzataFlotta.php");
include_once($classespath_."class.Fermata.php");


class NodoFermata extends Node{
// 	public $salite;
// 	public $discese;
	public $destinazioni;
	public $gruppo;
	public $biglietti;
	public $bigliettiSalite;
	public $busPartenza;
	public $busArrivo;
	public $importanza;
// 	public $operazioni;
	
	function __construct($idNode){
		parent::__construct($idNode);
		$salite = 0;
		$discese = 0;
		$destinazioni = array();
		$gruppo = new Gruppo();
		$biglietti = array();
		$this->busPartenza = array();
		$this->busArrivo = array();
		$this->importanza = array();
	}
}

class Gruppo{
	public $passeggeri; //array <destinazione, num. passeggeri>
	public $totalePasseggeri;
	public $posizione;
	
	function __construct($idPosizione=null){
		$this->passeggeri = array();
		$this->totalePasseggeri = 0;
		$this->posizione = $idPosizione;
		
	}
	
	public function isNew(){
		if(isset($this->posizione)){
			return true;
		}else{
			return false;
		}
	}
}

class Bus{
	public $id;
	public $comuni;
	public $percorso;
	public $nome;
	
	function __construct($id){
		$this->id = $id;
		$this->percorso = array();
		$this->comuni = array();
	}
}

class LineaGraph{
	
	public $idLinea;
	public $conn;
	public $graph;
	public $flotta;
	public $postiBus;
	
	function __construct($idLinea, $idCorsa, $corsaDataPartenza, $conn, $caricaDB){
		$sql = "SELECT TotalePosti
				FROM RT_TipologiaBus
				WHERE IsDefault=1";
		$row = $conn->query_first($sql);
		
		$this->postiBus = $row['TotalePosti'];
		unset($row);
		$this->flotta = array();
		
		/*creazione grafo*/
		$this->graph = new Graph();
		$this->idLinea = $idLinea;
		$this->conn = $conn;
		$db=$this->conn;
		$sql = "SELECT TrattaId FROM RT_Tratta WHERE LineaId=$this->idLinea";
		$rowsTratte = $db->fetch_array($sql);
	
		foreach ($rowsTratte as $key => $tratta){
			$sql = "SELECT ComuneId, KmInizioTratta FROM RT_Fermata WHERE TrattaId=".$tratta['TrattaId']." ORDER BY FermataPeso";
			$rowsFermate = $db->fetch_array($sql);
			
			foreach ($rowsFermate as $key2 => $fermata){
				$nodoFermata = new NodoFermata($fermata['ComuneId']);
				$this->graph->addNode($nodoFermata);
			}
			$i = 0;
  			while($i<(sizeof($rowsFermate)-1)){
  				$temp1 = $rowsFermate[$i];
  				$temp2 = $rowsFermate[$i+1];
  				$this->graph->connectNodes($temp1['ComuneId'], $temp2['ComuneId'],0, $temp2['KmInizioTratta']-$temp1['KmInizioTratta']);
  				$this->graph->edges[$temp1['ComuneId'].'-'.$temp2['ComuneId']]->info3[$tratta['TrattaId']]=$tratta['TrattaId'];
  				$i++;
 			}
 		}
		unset($rowsTratte);
		
		if(isset($idCorsa)){
			/*inizializzazione salite e fermate*/
			foreach ($this->graph->nodes as $node){			
				$sql = "SELECT
						RT_PrenotazioneDettaglio.PrenotazioneNumero, RT_PrenotazionePercorso.ComuneDiscesaId
						FROM
						RT_PrenotazionePercorso
						LEFT JOIN RT_Prenotazione ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						LEFT JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazioneDettaglio.ComunePartenza=RT_PrenotazionePercorso.ComuneSalita AND RT_PrenotazioneDettaglio.PrenotazioneId=RT_Prenotazione.PrenotazioneId)
						WHERE
						RT_PrenotazionePercorso.CorsaId=$idCorsa
						AND RT_PrenotazionePercorso.CorsaDataPartenza='$corsaDataPartenza'
						AND RT_PrenotazionePercorso.LineaId=$idLinea
						AND RT_PrenotazionePercorso.ComuneSalitaId=$node->id
						AND ((RT_Prenotazione.PrenotazioneStato=3 AND RT_PrenotazioneDettaglio.Escludi=0) OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=1) OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=0 AND RT_Prenotazione.ScadenzaPrenotazione>$corsaDataPartenza) OR (RT_Prenotazione.ABordo=1))";
				
				$rows = $db->fetch_array($sql);
				
				$node->salite = 0;
				foreach ($rows as $row){
					$node->salite++;
					if(array_key_exists($row['ComuneDiscesaId'], $node->destinazioni)){
						$node->destinazioni[$row['ComuneDiscesaId']] = $node->destinazioni[$row['ComuneDiscesaId']] + 1;
					}else{
						$node->destinazioni[$row['ComuneDiscesaId']] = 1;
					}		
				}
							
				$sql = "SELECT
						RT_PrenotazioneDettaglio.PrenotazioneNumero
						FROM
						RT_PrenotazionePercorso
						LEFT JOIN RT_Prenotazione ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						LEFT JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazioneDettaglio.ComunePartenza=RT_PrenotazionePercorso.ComuneSalita AND RT_PrenotazioneDettaglio.PrenotazioneId=RT_Prenotazione.PrenotazioneId)
						WHERE
						RT_PrenotazionePercorso.CorsaId=$idCorsa 
						AND RT_PrenotazionePercorso.CorsaDataPartenza='$corsaDataPartenza'
						AND RT_PrenotazionePercorso.LineaId=$idLinea
						AND RT_PrenotazionePercorso.ComuneDiscesaId=$node->id
						AND ((RT_Prenotazione.PrenotazioneStato=3 AND RT_PrenotazioneDettaglio.Escludi=0) OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=1) OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=0 AND RT_Prenotazione.ScadenzaPrenotazione>$corsaDataPartenza) OR (RT_Prenotazione.ABordo=1))";
				$rowsDiscese = $db->fetch_array($sql);
				$node->discese = sizeof($rowsDiscese);
				
				foreach ($rows as $row){
					//se carico dal DB gestisco le nuovo prenotazioni
					if($caricaDB){
						$this->gestioneCaricamentoPrenotazione($row['PrenotazioneNumero'],$idLinea, $idCorsa, $corsaDataPartenza);
					}
					if (!isset($node->biglietti[$row[ComuneDiscesaId]])){
						$node->biglietti[$row['ComuneDiscesaId']] = array();
						$node->bigliettiSalite[$row['ComuneDiscesaId']] = array();
					}
					$node->biglietti[$row['ComuneDiscesaId']][]=$row['PrenotazioneNumero'];
					$node->bigliettiSalite[$row['ComuneDiscesaId']][]=$row['PrenotazioneNumero'];
				}
			}
			unset($rows);
		}
		
		/*calcolo discendenti dei nodi*/
		$this->graph->calculateDescent2();
		
		
		

		if(isset($idCorsa)){
			
			/*calcolo importanza discendenti*/
			$this->calcoloImportanza($db);
			
			if($caricaDB==true){
				//caricamento bus da DB
				$this->caricaDB($idLinea, $idCorsa, $corsaDataPartenza);		
			}else{
				
				/*calcolo gruppi*/
				$this->calcolaGruppi();
				
				/*salvataggio su db*/
				if(sizeof($this->flotta)>0){
					$this->salvaDB($idLinea, $idCorsa, $corsaDataPartenza);
				}
			}
		}

	}
	
	
	private function calcolaGruppi(){
		/*inizializzazione gruppi*/
		$listaGruppi = array();

		foreach ($this->graph->nodes as $node){
			if($node->salite>0){
				$this->graph->nodes[$node->id]->gruppo = new Gruppo($node->id);
				$gruppo = $this->graph->nodes[$node->id]->gruppo;
				$listaGruppi[] = $this->graph->nodes[$node->id]->gruppo;
			}
		}
		
		/*calcolo iterativo*/
		$ii=0;
  		while(sizeof($listaGruppi)>0){
//   			$ii++;
//   			if($ii>100000){
//   				break;
//   			}

 			$gruppoAvanza = $this->getProssimoGruppo($listaGruppi);
 						
			//discesa
			if(array_key_exists($gruppoAvanza->posizione, $gruppoAvanza->passeggeri)){
				$gruppoAvanza->passeggeri[$gruppoAvanza->posizione] = 0;
				unset($gruppoAvanza->passeggeri[$gruppoAvanza->posizione]);
				$gruppoAvanza->totalePasseggeri = $gruppoAvanza->totalePasseggeri - $this->graph->nodes[$gruppoAvanza->posizione]->discese; 
			}
			//salita
			foreach ($this->graph->nodes[$gruppoAvanza->posizione]->destinazioni as $destinazione => $num){
				if(array_key_exists($destinazione, $gruppoAvanza->passeggeri)){
					$gruppoAvanza->passeggeri[$destinazione] = $gruppoAvanza->passeggeri[$destinazione] + $num;
				}else{
					$gruppoAvanza->passeggeri[$destinazione] = $num;
				}
				$gruppoAvanza->totalePasseggeri = $gruppoAvanza->totalePasseggeri + $num;
			}
			
			$nuoviGruppi = array();
			//divisione per destinazione
			foreach ($gruppoAvanza->passeggeri as $destinazione => $num){
					
				$privata = 0;
				$prossimaFermata = array();
				foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child){
					
 					if(strcmp($destinazione, $child)==0){
 						$prossimaFermata[$child] = $child;
 						
 					}else{
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
 				
 				if(count($prossimaFermata)>1){

 					$maxImportanza = 100;
 					$tempRemove = array();
 					foreach ($prossimaFermata as $key => $prossima){
 						if($this->graph->nodes[$prossima]->importanza[$destinazione]<$maxImportanza){
 							$maxImportanza = $this->graph->nodes[$prossima]->importanza[$destinazione];
 						}
 					}
 					
 					foreach ($prossimaFermata as $key => $prossima){
 						if($this->graph->nodes[$prossima]->importanza[$destinazione]>$maxImportanza){
 							$tempRemove[] = $key;
 						}
 					}
 					foreach ($tempRemove as $trem){
 						unset($prossimaFermata[$trem]);
 					}
 				}
 				
 				
 				if(sizeof($prossimaFermata)>0){
 					if(sizeof($prossimaFermata)==1){
 						//estrai il primo
 						$nextNode = array_shift($prossimaFermata);
 					}else{
 						//percorso piu breve
 						$nextNode = $this->graph->percorsoBreve($gruppoAvanza->posizione, $destinazione);
//  					$nextNode = array_pop($prossimaFermata);
 						
 					}
 				}
 				unset($prossimaFermata);
 				unset($privata);
 				unset($min);
				
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

				$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info2=$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info2 + $num;
				$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info = array_merge($this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info, $this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione]);
				if(!isset($this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info4[$destinazione])){
					$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info4[$destinazione] = array();
				}	
				
				$this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info4[$destinazione] = array_merge($this->graph->edges[$gruppoAvanza->posizione."-".$nextNode]->info4[$destinazione], $this->graph->nodes[$gruppoAvanza->posizione]->biglietti[$destinazione]);
				$nuoviGruppi[$nextNode] = $nextNode;
				unset($nextNode);
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
 			
 			//bus
 			$countEdge=0;
 			foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child){
 				$edgeId = $gruppoAvanza->posizione."-".$child;
 				if($this->graph->edges[$edgeId]->info2 >0){
 					$countEdge++;
 				}
 			}
 			
 			foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child){
 				$edgeId = $gruppoAvanza->posizione."-".$child;	
 				if($this->graph->edges[$edgeId]->info2 >0){
 					$fermataObj = new Fermata();
 					$fermataObj->conn = $this->conn;

 					if($fermataObj->isInterscambioLinea($this->idLinea, $gruppoAvanza->posizione)){
 						 
 							if(sizeof($this->graph->nodes[$gruppoAvanza->posizione]->busArrivo)==0){
 								//nuovo bus
 								$this->nuovoBus($edgeId);
 							}else{
 								//avanza bus
 								
 								$this->busAvanza2($edgeId, $this->conn);
 								
//  							$this->nuovoBus($edgeId);
// 								$this->taggingBus($edgeId);
 							}
	 						
	 					
 					}else{
 						if(sizeof($this->graph->nodes[$gruppoAvanza->posizione]->busArrivo)==0){
 							//nuovo bus
 							$this->nuovoBus($edgeId);
 						}else{
 							//bus avanza
 							$this->busAvanza($edgeId);
 						}
 					}
 				}
 			}
//  			unset($this->edges[$edgeId]->info4);
//  			unset($this->edges[$edgeId]->info3);
//  			unset($this->edges[$edgeId]->info2);
//  			unset($this->edges[$edgeId]->info);
 			
 			unset($this->graph->nodes[$gruppoAvanza->posizione]->gruppo);
 			unset($this->graph->nodes[$gruppoAvanza->posizione]->biglietti);
  		}
  		
  		foreach ($this->flotta as $bus){
  			$ultimaFermata = sizeof($bus->comuni);
  			$deposito = 0;
  			foreach ($bus->comuni[$ultimaFermata-1]['passeggeri'] as $ultimoComune => $passeggeri){
  				$deposito =  $ultimoComune;
  			}
  			if(array_key_exists($deposito, $this->graph->nodes[$bus->comuni[$ultimaFermata - 1]['comune']]->children)){
	  			$bus->comuni[$ultimaFermata]['comune'] = $deposito;
	  			$bus->comuni[$ultimaFermata]['passeggeri'] = array();
	  			if(!in_array($bus->comuni[$ultimaFermata-1]['comune']."-".$deposito, $bus->percorso))
	  				$bus->percorso[$ultimaFermata] = $bus->comuni[$ultimaFermata-1]['comune']."-".$deposito;
  			}else{
  				$ultimaTratta = sizeof($bus->percorso) - 1;
  				$deposito = $this->graph->edges[$bus->percorso[$ultimaTratta]]->nodeB;
  				$bus->comuni[$ultimaFermata-1]['comune'] = $deposito;
  				$bus->comuni[$ultimaFermata-1]['passeggeri'] = array();
  			}
  		}
 	}

 	private function busAvanza($edgeId){
 		$busConsiderati = array();
 		//i pulman in arrivo avanzano mantenedo gli stessi passeggeri a bordo
 		foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo as $nomeBus => $bus){
 			
  			if($this->graph->edges[$edgeId]->info2>0){
	 			$lastComune = sizeof($this->flotta[$bus]->comuni) - 1;
	 			if($this->flotta[$bus]->comuni[$lastComune]['comune'] != $this->graph->edges[$edgeId]->nodeA){
	 				
	 				//se il pulman non è stato gia' usato per questo comune
		 			$temp['comune'] = $this->graph->edges[$edgeId]->nodeA;
		 			$temp['passeggeri'] = array();
		 			$count = 0;	
		 			foreach ($this->flotta[$bus]->comuni[$lastComune]['passeggeri'] as $dest => $passeggeri){
		 				if($this->graph->edges[$edgeId]->info2>0){
			 				$tempPasseggeri = array(); 
			 				foreach ($passeggeri as $key => $passeggero){
			 					if(in_array($passeggero, $this->graph->edges[$edgeId]->info4[$dest])){
			 						if($dest!=$this->graph->edges[$edgeId]->nodeA)
			 							$tempPasseggeri[] = $passeggero;
			 						
			 						$keyInfo4 = array_search($passeggero, $this->graph->edges[$edgeId]->info4[$dest]);
			 						unset($this->graph->edges[$edgeId]->info4[$dest][$keyInfo4]);
			 						if(count($this->graph->edges[$edgeId]->info4[$dest]) <= 0){
			 							unset($this->graph->edges[$edgeId]->info4[$dest]);
			 						}					
			 						$this->graph->edges[$edgeId]->info2--;
			 						$count++;
			 					}
			 				}
			 				if(count($tempPasseggeri)>0)
			 					$temp['passeggeri'][$dest] = $tempPasseggeri;
		 				}
		 			}
		 			if(count($temp['passeggeri'])>0){
			 			$this->flotta[$bus]->comuni[$lastComune+1] = $temp;
			 			$this->flotta[$bus]->percorso[$lastComune+1] = $edgeId;
			 			$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$bus] = $bus;
			 			$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$bus] = $bus;
		 			}
		 			$busConsiderati[$bus] = $count;
	 			}
  			}
 		}
 		
 		//carico i nuovi passeggeri non presenti sugli autobus precedentemente
 		if($this->graph->edges[$edgeId]->info2 > 0){
 			foreach ($busConsiderati as $bus => $postiOccupati){
 				if($postiOccupati < $this->postiBus){
 					$lastComune = sizeof($this->flotta[$bus]->comuni) - 1;
 					$tempDest = array();
 					foreach ($this->graph->edges[$edgeId]->info4 as $dest => $passeggeri){
 						$tempRemove = array();
 						foreach ($passeggeri as $key => $passeggero){
 							if($postiOccupati < $this->postiBus){
 								$this->flotta[$bus]->comuni[$lastComune]['passeggeri'][$dest][] = $passeggero;
 								$postiOccupati++;
 								$tempRemove[] = $passeggero;
 							}
 						}
 						foreach ($tempRemove as $removePasseggero){
 							$key = array_search($removePasseggero, $passeggeri);
 							unset($this->graph->edges[$edgeId]->info4[$dest][$key]);
 							$this->graph->edges[$edgeId]->info2--;
 						}
 						if(count($passeggeri) <= 0){
 							$tempDest[] = $dest;
 						}
 					}
 					foreach ($tempDest as $removeDest){
 						unset($this->graph->edges[$edgeId]->info4[$removeDest]);
 					}
 				}
 			}
 		}
	 				
 		if($this->graph->edges[$edgeId]->info2 > 0){
 			$this->nuovoBus($edgeId);
 		}
 		
 	}
 	
 	
 	private function busAvanza2($edgeId, $db){
 		$busConsiderati = array();
 		//i pulman in arrivo avanzano mantenedo gli stessi passeggeri a bordo
 		foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo as $nomeBus => $bus){
 			if($this->graph->edges[$edgeId]->info2>0){
 				$lastComune = sizeof($this->flotta[$bus]->comuni) - 1;
 				if($this->flotta[$bus]->comuni[$lastComune]['comune'] != $this->graph->edges[$edgeId]->nodeA){
 	
 					//se il pulman non è stato gia' usato per questo comune
 					$temp['comune'] = $this->graph->edges[$edgeId]->nodeA;
 					$temp['passeggeri'] = array();
 					$count = 0;
 					foreach ($this->flotta[$bus]->comuni[$lastComune]['passeggeri'] as $dest => $passeggeri){
 						if($this->graph->edges[$edgeId]->info2>0){
 							$tempPasseggeri = array();
 							foreach ($passeggeri as $key => $passeggero){
 								if(in_array($passeggero, $this->graph->edges[$edgeId]->info4[$dest])){
 									if($dest!=$this->graph->edges[$edgeId]->nodeA)
 										$tempPasseggeri[] = $passeggero;
 	
 									$keyInfo4 = array_search($passeggero, $this->graph->edges[$edgeId]->info4[$dest]);
 									unset($this->graph->edges[$edgeId]->info4[$dest][$keyInfo4]);
 									if(count($this->graph->edges[$edgeId]->info4[$dest]) <= 0){
 										unset($this->graph->edges[$edgeId]->info4[$dest]);
 									}
 									$this->graph->edges[$edgeId]->info2--;
 									$count++;
 								}
 							}
 							if(count($tempPasseggeri)>0)
 								$temp['passeggeri'][$dest] = $tempPasseggeri;
 						}
 					}
 					if(count($temp['passeggeri'])>0){
 						$this->flotta[$bus]->comuni[$lastComune+1] = $temp;
 						$this->flotta[$bus]->percorso[$lastComune+1] = $edgeId;
 						$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$bus] = $bus;
 						$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$bus] = $bus;
 					}
 					$busConsiderati[$bus] = $count;

 				}
 			}
 		}
 		
 		//carico i nuovi passeggeri non presenti sugli autobus precedentemente
 		if($this->graph->edges[$edgeId]->info2 > 0){
 			foreach ($busConsiderati as $bus => $postiOccupati){
 				if($postiOccupati < $this->postiBus){
 					$lastComune = sizeof($this->flotta[$bus]->comuni) - 1;
 					$tempDest = array();
 					foreach ($this->graph->edges[$edgeId]->info4 as $dest => $passeggeri){
 						$tempRemove = array();
 						foreach ($passeggeri as $key => $passeggero){
 							if($postiOccupati < $this->postiBus){
 								$this->flotta[$bus]->comuni[$lastComune]['passeggeri'][$dest][] = $passeggero;
 								$postiOccupati++;
 								$tempRemove[] = $passeggero;
 							}
 						}
 						foreach ($tempRemove as $removePasseggero){
 							$key = array_search($removePasseggero, $passeggeri);
 							unset($this->graph->edges[$edgeId]->info4[$dest][$key]);
 							$this->graph->edges[$edgeId]->info2--;
 						}
 						if(count($passeggeri) <= 0){
 							$tempDest[] = $dest;
 						}
 					}
 					foreach ($tempDest as $removeDest){
 						unset($this->graph->edges[$edgeId]->info4[$removeDest]);
 					}
 				}
 			}
 		}
 		
 		if($this->graph->edges[$edgeId]->info2 > 0){
 			$this->nuovoBus($edgeId);
 		}
 		
 		if(sizeof($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza)>1){
 			$this->travasoBus($edgeId, $db);
 			$this->travasoDivisioneBus($edgeId, $db);
 		}			
 	}
 	
 	
 	private function travasoBus($edgeId, $db){
 		$fermataObj = new Fermata();
 		$fermataObj->conn = $db;
 		
 		foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo as $nomeBus1 => $bus1){
 			foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo as $nomeBus2 => $bus2){
 				if($nomeBus1!=$nomeBus2){
 					foreach ($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'] as $dest => $passeggeri){

 						if(array_key_exists($dest, $this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'])){
 							$temp = $passeggeri;
 							foreach ($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest] as $keyPass=>$passeggero2){
 								$temp[] = $passeggero2;
 							}
 							
 							if(count($passeggeri)>count($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest])){
 								$this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest] = $temp;
 								unset($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest]);
 							}else{
 								$this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest] = $temp;
 								unset($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest]);
 							}
 						}
 					}
 				}
 			}
 		}
 	}
 	
 	
 	private function travasoDivisioneBus($edgeId, $db){
 		$fermataObj = new Fermata();
 		$fermataObj->conn = $db;
 			
 		foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo as $nomeBus1 => $bus1){
 			foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo as $nomeBus2 => $bus2){
 				if($nomeBus1!=$nomeBus2){
 					$add = array();
 					foreach ($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'] as $dest => $passeggeri){
 						$count1=0;
 						$count2=0;
 						foreach ($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'] as $dest2 => $passeggeri2){
 							$res = $fermataObj->getTratta($dest, $dest2);
 							if($res != false){
 								$count1+=count($passeggeri2);
 							}
 						}
 							
 						foreach ($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'] as $dest2 => $passeggeri2){
 							$res = $fermataObj->getTratta($dest, $dest2);
 							if($res != false){
 								$count2+=count($passeggeri2);
 							}
 						}
 						
 						
 						if($count1<=$count2){
 							$this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest] = $this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest];
 							unset($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest]);
 						}				
 						
 					}
 					
 					if(count($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'])<=0){
 						unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$bus1]);
 						unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$bus1]);
 						unset($this->flotta[$bus1]->percorso[count($this->flotta[$bus1]->percorso)-1]);
 					}
 					
 					
 				}
 			}
 		}
 	}
 			
 	
 	private function taggingBus($edgeId){
 		if(sizeof($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza) == 1 &&
			sizeof($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo) == 1){
 			//caso in cui un bus arriva ed un bus parte
 			$busA = array_pop($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo);
 			$busB = array_pop($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza);
 			
 			$this->mergeBus($busA, $busB);
 			
 			$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$busA] = $busA;
 			$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo[$busA] = $busA;
 			
 			unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$busB]);
 			$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$busA] = $busA;

 		}else{
 			//caso in cui 1 o piu' bus arrivano e 1 o piu' bus partono
 			$tempCount = array();
 			foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza as $busParte){
 				foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo as $busArriva){
 					$lastIndexBusArriva = sizeof($this->flotta[$busArriva]->comuni)-1;
 					
 					if($this->flotta[$busArriva]->comuni[$lastIndexBusArriva]['comune']!=$this->graph->edges[$edgeId]->nodeA){
	 					$count=0;
	 					if(count($this->flotta[$busParte]->comuni)<=1){
		 					foreach ($this->flotta[$busParte]->comuni[0] as $fermata=>$passeggeri){
		 						if(isset($passeggeri) && sizeof($passeggeri)>0){
			 						foreach ($passeggeri as $d => $gruppo){
			 							if(isset($gruppo)){
				 							foreach ($gruppo as $persona){
				 								if(isset($persona) && isset($this->flotta[$busArriva]->comuni[$lastIndexBusArriva]['passeggeri'][$d])){
						 							if(in_array($persona, $this->flotta[$busArriva]->comuni[$lastIndexBusArriva]['passeggeri'][$d])){
						 								$count++;
						 							}
				 								}
				 							}
			 							}
			 						}
		 						}
		 					}
		 					$tempCount[$busArriva][$busParte] = $count;
	 					}
 					}
 				}
 			}
 			
 			$copyCount = $tempCount;
 			$servito = array();
 			foreach ($tempCount as $busArriva => $temp){
 				$max = 0;
 				$idBus = -1;
 				
	 			foreach ($temp as $busParte => &$tot) {
	 				if($max<=$tot && !in_array($busParte, $servito)){
	 					$max = $tot;
	 					$idBus = $busParte;
	 					$tot = -1;
	 				}
	 			}
 				
	 			if($idBus != -1){
	 				$servito[$idBus] = $idBus;
	 			}
	 			
	 			if($idBus > -1 && $idBus != $busArriva){
	 				$this->mergeBus($busArriva, $idBus);

	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$busArriva] = $busArriva;
	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo[$busArriva] = $busArriva;
	 				
	 				unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$idBus]);
	 				unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$idBus]);
	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$busArriva] = $busArriva;
	 			}
 			} 			
 		}
 	}
 	
 	private function mergeBus($busA, $busB){
 		$index = sizeof($this->flotta[$busA]->comuni);
 		$this->flotta[$busA]->comuni[$index] = $this->flotta[$busB]->comuni[0];
 		$this->flotta[$busA]->percorso[$index] = $this->flotta[$busB]->percorso[0];
 		unset($this->flotta[$busB]);
 	}
 	
 	private function nuovoBus($edgeId){
 		$postiDisponibili = 0;
 		$selectedBus = 0;
 		$selectedBusIndex = 0;
		$this->graph->edges[$edgeId]->info4 = array_reverse($this->graph->edges[$edgeId]->info4,true);
 		foreach ($this->graph->edges[$edgeId]->info4 as $key=>$value){
 			$value = array_reverse($value,true);
 			if($postiDisponibili == 0){
 				//posti esauriti, si crea un nuovo bus
 				$busId = sizeof($this->flotta);
 				while(array_key_exists($busId, $this->flotta)){
 					$busId++;
 				}
 				$bus = new Bus($busId);
 				$postiDisponibili = $this->postiBus;
 				$this->flotta[$busId] = $bus;
 				$selectedBus = $busId;
 			}
 			if($postiDisponibili >= sizeof($value)){
  				//posti disponibili
				$temp=array();
 				$temp['comune'] = $this->graph->edges[$edgeId]->nodeA;
 				$temp['passeggeri'][$key] = $value;
 				if(!isset($this->flotta[$selectedBus]->comuni[$selectedBusIndex])){
 					$this->flotta[$selectedBus]->comuni[$selectedBusIndex] = array();
 				}
 				if(!isset($this->flotta[$selectedBus]->percorso[$selectedBusIndex])){
 					$this->flotta[$selectedBus]->percorso[$selectedBusIndex] = array();
 				}
 				if((isset($this->flotta[$selectedBus]->comuni[$selectedBusIndex]['comune'])) && ($temp['comune'] == $this->flotta[$selectedBus]->comuni[$selectedBusIndex]['comune'])){
 					$this->flotta[$selectedBus]->comuni[$selectedBusIndex]['passeggeri'][$key] = $temp['passeggeri'][$key];
 				}else{
 					$this->flotta[$selectedBus]->comuni[$selectedBusIndex] = $temp;
 					$this->flotta[$selectedBus]->percorso[$selectedBusIndex] = $edgeId;
 				}
 					 				
 				if(!in_array($selectedBus, $this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza)){
 					$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$selectedBus]=$selectedBus;
 				}
 				if(!in_array($selectedBus, $this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo)){
 						$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$selectedBus]=$selectedBus;
 				}	
 				$postiDisponibili -= sizeof($value);
 			}else{
 				//posti non disponibili
 				$tempPasseggeri = $value;
 				while(sizeof($tempPasseggeri)>0){
 					$tempPasseggeri = $this->caricoPasseggeriEntrano($edgeId, $selectedBus, $tempPasseggeri, $postiDisponibili, $selectedBusIndex, $key);
 					if(sizeof($tempPasseggeri)>0 && sizeof($tempPasseggeri)>$postiDisponibili){
 						//nuovo bus
	 					$busId = sizeof($this->flotta);
		 				while(array_key_exists($busId, $this->flotta)){
		 					$busId++;
		 				}
 						$bus = new Bus($busId);
 						$postiDisponibili = $this->postiBus;
 						$this->flotta[$busId] = $bus;
 						$selectedBus = $busId;
 					}
 				}
 			}
 		}
 	}
 	
 	private function caricoPasseggeriEntrano($edgeId, $selectedBus, $tempPasseggeri, &$postiDisponibili, $selectedBusIndex, $key){
 		$tempPasseggeri1 = array();
 		$temp['comune'] = $this->graph->edges[$edgeId]->nodeA;
 		$temp['passeggeri'][$key] = array();
 		//carico solo i passeggeri con idPrenotazione unico che ci entrano
 		$tempPasseggeri = array_reverse($tempPasseggeri,true);
 		
 		while(sizeof($tempPasseggeri)>0){
 			$p=array_pop($tempPasseggeri);
 			$sql = "SELECT b.PrenotazioneNumeroId FROM RT_PrenotazioneNumero as a
		 			LEFT JOIN RT_PrenotazioneNumero as b ON (a.PrenotazioneId=b.PrenotazioneId)
		 			WHERE
		 			a.PrenotazioneNumeroId = $p;";
 			$rows = $this->conn->fetch_array($sql);
 			$rows = array_reverse($rows,true);
 			if(sizeof($rows)<$this->postiBus){
	 			if(sizeof($rows)<$postiDisponibili){
	 				foreach ($rows as $row){
	 					array_push($temp['passeggeri'][$key], $row['PrenotazioneNumeroId']);
	 					unset($tempPasseggeri[array_search($row['PrenotazioneNumeroId'],$tempPasseggeri)]);
	 				}
	 				$postiDisponibili -= sizeof($rows);
	 			}else{
	 				array_push($tempPasseggeri1, $p);
	 			}
 			}else{
 				foreach ($rows as $row){ 					
 					if($postiDisponibili>0){
 						if(in_array($row['PrenotazioneNumeroId'], $tempPasseggeri) || $row['PrenotazioneNumeroId'] == $p){
	 						array_push($temp['passeggeri'][$key], $row['PrenotazioneNumeroId']);
	 						$postiDisponibili--;
 						}
 					}else{
 						array_push($tempPasseggeri1, $row['PrenotazioneNumeroId']);
 					}
 					if(in_array($row['PrenotazioneNumeroId'], $tempPasseggeri)){
 						unset($tempPasseggeri[array_search($row['PrenotazioneNumeroId'],$tempPasseggeri)]);
 					}
 				}
 			}
 			unset($rows);
 		}
 		if(sizeof($temp['passeggeri'][$key])>0){
 			if(!isset($this->flotta[$selectedBus]->comuni[$selectedBusIndex])){
 				$this->flotta[$selectedBus]->comuni[$selectedBusIndex] = array();
 			}
 			if(!isset($this->flotta[$selectedBus]->percorso[$selectedBusIndex])){
 				$this->flotta[$selectedBus]->percorso[$selectedBusIndex] = array();
 			}
 			
 			if(isset($this->flotta[$selectedBus]->comuni[$selectedBusIndex]['comune']) && ($temp['comune'] == $this->flotta[$selectedBus]->comuni[$selectedBusIndex]['comune'])){
 				$this->flotta[$selectedBus]->comuni[$selectedBusIndex]['passeggeri'][$key] = $temp['passeggeri'][$key];
 			}else{
 				$this->flotta[$selectedBus]->comuni[$selectedBusIndex] = $temp;
 				$this->flotta[$selectedBus]->percorso[$selectedBusIndex] = $edgeId;
 			}
 			
 			if(!in_array($selectedBus,$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza)){
 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$selectedBus]=$selectedBus;
 			}
 			if(!in_array($selectedBus,$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo)){
 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$selectedBus]=$selectedBus;
 			}	
 		}
 		unset($tempPasseggeri);
 		return $tempPasseggeri1;
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

	function deleteAll($lineaId, $corsaId, $corsaDataPartenza){
		//caricamento bus
		$sql = "SELECT BusId FROM RT_GestioneOttimizzataFlotta
			WHERE
			LineaId=$lineaId
			AND CorsaId=$corsaId
			AND CorsaDataPartenza='$corsaDataPartenza'";
		$rows = $this->conn->fetch_array($sql);
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
		//conto il numero di volte in cui ï¿½ presente numero Prenotazione
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
		global $user;
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
			foreach ($bus->percorso as $edge){
				$km += $this->graph->edges[$edge]->peso;
			}
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
				if(isset($comunePre)){
					$data = array();
					$data['LineaId'] = $lineaId;
					$data['CorsaId'] = $corsaId;
					$data['CorsaDataPartenza'] = $corsaDataPartenza;
					$data['Comune'] = $comunePre;
					$data['BusPartenza'] = $bus->id;
					$data['Ordine'] = $key-1;
					
					$tratta = $this->graph->edges[$bus->percorso[$key]]->info3;
					$tt = array_shift($tratta);
					$data['TrattaId'] = $tt;
						
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
					
					$tratta = $this->graph->edges[$bus->percorso[$key]]->info3;
					$tt = array_shift($tratta);
					$data['TrattaId'] = $tt;
			
					$data=$storico->operazioni_insert($data, $user);
					$lastid = $db->insert("RT_GestioneOttimizzataNodo", $data);
				}
				
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
			$flotta = new GestioneOttimizzataFlotta();
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
		
		$prenotazioneDettaglio = new PrenotazioneDettaglio();
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
			foreach ($bus->comuni[$ultimaFermata-1]['passeggeri'] as $ultimoComune => $passeggeri){
				$deposito =  $ultimoComune;
			}
		
			if(isset($this->graph->nodes[$deposito]->destinazioni[$deposito]))
				$this->graph->nodes[$deposito]->destinazioni[$deposito]++;
			else
				$this->graph->nodes[$deposito]->destinazioni[$deposito]=1;
		}
		
		
		 
		
		
// 		foreach ($this->flotta as $bus){
// 			$ultimaFermata = sizeof($bus->comuni);
// 			$deposito = 0;
// 			foreach ($bus->comuni[$ultimaFermata-1]['passeggeri'] as $ultimoComune => $passeggeri){
// 				$deposito =  $ultimoComune;
// 			}
// 			$bus->comuni[$ultimaFermata]['comune'] = $deposito;
// 			$bus->comuni[$ultimaFermata]['passeggeri'] = array();
// 			if(isset($this->graph->nodes[$deposito]->destinazioni[$deposito]))
// 				$this->graph->nodes[$deposito]->destinazioni[$deposito]++;
// 			else
// 				$this->graph->nodes[$deposito]->destinazioni[$deposito]=1;
// 		}
		
		
		
		
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
			//ï¿½ una nuova prenotazione eseguita dopo la generazione automatica e va segnalata
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
			$prenotazioneDettaglio = new PrenotazioneDettaglio();
			$prenotazioneDettaglio->conn = $this->conn;
			$operazione['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione($prenotazioneNumero, $idLinea, $idCorsa, $corsaDataPartenza);
			$this->conn->insert('RT_GestioneOttimizzataModifiche', $operazione);
		}
	}
	
	function calcoloImportanza($db){
		
		$sql = "SELECT TrattaId FROM RT_Tratta WHERE LineaId=$this->idLinea";
		$rowsTratte = $db->fetch_array($sql);

		foreach ($rowsTratte as $key => $tratta){
			$sql = "SELECT ComuneId, ImportanzaTratta FROM RT_Fermata WHERE TrattaId=".$tratta['TrattaId']." ORDER BY FermataPeso";
			$rowsFermate = $db->fetch_array($sql);

			foreach ($rowsFermate as $key2 => $fermata){
				foreach ($rowsFermate as $key3 => $fermata2){
					if(array_key_exists($fermata2['ComuneId'], $this->graph->nodes[$fermata['ComuneId']]->descents)){
						if(!isset($this->graph->nodes[$fermata['ComuneId']]->importanza[$fermata2['ComuneId']]) || ($this->graph->nodes[$fermata['ComuneId']]->importanza[$fermata2['ComuneId']] > $fermata2['ImportanzaTratta'])){
							$this->graph->nodes[$fermata['ComuneId']]->importanza[$fermata2['ComuneId']] = $fermata2['ImportanzaTratta'];
						}
					}
				}
			}
		}
		
		
		foreach ($this->graph->nodes as $key=>$node){
			foreach ($this->graph->nodes[$key]->descents as $key2=>$value2){
				if(isset($this->graph->nodes[$key]->importanza[$key2]) && $this->graph->nodes[$key]->importanza[$key2]>0){
					foreach ($this->graph->nodes[$key]->descents as $key3=>$value3){
						if(in_array($key3, $this->graph->nodes[$key2]->descents)){
							
								$this->graph->nodes[$key]->importanza[$key3] = $this->graph->nodes[$key]->importanza[$key2];
						}
					}	
				}
				if(!isset($this->graph->nodes[$key]->importanza[$key2])){
					$this->graph->nodes[$key]->importanza[$key2] = 0;
				}
			}
		}
	}
	

}
?>