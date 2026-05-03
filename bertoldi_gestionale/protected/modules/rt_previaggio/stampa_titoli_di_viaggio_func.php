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

// 	$errors=new Errors();
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

function stampa_titoli_di_viaggio($PrenotazioneId, $DataPartenza, $CorsaId, $Tragitto, $PrenotazioneTitoloId, $TipoTitolo, $fpdfRequired = true, $pdf = null, $app = false) {

    global $dizionario, $abilita_modifica, $tratta_wizard, $db, $user, $basepath;
    //error_reporting(E_ALL);

    ini_set('display_errors', 0);
    
    //$voucherpath=$basepath."/protected/modules/pdfvoucher_clienti/";
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
	
    $sql="select * from RT_Prenotazione where PrenotazioneId=$PrenotazioneId limit 1";

    $ArrObjectTP = $db->fetch_array($sql);
    
    $tipoViaggio=1;
    $prenotazione = null;
    foreach($ArrObjectTP as $p) {
        $tipoViaggio=$p['TipoViaggioId'];
		$prenotazione = $p;
    }

    $Euro = chr(128);
    if (ob_get_level() > 0) {
		ob_end_clean(); //    the buffer and never prints or returns anything.
	}
    ob_start(); // it starts buffering
    if(!isset($pdf)){
        $pdf = new FPDI();
        $pdf->AddPage();
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
    
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',10);
    
    
    // serve per definire il tipo di stampa
    $tipo_titolo=$TipoTitolo;
    
    $dt=new DT();
    $seller_type=2;
    
    $gestore=new Gestore();
    $gestore->conn=$db;
    $gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
    $InGestoreFigli=implode(",", $gestorefigli);
    
    
    if ($user->GestoreId==1)
        $seller_type=1;
        
        $sq_titolo="PrenotazioneTitoloId=".$PrenotazioneTitoloId;
        
        $sq_tipo_titolo="TipoTitolo='$TipoTitolo'";
        
        
        $sql="select * from RT_PrenotazioneDettaglioCompleto where Stato=1 and Cancella=0 and PrenotazioneTitoloId is not null and PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='$Tragitto' and OdcIdRef=$user->OdcId and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza'  and GestoreIdRef IN ($InGestoreFigli) order by ClienteNome asc";
        
        if (($user->GestoreId==1))
            $sql="select * from RT_PrenotazioneDettaglioCompleto where Stato=1 and Cancella=0 and PrenotazioneTitoloId is not null and PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='$Tragitto' and OdcIdRef=$user->OdcId and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza' order by ClienteNome asc";
            
            $ArrObjectP = $db->fetch_array($sql);
            $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
            
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
				
                $CodicePrenotazione = $ArrObjectP[$np]['CodicePrenotazione'];
                $CodiceBiglietto=$ArrObjectP[$np]['CodicePrenotazione']."/".$ArrObjectP[$np]['PrenotazioneNumero'];
                $DataTitolo=$DataPartenza;
                if($prenotazione['TipoTour'] == 0) {
                	$TipologiaBiglietto = 'Tour di gruppo';
                } else {
                	$TipologiaBiglietto = 'Tour privato / personalizzato';
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
				$tempOraArrivo = new DateTime($ArrObjectP[$np]['OrarioArrivo']);
				$tempOraArrivo->modify("+".$sosta." hours");
				$tempOraArrivoS = $tempOraArrivo->format("H:i:s"); 
				//FINE sosta
                
                $ComuneSalitaBus=utf8_decode(rimuoviParentesiComune_func($ArrObjectP[$np]['ComunePartenza']));
                $DataFermataSalitaBus=$dt->format($ArrObjectP[$np]['DataPartenza'], "y-m-d", "d/m/Y");
                $OrarioFermataSalitaBus=$dt->format($ArrObjectP[$np]['OrarioPartenza'], "H:i:s", "H:i");
                $FermataSalitaBus=utf8_decode($ArrObjectP[$np]['FermataPartenza']);
                $SalitaBus=" il ".$DataFermataSalitaBus." ore ".$OrarioFermataSalitaBus." (".$FermataSalitaBus.")";
                $ComuneDiscesaBus=utf8_decode(rimuoviParentesiComune_func($ArrObjectP[$np]['ComuneArrivo']));
                $DataFermataDiscesaBus=$dt->format($ArrObjectP[$np]['DataArrivo'], "y-m-d", "d/m/Y");
                $OrarioFermataDiscesaBus=$dt->format($tempOraArrivoS, "H:i:s", "H:i");
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
				
				//recupero coordinate fermata
				$sql = "SELECT * FROM RT_Fermata WHERE FermataId = ".$percorso['FermataSalitaId'];
                $fermataSalitaA = $db->query_first($sql);
				$sql = "SELECT * FROM RT_Fermata WHERE FermataId = ".$percorso['FermataDiscesaId'];
                $fermataDiscesaA = $db->query_first($sql);
				
				$urlPartenza = null;
				if(isset($fermataSalitaA['Latitudine']) && $fermataSalitaA['Latitudine'] != 0) {
					$urlPartenza = "https://www.google.com/maps?q=".$fermataSalitaA['Latitudine'].",".$fermataSalitaA['Longitudine'];
				}
				
				$urlArrivo = null;
				if(isset($fermataDiscesaA['Latitudine']) && $fermataDiscesaA['Latitudine'] != 0) {
					$urlArrivo = "https://www.google.com/maps?q=".$fermataDiscesaA['Latitudine'].",".$fermataDiscesaA['Longitudine'];
				}
				//fine coordinate fermata
                
                
                if ($ArrObjectR['PrenotazioneNumero']>0)
                {
                    $sql = "SELECT RitornoAperto FROM RT_Corsa WHERE CorsaId = ".$ArrObjectR['CorsaId'];
                    $corsaRitornoTemp = $db->query_first($sql);
                    if($corsaRitornoTemp['RitornoAperto'] == 0){
                        //$ImportoBus=$ImportoBus+$ArrObjectR['Importo'];
                        $DataPartenzaR=$ArrObjectR['DataPartenza'];
                        $DataCorsaR=$dt->format($DataPartenzaR, "y-m-d", "d/m/Y");
                        
                        $ComuneSalitaBusR=utf8_decode(rimuoviParentesiComune_func($ArrObjectR['ComunePartenza']));
                        $DataFermataSalitaBusR=$dt->format($ArrObjectR['DataPartenza'], "y-m-d", "d/m/Y");
                        $OrarioFermataSalitaBusR=$dt->format($ArrObjectR['OrarioPartenza'], "H:i:s", "H:i");
                        $FermataSalitaBusR=utf8_decode($ArrObjectR['FermataPartenza']);
                        $SalitaBusR=" il ".$DataFermataSalitaBusR." ore ".$OrarioFermataSalitaBusR." (".$FermataSalitaBusR.")";
                        $ComuneDiscesaBusR=utf8_decode(rimuoviParentesiComune_func($ArrObjectR['ComuneArrivo']));
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
				$pdf->setSourceFile($pdftemplate.'pdftemplate/voucherweb.pdf');
				$tplIdx = $pdf->importPage(1);
				$pdf->useTemplate($tplIdx, 5, 5, 200);
				
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
                    
                    if ($tipo_titolo=='E' || $tipo_titolo=='X') {
                        //SERVIZIO BUS -----------------------------------------------
                        
                        //ANDATA
                        //LINEA
                        
                        //PARTENZA
                        $pdf->SetXY($base2X + 47.5, $base2Y + 61.5);
                        if(isset($urlPartenza)) {
							$pdf->SetTextColor(51, 102, 255);
							$pdf->Write(0, $ComuneSalitaBus.' - '.$FermataSalitaBus, $urlPartenza);
							$pdf->SetTextColor(0, 0, 0);
                        } else {
							$pdf->Write(0, $ComuneSalitaBus.' - '.$FermataSalitaBus);
						}
                        //DATA PARTENZA + ORA PARTENZA
                        $pdf->SetXY($base2X + 47.5, $base2Y + 69);
                        $pdf->Write(0, $DataFermataSalitaBus . " - " . $OrarioFermataSalitaBus);
                        
                        //ARRIVO
                        $pdf->SetXY($base2X  + 47.5, $base2Y + 76.5);
                        if(isset($urlArrivo)) {
							$pdf->SetTextColor(51, 102, 255);
							$pdf->Write(0, $ComuneDiscesaBus.' - '.$FermataDiscesaBus, $urlArrivo);
							$pdf->SetTextColor(0, 0, 0);
                        } else {
							$pdf->Write(0, $ComuneDiscesaBus.' - '.$FermataDiscesaBus);
						}
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
								$offsetRiepilogo += 6;
								$pdf->SetXY($base2X + 47.5, $base2Y + $offsetRiepilogo);
								$pdf->Write(0, "- x".$p['NumeroPax']." ".$p['TipologiaBiglietto']);
							}
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
							if (isset($_SERVER['HTTP_HOST'])) {
								$qrcode->setData("http://".$_SERVER['HTTP_HOST']."/protected/modules/rt_qrcode/qrcode.php?code=".$CodiceQrcode['CodiceQrcode']);
                            } else {
								$qrcode->setData("http://".Config::$ServerName."/protected/modules/rt_qrcode/qrcode.php?code=".$CodiceQrcode['CodiceQrcode']);
							}
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
							$pdf->setSourceFile($pdftemplate.'pdftemplate/voucherweb.pdf');
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
							if(isset($urlArrivo)) {
								$pdf->SetTextColor(51, 102, 255);
								$pdf->Write(0, $ComuneSalitaBusR.' - '.$FermataSalitaBusR, $urlArrivo);
								$pdf->SetTextColor(0, 0, 0);
							} else {
								$pdf->Write(0, $ComuneSalitaBusR.' - '.$FermataSalitaBusR);
							}
							//DATA PARTENZA + ORA PARTENZA
							$pdf->SetXY($base2X + 47.5, $base2Y + 69);
							$pdf->Write(0, $DataFermataSalitaBusR . " - " . $OrarioFermataSalitaBusR);
							
							//ARRIVO
							$pdf->SetXY($base2X  + 47.5, $base2Y + 76.5);
							if(isset($urlPartenza)) {
								$pdf->SetTextColor(51, 102, 255);
								$pdf->Write(0, $ComuneDiscesaBusR.' - '.$FermataDiscesaBusR, $urlPartenza);
								$pdf->SetTextColor(0, 0, 0);
							} else {
								$pdf->Write(0, $ComuneDiscesaBusR.' - '.$FermataDiscesaBusR);
							}
							//DATA ARRIVO + ORA ARRIVO
							$pdf->SetXY($base2X + 47.5, $base2Y + 84);
							$pdf->Write(0, $DataFermataDiscesaBusR . " - " . $OrarioFermataDiscesaBusR);
							
							$offsetRiepilogo = 100;
							
							//RIEPILOGO
							if(count($passeggeriBiglietto)) {
								foreach($passeggeriBiglietto as $p) {
									$offsetRiepilogo += 6;
									$pdf->SetXY($base2X + 47.5, $base2Y + $offsetRiepilogo);
									$pdf->Write(0, "- x".$p['NumeroPax']." ".$p['TipologiaBiglietto']);
								}
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
                        
                        
                    }//fine tipo EMESSO
                    
                    // rimborso
                    else {
                        //SERVIZIO BUS -----------------------------------------------
                        
                        //ANDATA
                        //LINEA
                        //$pdf->SetXY($base2X + 37, $base2Y);
                        //$pdf->Write(0, "###");
                        //PREZZO
                        $pdf->SetXY($base2X + 138, $baseY + 74);
                        $pdf->Write(0, "RIMBORSO");
                        
                        //PARTENZA
                        $pdf->SetXY($base2X + 47.5, $base2Y + 61.5);
                        $pdf->Write(0, "###");
                        //DATA PARTENZA + ORA PARTENZA
                        $pdf->SetXY($base2X + 47.5, $base2Y + 69);
                        $pdf->Write(0, "###");
                        
                        //ARRIVO
                        $pdf->SetXY($base2X  + 47.5, $base2Y + 76.5);
                        $pdf->Write(0, "###");
                        //DATA ARRIVO + ORA ARRIVO
                        $pdf->SetXY($base2X + 47.5, $base2Y + 84);
                        $pdf->Write(0, "###");
                        
						//RIEPILOGO
						$pdf->SetXY($base2X + 47.5, $base2Y + 106);
						$pdf->Write(0, "###");
						$pdf->SetXY($base2X + 47.5, $base2Y + 113.5);
						$pdf->Write(0, "RIMBORSO");
						
                        //Avvertenze coupon 
                        
                        $sql = "SELECT * FROM RT_Coupon where CouponNome like '%$CodiceBiglietto%'";
                        
                        $rowCoupon = $db->query_first($sql);
                        if(isset($rowCoupon['CouponId'])){
							$pdf->SetFont('Arial','',8);
                            $pdf->SetXY($base2X + 47.5, $base2Y + 120);
                            $pdf->Write(0, "Questo titolo rappresenta un coupon di rimborso del valore di ".$rowCoupon['Importo']."€");
                            $pdf->SetXY($base2X + 47.5, $base2Y + 124);
                            $pdf->Write(0, "Il Codice Coupon è: ".$rowCoupon['Codice']);
                            $pdf->SetXY($base2X + 47.5, $base2Y + 128);
                            $scadenza = new DateTime($rowCoupon['DataIns']);
                            $scadenza->modify('+12 months');
                            $scadenzaF = $scadenza->format('d/m/Y');
                            $pdf->Write(0, "E' utilizzabile ".$rowCoupon['MaxUtilizzi']." volta entro 12 mesi dalla data di emissione. Data di scadenza: ".$scadenzaF );
                        }

                    }//fine tipo RIMBORSO
					
					
					
					
                    
                    
                    if($np != $numeropasseggeri-1){
                        //Aggiungo la pagina
                        $pdf->AddPage();
                        //Setto il template
                        $pdf->useTemplate($tplIdx, 5, 5, 200);
                    }
                    
                if (Config::$covidMode == true){
					$docAdd = 'pdftemplate/informazioni_covid_25_01_2022.pdf';
					$pagecount = $pdf->setSourceFile($docAdd);
					for($i=0; $i<$pagecount; $i++){
						$pdf->AddPage();
						$tplidx1 = $pdf->importPage($i+1, '/MediaBox');
						$pdf->useTemplate($tplidx1, 10, 10, 200);
					}
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

// Funzione per rimuovere il testo tra parentesi tonde
function rimuoviParentesiComune_func($string) {
	// Espressione regolare per rimuovere qualsiasi testo tra parentesi (con spazi opzionali)
	return preg_replace('/\s*\(.*?\)\s*/i', '', $string);
}


