<script type="text/javascript">
	$(document).ready(function(){
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
	       	url: "/protected/modules/rt_gestioneottimizzata/gestioneottimizzata_action.php",
	      	data: $("#application_form").serialize(),
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
	
	function avviso_operazione(tipoavv) {
	    $("#loading_big_" + tipoavv).fadeIn(2000,function() {
	    	$("#loading_big_" + tipoavv).fadeOut(2000,function() { });
	    });
	}
</script>
