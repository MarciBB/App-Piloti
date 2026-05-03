<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."as_reportool/as_reportool.php");

$ModuloId=12;

function show_list()
{
	global $user,$HtmlCommon, $dizionario;
	$db= new Database();
	$db->connect();
	
	$gestore=new Gestore();
	$gestore->conn=$db;
	$ges=$user->GestoreId;
	if (($user->GestoreId==1))
	{
	    $ges=1;
	}
	$gestorefigli=$gestore->getGestoreFigli($ges);
	$InGestoreFigli=implode(",", $gestorefigli);
	
	?>
	
	<div>

	<?php
	//$as_dbengine->Connect('localhost','dbadmin','AccediDb2011!','resolve_dev_odc'); 
	# Your MySQL host, login, password and database name.
	
	$post_gestore_id=$_POST['GestoreId'];
	$post_sede_id=$_POST['SedeId'];
	$post_tipo_report=$_POST['tipo_report'];
	$post_dal=$_POST['Dal'];
	$post_al=$_POST['Al'];
	
	$dt=new DT();
	$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
	$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");
	
	
	
	if ($post_tipo_report==1)
	{
	$tipo_report1="Giornaliero";
	$rep=new CReporTool();
	
	/**controllo i titoli E ed R**/
	$q="SELECT RT_PrenotazioneTitoloProvvigione.GestoreId, 
			(case when (RT_PrenotazioneTitoloProvvigione.GestoreId = 1) THEN 0 ELSE	
	 (
	    CASE 
	
	        WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Andata/Ritorno' and RT_PrenotazioneTitolo.TipoTitolo = 'E' THEN  ROUND(RT_PrenotazioneTitoloProvvigione.ImportoAgenzia, 2)
	        WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Corsa Semplice' and RT_PrenotazioneTitolo.TipoTitolo = 'E' THEN  ROUND(RT_PrenotazioneTitoloProvvigione.ImportoAgenzia, 2)
	        WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Corsa Semplice' and RT_PrenotazioneTitolo.TipoTitolo = 'R' THEN  ROUND(RT_PrenotazioneTitoloProvvigione.ImportoAgenzia, 2)
			WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Andata/Ritorno' and RT_PrenotazioneTitolo.TipoTitolo = 'R' THEN
				ROUND(RT_PrenotazioneTitoloProvvigione.ImportoAgenzia/(select count(*) from RT_PrenotazioneDettaglio d1 where d1.PrenotazioneNumero =RT_PrenotazioneTitolo.PrenotazioneNumeroId), 2) 
	 END) END) AS ImportoAgenzia,
		
	( CASE WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Andata/Ritorno' and RT_PrenotazioneTitolo.TipoTitolo = 'E' 
			THEN ROUND(RT_PrenotazioneTitolo.ImportoTitolo, 2) 
		WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Corsa Semplice' and RT_PrenotazioneTitolo.TipoTitolo = 'E' 
			THEN ROUND(RT_PrenotazioneTitolo.ImportoTitolo, 2) 
		WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Corsa Semplice' and RT_PrenotazioneTitolo.TipoTitolo = 'R' 
			THEN ROUND(RT_PrenotazioneTitolo.ImportoTitolo, 2) 
		WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Andata/Ritorno' and RT_PrenotazioneTitolo.TipoTitolo = 'R'  
			THEN ROUND(RT_PrenotazioneTitolo.ImportoTitolo/(select count(*) from RT_PrenotazioneDettaglio d1 where d1.PrenotazioneNumero =RT_PrenotazioneTitolo.PrenotazioneNumeroId), 2) 
		 END) AS ImportoTitolo,  
			
	(CASE 
		WHEN (SELECT p.Libera FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId) = 1 
			AND RT_PrenotazioneTitolo.Codice NOT LIKE 'E-%' 
			AND RT_PrenotazioneTitolo.TipoTitolo = 'E' 
		THEN (SELECT p.TipoTourPasseggeri FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId)

		WHEN (SELECT p.Libera FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId) = 1 
			AND RT_PrenotazioneTitolo.Codice NOT LIKE 'E-%' 
			AND RT_PrenotazioneTitolo.TipoTitolo = 'R' 
		THEN -(SELECT p.TipoTourPasseggeri FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId)

		WHEN (SELECT p.Libera FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId) = 0 
			AND RT_PrenotazioneTitolo.Codice NOT LIKE 'E-%' 
			AND RT_PrenotazioneTitolo.TipoTitolo = 'E' 
		THEN (SELECT p.TotalePaxPrenotati FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId)

		WHEN (SELECT p.Libera FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId) = 0 
			AND RT_PrenotazioneTitolo.Codice NOT LIKE 'E-%' 
			AND RT_PrenotazioneTitolo.TipoTitolo = 'R' 
		THEN -(SELECT p.TotalePaxPrenotati FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId)

		ELSE 0 
	END) AS Posti,

	RT_PrenotazioneTitolo.Codice, 
	RT_PrenotazioneTitolo.Anno, 
	RT_PrenotazioneTitolo.TipoTitolo, 
	RT_PrenotazioneTitolo.DataIns, 
	Gestore.RagioneSociale,
	CONCAT (RT_PrenotazioneDettaglio.LineaNome, ' / ',RT_PrenotazioneDettaglio.CorsaNome) as CorsaNome,
	RT_PrenotazioneDettaglio.CorsaId,
	date_format(`RT_PrenotazioneDettaglio`.`DataPartenza`,_utf8'%Y-%m-%d') AS DataIncasso, 
	date_format(`RT_PrenotazioneDettaglio`.`DataPartenza`,_utf8'%d/%m/%Y') AS DataIncassoF, 
	
	(SELECT GROUP_CONCAT(DISTINCT tp.PagamentoTipo SEPARATOR ', ') 
		 FROM RT_PrenotazioneMovimento m
		 LEFT JOIN RT_PagamentoTipo tp ON tp.PagamentoTipoId = m.PagamentoTipoId
		 WHERE m.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
		 AND m.Stato = 1 
		 AND m.Cancella = 0 
		 AND m.TipoMovimento = 'I' 
		 AND m.ImportoPagato > 0) AS Pagamento,
    
	(SELECT 
		IFNULL(GROUP_CONCAT(n.Nota SEPARATOR '. '), '') 
	FROM RT_PrenotazionePercorsoNote n
	WHERE n.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
	) AS Note,
	(select p.ClienteNome 
		from RT_Prenotazione p
		WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
		group by p.PrenotazioneId
	) as ClienteNome

	FROM RT_PrenotazioneTitoloProvvigione 
	INNER JOIN RT_PrenotazioneTitolo ON RT_PrenotazioneTitoloProvvigione.PrenotazioneTitoloId = RT_PrenotazioneTitolo.PrenotazioneTitoloId 
	INNER JOIN Gestore ON RT_PrenotazioneTitoloProvvigione.GestoreId = Gestore.GestoreId 
	Inner join RT_PrenotazioneDettaglio ON RT_PrenotazioneDettaglio.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId  
	left join RT_TipologiaBiglietto on RT_TipologiaBiglietto.TipologiaBigliettoId  = RT_PrenotazioneTitolo.TipologiaBigliettoId ";
	
	$qw = "where RT_PrenotazioneTitoloProvvigione.GestoreId IN ($InGestoreFigli) 
	and RT_PrenotazioneTitolo.Stato = 1 and RT_PrenotazioneTitolo.Cancella = 0
	and RT_PrenotazioneTitolo.OdcIdRef = $user->OdcId 
	and date_format(`RT_PrenotazioneDettaglio`.`DataPartenza`,_utf8'%Y-%m-%d')>='$post_dal_format' 
	and date_format(`RT_PrenotazioneDettaglio`.`DataPartenza`,_utf8'%Y-%m-%d')<='$post_al_format'
	and ((RT_PrenotazioneDettaglio.Importo > 0 and RT_PrenotazioneTitolo.TipoTitolo = 'E') or 
	(RT_PrenotazioneTitolo.ImportoTitolo < 0 and RT_PrenotazioneTitolo.TipoTitolo = 'R' and RT_PrenotazioneTitolo.Codice like 'E-%' and RT_PrenotazioneDettaglio.Rimborso = 1 and RT_TipologiaBiglietto.OccupaPosto = 0) 
	or (RT_PrenotazioneTitolo.ImportoTitolo < 0 and RT_PrenotazioneTitolo.TipoTitolo = 'R' and RT_PrenotazioneTitolo.Codice like 'E-%' and RT_PrenotazioneDettaglio.Rimborso = 0 and RT_TipologiaBiglietto.OccupaPosto = 1) 
	or (RT_PrenotazioneTitolo.ImportoTitolo < 0 and RT_PrenotazioneTitolo.TipoTitolo = 'R' and RT_PrenotazioneTitolo.Codice not like 'E-%' and RT_PrenotazioneDettaglio.Rimborso = 1 and RT_PrenotazioneDettaglio.PrenotazioneNumero = RT_PrenotazioneTitolo.PrenotazioneNumeroId)) ";
	if (isset($post_gestore_id) and ($post_gestore_id>0) )
	    $qw.="and RT_PrenotazioneTitoloProvvigione.GestoreId=$post_gestore_id ";
	
	$qo="  GROUP BY RT_PrenotazioneTitolo.Codice
		  order by RT_PrenotazioneTitolo.DataIns asc";
	$q=$q.$qw.$qo;
	 //echo $q;
	
	/**controllo i titoli X**/
	$q1="SELECT RT_PrenotazioneTitoloProvvigione.GestoreId,
			(case when (RT_PrenotazioneTitoloProvvigione.GestoreId = 1) THEN 0 ELSE	
	 ( CASE WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Andata/Ritorno' 
			THEN ROUND(RT_PrenotazioneTitoloProvvigione.ImportoAgenzia, 2) 
		WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Corsa Semplice' 
			THEN ROUND(RT_PrenotazioneTitoloProvvigione.ImportoAgenzia, 2) END) END) AS ImportoAgenzia, 
	
	( CASE WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Andata/Ritorno' 
			THEN ROUND(RT_PrenotazioneTitolo.ImportoTitolo, 2) 
		WHEN RT_PrenotazioneDettaglio.TipoViaggio = 'Corsa Semplice'
			THEN ROUND(RT_PrenotazioneTitolo.ImportoTitolo, 2) END) AS ImportoTitolo, 
	
	0 as Posti,
	
	RT_PrenotazioneTitolo.Codice,
	RT_PrenotazioneTitolo.Anno,
	RT_PrenotazioneTitolo.TipoTitolo,
	RT_PrenotazioneTitolo.DataIns,
	Gestore.RagioneSociale,
	CONCAT (RT_PrenotazioneDettaglio.LineaNome, ' / ',RT_PrenotazioneDettaglio.CorsaNome) as CorsaNome,
	RT_PrenotazioneDettaglio.CorsaId,
	date_format(`RT_PrenotazioneDettaglio`.`DataPartenza`,_utf8'%Y-%m-%d') AS DataIncasso,
	date_format(`RT_PrenotazioneDettaglio`.`DataPartenza`,_utf8'%d/%m/%Y') AS DataIncassoF,
	
	(SELECT GROUP_CONCAT(DISTINCT tp.PagamentoTipo SEPARATOR ', ') 
		 FROM RT_PrenotazioneMovimento m
		 LEFT JOIN RT_PagamentoTipo tp ON tp.PagamentoTipoId = m.PagamentoTipoId
		 WHERE m.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
		 AND m.Stato = 1 
		 AND m.Cancella = 0 
		 AND m.TipoMovimento = 'I' 
		 AND m.ImportoPagato > 0) AS Pagamento,
    
	(SELECT 
		IFNULL(GROUP_CONCAT(n.Nota SEPARATOR '. '), '') 
	FROM RT_PrenotazionePercorsoNote n
	WHERE n.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
	) AS Note,
	(select p.ClienteNome 
		from RT_Prenotazione p
		WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
		group by p.PrenotazioneId
	) as ClienteNome

	FROM RT_PrenotazioneTitoloProvvigione
	INNER JOIN RT_PrenotazioneTitolo ON RT_PrenotazioneTitoloProvvigione.PrenotazioneTitoloId = RT_PrenotazioneTitolo.PrenotazioneTitoloId
	INNER JOIN Gestore ON RT_PrenotazioneTitoloProvvigione.GestoreId = Gestore.GestoreId
	Inner join RT_PrenotazioneDettaglio ON RT_PrenotazioneDettaglio.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId ";
	
	$qw1 = "where RT_PrenotazioneTitoloProvvigione.GestoreId IN ($InGestoreFigli)
	and RT_PrenotazioneTitolo.Stato = 1 and RT_PrenotazioneTitolo.Cancella = 0
	and RT_PrenotazioneTitolo.OdcIdRef = $user->OdcId
	and date_format(`RT_PrenotazioneDettaglio`.`DataPartenza`,_utf8'%Y-%m-%d')>='$post_dal_format'
	and date_format(`RT_PrenotazioneDettaglio`.`DataPartenza`,_utf8'%Y-%m-%d')<='$post_al_format'
	and (RT_PrenotazioneDettaglio.Importo > 0 and RT_PrenotazioneTitolo.TipoTitolo = 'X') ";
	if (isset($post_gestore_id) and ($post_gestore_id>0) )
		$qw1.="and RT_PrenotazioneTitoloProvvigione.GestoreId=$post_gestore_id ";
	
	$qo1=" GROUP BY RT_PrenotazioneTitolo.Codice
	        order by RT_PrenotazioneTitolo.DataIns asc";
	$q1=$q1.$qw1.$qo1;

	$qt = "Select * from ( (".$q.") UNION ALL (".$q1.")) a
			order by Date(DataIncasso) asc, RagioneSociale asc";
	
	$rep->SetQuery($qt);
	$rep->AddGroupingField('RagioneSociale','RagioneSociale ',$dizionario['gestore']['gestore'].': ',$dizionario['stampe']['totali_agenzia'].': %name%');
	$rep->AddGroupingField('DataIncassoF','DataIncassoF ',$dizionario['generale']['data'].': ',$dizionario['stampe']['tot_data'].': %name%');
	$rep->AddGroupingField('CorsaNome','CorsaNome ',$dizionario['generale']['corsa'].': ','Totali Corsa'.': %name%');
	
	$rep->AddField('RagioneSociale',$dizionario['generale']['agenzia']);
	$rep->AddField('DataIncassoF',$dizionario['generale']['data']);
	$rep->AddField('CorsaNome',$dizionario['generale']['corsa']);
	$rep->AddField('Codice',$dizionario['generale']['biglietto']);
	$rep->AddField('ClienteNome',$dizionario['stampe']['passeggero']);
	$rep->AddField('TipoTitolo',$dizionario['generale']['tipo']);
	$rep->AddField('RagioneSociale',$dizionario['generale']['agenzia']);
	$rep->AddField('Pagamento',$dizionario['movimento']['tipo_pagamento']);
	$rep->AddField('Note',$dizionario['generale']['note']);
	$rep->AddField('Posti',$dizionario['tipo_big']['passeggeri'],1,'','r');
	$rep->AddField('ImportoTitolo',$dizionario['generale']['importo'].' (&euro;)',1,'','money');
	$rep->AddField('ImportoAgenzia',$dizionario['gestore']['provvigione'].' (&euro;)',1,'','money');

	$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
	$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
	$rep->SetSummary('<strong>'.$dizionario['stampe']['totali'].' &euro; <strong> ');

	}
	$gestore="Tutti";
	$sede="Tutte";
	
	if ($post_gestore_id>0) {
	    $sql = "SELECT RagioneSociale from Gestore where GestoreId=$post_gestore_id";
	    $row = $db->query_first($sql);
	    if (!empty($row['RagioneSociale']))
	    $gestore=$row['RagioneSociale'];
	}
	
	$titolo_report=$dizionario['menu_voci']['41']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
	$titolo_report.="<br />".$dizionario['stampe']['periodo_considerato']." ".$post_dal." ".$dizionario['generale']['al']." ".$post_al;
	$titolo_report.="<br />".$dizionario['gestore']['gestore'].": ".$gestore;
	
	$rep->DrawReport($titolo_report);
	?>
	
	</div>
	<style>
	.exportToExcel {
	    font-size: 12px !important;
	    margin-left: 10px !important;
	}
	</style> 
	<script src= "/js/jquery.table2excel.js"> </script> 
	
	<script>
	$(document).ready(function() {
		$(".exportToExcel").click(function(e){
			$(".report_excel").table2excel({
				exclude: ".noExl",
				name: "Competenze Provvigioni",
				filename: "competenze_provvigioni" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
				fileext: ".xls",
				exclude_img: true,
				exclude_links: true,
				exclude_inputs: true,
				preserveColors: true
			});
		});
		
	});
	</script>
	<?php
   
}

if(is_object($user)) {
   
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
	if(!isset($_REQUEST['do'])){
    	$do='';
    } else {
    	$do=$_REQUEST['do'];
    }
		
	switch($do) {
	    default:
        	$FunzioneId=1;
            $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                   
            if (sizeof($permesso))
            	show_list();    
            break;
	}
} else {
	// se l'utente non e' loggato
	header("Location: /logout.php");
} 

?>