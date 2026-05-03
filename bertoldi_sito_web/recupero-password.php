<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['area_clienti']['recupero'];
$PageDescription = $dizionario['area_clienti']['descr'];
$PageKeywords = $dizionario['area_clienti']['descr'];

$page_title = $dizionario['area_clienti']['titolo'];
$page_parent = $dizionario['area_clienti']['descr'];

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
				  <span><a href="/area-clienti.php"><?=$dizionario['area_clienti']['area']?></a> / <?=$dizionario['area_clienti']['recupero']?></span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<div style="margin:15px !important;" >
									<?=$dizionario['area_clienti']['recupero_msg']?>
                                
									<br><br>
											
									<div class="clear"></div>
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap cognome">
												<div class="status"></div>
												<input tabindex="1" type="text" name="email" id="email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['email']?>" />
											</span>
										</div>
									</div>
										   
									<div><a href="/login.php"><?=$dizionario['area_clienti']['torna']?></a></div>
									
									<br>
									
									<div class="submit_holder">
										<button type="submit" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="recupero-button"><i class="fa fa-key"></i> Recupera Password</button>
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
		$(document).ready(function(){
			$('#recupero-button').click(function(){
				var formData = {
						action: 'recupero-password',
				        email: $('#email').val()
				};
				$.ajax({
				    type: "POST",
				    url: "/gestione_utente.php",
				    // The key needs to match your method's input parameter (case-sensitive).
				    data: formData,
				    dataType: "text",
				    success: function(data){
						if(data.indexOf("false") > -1){
							$('#email').parent().children('.status').html("<?=$dizionario['area_clienti']['email_inesistente']?>");						
						} else {
							window.location.replace("/login.php?tipo=rp");
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