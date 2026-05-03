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
	//$as_dbengine->Connect('localhost','dbadmin','AccediDb2011!','resolve_dev_odc'); 
	# Your MySQL host, login, password and database name.
	$post_percorso_id=$_POST['PercorsoId'];
	$post_gestore_id=$_POST['GestoreId'];
	$post_sede_id=$_POST['SedeId'];
	$post_tipo_report=$_POST['tipo_report'];
	$post_dal=$_POST['Dal'];
	$post_al=$_POST['Al'];
	$servizio=$_POST['servizio'];
	$dt=new DT();
	$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
	$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");
	
	$rep=new CReporTool();
	
	if ($post_tipo_report==1){
		$tipo_report1 = $dizionario['conto']['giornaliero'];
		$qs.= " date_format(t.`DataIns`, _utf8'%Y-%m-%d')";
	} else if($post_tipo_report==2) {
		$qs.= " date_format(t.`DataIns`, _utf8'%Y-%m')";
		$tipo_report1 = $dizionario['conto']['mensile'];
	}  else {
		$qs.= " date_format(t.`DataIns`, _utf8'%Y')";
		$tipo_report1 = $dizionario['conto']['annuo'];
	}
	
	if ($servizio=="agenzie"){
		$qt = "(case when (t.GestoreIdRef = 1 or t.GestoreIdRef = 2) then g.RagioneSociale
					else 'Altre Aziende'
				END) as Gestore, ";
		$titoloServizi = $dizionario['conto']['gestore_agenzie'];
	} else {
		$qt = "";
		$titoloServizi = $dizionario['conto']['totali'];
	}
	$tipo_report1 .= " - ".$titoloServizi;
	
	$q="SELECT $qs AS DataIncassato, $qt
	sum(if((t.`TipoTitolo` = _utf8'E' or t.`TipoTitolo` = _utf8'X'), t.`ImportoVenduto`, 0)) AS TotaleIncassato, 
	sum(if((t.`TipoTitolo` = _utf8'R'), t.`ImportoVenduto`, 0)) AS TotaleRimborsato, 
	pp.`PercorsoId` AS `PercorsoId`,
	pp.`PercorsoNome` AS `PercorsoNome` 
	from RT_PrenotazioneTitolo t 
			left join RT_Prenotazione p on t.PrenotazioneId = p.PrenotazioneId 
			left join (select distinct PercorsoNome, PercorsoId, PrenotazioneId from RT_PrenotazionePercorso ) pp 
				on pp.PrenotazioneId = p.PrenotazioneId and pp.PrenotazioneId = t.PrenotazioneId 
			left join Gestore g on g.GestoreId = t.GestoreIdRef
			where (p.`NonViaggiata` = 0) and 
			date(t.DataIns) >='$post_dal_format' and date(t.DataIns) <='$post_al_format' 
			and t.Stato = 1 and t.Cancella = 0 and (p.PrenotazioneStato = 3 or p.PrenotazioneStato = 7)
			and (((select count(*) 
				from RT_PrenotazionePercorso temp
				where temp.PercorsoId=10 and temp.PrenotazioneId = t.PrenotazioneId) = 0) || pp.`PercorsoId` = 10)";
	
	if (isset($post_percorso_id) and ($post_percorso_id>0) )
		$q.=" and pp.PercorsoId = $post_percorso_id ";
	
	if ($servizio=="agenzie"){
		if ($post_tipo_report==1){
			$q.= " group by Gestore, date(t.`DataIns`), pp.`PercorsoId` , pp.`PercorsoNome` order by t.GestoreIdRef asc";
		} else if($post_tipo_report==2) {
			$q.= " group by Gestore, month(t.`DataIns`), pp.`PercorsoId` , pp.`PercorsoNome` order by t.GestoreIdRef asc";
		}  else {
			$q.= " group by Gestore, year(t.`DataIns`), pp.`PercorsoId` , pp.`PercorsoNome` order by t.GestoreIdRef asc";
		}
	} else {
		if ($post_tipo_report==1){
			$q.= " group by date(t.`DataIns`), pp.`PercorsoId` , pp.`PercorsoNome`";
		} else if($post_tipo_report==2) {
			$q.= " group by month(t.`DataIns`), pp.`PercorsoId` , pp.`PercorsoNome`";
		}  else {
			$q.= " group by year(t.`DataIns`), pp.`PercorsoId` , pp.`PercorsoNome`";
		}
	}
	
	//$q.="order by RagioneSociale,Comune,DataFattura asc";
	//$q.="order by DataFattura,RagioneSociale,Comune asc";
// echo($q);
	$rep->SetQuery($q);
	$rep->AddGroupingField('PercorsoNome','Servizio linea ',$dizionario['stampe']['servizio_linea'].': ',$dizionario['stampe']['tot_incassato'].' %name%');
	//$rep->AddGroupingField('AppCalendarioDataF','DataIncassoF ','Data Incasso: ','Tot. Incassato il %name%');
	if (isset($servizio) and ($servizio=='agenzie') ){
		$rep->AddGroupingField('Gestore','Gestore ',$dizionario['generale']['agenzia'].': ',$dizionario['stampe']['totali_agenzia'].' %name%');
		$rep->AddField('Gestore',$dizionario['generale']['agenzia']);
	}
	
	$rep->AddField('PercorsoNome',$dizionario['stampe']['servizio_linea']);
	$rep->AddField('DataIncassato',$dizionario['stampe']['data_incasso']);
	
	$rep->AddField('TotaleIncassato',$dizionario['stampe']['incassato'].' (&euro;)',1,'','money');
	$rep->AddField('TotaleRimborsato',$dizionario['stampe']['rimborsato'].' (&euro;)',1,'','money');
	//$rep->AddField('ProvvigioneAgenzia','ProvvigioneNetta (&euro;)',1,'','money');
	
	$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
	
	$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
	
	$rep->SetSummary('<strong>'.$dizionario['stampe']['tot_incassato'].' &euro; <strong> ');
	
	$gestore="Tutti";
	$sede="Tutte";
	
	if ($post_gestore_id>0) {
		$sql = "SELECT RagioneSociale from Gestore where GestoreId=$post_gestore_id";
	    $row = $db->query_first($sql);
	    if (!empty($row['RagioneSociale'])) {
	   		$gestore=$row['RagioneSociale'];
	    }
	}
	    
	if ($post_sede_id>0) {
	   $sql = "SELECT CodiceSede from ElencoSediView where SedeId=$post_sede_id";
	   $row = $db->query_first($sql);
	   if (!empty($row['CodiceSede'])){
	   		$sede=$row['CodiceSede'];
	   }
	}
	
	$titolo_report=$dizionario['menu_voci']['9']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
	$titolo_report.="<br />".$dizionario['stampe']['tipo_report']." ".$tipo_report1;
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
				filename: "corrispettivi" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
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