
<script type="text/javascript">
    function avviso_operazione(tipoavv) {
        if (tipoavv === "ok") {
            $("#loading_big_" + tipoavv).fadeIn(2000, function () {
                $("#loading_big_" + tipoavv).fadeOut(2000);
            });
        } else {
            $("#loading_big_" + tipoavv).fadeIn(2000);
        }
    }

    function submit_form_gestore_convenzione() {
        var messaggio;
        var id;

        $.ajax({
            type: "POST",
            url: "/protected/modules/gestore_convenzione/gestore_convenzione_action.php",
            data: $("#application_form").serialize(),
            success: function (msg) {
                msg = jQuery.trim(msg);

                if (msg.indexOf(",") > -1) {
                    arr_response = msg.split(",");
                    messaggio = arr_response[0];
                    id = arr_response[1];
                } else {
                    messaggio = msg;
                }

                if ($("#brain_oscura").css("display") === "none") {
                    loadMainContent('gestore_convenzione', 'gestore_convenzione.php', this);
                    avviso_operazione(messaggio);
                } else {
                    ChiudiBox();
					setTimeout(function() {
						loadMainContent('gestore', 'gestore.php?do=add&step=3', this);
					}, 500);
                }
            } // end success
        });
    }

    $(document).ready(function () {
        adatta_dialog_box();

        $("#application_form").validate({
            submitHandler: function (form) {
                submit_form_gestore_convenzione();
            }
        });
    });
</script>

