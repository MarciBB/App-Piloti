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
	       	url: "/protected/modules/rt_busFlotta/busFlotta_action.php",
	      	data: $("#application_form").serialize(),
	      	dataType: 'json',
	    	success: function(data) {
		    	if(data.result) {
			    	ChiudiBox();
			    	
			    	avviso_operazione("ok");
	
			    	loadMainContent('rt_busFlotta', 'busFlotta.php?');
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
</script>
