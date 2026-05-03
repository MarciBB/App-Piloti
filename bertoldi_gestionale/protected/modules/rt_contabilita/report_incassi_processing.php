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

$ModuloId=9;

function show_list()
{
	global $user,$HtmlCommon, $dizionario;
	$db= new Database();
	$db->connect();
	
	$gestore=new Gestore();
	$gestore->conn=$db;
	$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
	$InGestoreFigli=implode(",", $gestorefigli);
	?>

	<div>
	
	<?php
	
	$post_dal=$_POST['Dal'];
	$post_al=$_POST['Al'];
	$dt=new DT();
	$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
	$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");
	
	$post_percorso = $_POST['percorso'];
	$post_tipo_tour = $_POST['tipo_tour'];
	$post_linea = $_POST['linea'];
	$post_flotta = $_POST['flotta'];
	$post_tipo_noleggio = $_POST['tipo_noleggio'];
	$post_area_lavoro = $_POST['area_lavoro'];
	
	$rep=new CReporTool();
	
	if ($post_tipo_report==1){
		$tipo_report1 = $dizionario['conto']['giornaliero'];
		$qs = " date_format(p.`DataIns`, _utf8'%Y-%m-%d')";
	} else if($post_tipo_report==2) {
		$qs = " date_format(p.`DataIns`, _utf8'%Y-%m')";
		$tipo_report1 = $dizionario['conto']['mensile'];
	}  else {
		$qs = " date_format(p.`DataIns`, _utf8'%Y')";
		$tipo_report1 = $dizionario['conto']['annuo'];
	}
	
	
	$tipo_report1 .= " - ".$dizionario['canale'][$post_tipo_canale];
	
	$q="SELECT 
			YEAR(pp.CorsaDataPartenza) AS Anno,
			CASE 
				WHEN MONTH(pp.CorsaDataPartenza) = 1 THEN 'Gennaio'
				WHEN MONTH(pp.CorsaDataPartenza) = 2 THEN 'Febbraio'
				WHEN MONTH(pp.CorsaDataPartenza) = 3 THEN 'Marzo'
				WHEN MONTH(pp.CorsaDataPartenza) = 4 THEN 'Aprile'
				WHEN MONTH(pp.CorsaDataPartenza) = 5 THEN 'Maggio'
				WHEN MONTH(pp.CorsaDataPartenza) = 6 THEN 'Giugno'
				WHEN MONTH(pp.CorsaDataPartenza) = 7 THEN 'Luglio'
				WHEN MONTH(pp.CorsaDataPartenza) = 8 THEN 'Agosto'
				WHEN MONTH(pp.CorsaDataPartenza) = 9 THEN 'Settembre'
				WHEN MONTH(pp.CorsaDataPartenza) = 10 THEN 'Ottobre'
				WHEN MONTH(pp.CorsaDataPartenza) = 11 THEN 'Novembre'
				WHEN MONTH(pp.CorsaDataPartenza) = 12 THEN 'Dicembre'
			END AS Mese,
			SUM(t.ImportoVenduto) AS SommaImportoVenduto,
			ROUND((SUM(t.ImportoVenduto) / (
				SELECT 
					SUM(t2.ImportoVenduto)
				FROM 
					RT_PrenotazioneTitolo t2
				LEFT JOIN 
					RT_PrenotazionePercorso pp2 ON pp2.PrenotazioneId = t2.PrenotazioneId
				WHERE 
					(pp2.PrenotazioneStato = 3 OR pp2.PrenotazioneStato = 7)
					AND pp2.Stato = 1 
					AND pp2.Cancella = 0  
					AND t2.Stato = 1 
					AND t2.Cancella = 0
					AND pp2.CorsaDataPartenza >= '$post_dal_format' && pp2.CorsaDataPartenza <= '$post_al_format'
			) * 100), 2) AS PercentualeIncasso,
			COUNT(DISTINCT CONCAT(pp.CorsaId, '_', pp.CorsaDataPartenza)) AS Tratte,
			SUM(t.ImportoVenduto) / COUNT(DISTINCT CONCAT(pp.CorsaId, '_', pp.CorsaDataPartenza)) AS ImportoMedioPerTratta,
			SUM(CASE WHEN tb.OccupaPosto = 1 THEN 1 ELSE 0 END) AS Pax,
			SUM(t.ImportoVenduto) / SUM(CASE WHEN tb.OccupaPosto = 1 THEN 1 ELSE 0 END) AS ImportoMedioPerPax,
			SUM(CASE WHEN tb.OccupaPosto = 1 THEN 1 ELSE 0 END) / COUNT(DISTINCT CONCAT(pp.CorsaId, '_', pp.CorsaDataPartenza)) AS PaxPerTratta
		FROM 
			RT_PrenotazioneTitolo t
		LEFT JOIN 
			RT_PrenotazionePercorso pp ON pp.PrenotazioneId = t.PrenotazioneId
		LEFT JOIN 
			RT_TipologiaBiglietto tb ON t.TipologiaBigliettoId = tb.TipologiaBigliettoId
		LEFT JOIN 
			RT_Linea l ON l.LineaId = pp.LineaId
		LEFT JOIN 
			RT_Corsa c ON c.CorsaId = pp.CorsaId
		LEFT JOIN 
			RT_TipologiaBus bb ON c.TipologiaBusDefaultId = bb.TipologiaBusId
		LEFT JOIN 
			RT_Flotta f ON f.TipologiaBusId = bb.TipologiaBusId
		WHERE 
			(pp.PrenotazioneStato = 3 OR pp.PrenotazioneStato = 7)
			AND pp.Stato = 1 
			AND pp.Cancella = 0  
			AND t.Stato = 1 
			AND t.Cancella = 0
			AND pp.CorsaDataPartenza >= '$post_dal_format' && pp.CorsaDataPartenza <= '$post_al_format'
		
	";
	
	if(isset($post_percorso) && $post_percorso != '') {
		$q .= " AND pp.PercorsoId = $post_percorso";
	}
	if(isset($post_tipo_tour) && $post_tipo_tour != '') {
		$q .= " AND l.TipoTour = $post_tipo_tour";
	}
	if(isset($post_linea) && $post_linea != '') {
		$q .= " AND l.LineaId = $post_linea";
	}
	if(isset($post_flotta) && $post_flotta != '') {
		$q .= " AND f.FlottaId = $post_flotta";
	}
	if(isset($post_tipo_noleggio) && $post_tipo_noleggio != '') {
		$q .= " AND f.TipoNoleggio = $post_tipo_noleggio";
	}
	if(isset($post_area_lavoro) && $post_area_lavoro != '') {
		$q .= " AND f.AreaLavoro = $post_area_lavoro";
	}
	
	$q .= " GROUP BY 
				YEAR(pp.CorsaDataPartenza),
				MONTH(pp.CorsaDataPartenza)
			ORDER BY 
				Anno, 
				MONTH(pp.CorsaDataPartenza);";

	
	$rep->SetQuery($q);
 	$rep->AddGroupingField('Anno','Anno ','Anno: ','Totale Incasso Anno %name%');
  	$rep->AddField('Mese','Mese');
	
 	$rep->AddField('SommaImportoVenduto',$dizionario['stampe']['incassato'].' (&euro;)',1,'','money');
 	$rep->AddField('PercentualeIncasso',"% Incasso &euro;",1,'','money');
	$rep->AddField('Tratte',"Tratte",1,'','int');
	$rep->AddField('ImportoMedioPerTratta',"Incasso &euro; / Tratta",1,'','money');
	$rep->AddField('Pax',"Pax",1,'','int');
	$rep->AddField('ImportoMedioPerPax',"Incasso &euro; / Pax",1,'','money');
	$rep->AddField('PaxPerTratta',"Pax / Tratta",1,'','money');
	
	
	
 	
	$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
	
	$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
	
	$rep->SetSummary('<strong>'.$dizionario['stampe']['tot_incassato'].' &euro; <strong> ');
	
	$titolo_report=$dizionario['menu_voci']['54']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
	$titolo_report.="<br />".$dizionario['stampe']['periodo_considerato']." ".$post_dal." ".$dizionario['generale']['al']." ".$post_al;

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
				name: "Corrispettivi",
				filename: "report_incassi_" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
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

    $db = new Database();
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
            if (sizeof($permesso)) {
            	show_list();    
            }
		    break;
	}
} else {
	// se l'utente non è loggato
	header("Location: /logout.php");
}


?>