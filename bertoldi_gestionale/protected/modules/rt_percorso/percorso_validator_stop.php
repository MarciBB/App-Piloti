<script type="text/javascript">
    $(document).ready(function() {
        // Adatta la dimensione della dialog box all'inizio
        adatta_dialog_box();

        // Validazione del form e invio tramite funzione custom
        $("#application_form").validate({
            submitHandler: function(form) {
                submit_form_percorso_stop();
            }
        });
    });


    // Funzione per duplicare una corsa specifica
    function DuplicaCorsa(CorsaId, CorsaNome) {
		const scrollPosition = $(window).scrollTop(); 
		const conferma = confirm('Duplicare la corsa ' + CorsaNome + '?');
		
        if (conferma) {
            const page_to_load = "/protected/modules/rt_percorso/percorso_action.php?do=DuplicaCorsa&CorsaId=" + CorsaId;
            $.get(page_to_load, function(data) {
                alert("La corsa " + CorsaNome + " è stata duplicata correttamente!");
                avviso_operazione("ok");
                setTimeout(function() {
					loadMainContent('rt_percorso', 'percorso.php?do=add&step=2', this);
				}, 500); // Timeout di 500ms
            });
        } 
		
			setTimeout(function() {
				$(window).scrollTop(scrollPosition);
			}, 2);
		
    }

    // Funzione per visualizzare un avviso di operazione completata o in corso
    function avviso_operazione(tipoavv) {
        tipoavv = jQuery.trim(tipoavv);
        if (tipoavv === "ok") {
            $("#loading_big_" + tipoavv).fadeIn(2000, function() {
                $("#loading_big_" + tipoavv).fadeOut(2000);
            });
        } else {
            $("#loading_big_" + tipoavv).fadeIn(2000);
        }
    }

    // Funzione per inviare il form percorso e gestire lo step successivo
    function submit_form_percorso_stop() {
        ChiudiBox();
        const step_successivo = $("#step_successivo").val() - 1;

        $.ajax({
            type: "POST",
            url: "/protected/modules/rt_percorso/percorso_action.php",
            data: $("#application_form").serialize(),
            success: function(msg) {
                avviso_operazione("ok");
                loadMainContent1('rt_percorso', 'percorso.php?do=add&step=' + step_successivo, this);
            } // Fine success
        });
    }

    // Funzione per caricare contenuti principali tramite AJAX e aggiornare l'interfaccia
    function loadMainContent1(modulo, page, elem) {
        $("#layer_nero2").show();
        const page_to_load = "/protected/modules/" + modulo + "/" + page;

        $.get(page_to_load, function(data) {
            $("#brain_main-content").html(data);
            $("#layer_nero2").hide();
        });
    }
</script>
