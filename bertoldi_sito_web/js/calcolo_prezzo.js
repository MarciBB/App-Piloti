// JavaScript Document

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
				codiceCoupon: $('#coupon').val()
		};
		$.ajax({
		    type: "POST",
		    url: "/gestione_utente.php",
		    // The key needs to match your method's input parameter (case-sensitive).
		    data: formData,
		    dataType: "json",
		    success: function(data){
		    	if(data.result == true){
		    		$('.status_coupon').html('Coupon Valido');
		    		var v = parseFloat(Math.round(data.importo * 100) / 100).toFixed(2);
		    		$('.valore_coupon').html(v+'&euro;');
		    		
		    		var residuo = parseFloat($("#prezzo_totale").val()) - v;
					if (residuo < 0){
						residuo = 0;
					}
		    		//formatto il totale finale
		    		$("#totale_finale").html(parseFloat(residuo).formatMoney());
		    		
		    	} else {
		    		$('.status_coupon').html('Coupon non valido');
		    		$('.valore_coupon').html('-');
		    		$("#totale_finale").html(parseFloat($('#prezzo_totale').val()).formatMoney());
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
			alert("Per diminuire il numero di biglietti rimuovere i passeggeri");
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
			var riga_biglietto = $('<tr class="passeggero" id="riga' + riga + '">'+
					'<td>'+
						'<span class="passeggeroBiglietto">' + tipo + '</span>'+
						'<input type="hidden" name="passeggeri[' + riga + '][TipoBigliettoId]" id="passeggeri[' + riga + '][TipoBigliettoId]" value="' + id + '" class="passeggeroId">'+
					'</td>'+
				    '<td>'+
				     	'<input type="text" value="" id="PasseggeroCognome' + riga + '" name="passeggeri[' + riga + '][Cognome]" class="passeggeroCognome" onChange="javascript:changePasseggero(' + riga + ')">'+
				    '</td>'+
				    '<td>'+
				    	'<input type="text" value="" id="PasseggeroNome' + riga + '" name="passeggeri[' + riga + '][Nome]" class="passeggeroNome" onChange="javascript:changePasseggero(' + riga + ')">'+
				    
				    '</td><td>'+
				   	 	'<select id="PasseggeroSesso' + riga + '" name="passeggeri[' + riga + '][SessoId]" class="passeggeroSesso" onChange="javascript:changePasseggero(' + riga + ')">'+
				           	'<option value="">- seleziona -</option>'+
				            '<option value="1">Sig.</option>'+
				            '<option value="2">Sig.ra / Sig.na</option>'+
				        '</select>'+
				    '</td>'+
				    '<td>'+
				    	'<input type="text" value="" id="PasseggeroEta' + riga + '" name="passeggeri[' + riga + '][Eta]" class="passeggeroEta">'+
				    '</td>'+
				    '<td>'+
						'<a href="javascript:rimuoviBiglietto(\'' + id + '\', \'riga' + riga + '\')"><img src="/images/close.png" alt="Rimuovi" title="Rimuovi" ></a>'+
				    '</td>'+
				    '<td style="text-align: center;">'+
				    	'<input class="passeggeroPrincipale" type="radio" name="PasseggeriPrincipale" style="float: none;" value="' + riga + '" onChange="javascript:changePrincipale();"/>'+
				    '</td>'+
				'</tr>');
			
			$("#passeggeri").append(riga_biglietto);
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
	            required: "Questo campo è obbligatorio"
	        }
	    });
	});
	
	$("#passeggeri .passeggeroCognome").each(function() {
		$(this).rules('add', {
	        required:true,
	        messages: {
	            required: "Questo campo è obbligatorio"
	        }
	    });
	});
	
	$("#passeggeri .passeggeroNome").each(function() {
		$(this).rules('add', {
	        required:true,
	        messages: {
	            required: "Questo campo è obbligatorio"
	        }
	    });
	});
	
	$("#passeggeri .passeggeroSesso").each(function() {
		$(this).rules('add', {
	        required:true,
	        messages: {
	            required: "Questo campo è obbligatorio"
	        }
	    });
	});
	
	$("#passeggeri .passeggeroEta").each(function() {
		$(this).rules('add', {
	        required:true,
	        number: true,
	        min: 0,
	        messages: {
	            required: "Questo campo è obbligatorio",
	            number: "Questo campo deve essere di tipo numerico",
	            min: "Et&agrave; deve essere maggiore o pari a zero",
	        }
	    });
	});
	
	$("#passeggeri .passeggeroEta").change(function() {
		var tr = $(this).parent().parent();
		var rigaId = tr.attr('id');
		var riga = rigaId.replace("riga","");
		
		var name = 'input[name="passeggeri[' + riga + '][TipoBigliettoId]"]';
		var idBiglietto = tr.find(name).val();
		
		var etaDa_A = vincoliEta[idBiglietto].split("_");
		var etaDa = parseInt(etaDa_A[0]);
		var etaA = parseInt(etaDa_A[1]);
		
		var etaInserita = parseInt($(this).val());
		
		if(etaInserita < etaDa || etaInserita > etaA){
			alert("L'et\u00E1 deve essere compresa tra " + etaDa + " e " + etaA + " anni");
			$(this).val("");
		}
	});
	
	$('.blue_btn').click(function() {
		var principale = $('input.passeggeroPrincipale:checked').val();
    	
    	if (principale === undefined) {
    		alert("Selezionare un passeggero principale.");
        	e.preventDefault();
    	} else {
    		$('#form_conferma_itinerario').submit();
    	}
	});
}

function rimuoviBiglietto(tipoBiglietto, idRiga) { 
	$("#biglietti input[id='numero_" + tipoBiglietto + "']").val($("#biglietti input[id='numero_" + tipoBiglietto + "']").val() - 1);
	$('#' + idRiga).remove();
	
	// Aggiorna il totale dei passeggeri;
	totalePasseggeri -= 1;
	$("#biglietti #posti_totale").val(totalePasseggeri);
	
	// Salva il vecchio valore
    oldValue = $("#biglietti input[id='numero_" + tipoBiglietto + "']").val();
    
    // Aggiorna i prezzi
    calcolaPrezzi($("#biglietti input[id='numero_" + tipoBiglietto + "']"), tipoBiglietto);
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
	var selectedRow = $('input.passeggeroPrincipale:checked').val();
	var cognome = $("#PasseggeroCognome"+selectedRow).val();
	var nome = $("#PasseggeroNome"+selectedRow).val();
	var sesso = $("#PasseggeroSesso"+selectedRow).val();
	$("input[name='nome_cognome']").val(cognome+" "+nome);
	$("input[name='sesso']").val(sesso);
}

function changePasseggero(index){
	var radio = $('#riga'+index+' input.passeggeroPrincipale');
	if(radio.attr('checked')=='checked'){
		var cognome = $("#PasseggeroCognome"+index).val();
		var nome = $("#PasseggeroNome"+index).val();
		var sesso = $("#PasseggeroSesso"+index).val();
		$("input[name='nome_cognome']").val(cognome+" "+nome);
		$("input[name='sesso']").val(sesso);
	}
}