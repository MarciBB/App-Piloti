<script type="text/javascript">
    function avviso_operazione(tipoavv) {
        if (tipoavv == "ok") {
            $("#loading_big_" + tipoavv).fadeIn(2000, function() {
                $("#loading_big_" + tipoavv).fadeOut(2000, function() {});
            });
        } else {
            $("#loading_big_" + tipoavv).fadeIn(2000, function() {});
        }
    }

    function loadMainContent1(modulo, page, elem) {
        $("#layer_nero2").show();
        page_to_load = "/protected/modules/" + modulo + "/" + page;
        $.get(page_to_load, function(data) {
            $("#brain_main-content").html("");
            $("#brain_main-content").html(data);
            $("#layer_nero2").hide();
        });
    }

    function CambiaStep(modulo, page, elem) {
        $("#layer_nero2").show();
        $("#brain_menu").find('a').removeClass("brain_sel");
        $(elem).addClass("brain_sel");
        page_to_load = "/protected/modules/" + modulo + "/" + page;
        $.get(page_to_load, function(data) {
            CaricaInBox(data);
            $("#layer_nero2").hide();
        });
    }

    function loadMediazioneStep(modulo, page, elem) {
        CambiaStep(modulo, page, elem);
    }

    function submit_form_provvigione() {
        var messaggio;
        var id;
        var step_successivo = $("#step_successivo").val();
        $.ajax({
            type: "POST",
            url: "/protected/modules/rt_provvigione/provvigione_action.php",
            data: $("#application_form").serialize(),
            success: function(msg) {
                msg = jQuery.trim(msg);
                if (msg.indexOf(',') > -1) {
                    arr_response = msg.split(",");
                    messaggio = arr_response[0];
                    id = arr_response[1];
                } else {
                    messaggio = msg;
                }
                avviso_operazione("ok");
                if (step_successivo == '0') {
                    loadMainContent('rt_provvigione', 'provvigione.php?do=edit&Idprovvigione=0', this);
                } else {
                    loadMainContent1('rt_provvigione', 'provvigione.php?do=add&step=' + step_successivo, this);
                }
            }
        });
    }

    $(document).ready(function() {
        adatta_dialog_box();
        $("#application_form").validate({
            submitHandler: function(form) {
                submit_form_provvigione();
            }
        });
        
        $('.accordion a').click(function () {
            $(this).next('.accordion-content').slideToggle();
        });

        $('#massAggiorna').click(function () {
            var provvigioneId = $('#massProvvigioneId').val();
            var percentuale = $('#massPercentuale').val();
            var fisso = $('#massFisso').val();
            var lineaId = $('#massLineaId').val();

            if (provvigioneId == "" ) {
                alert('Seleziona una provvigione valida e inserisci numeri validi per percentuale e fisso.');
            } else {

                $.ajax({
                    type: "POST",
                    url: "/protected/modules/rt_provvigione/provvigione_action.php",
                    data: {
                        action: 'createAll',
                        massProvvigioneId: provvigioneId,
                        massPercentuale: percentuale,
                        massFisso: fisso,
                        massLineaId: lineaId
                    },
                    success: function(msg) {
                        loadMainContent1('rt_provvigione', 'provvigione.php?do=add&step=2', this);
                    }
                });
            }
        });
    });
</script>
