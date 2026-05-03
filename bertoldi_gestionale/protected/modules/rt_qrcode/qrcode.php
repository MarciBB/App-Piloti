<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");

$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

include_once($classespath_."class.Form.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.PagamentoTipo.php");

global $dizionario;

$db= new Database();
$db->connect();

$dt = new DT();

$Codice = $_GET['code'];


$sql = "SELECT PrenotazioneNumeroId FROM RT_PrenotazioneNumero WHERE CodiceQrcode='$Codice'";
$prenotazioneId = $db->query_first($sql);
$PrenotazioneNumeroId = $prenotazioneId['PrenotazioneNumeroId']; 
$sql = "SELECT * FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero=$PrenotazioneNumeroId";
$prenotazioni = $db->fetch_array($sql);
if(isset($_GET['app'])) {
	$app = $_GET['app'];
} else {
	$app = null;
}
if(isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = null;
}



if(isset($action) && $action == 'validate'){
	//chiamata tramite app
	$risultato['A'] = 0;
	if (count($prenotazioni) > 0) {
		$prenotazione = $prenotazioni[0];
		
		$infoBiglietto['PrenotazioneId'] = $prenotazione['PrenotazioneId'];
	
		$sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId = $PrenotazioneNumeroId";
		$titolo  = $db->query_first($sql);
	
		$sql = "SELECT ClienteNome, ClienteCellulare, ClienteSessoId FROM RT_Prenotazione WHERE PrenotazioneId = ".$prenotazione['PrenotazioneId'];
		$PrenotazioneInfo  = $db->query_first($sql);
	
		$sql = "SELECT s.CodiceSede, g.RagioneSociale FROM Sede s LEFT JOIN Gestore g ON (s.GestoreId=g.GestoreId) WHERE SedeId=".$prenotazione['SedeIns'];
		$sede = $db->query_first($sql);
		$Agenzia = $sede['RagioneSociale'];
		$Rivendita = $sede['CodiceSede'];
	
		$todays_date = time();
		if (count($prenotazioni) == 1) {
			$event_date = strtotime($dt->format($prenotazione['DataPartenza'], "Y-m-d", "d-m-Y")." ".$prenotazione['OrarioPartenza']);
			$infoBiglietto['CorsaId'] = $prenotazione['CorsaId'];
			if ($todays_date>$event_date)
			{
				$risultato['A'] = 1; //Biglietto Valido � scaduto e non pi&ugrave; utilizzabile.
				$risultato['R'] = 0;
				$infoBiglietto['DataPartenza'] = $prenotazione['DataPartenza'];
			}
			else
			{
				$risultato['A'] = 2; //Biglietto Valido
				$risultato['R'] = 0;
				$infoBiglietto['DataPartenza'] = $prenotazione['DataPartenza'];
			}
		}else{
				
			$event_date_andata = strtotime($dt->format($prenotazione['DataPartenza'], "Y-m-d", "d-m-Y")." ".$prenotazione['OrarioPartenza']);
			$event_date_ritorno = strtotime($dt->format($prenotazioni[1]['DataPartenza'], "Y-m-d", "d-m-Y")." ".$prenotazioni[1]['OrarioPartenza']);
			$infoBiglietto['CorsaId'] = $prenotazione['CorsaId'];
			if ($todays_date > $event_date_andata)
			{
				$risultato['A'] = 1; //Biglietto di Andata Valido � scaduto e non pi&ugrave; utilizzabile.
				$infoBiglietto['DataPartenza'] = $prenotazione['DataPartenza'];
			}
			else
			{
				$risultato['A'] = 2; //Biglietto di Andata valido
				$infoBiglietto['DataPartenza'] = $prenotazione['DataPartenza'];
			}
			$infoBiglietto['CorsaIdRitorno'] = $prenotazioni[1]['CorsaId'];
			if ($todays_date > $event_date_ritorno)
			{
				$risultato['R'] = 1; //Biglietto di Ritorno Valido � scaduto e non pi&ugrave; utilizzabile.
				$infoBiglietto['DataPartenzaRitorno'] = $prenotazioni[1]['DataPartenza'];
			}
			else
			{
				$risultato['R'] = 2; //Biglietto di Ritorno valido
				$infoBiglietto['DataPartenzaRitorno'] = $prenotazioni[1]['DataPartenza'];
			}
		}
	
	
		$infoCliente['ClienteNome'] = utf8_decode($PrenotazioneInfo['ClienteNome']) ;
		$infoCliente['ClienteCellulare'] = $PrenotazioneInfo['ClienteCellulare'];
			
		$infoBiglietto['Codice'] = $titolo['Codice']."/".$titolo['Anno'];
		$infoBiglietto['TipologiaBiglietto'] = $prenotazione['TipologiaBiglietto'];
		$infoBiglietto['PercorsoNome'] =  $prenotazione['PercorsoNome'];
		$infoBiglietto['CorsaNome'] = $prenotazione['CorsaNome'];
		$infoBiglietto['Importo'] = number_format ($prenotazione['Importo'], 2, ",", "" );
		$infoBiglietto['Agenzia'] = utf8_decode($Agenzia) . " (" . $Rivendita . ")";
			
		$infoBiglietto['Partenza'] = utf8_decode($prenotazione['ComunePartenza']) . " - " . utf8_decode($prenotazione['FermataPartenza']) . " del " . $dt->format($prenotazione['DataPartenza'], "Y-m-d", "d/m/Y") . " ore " . $dt->format($prenotazione['OrarioPartenza'], "H:i:s", "H:i");
		$infoBiglietto['Arrivo'] = utf8_decode($prenotazione['ComuneArrivo']) . " - " . utf8_decode($prenotazione['FermataArrivo']) . " il " . $dt->format($prenotazione['DataArrivo'], "Y-m-d", "d/m/Y") . " ore " . $dt->format($prenotazione['OrarioArrivo'], "H:i:s", "H:i");
	
		if (count($prenotazioni) == 2) {
			$prenotazione = $prenotazioni[1];
			$infoBiglietto['PartenzaRitorno'] = utf8_decode($prenotazione['ComunePartenza']) . " - " . utf8_decode($prenotazione['FermataPartenza']) . " del " . $dt->format($prenotazione['DataPartenza'], "Y-m-d", "d/m/Y") . " ore " . $dt->format($prenotazione['OrarioPartenza'], "H:i:s", "H:i");
			$infoBiglietto['ArrivoRitorno'] = utf8_decode($prenotazione['ComuneArrivo']) . " - " . utf8_decode($prenotazione['FermataArrivo']) . " il " . $dt->format($prenotazione['DataArrivo'], "Y-m-d", "d/m/Y") . " ore " . $dt->format($prenotazione['OrarioArrivo'], "H:i:s", "H:i");
		}
	} else {
		$risultato['A'] = 0;
	}
	echo json_encode(array('risultato'=>$risultato, 'infoCliente'=>$infoCliente, 'infoBiglietto'=>$infoBiglietto, 'codice' => $Codice));

} else {
	//chiamata tramite browser
	?>
	<!DOCTYPE html>
	<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= Config::$application_company. " - ".Config::$application_name ?></title>
		<style>
			:root {
				--bg:#f6f7fb;
				--fg:#1f2a37;
				--muted:#6b7280;
				--primary:#2563eb;
				--ok:#059669;
				--warn:#d97706;
				--danger:#dc2626;
				--card:#ffffff;
				--border:#e5e7eb;
			}
			*{box-sizing:border-box}
			html,body{margin:0;padding:0;background:var(--bg);color:var(--fg);font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Ubuntu,"Helvetica Neue",Arial,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";line-height:1.4}
			.container{max-width:880px;margin:24px auto;padding:0 16px}
			.header{display:flex;align-items:center;gap:16px;margin-bottom:16px}
			.header img{height:48px;width:auto}
			.header h1{font-size:18px;margin:0;font-weight:600}
			.badges{display:flex;flex-wrap:wrap;gap:8px;margin-left:auto}
			.badge{padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid var(--border);background:#fff}
			.badge.ok{background:rgba(5,150,105,.1);color:var(--ok);border-color:rgba(5,150,105,.25)}
			.badge.warn{background:rgba(217,119,6,.08);color:var(--warn);border-color:rgba(217,119,6,.25)}
			.badge.danger{background:rgba(220,38,38,.08);color:var(--danger);border-color:rgba(220,38,38,.25)}
			.grid{display:grid;grid-template-columns:1fr;gap:16px}
			@media (min-width: 900px){.grid{grid-template-columns:1fr 1fr}}
			.card{background:var(--card);border:1px solid var(--border);border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.04);padding:16px}
			.card h2{font-size:16px;margin:0 0 12px 0}
			.row{display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px dashed var(--border)}
			.row:last-child{border-bottom:none}
			.k{color:var(--muted)}
			.v{font-weight:600}
			.center-wrap{min-height:60vh;display:flex;align-items:center;justify-content:center}
			.section{margin-top:16px}
			.section h3{font-size:14px;margin:0 0 10px 0;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
			.list{margin:0;padding-left:18px}
			.footer{margin-top:16px;display:flex;justify-content:space-between;align-items:center;color:var(--muted);font-size:12px}
			.inline{display:inline}
			.page-footer{margin:24px 0;color:var(--muted);text-align:center;font-size:12px}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="header">
				<img src="/images/logo.png" alt="Re.Ticket">
	<?php 
	if (count($prenotazioni) > 0) {
		echo '<div class="badges">';
		$prenotazione = $prenotazioni[0];
		
		$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$prenotazione['PrenotazioneId'];
		$temp = $db->query_first($sql);
		$codicePrenotazione = $temp['CodicePrenotazione'];
		$clientePrenotazione = $temp['ClienteNome'];
		if($temp['ClienteNome'] == 0) {
			$tipoTour = $dizionario['generale']['tour_gruppo'];
		} else {
			$tipoTour = $dizionario['generale']['tour_privato'];
		}
		$importoPrenotazione = $temp['TotalePrenotazione'];
		
		$sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId = $PrenotazioneNumeroId";
		$titolo  = $db->query_first($sql);
		
		$sql = "SELECT s.CodiceSede, g.RagioneSociale FROM Sede s LEFT JOIN Gestore g ON (s.GestoreId=g.GestoreId) WHERE SedeId=".$prenotazione['SedeIns'];
		$sede = $db->query_first($sql);
		$Agenzia = $sede['RagioneSociale'];
		$Rivendita = $sede['CodiceSede'];
		
		$todays_date = time();
		if (count($prenotazioni) == 1) {
			$event_date = strtotime($dt->format($prenotazione['DataPartenza'], "Y-m-d", "d-m-Y"));
			if ($todays_date>$event_date) { echo "<span class='badge ok'>".$dizionario['qrcode']['biglietto_valido1']."</span>"; }
			else { echo "<span class='badge ok'>".$dizionario['qrcode']['biglietto_valido2']."</span>"; }
		}else{
			$event_date_andata = strtotime($dt->format($prenotazione['DataPartenza'], "Y-m-d", "d-m-Y"));
			$event_date_ritorno = strtotime($dt->format($prenotazioni[1]['DataPartenza'], "Y-m-d", "d-m-Y"));
			if ($todays_date>$event_date_andata) { echo "<span class='badge ok'>".$dizionario['qrcode']['andata_valido']."</span>"; }
			else { echo "<span class='badge warn'>".$dizionario['qrcode']['andata_valido2']."</span>"; }
			if ($todays_date>$event_date_ritorno) { echo "<span class='badge ok'>".$dizionario['qrcode']['ritorno_valido']."</span>"; }
			else { echo "<span class='badge warn'>".$dizionario['qrcode']['ritorno_valido2']."</span>"; }
		}
		
		?>
		<?php echo '</div>'; } ?>
			</div> <!-- .header -->
			<?php if (count($prenotazioni) > 0) { ?>
			<div class="grid">
				<div class="card">
					<h2><?=$dizionario['qrcode']['info']?></h2>
					<div class="row"><div class="k"><?=$dizionario['qrcode']['intestato']?></div><div class="v"><?= utf8_decode($clientePrenotazione) ?></div></div>
					<div class="row"><div class="k"><?=$dizionario['generale']['tipo_tour']?></div><div class="v"><?= utf8_decode($tipoTour) ?></div></div>
					<div class="row"><div class="k"><?=$dizionario['qrcode']['titolo']?></div><div class="v"><?php if(isset($titolo['Codice'])) { echo $titolo['Codice']."/".$titolo['Anno']; } else { echo $codicePrenotazione."/".$PrenotazioneNumeroId; } ?></div></div>
					<div class="row"><div class="k"><?=$dizionario['percorso']['percorso']?></div><div class="v"><?= $prenotazione['PercorsoNome']?></div></div>
					<div class="row"><div class="k"><?=$dizionario['generale']['linea']?></div><div class="v"><?= $prenotazione['LineaNome']?></div></div>
					<div class="row"><div class="k"><?=$dizionario['generale']['corsa']?></div><div class="v"><?= $prenotazione['CorsaNome']?></div></div>
					<div class="row"><div class="k"><?=$dizionario['generale']['importo']?></div><div class="v"><?= number_format ($titolo['ImportoTitolo'], 2, ",", "" )?>€</div></div>
					<div class="row"><div class="k"><?=$dizionario['qrcode']['emesso_da']?></div><div class="v"><?= utf8_decode($Agenzia) . " (" . $Rivendita . ")"?></div></div>
				</div>
				<div class="card">
					<h2><?=$dizionario['qrcode']['info_viaggio']?></h2>
					<div class="row"><div class="k"><?=$dizionario['generale']['partenza']?></div><div class="v"><?= utf8_decode($prenotazione['ComunePartenza']) . " - " . utf8_decode($prenotazione['FermataPartenza']) . " del " . $dt->format($prenotazione['DataPartenza'], "Y-m-d", "d/m/Y") . " " . $dt->format($prenotazione['OrarioPartenza'], "H:i:s", "H:i") ?></div></div>
					<div class="row"><div class="k"><?=$dizionario['generale']['arrivo']?></div><div class="v"><?= utf8_decode($prenotazione['ComuneArrivo']) . " - " . utf8_decode($prenotazione['FermataArrivo']) . " il " . $dt->format($prenotazione['DataArrivo'], "Y-m-d", "d/m/Y") . " " . $dt->format($prenotazione['OrarioArrivo'], "H:i:s", "H:i") ?></div></div>
		<?php
		if (count($prenotazioni) == 2) {
			$prenotazione = $prenotazioni[1];
			?>
					<div class="section">
						<h3><?=$dizionario['qrcode']['viaggio_ritorno']?></h3>
						<div class="row"><div class="k"><?=$dizionario['generale']['partenza']?></div><div class="v"><?= utf8_decode($prenotazione['ComunePartenza']) . " - " . utf8_decode($prenotazione['FermataPartenza']) . " del " . $dt->format($prenotazione['DataPartenza'], "Y-m-d", "d/m/Y") . " " . $dt->format($prenotazione['OrarioPartenza'], "H:i:s", "H:i") ?></div></div>
						<div class="row"><div class="k"><?=$dizionario['generale']['arrivo']?></div><div class="v"><?= utf8_decode($prenotazione['ComuneArrivo']) . " - " . utf8_decode($prenotazione['FermataArrivo']) . " il " . $dt->format($prenotazione['DataArrivo'], "Y-m-d", "d/m/Y") . " " . $dt->format($prenotazione['OrarioArrivo'], "H:i:s", "H:i") ?></div></div>
					</div>
			<?php
		}?>
				</div>
			</div>
			<div class="card section">
				<h2><?= $dizionario['biglietto']['riepilogo']?></h2>
				<?php 
		$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
				LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
		 		WHERE PrenotazioneId = ".$prenotazione['PrenotazioneId']." and t.OccupaPosto = 1";
		$passeggeriBiglietto = $db->fetch_array($sql);
		$DettaglioOperazione = '';
		if(count($passeggeriBiglietto)) {
			$DettaglioOperazione .= "<div class='section'><h3>".$dizionario['tipo_big']['passeggeri']."</h3><ul class='list'>";
			foreach($passeggeriBiglietto as $p) {
				$DettaglioOperazione .="<li>x".$p['NumeroPax']." ".$p['TipologiaBiglietto']."</li>";
			}
			$DettaglioOperazione .="</ul></div>";
		}
                            
		$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
				LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
		 		WHERE PrenotazioneId = ".$prenotazione['PrenotazioneId']." and t.OccupaPosto = 0";
		$passeggeriBiglietto = $db->fetch_array($sql);
		if(count($passeggeriBiglietto)) {
			$DettaglioOperazione .= "<div class='section'><h3>".$dizionario['tipo_big']['servizi']."</h3><ul class='list'>";
			foreach($passeggeriBiglietto as $p) {
				$DettaglioOperazione .="<li>x".$p['NumeroPax']." ".$p['TipologiaBiglietto']."</li>";
			}
			$DettaglioOperazione .="</ul></div>";
		}
		echo $DettaglioOperazione;
				?>
				<div class="footer">
					<span><?= Config::$application_company ?></span>
					<span><?= date('d/m/Y H:i') ?></span>
				</div>
			</div>
			<?php } else { ?>
			<div class="center-wrap">
				<div class="card" style="text-align:center">
					<div style="color:var(--danger);font-weight:600">Errore: Prenotazione inesistente</div>
					<div>Biglietto non valido</div>
				</div>
			</div>
			<?php } ?>
			<div class="page-footer">Bertoldi Boats - Biglietteria Online ©2026 - All rights reserved powered by Brain Computing S.p.A</div>
		</div>
	</body>
	</html>
<?php }

?>
