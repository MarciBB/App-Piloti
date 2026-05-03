
<script type="text/javascript"> 
    
    
    function avviso_operazione(tipoavv){
        if (tipoavv=="ok"){    
	        $("#loading_big_"+tipoavv).fadeIn(2000,function() {			
	                $("#loading_big_"+tipoavv).fadeOut(2000,function() {			
					});
			});
       	}else{
            $("#loading_big_"+tipoavv).fadeIn(2000,function() {			
        	});
        }
    }
    
    function loadMainContent1(modulo,page,elem)
        {
        // $("#brain_oscura").css("display","none");   
        $("#layer_nero2").show();
        page_to_load="/protected/modules/"+modulo+"/"+page;
        //	$("#layer_nero2").toggle();
        $.get(page_to_load, function(data){
                
                $("#brain_main-content").html("");
                $("#brain_main-content").html(data);    
                
                $("#layer_nero2").hide(); 
                
          } );
          
        }
        


 function EmettiTitoliViaggio(CorsaId,DataCorsa)
    {
        stringa="Procedere con l'emissione dei titoli di viaggio?";
      
        
         conferma = confirm(stringa);
       
        if (conferma)
       {
           page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=EmettiTitoliViaggio&CorsaId="+CorsaId+"&CorsaData="+DataCorsa;
       $.get(page_to_load, function(data){
               alert("I titoli di viaggio sono stati emessi correttamente.");
               avviso_operazione("ok");
               loadMediazioneStep("rt_previaggio","previaggio.php?step=1&do=add&DataPartenza="+DataCorsa+"&CorsaId="+CorsaId,null);
              
           
              } );
       } 
        
    }
    
    
 function ConsolidaCorsa(CorsaId,DataCorsa,DataCorsaF)
    {
        stringa="Consolidare la corsa del "+DataCorsaF+"?";
      
        
         conferma = confirm(stringa);
       
        if (conferma)
       {
           page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=ConsolidaCorsa&CorsaId="+CorsaId+"&CorsaData="+DataCorsa;
       $.get(page_to_load, function(data){
               alert("La corsa e' stata consolidata correttamente");
               avviso_operazione("ok");
               loadMediazioneStep("rt_previaggio","previaggio.php?step=4&do=add&DataPartenza="+DataCorsa+"&CorsaId="+CorsaId,null);
              
           
              } );
       } 
        
    }
    
    function InizializzaCorsa(CorsaId,DataCorsa,DataCorsaF)
    {
        stringa="Inizializzare la preparazione della corsa "+DataCorsaF+"?";
      
        
         conferma = confirm(stringa);
       
        if (conferma)
       {
           page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=InizializzaCorsa&CorsaId="+CorsaId+"&CorsaData="+DataCorsa;
       $.get(page_to_load, function(data){
               alert("La corsa e' stata inizializzata correttamente");
               avviso_operazione("ok");
               loadMediazioneStep("rt_previaggio","previaggio.php?step=0&do=add&DataPartenza="+DataCorsa+"&CorsaId="+CorsaId,null);
              
           
              } );
       } 
        
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
                    $("#brain_oscura_pre").hide();

              } );
    
    
}


    
    
function loadMediazioneStep(modulo,page,elem)
    {
          CambiaStep(modulo,page,elem);
          /*  if ($("#application_formTrackList")[0].length == 0) {
                CambiaStep(modulo,page,elem);
                    } else {
                       var flag=confirm("I dati inseriti nello step corrente non sono stati ancora salvati. Cambiando pagina le modifiche non saranno memorizzate. Continuare?","Si","No");
                        if (flag)
                         CambiaStep(modulo,page,elem);
                        else
                            return false;
                    }*/
     }
    
    function submit_form_previaggio(){
        var messaggio;
        var id;
        var step_successivo=$("#step_successivo").val();
        var CorsaId='<?=$CorsaId?>';
        var DataPartenza='<?=$DataPartenza?>';
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_previaggio/previaggio_action.php",
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
              avviso_operazione("ok");
              loadMainContent('rt_previaggio','previaggio.php?do=add&step='+step_successivo+'&CorsaId='+CorsaId+'&DataPartenza='+DataPartenza,this);
            } // end success
         });
    }
    
    function submit_form_gestione() {
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_previaggio/previaggio_action.php",
	      	data: $("#application_form_gestione").serialize(),
	    	success: function(data) {
	    		dialog_box();
				$("#brain_listaSelezione").html(data);
				adatta_dialog_box();		
	    	},
	    	error: function(a,b,c){
		    	alert(a.status);
		    	alert(c);
	    	}
	  	});
	}

    function submit_form_addBus() {
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_previaggio/previaggio_action.php",
	       	dataType: "json",
	      	data: $("#application_form_addBus").serialize(),
	    	success: function(data) {
	    		ChiudiBox();
	    		loadMainContent('rt_previaggio','previaggio.php?do=add&step='+data.step+'&CorsaId='+data.corsaId+'&DataPartenza='+data.dataPartenza,this);	
	    	},
	    	error: function(a,b,c){
		    	alert(a.status);
		    	alert(c);
	    	}
	  	});
	}

	
	
    
   $(document).ready(function() {
   		adatta_dialog_box();   
    	$("#application_form").validate({
                submitHandler: function(form) {
               		// $(form).ajaxSubmit();
               		submit_form_previaggio();
           		} 
   		});

    	$("#application_form_gestione").validate({
         		submitHandler: function(form) {
            		submit_form_gestione();
        		}
    	});

    	$("#application_form_addBus").validate({
      		submitHandler: function(form) {
         		submit_form_addBus();
     		}
 	 	});
 	 	$(".percorsoCompleto").hide();  
});       
            
      </script>
