<?php global $dizionario;?>
<script type="text/javascript">

	var action = '<?php echo $action; ?>';
	var importoTotale = 0;

	var loadingSubmit = false;
	$(document).ready(function() {

		adatta_dialog_box();

		$("#application_form #Salva").click(function(){
			$("#application_form #Salva").hide();
		});
		
		$("#application_form").validate({
			submitHandler: function(form) {
				if(loadingSubmit == false){
					submit_form_movimento();
					loadingSubmit = true;
				}
				return false;
			},
			invalidHandler: function(form){
				$("#application_form #Salva").show();
			},
			
		});


			
			return false;
	});


	
	function submit_form_movimento() {
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_biglietto/prenotazione_fattura_action.php",
	      	data: $("#application_form").serialize(),
	      	dataType: 'json',
	      	async: false,
	    	success: function(data) {
		    	ChiudiBox();
		    	avviso_operazione("ok");
		    	loadMediazioneStep('rt_biglietto','biglietto.php?do=add&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
	    	}
	  	});
	}

	function avviso_operazione(tipoavv) {
        $("#loading_big_" + tipoavv).fadeIn(2000,function() {
        	$("#loading_big_" + tipoavv).fadeOut(2000,function() { });
        });
    }



</script>