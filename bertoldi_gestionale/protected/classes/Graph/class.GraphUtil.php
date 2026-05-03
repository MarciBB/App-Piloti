<?php
include_once($classespath_."Graph/class.InterscambioGraph.php");

class GraphUtil{
		
	
	
	public static function interscambio($LineaId, $CorsaId, $DataPartenza, $comunePartenza, $comuneDestinazione, $db){
	
// 		ini_set('display_errors', 1);
// 		ini_set('error_reporting', E_ALL);
		$gruppo = new InterscambioGraph($LineaId, $CorsaId, $DataPartenza, $db, $comunePartenza, $comuneDestinazione);
		
		return $gruppo;
	
	}
	
	public static function getNumeroBiglietto($titoloId, $ordine, $tipoViaggio,
			$CorsaId, $CorsaDataPartenza, $TrattaId, $ComuneId, $interscambio, $db){
		$biglietto = array();
		if($interscambio == 0 || $interscambio == 2 || $interscambio == 3) {
			//mantengo lo stesso numero di posto se scendo o meno
			$k = $ordine-1;
			$sql = "SELECT * FROM RT_PrenotazioneTitoloPosto
					WHERE PrenotazioneTitoloId = $titoloId AND
					CorsaId = $CorsaId AND CorsaDataPartenza = '$CorsaDataPartenza' AND TipoViaggio = '$tipoViaggio' 
					AND Ordine = $k";
			$row = $db->query_first($sql);
			$biglietto['NumeroBus'] = $row['NumeroBus'];
			$biglietto['NumeroPosto'] = $row['NumeroPosto'];
		} elseif($interscambio == 1) {
			$sql = "SELECT * FROM RT_Fermata
					WHERE ComuneId = $ComuneId AND TrattaId = $TrattaId";
			$fermata = $db->query_first($sql);
			$sql = "SELECT * FROM RT_PrenotazioneTitoloPosto p
					WHERE p.CorsaId = $CorsaId AND p.CorsaDataPartenza = '$CorsaDataPartenza' 
						AND p.TrattaId = $TrattaId 
						AND (p.Interscambio = 2 OR p.Interscambio = 3)
						AND p.ComuneId IN 
							(SELECT f.ComuneId FROM RT_Fermata f 
							WHERE f.TrattaId = $TrattaId AND f.Stato = 1 AND f.Cancella = 0
							AND FermataPeso <= ".$fermata['FermataPeso'].")
					ORDER BY NumeroBus ASC, NumeroPosto ASC";
			$row = $db->fetch_array($sql);
			$continua = true;
			if(isset($row) && count($row) > 0) {
				$trovato = true;
				$ii = 0;
				while($trovato) {
					$sql = "SELECT * FROM RT_PrenotazioneTitoloPosto p
							WHERE p.CorsaId = $CorsaId AND p.CorsaDataPartenza = '$CorsaDataPartenza' 
								AND p.TrattaId = $TrattaId 
								AND p.Interscambio = 1
								AND p.ComuneId IN 
									(SELECT f.ComuneId FROM RT_Fermata f 
									WHERE f.TrattaId = $TrattaId AND f.Stato = 1 AND f.Cancella = 0
									AND FermataPeso <= ".$fermata['FermataPeso'].")
								AND p.PrenotazioneTitoloInterscambioId > ".$row[$ii]['PrenotazioneTitoloInterscambioId']."
								AND p.NumeroBus = ".$row[$ii]['NumeroBus']."
								AND p.NumeroPosto = ".$row[$ii]['NumeroPosto']."	
							ORDER BY NumeroBus ASC, NumeroPosto ASC";
					$temp = $db->query_first($sql);
					if(!isset($temp['PrenotazioneTitoloInterscambio'])){
						$trovato = false;
						$biglietto['NumeroBus'] = $row[$ii]['NumeroBus'];
						$biglietto['NumeroPosto'] = $row[$ii]['NumeroPosto'];
					}
					$ii++;
					if($ii == count($row)){
						$trovato = false;
					}
				}
				
				if(isset($biglietto['NumeroPosto'])) {
					$continua = false;
				} else {
					$continua = true;
				}
			} else {
				$continua = true;
			}
			
			if($continua) {
				$sql = "SELECT * FROM RT_PrenotazioneTitoloPosto
						WHERE CorsaId = $CorsaId AND CorsaDataPartenza = '$CorsaDataPartenza'
						AND TrattaId = $TrattaId AND ComuneId = $ComuneId
						AND (Interscambio = 0 OR Interscambio = 1)
						ORDER BY NumeroBus DESC, NumeroPosto DESC";
				$row = $db->query_first($sql);
				if(!isset($row)) {
					//primo passeggero del bus
					$biglietto['NumeroBus'] = 1;
					$biglietto['NumeroPosto'] = 1;
				} else {
					$temp = array();
					$temp['NumeroBus'] = $row['NumeroBus'];
					$temp['NumeroPosto'] = $row['NumeroPosto'];
					
					//recupero i bus ed i posti totali
					$bus = array();
					$countBus = 1;
					//bus default corsa
					$sql = "SELECT b.TotalePostiNumerati
					FROM RT_TipologiaBus b
					LEFT JOIN RT_Corsa c ON c.TipologiaBusDefaultId = b.TipologiaBusId
					WHERE c.CorsaId=$CorsaId";

					$row = $db->query_first($sql);
					$bus[$countBus] = $row['TotalePostiNumerati'];
					if($bus[$countBus] == 0){
						$bus[$countBus] = $row['TotalePosti'];
					}
					
					//bus e posti aggiunti sulla ste tratte
					$sql = "SELECT *
					FROM RT_CorsaPaxTratta
					WHERE CorsaId=$CorsaId AND DataPartenza = '$CorsaDataPartenza' AND TrattaId = $TrattaId
					AND Stato = 1 AND Cancella = 0
					ORDER BY DataIns";
					
					$rows = $db->fetch_array($sql);
					$countBus++;
					foreach($rows as $b) {
						if($b['TipologiaBusId'] == 0) {
							$bus[0] += $b['NumeroPax'];
						} else {
							$bus[$countBus] = $b['NumeroPax'];
							$countBus++;
						}	
					}
					// fine recupero bus
					if(!isset($temp['NumeroBus'])){
						$biglietto['NumeroBus'] = 1;
						$biglietto['NumeroPosto'] = 1;
					} else {
						if($temp['NumeroPosto'] >= $bus[$temp['NumeroBus']]) {
							$biglietto['NumeroBus'] = $temp['NumeroBus']+1;
							$biglietto['NumeroPosto'] = 1;
						} else {
							$biglietto['NumeroBus'] = $temp['NumeroBus'];
							$biglietto['NumeroPosto'] = $temp['NumeroPosto']+1;
						}
					}
				}
			}
		}
		return $biglietto;
	}

}
?>