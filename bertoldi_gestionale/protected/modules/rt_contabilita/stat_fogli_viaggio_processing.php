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

function show_list() {
	global $user,$HtmlCommon, $dizionario;
	$db= new Database();
	$db->connect();
	
	$gestore=new Gestore();
	$gestore->conn=$db;
	$ges=$user->GestoreId;
	if (($user->GestoreId==1) or ($user->GestoreId==2))
	{
	    $ges=1;
	}
	$gestorefigli=$gestore->getGestoreFigli($ges);
	$InGestoreFigli=implode(",", $gestorefigli);
	?>
	
	<div>
	
	<?php
	
	$post_pagamento_id=$_POST['PagamentoId'];
	$post_tipo_report=$_POST['tipo_report'];
	$post_dal=$_POST['Dal'];
	$post_al=$_POST['Al'];
	
	$dt=new DT();
	$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
	$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");
	
	if ($post_tipo_report==1) {
		$tipo_report1="Giornaliero";
		$rep=new CReporTool();
		
		/**controllo i titoli E ed R**/
		$q="SELECT f.CorsaDataPartenza as DataIncasso, 
			date_format(f.CorsaDataPartenza, _utf8'%d/%m/%Y') AS DataIncassoF, 
			CONCAT (l.LineaNome , ' / ', cs.CorsaNome) as Corsa,
			p.CodicePrenotazione as Prenotazione,
			p.CodiceBiglietto as Biglietto,
			p.DaPagare as Importo,
			p.Pagamento as TipoPagamento,
			t.TipologiaBus,
			CONCAT (a1.Cognome, ' ', a1.Nome, ' - ', a2.Cognome, ' ', a2.Nome) as Autisti
			FROM RT_MobileCaricoPasseggeri p 
			left join RT_GestioneOttimizzataFlotta f on f.GestioneOttimizzataFlottaId = p.BusId
			left join RT_Linea l on f.LineaId = l.LineaId
			left join RT_Corsa cs on f.CorsaId = cs.CorsaId
			left join RT_TipologiaBus t on f.BusId = t.TipologiaBusId 
			left join RT_PreparazioneBusAutisti b on b.BusId = f.GestioneOttimizzataFlottaId and b.BusId = p.BusId
			left join RT_Autisti a1 on a1.AutistiId = b.Autista1
			left join RT_Autisti a2 on a2.AutistiId = b.Autista2 ";
		
		$qw = "where p.DaPagare > 0 and p.Caricato = 1 and f.GestioneOttimizzataFlottaId is not null 
		and date_format(f.CorsaDataPartenza,_utf8'%Y-%m-%d')>='$post_dal_format' 
		and date_format(f.CorsaDataPartenza,_utf8'%Y-%m-%d')<='$post_al_format' ";
		if($post_pagamento_id != ''){
			$qw .= "  and p.Pagamento = '$post_pagamento_id'";
		}
		$qo=" order by f.CorsaDataPartenza asc, l.LineaNome asc, cs.CorsaNome asc, CodicePrenotazione asc";
		$q=$q.$qw.$qo;
		
		$rep->SetQuery($q);
		$rep->AddGroupingField('DataIncassoF','DataIncassoF ',$dizionario['generale']['data'].': ',$dizionario['stampe']['tot_data'].': %name%');
		$rep->AddGroupingField('Corsa','Corsa ',$dizionario['generale']['corsa'].': ',$dizionario['stampe']['tot_corsa'].': %name%');
		$rep->AddGroupingField('Autisti','Autisti ',$dizionario['foglioviaggio']['autista'].': ',$dizionario['stampe']['tot_bus'].': %name%');
		$rep->AddGroupingField('TipoPagamento','TipoPagamento ',$dizionario['stampe']['pagamento'].': ',$dizionario['stampe']['tot_pagamento'].': %name%');
		
		$rep->AddField('DataIncassoF',$dizionario['generale']['data']);
		$rep->AddField('Corsa',$dizionario['generale']['corsa']);
		$rep->AddField('Autisti',$dizionario['foglioviaggio']['autista']);
		$rep->AddField('TipoPagamento',$dizionario['stampe']['pagamento']);
		$rep->AddField('Biglietto',$dizionario['generale']['biglietto']);
		$rep->AddField('Importo',$dizionario['generale']['importo'].' (&euro;)',1,'','money');
		
		$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
		$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
		$rep->SetSummary('<strong>'.$dizionario['stampe']['totali'].' &euro; <strong> ');
	
	}
	$gestore="Tutti";
	$sede="Tutte";
	   
	$titolo_report=$dizionario['menu_voci']['49']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
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
				name: "Fogli di viaggio",
				filename: "fogli_viaggio" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
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
} 
// se l'utente non è loggato
else {
	header("Location: /logout.php");
}
?>