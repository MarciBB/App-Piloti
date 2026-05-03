
<script type="text/javascript"> 
    
    
    function avviso_operazione(tipoavv)
    {
       
        
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

    function servizioSelect(){
		var tipo = $('#OccupaPosto').val();
		if(tipo == 0){
			$('#EtaDa').val(0);
			$('#EtaA').val(3);
			$('#EtaDa').parent().hide();
			$('#EtaA').parent().hide();
			$('#TipoPrezzo').val(0);
			$('#TipoBigliettoIdRiferimento').val("");
			$('#TipoBigliettoIdRiferimento').attr('disabled', true);
			$('#TipoPrezzo').parent().hide();
			$('#TipoBigliettoIdRiferimento').parent().hide();
		} else {
			$('#EtaDa').parent().show();
			$('#EtaA').parent().show();
			$('#TipoPrezzo').parent().show();
			$('#TipoBigliettoIdRiferimento').parent().show();
		}
    }
    
    function submit_form_tipologiabiglietto()
    {
        
        var messaggio;
        var id;
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_tipologiabiglietto/tipologiabiglietto_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
               
              msg=jQuery.trim(msg);
               
              if(msg.indexOf(',')>-1)
              {
                  arr_response=msg.split(",");
                  messaggio=arr_response[0];
                  id=arr_response[1];
              }
              else
                  messaggio=msg;
              
                if ($("#brain_oscura").css("display")=="none")
                {
                loadMainContent('rt_tipologiabiglietto','tipologiabiglietto.php',this);
                avviso_operazione(messaggio);
                }
                else
                {
                  ChiudiBox();
                  loadMainContent('rt_tipologiabiglietto','tipologiabiglietto.php',this);

                }    
                
              
              
               
              
               
            } // end success
           
         });
        
        
    }
    
   

    
   $(document).ready(function() {
	   servizioSelect();
    	adatta_dialog_box();
		
		if ($('#TipoPrezzo').val() === '0') {
			// Aggiungi l'attributo disabled alla select
				$('#TipoBigliettoIdRiferimento').val("");
				$('#TipoBigliettoIdRiferimento').attr('disabled', true);
			} else {
				// Rimuovi l'attributo disabled dalla select
				$('#TipoBigliettoIdRiferimento').removeAttr('disabled');
			}     
		
    	$('#OccupaPosto').change(function(){
				servizioSelect();
			});
			  $("#application_form").validate({
					submitHandler: function(form) {
				   // $(form).ajaxSubmit();
				   submit_form_tipologiabiglietto();
			   }
		 });
	 
		$('#TipoPrezzo').change(function() {
			  var selectedValue = $(this).val();

			if (selectedValue === '0') {
				$('#TipoBigliettoIdRiferimento').val("");
				$('#TipoBigliettoIdRiferimento').attr('disabled', true);
			} else {
				// Rimuovi l'attributo disabled dalla select
				$('#TipoBigliettoIdRiferimento').removeAttr('disabled');
			}
		});
        
        
              
          
});       
            
      </script>
