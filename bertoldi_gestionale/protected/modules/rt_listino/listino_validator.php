
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
        
        
function CambiaStep(modulo,page,elem)
{
    $("#layer_nero2").show();
            $("#brain_menu").find('a').removeClass("brain_sel");
                    $(elem).addClass("brain_sel");


            page_to_load="/protected/modules/"+modulo+"/"+page;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
                    CaricaInBox(data);
                    $("#layer_nero2").hide(); 

              } );
    
    
}


    
    
function loadMediazioneStep(modulo,page,elem)
    {
          CambiaStep(modulo,page,elem);
          /*  if ($("#application_formTrackList")[0].length == 0) {
                CambiaStep(modulo,page,elem);
                    } else {
                       var flag=confirm("I dati inseriti nello step corrente non sono stati ancora salvati. Cambiando pagina le modifiche non saranno memorizzate. Continuare?","Si","No");
                        if (flag)
                         CambiaStep(modulo,page,elem);
                        else
                            return false;
                    }*/
     }
    
    function submit_form_listino()
    {
        
        var messaggio;
        var id;
        var step_successivo=$("#step_successivo").val();
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_listino/listino_action.php",
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
              
               
                 avviso_operazione("ok");
                 if (step_successivo=='0')
                      loadMainContent('rt_listino','listino.php?do=edit&IdListino=0',this);
                  else
                   loadMainContent1('rt_listino','listino.php?do=add&step='+step_successivo,this);
              
                
              
              
               
              
               
            } // end success
           
         });
        
        
    }
    
   

    
   $(document).ready(function() {
    adatta_dialog_box();   
    
	      $("#application_form").validate({
                submitHandler: function(form) {
               // $(form).ajaxSubmit();
               submit_form_listino();
           }
     });
        
              
              
           // this starts tracking changes in the form
                
               // var oldvals1 = $('#application_form').trackChanges({events: "change blur keypress keydown click", changeListVisible: false});
  
});       
            
      </script>
