<?php
// Imposta il percorso base del documento
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

// Carica la configurazione dell'applicazione
$config = new Config();
$run = $config->load();

// Se l'utente è già autenticato come autista, reindirizza alla pagina principale
if (isset($_SESSION['autista'])) {
	header('Location: ./index.php?code=gC_6XHrZvC9$avW!');
	// exit; // Scommentare se si vuole terminare lo script dopo il redirect
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<!-- Meta tag per compatibilità e responsive -->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Bertoldi Boats - App Operatore</title>

	<!-- Manifest per PWA -->
	<link rel="manifest" href="./manifest.json">

	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">

	<!-- Fogli di stile personalizzati -->
	<link rel="stylesheet" type="text/css" href="./css/style.css" />
	<link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
	<link rel="stylesheet" href="/css/home.css" type="text/css" />
	<link rel="stylesheet" href="/css/home_2.css" type="text/css" />

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

	<!-- Script JS e librerie -->
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
	<script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
	<script type="text/javascript" src="/js/menu_hover.js"></script>
	<script type="text/javascript" language="javascript" src="/js/dialogbox.js?v=3"></script>
	<script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
	<script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
	<script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
	<script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>

	<!-- Bootstrap JS e dipendenze -->
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

	<!-- Stili personalizzati -->
	<style>
		#accordion_content_custom .ui-accordion .ui-accordion-content {
			padding: 1em 1em !important;
		}
		.errorUser, .errorPin {
			color: red;
		}
	</style>

	<!--[if lte IE 8]>
	<link rel="stylesheet" href="/css/styleIE.css" type="text/css" />
	<![endif]-->
</head>
<body class="main-bg bg-ancore" id="login">

	<!-- Top menu con logo -->
	<div id="top-menu" class="transparent">
		<img src="./img/logo.png" class="logo">
	</div>

	<div class="main-container">
		<div class="content">
			<div class="main-title">
				<h5><b>Benvenuto Su</b></h5>
				<h3><b>Bertoldi Boats</b></h3>
			</div>
			<div class="card-cta">
				<p>Gestisci prenotazioni e pagamenti direttamente da mobile!</p>
			</div>
			<!-- Form di login -->
			<form class="login-form">
				<div class="form-group" id="login-username">
					<label for="username">Nome Utente</label>
					<input type="username" name="username" id="login-input-username" required="required" placeholder="Inserisci Nome Utente">
					<small class="form-text errorUser" style="display:none;">Credenziali non corrette</small>
				</div>
				<div class="form-group" id="login-password">
					<label for="password">Password</label>
					<input type="password" name="password" id="login-input-password" required="required" placeholder="Inserisci Password">
					<small class="form-text errorUser" style="display:none;">Credenziali non corrette</small>
				</div>
				<div class="form-group" id="login-pin">
					<label for="pin">PIN</label>
					<input type="password" name="pin" id="login-input-pin" required="required" placeholder="Inserisci PIN" pattern="[0-9]*" maxlength="4">
					<small class="form-text errorPin" style="display:none;">PIN non corretto</small>
				</div>
				<button type="button" class="btn btn-primary" id="login-submit">EFFETTUA IL LOGIN</button>
				<div class="row" id="login-goback" style="display:none;">
					<div class="col-md-12">
						<a href="#" data-role="button" class="magin-button"><i class='lIcon fa fa-arrow-left'></i>Torna indietro</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- jQuery per compatibilità -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>

	<script type="text/javascript">
	// Funzione per effettuare il login tramite AJAX
	function login() {
		// Controlla che tutti i campi siano compilati
		if (
			$("#login-input-username").val() == "" ||
			$("#login-input-password").val() == "" ||
			$("#login-input-pin").val() == ""
		) {
			$('.errorUser').show();
			$('.errorPin').show();
		} else {
			// Recupera i valori dei campi
			var user = $("#login-input-username").val();
			var psw = $("#login-input-password").val();
			var pin = $("#login-input-pin").val();
			var formData = {
				action: "loginBrowser",
				username: user,
				password: psw,
				pin: pin
			};

			// Effettua la chiamata AJAX per il login
			$.ajax({
				url: '<?php echo Config::$UrlMobile; ?>',
				type: "POST",
				data: formData,
				dataType: 'json',
				success: function(responce) {
					// Se login corretto, reindirizza alla home
					if (responce.result == "true") {
						$('.errorUser').hide();
						$('.errorPin').hide();
						window.location = "./index.php?code=gC_6XHrZvC9$avW!";
					} else if (responce.error == "psw") {
						// Errore credenziali username/password
						$('.errorUser').show();
						$('#login-username').show();
						$('#login-password').show();
					} else {
						// Errore PIN
						$('.errorPin').show();
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					// Errore di comunicazione con il server
					console.log(xhr, ajaxOptions, thrownError);
					alert("L'applicazione non riesce a comunicare con il server");
				}
			});
		}
	}

	$(document).ready(function() {
		// Click sul pulsante login
		$('#login-submit').click(function() {
			login();
		});

		// Premi INVIO in qualsiasi campo del form per inviare il login
		$('.login-form').on('keydown', function(e) {
			if (e.key === 'Enter' || e.keyCode === 13) {
				e.preventDefault();
				$('#login-submit').click();
			}
		});
	});
	</script>
</body>
</html>
		
		
