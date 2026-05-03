<?php
$basepath="/srv/www/htdocs/roccobus.it";
include_once($basepath."/main_include.php");

ini_set("zlib.output_compression", "On");
ini_set("zlib.output_compression", 4096);
$voucherpath=$basepath."/protected/booking_web/pdfvoucher_clienti/";
require($basepath."/protected/booking_web/pdfclass/fpdf.php");
require($basepath."/protected/booking_web/pdfclass/fpdi.php");

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
$db=new Database();
$conn=$db->connect();   


// seleziono tutti 

$sql="SELECT
RT_PrenotazioneDettaglio.PrenotazioneNumero,
RT_PrenotazioneDettaglio.PrenotazioneId
FROM
RT_PrenotazioneTransazione
INNER JOIN RT_Prenotazione ON RT_PrenotazioneTransazione.PrenotazioneId = RT_Prenotazione.PrenotazioneId
INNER JOIN RT_PrenotazioneDettaglio ON RT_Prenotazione.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId
WHERE
RT_PrenotazioneTransazione.Notificata = 0 AND
RT_PrenotazioneTransazione.Stato = 1 AND
RT_PrenotazioneTransazione.Cancella = 0 AND
RT_Prenotazione.PrenotazioneStato = 11
GROUP BY
RT_PrenotazioneDettaglio.PrenotazioneNumero,
RT_PrenotazioneDettaglio.PrenotazioneId
ORDER BY
RT_PrenotazioneTransazione.PrenotazioneId ASC,
RT_PrenotazioneDettaglio.PrenotazioneNumero ASC,
RT_PrenotazioneDettaglio.DataPartenza ASC,
RT_PrenotazioneDettaglio.OrarioPartenza ASC";


$ArrObject=$db->fetch_array($sql);

$OldPrenotazioneNumero;
$nuovopdf=false;
$Titolo = "";
	$DataEmissione = "#####";
	$TipoBiglietto = "#####";
	$PrezzoTotale = "#####";
        $PrezzoTotaleF = "#####";
        $PrezzoShuttleF="#####";
        $PrezzoBusF="#####";
	$PrezzoAndata = "#####";
	$PrezzoRitorno = "#####";
	
	$CodicePrenotazione="#####";
	$CustomerName="#####";
	
	$Servizio = "Servizio";
	$Rivendita = "Web";
	
	//SERVIZIO BUS
	$LineaBus = "#####";
	$PrezzoBus = "#####";
	
	//ANDATA
	$BusAndataPartenza = "#####";
	$BusAndataPartenzaData = "#####";
	$BusAndataPartenzaOra = "#####";
	$BusAndataArrivo = "#####";
	$BusAndataArrivoData = "#####";
	$BusAndataArrivoOra = "#####";
	
	//RITORNO
	$BusRitornoPartenza = "#####";
	$BusRitornoPartenzaData = "#####";
	$BusRitornoPartenzaOra = "#####";
	$BusRitornoArrivo = "#####";
	$BusRitornoArrivoData = "#####";
	$BusRitornoArrivoOra = "#####";
	
	//SERVIZIO SHUTTLE
	$LineaShuttle = "#####";
	$PrezzoShuttle = "#####";
	
	//ANDATA
	$ShuttleAndataPartenza = "#####";
	$ShuttleAndataPartenzaData = "#####";
	$ShuttleAndataPartenzaOra = "#####";
	$ShuttleAndataArrivo = "#####";
	$ShuttleAndataArrivoData = "#####";
	$ShuttleAndataArrivoOra = "#####";
	
	//RITORNO
	$ShuttleRitornoPartenza = "#####";
	$ShuttleRitornoPartenzaData = "#####";
	$ShuttleRitornoPartenzaOra = "#####";
	$ShuttleRitornoArrivo = "#####";
	$ShuttleRitornoArrivoData = "#####";
	$ShuttleRitornoArrivoOra = "#####";
        $TipoViaggioId=1;
$x=0;
while($x<sizeof($ArrObject))
{
    $PrezzoTotale=0;
   
    
     $PrenotazioneNumero=$ArrObject[$x]['PrenotazioneNumero'];
     
     $sql="select * from RT_WEB_ViewVoucherDaInviare where 
         PrenotazioneNumero=$PrenotazioneNumero and Tragitto='Andata' and TipoServizio='Bus'
     order by DataPartenza ASC,OrarioPartenza ASC";
   //  die($sql);
     
     $row=$db->query_first($sql);
     $BusAndataPrezzo=0;
     if (!empty($row['PrenotazioneDettaglioId']))
     {
         //ANDATA
	$BusAndataPartenza = $row['ComunePartenza']." - ".$row['FermataPartenza'];
	 $dt=new DT($row['DataPartenza'],"Y-m-d");
        $newdata=$dt->getDate('d/m/Y');
        $BusAndataPartenzaData = $newdata;
	$BusAndataPartenzaOra =  substr($row['OrarioPartenza'],0,5);
	$BusAndataArrivo = $row['ComuneArrivo']." - ".$row['FermataArrivo'];
        $dt=new DT($row['DataArrivo'],"Y-m-d");
        $newdata=$dt->getDate('d/m/Y');
        
	$BusAndataArrivoData = $newdata;
	$BusAndataArrivoOra = substr($row['OrarioArrivo'],0,5);
        $BusAndataPrezzo= $row['Importo'];
        
        
         if ($x==0)
     {
        $Euro = chr(128);
	$Titolo = "Titolo";
	$DataEmissione = "16/05/2013";
	$TipoBiglietto = $row['TipologiaBiglietto'];
	$PrenotazioneId=$row['PrenotazioneId'];
	$PrezzoAndata = $PrezzoTotale;
	$PrezzoRitorno = $PrezzoTotale;
	
	$CodicePrenotazione= $row['CodicePrenotazione'];
	$CustomerName= $row['ClienteNome'];
        $CustomerEmail= $row['ClienteEmail'];
	
	$Servizio = "Servizio";
	$Rivendita = "Web";
	
	//SERVIZIO BUS
	$LineaBus =  $row['PercorsoNome'];
	  $TipoViaggioId=$row['TipoViaggioId'];
        
        
     }   
        
        
     }
    
     $sql="select * from RT_WEB_ViewVoucherDaInviare where 
         PrenotazioneNumero=$PrenotazioneNumero and Tragitto='Ritorno' and TipoServizio='Bus'
     order by DataPartenza ASC,OrarioPartenza ASC";
     $row=$db->query_first($sql);
     $BusRitornoPrezzo=0;
     if (!empty($row['PrenotazioneDettaglioId']))
     {
         //ANDATA
	$BusRitornoPartenza = $row['ComunePartenza']." - ".$row['FermataPartenza'];
	 $dt=new DT($row['DataPartenza'],"Y-m-d");
        $newdata=$dt->getDate('d/m/Y');
        
        $BusRitornoPartenzaData = $newdata;
	$BusRitornoPartenzaOra = substr($row['OrarioPartenza'],0,5);
	$BusRitornoArrivo = $row['ComuneArrivo']." - ".$row['FermataArrivo'];
        
         $dt=new DT($row['DataArrivo'],"Y-m-d");
        $newdata=$dt->getDate('d/m/Y');
        
	$BusRitornoArrivoData = $newdata;
	$BusRitornoArrivoOra = substr($row['OrarioArrivo'],0,5);
         $BusRitornoPrezzo= $row['Importo'];
     }
     
     $sql="select * from RT_WEB_ViewVoucherDaInviare where 
         PrenotazioneNumero=$PrenotazioneNumero and Tragitto='Andata' and TipoServizio='Navetta'
     order by DataPartenza ASC,OrarioPartenza ASC";
     $row=$db->query_first($sql);
     $ShuttleAndataPrezzo=0;
     if (!empty($row['PrenotazioneDettaglioId']))
     {
         //ANDATA
	$ShuttleAndataPartenza = $row['ComunePartenza']." - ".$row['FermataPartenza'];
	
          $dt=new DT($row['DataPartenza'],"Y-m-d");
        $newdata=$dt->getDate('d/m/Y');
        $ShuttleAndataPartenzaData =$newdata;
	$ShuttleAndataPartenzaOra = substr($row['OrarioPartenza'],0,5);
	$ShuttleAndataArrivo = $row['ComuneArrivo']." - ".$row['FermataArrivo'];
	 $dt=new DT($row['DataArrivo'],"Y-m-d");
        $newdata=$dt->getDate('d/m/Y');
        
        $ShuttleAndataArrivoData = $newdata;
	$ShuttleAndataArrivoOra = substr($row['OrarioArrivo'],0,5);
         $ShuttleAndataPrezzo= $row['Importo'];
     }
    
    
    $sql="select * from RT_WEB_ViewVoucherDaInviare where 
         PrenotazioneNumero=$PrenotazioneNumero and Tragitto='Ritorno' and TipoServizio='Navetta'
     order by DataPartenza ASC,OrarioPartenza ASC";
     $row=$db->query_first($sql);
     $ShuttleRitornoPrezzo=0;
     if (!empty($row['PrenotazioneDettaglioId']))
     {
         //ANDATA
	$ShuttleRitornoPartenza = $row['ComunePartenza']." - ".$row['FermataPartenza'];
	 $dt=new DT($row['DataPartenza'],"Y-m-d");
        $newdata=$dt->getDate('d/m/Y');
        
        $ShuttleRitornoPartenzaData = $newdata;
	$ShuttleRitornoPartenzaOra = substr($row['OrarioPartenza'],0,5);
	$ShuttleRitornoArrivo = $row['ComuneArrivo']." - ".$row['FermataArrivo'];
	
        
         $dt=new DT($row['DataArrivo'],"Y-m-d");
        $newdata=$dt->getDate('d/m/Y');
        
        $ShuttleRitornoArrivoData = $newdata;
	$ShuttleRitornoArrivoOra = substr($row['OrarioArrivo'],0,5);
        $ShuttleRitornoPrezzo= $row['Importo'];
     }
      
      
     $PrezzoBus = $BusAndataPrezzo+$BusRitornoPrezzo;
     $PrezzoShuttle=$ShuttleAndataPrezzo+$ShuttleRitornoPrezzo;
     $PrezzoTotale=$BusAndataPrezzo+$BusRitornoPrezzo+$ShuttleAndataPrezzo+$ShuttleRitornoPrezzo;
    $PrezzoAndata=$PrezzoTotale;
    $PrezzoRitorno=$PrezzoTotale;
    
    $PrezzoTotaleF= $Euro." ".number_format($PrezzoTotale,2,",","");
     $PrezzoShuttleF= $Euro." ".number_format($PrezzoShuttle,2,",","");
      $PrezzoBusF= $Euro." ".number_format($PrezzoBus,2,",","");
    
    $x++;

    
    
    $pdf =new FPDI();  
	$pdf->AddPage();  
	// set the sourcefile  
	$parX = 15;
	
	//Base Top
	$baseX = 36;
	$baseY = 25;
	
	//Base Bus
	$base2X = 26;
	$base2Y = 67.5;
	
	//Base Shuttle
	$base3Y = 127;
	
	//Base Footer
	$base4X = 40;
	$base4Y = 231.8;
	
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
	$pdf->Write(0, $PrezzoTotaleF);
	
	//SERVIZIO BUS -----------------------------------------------
	
	//ANDATA
	//LINEA
	$pdf->SetXY($base2X + 37, $base2Y);  
	$pdf->Write(0, $LineaBus);
	//PREZZO
	$pdf->SetXY($base2X + 138, $base2Y);  
	$pdf->Write(0, $PrezzoBusF);
	
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
	$pdf->Write(0, $PrezzoShuttleF);
	
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
	$pdf->Write(0, $PrezzoTotaleF);
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
	
       // $TipoViaggioId=1;
        if ($TipoViaggioId==1)
        {
            $Titolo="#####";
            $CustomerName="#####";
            $DataEmissione="#####";
            $PrezzoTotaleF="#####";
            $BusRitornoPartenzaData="#####";
            $LineaBus="#####";
            $TipoBiglietto="#####";
            $Rivendita="#####";
            
        }
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
	$pdf->Write(0, $PrezzoTotaleF);
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
        
	
	
	
	$file_name=$PrenotazioneId.$PrenotazioneNumero.$CodicePrenotazione.".pdf";
	$pdf->Output($voucherpath.$file_name,'F');
        //$pdf->Output("test.pdf",'D');
    
    die();
   
    $content="ciao";
     $inviatoda='Rocco Autolinee S.r.l. - Booking Online  <'.$email_from_address.'>';
     $emailsubject="Conferma prenotazione numero. ".$CodicePrenotazione;	
    mail_attachment ($inviatoda, $CustomerEmail, $emailsubject, $content,$voucherpath.$file_name);
	
    
    
    
}




function mail_attachment ($from , $to, $subject, $message, $attachment){
	$fileatt = $attachment; // Path to the file
	$fileatt_type = "application/octet-stream"; // File Type
    $start=	strrpos($attachment, '/') == -1 ? strrpos($attachment, '//') : strrpos($attachment, '/')+1;
	$fileatt_name = substr($attachment, $start, strlen($attachment)); // Filename that will be used for the file as the attachment
 
	$email_from = $from; // Who the email is from
	
	$email_subject =  $subject; // The Subject of the email
	$email_txt = $message; // Message that the email has in it
 
	$email_to = $to; // Who the email is to
 
	$headers = "From: ".$email_from;
 
	$file = fopen($fileatt,'rb');
	$data = fread($file,filesize($fileatt));
	fclose($file);
	$msg_txt="";
	$semi_rand = md5(time());
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
	$headers .= "\nMIME-Version: 1.0\n" .
            "Content-Type: multipart/mixed;\n" .
            " boundary=\"{$mime_boundary}\"";
	$email_txt .= $msg_txt;
	$email_message .= "Fromato file MIME .\n\n" .
                "--{$mime_boundary}\n" .
                "Content-Type:text/html; charset=\"iso-8859-1\"\n" .
               "Content-Transfer-Encoding: 7bit\n\n" .
	$email_txt . "\n\n";
	$data = chunk_split(base64_encode($data));
	$email_message .= "--{$mime_boundary}\n" .
                  "Content-Type: {$fileatt_type};\n" .
                  " name=\"{$fileatt_name}\"\n" .
                  //"Content-Disposition: attachment;\n" .
                  //" filename=\"{$fileatt_name}\"\n" .
                  "Content-Transfer-Encoding: base64\n\n" .
                 $data . "\n\n" .
                  "--{$mime_boundary}--\n";
	ini_set(SMTP,'217.72.102.149');
	ini_set(sendmail_from,'info@braincomputing.com'); //the INI lines are to force the From Address to be used !

	//echo("mail:".$email_to."##");	
	$ok = mail($email_to, $email_subject, $email_message, $headers);
 
	if($ok)
	{
	echo "File Sent Successfully.";
	//unlink($attachment); // delete a file after attachment sent.
	}
	else
	{
		echo("Sorry but the email could not be sent. Please go back and try again!");
	}
}




?>
