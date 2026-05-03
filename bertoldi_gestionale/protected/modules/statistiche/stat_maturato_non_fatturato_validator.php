

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
				['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
				monthStatus: 'Mostra un altro mese',
				yearStatus: 'Mostra un altro anno',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: 'Settimana dell\'anno',
		dayNames:
				['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
				dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
				dayStatus: 'Imposta DD come primo giorno della settimana',
				dateStatus: 'Seleziona DD, M d',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: 'Seleziona una data',
                                dateFormat: 'dd/mm/yy'});
	});
        
        $(function() {
		$( "#Al" ).datepicker({
		monthNames:
				['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
				monthStatus: 'Mostra un altro mese',
				yearStatus: 'Mostra un altro anno',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: 'Settimana dell\'anno',
		dayNames:
				['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
				dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
				dayStatus: 'Imposta DD come primo giorno della settimana',
				dateStatus: 'Seleziona DD, M d',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: 'Seleziona una data',
                                dateFormat: 'dd/mm/yy'});
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
           url: "/protected/modules/statistiche/stat_maturato_non_fatturato_processing.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
              
               $('#risultato_report').html(msg);
                     avviso_operazione("ok");
                     //loadMainContent('gestore','gestore.php?do=add&step='+step_successivo,this);
            } // end success
           
         });
        
        
    }
    


            
      </script>
