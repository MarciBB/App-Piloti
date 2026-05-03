<script type="text/javascript">
	$(document).ready(function() {

		$("#DaVendere").change(toggleVenditaFields);
    	toggleVenditaFields(false);

		// Gestione visualizzazione campi buono regalo
        $('#VenditaBuonoRegalo').change(function() {
            toggleBuonoRegaloFields();
        });
        
        // Inizializza stato campi buono regalo al caricamento
        toggleBuonoRegaloFields();

		adatta_dialog_box();   
		$("#application_form").validate({
			rules: {
				"Coupon[VenditaEmailDestinatario]": {
					required: function() {
						return $("#VenditaBuonoRegalo").val() == "1";
					},
					email: true
				}
			},
			messages: {
				"Coupon[VenditaEmailDestinatario]": {
					required: "Email destinatario è obbligatoria per i buoni regalo",
					email: "Inserisci un indirizzo email valido"
				}
			},
			submitHandler: function(form) {
				var validate = false;
				if($('#DataDalId').val() == '' || $('#DataAlId').val() == '') {
					validate = true;
				} else {
					var dal = $('#DataDalId').val();
					var dalNew = dal.split("/").reverse().join("-");
					var al = $('#DataAlId').val();
					var alNew = al.split("/").reverse().join("-");
					
					if(dalNew > alNew) {
						alert("Intervallo di validita' non corretto, Controllare le date");
						validate = false;
					} else {
						validate = true;
					}
				}
				if(validate == true) {
					submit_form_pagamento(validate);
				} else {
				}
			}
		});
		$("#getCodiceCoupon").click(function(){
			var data = {
					'action': 'GetCodiceCoupon'
			};
	        page_to_load="/protected/modules/rt_coupon/coupon_action.php";

	        $.ajax({
	            type: "POST",
	            url: page_to_load,
	            data: data,
	            dataType: 'json',
	            success: function(data) {
	            	$('#Codice').val(data.code);
	            }
	        });
		});
		$('#numeroCoupon').parent().hide();
		$("input[name='tipoCreazione']").change(function() {
			if(this.value == 'more'){
				$('#numeroCoupon').parent().show();
				$('#Codice').parent().hide();
				$('#getCodiceCoupon').parent().hide();
				$('#Codice').val("NO");
			} else {
				$('#numeroCoupon').val("1");
				$('#numeroCoupon').parent().hide();
				$('#Codice').parent().show();
				$('#getCodiceCoupon').parent().show();
			}
		});
		if($("input[name='tipoCoupon']").val() == 'fisso'){
			$('#Percentuale').parent().hide();
		} else if ($("input[name='tipoCoupon']").val() == 'percentuale') {
			$('#Importo').parent().hide();
		}
		$("input[name='tipoCoupon']").change(function() {
			if(this.value == 'fisso'){
				$('#Importo').parent().show();
				$('#Percentuale').parent().hide();
				$('#Percentuale').val(0);
			} else {
				$('#Importo').parent().hide();
				$('#Percentuale').parent().show();
				$('#Importo').val(0);
			}
		});

		
		
	});

	function toggleVenditaFields(change = true) {
        if ($("#DaVendere").val() == "1" || $("#DaVendere").val() == 1) {
            $("#vendita_fields").show();
            $("#VenditaStato").attr("required", true);
            $("#VenditaEmail").attr("required", true);
			if (change) {
            	$("#VenditaStato").val("1").change();
			}
        } else {
            $("#vendita_fields").hide();
            $("#buono_regalo_fields").hide();
            $("#VenditaStato").removeAttr("required");
            $("#VenditaEmail").removeAttr("required");
            $("#VenditaEmailDestinatario").removeAttr("required");
            // Reset campi buono regalo
			if (change) {
				$("#VenditaBuonoRegalo").val("0");
				$("#VenditaEmailDestinatario").val("");
				$("#VenditaMessaggioDestinatario").val("");
			}
        }
    }
	
	function toggleBuonoRegaloFields() {
        // Leggi il valore attuale dal DOM
        var buonoRegaloValue = $("#VenditaBuonoRegalo").val();
        
        if (buonoRegaloValue == "1") {
            $("#buono_regalo_fields").show();
            $("#VenditaEmailDestinatario").attr("required", true);
        } else {
            $("#buono_regalo_fields").hide();
            $("#VenditaEmailDestinatario").removeAttr("required");
            // Pulisci i campi SOLO se il valore è effettivamente "0"
            if (buonoRegaloValue == "0") {
                $("#VenditaEmailDestinatario").val("");
                $("#VenditaMessaggioDestinatario").val("");
            }
        }
    }
	
	function submit_form_pagamento(validate) {
		if(validate) {
    		$.ajax({
    	   		type: "POST",
    	       	url: "/protected/modules/rt_coupon/coupon_action.php",
    	      	data: $("#application_form").serialize(),
    	      	dataType: 'json',
    	    	success: function(data) {
					if(data.result) {
						ChiudiBox();
						avviso_operazione("ok");
						setTimeout(function() {
							loadMainContent('rt_coupon', 'coupon.php', this);
						}, 2000);
    		    	} else {
    		    		avviso_operazione("no");
    		    	}
    	    	}
    	  	});
	  	} else {
	  	}
	}
	
	function avviso_operazione(tipoavv) {
	    $("#loading_big_" + tipoavv).fadeIn(2000,function() {
	    	$("#loading_big_" + tipoavv).fadeOut(2000,function() { });
	    });
	}

	function stripeLink(couponId) { 
		page_to_load="/protected/modules/rt_coupon/coupon_action.php?do=stripeLink&couponId="+couponId;
       	$.get(page_to_load, function(data){
			window.open('/pagamento_stripe_link.php?session_id='+data.trim(), '_blank');
		} );
	}

	function stripeInvioLink(couponId) { 
		CorsaId = $("#CorsaSelezionataA").val();
		let confirmResult = confirm("<?=$dizionario['biglietto']['val_invia_email']?>");
		if (confirmResult) {  
			let page_to_load = "/protected/modules/rt_coupon/coupon_action.php?do=stripeSendLink&couponId=" + couponId;

			$.ajax({
				type: 'GET',
				url: page_to_load,
				success: function(data) {
					if (data.trim() === "ok") {
						alert("<?=$dizionario['biglietto']['val_email_ok']?>");
					} else {
						alert("<?=$dizionario['biglietto']['val_email_error']?>");
					}
					setTimeout(function() {
						avviso_operazione("ok");
						loadMediazioneStep('rt_coupon', 'coupon.php', this);
						
					}, 2000);
				},
				error: function() {
					alert("Errore di comunicazione con il server. Riprova più tardi.");
					setTimeout(function() {
						avviso_operazione("ok");
						loadMediazioneStep('rt_coupon', 'coupon.php', this);
						
					}, 2000);
				}
			});
		} else {
			setTimeout(function() {
				avviso_operazione("ok");
				loadMediazioneStep('rt_coupon', 'coupon.php', this);
				
			}, 1000);
		}
	}
</script>
