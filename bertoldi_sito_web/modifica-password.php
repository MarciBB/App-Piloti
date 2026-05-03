<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['area_clienti']['recupero'];
$PageDescription = $dizionario['area_clienti']['descr'];
$PageKeywords = $dizionario['area_clienti']['descr'];

$LinkActive = 4; 
include_once($basepath."/include/meta.php");
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
				<?php
					if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1){
						echo '<br>'.$dizionario['prenota']['prenotazione_gestita_da'].'<b>'.$_SESSION['gestore']['RagioneSociale']."</b>";
					}
				?>
			</b></div>
			<div style="margin-bottom:10px;margin-top:0px;">
				<h3 style="margin-top:0px;"><b><?=$PageTitle?></b></h3>
			</div>
			<div class="ticket-list">
			
				<div class="filters">
				  <span><a href="/area-clienti-php"><?=$dizionario['area_clienti']['area']?></a> / <?=$dizionario['area_clienti']['recupero']?></span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<div style="margin:15px !important;" >
								<div class="info">
                                	<form id="modifica-password">
									
										<div class="col-sm-12">
											<div class="bg-contact">
												<span class="wpcf7-form-control-wrap password">
													<div class="status"></div>
													<input tabindex="17" type="password" name="password" id="password" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['psw']?>" />
												</span>
											</div>
										</div>
										
										<div class="col-sm-12">
											<div class="bg-contact">
												<span class="wpcf7-form-control-wrap conferma_password">
													<div class="status"></div>
													<input tabindex="18" type="password" name="conferma_password" id="conferma_password" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['conferma']?> <?=$dizionario['area_clienti']['psw']?>" />
												</span>
											</div>
										</div>
                              		</form>
                                </div>
                               <div><a href="/login.php"><?=$dizionario['area_clienti']['torna']?></a></div>
                                <br>
                                <div class="submit_holder">
									<button type="submit" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="recupero-button"><i class="fa fa-key"></i> Modifica Password</button>
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

<script type="text/javascript">
	    function passwordValidate(){
			var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/;
			  if(!regex.test($('#password').val())){
				$('#password').parent().children('.status').html("<?=$dizionario['area_clienti']['psw_non_vaida']?>");
				return false;
			} else {
				 $('#password').parent().children('.status').html("");
				 return true;
			 }
		}
		function passwordConfermaValidate(){
			var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/;
			  if(!regex.test($('#conferma_password').val())){
				$('#conferma_password').parent().children('.status').html("<?=$dizionario['area_clienti']['psw_non_vaida']?>");
				return false;
			 } else if($('#conferma_password').val() != $('#password').val()){
				 $('#conferma_password').parent().children('.status').html("<?=$dizionario['area_clienti']['psw_no_uguale']?>");
				 return false;
			 } else {
				 $('#conferma_password').parent().children('.status').html("");
				 return true;
			 }
		}
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
			var emailParam = getUrlParameter('email');
			var codiceParam = getUrlParameter('codiceRecupero');
			
			$('#password').change(function(){
				passwordValidate();
			});
			$('#conferma_password').change(function(){
				passwordConfermaValidate();
			});
			$('#recupero-button').click(function(){
				if(passwordValidate() && passwordConfermaValidate()) {
					var formData = {
							action: 'modifica-password',
							password: $('#password').val(),
							email: emailParam,
							codice: codiceParam
							
					};
					$.ajax({
						type: "POST",
						url: "/gestione_utente.php",
						// The key needs to match your method's input parameter (case-sensitive).
						data: formData,
						dataType: "json",
						success: function(data){
							if(data.result == 'true'){
								window.location.replace("/login.php");
							} else {
								$('#email').parent().children('.status').html("<?=$dizionario['area_clienti']['email_inesistente']?>");
							}
						},
						failure: function(errMsg) {
							alert(errMsg);
						}
					});
				}
			});
		});
    </script>
	
</html>