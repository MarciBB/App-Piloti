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
include_once($classespath_."class.Scontistica.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.DT.php");

$ModuloId=35;

global $dizionario;

function addPromozioneCorsa($ListinoId)
{
	global $HtmlCommon,$user,$db;  

	$db = new Database();
	$db->connect();  
	$page = new Form();
	
	$HtmlCommon->html_titolo_pagina("Gestione promozione corsa",0,"","");
	$HtmlCommon->html_titolo_box ("Gestione promozione corsa");
	?>
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	
		<h2>Associazione Promozione/Corse</h2>
		<div class="brain_formModifica formGestoreEdita">
		
		<div class="GestoreSedeAdd"><a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=addScontisticaCorsa&ListinoId=<?=$ListinoId?>');" title="aggiungi / rimuovi corse in promozione"><i class="fa fa-plus" aria-hidden="true"></i> aggiungi / rimuovi corse in promozione</a></div>
		
		<br />
			<table width="100%" cellspacing="0" cellpadding="0" border="0"
				id="gestoreElencoAule">
				<tbody>
					<tr class="rowIntestazione">
						<td>Stato</td>
						<td>Dal</td>
						<td>Al</td>
						<td>Corse</td>
						<td>Edita</td>
					</tr>
			
					<?
					$sql="SELECT
RT_ScontisticaCorsa.ScontisticaCorsaId,
RT_ScontisticaCorsa.ListinoId,
RT_ScontisticaCorsa.Dal,
RT_ScontisticaCorsa.Al,
RT_ScontisticaCorsa.Stato,
RT_ScontisticaCorsaDettaglio.CorsaId,
RT_Corsa.CorsaNome,
RT_Linea.LineaNome
FROM
RT_ScontisticaCorsa
INNER JOIN RT_ScontisticaCorsaDettaglio ON RT_ScontisticaCorsa.ScontisticaCorsaId = RT_ScontisticaCorsaDettaglio.ScontisticaCorsaId
INNER JOIN RT_Corsa ON RT_ScontisticaCorsaDettaglio.CorsaId = RT_Corsa.CorsaId
INNER JOIN RT_Linea ON RT_Corsa.LineaId = RT_Linea.LineaId where ListinoId=$ListinoId";
	//	echo($sql);	
					$ArrObject = $db->fetch_array($sql);
					
					$validitaBiglietto = array();
					foreach ($ArrObject as $row) {
                                           
						if (isset($validitaBiglietto[$row['ScontisticaCorsaId']])) {
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Corse'] .= "<br /> Linea: " . $row['LineaNome']."- Corsa: ".$row['CorsaNome'];
						} else {
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Dal'] = $row['Dal'];
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Stato'] = $row['Stato'];
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Al'] = $row['Al'];
							$validitaBiglietto[$row['ScontisticaCorsaId']]['Corse'] = "Linea: ".$row['LineaNome']."- Corsa: ".$row['CorsaNome'];
						}
                                                
					}
					
					foreach ($validitaBiglietto as $ScontisticaCorsaId => $row) {
						
					?>
						<!-- QUI L'ELENCO DELLE FERMATE -->
						<tr class="rowBianca">
							<td><?php
								if($row['Stato'] == 1){
									?><span class="operatore_attivo"><?=$dizionario['generale']['attiva']?></span><?php 
								}else{
									?><span class="operatore_disattivo"><?=$dizionario['generale']['disattiva']?></span><?php
								}
							?></td>
							<td><span><?=$row['Dal']?></span></td>
							<td><span><?=$row['Al']?></span></td>
							<td><span><?=$row['Corse']?></span></td>
							<td><a title="edita" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=editScontisticaCorsa&ListinoId=<?=$ListinoId?>&ScontisticaCorsaId=<?= $ScontisticaCorsaId?>');" href="#"><img src="/images/edita_item.png" title="edita" alt="edita"></a></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
			<!-- FINE --> 
			<br />
			
			<div class="GestoreSedeAdd"><a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_scontistica_classe','listino_classe.php?do=addScontisticaCorsa&ListinoId=<?=$ListinoId?>');" title="aggiungi / rimuovi validit&agrave; biglietto"><i class="fa fa-plus" aria-hidden="true"></i> aggiungi / rimuovi validit&agrave; biglietto</a></div>
		</div>
	</div>
	<?
}

function editScontisticaCorsa($ListinoId, $ScontisticaCorsaId = null)
{
	global $HtmlCommon,$user,$db;  

	$db = new Database();
	$db->connect();  
	$page = new Form();
	$dt=new DT();
	
	include_once("listino_classe_validator.php"); 
	
	$validitaBiglietto = array();
	
	if($ScontisticaCorsaId != null){
	$sql="select * from RT_ScontisticaCorsa where ScontisticaCorsaId=$ScontisticaCorsaId";	
         $validitaBiglietto = $db->query_first($sql);
        
	}
	
	$HtmlCommon->html_titolo_pagina("Inserisci validit&agrave;",0,"","");
	$HtmlCommon->html_titolo_box ("Inserisci validit&agrave;");
	
	$corsa = new Corsa();
	$corsa->conn = $db;
	$elenco_corse = $corsa->getAll();
	?>
	<script type="text/javascript"> 
    $(document).ready(function() {
            
	   // Datepicker
		var d = new Date();
		$(function() {
			$( "#DataDalId" ).datepicker({
			monthNames:
					['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
					'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
					monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
					'Lug','Ago','Set','Ott','Nov','Dic'],
					monthStatus: 'Mostra un altro mese',
					yearStatus: 'Mostra un altro anno',
					weekHeader: 'Sm', weekStatus: '',
					weekStatus: 'Settimana dell\'anno',
			dayNames:
					['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
					dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
					dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
					dayStatus: 'Imposta DD come primo giorno della settimana',
					dateStatus: 'Seleziona DD, M d',
					dateFormat: 'dd/mm/yy', firstDay: 1,
					initStatus: 'Seleziona una data',
	                                dateFormat: 'dd/mm/yy'});
		
		    $( "#DataAlId" ).datepicker({
				monthNames:
						['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
						'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
						monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
						'Lug','Ago','Set','Ott','Nov','Dic'],
						monthStatus: 'Mostra un altro mese',
						yearStatus: 'Mostra un altro anno',
						weekHeader: 'Sm', weekStatus: '',
						weekStatus: 'Settimana dell\'anno',
				dayNames:
						['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
						dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
						dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
						dayStatus: 'Imposta DD come primo giorno della settimana',
						dateStatus: 'Seleziona DD, M d',
						dateFormat: 'dd/mm/yy', firstDay: 1,
						initStatus: 'Seleziona una data',
		                                dateFormat: 'dd/mm/yy'});
			});
	
    });
   </script>
    
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
			<form id="application_form" name="application_form" method="post" action="#">

				<div class="brain_data-content"><?
					if($ScontisticaCorsaId == null){
						$page->create_textbox_hidden("action","AggiungiPromozioneCorsa");
					}else{
						$page->create_textbox_hidden("action","AggiungiPromozioneCorsa");
						$page->create_textbox_hidden("ScontisticaCorsaId",$ScontisticaCorsaId);
					}
					$page->create_textbox_hidden("ListinoId",$ListinoId);
					if($ScontisticaCorsaId == null){
						$page->create_textbox("Dal:", "DataDalId", "RT_ScontisticaCorsa[Dal]", "", 1, "brain_campoForm", array("class"=>"'required italianDate'"), "", "10");
						$page->create_textbox("Al:", "DataAlId", "RT_ScontisticaCorsa[Al]", "", 1, "brain_campoForm", array("class"=>"'required italianDate'"), "", "10");
					}else{
						$page->create_textbox("Dal:", "DataDalId", "RT_ScontisticaCorsa[Dal]", $dt->format($validitaBiglietto["Dal"], "Y-m-d", "d/m/Y"), 1, "brain_campoForm", array("class"=>"'required italianDate'"), "", "10");
						$page->create_textbox("Al:", "DataAlId", "RT_ScontisticaCorsa[Al]", $dt->format($validitaBiglietto["Al"], "Y-m-d", "d/m/Y"), 1, "brain_campoForm", array("class"=>"'required italianDate'"), "", "10");
						$statoChecked = "";
						if($validitaBiglietto["Stato"] == 1){
							$statoChecked = array("checked"=>"'checked'");
						}
						$page->create_input_checkbox("Abilitata","StatoId","RT_ScontisticaCorsa[Stato]",1,null,"brain_campoForm",$statoChecked,"");
					}
					print("<br style=\"clear:both;\"/>");
					foreach ($elenco_corse as $biglietto) {
						if($ScontisticaCorsaId == null){
							$page->create_input_checkbox($biglietto['CorsaNome'],$biglietto['CorsaNome'],"ValiditaPromozioneCorsa[]",$biglietto['CorsaId'],null,"brain_campoForm","","");
						}else{
							$attribute = "";
                                                        $CorsaId1=$biglietto['CorsaId'];
                                                        $sql="select ScontisticaCorsaId from RT_ScontisticaCorsaDettaglio where ScontisticaCorsaId=$ScontisticaCorsaId and CorsaId=$CorsaId1";
                                                        $r = $db->query_first($sql);  
                                                        $exist=false;
                                                        if (!empty($r['ScontisticaCorsaId']))
                                                            $exist=true;
							if($exist){
								$attribute = array("checked"=>"'checked'");
							}
			                
							$page->create_input_checkbox($biglietto['LineaNome']." ".$biglietto['CorsaNome'],$biglietto['LineaNome']." ".$biglietto['CorsaNome'],"ValiditaPromozioneCorsa[]",$biglietto['CorsaId'],null,"brain_campoForm",$attribute,"");
						}
						print("<br style=\"clear:both;\"/>");
					}
				?></div>

				<div class="divSubmit"><?
					$page->create_button("Salva","Salva","salva","brain_salva","submit");
				?></div>
			</form>
		</div>
	</div>
	<?php
}


function add()
{
    global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  
$dt=new DT();
include_once("listino_classe_validator.php");       
 $HtmlCommon->html_titolo_pagina("Aggiungi classe di scontistica",0,"rt_scontistica_classe","listino_classe.php");

$HtmlCommon->html_titolo_box("Aggiungi classe di scontistica");  
$arr_stato[]= array("StatoId" => '0',"Stato" => 'Non Attivo');
$arr_stato[]= array("StatoId" => '1',"Stato" => 'Attivo');
$arr_is[]= array("ArrIsId" => '0',"ArrIs" => 'No');
$arr_is[]= array("ArrIsId" => '1',"ArrIs" => 'Si');
?>
<script type="text/javascript"> 
    $(document).ready(function() {
        
        
   // Datepicker
	var d = new Date();
	$(function() {
		$( "#DataDalId" ).datepicker({
		monthNames:
				['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
				monthStatus: 'Mostra un altro mese',
				yearStatus: 'Mostra un altro anno',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: 'Settimana dell\'anno',
		dayNames:
				['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
				dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
				dayStatus: 'Imposta DD come primo giorno della settimana',
				dateStatus: 'Seleziona DD, M d',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: 'Seleziona una data',
                                dateFormat: 'dd/mm/yy'});
	
    $( "#DataAlId" ).datepicker({
		monthNames:
				['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
				monthStatus: 'Mostra un altro mese',
				yearStatus: 'Mostra un altro anno',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: 'Settimana dell\'anno',
		dayNames:
				['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
				dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
				dayStatus: 'Imposta DD come primo giorno della settimana',
				dateStatus: 'Seleziona DD, M d',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: 'Seleziona una data',
                                dateFormat: 'dd/mm/yy'});


});
	

 });
        
</script>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action","create");
      
                 print("<br style=\"clear:both;\"/>");

                
                
                
                $page->create_textbox("Classe di sconto","ListinoNome","Listino[ListinoNome]","",1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                
                 print("<br style=\"clear:both;\"/>");
                 
                $page->create_textbox("Attiva dal:","DataDalId","Listino[AttivaDal]","",1,"brain_campoForm",array("class"=>"'required italianDate'"),"","10");
                $page->create_textbox("Attiva al:","DataAlId","Listino[AttivaAl]","",1,"brain_campoForm",array("class"=>"'required italianDate'"),"","10");
                 print("<br style=\"clear:both;\"/>");
              
                 
                 $page->create_textbox("Da Pax","Listino","Listino[DaPax]",0,1,"brain_campoForm campiformBig",array("class"=>"'required numeric'"));           
                 print("<br style=\"clear:both;\"/>");
                 
                 $page->create_textbox("A Pax","Listino","Listino[APax]",0,1,"brain_campoForm campiformBig",array("class"=>"'required numeric'"));           
                 print("<br style=\"clear:both;\"/>");
               
                $page->create_textbox("Peso","Peso","Listino[ListinoPeso]","",1,"brain_campoForm",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                $page->create_select("Stato","Listino[Stato]","StatoId","brain_campoForm",$arr_stato,1,"StatoId","Stato",
                array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
               
                
                
                
                print("<br style=\"clear:both;\"/>");
                ?>
                                      </div>
                         </div>
                             <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva","salva","brain_salva","submit");
                                  
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    </div>   
		</div>
                                    
                                    
<?
}



function edit($ListinoId)
{
include_once("listino_classe_validator.php");      


  global $HtmlCommon,$user;  

$db= new Database();
$db->connect();  
$page=new Form();  
$dt=new DT();

$Listino=new Scontistica($ListinoId);
$Listino->conn=$db;
$Listino->inizializzaDatiGenerali();
$arrListino=$Listino->DatiGenerali;



 $HtmlCommon->html_titolo_pagina("Edita classe di scontistica",0,"rt_scontistica_classe","listino_classe.php");
$HtmlCommon->html_titolo_box ("Edita classe di scontistica - ".$arrFermata['ListinoNome']);
$arr_stato[]= array("StatoId" => '0',"Stato" => 'Disattiva');
$arr_stato[]= array("StatoId" => '1',"Stato" => 'Attiva');
$arr_is[]= array("ArrIsId" => '0',"ArrIs" => 'No');
$arr_is[]= array("ArrIsId" => '1',"ArrIs" => 'Si');
?>
<script type="text/javascript"> 
    $(document).ready(function() {
        
        
   // Datepicker
	var d = new Date();
	$(function() {
		$( "#DataDalId" ).datepicker({
		monthNames:
				['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
				monthStatus: 'Mostra un altro mese',
				yearStatus: 'Mostra un altro anno',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: 'Settimana dell\'anno',
		dayNames:
				['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
				dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
				dayStatus: 'Imposta DD come primo giorno della settimana',
				dateStatus: 'Seleziona DD, M d',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: 'Seleziona una data',
                                dateFormat: 'dd/mm/yy'});
	
    $( "#DataAlId" ).datepicker({
		monthNames:
				['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
				monthStatus: 'Mostra un altro mese',
				yearStatus: 'Mostra un altro anno',
				weekHeader: 'Sm', weekStatus: '',
				weekStatus: 'Settimana dell\'anno',
		dayNames:
				['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
				dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
				dayStatus: 'Imposta DD come primo giorno della settimana',
				dateStatus: 'Seleziona DD, M d',
				dateFormat: 'dd/mm/yy', firstDay: 1,
				initStatus: 'Seleziona una data',
                                dateFormat: 'dd/mm/yy'});


});
	

 });
        
</script>
<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                         <div class="brain_formModifica">
                                <div class="brain_data-content">   
                <?
                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$ListinoId);
              
                ?>

        
<div id="elenco_comuni"></div>
    <?
    print("<br style=\"clear:both;\"/>");
                
                
               $page->create_textbox("Classe di listino","Listino","Listino[ListinoNome]",$arrListino['ListinoNome'],1,"brain_campoForm campiformBig",array("class"=>"'required'"));           
                 print("<br style=\"clear:both;\"/>");
                 
                  $page->create_textbox("Attiva dal:","DataDalId","Listino[AttivaDal]",$dt->format($arrListino['AttivaDal'], "Y-m-d", "d/m/Y"),1,"brain_campoForm",array("class"=>"'required italianDate'"),"","10");
                $page->create_textbox("Attiva al:","DataAlId","Listino[AttivaAl]",$dt->format($arrListino['AttivaAl'], "Y-m-d", "d/m/Y"),1,"brain_campoForm",array("class"=>"'required italianDate'"),"","10");
                
                 
                 $page->create_textbox("Da Pax","Listino","Listino[DaPax]",$arrListino['DaPax'],1,"brain_campoForm campiformBig",array("class"=>"'required numeric'"));           
                 print("<br style=\"clear:both;\"/>");
                 
                 $page->create_textbox("A Pax","Listino","Listino[APax]",$arrListino['APax'],1,"brain_campoForm campiformBig",array("class"=>"'required numeric'"));           
                 print("<br style=\"clear:both;\"/>");
                
                    
                      $page->create_textbox("Peso","Peso","Listino[ListinoPeso]",$arrListino['ListinoPeso'],1,"brain_campoForm",array("class"=>"'required'"));
                
                print("<br style=\"clear:both;\"/>");
                    
                $page->create_select("Stato","Listino[Stato]","StatoId","brain_campoForm",$arr_stato,$arrListino['Stato'],"StatoId","Stato",
                     array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
                ?>
            </div>
                         </div>
                        <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva","salva","brain_salva","submit");
                                  //$page->create_button("Cancella","Cancella","elimina","brain_cancella","button");
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    </div>   
		</div>
<?  
}


function show_list()
{
global $user,$HtmlCommon;
$HtmlCommon->html_titolo_pagina("Elenco sedi");
$HtmlCommon->html_titolo_box("Elenco sedi");
$db= new Database();
$db->connect();
include_once("sede_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="20%">gestore</th>
            <th width="20%">comune</th>
            <th width="25%">indirizzo</th>
            <th width="5%">telefono</th>
            <th width="5%">fax</th>
            <th width="10%">email</th>
            <th width="10%">codice</th>
            <th width="5%">edita</th>
        </tr>
        <tr class="brain_tabellaFilter">
            <th><span></span><input type="hidden" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="hidden" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="8" class="dataTables_empty">Caricamento in corso...</td>
        </tr>
    </tbody>
    <tfoot> 
        <tr>
            <td colspan="8" ></td>
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
			if(!isset($do)) 
			$do='';
		
		
			switch($do) {
                                
                                case "add":
					$FunzioneId=2;
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            add();
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;
                                        
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "edit":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           edit($_REQUEST['ListinoId']);
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                 case "addPromozioneCorsa":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           addPromozioneCorsa($_REQUEST['ListinoId']);
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "addScontisticaCorsa":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           editScontisticaCorsa($_REQUEST['ListinoId'],null);
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "editScontisticaCorsa":
				
                                $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           editScontisticaCorsa($_REQUEST['ListinoId'],$_REQUEST['ScontisticaCorsaId']);
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