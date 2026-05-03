
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
    
    function submit_form()
    {
        
        var messaggio;
        var id;
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/aula/aula_action.php",
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
                loadMainContent('aula','aula.php',this);
                avviso_operazione(messaggio);
                }
                else
                {
                  ChiudiBox();
                  loadMainContent('gestore','gestore.php?do=add&step=2',this);

                }    
                
              
              
               
              
               
            } // end success
           
         });
        
        
    }
    
    function changeAnagraficaForm(modulo,page,elem)
    {
	
	$("#layer_nero2").toggle();
	page=page+"&tipo="+elem.value;
	
	
	page_to_load="/protected/modules/"+modulo+"/"+page;
	//	$("#layer_nero2").toggle();
	$.get(page_to_load, function(data){
		CaricaInBox(data);
		//$("#layer_nero2").toggle();
	  } );
	  $("#layer_nero2").toggle();
    }    

    
   $(document).ready(function() { 
    
	      $("#application_form").validate({
                submitHandler: function(form) {
               // $(form).ajaxSubmit();
               submit_form();
           }
     });
        
              
              
          
});       
            
      </script>
