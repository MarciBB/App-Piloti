
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
           url: "/protected/modules/sede/sede_action.php",
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
                loadMainContent('sede','sede.php',this);
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
    
   

    
   $(document).ready(function() {
    adatta_dialog_box();   
    
	      $("#application_form").validate({
                submitHandler: function(form) {
               // $(form).ajaxSubmit();
               submit_form();
           }
     });
        
              
              
          
});       
            
      </script>
