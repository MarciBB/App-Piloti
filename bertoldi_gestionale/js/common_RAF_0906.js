/* Common js */

var divtoupdate;    

function CaricaElencoComuni(modulo,page)
{
    //dialog_box();
    
    page_to_load="/protected/modules/"+modulo+"/"+page;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
                    $("#elenco_comuni").show();
                    $("#elenco_comuni").html(data);
                    
                  

              } );

}

    
function ExternalLoad(modulo,page)
{
    dialog_box();
    
    page_to_load="/protected/modules/"+modulo+"/"+page;
            //	$("#layer_nero2").toggle();
            $.get(page_to_load, function(data){
                    $("#brain_listaSelezione").html(data);
                    
                    $("#layer_nero2").hide(); 

              } );

}

function UpdateFieldFromBox(ElementId,LabelId)
{
    fieldtoupdate=$("#fieldtoupdate").val();
    fieldtoupdateSeleziona=fieldtoupdate+"Seleziona";
    labeltoupdate=$("#labeltoupdate").val();
    $("#"+fieldtoupdate).val(ElementId);
     $("#"+fieldtoupdateSeleziona).html("CAMBIA COMUNE");
     $("#"+labeltoupdate).val(LabelId);
     $("#elenco_comuni").html();
     $("#elenco_comuni").hide();
  //  ChiudiBox();
    
    
    
    
}

function CaricaInBox(data)
{
    if ($("#brain_oscura").css("display")=="none")
    $("#brain_main-content").html(data);
    else
    $("#brain_listaSelezione").html(data);    
        
}
    
    
function loadMainContent(modulo,page,elem)
        {
            
        $("#layer_nero2").show();
        $("#menubar").find('a').removeClass("brain_sel");
        $("#menubar").find('span').removeClass("brain_sel");
        
        $(elem).parent().addClass("brain_sel");
        $(elem).parent().parent().addClass("brain_sel");
        
        page_to_load="/protected/modules/"+modulo+"/"+page;
        //	$("#layer_nero2").toggle();
        $.get(page_to_load, function(data){
                CaricaInBox(data);
                $("#layer_nero2").hide(); 
                
          } );
          
        }
