<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 


$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

include_once($classespath_."/class.Sede.php");
include_once($classespath_."/class.Corsa.php");
include_once($classespath_."/class.Gestore.php");
include_once($classespath_."/class.Prenotazione.php");
include_once($classespath_ . "/class.ServiceFiscalGateway.php");
include_once($classespath_."class.Form.php");
include_once($classespath_."class.TipologiaBus.php");

include_once ($classespath_ . "Graph/class.DisponibilitaGraph.php");

$ModuloId=28;

function aggiornaDisponibilita($corsaId, $data){
	global $db;

	$sql = "SELECT
	rt_corsa.CorsaId AS CorsaId,
	rt_appcalendario.AppCalendarioData AS AppCalendarioData,
	DATE_FORMAT(rt_appcalendario.AppCalendarioData, _utf8 '%d/%m/%Y') AS DataPartenzaFormattata,
	rt_corsa.CorsaNome AS CorsaNome,
	rt_corsa.LineaId AS LineaId,
	rt_linea.LineaNome AS LineaNome,
	rt_corsa.OrarioPartenza AS OrarioPartenza,
	IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti),0,rt_viewsingolacorsapostiaggiunti.PostiAggiunti) AS PostiAggiunti,
	IF(ISNULL(rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati),0,rt_viewsingolacorsapostiprenotati.TotalePaxPrenotati) AS TotalePrenotati,
	IF(ISNULL(rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati),0,rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati) AS PostiRealmentePrenotati,
	(rt_tipologiabus.TotalePosti + IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti),0,rt_viewsingolacorsapostiaggiunti.PostiAggiunti)) AS PostiTotali,
	((rt_tipologiabus.TotalePosti + IF(ISNULL(rt_viewsingolacorsapostiaggiunti.PostiAggiunti),0,rt_viewsingolacorsapostiaggiunti.PostiAggiunti)) - IF(ISNULL(rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati),0,rt_viewsingolacorsapostirealmenteprenotati.TotalePaxPrenotati)) AS PostiRealmenteDisponibili,
	IF(ISNULL(rt_corsabloccoweb.CorsaBloccoId),0,1) AS CorsaWebBloccata,
	IF(ISNULL(rt_corsablocco.CorsaBloccoId),0,1) AS CorsaBloccata,
	rt_corsaconsolidamento.DataIns AS DataConsolidamentoF,
	rt_corsainiziopreparazione.DataIns AS DataInizializzazioneF,
	rt_corsa.OdcIdRef AS OdcIdRef,
	rt_corsa.GestoreIdRef AS GestoreIdRef
	FROM RT_Corsa AS rt_corsa
	JOIN RT_CorsaSettimana AS rt_corsasettimana ON(rt_corsa.CorsaId = rt_corsasettimana.CorsaId)
	JOIN RT_AppSettimana AS rt_appsettimana ON (rt_corsasettimana.SettimanaId = rt_appsettimana.AppSettimanaId)
	JOIN RT_AppCalendario AS rt_appcalendario ON (rt_appsettimana.AppSettimanaGiorno = rt_appcalendario.GiornoSettimana)
	JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
	LEFT JOIN (
	SELECT
	rt_corsapax.CorsaId AS CorsaId,
	rt_corsapax.DataPartenza AS DataPartenza,
	SUM(rt_corsapax.NumeroPax) AS PostiAggiunti,
	rt_corsapax.OdcIdRef AS OdcIdRef
	FROM RT_CorsaPax AS rt_corsapax
	WHERE rt_corsapax.Cancella = 0
	GROUP BY rt_corsapax.CorsaId, rt_corsapax.DataPartenza, rt_corsapax.OdcIdRef
	) AS rt_viewsingolacorsapostiaggiunti ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiaggiunti.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostiaggiunti.DataPartenza)
	LEFT JOIN (
	SELECT
	rt_prenotazionepercorso.CorsaId AS CorsaId,
	rt_prenotazionepercorso.CorsaDataPartenza AS CorsaDataPartenza,
	(COUNT(rt_prenotazione.PrenotazioneId) - SUM(rt_prenotazionedettaglio.Escludi)) AS TotalePaxPrenotati,
	rt_prenotazione.OdcIdRef AS OdcIdRef
	FROM RT_PrenotazionePercorso AS rt_prenotazionepercorso
	JOIN RT_Prenotazione AS rt_prenotazione ON (rt_prenotazionepercorso.PrenotazioneId = rt_prenotazione.PrenotazioneId)
	JOIN RT_PrenotazioneDettaglio AS rt_prenotazionedettaglio ON (rt_prenotazionepercorso.PrenotazioneId = rt_prenotazionedettaglio.PrenotazioneId AND rt_prenotazionedettaglio.ComunePartenza = rt_prenotazionepercorso.ComuneSalita AND rt_prenotazionedettaglio.PrenotazioneId = rt_prenotazione.PrenotazioneId)
	JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazionepercorso.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId)
	WHERE rt_prenotazione.Cancella = 0
	AND rt_prenotazionepercorso.Cancella = 0
	AND rt_prenotazionepercorso.Stato = 1
	AND rt_appprenotazionestato.OccupaPosti = 1
	AND (rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazionedettaglio.Escludi = 0)
	OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 1)
	OR (rt_prenotazione.PrenotazioneStato = 1 AND rt_prenotazione.Pagato = 0 AND rt_prenotazione.ScadenzaPrenotazione > rt_prenotazionedettaglio.DataPartenza)
	OR (rt_prenotazione.ABordo = 1 AND rt_prenotazione.PrenotazioneStato <> 6 AND rt_prenotazione.PrenotazioneStato <> 4 AND rt_prenotazione.PrenotazioneStato <> 7 AND rt_prenotazione.PrenotazioneStato <> 3)
	OR (rt_prenotazione.PrenotazioneStato = 3 AND rt_prenotazione.ABordo = 1 AND rt_prenotazionedettaglio.Escludi = 0)
	GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
	) AS rt_viewsingolacorsapostiprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostiprenotati.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostiprenotati.CorsaDataPartenza)
	LEFT JOIN (
	SELECT
	rt_prenotazionepercorso.CorsaId AS CorsaId,
	rt_prenotazionepercorso.CorsaDataPartenza AS CorsaDataPartenza,
	SUM(rt_prenotazione.TotalePaxPrenotati - rt_prenotazionepercorso.PasseggeriEsclusi) AS TotalePaxPrenotati,
	rt_prenotazione.OdcIdRef AS OdcIdRef
	FROM RT_Prenotazione AS rt_prenotazione
	JOIN RT_PrenotazionePercorso AS rt_prenotazionepercorso ON (rt_prenotazione.PrenotazioneId = rt_prenotazionepercorso.PrenotazioneId)
	JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazionepercorso.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId)
	WHERE rt_prenotazione.Cancella = 0
	AND rt_prenotazionepercorso.Cancella = 0
	AND rt_prenotazionepercorso.Stato = 1
	AND rt_appprenotazionestato.OccupaPosti = 1
	GROUP BY rt_prenotazionepercorso.CorsaDataPartenza, rt_prenotazionepercorso.CorsaId, rt_prenotazionepercorso.OdcIdRef
	) AS rt_viewsingolacorsapostirealmenteprenotati ON (rt_corsa.CorsaId = rt_viewsingolacorsapostirealmenteprenotati.CorsaId AND rt_appcalendario.AppCalendarioData = rt_viewsingolacorsapostirealmenteprenotati.CorsaDataPartenza)
	JOIN RT_TipologiaBus AS rt_tipologiabus ON (rt_corsa.TipologiaBusDefaultId = rt_tipologiabus.TipologiaBusId)
	LEFT JOIN RT_CorsaBloccoWeb AS rt_corsabloccoweb ON (rt_corsa.CorsaId = rt_corsabloccoweb.CorsaId AND rt_appcalendario.AppCalendarioData = rt_corsabloccoweb.DataPartenza)
	LEFT JOIN RT_CorsaBlocco AS rt_corsablocco ON (rt_corsa.CorsaId = rt_corsablocco.CorsaId AND rt_appcalendario.AppCalendarioData = rt_corsablocco.DataPartenza)
	LEFT JOIN RT_CorsaInizioPreparazione AS rt_corsainiziopreparazione ON(rt_corsa.CorsaId = rt_corsainiziopreparazione.CorsaId AND rt_appcalendario.AppCalendarioData = rt_corsainiziopreparazione.DataCorsa)
	LEFT JOIN RT_CorsaConsolidamento AS rt_corsaconsolidamento ON (rt_corsa.CorsaId = rt_corsaconsolidamento.CorsaId AND rt_appcalendario.AppCalendarioData = rt_corsaconsolidamento.DataCorsa)
	WHERE rt_corsa.CorsaId = $corsaId and rt_appcalendario.AppCalendarioData = '$data'";

	$corsa=$db->query_first($sql);


	$sql = "DELETE FROM RT_DisponibilitaPostiCron WHERE LineaId = ".$corsa['LineaId']." and CorsaId = ".$corsaId." and DataPartenza = '".$data."'";
	$db->query($sql);

	$grafo = new DisponibilitaGraph($corsa['LineaId'], $corsaId, $data, $db);
	foreach($grafo->graph->nodes as $k=>$pickup){
		$sql = "select t.TrattaId, t.TrattaPeso, KmInizioTratta from RT_Fermata f
				left join RT_Tratta t on t.TrattaId = f.TrattaId
	 			left join RT_Orario o on o.FermataId = f.FermataId
				where t.LineaId = ".$corsa['LineaId']." and f.ComuneId = $k
				and f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
				and o.CorsaId = $corsaId and o.Orario is not null and o.Stato = 1 and o.Cancella = 0
				order by TrattaPeso asc, f.ImportanzaTratta desc";
		$tempRow = $db->query_first($sql);
		if(isset($grafo->gruppiDispo[$k])){
			$sql = "INSERT INTO RT_DisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti, TrattaId, PesoTratta, KmInizioTratta) VALUES
					(".$corsa['LineaId'].",".$corsaId.", '".$data."',".$k.",
							".$grafo->gruppiDispo[$k]->totalePasseggeri.",  ".$tempRow['TrattaId'].", ".$tempRow['TrattaPeso'].", ".$tempRow['KmInizioTratta'].")";
			$db->query($sql);
		} else {
			$tot = 0;
			$sql = "INSERT INTO RT_DisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti, TrattaId, PesoTratta, KmInizioTratta) VALUES
					(".$corsa['LineaId'].",".$corsaId.", '".$data."',".$k.",
					$tot, ".$tempRow['TrattaId'].", ".$tempRow['TrattaPeso'].", ".$tempRow['KmInizioTratta'].")";
			$db->query($sql);
		}
	}


	$totPostiBus = $corsa['PostiTotali'];
	$postiRP=$corsa['PostiRealmentePrenotati'];
	$tot1=0;
	$sql = "DELETE FROM RT_MaxDisponibilitaPostiCron WHERE LineaId = ".$corsa['LineaId']." and CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'";
	$r=$db->query($sql);


	foreach($grafo->graph->nodes as $k=>$pickup){
		if(isset($grafo->gruppiDispo[$k])){
			$tot=$grafo->gruppiDispo[$k]->totalePasseggeri;
			if ($tot>$tot1)
				$tot1=$tot;
			// 			$tot = $grafo->gruppiDispo[$k]->totalePasseggeri + $grafo->graph->nodes[$k]->salite;


		}
	}

	if ($tot1>0){
		$sql = "select * from RT_DisponibilitaPostiCron
		where Posti = $tot1 and CorsaId = ".$corsa['CorsaId']." and
        			DataPartenza = '".$corsa['AppCalendarioData']."'
        	order by PesoTratta desc";
		$row2 =  $db->query_first($sql);
		$peso = $row2['PesoTratta'];

		$km = $row2['KmInizioTratta'];
		$trattaId = $row2['TrattaId'];
		if(isset($peso)){
			$sql = "SELECT Max(Posti) as postiM, c.* FROM RT_DisponibilitaPostiCron c
				where CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'
				and PesoTratta = $peso
				and c.Posti > 0 and c.Posti<>". $tot1 ." and TrattaId <> $trattaId group by TrattaId
				order by postiM desc";

			$row3 = $db->fetch_array($sql);
			$postiOccupatiTratta = 0;

			foreach ($row3 as $num => $val){
				if($val['postiM']<$tot1/2){
					$sql = "SELECT Posti, KmInizioTratta FROM RT_DisponibilitaPostiCron c
							where CorsaId = ".$corsa['CorsaId']." and DataPartenza = '".$corsa['AppCalendarioData']."'
							and TrattaId  = ".$val['TrattaId']."
							and c.Posti = ".$val['postiM']."
							group by TrattaId";

					$row4 = $db->fetch_array($sql);

					if($row4[0]['KmInizioTratta']>$km-50 && $row4[0]['KmInizioTratta']<$km+50)
						$postiOccupatiTratta += $row4[0]['Posti'];
				}
			}

			$tot1 += $postiOccupatiTratta;

		}

		$sql = "INSERT INTO RT_MaxDisponibilitaPostiCron (LineaId, CorsaId, DataPartenza, Comune, Posti,PostiRP) VALUES
					(".$corsa['LineaId'].",".$corsa['CorsaId'].", '".$corsa['AppCalendarioData']."',".$k.",
							".$tot1.",".$postiRP." )";
		$db->query($sql);

	}
}

function AggiungiPax() {
    global $user;
    $db= new Database();
    $db->connect();
    $storico=new StoricoOperazioni();
    $storico->conn=$db; 
    
    $data=$_POST['CorsaPaxTratta'];
    $pax = $data['NumeroPax'];
    
    if($pax < 0) {
    	
    	$pax = $pax * (-1);
    	$trattaId = $data['TrattaId'];
    	$corsaId = $data['CorsaId'];
    	$dataPartenza = $data['DataPartenza'];
    
    	$sql = "select TotalePosti from RT_Tratta t
    	left join RT_TipologiaBus b on b.TipologiaBusId = t.TipologiaBusDefaultId
    	Where TrattaId = $trattaId";
    	$row = $db->query_first($sql);
    	$postiDefault = $row['TotalePosti'];
    	if(!isset($postiDefault)) {
    		$corsa=new Corsa($corsaId);
    		$corsa->conn=$db;
    		$corsa->inizializzaDatiGenerali();
    		$arr_corsa=$corsa->DatiGenerali;
    		$CorsaNome=$arr_corsa['CorsaNome'];
    		$BusDefault=$arr_corsa['TipologiaBusDefaultId'];
    			
    		$tipologiabus=new TipologiaBus($BusDefault);
    		$tipologiabus->conn=$db;
    		$tipologiabus->inizializzaDatiGenerali();
    		$arr_tipologiabus=$tipologiabus->DatiGenerali;
    		$postiDefault=$arr_tipologiabus['TotalePosti'];
    	}
    
    	$sql = "SELECT MAX(Posti) as Occupati FROM RT_DisponibilitaPostiCron
    	WHERE TrattaId = $trattaId AND CorsaId = $corsaId AND DataPartenza = '$dataPartenza'";
    	$row = $db->query_first($sql);
    	$postiOccupati = $row['Occupati'];
    	if(!isset($postiOccupati)) {
    		$postiOccupati = 0;
    	}
    	$sql = "select SUM(NumeroPax) as NumeroPax from RT_CorsaPaxTratta 
    			where TrattaId = $trattaId and CorsaId = $corsaId and DataPartenza = '$dataPartenza'";
    	$row = $db->query_first($sql);
    	$postiAggiunti = $row['NumeroPax'];
    	if(!isset($postiAggiunti)) {
    		$postiAggiunti = 0;
    	}
    	
    	$postiDisponibili = $postiDefault-$postiOccupati+$postiAggiunti;
    
    
    	if($pax > $postiDisponibili) {
    		$insert = false;
    	} else {
    		$insert = true;
    	}
    } else {
    	$insert = true;
    }
       
    if($insert){
    	$data=$storico->operazioni_insert($data,$user);
    	 
    	$lastidA=$db->insert("RT_CorsaPaxTratta", $data);
    	
    	aggiornaDisponibilita($corsaId, $dataPartenza);
    	
    	echo("ok");
    	exit();
    } else {
    	echo("E' possibile sottrarre al massimo ".$postiDisponibili." posti!,-1");
    	exit();
    }
    
    
   
}

/**
 * Funzione per bloccare o sbloccare una corsa.
 */
function BloccaCorsa()
{
	global $user;
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	// Recupera i dati dalla richiesta
	$CorsaId = $_REQUEST['CorsaId'];
	$CorsaData = $_REQUEST['CorsaData'];
	$Operazione = $_REQUEST['Stato'];
	$rimborso = $_REQUEST['rimborso'];

	// Inizializza l'oggetto Corsa
	$corsa = new Corsa($CorsaId);
	$corsa->conn = $db;
	$corsa->inizializzaDatiGenerali();
	$odccorsa = $corsa->DatiGenerali['OdcIdRef'];

	// Verifica se l'utente ha i permessi per modificare la corsa
	if ($odccorsa == $user->OdcId) {
		if ($Operazione == 0) { // Disattiva la corsa
			$data['CorsaId'] = $CorsaId;
			$data['DataPartenza'] = $CorsaData;
			$data = $storico->operazioni_insert($data, $user);
			$lastidA = $db->insert("RT_CorsaBlocco", $data);

			//emetti il rimborso per tutte le prenotazioni della corsa selezionata
			
			if($rimborso == 1) {
				$sql = "SELECT 
								t.PrenotazioneNumeroId, t.PrenotazioneId, t.ImportoVenduto
							FROM
								RT_PrenotazioneDettaglio d
									LEFT JOIN
								RT_PrenotazioneTitolo t ON t.PrenotazioneNumeroId = d.PrenotazioneNumero
							WHERE
								d.CorsaId = $CorsaId
									AND d.DataInizioItinerario = '$CorsaData'
									AND d.Escludi = 0
									AND d.Rimborso = 0
									AND t.PrenotazioneTitoloId IS NOT NULL";
				$rows = $db->fetch_array($sql);
				foreach($rows as $row) {
					RimborsoParziale($row['PrenotazioneNumeroId'], $row['ImportoVenduto'], $row['PrenotazioneId'], $db, $storico);
				}
			}

		} elseif ($Operazione == 1) { // Attiva la corsa
			$result = $db->delete("RT_CorsaBlocco", "OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$CorsaData'");
		}
		echo("ok");
	} else {
		echo("no");
	}
}





function BloccaCorsaWeb()
{
    
    global $user;
    $db= new Database();
    $db->connect();
    $storico=new StoricoOperazioni();
    $storico->conn=$db;
    
    $CorsaId=$_REQUEST['CorsaId'];
    $CorsaData=$_REQUEST['CorsaData'];
    $Operazione=$_REQUEST['Stato'];
    
    $corsa=new Corsa($CorsaId);
    $corsa->conn=$db;
    $corsa->inizializzaDatiGenerali();
    $odccorsa=$corsa->DatiGenerali['OdcIdRef'];
    
    if ($odccorsa==$user->OdcId)
    {
    
    if ($Operazione==0) //disattiva
    {
        $data['CorsaId']=$CorsaId;
         $data['DataPartenza']=$CorsaData;
         $data=$storico->operazioni_insert($data,$user);
       $lastidA=$db->insert("RT_CorsaBloccoWeb", $data); 
        
    }
        
    elseif ($Operazione==1) //attiva 
    {
        $result=$db->delete("RT_CorsaBloccoWeb","OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$CorsaData'");
        
        
    }
    echo("ok");
    }
    echo("no");
    
}


function calendarioUpdate() {
	global $HtmlCommon,$user, $dizionario;
	if(isset($_GET['anno'])){
		$anno = $_GET['anno'];
	} else {
		$anno = date('Y');
	}

	if(isset($_GET['mese'])){
		$mese = $_GET['mese'];
	} else {
		$mese = date('m');
	}

	$db = new Database();
	$db->connect();
	$page = new Form();

	include_once("corsapartenza_validator.php");
	
	$sql = "select * from RT_AppCalendario
	where YEAR(AppCalendarioData) = $anno and month(AppCalendarioData) = $mese";
	$calendario = $db->fetch_array($sql);

	$mesi = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile',
			'Maggio', 'Giugno', 'Luglio', 'Agosto',
			'Settembre', 'Ottobre', 'Novembre','Dicembre');
	if($mese == 1){
		$mese_pre = 12;
		$anno_pre = $anno - 1;
		$mese_suc = $mese + 1;
		$anno_suc = $anno;
	} elseif($mese == 12) {
		$mese_pre = $mese - 1;
		$anno_pre = $anno;
		$mese_suc = 1;
		$anno_suc = $anno + 1;
	} else {
		$mese_pre = $mese - 1;
		$anno_pre = $anno;
		$mese_suc = $mese + 1;
		$anno_suc = $anno;
	}
	?>
	<form id="application_form" name="application_form" method="post" action="#">
                   
                         <div class="brain_formModifica">
                         	
                         	<table id="brain_datatables" class="display">
                         		<thead>
                         			<tr class="brain_tabellaTr">
                         				<th colspan="7">
	                         				<h2 style="text-align: center; color:white;">
                         				<a style="color:white;" href="#" onclick="javascript:calendario(<?php echo $mese_pre.",".$anno_pre?>);"> << </a>
                         				<?php echo $mesi[$mese-1]." ".$anno;?>
                         				<a style="color:white;" href="#" onclick="javascript:calendario(<?php echo $mese_suc.",".$anno_suc?>);"> >> </a>
	                         				</h2>
                         				</th>
                         			</tr>
                         			<tr class="brain_tabellaTr">
                         				<th><?php echo $dizionario['generale']['lunedi'];?></th>
                         				<th><?php echo $dizionario['generale']['martedi'];?></th>
                         				<th><?php echo $dizionario['generale']['mercoledi'];?></th>
                         				<th><?php echo $dizionario['generale']['giovedi'];?></th>
                         				<th><?php echo $dizionario['generale']['venerdi'];?></th>
                         				<th><?php echo $dizionario['generale']['sabato'];?></th>
                         				<th><?php echo $dizionario['generale']['domenica'];?></th>
                         			</tr>
                         		</thead>
                         		<tbody>
                               <?php
                               $num = 0;
                               foreach($calendario as $k => $day){
                               		
                               		if($k == 0 && ($day['GiornoSettimana'] > 1 || $day['GiornoSettimana'] == 0)){
                               			echo "<tr>";
                               			if($day['GiornoSettimana'] == 0){
                               				$fine = 6;
                               			} else {
                               				$fine = $day['GiornoSettimana']-1;
                               			}
                               			for($ii = 0; $ii < $fine; $ii++ ) {
                               				echo "<td style='width:15%;'></td>";
                               				$num++;
                               			}
                               		}
                               			if($num == 0){
                               				echo "<tr>";
                               			}
                               			$r = getCalendario($day['AppCalendarioData']);
                               			
                               			echo "<td style='width:15%;vertical-align: top;'><b>".date('d', strtotime($day['AppCalendarioData']))."</b><br>";
                               			echo "<div style='text-align:center;'>";
                               			foreach($r as $c){
                               				echo "<div style='text-align:center; border: 1px solid; #000'>";
                               				$blocco="bloccare";
                               				$bloccospan = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\"></i>";
                               				if ($c['CorsaBloccata']==1){
                               					$blocco="Sbloccare";
                               					$bloccospan = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\"></i>";
                               				}
                               				$bloccoweb="bloccare";
                               				$bloccowebspan = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\"></i>";
                               				if ($c['CorsaWebBloccata']==1){
                               					$bloccoweb="Sbloccare";
                               					$bloccowebspan = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\"></i>";
                               				}
                               				echo "<b>".$c['CorsaNome']."</b><br>";
                               				echo "<table style='width:100%; border: 0px;'><tr><td colspan='2' style='text-align:center;'>operatore</td><td colspan='2' style='text-align:center;'>web</td></tr><tr>";
                               				echo "<td style='text-align:center;'>".$bloccospan."</td>";
                               				echo "<td style='text-align:center;'><a href=\"#\" onclick=\"javascript:BloccaSbloccaCorsa(".$c['CorsaBloccata'].",".$c['CorsaId'].",'".$c['CorsaNome']."','".$c['AppCalendarioData']."','".$c['OrarioPartenza']."','$blocco', true, '$mese', '$anno');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a></td>";
                               				echo "<td style='text-align:center;'>".$bloccowebspan."</td>";
                               				echo "<td style='text-align:center;'><a href=\"#\" onclick=\"javascript:BloccaSbloccaCorsaWeb(".$c['CorsaWebBloccata'].",".$c['CorsaId'].",'".$c['CorsaNome']."','".$c['AppCalendarioData']."','".$c['OrarioPartenza']."','$bloccoweb', true, '$mese', '$anno');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a></td>";
                               				echo "</tr></table></div>";
                               			}
                               			echo "</div></td>";
                               			if($num == 6){
                               				echo "</tr>";
                               				$num = 0;
                               			} else {
                               				$num++;
                               			}
                               		
                               } ?>
                               </tbody>
                             </table> 
                         </div>
                             
                   </form>
                                
	<?
	exit();
}
function getCalendario($data){
	global $db,$user;
	$OdcIdRef=$user->OdcId;

	$sql1= "select
	c.`CorsaId` AS `CorsaId`,
	appcal.`AppCalendarioData` AS `AppCalendarioData`,
	date_format(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`,
	c.`CorsaNome` AS `CorsaNome`,
	c.`LineaId` AS `LineaId`,
	`RT_Linea`.`LineaNome` AS `LineaNome`,
	c.`OrarioPartenza` AS `OrarioPartenza`,
	if(isnull(`RT_CorsaBloccoWeb`.`CorsaBloccoId`), 0, 1) AS `CorsaWebBloccata`,
	if(isnull(`RT_CorsaBlocco`.`CorsaBloccoId`), 0, 1) AS `CorsaBloccata`,
	if(isnull(`RT_MaxDisponibilitaPostiCron`.`Posti`), 0, `RT_MaxDisponibilitaPostiCron`.`Posti`) AS `MaxPostiOccupati`
	from `RT_Corsa` c join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
	join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
	join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
	join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
	join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
	left join `RT_CorsaBloccoWeb` ON (c.`CorsaId` = `RT_CorsaBloccoWeb`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaBloccoWeb`.`DataPartenza`)
	left join `RT_CorsaBlocco` ON (c.`CorsaId` = `RT_CorsaBlocco`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaBlocco`.`DataPartenza`)
	left join `RT_CorsaInizioPreparazione` ON (c.`CorsaId` = `RT_CorsaInizioPreparazione`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaInizioPreparazione`.`DataCorsa`)
	left join `RT_CorsaConsolidamento` ON (c.`CorsaId` = `RT_CorsaConsolidamento`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaConsolidamento`.`DataCorsa`)
	left join `RT_MaxDisponibilitaPostiCron` ON (appcal.`AppCalendarioData` = `RT_MaxDisponibilitaPostiCron`.`DataPartenza` and c.`CorsaId` = `RT_MaxDisponibilitaPostiCron`.`CorsaId`)
	where ((c.`Cancella` = 0) and (appcal.`AppCalendarioData` >= c.`AttivaDal`) and (appcal.`AppCalendarioData` <= c.`AttivaAl`) and ((appcal.`Feriale` = c.`IncludiFeriale`) or (appcal.`Prefestivo` = c.`IncludiPrefestivo`) or (appcal.`Festivo` = c.`IncludiFestivo`))) and c.`OdcIdRef`=1
	and appcal.`AppCalendarioData`='$data'
	group by c.`CorsaId` , appcal.`AppCalendarioData` order by appcal.`AppCalendarioData` , `RT_Linea`.`PercorsoId` , `RT_Linea`.`LineaNome` , c.`CorsaNome`
	";
	$calendario = $db->fetch_array($sql1);

	return $calendario;
}

function TrasferisciCorsa() {
	global $user;
    $db= new Database();
    $db->connect();
    $storico=new StoricoOperazioni();
    $storico->conn=$db;

	// Recupero parametri e validazione
	$CorsaIdOld = isset($_POST['CorsaIdOld']) ? $_POST['CorsaIdOld'] : null;
	$DataPartenzaOld = isset($_POST['DataPartenzaOld']) ? $_POST['DataPartenzaOld'] : null;
	$CorsaId = isset($_POST['NuovaCorsaId']) ? $_POST['NuovaCorsaId'] : null;
	$DataPartenzaF = isset($_POST['DataPartenza']) ? $_POST['DataPartenza'] : null;
	$dataDateTime = $DataPartenzaF ? DateTime::createFromFormat('d/m/Y', $DataPartenzaF) : false;
	$DataPartenza = $dataDateTime ? $dataDateTime->format('Y-m-d') : $DataPartenzaF;
	$modalita = isset($_POST['ModalitaTrasferimento']) ? $_POST['ModalitaTrasferimento'] : (isset($_POST['modalita_trasferimento']) ? $_POST['modalita_trasferimento'] : 'trasferisci');

	// ERRORE 1: Parametri mancanti
	if (!$CorsaIdOld || !$DataPartenzaOld || !$CorsaId || !$DataPartenza) {
		echo json_encode(array('success' => false, 'error' => 'Parametri mancanti', 'code' => 'ERR_MISSING_PARAMS'));
		exit();
	}

	// ERRORE 2: Nessuna modifica (dati uguali)
	if ($CorsaIdOld == $CorsaId && $DataPartenzaOld == $DataPartenza) {
		echo json_encode(array('success' => false, 'error' => 'Nessuna modifica: dati identici', 'code' => 'ERR_NO_CHANGE'));
		exit();
	}

	// 0) recupero le corse
	$sql1 = "SELECT * FROM RT_Corsa WHERE CorsaId = $CorsaIdOld";
	$corsaOld = $db->query_first($sql1);
	$sql2 = "SELECT * FROM RT_Corsa WHERE CorsaId = $CorsaId";
	$corsaNew = $db->query_first($sql2);


	// 1) Se modalità scambia, recupera anche per la nuova corsa
	$prenotazioniPercorsoNew = array();
	$prenotazioniDettaglioNew = array();
	if ($modalita == 'scambia') {
		$sql3 = "SELECT * FROM RT_PrenotazionePercorso WHERE CorsaId = $CorsaId AND CorsaDataPartenza = '$DataPartenza'";
		$prenotazioniPercorsoNew = $db->fetch_array($sql3);
		$sql4 = "SELECT * FROM RT_PrenotazioneDettaglio WHERE CorsaId = $CorsaId AND DataInizioItinerario = '$DataPartenza'";
		$prenotazioniDettaglioNew = $db->fetch_array($sql4);
	}

	// ERRORE 3: Nessuna prenotazione trovata per la corsa vecchia
	$sqlCheckPren1 = "SELECT COUNT(*) as cnt FROM RT_PrenotazionePercorso WHERE CorsaId = $CorsaIdOld AND CorsaDataPartenza = '$DataPartenzaOld'";
	$resCheckPren1 = $db->query_first($sqlCheckPren1);
	$sqlCheckPren2 = "SELECT COUNT(*) as cnt FROM RT_PrenotazioneDettaglio WHERE CorsaId = $CorsaIdOld AND DataInizioItinerario = '$DataPartenzaOld'";
	$resCheckPren2 = $db->query_first($sqlCheckPren2);
	if ((int)$resCheckPren1['cnt'] == 0 && (int)$resCheckPren2['cnt'] == 0) {
		echo json_encode(array('success' => false, 'error' => 'Nessuna prenotazione trovata per la corsa da trasferire', 'code' => 'ERR_NO_BOOKINGS'));
		exit();
	}

	// Recupera i dati dalla nuova corsa per RT_PrenotazionePercorso
	$CorsaNomeNew = isset($corsaNew['CorsaNome']) ? $db->escape($corsaNew['CorsaNome']) : '';
	$CorsaOrarioPartenzaNew = isset($corsaNew['OrarioPartenza']) ? $corsaNew['OrarioPartenza'] : '';
	$DataOraSalitaNew = $DataPartenza . ' ' . (isset($corsaNew['OrarioPartenza']) ? $corsaNew['OrarioPartenza'] : '');
	$DataOraDiscesaNew = $DataPartenza . ' ' . (isset($corsaNew['OrarioArrivo']) ? $corsaNew['OrarioArrivo'] : '');

	$sqlUpdate1 = "UPDATE RT_PrenotazionePercorso SET 
		CorsaId = $CorsaId, 
		CorsaDataPartenza = '$DataPartenza',
		CorsaNome = '$CorsaNomeNew',
		CorsaOrarioPartenza = '$CorsaOrarioPartenzaNew',
		DataOraSalita = '$DataOraSalitaNew',
		DataOraDiscesa = '$DataOraDiscesaNew'
		WHERE CorsaId = $CorsaIdOld AND CorsaDataPartenza = '$DataPartenzaOld'";
	$db->query($sqlUpdate1);

	// Recupera i dati dalla nuova corsa
	$DataPartenzaNew = isset($corsaNew['DataPartenza']) ? $corsaNew['DataPartenza'] : $DataPartenza;
	$OrarioPartenzaNew = isset($corsaNew['OrarioPartenza']) ? $corsaNew['OrarioPartenza'] : '';
	$DataArrivoNew = isset($corsaNew['DataArrivo']) ? $corsaNew['DataArrivo'] : '';
	$OrarioArrivoNew = isset($corsaNew['OrarioArrivo']) ? $corsaNew['OrarioArrivo'] : '';
	$CorsaNomeNew = isset($corsaNew['CorsaNome']) ? $db->escape($corsaNew['CorsaNome']) : '';

	$sqlUpdate2 = "UPDATE RT_PrenotazioneDettaglio SET 
		CorsaId = $CorsaId, 
		DataInizioItinerario = '$DataPartenza',
		DataPartenza = '$DataPartenzaNew',
		OrarioPartenza = '$OrarioPartenzaNew',
		DataArrivo = '$DataArrivoNew',
		OrarioArrivo = '$OrarioArrivoNew',
		CorsaNome = '$CorsaNomeNew'
		WHERE CorsaId = $CorsaIdOld AND DataInizioItinerario = '$DataPartenzaOld'";
	$db->query($sqlUpdate2);

	// 3) Se modalità scambia, aggiorna solo le prenotazioni effettivamente trovate negli array
	if ($modalita == 'scambia') {
		$scambiato = false;
		// RT_PrenotazionePercorso
		if(count($prenotazioniPercorsoNew) > 0) {
			$CorsaNomeOld = isset($corsaOld['CorsaNome']) ? $db->escape($corsaOld['CorsaNome']) : '';
			$CorsaOrarioPartenzaOld = isset($corsaOld['OrarioPartenza']) ? $corsaOld['OrarioPartenza'] : '';
			$DataOraSalitaOld = $DataPartenzaOld . ' ' . (isset($corsaOld['OrarioPartenza']) ? $corsaOld['OrarioPartenza'] : '');
			$DataOraDiscesaOld = $DataPartenzaOld . ' ' . (isset($corsaOld['OrarioArrivo']) ? $corsaOld['OrarioArrivo'] : '');
			foreach ($prenotazioniPercorsoNew as $row) {
				$id = isset($row['PrenotazionePercorsoId']) ? $row['PrenotazionePercorsoId'] : null;
				if ($id) {
					$sqlUpdate3 = "UPDATE RT_PrenotazionePercorso SET 
						CorsaId = $CorsaIdOld, 
						CorsaDataPartenza = '$DataPartenzaOld',
						CorsaNome = '$CorsaNomeOld',
						CorsaOrarioPartenza = '$CorsaOrarioPartenzaOld',
						DataOraSalita = '$DataOraSalitaOld',
						DataOraDiscesa = '$DataOraDiscesaOld'
						WHERE PrenotazionePercorsoId = $id";
					$db->query($sqlUpdate3);
					$scambiato = true;
				}
			}
		}
		// RT_PrenotazioneDettaglio
		if(count($prenotazioniDettaglioNew) > 0) {
			// Recupera i dati dalla corsa vecchia
			$DataPartenzaOldVal = isset($corsaOld['DataPartenza']) ? $corsaOld['DataPartenza'] : $DataPartenzaOld;
			$OrarioPartenzaOld = isset($corsaOld['OrarioPartenza']) ? $corsaOld['OrarioPartenza'] : '';
			$DataArrivoOld = isset($corsaOld['DataArrivo']) ? $corsaOld['DataArrivo'] : '';
			$OrarioArrivoOld = isset($corsaOld['OrarioArrivo']) ? $corsaOld['OrarioArrivo'] : '';
			$CorsaNomeOld = isset($corsaOld['CorsaNome']) ? $db->escape($corsaOld['CorsaNome']) : '';
			foreach ($prenotazioniDettaglioNew as $row) {
				$id = isset($row['PrenotazioneDettaglioId']) ? $row['PrenotazioneDettaglioId'] : null;
				if ($id) {
					$sqlUpdate4 = "UPDATE RT_PrenotazioneDettaglio SET 
						CorsaId = $CorsaIdOld, 
						DataInizioItinerario = '$DataPartenzaOld',
						DataPartenza = '$DataPartenzaOldVal',
						OrarioPartenza = '$OrarioPartenzaOld',
						DataArrivo = '$DataArrivoOld',
						OrarioArrivo = '$OrarioArrivoOld',
						CorsaNome = '$CorsaNomeOld'
						WHERE PrenotazioneDettaglioId = $id";
					$db->query($sqlUpdate4);
					$scambiato = true;
				}
			}
		}
		// ERRORE 4: Nessuna prenotazione da scambiare trovata
		if (!$scambiato) {
			echo json_encode(array('success' => false, 'error' => 'Nessuna prenotazione trovata per la corsa di scambio', 'code' => 'ERR_NO_BOOKINGS_SWAP'));
			exit();
		}
	}

	// Successo: salva lo storico trasferimento/scambio
	$CorsaId_1 = (int)$CorsaIdOld;
	$DataPartenza_1 = $db->escape($DataPartenzaOld);
	$CorsaId_2 = (int)$CorsaId;
	$DataPartenza_2 = $db->escape($DataPartenza);
	$OpeIns = isset($user->OperatoreId) ? (int)$user->OperatoreId : 1;
	$Modalita = $db->escape($modalita);
	$sqlInsert = "INSERT INTO RT_CorsaTrasferimento
		(CorsaId_1, DataPartenza_1, CorsaId_2, DataPartenza_2, DataIns, OpeIns, Modalita)
		VALUES ($CorsaId_1, '$DataPartenza_1', $CorsaId_2, '$DataPartenza_2', NOW(), $OpeIns, '$Modalita')";
	$db->query($sqlInsert);

	echo json_encode(array('success' => true));
	exit();
}

function CambiaCorsa() {
	global $user;
    $db= new Database();
    $db->connect();
    $storico=new StoricoOperazioni();
    $storico->conn=$db; 

	//recupero le informazioni da request post
    $CorsaIdOld = $_POST['CorsaIdOld'];
	$DataPartenzaOld = $_POST['DataPartenzaOld'];
	$OrarioPartenzaOld = $_POST['OrarioPartenzaOld'];
	$OrarioArrivoOld = $_POST['OrarioArrivoOld'];
	$BarcaOld = $_POST['BarcaOld'];
	$dataDateTime = DateTime::createFromFormat('d/m/Y', $_POST['DataPartenza']);
	$DataPartenzaF = $_POST['DataPartenza'];
	$DataPartenza = $dataDateTime->format('Y-m-d');
	$OrarioPartenza = $_POST['OrarioPartenza'];
	$OrarioArrivo = $_POST['OrarioArrivo'];
	$Barca = $_POST['Barca'];
	
	// Calcolo la differenza tra i due orari
	$dateTimeOrarioPartenzaOld = DateTime::createFromFormat('H:i:s', $OrarioPartenzaOld);
	$dateTimeOrarioPartenza = DateTime::createFromFormat('H:i', $OrarioPartenza);
	$differenzaOrario = $dateTimeOrarioPartenzaOld->diff($dateTimeOrarioPartenza);
	
	//verifico se i dati nuovi sono diversi dalla corsa precedente
	if($DataPartenzaOld == $DataPartenza && $OrarioPartenzaOld == $OrarioPartenza.":00" && $OrarioArrivoOld == $OrarioArrivo.":00" && $BarcaOld == $Barca) {
		echo "-1";
		exit();
	}
	
	//recupero la corsa da sostituire
	$sql = "Select * from RT_Corsa where CorsaId = ".$CorsaIdOld;
	$corsaOld = $db->query_first($sql);
	
	//recupero tipologia bus dalla flotta
	$sql = "SELECT * FROM RT_Flotta WHERE FlottaId = $Barca";
	$flotta = $db->query_first($sql);
	$TipologiaBusDefaultId = $flotta['TipologiaBusId'];
	
	//duplico la corsa

	/********Inizio duplico la corsa ************/
		//creazione corsa
		$corsaLibera = array();
		$corsaLibera['LineaId'] = $corsaOld['LineaId'];
		$corsaLibera['CorsaNome'] = 'Cambio giorno ' . $DataPartenzaF;
		$corsaLibera['FlottaDefaultId'] = $Barca;
		$corsaLibera['TipologiaBusDefaultId'] = $TipologiaBusDefaultId;
		$corsaLibera['CorsaPeso'] = 1;
		$corsaLibera['AttivaDal'] = $DataPartenza;
		$corsaLibera['AttivaAl'] = $DataPartenza;
		$corsaLibera['VendibileDal'] = $DataPartenza;
		$corsaLibera['VendibileAl'] = $DataPartenza;
		$corsaLibera['OrePrimaStopVendita'] = '00:30:00';
		$corsaLibera['IncludiFeriale'] = 1;
		$corsaLibera['IncludiPrefestivo'] = 1;
		$corsaLibera['IncludiFestivo'] = 1;
		$corsaLibera['OrarioPartenza'] = $OrarioPartenza;
		$corsaLibera['OrarioArrivo'] = $OrarioArrivo;
		$corsaLibera['NextDay'] = 0;
		$corsaLibera = $storico->operazioni_insert($corsaLibera, $user);
		$CorsaAndata = $db->insert("RT_Corsa", $corsaLibera);
		//inserisco i giorni della settimana della corsa
		for ($i = 1; $i <= 7; $i++) {
			$corsaSettLibera = array();
			$corsaSettLibera['CorsaId'] = $CorsaAndata;
			$corsaSettLibera['SettimanaId'] = $i;
			$corsaSettLibera = $storico->operazioni_insert($corsaSettLibera, $user);
			$db->insert("RT_CorsaSettimana", $corsaSettLibera);
		}
		
		//inserimento orari corsa
		$sql = "select * from RT_Orario where CorsaId = ".$CorsaIdOld;
		$orari = $db->fetch_array($sql);
		foreach($orari as $orario){
			$tempOrario = DateTime::createFromFormat('H:i:s', $orario['Orario']);
			$tempOrario = $tempOrario->add($differenzaOrario);
			$temp = array();
			$temp['Orario'] = $tempOrario->format('H:i:s');
			$temp['GiorniAggiuntivi'] = $orario['GiorniAggiuntivi'];
			$temp['CorsaId'] = $CorsaAndata;
			$temp['FermataId'] = $orario['FermataId'];
			$temp = $storico->operazioni_insert($temp, $user);
			$db->insert("RT_Orario", $temp);
		}
		
		//inserimento tariffa
		$sql = "select * from RT_CorsaTariffa where CorsaId = ".$CorsaIdOld;
		$orari = $db->fetch_array($sql);
		foreach($orari as $orario) {
			$temp = array();
			$temp['CorsaId'] = $CorsaAndata;
			$temp['TipologiaBigliettoId'] = $orario['TipologiaBigliettoId'];
			$temp['FermataPickup'] = $orario['FermataPickup'];
			$temp['FermataDropOff'] = $orario['FermataDropOff'];
			$temp['TrattaId'] = $orario['TrattaId'];
			$temp['ListinoId'] = $orario['ListinoId'];
			$temp['Tariffa'] = $orario['Tariffa'];
			$temp = $storico->operazioni_insert($temp, $user);
			$db->insert("RT_CorsaTariffa", $temp);
		}
		
		//inserimento validita biglietto
		$sql = "select * from RT_ValiditaBiglietto where CorsaId = ".$CorsaIdOld;
		$rows = $db->fetch_array($sql);
		foreach($rows as $row) {
			$data = array();
			$data['Dal'] = $row['Dal'];
			$data['Al'] = $row['Al'];
			$data['CorsaId'] = $CorsaAndata;
			$data = $storico->operazioni_insert($data, $user);
			$idTemp = $db->insert("RT_ValiditaBiglietto", $data);
			
			$sql="select * from RT_ValiditaBigliettoDettaglio where ValiditaBigliettoId = ".$row['ValiditaBigliettoId'];
			$rows2 = $db->fetch_array($sql);
			foreach($rows2 as $row2) {
			$data = array();
				$data['BigliettoId'] = $row2['BigliettoId'];
				$data['ValiditaBigliettoId'] = $idTemp;
				$data = $storico->operazioni_insert($data, $user);
				$db->insert("RT_ValiditaBigliettoDettaglio", $data);
			}	
		}
		
		/********FINE Duplicazione corsa ************/
	
	//blocco la vecchia corsa
	if($corsaOld['LineaId'] == 14) {
		$data = array();
		$data['CorsaId'] = $CorsaIdOld;
		$data['DataPartenza'] = $DataPartenzaOld;
		$data = $storico->operazioni_insert($data, $user);
		$db->insert("RT_CorsaBlocco", $data);
		$data = array();
		$data['CorsaId'] = $CorsaIdOld;
		$data['DataPartenza'] = $DataPartenzaOld;
		$data = $storico->operazioni_insert($data, $user);
		$db->insert("RT_CorsaBloccoWeb", $data);
	}
	
	//porto tutte le prenotazioni dalla vecchia corsa alla nuova
	$sql = "select * from RT_PrenotazioneDettaglio WHERE CorsaId = ".$CorsaIdOld." AND DataInizioItinerario = '".$DataPartenzaOld."'";
	$rows = $db->fetch_array($sql);
	foreach($rows as $row){
		$tempOrarioP = DateTime::createFromFormat('H:i:s', $row['OrarioPartenza']);
		$tempOrarioP = $tempOrarioP->add($differenzaOrario);
		$tempOrarioA = DateTime::createFromFormat('H:i:s', $row['OrarioArrivo']);
		$tempOrarioA = $tempOrarioA->add($differenzaOrario);
		$data = array();
		$data['CorsaId'] = $CorsaAndata;
		$data['CorsaNome'] = $corsaLibera['CorsaNome'];
		$data['DataInizioItinerario'] = $DataPartenza;
		$data['CorsaInizioItinerario'] = $CorsaAndata;
		$data['DataPartenza'] = $DataPartenza;
		$data['OrarioPartenza'] = $tempOrarioP->format('H:i:s');
		$data['DataArrivo'] = $DataPartenza;
		$data['OrarioArrivo'] = $tempOrarioA->format('H:i:s');
		$data = $storico->operazioni_update($data, $user);
		$db->update("RT_PrenotazioneDettaglio", $data, "PrenotazioneDettaglioId = ".$row['PrenotazioneDettaglioId']);
	}
	$sql = "select * from RT_PrenotazionePercorso WHERE CorsaId = ".$CorsaIdOld." AND CorsaDataPartenza = '".$DataPartenzaOld."'";
	$rows = $db->fetch_array($sql);
	foreach($rows as $row){
		$tempDataOraSalita = DateTime::createFromFormat('Y-m-d H:i:s', $row['DataOraSalita']);
		$tempDataOraSalita = $tempDataOraSalita->add($differenzaOrario);
		$tempDataOraDiscesa = DateTime::createFromFormat('Y-m-d H:i:s', $row['DataOraDiscesa']);
		$tempDataOraDiscesa = $tempDataOraDiscesa->add($differenzaOrario);
		$data = array();
		$data['CorsaId'] = $CorsaAndata;
		$data['CorsaNome'] = $corsaLibera['CorsaNome'];
		$data['CorsaDataPartenza'] = $DataPartenza;
		$data['CorsaOrarioPartenza'] = $OrarioPartenza;
		$data['DataOraSalita'] = $DataPartenza . " " . $tempDataOraSalita->format('H:i:s');
		$data['DataOraDiscesa'] = $DataPartenza . " " . $tempDataOraDiscesa->format('H:i:s');
		$data = $storico->operazioni_update($data, $user);
		$db->update("RT_PrenotazionePercorso", $data, "PrenotazionePercorsoId = ".$row['PrenotazionePercorsoId']);
		
		//RT_PrenotazioneTratta
	}
	
	echo "ok";
	exit;
}

function creaNomeTratta($partenzaId, $destinazioneId)
{
	global $db;
	$partenza = getComune($partenzaId);
	$destinazione = getComune($destinazioneId);

	return $partenza['Comune'] . " - " . $destinazione['Comune'];
}

function calcolaOre($orarioPartenza, $orarioArrivo)
{
	// Crea oggetti DateTime per le due variabili
	$partenzaDateTime = new DateTime($orarioPartenza);
	$arrivoDateTime = new DateTime($orarioArrivo);

	// Calcola la differenza tra le due date
	$differenza = $partenzaDateTime->diff($arrivoDateTime);

	// Ottieni il numero totale di ore dalla differenza
	$oreTotali = $differenza->h;

	return $oreTotali;
}

function getComune($comuneId)
{
	global $db;
	$sql = "Select * FROM Comune WHERE ComuneId = $comuneId";
	$comune = $db->query_first($sql);
	return $comune;
}

function RimborsoParziale($PrenotazioneNumeroId, $ImportoRimborsabile, $prenotazioneId, $db, $storico) {
	//     ini_set('display_errors', 1);
	//     ini_set('error_reporting', E_ALL);

	global $user, $dizionario;

	$prenotazione_wizard = new Prenotazione($prenotazioneId);
	$prenotazione_wizard->conn = $db;

	//$storico = new StoricoOperazioni();
	//$storico->conn = $db;

	$dt = new DT();

	$Tragitto = 0;
	$ImportoRimborsabile = $ImportoRimborsabile * (-1);
	$TipoMovimento = 'R';
	$PagamentoTipoId = 12;
	$DataRimborso = date('Y-m-d H:i:s', time());
	//controllo emissione coupon
	$sql = "SELECT EmettiCoupon FROM RT_PagamentoTipo Where PagamentoTipoId = $PagamentoTipoId";
	$emettiCoupon = $db->query_first($sql);
	$codice = '';

	//recupero info rimuovi da google calendar
	$googleCalendarRimuovi = 1;

	if($googleCalendarRimuovi == 1) {
		$dup = null;
		$dup['Escludi'] = 1;
		$dup['Rimborso'] = 1;
		$dup = $storico->operazioni_update($dup, $user);

		$db->update("RT_PrenotazioneDettaglio", $dup, "PrenotazioneId=$prenotazioneId");
	}

	$sql = "select PrenotazioneId, TipologiaBigliettoId, PasseggeroId from RT_PrenotazioneNumero where PrenotazioneNumeroId=$PrenotazioneNumeroId";
	$row1 = $db->query_first($sql);

	$PrenotazioneId = $row1['PrenotazioneId'];
	$idMovimento = null;
	if ($PrenotazioneId > 0) {
		$TipologiaBigliettoId = $row1['TipologiaBigliettoId'];
		$PasseggeroId = $row1['PasseggeroId'];

		$dup = null;
		$dup['PrenotazioneId'] = $PrenotazioneId;
		$dup['TipologiaBigliettoId'] = $TipologiaBigliettoId;
		$dup['PasseggeroId'] = $PasseggeroId;
		$dup = $storico->operazioni_insert($dup, $user);
		$NuovaPrenotazioneNumero = $db->insert("RT_PrenotazioneNumero", $dup);

		$sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId = $PrenotazioneNumeroId";
		$titolo = $db->query_first($sql);

		$movimento = array();
		$movimento['PrenotazioneId'] = $PrenotazioneId;
		$movimento['TipoMovimento'] = $TipoMovimento;
		$movimento['PagamentoTipoId'] = $PagamentoTipoId;
		$CausaleTragitto = ($Tragitto == '0') ? 'Andata e Ritorno' : $Tragitto;
		$movimento['Causale'] = "Rimborso titolo " . $titolo['Codice'] . " tragitto " . $CausaleTragitto;
		if ($emettiCoupon['EmettiCoupon'] == 1) {
			$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
			$random_string_length = 10;
			$codiceCoupon = '';
			for ($i = 0; $i < $random_string_length; $i++) {
				$codiceCoupon .= $characters[rand(0, strlen($characters) - 1)];
			}
			$movimento['Causale'] .= '<br>Codice coupon di rimborso: ' . $codiceCoupon;
			$movimento['Coupon'] = $codiceCoupon;
		}
		$movimento['Data'] = $DataRimborso;
		$movimento['Importo'] = $ImportoRimborsabile;
		$movimento['Supplemento'] = 0;
		$movimento['DataPagamento'] = $DataRimborso;
		$movimento['ImportoPagato'] = $ImportoRimborsabile;
		$movimento['Scadenza'] = 'NULL';
		$movimento['CodicePagamento'] = 'NULL';
		$movimento['CanalePagamentoId'] = 'NULL';
		$movimento = $storico->operazioni_insert($movimento, $user);
		$idMovimento = $db->insert("RT_PrenotazioneMovimento", $movimento);
	}

	$sql = "select * from RT_PrenotazioneDettaglio where PrenotazioneNumero=$PrenotazioneNumeroId ";
	if ($Tragitto != '0')
		$sql .= " and Tragitto='$Tragitto'";

	$sql .= " order by TipoServizio desc";

	$ArrObject = $db->fetch_array($sql);
	$x = sizeof($ArrObject);
	$y = 0;

	$ImportoRimborsabilePerTratta = $ImportoRimborsabile;
	$ImportoResiduo = $ImportoRimborsabile;
	$arrTragitto[$Tragitto] = $ImportoResiduo;
	if ($Tragitto == '0') {
		$arrTragitto['Andata'] = $ImportoResiduo / 2;
		$arrTragitto['Ritorno'] = $ImportoResiduo / 2;
		$ImportoRimborsabilePerTratta = $ImportoRimborsabile / 2;
	}

	while ($y < $x) {
		$row = null;
		$row = $ArrObject[$y];
		$TipoServizio = $row['TipoServizio'];
		$CorsaId = $row['CorsaInizioItinerario'];
		if ($TipoServizio == 'Bus') {
			//$CorsaId=$row['CorsaInizioItinerario'];
			$DataInizio = $row['DataInizioItinerario'];
			$PrenotazioneId = $row['PrenotazioneId'];

			$sql = "Select PrenotazionePercorsoId,PasseggeriEsclusi from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and CorsaDataPartenza='$DataInizio' and CorsaId=$CorsaId";
			$row1 = $db->query_first($sql);

			$sql = "SELECT t.OccupaPosto FROM RT_PrenotazioneNumero n
					left join RT_tipologiaBiglietto t on t.TipologiaBigliettoId = n.TipologiaBigliettoId
					where n.PrenotazioneNumeroId = $PrenotazioneNumeroId";
			$rowTempPosto = $db->query_first($sql);
			if ($rowTempPosto['OccupaPosto'] == 1) {
				$esclusi = $row1['PasseggeriEsclusi'] + 1;
			} else {
				$esclusi = $row1['PasseggeriEsclusi'];
			}
			$PrenotazionePercorsoId = $row1['PrenotazionePercorsoId'];

			if($googleCalendarRimuovi == 1) {
				$dup = null;
				$dup['PasseggeriEsclusi'] = $esclusi;
				$dup = $storico->operazioni_update($dup, $user);
				$db->update("RT_PrenotazionePercorso", $dup, "PrenotazionePercorsoId=$PrenotazionePercorsoId");
			}
		}

		$row['PrenotazioneDettaglioId'] = null;
		unset($row['PrenotazioneDettaglioId']);
		$row['PrenotazioneNumero'] = $NuovaPrenotazioneNumero;
		$ImportoOldPrenotazione = $row['Importo'];
		$TragittoCorrente = $row['Tragitto'];
		$row['Importo'] = $ImportoRimborsabilePerTratta;
		$Venduto = $row['Importo'];
		$s = "select LineaId from RT_Corsa where CorsaId=$CorsaId";
		$rows = $db->query_first($s);

		$LineaId = $rows['LineaId'];
		$sql33 = "select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and LineaId=$LineaId";

		$row33 = $db->query_first($sql33);
		$PercAge = 0;
		$FissoAge = 0;
		if ($row33['GestoreConvenzioneId'] > 0) {
			$PercAge = $row33['Percentuale'];
			$FissoAge = $row33['Fisso'];
		}

		$ImportoBase = $Venduto * (-1);
		$ImportoAgenziaNetto = number_format($ImportoBase * ($PercAge / 100) + $FissoAge, 4);

		if ($user->GestoreId == 1) {
			$row['DaBonificare'] = 0;
			$row['DaFatturare'] = 0;
		}

		$row['Rimborso'] = 1;
		$row['Escludi'] = 1;

		$row = $storico->operazioni_insert($row, $user);
		$db->insert("RT_PrenotazioneDettaglio", $row);
		$del = $db->delete("RT_PrenotazionePosto", "PrenotazioneNumeroId=$PrenotazioneNumeroId and CorsaId=$CorsaId");

		$y++;
	}

	$PrenotazioneObj = new Prenotazione($PrenotazioneId);
	$PrenotazioneObj->conn = $db;
	$codice = $PrenotazioneObj->EmettiBigliettiRimborso($NuovaPrenotazioneNumero, $ImportoRimborsabile);
	if (isset($idMovimento)) {
		$movimento['Causale'] .= '- Titolo ' . $codice;
		$db->update("RT_PrenotazioneMovimento", $movimento, "PrenotazioneMovimentoId=" . $idMovimento);
	}

	// setta lo stato rimborsato se solo se � completamente rimborsata e si decide di rimuovere il tour
	if($googleCalendarRimuovi == 1) {
		$PrenotazioneObj->isRimborsata($PrenotazioneId);
	}

	$modifica = 0;


	//Emissione Coupon
	var_dump($emettiCoupon);
	if ($emettiCoupon['EmettiCoupon'] == 1) {
		$sql = "SELECT * 
			FROM RT_PrenotazioneTitolo Where 
			PrenotazioneId = $PrenotazioneId and PrenotazioneNumeroId = $NuovaPrenotazioneNumero and TipoTitolo = 'R'";
		$titoloInfo = $db->query_first($sql);
		$coupon['CouponNome'] = 'Rimborso per biglietto ' . $titoloInfo['Codice'] . '/' . $titoloInfo['Anno'];
		$coupon['Importo'] = -$ImportoRimborsabile;
		$coupon['MaxUtilizzi'] = 1;
		$coupon['Valore'] = $coupon['Importo'];
		$coupon['Utilizzi'] = 0;
		$coupon['Codice'] = $codiceCoupon;
		$coupon['TitoloRimborsatoId'] = $titoloInfo['PrenotazioneTitoloId'];
		$coupon = $storico->operazioni_insert($coupon, $user);
		var_dump($coupon);
		$result = $db->insert("RT_Coupon", $coupon);
	}

	/**controllo disponibilita posti**/
	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoA = $prenotazione_wizard->DatiGeneraliPercorso;
	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
	$DatiGeneraliPercorsoR = $prenotazione_wizard->DatiGeneraliPercorso;
	aggiornaDisponibilita($DatiGeneraliPercorsoA['CorsaId'], $DatiGeneraliPercorsoA['CorsaDataPartenza']);
	if (isset($DatiGeneraliPercorsoR['CorsaDataPartenza'])) {
		aggiornaDisponibilita($DatiGeneraliPercorsoR['CorsaId'], $DatiGeneraliPercorsoR['CorsaDataPartenza']);
	}
	/**fine controllo disponibilita posti**/

	//annulla ricevuta emessa con fiscal gateway
	if(isset($idMovimento)) {
		//se è stata emessa una ricevuta con fiscal gateway viene annullata ed il metodo gestisce già il caso di ricevuta non emessa
		$annullataRicevuta = fiscalGatewayAnnullaRicevuta($idMovimento);
	}
	
	return true;
}

function fiscalGatewayAnnullaRicevuta($movimentoId){
	global $db;
	$service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);
	
	//recupero movimento
	$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
	$movimento = $db->query_first($sql);
	
	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
	$prenotazione = $db->query_first($sql);
	
	//recupero ordine id da annullare
	$sql = "SELECT * FROM RT_PrenotazioneMovimento
		WHERE
			PrenotazioneId = ".$movimento['PrenotazioneId']." 
				AND TipoMovimento = 'I'
				AND PrenotazioneMovimentoId < ".$movimento['PrenotazioneMovimentoId']." 
				AND ScontrinoId IS NOT NULL";
	$orderRow = $db->query_first($sql);
	//se non è presente lo scontrino ritorno false e non emette la nota di credito
	if(!isset($orderRow['ScontrinoId'])) {
		return false;
	}
	$orderId = strval($orderRow['ScontrinoId']);
	
	//invio richiesta annullamento ricevuta con Fiscal Gateway
	$result = $service->deleteBill($orderId);
	//memorizzo l'annullamento della ricevuta digitale
	if (isset($result['status_code'], $result['response']['success']) &&
		$result['status_code'] === 200 &&
		$result['response']['success'] === true) {
		//salvataggio info scontrino in PrenotazioneMovimento
		$data = [
			'ScontrinoIdAnnullato' => $orderId,
			'ScontrinoDataAnnullato' => date("Y-m-d H:i:s"),
		];
		$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
		if(abs($movimento['ImportoPagato']) < abs($orderRow['ImportoPagato'])) {
			//invio su fiscal gateway la differenza dell'importo totale e del rimborso crendo un nuovo scontrino
			//recupero id dell'ordine scontrino
			$newOrderId = $service->getNumeroOrdine($db);

			//recupero tipo pagamento
			$paymentMethodsType = $service->getTipoPagamento($orderRow['PagamentoTipoId']);

			//recupero importo
			$amount = (int) round(abs(floatval($orderRow['ImportoPagato']) - abs($movimento['ImportoPagato'])) * 100);

			//recupero prodotto
			$productId = $prenotazione['CodicePrenotazione'];

			//invio richiesta emissione ricevuta con Fiscal Gateway
			$result = $service->postBillReceipt($newOrderId, $paymentMethodsType, $amount, $productId, Config::$fiscalGatewayVAT);
			if (isset($result['status_code'], $result['response']['success']) &&
				$result['status_code'] === 200 &&
				$result['response']['success'] === true) {
					//salvataggio info scontrino in PrenotazioneMovimento
					$data = [
						'ScontrinoId' => $newOrderId,
						'ScontrinoData' => date("Y-m-d H:i:s"),
						'ScontrinoTipo' => '1',
						'ScontrinoInvioAuto' => $orderRow['ScontrinoInvioAuto'],
					];
					$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
					if($orderRow['ScontrinoInvioAuto'] == 1) {
						$dataMovimento = array();
						$dataMovimento['ScontrinoNotifica'] = 1;
						$updInviaTitolo=$db->update('RT_PrenotazioneMovimento', $dataMovimento,"PrenotazioneMovimentoId = ".$movimentoId);
					}
			}
		}
		return true;
	} else {
		return false;
	}
}



if (is_object($user)) {
	$db = new Database();
	$db->connect();
	$user->conn = $db;
	$permessi = $user->get_permessi_modulo($ModuloId);

	if (sizeof($permessi) > 0) {
		if (!empty($_POST)) {
			// Gestione azione di massa blocca/sblocca corse
			if (isset($_POST['mass_action']) && isset($_POST['corse'])) {
				$FunzioneId = 2;
				$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
				if (sizeof($permesso)) {
					$action = $_POST['mass_action'];
					$corse = json_decode($_POST['corse'], true);
					if (is_array($corse)) {
						if ($action === 'blocca') {
							bloccaCorseMassiva($corse);
						} elseif ($action === 'sblocca') {
							sbloccaCorseMassivo($corse);
						} elseif ($action === 'blocca_web') {
							bloccaCorseWebMassiva($corse);
						} elseif ($action === 'sblocca_web') {
							sbloccaCorseWebMassiva($corse);
						}
					}
					echo json_encode(['success' => true]);
					exit;
				} else {
					echo json_encode(['success' => false, 'error' => 'Permessi insufficienti']);
					exit;
				}
			}
			// ...gestione azioni classiche...
			switch ($_POST['action']) {
				case "AggiungiPax":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso)) {
						AggiungiPax();
					} else {
						Errors::$ErrorePermessiModuloFunzione;
					}
					break;

				case "CambiaCorsa":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso)) {
						CambiaCorsa();
					} else {
						Errors::$ErrorePermessiModuloFunzione;
					}
					break;

				case "TrasferisciCorsaSubmit":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso)) {
						TrasferisciCorsa();
					} else {
						Errors::$ErrorePermessiModuloFunzione;
					}
					break;
					
			}
		} elseif (!empty($_REQUEST)) {
			switch ($_REQUEST['do']) {
				case "BloccaCorsa":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso)) {
						BloccaCorsa();
					} else {
						Errors::$ErrorePermessiModuloFunzione;
					}
					break;

				case "BloccaCorsaWeb":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso)) {
						BloccaCorsaWeb();
					} else {
						Errors::$ErrorePermessiModuloFunzione;
					}
					break;

				case "calendario":
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
					if (sizeof($permesso)) {
						calendarioUpdate();
					} else {
						Errors::$ErrorePermessiModuloFunzione;
					}
					break;
			}
		}
	} else {
		Errors::$ErrorePermessiModulo;
	}
} else {
	header("Location: /logout.php");
}



// Funzioni per blocco/sblocco di massa
function bloccaCorseMassiva($corse) {
	global $db;
	foreach ($corse as $corsa) {
		$CorsaId = $db->pulisci($corsa['CorsaId']);
		$DataPartenza = $db->pulisci($corsa['DataPartenza']);
		$sql = "INSERT IGNORE INTO RT_CorsaBlocco (CorsaId, DataPartenza) VALUES ('".$CorsaId."', '".$DataPartenza."')";
		$db->query($sql);
	}
}

function sbloccaCorseMassivo($corse) {
	global $db;
	foreach ($corse as $corsa) {
		$CorsaId = $db->pulisci($corsa['CorsaId']);
		$DataPartenza = $db->pulisci($corsa['DataPartenza']);
		$sql = "DELETE FROM RT_CorsaBlocco WHERE CorsaId = '".$CorsaId."' AND DataPartenza = '".$DataPartenza."'";
		$db->query($sql);
	}
}

function bloccaCorseWebMassiva($corse) {
	global $db;
	foreach ($corse as $corsa) {
		$CorsaId = $db->pulisci($corsa['CorsaId']);
		$DataPartenza = $db->pulisci($corsa['DataPartenza']);
		// Prende spunto da bloccaCorsaWeb: inserisce se non esiste
		$sql = "INSERT IGNORE INTO RT_CorsaBloccoWeb (CorsaId, DataPartenza) VALUES ('".$CorsaId."', '".$DataPartenza."')";
		$db->query($sql);
	}
}

function sbloccaCorseWebMassiva($corse) {
	global $db;
	foreach ($corse as $corsa) {
		$CorsaId = $db->pulisci($corsa['CorsaId']);
		$DataPartenza = $db->pulisci($corsa['DataPartenza']);
		// Prende spunto da bloccaCorsaWeb: cancella se esiste
		$sql = "DELETE FROM RT_CorsaBloccoWeb WHERE CorsaId = '".$CorsaId."' AND DataPartenza = '".$DataPartenza."'";
		$db->query($sql);
	}
}


?>