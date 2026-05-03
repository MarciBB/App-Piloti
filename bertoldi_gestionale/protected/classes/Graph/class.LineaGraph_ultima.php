<?php
// ini_set('display_errors', 1);
//     ini_set('error_reporting', E_ERROR);
//     ini_set('max_execution_time', 36000);
  
include_once($classespath_."Graph/Graph.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."class.Flotta.php");
include_once($classespath_."class.GestioneOttimizzataFlotta.php");
include_once($classespath_."class.Fermata.php");

class NodoFermata extends Node{
// 	public $salite;F
// 	public $discese;
	public $destinazioni;
	public $gruppo;
	public $biglietti;
	public $bigliettiSalite;
	public $busPartenza;
	public $busArrivo;
	public $importanza;
// 	public $operazioni;
	public $percorsi;
	
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
		$this->percorsi = array();
	}
}

class Gruppo{
	public $passeggeri; //array <destinazione, num. passeggeri>
	public $totalePasseggeri;
	public $posizione;
	public $posizionePrecedente;
	
	function __construct($idPosizione=null){
		$this->passeggeri = array();
		$this->totalePasseggeri = 0;
		$this->posizione = $idPosizione;
		$this->posizionePrecedente = array();
		
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
	public $strategia;
	public $unire;
	
	function __construct($idLinea, $idCorsa, $corsaDataPartenza, $conn, $caricaDB, $strategia=null, $unire=null, $maxKm=500){
		$sql = "SELECT TotalePosti
				FROM RT_TipologiaBus
				WHERE IsDefault=1";
		$row = $conn->query_first($sql);
		
		$this->postiBus = $row['TotalePosti'];

		unset($row);
		$this->flotta = array();
		if(!isset($strategia) || $strategia==0){
			$this->strategia = 7;
		}else{
			$this->strategia = $strategia;
		}
		
		if(!isset($unire)){
			$this->unire = true;
		}else{
			$this->unire = $unire;
		}
		
		/*creazione grafo*/
		$this->graph = new Graph();
		$this->idLinea = $idLinea;
		$this->conn = $conn;
		$db=$this->conn;
		
		if (! isset ( $idCorsa )) {
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
			unset ( $rowsTratte );
			
// 			print_r($this->graph->nodes);
		} else {
			//selezione tratte
			$sql = "SELECT DISTINCT t.TrattaId
			FROM RT_Tratta t LEFT JOIN RT_CorsaTariffa ct ON (t.TrattaId = ct.TrattaId)
			WHERE t.Stato = 1 AND
			t.Cancella = 0 AND
			t.LineaId = $this->idLinea AND
			ct.CorsaId = $idCorsa AND
			t.DaConfermare = 0";
				
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
			unset ( $rowsTratte );
		}
		
		if(isset($idCorsa)){
			/*inizializzazione salite e fermate*/
                    $saliteTotali=0;
                    $disceseTotali=0;
			foreach ($this->graph->nodes as $node){
// 				echo "<br><br>nodo ".$node->id."<br>";                         
				$sql = "SELECT
						RT_PrenotazioneDettaglio.PrenotazioneNumero, RT_PrenotazionePercorso.ComuneDiscesaId
						FROM
						RT_PrenotazionePercorso
						INNER JOIN RT_Prenotazione ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						INNER JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazioneDettaglio.ComunePartenza=RT_PrenotazionePercorso.ComuneSalita AND RT_PrenotazioneDettaglio.PrenotazioneId=RT_Prenotazione.PrenotazioneId)
						WHERE
						RT_PrenotazionePercorso.CorsaId=$idCorsa
						AND RT_PrenotazionePercorso.CorsaDataPartenza='$corsaDataPartenza'
						AND RT_PrenotazionePercorso.LineaId=$idLinea
						AND RT_PrenotazionePercorso.ComuneSalitaId=$node->id
						AND ((RT_Prenotazione.PrenotazioneStato=3 AND RT_PrenotazioneDettaglio.Escludi=0) 
                                OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=1) 
                                OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=0 AND RT_Prenotazione.ScadenzaPrenotazione>$corsaDataPartenza) 
                                OR (RT_Prenotazione.ABordo=1 AND RT_Prenotazione.PrenotazioneStato !=6 AND RT_Prenotazione.PrenotazioneStato !=7 AND RT_Prenotazione.PrenotazioneStato !=4 AND RT_Prenotazione.PrenotazioneStato !=3) 
                                OR (RT_Prenotazione.PrenotazioneStato=3 AND RT_Prenotazione.ABordo=1 AND RT_PrenotazioneDettaglio.Escludi=0))";
				$rows = $db->fetch_array($sql);
    
				$node->salite = 0;
				foreach ($rows as $row){
					if(array_key_exists($row['ComuneDiscesaId'], $this->graph->nodes)){
						$node->salite++;
	                                        $saliteTotali++;
						if(array_key_exists($row['ComuneDiscesaId'], $node->destinazioni)){
							$node->destinazioni[$row['ComuneDiscesaId']] = $node->destinazioni[$row['ComuneDiscesaId']] + 1;
						}else{
							$node->destinazioni[$row['ComuneDiscesaId']] = 1;
						}
					}		
				}
							
// 				echo "salite ".$node->salite;
                                $sql = "SELECT
						RT_PrenotazioneDettaglio.PrenotazioneNumero
						FROM
						RT_PrenotazionePercorso
						INNER JOIN RT_Prenotazione ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						INNER JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazioneDettaglio.ComunePartenza=RT_PrenotazionePercorso.ComuneSalita AND RT_PrenotazioneDettaglio.PrenotazioneId=RT_Prenotazione.PrenotazioneId)
						WHERE
						RT_PrenotazionePercorso.CorsaId=$idCorsa 
						AND RT_PrenotazionePercorso.CorsaDataPartenza='$corsaDataPartenza'
						AND RT_PrenotazionePercorso.LineaId=$idLinea
						AND RT_PrenotazionePercorso.ComuneDiscesaId=$node->id
						AND ((RT_Prenotazione.PrenotazioneStato=3 AND RT_PrenotazioneDettaglio.Escludi=0) 
                                OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=1) 
                                OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=0 AND RT_Prenotazione.ScadenzaPrenotazione>$corsaDataPartenza) 
                                OR (RT_Prenotazione.ABordo=1 AND RT_Prenotazione.PrenotazioneStato !=6 AND RT_Prenotazione.PrenotazioneStato !=7 AND RT_Prenotazione.PrenotazioneStato !=4 AND RT_Prenotazione.PrenotazioneStato !=3) 
                                OR (RT_Prenotazione.PrenotazioneStato=3 AND RT_Prenotazione.ABordo=1 AND RT_PrenotazioneDettaglio.Escludi=0))";
				$rowsDiscese = $db->fetch_array($sql);
				$node->discese = sizeof($rowsDiscese);
				$disceseTotali+=sizeof($rowsDiscese);
				foreach ($rows as $row){
					if(isset($row['ComuneDiscesaId'])){
					//se carico dal DB gestisco le nuovo prenotazioni
						if($caricaDB){
							if($row['PrenotazioneNumero']!=0){
								$this->gestioneCaricamentoPrenotazione($row['PrenotazioneNumero'],$idLinea, $idCorsa, $corsaDataPartenza);
							}
						}
						if (!isset($node->biglietti[$row[ComuneDiscesaId]])){
							$node->biglietti[$row['ComuneDiscesaId']] = array();
							$node->bigliettiSalite[$row['ComuneDiscesaId']] = array();
						}
						$node->biglietti[$row['ComuneDiscesaId']][]=$row['PrenotazioneNumero'];
						$node->bigliettiSalite[$row['ComuneDiscesaId']][]=$row['PrenotazioneNumero'];
					}
				}
// 				echo "discese ".$node->discese;
			}
			unset($rows);
		}
		
		/*calcolo discendenti dei nodi*/
		$this->graph->calculateDescent2();
		
		if(isset($idCorsa)){
		
// 			/*calcolo importanza discendenti*/
// 			$this->calcoloImportanza($db);
// 			echo "<br>importanza ok";
			
			if($caricaDB==true){
				//caricamento bus da DB
				$this->checkGestioneOttimizzataModifiche($idLinea, $idCorsa, $corsaDataPartenza);
				$this->caricaDB($idLinea, $idCorsa, $corsaDataPartenza);
		
			}else{
// 				echo "<br> inizio gruppi";
				/*calcolo gruppi*/
				$this->calcolaGruppi();
// 				echo "<br> fine gruppi";
				
				$this->sistemaInterscambio();
// 				print_r($this->flotta);
// 				die("eccomi");
				if($this->unire || $this->unire=="true"){
					$this->unisciBus($maxKm);
// 									die("eccomi");

					
				}
		
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
		
// 			echo "<br>Posizione ".$gruppoAvanza->posizione;
			
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
// 				if($gruppoAvanza->posizione == 6630){
// 					echo "possibili destinazioni";
// 					var_dump($prossimaFermata);
// 					echo "figli";
// 					var_dump($this->graph->nodes[$gruppoAvanza->posizione]->children);
// 				}
				$prossimaFermata1=$prossimaFermata;
				$nextNode = $this->prossimoNodo($prossimaFermata1, $gruppoAvanza, $this->strategia, $destinazione);
		
				
				
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
			
			$arrayNumPasseggeri = array();
			foreach ($this->graph->nodes[$gruppoAvanza->posizione]->children as $child){
				$arrayNumPasseggeri[$child]=$this->graph->nodes[$child]->gruppo->totalePasseggeri;
			}
			arsort($arrayNumPasseggeri);
			
			foreach ($arrayNumPasseggeri as $child => $num){
				$edgeId = $gruppoAvanza->posizione."-".$child;
// 				echo "<br><br>arco ".$edgeId;			
					
				if($this->graph->edges[$edgeId]->info2 >0){
					$fermataObj = new Fermata();
					$fermataObj->conn = $this->conn;

					if($fermataObj->isInterscambioLinea($this->idLinea, $gruppoAvanza->posizione)){
							
						if(sizeof($this->graph->nodes[$gruppoAvanza->posizione]->busArrivo)==0){
							//nuovo bus
// 							echo "<br>1<br>";if($gruppoAvanza->posizione == 6630){die("madonna");}
							$this->nuovoBus($edgeId);
							
						}else{
// 							if($gruppoAvanza->posizione==7131){
// 								echo "<br>2<br>";
// 							}
							//avanza bus
// 							echo "<br>2<br>";
							
							$this->nuovoBus($edgeId);
// 							if($gruppoAvanza->posizione == 6630){die("madonna");}
							$this->taggingBus($edgeId);
							
						}
					}else{
						if(sizeof($this->graph->nodes[$gruppoAvanza->posizione]->busArrivo)==0){
							//nuovo bus
// 							echo "<br>3<br>";if($gruppoAvanza->posizione == 6630){die("madonna");}
							$this->nuovoBus($edgeId);
							
						}else{
							//bus avanza
// 							echo "<br>4<br>";if($gruppoAvanza->posizione == 6630){die("madonna");}
							$this->busAvanza($edgeId);
							
						}
					}
		
// 					 					echo "<br>bus arrivo";
// 					 					var_dump($this->graph->nodes[$gruppoAvanza->posizione]->busArrivo);
// 					 					foreach ($this->graph->nodes[$gruppoAvanza->posizione]->busArrivo as $key=>$value){
// 					 						echo "<br>$key: ";
// 					 						var_dump($this->flotta[$key]->comuni[count($this->flotta[$key]->comuni)-1]);
// 					 					}
// 					 					echo "<br>bus partenza";
// 					 					var_dump($this->graph->nodes[$gruppoAvanza->posizione]->busPartenza);
// 					 					foreach ($this->graph->nodes[$gruppoAvanza->posizione]->busPartenza as $key=>$value){
// 					 						echo "<br>$key: ";
// 					 						var_dump($this->flotta[$key]->comuni[count($this->flotta[$key]->comuni)-1]);
// 					 					}
		
		
				}
		
			}
			
			if($gruppoAvanza->posizione == 6630){
// 				die("caravan");
			}
			
			unset($this->graph->nodes[$gruppoAvanza->posizione]->gruppo);
			unset($this->graph->nodes[$gruppoAvanza->posizione]->biglietti);
		}
		
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
				 
				/*if(!in_array($bus->comuni[$ultimaFermata-1]['comune']."-".$deposito, $bus->percorso))*/
				$bus->percorso[$ultimaFermata] = $bus->comuni[$ultimaFermata-1]['comune']."-".$deposito;
		
			}else{
				if($countDepositi<=1){
					$ultimaTratta = sizeof($bus->percorso) - 1;
					$deposito = $this->graph->edges[$bus->percorso[$ultimaTratta]]->nodeB;
					$bus->comuni[$ultimaFermata-1]['comune'] = $deposito;
					$bus->comuni[$ultimaFermata-1]['passeggeri'] = array();
				}else{
					$ultimaTratta = sizeof($bus->percorso) - 1;
					$deposito = $this->graph->edges[$bus->percorso[$ultimaTratta]]->nodeB;
					$bus->comuni[$ultimaFermata]['comune'] = $deposito;
					$bus->comuni[$ultimaFermata]['passeggeri'] = array();
				}
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
 					
 					//se il pulman non e' stato gia' usato per questo comune
 					$temp['comune'] = $this->graph->edges[$edgeId]->nodeA;
 					$temp['passeggeri'] = array();
 					$count = 0;
 					foreach ($this->flotta[$bus]->comuni[$lastComune]['passeggeri'] as $dest => $passeggeri){
 						if($this->graph->edges[$edgeId]->info2>0){
 							$tempPasseggeri = array();
 							foreach ($passeggeri as $key => $passeggero){
 								if($count<$this->postiBus){
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
 		
 		foreach ($busConsiderati as $idBus => $count){
 			if($count==0 && $this->flotta[$idBus]->comuni[count($this->flotta[$idBus]->comuni)-1]['comune']!=$this->graph->edges[$edgeId]->nodeA){
 				$tempAddComune['comune'] = $this->graph->edges[$edgeId]->nodeA;
 				$tempAddComune['passeggeri'] = array();
 				$this->flotta[$idBus]->comuni[count($this->flotta[$idBus]->comuni)] = $tempAddComune;
 			}
 		}
 		

 		//carico i nuovi passeggeri non presenti sugli autobus precedentemente
 		if($this->graph->edges[$edgeId]->info2 > 0){
 			
 			
 			$tempOrdinaPosti = array();
 			foreach ($busConsiderati as $bus => $postiOccupati){
 				$tempOrdinaPosti[$postiOccupati] = $bus;
 			}
 			asort($tempOrdinaPosti);
 			
 			foreach ($tempOrdinaPosti as $postiOccupati => $bus){
 				if($postiOccupati < $this->postiBus){
 					$lastComune = sizeof($this->flotta[$bus]->comuni) - 1;
 					$tempDest = array();
 			
 					foreach ($this->graph->edges[$edgeId]->info4 as $dest => $passeggeri){
 						if(array_key_exists ($dest , $this->flotta[$bus]->comuni[$lastComune]['passeggeri'])){
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
 					}
 					foreach ($tempDest as $removeDest){
 						unset($this->graph->edges[$edgeId]->info4[$removeDest]);
 					}
 				}
 			}
 			
 			$tempOrdinaPosti = array();
 			foreach ($busConsiderati as $bus => $postiOccupati){
 				$tempOrdinaPosti[$postiOccupati] = $bus;
 			}
 			asort($tempOrdinaPosti);
 			
 			foreach ($tempOrdinaPosti as $postiOccupati => $bus){           
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
 		
 		foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo as $nomeBus => $idBus){
 			if(count( $this->flotta[$idBus]->comuni[count($this->flotta[$idBus]->comuni)-1])==0 && $this->flotta[$idBus]->comuni[count($this->flotta[$idBus]->comuni)-1]['comune']!=$this->graph->edges[$edgeId]->nodeA){
 				$tempAddComune['comune'] = $this->graph->edges[$edgeId]->nodeA;
 				$tempAddComune['passeggeri'] = array();
 				$this->flotta[$idBus]->comuni[count($this->flotta[$idBus]->comuni)] = $tempAddComune;
 			}
 		}
 		
 		//i pulman in arrivo avanzano mantenedo gli stessi passeggeri a bordo
 		foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo as $nomeBus => $bus){
 			if($this->graph->edges[$edgeId]->info2>0){
 				$lastComune = sizeof($this->flotta[$bus]->comuni) - 1;
 				if($this->flotta[$bus]->comuni[$lastComune]['comune'] != $this->graph->edges[$edgeId]->nodeA){
 	
 					//se il pulman non e' stato gia' usato per questo comune
 					$temp['comune'] = $this->graph->edges[$edgeId]->nodeA;
 					$temp['passeggeri'] = array();
 					$count = 0;
 					foreach ($this->flotta[$bus]->comuni[$lastComune]['passeggeri'] as $dest => $passeggeri){
 						if($this->graph->edges[$edgeId]->info2>0){
 							$tempPasseggeri = array();
 							foreach ($passeggeri as $key => $passeggero){
 								if($count<$this->postiBus){
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
 		
 		foreach ($busConsiderati as $idBus => $count){
 			if($count==0 && $this->flotta[$idBus]->comuni[count($this->flotta[$idBus]->comuni)-1]['comune']!=$this->graph->edges[$edgeId]->nodeA){
 				$tempAddComune['comune'] = $this->graph->edges[$edgeId]->nodeA;
 				$tempAddComune['passeggeri'] = array();
 				$this->flotta[$idBus]->comuni[count($this->flotta[$idBus]->comuni)] = $tempAddComune;
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

 			$busDelete = array();
 			foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza as $nomeBus => $bus){
 				if(count($this->flotta[$bus]->comuni[count($this->flotta[$bus]->comuni)-1]['passeggeri'])==0){
 					$busDelete [] = $bus;
 				}
 			}
 			
 			foreach ($busDelete as $busD){
 				unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$busD]);
 				unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$busD]);
 				unset($this->flotta[$busD]->percorso[count($this->flotta[$busD]->percorso)-1]);
 			}           
 		}			
 	}
 	
 	
 	private function travasoBus($edgeId, $db){
 		$fermataObj = new Fermata();
 		$fermataObj->conn = $db;
 		
 		foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo as $nomeBus1 => $bus1){
 			foreach ($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo as $nomeBus2 => $bus2){
 				if($nomeBus1!=$nomeBus2){
                                    $passeggeriBus1 = 0;
                                     foreach ($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'] as $dest => $passeggeri){
                                         $passeggeriBus1 += count($passeggeri);
                                     }
                                     $passeggeriBus2 = 0;
                                     foreach ($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'] as $dest => $passeggeri){
                                         $passeggeriBus2 += count($passeggeri);
                                     }
                                    
 					foreach ($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'] as $dest => $passeggeri){

 						if(array_key_exists($dest, $this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'])){
 							$temp = $passeggeri;
 							foreach ($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest] as $keyPass=>$passeggero2){
 								$temp[] = $passeggero2;
 							}
 							
 							if(count($passeggeri)>count($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest])){
                                                            if($passeggeriBus1+count($temp) <= $this->postiBus ){
 								$this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest] = $temp;
 								unset($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest]);
                                                            }
 							}else{
                                                            if($passeggeriBus2+count($temp) <= $this->postiBus){
 								$this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest] = $temp;
 								unset($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest]);
                                                            }
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
                                    
                                    $passeggeriBus1 = 0;
                                     foreach ($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'] as $dest => $passeggeri){
                                         $passeggeriBus1 += count($passeggeri);
                                     }
                                     $passeggeriBus2 = 0;
                                     foreach ($this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'] as $dest => $passeggeri){
                                         $passeggeriBus2 += count($passeggeri);
                                     }
                                    
                                    
                                    
                                    
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
 						
                                                $a = count($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest]);
 						
                                                if($passeggeriBus2+count($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest]) <= $this->postiBus){
                                                    
 						if($count1<=$count2){
 							$this->flotta[$bus2]->comuni[count($this->flotta[$bus2]->comuni)-1]['passeggeri'][$dest] = $this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest];
 							unset($this->flotta[$bus1]->comuni[count($this->flotta[$bus1]->comuni)-1]['passeggeri'][$dest]);
 						}
                                               }
 						
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
		 					//$tempCount[$busArriva][$busParte] = $count;
		 					$tempCount[$busParte][$busArriva] = $count;
	 					}
 					}
 				}
 			}
 			//echo "matrice<br>";
 			//var_dump($tempCount);
 			//echo "<br>";
 			$copyCount = $tempCount;
 			$servito = array();
 			//foreach ($tempCount as $busArriva => $temp){
 			foreach ($tempCount as $busParte => $temp){	
 				$max = 0;
 				$idBus = -1;
 				
	 			//foreach ($temp as $busParte => &$tot) {
	 			foreach ($temp as $busArriva => &$tot) {
	 				//if($max<=$tot && !in_array($busParte, $servito)){
	 				if($max<=$tot && !in_array($busArriva, $servito)){
	 					$max = $tot;
	 					//$idBus = $busParte;
	 					$idBus = $busArriva;
	 					$tot = -1;
	 				}
	 			}
 				//echo "$busParte selezionato $idBus";
	 			if($idBus != -1){
	 				$servito[$idBus] = $idBus;
	 			}
	 			
	 			if($idBus > -1 && $idBus != $busParte){
	 			//if($idBus > -1 && $idBus != $busArriva){	
	 				$this->mergeBus($idBus,$busParte);
	 				//$this->mergeBus($busArriva, $idBus);

	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$idBus] = $idBus;
	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo[$idBus] = $idBus;
	 				
// 	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$busArriva] = $busArriva;
// 	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busArrivo[$busArriva] = $busArriva;
// 	 				unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$idBus]);
// 	 				unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$idBus]);
// 	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$busArriva] = $busArriva;
	 				unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$busParte]);
	 				unset($this->graph->nodes[$this->graph->edges[$edgeId]->nodeA]->busPartenza[$busParte]);
	 				$this->graph->nodes[$this->graph->edges[$edgeId]->nodeB]->busArrivo[$idBus] = $idBus;
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
		$ii = 0;
 		foreach ($this->graph->edges[$edgeId]->info4 as $key=>$value){
 			$ii++;
 			$value = array_reverse($value,true);
 			if($postiDisponibili == 0){
//  				if($edgeId == '6630-8239' && $ii>21){echo "<br>$ii aaaaaaaa"; break;}
//  		    	if($edgeId == '6630-8239' && $ii<=21){echo "<br>$ii aaaaaaaa";}
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
//  			echo "disponibili: $postiDisponibili, occupare: ".sizeof($value);
 			if($postiDisponibili >= sizeof($value)){
 		
//  				if($edgeId == '6630-8239' && $ii>21){echo "<br>$ii bbbbbbbbb"; break;}
//  				if($edgeId == '6630-8239' && $ii<=21){echo "<br>$ii bbbbbbbbb";}
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
//  				if($edgeId == '6630-8239' && $ii>21){echo "<br>$ii ccccccccccc"; break;}
//  				if($edgeId == '6630-8239' && $ii<=21){echo "<br>$ii ccccccccccc";}
 				//posti non disponibili
 				$tempPasseggeri = $value;
 				$jj = 0;
 				while(sizeof($tempPasseggeri)>0){
//  					$jj++;
//  					if($edgeId == '6630-8239'){
//  						echo "<br>$jj quanti ".sizeof($tempPasseggeri);
//  					}
//  					if($edgeId == '6630-8239' && $jj>2){echo "<br>cazz$jj"; break;}
 					
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
//  		print_r($tempPasseggeri);echo "<br>";
 		while(sizeof($tempPasseggeri)>0){
 			$p=array_pop($tempPasseggeri);
 			$sql = "SELECT b.PrenotazioneNumeroId FROM RT_PrenotazioneNumero as a
		 			LEFT JOIN RT_PrenotazioneNumero as b ON (a.PrenotazioneId=b.PrenotazioneId)
		 			WHERE
		 			a.PrenotazioneNumeroId = $p;";
 			$rows = $this->conn->fetch_array($sql);
 			$rows = array_reverse($rows,true);
//  			echo "<br>da caricare ".sizeof($rows)." dispoBUS ". $this->postiBus;
 			if(sizeof($rows)<$this->postiBus){
//  				echo "<br>meno";
//  				echo "<br>da caricare ".sizeof($rows)." dispo ". $postiDisponibili;
	 			if(sizeof($rows)<=$postiDisponibili){
	 				foreach ($rows as $row){
	 					array_push($temp['passeggeri'][$key], $row['PrenotazioneNumeroId']);
	 					unset($tempPasseggeri[array_search($row['PrenotazioneNumeroId'],$tempPasseggeri)]);
	 				}
// 	 				echo "<br>d = $postiDisponibili - ".sizeof($rows);
	 				$postiDisponibili -= sizeof($rows);
// 	 				echo " = $postiDisponibili";
	 			}else{
	 				foreach ($rows as $row){
	 					if (!in_array($row['PrenotazioneNumeroId'], $tempPasseggeri1)) {
	 						array_push($tempPasseggeri1, $row['PrenotazioneNumeroId']);
	 					}
	 				}
	 			}
 			}else{
//  				echo "<br>uno a uno";
 				foreach ($rows as $row){ 					
 					if($postiDisponibili>0){
 						if(in_array($row['PrenotazioneNumeroId'], $tempPasseggeri) || $row['PrenotazioneNumeroId'] == $p){
	 						array_push($temp['passeggeri'][$key], $row['PrenotazioneNumeroId']);
	 						$postiDisponibili--;
// 	 						echo "<br> d= $postiDisponibili";
 						}
 					}else{
 						if (!in_array($row['PrenotazioneNumeroId'], $tempPasseggeri1)) {
 							array_push($tempPasseggeri1, $row['PrenotazioneNumeroId']);
 						}
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
				if(array_key_exists($edge, $this->graph->edges)){
					$km += $this->graph->edges[$edge]->peso;
				}else{
					list($nodeA, $nodeB) = split('[/.-]', $edge);
					$km += $this->graph->getKmPercorsoBreve($nodeA, $nodeB);
				}
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
                                        
					$tratta = $this->graph->edges[$bus->percorso[$key]]->info3;
                                        if(!isset($tratta) || count($tratta)==0){
                                            $tratta = $this->graph->edges[$bus->percorso[$key-2]]->info3;
                                        }
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
					
					if(count($bus->percorso)>1){
                                            $tratta = $this->graph->edges[$bus->percorso[$key]]->info3;
                                        }else{
                                            $tratta = $this->graph->edges[$bus->percorso[$key-1]]->info3;
                                        }
                                        if(!isset($tratta) || count($tratta)==0){
                                            $tratta = $this->graph->edges[$bus->percorso[$key-2]]->info3;
                                        }
					$tt = array_shift($tratta);
					$data['TrattaId'] = $tt;
			
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
			$prenotazioneDettaglio = new PrenotazioneDettaglio();
			$prenotazioneDettaglio->conn = $this->conn;
			$operazione['PrenotazioneId'] = $prenotazioneDettaglio->getIdPrenotazione($prenotazioneNumero, $idLinea, $idCorsa, $corsaDataPartenza);
			$this->conn->insert('RT_GestioneOttimizzataModifiche', $operazione);
		}
	}
	
	function calcoloImportanza($db){
		
		//$sql = "SELECT TrattaId FROM RT_Tratta WHERE LineaId=$this->idLinea";
                $sql="SELECT
RT_Tratta.TrattaId,
count(RT_Fermata.ImportanzaTratta) as NumeroFermateConImportanza
FROM
RT_Tratta
INNER JOIN RT_Fermata ON RT_Tratta.TrattaId = RT_Fermata.TrattaId
WHERE
RT_Tratta.LineaId = 1 AND
RT_Fermata.ImportanzaTratta >0
GROUP BY
RT_Tratta.TrattaId
HAVING
NumeroFermateConImportanza >0";
            
		$rowsTratte = $db->fetch_array($sql);

		foreach ($rowsTratte as $key => $tratta){
			//$sql = "SELECT ComuneId, ImportanzaTratta FROM RT_Fermata WHERE TrattaId=".$tratta['TrattaId']." ORDER BY FermataPeso";
                    $sql = "SELECT ComuneId, ImportanzaTratta FROM RT_Fermata WHERE ImportanzaTratta>0 and TrattaId=" . $tratta ['TrattaId'] . " ORDER BY FermataPeso";
			$rowsFermate = $db->fetch_array($sql);
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
		
		
		foreach ($this->graph->nodes as $key=>$node){
			foreach ($this->graph->nodes[$key]->descents as $key2=>$value2){
				/*if(isset($this->graph->nodes[$key]->importanza[$key2]) && $this->graph->nodes[$key]->importanza[$key2]>0){
					foreach ($this->graph->nodes[$key]->descents as $key3=>$value3){
						if(in_array($key3, $this->graph->nodes[$key2]->descents)){
							
								$this->graph->nodes[$key]->importanza[$key3] = $this->graph->nodes[$key]->importanza[$key2];
						}
					}	
				}*/
				//if(!isset($this->graph->nodes[$key]->importanza[$key2])){
					$this->graph->nodes[$key]->importanza[$key2] = 0;
				//}
			}
		}
	}
	
	
	function sistemaInterscambio(){
		$array = array();
		
		$fermataObj = new Fermata();
		$fermataObj->conn = $this->conn;
		
		foreach ($this->flotta as $keyBus => $bus){
			if(!$fermataObj->isInterscambioLinea($this->idLinea, $bus->comuni[0]['comune'])){
				$busInterscambio = array();
				$comune = $bus->comuni[0]['comune'];
				
				//calcolo persone che salgono da altri bus
				$countSaliteInterscambio = 0;
				if(sizeof($this->graph->nodes[$comune]->busPartenza)>1 || sizeof($this->graph->nodes[$comune]->busArrivo)>1){
					foreach ($this->graph->nodes[$comune]->busArrivo as $tempBusId){
						if($tempBusId != $bus->id){
							$indexComuneTempBusId = -1;
							foreach ($this->flotta[$tempBusId]->comuni as $key=>$value){
								if(strcmp($value['comune'], $comune)==0){
									$indexComuneTempBusId = $key;
								}
							}
							
							$busInterscambio['id'] = $tempBusId;
							$busInterscambio['indexComune'] = $indexComuneTempBusId;
							$busInterscambio['passeggeri'] = array();
							foreach ($bus->comuni[0]['passeggeri'] as $key => $passeggeri){
								foreach ($passeggeri as $p){
									if(in_array($p, $this->flotta[$tempBusId]->comuni[$indexComuneTempBusId-1]['passeggeri'][$key])){
										$busInterscambio['passeggeri'][$key][$p] = $p;
										$countSaliteInterscambio++;
									}
								}
							}
							if(count($busInterscambio['passeggeri'])>0)
								break;
								
						}
					}
					if(count($busInterscambio)>0 && $countSaliteInterscambio>0 && count($busInterscambio['passeggeri'])>0){
						$array[$keyBus] = $busInterscambio;
					}
				}	
			}
		}
		
		//calcolo comuni da agiungere per raggiungere l'interscambio
		foreach ($array as $busInizio => $busInterscambio){
					
			$stop = false;
			$arrayComuni = array();
			$i = $busInterscambio['indexComune']-1;
			
			$comune = $this->flotta[$busInterscambio['id']]->comuni[$i]['comune'];

			$kk = 0;	
			while(!$stop && $i>=0){
// 				$kk++;
// 				if($kk>10){
// 					break;
// 				}

				
				if($fermataObj->isInterscambioLinea($this->idLinea, $comune)){
					$stop = true;
				}
				$tempComune['comune'] = $comune;
				$tempComune['indexComune'] = $i;
				$arrayComuni[] = $tempComune; 
				$i--;
				$comune = $this->flotta[$busInterscambio['id']]->comuni[$i]['comune'];
				
			}
			
			foreach ($busInterscambio['passeggeri'] as $dest => $passeg){
				foreach ($passeg as $p){
					
					$tempKey = array_search($p,$this->flotta[$busInterscambio['id']]->comuni[$busInterscambio['indexComune']]['passeggeri'][$dest]);
					
					unset($this->flotta[$busInterscambio['id']]->comuni[$busInterscambio['indexComune']]['passeggeri'][$dest][$tempKey]);
				}
			}
			
			
			//aggiunta dei comuni al nuovo bus per farlo partire dal comune di interscambio
			foreach ($arrayComuni as $comune){
				$temp['comune'] = $comune['comune'];
				$temp['passeggeri'] = $busInterscambio['passeggeri'];
				
				//inserisco passeggeri nel nuovo
				array_unshift($this->flotta[$busInizio]->comuni, $temp);
				
				//cancello dal vecchio
				foreach ($busInterscambio['passeggeri'] as $dest => $passeg){
					foreach ($passeg as $p){
						$tempKey = array_search($p, $this->flotta[$busInterscambio['id']]->comuni[$comune['indexComune']]['passeggeri'][$dest]);
						unset($this->flotta[$busInterscambio['id']]->comuni[$comune['indexComune']]['passeggeri'][$dest][$tempKey]);
					}
				
				}
				
			}
		}
	}
	
	
	function unisciBusVicini(){
		
			$busDaUnire = array();
			foreach ($this->flotta as $busKey => $bus){
				foreach ($this->flotta as $busKey2 => $bus2){
					if($busKey != $busKey2 && $bus->comuni[0]['comune']==$bus2->comuni[count($bus2->comuni)-1]['comune'] && !in_array($busKey2, $busDaUnire)){
						if(!isset($busDaUnire[$busKey])){
							//se non � stato ancora settato lo inserisco
							$busDaUnire[$busKey] = $busKey2;
						}else{
							//se � settato scelgo il bus con maggior numero di passeggeri
							$tempCount = 0;
							foreach ($bus->comuni as $indexComune => $passeggeri){
								foreach ($passeggeri as $p){
									$tempCount++;
								}
							}
							$tempCount2 = 0;
							foreach ($bus2->comuni as $indexComune => $passeggeri){
								foreach ($passeggeri as $p){
									$tempCount2++;
								}
							}
								
							if($tempCount2>$tempCount){
								$busDaUnire[$busKey] = $busKey2;
							}
						}
					}
				}
			}
			
			if(count($busDaUnire)>0){
				foreach ($busDaUnire as $busInizio => $busFine){
					$busFine=$busDaUnire[$busInizio];
					foreach ($this->flotta[$busInizio]->comuni as $keyIndex => $array){
						if($keyIndex == 0){
							$this->flotta[$busFine]->comuni[count($this->flotta[$busFine]->comuni)-1] = $array;
						}else{
							$this->flotta[$busFine]->comuni[] = $array;
						}
					}

					foreach ($busDaUnire as $busInizio2 => $busFine2){
						if($busFine2 == $busInizio){
							$busDaUnire[$busInizio2] = $busFine;
						}
					}
					
					unset($this->flotta[$busInizio]);
					
				}
				
			}	
	}
	
	function unisciBus($maxKm){
		$busDaUnire = array();
		$ii=0;
		foreach ($this->flotta as $busKey => $bus){
			foreach ($this->flotta as $busKey2 => $bus2){
				
				$da = $bus2->comuni[count($bus2->comuni)-1]['comune'];
				$a = $bus->comuni[0]['comune'];
				if(in_array($a, $this->graph->nodes[$da]->descents)){
// 					echo "($da, $a)";
					$ii++;
// 					if($ii>1){die("cazzuola");}
					$distanza = $this->graph->getKmPercorsoBreve($da, $a);
					if($distanza<$maxKm && $distanza>0){
						if($busKey != $busKey2 && in_array($bus->comuni[0]['comune'], $this->graph->nodes[$bus2->comuni[count($bus2->comuni)-1]['comune']]->descents) && !in_array($busKey2, $busDaUnire)){
							$busDaUnire[$busKey] = $busKey2;
						}
					}
				}
				
			}
		}
// 		die("maronna");
		
		if(count($busDaUnire)>0){
			foreach ($busDaUnire as $busInizio => $busFine){
				$busFine=$busDaUnire[$busInizio];
				$this->flotta[$busFine]->percorso[] = $this->flotta[$busFine]->comuni[count($this->flotta[$busFine]->comuni)-1]['comune']."-".$this->flotta[$busInizio]->comuni[0]['comune']; 
				foreach ($this->flotta[$busInizio]->comuni as $keyIndex => $array){
					$this->flotta[$busFine]->comuni[] = $array;
				}
				
				foreach ($this->flotta[$busInizio]->percorso as $keyIndex => $array){
					$this->flotta[$busFine]->percorso[] = $array;
				}
				
		
				foreach ($busDaUnire as $busInizio2 => $busFine2){
					if($busFine2 == $busInizio){
						$busDaUnire[$busInizio2] = $busFine;
					}
				}
					
				unset($this->flotta[$busInizio]);
					
			}
			
		}	
	}
	
	function prossimoNodo($prossimaFermata, $gruppoAvanza, $strategia, $destinazione){
		if(isset($strategia)){
			switch ($strategia){
				case 1: 
					$nextNode = $this->strategiaPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione);
					break;
				case 2:
					$nextNode = $this->strategiaOperazioniPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione);
					break;
				case 3:
					$nextNode = $this->strategiaImportanzaPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione);
					break;
				case 4:
					$nextNode = $this->strategiaImportanzaOperazioniPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione);
					break;
				case 5:
					$nextNode = $this->strategiaOperazioniImportanzaPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione);
					break;
				case 6:
					$nextNode = $this->strategiaKln_Raffadalli($prossimaFermata, $gruppoAvanza, $destinazione);
					break;
				case 7:
					$nextNode = $this->strategiaOperazioniImportanza($prossimaFermata, $gruppoAvanza, $destinazione);
					break;
			}
		}else{
			$nextNode = $this->strategiaOperazioniImportanza($prossimaFermata, $gruppoAvanza, $destinazione);
		}
		return $nextNode;
	}
	
	
	function strategiaPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione){
		if(sizeof($prossimaFermata)>0){
			if(sizeof($prossimaFermata)==1){
				//estrai il primo
				$nextNode = array_shift($prossimaFermata);
			}else{
				//percorso piu breve
				$nextNode = $this->graph->percorsoBreve($gruppoAvanza->posizione, $destinazione);
				if($nextNode == -1){
					$nextNode = array_shift ( $prossimaFermata );
				}
		
			}
		}	
		return $nextNode;
	}
	
	function strategiaOperazioniPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione){
		if(count($prossimaFermata)>1){
			$max=0;
			$p=null;
			$numOp=0;
			foreach ($prossimaFermata as $key => $prossima){
				if(!in_array($prossima, $gruppoAvanza->posizionePrecedente)){
					if(isset($this->graph->nodes[$prossima]->operazioni) && $this->graph->nodes[$prossima]->operazioni > $max){
						$max = $this->graph->nodes[$prossima]->operazioni;
						$p=$prossima;
						$numOp = 0;
					}else if(isset($this->graph->nodes[$prossima]->operazioni) && $this->graph->nodes[$prossima]->operazioni == $max){
						$numOp++;
					}
				}
					
			}
			if(isset($p) && $numOp==0){
				if(isset($this->graph->edges[$gruppoAvanza->posizione."-".$p]) && !isset($this->graph->edges[$p."-".$gruppoAvanza->posizione])){
					$nextNode=$p;
				}
			}
		}
			
		if(!isset($nextNode)){
			//percorso breve
// 			$nextNode = array_shift($prossimaFermata);
			if(sizeof($prossimaFermata)>0){
				if(sizeof($prossimaFermata)==1){
					//estrai il primo
					$nextNode = array_shift($prossimaFermata);
				}else{
					//percorso piu breve
					$nextNode = $this->graph->percorsoBreve($gruppoAvanza->posizione, $destinazione);
					if($nextNode == -1){
						$nextNode = array_shift ( $prossimaFermata );
					}
				}
			}
		}
		return $nextNode;
	}
	
	function strategiaKln_Raffadalli($prossimaFermata, $gruppoAvanza, $destinazione){
		if(count($prossimaFermata)>1){
			
			foreach ($prossimaFermata as $key => $prossima){
				if($destinazione==$prossima){
					$nextNode = $prossima;
					break;
				}else{
					$sql = "SELECT ImportanzaTratta FROM RT_Fermata Where ComuneId=$prossima";
					$row = $this->conn->query_first($sql);
					if($row['ImportanzaTratta']==0){
						$nextNode = $prossima;
					}
				}
			}
			if(!isset($nextNode)){
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
		
			$nextNode = array_shift($prossimaFermata);
		}else{
			$nextNode = array_shift($prossimaFermata);
		}
		
		return $nextNode;
	}
	
	function strategiaImportanzaPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione){
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
		
		//percorso breve
		if(sizeof($prossimaFermata)>0){
			if(sizeof($prossimaFermata)==1){
				//estrai il primo
				$nextNode = array_shift($prossimaFermata);
			}else{
				//percorso piu breve
				$nextNode = $this->graph->percorsoBreve($gruppoAvanza->posizione, $destinazione);
				if($nextNode == -1){
					$nextNode = array_shift ( $prossimaFermata );
				}
			}
		}
		return $nextNode;
	}
	
	function strategiaImportanzaOperazioniPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione){
		//importanza
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
		
		//operazioni
		if(!isset($nextNode)){
			if(count($prossimaFermata)>1){
				$max=0;
				$p=null;
				$numOp=0;
				foreach ($prossimaFermata as $key => $prossima){
					if(!in_array($prossima, $gruppoAvanza->posizionePrecedente)){
						if(isset($this->graph->nodes[$prossima]->operazioni) && $this->graph->nodes[$prossima]->operazioni > $max){
							$max = $this->graph->nodes[$prossima]->operazioni;
							$p=$prossima;
							$numOp = 0;
						}else if(isset($this->graph->nodes[$prossima]->operazioni) && $this->graph->nodes[$prossima]->operazioni == $max){
							$numOp++;
						}
					}
			
				}
				if(isset($p) && $numOp==0){
					if(isset($this->graph->edges[$gruppoAvanza->posizione."-".$p]) && !isset($this->graph->edges[$p."-".$gruppoAvanza->posizione])){
						$nextNode=$p;
					}
				}
			}
			
			if(!isset($nextNode)){
				//percorso breve
				if(sizeof($prossimaFermata)>0){
					if(sizeof($prossimaFermata)==1){
						//estrai il primo
						$nextNode = array_shift($prossimaFermata);
					}else{
						//percorso piu breve
						$nextNode = $this->graph->percorsoBreve($gruppoAvanza->posizione, $destinazione);
						if($nextNode == -1){
							$nextNode = array_shift ( $prossimaFermata );
						}
					}
				}
			}
		}
		return $nextNode;
	}
	
	function strategiaOperazioniImportanzaPercorsoBreve($prossimaFermata, $gruppoAvanza, $destinazione){
		
		//operazioni
		if(count($prossimaFermata)>1){
			$max=0;
			$p=null;
			$numOp=0;
			foreach ($prossimaFermata as $key => $prossima){
				if(!in_array($prossima, $gruppoAvanza->posizionePrecedente)){
					if(isset($this->graph->nodes[$prossima]->operazioni) && $this->graph->nodes[$prossima]->operazioni > $max){
						$max = $this->graph->nodes[$prossima]->operazioni;
						$p=$prossima;
						$numOp = 0;
					}else if(isset($this->graph->nodes[$prossima]->operazioni) && $this->graph->nodes[$prossima]->operazioni == $max){
						$numOp++;
					}
				}
		
			}
			if(isset($p) && $numOp==0){
				if(isset($this->graph->edges[$gruppoAvanza->posizione."-".$p]) && !isset($this->graph->edges[$p."-".$gruppoAvanza->posizione])){
					$nextNode=$p;
				}
			}
		
		}
// 		//importanza
		if(!isset($nextNode)){
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
			
			//percorso breve
			if(sizeof($prossimaFermata)>0){
				if(sizeof($prossimaFermata)==1){
					//estrai il primo
					$nextNode = array_shift($prossimaFermata);
				}else{
					//percorso piu breve
					$nextNode = $this->graph->percorsoBreve($gruppoAvanza->posizione, $destinazione);
					if($nextNode == -1){
						$nextNode = array_shift ( $prossimaFermata );
					}
				}
			}

		}
		
		return $nextNode;
	}
	
	function strategiaOperazioniImportanza($prossimaFermata, $gruppoAvanza, $destinazione){
	
		//operazioni
		if(count($prossimaFermata)>1){
			$max=0;
			$p=null;
			$numOp=0;
			foreach ($prossimaFermata as $key => $prossima){
				if(!in_array($prossima, $gruppoAvanza->posizionePrecedente)){
					if(isset($this->graph->nodes[$prossima]->operazioni) && $this->graph->nodes[$prossima]->operazioni > $max){
						$max = $this->graph->nodes[$prossima]->operazioni;
						$p=$prossima;
						$numOp = 0;
					}else if(isset($this->graph->nodes[$prossima]->operazioni) && $this->graph->nodes[$prossima]->operazioni == $max){
						$numOp++;
					}
				}
	
			}
			if(isset($p) && $numOp==0){
				if(isset($this->graph->edges[$gruppoAvanza->posizione."-".$p]) && !isset($this->graph->edges[$p."-".$gruppoAvanza->posizione])){
					$nextNode=$p;
				}
			}
	
		}
		// 		//importanza
		if(!isset($nextNode)){
// 			if(count($prossimaFermata)>1){
// 				$maxImportanza = 100;
// 				$tempRemove = array();
// 				foreach ($prossimaFermata as $key => $prossima){
// 					if($this->graph->nodes[$prossima]->importanza[$destinazione]<$maxImportanza){
// 						$maxImportanza = $this->graph->nodes[$prossima]->importanza[$destinazione];
// 					}
// 				}
					
// 				foreach ($prossimaFermata as $key => $prossima){
// 					if($this->graph->nodes[$prossima]->importanza[$destinazione]>$maxImportanza){
// 						$tempRemove[] = $key;
// 					}
// 				}
// 				foreach ($tempRemove as $trem){
// 					unset($prossimaFermata[$trem]);
// 				}
// 			}
				
			//estrai il primo
			if(sizeof($prossimaFermata)>0){
				//estrai il primo
				$nextNode = array_shift($prossimaFermata);
			}
	
		}
	
		return $nextNode;
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
	
}
?>