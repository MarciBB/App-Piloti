function regioni_nazione(oggetto,FieldRegionId,FieldComuneId){
    
    
    
        
      //  document.getElementById(FieldRegionId).setAttribute("class", "brain_campoForm required");
    

	inserireIndicazioneGeograficaRegione(oggetto,FieldRegionId);
	inserireIndicazioneGeograficaComune(oggetto,FieldComuneId);
}
function regioni_citta(oggetto,FieldComuneId){
 
  // document.getElementById(FieldComuneId).setAttribute("class", "brain_campoForm required");

	inserireIndicazioneGeograficaComune(oggetto,FieldComuneId);
 
 
}

function inserireIndicazioneGeograficaRegione(oggetto,FieldRegionId){
$.ajax({
   type: "POST",
   url: "/protected/modules/regione/GetRegioneByIdNazione.php",
   data: "id_nazione="+oggetto.value,
   success: function(msg){
 $("#"+FieldRegionId).html(msg);
  
   //return msg;	
   }
 });
//document.getElementById("div_regione").setAttribute("class", "form mandatory campo-form");
//document.getElementById("td_regione").style.display=''; 
//document.getElementById("td_regione1").style.display=''; 
}


function nascondiIndicazioneGeograficaRegione(FieldComuneId){

}


function nascondiIndicazioneGeograficaComune(FieldComuneId){

$("#"+FieldComuneId).html("");


}

function inserireIndicazioneGeograficaComune(oggetto,FieldComuneId){
$.ajax({
   type: "POST",
  url: "/protected/modules/comune/GetComuneByIdRegione.php",
   data: "id_regione="+oggetto.value,
   success: function(msg){
 $("#"+FieldComuneId).html(msg);
  
   //return msg;	
   }
 });
//document.getElementById("div_regione").setAttribute("class", "form mandatory campo-form");
//document.getElementById("td_regione").style.display=''; 
//document.getElementById("td_regione1").style.display=''; 
}


