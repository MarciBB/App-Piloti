<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$ModuloId=1;
$aColumns = array('PrenotazioneStato','RagioneSociale','CodicePrenotazione','PrenotazioneNumeroId','Codice','Anno','TipoTitolo','DataIns','ClienteNome','TipologiaBiglietto','ImportoTitolo','ImportoVenduto','PrenotazioneId','CorsaId','PrenotazioneTitoloId','PrenotazioneStatoId');
$sIndexColumn = "PrenotazioneId";
$sTable = "(select 
		`RT_Prenotazione`.`PrenotazioneStato` AS `PrenotazioneStatoId`,
        `RT_PrenotazioneNumero`.`PrenotazioneNumeroId` AS `PrenotazioneNumeroId`,
        `RT_PrenotazioneTitolo`.`Codice` AS `Codice`,
        `RT_PrenotazioneTitolo`.`Anno` AS `Anno`,
        `RT_PrenotazioneTitolo`.`Progressivo` AS `Progressivo`,
        (case
            when (`RT_PrenotazioneTitolo`.`TipoTitolo` = 'X') then 'E'
            else `RT_PrenotazioneTitolo`.`TipoTitolo`
        end) AS `TipoTitolo`,
        `RT_Prenotazione`.`CodicePrenotazione` AS `CodicePrenotazione`,
        `RT_Prenotazione`.`ClienteNome` AS `ClienteNome`,
        `RT_AppPrenotazioneStato`.`PrenotazioneStato` AS `PrenotazioneStato`,
        date_format(`RT_PrenotazioneTitolo`.`DataIns`,
                _utf8'%d/%m/%Y %H:%i:%s') AS `DataIns`,
        (case
            when (`RT_PrenotazioneTitolo`.`TipoTitolo` = 'X') then 'EXTRA'
            else `RT_PrenotazioneDettaglio`.`TipologiaBiglietto`
        end) AS `TipologiaBiglietto`,
        `RT_PrenotazioneTitolo`.`ImportoTitolo` AS `ImportoTitolo`,
		`RT_PrenotazioneTitolo`.`ImportoVenduto` AS `ImportoVenduto`,
        `RT_PrenotazioneNumero`.`OdcIdRef` AS `OdcIdRef`,
        `RT_PrenotazioneNumero`.`GestoreIdRef` AS `GestoreIdRef`,
        `Operatore`.`Username` AS `Username`,
        `Gestore`.`RagioneSociale` AS `RagioneSociale`,
        `RT_PrenotazioneDettaglio`.`CorsaInizioItinerario` AS `CorsaId`,
        `RT_PrenotazioneTitolo`.`PrenotazioneTitoloId` AS `PrenotazioneTitoloId`,
        `RT_PrenotazioneNumero`.`PrenotazioneId` AS `PrenotazioneId`,
		`RT_PrenotazioneTitolo`.`DataIns` AS `DataInsTitolo`
    from
        ((((((`RT_PrenotazioneNumero`
        join `RT_PrenotazioneTitolo` ON ((`RT_PrenotazioneNumero`.`PrenotazioneNumeroId` = `RT_PrenotazioneTitolo`.`PrenotazioneNumeroId`)))
        join `RT_Prenotazione` ON ((`RT_PrenotazioneTitolo`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)))
        join `RT_AppPrenotazioneStato` ON ((`RT_Prenotazione`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)))
        join `RT_PrenotazioneDettaglio` ON ((`RT_PrenotazioneNumero`.`PrenotazioneNumeroId` = `RT_PrenotazioneDettaglio`.`PrenotazioneNumero`)))
        join `Operatore` ON ((`RT_Prenotazione`.`OpeIns` = `Operatore`.`OperatoreId`)))
        join `Gestore` ON ((`RT_Prenotazione`.`GestoreIdRef` = `Gestore`.`GestoreId`)))
    group by `RT_PrenotazioneNumero`.`PrenotazioneNumeroId` , `RT_PrenotazioneTitolo`.`PrenotazioneTitoloId`
    order by `RT_PrenotazioneTitolo`.`DataIns` desc	
		) as RT_PrenotazioneListaTitoli";
$OdcIdRef=$user->OdcId;
$OperatoreTipoId=$user->OperatoreTipoId;
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");

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
				if($aColumns[ intval( $_GET['iSortCol_'.$i] ) ] == "DataIns") {
					$sOrder .= " DataInsTitolo ".$_GET['sSortDir_'.$i].", ";
				} else {
					$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".$db->escape( $_GET['sSortDir_'.$i] ) .", ";
				}
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
                         
			$sWhere .= $aColumns[$j]." LIKE '%".$db->escape( $_GET['sSearch_'.$i])."%' ";    

		}
               
	}
	
     /*
	 * SQL queries
	 * Get data to display
	 */
       if (($user->GestoreId!=1)) {
	        if ($sWhere=="")
	            $sWhere =" where GestoreIdRef IN ($InGestoreFigli) ";
	            
	        else
	            $sWhere.=" and GestoreIdRef IN ($InGestoreFigli) ";
	        }
		else {
        	if ($sWhere=="")
            	$sWhere="where 1=1";
        }
       
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
        
	$PrenotazioneId=0;
        $CorsaId=0;
    while ( $aRow = $db->fetch( $rResult ) )
	{
		 $row = array();
               
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
            if ( $aColumns[$i] == "PrenotazioneId") {
            	$PrenotazioneId=$aRow[ $aColumns[$i] ];
                $CorsaId=$aRow['CorsaId'];
				/* General output */
                $row[] = "<a href=\"#\" onclick=\"loadMainContent('rt_biglietto','biglietto.php?do=edit&amp;CorsaId=".$CorsaId."&PrenotazioneId=".$PrenotazioneId."',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></a>";
            } elseif ( $aColumns[$i] == "PrenotazioneStatoId") {
            	$stato = $aRow['PrenotazioneStatoId'];
            } elseif ( $aColumns[$i] == "ImportoTitolo") {
            	$row[]=  number_format($aRow['ImportoTitolo'],2,",",".");
            } elseif ( $aColumns[$i] == "ImportoVenduto") {
                $stato = $aRow['PrenotazioneStatoId'];
            	if ($stato != 4) {
            		$row[] =  number_format($aRow['ImportoVenduto'],2,",",".");
            	} else {
            		$row[] = number_format(0,2,",",".");
            	}
            } elseif ( $aColumns[$i] == "ClienteNome") {
            	// Gestione specifica per ClienteNome con htmlentities
            	$clienteNome = $aRow[$aColumns[$i]];
            	$row[] = htmlentities($clienteNome, ENT_QUOTES, 'UTF-8');
            } elseif (( $aColumns[$i] != '' ) and ( $aColumns[$i] != 'CorsaId' )  and ( $aColumns[$i] != 'PrenotazioneTitoloId' )) {
				/* General output */
				$value = $aRow[$aColumns[$i]];
				// Applica htmlentities anche agli altri campi stringa per sicurezza
				if (is_string($value)) {
					$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
				}
				$row[] = $value;
			}
		}
               
    $prenotazione_wizard=new Prenotazione($PrenotazioneId);
    $prenotazione_wizard->conn=$db;
    $prenotazione_wizard->inizializzaDatiGenerali();
    $titoli=$prenotazione_wizard->TitoliEmessiYesNo();
    
    $DatiGeneraliArr=$prenotazione_wizard->DatiGenerali;
    $prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
    $DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso;
    
    $CorsaId=$DatiGeneraliPercorsoArr['CorsaId'];
    $DataCorsa=$DatiGeneraliPercorsoArr['CorsaDataPartenza'];
        $titolo=$aRow['Codice'];    
    $titoloid=$aRow['PrenotazioneTitoloId']; 
    $tipo_titolo=$aRow['TipoTitolo']; 
    //$url="/protected/modules/rt_previaggio/stampa_titoli_di_viaggio.php?CorsaId=".$CorsaId."&DataPartenza=".$DataCorsa."&PrenotazioneId=".$PrenotazioneId."&PrenotazioneTitoloId=".$titoloid."&TipoTitolo=".$tipo_titolo;
   
    if ($titolo<>'')
    	$row[] = "<a href=\"#\" onclick=\"loadMainContent('rt_biglietto','biglietto.php?do=edit&amp;CorsaId=".$CorsaId."&PrenotazioneId=".$PrenotazioneId."&step=3',this);\" title=\"edita\"><img alt=\"stampa\" title=\"stampa\" src=/images/print.gif /></a>";
	else
    	$row[]=''; 
	 $output['aaData'][] = array_decode_list($row);
	}
      
	
	// Imposta l'header Content-Type per UTF-8
header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>
