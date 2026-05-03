
<script type="text/javascript"> 
    
    
    
   
    
    function CalcolaPrezzoTipoBiglietto()
    {
          
      NTipologieBigletti=$("#NumeroBiglietti").val();
      
      
      
      i=0;
      PrezzoParziale=0;
      PrezzoTotale=0;
      QntTotale=0;
      while(i<=NTipologieBigletti)
      {
          Qnt=parseInt($("#Pax"+i).val());
          
              PP1=$("#Prezzo"+i).val();
                var PrezzoPax = parseFloat(PP1).toFixed(2);
            
            R1=$("#PrezzoRiduzione"+i).val();
            R1.replace(",", ".");
            Riduzione = parseFloat(R1).toFixed(2);

            A1=$("#PrezzoAumento"+i).val();
            A1.replace(",", ".");
           Aumento = parseFloat(A1).toFixed(2);
            PB=(Qnt*PrezzoPax);
           
           PrezzoBase = parseFloat(PB).toFixed(2);
           Pb1 = parseFloat(PB).toFixed(2);
           Pb1=Pb1.replace(".", ",");
         
           PrezzoParziale=PrezzoBase-(Riduzione*1)+(Aumento*1);
            PrezzPar = parseFloat(PrezzoParziale).toFixed(2);
            PrezzPar=PrezzPar.replace(".", ",");
            
            if (Qnt<=0)
                {
                        Pb1=0;
                     PrezzoParziale=0;
                     PrezzPar=0;
                }
               
            QntTotale=Qnt+QntTotale;
            
            PrezzoTotale=PrezzoTotale+PrezzoParziale;
             PrezzoTot = parseFloat(PrezzoTotale).toFixed(2);
             
             
            PrezzoTot=PrezzoTot.replace(".", ",");
           
            $("#PrezzoParziale"+i).html(Pb1);
            $("#PrezzoFinale"+i).html(PrezzPar);
           i++;
      }
      $("#PrezzoTotalePax").html("<strong>"+PrezzoTot+" &euro;</strong>");
      $("#TotalePax").val(QntTotale);
     

      
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
        


 function QuadraPullmanCorrente(oggetto,CorsaId,DataCorsa,BusId,NumeroBus)
    {
        stringa="La conferma genererà l'emissione dei titoli di viaggio fiscali per i viaggiatori presenti sull'autobus corrente sprovvisti durante il viaggio. Continuare?";
      
        
         conferma = confirm(stringa);
       
        if (conferma)
       {
           page_to_load="/protected/modules/rt_postviaggio/postviaggio_action.php?do=QuadraPullmanCorrente&CorsaId="+CorsaId+"&CorsaData="+DataCorsa+"&BusId="+BusId+"&BusNumero="+NumeroBus;
       $.get(page_to_load, function(data){
               alert("I titoli di viaggio sono stati emessi correttamente.");
               avviso_operazione("ok");
            
               loadMediazioneStep("rt_postviaggio","postviaggio.php?step=2&do=add&DataPartenza="+DataCorsa+"&CorsaId="+CorsaId,null);
            
            
            $("#"+oggetto).hide();
           
              } );
       } 
        
    }
    
    
 function ConsolidaCorsa(CorsaId,DataCorsa,DataCorsaF)
    {
        stringa="Consolidare la corsa del "+DataCorsaF+"?";
      
        
         conferma = confirm(stringa);
       
        if (conferma)
       {
           page_to_load="/protected/modules/rt_previaggio/previaggio_action.php?do=ConsolidaCorsa&CorsaId="+CorsaId+"&CorsaData="+DataCorsa;
       $.get(page_to_load, function(data){
               alert("La corsa e' stata consolidata correttamente");
               avviso_operazione("ok");
               loadMediazioneStep("rt_previaggio","previaggio.php?step=4&do=add&DataPartenza="+DataCorsa+"&CorsaId="+CorsaId,null);
              
           
              } );
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
    
    function submit_form_previaggio()
    {
        
        var messaggio;
        var id;
        var step_successivo=$("#step_successivo").val();
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_previaggio/previaggio_action.php",
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
                      loadMainContent('rt_previaggio','previaggio.php?do=edit&Idprovvigione=0',this);
                  else
                   loadMainContent1('rt_previaggio','previaggio.php?do=add&step='+step_successivo,this);
              
                
              
              
               
              
               
            } // end success
           
         });
        
        
    }
    
   

    
   $(document).ready(function() {
    adatta_dialog_box();   
    
	      $("#application_form").validate({
                submitHandler: function(form) {
               // $(form).ajaxSubmit();
               submit_form_previaggio();
           }
     });
        
              
              
           // this starts tracking changes in the form
                
               // var oldvals1 = $('#application_form').trackChanges({events: "change blur keypress keydown click", changeListVisible: false});
  
});       
            
      </script>
