<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");

$ModuloId=1;
$aColumns = array('Data', 'DataA', 'Descrizione', 'Costo', 'TipoSpesa', 'Fornitore', 'Categoria', 'Destinazione', 'MetodoPagamento', 'Pagato', 'SpesaId');
$sIndexColumn = "SpesaId";
$sTable = "RT_Spese";

$OdcIdRef = $user->OdcId;
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
include_once($classespath_."class.Spesa.php");
include_once($classespath_."class.Fornitore.php");

global $db;

$errors = new Errors();
$speseObj = new Spesa();
$speseObj->conn = $db;

$sql = "SELECT 
RT_Spese.Data, 
RT_Spese.Data as DataA,
RT_Spese.Descrizione,
RT_Spese.Costo,
RT_Spese.TipoSpesa,
RT_Fornitori.Nome as Fornitore,
RT_FornitoriCategorie.Nome as Categoria,
RT_SpeseDestinazione.Nome as Destinazione,
RT_SpesePagamento.Nome as MetodoPagamento,
RT_Spese.Pagato,
RT_Spese.SpesaId
FROM RT_Spese
LEFT JOIN RT_Fornitori ON RT_Spese.FornitoreId = RT_Fornitori.FornitoreId
LEFT JOIN RT_FornitoriCategorie ON RT_Spese.CategoriaId = RT_FornitoriCategorie.FornitoriCategoriaId
LEFT JOIN RT_SpeseDestinazione ON RT_Spese.DestinazioneId = RT_SpeseDestinazione.SpeseDestinazioneId
LEFT JOIN RT_SpesePagamento ON RT_Spese.MetodoPagamentoId = RT_SpesePagamento.SpesePagamentoId
";

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
	 * MySQL connection
	 */
	// usa la connessione centralizzata $db (Database)
	
	
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
	$sOrder = " ORDER BY Data DESC ";
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
		for ( $i=0 ; $i < count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".$db->escape( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}

	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{

		$j = $i;

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
            if($aColumns[$j] == 'TipoSpesa') {
				if($_GET['sSearch_'.$i] == 'Fissi') {
					$sWhere .= $aColumns[$j]." = 1 ";
				} else if ($_GET['sSearch_'.$i] == 'Variabili') {
					$sWhere .= $aColumns[$j]." = 2 ";
				} else {
					$sWhere .= $aColumns[$j]." = 0 ";
				}
			} else if($aColumns[$j] == 'Fornitore') {	   
				$sWhere .= "RT_Fornitori.FornitoreId = '".$db->escape($_GET['sSearch_'.$i])."' ";
			} else if($aColumns[$j] == 'Categoria') {	   
				$sWhere .= "RT_FornitoriCategorie.FornitoriCategoriaId = '".$db->escape($_GET['sSearch_'.$i])."' ";
			} else if($aColumns[$j] == 'Destinazione') {	   
				$sWhere .= "RT_SpeseDestinazione.SpeseDestinazioneId = '".$db->escape($_GET['sSearch_'.$i])."' ";
			} else if($aColumns[$j] == 'MetodoPagamento') {	   
				$sWhere .= "RT_SpesePagamento.SpesePagamentoId = '".$db->escape($_GET['sSearch_'.$i])."' ";
			} else if($aColumns[$j] == 'Descrizione') {	   
				$sWhere .= "RT_Spese.Descrizione LIKE '%".$db->escape($_GET['sSearch_'.$i])."%' ";
			} else if($aColumns[$j] == 'Data') {
				if(isset($_GET['sSearch_0']) && $_GET['sSearch_0'] != '') {
					$sWhere .= "RT_Spese.Data >= '".$db->escape($_GET['sSearch_0'])."' ";
				}
			} else if($aColumns[$j] == 'DataA') {
				$sWhere .= "RT_Spese.Data <= '".$db->escape($_GET['sSearch_1'])."' ";
			} else {
				$sWhere .= $aColumns[$j]." LIKE '%".$db->escape($_GET['sSearch_'.$i])."%' ";                          
			}				
		}
               
	}

     /*
	 * SQL queries
	 * Get data to display
	 */
        
        if ($sWhere=="")
            $sWhere =" where RT_Spese.OdcIdRef = $OdcIdRef AND RT_Spese.Cancella = 0";
        else
            $sWhere.=" and RT_Spese.OdcIdRef = $OdcIdRef AND RT_Spese.Cancella = 0";
       
	/*$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere 
		$sOrder
		$sLimit
	";*/
    $sQuery = " $sql 
		$sWhere 
		$sOrder
		$sLimit";
	//echo $sQuery;       

        
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
		SELECT COUNT(*) as tot
		FROM   ($sql  $sWhere  $sOrder) tabella
	";

	$rResultTotal = $db->query( $sQuery );
	$aResultTotal = $db->fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	$sqlImporto = "SELECT SUM(Costo) as Tot FROM ($sql  $sWhere  $sOrder) tabella";
	$rResultImporto = $db->query( $sqlImporto );
	$aResultImporto = $db->fetch_array($rResultImporto);
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iTotal,
		"TotalCosto" => $aResultImporto['Tot'],
		"aaData" => array()
	);
        
	
	while ( $aRow = $db->fetch_array( $rResult ) ) {
		$row = array();
		 
		for ( $i=0 ; $i<count($aColumns) ; $i++ ) {

			if ( $aColumns[$i] == "TipoSpesa") {
				
				$tipo = $aRow[ $aColumns[$i] ];
				if($tipo == 1) {
				    $row[] = $dizionario['spese']['fissi'];
				} else if($tipo == 2) {
					$row[] = $dizionario['spese']['variabili'];
				} else {
					$row[] = $dizionario['spese']['senza_tipo'];
				}
				
				/* General output */
			} elseif ( $aColumns[$i] == "Data") {
				
				$dataString = $aRow[ $aColumns[$i] ];
				if($dataString == '0000-00-00') {
					$row[] = 'N.D.';
				} else {
					$date = new DateTime($dataString);
					$row[] = $date->format('d/m/Y');
				}
				
			} elseif ( $aColumns[$i] == "Costo") {
				
				$costo = $aRow[ $aColumns[$i] ];
				$row[] = number_format((float)$costo, 2, ',', '.') . ' €';

			} elseif ( $aColumns[$i] == "Pagato") {
				
				$pagato = $aRow[ $aColumns[$i] ];
				if($pagato) {
				    $row[] = '<i class="fa fa-check" style="color:green"></i>';
				} else {
					$row[] = '<i class="fa fa-times" style="color:red"></i>';
				}
			} elseif ( $aColumns[$i] == "SpesaId") {
				
				$SpesaId = $aRow[ $aColumns[$i] ];
				/* General output */
				$temp = "<a href=\"#\" onclick=\"loadMainContent('rt_spese','spese.php?do=edit&amp;SpesaId=".$SpesaId."',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></a>";
				$temp .= " <a href=\"#\" onclick=\"javascript:CancellaSpesa($SpesaId);\" title=\"elimina\"><i class=\"fa fa-trash-o edita\" aria-hidden=\"true\" alt=\"elimina\" title=\"elimina\"></i></a>";
				$row[] = $temp;
			} elseif ( $aColumns[$i] != '' ) {
				/* General output */
				$row[] = $aRow[ $aColumns[$i] ];
			}
		}
		 
		$output['aaData'][] = $row;
	}
        
      
	header('Content-Type: application/json');
	echo json_encode( $output );
?>
