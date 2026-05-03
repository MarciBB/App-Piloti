<?php

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['chi_siamo']['titolo'];
$PageDescription = $dizionario['chi_siamo']['descr'];
$PageKeywords = $dizionario['chi_siamo']['key'];

$LinkActive = 4;

$page_title = $dizionario['chi_siamo']['titolo'];
$page_parent = $dizionario['chi_siamo']['descr'];

if ( ! session_id() ) {
   session_start();
}
$userInfo = $_SESSION['USER'];

if(!isset($userInfo) || $userInfo == "" ){
    
    header('location: /area-clienti.php');
    die();
}

if(!isset($_GET['titoloId'])){

	header('location: /mie-prenotazioni.php');
	die();
}

$LinkActive = 4;



global $search;



$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
$userInfo = $_SESSION['USER'];

global $db;
$db = new Database();
$db->connect();

$titoloid = $_GET['titoloId'];
$sql = "SELECT * FROM RT_Prenotazione p
		left join RT_PrenotazioneTitolo pt on p.PrenotazioneId = pt.Prenotazioneid
		left join RT_PrenotazioneDettaglio d on p.PrenotazioneId = d.Prenotazioneid and d.PrenotazioneId = pt.PrenotazioneId
		where p.MembershipClubCode = '".$userInfo['MembershipClubCode']."' and pt.PrenotazioneTitoloId = $titoloid";
 
$rowTitolo = $db->query_first($sql);

if ($rowTitolo['TipoTitolo'] == 'E') {

	$sql="select distinct Tragitto, RT_PrenotazioneDettaglio.LineaId,  RT_PrenotazioneDettaglio.DataPartenza,  RT_PrenotazioneDettaglio.OrarioPartenza 
			from RT_PrenotazioneDettaglio 
			where PrenotazioneNumero=".$rowTitolo['PrenotazioneNumeroId']." and Escludi=0";
	
	$ArrNumeroTragitti = $db->fetch_array($sql);
	$countTragitti = sizeof($ArrNumeroTragitti);

} else {
	header('location: /mie-prenotazioni.php');
	die();
}

//calcolo penale
$sql = "SELECT RimborsoRegolaId, NomeRegola, GiorniPrima, OrePrima FROM RT_RimborsoRegola
			Where TipoPrenotazione like '%N%' and Stato=1
			order by GiorniPrima desc, OrePrima desc";
$regole = $db->fetch_array($sql);

$dateSoglia = array();
$dataInizioCorsa = $ArrNumeroTragitti[0]['DataPartenza']." ".$ArrNumeroTragitti[0]['OrarioPartenza'];
foreach ($regole as $key => $row){
	$datetime = new DateTime($dataInizioCorsa);
	$datetime->modify('-'.$row['GiorniPrima'].' day');
	$datetime->modify('-'.$row['OrePrima'].' hour');
	$dateSoglia[$row['RimborsoRegolaId']] = $datetime;
}
$today = new DateTime();
$selectDate = $regole[0]['RimborsoRegolaId'];
foreach ($dateSoglia as $id => $dataCheck){
	if($today > $dataCheck){
		$selectDate = $id;
	}
}
$sql = "Select * FROM RT_RimborsoPenale
	left join RT_RimborsoRegola on (RT_RimborsoRegola.RimborsoRegolaId = RT_RimborsoPenale.RimborsoRegolaId)
	where RT_RimborsoPenale.LineaId = 1 and RT_RimborsoPenale.RimborsoRegolaId = $selectDate";

$penale = $db->fetch_array($sql);
$importoRimborso = $rowTitolo['ImportoTitolo'] - ($rowTitolo['ImportoTitolo']* $penale[0]['Percentuale']/100) - $penale[0]['Fisso'];
?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>

<body class="home page-template page-template-homepage page-template-homepage-php page page-id-1036 fullwidth-bg wpb-js-composer js-comp-ver-5.0 vc_responsive">
	
    <!-- Wrapper
	================================================== -->
	<div id="page" class="hfeed site fullwidth">
    	
        
        
        <!-- Top Header
        ================================================== -->
        <? include_once($basepath."/include/top_header.php") ?> 
        
        <section id="main" class="clearfix">
            <div class="container">
            	<div id="content" class="site-content" role="main">
                
                    <!-- Main
                    ================================================== -->
                	<div class="main_content_article">
                        <br>
                    	<ul class="breadcrumb">
                            <li><a href="/area-clienti.php"><?=$dizionario['area_clienti']['area']?></a></li>
                            <li><?=$dizionario['area_clienti']['rimporso_biglietto']?>: <?=$rowTitolo['Codice']?></li>
                        </ul>
                        
                        <h4><?=$dizionario['area_clienti']['rimborso_info']?></h4>
                                <p>
                                	<strong><?=$dizionario['area_clienti']['data']?>:</strong> <?=$rowTitolo['DataIns']?><br>
                                	<strong><?=$dizionario['area_clienti']['codice']?>:</strong> <?=$rowTitolo['CodicePrenotazione']?><br>
                                	<strong><?=$dizionario['area_clienti']['partenza']?>:</strong> <?=$rowTitolo['DataInizioItinerario']?><br>
                                	<strong><?=$dizionario['area_clienti']['DA']?>:</strong> <?=$rowTitolo['ComunePartenza']?><br>
                                	<strong><?=$dizionario['area_clienti']['A']?>:</strong> <?=$rowTitolo['ComuneArrivo']?><br>
                                	<strong><?=$dizionario['area_clienti']['tipo']?>:</strong> <?php if($rowTitolo['TipoViaggioId'] == 1) 
				                                			echo "CS"; 
				                                		else
				                                			echo "A/R" ?><br>
                                	
                                
                                	<strong><?=$dizionario['area_clienti']['codice_big']?>:</strong> <?=$rowTitolo['Codice']."/".$rowTitolo['Anno']?><br>
	                                <strong><?=$dizionario['area_clienti']['importo']?>:</strong> <?=$rowTitolo['ImportoTitolo']?>&euro;<br>
	                                <strong><?=$dizionario['area_clienti']['biglietto']?>:</strong> <?=$rowTitolo['TipologiaBiglietto']?><br>
	                                <strong><?=$dizionario['area_clienti']['intestato']?>:</strong> <?=$rowTitolo['Cognome']." ".$rowTitolo['Nome']?><br>
                                </p>
                                <h4><?=$dizionario['area_clienti']['rimborso_coupon']?></h4>
                                <?php if($importoRimborso <= 0) {?>
                                	<p>Impossibile eseguire il rimborso per questo biglietto.</p>
                                <?php } else { ?>
	                                <?php if($penale == 0) {?>
	                                	<p><?=$dizionario['area_clienti']['rimborso_coupon_msg']?></p>
	                                <?php } else { ?>
	                                	<p><?=$dizionario['area_clienti']['rimborso_coupon_msg_penale']?> <?php echo $penale[0]['Fisso'];?>&euro; e <?php echo $penale[0]['Percentuale'];?>%<br>
	                                	<?=$dizionario['area_clienti']['rimborso_coupon_msg_penale_2']?></p>	
	                                <?php }?>
	                                <?php 
	                                $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['Andata-Ritorno']);
	                                $arr_stato[]= array("StatoId" => 'Andata',"Stato" => $dizionario['Andata']);
	                                $arr_stato[]= array("StatoId" => 'Ritorno',"Stato" => $dizionario['Ritorno']);
	                                if ($countTragitti<2)
	                                {
	                                	$arr_stato=null;
	                                	$arr_stato[0]['StatoId']=$ArrNumeroTragitti[0]['Tragitto'];
	                                	$arr_stato[0]['Stato']=$ArrNumeroTragitti[0]['Tragitto'];
	                                } 
	                                ?>
	                                <select name='tragitto' id="tragitto">
	                                <?php 
	                                foreach ($arr_stato as $k => $option){
	                                	echo "<option value='".$option['StatoId']."'>".$option['Stato']."</option>";
	                                }
	                                ?>
	                                </select>
	                                
	                                <p><h4><a href="#" id="rimborso-coupon"><?=$dizionario['area_clienti']['rimborso_coupon_btn']?></a> >></h4></p>
                                <?php 
				                }
	                            ?>
					</div>
						
				</div>
                <!--/#content-->
            </div>
            <!--/container-->

            <!-- Bottom
            ================================================== -->
        	<? include_once($basepath."/include/bottom.php") ?>
        	 
        </section>
        <!--/#main-->
        
        
        <!-- Footer
        ================================================== -->
        <? include_once($basepath."/include/footer.php") ?>    
        
    </div>
    <!-- #page -->
  
	<? include_once($basepath."/include/html_close.php") ?>   

	<script>
	    
		$(document).ready(function(){
			

			$('#rimborso-coupon').click(function(){
				$('#rimborso-coupon').off('click');
				var formData = {
						action: 'rimborso',
						titoloId: <?=$rowTitolo['PrenotazioneTitoloId']?>,
						tragitto: $('#tragitto').val()
				};
				$.ajax({
				    type: "POST",
				    url: "/gestione_utente.php",
				    // The key needs to match your method's input parameter (case-sensitive).
				    data: formData,
				    dataType: "text",
				    success: function(data){
					    window.location.replace("/mie-prenotazioni.php");
					},
				    failure: function(errMsg) {
				        alert(errMsg);
				    }
				});
			});
		});
    </script>
</body>
</html>




<body>
	
    <!-- Wrapper
	================================================== -->
	<div id="wrapper">
    	
        
        
            <!-- Top Header
            ================================================== -->
            <? include_once($basepath."/include/top_header.php") ?>       
        
        
        
        <!-- BG BOTTOM
        ================================================== -->
        <div class="bg_bottom">
        
        	 <!-- Biriciola
            ================================================== -->
            <div id="briciola">
            </div><!-- header -->
        	
            <!-- Header
            ================================================== -->
            <div id="header2">
            </div><!-- header -->
            
            <!-- Main
            ================================================== -->
            <div id="main">
                <div class="container">
                    <div class="sixteen columns alpha omega" style="width: 960px;">
                    	<div class="main_content_article">
                            <div class="ten columns" style="margin-right: 50px;">
                            <br>
                            	<ul class="breadcrumb">
                                    <li><a href="/area-clienti.php"><?=$dizionario['area_clienti']['area']?></a></li> -
                                    <li><?=$dizionario['area_clienti']['rimporso_biglietto']?>: <?=$rowTitolo['Codice']?></li>    
                                </ul>
                                <hr/>
                                <div class="clear"></div>
                                
                                <div>
                                <style>
                                	.opzioni > li > h4 > a:hover{
                                		color: red;
                                	}
                                </style>
                                	<ul class="opzioni">
                                	
                                            <li><h4><a href="/profilo.php"><i class="fa fa-user"></i> <?=$dizionario['area_clienti']['profilo']?></a></h4></li>
                                            <li class="active"><h4><a href="/mie-prenotazioni.php"><i class="fa fa-bus"></i> <?=$dizionario['area_clienti']['prenotazioni']?></a></h4></li>
                                            <li><h4><a href="/miei-coupon.php"><i class="fa fa-bus"></i> <?=$dizionario['area_clienti']['coupon']?></a></h4></li>                                        
                                            <li><h4><a href="#" id="logout"><i class="fa fa-sign-out"></i>  <?=$dizionario['area_clienti']['logout']?></a></h4></li>
										
                                	
                                	</ul>
                                </div>
                                <div class="clear"></div>
                                <br/>
                                <p id="tipo_accesso"></p>
                                <h4><?=$dizionario['area_clienti']['rimborso_info']?></h4>
                                <p>
                                	<strong><?=$dizionario['area_clienti']['data']?>:</strong> <?=$rowTitolo['DataIns']?><br>
                                	<strong><?=$dizionario['area_clienti']['codice']?>:</strong> <?=$rowTitolo['CodicePrenotazione']?><br>
                                	<strong><?=$dizionario['area_clienti']['partenza']?>:</strong> <?=$rowTitolo['DataInizioItinerario']?><br>
                                	<strong><?=$dizionario['area_clienti']['DA']?>:</strong> <?=$rowTitolo['ComunePartenza']?><br>
                                	<strong><?=$dizionario['area_clienti']['A']?>:</strong> <?=$rowTitolo['ComuneArrivo']?><br>
                                	<strong><?=$dizionario['area_clienti']['tipo']?>:</strong> <?php if($rowTitolo['TipoViaggioId'] == 1) 
				                                			echo "CS"; 
				                                		else
				                                			echo "A/R" ?><br>
                                	
                                
                                	<strong><?=$dizionario['area_clienti']['codice_big']?>:</strong> <?=$rowTitolo['Codice']."/".$rowTitolo['Anno']?><br>
	                                <strong><?=$dizionario['area_clienti']['importo']?>:</strong> <?=$rowTitolo['ImportoTitolo']?>&euro;<br>
	                                <strong><?=$dizionario['area_clienti']['biglietto']?>:</strong> <?=$rowTitolo['TipologiaBiglietto']?><br>
	                                <strong><?=$dizionario['area_clienti']['intestato']?>:</strong> <?=$rowTitolo['Cognome']." ".$rowTitolo['Nome']?><br>
                                </p>
                                <h4><?=$dizionario['area_clienti']['rimborso_coupon']?></h4>
                                <p><?=$dizionario['area_clienti']['rimborso_coupon_msg']?></p>
                                
                                <?php 
                                $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['Andata-Ritorno']);
                                $arr_stato[]= array("StatoId" => 'Andata',"Stato" => $dizionario['Andata']);
                                $arr_stato[]= array("StatoId" => 'Ritorno',"Stato" => $dizionario['Ritorno']);
                                if ($countTragitti<2)
                                {
                                	$arr_stato=null;
                                	$arr_stato[0]['StatoId']=$ArrNumeroTragitti[0]['Tragitto'];
                                	$arr_stato[0]['Stato']=$ArrNumeroTragitti[0]['Tragitto'];
                                } 
                                ?>
                                <select name='tragitto' id="tragitto">
                                <?php 
                                foreach ($arr_stato as $k => $option){
                                	echo "<option value='".$option['StatoId']."'>".$option['Stato']."</option>";
                                }
                                ?>
                                </select>
                                <?php 

                                ?>
                                <p><h6><a href="#" id="rimborso-coupon"><?=$dizionario['area_clienti']['rimborso_coupon_btn']?></a>></h6></p>
                                
                            </div>
                                 
                                
							</div><!-- ten -->
                            <div class="five columns omega alpha" style="float: right; width: 300px; margin-top: -10px">
                            		
                                     <div class="box_home red">
                                <h4><?=$dizionario['Prenota-Viaggio']?></h4>
                                <? include_once($basepath . "/include/modulo_ricerca.php"); ?>
                            </div>
                            <div class="box_home black">
                                <br/>
                                    <h4 style="color: #FFF"><?=$dizionario['Servizi']?></h4>
                                <br/>
                                <ul>
                                    <? include($basepath . "/include/menu_servizi.php"); ?>
                                </ul>
                            </div>
                            <br/>
                            <? include($basepath . "/include/info_contatti.php"); ?>

                            </div><!-- five --> 
                            <div class="clear" style="margin-bottom: 30px;"></div>         
                            
                            
    					</div><!-- main_content_article -->
                    </div><!-- sixteen -->
                </div><!-- container -->
            </div><!-- main -->
            
            <!-- Footer
            ================================================== -->
            <? include_once($basepath."/include/footer.php") ?>        
            
        </div><!-- BG BOTTOM -->
        
    </div><!-- wrapper -->
    
    
<? include_once($basepath."/include/html_close.php") ?>   

