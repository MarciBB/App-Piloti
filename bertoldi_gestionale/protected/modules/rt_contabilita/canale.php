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

    include_once("canale_validator.php");  
    $datacorrente=Date('d/m/Y');


    $linea=new Percorso();
    $linea->conn=$db;
    $arr_linee=$linea->getAllForSelect();
    ?>


	<div>
    <!--inizio riga per i filtri--->		
    	<div class="brainFiltri">
			<form id="application_form" name="application_form"  method="post" action="#">
                      
            <div class="rowForm">
				<label for="tipo_canale"><?=$dizionario['canale']['tipo_canale']?></label>
				<select name="tipo_canale">
                	<option value="all" selected ><?=$dizionario['canale']['all']?></option>
                	<option value="backoffice"><?=$dizionario['canale']['backoffice']?></option>
                	<option value="agenzia"><?=$dizionario['canale']['agenzia']?></option>   	
                	<option value="web_it"><?=$dizionario['canale']['web_it']?></option>
                	<option value="app_it"><?=$dizionario['canale']['app_it']?></option>
                </select>
		    </div>
		    
		    <div class="rowForm">
				<label for="tipo_report"><?=$dizionario['conto']['tipo_report']?></label>
				<select name="tipo_report">
                	<option value="1" selected ><?=$dizionario['conto']['giornaliero']?></option>
                	<option value="2"><?=$dizionario['conto']['mensile']?></option>
                	<option value="3"><?=$dizionario['conto']['annuo']?></option>
                </select>
		    </div>
              
			<div class="rowForm">
            	<label for="Dal"><?=$dizionario['generale']['dal']?></label>
            	<input class="required" type="text" value="<?=$datacorrente?>" id="Dal" name="Dal" maxlength="255" size="10">
            	<label for="dataAl"><?=$dizionario['generale']['al']?></label>
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