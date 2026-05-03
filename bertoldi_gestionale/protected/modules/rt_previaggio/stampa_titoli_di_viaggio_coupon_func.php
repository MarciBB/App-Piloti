<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
global $basepath, $checkCronBooking;
if(!isset($basepath) || $basepath == $_SERVER['DOCUMENT_ROOT']){
    $basepath=$_SERVER['DOCUMENT_ROOT'];
    include_once($basepath."/main_include.php");
    $config=new Config();
    $run=$config->load();
    $modulespath_=Config::$modulespath;
    $classespath_=Config::$classespath;
} else {
    if(!$checkCronBooking) {
        include_once($basepath."/main_include_cron.php");
        $config=new Config();
        $run=$config->loadCron($type);
        $modulespath_= $basepath."/protected/modules/";
        $classespath_= $basepath."/protected/classes/";
    }
}

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
include_once($classespath_."class.QRCode.php");

$ModuloId=1;

function stampa_titoli_di_viaggio_coupon($PrenotazioneId, $DataPartenza, $CorsaId, $Tragitto, $PrenotazioneTitoloId, $TipoTitolo, $fpdfRequired = true, $pdf = null, $app = false)
{
    global $dizionario, $abilita_modifica, $tratta_wizard, $db, $user, $basepath;
    
    //error_reporting(E_ALL);
    ini_set('display_errors', 0);

    if($fpdfRequired){
        ini_set("zlib.output_compression", "On");
        ini_set("zlib.output_compression", 4096);
        require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdf.php");
        require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdi.php");
		if($app) {
			$pdftemplate = $basepath.'/protected/modules/rt_previaggio/';
		} else {
			$pdftemplate = '';
		}
        
    } else {
        $pdftemplate = $basepath.'/protected/modules/rt_previaggio/';
    }



    
    $Euro = chr(128);
    
    if (ob_get_level() > 0) {
		ob_end_clean(); //    the buffer and never prints or returns anything.
	}
    ob_start(); // it starts buffering
    if(!isset($pdf)){
        $pdf = new FPDI();
        $pdf->AddPage('', array(200, 280));
    }

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
    $base4X = 95;
    $base4Y = 236.8;
    
    //$pdf->setSourceFile('pdftemplate/voucherweb.pdf');
    
    //$tplIdx = $pdf->importPage(1);
    //$pdf->useTemplate($tplIdx, 5, 5, 200);
    $pdf->SetTextColor(0,0,0);
    //$pdf->SetFont('Arial','B',12);
    //$pdf->SetXY(141-$parX, 51);
    //$pdf->Write(0, $CodicePrenotazione);
    $pdf->SetFont('Arial','',10);    
    
    $tipo_titolo="E";
    
    $sql = "SELECT * FROM RT_Prenotazione where PrenotazioneId = $PrenotazioneId";
    $prenotazione = $db->query_first($sql);
    
    if (isset($TipoTitolo))
        $tipo_titolo = $TipoTitolo;
        
        $dt=new DT();
        $seller_type=2;
        
        
        $gestore=new Gestore();
        $gestore->conn=$db;
        $gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
        $InGestoreFigli=implode(",", $gestorefigli);
        
        
        if ($user->GestoreId==1)
            $seller_type=1;
            
            
            $sq_titolo="1=1";
            
            if (isset($PrenotazioneTitoloId)) {
                $sq_titolo="PrenotazioneTitoloId = ".$PrenotazioneTitoloId;
            }
            
            $sq_tipo_titolo="1=1";
            
            if (isset($TipoTitolo))  {
                $sq_tipo_titolo="TipoTitolo = '$TipoTitolo'";
            }
            
            $sql="select * from RT_PrenotazioneDettaglioCompleto where Stato=1 and Cancella=0 and PrenotazioneTitoloId is not null and PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='$Tragitto' and OdcIdRef=$user->OdcId and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza'  and GestoreIdRef IN ($InGestoreFigli) order by ClienteNome asc";
            if (($user->GestoreId==1) or ($user->GestoreId==2)) {
                $sql="select * from RT_PrenotazioneDettaglioCompleto where Stato=1 and Cancella=0 and PrenotazioneTitoloId is not null and PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='$Tragitto' and OdcIdRef=$user->OdcId and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza' order by ClienteNome asc";
            }        
            
            
            
            $ArrObjectP = $db->fetch_array($sql);
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
            while ($np<$numeropasseggeri) {
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
                if($prenotazione['TipoTour'] == 0) {
                	$TipologiaBiglietto = $dizionario['generale']['tour_gruppo'];
                } else {
                	$TipologiaBiglietto = $dizionario['generale']['tour_privato'];
                }                
                $ClienteNome=utf8_decode(ucwords(strtolower($prenotazione['ClienteNome'])));
                
                if($tipo_titolo=='X' || ($tipo_titolo=='R' && $tempRow['OccupaPosto'] == 1 && strpos($ArrObjectP[$np]['Codice'], 'E-') == 0)){
                    $TipologiaBiglietto = "EXTRA";
                }
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
                
                $sqlTemp = "SELECT * FROM RT_Corsa WHERE CorsaId = ".$CorsaId;
                $rowTemp = $db->query_first($sqlTemp);
                $LineaId = $rowTemp['LineaId'];
                
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
                $ritornoOpen = 0;
                
                //controllo tratta italiana
                $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$PrenotazioneId;
                $percorso = $db->query_first($sql);
                $sqlAndata = "SELECT r.idnazione FROM Comune c
                        LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
                        LEFT JOIN Regione r on r.RegioneId = p.RegioneId
                        WHERE c.ComuneId = ".$percorso['ComuneSalitaId'];
                $nazioneAndata = $db->query_first($sqlAndata);
                $sqlRitorno = "SELECT r.idnazione FROM Comune c
                        LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
                        LEFT JOIN Regione r on r.RegioneId = p.RegioneId
                        WHERE c.ComuneId = ".$percorso['ComuneDiscesaId'];
                $nazioneRitorno = $db->query_first($sqlRitorno);
                $viaggioItalia = false;
                if($nazioneAndata['idnazione'] == 1 && $nazioneRitorno['idnazione'] == 1) {
                    $viaggioItalia = true;
                } else {
                    $viaggioItalia = false;
                }
                //fine controllo tratta italiana
                
                
                if ($ArrObjectR['PrenotazioneNumero']>0)
                {
                    $sql = "SELECT RitornoAperto FROM RT_Corsa WHERE CorsaId = ".$ArrObjectR['CorsaId'];
                    $corsaRitornoTemp = $db->query_first($sql);
                    if($corsaRitornoTemp['RitornoAperto'] == 0){
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
                    } else {
                        $DataPartenzaR="Open";
                        $ComuneSalitaBusR=utf8_decode($ArrObjectR['ComunePartenza']);
                        $FermataSalitaBusR=utf8_decode($ArrObjectR['FermataPartenza']);
                        $ComuneDiscesaBusR=utf8_decode($ArrObjectR['ComuneArrivo']);
                        $FermataDiscesaBusR=utf8_decode($ArrObjectR['FermataArrivo']);
                        $TipoViaggioId=2;
                        $ritornoOpen = 1;
                        $maxDataOpen = new DateTime($DataPartenza);
                        $maxDataOpen->modify('+6 months');
                        $ScadenzaOpen = $maxDataOpen->format('d/m/Y');
                    }
                }
				
				//*****BIGLIETTO ANDATA CORSA SINGOLA
				$pdf->setSourceFile($pdftemplate.'pdftemplate/voucherweb_coupon.pdf');
				$tplIdx = $pdf->importPage(1);
				$pdf->useTemplate($tplIdx, 10, 0, 160);
				
                $ImportoBusF=  number_format($ImportoBus,2,",","");
                $ImportoNettoBusF=  number_format($ImportoBus / 1.1 ,2,",","");
                
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
                    
                    if ($tipo_titolo=='R') {
                    
                        //Avvertenze coupon 
                        
                        $sql = "SELECT * FROM RT_Coupon where CouponNome like '%$CodiceBiglietto%'";
                        
                        $rowCoupon = $db->query_first($sql);
                        if(isset($rowCoupon['CouponId'])){
							$pdf->SetFont('Helvetica','',22);
							$pdf->SetTextColor(24, 58, 101);
                            $pdf->SetXY($base2X + 55, $base2Y + 180);
                            $pdf->Write(0, $rowCoupon['Codice']); 
                        }
						
						$pdf->AddPage('', array(200, 270));
						$pdf->setSourceFile($pdftemplate.'pdftemplate/voucherweb_coupon.pdf');
						$tplIdx = $pdf->importPage(2);
						$pdf->useTemplate($tplIdx, 10, 0, 160);
						
						if(isset($rowCoupon['CouponId'])){
							$pdf->SetFont('Helvetica','',22);
							$pdf->SetTextColor(24, 58, 101);
                            $pdf->SetXY($base2X + 55, $base2Y + 180);
                            $pdf->Write(0, $rowCoupon['Codice']); 
                        }

                    }//fine tipo RIMBORSO
					

                    if($np != $numeropasseggeri-1){
                        //Aggiungo la pagina
                        $pdf->AddPage();
                        //Setto il template
                        $pdf->useTemplate($tplIdx, 5, 5, 200);
                    }
                    
                    if (Config::$covidMode == true){
                        //						$docAdd = 'pdftemplate/Autodichiarazione_ingresso.pdf';
                        //						$pagecount = $pdf->setSourceFile($docAdd);
                        //						for($i=0; $i<$pagecount; $i++){
                        //							$pdf->AddPage();
                        //							$tplidx1 = $pdf->importPage($i+1, '/MediaBox');
                        //							$pdf->useTemplate($tplidx1, 10, 10, 200);
                        //						}
                        
                        $docAdd = 'pdftemplate/informazioni_covid_25_01_2022.pdf';
                        $pagecount = $pdf->setSourceFile($docAdd);
                        for($i=0; $i<$pagecount; $i++){
                            $pdf->AddPage();
                            $tplidx1 = $pdf->importPage($i+1, '/MediaBox');
                            $pdf->useTemplate($tplidx1, 10, 10, 200);
                        }
                        
                        // 						$docAdd = 'pdftemplate/modulo_rientro_sintetico_05_gennaio_2021.pdf';
                        // 						$pagecount = $pdf->setSourceFile($docAdd);
                        // 						for($i=0; $i<$pagecount; $i++){
                        // 							$pdf->AddPage();
                        // 							$tplidx1 = $pdf->importPage($i+1, '/MediaBox');
                        // 							$pdf->useTemplate($tplidx1, 10, 10, 200);
                        // 						}
                    }
                    
                    
                    $np++;
            }
            
            if($fpdfRequired) {
                if ($np==0)
                    print("Non ci sono biglietti da preparare");
                    else {
                        $file_name=$CodicePrenotazione.".pdf";
                        //$pdf->Output($voucherpath.$file_name,'F');
                        if($app) {
							$pdf->Output("biglietti.pdf",'D');
						} else {
							$pdf->Output("biglietti.pdf",'I');
						}
                    }
                    ob_end_flush();
            }
                            
}


function stampa_coupon($CouponId, $fpdfRequired = true, $pdf = null, $app = false)
{
    global $dizionario, $db, $user, $basepath;

    ini_set('display_errors', 0);

    if($fpdfRequired){
        ini_set("zlib.output_compression", "On");
        ini_set("zlib.output_compression", 4096);
        require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdf.php");
        require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdi.php");
        if($app) {
            $pdftemplate = $basepath.'/protected/modules/rt_previaggio/';
        } else {
            $pdftemplate = '';
        }
    } else {
        $pdftemplate = $basepath.'/protected/modules/rt_previaggio/';
    }

    $Euro = chr(128);

    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    ob_start();
    if(!isset($pdf)){
        $pdf = new FPDI();
        $pdf->AddPage('', array(200, 280));
    }

    // Base positions
    $base2X = 26;
    $base2Y = 60;

    // Recupera il coupon
    $sql = "SELECT * FROM RT_Coupon WHERE CouponId = $CouponId";
    $rowCoupon = $db->query_first($sql);

    // Pagina principale con codice coupon
    $pdf->setSourceFile($pdftemplate.'pdftemplate/voucherweb_coupon.pdf');
    $tplIdx = $pdf->importPage(1);
    $pdf->useTemplate($tplIdx, 25, 0, 160);

    if(isset($rowCoupon['CouponId'])){
        $pdf->SetFont('Helvetica','',22);
        $pdf->SetTextColor(24, 58, 101);
        $pdf->SetXY($base2X + 55, $base2Y + 155);
        $pdf->Write(0, $rowCoupon['Codice']);

        // Visualizza importo/percentuale
        $pdf->SetFont('Arial','',9);
        $pdf->SetTextColor(0,0,0);
        $y = $base2Y + 165;
        // Importo/percentuale allineato a sinistra
        if (!empty($rowCoupon['Percentuale']) && floatval($rowCoupon['Percentuale']) != 0) {
            $pdf->SetXY($base2X + 10, $y);
            $pdf->Write(0, 'Sconto: ' . $rowCoupon['Percentuale'] . '%');
            $y += 5;
        } elseif (!empty($rowCoupon['Importo'])) {
            $pdf->SetXY($base2X + 10, $y);
            $pdf->Write(0, 'Importo coupon: ' . number_format($rowCoupon['Importo'],2,',','') . ' '.$Euro);
            $y += 5;
        }

        // Frase introduttiva subito dopo importo/percentuale
        $pdf->SetXY($base2X + 10, $y);
        $pdf->Write(0, 'Il seguente coupon e\' utilizzabile nei seguenti casi:');
        $y += 5;

        // Visualizza intervallo di validità allineato a sinistra
        if (!empty($rowCoupon['ValidoDa']) || !empty($rowCoupon['ValidoA'])) {
            $pdf->SetXY($base2X + 10, $y);
            if (!empty($rowCoupon['ValidoDa']) && empty($rowCoupon['ValidoA'])) {
                $intervallo = 'Valido dal ' . date('d/m/Y', strtotime($rowCoupon['ValidoDa']));
            } elseif (!empty($rowCoupon['ValidoDa']) && !empty($rowCoupon['ValidoA'])) {
                $intervallo = 'Valido dal ' . date('d/m/Y', strtotime($rowCoupon['ValidoDa'])) . ' al ' . date('d/m/Y', strtotime($rowCoupon['ValidoA']));
            } elseif (empty($rowCoupon['ValidoDa']) && !empty($rowCoupon['ValidoA'])) {
                $intervallo = 'Valido fino al ' . date('d/m/Y', strtotime($rowCoupon['ValidoA']));
            } else {
                $intervallo = '';
            }
            if ($intervallo !== '') {
                $pdf->Write(0, $intervallo);
                $y += 5;
            }
        }

        // Fasce orarie del coupon
        if (isset($rowCoupon['CouponId'])) {
            $sqlFasce = "SELECT * FROM RT_CouponFasciaOraria WHERE CouponId = " . intval($rowCoupon['CouponId']);
            $fasceOrarie = $db->fetch_array($sqlFasce);
            if (!empty($fasceOrarie)) {
                foreach ($fasceOrarie as $record) {
                    $fascia = '';
                    if (!empty($record['OraInizio']) && !empty($record['OraFine'])) {
                        $fascia = 'Fascia oraria: ' . substr($record['OraInizio'],0,5) . ' - ' . substr($record['OraFine'],0,5);
                    } elseif (!empty($record['OraInizio'])) {
                        $fascia = 'Fascia oraria: dalle ' . substr($record['OraInizio'],0,5);
                    } elseif (!empty($record['OraFine'])) {
                        $fascia = 'Fascia oraria: fino alle ' . substr($record['OraFine'],0,5);
                    }
                    if ($fascia !== '') {
                        $pdf->SetXY($base2X + 10, $y);
                        $pdf->Write(0, $fascia);
                        $y += 5;
                    }
                }
            }
        }

        // Visualizza partenza/destinazione/linea allineati a sinistra
        $infoLuogo = [];
        // Gestione PartenzaId multipli
        if (!empty($rowCoupon['PartenzaId'])) {
            $partenzaIds = explode(',', $rowCoupon['PartenzaId']);
            $nomiPartenza = [];
            foreach ($partenzaIds as $pid) {
                $pid = intval(trim($pid));
                if ($pid > 0) {
                    $sql = "SELECT Comune FROM Comune WHERE ComuneId = $pid";
                    $row = $db->query_first($sql);
                    if($row && !empty($row['Comune'])) $nomiPartenza[] = $row['Comune'];
                }
            }
            if (count($nomiPartenza) > 0) $infoLuogo[] = 'Partenza: ' . implode(', ', $nomiPartenza);
        }
        // Gestione DestinazioneId multipli
        if (!empty($rowCoupon['DestinazioneId'])) {
            $destinazioneIds = explode(',', $rowCoupon['DestinazioneId']);
            $nomiDestinazione = [];
            foreach ($destinazioneIds as $did) {
                $did = intval(trim($did));
                if ($did > 0) {
                    $sql = "SELECT Comune FROM Comune WHERE ComuneId = $did";
                    $row = $db->query_first($sql);
                    if($row && !empty($row['Comune'])) $nomiDestinazione[] = $row['Comune'];
                }
            }
            if (count($nomiDestinazione) > 0) $infoLuogo[] = 'Destinazione: ' . implode(', ', $nomiDestinazione);
        }
        // Gestione LineaId multipli
        if (!empty($rowCoupon['LineaId'])) {
            $lineaIds = explode(',', $rowCoupon['LineaId']);
            $nomiLinea = [];
            foreach ($lineaIds as $lid) {
                $lid = intval(trim($lid));
                if ($lid > 0) {
                    $sql = "SELECT LineaNome FROM RT_Linea WHERE LineaId = $lid";
                    $row = $db->query_first($sql);
                    if($row && !empty($row['LineaNome'])) $nomiLinea[] = $row['LineaNome'];
                }
            }
            if (count($nomiLinea) > 0) $infoLuogo[] = 'Linea: ' . implode(', ', $nomiLinea);
        }
        if (count($infoLuogo) > 0) {
            foreach ($infoLuogo as $luogo) {
                $pdf->SetXY($base2X + 10, $y);
                $pdf->Write(0, $luogo);
                $y += 5;
            }
        }
    }

    // Pagina aggiuntiva (se serve)
    $pdf->AddPage('', array(200, 280));
    $pdf->setSourceFile($pdftemplate.'pdftemplate/voucherweb_coupon.pdf');
    $tplIdx = $pdf->importPage(2);
    $pdf->useTemplate($tplIdx, 25, 0, 160);

    if(isset($rowCoupon['CouponId'])){
        $pdf->SetFont('Helvetica','',22);
        $pdf->SetTextColor(24, 58, 101);
        $pdf->SetXY($base2X + 55, $base2Y + 155);
        $pdf->Write(0, $rowCoupon['Codice']);

        // Visualizza importo/percentuale
        $pdf->SetFont('Arial','',9);
        $pdf->SetTextColor(0,0,0);
        $y = $base2Y + 165;
        // Importo/percentuale allineato a sinistra
        if (!empty($rowCoupon['Percentuale']) && floatval($rowCoupon['Percentuale']) != 0) {
            $pdf->SetXY($base2X + 10, $y);
            $pdf->Write(0, 'Discount: ' . $rowCoupon['Percentuale'] . '%');
            $y += 5;
        } elseif (!empty($rowCoupon['Importo'])) {
            $pdf->SetXY($base2X + 10, $y);
            $pdf->Write(0, 'Coupon Amount: ' . number_format($rowCoupon['Importo'],2,',','') . ' '.$Euro);
            $y += 5;
        }

        // Frase introduttiva subito dopo importo/percentuale
        $pdf->SetXY($base2X + 10, $y);
        $pdf->Write(0, 'The following coupon can be used in the following cases:');
        $y += 5;

        // Visualizza intervallo di validità allineato a sinistra
        if (!empty($rowCoupon['ValidoDa']) || !empty($rowCoupon['ValidoA'])) {
            $pdf->SetXY($base2X + 10, $y);
            if (!empty($rowCoupon['ValidoDa']) && empty($rowCoupon['ValidoA'])) {
                $intervallo = 'Valid from ' . date('d/m/Y', strtotime($rowCoupon['ValidoDa']));
            } elseif (!empty($rowCoupon['ValidoDa']) && !empty($rowCoupon['ValidoA'])) {
                $intervallo = 'Valid from ' . date('d/m/Y', strtotime($rowCoupon['ValidoDa'])) . ' to ' . date('d/m/Y', strtotime($rowCoupon['ValidoA']));
            } elseif (empty($rowCoupon['ValidoDa']) && !empty($rowCoupon['ValidoA'])) {
                $intervallo = 'Valid until ' . date('d/m/Y', strtotime($rowCoupon['ValidoA']));
            } else {
                $intervallo = '';
            }
            if ($intervallo !== '') {
                $pdf->Write(0, $intervallo);
                $y += 5;
            }
        }

        // Fasce orarie del coupon (seconda pagina)
        if (isset($rowCoupon['CouponId'])) {
            $sqlFasce = "SELECT * FROM RT_CouponFasciaOraria WHERE CouponId = " . $rowCoupon['CouponId'];
            $fasceOrarie = $db->fetch_array($sqlFasce);
            if (!empty($fasceOrarie)) {
                foreach ($fasceOrarie as $record) {
                    $fascia = '';
                    if (!empty($record['OraInizio']) && !empty($record['OraFine'])) {
                        $fascia = 'Time slot: ' . substr($record['OraInizio'],0,5) . ' - ' . substr($record['OraFine'],0,5);
                    } elseif (!empty($record['OraInizio'])) {
                        $fascia = 'Time slot: from ' . substr($record['OraInizio'],0,5);
                    } elseif (!empty($record['OraFine'])) {
                        $fascia = 'Time slot: until ' . substr($record['OraFine'],0,5);
                    }
                    if ($fascia !== '') {
                        $pdf->SetXY($base2X + 10, $y);
                        $pdf->Write(0, $fascia);
                        $y += 5;
                    }
                }
            }
        }

        // Visualizza partenza/destinazione/linea allineati a sinistra
        $infoLuogo = [];
        // Gestione PartenzaId multipli
        if (!empty($rowCoupon['PartenzaId'])) {
            $partenzaIds = explode(',', $rowCoupon['PartenzaId']);
            $nomiPartenza = [];
            foreach ($partenzaIds as $pid) {
                $pid = intval(trim($pid));
                if ($pid > 0) {
                    $sql = "SELECT Comune FROM Comune WHERE ComuneId = $pid";
                    $row = $db->query_first($sql);
                    if($row && !empty($row['Comune'])) $nomiPartenza[] = $row['Comune'];
                }
            }
            if (count($nomiPartenza) > 0) $infoLuogo[] = 'Departure: ' . implode(', ', $nomiPartenza);
        }
        // Gestione DestinazioneId multipli
        if (!empty($rowCoupon['DestinazioneId'])) {
            $destinazioneIds = explode(',', $rowCoupon['DestinazioneId']);
            $nomiDestinazione = [];
            foreach ($destinazioneIds as $did) {
                $did = intval(trim($did));
                if ($did > 0) {
                    $sql = "SELECT Comune FROM Comune WHERE ComuneId = $did";
                    $row = $db->query_first($sql);
                    if($row && !empty($row['Comune'])) $nomiDestinazione[] = $row['Comune'];
                }
            }
            if (count($nomiDestinazione) > 0) $infoLuogo[] = 'Destination: ' . implode(', ', $nomiDestinazione);
        }
        // Gestione LineaId multipli
        if (!empty($rowCoupon['LineaId'])) {
            $lineaIds = explode(',', $rowCoupon['LineaId']);
            $nomiLinea = [];
            foreach ($lineaIds as $lid) {
                $lid = intval(trim($lid));
                if ($lid > 0) {
                    $sql = "SELECT LineaNome FROM RT_Linea WHERE LineaId = $lid";
                    $row = $db->query_first($sql);
                    if($row && !empty($row['LineaNome'])) $nomiLinea[] = $row['LineaNome'];
                }
            }
            if (count($nomiLinea) > 0) $infoLuogo[] = 'Line: ' . implode(', ', $nomiLinea);
        }
        if (count($infoLuogo) > 0) {
            foreach ($infoLuogo as $luogo) {
                $pdf->SetXY($base2X + 10, $y);
                $pdf->Write(0, $luogo);
                $y += 5;
            }
        }
    }

    if($fpdfRequired) {
        $file_name = $rowCoupon['Codice'].".pdf";
        if($app) {
            $pdf->Output($file_name,'D');
        } else {
            $pdf->Output($file_name,'I');
        }
        ob_end_flush();
    }
}


?>