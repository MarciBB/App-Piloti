$(document).ready(function()
{	
			 $("#form_search").validate(  
				{  
					rules:  
					{  
					'arrivalDate':{  
						required: true  
						
						},  
					'departureDate':{  
						required: true
						
						},  
					'numberOfAdults':{  
						required: true
						
						}
					},  
					messages:  
					{  
					'arrivalDate':{  
						required: "Questo campo e' obbligatorio"
												
						},  
					'departureDate':{  
						required: "Questo campo e' obbligatorio"
						
						}, 
					'numberOfAdults':{  
						required: "Seleziona la tipologia di camera!"
						
						}
					}  
				});

});
