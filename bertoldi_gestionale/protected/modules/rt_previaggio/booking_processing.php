<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$basepath=$_SERVER['DOCUMENT_ROOT'];
ini_set("zlib.output_compression", "On");
ini_set("zlib.output_compression", 4096);
//$voucherpath=$basepath."/protected/modules/pdfvoucher_clienti/";
require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdf.php");
require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdi.php");

	/* Variabili */
	$Euro = chr(128);
	
	//TOP
	$Titolo = "Titolo";
	$DataEmissione = "16/05/2013";
	$TipoBiglietto = "Andata/Ritorno";
	$PrezzoTotale = "1000".$Euro;
	$PrezzoAndata = "500".$Euro;
	$PrezzoRitorno = "500".$Euro;
	
	$CodicePrenotazione="1000000";
	$CustomerName="Antonio Esposito";
	
	$Servizio = "Servizio";
	$Rivendita = "Web";
	
	//SERVIZIO BUS
	$LineaBus = "Lagonegro - Milano";
	$PrezzoBus = "700".$Euro;
	
	//ANDATA
	$BusAndataPartenza = "Lagonegro - Parcheggio Multipiano";
	$BusAndataPartenzaData = "20/05/2013";
	$BusAndataPartenzaOra = "8:20";
	$BusAndataArrivo = "Milano - Rogoredo";
	$BusAndataArrivoData = "20/05/2013";
	$BusAndataArrivoOra = "18:20";
	
	//RITORNO
	$BusRitornoPartenza = "Milano - Rogoredo";
	$BusRitornoPartenzaData = "25/05/2013";
	$BusRitornoPartenzaOra = "8:30";
	$BusRitornoArrivo = "Lagonegro - Parcheggio Multipiano";
	$BusRitornoArrivoData = "25/05/2013";
	$BusRitornoArrivoOra = "18:30";
	
	//SERVIZIO SHUTTLE
	$LineaShuttle = "Maratea - Lagonegro";
	$PrezzoShuttle = "300".$Euro;
	
	//ANDATA
	$ShuttleAndataPartenza = "Maratea Stazione";
	$ShuttleAndataPartenzaData = "20/05/2013";
	$ShuttleAndataPartenzaOra = "10:00";
	$ShuttleAndataArrivo = "Lagonegro - Parcheggio Multipiano";
	$ShuttleAndataArrivoData = "20/05/2013";
	$ShuttleAndataArrivoOra = "10:30";
	
	//RITORNO
	$ShuttleRitornoPartenza = "Lagonegro - Parcheggio Multipiano";
	$ShuttleRitornoPartenzaData = "25/05/2013";
	$ShuttleRitornoPartenzaOra = "10:00";
	$ShuttleRitornoArrivo = "Maratea Stazione";
	$ShuttleRitornoArrivoData = "25/05/2013";
	$ShuttleRitornoArrivoOra = "10:20";
	
	/* FINE VARIABILI */
	
	
        $pdf =new FPDI();  
	$pdf->AddPage();  
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
	
	$pdf->setSourceFile('pdftemplate/voucherweb.pdf'); 
	
	$tplIdx = $pdf->importPage(1);  
	$pdf->useTemplate($tplIdx, 5, 5, 200);  
	$pdf->SetTextColor(0,0,0);
	//$pdf->SetFont('Arial','B',12);  
	//$pdf->SetXY(141-$parX, 51);  
	//$pdf->Write(0, $CodicePrenotazione);
	$pdf->SetFont('Arial','',10);  
	//$pdf->SetXY(141-$parX, 58);  
	//$pdf->Write(0, $CustomerName);
	
	//TOP --------------------------------------------------------
	//TITOLO
	$pdf->SetXY($baseX + 70.5, $baseY);  
	$pdf->Write(0, $Titolo);
	//DATA EMISSIONE
	$pdf->SetXY($baseX + 128.5, $baseY);  
	$pdf->Write(0, $DataEmissione);
	//NOMINATIVO
	$pdf->SetXY($baseX, $baseY + 14.5);  
	$pdf->Write(0, $CustomerName);
	//TIPO BIGLIETTO
	$pdf->SetXY($baseX + 70.5, $baseY + 14.5);  
	$pdf->Write(0, $TipoBiglietto);
	//PREZZO TOTALE
	$pdf->SetXY($baseX + 128.5, $baseY + 14.5);  
	$pdf->Write(0, $PrezzoTotale);
	
	//SERVIZIO BUS -----------------------------------------------
	
	//ANDATA
	//LINEA
	$pdf->SetXY($base2X + 37, $base2Y);  
	$pdf->Write(0, $LineaBus);
	//PREZZO
	$pdf->SetXY($base2X + 138, $base2Y);  
	$pdf->Write(0, $PrezzoBus);
	
	//PARTENZA
	$pdf->SetXY($base2X, $base2Y + 13);  
	$pdf->Write(0, $BusAndataPartenza);
	//DATA PARTENZA
	$pdf->SetXY($base2X + 108.5, $base2Y + 13);  
	$pdf->Write(0, $BusAndataPartenzaData);
	//ORA PARTENZA
	$pdf->SetXY($base2X + 138, $base2Y + 13);  
	$pdf->Write(0, $BusAndataPartenzaOra);
	
	//ARRIVO
	$pdf->SetXY($base2X, $base2Y + 23);  
	$pdf->Write(0, $BusAndataArrivo);
	//DATA ARRIVO
	$pdf->SetXY($base2X + 108.5, $base2Y + 23);  
	$pdf->Write(0, $BusAndataArrivoData);
	//ORA ARRIVO
	$pdf->SetXY($base2X + 138, $base2Y + 23);  
	$pdf->Write(0, $BusAndataArrivoOra);
	
	//RITORNO
	//PARTENZA
	$pdf->SetXY($base2X, $base2Y + 34.5);  
	$pdf->Write(0, $BusRitornoPartenza);
	//DATA PARTENZA
	$pdf->SetXY($base2X + 108.5, $base2Y + 34.5);  
	$pdf->Write(0, $BusRitornoPartenzaData);
	//ORA PARTENZA
	$pdf->SetXY($base2X + 138, $base2Y + 34.5);  
	$pdf->Write(0, $BusRitornoPartenzaOra);
	
	//ARRIVO
	$pdf->SetXY($base2X, $base2Y + 44.5);  
	$pdf->Write(0, $BusRitornoArrivo);
	//DATA ARRIVO
	$pdf->SetXY($base2X + 108.5, $base2Y + 44.5);  
	$pdf->Write(0, $BusRitornoArrivoData);
	//ORA ARRIVO
	$pdf->SetXY($base2X + 138, $base2Y + 44.5);  
	$pdf->Write(0, $BusRitornoArrivoOra);
	
	//SERVIZIO SHUTTLE -----------------------------------------------
	
	//ANDATA
	//LINEA
	$pdf->SetXY($base2X + 37, $base3Y);  
	$pdf->Write(0, $LineaShuttle);
	//PREZZO
	$pdf->SetXY($base2X + 138, $base3Y);  
	$pdf->Write(0, $PrezzoShuttle);
	
	//PARTENZA
	$pdf->SetXY($base2X, $base3Y + 13);  
	$pdf->Write(0, $ShuttleAndataPartenza);
	//DATA PARTENZA
	$pdf->SetXY($base2X + 108.5, $base3Y + 13);  
	$pdf->Write(0, $ShuttleAndataPartenzaData);
	//ORA PARTENZA
	$pdf->SetXY($base2X + 138, $base3Y + 13);  
	$pdf->Write(0, $ShuttleAndataPartenzaOra);
	
	//ARRIVO
	$pdf->SetXY($base2X, $base3Y + 23);  
	$pdf->Write(0, $ShuttleAndataArrivo);
	//DATA ARRIVO
	$pdf->SetXY($base2X + 108.5, $base3Y + 23);  
	$pdf->Write(0, $ShuttleAndataArrivoData);
	//ORA ARRIVO
	$pdf->SetXY($base2X + 138, $base3Y + 23);  
	$pdf->Write(0, $ShuttleAndataArrivoOra);
	
	//RITORNO
	//PARTENZA
	$pdf->SetXY($base2X, $base3Y + 34.5);  
	$pdf->Write(0, $ShuttleRitornoPartenza);
	//DATA PARTENZA
	$pdf->SetXY($base2X + 108.5, $base3Y + 34.5);  
	$pdf->Write(0, $ShuttleRitornoPartenzaData);
	//ORA PARTENZA
	$pdf->SetXY($base2X + 138, $base3Y + 34.5);  
	$pdf->Write(0, $ShuttleRitornoPartenzaOra);
	
	//ARRIVO
	$pdf->SetXY($base2X, $base3Y + 44.5);  
	$pdf->Write(0, $ShuttleRitornoArrivo);
	//DATA ARRIVO
	$pdf->SetXY($base2X + 108.5, $base3Y + 44.5);  
	$pdf->Write(0, $ShuttleRitornoArrivoData);
	//ORA ARRIVO
	$pdf->SetXY($base2X + 138, $base3Y + 44.5);  
	$pdf->Write(0, $ShuttleRitornoArrivoOra);
	
	//FOOTER
	//ANDATA
	//TITOLO
	$pdf->SetXY($base4X, $base4Y);  
	$pdf->Write(0, $Titolo);
	//DEL
	$pdf->SetXY($base4X, $base4Y + 5.5);  
	$pdf->Write(0, $DataEmissione);
	//NOME
	$pdf->SetXY($base4X, $base4Y + 11);  
	$pdf->Write(0, $CustomerName);
	//PREZZO
	$pdf->SetXY($base4X, $base4Y + 16.4);  
	$pdf->Write(0, $PrezzoAndata);
	//CORSA DEL
	$pdf->SetXY($base4X, $base4Y + 21.8);  
	$pdf->Write(0, $BusAndataPartenzaData);
	//SERVIZIO
	$pdf->SetXY($base4X, $base4Y + 27.1);  
	$pdf->Write(0, $LineaBus);
	//TIPO BIGLIETTO
	$pdf->SetXY($base4X, $base4Y + 32.3);  
	$pdf->Write(0, $TipoBiglietto);
	//RIVENDITA
	$pdf->SetXY($base4X, $base4Y + 38);  
	$pdf->Write(0, $Rivendita);
	
	//RITORNO
	//TITOLO
	$pdf->SetXY($base4X + 95, $base4Y);  
	$pdf->Write(0, $Titolo);
	//DEL
	$pdf->SetXY($base4X + 95, $base4Y + 5.5);  
	$pdf->Write(0, $DataEmissione);
	//NOME
	$pdf->SetXY($base4X + 95, $base4Y + 11);  
	$pdf->Write(0, $CustomerName);
	//PREZZO
	$pdf->SetXY($base4X + 95, $base4Y + 16.4);  
	$pdf->Write(0, $PrezzoAndata);
	//CORSA DEL
	$pdf->SetXY($base4X + 95, $base4Y + 21.8);  
	$pdf->Write(0, $BusRitornoPartenzaData);
	//SERVIZIO
	$pdf->SetXY($base4X + 95, $base4Y + 27.1);  
	$pdf->Write(0, $LineaBus);
	//TIPO BIGLIETTO
	$pdf->SetXY($base4X + 95, $base4Y + 32.3);  
	$pdf->Write(0, $TipoBiglietto);
	//RIVENDITA
	$pdf->SetXY($base4X + 95, $base4Y + 38);  
	$pdf->Write(0, $Rivendita);
	
	
	$file_name=$CodicePrenotazione.".pdf";
	//$pdf->Output($voucherpath.$file_name,'F');
        $pdf->Output("test.pdf",'D');

?>
