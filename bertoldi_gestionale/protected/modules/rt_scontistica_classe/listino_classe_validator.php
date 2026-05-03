
<script type="text/javascript"> 
    
    
    function avviso_operazione(tipoavv)
    {
       
        
        if (tipoavv=="ok") {    
			$("#loading_big_"+tipoavv).fadeIn(2000,function() {			

                $("#loading_big_"+tipoavv).fadeOut(2000,function() {
                 });
			});
        } else {
			$("#loading_big_"+tipoavv).fadeIn(2000,function() {			
			});
        }
        
    }
    
    function submit_form_listinoclassi()
    {
        
        var messaggio;
        var id;
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_scontistica_classe/listino_classe_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
               
				msg=jQuery.trim(msg);
				   
				if(msg.indexOf(',')>-1) {
					arr_response=msg.split(",");
					messaggio=arr_response[0];
					id=arr_response[1];
					alert(messaggio);
				} else {
					messaggio=msg;
				}
				if ($("#brain_oscura").css("display")=="none") {
					loadMainContent('rt_scontistica','listino.php',this);
					avviso_operazione(messaggio);
				} else {
					ChiudiBox();
					loadMainContent('rt_scontistica','listino.php?do=add&step=2',this);
				}    
               
            } // end success
         });
    }
    
   function onLineaIdChange(selectElement) {
		// Recupera il valore selezionato dall'elemento passato
		const lineaId = $(selectElement).val();

		// Controlla se il valore selezionato non è vuoto
		if (lineaId !== '') {
			// Effettua la chiamata AJAX
			$.ajax({
				type: "POST",
				url: "/protected/modules/rt_scontistica_classe/listino_classe_action.php",
				data: {
					action: "getCorse",
					lineaId: lineaId
				},
				dataType: "json",
				success: function (response) {
					// Azzerare il contenuto di #elencoCorse
					$('#elencoCorse').html('');

					// Itera attraverso i risultati e crea le checkbox
					response.forEach(function (corsa) {
						const checkboxHtml = `
							<div class="brain_campoForm">
								<label for="Corsa_${corsa.CorsaId}">
									${corsa.CorsaNome}
								</label>
								<input type="checkbox" name="ValiditaPromozioneCorsa[]" id="Corsa_${corsa.CorsaId}" value="${corsa.CorsaId}">
							</div>
						`;
						// Aggiunge la checkbox al contenitore
						$('#elencoCorse').append(checkboxHtml);
					});
				},
				error: function () {
					alert('Errore durante il caricamento delle corse. Riprova.');
				}
			});
		} else {
			// Se il valore è vuoto, azzera il contenuto di #elencoCorse
			$('#elencoCorse').html('');
		}
	}


    
   $(document).ready(function() {
		adatta_dialog_box();   
    
		$("#application_form").validate({
			submitHandler: function(form) {
			   // $(form).ajaxSubmit();
			   submit_form_listinoclassi();
			}
		});   
	});       
            
      </script>
