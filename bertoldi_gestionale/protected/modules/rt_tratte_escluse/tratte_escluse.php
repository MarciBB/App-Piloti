<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();

include_once($classespath_."class.Form.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");

include_once($classespath_."class.TipologiaBiglietto.php");

$ModuloId=46;





function edit()
{
//include_once("mustache.min.js");    
include_once("tratte_escluse_validator.php");      


  global $HtmlCommon,$user, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  


$tipologiabiglietto->conn=$db;


$HtmlCommon->html_titolo_pagina("Tratte non vendibili",0,"rt_tratte_escluse","tratte_escluse.php");
$HtmlCommon->html_titolo_box ("Tratte non vendibili");

$sqlp="SELECT
Comune.ComuneId,    
Nazione.Nazione,
Comune.Comune
FROM
Comune
INNER JOIN Provincia ON Comune.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
INNER JOIN RT_Fermata ON Comune.ComuneId = RT_Fermata.ComuneId
WHERE
RT_Fermata.IsPickup = 1
GROUP BY
Comune.ComuneId
ORDER BY
Nazione.Nazione ASC,
Comune.Comune ASC";

 $row_pickup = $db->fetch_array($sqlp);
 
 
 $sqld="SELECT
Comune.ComuneId,      
Nazione.Nazione,
Comune.Comune
FROM
Comune
INNER JOIN Provincia ON Comune.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
INNER JOIN RT_Fermata ON Comune.ComuneId = RT_Fermata.ComuneId
WHERE
RT_Fermata.IsDropOff = 1
GROUP BY
Comune.ComuneId
ORDER BY
Nazione.Nazione ASC,
Comune.Comune ASC";

 $row_dropoff = $db->fetch_array($sqld);

$optionPickup="<option value='0'> select </option>";
foreach ($row_pickup as $key=>$value)
{
   $comuneId=$value['ComuneId'];
 //  $nazcom=$value['Nazione']." - ".$value['Comune'];
 $nazcom=$value['Comune']." - ".$value['Nazione'];
   $optionPickup.="<option value='".$comuneId."'>$nazcom</option>";
    
   
}

$optionDropOff="<option value='0'> select </option>";
foreach ($row_dropoff as $key=>$value)
{
   $comuneId=$value['ComuneId'];
 //  $nazcom=$value['Nazione']." - ".$value['Comune'];
    $nazcom=$value['Comune']." - ".$value['Nazione'];
   $optionDropOff.="<option value='".$comuneId."'>$nazcom</option>";
    
   
}
?>

<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",1);
                print("<br style=\"clear:both;\"/>");
                 ?>
                
                                    <div style="padding: 10px">
                <button  type="button" onclick="addBlock()">+ Aggiungi</button>
                                    </div>

                <table class="table_dinamica">
                    <td class="pickdrop"><label>Pickup</label></td>
                    <td class="pickdrop"><label>Dropoff</label></td>
                    <td class="pickdrop_btn">&nbsp;</td>
                </table>
            <table id="container_fermate"  class="table_dinamica">
                
                
                <tr>
                    <td class="pickdrop">
                        <select name="pickup[]">
                            <?=$optionPickup?>
                        </select>                        
                    </td>
                    <td class="pickdrop">
                        <select name="dropoff[]">
                            <?=$optionDropOff?>
                        </select>                        
                    </td>
                    <td class="pickdrop_btn">
                        <button type="button" onclick="removeBlock(this)">X</button>
                    </td>
                </tr>
                
                
                <?php
                
                $sql="SELECT
Comune.Comune as comunePickup,
Comune.ComuneId as comuneIdPickup,
Nazione.Nazione as nazionePickup,
cd.Comune as comuneDropOff,
cd.ComuneId as comuneIdDropOff,
nd.Nazione as nazioneDropOff

FROM
RT_TratteNonVendibili
INNER JOIN Comune ON RT_TratteNonVendibili.ComunePickUpId = Comune.ComuneId
INNER JOIN Provincia ON Comune.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
INNER JOIN Nazione ON Regione.idnazione = Nazione.NazioneId
INNER JOIN Comune cd ON RT_TratteNonVendibili.ComuneDropOffId = cd.ComuneId
INNER JOIN Provincia pd ON cd.provincia = pd.ProvinciaId
INNER JOIN Regione rd ON pd.RegioneId = rd.RegioneId
INNER JOIN Nazione nd ON rd.idnazione = nd.NazioneId";
                
                $optionPickupE="";
                 $optionDropE="";
                 
                 $row_edit = $db->fetch_array($sql);
                 
                foreach ($row_edit as $key=>$value)
                {
                   $cidp=$value['comuneIdPickup'];
                   //$nazcompe=$value['nazionePickup']." - ".$value['comunePickup'];
                   $nazcompe=$value['comunePickup']." - ".$value['nazionePickup'];
                   $optionPickupE="<option value='".$cidp."'>$nazcompe</option>";
                   
                   
                   $cidd=$value['comuneIdDropOff'];
                 //  $nazcomde=$value['nazioneDropOff']." - ".$value['comuneDropOff'];
                    $nazcomde=$value['comuneDropOff']." - ".$value['nazioneDropOff'];
                  
                   $optionDropE="<option value='".$cidd."'>$nazcomde</option>";
                   
                   ?>
                    <tr>
                    <td class="pickdrop">
                        <select name="pickup[]">
                            <?=$optionPickupE?>
                        </select>                        
                    </td>
                    <td class="pickdrop">
                        <select name="dropoff[]">
                            <?=$optionDropE?>
                        </select>                        
                    </td>
                    <td class="pickdrop_btn">
                        <button type="button" onclick="removeBlock(this)">X</button>
                    </td>
                </tr>
                
                
                    <?
                   

                }
                
                
                
                
                
                
                
                ?>
                
                
                
                
                
                
                
                
                
                
            </table>       
        
        
        
            
                                
                                
                                
                                
                                
                                
                                
                                
                                
               </div>
                         </div>
                        <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
                                  //$page->create_button("Cancella","Cancella","elimina","brain_cancella","button");
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    
                    <script type="text/x-mustache" id="template">
            <tr>
                <td class="pickdrop">
                    <select  name="pickup[]">
                       <?=$optionPickup?>
                    </select>                        
                </td>
                <td class="pickdrop">
                    <select  name="dropoff[]">
                      <?=$optionDropOff?>
                    </select>                        
                </td>
                <td class="pickdrop_btn">
                    <button type="button" onclick="removeBlock(this)">X</button>
                </td>
            </tr>
        </script>
        
        <script>
            var container_fermate = $('#container_fermate');
            var counter = 1;
            function addBlock(){
                
                var temp = $("#template").html();
                var block = Mustache.render(temp, { id: counter });
                container_fermate.prepend(block);
                counter++;
            }
            function removeBlock(el){
                var $this = $(el);
                $this.parent().parent().remove();
            }
        </script>
                    
                    </div>   
		</div>
<?  
}


function show_list()
{
global $user,$HtmlCommon,$db,$ModuloId, $dizionario;   
$HtmlCommon->html_titolo_pagina($dizionario['tipo_big']['titolo_elenco']);
$HtmlCommon->html_titolo_box($dizionario['tipo_big']['titolo_elenco']);
$db= new Database();
$db->connect();

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tratte_escluse','tratte_escluse.php?do=add',$dizionario['tipo_big']['aggiungi']);


include_once("tratte_escluse_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="5%"><?=$dizionario['generale']['stato']?></th>
            <th width="30%"><?=$dizionario['tipo_big']['aggiungi']?>tipologia biglietto</th>
            <th width="30%"><?=$dizionario['generale']['descrizione']?></th>
           <th width="10%"><?=$dizionario['generale']['peso']?></th>
            <th width="5%"><?=$dizionario['generale']['edita']?></th>
        </tr>
        <tr class="brain_tabellaFilter">
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="hidden" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="5" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
        </tr>
    </tbody>
    <tfoot> 
        <tr>
            <td colspan="5" ></td>
        </tr> 
    </tfoot> 
</table>

<?
   
}



if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
			 $do=$_REQUEST['do'];
			if(!isset($do)) 
			$do='';
		
		
			switch($do) {
                                
                               
                                case "edit":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           edit();
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                

				default:
				$FunzioneId=1;
                                show_list();    
                         		// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
			}
		
	} // end verifica permessi
	else {
            Errors::$ErrorePermessiModulo;
            
        }

} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>