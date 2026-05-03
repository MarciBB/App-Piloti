<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");

global $db, $dizionario;

$ModuloId=1;
// $aColumns = array('CorsaWebBloccata','CorsaBloccata','DataInizializzazioneF','DataConsolidamentoF','CorsaNome','LineaNome','DataPartenzaFormattata','OrarioPartenza','PostiTotali','TotalePrenotati','LineaId','PostiRealmentePrenotati','PostiRealmenteDisponibili','MaxPostiOccupati','ServiziPrenotati','AppCalendarioData','CorsaId' );
$aColumns = array('CorsaWebBloccata','CorsaBloccata','CorsaNome','LineaNome','DataPartenzaFormattata','OrarioPartenza','PostiTotali','TotalePrenotati','LineaId','PostiRealmentePrenotati','PostiRealmenteDisponibili','MaxPostiOccupati','ServiziPrenotati','AppCalendarioData','CorsaId' );
$sIndexColumn = "CorsaId";
$sTable = "RT_ViewElencoGestioneOperativita_newStorico";

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
	 * MySQLi connection - using existing Database class
	 */
	// $db connection is already available from main_include.php
	
	
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
        $filtro=0;
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
                          $filtro=1;
                           $sWhere .= $aColumns[$j]." LIKE '".$db->escape($_GET['sSearch_'.$i])."%' ";    
                           
                         
                        
                         
                            
		}
               
	}
	
     /*
	 * SQL queries
	 * Get data to display
	 */
        
        if ($sWhere=="")
            $sWhere =" where OdcIdRef=$OdcIdRef";
        else
            $sWhere.=" and OdcIdRef=$OdcIdRef";
        //if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
      if ($filtro==0)
        {
            $oggi=Date('Y-m-d');
            $sWhere.=" and AppCalendarioData>='$oggi'";
            
        }
     //  echo($sWhere);
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere 
		$sOrder
		$sLimit
	";
       
     
        
	$rResult = $db->query( $sQuery ) or die("Database Error");
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = $db->query( $sQuery ) or die("Database Error");
	$aResultFilterTotal = $db->fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
	";
	$rResultTotal = $db->query( $sQuery ) or die("Database Error");
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
                    
                         if ( $aColumns[$i] == "CorsaWebBloccata")
			{
                            $StatoWeb=$aRow[ $aColumns[$i] ];
                            if($StatoWeb==0) 
                                $row[]="<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"".$dizionario['generale']['attiva']."\"></i>";
                            else
                                $row[]="<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"".$dizionario['generale']['disattiva']."\"></i>";
				/* General output */                                

			}
                    
                         elseif ( $aColumns[$i] == "CorsaBloccata")
			{
                            $Stato=$aRow[ $aColumns[$i] ];
                            if($Stato==0) 
                                $row[]="<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"".$dizionario['generale']['attiva']."\"></i>";
                            else
                                $row[]="<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"".$dizionario['generale']['disattiva']."\"></i>";
				/* General output */                                

			}
                    
                    
			elseif ( $aColumns[$i] == "CorsaId")
			{
                            $CorsaId=$aRow[ $aColumns[$i] ];
                            $CorsaNome=$aRow['CorsaNome'];
                            $DataPartenza=$aRow['AppCalendarioData'];
                            
                            $OrarioPartenza=$aRow['OrarioPartenza'];
                             $Stato=$aRow['CorsaBloccata'];
                             
                            $blocco="Bloccare";
                            if ($Stato==1)
                                 $blocco="Sbloccare";
                            
                             $bloccoweb="Bloccare";
                            if ($StatoWeb==1)
                                 $bloccoweb="Sbloccare";
                            
				/* General output */
                                $row[] = ("<a href=\"#\" onclick=\"ExternalLoad('rt_corsapartenza','corsapartenza.php?do=GestionePax&amp;DataPartenza=$DataPartenza&amp;CorsaId=".$CorsaId."',this);\" title=\"edita\"><i class=\"fa fa-plus-circle blue\" aria-hidden=\"true\" alt=\"aggiungi pax\" title=\"aggiungi pax\"></i></a>");
			        $row[] = ("<a href=\"#\" onclick=\"javascript:BloccaSbloccaCorsa($Stato,$CorsaId,'$CorsaNome','$DataPartenza','$OrarioPartenza','$blocco');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a>");
                                $row[] = ("<a href=\"#\" onclick=\"javascript:BloccaSbloccaCorsaWeb($StatoWeb,$CorsaId,'$CorsaNome','$DataPartenza','$OrarioPartenza','$bloccoweb');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a>");
                                $row[] = ("<a href=\"#\" onclick=\"loadMainContent('rt_previaggio','previaggio.php?do=GestionePreViagigo&amp;DataPartenza=$DataPartenza&amp;CorsaId=".$CorsaId."',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"pre viaggio\" title=\"pre viaggio\"></i></a>");
			      //  $row[] = ("<a href=\"#\" onclick=\"loadMainContent('rt_postviaggio','postviaggio.php?do=GestionePostViaggio&amp;DataPartenza=$DataPartenza&amp;CorsaId=".$CorsaId."',this);\" title=\"edita\"><img alt=\"aggiungi pax\" title=\"post viaggio\" src=/images/incompilazione.png /></a>");
			       
		
                                
                                
                        } elseif ( $aColumns[$i] == 'TotalePrenotati') {
                                        $PostiPrenotati = $aRow['TotalePrenotati'];
                                        $AppCalendarioData = $aRow['AppCalendarioData'];
                                        $CorsaId = $aRow['CorsaId'];
                                        if ($PostiPrenotati>0)
                                          $row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','prenotati_corsa.php?do=show_list&amp;CorsaId=".$CorsaId."&DataCorsa=".$AppCalendarioData."');\" title=\"elenco prenotati\">".$PostiPrenotati."</a>";
                                        else
                                           $row[]= $PostiPrenotati;
                        } elseif ( $aColumns[$i] == 'ServiziPrenotati') {
                        	$ServiziPrenotati = $aRow['ServiziPrenotati'];
                            $AppCalendarioData = $aRow['AppCalendarioData'];
                        	$CorsaId = $aRow['CorsaId'];
                        	if ($ServiziPrenotati>0)
                        		$row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','servizi_corsa.php?do=show_list&amp;CorsaId=".$CorsaId."&DataCorsa=".$AppCalendarioData."');\" title=\"elenco servizi\">".$ServiziPrenotati."</a>";
                        	else
                        		$row[]= $ServiziPrenotati;
                            }elseif ($aColumns[$i] == 'LineaId'){
                            	$PostiDisponibili = $aRow['PostiTotali'] - $aRow['TotalePrenotati'];
                            	$row[] = $PostiDisponibili;
                            }elseif ($aColumns[$i] == 'PostiRealmentePrenotati'){
                            	$PostiRealmentePrenotati = $aRow['PostiRealmentePrenotati'];
                            	if ($PostiRealmentePrenotati>0)
                            		$row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','realmente_prenotati_corsa.php?do=show_list&amp;CorsaId=".$CorsaId."&DataCorsa=".$AppCalendarioData."');\" title=\"elenco realmente prenotati\">".$PostiRealmentePrenotati."</a>";
                            	else
                            		$row[]= $PostiRealmentePrenotati;
                            }elseif (( $aColumns[$i] != '' ) and ($aColumns[$i] != 'AppCalendarioData'))
							{
								/* General output */
							$row[] = ($aRow[ $aColumns[$i] ]);
							}
		}
               
                
                
		$output['aaData'][] = array_decode_list($row);
              
	}
    	echo json_encode( $output );
    ?>