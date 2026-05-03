<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$ModuloId=2;
$aColumns = array('MediazioneStato','InAttesaDiDati','Codice', 'Oggetto', 'Materia', 'data','Comune','Mediazioneid','InAttesaDiDatiId','DataIns','OpeIns','ComuneIns','CodiceSedeIns','IndirizzoIns','DataPresentazioneIstanza','DataScadenzaIstanza','Indirizzo','CodiceSede');
$sIndexColumn = "Mediazioneid";
$sTable = "ElencoMediazioniFinaleView";

$OdcIdRef=$user->OdcId;
$OperatoreTipoId=$user->OperatoreTipoId;
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.AnagraficaParte.php");
$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);
$dt=new DT();
$MedId='';
if ($_REQUEST['AnagraficaId']>0)
{
$AnagraficaId=$_REQUEST['AnagraficaId'];
$anag=new AnagraficaParte($AnagraficaId);
$anag->conn=$db;
//$anag->getAnagraficaMediazioni($user,$InGestoreFigli);




}
	
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
	
       if ($sWhere=="")
            $sWhere =" where GestoreId IN ($InGestoreFigli) ";
            
        else
            $sWhere.=" and GestoreId IN ($InGestoreFigli) ";
        
        if ($OperatoreTipoId==2) // se si tratta di un mediatore
        {
            
            $sWhere.=" and MediatoreId=$user->MediatoreId ";
            
        }
        
        
	
	/*
	 * SQL queries
	 * Get data to display
	 */
        
      
       
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
        
	
	while ( $aRow = $db->fetch_array( $rResult ) )
	{
		 $row = array();
               
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
                    
			if ( $aColumns[$i] == "Codice" )
			{
				$MediazioneId=$aRow['Mediazioneid'];
                                $OpeIns=addslashes($aRow['OpeIns']);
                                $DataIns=addslashes($aRow['DataIns']);
                                
                                $Data1=$dt->format($aRow['DataIns'], "y-m-d", "d/m/Y h:m:i");
                                $DataIns=addslashes($Data1);
                                
                                
                                $Data1=$dt->format($aRow['DataPresentazioneIstanza'], "y-m-d", "d/m/Y h:m:i");
                                $DataPresentazioneIstanza=addslashes($Data1);
                                $Data1=$dt->format($aRow['DataScadenzaIstanza'], "y-m-d", "d/m/Y");
                                $DataScadenzaIstanza=addslashes($Data1);
                                $SedeIns=addslashes($aRow['IndirizzoIns']." ".$aRow['ComuneIns']);
                                
                                $Contenuto="<ul>";
                                 $Contenuto.="<li><span class=brain_etichetta>Data Presentazione:</span>";
                                $Contenuto.="<span class=brain_valore>&nbsp;".$DataPresentazioneIstanza."</span></li>";
                                $Contenuto.="<li><span class=brain_etichetta>Data Scadenza:</span>";
                                $Contenuto.="<span class=brain_valore>&nbsp;".$DataScadenzaIstanza."</span></li>";
                                
                                $Contenuto.="<li><span class=brain_etichetta>Data Inserimento:</span>";
                                $Contenuto.="<span class=brain_valore>&nbsp;".$DataIns."</span></li>";
                                $Contenuto.="<li><span class=brain_etichetta>Operatore:</span>";
                                $Contenuto.="<span class=brain_valore>&nbsp;".$OpeIns."</span></li>";
                                $Contenuto.="<li><span class=brain_etichetta>Sede Inserimento:</span>";
                                $Contenuto.="<span class=brain_valore>&nbsp;".$SedeIns."</span></li>";
                               
                                
                                
                                
                                $Contenuto.="</ul>";
                                
				$row[] = "<a  href=\"#\" onmouseout=\"javascript:UnLoadToolTip()\"; onmouseover=\"javascript:LoadToolTip('$Contenuto',$(this));\">".$aRow[ $aColumns[$i] ]."</a>";
			} 
                        
                     /*   elseif ( $aColumns[$i] == "CodiceSede" )
			{
				$Sede="Napoli";
                                
                                $Contenuto="<ul>";
                                $Contenuto.="<li>";
                                $Contenuto.="<span class=brain_valore>&nbsp;".$Sede."</span></li>";
                                
                                $Contenuto.="</ul>";
                                
				$row[] = "<a id=\"$MediazioneId\" href=\"#\" onmouseout=\"javascript:UnLoadToolTip()\"; onmouseover=\"javascript:LoadToolTip('$Contenuto',$(this));\">".$aRow[ $aColumns[$i] ]."</a>";
			} */
                        
                        
                         elseif ( $aColumns[$i] == "InAttesaDiDati")
			{
                            $attesa=$aRow['InAttesaDiDatiId'];
                            if ($attesa==1)
                               $row[] = "<span class=\"inattesa\">".$aRow[ $aColumns[$i] ]."</span>";
                            else
                                $row[] = "";
                 	}
                        
                        
                        elseif ( $aColumns[$i] == "Mediazioneid")
			{
                            $MediazioneId=$aRow[ $aColumns[$i] ];
				/* General output */
                                $row[] = "<a href=\"javascript:void(0);\" onclick=\"loadMainContent('mediazione','mediazione.php?do=edit&amp;MediazioneId=".$MediazioneId."',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></a>";
				
                                        

// $row[] = "<img src=/DataTables/examples/examples_support/details_open.png />";
			}
                        elseif ( $aColumns[$i] == "MediazioneStato")
			{
                              $tipo=trim($aRow[ $aColumns[$i] ]);
                              
                              $tipo1=strtolower(str_replace(" ","",$tipo));
                              
                              $row[] = "<span class=".$tipo1.">".$tipo."</span>";
			}
			elseif ( $aColumns[$i] == "data")
			{
                              
                              $data_filtrata="";
                              if (!empty($aRow[$aColumns[$i]]))
                              $data_filtrata=substr($aRow[ $aColumns[$i] ], 0, -3);
                              
                              
                              $row[] = $data_filtrata;
			}
                         elseif ( $aColumns[$i] == "Comune")
			{
                             $tipo=$aRow[ $aColumns[$i] ];
                             if ($aRow['CodiceSede'])
                             $row[] = addslashes($aRow['Comune']);
                             else
                                 $row[]="";
                                 
			}
                        
                         
                        
			else if ($i<7)
			{
				/* General output */
				$row[] = htmlentities($aRow[ $aColumns[$i] ]);
			}
                        
                        
                        
		}
               
		$output['aaData'][] = $row;
	}
        
      
	
	echo json_encode( $output );
        
        

?>
