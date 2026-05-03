<?php

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

if ( ! session_id() ) {
   session_start();
}
if(isset($_SESSION['USER'])){
   $user = $_SESSION['USER'];
 }

if(isset($user) && $user != "" ){
    header('location: /mie-prenotazioni.php');
    die();
}



$PageTitle = $dizionario['area_clienti']['titolo'];
$PageDescription = $dizionario['area_clienti']['descr'];
$PageKeywords = $dizionario['area_clienti']['key'];

$LinkActive = 4;

$page_title = $dizionario['area_clienti']['titolo'];
$page_parent = $dizionario['area_clienti']['descr'];

include_once($basepath . "/include/meta.php");

global $search;

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;


?>

</head>
<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
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
			<div style="margin-bottom:10px;margin-top:0px;">
				<h3 style="margin-top:0px;"><b><?=$dizionario['area_clienti']['area']?></b></h3>
			</div>
			<div class="ticket-list">
				
				<div class="filters">
				  <span><?=$dizionario['area_clienti']['area']?></span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<div style="margin:15px !important;">
								<p id="tipo_accesso"></p>
								<div>
									<style>
										.opzioni > li > h4 > a:hover{
											color: red;
										}
									</style>
                                	<ul class="opzioni">
                                	<?php 
	                                	if(!isset($user) || $user == "" ){ ?>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/login.php'"><i class="fa fa-user"></i> <?=$dizionario['area_clienti']['accedi']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/registrazione.php'"><i class="fa fa-sign-in"></i> <?=$dizionario['area_clienti']['registrati']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/recupero-password.php'"><i class="fa fa-key"></i> <?=$dizionario['area_clienti']['recupero']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/acquista-coupon.php'"><i class="fa fa-gift"></i> <?=$dizionario['coupon']['acquista_coupon']?></button>
										<?php 
										} else { ?>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/profilo.php'"><i class="fa fa-user"></i> <?=$dizionario['area_clienti']['profilo']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/mie-prenotazioni.php'"><i class="fa fa-ticket"></i> <?=$dizionario['area_clienti']['prenotazioni']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/miei-coupon.php'"><i class="fa fa-ticket"></i> <?=$dizionario['area_clienti']['coupon']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/acquista-coupon.php'"><i class="fa fa-gift"></i> <?=$dizionario['coupon']['acquista_coupon']?></button>
											<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="logout"><i class="fa fa-sign-out"></i> <?=$dizionario['area_clienti']['logout']?></button>
										<?php 
										}
                                	?>
                                	</ul>
                                </div>
							</div>
						</div>
					</div>
				</div>
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
	function getUrlParameter(sParam)
	{
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++) 
		{
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] == sParam) 
			{
				return sParameterName[1];
			}
		}
	}    
	$(document).ready(function(){
		var tipo = getUrlParameter('tipo');
		if(tipo == "r"){
			$('#tipo_accesso').html('<?=$dizionario['area_clienti']['messaggio_registrazione']?>');
		} else if(tipo == "v"){
			$('#tipo_accesso').html('<?=$dizionario['area_clienti']['messaggio_valida']?>');
		} else if(tipo == "vf"){
			$('#tipo_accesso').html('<?=$dizionario['area_clienti']['messaggio_no_valida']?>');
		}

		$('#logout').click(function(){
			var formData = {
					action: 'logout'
			};
			$.ajax({
				type: "POST",
				url: "/gestione_utente.php",
				// The key needs to match your method's input parameter (case-sensitive).
				data: formData,
				dataType: "json",
				success: function(data){
						window.location.replace("/");
				},
				failure: function(errMsg) {
					alert(errMsg);
				}
			});
		});
	});
</script>
	
</html>

