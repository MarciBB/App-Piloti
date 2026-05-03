<?php global $dizionario;?>
<script type="text/javascript"> 
    
  var divtoupdate;  
    var newId=0;
    
   
    
   $(document).ready(function() {
     
        
             adatta_dialog_box();   
    
	      $("#application_form").validate({
               //   alert("prova");
                submitHandler: function(form) {
                    
                
                var step_corrente=$("#step_corrente").val();
                submit_form_gestore();
         
                
                
             
               
           }
     });
     
     
    $('#CambiaStatoMediazioneId').submit(function() {

     $.ajax({
           type: "POST",
           url: "/protected/modules/gestore/gestore_action.php",
           data: $("#CambiaStatoMediazioneId").serialize(),
           success: function(msg) {
               
              
               alert(jQuery.trim(msg));
               
            } // end success
           
         });
            
return false;

});
     
         
         
         
                // this starts tracking changes in the form
                
                var oldvals1 = $('#application_form').trackChanges({events: "change blur keypress keydown click", changeListVisible: false});
  
});       







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
            if ($("#application_formTrackList")[0].length == 0) {
                CambiaStep(modulo,page,elem);
                    } else {
                       var flag=confirm("<?=$dizionario['convenzione']['mess_continua']?>","<?=$dizionario['generale']['si']?>","<?=$dizionario['generale']['no']?>");
                        if (flag)
                         CambiaStep(modulo,page,elem);
                        else
                            return false;
                    }
     }
     
    
    
    
    
    
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
    
    function submit_form_gestore()
    {
       
         
        /*$("#brain_oscura").css("display","none");*/
        var step_successivo=$("#step_successivo").val();
        var step_corrente=$("#step_corrente").val();
        
        /*if ($("#application_form").attr("class")=="nogo")
            step_successivo=step_successivo-1;*/
        
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/gestore/gestore_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
                     avviso_operazione("ok");
                     loadMainContent('gestore','gestore.php?do=add&step='+step_successivo,this);
            } // end success
           
         });
        
        
    }
    


            
      </script>
