<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Classe di interrogazione del db per tutte le tabelle riguardanti la prenotazione
 * Data Ultima Modifica 17/09/2014
 * @author Marco Casaburi
 */

class Prenotazione {
    
    
    public $Id;
    public $conn;
    public $DatiGenerali;
    public $DatiGeneraliPercorso;
    public $DatiGeneraliCarico;
    
    
    
    function __construct($Id=null) {
        $this->Id = $Id;
    }
    
    public function TitoliEmessiYesNo()
    {
        global $user;
        $db=$this->conn;
        $PrenotazioneId=$this->Id;
        $sql="select * from RT_PrenotazioneTitolo where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Stato=1 and Cancella=0";
        $row = $db->query_first($sql);
        
        if (!empty($row['PrenotazioneId']))
            return true;
            else
                return false;
    }
    
    
    public function CheckPrivilegiPrenotazione()
    {
        // verifico se posso anche solo pernotare
        global $user;
    }
    
    
    
    public function CheckStatoPrenotazione()
    {
        // verifico se posso anche solo prenotare
        global $user;
    }
    
    public function GetDaConfermare($arr_tratte)
    {
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        
        //      print_r($arr_tratte);
        $data=$arr_tratte;
        $valori_tratte=0;
        foreach ($data as $chiave => $valore)
        {
            
            
            $valori_tratte.=",".$valore['TrattaId'];
        }
        
        
        
        $sql="select * from RT_Tratta where TrattaId IN ($valori_tratte) and Stato=1 and Cancella=0 and DaConfermare=1";
        $row = $db->query_first($sql);
        
        if (!empty($row['TrattaId']))
            return 1;
            else
                return 0;
                
                
    }
    public function GetFermateDaConfermare($arr_tratte)
    {
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        
        //      print_r($arr_tratte);
        $data=$arr_tratte;
        $valori_tratte=0;
        foreach ($data as $chiave => $valore)
        {
            
            
            $valori_tratte.=",".$valore['FermataId'];
        }
        
        
        
        $sql="select FermataId from RT_Fermata where FermataId IN ($valori_tratte) and Stato=1 and Cancella=0 and IsDaConfermare=1";
        
        //die($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['FermataId']))
            return 1;
            else
                return 0;
                
                
    }
    
    public function GetScontoPromozioneAttiva($CorsaId,$DataCorsa,$Pax,$TipoBigliettoId)
    {
        //die();
        //global $user;
        $db=$this->conn;
        
        $PostiPrenotati=1;
        $PostiPrenotati1=0;
        
        $sql="select
		        `RT_PrenotazionePercorso`.`CorsaId` AS `CorsaId`,
		        `RT_PrenotazionePercorso`.`CorsaDataPartenza` AS `CorsaDataPartenza`,
		        (sum((`RT_Prenotazione`.`TotalePaxPrenotati` - `RT_PrenotazionePercorso`.`PasseggeriEsclusi`)) - (select
		                count(0)
		            from
		                (`RT_PrenotazioneTitolo` `t`
		                left join `RT_PrenotazioneDettaglio` `n` ON (((`t`.`PrenotazioneNumeroId` = `n`.`PrenotazioneNumero`)
		                    and (`t`.`PrenotazioneId` = `n`.`PrenotazioneId`))))
		            where
		                ((`n`.`CorsaId` = `RT_PrenotazionePercorso`.`CorsaId`)
		                    and (`n`.`DataInizioItinerario` = `RT_PrenotazionePercorso`.`CorsaDataPartenza`)
		                    and (`t`.`Codice` like 'E-%')))) AS `TotalePaxPrenotati`,
		        `RT_Prenotazione`.`OdcIdRef` AS `OdcIdRef`
		    from
		        ((`RT_Prenotazione`
		        join `RT_PrenotazionePercorso` ON ((`RT_Prenotazione`.`PrenotazioneId` = `RT_PrenotazionePercorso`.`PrenotazioneId`)))
		        join `RT_AppPrenotazioneStato` ON ((`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)))
		    where
		        ((`RT_Prenotazione`.`Cancella` = 0)
		            and (`RT_PrenotazionePercorso`.`Cancella` = 0)
		            and (`RT_PrenotazionePercorso`.`Stato` = 1)
		            and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)) and
				`RT_PrenotazionePercorso`.CorsaId=$CorsaId and `RT_PrenotazionePercorso`.CorsaDataPartenza='$DataCorsa'
		    group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`";
        
        $row = $db->query_first($sql);
        if (!empty($row['TotalePaxPrenotati']))
        {
            $PostiPrenotati=$row['TotalePaxPrenotati'];
            $PostiPrenotati1=$PostiPrenotati;
        } else {
            $PostiPrenotati=0;
            $PostiPrenotati1=0;
        }
        
        $PostiPrenotati2=$PostiPrenotati+$Pax;
        
        // seleziono le promozioni attive per il numero di pax che si vuole prenotare
        $sql="SELECT
		RT_Scontistica.ListinoId
		FROM
		RT_Scontistica
		LEFT JOIN RT_ScontisticaBiglietto ON RT_Scontistica.ListinoId = RT_ScontisticaBiglietto.ListinoId
		LEFT JOIN RT_ScontisticaCorsa ON RT_ScontisticaBiglietto.ListinoId = RT_ScontisticaCorsa.ListinoId
		LEFT JOIN RT_ScontisticaCorsaDettaglio ON RT_ScontisticaCorsa.ScontisticaCorsaId = RT_ScontisticaCorsaDettaglio.ScontisticaCorsaId
		WHERE
		RT_ScontisticaBiglietto.BigliettoId = $TipoBigliettoId AND
		RT_Scontistica.DaPax <= $PostiPrenotati2 AND
		RT_Scontistica.APax >= $PostiPrenotati2 AND
		CorsaId=$CorsaId and
		RT_ScontisticaCorsa.Dal <='$DataCorsa' AND
		RT_ScontisticaCorsa.Al >= '$DataCorsa' AND
		RT_Scontistica.Stato = 1 AND
		RT_Scontistica.Cancella = 0 AND
		RT_ScontisticaBiglietto.Stato = 1 AND
		RT_ScontisticaBiglietto.Cancella = 0 AND
		RT_Scontistica.AttivaDal <= Now() AND
		RT_Scontistica.AttivaAl >= Now() AND
		RT_ScontisticaCorsa.Stato = 1
		order by RT_Scontistica.ListinoPeso,DaPax,APax";
        //     echo $sql."<br><br>";
        $row = $db->query_first($sql);
        $ListinoId=0;
        if (!empty($row['ListinoId']))
        {
            $ListinoId=$row['ListinoId'];
        }
        
        return $ListinoId;
    }
    
    public function getScontisticaGiornoPrima($DataPartenza,$CorsaId)
    {
        
        /* global $user;
         $db=$this->conn;
         $DataOdierna=Date('Y-m-d');
         $to_time = strtotime($DataPartenza);
         $from_time = strtotime($DataOdierna);
         $secondi_residui=round(($to_time - $from_time));
         $giorni=round($secondi_residui/3600/24);
         
         
         $sql="select ListinoId from RT_Scontistica where DaGiorniPrima<=$giorni and AGiorniPrima>=$giorni and Stato=1 and Cancella=0";
         
         $row = $db->query_first($sql);
         $ListinoId=0;
         if (!empty($row['ListinoId']))
         $ListinoId=$row['ListinoId'];
         
         return $ListinoId;*/
         return 0; 
    }
    
    
    /**
     * Crea il dettaglio della prenotazione per ogni biglietto e percorso associato.
     * @param int $CorsaId ID della corsa
     * @param string $Tragitto Tipo di tragitto (es. 'Andata', 'Ritorno')
     * @param string $TipoViaggio Tipo di viaggio
     * @param int $stato Stato della prenotazione
     * @param int|null $gestore ID gestore (opzionale)
     * @return bool
     */
    public function CreateDettaglioPrenotazione($CorsaId, $Tragitto, $TipoViaggio, $stato, $gestore = null)
    {
        global $user;
        $db = $this->conn;
        $PrenotazioneId = $this->Id;
        $storico = new StoricoOperazioni();
        $storico->conn = $db;
        $dt = new DT();

        // Recupero aliquote IVA e provvigioni
        $sql = "SELECT * FROM RT_AppAliquota WHERE OdcIdRef=$user->OdcId AND ValidaDal<=NOW() AND ValidaAl>=NOW() AND Stato=1 AND Cancella=0";
        $row22 = $db->query_first($sql);
        $aliquota_b = 0;
        $aliquota_p = 0;
        if ($row22['AliquotaId'] > 0) {
            $aliquota_b = $row22['AliquotaBiglietto'];
            $aliquota_p = $row22['AliquotaProvvigioni'];
        }

        // Recupero percorsi associati alla prenotazione
        $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE OdcIdRef=$user->OdcId AND PrenotazioneId=$PrenotazioneId AND Stato=1 AND Cancella=0 AND CorsaId=$CorsaId";
        $ArrPercorso = $db->fetch_array($sql);

        // Recupero tipo viaggio
        $sql = "SELECT TipoViaggioId FROM RT_Prenotazione WHERE OdcIdRef=$user->OdcId AND PrenotazioneId=$PrenotazioneId AND Stato=1 AND Cancella=0";
        $row = $db->query_first($sql);
        $TipoViaggioId = $row['TipoViaggioId'];

        // Recupero biglietti associati alla prenotazione
        $sql = "SELECT TipologiaBigliettoId, TipologiaBiglietto, AumentoPax, RiduzionePax, NumeroPax, PrezzoTotalePax FROM RT_PrenotazioneBiglietto WHERE OdcIdRef=$user->OdcId AND PrenotazioneId=$PrenotazioneId AND Stato=1 AND Cancella=0";
        $ArrObjectP = $db->fetch_array($sql);
        $tipologieBigliettiCount = sizeof($ArrObjectP);
        $tpb = 0;

        // Ciclo su tutte le tipologie di biglietti
        while ($tpb < $tipologieBigliettiCount) {
            $TipologiaBigliettoId = $ArrObjectP[$tpb]['TipologiaBigliettoId'];
            $TipologiaBiglietto = $ArrObjectP[$tpb]['TipologiaBiglietto'];
            $NumeroPax = $ArrObjectP[$tpb]['NumeroPax'];
            $PrezzoTotalePax = $ArrObjectP[$tpb]['PrezzoTotalePax'];
            $AumentoPax = $ArrObjectP[$tpb]['AumentoPax'];
            $RiduzionePax = $ArrObjectP[$tpb]['RiduzionePax'];

            $nper = 0;
            $numeroPercorsi = sizeof($ArrPercorso);

            // Ciclo su tutti i percorsi
            while ($nper < $numeroPercorsi) {
                $dataToIns = [];
                $dataToIns['PrenotazioneId'] = $PrenotazioneId;
                $dataToIns['Tragitto'] = $Tragitto;
                $dataToIns['PercorsoNome'] = $ArrPercorso[$nper]['PercorsoNome'];
                $dataToIns['LineaNome'] = $ArrPercorso[$nper]['LineaNome'];
                $dataToIns['CorsaNome'] = $ArrPercorso[$nper]['CorsaNome'];

                $ntr = 0;
                $numeroTratte = 1; // In questo caso si considera una sola tratta

                // Ciclo sulle tratte (qui solo una)
                while ($ntr < $numeroTratte) {
                    $LineaId = $ArrPercorso[$nper]['LineaId'];
                    $dataToIns['LineaId'] = $LineaId;

                    // Recupero PercorsoId dalla linea
                    $sql = "SELECT PercorsoId FROM RT_Linea WHERE OdcIdRef=$user->OdcId AND LineaId=$LineaId";
                    $row = $db->query_first($sql);
                    $PercorsoId = $row['PercorsoId'];
                    $dataToIns['PercorsoId'] = $PercorsoId;

                    // Recupero provvigioni gestore
                    $sql = "SELECT * FROM RT_GestoreProvvigioneDettaglio WHERE GestoreId=$user->GestoreId AND BigliettoId=$TipologiaBigliettoId AND LineaId=$LineaId";
                    $row = $db->query_first($sql);
                    $percentuale_a = 0;
                    $fisso_a = 0;
                    if ($row['GestoreConvenzioneId'] > 0) {
                        $percentuale_a = $row['Percentuale'];
                        $fisso_a = $row['Fisso'];
                    }

                    $dataToIns['AliquotaBiglietto'] = $aliquota_b;
                    $dataToIns['AliquotaProvvigione'] = $aliquota_p;

                    $Mezzo = "Bus";
                    $dataToIns['TipoServizio'] = $Mezzo;
                    $dataToIns['TipologiaBiglietto'] = $TipologiaBiglietto;
                    $dataToIns['CorsaId'] = $CorsaId;
                    $dataToIns['TipoViaggio'] = $TipoViaggio;

                    // Imposto data e orario di partenza/arrivo
                    $dataToIns['DataInizioItinerario'] = date("Y-m-d", strtotime($ArrPercorso[$nper]['CorsaDataPartenza']));
                    $dataToIns['CorsaInizioItinerario'] = $CorsaId;

                    // Imposto comuni e fermate di partenza/arrivo
                    $dataToIns['ComunePartenza'] = $ArrPercorso[$nper]['ComuneSalita'];
                    $dataToIns['ComuneArrivo'] = $ArrPercorso[$nper]['ComuneDiscesa'];
                    $dataToIns['FermataPartenza'] = $ArrPercorso[$nper]['FermataSalita'];
                    $dataToIns['FermataArrivo'] = $ArrPercorso[$nper]['FermataDiscesa'];

                    // Calcolo date e orari effettivi di partenza/arrivo
                    $fsalitaid = $ArrPercorso[$nper]['FermataSalitaId'];
                    $fdiscesaid = $ArrPercorso[$nper]['FermataDiscesaId'];

                    $sql = "SELECT GiorniAggiuntivi, FermataId FROM RT_Orario WHERE CorsaId=$CorsaId AND FermataId=$fsalitaid";
                    $row22 = $db->query_first($sql);
                    $DataPartenza1 = Date('Y-m-d');
                    if ($row22['FermataId'] > 0) {
                        $dt = new DT($ArrPercorso[$nper]['DataOraSalita'], 'Y-m-d');
                        $dt->addDays(0);
                        $DataPartenza1 = $dt->getDate('Y-m-d');
                    }

                    $sql = "SELECT GiorniAggiuntivi, FermataId FROM RT_Orario WHERE CorsaId=$CorsaId AND FermataId=$fdiscesaid";
                    $row22 = $db->query_first($sql);
                    $DataDiscesa1 = Date('Y-m-d');
                    if ($row22['FermataId'] > 0) {
                        $dt = new DT($ArrPercorso[$nper]['DataOraDiscesa'], 'Y-m-d');
                        $dt->addDays(0);
                        $DataDiscesa1 = $dt->getDate('Y-m-d');
                    }

                    $dataToIns['DataPartenza'] = $DataPartenza1;
                    $dataToIns['OrarioPartenza'] = date("H:i:s", strtotime($ArrPercorso[$nper]['DataOraSalita']));
                    $dataToIns['DataArrivo'] = $DataDiscesa1;
                    $dataToIns['OrarioArrivo'] = date("H:i:s", strtotime($ArrPercorso[$nper]['DataOraDiscesa']));

                    // Recupero prezzo base dalla tabella tariffe
                    $sq = "SELECT PrenotazioneTariffaId, ListinoPrezzo, ConRitorno FROM RT_PrenotazioneTariffa WHERE PrenotazioneId=$PrenotazioneId AND TipologiaBigliettoId=$TipologiaBigliettoId AND Stato=1 AND Cancella=0 ORDER BY ListinoPrezzo DESC";
                    $row22 = $db->query_first($sq);
                    $PrezzoBase = $row22['ListinoPrezzo'];
                    $ConRitorno = $row22['ConRitorno'] + 1;
                    $PrezzoBase = $PrezzoBase / $ConRitorno;
                    $delta = 0;

                    // Calcolo eventuale delta per aumenti/riduzioni
                    if ($Mezzo == 'Bus') {
                        $delta = ($AumentoPax - $RiduzionePax) / $NumeroPax;
                    }
                    if ($TipoViaggio != "Corsa Semplice") {
                        $delta = $delta / 2;
                    }

                    $moltiplicatore = 1;
                    if ($stato == 7) {
                        $moltiplicatore = -1;
                    }

                    // Calcolo importo finale
                    $sql = "SELECT * FROM RT_PrenotazioneBiglietto WHERE PrenotazioneId=$PrenotazioneId AND TipologiaBigliettoId=$TipologiaBigliettoId";
                    $Importi = $db->query_first($sql);
                    $Importo = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
                    if ($TipoViaggioId == 2) {
                        $dataToIns['Importo'] = $Importo / 2;
                    } else {
                        $dataToIns['Importo'] = $Importo;
                    }

                    // Inserimento storico operazione
                    $dataToIns = $storico->operazioni_insert($dataToIns, $user);

                    // Recupero i passeggeri associati al biglietto
                    $sql = "SELECT pn.PrenotazioneNumeroId, pn.PrenotazioneId, TipoBigliettoId, pp.Cognome, pp.Nome, pp.SessoId, pp.Eta
                            FROM RT_PrenotazioneNumero pn
                            LEFT JOIN RT_PrenotazionePasseggeri pp ON (pn.PasseggeroId = pp.PrenotazionePasseggeroId)
                            WHERE pn.PrenotazioneId = $PrenotazioneId AND pn.TipologiaBigliettoId = $TipologiaBigliettoId";
                    $ArrPN = $db->fetch_array($sql);

                    // Inserisco il dettaglio per ogni passeggero
                    foreach ($ArrPN as $PN) {
                        $dataToIns['PrenotazioneNumero'] = $PN['PrenotazioneNumeroId'];
                        $dataToIns['Cognome'] = $PN['Cognome'];
                        $dataToIns['Nome'] = $PN['Nome'];
                        $dataToIns['SessoId'] = $PN['SessoId'];
                        $dataToIns['Eta'] = $PN['Eta'];
                        if (isset($gestore)) {
                            $dataToIns['GestoreIdRef'] = $gestore;
                        }
                        $ins = $db->insert("RT_PrenotazioneDettaglio", $dataToIns);
                    }

                    $ntr++;
                }
                $nper++;
            }
            $tpb++;
        }

        return true;
    }
    public function CreateDettaglioPrenotazioneOld ($CorsaId)
    {
        global $user;
        $db=$this->conn;
        $PrenotazioneId=$this->Id;
        $storico=new StoricoOperazioni();
        $storico->conn=$db;
        $dt=new DT();
        $sql="select * from RT_FoglioCaricoTitoliDiViaggio where OdcIdRef=$user->OdcId and CorsaId=$CorsaId  and PrenotazioneId=$PrenotazioneId";
        //echo($sql);
        $ArrObjectP = $db->fetch_array($sql);
        $numeropasseggeri=sizeof($ArrObjectP);
        $np=0;
        $dettaglio=null;
        
        $sql="select * from RT_AppAliquota where OdcIdRef=$user->OdcId and ValidaDal<=Now() and ValidaAl>=Now() and Stato=1 and Cancella=0";
        
        $row22=$db->query_first($sql);
        $aliquota_b=0;
        $aliquota_p=0;
        if ($row22['AliquotaId']>0)
        {
            
            $aliquota_b=$row22['AliquotaBiglietto'];
            $aliquota_p=$row22['AliquotaProvvigioni'];
            
        }
        
        
        $DNAP='';
        $DNAA='';
        $DNRP='';
        $DNRA='';
        
        
        while ($np<$numeropasseggeri)
        {
            $moltiplicatore=1;
            $PrenStatoId=$ArrObjectP[$np]['PrenStatoId'];
            if ($PrenStatoId==7)
                $moltiplicatore=-1;
                
                
                $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
                $Agenzia=$ArrObjectP[$np]['RagioneSociale'];
                $Percorso=$ArrObjectP[$np]['PercorsoNome'];
                $Salita=$ArrObjectP[$np]['ComuneSalita'];
                $Discesa=$ArrObjectP[$np]['ComuneDiscesa'];
                $TrattaIdSalita=$ArrObjectP[$np]['TrattaIdP'];
                $TrattaIdDiscesa=$ArrObjectP[$np]['TrattaIdD'];
                
                
                $SiglaSalita=$ArrObjectP[$np]['SiglaSalita'];
                $SiglaDiscesa=$ArrObjectP[$np]['SiglaDiscesa'];
                $ClienteNome=$ArrObjectP[$np]['ClienteNome'];
                $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
                $TotalePrenotazione=$ArrObjectP[$np]['TotalePrenotazione'];
                $TipoViaggio=$ArrObjectP[$np]['TipoViaggio'];
                $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
                $TotalePrenotazioneF=  number_format($TotalePrenotazione,2,",","");
                $DataCorsa=$ArrObjectP[$np]['DataCorsa'];
                $CorsaDataPartenza=$ArrObjectP[$np]['CorsaDataPartenza'];
                $OraCorsa=  substr($ArrObjectP[$np]['CorsaOrarioPartenza'],0,5);
                $DataOperazione= $ArrObjectP[$np]['DataOperazione'];
                $CodicePrenotazione= $ArrObjectP[$np]['CodicePrenotazione'];
                $PrenotazioneNumero= $ArrObjectP[$np]['PrenotazioneNumeroId'];
                $LineaIdentificativo= $ArrObjectP[$np]['LineaIdentificativo'];
                $BusNumero= $ArrObjectP[$np]['BusNumero'];
                $FermataSalita=$ArrObjectP[$np]['FermataSalita'];
                $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
                $OraSalita=$ArrObjectP[$np]['DataOraSalitaF'];
                $OraSalitaBus=$ArrObjectP[$np]['DataOraSalitaF'];
                $OraDiscesa=$ArrObjectP[$np]['DataOraDiscesaF'];
                $LineaDa=$ArrObjectP[$np]['LineaDa'];
                $LineaA=$ArrObjectP[$np]['LineaA'];
                $TotalePostiPrenotati=$ArrObjectP[$np]['TotalePostiPrenotati'];
                $TipologiaBigliettoId=$ArrObjectP[$np]['TipologiaBigliettoId'];
                $OrarioPartenza=substr($ArrObjectP[$np]['OrarioPartenza'],0,5);
                $OrarioArrivo=substr($ArrObjectP[$np]['OrarioArrivo'],0,5);
                $NextDay=$ArrObjectP[$np]['NextDay'];
                
                $MezzoSalita=$ArrObjectP[$np]['MezzoSalita'];
                $MezzoDiscesa=$ArrObjectP[$np]['MezzoDiscesa'];
                
                $LineaNome=$ArrObjectP[$np]['LineaNome'];
                $PercorsoNome=$ArrObjectP[$np]['PercorsoNome'];
                $CorsaNome=$ArrObjectP[$np]['CorsaNome'];
                $LineaId=$ArrObjectP[$np]['LineaId'];
                
                
                // percentuale agenzia
                
                $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
                //echo($sql);
                $row=$db->query_first($sql);
                $percentuale_a=0;
                $fisso_a=0;
                if ($row['GestoreConvenzioneId']>0)
                {
                    
                    $percentuale_a=$row['Percentuale'];
                    $fisso_a=$row['Fisso'];
                    
                }
                
                
                
                $PercorsoId=$ArrObjectP[$np]['PercorsoId'];
                
                // prima fermata su tratta bus
                
                $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and MezzoId=2 order by FermataPeso asc limit 1";
                
                $row = $db->query_first($sql);
                $DataRitornoR=" - ";
                $FermataNomeP="";
                $FermataOrarioP="";
                $ComuneNomeP="";
                if ($row['ComuneId']>0)
                {
                    
                    $FermataNomeP=$row['FermataNome'];
                    $FermataOrarioP=($row['Orario']);
                    $ComuneNomeP=$row['Comune'];
                }
                
                // prima fermata su tratta bus
                
                $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and MezzoId=2 order by FermataPeso desc limit 1";
                
                $row = $db->query_first($sql);
                
                $FermataNomeD="";
                $FermataOrarioD="";
                $ComuneNomeD="";
                if ($row['ComuneId']>0)
                {
                    $dt=new DT();
                    $FermataNomeD=$row['FermataNome'];
                    $FermataOrarioD=($row['Orario']);
                    $ComuneNomeD=$row['Comune'];
                }
                
                
                // se salgo sul pulman e scendo con pullman
                
                $CorsaDataPartenza_na='';
                $CorsaDataPartenza_nr='';
                $CorsaDataArrivo_nr='';
                if (($MezzoSalita==2) and ($MezzoDiscesa==2))
                {
                    $ComuneSalitaBus=$Salita;
                    $FermataSalitaBus=$FermataSalita;
                    $OraFermataSalitaBus=$OrarioPartenza;
                    $OraFermataSalitaBus1=substr($OraSalita,-5);
                    $ComuneDiscesaBus=$Discesa;
                    $FermataDiscesaBus=$FermataDiscesa;
                    $OraFermataDiscesaBus=substr($OraDiscesa,-5);
                    
                    $Ca=substr($OraDiscesa,0,10);
                    $CorsaDataArrivo_ba=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                    $CorsaOraArrivo_ba=$OraFermataDiscesaBus;
                    
                    
                    
                }
                // se salgo con la navetta e scendo con il pullman
                elseif(($MezzoSalita==1) and ($MezzoDiscesa==2))
                {
                    $ComuneSalitaBus=$ComuneNomeP;
                    $FermataSalitaBus=$FermataNomeP;
                    $OraFermataSalitaBus=substr($FermataOrarioP,0,5);
                    $OraFermataSalitaBus1=$OraFermataSalitaBus;
                    $ComuneDiscesaBus=$Discesa;
                    $FermataDiscesaBus=$FermataDiscesa;
                    $OraFermataDiscesaBus=substr($OraDiscesa,-5);
                    
                    
                    
                    $Ca=substr($OraDiscesa,0,10);
                    $CorsaDataArrivo_ba=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                    $CorsaOraArrivo_ba=$OraFermataDiscesaBus;
                    
                    
                    $ComuneSalitaNavetta=$Salita;
                    $FermataSalitaNavetta=$FermataSalita;
                    $OraFermataSalitaNavetta=substr($OraSalita,-5);
                    
                    $ComuneDiscesaNavetta=$ComuneSalitaBus;
                    $FermataDiscesaNavetta=$FermataSalitaBus;
                    $OraFermataDiscesaNavetta=$OraFermataSalitaBus;
                    $CorsaDataPartenza_na=$CorsaDataPartenza;
                    
                    
                    $Ca=substr($OraSalita,0,10);
                    $CorsaDataArrivo_na=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                    $CorsaOraArrivo_na=$OraFermataDiscesaNavetta;
                    
                    $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and TrattaId=$TrattaIdSalita and MezzoId=1  order by FermataPeso desc limit 1";
                    
                    $row = $db->query_first($sql);
                    
                    $FermataNomeD="";
                    $FermataOrarioD="";
                    $ComuneNomeD="";
                    if ($row['ComuneId']>0)
                    {
                        $ComuneDiscesaNavetta=$row['Comune'];
                        $FermataDiscesaNavetta=$row['FermataNome'];
                        $CorsaOraArrivo_na=$row['Orario'];
                        
                        
                        
                        
                        
                        
                        
                        
                    }
                    
                    
                    
                    
                    
                }
                // se salgo con il pullman e scendo con la navetta
                elseif(($MezzoSalita==2) and ($MezzoDiscesa==1))
                {
                    $ComuneSalitaBus=$Salita;
                    $FermataSalitaBus=$FermataSalita;
                    $OraFermataSalitaBus=substr($OraSalita,-5);
                    $OraFermataSalitaBus1=substr($OraSalita,-5);
                    
                    
                    $ComuneDiscesaBus=$ComuneNomeD;
                    $FermataDiscesaBus=$FermataNomeD;
                    $OraFermataDiscesaBus=substr($FermataOrarioD,0,5);
                    
                    $CorsaDataArrivo_ba=substr($OraDiscesa,0,10);
                    $CorsaOraArrivo_ba=$OraFermataDiscesaBus;
                    
                    $Ca=substr($OraDiscesa,0,10);
                    $CorsaDataArrivo_ba=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                    $CorsaOraArrivo_ba=$OraFermataDiscesaBus;
                    
                    
                    $sql="select FermataNome,Orario,Comune,ComuneId,GiorniAggiuntivi from RT_ElencoFermataOrario where  TrattaId=$TrattaIdDiscesa and CorsaId=$CorsaId and MezzoId=1  order by FermataPeso asc limit 1";
                    
                    //   echo($sql);
                    $row = $db->query_first($sql);
                    
                    $FermataNomeD="";
                    $FermataOrarioD="";
                    $ComuneNomeD="";
                    if ($row['ComuneId']>0)
                    {
                        $ComuneSalitaNavetta=$row['Comune'];
                        $FermataSalitaNavetta=$row['FermataNome'];
                        $OraFermataSalitaNavetta=$row['Orario'];
                        
                        $ComuneDiscesaNavetta=$Discesa;
                        $FermataDiscesaNavetta=$FermataDiscesa;
                        $OraFermataDiscesaNavetta=$OraDiscesa;
                        $GiorniAggiuntivi=$row['GiorniAggiuntivi'];
                        
                        $Ca=substr($OraDiscesa,0,10);
                        
                        /*$dtp=new DT($Ca,'Y-m-d H:i:s');
                         $dtp->addDays($GiorniAggiuntivi);
                         $Ca=$dtp->getDate();
                         */
                        
                        $CorsaDataArrivo_na=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                        $CorsaDataPartenza_na=$CorsaDataArrivo_na;
                        $CorsaOraArrivo_na=substr($OraDiscesa,-5);
                        
                        
                        
                        
                    }
                    
                    
                    
                }
                $DataInizioItinerario=$CorsaDataPartenza;
                
                
                
                
                $CorsaInizioItinerario=$CorsaId;
                $dettaglio_ba['PrenotazioneId']=$PrenotazioneId;
                $dettaglio_ba['TipoServizio']='Bus';
                $dettaglio_ba['Tragitto']='Andata';
                $dettaglio_ba['ComunePartenza']=$ComuneSalitaBus;
                $dettaglio_ba['FermataPartenza']=$FermataSalitaBus;
                
                $CorsaDataPartenza1=$CorsaDataPartenza;
                if ($MezzoSalita==2)
                {
                    $dt_a=new DT($OraSalitaBus,'d/m/Y H:i');
                    $CorsaDataPartenza1= $dt_a->getDate('Y-m-d');
                }
                
                
                
                $dettaglio_ba['DataPartenza']=$CorsaDataPartenza1;
                
                
                
                $dettaglio_ba['DataInizioItinerario']=$DataInizioItinerario;
                $dettaglio_ba['CorsaInizioItinerario']=$CorsaInizioItinerario;
                
                $dettaglio_ba['OrarioPartenza']=$OraFermataSalitaBus1;
                $dettaglio_ba['ComuneArrivo']=$ComuneDiscesaBus;
                $dettaglio_ba['FermataArrivo']=$FermataDiscesaBus;
                $dettaglio_ba['TipoViaggio']=$TipoViaggio;
                // $dettaglio_ba['OrarioArrivo']=$OraFermataDiscesaBus;
                $dettaglio_ba['LineaId']=$LineaId;
                $dettaglio_ba['PercorsoId']=$PercorsoId;
                $dettaglio_ba['CorsaId']=$CorsaId;
                $dettaglio_ba['LineaNome']=$LineaNome;
                $dettaglio_ba['PercorsoNome']=$PercorsoNome;
                $dettaglio_ba['CorsaNome']=$CorsaNome;
                
                $dettaglio_ba['DataArrivo']=$CorsaDataArrivo_ba;
                $dettaglio_ba['OrarioArrivo']=$CorsaOraArrivo_ba;
                
                
                
                
                
                
                
                if ($NextDay>0)
                    $OrarioArrivo=$OrarioArrivo." (+".$NextDay."gg)";
                    
                    $DataRitorno="";
                    if ($TipoViaggioId==2)
                    {
                        $sql="select CorsaId,CorsaDataPartenza from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and Direzione='R'";
                        $row = $db->query_first($sql);
                        $DataRitornoR=" - ";
                        
                        $CorsaIdRitorno=0;
                        if ($row['CorsaDataPartenza'])
                        {
                            
                            $DataRitorno=$row['CorsaDataPartenza'];
                            $CorsaIdRitorno=$row['CorsaId'];
                        }
                        
                        $sql="select * from RT_ViewElencoPrenotazioniTicketComuni where  OdcIdRef=$user->OdcId and CorsaId=$CorsaIdRitorno and PrenotazioneId=$PrenotazioneId and CorsaDataPartenza='$DataRitorno'";
                        //     echo($sql);
                        $row_r=$db->query_first($sql);
                        
                        $OraCorsaR=  substr($row_r['CorsaOrarioPartenza'],0,5);
                        $DataCorsaR=$row_r['DataCorsa'];
                        $CorsaDataPartenzaR=$row_r['CorsaDataPartenza'];
                        $SalitaR=$row_r['ComuneSalita'];
                        $DiscesaR=$row_r['ComuneDiscesa'];
                        $SiglaSalitaR=$row_r['SiglaProvinciaSR'];
                        $SiglaDiscesaR=$row_r['SiglaProvinciaDR'];
                        $PrenotazioneNumeroR= $row_r['PrenotazioneNumeroId'];
                        
                        $FermataSalitaR=$row_r['FermataSalita'];
                        $FermataDiscesaR=$row_r['FermataDiscesa'];
                        $OraSalitaR=$row_r['DataOraSalitaF'];
                        $OraDiscesaR=$row_r['DataOraDiscesaF'];
                        $LineaDaR=$row_r['LineaDa'];
                        $LineaAR=$row_r['LineaA'];
                        
                        $OrarioPartenzaR=substr($row_r['OrarioPartenza'],0,5);
                        $OrarioArrivoR=substr($row_r['OrarioArrivo'],0,5);
                        $NextDayR=$row_r['NextDay'];
                        
                        
                        $MezzoSalitaR=$row_r['MezzoSalita'];
                        $MezzoDiscesaR=$row_r['MezzoDiscesa'];
                        $LineaNomeR=$row_r['LineaNome'];
                        $PercorsoNomeR=$row_r['PercorsoNome'];
                        $CorsaNomeR=$row_r['CorsaNome'];
                        $LineaIdR=$row_r['LineaId'];
                        $PercorsoIdR=$row_r['PercorsoId'];
                        
                        $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaIdRitorno and MezzoId=2 order by FermataPeso desc limit 1";
                        
                        $row = $db->query_first($sql);
                        
                        $FermataNomePR="";
                        $FermataOrarioPR="";
                        $ComuneNomePR="";
                        if ($row['ComuneId']>0)
                        {
                            
                            $FermataNomePR=$row['FermataNome'];
                            $FermataOrarioPR=($row['Orario']);
                            $ComuneNomePR=$row['Comune'];
                        }
                        
                        $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaIdRitorno and MezzoId=2 order by FermataPeso asc limit 1";
                        
                        $row = $db->query_first($sql);
                        
                        $FermataNomeDR="";
                        $FermataOrarioDR="";
                        $ComuneNomeDR="";
                        if ($row['ComuneId']>0)
                        {
                            $dt=new DT();
                            $FermataNomeDR=$row['FermataNome'];
                            $FermataOrarioDR=($row['Orario']);
                            $ComuneNomeDR=$row['Comune'];
                        }
                        
                        
                        if (($MezzoSalitaR==2) and ($MezzoDiscesaR==2))
                        {
                            $ComuneSalitaBusR=$SalitaR;
                            $FermataSalitaBusR=$FermataSalitaR;
                            $OraFermataSalitaBusR=substr($OraSalitaR,-5);
                            
                            $ComuneDiscesaBusR=$DiscesaR;
                            $FermataDiscesaBusR=$FermataDiscesaR;
                            $OraFermataDiscesaBusR=substr($OraDiscesaR,-5);
                            
                            $Ca=substr($OraDiscesaR,0,10);
                            $CorsaDataArrivo_br=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                            $CorsaOraArrivo_br=$OraFermataDiscesaBusR;
                            
                            
                        }
                        // se salgo con la navetta e scendo con il pullman
                        elseif(($MezzoSalitaR==1) and ($MezzoDiscesaR==2))
                        {
                            
                            $ComuneSalitaBusR=$ComuneNomeDR;
                            $FermataSalitaBusR=$FermataNomeDR;
                            $OraFermataSalitaBusR=substr($FermataOrarioDR,0,5);
                            
                            $ComuneDiscesaBusR=$DiscesaR;
                            $FermataDiscesaBusR=$FermataDiscesaR;
                            $OraFermataDiscesaBusR=$OrarioArrivoR;
                            
                            
                            $Ca=substr($OraDiscesaR,0,10);
                            $CorsaDataArrivo_br=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                            $CorsaOraArrivo_br=$OraFermataDiscesaBusR;
                            
                            
                            
                            $sql="select FermataNome,Orario,Comune,ComuneId,GiorniAggiuntivi from RT_ElencoFermataOrario where CorsaId=$CorsaIdRitorno and MezzoId=1  order by FermataPeso desc limit 1";
                            //   echo($sql);
                            $row = $db->query_first($sql);
                            
                            $FermataNomeD="";
                            $FermataOrarioD="";
                            $ComuneNomeD="";
                            if ($row['ComuneId']>0)
                            {
                                $GA=$row['GiorniAggiuntivi'];
                                $ComuneDiscesaNavettaR=$row['Comune'];
                                $FermataDiscesaNavettaR=$row['FermataNome'];
                                $OraFermataDiscesaNavettaR=substr($row['Orario'],0,5);
                                
                                $ComuneSalitaNavettaR=$SalitaR;
                                $FermataSalitaNavettaR=$FermataSalitaR;
                                $OraFermataSalitaNavettaR=substr($OraSalitaR,-5);
                                
                                $Ca=substr($OraSalitaR,0,10);
                                
                                
                                $CorsaDataArrivo_nr=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                                $CorsaOraArrivo_nr=$OraFermataDiscesaNavettaR;
                                $CorsaDataPartenza_nr=$CorsaDataPartenzaR;
                                $CorsaDataArrivo_nr=$CorsaDataPartenza_nr;
                                
                            }
                            
                            
                        }
                        // se salgo con il pullman e scendo con la navetta
                        elseif(($MezzoSalitaR==2) and ($MezzoDiscesaR==1))
                        {
                            $ComuneSalitaBusR=$SalitaR;
                            $FermataSalitaBusR=$FermataSalitaR;
                            $OraFermataSalitaBusR=substr($OraSalitaR,-5);
                            
                            $ComuneDiscesaBusR=$ComuneNomePR;
                            $FermataDiscesaBusR=$FermataNomePR;
                            $OraFermataDiscesaBusR=substr($FermataOrarioPR,0,5);
                            
                            $Ca=substr($OraDiscesaR,0,10);
                            $CorsaDataArrivo_br=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                            $CorsaOraArrivo_br=$OraFermataDiscesaBusR;
                            
                            
                            $ComuneDiscesaNavettaR=$DiscesaR;
                            $FermataDiscesaNavettaR=$FermataDiscesaR;
                            $OraFermataDiscesaNavettaR="il ".$OraDiscesaR;
                            
                            $ComuneSalitaNavettaR=$ComuneDiscesaBusR;
                            $FermataSalitaNavettaR=$FermataDiscesaBusR;
                            $OraFermataSalitaNavettaR=$OraFermataDiscesaBusR;
                            
                            
                            $Ca=substr($OraDiscesaR,0,10);
                            $CorsaDataArrivo_nr=substr($Ca,-4)."-".substr($Ca,3,2)."-".substr($Ca,0,2);
                            $CorsaOraArrivo_nr=substr($OraDiscesaR,-5);
                            $CorsaDataPartenza_nr=$CorsaDataArrivo_nr;
                            $CorsaDataArrivo_nr=$CorsaDataPartenza_nr;
                            
                        }
                        if ($NextDayR>0)
                            $OrarioArrivo=$OrarioArrivo." (+".$NextDayR."gg)";
                            
                            
                            
                            
                            $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaIdR";
                            
                            $row=$db->query_first($sql);
                            $percentuale_r=0;
                            $fisso_r=0;
                            if ($row['GestoreConvenzioneId']>0)
                            {
                                
                                $percentuale_r=$row['Percentuale'];
                                $fisso_r=$row['Fisso'];
                                
                            }
                            
                            
                            $CorsaDataPartenzaR1=$CorsaDataPartenzaR;
                            if ($MezzoSalita==2)
                            {
                                $dt_a=new DT($OraSalitaR,'d/m/Y H:i');
                                $CorsaDataPartenzaR1= $dt_a->getDate('Y-m-d');
                            }
                            
                            
                            $dettaglio_br['PrenotazioneId']=$PrenotazioneId;
                            $dettaglio_br['TipoServizio']='Bus';
                            $dettaglio_br['TipoViaggio']=$TipoViaggio;
                            $dettaglio_br['Tragitto']='Ritorno';
                            $dettaglio_br['ComunePartenza']=$ComuneSalitaBusR;
                            $dettaglio_br['FermataPartenza']=$FermataSalitaBusR;
                            $dettaglio_br['DataPartenza']=$CorsaDataPartenzaR1;
                            $dettaglio_br['OrarioPartenza']=$OraFermataSalitaBusR;
                            $dettaglio_br['ComuneArrivo']=$ComuneDiscesaBusR;
                            $dettaglio_br['FermataArrivo']=$FermataDiscesaBusR;
                            $dettaglio_br['DataInizioItinerario']=$DataRitorno;
                            $dettaglio_br['CorsaInizioItinerario']=$CorsaIdRitorno;
                            
                            $dettaglio_br['LineaId']=$LineaIdR;
                            $dettaglio_br['PercorsoId']=$PercorsoIdR;
                            $dettaglio_br['CorsaId']=$CorsaIdRitorno;
                            
                            $dettaglio_br['LineaNome']=$LineaNomeR;
                            $dettaglio_br['PercorsoNome']=$PercorsoNomeR;
                            $dettaglio_br['CorsaNome']=$CorsaNomeR;
                            $dettaglio_br['DataArrivo']=$CorsaDataArrivo_br;
                            $dettaglio_br['OrarioArrivo']=$CorsaOraArrivo_br;
                            $dettaglio_br['PrenotazioneNumero']=$PrenotazioneNumero;
                            
                            $dettaglio_br['AliquotaBiglietto']=$aliquota_b;
                            $dettaglio_br['AliquotaProvvigione']=$aliquota_p;
                            
                            
                            
                            
                    }
                    
                    
                    
                    // calcolo l'importo
                    
                    $sql="select * from RT_PrenotazioneTotalePerServizioPax where  PrenotazioneId=$PrenotazioneId and AppMezzo='Bus' and TipologiaBigliettoId=$TipologiaBigliettoId";
                    
                    $row_i=$db->query_first($sql);
                    $TotBus=0;
                    $TotBusF="0";
                    $TotalePostiPrenotati=1;
                    
                    $NumeroPax=1;
                    $AumentoPax=0;
                    $RiduzionePax=0;
                    $TipologiaBiglietto="";
                    if (!empty($row_i['TotalePerTipologia'])){
                        $TotBus=$TotBus+$row_i['TotalePerTipologia']/$TotalePostiPrenotati;
                        
                        $NumeroPax=$row_i['NumeroPax'];
                        $RiduzionePax=$row_i['RiduzionePax'];
                        $AumentoPax=$row_i['AumentoPax'];
                        $TipologiaBiglietto=$row_i['TipologiaBiglietto'];
                        
                        if (!($ComuneSalitaNavetta<>""))
                        {
                            $delta1=($AumentoPax-$RiduzionePax)/$NumeroPax;
                            $TotBus=$TotBus+$delta1;
                        }
                        
                    }
                    
                    $sql="select TotalePerTipologia from RT_PrenotazioneTotalePerServizioPax where  PrenotazioneId=$PrenotazioneId and AppMezzo='Navetta' and TipologiaBigliettoId=$TipologiaBigliettoId";
                    $row_i=$db->query_first($sql);
                    $TotNav=0;
                    $TotNavF="0";
                    $TotalePostiPrenotati=1;
                    
                    if (!empty($row_i['TotalePerTipologia'])){
                        $TotNav=($row_i['TotalePerTipologia']/$TotalePostiPrenotati)*($moltiplicatore);
                        $TotNavF=number_format($TotNav,2,",","");
                        
                    }
                    
                    
                    
                    $delta=($AumentoPax-$RiduzionePax)/$NumeroPax;
                    $TotNav=($TotNav+$delta)*($moltiplicatore);
                    $TotBusF=number_format($TotBus,2,",","");
                    $TotaleBiglietto=$TotBus+$TotNav;
                    
                    
                    if ($TipoViaggioId==2)
                    {
                        $TotBus=$TotBus/2;
                        $TotNav=$TotNav/2;
                        $dettaglio_br['Importo']=$TotBus;
                        $dettaglio_br['TipologiaBiglietto']=$TipologiaBiglietto;
                        /*$DaRisonoscereAgenziaR=($TotBus*$percentuale_r/100)+$fisso_r;
                         $dettaglio_br['ImportoAgenzia']=$DaRisonoscereAgenziaR;*/
                        
                        $arr=$this->ImportiPrenotazione($TotBus,$percentuale_r,$fisso_r,$aliquota_b,$aliquota_p);
                        $dettaglio_br['ImportoAgenzia']=$arr[0];
                        $dettaglio_br['DaBonificare']=$arr[1];
                        $dettaglio_br['DaFatturare']=$arr[2];
                        
                        $dettaglio_br['PercentualeAgenzia']=$percentuale_r;
                        $dettaglio_br['FissoAgenzia']=$fisso_r;
                    }
                    $dettaglio_ba['Importo']=$TotBus;
                    $dettaglio_ba['TipologiaBiglietto']=$TipologiaBiglietto;
                    $dettaglio_ba['PrenotazioneNumero']=$PrenotazioneNumero;
                    
                    $dettaglio_ba['AliquotaBiglietto']=$aliquota_b;
                    $dettaglio_ba['AliquotaProvvigione']=$aliquota_p;
                    
                    
                    /*$TotBus1=$TotBus*($aliquota_b/100+1);
                     $DaRisonoscereAgenziaA=($TotBus1*$percentuale_a/100)+$fisso_a;
                     $DaRisonoscereAgenziaA=  number_format($DaRisonoscereAgenziaA,2);*/
                    
                    //   echo($DaRisonoscereAgenziaA);
                    $arr=$this->ImportiPrenotazione($TotBus,$percentuale_a,$fisso_a,$aliquota_b,$aliquota_p);
                    $dettaglio_ba['ImportoAgenzia']=$arr[0];
                    $dettaglio_ba['DaBonificare']=$arr[1];
                    $dettaglio_ba['DaFatturare']=$arr[2];
                    
                    $dettaglio_ba['PercentualeAgenzia']=$percentuale_a;
                    $dettaglio_ba['FissoAgenzia']=$fisso_a;
                    
                    if ($tipo_titolo=='R')
                        $TotaleBiglietto=$TotaleBiglietto*(-1);
                        
                        $TotaleBigliettoF=number_format($TotaleBiglietto,2,",","");
                        
                        
                        
                        $DataEticket=$ArrObjectP[$np]['DataEticket'];
                        $AnnoEticket=$ArrObjectP[$np]['Anno'];
                        $Eticket=$ArrObjectP[$np]['Eticket'];
                        
                        $CodiceBiglietto=$Eticket."/".$AnnoEticket;
                        $DataTitolo=$DataEticket;
                        
                        $dettaglio_ba=$storico->operazioni_insert($dettaglio_ba, $user);
                        $idins=$db->insert("RT_PrenotazioneDettaglio",$dettaglio_ba);
                        //  print_r($dettaglio_ba);
                        
                        if ($ComuneSalitaNavetta<>"")
                        {
                            
                            $dettaglio_na['PrenotazioneId']=$PrenotazioneId;
                            $dettaglio_na['TipoServizio']='Navetta';
                            $dettaglio_na['Tragitto']='Andata';
                            $dettaglio_na['ComunePartenza']=$ComuneSalitaNavetta;
                            $dettaglio_na['FermataPartenza']=$FermataSalitaNavetta;
                            $dettaglio_na['DataPartenza']=$CorsaDataPartenza_na;
                            $dettaglio_na['OrarioPartenza']=$OraFermataSalitaNavetta;
                            $dettaglio_na['ComuneArrivo']=$ComuneDiscesaNavetta;
                            $dettaglio_na['FermataArrivo']=$FermataDiscesaNavetta;
                            $dettaglio_na['TipoViaggio']=$TipoViaggio;
                            $dettaglio_na['LineaId']=$LineaId;
                            $dettaglio_na['PercorsoId']=$PercorsoId;
                            $dettaglio_na['CorsaId']=$CorsaId;
                            $dettaglio_na['LineaNome']=$LineaNome;
                            $dettaglio_na['PercorsoNome']=$PercorsoNome;
                            $dettaglio_na['CorsaNome']=$CorsaNome;
                            $dettaglio_na['TipologiaBiglietto']=$TipologiaBiglietto;
                            $dettaglio_na['Importo']=$TotNav;
                            $dettaglio_na['PrenotazioneNumero']=$PrenotazioneNumero;
                            $dettaglio_na['DataInizioItinerario']=$DataInizioItinerario;
                            $dettaglio_na['CorsaInizioItinerario']=$CorsaInizioItinerario;
                            
                            // $DaRisonoscereAgenziaNA=($TotNav*$percentuale_a/100)+$fisso_a;
                            // $dettaglio_na['ImportoAgenzia']=$DaRisonoscereAgenziaNA;
                            
                            $arr=$this->ImportiPrenotazione($TotNav,$percentuale_a,$fisso_a,$aliquota_b,$aliquota_p);
                            $dettaglio_na['ImportoAgenzia']=$arr[0];
                            $dettaglio_na['DaBonificare']=$arr[1];
                            $dettaglio_na['DaFatturare']=$arr[2];
                            
                            
                            $dettaglio_na['PercentualeAgenzia']=$percentuale_a;
                            $dettaglio_na['FissoAgenzia']=$fisso_a;
                            
                            $dettaglio_na['DataArrivo']=$CorsaDataArrivo_na;
                            $dettaglio_na['OrarioArrivo']=$CorsaOraArrivo_na;
                            
                            $dettaglio_na['AliquotaBiglietto']=$aliquota_b;
                            $dettaglio_na['AliquotaProvvigione']=$aliquota_p;
                            
                            $dettaglio_na=$storico->operazioni_insert($dettaglio_na, $user);
                            $idins=$db->insert("RT_PrenotazioneDettaglio",$dettaglio_na);
                            
                            //print_r($dettaglio_na);
                            
                        }
                        
                        if ($TipoViaggioId==2)
                        {
                            $dettaglio_br=$storico->operazioni_insert($dettaglio_br, $user);
                            $idins=$db->insert("RT_PrenotazioneDettaglio",$dettaglio_br);
                            //    print_r($dettaglio_br);
                            
                            if ($ComuneSalitaNavettaR<>"")
                            {
                                $dettaglio_nr['PrenotazioneId']=$PrenotazioneId;
                                $dettaglio_nr['TipoServizio']='Navetta';
                                $dettaglio_nr['Tragitto']='Ritorno';
                                $dettaglio_nr['ComunePartenza']=$ComuneSalitaNavettaR;
                                $dettaglio_nr['FermataPartenza']=$FermataSalitaNavettaR;
                                $dettaglio_nr['DataPartenza']=$CorsaDataPartenza_nr;
                                $dettaglio_nr['OrarioPartenza']=$OraFermataSalitaNavettaR;
                                $dettaglio_nr['ComuneArrivo']=$ComuneDiscesaNavettaR;
                                $dettaglio_nr['FermataArrivo']=$FermataDiscesaNavettaR;
                                $dettaglio_nr['TipoViaggio']=$TipoViaggio;
                                $dettaglio_nr['LineaId']=$LineaIdR;
                                $dettaglio_nr['PercorsoId']=$PercorsoIdR;
                                $dettaglio_nr['CorsaId']=$CorsaIdRitorno;
                                $dettaglio_nr['LineaNome']=$LineaNomeR;
                                $dettaglio_nr['PercorsoNome']=$PercorsoNomeR;
                                $dettaglio_nr['CorsaNome']=$CorsaNomeR;
                                $dettaglio_nr['TipologiaBiglietto']=$TipologiaBiglietto;
                                $dettaglio_nr['Importo']=$TotNav;
                                $dettaglio_nr['DataArrivo']=$CorsaDataArrivo_nr;
                                $dettaglio_nr['OrarioArrivo']=$CorsaOraArrivo_nr;
                                $dettaglio_nr['PrenotazioneNumero']=$PrenotazioneNumero;
                                $dettaglio_nr['DataInizioItinerario']=$DataRitorno;
                                $dettaglio_nr['CorsaInizioItinerario']=$CorsaIdRitorno;
                                
                                /*$DaRisonoscereAgenziaRN=($TotNav*$percentuale_r/100)+$fisso_r;
                                 $dettaglio_nr['ImportoAgenzia']=$DaRisonoscereAgenziaRN;*/
                                
                                $arr=$this->ImportiPrenotazione($TotNav,$percentuale_r,$fisso_r,$aliquota_b,$aliquota_p);
                                $dettaglio_nr['ImportoAgenzia']=$arr[0];
                                $dettaglio_nr['DaBonificare']=$arr[1];
                                $dettaglio_nr['DaFatturare']=$arr[2];
                                
                                
                                $dettaglio_nr['PercentualeAgenzia']=$percentuale_r;
                                $dettaglio_nr['FissoAgenzia']=$fisso_r;
                                $dettaglio_nr['AliquotaBiglietto']=$aliquota_b;
                                $dettaglio_nr['AliquotaProvvigione']=$aliquota_p;
                                
                                $dettaglio_nr=$storico->operazioni_insert($dettaglio_nr, $user);
                                $idins=$db->insert("RT_PrenotazioneDettaglio",$dettaglio_nr);
                                //print_r($dettaglio_nr);
                                
                            }
                            
                        }
                        
                        
                        
                        $np++;
        }
        
        return true;
    }
    
    public function ImportiPrenotazione($Venduto,$PercAge,$FissoAge,$PercB,$PercP)
    {
        $ImportoBase=  number_format($Venduto/($PercB/100+1),4);
        $ImportoAgenziaNetto=number_format($ImportoBase*($PercAge/100)+$FissoAge,4);
        $DaFatturare=number_format($ImportoAgenziaNetto*($PercP/100+1),4);
        $DaBonificare=number_format($Venduto-$DaFatturare,4);
        
        
        // print($Venduto);
        
        $arr[0]=$ImportoAgenziaNetto;
        $arr[1]=$DaBonificare;
        $arr[2]=$DaFatturare;
        
        
        
        
        return $arr;
        
    }
    
    public function GeneraCorrispettivo($PrenotazioneTitoloId,$CorsaId,$tipo_titolo)
    {
        global $user;
        $db=$this->conn;
        $PrenotazioneId=$this->Id;
        $arr_corrispettivo=null;
        $sql="select * from RT_FoglioCaricoTitoliDiViaggio where TipoTitolo='E' and  CorsaId=$CorsaId and PrenotazioneTitoloId=$PrenotazioneTitoloId and OdcIdRef=$user->OdcId ";
        
        
        
        $ArrObjectP = $db->fetch_array($sql);
        $numeropasseggeri=sizeof($ArrObjectP);
        $np=0;
        while ($np<$numeropasseggeri)
        {
            $PrenotazioneId=$ArrObjectP[$np]['PrenotazioneId'];
            $Agenzia=$ArrObjectP[$np]['RagioneSociale'];
            $Percorso=$ArrObjectP[$np]['PercorsoNome'];
            $Salita=$ArrObjectP[$np]['ComuneSalita'];
            $Discesa=$ArrObjectP[$np]['ComuneDiscesa'];
            $SiglaSalita=$ArrObjectP[$np]['SiglaSalita'];
            $SiglaDiscesa=$ArrObjectP[$np]['SiglaDiscesa'];
            $ClienteNome=$ArrObjectP[$np]['ClienteNome'];
            $ClienteCellulare=$ArrObjectP[$np]['ClienteCellulare'];
            $TotalePrenotazione=$ArrObjectP[$np]['TotalePrenotazione'];
            $TipoViaggio=$ArrObjectP[$np]['TipoViaggio'];
            $TipoViaggioId=$ArrObjectP[$np]['TipoViaggioId'];
            $TotalePrenotazioneF=  number_format($TotalePrenotazione,2,",","");
            $DataCorsa=$ArrObjectP[$np]['DataCorsa'];
            $OraCorsa=  substr($ArrObjectP[$np]['CorsaOrarioPartenza'],0,5);
            $DataOperazione= $ArrObjectP[$np]['DataOperazione'];
            $CodicePrenotazione= $ArrObjectP[$np]['CodicePrenotazione'];
            $PrenotazioneNumero= $ArrObjectP[$np]['PrenotazioneNumeroId'];
            $LineaIdentificativo= $ArrObjectP[$np]['LineaIdentificativo'];
            $BusNumero= $ArrObjectP[$np]['BusNumero'];
            $FermataSalita=$ArrObjectP[$np]['FermataSalita'];
            $FermataDiscesa=$ArrObjectP[$np]['FermataDiscesa'];
            $OraSalita=$ArrObjectP[$np]['DataOraSalitaF'];
            $OraDiscesa=$ArrObjectP[$np]['DataOraDiscesaF'];
            $LineaDa=$ArrObjectP[$np]['LineaDa'];
            $LineaA=$ArrObjectP[$np]['LineaA'];
            $TotalePostiPrenotati=$ArrObjectP[$np]['TotalePostiPrenotati'];
            $TipologiaBigliettoId=$ArrObjectP[$np]['TipologiaBigliettoId'];
            $OrarioPartenza=substr($ArrObjectP[$np]['OrarioPartenza'],0,5);
            $OrarioArrivo=substr($ArrObjectP[$np]['OrarioArrivo'],0,5);
            $NextDay=$ArrObjectP[$np]['NextDay'];
            
            $MezzoSalita=$ArrObjectP[$np]['MezzoSalita'];
            $MezzoDiscesa=$ArrObjectP[$np]['MezzoDiscesa'];
            
            
            // prima fermata su tratta bus
            
            $sql="select FermataNome,Orario,Comune,ComuneId from RT_ElencoFermataOrario where CorsaId=$CorsaId and MezzoId=2 order by FermataPeso asc limit 1";
            
            $row = $db->query_first($sql);
            $DataRitornoR=" - ";
            $FermataNomeA="";
            $FermataOrarioA="";
            $ComuneNomeA="";
            if ($row['ComuneId']>0)
            {
                $dt=new DT();
                $FermataNomeA=$row['FermataNome'];
                $FermataOrarioA=($row['Orario']);
                $ComuneNomeA=$row['Comune'];
            }
            
            
            if ($MezzoSalita==2) // bus
            {
                $OrarioPartenza=$OraSalita;
                $LineaDa=$Salita;
                
                $OrarioArrivo=$OraDiscesa;
                $LineaA=$Discesa;
                $FermataPartenza=$FermataSalita;
            }
            else
            {
                
                $OrarioPartenza=substr($FermataOrarioA,0,5);
                $LineaDa=$ComuneNomeA;
                $FermataPartenza=$FermataNomeA;
                
            }
            
            
            
            
            if ($NextDay>0)
                $OrarioArrivo=$OrarioArrivo." (+".$NextDay."gg)";
                 
                 $sql="select * from RT_PrenotazioneTotalePerServizioPax where  PrenotazioneId=$PrenotazioneId and AppMezzo='Bus' and TipologiaBigliettoId=$TipologiaBigliettoId";
                 
                 $row_i=$db->query_first($sql);
                 $TotBus=0;
                 $TotBusF="0";
                 $TotalePostiPrenotati=1;
                 
                 $NumeroPax=1;
                 $AumentoPax=0;
                 $RiduzionePax=0;
                 $TipologiaBiglietto="";
                 if (!empty($row_i['TotalePerTipologia'])){
                     $TotBus=$TotBus+$row_i['TotalePerTipologia']/$TotalePostiPrenotati;
                     
                     $NumeroPax=$row_i['NumeroPax'];
                     $RiduzionePax=$row_i['RiduzionePax'];
                     $AumentoPax=$row_i['AumentoPax'];
                     $TipologiaBiglietto=$row_i['TipologiaBiglietto'];
                 }
                 
                 $sql="select TotalePerTipologia from RT_PrenotazioneTotalePerServizioPax where  PrenotazioneId=$PrenotazioneId and AppMezzo='Navetta' and TipologiaBigliettoId=$TipologiaBigliettoId";
                 $row_i=$db->query_first($sql);
                 $TotNav=0;
                 $TotNavF="0";
                 $TotalePostiPrenotati=1;
                 
                 if (!empty($row_i['TotalePerTipologia'])){
                     $TotNav=$row_i['TotalePerTipologia']/$TotalePostiPrenotati;
                     $TotNavF=number_format($TotNav,2,",","");
                     
                 }
                 
                 
                 
                 $delta=($AumentoPax-$RiduzionePax)/$NumeroPax;
                 $TotNav=$TotNav+$delta;
                 $TotBusF=number_format($TotBus,2,",","");
                 $TotaleBiglietto=$TotBus+$TotNav;
                 
                 if ($tipo_titolo=='R')
                     $TotaleBiglietto=$TotaleBiglietto*(-1);
                     
                     $TotaleBigliettoF=number_format($TotaleBiglietto,2,",","");
                     
                     
                     
                     $DataEticket=$ArrObjectP[$np]['DataEticket'];
                     $AnnoEticket=$ArrObjectP[$np]['Anno'];
                     $Eticket=$ArrObjectP[$np]['Eticket'];
                     
                     $CodiceBiglietto=$Eticket."/".$AnnoEticket;
                     $DataTitolo=$DataEticket;
                     
                     $arr_corrispettivo[0]['TipoServizio']='Bus';
                     $arr_corrispettivo[0]['Importo']=$TotBus;
                     $arr_corrispettivo[0]['Partenza']=$LineaDa;
                     $arr_corrispettivo[0]['Destinazione']=$Discesa;
                     $arr_corrispettivo[0]['TipoViaggioId']=$TipoViaggioId;
                     $arr_corrispettivo[0]['Percorso']=$Percorso;
                     $arr_corrispettivo[0]['Tratta']=$TrattaBus;
                     $arr_corrispettivo[0]['TipologiaBiglietto']=$TipologiaBiglietto;
                     
                     if ($TotNav<>0)
                     {
                         $arr_corrispettivo[1]['TipoServizio']='Navetta';
                         $arr_corrispettivo[1]['Importo']=$TotNav;
                         
                         
                         
                         
                         $arr_corrispettivo[0]['TipoViaggioId']=$TipoViaggioId;
                         $arr_corrispettivo[0]['Percorso']=$Percorso;
                         $arr_corrispettivo[0]['Tratta']=$TrattaNavetta;
                         $arr_corrispettivo[0]['TipologiaBiglietto']=$TipologiaBiglietto;
                     }
                     
                     
                     
                     $np++;
        }
        
        return $arr_corrispettivo;
    }
    
     
     public function EmettiBigliettiRimborso($PrenotazioneNumeroId, $ImportoRimborso) {
         global $user;
         
         $db = $this->conn;
         
         $PrenotazioneId = $this->Id;

         //recupero la prenotazione
         $sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
         $prenotazione = $db->query_first($sql);
         
         //recupero il gestore che ha emesso la prenotazione
         $gestoreIdRef = $prenotazione['GestoreIdRef'];
         
         $storico = new StoricoOperazioni();
         $storico->conn = $db;
         
         $sql="Select PercorsoId,LineaId from RT_PrenotazionePercorso where Stato=1 AND Cancella=0 AND PrenotazioneId=$PrenotazioneId";
         $row = $db->query_first($sql);
         $PercorsoId = $row['PercorsoId'];
         $LineaId = $row['LineaId'];
         
         $sql="Select gp.IdentificativoBiglietto from Gestore g LEFT JOIN GestoreGruppo gp ON (g.GestoreGruppoId = gp.GestoreGruppoId) where GestoreId = $gestoreIdRef";
         $gestore = $db->query_first($sql);
         $IdentificativoBiglietto = $gestore['IdentificativoBiglietto'];
         if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
             $IdentificativoBiglietto = Config::$identificativoBiglietto;
         }
         
         $sql="Select TipoViaggioId from RT_Prenotazione where PrenotazioneId=$PrenotazioneId";
         $viaggio = $db->query_first($sql);
         $TipoViaggioId=$viaggio['TipoViaggioId'];
         
         /*recupero l'ultimo movimento per verificare l'importo venduto, diverso dall'importo del titolo */
         $sql = "SELECT * FROM RT_PrenotazioneMovimento where PrenotazioneId = $PrenotazioneId AND TipoMovimento = 'R' order by DataIns DESC, DataAgg DESC";
         $tempMovimento = $db->query_first($sql);
         if($tempMovimento['PagamentoTipoId'] == 12){
             $isCoupon = true;
         } else {
             $isCoupon = false;
         }
         
         $sql="Select * from RT_PrenotazioneNumero where PrenotazioneNumeroId=$PrenotazioneNumeroId and PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0 order by PrenotazioneNumeroId asc";
         $prenotazioneNumero = $db->fetch_array($sql);
         $codice = '';
         foreach ($prenotazioneNumero as $numero) {
             
             $TipologiaBigliettoId = $numero['TipologiaBigliettoId'];
             
             $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId = $gestoreIdRef and BigliettoId = $TipologiaBigliettoId and LineaId = $LineaId";
             $row=$db->query_first($sql);
             $percentuale_a=0;
             $fisso_a=0;
             if ($row['GestoreConvenzioneId']>0)
             {
                 $percentuale_a=$row['Percentuale'];
                 $fisso_a=$row['Fisso'];
                 
             }
             
             
             $sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = $TipologiaBigliettoId";
             $tempTipo = $db->query_first($sql);
             if($tempTipo['OccupaPosto'] == 1){
                 $progressivo = $this->GetProgressivoTitoloDiViaggio();
                 $identificativoServizio = "";
             } else {
                 $progressivo = $this->GetProgressivoTitoloDiViaggioServizi();
                 $identificativoServizio = "E-";
             }
             $codice = $identificativoServizio.$IdentificativoBiglietto . '-' . Str_pad($progressivo, 8, "0", STR_PAD_LEFT);
             
             $titolo = array();
             $titolo['PrenotazioneId'] = $PrenotazioneId;
             $titolo['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
             $titolo['Codice'] = $codice;
             $titolo['Anno'] = date('Y');
             $titolo['Progressivo'] = $progressivo;
             $titolo['TipoTitolo'] = "R";
             $titolo['PercorsoId'] = $PercorsoId;
             
             $sql = "SELECT pn.PrenotazioneNumeroId, pn.PasseggeroId, pp.Cognome, pp.Nome, pp.SessoId, pp.Eta, pn.TipologiaBigliettoId, tb.TipologiaBiglietto
				FROM RT_PrenotazioneNumero pn
				LEFT JOIN RT_PrenotazionePasseggeri pp ON (pn.PasseggeroId = pp.PrenotazionePasseggeroId)
				LEFT jOIN RT_TipologiaBiglietto tb ON (tb.TipologiaBigliettoId = pp.TipoBigliettoId)
				WHERE pn.PrenotazioneNumeroId = $PrenotazioneNumeroId";
             
             $datiPasseggero = $db->query_first($sql);
             $titolo['PasseggeroId'] = $datiPasseggero['PasseggeroId'];
             $titolo['Cognome'] = $datiPasseggero['Cognome'];
             $titolo['Nome'] = $datiPasseggero['Nome'];
             $titolo['SessoId'] = $datiPasseggero['SessoId'];
             $titolo['Eta'] = $datiPasseggero['Eta'];
             $titolo['TipologiaBigliettoId'] = $datiPasseggero['TipologiaBigliettoId'];
             $titolo['TipologiaBiglietto'] = $datiPasseggero['TipologiaBiglietto'];
             
             $titolo['ImportoTitolo'] = $ImportoRimborso;
             if($isCoupon){
                 $titolo['ImportoVenduto'] = 0;
             } else {
                 $titolo['ImportoVenduto'] = $ImportoRimborso;
             }
             $titolo = $storico->operazioni_insert($titolo, $user);
             $titolo['GestoreIdRef'] = $gestoreIdRef;
             $titoloId = $db->insert("RT_PrenotazioneTitolo", $titolo);
             
             /*inserimento IVA*/
             $sql="select * from RT_ViewImportiPerIva003 where PrenotazioneTitoloId=$titoloId";
             $ivaarr = $db->fetch_array($sql);
             if(count($ivaarr) > 0) {
                 foreach ($ivaarr as $arr_iva) {
                     $Scorporo=0;
                     $ConfineId=0;
                     $Aliquota=0;
                     $Importo=$arr_iva['ImportoTitolo'];
                     
                     if ($TipoViaggioId==2)
                         $Importo=$Importo/2;
                         
                         $KmTot=$arr_iva['KmPercorsi'];
                         if ($arr_iva['np']==$arr_iva['nd']) {
                             $TotImponibile=$Importo;
                             $TotIva=$TotImponibile-($TotImponibile/$arr_iva['ScorporoP']);
                             $KmSuConfine=$KmTot;
                             $Scorporo=$arr_iva['ScorporoD'];
                             $ConfineId=$arr_iva['cidp'];
                             $Aliquota=$arr_iva['AliquotaPartenza'];
                         } else {
                             if (!is_null($arr_iva['ScorporoP'])) {
                                 $Scorporo=$arr_iva['ScorporoP'];
                                 $ConfineId=$arr_iva['cidp'];
                                 $Aliquota=$arr_iva['AliquotaPartenza'];
                             } else {
                                 $Scorporo=$arr_iva['ScorporoD'];
                                 $ConfineId=$arr_iva['cidd'];
                                 $Aliquota=$arr_iva['AliquotaDestinazione'];
                             }
                             
                             
                             $KmPercorsiTotali= $arr_iva['KmPercorsi'];
                             $KmSuConfine=$arr_iva['KmTotali'];
                             $TotImponibile=(($Importo*$KmSuConfine)/$KmPercorsiTotali);
                             $TotIva=$TotImponibile-($TotImponibile/$Scorporo);
                             
                         }
                         if($KmSuConfine > 0) {
                             if($Aliquota == 0) {
                                 $TotIva = 0;
                             }
                             $diva=array();
                             $Importo=  number_format($Importo,2);
                             $Imponibile=  number_format($TotImponibile,2);
                             $TotIva=  number_format($TotIva,2);
                             $diva['ConfineId']=$ConfineId;
                             $diva['PrenotazioneTitoloId']=$titoloId;
                             $diva['KmPercorsiTotale']=$KmTot;
                             $diva['KmPercorsiTerritorio']=$KmSuConfine;
                             $diva['AliquotaIva']=$Aliquota;
                             $diva['ImportoTitolo']=$Importo;
                             $diva['ImportoTitoloPerConfine']=$Imponibile;
                             $diva['ImportoIvaConfine']=$TotIva;
                             $diva = $storico->operazioni_insert($diva, $user);
                             $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
                         }
                 }
             } else if(Config::$ivaTerritoriaItaliano){
                 //se non � presente in tabella alcun record il viaggio avviene su territorio italiano
                 $Importo=$titolo['ImportoTitolo'];
                 if ($TipoViaggioId==2)
                     $Importo=$Importo/2;
                     
                     $sql = "SELECT * FROM RT_Confine WHERE ConfineId = 2";
                     $confine=$db->query_first($sql);
                     
                     $diva=array();
                     $Imponibile = $Importo;
                     $TotImponibile = $Imponibile;
                     $TotIva = $TotImponibile-($TotImponibile/$confine['ValoreScorporo']);
                     $Importo=  number_format($Importo,2);
                     $Imponibile=  number_format($TotImponibile,2);
                     $TotIva=  number_format($TotIva,2);
                     $diva['ConfineId']=2;
                     $diva['PrenotazioneTitoloId']=$titoloId;
                     $diva['KmPercorsiTotale']=$kmArray['KmPercorsiAndata'];
                     $diva['KmPercorsiTerritorio']=$kmArray['KmPercorsiAndata'];
                     $diva['AliquotaIva']=$confine['Aliquota'];
                     $diva['ImportoTitolo']=$Importo;
                     $diva['ImportoTitoloPerConfine']=$Imponibile;
                     $diva['ImportoIvaConfine']=$TotIva;
                     $diva = $storico->operazioni_insert($diva, $user);
                     $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
             }
             /* FINE inserimento IVA */
             
             
             
             $provv=array();
             $provv['PrenotazioneTitoloId'] = $titoloId;
             $provv['GestoreId'] = $gestoreIdRef;
             
             $provv['PercentualeAgenzia'] = $percentuale_a;
             $provv['FissoAgenzia'] = $fisso_a;
             $provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoVenduto']*$percentuale_a)/100;
             $provv = $storico->operazioni_insert($provv, $user);
             $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);

             $finito=false;
             $gestorePadreId=$user->GestoreId;
             $conta=0;
             $gestorePadreIdOld = null;
             while($finito==false || $conta<100)
             {
                 
                 $gestoreNew=new Gestore($gestorePadreId);
                 $gestoreNew->conn=$db;
                 $gestoreNew->inizializzaDatiGenerali(null);
                 $gestorePadreIdOld = $gestorePadreId;
                 $gestorePadreId=$gestoreNew->GestoreDatiGenerali['GestorePadre'];
                 
                 if ($gestorePadreId>0 && $gestorePadreIdOld != $gestorePadreId) {
                     $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$gestorePadreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
                     $row=$db->query_first($sql);
                     $percentuale_a=0;
                     $fisso_a=0;
                     if ($row['GestoreConvenzioneId']>0)
                     {
                         $percentuale_a=$row['Percentuale'];
                         $fisso_a=$row['Fisso'];
                     }
                     
                     $provv=array();
                     $provv['PrenotazioneTitoloId']=$titoloId;
                     $provv['GestoreId']=$gestorePadreId;
                     
                     //$provv['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
                     $provv['PercentualeAgenzia'] = $percentuale_a;
                     $provv['FissoAgenzia'] = $fisso_a;
                     $provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoVenduto']*$percentuale_a)/100;
                     $provv = $storico->operazioni_insert($provv, $user);
                     $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
                 }
                 else
                     $finito=true;
                     
                     $conta++;
             }
         }
         
         $data = array();
         $data['PrenotazioneStato'] = 3;
         $data=$storico->operazioni_update($data,$user);
         
         $result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         
         $result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         return $codice;
     }
     
     
     public function EmettiBigliettiRimborsoExtra($PrenotazioneNumeroId, $ImportoRimborso) {
         global $user;
         
         $db = $this->conn;
         
         $PrenotazioneId = $this->Id;

         //recupero la prenotazione
         $sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
         $prenotazione = $db->query_first($sql);
         
         //recupero il gestore che ha emesso la prenotazione
         $gestoreIdRef = $prenotazione['GestoreIdRef'];
         
         $storico = new StoricoOperazioni();
         $storico->conn = $db;
         
         $sql="Select PercorsoId,LineaId from RT_PrenotazionePercorso where Stato=1 AND Cancella=0 AND PrenotazioneId=$PrenotazioneId";
         $row = $db->query_first($sql);
         $PercorsoId = $row['PercorsoId'];
         $LineaId = $row['LineaId'];
         
         $sql="Select gp.IdentificativoBiglietto from Gestore g LEFT JOIN GestoreGruppo gp ON (g.GestoreGruppoId = gp.GestoreGruppoId) where GestoreId = $gestoreIdRef";
         $gestore = $db->query_first($sql);
         $IdentificativoBiglietto = $gestore['IdentificativoBiglietto'];
         if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
             $IdentificativoBiglietto = Config::$identificativoBiglietto;;
         }
         
         $sql="Select TipoViaggioId from RT_Prenotazione where PrenotazioneId=$PrenotazioneId";
         $viaggio = $db->query_first($sql);
         $TipoViaggioId=$viaggio['TipoViaggioId'];
         
         /*recupero l'ultimo movimento per verificare l'importo venduto, diverso dall'importo del titolo */
         $sql = "SELECT * FROM RT_PrenotazioneMovimento where PrenotazioneId = $PrenotazioneId AND TipoMovimento = 'R' order by DataIns DESC, DataAgg DESC";
         $tempMovimento = $db->query_first($sql);
         if($tempMovimento['PagamentoTipoId'] == 12){
             $isCoupon = true;
         } else {
             $isCoupon = false;
         }
         
         $sql="Select * from RT_PrenotazioneNumero where PrenotazioneNumeroId=$PrenotazioneNumeroId and PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0 order by PrenotazioneNumeroId asc";
         $prenotazioneNumero = $db->fetch_array($sql);
         $codice = '';
         foreach ($prenotazioneNumero as $numero) {
             
             $TipologiaBigliettoId = $numero['TipologiaBigliettoId'];
             
             $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId = $gestoreIdRef and BigliettoId = $TipologiaBigliettoId and LineaId = $LineaId";
             $row=$db->query_first($sql);
             $percentuale_a=0;
             $fisso_a=0;
             if ($row['GestoreConvenzioneId']>0)
             {
                 $percentuale_a=$row['Percentuale'];
                 $fisso_a=$row['Fisso'];  
             }
             
             $progressivo = $this->GetProgressivoTitoloDiViaggioServizi();
             $identificativoServizio = "E-";
             
             $codice = $identificativoServizio.$IdentificativoBiglietto . '-' . Str_pad($progressivo, 8, "0", STR_PAD_LEFT);

             $titolo = array();
             $titolo['PrenotazioneId'] = $PrenotazioneId;
             $titolo['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
             $titolo['Codice'] = $codice;
             $titolo['Anno'] = date('Y');
             $titolo['Progressivo'] = $progressivo;
             $titolo['TipoTitolo'] = "R";
             $titolo['PercorsoId'] = $PercorsoId;
             
             $sql = "SELECT pn.PrenotazioneNumeroId, pn.PasseggeroId, pp.Cognome, pp.Nome, pp.SessoId, pp.Eta, pn.TipologiaBigliettoId, tb.TipologiaBiglietto
                FROM RT_PrenotazioneNumero pn
                LEFT JOIN RT_PrenotazionePasseggeri pp ON (pn.PasseggeroId = pp.PrenotazionePasseggeroId)
                LEFT jOIN RT_TipologiaBiglietto tb ON (tb.TipologiaBigliettoId = pp.TipoBigliettoId)
                WHERE pn.PrenotazioneNumeroId = $PrenotazioneNumeroId";
             
             $datiPasseggero = $db->query_first($sql);
             $titolo['PasseggeroId'] = $datiPasseggero['PasseggeroId'];
             $titolo['Cognome'] = $datiPasseggero['Cognome'];
             $titolo['Nome'] = $datiPasseggero['Nome'];
             $titolo['SessoId'] = $datiPasseggero['SessoId'];
             $titolo['Eta'] = $datiPasseggero['Eta'];
             $titolo['TipologiaBigliettoId'] = $datiPasseggero['TipologiaBigliettoId'];
             $titolo['TipologiaBiglietto'] = $datiPasseggero['TipologiaBiglietto'];
             
             $titolo['ImportoTitolo'] = $ImportoRimborso;
             if($isCoupon){
                 $titolo['ImportoVenduto'] = 0;
             } else {
                 $titolo['ImportoVenduto'] = $ImportoRimborso;
             }
             $titolo = $storico->operazioni_insert($titolo, $user);
             $titolo['GestoreIdRef'] = $gestoreIdRef;
             $titoloId=$db->insert("RT_PrenotazioneTitolo", $titolo);
             
             /*inserimento IVA*/
             if(Config::$ivaTitoloExtra){
                 $sql="select * from RT_ViewImportiPerIva003 where PrenotazioneTitoloId=$titoloId";
                 $ivaarr = $db->fetch_array($sql);
                 if(count($ivaarr) > 0) {
                     foreach ($ivaarr as $arr_iva) {
                         $Scorporo=0;
                         $ConfineId=0;
                         $Aliquota=0;
                         $Importo=$arr_iva['ImportoTitolo'];
                         
                         if ($TipoViaggioId==2)
                             $Importo=$Importo/2;
                             
                             $KmTot=$arr_iva['KmPercorsi'];
                             if ($arr_iva['np']==$arr_iva['nd']) {
                                 $TotImponibile=$Importo;
                                 $TotIva=$TotImponibile-($TotImponibile/$arr_iva['ScorporoP']);
                                 $KmSuConfine=$KmTot;
                                 $Scorporo=$arr_iva['ScorporoD'];
                                 $ConfineId=$arr_iva['cidp'];
                                 $Aliquota=$arr_iva['AliquotaPartenza'];
                             } else {
                                 if (!is_null($arr_iva['ScorporoP'])) {
                                     $Scorporo=$arr_iva['ScorporoP'];
                                     $ConfineId=$arr_iva['cidp'];
                                     $Aliquota=$arr_iva['AliquotaPartenza'];
                                 } else {
                                     $Scorporo=$arr_iva['ScorporoD'];
                                     $ConfineId=$arr_iva['cidd'];
                                     $Aliquota=$arr_iva['AliquotaDestinazione'];
                                 }
                                 
                                 
                                 $KmPercorsiTotali= $arr_iva['KmPercorsi'];
                                 $KmSuConfine=$arr_iva['KmTotali'];
                                 $TotImponibile=(($Importo*$KmSuConfine)/$KmPercorsiTotali);
                                 $TotIva=$TotImponibile-($TotImponibile/$Scorporo);
                                 
                             }
                             if($KmSuConfine > 0) {
                                 if($Aliquota == 0) {
                                     $TotIva = 0;
                                 }
                                 $diva=array();
                                 $Importo=  number_format($Importo,2);
                                 $Imponibile=  number_format($TotImponibile,2);
                                 $TotIva=  number_format($TotIva,2);
                                 $diva['ConfineId']=$ConfineId;
                                 $diva['PrenotazioneTitoloId']=$titoloId;
                                 $diva['KmPercorsiTotale']=$KmTot;
                                 $diva['KmPercorsiTerritorio']=$KmSuConfine;
                                 $diva['AliquotaIva']=$Aliquota;
                                 $diva['ImportoTitolo']=$Importo;
                                 $diva['ImportoTitoloPerConfine']=$Imponibile;
                                 $diva['ImportoIvaConfine']=$TotIva;
                                 $diva = $storico->operazioni_insert($diva, $user);
                                 $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
                             }
                     }
                 } else if(Config::$ivaTerritoriaItaliano){
                     //se non � presente in tabella alcun record il viaggio avviene su territorio italiano
                     $Importo=$titolo['ImportoTitolo'];
                     if ($TipoViaggioId==2)
                         $Importo=$Importo/2;
                         
                         $sql = "SELECT * FROM RT_Confine WHERE ConfineId = 2";
                         $confine=$db->query_first($sql);
                         
                         $diva=array();
                         $Imponibile = $Importo;
                         $TotImponibile = $Imponibile;
                         $TotIva = $TotImponibile-($TotImponibile/$confine['ValoreScorporo']);
                         $Importo=  number_format($Importo,2);
                         $Imponibile=  number_format($TotImponibile,2);
                         $TotIva=  number_format($TotIva,2);
                         $diva['ConfineId']=2;
                         $diva['PrenotazioneTitoloId']=$titoloId;
                         $diva['KmPercorsiTotale']=$kmArray['KmPercorsiAndata'];
                         $diva['KmPercorsiTerritorio']=$kmArray['KmPercorsiAndata'];
                         $diva['AliquotaIva']=$confine['Aliquota'];
                         $diva['ImportoTitolo']=$Importo;
                         $diva['ImportoTitoloPerConfine']=$Imponibile;
                         $diva['ImportoIvaConfine']=$TotIva;
                         $diva = $storico->operazioni_insert($diva, $user);
                         $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
                 }
             }
             /* FINE inserimento IVA */
             
             $provv=array();
             $provv['PrenotazioneTitoloId'] = $titoloId;
             $provv['GestoreId'] = $gestoreIdRef;
             
             //$provv['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
             $provv['PercentualeAgenzia'] = $percentuale_a;
             $provv['FissoAgenzia'] = $fisso_a;
             $provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoVenduto']*$percentuale_a)/100;
             $provv = $storico->operazioni_insert($provv, $user);
             $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
                   
             $finito=false;
             $gestorePadreId=$user->GestoreId;
             $conta=0;
             $gestorePadreIdOld = null;
             while($finito==false || $conta<100)
             {
                 
                 $gestoreNew=new Gestore($gestorePadreId);
                 $gestoreNew->conn=$db;
                 $gestoreNew->inizializzaDatiGenerali(null);
                 $gestorePadreIdOld = $gestorePadreId;
                 $gestorePadreId=$gestoreNew->GestoreDatiGenerali['GestorePadre'];
                 
                 if ($gestorePadreId>0 && $gestorePadreIdOld != $gestorePadreId) {
                     $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$gestorePadreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
                     $row=$db->query_first($sql);
                     $percentuale_a=0;
                     $fisso_a=0;
                     if ($row['GestoreConvenzioneId']>0)
                     {
                         $percentuale_a=$row['Percentuale'];
                         $fisso_a=$row['Fisso'];
                     }
                     
                     $provv=array();
                     $provv['PrenotazioneTitoloId']=$titoloId;
                     $provv['GestoreId']=$gestorePadreId;
                     
                     //$provv['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
                     $provv['PercentualeAgenzia'] = $percentuale_a;
                     $provv['FissoAgenzia'] = $fisso_a;
                     $provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoVenduto']*$percentuale_a)/100;
                     $provv = $storico->operazioni_insert($provv, $user);
                     $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
                      
                 }
                 else
                     $finito=true;
                     
                     $conta++;
             }
  
         }
         
         $data = array();
         $data['PrenotazioneStato'] = 3;
         $data=$storico->operazioni_update($data,$user);
         
         $result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         
         $result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         return $codice;
     }
     
     
    public function EmettiBigliettiExtra($importoExtra, $coupon = false) {
        global $user;
        
        $db = $this->conn;
        
        if (!isset($PrenotazioneId)) {
           $PrenotazioneId = $this->Id;
        }

        //recupero la prenotazione
        $sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
        $prenotazione = $db->query_first($sql);
        
        //recupero il gestore che ha emesso la prenotazione
        $gestoreIdRef = $prenotazione['GestoreIdRef'];
        
        //controllo importo pagato con coupon rimborsati
        $sql = "SELECT * FROM RT_PrenotazioneMovimento where PrenotazioneId = $PrenotazioneId and Coupon is not null";
        $coupons = $db->fetch_array($sql);
        $importoCouponRimborso = 0;
        foreach ($coupons as $c){
           $sql = "SELECT * FROM RT_Coupon where CouponNome like '%Rimborso%' and Codice = '".$c['Coupon']."'";
           $row = $db->query_first($sql);
           if(isset($row['CouponId'])){
              $importoCouponRimborso += $c['ImportoPagato'];
           }
        }
        
        $storico = new StoricoOperazioni();
        $storico->conn = $db;
        
        $sql="Select PercorsoId,LineaId from RT_PrenotazionePercorso where Stato=1 AND Cancella=0 AND PrenotazioneId=$PrenotazioneId";
        $row = $db->query_first($sql);
        $PercorsoId = $row['PercorsoId'];
        $LineaId = $row['LineaId'];
        
        $sql="Select gp.IdentificativoBiglietto from Gestore g LEFT JOIN GestoreGruppo gp ON (g.GestoreGruppoId = gp.GestoreGruppoId) where GestoreId = $gestoreIdRef";
        $gestore = $db->query_first($sql);
        $IdentificativoBiglietto = $gestore['IdentificativoBiglietto'];
        if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
           $IdentificativoBiglietto = Config::$identificativoBiglietto;;
        }
        
        $sql="Select * from RT_PrenotazioneNumero where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0 order by PrenotazioneNumeroId asc";
        $prenotazioneNumero = $db->fetch_array($sql);
        
        $sql="Select TipoViaggioId from RT_Prenotazione where PrenotazioneId=$PrenotazioneId";
        $viaggio = $db->query_first($sql);
        $TipoViaggioId=$viaggio['TipoViaggioId'];
        
        /*recupero l'ultimo movimento per verificare l'importo venduto, diverso dall'importo del titolo */
        $sql = "SELECT * FROM RT_PrenotazioneMovimento where PrenotazioneId = $PrenotazioneId AND TipoMovimento = 'I' order by DataIns DESC, DataAgg DESC";
        $tempMovimento = $db->query_first($sql);
        if($tempMovimento['PagamentoTipoId'] == 12){
           $importoMovimento = 0;
        } else {
           $importoMovimento = $tempMovimento['ImportoPagato'];
        }
        
        $countNumeroTitoli = 1;
        
        $sql = "SELECT n.PrenotazioneNumeroId, n.TipologiaBigliettoId FROM RT_PrenotazionePasseggeri p
                left join RT_PrenotazioneNumero n on (n.PasseggeroId = p.PrenotazionePasseggeroId and
                p.PrenotazioneId = n.PrenotazioneId and p.TipoBigliettoId = n.TipologiaBigliettoId)
                where p.PrenotazioneId = $PrenotazioneId and p.principale = 1;";
        
        $numero = $db->query_first($sql);
        $PrenotazioneNumeroId = $numero['PrenotazioneNumeroId'];
        $TipologiaBigliettoId = $numero['TipologiaBigliettoId'];
        
        $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId = $gestoreIdRef and BigliettoId = $TipologiaBigliettoId and LineaId = $LineaId";
        $row=$db->query_first($sql);
        $percentuale_a=0;
        $fisso_a=0;
        if(!$coupon) {
           if ($row['GestoreConvenzioneId']>0) {
              $percentuale_a = $row['Percentuale'];
              $fisso_a = $row['Fisso'];
           }
        } else {
           $percentuale_a = 0;
           $fisso_a = 0;
        }
        
        $progressivo = $this->GetProgressivoTitoloDiViaggioServizi();
        $identificativoServizio = "E-";
        
        $codice = $identificativoServizio.$IdentificativoBiglietto . '-' . Str_pad($progressivo, 8, "0", STR_PAD_LEFT);
        $titolo = array();
        $titolo['PrenotazioneId'] = $PrenotazioneId;
        $titolo['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
        $titolo['Codice'] = $codice;
        $titolo['Anno'] = date('Y');
        $titolo['Progressivo'] = $progressivo;
        $titolo['TipoTitolo'] = "X";
        $titolo['PercorsoId'] = $PercorsoId;
        
        $sql = "SELECT pn.PrenotazioneNumeroId, pn.PasseggeroId, pp.Cognome, pp.Nome, pp.SessoId, pp.Eta, pn.TipologiaBigliettoId, tb.TipologiaBiglietto
             FROM RT_PrenotazioneNumero pn
             LEFT JOIN RT_PrenotazionePasseggeri pp ON (pn.PasseggeroId = pp.PrenotazionePasseggeroId)
             LEFT jOIN RT_TipologiaBiglietto tb ON (tb.TipologiaBigliettoId = pp.TipoBigliettoId)
             WHERE pn.PrenotazioneNumeroId = $PrenotazioneNumeroId";
        $datiPasseggero = $db->query_first($sql);
        $titolo['PasseggeroId'] = $datiPasseggero['PasseggeroId'];
        $titolo['Cognome'] = $datiPasseggero['Cognome'];
        $titolo['Nome'] = $datiPasseggero['Nome'];
        $titolo['SessoId'] = $datiPasseggero['SessoId'];
        $titolo['Eta'] = $datiPasseggero['Eta'];
        $titolo['TipologiaBigliettoId'] = $datiPasseggero['TipologiaBigliettoId'];
        $titolo['TipologiaBiglietto'] = $datiPasseggero['TipologiaBiglietto'];
        
        $titolo['ImportoTitolo'] = $importoExtra;
        if(($importoMovimento) == $titolo['ImportoTitolo']) {
           $titolo['ImportoVenduto'] = $titolo['ImportoTitolo'];
        } else {
           $titolo['ImportoVenduto'] = $importoMovimento;
        }
        
        $titolo = $storico->operazioni_insert($titolo, $user);
        $titolo['GestoreIdRef'] = $gestoreIdRef;
        $titoloId=$db->insert("RT_PrenotazioneTitolo", $titolo);
        
        /*inserimento IVA*/
        if(Config::$ivaTitoloExtra) {
           $sql="select * from RT_ViewImportiPerIva003 where PrenotazioneTitoloId=$titoloId";
           $ivaarr = $db->fetch_array($sql);
           if(count($ivaarr) > 0) {
              foreach ($ivaarr as $arr_iva) {
                 $Scorporo=0;
                 $ConfineId=0;
                 $Aliquota=0;
                 $Importo=$arr_iva['ImportoTitolo'];
                 
                 if ($TipoViaggioId==2)
                    $Importo=$Importo/2;
                    
                    $KmTot=$arr_iva['KmPercorsi'];
                    if ($arr_iva['np']==$arr_iva['nd']) {
                        $TotImponibile=$Importo;
                        $TotIva=$TotImponibile-($TotImponibile/$arr_iva['ScorporoP']);
                        $KmSuConfine=$KmTot;
                        $Scorporo=$arr_iva['ScorporoD'];
                        $ConfineId=$arr_iva['cidp'];
                        $Aliquota=$arr_iva['AliquotaPartenza'];
                    } else {
                        if (!is_null($arr_iva['ScorporoP'])) {
                           $Scorporo=$arr_iva['ScorporoP'];
                           $ConfineId=$arr_iva['cidp'];
                           $Aliquota=$arr_iva['AliquotaPartenza'];
                        } else {
                           $Scorporo=$arr_iva['ScorporoD'];
                           $ConfineId=$arr_iva['cidd'];
                           $Aliquota=$arr_iva['AliquotaDestinazione'];
                        }
                        
                        
                        $KmPercorsiTotali= $arr_iva['KmPercorsi'];
                        $KmSuConfine=$arr_iva['KmTotali'];
                        $TotImponibile=(($Importo*$KmSuConfine)/$KmPercorsiTotali);
                        $TotIva=$TotImponibile-($TotImponibile/$Scorporo);
                        
                    }
                    if($KmSuConfine > 0) {
                        if($Aliquota == 0) {
                           $TotIva = 0;
                        }
                        $diva=array();
                        $Importo=  number_format($Importo,2);
                        $Imponibile=  number_format($TotImponibile,2);
                        $TotIva=  number_format($TotIva,2);
                        $diva['ConfineId']=$ConfineId;
                        $diva['PrenotazioneTitoloId']=$titoloId;
                        $diva['KmPercorsiTotale']=$KmTot;
                        $diva['KmPercorsiTerritorio']=$KmSuConfine;
                        $diva['AliquotaIva']=$Aliquota;
                        $diva['ImportoTitolo']=$Importo;
                        $diva['ImportoTitoloPerConfine']=$Imponibile;
                        $diva['ImportoIvaConfine']=$TotIva;
                        $diva = $storico->operazioni_insert($diva, $user);
                        $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
                    }
              }
           } else if(Config::$ivaTerritoriaItaliano){
              //se non � presente in tabella alcun record il viaggio avviene su territorio italiano
              $Importo=$titolo['ImportoTitolo'];
              if ($TipoViaggioId==2)
                 $Importo=$Importo/2;
                 
                 $sql = "SELECT * FROM RT_Confine WHERE ConfineId = 2";
                 $confine=$db->query_first($sql);
                 
                 $diva=array();
                 $Imponibile = $Importo;
                 $TotImponibile = $Imponibile;
                 $TotIva = $TotImponibile-($TotImponibile/$confine['ValoreScorporo']);
                 $Importo=  number_format($Importo,2);
                 $Imponibile=  number_format($TotImponibile,2);
                 $TotIva=  number_format($TotIva,2);
                 $diva['ConfineId']=2;
                 $diva['PrenotazioneTitoloId']=$titoloId;
                 $diva['KmPercorsiTotale']=$kmArray['KmPercorsiAndata'];
                 $diva['KmPercorsiTerritorio']=$kmArray['KmPercorsiAndata'];
                 $diva['AliquotaIva']=$confine['Aliquota'];
                 $diva['ImportoTitolo']=$Importo;
                 $diva['ImportoTitoloPerConfine']=$Imponibile;
                 $diva['ImportoIvaConfine']=$TotIva;
                 $diva = $storico->operazioni_insert($diva, $user);
                 $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
           }
        }
        /* FINE inserimento IVA */

        $provv=array();
        $provv['PrenotazioneTitoloId']=$titoloId;
        $provv['GestoreId'] = $gestoreIdRef;
        
        $provv['PercentualeAgenzia'] = $percentuale_a;
        $provv['FissoAgenzia'] = $fisso_a;
        $provv['ImportoAgenzia'] = $fisso_a+(($titolo['ImportoTitolo'] - $importoCouponRimborso/$countNumeroTitoli)*$percentuale_a)/100;
        $provv = $storico->operazioni_insert($provv, $user);
        $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
        
        $finito=false;
        $gestorePadreId = $gestoreIdRef;
        $conta = 0;
        $gestorePadreIdOld = null;
        while($finito==false || $conta<100)
        {
           $gestoreNew=new Gestore($gestorePadreId);
           $gestoreNew->conn=$db;
           $gestoreNew->inizializzaDatiGenerali(null);
           $gestorePadreIdOld = $gestorePadreId;
           $gestorePadreId=$gestoreNew->GestoreDatiGenerali['GestorePadre'];
           
           if ($gestorePadreId>0 && $gestorePadreIdOld != $gestorePadreId) {
              $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$gestorePadreId and BigliettoId=$TipologiaBigliettoId and LineaId=$LineaId";
              $row=$db->query_first($sql);
              $percentuale_a=0;
              $fisso_a=0;
              if(!$coupon){
                 if ($row['GestoreConvenzioneId']>0) {
                    $percentuale_a = $row['Percentuale'];
                    $fisso_a = $row['Fisso'];
                 }
              } else {
                 $percentuale_a = 0;
                 $fisso_a = 0;
              }
              
              $provv=array();
              $provv['PrenotazioneTitoloId']=$titoloId;
              $provv['GestoreId']=$gestorePadreId;
              
              $provv['PercentualeAgenzia'] = $percentuale_a;
              $provv['FissoAgenzia'] = $fisso_a;
              $provv['ImportoAgenzia'] = $fisso_a+(($titolo['ImportoTitolo'] - $importoCouponRimborso/$countNumeroTitoli)*$percentuale_a)/100;
              $provv = $storico->operazioni_insert($provv, $user);
              $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
           }
           else
              $finito=true;

            $conta++;
        }
    }
     
     
     
     public function EmettiBiglietti($PrenotazioneId = null, $coupon = false) {
         global $user;
         
         $db = $this->conn;
         
         if (!isset($PrenotazioneId)) {
             $PrenotazioneId = $this->Id;
         }
         
         //recupero la prenotazione
         $sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
         $prenotazione = $db->query_first($sql);
         
         //recupero il gestore che ha emesso la prenotazione
         $gestoreIdRef = $prenotazione['GestoreIdRef'];
         
         //controllo importo pagato con coupon rimborsati
         $sql = "SELECT * FROM RT_PrenotazioneMovimento where PrenotazioneId = $PrenotazioneId and Coupon is not null";
         $coupons = $db->fetch_array($sql);
         $importoCouponRimborso = 0;
         foreach ($coupons as $c){
             $sql = "SELECT * FROM RT_Coupon where CouponNome like '%Rimborso%' and Codice = '".$c['Coupon']."'";
             $row = $db->query_first($sql);
             if(isset($row['CouponId'])){
                 $importoCouponRimborso += $c['ImportoPagato'];
             }
         }
         
         //definisco l'oggetto StoricoOperazioni
         $storico = new StoricoOperazioni();
         $storico->conn = $db;
         
         //recupero percorso e linea
         $sql="Select PercorsoId, LineaId from RT_PrenotazionePercorso where Stato=1 AND Cancella=0 AND PrenotazioneId = $PrenotazioneId";
         $row = $db->query_first($sql);
         $PercorsoId = $row['PercorsoId'];
         $LineaId = $row['LineaId'];
         
         //recupero identificativo gruppo gestore
         $sql="Select gp.IdentificativoBiglietto from Gestore g LEFT JOIN GestoreGruppo gp ON (g.GestoreGruppoId = gp.GestoreGruppoId) where GestoreId = $gestoreIdRef";
         $gestore = $db->query_first($sql);
         $IdentificativoBiglietto = $gestore['IdentificativoBiglietto'];
         if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
             $IdentificativoBiglietto = Config::$identificativoBiglietto;;
         }
         
         //recupero prenotazione numero della prenotazione
         $sql = "Select * from RT_PrenotazioneNumero n
         	left join RT_PrenotazionePasseggeri p on p.PrenotazionePasseggeroId = n.PasseggeroId 
         	where n.PrenotazioneId = $PrenotazioneId 
         	and n.OdcIdRef = $user->OdcId and n.Cancella = 0 and
         	p.Principale = 1 
         	order by n.PrenotazioneNumeroId asc";
         $prenotazioneNumero = $db->fetch_array($sql);
         
         //recupero tipo viaggio e totale da pagare
         $sql="Select TipoViaggioId, TotaleDaPagare from RT_Prenotazione where PrenotazioneId = $PrenotazioneId";
         $viaggio = $db->query_first($sql);
         $TipoViaggioId=$viaggio['TipoViaggioId'];
         
         //numero dei titoli da emettere
         $countNumeroTitoli = count($prenotazioneNumero);

         /*recupero l'ultimo movimento per verificare l'importo venduto, diverso dall'importo del titolo */
         $sql = "SELECT * FROM RT_PrenotazioneMovimento where PrenotazioneId = $PrenotazioneId AND TipoMovimento = 'I' order by DataIns DESC, DataAgg DESC";
         $tempMovimento = $db->query_first($sql);
         $pagatoTitolo = 0;
         if($tempMovimento['PagamentoTipoId'] == 12){
             $importoMovimento = 0;
             $pagatoTitolo = 0;
         } else {
             $importoMovimento = $tempMovimento['ImportoPagato'];
             if($viaggio['TotaleDaPagare'] == $importoMovimento){
                 $prezzoTotale = true;
             } else {
                 $prezzoTotale = false;
             }

             $sql = "SELECT Sum(ImportoPagato) as Pagato, Sum(Supplemento) as Supplemento  
                        from RT_PrenotazioneMovimento 
                        WHERE Stato = 1 AND Cancella = 0 AND TipoMovimento = 'I' AND ImportoPagato > 0
                        AND PagamentoTipoId NOT IN (7, 12)
                        AND PrenotazioneId = $PrenotazioneId";
            $tempPagato = $db->query_first($sql);
            $pagatoTitolo = $tempPagato['Pagato'] - $tempPagato['Supplemento'];
         }
         
         /*interscambio */
         $sql = "SELECT * FROM RT_PrenotazionePercorso where PrenotazioneId = $PrenotazioneId";
         $tempPercorso = $db->fetch_array($sql);
         $interscambioPercorso = array();
         foreach($tempPercorso as $percoso){
             $interscambio = array();
             $grafo = new GrafoTratte($percoso['LineaId'], $percoso['CorsaId'], $db, $percoso['ComuneSalitaId'], $percoso['ComuneDiscesaId']);
             if(count($grafo->flotta) > 1){
                 foreach ($grafo->flotta as $bus){
                     foreach ($bus->comuni as $k => $comune){
                         if(isset($comune['passeggeri']) && count($comune['passeggeri'])>0 &&
                             isset($bus->comuni[$k+1]['passeggeri']) && count($bus->comuni[$k+1]['passeggeri']) == 0
                             && $percoso['ComuneDiscesaId'] != $bus->comuni[$k+1]['comune']){
                                 $interscambio[] = $bus->comuni[$k+1]['comune'];
                         }
                     }
                 }
                 if(count($interscambio) > 0){
                     $temp = "".$interscambio[0];
                     for ($ii = 1; $ii < count($interscambio); $ii++ ){
                         $temp .= ', '.$interscambio[$ii];
                     }
                     $sql = "SELECT c.Comune, f.ComuneId, o.Orario, o.GiorniAggiuntivi FROM RT_Orario o
				LEFT JOIN RT_Fermata f ON f.FermataId = o.FermataId
				LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
				LEFT JOIN Comune c ON c.ComuneId =f.ComuneId
				WHERE f.ComuneId IN ($temp)
				AND o.Orario IS NOT NULL AND o.GiorniAggiuntivi IS NOT NULL
				AND t.LineaId = ".$percoso['LineaId']."
				AND o.CorsaId = ".$percoso['CorsaId']."
				AND f.Cancella = 0
				and o.Cancella = 0
				and t.cancella = 0
				GROUP BY f.ComuneId
					ORDER BY o.GiorniAggiuntivi ASC, o.Orario ASC";
                     $interscambioPercorso[] = $db->fetch_array($sql);
                 }
             }
         }
         /*fine interscambio*/
         
         foreach ($prenotazioneNumero as $numero) {
             //verifico se esiste un titolo già emesso associato al numero prenotazione
             $sql = "select * from RT_PrenotazioneTitolo where PrenotazioneNumeroId = ".$numero['PrenotazioneNumeroId'];
             $tempCount = $db->fetch_array($sql);

             //se non esiste già un titlo proseguo con l'emissione
             if(count($tempCount) == 0){
                //recupero le info del biglietto acquistato legaot al numero prenotazione
                 $sql="SELECT NumeroPax, PrezzoPax, RiduzionePax, AumentoPax FROM RT_PrenotazioneBiglietto
						WHERE PrenotazioneId=$PrenotazioneId AND TipologiaBigliettoId=".$numero['TipologiaBigliettoId'];
                 $Importi = $db->query_first($sql);
                 $PrenotazioneNumeroId = $numero['PrenotazioneNumeroId'];
                 $TipologiaBigliettoId = $numero['TipologiaBigliettoId'];
                 
                 //recupero provvigioni da applicare al gestore della prenotazione
                 $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId = $gestoreIdRef and BigliettoId = $TipologiaBigliettoId and LineaId = $LineaId";
                 $row=$db->query_first($sql);
                 $percentuale_a=0;
                 $fisso_a=0;
                 if(!$coupon) {
                     if ($row['GestoreConvenzioneId']>0) {
                         $percentuale_a = $row['Percentuale'];
                         $fisso_a = $row['Fisso'];
                     }
                 } else {
                     $percentuale_a = 0;
                     $fisso_a = 0;
                 }
                 
                 //recupero il progressivo del titolo di viaggio in base se è occupa posto (passeggero o servizio)
                 $sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = $TipologiaBigliettoId";
                 $tempTipo = $db->query_first($sql);
                 if($tempTipo['OccupaPosto'] == 1){
                     $progressivo = $this->GetProgressivoTitoloDiViaggio();
                     $identificativoServizio = "";
                 } else {
                     $progressivo = $this->GetProgressivoTitoloDiViaggioServizi();
                     $identificativoServizio = "E-";
                 }
                 $codice = $identificativoServizio.$IdentificativoBiglietto . '-' . Str_pad($progressivo, 8, "0", STR_PAD_LEFT);
                 
                 //inserimento del titolo di viaggio
                 $titolo = array();
                 $titolo['PrenotazioneId'] = $PrenotazioneId;
                 $titolo['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
                 $titolo['Codice'] = $codice;
                 $titolo['Anno'] = date('Y');
                 $titolo['Progressivo'] = $progressivo;
                 $titolo['TipoTitolo'] = "E";
                 $titolo['PercorsoId'] = $PercorsoId;
                 $sql = "SELECT pn.PrenotazioneNumeroId, pn.PasseggeroId, pp.Cognome, pp.Nome, pp.SessoId, pp.Eta, pn.TipologiaBigliettoId, tb.TipologiaBiglietto
					FROM RT_PrenotazioneNumero pn
					LEFT JOIN RT_PrenotazionePasseggeri pp ON (pn.PasseggeroId = pp.PrenotazionePasseggeroId)
					LEFT jOIN RT_TipologiaBiglietto tb ON (tb.TipologiaBigliettoId = pp.TipoBigliettoId)
					WHERE pn.PrenotazioneNumeroId = $PrenotazioneNumeroId";
                 $datiPasseggero = $db->query_first($sql);
                 $titolo['PasseggeroId'] = $datiPasseggero['PasseggeroId'];
                 $titolo['Cognome'] = $prenotazione['ClienteNome'];
                 $titolo['Nome'] = $prenotazione['ClienteNome'];
                 $titolo['SessoId'] = $prenotazione['ClienteSessoId'];
                 $titolo['Eta'] = 0;
                 $titolo['TipologiaBigliettoId'] = $datiPasseggero['TipologiaBigliettoId'];
                 $titolo['TipologiaBiglietto'] = $datiPasseggero['TipologiaBiglietto'];
                 
                 $titolo['ImportoTitolo'] = $pagatoTitolo;
                 $titolo['ImportoVenduto'] = $titolo['ImportoTitolo'];

                 $titolo = $storico->operazioni_insert($titolo, $user);
                 $titolo['GestoreIdRef'] = $gestoreIdRef;
                 $titoloId = $db->insert("RT_PrenotazioneTitolo", $titolo);
                 //fine inserimento titolo di viaggio
                 
                 /*inserimento interscambio*/
                 foreach($interscambioPercorso as $id => $p){
                     if($id == 0){
                         $tempTipoViaggio = 'A';
                     } else {
                         $tempTipoViaggio = 'R';
                     }
                     foreach($p as $k => $fermate){
                         $fermate['PrenotazioneTitoloId'] = $titoloId;
                         $fermate['TipoViaggio'] = $tempTipoViaggio;
                         $fermate['Ordine'] = $k;
                         print_r($fermate);
                         $db->insert("RT_PrenotazioneTitoloInterscambio", $fermate);
                     }
                 }
                 /*fine interscambio*/
                 
                 
                 /*inserimento IVA*/
                 $sql="select * from RT_ViewImportiPerIva003 where PrenotazioneTitoloId=$titoloId";
                 $ivaarr = $db->fetch_array($sql);
                 if(count($ivaarr) > 0) {
                     foreach ($ivaarr as $arr_iva) {
                         $Scorporo=0;
                         $ConfineId=0;
                         $Aliquota=0;
                         $Importo=$arr_iva['ImportoTitolo'];
                         
                         if ($TipoViaggioId==2)
                             $Importo=$Importo/2;
                             
                             $KmTot=$arr_iva['KmPercorsi'];
                             if ($arr_iva['np']==$arr_iva['nd']) {
                                 $TotImponibile=$Importo;
                                 $TotIva=$TotImponibile-($TotImponibile/$arr_iva['ScorporoP']);
                                 $KmSuConfine=$KmTot;
                                 $Scorporo=$arr_iva['ScorporoD'];
                                 $ConfineId=$arr_iva['cidp'];
                                 $Aliquota=$arr_iva['AliquotaPartenza'];
                             } else {
                                 if (!is_null($arr_iva['ScorporoP'])) {
                                     $Scorporo=$arr_iva['ScorporoP'];
                                     $ConfineId=$arr_iva['cidp'];
                                     $Aliquota=$arr_iva['AliquotaPartenza'];
                                 } else {
                                     $Scorporo=$arr_iva['ScorporoD'];
                                     $ConfineId=$arr_iva['cidd'];
                                     $Aliquota=$arr_iva['AliquotaDestinazione'];
                                 }
                                 
                                 
                                 $KmPercorsiTotali= $arr_iva['KmPercorsi'];
                                 $KmSuConfine=$arr_iva['KmTotali'];
                                 $TotImponibile=(($Importo*$KmSuConfine)/$KmPercorsiTotali);
                                 $TotIva=$TotImponibile-($TotImponibile/$Scorporo);
                                 
                             }
                             if($KmSuConfine > 0) {
                                 if($Aliquota == 0) {
                                     $TotIva = 0;
                                 }
                                 $diva=array();
                                 $Importo=  number_format($Importo,2);
                                 $Imponibile=  number_format($TotImponibile,2);
                                 $TotIva=  number_format($TotIva,2);
                                 $diva['ConfineId']=$ConfineId;
                                 $diva['PrenotazioneTitoloId']=$titoloId;
                                 $diva['KmPercorsiTotale']=$KmTot;
                                 $diva['KmPercorsiTerritorio']=$KmSuConfine;
                                 $diva['AliquotaIva']=$Aliquota;
                                 $diva['ImportoTitolo']=$Importo;
                                 $diva['ImportoTitoloPerConfine']=$Imponibile;
                                 $diva['ImportoIvaConfine']=$TotIva;
                                 $diva = $storico->operazioni_insert($diva, $user);
                                 $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
                             }
                     }
                 } else if(Config::$ivaTerritoriaItaliano){
                     //se non � presente in tabella alcun record il viaggio avviene su territorio italiano
                     $Importo=$titolo['ImportoTitolo'];
                     if ($TipoViaggioId==2)
                         $Importo=$Importo/2;
                         
                         $sql = "SELECT * FROM RT_Confine WHERE ConfineId = 2";
                         $confine=$db->query_first($sql);
                         
                         $diva=array();
                         $Imponibile = $Importo;
                         $TotImponibile = $Imponibile;
                         $TotIva = $TotImponibile-($TotImponibile/$confine['ValoreScorporo']);
                         $Importo=  number_format($Importo,2);
                         $Imponibile=  number_format($TotImponibile,2);
                         $TotIva=  number_format($TotIva,2);
                         $diva['ConfineId']=2;
                         $diva['PrenotazioneTitoloId']=$titoloId;
                         $diva['KmPercorsiTotale']=$kmArray['KmPercorsiAndata'];
                         $diva['KmPercorsiTerritorio']=$kmArray['KmPercorsiAndata'];
                         $diva['AliquotaIva']=$confine['Aliquota'];
                         $diva['ImportoTitolo']=$Importo;
                         $diva['ImportoTitoloPerConfine']=$Imponibile;
                         $diva['ImportoIvaConfine']=$TotIva;
                         $diva = $storico->operazioni_insert($diva, $user);
                         $ivaId=$db->insert("RT_PrenotazioneTitoloIva", $diva);
                 }
                 /* FINE inserimento IVA */
                 
                 /*inseriemnto provvigione Titolo agenzia venditore*/
                 $provv = array();
                 $provv['PrenotazioneTitoloId'] = $titoloId;
                 $provv['GestoreId'] = $gestoreIdRef;
                 
                 $provv['PercentualeAgenzia'] = $percentuale_a;
                 $provv['FissoAgenzia'] = $fisso_a;
                 $provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoVenduto']*$percentuale_a)/100;
                 $provv = $storico->operazioni_insert($provv, $user);

                 $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
    
                 /*inserimento provvigione Titolo per agenzie padri se ci sono*/
                 $finito=false;
                 $gestorePadreId = $gestoreIdRef;
 				 
                 $conta=0;
				 $gestorePadreIdOld = null;
                 while($finito==false || $conta<3) {

					 $gestoreNew=new Gestore($gestorePadreId);
                     $gestoreNew->conn=$db;
                     $gestoreNew->inizializzaDatiGenerali(null);
					 $gestorePadreIdOld = $gestorePadreId;
                     $gestorePadreId = $gestoreNew->GestoreDatiGenerali['GestorePadre'];                    
                  
					 if ($gestorePadreId>0 && $gestorePadreIdOld != $gestorePadreId) {
                         $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId = $gestorePadreId and BigliettoId = $TipologiaBigliettoId and LineaId = $LineaId";
                         $row=$db->query_first($sql);
                         $percentuale_a=0;
                         $fisso_a=0;
                         if(!$coupon){
                             if ($row['GestoreConvenzioneId']>0) {
                                 $percentuale_a = $row['Percentuale'];
                                 $fisso_a = $row['Fisso'];
                             }
                         } else {
                             $percentuale_a = 0;
                             $fisso_a = 0;
                         }
                         
                         $provv=array();
                         $provv['PrenotazioneTitoloId']=$titoloId;
                         $provv['GestoreId']=$gestorePadreId;
                         
                         $provv['PercentualeAgenzia'] = $percentuale_a;
                         $provv['FissoAgenzia'] = $fisso_a;
                         $provv['ImportoAgenzia'] = $fisso_a+($titolo['ImportoVenduto']*$percentuale_a)/100;
                         $provv = $storico->operazioni_insert($provv, $user);
                         $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
                     } else {
                         $finito=true;
                     }    
                     $conta++;
                 }
             }
             
         }
         
         $data = array();
         $data['PrenotazioneStato'] = 3;
         $data=$storico->operazioni_update($data,$user);
         
         $result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         $result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
     }
     
     //FUNZIONE OBSOLETA NON PIU UTILIZZATA
     public function EmettiBigliettiAuto($NumeroBiglietti, $CorsaIdAndata, $CorsaIdRitorno, $tipo, $DataTitolo)
     {
         global $user;
         $db=$this->conn;
         $PrenotazioneId=$this->Id;
         
         $nt=0;
         
         $sql="SELECT
	rt_corsa.CorsaId AS CorsaId,
	rt_corsa.LineaId AS LineaId,
	rt_corsa.CorsaNome AS CorsaNome,
	rt_corsa.AttivaDal AS AttivaDal,
	rt_corsa.AttivaAl AS AttivaAl,
	rt_corsa.IncludiFeriale AS IncludiFeriale,
	rt_corsa.IncludiPrefestivo AS IncludiPrefestivo,
	rt_corsa.IncludiFestivo AS IncludiFestivo,
	rt_corsa.OrarioPartenza AS OrarioPartenza,
	rt_corsa.OrarioArrivo AS OrarioArrivo,
	rt_corsa.NextDay AS NextDay,
	rt_corsa.DataIns AS DataIns,
	rt_corsa.OpeIns AS OpeIns,
	rt_corsa.SedeIns AS SedeIns,
	rt_corsa.IpIns AS IpIns,
	rt_corsa.DataAgg AS DataAgg,
	rt_corsa.OpeAgg AS OpeAgg,
	rt_corsa.SedeAgg AS SedeAgg,
	rt_corsa.IpAgg AS IpAgg,
	rt_corsa.Stato AS Stato,
	rt_corsa.Cancella AS Cancella,
	rt_corsa.UpdateCount AS UpdateCount,
	rt_corsa.OdcIdRef AS OdcIdRef,
	rt_corsa.GestoreIdRef AS GestoreIdRef,
	rt_linea.LineaNome AS LineaNome,
	rt_linea.PercorsoId AS PercorsoId,
	rt_percorso.PercorsoNome AS PercorsoNome,
	rt_corsa.CorsaPeso AS CorsaPeso,
	rt_corsa.VendibileDal AS VendibileDal,
	rt_corsa.VendibileAl AS VendibileAl,
	rt_percorso.Identificativo AS Identificativo
	FROM RT_Corsa AS rt_corsa
	LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
	LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
	WHERE rt_corsa.Cancella = 0
	AND rt_corsa.CorsaId = $CorsaIdAndata";
         
         
         $row = $db->query_first($sql);
         $PercorsoId=$row['PercorsoId'];
         $Identificativo=$row['Identificativo'];
         
         
         $data['PrenotazioneStato']=3;
         $result=$db->update("RT_Prenotazione", $data,"PrenotazioneId=$PrenotazioneId");
         $result=$db->update("RT_PrenotazionePercorso", $data,"PrenotazioneId=$PrenotazioneId");
         
         $sql="Select PercorsoId,LineaId,ComuneSalitaId, ComuneDiscesaId,CorsaDataPartenza from RT_PrenotazionePercorso where Stato=1 AND Cancella=0 AND PrenotazioneId=$PrenotazioneId";
         $rowTemp = $db->query_first($sql);
         $ComuneSalitaId=$row['ComuneSalitaId'];
         $ComuneDiscesaId=$row['ComuneDiscesaId'];
         $CorsaDataPartenza=$row['CorsaDataPartenza'];
         $LineaId=$row['LineaId'];
         
         $sql="Select * from RT_PrenotazioneNumero where PrenotazioneId=$PrenotazioneId and Cancella=0 order by PrenotazioneNumeroId asc";
         
         $ArrObject = $db->fetch_array($sql);
         $nt=0;
         while($nt<sizeof($ArrObject)) {
             $PrenotazioneNumeroId=$ArrObject[$nt]['PrenotazioneNumeroId'];
             
             $sql = "SELECT pn.PrenotazioneNumeroId, pn.PasseggeroId, pp.Cognome, pp.Nome, pp.SessoId, pp.Eta, pn.TipologiaBigliettoId, tb.TipologiaBiglietto
    	FROM RT_PrenotazioneNumero pn
    	LEFT JOIN RT_PrenotazionePasseggeri pp ON (pn.PasseggeroId = pp.PrenotazionePasseggeroId)
    	LEFT jOIN RT_TipologiaBiglietto tb ON (tb.TipologiaBigliettoId = pp.TipoBigliettoId)
    	WHERE pn.PrenotazioneNumeroId = $PrenotazioneNumeroId";
             $datiPasseggero = $db->query_first($sql);
             $d1['PasseggeroId'] = $datiPasseggero['PasseggeroId'];
             $d1['Cognome'] = $datiPasseggero['Cognome'];
             $d1['Nome'] = $datiPasseggero['Nome'];
             $d1['SessoId'] = $datiPasseggero['SessoId'];
             $d1['Eta'] = $datiPasseggero['Eta'];
             $d1['TipologiaBigliettoId'] = $datiPasseggero['TipologiaBigliettoId'];
             $TipologiaBigliettoId = $d1['TipologiaBigliettoId'];
             $d1['TipologiaBiglietto'] = $datiPasseggero['TipologiaBiglietto'];
             
             $sql = "Select * from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and TipologiaBigliettoId = ".$datiPasseggero['TipologiaBigliettoId'];
             $datiImporto = $db->query_first($sql);
             $d1['ImportoTitolo'] = $datiImporto['PrezzoPax'];
             $d1['ImportoVenduto'] = $datiImporto['PrezzoPax'];
             /* while($nt<$NumeroBiglietti)
              {*/
             
             //         $progressivo=$this->GetProgressivoTitoloDiViaggio($anno,$PercorsoId); // get codice
             //         $codice=$Identificativo."-".$this->GetCodiceTitoloDiViaggio($anno,$PercorsoId); // get codice
             
             
             $sql="Select gp.IdentificativoBiglietto from Gestore g LEFT JOIN GestoreGruppo gp ON (g.GestoreGruppoId = gp.GestoreGruppoId) where GestoreId=$user->GestoreId";
             $gestore = $db->query_first($sql);
             $IdentificativoBiglietto = $gestore['IdentificativoBiglietto'];
             if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
                 $IdentificativoBiglietto = Config::$identificativoBiglietto;;
             }
             
             $sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = ".$datiPasseggero['TipologiaBigliettoId'];
             $tempTipo = $db->query_first($sql);
             if($tempTipo['OccupaPosto'] == 1){
                 $progressivo = $this->GetProgressivoTitoloDiViaggio();
                 $identificativoServizio = "";
             } else {
                 $progressivo = $this->GetProgressivoTitoloDiViaggioServizi();
                 $identificativoServizio = "E-";
             }
             
             $codice = $identificativoServizio.$IdentificativoBiglietto . '-' . Str_pad($progressivo, 8, "0", STR_PAD_LEFT);
             
             $anno=Date('Y');
             $d1['Anno']=$anno;
             $d1['Codice']=$codice;
             $d1['PrenotazioneId']=$this->Id;
             $d1['TipoTitolo']='E';
             $d1['Progressivo']=$progressivo;
             $d1['PercorsoId']=$PercorsoId;
             $d1['PrenotazioneNumeroId']=$PrenotazioneNumeroId;
             
             if ($tipo=='EP')
                 $d1['PostViaggio']=1;
                 
                 $d1['OpeIns'] = $user->OperatoreId;
                 $d1['SedeIns'] = $user->SedeId;
                 $d1['DataIns'] = $DataTitolo;
                 $d1['IpIns' ]= getenv('REMOTE_ADDR');
                 $d1['OdcIdRef'] = $user->OdcId;
                 $d1['GestoreIdRef'] = $user->GestoreId;
                 $d1['Cancella']=0;
                 
                 
                 $titoloId = $PrenotazioneTitoloId=$db->insert("RT_PrenotazioneTitolo", $d1);
                 $importoVenduto = $d1['ImportoVenduto'];
                 
                 
                 $storico = new StoricoOperazioni();
                 $storico->conn = $db;
                 
                 /*inseriemnto provvigione Titolo agenzia venditore*/
                 $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and BigliettoId=$TipologiaBigliettoId and LineaId=".$LineaId;
                 
                 $row=$db->query_first($sql);
                 $percentuale_a = $row['Percentuale'];
                 $fisso_a = $row['Fisso'];
                 
                 //verifico se ci sono provvigioni avanzate per comune partenza o destinazione
                 //             $sql = "SELECT * FROM RT_ProvvigioneAvanzata
                 // 			        WHERE GestoreId=$user->GestoreId
                 // 			        AND BigliettoId=$TipologiaBigliettoId
                 // 			        AND ((PickupId = ".$ComuneSalitaId." AND DropOffId = 0)
                 //            				OR (DropOffId = ".$ComuneDiscesaId." AND PickupId = 0)
                 //            				OR (PickupId =  ".$ComuneSalitaId." AND DropOffId = ".$ComuneDiscesaId."))
                 //            			AND Stato = 1 AND Cancella = 0 AND '$CorsaDataPartenza' >= Dal AND '$CorsaDataPartenza' <=Al";
                 //             $provvigioneAvanzata = $db->query_first($sql);
                 //             $avanzata_a = 0;
                 //             if(isset($provvigioneAvanzata['ProvvigioneAvanzataId'])){
                 //                 $percentuale_a = $provvigioneAvanzata['Percentuale'];
                 //                 $fisso_a = $provvigioneAvanzata['Fisso'];
                 //                 $avanzata_a = $provvigioneAvanzata['ProvvigioneAvanzataId'];
                 //             }
                 
                 $provv=array();
                 $provv['PrenotazioneTitoloId']=$titoloId;
                 $provv['GestoreId']=$user->GestoreId;
                 
                 $provv['PercentualeAgenzia'] = $percentuale_a;
                 $provv['FissoAgenzia'] = $fisso_a;
                 $provv['ImportoAgenzia'] = $fisso_a+($importoVenduto*$percentuale_a)/100;
                 //$provv['ProvvigioneAvanzataId'] = $avanzata_a;
                 $provv = $storico->operazioni_insert($provv, $user);
                 
                 $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
                 
                 /*inserimento provvigione Titolo per agenzie padri se ci sono*/
                 $finito=false;
                 $gestorePadreId=$user->GestoreId;
                 $conta=0;
                 $gestorePadreIdOld = null;
                 while($finito==false || $conta<100) {
                     $gestoreNew = new Gestore($gestorePadreId);
                     $gestoreNew->conn=$db;
                     $gestoreNew->inizializzaDatiGenerali(null);
                     $gestorePadreIdOld = $gestorePadreId;
                     $gestorePadreId=$gestoreNew->GestoreDatiGenerali['GestorePadre'];
                     
                     if ($gestorePadreId>0 && $gestorePadreIdOld != $gestorePadreId) {
                         $sql="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$gestorePadreId and BigliettoId=$TipologiaBigliettoId and LineaId=1";
                         $row=$db->query_first($sql);
                         $percentuale_a=0;
                         $fisso_a=0;
                         if(!$coupon){
                             if ($row['GestoreConvenzioneId']>0) {
                                 $percentuale_a = $row['Percentuale'];
                                 $fisso_a = $row['Fisso'];
                             }
                             //verifico se ci sono provvigioni avanzate per comune partenza o destinazione
                             $sql = "SELECT * FROM RT_ProvvigioneAvanzata
		        			WHERE GestoreId=$gestorePadreId
		        			AND BigliettoId=$TipologiaBigliettoId
		        			AND ((PickupId = ".$ComuneSalitaId." AND DropOffId = 0)
           						OR (DropOffId = ".$ComuneDiscesaId." AND PickupId = 0)
		           				OR (PickupId =  ".$ComuneSalitaId." AND DropOffId = ".$ComuneDiscesaId."))
		           			AND Stato = 1 AND Cancella = 0 AND '$CorsaDataPartenza' >= Dal AND '$CorsaDataPartenza' <=Al";
                             $provvigioneAvanzata = $db->query_first($sql);
                             $avanzata_a = 0;
                             if(isset($provvigioneAvanzata['ProvvigioneAvanzataId'])){
                                 $percentuale_a = $provvigioneAvanzata['Percentuale'];
                                 $fisso_a = $provvigioneAvanzata['Fisso'];
                                 $avanzata_a = $provvigioneAvanzata['ProvvigioneAvanzataId'];
                             }
                         } else {
                             $percentuale_a = 0;
                             $fisso_a = 0;
                         }
                         
                         $provv=array();
                         $provv['PrenotazioneTitoloId']=$titoloId;
                         $provv['GestoreId']=$gestorePadreId;
                         
                         //$provv['ImportoTitolo'] = $Importi['PrezzoPax'] + ($Importi['AumentoPax'] / $Importi['NumeroPax']) - ($Importi['RiduzionePax'] / $Importi['NumeroPax']);
                         $provv['PercentualeAgenzia'] = $percentuale_a;
                         $provv['FissoAgenzia'] = $fisso_a;
                         $provv['ImportoAgenzia'] = $fisso_a+($importoVenduto*$percentuale_a)/100;
                         $provv['ProvvigioneAvanzataId'] = $avanzata_a;
                         $provv = $storico->operazioni_insert($provv, $user);
                         $provvId=$db->insert("RT_PrenotazioneTitoloProvvigione", $provv);
                     } else {
                         $finito=true;
                     }
                     
                     $conta++;
                 }
                 
                 //       print_r($progressivo."\n");
                 
                 
                 $nt++;
         }
     }
     
     
     public function GeneraCodiciPrenotazione($NumeroBiglietti)
     {
         
         global $user;
         
         $db=$this->conn;
         
         $storico = new StoricoOperazioni();
         $storico->conn = $db;
         
         $PrenotazioneId = $this->Id;
         
         $nt=0;
         
         $sql="Select * from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0";
         $Biglietti = $db->fetch_array($sql);
         
         foreach ($Biglietti as $Biglietto) {
             
             $TipologiaBigliettoId=$Biglietto['TipologiaBigliettoId'];
             
             $sql="Select * from RT_PrenotazionePasseggeri where PrenotazioneId=$PrenotazioneId and TipoBigliettoId=$TipologiaBigliettoId and OdcIdRef=$user->OdcId and Cancella=0";
             $Passeggeri = $db->fetch_array($sql);
             
             foreach ($Passeggeri as $Passeggero) {
                 $d1=null;
                 $d1['PrenotazioneId'] = $PrenotazioneId;
                 $d1['TipologiaBigliettoId'] = $TipologiaBigliettoId;
                 $d1['PasseggeroId'] = $Passeggero['PrenotazionePasseggeroId'];
                 $d1['CodiceQrcode'] = $this->codiceCasuale();
                 
                 $d1 = $storico->operazioni_insert($d1, $user);
                 $lastidA = $db->insert("RT_PrenotazioneNumero", $d1);
             }
         }
     }
     
     function codiceCasuale(){
         $N_Caratteri = 32;
         $Stringa = "";
         for($I=0;$I<$N_Caratteri;$I++){
             do{
                 $N = Ceil(rand(48,122));
             }while(!((($N >= 48) && ($N <= 57)) || (($N >= 65) && ($N <= 90)) || (($N >= 97) && ($N <= 122))));
             $Stringa = $Stringa.Chr ($N);
         }
         return $Stringa;
     }
     
     public function AnnullaPrenotazione($stato, $annullaViaggio = false)
     {
         global $user;
         $db=$this->conn;
         $storico=new StoricoOperazioni();
         $storico->conn=$db;
         $PrenotazioneId=$this->Id;
         $data = null;
         //annullo prenotazione
         if ($stato==10 || $stato==16) {
             $data['NonViaggiata'] = 1;
         }
         $data['PrenotazioneStato'] = $stato;
         $data=$storico->operazioni_update($data,$user);
         
         $result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         
         //annullo percorso
         $data = null;
         $data['PrenotazioneStato'] = $stato;
         $data=$storico->operazioni_update($data,$user);
         $result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         
         if(!$annullaViaggio){
            //annullo biglietti
            $sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneId=$PrenotazioneId";
            $biglietti = $db->fetch_array($sql);
            foreach ($biglietti as $b){
                $data = null;
                $data['Stato'] = 0;
                $data['Cancella'] = 1;
                $data=$storico->operazioni_update($data,$user);
                $result=$db->update("RT_PrenotazioneTitolo", $data,"PrenotazioneTitoloId=".$b['PrenotazioneTitoloId']);
            }
        }
         
     }
     
     public function RimborsaBiglietti()
     {
         
         global $user;
         $db=$this->conn;
         $storico=new StoricoOperazioni();
         $storico->conn=$db;
         $PrenotazioneId=$this->Id;
         
         
         $sql="Select * from RT_PrenotazioneTitolo where PrenotazioneId=$PrenotazioneId and TipoTitolo='E' and OdcIdRef=$user->OdcId and Stato=1 and Cancella=0 order by PrenotazioneTitoloId asc";
         $ArrObject = $db->fetch_array($sql);
         
         $conta=0;
         
         
         while($conta<sizeof($ArrObject))
         {
             $PrenotazioneTitoloId=$ArrObject[$conta]['PrenotazioneTitoloId'];
             $PercorsoId=$ArrObject[$conta]['PercorsoId'];
             $ImportoTitolo=$ArrObject[$conta]['ImportoTitolo'];
             $PrenotazioneNumeroId=$ArrObject[$conta]['PrenotazioneNumeroId'];
             
             $sql="SELECT
		 rt_corsa.CorsaId AS CorsaId,
		 rt_corsa.LineaId AS LineaId,
		 rt_corsa.CorsaNome AS CorsaNome,
		 rt_corsa.AttivaDal AS AttivaDal,
		 rt_corsa.AttivaAl AS AttivaAl,
		 rt_corsa.IncludiFeriale AS IncludiFeriale,
		 rt_corsa.IncludiPrefestivo AS IncludiPrefestivo,
		 rt_corsa.IncludiFestivo AS IncludiFestivo,
		 rt_corsa.OrarioPartenza AS OrarioPartenza,
		 rt_corsa.OrarioArrivo AS OrarioArrivo,
		 rt_corsa.NextDay AS NextDay,
		 rt_corsa.DataIns AS DataIns,
		 rt_corsa.OpeIns AS OpeIns,
		 rt_corsa.SedeIns AS SedeIns,
	 	 rt_corsa.IpIns AS IpIns,
		 rt_corsa.DataAgg AS DataAgg,
		 rt_corsa.OpeAgg AS OpeAgg,
		 rt_corsa.SedeAgg AS SedeAgg,
		 rt_corsa.IpAgg AS IpAgg,
		 rt_corsa.Stato AS Stato,
		 rt_corsa.Cancella AS Cancella,
		 rt_corsa.UpdateCount AS UpdateCount,
		 rt_corsa.OdcIdRef AS OdcIdRef,
		 rt_corsa.GestoreIdRef AS GestoreIdRef,
		 rt_linea.LineaNome AS LineaNome,
		 rt_linea.PercorsoId AS PercorsoId,
		 rt_percorso.PercorsoNome AS PercorsoNome,
		 rt_corsa.CorsaPeso AS CorsaPeso,
		 rt_corsa.VendibileDal AS VendibileDal,
		 rt_corsa.VendibileAl AS VendibileAl,
		 rt_percorso.Identificativo AS Identificativo
		 FROM RT_Corsa AS rt_corsa
		 LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
		 LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
		 WHERE rt_corsa.Cancella = 0
		 AND rt_linea.PercorsoId = $PercorsoId";
             $row = $db->query_first($sql);
             
             $Identificativo=$row['Identificativo'];
             
             $anno=Date('Y');
             $progressivo=$this->GetProgressivoTitoloDiViaggio($anno,$PercorsoId); // get codice
             
             $codice=$Identificativo."-".$this->GetCodiceTitoloDiViaggio($anno,$PercorsoId); // get codice
             $d1['Anno']=$anno;
             $d1['Codice']=$codice;
             $d1['PrenotazioneId']=$this->Id;
             $d1['TipoTitolo']='R';
             $d1['Progressivo']=$progressivo;
             $d1['PercorsoId']=$PercorsoId;
             $d1['ImportoTitolo']=$ImportoTitolo;
             $d1['PrenotazioneNumeroId']=$PrenotazioneNumeroId;
             $d1=$storico->operazioni_insert($d1,$user);
             $lastidA=$db->insert("RT_PrenotazioneTitolo", $d1);
             
             $sql="Select * from RT_PrenotazioneTitoloDettaglio where PrenotazioneTitoloId=$PrenotazioneTitoloId";
             $ArrObject_t = $db->fetch_array($sql);
             
             $contat=0;
             
             
             while($contat<sizeof($ArrObject_t))
             {
                 $d1t['PrenotazioneTitoloId']=$lastidA;
                 $d1t['TipoServizio']=$ArrObject_t[$contat]['TipoServizio'];
                 $d1t['Importo']=($ArrObject_t[$contat]['Importo'])*(-1);
                 $d1t['TipologiaBiglietto']=$ArrObject_t[$contat]['TipologiaBiglietto'];
                 $d1t=$storico->operazioni_insert($d1t,$user);
                 $pp=$db->insert("RT_PrenotazioneTitoloDettaglio", $d1t);
                 
                 $contat++;
             }
             
             
             
             
             $d2['PrenotazioneTitoloIdEmesso']=$PrenotazioneTitoloId;
             $d2['PrenotazioneTitoloIdRimborsato']=$lastidA;
             $d2=$storico->operazioni_insert($d2,$user);
             
             $lastidA=$db->insert("RT_PrenotazioneTitoloRimborsato", $d2);
             
             $conta++;
         }
         
         
         $data['PrenotazioneStato']=7;
         $data=$storico->operazioni_update($data,$user);
         $result=$db->update("RT_PrenotazionePercorso", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         $result=$db->update("RT_Prenotazione", $data,"OdcIdRef=$user->OdcId and PrenotazioneId=$PrenotazioneId");
         
         
         
         
         
     }
     
     public function getStatoPrenotazioneStato($StatoId) {
         global $user;
         $db=$this->conn;
         $PrenotazioneId=$this->Id;
         $sql = "SELECT * from RT_AppPrenotazioneStato where PrenotazioneStatoid=$StatoId";
         $row = $db->query_first($sql);
         $PrenotazioneStato=$row['PrenotazioneStato'];
         return $PrenotazioneStato;
     }
     
     
     public function getStatoPrenotazioneStatoIdByCorsa($CorsaId) {
         global $user;
         $db=$this->conn;
         $PrenotazioneId=$this->Id;
         $sql = "SELECT * from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and CorsaId=$CorsaId and OdcIdRef=$user->OdcId";
         
         $row = $db->query_first($sql);
         $CorsaStato=$row['PrenotazioneStato'];
         
         return $CorsaStato;
     }
     
     
     private function GetProgressivoTitoloDiViaggio()
     {
         $db = $this->conn;
         
         $sql = "SELECT MAX(Progressivo) progressivo from RT_PrenotazioneTitolo where Codice NOT LIKE 'E-%'";
         if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
             $sql .= " AND Codice LIKE '".Config::$identificativoBiglietto."-%'";
         }
         
         $row = $db->query_first($sql);
         
         if ((!empty($row['progressivo'])) and ($row['progressivo'] > 0))
             return $row['progressivo'] + 1;
             
             return 1;
     }
     
     private function GetProgressivoTitoloDiViaggioServizi()
     {
         $db = $this->conn;
         
         $sql = "SELECT MAX(Progressivo) progressivo from RT_PrenotazioneTitolo where Codice LIKE 'E-%'";
         if(strtotime("now") >= strtotime(Config::$dataIdBiglietti)) {
             $sql .= " AND Codice LIKE '%".Config::$identificativoBiglietto."-%'";
         }
         
         $row = $db->query_first($sql);
         
         if ((!empty($row['progressivo'])) and ($row['progressivo'] > 0))
             return $row['progressivo'] + 1;
             
             return 1;
     }
     
     
     
     private function GetCodiceTitoloDiViaggio($y,$PercorsoId) {
         global $user;
         $db=$this->conn;
         $sql = "SELECT Anno, OdcIdRef, MAX(Progressivo) AS AttualeNumero, PercorsoId
	FROM RT_PrenotazioneTitolo
	WHERE Stato = 1
	AND Cancella = 0
	AND Anno = 2015
	AND PercorsoId = 1
	GROUP BY Anno, OdcIdRef, PercorsoId";
         
         $row = $db->query_first($sql);
         $ProgressivoOdc=1;
         if (!empty($row['AttualeNumero']))
             $ProgressivoOdc=$row['AttualeNumero']+1;
             
             $l=strlen($ProgressivoOdc);
             
             
             
             $c=0;
             $ProgressivoOdc_c="";
             while($l<=6)
             {
                 $ProgressivoOdc_c.="0";
                 $l++;
                 
             }
             
             $ProgressivoOdc_c1=$ProgressivoOdc_c."".$ProgressivoOdc;
             
             return $ProgressivoOdc_c1;
             
     }
     public function GetTipologiaPrenotazioneAbilitata($CorsaAndataId,$FermataAndataId, $libera = false)
     {
         global $user;
         $db=$this->conn;
         $Id=$this->Id;
         
		 if($libera) {
			 //tutti i permessi per tipologia prenotazione in caso di prenotazione libera
			return 2;
		 } else {
			 $corsa = new Corsa($CorsaAndataId);
			 $corsa->conn=$db;
			 $corsa->inizializzaDatiGenerali();
			 $arr_corsa=$corsa->DatiGenerali;
			 $lineaId=$arr_corsa['LineaId'];
			 
			 $sql="SELECT
					gestoreconvenzione.ValidaDal AS ValidaDal,
					gestoreconvenzione.ValidaAl AS ValidaAl,
					gestoreconvenzione.LineaId AS LineaId,
					gestoreconvenzione.SoloPrenotazione AS SoloPrenotazione,
					gestoreconvenzione.ListaAttesa AS ListaAttesa,
					gestore.GestoreId AS GestoreId,
					gestore.OdcId AS OdcId,
					gestoreconvenzione.GestoreConvenzioneId AS GestoreConvenzioneId
					FROM Gestore AS gestore
					JOIN GestoreConvenzione AS gestoreconvenzione ON (gestore.GestoreId = gestoreconvenzione.GestoreId)
					WHERE gestore.Stato = 1
					AND gestore.Cancella = 0
					AND gestoreconvenzione.Cancella = 0
					AND gestoreconvenzione.Stato = 1
					AND gestoreconvenzione.ValidaDal <= NOW()
					AND gestoreconvenzione.ValidaAl >= NOW()
					AND gestoreconvenzione.LineaId = $lineaId
					AND gestore.GestoreId = $user->GestoreId";
			 
			 $row = $db->query_first($sql);
			 $r=0;
			 if (!empty($row['GestoreConvenzioneId']))
			 {
				 $SoloPrenotazione=$row['SoloPrenotazione'];
				 if ($SoloPrenotazione==0) {
					 $r=1; // emissione titolo obbligatoria
				 } else {
						 
					 $sql="select * from RT_Fermata where OdcIdRef=$user->OdcId and FermataId=$FermataAndataId";
					 
					 
					 $row = $db->query_first($sql);
					 $IsBlackList=$row['IsBlackList'];
					 
					 if ($IsBlackList==1)
						$r=1; // emissione titolo obbligatoria
					 else
						$r=2; // consentita la sola prenotazione		 
				 }
			 }
			 // nessun permesso
			 return $r;
         }  
     }
     
    public function inizializzaDatiGenerali()
    {
        global $user;
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_Prenotazione WHERE PrenotazioneId=$Id";
        $row = $db->query_first($sql);
        
        if (!empty($row['PrenotazioneId']))
            $this->DatiGenerali=$row;
            else
            {
                print("errore");
                exit();
            }
            
    }
     
     public function inizializzaDettagliCarico()
     {
         global $user;
         $db=$this->conn;
         $Id=$this->Id;
         $sql = "SELECT * From RT_FoglioBusCarico WHERE PrenotazioneId=$Id and OdcIdRef=$user->OdcId";
         $row = $db->query_first($sql);
         
         if (!empty($row['PrenotazioneId']))
             $this->DatiGeneraliCarico=$row;
             else
             {
                 print("errore");
                 exit();
                 
                 
             }
             
     }
     
     
     public function inizializzaDatiGeneraliPercorso($Direzione)
     {
         global $user;
         $db=$this->conn;
         $Id=$this->Id;
         $sql = "SELECT * From RT_PrenotazionePercorso WHERE PrenotazioneId=$Id and Direzione='$Direzione' and OdcIdRef=$user->OdcId";
         
         $row = $db->query_first($sql);
         
         if (!empty($row['PrenotazioneId']))
             $this->DatiGeneraliPercorso=$row;
             else
             {
                 $this->DatiGeneraliPercorso=null;
             }
             
     }
      
     public function CheckCoerenzaAR($DataA, $DataR, $app = false) {
         global $dizionario;
         $to_time = new DateTime($DataR);
         $from_time = new DateTime($DataA);
          
         $errore="";
         $ritornoOpen=$_POST['Prenotazione']['RitornoOpen'];
         // $secondi_residui=round(($to_time->getTimestamp() - $from_time->getTimestamp()));
         if($to_time >= $from_time || $ritornoOpen == 1){
             return "";
         } else {
             if(!isset($DataR) || (isset($DataR) && $DataR == '')){
                 $errore = $dizionario['biglietto']['errore_no_data_r'];
             } else {
                 $errore = $dizionario['biglietto']['errore_minore_data_r'];
             }
             
         }
         return $errore;
     }
     
     
     
     public function CheckNumeroPax($NumeroPax)
     {
         $errore="";
         if (!($NumeroPax>0))
             $errore="Inserire almeno un passeggero \n";
             
             return $errore;
     }
     
     public function CheckDisponibilitaPax($DataPartenza, $CorsaId, $NumeroPaxRichiesti, $Percorso, $pickup = null, $dropoff = null)
     {
         global $user;
         $db=$this->conn;
		 if(isset($_POST['Prenotazione']['RitornoOpen'])) {
			 $ritornoOpen = $_POST['Prenotazione']['RitornoOpen'];
		 } else {
			 $ritornoOpen = 0;
		 }			 
         
         if($ritornoOpen == 0){
             // 		$sql="select PostiRealmenteDisponibili, PostiTotali, LineaId from RT_ViewElencoGestioneOperativita_new where OdcIdRef=$user->OdcId
             // 	             and CorsaId=$CorsaId and AppCalendarioData='$DataPartenza' limit 1" ;
             $sql = "select LineaId from RT_Corsa where CorsaId = $CorsaId";
             $tempL = $db->query_first($sql);
             $LineaId = $tempL['LineaId'];
			 
			 $sql = "select * from RT_Linea where LineaId = ".$LineaId;
			 $tempL = $db->query_first($sql);
			 $tipoTour = $tempL['TipoTour'];
             
             $grafo = new GrafoTratte($LineaId, $CorsaId, $db, $pickup, $dropoff, true);
             $string = '';
             $f = new Fermata();
             $f->conn = $db;
             $first = true;
             foreach($grafo->flotta as $flotta){
                 
                 foreach ($flotta->comuni as $c => $comune){
                     // 				if(count($comune['passeggeri']) > 0) {
                     if(!$f->isInterscambioLinea($LineaId, $comune['comune'])){
                         if($first){
                             $string .= $comune['comune'];
                             $first = false;
                         } else {
                             $string .= ','.$comune['comune'];
                         }
                     }
                     // 				}
                 }
             }
             $sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
					where CorsaId = $CorsaId and DataPartenza = '$DataPartenza' and Comune IN ($string) ";
             
             $tempR = $db->query_first($sql);
			 
			 //recupero posti corsa default
			 $sql = "Select b.TotalePosti
						from RT_TipologiaBus b
						left join RT_Corsa c ON (c.TipologiaBusDefaultId = b.TipologiaBusId)
						where c.CorsaId = $CorsaId";
			 $tempR = $db->query_first($sql);
			 $postiCorsaDefault = $tempR['TotalePosti'];
				 
             if(isset($tempR['Posti'])){                 
                 $postiOccupati = $tempR['Posti'];
                 
                 $sql = "Select TrattaId from RT_DisponibilitaPostiCron
						where CorsaId = $CorsaId and DataPartenza = '$DataPartenza' and Posti = $postiOccupati and Comune IN ($string)";
                 
                 $tratta =  $db->query_first($sql);
                 
                 $sql = "SELECT TipologiaBusDefaultId from RT_Tratta c
					WHERE TrattaId = ".$tratta['TrattaId'];
                 
                 $check = $db->query_first($sql);
                 if(isset($check['TipologiaBusDefaultId']) && $check['TipologiaBusDefaultId']>0) {
                     $sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
    			(select IFNULL((select SUM(c1.NumeroPax)
    			from RT_CorsaPaxTratta c1
    			where
    			c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.TrattaId = ".$tratta['TrattaId']."
				    group by c1.CorsaId , c1.DataPartenza , TrattaId),0))
				   ) AS `PostiTotali`
				from RT_Tratta c
				join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
				where c.TrattaId = ".$tratta['TrattaId'];
                     $tempR1 = $db->query_first($sql);
                 } else {
                     $sql = "Select (`RT_TipologiaBus`.`TotalePosti` +
							(select IFNULL((select SUM(c1.NumeroPax)
							from RT_CorsaPax c1
							where
							c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza'
							group by c1.CorsaId , c1.DataPartenza),0))
							) AS `PostiTotali`
							from RT_Corsa c
							join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
							where c.CorsaId = $CorsaId";
                     $tempR1 = $db->query_first($sql);
                 }
                 
                 $disponibili = intval($tempR1['PostiTotali']) - intval($postiOccupati);
                 $totali = intval($tempR1['PostiTotali']);			 
				 if($tipoTour == 1 && intval($postiOccupati) > 0) {
					 $disponibili = 0;
					 $totali = 1;
				 } else if ($tipoTour == 1 && intval($postiOccupati) == 0) {
					 $disponibili = 1;
					 $totali = 1;
				 }		 
             } else {
                 $sql = "select IFNULL((select
						count(0)
						from
						`RT_PrenotazionePercorso`
						join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
						join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
						and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
						and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
						join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
						left join `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
						left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
						where
						((`RT_Prenotazione`.`Cancella` = 0)
						and (`RT_PrenotazionePercorso`.`Cancella` = 0)
						and (`RT_PrenotazionePercorso`.`Stato` = 1)
						and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
						and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
						and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
						and (`tb`.`OccupaPosto` = 1))
						and `RT_PrenotazionePercorso`.`CorsaId` = $CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataPartenza'
						group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0) as PostiRealmentePrenotati";
                 $tempR1 = $db->query_first($sql);
                 if(isset($tempR1['PostiRealmentePrenotati'])){
                     $postiRealmentePrenotati = $tempR1['PostiRealmentePrenotati'];
                 } else {
                     $postiRealmentePrenotati = 0;
                 }
                 $sql = "select IFNULL((select SUM(c1.NumeroPax)
							from RT_CorsaPax c1
							where
							c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.OdcIdRef = 1
							group by c1.CorsaId , c1.DataPartenza , c1.OdcIdRef),0) as PostiAggiunti";
                 $tempR = $db->query_first($sql);
                 if(isset($tempR['PostiAggiunti'])){
                     $postiCorsaAggiunti = $tempR['PostiAggiunti'];
                 } else {
                     $postiCorsaAggiunti = 0;
                 }
                 
                 
                 $disponibili = $postiCorsaDefault + $postiCorsaAggiunti - $postiRealmentePrenotati;
                 $totali = $postiCorsaDefault + $postiCorsaAggiunti;
				 
				 if($tipoTour == 1 && $postiRealmentePrenotati > 0) {
					 $disponibili = 0;
					 $totali = 1;
				 } else if ($tipoTour == 1 && $postiRealmentePrenotati == 0) {
					 $disponibili = 1;
					 $totali = 1;
				 }
             }
             
			 if($tipoTour == 0) {
				 //controllo posti inizio tratta
				 $inizio = true;
				 if(isset($pickup)) {
					 $sql = "SELECT distinct FermataId, FermataOrario, TrattaNome, TrattaPeso, TrattaId From RT_ElencoFermataOrarioPK
								WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$pickup and TrattaStato=1 order by TrattaPeso desc ";
					 $arr_fermate=$db->fetch_array($sql);
					 $trattaPartenza = $arr_fermate[0]['TrattaId'];
					 
					 $sql = "select MAX(Posti) as Posti from RT_DisponibilitaPostiCron
								where CorsaId = $CorsaId and DataPartenza = '$DataPartenza' and TrattaId = $trattaPartenza ";
					 $tempR = $db->query_first($sql);
					 if(isset($tempR['Posti'])) {
						 $tempOccupatiInizio = $tempR['Posti'];
					 } else {
						 $tempOccupatiInizio = 0;
					 }
					 
					 $sql = "Select ($postiCorsaDefault +
								(select IFNULL((select SUM(c1.NumeroPax)
								from RT_CorsaPaxTratta c1
								where
								c1.Cancella = 0 and c1.CorsaId = $CorsaId and c1.DataPartenza = '$DataPartenza' and c1.TrattaId = ".$trattaPartenza." and c1.OdcIdRef = 1
									group by c1.CorsaId , c1.DataPartenza , TrattaId, c1.OdcIdRef),0))
								   ) AS `PostiTotali`
								from RT_Tratta c
								where c.TrattaId = ".$trattaPartenza;
					
					 $tempR1 = $db->query_first($sql);
					 $tempInizioTot = $tempR1['PostiTotali'];
					 $tempDisponibili = $tempInizioTot - $tempOccupatiInizio;
					 
					 if($tempDisponibili  > 0) {
						 if($tempDisponibili < $disponibili) {
							 $disponibili = $tempDisponibili;
						 } else {
							 $disponibili = $disponibili;
						 }
					 } else {
						 $disponibili = 0;
						 $inizio = false;
					 }
				 } else {
					$inizio = false;
				 }
             }
             
             $row = array();
             $row['LineaId'] = $LineaId;
             $row['PostiRealmenteDisponibili'] = $disponibili;
             $row['PostiTotali'] = $totali;
             
             $err = "Posti non disponibili";
             if (!empty($row['LineaId'])) {
                 $pd = $row['PostiRealmenteDisponibili'];
                 $pcDefault = $row['PostiTotali'];
                 $lineaId = $row['LineaId'];
				 
                 $check = ($pd - $NumeroPaxRichiesti);
                 if (!($check>=0) ){
                     if(isset($pickup) && isset($dropoff) && $inizio){
                         $grafo1 = new DisponibilitaGraph($lineaId, $CorsaId, $DataPartenza, $db, 200, false);
                         $p = $grafo1->getPostiDisponibili($pickup, $dropoff, $row['PostiTotali']);
                         
                         $sql = "select * from RT_MaxDisponibilitaPostiCron where
							CorsaId = $CorsaId and LineaId = $lineaId and DataPartenza = '$DataPartenza'";
                         $rtemp = $db->query_first($sql);
                         if( $p > $row['PostiTotali']){
                             echo "posti occupati: ".$rtemp['Posti']."-------";
                         } else {
                             echo "posti occupati: ".$p."-------";
                         }
                         $t = $pcDefault - $p;
                         if($t <= 0 || ($NumeroPaxRichiesti > $t)){
                             $err="Per la corsa di ".$Percorso." non sono disponibili i posti richiesti. \n";
                         } else {
                             $err="";
                         }
                     } else {
                         $err="Per la corsa di ".$Percorso." non sono disponibili i posti richiesti.\n";
                     }
                 }else{
                     $err="";
                 }
             }
         } else {
             $err = "";
         }
         
         return $err;
         
     }
     
     
     public function CheckDisponibilitaPaxRes($DataPartenza,$CorsaId,$NumeroPaxRichiesti,$Percorso, $pickup = null, $dropoff = null)
     {
         global $user;
         $db=$this->conn;
         $ritornoOpen = $_POST['Prenotazione']['RitornoOpen'];
         if($ritornoOpen == 0){
             // 		$sql="select PostiRealmenteDisponibili, PostiTotali, LineaId from RT_ViewElencoGestioneOperativita_new where OdcIdRef=$user->OdcId
             // 	             and CorsaId=$CorsaId and AppCalendarioData='$DataPartenza' limit 1" ;
             
             
             $sql = "select
		`RT_Corsa`.`LineaId` AS `LineaId`,
		(`RT_TipologiaBus`.`TotalePosti` + (select IFNULL((select SUM(c.NumeroPax)
		from
		RT_CorsaPax c
		where
		c.Cancella = 0
		and c.CorsaId = $CorsaId
		and c.DataPartenza = '$DataPartenza'
		and c.OdcIdRef = $user->OdcId
		group by c.CorsaId , c.DataPartenza , c.OdcIdRef),0))) AS `PostiTotali`,
		
		((`RT_TipologiaBus`.`TotalePosti` + (
		select IFNULL((select SUM(c.NumeroPax)
		from
		RT_CorsaPax c
		where
		c.Cancella = 0
		and c.CorsaId = $CorsaId
		and c.DataPartenza = '$DataPartenza'
		and c.OdcIdRef = $user->OdcId
		group by c.CorsaId , c.DataPartenza , c.OdcIdRef),0)))
		- (
		select IFNULL((select
		COUNT(*) as Tot
		from
		(((((((`RT_PrenotazioneDettaglio` `pd`
		left join `RT_Prenotazione` `p` ON ((`pd`.`PrenotazioneId` = `p`.`PrenotazioneId`)))
		left join `RT_AppPrenotazioneStato` `apps` ON ((`p`.`PrenotazioneStato` = `apps`.`PrenotazioneStatoId`)))
		left join `Operatore` `o` ON ((`p`.`OpeIns` = `o`.`OperatoreId`)))
		left join `Gestore` `g` ON ((`o`.`GestoreId` = `g`.`GestoreId`)))
		left join `RT_PrenotazioneDettaglioCompleto` `rdc` ON (((`p`.`PrenotazioneId` = `rdc`.`PrenotazioneId`)
		and (`rdc`.`PrenotazioneId` = `pd`.`PrenotazioneId`)
		and (`rdc`.`PrenotazioneNumero` = `pd`.`PrenotazioneNumero`)
		and (`rdc`.`DataPartenza` = `pd`.`DataPartenza`))))
		left join `RT_PrenotazioneBiglietto` `pb` ON (((`pb`.`PrenotazioneId` = `p`.`PrenotazioneId`)
		and (`pb`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
		left join `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBigliettoId` = `pb`.`TipologiaBigliettoId`)
		)))
		where
		(apps.OccupaPosti = 1
		and (`pd`.`Escludi` <> 1)
		and (`pd`.`Rimborso` <> 1))
		and `tp`.`OccupaPosto` = 1
		and (`rdc`.Codice not like 'E-%' or `rdc`.Codice is null)
		
		and pd.CorsaId=$CorsaId and pd.DataInizioItinerario='$DataPartenza'),0))) AS `PostiRealmenteDisponibili`
		
		
		from
		`RT_Corsa`
		join `RT_CorsaSettimana` ON (`RT_Corsa`.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
		join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
		join `RT_AppCalendario` ON (`RT_AppSettimana`.`AppSettimanaGiorno` = `RT_AppCalendario`.`GiornoSettimana`)
		
		join `RT_TipologiaBus` ON (`RT_Corsa`.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
		where
		`RT_Corsa`.`Cancella` = 0
		and `RT_AppCalendario`.`AppCalendarioData` >= `RT_Corsa`.`AttivaDal`
		and `RT_AppCalendario`.`AppCalendarioData` <= `RT_Corsa`.`AttivaAl`
		and ((`RT_AppCalendario`.`Feriale` = `RT_Corsa`.`IncludiFeriale`)
		or (`RT_AppCalendario`.`Prefestivo` = `RT_Corsa`.`IncludiPrefestivo`)
		or (`RT_AppCalendario`.`Festivo` = `RT_Corsa`.`IncludiFestivo`))
		
		and `RT_Corsa`.`OdcIdRef`=$user->OdcId and `RT_Corsa`.CorsaId=$CorsaId and `RT_AppCalendario`.`AppCalendarioData`='$DataPartenza'
		group by `RT_Corsa`.`CorsaId` , `RT_AppCalendario`.`AppCalendarioData`";
             
             
             
             $row= $db->query_first($sql);
             
             if (!empty($row['LineaId'])) {
                 $pd = $row['PostiRealmenteDisponibili'];
                 $pcDefault = $row['PostiTotali'];
                 $lineaId = $row['LineaId'];
                 $check=($pd-$NumeroPaxRichiesti);
                 if (!($check>=0)){
                     return 1;
                 }else{
                     return 0;
                 }
             }
         } else {
             return 0;
         }
     }
     
     public function getInterscambioTratte($tratta1,$tratta2,$cm)
     {
         $linea=1;
         $db=$this->conn;
         
         $sql="select FermataPeso from RT_Fermata where  TrattaId=$tratta1 and  ComuneId=$cm" ;
         
         $r = $db->query_first($sql);
         $fermataPeso=$r['FermataPeso'];
         
         $sql="select ComuneId from RT_Fermata where  TrattaId=$tratta1 and IsInterscambio=1 and FermataPeso>$fermataPeso order by FermataPeso asc" ;
         $array1 = $db->fetch_array($sql);
         // echo("<br >".$sql."ho<br />");
         
         $sql="select ComuneId from RT_Fermata where  TrattaId=$tratta2 and IsInterscambio=1 order by FermataPeso asc" ;
         $array2 = $db->fetch_array($sql);
         // echo($sql);
         
         $common = array();
         
         
         foreach ($array1 as $arr) {
             $comId1=$arr['ComuneId'];
             foreach($array2 as $search){
                 $comId2=$search['ComuneId'];
                 
                 if($comId1==$comId2){
                     $common[] = $comId1;
                 }
             }
             
             //print_r($common);
             
         }
         
         
         
         return $common;
         
     }
     
     public function getKmTratta($trattaId)
     {
         $db=$this->conn;
         $sql="select KmTratta from RT_Tratta where  TrattaId=$trattaId" ;
         $ArrObject = $db->query_first($sql);
         $km=$ArrObject['KmTratta'];
         return $km;
         
     }
     
     public function getKmInizioTratta($trattaId,$comuneId)
     {
         $db=$this->conn;
         $sql="select KmInizioTratta from RT_Fermata where  ComuneId=$comuneId and TrattaId=$trattaId" ;
         $ArrObject = $db->query_first($sql);
         $km=$ArrObject['KmInizioTratta'];
         return $km;
         
     }
     
     public function GetKmReali ($arr_tratte,$pickup,$dropoff,$data,$ritorno=0)
     {
         $db=$this->conn;
         
         $sql="select ComuneId,KmInizioTratta from RT_Fermata where  FermataId=$pickup" ;
         
         $ArrObject = $db->query_first($sql);
         $km1=$ArrObject['KmInizioTratta'];
         $cm1=$ArrObject['ComuneId'];
         
         $sql="select ComuneId,KmInizioTratta from RT_Fermata where  FermataId=$dropoff" ;
         $ArrObject = $db->query_first($sql);
         $km2=$ArrObject['KmInizioTratta'];
         $cm2=$ArrObject['ComuneId'];
         
         $d=0;
         $trattaprezzo=array();
         $trattaKm=array();
         $kCorrente=0;
         
         
         if (sizeof($arr_tratte)==1)
         {
             
             $km+=$km2-$km1;
             $trattaKm[$arr_tratte[0]]=$km2-$km1;
         }
         else
         {
             $d=0;
             $trattaC=0;
             $trattaS=0;
             $interscambi=array();
             $comuneInterscambio=0;
             while($d<sizeof($arr_tratte))
             {
                 $trattaC=$arr_tratte[$d]['TrattaId'];
                 if ($d<sizeof($arr_tratte)-1)
                 {
                     $trattaC=$arr_tratte[$d]['TrattaId'];
                     $trattaS=$arr_tratte[$d+1]['TrattaId'];
                     $interscambi=$this->getInterscambioTratte($trattaC, $trattaS,$cm1);
                     $comuneInterscambio=$interscambi[0];
                 }
                 else
                 {
                     $cm1=$comuneInterscambio;
                     $comuneInterscambio=$cm2;
                     
                     
                 }
                 
                 $k1a=$this->getKmInizioTratta($trattaC, $comuneInterscambio);
                 $k1b=$this->getKmInizioTratta($trattaC, $cm1);
                 $k=$k1a-$k1b;
                 
                 $sq="select Comune from Comune where ComuneId=$cm1";
                 $r=$db->query_first($sq);
                 $da=$r['Comune'];
                 
                 $sq="select Comune from Comune where ComuneId=$comuneInterscambio";
                 $r1=$db->query_first($sq);
                 $a=$r1['Comune'];
                 
                 $trattaKm[$trattaC]=$k;
                 $km+=$k;
                 $cm1=$comuneInterscambio;
                 
                 if (!($k>0))
                 {
                     $arr_tratte[$d]=null;
                     unset($arr_tratte[$d]);
                     
                 }
                 
                 $sq="select TrattaNome from RT_Tratta where TrattaId=$trattaC";
                 $r=$db->query_first($sq);
                 $trattaN=$r['TrattaNome'];
                 
                 
                 
                 //  echo("<br /><br />da ".$da." a ".$a." tratta: ".$trattaN." Km: ".$trattaKm[$trattaC]);
                 
                 
                 
                 
                 
                 $d++ ;
             }
             
             
         }

         return $arr_tratte;
     }
     
    public function GetKm ($arr_tratte,$pickup,$dropoff,$data,$ritorno=0) {
		$db=$this->conn;

		$ArrTratte= Array();
		$kmTot=0;
		$i=0;

        if(isset($arr_tratte)) {
			foreach ($arr_tratte as $chiave => $valore) {
				$TrattaId=$valore['TrattaId'] ;
				$Km=$valore['Km'] ;
				$ArrTratte[$TrattaId]=$Km;
				$kmTot+=$Km;
			}
		}

        // Recupero i km di partenza
		$ArrTratte[99999]=$kmTot;
		return $ArrTratte;
}
     
     public function GetKmOld ($arr_tratte,$pickup,$dropoff,$data,$ritorno=0)
     {
         $db=$this->conn;
         
         $sql="select ComuneId,KmInizioTratta from RT_Fermata where  FermataId=$pickup" ;
         
         $ArrObject = $db->query_first($sql);
         $km1=$ArrObject['KmInizioTratta'];
         $cm1=$ArrObject['ComuneId'];
         
         $sql="select ComuneId,KmInizioTratta from RT_Fermata where  FermataId=$dropoff" ;
         $ArrObject = $db->query_first($sql);
         $km2=$ArrObject['KmInizioTratta'];
         $cm2=$ArrObject['ComuneId'];
         
         $d=0;
         $trattaprezzo=array();
         $trattaKm=array();
         $kCorrente=0;
         
         
         if (sizeof($arr_tratte)==1)
         {
             
             $km+=$km2-$km1;
             $trattaKm[$arr_tratte[0]['TrattaId']]=$km2-$km1;
         }
         else
         {
             $d=0;
             $trattaC=0;
             $trattaS=0;
             $interscambi=array();
             $comuneInterscambio=0;
             while($d<sizeof($arr_tratte))
             {
                 $trattaC=$arr_tratte[$d]['TrattaId'];
                 
                 // echo($d." ".(sizeof($arr_tratte)-1));
                 
                 if ($d<sizeof($arr_tratte)-1)
                 {
                     $trattaC=$arr_tratte[$d]['TrattaId'];
                     $trattaS=$arr_tratte[$d+1]['TrattaId'];
                     $interscambi=$this->getInterscambioTratte($trattaC, $trattaS,$cm1);
                     $comuneInterscambio=$interscambi[0];
                 }
                 else
                 {
                     $cm1=$comuneInterscambio;
                     $comuneInterscambio=$cm2;
                     
                     
                 }
                 
                 $k1a=$this->getKmInizioTratta($trattaC, $comuneInterscambio);
                 $k1b=$this->getKmInizioTratta($trattaC, $cm1);
                 $k=$k1a-$k1b;
                 
                 $sq="select Comune from Comune where ComuneId=$cm1";
                 $r=$db->query_first($sq);
                 $da=$r['Comune'];
                 
                 $sq="select Comune from Comune where ComuneId=$comuneInterscambio";
                 $r1=$db->query_first($sq);
                 $a=$r1['Comune'];
                 
                 $trattaKm[$trattaC]=$k;
                 $km+=$k;
                 $cm1=$comuneInterscambio;
                 $sq="select TrattaNome from RT_Tratta where TrattaId=$trattaC";
                 $r=$db->query_first($sq);
                 $trattaN=$r['TrattaNome'];
                 // echo("<br /><br />da ".$da." a ".$a." tratta: ".$trattaN." Km: ".$trattaKm[$trattaC]);
                 
                 $d++ ;
             }
             
             
         }
         
         
         
         
         $trattaKm[99999]=$km;
         
         //print_r($trattaKm);
         
         return $trattaKm;
     }
     
     
     public function getKmSuTrattaInterscambio($TrattaId,$trattaP,$trattaS,&$ComuneIp,&$ComuneId)
     {
         $db=$this->conn;
         $sql="select ComuneId,KmInizioTratta from RT_Fermata where TrattaId=$trattaS and Stato=1 and Cancella=0 order by FermataPeso asc limit 1";
         $rc=$db->query_first($sql);
         $comuneSuccessivo=$rc['ComuneId'];
         $kmComuneSuccessivo=$rc['KmInizioTratta'];
         
         $sql="select ComuneId,KmInizioTratta from RT_Fermata where TrattaId=$TrattaId and Stato=1 and ComuneId=$comuneSuccessivo Cancella=0 order by FermataPeso asc limit 1";
         $rc=$db->query_first($sql);
         $comuneSuccessivo=$rc['ComuneId'];
         $kmComuneSuccessivo=$rc['KmInizioTratta'];
         
     }
     
     
     
     function GetNodi(){
         global $db;
         
         $fermataObj = new Fermata();
         $fermataObj->conn = $db;
         
         $last = $_POST['last'];
         $lineaId = $_POST['LineaId'];
         $comune = $_POST['Comune'];
         $corsaId = $_POST['CorsaId'];
         $dataPartenza = $_POST['DataPartenza'];
         $destinazione = $_POST['Destinazione'];
         
         $gruppo = new LineaGraph($lineaId, $corsaId, $dataPartenza, $db, true);
         $nodes = array();
         $child = array();
         $search = false;
         while($search==false){
             if(sizeof($gruppo->graph->nodes[$comune]->children)>1){
                 if($fermataObj->isInterscambioLinea($lineaId, $comune) || $fermataObj->isInizioTratta($lineaId, $comune)){
                     $tempArray = array();
                     foreach ($gruppo->graph->nodes[$comune]->children as $key=>$c){
                         if(strcmp($c,$destinazione)==0){
                             $tempArray = array();
                             $tempArray[] = $c;
                             $child = array();
                             $search = true;
                         }else if($search == false){
                             if(in_array($destinazione, $gruppo->graph->nodes[$c]->descents)){
                                 $tempArray[] = $c;
                             }
                         }
                     }
                     if(sizeof($tempArray)>1){
                         foreach ($tempArray as $temp){
                             $child[] = $temp;
                         }
                         $search = true;
                     }else{
                         $child = array();
                         $nodes[] = $tempArray[0];
                         $last = $comune;
                         $comune = $tempArray[0];
                     }
                 }else{
                     $trattaId = $fermataObj->getTratta($last, $comune);
                     
                     $tempComune = $fermataObj->getProssimoComune($trattaId, $comune);
                     $nodes[] = $tempComune;
                     $last = $comune;
                     $comune = $tempComune;
                 }
             }else if(sizeof($gruppo->graph->nodes[$comune]->children)==1){
                 foreach ($gruppo->graph->nodes[$comune]->children as $key=>$c){
                     $last = $comune;
                     $comune = $c;
                     $nodes[] = $c;
                     if(strcmp($comune,$destinazione)==0){
                         $search=true;
                         $child = array();
                     }
                 }
             }else{
                 $search = true;
             }
         }
         
         $nodesNomi = array();
         if(sizeof($nodes)>0){
             foreach ($nodes as $index=>$node){
                 $comuneObj = new Comune($node);
                 $comuneObj->conn = $db;
                 $comuneObj->inizializzaDatiGenerali();
                 $nodesNomi[$index] =  $comuneObj->Comune;
             }
         }
         
         $childNomi = array();
         if(sizeof($child)>0){
             foreach ($child as $index=>$node){
                 $comuneObj = new Comune($node);
                 $comuneObj->conn = $db;
                 $comuneObj->inizializzaDatiGenerali();
                 $childNomi[$index] =  $comuneObj->Comune;
             }
         }
         echo json_encode(array('nodes'=>$nodes, 'child'=>$child, 'nodesNomi'=>$nodesNomi, 'childNomi'=>$childNomi));
     }
     
    public function GetTratte($CorsaId, $FermataP, $FermataD)
    {
        global $prenotazione_wizard;
        $db = $this->conn;

        $fer = new Fermata();
        $fer->conn = $db;
        $ComuneIdP = $fer->getComuneIdByFermataId($FermataP);
        $ComuneIdD = $fer->getComuneIdByFermataId($FermataD);

        // Nel caso di un reset del percorso breve ricreo le tratte tramite il grafo
        $sql = "SELECT * FROM RT_PercorsoBreve WHERE ComunePickupId = $ComuneIdP AND ComuneDropOffId = $ComuneIdD AND CorsaId = $CorsaId";
        $r = $db->query_first($sql);

        if (!isset($r['PercorsoBreveId'])) {
            $trattaPartenza = null;
            $trattaArrivo = null;
            $sql = "SELECT LineaId FROM RT_Corsa WHERE CorsaId = $CorsaId";
            $r = $db->query_first($sql);
            $lId = $r['LineaId'];
            $grafo = new GrafoTratte($lId, $CorsaId, $db, $ComuneIdP, $ComuneIdD);
            $TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);

            $pre = new Prenotazione();
            $pre->conn = $db;
            $this->CreatePercorsoBreve($ComuneIdP, $ComuneIdD, $db, $TrattePercorse, $trattaPartenza, $trattaArrivo, $CorsaId, $lId);
        }

        // Recupero delle tratte
        $TrattePercorse = array();
        $sql = "SELECT * FROM RT_PercorsoBreve WHERE ComunePickupId = $ComuneIdP AND ComuneDropOffId = $ComuneIdD AND CorsaId = $CorsaId AND KmPercorsi > 0";
        $r = $db->query_first($sql);
        $q = 0;
        if ($r['PercorsoBreveId'] > 0) {
            $percorsoBreveId = $r['PercorsoBreveId'];
            $sql = "SELECT * FROM RT_PercorsoBreveTratte WHERE PercorsoBreveId = $percorsoBreveId ORDER BY PercorsoBreveTratteId ASC";
            $q = 1;
            $TrattePercorse = $db->fetch_array($sql);
        } else {
            $sql = "SELECT * FROM RT_PrenotazioneTratta WHERE PrenotazioneId = $prenotazione_wizard->Id ORDER BY PrenotazioneTrattaId";
            $TrattePercorse = $db->fetch_array($sql);
            $q = 2;
        }

        $i = 0;
        $ArrTratte = array();
        foreach ($TrattePercorse as $tp) {
            $TrattaId = $tp['TrattaId'];
            $Km = ($q == 2) ? $tp['TrattaKmPercorsi'] : $tp['Km'];

            $sql = "SELECT * FROM RT_Tratta WHERE TrattaId = $TrattaId";
            $row = $db->query_first($sql);
            if (!empty($row['TrattaId'])) {
                $ArrTratte[$i]['TrattaId'] = $TrattaId;
                $ArrTratte[$i]['TrattaPeso'] = $row['TrattaPeso'];
                $ArrTratte[$i]['TrattaNome'] = $row['TrattaNome'];
                $ArrTratte[$i]['Km'] = $Km;
            }
            $i++;
        }

        /*
        // Debug grafo
        $cor = new Corsa($CorsaId);
        $cor->conn = $db;
        $cor->inizializzaDatiGenerali();
        $lineaId = $cor->DatiGenerali['LineaId'];
        $grafo = new GrafoTratte($lineaId, $CorsaId, $db, $ComuneIdP, $ComuneIdD);
        $trattaPartenza = null;
        $trattaArrivo = null;
        $TrattePercorse1 = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);

        $arr_comuni = ($grafo->flotta[0]->percorso);
        $KmTotArco = 0;
        foreach ($arr_comuni as $comuneId) {
            $arr_comu = explode("-", $comuneId);
            $KmArco = $grafo->graph->edges[$comuneId]->peso;
            $da = $arr_comu[0];
            $a = $arr_comu[1];

            $sql = "SELECT Comune FROM Comune WHERE ComuneId = $da";
            $row = $db->query_first($sql);

            $sql = "SELECT Comune FROM Comune WHERE ComuneId = $a";
            $row = $db->query_first($sql);

            $KmTotArco += $KmArco;
            $trattaId = $grafo->graph->edges[$comuneId]->trattaPeso;

            $sql = "SELECT TrattaNome FROM RT_Tratta WHERE TrattaId = $trattaId";
            $row = $db->query_first($sql);
            $TrattaNome = ($row['TrattaNome']);

            // Debug output
        }
        if ($KmTotArco > 0) {
            $d = null;
            $d['KmPercorsi'] = $KmTotArco;
            $db->update("RT_PercorsoBreve", $d, "ComunePickupId = $ComuneIdP AND ComuneDropOffId = $ComuneIdD AND CorsaId = $CorsaId");
        }
        */

        return $ArrTratte;
    }
     
     public function GetListini($arr_tratte,$FermataP,$FermataD,$CorsaId) {
         global $user;
         $db=$this->conn;
         
         
         
         $NumeroTratte=sizeof($arr_tratte);
         $sqlCorsa = "Select RitornoAperto From RT_Corsa Where CorsaId = $CorsaId";
         $tempCorsa = $db->query_first($sqlCorsa);
         if($tempCorsa['RitornoAperto'] == 1){
             foreach ($arr_tratte as $chiave => $valore){
                 $TrattaId = $valore['TrattaId'];
                 $listino_id[$TrattaId]['ListinoId'] = '1';
             }
             return $listino_id;
         }
         
         
         
         if ($NumeroTratte==1){
             //caso in cui il percorso e' composto da una tratta
             
             $TrattaId=$arr_tratte[0]['TrattaId'];
             
             $sql="select distinct ListinoId from RT_CorsaTariffa where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and FermataPickup=$FermataP and FermataDropOff=$FermataD and TrattaId=$TrattaId order by CorsaTariffaId desc" ;
             $ArrObject = $db->fetch_array($sql);
             // $listino_id[$TrattaId]['ListinoId'] = $ArrObject[0]['ListinoId'];
             $listino_id[$TrattaId]['ListinoId']=1;
             return ($listino_id);
         } else {
             //caso in cui il percorso e' composto da piu' tratta
             $TrattaPartenza=$arr_tratte[0]['TrattaId'];
             //recupero del listino della tratta di partenza
             $sql="SELECT
			RT_CorsaTariffa.*
			FROM
			RT_CorsaTariffa
			INNER JOIN RT_Fermata ON RT_CorsaTariffa.FermataDropOff = RT_Fermata.FermataId
			WHERE
			RT_CorsaTariffa.Cancella = 0 AND
			RT_CorsaTariffa.CorsaId = $CorsaId AND
			RT_CorsaTariffa.FermataPickup = $FermataP AND
			RT_CorsaTariffa.TrattaId = $TrattaPartenza ORDER BY
			RT_CorsaTariffa.CorsaTariffaId DESC Limit 1";
             $ArrObject = $db->fetch_array($sql);
             //se non e' stato trovato un listino associato alla tratta prende di default il listino con id 1 (luca)
             if(isset($ArrObject[0]['ListinoId'])){
                 $listino_id[$TrattaPartenza]['ListinoId']=$ArrObject[0]['ListinoId'];
             }else{
                 $listino_id[$TrattaPartenza]['ListinoId']=1;
                 //$listino_id[$TrattaPartenza]['ListinoId']="1";
             }
             
             //recupero del listino delle tratte restanti
             $inizio_array=0;
             $fine_array=sizeof($arr_tratte)-1;
             
             $conta=1;
             
             while ($conta<$fine_array) {
                 $tratta_intermedia=$arr_tratte[$conta]['TrattaId'];
                 
                 $sql="SELECT
				RT_CorsaTariffa.*
				FROM
				RT_CorsaTariffa
				INNER JOIN RT_Fermata ON RT_CorsaTariffa.FermataDropOff = RT_Fermata.FermataId
				WHERE
				(RT_Fermata.IsInterscambio = 1 AND
				RT_Fermata.Cancella = 0 and RT_Fermata.Stato=1) and RT_CorsaTariffa.CorsaId = $CorsaId AND
				    RT_CorsaTariffa.TrattaId = $tratta_intermedia
				ORDER BY
				RT_CorsaTariffa.CorsaTariffaId DESC Limit 1";
                 $ArrObject = $db->fetch_array($sql);
                 //se non e' stato trovato un listino associato alla tratta prende di default il listino con id 1 (luca)
                 if(isset($ArrObject[0]['ListinoId'])){
                     $listino_id[$tratta_intermedia]['ListinoId']=$ArrObject[0]['ListinoId'];
                 } else {
                     $listino_id[$tratta_intermedia]['ListinoId']=1;
                     
                     //$listino_id[$tratta_intermedia]['ListinoId']="1";
                 }
                 $conta++;
             }
             //recupero il listino dell'ultima tratta
             $TrattaDestinazione=$arr_tratte[sizeof($arr_tratte)-1]['TrattaId'];
             $sql="SELECT
			RT_CorsaTariffa.*
			FROM
			RT_CorsaTariffa
			INNER JOIN RT_Fermata ON RT_CorsaTariffa.FermataPickup = RT_Fermata.FermataId
			WHERE
			RT_CorsaTariffa.CorsaId = $CorsaId AND
			RT_CorsaTariffa.TrattaId = $TrattaDestinazione AND
			RT_CorsaTariffa.FermataDropOff = $FermataD and
			RT_CorsaTariffa.Cancella = 0
			ORDER BY RT_Fermata.FermataPeso asc,RT_CorsaTariffa.CorsaTariffaId DESC  limit 1";
             $ArrObject = $db->fetch_array($sql);
             //se non e' stato trovato un listino associato alla tratta prende di default il listino con id 1 (luca)
             if(isset($ArrObject[0]['ListinoId'])){
                 $listino_id[$TrattaDestinazione]['ListinoId']=$ArrObject[0]['ListinoId'];
             }else{
                 //$listino_id[$TrattaDestinazione]['ListinoId']="1";
                 $listino_id[$TrattaDestinazione]['ListinoId']=1;
             }
             return $listino_id;
         }
     }
     
     
     public function CalcolaPrezzo($ArrObject,$arr_listini,$arr_tratte)
     {
         $prezzo_totale="110,25";
         return $prezzo_totale;
         
     }
     
     
     public function ScriviTbTariffe($arr_listini,$arr_tratte,$corsaritorno,$pickup,$dropoff,$DataAndata,$arr_tratte_andata)
     {
         global $user;
         $db=$this->conn;
         $PrenotazioneId=$this->Id;
         $storico=new StoricoOperazioni();
         $storico->conn=$db;
         $conritorno=0;
         
         if(isset($arr_tratte_andata)) {
            $arrTrattePrezzi = $this->GetKm($arr_tratte_andata, $pickup, $dropoff, 0, 0);
         } else {
            $arrTrattePrezzi = $this->GetKm($arr_tratte, $pickup, $dropoff, 0, 0);
         }
         
         $ListinoScontoId=$this->getScontisticaGiornoPrima($DataAndata,1);
         
         if ($corsaritorno>0)
             $conritorno=1;
             
             
             $sql="Select * from RT_PrenotazioneBiglietto where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0";
             $ArrObject = $db->fetch_array($sql);
             
             
             
             $ntb=0;
             
             
             
             while($ntb<sizeof($ArrObject))
             {
                 $TipoBigliettoId=$ArrObject[$ntb]['TipologiaBigliettoId'];
                 $TipoBiglietto=$ArrObject[$ntb]['TipologiaBiglietto'];
                 $NumeroPaxBiglietto=$ArrObject[$ntb]['NumeroPax'];
                 
                 // per ogni tratta
                 $TotalePerTipologia=0;
                 $ntratte=0;
                 while($ntratte<sizeof($arr_tratte))
                 {
                     
                     $TrattaId=$arr_tratte[$ntratte]['TrattaId'];
                     $km = isset($arrTrattePrezzi[$TrattaId]) ? $arrTrattePrezzi[$TrattaId] : 0;
                     
                     $ListinoId=$arr_listini[$TrattaId]['ListinoId'];
                     
                     $sql="Select * from RT_ListinoBiglietto where ListinoId=$ListinoId and BigliettoId=$TipoBigliettoId and  OdcIdRef=$user->OdcId and Cancella=0 order by ListinoBigliettoId desc";
                     
                     
                     $ArrPrezzo = $db->query_first($sql);
                     $PrezzoPax=0;
                     $listinoNome="";
                     
                     if (!empty($ArrPrezzo['ListinoId']))
                     {
                         $PrezzoPax=$ArrPrezzo['Prezzo'];
                         $TotalePerTipologia+=$NumeroPaxBiglietto*$PrezzoPax;
                         $l=new Listino($ListinoId);
                         $l->conn=$db;
                         $l->inizializzaDatiGenerali();
                         $arr_l=$l->DatiGenerali;
                         $listinoNome=$arr_l['ListinoNome'];
                         
                     }
                     $d1=null;
                     unset($d1);
                     
                     
                     $PercSconto=0;
                     $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoId and BigliettoId=$TipoBigliettoId and Stato=1 and Cancella=0";
                     //  echo($sql);
                     $rowsconto = $db->query_first($sql);
                     if (!empty($rowsconto['Prezzo']))
                         $PercSconto=$rowsconto['Prezzo'];
                         
                         //$PrezzoPax=$ArrPrezzo['Prezzo']*$km;
                         if ( ($ListinoScontoId<>0) and (!empty($PercSconto)))
                         {
                             
                             //echo("sono qui");
                             $PrezzoPax=$PrezzoPax+($PercSconto*$PrezzoPax/100);
                             
                         }
                         
                         
                         $d1['PrenotazioneId']=$PrenotazioneId;
                         $d1['TipologiaBigliettoId']=$TipoBigliettoId;
                         $d1['TipologiaBiglietto']=$TipoBiglietto;
                         $d1['ListinoId']=$ListinoId;
                         $d1['ListinoScontoGGprimaId']=$ListinoScontoId;
                         $d1['PercScontoGGprima']=$PercSconto;
                         // $d1['ListinoPrezzo']=$PrezzoPax;
                         $d1['KmTratta']=$km;
                         $d1['PrezzoTratta']=$PrezzoPax;
                         $d1['ListinoPrezzo']=$km*$PrezzoPax;
                         
                         $d1['ListinoNome']=$listinoNome;
                         $d1['TrattaId']=$TrattaId;
                         $d1['ConRitorno']=$conritorno;
                         
                         
                         $d1=$storico->operazioni_insert($d1,$user);
                         $lastidA=$db->insert("RT_PrenotazioneTariffa", $d1);
                         
                         $ntratte++;
                 }
                 
                 // update ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¹
                 /* if (($TotalePerTipologia>0) and ($TipoBigliettoId>0))
                  {
                  $data_upd['PrezzoTotalePax']=$TotalePerTipologia;
                  $db->update("RT_PrenotazioneBiglietto",$data_upd,"TipologiaBigliettoId=$TipoBigliettoId and PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId");
                  
                  
                  }*/
                 
                 
                 
                 
                 
                 $ntb++;
             }
             
             // seleziono gli id delle tipologie di biglietti prenotati
             
             // per ogni tipobiglietto RT_ListinoBiglietto e prendo il prezzo
             
             // per ogni tratta faccio un insert
             
             return true;
             
     }
     
     public function ScriviTbPostoScelto($arr_posti_scelti,$arr_posti_scelti_inf,$CorsaId,$DataPartenza)
     {
         global $user;
         $db=$this->conn;
         $PrenotazioneId=$this->Id;
         $storico=new StoricoOperazioni();
         
         
         $sql="Select * from RT_PrenotazioneNumero where PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId and Cancella=0 order by PrenotazioneNumeroId asc";
         
         $ArrObject = $db->fetch_array($sql);
         
         
         
         $data=$arr_posti_scelti;
         $ck="";
         
         if (sizeof($data>0))
         {
             $quanti=0;
             foreach ($data as $chiave => $valore)
             {
                 
                 $ck=$chiave;
                 $chiave=str_replace("'","",$chiave);
                 $chiave=str_replace("\\","",$chiave);
                 $arr_chiave=explode('_', $chiave);
                 
                 $nPiano=$arr_chiave[0];
                 $nRiga=$arr_chiave[1];
                 $nColonna=$arr_chiave[2];
                 $CorsaSel=$arr_chiave[3];
                 $TipologiaBusId=$arr_chiave[4];
                 $d1=null;
                 $DescrizionePosto="lato finestrino";
                 if ($CorsaSel==$CorsaId)
                 {
                     
                     
                     
                     $sql="Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$nPiano and Riga=$nRiga and Colonna=$nColonna and TipologiaBusId=$TipologiaBusId and OdcIdRef=$user->OdcId";
                     // echo ($sql);
                     
                     $row1 = $db->query_first($sql);
                     $NumeroPosto="";
                     $DescrizionePosto="";
                     
                     if (!empty($row1['TipologiaBusId']))
                     {
                         $NumeroPosto=$row1['NumeroPosto'];
                         $DescrizionePosto=$row1['DescrizionePosto'];
                     }
                     $preferenza_inferiore=$arr_posti_scelti_inf[$ck];
                     
                     
                     $d1['CorsaId']=$CorsaId;
                     $d1['DataPartenza']=$DataPartenza;
                     $d1['Piano']=$nPiano;
                     $d1['PreferenzaPiano']=$preferenza_inferiore;
                     $d1['Riga']=$nRiga;
                     $d1['Colonna']=$nColonna;
                     $d1['Posto']=$valore;
                     $d1['DescrizionePosto']=$DescrizionePosto;
                     $d1['PrenotazioneId']=$PrenotazioneId;
                     $d1['TipoPrenotazione']=1;
                     $d1['PrenotazioneNumeroId']=$ArrObject[$quanti]['PrenotazioneNumeroId'];
                     
                     
                     
                     
                     // print_r($d1);
                     if ($d1['PrenotazioneNumeroId']>0)
                     {
                         $d1=$storico->operazioni_insert($d1,$user);
                         $lastidA=$db->insert("RT_PrenotazionePosto", $d1);
                     }
                 }
                 $quanti++;
                 
             }
         }
         
         
     }
     
     
     public function ScriviTbBiglietti($data,$scrivi,$arr_riduzione,$arr_aumento,$arr_tratte,$arr_listini,$pickup,$dropoff,$DataAndata,$ar1,$arr_tratte_andata)
     {
         global $user;
         $db=$this->conn;
         $PrenotazioneId=$this->Id;
         $storico=new StoricoOperazioni();
         $TotalePrenotazione=0;
         
         $storico->conn=$db;
         $TotaleBiglietti=0;
         /*print_r($arr_tratte);
          print_r($arr_listini);*/
         $ScontoAR=0;
         
         $sql = "SELECT * FROM RT_PrenotazionePercorso WHERE Direzione = 'R' AND PrenotazioneId = $PrenotazioneId";
         $corsaTemp = $db->query_first($sql);
         $sql = "SELECT RitornoAperto FROM RT_Corsa WHERE CorsaId = ".$corsaTemp['CorsaId'];
         $open = $db->query_first($sql);
         $ritornoOpen = $open['RitornoAperto'];
         
         if ($ar1==1)
         {
             if($ritornoOpen == 1){
                 $ScontoAR=0;
             } else {
                 $ScontoAR=Config::$scontoAR;
             }
             
         }
         
         $arrTrattePrezzi=$this->GetKm($arr_tratte, $pickup, $dropoff, 0, 0);
         // echo($DataAndata." ".$scrivi."<br />");
         
         $ListinoScontoId1=0;
         $ListinoScontoId1=$this->getScontisticaGiornoPrima($DataAndata,1);
         
         
         foreach ($data as $chiave => $valore) {
             $chiave=str_replace("'","",$chiave);
             $chiave=str_replace("\\","",$chiave);
             
             
             $arr_chiave=explode('_', $chiave);
             
             $d1=null;
             $TipoBigliettoId=$arr_chiave[0];
             $DescrizioneBiglietto=$arr_chiave[1];
             $pax=$valore;
             $d1['PrenotazioneId']=$PrenotazioneId;
             $d1['TipologiaBigliettoId']=$TipoBigliettoId;
             $d1['TipologiaBiglietto']=$DescrizioneBiglietto;
             $d1['NumeroPax']=$pax;
             
             if ($valore>0) {
                 
                 $tot_aumento=$arr_aumento[$TipoBigliettoId];
                 $tot_riduzione=$arr_riduzione[$TipoBigliettoId];
                 
                 if ($scrivi)
                 {
                     $d1['RiduzionePax']=$tot_riduzione;
                     $d1['AumentoPax']=$tot_aumento;
                     
                     
                     $ntratte=0;
                     $PrezzoPax=0;
                     $PrezzoAndata=0;
                     $PrezzoRitorno=0;
                     $PrezzoPaxArr=array();
                     
                     while($ntratte<sizeof($arr_tratte))
                     {
                         $TrattaId=$arr_tratte[$ntratte]['TrattaId'];
                         $km=$arrTrattePrezzi[$TrattaId];
                         $ListinoId=$arr_listini[$TrattaId]['ListinoId'];
                         $sql="Select * from RT_ListinoBiglietto where ListinoId=$ListinoId and BigliettoId=$TipoBigliettoId and  OdcIdRef=$user->OdcId and Cancella=0";
                         
                         $ArrPrezzo = $db->query_first($sql);
                         
                         $listinoNome="";
                         
                         if (!empty($ArrPrezzo['ListinoId']))
                         {
                             $PercSconto=0;
                             $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoId1 and BigliettoId=$TipoBigliettoId and Stato=1 and Cancella=0";
                             //  echo($sql);
                             $rowsconto = $db->query_first($sql);
                             if (!empty($rowsconto['Prezzo']))
                                 $PercSconto=$rowsconto['Prezzo'];
                                 
                                 //$PrezzoPax=$ArrPrezzo['Prezzo']*$km;
                                 $Pr=$ArrPrezzo['Prezzo']*$km;
                                 
                                 if (empty($PrezzoPaxArr[$BigliettoId][$TrattaId]))
                                     $PrezzoAndata+=$Pr;
                                     else
                                         $PrezzoRitorno+=$Pr;
                                         
                                         $Pr=$Pr-($ScontoAR*$Pr/100);
                                         if ( ($ListinoScontoId1<>0) and (!empty($PercSconto)))
                                         {
                                             //echo("sono qui");
                                             $Pr=$Pr-($PercSconto*$Pr/100);
                                         }
                                         $PrezzoPaxArr[$TipoBigliettoId][]=$Pr;
                                         $PrezzoPax+=$Pr;
                         }
                         //  print_r($PrezzoPaxArr);
                         
                         $c=0;
                         $PrezzoPax=0;
                         while($c<  sizeof($PrezzoPaxArr))
                         {
                             $arrTipo=$PrezzoPaxArr[$TipoBigliettoId];
                             $d=0;
                             while($d<sizeof($arrTipo))
                             {
                                 $PrezzoPax+=$arrTipo[$d];
                                 $d++;
                             }
                             
                             
                             
                             $c++;
                         }
                         $ntratte++;
                     }
                     $d1['PercScontoAR']=$ScontoAR;
                     $d1['PerScontoPromozioneGGprima']=$PercSconto;
                     $d1['ListinoIdPromozioneGGprima']=$ListinoScontoId1;
                     $d1['PrezzoPax']=$PrezzoPax;
                     $d1['PrezzoAndata']=$PrezzoAndata;
                     $d1['PrezzoRitorno']=$PrezzoRitorno;
                     $d1['PrezzoARpieno']=$PrezzoAndata+$PrezzoRitorno;
                     
                     $d1['PrezzoBasePax']=$PrezzoPax*$pax;
                     $d1['PrezzoTotalePax']=$d1['PrezzoBasePax']-$tot_riduzione+$tot_aumento;
                     
                     $d1=$storico->operazioni_insert($d1,$user);
                     
                     $TotalePrenotazione+=$d1['PrezzoTotalePax'];
					 
					 if ($d1['PrezzoPax'])
						$lastidA=$db->insert("RT_PrenotazioneBiglietto", $d1);
                 }
                 
				 
                     
				 //conteggio il numero di posti se il tipo di biglietto occupa posto
				 $sql = "select OccupaPosto from RT_TipologiaBiglietto where TipologiaBigliettoId = $TipoBigliettoId";
				 $rowTemp = $db->query_first($sql);
				 
				 if($rowTemp['OccupaPosto'] == 1){
					 $TotaleBiglietti+=$valore;
				 }
                     
             }
         }
         
         $data_upd['TotalePaxPrenotati'] = $TotaleBiglietti;
         $data_upd['TotalePrenotazione'] = $TotalePrenotazione;
         $data_upd['TotaleResiduo'] = $TotalePrenotazione;
         
         if ($scrivi){
             $db->update("RT_Prenotazione",$data_upd,"PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId");
         }
         
         return $TotaleBiglietti;
     }
     
     
     public function ScriviTbTratte($CorsaId, $arrTratte, $TrattaDirezione, $gestore = null)
     {
         global $user;
         $db=$this->conn;
         $PrenotazioneId=$this->Id;
         $storico=new StoricoOperazioni();
         $storico->conn=$db;

         $i=0;
         while ($i< sizeof($arrTratte))
         {
             $TrattaId=$arrTratte[$i]['TrattaId'];
             $tratta=new Tratta($TrattaId);
             $tratta->conn=$db;
             $tratta->inizializzaDatiGenerali();
             $TrattaNome=$tratta->DatiGenerali['TrattaNome'];
             $TrattaPeso=$tratta->DatiGenerali['TrattaPeso'];
             $TrattaNodo=$tratta->DatiGenerali['NodoPeso'];
             
             $prenotazione_tratte=null;
             unset($prenotazione_tratte);
             $prenotazione_tratte['PrenotazioneId']=$PrenotazioneId;
             $prenotazione_tratte['TrattaId']=$TrattaId;
             $prenotazione_tratte['TrattaNome']=$TrattaNome;
             $prenotazione_tratte['TrattaPeso']=$TrattaPeso;
             $prenotazione_tratte['TrattaNodo']=$TrattaNodo;
             $prenotazione_tratte['TrattaDirezione']=$TrattaDirezione;
             $prenotazione_tratte['CorsaId']=$CorsaId;
             $prenotazione_tratte['TrattaKmPercorsi']=$arrTratte[$i]['Km'];
             
             
             
             $chiave=$TrattaId."_".$CorsaId."_".$TrattaDirezione;
             //$prenotazione_tratte['TrattaNote']=$arrTrattaNote[$chiave];
             $prenotazione_tratte = $storico->operazioni_insert($prenotazione_tratte,$user);
			 if(isset($gestore)) {
				$prenotazione_tratte['GestoreIdRef'] = $gestore;
			 }
             $lastidA=$db->insert("RT_PrenotazioneTratta", $prenotazione_tratte);
             //print_r($prenotazione_tratte);
             
             $i++;
         }
         
         return true;
     }
     
     
     
     
	public function ScriviTbPercorso($CorsaId,$Pickup,$DropOff,$DataCorsa,$Direzione,$StatoPrenotazione,$arr_note,$NumeroTotalePax, $gestore = null)
	{
         
         global $user;
         $db=$this->conn;
         $PrenotazioneId=$this->Id;
         $storico=new StoricoOperazioni();
         $storico->conn=$db;
         
         
         $corsa=new Corsa($CorsaId);
         $corsa->conn=$db;
         $corsa->inizializzaDatiGenerali();
         $arr_corsa=$corsa->DatiGenerali;
         $CorsaNome=$arr_corsa['CorsaNome'];
         $OrarioPartenza=$arr_corsa['OrarioPartenza'];
         
         $linea = new Linea($arr_corsa['LineaId']);
         $linea->conn=$db;
         $linea->inizializzaDatiGenerali();
         $arr_linea=$linea->DatiGenerali;
         
         $percorso=new Percorso($arr_linea['PercorsoId']);
         $percorso->conn=$db;
         $percorso->inizializzaDatiGenerali();
         $arr_percorso=$percorso->DatiGenerali;
         
         $fermata=new Fermata($Pickup);
         $fermata->conn=$db;
         $fermata->inizializzaDatiGenerali();
         $arr_fermata=$fermata->DatiGenerali;
         
         $fermata1=new Fermata($DropOff);
         $fermata1->conn=$db;
         $fermata1->inizializzaDatiGenerali();
         $arr_fermata1=$fermata1->DatiGenerali;
         
         $data_prenotazione_percorso=null;
         $data_prenotazione_percorso['CorsaId']=$CorsaId;
         $data_prenotazione_percorso['LineaId']=$arr_linea['LineaId'];
         $data_prenotazione_percorso['PercorsoId']=$arr_percorso['PercorsoId'];
         $data_prenotazione_percorso['PrenotazioneId']=$PrenotazioneId;
         $data_prenotazione_percorso['Direzione']=$Direzione;
         $data_prenotazione_percorso['CorsaNome']=$CorsaNome;
         $data_prenotazione_percorso['CorsaDataPartenza']=$DataCorsa;
         $data_prenotazione_percorso['CorsaOrarioPartenza']=$OrarioPartenza;
         $data_prenotazione_percorso['ComuneSalitaId']=$arr_fermata['ComuneId'];
         $data_prenotazione_percorso['ComuneSalita']=$arr_fermata['Comune'];
         $data_prenotazione_percorso['ComuneDiscesaId']=$arr_fermata1['ComuneId'];
         $data_prenotazione_percorso['ComuneDiscesa']=$arr_fermata1['Comune'];
         $data_prenotazione_percorso['LineaNome']=$arr_fermata['LineaNome'];
         $data_prenotazione_percorso['PercorsoNome']=$arr_fermata['PercorsoNome'];
         $data_prenotazione_percorso['FermataSalitaId']=$Pickup;
         $data_prenotazione_percorso['FermataSalita']=$arr_fermata['FermataNome'];
         $data_prenotazione_percorso['FermataDiscesaId']=$DropOff;
         $data_prenotazione_percorso['FermataDiscesa']=$arr_fermata1['FermataNome'];
         // determina stato prenotazione
         $data_prenotazione_percorso['PrenotazioneStato']=$StatoPrenotazione;
         
         $sql="select KmPercorsiAndata,KmPercorsiRitorno from RT_Prenotazione where PrenotazioneId=$PrenotazioneId";
         
         $r=$db->query_first($sql);
         $KmA=$r['KmPercorsiAndata'];
         $KmR=$r['KmPercorsiRitorno'];
         $KmPercorsi=0;
         
         if ($Direzione=='A')
             $KmPercorsi=$KmA;
		 elseif($Direzione=='R')
             $KmPercorsi=$KmR;
             
             
		 $orario=new Orario();
		 $orario->conn=$db;
		 
		 $arr_orario_salita=$orario->getOrarioByCorsaFermata($CorsaId, $Pickup);
		 $arr_orario_discesa=$orario->getOrarioByCorsaFermata($CorsaId, $DropOff);
		 
		 $giorni_agg_partenza=$arr_orario_salita['GiorniAggiuntivi'];
		 $orario_partenza=$arr_orario_salita['Orario'];
		 
		 $giorni_agg_arrivo=$arr_orario_discesa['GiorniAggiuntivi'];
		 $orario_arrivo=$arr_orario_discesa['Orario'];
		 
		 $dt=new DT($DataCorsa,"Y-m-d");
		 $dt->addDays($giorni_agg_partenza);
		 $DataPartenza=$dt->getDate();
		 $DataOraPartenza=$DataPartenza." ".$orario_partenza;
		 
		 
		 $dt=new DT($DataCorsa,"Y-m-d");
		 $dt->addDays($giorni_agg_arrivo);
		 $DataArrivo=$dt->getDate();
		 $DataOraArrivo=$DataArrivo." ".$orario_arrivo;
		 
		 $data_prenotazione_percorso['DataOraSalita']=$DataOraPartenza;
		 $data_prenotazione_percorso['DataOraDiscesa']=$DataOraArrivo;
		 $data_prenotazione_percorso=$storico->operazioni_insert($data_prenotazione_percorso,$user);
		 $data_prenotazione_percorso['KmPercorsi']=$KmPercorsi;
		 
		 if(isset($gestore)) {
			$data_prenotazione_percorso['GestoreIdRef'] = $gestore;
		 }
		 
		 $lastidA=$db->insert("RT_PrenotazionePercorso", $data_prenotazione_percorso);
		 
		 //$xx=$this->GeneraCodiciPrenotazione($NumeroTotalePax, $lastidA);
		 
		 if(isset($arr_note[$CorsaId."_1"])) {
            $data_nota['Nota'] = $arr_note[$CorsaId."_1"];
         } else {
            $data_nota['Nota'] = $arr_note["_1"];
         }
		 $data_nota['TipoNota'] = 'S';
		 $data_nota['PrenotazioneId'] = $PrenotazioneId;
		 $data_nota['PrenotazionePercorsoId'] = $lastidA; 
		 if (!empty($data_nota['Nota'])) {
			$idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
         }
			 
         if(isset($arr_note[$CorsaId."_2"])) {
            $data_nota['Nota'] = $arr_note[$CorsaId."_2"];
         } else {
            $data_nota['Nota'] = $arr_note["_2"];
         }
		 $data_nota['TipoNota'] = 'D';
		 $data_nota['PrenotazioneId'] = $PrenotazioneId;
		 $data_nota['PrenotazionePercorsoId'] = $lastidA;		 
		 if (!empty($data_nota['Nota'])) {
			 $idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
         }
		
         if(isset($arr_note[$CorsaId."_3"])) {
            $data_nota['Nota'] = $arr_note[$CorsaId."_3"];
         } else {
            $data_nota['Nota'] = $arr_note["_3"];
         }
		 $data_nota['TipoNota'] = 'B';
		 $data_nota['PrenotazioneId'] = $PrenotazioneId;
		 $data_nota['PrenotazionePercorsoId'] = $lastidA;
		 if (!empty($data_nota['Nota'])) {
			 $idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
         }
		
         if(isset($arr_note[$CorsaId."_4"])) {
            $data_nota['Nota'] = $arr_note[$CorsaId."_4"];
         } else {
            $data_nota['Nota'] = $arr_note["_4"];
         }
		 $data_nota['TipoNota'] = 'P';
		 $data_nota['PrenotazioneId'] = $PrenotazioneId;
		 $data_nota['PrenotazionePercorsoId'] = $lastidA;
		 if (!empty($data_nota['Nota'])) {
			 $idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
         }
			 
         if(isset($arr_note[$CorsaId."_5"])) {
            $data_nota['Nota'] = $arr_note[$CorsaId."_5"];
         } else {
            $data_nota['Nota'] = $arr_note["_5"];
         }
		 $data_nota['TipoNota'] = 'G';
		 $data_nota['PrenotazioneId'] = $PrenotazioneId;
		 $data_nota['PrenotazionePercorsoId'] = $lastidA;
		 if (!empty($data_nota['Nota'])) {
			 $idnote = $db->insert("RT_PrenotazionePercorsoNote", $data_nota);
         }
			 
		 return true;
	}
     
     public function SettaId($PrenotazioneId)
     {
         $this->Id=$PrenotazioneId;
         return true;
         
     }
     
     
     function GetTipologiaBigliettiPrezziFaseModifica($scrivi,$PrenotazioneId,$prenotazione_wizard,$Data,$CorsaId,$FermataIdAP,$FermataIdAD,$CorsaRitornoId,$FermataIdRP,$FermataIdRD,$TV,$arr_biglietti_prenotati,$arr_aumento,$arr_riduzione,$PrenotazioneIdOld1,$prenotazioneOldMarcs = null, $statoOld = null,$DataRitorno=null, $listinoScontiGPold=null, $listinoScontiGPoldR=null, $TipoTour = 0)
     {
         $scrivi=0;
         $PrenotazioneId=0;
         
         global $user;
         $db=$this->conn;
         $storico=new StoricoOperazioni();
         $storico->conn=$db;
         $PrenotazioneIdOld=$PrenotazioneId;
         $PercSconto=0;
         $PercScontoR=0;
         $arrPrezziBiglietto=array();
         $ar=1;
         $ar1=1;
         $molt=1;

         $libera = null;

         if ($TV==1)
         {
             $ar1=0;
             $ar=0;
         }
         $fer=new Fermata();
         $fer->conn=$db;
         
         $Cp=$fer->getComuneIdByFermataId($FermataIdAP);
         $Cr=$fer->getComuneIdByFermataId($FermataIdAD);
         
         $sql = "SELECT RitornoAperto FROM RT_Corsa WHERE CorsaId = $CorsaRitornoId";
         $open = $db->query_first($sql);
         $ritornoOpen = $open['RitornoAperto'];
         
         //$PrenotazioneId=0;
         
         //recupero dati modifica
         $mod = 0;
         $DataPartenzaOriginale = null;
         $DataRitornoOriginale = null;
         $PrenotazionePercorsoIdAndata = 0;
         $PrenotazionePercorsoIdRitorno = 0;
         //echo($PrenotazioneId." aa".$PrenotazioneIdOld1."<br />");
         if (isset($PrenotazioneId) && $PrenotazioneId != 0) {
             $PrenotazioneObj = new Prenotazione($PrenotazioneId);
             $PrenotazioneObj->conn = $db;
             $PrenotazioneObj->inizializzaDatiGenerali();
             $DatiGeneraliArr = $PrenotazioneObj->DatiGenerali;
             $Multi = $DatiGeneraliArr['Multi'];
             
             $DataIns = null;
             $sqlData = "SELECT DataIns FROM RT_Prenotazione WHERE PrenotazioneId = ".$prenotazione_wizard->Id;
             $rowData = $db->query_first($sqlData);
             if(isset($rowData['DataIns'])){
                 $DataIns = $rowData['DataIns'];
             } else {
                 $DataIns = null;
             }
         }
         
         $biglietti = array();
         
         if ($PrenotazioneId>0)
         {
             $PrenotazioneIdOld=$PrenotazioneIdOld1;
             if(!isset($PrenotazioneIdOld)){
                 $PrenotazioneIdOld = $prenotazioneOldMarcs;
             }
             //              echo "prenotazione oldId = $PrenotazioneIdOld";
             $mod=1;
             $prenotazione_wizard->inizializzaDatiGenerali();
             $DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
             $TipoViaggioOriginale = $DatiGeneraliArr['TipoViaggioId'];
             $Multi = $DatiGeneraliArr['Multi'];
             $Stato = $DatiGeneraliArr['PrenotazioneStato'];
             $ritornoOpenOriginale = $DatiGeneraliArr['RitornoOpen'];
             
             $prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
             $DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
             $DataPartenzaOriginale = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
             //print_r($DatiGeneraliPercorsoArr);
             
             
             if ($TipoViaggioOriginale==2)
             {
                 $prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
                 $DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso;
                 $DataRitornoOriginale=$DatiGeneraliPercorsoArr['CorsaDataPartenza'];
             }
             
             $sql = "SELECT pd.TipologiaBigliettoId, pd.TipologiaBiglietto, td.TipologiaBigliettoPeso
 			FROM RT_PrenotazioneBiglietto pd
			left join RT_TipologiaBiglietto td on (pd.TipologiaBigliettoId = td.TipologiaBigliettoId)
 			WHERE pd.PrenotazioneId = $prenotazione_wizard->Id 
 			order by tb.OccupaPosto desc, tb.TipologiaBigliettoPeso";
             
             $biglietti = $db->fetch_array($sql);
             
         }
         
         //prendo le tratte attraversate ed i listini associati
         
         $arr_tratte = $this->GetTratte($CorsaId, $FermataIdAP, $FermataIdAD);
         
         if ($CorsaRitornoId > 0){
             $arr_tratter = $this->GetTratte($CorsaRitornoId, $FermataIdRP, $FermataIdRD);
             //  	echo "<br>tratte ritorno"; print_r($arr_tratter);
         }
         $arr_listini = $this->GetListini($arr_tratte,$FermataIdAP,$FermataIdAD,$CorsaId);
         
         if ($CorsaRitornoId > 0){
             $arr_listinir = $this->GetListini($arr_tratter,$FermataIdRP,$FermataIdRD,$CorsaRitornoId);
             //  		echo "<br>listino ritorno"; print_r($arr_listini);
         }
         
         $readonly="";
         if ($user->SedeLegale!=1)
             $readonly="readonly";
             
             $at=0;
             
             while ($at < sizeof($arr_tratte))
             {
                 
                 if ($at > 0)
                     $tratteid .= ",".$arr_tratte[$at]['TrattaId'];
                     else
                         $tratteid = $arr_tratte[$at]['TrattaId'];
                         
                         $at++;
             }
             
             //Prendo i biglietti validi
             $ar=0;
             //$sql="select distinct TipologiaBigliettoId,TipologiaBiglietto, TipologiaBigliettoPeso from RT_ViewPrenotazioneTipoBiglietti where TrattaId In($tratteid) and AR=$ar and '$Data' >= ValiditaBigliettoDal and '$Data' <= ValiditaBigliettoAl";
             
             $escludiTIpoBiglietto="tb.TipologiaBigliettoId<>11 and ";
             
             if (($user->GestoreId==1) or ($user->GestoreId==2))
                 $escludiTIpoBiglietto="";
                 
                 $sql = "select tb.TipologiaBigliettoId, tb.TipologiaBiglietto, tb.TipologiaBigliettoPeso, tb.OccupaPosto
						  from RT_TipologiaBiglietto tb
						  left join RT_ValiditaBigliettoDettaglio vbd on (tb.TipologiaBigliettoId = vbd.BigliettoId)
						  left join RT_ValiditaBiglietto vb on (vb.ValiditaBigliettoId = vbd.ValiditaBigliettoId)
						  where $escludiTIpoBiglietto tb.AR=$ar and '$Data' >= vb.Dal and '$Data' <= vb.Al and vb.CorsaId = $CorsaId and tb.Stato = 1
						  and TipoTour = $TipoTour
						  order by tb.OccupaPosto desc, tb.TipologiaBigliettoPeso";
              
                 $ArrObject = $db->fetch_array($sql);
                 
                 //unsco i biglietti gi� prenotati ai biglietti validi
                 $ArrObject = array_merge($ArrObject, $biglietti);
                 $ArrObject = array_map('unserialize', array_unique(array_map('serialize', $ArrObject)));
                 
                 //funzione di comparazione per l'ordinamento
                 function cmp1($a, $b) {
                     if (($a['OccupaPosto'] < $b['OccupaPosto']) || ($a['OccupaPosto'] == $b['OccupaPosto'] && $a['TipologiaBigliettoPeso'] > $b['TipologiaBigliettoPeso'])) {
                         return 1;
                     } else if(($a['OccupaPosto'] > $b['OccupaPosto']) || ($a['OccupaPosto'] == $b['OccupaPosto'] && $a['TipologiaBigliettoPeso'] < $b['TipologiaBigliettoPeso'])) {
                         return -1;
                     } else {
                         return 0;
                     }
                     return 0;
                 }
                 
                 //ordino i biglietti per la visualizzazione
                 usort($ArrObject, 'cmp1');
                 
                 $KmAndata=0;
                 $KmRitorno=0;
                 $i=0;
                 $numerobiglietti=0;
                 $prenotazione=new Prenotazione();
                 $prenotazione->conn=$db;
                 $kmarr=$prenotazione->GetKM($arr_tratte,$FermataIdAP,$FermataIdAD,0,0);
                 
                 $km=$kmarr[99999];
                 $ListinoMoltiplicatore = $this->ListinoMoltiplicatore($km, null);
                 
                 $KmAndata=$km;
                 if ($CorsaRitornoId>0)
                 {
                     $kmarrR=$prenotazione->GetKM($arr_tratter,$FermataIdRP,$FermataIdRD,0,0);
                     $kmR=$kmarrR[99999];
                     $KmRitorno=$kmR;
                 }
                 
                 $ListinoScontoId = $prenotazione->getScontisticaGiornoPrima($Data,1);
                 if(isset($listinoScontiGPold)){
                     $ListinoScontoId = $listinoScontiGPold;
                 }
                 
                 $arr_tratte_andata=$arr_tratte;
                 $n_tratte_andata=sizeof($arr_tratte_andata);
                 if ($CorsaRitornoId>0)
                 {
                     
                     $c=0;
                     while($c<sizeof($arr_tratter))
                     {
                         $arr_tratte[]=$arr_tratter[$c];
                         $TrattaId=$arr_tratter[$c]['TrattaId'];
                         $arr_listini[$TrattaId]=$arr_listinir[$TrattaId];
                         $kmarr[$TrattaId]=$kmarrR[$TrattaId];
                         $c++;
                     }
                     $c=0;
                     
                 }
                 
                 $ricalcolo=0;
                 
                 //die($ListinoScontoId);
                 $ScontoAR=0;
                 if ($ar1==1) {
                     if($ritornoOpen == 1){
                         $ScontoAR=0;
                     } else {
                         $ScontoAR=Config::$scontoAR;
                     }
                     
                 }
                 
                 $PercScontoOld1=0;
                 $TotalePrenotazione=0;
                 $TotalePax=0;
                 
                 $totpax=0;
                 $PrezzoTrattaArr=array();
                 
                 //biglietti
                 while ($i< sizeof($ArrObject))
                 {
                     $BigliettoId = $ArrObject[$i]['TipologiaBigliettoId'];
                     $BigliettoDescr = $ArrObject[$i]['TipologiaBiglietto'];
                     $stringa = $BigliettoId."_".$BigliettoDescr;
                     
                     $ntratte = 0;
                     $PrezzoPax = 0;
                     $PrezzoPaxArr = array();
                     $PrezzoAndata = 0;
                     $PrezzoRitorno = 0;
                     
					 
                     if($ArrObject[$i]['OccupaPosto'] == 1 && !$libera){
                         //biglietti non occupa posto
                         
                         //prezzo dell'andata
                         if(isset($listinoScontiGPold) && $listinoScontiGPold>0 && !$this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId)){
                             $ListinoScontoId = $listinoScontiGPold;
                         } else {
                             $ListinoScontoId = $prenotazione->GetScontoPromozioneAttiva($CorsaId, $Data, 1, $BigliettoId);
                         }
                         
                         $PercSconto=0;
                         $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoId and BigliettoId=$BigliettoId and Stato=1 and Cancella=0";
                         $rowsconto = $db->query_first($sql);
                         if (!empty($rowsconto['Prezzo'])) {
                             $PercSconto = $rowsconto['Prezzo'];
                         }
                         
                         $sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = $CorsaId AND TipologiaBigliettoId = $BigliettoId AND FermataPickup = $Cp AND FermataDropoff = $Cr";
                         $tempTariffa = $db->query_first($sql);
                         $PrezzoPax = $tempTariffa['Tariffa'];
                         $PrezzoAndata = $PrezzoPax;
                         
                         //prezzo ritorno
                         if($CorsaRitornoId > 0){
                             $PercScontoR=0;
                             $ListinoScontoIdR = 0;
                             if(isset($listinoScontiGPoldR) && $listinoScontiGPoldR>0 && !$this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId)){
                                 $ListinoScontoIdR = $listinoScontiGPoldR;
                             } else {
                                 $ListinoScontoIdR=$prenotazione->GetScontoPromozioneAttiva($CorsaRitornoId, $DataRitorno, 1, $BigliettoId);
                             }
                             if($ListinoScontoIdR == '') {
                                 $ListinoScontoIdR = 0;
                             }
                             $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoIdR and BigliettoId=$BigliettoId and Stato=1 and Cancella=0";
                             $rowsconto = $db->query_first($sql);
                             if (!empty($rowsconto['Prezzo']))
                                 $PercScontoR=$rowsconto['Prezzo'];
                                 
                                 $sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = $CorsaRitornoId AND TipologiaBigliettoId = $BigliettoId AND FermataPickup = $Cr AND FermataDropoff = $Cp";
                                 $tempTariffa = $db->query_first($sql);
                                 $PrezzoRitorno = $tempTariffa['Tariffa']; // + ($tempTariffa['Tariffa'] * $PercScontoR /100);
                         }
                     } else if($ArrObject[$i]['OccupaPosto'] == 0){
                         //biglietto per servizio

                         if(isset($TrattaId) && isset($arr_listini[$TrattaId]['ListinoId'])) {
                            $ListinoId = $arr_listini[$TrattaId]['ListinoId'];
                         } else {
                             $ListinoId = null;
                         }
                         
                         if (!empty($ArrPrezzo['ListinoId']))
                         {
                             
                             $ListinoScontoId=$prenotazione->GetScontoPromozioneAttiva($CorsaId, $Data, 1, $BigliettoId);
                             
                             $PercSconto=0;
                             $PercScontoNew=0;
                             $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoId and BigliettoId=$BigliettoId and Stato=1 and Cancella=0";
                             
                             $rowsconto = $db->query_first($sql);
                             if (!empty($rowsconto['Prezzo'])) {
                                 $PercSconto=$rowsconto['Prezzo'];
                                 $PercScontoNew=$PercSconto;
                             }
                             
                             if ($CorsaRitornoId>0) {
                                 
                                 $PercScontoR=0;
                                 
                                 $ListinoScontoIdR=$prenotazione->GetScontoPromozioneAttiva($CorsaRitornoId, $DataRitorno, 1, $BigliettoId);
                                 
                                 $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoIdR and BigliettoId=$BigliettoId and Stato=1 and Cancella=0";
                                 $rowsconto = $db->query_first($sql);
                                 if (!empty($rowsconto['Prezzo']))
                                     $PercScontoR=$rowsconto['Prezzo'];
                                     
                                     //$PercSconto=$PercSconto+$PercScontoR;
                             }
                             
                         }
                         
                         $ScontoAR=0;
                         if ($ar1==1)
                         {
                             //$km=$km*2;
                             if($ritornoOpen == 1){
                                 $ScontoAR=0;
                             } else {
                                 $ScontoAR=Config::$scontoAR;
                             }
                             
                         }
                         
                         $sql = "Select * from RT_ListinoServizi where BigliettoId = $BigliettoId and CorsaId = 0";
                         $rowServizio =  $db->query_first($sql);
                         $PrezzoAndata = $rowServizio['Prezzo'];
                         if($CorsaRitornoId > 0){
                             if($ritornoOpen == 1){
                                 $PrezzoRitorno = $PrezzoAndata;
                             } else {
                                 $sql = "Select * from RT_ListinoServizi where BigliettoId = $BigliettoId and CorsaId = 0";
                                 $rowServizio =  $db->query_first($sql);
                                 $PrezzoRitorno = $rowServizio['Prezzo'];
                             }
                         }
                         $PrezzoPax = $PrezzoAndata + $PrezzoRitorno;
                     }
                     
                     // 	echo "$PrezzoPax $PrezzoAndata $PrezzoRitorno"; die();
                     $sql = "select * from RT_PrenotazioneBiglietto where TipologiaBigliettoId=$BigliettoId and PrenotazioneId=$PrenotazioneIdOld and OdcIdRef=$user->OdcId and cancella=0";
                     $rowtipo = $db->query_first($sql);
                     
                     //   	echo $sql;echo "<br>";
                     
                     $pax = 0;
                     $riduzione = 0;
                     $aumento = 0;
                     $riduzioneNote = '';
                     $aumentoNote = '';
                     $base = 0;
                     $finale = 0;
                     
                     if (isset($arr_biglietti_prenotati)) {
                         
                         foreach ($arr_biglietti_prenotati as $chiave => $valore) {
                             $chiave = str_replace("'","",$chiave);
                             $chiave = str_replace("\\","",$chiave);
                             
                             
                             $arr_chiave = explode('_', $chiave);
                             $TpBiglietto = $arr_chiave[0];
                             
                             if ($BigliettoId == $TpBiglietto)
                             {
                                 $DescrizioneBiglietto = $arr_chiave[1];
                                 $pax = $valore;
                                 
                                 if ($valore > 0)
                                 {
                                     $aumento = floatval(str_replace(',', '.', $arr_aumento[$TpBiglietto]['Valore']));
                                     $aumentoNote = isset($arr_aumento[$TpBiglietto]['Note']) ? $arr_aumento[$TpBiglietto]['Note'] : '';
                                     $riduzione = floatval(str_replace(',', '.', $arr_riduzione[$TpBiglietto]['Valore']));
                                     $riduzioneNote = isset($arr_riduzione[$TpBiglietto]['Note']) ? $arr_riduzione[$TpBiglietto]['Note'] : '';
                                 }
                             }
                         }
                     }
                     else{
                         
                         if (!empty($rowtipo['TipologiaBigliettoId']))
                         {
                             $pax = $rowtipo['NumeroPax'];
                             $totpax = $totpax + $pax;
                             
                             
                             if (!isset($arr_aumento)) {
                                 $aumento = $rowtipo['AumentoPax'];
                                 $aumentoNote = $rowtipo['AumentoPaxNote'];
                             }
                             
                             if (!isset($arr_riduzione)) {
                                 $riduzione = $rowtipo['RiduzionePax'];
                                 $riduzioneNote = $rowtipo['RiduzionePaxNote'];
                             }
                             
                             
                             $PrezzoAndataOld = $PrezzoAndata;
                             
                             
                         }
                     }
                     
                     
                     if (( ($pax>0) || ( ($pax==0) && ($scrivi==0))) && ($PrezzoPax>=0))
                     {
                         if ($PrezzoPax>=0){
                             $ListinoMoltiplicatore1=$ListinoMoltiplicatore;
                             if ($molt==0)
                                 $ListinoMoltiplicatore1=1;
                                 $PrezzoAndata=$PrezzoAndata*$ListinoMoltiplicatore1;
                                 $PrezzoRitorno=$PrezzoRitorno*$ListinoMoltiplicatore1;
                                 
                                 $PrezzoAndata=$PrezzoAndata+$PrezzoAndata*$PercSconto/100;
                                 $PrezzoRitorno=$PrezzoRitorno+$PrezzoRitorno*$PercScontoR/100;
                                 
                                 $PrezzoAndata=$this->arrotonda($PrezzoAndata);
                                 $PrezzoRitorno=$this->arrotonda($PrezzoRitorno);
                                 
                                 $prezzoTotaleAR=$PrezzoAndata+$PrezzoRitorno;
                                 $kmTotale=$KmAndata+$KmRitorno;
                                 if ($ScontoAR>0)
                                     $PrezzoPax=$prezzoTotaleAR-$prezzoTotaleAR*$ScontoAR/100;
                                     else
                                         $PrezzoPax=$prezzoTotaleAR;
                                         
                                         // echo ($PrezzoPax."<br />");
                                         
                                         // $PrezzoPax=$PrezzoPax+$PrezzoPax*$PercSconto/100;
                                         //  echo ($PrezzoPax."*".$PercSconto."<br />");
                                         $PrezzoPax=$this->arrotonda($PrezzoPax);
                                         
                                         $base=$PrezzoPax*$pax;
                                         
                                         //se si sta modificando una prenotazione confronto il prezzo precedente con quello ricalcolato
                                         if (!empty($rowtipo['TipologiaBigliettoId'])){
                                             $PrezzoAndataOld1 = 0;
                                             $PrezzoRitornoOld1 = 0;
                                             $ScontoAROld1 = 0;
                                             
                                             $PrezzoPaxOld1 =  0;
                                             $baseOld1=0;
                                             $finaleOld1=0;
                                             $ListinoMoltiplicatoreOld=0;
                                             
                                             $molt=1;
                                             
                                             // 	            $change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId);
                                             // 	            if($Stato!=3 && isset($statoOld) && $statoOld!=3){
                                             // 	            	$change = true;
                                             // 	            }
                                             
                                             if($Stato!=3 && isset($statoOld) && $statoOld!=3 ){
                                                 // 	            	echo "A";
                                                 // 	            	$change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId);
                                                 if($scrivi == 1 && $TV != $TipoViaggioOriginale){
                                                     $change = true;
                                                     // 	            		echo "B";
                                                 }
                                             } else if($Stato==3 || (isset($statoOld) && $statoOld==3) || $Stato==4){
                                                 if($ritornoOpenOriginale == 1 && $ritornoOpen == 0){
                                                     $change = false;
                                                     // 	            		echo "S";
                                                 }else{
                                                     $change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId);
                                                     if($scrivi == 1){
                                                         // 		            		echo "C";
                                                         $change = true;
                                                     } else {
                                                         // 		            		echo "D";
                                                         
                                                         $change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId);
                                                         // 		            		$change = false;
                                                     }
                                                 }
                                             } else {
                                                 // 	            	echo "E";
                                                 if($Stato==1){
                                                     $change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId);
                                                 } else {
                                                     
                                                     if(isset($DataIns) && $DataIns <= Config::$dataVersione){
                                                         $change = false;
                                                     } else {
                                                         $change = true;
                                                     }
                                                 }
                                             }
                                             if(!$change){
                                                 //parametri della prenotazione non modificati
                                                 $PrezzoAndata=  $rowtipo['PrezzoAndata'];
                                                 $PrezzoRitorno=  $rowtipo['PrezzoRitorno'];
                                                 $ScontoAR=  $rowtipo['PercScontoAR'];
                                                 $PercSconto=  $rowtipo['PerScontoPromozioneGGprima'];
                                                 //  echo("qui qui");
                                                 $PrezzoPax=  $rowtipo['PrezzoPax'];
                                                 if($Stato!=3 && isset($statoOld) && $statoOld!=3){
                                                     $base=$PrezzoPax*$pax;
                                                     $finale=$PrezzoPax*$pax;
                                                 } else {
                                                     $base=$rowtipo['PrezzoBasePax'];
                                                     $finale=$rowtipo['PrezzoTotalePax'];
                                                 }
                                                 $molt=0;
                                                 // $ListinoMoltiplicatore=$rowtipo['Moltiplicatore'];
                                             }else{
                                                 //parametri della prenotazione non modificati
                                                 //if (!intval($Multi)) {
                                                 $PrezzoAndataOld1=  $rowtipo['PrezzoAndata'];
                                                 $PrezzoRitornoOld1=  $rowtipo['PrezzoRitorno'];
                                                 $ScontoAROld1=  $rowtipo['PercScontoAR'];
                                                 
                                                 $PercScontoOld1=  $rowtipo['PerScontoPromozioneGGprima'];
                                                 $PrezzoPaxOld1=  $rowtipo['PrezzoPax'];
                                                 $baseOld1=$rowtipo['PrezzoBasePax'];
                                                 $finaleOld1=$rowtipo['PrezzoTotalePax'];
                                                 $ListinoMoltiplicatoreOld=$rowtipo['Moltiplicatore'];
                                                 //}
                                                 
                                                 if (($Stato==3 || $statoOld==3) && $baseOld1>$base){
                                                     $base = $baseOld1;
                                                     $PrezzoAndata = $PrezzoAndataOld1;
                                                     $PrezzoRitorno = $PrezzoRitornoOld1;
                                                     $ScontoAR = $ScontoAROld1;
                                                     $PercSconto = $PercScontoOld1;
                                                     $PrezzoPax = $PrezzoPaxOld1;
                                                     $ListinoMoltiplicatore = $ListinoMoltiplicatoreOld;
                                                     $ListinoMoltiplicatore1 = $ListinoMoltiplicatoreOld;
                                                 }
                                                 
                                             }
                                         }
                         }else{
                             $base = 0;
                             $PrezzoAndata = 0;
                             $PrezzoRitorno = 0;
                             $ScontoAR = 0;
                             $PercSconto = 0;
                             $PrezzoPax = 0;
                             $ListinoMoltiplicatore = 0;
                             $ListinoMoltiplicatore1 = 0;
                         }
                         
                         $finale_complete = $base + $aumento - $riduzione;
                         $finale = $this->arrotonda($finale_complete);
                         
                         $arrPrezziBiglietto[$numerobiglietti]['Pax']=$pax;
                         $arrPrezziBiglietto[$numerobiglietti]['BigliettoId']=$BigliettoId;
                         $arrPrezziBiglietto[$numerobiglietti]['DescrizioneBiglietto']=$BigliettoDescr;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoAndata']=$PrezzoAndata;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoRitorno']=$PrezzoRitorno;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoTotaleAR']=$prezzoTotaleAR;
                         $arrPrezziBiglietto[$numerobiglietti]['PromozioneId']=$ListinoScontoId;
                         $arrPrezziBiglietto[$numerobiglietti]['PromozioneSconto']=$PercSconto;
                         $arrPrezziBiglietto[$numerobiglietti]['ScontoAR']=$ScontoAR;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoBasePax']=$base;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoPax']=$PrezzoPax;
                         $arrPrezziBiglietto[$numerobiglietti]['Aumento']=$aumento;
                         $arrPrezziBiglietto[$numerobiglietti]['Riduzione']=$riduzione;
                         $arrPrezziBiglietto[$numerobiglietti]['Totale']=$finale;
                         $arrPrezziBiglietto[$numerobiglietti]['RiduzioneNote']=$riduzioneNote;
                         $arrPrezziBiglietto[$numerobiglietti]['AumentoNote']=$aumentoNote;
                         
                         
                         
                         
                         $numerobiglietti++;
                     }
                     
                     $i++;
                     
                 }
                 $arrPrezziBiglietto[9999]=$KmAndata+$KmRitorno;
                 
                 
                 
                 
                 return ($arrPrezziBiglietto);
     }
     
     //funzione di comparazione per l'ordinamento
     private function cmp($a, $b) {
         if (($a['OccupaPosto'] < $b['OccupaPosto']) || ($a['OccupaPosto'] == $b['OccupaPosto'] && $a['TipologiaBigliettoPeso'] > $b['TipologiaBigliettoPeso'])) {
             return 1;
         } else if(($a['OccupaPosto'] > $b['OccupaPosto']) || ($a['OccupaPosto'] == $b['OccupaPosto'] && $a['TipologiaBigliettoPeso'] < $b['TipologiaBigliettoPeso'])) {
             return -1;
         } else {
             return 0;
         }
         return 0;
     }
     
     function GetTipologiaBigliettiPrezzi($scrivi,$PrenotazioneId,$prenotazione_wizard,$Data,$CorsaId,$FermataIdAP,$FermataIdAD,$CorsaRitornoId,$FermataIdRP,$FermataIdRD,$TV,$arr_biglietti_prenotati,$arr_aumento,$arr_riduzione,$PrenotazioneIdOld1,$prenotazioneOldMarcs = null, $statoOld = null,$DataRitorno=null, $listinoScontiGPold=null, $listinoScontiGPoldR=null, $TipoTour = 0, $libera = false, $ComunePId = null, $ComuneDId = null, $gestoreIdRef = null)
     {
         global $user;
         $db = $this->conn;
         $storico = new StoricoOperazioni();
         $storico->conn=$db;
         $PrenotazioneIdOld = $PrenotazioneId;
         $PercSconto = 0;
         $PercScontoR = 0;
         $arrPrezziBiglietto=array();
         $ar=1;
         $ar1=1;
         $molt=1;
         if ($TV==1)
         {
             $ar1=0;
             $ar=0;
         }
         $fer=new Fermata();
         $fer->conn=$db;
         if (!$libera) {
			$Cp = $fer->getComuneIdByFermataId($FermataIdAP);
			$Cr  =$fer->getComuneIdByFermataId($FermataIdAD);
		 } else {
			$Cp = $ComunePId;
			$Cr = $ComuneDId;
		 }
		 if(!$libera) {
			 $sql = "SELECT RitornoAperto FROM RT_Corsa WHERE CorsaId = $CorsaRitornoId";
			 $open = $db->query_first($sql);
			 $ritornoOpen = $open['RitornoAperto'];
		 } else {
			 $ritornoOpen = false;
		 }
		 
         //recupero dati modifica
         $mod = 0;
         $DataPartenzaOriginale = null;
         $DataRitornoOriginale = null;
         $PrenotazionePercorsoIdAndata = 0;
         $PrenotazionePercorsoIdRitorno = 0;
         //echo($PrenotazioneId." aa".$PrenotazioneIdOld1."<br />");
         if (isset($PrenotazioneId) && $PrenotazioneId != 0) {
             $PrenotazioneObj = new Prenotazione($PrenotazioneId);
             $PrenotazioneObj->conn = $db;
             $PrenotazioneObj->inizializzaDatiGenerali();
             $DatiGeneraliArr = $PrenotazioneObj->DatiGenerali;
             $Multi = $DatiGeneraliArr['Multi'];
             
             $DataIns = null;
             $sqlData = "SELECT DataIns FROM RT_Prenotazione WHERE PrenotazioneId = ".$prenotazione_wizard->Id;
             $rowData = $db->query_first($sqlData);
             if(isset($rowData['DataIns'])){
                 $DataIns = $rowData['DataIns'];
             } else {
                 $DataIns = null;
             }
         }
         $biglietti = array();
         if (is_object($prenotazione_wizard)) {
             $PrenotazioneIdOld=$PrenotazioneIdOld1;
             if(!isset($PrenotazioneIdOld)){
                 $PrenotazioneIdOld = $prenotazioneOldMarcs;
             }
             
             $mod=1;
             $prenotazione_wizard->inizializzaDatiGenerali();
             $DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
             $TipoViaggioOriginale = $DatiGeneraliArr['TipoViaggioId'];
             $Multi = $DatiGeneraliArr['Multi'];
             $Stato = $DatiGeneraliArr['PrenotazioneStato'];
			 $LiberaOld = $DatiGeneraliArr['Libera'];
             $ritornoOpenOriginale = $DatiGeneraliArr['RitornoOpen'];
             
             $prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
             $DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
             $DataPartenzaOriginale = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
             
             if ($TipoViaggioOriginale==2)
             {
                 $prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
                 $DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso;
                 $DataRitornoOriginale=$DatiGeneraliPercorsoArr['CorsaDataPartenza'];
             }
             
			 $sql = "SELECT pd.TipologiaBigliettoId, pd.TipologiaBiglietto, tb.TipologiaBigliettoPeso, tb.OccupaPosto
			 			FROM RT_PrenotazioneBiglietto pd
						left join RT_TipologiaBiglietto tb on (pd.TipologiaBigliettoId = tb.TipologiaBigliettoId)
			 			WHERE pd.PrenotazioneId = $prenotazione_wizard->Id
			 			order by tb.OccupaPosto desc, tb.TipologiaBigliettoPeso ";
             $biglietti = $db->fetch_array($sql); 
         }
         //prendo le tratte attraversate ed i listini associati
         if(!$libera && !isset($FermataIdAP) || !isset($FermataIdAD) || $FermataIdAP == "" || $FermataIdAD == ""){
             echo "Nessuna fermata selezionata"; die();
         }
		 
		 $arr_tratte = null;
		 $arr_tratter = null;
		 $arr_listini = null;
		 $arr_listinir = null;
		if(!$libera) {
			$arr_tratte = $this->GetTratte($CorsaId, $FermataIdAP, $FermataIdAD);
			if ($CorsaRitornoId > 0){
				if(!isset($FermataIdRP) || $FermataIdRP == "" || !isset($FermataIdRD) || $FermataIdRD == ''){
					echo "Nessuna fermata selezionata"; die();
				}
				$arr_tratter = $this->GetTratte($CorsaRitornoId, $FermataIdRP, $FermataIdRD);
			}

			$arr_listini = $this->GetListini($arr_tratte,$FermataIdAP,$FermataIdAD,$CorsaId);

			if ($CorsaRitornoId > 0){
				$arr_listinir = $this->GetListini($arr_tratter,$FermataIdRP,$FermataIdRD,$CorsaRitornoId);
			}
		}
         
         $readonly="";
         if ($user->SedeLegale!=1)
             $readonly="readonly";
             
             $at=0;
             
             while ($at < sizeof($arr_tratte))
             {
                 
                 if ($at > 0)
                     $tratteid .= ",".$arr_tratte[$at]['TrattaId'];
                     else
                         $tratteid = $arr_tratte[$at]['TrattaId'];
                         
                         $at++;
             }
              
             //Prendo i biglietti validi
             $ar=0;

             $escludiTIpoBiglietto="tb.TipologiaBigliettoId<>11 and ";
             if (($user->GestoreId==1) or ($user->GestoreId==2)) {
                 $escludiTIpoBiglietto="";
			 }
			 
			 $corsaWhereSql = " and vb.CorsaId = $CorsaId ";
			 if($libera) {
				 //se la prenotazione e' libera recupero le tipologie biglietto attive senza controllare la corsa e data
				 $sql = "select tb.TipologiaBigliettoId, tb.TipologiaBiglietto, tb.TipologiaBigliettoPeso, tb.OccupaPosto
						  from RT_TipologiaBiglietto tb
						  where $escludiTIpoBiglietto tb.AR=$ar and tb.Stato = 1
						  and TipoTour = $TipoTour AND tb.TipologiaBigliettoId NOT IN (44, 45, 55)
						  order by tb.OccupaPosto desc, tb.TipologiaBigliettoPeso ";
			 } else {
				
				//effettuo la conversione del formato data se necessario
				if ($this->isValidDateFormat($Data)) {
					$dateParts = explode('/', $Data);
					$Data = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
				}
				 
				 //se la prenotazione e' standatd recupero le tipologie biglietto effettuando il controllo sulla validita per data per la corsa selezionata
				 $sql = "select tb.TipologiaBigliettoId, tb.TipologiaBiglietto, tb.TipologiaBigliettoPeso, tb.OccupaPosto
						  from RT_TipologiaBiglietto tb
						  left join RT_ValiditaBigliettoDettaglio vbd on (tb.TipologiaBigliettoId = vbd.BigliettoId)
						  left join RT_ValiditaBiglietto vb on (vb.ValiditaBigliettoId = vbd.ValiditaBigliettoId)
						  where $escludiTIpoBiglietto tb.AR=$ar and '$Data' >= vb.Dal and '$Data' <= vb.Al and vb.CorsaId = $CorsaId and tb.Stato = 1
						  and TipoTour = $TipoTour
						  order by tb.OccupaPosto desc, tb.TipologiaBigliettoPeso ";
			 }
			 
                 $ArrObject = $db->fetch_array($sql);
                 
                 //unisco i biglietti gia prenotati ai biglietti validi
                 $ArrObject = array_merge($ArrObject, $biglietti);
                 $ArrObject = array_map('unserialize', array_unique(array_map('serialize', $ArrObject)));
                 
                 //ordino i biglietti per la visualizzazione
                 usort($ArrObject,  array($this,'cmp'));
                 
                 $KmAndata=0;
                 $KmRitorno=0;
                 $i=0;
                 $numerobiglietti=0;
                 $prenotazione=new Prenotazione();
                 $prenotazione->conn=$db;
                 
                 $kmarr = $prenotazione->GetKM($arr_tratte,$FermataIdAP,$FermataIdAD,0,0);
                 
                 $km=$kmarr[99999];
                 $ListinoMoltiplicatore = 1;
                 
                 $KmAndata=$km;
                 if ($CorsaRitornoId>0)
                 {
                     $kmarrR=$prenotazione->GetKM($arr_tratter,$FermataIdRP,$FermataIdRD,0,0);
                     $kmR=$kmarrR[99999];
                     $KmRitorno=$kmR;
                 }
                 
				 if(!$libera) {
					 $ListinoScontoId = $prenotazione->getScontisticaGiornoPrima($Data,1);
					 if(isset($listinoScontiGPold) && $listinoScontiGPold>0){
						 $ListinoScontoId = $listinoScontiGPold;
					 }
				 } else {
					$ListinoScontoId = null;
				 }
				 
                 $arr_tratte_andata=$arr_tratte;
                 $n_tratte_andata=sizeof($arr_tratte_andata);
                 if ($CorsaRitornoId>0)
                 {
                     $c=0;
                     while($c<sizeof($arr_tratter))
                     {
                         $arr_tratte[]=$arr_tratter[$c];
                         $TrattaId=$arr_tratter[$c]['TrattaId'];
                         $arr_listini[$TrattaId]=$arr_listinir[$TrattaId];
                         $kmarr[$TrattaId]=$kmarrR[$TrattaId];
                         $c++;
                     }
                     $c=0;
                 }
                 
                 $ScontoAR=0;
                 if ($ar1==1) {
                     if($ritornoOpen == 1){
                         $ScontoAR=0;
                     } else {
                         $ScontoAR=Config::$scontoAR;
                     }
                 }
                 
                 $PercScontoOld1=0;
                 $TotalePrenotazione=0;
                 $TotalePax=0;
                 
                 $totpax=0;

                 //biglietti
                 while ($i< sizeof($ArrObject))
                 {
                     $PercSconto = 0;
                     $PercScontoR = 0;
					 
                     $BigliettoId = $ArrObject[$i]['TipologiaBigliettoId'];
                     $BigliettoDescr = $ArrObject[$i]['TipologiaBiglietto'];
                   
                     $PrezzoPax = 0;
                     $PrezzoAndata = 0;
                     $PrezzoRitorno = 0;
					 
					 if($ArrObject[$i]['OccupaPosto'] == 1 && !$libera){
						 //biglietti occupa posto (passeggeri)
						if(!$libera) { 
						//caso di prenotazione normale
							 //prezzo dell'andata
							 if(isset($listinoScontiGPold) && $listinoScontiGPold>0 && !$this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId)){
								 $ListinoScontoId = $listinoScontiGPold;
							 } else {
								 $ListinoScontoId = $prenotazione->GetScontoPromozioneAttiva($CorsaId, $Data, 1, $BigliettoId);
							 }
							 
							 
							 $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoId and BigliettoId=$BigliettoId and Stato=1 and Cancella=0";
							 
							 $rowsconto = $db->query_first($sql);
							 if (!empty($rowsconto['Prezzo'])) {
								 $PercSconto = $rowsconto['Prezzo'];
							 }
							 
							 $sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = $CorsaId AND TipologiaBigliettoId = $BigliettoId AND FermataPickup = $Cp AND FermataDropoff = $Cr";
							 
							 $tempTariffa = $db->query_first($sql);
							 $PrezzoPax = $tempTariffa['Tariffa'];
							 $PrezzoAndata = $PrezzoPax;
							 
							 //prezzo ritorno
                             $ListinoScontoIdR = null;
							 if($CorsaRitornoId > 0){
								 $PercScontoR=0;
								 if(isset($listinoScontiGPoldR) && $listinoScontiGPoldR>0 && !$this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId)){
									 $ListinoScontoIdR = $listinoScontiGPoldR;
								 } else {
									 $ListinoScontoIdR=$prenotazione->GetScontoPromozioneAttiva($CorsaRitornoId, $DataRitorno, 1, $BigliettoId);
								 }
								 $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoIdR and BigliettoId=$BigliettoId and Stato=1 and Cancella=0";
								 $rowsconto = $db->query_first($sql);
								 if (!empty($rowsconto['Prezzo'])){
									 $PercScontoR=$rowsconto['Prezzo'];
								 }
								 
								 $sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE CorsaId = $CorsaRitornoId AND TipologiaBigliettoId = $BigliettoId AND FermataPickup = $Cr AND FermataDropoff = $Cp";
								 $tempTariffa = $db->query_first($sql);
								 $PrezzoRitorno = $tempTariffa['Tariffa'] ; //+ ($tempTariffa['Tariffa'] * $PercScontoR /100);
							 }
						} else {
							//caso di prenotazione libera
							$PercSconto = 0;
							$ScontoAR=0;
							$sql = "SELECT Tariffa FROM RT_CorsaTariffa WHERE TipologiaBigliettoId = $BigliettoId AND FermataPickup = $Cp AND FermataDropoff = $Cr";

							$tempTariffa = $db->query_first($sql);
							$PrezzoPax = $tempTariffa['Tariffa'];
							$PrezzoAndata = $PrezzoPax;
							$PrezzoRitorno = 0;
							$PrezzoPax = $PrezzoAndata + $PrezzoRitorno;
							$ListinoMoltiplicatore = 1;
						}
					 } else if($ArrObject[$i]['OccupaPosto'] == 0){
						 //biglietto per servizio
						 if(isset($TrattaId) && isset($arr_listini[$TrattaId]['ListinoId'])) {
							$ListinoId = $arr_listini[$TrattaId]['ListinoId'];
						 } else {
							$ListinoId = null;
						 }
						 
						 if (!empty($ArrPrezzo['ListinoId'])) {
							 $ListinoScontoId=$prenotazione->GetScontoPromozioneAttiva($CorsaId, $Data, 1, $BigliettoId);

							 $PercSconto=0;
							 $PercScontoNew=0;
							 $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoId and BigliettoId=$BigliettoId and Stato=1 and Cancella=0";
							 
							 $rowsconto = $db->query_first($sql);
							 if (!empty($rowsconto['Prezzo'])) {
								 $PercSconto=$rowsconto['Prezzo'];
								 $PercScontoNew=$PercSconto;
							 }

							 $ListinoScontoIdR = null;
							 if ($CorsaRitornoId>0) {
								 
								 $PercScontoR=0;
								 
								 $ListinoScontoIdR=$prenotazione->GetScontoPromozioneAttiva($CorsaRitornoId, $DataRitorno, 1, $BigliettoId);
								 
								 $sql="select Prezzo from RT_ScontisticaBiglietto where ListinoId=$ListinoScontoIdR and BigliettoId=$BigliettoId and Stato=1 and Cancella=0";
								 $rowsconto = $db->query_first($sql);
								 if (!empty($rowsconto['Prezzo']))
									 $PercScontoR=$rowsconto['Prezzo'];
									 
									 //$PercSconto=$PercSconto+$PercScontoR;
							 }

						 }
						 
						 $ScontoAR=0;
						 if ($ar1==1)
						 {
							 //$km=$km*2;
							 if($ritornoOpen == 1){
								 $ScontoAR=0;
							 } else {
								 $ScontoAR=Config::$scontoAR;
							 }
							 
						 }
						 
						 $sql = "Select * from RT_ListinoServizi where BigliettoId = $BigliettoId and CorsaId = 0";
						 
						 $rowServizio =  $db->query_first($sql);
						 $PrezzoAndata = $rowServizio['Prezzo'];
						 if($CorsaRitornoId > 0){
							 if($ritornoOpen == 1){
								 $PrezzoRitorno = $PrezzoAndata;
							 } else {
								 $sql = "Select * from RT_ListinoServizi where BigliettoId = $BigliettoId and CorsaId = 0";
								 $rowServizio =  $db->query_first($sql);
								 $PrezzoRitorno = $rowServizio['Prezzo'];
							 }
						 } else {
							 $PrezzoRitorno = 0;
						 }
						 $PrezzoPax = $PrezzoAndata + $PrezzoRitorno;
						 $ListinoMoltiplicatore = 1;
					 }

					//echo "<br>$PrezzoPax $PrezzoAndata $PrezzoRitorno $ListinoMoltiplicatore<br>";
                     
                     
                     //riduzione ed aumento 
					 //(recupero i valori inseriti nella prenotazione se sono in modifica)
					 $rowtipo = array();
					$sql = "select b.*, t.OccupaPosto from RT_PrenotazioneBiglietto b
                                left join RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId  
                                where b.TipologiaBigliettoId=$BigliettoId and b.PrenotazioneId=$PrenotazioneIdOld and b.OdcIdRef=$user->OdcId and b.Cancella = 0";
                    $rowtipo = $db->query_first($sql);
                     
                     $pax = 0;
                     $riduzione = 0;
                     $aumento = 0;
                     $riduzioneNote = '';
                     $aumentoNote = '';
                     $base = 0;
                     $finale = 0;
                     if (isset($arr_biglietti_prenotati)) {
                         foreach ($arr_biglietti_prenotati as $chiave => $valore) {
                             $chiave = str_replace("'","",$chiave);
                             $chiave = str_replace("\\","",$chiave);
                             
                             
                             $arr_chiave = explode('_', $chiave);
                             $TpBiglietto = $arr_chiave[0];
                             
                             if ($BigliettoId == $TpBiglietto)
                             {
                                 $DescrizioneBiglietto = $arr_chiave[1];
                                 $pax = $valore;
                                 if ($valore > 0)
                                 {

                                     $aumento = floatval(str_replace(',', '.', $arr_aumento[$TpBiglietto]['Valore']));
                                     $aumentoNote = isset($arr_aumento[$TpBiglietto]['Note']) ? $arr_aumento[$TpBiglietto]['Note'] : '';
                                     $riduzione = floatval(str_replace(',', '.', $arr_riduzione[$TpBiglietto]['Valore']));;
                                     $riduzioneNote = isset($arr_riduzione[$TpBiglietto]['Note']) ? $arr_riduzione[$TpBiglietto]['Note'] : '';
                                 }
                             }
                         }
                     } else{
                         if (!empty($rowtipo['TipologiaBigliettoId'])) {
                            $pax = $rowtipo['NumeroPax'];
                            $totpax = $totpax + $pax;
                             
                            if (!isset($arr_aumento)) {
                                if($rowtipo['OccupaPosto'] == 1 && $libera == 1 && $LiberaOld != $libera) {
                                    //passaggio da prenotazione standard a libera
                                    $aumento = $rowtipo['PrezzoAndata'];
                                } else if($rowtipo['OccupaPosto'] == 1 && $libera == 0 && $LiberaOld != $libera) {
                                    //passaggio da prenotazione libera a standard
                                    $aumento = 0;
                                } else {
                                    //libera o prenotazione standard
                                    $aumento = $rowtipo['AumentoPax'];
                                }
                                $aumentoNote = $rowtipo['AumentoPaxNote'];
                            }
                             
                            if (!isset($arr_riduzione)) {
                                $riduzione = $rowtipo['RiduzionePax'];
                                $riduzioneNote = $rowtipo['RiduzionePaxNote'];
                            }
                             
                            $PrezzoAndataOld = $PrezzoAndata;
                         }
                     }
                     if (( ($pax>0) || ( ($pax==0) && ($scrivi==0))) && ($PrezzoPax>=0))
                     {
                         if ($PrezzoPax>=0){
                             $ListinoMoltiplicatore1=$ListinoMoltiplicatore;
                             
                             $PrezzoAndata=$PrezzoAndata+$PrezzoAndata*$PercSconto/100;
                             $PrezzoRitorno=$PrezzoRitorno+$PrezzoRitorno*$PercScontoR/100;
                             
                             if(!$libera) {
                                $PrezzoAndata = $this->arrotonda($PrezzoAndata, true);
                                $PrezzoRitorno = $this->arrotonda($PrezzoRitorno, true);
                             }

                             $prezzoTotaleAR=$PrezzoAndata+$PrezzoRitorno;
                             $kmTotale = $KmAndata+$KmRitorno;

                             $change = false;
							 
                             if ($ScontoAR>0)
                                 $PrezzoPax=$prezzoTotaleAR-$prezzoTotaleAR*$ScontoAR/100;
                             else
                                 $PrezzoPax=$prezzoTotaleAR;
                                     
                                     //          echo ($PrezzoPax."<br />");
                                     
                                     // $PrezzoPax=$PrezzoPax+$PrezzoPax*$PercSconto/100;
                                     //  echo ($PrezzoPax."*".$PercSconto."<br />");
                                     if(!$libera) {
                                        $PrezzoPax = $this->arrotonda($PrezzoPax, true);
                                     }
                                     
                                     $base=$PrezzoPax*$pax;
                                     
                                     //se si sta modificando una prenotazione confronto il prezzo precedente con quello ricalcolato
                                     if (!empty($rowtipo['TipologiaBigliettoId'])){
                                         
                                         $PrezzoAndataOld1 = 0;
                                         $PrezzoRitornoOld1 = 0;
                                         $ScontoAROld1 = 0;
                                         
                                         $PrezzoPaxOld1 =  0;
                                         $baseOld1=0;
                                         $finaleOld1=0;
                                         $ListinoMoltiplicatoreOld=0;
                                         $molt=1;

                                         //  	            echo "<br>$Stato, $statoOld, $scrivi, $ritornoOpenOriginale, $ritornoOpen <br>";
                                         if(($Stato!=3 && isset($statoOld) && $statoOld!=3) ){
                                             //  	            	echo "A";
                                             $change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId);
                                             // 	            	if($change){echo "cambia"; }else {echo "noooo";}
                                             if($scrivi == 1 && $TV != $TipoViaggioOriginale){
                                                 $change = true;
                                                 //  	            		echo "B";
                                             }
                                         } else if($Stato==3 || (isset($statoOld) && $statoOld==3) || $Stato==4){
                                             if($ritornoOpenOriginale == 1 && $ritornoOpen == 0){
                                                 //  	            		echo "S";
                                                 $change = false;
                                             }else{
                                                 $change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId);
                                                 if($scrivi == 1){
                                                     //  		            		echo "C";
                                                     $change = true;
                                                 } else {
                                                     //  		            		echo "D";
                                                     // 		         			echo "<br>$Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId<br>";
                                                     $change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId);
                                                     // 		            		$change = false;
                                                 }
                                             }
                                         } else {
                                             // 	            	echo "E";
                                             if($Stato==1){
                                                 $change = $this->checkIfChanged($Cp, $Cr, $TV, $Data, $CorsaId, $DataRitorno, $CorsaRitornoId);
                                             } else {
                                                 
                                                 if(isset($DataIns) && $DataIns <= Config::$dataVersione){
                                                     $change = false;
                                                 } else {
                                                     $change = true;
                                                 }
                                             }
                                         }
                                         
                                         
                                         
                                         // 	            $change = false;
                                         if(!$change){
                                             //parametri della prenotazione non modificati 
                                             if( $rowtipo['OccupaPosto'] == 1 && $libera == 1 && $LiberaOld != $libera) {
                                                //passaggio da prenotazione standard a libera
                                                $PrezzoAndata =  0;
                                                $PrezzoRitorno =  0;
                                                $PrezzoPax = 0;
                                                $PercSconto = 0;
                                            } else if($rowtipo['OccupaPosto'] == 1 && $libera == 0 && $LiberaOld != $libera) {
                                                //passaggio da prenotazione libera a standard
                                                $PrezzoAndata = $rowtipo['AumentoPax'];
                                                $PrezzoRitorno = 0;
                                                $PrezzoPax = $rowtipo['AumentoPax'];
                                                $PercSconto = 0;
                                             } else {
                                                //libera o prenotazione standard
                                                $PrezzoAndata =  $rowtipo['PrezzoAndata'];
                                                $PrezzoRitorno =  $rowtipo['PrezzoRitorno'];
                                                $PrezzoPax = $rowtipo['PrezzoPax'];
                                                $PercSconto=  $rowtipo['PerScontoPromozioneGGprima'];
                                             }
                                             
                                             $ScontoAR = $rowtipo['PercScontoAR'];
                                             
                                             if($Stato!=3 && isset($statoOld) && $statoOld!=3){
                                                 $base=$PrezzoPax*$pax;
                                                 $finale=$PrezzoPax*$pax;
                                             } else {
                                                if($rowtipo['OccupaPosto'] == 1 && $libera == 1 && $LiberaOld != $libera) {
                                                    $base = 0;
                                                    $finale = 0;
                                                } else if($rowtipo['OccupaPosto'] == 1 && $libera == 0 && $LiberaOld != $libera) {
                                                    $base = $rowtipo['AumentoPax'];
                                                    $finale = $rowtipo['PrezzoTotalePax'];       
                                                } else {
                                                    $base = $rowtipo['PrezzoBasePax'];
                                                    $finale = $rowtipo['PrezzoTotalePax'];
                                                }
                                             }
                                             
                                             // 	            	$molt=0;
                                             // $ListinoMoltiplicatore=$rowtipo['Moltiplicatore'];
                                             // 	            	echo "V";
                                        } else {
                                             //parametri della prenotazione non modificati
                                            if($rowtipo['OccupaPosto'] == 1 && $libera == 1 && $LiberaOld != $libera) {
                                                $PrezzoAndataOld1 = 0;
                                                $PrezzoRitornoOld1 = 0;
                                                $baseOld1 = 0;
                                                $PercScontoOld1 = 0;
                                                $PrezzoPaxOld1 = 0;
                                            } else if($rowtipo['OccupaPosto'] == 1 && $libera == 0 && $LiberaOld != $libera) {
                                                $PrezzoAndataOld1 = $rowtipo['AumentoPax'];
                                                $PrezzoRitornoOld1 = 0;
                                                $baseOld1 = $rowtipo['AumentoPax'];
                                                $PrezzoPaxOld1 = $rowtipo['AumentoPax'];
                                                $PercScontoOld1 = $rowtipo['PerScontoPromozioneGGprima'];
                                            } else {
                                                $PrezzoAndataOld1 = $rowtipo['PrezzoAndata'];
                                                $PrezzoRitornoOld1 = $rowtipo['PrezzoRitorno'];
                                                $baseOld1 = $rowtipo['PrezzoBasePax'];
                                                $PrezzoPaxOld1 = $rowtipo['PrezzoPax'];
                                                $PercScontoOld1 = $rowtipo['PerScontoPromozioneGGprima'];
                                            }
                                            $ScontoAROld1 = $rowtipo['PercScontoAR'];
                                            
                                            $finaleOld1 = $rowtipo['PrezzoTotalePax'];
                                            $ListinoMoltiplicatoreOld = $rowtipo['Moltiplicatore'];
                                             
                                             //  	            		echo "Z";
                                             if (($Stato==3 || $statoOld==3) && $baseOld1>$base){
                                                  // 	            		echo "X";
                                                 $base = $baseOld1;
                                                 $PrezzoAndata = $PrezzoAndataOld1;
                                                 $PrezzoRitorno = $PrezzoRitornoOld1;
                                                 $ScontoAR = $ScontoAROld1;
                                                 $PercSconto = $PercScontoOld1;
                                                 $PrezzoPax = $PrezzoPaxOld1;
                                                 $ListinoMoltiplicatore = $ListinoMoltiplicatoreOld;
                                                 $ListinoMoltiplicatore1 = $ListinoMoltiplicatoreOld;
                                             }
                                             
                                         }
                                     }
									
                         }else{
                             $base = 0;
                             $PrezzoAndata = 0;
                             $PrezzoRitorno = 0;
                             $ScontoAR = 0;
                             $PercSconto = 0;
                             $PrezzoPax = 0;
                             $ListinoMoltiplicatore = 0;
                             $ListinoMoltiplicatore1 = 0;
                         }
                         
                         $finale_complete = $base + $aumento - $riduzione;
                         if(!$libera) {
                            $finale = $this->arrotonda($finale_complete);
                         } else {   
                            $finale = $finale_complete;
                         }
                         
                         $arrPrezziBiglietto[$numerobiglietti]['Pax']=$pax;
                         $arrPrezziBiglietto[$numerobiglietti]['BigliettoId']=$BigliettoId;
                         $arrPrezziBiglietto[$numerobiglietti]['DescrizioneBiglietto']=$BigliettoDescr;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoAndata']=$PrezzoAndata;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoRitorno']=$PrezzoRitorno;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoTotaleAR']=$prezzoTotaleAR;
                         $arrPrezziBiglietto[$numerobiglietti]['PromozioneId']=$ListinoScontoId;
                         $arrPrezziBiglietto[$numerobiglietti]['PromozioneSconto']=$PercSconto;
                         $arrPrezziBiglietto[$numerobiglietti]['ScontoAR']=$ScontoAR;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoBasePax']=$base;
                         $arrPrezziBiglietto[$numerobiglietti]['PrezzoPax']=$PrezzoPax;
                         $arrPrezziBiglietto[$numerobiglietti]['Aumento']=$aumento;
                         $arrPrezziBiglietto[$numerobiglietti]['Riduzione']=$riduzione;
                         $arrPrezziBiglietto[$numerobiglietti]['Totale']=$finale;
                         $arrPrezziBiglietto[$numerobiglietti]['RiduzioneNote']=$riduzioneNote;
                         $arrPrezziBiglietto[$numerobiglietti]['AumentoNote']=$aumentoNote;

                         if ($scrivi)
                         {
                             $d1=null;
                             $d1['PrenotazioneId']=$PrenotazioneId;
                             $d1['TipologiaBigliettoId']=$BigliettoId;
                             $d1['TipologiaBiglietto']=$BigliettoDescr;
                             $d1['NumeroPax']=$pax;
                             $d1['AumentoPax']=$aumento;
                             $d1['RiduzionePax']=$riduzione;
                             $d1['AumentoPaxNote']=$aumentoNote;
                             $d1['RiduzionePaxNote']=$riduzioneNote;
                             $d1['PercScontoAR']=$ScontoAR;
                             $d1['PerScontoPromozioneGGprima']=$PercSconto;
                             $d1['ListinoIdPromozioneGGprima']=$ListinoScontoId;
                             $d1['ListinoIdPromozioneGGprimaR']=$ListinoScontoIdR;
                             $d1['PerScontoPromozioneGGprimaR']=$PercScontoR;
                             $d1['Moltiplicatore']=$ListinoMoltiplicatore;
                             $d1['PrezzoPax']=$PrezzoPax;
                             $d1['PrezzoAndata']=$PrezzoAndata;
                             $d1['PrezzoRitorno']=$PrezzoRitorno;
                             $d1['PrezzoARpieno']=$prezzoTotaleAR;
                             $d1['PrezzoBasePax']=$base;
                             $d1['PrezzoTotalePax']=$finale;
                             
                             $d1=$storico->operazioni_insert($d1,$user);
							 if(isset($gestoreIdRef)) {
								$d1['GestoreIdRef'] = $gestoreIdRef;
							 }
							 
                             $TotalePrenotazione += $finale;
                             
                             if($ArrObject[$i]['OccupaPosto'] == 1){
                                 $TotalePax += $pax;
                             }

                             if (isset($d1['PrezzoPax'])){
                                 $lastidA=$db->insert("RT_PrenotazioneBiglietto", $d1);
                             }
                         } 
                         
                         $numerobiglietti++;
                     }
                     
                     $i++;
                     
                 }
                 
                 //echo("<br />totale prenotazione".$TotalePrenotazione);
                 $data_upd=null;
                 $data_upd['TotalePaxPrenotati']=$TotalePax;
                 $data_upd['TotalePrenotazione']=$TotalePrenotazione;
                 
                 //calcolo il totale da pagare e il residuo
                 $movimentoObj = new PrenotazioneMovimento();
                 $movimentoObj->conn = $db;
                 
                 $movimenti = $movimentoObj->getAllPrenotazioneMovimento($PrenotazioneId);
                 if (!empty($movimenti)) {
                     //  echo("<pre>");
                     //  echo("MOVIMENTI TROVATI PER PRENOTAZIONE ".$prenotazioneOldMarcs."<BR />");
                     $TotaleDaPagare = $TotalePrenotazione;
                     $TotaleDaPagareMulti = 0;
                     $TotalePagato = 0;
                     $TotaleResiduo = 0;
                     
                     if (intval($Multi)) {
                         $TotaleDaPagareMulti = $TotalePrenotazione;
                         
                         foreach ($movimenti as $movimento) {
                             if ($movimento['TipoMovimento'] == 'I') {
                                 $TotalePagato += $movimento['ImportoPagato'];
                             }
                         }
                         
                         
                         $TotaleResiduo = $TotaleDaPagareMulti - $TotalePagato;
                         $Pagato = ($TotaleResiduo <= 0);
                     } else {
                         foreach ($movimenti as $movimento) {
                             //	 echo("parziale pagato ".$movimento['ImportoPagato']." per tipo ".$movimento['TipoMovimento']."<br />");
                             
                             if ($movimento['TipoMovimento'] == 'I') {
                                 $TotalePagato += $movimento['ImportoPagato'];
                                 //echo("parziale pagato progressivo ".$TotalePagato."<br />");
                                 
                             }
                         }
                         
                         $TotaleResiduo = $TotaleDaPagare - $TotalePagato;
                         $Pagato = ($TotaleResiduo <= 0);
                     }
                 } else {
                     //   echo("MOVIMENTI NON TROVATI PER PRENOTAZIONE ".$prenotazioneOldMarcs."<BR />");
                     
                     $TotaleDaPagare = $TotalePrenotazione;
                     $TotaleDaPagareMulti = 0;
                     $TotalePagato = 0;
                     $TotaleResiduo = $TotaleDaPagare - $TotalePagato;
                     $Pagato = 0;
                 }
                 
                 $data_upd['TotaleDaPagare'] = $TotaleDaPagare;
                 $data_upd['TotaleDaPagareMulti'] = $TotaleDaPagareMulti;
                 $data_upd['TotalePagato'] = $TotalePagato;
                 $data_upd['TotaleResiduo'] = $TotaleResiduo;
                 $data_upd['Pagato'] = $Pagato;
                 $data_upd['KmPercorsiAndata'] = $KmAndata;
                 $data_upd['KmPercorsiRitorno'] = $KmRitorno;
                 $data_upd['KmPercorsiTotale'] = $KmAndata+$KmRitorno;
				 if(isset($gestoreIdRef)) {
					$data_upd['GestoreIdRef'] = $gestoreIdRef;
                 }
				 
                 /* if ($KmAndata+$KmRitorno<=0)
                  die("si è verificato un errore nel calcolo dei km");*/
                 
                 $arrPrezziBiglietto[9999]=$KmAndata+$KmRitorno;
                 //   print_r($arrPrezziBiglietto);
                 if(isset($Stato) && $Stato != 4 && $libera == $LiberaOld && $libera==0){
                     if(!($ritornoOpenOriginale == 1 && $ritornoOpen == 0) && !($change==false && $Stato==3 && !isset($statoOld))){
                         $arrBigliettiNew=array();
                         if (($PrenotazioneId>0) and (is_object($prenotazione_wizard)))
                             $arrBigliettiNew=$this->GetTipologiaBigliettiPrezziFaseModifica(0,null,null,$Data,$CorsaId,$FermataIdAP,$FermataIdAD,$CorsaRitornoId,$FermataIdRP,$FermataIdRD,$TV,$arr_biglietti_prenotati,$arr_aumento,$arr_riduzione,$PrenotazioneIdOld1,$prenotazioneOldMarcs, $statoOld ,$DataRitorno, $listinoScontiGPold, $listinoScontiGPoldR, $TipoTour);
                             // 		  echo "<br><br><br><br>"; print_r($arrBigliettiNew);
                             if (count($arrBigliettiNew)>0){
                                 $arrPrezziBigliettoNew = $this->confrontaPrezziBiglietti($arrPrezziBiglietto,$arrBigliettiNew);
                                 unset($arrPrezziBiglietto);
                                 $arrPrezziBiglietto = $arrPrezziBigliettoNew;
                             }
                     }
                 }
                 
                 if ($scrivi){
                     $db->update("RT_Prenotazione", $data_upd, "PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId");
                 }
                 
                 return ($arrPrezziBiglietto);
     }
     
     public function aggiornaTotaliPrenotazione()
     {
         global $user;
         $db = $this->conn;
         $PrenotazioneId=$this->Id;
         
         $movimentoObj = new PrenotazioneMovimento();
         $movimentoObj->conn = $db;
         
         $movimenti = $movimentoObj->getAllPrenotazioneMovimento($PrenotazioneId);
         if (!empty($movimenti)) {
             echo("<pre>");
             echo("MOVIMENTI TROVATI PER PRENOTAZIONE ".$PrenotazioneId."<BR />");
             $TotaleDaPagare = $TotalePrenotazione;
             $TotaleDaPagareMulti = 0;
             $TotalePagato = 0;
             $TotaleResiduo = 0;
             
             if (intval($Multi)) {
                 $TotaleDaPagareMulti = $TotalePrenotazione;
                 
                 foreach ($movimenti as $movimento) {
                     //$TotaleDaPagareMulti += $movimento['Importo'] + $movimento['Supplemento'];
                     if ($movimento['TipoMovimento'] == 'I') {
                         $TotalePagato += $movimento['ImportoPagato'];
                     }
                 }
                 
                 //$TotaleResiduo = ($TotalePagato > 0)? 0 : $TotalePrenotazione;
                 $TotaleResiduo = $TotaleDaPagareMulti - $TotalePagato;
                 $Pagato = ($TotaleResiduo <= 0);
             } else {
                 foreach ($movimenti as $movimento) {
                     //echo("parziale pagato ".$movimento['ImportoPagato']." per tipo ".$movimento['TipoMovimento']."<br />");
                     
                     if ($movimento['TipoMovimento'] == 'I') {
                         $TotalePagato += $movimento['ImportoPagato'];
                         //echo("parziale pagato progressivo ".$TotalePagato."<br />");
                         
                     }
                 }
                 
                 $TotaleResiduo = $TotaleDaPagare - $TotalePagato;
                 $Pagato = ($TotaleResiduo <= 0);
             }
         } else {
             echo("MOVIMENTI NON TROVATI PER PRENOTAZIONE ".$PrenotazioneId."<BR />");
             
             $TotaleDaPagare = $TotalePrenotazione;
             $TotaleDaPagareMulti = 0;
             $TotalePagato = 0;
             $TotaleResiduo = $TotaleDaPagare - $TotalePagato;
             $Pagato = 0;
         }
         
         $data_upd['TotaleDaPagare'] = $TotaleDaPagare;
         $data_upd['TotaleDaPagareMulti'] = $TotaleDaPagareMulti;
         $data_upd['TotalePagato'] = $TotalePagato;
         $data_upd['TotaleResiduo'] = $TotaleResiduo;
         $data_upd['Pagato'] = $Pagato;
         $data_upd['KmPercorsiAndata'] = $KmAndata;
         $data_upd['KmPercorsiRitorno'] = $KmRitorno;
         $data_upd['KmPercorsiTotale'] = $KmAndata+$KmRitorno;
         
         // print_r($data_upd);
         
         //if ($scrivi)
         //	$db->update("RT_Prenotazione", $data_upd, "PrenotazioneId=$PrenotazioneId and OdcIdRef=$user->OdcId");
         
         
     }
     
     private function confrontaPrezziBiglietti($arrOld,$arrNew)
     {
         $newArrBiglietti=array();
         
         foreach ($arrOld as $chiave=>$valore)
         {
             $IdbigliettoOld=$valore['BigliettoId'];
             $paxOld=$valore['Pax'];
             $prezzoPaxOld=$valore['PrezzoPax'];
             $variazione=false;
             if ($paxOld>0)
             {
                 foreach($arrNew as $chiavenew=>$valorenew)
                 {
                     $IdbigliettoNew=$valorenew['BigliettoId'];
                     if ($IdbigliettoNew==$IdbigliettoOld)
                     {
                         $prezzoPaxNew=$valorenew['PrezzoPax'];
                         
                         if ($prezzoPaxNew>$prezzoPaxOld+1)
                         {
                             
                             $arrOld[$chiave]['PrezzoAndata']= $valorenew['PrezzoAndata'];
                             $arrOld[$chiave]['PrezzoRitorno']= $valorenew['PrezzoRitorno'];
                             $arrOld[$chiave]['PrezzoTotaleAR']= $valorenew['PrezzoTotaleAR'];
                             $arrOld[$chiave]['PromozioneId']= $valorenew['PromozioneId'];
                             $arrOld[$chiave]['PromozioneSconto']= $valorenew['PromozioneSconto'];
                             
                             $arrOld[$chiave]['PrezzoPax']= $valorenew['PrezzoPax']*$paxOld;
                             $arrOld[$chiave]['PrezzoBasePax']=$valorenew['PrezzoPax'];
                             $arrOld[$chiave]['Totale']=$arrOld[$chiave]['PrezzoPax']+$arrOld[$chiave]['Aumento']-$arrOld[$chiave]['Riduzione'];
                             
                             
                             
                             //    echo ("trovato nuovo prezzo per ".$IdbigliettoOld.". Il nuovo prezzo è ".$prezzoPaxNew."<br />");
                             $variazione=true;
                             
                         }
                         
                     }
                     
                 }
             }
             
             
             
         }
         
         return $arrOld;
     }
     
    public function arrotonda($prezzo, $arrotondaAlCinque = false)
    {
        if ($arrotondaAlCinque) {
            // Arrotonda al multiplo di 5 più vicino (matematico)
            $fattore = 5;
            $resto = $prezzo % $fattore;
            if ($resto >= ($fattore / 2)) {
                // Arrotonda al multiplo superiore
                return ceil($prezzo / $fattore) * $fattore;
            } else {
                // Arrotonda al multiplo inferiore
                return floor($prezzo / $fattore) * $fattore;
            }
        } else {
            // Arrotondamento classico all'unità
            return round($prezzo);
        }
    }
     
     public function GetPasseggeri()
     {
         global $user;
         $db = $this->conn;
         $Id = $this->Id;
         $sql = "SELECT pp.*, tb.TipologiaBiglietto From RT_PrenotazionePasseggeri pp LEFT JOIN RT_TipologiaBiglietto tb ON (pp.TipoBigliettoId = tb.TipologiaBigliettoId) WHERE pp.PrenotazioneId=$Id and pp.OdcIdRef=$user->OdcId";
         $rows = $db->fetch_array($sql);
         return $rows;
     }
     
     public function GetProgressivoCodicePrenotazione()
     {
         global $user;
         $db = $this->conn;
         
         $sql = "SELECT CodicePrenotazione codice FROM RT_Prenotazione WHERE OdcIdRef=$user->OdcId ORDER BY CodicePrenotazione Desc";
         
         $row = $db->query_first($sql);
         
         $id = "";
         if ((!empty($row['codice']))) {
             
             $id = intval(substr($row['codice'], 2)) + 1;
         } else {
             $id = 1;
         }
         
         return Config::$identificativoPrenotazione . str_pad($id, 11, "0", STR_PAD_LEFT);
     }
     
     public function GetTotaliPrenotazione()
     {
         global $user;
         $db = $this->conn;
         $row=null;
         if ($this->DatiGenerali['Multi']) {
             $sql = "SELECT SUM(TotalePrenotazione) TotalePrenotazione, SUM(TotaleDaPagare) TotaleDaPagare, MAX(TotaleDaPagareMulti) TotaleDaPagareMulti, SUM(TotalePagato) TotalePagato, SUM(TotaleResiduo) TotaleResiduo FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $this->DatiGenerali['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $this->DatiGenerali['PrenotazioneStato']  . " AND OdcIdRef = $user->OdcId";
             $row = $db->query_first($sql);
             // 		if ($row['TotaleDaPagareMulti'] != 0) {
             // 			$row['TotaleDaPagare'] = $row['TotaleDaPagareMulti'];
             // 		}
             $row['TotaleDaPagareMulti'] = $row['TotaleDaPagare'];
         } else {
             $sql = "SELECT SUM(TotalePrenotazione) TotalePrenotazione, SUM(TotaleDaPagare) TotaleDaPagare, SUM(TotalePagato) TotalePagato, SUM(TotaleResiduo) TotaleResiduo FROM RT_Prenotazione WHERE PrenotazioneId = " . $this->DatiGenerali['PrenotazioneId'] ;
             $row = $db->query_first($sql);
         }
         
         //$row = $db->query_first($sql);
         
         return $row;
     }
     
     public function GetPenale(){
         global $user;
         $db = $this->conn;
         
         if ($this->DatiGenerali['Multi']) {
             $sql = "SELECT SUM(Penale) FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $this->DatiGenerali['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $this->DatiGenerali['PrenotazioneStato']  . " AND OdcIdRef = $user->OdcId";
             $row = $db->query_first($sql);
             if ($row['TotaleDaPagareMulti'] != 0) {
                 $row['TotaleDaPagare'] = $row['TotaleDaPagareMulti'];
             }
             
         } else {
             $sql = "SELECT Penale FROm RT_Prenotazione WHERE PrenotazioneId = ".$this->DatiGenerali['PrenotazioneId']." ";
             
             $row = $db->query_first($sql);
         }
         
         return $row;
     }
     
     
     public function CreatePercorsoBreve($ComuneAndataId,$ComuneRitornoId,$db,$TrattePercorse,$trattaPartenza,$trattaArrivo,$CorsaAndata,$lineaId)
     {
         global $user;
         if($trattaPartenza) {
             $kmTot=0;
             foreach ($TrattePercorse as $chiave => $valore) {
                 $Km=$valore;
                 $kmTot+=$Km;
             }
             
             $data=null;
             $data['ComunePickupId']=$ComuneAndataId;
             $data['ComuneDropOffId']=$ComuneRitornoId;
             $data['TrattaPickupId']=$trattaPartenza;
             $data['TrattaDropOffId']=$trattaArrivo;
             $data['KmPercorsi']=$kmTot;
             $data['CorsaId']=$CorsaAndata;
             if ($kmTot>0)
             {
                 $lastid=$db->insert("RT_PercorsoBreve",$data);
                 
                 if ($trattaPartenza==$trattaArrivo) {
                     $data=null;
                     $data['PercorsoBreveId']=$lastid;
                     $data['TrattaId']=$trattaPartenza;
                     $data['Km']=$kmTot;
                     $db->insert("RT_PercorsoBreveTratte",$data);
                 } else {
                     foreach ($TrattePercorse as $chiave => $valore) {
                         $data=null;
                         $data['PercorsoBreveId']=$lastid;
                         $data['TrattaId']=$chiave;
                         $data['Km']=$valore;
                         $db->insert("RT_PercorsoBreveTratte",$data);
                     }
                 }
             }
             
             
             
             
             
         } /*else {
         $data=null;
         $data['ComunePickupId']=$ComuneAndataId;
         $data['ComuneDropOffId']=$ComuneRitornoId;
         $data['KmPercorsi']=0;
         $data['CorsaId']=$CorsaAndata;
         $lastid=$db->insert("RT_PercorsoBreve",$data);
         }*/
         return true;
     }
     
     public function ListinoMoltiplicatore($Km, $CorsaId=null)
     {
         global $user;
         $db = $this->conn;
         
         $sql="select Moltiplicatore from RT_ListinoMoltiplicatore where KmDa<=$Km and KmA>=$Km";
         // echo($sql);
         $row=$db->query_first($sql);
         if (!empty($row['Moltiplicatore']))
             return $row['Moltiplicatore'];
             else
                 return 1;
                 
                 
     }
     
     public function checkIfChanged($ComuneP, $ComuneD, $Tp, $DataPa, $CorsaId, $DataRitorno=null, $CorsaRId=null)
     {
         global $prenotazione_wizard;
         if (is_object($prenotazione_wizard))
         {
             $prenotazione_wizard->inizializzaDatiGenerali();
             $DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
             $TipoViaggioOriginale = $DatiGeneraliArr['TipoViaggioId'];
             // 		echo "TP = $Tp";
             
             if($Tp==1){
                 $prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
                 $DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
             }else{
                 $prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
                 $DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
                 $prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
                 $DatiGeneraliPercorsoArrRitorno = $prenotazione_wizard->DatiGeneraliPercorso;
             }
             // print($Tp);
             //  		print_r($DatiGeneraliPercorsoArr);
             $ComuneSalitaId=$DatiGeneraliPercorsoArr['ComuneSalitaId'];
             $ComuneDiscesaId=$DatiGeneraliPercorsoArr['ComuneDiscesaId'];
             $DataPartenzaOriginale = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
             $CorsaIdOriginale = $DatiGeneraliPercorsoArr['CorsaId'];
             if(isset($DatiGeneraliPercorsoArrRitorno)) {
                 $DataRitornoOriginale = $DatiGeneraliPercorsoArrRitorno['CorsaDataPartenza'];
                 $CorsaRitornoIdOriginale = $DatiGeneraliPercorsoArrRitorno['CorsaId'];
             } else {
                 $DataRitornoOriginale = null;
                 $CorsaRitornoIdOriginale = null;
             }
             $change = false;
             // 		echo "<br>1: $ComuneSalitaId $ComuneP";
             if($ComuneSalitaId != $ComuneP){
                 $change = true;
             }
             // 		echo "<br>2: $ComuneDiscesaId $ComuneD";
             if(!$change && $ComuneDiscesaId != $ComuneD){
                 $change = true;
             }
             // 		echo "<br>3: $DataPartenzaOriginale $DataPa";
             if(!$change && isset($DataPa) && $DataPartenzaOriginale != $DataPa){
                 $change = true;
             }
             /*if(!$change && $TipoViaggioOriginale != $Tp){
              echo("cambio4");
              $change = true;
              }*/
             // 		echo "<br>4: $CorsaIdOriginale $CorsaId";
             if(!$change && $CorsaIdOriginale != $CorsaId){
                 $change = true;
             }
             
             // 		echo "<br>5: $CorsaRitornoIdOriginale $CorsaRId";
             if(!$change && isset($CorsaRId) && $CorsaRId != 0 && $CorsaRitornoIdOriginale != $CorsaRId){
                 $change = true;
             }
             
             // 		echo "<br>6: $DataRitornoOriginale $DataRitorno";
             if(!$change && isset($DataRitorno) && $DataRitornoOriginale != $DataRitorno){
                 $change = true;
             }
             
             return $change;
             
         }else{
             return true;
         }
     }
     
     public function isRimborsata($PrenotazioneId) {
         global $user;
         $db = $this->conn;
         $storico = new StoricoOperazioni();
         $storico->conn = $db;
         
         $sql = "SELECT count(*) Prenotazioni
				FROM RT_PrenotazioneDettaglio pd
				WHERE pd.PrenotazioneId = $PrenotazioneId AND pd.Rimborso = 0
				GROUP BY pd.PrenotazioneId";
         $row = $db->query_first($sql);
         $prenotazioni = $row['Prenotazioni'];
         
         $sql = "SELECT count(*) Rimborsate
				FROM RT_PrenotazioneDettaglio pd
				WHERE pd.PrenotazioneId = $PrenotazioneId AND pd.Rimborso = 1
				GROUP BY pd.PrenotazioneId";
         $row = $db->query_first($sql);
         $rimborsate = $row['Rimborsate'];
         
         // se le prenotazioni sono uguali alle rimborsate cambio lo stato a rimborsata
         if ($prenotazioni == $rimborsate) {
             $dataPrenotazione['PrenotazioneStato'] = 7;
             $dataPrenotazione = $storico->operazioni_update($dataPrenotazione, $user);
             $db->update("RT_Prenotazione", $dataPrenotazione, "PrenotazioneId = " . $PrenotazioneId);
             
             $dataPercorso['PrenotazioneStato'] = 7;
             $dataPercorso = $storico->operazioni_update($dataPercorso, $user);
             $db->update("RT_PrenotazionePercorso", $dataPercorso, "PrenotazioneId = " . $PrenotazioneId);
         }
     }
	 
	private function isValidDateFormat($dateString)
	{
		$format = 'd/m/Y';  // Formato dd/mm/yyyy
		$dateTimeObject = DateTime::createFromFormat($format, $dateString);

		return $dateTimeObject && $dateTimeObject->format($format) === $dateString;
	}
}
?>
