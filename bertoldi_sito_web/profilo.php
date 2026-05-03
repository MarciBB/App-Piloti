<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['area_clienti']['profilo_cliente'];
$PageDescription = $dizionario['area_clienti']['profilo_cliente'];
$PageKeywords = $dizionario['area_clienti']['key'];

$LinkActive = 4;

if ( ! session_id() ) {
   session_start();
}
$userInfo = $_SESSION['USER'];

if(!isset($userInfo) || $userInfo == "" ){
    header('location: /area-clienti.php');
    die();
}

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
$userInfo = $_SESSION['USER'];

global $db;
$db = new Database();
$db->connect();

$sql = "Select NazioneId, Nazione FROM Nazione";
$rowNazione = $db->fetch_array($sql);


$sql = "SELECT n.NazioneId, c.ComuneId, c.Comune FROM Comune c
		left join Provincia p on c.provincia = p.ProvinciaId
		left join Regione r on r.RegioneId = p.RegioneId
		left join Nazione n on r.idNazione = n.NazioneId
		where  c.ComuneId = '".$userInfo['ComuneResidenzaId']."%'";
$comuneUtente = $db->query_first($sql);
?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>

<style>
	.hidden{
		display:none;
	}
</style>
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
				  <span><a href="/area-clienti.php"><?=$dizionario['area_clienti']['area']?></a> / <?=$dizionario['area_clienti']['profilo_cliente']?></span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<div style="margin:15px !important;">
								<div class="row">
            				<div class="col-md-8">
            					<?=$dizionario['area_clienti']['profilo_intro']?>
                                
                                <br>
                                
                                <div class="info">
                            
                           	 		<div class="row">
                           	 			<div class="col-sm-12">
                                            <div class="bg-contact">
                                            	<span class="wpcf7-form-control-wrap nome">
                                            		<h3><?=$dizionario['area_clienti']['codice']?>: <?php echo $userInfo['MembershipClubCode'];?></h3>
              									</span>
              								</div>
              								
              								<div class="bg-contact">
                                            	<span class="wpcf7-form-control-wrap nome">
                                            		<h3><?=$dizionario['area_clienti']['anagrafica']?></h3>
              									</span>
              								</div>
              								
              								<div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap cognome">
                                                		<div class="status"></div>
                  										<input tabindex="1" type="text" readonly="readonly" name="cognome" id="cognome" value="<?php echo $userInfo['CognomeRagioneSociale'];?>" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['cognome']?>" />
                  									</span>
                  								</div>
                                            </div>
                                            
                                            <div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap nome">
                                                		<div class="status"></div>
                  										<input tabindex="2" type="text" readonly="readonly" name="nome" id="nome" value="<?php echo $userInfo['Nome'];?>" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['nome']?>" />
                  									</span>
                  								</div>
                                            </div>
                                
                                			<div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap codice_fiscale">
                                                		<div class="status"></div>
                  										<input tabindex="3" type="text" name="codice_fiscale" id="codice_fiscale" value="<?php echo $userInfo['CodiceFiscale'];?>" size="40" class="no-edit wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['cod_fisc']?>" />
                  									</span>
                  								</div>
                                            </div>
                                
                                			<div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap partita_iva">
                                                		<div class="status"></div>
                  										<input tabindex="4" type="text" name="partita_iva" id="partita_iva" value="<?php echo $userInfo['PartitaIva'];?>" size="40" class="no-edit wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['iva']?>" />
                  									</span>
                  								</div>
                                            </div>
                                
                                			<div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap sesso">
                                                		<div class="status"></div>
                                                		<?=$dizionario['area_clienti']['sesso']?>
                  										<label margin-right: 10px;" for="maschio"><input class="no-edit" tabindex="5"  id="maschio" value="1" name="sesso" type="radio" style="width: auto;">M</label>
                                                        <label margin-right: 10px;" for="femmina"><input class="no-edit" tabindex="6" id="femmina" name="sesso" value="2" type="radio" style="width: auto;">F</label>
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
                  										<input tabindex="8" type="text" name="indirizzo" id="indirizzo" value="<?php echo $userInfo['IndirizzoResidenza'];?>" size="40" class="no-edit wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['indirizzo']?>" />
                  									</span>
                  								</div>
                                            </div>
                                            
                                            <div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap cap">
                                                		<div class="status"></div>
                  										<input tabindex="9" type="text" name="cap" id="cap" value="<?php echo $userInfo['CapResidenza'];?>" size="40" class="no-edit wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['cap']?>" />
                  									</span>
                  								</div>
                                            </div>
                                            
                                            <div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap nazione">
                                                		<div class="status"></div>
                                                		<select tabindex="10" name="nazionee" id="nazione" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" disabled="true">
                                                        <?php 
                                                        	foreach ($rowNazione as $key=>$value){
    															if ($value['NazioneId'] == $comuneUtente['NazioneId']){
    																echo "<option value='".$value['NazioneId']."' selected='selected'>".$value['Nazione']."</option>";
    															} else {
    																echo "<option value='".$value['NazioneId']."'>".$value['Nazione']."</option>";
    															}
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
                                                		<input tabindex="11" type="text" name="comune" id="autocomplete" value="<?php echo $comuneUtente['Comune'];?>" size="40" class="biginput no-edit wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['comune']?>" />
            									        <input type='hidden' name='comuneId' id='comuneId' value='<?php echo $comuneUtente['ComuneId'];?>'/>
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
                                                		<input readonly tabindex="12" type="text" name="email" id="email" value="<?php echo $userInfo['Email'];?>" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['email']?>" />
                  									</span>
                  								</div>
                                            </div>
                                            
                                            <div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap numero_personale">
                                                		<div class="status"></div>
                                                		<input tabindex="14" type="text" name="numero_personale" id="numero_personale" value="<?php echo $userInfo['Telefono'];?>" size="40" class="no-edit wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['tel']?>" />
                  									</span>
                  								</div>
                                            </div>
                                
                                			<div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap numero_cellulare">
                                                		<div class="status"></div>
                                                		<input tabindex="15" type="text" name="numero_cellulare" id="numero_cellulare" value="<?php echo $userInfo['Cellulare'];?>" size="40" class="no-edit wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['cel']?>" />
                  									</span>
                  								</div>
                                            </div>
                                			
                                			<div class="col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap numero_familiare">
                                                		<div class="status"></div>
                                                		<input tabindex="16" type="text" name="numero_familiare" id="numero_familiare" value="<?php echo $userInfo['TelefonoFamiliare'];?>" size="40" class="no-edit wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['tel_val']?>" />
                  									</span>
                  								</div>
                                            </div>
                                
                                			<div class="hidden"> <td colspan=2><?=$dizionario['area_clienti']['psw_mesg']?></div>
                                			
                                			<div class="hidden col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap password">
                                                		<div class="status"></div>
                                                		<input tabindex="17" type="password" name="password" id="password" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['psw']?>" />
                  									</span>
                  								</div>
                                            </div>
                                            
                                            <div class="hidden col-sm-12">
                                                <div class="bg-contact">
                                                	<span class="wpcf7-form-control-wrap conferma_password">
                                                		<div class="status"></div>
                                                		<input tabindex="18" type="password" name="conferma_password" id="conferma_password" value="" size="40" class="biginput wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="<?=$dizionario['area_clienti']['conferma']?> <?=$dizionario['area_clienti']['psw']?>" />
                  									</span>
                  								</div>
                                            </div>
                                            
                                        </div>
                           	 		</div>
                           	 		
                       	 		</div>
                               		
               					<div class="submit_holder">
									<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="modifica"><i class="fa fa-pencil"></i> <?=$dizionario['area_clienti']['modifica']?></button>
									<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="salva"><i class="fa fa-check"></i> <?=$dizionario['area_clienti']['salva']?></button>
									<button type="button" class="btn btn-big btn-default btn-space" style="width: 100%;" id="annulla"><i class="fa fa-arrow-left"></i> <?=$dizionario['area_clienti']['annulla']?></button>

								</div><!-- submit_holder -->
            				</div>
            				
            				<div class="col-md-4">

									<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/profilo.php'"><i class="fa fa-user"></i> <?=$dizionario['area_clienti']['profilo']?></button>
									<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/mie-prenotazioni.php'"><i class="fa fa-ticket  "></i> <?=$dizionario['area_clienti']['prenotazioni']?></button>
									<!-- <button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/miei-coupon.php'"><i class="fa fa-ticket  "></i> <?=$dizionario['area_clienti']['coupon']?></button>-->
									<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="logout-profilo"><i class="fa fa-sign-out"></i> <?=$dizionario['area_clienti']['logout']?></button>

								
            				</div>
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
	    function indirizzoValidate(){
	    	if($('#indirizzo').val() == ''){
		    	$('#indirizzo').parent().children('.status').html("<?=$dizionario['area_clienti']['obbligatorio']?>");
		    	return false;
			} else {
				$('#indirizzo').parent().children('.status').html("");
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
    		 var regex = /^[A-Za-z ]+$/;
			  if(!regex.test($('#cognome').val())){
				$('#cognome').parent().children('.status').html("<?=$dizionario['area_clienti']['cognome_non_valido']?>");
				return false;
			 } else {
				 $('#cognome').parent().children('.status').html("");
				 return true;
			 }
    	}
    	
    	function passwordValidate(){
    		var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/;
    		if($('#password').val() == "" && $('#conferma_password').val() == ""){
				return true;
        	}
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
    		if($('#password').val() == "" && $('#conferma_password').val() == ""){
				return true;
        	}
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
					    onSelect: function (suggestion) {
					      $('#comuneId').val(suggestion.data);
					    }
					  });
					
				}
			
			});
		}
    	
		$(document).ready(function(){
			changeNazione(); 
			
			$('#nazione').change(function(){
				changeNazione(); 
			});
			
			$('#salva').hide();
			$('#annulla').hide();
			$('.no-edit').prop('readonly', true);
			$('input[name=\'sesso\']').prop('disabled', true);
			$('#nazione').prop('disabled', true);
			$('input[name=\'sesso\']').each(function() {
				var sesso = 0;
				<?php if(isset($userInfo['Sesso'])){
					echo "sesso = ".$userInfo['Sesso'].";";
				}
				?>
				  if($( this ).val() == sesso){
					  $( this ).prop('checked', true);
				  }
			});
			$('#modifica').click(function(){
				$('#salva').show();
				$('#annulla').show();
				$('.hidden').show();
				$(this).hide();
				$('.no-edit').prop('readonly', false);
				$('input[name=\'sesso\']').prop('disabled', false);
				$('#nazione').prop('disabled', false);
			});
			$('#annulla').click(function(){
				$('#salva').hide();
				$('#annulla').hide();
				$('.hidden').hide();
				$('#modifica').show();
				$('.no-edit').prop('readonly', true);
				$('input[name=\'sesso\']').prop('disabled', true);
				$('#nazione').prop('disabled', true);
				$('#nome').val('<?php echo $userInfo['Nome']?>');
				$('#cognome').val('<?php echo $userInfo['CognomeRagioneSociale']?>');
				$('#codice_fiscale').val('<?php echo $userInfo['CodiceFiscale']?>');
				$('#partita_iva').val('<?php echo $userInfo['PartitaIva']?>');
				$('#email').val('<?php echo $userInfo['Email']?>');
				$('input[name=\'sesso\']').each(function() {
					var sesso = 0;
					<?php if(isset($userInfo['Sesso'])){
						echo "sesso = ".$userInfo['Sesso'].";";
					}
					?>
					  if($( this ).val() == sesso){
						  $( this ).prop('checked', true);
					  }
				});
				$('#indirizzo').val('<?php echo $userInfo['IndirizzoResidenza']?>');
				$('#cap').val('<?php echo $userInfo['CapResidenza']?>');
				$('input[nome=\'comune\']').val("<?php echo $comuneUtente['Comune']?>");
				$('#comuneId').val('<?php echo $comuneUtente['ComuneId']?>');
				$('#password').val('');
				$('#numero_personale').val('<?php echo $userInfo['Telefono']?>');
				$('#numero_cellulare').val('<?php echo $userInfo['Cellulare']?>');
				$('#conferma_password').val('');
				$("#nazione option").each(function(){
					var nazioneId = 0;
					<?php if(isset($comuneUtente['NazioneId'])){
						echo "nazioneId = " .$comuneUtente['NazioneId'].";";
					} 
					?>
							if($(this).val() == nazioneId){
						    	$(this).prop('selected',true);
							} else {
								$(this).prop('selected',false);
							}
						});
			});
			$('input[name=\'sesso\']').change(function(){
				sessoValidate();
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
			$('#password').change(function(){
				passwordValidate();
			});
			$('#password').click(function(){
				if($(this).val() == ''){
					$(this).val('');
				}
			});
			$('#conferma_password').click(function(){
				if($(this).val() == ''){
					$(this).val('');
				}
			});
			$('#conferma_password').change(function(){
				passwordConfermaValidate();
			});
                        
            $('#logout-profilo').click(function(){
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
                        
			$('#salva').on('click', function(){
				var indirizzo = indirizzoValidate();
				var cap = capValidate();
				var comune = comuneValidate();
				var name = nomeValidate();
				var cognome = cognomeValidate();
				var numPersonale = personaleValidate();
				var numCellulare = cellulareValidate();
				var numFamiliare = familiareValidate();
				var psw = passwordValidate();
				var pswC = passwordConfermaValidate();
				var sesso = sessoValidate();
				if( name && cognome  && psw && pswC && numPersonale && numCellulare && numFamiliare && indirizzo && comune && cap && sesso){

					var formData = {
						action: 'modifica',
						nome: $('#nome').val(),
						cognome: $('#cognome').val(),
				        email: $('#email').val(),
				        password: $('#password').val(),
				        sesso: $('input[name=\'sesso\']:checked').val(),
				        codiceFiscale: $('#codice_fiscale').val(),
				        partitaIva: $('#partita_iva').val(),
				        indirizzo: $('#indirizzo').val(),
				        cap: $('#cap').val(),
				        nazione: $('#nazione').val(),
				        comune: $('#comuneId').val(),
				        numeroTelefono: $('#numero_personale').val(),
				        numeroCellulare: $('#numero_cellulare').val(),
				        numeroFamiliare: $('#numero_familiare').val()};
					$.ajax({
					    type: "POST",
					    url: "/gestione_utente.php",
					    // The key needs to match your method's input parameter (case-sensitive).
					    data: formData,
					    dataType: "json",
					    success: function(data){
							if(data.result == 'true'){
								window.location.replace("/profilo.php")
							} else {
								$('#email').parent().children('.status').html("Email gi&agrave; presente. Utilizza un'altra email");
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