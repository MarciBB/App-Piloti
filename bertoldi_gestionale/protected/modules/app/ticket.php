<?php 

$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include_app.php");

$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."/class.TipologiaBus.php");

include_once($classespath_."class.DT.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once ($classespath_ . "Graph/class.GraphUtil.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.GestioneOttimizzataFlotta.php");
include_once($classespath_."class.Autisti.php");
include_once($classespath_."class.Flotta.php");
include_once($classespath_."class.PreparazioneBusAutista.php");
include_once($classespath_."class.GestioneOttimizzataModifiche.php");
include_once($classespath_."class.Prenotazione.php");

$ModuloId=1;
global $db, $autista, $user;
$db=new Database();
$db->connect();

//controllo login
if(!isset($_SESSION['autista'])){
	header('Location: ./login.php');
}
$autista = $_SESSION['autista'];
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');


function creaBiglietto(){
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>                            
                                                        
			    <meta name="viewport" content="width=device-width, initial-scale=1.0">
			    <title>Bertoldi Boats - App Operatore</title>
			    
    			<link rel="manifest" href="./manifest.json">
			    
			    <link rel="stylesheet" type="text/css" href="/css/reset.css" />
			    <link rel="stylesheet" type="text/css" href="/css/style.css?v4" />
			    <link rel="stylesheet" type="text/css" href="./css/app.css" />
                <link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css"/>
			    <script type="text/javascript" src="/js/jquery.min.js"></script>
                <script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
                <script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
			    <script type="text/javascript" src="/js/menu_hover.js"></script> 
			    <script type="text/javascript" language="javascript" src="/js/dialogbox.js?v=3"></script>
			    <script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
			    <script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
                <script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>
                              
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
                <script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
                              
                <!-- Bootstrap CSS -->
    			<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
                <!-- jQuery first, then Popper.js, then Bootstrap JS -->
			    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
			    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
			                  
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
	    	    <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
	    	    <script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
	    	    
	    	    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
                
                <script> 
					$(function() {
						$( "#catalog" ).accordion({
							autoHeight: false,
							navigation: true
						});
						
				                $( "#catalog tr" ).draggable({
							appendTo: "body"
							//helper: "clone"
						});
				                
				                $( "#catalog1" ).accordion({
							autoHeight: false,
							navigation: true
						});
						
						$( ".accordion_bus" ).accordion({
							autoHeight: false,
							navigation: true,
							collapsible: true,
							autoHeight: false,
							navigation: true,
							header: '.accordion_header'
						});
						
						$( "#catalog1 tr" ).draggable({
							appendTo: "body"
						});

						
					});
				</script>
                              
                <style>
                	#accordion_content_custom .ui-accordion .ui-accordion-content { 
                		padding: 1em 1em!important;
                	}
                </style>                      
                              
                              
                              
                            
				<!--[if lte IE 8]>
				<link rel="stylesheet" href="/css/styleIE.css" type="text/css" />
				<![endif]-->    
                                
					<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
					<link rel="stylesheet" href="/css/home.css" type="text/css" />
					<link rel="stylesheet" href="/css/home_2.css" type="text/css" />
                  	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">              
			</head>
			<body>


 

<!-- Fine Live Search -->
<?php 


    $codiceControllo='gC_6XHrZvC9$avW!';
     echo $_POST['CorsaId'];
    if (!isset($_POST['CorsaId']))
    {
    	$code=$_REQUEST['code'];
	    if ($code!=$codiceControllo){
	    	header('Location: ./login.php');
	        die("servizio non disponibile");
	    }
    }
   
	global $user,$HtmlCommon, $dizionario, $autista;
	
    $user = new Operatore();
    $user->OperatoreId=1;
    $user->OdcId=1;
    $user->IsAdmin=1;
    $user->GestoreId=1;
	$DataPartenza=$_REQUEST['DataPartenza'];
	$CorsaId=$_REQUEST['CorsaId'];
	$latitudine=$_REQUEST['latitudine'];
	$longitudine=$_REQUEST['longitudine'];
	
	$datacorrente=Date('d/m/Y', strtotime($DataPartenza));
	$CorsaId=1;
        
	if (isset($_POST['CorsaId'])){
		$CorsaId=$_POST['CorsaId'];
	}

	$all=true;
	if (isset($_REQUEST['all_route']))
		$all=true;
	if (isset($_POST['all_route']))
		$all=true;
        
	include_once("previaggio_validator.php");
	$db = new Database();
	$db->connect();
	$corsaobj=new Corsa($CorsaId);
	$corsaobj->conn=$db;
	$corsaobj->inizializzaDatiGenerali();
	$arr_corsa=$corsaobj->DatiGenerali;
	
	$titolo = $arr_corsa['CorsaNome']." del ".$datacorrente;

	//$HtmlCommon->html_titolo_box($titolo);
	$page = new Form();
	$lineaId = $arr_corsa['LineaId'];
    
   	$sql="Select CorsaId,CorsaNome from RT_Corsa where LineaId=1 and Stato=1 and Cancella=0 and AttivaAl>=CURRENT_DATE() order by CorsaPeso asc";
	$arr_corse=$db->fetch_array($sql);
	
	
	//filtro sul tempo se la data selezionata e' quella di oggi
	if(date('Y-m-d') == $DataPartenza){
		$timeSql = '';
		//calcolo ritardo
		$ritardo = 60; //minuti
		$time = date('H:i');
		$timestamp = strtotime($time) - $ritardo*60;
		$time = date('H:i', $timestamp);
		$timeSql = " and ((o.Orario > '$time' and o.GiorniAggiuntivi = 0) or o.GiorniAggiuntivi = 1)";
	}
	$pickupId = 0;
	//recupero fermata pi� vicina a posizione GPS

	if($latitudine > 0 && $longitudine > 0){
		$sql = "select f.FermataId, c.ComuneId from RT_Fermata f
				left join RT_Orario o on f.FermataId = o.FermataId
				left join Comune c on c.ComuneId = f.ComuneId
				left join RT_Tratta t on t.TrattaId = f.TrattaId
				where o.CorsaId = $CorsaId
				and o.Orario IS NOT NULL and o.Orario <> '' and o.Stato = 1 and o.Cancella = 0
				and f.Stato = 1 and f.Cancella = 0 and f.IsPickup = 1 and t.TrattaPrincipaleId IS NULL
				group by c.ComuneId
				order by  ( 3959 * acos( cos( radians(".$latitudine.") ) * cos( radians( latitudine ) )
				* cos( radians( longitudine ) - radians(".$longitudine.") ) + sin( radians(".$latitudine.") ) * sin(radians(latitudine)) ) ) ASC";
				$tempPickup = $db->query_first($sql);
		$pickupId = $tempPickup['ComuneId'].'_'.$tempPickup['FermataId'];

	} else {
		$sql = "select f.FermataId, c.ComuneId from RT_Fermata f
		left join RT_Orario o on f.FermataId = o.FermataId
		left join Comune c on c.ComuneId = f.ComuneId
		left join RT_Tratta t on t.TrattaId = f.TrattaId
		where o.CorsaId = $CorsaId
		and o.Orario IS NOT NULL and o.Orario <> '' and o.Stato = 1 and o.Cancella = 0
		and f.Stato = 1 and f.Cancella = 0 and f.IsPickup = 1  and t.TrattaPrincipaleId IS NULL
		$timeSql
		group by c.ComuneId
		order by  o.GiorniAggiuntivi ASC, o.Orario ASC, f.TrattaId";
		$tempPickup = $db->query_first($sql);
		$pickupId = $tempPickup['ComuneId'].'_'.$tempPickup['FermataId'];
	}
	
	//recupero fermate pickup
	$sql = "select f.FermataId, f.FermataNome, c.Comune, c.ComuneId, MIN(o.Orario) as Orario, o.GiorniAggiuntivi, f.TrattaId, p.RegioneId 
			from RT_Fermata f 
			left join RT_Orario o on f.FermataId = o.FermataId
			left join Comune c on c.ComuneId = f.ComuneId
			left join Provincia p on c.provincia = p.ProvinciaId
			left join RT_Tratta t on t.TrattaId = f.TrattaId
			where o.CorsaId = $CorsaId
			and o.Orario IS NOT NULL and o.Orario <> '' and o.Stato = 1 and o.Cancella = 0 
			and f.Stato = 1 and f.Cancella = 0 and f.IsPickup = 1 and t.TrattaPrincipaleId IS NULL
			group by c.ComuneId
            order by c.Comune ASC";
			//order by  o.GiorniAggiuntivi ASC, MIN(o.Orario) ASC, f.TrattaId";
	$partenza = $db->fetch_array($sql);
	
	$arr_partenza = array();
	foreach($partenza as $p){
		$orario = substr($p['Orario'], 0, 5);
		$entry = $p['ComuneId'].'_'.$p['FermataId'];
// 		$arr_partenza[] = array('pickupId'=>$entry, 'pickup'=>$orario.' '.$p['Comune'].' - '.$p['FermataNome']);
		$arr_partenza[] = array('pickupId'=>$entry, 'pickup'=>$p['Comune'].' - '.$orario);
		if($entry == $pickupId){
			//primo pickup selezionato
			$primoPickup = $p;
		}
	}
	if($pickupId == '_'){
		$primoPickup = $partenza[0];
	}
	
	//recupero le fermate dropoff
	$sql = "select f.FermataId, f.FermataNome, c.Comune, c.ComuneId, MAX(o.Orario) as Orario, o.GiorniAggiuntivi, f.TrattaId, p.RegioneId 
			from RT_Fermata f
			left join RT_Orario o on f.FermataId = o.FermataId
			left join Comune c on c.ComuneId = f.ComuneId
			left join Provincia p on c.provincia = p.ProvinciaId
			left join RT_Tratta t on t.TrattaId = f.TrattaId
			where o.CorsaId = $CorsaId
			and o.Orario IS NOT NULL and o.Orario <> '' and o.Stato = 1 and o.Cancella = 0 and t.TrattaPrincipaleId IS NULL
			and f.Stato = 1 and f.Cancella = 0 and f.IsDropOff = 1"; 
			if(!Config::$venditaStessaRegione) {
				$sql .= " and p.RegioneId <> ".$primoPickup['RegioneId'];
			}
			$sql .= " group by c.ComuneId
            order by c.Comune ASC";
			//order by  o.GiorniAggiuntivi ASC, MAX(o.Orario) ASC, f.TrattaId";
	$destinazione = $db->fetch_array($sql);

	$arr_destinazione = array();
	$arr_destinazione[] = array('dropoffId'=>0, 'dropoff'=>'- Seleziona Destinazione -');
	foreach($destinazione as $p){
		$orario = substr($p['Orario'], 0, 5);
		//$arr_destinazione[] = array('dropoffId'=>$p['ComuneId'].'_'.$p['FermataId'], 'dropoff'=>$orario.' '.$p['Comune'].' - '.$p['FermataNome']);
		$arr_destinazione[] = array('dropoffId'=>$p['ComuneId'].'_'.$p['FermataId'], 'dropoff'=>$p['Comune'].' - '.$orario);
	}
	$arr_posti = array();
	for ($i = 1; $i <= 10; $i++) {
		$arr_posti[] = array('postiId'=>$i, 'posti'=>$i);
	}
	$arr_congiunti = array();
	$arr_congiunti[] = array('congiuntiId'=>0, 'congiunti'=>'No');
	$arr_congiunti[] = array('congiuntiId'=>1, 'congiunti'=>'Si');
	?>
	
	<div class="row userInfo" style="padding:5px; margin-right:0px">
		<div class="col-sm-6 col-6 right">Benvenuto <?php echo $autista['Nome']." ".$autista['Cognome'];?></div>
		<div class="col-sm-6 col-6"><button type="button" class=" float-right btn btn-secondary btn-sm" id="logout"> <i class="fa fa-sign-out" aria-hidden="true"></i> Logout</button></div>
	</div>                           

	<div id="brain_loading"><?php echo $dizionario['generale']['caricamento_dati'];?></div>

	<div id="layer_nero2" class="notifica">
		<div id="loading_big_loading"><img src="/images/ajax-loader.gif" alt="<?php echo $dizionario['generale']['caricamento_attendere']; ?>" /><p><?php echo $dizionario['generale']['caricamento_attendere'];?></p></div>
	</div>
	<!-- loading div -->

	<div id="layer_nero" class="notifica">
		<div id="loading_big_loading"><img src="/images/ajax-loader.gif" alt="<?php echo $dizionario['generale']['caricamento_attendere'];?>" /><p><?php echo $dizionario['generale']['caricamento_attendere'];?></p></div>
	</div>
	<div id="loading_big_ok" class="notifica">
		<div id="loading_big_loading"><img src="/images/loading_ok.png" alt="<?php echo $dizionario['generale']['operazione_completata'];?>" /><p><?php echo $dizionario['generale']['operazione_completata'];?></p></div>
	</div>
	<div id="loading_big_no" class="notifica">
		<div id="loading_big_loading"><img src="/images/loading_err.png" alt="<?php echo $dizionario['generale']['errore_operazione'];?>" /><p><?php echo $dizionario['generale']['errore_operazione'];?></p></div>
	</div>
	<!-- loading div -->
                                    
	<div id="brain_oscura" class="brain_oscura">
		<div id="brain_boxCentrato_mobile">
			<div id="brain_listaSelezione" class="brain_dialogbox"></div>
			<br style="clear:both;"/>
		</div>
	</div>   
                            
	<div id="brain_oscura_pre" class="brain_oscura"></div>  
	
	<div class="brainFiltri">
		<form   method="post" action="index.php">
			<?php if ($all) { ?>
				<input type="hidden" name="all_route" value="true" />
			<?php }
			$page->create_select_no_default($dizionario['generale']['corsa'],"CorsaId","CorsaId","rowForm",$arr_corse,$CorsaId,"CorsaId","CorsaNome",
		    	array("class"=>"'required custom-select'", 'disabled'=>'disabled'),1);
		 
			if ($all) { ?>
			<div class="rowForm">
				<label for="Dal"><?=$dizionario['generale']['data_corsa']?></label>
				<input class="required" type="text" value="<?=$datacorrente?>" id="Dal" name="Dal" maxlength="255" size="10" disabled='disabled'>
			</div>
			<?php } else { ?>
				<input type="hidden" name="Dal" value="<?=$datacorrente?>" />   
			<?php } ?>
			<div class="rowForm">
				<button type="submit" class="btn btn-primary btn-sm" name="applica" id="applica" style="display:none;">Cerca</button>
			</div>
			<br style="clear:both;" />
		</form>
		<form   method="post" action="index.php">
			<input type="hidden" name="CorsaId" value="<?=$CorsaId?>"/>
			<input type="hidden" name="DataPartenza" value="<?=$DataPartenza?>"/>
			<button type="submit" class="btn btn-secondary btn-sm" id="indietro" name="indietro"><i class="fa fa-arrow-left" aria-hidden="true"></i> Indietro</button>
		</form>
	</div>
	<div class="text-center">
                   
    <?php $HtmlCommon->html_titolo_pagina($titolo, null, null, null); ?>
    	<?php if ($CorsaId>0 && count($arr_partenza)>0) { ?>
        	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
				<div class="brain_formModifica formGestoreEdita" style="width: 100%!important;padding:0px!important;border:none!important;background: none!important;">
					<div id="result"></div>
					<form id="ticketForm">
						<input type="hidden" name="CorsaId" value="<?=$CorsaId?>"/>
						<input type="hidden" name="DataPartenza" value="<?=$DataPartenza?>"/>
						<input type="hidden" name="action" value="prenotaBigliettoABordo"/>
						<input type="hidden" name="AutistaId" value="<?=$autista['AutistiId']?>"/>
						
						
						<?php 
						$page->create_select_no_default($dizionario['generale']['partenza'],"PickupId","PickupId","rowForm",$arr_partenza,$pickupId,"pickupId","pickup",
								array("class"=>"'required custom-select'", "style"=>"width:100%!important;"),1);
						echo '<br style="clear:both;" />';
						$page->create_select_no_default($dizionario['generale']['destinazione'],"DropoffId","DropoffId","rowForm",$arr_destinazione,$dropoffId,"dropoffId","dropoff",
								array("class"=>"'required custom-select'", "style"=>"width:100%!important;"),1);
						echo '<br style="clear:both;" />';
						$page->create_select_no_default($dizionario['partenza']['num_posti'],"Posti","Posti","rowForm",$arr_posti,1,"postiId","posti",
								array("class"=>"'required custom-select'", "style"=>"width:100%!important;"),1);
						echo '<br style="clear:both;" />';
						if(Config::$opzioneCongiunti) {
							$page->create_select_no_default($dizionario['generale']['congiunti'],"Congiunti","Congiunti","rowForm",$arr_congiunti,0,"congiuntiId","congiunti",
									array("class"=>"'required custom-select'", "style"=>"width:100%!important;"),1);
							echo '<br style="clear:both;" />';
						} else { ?>
							<input type="hidden" name="Congiunti" id="Congiunti" value="0"/>
						<?php }
							$page->create_textbox($dizionario['biglietto']['email'],"Email","Email",$email,1,"rowForm",array("class"=>"'email custom-select width100'"));
						?>
						<div id="nofermata"></div>
						<div><h1><b>Totale: <span id="totaleBiglietti">-</span></b></h1></div>
						<button type="button" class="btn btn-primary" id="emetti" name="emetti" disabled='disabled'><i class="fa fa-check" aria-hidden="true"></i> Prenota</button>
					</form>
					<br>
					<form id="pagamentoForm" style="display:none;">
						<input type="hidden" name="CorsaId" value="<?=$CorsaId?>"/>
						<input type="hidden" name="DataPartenza" value="<?=$DataPartenza?>"/>
						<input type="hidden" name="action" value="pagamentoABordo"/>
						<input type="hidden" name="AutistaId" value="<?=$autista['AutistiId']?>"/>
						<input type="hidden" name="PrenotazioneId" id="PrenotazioneId" value=""/>
						
		
						<div id="nofermata">Prenotazione effettuate correttamente. Pagamento effettuato?</div>
						<br>
						<button type="button" id="pagamentoConferma" class="btn btn-success" data-toggle="modal" data-target="#confermaPagamento"><i class="fa fa-ticket" aria-hidden="true"></i> Incasso avvenuto</button>
						<br><br>
						<button type="button" id="pagamentoAnnulla" class="btn btn-danger" data-toggle="modal" data-target="#annullaPagamento"><i class="fa fa-ban" aria-hidden="true"></i> Errore incasso</button>
					</form>
					
				</div>	
	        </div>
     
        <?php } else { ?>
        <h3>Nessuna fermata prenotabile</h3>
        <?php } ?>
	</div>
	
	<!-- modal di conferma --> 
	<div class="modal fade" id="confermaPagamento" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel">Conferma Pagamento</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        Confermi di aver ricevuto l'incasso?<br>Confermando l'operazione il biglietto sar&agrave; emesso.
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
	        <button id="pagamentoOk" name="pagamentoOk" type="button" class="btn btn-primary">Conferma</button>
	      </div>
	    </div>
	  </div>
	</div>
	<div class="modal fade" id="annullaPagamento" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel">Annulla Pagamento</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        Vuoi annullare la prenotazione?
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
	        <button id="pagamentoErrore" name="pagamentoErrore" type="button" class="btn btn-primary">Si</button>
	      </div>
	    </div>
	  </div>
	</div>
		<script type="text/javascript">
		    $(document).ready(function() {
		    	
			   	$('#logout').click(function(){
			    	var formData = {
							action:"logoutBrowser",
					};
			
					$.ajax({
			    		  url: '<?php echo Config::$UrlMobile; ?>',
			    		  type: "POST",
			    		  data : formData,
			    		  dataType: 'json',
			    		  success: function(responce){
			    				window.location = "./login.php";  
			    		  },
			    		  error: function(xhr, ajaxOptions, thrownError) {
			    		        
			    		  },
			    		});
			    });

			   	
			   	$('#PickupId').change(function(){
			   		$('#DropoffId').val(0);
					var formData = {
							action:"getDropoffABordo",
							PickupId: $('#PickupId').val(),
							CorsaId: $('input[name=CorsaId]').val(),
							DataPartenza: $('input[name=DataPartenza]').val(),
					};
					$.ajax({
			    		  url: '<?php echo Config::$UrlMobile; ?>',
			    		  type: "POST",
			    		  data : formData,
			    		  dataType: 'json',
			    		  success: function(responce){
			    			  $('#totaleBiglietti').html('-');
			    			  $('#emetti').prop('disabled', true);
			    			  $('#nofermata').html("");
			    			  var html = "";//"<option value='0'>- Seleziona Destinazione -</option>";
			    			  $.each(responce.result, function(k, v) {
			    			        html += "<option value='"+v.dropoffId+"'>"+v.dropoff+"</option>";
			    			    });
			    			  $('#DropoffId').html(html);
			    			  $('#DropoffId').val(0);
			    			  
			    		  },
			    		  error: function(xhr, ajaxOptions, thrownError) {
			    		        alert(
			                		  "L'applicazione non riesce a comunicare con il server"       
			        		  	);
			    		  },
			    		});
					
					
				});

				$('#PickupId, #DropoffId, #Posti, #Congiunti').change(function(){
					if($('#PickupId').val() != 0 && $('#DropoffId').val() != 0 && $('#Posti').val() != 0){
						var formData = {
							action:"getTotaleABordo",
							PickupId: $('#PickupId').val(),
							DropoffId: $('#DropoffId').val(),
							Posti: $('#Posti').val(),
							CorsaId: $('input[name=CorsaId]').val(),
							Congiunti: $('#Congiunti').val(),
							DataPartenza: $('input[name=DataPartenza]').val(),
						};
						$.ajax({
				    		  url: '<?php echo Config::$UrlMobile; ?>',
				    		  type: "POST",
				    		  data : formData,
				    		  dataType: 'json',
				    		  success: function(responce){
				    			  $('#pagamentoForm').hide();
				    			  if(responce.error != "") {
				    				  $('#totaleBiglietti').html('-');
					    			  $('#emetti').prop('disabled', true);
					    			  $('#nofermata').html("<b>"+responce.error+"</b>");
				    			  } else {
						    		  if(responce.totale){
						    		  		var importo = parseFloat(responce.totale).toFixed(2);
											$('#totaleBiglietti').html(importo+'&euro;');
											$('#emetti').removeProp('disabled');
											$('#nofermata').html("");
										} else {
						    			  $('#totaleBiglietti').html('-');
						    			  $('#emetti').prop('disabled', true);
						    			  $('#nofermata').html("<b>Non disponibile</b>");
						    		  }
				    			  }
				    			  
				    		  },
				    		  error: function(xhr, ajaxOptions, thrownError) {
				    		        alert(
				                		  "L'applicazione non riesce a comunicare con il server"       
				        		  	);
				    		        $('#emetti').prop('disabled', true);
				    		        $('#totaleBiglietti').html('-');
				    		        $('#nofermata').html("");
				    		        $('#pagamentoForm').hide();
				    		  },
				    		});
					} else {
						$('#totaleBiglietti').html('-');
					}
					
				});
			    
			   	$('#emetti').click(function(){
					$.ajax({
			    		  url: '<?php echo Config::$UrlMobile; ?>',
			    		  type: "POST",
			    		  data: $("#ticketForm").serialize(),
			    		  dataType: 'html',
			    		  success: function(responce){
			    				$('#pagamentoForm').show();
			    				$('#emetti').hide();
			    				$('#Email').prop('disabled', true);
			    				$('#Posti').prop('disabled', true);
			    				$('#DropoffId').prop('disabled', true);
			    				$('#PickupId').prop('disabled', true);
			    				$('#indietro').prop('disabled', true);
			    				var res = responce.split("_");
			    				$('#PrenotazioneId').val(res[2]);
			    		  },
			    		  error: function(xhr, ajaxOptions, thrownError) {
			    		        alert(
			                		  "L'applicazione non riesce a comunicare con il server"       
			        		  	);
			    		        $('#pagamentoForm').hide();
			    		        $('#emetti').show();
			    				$('#Email').removeProp('disabled');
			    				$('#Posti').removeProp('disabled');
			    				$('#DropoffId').removeProp('disabled');
			    				$('#PickupId').removeProp('disabled');
			    				$('#indietro').removeProp('disabled');
			    		  },
			    		});
			    });
			   	$('#pagamentoErrore').click(function(){
			   		var formData = {
							action:"annullaABordo",
							AutistaId: $('input[name=AutistaId]').val(),
							PrenotazioneId: $('#PrenotazioneId').val(),
						};
					$.ajax({
			    		  url: '<?php echo Config::$UrlMobile; ?>',
			    		  type: "POST",
			    		  data: formData,
			    		  dataType: 'html',
			    		  success: function(responce){
			    			  $('#annullaPagamento').modal('toggle');
								$('#pagamentoForm').hide();
								$('#emetti').show();
				    			$('#Email').removeProp('disabled');
				    			$('#Posti').removeProp('disabled');
				    			$('#DropoffId').removeProp('disabled');
				    			$('#PickupId').removeProp('disabled');
				    			$('#indietro').removeProp('disabled');
				    			var html= '<div class="alert alert-warning alert-dismissible" role="alert">';
				    			html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
				    			html += 'Prenotazione annullata!';
				    			html += '</div>';
				    			$('#result').html(html);
				    		  
			    		  },
			    		  error: function(xhr, ajaxOptions, thrownError) {
			    		        alert(
			                		  "L'applicazione non riesce a comunicare con il server"       
			        		  	);
			    		  },
			    		});
			   	});
			   	$('#pagamentoOk').click(function(){
			   		var formData = {
							action:"emettiABordo",
							AutistaId: $('input[name=AutistaId]').val(),
							PrenotazioneId: $('#PrenotazioneId').val(),
							CorsaId: $('input[name=CorsaId]').val(),
							DataPartenza: $('input[name=DataPartenza]').val(),
						};
					$.ajax({
			    		  url: '<?php echo Config::$UrlMobile; ?>',
			    		  type: "POST",
			    		  data: formData,
			    		  dataType: 'html',
			    		  success: function(responce){
			    			  $('#confermaPagamento').modal('toggle');
								$('#pagamentoForm').hide();
								$('#emetti').show();
				    			$('#Email').removeProp('disabled');
				    			$('#Posti').removeProp('disabled');
				    			$('#DropoffId').removeProp('disabled');
				    			$('#PickupId').removeProp('disabled');
				    			$('#indietro').removeProp('disabled');
				    			var html= '<div class="alert alert-success alert-dismissible" role="alert">';
				    			html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
				    			html += 'Prenotazione eseguita! Biglietti saranno inviati per email.';
				    			html += '</div>';
				    			$('#result').html(html);
				    		  
			    		  },
			    		  error: function(xhr, ajaxOptions, thrownError) {
			    		        alert(
			                		  "L'applicazione non riesce a comunicare con il server"       
			        		  	);
			    		  },
			    		});
			   	});
		   		// Datepicker
				var d = new Date();
			        $(function() {
					$( "#Dal" ).datepicker({
		                             minDate : 0,
		                             maxDate : '+10D',
						monthNames:
							[<?=$dizionario['generale']['nome_mesi']?>],
							monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
							monthStatus: '<?=$dizionario['generale']['mese_status']?>',
							yearStatus: '<?=$dizionario['generale']['anno_status']?>',
							weekHeader: 'Sm', weekStatus: '',
							weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
						dayNames:
							[<?=$dizionario['generale']['nome_giorni']?>],
							dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
							dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
							dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
							dateStatus: '<?=$dizionario['generale']['data_status']?>',
							dateFormat: 'dd/mm/yy', firstDay: 1,
							initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
				            dateFormat: 'dd/mm/yy'
					});
				});
		 });
		
		</script>
	</body>
</html>
	<?php
	$db->close();
}




if(is_array($autista)) {
    $db= new Database();
    $db->connect();
    $user = new Operatore();
    $user->OperatoreId=1;
    $user->OdcId=1;
    $user->IsAdmin=1;
    $user->GestoreId=1;
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
	if(!isset($_REQUEST['do'])){
    	$do='';
    } else {
    	$do=$_REQUEST['do'];
    }
	
	switch($do) {
		default:
			creaBiglietto();    
			break;
		}
} 
// se l'utente non e' loggato
else {
	header('Location: ./login.php');
}
?>