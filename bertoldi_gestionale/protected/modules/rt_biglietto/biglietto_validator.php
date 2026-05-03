<?php global $dizionario;?>
<script type="text/javascript">
var posti_selezionati_a = 0;
var posti_selezionati_r = 0;
var totalePasseggeri = 0;

function cancellaMovimento(movimentoId, prenotazioneId){
	var page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=CancellaMovimento&PrenotazioneId="+prenotazioneId+"&PrenotazioneMovimentoId="+movimentoId;
	$.ajax({
        type: "POST",
        url: page_to_load,
        success: function(data) {
        	alert("<?=$dizionario['biglietto']['movimento_annullata']?>");
        	loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+prenotazioneId+'&CorsaId='+corsaId+'&step=2', this);
        }
    });
	
}

function selezionaPasseggeri() {
	// Recupera il valore del parametro di input
	var parametroValore = $('#TipoTourPasseggeri').val();
	
	$('.rimuoviBiglietti').each(function() {
		var hrefFunction = $(this).attr('href');
        eval(hrefFunction); // Esegue la funzione presente nell'href
    });
	$('#TipoTourPasseggeri').val(parametroValore);

    // Recupera tutte le righe della tabella
    $('#pagamentiTabella tbody tr.rowBianca').each(function() {
        // Trova l'input con name che corrisponde al pattern desiderato
        var input = $(this).find('input[name^="BigliettoTipologiaPax["][name$="passeggeri]"]');
		// Verifica se l'input è stato trovato
        if (input.length > 0) {
			// Estrae i valori y e z dal name dell'input
			var match = input.attr('name').match(/\[(\d+)_Da (\d+) a (\d+) passeggeri\]/);
			if (match) {
				var x = parseInt(match[1]);
				var y = parseInt(match[2]);
				var z = parseInt(match[3]);
				// Controlla se il parametro di input è compreso tra y e z ed Imposta il valore dell'input corrente a 1 e gli altri a 0
				if (parametroValore >= y && parametroValore <= z) {
					$('input[name="BigliettoTipologiaPax['+x+'_Da '+y+' a '+z+' passeggeri]').val(1);
					$('input[name="BigliettoTipologiaPax['+x+'_Da '+y+' a '+z+' passeggeri]').trigger('change');
				} else {
					$('input[name="BigliettoTipologiaPax['+x+'_Da '+y+' a '+z+' passeggeri]').val(0);
					
				}
			}
		}
		
		// Trova l'input con name che corrisponde al pattern desiderato
        var input = $(this).find('input[name^="BigliettoTipologiaPax["][name$="passeggeri exclusive]"]');
		// Verifica se l'input è stato trovato
        if (input.length > 0) {
			// Estrae i valori y e z dal name dell'input
			var match = input.attr('name').match(/\[(\d+)_Da (\d+) a (\d+) passeggeri exclusive\]/);
			if (match) {
				var x = parseInt(match[1]);
				var y = parseInt(match[2]);
				var z = parseInt(match[3]);
				// Controlla se il parametro di input è compreso tra y e z ed Imposta il valore dell'input corrente a 1 e gli altri a 0
				if (parametroValore >= y && parametroValore <= z) {
					$('input[name="BigliettoTipologiaPax['+x+'_Da '+y+' a '+z+' passeggeri exclusive]').val(1);
					$('input[name="BigliettoTipologiaPax['+x+'_Da '+y+' a '+z+' passeggeri exclusive]').trigger('change');
				} else {
					$('input[name="BigliettoTipologiaPax['+x+'_Da '+y+' a '+z+' passeggeri exclusive]').val(0);
					
				}
			}
		}
    });
}

function MessaggioTicket(prenotazione, prezzo) {
	var tipo = $("#Tipo").val();
	var messaggio = "";
	if(tipo == 'ticket') {
		messaggio = "<?php echo $dizionario['biglietto']['messaggio_ticket_default'];?>";
		messaggio = messaggio.replace("%v1%", prenotazione);
	} else if(tipo == 'payment') {
		messaggio = "<?php echo $dizionario['biglietto']['messaggio_payment_default'];?>";
		messaggio = messaggio.replace("%v1%", prezzo);
		messaggio = messaggio.replace("%v2%", prenotazione);
	} else if(tipo == 'payment_intesasanpaolo') {
		messaggio = `<?php echo $dizionario['biglietto']['messaggio_payment_intesasanpaolo'];?>`;
		messaggio = messaggio.replace("%v1%", prenotazione);
		messaggio = messaggio.replace("%v2%", prezzo);
	} else if(tipo == 'payment_paypal') {
		messaggio = `<?php echo $dizionario['biglietto']['messaggio_payment_paypal'];?>`;
		messaggio = messaggio.replace("%v1%", prenotazione);
		messaggio = messaggio.replace("%v2%", prezzo);
	} else if(tipo == 'payment_banca5') {
		messaggio = `<?php echo $dizionario['biglietto']['messaggio_payment_banca5'];?>`;
		messaggio = messaggio.replace("%v1%", prenotazione);
		messaggio = messaggio.replace("%v2%", prezzo);
	} else if(tipo == 'payment_postepay') {
		messaggio = `<?php echo $dizionario['biglietto']['messaggio_payment_postepay'];?>`;
		messaggio = messaggio.replace("%v1%", prenotazione);
		messaggio = messaggio.replace("%v2%", prezzo);
	} else if(tipo == 'payment_barzahlen') {
		messaggio = `<?php echo $dizionario['biglietto']['messaggio_payment_barzahlen'];?>`;
		messaggio = messaggio.replace("%v1%", prenotazione);
		messaggio = messaggio.replace("%v2%", prezzo);
	} else {
		messaggio = "";
	}
	$("#Messaggio").val(messaggio);
}

function ControllaDataPassata(diff, diff2)
{
    if (diff>0){
    	alert("<?=$dizionario['biglietto']['corsa_partita']?>");
    	
        return true;
    }else if (diff==0){
    	if(diff2>0){
    		alert("<?=$dizionario['biglietto']['corsa_partita_no_fermata']?>");
		} else {
			alert("<?=$dizionario['biglietto']['corsa_partita_fermata']?>");
		}
        return true;
    } else {
    	return true;
    }
}

// function ScegliPosto(oggetto,corsa)
// {
//         ischecked=oggetto.checked;
//         posti_selezionabili=0;

//           if ($("#TotalePax"))
//             posti_selezionabili=$("#TotalePax").val();
//           else
//             {
//                 alert("Il numero di viaggiatori non e' stato ancora inserito.");
//                 return false;
//             }

//         if (posti_selezionabili==0)
//             {
//                 alert("E' necessario indicare il numero dei passeggeri");
//                 oggetto.checked=false;
//                 return false;

//             }

//        // alert(posti_selezionabili);

//        if ($("#posti_riservati_a").val()!='0')
//          {
//               posti_selezionati_a=$("#posti_riservati_a").val();
//              $("#posti_riservati_a").val('0');
//          }

//          if ($("#posti_riservati_r").val()!='0')
//          {
//               posti_selezionati_r=$("#posti_riservati_r").val();
//              $("#posti_riservati_r").val('0');
//          }


//      if (corsa=='andata')
//          {
//         if (ischecked)
//             {
//                 if ((posti_selezionati_a)==posti_selezionabili)
//                     {
//                         alert("Tutti i posti sono stati selezionati per la corsa di "+corsa);
//                         oggetto.checked=0;
//                     }
//                     else
//                       posti_selezionati_a=posti_selezionati_a+1;

//             }

//         else
//                 posti_selezionati_a=posti_selezionati_a-1;

//          }
//        else
//          {
//         if (ischecked)
//             {
//                 if ((posti_selezionati_r)==posti_selezionabili)
//                     {
//                              alert("Tutti i posti sono stati selezionati per la corsa di "+corsa);

//                         oggetto.checked=0;
//                     }
//                     else
//                       posti_selezionati_r=posti_selezionati_r+1;

//             }

//         else
//                 posti_selezionati_r=posti_selezionati_r-1;

//          }
// }

	var numeroBigliettiPrecendente;
	
	function SalvaNumeroBiglietti(input) {
		numeroBigliettiPrecendente = input.value;
	}

	function rimuoviBiglietto(name, riga) {
		$("#pagamentiTabella a").click(function(){return false;});
		var current = $("input[name='BigliettoTipologiaPax[" + name + "]']").val();
		$("input[name='BigliettoTipologiaPax[" + name + "]']").val(current - 1);

		$("#" + riga).remove();
		
		totalePasseggeri--;
		
		//se è un tour privato e ho eliminato un passeggero porto a 0 il valore
		var tipoTourPasseggeri = $('#TipoTourPasseggeri');
		if (tipoTourPasseggeri.length > 0) {
			tipoTourPasseggeri.val(0);
		}
		
		CalcolaPrezzoTipoBiglietto(null);
	}

	function ControlloResidio(riga){
		if(parseInt($("#StatoPassaggio").val()) == 3){
			var riduzioneTotale = 0;
			var newResiduo = parseFloat($("#newResiduo").val());
			for (i=0; i<10 ; i++) {
				if($("#PrezzoRiduzione"+i).val()){
					riduzioneTotale = riduzioneTotale + parseFloat($("#PrezzoRiduzione"+i).val()) - parseFloat($("#oldRiduzione"+riga).val());
				}
			}
			var differenza = newResiduo-riduzioneTotale;
			if(differenza>=0){
				$('#variazioneIndicazione').html(differenza.toFixed(2)+" EURO");
			} else {
				$("#PrezzoRiduzione"+riga).val($("#oldRiduzione"+riga).val());
				alert("Non e' possibile avere un riduzione maggiore poiche' il biglietto e' gia' stato emesso");
			}
		}
	}

	function ControlloResidioAumento(riga){
		if(parseInt($("#StatoPassaggio").val()) == 3){
			var aumentoTotale = 0;
			var oldResiduo = parseFloat($("#oldResiduo").val());
			for (i=0; i<10 ; i++) {
				if($("#PrezzoAumento"+i).val()){
					aumentoTotale = aumentoTotale + parseFloat($("#PrezzoAumento"+i).val());
				}
			}
			if(aumentoTotale>=0){
// 				var differenza = oldResiduo - aumentoTotale;
// 				if(differenza>=0){
// 					$('#variazioneIndicazione').html(differenza.toFixed(2)+" EURO");
// 				} else {
// 					$("#PrezzoAumento"+riga).val($("#oldAumento"+riga).val());
// 					alert("Non e' possibile avere un diminuire oltre l'aumento poiche' il biglietto e' gia' stato emesso");
// 				}
			} else {
				$("#PrezzoAumento"+riga).val($("#oldAumento"+riga).val());
				alert("Valore non valido");
			}
			
		}
	}

	function occupaPosto(id, tragitto) {
		
		if(tragitto == 'A') {
			tragitto = '#postoA_tot';
		} else {
			tragitto = '#postoR_tot';
		}
		if ($('#'+id+'_img').hasClass( "posto_libero" )){
			if($(tragitto).val() < $('#TotalePax').val()) {
				$('#'+id).val(1);
				$('#'+id+'_img').addClass("posto_occupato");
				$('#'+id+'_img').removeClass("posto_libero");
				$('#'+id+'_img').attr('src', "/images/bus/taken_seat2.png");
				$(tragitto).val(parseInt($(tragitto).val())+1);
			} else {
				alert("<?php echo $dizionario['biglietto']['posti_finiti']?>");
			}
		} else {
			$('#'+id).val(0);
			$('#'+id+'_img').addClass("posto_libero");
			$('#'+id+'_img').removeClass("posto_occupato");
			$('#'+id+'_img').attr('src', "/images/bus/empty_seat.png");
			$(tragitto).val(parseInt($(tragitto).val())-1);
		}
	}
	
	function ModificaBiglietti(input, bigliettoId) {
		var numero = input.value;
		// Controlla se il valore dell'input è maggiore del numero di elementi con classe 'passeggeroId' e valore bigliettoId
		if (input.value < $('#passeggeri .passeggeroId[value="'+bigliettoId+'"]').length) {
			//rimuovi servizio da lista Info Passeggeri
			$('.rimuovi_' + bigliettoId).parent().remove();

			// Rimuovi righe hidden dei servizi
			$('#passeggeri .passeggeroId[value="' + bigliettoId+'"]').parent().remove();
			
			//calcola i totali dopo la modifica di rimozione per aggiungere il numero di biglietti desiderati
			CalcolaPrezzoTipoBiglietto(input, false);
		} else {
			// Chiama CalcolaPrezzoTipoBiglietto se la condizione non è soddisfatta
			CalcolaPrezzoTipoBiglietto(input);
		}
	}
	
    function CalcolaPrezzoTipoBiglietto(input, message = true)
    {
      /* Calcolo il totale dei biglietti */
      NTipologieBigletti=$("#NumeroBiglietti").val();

      i=0;
      QntTotale = 0;
      while(i < NTipologieBigletti)
      {
          Qnt = parseInt($("#Pax"+i).val());
          if (Qnt <= 0) {
      		Qnt = 0;
          }
            
          QntTotale = QntTotale + Qnt;

          i++;
      }

      if (message && input != null && QntTotale < totalePasseggeri) {
          alert("<?=$dizionario['biglietto']['diminuire_biglietti']?>");
          input.value = numeroBigliettiPrecendente;
          return;
      }
	  	
	  //effettuo un controllo affinchè se sono tour privato posso selezionare un solo gruppo tra i biglietti passeggeri
	  if($("#TipoTour").val() == 1 ) {
		if (!$(input).hasClass("servizioInput")) {
			
			var inputsServizi = $(".servizioInput");
			var totServizi = 0;
			inputsServizi.each(function(){
				var valore = parseFloat($(this).val()) || 0;
				totServizi += valore;
			});
			
			if(input != null && ((input.value > 1 && !$(input).hasClass("servizioInput")) || (totalePasseggeri - totServizi) > 0)) {
				alert("<?=$dizionario['biglietto']['messaggio_privato_1_biglietto']?>");
				input.value = numeroBigliettiPrecendente;
				return;
			}
		}
	  }

  	  /* Calcola i prezzi */
      NTipologieBigletti=$("#NumeroBiglietti").val();

      i=0;
      PrezzoParziale = 0;
      PrezzoTotale = 0;
      QntTotale = 0;
      while(i < NTipologieBigletti)
      {
          Qnt = parseInt($("#Pax"+i).val());

          if (Qnt <= 0) {
        	PB_Unit = parseFloat(0);
        	PF_Unit = parseFloat(0);
          } else {
        	  PrezzoPax = parseFloat($("#Prezzo"+i).val().replace('.', '').replace(',', '.')).toFixed(2);
			  R1 = $("#PrezzoRiduzione"+i).val();
			  if (R1 === null || R1 === undefined || R1 === '' || isNaN(R1)) {
				  R1 = 0;
			  } else {
				  R1 = R1.replace(",", ".");
			  }
			  Riduzione = parseFloat(R1);

              A1 = $("#PrezzoAumento"+i).val();
			  if (A1 === null || A1 === undefined || A1 === '' || isNaN(A1)) {
				  A1 = 0;
			  } else {
				  A1 = A1.replace(",", ".");
			  }
              Aumento = parseFloat(A1);

			  // Arrotonda il prezzo parziale al multiplo di 5 superiore (come in PHP)
			  PB_Complete = (Qnt * PrezzoPax);
			  PB_Frac = PB_Complete - Math.floor(PB_Complete);
			  PB_Unit = parseInt(PB_Complete);
			  if (PB_Frac > 0) {
			       PB_Unit += 1;
			  }
			  PB_Complete = (Qnt * PrezzoPax);
			  

			  // Arrotonda il subtotale al multiplo di 5 superiore (come in PHP)
			  PF_Complete = PB_Unit - Riduzione + Aumento;
			  PF_Frac = PF_Complete - Math.floor(PF_Complete);
			  PF_Unit = parseInt(PF_Complete);
			  if (PF_Frac > 0) {
			       PF_Unit += 1;
			  }
			  PF_Complete = PB_Unit - Riduzione + Aumento;
			  

			  if($("#Libera").val() == 1) {
				PB_Unit=PB_Complete;
				PF_Unit=PF_Complete;
			  }
          }

          Pb1 = PB_Unit.toFixed(2);
          Pb1 = Pb1.replace('.', ',').replace(/\d(?=(\d{3})+,)/g, '$&.');
          $("#PrezzoParziale"+i).html(Pb1 + " &euro;");

          Pf1 = PF_Unit.toFixed(2);
          Pf1 = Pf1.replace('.', ',').replace(/\d(?=(\d{3})+,)/g, '$&.');
          $("#PrezzoFinale"+i).html(Pf1 + " &euro;");

          PrezzoTotale = PrezzoTotale + PF_Unit;
          QntTotale = QntTotale + Qnt;

          i++;
      }

      PrezzoTot = parseFloat(PrezzoTotale).toFixed(2);
      PrezzoTot = PrezzoTot.replace('.', ',').replace(/\d(?=(\d{3})+,)/g, '$&.');
      $("#PrezzoTotalePax").html("<strong>"+PrezzoTot+" &euro;</strong>");
      $("#TotalePax").val(QntTotale);

      MostraPasseggeriChange();
    }

    function MostraPasseggeriChange() {
		
    	data = {};
    	data['action'] = 'GetPasseggeri';
    	data['StatoPassaggio'] = $('#StatoPassaggio').val();
    	
    	var totalePax = $("#TotalePax").val();

        if (totalePax !== undefined && totalePax != 0) {
        	NTipologieBigletti=$("#NumeroBiglietti").val();

        	data['Passeggeri'] = {};

            i=0;
            while(i < NTipologieBigletti)
            {
                Qnt = parseInt($("#Pax"+i).val());
                TipoId = $("#TipoBigliettoId"+i).val();

                if (Qnt > 0)
                {
                	passeggero = {};
                    passeggero['TipoId'] = TipoId;
                    passeggero['Qnt'] = Qnt;
                    data['Passeggeri'][i] = passeggero;
           		}

      			i++;
            }
        }

        data['PasseggeriInseriti'] = {};
        $('.passeggero').each(function(i) {
        	passeggero = {};
        	passeggero['TipoBigliettoId'] = $(this).find('.passeggeroId').val();
        	passeggero['TipologiaBiglietto'] = $(this).find('.passeggeroBiglietto').html();
        	passeggero['Cognome'] = $(this).find('.passeggeroCognome').val();
        	passeggero['Nome'] = $(this).find('.passeggeroNome').val();
        	passeggero['SessoId'] = $(this).find('.passeggeroSesso').val();
        	passeggero['Eta'] = $(this).find('.passeggeroEta').val();
        	passeggero['Principale'] = ($(this).find('.passeggeroPrincipale').attr("checked"))? 1 : 0;
        	data['PasseggeriInseriti'][i] = passeggero;
       	});
        

        page_to_load="/protected/modules/rt_biglietto/biglietto_action.php";

        $.ajax({
            type: "POST",
            url: page_to_load,
            data: data,
            success: function(data) {
            	$("#passeggeri").html(data);
            }
        });
    }

    function ShowAumentoNote(x, bigliettoId) {
    	A1 = $("#PrezzoAumento" + x).val();
        A1 = A1.replace(",", ".");
        Aumento = parseFloat(A1);

        tr = $('#NoteAumento' + x);
        if (Aumento > 0) {
        	tr.html(
                '<td colspan="12">' +
                	'<div class="brain_campiform">' +
						'<label for="BigliettoTipologiaPaxAum[' + bigliettoId + '][Note]"><?php echo $dizionario['biglietto']['note_aumento'];?></label>' +
						'<input id="AumentoNote' + x + '"' +
					 	'type="text"' +
						'value=""' +
						'name="BigliettoTipologiaPaxAum[' + bigliettoId + '][Note]"' +
						'class="">' +
					'</div>' +
				'</td>'
			);
        } else {
        	tr.html('');    
        }
    }

    function ShowRiduzioneNote(x, bigliettoId) {
    	R1 = $("#PrezzoRiduzione" + x).val();
        R1 = R1.replace(",", ".");
        Riduzione = parseFloat(R1);
        
    	tr = $('#NoteRiduzione' + x);
        if (Riduzione > 0) {
        	tr.html(
                '<td colspan="12">' +
                	'<div class="brain_campiform">' +
						'<label for="BigliettoTipologiaPaxRid[' + bigliettoId + '][Note]"><?php echo $dizionario['biglietto']['note_riduzione'];?></label>' +
						'<input id="RiduzioneNote' + x + '"' +
					 	'type="text"' +
						'value=""' +
						'name="BigliettoTipologiaPaxRid[' + bigliettoId + '][Note]"' +
						'class="">' +
					'</div>' +
				'</td>'
			);
        } else {
        	tr.html('');    
        }
    }

	function loadMediazioneStep(modulo,page,elem)
    {
	    CambiaStep(modulo,page,elem);
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

    function MostraNotePerTratta()
    {

         TipoViaggio=$("#TipoViaggioId").val();
         CorsaAndataId=$("#CorsaSelezionataA").val();
         var CorsaDataAndata=$("#DataSelezionataA").val();
         var PartenzaId = $("#PartenzaId").val();
         var DestinazioneId = $("#DestinazioneId").val();
         CorsaRitornoId=0;
         var CorsaDataRitorno = 0;
        if (TipoViaggio==2) {
             CorsaRitornoId=$("#CorsaSelezionataR").val();
             CorsaDataRitorno=$("#DataSelezionataR").val();
        }

            page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetNotePerTratta&CorsaAndataId="+CorsaAndataId+"&CorsaRitornoId="+CorsaRitornoId+"&TV="+TipoViaggio+"&Tp=A&CorsaDataAndata="+CorsaDataAndata+"&CorsaDataRitorno="+CorsaDataRitorno+'&PartenzaId='+PartenzaId+'&DestinazioneId='+DestinazioneId;
         $.get(page_to_load, function(data){
             $("#NotePerTratta").show();
              $("#ElencoNoteAndata").show();
              $("#ElencoNoteAndata").html(data);

             } );

             /*if (TipoViaggio==2)
                 {
                      page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetNotePerTratta&CorsaId="+CorsaRitornoId+"&FermataIdAP="+FermataIdRP+"&FermataIdAD="+FermataIdRD+"&TV="+TipoViaggio+"&Tp=R";
         $.get(page_to_load, function(data){
             $("#NotePerTratta").show();
            $("#ElencoNoteRitorno").show();
              $("#ElencoNoteRitorno").html(data);
             } );



                 }*/

        MostraPasseggeri();
    }
	
	

    function MostraPasseggeri() {
        page_to_load="/protected/modules/rt_biglietto/biglietto_action.php";

        $.ajax({
            type: 'POST',
            url: page_to_load,
            data: {'action' : 'GetPasseggeri'},
            success: function(data) {
            	$("#passeggeri").html(data);
            }
        });
    }

    function AddCheckModifica(){
    	if($('#modPre').val() == 1){
			$('#PartenzaId').change(function(){
				$('#modificaItinerario').val(1);
			});
			$('#DestinazioneId').change(function(){
				$('#modificaItinerario').val(1);
			});
			$('#TipoViaggioId').change(function(){
				$('#modificaItinerario').val(1);
			});
			$('#TipoTour').change(function(){
				$('#modificaItinerario').val(1);
			});
			$('input[name=\'CorsaA\']').change(function(){
				$('#modificaData').val(1);
			});
			$('input[name=\'CorsaR\']').change(function(){
				$('#modificaDataRitorno').val(1);
			});
			$('.passeggeroCognome').change(function(){
				$('#modificaNominativo').val(1);
			});
			$('.passeggeroNome').change(function(){
				$('#modificaNominativo').val(1);
			});
		}
    }

    function MostraTastiDiPrenotazione(CorsaAndataId,FermataIdAP)
    {

	   var libera = $('#Libera').val();
       $("#Prenota").hide();
       $("#Emetti").hide();
       $("#Avanti").hide();

       $("#Prenota").attr('disabled','disabled');
       $("#Emetti").attr('disabled','disabled');
       $("#Avanti").attr('disabled','disabled');

        page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=AbilitaTipoPrenotazione&CorsaId="+CorsaAndataId+"&FermataIdAP="+FermataIdAP+"&Libera="+libera;


        $.get(page_to_load, function(data){
			AddCheckModifica();
             msg=jQuery.trim(data);

              if (msg=='2') // emissione obbligatoria
              {
                    $("#Prenota").removeAttr('disabled');
                    //$("#Emetti").removeAttr('disabled');
                    //$("#Avanti").removeAttr('Avanti');
                    $("#Prenota").show();
                    //$("#Emetti").show();
              }
               else if (msg=='1') // solo prenotazione
              {

                // var agree=confirm("E' obbligatorio emettere il titolo di viaggio! Continuare?");
                 agree=true;
                 if (agree)
                 {
                          $("#Avanti").removeAttr('disabled');
                          $("#Avanti").show();
                 }
                 else
                     alert("<?=$dizionario['biglietto']['impossibile_continuare']?>");
              }
             } );

    }
 
	function MostraTipoBiglietti(Tp) {
		posti_selezionati_a = 0;
        posti_selezionati_r = 0;
        TipoViaggio = $("#TipoViaggioId").val();
        TipoTour = $("#TipoTour").val();
        FermataIdAP = $("#FermataIdAP").val();
        FermataIdAD = $("#FermataIdAD").val();
        CorsaAndataId = $("#CorsaSelezionataA").val();
        DataAndata = $("#DataSelezionataA").val();
        CorsaRitornoId = $("#CorsaSelezionataR").val();
        DataRitorno = $("#DataSelezionataR").val();
		Libera = $('#Libera').val();
		DataPartenzaLibera = $('#DataPartenzaLibera').val();
		OrarioPartenzaLibera = $('#OrarioPartenzaLibera').val();
		OrarioArrivoLibera = $('#OrarioArrivoLibera').val();
		var PartenzaId = $("#PartenzaId").val();
        var DestinazioneId = $("#DestinazioneId").val();
        
        if ((Libera == 0 && FermataIdAP > 0 && FermataIdAD > 0) || 
			(Libera == 1 && DataPartenzaLibera != '' && OrarioPartenzaLibera != '' && OrarioArrivoLibera != '')) {
        	// seleziona i listini possibili
        	FermataIdRP=0;
            FermataIdRD=0;
            if (CorsaRitornoId>0) {
				FermataIdRP=$("#FermataIdRP").val();
				FermataIdRD=$("#FermataIdRD").val();
			}
            page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetTipologiaBiglietti&CorsaId="+CorsaAndataId+"&FermataIdAP="+FermataIdAP+"&FermataIdAD="+FermataIdAD+"&TV="+TipoViaggio+"&Tp="+Tp+"&Data="+DataAndata+"&CorsaRitornoId="+CorsaRitornoId+"&FermataIdRP="+FermataIdRP+"&FermataIdRD="+FermataIdRD+"&DataR="+DataRitorno+"&TipoTour="+TipoTour+"&Libera="+Libera+"&PartenzaId="+PartenzaId+"&DestinazioneId="+DestinazioneId;

			$.get(page_to_load, function(data){
	            $("#InfoBiglietti").show();
	            $("#TipologiaBiglietti").show();
	            $("#TipologiaBiglietti").html(data);
	             
	            MostraTastiDiPrenotazione(CorsaAndataId,FermataIdAP);
	            AddCheckModifica();
			});
		}
		
		if($('#Libera').val() == 1) {
			MostraNotePerTratta();
			MostraTastiDiPrenotazione(0,0);
		}
		
    }


    function MostraSchemaBus()
    {
        $("#SelezionePosti").show();

          TipoViaggio=$("#TipoViaggioId").val();
        CorsaAndataId=$("#CorsaSelezionataA").val();
        CorsaRitornoId=$("#CorsaSelezionataR").val();
         DataAndata=$("#DataSelezionataA").val();
        DataRitorno=$("#DataSelezionataR").val();

         page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=MostraSchemaBus&TipoViaggio="+TipoViaggio+"&CorsaAndataId="+CorsaAndataId+"&CorsaRitornoId="+CorsaRitornoId+"&DataAndata="+DataAndata+"&DataRitorno="+DataRitorno;

        $.get(page_to_load, function(data){
            var seleziona='SelezionaPostiA';
            $('#'+seleziona).show();
            $('#'+seleziona).html(data);
        });
    }

    function MostraFermate() {

        TipoViaggio = $("#TipoViaggioId").val();
        CorsaAndataId = $("#CorsaSelezionataA").val();
        CorsaRitornoId = $("#CorsaSelezionataR").val();
        if($('#CorsaRitornoAperto').attr("checked") == true){
			CorsaRitornoAperto = true;
        } else {
        	CorsaRitornoAperto = false;
        }
        ComunePartenzaId=$("#PartenzaId").val();
        ComuneDestinazioneId=$("#DestinazioneId").val();

        DataSelezionataA = $("#DataSelezionataA").val();

        $("#FermataSalitaA").html("");
        $("#FermataDiscesaA").html("");
        $("#FermataSalitaR").html("");
        $("#FermataDiscesaR").html("");

        $("#InfoFermateA").hide();
        $("#TipologiaBiglietti").html("");
        $("#InfoBiglietti").hide();

        if (CorsaAndataId>0){
           	$("#TipologiaBiglietti").show();
         	page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetFermate&CorsaId="+CorsaAndataId+"&ComuneAndata="+ComunePartenzaId+"&ComuneRitorno="+ComuneDestinazioneId+"&Tp=A&DataSelezionataA="+DataSelezionataA;
         	DataAndata =  $("#DataSelezionataA").val();
         	$.get(page_to_load, function(data){
            	$("#InfoFermateA").show();
				$("#InfoFermateA > .info").html(data);
				
            	if(TipoViaggio==1 && CorsaAndataId>0){
            		$("#InfoBiglietti").show();
            		$("#TipologiaBiglietti").html("<h2 style='text-align:center;'><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?php echo $dizionario['generale']['caricamento_in_corso'];?></h2>");
                	MostraTipoBiglietti("A");
            	} else if ((TipoViaggio == 2 && CorsaAndataId > 0 && CorsaRitornoId > 0) || CorsaRitornoAperto == true){
                	if(CorsaRitornoAperto == true){
                		CorsaAndataId = $("#CorsaSelezionataA").val();
                        if(CorsaAndataId != ''){
                			page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetCorsaRitornoAperta&CorsaAndataId="+CorsaAndataId;
                				$.get(page_to_load, function(data){
                					var json = JSON.parse(jQuery.trim( data ));
                	   				$('#CorsaSelezionataR').val(json['CorsaId']);

                	   				$("#InfoBiglietti").show();
            	            		$("#TipologiaBiglietti").html("<h2 style='text-align:center;'><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?php echo $dizionario['generale']['caricamento_in_corso'];?></h2>");
            	            		DataRitorno=  $("#DataSelezionataR").val();
            	            		page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetFermate&CorsaId="+CorsaRitornoId+"&ComuneAndata="+ComuneDestinazioneId+"&ComuneRitorno="+ComunePartenzaId+"&Tp=R&DataSelezionataA="+DataRitorno+"&CorsaRitornoAperto="+CorsaRitornoAperto+"&CorsaAndata="+CorsaAndataId+"&DataAndata="+DataSelezionataA;
            	            		
            	                 	$.get(page_to_load, function(data){
            	        	            $("#InfoFermateR").show();
            	        				$("#InfoFermateR > .info").html(data);
            	                       	if(TipoViaggio == 2 && CorsaAndataId>0){
            	                       		$("#InfoBiglietti").show();
            	                    		$("#TipologiaBiglietti").html("<h2 style='text-align:center;'><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?php echo $dizionario['generale']['caricamento_in_corso'];?></h2>");
            	                        	MostraTipoBiglietti("R");
            	                       	}
            	                     } );
									
                					
                   			});
                        }
                    } else{
	            		$("#InfoBiglietti").show();
	            		$("#TipologiaBiglietti").html("<h2 style='text-align:center;'><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?php echo $dizionario['generale']['caricamento_in_corso'];?></h2>");
	            		DataRitorno=  $("#DataSelezionataR").val();
	            		page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetFermate&CorsaId="+CorsaRitornoId+"&ComuneAndata="+ComuneDestinazioneId+"&ComuneRitorno="+ComunePartenzaId+"&Tp=R&DataSelezionataA="+DataRitorno+"&CorsaRitornoAperto="+CorsaRitornoAperto+"&CorsaAndata="+CorsaAndataId+"&DataAndata="+DataSelezionataA;
	            		
	                 	$.get(page_to_load, function(data){
	        	            $("#InfoFermateR").show();
	        				$("#InfoFermateR > .info").html(data);
	                       	if(TipoViaggio == 2 && CorsaAndataId>0){
	                       		$("#InfoBiglietti").show();
	                    		$("#TipologiaBiglietti").html("<h2 style='text-align:center;'><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?$dizionario['generale']['caricamento_in_corso']?> </h2>");
	                        	MostraTipoBiglietti("R");
	                       	}
	                     } );
                    }
                     
            	}
            	AddCheckModifica();
             });

           
       }
        
//        if ((CorsaRitornoId>0) && (TipoViaggio==2)) {
// 			page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetFermate&CorsaId="+CorsaRitornoId+"&ComuneAndata="+ComuneDestinazioneId+"&ComuneRitorno="+ComunePartenzaId+"&Tp=R&DataSelezionataA="+DataSelezionataA;
//          	$.get(page_to_load, function(data){
// 	            $("#InfoFermateR").show();
// 				$("#InfoFermateR > .info").html(data);
//                	if(TipoViaggio == 2 && CorsaAndataId>0){
//                		$("#InfoBiglietti").show();
//             		$("#TipologiaBiglietti").html("<h2 style='text-align:center;'>Caricamento in corso ... </h2>");
//                 	MostraTipoBiglietti("R");
//                	}
//              } );

//              DataRitorno=  $("#DataSelezionataR").val();
//         }
        MostraNotePerTratta();
    }

    function MostraPossibiliDestinazioni(ObjPartenza)
    {
        $("#DestinazioneId").val("");

        // caricare l'elenco delle possibili destinazioni a partire dal comune


    }

    function MostraElencoCorse()
    {
        ComunePartenzaId = $("#PartenzaId").val();
        ComuneDestinazioneId = $("#DestinazioneId").val();
        TipoViaggio = $("#TipoViaggioId").val();
        TipoTour = $("#TipoTour").val();
		TipoPrenotazione = $("#Libera").val();
        modPre = $("#modPre").val();
        TipoRicercaId = $("#TipoRicercaId").val();

        if(TipoViaggio == 1){
        	$('#CorsaRitornoAperto').attr('checked', false);
        	$('#Prenotazione[RitornoOpen]').val(0);
        	$('#CorsaSelezionataR').val('');
        	$('#DataSelezionataR').val('');
        }
        
        $("#ElencoA").html("");
        $("#ElencoR").html("");
        $("#ElencoCorseA").hide();
        $("#ElencoCorseR").hide();
        $("#FermataSalitaA").html("");
        $("#FermataDiscesaA").html("");
        $("#InfoFermateA").hide();
        $("#NotePerTratta").hide("");
        $("#ElencoCorseL").hide();
		totalePasseggeri = 0;
        
        var minDate = $( "#RicercaAId" ).datepicker( "option", "minDate" );
		data_corrente=$( "#RicercaDaId" ).val();
		$( "#RicercaAId" ).datepicker( "option", "minDate", data_corrente);
        
        $("#CorsaSelezionataA").html("");
        $("#CorsaSelezionataR").html("");
        $("#DataSelezionataA").html("");
        $("#DataSelezionataR").html("");
        $("#InfoFermateA > .info").html("");
        $("#InfoFermateR > .info").html("");
        $("#InfoFermateA").hide();
        $("#InfoFermateR").hide();
        $("#InfoBiglietti").hide();
		$("#passeggeri").html("");
       
        if ((ComunePartenzaId>0) && (ComuneDestinazioneId>0) && (TipoViaggio>0) && (TipoTour != '' && TipoTour>=0) && TipoPrenotazione >= 0) {
			if(TipoPrenotazione == 0) {
				//prenotazione standard
				$("#ElencoCorseA").show();
				if (modPre=='0')
					$("#RicercaDaId").val('');
				 
				if (TipoViaggio==2) {
					$("#ElencoCorseR").show();
					if (modPre=='0')
						$("#RicercaAId").val('');
				} else {
					if (modPre=='0')
						$("#RicercaAId").val(data_corrente);
				}
				if (modPre=='1') {
					page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetCorse&PartenzaId="+ComunePartenzaId+"&DestinazioneId="+ComuneDestinazioneId+"&Tp=A&TipoTour="+TipoTour;
					$.get(page_to_load, function(data){
						$("#ElencoCorseA").show();
						$("#ElencoA").html(data);
						AddCheckModifica();
						if (TipoViaggio==2) {
							page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetCorse&PartenzaId="+ComunePartenzaId+"&DestinazioneId="+ComuneDestinazioneId+"&Tp=R&TipoTour="+TipoTour;
							$.get(page_to_load, function(data){
								$("#ElencoCorseR").show();
								$("#ElencoR").html(data);
								AddCheckModifica();
							});
						}
						AddCheckModifica();
						MostraFermate();                 
					});
				}
				$("#OrarioPartenzaLibera").val("00:00");
				$("#OrarioArrivoLibera").val("00:00");
			} else {
				//prenotazione libera
				$("#ElencoCorseL").show();
				$("#OrarioPartenzaLibera").val($('#filtro_libera_orario_partenza').val());
				$("#OrarioArrivoLibera").val($('#filtro_libera_orario_arrivo').val());
				
				// Ottieni la data odierna
				var today = new Date();

				// Estrai il giorno, il mese e l'anno
				var day = ('0' + today.getDate()).slice(-2); // Aggiunge uno zero davanti e prende solo gli ultimi due caratteri
				var month = ('0' + (today.getMonth() + 1)).slice(-2); // Aggiunge uno zero davanti e prende solo gli ultimi due caratteri
				var year = today.getFullYear();

				// Formatta la data nel formato desiderato (GG/MM/AAAA)
				var formattedDate = day + '/' + month + '/' + year;

				// Imposta il valore del campo di input con la data formattata
				if (modPre=='0') {
					$("#DataPartenzaLibera").val(formattedDate);
					$("#RicercaDaId").val(formattedDate);
					$("#OrarioPartenzaLibera").val("");
					$("#OrarioArrivoLibera").val("");
				} else {
					MostraTipoBiglietti("A");
				}
			}
		}
    }
    
    function MostraElencoCorseAR(ElencoCorse)
    {
        ComunePartenzaId=$("#PartenzaId").val();
        ComuneDestinazioneId=$("#DestinazioneId").val();
        TipoViaggio=$("#TipoViaggioId").val();
        TipoTour=$("#TipoTour").val();
      
       // $("#FermataSalitaA").html("");
       // $("#FermataDiscesaA").html("");
       // $("#InfoFermateA").hide();
        
         var minDate = $( "#RicercaAId" ).datepicker( "option", "minDate" );
		data_corrente=$( "#RicercaDaId" ).val();

			$( "#RicercaAId" ).datepicker( "option", "minDate", data_corrente);
        
        if (ElencoCorse=='A')
        {
            
         page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetCorse&PartenzaId="+ComunePartenzaId+"&DestinazioneId="+ComuneDestinazioneId+"&Tp=A&TipoTour="+TipoTour;
       $.get(page_to_load, function(data){
        
 
        $("#ElencoCorseA").show();
        $("#ElencoA").html(data);
        AddCheckModifica(); 
           
              } );
              
              
         }
          if (ElencoCorse=='R')
        {
            
         page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetCorse&PartenzaId="+ComunePartenzaId+"&DestinazioneId="+ComuneDestinazioneId+"&Tp=R&TipoTour="+TipoTour;
       $.get(page_to_load, function(data){
        
 
        $("#ElencoCorseR").show();
        $("#ElencoR").html(data);
        AddCheckModifica(); 
           
              } );
              
              
         }
    }

    function avviso_operazione(tipoavv)
    {
        if (tipoavv=="ok")
        {
        	$("#loading_big_"+tipoavv).fadeIn(2000,function() {
            	$("#loading_big_"+tipoavv).fadeOut(2000,function() {});
    		});
        }
        else
        {
            $("#loading_big_"+tipoavv).fadeIn(2000,function() {});

        }
    }

	function check_penale_modifica(){
		var data = {
				'modificaNominativo': $('#modificaNominativo').val(),
				'modificaItinerario': $('#modificaItinerario').val(),
				'modificaData': $('#modificaData').val(),
				'modificaDataRitorno': $('#modificaDataRitorno').val(),
				'Prenotazione[RitornoOpen]': $('#Prenotazione[RitornoOpen]').val(),
				'action': 'checkModificaPenale',
				'CorsaSelezionataA': $('#CorsaSelezionataA').val(),
				'DataSelezionataA': $('#DataSelezionataA').val(),
				'CorsaSelezionataR': $('#CorsaSelezionataR').val(),
				'DataSelezionataR': $('#DataSelezionataR').val()
				}
		$.ajax({
	           type: "POST",
	           url: "/protected/modules/rt_biglietto/biglietto_action.php",
	           data: data,
	           dataType: 'json',
	           success: function(msg) {
		        	if(msg.modifica == 'no'){
			        	if(!msg.isAdmin || msg.isAdmin == 0 || msg.isAdmin == "0"){
			        		alert(msg.messaggio);
			        		caricamentoInCorso = false;
	            			$('#layer_nero2').css('display','none');
			        	} else {
			        		var r = confirm("AVVISO PER AMMINISTRATORE: "+msg.messaggio+" <?=$dizionario['generale']['continuare']?>");
					    	 if (r == true) {
					    		 submit_form_biglietto();
					    	 } else {
					    		 caricamentoInCorso = false;
			            			$('#layer_nero2').css('display','none');
					    	 }
				        }
				     } else {
					     if(msg.penale == 'si'){
					    	 if(!msg.isAdmin  || msg.isAdmin == 0 || msg.isAdmin == "0"){
					    		 var r = confirm(msg.messaggio+" <?=$dizionario['generale']['continuare']?>");
						    	 if (r == true) {
						    		 submit_form_biglietto();
						    	 } else {
						    		 caricamentoInCorso = false;
				            			$('#layer_nero2').css('display','none');
						    	 }
					    	 } else {
					    		 caricamentoInCorso = false;
					    		 $('body').append('<div id="yesno_dialog" title="AVVISO PER AMMINISTRATORE"><p>'+msg.messaggio+' <?=$dizionario['generale']['applicare_penale']?></p></div>');
					    		    $("#yesno_dialog").dialog({
					    		        title: "Yes or No",
					    		        resizable: false,
					    		        modal: true,
					    		        buttons: {
					    		            "Si" : function () {
					    		            	submit_form_biglietto();
					    		                $(this).dialog("close");
					    		                $(this).remove();
					    		                $("#yesno_dialog").remove();
					    		            },
					    		            "No" : function (){
					    		            	$('#modificaNominativo').val(0);
					    						$('#modificaItinerario').val(0);
					    						$('#modificaData').val(0);
					    						$('#modificaDataRitorno').val(0);
					    						submit_form_biglietto();
					    		                $(this).dialog("close");
					    		                $(this).remove();
					    		                $("#yesno_dialog").remove();
					    		            },
					    		            "Annulla" : function (){
					    		                $(this).dialog("close");
					    		                $(this).remove();
					    		                $("#yesno_dialog").remove();
					    		            }

					    		        }
					    		    });
						   	 }
					     } else {
					    	 submit_form_biglietto();
						 }
				    	 
				     }   
	           },
	           error: function(){
					alert("errore");
					caricamentoInCorso = false;
        			$('#layer_nero2').css('display','none');
		       }
		});
	}	

	function check_duplicato(){
		$('.passeggeroCognome').each(function( index ) {
			$(this).val($(this).val().trim());
		});
		$('.passeggeroNome').each(function( index ) {
			$(this).val($(this).val().trim());
		});
		var data = {
				'data': $("#application_form").serialize(),
				'action': 'checkDuplicatoNominativo'
		}
		$.ajax({
	           type: "POST",
	           url: "/protected/modules/rt_biglietto/biglietto_action.php",
	           data: data,
	           dataType: 'json',
	           success: function(msg) {
					if(msg.result == false){
						 submit_form_biglietto();
					}else{
						var r = confirm(msg.messaggio+" <?=$dizionario['generale']['continuare']?>");
				    	 if (r == true) {
				    		 submit_form_biglietto();
				    	 } else {
				    		 caricamentoInCorso = false;
		            		$('#layer_nero2').css('display','none');
				    	 }
					}
	           },
	           error: function(){
					alert("<?=$dizionario['generale']['problemi_server']?>");
					caricamentoInCorso = false;
        			$('#layer_nero2').css('display','none');
		       }
		});
	}	
	
	// Funzione per controllare lo stato di #BarcaLibera
    function controllaBarcaLibera() {
		// Se il valore di #Libera è 1, imposta #BarcaLibera a required e aggiungi la classe required
		if ($('#Libera').val() == 1) {
			$('#BarcaLibera').attr('required', true).addClass('required');
			$('#TitoloLibera').attr('required', true).addClass('required');
		} else { // Altrimenti, rimuovi l'attributo "required" da #BarcaLibera e rimuovi la classe required
			$('#BarcaLibera').removeAttr('required').removeClass('required');
			$('#TitoloLibera').removeAttr('required').removeClass('required');

		}
    }
    
    function submit_form_biglietto()
    {
        var messaggio;
        $.ajax({
           type: "POST",
           url: "/protected/modules/rt_biglietto/biglietto_action.php",
           data: $("#application_form").serialize(),
           success: function(msg) {
        	   $('#layer_nero2').css('display','none');
        	   caricamentoInCorso = false;
           		msg = jQuery.trim(msg);

          		// controllo finale
          		if(msg.indexOf('##') > -1)
         	 	{
              		arr_response = msg.split("##");
              		messaggio = arr_response[0];
              		check_successivo = parseInt(arr_response[1]);
               		if (check_successivo > 0)
                 	{
                     	if(check_successivo==999){
                         	alert(messaggio);
                     	}else{
                     		var agree = confirm(messaggio);
                         	if (agree)
                        	{
    							$("#RichiestaCofermaErrori").val(check_successivo);
    							$("#application_form").submit();
                        	}
                        	else
                          		$("#RichiestaCofermaErrori").val(1);
                     		}
                     	}
                    	
                 	else {
						setTimeout(function() {
							loadMainContent('rt_biglietto','biglietto.php',this);
						}, 2000);
					}
                    	
          		}
          		else if(msg.indexOf('@') > -1)
          		{
               		arr_response = msg.split("@");
               		PrenotazioneId = arr_response[1];
               		Azione = arr_response[0];
               		CorsaId = $("#CorsaSelezionataA").val();
               		DataCorsa = $("#DataSelezionataA").val();
               		//window.open("/protected/modules/rt_previaggio/stampa_titoli_di_viaggio.php?CorsaId="+CorsaId+"&DataPartenza="+DataCorsa+"&PrenotazioneId="+PrenotazioneId+"&TipoTitolo="+Azione);
       				//loadMainContent('rt_biglietto','biglietto.php',this);
        			// loadMainContent('rt_biglietto','biglietto.php?do=edit&CorsaId=1&PrenotazioneId='+PrenotazioneId,this);
           			setTimeout(function() {
 						loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+PrenotazioneId+'&CorsaId='+CorsaId+'&step=2',this);
					}, 2000);
          		}
          		else if (msg.indexOf('_') > -1)
          		{
          			arr_response = msg.split("_");
        	  		messaggio = arr_response[0];
        	  		modifica = arr_response[1];
        	  		prenotazioneId = arr_response[2];
        	  		multi = arr_response[3];
            	  		
        	  		step_prenotazione = 1;
        	  		step_pagamenti = 2;
        	  		var step_messaggi = 4;
        	  		
                	corsaId = $("#CorsaSelezionataA").val();
					if (corsaId === undefined) {
						corsaId = arr_response[4];
					}

                	if ($("#brain_oscura").css("display")=="none")
                	{
						if (modifica > 0) {
							if (modifica == 2) {
								alert("<?=$dizionario['biglietto']['alert_rettifica_prezzo']?>");
								setTimeout(function() {
									loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+prenotazioneId+'&CorsaId='+corsaId+'&step='+step_pagamenti, this);
								}, 2000);
								
								
							} else if (modifica == 3) {
								alert(messaggio);
								setTimeout(function() {
									loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+prenotazioneId+'&CorsaId='+corsaId+'&step='+step_messaggi, this);
								}, 2000);
							} else {
								setTimeout(function() {
									loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+prenotazioneId+'&CorsaId='+corsaId+'&step='+step_prenotazione, this);
								}, 2000);
							}
						} else {
							if (multi == "1") {
								setTimeout(function() {
									loadMainContent('rt_biglietto','biglietto.php?do=add',this);
								}, 2000);
							} else {
								setTimeout(function() {
									loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+prenotazioneId+'&CorsaId='+corsaId+'&step='+step_pagamenti, this);
								}, 2000);
							}
						}

                    	avviso_operazione(messaggio);
                	}
                	else
                   	{
                    	ChiudiBox();
						setTimeout(function() {
                    		loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+prenotazioneId+'&CorsaId='+corsaId+'&step='+step_pagamenti, this);
						}, 2000);
					}
          		}
          		else if (msg.indexOf('||') > -1)
          		{
          			arr_response = msg.split("||");
        	  		messaggio = arr_response[1];
        	  		alert(messaggio);
        	  		ChiudiBox();
					setTimeout(function() {
        	  			loadMainContent('rt_biglietto','biglietto.php',this);
					}, 2000);
          		}
          		else
          		{
          			alert(msg);
          			caricamentoInCorso = false;
        			$('#layer_nero2').css('display','none');
          		}
            }, // end success
            error: function(){
				alert("<?=$dizionario['generale']['problemi_server']?>");
				caricamentoInCorso = false;
    			$('#layer_nero2').css('display','none');
	       }
         });
    }


function ConfermaPrenotazione(PrenotazioneId) {
	var stringa = "<?=$dizionario['biglietto']['confermare_prenotazione']?>";

	var conferma = confirm(stringa);

	if (conferma) {
		var page_to_load = "/protected/modules/rt_biglietto/biglietto_action.php?do=ConfermaPrenotazione&PrenotazioneId=" + PrenotazioneId;
		$.get(page_to_load, function(data) {
			alert("<?=$dizionario['biglietto']['val_prenotazione_confermata']?>");
			stripeInvioLink(PrenotazioneId);
		});
	}
}



function ConfermaPrenotazioneLista(PrenotazioneId,CodicePrenotazione)
    {
        stringa="<?=$dizionario['biglietto']['confermare_prenotazione']?> "+CodicePrenotazione+"?";


         conferma = confirm(stringa);

        if (conferma)
       {
           page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=ConfermaPrenotazioneLista&PrenotazioneId="+PrenotazioneId;
       $.get(page_to_load, function(data){

          msg=jQuery.trim(data);
          alert(msg);
         //alert("ok");

               avviso_operazione("ok");



              } );
       }

    }

    function changePrincipale(){
    	var selectedRow = $('input.passeggeroPrincipale:checked').val();
		var cognome = $("#PasseggeroCognome"+selectedRow).val();
		var nome = $("#PasseggeroNome"+selectedRow).val();
		var sesso = $("#PasseggeroSesso"+selectedRow).val();
		//$("input[name='Prenotazione[ClienteNome]']").val(cognome+" "+nome);
		//$("input[name='Prenotazione[ClienteSessoId]']").val(sesso);
    }

    function changePasseggero(index){
		var radio = $('#riga'+index+' input.passeggeroPrincipale');
		if(radio.attr('checked')==true){
			var cognome = $("#PasseggeroCognome"+index).val();
			var nome = $("#PasseggeroNome"+index).val();
			var sesso = $("#PasseggeroSesso"+index).val();
			//$("input[name='Prenotazione[ClienteNome]']").val(cognome+" "+nome);
			//$("input[name='Prenotazione[ClienteSessoId]']").val(sesso);
		}
    }

    function MostraRitornoAperto(){
    	$('#RicercaAId').parent().hide();
		$('#ElencoR').hide();
		$('#RicercaAId').removeClass('required');
        CorsaAndataId = $("#CorsaSelezionataA").val();
        if(CorsaAndataId != ''){
			page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=GetCorsaRitornoAperta&CorsaAndataId="+CorsaAndataId;
				$.get(page_to_load, function(data){
					var json = JSON.parse(jQuery.trim( data ));
	   				$('#CorsaSelezionataR').val(json['CorsaId']);
	   				$('#DataSelezionataR').val(json['VendibileAl']);
	   				$('input[name=\'Prenotazione[RitornoOpen]\']').val(1);
					MostraFermate();
   			});
        }
    }

    var caricamentoInCorso = false;
    var rimborsoInCorso = false;
    var annulla = false;
	var annullaViaggio = false;
    var modifica_base = false;
    var aggiorna_corsa = false;
	var conferma_prenotazione = false;
   $(document).ready(function() {

    	$('.select2').select2();
            
	   controllaBarcaLibera();
	   // Quando il valore di #Libera cambia
		$('#Libera').change(function() {
			// Controlla e aggiorna lo stato di #BarcaLibera
			controllaBarcaLibera();
		});
	   
		$('#ConfermaPrenotazioneStripe').click(function(){
			$('#tipoPagamento').val(22);
		});
		$('#ConfermaPrenotazionePaypal').click(function(){
			$('#tipoPagamento').val(1);
		});
		$('body').keypress(function(e){
			if(e.which == 13){
				return false;
			}
		});
		
		//controllo per penali su modifica
		AddCheckModifica();
		
	   
	   adatta_dialog_box();

	   $('#Annulla').click(function(){
			annulla = true;
	   });
	   $('#AnnullaViaggio').click(function(){
			annullaViaggio = true;
	   });
       $('#ModificaAnagrafica').click(function(){
	 		modifica_base = true;
	   });
       $('#AggiornaCorsa').click(function(){
	 		aggiorna_corsa = true;
	   });    
	   $('#ConfermaPrenotazione').click(function(){
			ConfermaPrenotazione($('#prenotazioneDaConfermare').val());
	 		//conferma_prenotazione = true;
	   });  

	   
                          
		$("#application_form").validate({
          	submitHandler: function(form) {
            	// $(form).ajaxSubmit();
            	var principale = $('input.passeggeroPrincipale:checked').val();

            	if( $('#RimborsoTragitto').lenght==0){
	            	if (principale !== undefined) {
	            		submit_form_biglietto();
	            	} else {
	                	alert("<?=$dizionario['biglietto']['val_seleziona_principale']?>");
	                }
            	}else{
					if(caricamentoInCorso == false){
						if(annulla){
							submit_form_biglietto();
							annulla = false;
						} else if(annullaViaggio){
							submit_form_biglietto();
							annullaViaggio = false;
						} else if(modifica_base){
							submit_form_biglietto();
							modifica_base = false;
						} else if(conferma_prenotazione){
							submit_form_biglietto();
							conferma_prenotazione = false;
						} else if(aggiorna_corsa) {
							submit_form_biglietto();
							aggiorna_corsa = false;
						} else {
							if($('#modPre').val() == 1){
	 							check_penale_modifica();
	 						} else {
	 							check_duplicato();
// 	 							submit_form_biglietto();
	 						}
						}
            			caricamentoInCorso = true;
            			$('#layer_nero2').css('display','block');
					}
            		
            	}
           	}
     	});

		$('#rimborsoSalva').click(function() {
			if(rimborsoInCorso == false){
				var importoRimborso = $('#RimborsoValoreRimborso').val();
				importoRimborso = parseFloat(importoRimborso.replace(",", "."));
				$('#RimborsoValoreRimborso').val($('#RimborsoValoreRimborso').val().replace(".", ","));
				var importoMassimo = $('#RimborsoImportoMassimo').val();
		        importoMassimo = parseFloat(importoMassimo.replace(",", "."));
		        
				if(!isNaN(parseFloat(importoRimborso)) && isFinite(importoRimborso) && importoRimborso <= importoMassimo && $("label[for='RimborsoValoreRimborso']").length<=0){
					var codiceTitolo = $('#CodiceTitolo').val();
					var importo = $('#RimborsoValoreRimborso').val();
					var tragitto = $('#RimborsoTragitto').val();
					if (tragitto == 0){
						tragitto = 'Andata e Ritorno';
					}
					$("body").append('<div id="dialog-confirm_rimborso" title="Attenzione!" style="display: none;">'+
				  		'<p>'+
							'<?=$dizionario['biglietto']['val_rimborsare1']?> ' + codiceTitolo + ' <?=$dizionario['biglietto']['val_rimborsare2']?> ' + tragitto + ' <?=$dizionario['biglietto']['val_rimborsare3']?> ' + importo + ' <?=$dizionario['biglietto']['val_rimborsare4']?>'+
						'</p>'+
					'</div>');
		
		        	$("#dialog-confirm_rimborso" ).dialog({
			           	closeOnEscape: false,
			           	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
			       		resizable: false,
		         	    modal: true,
			           	buttons: {
			           	    "Si": function() {
			           	    	rimborsoInCorso = true;
			           	    	$('#layer_nero2').css('display','block');
			           	    	$("#application_form").submit();
			           	    	
			           	    	$(this).dialog("destroy");
			           	        $("#dialog-confirm_rimborso").remove();
			           	     	rimborsoInCorso = false;
			           	 	},
			           	   	"No": function() {
			           			$(this).dialog("destroy");
			        			$("#dialog-confirm_rimborso").remove();
		            	    }
		            	}
		        	});
				} else {
					$("body").append('<div id="dialog-confirm_rimborso" title="Attenzione!" style="display: none;">'+
					  		'<p>'+
								'<?=$dizionario['biglietto']['val_importo_non_corretto']?>'+
							'</p>'+
						'</div>');
			
			        	$("#dialog-confirm_rimborso" ).dialog({
				           	closeOnEscape: false,
				           	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
				       		resizable: false,
			         	    modal: true,
				           	buttons: {
				           	   	"<?=$dizionario['biglietto']['val_continua']?>": function() {
				           			$(this).dialog("destroy");
				        			$("#dialog-confirm_rimborso").remove();
			            	    }
			            	}
			        	});
				}
			}
		});
		
		$("#EmettiTitoliDiViaggio").click(function() {
			$.ajax({
		    	type: "GET",
		        url: "/protected/modules/rt_biglietto/biglietto_action.php?do=EmettiTitoliDiViaggio",
		        dataType: 'json',
		        success: function(data) {
		        	loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=3',this);
		        },
		        error: function(){
					alert("<?=$dizionario['generale']['problemi_server']?>");
					caricamentoInCorso = false;
        			$('#layer_nero2').css('display','none');
		       }
			});
        });

        $('#RimborsoTragitto').change(function() {
            action = 'GetImportoMassimoRimborsabile';
            prenotazioneNumeroId = $('#PrenotazioneNumeroId').val();
            var codice = $('#CodiceTitolo').val();
            tragitto = $(this).val();
            
        	$.ajax({
		    	type: "POST",
		        url: "/protected/modules/rt_biglietto/biglietto_action.php",
		        data: {'action':action, 'PrenotazioneNumeroId':prenotazioneNumeroId, 'Tragitto':tragitto, 'Codice':codice},
		        dataType: 'json',
		        success: function(data) {
		        	$('#RimborsoImportoMassimo').val(data.importo);
		        	$("#RimborsoValoreRimborso").trigger("change");
		        	$('#penaleRimborso').html(data.penale);
		        }
			});
        });

        $('#RimborsoValoreRimborso').change(function() {
            importoMassimo = $('#RimborsoImportoMassimo').val();
            importoMassimo = parseFloat(importoMassimo.replace(",", "."));

            importo = $(this).val();
            importo = (importo != '')? importo : '0';
            importo = parseFloat(importo.replace(",", "."));

            if (importo > importoMassimo) {
            	$("label[for='RimborsoValoreRimborso']").remove();
            	$(this).parent().append('<label for="RimborsoValoreRimborso"><?=$dizionario['biglietto']['alert_rimborsabile']?></label>');
            } else {
            	$("label[for='RimborsoValoreRimborso']").remove();
            }

            residuo = importoMassimo - importo;
            residuo = residuo.toFixed(2).replace(".", ",");
            $('#RimborsoResiduo').val(residuo);
        });

        $('#FineMulti, #FineMulti1').click(function() {
            $("body").append('<div id="dialog-confirm" title="Attenzione!" style="display: none;">'+
  				'<p>'+
					'<?=$dizionario['biglietto']['val_fine_multi_tratta']?>'+
				'</p>'+
			'</div>');
				
           	$("#dialog-confirm" ).dialog({
               	closeOnEscape: false,
            	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
           		resizable: false,
            	modal: true,
            	buttons: {
	            	"Si": function() {
		            	var step = 2;
	            		//loadMediazioneStep('rt_biglietto','biglietto.php?&do=add&reset=1',this);
	            		loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&reset=1&PrenotazioneId='+prenotazioneId+'&CorsaId='+corsaId+'&step='+step, this);
	            	    $(this).dialog("destroy");
	            	    $("#dialog-confirm").remove();
	            	},
	           		"No": function() {
	            		$('#PrenotazioneMulti').val("1");
	            	    $(this).dialog("destroy");
	            	    $("#dialog-confirm").remove();
	            	}
            	}
            });
        });

        $('#CorsaRitornoAperto').change(function(e,t){
			if($(this).attr("checked") == true){
				MostraRitornoAperto();
			} else {
				$('#RicercaAId').parent().show();
				$('#ElencoR').show();
				$('#RicercaAId').addClass('required');
				$('input[name=\'Prenotazione[RitornoOpen]\']').val(0);
				$('#CorsaSelezionataR').val('');
   				$('#DataSelezionataR').val('');
			}
         });
        $('#PrenotazioneMulti').change(function(e,t){
        	if($('#PrenotazioneMulti').val() == 1){
        		$('#CorsaRitornoAperto').hide();
        		$('#label_CorsaRitornoAperto').hide();
        		$('#label_CorsaRitornoAperto').css("opacity","0");
        		$('#CorsaRitornoAperto').attr("checked", false);
        		
        		$('#RicercaAId').parent().show();
        		$('#RicercaAId').show();
        		$('.brain_campoForm').show();
 				$('#ElencoR').show();
 				$('#RicercaAId').addClass('required');
 				$('input[name=\'Prenotazione[RitornoOpen]\']').val(0);
        	} else {
        		$('#CorsaRitornoAperto').show();
        		$('#label_CorsaRitornoAperto').show();
        		$('#label_CorsaRitornoAperto').css("opacity","1");
        	}
        });

        if($('#CorsaRitornoAperto').attr("checked") == true){
        	$('#RicercaAId').parent().hide();
    		$('#ElencoR').hide();
    		$('#RicercaAId').removeClass('required');
        }
		
		$('#TipoTour').change(function(){
			if($('#TipoTour').val() == 0) {
				$('#TipoViaggioIdSelect').val("");
				$('#TipoViaggioId').val("");
				$("#TipoViaggioIdSelect").removeAttr("disabled");
				$('#Libera').val(0);
				$('.libera-block').hide();
			} else if($('#TipoTour').val() == 1) {
				$('#TipoViaggioIdSelect').val(1);
				$('#TipoViaggioId').val(1);
				$("#TipoViaggioIdSelect").attr("disabled", "disabled");
				$('#Libera').val(0);
				$('.libera-block').show();
			} else {
				$('#TipoViaggioIdSelect').val("");
				$('#TipoViaggioId').val("");
				$("#TipoViaggioIdSelect").attr("disabled", "disabled");
			}
			MostraElencoCorse();
		});
		
		$('#TipoViaggioIdSelect').change(function(){
			$('#TipoViaggioId').val($('#TipoViaggioIdSelect').val());
			MostraElencoCorse();
		});

		$("#OrarioPartenzaLibera, #OrarioArrivoLibera").focus( function() {
				$(this).data('original-val', $(this).val())
			})
			.blur(function() {
				if ($(this).data('original-val') != $(this).val()) {    
					MostraTipoBiglietti("A");
				}
			});
		
		// Verifica se esiste un'opzione con value=""
		if ($('#Libera option[value=""]').length > 0) {
			// Rimuovi l'opzione vuota
			$('#Libera option[value=""]').remove();
		}
		
	});

	function paypal() {
		 $('#form_paypal').submit(); 
	}
	
	function stripe(prenotazioneId) { 
		page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=stripe&prenotazioneId="+prenotazioneId;
       	$.get(page_to_load, function(data){
       		
       		//var stripe = Stripe('<?php echo Config::$StripePublicKey?>');
       		
// 			stripe.redirectToCheckout({ sessionId: data.trim() });
			window.open('/pagamento_stripe.php?session_id='+data.trim(), '_blank');
//            alert("La richiesta di reinvio è stata effettuata");
//            		avviso_operazione("ok");
//            		loadMainContent('rt_biglietto','biglietto.php?do=add&step=3',this);
           
              } );
	}
	
	function stripeLink(prenotazioneId) { 
		page_to_load="/protected/modules/rt_biglietto/biglietto_action.php?do=stripeLink&prenotazioneId="+prenotazioneId;
       	$.get(page_to_load, function(data){
			window.open('/pagamento_stripe_link.php?session_id='+data.trim(), '_blank');
		} );
	} 

	function stripeInvioLink(prenotazioneId) { 
		CorsaId = $("#CorsaSelezionataA").val();
		let confirmResult = confirm("<?=$dizionario['biglietto']['val_invia_email']?>");
		if (confirmResult) {  
			let page_to_load = "/protected/modules/rt_biglietto/biglietto_action.php?do=stripeSendLink&prenotazioneId=" + prenotazioneId;

			$.ajax({
				type: 'GET',
				url: page_to_load,
				success: function(data) {
					if (data.trim() === "ok") {
						alert("<?=$dizionario['biglietto']['val_email_ok']?>");
					} else {
						alert("<?=$dizionario['biglietto']['val_email_error']?>");
					}
					setTimeout(function() {
						avviso_operazione("ok");
						loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
						
					}, 2000);
				},
				error: function() {
					alert("Errore di comunicazione con il server. Riprova più tardi.");
					setTimeout(function() {
						avviso_operazione("ok");
						loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
						
					}, 2000);
				}
			});
		} else {
			setTimeout(function() {
				avviso_operazione("ok");
				loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
				
			}, 2000);
		}
	}

	function stripeInvioLinkAgenzia(prenotazioneId) { 
		CorsaId = $("#CorsaSelezionataA").val();
		let confirmResult = confirm("<?=$dizionario['biglietto']['val_invia_email_agenzia']?>");
		if (confirmResult) {  
			let page_to_load = "/protected/modules/rt_biglietto/biglietto_action.php?do=stripeSendLinkAgenzia&prenotazioneId=" + prenotazioneId;

			$.ajax({
				type: 'GET',
				url: page_to_load,
				success: function(data) {
					if (data.trim() === "ok") {
						alert("<?=$dizionario['biglietto']['val_email_ok']?>");
					} else {
						alert("<?=$dizionario['biglietto']['val_email_error']?>");
					}
					setTimeout(function() {
						avviso_operazione("ok");
						loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
						
					}, 2000);
				},
				error: function() {
					alert("Errore di comunicazione con il server. Riprova più tardi.");
					setTimeout(function() {
						avviso_operazione("ok");
						loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
						
					}, 2000);
				}
			});
		} else {
			setTimeout(function() {
				avviso_operazione("ok");
				loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
				
			}, 2000);
		}
	}

	function telecash() {
		 $('#form_telecash').submit(); 
	}
	
	function emettiRicevuta(movimentoId) {
		if (confirm("Sei sicuro di voler emettere lo scontrino elettronico?")) {  
			let page_to_load = "/protected/modules/rt_biglietto/biglietto_action.php";

			$.ajax({
				type: 'POST',
				url: page_to_load,
				data: {
					'action': 'FiscalGatewayEmettiRicevuta',
					'MovimentoId': movimentoId
				},
				success: function(data) {
					setTimeout(function() {
						loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
					}, 2000);
				}
			});
		}
	}

	function downloadRicevuta(movimentoId) {
        page_to_load="/protected/modules/rt_biglietto/biglietto_action.php";

        $.ajax({
            type: 'POST',
            url: page_to_load,
            data: {'action' : 'FiscalGatewayDownloadRicevuta',
					'MovimentoId' : movimentoId},
            success: function(data) {
            	try {
					let response = JSON.parse(data);

					// Controlla se lo status_code è 200 e se success è true
					if (response.status_code === 200 && response.response.success && response.response.result.url) {
						window.open(response.response.result.url, '_blank'); // Apre il link in una nuova finestra
					} else {
						console.error("Errore: URL non valido o risposta non corretta");
					}
				} catch (error) {
					console.error("Errore nel parsing della risposta", error);
				}
            }
        });
    }
	
	function inviaRicevuta(movimentoId) {
		if (confirm("Sei sicuro di voler inviare lo scontrino elettronico al cliente?")) {  
			let page_to_load = "/protected/modules/rt_biglietto/biglietto_action.php";

			$.ajax({
				type: 'POST',
				url: page_to_load,
				data: {
					'action': 'FiscalGatewayInviaRicevuta',
					'MovimentoId': movimentoId
				},
				success: function(data) {
					alert("Ricevuta inviata con successo");
					setTimeout(function() {
						loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
					}, 2000);

				}
			});
		}
	}
	
	function emettiNotaCredito(movimentoId) {
		if (confirm("Sei sicuro di voler emettere la nota di credito elettronica?")) {  
			let page_to_load = "/protected/modules/rt_biglietto/biglietto_action.php";

			$.ajax({
				type: 'POST',
				url: page_to_load,
				data: {
					'action': 'FiscalGatewayEmettiNotaCredito',
					'MovimentoId': movimentoId
				},
				success: function(data) {
					setTimeout(function() {
						loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
					}, 2000);
				}
			});
		}
	}

	function annullaRicevuta(movimentoId) {
    // Chiede conferma prima di procedere all'annullamento
    if (confirm("Sei sicuro di voler annullare questa ricevuta? L'operazione non può essere annullata.")) {
        page_to_load = "/protected/modules/rt_biglietto/biglietto_action.php";

        $.ajax({
            type: 'POST',
            url: page_to_load,
            data: {
                'action': 'FiscalGatewayAnnullaRicevuta',
				'post': true,
                'MovimentoId': movimentoId
            },
            success: function(data) {
                try {
                    let response = JSON.parse(data);
                    
                    // Controlla se l'annullamento è andato a buon fine
                    if (response.success === true) {
                        alert("Ricevuta annullata correttamente");
                        // Eventualmente ricarica la pagina o aggiorna i dati
                        setTimeout(function() {
							loadMediazioneStep('rt_biglietto', 'biglietto.php?CorsaId=' + CorsaId + '&do=add&step=2', this);
						}, 2000);
                    } else {
                        alert("Errore durante l'annullamento della ricevuta");
                    }
                } catch (error) {
                    console.error("Errore nel parsing della risposta", error);
                    alert("Errore durante l'annullamento della ricevuta");
                }
            },
            error: function() {
                alert("Errore di comunicazione con il server");
            }
        });
    }
}
	
</script>