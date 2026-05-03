<?php
$basepath = $_SERVER ['DOCUMENT_ROOT'];
include_once ($basepath . "/main_include.php");
$config = new Config ();
$run = $config->load ();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors ();
include_once ($classespath_ . "class.Form.php");
include_once ($classespath_ . "class.Ruolo.php");
include_once ($classespath_ . "class.Sede.php");
include_once ($classespath_ . "class.Gestore.php");
include_once ($classespath_ . "class.Nazione.php");
include_once ($classespath_ . "class.Regione.php");
include_once ($classespath_ . "class.Comune.php");
include_once ($classespath_ . "class.Percorso.php");
include_once ($classespath_ . "class.Linea.php");
include_once ($classespath_ . "class.Corsa.php");
include_once ($classespath_ . "class.Tratta.php");
include_once ($classespath_ . "class.TrattaTipo.php");
include_once ($classespath_ . "class.Mezzo.php");
include_once ($classespath_ . "class.TrattaDirezione.php");
include_once ($classespath_ . "class.Prenotazione.php");
include_once ($classespath_ . "class.PrenotazioneDettaglio.php");
include_once ($classespath_ . "class.Fermata.php");
include_once ($classespath_ . "class.QRCode.php");
include_once ($classespath_ . "class.GestioneOttimizzataPasseggeri.php");
include_once ($classespath_ . "Graph/class.LineaGraph.php");


$ModuloId = 1;

function stampa_voucher_di_viaggio() {
    
    global $db, $user, $dizionario;
	error_reporting(E_ALL);
	ini_set ( 'display_errors', 0 );
	
	$basepath = $_SERVER ['DOCUMENT_ROOT'];
	
	
	require ($basepath . "/protected/modules/rt_previaggio/pdfclass/fpdf.php");
	require ($basepath . "/protected/modules/rt_previaggio/pdfclass/fpdi.php");
	
	

	$dt = new DT ();
	
	
	if(!isset($_REQUEST ['Tipo']) || $_REQUEST ['Tipo'] == 'bus') {
    	//paramentri
    	$DataPartenza = $_REQUEST ['DataPartenza'];
    	$CorsaId = $_REQUEST ['CorsaId'];
    	$LineaId = $_REQUEST ['LineaId'];
    	$BusId = $_REQUEST ['BusId'];
    	
    	//creo la classe Prenotzione dettaglio
    	$p = new PrenotazioneDettaglio();
    	$p->conn = $db;
    	
    	//recupero di tutte le informazioni dell'autobus da db
     	$corsa = new LineaGraph($LineaId, $CorsaId, $DataPartenza, $db, true);
     			
     	$vouchersDaStampare = array();
     	
     	//persone che saliranno sul bus non provenienti da altri bus
     	foreach ($corsa->flotta[$BusId]->comuni as $index => $comune) {
    	 	if($corsa->graph->nodes[$comune['comune']]->salite > 0) {
    	 		
    	 		$gruppiPasseggeri = array();
    	 		
    	 		foreach ($corsa->graph->nodes[$comune['comune']]->bigliettiSalite as $dest => $passeg) {
    	 			
    	 			foreach ($passeg as $value) {
    	 				
    	 				if(in_array($value, $corsa->flotta[$BusId]->comuni[$index]['passeggeri'][$dest])) {
    	 					$r = $p->getPrenotazioneDettaglio($value, $LineaId, $CorsaId, $DataPartenza);
    	 					
    	 					//recupero importo e tipo titolo
    	 					$sql = "SELECT COUNT(*) as Tot FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = " . $r['PrenotazioneId'] . "AND Tipo = 'E'";
    	 					$titoloEsiste = $db->query_first($sql);
    
    	 					
    	 					if ($titoloEsiste['Tot'] == "0") {
    	 						$vouchersDaStampare[] =  $r;
    	 					}
    	 				}
    	 			}
    	 		}
    	 	}
     	}
	} else {
	    $vouchersDaStampare = array();
	    $prenotazioneId = $_REQUEST ['PrenotazioneId'];
	    $sql = "SELECT  RT_PrenotazioneDettaglio.*, RT_PrenotazionePercorso.ComuneSalitaId, RT_PrenotazionePercorso.ComuneDiscesaId
					FROM RT_PrenotazionePercorso
					LEFT JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazionePercorso.ComuneSalita = RT_PrenotazioneDettaglio.ComunePartenza)
					WHERE RT_PrenotazioneDettaglio.PrenotazioneId=$prenotazioneId
					GROUP BY PrenotazioneId";
	    
	    $r = $db->fetch_array($sql);
	    $DataPartenza = $r[0] ['DataPartenza'];
	    $CorsaId = $r[0] ['CorsaId'];
	    $LineaId = $r[0] ['LineaId'];
	    $vouchersDaStampare = $r;
	}
 	
 	
 	if (count($vouchersDaStampare) > 0) {
 	    
//  	    ini_set ( "zlib.output_compression", "On" );
//  	    ini_set ( "zlib.output_compression", 4096 );
 	    $Euro = chr(128);
 	    
 	    // set the sourcefile
 	    $parX = 15;
 	    
 	    // Base Top
 	    $baseX = 36;
 	    $baseY = 22;
 	    
 	    // Base Bus
 	    $base2X = 26;
 	    $base2Y = 60;
 	    
 	    // Base Shuttle
 	    $base3Y = 119.5;
 	    
 	    // Base Footer
 	    $base4X = 95;
 	    $base4Y = 236.8;
 	    
 	    $pdf = new FPDI();
 	    $pdf->setSourceFile('pdftemplate/voucherweb.pdf');
 	    $tplIdx = $pdf->importPage(1);
 	    
 	    $pdf->SetTextColor(0, 0, 0);
 	    $pdf->SetFont('Arial', '', 10);
 		
	 	foreach ($vouchersDaStampare as $r) {
	 		$PrenotazioneNumero = $r['PrenotazioneNumero'];
	 		$PrenotazioneId = $r['PrenotazioneId'];
			$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$PrenotazioneId;
			$importoRow = $db->query_first($sql);
	 		$ImportoBus = $importoRow['TotaleDaPagare'];
	 		
	 		$DataTitolo = $DataPartenza;
	 		$DataTitolo = $dt->format ( $DataTitolo, "y-m-d", "d/m/Y" );
	 		
	 		$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = " . $r['PrenotazioneId'];
	 		$prenotazione = $db->query_first($sql);
	 		
	 		$CodiceBiglietto = $prenotazione['CodicePrenotazione'] . "/" . $r['PrenotazioneNumero'];
	 		if($prenotazione['TipoTour'] == 0) {
                $TipologiaBiglietto = $dizionario['generale']['tour_gruppo'];
			} else {
                $TipologiaBiglietto = $dizionario['generale']['tour_privato'];
			}
	 		$ClienteNome=utf8_decode(ucwords(strtolower($prenotazione['ClienteNome'])));
	 		$Percorso = $r['PercorsoNome'];
	 		$sql = "SELECT s.CodiceSede, g.RagioneSociale FROM Sede s LEFT JOIN Gestore g ON (s.GestoreId=g.GestoreId) WHERE SedeId=".$r['SedeIns'];
	 		$sede = $db->query_first($sql);
	 		$Agenzia = $sede['RagioneSociale'];
	 		$Rivendita = $sede['CodiceSede'];
			
			//INIZIO sosta
			//calcolo la sosta prenotata
			$sosta = 0;
			if($prenotazione['TipoTour'] == 1){
				$sql = "select * from RT_PrenotazioneBiglietto where TipologiaBigliettoId = 23 AND PrenotazioneId = $PrenotazioneId";
				$sostaRow = $db->query_first($sql);
				if(isset($sostaRow['NumeroPax'])) {
					$sosta = $sostaRow['NumeroPax'];
				}
			}
			//sommo la sosta all'orario di arrivo
			$tempOraArrivo = new DateTime($r['OrarioArrivo']);
			$tempOraArrivo->modify("+".$sosta." hours");
			$tempOraArrivoS = $tempOraArrivo->format("H:i:s"); 
			//FINE sosta
	 		
	 		$DataCorsa = $dt->format ($DataPartenza, "y-m-d", "d/m/Y" );
	 		$ComuneSalitaBus = utf8_decode($r['ComunePartenza']);
	 		$DataFermataSalitaBus = $dt->format ( $r['DataPartenza'], "y-m-d", "d/m/Y" );
	 		$OrarioFermataSalitaBus = $dt->format ( $r['OrarioPartenza'], "H:i:s", "H:i" );
	 		$FermataSalitaBus = utf8_decode($r['FermataPartenza']);
	 		$SalitaBus = " il " . $DataFermataSalitaBus . " ore " . $OrarioFermataSalitaBus . " (" . $FermataSalitaBus . ")";
	 		$ComuneDiscesaBus = utf8_decode($r['ComuneArrivo']);
	 		$DataFermataDiscesaBus = $dt->format ( $r['DataArrivo'], "y-m-d", "d/m/Y" );
	 		$OrarioFermataDiscesaBus = $dt->format ( $tempOraArrivoS, "H:i:s", "H:i" );
	 		$FermataDiscesaBus = utf8_decode($r['FermataArrivo']);
	 		$DiscesaBus = " il " . $DataFermataDiscesaBus . " ore " . $OrarioFermataDiscesaBus . " (" . $FermataDiscesaBus . ")";
	 		
	 		$TipoViaggioId = 1;
	 		
	 		//$sql = "select * from RT_PrenotazioneDettaglioCompleto where TipoServizio='Bus' and Tragitto='Ritorno' and OdcIdRef=$user->OdcId and PrenotazioneNumero=$PrenotazioneNumero";
	 		$sql = "SELECT * FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero=".$r['PrenotazioneNumero']." AND Tragitto='Ritorno'";
	 		$ArrObjectR = $db->query_first($sql);
	 		
	 		$DataPartenzaR = "###";
	 		$DataCorsaR = "###";
	 		
	 		$ComuneSalitaBusR = "###";
	 		$DataFermataSalitaBusR = "###";
	 		$OrarioFermataSalitaBusR = "###";
	 		$FermataSalitaBusR = "###";
	 		$SalitaBusR = "###";
	 		$ComuneDiscesaBusR = "###";
	 		$DataFermataDiscesaBusR = "###";
	 		$OrarioFermataDiscesaBusR = "###";
	 		$FermataDiscesaBusR = "###";
	 		$DiscesaBusR = "###";
	 		
	 		if ($ArrObjectR['PrenotazioneNumero'] > 0) {
	 			$DataPartenzaR = $ArrObjectR['DataPartenza'];
	 			$DataCorsaR = $dt->format ($DataPartenzaR, "y-m-d", "d/m/Y");
	 				
	 			$ComuneSalitaBusR = utf8_decode($ArrObjectR['ComunePartenza']);
	 			$DataFermataSalitaBusR = $dt->format($ArrObjectR['DataPartenza'], "y-m-d", "d/m/Y");
	 			$OrarioFermataSalitaBusR = $dt->format($ArrObjectR ['OrarioPartenza'], "H:i:s", "H:i");
	 			$FermataSalitaBusR = utf8_decode($ArrObjectR['FermataPartenza']);
	 			$SalitaBusR = " il " . $DataFermataSalitaBusR . " ore " . $OrarioFermataSalitaBusR . " (" . $FermataSalitaBusR . ")";
	 			$ComuneDiscesaBusR = utf8_decode($ArrObjectR['ComuneArrivo']);
	 			$DataFermataDiscesaBusR = $dt->format($ArrObjectR['DataArrivo'], "y-m-d", "d/m/Y");
	 			$OrarioFermataDiscesaBusR = $dt->format($ArrObjectR ['OrarioArrivo'], "H:i:s", "H:i");
	 			$FermataDiscesaBusR = utf8_decode($ArrObjectR['FermataArrivo']);
	 			$DiscesaBusR = " il " . $DataFermataDiscesaBusR . " ore " . $OrarioFermataDiscesaBusR . " (" . $FermataDiscesaBusR . ")";
	 			$TipoViaggioId = 2;
	 		}
			$ImportoBusF = number_format ($ImportoBus, 2, ",", "" );
	 		
	 		$TotaleBiglietto = $ImportoBus;
	 		
	 		$TotaleBigliettoF = number_format ( $TotaleBiglietto, 2, ",", "" );
	 		
	 		// Aggiungo la pagina
	 		$pdf->AddPage();
	 		// Setto il template
	 		$pdf->useTemplate($tplIdx, 5, 5, 200);
	 		
	 		// pdf
	 		
	 		//TOP --------------------------------------------------------
			//TITOLO
			$pdf->SetXY($baseX + 37, $baseY + 51.5);
			$pdf->Write(0, $CodiceBiglietto);
			//DATA EMISSIONE
			$pdf->SetXY($baseX + 37, $baseY + 59);
			$pdf->Write(0, $DataTitolo);
			//NOMINATIVO
			$pdf->SetXY($baseX + 37, $baseY + 44);
			$pdf->Write(0, $ClienteNome);
			//TIPO BIGLIETTO
			$pdf->SetXY($baseX + 37, $baseY + 66.5);
			$pdf->Write(0, $TipologiaBiglietto);
			//PREZZO TOTALE
			$pdf->SetXY($baseX + 37, $baseY + 74);
			$pdf->Write(0, $TotaleBigliettoF.$Euro);
	 		
	 		//SERVIZIO BUS -----------------------------------------------
                        
                        //ANDATA
                        //LINEA
                        
                        //PARTENZA
                        $pdf->SetXY($base2X + 47.5, $base2Y + 61.5);
                        $pdf->Write(0, $ComuneSalitaBus.' '.$FermataSalitaBus);
                        //DATA PARTENZA + ORA PARTENZA
                        $pdf->SetXY($base2X + 47.5, $base2Y + 69);
                        $pdf->Write(0, $DataFermataSalitaBus . " - " . $OrarioFermataSalitaBus);
                        
                        //ARRIVO
                        $pdf->SetXY($base2X  + 47.5, $base2Y + 76.5);
                        $pdf->Write(0, $ComuneDiscesaBus.' '.$FermataDiscesaBus);
                        //DATA ARRIVO + ORA ARRIVO
                        $pdf->SetXY($base2X + 47.5, $base2Y + 84);
                        $pdf->Write(0, $DataFermataDiscesaBus . " - " . $OrarioFermataDiscesaBus);
						
						//OFFSET RIEPILOGO
						$offsetRiepilogo = 100;
						
						//RIEPILOGO
						$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
								LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
								WHERE PrenotazioneId = $PrenotazioneId and t.OccupaPosto = 1";
						$passeggeriBiglietto = $db->fetch_array($sql);
						
						if(count($passeggeriBiglietto)) {
							foreach($passeggeriBiglietto as $p) {
								$riepilogo .= "- x".$p['NumeroPax']." ".$p['TipologiaBiglietto'];
							}
							$offsetRiepilogo += 6;
							$pdf->SetXY($base2X + 47.5, $base2Y + $offsetRiepilogo);
							$pdf->Write(0, $riepilogo);
						}
						
						$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
								LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
								WHERE PrenotazioneId = $PrenotazioneId and t.OccupaPosto = 0";
						$serviziBiglietto = $db->fetch_array($sql);
						if(count($serviziBiglietto)) {
							foreach($serviziBiglietto as $p) {
								$offsetRiepilogo += 6;
								$pdf->SetXY($base2X + 47.5, $base2Y + $offsetRiepilogo);
								$pdf->Write(0, "- x".$p['NumeroPax']." ".$p['TipologiaBiglietto']);
							}
						}
						//FINE RIEPILOGO
                        

                        if (strpos($CodiceBiglietto, 'E-') !== false && strpos($CodiceBiglietto, 'E-') == 0) {
                            //è il titolo di un pagamento extra associato al passeggero principale
                            $pdf->SetXY($base2X - 8, $base2Y + 110);
                            $pdf->Write(0, "Il titolo rappresenta un pagamento extra della prenotazione");
                        } else {
                            //è il biglietto di un passeggero
                            //QRCode
                            $sql = "SELECT CodiceQrcode FROM RT_PrenotazioneNumero WHERE PrenotazioneNumeroId=$PrenotazioneNumero";
                            $CodiceQrcode = $db->query_first($sql);
                            $qrcode = new QRCode();
                            $qrcode->setData("http://".$_SERVER['HTTP_HOST']."/protected/modules/rt_qrcode/qrcode.php?code=".$CodiceQrcode['CodiceQrcode']);
                            $qrcode->setImageSize(100, 100);
                            $qrcode->setMargin("0");
                            $qrcode->setOutputEncoding(QRCode::$_ENCODING_UTF8);
                            $qrcode->setOutPutFormat(QRCode::$_OUTPUT_FORMAT_PNG);
                            $pdf->Image($qrcode->getUrlQuery(), $base2X + 65, $base2Y + 190, 30, 0, 'PNG');
                            
                            //Avvertenze ritorno Open
                            if($ritornoOpen == 1){
                                $pdf->SetXY($base2X - 8, $base2Y + 110);
                                $pdf->Write(0, "Il biglietto e' di tipo RitornoOpen. il biglietto emesso e' modificabile alle condizioni" );
                                $pdf->SetXY($base2X - 8, $base2Y + 115);
                                $pdf->Write(0, "riportate nel regolamento di viaggio, comunque entro il termine massimo di " );
                                $pdf->SetXY($base2X - 8, $base2Y + 120);
                                $pdf->Write(0, "6 mesi dalla data di partenza della corsa di andata. Data di scadenza: ".$ScadenzaOpen );
                            }
                            
                        }

						//se il biglietto è di tipo A/R aggiungo pagina RITORNO
						if($TipoViaggioId == 2) {
							//aggiungo una seconda pagina per il viaggio di ritorno
							$pdf->setSourceFile('pdftemplate/voucherweb.pdf');
							$tplIdx = $pdf->importPage(1);
							$pdf->AddPage();
							$pdf->useTemplate($tplIdx, 5, 5, 200);
							
							//TOP RITORNO --------------------------------------------------------
							//TITOLO
							$pdf->SetXY($baseX + 37, $baseY + 51.5);
							$pdf->Write(0, $CodiceBiglietto);
							//DATA EMISSIONE
							$pdf->SetXY($baseX + 37, $baseY + 59);
							$pdf->Write(0, $DataTitolo);
							//NOMINATIVO
							$pdf->SetXY($baseX + 37, $baseY + 44);
							$pdf->Write(0, $ClienteNome);
							//TIPO BIGLIETTO
							$pdf->SetXY($baseX + 37, $baseY + 66.5);
							$pdf->Write(0, $TipologiaBiglietto);
							//PREZZO TOTALE
							$pdf->SetXY($baseX + 37, $baseY + 74);
							$pdf->Write(0, $TotaleBigliettoF.$Euro);
							
							//ANDATA
							//LINEA
							
							//PARTENZA
							$pdf->SetXY($base2X + 47.5, $base2Y + 61.5);
							$pdf->Write(0, $ComuneSalitaBusR.' '.$FermataSalitaBusR);
							//DATA PARTENZA + ORA PARTENZA
							$pdf->SetXY($base2X + 47.5, $base2Y + 69);
							$pdf->Write(0, $DataFermataSalitaBusR . " - " . $OrarioFermataSalitaBusR);
							
							//ARRIVO
							$pdf->SetXY($base2X  + 47.5, $base2Y + 76.5);
							$pdf->Write(0, $ComuneDiscesaBusR.' '.$FermataDiscesaBusR);
							//DATA ARRIVO + ORA ARRIVO
							$pdf->SetXY($base2X + 47.5, $base2Y + 84);
							$pdf->Write(0, $DataFermataDiscesaBusR . " - " . $OrarioFermataDiscesaBusR);
							
							$offsetRiepilogo = 100;
							
							//RIEPILOGO
							$riepilogo = "";
							if(count($passeggeriBiglietto)) {
								foreach($passeggeriBiglietto as $p) {
									$riepilogo .= "- x".$p['NumeroPax']." ".$p['TipologiaBiglietto'];
								}
								$offsetRiepilogo += 6;
								$pdf->SetXY($base2X + 47.5, $base2Y + $offsetRiepilogo);
								$pdf->Write(0, $riepilogo);
							}

							if(count($serviziBiglietto)) {
								foreach($serviziBiglietto as $p) {
									$offsetRiepilogo += 6;
									$pdf->SetXY($base2X + 47.5, $base2Y + $offsetRiepilogo);
									$pdf->Write(0, "- x".$p['NumeroPax']." ".$p['TipologiaBiglietto']);
								}
							}
							//FINE RIEPILOGO
							
							if (strpos($CodiceBiglietto, 'E-') !== false && strpos($CodiceBiglietto, 'E-') == 0) {
                            //è il titolo di un pagamento extra associato al passeggero principale
								$pdf->SetXY($base2X - 8, $base2Y + 110);
								$pdf->Write(0, "Il titolo rappresenta un pagamento extra della prenotazione");
							} else {
								//è il biglietto di un passeggero
								//QRCode
								$pdf->Image($qrcode->getUrlQuery(), $base2X + 65, $base2Y + 190, 30, 0, 'PNG');
								
								
							}
							
						}
	 			
		}
	
		$pdf->Output("biglietti.pdf", 'D');
		
 	} else {
 		print ("Non ci sono biglietti da preparare") ;
 	}
}

if (is_object ( $user )) {
	$db = new Database ();
	$db->connect ();
	$user->conn = $db;
	$permessi = $user->get_permessi_modulo ( $ModuloId );
	if(isset($_REQUEST['do'])) {
		$do=$_REQUEST['do'];
	} else {
		$do=null;
	}
	
	if (!isset ( $do ))
		$do = '';
	
	switch ($do) {
		
		default :
			stampa_voucher_di_viaggio();
			break;
	}
} // se l'utente non Ã¨ loggato
else {
	header ( "Location: /logout.php" );
}
?>