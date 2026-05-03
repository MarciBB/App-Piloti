
<script type="text/javascript"> 
    
  var divtoupdate;  
    var newId=0;
    
   
    
   $(document).ready(function() {
     
        
	adatta_dialog_box();   
    
	$("#application_form").validate({
		submitHandler: function(form) {
			var step_corrente=$("#step_corrente").val();
			submit_form_percorso();
         
		}
	});
     
     
    $('#CambiaStatoMediazioneId').submit(function() {

		$.ajax({
           type: "POST",
           url: "/protected/modules/rt_percorso/percorso_action.php",
           data: $("#CambiaStatoMediazioneId").serialize(),
           success: function(msg) {
               
              
               alert(jQuery.trim(msg));
               
            } // end success
           
         });
            
		return false;

	});

	// this starts tracking changes in the form
	if ($('#application_form').length > 0) {
		var oldvals1 = $('#application_form').trackChanges({events: "change blur keypress keydown click", changeListVisible: false});
	}
});       





function ResetPercorsoBreve()
{
         $.ajax({
           type: "POST",
           url: "/protected/modules/rt_percorso/percorso_action.php?do=resetPercorso",
           data: null,
           success: function(msg) {
               alert("reset eseguito");
            } // end success
           
         });
    
    
}

function CambiaStep(modulo,page,elem)
{
    $("#layer_nero2").show();
            $("#brain_menu").find('a').removeClass("brain_sel");
                    $(elem).addClass("brain_sel");


            page_to_load="/protected/modules/"+modulo+"/"+page;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
                    CaricaInBox(data);
                    $("#layer_nero2").hide(); 

              } );
    
    
}

function loadMediazioneStep(modulo,page,elem) {
	if ($("#application_formTrackList")[0].length == 0) {
		CambiaStep(modulo,page,elem);
	} else {
	   var flag=confirm("I dati inseriti nello step corrente non sono stati ancora salvati. Cambiando pagina le modifiche non saranno memorizzate. Continuare?","Si","No");
		if (flag)
		 CambiaStep(modulo,page,elem);
		else
			return false;
	}
 }
     
    
    
   
    
    
    function avviso_operazione(tipoavv)
    {
       tipoavv=jQuery.trim(tipoavv);
        
        if (tipoavv=="ok")
        {    
        $("#loading_big_"+tipoavv).fadeIn(2000,function() {			

                $("#loading_big_"+tipoavv).fadeOut(2000,function() {			

                

                 });
            
        
    

        });
        }
        else
        {
        
            $("#loading_big_"+tipoavv).fadeIn(2000,function() {			

       
    

        });
        
        
        }
        
    }
    
    function submit_form_percorso() {
       
    	$("#layer_nero2").show();
        var step_successivo=$("#step_successivo").val();
        var step_corrente=$("#step_corrente").val();
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_percorso/percorso_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
        	   $("#layer_nero2").hide(); 
        	   msg = msg.trim();
               if(msg == "stop") {
            	   ChiudiBox();
                   loadMainContent('rt_percorso','percorso.php?do=add&step=2',this);
               } else {
            	   avviso_operazione("ok");
                   loadMainContent('rt_percorso','percorso.php?do=add&step='+step_successivo,this);
               }
            } // end success
         });
 
    }
    
	function salvaComune(lineaId, idComune, idBiglietto) {
        $("#ComuneId").val(idComune);
		$("#TipologiaBigliettoId").val(idBiglietto);
        $("#action").val("create_tariffe_comune");
        $( "input[name^='Prezzi[']" ).attr( "disabled", true );
        $( "input[name*='"+lineaId+"_"+idBiglietto+"_"+idComune+"_']" ).attr( "disabled", false );

    	$("#layer_nero2").show();
        var step_successivo=$("#step_successivo").val();
        var step_corrente=$("#step_corrente").val();
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_percorso/percorso_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
           	   $("#ComuneId").val(-1);
			   $("#TipologiaBigliettoId").val(-1);
           	   $("#action").val("create_tariffe");
           	   $( "input[name^='Prezzi[']" ).attr( "disabled", false );
        	   $("#layer_nero2").hide(); 
        	   msg = msg.trim();
               avviso_operazione("ok");
//                if(msg == "stop") {
//             	   ChiudiBox();
//                    loadMainContent('rt_percorso','percorso.php?do=add&step=2',this);
//                } else {
//             	   avviso_operazione("ok");
//                    loadMainContent('rt_percorso','percorso.php?do=add&step='+step_successivo,this);
//                }
            } // end success
         });
 
    }
	
	// Funzione per confermare cancellare la linea
	function confermaCancella(lineaId) {
		// Fai la chiamata AJAX per verificare la linea
		$.ajax({
			url: '/protected/modules/rt_percorso/percorso_action.php',
			type: 'POST',
			data: {
				action: 'check_linea',
				LineaId: lineaId
			},
			success: function(response) {
				if (response.trim() == 'ok') {
					// Mostra un messaggio di conferma per cancellare la linea
					if (confirm("Vuoi procedere alla cancellazione della linea?")) {
						// Se l'utente conferma, chiama la funzione cancella_linea
						cancellaLinea(lineaId);
					}
				} else if (response.trim() == 'no') {
					// Avvisa l'utente che ci sono prenotazioni attive
					if (confirm("Ci sono prenotazioni sulla linea selezionata e non è possibile rimuoverla. Vuoi procedere a disattivarla?")) {
						// Se l'utente conferma, chiama la funzione disattiva_linea
						disattivaLinea(lineaId);
					}
				}
			},
			error: function(xhr, status, error) {
				alert("Si è verificato un errore durante la verifica della linea: " + error);
			}
		});
	}
	
	// Funzione per cancellare la linea
    function cancellaLinea(lineaId) {
        $.ajax({
            url: '/protected/modules/rt_percorso/percorso_action.php',
            type: 'POST',
            data: {
                action: 'cancella_linea',
                LineaId: lineaId
            },
            success: function(response) {
                alert("Operazione completata. La linea è stata rimossa.");
				loadMainContent('rt_percorso','percorso.php?do=add&step=2',this);
            },
            error: function(xhr, status, error) {
                alert("Errore durante la cancellazione della linea: " + error);
            }
        });
    }

    // Funzione per disattivare la linea
    function disattivaLinea(lineaId) {
        $.ajax({
            url: '/protected/modules/rt_percorso/percorso_action.php',
            type: 'POST',
            data: {
                action: 'disattiva_linea',
                LineaId: lineaId
            },
            success: function(response) {
                alert("Operazione completata. La linea è stata disattivata.");
				loadMainContent('rt_percorso','percorso.php?do=add&step=2',this);
            },
            error: function(xhr, status, error) {
                alert("Errore durante la disattivazione della linea: " + error);
            }
        });
    }

            
      </script>
