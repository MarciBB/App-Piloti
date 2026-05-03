<?php 
// Variabile globale per il dizionario
global $dizionario;
?>

<!-- Importazione di stili CSS per DataTables -->
<style type="text/css" title="currentStyle">
    @import "/DataTables/media/css/demo_page.css";
    @import "/DataTables/media/css/demo_table.css";
</style>

<!-- Inclusione del file JavaScript di DataTables -->
<script type="text/javascript" language="javascript" src="/js/jquery.dataTables.js"></script>

<script type="text/javascript" charset="utf-8">
    var oTable; // Variabile globale per la tabella DataTables
    var asInitVals = []; // Array per memorizzare i valori iniziali degli input

    (function($) {
        /*
         * Funzione: fnGetColumnData
         * Scopo: Restituisce un array di valori di una colonna specifica della tabella.
         * Parametri:
         * - oSettings: oggetto delle impostazioni di DataTables
         * - iColumn: indice della colonna
         * - bUnique: se true, restituisce solo valori unici
         * - bFiltered: se true, considera solo i dati filtrati
         * - bIgnoreEmpty: se true, ignora i valori vuoti
         */
        $.fn.dataTableExt.oApi.fnGetColumnData = function (oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty) {
            if (typeof iColumn == "undefined") return new Array();

            if (typeof bUnique == "undefined") bUnique = true;
            if (typeof bFiltered == "undefined") bFiltered = true;
            if (typeof bIgnoreEmpty == "undefined") bIgnoreEmpty = true;

            var aiRows = bFiltered ? oSettings.aiDisplay : oSettings.aiDisplayMaster; // Righe da considerare
            var asResultData = []; // Array per i risultati

            for (var i = 0, c = aiRows.length; i < c; i++) {
                var iRow = aiRows[i];
                var aData = this.fnGetData(iRow);
                var sValue = aData[iColumn];

                if (sValue) {
                    if (bIgnoreEmpty && sValue.length == 0) continue;
                    if (bUnique && jQuery.inArray(sValue, asResultData) > -1) continue;
                    asResultData.push(sValue);
                }
            }

            return asResultData;
        };
    }(jQuery));

    /*
     * Funzione: fnCreateSelect
     * Scopo: Crea un elemento <select> con opzioni basate sui dati forniti.
     */
    function fnCreateSelect(aData) {
        var r = '<select><option value=""><?=$dizionario['generale']['tutti']?></option>';
        for (var i = 0, iLen = aData.length; i < iLen; i++) {
            var valore_select1 = aData[i];
            var valore_select = valore_select1.replace(/(<.*?>)/ig, ""); // Rimuove tag HTML
            r += '<option value="' + valore_select + '">' + aData[i] + '</option>';
        }
        return r + '</select>';
    }

    /*
     * Funzione: fnFormatDetails
     * Scopo: Formatta i dettagli di una riga espandibile.
     */
    function fnFormatDetails(nTr) {
        var aData = oTable.fnGetData(nTr);
        if (aData) {
            var sOut = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
            sOut += '<tr><td>Rendering engine:</td><td>' + aData[2] + '</td></tr>';
            sOut += '<tr><td>Link to source:</td><td>Could provide a link here</td></tr>';
            sOut += '<tr><td>Extra info:</td><td>And any further details here (images etc)</td></tr>';
            sOut += '</table>';
        }
        return sOut;
    }

    $(document).ready(function() {
        // Inizializzazione della tabella DataTables
        oTable = $("#brain_datatables").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "/protected/modules/rt_biglietto/biglietto_processing_daconfermare.php",
            "aoColumns": [
                null, null, null, null, null, null, null, null, null, null, null, null, null, null,
                { "sClass": "center", "bSortable": false }
            ],
            "bAutoWidth": false,
            "oLanguage": {
                "sProcessing": "<i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?>",
                "sLengthMenu": "<?=$dizionario['generale']['lista_visualizza']?>",
                "sZeroRecords": "<?=$dizionario['generale']['no_result']?>",
                "sInfo": "<?=$dizionario['generale']['da_a_risultati_trovati']?>",
                "sInfoEmpty": "<?=$dizionario['generale']['zero_risultati']?>",
                "sInfoFiltered": ""
            },
            "aaSorting": [],
            "fnInitComplete": function() {
                // Aggiunta di filtri personalizzati alle intestazioni delle colonne
                $("thead th span").each(function(i) {
                    this.innerHTML = fnCreateSelect(oTable.fnGetColumnData(i));
                    $('select', this).change(function() {
                        oTable.fnFilter($(this).val(), i);
                    });
                });
            }
        });

        // Gestione del click sulle immagini per espandere/chiudere i dettagli
        $('#brain_datatables tbody td a img.brain_collapse').live('click', function() {
            var nTr = this.parentNode.parentNode.parentNode;
            if (this.src.match('close_details')) {
                this.src = "/images/open_details.png";
                oTable.fnClose(nTr);
            } else {
                this.src = "/images/close_details.png";
                oTable.fnOpen(nTr, fnFormatDetails(nTr), 'details');
            }
        });

        // Filtri per le colonne tramite input nelle intestazioni
        $("thead input").keyup(function() {
            oTable.fnFilter(this.value, $("thead input").index(this));
        });

        // Gestione dei valori iniziali degli input
        $("thead input").each(function(i) {
            if (typeof asInitVals !== 'undefined') {
                asInitVals[i] = this.value;
            }
        });

        $("thead input").focus(function() {
            if (this.className == "search_init") {
                this.className = "";
                this.value = "";
            }
        });

        $("thead input").blur(function(i) {
            if (this.value == "") {
                this.className = "search_init";
                if (typeof asInitVals !== 'undefined' && asInitVals[$("thead input").index(this)] !== undefined) {
                    this.value = asInitVals[$("thead input").index(this)];
                } else {
                    this.value = "";
                }
            }
        });
    });
</script>
