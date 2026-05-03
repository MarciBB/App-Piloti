<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['contatti']['titolo'];
$PageDescription = $dizionario['contatti']['descr'];
$PageKeywords = $dizionario['contatti']['key'];

$page_title = $dizionario['Contatti'];

$LinkActive = $dizionario['Contatti'];
?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>

<!-- Gmaps JS
================================================== --> 
<script src="/js/gmaps.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript">

	function onSubmit(token) {
		document.getElementById("contatti_italia").submit();
	}

</script>

<!-- Tabs
================================================== -->
<script>
    $(function() {
        $( "#tabs" ).tabs({                  
        });
                
        $('#a_map2').click(function(){
            map2.refresh();
        });
        $('#a_map1').click(function(){
            map.refresh();
        });
    });
            
            
</script>
</head>

<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
		<div class="content">
			<div style="margin-bottom:10px;">
				<h3><b><?php echo $page_title;?></b></h3>
			</div>
			<div class="ticket-list">
			
				<div class="filters">
				  <span>Dove siamo</span>
				</div>
				<div class="allTicket">
					<!-- MAPPA GOOGLE -->
					<div class="wpb_column vc_column_container vc_col-sm-12">
						<div class="vc_column-inner ">
							<div class="wpb_wrapper">
								<div class="wpb_gmaps_widget wpb_content_element vc_custom_1476686139292">
									<div class="wpb_wrapper">
										<div class="wpb_map_wraper">
											<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2797.032403137132!2d10.605511676571751!3d45.489292231763656!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x478193547cc8f1ef%3A0xc85c4d660daeab12!2sBertoldi%20Boats%20-%20Tour%20motoscafi%20a%20Sirmione%20e%20sul%20lago%20di%20Garda!5e0!3m2!1sit!2sit!4v1715795018930!5m2!1sit!2sit" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- /MAPPA GOOGLE -->
					<div class="filters">
					  <span>Contattaci</span>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="contatti-box">
								<p><strong><?php echo $dizionario['area_clienti']['indirizzo_lowcase'];?></strong>
									<br /> Lungolago Armando Diaz, 25019 Sirmione (Brescia)</p>
									
								<p><b>Website</b>
									<br /> <a href="https://www.bertoldiboats.com">www.bertoldiboats.com</a>
								</p>
								<p><strong><?php echo $dizionario['Telefono'];?></strong>
									<br/><a href="+390307778527‬">+39 030 7778527‬</a>‬</p>
								<p><strong>Email</strong>
									<br /><a href="mailto:info@bertoldiboats.com">info@bertoldiboats.com</a>
								</p>
								<p><strong>P.IVA</strong>
									<br />03504290986
								</p> 
							</div>
						</div>
						<div class="col-md-6">
							<div class="wpb_wrapper">
								<div class="addon-themeum-action text-uppercase" style="text-align:left;">
									<div class="themeum-action">
										<h2 class="action-titlecustomstyle"><?php echo $dizionario['contatti']['modulo'];?></h2>
									</div>
								</div>
								<div role="form" class="wpcf7" id="wpcf7-f978-p979-o1" lang="en-US" dir="ltr">
									<div class="screen-reader-response"></div>
									<form id="contatti_italia"  method="POST" action="/actions/contatti.php" class="wpcf7-form" novalidate>
										<div style="display: none;">
											<input type="hidden" name="_wpcf7" value="978" />
											<input type="hidden" name="_wpcf7_version" value="4.9.1" />
											<input type="hidden" name="_wpcf7_locale" value="it_IT" />
											<input type="hidden" name="_wpcf7_unit_tag" value="wpcf7-f978-p979-o1" />
											<input type="hidden" name="_wpcf7_container_post" value="979" />
										</div>
										<div class="row">
											<div class="col-sm-6">
												<div class="bg-contact">
													<span class="wpcf7-form-control-wrap nome">
														<input type="text" name="nome" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['Nome']?>" />
													</span>
												</div>
											</div>
											<div class="col-sm-6">
												<div class="bg-contact">
													<span class="wpcf7-form-control-wrap cognome">
														<input type="text" name="cognome" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['Cognome']?>" />
													</span>
												</div>
											</div>
											<div class="col-sm-6">
												<div class="bg-contact">
													<span class="wpcf7-form-control-wrap email">
														<input type="email" name="email" value="" size="40" class="wpcf7-form-control wpcf7-text" aria-invalid="false" placeholder="<?=$dizionario['Email']?>" />
													</span>
												</div>
											</div>
											<div class="col-sm-6">
												<div class="bg-contact">
													<span class="wpcf7-form-control-wrap telefono">
														<input type="text" name="telefono" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['Telefono']?>" />
													</span>
												</div>
											</div>
											<div class="col-sm-12">
												<div class="bg-contact">
													<span class="wpcf7-form-control-wrap richiesta">
														<textarea name="richiesta" cols="40" rows="10" class="wpcf7-form-control wpcf7-textarea" aria-invalid="false" placeholder="<?=$dizionario['Richiesta']?>"></textarea>
													</span>
												</div>
											</div>
											<div class="row">
												<div class="form-group col-md-12">
													<div id="privacy_error"></div>
													<label class="concheck"><?=$dizionario['Accetto-Privacy-GDPR']?>
														<input type="checkbox" id="privacy" name="privacy">
														<span class="checkmark1"></span>
														<div id="privacy_error"></div>
													</label>
												</div>
												
											</div>
											<div class="row">
												<div class="col-sm-12">
												
													<button class="btn btn-primary w-full btn-big mt-5 rounded-pill g-recaptcha" data-sitekey="6Lftot0pAAAAAGFZ-AWUWk0SrtzLVj5QmrZTUDQQ"  data-callback='onSubmit' data-action='submit' >
														<?=$dizionario['Invia']?>
													</button>
												</div>
											</div>
										</div>
										<div class="wpcf7-response-output wpcf7-display-none"></div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
    
        
        <!-- Top Header
        ================================================== -->
        <? include_once($basepath."/include/top_header.php") ?>  




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
</html>