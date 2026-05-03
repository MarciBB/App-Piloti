<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/print_stat_mediazione.css" media="print" />
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
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
//include_once($classespath_."as_reportool/as_reportool.php");

$ModuloId=8;



function show_list() {
	global $user, $dizionario;
	$db= new Database();
	$db->connect();
	$page=new Form();
	$gestore=new Gestore();
	$gestore->conn=$db;
	
	$gestore->getGestoreAll($user->GestoreId);
	$arr_gestore=$gestore->ArrGestore;
	
	$sede=new Sede();
	$sede->conn=$db;
	$gestorecorrente=$user->GestoreId;
	$arr_sedi=$sede->getSediByGestori($user->GestoreId);
	$linea=new Linea();
	$linea->conn=$db;
	$arr_linee=$linea->getAllForSelect();


	include_once("stampe_stats_prenotazioni_validator.php");  
	$datacorrente=Date('d/m/Y');
	?>

	<div>
	
	<!--inizio riga per i filtri--->		
			
			
		<div class="brainFiltri">
			<form id="application_form" name="application_form"  method="post" action="#">
			<?php
		    $page->create_select($dizionario['generale']['linea'],"LineaId","LineaId","rowForm",$arr_linee,0,"LineaId","LineaNome",
		    	array("onChange"=>"'javascript:getCorsaByLinea(this);'"),1);
    
     		$page->create_select($dizionario['generale']['corsa'],"CorsaId","CorsaId","rowForm",$arr_corse,0,"CorsaId","CorsaNome",
    			array(null),1);
			?>
                                    
				<div class="rowForm">
					<label for="Dal"><?=$dizionario['generale']['dal']?></label>
					<input class="required" type="text" value="<?=$datacorrente?>" id="Dal" name="Dal" maxlength="255" size="10">
				</div>
                                    
				<div class="rowForm">
					<label for="Al"><?=$dizionario['generale']['al']?></label>
					<input class="required" type="text" value="<?=$datacorrente?>" id="Al" name="Al" maxlength="255" size="10">
				</div>
                                    
			<?php
                                    
			$sql="SELECT
			RT_PrenotazionePercorso.ComuneSalita
			FROM
			RT_PrenotazionePercorso
			GROUP BY
			RT_PrenotazionePercorso.ComuneSalita
			ORDER BY
			RT_PrenotazionePercorso.ComuneSalita ASC";
			$arr_comuni_pk=$db->fetch_array($sql);
                                    
                                    
			$sql="SELECT
			RT_PrenotazionePercorso.ComuneDiscesa
			FROM
			RT_PrenotazionePercorso
			GROUP BY
			RT_PrenotazionePercorso.ComuneDiscesa
			ORDER BY
			RT_PrenotazionePercorso.ComuneDiscesa ASC";
			$arr_comuni_do=$db->fetch_array($sql);                                    
                                    
			$page->create_select($dizionario['stampe']['comune_salita'],"ComuneSalitaId","ComuneSalitaId","rowForm",$arr_comuni_pk,0,"ComuneSalita","ComuneSalita",
				array(null),1);
			$page->create_select($dizionario['stampe']['comune_discesa'],"ComuneDiscesaId","ComuneDiscesaId","rowForm",$arr_comuni_do,0,"ComuneDiscesa","ComuneDiscesa",
				array(null),1);
			?>
                                    
				<div class="rowForm">
					<label for="durata_min"><?=$dizionario['conto']['durata']?> (min)</label>
					<input type="number" id="durata_min" name="durata_min" min="1" step="1" value="1" class="required">
				</div>
				<div class="rowForm">
					<label for="durata_max"><?=$dizionario['conto']['durata']?> (max)</label>
					<input type="number" id="durata_max" name="durata_max" min="1" step="1" value="1" class="required">
				</div>
				<div class="rowForm">
					<input name="applica" type="submit" value="<?=$dizionario['generale']['genera']?>" />
				</div>
				<br style="clear:both;" />
			</form>
		</div>		
<!--fine riga per i filtri--->	

	<div id="risultato_report">
    
    
	</div>
</div>
<?php
   
}


if(is_object($user)) {
   
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
	$do=$_GET['do'];
	if(!isset($do)) 
		$do='';
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