<?php 
    global $dizionario;
?>
<script type="text/javascript"> 
    var divtoupdate;  
    var newId = 0;

    $(document).ready(function() {
        adatta_dialog_box();   

        $("#application_form").validate({
            submitHandler: function(form) {
                var step_corrente = $("#step_corrente").val();
                submit_form_tratta_wz();
            }
        });

        $('#CambiaStatoMediazioneId').submit(function() {
            $.ajax({
                type: "POST",
                url: "/protected/modules/rt_tratta/tratta_wz_action.php",
                data: $("#CambiaStatoMediazioneId").serialize(),
                success: function(msg) {
                    alert(jQuery.trim(msg));
                }
            });
            return false;
        });

        // Start tracking changes in the form
        var oldvals1 = $('#application_form').trackChanges({
            events: "change blur keypress keydown click", 
            changeListVisible: false
        });

        $('.accordion_link').click(function () {
            $('.accordion-content').slideToggle();
        });

        $('#massAggiorna').click(function () {
            var massTrattaId = $('#massTrattaId').val();
            var massIsPickup = $('#massIsPickup').val();
            var massIsDropOff = $('#massIsDropOff').val();
            var massIsInterscambio = $('#massIsInterscambio').val();
            var massIsBlackList = $('#massIsBlackList').val();
            var massIsDaConfermare = $('#massIsDaConfermare').val();
            var massWebSelling = $('#massWebSelling').val();
            var massStato = $('#massStato').val();

            if (!massTrattaId || massTrattaId === "null" || massTrattaId === null) {
                alert('Seleziona almeno una tratta');
                return;
            } else {

                $.ajax({
                    type: "POST",
                    url: "/protected/modules/rt_tratta/tratta_wz_action.php",
                    data: {
                        action: 'massUpdate',
                        massTrattaId: massTrattaId,
                        massIsPickup: massIsPickup,
                        massIsDropOff: massIsDropOff,
                        massIsInterscambio: massIsInterscambio,
                        massIsBlackList: massIsBlackList,
                        massIsDaConfermare: massIsDaConfermare,
                        massWebSelling: massWebSelling,
                        massStato: massStato
                    },
                    success: function(msg) {
                        loadMainContent('rt_tratta', 'tratta_wz.php?do=add&step=2', this);
                    }
                });
            }
        });
    });

    function CambiaStep(modulo, page, elem) {
        $("#layer_nero2").show();
        $("#brain_menu").find('a').removeClass("brain_sel");
        $(elem).addClass("brain_sel");

        var page_to_load = "/protected/modules/" + modulo + "/" + page;
        $.get(page_to_load, function(data) {
            CaricaInBox(data);
            $("#layer_nero2").hide(); 
        });
    }

    function loadMediazioneStep(modulo, page, elem) {
        if ($("#application_formTrackList")[0].length == 0) {
            CambiaStep(modulo, page, elem);
        } else {
            var flag = confirm("I dati inseriti nello step corrente non sono stati ancora salvati. Cambiando pagina le modifiche non saranno memorizzate. Continuare?", "Si", "No");
            if (flag) {
                CambiaStep(modulo, page, elem);
            } else {
                return false;
            }
        }
    }

    function avviso_operazione(tipoavv) {
        tipoavv = jQuery.trim(tipoavv);

        if (tipoavv == "ok") {    
            $("#loading_big_" + tipoavv).fadeIn(2000, function() {			
                $("#loading_big_" + tipoavv).fadeOut(2000);
            });
        } else {
            $("#loading_big_" + tipoavv).fadeIn(2000);
        }
    }

    function submit_form_tratta_wz() {
        var step_successivo = $("#step_successivo").val();
        var step_corrente = $("#step_corrente").val();

        $.ajax({
            type: "POST",
            url: "/protected/modules/rt_tratta/tratta_wz_action.php",
            data: $("#application_form").serialize(),
            success: function(msg) {
                avviso_operazione("ok");
                loadMainContent('rt_tratta', 'tratta_wz.php?do=add&step=' + step_successivo, this);
            }
        });
    }

    function CancellaFermata(FermataId) {
        var stringa = "<?=$dizionario['tratta']['sicuro_cancella_fermata']?>";
        var conferma = confirm(stringa);
        if (conferma) {
            var page_to_load = "/protected/modules/rt_tratta/tratta_wz_action.php?action=CancellaFermata&FermataId=" + FermataId;
            $.get(page_to_load, function(data) {
                alert("<?=$dizionario['tratta']['fermata_cancellata']?>");
                avviso_operazione("ok");
                loadMainContent('rt_tratta', 'tratta_wz.php?do=add&step=2', this);
            });
        }  
    }
</script>
