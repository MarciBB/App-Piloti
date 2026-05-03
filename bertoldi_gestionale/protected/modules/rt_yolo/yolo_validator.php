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
	
	
	function avviso_operazione(tipoavv) {
	    $("#loading_big_" + tipoavv).fadeIn(2000,function() {
	    	$("#loading_big_" + tipoavv).fadeOut(2000,function() { });
	    });
	}
	
</script>
