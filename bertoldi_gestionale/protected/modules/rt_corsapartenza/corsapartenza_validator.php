<?php
global $dizionario;
?>
<script type="text/javascript"> 
	function calendario(mese, anno)
	{
		page_to_load="/protected/modules/rt_corsapartenza/corsapartenza_action.php?do=calendario&mese="+mese+"&anno="+anno;
	   	$.get(page_to_load, function(data){
	    	$("#calendario").html(data);
	    } );

	}

    function VerificaPosti(NumeroMinimo,oggetto) {
        numero=oggetto.value;
        if (numero<0) {
        	n=numero*(-1);
            n1=NumeroMinimo*(-1);
               
            if (NumeroMinimo<n) 
            	alert("E' possibile sottrarre al massimo "+NumeroMinimo+" posti!");
            oggetto.value=n*(-1);
        }
    }

        
	function BloccaSbloccaCorsa(Stato, CorsaId, CorsaNome, DataCorsa, OraCorsa, Blocco, l = false, mese = "", anno = "") {
		if (Blocco === 'Bloccare') {
			var stringa = "Sei sicuro di voler bloccare la corsa " + CorsaNome + " del " + DataCorsa + " delle ore " + OraCorsa + "?";
			window.confirmWithOptions(stringa, {
				"Blocca corsa": function () {
					handleBloccaCorsa(Stato, CorsaId, CorsaNome, DataCorsa, OraCorsa, Blocco, l, mese, anno, 0);
					$(this).dialog("close");
				},
				"Blocca corsa e rimborsa i passeggeri": function () {
					handleBloccaCorsa(Stato, CorsaId, CorsaNome, DataCorsa, OraCorsa, Blocco, l, mese, anno, 1);
					$(this).dialog("close");
				},
				"Annulla": function () {
					$(this).dialog("close");
				}
			}, 400);
		} else {
			var stringa = "Sei sicuro di voler sbloccare la corsa " + CorsaNome + " del " + DataCorsa + " delle ore " + OraCorsa + "?";
			window.confirmWithOptions(stringa, {
				"Conferma": function () {
					handleBloccaCorsa(Stato, CorsaId, CorsaNome, DataCorsa, OraCorsa, Blocco, l, mese, anno, 0);
					$(this).dialog("close");
				},
				"Annulla": function () {
					$(this).dialog("close");
				}
			});
		}
	}

	function handleBloccaCorsa(Stato, CorsaId, CorsaNome, DataCorsa, OraCorsa, Blocco, l, mese, anno, rimborso) {
		var page_to_load = "/protected/modules/rt_corsapartenza/corsapartenza_action.php?do=BloccaCorsa&Stato=" + Stato + "&CorsaId=" + CorsaId + "&CorsaData=" + DataCorsa + "&rimborso=" + rimborso;

		$.get(page_to_load, function (data) {
			var bloccoText = "";
			if(Blocco == 'Bloccare') {
				bloccoText = "bloccata";
			} else {
				bloccoText = "sbloccata";
			}
			var message = "La corsa " + CorsaNome + " è stata " + bloccoText + (rimborso ? " e i passeggeri sono stati rimborsati!" : "!");
			window.confirmWithOptions(message, {
				"Ok": function () {
					$(this).dialog("close");
				}
			});
			if (l == false) {
				avviso_operazione("ok");
				loadMainContent('rt_corsapartenza', 'corsapartenza.php', this);
			} else {
				loadMainContent('rt_corsapartenza', 'corsapartenza.php?do=calendario&mese=' + mese + '&anno=' + anno, this);
			}
		});
	}

	// Helper function to create a custom confirm dialog with multiple options
	window.confirmWithOptions = function (message, buttons, width = 300) {
		var dialog = $("<div>").attr("title", "Conferma").html(message).appendTo("body");

		dialog.dialog({
			modal: true,
			buttons: buttons,
			resizable: false,
			width: width,
			close: function () {
				dialog.remove();
			}
		});
	};


	function BloccaSbloccaCorsaWeb(Stato, CorsaId, CorsaNome, DataCorsa, OraCorsa, Blocco, l = false, mese = "", anno = "") {
		var stringa = "Sei sicuro di voler " + ((Blocco == 'Bloccare') ? 'bloccare' : 'sbloccare') + " la corsa " + CorsaNome + " del " + DataCorsa + " delle ore " + OraCorsa + " PER IL WEB?";
		window.confirmWithOptions(stringa, {
			"Conferma": function () {
				var page_to_load = "/protected/modules/rt_corsapartenza/corsapartenza_action.php?do=BloccaCorsaWeb&Stato=" + Stato + "&CorsaId=" + CorsaId + "&CorsaData=" + DataCorsa;
				$.get(page_to_load, function (data) {
					var message = "La corsa " + CorsaNome + " è stata " + ((Blocco == 'Bloccare') ? 'bloccata' : 'sbloccata') + " per il web!";
					window.confirmWithOptions(message, {
						"Ok": function () {
							$(this).dialog("close");
						}
					});
					if (l == false) {
						avviso_operazione("ok");
						loadMainContent('rt_corsapartenza', 'corsapartenza.php', this);
					} else {
						loadMainContent('rt_corsapartenza', 'corsapartenza.php?do=calendario&mese=' + mese + '&anno=' + anno, this);
					}
				});
				$(this).dialog("close");
			},
			"Annulla": function () {
				$(this).dialog("close");
			}
		});
	}

    
	function avviso_operazione(tipoavv) {
		if (tipoavv == "ok") {
			$("#loading_big_" + tipoavv).fadeIn(2000, function () {
				$("#loading_big_" + tipoavv).fadeOut(2000, function () {
					// Aggiungi eventuali azioni da eseguire dopo il fadeOut
				});
			});
		} else {
			$("#loading_big_" + tipoavv).fadeIn(2000, function () {
				// Aggiungi eventuali azioni da eseguire dopo il fadeIn
			});
		}
	}

    
	function submit_form_corsapartenza() {
		var messaggio;
		var id;

		$.ajax({
			type: "POST",
			url: "/protected/modules/rt_corsapartenza/corsapartenza_action.php",
			data: $("#application_form").serialize(),
			success: function (msg) {
				msg = jQuery.trim(msg);
				if (msg.indexOf(',') > -1) {
					arr_response = msg.split(",");
					messaggio = arr_response[0];
					id = arr_response[1];
				} else {
					messaggio = msg;
				}
				if (id == -1) {
					alert(messaggio);
				} else {
					if ($("#brain_oscura").css("display") == "none") {
						loadMainContent('rt_corsapartenza', 'corsapartenza_action.php', this);
						avviso_operazione(messaggio);
					} else {
						CorsaId = '<?=$CorsaId?>';
						DataPartenza = '<?=$DataPartenza?>';
						ExternalLoad('rt_corsapartenza', 'corsapartenza.php?do=GestionePax&CorsaId=' + CorsaId + '&DataPartenza=' + DataPartenza, this);
					}
				}
			}
		});
	}

	function submit_form_cambiodata() {
		var messaggio;
		var id;

		$.ajax({
			type: "POST",
			url: "/protected/modules/rt_corsapartenza/corsapartenza_action.php",
			data: $("#application_form_cambia").serialize(),
			success: function (msg) {
				msg = jQuery.trim(msg);
				if (msg == 'ok') {
					ChiudiBox();
					avviso_operazione("ok");
					alert("<?=$dizionario['partenza']['messaggio_cambio_ok']?>");
					loadMainContent('rt_corsapartenza', 'corsapartenza.php', this);
				} else if (msg == '-1') {
					alert("<?=$dizionario['partenza']['messaggio_cambio_uguale']?>");
				} else {
					alert(messaggio = msg);
				}
			}
		});
	}

	function submit_form_trasferisci() {
		$.ajax({
			type: "POST",
			url: "/protected/modules/rt_corsapartenza/corsapartenza_action.php",
			data: $("#application_form_trasferisci").serialize(),
			success: function (msg) {
				msg = jQuery.trim(msg);
				var response = {};
				try {
					response = JSON.parse(msg);
				} catch (e) {
					response = { success: false, error: msg };
				}

				if (response.success) {
					ChiudiBox();
					avviso_operazione("ok");
					alert("<?=$dizionario['partenza']['messaggio_trasferimento_ok']?>");
					loadMainContent('rt_corsapartenza', 'corsapartenza.php', this);
				} else if (typeof response.code !== 'undefined') {
					switch(response.code) {
						case 'ERR_MISSING_PARAMS':
							alert("<?=$dizionario['partenza']['messaggio_errore_parametri_mancanti']?>");
							break;
						case 'ERR_NO_CHANGE':
							alert("<?=$dizionario['partenza']['messaggio_nessuna_modifica']?>");
							break;
						case 'ERR_NO_BOOKINGS':
							alert("<?=$dizionario['partenza']['messaggio_no prenotazioni_trasferire']?>");
							break;
						case 'ERR_NO_BOOKINGS_SWAP':
							alert("<?=$dizionario['partenza']['messaggio_no prenotazioni_scambio']?>");
							break;
						default:
							alert("Errore: " + response.error);
					}
				} else if (typeof response.error !== 'undefined') {
					alert("Errore: " + response.error);
				} else {
					alert("<?=$dizionario['partenza']['messaggio_errore_generico_trasferimento']?>");
				}
			},
			error: function (xhr, status, error) {
				alert("Errore AJAX: " + error);
			}
		});
	}

	$(document).ready(function () {
		adatta_dialog_box();

		$("#application_form").validate({
			submitHandler: function (form) {
				submit_form_corsapartenza();
			}
		});

		$('#application_form_cambia').validate({
			submitHandler: function (form) {
				submit_form_cambiodata();
			}
		});

		$('#application_form_trasferisci').validate({
			submitHandler: function (form) {
				submit_form_trasferisci();
			}
		});
	});
    
   
      </script>
