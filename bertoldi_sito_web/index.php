<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath.'/main_include.php');

global $db;

$PageTitle = $dizionario['index']['meta_titolo'];
$PageDescription = $dizionario['index']['meta_descr'];
$PageKeywords = $dizionario['index']['meta_key'];

$page_title = "Home";

$LinkActive = 1;

if(isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];
} else {
	$user = null;
}

//imposto la data partenza iniziale
$DataPartenza = date('Y-m-d');
if (isset($_POST['DataPartenza'])) {
	$dt = new DT();
	$post_dal = $_POST['DataPartenza'];
	$DataPartenza = $_POST['DataPartenza'];
	$dateTime = new DateTime($DataPartenza);
	$datacorrente = $dateTime->format('d/m/Y');
}

//inizializzo il gestore di connessione tramite parametro code
//if(isset($_GET['code']) ){
    if(!isset($db)) {
        $db = new Database();
        $db->connect();
    }
    getGestoreByCode();
//}

clearSession();

$_SESSION['ticket_date'] = $DataPartenza;

?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>

<body class="main-bg" id="home-applicativo">

<!-- Google Tag Manager (noscript) -->
<?php if(!empty(Config::$googleTagManagerId)): ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?=Config::$googleTagManagerId?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
<!-- End Google Tag Manager (noscript) -->

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>  

	
    <!-- Wrapper
	================================================== -->
	<div id="page" class="hfeed site fullwidth">
    	
        <div class="main-container">
			<div class="content">
				<div style="margin-bottom:10px;" class="benvenuto-user"><?=$dizionario['prenota']['benvenuto']?> <b><?php 
					if(isset($user)){
							echo $user['Nome'].' '.$user['CognomeRagioneSociale'];
					} else {
							echo $dizionario['prenota']['ospite'];
					} ?>
					</b>
					<?php 
					if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1){
						echo '<br>'.$dizionario['prenota']['prenotazione_gestita_da'].'<b>'.$_SESSION['gestore']['RagioneSociale']."</b>";
					}
					?>
				</div>
				<div style="margin-bottom:10px;" class="benvenuto-plugin">
					<h2><?=$dizionario['prenota']['prenota_il_tuo_tour']?></h2>
					<p><?=$dizionario['prenota']['segui_passaggi']?></p>
				</div>
				<form method="post" action="index.php<?= ($_SESSION['code_gestore'] != '') ? $_SESSION['code_gestore'] . (($sessionId != '') ? '&session_id=' . $sessionId : '') : (($sessionId != '') ? '?session_id=' . $sessionId : '') ?>" id="search">
					<div class="form-group form-icon w-full">
						<label for="data"><?=$dizionario['ticket']['seleziona_data']?></label>

						<div class="control-group">
							<input type="date" name="DataPartenza" id="DataPartenza" value="<?= $DataPartenza ?>">
							<img src="./css/imgs/nota.png">
						</div>
					
					</div>
					<button type="button" class="btn btn-big btn-primary" style="width: 100%;" onclick="handlePrenotaSubito()"><?=$dizionario['ticket']['prenota_subito']?></button>
				</form>
			</div>
		</div>
        
        
        
            

		<!-- Bottom
		================================================== -->
		<?php include_once($basepath."/include/bottom.php"); ?>
        	 
        
        <!-- Footer
        ================================================== -->
        <?php include_once($basepath."/include/footer.php"); ?>    
        
    </div>
    <!-- #page -->
  
   	<?php include_once($basepath."/include/html_close.php"); ?>   

</body>
<script>
	$('#DataPartenza').change(function() {
		var data = $('#DataPartenza').val();
		$("#search").submit();

	});
	
	function handlePrenotaSubito() {
		// Invio evento a dataLayer
		dataLayer.push({
			'event': 'click_prenota_subito'
		});
		
		// Redirect alla pagina di prenotazione
		window.location.href = '/prenota/1.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>';
	}
</script>

</html>