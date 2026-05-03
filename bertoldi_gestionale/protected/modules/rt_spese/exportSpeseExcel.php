<?PHP
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$classespath_= Config::$classespath;

include_once($classespath_."class.Gestore.php");
ob_end_clean();

	$db= new Database();
	$db->connect();
	
	$sTable = "SELECT 
RT_Spese.Data, 
RT_Spese.Descrizione,
RT_Spese.Costo,
CASE 
	WHEN RT_Spese.TipoSpesa = 0 THEN '- Senza Tipo -'
	WHEN RT_Spese.TipoSpesa = 1 THEN 'Fissi'
	WHEN RT_Spese.TipoSpesa = 2 THEN 'Variabili'
	ELSE NULL -- Gestione dei casi non previsti
END AS TipoSpesa,
RT_Fornitori.Nome as Fornitore,
RT_FornitoriCategorie.Nome as Categoria,
RT_SpeseDestinazione.Nome as Destinazione,
RT_SpesePagamento.Nome as MetodoPagamento,
CASE 
	WHEN RT_Spese.Pagato = 1 THEN 'Pagato'
	WHEN RT_Spese.Pagato = 0 THEN 'Non Pagato'
	ELSE NULL -- Gestione dei casi non previsti
END AS Pagato
FROM RT_Spese
LEFT JOIN RT_Fornitori ON RT_Spese.FornitoreId = RT_Fornitori.FornitoreId
LEFT JOIN RT_FornitoriCategorie ON RT_Spese.CategoriaId = RT_FornitoriCategorie.FornitoriCategoriaId
LEFT JOIN RT_SpeseDestinazione ON RT_Spese.DestinazioneId = RT_SpeseDestinazione.SpeseDestinazioneId
LEFT JOIN RT_SpesePagamento ON RT_Spese.MetodoPagamentoId = RT_SpesePagamento.SpesePagamentoId";
	
	
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
			( $_GET['sSearch_9'] && $_GET['sSearch_9'] != "" )
			) {
		$sWhere = " WHERE (";
		
		if(isset($_GET['sSearch_0']) && $_GET['sSearch_0'] != '') {
			$sWhere .= "RT_Spese.Data >= '".$db->escape( $_GET['sSearch_0'])."' AND ";
		}
		if(isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '') {
			$sWhere .= "RT_Spese.Data <= '".$db->escape( $_GET['sSearch_1'])."' AND ";
		}
		if ( $_GET['sSearch_2'] && $_GET['sSearch_2'] != "" ) {
			$sWhere .= "RT_Spese.Descrizione LIKE '%".$db->escape( $_GET['sSearch_2'])."%' AND ";
		}
		if ( $_GET['sSearch_3'] && $_GET['sSearch_3'] != "" ) {
			$sWhere .= "RT_Spese.Costo LIKE '%".$db->escape( $_GET['sSearch_3'] )."%' AND ";
		}
		if ( $_GET['sSearch_4'] && $_GET['sSearch_4'] != "" ) {
			if($_GET['sSearch_4'] == 'Fissi') {
				$sWhere .= " RT_Spese.TipoSpesa = 1 AND ";
			} else if ($_GET['sSearch_4'] == 'Variabili') {
				$sWhere .= " RT_Spese.TipoSpesa = 2 AND ";
			} else {
				$sWhere .= " RT_Spese.TipoSpesa = 0 AND ";
			}
		}
		if ( $_GET['sSearch_5'] && $_GET['sSearch_5'] != "" ) {
			$sWhere .= "RT_Fornitori.Nome LIKE '".$db->escape( $_GET['sSearch_5'] )."%' AND ";
		}
		if ( $_GET['sSearch_6'] && $_GET['sSearch_6'] != "" ) {
			$sWhere .= "RT_FornitoriCategorie.Nome LIKE '".$db->escape( $_GET['sSearch_6'] )."%' AND ";
		}
		if ( $_GET['sSearch_7'] && $_GET['sSearch_7'] != "" ) {
			$sWhere .= "RT_SpeseDestinazione.Nome LIKE '".$db->escape( $_GET['sSearch_7'] )."%' AND ";
		}
		if ( $_GET['sSearch_8'] && $_GET['sSearch_8'] != "" ) {
			$sWhere .= "RT_SpesePagamento.Nome LIKE '".$db->escape( $_GET['sSearch_8'] )."%' AND ";
		}
		if ( $_GET['sSearch_9'] && $_GET['sSearch_9'] != "" ) {
			$sWhere .= "RT_Spese.Pagato LIKE '".$db->escape( $_GET['sSearch_9'] )."%' AND ";
		}

		$sWhere = substr_replace( $sWhere, "", -4 );
		$sWhere .= ')';
		
		$sWhere.="  AND RT_Spese.Cancella = 0";
	} else {
		$sWhere.=" WHERE RT_Spese.Cancella = 0";
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