<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['login']['titolo'];
$PageDescription = $dizionario['login']['descr'];
$PageKeywords = $dizionario['login']['descr'];

$page_title = $dizionario['login']['titolo'];
$page_parent = $dizionario['login']['descr'];

$LinkActive = 4;

if ( ! session_id() ) {
	session_start();
}
?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>

<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
		<div class="content">
			<div style="margin-bottom:10px;"><?=$dizionario['prenota']['benvenuto']?> <b><?=$dizionario['prenota']['ospite']?></b></div>
			
				<?php
				if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1){
					echo '<br>'.$dizionario['prenota']['prenotazione_gestita_da'].'<b>'.$_SESSION['gestore']['RagioneSociale']."</b>";
				}
				?>
			<div style="margin-bottom:10px;margin-top:0px;">
				<h3 style="margin-top:0px;"><b><?=$PageTitle?></b></h3>
			</div>
			<div class="ticket-list">
			
				<div class="filters">
				  <span><a href="/area-clienti.php"><?=$dizionario['area_clienti']['area']?></a> / <?=$dizionario['area_clienti']['accedi']?></span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<div style="margin:15px !important;">
								<div class="clear"></div>
								<div class="alert alert-primary" role="alert" id="tipo_accesso"
									style="
									  color: #004085;
									  background-color: #cce5ff;
									  border-color: #b8daff;
									  display:none;">
								</div>
								<div class="alert alert-primary" role="alert" id="tipo_accesso_errore"
									style="
									  color: #721c24;
									  background-color: #f8d7da;
									  border-color: #f5c6cb;
									  display:none;">
								</div>
								
								<?=$dizionario['area_clienti']['accedi']?>
								
								<div class="info">
									
									<div class="row">
										<div class="col-sm-12">
											<div class="bg-contact">
												<span class="wpcf7-form-control-wrap cognome">
													<input tabindex="1" type="text" name="email" id="email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['email']?>" />
												</span>
											</div>
										</div>
										
										<div class="col-sm-12">
											<div class="bg-contact">
												<span class="wpcf7-form-control-wrap cognome">
													<input tabindex="2" type="password" name="password" id="password" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['psw']?>" />
													<div class="status"></div>
												</span>
											</div>
										</div>
									</div>
									
								</div>
								
								<div><a href="/recupero-password.php"><?=$dizionario['area_clienti']['btn_recupera']?></a></div>
								
								<br>
								
								<div class="submit_holder">
									<button type="submit" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="login-button"><i class="fa fa-user"></i> <?=$dizionario['Accedi']?></button>
								</div><!-- submit_holder -->


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
				$('#tipo_accesso').html("<?=$dizionario['area_clienti']['msg1']?>");
				$('#tipo_accesso').show();
				$('#tipo_accesso_errore').html();
				$('#tipo_accesso_errore').hide();
			} else if(tipo == "v"){
				$('#tipo_accesso').html("<?=$dizionario['area_clienti']['msg2']?>");
				$('#tipo_accesso').show();
				$('#tipo_accesso_errore').html();
				$('#tipo_accesso_errore').hide();
			} else if(tipo == "vf"){
				$('#tipo_accesso_errore').html("<?=$dizionario['area_clienti']['msg3']?>");
				$('#tipo_accesso_errore').show();
				$('#tipo_accesso').html();
				$('#tipo_accesso').hide();
			} else if(tipo == "rp"){
				$('#tipo_accesso').html("<?=$dizionario['area_clienti']['msg4']?>");
				$('#tipo_accesso').show();
				$('#tipo_accesso_errore').html();
				$('#tipo_accesso_errore').hide();
			} else if(tipo == "c"){
				$('#tipo_accesso').html("<?=$dizionario['area_clienti']['msg5']?>");
				$('#tipo_accesso').show();
				$('#tipo_accesso_errore').html();
				$('#tipo_accesso_errore').hide();
			}

			$('#login-button').click(function(){
				var formData = {
						action: 'login',
				        email: $('#email').val(),
				        password: $('#password').val(),
				        tipo: tipo
				};
				$.ajax({
				    type: "POST",
				    url: "/gestione_utente.php",
				    // The key needs to match your method's input parameter (case-sensitive).
				    data: formData,
				    dataType: "json",
				    success: function(data){
				    	$('#password').parent().children('.status').html("");
						if(data.result == 'true'){
							$('#password').parent().children('.status').html("");
							window.location.replace("/area-clienti.php");
						} 

						if(data.result == 'truec'){
							$('#password').parent().children('.status').html("");
							window.location.replace("/conferma_itinerario.php");
						}

						if(data.result == 'false'){
							$('#password').parent().children('.status').html("Username o password incorrette.");
						}
					},
				    failure: function(errMsg) {
				        alert(errMsg);
				    }
				});
			});
		});
    </script>
</html>