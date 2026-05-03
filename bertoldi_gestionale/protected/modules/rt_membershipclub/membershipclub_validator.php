
<script type="text/javascript"> 
    
  var divtoupdate;  
    var newId=0;
    
    function checkEmail(submit){
 	   
 	   if($('#Email').val() != $('#email_modifica').val()){
 			var dataForm =  {
 				    action: 'checkEmail',
 				    email: $('#Email').val(),
 				    operation: $('#action').val(),
 				    id: $('#MemberId').val()
 				};
 	        $.ajax({
 	           type: "POST",
 	           url: "/protected/modules/rt_membershipclub/membershipclub_action.php",
 	           data: dataForm,
 	           dataType: 'json',
 	           success: function(data) {
                     if (data.result == false){
                         alert("Email gia' presente, utilizzare un'altra email");
                     } else {
                         
                    	 	$('#result_email').html('');
                    	 	if(submit == 1){
 	                   	 	var step_corrente=$("#step_corrente").val();
 	                        submit_form_membershipclub();
                        	}
                     }
 	            } // end success
 	           
 	         });
 		} else {
 			if(submit == 1){
            	 	var step_corrente=$("#step_corrente").val();
                 submit_form_membershipclub();
            	}		
 		}
 	}
    
   $(document).ready(function() {
	   $('#Email').change(function(){
	    	checkEmail(0); 
		});
         if (newId==0) 
          {   
               n=$("#numero_soggetti").attr("numero_soggetti");
               newId=parseInt(n)+1;
               
          }    
             adatta_dialog_box();   
    
	      $("#application_form").validate({
               //   alert("prova");
                submitHandler: function(form) {
                    
                	checkEmail(1);
//                 var step_corrente=$("#step_corrente").val();
//                 submit_form_membershipclub();
         
                
                
             
               
           }
     });
     
     
   
     
         
         
         
                // this starts tracking changes in the form
                
                var oldvals1 = $('#application_form').trackChanges({events: "change blur keypress keydown click", changeListVisible: false});
  
});       

    
    function ControllaCF(cf)
{
	var validi, i, s, set1, set2, setpari, setdisp;
	if( cf == '' )  return '';
	cf = cf.toUpperCase();
	if( cf.length != 16 )
		return "La lunghezza del codice fiscale non è\n"
		+"corretta: il codice fiscale dovrebbe essere lungo\n"
		+"esattamente 16 caratteri.\n";
	validi = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	for( i = 0; i < 16; i++ ){
		if( validi.indexOf( cf.charAt(i) ) == -1 )
			return "Il codice fiscale contiene un carattere non valido `" +
				cf.charAt(i) +
				"'.\nI caratteri validi sono le lettere e le cifre.\n";
	}
	set1 = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	set2 = "ABCDEFGHIJABCDEFGHIJKLMNOPQRSTUVWXYZ";
	setpari = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	setdisp = "BAKPLCQDREVOSFTGUHMINJWZYX";
	s = 0;
	for( i = 1; i <= 13; i += 2 )
		s += setpari.indexOf( set2.charAt( set1.indexOf( cf.charAt(i) )));
	for( i = 0; i <= 14; i += 2 )
		s += setdisp.indexOf( set2.charAt( set1.indexOf( cf.charAt(i) )));
	if( s%26 != cf.charCodeAt(15)-'A'.charCodeAt(0) )
		return "Il codice fiscale non è corretto:\n"+
			"il codice di controllo non corrisponde.\n";
	return "Il codice fiscale è corretto.";
}

function ControllaPIVA(pi)
{
	if( pi == '' )  return '';
	if( pi.length != 11 )
		return "La lunghezza della partita IVA non è\n" +
			"corretta: la partita IVA dovrebbe essere lunga\n" +
			"esattamente 11 caratteri.\n";
	validi = "0123456789";
	for( i = 0; i < 11; i++ ){
		if( validi.indexOf( pi.charAt(i) ) == -1 )
			return "La partita IVA contiene un carattere non valido `" +
				pi.charAt(i) + "'.\nI caratteri validi sono le cifre.\n";
	}
	s = 0;
	for( i = 0; i <= 9; i += 2 )
		s += pi.charCodeAt(i) - '0'.charCodeAt(0);
	for( i = 1; i <= 9; i += 2 ){
		c = 2*( pi.charCodeAt(i) - '0'.charCodeAt(0) );
		if( c > 9 )  c = c - 9;
		s += c;
	}
	if( ( 10 - s%10 )%10 != pi.charCodeAt(10) - '0'.charCodeAt(0) )
		return "La partita IVA non è valida:\n" +
			"il codice di controllo non corrisponde.\n";
	return 'La partita IVA è valida.';
}

function check_presenza_anagrafica(SoggettoId,SoggettoTipo,AnagraficaId,AnagraficaTipo)
{
    result=false;
    page_to_load="/mediazionesoggetti.php?do=check_presenza_anagrafica&SoggettoId="+SoggettoId+"&SoggettoTipo="+SoggettoTipo+"&AnagraficaId="+AnagraficaId+"&AnagraficaTipo="+AnagraficaTipo;
            //	$("#layer_nero2").toggle();
            
    $.ajax({
    url: page_to_load,
    async: false,
  success: function(data){
    
      if (jQuery.trim(data)!="ok")
                    {    
                        alert(jQuery.trim(data));
                        result= false;
                    }
                    else
                        result= true;

    
    
  }
 
});        
            
   
  return result; 
    
}



function AddAnagraficaParteIntoMediazione(ElementId)
{
    
    
    
    soggettocorrente=$("#soggetto_corrente").val();
    soggettotipo=$("#MediazioneSoggettoParteTipoId").val();
    
    if (check_presenza_anagrafica(soggettocorrente,soggettotipo,ElementId,0)==true)
        {
    
     ChiudiBox();
    
    $("#soggetto_"+soggettocorrente+" #anagrafica").html("");
    
    
     page_to_load="/mediazionesoggetti.php?do=add_anagrafica_soggetto&SoggettoId="+soggettocorrente+"&AnagraficaId="+ElementId+"&SoggettoTipo="+soggettotipo;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
                    $("#soggetto_"+soggettocorrente+" #anagrafica").html(data);
                   

              } );
              
        }       
 
}



function AddAnagraficaEstIntoMediazione(ElementId)
{
    

    
    
    soggettocorrente=$("#soggetto_corrente").val();
    anagraficaest_tipo=$("#anagraficaest_corrente").val();
    
    
    
    
     divanagraficaesterna="ElencoAssistenti";
    if (anagraficaest_tipo=="R")
       divanagraficaesterna="ElencoRappresentanti";
   
   
    
    soggettotipo=$("#MediazioneSoggettoParteTipoId").val();
    
    
     if (check_presenza_anagrafica(soggettocorrente,soggettotipo,ElementId,anagraficaest_tipo)==true)
        {
            
     $("#brain_listaSelezione").html("");      
        
     ChiudiBox();
   
    $("#soggetto_"+soggettocorrente+" #"+divanagraficaesterna).html("data");
    
   
    
     page_to_load="/mediazionesoggetti.php?do=add_anagraficaest_soggetto&SoggettoId="+soggettocorrente+"&AnagraficaId="+ElementId+"&SoggettoTipo="+soggettotipo+"&AnagraficaEstTipo="+anagraficaest_tipo;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
               
                    $("#soggetto_"+soggettocorrente+" #"+divanagraficaesterna).html(data);
                   

              } );
              
        }     
 
}




function EliminaDisponibilita(MediatoreDisponibilitaId)
{
    
   // soggettotipo=$("#MediazioneSoggettoParteTipoId").val();
   page_to_load="/protected/modules/mediatore/mediatore_action.php?do=elimina_disponibilita&MediatoreDisponibilitaId="+MediatoreDisponibilitaId;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
                      alert("Disponibilita eliminata con successo.");
                      loadMediazioneStep('mediatore','mediatore.php?do=add&step=4',this);

              } );
    
    
}


function SettaPreferenzaComunicazione(SoggettoTipo,SoggettoId,valore)
{

 
   page_to_load="/mediazionesoggetti.php?do=SettaPreferenzaComunicazione&SoggettoTipo="+SoggettoTipo+"&SoggettoId="+SoggettoId+"&preferenza="+valore;
       $.get(page_to_load, function(data){
              } );
    
    
}

function EliminaAnagraficaEstSoggetto(SoggettoId,SoggettoTipo,AnagraficaEstId,AnagraficaEstTipo)
{
    
     anagraficaest_tipo=AnagraficaEstTipo;
    
     divanagraficaesterna="ElencoAssistenti";
    if (anagraficaest_tipo=="R")
       divanagraficaesterna="ElencoRappresentanti";
   
   
   soggettotipo=$("#MediazioneSoggettoParteTipoId").val();
   page_to_load="/mediazionesoggetti.php?do=elimina_anagraficaest_soggetto&SoggettoId="+SoggettoId+"&AnagraficaId="+AnagraficaEstId+"&SoggettoTipo="+SoggettoTipo+"&AnagraficaEstTipo="+AnagraficaEstTipo;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
                      $("#soggetto_"+SoggettoId+" #"+divanagraficaesterna).html(data);
                   

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
    
    function submit_form_membershipclub()
    {
       
         
        /*$("#brain_oscura").css("display","none");*/
        var step_successivo=$("#step_successivo").val();
        var step_corrente=$("#step_corrente").val();
        
        /*if ($("#application_form").attr("class")=="nogo")
            step_successivo=step_successivo-1;*/
        
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_membershipclub/membershipclub_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
                     avviso_operazione("ok");
                     if (step_successivo=='0')
                         loadMainContent('rt_membershipclub','membershipclub.php',this);
                     else
                     loadMainContent('rt_membershipclub','membershipclub.php?do=add&step='+step_successivo,this);
            } // end success
           
         });
        
        
    }
    







            
      </script>
