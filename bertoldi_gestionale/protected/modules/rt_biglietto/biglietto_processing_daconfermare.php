<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$ModuloId=1;
$aColumns = array('PrenotazioneStato','RagioneSociale','DataOperazione','CodicePrenotazione','ClienteNome','ClienteCellulare','CorsaNome','DataCorsa','CorsaOrarioPartenza','ComuneSalita','ComuneDiscesa','TotalePostiPrenotati','TotalePrenotazione','PrenotazioneId','CorsaId');
$sIndexColumn = "PrenotazioneId";
$sTable = "RT_ViewElencoPrenotazioneDaConfermare";
$OdcIdRef=$user->OdcId;
$OperatoreTipoId=$user->OperatoreTipoId;
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Gestore.php");

$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);
$dt=new DT();

	
	/* Database connection information */
	/*$gaSql['user']       = Config::$dbuser;
	$gaSql['password']   = Config::$dbpass;
	$gaSql['db']         = Config::$dbname;
	$gaSql['server']     = Config::$dbserver;
	*/
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
	 * no need to edit below this line
	 */
	
	/* usa la connessione centralizzata $db */
	
	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
	    $sLimit = "LIMIT ".$db->escape( $_GET['iDisplayStart'] ).", ".
	        $db->escape( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				".$db->escape( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	
	/* 
	 * Filtering
	 */
	$sWhere = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".$db->escape( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
            $j=$i;
		if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
                         
                           $sWhere .= $aColumns[$j]." LIKE '%".$db->escape($_GET['sSearch_'.$i])."%' ";    
                           
                         
                        
                         
                            
		}
               
	}
	
     /*
	 * SQL queries
	 * Get data to display
	 */
     if ($sWhere=="")
            $sWhere =" where GestoreIdRef IN ($InGestoreFigli) ";
            
        else
            $sWhere.=" and GestoreIdRef IN ($InGestoreFigli) ";
       
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere 
		$sOrder
		$sLimit
	";
        
       
     
        
	$rResult = $db->query( $sQuery );
	
	/* Data set length after filtering */
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $db->query( $sQuery );
	$aResultFilterTotal = $db->fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
	";
	$rResultTotal = $db->query( $sQuery );
	$aResultTotal = $db->fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
        
	
	while ( $aRow = $db->fetch( $rResult ) )
	{
     $row = array();
           
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( $aColumns[$i] == "PrenotazioneId")
        {
            $PrenotazioneId=$aRow[ $aColumns[$i] ];
            $CorsaId=$aRow['CorsaId'];
            $CodicePrenotazione=$aRow['CodicePrenotazione'];
            
            /* General output */
            $row[] = "<a href=\"#\" onclick=\"javascript:ConfermaPrenotazione('$PrenotazioneId','$CodicePrenotazione');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a>";
            $row[] = "<a href=\"#\" onclick=\"loadMainContent('rt_biglietto','biglietto.php?do=edit&amp;CorsaId=".$CorsaId."&PrenotazioneId=".$PrenotazioneId."',this);\" title=\"edita\"><img alt=\"edita\" title=\"edita\" src=/images/edita_item.png /></a>";
        }
        elseif($aColumns[$i] == 'DataCorsa'){
            $sql = "SELECT RitornoAperto FROM RT_Corsa WHERE CorsaId = ".$aRow['CorsaId'];
            $rowTemp = $db->fetch_array($sql);
            if($rowTemp[0]['RitornoAperto'] == 1){
                $row[] = 'Open';
            } else {
                $row[] = $aRow[ $aColumns[$i] ];
            }
        }
        elseif ( $aColumns[$i] == "ClienteNome") {
            // Gestione specifica per ClienteNome con htmlentities
            $clienteNome = $aRow[$aColumns[$i]];
            $row[] = htmlentities($clienteNome, ENT_QUOTES, 'UTF-8');
        }
        elseif (( $aColumns[$i] != '' ) and ( $aColumns[$i] != 'CorsaId' ))
        {
            /* General output */
            $value = $aRow[$aColumns[$i]];
            // Applica htmlentities anche agli altri campi stringa per sicurezza
            if (is_string($value)) {
                $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
            }
            $row[] = $value;
        }
    }
           
    $output['aaData'][] = array_decode_list($row);
}

// Imposta l'header Content-Type per UTF-8
header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>
