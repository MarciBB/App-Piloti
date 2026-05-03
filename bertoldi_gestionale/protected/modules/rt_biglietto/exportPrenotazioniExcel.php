<?PHP
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$classespath_= Config::$classespath;

include_once($classespath_."class.Gestore.php");
ob_end_clean();

	$db= new Database();
	$db->connect();
	
	$sTable = "Select * from (SELECT 
			rt_appprenotazionestato.PrenotazioneStato AS PrenotazioneStato,
			gestore.RagioneSociale AS Agenzia,
			CONCAT(CONVERT(DATE_FORMAT(rt_prenotazione.DataIns, _utf8 '%d/%m/%Y %H:%i:%s') USING LATIN1), _latin1 ' da ', operatore.Username) AS DataCreazione,
			rt_prenotazione.CodicePrenotazione AS CodicePrenotazione,
			rt_prenotazione.ClienteNome AS ClienteNome,
			rt_prenotazionepercorso.LineaNome AS LineaNome,
			DATE_FORMAT(rt_prenotazionepercorso.DataOraSalita, _utf8 '%d/%m/%Y') AS CorsaDataPartenza,
			DATE_FORMAT(rt_prenotazionepercorso.DataOraSalita, _utf8 '%H:%i') AS CorsaOrarioPartenza,
			rt_prenotazionepercorso.ComuneSalita AS ComuneSalita,
			rt_prenotazionepercorso.ComuneDiscesa AS ComuneDiscesa,
			IF((rt_prenotazione.TipoTour = 1), _utf8 'Privato / Personalizzato', _utf8 'Tour di Gruppo') AS TipoTour,
			(rt_prenotazione.TotalePaxPrenotati - rt_prenotazionepercorso.PasseggeriEsclusi) AS TotalePostiPrenotati,
			rt_prenotazione.TotalePrenotazione AS TotalePrenotazione,
			rt_prenotazione.TotaleResiduo AS TotaleResiduo,
			rt_prenotazione.GestoreIdRef AS GestoreIdRef

FROM RT_Prenotazione AS rt_prenotazione 
JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazione.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId) 
JOIN RT_PrenotazionePercorso AS rt_prenotazionepercorso ON (rt_prenotazione.PrenotazioneId = rt_prenotazionepercorso.PrenotazioneId) 
JOIN Operatore AS operatore ON (rt_prenotazione.OpeIns = operatore.OperatoreId)
JOIN Gestore AS gestore ON (operatore.GestoreId = gestore.GestoreId) 
GROUP BY rt_prenotazione.PrenotazioneId, rt_prenotazionepercorso.PrenotazionePercorsoId 
ORDER BY rt_prenotazione.PrenotazioneId DESC
) AS RT_PrenotazioneLista";
	
	
	$gestore=new Gestore();
	$gestore->conn=$db;
	$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
	$InGestoreFigli=implode(",", $gestorefigli);
	
	if(( $_GET['sSearch_0'] && $_GET['sSearch_0'] != "" ) ||
			( $_GET['sSearch_1'] && $_GET['sSearch_1'] != "" ) ||
			( $_GET['sSearch_2'] && $_GET['sSearch_2'] != "" ) ||
			( $_GET['sSearch_3'] && $_GET['sSearch_3'] != "" )||
			( $_GET['sSearch_4'] && $_GET['sSearch_4'] != "" )||
			( $_GET['sSearch_5'] && $_GET['sSearch_5'] != "" )||
			( $_GET['sSearch_6'] && $_GET['sSearch_6'] != "" ) ||
			( $_GET['sSearch_7'] && $_GET['sSearch_7'] != "" ) ||
			( $_GET['sSearch_8'] && $_GET['sSearch_8'] != "" )||
			( $_GET['sSearch_9'] && $_GET['sSearch_9'] != "" )||
			( $_GET['sSearch_10'] && $_GET['sSearch_10'] != "" )||
			( $_GET['sSearch_11'] && $_GET['sSearch_11'] != "" )||
			( $_GET['sSearch_12'] && $_GET['sSearch_12'] != "" )||
			( $_GET['sSearch_13'] && $_GET['sSearch_13'] != "" )
			) {
		$sWhere = " WHERE (";
		if ( $_GET['sSearch_0'] && $_GET['sSearch_0'] != "" ) {
			$sWhere .= "PrenotazioneStato LIKE '".$db->escape( $_GET['sSearch_0'] )."%' OR ";
		}
		if ( $_GET['sSearch_1'] && $_GET['sSearch_1'] != "" ) {
			$sWhere .= "RagioneSociale LIKE '".$db->escape( $_GET['sSearch_1'] )."%' OR ";
		}
		if ( $_GET['sSearch_2'] && $_GET['sSearch_2'] != "" ) {
			$sWhere .= "DataIns LIKE '".$db->escape( $_GET['sSearch_2'] )."%' OR ";
		}
		if ( $_GET['sSearch_3'] && $_GET['sSearch_3'] != "" ) {
			$sWhere .= "CodicePrenotazione LIKE '".$db->escape( $_GET['sSearch_3'] )."%' OR ";
		}
		if ( $_GET['sSearch_4'] && $_GET['sSearch_4'] != "" ) {
			$sWhere .= "ClienteNome LIKE '".$db->escape( $_GET['sSearch_4'] )."%' OR ";
		}
		if ( $_GET['sSearch_5'] && $_GET['sSearch_5'] != "" ) {
			$sWhere .= "LineaNome LIKE '".$db->escape( $_GET['sSearch_5'] )."%' OR ";
		}
		if ( $_GET['sSearch_6'] && $_GET['sSearch_6'] != "" ) {
			$sWhere .= "CorsaDataPartenza LIKE '".$db->escape( $_GET['sSearch_6'] )."%' OR ";
		}
		if ( $_GET['sSearch_7'] && $_GET['sSearch_7'] != "" ) {
			$sWhere .= "CorsaOrarioPartenza LIKE '".$db->escape( $_GET['sSearch_7'] )."%' OR ";
		}
		if ( $_GET['sSearch_8'] && $_GET['sSearch_8'] != "" ) {
			$sWhere .= "ComuneSalita LIKE '".$db->escape( $_GET['sSearch_8'] )."%' OR ";
		}
		if ( $_GET['sSearch_9'] && $_GET['sSearch_9'] != "" ) {
			$sWhere .= "ComuneDiscesa LIKE '".$db->escape( $_GET['sSearch_9'] )."%' OR ";
		}
		if ( $_GET['sSearch_10'] && $_GET['sSearch_10'] != "" ) {
			$sWhere .= "TipoTour LIKE '".$db->escape( $_GET['sSearch_10'] )."%' OR ";
		}
		if ( $_GET['sSearch_11'] && $_GET['sSearch_11'] != "" ) {
			$sWhere .= "TotalePostiPrenotati LIKE '".$db->escape( $_GET['sSearch_11'] )."%' OR ";
		}
		if ( $_GET['sSearch_12'] && $_GET['sSearch_12'] != "" ) {
			$sWhere .= "TotalePrenotazione LIKE '".$db->escape( $_GET['sSearch_12'] )."%' OR ";
		}
		if ( $_GET['sSearch_13'] && $_GET['sSearch_13'] != "" ) {
			$sWhere .= "TotaleResiduo LIKE '".$db->escape( $_GET['sSearch_13'] )."%' OR ";
		}
		
		
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
		
		$sWhere.=" and GestoreIdRef IN ($InGestoreFigli)";
	} else {
		$sWhere.=" WHERE GestoreIdRef IN ($InGestoreFigli)";
	}
	
	$data = $db->fetch_array($sTable.$sWhere);

  function cleanData(&$str)
  {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

  // file name for download
  $filename = "Prenotazioni_" . date("YmdHis") .".xls";

  header("Content-Disposition: attachment; filename=\"$filename\"");
  header("Content-Type: application/vnd.ms-excel");

  $flag = false;
  foreach($data as $row) {
  	unset($row['GestoreIdRef']);
    if(!$flag) {
      // display field/column names as first row
      echo implode("\t", array_keys($row)) . "\n";
      $flag = true;
    }
    array_walk($row, 'cleanData');
    echo implode("\t", array_values($row)) . "\n";
  }

  exit;
?>