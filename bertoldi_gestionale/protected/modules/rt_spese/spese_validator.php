<?php 
	global $dizionario;
?>
<script type="text/javascript">
	$(document).ready(function() {
		adatta_dialog_box();   
		$("#application_form").validate({
			submitHandler: function(form) {
				submit_form_pagamento();
			}
		});
		
		$('#Costo').keyup(function () {
			let value = $(this).val(); // Ottieni il valore corrente
			let formattedValue = value.replace(',', '.'); // Sostituisci la virgola con il punto
			$(this).val(formattedValue); // Aggiorna il campo con il valore corretto
		});
	});
	
	function submit_form_pagamento() {
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_spese/spese_action.php",
	      	data: $("#application_form").serialize(),
	      	dataType: 'json',
	    	success: function(data) {
		    	if(data.result) {
			    	ChiudiBox();
			    	
			    	avviso_operazione("ok");
	
			    	loadMainContent('rt_spese', 'spese.php');
		    	} else {
		    		avviso_operazione("no");
		    	}
	    	}
	  	});
	}
	
	function avviso_operazione(tipoavv) {
	    $("#loading_big_" + tipoavv).fadeIn(2000,function() {
	    	$("#loading_big_" + tipoavv).fadeOut(2000,function() { });
	    });
	}
	
	function CancellaSpesa(SpesaId) {
        stringa="<?=$dizionario['spese']['sicuro_cancella_spesa']?>";
      	conferma = confirm(stringa);
       	if (conferma) {
        	page_to_load="/protected/modules/rt_spese/spese_action.php?action=CancellaSpesa&SpesaId="+SpesaId;
       		$.get(page_to_load, function(data){
				console.log(data);
	        	alert("<?=$dizionario['spese']['spesa_cancellata']?>");
		        avviso_operazione("ok");
		        loadMainContent('rt_spese','spese.php',this);
            });
       }  
    }
</script>
