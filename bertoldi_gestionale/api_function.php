<?php
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL);

// function routes() {
//     global $user, $pagamentoTipoId, $operatoreGoEuro;
//     $db = new Database();
//     $db->connect();
    
//     $sql="SELECT concat(c.Comune,' - ',f.FermataNome) as Fermata, f.FermataNome,
// 		f.FermataId, f.ComuneId, MAX(f.Latitudine) AS Latitudine, MAX(f.Longitudine) AS Longitudine
// 		FROM RT_Fermata f
// 		LEFT JOIN RT_Tratta t ON (t.TrattaId = f.TrattaId)
// 		LEFT JOIN Comune c ON (f.ComuneId = c.ComuneId)
// 		WHERE f.Stato = 1 AND
// 		 f.Cancella = 0 AND
// 		 f.IsDaConfermare = 0 AND
// 		 f.IsBlackList = 0 and
// 		 t.Stato = 1 AND
// 		 t.Cancella = 0 AND
// 		 t.DaConfermare = 0
// 		 AND t.TrattaPrincipaleId IS NULL
// 		group by f.FermataId, f.ComuneId, f.FermataNome";
    
//     $fermateTemp = $db->fetch_array($sql);
//     $add = array();
//     $fermate = array();
//     foreach ($fermateTemp as $f){
//         if(!in_array($f['Fermata'], $add)){
//             $fermate[] = $f;
//             $add[$f['Fermata']] = $f['Fermata'];
//         }
//     }
    
//     $sql = "SELECT LineaId FROM RT_Linea WHERE Stato = 1 and Cancella = 0";
//     $rowLinee = $db->fetch_array($sql);
    
//     //recupero fermate connesse sullo stesso comune
//     $count = 0;
//     foreach($fermate as $fermata){
//         $connection = array();
//         $sql = "SELECT f.FermataId, f.FermataNome 
// 			FROM RT_Fermata f WHERE
// 			LEFT JOIN RT_Tratta t ON (t.TrattaId = f.TrattaId)
// 				f.ComuneId = ".$fermata['ComuneId']."
// 				AND f.FermataId <> ".$fermata['FermataId']."
// 				AND f.FermataNome not like '%".$fermata['FermataNome']."%' 
// 				AND f.Stato = 1 AND f.Cancella = 0 AND f.IsDaConfermare = 0 AND f.IsBlackList = 0
// 				AND t.Stato = 1 AND
// 				 t.Cancella = 0 AND
// 				 t.DaConfermare = 0
// 				 AND t.TrattaPrincipaleId IS NULL
// 				GROUP BY f.FermataId, f.ComuneId, f.FermataNome";
//         $tempRow = $db->fetch_array($sql);
//         foreach($tempRow as $f){
//             $connection[] = $f['FermataId'];
//         }
//         if(count($connection) > 0){
//             $fermate[$count]['virtual_station'] = 'C-'.$fermata['ComuneId'];
//         } else {
//             $fermate[$count]['virtual_station'] = '';
//         }
//         $fermate[$count]['connections'] = $connection;
//         $count++;
//     }
    
//     //recupero le fermate dalle linee
//     foreach($rowLinee as $id=>$linea){
        
//         $count = 0;
//         foreach($fermate as $fermata){
//             $comune = $fermata['ComuneId'];
            
            
//             $sql = "SELECT c.FermataDropOff as ComuneId, f.FermataId 
// 					FROM RT_CorsaTariffa c
// 					LEFT JOIN RT_Fermata f on f.ComuneId = c.FermataDropOff
// 					LEFT JOIN RT_Tratta t ON (t.TrattaId = f.TrattaId)
// 					WHERE c.TipologiaBigliettoId = 17 AND c.Tariffa > 0 
// 					AND c.FermataPickup = $comune 
// 					AND f.Stato = 1 AND
// 						 f.Cancella = 0 AND
// 						 f.IsDaConfermare = 0 AND
// 						 f.IsBlackList = 0
// 						 AND t.Stato = 1 AND
// 						 t.Cancella = 0 AND
// 						 t.DaConfermare = 0
// 						 AND t.TrattaPrincipaleId IS NULL
// 					GROUP BY c.FermataDropOff, FermataNome";
//             $connessioni = $db->fetch_array($sql);
            
//             foreach($connessioni as $f){
//                 $fermate[$count]['connections'][] = $f['FermataId'];
//             }
            
            
            
            
            
            
            
//             //     		$sql = "SELECT * FROM RT_Fermata WHERE ComuneId = ".$fermata['ComuneId'];
//             //     		$tratte = $db->fetch_array($sql);
//             //     		foreach($tratte as $t){
//             //     			$sql = "select * from RT_Fermata
//             //     					where TrattaId = ". $t['TrattaId'] ."
//             //     					and FermataPeso > ".$t['FermataPeso'] ."
//             //     					and Stato = 1 AND
//             // 						 Cancella = 0 AND
//             // 						 IsDaConfermare = 0 AND
//             // 						 IsBlackList = 0
//             //     					order by FermataPeso";
//             //     			$figlio = $db->query_first($sql);
//             //     			$figlio['FermataNome'] = str_replace("'","\'",$figlio['FermataNome']);
//             //     			$sql = "select * from RT_Fermata
//             //     					where FermataNome = '".$figlio['FermataNome']."'
//             //     					and ComuneId = ".$figlio['ComuneId'];
//             //     			$figlio = $db->query_first($sql);
//             //     			if(isset($figlio['FermataId'])){
//             // 	    			if(!in_array($figlio['FermataId'], $fermata['connections'])){
//             // 	    				$fermate[$count]['connections'][] = $figlio['FermataId'];
//             // 	    			}
//             //     			}
            
//             //     			$sql = "select * from RT_Fermata
//             //     					where TrattaId = ". $t['TrattaId'] ."
//             //     					and FermataPeso < ".$t['FermataPeso'] ."
//             //     					and Stato = 1 AND
//             // 						 Cancella = 0 AND
//             // 						 IsDaConfermare = 0 AND
//             // 						 IsBlackList = 0
//             //     					order by FermataPeso DESC";
//             //     			$padre = $db->query_first($sql);
            
//             //     			$padre['FermataNome'] = str_replace("'","\'",$padre['FermataNome']);
//             //     			$sql = "select * from RT_Fermata
//             //     					where FermataNome = '".$padre['FermataNome']."'
//             //     					and ComuneId = ".$padre['ComuneId'];
//             //     			$padre = $db->query_first($sql);
            
//             //     			if(isset($padre['FermataId'])){
//             // 	    			if(!in_array($padre['FermataId'], $fermata['connections'])){
//             // 	    				$fermate[$count]['connections'][] = $padre['FermataId'];
//             // 	    			}
//             //     			}
//             //     		}
            
//             $count++;
//         }
        
//     }
    
//     //elimino le connessioni delle fermate doppione
//     $count = 0;
//     foreach($fermate as $fermata){
//         $connection = array();
//         $lista = "";
//         foreach($fermata['connections'] as $c){
//             $lista .= "$c,";
//         }
//         $lista=rtrim($lista,",");
//         $sql = "SELECT FermataId, FermataNome, ComuneId FROM RT_Fermata WHERE
// 				FermataId IN (".$lista.")
// 				GROUP BY ComuneId";
        
//         $tempRow = $db->fetch_array($sql);
//         foreach($tempRow as $f){
//             if($fermata['FermataId'] != $f['FermataId']){
//                 $connection[] = $f['FermataId'];
//             }
//         }
//         $fermate[$count]['connections'] = $connection;
//         $count++;
//     }
    
//     $station = array();
//     $virtualStation = array();
//     $virtualStationCodice = array();
//     $add = array();
//     foreach($fermate as $fermata){
//         $temp = array();
//         $temp['code'] = $fermata['FermataId'];
//         $temp['name'] = $fermata['Fermata'];
//         $temp['latitude'] = $fermata['Latitudine'];
//         $temp['longitude'] = $fermata['Longitudine'];
//         //         if(isset($fermata['virtual_station']) && $fermata['virtual_station'] != "") {
//         //         	$temp['virtualStation'] = $fermata['virtual_station'];
//         //         } else {
//         //         	$temp['virtualStation'] = null;
//         //         }
//         $temp['connections'] = $fermata['connections'];
//         //         if(count($fermata['connections']) > 0) {
//         $station[] = $temp;
//         //         }
        
//         if(isset($fermata['virtual_station']) && $fermata['virtual_station'] != "" && !in_array($fermata['virtual_station'], $virtualStationCodice)){
//             $sql = "SELECT Comune FROM Comune WHERE ComuneId = ".$fermata['ComuneId'];
//             $row = $db->query_first($sql);
//             $tempVirtual = array();
//             $tempVirtual['code'] = $fermata['virtual_station'];
//             $tempVirtual['name'] = $row['Comune'];
//             $tempVirtual['latitude'] = $fermata['Latitudine'];
//             $tempVirtual['longitude'] = $fermata['Longitudine'];
//             $virtualStation[] = $tempVirtual;
//             $virtualStationCodice[] = $fermata['virtual_station'];
//         }
//     }
//     $responce['stations'] = $station;
//     //     $responce['virtualStations'] = $virtualStation;
    
//     return $responce;
// }

// --- FUNZIONE PER get-date-corsa ---
function getExperienceDates($lineaId, $dataInizio, $dataFine, $orario = null) {
    global $db;

    $db = new Database();
    $conn = $db->connect();

    $lineaId = intval($lineaId);
    if($lineaId <= 0 || $dataInizio == '' || $dataFine == '') {
        return array();
    }

    // Costruzione query
    $sql = "
        SELECT
            RT_Corsa.CorsaId AS CorsaId,
            RT_AppCalendario.AppCalendarioData AS AppCalendarioData,
            DATE_FORMAT(RT_AppCalendario.AppCalendarioData, '%d/%m/%Y') AS DataPartenzaFormattata,
            RT_Corsa.CorsaNome AS CorsaNome,
            RT_Corsa.LineaId AS LineaId,
            RT_Linea.LineaNome AS LineaNome,
            RT_Corsa.OrarioPartenza AS OrarioPartenza,
            RT_Corsa.OdcIdRef AS OdcIdRef,
            RT_Corsa.GestoreIdRef AS GestoreIdRef,
            COUNT(*) AS NumeroCorse
        FROM
            RT_Corsa
            JOIN RT_CorsaSettimana ON RT_Corsa.CorsaId = RT_CorsaSettimana.CorsaId
            JOIN RT_AppSettimana ON RT_CorsaSettimana.SettimanaId = RT_AppSettimana.AppSettimanaId
            JOIN RT_AppCalendario ON RT_AppSettimana.AppSettimanaGiorno = RT_AppCalendario.GiornoSettimana
            JOIN RT_Linea ON RT_Corsa.LineaId = RT_Linea.LineaId
            JOIN RT_TipologiaBus ON RT_Corsa.TipologiaBusDefaultId = RT_TipologiaBus.TipologiaBusId
            LEFT JOIN RT_CorsaBloccoWeb ON (
                RT_Corsa.CorsaId = RT_CorsaBloccoWeb.CorsaId
                AND RT_AppCalendario.AppCalendarioData = RT_CorsaBloccoWeb.DataPartenza
            )
            LEFT JOIN RT_CorsaBlocco ON (
                RT_Corsa.CorsaId = RT_CorsaBlocco.CorsaId
                AND RT_AppCalendario.AppCalendarioData = RT_CorsaBlocco.DataPartenza
            )
        WHERE
            RT_Corsa.Cancella = 0
            AND RT_Linea.IsWebSelling = 1
            AND RT_Corsa.Stato = 1
            AND RT_AppCalendario.AppCalendarioData >= RT_Corsa.AttivaDal
            AND RT_AppCalendario.AppCalendarioData <= RT_Corsa.AttivaAl
            AND (
                (RT_AppCalendario.Feriale = RT_Corsa.IncludiFeriale AND RT_Corsa.IncludiFeriale = 1)
                OR (RT_AppCalendario.Prefestivo = RT_Corsa.IncludiPrefestivo AND RT_Corsa.IncludiPrefestivo = 1)
                OR (RT_AppCalendario.Festivo = RT_Corsa.IncludiFestivo AND RT_Corsa.IncludiFestivo = 1)
            )
            AND RT_AppCalendario.AppCalendarioData >= '" . addslashes($dataInizio) . "'
            AND RT_AppCalendario.AppCalendarioData <= '" . addslashes($dataFine) . "'
            AND RT_Corsa.LineaId = " . intval($lineaId);

    // Se orario è passato, aggiungi filtro
    if ($orario) {
        $sql .= " AND RT_Corsa.OrarioPartenza >= '" . addslashes($orario) . "'";
    }

    $sql .= "
        GROUP BY
            RT_AppCalendario.AppCalendarioData
        ORDER BY
            RT_AppCalendario.AppCalendarioData,
            RT_Linea.PercorsoId,
            RT_Linea.LineaNome,
            RT_Corsa.CorsaPeso ASC,
            RT_Corsa.CorsaNome
    ";

    $result = $db->fetch_array($sql);

    return $result;
}

//restituisce il prezzo delle tipologie di ticket disponibili
// function search($idDeparturePost, $idArrivalPost, $dateP, $dateR, $adulti, $infant, $bambini, $nextDays = 0) {
//     global $user, $pagamentoTipoId, $operatoreGoEuro;
    
//     $db = new Database();
//     $db->connect();
    
//     $arr_biglietti_prenotati['17_Adulti  (da 13 a 99 anni)'] = $adulti;
//     $arr_biglietti_prenotati['3_Ragazzi (da 1 a 12 anni)'] = $bambini;
//     $arr_biglietti_prenotati['1_Neonati (da 0 a 0 anni)'] = $infant;
    
//     $errorParam = false;
//     if($adulti == 0 && $bambini == 0){
//         $errorParam = true;
//     }
//     $today = date('Y-m-d');
//     if($dateP < $today){
//         $errorParam = true;
//     }
//     if(isset($dateR) && $dateR < $today && $dateR < $dateP ) {
//         $errorParam = true;
//     }
    
//     $sql = "SELECT * FROM RT_Fermata f2
// 		LEFT JOIN RT_Tratta t2 ON t2.TrattaId = f2.TrattaId
// 		WHERE f2.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$idDeparturePost." AND Stato = 1)
// 		AND f2.Stato = 1";
    
//     $tempSalita = $db->query_first($sql);
//     if($tempSalita['FermataId'] != $idDeparturePost){
//         //echo "AAAA";
//         //$errorParam = true;
//     }
//     $sql = "SELECT * FROM RT_Fermata f
// 		LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
// 		WHERE f.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$idArrivalPost." AND Stato = 1)
// 		AND f.Stato = 1";
//     $tempDiscesa = $db->query_first($sql);
//     if($tempDiscesa['FermataId'] != $idArrivalPost){
//         //echo "ZZZZ";
//         //$errorParam = true;
//     }
    
//     if($idArrivalPost == 84 || $idArrivalPost == 73 || $idDeparturePost == 65 || $idArrivalPost == 42 || $idDeparturePost == 42 || $idArrivalPost == 10) {
//         //echo "XXXX";
//         $errorParam = true;
//     }
    
//     if($errorParam){
//         $data['currency'] = 'EUR';
//         $data['solutions'] = array();
//         $data['combinations'] = array();
        
//         return $data;
//     }
    
//     $totPosti = $adulti+$infant+$bambini;
    
    
    
//     $user = new Operatore();
//     $user->conn = $db;
//     $user->inizializzaMobile($operatoreGoEuro);
//     $comune = explode("C-",$idDeparturePost);
//     if(count($comune) > 1){

//     } else {
//         $sql = "SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$idDeparturePost;
//         $tempRow = $db->query_first($sql);
//         $idDeparture = $tempRow['ComuneId'];
//     }
    
//     $comune = explode("C-",$idArrivalPost);
//     if(count($comune) > 1){
        
//     } else {
//         $sql = "SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$idArrivalPost;
//         $tempRow = $db->query_first($sql);
//         $idArrival = $tempRow['ComuneId'];
//     }
    
//     //ricavo il tipo viaggio TP = 1 -> Corsa Singola, TP = 2 -> A/R
//     $Tp = 1;
//     if(isset($dateP) && isset($dateR) && $dateR!='' && $dateP!=''){
//         $Tp = 2;
//     }
    
//     //arrey di ritorno della chiamata
//     $dataSend = array();
    
//     for($i = 0; $i<=$nextDays ;$i++){
        
//         $pieces = explode("-", $dateP);
//         $from_unix_time = mktime(0, 0, 0, $pieces[1], $pieces[2], $pieces[0]);
//         $dayP = strtotime("+".$i." day", $from_unix_time);
//         $dayPformatted = date('Y-m-d', $dayP);
        
//         //echo "$i $dayPformatted<br>";
        
//         //creo l'arrey di biglietti
//         $prezziBiglietti = array();
        
//         /*****************recupero le corse******************/
//         // 		echo "recupero le corse inizio";
//         $corsaObj = new Corsa();
//         $corsaObj->conn = $db;
//         //corse di andata
//         $ComunePartenzaId = $idDeparture;
//         $ComuneDestinazioneId = $idArrival;
        
//         $corseA = array();
//         $corseAVerificare = $corsaObj->getCorseValideBO($dayPformatted, "", "");
     
//         foreach ($corseAVerificare as $key => $corsa){
//             /*verifica se si deve visualizzare*/
//             $visualizza = false;
//             $cId = $corsa['CorsaId'];
//             $lId = $corsa['LineaId'];
//             $dId = $corsa['AppCalendarioData'];
//             $sql = "SELECT * FROM RT_CorsaBlocco WHERE CorsaId = $cId AND DataPartenza = '$dId'";
//             //echo $cId;
//             $tempRow = $db->fetch_array($sql);
//             if(count($tempRow) > 0){
//                 //corsa bloccata
//                 $visualizza = false;
//                 //echo "corsa bloccata";
//             } else {
//                 //echo "corsa non bloccata";
//                 //corsa non bloccata
//                 $trattaPartenza = null;
//                 $trattaArrivo = null;
//                 //controllo se le fermate sono attive
//                 $sql="select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$cId";
//                 $r = $db->query_first($sql);
                
//                 if (!empty($r['PercorsoBreveId'])){
//                     $trattaPartenza=$r['TrattaPickupId'];
//                     $trattaArrivo=$r['TrattaDropOffId'];
//                     $visualizza=true;
//                 }else{
//                     $grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, 0);
//                     if(isset($grafo->flotta[0])) {
// 						$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
// 						$pre=new Prenotazione();
// 						$pre->conn=$db;
// 						$sql = "SELECT DISTINCT FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId FROM RT_ElencoFermataOrarioPK WHERE Stato=1 AND Cancella=0 AND IsPickup=1 AND CorsaId=$cId AND ComuneId=$ComunePartenzaId AND TrattaStato=1 ORDER BY TrattaPeso ASC";
						
// 						$arr_fermate=$db->fetch_array($sql);
// 						if(isset($arr_fermate[0])) {
// 							$trattaPartenza = $arr_fermate[0]['TrattaId'];
// 							if($ComuneDestinazioneId == 6462) {
// 								$sql = "SELECT DISTINCT FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId FROM RT_ElencoFermataOrarioDO WHERE Stato=1 AND Cancella=0 AND IsDropOff=1 AND CorsaId=$cId AND ComuneId=$ComuneDestinazioneId AND TrattaStato=1 AND TrattaId != 1 ORDER BY TrattaPeso ASC";
// 							} else {
// 								$sql = "SELECT DISTINCT FermataIdDrop, FermataOrarioDrop, TrattaNome, TrattaPeso, TrattaId FROM RT_ElencoFermataOrarioDO WHERE Stato=1 AND Cancella=0 AND IsDropOff=1 AND CorsaId=$cId AND ComuneId=$ComuneDestinazioneId AND TrattaStato=1 ORDER BY TrattaPeso ASC";
// 							}
							
// 							$arr_fermate_d=$db->fetch_array($sql);
// 							if(isset($arr_fermate_d[0])) {
// 								$trattaArrivo = $arr_fermate_d[0]['TrattaId'];
// 							}
// 							$ritorno=$pre->CreatePercorsoBreve($ComunePartenzaId,$ComuneDestinazioneId,$db,$TrattePercorse,$trattaPartenza,$trattaArrivo,$cId,$lId);
// 						} else {
// 							$trattaPartenza = null;
//                             $trattaArrivo = null;
// 						}
// 						unset($TrattePercorse);
//                         unset($pre);
//                         unset($ritorno);
// 					} else {
// 						$trattaPartenza = null;
//                         $trattaArrivo = null;
// 					}
// 					unset($grafo);
//                 }
                
//                 if(!isset($trattaPartenza) || !isset($trattaArrivo)){
//                     $visualizza = false;
//                     //echo "fermate non attive";
//                 } else {
//                     //echo "fermate attive";
//                     $sql = "SELECT DISTINCT FermataId,FermataOrario,TrattaNome, FermataNome FROM RT_ElencoFermataOrarioPK WHERE Stato=1 AND Cancella=0 AND IsPickup=1 AND CorsaId=$cId AND ComuneId=$ComunePartenzaId AND TrattaId=$trattaPartenza AND TrattaStato=1 ORDER BY TrattaPeso ASC";
//                     //echo $sql;
//                     $arr_fermate=$db->fetch_array($sql);
//                     // 						echo "$sql";
//                     $sql = "SELECT DISTINCT FermataIdDrop,FermataOrarioDrop,TrattaNome, FermataNome FROM RT_ElencoFermataOrarioDO WHERE Stato=1 AND Cancella=0 AND IsDropOff=1 AND CorsaId=$cId AND ComuneId=$ComuneDestinazioneId AND TrattaId=$trattaArrivo AND TrattaStato=1 ORDER BY TrattaPeso ASC";
//                     // 						echo "$sql";
//                     $arr_fermate_d=$db->fetch_array($sql);
                    
//                     $sql = "SELECT FermataNome FROM RT_Fermata WHERE FermataId = $idDeparturePost";
//                     $verificaDeparture = $db->query_first($sql);
//                     $sql = "SELECT FermataNome FROM RT_Fermata WHERE FermataId = $idArrivalPost";
//                     $verificaArrival = $db->query_first($sql);
                    
//                     //var_dump($arr_fermate);
//                     if ((sizeof ( $arr_fermate )>0) && (sizeof($arr_fermate_d)>0) && ($arr_fermate[0]['FermataNome'] == $verificaDeparture['FermataNome']) && ($arr_fermate_d[0]['FermataNome'] == $verificaArrival['FermataNome'])){
//                         //echo "fermata SI";
// 						$visualizza=true;
//                     }else{
// 						//echo "fermata NO";
//                         $visualizza=false;
//                     }
                    
                    
//                     //controllo se la tratta e' vendibile
//                     $sql = "SELECT TratteNonVendibiliId FROM RT_TratteNonVendibili WHERE ComunePickUpId=$ComunePartenzaId AND ComuneDropOffId=$ComuneDestinazioneId";
//                     $arr_esclusi=$db->fetch_array($sql);
//                     if ((sizeof ( $arr_esclusi )>0)){
//                         //echo "tratta non vendibile";
// 						$visualizza=false;
//                     }
                    
//                     //controllo se la tariffa 0
//                     $sql = "SELECT * FROM RT_CorsaTariffa WHERE CorsaId = $cId AND FermataPickup = $ComunePartenzaId AND FermataDropOff = $ComuneDestinazioneId AND TipologiaBigliettoId <> 11 AND Tariffa > 0";
//                     $arr_tariffe=$db->fetch_array($sql);
//                     if ((sizeof ( $arr_tariffe )==0)){
//                         //echo "tariffa zero";
// 						$visualizza=false;
//                     }
//                 }
                
//             }
//             /*se la corsa visualizzabile l'aggiungo alla lista*/
//             if($visualizza){
//                 $disponibili = getPostiDisponibili($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, $dId);
//                 if($disponibili >= $totPosti){
//                     $corsa['disponibili'] = $disponibili;
//                     $corseA[] = $corsa;
//                 }
//             } 
//         }
		  
//         // 		echo "recupero le corse andata fine<br>";
//         //corse di ritorno
//         if($Tp == 2){
//             $ComunePartenzaId = $idArrival;
//             $ComuneDestinazioneId = $idDeparture;
//             $corseR = array();
//             $corseRVerificare = $corsaObj->getCorseValideBO($dateR, "", "");
//             foreach ($corseRVerificare as $key => $corsa){
//                 /*verifica se si deve visualizzare*/
//                 $visualizza = false;
//                 $cId = $corsa['CorsaId'];
//                 $lId = $corsa['LineaId'];
//                 $dId = $corsa['AppCalendarioData'];
//                 $sql = "SELECT * FROM RT_CorsaBlocco WHERE CorsaId = $cId AND DataPartenza = '$dId'";
                
//                 $tempRow = $db->fetch_array($sql);
//                 if(count($tempRow) > 0){
//                     //corsa bloccata
//                     $visualizza = false;
//                 } else {
//                     //corsa non bloccata
                    
//                     $trattaPartenza = null;
//                     $trattaArrivo = null;
//                     //controllo se le fermate sono attive
//                     $sql="select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$cId";
//                     $r = $db->query_first($sql);
                    
//                     if (!empty($r['PercorsoBreveId'])){
//                         $trattaPartenza=$r['TrattaPickupId'];
//                         $trattaArrivo=$r['TrattaDropOffId'];
//                         $visualizza=true;
//                     }else{
//                         $grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId);
// 						$percorso = null;
// 						if(isset($grafo->flotta[0])) {
// 							$percorso = $grafo->flotta[0]->percorso;
// 						}
//                         $TrattePercorse = $grafo->getTratte($percorso, $trattaPartenza, $trattaArrivo);
//                         $pre=new Prenotazione();
//                         $pre->conn=$db;
//                         $ritorno=$pre->CreatePercorsoBreve($ComunePartenzaId,$ComuneDestinazioneId,$db,$TrattePercorse,$trattaPartenza,$trattaArrivo,$cId,$lId);
//                     }
                    
//                     $sql = "SELECT DISTINCT FermataId,FermataOrario,TrattaNome FROM RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaId=$trattaPartenza and TrattaStato=1 order by TrattaPeso desc ";
//                     $arr_fermate=$db->fetch_array($sql);
//                     // 						echo "$sql";
//                     $sql = "SELECT DISTINCT FermataIdDrop,FermataOrarioDrop,TrattaNome FROM RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId and TrattaId=$trattaArrivo  and TrattaStato=1 order by TrattaPeso asc";
//                     // 						echo "$sql";
//                     $arr_fermate_d=$db->fetch_array($sql);
//                     if ((sizeof ( $arr_fermate )>0) and (sizeof($arr_fermate_d)>0)){
//                         $visualizza=true;
//                     }else{
//                         $visualizza=false;
//                     }
                    
//                     //controllo se la tratta e' vendibile
//                     $sql = "SELECT TratteNonVendibiliId FROM RT_TratteNonVendibili WHERE ComunePickUpId=$ComunePartenzaId AND ComuneDropOffId=$ComuneDestinazioneId";
//                     $arr_esclusi=$db->fetch_array($sql);
//                     if ((sizeof ( $arr_esclusi )>0)){
//                         $visualizza=false;
//                     }
                    
//                     //controllo se la tariffa 0
//                     $sql = "SELECT * FROM RT_CorsaTariffa WHERE CorsaId = $cId AND FermataPickup = $ComunePartenzaId AND FermataDropOff = $ComuneDestinazioneId AND TipologiaBigliettoId <> 11 AND Tariffa > 0";
//                     $arr_tariffe=$db->fetch_array($sql);
//                     if ((sizeof ( $arr_tariffe )==0)){
//                         $visualizza=false;
//                     }
                    
                    
//                 }
//                 /*se la corsa e' visualizzabile l'aggiungo alla lista*/
//                 if($visualizza){
//                     $disponibili = getPostiDisponibili($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, $dId);
//                     if($disponibili >= $totPosti){
//                         $corsa['disponibili'] = $disponibili;
//                         $corseR[] = $corsa;
//                     }
//                 }
//             }
//         }
		
//         // 		echo "recupero le corse FINEEEE<br>";
//         /***********se sono presenti le corse di andata se TP=1 o andata e ritorno se Tp=2 proseguo*****************/
//         if(($Tp == 1 && count($corseA)> 0) || ($Tp == 2 && count($corseA)> 0 && count($corseR)>0)){
//             $prezziBiglietti = array();
//             foreach ($corseA as $c => $corsa){
               
//                 $CorsaId = $corsa['CorsaId'];
//                 $lineaId = $corsa['LineaId'];
//                 $disponibili = $corsa['disponibili'];
                
//                 /*******recupero delle fermate*************/
//                 //fermate andata
//                 $ComuneAndataId = $idDeparture;
//                 $ComuneRitornoId = $idArrival;
//                 $sql="SELECT * FROM RT_PercorsoBreve WHERE ComunePickupId=$ComuneAndataId AND ComuneDropOffId=$ComuneRitornoId AND CorsaId=$CorsaId";
//                 $r=$db->query_first($sql);
//                 $KmPercorsi=$r['KmPercorsi'];
//                 if (!empty($r['PercorsoBreveId'])){
//                     $trattaPartenza=$r['TrattaPickupId'];
//                     $trattaArrivo=$r['TrattaDropOffId'];
//                 }else{
//                     $grafo = null;
//                     $grafo = new GrafoTratte($lineaId, $CorsaId, $db, $ComuneAndataId, $ComuneRitornoId);
//                     $TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
                    
//                     $pre=new Prenotazione();
//                     $pre->conn=$db;
//                     $pre-> CreatePercorsoBreve($ComuneAndataId,$ComuneRitornoId,$db,$TrattePercorse,$trattaPartenza,$trattaArrivo,$CorsaId,$lineaId);
//                 }
//                 $sql="SELECT
// 	                checkPickupDropOff.comunePickupId,
// 	                checkPickupDropOff.comunePickup,
// 	                checkPickupDropOff.fermataPickupId,
// 	                checkPickupDropOff.comuneDropOffId,
// 	                checkPickupDropOff.comuneDropOff,
// 	                checkPickupDropOff.fermataDropOffId,
// 	                checkPickupDropOff.TrattaGruppoConnessione,
// 	                checkPickupDropOff.oraPickUp,
// 	                checkPickupDropOff.oraDropOff,
// 	                checkPickupDropOff.CorsaNome,
// 	                checkPickupDropOff.CorsaId,
// 	                checkPickupDropOff.trattaP,
// 	                checkPickupDropOff.trattaD,
// 	                checkPickupDropOff.fermataNomePickup,
// 	                checkPickupDropOff.fermataNomeDropOff,
// 	                tp.NodoPeso,
// 	                td.NodoPeso
// 	                FROM
// 	                checkPickupDropOff
// 	                INNER JOIN RT_Tratta AS tp ON checkPickupDropOff.trattaP = tp.TrattaId
// 	                INNER JOIN RT_Tratta AS td ON checkPickupDropOff.trattaD = td.TrattaId
// 	                WHERE
// 	                checkPickupDropOff.comunePickupId = $ComuneAndataId AND
// 	                checkPickupDropOff.CorsaId = $CorsaId AND
// 	                checkPickupDropOff.comuneDropOffId = $ComuneRitornoId AND (tp.NodoPeso < td.NodoPeso OR trattaP = trattaD) AND oraPickUp < oraDropOff
//                     ORDER BY TrattaGruppoConnessione ASC, tp.NodoPeso ASC, td.NodoPeso ASC";
                
//                 $checkFermate = $db->query_first($sql);
//                 $FermataIdAProw['FermataId'] = $checkFermate['fermataPickupId'];
//                 $FermataIdAProw['FermataOrario'] = $checkFermate['oraPickUp'];
//                 $FermataIdAProw['FermataNome'] = $checkFermate['fermataNomePickup'];
//                 $FermataIdAProw['Orario'] = $checkFermate['oraPickUp'];
//                 $FermataIdAProw['GiorniAggiuntivi'] = 0;
                
//                 $FermataIdADrow['FermataIdDrop'] = $checkFermate['fermataDropOffId'];
//                 $FermataIdADrow['FermataOrarioDrop'] = $checkFermate['oraDropOff'];
//                 $FermataIdADrow['FermataNome'] = $checkFermate['fermataNomeDropOff'];
//                 $FermataIdADrow['Orario'] = $checkFermate['oraDropOff'];
//                 $FermataIdADrow['GiorniAggiuntivi'] = 0;
                
//                 $FermataIdAP = $FermataIdAProw['FermataId'];
//                 $timeAP = $FermataIdAProw['Orario'];
//                 $dateAP = date('Y-m-d', strtotime($dayPformatted. ' + '.$FermataIdAProw['GiorniAggiuntivi'].' days'));
//                 $soluzioneIdA = str_replace("-","",$dayPformatted).'-'.$CorsaId;
                
//                 $FermataIdAD = $FermataIdADrow['FermataIdDrop'];
//                 $timeAD = $FermataIdADrow['Orario'];
//                 $dateAD = date('Y-m-d', strtotime($dayPformatted. ' + '.$FermataIdADrow['GiorniAggiuntivi'].' days'));
//                 unset($prenotazione);
//                 $prenotazione = new Prenotazione ();
//                 $prenotazione->conn = $db;
//                 $timeRP="";
//                 $timeRD="";
//                 $dateRP="";
//                 $dateRD="";
//                 if($Tp == 1){
//                     /******calcolo prezzi per Corsa Singola*********/
//                     unset($prezziBiglietti);

// 					$prezziBiglietti[] = $prenotazione->GetTipologiaBigliettiPrezzi(0, 0, null, $dayPformatted, $CorsaId, $FermataIdAP, $FermataIdAD, 0, null, null, $Tp, $arr_biglietti_prenotati, null, null, 0,null,null,null);
                    
//                     $temp = array();
//                     $temp['soluzioneIdA'] = $soluzioneIdA;
//                     $temp['soluzioneIdR'] = '';
//                     $temp['dateAP'] = $dateAP;
//                     $temp['timeAP'] = $timeAP;
//                     $temp['dateAD'] = $dateAD;
//                     $temp['timeAD'] = $timeAD;
//                     $temp['dateRP'] = '';
//                     $temp['timeRP'] = '';
//                     $temp['dateRD'] = '';
//                     $temp['timeRD'] = '';
//                     $temp['disponibiliA'] =	$disponibili;
//                     $temp['disponibiliR'] =	'';
                    
//                     $temp['prices'] = array();
//                     foreach ($prezziBiglietti as $k => $val){
                        
//                         foreach ($val as $b => $biglietti){
//                             if($biglietti['BigliettoId'] != '23' && $biglietti['BigliettoId'] != '11' && $biglietti['BigliettoId'] != '22' && isset($biglietti['BigliettoId']) && $biglietti['BigliettoId'] != null){
//                                 $new = array();
//                                 $new['BigliettoId'] = $biglietti['BigliettoId'];
//                                 $new['DescrizioneBiglietto'] = $biglietti['DescrizioneBiglietto'];
//                                 $new['PrezzoPax'] = $biglietti['PrezzoPax'];
//                                 $temp['prices'][] = $new;
//                             }
//                         }
                        
//                     }
//                     $dataSend[] = $temp;
                    
//                 } else if($Tp == 2){
                    
//                     foreach ($corseR as $c => $corsaR){
                        
//                         $corsaIdR = $corsaR['CorsaId'];
//                         $lineaIdR = $corsaR['LineaId'];
//                         $disponibiliR = $corsaR['disponibili'];
                        
//                         $ComuneAndataRId = $idArrival;
//                         $ComuneRitornoRId = $idDeparture;
//                         /********** recupero fermate ritorno*******/
//                         $sql="select * from RT_PercorsoBreve where ComunePickupId=$ComuneAndataRId and ComuneDropOffId=$ComuneRitornoRId and CorsaId=$corsaIdR";
//                         $r=$db->query_first($sql);
//                         $KmPercorsi=$r['KmPercorsi'];
//                         if (!empty($r['PercorsoBreveId'])){
//                             $trattaPartenza=$r['TrattaPickupId'];
//                             $trattaArrivo=$r['TrattaDropOffId'];
//                         }else{
//                             $grafo = null;
//                             $grafo = new GrafoTratte($lineaIdR, $corsaIdR, $db, $ComuneAndataRId, $ComuneRitornoRId);
//                             $TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);
                            
//                             $pre=new Prenotazione();
//                             $pre->conn=$db;
//                             $pre-> CreatePercorsoBreve($ComuneAndataRId,$ComuneRitornoRId,$db,$TrattePercorse,$trattaPartenza,$trattaArrivo,$corsaIdR,$lineaIdR);
//                         }
                        
//                         $sql="SELECT
// 	                        checkPickupDropOff.comunePickupId,
// 	                        checkPickupDropOff.comunePickup,
// 	                        checkPickupDropOff.fermataPickupId,
// 	                        checkPickupDropOff.comuneDropOffId,
// 	                        checkPickupDropOff.comuneDropOff,
// 	                        checkPickupDropOff.fermataDropOffId,
// 	                        checkPickupDropOff.TrattaGruppoConnessione,
// 	                        checkPickupDropOff.oraPickUp,
// 	                        checkPickupDropOff.oraDropOff,
// 	                        checkPickupDropOff.CorsaNome,
// 	                        checkPickupDropOff.CorsaId,
// 	                        checkPickupDropOff.trattaP,
// 	                        checkPickupDropOff.trattaD,
// 	                        checkPickupDropOff.fermataNomePickup,
// 	                        checkPickupDropOff.fermataNomeDropOff,
// 	                        tp.NodoPeso,
// 	                        td.NodoPeso
// 	                        FROM
// 	                        checkPickupDropOff
// 	                        INNER JOIN RT_Tratta AS tp ON checkPickupDropOff.trattaP = tp.TrattaId
// 	                        INNER JOIN RT_Tratta AS td ON checkPickupDropOff.trattaD = td.TrattaId
// 	                        WHERE
// 	                        checkPickupDropOff.comunePickupId = $ComuneAndataRId AND
// 	                        checkPickupDropOff.CorsaId = $corsaIdR AND
// 	                        checkPickupDropOff.comuneDropOffId = $ComuneRitornoRId AND (tp.NodoPeso < td.NodoPeso OR trattaP = trattaD) AND oraPickUp < oraDropOff
//                             ORDER BY TrattaGruppoConnessione ASC, tp.NodoPeso ASC, td.NodoPeso ASC";
                        
//                         $checkFermate = $db->query_first($sql);
//                         $FermataIdRProw['FermataId'] = $checkFermate['fermataPickupId'];
//                         $FermataIdRProw['FermataOrario'] = $checkFermate['oraPickUp'];
//                         $FermataIdRProw['FermataNome'] = $checkFermate['fermataNomePickup'];
//                         $FermataIdRProw['Orario'] = $checkFermate['oraPickUp'];
//                         $FermataIdRProw['GiorniAggiuntivi'] = 0;
                        
//                         $FermataIdRDrow['FermataIdDrop'] = $checkFermate['fermataDropOffId'];
//                         $FermataIdRDrow['FermataOrarioDrop'] = $checkFermate['oraDropOff'];
//                         $FermataIdRDrow['FermataNome'] = $checkFermate['fermataNomeDropOff'];
//                         $FermataIdRDrow['Orario'] = $checkFermate['oraDropOff'];
//                         $FermataIdRDrow['GiorniAggiuntivi'] = 0;
                        
//                         $FermataIdRP = $FermataIdRProw['FermataId'];
//                         $timeRP = $FermataIdRProw['Orario'];
//                         $dateRP = $dateR;
//                         $soluzioneIdR = str_replace("-","",$dateRP).'-'.$corsaIdR;
                        
//                         $FermataIdRD = $FermataIdRDrow['FermataIdDrop'];
//                         $timeRD = $FermataIdRDrow['Orario'];
//                         $dateRD = date('Y-m-d', strtotime($dateR. ' + '.$FermataIdRDrow['GiorniAggiuntivi'].' days'));
                        
//                         // 					echo "$dateP, $CorsaId, $FermataIdAP, $FermataIdAD, $corsaIdR, $FermataIdRP, $FermataIdRD, $Tp, $dateR";
//                         /************calcolo prezzi per A/R **************/
//                         unset($prezziBiglietti);
//                         $prezziBiglietti[] = $prenotazione->GetTipologiaBigliettiPrezzi(0, 0, null, $dayPformatted, $CorsaId, $FermataIdAP, $FermataIdAD, $corsaIdR, $FermataIdRP, $FermataIdRD, $Tp, $arr_biglietti_prenotati, null, null, 0,null,null,$dateR);
                        
                        
//                         $temp = array();
//                         $temp['soluzioneIdA'] = $soluzioneIdA;
//                         $temp['soluzioneIdR'] = $soluzioneIdR;
//                         $temp['dateAP'] = $dateAP;
//                         $temp['timeAP'] = $timeAP;
//                         $temp['dateAD'] = $dateAD;
//                         $temp['timeAD'] = $timeAD;
//                         $temp['dateRP'] = $dateRP;
//                         $temp['timeRP'] = $timeRP;
//                         $temp['dateRD'] = $dateRD;
//                         $temp['timeRD'] = $timeRD;
//                         $temp['disponibiliA'] =	$disponibili;
//                         $temp['disponibiliR'] =	$disponibiliR;
                        
//                         $temp['prices'] = array();
                        
//                         foreach ($prezziBiglietti as $k => $val){
                            
//                             foreach ($val as $b => $biglietti){
//                                 if($biglietti['BigliettoId'] != '23' && $biglietti['BigliettoId'] != '11' && $biglietti['BigliettoId'] != '22' && isset($biglietti['BigliettoId']) && $biglietti['BigliettoId'] != null){
//                                     $new = array();
//                                     $new['BigliettoId'] = $biglietti['BigliettoId'];
//                                     $new['DescrizioneBiglietto'] = $biglietti['DescrizioneBiglietto'];
//                                     $new['PrezzoPax'] = $biglietti['PrezzoPax'];
//                                     $temp['prices'][] = $new;
//                                 }
//                             }
                            
//                         }
//                         $dataSend[] = $temp;
                        
                        
//                     }
//                 }
                
                
//                 // 			echo "FINE recupero biglietti <br>";
                
//             }
//             // 			echo "FINE corsa <br>";
//         }
        
//         // 		echo "seconda fase FINEEEE<br>";
//     }
//     /************nell'array $prezziBiglietti sono presenti tutti i prezzi, ogni elemento un array di prezzi per una determinata corsa******************/
     
//     $solutionsA = array();
//     $solutionsR = array();
//     $combinations = array();
//     $added = array();
//     $offer = array();
//     foreach($dataSend as $combination){
//         if(!in_array($combination['soluzioneIdA'], $added)){
//             $solutionA['solutionId'] = $combination['soluzioneIdA'];
//             $solutionA['departureDateTime'] = $combination['dateAP'].'T'.$combination['timeAP'].'+01:00';
//             $solutionA['arrivalDateTime'] = $combination['dateAD'].'T'.$combination['timeAD'].'+01:00';
//             $solutionA['departureStationCode'] = $idDeparturePost;
//             $solutionA['arrivalStationCode'] = $idArrivalPost;
//             $added[$combination['soluzioneIdA']] = $combination['soluzioneIdA'];
            
//             $segmento = array();
//             $segmento['departureDateTime'] = $solutionA['departureDateTime'];
//             $segmento['arrivalDateTime'] = $solutionA['arrivalDateTime'];
//             $segmento['departureStationCode'] = $idDeparturePost;
//             $segmento['arrivalStationCode'] = $idArrivalPost;
//             $segmento['travelMode'] = 'bus';
//             $solutionA['segments'] = array();
//             $solutionA['segments'][] = $segmento;
            
//             $solutionsA[] = $solutionA;
//         }
//         if(isset($combination['soluzioneIdR']) && $combination['soluzioneIdR']!= "" && !in_array($combination['soluzioneIdR'], $added)){
//             $solutionR['solutionId'] = $combination['soluzioneIdR'];
//             $solutionR['departureDateTime'] = $combination['dateRP'].'T'.$combination['timeRP'].'+01:00';
//             $solutionR['arrivalDateTime'] = $combination['dateRD'].'T'.$combination['timeRD'].'+01:00';
//             $solutionR['departureStationCode'] = $idArrivalPost;
//             $solutionR['arrivalStationCode'] = $idDeparturePost;
//             $added[$combination['soluzioneIdR']] = $combination['soluzioneIdR'];
            
//             $segmento = array();
//             $segmento['departureDateTime'] = $solutionR['departureDateTime'];
//             $segmento['arrivalDateTime'] = $solutionR['arrivalDateTime'];
//             $segmento['departureStationCode'] = $idArrivalPost;
//             $segmento['arrivalStationCode'] = $idDeparturePost;
//             $segmento['travelMode'] = 'bus';
//             $solutionR['segments'] = array();
//             $solutionR['segments'][] = $segmento;
            
//             $solutionsR[] = $solutionR;
//         }
        
//         $combo = array();
//         $combo['outboundSolutionId'] = $combination['soluzioneIdA'];
//         if(isset($combination['soluzioneIdR']) && $combination['soluzioneIdR'] != ''){
//             $combo['inboundSolutionId'] = $combination['soluzioneIdR'];
//         }
//         $prezzi = array();
//         $fares = array();
//         foreach($combination['prices'] as $biglietto){
//             if($biglietto['BigliettoId'] == 17){
//                 $tempPrezzo['passengerType'] = 'adult';
//                 $tempPrezzo['priceInCents'] = round($biglietto['PrezzoPax'], 2)*100;
//                 $fares[] = $tempPrezzo;
//                 $prezzi['adulto'] = $biglietto['PrezzoPax'];
//             }
//             if($biglietto['BigliettoId'] == 3){
//                 $tempPrezzo['passengerType'] = 'child';
//                 $tempPrezzo['priceInCents'] = round($biglietto['PrezzoPax'], 2)*100;
//                 $fares[] = $tempPrezzo;
//                 $prezzi['bambini'] = $biglietto['PrezzoPax'];
//             }
//             if($biglietto['BigliettoId'] == 1){
//                 $tempPrezzo['passengerType'] = 'infant';
//                 $tempPrezzo['priceInCents'] = round($biglietto['PrezzoPax'], 2)*100;
//                 $fares[] = $tempPrezzo;
//                 $prezzi['infant'] = $biglietto['PrezzoPax'];
//             }
            
//         }
//         $offer['fares'] = $fares;
//         $offer['offerId'] = 'standard';
// 		$tempTotal = 0; 
// 		if(isset($prezzi['adulto'])) {
// 			$tempTotal = $adulti * $prezzi['adulto'];
// 		}
// 		if(isset($prezzi['bambini'])) {
// 			$tempTotal += $bambini * $prezzi['bambini'];
// 		}
// 		if(isset($prezzi['infant'])) {
// 			$tempTotal += $infant * $prezzi['infant'];
// 		}
//         $offer['totalPriceInCents'] = $tempTotal * 100;
//         $offer['url'] = Config::$UrlDominio; 
//         if(isset($combination['disponibiliR']) && $combination['disponibiliR'] != "" && $combination['disponibiliA'] > $combination['disponibiliR']){
//             $offer['availability'] = $combination['disponibiliR'];
//         } else {
//             $offer['availability'] = $combination['disponibiliA'];
//         }
//         $combo['offers'][] = $offer;
//         $combinations[] = $combo;
        
//     }
//     $data['currency'] = 'EUR';
//     $data['solutions'] = array_merge($solutionsA, $solutionsR);
//     $data['combinations'] = $combinations;
    
//     return $data;
    
// }

function getPostiDisponibili($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, $dataPartenza){
    global $user, $pagamentoTipoId, $operatoreGoEuro;
    $dId = $dataPartenza;
    $grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId, true);
    $string = '';
    $f = new Fermata();
    $f->conn = $db;
    $first = true;
    $tratte = "";
    
    $CorsaId = $cId;
    $DataPartenza = $dataPartenza;
    // 	var_dump($grafo->graph->nodes[$ComunePartenzaId]);
    foreach($grafo->flotta as $flotta){
        
        $trattaId2 = null;
        $sql = "select * from RT_Tratta where TrattaId = $flotta->trattaId";
        $tratta = $db->query_first($sql);
        if(isset($tratta['TrattaPrincipaleId'])){
            $trattaId2 = $tratta['TrattaPrincipaleId'];
        } else {
            $sql = "SELECT * FROM RT_Tratta WHERE TrattaPrincipaleId = ".$tratta['TrattaId'];
            $rowT = $db->query_first($sql);
            if(isset($rowT['TrattaId'])){
                $trattaId2 = $rowT['TrattaId'];
            } else {
                $trattaId2 = null;
            }
        }
        if(isset($trattaId2)){
            $tratte .= $flotta->trattaId.",".$trattaId2.",";
        } else {
            $tratte .= $flotta->trattaId.",";
        }
        
        foreach ($flotta->comuni as $c => $comune){
            if(count($comune['passeggeri']) > 0){
                if($first){
                    $string .= $comune['comune'];
                    $first = false;
                } else {
                    $string .= ','.$comune['comune'];
                }
            }
        }
    }
    $tratte = rtrim($tratte,',');
    $sql = "SELECT MAX(Posti) AS Posti FROM RT_DisponibilitaPostiCron
	WHERE CorsaId = $cId AND DataPartenza = '$dId' AND Comune IN ($string) AND TrattaId IN ($tratte)";
    $tempR = $db->query_first($sql);
    if(isset($tempR['Posti'])){
		//recupero posti disponibili e posti totali nel comune con meno posti disponibili
		$sql = "SELECT 
					cc.TrattaId,
					(
						(
							b.TotalePosti +
							IFNULL((
								SELECT SUM(c1.NumeroPax)
								FROM RT_CorsaPaxTratta c1
								WHERE c1.Cancella = 0 
								AND c1.CorsaId = $cId 
								AND c1.DataPartenza = '$dId' 
								AND c1.TrattaId = cc.TrattaId 
								AND c1.OdcIdRef = 1
								GROUP BY c1.CorsaId, c1.DataPartenza, c1.TrattaId, c1.OdcIdRef
							), 0)
						) - cc.Posti
					) AS PostiDisponibili
				FROM 
					RT_DisponibilitaPostiCron cc
				LEFT JOIN 
					RT_Corsa c ON c.CorsaId = cc.CorsaId
				LEFT JOIN  
					RT_TipologiaBus b ON c.TipologiaBusDefaultId = b.TipologiaBusId
				WHERE 
					cc.CorsaId = $cId 
					AND cc.DataPartenza = '$dId' 
					AND cc.Comune IN ($string) 
					AND cc.TrattaId IN ($tratte)
				ORDER BY 
					PostiDisponibili ASC
				LIMIT 1";
		$tempR1 = $db->query_first($sql);
		$disponibili = intval($tempR1['PostiDisponibili']);
    } else {
        $sql = "SELECT IFNULL((SELECT
		count(0)
		FROM
		`RT_PrenotazionePercorso`
		JOIN `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
		JOIN `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
		AND `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
		AND `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
		JOIN `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
		LEFT JOIN `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
		LEFT JOIN `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
		WHERE
		((`RT_Prenotazione`.`Cancella` = 0)
		AND (`RT_PrenotazionePercorso`.`Cancella` = 0)
		AND (`RT_PrenotazionePercorso`.`Stato` = 1)
		AND (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
		AND (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
		AND (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
		AND (`tb`.`OccupaPosto` = 1))
		AND (`RT_PrenotazionePercorso`.`CorsaId` = $CorsaId)
		AND (`RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataPartenza')
		GROUP BY 
			`RT_PrenotazionePercorso`.`CorsaDataPartenza` , 
			`RT_PrenotazionePercorso`.`CorsaId` , 
			`RT_PrenotazionePercorso`.`OdcIdRef`),0) AS PostiRealmentePrenotati";
        $tempR1 = $db->query_first($sql);
                 if(isset($tempR1['PostiRealmentePrenotati'])){
                     $postiRealmentePrenotati = $tempR1['PostiRealmentePrenotati'];
                 } else {
                     $postiRealmentePrenotati = 0;
                 }
        //$postiRealmentePrenotati = 0;
        
		if(isset($tratta)) {
			$sql = "SELECT IFNULL((SELECT SUM(c1.NumeroPax)
			FROM RT_CorsaPaxTratta c1
			WHERE
			c1.Cancella = 0 AND c1.CorsaId = $CorsaId AND c1.DataPartenza = '$DataPartenza' AND c1.OdcIdRef = 1 AND c1.TrattaId = ".$tratta['TrattaId']."
			GROUP BY c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) AS PostiAggiunti";
			$tempR = $db->query_first($sql);
		} else {
			$tempR = null;
		}
        if(!isset($tempR['PostiAggiunti'])){
            $sql = "SELECT IFNULL((SELECT SUM(c1.NumeroPax)
			FROM RT_CorsaPax c1
			WHERE
			c1.Cancella = 0 AND c1.CorsaId = $CorsaId AND c1.DataPartenza = '$DataPartenza' AND c1.OdcIdRef = 1
			GROUP BY c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) AS PostiAggiunti";
            $tempR = $db->query_first($sql);
        }
        if(isset($tempR['PostiAggiunti'])){
            $postiCorsaAggiunti = $tempR['PostiAggiunti'];
        } else {
            $postiCorsaAggiunti = 0;
        }
        
        $sql = "SELECT b.TotalePosti
		FROM RT_TipologiaBus b
		LEFT JOIN RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
		WHERE c.CorsaId = $CorsaId";
        $tempR = $db->query_first($sql);
        $postiCorsaDefault = $tempR['TotalePosti'];
        $disponibili = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
    }
    
    return $disponibili;
}

function reservation($dataAndata, $corsaAndata, $dataRitorno, $corsaRitorno, $departureId, $arrivalId,
    $passengers, $phoneNumber, $email = null, $customerName = null){
        
        global $user, $pagamentoTipoId, $operatoreGoEuro;
        
        $db = new Database();
        $db->connect();
        
        //recupero corsa id e data partenza di andata e ritorno
        //$temp = explode("-",$solutionId);
        //$dataAndata = date("Y-m-d", strtotime($temp[0]));
        $corsaAndataId = $corsaAndata;
        $sql = "SELECT LineaId FROM RT_Corsa WHERE CorsaId = ".$corsaAndataId;
        $lineaRow = $db->query_first($sql);
        $lineaAndata = $lineaRow['LineaId'];
        if(isset($dataRitorno) && isset($corsaRitorno)){
            //$temp = explode("-",$solutionRId);
            $corsaRitornoId = $corsaRitorno;
            //$dataRitorno = date("Y-m-d", strtotime($temp[0]));
            $sql = "SELECT LineaId FROM RT_Corsa WHERE CorsaId = ".$corsaRitornoId;
            $lineaRow = $db->query_first($sql);
            $lineaRitorno = $lineaRow['LineaId'];
            $tipoViaggio = 2;
        } else {
            $corsaRitornoId = '';
            $dataRitorno = '';
            $lineaRitorno = '';
            $tipoViaggio = 1;
        }
        
        //recupero comune e fermata
        //$fermataPickup = $departureId;
        //$sql = "SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$fermataPickup;
        //$tempRow = $db->query_first($sql);
        //$comunePickup = $tempRow['ComuneId'];
        
        $comunePickup = $departureId;

        //$fermataDropoff = $arrivalId;
        //$sql = "SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$fermataDropoff;
        //$tempRow = $db->query_first($sql);
        //$comuneDropoff = $tempRow['ComuneId'];

        $comuneDropoff = $arrivalId;
        
        $sql="SELECT
				checkPickupDropOff.comunePickupId,
				checkPickupDropOff.comunePickup,
				checkPickupDropOff.fermataPickupId,
				checkPickupDropOff.comuneDropOffId,
				checkPickupDropOff.comuneDropOff,
				checkPickupDropOff.fermataDropOffId,
				checkPickupDropOff.oraPickUp,
				checkPickupDropOff.oraDropOff,
				checkPickupDropOff.CorsaNome,
				checkPickupDropOff.CorsaId,
				checkPickupDropOff.trattaP,
				checkPickupDropOff.trattaD,
				checkPickupDropOff.fermataNomePickup,
				checkPickupDropOff.fermataNomeDropOff,
				tp.NodoPeso,
				td.NodoPeso
				FROM
				checkPickupDropOff
				INNER JOIN RT_Tratta as tp ON checkPickupDropOff.trattaP = tp.TrattaId
				INNER JOIN RT_Tratta as td ON checkPickupDropOff.trattaD = td.TrattaId
				WHERE
				checkPickupDropOff.comunePickupId = $comunePickup AND
				checkPickupDropOff.CorsaId = $corsaAndataId AND
				checkPickupDropOff.comuneDropOffId = $comuneDropoff AND (tp.NodoPeso < td.NodoPeso OR trattaP=trattaD) AND oraPickUp < oraDropOff
                ORDER BY tp.NodoPeso ASC, td.NodoPeso ASC";

        $checkFermate = $db->query_first($sql);
        $temp_fermata['FermataId'] = $checkFermate['fermataPickupId'];
        $temp_fermata['FermataOrario'] = $checkFermate['oraPickUp'];
        $temp_fermata['FermataNome'] = $checkFermate['fermataNomePickup'];
        $temp_fermata['Orario'] = $checkFermate['oraPickUp'];
        $arr_fermate[] = $temp_fermata;
        $fermataPickup = $arr_fermate[0]['FermataId'];
        
        $temp_fermataD['FermataIdDrop'] = $checkFermate['fermataDropOffId'];
        $temp_fermataD['FermataOrarioDrop'] = $checkFermate['oraDropOff'];
        $temp_fermataD['FermataNome'] = $checkFermate['fermataNomeDropOff'];
        $temp_fermataD['Orario'] = $checkFermate['oraDropOff'];
        $arr_fermate_d[] = $temp_fermataD;
        $fermataDropoff = $arr_fermate_d[0]['FermataIdDrop'];
        
        $etichette = array();
        $bigliettiId = array();
        $biglietti_aumento = array();
        $biglietti_riduzione = array();
        $biglietti_prenotati = array();
        $typeIndex = array();
        $typeLabel = array();
        $typeEta = array();
        $passeggeri = array();
        if(!is_array($passengers)){
            $passengers = json_decode($passengers, TRUE);
        }
        $countPasseggeri = 0;
        foreach($passengers as $p){
            $tipoId = intval($p['passengerType']);
            if(!isset($typeIndex[$tipoId])){
                $sql = "SELECT TipologiaBiglietto, EtaDa FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = ".$tipoId;
                $bigliettoTemp = $db->query_first($sql);
                $label = $tipoId."_".$bigliettoTemp['TipologiaBiglietto'];
                $typeIndex[$tipoId] = count($bigliettiId);
                $typeLabel[$tipoId] = $bigliettoTemp['TipologiaBiglietto'];
                $typeEta[$tipoId] = $bigliettoTemp['EtaDa'];
                $etichette[] = $label;
                $bigliettiId[] = $tipoId;
                $biglietti_aumento[] = 0;
                $biglietti_riduzione[] = 0;
                $biglietti_prenotati[] = 0;
            }
            $idx = $typeIndex[$tipoId];
            $biglietti_prenotati[$idx] += 1;
            $passeggero = array();
            $passeggero['TipoBigliettoId'] = $tipoId;
            $passeggero['Eta'] = isset($typeEta[$tipoId]) ? $typeEta[$tipoId] : 0;
            $passeggero['SessoId'] = 3;
            $passeggero['Principale'] = 0;
            $fallbackNome = isset($typeLabel[$tipoId]) ? $typeLabel[$tipoId] : '';
            $passeggero['Cognome'] = (isset($p['lastName']) && $p['lastName'] !== '') ? $p['lastName'] : $fallbackNome;
            $passeggero['Nome'] = (isset($p['firstName']) && $p['firstName'] !== '') ? $p['firstName'] : $fallbackNome;
            $passeggeri[] = $passeggero;
            $countPasseggeri++;
        }
        if($countPasseggeri > 0){
            $passeggeri[0]['Principale'] = 1;
        }
        $idAdulto = 0;
        if(isset($customerName) && $customerName !== ''){
            $clienteNome = $customerName;
        } else if($countPasseggeri > 0){
            $clienteNome = $passeggeri[$idAdulto]['Cognome'].' '.$passeggeri[$idAdulto]['Nome'];
        } else {
            $clienteNome = '';
        }
        $clienteCellulare = $phoneNumber;
        $clienteCellulareFamiliare = '';
        if(isset($email)){
            $clienteEmail = $email;
        } else {
            $clienteEmail = '';
        }
        if($countPasseggeri > 0){
            $clienteSessoId = $passeggeri[$idAdulto]['SessoId'];
            $clienteEta = $passeggeri[$idAdulto]['Eta'];
        } else {
            $clienteSessoId = 3;
            $clienteEta = 0;
        }
        
        $notaAndata = '';
        $notaRitorno = '';
        
        
        $count = 0;
        foreach ($etichette as $entry){
            $arr_biglietti_prenotati[$entry] = $biglietti_prenotati[$count];
            $arr_biglietti_aumento[$bigliettiId[$count]] = $biglietti_aumento[$count];
            $arr_biglietti_riduzione[$bigliettiId[$count]] = $biglietti_riduzione[$count];
            $count++;
        }
        
        
        $mobileObj = new Mobile();
        $mobileObj->conn = $db;
        
        $mobileObj->selectUserByOperatoreId($operatoreGoEuro);
        if(!isset($fermataPickup) || !isset($fermataDropoff)){
            $responce['reservationId'] = "";
            $responce['totalPriceInCents'] = null;
            $responce['currency'] = 'EUR';
            return $responce;
        }
        
        $nuovaPrenotazioneId = $mobileObj->prenotaWithoutEcho($notaAndata, $notaRitorno, $arr_biglietti_prenotati, $arr_biglietti_riduzione, $arr_biglietti_aumento, $dataRitorno, $corsaRitornoId, $dataAndata, $corsaAndataId, $comunePickup, $fermataPickup, $comuneDropoff, $fermataDropoff, $tipoViaggio, $passeggeri, $clienteNome, $clienteCellulare, $clienteCellulareFamiliare, $clienteEmail, $clienteSessoId, $clienteEta, 13);
        
        $mobileObj->selectUserByOperatoreId($operatoreGoEuro);
        
        $responce = array();
        if($nuovaPrenotazioneId != "error"){
            // Aggiornamento dei campi API nella tabella RT_Prenotazione
            $dataApi = array();
            $dataApi['APIdepartureId'] = $departureId;
            $dataApi['APIarrivalId'] = $arrivalId;
            $result = $db->update("RT_Prenotazione", $dataApi, "PrenotazioneId = $nuovaPrenotazioneId");
    
            if(isset($corsaRitornoId)){
                $data['FermataSalitaId'] = $fermataDropoff;
                $data['FermataDiscesaId'] = $fermataPickup;
                $result = $db->update ( "RT_PrenotazionePercorso", $data, "PrenotazioneId = $nuovaPrenotazioneId AND Direzione = 'R'" );
            }
            
            $sql = "SELECT * FROM RT_Prenotazione where PrenotazioneId = $nuovaPrenotazioneId";
            $p = $db->query_first($sql);
            $responce['reservationId'] = "".$nuovaPrenotazioneId;
            $responce['totalPriceInCents'] = floatval ($p['TotaleDaPagare'])*100;
            $responce['currency'] = 'EUR';
        } else {
            $responce['code'] = '105';
            $responce['message'] = 'Invalid journey';
        }
        
        $dispo = new Disponibilita();
        $dispo->aggiornaDisponibilita($corsaAndataId, $dataAndata,  $db);
        if(isset($corsaRitornoId)){
            $dispo->aggiornaDisponibilita($corsaRitornoId, $dataRitorno,  $db);
        }
        
        return $responce;
}

function reservationDelete($prenotazioneId) {
    global $user, $pagamentoTipoId, $operatoreGoEuro;
    $db = new Database();
    $db->connect();
    $mobileObj = new Mobile();
    $mobileObj->conn = $db;
    $mobileObj->selectUserByOperatoreId($operatoreGoEuro);
    $data['PrenotazioneStato'] = 4;
    
    $storico = new StoricoOperazioni ();
    $storico->conn = $db;
    $dataDettaglio = $storico->operazioni_update($data, $user);
    $result = $db->update ( "RT_Prenotazione", $data, "PrenotazioneId = $prenotazioneId" );
    
    /**controllo disponibilita posti**/
    $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$prenotazioneId." AND Direzione = 'A'";
    $percorsoA = $db->query_first($sql);
    $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$prenotazioneId." AND Direzione = 'R'";
    $percorsoR = $db->query_first($sql);
    $dispo = new Disponibilita();
    $dispo->aggiornaDisponibilita($percorsoA['CorsaDataPartenza'], $percorsoA['CorsaId']);
    $sql = "select * from RT_Prenotazione where PrenotazioneId = $prenotazioneId";
    $prenotazione = $db->query_first($sql);
    if ($prenotazione['TipoViaggioId'] == 2) {
        $dispo->aggiornaDisponibilita($percorsoR['CorsaDataPartenza'], $percorsoR['CorsaId']);
    }
    /**fine controllo disponibilita posti**/
    
    $responce['status'] = 'ok';
    return $responce;
}

function booking($prenotazioneId) {
    
    global $user, $pagamentoTipoId, $operatoreGoEuro, $db;
    
    $prenotazioneId = intval($prenotazioneId);
    
    $db = new Database();
    $db->connect();
    $storico = new StoricoOperazioni ();
    $storico->conn = $db;
    
    $user = new Operatore();
    $user->conn = $db;
    $user->inizializzaMobile($operatoreGoEuro);
    
    $sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
    $prenotazione = $db->query_first($sql);
    if($prenotazione['PrenotazioneStato'] == 13) {
        $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$prenotazione['PrenotazioneId'];
        $tragitti = $db->fetch_array($sql);
        $corsaA = 0;
        $dataA = 0;
        $corsaR = 0;
        $dataR = 0;
        $andata = array();
        $ritorno = array();
        
        foreach ($tragitti as $entry){
            if($entry['Direzione'] == 'A'){
                $corsaA = $entry['CorsaId'];
                $dataA = $entry['CorsaDataPartenza'];
                
                $andata['departureDateTime'] = str_replace(" ","T",$entry['DataOraSalita'])."+01:00";
                $andata['arrivalDateTime'] = str_replace(" ","T",$entry['DataOraDiscesa'])."+01:00";
                $andata['departureStationCode'] = $entry['ComuneSalitaId'];
                /*$sql = "select * from RT_Fermata f2
						left join RT_Tratta t2 on t2.TrattaId = f2.TrattaId
						where f2.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$andata['departureStationCode']." AND Stato = 1)
						and t2.TrattaPrincipaleId IS NULL AND f2.Stato = 1 AND FermataNome = '".$entry['FermataSalita']."'";
                $tempSalita = $db->query_first($sql);
                if(isset($tempSalita['FermataId'])){
                    $andata['departureStationCode'] = $tempSalita['FermataId'];
                }*/
                $andata['arrivalStationCode'] = $entry['ComuneDiscesaId'];
                /*$sql = "select * from RT_Fermata f
						left join RT_Tratta t on t.TrattaId = f.TrattaId
						where f.ComuneId IN (select ComuneId from RT_Fermata f1 where f1.FermataId = ".$andata['arrivalStationCode']." AND Stato = 1)
						and t.TrattaPrincipaleId IS NULL AND f.Stato = 1 AND FermataNome = '".$entry['FermataDiscesa']."'";
                $tempDiscesa = $db->query_first($sql);
                if(isset($tempDiscesa['FermataId'])){
                    $andata['arrivalStationCode'] = $tempDiscesa['FermataId'];
                }*/
                $andata['typeExperience'] = $entry['PercorsoNome'];
                $andata['experience'] = $entry['LineaNome'];

                // Controlli per sovrascrivere con i valori API originali
                if (!is_null($prenotazione['APIdepartureId']) && $andata['departureStationCode'] != $prenotazione['APIdepartureId']) {
                    $andata['departureStationCode'] = $prenotazione['APIdepartureId'];
                }
                
                if (!is_null($prenotazione['APIarrivalId']) && $andata['arrivalStationCode'] != $prenotazione['APIarrivalId']) {
                    $andata['arrivalStationCode'] = $prenotazione['APIarrivalId'];
                }
            }
            if($entry['Direzione'] == 'R'){
                $corsaR = $entry['CorsaId'];
                $dataR = $entry['CorsaDataPartenza'];
                
                $ritorno['departureDateTime'] = str_replace(" ","T",$entry['DataOraSalita'])."+01:00";
                $ritorno['arrivalDateTime'] = str_replace(" ","T",$entry['DataOraDiscesa'])."+01:00";
                $ritorno['departureStationCode'] = $entry['ComuneSalitaId'];
                /*$sql = "SELECT * FROM RT_Fermata f2
						LEFT JOIN RT_Tratta t2 ON t2.TrattaId = f2.TrattaId
						WHERE f2.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$ritorno['departureStationCode']." AND Stato = 1)
						AND t2.TrattaPrincipaleId IS NULL AND f2.Stato = 1 AND FermataNome = '".$entry['FermataSalita']."'";
                $tempSalita = $db->query_first($sql);
                if(isset($tempSalita['FermataId'])){
                    $ritorno['departureStationCode'] = $tempSalita['FermataId'];
                }*/
                $ritorno['arrivalStationCode'] = $entry['ComuneDiscesaId'];
                /*$sql = "SELECT * FROM RT_Fermata f
						LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
						WHERE f.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$ritorno['arrivalStationCode']." AND Stato = 1)
						AND t.TrattaPrincipaleId IS NULL AND f.Stato = 1 AND FermataNome = '".$entry['FermataDiscesa']."'";
                $tempDiscesa = $db->query_first($sql);
                if(isset($tempDiscesa['FermataId'])){
                    $ritorno['arrivalStationCode'] = $tempDiscesa['FermataId'];
                }*/
                $ritorno['typeExperience'] = $entry['PercorsoNome'];
                $ritorno['experience'] = $entry['LineaNome'];

                // Controlli per sovrascrivere con i valori API originali
                if (!is_null($prenotazione['APIarrivalId']) && $ritorno['departureStationCode'] != $prenotazione['APIarrivalId']) {
                    $ritorno['departureStationCode'] = $prenotazione['APIarrivalId'];
                }
                
                if (!is_null($prenotazione['APIdepartureId']) && $ritorno['arrivalStationCode'] != $prenotazione['APIdepartureId']) {
                    $ritorno['arrivalStationCode'] = $prenotazione['APIdepartureId'];
                }
            }
        }
        
        $data['PrenotazioneId'] = $prenotazioneId;
        $data['PagamentoTipoId'] = $pagamentoTipoId;
        $data['TipoMovimento'] = 'I';
        $data['Causale'] = 'Pagamento tramite API';
        $data['Data'] = date("Y-m-d H:i:s");
        $data['Importo'] = $prenotazione['TotaleDaPagare'];
        $data['Supplemento'] = '0';
        $data['DataPagamento'] = date("Y-m-d H:i:s");
        $data['ImportoPagato'] = $prenotazione['TotaleDaPagare'];;
        $data['Scadenza'] = null;
        $data['CodicePagamento'] = '';
        $data['CanalePagamentoId'] = 0;
        $data['Coupon'] = '';
        $data = $storico->operazioni_insert($data, $user);
        $result = $db->insert("RT_PrenotazioneMovimento", $data);
        
        $pre = new Prenotazione($prenotazione['PrenotazioneId']);
        $pre->conn=$db;
        
        $pre->EmettiBigliettiAuto($prenotazione['Pax'],$corsaA,$corsaR, "", date('Y-m-d H:i:s'));
        
        $dataPrenotazione['TipoPagamentoId'] = $pagamentoTipoId;
        $dataPrenotazione['Pagato'] = 1;
        $dataPrenotazione['TotalePagato'] = $prenotazione['TotaleDaPagare'];
        $dataPrenotazione['TotaleResiduo'] = 0;
        $dataPrenotazione = $storico->operazioni_update($dataPrenotazione, $user);
        $result = $db->update ( "RT_Prenotazione", $dataPrenotazione, "PrenotazioneId = $prenotazioneId" );
        
        $dataTitolo['InviaTitolo'] = 0;
        $result = $db->update ( "RT_PrenotazioneTitolo", $dataTitolo, "PrenotazioneId = $prenotazioneId" );
        
        $sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = $prenotazioneId";
        
        $titoli = $db->fetch_array($sql);
        $ticket = array();
        foreach ($titoli as $t){
            $passenger = array();
            $temp = array();
            $passenger['passengerType'] = $t['TipologiaBiglietto'];
            $passenger['firstName'] = $t['Nome'];
            $passenger['lastName'] = $t['Cognome'];
            
            $temp['passengers'][] = $passenger;
            $temp['ticketPriceInCents'] = floatval($t['ImportoTitolo'])*100;
            $temp['ticketNumber'] = $t['Codice'];
            $sql = "SELECT CodiceQrcode FROM RT_PrenotazioneNumero WHERE PrenotazioneNumeroId=".$t['PrenotazioneNumeroId'];
            $qrcode = $db->query_first($sql);
            $temp['ticketUrl'] = Config::$UrlDominio."/ticket.php?code=".$qrcode['CodiceQrcode'];
            $temp['outboundSegments'][] = $andata;
            if($prenotazione['TipoViaggioId'] == 2){
                $temp['inboundSegments'][] = $ritorno;
            }
            $ticket[] = $temp;
        }
        
        $responce['currency'] = 'EUR';
        //$responce['pnr'] = "".$prenotazioneId;
        //$responce['bookingId'] = $prenotazione['CodicePrenotazione'];
		$responce['pnr'] = $prenotazione['CodicePrenotazione'];
        $responce['bookingId'] = $prenotazione['CodicePrenotazione'];
        $responce['status'] = 'confirmed';
        $responce['totalPriceInCents'] = floatval($prenotazione['TotalePrenotazione'])*100;
        $responce['refundableAmountInCents'] = 0;
        $responce['tickets'] = $ticket;
        $responce['customer'] = $prenotazione['ClienteNome'];
        $responce['customerEmail'] = $prenotazione['ClienteEmail'];
        $responce['customerPhone'] = $prenotazione['ClienteCellulare'];
    } else {
        $responce = array();
    }
    
    $dispo = new Disponibilita();
    $dispo->aggiornaDisponibilita($corsaA, $dataA, $db);
    if(isset($corsaR)){
        $dispo->aggiornaDisponibilita($corsaR, $dataR,  $db);
    }
    
    return $responce;
}

function getBooking($codicePrenotazione) {
    global $user, $pagamentoTipoId, $operatoreGoEuro;
    
    $db = new Database();
    $db->connect();
    $storico = new StoricoOperazioni ();
    $storico->conn = $db;
    
    $user = new Operatore();
    $user->conn = $db;
    $user->inizializzaMobile($operatoreGoEuro);
    
    $sql = "SELECT * FROM RT_Prenotazione WHERE CodicePrenotazione = '$codicePrenotazione' ORDER BY DataIns Desc";
    
    $prenotazione = $db->query_first($sql);
    $prenotazioneId = $prenotazione['PrenotazioneId'];
    if($prenotazione['PrenotazioneStato'] == 3) {
        $sql = "SELECT * FROM RT_PrenotazionePercorso where PrenotazioneId = ".$prenotazione['PrenotazioneId'];
        $tragitti = $db->fetch_array($sql);
        $corsaA = 0;
        $corsaR = 0;
        $andata = array();
        $ritorno = array();
        
        foreach ($tragitti as $entry){
            
            if($entry['Direzione'] == 'A'){
                $corsaA = $entry['CorsaId'];
                
                $andata['departureDateTime'] = str_replace(" ","T",$entry['DataOraSalita'])."+01:00";
                $andata['arrivalDateTime'] = str_replace(" ","T",$entry['DataOraDiscesa'])."+01:00";
                $andata['departureStationCode'] = $entry['ComuneSalitaId'];
                /*$sql = "SELECT * FROM RT_Fermata f2
						LEFT JOIN RT_Tratta t2 ON t2.TrattaId = f2.TrattaId
						WHERE f2.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$andata['departureStationCode']." AND Stato = 1 )
						AND t2.TrattaPrincipaleId IS NULL AND f2.Stato = 1 AND FermataNome = '".$entry['FermataSalita']."'";
                $tempSalita = $db->query_first($sql);
                if(isset($tempSalita['FermataId'])){
                    $andata['departureStationCode'] = $tempSalita['FermataId'];
                }*/
				$andata['arrivalStationCode'] = $entry['ComuneDiscesaId'];
                
                /*$sql = "SELECT * FROM RT_Fermata f
						LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
						WHERE f.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$andata['arrivalStationCode']." AND Stato = 1)
						AND t.TrattaPrincipaleId IS NULL AND f.Stato = 1 AND FermataNome = '".$entry['FermataDiscesa']."'";
                $tempDiscesa = $db->query_first($sql);
                if(isset($tempDiscesa['FermataId'])){
                    $andata['arrivalStationCode'] = $tempDiscesa['FermataId'];
                }*/
                $andata['typeExperience'] = $entry['PercorsoNome'];
                $andata['experience'] = $entry['LineaNome'];
                
                // Controlli per sovrascrivere con i valori API originali
                if (!is_null($prenotazione['APIdepartureId']) && $andata['departureStationCode'] != $prenotazione['APIdepartureId']) {
                    $andata['departureStationCode'] = $prenotazione['APIdepartureId'];
                }
                
                if (!is_null($prenotazione['APIarrivalId']) && $andata['arrivalStationCode'] != $prenotazione['APIarrivalId']) {
                    $andata['arrivalStationCode'] = $prenotazione['APIarrivalId'];
                }
            }
            if($entry['Direzione'] == 'R'){
                $corsaR = $entry['CorsaId'];
                
                $ritorno['departureDateTime'] = str_replace(" ","T",$entry['DataOraSalita'])."+01:00";
                $ritorno['arrivalDateTime'] = str_replace(" ","T",$entry['DataOraDiscesa'])."+01:00";
                $ritorno['departureStationCode'] = $entry['ComuneSalitaId'];
                /*$sql = "SELECT * FROM RT_Fermata f2
						LEFT JOIN RT_Tratta t2 ON t2.TrattaId = f2.TrattaId
						WHERE f2.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$ritorno['departureStationCode']." AND Stato = 1)
						AND t2.TrattaPrincipaleId IS NULL AND f2.Stato = 1  AND FermataNome = '".$entry['FermataSalita']."'";
                $tempSalita = $db->query_first($sql);
                if(isset($tempSalita['FermataId'])){
                    $ritorno['departureStationCode'] = $tempSalita['FermataId'];
                }*/
                $ritorno['arrivalStationCode'] = $entry['ComuneDiscesaId'];
                /*$sql = "SELECT * FROM RT_Fermata f
						LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
						WHERE f.ComuneId IN (SELECT ComuneId FROM RT_Fermata WHERE FermataId = ".$ritorno['arrivalStationCode']." AND Stato = 1)
						AND t.TrattaPrincipaleId IS NULL AND f.Stato = 1 AND FermataNome = '".$entry['FermataDiscesa']."'";
                $tempDiscesa = $db->query_first($sql);
                if(isset($tempDiscesa['FermataId'])){
                    $ritorno['arrivalStationCode'] = $tempDiscesa['FermataId'];
                }*/
                $ritorno['typeExperience'] = $entry['PercorsoNome'];
                $ritorno['experience'] = $entry['LineaNome'];
                
                // Controlli per sovrascrivere con i valori API originali
                if (!is_null($prenotazione['APIarrivalId']) && $ritorno['departureStationCode'] != $prenotazione['APIarrivalId']) {
                    $ritorno['departureStationCode'] = $prenotazione['APIarrivalId'];
                }
                
                if (!is_null($prenotazione['APIdepartureId']) && $ritorno['arrivalStationCode'] != $prenotazione['APIdepartureId']) {
                    $ritorno['arrivalStationCode'] = $prenotazione['APIdepartureId'];
                }
            }
        }
        
        $sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = $prenotazioneId";
        
        $titoli = $db->fetch_array($sql);
        $ticket = array();
        foreach ($titoli as $t){
            $passenger = array();
            $temp = array();
            $passenger['passengerType'] = $t['TipologiaBiglietto'];
            $passenger['firstName'] = $t['Nome'];
            $passenger['lastName'] = $t['Cognome'];
            
            $temp['passengers'][] = $passenger;
            $temp['ticketPriceInCents'] = floatval($t['ImportoTitolo'])*100;
            $temp['ticketNumber'] = $t['Codice'];
            $sql = "SELECT CodiceQrcode FROM RT_PrenotazioneNumero WHERE PrenotazioneNumeroId=".$t['PrenotazioneNumeroId'];
            $qrcode = $db->query_first($sql);
            $temp['ticketUrl'] = Config::$UrlDominio."/ticket.php?code=".$qrcode['CodiceQrcode'];
            $temp['outboundSegments'][] = $andata;
            if($prenotazione['TipoViaggioId'] == 2){
                $temp['inboundSegments'][] = $ritorno;
            }
            $ticket[] = $temp;
        }
        
        $responce['currency'] = 'EUR';
        //$responce['pnr'] = "".$prenotazioneId;
		$responce['pnr'] = $prenotazione['CodicePrenotazione'];
        $responce['bookingId'] = $prenotazione['CodicePrenotazione'];
        $responce['status'] = 'confirmed';
        $responce['totalPriceInCents'] = floatval($prenotazione['TotalePrenotazione'])*100;
        $responce['refundableAmountInCents'] = 0;
        $responce['tickets'] = $ticket;
        $responce['customer'] = $prenotazione['ClienteNome'];
        $responce['customerEmail'] = $prenotazione['ClienteEmail'];
        $responce['customerPhone'] = $prenotazione['ClienteCellulare'];
    } else {
        $responce = array();
    }
    
    return $responce;
}

function bookingDelete($codicePrenotazione, $force) {
    ini_set('display_errors', 0);
    ini_set('error_reporting', E_ALL);
    global $user, $pagamentoTipoId, $operatoreGoEuro;
    
    $db = new Database();
    $db->connect();
    
    $mobileObj = new Mobile();
    $mobileObj->conn = $db;
    $mobileObj->selectUserByOperatoreId($operatoreGoEuro);
    
    $sql = "SELECT * FROM RT_Prenotazione WHERE CodicePrenotazione = '$codicePrenotazione'";
    $prenotazione = $db->query_first($sql);
    $prenotazioneId = $prenotazione['PrenotazioneId'];
    if($prenotazione['PrenotazioneStato'] != 3){
        return array('code' => 200, 'message' => 'no booking');
    }
    if($force) {
        $prenotazione['DataAgg'];
        
        $date = date("Y-m-d H:i:s");
        $time = strtotime($date);
        $time = $time - (15 * 60);
        $date = date("Y-m-d H:i:s", $time);
        
        if($prenotazione['DataAgg'] > $date){
            $prenotazioneId = $prenotazione['PrenotazioneId'];
            $data['PrenotazioneStato'] = 4;
            $storico = new StoricoOperazioni ();
            $storico->conn = $db;
            $dataDettaglio = $storico->operazioni_update($data, $user);
            $result = $db->update ( "RT_Prenotazione", $data, "PrenotazioneId = $prenotazioneId" );
            
            $dataT['Stato'] = 0;
            $dataT = $storico->operazioni_update($dataT, $user);
            $result = $db->update ( "RT_PrenotazioneTitolo", $dataT, "PrenotazioneId = $prenotazioneId" );
            
            /**controllo disponibilita posti**/
            $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$prenotazioneId." AND Direzione = 'A'";
            $percorsoA = $db->query_first($sql);
            $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$prenotazioneId." AND Direzione = 'R'";
            $percorsoR = $db->query_first($sql);
            $dispo = new Disponibilita();
            $dispo->aggiornaDisponibilita($percorsoA['CorsaId'], $percorsoA['CorsaDataPartenza'], $db);
            if ($prenotazione['TipoViaggioId'] == 2) {
                $dispo->aggiornaDisponibilita($percorsoR['CorsaId'], $percorsoR['CorsaDataPartenza'], $db);
            }
            /**fine controllo disponibilita posti**/
            
            return array('refundedAmountInCents' => floatval($prenotazione['TotalePrenotazione'])*100);
        } else {
            return array('code' => 123, 'message' => 'time out');
        }
    } else {
        return array('code' => 200, 'message' => 'Unsupported user deletion');
    }
    return $result;
}

function getExperiences($tipoTour = null) {
    $db = new Database();
    $db->connect();

    $sql = "SELECT
        l.LineaId,
        l.LineaNome,
        CASE
            WHEN l.TipoTour = 0 THEN 'Tour di gruppo'
            ELSE 'Tour Privato'
        END AS TipoTour
    FROM RT_Linea l
    LEFT JOIN RT_Percorso p ON p.PercorsoId = l.PercorsoId
    WHERE l.Stato = 1 AND l.Cancella = 0 AND p.Stato = 1 AND p.Cancella = 0 AND l.IsWebSelling = 1 AND l.LineaId <> 14 AND  (CASE
                WHEN IFNULL(
                       (SELECT COUNT(*)
                        FROM RT_Tratta t
                        WHERE t.LineaId = l.LineaId
                          AND t.TrattaTipoId = 2
                          AND t.Stato = 1
                          AND t.Cancella = 0
                        GROUP BY t.LineaId), 0) = 0
                THEN 0
                ELSE 1
             END) = 0";
    if ($tipoTour !== null && $tipoTour !== '') {
        $tipoTour = intval($tipoTour);
        $sql .= " AND l.TipoTour = $tipoTour";
    }

    return $db->fetch_array($sql);
}

function getExperience($lineaId) {
    $lineaId = intval($lineaId);
    $db = new Database();
    $db->connect();

    $sql = "SELECT
        l.LineaId,
        p.PercorsoNome,
        l.LineaNome,
        CASE
            WHEN l.TipoTour = 0 THEN 'Tour di gruppo'
            ELSE 'Tour Privato'
        END AS TipoTour,
        l.LineaDa as Porto,
        l.LineaDescrizione as Descrizione,
        l.LinkDescrizione as Link
    FROM RT_Linea l
    LEFT JOIN RT_Percorso p ON p.PercorsoId = l.PercorsoId
    WHERE l.Stato = 1 AND l.Cancella = 0 AND p.Stato = 1 AND p.Cancella = 0
    AND l.IsWebSelling = 1 AND l.LineaId <> 14 AND  (CASE
                WHEN IFNULL(
                       (SELECT COUNT(*)
                        FROM RT_Tratta t
                        WHERE t.LineaId = l.LineaId
                          AND t.TrattaTipoId = 2
                          AND t.Stato = 1
                          AND t.Cancella = 0
                        GROUP BY t.LineaId), 0) = 0
                THEN 0
                ELSE 1
             END) = 0
    AND l.LineaId = $lineaId";

    $result = $db->query_first($sql);
    if(isset($result['LineaId'])) {
        if($result['LineaId'] == 1) {
            $result['AndataRitorno'] = 1;
        } else {
            $result['AndataRitorno'] = 0;
        }
        return $result;
    }
    return array();
}

function getExperiencePrices($lineaId, $dataPartenza) {
    $lineaId = intval($lineaId);
    $dataPartenza = addslashes($dataPartenza);

    $db = new Database();
    $db->connect();

    $sqlTipoTour = "SELECT TipoTour FROM RT_Linea WHERE LineaId = $lineaId AND Stato = 1 AND Cancella = 0";
    $linea = $db->query_first($sqlTipoTour);
    if(!isset($linea['TipoTour'])) {
        return array();
    }

    $tipoTour = intval($linea['TipoTour']);

    $sql = "SELECT *
            FROM RT_TipologiaBiglietto t
            LEFT JOIN RT_ValiditaBigliettoDettaglio vb ON t.TipologiaBigliettoId = vb.BigliettoId
            LEFT JOIN RT_ValiditaBiglietto v ON v.ValiditaBigliettoId = vb.ValiditaBigliettoId
            LEFT JOIN RT_Corsa c ON c.CorsaId = v.CorsaId
            WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = $tipoTour AND t.OccupaPosto = 1
            AND c.LineaId = $lineaId AND v.Dal <= '$dataPartenza' AND v.Al >= '$dataPartenza'
            AND TipologiaBigliettoId <> 11
            GROUP BY t.TipologiaBigliettoId
            ORDER BY t.TipologiaBigliettoPeso";

    $rows = $db->fetch_array($sql);
    $result = array();
    foreach($rows as $row){
        $result[] = array(
            'TipologiaBigliettoId' => $row['TipologiaBigliettoId'],
            'Nome' => $row['TipologiaBiglietto'],
            'Descrizione' => $row['TipologiaBigliettoDescr']
        );
    }

    return $result;
}

function availableRides($comunePartenzaId, $comuneDestinazioneId, $dataPartenza, $lineaId, $bigliettiInput) {
    global $user, $pagamentoTipoId, $operatoreGoEuro;

    $db = new Database();
    $db->connect();

    $comunePartenzaId = intval($comunePartenzaId);
    $comuneDestinazioneId = intval($comuneDestinazioneId);
    $lineaId = intval($lineaId);
    $dataPartenzaSql = availableRidesNormalizeDate($dataPartenza);

    $andataRitorno = 0;
    if($lineaId == 1) {
        $andataRitorno = 1;
    }

    if ($comunePartenzaId <= 0 || $comuneDestinazioneId <= 0 || $lineaId <= 0 || $dataPartenzaSql == null) {
        return array('currency' => 'EUR', 'rides' => array(), 'message' => 'Parameters not valid.');
    }

    $tipoTour = 0;
    $rowTipoTour = $db->query_first("SELECT TipoTour FROM RT_Linea WHERE LineaId = $lineaId AND Stato = 1 AND Cancella = 0");
    if (isset($rowTipoTour['TipoTour'])) {
        $tipoTour = intval($rowTipoTour['TipoTour']);
    }

    $biglietti = availableRidesNormalizeBiglietti($bigliettiInput);
    if (count($biglietti) == 0) {
        return array('currency' => 'EUR', 'rides' => array(), 'message' => 'No tickets selected.');
    }

    $user = new Operatore();
    $user->conn = $db;
    $user->inizializzaMobile($operatoreGoEuro);

    $postiTotali = availableRidesCalcPostiTotali($db, $tipoTour, $biglietti);
    if ($postiTotali <= 0) {
        return array('currency' => 'EUR', 'rides' => array(), 'message' => 'Passengers not valid.');
    }

    $sOrder = "(SELECT COUNT(*) FROM RT_CorsaTrasferimento ct WHERE DataPartenza_2 = RT_AppCalendario.AppCalendarioData AND CorsaId_2 = RT_Corsa.CorsaId) DESC";
    $sWhere = "RT_Corsa.Stato = 1 AND RT_Linea.TipoTour = $tipoTour AND RT_Linea.LineaId = $lineaId";
    $sWhere .= " AND (TIMEDIFF(ADDTIME(CONCAT(RT_AppCalendario.AppCalendarioData,' 00:00:00'),RT_Corsa.OrarioPartenza),NOW())) > `RT_Corsa`.OrePrimaStopVendita ";

    $corsaObj = new Corsa();
    $corsaObj->conn = $db;
    $corse = $corsaObj->getCorseValideBO($dataPartenzaSql, $sOrder, $sWhere);

    $dataFiltroA = date('d/m/Y', strtotime($dataPartenzaSql));
    $arrayCorseVisualizzate = array();
    $rides = array();
    $prenotazione = new Prenotazione();
    $prenotazione->conn = $db;
    $now = new DateTime();

    foreach ($corse as $corsa) {
        $corsaId = intval($corsa['CorsaId']);
        $lineaCorsaId = intval($corsa['LineaId']);
        $dataCorsa = $corsa['AppCalendarioData'];

        $sql = "SELECT * FROM RT_CorsaBlocco WHERE CorsaId = $corsaId AND DataPartenza = '" . addslashes($dataCorsa) . "'";
        $blocco = $db->fetch_array($sql);
        if (count($blocco) > 0) {
            continue;
        }

        $trattaPartenza = null;
        $trattaArrivo = null;
        $visualizza = false;

        $sql = "SELECT * FROM RT_PercorsoBreve WHERE ComunePickupId=$comunePartenzaId AND ComuneDropOffId=$comuneDestinazioneId AND CorsaId=$corsaId";
        $percorsoBreve = $db->query_first($sql);
        if (!empty($percorsoBreve['PercorsoBreveId'])) {
            $trattaPartenza = intval($percorsoBreve['TrattaPickupId']);
            $trattaArrivo = intval($percorsoBreve['TrattaDropOffId']);
            $visualizza = true;
        } else {
            $sql = "SELECT distinct FermataId, TrattaId From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and CorsaId=$corsaId and ComuneId=$comunePartenzaId and TrattaStato=1 order by TrattaPeso desc";
            $arrFermatePk = $db->fetch_array($sql);
            if (isset($arrFermatePk[0])) {
                $trattaPartenza = intval($arrFermatePk[0]['TrattaId']);
            }

            $sql = "SELECT distinct FermataIdDrop, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and IsDropOff=1 and CorsaId=$corsaId and ComuneId=$comuneDestinazioneId and TrattaStato=1 order by TrattaPeso asc";
            $arrFermateDo = $db->fetch_array($sql);
            if (isset($arrFermateDo[0])) {
                $trattaArrivo = intval($arrFermateDo[0]['TrattaId']);
            }

            if (!empty($trattaPartenza) && !empty($trattaArrivo)) {
                $visualizza = true;
            }
        }

        if (!$visualizza) {
            continue;
        }

        if (isset($user->GestoreId) && intval($user->GestoreId) > 0) {
            $gestoreId = intval($user->GestoreId);
            $sql = "SELECT * FROM GestoreConvenzione where GestoreId = $gestoreId and LineaId = $lineaCorsaId and Now()<=ValidaAl and Now()>=ValidaDal";
            $convenzione = $db->fetch_array($sql);
            if (count($convenzione) == 0) {
                continue;
            }
        }

        $sql = "SELECT * FROM RT_CorsaTariffa where CorsaId = $corsaId AND FermataPickup = $comunePartenzaId AND FermataDropOff = $comuneDestinazioneId AND TipologiaBigliettoId <> 11 AND Tariffa > 0";
        $tariffeBase = $db->fetch_array($sql);
        if (count($tariffeBase) == 0) {
            continue;
        }
        
        $disponibili = intval(getPostiDisponibili($lineaCorsaId, $corsaId, $db, $comunePartenzaId, $comuneDestinazioneId, $dataCorsa));
        $occupati = availableRidesGetOccupatiApprox($db, $corsaId, $dataCorsa, $disponibili);
        if($tipoTour == 1) {
            if($occupati > 0) {
                $disponibili = 0;
            } else {
                $disponibili = 1;
            }
        }
        $orarioPartenza = isset($corsa['OrarioPartenza']) ? $corsa['OrarioPartenza'] : '';
        $dataFormattata = date('d/m/Y', strtotime($dataCorsa));

        $dataOraSpec = DateTime::createFromFormat('d/m/Y H:i:s', $dataFormattata . ' ' . $orarioPartenza);
        if (!$dataOraSpec || $now >= $dataOraSpec) {
            continue;
        }

        if ($dataFormattata != $dataFiltroA) {
            continue;
        }

        if ($tipoTour == 0) {
            if ($disponibili < $postiTotali) {
                continue;
            }
        } else {
            if ($occupati != 0 || $disponibili < $postiTotali) {
                continue;
            }
        }

        if (in_array($orarioPartenza, $arrayCorseVisualizzate)) {
            continue;
        }

        $sql = "SELECT * FROM RT_Corsa WHERE CorsaId = $corsaId AND Stato = 1 AND Cancella = 0";
        $corsaTemp = $db->query_first($sql);
        if (isset($corsaTemp['FlottaDefaultId']) && intval($corsaTemp['FlottaDefaultId']) > 0) {
            $dataCorsaSpec = $dataOraSpec->format('Y-m-d');
            $flottaId = intval($corsaTemp['FlottaDefaultId']);
            $orarioCorsa = addslashes($corsaTemp['OrarioPartenza']);
            $orarioArrivoCorsa = addslashes($corsaTemp['OrarioArrivo']);
            $sql = "SELECT pp.PrenotazionePercorsoId FROM RT_PrenotazionePercorso pp
                    LEFT JOIN RT_Corsa c ON c.CorsaId = pp.CorsaId
                    WHERE pp.CorsaDataPartenza = '$dataCorsaSpec' AND c.FlottaDefaultId = $flottaId
                    AND pp.CorsaOrarioPartenza >= '$orarioCorsa' AND pp.CorsaOrarioPartenza < '$orarioArrivoCorsa'
                    AND pp.PrenotazioneStato NOT IN (6,4) AND pp.CorsaId <> '$corsaId'";
            $checkFlotta = $db->query_first($sql);
            if (isset($checkFlotta['PrenotazionePercorsoId']) && intval($checkFlotta['PrenotazionePercorsoId']) > 0) {
                continue;
            }
        }

        $totalPrice = 0;
        $validita = true;
        foreach ($biglietti as $tipologiaBigliettoId => $qty) {
            $tipologiaBigliettoId = intval($tipologiaBigliettoId);
            $qty = intval($qty);
            if ($qty <= 0) {
                continue;
            }

            $sql = "SELECT v.ValiditaBigliettoId
                    FROM RT_ValiditaBiglietto v
                    LEFT JOIN RT_ValiditaBigliettoDettaglio b on b.ValiditaBigliettoId = v.ValiditaBigliettoId
                    WHERE CorsaId = $corsaId
                    AND '$dataPartenzaSql' >= Dal
                    AND '$dataPartenzaSql' <= Al
                    AND v.Stato = 1 AND b.Stato = 1 AND v.Cancella = 0 AND b.Cancella = 0
                    AND BigliettoId = $tipologiaBigliettoId";
            $validitaRow = $db->query_first($sql);
            if (!isset($validitaRow['ValiditaBigliettoId'])) {
                $validita = false;
                break;
            }

            $sql = "SELECT Tariffa FROM RT_CorsaTariffa
                    WHERE FermataPickup = $comunePartenzaId
                    AND FermataDropOff = $comuneDestinazioneId
                    AND TipologiaBigliettoId = $tipologiaBigliettoId
                    AND CorsaId = $corsaId";
            $tariffaInfo = $db->query_first($sql);
            if (!isset($tariffaInfo['Tariffa'])) {
                $validita = false;
                break;
            }
            $totalPrice += floatval($tariffaInfo['Tariffa']) * $qty;

            $percSconto = 0;
            $listinoScontoId = $prenotazione->GetScontoPromozioneAttiva($corsaId, $dataPartenzaSql, 1, $tipologiaBigliettoId);
            $sql = "select Prezzo from RT_ScontisticaBiglietto where ListinoId = " . intval($listinoScontoId) . " and BigliettoId = $tipologiaBigliettoId and Stato = 1 and Cancella = 0";
            $rowsconto = $db->query_first($sql);
            if (!empty($rowsconto['Prezzo'])) {
                $percSconto = floatval($rowsconto['Prezzo']);
                if ($percSconto != 0) {
                    $totalPrice = $totalPrice + $totalPrice * $percSconto / 100;
                    $totalPrice = $prenotazione->arrotonda($totalPrice);
                }
            }
        }

        if (!$validita) {
            continue;
        }

        $sql = "SELECT o.Orario, f.ComuneId, f.FermataId, f.FermataNome
                FROM RT_Orario o
                LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
                WHERE CorsaId = $corsaId
                AND (f.ComuneId = $comunePartenzaId OR f.ComuneId = $comuneDestinazioneId)
                AND o.Stato = 1
                AND o.Cancella = 0
                AND f.Cancella = 0 AND f.Stato = 1
                order by f.FermataPeso asc";
        $arrData = $db->fetch_array($sql);
        if (!isset($arrData[0]) || !isset($arrData[1])) {
            continue;
        }

        $time1 = new DateTime($arrData[0]['Orario']);
        $time2 = new DateTime($arrData[1]['Orario']);
        $timeDifference = $time1->diff($time2);
        $formattedDifference = sprintf('%dh %dm', $timeDifference->h + $timeDifference->days * 24, $timeDifference->i);

        $sql = "SELECT f.FermataId FROM RT_Fermata f
                LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId
                WHERE t.LineaId = $lineaId AND f.Stato = 1 AND f.Cancella = 0
                AND f.IsDaConfermare = 1
                AND (f.ComuneId = $comunePartenzaId OR f.ComuneId = $comuneDestinazioneId)
                GROUP BY f.ComuneId";
        $arrData2 = $db->fetch_array($sql);
        $daConfermare = count($arrData2) > 0;

        $arrayCorseVisualizzate[] = $orarioPartenza;
        $rides[] = array(
            'CorsaId' => $corsaId,
            'CorsaNome' => isset($corsa['CorsaNome']) ? utf8_decode($corsa['CorsaNome']) : null,
            'LineaNome' => isset($corsa['LineaNome']) ? $corsa['LineaNome'] : null,
            'DataPartenza' => $dataCorsa,
            'DataPartenzaFormattata' => $dataFormattata,
            'OrarioPartenza' => date('H:i', strtotime($arrData[0]['Orario'])),
            'OrarioArrivo' => date('H:i', strtotime($arrData[1]['Orario'])),
            'Durata' => $formattedDifference,
            'PostiDisponibili' => $disponibili,
            'PostiOccupati' => $occupati,
            'PrezzoTotale' => floatval($totalPrice),
            'PrezzoTotaleInCents' => intval(round(floatval($totalPrice) * 100)),
            'PickupId' => $arrData[0]['ComuneId'] . '_' . $arrData[0]['FermataId'],
            'Pickup' => $arrData[0]['FermataNome'],
            'DropoffId' => $arrData[1]['ComuneId'] . '_' . $arrData[1]['FermataId'],
            'Dropoff' => $arrData[1]['FermataNome'],
            'DaConfermare' => $daConfermare
        );
    }

    $ridesReturn = array();
    if ($andataRitorno == 1) {
        $arrayCorseVisualizzateR = array();
        $comunePartenzaIdR = $comuneDestinazioneId;
        $comuneDestinazioneIdR = $comunePartenzaId;
        $sWhereR = "RT_Corsa.Stato = 1 AND RT_Linea.TipoTour = $tipoTour AND RT_Linea.LineaId = 3";
        $sWhereR .= " AND (TIMEDIFF(ADDTIME(CONCAT(RT_AppCalendario.AppCalendarioData,' 00:00:00'),RT_Corsa.OrarioPartenza),NOW())) > `RT_Corsa`.OrePrimaStopVendita ";
        $corseR = $corsaObj->getCorseValideBO($dataPartenzaSql, $sOrder, $sWhereR);

        foreach ($corseR as $corsa) {
            $corsaId = intval($corsa['CorsaId']);
            $lineaCorsaId = intval($corsa['LineaId']);
            $dataCorsa = $corsa['AppCalendarioData'];

            $sql = "SELECT * FROM RT_CorsaBlocco WHERE CorsaId = $corsaId AND DataPartenza = '" . addslashes($dataCorsa) . "'";
            $blocco = $db->fetch_array($sql);
            if (count($blocco) > 0) {
                continue;
            }

            $trattaPartenzaR = null;
            $trattaArrivoR = null;
            $visualizzaR = false;

            $sql = "SELECT * FROM RT_PercorsoBreve WHERE ComunePickupId=$comunePartenzaIdR AND ComuneDropOffId=$comuneDestinazioneIdR AND CorsaId=$corsaId";
            $percorsoBreveR = $db->query_first($sql);
            if (!empty($percorsoBreveR['PercorsoBreveId'])) {
                $trattaPartenzaR = intval($percorsoBreveR['TrattaPickupId']);
                $trattaArrivoR = intval($percorsoBreveR['TrattaDropOffId']);
                $visualizzaR = true;
            } else {
                $sql = "SELECT distinct FermataId, TrattaId From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and CorsaId=$corsaId and ComuneId=$comunePartenzaIdR and TrattaStato=1 order by TrattaPeso desc";
                $arrFermatePkR = $db->fetch_array($sql);
                if (isset($arrFermatePkR[0])) {
                    $trattaPartenzaR = intval($arrFermatePkR[0]['TrattaId']);
                }

                $sql = "SELECT distinct FermataIdDrop, TrattaId From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and IsDropOff=1 and CorsaId=$corsaId and ComuneId=$comuneDestinazioneIdR and TrattaStato=1 order by TrattaPeso asc";
                $arrFermateDoR = $db->fetch_array($sql);
                if (isset($arrFermateDoR[0])) {
                    $trattaArrivoR = intval($arrFermateDoR[0]['TrattaId']);
                }

                if (!empty($trattaPartenzaR) && !empty($trattaArrivoR)) {
                    $visualizzaR = true;
                }
            }

            if (!$visualizzaR) {
                continue;
            }

            if (isset($user->GestoreId) && intval($user->GestoreId) > 0) {
                $gestoreId = intval($user->GestoreId);
                $sql = "SELECT * FROM GestoreConvenzione where GestoreId = $gestoreId and LineaId = $lineaCorsaId and Now()<=ValidaAl and Now()>=ValidaDal";
                $convenzioneR = $db->fetch_array($sql);
                if (count($convenzioneR) == 0) {
                    continue;
                }
            }

            //$sql = "SELECT * FROM RT_CorsaTariffa where CorsaId = $corsaId AND FermataPickup = $comunePartenzaIdR AND FermataDropOff = $comuneDestinazioneIdR AND TipologiaBigliettoId <> 11 AND Tariffa > 0";
            //$tariffeBaseR = $db->fetch_array($sql);
            //if (count($tariffeBaseR) == 0) {
            //   echo "NO";
            //    continue;
            //}

            $disponibiliR = intval(getPostiDisponibili($lineaCorsaId, $corsaId, $db, $comunePartenzaIdR, $comuneDestinazioneIdR, $dataCorsa));
            $occupatiR = availableRidesGetOccupatiApprox($db, $corsaId, $dataCorsa, $disponibiliR);
            if($tipoTour == 1) {
                if($occupatiR > 0) {
                    $disponibiliR = 0;
                } else {
                    $disponibiliR = 1;
                }
            }
            $orarioPartenzaR = isset($corsa['OrarioPartenza']) ? $corsa['OrarioPartenza'] : '';
            $dataFormattataR = date('d/m/Y', strtotime($dataCorsa));

            $dataOraSpecR = DateTime::createFromFormat('d/m/Y H:i:s', $dataFormattataR . ' ' . $orarioPartenzaR);
            if (!$dataOraSpecR || $now >= $dataOraSpecR) {
                continue;
            }

            if ($dataFormattataR != $dataFiltroA) {
                continue;
            }

            if ($tipoTour == 0) {
                if ($disponibiliR < $postiTotali) {
                    continue;
                }
            } else {
                if ($occupatiR != 0 || $disponibiliR < $postiTotali) {
                    continue;
                }
            }

            if (in_array($orarioPartenzaR, $arrayCorseVisualizzateR)) {
                continue;
            }

            $sql = "SELECT * FROM RT_Corsa WHERE CorsaId = $corsaId AND Stato = 1 AND Cancella = 0";
            
            $corsaTempR = $db->query_first($sql);
            if (isset($corsaTempR['FlottaDefaultId']) && intval($corsaTempR['FlottaDefaultId']) > 0) {
                $dataCorsaSpecR = $dataOraSpecR->format('Y-m-d');
                $flottaIdR = intval($corsaTempR['FlottaDefaultId']);
                $orarioCorsaR = addslashes($corsaTempR['OrarioPartenza']);
                $orarioArrivoCorsaR = addslashes($corsaTempR['OrarioArrivo']);
                $sql = "SELECT pp.PrenotazionePercorsoId FROM RT_PrenotazionePercorso pp
                        LEFT JOIN RT_Corsa c ON c.CorsaId = pp.CorsaId
                        WHERE pp.CorsaDataPartenza = '$dataCorsaSpecR' AND c.FlottaDefaultId = $flottaIdR
                        AND pp.CorsaOrarioPartenza >= '$orarioCorsaR' AND pp.CorsaOrarioPartenza < '$orarioArrivoCorsaR'
                        AND pp.PrenotazioneStato NOT IN (6,4) AND pp.CorsaId <> '$corsaId'";
                $checkFlottaR = $db->query_first($sql);
                if (isset($checkFlottaR['PrenotazionePercorsoId']) && intval($checkFlottaR['PrenotazionePercorsoId']) > 0) {
                    continue;
                }
            }

            $totalPriceR = 0;
            $validitaR = true;
            foreach ($biglietti as $tipologiaBigliettoId => $qty) {
                $tipologiaBigliettoId = intval($tipologiaBigliettoId);
                $qty = intval($qty);
                if ($qty <= 0) {
                    continue;
                }

                $sql = "SELECT v.ValiditaBigliettoId
                        FROM RT_ValiditaBiglietto v
                        LEFT JOIN RT_ValiditaBigliettoDettaglio b on b.ValiditaBigliettoId = v.ValiditaBigliettoId
                        WHERE CorsaId = $corsaId
                        AND '$dataPartenzaSql' >= Dal
                        AND '$dataPartenzaSql' <= Al
                        AND v.Stato = 1 AND b.Stato = 1 AND v.Cancella = 0 AND b.Cancella = 0
                        AND BigliettoId = $tipologiaBigliettoId";
                $validitaRowR = $db->query_first($sql);
                if (!isset($validitaRowR['ValiditaBigliettoId'])) {
                    $validitaR = false;
                    break;
                }

                /*$sql = "SELECT Tariffa FROM RT_CorsaTariffa
                        WHERE FermataPickup = $comunePartenzaIdR
                        AND FermataDropOff = $comuneDestinazioneIdR
                        AND TipologiaBigliettoId = $tipologiaBigliettoId
                        AND CorsaId = $corsaId";
                $tariffaInfoR = $db->query_first($sql);
                if (!isset($tariffaInfoR['Tariffa'])) {
                    $validitaR = false;
                    break;
                }
                $totalPriceR += floatval($tariffaInfoR['Tariffa']) * $qty;

                $percScontoR = 0;
                $listinoScontoIdR = $prenotazione->GetScontoPromozioneAttiva($corsaId, $dataPartenzaSql, 1, $tipologiaBigliettoId);
                $sql = "select Prezzo from RT_ScontisticaBiglietto where ListinoId = " . intval($listinoScontoIdR) . " and BigliettoId = $tipologiaBigliettoId and Stato = 1 and Cancella = 0";
                $rowscontoR = $db->query_first($sql);
                if (!empty($rowscontoR['Prezzo'])) {
                    $percScontoR = floatval($rowscontoR['Prezzo']);
                    if ($percScontoR != 0) {
                        $totalPriceR = $totalPriceR + $totalPriceR * $percScontoR / 100;
                        $totalPriceR = $prenotazione->arrotonda($totalPriceR);
                    }
                }
                    */
            }

            if (!$validitaR) {
                continue;
            }

            $sql = "SELECT o.Orario, f.ComuneId, f.FermataId, f.FermataNome
                    FROM RT_Orario o
                    LEFT JOIN RT_Fermata f on f.FermataId = o.FermataId
                    WHERE CorsaId = $corsaId
                    AND (f.ComuneId = $comunePartenzaIdR OR f.ComuneId = $comuneDestinazioneIdR)
                    AND o.Stato = 1
                    AND o.Cancella = 0
                    AND f.Cancella = 0 AND f.Stato = 1
                    order by f.FermataPeso asc";
            $arrDataR = $db->fetch_array($sql);
            if (!isset($arrDataR[0]) || !isset($arrDataR[1])) {
                continue;
            }

            $time1R = new DateTime($arrDataR[0]['Orario']);
            $time2R = new DateTime($arrDataR[1]['Orario']);
            $timeDifferenceR = $time1R->diff($time2R);
            $formattedDifferenceR = sprintf('%dh %dm', $timeDifferenceR->h + $timeDifferenceR->days * 24, $timeDifferenceR->i);

            $sql = "SELECT f.FermataId FROM RT_Fermata f
                    LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId
                    WHERE t.LineaId = $lineaId AND f.Stato = 1 AND f.Cancella = 0
                    AND f.IsDaConfermare = 1
                    AND (f.ComuneId = $comunePartenzaIdR OR f.ComuneId = $comuneDestinazioneIdR)
                    GROUP BY f.ComuneId";
            $arrData2R = $db->fetch_array($sql);
            $daConfermareR = count($arrData2R) > 0;

            $arrayCorseVisualizzateR[] = $orarioPartenzaR;
            $ridesReturn[] = array(
                'CorsaId' => $corsaId,
                'CorsaNome' => isset($corsa['CorsaNome']) ? utf8_decode($corsa['CorsaNome']) : null,
                'LineaNome' => isset($corsa['LineaNome']) ? $corsa['LineaNome'] : null,
                'DataPartenza' => $dataCorsa,
                'DataPartenzaFormattata' => $dataFormattataR,
                'OrarioPartenza' => date('H:i', strtotime($arrDataR[0]['Orario'])),
                'OrarioArrivo' => date('H:i', strtotime($arrDataR[1]['Orario'])),
                'Durata' => $formattedDifferenceR,
                'PostiDisponibili' => $disponibiliR,
                'PostiOccupati' => $occupatiR,
                'PrezzoTotale' => floatval($totalPriceR),
                'PrezzoTotaleInCents' => intval(round(floatval($totalPriceR) * 100)),
                'PickupId' => $arrDataR[0]['ComuneId'] . '_' . $arrDataR[0]['FermataId'],
                'Pickup' => $arrDataR[0]['FermataNome'],
                'DropoffId' => $arrDataR[1]['ComuneId'] . '_' . $arrDataR[1]['FermataId'],
                'Dropoff' => $arrDataR[1]['FermataNome'],
                'DaConfermare' => $daConfermareR
            );
        }
    }

    $result = array(
        'currency' => 'EUR',
        'dataPartenza' => $dataPartenzaSql,
        'postiTotaliRichiesti' => $postiTotali,
        'rides' => $rides
    );
    if ($andataRitorno == 1) {
        $result['ridesReturn'] = $ridesReturn;
    }
    return $result;
}

function availableRidesNormalizeDate($dataPartenza) {
    if (!is_string($dataPartenza) || trim($dataPartenza) == '') {
        return null;
    }
    $dataPartenza = trim($dataPartenza);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataPartenza)) {
        return $dataPartenza;
    }
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataPartenza)) {
        $parts = explode('/', $dataPartenza);
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
    return null;
}

function availableRidesNormalizeBiglietti($bigliettiInput) {
    $normalized = array();

    if (is_object($bigliettiInput)) {
        $bigliettiInput = (array)$bigliettiInput;
    }

    if (is_array($bigliettiInput)) {
        foreach ($bigliettiInput as $k => $v) {
            if (is_object($v)) {
                $v = (array)$v;
            }

            if (is_array($v) && isset($v['tipologiaBigliettoId'])) {
                $id = intval($v['tipologiaBigliettoId']);
                $qty = isset($v['qty']) ? intval($v['qty']) : (isset($v['quantita']) ? intval($v['quantita']) : 0);
            } else if (is_array($v) && isset($v['id'])) {
                $id = intval($v['id']);
                $qty = isset($v['qty']) ? intval($v['qty']) : 0;
            } else if (is_numeric($k)) {
                $id = intval($k);
                $qty = intval($v);
            } else {
                $id = intval($k);
                $qty = intval($v);
            }

            if ($id > 0 && $qty > 0) {
                if (!isset($normalized[$id])) {
                    $normalized[$id] = 0;
                }
                $normalized[$id] += $qty;
            }
        }
    }

    return $normalized;
}

function availableRidesCalcPostiTotali($db, $tipoTour, $biglietti) {
    if ($tipoTour == 0) {
        $tot = 0;
        foreach ($biglietti as $qty) {
            $tot += intval($qty);
        }
        return $tot;
    }

    $maxPax = 0;
    foreach ($biglietti as $tipologiaBigliettoId => $qty) {
        if (intval($qty) <= 0) {
            continue;
        }
        $sql = "SELECT TipologiaBiglietto FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = " . intval($tipologiaBigliettoId);
        $temp = $db->query_first($sql);
        if (isset($temp['TipologiaBiglietto'])) {
            $pax = availableRidesExtractPaxFromTicketDescription($temp['TipologiaBiglietto']);
            if ($pax > $maxPax) {
                $maxPax = $pax;
            }
        }
    }

    if ($maxPax > 0) {
        return $maxPax;
    }

    $tot = 0;
    foreach ($biglietti as $qty) {
        $tot += intval($qty);
    }
    return $tot;
}

function availableRidesExtractPaxFromTicketDescription($desc) {
    $desc = strtolower(trim($desc));
    if ($desc == '') {
        return 0;
    }

    if (preg_match('/(\d+)\s*-\s*(\d+)\s*pax/', $desc, $m)) {
        return intval($m[2]);
    }
    if (preg_match('/fino\s*a\s*(\d+)\s*pax/', $desc, $m)) {
        return intval($m[1]);
    }
    if (preg_match('/(\d+)\s*pax/', $desc, $m)) {
        return intval($m[1]);
    }

    return 0;
}

function availableRidesGetOccupatiApprox($db, $corsaId, $dataPartenza, $disponibili) {
    $corsaId = intval($corsaId);
    $dataPartenza = addslashes($dataPartenza);
    $disponibili = intval($disponibili);

    $sql = "SELECT b.TotalePosti
            FROM RT_TipologiaBus b
            LEFT JOIN RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
            WHERE c.CorsaId = $corsaId";
    $row = $db->query_first($sql);
    $postiDefault = isset($row['TotalePosti']) ? intval($row['TotalePosti']) : 0;

    $sql = "SELECT IFNULL((SELECT SUM(c1.NumeroPax)
            FROM RT_CorsaPax c1
            WHERE c1.Cancella = 0 AND c1.CorsaId = $corsaId AND c1.DataPartenza = '$dataPartenza' AND c1.OdcIdRef = 1
            GROUP BY c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
    $add = $db->query_first($sql);
    $postiAggiunti = isset($add['PostiAggiunti']) ? intval($add['PostiAggiunti']) : 0;

    $totali = $postiDefault + $postiAggiunti;
    $occupati = $totali - $disponibili;
    if ($occupati < 0) {
        $occupati = 0;
    }
    return $occupati;
}

function checkToken($token) {
    $db = new Database();
    $db->connect();
    $sql = "SELECT OperatoreId FROM ApiToken WHERE Token = '$token'";
    $result = $db->query_first($sql);
    if(isset($result['OperatoreId'])) {
        return $result['OperatoreId'];
    } else {
        return -1;
    }
}

// --- FUNZIONE PER get-porti ---
function getPorti($lineaId) {
    global $db;

    $db = new Database();
    $conn = $db->connect();

    $lineaId = intval($lineaId);
    if ($lineaId <= 0) {
        return array('portiPartenza' => array(), 'portiArrivo' => array());
    }

    // Query Porto Partenza
    $sqlPortoPartenza = "SELECT f.FermataId, f.FermataNome, c.Comune, c.ComuneId, o.CorsaId, MIN(o.Orario) AS Orario, o.GiorniAggiuntivi, f.TrattaId
        FROM RT_Fermata f
        LEFT JOIN RT_Orario o ON f.FermataId = o.FermataId
        LEFT JOIN Comune c ON c.ComuneId = f.ComuneId
        LEFT JOIN Provincia p ON c.provincia = p.ProvinciaId
        LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
        WHERE t.LineaId = $lineaId
            AND o.Orario IS NOT NULL AND o.Orario <> ''
            AND o.Stato = 1 AND o.Cancella = 0
            AND f.Stato = 1 AND f.Cancella = 0
            AND f.IsPickup = 1
            AND c.ComuneId IS NOT NULL
            AND f.WebSelling = 1
        GROUP BY c.ComuneId
        ORDER BY c.Comune ASC";
    $arrPortoPartenza = $db->fetch_array($sqlPortoPartenza);

    // Query Porto Destinazione
    $sqlPortoDestinazione = "SELECT f.FermataId, f.FermataNome, c.Comune, c.ComuneId, o.CorsaId, MIN(o.Orario) AS Orario, o.GiorniAggiuntivi, f.TrattaId
        FROM RT_Fermata f
        LEFT JOIN RT_Orario o ON f.FermataId = o.FermataId
        LEFT JOIN Comune c ON c.ComuneId = f.ComuneId
        LEFT JOIN Provincia p ON c.provincia = p.ProvinciaId
        LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
        WHERE t.LineaId = $lineaId
            AND o.Orario IS NOT NULL AND o.Orario <> ''
            AND o.Stato = 1 AND o.Cancella = 0
            AND f.Stato = 1 AND f.Cancella = 0
            AND f.IsDropOff = 1
            AND c.ComuneId IS NOT NULL
            AND f.WebSelling = 1
        GROUP BY c.ComuneId
        ORDER BY c.Comune ASC";
    $arrPortoDestinazione = $db->fetch_array($sqlPortoDestinazione);

    return array(
        'portiPartenza' => $arrPortoPartenza,
        'portiArrivo' => $arrPortoDestinazione
    );
}
