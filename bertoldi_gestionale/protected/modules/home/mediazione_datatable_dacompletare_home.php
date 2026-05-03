<style type="text/css" title="currentStyle">
	        @import "/DataTables/media/css/demo_page.css";
		@import "/DataTables/media/css/demo_table.css";
                </style>
                
                <script type="text/javascript" language="javascript" src="/js/jquery.dataTables.js"></script>
                
		<!--<script type="text/javascript" language="javascript" src="/DataTables/media/js/jquery.js"></script>-->
		<!--<script type="text/javascript" language="javascript" src="/DataTables/media/js/jquery.dataTables.js"></script>-->
	        <script type="text/javascript" charset="utf-8">
                    
                    var oTable;
                    var asInitVals;
                    
                    (function($) {
			/*
			 * Function: fnGetColumnData
			 * Purpose:  Return an array of table values from a particular column.
			 * Returns:  array string: 1d data array 
			 * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
			 *           int:iColumn - the id of the column to extract the data from
			 *           bool:bUnique - optional - if set to false duplicated values are not filtered out
			 *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
			 *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
			 * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
			 */
			$.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
				// check that we have a column id
				if ( typeof iColumn == "undefined" ) return new Array();
				
				// by default we only wany unique data
				if ( typeof bUnique == "undefined" ) bUnique = true;
				
				// by default we do want to only look at filtered data
				if ( typeof bFiltered == "undefined" ) bFiltered = true;
				
				// by default we do not wany to include empty values
				if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;
				
				// list of rows which we're going to loop through
				var aiRows;
				
				// use only filtered rows
				if (bFiltered == true) aiRows = oSettings.aiDisplay; 
				// use all rows
				else aiRows = oSettings.aiDisplayMaster; // all row numbers
			
				// set up data array	
				var asResultData = new Array();
				
				for (var i=0,c=aiRows.length; i<c; i++) {
					iRow = aiRows[i];
					var aData = this.fnGetData(iRow);
					var sValue = aData[iColumn];
					
					if (sValue)
                                        {
					
					// ignore empty values?
					if (bIgnoreEmpty == true && sValue.length == 0) continue;
			
					// ignore unique values?
					else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;
					
					// else push the value onto the result data array
					else asResultData.push(sValue);
                                        }
				}
				
				return asResultData;
			}}(jQuery));
			
			
			function fnCreateSelect( aData )
			{
				var r='<select><option value="">Tutti</option>', i, iLen=aData.length;
                               
				for ( i=0 ; i<iLen ; i++ )
				{
                                    valore_select1=aData[i];
                                    var valore_select = valore_select1.replace(/(<.*?>)/ig,"");
					r += '<option value="'+valore_select+'">'+aData[i]+'</option>';
				}
				return r+'</select>';
			}
                    
                    
                   
                    
                    function fnFormatDetails ( nTr )
			{
                               
                           
				var aData = oTable.fnGetData( nTr );
                                if (aData)
                                    {
				var sOut = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
				sOut += '<tr><td>Rendering engine:</td><td>'+aData[2]+'</td></tr>';
				sOut += '<tr><td>Link to source:</td><td>Could provide a link here</td></tr>';
				sOut += '<tr><td>Extra info:</td><td>And any further details here (images etc)</td></tr>';
				sOut += '</table>';
                                }
                                
                             
				
				return sOut;
			}
                    
                    
			$(document).ready(function() {
                            
                          
                            
                            /* Init the table */
                            oTable = $("#brain_datatables_dacompletare").dataTable({
                                    "bProcessing": true,
                                    "bServerSide": true,
                                    "sAjaxSource": "/protected/modules/home/mediazione_processing_dacompletare_home.php",
                                     "bInfo": false,
                                    "bPaginate": false,
                                    
                                
                                    "aoColumns": [
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    { "sClass": "center", "bSortable": false }
                        ],
                        "bAutoWidth":false,
                        "oLanguage": {
                        "sProcessing":"Caricamento dati in corso...",
			"sLengthMenu": "Visualizza _MENU_ risultati per pagina",
			"sZeroRecords": "Nessun risultato trovato",
			//"sInfo": "risultati totali: _TOTAL_",
                        "sInfo": "da _START_ a _END_ di _TOTAL_ risultati trovati",
			"sInfoEmpty": "0 di 0 risulati trovati",
                        "sInfoFiltered": ""
			//"sInfoFiltered": "(risultati totali: _MAX_)"
		},
                        
                        
                        
                        "aaSorting": [],
                            "fnInitComplete": function () {
                                        
                                        
                                         $("thead th span").each( function (i) {
                                             
                                            this.innerHTML = fnCreateSelect( oTable.fnGetColumnData(i) );
                                            $('select', this).change( function () {
                                                    oTable.fnFilter( $(this).val(), i);
                                            } );
                                    } );     
                                    
                                    
                                    /* var bottone_aggiungi='<div class="brain_servicebar"><a class="brain_aggiungi" href="javascript:void(0);" onclick="loadMainContent(\'mediazione\',\'mediazione.php?do=add\',this);" title="Crea Mediazione">aggiungi mediazione</a></div> ';                          
                                     $("#brain_datatables_length").after(bottone_aggiungi);*/
                                    
						$('tbody tr').each(function(){
								$(this).find('td:eq(0)').attr('nowrap', 'nowrap');
						});					 
		
				}
				    
				    
                } );
                
               
                 $('#brain_datatables_dacompletare tbody td a img.brain_collapse').live( 'click', function () {
					var nTr = this.parentNode.parentNode.parentNode;
                                       
					if ( this.src.match('close_details') )
					{
						/* This row is already open - close it */
						this.src = "/images/open_details.png";
						oTable.fnClose( nTr );
					}
					else
					{
						/* Open this row */
						this.src = "/images/close_details.png";
						oTable.fnOpen( nTr, fnFormatDetails(nTr), 'details' );
					}
				} );
                            
                	
      
   
                            
                            
				
                                
        $("thead input").keyup( function () {
		/* Filter on the column (the index) of this element */
		oTable.fnFilter( this.value, $("thead input").index(this) );
	} );
	
	
	
	/*
	 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
	 * the footer
	 */
	$("thead input").each( function (i) {
		if (typeof asInitVals !== 'undefined') {
			asInitVals[i] = this.value;
		}
	} );
	
	$("thead input").focus( function () {
		if ( this.className == "search_init" )
		{
			this.className = "";
			this.value = "";
		}
	} );
	
	$("thead input").blur( function (i) {
		if ( this.value == "" )
		{
			this.className = "search_init";
			this.value = asInitVals[$("thead input").index(this)];
		}
	} );
        
   


                
            
        
    
} );
		</script>
