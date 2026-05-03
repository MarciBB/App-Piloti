<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$ModuloId=1;
$aColumns = array('Pullman','NumeroPullman','PrenotazioneStatoDescr','NonViaggiato','CodicePrenotazione','ClienteNome','ComunePartenza','ComuneArrivo','TipoViaggio','NumeroPax','TotaleImportoPrenotazione','PrenotazioneId','CorsaId','PrenotazioneStato','TipoViaggioId','Codifica');
$sIndexColumn = "PrenotazioneId";
$sTable = "RT_PostViaggioElenco";
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

$CorsaId=$_REQUEST['CorsaId'];
$CorsaDataPartenza=$_REQUEST['DataPartenza'];

	
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
			$sWhere .= $aColumns[$i]." LIKE '".$db->escape( $_GET['sSearch'] )."%' OR ";
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
                         
                           $sWhere .= $aColumns[$j]." LIKE '".$db->escape($_GET['sSearch_'.$i])."%' ";    
                           
                         
                        
                         
                            
		}
               
	}
	
     /*
	 * SQL queries
	 * Get data to display
	 */
       if ($sWhere=="")
            $sWhere =" where GestoreIdRef IN ($InGestoreFigli) and CorsaId=$CorsaId and DataPartenza='$CorsaDataPartenza' ";
            
        else
            $sWhere.=" and GestoreIdRef IN ($InGestoreFigli) and CorsaId=$CorsaId and DataPartenza='$CorsaDataPartenza' ";
        
        
       
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere 
		$sOrder
		$sLimit
	";
        
    
  //   echo($sQuery);
        
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
        
	
	while ( $aRow = $db->fetch_array( $rResult ) )
	{
		 $row = array();
               
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
                    
                         if ( $aColumns[$i] == "PrenotazioneId")
			{
                            $PrenotazioneId=$aRow[ $aColumns[$i] ];
                            $CorsaId=$aRow['CorsaId'];
                            $PrenotazioneStatoId=$aRow['PrenotazioneStato'];
                             $TipoViaggioId=$aRow['TipoViaggioId'];
                             $Codifica=$aRow['Codifica'];
                             $ClienteNome=  addslashes($aRow['ClienteNome']);
                             $Cod=0;
                             if ($Codifica==0)
                                 $Cod=1;
				/* General output */
                                if ($TipoViaggioId==1)
                                $row[] = "<a href=\"#\" onclick=\"javascript:SetNonViaggiato('$Cod','$PrenotazioneId','$ClienteNome');\" title=\"Viaggiato / non Viaggiato\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"Viaggiato / non Viaggiato\" title=\"Viaggiato / non Viaggiato\"></i></a>";
                                
                                else
                                $row[]='';    
                                
                              //     $row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_postviaggio','postviaggio.php?do=QuadraturaPrenotazione&amp;DataPartenza=".$DataPartenza."&amp;CorsaId=".$CorsaId."&PrenotazioneId=".$PrenotazioneId."',this);\" title=\"edita\"><img alt=\"edita\" title=\"edita\" src=/images/edita_item.png /></a>";
                             
			}
                       
                   	elseif (( $aColumns[$i] != '' ) and ( $aColumns[$i] != 'CorsaId' ) and ( $aColumns[$i] != 'TipoViaggioId' ) and ( $aColumns[$i] != 'PrenotazioneStato' )and ( $aColumns[$i] != 'Codifica' ))
			{
				/* General output */
				$row[] = $aRow[ $aColumns[$i] ];
			}
		}
               
               // $row[]="<a href=\"/protected/modules/rt_previaggio/stampa_titoli_di_viaggio_pdf.php?added=true&amp;type=1&amp;CorsaId=".$CorsaId."&amp;DataPartenza=".$CorsaDataPartenza."&amp;seller_type=1&amp;PrenotazioneId=".$PrenotazioneId."\" class=\icona_stampa\" target=\"_new\"><img alt=\"Stampa PDF\" src=\"../images/print-icon.png\"></a>";
		 $row[]="";
		
                $output['aaData'][] = $row;
	}
        
      
	
	echo json_encode( $output );
        
        

?>
