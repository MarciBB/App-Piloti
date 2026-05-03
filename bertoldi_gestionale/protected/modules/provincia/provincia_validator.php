
<!--<script src="/js/jquery-autocomplete/autocomplete.custom.js" type="text/javascript"></script>-->

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
           url: "/protected/modules/provincia/provincia_action.php",
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
                loadMainContent('provincia','provincia.php',this);
                avviso_operazione(messaggio);
                }
                messaggio=msg;
              
                if ($("#brain_oscura").css("display")=="none")
                {
                loadMainContent('provincia','provincia.php',this);
                avviso_operazione(messaggio);
                }
                else
                {
                    ChiudiBox();

                }    
                
              
              
               
              
               
            } // end success
           
         });
        
        
    }
    
    function getRegione(ElementId)
    {  
    if ((ElementId!=0)&&(ElementId!=""))
        {       
     page_to_load="/protected/modules/regione/regione.php?do=get_regione_camping&NazioneId="+ElementId;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
                    $("#Regione").html(data);                   

              } );              
        }
    }
   
    
   $(document).ready(function() { 
    
	      $("#application_form").validate({
                submitHandler: function(form) {
               // $(form).ajaxSubmit();
               submit_form();
           }
     });
         $("#DataNascita").mask("99/99/9999");     
        
              
              
          
});       
            
      </script>
