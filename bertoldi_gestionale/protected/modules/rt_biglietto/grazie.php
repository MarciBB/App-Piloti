<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_= Config::$modulespath;
$classespath_= Config::$classespath;
$errors = new Errors();
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.PagamentoTipo.php");
include_once($classespath_."class.PrefissoTelefono.php");

global $user, $dizionario;

if (isset($_POST['test'])) {
	isExistTransaction();
	exit();
}

if (isset($_POST['result'])) {
	echo json_encode(array("ok"=>true));
} else {
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>                            
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?=Config::$application_name?> Ver. <?=Config::$application_version?> - <?=Config::$application_company?></title>
			    
			<link rel="stylesheet" type="text/css" href="/css/reset.css" />
			<link rel="stylesheet" type="text/css" href="/css/style.css" />
			<link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css"/>
          	<script type="text/javascript" src="/js/jquery.min.js"></script>
			<script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
		</head>
		<body>
		<span id="body">in attesa</span>
		</body>
	</html>
	
	<script type="text/javascript">
		$(document).ready( function() {
	
			function processTransaction(codiceTransazione) {
				clearInterval(test);
	
				$.ajax({
			    	type: "POST",
			        url: "/protected/modules/rt_biglietto/grazie.php",
			        data: {'result':true, 'codiceTransazione':codiceTransazione},
			        dataType: 'json',
			        success: function(data) {
				        $('#body').html("<p><?=$dizionario['biglietto']['grazie_pagamento']?></p>"+
						"<p><?=$dizionario['biglietto']['grazie_esci']?></p>"
						);
				    }
				});
			}
			
	    	function testTransaction(){
	    		$.ajax({
	    			type: "POST",
			        url: "/protected/modules/rt_biglietto/grazie.php",
			        data: {'test':true},
			        dataType: 'json',
			        success: function(data) {
			        	if (data.result) {
				        	processTransaction(data.codiceTransazione);
			        	}
			        }
				});
		    }
		
	    	var test = setInterval(testTransaction, 30000);
		
		    testTransaction();
		});
	</script>
<?php
}

function isExistTransaction() {
	$db = new Database();
	$db->connect();
	
	$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	$prenotazione = $prenotazione_wizard->DatiGenerali;
	
	//controlla se esiste la tranziozione associata alla prenotazione
	$sql = "SELECT * FROM RT_PrenotazioneTransazione WHERE PrenotazioneId = " . $prenotazione['PrenotazioneId'];
	$row = $db->query_first($sql);
	
	if (empty($row['PrenotazioneTransazioneWeb'])) {
		echo json_encode(array('result' => false));
	} else {
		echo json_encode(array('result' => true, 'codiceTransazione' => $row['CodiceTransazione']));
	}
}
?>