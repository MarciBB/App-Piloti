<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['area_clienti']['prenotazioni'];
$PageDescription = $dizionario['area_clienti']['descr'];
$PageKeywords = $dizionario['area_clienti']['key'];

$page_title = $dizionario['area_clienti']['prenotazioni'];
$page_parent = $dizionario['area_clienti']['descr'];

$LinkActive = 4;

if ( ! session_id() ) {
   session_start();
}

if(isset($_SESSION['USER'])) {
	$userInfo = $_SESSION['USER'];
}

if(!isset($userInfo) || $userInfo == "" || !isset($_GET['ticket'])){
    header('location: /area-clienti.php');
    die();
}

global $search;

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;

global $db;
$db = new Database();
$db->connect();

//verifico che la prenotazione appartiene all'utente connesso.
$sql = "Select * from RT_Prenotazione WHERE PrenotazioneId = ".$_GET['ticket'];
$prenotazione = $db->query_first($sql);
if(!isset($prenotazione['PrenotazioneId']) || $prenotazione['MembershipClubCode'] != $userInfo['MembershipClubCode']) {
	header('location: /area-clienti.php');
    die();
}

//recupero percorso di andata 
$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE Direzione = 'A' AND PrenotazioneId = ".$prenotazione['PrenotazioneId'];
$percorsoA = $db->query_first($sql);
//recupero percorso di ritorno 
if($prenotazione['TipoViaggioId'] == 1) {
	$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE Direzione = 'R' AND PrenotazioneId = ".$prenotazione['PrenotazioneId'];
	$percorsoR = $db->query_first($sql);
}

//recupero le informazioni della prenotazione
	
	$CorsaId = $percorsoA['CorsaId'];
	$DataPartenza = $percorsoA['CorsaDataPartenza'];
	$dateTime = new DateTime($DataPartenza);
	$datacorrente = $dateTime->format('d/m/Y');
	$PrenotazioneId = $prenotazione['PrenotazioneId'];


	$DataPartenzaRitorno = null;
	$CorsaIdRitorno = null;
	if ($prenotazione['TipoViaggioId'] == 1) {
		$DataPartenzaRitorno = $percorsoR['CorsaDataPartenza'];
		$CorsaIdRitorno = $percorsoR['CorsaId'];
	}

	//recupero la corsa selezionata
	$sqlCorsa = "select 
			c.`CorsaId` AS `CorsaId`,
			appcal.`AppCalendarioData` AS `AppCalendarioData`,
			date_format(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`,
			c.`CorsaNome` AS `CorsaNome`,
			c.`LineaId` AS `LineaId`,
			`RT_Linea`.`LineaNome` AS `LineaNome`,
			c.`OrarioPartenza` AS `OrarioPartenza`
		from
			`RT_Corsa` c
			join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
			join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
			join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
				join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
		where appcal.`AppCalendarioData` = '$DataPartenza' and c.`CorsaId` = $CorsaId";
	$corsa = $db->query_first($sqlCorsa);

	//recupero la corsa di ritorno se presente
	if (isset($DataPartenzaRitorno) && isset($CorsaIdRitorno)) {
		$sqlCorsa = "select 
			c.`CorsaId` AS `CorsaId`,
			appcal.`AppCalendarioData` AS `AppCalendarioData`,
			date_format(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`,
			c.`CorsaNome` AS `CorsaNome`,
			c.`LineaId` AS `LineaId`,
			`RT_Linea`.`LineaNome` AS `LineaNome`,
			c.`OrarioPartenza` AS `OrarioPartenza`
		from
			`RT_Corsa` c
			join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
			join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
			join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
				join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
		where appcal.`AppCalendarioData` = '$DataPartenzaRitorno' and c.`CorsaId` = $CorsaIdRitorno";
		$corsaRitorno = $db->query_first($sqlCorsa);
	}


	//recupero il titolo 
	$queryTitolo = "SELECT * FROM RT_PrenotazioneTitolo Where PrenotazioneId = '$PrenotazioneId' AND Stato = 1 AND Cancella = 0 AND TipoTitolo = 'E'";
	$titolo = $db->query_first($queryTitolo);
	$prenotazioneTitoloId = $titolo['PrenotazioneTitoloId'];

	//recupero la prenotazione eseguita da questo operatori la corsa selezionata
	$sqlPrenotazioni = "select
				`apps`.`PrenotazioneStato` AS `StatoPrenotazione`,
				`g`.`RagioneSociale` AS `RagioneSociale`,
				(case
					when
						(`rdc`.`Codice` is not null)
					then
						concat(convert( `rdc`.`Codice` using utf8),
								_utf8'/',
								cast(`rdc`.`Anno` as char (5) charset utf8))
					when (`rdc`.`Codice` is null AND `apps`.`PrenotazioneStato` = _latin1'Biglietto emesso') 
						then 
							(convert((SELECT concat(Codice,_utf8'/',Anno) FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = `p`.`PrenotazioneId` AND TipoTitolo = 'E') using utf8))
								
					else concat(convert( `rdc`.`CodicePrenotazione` using utf8),
							_utf8'/',
							cast(`rdc`.`PrenotazioneNumero` as char (10) charset utf8))
				end) AS `NumeroBiglietto`,
				(case
					when (`tp`.`OccupaPosto` = 1) then `p`.`ClienteNome`
					else concat('Servizio: ', _latin1' ', `pd`.`Nome`)
				end) AS `Cliente`,
				concat(_latin1'+',
						`p`.`ClienteCellularePrefisso`,
						_latin1' ',
						`p`.`ClienteCellulare`) AS `ClienteCellulare`,
				concat(_latin1'+',
						`p`.`ClienteCellularePrefisso`,
						_latin1' ',
						`p`.`ClienteCellulareFamiliare`) AS `ClienteWhatsapp`,
				p.ClienteEmail AS ClienteEmail,
				`pd`.`OrarioPartenza` AS `OrarioPartenza`,
				`pd`.`OrarioArrivo` AS `OrarioArrivo`,
				`pd`.`DataArrivo` AS `DataArrivo`,
				`pd`.`ComunePartenza` AS `ComunePartenza`,
				`pd`.`ComuneArrivo` AS `ComuneArrivo`,
				(case
					when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then _utf8'CS'
					else _utf8'A/R'
				end) AS `TipoViaggio`,
				(case
					when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then round(`pd`.`Importo`, 2)
					else round((`pb`.`PrezzoPax` + ((`pb`.`AumentoPax` - `pb`.`RiduzionePax`) / `pb`.`NumeroPax`)),
							2)
				end) AS `Importo`,
				`pd`.`CorsaId` AS `CorsaId`,
				`pd`.`DataPartenza` AS `DataPartenza`,
				`pd`.`PrenotazioneDettaglioId` AS `PrenotazioneDettaglioId`,
				`pd`.`DataInizioItinerario` AS `DataInizioItinerario`,
				`p`.`PrenotazioneId` AS PrenotazioneId,
				`p`.`DataIns` AS DataIns,
				`p`.`Libera` AS Libera,
				`pd`.`LineaId` AS `LineaId`,
				l.TipoTour AS TipoTour
			from
					(((((((`RT_PrenotazioneDettaglio` `pd`
			left join `RT_Prenotazione` `p` ON ((`pd`.`PrenotazioneId` = `p`.`PrenotazioneId`)))
			left join `RT_AppPrenotazioneStato` `apps` ON ((`p`.`PrenotazioneStato` = `apps`.`PrenotazioneStatoId`)))
			left join `Operatore` `o` ON ((`p`.`OpeIns` = `o`.`OperatoreId`)))
			left join `Gestore` `g` ON ((`o`.`GestoreId` = `g`.`GestoreId`)))
			left join `RT_PrenotazioneDettaglioCompleto` `rdc` ON (((`p`.`PrenotazioneId` = `rdc`.`PrenotazioneId`)
				and (`rdc`.`PrenotazioneId` = `pd`.`PrenotazioneId`)
				and (`rdc`.`PrenotazioneNumero` = `pd`.`PrenotazioneNumero`)
				and (`rdc`.`DataPartenza` = `pd`.`DataPartenza`))))
			left join `RT_PrenotazioneBiglietto` `pb` ON (((`pb`.`PrenotazioneId` = `p`.`PrenotazioneId`)
				and (`pb`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
			left join `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBigliettoId` = `pb`.`TipologiaBigliettoId`))))
			left join RT_Linea l ON l.LineaId = pd.LineaId
			where
				(((`p`.`PrenotazioneStato` = 3)
					or (`p`.`PrenotazioneStato` = 1)
					or (`p`.`PrenotazioneStato` = 2))
					and (`pd`.`Escludi` <> 1)
					and (`pd`.`Rimborso` <> 1))
					and `tp`.`OccupaPosto` = 1
					and (`rdc`.Codice not like 'E-%' or `rdc`.Codice is null)
					and `pd`.`CorsaId` = $CorsaId
					and `pd`.`DataPartenza` = '$DataPartenza'
					and `p`.`PrenotazioneId` = $PrenotazioneId";
	$prenotazione = $db->query_first($sqlPrenotazioni);

	$dateTime = new DateTime($prenotazione['OrarioPartenza']);
	$formattedOrarioPartenza = $dateTime->format('H:i');

	$dateTime = new DateTime($prenotazione['OrarioArrivo']);
	$formattedOrarioArrivo = $dateTime->format('H:i');

	//recupero lista passeggeri
	$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
			LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
			WHERE PrenotazioneId = " . $prenotazione['PrenotazioneId'] . " and t.OccupaPosto = 1";
	$passeggeriBiglietto = $db->fetch_array($sql);

	//recupero lista servizi
	$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
			LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
			WHERE PrenotazioneId = " . $prenotazione['PrenotazioneId'] . " and t.OccupaPosto = 0";
	$serviziBiglietto = $db->fetch_array($sql);

	if (isset($DataPartenzaRitorno) && isset($CorsaIdRitorno)) {
		//recupero la prenotazione eseguita da questo operatori la corsa di ritorno se presente
		$sqlPrenotazioni = "select
			`apps`.`PrenotazioneStato` AS `StatoPrenotazione`,
			`g`.`RagioneSociale` AS `RagioneSociale`,
			(case
				when
					(`rdc`.`Codice` is not null)
				then
					concat(convert( `rdc`.`Codice` using utf8),
							_utf8'/',
							cast(`rdc`.`Anno` as char (5) charset utf8))
				when (`rdc`.`Codice` is null AND `apps`.`PrenotazioneStato` = _latin1'Biglietto emesso') 
					then 
						(convert((SELECT concat(Codice,_utf8'/',Anno) FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = `p`.`PrenotazioneId` AND TipoTitolo = 'E') using utf8))
							
				else concat(convert( `rdc`.`CodicePrenotazione` using utf8),
						_utf8'/',
						cast(`rdc`.`PrenotazioneNumero` as char (10) charset utf8))
			end) AS `NumeroBiglietto`,
			(case
				when (`tp`.`OccupaPosto` = 1) then `p`.`ClienteNome`
				else concat('Servizio: ', _latin1' ', `pd`.`Nome`)
			end) AS `Cliente`,
			concat(_latin1'+',
					`p`.`ClienteCellularePrefisso`,
					_latin1' ',
					`p`.`ClienteCellulare`) AS `ClienteCellulare`,
			concat(_latin1'+',
					`p`.`ClienteCellularePrefisso`,
					_latin1' ',
					`p`.`ClienteCellulareFamiliare`) AS `ClienteWhatsapp`,
			p.ClienteEmail AS ClienteEmail,
			`pd`.`OrarioPartenza` AS `OrarioPartenza`,
			`pd`.`OrarioArrivo` AS `OrarioArrivo`,
			`pd`.`DataArrivo` AS `DataArrivo`,
			`pd`.`ComunePartenza` AS `ComunePartenza`,
			`pd`.`ComuneArrivo` AS `ComuneArrivo`,
			(case
				when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then _utf8'CS'
				else _utf8'A/R'
			end) AS `TipoViaggio`,
			(case
				when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then round(`pd`.`Importo`, 2)
				else round((`pb`.`PrezzoPax` + ((`pb`.`AumentoPax` - `pb`.`RiduzionePax`) / `pb`.`NumeroPax`)),
						2)
			end) AS `Importo`,
			`pd`.`CorsaId` AS `CorsaId`,
			`pd`.`DataPartenza` AS `DataPartenza`,
			`pd`.`PrenotazioneDettaglioId` AS `PrenotazioneDettaglioId`,
			`pd`.`DataInizioItinerario` AS `DataInizioItinerario`,
			`p`.`PrenotazioneId` AS PrenotazioneId,
			`p`.`DataIns` AS DataIns,
			`p`.`Libera` AS Libera,
			`pd`.`LineaId` AS `LineaId`,
			l.TipoTour AS TipoTour
		from
				(((((((`RT_PrenotazioneDettaglio` `pd`
		left join `RT_Prenotazione` `p` ON ((`pd`.`PrenotazioneId` = `p`.`PrenotazioneId`)))
		left join `RT_AppPrenotazioneStato` `apps` ON ((`p`.`PrenotazioneStato` = `apps`.`PrenotazioneStatoId`)))
		left join `Operatore` `o` ON ((`p`.`OpeIns` = `o`.`OperatoreId`)))
		left join `Gestore` `g` ON ((`o`.`GestoreId` = `g`.`GestoreId`)))
		left join `RT_PrenotazioneDettaglioCompleto` `rdc` ON (((`p`.`PrenotazioneId` = `rdc`.`PrenotazioneId`)
			and (`rdc`.`PrenotazioneId` = `pd`.`PrenotazioneId`)
			and (`rdc`.`PrenotazioneNumero` = `pd`.`PrenotazioneNumero`)
			and (`rdc`.`DataPartenza` = `pd`.`DataPartenza`))))
		left join `RT_PrenotazioneBiglietto` `pb` ON (((`pb`.`PrenotazioneId` = `p`.`PrenotazioneId`)
			and (`pb`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
		left join `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBigliettoId` = `pb`.`TipologiaBigliettoId`))))
		left join RT_Linea l ON l.LineaId = pd.LineaId
		where
			(((`p`.`PrenotazioneStato` = 3)
				or (`p`.`PrenotazioneStato` = 1)
				or (`p`.`PrenotazioneStato` = 2))
				and (`pd`.`Escludi` <> 1)
				and (`pd`.`Rimborso` <> 1))
				and `tp`.`OccupaPosto` = 1
				and (`rdc`.Codice not like 'E-%' or `rdc`.Codice is null)
				and `pd`.`CorsaId` = $CorsaIdRitorno
				and `pd`.`DataPartenza` = '$DataPartenzaRitorno'
				and `p`.`PrenotazioneId` = $PrenotazioneId";
		$prenotazioneRitorno = $db->query_first($sqlPrenotazioni);

		$dateTime = new DateTime($prenotazioneRitorno['OrarioPartenza']);
		$formattedOrarioPartenzaRitorno = $dateTime->format('H:i');

		$dateTime = new DateTime($prenotazioneRitorno['OrarioArrivo']);
		$formattedOrarioArrivoRitorno = $dateTime->format('H:i');
	}

?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>
<body class="main-bg bg-ancore" id="validazione-ingresso">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
			<div class="bg-sea confirm-title">
				<h2>Dettaglio Prenotazione</h2>
				<div class="operator">
					Ecco i riferimenti della<br>
					prenotazione validata.
				</div>
			</div>
			<div class="content">
				<div class="info-container dark-bg-container">

					<?php
					if (isset($DataPartenzaRitorno) && isset($CorsaIdRitorno)) { ?>
						<div class="info">
							<div class="info-title"><b>- Andata -</b></div>
						</div>
					<?php } ?>
					<div class="info">
						<div class="info-title">Nome Tour:</div>
						<div class="info-value">
							<b><?= $corsa['LineaNome'] ?>
								<br>
								<?= $corsa['CorsaNome'] ?></b>
						</div>
					</div>
					<div class="info">
						<div class="info-title">Data/Orario:</div>
						<div class="info-value"><?= $corsa['DataPartenzaFormattata'] ?> <?= $formattedOrarioPartenza ?> - <?= $formattedOrarioArrivo ?></div>
					</div>
					<?php
					if (isset($DataPartenzaRitorno) && isset($CorsaIdRitorno)) { ?>
						<div class="info">
							<div class="info-title"><b>- Ritorno -</b></div>
						</div>
						<div class="info">
							<div class="info-title">Nome Tour:</div>
							<div class="info-value">
								<b><?= $corsaRitorno['LineaNome'] ?>
									<br>
									<?= $corsaRitorno['CorsaNome'] ?></b>
							</div>
						</div>
						<div class="info">
							<div class="info-title">Data/Orario:</div>
							<div class="info-value"><?= $corsaRitorno['DataPartenzaFormattata'] ?> <?= $formattedOrarioPartenzaRitorno ?> - <?= $formattedOrarioArrivoRitorno ?></div>
						</div>
					<?php }
					?>
					<div class="info">
						<div class="info-title">Prenotazione / Biglietto:</div>
						<div class="info-value"><?= $prenotazione['NumeroBiglietto'] ?></div>
					</div>

					<div class="info">
						<div class="info-title">Stato:</div>
						<div class="info-value"><?= $prenotazione['StatoPrenotazione'] ?></div>
					</div>

					<div class="info">
						<div class="info-title">Cliente:</div>
						<div class="info-value"><?= $prenotazione['Cliente'] ?></div>
						<?php if (isset($prenotazione['ClienteCellulare']) && $prenotazione['ClienteCellulare'] != '') { ?>
							<div class="info-value info-contact"><a class="white" href="tel:<?= str_replace(' ', '', $prenotazione['ClienteCellulare']) ?>"><i class="fa fa-phone" aria-hidden="true"></i> <?= $prenotazione['ClienteCellulare'] ?></a></div>
						<?php } ?>
						<?php if (isset($prenotazione['ClienteWhatsapp']) && $prenotazione['ClienteWhatsapp'] != '' && $prenotazione['ClienteWhatsapp'] != '+39 ') { ?>
							<div class="info-value info-contact"><a class="white" target="_blank" href="https://wa.me/<?= str_replace(' ', '', $prenotazione['ClienteWhatsapp']) ?>"><img class="iconImage" src="css/imgs/whatsapp_logo.png"> <?= $prenotazione['ClienteWhatsapp'] ?></a></div>
						<?php } ?>
						<?php if (isset($prenotazione['ClienteEmail']) && $prenotazione['ClienteEmail'] != '') { ?>
							<div class="info-value info-contact"><a class="white" target="_blank" href="mailto:<?= str_replace(' ', '', $prenotazione['ClienteEmail']) ?>"><i class="fa fa-envelope-o" aria-hidden="true"></i> <?= $prenotazione['ClienteEmail'] ?></a></div>
						<?php } ?>
					</div>

					<?php
					if (count($passeggeriBiglietto) > 0) { ?>
						<div class="info">
							<div class="info-title">Passeggeri:</div>
							<?php foreach ($passeggeriBiglietto as $p) {
								echo "<div class='info-value'>- x" . $p['NumeroPax'] . " " . $p['TipologiaBiglietto'] . "</div>";
							} ?>
						</div>
					<?php } ?>

					<?php
					if (count($serviziBiglietto) > 0) { ?>
						<div class="info">
							<div class="info-title">Servizi:</div>
							<?php foreach ($serviziBiglietto as $p) {
								echo "<div class='info-value'>- x" . $p['NumeroPax'] . " " . $p['TipologiaBiglietto'] . "</div>";
							} ?>
						</div>
					<?php } ?>
					<div class="info">
						<div class="info-title">Data prenotazione:</div>
						<?php $dateTime = new DateTime($prenotazione['DataIns']);
						$formattedDate = $dateTime->format('d/m/Y');
						$formattedTime = $dateTime->format('H:i'); ?>
						<div class="info-value"><?= $formattedDate . ' - ' . $formattedTime ?></div>
					</div>
				</div>

				<form method="post" action="<?php echo Config::$UrlMobile; ?>" id="search" target="_blank">

					<input type="hidden" name="prenotazioneId" id="prenotazioneId" value="<?= $PrenotazioneId ?>">
					<input type="hidden" name="corsaId" id="corsaId" value="<?= $CorsaId ?>">
					<input type="hidden" name="dataPartenza" id="dataPartenza" value="<?= $DataPartenza ?>">
					<input type="hidden" name="prenotazioneTitoloId" id="prenotazioneTitoloId" value="<?= $prenotazioneTitoloId ?>">
					<input type="hidden" name="action" id="action" value="stampaCartaceoPdf">
					<button type="submit" class="btn btn-big btn-primary button-action">STAMPA TICKET</button>

				</form>

				<form method="post" action="<?php echo Config::$UrlMobile; ?>" id="search" target="_blank">

					<input type="hidden" name="prenotazioneId" id="prenotazioneId" value="<?= $PrenotazioneId ?>">
					<input type="hidden" name="corsaId" id="corsaId" value="<?= $CorsaId ?>">
					<input type="hidden" name="dataPartenza" id="dataPartenza" value="<?= $DataPartenza ?>">
					<input type="hidden" name="prenotazioneTitoloId" id="prenotazioneTitoloId" value="<?= $prenotazioneTitoloId ?>">
					<input type="hidden" name="action" id="action" value="stampaPdf">
					<button type="submit" class="btn btn-big btn-primary button-action">STAMPA TICKET DIGITALE</button>

				</form>

				<form method="post" action="index.php" id="search">

					<button type="submit" class="btn btn-big btn-secondary button-action">TORNA ALLA HOME</button>

				</form>




			</div>
			
		</div>
	<!-- Bottom
		================================================== -->
		<?php include_once($basepath."/include/bottom.php"); ?>
        	 
        
        <!-- Footer
        ================================================== -->
        <?php include_once($basepath."/include/footer.php"); ?>    
        
    </div>
    <!-- #page -->
  
   	<?php include_once($basepath."/include/html_close.php"); ?>   

</body>

</html>
