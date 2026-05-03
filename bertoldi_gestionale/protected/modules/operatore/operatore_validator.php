<?php global $dizionario;?>
<script type="text/javascript"> 
    
    
    
    function getElencoMediatori(oggetto)
{
    $("#MediatoreSel").css("display","none");
    $("#MediatoreId").removeClass("required");
    
    if ($("#OperatoreTipoId").val()==2)
        {
    page_to_load="/mediazionesoggetti.php?do=getElencoMediatori";
            //	$("#layer_nero2").toggle();
            
    $.ajax({
    url: page_to_load,
    async: false,
  success: function(data){
    $("#MediatoreId").addClass("required");
    $("#MediatoreSel").css("display","");
    $("#MediatoreId").html(data);
  }
});
        }
}
    
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
    
    function mergeRuoli(){       
        
        var values = new Array();
        $.each($("input[name='RuoliAttivi[]']:checked"), function() {
          values.push($(this).val());
          // or you can do something to the actual checked checkboxes by working directly with  'this'
          // something like $(this).hide() (only something useful, probably) :P
        });
        if(values==""){
            alert("Selezionare almeno un ruolo");
            exit();
        }
        $.ajax({
           type: "POST",           
           url: "/protected/modules/operatore/operatore_action.php?do=merge_ruoli",
           data: "action=merge_ruoli&ruoli="+values,
           async: false,
           success: function(msg) {               
              msg=jQuery.trim(msg);              
              $('#boxContenitoreTabella').html(msg);           
            } // end success
         });
       
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
    
    
    function eliminaOperatore(dataReq){       
        result=true;
        
        $.ajax({
           type: "POST",           
           url: "/protected/modules/operatore/operatore_action.php",
           data: dataReq,
           async: false,
           success: function(msg) {               
              msg=jQuery.trim(msg);
              loadMainContent('operatore','operatore.php',this);
                avviso_operazione(messaggio);  
            } // end success
         });
         
       
    }
    
    function validate_password(){
        var a=$('#password').val();
        var b= $('#co_password').val();
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
    	CambiaStep(modulo,page,elem);
	}
     
    
    function submit_form()
    {        
        var messaggio;
        var id;
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/operatore/operatore_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {               
              msg=jQuery.trim(msg);               
              if(msg.indexOf(',')>-1)
              {
                  arr_response=msg.split(",");
                  messaggio=arr_response[0];
                  id=arr_response[1];
                  loadMainContent('operatore','operatore.php?do=step&step=2&operatoreId='+id,this);
            avviso_operazione(messaggio);  
              }
              else
                  {
                  messaggio=msg;
                  loadMainContent('operatore','operatore.php',this);
                  avviso_operazione(messaggio);  
                  }
              
            
            } // end success
         });
    }
    
$(document).ready(function() {
adatta_dialog_box();   
 
 if ($("#OperatoreTipoId").val()!=2)
    $("#MediatoreSel").css("display", "none");


    $("#application_form").validate({
        submitHandler: function(form) {
            // $(form).ajaxSubmit();            
            if(usernameExist()==true){
                if(validate_password())
                    submit_form();
                else
                    alert("<?=$dizionario['convenzione']['password_errata']?>");
            }else{
                alert("<?=$dizionario['convenzione']['username_errata']?>");
            }
        }
    });  
});

    $('#CambiaStatoOperatoreId').submit(function() {

     $.ajax({
           type: "POST",
           url: "/protected/modules/operatore/operatore_action.php",
           data: $("#CambiaStatoOperatoreId").serialize(),
           success: function(msg) {
               alert(jQuery.trim(msg));
               
            } // end success
           
         });
            
return false;

});

    $('#EliminaOperatore').submit(function() {

    alert('Operatore eliminato');
            
return false;

});
</script>
