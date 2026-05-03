<script type="text/javascript">
    // Mostra un avviso di operazione completata o in corso
    function avviso_operazione(tipoavv) {
        if (tipoavv === "ok") {
            $("#loading_big_" + tipoavv).fadeIn(2000, function() {
                $("#loading_big_" + tipoavv).fadeOut(2000);
            });
        } else {
            $("#loading_big_" + tipoavv).fadeIn(2000);
        }
    }

    // Invio del form della tratta tramite AJAX
    function submit_form_tratta() {
        let messaggio;
        let id;

        $.ajax({
            type: "POST",
            url: "/protected/modules/rt_tratta/tratta_action.php",
            data: $("#application_form").serialize(),
            success: function(msg) {
                msg = jQuery.trim(msg);

                if (msg.indexOf(',') > -1) {
                    const arr_response = msg.split(",");
                    messaggio = arr_response[0];
                    id = arr_response[1];
                } else {
                    messaggio = msg;
                }

                if ($('#return').val() === '1') {
                    ChiudiBox();
                } else {
                    if ($("#brain_oscura").css("display") === "none") {
                        loadMainContent('rt_tratta', 'tratta.php', this);
                        avviso_operazione(messaggio);
                    } else {
                        ChiudiBox();
                        setTimeout(function() {
                            loadMainContent('rt_percorso', 'percorso.php?do=add&step=2', this);
                        }, 500); // Timeout di 500ms
                    }
                }
            } // Fine success
        });
    }

    // Funzione per confermare ed eseguire la cancellazione della tratta
    function CancellaTratta(TrattaId) {
        const conferma = confirm("<?= $dizionario['tratta']['sicuro_cancella_tratta'] ?>");
        if (conferma) {
            const page_to_load = `/protected/modules/rt_tratta/tratta_action.php?action=CancellaTratta&TrattaId=${TrattaId}`;
            $.get(page_to_load, function(data) {
                alert("<?= $dizionario['tratta']['tratta_cancellata'] ?>");
                avviso_operazione("ok");
                loadMainContent('rt_tratta', 'tratta.php', this);
            });
        }
    }

    // Quando il documento è pronto, configura la validazione del form e i cambiamenti di dropdown
    $(document).ready(function() {
        adatta_dialog_box();

        $("#application_form").validate({
            submitHandler: function(form) {
                submit_form_tratta();
            }
        });

        $('#ComuneId').change(function() {
            const data = {
                action: 'getFermateComune',
                ComuneId: $('#ComuneId').val()
            };

            $.ajax({
                type: "POST",
                url: "/protected/modules/rt_tratta/tratta_action.php",
                data: data,
                success: function(msg) {
                    $('#fermateTratta').html(msg);
                } // Fine success
            });
        });
    });
</script>
