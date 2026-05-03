<?php
/**
 classe depretaca e sostituita dal metodo getCorseOpt in biglietto_action
 **/
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
include_once($classespath_."class.Form.php");
include_once($classespath_."/class.Sede.php");

include_once($classespath_."/class.Fermata.php");
include_once($classespath_."/class.Tratta.php");
include_once($classespath_."/class.Prenotazione.php");
include_once($classespath_."/class.Corsa.php");
include_once($classespath_."/class.Orario.php");
include_once($classespath_."/class.Listino.php");
include_once($classespath_."/class.TipologiaBus.php");
include_once($classespath_."/class.TipologiaBus.php");
include_once($classespath_."class.DT.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once ($classespath_ . "Graph/class.GrafoTratte.php");
$ModuloId=1;
$aColumns = array('CorsaId','CorsaNome','LineaNome','DataPartenzaFormattata','AppSettimanaGiornoDescr','OrarioPartenza','PostiCorsaPrenotati','PostiDisponibili','AppCalendarioData','LineaId' );
$sIndexColumn = "CorsaId";
$sTable = "RT_ViewCorsePrenotabiliBooking";

$OdcIdRef=$user->OdcId;
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();

$prenotazione_wizard = null;

if (isset ( $_SESSION ['PRENOTAZIONE_WIZARD'] )) {
	$prenotazione_wizard = unserialize ( $_SESSION ['PRENOTAZIONE_WIZARD'] );
}
if(!isset($db)){
	$db = new Database();
	$db->connect();
}
$DataFiltroA=$_REQUEST['DataFiltroA'];
$DataFiltroR=$_REQUEST['DataFiltroR'];
$Tp=$_REQUEST['Tp'];
$storico=new StoricoOperazioni();
$storico->conn=$db;
/*cancello la vecchia ricerca*/
// $db->delete("DataRicercaTemp","UserId>0");
// $db->delete("RT_ComuniRicercaTemp","UserId>0");
/*prelevo i dati dalla richiesta*/
$CorsaId_post=$_REQUEST['Corsaid'];
$DataCorsa_post=$_REQUEST['DataCorsa'];

/*formattazione delle date*/
$dt=new DT();
if ($Tp=='A'){
	$DataInizio=$dt->format($DataFiltroA, "d/m/Y", "Y-m-d");
} else {
	$DataInizio=$dt->format($DataFiltroR, "d/m/Y", "Y-m-d");  
}
/*recupero dati se sto modificando/visualizzando una prenotazione*/
$ComuneSalitaId_post=0;
$ComuneDiscesaId_post=0;
$CorsaPrenotata_post=0;
$DataPrenotata_post=null;
if (is_object ( ($prenotazione_wizard) )) {
	$prenotazione_wizard->conn = $db;
    $prenotazione_wizard->inizializzaDatiGeneraliPercorso ( $Tp );
	$DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso;
	$ComuneSalitaId_post=$DatiGeneraliPercorsoArr['ComuneSalitaId'];
	$ComuneDiscesaId_post=$DatiGeneraliPercorsoArr['ComuneDiscesaId'];
	$CorsaPrenotata_post=$DatiGeneraliPercorsoArr['CorsaId'];
	$DataPrenotata_post=$DatiGeneraliPercorsoArr['CorsaDataPartenza'];
}

/*calcolo data fine della ricerca max 8 giorni dopo*/
$dt=new DT($DataInizio,"Y-m-d");
$n_limite=4;
$dt->addDays($n_limite);
$DataFine=$dt->getDate();

/*recupero comuni da form*/
if ($_REQUEST['ComunePartenzaId']>0){
	$ComunePartenzaId=$_REQUEST['ComunePartenzaId'];
}
if ($_REQUEST['ComuneDestinazioneId']>0){
	$ComuneDestinazioneId=$_REQUEST['ComuneDestinazioneId'];
}
if ($Tp=='R')
    {
    $ComuneDestinazioneId=$_REQUEST['ComunePartenzaId'];
    $ComunePartenzaId=$_REQUEST['ComuneDestinazioneId'];
}


/*cancello la vecchia ricerca ed avvio la nuova associata a questo utente*/
$db->delete("DataRicercaTemp","UserId=".$user->OperatoreId);
$db->delete("RT_ComuniRicercaTemp","UserId=".$user->OperatoreId);

$d1['UserId']=$user->OperatoreId;
$d1['ComunePkId']=$ComunePartenzaId;
$d1['ComuneDoId']=$ComuneDestinazioneId;

$d1=$storico->operazioni_insert($d1,$user);
$db->insert("RT_ComuniRicercaTemp",$d1);
          
if ($CorsaId_post>0){
	$BasicData=$DataCorsa_post;
    $d['DataRicerca']=$BasicData;
    $d['UserId']=$user->OperatoreId;
    $db->insert("DataRicercaTemp",$d);
} else {
	$BasicData=$DataInizio;
	$d['DataRicerca']=$BasicData;
	$d['UserId']=$user->OperatoreId;
	$db->insert("DataRicercaTemp",$d);
}
$BasicData=$DataInizio;
$q=0;
while ($q<$n_limite) {
	$d['DataRicerca']=$BasicData;
	$d['UserId']=$user->OperatoreId;
	$db->insert("DataRicercaTemp",$d);
	$dt=new DT($BasicData,"Y-m-d");
	$dt->addDays(1);
	$BasicData=$dt->getDate();
	$q++;
}
        	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".$db->escape( $_GET['iDisplayStart'] ).", ".
                $db->escape($n_limite);
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
            $sWhere =" where  OdcIdRef=$OdcIdRef and OpeIns=$user->OperatoreId and UserId=$user->OperatoreId";
        else
            $sWhere.=" and OdcIdRef=$OdcIdRef and OpeIns=$user->OperatoreId and UserId=$user->OperatoreId";
        
        $flag_stop_vendite=1;
        $sede=new Sede();
        $sede->conn=$db;
        $sede->inizializza($user->SedeId);
        $flag_stop_vendite=$sede->VenditaOltreOrario;
        
        if ($flag_stop_vendite==0)
            $sWhere.=" and OreResidue>0 ";
        
      /*  if ($CorsaId_post>0)
        {
            $sWhere.=" or (CorsaId=$CorsaId_post and AppCalendarioData='$DataCorsa_post')";
        }*/
        
    $sQuery = "
		SELECT distinct SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere 
               
		$sOrder
		$sLimit
	";
       
	$rResult = $db->fetch_array($sQuery);
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$aResultFilterTotal = $db->fetch_array($sQuery);
	$iFilteredTotal = $aResultFilterTotal[0]['FOUND_ROWS()'];
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
	";
	$aResultTotal = $db->fetch_array($sQuery);
	$iTotal = $aResultTotal[0]['COUNT(CorsaId)'];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);

    foreach ($rResult as $aRow) {
            $visualizza=false;
            
            $cId=$aRow['CorsaId'];
            $lId=$aRow['LineaId'];
            $dId=$aRow['AppCalendarioData'];
          
     	if (($cId==$CorsaPrenotata_post)  and ($ComunePartenzaId==$ComuneSalitaId_post)  and ($dId==$DataPrenotata_post)and ($ComuneDestinazioneId==$ComuneDiscesaId_post) ){
        	$visualizza=true;
		} else {
        	$trattaPartenza = null;
        	$trattaArrivo = null;
 
        	$sql="select * from RT_PercorsoBreve where ComunePickupId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId and CorsaId=$cId";     
			$r = $db->query_first($sql);
		
		if (!empty($r['PercorsoBreveId'])){
        	$trattaPartenza=$r['TrattaPickupId']; 
			$trattaArrivo=$r['TrattaDropOffId'];
        	$visualizza=true;
		}else{
			$grafo = new GrafoTratte($lId, $cId, $db, $ComunePartenzaId, $ComuneDestinazioneId);
            $TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);  
            $pre=new Prenotazione();
			$pre->conn=$db;
			$ritorno=$pre->CreatePercorsoBreve($ComunePartenzaId,$ComuneDestinazioneId,$db,$TrattePercorse,$trattaPartenza,$trattaArrivo,$cId,$lId);
        }
                
        $sql = "SELECT distinct FermataId,FermataOrario,TrattaNome From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$cId and ComuneId=$ComunePartenzaId and TrattaId=$trattaPartenza and TrattaStato=1 order by TrattaPeso desc ";    
        $arr_fermate=$db->fetch_array($sql);
		$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$cId and ComuneId=$ComuneDestinazioneId and TrattaId=$trattaArrivo  and TrattaStato=1 order by TrattaPeso asc";      
		$arr_fermate_d=$db->fetch_array($sql);
        if ((sizeof ( $arr_fermate )>0) and (sizeof($arr_fermate_d)>0)){
        	$visualizza=true;
        }else{
        	$visualizza=false;
        }
        
        
        $sql = "SELECT TratteNonVendibiliId from RT_TratteNonVendibili  WHERE ComunePickUpId=$ComunePartenzaId and ComuneDropOffId=$ComuneDestinazioneId  ";    
        echo($sql);
        
        $arr_esclusi=$db->fetch_array($sql);
         if ((sizeof ( $arr_esclusi )>0)){
             $visualizza=false;
         }
        
        
	}
            
    $row = array();
    if ($visualizza){
               
		for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
			if ( $aColumns[$i] == "CorsaNome") {
				$row[] = utf8_decode($aRow[ 'CorsaNome' ]);
			} else if ( $aColumns[$i] == "CorsaId") {
                $CorsaId=$aRow['CorsaId'];
                $DataPartenza=$aRow['AppCalendarioData'];
                            
                $dataodierna=Date('Y-m-d');
                $dt=new DT();
                $diff=$dt->compare($dataodierna,$DataPartenza,'Y-m-d');
                            
                $OrarioPartenza=$aRow['OrarioPartenza'];
                $name_input="Corsa[$CorsaId.]";
                $arr_field=$Tp."_".$CorsaId."_".$DataPartenza;
                $arr_field="";
                
				/* General output */
                $disponibili=$aRow['PostiDisponibili'];
                if (($disponibili>0) or ($CorsaId_post==$CorsaId) or ($user->SedeLegale==1)) {
                	$ck = "";
					if (($CorsaId_post == $CorsaId) and ($DataCorsa_post == $DataPartenza))
						$ck = "checked";
                    if($Tp == 'A'){
                    	$mod = "$('#modificaData').val(1);";
                    } else {
                    	$mod = "$('#modificaDataRitorno').val(1);";
                    }           
                    $row[] = "<input $ck type=\"radio\" name=\"Corsa$Tp\" onclick=\"javascript:ControllaDataPassata('$diff');$mod $('#CorsaSelezionata$Tp').val($CorsaId);$('#DataSelezionata$Tp').val('$DataPartenza');MostraFermate();\" />";
                 } else {
                 	$row[]='';
                 }
            } else if ( $aColumns[$i] == 'PostiCorsaPrenotati') {
                 $PostiPrenotati=$aRow['PostiCorsaPrenotati'];
                 $AppCalendarioData=$aRow['AppCalendarioData'];
                 if ($PostiPrenotati>0)
                 	$row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','prenotati_corsa.php?do=show_list&amp;CorsaId=".$CorsaId."&DataCorsa=".$AppCalendarioData."');\" title=\"elenco prenotati\">".$PostiPrenotati."</a>";
                 else
                 	$row[]= $PostiPrenotati;
             } elseif (( $aColumns[$i] != 'CorsaNome' ) and ( $aColumns[$i] != '' ) and ( $aColumns[$i] != 'PostiCorsaPrenotati' ) and ($aColumns[$i] != 'AppCalendarioData') and ($aColumns[$i] != 'LineaId')) {
				/* General output */
				$row[] = ($aRow[ $aColumns[$i] ]);
			 }
		}
        $output['aaData'][] = ($row);
    }
}
echo json_encode( $output );
        
        

?>
