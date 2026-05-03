<?PHP
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
ob_end_clean();

	$db= new Database();
	$db->connect();
	
	$CorsaId=$_REQUEST['CorsaId'];
	$CorsaIdA=$CorsaId;
	$DataCorsa=$_REQUEST['DataCorsa'];
	$Order=$_REQUEST['Order'];
	
	$sql = " select 
	        (case
	           when (`apps`.`PrenotazioneStato` = _latin1'Biglietto emesso') then _utf8'BE'
	            else _utf8'C'
	        end) AS `StatoPrenotazione`,
	        `g`.`RagioneSociale` AS `RagioneSociale`,
	        (case
	            when
	                (`rdc`.`Codice` is not null)
	            then
	                concat(convert( `rdc`.`Codice` using utf8),
	                        _utf8'/',
	                        cast(`rdc`.`Anno` as char (5) charset utf8))
	            else concat(convert( `rdc`.`CodicePrenotazione` using utf8),
	                    _utf8'/',
	                    cast(`rdc`.`PrenotazioneNumero` as char (10) charset utf8))
	        end) AS `NumeroBiglietto`,
	        (case
	            when (`tp`.`OccupaPosto` = 1) then concat(`pd`.`Cognome`, _latin1' ', `pd`.`Nome`)
	            else concat('Servizio: ', _latin1' ', `pd`.`Nome`)
	        end) AS `Cliente`,
	        concat(_latin1'+',
	                `p`.`ClienteCellularePrefisso`,
	                _latin1' ',
	                `p`.`ClienteCellulare`) AS `ClienteCellulare`,
	        `pd`.`OrarioPartenza` AS `OrarioPartenza`,
	        `pd`.`OrarioArrivo` AS `OrarioArrivo`,
	        `pd`.`DataArrivo` AS `DataArrivo`,
	        `pd`.`ComunePartenza` AS `ComunePartenza`,
	        `pd`.`ComuneArrivo` AS `ComuneArrivo`,
	        (case
	            when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then _utf8'CS'
	            else _utf8'A/R'
	        end) AS `TipoViaggio`,
	        (case
	            when (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') then round(`pd`.`Importo`, 2)
	            else round((`pb`.`PrezzoPax` + ((`pb`.`AumentoPax` - `pb`.`RiduzionePax`) / `pb`.`NumeroPax`)),
	                    2)
	        end) AS `Importo`,
	        `pd`.`CorsaId` AS `CorsaId`,
	        `pd`.`DataPartenza` AS `DataPartenza`,
	        `pd`.`PrenotazioneDettaglioId` AS `PrenotazioneDettaglioId`,
	        `pd`.`DataInizioItinerario` AS `DataInizioItinerario`
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
        left join `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBiglietto` = `pb`.`TipologiaBiglietto`)
            and (`tp`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
	    where
	        (((`p`.`PrenotazioneStato` = 3)
	            or (`p`.`PrenotazioneStato` = 1)
	            or (`p`.`PrenotazioneStato` = 2))
	            and (`pd`.`Escludi` <> 1)
	            and (`pd`.`Rimborso` <> 1))
				and `pd`.`CorsaId`=$CorsaId and `pd`.`DataInizioItinerario` ='".$DataCorsa."' and `tp`.`OccupaPosto` = 0 ";
		
	if($Order == '1'){
		$sql .= " order by pd.DataArrivo desc, pd.OrarioArrivo desc, pd.ComuneArrivo";
	}else{
		$sql .= " order by pd.DataPartenza, pd.OrarioPartenza, pd.ComunePartenza";
	}
	$data = $db->fetch_array($sql);

  function cleanData(&$str)
  {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

  // file name for download
  $filename = "Corsa_" . $DataCorsa . "_".$CorsaId.".xls";

  header("Content-Disposition: attachment; filename=\"$filename\"");
  header("Content-Type: application/vnd.ms-excel");

  $flag = false;
  foreach($data as $row) {
  
    if(!$flag) {
      // display field/column names as first row
      echo implode("\t", array_keys($row)) . "\n";
      $flag = true;
    }
    array_walk($row, 'cleanData');
    echo implode("\t", array_values($row)) . "\n";
  }

  exit;
?>