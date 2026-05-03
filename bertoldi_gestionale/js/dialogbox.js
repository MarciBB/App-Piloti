function dialog_box (){

	// ridimensiona background
	var windowH = $(document).height();
	
	$("div#brain_oscura").css('height',windowH);

        $("#brain_oscura").show();

        $("div#brain_boxCentrato").hide();

        $("#brain_loading").show();

}

function dialog_box_previaggio (){

	// ridimensiona background
	var windowH = $(document).height();
	$("div#brain_oscura_pre").css('height',windowH);

        $("#brain_oscura_pre").show();
       /* $("div#brain_boxCentrato").hide();
        $("#brain_loading").show();*/

}


function adatta_dialog_box() {

	// centra dialog box in verticale
    var AltezzaScroll = $(document).scrollTop() +50;
	
	$("div#brain_boxCentrato").css('margin', ''+AltezzaScroll+'px auto 0 auto');

    var larghezzaPagina = $(document).width();
    //alert(larghezzaPagina);

	var larghezzaDiv = $("div#brain_boxCentrato").width();
        //alert(larghezzaDiv);

	var posizioneSX = larghezzaPagina/2 - larghezzaDiv/2;
        //alert(posizioneSX);
        $("div#brain_boxCentrato").css('margin-left', ''+posizioneSX+'px');
        
        $("#brain_loading").hide();
        $("div#brain_boxCentrato").fadeIn('fast');
}

function ChiudiBox() {

    $('#brain_boxCentrato').fadeOut('fast', function() {
        // svuota il div brain_listaSelezione
        $("#brain_listaSelezione").html("");
    });
    $("#brain_oscura").fadeOut('fast');
    
    
}
