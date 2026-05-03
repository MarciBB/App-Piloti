<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include_app.php");

$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."/class.TipologiaBus.php");

include_once($classespath_."class.DT.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once ($classespath_ . "Graph/class.GraphUtil.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.GestioneOttimizzataFlotta.php");
include_once($classespath_."class.Autisti.php");
include_once($classespath_."class.Flotta.php");
include_once($classespath_."class.PreparazioneBusAutista.php");
include_once($classespath_."class.GestioneOttimizzataModifiche.php");
include_once($classespath_."class.Prenotazione.php");

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$ModuloId=1;
global $db, $autista, $user;
$db=new Database();
$db->connect();

//controllo login
if(!isset($_SESSION['autista'])){
	header('Location: ./login.php');
}
$autista = $_SESSION['autista'];
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

function gestioneOttimizzata(){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>


	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<title>Bertoldi Boats - App Operatore</title>

	<link rel="manifest" href="./manifest.json">
	
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
  
		<link rel="stylesheet" type="text/css" href="/css/reset.css" />
		<link rel="stylesheet" type="text/css" href="/css/style.css?v5" />
		<link rel="stylesheet" type="text/css" href="./css/app.css" />
		<link rel="stylesheet" type="text/css" href="./css/style.css" />
		<link
			href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css"
			rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script
			src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
		<script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
		<script type="text/javascript" src="/js/menu_hover.js"></script>
		<script type="text/javascript" language="javascript"
			src="/js/dialogbox.js?v=3"></script>
		<script type="text/javascript" language="javascript"
			src="/js/validate/jquery.validate.js"></script>
		<script type="text/javascript" language="javascript"
			src="/js/jquery.form.track.changes.js"></script>
		<script type="text/javascript" language="javascript"
			src="/js/ui.multiselect.js"></script>

		<script type="text/javascript"
			src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
		<script type="text/javascript"
			src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
		<script type="text/javascript"
			src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>

		<!-- Bootstrap CSS -->
		<link rel="stylesheet"
			href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
			integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
			crossorigin="anonymous">
			<!-- jQuery first, then Popper.js, then Bootstrap JS -->
			<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
				integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
				crossorigin="anonymous"></script>
			<script
				src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
				integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
				crossorigin="anonymous"></script>

			<script
				src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"
				type="text/javascript"></script>
			<script
				src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
			<script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>

			<script
				src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
				integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
				crossorigin="anonymous"></script>

			<script> 
					$(function() {
						$( "#catalog" ).accordion({
							autoHeight: false,
							navigation: true
						});
						
						$( "#catalog tr" ).draggable({
							appendTo: "body"
							//helper: "clone"
						});
				                
						$( "#catalog1" ).accordion({
							autoHeight: false,
							navigation: true
						});
						
						$( ".accordion_bus" ).accordion({
							autoHeight: false,
							navigation: true,
							collapsible: true,
							autoHeight: false,
							navigation: true,
							header: '.accordion_header'
						});
						
						$( "#catalog1 tr" ).draggable({
							appendTo: "body"
						});
						
					});
				</script>

			<style>
#accordion_content_custom .ui-accordion .ui-accordion-content {
	padding: 1em 1em !important;
}
</style>

			<!--[if lte IE 8]>
				<link rel="stylesheet" href="/css/styleIE.css" type="text/css" />
				<![endif]-->

			<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
			<link rel="stylesheet" href="/css/home.css" type="text/css" />
			<link rel="stylesheet" href="/css/home_2.css" type="text/css" />
			<link
				href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css"
				rel="stylesheet">

</head>
<body class="main-bg bg-ancore" id="validazione-ingresso">

	<!-- Fine Live Search -->
<?php
    
    $codiceControllo='gC_6XHrZvC9$avW!';

	global $user,$HtmlCommon, $dizionario, $autista;
	
	$db= new Database();
	$db->connect();
	
    $user = new Operatore();
    $user->OperatoreId=1;
    $user->OdcId=1;
    $user->IsAdmin=1;
    $user->GestoreId=1;
    
	?>

<div id="top-menu">
	<a href="/protected/modules/app/"> 
		<img src="./img/logo.png" class="logo" />
	</a>
	<button class="btn btn-outline" id="logout">
		<span class="nowhitespace">LOG OUT</span>
		<svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
		  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
		</svg>
  </button>
</div>

<div class="main-container">
	<div class="bg-sea confirm-title">
		<h2>Inquadra QrCode</h2>
		<div class="operator">
			per validare l’ingresso<br>
				dei clienti al molo. 
		</div>
	</div>
	<div class="content">
		<!-- Pulsante per avviare la scansione del QrCode -->
		<button type="button" class="btn btn-primary btn-big" id="scansiona" style="margin-top:10px;">Avvia scansione QrCode</button>
		<!-- Pulsante per tornare indietro, visibile solo durante la scansione -->
		<button type="button" class="btn btn-secondary btn-big" id="go_back" style="display:none;margin-top:10px; margin-bottom:10px;">Torna indietro</button>
		<!-- Contenitore dell'app Vue per la scansione, inizialmente nascosto -->
		<div id="app" style="display:none;">
			<div class="preview-container"
				style="-webkit-transform: scaleX(-1); transform: scaleX(-1); z-index: -10000; width:100% !important;">
				<!-- Video dove viene mostrato il feed della videocamera -->
				<video id="preview" style="width:100% !important;"></video>
			</div>
		</div>
	</div>
</div>
        
<?php include_once("app.php");?>
<script type="text/javascript">
    $(document).ready(function() {	
		// Al click su "Avvia scansione QrCode"
		$("#scansiona").click(function(){
			app.initVideo(); // Inizializza la videocamera e la scansione
			$("#scansiona").toggle(); // Nasconde il pulsante "scansiona"
			$("#go_back").toggle();   // Mostra il pulsante "Torna indietro"
			$("#app").toggle();       // Mostra il video della webcam
			if($("#app").is(":visible")){
				//app.start(); // (opzionale) Avvia la scansione se necessario
			} else {
				app.stop(); // Ferma la scansione se si nasconde l'app
			}
		});
		
		// Al click su "Torna indietro"
		$("#go_back").click(function(){
			if($("#app").is(":visible")){
				app.stop(); // Ferma la scansione
			}
			window.location.href = "."; // Torna alla pagina principale
		});
	
		// Logout dell'utente
		$('#logout').click(function(){
	    	var formData = {
					action:"logoutBrowser",
			};
			$.ajax({
	    		  url: '<?php echo Config::$UrlMobile; ?>',
	    		  type: "POST",
	    		  data : formData,
	    		  dataType: 'json',
	    		  success: function(responce){
	    				window.location = "./login.php";  
	    		  },
	    		  error: function(xhr, ajaxOptions, thrownError) {
	    		        
	    		  },
	    		});
	    });
		
 });
</script>
<!-- Fine script -->

</body>
</html>
<?php
	$db->close();
}




if(is_array($autista)) {
	$db= new Database();
    $db->connect();
    $user = new Operatore();
    $user->OperatoreId=1;
    $user->OdcId=1;
    $user->IsAdmin=1;
    $user->GestoreId=1;
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
	if(!isset($_REQUEST['do'])){
    	$do='';
    } else {
    	$do=$_REQUEST['do'];
    }
		
	switch($do) {
		default:
			gestioneOttimizzata();    
			break;
		}
}
// se l'utente non e' loggato
else {
	header('Location: ./login.php'); 
}
?>