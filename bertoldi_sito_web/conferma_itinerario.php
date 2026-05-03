<?php
//Autore: Marco Casaburi
//Data ultima modifica: 13/12/2018

ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL);

$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['conferma']['titolo'];
$PageDescription = "";
$PageKeywords = "";
$page_title = $PageTitle;

$config = new Config();
$run = $config->load();
$classespath_ = Config::$classespath;
$db = new Database();
$conn = $db->connect();

//recupero informazioni sul profilo
if(isset($_SESSION['USER'])){
    $userInfo = $_SESSION['USER'];
    if(isset($userInfo)){
        $utenteObj = new UtenteWeb();
        $utenteObj->conn = $db;
    }
} else {
    $userInfo = null;
    $utenteObj = null;
}

$sql = "SELECT TipologiaBigliettoId, EtaDa, EtaA FROM RT_TipologiaBiglietto WHERE Stato = 1 AND Cancella = 0";
$rowsEta = $db->fetch_array($sql);

global $search;
$PercorsoAndata = null;
$PercorsoRitorno = null;

if (isset($_SESSION['CURRENT_SEARCH'])) {
    $search = unserialize($_SESSION['CURRENT_SEARCH']);
    $search->conn = $db;
    
    $scontoAndataRitorno = $search->ScontoAndataRitorno;
    
    $arr = $search->ArrSearch;
    $ItAndata = ($_POST['ItinerarioAndata']);
    if ($search->ItinerarioIdAndata >= 0) {
        $ItAndata = $search->ItinerarioIdAndata;
    }
    
    $search->setItinerarioAndataScelto($ItAndata);
    
    $ElencoCorseAndata = $search->ArrCorseAndata;
    $PercorsoAndata = $ElencoCorseAndata[$ItAndata];
    
    //controllo tratta italiana
    $sqlAndata = "SELECT r.idnazione FROM Comune c
                        LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
                        LEFT JOIN Regione r on r.RegioneId = p.RegioneId
                        WHERE c.ComuneId = ".$PercorsoAndata['ComunePickUpId'];
    $nazioneAndata = $db->query_first($sqlAndata);
    $sqlRitorno = "SELECT r.idnazione FROM Comune c
                        LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
                        LEFT JOIN Regione r on r.RegioneId = p.RegioneId
                        WHERE c.ComuneId = ".$PercorsoAndata['ComuneDropOffId'];
    $nazioneRitorno = $db->query_first($sqlRitorno);
    if($nazioneAndata['idnazione'] == 1 && $nazioneRitorno['idnazione'] == 1) {
        $viaggioItalia = true;
    } else {
        $viaggioItalia = false;
    }
    //fine controllo tratta italiana
    
    $nazione_residenza=new Nazione(null);
    $nazione_residenza->conn=$db;
    $nazione_residenza->getAllNazione();
    $arr_nazione_residenza=$nazione_residenza->ArrNazione;
    
    $errore_data = false;
    
    $DataPartenzaAndata = $arr['DataPartenzaAndata'];
    $DataPartenzaRitorno = $arr['DataPartenzaRitorno'];
    
    if ($arr['TipoViaggioId'] == 2) {
        if (($DataPartenzaAndata >= $DataPartenzaRitorno) and ($arr['TipoPercorsoId'] == 1)) {
            $errore_data = true;
        }
        
        if (($DataPartenzaAndata > $DataPartenzaRitorno) and ($arr['TipoPercorsoId'] == 2)) {
            $errore_data = true;
        }
        
        $ItRitorno = $_POST['ItinerarioRitorno'];
        
        if ($search->ItinerarioIdRitorno >= 0) {
            $ItRitorno = $search->ItinerarioIdRitorno;
        }
        
        $search->setItinerarioRitornoScelto($ItRitorno);
        
        $ElencoCorseRitorno = $search->ArrCorseRitorno;
        $PercorsoRitorno = $ElencoCorseRitorno[$ItRitorno];
    }
    
    $posti_esauriti = false;
} else {
	header("location: /");
}
?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
<style>
.error{
	color:red;
}
</style>
<script type="text/javascript">
    var vincoliEta = {};
    <?php foreach ($rowsEta as $row) { ?>
	    vincoliEta['<?php echo $row['TipologiaBigliettoId']; ?>'] = "<?php echo $row['EtaDa']; ?>_<?php echo $row['EtaA']; ?>";
    <?php } ?>
</script>

<?php include($basepath . "/js/calcolo_prezzo.php");?>
</head>

<body class="home page-template page-template-homepage page-template-homepage-php page page-id-1036 fullwidth-bg wpb-js-composer js-comp-ver-5.0 vc_responsive">
	<div style="display:none;" id="loader"><img src="/images/ajax-loader.gif"></div>
    
    <!-- Wrapper
	================================================== -->
	<div id="page" class="hfeed site fullwidth">
    	
        
        
        <!-- Top Header
        ================================================== -->
        <? include_once($basepath."/include/top_header.php") ?>  
        
        
		<section id="main" style="background:#ebebeb;">
			<div class="container">
				<div id="content" class="site-content" role="main">
				
					<? if ($errore_data == false) { ?>
					<div class="row">
						<div class="col-md-9">
							<?
                            $at = 0;
                            $tratteId = "";
                            foreach ($PercorsoAndata['tratte'] as $trattaId => $trattaKm) {
                                if ($at > 0)
                                    $tratteId .= "," . $trattaId;
                                else
                                    $tratteId = $trattaId;
                                $at++;
                            }
                        
                            //Prendo i biglietti validi
                            $dataPartenza = $PercorsoAndata['DataPartenza'];
                            $corsaIdPartenza = $PercorsoAndata['CorsaId'];
                            $ar = 0;
                        //     $sql = "SELECT DISTINCT TipologiaBigliettoId, TipologiaBiglietto, TipologiaBigliettoPeso FROM RT_ViewPrenotazioneTipoBiglietti WHERE TrattaId IN($tratteId) AND AR=$ar AND '$dataPartenza' >= ValiditaBigliettoDal AND '$dataPartenza' <= ValiditaBigliettoAl";
                            
                            $sql = "SELECT DISTINCT vbd.BigliettoId as TipologiaBigliettoId, tb.TipologiaBiglietto, tb.TipologiaBigliettoPeso
	                            FROM RT_ValiditaBigliettoDettaglio vbd
	                            LEFT JOIN RT_TipologiaBiglietto tb ON (tb.TipologiaBigliettoId = vbd.BigliettoId)
	                        	LEFT JOIN RT_ValiditaBiglietto vb ON (vb.ValiditaBigliettoId = vbd.ValiditaBigliettoId) 
                        		WHERE vb.CorsaId=$corsaIdPartenza AND
                        		'$dataPartenza' >= vb.Dal AND '$dataPartenza' <= vb.Al 
                            order by tb.TipologiaBigliettoPeso";
                        
                            $biglietti = $db->fetch_array($sql);
                        
                            ?>
                            	
                        	<div style="padding-top:20px;" class="intestazione_viaggio">
                                    <h2 class="thm-titlestandardstyle active"><?php echo $dizionario['conferma']['conferma']?>!</span></h2></div> 
          
                             
							<div class="clear"></div>
                        	<!-- Nav tabs -->
                           
                            <?php if(isset($userInfo)) { ?>
            					<p class="intestazione_sezione">
            						<?=$dizionario['area_clienti']['sei_collegato']?>
            						<strong><?=$userInfo['CognomeRagioneSociale']?> <?=$userInfo['Nome']?></strong><br>
            						<?=$dizionario['area_clienti']['codice']?>: <strong><?=$userInfo['MembershipClubCode']?></strong>
            					</p>
            				<?php 
            				} else { ?>
            					<p id="registrazione_mobile_conferma">[ <?php echo $dizionario['conferma']['member_1'];?> ]</p>
            				<?php } ?>
                            <!--hoteldetail-->
                                
                            <div id="buynowbtn" class="result-popup mfp-with-anim">
                            	<div role="form" class="wpcf7" id="" dir="ltr" lang="it-IT">
                                	<div class="screen-reader-response"></div>
                                	<form id="form_conferma_itinerario" method="post" action="/splash_prenota.php" class="wpcf7-form" novalidate role="form">
                                        <input id="step2" type="checkbox">
                                        <input id="step3" type="checkbox">
                                        
                                        <!-- STEP 1 -->
                                        <div class="row form-group" id="part1">
                                            <h3 class="title"><i class="fa fa-ticket" aria-hidden="true" style="color:#ff6633;"></i> <?php echo $dizionario['conferma']['posti_a_sedere'];?></h3>
                                            <table class="data responsive" id="biglietti">
                                            	<tbody>
                                                	<tr>
                                                        <th><?=$dizionario['conferma']['posto']?></th>
                                                        <th><?=$dizionario['conferma']['biglietto']?></th>
                                                        <th><?=$dizionario['conferma']['prezzo_unitario']?></th>
                                                        <th><?=$dizionario['conferma']['prezzo_finale']?></th>
                                                    </tr>
                                                    
                                                    <?php
                                                        $index = 0;
                                                        $prezzoTotale = 0;
                                                        foreach ($biglietti as $biglietto) {
                                                        	if($biglietto['TipologiaBigliettoId'] != 11) {
	                                                            $prezzoTotaleAndata = 0;
	                                                            $prezzoTotaleRitorno = 0;
	                                                            $prezzoTotaleBiglietto = 0;
	                                                    
	                                                            //trovo il motiplicatore
	                                                            $moltiplicatore = $search->ListinoMoltiplicatore($PercorsoAndata['km'], $PercorsoAndata['CorsaId']);
	                                                    
	                                                            //trovo la variazione prezzo andata
	                                                            $variazionePrezzoAndata = $search->GetScontoPromozioneAttiva($PercorsoAndata['CorsaId'], $DataPartenzaAndata, 1, $biglietto['TipologiaBigliettoId']);
	                                                    		
	                                                            //calcolo il prezzo totale andata
	                                                            $sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = ".$PercorsoAndata['CorsaId']." AND TipologiaBigliettoId = ".$biglietto['TipologiaBigliettoId']." AND FermataPickup = ".$PercorsoAndata['ComunePickUpId']." AND FermataDropoff = ".$PercorsoAndata['ComuneDropOffId'];
	                                                            $tempTariffa = $db->query_first($sql);
	                                                            $PrezzoPax = $tempTariffa['Tariffa'];
	                                                            $prezzoTotaleAndata = $PrezzoPax;
	                                                            
	                                                            //applico la variazione di prezzo
	                                                            $prezzoTotaleAndata = $prezzoTotaleAndata + ($prezzoTotaleAndata * $variazionePrezzoAndata / 100);
	                                                            
	                                                            //effettua l'arrotonadamento del prezzo all'euro superiore
	                                                            $prezzoTotaleAndata_frac = $prezzoTotaleAndata - floor($prezzoTotaleAndata);
	                                                            $prezzoTotaleAndata = intval($prezzoTotaleAndata);
	                                                            if ($prezzoTotaleAndata_frac > 0) {
	                                                                $prezzoTotaleAndata += 1;
	                                                            }
	                                                    
	                                                            $prezzoTotaleAndataNoSconto = $prezzoTotaleAndata;
	                                                            
	                                                            //se il biglietto � di tipi Andata e Ritorno
	                                                            if ($arr['TipoViaggioId'] == 2 ) {
	                                                    
	                                                    			//trovo la variazione prezzo ritorno
	                                                    			$variazionePrezzoRitorno = $search->GetScontoPromozioneAttiva($PercorsoRitorno['CorsaId'], $DataPartenzaRitorno, 1, $biglietto['TipologiaBigliettoId']);
	                                                    			
	                                                    			//calcolo il prezzo totale ritorno
	                                                    			$sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = ".$PercorsoRitorno['CorsaId']." AND TipologiaBigliettoId = ".$biglietto['TipologiaBigliettoId']." AND FermataPickup = ".$PercorsoRitorno['ComunePickUpId']." AND FermataDropoff = ".$PercorsoRitorno['ComuneDropOffId'];
	                                                    			$tempTariffa = $db->query_first($sql);
	                                                    			$PrezzoPax = $tempTariffa['Tariffa'];
	                                                    			$prezzoTotaleRitorno = $PrezzoPax;
	                                                                
	                                                                //applico la variazione di prezzo
	                                                                $prezzoTotaleRitorno = $prezzoTotaleRitorno + ($prezzoTotaleRitorno * $variazionePrezzoRitorno / 100);
	                                                                
	                                                                //effettua l'arrotonadamento del prezzo all'euro superiore
	                                                                $prezzoTotaleRitorno_frac = $prezzoTotaleRitorno - floor($prezzoTotaleRitorno);
	                                                                $prezzoTotaleRitorno = intval($prezzoTotaleRitorno);
	                                                                if ($prezzoTotaleRitorno_frac > 0) {
	                                                                    $prezzoTotaleRitorno += 1;
	                                                                }
	                                                    
	                                                                $prezzoTotaleRitornoNoSconto = $prezzoTotaleRitorno;
	                                                    
	                                                            } else if ($arr['TipoViaggioId'] == 3) {
	                                                                $scontoAndataRitorno = 0;
	                                                            	//se il tipo di viaggio � di tipo Ritorno Open
	                                                            	$sql = "select LineaId from RT_Corsa where CorsaId = ".$PercorsoAndata['CorsaId'];
	                                                            	
	                                                            	$lineaId = $db->query_first($sql);
	                                                    
	                                                            	if($lineaId['LineaId'] == 1){
	                                                            		$lineaIdR = 2;
	                                                            	} else if ($lineaId['LineaId'] == 2){
	                                                            		$lineaIdR = 1;
	                                                            	} else if ($lineaId['LineaId'] == 13) {
	                                                            		$lineaIdR = 14;
	                                                            	} else if ($lineaId['LineaId'] == 14) {
	                                                            		$lineaIdR = 13;
	                                                            	}  else if ($lineaId['LineaId'] == 16) {
	                                                            		$lineaIdR = 17;
	                                                            	} else {
	                                                            		$lineaIdR = 16;
	                                                            	}
	                                                            	
	                                                            	$sql = "select CorsaId from RT_Corsa where LineaId = $lineaIdR and RitornoAperto = 1";
	                                                            	
	                                                            	$corsaIdR = $db->query_first($sql);
	                                                            	
	                                                            	//calcolo il prezzo totale ritorno open
	                                                            	$sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = ".$corsaIdR['CorsaId']." AND TipologiaBigliettoId = ".$biglietto['TipologiaBigliettoId']." AND FermataPickup = ".$PercorsoAndata['ComuneDropOffId']." AND FermataDropoff = ".$PercorsoAndata['ComunePickUpId'];
	                                                            	$tempTariffa = $db->query_first($sql);
	                                                            	$PrezzoPax = $tempTariffa['Tariffa'];
	                                                            	$prezzoTotaleRitorno = $PrezzoPax;
	                                                    
	                                                            	//effettua l'arrotonadamento del prezzo all'euro superiore
	                                                            	$prezzoTotaleRitorno_frac = $prezzoTotaleRitorno - floor($prezzoTotaleRitorno);
	                                                            	$prezzoTotaleRitorno = intval($prezzoTotaleRitorno);
	                                                            	if ($prezzoTotaleRitorno_frac > 0) {
	                                                            		$prezzoTotaleRitorno += 1;
	                                                            	}
	                                                            }
	                                                            
	                                                            // calcolo il totale del biglietto andata e ritorno
	                                                            $prezzoTotaleBiglietto = $prezzoTotaleAndata + $prezzoTotaleRitorno;
	                                                    
	                                                            //applica lo sconto se andata e ritorno
	                                                            if ($arr['TipoViaggioId'] == 2 || $arr['TipoViaggioId'] == 3) {
	                                                                $prezzoTotaleBiglietto = $prezzoTotaleBiglietto - ($prezzoTotaleBiglietto * $scontoAndataRitorno / 100);
	                                                            }
	                                                    
	                                                            //effettua l'arrotonadamento del prezzo all'euro superiore
	                                                            $prezzoTotaleBiglietto_frac = $prezzoTotaleBiglietto - floor($prezzoTotaleBiglietto);
	                                                            $prezzoTotaleBiglietto = intval($prezzoTotaleBiglietto);
	                                                            if ($prezzoTotaleBiglietto_frac > 0) {
	                                                                $prezzoTotaleBiglietto += 1;
	                                                            }
	                                                    
// 	                                                            if ($biglietto['TipologiaBigliettoId'] == "17") {
// 	                                                                $prezzoTotale = $prezzoTotaleBiglietto;
// 	                                                            }
	                                                            $prezzoTotale = 0;
	                                                            
	                                                            //recupero descrizione della tipologia del biglietto
	                                                            $sql = "SELECT TipologiaBigliettoDescr FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId=".$biglietto['TipologiaBigliettoId'];
	                                                            $rowDescr = $db->query_first($sql);
	                                                            $biglietto['TipologiaBigliettoDescr'] = $rowDescr['TipologiaBigliettoDescr'];
	                                                            
	                                                            if($prezzoTotaleBiglietto>0){
	                                                            ?>
	                                                                <tr>
	                                                                    <td>
	                                                                    	<div class="status"></div>
	                                                                		<?php //if ($biglietto['TipologiaBigliettoId'] == "17") { ?>
	                                                                        <!--     <input type="text" id="numero_<?php echo $biglietto['TipologiaBigliettoId']; ?>" name="biglietti[<?php echo $biglietto['TipologiaBigliettoId']; ?>][numero]" value="1" tabindex="1"/>  -->
	                                                                		<?php // } else { ?>
	                                                                            <input type="text" id="numero_<?php echo $biglietto['TipologiaBigliettoId']; ?>" name="biglietti[<?php echo $biglietto['TipologiaBigliettoId']; ?>][numero]" value="0" tabindex="1"/>
	                                                                        <?php // } ?>
	                                                                    </td>
	                                                                    <td style="text-align: left !important; ">
	                                                                        <a href="javascript: void(0)" class="promo" title="<?php echo $biglietto['TipologiaBigliettoDescr'];?>"/><img src="/img/info.png" style="float: left; width: 15px !Important;height: 15px !important; margin-right: 8px "/></a>
	                                                                        <span id="tipo_<?php echo $biglietto['TipologiaBigliettoId']; ?>"><?php echo $biglietto['TipologiaBiglietto']; ?></span>
	                                                                    </td>
	                                                                	<td>
	                                                            			<input type="hidden" id="prezzo_andata_no_sconto_<?php echo $biglietto['TipologiaBigliettoId']; ?>" name="biglietti[<?php echo $biglietto['TipologiaBigliettoId']; ?>][prezzoAndataNoSconto]" value="<?php echo $prezzoTotaleAndataNoSconto ?>" tabindex="1"/>
	                                                            			<?php if ($arr['TipoViaggioId'] == 2) { ?>
	                                                                    		<input type="hidden" id="prezzo_ritorno_no_sconto_<?php echo $biglietto['TipologiaBigliettoId']; ?>" name="biglietti[<?php echo $biglietto['TipologiaBigliettoId']; ?>][prezzoRitornoNoSconto]" value="<?php echo $prezzoTotaleRitornoNoSconto ?>" tabindex="1"/>
	                                                            			<?php } ?>
	                                                            			<input type="hidden" id="prezzo_<?php echo $biglietto['TipologiaBigliettoId']; ?>" name="biglietti[<?php echo $biglietto['TipologiaBigliettoId']; ?>][prezzo]" value="<?php echo $prezzoTotaleBiglietto; ?>" />
	                                                                        <span id="unitario_<?php echo $biglietto['TipologiaBigliettoId']; ?>"><?php echo $prezzoTotaleBiglietto; ?></span>
	                                                                    </td>
	                                                                    <td>
	                                                                        <?php // if ($biglietto['TipologiaBigliettoId'] == "17") { ?>
	                                                                          <!--    <input type="hidden" id="prezzo_totale_<?php echo $biglietto['TipologiaBigliettoId']; ?>" name="biglietti[<?php echo $biglietto['TipologiaBigliettoId']; ?>][prezzo_totale]" value="<?php echo $prezzoTotaleBiglietto; ?>" />
	                                                                        	<span id="totale_<?php echo $biglietto['TipologiaBigliettoId']; ?>"><?php echo $prezzoTotaleBiglietto; ?></span> -->
	                                                            			<?php //} else { ?>
	                                                                            <input type="hidden" id="prezzo_totale_<?php echo $biglietto['TipologiaBigliettoId']; ?>" name="biglietti[<?php echo $biglietto['TipologiaBigliettoId']; ?>][prezzo_totale]" value="0" />
	                                                                            <span id="totale_<?php echo $biglietto['TipologiaBigliettoId']; ?>">0,00&euro;</span>
	                                                            			<?php //} ?>
	                                                                    </td>
	                                                                </tr>
															<?php } ?>  
														<?php } ?>                                                              
													<?php } ?>
												</tbody>
												<tfoot>
                                                    <tr>
                                                        <td colspan="3"></td>
                                            			<input type="hidden" id="prezzo_totale" name="prezzo_totale" value="<?php echo $prezzoTotale; ?>" />
                                                		<input type="hidden" id="posti_totale" name="posti_totale" value="0" />
                                                		<td><span id="totale"><?= $prezzoTotale ?></span></td>
                                                	</tr>
                                            	</tfoot>
                                            </table>
                                            
                                            <div id="error_numero_posti" class="error" style="display:none"><?php echo $dizionario['conferma']['error_numero_posti'];?></div>
                                        	
                                        	<div class="checkButtons">
                                                <label for="step2" id="continue-step2" class="continue">
                                                    <div class="btn btn-default btn-success btn-lg" onclick="step2check();"><?php echo $dizionario['conferma']['continua'];?> <span class="glyphicon glyphicon-chevron-right"></span></div>
                                                </label>
                                            </div>
                                        </div>
                                        <script type="text/javascript">
											function step2check(){
												if($('#posti_totale').val() == 0){
													$("#error_numero_posti").show();
													$('#back1step').click();
												} else {
													$("#error_numero_posti").hide();
												}
											}
                                        </script>
                                        <!--/.STEP 1-->
                                        
                                        <!-- STEP 2 -->
                                        <div class="row form-group" id="part2">
                                            <div class="row thm-flight thm-flight-segment col-md-12">
                                                <h3 class="title"><i class="fa fa-users" aria-hidden="true" style="color:#ff6633;"></i> <?=$dizionario['conferma']['info_passeggeri']?></h3>
                                                <br/>
                                                <div id="passeggeri">
	                                                <!--
	                                                <div id="riga<?= $index ?>" class="passeggero row">
	                                                	<div class="col-md-12">
	                                                    	<h5 class=""><?=$dizionario['Biglietto']?> <?=$dizionario['conferma']['adulto']?></h5>
	                                                	</div>
	                                                	<input class="passeggeroId" type="hidden" value="17" id="<?= "Passeggeri[$index][TipoBigliettoId]" ?>" name="<?= "passeggeri[$index][TipoBigliettoId]" ?>">
	                                                    <div class="form-group col-md-4">
	                                                        <label for="btnfield_name"><?=$dizionario['Nome']?>: <span class="required">*</span></label>
	                                                        <br>
	                                                        <span class="wpcf7-form-control-wrap name">
	                                							<input name="passeggeri[<?= $index ?>][Nome]" id="PasseggeroNome<?= $index ?>" value="" size="40" class="passeggeroNome wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control" id="btnfield_name" aria-required="true" aria-invalid="false" type="text" onChange="javascript:changePasseggero(<?php echo $index; ?>)">
	                                						</span>
	                                					</div>
	                                                    <div class="form-group col-md-4">
	                                                        <label for="btnfield_name"><?=$dizionario['Cognome']?>: <span class="required">*</span></label>
	                                                        <br>
	                                                        <span class="wpcf7-form-control-wrap surname">
	                                							<input name="passeggeri[<?= $index ?>][Cognome]" id="PasseggeroCognome<?= $index ?>" value="" size="40" class="passeggeroCognome wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control" id="btnfield_name" aria-required="true" aria-invalid="false" type="text" onChange="javascript:changePasseggero(<?php echo $index; ?>)">
	                                						</span>
	                                					</div>
	                                                    <div class="form-group col-md-2">
	                                                        <label for="btnfield_name"><?=$dizionario['Eta']?>: <span class="required">*</span></label>
	                                                        <span class="wpcf7-form-control-wrap surname">
	                                                        	<input name="passeggeri[<?= $index ?>][Eta]" id="PasseggeroEta<?= $index ?>" value="" size="40" class="passeggeroEta wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control" id="btnfield_name" aria-required="true" aria-invalid="false" type="text">
	                                                        </span>
	                                                    </div>
	                                                    <div class="form-group col-md-2">
	                                                        <label class="conradio"><?=$dizionario['conferma']['principale']?>
	                                                            <input type="radio" checked="" name="PasseggeriPrincipale" value="<?php echo $index; ?>" onChange="javascript:changePrincipale();">
	                                                            <span class="checkmark"></span>
															</label>
	                                                        <a href="javascript:rimuoviBiglietto('17', 'riga<?= $index ?>')"><img src="images/close.png" alt="<?=$dizionario['conferma']['rimuovi']?>" title="<?=$dizionario['conferma']['rimuovi']?>"></a> <?=$dizionario['conferma']['rimuovi']?> 
														</div>
														<div class="form-group col-md-4">
	                                                    	<label for="btnfield_name"><?=$dizionario['Sesso']?>: <span class="required">*</span></label>
	                                                        <br>
	                                                    	<select class="passeggeroSesso select2" name="passeggeri[<?= $index ?>][SessoId]" id="PasseggeroSesso<?= $index ?>" onChange="javascript:changePasseggero(<?php echo $index; ?>)">
	                                                            <option value="">- <?=$dizionario['conferma']['seleziona']?> -</option>
	                                                            <option value="1"><?=$dizionario['conferma']['sig']?></option>
	                                                            <option value="2"><?=$dizionario['conferma']['sigra']?></option>
	                                                        </select>
	                                                    </div>
	                                                </div>
	                                                -->
	                                            </div>
                                            </div>
                                            <div class="checkButtons">
                                                <label for="step2" id="back-step2" class="back">
                                                    <div class="btn btn-default btn-grey btn-lg" id="back1step" role="button"><span class="glyphicon glyphicon-chevron-left"></span> Indietro</div>
                                                </label>
                                                <label for="step3" id="continue-step3" class="continue">
                                                    <div class="btn btn-default btn-success btn-lg" role="button"><?php echo $dizionario['conferma']['continua'];?> <span class="glyphicon glyphicon-chevron-right"></span></div>
                                                </label>
                                            </div>
                                        </div>
                                        <!--/.STEP 2-->
                                        
	                                    <!-- STEP 3 -->
										<div class="row form-group" id="part3">
											<h3 class="titleCheck"><i class="fa fa-user" aria-hidden="true" style="color:#ff6633;"></i> <?=$dizionario['conferma']['dati_principale']?>  <?=$dizionario['conferma']['dati_principale_inserisci']?></h3>
                                            <input type="hidden" name="nome_cognome" id="nome_cognome" value=""/>
											<input type="hidden" name="sesso" id="sesso" value="">
                                            
                                        	<div class="row">
                                            	<div class="col-md-12">
                                            		<?php if(isset($userInfo)) { ?>
            											<p class="intestazione_sezione">
            												<?=$dizionario['area_clienti']['sei_collegato']?>
            											 	<strong><?=$userInfo['CognomeRagioneSociale']?> <?=$userInfo['Nome']?></strong><br>
            												<?=$dizionario['area_clienti']['codice']?>: <strong><?=$userInfo['MembershipClubCode']?></strong>
            											</p>
            											<input type="hidden" id="membership_id" name="membership_id" value="<?php echo $userInfo['MembershipClubCode'];?>">
            											<?php 
            										} else { ?>
            											<input type="hidden" id="membership_id" name="membership_id" value="<?php echo $userInfo['MembershipClubCode'];?>">
            										<?php } ?>
                                           		</div>
                                           		
                                           		<div class="form-group col-md-6">
                                                    <label for="btnfield_email"><?=$dizionario['conferma']['email']?>: <span class="required">*</span></label>
                                                    <br>
                                                    <span class="wpcf7-form-control-wrap email">
                                                        <div class="status"></div>
														<input name="email" id="email" value="<?php if(isset($userInfo)) echo $userInfo['Email'];?>" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control" aria-required="true" aria-invalid="false" type="text" <?php if(isset($userInfo)) echo "readonly='readonly'";?>>
													</span>
												</div>
                                                <div class="form-group col-md-6">
                                                    <label for="btnfield_email"><?=$dizionario['conferma']['conferma_mail']?>: <span class="required">*</span></label>
                                                    <br>
                                                    <span class="wpcf7-form-control-wrap emailconfirm">
														<input name="conferma_email" id="conferma_email" value="<?php if(isset($userInfo)) echo $userInfo['Email'];?>" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control" aria-required="true" aria-invalid="false" type="text" <?php if(isset($userInfo)) echo "readonly='readonly'";?>>
													</span>
												</div>
                                           		
                                           		<div class="form-group col-md-6">
                                                    <label for="btnfield_phone"><?=$dizionario['conferma']['prefiso']?> <?=$dizionario['conferma']['tel']?>: <span class="required">*</span></label>
                                                    <br>
                                                    <span class="wpcf7-form-control-wrap numero_personale">
                                                    	<div class="status"></div>
                                                    	<select style="padding: 5px;" tabindex="11" name="prefisso_numero_personale" id="prefisso_numero_personal" class="select2"/>
                                            				<option value="" selected="selected">- <?=$dizionario['conferma']['seleziona']?> -</option>
    													        <?php
    													        $prefissoObj = new PrefissoTelefono($db);
    													        $arr_prefisso = $prefissoObj->getAllForSelect();
    													        foreach ($arr_prefisso as $prefisso) {
    													        	if($prefisso['Prefisso'] == '39'){
    													        		echo "<option value='" . $prefisso['Prefisso'] . "' selected = 'selected'>" . $prefisso['Descrizione'] . "</option>";
    													        	} else {
    													        		echo "<option value='" . $prefisso['Prefisso'] . "'>" . $prefisso['Descrizione'] . "</option>";
    													        	}
    													            
    													        }
    													        ?>
                                            			</select>
                                            			
                                            			<?php 
			                                                $tel = "";
			                                                if(isset($userInfo)) {
																if( $userInfo['Cellulare'] != ''){
			                                                		$tel = $userInfo['Cellulare'];
			                                                	} else {
																	$tel = $userInfo['Telefono'];
																}
                                                            }
														?>
														
												</div>
                                                <div class="form-group col-md-6">
                                                	<div class="status"></div>
														<?=$dizionario['conferma']['tel_personale']?> <span class="required">*</span>
														<input name="numero_personale" id="numero_personale" value="<?php if(isset($userInfo)) echo $tel;?>" size="40" class="wpcf7-form-control wpcf7-text form-control" aria-invalid="false" type="text" <?php if(isset($userInfo)) echo "readonly='readonly'";?>>
													</span>
                                                	
                                                	<input type="hidden" ame="numero_familiare" id="numero_familiare" value="<?php if(isset($userInfo)) echo $userInfo['TelefonoFamiliare'];?>">
                                                    
												</div>
											</div>
										
											<? if (($DataPartenzaAndata > date('Y-m-d')) and ($posti_esauriti == false)) { ?>
												<h3 class="titleCheck"><i class="fa fa-percent" aria-hidden="true" style="color:#ff6633;"></i> <?=$dizionario['conferma']['coupon_sconto']?></h3>
                                                <p><?=$dizionario['conferma']['inserisci_coupon']?></p>
                                                <div class="row">
                                                	<div class="form-group col-md-6">
                                                        <label for="btnfield_phone"><?=$dizionario['conferma']['codice_coupon']?></label>
                                                        <br>
                                                        <span class="wpcf7-form-control-wrap sconto">
                                                        	<div class="status_coupon"></div>
                      										<input name="coupon" id="coupon" value="" size="40" class="wpcf7-form-control wpcf7-text form-control" aria-invalid="false" type="text">
                      									</span>
                      								</div>
                                                    <div class="form-group col-md-6">
                                                        <label for="btnfield_phone"><?=$dizionario['conferma']['valore_coupon']?></label>
                                                        <br>
                                                        <span class="wpcf7-form-control-wrap valore_coupon">-</span>
                                                        <input type="hidden" id="importo_coupon" name="importo_coupon" value="0">
                                                    </div>
                                                </div>
            								<? 
    											} elseif ($posti_esauriti == true) {
                                                    echo "<table style='width: 100%; margin-top: 5px;'><tr><td colspan='9' style='text-align:center; line-height:24px; font-size: 16px; color: #F00; font-weight: bold;'>I posti per l'itinerario selezionato non sono disponibili. Per verificare disponibilit&agrave; lastminute contatti i nostri uffici ai numeri +39 0362 1850590 - 0942 981409/982115 <td/></tr></table>";
                                                } else {
                                                    echo "<table style='width: 100%; margin-top: 5px;'><tr><td colspan='9' style='text-align:center; line-height:24px; font-size: 16px; color: #F00; font-weight: bold;'>L' acquisto di Titoli di viaggio on-line è possibile con almeno 1 giorno di anticipo rispetto alla data di partenza.<br /><br />Per informazioni su partenze / prenotazioni / disponibilità di posti nelle prossime 24 ore contatti i nostri uffici ai numeri +39 0942.981409 / +39 0942.982115 <td/></tr></table>";
                                                } 
                                            ?>
											
											<h3 class="titleCheck"><i class="fa fa-credit-card" aria-hidden="true" style="color:#ff6633;"></i> <?=$dizionario['conferma']['pagamento']?></h3>
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="btnfield_phone"><?=$dizionario['conferma']['pagamento_seleziona']?>: <span class="required">*</span></label>
                                                    <br>
                                                    <div class="paymentCont">
                                                        <div class="paymentWrap">
                                                            <div class="btn-group paymentBtnGroup btn-group-justified" data-toggle="buttons">
                                                                <label class="btn paymentMethod active">
                                                                   <div class="method stripe"></div>
                                                                    <input id="stripe" type="radio" name="metodo_pagamento" checked tabindex="5" value="2" />
                                                                   <!--   <div class="method credit-card"></div>
                                                                    <input id="banca" type="radio" name="metodo_pagamento" tabindex="3" value="1" />
                                                                    -->
                                                                </label>
																<?php if(Config::$paypalActive){?>
                                                                <label class="btn paymentMethod ">
                                                                    <div class="method paypal"></div>
                                                                    <input id="paypal" type="radio" name="metodo_pagamento" tabindex="4" value="1" />
                                                                </label>
																<?php } ?>
                                                                <?php if(Config::$telecashActive){?>
                                                                <label class="btn paymentMethod">
                                                                    <div class="method telecheck"></div>
                                                                    <input id="telecheck" type="radio" name="metodo_pagamento" tabindex="5" value="1" />
                                                                </label>
                                                                <?php } ?>
                                                            </div>
                                                            
                                                            <div id="metodo_pagamento_val"></div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php if($viaggioItalia && Config::$fatturaincloudBiglietti) { ?>
                                                <h3 class="titleCheck"><i class="fa fa-credit-card" aria-hidden="true" style="color:#ff6633;"></i> <?=$dizionario['conferma']['fattura']?></h3>
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="btnfield_phone"><?=$dizionario['conferma']['fattura_si_no']?>: <span class="required">*</span></label>
                                                        
                                                        <select class="fatturazione select2" name="fattura" id="fattura" onChange="javascript:changeFattura()">
                                                            <option value="0">- <?=$dizionario['conferma']['no']?> -</option>
                                                            <option value="1"><?=$dizionario['conferma']['si']?></option>
                                                        </select>
                                                        <div class="datiFattura" style="display: none;">
                                                        	<h4>Dati Fatturazione</h4>
                                                        	<div class="row">
                                                        		<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['ragione_sociale']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_ragionesociale" id="fattura_ragionesociale" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_ragionesociale" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['iva']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_partita_iva" id="fattura_partita_iva" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_partita_iva" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['cod_fisc']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_codice_fiscale" id="fattura_codice_fiscale" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_codice_fiscale" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
            												</div>
            												<div class="row">
                												<div class="form-group col-md-12">
                                                                    <label><?=$dizionario['area_clienti']['indirizzo']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_indirizzo" id="fattura_indirizzo" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_indirizzo" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['cap']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_cap" id="fattura_cap" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_cap" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['provincia']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_provincia" id="fattura_provincia" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_provincia" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['nazione']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                                                                        <select name="fattura_nazione" id="fattura_nazione" class="select2 fattura_nazione">
                                                                        	<?php foreach ($arr_nazione_residenza as $id => $value) { ?>
                                                                        		<?php if ($value['NazioneId'] == 1) {?>
                                                                        			<option selected value='<?php echo $value['NazioneId'];?>'><?php echo $value['Nazione'];?></option>
                                                                        		<?php } else { ?>
                                                                        			<option value='<?php echo $value['NazioneId'];?>'><?php echo $value['Nazione'];?></option>
                                                                        		<?php } ?>
                                                                        	<?php } ?>
                                                                        </select>
                													</span><br>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['comune']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_comune" id="fattura_comune" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_comune" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                											</div>
                											<div class="row">
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['email_pec']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_emailpec" id="fattura_emailpec" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_emailpec" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['codice_destinatario']?>:</label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_codice_destinatario" id="fattura_codice_destinatario" value="" size="40" class="wpcf7-form-control wpcf7-text  form-control" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['email']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_email" id="fattura_email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_email" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                												<div class="form-group col-md-6">
                                                                    <label><?=$dizionario['area_clienti']['tel']?>: <span class="required">*</span></label>
                                                                    <br>
                                                                    <span class="wpcf7-form-control-wrap">
                                                                        <div class="status"></div>
                														<input name="fattura_tel" id="fattura_tel" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required form-control fattura_tel" aria-required="true" aria-invalid="false" type="text">
                													</span>
                												</div>
                                                        	</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            
											<div class="row">
												<div class="form-group col-md-12"> 
													<span class="wpcf7-form-control-wrap ext_info">
                              							<textarea readonly="readonly" name="ext_info" cols="40" rows="10" class="wpcf7-form-control wpcf7-textarea form-control" value="" id="btnfield_ext_info" aria-invalid="false">
                              								<?=$dizionario['conferma']['termini']?>
                              							</textarea>
													</span>
												</div>
												<div class="form-group col-md-6">
													<div id="privacy_error"></div>
                                                    <label class="concheck"><?=$dizionario['conferma']['accetto_termini']?>
                                                        <input type="checkbox" id="privacy" name="privacy" checked="checked">
                                                        <span class="checkmark1"></span>
                                                    </label>
                                                </div>
											</div>
										
    										<div class="row">
                                                <div class="checkButtons">
                                                    <label for="step3" id="back-step3" class="back" >
                                                        <div class="btn btn-default btn-grey btn-lg"><span class="glyphicon glyphicon-chevron-left"></span> <?=$dizionario['conferma']['indietro']?></div>
                                                    </label>
                                                    <label class="continue">
                                                        <button type="button" class="blue_btn btn btn-default btn-success btn-lg"><?=$dizionario['prenota_ricerca']?>! <span class="glyphicon glyphicon-chevron-right"></span></button>
                                                    </label>
                                                </div>
    										</div>
                                                
										</div>
                                        <!--/.STEP 3-->                                        
                                    </form>
                               	</div>
                           	</div>
						</div>
						
						<div class="col-md-3">
        					<div class="package-sidebar">
        						<h3 class="titleSummary"><?=$dizionario['conferma']['riepilogo']?></h3>
        						
        						<?
                                $dataPickUp = new DT($PercorsoAndata['DataPickUp'], 'Y-m-d');
                                $orarioPickUp = substr($PercorsoAndata['OrarioPickUp'], 0, 5);
                            
                                $dataDropOff = new DT($PercorsoAndata['DataDropOff'], 'Y-m-d');
                                $orarioDropOff = substr($PercorsoAndata['OrarioDropOff'], 0, 5);
                            
                                $comunePartenza = $PercorsoAndata['ComunePickUp'];
                                $fermataPartenza = $PercorsoAndata['FermataPickUp'];
                                $comuneArrivo = $PercorsoAndata['ComuneDropOff'];
                                $fermataArrivo = $PercorsoAndata['FermataDropOff'];
                                ?>
        						<ul id="andata" class="summary">
                                    <li><strong><?=$dizionario['Andata']?></strong></li>
                                    <li><span><?=$dizionario['conferma']['data_partenza']?>:</span> <?= $dataPickUp->getDate('d/m/Y') ?></li>
                                    <li><span><?=$dizionario['conferma']['ora_partenza']?>:</span> <?= $orarioPickUp ?></li>
                                    <li><span style="float:none;"><?=$dizionario['search']['luogo_partenza']?>:</span><br><?= $comunePartenza . " (" . $fermataPartenza . ")" ?></li>
                                    <li><span style="float:none;"><?=$dizionario['search']['luogo_arrivo']?>:</span><br><?= $comuneArrivo . " (" . $fermataArrivo . ")" ?></li>
                                    <li><span><?=$dizionario['conferma']['data_arrivo']?>:</span> <?= $dataDropOff->getDate('d/m/Y') ?></li>
                                    <li><span><?=$dizionario['conferma']['ora_arrivo']?>:</span> <?= $orarioDropOff ?></li>
                                </ul>
                                
                                <?php if ($arr['TipoViaggioId'] == 2) { ?>
                                	<?php
                                    $dataPickUp = new DT($PercorsoRitorno['DataPickUp'], 'Y-m-d');
                                    $orarioPickUp = substr($PercorsoRitorno['OrarioPickUp'], 0, 5);
                            
                                    $dataDropOff = new DT($PercorsoRitorno['DataDropOff'], 'Y-m-d');
                                    $orarioDropOff = substr($PercorsoRitorno['OrarioDropOff'], 0, 5);
                            
                                    $comunePartenza = $PercorsoRitorno['ComunePickUp'];
                                    $fermataPartenza = $PercorsoRitorno['FermataPickUp'];
                                    $comuneArrivo = $PercorsoRitorno['ComuneDropOff'];
                                    $fermataArrivo = $PercorsoRitorno['FermataDropOff'];
                                    ?>
                                    <ul id="ritorno" class="summary">
                                    	<li><strong><?=$dizionario['Ritorno']?></strong></li>
                                        <li><span><?=$dizionario['conferma']['data_partenza']?>:</span> <?= $dataPickUp->getDate('d/m/Y') ?></li>
                                        <li><span><?=$dizionario['conferma']['ora_partenza']?>:</span> <?= $orarioPickUp ?></li>
                                        <li><span style="float:none;"><?=$dizionario['search']['luogo_partenza']?>:</span><br><?= $comunePartenza . " (" . $fermataPartenza . ")" ?></li>
                                        <li><span style="float:none;"><?=$dizionario['search']['luogo_arrivo']?>:</span><br><?= $comuneArrivo . " (" . $fermataArrivo . ")" ?></li>
                                        <li><span><?=$dizionario['conferma']['data_arrivo']?>:</span> <?= $dataDropOff->getDate('d/m/Y') ?></li>
                                        <li><span><?=$dizionario['conferma']['ora_arrivo']?>:</span> <?= $orarioDropOff ?></li>
                                    </ul>
                                    
                                <? }  else if ($arr['TipoViaggioId'] == 3) { ?>
                                	<?
                                    $comunePartenza = $PercorsoAndata['ComuneDropOff'];
                                    $fermataPartenza = $PercorsoAndata['FermataDropOff'];
                                    $comuneArrivo = $PercorsoAndata['ComunePickUp'];
                                    $fermataArrivo = $PercorsoAndata['FermataPickUp'];
                                    ?>
                                    <ul id="ritorno" class="summary">
                                    	<li><strong><?=$dizionario['Ritorno-Open']?></strong></li>
                                        <li><span><?=$dizionario['conferma']['data_partenza']?>:</span> -</li>
                                        <li><span><?=$dizionario['conferma']['ora_partenza']?>:</span> -</li>
                                        <li><span><?=$dizionario['search']['luogo_partenza']?>:</span> <?= $comunePartenza . " (" . $fermataPartenza . ")" ?></li>
                                        <li><span><?=$dizionario['search']['luogo_arrivo']?>:</span> <?= $comuneArrivo . " (" . $fermataArrivo . ")" ?></li>
                                        <li><span><?=$dizionario['conferma']['data_arrivo']?>:</span> -</li>
                                        <li><span><?=$dizionario['conferma']['ora_arrivo']?>:</span> -</li>
                                    </ul> 
                                <? } ?>
                                
                                <p class="totalPrice">
	                                <?=$dizionario['conferma']['prezzo_finale']?> <span id="totale_finale"><?php echo $prezzoTotale; ?></span>
								</p>
                                
                                <div class="need-help">
                                    <h3 class="title"><?=$dizionario['conferma']['aiuto_1']?></h3>
                                    <div><?=$dizionario['conferma']['aiuto_2']?>:</div>
                                    <p><?=$dizionario['linee']['italia']?>:</p>
                                    <p><i class="fa fa-phone-square"></i>+39 0362 1850590</p>
                                    <p><i class="fa fa-phone-square"></i>+39 0942981409/982115</p>
                                    <p><?=$dizionario['linee']['germania']?>:</p>
                                    <p><i class="fa fa-phone-square"></i>0221613854/613892</p>
                                    <p><i class="fa fa-envelope-square"></i>info@onebus.it</p>
                                </div>
        					</div>
        				</div>
					</div>
					<?php } else { ?>
						<div id="header2">
                            <div class="container">
                                <div class="">
                                    <h4 style="width: 400px; margin: 10px auto 0;"><?=$dizionario['conferma']['errore']?></h4>
                                    <hr class="near_separator"/>
    
                                    <p class="big_text">
                                        <span><?=$dizionario['conferma']['errore_mess']?>
                                    </p>
                                </div><!-- banner_container -->
                                
                                <div class="clear"></div>
                                
                            </div><!-- container -->
                        </div><!-- header -->
					<?php } ?>
				
				</div>
                <!--/#content-->
            </div>
            <!--/container-->

            <!-- Bottom
            ================================================== -->
        	<? include_once($basepath."/include/bottom.php") ?>
        	 
        </section>
        <!--/#main-->
<?php $_SESSION['CURRENT_SEARCH'] = serialize($search);?>        
        
        <!-- Footer
        ================================================== -->
        <? include_once($basepath."/include/footer.php") ?>    
        
    </div>
    <!-- #page -->
  
   	<? include_once($basepath."/include/html_close.php") ?>   

<div id="myModal" class="modal">
  <!-- Modal content -->
  <div class="modal-content">
    <span class="close">&times;</span>
    <p>ATTENZIONE PRENDERE VISIONE SULLE CONDIZIONI DI RIENTRO NEI PAESI EUROPEI:

	<a style="color:red;" target="_new" href="http://www.viaggiaresicuri.it/" title="Rientro In Italia">Rientro In Italia</a><br />
	<a style="color:red;" target="_new" href="https://ambberlino.esteri.it/ambasciata_berlino/it/in_linea_con_utente/covid-19/emergenza-coronavirus-difficolta.html" title="Rientro In Germania">Rientro In Germania</a><br />
	</p>
  </div>
</div>

<style>
/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: absolute; 
    z-index: 99999999;
    padding-top: 100px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    /*width: 50%;*/
	width:42%;
	text-align: center;
}

.modal-content p{
    font-size: 26px;
	font-weight: 300;
	letter-spacing: 0px;
	line-height: 35px;
}

.modal-content a{
    font-size: 26px;
	font-weight: 300;
	letter-spacing: 0px;
	line-height: 35px;
}

/* The Close Button */
.close {
    color: #aaaaaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

.close2 {
	position: relative;
    top: 23px;
    left: 18px;
    width: 1.5em;
    height: 1.5em;
    background: #262e35;
    color: #e7111b;
    border-radius: 50%;
	border: 1px solid #262c32;
}
.close2:hover {
	background: #e7111b;
    color: #262e35;
}

@media only screen and (max-width: 1030px){
	.modal-content {
		width:80%
	}
 } 
 
 @media only screen and (max-width: 480px){
	.modal-content {
		width:90%
	}
 } 
</style>
<script>
	// Get the modal
	var modal = document.getElementById('myModal');

	// Get the <span> element that closes the modal
	var span = document.getElementsByClassName("close")[0];

	// When the user clicks on <span> (x), close the modal
	span.onclick = function() {
		modal.style.display = "none";
	}

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
			document.body.style.overflow = 'auto';
		}
	}
	
    // Delayed Modal Display + Cookie On Click
    $(document).ready(function() {
		
		// Show the modal, with delay func.
		function show_modal(){
			modal.style.display = "block"; //PER DISABILITARE COMMENTARE QUESTA RIGA
			document.body.style.overflow = 'hidden';
			window.scrollTo(0, 0);
		}

		// Set delay func. time in milliseconds
		window.setTimeout(show_modal, 10);

		// On click of specified class (e.g. 'nothanks'), trigger cookie. Scade in un 1 giorno
		$(".close").click(function() {
			document.body.style.overflow = 'auto';
		});
    });
    
    function changeFattura(){
    	$(".datiFattura").toggle();
    }
  </script>


</body>
</html>