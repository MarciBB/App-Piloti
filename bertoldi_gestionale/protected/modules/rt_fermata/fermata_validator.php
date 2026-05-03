<script type="text/javascript">

    function avviso_operazione(tipoavv) {
        if (tipoavv == "ok") {    
            $("#loading_big_" + tipoavv).fadeIn(2000, function() {
                $("#loading_big_" + tipoavv).fadeOut(2000);
            });
        } else {
            $("#loading_big_" + tipoavv).fadeIn(2000);
        }
    }

    function submit_form_fermata() {
        var messaggio;
        var id;

        $.ajax({
            type: "POST",
            url: "/protected/modules/rt_fermata/fermata_action.php",
            data: $("#application_form").serialize(),
            success: function(msg) {
                msg = jQuery.trim(msg);
                
                if (msg.indexOf(',') > -1) {
                    var arr_response = msg.split(",");
                    messaggio = arr_response[0];
                    id = arr_response[1];
                } else {
                    messaggio = msg;
                }

                if ($("#brain_oscura").css("display") == "none") {
                    loadMainContent('rt_fermata', 'fermata.php', this);
                    avviso_operazione(messaggio);
                } else {
                    ChiudiBox();
					setTimeout(function() {
                            loadMainContent('rt_tratta', 'tratta_wz.php?do=add&step=2', this);
					}, 500);
                }
            }
        });
    }

    $(document).ready(function() {
        adatta_dialog_box();

        $("#application_form").validate({
            submitHandler: function(form) {
                submit_form_fermata();
            }
        });
    });

</script>
