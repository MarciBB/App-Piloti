<?php
	
	$basepath=$_SERVER['DOCUMENT_ROOT'];
	include_once($basepath."/main_include.php");
	$config=new Config();
	$run=$config->load(); 
	$modulespath_=Config::$modulespath;
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

	$ModuloId=1;



function stampa_titoli_di_viaggio()
{
	//error_reporting(E_ALL);
	ini_set('display_errors', 0);
	$basepath=$_SERVER['DOCUMENT_ROOT'];
	ini_set("zlib.output_compression", "On");
	ini_set("zlib.output_compression", 4096);
	//$voucherpath=$basepath."/protected/modules/pdfvoucher_clienti/";
	require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdf.php");
	require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdi.php");
	
	$Euro = chr(128);
	
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

global $abilita_modifica,$tratta_wizard,$db,$user;

$DataPartenza=$_REQUEST['DataPartenza'];
$CorsaId=$_REQUEST['CorsaId'];
// serve per definire il tipo di stampa
$type = $_REQUEST['type']; 
$PrenotazioneId=$_REQUEST['PrenotazioneId'];
$PrenotazioneTitoloId=$_REQUEST['PrenotazioneTitoloId'];
$tipo_titolo="E";

if (isset($_REQUEST['TipoTitolo']))
$tipo_titolo=$_REQUEST['TipoTitolo'];

$dt=new DT();
$seller_type=2;


$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);


if ($user->GestoreId==1)
    $seller_type=1;

    
$sq_titolo="1=1";

if (isset($_REQUEST['PrenotazioneTitoloId']))
      {
    $PrenotazioneTitoloId=$_REQUEST['PrenotazioneTitoloId'];
    $sq_titolo="PrenotazioneTitoloId=".$PrenotazioneTitoloId;
}

$sq_tipo_titolo="1=1";

if (isset($_REQUEST['TipoTitolo']))
      {
    $TipoTitolo=$_REQUEST['TipoTitolo'];
    $sq_tipo_titolo="TipoTitolo='$TipoTitolo'";
}


    

$PrenotazioenId=$_REQUEST['PrenotazioneId'];
$BusNumero=$_REQUEST['NumeroBus'];
$TipoBusId=$_REQUEST['TipoBus'];

if (!isset($_REQUEST['TipoBus']))
$sql="select * from RT_PrenotazioneDettaglioCompleto where PrenotazioneTitoloId is not null and PrenotazioneId=$PrenotazioenId and TipoServizio='Bus' and Tragitto='Andata' and OdcIdRef=$user->OdcId and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza'  and GestoreIdRef IN ($InGestoreFigli) order by ClienteNome asc";
else
$sql="select * from RT_PrenotazioneDettaglioCompletoNonFiscale where BusNumero=$BusNumero and BusId=$TipoBusId and TipoServizio='Bus' and Tragitto='Andata' and OdcIdRef=$user->OdcId and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataCorsa='$DataPartenza'  and GestoreIdRef IN ($InGestoreFigli) order by ClienteNome asc";

if (isset($_REQUEST['added']))
 $sql="select * from RT_PrenotazioneDettaglioCompletoNonFiscalePDF where PrenotazioneId=$PrenotazioenId and TipoServizio='Bus' and Tragitto='Andata' and OdcIdRef=$user->OdcId and $sq_titolo and $sq_tipo_titolo and CorsaId=$CorsaId and DataInizioItinerario='$DataPartenza'  and GestoreIdRef IN ($InGestoreFigli) order by ClienteNome asc";
 
$ArrObjectP = $db->fetch_array($sql);

$sql="select * from RT_Prenotazione where PrenotazioneId=$PrenotazioneId limit 1";
$prenotazione = $db->query_first($sql);

 $numeropasseggeri=sizeof($ArrObjectP);
            $np=0;
               while ($np<$numeropasseggeri)
             {
                   $ImportoNavetta=0;
                  $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                  $PrenotazioneNumero=$ArrObjectP[$np]['PrenotazioneNumero'];
                  $PrenotazioneTitoloId=$ArrObjectP[$np]['PrenotazioneTitoloId'];
                   $ImportoBus=$ArrObjectP[$np]['Importo'];
                      $DataBiglietto=$ArrObjectP[$np]['DataEticket'];
                  
                      $CodiceBiglietto=$ArrObjectP[$np]['CodicePrenotazione']."/".$ArrObjectP[$np]['PrenotazioneNumero'];
                      $DataTitolo=$DataPartenza;
                      $TipologiaBiglietto=$ArrObjectP[$np]['TipologiaBiglietto'];
                      $ClienteNome=utf8_decode(ucwords(strtolower($ArrObjectP[$np]['ClienteNome'])));
                      $Percorso=utf8_decode($ArrObjectP[$np]['PercorsoNome']);
                      $Agenzia=utf8_decode($ArrObjectP[$np]['RagioneSociale']);
                      $Rivendita=utf8_decode($ArrObjectP[$np]['CodiceSede']);
                     
                  
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
                  
                  $ComuneSalitaBus=utf8_decode(rimuoviParentesiComune($ArrObjectP[$np]['ComunePartenza']));
                  $DataFermataSalitaBus=$dt->format($ArrObjectP[$np]['DataPartenza'], "y-m-d", "d/m/Y");
                  $OrarioFermataSalitaBus=$dt->format($ArrObjectP[$np]['OrarioPartenza'], "H:i:s", "H:i");
                  $FermataSalitaBus=utf8_decode($ArrObjectP[$np]['FermataPartenza']);
                  $SalitaBus=" il ".$DataFermataSalitaBus." ore ".$OrarioFermataSalitaBus." (".$FermataSalitaBus.")";
                  $ComuneDiscesaBus=utf8_decode(rimuoviParentesiComune($ArrObjectP[$np]['ComuneArrivo']));
                  $DataFermataDiscesaBus=$dt->format($ArrObjectP[$np]['DataArrivo'], "y-m-d", "d/m/Y");
                  $OrarioFermataDiscesaBus=$dt->format($tempOraArrivoS, "H:i:s", "H:i");
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
                   $ImportoBus=$ImportoBus+$ArrObjectR['Importo'];
                   $DataPartenzaR=$ArrObjectR['DataPartenza'];   
                   $DataCorsaR=$dt->format($DataPartenzaR, "y-m-d", "d/m/Y");
                   
                  $ComuneSalitaBusR=utf8_decode(rimuoviParentesiComune($ArrObjectR['ComunePartenza']));
                  $DataFermataSalitaBusR=$dt->format($ArrObjectR['DataPartenza'], "y-m-d", "d/m/Y");
                  $OrarioFermataSalitaBusR=$dt->format($ArrObjectR['OrarioPartenza'], "H:i:s", "H:i");
                  $FermataSalitaBusR=utf8_decode($ArrObjectR['FermataPartenza']);
                  $SalitaBusR=" il ".$DataFermataSalitaBusR." ore ".$OrarioFermataSalitaBusR." (".$FermataSalitaBusR.")";
                  $ComuneDiscesaBusR=utf8_decode(rimuoviParentesiComune($ArrObjectR['ComuneArrivo']));
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
						$pdf->SetXY($base2X + 37, $base2Y);  
						$pdf->Write(0, $Percorso);
						//PREZZO
						$pdf->SetXY($base2X + 138, $base2Y);  
						$pdf->Write(0, $ImportoBusF.$Euro);
						
						//PARTENZA
						$pdf->SetXY($base2X, $base2Y + 13);  
						$pdf->Write(0, $ComuneSalitaBus.' - '.$FermataSalitaBus);
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base2Y + 13);  
						$pdf->Write(0, $DataFermataSalitaBus);
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base2Y + 13);  
						$pdf->Write(0, $OrarioFermataSalitaBus);
						
						//ARRIVO
						$pdf->SetXY($base2X, $base2Y + 23);  
						$pdf->Write(0, $ComuneDiscesaBus.' - '.$FermataDiscesaBus);
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base2Y + 23);  
						$pdf->Write(0, $DataFermataDiscesaBus);
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base2Y + 23);  
						$pdf->Write(0, $OrarioFermataDiscesaBus);
											
						//RITORNO
						//PARTENZA
						$pdf->SetXY($base2X, $base2Y + 34.5);  
						$pdf->Write(0, $ComuneSalitaBusR.' - '.$FermataSalitaBusR);
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base2Y + 34.5);  
						$pdf->Write(0, $DataFermataSalitaBusR);
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base2Y + 34.5);  
						$pdf->Write(0, $OrarioFermataSalitaBusR);
											
						//ARRIVO
						$pdf->SetXY($base2X, $base2Y + 44.5);  
						$pdf->Write(0, $ComuneDiscesaBusR.' - '.$FermataDiscesaBusR);
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base2Y + 44.5);  
						$pdf->Write(0, $DataFermataDiscesaBusR);
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base2Y + 44.5);  
						$pdf->Write(0, $OrarioFermataDiscesaBusR);
						
						//SERVIZIO SHUTTLE -----------------------------------------------
			
						//ANDATA
						//LINEA
						$pdf->SetXY($base2X + 37, $base3Y);  
						$pdf->Write(0, $ComuneSalitaNavetta." - ".$ComuneDiscesaNavetta);
						//PREZZO
						$pdf->SetXY($base2X + 138, $base3Y);  
						$pdf->Write(0, $TotNavF.$Euro);
						
						//PARTENZA
						$pdf->SetXY($base2X, $base3Y + 13);  
						$pdf->Write(0, $ComuneSalitaNavetta." ".$FermataSalitaNavetta);
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base3Y + 13);  
						$pdf->Write(0, $DataFermataSalitaNavetta);
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base3Y + 13);  
						$pdf->Write(0, $OrarioFermataSalitaNavetta);
						
						//ARRIVO
						$pdf->SetXY($base2X, $base3Y + 23);  
						$pdf->Write(0, $ComuneDiscesaNavetta." ".$FermataDiscesaNavetta);
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base3Y + 23);  
						$pdf->Write(0, $DataFermataDiscesaNavetta);
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base3Y + 23);  
						$pdf->Write(0, $OrarioFermataDiscesaNavetta);
						
						//RITORNO
						//PARTENZA
						$pdf->SetXY($base2X, $base3Y + 34.5);  
						$pdf->Write(0, $ComuneSalitaNavettaR." ".$FermataSalitaNavettaR);
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base3Y + 34.5);  
						$pdf->Write(0, $DataFermataSalitaNavettaR);
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base3Y + 34.5);  
						$pdf->Write(0, $OrarioFermataSalitaNavettaR);
						
						//ARRIVO
						$pdf->SetXY($base2X, $base3Y + 44.5);  
						$pdf->Write(0, $ComuneDiscesaNavettaR." ".$FermataDiscesaNavettaR);
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base3Y + 44.5);  
						$pdf->Write(0, $DataFermataDiscesaNavettaR);
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base3Y + 44.5);  
						$pdf->Write(0, $OrarioFermataDiscesaNavettaR);
						
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
						$pdf->SetXY($base2X + 37, $base2Y);  
						$pdf->Write(0, "###");
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
						$pdf->SetXY($base2X + 37, $base3Y);  
						$pdf->Write(0, "###");
						//PREZZO
						$pdf->SetXY($base2X + 138, $base3Y);  
						$pdf->Write(0, 'RIMBORSO');
						
						//PARTENZA
						$pdf->SetXY($base2X, $base3Y + 13);  
						$pdf->Write(0, "###");
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base3Y + 13);  
						$pdf->Write(0, "###");
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base3Y + 13);  
						$pdf->Write(0, "###");
						
						//ARRIVO
						$pdf->SetXY($base2X, $base3Y + 23);  
						$pdf->Write(0, "###");
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base3Y + 23);  
						$pdf->Write(0, "###");
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base3Y + 23);  
						$pdf->Write(0, "###");
						
						//RITORNO
						//PARTENZA
						$pdf->SetXY($base2X, $base3Y + 34.5);  
						$pdf->Write(0, "###");
						//DATA PARTENZA
						$pdf->SetXY($base2X + 108.5, $base3Y + 34.5);  
						$pdf->Write(0, "###");
						//ORA PARTENZA
						$pdf->SetXY($base2X + 138, $base3Y + 34.5);  
						$pdf->Write(0, "###");
						
						//ARRIVO
						$pdf->SetXY($base2X, $base3Y + 44.5);  
						$pdf->Write(0, "###");
						//DATA ARRIVO
						$pdf->SetXY($base2X + 108.5, $base3Y + 44.5);  
						$pdf->Write(0, "###");
						//ORA ARRIVO
						$pdf->SetXY($base2X + 138, $base3Y + 44.5);  
						$pdf->Write(0, "###");
						
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
        			$np++;
             }
             
             if ($np==0)
                 print("Non ci sono biglietti da preparare");
			 else {
			 	$file_name=$CodicePrenotazione.".pdf";
				//$pdf->Output($voucherpath.$file_name,'F');
				$pdf->Output("biglietti.pdf",'D');
			 }
             

}






if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
			 $do=$_REQUEST['do'];
			if(!isset($do)) 
			$do='';
		
		
			switch($do) {
                                
                              

				default:
                                    stampa_titoli_di_viaggio();    
                		break;
			}
		

	

} 
// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}


// Funzione per rimuovere il testo tra parentesi tonde
function rimuoviParentesiComune($string) {
	// Espressione regolare per rimuovere qualsiasi testo tra parentesi (con spazi opzionali)
	return preg_replace('/\s*\(.*?\)\s*/i', '', $string);
}
?>