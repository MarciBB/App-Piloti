<?php global $dizionario; ?>
<!-- Importazione dei CSS di DataTables -->
<style type="text/css" title="currentStyle">
    @import "/DataTables/media/css/demo_page.css";
    @import "/DataTables/media/css/demo_table.css";
</style>

<!-- Inclusione della libreria DataTables -->
<script type="text/javascript" language="javascript" src="/js/jquery.dataTables.js"></script>

<script type="text/javascript" charset="utf-8">
    var oTable;      // Variabile globale per la tabella DataTable
    var asInitVals = [];  // Array per valori iniziali dei filtri

    // Estensione di DataTables per ottenere i dati di una colonna
    (function($) {
        $.fn.dataTableExt.oApi.fnGetColumnData = function (oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty) {
            if (typeof iColumn == "undefined") return [];

            if (typeof bUnique == "undefined") bUnique = true;
            if (typeof bFiltered == "undefined") bFiltered = true;
            if (typeof bIgnoreEmpty == "undefined") bIgnoreEmpty = true;

            // Ottiene le righe da considerare (filtrate o tutte)
            var aiRows = bFiltered ? oSettings.aiDisplay : oSettings.aiDisplayMaster;
            var asResultData = [];

            // Cicla sulle righe e raccoglie i dati della colonna richiesta
            for (var i = 0, c = aiRows.length; i < c; i++) {
                var iRow = aiRows[i];
                var aData = this.fnGetData(iRow);
                var sValue = aData[iColumn];
                if (sValue) {
                    if (bIgnoreEmpty && sValue.length == 0) continue; // Salta valori vuoti
                    else if (bUnique && jQuery.inArray(sValue, asResultData) > -1) continue; // Salta duplicati
                    else asResultData.push(sValue); // Aggiunge il valore
                }
            }
            return asResultData;
        }
    }(jQuery));

    // Crea un elemento select per il filtro di colonna
    function fnCreateSelect(aData) {
        var r = '<select><option value=""><?=$dizionario['generale']['tutti']?></option>';
        for (var i = 0, iLen = aData.length; i < iLen; i++) {
            var valore_select = aData[i].replace(/(<.*?>)/ig, ""); // Rimuove eventuali tag HTML
            r += '<option value="' + valore_select + '">' + aData[i] + '</option>';
        }
        return r + '</select>';
    }

    // Formatta i dettagli aggiuntivi per una riga (espansione)
    function fnFormatDetails(nTr) {
        var aData = oTable.fnGetData(nTr);
        if (aData) {
            var sOut = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
            sOut += '<tr><td>Rendering engine:</td><td>' + aData[2] + '</td></tr>';
            sOut += '<tr><td>Link to source:</td><td>Could provide a link here</td></tr>';
            sOut += '<tr><td>Extra info:</td><td>And any further details here (images etc)</td></tr>';
            sOut += '</table>';
            return sOut;
        }
        return '';
    }

    // Inizializzazione della tabella DataTable
    $(document).ready(function() {
        oTable = $("#brain_datatables").dataTable({
            "bProcessing": true, // Mostra indicatore di caricamento
            "bServerSide": true, // Attiva il caricamento server-side
            "sAjaxSource": "/protected/modules/rt_corsapartenza/corsapartenza_processing.php", // URL sorgente dati
            "aoColumns": [
                {
                    "sClass": "center",
                    "bSortable": false,
                    "mData": null,
                    // Renderizza una checkbox per la selezione della corsa
                    "mRender": function (data, type, row) {
                        var corsaId = row[2];
                        var dataPartenza = row[5];
                        return '<input type="checkbox" class="select-corsa" value="' + corsaId + '" data-partenza="' + dataPartenza + '">';
                    }
                },
                { "sClass": "center", "bSortable": false },
                null, null, null, null, null,
                { "sClass": "center", "bSortable": false },
                { "sClass": "center", "bSortable": true },
                { "sClass": "center", "bSortable": true },
                { "sClass": "center", "bSortable": true },
                { "sClass": "center", "bSortable": false },
                { "sClass": "center", "bSortable": false },
                { "sClass": "center", "bSortable": false },
                null,
                { "sClass": "center", "bSortable": false },
                { "sClass": "center", "bSortable": false }
            ],
            "bAutoWidth": false, // Disabilita larghezza automatica colonne
            "oLanguage": {
                // Traduzioni e testi personalizzati
                "sProcessing": "<i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?>",
                "sLengthMenu": "<?=$dizionario['generale']['lista_visualizza']?>",
                "sZeroRecords": "<?=$dizionario['generale']['no_result']?>",
                "sInfo": "<?=$dizionario['generale']['da_a_risultati_trovati']?>",
                "sInfoEmpty": "<?=$dizionario['generale']['zero_risultati']?>",
                "sInfoFiltered": ""
            },
            "aaSorting": [], // Nessun ordinamento iniziale
            // Al termine dell'inizializzazione, aggiunge i filtri select alle intestazioni
            "fnInitComplete": function () {
                $("thead th span").each(function (i) {
                    this.innerHTML = fnCreateSelect(oTable.fnGetColumnData(i));
                    $('select', this).change(function () {
                        oTable.fnFilter($(this).val(), i); // Filtra la colonna in base al valore selezionato
                    });
                });
            }
        });

        // Gestisce la selezione/deselezione di tutte le checkbox
        $('#select-all-corse').change(function () {
            var checked = $(this).is(':checked');
            $('.select-corsa').attr('checked', checked);
        });

        // Se una checkbox viene deselezionata, deseleziona anche quella nell'header
        $('.select-corsa').change(function () {
            if (!$(this).is(':checked')) {
                $('#select-all-corse').attr('checked', false);
            }
        });

        // Gestione azione di massa sulle corse selezionate
        $('#btn-mass-action-corse').click(function () {
            var action = $('#mass-action-corse').val();
            if (!action) {
                alert('Seleziona un\'azione di massa.');
                return;
            }
            var selected = $('.select-corsa:checked');
            if (selected.length === 0) {
                alert('Seleziona almeno una corsa.');
                return;
            }
            var corse = [];
            selected.each(function () {
                corse.push({
                    CorsaId: $(this).val(),
                    DataPartenza: $(this).data('partenza')
                });
            });
            // Invio richiesta AJAX per eseguire l'azione di massa
            $.ajax({
                url: '/protected/modules/rt_corsapartenza/corsapartenza_action.php',
                type: 'POST',
                data: {
                    mass_action: action,
                    corse: JSON.stringify(corse)
                },
                success: function (resp) {
                    oTable.fnDraw(false); // Aggiorna la tabella senza cambiare pagina
                },
                error: function () {
                    alert('Errore durante l\'operazione.');
                }
            });
        });

        // Gestione apertura/chiusura dettagli riga (espansione)
        $('#brain_datatables tbody td a img.brain_collapse').live('click', function () {
            var nTr = this.parentNode.parentNode.parentNode;
            if (this.src.match('close_details')) {
                this.src = "/images/open_details.png";
                oTable.fnClose(nTr);
            } else {
                this.src = "/images/close_details.png";
                oTable.fnOpen(nTr, fnFormatDetails(nTr), 'details');
            }
        });

        // Filtraggio tramite input nelle intestazioni
        $("thead input").keyup(function () {
            oTable.fnFilter(this.value, $("thead input").index(this));
        });

        // Salva i valori iniziali degli input di ricerca
        $("thead input").each(function (i) {
            if (typeof asInitVals !== 'undefined') {
                asInitVals[i] = this.value;
            }
        });

        // Gestione focus sugli input di ricerca (rimuove testo iniziale)
        $("thead input").focus(function () {
            if (this.className == "search_init") {
                this.className = "";
                this.value = "";
            }
        });

        // Ripristina testo iniziale se input vuoto in blur
        $("thead input").blur(function (i) {
            if (this.value == "") {
                this.className = "search_init";
                this.value = asInitVals[$("thead input").index(this)];
            }
        });
    });
</script>
