<style type="text/css" title="currentStyle">
    @import "/DataTables/media/css/demo_page.css";
    @import "/DataTables/media/css/demo_table.css";
</style>
<script type="text/javascript" language="javascript" src="/js/jquery.dataTables.js"></script>   
              
<!--<script type="text/javascript" language="javascript" src="/DataTables/media/js/jquery.js"></script>-->
<!--<script type="text/javascript" language="javascript" src="/DataTables/media/js/jquery.dataTables.js"></script>-->
<script type="text/javascript" charset="utf-8">  
$(document).ready(function() {
	var oTable;
	
	/* Add the events etc before DataTables hides a column */
	$("thead input").keyup( function () {
		/* Filter on the column (the index) of this element */
		oTable.fnFilter( this.value, oTable.oApi._fnVisibleToColumnIndex( 
			oTable.fnSettings(), $("thead input").index(this) ) );
	} );
	
	/*
	 * Support functions to provide a little bit of 'user friendlyness' to the textboxes
	 */
	$("thead input").each( function (i) {
		this.initVal = this.value;
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
			this.value = this.initVal;
		}
	} );
	
	oTable = $('#elenco_clienti #SelTratta1').dataTable( {
		"sDom": 'RC<"clear">lfrtip',
		"aoColumnDefs": [
			{ "bSortable": false, "bVisible": true, "aTargets": [ 0 ] },
                        { "bSortable": false, "bVisible": true, "aTargets": [ 1 ] },
                        { "bSortable": false, "bVisible": true, "aTargets": [ 2 ] },
                        { "bSortable": false, "bVisible": true, "aTargets": [ 3 ] }
		],
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
                "bAutoWidth":false,
	} );
} );
</script>