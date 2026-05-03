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
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Flotta.php");

//include_once($classespath_."as_reportool/as_reportool.php");

$ModuloId=9;



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

    include_once("report_incassi_validator.php");  
    $datacorrente=Date('d/m/Y');


    $percorso = new Percorso();
    $percorso->conn = $db;
    $arr_percorso = $percorso->getAllForSelect();
	
	$linea = new Linea();
    $linea->conn = $db;
    $arr_linea = $linea->getAllForSelect();
	
	$flotta = new Flotta();
    $flotta->conn = $db;
    $arr_flotta = $flotta->getAllForSelectModel();
    ?>


	<div>
    <!--inizio riga per i filtri--->		
    	<div class="brainFiltri">
			<form id="application_form" name="application_form"  method="post" action="#">
                      
            <div class="rowForm">
				<label for="percorso"><?=$dizionario['percorso']['percorso']?></label>
				<select name="percorso">
                	<option value="" selected ><?=$dizionario['generale']['tutti']?></option>
                	<?php foreach($arr_percorso as $p) { ?>
						<option value="<?= $p['PercorsoId'] ?>"><?=$p['PercorsoNome']?></option>
					<?php } ?>
                </select>
		    </div>
		    
		    <div class="rowForm">
				<label for="tipo_tour"><?=$dizionario['generale']['tipo_tour']?></label>
				<select name="tipo_tour">
                	<option value="" selected ><?=$dizionario['generale']['tutti']?></option>
                	<option value="0"><?=$dizionario['generale']['tour_gruppo']?></option>
                	<option value="1"><?=$dizionario['generale']['tour_privato']?></option>
                </select>
		    </div>

			<div class="rowForm">
				<label for="linea"><?=$dizionario['generale']['tour']?></label>
				<select name="linea">
                	<option value="" selected ><?=$dizionario['generale']['tutti']?></option>
                	<?php foreach($arr_linea as $p) { ?>
						<option value="<?= $p['LineaId'] ?>"><?=$p['LineaNome']?></option>
					<?php } ?>
                </select>
		    </div>
			
			<div class="rowForm">
				<label for="flotta"><?=$dizionario['flotta']['autobus']?></label>
				<select name="flotta" id="barca">
                	<option value="" selected ><?=$dizionario['generale']['tutti']?></option>
                	<?php foreach($arr_flotta as $p) { ?>
						<option value="<?= $p['FlottaId'] ?>"><?=$p['Modello']?></option>
					<?php } ?>
                </select>
		    </div>
			
			<div class="rowForm">
				<label for="tipo_noleggio"><?=$dizionario['flotta']['tipo_noleggio']?></label>
				<select name="tipo_noleggio" id="tipoNoleggio">
                	<option value="" selected ><?=$dizionario['generale']['tutti']?></option>
                	<option value="0"><?=$dizionario['flotta']['noleggio_con_conducente']?></option>
                	<option value="1"><?=$dizionario['flotta']['noleggio_con_licenza']?></option>
					<option value="1"><?=$dizionario['flotta']['exlusive']?></option>
                </select>
		    </div>
			
			<div class="rowForm">
				<label for="area_lavoro"><?=$dizionario['flotta']['area_lavoro']?></label>
				<select name="area_lavoro" id="areaLavoro">
                	<option value="" selected ><?=$dizionario['generale']['tutti']?></option>
                	<option value="0"><?=$dizionario['flotta']['sirmione']?></option>
                	<option value="1"><?=$dizionario['flotta']['desenzano']?></option>
                </select>
		    </div>
              
			<div class="rowForm">
            	<label for="Dal"><?=$dizionario['generale']['dal_m']?></label>
            	<input class="required" type="text" value="<?=$datacorrente?>" id="Dal" name="Dal" maxlength="255" size="10">
            	<label for="dataAl"><?=$dizionario['generale']['al_m']?></label>
            	<input class="required" type="text" value="<?=$datacorrente?>" id="Al" name="Al" maxlength="255" size="10">
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