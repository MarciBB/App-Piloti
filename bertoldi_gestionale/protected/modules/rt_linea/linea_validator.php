<script type="text/javascript">

    // Funzione per mostrare un avviso di operazione completata
    function avviso_operazione(tipoavv) {
        if (tipoavv === "ok") {
            // Se il tipo di avviso è "ok", mostra l'elemento e poi lo nasconde con un effetto di fade
            $("#loading_big_" + tipoavv).fadeIn(2000, function() {
                $("#loading_big_" + tipoavv).fadeOut(2000);
            });
        } else {
            // Altrimenti, mostra l'elemento senza nasconderlo
            $("#loading_big_" + tipoavv).fadeIn(2000);
        }
    }

    // Funzione per inviare il form linea
    function submit_form_linea() {
        var messaggio;
        var id;

        // Esecuzione dell'Ajax per l'invio del form
        $.ajax({
            type: "POST",
            url: "/protected/modules/rt_linea/linea_action.php",
            data: $("#application_form").serialize(),
            success: function(msg) {
                msg = jQuery.trim(msg);

                // Verifica se il messaggio di risposta contiene una virgola
                if (msg.indexOf(',') > -1) {
                    var arr_response = msg.split(",");
                    messaggio = arr_response[0];
                    id = arr_response[1];
                } else {
                    messaggio = msg;
                }

                // Condizioni per visualizzare o nascondere il box principale
                if ($("#brain_oscura").css("display") === "none") {
                    loadMainContent('rt_linea', 'linea.php', this);
                    avviso_operazione(messaggio);
                } else {
					ChiudiBox();
					setTimeout(function() {
						loadMainContent('rt_percorso', 'percorso.php?do=add&step=2', this);
					}, 500);
                }
            } // Fine callback success
        });
    }

    // Impostazioni al caricamento del documento
    $(document).ready(function() {
        adatta_dialog_box(); // Adatta la dimensione del dialog box

        // Validazione del form application_form
        $("#application_form").validate({
            submitHandler: function(form) {
                submit_form_linea(); // Invia il form tramite Ajax
            }
        });
    });

</script>
