<script type="text/javascript">
	$(document).ready(function() {
		adatta_dialog_box();   
		$("#application_form").validate({
			submitHandler: function(form) {
				submit_form_pagamento();
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

		
	});
	
	function submit_form_pagamento() {
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_coupon/coupon_action.php",
	      	data: $("#application_form").serialize(),
	      	dataType: 'json',
	    	success: function(data) {
		    	if(data.result) {
			    	ChiudiBox();
			    	
			    	avviso_operazione("ok");
	
			    	loadMainContent('rt_coupon', 'coupon.php?');
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
