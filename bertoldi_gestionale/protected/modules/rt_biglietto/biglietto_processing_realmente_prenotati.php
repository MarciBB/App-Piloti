<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$ModuloId=1;
$aColumns = array('StatoPrenotazione','RagioneSociale','NumeroBiglietto','Cliente','ClienteCellulare','DataPartenza','OrarioPartenza','ComunePartenza','ComuneArrivo','Importo', 'PrenotazioneId');
$sIndexColumn = "PrenotazioneDettaglioId";
$sTable = "RT_ViewBigliettiPrenotati";
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

$CorsaId_post=$_REQUEST['CorsaId'];
$DataCorsa_post=$_REQUEST['DataCorsa'];
$Order_post=$_REQUEST['Order'];	
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

	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
            $j=$i;
		if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{     
			if ( $sWhere == "" )
			{
				$sWhere .= " AND ";
			}        
			
			
			if($aColumns[$i] == 'Cliente'){
				$temp = "(case
				            when (`tp`.`OccupaPosto` = 1) then concat(`pd`.`Cognome`, _latin1' ', `pd`.`Nome`)
				            else concat('Servizio: ', _latin1' ', `pd`.`Nome`)
				        end)";
			}
			if($aColumns[$i] == 'StatoPrenotazione'){
				$temp = "(case
				            when (`apps`.`PrenotazioneStato` = _latin1'Biglietto emesso') then _utf8'BE'
				            else _utf8'C'
				        end)";
			}
			if ($aColumns[$i] == 'RagioneSociale') {
				$temp = "`g`.`RagioneSociale`";
			}
				
			if ($aColumns[$i] == 'NumeroBiglietto') {
				$temp = "(case
				            when
				                (`rdc`.`Codice` is not null)
				            then
				                concat(convert( `rdc`.`Codice` using utf8),
				                        _utf8'/',
				                        cast(`rdc`.`Anno` as char (5) charset utf8))
				            else concat(convert( `rdc`.`CodicePrenotazione` using utf8),
				                    _utf8'/',
				                    cast(`rdc`.`PrenotazioneNumero` as char (10) charset utf8))
				        end)";
			}
			if ($aColumns[$i] == 'ClienteCellulare') {
				$temp = "concat(_latin1'+',
	                `p`.`ClienteCellularePrefisso`,
	                _latin1' ',
	                `p`.`ClienteCellulare`)";
			}
			if ($aColumns[$i] == 'DataPartenza') {
				$temp = "`pd`.`DataPartenza`";
			}
			if ($aColumns[$i] == 'OrarioPartenza') {
				$temp = "`pd`.`DataPartenza`";
			}
			if ($aColumns[$i] == 'ComunePartenza') {
				$temp = "`pd`.`ComunePartenza`";
			}
			if ($aColumns[$i] == 'ComuneArrivo') {
				$temp = "`pd`.`ComuneArrivo`";
			}
			if ($aColumns[$i] == 'TipoViaggio') {
				$temp = "(case
				            when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then _utf8'CS'
				            else _utf8'A/R'
				        end)";
			}
			if ($aColumns[$i] == 'Importo') {
				$temp = "(case
				            when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then round(`pd`.`Importo`, 2)
				            else round((`pb`.`PrezzoPax` + ((`pb`.`AumentoPax` - `pb`.`RiduzionePax`) / `pb`.`NumeroPax`)),
				                    2)
				        end)";
			}
			$sWhere .= $temp." LIKE '%".$db->escape( $_GET['sSearch_'.$i])."%' ";           
		}
               
	}
	
    
        if (($user->GestoreId!=1) and ($user->GestoreId!=2))
        {
	    	$sWhere.=" and GestoreIdRef IN ($InGestoreFigli) ";
        }
        else {
        	if ($sWhere=="")
            	$sWhere="and 1=1";
        }
        
        $sWhere.=" and pd.CorsaId=$CorsaId_post and pd.DataInizioItinerario='$DataCorsa_post' ";

        if($sOrder == ""){
		    if($Order_post == '1'){
		    	$sOrder = "order by pd.DataArrivo desc, pd.OrarioArrivo desc, pd.ComuneArrivo";
		    }else{
		    	$sOrder = "order by pd.DataPartenza, pd.OrarioPartenza, pd.ComunePartenza";
		    }
        }
	
	$sQuery = "
	    select
	        (case
	            when (`apps`.`PrenotazioneStato` = _latin1'Biglietto emesso') then _utf8'BE'
	            else _utf8'C'
	        end) AS `StatoPrenotazione`,
	        `g`.`RagioneSociale` AS `RagioneSociale`,
	        (case
	            when
	                (`rdc`.`Codice` is not null)
	            then
	                concat(convert( `rdc`.`Codice` using utf8),
	                        _utf8'/',
	                        cast(`rdc`.`Anno` as char (5) charset utf8))
				when (`rdc`.`Codice` is null AND `apps`.`PrenotazioneStato` = _latin1'Biglietto emesso') 
					then 
						(convert((SELECT concat(Codice,_utf8'/',Anno) FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = `p`.`PrenotazioneId` AND TipoTitolo = 'E') using utf8))
	                        
	            else concat(convert( `rdc`.`CodicePrenotazione` using utf8),
	                    _utf8'/',
	                    cast(`rdc`.`PrenotazioneNumero` as char (10) charset utf8))
	        end) AS `NumeroBiglietto`,
	        (case
	            when (`tp`.`OccupaPosto` = 1) then concat(`p`.`ClienteNome`, _latin1' (', `pd`.`Nome`, _latin1')')
	            else concat('Servizio: ', _latin1' ', `pd`.`Nome`)
	        end) AS `Cliente`,
	        concat(_latin1'+',
	                `p`.`ClienteCellularePrefisso`,
	                _latin1' ',
	                `p`.`ClienteCellulare`) AS `ClienteCellulare`,
	        `pd`.`OrarioPartenza` AS `OrarioPartenza`,
	        `pd`.`OrarioArrivo` AS `OrarioArrivo`,
	        `pd`.`DataArrivo` AS `DataArrivo`,
	        `pd`.`ComunePartenza` AS `ComunePartenza`,
	        `pd`.`ComuneArrivo` AS `ComuneArrivo`,
	        (case
	            when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then _utf8'CS'
	            else _utf8'A/R'
	        end) AS `TipoViaggio`,
	        (case
	            when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then round(`pd`.`Importo`, 2)
	            else round((`pb`.`PrezzoPax` + ((`pb`.`AumentoPax` - `pb`.`RiduzionePax`) / `pb`.`NumeroPax`)),
	                    2)
	        end) AS `Importo`,
	        `pd`.`CorsaId` AS `CorsaId`,
	        `pd`.`DataPartenza` AS `DataPartenza`,
	        `pd`.`PrenotazioneDettaglioId` AS `PrenotazioneDettaglioId`,
	        `pd`.`DataInizioItinerario` AS `DataInizioItinerario`,
	        `p`.`PrenotazioneId` AS PrenotazioneId
	    from
	            (((((((`RT_PrenotazioneDettaglio` `pd`
        left join `RT_Prenotazione` `p` ON ((`pd`.`PrenotazioneId` = `p`.`PrenotazioneId`)))
        left join `RT_AppPrenotazioneStato` `apps` ON ((`p`.`PrenotazioneStato` = `apps`.`PrenotazioneStatoId`)))
        left join `Operatore` `o` ON ((`p`.`OpeIns` = `o`.`OperatoreId`)))
        left join `Gestore` `g` ON ((`p`.`GestoreIdRef` = `g`.`GestoreId`)))
        left join `RT_PrenotazioneDettaglioCompleto` `rdc` ON (((`p`.`PrenotazioneId` = `rdc`.`PrenotazioneId`)
            and (`rdc`.`PrenotazioneId` = `pd`.`PrenotazioneId`)
            and (`rdc`.`PrenotazioneNumero` = `pd`.`PrenotazioneNumero`)
            and (`rdc`.`DataPartenza` = `pd`.`DataPartenza`)
			AND (`rdc`.`CorsaId` = `pd`.`CorsaId`))))
        left join `RT_PrenotazioneBiglietto` `pb` ON (((`pb`.`PrenotazioneId` = `p`.`PrenotazioneId`)
            and (`pb`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
        left join `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBigliettoId` = `pb`.`TipologiaBigliettoId`)
            )))  
	    where
	        (apps.OccupaPosti = 1 
	            and (`pd`.`Escludi` <> 1)
	            and (`pd`.`Rimborso` <> 1))
	            and `tp`.`OccupaPosto` = 1
	            and (`rdc`.Codice not like 'E-%' or `rdc`.Codice is null)
				 
		$sWhere 
		$sOrder
		$sLimit";
 	//echo $sQuery;
    $rResult = $db->fetch_array($sQuery);    
    
    
    $sQueryTot = "
    select
    COUNT(*) as Tot
    from
    (((((((`RT_PrenotazioneDettaglio` `pd`
        left join `RT_Prenotazione` `p` ON ((`pd`.`PrenotazioneId` = `p`.`PrenotazioneId`)))
        left join `RT_AppPrenotazioneStato` `apps` ON ((`p`.`PrenotazioneStato` = `apps`.`PrenotazioneStatoId`)))
        left join `Operatore` `o` ON ((`p`.`OpeIns` = `o`.`OperatoreId`)))
        left join `Gestore` `g` ON ((`o`.`GestoreId` = `g`.`GestoreId`)))
        left join `RT_PrenotazioneDettaglioCompleto` `rdc` ON (((`p`.`PrenotazioneId` = `rdc`.`PrenotazioneId`)
            and (`rdc`.`PrenotazioneId` = `pd`.`PrenotazioneId`)
            and (`rdc`.`PrenotazioneNumero` = `pd`.`PrenotazioneNumero`)
            and (`rdc`.`DataPartenza` = `pd`.`DataPartenza`)
			AND (`rdc`.`CorsaId` = `pd`.`CorsaId`))))
        left join `RT_PrenotazioneBiglietto` `pb` ON (((`pb`.`PrenotazioneId` = `p`.`PrenotazioneId`)
            and (`pb`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
        left join `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBigliettoId` = `pb`.`TipologiaBigliettoId`)
            )))  
    where
    (apps.OccupaPosti = 1
	            and (`pd`.`Escludi` <> 1)
	            and (`pd`.`Rimborso` <> 1))
    and `tp`.`OccupaPosto` = 1
    and (`rdc`.Codice not like 'E-%' or `rdc`.Codice is null)
    	
    $sWhere";
//      echo "$sQueryTot";
    $rResult2 = $db->fetch_array($sQueryTot);
       
	$output = array(
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => count($rResult),
			"iTotalDisplayRecords" => $rResult2[0]['Tot'],
			"aaData" => array()
	);
        
	foreach ($rResult as $aRow)
	{
    $row = array();
           
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
                
        if ( $aColumns[$i] == "PrenotazioneId") {
            $PrenotazioneId=$aRow[ $aColumns[$i] ];
            $CorsaId=$aRow['CorsaId'];
            /* General output */
            $row[] = "<a href=\"#\" onclick=\"ChiudiBox();loadMainContent('rt_biglietto','biglietto.php?do=edit&amp;CorsaId=".$CorsaId."&PrenotazioneId=".$PrenotazioneId."',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></a>";
        } elseif ( $aColumns[$i] == 'DataCorsa'){
            $sql = "SELECT RitornoAperto FROM RT_Corsa WHERE CorsaId = ".$aRow['CorsaId'];
            $rowTemp = $db->fetch_array($sql);
            if($rowTemp[0]['RitornoAperto'] == 1){
                $row[] = 'Open';
            } else {
                $row[] = $aRow[ $aColumns[$i] ];
            }
        } elseif ( $aColumns[$i] == "Cliente") {
            // Gestione specifica per Cliente con htmlentities
            $cliente = $aRow[$aColumns[$i]];
            $row[] = htmlentities($cliente, ENT_QUOTES, 'UTF-8');
        } elseif (( $aColumns[$i] != '' ) and ( $aColumns[$i] != 'CorsaId' )) {
            /* General output */
            $value = $aRow[$aColumns[$i]];
            // Applica htmlentities anche agli altri campi stringa per sicurezza
            if (is_string($value)) {
                $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
            }
            $row[] = $value;
        }
    }
           
    $output['aaData'][] = $row;
}

// Imposta l'header Content-Type per UTF-8
header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>
