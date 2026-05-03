
<script type="text/javascript"> 
    
    function VerificaPosti(NumeroMinimo,oggetto)
      {
        
          numero=oggetto.value;
          
          if (numero<0)
             {
                  n=numero*(-1);
                  n1=NumeroMinimo*(-1);
               
                
                if (NumeroMinimo<n) 
                    alert("E' possibile sottrarre al massimo "+NumeroMinimo+" posti!");
                    oggetto.value=n*(-1);
                 
             } 
          
          
      }
    
    function BloccaSbloccaCorsa(Stato,CorsaId,CorsaNome,DataCorsa,OraCorsa,Blocco)
    {
        stringa="Sei sicuro di voler "+Blocco+" la corsa "+CorsaNome+" del "+DataCorsa+" delle ore "+OraCorsa+"?";
      
        
         conferma = confirm(stringa);
       
        if (conferma)
       {
           page_to_load="/protected/modules/rt_corsapartenza/corsapartenza_action_storico.php?do=BloccaCorsa&Stato="+Stato+"&CorsaId="+CorsaId+"&CorsaData="+DataCorsa;
       $.get(page_to_load, function(data){
           
          
           alert("La corsa "+CorsaNome + "è stata "+Blocco+"!");
           
            avviso_operazione("ok");
               loadMainContent('rt_corsapartenza','corsapartenza_storico.php',this);
              
           
              } );
       } 
        
    }
    
    
    function BloccaSbloccaCorsaWeb(Stato,CorsaId,CorsaNome,DataCorsa,OraCorsa,Blocco)
    {
        stringa="Sei sicuro di voler "+Blocco+" la corsa "+CorsaNome+" del "+DataCorsa+" delle ore "+OraCorsa+" PER IL WEB?";
      
        
         conferma = confirm(stringa);
       
        if (conferma)
       {
           page_to_load="/protected/modules/rt_corsapartenza/corsapartenza_action_storico.php?do=BloccaCorsaWeb&Stato="+Stato+"&CorsaId="+CorsaId+"&CorsaData="+DataCorsa;
       $.get(page_to_load, function(data){
           
          
           alert("La corsa "+CorsaNome + "è stata "+Blocco+" per il web!");
           
            avviso_operazione("ok");
               loadMainContent('rt_corsapartenza','corsapartenza_storico.php',this);
              
           
              } );
       } 
        
    }

    function calendario(mese, anno)
    {
		page_to_load="/protected/modules/rt_corsapartenza/corsapartenza_action.php?do=calendario&mese="+mese+"&anno="+anno;
       	$.get(page_to_load, function(data){
        	$("#calendario").html(data);
        } );
        
        
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
    
    function submit_form_corsapartenza()
    {
        
        var messaggio;
        var id;
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_corsapartenza/corsapartenza_action_storico.php",
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
                loadMainContent('rt_corsapartenza','corsapartenza_action_storico.php',this);
                avviso_operazione(messaggio);
                }
                else
                {
                CorsaId='<?=$CorsaId?>';
                DataPartenza='<?=$DataPartenza?>';
                  //ChiudiBox();
                ExternalLoad('rt_corsapartenza','corsapartenza_storico.php?do=GestionePax&CorsaId='+CorsaId+'&DataPartenza='+DataPartenza,this);
                

                }    
                
              
              
               
              
               
            } // end success
           
         });
        
        
    }
    
   

    
   $(document).ready(function() {
    adatta_dialog_box();   
    
	      $("#application_form").validate({
                submitHandler: function(form) {
               // $(form).ajaxSubmit();
               submit_form_corsapartenza();
           }
     });
        
              
              
          
});       
            
      </script>
