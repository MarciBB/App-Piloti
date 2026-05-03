<?php global $dizionario;?>
<script type="text/javascript">

	var tipoPagamentoAgenzia = 6; 		//Agenzia
	var tipoPagamentoContanti = 1; 		//Contanti
	var tipoPagamentoABordo = 7;		//A bordo
	var action = '<?php echo $action; ?>';
	var pagato = '<?php echo $prenotazione['Pagato']; ?>';
	var importoTotale = 0;

	var loadingSubmit = false;
	$(document).ready(function() {
		importoTotale = $('#Importo').val();
		adatta_dialog_box();

		$('#ImportoPagato').change(function(){
			var importo = $('#Importo').val();
			var importoPagato = $('#ImportoPagato').val();
			if(importoPagato > importo) {
				$('#ImportoPagato').val($('#Importo').val());
			}

		});

		$("#application_form #Salva").click(function(){
			$("#application_form #Salva").hide();
		});
		
		$("#application_form").validate({
			submitHandler: function(form) {
				if(loadingSubmit == false){
					submit_form_movimento();
					loadingSubmit = true;
				}
				return false;
			},
			invalidHandler: function(form){
				$("#application_form #Salva").show();
			},
			
		});

		$('#Importo').blur(function(){
			//calcoloSupplemento();
		});
		$('#CodiceCoupon').parent().hide();
		$('#ControllaCoupon').hide();

		$('#ControllaCoupon').click(function() {
			controllaCodiceCoupon();
		});
		
		$('#TipoPagamento').change(function() {
			var tipo = $('#TipoPagamento').val();
			
			if (tipo == tipoPagamentoContanti) {
				$('#CodicePagamento').parent().hide();
				$('#CanalePagamento').parent().hide();
			} else {
				$('#CodicePagamento').parent().show();
				$('#CanalePagamento').parent().show();
			}
			checkCoupon();

			if(tipo == tipoPagamentoABordo){
				$('#spanSaldoReset').hide();
			}else{
				$('#spanSaldoReset').show();
			}

			if ( pagato == 0) {
				calcoloSupplemento();
				calcoloDataScadenza();
				

				if (tipo == tipoPagamentoContanti) {
					var importo = $('#Importo').val();
					$('#ImportoPagato').val(importo);

					var today = new Date();
					var dd = today.getDate();
					var mm = today.getMonth()+1;
					var yyyy = today.getFullYear();
					if(dd<10){dd='0'+dd;} 
					if(mm<10){mm='0'+mm;}
					dataPagamento = dd+'/'+mm+'/'+yyyy;
					$('#DataPagamento').val(dataPagamento);
				} else {
					$('#DataPagamento').val("");
				}
				
				if(tipo == tipoPagamentoABordo){
					$('#spanSaldoReset').hide();
				}else{
					if (tipo == tipoPagamentoAgenzia) {
						var importo = $('#Importo').val();
						$('#ImportoPagato').val(importo);
						$('#spanSaldoReset').hide();
					} else {
						$('#spanSaldoReset').show();
					}
				}
			}
		});

		$('#DataMovimento').blur(function() {
			if (action == 'add' && pagato == 0) {
				calcoloDataScadenza();
			}
		});

		$('#OraMovimento').blur(function() {
			if (action == 'add' && pagato == 0) {
				calcoloDataScadenza();
			}
		});

		$('#Saldo').click(function() {
			var saldo = $('#ImportoPagato').val();
			saldo = (saldo != '')? saldo : "0";
			saldo = parseFloat(saldo.replace(',','.'));
			
			if (saldo > 0) {
				saldo = 0;
				testo = "<?=$dizionario['generale']['saldo']?>";
				dataPagamento = '';
			} else {
				var importo = $('#Importo').val();
				importo = (importo != '')? importo : "0";
				importo = parseFloat(importo.replace(',','.'));
				
				var supplemento = $('#Supplemento').val();
				supplemento = (supplemento != '')? supplemento : "0";
				supplemento = parseFloat(supplemento.replace(',','.'));

				var today = new Date();
				var dd = today.getDate();
				var mm = today.getMonth()+1;
				var yyyy = today.getFullYear();
				if(dd<10){dd='0'+dd;} 
				if(mm<10){mm='0'+mm;}
				
				saldo = importo + supplemento;
				testo = "<?=$dizionario['generale']['reset']?>";
				dataPagamento = dd+'/'+mm+'/'+yyyy;
			}

			$('#Saldo').text(testo);
			$('#ImportoPagato').val(saldo.toFixed(2).replace('.',','));
			$('#DataPagamento').val(dataPagamento);
			
			return false;
		});

		$('#DataScadenza').change(function () {
			var data = $(this).val();
			checkScadenza(data);
		});

		var tipo = $('#TipoPagamento').val();
		if (tipo == tipoPagamentoContanti) {
			$('#CodicePagamento').parent().hide();
		} else {
			$('#CodicePagamento').parent().show();
		}
		if(tipo == tipoPagamentoABordo){
			$('#spanSaldoReset').hide();
		}else{
			$('#spanSaldoReset').show();
		}
	});

	function checkScadenza(value) {
		var dataCorsaText = '<?php echo $dt->format($percorso['CorsaDataPartenza'], "Y-m-d", "d/m/Y"); ?>';
		dataSplit = dataCorsaText.split("/");
		dataCorsa = new Date(dataSplit[2], dataSplit[1], dataSplit[0]);
		
		var dataText = value;
		dataSplit = dataText.split("/");
		data = new Date(dataSplit[2], dataSplit[1], dataSplit[0]);

		var diff = data.getTime() - dataCorsa.getTime();
		
		if (diff > 0) {
			$("body").append('<div id="dialog-confirm_scadenza" title="Attenzione!" style="display: none;">'+
	  			'<p>'+
					'<?php echo $dizionario['movimento']['check_scadenza1']." (".$dt->format($percorso['CorsaDataPartenza'], "Y-m-d", "d/m/Y").")."; ?>'+
					'<br>'+
					'<?=$dizionario['movimento']['check_scadenza2']?>'+
					'<br>'+
					'<?=$dizionario['movimento']['check_scadenza3']?> (<?php echo $dt->format($percorso['CorsaDataPartenza'], "Y-m-d", "d/m/Y"); ?>)'+
				'</p>'+
			'</div>');

			$("#dialog-confirm_scadenza" ).dialog({
            	closeOnEscape: false,
            	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
           		resizable: false,
           	    modal: true,
           	    buttons: {
           	    	"Si": function() {
           	    		$(this).dialog("destroy");
           	          	$("#dialog-confirm_scadenza").remove();
           	        },
           	        "No": function() {
           	        	$('#DataScadenza').val('<?php echo $dt->format($percorso['CorsaDataPartenza'], "Y-m-d", "d/m/Y"); ?>');
               	        
           	          	$(this).dialog("destroy");
           	          	$("#dialog-confirm_scadenza").remove();
        	        }
        	    }
			});
		}
	}
	
	function submit_form_movimento() {
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_biglietto/prenotazione_movimento_action.php",
	      	data: $("#application_form").serialize(),
	      	dataType: 'json',
	      	async: false,
	    	success: function(data) {
		    			    	
		    	if(data.result) {
			    	ChiudiBox();

			    	avviso_operazione("ok");
			    	
			    	if(data.emetti) {
           	    		$.ajax({
			    	   		type: "POST",
			    	       	url: "/protected/modules/rt_biglietto/biglietto_action.php?do=EmettiTitoliDiViaggio&movimentoId="+data.movimentoId+"&coupon="+data.coupon,
			    	    	success: function() {
								setTimeout(function() {
									loadMediazioneStep('rt_biglietto', 'biglietto.php?do=edit&PrenotazioneId=' + data.prenotazioneId + '&CorsaId=' + data.corsaId + '&step=3', this);
								}, 2000);
			    	    	}
						});
			    	} if(data.emettiExtra){
				    	var importoE = $('#ImportoPagato').val();
			    		$.ajax({
			    	   		type: "POST",
			    	   		
			    	       	url: "/protected/modules/rt_biglietto/biglietto_action.php?do=EmettiTitoliExtra&movimentoId="+data.movimentoId+"&coupon="+data.coupon,
			    	    	success: function() {
								setTimeout(function() {
			    	    			loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=3',this);
								}, 2000);
							}
						});
				    }else {
			    		loadingSubmit = false;
			    		if(data.sendEmail && data.sendSMS) {
							//invio email e sms
			    			//invio sms
							$("body").append('<div id="dialog-confirm_sms" title="Attenzione!" style="display: none;">'+
						  			'<p>'+
										'<?=$dizionario['movimento']['invio_sms']?>'+
									'</p>'+
								'</div>');

		            		$("#dialog-confirm_sms" ).dialog({
				            	closeOnEscape: false,
				            	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
				           		resizable: false,
				           	    modal: true,
				           	    buttons: {
				           	    	"Si": function() {
				           	    		$.ajax({
									   		type: "POST",
									       	url: "/protected/modules/rt_send_comunicazioni/send_sms.php",
									      	data: {'pagamentoId':data.tipoPagamento},
									      	dataType: 'text',
									    	success: function(data1) {
									    		//invio email
												$("body").append('<div id="dialog-confirm_email" title="Attenzione!" style="display: none;">'+
											  			'<p>'+
															'<?=$dizionario['movimento']['invio_email']?>'+
														'</p>'+
													'</div>');

								            		$("#dialog-confirm_email" ).dialog({
										            	closeOnEscape: false,
										            	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
										           		resizable: false,
										           	    modal: true,
										           	    buttons: {
										           	    	"Si": function() {
										           	    		$.ajax({
															   		type: "POST",
															       	url: "/protected/modules/rt_send_comunicazioni/send_email.php",
															      	data: {'pagamentoId':data.tipoPagamento},
															      	dataType: 'text',
															    	success: function(data1) {
																		setTimeout(function() {
																    		loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
																		}, 2000);
																	}
															  	});
										           	    		$(this).dialog("destroy");
										           	          	$("#dialog-confirm_email").remove();
										           	        },
										           	        "No": function() {
										           	          	$(this).dialog("destroy");
										           	          	$("#dialog-confirm_email").remove();
																setTimeout(function() {
																	loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
																}, 2000); 	
									            	        }
									            	    }
									            	});
									    	}
				           	    		});
				           	    		$(this).dialog("destroy");
				           	          	$("#dialog-confirm_sms").remove();
				           	        },
				           	        "No": function() {
				           	          	$(this).dialog("destroy");
				           	          	$("#dialog-confirm_sms").remove();
				           	       //invio email
										$("body").append('<div id="dialog-confirm_email" title="Attenzione!" style="display: none;">'+
									  			'<p>'+
													'<?=$dizionario['movimento']['invio_email']?>'+
												'</p>'+
											'</div>');

						            		$("#dialog-confirm_email" ).dialog({
								            	closeOnEscape: false,
								            	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
								           		resizable: false,
								           	    modal: true,
								           	    buttons: {
								           	    	"Si": function() {
								           	    		$.ajax({
													   		type: "POST",
													       	url: "/protected/modules/rt_send_comunicazioni/send_email.php",
													      	data: {'pagamentoId':data.tipoPagamento},
													      	dataType: 'text',
													    	success: function(data1) {
																setTimeout(function() {
														    		loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
																}, 2000);
															}
													  	});
								           	    		$(this).dialog("destroy");
								           	          	$("#dialog-confirm_email").remove();
								           	        },
								           	        "No": function() {
								           	          	$(this).dialog("destroy");
								           	          	$("#dialog-confirm_email").remove();
														setTimeout(function() {
									           	        	loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
														}, 2000); 	 	
							            	        }
							            	    }
							            	});
					           	          	
			            	        }
			            	    }
			            	});
							

				            	
						} else if(data.sendEmail && !data.sendSMS){
							//invio email
							$("body").append('<div id="dialog-confirm_email" title="Attenzione!" style="display: none;">'+
						  			'<p>'+
										'<?=$dizionario['movimento']['invio_email']?>'+
									'</p>'+
								'</div>');

			            		$("#dialog-confirm_email" ).dialog({
					            	closeOnEscape: false,
					            	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
					           		resizable: false,
					           	    modal: true,
					           	    buttons: {
					           	    	"Si": function() {
					           	    		$.ajax({
										   		type: "POST",
										       	url: "/protected/modules/rt_send_comunicazioni/send_email.php",
										      	data: {'pagamentoId':data.tipoPagamento},
										      	dataType: 'text',
										    	success: function(data1) {
													setTimeout(function() {
											    		loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
													}, 2000);
												}
										  	});
					           	    		$(this).dialog("destroy");
					           	          	$("#dialog-confirm_email").remove();
					           	        },
					           	        "No": function() {
					           	          	$(this).dialog("destroy");
					           	          	$("#dialog-confirm_email").remove();
											setTimeout(function() {
						           	         	loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
											}, 2000);  	
				            	        }
				            	    }
				            	});
						} else if(!data.sendEmail && data.sendSMS){
							//invio sms
							$("body").append('<div id="dialog-confirm_sms" title="Attenzione!" style="display: none;">'+
						  			'<p>'+
										'<?=$dizionario['movimento']['invio_sms']?>'+
									'</p>'+
								'</div>');

		            		$("#dialog-confirm_sms" ).dialog({
				            	closeOnEscape: false,
				            	open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
				           		resizable: false,
				           	    modal: true,
				           	    buttons: {
				           	    	"Si": function() {
				           	    		$.ajax({
									   		type: "POST",
									       	url: "/protected/modules/rt_send_comunicazioni/send_sms.php",
									      	data: {'pagamentoId':data.tipoPagamento},
									      	dataType: 'text',
									    	success: function(data1) {
												setTimeout(function() {
										    		loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
												}, 2000);
											}
									  	});
				           	    		$(this).dialog("destroy");
				           	          	$("#dialog-confirm_sms").remove();
				           	        },
				           	        "No": function() {
				           	          	$(this).dialog("destroy");
				           	          	$("#dialog-confirm_sms").remove();
										setTimeout(function() {
					           	        	loadMediazioneStep('rt_biglietto','biglietto.php?do=edit&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
										}, 2000);  	
			            	        }
			            	    }
			            	});
							
						} else {
							//nessun invio di messaggi
							setTimeout(function() {
					    		loadMediazioneStep('rt_biglietto','biglietto.php?do=add&PrenotazioneId='+data.prenotazioneId+'&CorsaId='+data.corsaId+'&step=2',this);
							}, 2000);
						}
			    	}
		    	} else {
		    		avviso_operazione("no");
		    		loadingSubmit = false;
		    	}
	    	}
	  	});
	}

	function avviso_operazione(tipoavv) {
        $("#loading_big_" + tipoavv).fadeIn(2000,function() {
        	$("#loading_big_" + tipoavv).fadeOut(2000,function() { });
        });
    }

	function calcoloSupplemento(){
		var importo = $('#Importo').val();
		var tipoPagamento = $('#TipoPagamento').val();
		if(importo != '' && tipoPagamento != ''){
			$.ajax({
		   		type: "POST",
		       	url: "/protected/modules/rt_biglietto/prenotazione_movimento_action.php",
		      	data: {'action':'calcoloSupplemento','importo':importo,'tipoPagamento':tipoPagamento},
		      	dataType: 'json',
		    	success: function(data) {
			    	if(data.result) {
			    		 $('#Supplemento').val(data.supplemento);
			    	} else {
			    		$('#Supplemento').val(importo);
			    	}

			    	$('#Saldo').text("saldo");

			    	if (tipoPagamento != tipoPagamentoAgenzia && tipoPagamento != tipoPagamentoContanti) {
			    		$('#ImportoPagato').val((0).toFixed(2).replace('.',','));
			    	}
		    	}
		  	});
	     } else {
	    	 $('#Supplemento').val('');
	     }
	 }

	 function controllaCodiceCoupon(){
		var codice =  $('#CodiceCoupon').val();
		var prenotazioneId = $("input[name='Movimento[PrenotazioneId]']").val();
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_biglietto/prenotazione_movimento_action.php",
	      	data: {'action':'controllaCodiceCoupon','codiceCoupon':codice, 'prenotazioneId':prenotazioneId},
	      	dataType: 'json',
	    	success: function(data) {
				if(data.result == true){
					var importo;
					if($.type(data.importo) === "string"){
		    			importo = parseFloat(data.importo.replace(',','.'));
		    		} else {
		    			importo = parseFloat(data.importo);
		    		}
					importo=importo.toString();
					var percentuale = data.percentuale;
					if(importo <= 0){
						var temp = parseFloat($('#Importo').val().replace(',','.'));	
						importo = temp*parseFloat(percentuale)/100;
						importo=importo.toString();
					}
					importo = importo.replace('.',',');					
					var pagImporto = $('#Importo').val();
					var finalImporto = 0;
					if(parseFloat(pagImporto) <= parseFloat(importo)){
						finalImporto = pagImporto;
					} else {
						finalImporto = importo;
					}
						
					$('#ControllaCoupon').hide();
		    		$('#Salva').show();
		    		$('#DataPagamento').parent().show();
		    		$('#ImportoPagato').parent().show();
		    		$('#ImportoPagato').val(finalImporto);
		    		$('#Importo').val(finalImporto);		
		    		$('#CodiceCoupon').attr('readonly','true');
		    		$('#spanSaldoReset').hide();
		    		var today = new Date();
					var dd = today.getDate();
					var mm = today.getMonth()+1;
					var yyyy = today.getFullYear();
					if(dd<10){dd='0'+dd;} 
					if(mm<10){mm='0'+mm;}
					dataPagamento = dd+'/'+mm+'/'+yyyy;
					$('#DataPagamento').val(dataPagamento);
					if(data.andata == 'OK' && (data.ritorno == 'OK' || data.ritorno == 'NO')) {
						$('#risultatoControlloCodice').html("");
			    		$('#risultatoControlloCodice').hide();
					} else {
						var html = "";
						if(data.andata == 'OK') {
							html = "Valido per viaggio di andata";
						} else {
							html = "Valido per viaggio di ritorno";
						}
						$('#risultatoControlloCodice').html(html);
					}
					
				} else {
					var html = data.message;
					if(data.andata != 'OK') {
						html += "<br> - Andata: "+data.andata;
					}
					if(data.ritorno != 'OK' && data.ritorno != 'NO') {
						html += "<br> - Ritorno: "+data.ritorno;
					}
					$('#risultatoControlloCodice').html(html);
				}
		    },
		});
	}

	function checkCoupon(){
		var tipoPagamento = $('#TipoPagamento').val();
		$.ajax({
	   		type: "POST",
	       	url: "/protected/modules/rt_biglietto/prenotazione_movimento_action.php",
	      	data: {'action':'checkCoupon','tipoPagamento':tipoPagamento},
	      	dataType: 'json',
	    	success: function(data) {
	    		var tipoPagamento = $('#TipoPagamento').val();
		    	if(data.result == true){
		    		$('#CodiceCoupon').parent().show();
		    		$('#CodicePagamento').parent().hide();
		    		$('#CanalePagamento').parent().hide();
		    		$('#ControllaCoupon').show();
		    		$('#Salva').hide();
		    		$('#DataPagamento').parent().hide();
		    		$('#ImportoPagato').parent().hide();
		    		$('#risultatoControlloCodice').show();
			    } else {
			    	$('#Importo').val(importoTotale);
			    	$('#Supplemento').val("0,00");
			    	if(tipoPagamento != tipoPagamentoAgenzia){	
			    		$('#ImportoPagato').val('0,00');
			    	} else {
			    		$('#ImportoPagato').val(importoTotale);
			    		$('#DataPagamento').val($('#DataMovimento').val());		    		
				    }	
			    	$('#CodiceCoupon').parent().hide();
			    	if (tipoPagamento != tipoPagamentoContanti) {
				    	$('#CodicePagamento').parent().show();
			    		$('#CanalePagamento').parent().show();
			    	}
			    	$('#ControllaCoupon').hide();
		    		$('#Salva').show();
		    		$('#DataPagamento').parent().show();
		    		$('#ImportoPagato').parent().show();
		    		$('#risultatoControlloCodice').hide();
			    }
	    	}
		});
	}

	 function calcoloDataScadenza(){
		 var dataMovimento = $('#DataMovimento').val();
		 var oraMovimento = $('#OraMovimento').val();
		 var tipoPagamento = $('#TipoPagamento').val();
		 if(dataMovimento != '' && oraMovimento != '' && tipoPagamento != ''){
			$.ajax({
		   		type: "POST",
		       	url: "/protected/modules/rt_biglietto/prenotazione_movimento_action.php",
		      	data: {'action':'calcoloDataScadenza', 'dataMovimento':dataMovimento, 'oraMovimento':oraMovimento, 'tipoPagamento':tipoPagamento},
		      	dataType: 'json',
		    	success: function(data) {
			    	if(data.result) {
			    		 $('#DataScadenza').val(data.dataScadenza);
			    		 $('#OraScadenza').val(data.oraScadenza);

			    		 checkScadenza(data.dataScadenza);
			    	} else {
			    		 $('#DataScadenza').val('');
			    		 $('#OraScadenza').val('');
			    	}
		    	}
		  	});
	     } else {
	    	 $('#DataScadenza').val('');
    		 $('#OraScadenza').val('');
	     }
	 }
</script>