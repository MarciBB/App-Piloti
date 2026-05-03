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
	$post_tipo_canale=$_POST['tipo_canale'];
	$post_tipo_report=$_POST['tipo_report'];
	$post_dal=$_POST['Dal'];
	$post_al=$_POST['Al'];
	$dt=new DT();
	$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
	$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");
	
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
	
	/*if ($servizio=="agenzie"){
		$qt = "(case when (t.GestoreIdRef = 1 or t.GestoreIdRef = 2) then g.RagioneSociale
					else 'Altre Aziende'
				END) as Gestore, ";
		$titoloServizi = $dizionario['conto']['gestore_agenzie'];
	} else {
		$qt = "";
		$titoloServizi = $dizionario['conto']['totali'];
	}*/
	$tipo_report1 .= " - ".$dizionario['canale'][$post_tipo_canale];
	
	$q="SELECT $qs AS DataIncassato, 
    CASE
        WHEN p.Canale = 'backoffice' THEN 'Gestionale'
        WHEN p.Canale = 'agenzia' THEN 'Agenzia'
        WHEN p.Canale = 'api' THEN 'API'
        WHEN p.Canale = 'web_it' THEN 'App Cliente IT'
        WHEN p.Canale = 'web_de' THEN 'App Cliente DE'
        WHEN p.Canale = 'web_be' THEN 'App Cliente BE'
        WHEN p.Canale = 'app_it' THEN 'App Operatore Molo'
        ELSE 'N.D.'
    END as Canale,
    	sum(if((t.`TipoTitolo` = _utf8'E' or t.`TipoTitolo` = _utf8'X'), t.`ImportoVenduto`, 0)) AS TotaleIncassato, 
    	sum(if((t.`TipoTitolo` = _utf8'R'), t.`ImportoVenduto`, 0)) AS TotaleRimborsato,
        count(p.PrenotazioneId) as Prenotazioni
	from RT_PrenotazioneTitolo t 
    	left join RT_Prenotazione p on t.PrenotazioneId = p.PrenotazioneId 
    where  
    	date(p.DataIns) >='$post_dal_format' and date(p.DataIns) <='$post_al_format' 
    	and p.Stato = 1 and p.Cancella = 0 and (p.PrenotazioneStato = 3 or p.PrenotazioneStato = 7)";

	if(isset($post_tipo_canale) && $post_tipo_canale != 'all') {
	    $q .= " AND p.Canale = '$post_tipo_canale'";
	}
	
	if ($post_tipo_report==1){
		$q.= " group by date(p.`DataIns`), p.Canale";
	} else if($post_tipo_report==2) {
		$q.= " group by month(p.`DataIns`), p.Canale";
	}  else {
		$q.= " group by year(p.`DataIns`), p.Canale";
	}

 //echo($q);
	$rep->SetQuery($q);
    //$rep->AddField('Canale',$dizionario['canale']['canale']);
 	$rep->AddGroupingField('Canale','Canale ',$dizionario['canale']['canale'].': ',$dizionario['canale']['totali_canale'].' %name%');
  	$rep->AddField('DataIncassato',$dizionario['stampe']['data_incasso']);
	
 	$rep->AddField('TotaleIncassato',$dizionario['stampe']['incassato'].' (&euro;)',1,'','money');
 	$rep->AddField('Prenotazioni',$dizionario['canale']['prenotazioni'],1,'','int');
 	
	$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
	
	$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
	
	$rep->SetSummary('<strong>'.$dizionario['stampe']['tot_incassato'].' &euro; <strong> ');
	
	$titolo_report=$dizionario['menu_voci']['52']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
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