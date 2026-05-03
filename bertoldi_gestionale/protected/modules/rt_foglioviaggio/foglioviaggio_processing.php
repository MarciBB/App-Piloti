<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$ModuloId=45;
$aColumns = array('CorsaNome', 'LineaNome', 'CorsaDataPartenza', 'OrarioPartenza', 'Autista1', 'Autista2', 'TipologiaBus', 'Targa', 'Partenza', 'Arrivo', 'KmPartenza', 'KmArrivo', 'FoglioViaggioId');
$sIndexColumn = "FoglioViaggioId";
$sTable = "RT_FoglioViaggio";

$OdcIdRef=$user->OdcId;
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
	
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
	
	/* 
	 * MySQLi connection
	 */
	$db = new Database();
	
	
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
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
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
        
//         if ($sWhere=="")
//             $sWhere =" where OdcIdRef=$OdcIdRef";
//         else
//             $sWhere.=" and OdcIdRef=$OdcIdRef";
       
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere 
		$sOrder
		$sLimit
	";
        
       
     
        
	$rResult = $db->query( $sQuery );
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
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
        
	
	while ( $aRow = $db->fetch_array( $rResult ) ) {
		$row = array();
		 
		for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
                  
			if ( $aColumns[$i] == "CorsaDataPartenza") {
				$data=$aRow[ $aColumns[$i] ];
				$newDate = date("d-m-Y", strtotime($data));
				$newDate = str_replace('-', '/', $newDate);
				$row[]=$newDate;
			} elseif ( $aColumns[$i] == "OrarioPartenza") {
				$ora=$aRow[ $aColumns[$i] ];
				$rest = substr($ora, 0, -3);
				$row[]=$rest;
			} elseif ( $aColumns[$i] == "FoglioViaggioId") {
				
				$FoglioViaggioId=$aRow[ $aColumns[$i] ];
				/* General output */
				$row[] = "<a href=\"#\" onclick=\"javascript:ExternalLoad('rt_foglioviaggio','foglioviaggio.php?do=spese&amp;FoglioViaggioId=".$FoglioViaggioId."',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></a>";
				
			}  elseif ( $aColumns[$i] != '' ) {
				/* General output */
				$row[] = $aRow[ $aColumns[$i] ];
			}
		}
		
		$output['aaData'][] = array_decode_list($row);
	}
        
      
	
	echo json_encode( $output );
?>