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
	});
	
	function submit_form_pagamento() {
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_autisti/autisti_action.php",
	      	data: $("#application_form").serialize(),
	      	dataType: 'json',
	    	success: function(data) {
		    	if(data.result) {
			    	ChiudiBox();
			    	
			    	avviso_operazione("ok");
	
			    	loadMainContent('rt_autisti', 'autisti.php');
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

	function CancellaPin(Pin)
    {
        stringa="<?=$dizionario['autista']['sicuro_cancella_pin']?>";
      
        
         conferma = confirm(stringa);
       
        if (conferma)
       {
           page_to_load="/protected/modules/rt_autisti/autisti_action.php?action=CancellaPin&ClientAppId="+Pin;
       		$.get(page_to_load, function(data){
           
          
	           alert("<?=$dizionario['autista']['pin_cancellato']?>");
	           
	            avviso_operazione("ok");
	               loadMainContent('rt_autisti','autisti.php',this);
              
           
              } );
       } 
        
    }
</script>
