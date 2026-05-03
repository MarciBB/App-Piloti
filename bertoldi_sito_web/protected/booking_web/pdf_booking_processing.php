<?php
                   

$current_path=$_SERVER['SCRIPT_FILENAME'];
$todelete=$_SERVER['ORIG_PATH_INFO'];
$todelete=str_replace("/","\\",$todelete);
$root=str_replace($todelete,"",$current_path);
$idpagina_get="2f07b7f0-4f9c-5f6d-d426-4f1697bdb8d7";
$idmenu_get="9f7d5640-b302-7aef-3fce-4f1698c08428";
$idlingua_get="bbf76dde-fc97-06a3-f46b-4c0f6dcf9129";
include_once($root."\engine\basic_engine_include.php"); 

ini_set('display_errors', 0);

include_once($root."\engine_booking\protected\engine_booking.conf.php"); 
include_once($root."\protected\classes\class.RomeOpenTourService24.php"); 
include_once($root."\protected\classes\class.RomeOpenTourService48.php"); 
include_once($root."\protected\classes\class.RomeOpenTourService72.php"); 
include_once($root."\protected\classes\class.RomeBusAndBoat.php"); 
include_once($root."\protected\classes\class.RomeBusAndBoat72.php"); 
include_once($root."\protected\classes\class.BasketBooking.php");
include_once($root."\protected\classes\class.Smtp.php");
include_once($root."\protected\classes\class.Email.php");
require($root."\engine_booking\class.php\fpdf.php");
require($root."\engine_booking\class.php\fpdi.php");

error_reporting(E_ALL);
ini_set('display_errors', 0);

ini_set(sendmail_from,'info@braincomputing.com');  // the INI lines are to force the From Address to be used !
ini_set(SMTP,'217.72.102.149');

$conn=conn_mssql();
$xxx=1;
while ($xxx==1)
{
$xxx++;
$result = odbc_exec($conn, "select * from rex_stato_conferme_da_inviare where Tabella='prenotazioni_open_tour'" ); 
//$result = odbc_exec($conn, "select * from rex_stato_conferme where IdPrenotazione=26786" ); 

while ($row=odbc_fetch_array($result))
{
    $codice_prenotazione=$row['IdPrenotazione'];
    $conferma_id=$row['IdConferma'];
    $r=odbc_exec($conn, "update prenotazioni_open_tour  set stato='S' where codice_prenotazione='$codice_prenotazione'");
	
	
	$result2 = odbc_exec($conn, "select * from prenotazioni_open_tour  where codice_prenotazione='$codice_prenotazione'");
    while ($row2=odbc_fetch_array($result2))
    {
     $headers="";
     $CodicePrenotazione=$codice_prenotazione;
     $SiteName=SITO_WEB;
     $Title="Rome Open Tour";
     $CustomerName=$row2['nome_cliente'];
     $CustomerEmail=$row2['email'];     
     $tipo_biglietto=trim($row2['tipo_biglietto']);    
    
     $adulti=0;
	 if ($row2['adulti']>0)
         $adulti+=$row2['adulti'];
     if ($row2['senior']>0)
         $adulti+=$row2['senior'];
	 
     $NTicketDetails="";
     
     if ($adulti>0)
         $NTicketDetails=$adulti." adults ";
     if ($row2['junior']>0)
         $NTicketDetails.="+ ".$row2['junior']." juniors ";
     
     
     $TourType="";
     
     if ($tipo_biglietto=="1")
         $TourType="Rome 24 hours";
     elseif ($tipo_biglietto=="2")
         $TourType="Rome 48 hours";
     elseif ($tipo_biglietto=="3")
         $TourType="Rome Bus'n boat";
	 elseif ($tipo_biglietto=="9")
         $TourType="Rome 72 hours";
     elseif ($tipo_biglietto=="10")
         $TourType="Rome Bus'n boat 72 hours";		 
     
     
     
     $TourDate=$row2['data_partenza'];
     if ($TourDate=='29991231')
         $TourDate="Open day";
     else
     {
                   $anno=substr($row2['data_partenza'], 0, 4);
                   $mese=substr($row2['data_partenza'], 4, 2);
                   $giorno=substr($row2['data_partenza'], 6, 2);
                    $TourDate=$giorno."/".$mese."/".$anno;
                  
         
     }
     
    $tmpl_percorso = $root."/engine_booking/voucher_template/voucher.html";	
    $fh = fopen($tmpl_percorso,"r");
    $content = fread($fh,filesize($tmpl_percorso));
    fclose($fh);			
    /*$content=str_replace("###-TITLE-###",$Title,$content);
    $content=str_replace("###-SITE-###",$SiteName,$content);
    $content=str_replace("###-RESERVATION_CODE-###",$CodicePrenotazione,$content);
    $content=str_replace("###-CUSTOMER_NAME-###",$CustomerName,$content);
    $content=str_replace("###-TOUR_N_TICKET-###",$NTicketDetails,$content);
    $content=str_replace("###-TOUR_DATE-###",$TourDate,$content);
    $content=str_replace("###-TOUR_TYPE-###",$TourType,$content);*/
	
	
	
	/* genero il voucher fisico*/
	
	$pdf =new FPDI();  
	$pdf->AddPage();  
	// set the sourcefile  
	$parX=15;
	
	$pdf->setSourceFile('voucher_template\ROME_OPEN_TOUR_VOUCHERWEB_2012.pdf');  
	
	$tplIdx = $pdf->importPage(1);  
	$pdf->useTemplate($tplIdx, 5, 5, 200);  
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Arial','B',12);  
	$pdf->SetXY(141-$parX, 51);  
	$pdf->Write(0, $CodicePrenotazione);
	$pdf->SetFont('Arial','',10);  
	$pdf->SetXY(141-$parX, 58);  
	$pdf->Write(0, $CustomerName);
	//$pdf->Write(0,"Nome del cliente molto molto lungo");
	$pdf->SetFont('Arial','B',10);  
	$pdf->SetXY(141-$parX, 62);  
	$pdf->Write(0, $NTicketDetails);
	$pdf->SetFont('Arial','B',10);  
	$pdf->SetXY(141-$parX, 67);  
	$pdf->Write(0, $TourType);
	$pdf->SetFont('Arial','',10);  
	$pdf->SetXY(141-$parX, 71);  
	$pdf->Write(0, $TourDate);
	$file_name=$CodicePrenotazione.".pdf";
	$pdf->Output(VOUCHER_PATH.$file_name,'F');
	
	
	/*fine*/
	
	
    

     $eol="";
	if (strtoupper(substr(PHP_OS,0,3)=='WIN')) {
		$eol="\r\n";
		} elseif (strtoupper(substr(PHP_OS,0,3)=='MAC')) {
		$eol="\r";
		} else {
		$eol="\n";
		}
	
	$headers .= 'From: '.EMAIL_FROM_NAME.'  <'.EMAIL_FROM_ADDRESS.'>'.$eol;
	$headers .= "Content-Type: text/html; charset=iso-8859-1".$eol;
	
	$inviatoda='TheOpenTour.com  <info@theopentour.com>';

	$emailsubject=$TourType." reservation confirm n. ".$CodicePrenotazione;	
	
	
      //$send=mail($CustomerEmail, $emailsubject, $content, $headers.
	  
	  mail_attachment ($inviatoda, $CustomerEmail, $emailsubject, $content, "voucher/pre_paid/customers/".$file_name);
	  mail_attachment ($inviatoda, 'info@theopentour.com', $emailsubject, $content, "voucher/pre_paid/customers/".$file_name);
         $query="update rex_stato_conferme set StatoInvio=1 where IdConferma = '$conferma_id'";
        $result = odbc_exec($conn, $query );
        
        
    }

    
    
    
}
echo("cerco nuove prenotazioni....\n");
die();
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
