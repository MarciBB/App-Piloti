<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author a.esposito
 */
class Corsa {


	public $Id;
	public $conn;
	public $DatiGenerali;

	function __construct($Id=null) {
		$this->Id = $Id;
	}

	public function inizializzaDatiGenerali()
	{
		global $user;
		$db=$this->conn;
		$Id=$this->Id;
		$sql = "SELECT * From RT_Corsa WHERE CorsaId=$Id and OdcIdRef=$user->OdcId";
		$row = $db->query_first($sql);

		if (!empty($row['OdcIdRef']))
		$this->DatiGenerali=$row;
		else
		{
			print("errore");
			exit();
		}
	}

	function getPostiPrenotati($CorsaId,$DataPartenza)
	{
		global $user;
		$db=$this->conn;
		$sql = "SELECT TotalePaxPrenotati From RT_ViewSingolaCorsaPostiPrenotati WHERE CorsaId=$CorsaId and CorsaDataPartenza='$DataPartenza'  and OdcIdRef=$user->OdcId";

		$row = $db->query_first($sql);
		$postiprenotati=0;
		if (!empty($row['TotalePaxPrenotati']))
			$postiprenotati=$row['TotalePaxPrenotati'];
		return $postiprenotati;
	}
	
	function getAllByLineaId($LineaId, $DataPartenza = null)
	{
		global $user;
		$db = $this->conn;

		$sql = "SELECT
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
		AND rt_corsa.LineaId = $LineaId
		AND rt_corsa.OdcIdRef = $user->OdcId";

		if ($DataPartenza !== null) {
			$DataPartenzaSql = $db->escape($DataPartenza);
			$sql .= " AND rt_corsa.Stato = 1 ";
			$sql .= " AND '$DataPartenzaSql' >= rt_corsa.VendibileDal ";
			$sql .= " AND '$DataPartenzaSql' <= rt_corsa.VendibileAl ";
			$sql .= " AND '$DataPartenzaSql' >= rt_corsa.AttivaDal ";
			$sql .= " AND '$DataPartenzaSql' <= rt_corsa.AttivaAl ";
		}
		
		$row = $db->fetch_array($sql);
		return $row;
	}

	function getAllPickup()
	{
		global $user;
		$db=$this->conn;
		$Id=$this->Id;
		$sql = "SELECT * From RT_FoglioCaricoPickupPerCorsa where CorsaId=$Id order by Comune asc";
		$row = $db->fetch_array($sql);
		return $row;
	}

	function getAllDropOff()
	{
		global $user;
		$db=$this->conn;
		$Id=$this->Id;
		$sql = "SELECT * From RT_FoglioCaricoDropOffPerCorsa where CorsaId=$Id order by Comune asc";
		 
		$row = $db->fetch_array($sql);
		return $row;
	}
	
	// Funzione per rimuovere il testo tra parentesi tonde
	function rimuoviParentesiComune($string) {
		// Espressione regolare per rimuovere qualsiasi testo tra parentesi (con spazi opzionali)
		return preg_replace('/\s*\(.*?\)\s*/i', '', $string);
	}
	 
	function getAllFermatePartenza($noParentesi = false)
	{
		global $user;
		$db=$this->conn;
		$Id=$this->Id;
		$sql = "SELECT 
					comune.ComuneId AS ComuneId,
					comune.Comune AS Comune
				 FROM RT_Fermata f 
							LEFT JOIN Comune comune ON comune.ComuneId = f.ComuneId
							LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
                            LEFT JOIN RT_Linea l ON l.LineaId = t.LineaId
							where f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
                            and f.IsPickup = 1 and t.TrattaTipoId = 1 and l.Stato = 1 AND t.Cancella = 0
				GROUP BY comune.ComuneId
				ORDER BY comune.Comune";
		/*$sql = "SELECT DISTINCT 
				comune.ComuneId AS ComuneId,
				comune.Comune AS Comune,
				rt_corsatariffa.OdcIdRef AS OdcIdRef 
				FROM RT_CorsaTariffa AS rt_corsatariffa 
					JOIN Comune AS comune ON (rt_corsatariffa.FermataPickup = comune.ComuneId)
				WHERE rt_corsatariffa.OdcIdRef = $user->OdcId
						AND comune.ComuneId IN (
							SELECT ComuneId FROM RT_Fermata f 
							LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
                            LEFT JOIN RT_Linea l ON l.LineaId = t.LineaId
							where f.ComuneId = comune.ComuneId and f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
                            and f.IsPickup = 1 and t.TrattaTipoId = 1 and l.Stato = 1 AND t.Cancella = 0
						)
				ORDER BY comune.Comune";*/
		  //echo($sql);
		$row = $db->fetch_array($sql);
		
		if($noParentesi) {
			foreach ($row as &$item) {
				if (isset($item['Comune'])) {
					$item['Comune'] = $this->rimuoviParentesiComune($item['Comune']);
				}
			}
		}
		return $row;
	}

	function getAllFermateArrivo($noParentesi = false)
	{
		global $user;
		$db=$this->conn;
		$Id=$this->Id;
		$sql = "SELECT 
					comune.ComuneId AS ComuneId,
					comune.Comune AS Comune
				FROM RT_Fermata f 
							LEFT JOIN Comune comune ON comune.ComuneId = f.ComuneId
							LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
							LEFT JOIN RT_Linea l ON l.LineaId = t.LineaId
							where f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
							and f.IsDropoff = 1 and t.TrattaTipoId = 1 and l.Stato = 1 AND t.Cancella = 0
							and comune.Comune <> ''
				GROUP BY comune.ComuneId
				ORDER BY comune.Comune";

		/*$sql = "SELECT DISTINCT 
		comune.ComuneId AS ComuneId,
		comune.Comune AS Comune,
		rt_corsatariffa.OdcIdRef AS OdcIdRef 
		FROM 
		RT_CorsaTariffa AS rt_corsatariffa 
		JOIN Comune AS comune ON (rt_corsatariffa.FermataDropOff = comune.ComuneId) 
		WHERE rt_corsatariffa.OdcIdRef = $user->OdcId
        AND comune.ComuneId IN (
            SELECT ComuneId FROM RT_Fermata f 
            LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
			LEFT JOIN RT_Linea l ON l.LineaId = t.LineaId
            where f.ComuneId = comune.ComuneId and f.Stato = 1 and f.Cancella = 0 and t.Stato = 1 and t.Cancella = 0
			and f.IsDropoff = 1 and t.TrattaTipoId = 1 and l.Stato = 1 AND t.Cancella = 0
        )
		ORDER BY comune.Comune";*/
		//  echo($sql);
		$row = $db->fetch_array($sql);
		
		if($noParentesi) {
			foreach ($row as &$item) {
				if (isset($item['Comune'])) {
					$item['Comune'] = $this->rimuoviParentesiComune($item['Comune']);
				}
			}
		}
		return $row;
	}
	
	function getValiditaBiglietto($ValiditaBigliettoId)
	{
		global $user;
		$db=$this->conn;
		$Id=$this->Id;
		$sql = "SELECT * FROM RT_ValiditaBiglietto WHERE OdcIdRef=$user->OdcId AND ValiditaBigliettoID=$ValiditaBigliettoId";
		//  echo($sql);
		$row = $db->query_first($sql);
		return $row;
	}
	
	function existValiditaBigliettoDettaglio($ValiditaBigliettoId, $TipologiaBigliettoId)
	{
		global $user;
		$db=$this->conn;
		$Id=$this->Id;
		$sql = "SELECT * FROM RT_ValiditaBigliettoDettaglio WHERE OdcIdRef=$user->OdcId AND ValiditaBigliettoId=$ValiditaBigliettoId AND BigliettoId=$TipologiaBigliettoId";
		//  echo($sql);
		$row = $db->query_first($sql);
		return $row;
	}
        
        
	function getAll()
	{
	       global $user;
	        $db=$this->conn;
	        $Id=$this->Id;
	        
	        $sql = "select 
		        `RT_Corsa`.`CorsaId` AS `CorsaId`,
		        `RT_Corsa`.`LineaId` AS `LineaId`,
		        `RT_Corsa`.`CorsaNome` AS `CorsaNome`,
		        `RT_Corsa`.`AttivaDal` AS `AttivaDal`,
		        `RT_Corsa`.`AttivaAl` AS `AttivaAl`,
		        `RT_Corsa`.`IncludiFeriale` AS `IncludiFeriale`,
		        `RT_Corsa`.`IncludiPrefestivo` AS `IncludiPrefestivo`,
		        `RT_Corsa`.`IncludiFestivo` AS `IncludiFestivo`,
		        `RT_Corsa`.`OrarioPartenza` AS `OrarioPartenza`,
		        `RT_Corsa`.`OrarioArrivo` AS `OrarioArrivo`,
		        `RT_Corsa`.`NextDay` AS `NextDay`,
		        `RT_Corsa`.`DataIns` AS `DataIns`,
		        `RT_Corsa`.`OpeIns` AS `OpeIns`,
		        `RT_Corsa`.`SedeIns` AS `SedeIns`,
		        `RT_Corsa`.`IpIns` AS `IpIns`,
		        `RT_Corsa`.`DataAgg` AS `DataAgg`,
		        `RT_Corsa`.`OpeAgg` AS `OpeAgg`,
		        `RT_Corsa`.`SedeAgg` AS `SedeAgg`,
		        `RT_Corsa`.`IpAgg` AS `IpAgg`,
		        `RT_Corsa`.`Stato` AS `Stato`,
		        `RT_Corsa`.`Cancella` AS `Cancella`,
		        `RT_Corsa`.`UpdateCount` AS `UpdateCount`,
		        `RT_Corsa`.`OdcIdRef` AS `OdcIdRef`,
		        `RT_Corsa`.`GestoreIdRef` AS `GestoreIdRef`,
		        `RT_Linea`.`LineaNome` AS `LineaNome`,
		        `RT_Linea`.`PercorsoId` AS `PercorsoId`,
		        `RT_Percorso`.`PercorsoNome` AS `PercorsoNome`,
		        `RT_Corsa`.`CorsaPeso` AS `CorsaPeso`,
		        `RT_Corsa`.`VendibileDal` AS `VendibileDal`,
		        `RT_Corsa`.`VendibileAl` AS `VendibileAl`,
		        `RT_Percorso`.`Identificativo` AS `Identificativo`
				    from
				        ((`RT_Corsa`
				        left join `RT_Linea` ON ((`RT_Corsa`.`LineaId` = `RT_Linea`.`LineaId`)))
				        left join `RT_Percorso` ON ((`RT_Linea`.`PercorsoId` = `RT_Percorso`.`PercorsoId`)))
				    where
		        		(`RT_Corsa`.`Cancella` = 0) AND RT_Corsa.RitornoAperto = 0 and `RT_Corsa`.`OdcIdRef` = $user->OdcId and AttivaAl>=Now() order by LineaId";
	        
		// $sql = "SELECT * From RT_ElencoCorsa WHERE OdcIdRef=$user->OdcId and AttivaAl>=Now() order by LineaId";      
	   //  echo($sql);
	        $row = $db->fetch_array($sql);
	        return $row;   
	}
	
	
	function getCorsaValidaNoVincoli2($data, $corsaId, $lineaId ){
		global $user;
		$db=$this->conn;
		$sql = "select
		RT_Percorso.PercorsoNome AS PercorsoNome,
		RT_Linea.LineaArea AS LineaArea,
		RT_Linea.LineaNome AS LineaNome,
		RT_Corsa.CorsaNome AS CorsaNome,
		RT_AppSettimana.AppSettimanaGiorno AS AppSettimanaGiorno,
		RT_AppSettimana.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
		RT_Corsa.AttivaDal AS AttivaDal,
		RT_Corsa.AttivaAl AS AttivaAl,
		RT_AppCalendario.AppCalendarioData AS AppCalendarioData,
		date_format(RT_AppCalendario.AppCalendarioData,
		_utf8'%d/%m/%Y') AS DataPartenzaFormattata,
		RT_Corsa.IncludiFeriale AS IncludiFeriale,
		RT_Corsa.IncludiPrefestivo AS IncludiPrefestivo,
		RT_Corsa.IncludiFestivo AS IncludiFestivo,
		RT_AppCalendario.Feriale AS Feriale,
		RT_AppCalendario.Prefestivo AS Prefestivo,
		RT_AppCalendario.Festivo AS Festivo,
		RT_Percorso.PercorsoId AS PercorsoId,
		RT_Linea.LineaId AS LineaId,
		RT_Corsa.CorsaId AS CorsaId,
		RT_Percorso.OdcIdRef AS OdcIdRef,
		RT_Corsa.OrarioPartenza AS OrarioPartenza,
		RT_Corsa.VendibileDal AS VendibileDal,
		RT_Corsa.VendibileAl AS VendibileAl,
		RT_Corsa.OrePrimaStopVendita AS OrePrimaStopVendita,
		addtime(RT_AppCalendario.AppCalendarioData,
		RT_Corsa.OrarioPartenza) AS DataOraPartenza,
		timediff(addtime(RT_AppCalendario.AppCalendarioData,
		RT_Corsa.OrarioPartenza),
		now()) AS OreMancanti,
		RT_Corsa.TipologiaBusDefaultId AS TipologiaBusDefaultId,
		RT_TipologiaBus.TotalePosti AS PostiCorsaDefault
		from
		RT_Percorso
		left join RT_Linea ON (RT_Percorso.PercorsoId = RT_Linea.PercorsoId)
		left join RT_Corsa ON (RT_Linea.LineaId = RT_Corsa.LineaId)
		left join RT_CorsaSettimana ON (RT_Corsa.CorsaId = RT_CorsaSettimana.CorsaId)
		left join RT_AppSettimana ON (RT_CorsaSettimana.SettimanaId = RT_AppSettimana.AppSettimanaId)
		left join RT_AppCalendario ON (RT_AppSettimana.AppSettimanaGiorno = RT_AppCalendario.GiornoSettimana)
		left join RT_TipologiaBus ON (RT_Corsa.TipologiaBusDefaultId = RT_TipologiaBus.TipologiaBusId)
		
		where
		(RT_AppCalendario.AppCalendarioData >= RT_Corsa.AttivaDal)
		and (RT_AppCalendario.AppCalendarioData <= RT_Corsa.AttivaAl)
		and ((RT_AppCalendario.Feriale = RT_Corsa.IncludiFeriale)
		or (RT_AppCalendario.Prefestivo = RT_Corsa.IncludiPrefestivo)
		or (RT_AppCalendario.Festivo = RT_Corsa.IncludiFestivo)) and
		Date(RT_AppCalendario.AppCalendarioData) = '$data'  and RT_Corsa.CorsaId = $corsaId
		and RT_Linea.LineaId = $lineaId order by RT_AppCalendario.AppCalendarioData" ;
	
		$row = $db->fetch_array($sql);
		return $row[0];
	}
	
	function getCorsaValidaNoVincoli($data, $corsaId, $lineaId ){
		global $user;
		$db=$this->conn;
		$sql = "select
		RT_Percorso.PercorsoNome AS PercorsoNome,
		RT_Linea.LineaArea AS LineaArea,
		RT_Linea.LineaNome AS LineaNome,
		RT_Corsa.CorsaNome AS CorsaNome,
		RT_AppSettimana.AppSettimanaGiorno AS AppSettimanaGiorno,
		RT_AppSettimana.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
		RT_Corsa.AttivaDal AS AttivaDal,
		RT_Corsa.AttivaAl AS AttivaAl,
		RT_AppCalendario.AppCalendarioData AS AppCalendarioData,
		date_format(RT_AppCalendario.AppCalendarioData,
		_utf8'%d/%m/%Y') AS DataPartenzaFormattata,
		RT_Corsa.IncludiFeriale AS IncludiFeriale,
		RT_Corsa.IncludiPrefestivo AS IncludiPrefestivo,
		RT_Corsa.IncludiFestivo AS IncludiFestivo,
		RT_AppCalendario.Feriale AS Feriale,
		RT_AppCalendario.Prefestivo AS Prefestivo,
		RT_AppCalendario.Festivo AS Festivo,
		RT_Percorso.PercorsoId AS PercorsoId,
		RT_Linea.LineaId AS LineaId,
		RT_Corsa.CorsaId AS CorsaId,
		RT_Percorso.OdcIdRef AS OdcIdRef,
		RT_Corsa.OrarioPartenza AS OrarioPartenza,
		RT_Corsa.VendibileDal AS VendibileDal,
		RT_Corsa.VendibileAl AS VendibileAl,
		RT_Corsa.OrePrimaStopVendita AS OrePrimaStopVendita,
		addtime(RT_AppCalendario.AppCalendarioData,
		RT_Corsa.OrarioPartenza) AS DataOraPartenza,
		timediff(addtime(RT_AppCalendario.AppCalendarioData,
		RT_Corsa.OrarioPartenza),
		now()) AS OreMancanti,
		RT_Corsa.TipologiaBusDefaultId AS TipologiaBusDefaultId,
		RT_TipologiaBus.TotalePosti AS PostiCorsaDefault,
		if(isnull(RT_ViewSingolaCorsaPostiAggiunti.PostiAggiunti),
		0,
		RT_ViewSingolaCorsaPostiAggiunti.PostiAggiunti) AS PostiCorsaAggiunti,
		if(isnull(RT_ViewSingolaCorsaPostiPrenotati.TotalePaxPrenotati),
		0,
		RT_ViewSingolaCorsaPostiPrenotati.TotalePaxPrenotati) AS PostiCorsaPrenotati,
		RT_ViewSingolaCorsaPostiRealmentePrenotati.TotalePaxPrenotati AS PostiRealmentePrenotati
		from
		(((((((((RT_Percorso
		left join RT_Linea ON ((RT_Percorso.PercorsoId = RT_Linea.PercorsoId)))
		left join RT_Corsa ON ((RT_Linea.LineaId = RT_Corsa.LineaId)))
		left join RT_CorsaSettimana ON ((RT_Corsa.CorsaId = RT_CorsaSettimana.CorsaId)))
		left join RT_AppSettimana ON ((RT_CorsaSettimana.SettimanaId = RT_AppSettimana.AppSettimanaId)))
		left join RT_AppCalendario ON ((RT_AppSettimana.AppSettimanaGiorno = RT_AppCalendario.GiornoSettimana)))
		left join RT_TipologiaBus ON ((RT_Corsa.TipologiaBusDefaultId = RT_TipologiaBus.TipologiaBusId)))
		left join RT_ViewSingolaCorsaPostiAggiunti ON (((RT_Corsa.CorsaId = RT_ViewSingolaCorsaPostiAggiunti.CorsaId)
		and (RT_AppCalendario.AppCalendarioData = RT_ViewSingolaCorsaPostiAggiunti.DataPartenza))))
		left join RT_ViewSingolaCorsaPostiPrenotati ON (((RT_Corsa.CorsaId = RT_ViewSingolaCorsaPostiPrenotati.CorsaId)
		and (RT_ViewSingolaCorsaPostiPrenotati.CorsaDataPartenza = RT_AppCalendario.AppCalendarioData))))
		left join RT_ViewSingolaCorsaPostiRealmentePrenotati ON (((RT_Corsa.CorsaId = RT_ViewSingolaCorsaPostiRealmentePrenotati.CorsaId)
		and (RT_AppCalendario.AppCalendarioData = RT_ViewSingolaCorsaPostiRealmentePrenotati.CorsaDataPartenza))))
		where
		(RT_AppCalendario.AppCalendarioData >= RT_Corsa.AttivaDal)
            and (RT_AppCalendario.AppCalendarioData <= RT_Corsa.AttivaAl)
            and ((RT_AppCalendario.Feriale = RT_Corsa.IncludiFeriale)
            or (RT_AppCalendario.Prefestivo = RT_Corsa.IncludiPrefestivo)
            or (RT_AppCalendario.Festivo = RT_Corsa.IncludiFestivo)) and
		Date(RT_AppCalendario.AppCalendarioData) = '$data'  and RT_Corsa.CorsaId = $corsaId 
		and RT_Linea.LineaId = $lineaId order by RT_AppCalendario.AppCalendarioData" ;
		
		$row = $db->fetch_array($sql);
		return $row[0];
	}
        
	function getCorseValideBO($data, $sOrder, $sWhere)
	{
		global $user;
		$db = $this->conn;
		$sql1 = "";
		if ($sWhere != "") {
			$sql1 = "and $sWhere";
		}
		$sql = "
			SELECT
				RT_Corsa.CorsaId AS CorsaId,
				RT_AppCalendario.AppCalendarioData AS AppCalendarioData,
				date_format(RT_AppCalendario.AppCalendarioData, _utf8'%d/%m/%Y') AS DataPartenzaFormattata,
				RT_Corsa.CorsaNome AS CorsaNome,
				RT_Corsa.LineaId AS LineaId,
				RT_Linea.LineaNome AS LineaNome,
				RT_Corsa.OrarioPartenza AS OrarioPartenza,
				RT_Corsa.OdcIdRef AS OdcIdRef,
				RT_Corsa.GestoreIdRef AS GestoreIdRef
			FROM
				RT_Corsa
				JOIN RT_CorsaSettimana ON (RT_Corsa.CorsaId = RT_CorsaSettimana.CorsaId)
				JOIN RT_AppSettimana ON (RT_CorsaSettimana.SettimanaId = RT_AppSettimana.AppSettimanaId)
				JOIN RT_AppCalendario ON (RT_AppSettimana.AppSettimanaGiorno = RT_AppCalendario.GiornoSettimana)
				JOIN RT_Linea ON (RT_Corsa.LineaId = RT_Linea.LineaId)
				JOIN RT_TipologiaBus ON (RT_Corsa.TipologiaBusDefaultId = RT_TipologiaBus.TipologiaBusId)
				LEFT JOIN RT_CorsaBloccoWeb ON (
					RT_Corsa.CorsaId = RT_CorsaBloccoWeb.CorsaId
					AND RT_AppCalendario.AppCalendarioData = RT_CorsaBloccoWeb.DataPartenza
				)
				LEFT JOIN RT_CorsaBlocco ON (
					RT_Corsa.CorsaId = RT_CorsaBlocco.CorsaId
					AND RT_AppCalendario.AppCalendarioData = RT_CorsaBlocco.DataPartenza
				)
			WHERE
				(
					RT_Corsa.Cancella = 0
					AND RT_Corsa.Stato = 1
					AND RT_AppCalendario.AppCalendarioData >= RT_Corsa.AttivaDal
					AND RT_AppCalendario.AppCalendarioData <= RT_Corsa.AttivaAl
					AND (
						(RT_AppCalendario.Feriale = RT_Corsa.IncludiFeriale AND RT_Corsa.IncludiFeriale = 1)
						OR (RT_AppCalendario.Prefestivo = RT_Corsa.IncludiPrefestivo AND RT_Corsa.IncludiPrefestivo = 1)
						OR (RT_AppCalendario.Festivo = RT_Corsa.IncludiFestivo AND RT_Corsa.IncludiFestivo = 1)
					)
					AND RT_AppCalendario.AppCalendarioData = '$data'
					$sql1
				)
			GROUP BY
				RT_Corsa.CorsaId,
				RT_AppCalendario.AppCalendarioData
			ORDER BY
				RT_AppCalendario.AppCalendarioData,
				RT_Linea.PercorsoId,
				RT_Linea.LineaNome,
				
		";

		if(isset($sOrder) && $sOrder != "") {
			$sql .= " $sOrder ,";
		}
		$sql .= " RT_Corsa.CorsaPeso ASC,
				RT_Corsa.CorsaNome";
	//echo $sql	
		$row = $db->fetch_array($sql);
		return $row;
	}
	
	function getCorseValide($data, $sOrder, $sWhere)
	{
		global $user;
		$db=$this->conn;
		$sql = "select 
        RT_Percorso.PercorsoNome AS PercorsoNome,
        RT_Linea.LineaArea AS LineaArea,
        RT_Linea.LineaNome AS LineaNome,
        RT_Corsa.CorsaNome AS CorsaNome,
        RT_AppSettimana.AppSettimanaGiorno AS AppSettimanaGiorno,
        RT_AppSettimana.AppSettimanaGiornoDescr AS AppSettimanaGiornoDescr,
        RT_Corsa.AttivaDal AS AttivaDal,
        RT_Corsa.AttivaAl AS AttivaAl,
        RT_AppCalendario.AppCalendarioData AS AppCalendarioData,
        date_format(RT_AppCalendario.AppCalendarioData,
                _utf8'%d/%m/%Y') AS DataPartenzaFormattata,
        RT_Corsa.IncludiFeriale AS IncludiFeriale,
        RT_Corsa.IncludiPrefestivo AS IncludiPrefestivo,
        RT_Corsa.IncludiFestivo AS IncludiFestivo,
        RT_AppCalendario.Feriale AS Feriale,
        RT_AppCalendario.Prefestivo AS Prefestivo,
        RT_AppCalendario.Festivo AS Festivo,
        RT_Percorso.PercorsoId AS PercorsoId,
        RT_Linea.LineaId AS LineaId,
        RT_Corsa.CorsaId AS CorsaId,
        RT_Percorso.OdcIdRef AS OdcIdRef,
        RT_Corsa.OrarioPartenza AS OrarioPartenza,
        RT_Corsa.VendibileDal AS VendibileDal,
        RT_Corsa.VendibileAl AS VendibileAl,
        RT_Corsa.OrePrimaStopVendita AS OrePrimaStopVendita,
        addtime(RT_AppCalendario.AppCalendarioData,
                RT_Corsa.OrarioPartenza) AS DataOraPartenza,
        timediff(addtime(RT_AppCalendario.AppCalendarioData,
                        RT_Corsa.OrarioPartenza),
                now()) AS OreMancanti,
        RT_Corsa.TipologiaBusDefaultId AS TipologiaBusDefaultId,
        RT_TipologiaBus.TotalePosti AS PostiCorsaDefault,
        if(isnull(RT_ViewSingolaCorsaPostiAggiunti.PostiAggiunti),
            0,
            RT_ViewSingolaCorsaPostiAggiunti.PostiAggiunti) AS PostiCorsaAggiunti,
        if(isnull(RT_ViewSingolaCorsaPostiPrenotati.TotalePaxPrenotati),
            0,
            RT_ViewSingolaCorsaPostiPrenotati.TotalePaxPrenotati) AS PostiCorsaPrenotati,
        RT_ViewSingolaCorsaPostiRealmentePrenotati.TotalePaxPrenotati AS PostiRealmentePrenotati
    from
        (((((((((RT_Percorso
        left join RT_Linea ON ((RT_Percorso.PercorsoId = RT_Linea.PercorsoId)))
        left join RT_Corsa ON ((RT_Linea.LineaId = RT_Corsa.LineaId)))
        left join RT_CorsaSettimana ON ((RT_Corsa.CorsaId = RT_CorsaSettimana.CorsaId)))
        left join RT_AppSettimana ON ((RT_CorsaSettimana.SettimanaId = RT_AppSettimana.AppSettimanaId)))
        left join RT_AppCalendario ON ((RT_AppSettimana.AppSettimanaGiorno = RT_AppCalendario.GiornoSettimana)))
        left join RT_TipologiaBus ON ((RT_Corsa.TipologiaBusDefaultId = RT_TipologiaBus.TipologiaBusId)))
        left join RT_ViewSingolaCorsaPostiAggiunti ON (((RT_Corsa.CorsaId = RT_ViewSingolaCorsaPostiAggiunti.CorsaId)
            and (RT_AppCalendario.AppCalendarioData = RT_ViewSingolaCorsaPostiAggiunti.DataPartenza))))
        left join RT_ViewSingolaCorsaPostiPrenotati ON (((RT_Corsa.CorsaId = RT_ViewSingolaCorsaPostiPrenotati.CorsaId)
            and (RT_ViewSingolaCorsaPostiPrenotati.CorsaDataPartenza = RT_AppCalendario.AppCalendarioData))))
        left join RT_ViewSingolaCorsaPostiRealmentePrenotati ON (((RT_Corsa.CorsaId = RT_ViewSingolaCorsaPostiRealmentePrenotati.CorsaId)
            and (RT_AppCalendario.AppCalendarioData = RT_ViewSingolaCorsaPostiRealmentePrenotati.CorsaDataPartenza))))
    where
        ((RT_Percorso.Stato = 1)
            and (RT_Percorso.Cancella = 0)
            and (RT_Linea.Stato = 1)
            and (RT_Linea.Cancella = 0)
            and (RT_Corsa.Stato = 1)
            and (RT_Corsa.Cancella = 0)
            and (RT_AppCalendario.AppCalendarioData >= RT_Corsa.AttivaDal)
            and (RT_AppCalendario.AppCalendarioData <= RT_Corsa.AttivaAl)
            and ((RT_AppCalendario.Feriale = RT_Corsa.IncludiFeriale)
            or (RT_AppCalendario.Prefestivo = RT_Corsa.IncludiPrefestivo)
            or (RT_AppCalendario.Festivo = RT_Corsa.IncludiFestivo)))
				and Date(RT_AppCalendario.AppCalendarioData) = '$data' 
		and RT_Corsa.RitornoAperto != 1 ";
		if($sWhere != ""){
			$sql .= "and $sWhere";
		}
		$sql .= " order by RT_AppCalendario.AppCalendarioData";
		if($sOrder !=""){
			$sql.= ", $sOrder";
		}	
		
		$row = $db->fetch_array($sql);
		return $row;
	}
	
	
	function getCorsRitornoAperto($corsaId) {
		global $user;
		$db = $this->conn;
		$sql = "SELECT LineaId FROM RT_Corsa where CorsaId = $corsaId";		
		$row = $db->fetch_array($sql);
		if($row[0]['LineaId'] == 1){
			$linea = 2;
		} else if ($row[0]['LineaId'] == 2){
			$linea = 1;
		} else if ($row[0]['LineaId'] == 13) {
			$linea = 14;
		} else if ($row[0]['LineaId'] == 14) {
			$linea = 13;
		} else if ($row[0]['LineaId'] == 16) {
			$linea = 17;
		} else if ($row[0]['LineaId'] == 17) {
			$linea = 16;
		} else {
			$linea = 16;
		}
		$sql = "Select * FROM RT_Corsa WHERE LineaId = ".$linea." AND RitornoAperto = 1";

		$row = $db->fetch_array($sql);
		return $row[0];
	}

	
	function elencoCorse($lineaId, $userId){
		$db = $this->conn;
		$sql = "select 
        `RT_Corsa`.`CorsaId` AS `CorsaId`,
        `RT_Corsa`.`LineaId` AS `LineaId`,
        `RT_Corsa`.`CorsaNome` AS `CorsaNome`,
        `RT_Corsa`.`AttivaDal` AS `AttivaDal`,
        `RT_Corsa`.`AttivaAl` AS `AttivaAl`,
        `RT_Corsa`.`IncludiFeriale` AS `IncludiFeriale`,
        `RT_Corsa`.`IncludiPrefestivo` AS `IncludiPrefestivo`,
        `RT_Corsa`.`IncludiFestivo` AS `IncludiFestivo`,
        `RT_Corsa`.`OrarioPartenza` AS `OrarioPartenza`,
        `RT_Corsa`.`OrarioArrivo` AS `OrarioArrivo`,
        `RT_Corsa`.`NextDay` AS `NextDay`,
        `RT_Corsa`.`DataIns` AS `DataIns`,
        `RT_Corsa`.`OpeIns` AS `OpeIns`,
        `RT_Corsa`.`SedeIns` AS `SedeIns`,
        `RT_Corsa`.`IpIns` AS `IpIns`,
        `RT_Corsa`.`DataAgg` AS `DataAgg`,
        `RT_Corsa`.`OpeAgg` AS `OpeAgg`,
        `RT_Corsa`.`SedeAgg` AS `SedeAgg`,
        `RT_Corsa`.`IpAgg` AS `IpAgg`,
        `RT_Corsa`.`Stato` AS `Stato`,
        `RT_Corsa`.`Cancella` AS `Cancella`,
        `RT_Corsa`.`UpdateCount` AS `UpdateCount`,
        `RT_Corsa`.`OdcIdRef` AS `OdcIdRef`,
        `RT_Corsa`.`GestoreIdRef` AS `GestoreIdRef`,
        `RT_Linea`.`LineaNome` AS `LineaNome`,
        `RT_Linea`.`PercorsoId` AS `PercorsoId`,
        `RT_Percorso`.`PercorsoNome` AS `PercorsoNome`,
        `RT_Corsa`.`CorsaPeso` AS `CorsaPeso`,
        `RT_Corsa`.`VendibileDal` AS `VendibileDal`,
        `RT_Corsa`.`VendibileAl` AS `VendibileAl`,
        `RT_Percorso`.`Identificativo` AS `Identificativo`
		    from
		        ((`RT_Corsa`
		        left join `RT_Linea` ON ((`RT_Corsa`.`LineaId` = `RT_Linea`.`LineaId`)))
		        left join `RT_Percorso` ON ((`RT_Linea`.`PercorsoId` = `RT_Percorso`.`PercorsoId`)))
		    where
        		(`RT_Corsa`.`Cancella` = 0) AND RT_Corsa.RitornoAperto = 0 AND `RT_Linea`.`LineaId` = $lineaId and `RT_Corsa`.`OdcIdRef` = $userId";
		
		$row = $db->fetch_array($sql);
		return $row;
		
	}
        
        function elencoCorseNoObsolete($lineaId, $userId, $disattiva = false){
		$db = $this->conn;
		$sql = "select 
        `RT_Corsa`.`CorsaId` AS `CorsaId`,
        `RT_Corsa`.`LineaId` AS `LineaId`,
        `RT_Corsa`.`CorsaNome` AS `CorsaNome`,
        `RT_Corsa`.`AttivaDal` AS `AttivaDal`,
        `RT_Corsa`.`AttivaAl` AS `AttivaAl`,
        `RT_Corsa`.`IncludiFeriale` AS `IncludiFeriale`,
        `RT_Corsa`.`IncludiPrefestivo` AS `IncludiPrefestivo`,
        `RT_Corsa`.`IncludiFestivo` AS `IncludiFestivo`,
        `RT_Corsa`.`OrarioPartenza` AS `OrarioPartenza`,
        `RT_Corsa`.`OrarioArrivo` AS `OrarioArrivo`,
        `RT_Corsa`.`NextDay` AS `NextDay`,
        `RT_Corsa`.`DataIns` AS `DataIns`,
        `RT_Corsa`.`OpeIns` AS `OpeIns`,
        `RT_Corsa`.`SedeIns` AS `SedeIns`,
        `RT_Corsa`.`IpIns` AS `IpIns`,
        `RT_Corsa`.`DataAgg` AS `DataAgg`,
        `RT_Corsa`.`OpeAgg` AS `OpeAgg`,
        `RT_Corsa`.`SedeAgg` AS `SedeAgg`,
        `RT_Corsa`.`IpAgg` AS `IpAgg`,
        `RT_Corsa`.`Stato` AS `Stato`,
        `RT_Corsa`.`Cancella` AS `Cancella`,
        `RT_Corsa`.`UpdateCount` AS `UpdateCount`,
        `RT_Corsa`.`OdcIdRef` AS `OdcIdRef`,
        `RT_Corsa`.`GestoreIdRef` AS `GestoreIdRef`,
        `RT_Linea`.`LineaNome` AS `LineaNome`,
        `RT_Linea`.`PercorsoId` AS `PercorsoId`,
        `RT_Percorso`.`PercorsoNome` AS `PercorsoNome`,
        `RT_Corsa`.`CorsaPeso` AS `CorsaPeso`,
        `RT_Corsa`.`VendibileDal` AS `VendibileDal`,
        `RT_Corsa`.`VendibileAl` AS `VendibileAl`,
        `RT_Percorso`.`Identificativo` AS `Identificativo`
		    from
		        ((`RT_Corsa`
		        left join `RT_Linea` ON ((`RT_Corsa`.`LineaId` = `RT_Linea`.`LineaId`)))
		        left join `RT_Percorso` ON ((`RT_Linea`.`PercorsoId` = `RT_Percorso`.`PercorsoId`)))
		    where
        		(`RT_Corsa`.`Cancella` = 0) and (`RT_Corsa`.`Obsoleta` = 0)  AND RT_Corsa.RitornoAperto = 0 AND `RT_Linea`.`LineaId` = $lineaId and `RT_Corsa`.`OdcIdRef` = $userId";
		if ($disattiva) {
			$sql .= " and `RT_Corsa`.`Stato` = 1 and `RT_Corsa`.`AttivaAl` > Date(NOW()) ";
		}

		$row = $db->fetch_array($sql);
		return $row;
	}
        
        
}
?>
