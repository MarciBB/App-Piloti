<script type="text/javascript"> 
   
    function eliminaRuolo(dataReq){       
        result=true;
        
        $.ajax({
           type: "POST",           
           url: "/protected/modules/ruolo/ruolo_action.php",
           data: dataReq,
           async: false,
           success: function(msg) {               
              msg=jQuery.trim(msg);
              loadMainContent('ruolo','ruolo.php',this);
                avviso_operazione(messaggio);  
            } // end success
         });
         
       
    }
    
    
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
           url: "/protected/modules/ruolo/ruolo_action.php",
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
              
            if($('#action').val()=="create")
                loadMainContent('ruolo','ruolo.php?do=step&step=2&ruoloId='+id,this);
                else
                loadMainContent('ruolo','ruolo.php',this);        
            avviso_operazione(messaggio);  
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

$('#CambiaStatoRuoloId').submit(function() {

     $.ajax({
           type: "POST",
           url: "/protected/modules/ruolo/ruolo_action.php",
           data: $("#CambiaStatoRuoloId").serialize(),
           success: function(msg) {
               alert(jQuery.trim(msg));
               
            } // end success
           
         });
            
return false;

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
                       var flag=confirm("I dati inseriti nello step corrente non sono stati ancora salvati. Cambiando pagina le modifiche non saranno memorizzate. Continuare?","Si","No");
                        if (flag)
                         CambiaStep(modulo,page,elem);
                        else
                            return false;
                    }
     }
</script>
