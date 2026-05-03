
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
    
    function submit_form_timetable()
    {
    	var corsa_id=$("#CorsaId").val();
        var messaggio;
        var id;
        var step_successivo=$("#step_successivo").val();
        $("#layer_nero2").show();
        $("#brain_oscura_pre").show();
        window.scrollTo(0,0);
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_timetable/timetable_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
               $("#layer_nero2").hide();
               $("#brain_oscura_pre").hide();
               
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
                      loadMainContent('rt_timetable','timetable.php',this);
                  //else
                   //loadMainContent1('rt_timetable','timetable.php?do=add&step='+step_successivo+'&CorsaId='+corsa_id,this);
            } // end success
           
         });
        
        
    }

    function submit_form_timetable1()
    {
        
        var messaggio;
        var id;
        var step_successivo=$("#step_successivo").val();
        var corsa_id=$("#CorsaId").val();
        
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_timetable/timetable_action.php",
           data: $("#application_form1").serialize(),
           success: function(msg) {
               
              msg=jQuery.trim(msg);
               
              if(msg.indexOf(',')>-1) {
                  arr_response=msg.split(",");
                  messaggio=arr_response[0];
                  id=arr_response[1];
              }
              else
                  messaggio=msg;

                 avviso_operazione("ok");
                 if (step_successivo=='0')
                      loadMainContent('rt_timetable','timetable.php',this);
                 else
                      loadMainContent1('rt_timetable','timetable.php?do=add&step='+step_successivo+'&corsaId='+corsa_id,this);
                ChiudiBox();  
            } // end success
           
         });
        
        
    }
    
   

    
   $(document).ready(function() {
    adatta_dialog_box();   
    
	$("#application_form").validate({
                submitHandler: function(form) {
               // $(form).ajaxSubmit();
               $('#CorsaIdSelect').removeAttr("disabled");
               submit_form_timetable();
           }
     });

	$("#application_form1").validate({
        submitHandler: function(form) {
             // $(form).ajaxSubmit();
             $('#CorsaIdSelect').removeAttr("disabled");
             submit_form_timetable1();
             
         }
    });
              
    // this starts tracking changes in the form
	if ($('#application_form').length > 0) {	
		var oldvals1 = $('#application_form').trackChanges({events: "change blur keypress keydown click", changeListVisible: false});
	}
	
	
    $('#CorsaIdSelect').parent().hide();
	$("input[name='tipoCreazione']").change(function() {
		if($(this).val() == '1'){
			$('#CorsaIdSelect').parent().hide();
		} else if($(this).val() == '2'){
			$('#CorsaIdSelect').parent().show();
			$('#CorsaIdSelect option').attr('selected', 'selected');
			$('#CorsaIdSelect').attr("disabled", true);
		} else {
			$('#CorsaIdSelect').parent().show();
			$('#CorsaIdSelect option').removeAttr('selected');
			$('#CorsaIdSelect').removeAttr("disabled");
		}
	});                       
  
});       

    //Calcolo nuovo orario
    //Time to string
    function t2s(stringa) {
        pippo = stringa.split(":");
        return parseInt(pippo[0],10)*3600+parseInt(pippo[1],10)*60+parseInt(pippo[2],10);
    }
    //String to time
    function s2t(numero) {
        ss = numero % 60;
        nn = Math.floor(numero/60);
        mm = nn % 60;
        oo = Math.floor(nn/60);
        return String(1000+oo).substr(2)+":"+String(1000+mm).substr(2)+":"+String(1000+ss).substr(2);
    }

    //somma i minuti dell'offset e restituisce il nuovo orario e il giorno di offset
    function sommaMinuti(oreminuti,giorno,offset){
        var str = oreminuti;
        var res = str.split(":"); 
        var ore = res[0];
        var minuti = res[1];
        var offset = offset;
        var giorno = giorno;
        var segno = "positivo";
        var giorno_prima;
        
        if(offset<0){
            segno='negativo';
        }
                        
        var ore_offset = Math.floor( Math.abs(offset) / 60);          
        var minuti_offset = Math.abs(offset) % 60;
        
        ora_attuale = t2s(ore+':'+minuti+':00');
        ora_offset = t2s(ore_offset+':'+minuti_offset+':00');
        if(segno == 'positivo'){
            somma_ore = s2t(ora_attuale + ora_offset);
        }
        else {
            giorno_prima = 0;
            subsum = ora_attuale - ora_offset;
            if(ora_attuale < ora_offset){
                subsum = subsum+(24*3600);
                giorno_prima = 1;
            }
            somma_ore = s2t(subsum);
        }
        //console.log(ora_attuale+' '+ora_offset+' '+somma_ore);
        //console.log(s2t(2000));
        
        //total_ore = parseInt(ore) + parseInt(ore_offset);
        //total_minuti = parseInt(minuti) + parseInt(minuti_offset);
        
        //console.log(total_ore+' '+total_minuti);
        tm = somma_ore.split(':');
        total_ore = tm[0];
        total_minuti = tm[1];
                
        giorno = parseInt(giorno)+Math.floor(total_ore/24);
        
        if(giorno_prima==1){
            giorno = giorno-1;
        }
        
        total_ore = total_ore%24;
        
        if(total_ore<10){
            total_ore = '0'+total_ore;
        }
       /* if(total_minuti<10){
            total_minuti = '0'+total_minuti;
        }*/
        //console.log(total_ore+':'+total_minuti+':00!'+giorno);
        return total_ore+':'+total_minuti+'!'+giorno;        
    }
    
    //scrive i valori nel corrispondente input
    function scriviValori(orario_in,giorno_in,valore,n){
        //console.log('valori '+orario_in+' '+giorno_in+' '+valore+' '+n)
        $('.ora_'+valore+'_'+n).val(orario_in);
        $('.giorno_'+valore+'_'+n).val(giorno_in);

    }
    
    //normalizza il giorno rispetto al primo della tratta
    function normalizza_giorno(colonna,n){
        fattore = 0;
        for(i=0;i<n;i++){
            if($('.giorno_'+colonna+'_'+i).hasClass('first_1')){
                gio = $('.giorno_'+colonna+'_'+i).val();
                if(gio == -1){
                    fattore = 1;
                }
                else if(gio == 1){
                    fattore = -1;
                }
                else {
                    fattore = 0;
                }
            }
            valore_vecchio = $('.giorno_'+colonna+'_'+i).val();
            valore_nuovo = parseInt(valore_vecchio) + parseInt(fattore);
            $('.giorno_'+colonna+'_'+i).val(valore_nuovo);
            //$('.giorno_'+valore+'_'+n).val(giorno_in);
        }
    }
    
    //Aggiorna i valori degli input
    function aggiornaValori(valore,offset) {
        //console.log(valore+'-'+offset);
        var ora_arr=[], giorno_arr=[];
        $('.ora_'+valore).each( function(){                   
           ora_arr.push( $(this).val() );
        });
        $('.giorno_'+valore).each( function(){                   
           giorno_arr.push( $(this).val() );
        });
        for(i=0;i<ora_arr.length;i++){
            //oragiorno = '';
            if(ora_arr[i]!=''){
                oragiorno = sommaMinuti(ora_arr[i],giorno_arr[i],offset); 
                str = '';
                str = oragiorno.split('!');    

                scriviValori(str[0],str[1],valore,i);   
            }
        }
        normalizza_giorno(valore,i);
        $('#ora_offset_valore_'+valore).val('0');
        $('#minuti_offset_valore_'+valore).val('0');
    }
    function aggiungiValoreOrario(segno,ora,minuti,colonna){
        var offset = (parseInt(ora)*60)+parseInt(minuti);
        if(segno=='-'){
            offset = parseInt(offset)*(-1)
        }
        //console.log(offset);
        aggiornaValori(colonna,offset);
    }
            
            
      </script>
