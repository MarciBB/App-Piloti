<?php global $dizionario;?>
<script type="text/javascript"> 

  
   $(document).ready(function() {
        
              adatta_dialog_box();   
    
	      $("#application_form").validate({
                submitHandler: function(form) {
                    
                 if(validate_password())
                    submit_form_mediazione_stop();
                else
                    alert("<?=$dizionario['operatore']['mess_password_diverse']?>");
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
    
    function submit_form_mediazione_stop()
    {
       ChiudiBox();
        
        /*$("#brain_oscura").css("display","none");*/
        var step_successivo=$("#step_successivo").val()-1;
        
        /*if ($("#application_form").attr("class")=="nogo")
            step_successivo=step_successivo-1;*/
        
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/operatore/operatore_action.php",
           data: $("#application_form").serialize(),
           success: function(msg1) {
               var msg=jQuery.trim(msg1);
               if (msg!="ok")
               {
                  alert(msg);
               }
               else
                   {
                       alert("<?=$dizionario['operatore']['mess_password_cambiata']?>");
                   
                       avviso_operazione("ok");
                       loadMainContent1('/principale.php',this);
                   }
               
              
               
            } // end success
           
         });
        
        
    }
    


function loadMainContent1(page,elem)
        {
         $("#layer_nero2").hide();
          
        }

function validate_password(){
        var a=$('#password').val();
        var b= $('#co_password').val();
        if(a==b)
            return true;
        else
            return false;       
    }
    

    
    





            
      </script>
