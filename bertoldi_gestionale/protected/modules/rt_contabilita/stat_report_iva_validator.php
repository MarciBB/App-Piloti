<?php global $dizionario;?>

<script type="text/javascript"> 
        function getSediByGestore(oggetto)
{
      
     GestoreId=$("#GestoreId").val();
    
       
    page_to_load="/mediazionesoggetti.php?do=getSediByGestore&GestoreId="+GestoreId;
            //	$("#layer_nero2").toggle();
            
    $.ajax({
    url: page_to_load,
    async: false,
  success: function(data){
    
    $("#SedeId").html(data);
  }
});
}
    
</script> 

 
<script type="text/javascript"> 
    $(document).ready(function() {
        
        
   // Datepicker
	var d = new Date();
	
        
        $(function() {
		$( "#Dal" ).datepicker({
			monthNames:
				[<?=$dizionario['generale']['nome_mesi']?>],
				monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
				monthStatus: '<?=$dizionario['generale']['mese_status']?>',
				yearStatus: '<?=$dizionario['generale']['anno_status']?>',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
			dayNames:
				[<?=$dizionario['generale']['nome_giorni']?>],
				dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
				dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
				dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
				dateStatus: '<?=$dizionario['generale']['data_status']?>',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
	            dateFormat: 'dd/mm/yy'
		});
	});
        
        $(function() {
		$( "#Al" ).datepicker({
			monthNames:
				[<?=$dizionario['generale']['nome_mesi']?>],
				monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
				monthStatus: '<?=$dizionario['generale']['mese_status']?>',
				yearStatus: '<?=$dizionario['generale']['anno_status']?>',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
			dayNames:
				[<?=$dizionario['generale']['nome_giorni']?>],
				dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
				dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
				dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
				dateStatus: '<?=$dizionario['generale']['data_status']?>',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
	            dateFormat: 'dd/mm/yy'
		});
	});
	

 });

</script> 


<script type="text/javascript"> 
    

    
   
    
   $(document).ready(function() {
     
        
    
	      $("#application_form").validate({
               //   alert("prova");
                submitHandler: function(form) {
                    
                submit_form_statistiche();
           }
     });
   
});       








    
    
    
    
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
    
    function submit_form_statistiche()
    {
      
         
     
        
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_contabilita/stat_report_iva_processing.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
              
               $('#risultato_report').html(msg);
                     avviso_operazione("ok");
                     //loadMainContent('gestore','gestore.php?do=add&step='+step_successivo,this);
            } // end success
           
         });
        
        
    }
    


            
      </script>
