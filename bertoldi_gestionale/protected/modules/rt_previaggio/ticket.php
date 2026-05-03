<?php
    ini_set('display_errors', 0);
	$basepath=$_SERVER['DOCUMENT_ROOT'];
	include_once($basepath."/main_include.php");
	
	$config=new Config();
	$run=$config->load(); 
	$modulespath_= Config::$modulespath;
	$classespath_=Config::$classespath;
	$errors=new Errors();
	
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
	include_once($classespath_."class.Mobile.php");

	
	global $db, $user;
	$operatoreGoEuro = 191;
	$ModuloId=1;
	
	$Codice = $_GET['code'];
	
	if(!isset($db)){
		$db = new Database();
		$db->connect();
	}
	if(!isset($user)){
		$mobileObj = new Mobile();
	    $mobileObj->conn = $db;
	    $mobileObj->selectUserByOperatoreId($operatoreGoEuro);
	}
	
	
	$sql = "SELECT * FROM RT_PrenotazioneNumero WHERE CodiceQrcode='$Codice'";
	$prenotazioneId = $db->query_first($sql);
	$PrenotazioneNumeroId = $prenotazioneId['PrenotazioneNumeroId'];
	$PrenotazioneId = $prenotazioneId['PrenotazioneId'];
	
	
	$PrenotazioneObj = new Prenotazione($PrenotazioneId);
	$PrenotazioneObj->conn = $db;
	$PrenotazioneObj->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoArr = $PrenotazioneObj->DatiGeneraliPercorso;
	$PrenotazioneObj->inizializzaDatiGeneraliPercorso('R');
	$DatiGeneraliPercorsoArrRit = $PrenotazioneObj->DatiGeneraliPercorso;
	
	$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];
	$DataPartenza = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
	
	$Tragitto = 'Andata';
	
	$sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId = $PrenotazioneNumeroId";
	$titolo = $db->query_first($sql);
	
	$PrenotazioneTitoloId = $titolo['PrenotazioneTitoloId'];
	$TipoTitolo = $titolo['TipoTitolo'];
	
	//stampa_titoli_di_viaggio($PrenotazioneId, $DataPartenza, $CorsaId, $Tragitto, $PrenotazioneTitoloId, $TipoTitolo);
	
	global $dizionario;
    //error_reporting(E_ALL);
    ini_set('display_errors', 0);
    $basepath=$_SERVER['DOCUMENT_ROOT'];
    //ini_set("zlib.output_compression", "On");
    //ini_set("zlib.output_compression", 4096);
    //$voucherpath=$basepath."/protected/modules/pdfvoucher_clienti/";
    require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdf.php");
    require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdi.php");
    
    $Euro = chr(128);
    
    ob_end_clean(); //    the buffer and never prints or returns anything.
    ob_start(); // it starts buffering
    
    $pdf = new FPDI();
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
    
    global $abilita_modifica,$tratta_wizard,$db,$user;
    
    //$DataPartenza=$_REQUEST['DataPartenza'];
    //$CorsaId=$_REQUEST['CorsaId'];
    //$Tragitto=$_REQUEST['Tragitto'];
    //$PrenotazioneId=$_REQUEST['PrenotazioneId'];
    //$BusNumero=$_REQUEST['NumeroBus'];
    //$TipoBusId=$_REQUEST['TipoBus'];
    
    // serve per definire il tipo di stampa
    $type = $TipoTitolo;
    //$PrenotazioneTitoloId=$_REQUEST['PrenotazioneTitoloId'];
    $tipo_titolo="E";
    
    if (isset($TipoTitolo))
        $tipo_titolo=$TipoTitolo;
        
        $dt=new DT();
        $seller_type=2;
        
        
        $gestore=new Gestore();
        $gestore->conn=$db;
        $gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
        $InGestoreFigli=implode(",", $gestorefigli);
        
        
        if ($user->GestoreId==1)
            $seller_type=1;
            
            
            $sq_titolo="1=1";
            
            if (isset($PrenotazioneTitoloId))
            {
                $PrenotazioneTitoloId=$PrenotazioneTitoloId;
                $sq_titolo="PrenotazioneTitoloId=".$PrenotazioneTitoloId;
            }
            
            $sq_tipo_titolo="1=1";
            
            if (isset($TipoTitolo))
            {
                $TipoTitolo=$TipoTitolo;
                $sq_tipo_titolo="TipoTitolo='$TipoTitolo'";
            }
            
            if (!isset($_REQUEST['TipoBus']))
            {
                $sql="select * from RT_PrenotazioneDettaglioCompleto where Stato=1 and Cancella=0 and PrenotazioneTitoloId is not null and PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='$Tragitto' and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza' order by ClienteNome asc";
                
                if (($user->GestoreId==1) or ($user->GestoreId==2))
                    $sql="select * from RT_PrenotazioneDettaglioCompleto where Stato=1 and Cancella=0 and PrenotazioneTitoloId is not null and PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='$Tragitto' and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza' order by ClienteNome asc";
                    
            }
            else
            {
                $sql="select * from RT_PrenotazioneDettaglioCompletoNonFiscale where Stato=1 and Cancella=0 and BusNumero=$BusNumero and BusId=$TipoBusId and TipoServizio='Bus' and Tragitto='$Tragitto' and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataCorsa='$DataPartenza' order by ClienteNome asc";
                if (($user->GestoreId==1) or ($user->GestoreId==2))
                    $sql="select * from RT_PrenotazioneDettaglioCompletoNonFiscale where Stato=1 and Cancella=0 and BusNumero=$BusNumero and BusId=$TipoBusId and TipoServizio='Bus' and Tragitto='$Tragitto' and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataCorsa='$DataPartenza' order by ClienteNome asc";
                    
            }
            
            // die(); qui
            
            
            
            
            
            
            
            
            
            
            
            if (isset($_REQUEST['added']))
            {
                $sql="select * from RT_PrenotazioneDettaglioCompletoNonFiscalePDF where PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='$Tragitto' and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza' order by ClienteNome asc";
                
                if (($user->GestoreId==1) or ($user->GestoreId==2))
                    $sql="select * from RT_PrenotazioneDettaglioCompletoNonFiscalePDF where PrenotazioneId=$PrenotazioneId and TipoServizio='Bus' and Tragitto='$Tragitto' and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza' order by ClienteNome asc";
                    
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
                $TipologiaBiglietto=$ArrObjectP[$np]['TipologiaBiglietto'];
                $sql = "Select OccupaPosto from RT_TipologiaBiglietto where TipologiaBiglietto = '$TipologiaBiglietto'";
                $tempRow = $db->query_first($sql);
                if($tempRow['OccupaPosto'] == 0){
                    $ClienteNome=$ArrObjectP[$np]['TipologiaBiglietto'];
                } else {
                    $ClienteNome=utf8_decode(ucwords(strtolower($ArrObjectP[$np]['ClienteNome'])));
                }
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
                
                $sql="select * from RT_PrenotazioneDettaglioCompleto where TipoServizio='Bus' and Tragitto='Ritorno' and PrenotazioneNumero=$PrenotazioneNumero";
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
                
                $ImportoBusF=  number_format($ImportoBus,2,",","");
                $ImportoNettoBusF=  number_format($ImportoBus / 1.1 ,2,",","");
                
                $sql="select * from RT_PrenotazioneDettaglioCompleto where TipoServizio='Navetta' and Tragitto='Andata' and PrenotazioneNumero=$PrenotazioneNumero";
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
                        $sql="select * from RT_PrenotazioneDettaglioCompleto where TipoServizio='Navetta' and Tragitto='Ritorno' and PrenotazioneNumero=$PrenotazioneNumero";
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
                    
                    if ($tipo_titolo=='E' || $tipo_titolo=='X') {
                        //SERVIZIO BUS -----------------------------------------------
                        
                        //ANDATA
                        //LINEA
                        //$pdf->SetXY($base2X + 37, $base2Y);
                        //$pdf->Write(0, $Percorso);
                        //PREZZO
                        
                        if($viaggioItalia && Config::$fatturaincloudBiglietti) {
                            $pdf->SetXY($base2X + 120, $base2Y);
                            $pdf->Write(0, $ImportoBusF.$Euro);
                            $pdf->SetFont('Arial','',6);
                            $pdf->Write(0, ' IVA INC.');
                            $pdf->SetFont('Arial','',10);
                            $pdf->Write(0, ' / '.$ImportoNettoBusF.$Euro);
                            $pdf->SetFont('Arial','',6);
                            $pdf->Write(0, ' IVA ESC.');
                            $pdf->SetFont('Arial','',10);
                            
                        } else {
                            //tratta non italiana o fatturazione disattiva
                            $pdf->SetXY($base2X + 138, $base2Y);
                            $pdf->Write(0, $ImportoBusF.$Euro);
                        }
                        
                        
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
                            $qrcode->setImageSize(180, 180);
                            $qrcode->setMargin("L|0");
                            $qrcode->setOutputEncoding(QRCode::$_ENCODING_UTF8);
                            $qrcode->setOutPutFormat(QRCode::$_OUTPUT_FORMAT_PNG);
                            $pdf->Image($qrcode->getUrlQuery(), $base2X + 120, $base2Y + 110, 50, 0, 'PNG');
                            
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
                        
                        //Avvertenze interscambio
                        if(Config::$covidMode != false) {
                            if($LineaId != 13 && $LineaId !=14){
                                $sql = "SELECT * FROM RT_PrenotazioneTitoloInterscambio where PrenotazioneTitoloId = $PrenotazioneTitoloId and TipoViaggio = 'A'";
                                $rowInterscambioA = $db->fetch_array($sql);
                                if(isset($rowInterscambioA) && count($rowInterscambioA) > 0){
                                    $temp = "";
                                    foreach ($rowInterscambioA as $t){
                                        $temp .= " ".$t['Comune']." ore ".$dt->format($t['Orario'], "H:i:s", "H:i");
                                        if($t['GiorniAggiuntivi'] > 0){
                                            $temp .= " +".$t['GiorniAggiuntivi'];
                                        }
                                        $temp .= ",";
                                    }
                                    $temp = mb_substr($temp, 0, -1);
                                    $pdf->SetXY($base2X - 8, $base2Y + 110);
                                    $pdf->Write(0, "ATTENZIONE: si avvisa dei seguenti cambi bus. Viaggio di andata:");
                                    $pdf->SetXY($base2X - 8, $base2Y + 115);
                                    $pdf->Write(0, $temp);
                                }
                                $sql = "SELECT * FROM RT_PrenotazioneTitoloInterscambio where PrenotazioneTitoloId = $PrenotazioneTitoloId and TipoViaggio = 'R'";
                                $rowInterscambioR = $db->fetch_array($sql);
                                if(isset($rowInterscambioR) && count($rowInterscambioR) > 0){
                                    $temp = "";
                                    foreach ($rowInterscambioR as $t){
                                        $temp .= " ".$t['Comune']." ore ".$dt->format($t['Orario'], "H:i:s", "H:i");
                                        if($t['GiorniAggiuntivi'] > 0){
                                            $temp .= " +".$t['GiorniAggiuntivi']."gg";
                                        }
                                        $temp .= ",";
                                    }
                                    $temp = mb_substr($temp, 0, -1);
                                    $pdf->SetXY($base2X - 8, $base2Y + 120);
                                    $pdf->Write(0, "Viaggio di ritorno:");
                                    $pdf->SetXY($base2X - 8, $base2Y + 125);
                                    $pdf->Write(0, $temp);
                                }
                            }
                        }
                        
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
                        
                        
                        //Avvertenze coupon
                        
                        $sql = "SELECT * FROM RT_Coupon where CouponNome like '%$CodiceBiglietto%'";
                        
                        $rowCoupon = $db->query_first($sql);
                        if(isset($rowCoupon['CouponId'])){
                            $pdf->SetXY($base2X - 8, $base2Y + 110);
                            $pdf->Write(0, "Questo titolo rappresenta un coupon di rimborso del valore di ".$rowCoupon['Importo']."€");
                            $pdf->SetXY($base2X - 8, $base2Y + 115);
                            $pdf->Write(0, "Il Codice Coupon è: ".$rowCoupon['Codice']);
                            $pdf->SetXY($base2X - 8, $base2Y + 120);
                            $scadenza = new DateTime($rowCoupon['DataIns']);
                            $scadenza->modify('+12 months');
                            $scadenzaF = $scadenza->format('d/m/Y');
                            $pdf->Write(0, "E' utilizzabile ".$rowCoupon['MaxUtilizzi']." volta entro 12 mesi dalla data di emissione. Data di scadenza: ".$scadenzaF );
                        }
                        
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
            
            if ($np==0)
                print("Non ci sono biglietti da preparare");
                else {
                    //$file_name=$CodicePrenotazione.".pdf";
                    //$pdf->Output($voucherpath.$file_name,'F');
                    $pdf->Output("biglietti.pdf",'I');
                }
                ob_end_flush();
	
?>