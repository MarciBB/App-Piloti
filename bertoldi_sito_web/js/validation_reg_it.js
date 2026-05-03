$(document).ready(function()
{	
		$("#form_search").validate();
				
				$("#form_risultati_ricerca").validate(  
				{  
					ignore: "",
					
					errorPlacement: function(error, element) {
						if(element.attr("name") == "ItinerarioAndata") {
							error.appendTo( $('#error_1') );
						} 
						else if(element.attr("name") == "ItinerarioRitorno") {
							error.appendTo( $('#error_2') );
						} 
						else if(element.attr("name") == "dataPartenzaRitorno") {
							error.appendTo( $('#error_2') );
						} 
						else {
							error.insertAfter(element);
						}
					},
					rules:  
					{  
					'ItinerarioAndata':{  
						required: true			
						
						},  
					'ItinerarioRitorno':{  
						required: true,
						dataMaggiore: true
						} 
					},  
					messages:  
					{  
					'ItinerarioAndata':{  
						required: "Seleziona almeno una soluzione di andata"
						},  
					'ItinerarioRitorno':{  
						required: "Seleziona almeno una soluzione di ritorno"
						}
					}
				});
				
				jQuery.validator.addMethod("dataMaggiore", function(value, element) {
					return dataMaggiore($('input[name=ItinerarioAndata]:checked').attr('dataandata'), $('input[name=ItinerarioRitorno]:checked').attr('dataritorno'));
				}, "La data di ritorno deve essere successiva a quella di andata");
				var dataMaggiore = function(dataPartenzaAndata, dataPartenzaRitorno) {
					var inDate = new Date(dataPartenzaAndata),
						eDate = new Date(dataPartenzaRitorno);
                                     if($('#tipopercorso').val()==1){
					if(inDate >= eDate) {
						return false;
					}
					else {
						return true;	
					}
                                     }
                                     else {
                                         return true;
                                     }
		
				};
				
				$("#form_conferma_itinerario").validate(  
				{  
					errorPlacement: function(error, element) {
						if(element.attr("name") == "metodo_pagamento") {
							error.appendTo( $('#metodo_pagamento_val') );
						} 
						else if(element.attr("name") == "sesso") {
							error.appendTo( $('#sesso_val') );
						}
						else if(element.attr("name") == "privacy") {
							error.appendTo( $('#privacy_error') );
						}  
						else if(element.attr("name") == "posti_totale") {
							error.appendTo( $('#error_numero_posti') );
						}
						else {
							error.insertAfter(element);
						}
					},
					rules:  
					{  
					'posti_totale':{  
						required: true,					
						min: 1,
						number: true
						},
					'metodo_pagamento':{  
						required: true
						},
					'privacy':{  
						required: true
						}, 
					'email':{  
						required: true,
						email: true
						} ,
					'conferma_email':{  
						required: true,
						equalTo: "#email"
						} ,
					'prefisso_numero_personale': {
						required: true
						},
					'numero_personale':{  
						required: true,
						phone: true
						}
					},  
					
					messages:  
					{  
					'posti_totale':{  
						required: "Questo campo è obbligatorio",
						min: "Per prenotare questo viaggio bisogna inserire almeno un biglietto",
						number: "Questo campo deve essere di tipo numerico"
						},
					'metodo_pagamento':{  
						required: "Scegli un metodo di pagamento"
						} ,
					'privacy':{  
						required: "E' necessario accettare i termini e le condizioni per proseguire"
						} ,
					'email':{  
						required: "Questo campo è obbligatorio",
						email: "Inserisci un'email valida"
						} ,
					'conferma_email':{  
						required: "Questo campo è obbligatorio",
						equalTo: "L'email inserita non coincide"
						},
					'prefisso_numero_personale':{  
						required: "Questo campo è obbligatorio"
						},
					'numero_personale':{  
						required: "Questo campo è obbligatorio"
						}
					}
				});
				
				$.validator.addMethod('phone', function(value) {
				  return (
					value.match(/^((\+)?[1-9]{1,2})?([-\s\.])?(\(\d\)[-\s\.]?)?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}(\s*(ext|x)\s*\.?:?\s*([0-9]+))?$/)
				  );
				}, 'Per favore inserisci un numero valido');
				
                                $("#contatti_italia").validate(  
				{  
					rules:  
					{  
					'nome':{  
						required:true					
						},  
					'cognome':{  
						required: true
						
						},  
					'email':{  
						required: true,
                                                email: true
					},
					'richiesta':{  
						required: true				
						},
					'privacy':{  
						required: true				
						},
                                        'recaptcha_response_field':{  
						required: true				
						}                                        
					},  
					messages:  
					{  
					'nome':{  
						required: ""
												
						},  
					'cognome':{  
						required: ""
						
						}, 
					'email':{  
						required: "",
                                                email: ""
						} ,
					'richiesta':{  
						required: ""						
						},
					'privacy':{  
						required: ""						
						},
                                         'recaptcha_response_field':{  
						required: ""				
						}   
					}  
				});
                                $("#contatti_germania").validate(  
				{  
					rules:  
					{  
					'nome':{  
						required:true					
						},  
					'cognome':{  
						required: true
						
						},  
					'email':{  
						required: true,
                                                email: true
					},
					'richiesta':{  
						required: true				
						},
					'privacy':{  
						required: true				
						}
					},  
					messages:  
					{  
					'nome':{  
						required: ""
												
						},  
					'cognome':{  
						required: ""
						
						}, 
					'email':{  
						required: "",
                                                email: ""
						} ,
					'richiesta':{  
						required: ""						
						},
					'privacy':{  
						required: ""						
						} 
					}  
				});
				
				$.validator.addMethod('fattura_ragionesociale', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_partita_iva', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_codice_fiscale', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_indirizzo', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_cap', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_provincia', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_nazione', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_comune', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_emailpec', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_email', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');
				$.validator.addMethod('fattura_tel', function(value) {
					if($('#fattura').val() == 1) {
						if(value != '') {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}, 'Questo campo è obbligatorio');

});
