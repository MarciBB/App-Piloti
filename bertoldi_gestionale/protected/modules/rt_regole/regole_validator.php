<script type="text/javascript">

    function avviso_operazione(tipoavv) {
        if (tipoavv === "ok") {
            $("#loading_big_" + tipoavv).fadeIn(2000, function() {
                $("#loading_big_" + tipoavv).fadeOut(2000);
            });
        } else {
            $("#loading_big_" + tipoavv).fadeIn(2000);
        }
    }

    function loadMainContent1(modulo, page, elem) {
        $("#layer_nero2").show();
        const page_to_load = `/protected/modules/${modulo}/${page}`;

        $.get(page_to_load, function(data) {
            $("#brain_main-content").html(data);
            $("#layer_nero2").hide();
        });
    }

    function CambiaStep(modulo, page, elem) {
        $("#layer_nero2").show();
        $("#brain_menu").find('a').removeClass("brain_sel");
        $(elem).addClass("brain_sel");

        const page_to_load = `/protected/modules/${modulo}/${page}`;
        $.get(page_to_load, function(data) {
            CaricaInBox(data);
            $("#layer_nero2").hide();
        });
    }

    function loadMediazioneStep(modulo, page, elem) {
        CambiaStep(modulo, page, elem);
    }

    function submit_form_provvigione() {
        const step_successivo = $("#step_successivo").val();
        let messaggio;
        let id;

        $.ajax({
            type: "POST",
            url: "/protected/modules/rt_regole/regole_action.php",
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

                avviso_operazione("ok");

                if (step_successivo === '0') {
					setTimeout(function() {
                            loadMainContent('rt_regole', 'regole.php?do=edit', this);
					}, 500); 
                } else {
					setTimeout(function() {
                            loadMainContent1('rt_regole', `regole.php?do=add&step=${step_successivo}`, this);
					}, 500);
                    
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
    });

</script>
