<?php global $dizionario;?>
<script type="text/javascript"> 
    

  
    
    function set_password(){
        $('.cambia_pwd_1').slideUp();
        $('.cambia_pwd_2').slideDown(); 
        $('#password').attr("class", "required");
        $('#password').attr("name", "Operatore[Password]");
        $('#co_password').attr("class", "required");        
    }
    
    function unset_password(){
        $('.cambia_pwd_1').slideDown();
        $('.cambia_pwd_2').slideUp();
        $('#password').attr("class", "");
        $('#password').attr("name", "pass");
        $('#co_password').attr("class", "");        
    }
    
   
    function usernameExist(){
        var new_user=$('#Username').val();
        var ope_Id=$('#id').val();
        result=true;
                        
        $.ajax({
           type: "POST",           
           url: "/protected/modules/operatore/operatore_action.php?new_user="+new_user,
           data: "action=usernameExist&ope_id="+ope_Id,
           async: false,
           success: function(msg) {               
              msg=jQuery.trim(msg);              
              if(msg.indexOf('trov')>-1)
                   result= false;
              else
                   result=true;            
            } // end success
         });        
        return result; 
    }
    
    
    
    
    function validate_password(){
        var a=$('#password').val();
        var b= $('#copassword').val();
        if(a==b)
            return true;
        else
            return false;       
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
     
    
     function submit_form_mediatore()
    {
       
         
        /*$("#brain_oscura").css("display","none");*/
        var step_successivo=$("#step_successivo").val();
        var step_corrente=$("#step_corrente").val();
        
        /*if ($("#application_form").attr("class")=="nogo")
            step_successivo=step_successivo-1;*/
         if(usernameExist()==true){
                if(validate_password())
                    {
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/mediatore/mediatore_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
                     avviso_operazione("ok");
                     loadMainContent('mediatore','mediatore.php?do=add&step='+step_successivo,this);
            } // end success
           
         });
                    }
          else
                    alert("<?=$dizionario['convenzione']['password_errata']?>");
            }else{
                alert("<?=$dizionario['convenzione']['username_errata']?>");
            }
    }
    
$(document).ready(function() {
adatta_dialog_box();   
 



    $("#application_form").validate({
        submitHandler: function(form) {
            // $(form).ajaxSubmit();            
            if(usernameExist()==true){
                if(validate_password())
                    submit_form_mediatore();
                else
                    alert("<?=$dizionario['convenzione']['password_errata']?>");
            }else{
                alert("<?=$dizionario['convenzione']['username_errata']?>");
            }
        }
    });  
});

    $('#CambiaStatoMediazioneId').submit(function() {

     $.ajax({
           type: "POST",
           url: "/protected/modules/mediatore/mediatore_action.php",
           data: $("#CambiaStatoMediazioneId").serialize(),
           success: function(msg) {
               
              
               alert(jQuery.trim(msg));
               
            } // end success
           
         });
            
return false;

});

</script>
