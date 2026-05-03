<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_= Config::$modulespath;
$classespath_= Config::$classespath;
$errors = new Errors();
error_reporting(E_ALL);
ini_set('display_errors', 0);


global $user, $dizionario;

if (!isset($_REQUEST['session_id'])) {
    die("Nessun pagamento");
} else {
	$sessionId = $_REQUEST['session_id'];
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
			<script src="https://js.stripe.com/v3/"></script>
		</head>
		<style>
			#body{
				margin:20px;
			}
			.pg_grazie{
				padding-top:50px;
			}
		</style>
		<body style="text-align: center;">
                    <div class="pg_grazie"><span id="body"><h2>Pagamento in corso di registrazione...</h2></span></div>
		</body>
	</html>
	
	<script type="text/javascript">
		$(document).ready( function() {
			var stripe = Stripe('<?php echo Config::$StripePublicKey?>');
			stripe.redirectToCheckout({ sessionId: '<?php echo $sessionId;?>' });
		});
	</script>
<?php
}