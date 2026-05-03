<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 


$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

include_once($classespath_."class.Gestore.php");


$ModuloId = 28;


function tour($periodo, $daLibera = null, $aLbera = null, $barca = null, $tipoNoleggio = null, $areaLavoro = null) {
    global $user;

    $db = new Database();
    $db->connect();
    
    // Determine if we need Flotta JOINs
    $needFlottaJoin = (isset($barca) && $barca != '') || (isset($tipoNoleggio) && $tipoNoleggio != '') || (isset($areaLavoro) && $areaLavoro != '');
    
    // Build SELECT fields - transform correlated subquery into aggregated JOIN
    $selectFields = "c.`CorsaId`,
                        appcal.`AppCalendarioData`,
                        COUNT(DISTINCT CASE 
                            WHEN pp.CorsaId IS NOT NULL 
                            AND p_main.`Cancella` = 0
                            AND pp.`Cancella` = 0
                            AND pp.`Stato` = 1
                            AND aps.`OccupaPosti` = 1
                            AND pd.`Escludi` <> 1
                            AND pd.`Rimborso` <> 1
                            AND (tb.`OccupaPosto` = 1 OR tb.TipologiaBigliettoId IS NULL)
                            THEN CONCAT(pp.CorsaDataPartenza, '-', pp.CorsaId, '-', IFNULL(pp.OdcIdRef, ''))
                        END) AS PostiRealmentePrenotati";
    
    // Add Flotta fields only if needed
    if ($needFlottaJoin) {
        $selectFields .= ",
                        RT_Flotta.FlottaId AS Barca,
                        RT_Flotta.TipoNoleggio AS TipoNoleggio,
                        RT_Flotta.AreaLavoro AS AreaLavoro";
    }
    
    // Build FROM clause with LEFT JOIN instead of correlated subquery
    $fromClause = "`RT_Corsa` c
                        JOIN `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
                        JOIN `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
                        JOIN `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
                        LEFT JOIN `RT_PrenotazionePercorso` pp ON (pp.`CorsaId` = c.CorsaId AND pp.`CorsaDataPartenza` = appcal.AppCalendarioData)
                        LEFT JOIN `RT_Prenotazione` p_main ON (pp.`PrenotazioneId` = p_main.`PrenotazioneId`)
                        LEFT JOIN `RT_PrenotazioneDettaglio` pd ON (pp.`PrenotazioneId` = pd.`PrenotazioneId` 
                            AND pd.`ComunePartenza` = pp.`ComuneSalita`)
                        LEFT JOIN `RT_AppPrenotazioneStato` aps ON (pp.`PrenotazioneStato` = aps.`PrenotazioneStatoId`)
                        LEFT JOIN `RT_PrenotazioneNumero` pn ON (pd.`PrenotazioneNumero` = pn.`PrenotazioneNumeroId`)
                        LEFT JOIN `RT_TipologiaBiglietto` tb ON (tb.`TipologiaBigliettoId` = pn.`TipologiaBigliettoId`)";
    
    // Add Flotta JOINs only if needed
    if ($needFlottaJoin) {
        $fromClause .= "
                        JOIN `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
                        LEFT JOIN RT_Flotta ON RT_TipologiaBus.TipologiaBusId = RT_Flotta.TipologiaBusId";
    }
    
    $fromClause .= "
                        GROUP BY c.CorsaId, appcal.AppCalendarioData" . ($needFlottaJoin ? ", RT_Flotta.FlottaId, RT_Flotta.TipoNoleggio, RT_Flotta.AreaLavoro" : "");
    
    // Build period conditions for CASE statements
    $currentPeriodCondition = "";
    $previousPeriodCondition = "";
    
    switch ($periodo) {
        case 'settimana':
            $currentPeriodCondition = "YEARWEEK(AppCalendarioData, 1) = YEARWEEK(CURDATE(), 1)";
            $previousPeriodCondition = "YEARWEEK(AppCalendarioData, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), 1)";
            break;
        case 'mese':
            $currentPeriodCondition = "YEAR(AppCalendarioData) = YEAR(CURDATE()) AND MONTH(AppCalendarioData) = MONTH(CURDATE())";
            $previousPeriodCondition = "YEAR(AppCalendarioData) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR)) AND MONTH(AppCalendarioData) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
            break;
        case 'anno':
            $currentPeriodCondition = "YEAR(AppCalendarioData) = YEAR(CURDATE())";
            $previousPeriodCondition = "YEAR(AppCalendarioData) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
            break;
        case 'libera':
            $currentPeriodCondition = "DATE(AppCalendarioData) >= '$daLibera' AND DATE(AppCalendarioData) <= '$aLbera'";
            $previousPeriodCondition = "DATE(AppCalendarioData) >= DATE_SUB('$daLibera', INTERVAL 1 YEAR) AND DATE(AppCalendarioData) <= DATE_SUB('$aLbera', INTERVAL 1 YEAR)";
            break;
        default:
            // For the default 'giorno'
            $currentPeriodCondition = "DATE(AppCalendarioData) = CURDATE()";
            $previousPeriodCondition = "DATE(AppCalendarioData) = DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
    }
    
    // Build additional filters for Flotta
    $flottaConditions = "";
    if(isset($barca) && $barca != '') {
        $flottaConditions .= " AND Barca = ".$barca;
    }
    if(isset($tipoNoleggio) && $tipoNoleggio != '') {
        $flottaConditions .= " AND TipoNoleggio = ".$tipoNoleggio;
    }
    if(isset($areaLavoro) && $areaLavoro != '') {
        $flottaConditions .= " AND AreaLavoro = ".$areaLavoro;
    }
    
    // Single unified query with CASE WHEN for both periods
    $unifiedSql = "SELECT 
                    SUM(CASE WHEN (" . $currentPeriodCondition . ") THEN 1 ELSE 0 END) as currentTotal,
                    SUM(CASE WHEN (" . $previousPeriodCondition . ") THEN 1 ELSE 0 END) as previousTotal
                   FROM (
                    SELECT " . $selectFields . "
                    FROM " . $fromClause . "
                   ) AS tabella 
                   WHERE PostiRealmentePrenotati > 0 
                   AND ((" . $currentPeriodCondition . ") OR (" . $previousPeriodCondition . "))" 
                   . $flottaConditions;

    // Execute single query for both periods
    $row = $db->query_first($unifiedSql);
    $currentTotal = isset($row['currentTotal']) ? $row['currentTotal'] : 0;
    $previousTotal = isset($row['previousTotal']) ? $row['previousTotal'] : 0;

    $db->close();

    // Return both totals
    echo json_encode(array(
        'currentTotal' => $currentTotal,
        'previousTotal' => $previousTotal
    ));
	exit();
}


function entrate($periodo, $daLibera = null, $aLbera = null, $barca = null, $tipoNoleggio = null, $areaLavoro = null) {
    global $user;

    $db = new Database();
    $db->connect();
    
    $gestore = new Gestore();
    $gestore->conn = $db;
    $gestorefigli = $gestore->getGestoreFigli($user->GestoreId);
    $InGestoreFigli = implode(",", $gestorefigli);
    
    $baseSql = "SELECT
                    sum(RT_PrenotazioneTitolo.ImportoVenduto) as tot
                FROM
                    RT_PrenotazioneTitoloProvvigione
                INNER JOIN RT_PrenotazioneTitolo ON RT_PrenotazioneTitoloProvvigione.PrenotazioneTitoloId = RT_PrenotazioneTitolo.PrenotazioneTitoloId
                INNER JOIN Gestore ON RT_PrenotazioneTitoloProvvigione.GestoreId = Gestore.GestoreId
                INNER JOIN RT_PrenotazionePercorso ON RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
                LEFT JOIN RT_Corsa ON RT_PrenotazionePercorso.CorsaId = RT_Corsa.CorsaId
                LEFT JOIN `RT_TipologiaBus` ON (RT_Corsa.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
                LEFT JOIN RT_Flotta ON RT_TipologiaBus.TipologiaBusId = RT_Flotta.TipologiaBusId
                WHERE RT_PrenotazioneTitoloProvvigione.GestoreId = RT_PrenotazioneTitoloProvvigione.GestoreIdRef 
                    AND RT_PrenotazioneTitoloProvvigione.GestoreId IN ($InGestoreFigli) 
                    AND RT_PrenotazioneTitolo.Stato = 1 
                    AND RT_PrenotazioneTitolo.Cancella = 0";

    // Current period query
    $currentPeriodSql = $baseSql;
    // Previous year period query
    $previousYearSql = $baseSql;

    switch ($periodo) {
        case 'settimana':
            $currentPeriodSql .= " AND YEARWEEK(RT_PrenotazioneTitolo.DataIns, 1) = YEARWEEK(CURDATE(), 1)";
            $previousYearSql .= " AND YEARWEEK(RT_PrenotazioneTitolo.DataIns, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), 1)";
            break;
        case 'mese':
            $currentPeriodSql .= " AND YEAR(RT_PrenotazioneTitolo.DataIns) = YEAR(CURDATE()) AND MONTH(RT_PrenotazioneTitolo.DataIns) = MONTH(CURDATE())";
            $previousYearSql .= " AND YEAR(RT_PrenotazioneTitolo.DataIns) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR)) AND MONTH(RT_PrenotazioneTitolo.DataIns) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
            break;
        case 'anno':
            $currentPeriodSql .= " AND YEAR(RT_PrenotazioneTitolo.DataIns) = YEAR(CURDATE())";
            $previousYearSql .= " AND YEAR(RT_PrenotazioneTitolo.DataIns) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
            break;
        case 'libera':
            $currentPeriodSql .= " AND DATE(RT_PrenotazioneTitolo.DataIns) >= '$daLibera' AND DATE(RT_PrenotazioneTitolo.DataIns) <= '$aLbera'";
            $previousYearSql .= " AND DATE(RT_PrenotazioneTitolo.DataIns) >= DATE_SUB('$daLibera', INTERVAL 1 YEAR) AND DATE(RT_PrenotazioneTitolo.DataIns) <= DATE_SUB('$aLbera', INTERVAL 1 YEAR)";
            break;
        default:
            // For the default 'giorno'
            $currentPeriodSql .= " AND DATE(RT_PrenotazioneTitolo.DataIns) = CURDATE()";
            $previousYearSql .= " AND DATE(RT_PrenotazioneTitolo.DataIns) = DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
    }

    if (isset($barca) && $barca != '') {
        $currentPeriodSql .= " AND RT_Flotta.FlottaId = ".$barca;
        $previousYearSql .= " AND RT_Flotta.FlottaId = ".$barca;
    }
    if (isset($tipoNoleggio) && $tipoNoleggio != '') {
        $currentPeriodSql .= " AND RT_Flotta.TipoNoleggio = ".$tipoNoleggio;
        $previousYearSql .= " AND RT_Flotta.TipoNoleggio = ".$tipoNoleggio;
    }
    if (isset($areaLavoro) && $areaLavoro != '') {
        $currentPeriodSql .= " AND RT_Flotta.AreaLavoro = ".$areaLavoro;
        $previousYearSql .= " AND RT_Flotta.AreaLavoro = ".$areaLavoro;
    }

    // Execute current period query
    $currentRow = $db->query_first($currentPeriodSql);
    $currentTotal = isset($currentRow['tot']) ? $currentRow['tot'] : "0";

    // Execute previous year period query
    $previousRow = $db->query_first($previousYearSql);
    $previousTotal = isset($previousRow['tot']) ? $previousRow['tot'] : "0";
 
    $db->close();

    // Return both totals
    echo json_encode(array(
        'currentTotal' => $currentTotal,
        'previousTotal' => $previousTotal
    ));
	exit();
}



function prenotazioni($periodo, $daLibera = null, $aLbera = null, $barca = null, $tipoNoleggio = null, $areaLavoro = null) {
	global $user;

	$db = new Database();
	$db->connect();
	
	$gestore=new Gestore();
	$gestore->conn = $db;
	$gestorefigli = $gestore->getGestoreFigli($user->GestoreId);

	$InGestoreFigli = implode(",", $gestorefigli);
	
	$baseSql = "SELECT count(*) as tot FROM RT_Prenotazione 
			INNER JOIN RT_PrenotazionePercorso ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
			left JOIN RT_Corsa ON RT_PrenotazionePercorso.CorsaId = RT_Corsa.CorsaId
			left join `RT_TipologiaBus` ON (RT_Corsa.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
			left join RT_Flotta ON RT_TipologiaBus.TipologiaBusId = RT_Flotta.TipologiaBusId
			WHERE RT_Prenotazione.Stato = 1 AND (RT_Prenotazione.PrenotazioneStato <> 6  AND RT_Prenotazione.PrenotazioneStato <> 4)
				AND RT_Prenotazione.Cancella = 0
				AND RT_Prenotazione.GestoreIdRef IN ($InGestoreFigli)
				AND RT_PrenotazionePercorso.Direzione = 'A' ";
				
	// Current period query
    $currentPeriodSql = $baseSql;
    // Previous year period query
    $previousYearSql = $baseSql;
	
	switch ($periodo) {
		case 'settimana':
			$currentPeriodSql .= " AND YEARWEEK(RT_Prenotazione.DataIns, 1) = YEARWEEK(CURDATE(), 1)";
			$previousYearSql .= " AND YEARWEEK(RT_Prenotazione.DataIns, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), 1)";
			break;
		case 'mese':
			$currentPeriodSql .= " AND YEAR(RT_Prenotazione.DataIns) = YEAR(CURDATE()) AND MONTH(RT_Prenotazione.DataIns) = MONTH(CURDATE())";
			$previousYearSql .= " AND YEAR(RT_Prenotazione.DataIns) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR)) AND MONTH(RT_Prenotazione.DataIns) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
			break;
		case 'anno':
			$currentPeriodSql .= " AND YEAR(RT_Prenotazione.DataIns) = YEAR(CURDATE())";
			$previousYearSql .= " AND YEAR(RT_Prenotazione.DataIns) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
			break;
		case 'libera':
			$currentPeriodSql .= " AND DATE(RT_Prenotazione.DataIns) >= '$daLibera' AND DATE(RT_Prenotazione.DataIns) <= '$aLbera'";
			$previousYearSql .= " AND DATE(RT_Prenotazione.DataIns) >= DATE_SUB('$daLibera', INTERVAL 1 YEAR) AND DATE(RT_Prenotazione.DataIns) <= DATE_SUB('$aLbera', INTERVAL 1 YEAR)";
			break;
		default:
			// Per il valore predefinito 'giorno'
			$currentPeriodSql .= " AND DATE(RT_Prenotazione.DataIns) = CURDATE()";
            $previousYearSql .= " AND DATE(RT_Prenotazione.DataIns) = DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";

			break;
	}

	if (isset($barca) && $barca != '') {
        $currentPeriodSql .= " AND RT_Flotta.FlottaId = ".$barca;
        $previousYearSql .= " AND RT_Flotta.FlottaId = ".$barca;
    }
    if (isset($tipoNoleggio) && $tipoNoleggio != '') {
        $currentPeriodSql .= " AND RT_Flotta.TipoNoleggio = ".$tipoNoleggio;
        $previousYearSql .= " AND RT_Flotta.TipoNoleggio = ".$tipoNoleggio;
    }
    if (isset($areaLavoro) && $areaLavoro != '') {
        $currentPeriodSql .= " AND RT_Flotta.AreaLavoro = ".$areaLavoro;
        $previousYearSql .= " AND RT_Flotta.AreaLavoro = ".$areaLavoro;
    }

    // Execute current period query
    $currentRow = $db->query_first($currentPeriodSql);
    $currentTotal = isset($currentRow['tot']) ? $currentRow['tot'] : "0";

    // Execute previous year period query
    $previousRow = $db->query_first($previousYearSql);
    $previousTotal = isset($previousRow['tot']) ? $previousRow['tot'] : "0";
 
    $db->close();

    // Return both totals
    echo json_encode(array(
        'currentTotal' => $currentTotal,
        'previousTotal' => $previousTotal
    ));
	exit();
   
}

function getMonthsBetweenDates($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $end->modify('first day of next month'); // Include the end month

    $interval = new DateInterval('P1M'); // Period of 1 month
    $period = new DatePeriod($start, $interval, $end);

    $months = array();
    foreach ($period as $dt) {
        $months[] = $dt->format('m-Y'); // Format as month-year
    }

    return $months;
}

function prenotazioniGrafo($periodo, $daLibera = null, $aLibera = null, $barca = null, $tipoNoleggio = null, $areaLavoro = null) {
    global $user;

    $db = new Database();
    $db->connect();

    $gestore = new Gestore();
    $gestore->conn = $db;
    $gestorefigli = $gestore->getGestoreFigli($user->GestoreId);
    $InGestoreFigli = implode(",", $gestorefigli);

    $results = array(); // Array per i risultati

    switch ($periodo) {
        case 'settimana':
            $weeks = array(); // Array per le settimane del mese corrente

            $currentYear = date('Y');
            $currentMonth = date('m');
            $lastDayOfMonth = date('t', strtotime(date('Y-m-d')));
            
            // Calcola le settimane del mese corrente
            for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                $weekNumber = date('W', strtotime("$currentYear-$currentMonth-$day"));
                if (!in_array($weekNumber, $weeks)) {
                    $weeks[] = $weekNumber;
                }
            }

            // Calcola il totale delle prenotazioni per ciascuna settimana
            foreach ($weeks as $week) {
                $sql = "SELECT count(*) as tot FROM RT_Prenotazione 
						INNER JOIN RT_PrenotazionePercorso ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						left JOIN RT_Corsa ON RT_PrenotazionePercorso.CorsaId = RT_Corsa.CorsaId
						left join `RT_TipologiaBus` ON (RT_Corsa.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
						left join RT_Flotta ON RT_TipologiaBus.TipologiaBusId = RT_Flotta.TipologiaBusId
                        WHERE RT_Prenotazione.Stato = 1 AND (RT_Prenotazione.PrenotazioneStato <> 6  AND RT_Prenotazione.PrenotazioneStato <> 4)
                            AND RT_Prenotazione.Cancella = 0
                            AND RT_Prenotazione.GestoreIdRef IN ($InGestoreFigli)
                            AND YEARWEEK(RT_Prenotazione.DataIns, 1) = $currentYear$week";
				if(isset($barca) && $barca != '') {
					$sql .= " AND RT_Flotta.FlottaId = ".$barca;
				}
				if(isset($tipoNoleggio) && $tipoNoleggio != '') {
					$sql .= " AND RT_Flotta.TipoNoleggio = ".$tipoNoleggio;
				}
				if(isset($areaLavoro) && $areaLavoro != '') {
					$sql .= " AND RT_Flotta.AreaLavoro = ".$areaLavoro;
				}
                $row = $db->query_first($sql);
                $results[] = array('week' => $week, 'total' => isset($row['tot']) ? $row['tot'] : 0, 'month' => $currentMonth, 'year' => $currentYear);
            }
            break;

        case 'mese':
            $currentYear = date('Y');

            // Calcola il totale delle prenotazioni per tutti i mesi dell'anno corrente
            for ($month = 1; $month <= 12; $month++) {
                $sql = "SELECT count(*) as tot FROM RT_Prenotazione 
						INNER JOIN RT_PrenotazionePercorso ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						left JOIN RT_Corsa ON RT_PrenotazionePercorso.CorsaId = RT_Corsa.CorsaId
						left join `RT_TipologiaBus` ON (RT_Corsa.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
						left join RT_Flotta ON RT_TipologiaBus.TipologiaBusId = RT_Flotta.TipologiaBusId
                        WHERE RT_Prenotazione.Stato = 1 AND (RT_Prenotazione.PrenotazioneStato <> 6  AND RT_Prenotazione.PrenotazioneStato <> 4)
                            AND RT_Prenotazione.Cancella = 0
                            AND RT_Prenotazione.GestoreIdRef IN ($InGestoreFigli)
                            AND YEAR(RT_Prenotazione.DataIns) = $currentYear
                            AND MONTH(RT_Prenotazione.DataIns) = $month";
				if(isset($barca) && $barca != '') {
					$sql .= " AND RT_Flotta.FlottaId = ".$barca;
				}
				if(isset($tipoNoleggio) && $tipoNoleggio != '') {
					$sql .= " AND RT_Flotta.TipoNoleggio = ".$tipoNoleggio;
				}
				if(isset($areaLavoro) && $areaLavoro != '') {
					$sql .= " AND RT_Flotta.AreaLavoro = ".$areaLavoro;
				}
                $row = $db->query_first($sql);
                $results[] = array('month' => $month, 'total' => isset($row['tot']) ? $row['tot'] : 0, 'year' => $currentYear);
            }
            break;
			
		case 'libera':
            $arrayTemp = getMonthsBetweenDates($daLibera, $aLibera);

            // Calcola il totale delle prenotazioni per tutti i mesi nell'intervallo indicato
            foreach ($arrayTemp as $monthYear) {
				list($month, $currentYear) = explode('-', $monthYear);

				$sql = "SELECT count(*) as tot FROM RT_Prenotazione 
						INNER JOIN RT_PrenotazionePercorso ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						left JOIN RT_Corsa ON RT_PrenotazionePercorso.CorsaId = RT_Corsa.CorsaId
						left join `RT_TipologiaBus` ON (RT_Corsa.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
						left join RT_Flotta ON RT_TipologiaBus.TipologiaBusId = RT_Flotta.TipologiaBusId
                        WHERE RT_Prenotazione.Stato = 1 AND (RT_Prenotazione.PrenotazioneStato <> 6  AND RT_Prenotazione.PrenotazioneStato <> 4)
							AND RT_Prenotazione.Cancella = 0
							AND RT_Prenotazione.GestoreIdRef IN ($InGestoreFigli)
							AND YEAR(RT_Prenotazione.DataIns) = $currentYear
							AND MONTH(RT_Prenotazione.DataIns) = $month
							AND DATE(RT_Prenotazione.DataIns) >= '$daLibera'
							AND DATE(RT_Prenotazione.DataIns) <= '$aLibera'";
				if(isset($barca) && $barca != '') {
					$sql .= " AND RT_Flotta.FlottaId = ".$barca;
				}
				if(isset($tipoNoleggio) && $tipoNoleggio != '') {
					$sql .= " AND RT_Flotta.TipoNoleggio = ".$tipoNoleggio;
				}
				if(isset($areaLavoro) && $areaLavoro != '') {
					$sql .= " AND RT_Flotta.AreaLavoro = ".$areaLavoro;
				}
				// Esegui la query e ottieni il risultato
				$row = $db->query_first($sql);

				// Memorizza il risultato nell'array
				$results[] = array(
					'month' => $month, 
					'total' => isset($row['tot']) ? $row['tot'] : 0, 
					'year' => $currentYear
				);
			}
            break;

        default:
            $currentYear = date('Y');
            $currentMonth = date('m');
            $lastDayOfMonth = date('t', strtotime(date('Y-m-d')));

            // Calcola il totale delle prenotazioni per ciascun giorno del mese corrente
            for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                $sql = "SELECT count(*) as tot FROM RT_Prenotazione 
                        INNER JOIN RT_PrenotazionePercorso ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
						left JOIN RT_Corsa ON RT_PrenotazionePercorso.CorsaId = RT_Corsa.CorsaId
						left join `RT_TipologiaBus` ON (RT_Corsa.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
						left join RT_Flotta ON RT_TipologiaBus.TipologiaBusId = RT_Flotta.TipologiaBusId
                        WHERE RT_Prenotazione.Stato = 1 AND (RT_Prenotazione.PrenotazioneStato <> 6  AND RT_Prenotazione.PrenotazioneStato <> 4)
                            AND RT_Prenotazione.Cancella = 0
                            AND RT_Prenotazione.GestoreIdRef IN ($InGestoreFigli)
                            AND YEAR(RT_Prenotazione.DataIns) = $currentYear
                            AND MONTH(RT_Prenotazione.DataIns) = $currentMonth
                            AND DAY(RT_Prenotazione.DataIns) = $day";
				if(isset($barca) && $barca != '') {
					$sql .= " AND RT_Flotta.FlottaId = ".$barca;
				}
				if(isset($tipoNoleggio) && $tipoNoleggio != '') {
					$sql .= " AND RT_Flotta.TipoNoleggio = ".$tipoNoleggio;
				}
				if(isset($areaLavoro) && $areaLavoro != '') {
					$sql .= " AND RT_Flotta.AreaLavoro = ".$areaLavoro;
				}
                $row = $db->query_first($sql);
                $total = isset($row['tot']) ? $row['tot'] : 0;
                $results[] = array('day' => $day, 'total' => $total, 'month' => $currentMonth, 'year' => $currentYear);
			}
            break;
    }

    echo json_encode($results); // Restituisce i risultati come JSON
    $db->close();
    exit();
}

function barche($periodo) {
	global $user;

	$db= new Database();
	$db->connect();
	
	$sql = "SELECT count(*) as tot FROM `RT_Flotta` WHERE Cancella = 0";
	$row = $db->query_first($sql);

	if(isset($row['tot'])) {
		$tot = $row['tot'];
	} else {
		$tot = 0;
	}
	echo($tot);
	$db->close();
	exit();
   
}

function barcheAttive($periodo) {
	global $user;

	$db= new Database();
	$db->connect();
	
	$sql = "SELECT count(*) as tot FROM `RT_Flotta` WHERE Cancella = 0 AND Stato = 1";
	$row = $db->query_first($sql);

	if(isset($row['tot'])) {
		$tot = $row['tot'];
	} else {
		$tot = 0;
	}
	echo($tot);
	$db->close();
	exit();
   
}



if(is_object($user)) {
	$db= new Database();
	$db->connect();
	$user->conn=$db;
	$permessi = $user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0) {   
		
		if (!empty($_POST)) {
			switch($_POST['action']) {
				case "tour":
					tour($_POST['periodo'], $_POST['da'], $_POST['a'], $_POST['barca'], $_POST['tipoNoleggio'], $_POST['areaLavoro']);   
					break;
				case "entrate":
					entrate($_POST['periodo'], $_POST['da'], $_POST['a'], $_POST['barca'], $_POST['tipoNoleggio'], $_POST['areaLavoro']);     
					break;
				case "prenotazioni":
					prenotazioni($_POST['periodo'], $_POST['da'], $_POST['a'], $_POST['barca'], $_POST['tipoNoleggio'], $_POST['areaLavoro']);     
					break;
				case "barche":
					barche();
					break;
				case "barcheAttive":
					barcheAttive();
					break;
				case "prenotazioniGrafo":
					prenotazioniGrafo($_POST['periodo'], $_POST['da'], $_POST['a'], $_POST['barca'], $_POST['tipoNoleggio'], $_POST['areaLavoro']);  
					break;
			}
		}
	} else {
		echo "ciao";
		Errors::$ErrorePermessiModulo;
	}

}

// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>