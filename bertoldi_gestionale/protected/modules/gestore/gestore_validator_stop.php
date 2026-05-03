
<script type="text/javascript"> 

    
   
    
   $(document).ready(function() {
        
              adatta_dialog_box();   
    
	      $("#application_form").validate({
                submitHandler: function(form) {
                submit_form_gestore_stop();
                
                
             
               
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
    
    function submit_form_gestore_stop()
    {
       ChiudiBox();
        
        /*$("#brain_oscura").css("display","none");*/
        var step_successivo=$("#step_successivo").val()-1;
        
        /*if ($("#application_form").attr("class")=="nogo")
            step_successivo=step_successivo-1;*/
        
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/gestore/gestore_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
               
               
               if (step_successivo=="6")
                   {
                         if (jQuery.trim(msg)!="ok")
                    {    
                        
                        testo="La a mediazione e' stata inserita con successo. Il numero di protocollo assegnato e': "+jQuery.trim(msg);
                       alert(testo);
                      
                    }
                   }    
               
               
               avviso_operazione("ok");
               loadMainContent1('mediazione','mediazione.php?do=add&step='+step_successivo,this);
               
               
              
               
            } // end success
           
         });
        
        
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


   
            
      </script>
