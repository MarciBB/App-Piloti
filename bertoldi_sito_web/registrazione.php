<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['registrazione']['titolo'];
$PageDescription = $dizionario['registrazione']['descr'];
$PageKeywords = $dizionario['registrazione']['key'];

$page_title = $dizionario['registrazione']['titolo'];
$page_parent = $dizionario['registrazione']['descr'];

$LinkActive = 4;

$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();

global $db;
$db = new Database();
$db->connect();

$sql = "Select NazioneId, Nazione FROM Nazione";
$rowNazione = $db->fetch_array($sql);
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
				  <span><a href="/area-clienti.php"><?=$dizionario['area_clienti']['area']?></a> / <?=$dizionario['area_clienti']['registrati']?></span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<div style="margin:15px !important;">
							<div class="clear"></div>
							<?=$dizionario['area_clienti']['reg_msg1']?>
							<br><?=$dizionario['area_clienti']['reg_msg2']?><br><br>
							<div class="clear"></div>
							
							<div class="info">
								
								<div class="row">
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap nome">
												<h3><?=$dizionario['area_clienti']['anagrafica']?></h3>
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap cognome">
												<div class="status"></div>
												<input tabindex="1" type="text" name="cognome" id="cognome" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['cognome']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap nome">
												<div class="status"></div>
												<input tabindex="2" type="text" name="nome" id="nome" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['nome']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap codice_fiscale">
												<div class="status"></div>
												<input tabindex="3" type="text" name="codice_fiscale" id="codice_fiscale" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['cod_fisc']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap partita_iva">
												<div class="status"></div>
												<input tabindex="4" type="text" name="partita_iva" id="partita_iva" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['iva']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap sesso">
												<div class="status"></div>
												<?=$dizionario['area_clienti']['sesso']?>
												<label margin-right: 10px;" for="maschio"><input tabindex="5"  id="maschio" value="1" name="sesso" type="radio" style="width: auto;">M</label>
												<label margin-right: 10px;" for="femmina"><input tabindex="6" id="femmina" name="sesso" value="2" type="radio" style="width: auto;">F</label>
												<label margin-right: 10px;" for="nd"><input class="no-edit" tabindex="7" id="nd" name="sesso" value="3" type="radio" style="width: auto;">N.D.</label>
												<div class="clear"></div>
												<div id="sesso_val"></div>
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap nome">
												<h3><?=$dizionario['area_clienti']['residenza']?></h3>
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap indirizzo">
												<div class="status"></div>
												<input tabindex="8" type="text" name="indirizzo" id="indirizzo" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['indirizzo']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap cap">
												<div class="status"></div>
												<input tabindex="9" type="text" name="cap" id="cap" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['cap']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap nazione">
												<div class="status"></div>
												<select tabindex="10" name="nazione" id="nazione" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false">
												<?php 
													foreach ($rowNazione as $key=>$value){
														echo "<option value='".$value['NazioneId']."'>".$value['Nazione']."</option>";
													}
												?>
												</select>
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap cap">
												<div class="status"></div>
												<input tabindex="11" type="text" name="comune" id="autocomplete" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['comune']?>" />
												<input type='hidden' name='comuneId' id='comuneId' value='0'/>
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap nome">
												<h3><?=$dizionario['area_clienti']['recapito']?></h3>
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap email">
												<div class="status"></div>
												<input tabindex="12" type="text" name="email" id="email" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['email']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap conferma_email">
												<div class="status"></div>
												<input tabindex="13" type="text" name="conferma_email" id="conferma_email" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['conferma']?> <?=$dizionario['area_clienti']['email']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap numero_personale">
												<div class="status"></div>
												<input tabindex="14" type="text" name="numero_personale" id="numero_personale" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['tel']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap numero_cellulare">
												<div class="status"></div>
												<input tabindex="15" type="text" name="numero_cellulare" id="numero_cellulare" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['cel']?>" />
											</span>
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="bg-contact">
											<span class="wpcf7-form-control-wrap numero_familiare">
												<div class="status"></div>
												<input tabindex="16" type="text" name="numero_familiare" id="numero_familiare" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['tel_val']?>" />
											</span>
										</div>
									</div>
									
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
									
									
									<div class="col-md-12">
										<?=$dizionario['area_clienti']['termini_accetto']?>
									</div>
									<div class="form-group col-md-12">
										<label class="concheck"><?=$dizionario['area_clienti']['accetto']?>
											<input type="checkbox" id="privacy" name="privacy" tabindex="19">
											<span class="checkmark1"></span>
											<div id="privacy_error"></div>
										</label>
									</div>
									<div class="col-md-12">
										<?=$dizionario['area_clienti']['privacy_accetto']?>
									</div>
									<div class="form-group col-md-12">
										<label class="concheck"><?=$dizionario['area_clienti']['privacy_accetto_1']?>
											<input type="checkbox" id="privacy_accetto_1" name="privacy_accetto_1" tabindex="20">
											<span class="checkmark1"></span>
											<div id="privacy_accetto_1_error"></div>
										</label>
									</div>
									<div class="form-group col-md-12">
										<label class="concheck"><?=$dizionario['area_clienti']['privacy_accetto_2']?>
											<input type="checkbox" id="privacy_accetto_2" name="privacy_accetto_2" tabindex="21">
											<span class="checkmark1"></span>
											<div id="privacy_accetto_2_error"></div>
										</label>
									</div>
									<div class="form-group col-md-12">
										<label class="concheck"><?=$dizionario['area_clienti']['newsletter']?>
											<input type="checkbox" id="newsletter" name="newsletter" tabindex="22">
											<span class="checkmark1"></span>
										</label>
									</div>
								</div>
							</div>  
						</div>
                        <div class="submit_holder">
							<button type="submit" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="registrati"><i class="fa fa-user"></i> <?=$dizionario['Registrati']?></button>
                    	</div><!-- submit_holder -->
							
							
							
							
							
							
							
							
							
							
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
	    function indirizzoValidate(){
	    	if($('#indirizzo').val() == ''){
		    	$('#indirizzo').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
				$('#indirizzo').parent().children('.status').html("");
				 return true;
			}
		}

	    function privacyValidate(){
	    	if(!$('#privacy').is(':checked')){
		    	$('#privacy').parent().children('#privacy_error').html("<?php echo $dizionario['area_clienti']['privacy_error'];?>");
		    	return false;
			} else {
				$('#privacy').parent().children('#privacy_error').html("");
				 return true;
			}
		}

	    function privacy1Validate(){
	    	if(!$('#privacy_accetto_1').is(':checked')){
		    	$('#privacy_accetto_1').parent().children('#privacy_accetto_1_error').html("<?php echo $dizionario['area_clienti']['privacy_1_error'];?>");
		    	return false;
			} else {
				$('#privacy_accetto_1').parent().children('#privacy_accetto_1_error').html("");
				 return true;
			}
		}

	    function privacy2Validate(){
	    	if(!$('#privacy_accetto_2').is(':checked')){
		    	$('#privacy_accetto_2').parent().children('#privacy_accetto_2_error').html("<?php echo $dizionario['area_clienti']['privacy_1_error'];?>");
		    	return false;
			} else {
				$('#privacy_accetto_2').parent().children('#privacy_accetto_2_error').html("");
				 return true;
			}
		}
	    
	    function capValidate(){
	    	if($('#cap').val() == ''){
		    	$('#cap').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
				$('#cap').parent().children('.status').html("");
				 return true;
			}
		}
		
	    function comuneValidate(){
	    	if($('#comuneId').val() == 0){
		    	$('#comuneId').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
				$('#comuneId').parent().children('.status').html("");
				 return true;
			}
		}
	
    	function nomeValidate(){
    		var regex = /^[A-Za-z ]+$/;
			  if(!regex.test($('#nome').val())){
				$('#nome').parent().children('.status').html("<?=$dizionario['area_clienti']['nome_non_valido']?>");
				return false;
			 } else {
				 $('#nome').parent().children('.status').html("");
				 return true;
			 }
    	}
    	function cognomeValidate(){
    		if($('#cognome').val() == ''){
		    	$('#cognome').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
	    		 var regex = /^[A-Za-z ]+$/;
				  if(!regex.test($('#cognome').val())){
					$('#cognome').parent().children('.status').html("<?=$dizionario['area_clienti']['cognome_non_valido']?>");
					return false;
				 } else {
					 $('#cognome').parent().children('.status').html("");
					 return true;
				 }
			}
    	}
    	function emailValidate(){
    		if($('#email').val() == ''){
		    	$('#email').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
	    		var regex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/g;
				  if(!regex.test($('#email').val())){
					$('#email').parent().children('.status').html("<?=$dizionario['area_clienti']['email_non_valida']?>");
					return false;
				 } else {
					 $('#email').parent().children('.status').html("");
					 return true;
				 }
			}
    	}
    	function emailConfermaValidate(){
    		var regex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/g;
			  if(!regex.test($('#conferma_email').val())){
				$('#conferma_email').parent().children('.status').html("<?=$dizionario['area_clienti']['email_non_valida']?>");
				return false;
			 } else if($('#conferma_email').val() != $('#email').val()){
				 $('#conferma_email').parent().children('.status').html("<?=$dizionario['area_clienti']['email_non_uguale']?>");
				 return false;
			 } else {
				 $('#conferma_email').parent().children('.status').html("");
				 return true;
			 }
    	}
    	function passwordValidate(){
			var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*()\-_=+<>?]{8,}$/;
			  if(!regex.test($('#password').val())){
				$('#password').parent().children('.status').html("<?=$dizionario['area_clienti']['psw_non_vaida']?>");
				return false;
			} else {
				 $('#password').parent().children('.status').html("");
				 return true;
			 }
    	}
    	function passwordConfermaValidate(){
			var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*()\-_=+<>?]{8,}$/;
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

    	function personaleValidate(){
	    	if($('#numero_personale').val() == ''){
		    	$('#numero_personale').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
				 var regex = /^[\s()+-]*([0-9][\s()+-]*){6,20}$/;
				 if(!regex.test($('#numero_personale').val())){
					$('#numero_personale').parent().children('.status').html("<?=$dizionario['area_clienti']['tel_non_valido']?>");
					return false;
				 } else {
					 $('#numero_personale').parent().children('.status').html("");
					 return true;
				 }
			}
		}

	    function cellulareValidate(){
	    	if($('#numero_cellulare').val() == ''){
		    	$('#numero_cellulare').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
				 var regex = /^[\s()+-]*([0-9][\s()+-]*){6,20}$/;
				 if(!regex.test($('#numero_cellulare').val())){
					$('#numero_cellulare').parent().children('.status').html("<?=$dizionario['area_clienti']['tel_non_valido']?>");
					return false;
				 } else {
					 $('#numero_cellulare').parent().children('.status').html("");
					 return true;
				 }
			}
		}

	    function familiareValidate(){
		    if($('#numero_familiare').val() == ''){
		    	$('#numero_familiare').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
				 var regex = /^[\s()+-]*([0-9][\s()+-]*){6,20}$/;
				 if(!regex.test($('#numero_familiare').val())){
					$('#numero_familiare').parent().children('.status').html("<?=$dizionario['area_clienti']['tel_non_valido']?>");
					return false;
				 } else {
					 $('#numero_familiare').parent().children('.status').html("");
					 return true;
				 }
			}
		}

	    function sessoValidate(){
		    var sesso = $('input[name=\'sesso\']:checked').val();
		    
		    if(typeof sesso == 'undefined' || sesso == '' || sesso<1 || sesso>3){
		    	$('input[name=\'sesso\']').parent().parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
				$('input[name=\'sesso\']').parent().parent().children('.status').html("");
				return true; 
			}
		}

		function changeNazione(){
			var formData = {
					action: 'getComune',
					nazione: $('#nazione').val(),
					comune: ''
			};
			$.ajax({
			    type: "POST",
			    url: "/gestione_utente.php",
			    // The key needs to match your method's input parameter (case-sensitive).
			    data: formData,
			    dataType: "json",
			    success: function(data){
			    	
			    	currencies = new Array();
					data.result.forEach(function(element, index, array){
						currencies.push( { value: element['Comune'], data: element['ComuneId'] });
					});
					$('#autocomplete').autocomplete({
					    lookup: currencies,
					    select: function (event, suggestion) {
					      $('#comuneId').val(suggestion.item.data);
					    },
					    source:currencies
					  });
					
				}
			
			});
		}
    	
		$(document).ready(function(){
			changeNazione(); 
			
			$('#privacy').change(function(){
				privacyValidate(); 
			});
			$('#privacy_accetto_1').change(function(){
				privacy1Validate(); 
			});
			$('#privacy_accetto_2').change(function(){
				privacy2Validate(); 
			});
			$('#nazione').change(function(){
				changeNazione(); 
			});
			$('#indirizzo').change(function(){
				indirizzoValidate(); 
			});
			$('#cap').change(function(){
				capValidate(); 
			});
			$('#autocomplete').change(function(){
				comuneValidate(); 
			});
			$('#nome').change(function(){
				nomeValidate(); 
			});
			$('#cognome').change(function(){
				cognomeValidate();
			});
			$('#numero_personale').change(function(){
				personaleValidate();
			});
			$('#numero_cellulare').change(function(){
				cellulareValidate();
			});
			$('#numero_familiare').change(function(){
				familiareValidate();
			});
			$('input[name=\'sesso\']').change(function(){
				sessoValidate();
			});	
			$('#email').change(function(){
				emailValidate();
			});
			$('#conferma_email').change(function(){
				emailConfermaValidate();
			});
			$('#password').change(function(){
				passwordValidate();
			});
			$('#conferma_password').change(function(){
				passwordConfermaValidate();
			});
			$('#registrati').on('click', function(){
				var indirizzo = indirizzoValidate();
				var cap = capValidate();
				var comune = comuneValidate();
				var name = nomeValidate();
				var cognome = cognomeValidate();
				var email = emailValidate();
				var emailC = emailConfermaValidate();
				var psw = passwordValidate();
				var pswC = passwordConfermaValidate();
				var numPersonale = personaleValidate();
				var numCellulare = cellulareValidate();
				var numFamiliare = familiareValidate();
				var sesso = sessoValidate();
				var privacy = privacyValidate();
				var privacy1 = privacy1Validate();
				var privacy2 = privacy2Validate();
				
				if( nome && cognome && email && emailC && psw && pswC && numPersonale && numCellulare && numFamiliare && sesso && indirizzo && comune && cap && privacy && privacy1 && privacy2){
					var privacy = 1;
					if(!$('#privacy').is(':checked')){
						privacy = 0;
					}
					var privacy_accetto_1 = 1;
					if(!$('#privacy_accetto_1').is(':checked')){
						privacy_accetto_1 = 0;
					}
					var privacy_accetto_2 = 1;
					if(!$('#privacy_accetto_2').is(':checked')){
						privacy_accetto_2 = 0;
					}
					var newsletter = 1;
					if(!$('#newsletter').is(':checked')){
						newsletter = 0;
					}
					
					var formData = {
							action: 'registrazione',
							nome: $('#nome').val(),
							cognome: $('#cognome').val(),
							codiceFiscale: $('#codice_fiscale').val(),
							partitaIva: $('#partita_iva').val(),
					        email: $('#email').val(),
					        password: $('#password').val(),
					        sesso: $('input[name=\'sesso\']:checked').val(),
					        indirizzo: $('#indirizzo').val(),
					        cap: $('#cap').val(),
					        nazione: $('#nazione').val(),
					        comune: $('#comuneId').val(),
					        numeroPersonale: $('#numero_personale').val(),
					        numeroCellulare: $('#numero_cellulare').val(),
					        numeroFamiliare: $('#numero_familiare').val(),
					        privacy: privacy,
					        privacy_accetto_1: privacy_accetto_1,
					        privacy_accetto_2: privacy_accetto_2,
					        newsletter: newsletter 
					};
					$.ajax({
					    type: "POST",
					    url: "/gestione_utente.php",
					    // The key needs to match your method's input parameter (case-sensitive).
					    data: formData,
					    dataType: "text",
					    success: function(data){
							if(data.indexOf("false") < 0){			
								window.location.replace("/login.php?tipo=r")
							} else {
								$('#email').parent().children('.status').html("Email gi&agrave; presente. Utilizza un'altra email");
							}
						},
					    failure: function(errMsg) {
					        alert(errMsg);
					    }
					});
				} else {
					window.scrollTo(0, 0);
				}
			});
				
		});
    </script>

</html>