<?php
//Autore: Marco Casaburi
//Data ultima modifica: 17/09/2014
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
?>

<script type="text/javascript">

Number.prototype.formatMoney = function(places, symbol, thousand, decimal) {
	places = !isNaN(places = Math.abs(places)) ? places : 2;
	symbol = symbol !== undefined ? symbol : "€";
	thousand = thousand || ".";
	decimal = decimal || ",";
	var number = this, 
	    negative = number < 0 ? "-" : "",
	    i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
	    j = (j = i.length) > 3 ? j % 3 : 0;
	return  negative + (j ? i.substr(0, j) + thousand : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : "") + symbol;
};

var totalePasseggeri = 1;
var oldValue = 0;
var riga = 0;

$(document).ready(function() {
	initializeCheckPasseggeri();

	//se viene inserito un codice coupon controllo che sia corretto
	$('#coupon').change(function(){
		
		var formData = {
				action: 'checkCoupon',
				codiceCoupon: $('#coupon').val(),
				comunePartenzaId: $('#comunePartenzaId').val(),
				comuneArrivoId: $('#comuneArrivoId').val(),
				dataAndata: $('#dataAndata').val(),
				dataRitorno: $('#dataRitorno').val(),
				totale: $('#prezzo_totale').val()
		};
		$.ajax({
		    type: "POST",
		    url: "/gestione_utente.php",
		    // The key needs to match your method's input parameter (case-sensitive).
		    data: formData,
		    dataType: "json",
		    success: function(data){
		    	if(data.result == true){
		    		var importo;
		    		if($.type(data.importo) === "string"){
		    			importo = parseFloat(data.importo.replace(',','.'));
		    		} else {
		    			importo = parseFloat(data.importo);
		    		}

		    		var percentuale = data.percentuale;
		    		if(importo <= 0){
						importo = parseFloat($('#prezzo_totale').val())*percentuale/100;
						importo = Math.floor(importo*10)/10; 
					}
		    		var v = parseFloat(Math.round(importo * 100) / 100).toFixed(2);
		    		$('.valore_coupon').html(v+'&euro;');
		    		$('#importo_coupon').val(v);

		    		var modificaImporto = parseFloat($('#modificaImporto').val());
		    		if(typeof $('#modificaImporto').val() === "undefined") {
		    			modificaImporto = 0;
		    		}
		    		var modificaPenale = parseFloat($('#modificaPenale').val());
		    		if(typeof $('#modificaPenale').val() === "undefined") {
		    			modificaPenale = 0;
		    		}
		    		
					var residuo = parseFloat($("#prezzo_totale").val()) - v - modificaImporto + modificaPenale;
					if (residuo < 0){
						residuo = 0;
					}
		    		//formatto il totale finale
		    		$("#totale_finale").html(parseFloat(residuo).formatMoney());
		    	} else {
		    		var html = data.message;
					if(typeof data.andata !== "undefined" && data.andata != 'OK') {
						html += "<br> - Andata: "+data.andata;
					}
					if(typeof data.ritorno !== "undefined" &&  data.ritorno != 'OK' && data.ritorno != 'NO') {
						html += "<br> - Ritorno: "+data.ritorno;
					}
					html = "Ops, Coupon non valido - Sembra ci sia qualcosa che non va nelle date o nelle fermate";
		    		$('.valore_coupon').html(html);
		    		$("#totale_finale").html(parseFloat($("#prezzo_totale_modifica").val()).formatMoney());
		    	}
			},
		    failure: function(errMsg) {
		        alert(errMsg);
		    }
		});
	});
	
	//formatto tutti i prezzi unitari
	$("#biglietti span[id*='unitario']").each(function () {
		$(this).html(parseFloat($(this).html()).formatMoney());
	});
	
	//formatto tutti i prezzi totali
	$("#biglietti span[id*='totale']").each(function () {
		$(this).html(parseFloat($(this).html()).formatMoney());
	});
	
	//formatto il totale finale
	$("#totale_finale").html(parseFloat($("#totale_finale").html()).formatMoney());
	
	// calcola il prezzo totale dei singolo biglietto e aggiorna la somma totale della prenotazione
	$("#biglietti input[id*='numero']").change(function () {
		var split = $(this).attr("id").split("_");
		var id = split[1];
		
		if ($(this).val() == "") {
			$("#biglietti input[id='numero_" + id + "']").val(oldValue);
			return;
		}
		
		if (mostraPasseggeri(id)) {
			calcolaPrezzi($(this), id);
		} else {
			alert("<?=$dizionario['prenota']['diminuire_biglietti']?>");
		}
	}).focus(function(){
		// Salva il vecchio valore
        oldValue = $(this).val();
   }).keypress(function(evt) {
		var charCode=(evt.which)?evt.which:event.keyCode;
		if (charCode>31 && (charCode<48 || charCode>57))
			return false;
		if (charCode == 13) return false;
		return true;
	});
	
	function mostraPasseggeri(id) {
		var current_totale_posti = 0;
		
		$("#biglietti input[id*='numero']").each(function () {
			current_totale_posti += parseInt($(this).val());
		});
		
		if (current_totale_posti < totalePasseggeri) {
			$("#biglietti input[id='numero_" + id + "']").val(oldValue);
			return false;
		}
		
		var tipo = $("#biglietti #tipo_" + id).html();
		var current_posti = $("#biglietti input[id='numero_" + id + "']").val();
		var posti = oldValue;
		
		while (posti < current_posti) {
			posti++;
			
			riga++;
			var riga_biglietto = $(
					'<div id="riga' + riga + '" class="passeggero row">'+
					'<div class="col-md-12">'+
                    	'<h5 class="">'+tipo+'</h5>'+
					'</div>'+
                    '<input type="hidden" name="passeggeri[' + riga + '][TipoBigliettoId]" id="passeggeri[' + riga + '][TipoBigliettoId]" value="' + id + '" class="passeggeroId">'+
                    
                    '<div class="form-group col-md-4">'+
                        '<label for="btnfield_name"><?=$dizionario['Nome']?>: <span class="required">*</span></label>'+
                        '<br>'+
                        '<span class="wpcf7-form-control-wrap name">'+
							'<input name="passeggeri[' + riga + '][Nome]" id="PasseggeroNome' + riga + '" value="" size="40" class="passeggeroNome wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control" id="btnfield_name" aria-required="true" aria-invalid="false" type="text" onChange="javascript:changePasseggero(' + riga + ')">'+
						'</span>'+
					'</div>'+
                    '<div class="form-group col-md-4">'+
                        '<label for="btnfield_name"><?=$dizionario['Cognome']?>: <span class="required">*</span></label>'+
                        '<br>'+
                        '<span class="wpcf7-form-control-wrap surname">'+
							'<input name="passeggeri[' + riga + '][Cognome]" id="PasseggeroCognome' + riga + '" value="" size="40" class="passeggeroCognome wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control" id="btnfield_name" aria-required="true" aria-invalid="false" type="text" onChange="javascript:changePasseggero(' + riga + ')">'+
						'</span>'+
					'</div>'+
                    '<div class="form-group col-md-2">'+
                        '<label for="btnfield_name"><?=$dizionario['Eta']?>: <span class="required">*</span></label>'+
                        '<span class="wpcf7-form-control-wrap surname">'+
                        	'<input name="passeggeri[' + riga + '][Eta]" id="PasseggeroEta' + riga + '" value="" size="40" class="passeggeroEta wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control" id="btnfield_name" aria-required="true" aria-invalid="false" type="text">'+
                        '</span>'+
                    '</div>'+
                    '<div class="form-group col-md-2">'+
                        '<label class="conradio"><?=$dizionario['conferma']['principale']?>'+
                            '<input type="radio" checked="" name="PasseggeriPrincipale" value="' + riga + '" onChange="javascript:changePrincipale();">'+
                            '<span class="checkmark"></span>'+
						'</label>'+
                        '<a href="javascript:rimuoviBiglietto(\'' + id + '\', \'riga' + riga + '\')"><img src="images/close.png" alt="<?=$dizionario['conferma']['rimuovi']?>" title="<?=$dizionario['conferma']['rimuovi']?>"></a> <?=$dizionario['conferma']['rimuovi']?> '+
					'</div>'+
					'<div class="form-group col-md-4">'+
	                	'<label for="btnfield_name"><?=$dizionario['Sesso']?>: <span class="required">*</span></label>'+
	                    '<br>'+
	                	'<select class="passeggeroSesso select2" name="passeggeri[' + riga + '][SessoId]" id="PasseggeroSesso' + riga + '" onChange="javascript:changePasseggero(' + riga + ')">'+                    	
	                        '<option value="">- <?=$dizionario['conferma']['seleziona']?> -</option>'+
	                        '<option value="1"><?=$dizionario['conferma']['sig']?></option>'+
	                        '<option value="2"><?=$dizionario['conferma']['sigra']?></option>'+
	                    '</select>'+
	                '</div>'+
                '</div>');
			
			$("#passeggeri").append(riga_biglietto);
			$('#passeggeroSesso' + riga).select2({
			     //configuration
		    });
					
		}
		
		initializeCheckPasseggeri();
		
		totalePasseggeri = current_totale_posti;
		$("#biglietti #posti_totale").val(totalePasseggeri);
		return true;
	}
});

function initializeCheckPasseggeri() {
	$("#passeggeri .passeggeroId").each(function() {
		$(this).rules('add', {
	        required:true,
	        messages: {
	            required: "<?=$dizionario['prenota']['obbligatorio']?>"
	        }
	    });
	});
	
	$("#passeggeri .passeggeroCognome").each(function() {
		$(this).rules('add', {
	        required:true,
	        messages: {
	            required: "<?=$dizionario['prenota']['obbligatorio']?>"
	        }
	    });
	});
	
	$("#passeggeri .passeggeroNome").each(function() {
		$(this).rules('add', {
	        required:true,
	        messages: {
	            required: "<?=$dizionario['prenota']['obbligatorio']?>"
	        }
	    });
	});
	
	$("#passeggeri .passeggeroSesso").each(function() {
		$(this).rules('add', {
	        required:true,
	        messages: {
	            required: "<?=$dizionario['prenota']['obbligatorio']?>"
	        }
	    });
	});
	
	$("#passeggeri .passeggeroEta").each(function() {
		$(this).rules('add', {
	        required:true,
	        number: true,
	        min: 0,
	        messages: {
	            required: "<?=$dizionario['prenota']['obbligatorio']?>",
	            number: "<?=$dizionario['prenota']['numerico']?>",
	            min: "<?=$dizionario['prenota']['maggiore_zero']?>",
	        }
	    });
	});
	
	$("#passeggeri .passeggeroEta").change(function() {
		var tr = $(this).parent().parent().parent();
		var rigaId = tr.attr('id');
		var riga = rigaId.replace("riga","");
		
		var name = 'input[name="passeggeri[' + riga + '][TipoBigliettoId]"]';
		var idBiglietto = tr.find(name).val();
		
		var etaDa_A = vincoliEta[idBiglietto].split("_");
		var etaDa = parseInt(etaDa_A[0]);
		var etaA = parseInt(etaDa_A[1]);
		
		var etaInserita = parseInt($(this).val());
		
		if(etaInserita < etaDa || etaInserita > etaA){
			alert("<?=$dizionario['prenota']['eta_compresa']?> " + etaDa + " <?=$dizionario['prenota']['e']?> " + etaA + " <?=$dizionario['prenota']['anni']?>");
			$(this).val("");
		}
	});
	
	$('.blue_btn').click(function() {
		var principale = $('input[name="PasseggeriPrincipale"]:checked').val();
    	
    	if (principale === undefined) {
    		alert("<?=$dizionario['prenota']['seleziona_principale']?>");
        	e.preventDefault();
    	} else {
    		$('#form_conferma_itinerario').submit();
    	}
	});
}

function rimuoviBiglietto(tipoBiglietto, idRiga) { 
	if(totalePasseggeri > 1) { 
		$("#biglietti input[id='numero_" + tipoBiglietto + "']").val($("#biglietti input[id='numero_" + tipoBiglietto + "']").val() - 1);
		$('#' + idRiga).remove();
		
		// Aggiorna il totale dei passeggeri;
		totalePasseggeri -= 1;
		$("#biglietti #posti_totale").val(totalePasseggeri);
		
		// Salva il vecchio valore
	    oldValue = $("#biglietti input[id='numero_" + tipoBiglietto + "']").val();
	    
	    // Aggiorna i prezzi
	    calcolaPrezzi($("#biglietti input[id='numero_" + tipoBiglietto + "']"), tipoBiglietto);
	} else {
		alert("Impossibile rimuovere questo passeggero. Deve essere presente almeno 1 passeggero per proseguire la prenotazione");
	}
}

function calcolaPrezzi(input, id) {
	var posti = input.val();
	var prezzo_biglietto = $("#biglietti #prezzo_" + id).val();
	var old_totale_prezzo_biglietto = $("#biglietti #prezzo_totale_" + id).val();
	var old_totale_prezzo = $("#biglietti #prezzo_totale").val();
	
	var totale_prezzo_biglietto = posti * prezzo_biglietto;
	var totale_prezzo = (old_totale_prezzo - old_totale_prezzo_biglietto) + totale_prezzo_biglietto;
	
	
	$("#biglietti #prezzo_totale_" + id).val(totale_prezzo_biglietto);
	$("#biglietti #totale_" + id).html(totale_prezzo_biglietto.formatMoney());
	
	$("#biglietti #prezzo_totale").val(totale_prezzo);
	$("#biglietti #totale").html(totale_prezzo.formatMoney());

	var totale = totale_prezzo - $("#importo_coupon").val();
	if(totale<0){
		totale = 0;
	}
	$("#totale_finale").html(totale.formatMoney());
}

function changePrincipale(){
	var selectedRow = $('input[name="PasseggeriPrincipale"]:checked').val();
	var cognome = $("#PasseggeroCognome"+selectedRow).val();
	var nome = $("#PasseggeroNome"+selectedRow).val();
	var sesso = $("#PasseggeroSesso"+selectedRow).val();
	$("input[name='nome_cognome']").val(cognome+" "+nome);
	$("input[name='sesso']").val(sesso);
}

function changePasseggero(index){
	var radio = $('#riga'+index+' input[name="PasseggeriPrincipale"]');
	if(radio.attr('checked')=='checked'){
		var cognome = $("#PasseggeroCognome"+index).val();
		var nome = $("#PasseggeroNome"+index).val();
		var sesso = $("#PasseggeroSesso"+index).val();
		$("input[name='nome_cognome']").val(cognome+" "+nome);
		$("input[name='sesso']").val(sesso);
	}
}

</script>