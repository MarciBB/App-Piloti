<?php global $dizionario, $db;
	$sql = "SELECT FornitoreId as Id, Nome as Value FROM RT_Fornitori order by Nome asc";
	$fornitori = $db->fetch_array($sql);
	
	$sql = "SELECT FornitoriCategoriaId as Id, Nome as Value FROM RT_FornitoriCategorie order by Nome asc";
	$categorie = $db->fetch_array($sql);
	
	$sql = "SELECT SpeseDestinazioneId as Id, Nome as Value FROM RT_SpeseDestinazione order by Nome asc";
	$destinazioni = $db->fetch_array($sql);
	
	$sql = "SELECT SpesePagamentoId as Id, Nome as Value FROM RT_SpesePagamento order by Nome asc";
	$pagamenti = $db->fetch_array($sql);

?>
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
				
                    var r='<select><option value=""><?=$dizionario['generale']['tutti']?></option>', i, iLen=aData.length;

                    for ( i=0 ; i<iLen ; i++ )
                    {
                        valore_select1 = aData[i];
                        var valore_select = valore_select1.replace(/(<.*?>)/ig,"");
                            r += '<option value="'+valore_select+'">'+aData[i]+'</option>';
                    }
                    r = r+'</select>';
					return r;
            }
			
			function createSelectFromFornitori(fornitori) {
				// Inizializza la select con l'opzione "Tutti".
				let selectHtml = '<select style="width:150px;"><option value=""><?=$dizionario['generale']['tutti']?></option>';

				// Itera sull'array dei fornitori e crea le opzioni della select.
				fornitori.forEach(fornitore => {
					const { Id, Value } = fornitore; // Assumi che ogni fornitore sia un oggetto con "Id" e "Value".
					selectHtml += `<option value="${Id}">${Value}</option>`;
				});

				selectHtml += '</select>';

				return selectHtml;
			}
			
			function createSelectFromPagamenti(pagamenti) {
				// Inizializza la select con l'opzione "Tutti".
				let selectHtml = '<select style="width:150px;"><option value=""><?=$dizionario['generale']['tutti']?></option>';

				// Itera sull'array dei fornitori e crea le opzioni della select.
				pagamenti.forEach(pagamento => {
					const { Id, Value } = pagamento; // Assumi che ogni fornitore sia un oggetto con "Id" e "Value".
					selectHtml += `<option value="${Id}">${Value}</option>`;
				});

				selectHtml += '</select>';

				return selectHtml;
			}
			
			function createSelectFromDestinazioni(destinazioni) {
				// Inizializza la select con l'opzione "Tutti".
				let selectHtml = '<select style="width:150px;"><option value=""><?=$dizionario['generale']['tutti']?></option>';

				// Itera sull'array dei fornitori e crea le opzioni della select.
				destinazioni.forEach(destinazione => {
					const { Id, Value } = destinazione; // Assumi che ogni fornitore sia un oggetto con "Id" e "Value".
					selectHtml += `<option value="${Id}">${Value}</option>`;
				});

				selectHtml += '</select>';

				return selectHtml;
			}
			
			function createSelectFromCategorie(categorie) {
				// Inizializza la select con l'opzione "Tutti".
				let selectHtml = '<select><option value=""><?=$dizionario['generale']['tutti']?></option>';

				// Itera sull'array dei fornitori e crea le opzioni della select.
				categorie.forEach(categoria => {
					const { Id, Value } = categoria; // Assumi che ogni fornitore sia un oggetto con "Id" e "Value".
					selectHtml += `<option value="${Id}">${Value}</option>`;
				});

				selectHtml += '</select>';

				return selectHtml;
			}

			
			function fnCreateSelectPagato( aData )
            {
				
                    var r = '<select><option value=""><?=$dizionario['generale']['tutti']?></option>';

                    
                    r += '<option value="0"><?=$dizionario['spese']['non_pagato']?></option>';
					r += '<option value="1"><?=$dizionario['spese']['pagato']?></option>';
                    
                    r += '</select>';
					return r;
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
                            oTable = $("#brain_datatables").dataTable({
                                    "bProcessing": true,
                                    "bServerSide": true,
                                    "sAjaxSource": "/protected/modules/rt_spese/spese_processing.php",   
									"fnServerData": function ( sSource, aoData, fnCallback ) {
												$.getJSON( sSource, aoData, function (json) { 
													if (json.TotalCosto !== undefined && json.TotalCosto !== null) {
														var numero = json.TotalCosto;
														var numeroFormattato = numero.toLocaleString('it-IT', {
															style: 'decimal',
															minimumFractionDigits: 2,
															maximumFractionDigits: 2
														});

														// Rimuovi il punto decimale e separa il numero
														numeroFormattato = numeroFormattato.replace(".", ",");
														numeroFormattato = numeroFormattato.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

														// Aggiungi il simbolo dell'euro
														numeroFormattato += " €";
														$('#footerCosti').html("<b>"+json.iTotalRecords+" spese per un totale di " + numeroFormattato+"</b>");
													}
													fnCallback(json)
												} );
											},									
                                    "aoColumns": [
                                    null,
                                    null,
                                    null,
									null,
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
                        	"sProcessing":"<i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?>",
    						"sLengthMenu": "<?=$dizionario['generale']['lista_visualizza']?>",
    						"sZeroRecords": "<?=$dizionario['generale']['no_result']?>",
    						//"sInfo": "risultati totali: _TOTAL_",
                            "sInfo": "<?=$dizionario['generale']['da_a_risultati_trovati']?>",
    						"sInfoEmpty": "<?=$dizionario['generale']['zero_risultati']?>",
                        "sInfoFiltered": ""
			//"sInfoFiltered": "(risultati totali: _MAX_)"
		},
                        
                        
                        
                        "aaSorting": [],
                            "fnInitComplete": function () {
                                        
										$("input.searchDataDa", this).change( function () {
											oTable.fnFilter( $(this).val(), 0);			
                                        } );
										$("input.searchDataDa").keyup( function () {
											oTable.fnFilter( $(this).val(), 0);			
										} );
										
										$("input.searchDataA", this).change( function () {
											oTable.fnFilter( $(this).val(), 1);			
                                        } );
										$("input.searchDataA").keyup( function () {
											oTable.fnFilter( $(this).val(), 1);			
										} );
										
										$("span.searchDescrizione").html('<input type="text" />');
										$("span.searchDescrizione input", this).change( function () {
											oTable.fnFilter( $(this).val(), 2);			
                                        } );
										$("span.searchDescrizione input").keyup( function () {
											oTable.fnFilter( $(this).val(), 2);			
										} );
										
										$("span.searchCosto").html('<input type="text" />');
										$("span.searchCosto input", this).change( function () {
											oTable.fnFilter( $(this).val(), 3);			
                                        } );
										$("span.searchCosto input").keyup( function () {
											oTable.fnFilter( $(this).val(), 3);			
										} );
                                        
										$("span.searchTipoSpesa").html(fnCreateSelect( oTable.fnGetColumnData(4) ));
										$("span.searchTipoSpesa select", this).change( function () {
											oTable.fnFilter( $(this).val(), 4);			
                                        } );
										
										
										$("span.searchFornitore").html(createSelectFromFornitori( <?=json_encode($fornitori)?> ));
										$("span.searchFornitore select", this).change( function () {
											oTable.fnFilter( $(this).val(), 5);			
                                        } );
										
										$("span.searchCategoria").html(createSelectFromCategorie( <?=json_encode($categorie)?> ));
										$("span.searchCategoria select", this).change( function () {
											oTable.fnFilter( $(this).val(), 6);			
                                        } );
										
										$("span.searchDestinazione").html(createSelectFromDestinazioni( <?=json_encode($destinazioni)?> ));
										$("span.searchDestinazione select", this).change( function () {
											oTable.fnFilter( $(this).val(), 7);	
                                        } );
										
										$("span.searchPagamento").html(createSelectFromPagamenti( <?=json_encode($pagamenti)?> ));
										$("span.searchPagamento select", this).change( function () {
											oTable.fnFilter( $(this).val(), 8);	
                                        } );
										
										$("span.searchPagato").html(fnCreateSelectPagato( oTable.fnGetColumnData(9) ));
										$("span.searchPagato select", this).change( function () {
											oTable.fnFilter( $(this).val(), 9);	
                                        } );
                                    }
                } );
                
               
                 $('#brain_datatables tbody td a img.brain_collapse').live( 'click', function () {
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
			if (typeof asInitVals !== 'undefined') {
				this.value = asInitVals[$("thead input").index(this)];
			}
		}
	} );
  
} );
</script>