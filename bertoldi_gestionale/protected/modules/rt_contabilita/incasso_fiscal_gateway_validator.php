<?php global $dizionario; ?>

<!-- Script per la gestione della data e della validazione del form -->
<script type="text/javascript">
	$(document).ready(function() {

		// Inizializzazione del datepicker sul campo #Dal
		$(function() {
			$("#Dal").datepicker({
				monthNames: [<?=$dizionario['generale']['nome_mesi']?>], // Nomi dei mesi
				monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>], // Nomi brevi dei mesi
				monthStatus: '<?=$dizionario['generale']['mese_status']?>', // Stato del mese
				yearStatus: '<?=$dizionario['generale']['anno_status']?>', // Stato dell'anno
				weekHeader: 'Sm', // Intestazione della settimana
				weekStatus: '<?=$dizionario['generale']['settimana_status']?>', // Stato della settimana
				dayNames: [<?=$dizionario['generale']['nome_giorni']?>], // Nomi dei giorni
				dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>], // Nomi brevi dei giorni
				dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>], // Nomi minimi dei giorni
				dayStatus: '<?=$dizionario['generale']['giorno_status']?>', // Stato del giorno
				dateStatus: '<?=$dizionario['generale']['data_status']?>', // Stato della data
				dateFormat: 'dd/mm/yy', // Formato della data
				firstDay: 1, // Primo giorno della settimana (lunedì)
				initStatus: '<?=$dizionario['generale']['seleziona_data']?>' // Stato iniziale
			});
		});

	});
</script>

<script type="text/javascript">
	$(document).ready(function() {
		// Validazione del form con id #application_form
		$("#application_form").validate({
			submitHandler: function(form) {
				// Se la validazione va a buon fine, invia il form tramite AJAX
				submit_form_statistiche();
			}
		});
	});

	// Funzione per mostrare un avviso di operazione
	function avviso_operazione(tipoavv) {
		tipoavv = jQuery.trim(tipoavv);

		if (tipoavv == "ok") {
			// Mostra e nasconde l'avviso di successo
			$("#loading_big_" + tipoavv).fadeIn(2000, function() {
				$("#loading_big_" + tipoavv).fadeOut(2000, function() {});
			});
		} else {
			// Mostra l'avviso per altri tipi
			$("#loading_big_" + tipoavv).fadeIn(2000, function() {});
		}
	}

	// Funzione per inviare il form tramite AJAX
	function submit_form_statistiche() {
		$.ajax({
			type: "POST",
			url: "/protected/modules/rt_contabilita/incasso_fiscal_gateway_processing.php",
			data: $("#application_form").serialize(),
			success: function(msg) {
				// Mostra il risultato nel div #risultato_report
				$('#risultato_report').html(msg);
				avviso_operazione("ok");
				// Possibile caricamento di altri contenuti (commentato)
				// loadMainContent('gestore','gestore.php?do=add&step='+step_successivo,this);
			}
		});
	}
</script>
