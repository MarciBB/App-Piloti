<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$ModuloId=1;
$aColumns = array('MediatoreDisponibilitaId','DispoDataDal', 'DispoDataAl', 'Giorno', 'Comune', 'Indirizzo','orario','Festivi','Feriali');
$sIndexColumn = "MediatoreDisponibilitaId";
$sTable = "ViewDisponibilitaMediatore";

$OdcIdRef=$user->OdcId;
$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
include_once($classespath_."class.Mediatore.php");
global $mediatore_wizard;

if(isset($_SESSION['MEDIATORE_WIZARD'])) {
$mediatore_wizard=unserialize($_SESSION['MEDIATORE_WIZARD']);
}
$MediatoreId=$mediatore_wizard->MediatoreId;

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
	
       
        
	
	/*
	 * SQL queries
	 * Get data to display
	 */
        
        if ($sWhere=="")
            $sWhere =" where OdcIdRef=$OdcIdRef and MediatoreId=$MediatoreId";
        else
            $sWhere.=" and OdcIdRef=$OdcIdRef and MediatoreId=$MediatoreId";
       
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere 
		$sOrder
		$sLimit
	";
              
     
        
	$rResult = $db->query( $sQuery ) or exit("Error ".$sQuery);
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = $db->query( $sQuery ) or exit("Error ".$sQuery);
	$aResultFilterTotal = $db->fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
	";
	$rResultTotal = $db->query( $sQuery ) or exit("Error ".$sQuery);
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
        
	$row = array();
        $MediatoreDisponibilitaId="";
        $dispoDal="";
        $dispoAl="";
        $giorno="";
        $comune="";
        $orario="";
        $lastcomune=array();
        $lastday="";
        $lastorario="";
        $comune_intero="";
        $festivi="";
        $feriali="";
	while ( $aRow = $db->fetch_array( $rResult ) )
	{      
                
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
                    
			if ( $aColumns[$i] == "MediatoreDisponibilitaId" )
			{
			    if(($aRow[ $aColumns[$i] ]!=  $MediatoreDisponibilitaId)and($MediatoreDisponibilitaId!="")){
                                $row[]=$dispoDalRow;
                                 $row[]=$dispoAlRow;
                                 $row[]=$orario;
                                 $row[]=$giorno;
                                 $row[]=$comune_intero;
                                 if($festivi==1)
                                        $festivi="Si";
                                 else
                                     $festivi="No";
                                  if($feriali==1)
                                        $feriali="Si";
                                 else
                                     $feriali="No";
                                 $row[]=$festivi;
                                 $row[]=$feriali;
                                 $row[]=$edit;                                
                                $output['aaData'][] = $row;
                                $dispoDal="";
                                $dispoAl="";
                                $giorno="";
                                $comune="";
                                $orario="";
                                $lastcomune=array();
                                $lastday="";
                                $lastorario="";
                                $comune_intero="";
                                $festivi="";
                                $feriali="";
                                $row = array();
                            }
                            
                            $MediatoreDisponibilitaId=$aRow[ $aColumns[$i] ];	
                            $edit="<a href=\"#\" onclick=\"javascript:ExternalLoad('mediatore','mediatore.php?do=edit_disponibilita&amp;MediatoreDisponibilitaId=".$MediatoreDisponibilitaId."');\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></a>";
                            $delete="<a href=\"#\" onclick=\"javascript:EliminaDisponibilita('".$MediatoreDisponibilitaId."');\" title=\"cancella\"><img alt=\"cancella\" title=\"cancella\" src=/images/iconset/cancella_item.png /></a>";
                            $edit = $edit.$delete;
			}
                        
			elseif ( $aColumns[$i] == 'Giorno' )
			{
				/* General output */
                            if( $lastday!=$aRow[ $aColumns[$i] ]){
                                $lastday=$aRow[ $aColumns[$i] ];
                                $giorno.=$aRow[ $aColumns[$i] ]."<br/>";
                            }
			}
                        elseif ( $aColumns[$i] == 'orario' )
			{
				/* General output */
                            if( $lastorario!=$aRow[ $aColumns[$i] ]){
                                $lastorario=$aRow[ $aColumns[$i] ];
                                $orario.=$aRow[ $aColumns[$i] ]."<br/>";
                            }
			}
                        elseif ( $aColumns[$i] == 'Comune' )
			{
				/* General output */
                                
				$comune=$aRow[ $aColumns[$i] ].", ";
			}
                        elseif ( $aColumns[$i] == 'Indirizzo' )
			{
				/* General output */                           
                             if(!(in_array($comune.$aRow[ $aColumns[$i] ]."<br/>", $lastcomune))){
				$comune_intero.=$comune.$aRow[ $aColumns[$i] ]."<br/>";                                 
                                 array_push($lastcomune,$comune.$aRow[ $aColumns[$i] ]."<br/>");
                             }
                                 
			}
                        elseif ( $aColumns[$i] == 'Festivi' )
			{
				/* General output */                            
                             if($aRow[ $aColumns[$i] ]!=$festivi){
                                  $festivi=$aRow[ $aColumns[$i] ];                                  
                             }
			}
                        elseif ( $aColumns[$i] == 'Feriali' )
			{
				/* General output */                            
                             if($aRow[ $aColumns[$i] ]!=$feriali){
                                  $feriali=$aRow[ $aColumns[$i] ];                                  
                             }
			}
                         elseif ( $aColumns[$i] == 'DispoDataDal' )
			{
				/* General output */                            
                             if($aRow[ $aColumns[$i] ]!=$dispoDal){
                                  $dispoDal=$aRow[ $aColumns[$i] ];
                                  $dispoDalRow = "<strong>".$aRow[ $aColumns[$i] ]."</strong>";
                             }
			}
                         elseif ( $aColumns[$i] == 'DispoDataAl' )
			{
				/* General output */                            
                             if($aRow[ $aColumns[$i] ]!= $dispoAl){
                                $dispoAl=$aRow[ $aColumns[$i] ]; 
                                $dispoAlRow = "<strong>".$aRow[ $aColumns[$i] ]."</strong>";
                             }
			}
		}               
		
	}
         $row[]=$dispoDalRow;
         $row[]=$dispoAlRow;
         $row[]=$orario;
         $row[]=$giorno;
         $row[]=$comune_intero;
         if($festivi==1)
                $festivi="Si";
         else
             $festivi="No";
          if($feriali==1)
                $feriali="Si";
         else
             $feriali="No";
         $row[]=$festivi;
         $row[]=$feriali;
         $row[]=$edit;
         $output['aaData'][] = $row;        
       
	echo json_encode( $output );
        
        

?>
