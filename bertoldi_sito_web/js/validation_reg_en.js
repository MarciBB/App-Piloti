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
						required: "This field is required!"
												
						},  
					'departureDate':{  
						required: "This field is required!"
						
						}, 
					'numberOfAdults':{  
						required: "Please select the room type!"
						
						}
					}  
				});

});
