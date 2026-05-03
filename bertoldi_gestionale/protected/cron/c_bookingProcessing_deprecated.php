<?php
/* Configurazione log */
$file = '/var/log/csreisen_c_bookingProcessing.log';
$time_start = microtime(true);

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] START\n', FILE_APPEND | LOCK_EX);
/* LOG */

include_once("main_include.php");
$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";
include_once($classespath_.'class.Gestore.php');
include_once($classespath_.'class.QRCode.php');
include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');
require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdf.php");
require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdi.php");

global $user, $db, $voucherpath, $pdftemplate, $emailtemplate, $content_html, $pdfCovid;

// inizializzata la connessione
$db = new Database();
$db->connect();

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] include e connessione db eseguita\n', FILE_APPEND | LOCK_EX);
/* LOG */

$sql="update RT_Prenotazione set TotalePagato=TotaleDaPagare, TotaleResiduo=0 where PrenotazioneStato=3 and TotalePagato=0";
$db->query($sql);

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] aggiornamento totali eseguito\n', FILE_APPEND | LOCK_EX);
/* LOG */

// setto il percorso dove salvare i pdf dei biglietti
$voucherpath = $basepath."/protected/pdfvoucher/";

// setto il percorso del template del biglietto
$pdftemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/voucherweb.pdf";

// setto il percorso del info covid
$pdfCovid = $basepath."/protected/modules/rt_previaggio/pdftemplate/informazioni_covid_25_01_2022.pdf";

// setto il percorso del template da usare come contenuto del email di default
$emailtemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/voucher.html";
$fh = fopen($emailtemplate,"r");
$content_html = fread($fh,filesize($emailtemplate));
fclose($fh);


// recupero i titoli di viaggio da inviare via email
$sql = "SELECT * FROM RT_WEB_ViewVoucherDaInviare";
$titoli = $db->fetch_array($sql);

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] recupero i titoli di viaggio da inviare via email\n', FILE_APPEND | LOCK_EX);
/* LOG */

$prenotazioneId_precedente = null;
$pdf_precedente = null;
$email_precendente = null;

$send = false;

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] inizio for each titoli\n', FILE_APPEND | LOCK_EX);
/* LOG */

foreach ($titoli as $titolo) {

	// setto il percorso del template da usare come contenuto del email in base alla lingua della prenotazione
	$vaucherFileName = $titolo['Lingua'] == 'it' ? 'voucher.html' : 'voucher_'.$titolo['Lingua'].'.html';
	$emailtemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/$vaucherFileName";
    $fh = fopen($emailtemplate,"r");
    $content_html = fread($fh,filesize($emailtemplate));
    fclose($fh);

	$prenotazioneId = $titolo['PrenotazioneId'];
	$titoloId = $titolo['PrenotazioneTitoloId'];
	$corsaId = $titolo['CorsaId'];
    $codiceItinerario = $titolo['CodicePrenotazione'];
	$dataPartenza = $titolo['DataInizioItinerario'];
	$tipo_titolo = "E";
	
	if ($prenotazioneId != $prenotazioneId_precedente) {
		if ($send) {
		    $sqlGestore = "SELECT GestoreIdRef FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId_precedente";
		    $temp = $db->query_first($sqlGestore);
		    if($temp['GestoreIdRef'] == 1){
		        $onebus = true;
		    } else {
		        $onebus = false;
		    }
		    invioEmail($pdf_precedente, $prenotazioneId_precedente, $email_precendente, $onebus);
		}
		
		$pdf_precedente = new FPDI();
		$pdf_precedente->AddPage();
		
		$prenotazioneId_precedente = $prenotazioneId;
		$email_precendente = $titolo['ClienteEmail'];
		$user = getUser($prenotazioneId);
		
		$send = true;
	} else {
		$pdf_precedente->AddPage();
	}
	
	stampa_titoli_di_viaggio($pdf_precedente, $prenotazioneId, $titoloId, $corsaId, $dataPartenza, $tipo_titolo);
}

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] fine for each titoli\n', FILE_APPEND | LOCK_EX);
/* LOG */

// invia l'ultima email quando sono finiti i titoli
if (isset($prenotazioneId_precedente)) {
    $sqlGestore = "SELECT GestoreIdRef FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId_precedente";
    $temp = $db->query_first($sqlGestore);
    if($temp['GestoreIdRef'] == 1){
        $onebus = true;
    } else {
        $onebus = false;
    }
    invioEmail($pdf_precedente, $prenotazioneId_precedente, $email_precendente, $codiceItinerario, $onebus);
}

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] invia l\'ultima email quando sono finiti i titoli\n', FILE_APPEND | LOCK_EX);
/* LOG */

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] END - Tempo esecuzione: ' . microtime(true) - $time_start . '\n', FILE_APPEND | LOCK_EX);
/* LOG */


// ELENCO FUNZIONI
function invioEmail($pdf, $prenotazioneId, $email_to,$CodiceItinerario, $onebus = true) {
	global $voucherpath, $emailtemplate,$content_html;
	
	$email_from = "info@braincomputing.com";
	$oggetto = "Conferma prenotazione / Booking Confirm n. ".$CodiceItinerario;
	
	$file_name = "prenotazione_".$CodiceItinerario.".pdf";
	$pdf->Output($voucherpath.$file_name, 'F');
	
	$email_to = trim($email_to);
    if (!empty($email_to)) {
        mail_attachment($email_from, $email_to, $oggetto, $content_html, $voucherpath.$file_name, $onebus);
	}
	
	setNotificaOK($prenotazioneId);
}

// stampa titolo di viaggio
function stampa_titoli_di_viaggio($pdf, $prenotazioneId, $titoloId, $corsaId, $dataPartenza, $tipo_titolo)
{
    global $user, $db, $pdftemplate, $pdfCovid;
	
	$dt = new DT();
	
	// inizializzo il pdf
	$Euro = chr(128);
	
	// set the sourcefile
	$parX = 15;
	
	//Base Top
	$baseX = 36;
	$baseY = 22;
	
	//Base Bus
	$base2X = 26;
	$base2Y = 60;
	
	//Base Shuttle
	$base3Y = 119.5;
	
	//Base Footer
	$base4X = 40;
	$base4Y = 236.8;
	
	$pdf->setSourceFile($pdftemplate);
	
	$tplIdx = $pdf->importPage(1);
	$pdf->useTemplate($tplIdx, 5, 5, 200);
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Arial','',10);
	
	// recupero le informazioni per generare il biglietto
	$PrenotazioneId = $prenotazioneId;
	$PrenotazioneTitoloId = $titoloId;
	$CorsaId = $corsaId;
	$DataPartenza = $dataPartenza;
	
	// serve per definire il tipo di stampa
	if (!isset($tipo_titolo)) {
		$tipo_titolo = "E";
	}
	
	// serve per definire il gestore
	$gestore = new Gestore();
	$gestore->conn = $db;
	$gestorefigli = $gestore->getGestoreFigli($user->GestoreId);
	$InGestoreFigli=implode(",", $gestorefigli);
	
	// prepara la query per recuperare le informazioni sul biglietto
	$sq_titolo = "1=1";
	if (isset($PrenotazioneTitoloId))
	{
		$sq_titolo = "PrenotazioneTitoloId = " . $PrenotazioneTitoloId;
	}
	
	$sq_tipo_titolo = "1=1";
	if (isset($tipo_titolo))
	{
		$sq_tipo_titolo = "TipoTitolo = '$tipo_titolo'";
	}
	
	$sql="select * from RT_PrenotazioneDettaglioCompleto where Stato=1 and Cancella=0 and PrenotazioneTitoloId is not null and PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='Andata' and OdcIdRef=$user->OdcId and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza'  and GestoreIdRef IN ($InGestoreFigli) order by ClienteNome asc";
	$ArrObjectP = $db->fetch_array($sql);
	$numeropasseggeri = sizeof($ArrObjectP);
	
	$np = 0;
	   while ($np<$numeropasseggeri)
             {
                   $ImportoNavetta=0;
                   $TotaleBiglietto=0;
                   $ImportoBus=0;
                  $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                  $PrenotazioneNumero=$ArrObjectP[$np]['PrenotazioneNumero'];
                  $PrenotazioneTitoloId=$ArrObjectP[$np]['PrenotazioneTitoloId'];
                   $ImportoBus=$ArrObjectP[$np]['Importo'];
                  
                   
                      $DataBiglietto=$ArrObjectP[$np]['DataEticket'];
                  
                      $CodiceBiglietto=$ArrObjectP[$np]['CodicePrenotazione']."/".$ArrObjectP[$np]['PrenotazioneNumero'];
                      $DataTitolo=$DataPartenza;
                      $TipologiaBiglietto=$ArrObjectP[$np]['TipologiaBiglietto'];
                      $ClienteNome=utf8_decode(ucwords(strtolower($ArrObjectP[$np]['ClienteNome'])));
                      $Percorso=$ArrObjectP[$np]['PercorsoNome'];
                      $Agenzia=utf8_decode($ArrObjectP[$np]['RagioneSociale']);
                      $Rivendita=$ArrObjectP[$np]['CodiceSede'];
                     
                  
                  if ($PrenotazioneTitoloId>0)
                  {
                      $CodiceBiglietto=$ArrObjectP[$np]['Codice']."/".$ArrObjectP[$np]['Anno'];
                      $DataTitolo=$ArrObjectP[$np]['DataEticket'];
                      
                      
                  }
                  $DataCorsa=$dt->format($DataPartenza, "y-m-d", "d/m/Y");
                  $DataTitolo=$dt->format($DataTitolo, "y-m-d", "d/m/Y");
                  
                  $ComuneSalitaBus=utf8_decode($ArrObjectP[$np]['ComunePartenza']);
                  $DataFermataSalitaBus=$dt->format($ArrObjectP[$np]['DataPartenza'], "y-m-d", "d/m/Y");
                  $OrarioFermataSalitaBus=$dt->format($ArrObjectP[$np]['OrarioPartenza'], "H:i:s", "H:i");
                  $FermataSalitaBus=utf8_decode($ArrObjectP[$np]['FermataPartenza']);
                  $SalitaBus=" il ".$DataFermataSalitaBus." ore ".$OrarioFermataSalitaBus." (".$FermataSalitaBus.")";
                  $ComuneDiscesaBus=utf8_decode($ArrObjectP[$np]['ComuneArrivo']);
                  $DataFermataDiscesaBus=$dt->format($ArrObjectP[$np]['DataArrivo'], "y-m-d", "d/m/Y");
                  $OrarioFermataDiscesaBus=$dt->format($ArrObjectP[$np]['OrarioArrivo'], "H:i:s", "H:i");
                  $FermataDiscesaBus=utf8_decode($ArrObjectP[$np]['FermataArrivo']);
                  $DiscesaBus=" il ".$DataFermataDiscesaBus." ore ".$OrarioFermataDiscesaBus." (".$FermataDiscesaBus.")";
                  
                  $TipoViaggioId=1;
                  
                 
                  
                  $sql="select * from RT_PrenotazioneDettaglioCompleto where TipoServizio='Bus' and Tragitto='Ritorno' and OdcIdRef=$user->OdcId and PrenotazioneNumero=$PrenotazioneNumero";
                  $ArrObjectR = $db->query_first($sql);
                  
                  $DataPartenzaR="###";   
                  $DataCorsaR="###"; 
                  $ComuneSalitaBusR="###";  
                  $DataFermataSalitaBusR="###";  
                  $OrarioFermataSalitaBusR="###";  
                  $FermataSalitaBusR="###";  
                  $SalitaBusR="###";  
                  $ComuneDiscesaBusR="###";  
                  $DataFermataDiscesaBusR="###";  
                  $OrarioFermataDiscesaBusR="###";  
                  $FermataDiscesaBusR="###";  
                  $DiscesaBusR="###";  
                 
                  
                  if ($ArrObjectR['PrenotazioneNumero']>0)
                  {
                   //$ImportoBus=$ImportoBus+$ArrObjectR['Importo'];
                   $DataPartenzaR=$ArrObjectR['DataPartenza'];   
                   $DataCorsaR=$dt->format($DataPartenzaR, "y-m-d", "d/m/Y");
                   
                  $ComuneSalitaBusR=utf8_decode($ArrObjectR['ComunePartenza']);
                  $DataFermataSalitaBusR=$dt->format($ArrObjectR['DataPartenza'], "y-m-d", "d/m/Y");
                  $OrarioFermataSalitaBusR=$dt->format($ArrObjectR['OrarioPartenza'], "H:i:s", "H:i");
                  $FermataSalitaBusR=utf8_decode($ArrObjectR['FermataPartenza']);
                  $SalitaBusR=" il ".$DataFermataSalitaBusR." ore ".$OrarioFermataSalitaBusR." (".$FermataSalitaBusR.")";
                  $ComuneDiscesaBusR=utf8_decode($ArrObjectR['ComuneArrivo']);
                  $DataFermataDiscesaBusR=$dt->format($ArrObjectR['DataArrivo'], "y-m-d", "d/m/Y");
                  $OrarioFermataDiscesaBusR=$dt->format($ArrObjectR['OrarioArrivo'], "H:i:s", "H:i");
                  $FermataDiscesaBusR=utf8_decode($ArrObjectR['FermataArrivo']);
                  $DiscesaBusR=" il ".$DataFermataDiscesaBusR." ore ".$OrarioFermataDiscesaBusR." (".$FermataDiscesaBusR.")";
                  $TipoViaggioId=2;
                      
                      
                  }
                  $ImportoBusF=  number_format($ImportoBus,2,",","");
                  
                  $sql="select * from RT_PrenotazioneDettaglioCompleto where TipoServizio='Navetta' and Tragitto='Andata' and OdcIdRef=$user->OdcId and PrenotazioneNumero=$PrenotazioneNumero";
                  $ArrObjectR = $db->query_first($sql);
                  
                  
                  
                   $ImportoNavetta=0;
                   $DataPartenzaN="###";
                  $ComuneSalitaNavetta="###";
                  $DataFermataSalitaNavetta="###";
                  $OrarioFermataSalitaNavetta="###";
                  $FermataSalitaNavetta="###";
                  $SalitaNavetta="###";
                  $ComuneDiscesaNavetta="###";
                  $DataFermataDiscesaNavetta="###";
                  $OrarioFermataDiscesaNavetta="###";
                  $FermataDiscesaNavetta="###";
                  $DiscesaNavetta="###";
               
                  $ComuneSalitaNavettaR="###";
                  $DataFermataSalitaNavettaR="###";
                  $OrarioFermataSalitaNavettaR="###";
                  $FermataSalitaNavettaR="###";
                  $SalitaNavettaR="###";
                  $ComuneDiscesaNavettaR="###";
                  $DataFermataDiscesaNavettaR="###";
                  $OrarioFermataDiscesaNavettaR="###";
                  $FermataDiscesaNavettaR="###";
                  $DiscesaNavettaR="###";
                 
                  
                  
                  
                  
                  
                  if ($ArrObjectR['PrenotazioneNumero']>0)
                  {
                   $ImportoNavetta=$ArrObjectR['Importo'];
                   $DataPartenzaN=$ArrObjectR['DataPartenza'];   
                  $ComuneSalitaNavetta=utf8_decode($ArrObjectR['ComunePartenza']);
                  $DataFermataSalitaNavetta=$dt->format($ArrObjectR['DataPartenza'], "y-m-d", "d/m/Y");
                  $OrarioFermataSalitaNavetta=$dt->format($ArrObjectR['OrarioPartenza'], "H:i:s", "H:i");
                  $FermataSalitaNavetta=utf8_decode($ArrObjectR['FermataPartenza']);
                  $SalitaNavetta=" il ".$DataFermataSalitaNavetta." ore ".$OrarioFermataSalitaNavetta." (".$FermataSalitaNavetta.")";
                  $ComuneDiscesaNavetta=utf8_decode($ArrObjectR['ComuneArrivo']);
                  $DataFermataDiscesaNavetta=$dt->format($ArrObjectR['DataArrivo'], "y-m-d", "d/m/Y");
                  $OrarioFermataDiscesaNavetta=$dt->format($ArrObjectR['OrarioArrivo'], "H:i:s", "H:i");
                  $FermataDiscesaNavetta=utf8_decode($ArrObjectR['FermataArrivo']);
                  $DiscesaNavetta=" il ".$DataFermataDiscesaNavetta." ore ".$OrarioFermataDiscesaNavetta." (".$FermataDiscesaNavetta.")";
                  
                  
                    if ($TipoViaggioId==2)
                        {
                  $sql="select * from RT_PrenotazioneDettaglioCompleto where TipoServizio='Navetta' and Tragitto='Ritorno' and OdcIdRef=$user->OdcId and PrenotazioneNumero=$PrenotazioneNumero";
                  $ArrObjectR = $db->query_first($sql);
                  
                  if ($ArrObjectR['PrenotazioneNumero']>0)
                  {
                   $ImportoNavetta=$ImportoNavetta+$ArrObjectR['Importo'];
                   $DataPartenzaN=$ArrObjectR['DataPartenza'];   
                  $ComuneSalitaNavettaR=utf8_decode($ArrObjectR['ComunePartenza']);
                  $DataFermataSalitaNavettaR=$dt->format($ArrObjectR['DataPartenza'], "y-m-d", "d/m/Y");
                  $OrarioFermataSalitaNavettaR=$dt->format($ArrObjectR['OrarioPartenza'], "H:i:s", "H:i");
                  $FermataSalitaNavettaR=utf8_decode($ArrObjectR['FermataPartenza']);
                  $SalitaNavettaR=" il ".$DataFermataSalitaNavettaR." ore ".$OrarioFermataSalitaNavettaR." (".$FermataSalitaNavettaR.")";
                  $ComuneDiscesaNavettaR=utf8_decode($ArrObjectR['ComuneArrivo']);
                  $DataFermataDiscesaNavettaR=$dt->format($ArrObjectR['DataArrivo'], "y-m-d", "d/m/Y");
                  $OrarioFermataDiscesaNavettaR=$dt->format($ArrObjectR['OrarioArrivo'], "H:i:s", "H:i");
                  $FermataDiscesaNavettaR=utf8_decode($ArrObjectR['FermataArrivo']);
                  $DiscesaNavettaR=" il ".$DataFermataDiscesaNavettaR." ore ".$OrarioFermataDiscesaNavettaR." (".$FermataDiscesaNavettaR.")";
                  
                        
                        }
                    }  
                  }    
                    
                    $TotNavF=  number_format($ImportoNavetta,2,",","");
                    
                    $TotaleBiglietto=$ImportoNavetta+$ImportoBus;
                    
                   
                        //$TotaleBiglietto=$ImportoTitolo;
                    
                    
                    if ($tipo_titolo=='R')
                        $TotaleBiglietto=$TotaleBiglietto*(-1);
                    
                    $TotaleBigliettoF=number_format($TotaleBiglietto,2,",","");
                  	
                  	
					//pdf
					$pdf->SetXY($baseX - 20, $baseY - 12);  
					$pdf->Write(0, "Emesso da ".$Agenzia." (".$Rivendita.")");
				
					//TOP --------------------------------------------------------
					//TITOLO
					$pdf->SetXY($baseX + 70.5, $baseY);  
					$pdf->Write(0, $CodiceBiglietto);
					//DATA EMISSIONE
					$pdf->SetXY($baseX + 128.5, $baseY);  
					$pdf->Write(0, $DataTitolo);
					//NOMINATIVO
					$pdf->SetXY($baseX, $baseY + 14.5);  
					$pdf->Write(0, $ClienteNome);
					//TIPO BIGLIETTO
					$pdf->SetXY($baseX + 70.5, $baseY + 14.5);  
					$pdf->Write(0, $TipologiaBiglietto);
					//PREZZO TOTALE
					$pdf->SetXY($baseX + 128.5, $baseY + 14.5);  
					$pdf->Write(0, $TotaleBigliettoF.$Euro);
					
					if ($tipo_titolo=='E') { 					
						//SERVIZIO BUS -----------------------------------------------
			
						//ANDATA
						//LINEA
						//$pdf->SetXY($base2X + 37, $base2Y);  
						//$pdf->Write(0, $Percorso);
						//PREZZO
						$pdf->SetXY($base2X + 138, $base2Y);  
						$pdf->Write(0, $ImportoBusF.$Euro);
						
						//PARTENZA
						$pdf->SetXY($base2X, $base2Y + 13);  
						$pdf->Write(0, $ComuneSalitaBus.' '.$FermataSalitaBus);
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base2Y + 13);  
						$pdf->Write(0, $DataFermataSalitaBus);
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base2Y + 13);  
						$pdf->Write(0, $OrarioFermataSalitaBus);
						
						//ARRIVO
						$pdf->SetXY($base2X, $base2Y + 23);  
						$pdf->Write(0, $ComuneDiscesaBus.' '.$FermataDiscesaBus);
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base2Y + 23);  
						$pdf->Write(0, $DataFermataDiscesaBus);
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base2Y + 23);  
						$pdf->Write(0, $OrarioFermataDiscesaBus);
											
						//RITORNO
						//PARTENZA
						$pdf->SetXY($base2X, $base2Y + 34.5);  
						$pdf->Write(0, $ComuneSalitaBusR.' '.$FermataSalitaBusR);
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base2Y + 34.5);  
						$pdf->Write(0, $DataFermataSalitaBusR);
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base2Y + 34.5);  
						$pdf->Write(0, $OrarioFermataSalitaBusR);
											
						//ARRIVO
						$pdf->SetXY($base2X, $base2Y + 44.5);  
						$pdf->Write(0, $ComuneDiscesaBusR.' '.$FermataDiscesaBusR);
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base2Y + 44.5);  
						$pdf->Write(0, $DataFermataDiscesaBusR);
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base2Y + 44.5);  
						$pdf->Write(0, $OrarioFermataDiscesaBusR);
						
						//SERVIZIO SHUTTLE -----------------------------------------------
			
						//ANDATA
						//LINEA
//						$pdf->SetXY($base2X + 37, $base3Y);  
//						$pdf->Write(0, $ComuneSalitaNavetta." - ".$ComuneDiscesaNavetta);
						//PREZZO
//						$pdf->SetXY($base2X + 138, $base3Y);  
//						$pdf->Write(0, $TotNavF.$Euro);
						
						//PARTENZA
//						$pdf->SetXY($base2X, $base3Y + 13);  
//						$pdf->Write(0, $ComuneSalitaNavetta." ".$FermataSalitaNavetta);
						//DATA PARTENZA
//						$pdf->SetXY($base2X + 108.5, $base3Y + 13);  
//						$pdf->Write(0, $DataFermataSalitaNavetta);
						//ORA PARTENZA
//						$pdf->SetXY($base2X + 138, $base3Y + 13);  
//						$pdf->Write(0, $OrarioFermataSalitaNavetta);
						
						//ARRIVO
//						$pdf->SetXY($base2X, $base3Y + 23);  
//						$pdf->Write(0, $ComuneDiscesaNavetta." ".$FermataDiscesaNavetta);
						//DATA ARRIVO
//						$pdf->SetXY($base2X + 108.5, $base3Y + 23);  
//						$pdf->Write(0, $DataFermataDiscesaNavetta);
						//ORA ARRIVO
//						$pdf->SetXY($base2X + 138, $base3Y + 23);  
//						$pdf->Write(0, $OrarioFermataDiscesaNavetta);
						
						//RITORNO
						//PARTENZA
//						$pdf->SetXY($base2X, $base3Y + 34.5);  
//						$pdf->Write(0, $ComuneSalitaNavettaR." ".$FermataSalitaNavettaR);
						//DATA PARTENZA
//						$pdf->SetXY($base2X + 108.5, $base3Y + 34.5);  
//						$pdf->Write(0, $DataFermataSalitaNavettaR);
						//ORA PARTENZA
//						$pdf->SetXY($base2X + 138, $base3Y + 34.5);  
//						$pdf->Write(0, $OrarioFermataSalitaNavettaR);
						
						//ARRIVO
//						$pdf->SetXY($base2X, $base3Y + 44.5);  
//						$pdf->Write(0, $ComuneDiscesaNavettaR." ".$FermataDiscesaNavettaR);
						//DATA ARRIVO
//						$pdf->SetXY($base2X + 108.5, $base3Y + 44.5);  
//						$pdf->Write(0, $DataFermataDiscesaNavettaR);
						//ORA ARRIVO
//						$pdf->SetXY($base2X + 138, $base3Y + 44.5);  
//						$pdf->Write(0, $OrarioFermataDiscesaNavettaR);
						
						//QRCode
						
                                                /*$sql = "SELECT CodiceQrcode FROM RT_PrenotazioneNumero WHERE PrenotazioneNumeroId=$PrenotazioneNumero";
						$CodiceQrcode = $db->query_first($sql);
						$qrcode = new QRCode();
						$qrcode->setData("http://".$_SERVER['HTTP_HOST']."/protected/modules/rt_qrcode/qrcode.php?code=".$CodiceQrcode['CodiceQrcode']);
						$qrcode->setImageSize(180, 180);
						$qrcode->setMargin("L|0");
						$qrcode->setOutputEncoding(QRCode::$_ENCODING_UTF8);
						$qrcode->setOutPutFormat(QRCode::$_OUTPUT_FORMAT_PNG);
						$pdf->Image($qrcode->getUrlQuery(), $base2X + 120, $base2Y + 110, 50, 0, 'PNG');
						*/
						//FOOTER
						//ANDATA
						//TITOLO
						$pdf->SetXY($base4X, $base4Y);  
						$pdf->Write(0, $CodiceBiglietto);
						//DEL
						$pdf->SetXY($base4X, $base4Y + 5.5);  
						$pdf->Write(0, $DataTitolo);
						//NOME
						$pdf->SetXY($base4X, $base4Y + 11);  
						$pdf->Write(0, $ClienteNome);
						//PREZZO
						if($TipoViaggioId==2){
							$pdf->SetXY($base4X, $base4Y + 16.4);  
							$pdf->Write(0, ($TotaleBigliettoF/2).$Euro);
						}
						else {
							$pdf->SetXY($base4X, $base4Y + 16.4);  
							$pdf->Write(0, $TotaleBigliettoF.$Euro);
						}
						//CORSA DEL
						$pdf->SetXY($base4X, $base4Y + 21.8);  
						$pdf->Write(0, $DataCorsa);
						//SERVIZIO
						$pdf->SetXY($base4X, $base4Y + 27.1);  
						$pdf->Write(0, $Percorso);
						//TIPO BIGLIETTO
						$pdf->SetXY($base4X, $base4Y + 32.3);  
						$pdf->Write(0, $TipologiaBiglietto);
						//RIVENDITA
						$pdf->SetXY($base4X, $base4Y + 38);  
						$pdf->Write(0, $Agenzia);
						
						//Se esiste il ritorno
						if($TipoViaggioId==2){
							//RITORNO
							//TITOLO
							$pdf->SetXY($base4X + 95, $base4Y);  
							$pdf->Write(0, $CodiceBiglietto);
							//DEL
							$pdf->SetXY($base4X + 95, $base4Y + 5.5);  
							$pdf->Write(0, $DataTitolo);
							//NOME
							$pdf->SetXY($base4X + 95, $base4Y + 11);  
							$pdf->Write(0, $ClienteNome);
							//PREZZO
							$pdf->SetXY($base4X + 95, $base4Y + 16.4);  
							$pdf->Write(0, ($TotaleBigliettoF/2).$Euro);
							//CORSA DEL
							$pdf->SetXY($base4X + 95, $base4Y + 21.8);  
							$pdf->Write(0, $DataCorsaR);
							//SERVIZIO
							$pdf->SetXY($base4X + 95, $base4Y + 27.1);  
							$pdf->Write(0, $Percorso);
							//TIPO BIGLIETTO
							$pdf->SetXY($base4X + 95, $base4Y + 32.3);  
							$pdf->Write(0, $TipologiaBiglietto);
							//RIVENDITA
							$pdf->SetXY($base4X + 95, $base4Y + 38);  
							$pdf->Write(0, $Agenzia);
						}
						else {
							//RITORNO
							//TITOLO
							$pdf->SetXY($base4X + 95, $base4Y);  
							$pdf->Write(0, "###");
							//DEL
							$pdf->SetXY($base4X + 95, $base4Y + 5.5);  
							$pdf->Write(0, "###");
							//NOME
							$pdf->SetXY($base4X + 95, $base4Y + 11);  
							$pdf->Write(0, "###");
							//PREZZO
							$pdf->SetXY($base4X + 95, $base4Y + 16.4);  
							$pdf->Write(0, "###");
							//CORSA DEL
							$pdf->SetXY($base4X + 95, $base4Y + 21.8);  
							$pdf->Write(0, "###");
							//SERVIZIO
							$pdf->SetXY($base4X + 95, $base4Y + 27.1);  
							$pdf->Write(0, "###");
							//TIPO BIGLIETTO
							$pdf->SetXY($base4X + 95, $base4Y + 32.3);  
							$pdf->Write(0, "###");
							//RIVENDITA
							$pdf->SetXY($base4X + 95, $base4Y + 38);  
							$pdf->Write(0, "###");
						}
					}//fine tipo EMESSO
					
					// rimborso
					else { 					
						//SERVIZIO BUS -----------------------------------------------
			
						//ANDATA
						//LINEA
						//$pdf->SetXY($base2X + 37, $base2Y);  
						//$pdf->Write(0, "###");
						//PREZZO
						$pdf->SetXY($base2X + 138, $base2Y);  
						$pdf->Write(0, "RIMBORSO");
						
						//PARTENZA
						$pdf->SetXY($base2X, $base2Y + 13);  
						$pdf->Write(0, "###");
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base2Y + 13);  
						$pdf->Write(0, "###");
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base2Y + 13);  
						$pdf->Write(0, "###");
						
						//ARRIVO
						$pdf->SetXY($base2X, $base2Y + 23);  
						$pdf->Write(0, "###");
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base2Y + 23);  
						$pdf->Write(0, "###");
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base2Y + 23);  
						$pdf->Write(0, "###");
											
						//RITORNO
						//PARTENZA
						$pdf->SetXY($base2X, $base2Y + 34.5);  
						$pdf->Write(0, "###");
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base2Y + 34.5);  
						$pdf->Write(0, "###");
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base2Y + 34.5);  
						$pdf->Write(0, "###");
											
						//ARRIVO
						$pdf->SetXY($base2X, $base2Y + 44.5);  
						$pdf->Write(0, "###");
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base2Y + 44.5);  
						$pdf->Write(0, "###");
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base2Y + 44.5);  
						$pdf->Write(0, "###");
						
						//SERVIZIO SHUTTLE -----------------------------------------------
			
						//ANDATA
						//LINEA
						//$pdf->SetXY($base2X + 37, $base3Y);  
						//$pdf->Write(0, "###");
						//PREZZO
						//$pdf->SetXY($base2X + 138, $base3Y);  
						//$pdf->Write(0, 'RIMBORSO');
						
						//PARTENZA
						//$pdf->SetXY($base2X, $base3Y + 13);  
						//$pdf->Write(0, "###");
						//DATA PARTENZA
						//$pdf->SetXY($base2X + 108.5, $base3Y + 13);  
						//$pdf->Write(0, "###");
						//ORA PARTENZA
						//$pdf->SetXY($base2X + 138, $base3Y + 13);  
						//$pdf->Write(0, "###");
						
						//ARRIVO
						//$pdf->SetXY($base2X, $base3Y + 23);  
						//$pdf->Write(0, "###");
						//DATA ARRIVO
						//$pdf->SetXY($base2X + 108.5, $base3Y + 23);  
						//$pdf->Write(0, "###");
						//ORA ARRIVO
						//$pdf->SetXY($base2X + 138, $base3Y + 23);  
						//$pdf->Write(0, "###");
						
						//RITORNO
						//PARTENZA
						//$pdf->SetXY($base2X, $base3Y + 34.5);  
						//$pdf->Write(0, "###");
						//DATA PARTENZA
						//$pdf->SetXY($base2X + 108.5, $base3Y + 34.5);  
						//$pdf->Write(0, "###");
						//ORA PARTENZA
						//$pdf->SetXY($base2X + 138, $base3Y + 34.5);  
						//$pdf->Write(0, "###");
						
						//ARRIVO
						//$pdf->SetXY($base2X, $base3Y + 44.5);  
						//$pdf->Write(0, "###");
						//DATA ARRIVO
						//$pdf->SetXY($base2X + 108.5, $base3Y + 44.5);  
						//$pdf->Write(0, "###");
						//ORA ARRIVO
						//$pdf->SetXY($base2X + 138, $base3Y + 44.5);  
						//$pdf->Write(0, "###");
						
						//FOOTER
						//ANDATA
						if ($Tragitto == 'Andata') {
							//TITOLO
							$pdf->SetXY($base4X, $base4Y);
							$pdf->Write(0, $CodiceBiglietto);
							//DEL
							$pdf->SetXY($base4X, $base4Y + 5.5);
							$pdf->Write(0, $DataTitolo);
							//NOME
							$pdf->SetXY($base4X, $base4Y + 11);
							$pdf->Write(0, $ClienteNome);
							//PREZZO
							if($TipoViaggioId==2){
								$pdf->SetXY($base4X, $base4Y + 16.4);
								$pdf->Write(0, ($TotaleBigliettoF/2).$Euro);
							}
							else {
								$pdf->SetXY($base4X, $base4Y + 16.4);
								$pdf->Write(0, $TotaleBigliettoF.$Euro);
							}
							//CORSA DEL
							$pdf->SetXY($base4X, $base4Y + 21.8);
							$pdf->Write(0, "RIMBORSO");
							//SERVIZIO
							$pdf->SetXY($base4X, $base4Y + 27.1);
							$pdf->Write(0, "RIMBORSO");
							//TIPO BIGLIETTO
							$pdf->SetXY($base4X, $base4Y + 32.3);
							$pdf->Write(0, "RIMBORSO");
							//RIVENDITA
							$pdf->SetXY($base4X, $base4Y + 38);
							$pdf->Write(0, $Agenzia);
						} else {
							//TITOLO
							$pdf->SetXY($base4X, $base4Y);
							$pdf->Write(0, "###");
							//DEL
							$pdf->SetXY($base4X, $base4Y + 5.5);
							$pdf->Write(0, "###");
							//NOME
							$pdf->SetXY($base4X, $base4Y + 11);
							$pdf->Write(0, "###");
							//PREZZO
							$pdf->SetXY($base4X, $base4Y + 16.4);
							$pdf->Write(0, "###");
							//CORSA DEL
							$pdf->SetXY($base4X, $base4Y + 21.8);
							$pdf->Write(0, "###");
							//SERVIZIO
							$pdf->SetXY($base4X, $base4Y + 27.1);
							$pdf->Write(0, "###");
							//TIPO BIGLIETTO
							$pdf->SetXY($base4X, $base4Y + 32.3);
							$pdf->Write(0, "###");
							//RIVENDITA
							$pdf->SetXY($base4X, $base4Y + 38);
							$pdf->Write(0, "###");
						}
						
						
						//Se esiste il ritorno
						if($TipoViaggioId==2){
							//RITORNO
							//TITOLO
							$pdf->SetXY($base4X + 95, $base4Y);  
							$pdf->Write(0, $CodiceBiglietto);
							//DEL
							$pdf->SetXY($base4X + 95, $base4Y + 5.5);  
							$pdf->Write(0, $DataTitolo);
							//NOME
							$pdf->SetXY($base4X + 95, $base4Y + 11);  
							$pdf->Write(0, $ClienteNome);
							//PREZZO
							if ($Tragitto == 'Andata') {
								$pdf->SetXY($base4X + 95, $base4Y + 16.4);
								$pdf->Write(0, ($TotaleBigliettoF/2).$Euro);
							} else {
								$pdf->SetXY($base4X + 95, $base4Y + 16.4);
								$pdf->Write(0, ($TotaleBigliettoF).$Euro);
							}
							//CORSA DEL
							$pdf->SetXY($base4X + 95, $base4Y + 21.8);  
							$pdf->Write(0, "RIMBORSO");
							//SERVIZIO
							$pdf->SetXY($base4X + 95, $base4Y + 27.1);  
							$pdf->Write(0, "RIMBORSO");
							//TIPO BIGLIETTO
							$pdf->SetXY($base4X + 95, $base4Y + 32.3);  
							$pdf->Write(0, "RIMBORSO");
							//RIVENDITA
							$pdf->SetXY($base4X + 95, $base4Y + 38);  
							$pdf->Write(0, $Agenzia);
						}
						else {
							//RITORNO
							//TITOLO
							$pdf->SetXY($base4X + 95, $base4Y);  
							$pdf->Write(0, "###");
							//DEL
							$pdf->SetXY($base4X + 95, $base4Y + 5.5);  
							$pdf->Write(0, "###");
							//NOME
							$pdf->SetXY($base4X + 95, $base4Y + 11);  
							$pdf->Write(0, "###");
							//PREZZO
							$pdf->SetXY($base4X + 95, $base4Y + 16.4);  
							$pdf->Write(0, "###");
							//CORSA DEL
							$pdf->SetXY($base4X + 95, $base4Y + 21.8);  
							$pdf->Write(0, "###");
							//SERVIZIO
							$pdf->SetXY($base4X + 95, $base4Y + 27.1);  
							$pdf->Write(0, "###");
							//TIPO BIGLIETTO
							$pdf->SetXY($base4X + 95, $base4Y + 32.3);  
							$pdf->Write(0, "###");
							//RIVENDITA
							$pdf->SetXY($base4X + 95, $base4Y + 38);  
							$pdf->Write(0, "###");
						}
					}//fine tipo RIMBORSO
					
					
					if($np != $numeropasseggeri-1){
						//Aggiungo la pagina
						$pdf->AddPage();
						//Setto il template
						$pdf->useTemplate($tplIdx, 5, 5, 200);
					}
					
					if (Config::$covidMode == true){
					    $docAdd = $pdfCovid;
					    $pagecount = $pdf->setSourceFile($docAdd);
					    for($i=0; $i<$pagecount; $i++){
					        $pdf->AddPage();
					        $tplidx1 = $pdf->importPage($i+1, '/MediaBox');
					        $pdf->useTemplate($tplidx1, 10, 10, 200);
					    }
					}
					
        			$np++;
             }
}

/* prendo i dati dell'utente */
function getUser($prenotazioneId) {
	global $db;

	$sql = "SELECT OpeIns,SedeIns,GestoreIdRef,OdcIdRef FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";

	$row = $db->query_first($sql);
	$operatoreId = $row['OpeIns'];

	$user = new Operatore();
	$user->conn = $db;

	$user->OperatoreId=$row['OpeIns'];
	$user->GestoreId=$row['GestoreIdRef'];
	$user->SedeId=$row['SedeIns'];
	$user->OdcId=$row['OdcIdRef'];

	return $user;
}

/* invia l'email */
function mail_attachment($from, $to, $subject, $message, $attachment, $onebus = true) {
	global $db, $user;
	
	//i dati per l'invio sono presenti nella tabella ODC
	$sql = "SELECT * FROM Odc WHERE OdcId = $user->OdcId";
	$odc = $db->query_first($sql);
	
    $mail= new PHPMailer(); // create the mail
    $mail->Subject = $subject;
	$mail->AddAddress($to);
	$mail->MsgHTML($message);
	$mail->IsSMTP();
	//$mail->SMTPDebug  = 2;
	$mail->SMTPSecure = 'ssl';// SMTP account password
	$mail->SMTPAuth=true;
	$mail->IsHTML(true);
	
	// inserisce l'allegato
	$mail->AddAttachment($attachment);
        
    // setto il from
	if($onebus) {
	    $from = $odc['EmailSmtp'];
	} else {
	    $from = Config::$emailBigliettiAgenzia;
	}
    $fromName = $odc['NomeEmailSmtp'];
	$mail->SetFrom($from, $fromName);  
	
	$mail->Host = $odc['ServerSmtp'];				// Server SMTP
	$mail->Port = $odc['PortaSmtp'];				// Server SMTP Port
	$mail->Username = $odc['UserSmtp'];           	// SMTP account username
	$mail->Password = $odc['PwdSmtp'];      

       

        // SMTP account password
	$mail->Send();  
}

/* salva la transazione come notificata */
function setNotificaOK($prenotazioneId) {
	global $db, $user;
	
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	$dataT['Notificata'] = 1;
	$notifica = $storico->operazioni_update($dataT, $user);
	$result = $db->update("RT_PrenotazioneTransazione", $notifica, "PrenotazioneId=".$prenotazioneId);
}
?>
